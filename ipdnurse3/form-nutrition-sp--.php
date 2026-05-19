<?php
/**
 * form-nutrition-sp.php
 * API ทดแทน Stored Procedure: sp_update_bw_all
 * ประมวลผลจาก HOSxP (slave_his) และบันทึกลง kphis (prs_check_vitalsign)
 */
require_once '../include/DbUtils.php';
require_once '../include/Session.php';

header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set("Asia/Bangkok");
set_time_limit(120);

$an = isset($_REQUEST['an']) ? trim($_REQUEST['an']) : '';

if (empty($an)) {
    echo json_encode(['status' => 'error', 'message' => 'ไม่พบเลข AN', 'elapsed' => 0]);
    exit;
}

$startTime = microtime(true);

try {
    // 1. เชื่อมต่อ 2 ฐานข้อมูล (kphis สำหรับเขียน, slave_his สำหรับอ่าน)
    $conn_kphis = DbUtils::get_hosxp_connection();
    $conn_slave = DbUtils::get_slave_connection();

    // 2. ตรวจสอบว่า an นี้มีข้อมูลในตาราง prs_check_vitalsign หรือไม่
    $stmt_check = $conn_kphis->prepare("SELECT 1 FROM prs_check_vitalsign WHERE an = :an");
    $stmt_check->execute(['an' => $an]);
    if ($stmt_check->fetch()) {
        throw new Exception("AN: {$an} มีข้อมูลในระบบแล้ว กรุณาตรวจสอบ");
    }

    // 3. ดึงข้อมูลหลักจาก HOSxP (slave_his) - opdscreen, ovst, ipt, an_stat
    $sql_main = "
        SELECT 
            ov.an, o.hn, o.vstdate, o.height, o.bw, o.bmi, an_stat.age_y,
            CASE
                WHEN o.bmi >= 17.00 AND o.bmi <= 18.49 THEN 'Mild'
                WHEN o.bmi >= 16.00 AND o.bmi <= 16.99 THEN 'Moderate'
                WHEN o.bmi < 16.00                      THEN 'Severe'
                ELSE NULL
            END AS check_bmi
        FROM opdscreen o
        INNER JOIN ovst ov ON ov.vn = o.vn
        INNER JOIN ipt i ON i.an = ov.an
        INNER JOIN an_stat ON an_stat.an = ov.an
        WHERE i.an = :an
          AND o.height IS NOT NULL AND o.height > 0
          AND o.bw IS NOT NULL AND o.bw > 0
          AND o.bmi IS NOT NULL AND o.bmi > 0
        ORDER BY o.bmi ASC 
        LIMIT 1
    ";
    
    $stmt_main = $conn_slave->prepare($sql_main);
    $stmt_main->execute(['an' => $an]);
    $main_data = $stmt_main->fetch(PDO::FETCH_ASSOC);

    if (!$main_data) {
        throw new Exception("ไม่พบข้อมูลที่ถูกต้องสำหรับ AN: {$an} (height, bw, bmi ต้องมีค่าและมากกว่า 0)");
    }

    $hn = $main_data['hn'];
    $vstdate = $main_data['vstdate']; // yyyy-mm-dd

    // 4. ดึงข้อมูลประวัติน้ำหนักย้อนหลังจาก HOSxP (slave_his) โดยใช้ Case เมื่อ vstdate อยู่ในช่วง
    $sql_hist = "
        SELECT 
            MAX(CASE WHEN vstdate >= DATE_SUB(:v1, INTERVAL 7 DAY) AND vstdate < :v2 THEN bw ELSE NULL END) as bw_1week,
            MAX(CASE WHEN vstdate >= DATE_SUB(:v3, INTERVAL 21 DAY) AND vstdate < DATE_SUB(:v4, INTERVAL 7 DAY) THEN bw ELSE NULL END) as bw_2_3week,
            MAX(CASE WHEN vstdate >= DATE_SUB(:v5, INTERVAL 1 MONTH) AND vstdate < DATE_SUB(:v6, INTERVAL 21 DAY) THEN bw ELSE NULL END) as bw_1month,
            MAX(CASE WHEN vstdate >= DATE_SUB(:v7, INTERVAL 3 MONTH) AND vstdate < DATE_SUB(:v8, INTERVAL 1 MONTH) THEN bw ELSE NULL END) as bw_3month,
            MAX(CASE WHEN vstdate >= DATE_SUB(:v9, INTERVAL 5 MONTH) AND vstdate < DATE_SUB(:v10, INTERVAL 3 MONTH) THEN bw ELSE NULL END) as bw_5month,
            MAX(CASE WHEN vstdate >= DATE_SUB(:v11, INTERVAL 12 MONTH) AND vstdate < DATE_SUB(:v12, INTERVAL 5 MONTH) THEN bw ELSE NULL END) as bw_12month
        FROM opdscreen
        WHERE hn = :hn
          AND bw IS NOT NULL AND bw > 0
          AND vstdate >= DATE_SUB(:v13, INTERVAL 12 MONTH)
          AND vstdate < :v14
    ";
    
    $stmt_hist = $conn_slave->prepare($sql_hist);
    $params = [
        'hn' => $hn,
        'v1' => $vstdate, 'v2' => $vstdate, 'v3' => $vstdate, 'v4' => $vstdate, 
        'v5' => $vstdate, 'v6' => $vstdate, 'v7' => $vstdate, 'v8' => $vstdate, 
        'v9' => $vstdate, 'v10' => $vstdate, 'v11' => $vstdate, 'v12' => $vstdate, 
        'v13' => $vstdate, 'v14' => $vstdate
    ];
    $stmt_hist->execute($params);
    $hist_data = $stmt_hist->fetch(PDO::FETCH_ASSOC);

    // 5. บันทึกข้อมูลลง kphis (prs_check_vitalsign)
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
    $stmt_insert = $conn_kphis->prepare($sql_insert);
    $stmt_insert->execute([
        'an' => $main_data['an'],
        'hn' => $main_data['hn'],
        'vstdate' => $main_data['vstdate'],
        'height' => $main_data['height'],
        'bw' => $main_data['bw'],
        'bmi' => $main_data['bmi'],
        'age_y' => $main_data['age_y'],
        'check_bmi' => $main_data['check_bmi'],
        'bw_1week' => $hist_data['bw_1week'] ?? null,
        'bw_2_3week' => $hist_data['bw_2_3week'] ?? null,
        'bw_1month' => $hist_data['bw_1month'] ?? null,
        'bw_3month' => $hist_data['bw_3month'] ?? null,
        'bw_5month' => $hist_data['bw_5month'] ?? null,
        'bw_12month' => $hist_data['bw_12month'] ?? null
    ]);

    $elapsed = round(microtime(true) - $startTime, 2);

    // Log การใช้งาน
    try {
        Session::insertSystemAccessLog(json_encode(array(
            'form'   => 'NUTRITION-FORM',
            'action' => 'CALL_API_UPDATE_BW_ALL',
            'an'     => $an,
            'elapsed_sec' => $elapsed,
        ), JSON_UNESCAPED_UNICODE));
    } catch (Exception $logEx) {
        // ไม่ block ถ้า log ล้มเหลว
    }

    echo json_encode([
        'status'  => 'success',
        'message' => "ประมวลผลข้อมูลจาก Slave สำเร็จ ({$elapsed} วินาที)",
        'elapsed' => $elapsed,
    ]);

} catch (PDOException $e) {
    $elapsed = round(microtime(true) - $startTime, 2);
    echo json_encode([
        'status'  => 'error',
        'message' => 'ข้อผิดพลาดฐานข้อมูล: ' . $e->getMessage(),
        'elapsed' => $elapsed,
    ]);
} catch (Exception $e) {
    $elapsed = round(microtime(true) - $startTime, 2);
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage(),
        'elapsed' => $elapsed,
    ]);
}
?>
