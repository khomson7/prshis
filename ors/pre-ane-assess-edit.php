<?php
require_once '../include/Session.php';
Session::checkLoginSessionAndShowMessage();
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';

$conn = DbUtils::get_hosxp_connection();
$an = $_REQUEST['an'];
$id = $_REQUEST['id'];

$sql = "SELECT * FROM " . DbConstant::KPHIS_DBNAME . ".prs_pre_ane_assess WHERE an = :an AND id = :id AND (is_deleted = 0 OR is_deleted IS NULL)";
$stmt = $conn->prepare($sql);
$stmt->execute(['an' => $an, 'id' => $id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) { echo '<script>alert("ไม่พบข้อมูล");</script>'; return; }

// Checkbox fields
$cb = [
    'underlying_dm','underlying_ht','underlying_dlp','underlying_asthma','underlying_heart','underlying_other',
    'prev_surgery_ga','prev_surgery_ra','prev_complication',
    'general_normal','general_pale','general_jaundice','general_dry_mouth','general_fatigue','airway_unable',
    'lab_other_check','consult_med','consult_anesth',
    'plan_ga','plan_ra','plan_ra_ga','plan_mac','plan_tiva','plan_icu',
    'spec_a_line','spec_cvp','spec_pca','spec_dlt','spec_fiberoptic','spec_other',
    'act_check_identity','act_assess_asa','act_explain_anes','act_plan_anes','act_check_consent',
    'act_teach_breathe','act_prepare_body','act_pain_advice',
    'preop_talk','preop_vital_sign','preop_check_identity','preop_check_fluid','preop_no_infiltrate',
    'preop_comfort','preop_o2_mask','preop_3way','preop_other',
    'result_got_surgery','result_postpone','result_postpone_reason1','result_postpone_reason2',
    'result_postpone_reason3','result_postpone_reason4','result_postpone_reason5'
];

// Text/select fields
$text = [
    'visit_type','visit_place','ward','operation_date','activity','fc',
    'underlying_dm_tx','underlying_ht_tx','underlying_asthma_tx','underlying_heart_tx','underlying_other_text',
    'alcohol','alcohol_year','smoking','smoking_year','smoking_per_day',
    'allergy','allergy_detail','prev_surgery','prev_surgery_date','prev_complication_text','medication_history',
    'pe_bw','pe_ht','pe_bmi','pe_temp','pe_pr','pe_rr','pe_bp','pe_spo2',
    'consciousness','airway_mouth','airway_mouth_detail','mallampati','on_ett_no','thyromental',
    'denture','heart_lungs','heart_lungs_detail','motor_power','motor_power_detail',
    'other_exam','other_exam_detail',
    'lab_status','lab_hct','lab_hb','lab_plt','lab_other_cbc',
    'lab_fbs','lab_bun','lab_cr','lab_pt','lab_ptt','lab_inr',
    'lab_electrolyte_check','lab_na','lab_k','lab_cl','lab_hco3',
    'lab_ua_check','lab_sp_gr','lab_prot','lab_sugar',
    'lab_cxr_check','lab_cxr_result','lab_cxr_detail',
    'lab_ekg_check','lab_ekg_result','lab_ekg_detail','lab_other_detail',
    'blood_reserve','blood_wb','blood_prc','blood_ffp','blood_plt_unit','blood_cryo','blood_others',
    'consent_signed','asa_class',
    'problem_list_status','problem_1','problem_2','problem_3','problem_4','problem_5',
    'problem_6','problem_7','problem_8','problem_9','problem_10',
    'treatment_1','treatment_2','treatment_3','treatment_4',
    'consult_status','premed_1','premed_2','premed_3','premed_4','premed_5',
    'spec_other_text','act_advice','act_npo_time','act_npo_denture','act_other_5',
    'preop_arrival_time','preop_check_surgery','preop_other_text','preop_assess_by',
    'result_postpone_reason6','diagnosis','operation_plan','visit_date','visitor_name','equip_staff','attending_physician'
];
?>
<script>
$("#pre_ane_form").each(function() {
    $("input[name=version]").val(<?=json_encode($row['version'])?>);
    <?php foreach ($cb as $f): ?>
    if(<?=json_encode($row[$f])?> == "1"){ $("#<?=$f?>").prop('checked',true); }
    <?php endforeach; ?>
    <?php foreach ($text as $f): ?>
    <?php $v = $row[$f]; if ($v !== null && $v !== ''): ?>
    <?php if (in_array($f, ['medication_history','act_advice','diagnosis','operation_plan'])): ?>
    $("textarea[name=<?=$f?>]").val(<?=json_encode($v)?>);
    <?php else: ?>
    $("input[name=<?=$f?>]:not(:radio), select[name=<?=$f?>]").val(<?=json_encode($v)?>).trigger('change');
    <?php // For radio buttons ?>
    $("input[name=<?=$f?>][type=radio]").filter(function() { return this.value === String(<?=json_encode($v)?>); }).prop('checked', true).trigger('change');
    <?php endif; ?>
    <?php endif; ?>
    <?php endforeach; ?>
});
</script>
