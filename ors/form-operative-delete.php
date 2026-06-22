<?php
require_once '../include/Session.php';
require_once '../include/session-sso.php';
$loginname = isset($_SESSION['loginname']) ? $_SESSION['loginname'] : null;

require_once '../include/DbUtils.php';

header('Content-Type: application/json; charset=utf-8');

try {
    if (!Session::checkPermission('OPNOTE', 'REMOVE')) {
        throw new Exception('ไม่มีสิทธิ์ในการลบข้อมูล');
    }

    $conn = DbUtils::get_hosxp_connection();

    $id = $_POST['id'] ?? '';
    $an = $_POST['an'] ?? '';
    
    if (empty($id) || empty($an)) throw new Exception('ข้อมูลไม่ครบถ้วน (ID/AN)');

    $stmt_c = $conn->prepare("SELECT created_by FROM prs_operative_note WHERE id = :id AND an = :an");
    $stmt_c->execute(['id' => $id, 'an' => $an]);
    $row = $stmt_c->fetch(PDO::FETCH_ASSOC);
    if (!$row) throw new Exception('ไม่พบข้อมูลที่ต้องการลบ');
    if ($row['created_by'] !== $loginname && strtolower($loginname) !== 'admin') throw new Exception('ลบได้เฉพาะผู้บันทึก หรือ Admin เท่านั้น');

    $stmt = $conn->prepare("UPDATE prs_operative_note SET 
        is_deleted = 1,
        update_user = :update_user,
        update_datetime = NOW()
        WHERE id = :id AND an = :an
    ");

    $stmt->execute([
        'update_user' => $loginname,
        'id' => $id,
        'an' => $an
    ]);

    Session::insertSystemAccessLog(json_encode([
        'form' => 'OPERATIVE-NOTE-DELETE',
        'an'   => $an,
        'id'   => $id
    ], JSON_UNESCAPED_UNICODE));

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

