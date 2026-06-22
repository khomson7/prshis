<?php
ob_start();
require_once '../include/Session.php';
require_once '../include/session-sso.php';
require_once '../include/DbUtils.php';

date_default_timezone_set('Asia/Bangkok');
ob_end_clean();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status'=>'error','message'=>'Invalid request method']); exit;
}
$loginname = isset($_SESSION['loginname']) ? $_SESSION['loginname'] : '';
if (!$loginname) {
    echo json_encode(['status'=>'error','message'=>'Session expired']); exit;
}

$id = (int)($_POST['id'] ?? 0);
$an = trim($_POST['an'] ?? '');
if (!$id || !$an) {
    echo json_encode(['status'=>'error','message'=>'ข้อมูลไม่ครบถ้วน']); exit;
}

try {
    $conn = DbUtils::get_hosxp_connection();

    $stmt = $conn->prepare("UPDATE " . DbConstant::KPHIS_DBNAME . ".prs_or_complication SET is_deleted = 1 WHERE id = :id AND an = :an");
    $stmt->execute(['id' => $id, 'an' => $an]);

    Session::insertSystemAccessLog(json_encode([
        'form'=>'ORS-COMPLICATION','action'=>'DELETE','an'=>$an,'id'=>$id,
    ], JSON_UNESCAPED_UNICODE));

    echo json_encode(['status'=>'success']);

} catch (Exception $e) {
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}

