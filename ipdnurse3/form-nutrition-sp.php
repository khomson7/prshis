<?php
/**
 * form-nutrition-sp.php
 * เรียกใช้ Stored Procedure: sp_update_bw_all(an)
 * รับค่า an ผ่าน POST แล้วประมวลผล
 */
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';
require_once '../include/Session.php';

date_default_timezone_set("Asia/Bangkok");

$an = isset($_REQUEST['an']) ? trim($_REQUEST['an']) : '';

if (empty($an)) {
    echo '<script>NotificationMessage("ไม่พบเลข AN", "error")</script>';
    exit;
}

try {
    $conn = DbUtils::get_hosxp_connection();

    // Call stored procedure
    $stmt = $conn->prepare("CALL sp_update_bw_all(:an)");
    $stmt->execute(['an' => $an]);

    Session::insertSystemAccessLog(json_encode(array(
        'form'   => 'NUTRITION-FORM',
        'action' => 'CALL_SP_UPDATE_BW_ALL',
        'an'     => $an,
    ), JSON_UNESCAPED_UNICODE));

    echo '<script>NotificationMessage("ประมวลผล sp_update_bw_all สำเร็จ", "success")</script>';

} catch (PDOException $e) {
    echo $e->getMessage();
    echo '<script>NotificationMessage("ประมวลผลไม่สำเร็จ: ' . addslashes($e->getMessage()) . '", "error")</script>';
}
?>
