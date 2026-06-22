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

$an        = trim($_POST['an']        ?? '');
$doc_name  = trim($_POST['doc_name']  ?? '');
$doc_group = trim($_POST['doc_group'] ?? '');
$now       = date('Y-m-d H:i:s');

if (!$an || !$doc_name) {
    echo json_encode(['status' => 'error', 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
    exit;
}

// ---- ตรวจสอบไฟล์ ----
if (empty($_FILES['pdf_file']['name'])) {
    echo json_encode(['status' => 'error', 'message' => 'กรุณาเลือกไฟล์ PDF']);
    exit;
}

$file = $_FILES['pdf_file'];
if (isset($file['error']) && $file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'อัปโหลดล้มเหลว รหัสข้อผิดพลาด: ' . $file['error']]);
    exit;
}

$file_ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$mime_type = @mime_content_type($file['tmp_name']);
if (empty($mime_type)) {
    $mime_type = $file['type'] ?? '';
}

$is_pdf = false;
$magic = '';
if ($file_ext === 'pdf') {
    if (in_array($mime_type, ['application/pdf', 'application/x-pdf', 'application/acrobat', 'applications/vnd.pdf', 'text/pdf', 'text/x-pdf', 'application/octet-stream'])) {
        $is_pdf = true;
    } elseif (strpos((string)$mime_type, 'pdf') !== false) {
        $is_pdf = true;
    } else {
        // Fallback check magic bytes
        $handle = @fopen($file['tmp_name'], 'r');
        if ($handle) {
            $magic = fread($handle, 10);
            fclose($handle);
            if (strpos($magic, '%PDF') !== false) {
                $is_pdf = true;
            }
        }
    }
}

if (!$is_pdf) {
    $debug = "ext=$file_ext, mime=$mime_type, magic=" . bin2hex($magic) . ", err=" . ($file['error'] ?? 'none');
    echo json_encode(['status' => 'error', 'message' => 'ผิดพลาด รองรับเฉพาะไฟล์ PDF เท่านั้น (' . $debug . ')']);
    exit;
}

if ($file['size'] > 20 * 1024 * 1024) {
    echo json_encode(['status' => 'error', 'message' => 'ขนาดไฟล์ต้องไม่เกิน 20 MB']);
    exit;
}

// ---- อ่าน binary content ----
$file_data = file_get_contents($file['tmp_name']);
if ($file_data === false) {
    echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถอ่านไฟล์ได้']);
    exit;
}

// ---- INSERT ฐานข้อมูล ----
try {
    $conn = DbUtils::get_kphis_log_db_connection();

    $stmt = $conn->prepare("INSERT INTO prs_pdf_upload
                                (an, doc_name, doc_group, original_name, file_size, file_data,
                                 upload_at, upload_by, is_deleted)
                            VALUES
                                (:an, :doc_name, :doc_group, :original_name, :file_size, :file_data,
                                 :upload_at, :upload_by, 0)");

    $stmt->bindParam(':an',            $an);
    $stmt->bindParam(':doc_name',      $doc_name);
    $stmt->bindParam(':doc_group',     $doc_group);
    $stmt->bindParam(':original_name', $file['name']);
    $stmt->bindParam(':file_size',     $file['size'], PDO::PARAM_INT);
    $stmt->bindParam(':file_data',     $file_data,    PDO::PARAM_LOB);
    $stmt->bindParam(':upload_at',     $now);
    $stmt->bindParam(':upload_by',     $loginname);
    $stmt->execute();

    $new_id = $conn->lastInsertId();

    Session::insertSystemAccessLog(json_encode([
        'form'   => 'PDF-UPLOAD',
        'action' => 'SAVE',
        'an'     => $an,
        'id'     => $new_id,
    ], JSON_UNESCAPED_UNICODE));

    echo json_encode(['status' => 'success', 'id' => $new_id]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
