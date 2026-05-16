<?php
require_once '../include/Session.php';
require_once '../include/DbUtils.php';

date_default_timezone_set('Asia/Bangkok');
header('Content-Type: application/json; charset=utf-8');

$loginname = isset($_SESSION['loginname']) ? $_SESSION['loginname'] : '';
if (!$loginname) {
    echo json_encode(['status' => 'error', 'message' => 'Session expired']);
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$an = trim($_POST['an']  ?? '');

if (!$id || !$an) {
    echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
    exit;
}

try {
    $conn = DbUtils::get_kphis_log_db_connection();

    $stmt = $conn->prepare("SELECT * FROM prs_pdf_upload WHERE id = :id AND an = :an AND is_deleted = 0");
    $stmt->execute(['id' => $id, 'an' => $an]);
    $row = $stmt->fetch();

    if (!$row) {
        echo json_encode(['status' => 'error', 'message' => 'ไม่พบข้อมูล']);
        exit;
    }

    if ($row['upload_by'] !== $loginname) {
        echo json_encode(['status' => 'error', 'message' => 'สามารถลบได้เฉพาะเจ้าของไฟล์เท่านั้น']);
        exit;
    }

    // Soft delete
    $conn->prepare("UPDATE prs_pdf_upload
                       SET is_deleted = 1, deleted_at = :deleted_at, deleted_by = :deleted_by
                     WHERE id = :id")
         ->execute([
            'deleted_at' => date('Y-m-d H:i:s'),
            'deleted_by' => $loginname,
            'id'         => $id,
         ]);

    Session::insertSystemAccessLog(json_encode([
        'form'   => 'PDF-UPLOAD',
        'action' => 'DELETE',
        'an'     => $an,
        'id'     => $id,
    ], JSON_UNESCAPED_UNICODE));

    echo json_encode(['status' => 'success']);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
