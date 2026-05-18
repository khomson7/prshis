<?php
require_once '../include/Session.php';
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

$id          = (int)($_POST['id']          ?? 0);
$an          = trim($_POST['an']           ?? '');
$note        = trim($_POST['note']         ?? '');
$combinedB64 = $_POST['combinedB64']       ?? '';
$images      = json_decode($_POST['images'] ?? '[]', true);
$now         = date('Y-m-d H:i:s');

if (!$id || !$an || !is_array($images)) {
    echo json_encode(['status'=>'error','message'=>'ข้อมูลไม่ครบถ้วน']); exit;
}

$combined_bin = null;
if ($combinedB64) {
    $parts = explode(',', $combinedB64, 2);
    $b64data = count($parts) === 2 ? $parts[1] : $parts[0];
    $decoded = base64_decode($b64data, true);
    if ($decoded && strlen($decoded) > 0) $combined_bin = $decoded;
}

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

    // ตรวจสิทธิ์: อนุญาตเฉพาะผู้บันทึกเท่านั้น
    $stmt_owner = $conn->prepare("SELECT created_by FROM prs_opnote WHERE id = :id AND an = :an AND is_deleted = 0");
    $stmt_owner->execute(['id' => $id, 'an' => $an]);
    $owner = $stmt_owner->fetchColumn();
    if ($owner === false) {
        echo json_encode(['status'=>'error','message'=>'ไม่พบรายการ']); exit;
    }
    if ($owner !== $loginname) {
        echo json_encode(['status'=>'error','message'=>'ไม่มีสิทธิ์แก้ไข — เฉพาะผู้บันทึก (' . $owner . ') เท่านั้น']); exit;
    }

    $conn->beginTransaction();

    // UPDATE master
    $stmt_m = $conn->prepare("UPDATE prs_opnote
                                 SET note = :note, combined_data = :combined_data,
                                     updated_by = :updated_by, updated_at = :updated_at
                               WHERE id = :id AND an = :an");
    $stmt_m->bindParam(':note',         $note);
    $stmt_m->bindParam(':combined_data',$combined_bin, PDO::PARAM_LOB);
    $stmt_m->bindParam(':updated_by',   $loginname);
    $stmt_m->bindParam(':updated_at',   $now);
    $stmt_m->bindParam(':id',           $id,  PDO::PARAM_INT);
    $stmt_m->bindParam(':an',           $an);
    $stmt_m->execute();

    // ดึง existing item IDs
    $stmt_ex = $conn->prepare("SELECT id FROM prs_opnote_item WHERE annot_id = :annot_id");
    $stmt_ex->execute(['annot_id' => $id]);
    $existingIds = $stmt_ex->fetchAll(PDO::FETCH_COLUMN);

    // แยก items ที่มี itemId (update) และ itemId = null (insert ใหม่)
    $keepIds   = [];
    $stmt_upd  = $conn->prepare("UPDATE prs_opnote_item
                                    SET sort_order = :sort_order, svg_data = :svg_data,
                                        annotated_data = :annotated_data,
                                        canvas_w = :canvas_w, canvas_h = :canvas_h
                                  WHERE id = :item_id AND annot_id = :annot_id");
    $stmt_ins  = $conn->prepare("INSERT INTO prs_opnote_item
                                     (annot_id, an, sort_order, image_data, annotated_data,
                                      image_type, original_name, canvas_w, canvas_h, svg_data)
                                 VALUES
                                     (:annot_id, :an, :sort_order, :image_data, :annotated_data,
                                      :image_type, :original_name, :canvas_w, :canvas_h, :svg_data)");

    foreach ($images as $img) {
        $sort     = (int)($img['sort_order'] ?? 0);
        $svg_data = $img['svgData'] ?? '';
        $itemId   = isset($img['itemId']) && $img['itemId'] ? (int)$img['itemId'] : null;

        $annotated_bin = decodeB64($img['annotatedB64'] ?? '');
        $canvas_w      = (int)($img['canvasW'] ?? 0);
        $canvas_h      = (int)($img['canvasH'] ?? 0);

        if ($itemId && in_array($itemId, $existingIds)) {
            // UPDATE sort_order + svg_data + annotated_data + canvas size (ภาพต้นฉบับไม่เปลี่ยน)
            $stmt_upd->bindParam(':sort_order',    $sort,          PDO::PARAM_INT);
            $stmt_upd->bindParam(':svg_data',      $svg_data);
            $stmt_upd->bindParam(':annotated_data',$annotated_bin, PDO::PARAM_LOB);
            $stmt_upd->bindParam(':canvas_w',      $canvas_w,      PDO::PARAM_INT);
            $stmt_upd->bindParam(':canvas_h',      $canvas_h,      PDO::PARAM_INT);
            $stmt_upd->bindParam(':item_id',       $itemId,        PDO::PARAM_INT);
            $stmt_upd->bindParam(':annot_id',      $id,            PDO::PARAM_INT);
            $stmt_upd->execute();
            $keepIds[] = $itemId;
        } else {
            // INSERT ภาพใหม่
            $bin = decodeB64($img['b64'] ?? '');
            if (!$bin) continue;
            $mime = detectMime($bin);
            if (!in_array($mime, $allowed)) continue;
            if (strlen($bin) > 10 * 1024 * 1024) continue;

            $name = substr(trim($img['name'] ?? ''), 0, 255);
            $stmt_ins->bindParam(':annot_id',      $id,            PDO::PARAM_INT);
            $stmt_ins->bindParam(':an',             $an);
            $stmt_ins->bindParam(':sort_order',     $sort,          PDO::PARAM_INT);
            $stmt_ins->bindParam(':image_data',     $bin,           PDO::PARAM_LOB);
            $stmt_ins->bindParam(':annotated_data', $annotated_bin, PDO::PARAM_LOB);
            $stmt_ins->bindParam(':image_type',     $mime);
            $stmt_ins->bindParam(':original_name',  $name);
            $stmt_ins->bindParam(':canvas_w',       $canvas_w,      PDO::PARAM_INT);
            $stmt_ins->bindParam(':canvas_h',       $canvas_h,      PDO::PARAM_INT);
            $stmt_ins->bindParam(':svg_data',       $svg_data);
            $stmt_ins->execute();
            $keepIds[] = (int)$conn->lastInsertId();
        }
    }

    // DELETE items ที่ถูกลบออกจาก imageList
    $deleteIds = array_diff($existingIds, $keepIds);
    if (!empty($deleteIds)) {
        $placeholders = implode(',', array_fill(0, count($deleteIds), '?'));
        $stmt_del = $conn->prepare("DELETE FROM prs_opnote_item WHERE id IN ($placeholders) AND annot_id = ?");
        $params   = array_merge(array_values($deleteIds), [$id]);
        $stmt_del->execute($params);
    }

    $conn->commit();

    Session::insertSystemAccessLog(json_encode([
        'form'=>'IMAGE-ANNOT','action'=>'UPDATE','an'=>$an,'id'=>$id,
    ], JSON_UNESCAPED_UNICODE));

    echo json_encode(['status'=>'success','id'=>$id]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
