<?php
ob_start();
require_once '../include/Session.php';
require_once '../include/session-sso.php';
require_once '../include/DbUtils.php';

date_default_timezone_set('Asia/Bangkok');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('status' => 'error', 'message' => 'Invalid request method')); exit;
}
$loginname = isset($_SESSION['loginname']) ? $_SESSION['loginname'] : '';
if (!$loginname) {
    ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('status' => 'error', 'message' => 'Session expired')); exit;
}

function p($key, $default = null) {
    return (isset($_POST[$key]) && $_POST[$key] !== '') ? $_POST[$key] : $default;
}
function pint($key, $default = null) {
    return (isset($_POST[$key]) && $_POST[$key] !== '') ? (int)$_POST[$key] : $default;
}
function pf($key, $default = null) {
    return (isset($_POST[$key]) && $_POST[$key] !== '') ? (float)$_POST[$key] : $default;
}
function pb($key) {
    return (isset($_POST[$key]) && $_POST[$key] == '1') ? 1 : 0;
}

try {
    $conn = DbUtils::get_hosxp_connection();
    $conn->beginTransaction();

    $an = trim(isset($_POST['an']) ? $_POST['an'] : '');

    // map wound_condition radio → boolean fields เดิม
    $wc = isset($_POST['wound_condition']) ? trim($_POST['wound_condition']) : '';
    $_POST['wound_dry'] = ($wc === 'แผลแห้ง') ? '1' : '0';
    $_POST['wound_wet'] = ($wc === 'แผลซึม')  ? '1' : '0';
    $_POST['not_wound'] = ($wc === 'ไม่มีแผล') ? '1' : '0';

    $sql = "INSERT INTO prs_ors_nursing_focus (
                an, visit_date, shift, visit_time,
                patient_visit_date, patient_visit_time,
                anes_ga, anes_tiva, anes_ra, anes_mac, anes_other,
                wound_right, wound_left, wound_dry, wound_wet, not_wound, post_op_note,
                in_crystalloid_io, in_colloid_io, in_prc_io, in_ffp_io, in_other_io,
                in_crystalloid_pacu, in_colloid_pacu, in_prc_pacu, in_ffp_pacu, in_other_pacu,
                out_bloodloss_io, out_drain_io, out_urine_io, out_other_io,
                out_bloodloss_pacu, out_drain_pacu, out_urine_pacu, out_other_pacu,
                aldrete_score, pain_score, sedation_score,
                resp_room_air, resp_o2_with,
                discharge_to, transfer_by, assess_note,
                no_complication, has_complication, complication_detail,
                focus_text, remark,
                visit_nurse, nurse_position,
                created_name, created_position, created_by
            ) VALUES (
                :an, :visit_date, :shift, :visit_time,
                :patient_visit_date, :patient_visit_time,
                :anes_ga, :anes_tiva, :anes_ra, :anes_mac, :anes_other,
                :wound_right, :wound_left, :wound_dry, :wound_wet, :not_wound, :post_op_note,
                :in_crystalloid_io, :in_colloid_io, :in_prc_io, :in_ffp_io, :in_other_io,
                :in_crystalloid_pacu, :in_colloid_pacu, :in_prc_pacu, :in_ffp_pacu, :in_other_pacu,
                :out_bloodloss_io, :out_drain_io, :out_urine_io, :out_other_io,
                :out_bloodloss_pacu, :out_drain_pacu, :out_urine_pacu, :out_other_pacu,
                :aldrete_score, :pain_score, :sedation_score,
                :resp_room_air, :resp_o2_with,
                :discharge_to, :transfer_by, :assess_note,
                :no_complication, :has_complication, :complication_detail,
                :focus_text, :remark,
                :visit_nurse, :nurse_position,
                :created_name, :created_position, :created_by
            )";

    $stmt = $conn->prepare($sql);
    $stmt->execute(array(
        'an'                  => $an,
        'visit_date'          => p('visit_date', date('Y-m-d')),
        'shift'               => p('shift'),
        'visit_time'          => p('visit_time'),
        'patient_visit_date'  => p('patient_visit_date'),
        'patient_visit_time'  => p('patient_visit_time'),
        'anes_ga'             => pb('anes_ga'),
        'anes_tiva'           => pb('anes_tiva'),
        'anes_ra'             => pb('anes_ra'),
        'anes_mac'            => pb('anes_mac'),
        'anes_other'          => p('anes_other'),
        'wound_right'         => pb('wound_right'),
        'wound_left'          => pb('wound_left'),
        'wound_dry'           => pb('wound_dry'),
        'wound_wet'           => pb('wound_wet'),
        'not_wound'           => pb('not_wound'),
        'post_op_note'        => p('post_op_note'),
        'in_crystalloid_io'   => pf('in_crystalloid_io'),
        'in_colloid_io'       => pf('in_colloid_io'),
        'in_prc_io'           => pf('in_prc_io'),
        'in_ffp_io'           => pf('in_ffp_io'),
        'in_other_io'         => pf('in_other_io'),
        'in_crystalloid_pacu' => pf('in_crystalloid_pacu'),
        'in_colloid_pacu'     => pf('in_colloid_pacu'),
        'in_prc_pacu'         => pf('in_prc_pacu'),
        'in_ffp_pacu'         => pf('in_ffp_pacu'),
        'in_other_pacu'       => pf('in_other_pacu'),
        'out_bloodloss_io'    => pf('out_bloodloss_io'),
        'out_drain_io'        => pf('out_drain_io'),
        'out_urine_io'        => pf('out_urine_io'),
        'out_other_io'        => pf('out_other_io'),
        'out_bloodloss_pacu'  => pf('out_bloodloss_pacu'),
        'out_drain_pacu'      => pf('out_drain_pacu'),
        'out_urine_pacu'      => pf('out_urine_pacu'),
        'out_other_pacu'      => pf('out_other_pacu'),
        'aldrete_score'       => pint('aldrete_score'),
        'pain_score'          => pint('pain_score'),
        'sedation_score'      => pint('sedation_score'),
        'resp_room_air'       => pb('resp_room_air'),
        'resp_o2_with'        => p('resp_o2_with'),
        'discharge_to'        => p('discharge_to'),
        'transfer_by'         => p('transfer_by'),
        'assess_note'         => p('assess_note'),
        'no_complication'     => pb('no_complication'),
        'has_complication'    => pb('has_complication'),
        'complication_detail' => p('complication_detail'),
        'focus_text'          => p('focus_text'),
        'remark'              => p('remark'),
        'visit_nurse'         => p('visit_nurse'),
        'nurse_position'      => p('nurse_position'),
        'created_name'        => p('created_name', isset($_SESSION['name'])          ? $_SESSION['name']          : $loginname),
        'created_position'    => p('created_position', isset($_SESSION['entryposition']) ? $_SESSION['entryposition'] : ''),
        'created_by'          => $loginname,
    ));
    $new_id = $conn->lastInsertId();

    $conn->commit();

    Session::insertSystemAccessLog(json_encode(array(
        'form'   => 'ORS-NURSING-FOCUS',
        'action' => 'SAVE',
        'an'     => $an,
        'id'     => $new_id,
    ), JSON_UNESCAPED_UNICODE));

    ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('status' => 'success', 'id' => $new_id));

} catch (Exception $e) {
    if (isset($conn)) $conn->rollBack();
    ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
}

