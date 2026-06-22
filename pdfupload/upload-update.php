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

$id        = (int)($_POST['id']       ?? 0);
$an        = trim($_POST['an']        ?? '');
$doc_name  = trim($_POST['doc_name']  ?? '');
$doc_group = trim($_POST['doc_group'] ?? '');
$now       = date('Y-m-d H:i:s');

if (!$id || !$an || !$doc_name) {
    echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
    exit;
}

try {
    $conn = DbUtils::get_kphis_log_db_connection();

    // ดึงข้อมูลเดิม และตรวจสอบว่าเป็นเจ้าของ
    $stmt = $conn->prepare("SELECT id, upload_by FROM prs_pdf_upload WHERE id = :id AND an = :an AND is_deleted = 0");
    $stmt->execute(['id' => $id, 'an' => $an]);
    $row = $stmt->fetch();

    if (!$row) {
        echo json_encode(['status' => 'error', 'message' => 'ไม่พบข้อมูล']);
        exit;
    }

    if ($row['upload_by'] !== $loginname) {
        echo json_encode(['status' => 'error', 'message' => 'สามารถแก้ไขได้เฉพาะเจ้าของไฟล์เท่านั้น']);
        exit;
    }

    // ---- ถ้ามีการ upload ไฟล์ใหม่ ----
    if (!empty($_FILES['pdf_file']['name'])) {
        $file      = $_FILES['pdf_file'];

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

        $file_data = file_get_contents($file['tmp_name']);
        if ($file_data === false) {
            echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถอ่านไฟล์ได้']);
            exit;
        }

        $stmt2 = $conn->prepare("UPDATE prs_pdf_upload
                                    SET doc_name     = :doc_name,
                                        doc_group    = :doc_group,
                                        original_name= :original_name,
                                        file_size    = :file_size,
                                        file_data    = :file_data,
                                        upload_at    = :upload_at,
                                        updated_by   = :updated_by
                                  WHERE id = :id");

        $stmt2->bindParam(':doc_name',      $doc_name);
        $stmt2->bindParam(':doc_group',     $doc_group);
        $stmt2->bindParam(':original_name', $file['name']);
        $stmt2->bindParam(':file_size',     $file['size'],  PDO::PARAM_INT);
        $stmt2->bindParam(':file_data',     $file_data,     PDO::PARAM_LOB);
        $stmt2->bindParam(':upload_at',     $now);
        $stmt2->bindParam(':updated_by',    $loginname);
        $stmt2->bindParam(':id',            $id,            PDO::PARAM_INT);
        $stmt2->execute();

        $replaced = true;
    } else {
        // แก้เฉพาะชื่อ/กลุ่ม ไม่มีไฟล์ใหม่
        $conn->prepare("UPDATE prs_pdf_upload
                           SET doc_name  = :doc_name,
                               doc_group = :doc_group,
                               updated_by= :updated_by
                         WHERE id = :id")
             ->execute([
                'doc_name'   => $doc_name,
                'doc_group'  => $doc_group,
                'updated_by' => $loginname,
                'id'         => $id,
             ]);
        $replaced = false;
    }

    Session::insertSystemAccessLog(json_encode([
        'form'     => 'PDF-UPLOAD',
        'action'   => 'UPDATE',
        'an'       => $an,
        'id'       => $id,
        'replaced' => $replaced,
    ], JSON_UNESCAPED_UNICODE));

    echo json_encode(['status' => 'success', 'id' => $id]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
