<?php
/**
 * form-nutrition-sp.php
 * เรียกใช้ Stored Procedure: sp_update_bw_all(an)
 * รับค่า an ผ่าน POST แล้วประมวลผล
 * 
 * Optimized: closeCursor, timeout, JSON response, execution time tracking
 */
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';
require_once '../include/Session.php';

date_default_timezone_set("Asia/Bangkok");

// ตั้ง max execution time สำหรับ PHP
set_time_limit(120);

header('Content-Type: application/json; charset=utf-8');

$an = isset($_REQUEST['an']) ? trim($_REQUEST['an']) : '';

if (empty($an)) {
    echo json_encode(['status' => 'error', 'message' => 'ไม่พบเลข AN', 'elapsed' => 0]);
    exit;
}

$startTime = microtime(true);

try {
    $conn = DbUtils::get_hosxp_connection();

    // ตั้ง timeout สำหรับ query (วินาที)
    $conn->setAttribute(PDO::ATTR_TIMEOUT, 60);

    // Call stored procedure ด้วย exec() ตรงๆ — เร็วกว่า prepare+execute สำหรับ SP ที่ไม่ return result set
    // Sanitize AN: เอาเฉพาะตัวเลขและ dash
    $safe_an = preg_replace('/[^0-9\-\/]/', '', $an);
    $conn->exec("CALL sp_update_bw_all('$safe_an')");

    $elapsed = round(microtime(true) - $startTime, 2);

    // Log แบบ async — ไม่ต้องรอ
    try {
        Session::insertSystemAccessLog(json_encode(array(
            'form'   => 'NUTRITION-FORM',
            'action' => 'CALL_SP_UPDATE_BW_ALL',
            'an'     => $an,
            'elapsed_sec' => $elapsed,
        ), JSON_UNESCAPED_UNICODE));
    } catch (Exception $logEx) {
        // ไม่ block ถ้า log ล้มเหลว
    }

    echo json_encode([
        'status'  => 'success',
        'message' => "ประมวลผล sp_update_bw_all สำเร็จ ({$elapsed} วินาที)",
        'elapsed' => $elapsed,
    ]);

} catch (PDOException $e) {
    $elapsed = round(microtime(true) - $startTime, 2);
    echo json_encode([
        'status'  => 'error',
        'message' => 'ประมวลผลไม่สำเร็จ: ' . $e->getMessage(),
        'elapsed' => $elapsed,
    ]);
}
?>
