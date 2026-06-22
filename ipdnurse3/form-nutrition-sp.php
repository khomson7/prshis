<?php
/**
 * form-nutrition-sp.php
 * PHP-side processing ทดแทน Stored Procedure: sp_update_bw_all
 * 
 * ปรับปรุง:
 *  - แยก Read (Slave) / Write (kphis) เพื่อลด load บน Master
 *  - ใช้ Prepared Statement ทุก query (แก้ SQL Injection)
 *  - รวม INSERT + UPDATE เป็น 1 INSERT ที่มี history ครบ
 *  - ดึง history ย้อนหลัง 12 เดือน (แก้ bug เดิมที่ดึงแค่ 1 เดือน)
 *  - Return JSON พร้อมข้อมูลทั้งหมด (ไม่ต้อง reload หน้า)
 */
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';
require_once '../include/Session.php';

require_once '../include/session-sso.php';
date_default_timezone_set("Asia/Bangkok");
set_time_limit(60); // ลดจาก 120 → 60 เพราะ query เบาลง

header('Content-Type: application/json; charset=utf-8');

$an = isset($_REQUEST['an']) ? trim($_REQUEST['an']) : '';

if (empty($an)) {
    echo json_encode(['status' => 'error', 'message' => 'ไม่พบเลข AN', 'elapsed' => 0]);
    exit;
}

$startTime = microtime(true);

