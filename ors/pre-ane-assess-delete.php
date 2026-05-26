<?php
    ob_start();
    require_once '../include/Session.php';
    Session::checkLoginSessionAndShowMessage();

    require_once '../include/DbUtils.php';
    require_once '../include/KphisQueryUtils.php';

    date_default_timezone_set("Asia/Bangkok");

    header('Content-Type: application/json; charset=utf-8');

    $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
    $an = isset($_REQUEST['an']) ? trim($_REQUEST['an']) : '';

    if (!$id || !$an) {
        ob_end_clean();
        echo json_encode(['status' => 'error', 'message' => 'Missing id or an']);
        exit;
    }

try {
    $conn = DbUtils::get_hosxp_connection();

    $stmt = $conn->prepare("UPDATE " . DbConstant::KPHIS_DBNAME . ".prs_pre_ane_assess SET is_deleted = 1 WHERE id = :id AND an = :an");
    $stmt->execute(['id' => $id, 'an' => $an]);

    Session::insertSystemAccessLog(json_encode([
        'form'   => 'PRE-ANE-ASSESS',
        'action' => 'DELETE',
        'id'     => $id,
        'an'     => $an,
    ], JSON_UNESCAPED_UNICODE));

    ob_end_clean();
    echo json_encode(['status' => 'success']);

} catch (PDOException $e) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
