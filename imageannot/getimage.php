<?php
require_once '../include/Session.php';
require_once '../include/DbUtils.php';

$loginname = isset($_SESSION['loginname']) ? $_SESSION['loginname'] : '';
if (!$loginname) { http_response_code(403); exit; }

$id   = (int)($_GET['id']   ?? 0);
$an   = trim($_GET['an']    ?? '');
$type = trim($_GET['type']  ?? 'annotated'); // 'original' หรือ 'annotated'

if (!$id || !$an) { http_response_code(400); exit; }

try {
    $conn  = DbUtils::get_hosxp_connection();

    // เลือก field ตาม type เพื่อไม่ต้อง SELECT ทั้ง 2 BLOB พร้อมกัน (ประหยัด memory)
    $field = ($type === 'original') ? 'image_data' : 'annotated_image';

    $stmt  = $conn->prepare("SELECT image_type, {$field} AS img_data
                               FROM prs_image_annot
                              WHERE id = :id AND an = :an AND is_deleted = 0");
    $stmt->execute(['id' => $id, 'an' => $an]);
    $row   = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || $row['img_data'] === null) {
        http_response_code(404);
        exit;
    }

    $mime = $row['image_type'] ?: 'image/png';
    $data = is_resource($row['img_data']) ? stream_get_contents($row['img_data']) : $row['img_data'];

    header('Content-Type: '   . $mime);
    header('Content-Length: ' . strlen($data));
    header('Cache-Control: private, max-age=300');
    echo $data;

} catch (Exception $e) {
    http_response_code(500);
    exit;
}
