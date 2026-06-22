<?php
require_once '../include/Session.php';
require_once '../include/session-sso.php';
require_once '../include/DbUtils.php';

date_default_timezone_set('Asia/Bangkok');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status'=>'error','message'=>'Invalid request method']); exit;
}
$loginname = isset($_SESSION['loginname']) ? $_SESSION['loginname'] : '';
if (!$loginname) {
    echo json_encode(['status'=>'error','message'=>'Session expired']); exit;
}

$an          = trim($_POST['an']          ?? '');
$note        = trim($_POST['note']        ?? '');
$combinedB64 = $_POST['combinedB64']      ?? '';
$images      = json_decode($_POST['images'] ?? '[]', true);

if (!$an || !is_array($images) || count($images) === 0) {
    echo json_encode(['status'=>'error','message'=>'ข้อมูลไม่ครบถ้วน']); exit;
}

// ---- ถอด base64 → binary ----
function decodeB64(?string $b64): ?string {
    if (!$b64) return null;
    $parts = explode(',', $b64, 2);
    $data  = count($parts) === 2 ? $parts[1] : $parts[0];
    $bin   = base64_decode($data, true);
    return ($bin && strlen($bin) > 0) ? $bin : null;
}

function detectMime(string $bin): string {
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    return $finfo->buffer($bin) ?: 'image/png';
}

$allowed = ['image/png','image/jpeg','image/gif','image/webp'];

try {
    $conn = DbUtils::get_hosxp_connection();
    $conn->beginTransaction();

    // decode combined PNG
    $combined_bin = decodeB64($combinedB64);

    // INSERT master (พร้อม combined_data)
    $stmt_m = $conn->prepare("INSERT INTO prs_image_annot (an, note, combined_data, created_by)
                               VALUES (:an, :note, :combined_data, :created_by)");
    $stmt_m->bindParam(':an',            $an);
    $stmt_m->bindParam(':note',          $note);
    $stmt_m->bindParam(':combined_data', $combined_bin, PDO::PARAM_LOB);
    $stmt_m->bindParam(':created_by',    $loginname);
    $stmt_m->execute();
    $annot_id = $conn->lastInsertId();

    // INSERT items
    $stmt_item = $conn->prepare("INSERT INTO prs_image_annot_item
                                     (annot_id, an, sort_order, image_data, annotated_data,
                                      image_type, original_name, canvas_w, canvas_h, svg_data)
                                 VALUES
                                     (:annot_id, :an, :sort_order, :image_data, :annotated_data,
                                      :image_type, :original_name, :canvas_w, :canvas_h, :svg_data)");

    foreach ($images as $img) {
        $bin  = decodeB64($img['b64'] ?? '');
        if (!$bin) continue;

        $mime = detectMime($bin);
        if (!in_array($mime, $allowed)) continue;
        if (strlen($bin) > 10 * 1024 * 1024) continue;

        $sort           = (int)($img['sort_order'] ?? 0);
        $name           = substr(trim($img['name'] ?? ''), 0, 255);
        $svg_data       = $img['svgData']  ?? '';
        $canvas_w       = (int)($img['canvasW'] ?? 0);
        $canvas_h       = (int)($img['canvasH'] ?? 0);
        $annotated_bin  = decodeB64($img['annotatedB64'] ?? '');

        $stmt_item->bindParam(':annot_id',      $annot_id,      PDO::PARAM_INT);
        $stmt_item->bindParam(':an',            $an);
        $stmt_item->bindParam(':sort_order',    $sort,          PDO::PARAM_INT);
        $stmt_item->bindParam(':image_data',    $bin,           PDO::PARAM_LOB);
        $stmt_item->bindParam(':annotated_data',$annotated_bin, PDO::PARAM_LOB);
        $stmt_item->bindParam(':image_type',    $mime);
        $stmt_item->bindParam(':original_name', $name);
        $stmt_item->bindParam(':canvas_w',      $canvas_w,      PDO::PARAM_INT);
        $stmt_item->bindParam(':canvas_h',      $canvas_h,      PDO::PARAM_INT);
        $stmt_item->bindParam(':svg_data',      $svg_data);
        $stmt_item->execute();
    }

    $conn->commit();

    Session::insertSystemAccessLog(json_encode([
        'form'=>'IMAGE-ANNOT','action'=>'SAVE','an'=>$an,'id'=>$annot_id,
    ], JSON_UNESCAPED_UNICODE));

    echo json_encode(['status'=>'success','id'=>$annot_id]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}

