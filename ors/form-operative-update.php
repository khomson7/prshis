<?php
require_once '../include/Session.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$loginname = isset($_SESSION['loginname']) ? $_SESSION['loginname'] : null;

require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';

header('Content-Type: application/json; charset=utf-8');

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

    $id = $_POST['id'] ?? null;
    $an = $_POST['an'] ?? '';
    
    if (empty($id) || empty($an)) throw new Exception('ข้อมูลไม่ครบถ้วน');

    // ตรวจสิทธิ์ผู้เขียน
    $stmt_owner = $conn->prepare("SELECT created_by FROM prs_operative_note WHERE id = :id AND an = :an AND is_deleted = 0");
    $stmt_owner->execute(['id' => $id, 'an' => $an]);
    $owner = $stmt_owner->fetchColumn();
    if ($owner === false) {
        throw new Exception('ไม่พบรายการข้อมูล');
    }
    if ($owner !== $loginname) {
        throw new Exception('ไม่มีสิทธิ์แก้ไข — เฉพาะผู้บันทึก (' . $owner . ') เท่านั้น');
    }

    $conn->beginTransaction();

    $combinedB64 = $_POST['combinedB64'] ?? '';
    $images      = json_decode($_POST['images'] ?? '[]', true);
    $combined_bin = decodeB64($combinedB64);

    $stmt = $conn->prepare("UPDATE prs_operative_note SET 
        operation_date = :operation_date,
        time_started = :time_started,
        time_ended = :time_ended,
        surgeon = :surgeon,
        first_assistant = :first_assistant,
        second_assistant = :second_assistant,
        surgical_nurse = :surgical_nurse,
        clinical_diagnosis = :clinical_diagnosis,
        post_op_diagnosis = :post_op_diagnosis,
        operation_name = :operation_name,
        anesthetic_technique = :anesthetic_technique,
        anesthesiologist = :anesthesiologist,
        op_position = :op_position,
        incision = :incision,
        finding = :finding,
        procedure_detail = :procedure_detail,
        estimate_blood_loss = :estimate_blood_loss,
        urine_output = :urine_output,
        patho_status = :patho_status,
        wound_type = :wound_type,
        combined_data = :combined_data,
        update_user = :update_user,
        update_datetime = NOW()
    WHERE id = :id AND an = :an");

    $stmt->execute([
        'operation_date' => !empty($_POST['operation_date']) ? $_POST['operation_date'] : null,
        'time_started' => !empty($_POST['time_started']) ? $_POST['time_started'] : null,
        'time_ended' => !empty($_POST['time_ended']) ? $_POST['time_ended'] : null,
        'surgeon' => $_POST['surgeon'] ?? null,
        'first_assistant' => $_POST['first_assistant'] ?? null,
        'second_assistant' => $_POST['second_assistant'] ?? null,
        'surgical_nurse' => $_POST['surgical_nurse'] ?? null,
        'clinical_diagnosis' => $_POST['clinical_diagnosis'] ?? null,
        'post_op_diagnosis' => $_POST['post_op_diagnosis'] ?? null,
        'operation_name' => $_POST['operation_name'] ?? null,
        'anesthetic_technique' => $_POST['anesthetic_technique'] ?? null,
        'anesthesiologist' => $_POST['anesthesiologist'] ?? null,
        'op_position' => $_POST['op_position'] ?? null,
        'incision' => $_POST['incision'] ?? null,
        'finding' => $_POST['finding'] ?? null,
        'procedure_detail' => $_POST['procedure_detail'] ?? null,
        'estimate_blood_loss' => $_POST['estimate_blood_loss'] ?? null,
        'urine_output' => $_POST['urine_output'] ?? null,
        'patho_status' => $_POST['patho_status'] ?? null,
        'wound_type' => $_POST['wound_type'] ?? null,
        'combined_data' => $combined_bin,
        'update_user' => $loginname,
        'id' => $id,
        'an' => $an
    ]);

    // จัดการรูปภาพ (items)
    $stmt_ex = $conn->prepare("SELECT id FROM prs_operative_note_item WHERE annot_id = :annot_id");
    $stmt_ex->execute(['annot_id' => $id]);
    $existingIds = $stmt_ex->fetchAll(PDO::FETCH_COLUMN);

    $keepIds   = [];
    $stmt_upd  = $conn->prepare("UPDATE prs_operative_note_item
                                    SET sort_order = :sort_order, svg_data = :svg_data,
                                        annotated_data = :annotated_data,
                                        canvas_w = :canvas_w, canvas_h = :canvas_h
                                  WHERE id = :item_id AND annot_id = :annot_id");
    $stmt_ins  = $conn->prepare("INSERT INTO prs_operative_note_item
                                     (annot_id, an, sort_order, image_data, annotated_data,
                                      image_type, original_name, canvas_w, canvas_h, svg_data)
                                 VALUES
                                     (:annot_id, :an, :sort_order, :image_data, :annotated_data,
                                      :image_type, :original_name, :canvas_w, :canvas_h, :svg_data)");

    if (is_array($images)) {
        foreach ($images as $img) {
            $sort     = (int)($img['sort_order'] ?? 0);
            $svg_data = $img['svgData'] ?? '';
            $itemId   = isset($img['itemId']) && $img['itemId'] ? (int)$img['itemId'] : null;

            $annotated_bin = decodeB64($img['annotatedB64'] ?? '');
            $canvas_w      = (int)($img['canvasW'] ?? 0);
            $canvas_h      = (int)($img['canvasH'] ?? 0);

            if ($itemId && in_array($itemId, $existingIds)) {
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
                $bin = decodeB64($img['b64'] ?? '');
                if (!$bin) continue;
                $mime = detectMime($bin);
                if (!in_array($mime, $allowed)) continue;
                if (strlen($bin) > 10 * 1024 * 1024) continue;

                $name = substr(trim($img['name'] ?? ''), 0, 255);
                $stmt_ins->bindParam(':annot_id',      $id,            PDO::PARAM_INT);
                $stmt_ins->bindParam(':an',            $an);
                $stmt_ins->bindParam(':sort_order',    $sort,          PDO::PARAM_INT);
                $stmt_ins->bindParam(':image_data',    $bin,           PDO::PARAM_LOB);
                $stmt_ins->bindParam(':annotated_data',$annotated_bin, PDO::PARAM_LOB);
                $stmt_ins->bindParam(':image_type',    $mime);
                $stmt_ins->bindParam(':original_name', $name);
                $stmt_ins->bindParam(':canvas_w',      $canvas_w,      PDO::PARAM_INT);
                $stmt_ins->bindParam(':canvas_h',      $canvas_h,      PDO::PARAM_INT);
                $stmt_ins->bindParam(':svg_data',      $svg_data);
                $stmt_ins->execute();
                $keepIds[] = (int)$conn->lastInsertId();
            }
        }
    }

    $deleteIds = array_diff($existingIds, $keepIds);
    if (!empty($deleteIds)) {
        $placeholders = implode(',', array_fill(0, count($deleteIds), '?'));
        $stmt_del = $conn->prepare("DELETE FROM prs_operative_note_item WHERE id IN ($placeholders) AND annot_id = ?");
        $params   = array_merge(array_values($deleteIds), [$id]);
        $stmt_del->execute($params);
    }

    Session::insertSystemAccessLog(json_encode([
        'form' => 'OPERATIVE-NOTE-UPDATE',
        'an'   => $an,
        'id'   => $id
    ], JSON_UNESCAPED_UNICODE));

    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
