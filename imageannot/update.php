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

$id            = (int)($_POST['id']            ?? 0);
$an            = trim($_POST['an']             ?? '');
$title         = trim($_POST['title']          ?? '');
$doc_group     = trim($_POST['doc_group']      ?? '');
$canvas_json   = $_POST['canvas_json']         ?? '';
$form_data     = $_POST['form_data']           ?? '';
$form_note     = trim($_POST['form_note']      ?? '');
$annotated_b64 = $_POST['annotated_image']     ?? '';
$now           = date('Y-m-d H:i:s');

if (!$id || !$an || !$title) {
    echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
    exit;
}

function decodeBase64Image(string $b64): ?string {
    if (empty($b64)) return null;
    $parts = explode(',', $b64, 2);
    $data  = count($parts) === 2 ? $parts[1] : $parts[0];
    $bin   = base64_decode($data, true);
    return ($bin !== false && strlen($bin) > 0) ? $bin : null;
}

$annotated_bin = decodeBase64Image($annotated_b64);

try {
    $conn = DbUtils::get_hosxp_connection();

    $row = $conn->prepare("SELECT created_by FROM prs_image_annot WHERE id = :id AND an = :an AND is_deleted = 0");
    $row->execute(['id' => $id, 'an' => $an]);
    $rec = $row->fetch();

    if (!$rec) { echo json_encode(['status' => 'error', 'message' => 'ไม่พบข้อมูล']); exit; }
    if ($rec['created_by'] !== $loginname) {
        echo json_encode(['status' => 'error', 'message' => 'สามารถแก้ไขได้เฉพาะเจ้าของเท่านั้น']);
        exit;
    }

    if ($annotated_bin) {
        $stmt = $conn->prepare("UPDATE prs_image_annot
                                   SET title           = :title,
                                       doc_group       = :doc_group,
                                       canvas_json     = :canvas_json,
                                       annotated_image = :annotated_image,
                                       form_data       = :form_data,
                                       form_note       = :form_note,
                                       updated_by      = :updated_by,
                                       updated_at      = :updated_at
                                 WHERE id = :id");
        $stmt->bindParam(':title',           $title);
        $stmt->bindParam(':doc_group',       $doc_group);
        $stmt->bindParam(':canvas_json',     $canvas_json);
        $stmt->bindParam(':annotated_image', $annotated_bin, PDO::PARAM_LOB);
        $stmt->bindParam(':form_data',       $form_data);
        $stmt->bindParam(':form_note',       $form_note);
        $stmt->bindParam(':updated_by',      $loginname);
        $stmt->bindParam(':updated_at',      $now);
        $stmt->bindParam(':id',              $id, PDO::PARAM_INT);
        $stmt->execute();
    } else {
        $conn->prepare("UPDATE prs_image_annot
                           SET title = :title, doc_group = :doc_group,
                               canvas_json = :canvas_json,
                               form_data = :form_data, form_note = :form_note,
                               updated_by = :updated_by, updated_at = :updated_at
                         WHERE id = :id")
             ->execute([
                'title'       => $title,
                'doc_group'   => $doc_group,
                'canvas_json' => $canvas_json,
                'form_data'   => $form_data,
                'form_note'   => $form_note,
                'updated_by'  => $loginname,
                'updated_at'  => $now,
                'id'          => $id,
             ]);
    }

    Session::insertSystemAccessLog(json_encode([
        'form' => 'IMAGE-ANNOT', 'action' => 'UPDATE', 'an' => $an, 'id' => $id,
    ], JSON_UNESCAPED_UNICODE));

    echo json_encode(['status' => 'success', 'id' => $id]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
