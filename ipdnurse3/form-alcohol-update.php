<?php
require_once '../include/Session.php';
require_once '../include/DbUtils.php';

date_default_timezone_set('Asia/Bangkok');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$conn = DbUtils::get_hosxp_connection();

function auditEvaluationMessage(int $sum_score): string {
    if ($sum_score <= 7)      return 'ประเมินปัญหาการดื่มสุรา [ผู้ดื่มแบบเสี่ยงต่ำ] ' . $sum_score . ' คะแนน — Low risk drinker';
    elseif ($sum_score <= 15) return 'ประเมินปัญหาการดื่มสุรา [ผู้ดื่มแบบเสี่ยง] ' . $sum_score . ' คะแนน — Hazardous drinker';
    elseif ($sum_score <= 20) return 'ประเมินปัญหาการดื่มสุรา [ผู้ดื่มแบบอันตราย] ' . $sum_score . ' คะแนน — Harmful use';
    else                      return 'ประเมินปัญหาการดื่มสุรา [ผู่ดื่มแบบติด] ' . $sum_score . ' คะแนน — Alcohol dependence';
}

try {
    $conn->beginTransaction();

    $id         = (int)$_POST['id'];
    $an         = trim($_POST['an']);
    $sum_score  = isset($_POST['sum_score'])  ? (int)$_POST['sum_score']  : 0;
    $audit_date = isset($_POST['audit_date']) ? $_POST['audit_date']       : date('Y-m-d');
    $loginname  = isset($_SESSION['loginname']) ? $_SESSION['loginname']   : '';
    $now        = date('Y-m-d H:i:s');

    // -- 1. UPDATE prs_alcohol --
    $conn->prepare("UPDATE prs_alcohol
                       SET sum_score  = :sum_score,
                           audit_date = :audit_date,
                           audit_by   = :audit_by,
                           updated_by = :updated_by
                     WHERE id = :id AND an = :an")
         ->execute([
            'sum_score'  => $sum_score,
            'audit_date' => $audit_date,
            'audit_by'   => $loginname,
            'updated_by' => $loginname,
            'id'         => $id,
            'an'         => $an,
         ]);

    // -- 2. DELETE old items then INSERT prs_alcohol_item --
    $conn->prepare("DELETE FROM prs_alcohol_item WHERE alcohol_id = :alcohol_id")
         ->execute(['alcohol_id' => $id]);

    if (isset($_POST['q']) && is_array($_POST['q'])) {
        $stmt_item = $conn->prepare("INSERT INTO prs_alcohol_item
                                         (alcohol_id, content_index, total_score, remark)
                                     VALUES (:alcohol_id, :content_index, :total_score, :remark)");
        foreach ($_POST['q'] as $qn => $qdata) {
            $stmt_item->execute([
                'alcohol_id'    => $id,
                'content_index' => (int)$qn,
                'total_score'   => isset($qdata['score'])  ? (int)$qdata['score']  : 0,
                'remark'        => isset($qdata['remark']) ? trim($qdata['remark']) : '',
            ]);
        }
    }

    // -- 3. ipd_progress_note + ipd_progress_note_item --
    $evaluation_message = auditEvaluationMessage($sum_score);

    $stmt_cn = $conn->prepare("SELECT COUNT(*) FROM " . DbConstant::KPHIS_DBNAME . ".ipd_progress_note_item
                                WHERE an = :an AND progress_note_item_detail = :detail");
    $stmt_cn->execute(['an' => $an, 'detail' => $evaluation_message]);

    if ($stmt_cn->fetchColumn() == 0) {
        $groupname  = isset($_SESSION['groupname'])  ? $_SESSION['groupname']  : '';
        $owner_type = (strpos($groupname, 'doctor') !== false) ? 'doctor' : 'nurse';
        $doctor     = isset($_SESSION['doctorcode']) ? $_SESSION['doctorcode'] : null;

        $stmt_pn = $conn->prepare("INSERT INTO " . DbConstant::KPHIS_DBNAME . ".ipd_progress_note
                (an, progress_note_date, progress_note_time,
                 progress_note_owner_type, progress_note_doctor,
                 create_user, create_datetime, update_user, update_datetime, version)
                VALUES (:an, :pn_date, :pn_time, :owner_type, :doctor,
                        :create_user, :create_datetime, :update_user, :update_datetime, :version)");
        $stmt_pn->execute([
            'an'              => $an,
            'pn_date'         => date('Y-m-d'),
            'pn_time'         => date('H:i:s'),
            'owner_type'      => $owner_type,
            'doctor'          => $doctor,
            'create_user'     => $loginname,
            'create_datetime' => $now,
            'update_user'     => $loginname,
            'update_datetime' => $now,
            'version'         => 1,
        ]);
        $progress_note_id = $conn->lastInsertId();

        $conn->prepare("INSERT INTO " . DbConstant::KPHIS_DBNAME . ".ipd_progress_note_item
                (progress_note_id, an, progress_note_item_type, progress_note_item_detail,
                 create_user, create_datetime, update_user, update_datetime, version)
                VALUES (:progress_note_id, :an, 'note', :detail,
                        :create_user, :create_datetime, :update_user, :update_datetime, :version)")
             ->execute([
                'progress_note_id' => $progress_note_id,
                'an'               => $an,
                'detail'           => $evaluation_message,
                'create_user'      => $loginname,
                'create_datetime'  => $now,
                'update_user'      => $loginname,
                'update_datetime'  => $now,
                'version'          => 1,
             ]);
    }

    $conn->commit();

    Session::insertSystemAccessLog(json_encode([
        'form'   => 'ALCOHOL-FORM',
        'action' => 'UPDATE',
        'an'     => $an,
        'score'  => $sum_score,
    ], JSON_UNESCAPED_UNICODE));

    echo json_encode(['status' => 'success', 'id' => $id]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
