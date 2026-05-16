<?php
ob_start();
require_once '../include/Session.php';
require_once '../include/DbUtils.php';

date_default_timezone_set('Asia/Bangkok');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('status' => 'error', 'message' => 'Invalid request method')); exit;
}
$loginname = isset($_SESSION['loginname']) ? $_SESSION['loginname'] : '';
if (!$loginname) {
    ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('status' => 'error', 'message' => 'Session expired')); exit;
}

try {
    $conn = DbUtils::get_hosxp_connection();

    $id = (int)(isset($_POST['id']) ? $_POST['id'] : 0);
    $an = trim(isset($_POST['an']) ? $_POST['an'] : '');

    if (!$id || !$an) throw new Exception('ข้อมูลไม่ครบถ้วน');

    $stmt = $conn->prepare(
        "UPDATE prs_ors_nursing_focus
            SET is_deleted = 1,
                updated_by = :updated_by,
                updated_at = NOW()
          WHERE id = :id AND an = :an"
    );
    $stmt->execute(array(
        'updated_by' => $loginname,
        'id'         => $id,
        'an'         => $an,
    ));

    Session::insertSystemAccessLog(json_encode(array(
        'form'   => 'ORS-NURSING-FOCUS',
        'action' => 'DELETE',
        'an'     => $an,
        'id'     => $id,
    ), JSON_UNESCAPED_UNICODE));

    ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('status' => 'success'));

} catch (Exception $e) {
    ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
}
