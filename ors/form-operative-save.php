<?php
require_once '../include/Session.php';
require_once '../include/session-sso.php';
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
    if (!Session::checkPermission('OPNOTE', 'ADD')) {
        throw new Exception('ไม่มีสิทธิ์ในการเพิ่มข้อมูล');
    }

    $groupname = isset($_SESSION['groupname']) ? $_SESSION['groupname'] : '';
    $is_doctor = (mb_strpos($groupname, 'แพทย์') !== false);
    $update_user_val = $is_doctor ? $loginname : null;

    $conn = DbUtils::get_hosxp_connection();
    $conn->beginTransaction();

    $an = $_POST['an'] ?? '';
    $hn = $_POST['hn'] ?? '';
    
    if (empty($an)) throw new Exception('ไม่มีข้อมูล AN');

    $operation_date = !empty($_POST['operation_date']) ? $_POST['operation_date'] : null;
    $time_started = !empty($_POST['time_started']) ? $_POST['time_started'] : null;
    $time_ended = !empty($_POST['time_ended']) ? $_POST['time_ended'] : null;

    $combinedB64 = $_POST['combinedB64'] ?? '';
    $images      = json_decode($_POST['images'] ?? '[]', true);
    
    $surgeon_val = null;
    if (isset($_POST['surgeon'])) {
        if (is_array($_POST['surgeon'])) {
            $surgeon_val = json_encode($_POST['surgeon'], JSON_UNESCAPED_UNICODE);
        } else {
            $surgeon_val = $_POST['surgeon'];
        }
    }

    $combined_bin = decodeB64($combinedB64);

    $stmt = $conn->prepare("INSERT INTO prs_operative_note (
        an, hn, operation_date, time_started, time_ended,
        surgeon, first_assistant, second_assistant, surgical_nurse,
        clinical_diagnosis, post_op_diagnosis, operation_name,
        anesthetic_technique, anesthesiologist, op_position, incision,
        finding, procedure_detail, estimate_blood_loss, urine_output,
        patho_status, wound_type, combined_data, created_by, create_datetime,
        update_user, update_datetime
    ) VALUES (
        :an, :hn, :operation_date, :time_started, :time_ended,
        :surgeon, :first_assistant, :second_assistant, :surgical_nurse,
        :clinical_diagnosis, :post_op_diagnosis, :operation_name,
        :anesthetic_technique, :anesthesiologist, :op_position, :incision,
        :finding, :procedure_detail, :estimate_blood_loss, :urine_output,
        :patho_status, :wound_type, :combined_data, :created_by, NOW(),
        :update_user, NOW()
    )");

    $stmt->bindParam(':an', $an);
    $stmt->bindParam(':hn', $hn);
    $stmt->bindParam(':operation_date', $operation_date);
    $stmt->bindParam(':time_started', $time_started);
    $stmt->bindParam(':time_ended', $time_ended);
    $stmt->bindValue(':surgeon', $surgeon_val);
    $stmt->bindValue(':first_assistant', $_POST['first_assistant'] ?? null);
    $stmt->bindValue(':second_assistant', $_POST['second_assistant'] ?? null);
    $stmt->bindValue(':surgical_nurse', $_POST['surgical_nurse'] ?? null);
    $stmt->bindValue(':clinical_diagnosis', $_POST['clinical_diagnosis'] ?? null);
    $stmt->bindValue(':post_op_diagnosis', $_POST['post_op_diagnosis'] ?? null);
    $stmt->bindValue(':operation_name', $_POST['operation_name'] ?? null);
    $stmt->bindValue(':anesthetic_technique', $_POST['anesthetic_technique'] ?? null);
    $stmt->bindValue(':anesthesiologist', $_POST['anesthesiologist'] ?? null);
    $stmt->bindValue(':op_position', $_POST['op_position'] ?? null);
    $stmt->bindValue(':incision', $_POST['incision'] ?? null);
    $stmt->bindValue(':finding', $_POST['finding'] ?? null);
    $stmt->bindValue(':procedure_detail', $_POST['procedure_detail'] ?? null);
    $stmt->bindValue(':estimate_blood_loss', $_POST['estimate_blood_loss'] ?? null);
    $stmt->bindValue(':urine_output', $_POST['urine_output'] ?? null);
    $stmt->bindValue(':patho_status', $_POST['patho_status'] ?? null);
    $stmt->bindValue(':wound_type', $_POST['wound_type'] ?? null);
    $stmt->bindParam(':combined_data', $combined_bin, PDO::PARAM_LOB);
    $stmt->bindParam(':created_by', $loginname);
    $stmt->bindValue(':update_user', $update_user_val);

    $stmt->execute();
    $insert_id = $conn->lastInsertId();

    if (is_array($images) && count($images) > 0) {
        $stmt_item = $conn->prepare("INSERT INTO prs_operative_note_item
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

            $stmt_item->bindParam(':annot_id',      $insert_id,     PDO::PARAM_INT);
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
    }

    Session::insertSystemAccessLog(json_encode([
        'form' => 'OPERATIVE-NOTE-SAVE',
        'an'   => $an,
        'id'   => $insert_id
    ], JSON_UNESCAPED_UNICODE));

    $conn->commit();
    echo json_encode(['success' => true, 'id' => $insert_id]);

} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

