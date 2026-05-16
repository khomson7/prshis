<?php
require_once '../include/Session.php';
require_once '../include/DbUtils.php';

$loginname = isset($_SESSION['loginname']) ? $_SESSION['loginname'] : '';
if (!$loginname) { http_response_code(403); exit('Unauthorized'); }

$id = (int)($_GET['id'] ?? 0);
$an = trim($_GET['an']  ?? '');

if (!$id || !$an) { http_response_code(400); exit('Bad Request'); }

try {
    $conn = DbUtils::get_kphis_log_db_connection();
    $stmt = $conn->prepare("SELECT original_name, file_size, file_data
                              FROM prs_pdf_upload
                             WHERE id = :id AND an = :an AND is_deleted = 0");
    $stmt->execute(['id' => $id, 'an' => $an]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || $row['file_data'] === null) {
        http_response_code(404);
        exit('Not Found');
    }

    $disposition = isset($_GET['download']) ? 'attachment' : 'inline';
    $safe_name   = rawurlencode($row['original_name'] ?? 'document.pdf');

    header('Content-Type: application/pdf');
    header('Content-Disposition: ' . $disposition . '; filename="' . $safe_name . '"');
    header('Content-Length: ' . strlen($row['file_data']));
    header('Cache-Control: private, max-age=0');

    // ถ้า PDO คืน stream (บางกรณี) ให้ stream_get_contents ก่อน
    if (is_resource($row['file_data'])) {
        echo stream_get_contents($row['file_data']);
    } else {
        echo $row['file_data'];
    }

} catch (Exception $e) {
    http_response_code(500);
    exit('Error');
}
