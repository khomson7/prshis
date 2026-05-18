<?php
ob_start();
require_once '../include/Session.php';
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

    // ตรวจสิทธิ์: เฉพาะผู้บันทึกเท่านั้น
    $stmt_chk = $conn->prepare("SELECT created_by FROM prs_opnote WHERE id = :id AND an = :an AND is_deleted = 0");
    $stmt_chk->execute(['id' => $id, 'an' => $an]);
    $owner = $stmt_chk->fetchColumn();

    if ($owner === false) {
        echo json_encode(['status'=>'error','message'=>'ไม่พบรายการ']); exit;
    }
    if ($owner !== $loginname) {
        echo json_encode(['status'=>'error','message'=>'ไม่มีสิทธิ์ลบ — เฉพาะผู้บันทึก (' . $owner . ') เท่านั้น']); exit;
    }

    // Soft delete
    $stmt = $conn->prepare("UPDATE prs_opnote
                               SET is_deleted = 1, deleted_by = :deleted_by, deleted_at = NOW()
                             WHERE id = :id AND an = :an");
    $stmt->execute(['deleted_by' => $loginname, 'id' => $id, 'an' => $an]);

    Session::insertSystemAccessLog(json_encode([
        'form'=>'IMAGE-ANNOT','action'=>'DELETE','an'=>$an,'id'=>$id,
    ], JSON_UNESCAPED_UNICODE));

    echo json_encode(['status'=>'success']);

} catch (Exception $e) {
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
