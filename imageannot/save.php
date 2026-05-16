<?php
require_once '../include/Session.php';
require_once '../include/DbUtils.php';

date_default_timezone_set('Asia/Bangkok');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$loginname = isset($_SESSION['loginname']) ? $_SESSION['loginname'] : '';
if (!$loginname) {
    echo json_encode(['status' => 'error', 'message' => 'Session expired']);
    exit;
}

$an            = trim($_POST['an']            ?? '');
$title         = trim($_POST['title']         ?? '');
$doc_group     = trim($_POST['doc_group']     ?? '');
$original_name = trim($_POST['original_name'] ?? '');
$canvas_json   = $_POST['canvas_json']        ?? '';
$form_data     = $_POST['form_data']          ?? '';
$form_note     = trim($_POST['form_note']     ?? '');
$image_b64     = $_POST['image_data']         ?? '';
$annotated_b64 = $_POST['annotated_image']    ?? '';

if (!$an || !$title) {
    echo json_encode(['status' => 'error', 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
    exit;
}

function decodeBase64Image(string $b64): ?string {
    if (empty($b64)) return null;
    $parts = explode(',', $b64, 2);
    $data  = count($parts) === 2 ? $parts[1] : $parts[0];
    $bin   = base64_decode($data, true);
    return ($bin !== false && strlen($bin) > 0) ? $bin : null;
}

$image_bin     = decodeBase64Image($image_b64);
$annotated_bin = decodeBase64Image($annotated_b64);

if (!$image_bin) {
    echo json_encode(['status' => 'error', 'message' => 'ข้อมูลภาพไม่ถูกต้อง']);
    exit;
}

$allowed_mime  = ['image/png','image/jpeg','image/gif','image/webp'];
$finfo         = new finfo(FILEINFO_MIME_TYPE);
$detected_mime = $finfo->buffer($image_bin);
if (!in_array($detected_mime, $allowed_mime)) {
    echo json_encode(['status' => 'error', 'message' => 'รองรับเฉพาะ PNG, JPG, GIF, WEBP']);
    exit;
}
if (strlen($image_bin) > 15 * 1024 * 1024) {
    echo json_encode(['status' => 'error', 'message' => 'ขนาดภาพต้องไม่เกิน 15 MB']);
    exit;
}

try {
    $conn = DbUtils::get_hosxp_connection();

    $stmt = $conn->prepare("INSERT INTO prs_image_annot
                                (an, title, doc_group, original_name, image_type,
                                 image_data, canvas_json, annotated_image,
                                 form_data, form_note, created_by)
                            VALUES
                                (:an, :title, :doc_group, :original_name, :image_type,
                                 :image_data, :canvas_json, :annotated_image,
                                 :form_data, :form_note, :created_by)");

    $stmt->bindParam(':an',              $an);
    $stmt->bindParam(':title',           $title);
    $stmt->bindParam(':doc_group',       $doc_group);
    $stmt->bindParam(':original_name',   $original_name);
    $stmt->bindParam(':image_type',      $detected_mime);
    $stmt->bindParam(':image_data',      $image_bin,     PDO::PARAM_LOB);
    $stmt->bindParam(':canvas_json',     $canvas_json);
    $stmt->bindParam(':annotated_image', $annotated_bin, PDO::PARAM_LOB);
    $stmt->bindParam(':form_data',       $form_data);
    $stmt->bindParam(':form_note',       $form_note);
    $stmt->bindParam(':created_by',      $loginname);
    $stmt->execute();

    $new_id = $conn->lastInsertId();

    Session::insertSystemAccessLog(json_encode([
        'form' => 'IMAGE-ANNOT', 'action' => 'SAVE', 'an' => $an, 'id' => $new_id,
    ], JSON_UNESCAPED_UNICODE));

    echo json_encode(['status' => 'success', 'id' => $new_id]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
