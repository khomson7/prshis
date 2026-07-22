<?php
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';
require_once '../include/Session.php';
require_once '../include/session-sso.php';
date_default_timezone_set("Asia/Bangkok");

$conn = DbUtils::get_hosxp_connection();

$an = $_REQUEST['an'];
$id = empty($_REQUEST['id']) ? null : $_REQUEST['id'];
$remark = empty($_REQUEST['remark']) ? null : $_REQUEST['remark'];

$create_datetime = date('Y-m-d H:i:s');
$create_user = $_SESSION['loginname'];
$update_user = $_SESSION['loginname'];
$update_datetime = date('Y-m-d H:i:s');
$version = 1;

header('Content-Type: application/json');

try {
    if ($an != '' && $remark != '') {
        $conn->beginTransaction();
        
        if ($id != '') {
            $stmt_update = $conn->prepare("UPDATE " . DbConstant::KPHIS_DBNAME . ".prs_check_vitalsign SET remark = :remark, update_user = :update_user, update_datetime = :update_datetime WHERE id = :id");
            $stmt_update->execute([
                'remark' => $remark,
                'update_user' => $update_user,
                'update_datetime' => $update_datetime,
                'id' => $id
            ]);
        }

        // 1. Insert into ipd_progress_note (Parent)
        $progress_note_date = date('Y-m-d');
        $progress_note_time = date('H:i:s');
        $groupname = isset($_SESSION['groupname']) ? $_SESSION['groupname'] : '';
        $progress_note_owner_type = 'other'; // Default

        if (strpos($groupname, 'แพทย์') !== false) {
            $progress_note_owner_type = 'doctor';
        } else if (strpos($groupname, 'พยาบาล') !== false) {
            $progress_note_owner_type = 'nurse';
        }
        $progress_note_doctor = isset($_SESSION['doctorcode']) ? $_SESSION['doctorcode'] : null;

        $stmt_pn = $conn->prepare("INSERT INTO " . DbConstant::KPHIS_DBNAME . ".ipd_progress_note
                (an, progress_note_date, progress_note_time, progress_note_owner_type, progress_note_doctor, create_user, create_datetime, update_user, update_datetime, version)
                VALUES (:an, :progress_note_date, :progress_note_time, :progress_note_owner_type, :progress_note_doctor, :create_user, :create_datetime, :update_user, :update_datetime, :version)");
        $stmt_pn->execute([
            'an' => $an,
            'progress_note_date' => $progress_note_date,
            'progress_note_time' => $progress_note_time,
            'progress_note_owner_type' => $progress_note_owner_type,
            'progress_note_doctor' => $progress_note_doctor,
            'create_user' => $create_user,
            'create_datetime' => $create_datetime,
            'update_user' => $create_user,
            'update_datetime' => $create_datetime,
            'version' => 1
        ]);
        $progress_note_id = $conn->lastInsertId();

        // 2. Insert into ipd_progress_note_item (Child)
        $stmt_pni = $conn->prepare("INSERT INTO " . DbConstant::KPHIS_DBNAME . ".ipd_progress_note_item
                (progress_note_id, an, progress_note_item_type, progress_note_item_detail, create_user, create_datetime, update_user, update_datetime, version)
                VALUES (:progress_note_id, :an, 'note', :detail, :create_user, :create_datetime, :update_user, :update_datetime, :version)");
        $stmt_pni->execute([
            'progress_note_id' => $progress_note_id,
            'an' => $an,
            'detail' => $remark,
            'create_user' => $create_user,
            'create_datetime' => $create_datetime,
            'update_user' => $update_user,
            'update_datetime' => $update_datetime,
            'version' => 1
        ]);

        $conn->commit();
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
    }

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>