try {
    // =====================================================
    // 1. เชื่อมต่อ DB — ใช้ server หลักตัวเดียว (อ่าน+เขียน)
    // =====================================================
    $conn = DbUtils::get_hosxp_connection();  // server หลัก (kphis DB, cross-query hos.*)
    $hosDb = DbConstant::HOSXP_DBNAME;

    // =====================================================
    // 2. Early Exit — ตรวจข้อมูลซ้ำก่อน (query เบาบน kphis)
    // =====================================================
    $stmt_check = $conn->prepare(
        "SELECT 1 FROM prs_check_vitalsign WHERE an = :an LIMIT 1"
    );
    $stmt_check->execute(['an' => $an]);

    if ($stmt_check->fetch()) {
        $stmt_check->closeCursor();
        throw new Exception("AN: {$an} มีข้อมูลในระบบแล้ว กรุณาตรวจสอบ");
    }
    $stmt_check->closeCursor();

    // =====================================================
    // 3. ดึงข้อมูลหลักจาก hos (opdscreen + ovst + an_stat)
    //    ลด JOIN จาก 3 ตาราง → 2 ตาราง (ไม่ JOIN ipt)
    //    ใช้ ovst.an กรอง → opdscreen.hn ใช้ index
    // =====================================================
    $sql_main = "
        SELECT 
            ov.an,
            o.hn,
            o.vstdate,
            o.height,
            o.bw,
            o.bmi,
            ans.age_y,
            CASE
                WHEN o.bmi >= 17.00 AND o.bmi <= 18.49 THEN 'Mild'
                WHEN o.bmi >= 16.00 AND o.bmi <= 16.99 THEN 'Moderate'
                WHEN o.bmi < 16.00                      THEN 'Severe'
                ELSE NULL
            END AS check_bmi
        FROM {$hosDb}.opdscreen o
        INNER JOIN {$hosDb}.ovst ov ON ov.vn = o.vn AND ov.an = :an
        INNER JOIN {$hosDb}.an_stat ans ON ans.an = :an2
        WHERE o.bmi    > 0
          AND o.bw     > 0
          AND o.height > 0
        ORDER BY o.bmi ASC
        LIMIT 1
    ";

    $stmt_main = $conn->prepare($sql_main);
    $stmt_main->execute(['an' => $an, 'an2' => $an]);
    $main = $stmt_main->fetch(PDO::FETCH_ASSOC);
    $stmt_main->closeCursor();

    if (!$main) {
        throw new Exception(
            "ไม่พบข้อมูลที่ถูกต้องสำหรับ AN: {$an} (height, bw, bmi ต้องมีค่าและมากกว่า 0)"
        );
    }

    $hn = $main['hn'];
    $vstdate = $main['vstdate'];

    // =====================================================
    // 4. ดึงประวัติน้ำหนักย้อนหลัง 12 เดือน
    //    ใช้ CASE WHEN + MAX → ดึง 6 ช่วงเวลาใน 1 query
    //    ✅ แก้ bug เดิม: SP ดึงแค่ 1 เดือน (bw_3month, bw_5month = NULL)
    // =====================================================
    $sql_hist = "
        SELECT 
            MAX(CASE 
                WHEN o.vstdate >= DATE_SUB(:vd1, INTERVAL 7 DAY)
                 AND o.vstdate <  :vd2
                THEN o.bw END) AS bw_1week,

            MAX(CASE 
                WHEN o.vstdate >= DATE_SUB(:vd3, INTERVAL 21 DAY)
                 AND o.vstdate <  DATE_SUB(:vd4, INTERVAL 7 DAY)
                THEN o.bw END) AS bw_2_3week,

            MAX(CASE 
                WHEN o.vstdate >= DATE_SUB(:vd5, INTERVAL 1 MONTH)
                 AND o.vstdate <  DATE_SUB(:vd6, INTERVAL 21 DAY)
                THEN o.bw END) AS bw_1month,

            MAX(CASE 
                WHEN o.vstdate >= DATE_SUB(:vd7, INTERVAL 3 MONTH)
                 AND o.vstdate <  DATE_SUB(:vd8, INTERVAL 1 MONTH)
                THEN o.bw END) AS bw_3month,

            MAX(CASE 
                WHEN o.vstdate >= DATE_SUB(:vd9, INTERVAL 5 MONTH)
                 AND o.vstdate <  DATE_SUB(:vd10, INTERVAL 3 MONTH)
                THEN o.bw END) AS bw_5month,

            MAX(CASE 
                WHEN o.vstdate >= DATE_SUB(:vd11, INTERVAL 12 MONTH)
                 AND o.vstdate <  DATE_SUB(:vd12, INTERVAL 5 MONTH)
                THEN o.bw END) AS bw_12month

        FROM {$hosDb}.opdscreen o
        WHERE o.hn      = :hn
          AND o.bw      > 0
          AND o.vstdate >= DATE_SUB(:vd13, INTERVAL 12 MONTH)
          AND o.vstdate <  :vd14
    ";

    $stmt_hist = $conn->prepare($sql_hist);
    $hist_params = ['hn' => $hn];
    for ($i = 1; $i <= 14; $i++) {
        $hist_params["vd{$i}"] = $vstdate;
    }
    $stmt_hist->execute($hist_params);
    $hist = $stmt_hist->fetch(PDO::FETCH_ASSOC);
    $stmt_hist->closeCursor();

    // =====================================================
    // 5. INSERT ครั้งเดียว — รวมข้อมูลหลัก + history + status
    //    ✅ ลดจาก INSERT + SELECT vstdate + UPDATE = 3 query → 1 query
    // =====================================================
    $sql_insert = "
        INSERT INTO prs_check_vitalsign (
            an, hn, vstdate, height, bw, bmi, age_y, check_bmi,
            bw_1week, bw_2_3week, bw_1month, bw_3month, bw_5month, bw_12month,
            status_process
        ) VALUES (
            :an, :hn, :vstdate, :height, :bw, :bmi, :age_y, :check_bmi,
            :bw_1week, :bw_2_3week, :bw_1month, :bw_3month, :bw_5month, :bw_12month,
            'Y'
        )
    ";

    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->execute([
        'an' => $main['an'],
        'hn' => $main['hn'],
        'vstdate' => $main['vstdate'],
        'height' => $main['height'],
        'bw' => $main['bw'],
        'bmi' => $main['bmi'],
        'age_y' => $main['age_y'],
        'check_bmi' => $main['check_bmi'],
        'bw_1week' => $hist['bw_1week'] ?? null,
        'bw_2_3week' => $hist['bw_2_3week'] ?? null,
        'bw_1month' => $hist['bw_1month'] ?? null,
        'bw_3month' => $hist['bw_3month'] ?? null,
        'bw_5month' => $hist['bw_5month'] ?? null,
        'bw_12month' => $hist['bw_12month'] ?? null,
    ]);

    $insertedId = $conn->lastInsertId();

    $elapsed = round(microtime(true) - $startTime, 2);

    // =====================================================
    // 6. Log (ไม่ block ถ้า log ล้มเหลว)
    // =====================================================
    try {
        Session::insertSystemAccessLog(json_encode([
            'form' => 'NUTRITION-FORM',
            'action' => 'PHP_UPDATE_BW_ALL',
            'an' => $an,
            'elapsed_sec' => $elapsed,
        ], JSON_UNESCAPED_UNICODE));
    } catch (Exception $logEx) {
        // ไม่ block
    }

    // =====================================================
    // 7. Return JSON พร้อมข้อมูล — ไม่ต้อง reload หน้า
    // =====================================================
    echo json_encode([
        'status' => 'success',
        'message' => "ประมวลผลสำเร็จ ({$elapsed} วินาที)",
        'elapsed' => $elapsed,
        'data' => [
            'id' => $insertedId,
            'an' => $main['an'],
            'hn' => $main['hn'],
            'vstdate' => $main['vstdate'],
            'height' => $main['height'],
            'bw' => $main['bw'],
            'bmi' => $main['bmi'],
            'age_y' => $main['age_y'],
            'check_bmi' => $main['check_bmi'],
            'bw_1week' => $hist['bw_1week'] ?? null,
            'bw_2_3week' => $hist['bw_2_3week'] ?? null,
            'bw_1month' => $hist['bw_1month'] ?? null,
            'bw_3month' => $hist['bw_3month'] ?? null,
            'bw_5month' => $hist['bw_5month'] ?? null,
            'bw_12month' => $hist['bw_12month'] ?? null,
        ],
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    $elapsed = round(microtime(true) - $startTime, 2);
    echo json_encode([
        'status' => 'error',
        'message' => 'ข้อผิดพลาดฐานข้อมูล: ' . $e->getMessage(),
        'elapsed' => $elapsed,
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    $elapsed = round(microtime(true) - $startTime, 2);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'elapsed' => $elapsed,
    ], JSON_UNESCAPED_UNICODE);
}
?>
