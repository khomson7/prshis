<?php
    require_once '../include/DbUtils.php';
    require_once '../include/KphisQueryUtils.php';
    require_once '../include/Session.php';

     $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
    // SessionManager::checkLoginSessionAndShowMessage(); //เช็ค session
    /*if(!(
        // && SessionManager::checkPermission('IPD_NURSE_MAIN_PROGRAM','ACCESS')
        // && SessionManager::checkPermission('IPD_DISCHARGE_SUMMARY','ADD')
        // && SessionManager::checkPermission('IPD_DISCHARGE_SUMMARY','EDIT')
        SessionManager::checkPermission('IPD_DISCHARGE_SUMMARY','VIEW')
        // && SessionManager::checkPermission('IPD_DISCHARGE_SUMMARY','REMOVE')
        )){
        return;
    }*/

    $an = $_REQUEST['an'];
    $summary_id = $_REQUEST['summary_id'];
    $query_parameters = [
                            ':summary_id' => $summary_id,
                            ':an' => $an
                        ];
    $sql = "SELECT * FROM ".DbConstant::KPHIS_DBNAME.".ipd_summary WHERE ipd_summary.summary_id = :summary_id AND ipd_summary.an = :an ";
    $stmt = $conn->prepare($sql);
    $stmt->execute($query_parameters);
    $rowCount = 0;
    $row = $stmt->fetch();
        $summary_plan_date = $row['summary_plan_date'];
        $summary_plan_time = $row['summary_plan_time'];
        $principal_diagnosis = $row['principal_diagnosis'];
        $pre_admission_comorbidity = $row['pre_admission_comorbidity'];
        $post_admission_comorbidity = $row['post_admission_comorbidity'];
        $other_diagnosis = $row['other_diagnosis'];
        $external_cause = $row['external_cause'];
        $operating_room = $row['operating_room'];
        $tracheostomy = $row['tracheostomy'];
        $mechanical_ventilation = $row['mechanical_ventilation'];
        $mechanical_ventilation1 = $row['mechanical_ventilation1'];
        $mechanical_ventilation2 = $row['mechanical_ventilation2'];
        $packed_redcells = $row['packed_redcells'];
        $fresh_frozen_plasma = $row['fresh_frozen_plasma'];
        $platelets = $row['platelets'];
        $cryoprecipitate = $row['cryoprecipitate'];
        $whole_blood = $row['whole_blood'];
        $computer_tomography = $row['computer_tomography'];
        $computer_tomography_text = $row['computer_tomography_text'];
        $chemotherapy = $row['chemotherapy'];
        $mri = $row['mri'];
        $hemodialysis = $row['hemodialysis'];
        $non_or_other = $row['non_or_other'];
        $non_or_other_text = $row['non_or_other_text'];
        $discharge_status = $row['discharge_status'];
        $discharge_type = $row['discharge_type'];
        $hospital_refer = $row['hospital_refer'];

        $version = $row['version'];
?>
<script>
    $("#ipd_summary_form").each(function() {
        $("input[name=summary_an]").val(<?=json_encode($an )?>);//ฟิลด์ hidden "an"
        $("input[name=summary_id]").val(<?=json_encode($summary_id )?>);//ฟิลด์ hidden "summary_id"
        $("input[name=summary_version]").val(<?=json_encode($version )?>);//ฟิลด์ hidden "version"

        $("input[name=summary_plan_date]").val(<?=json_encode($summary_plan_date )?>);
        $("input[name=summary_plan_time]").val(<?=json_encode($summary_plan_time )?>);

        $("textarea#principal_diagnosis").val(<?=json_encode($principal_diagnosis)?>);
        $("textarea#pre_admission_comorbidity").val(<?=json_encode($pre_admission_comorbidity)?>);
        $("textarea#post_admission_comorbidity").val(<?=json_encode($post_admission_comorbidity)?>);
        $("textarea#other_diagnosis").val(<?=json_encode($other_diagnosis)?>);
        $("textarea#external_cause").val(<?=json_encode($external_cause)?>);
        $("textarea#operating_room").val(<?=json_encode($operating_room)?>);

        var tracheostomy = <?=json_encode($tracheostomy)?>;
        if(tracheostomy == "Y"){
            $("#tracheostomy").attr('checked',true);
        }

        var mechanical_ventilation = <?=json_encode($mechanical_ventilation)?>;
        if(mechanical_ventilation == "Y"){
            $("#mechanical_ventilation").attr('checked',true);
        }

        var mechanical_ventilation1 = <?=json_encode($mechanical_ventilation1)?>;
        if(mechanical_ventilation1 == "Y"){
            $("#mechanical_ventilation1").attr('checked',true);
        }

        var mechanical_ventilation2 = <?=json_encode($mechanical_ventilation2)?>;
        if(mechanical_ventilation2 == "Y"){
            $("#mechanical_ventilation2").attr('checked',true);
        }

        var packed_redcells = <?=json_encode($packed_redcells)?>;
        if(packed_redcells == "Y"){
            $("#packed_redcells").attr('checked',true);
        }

        var fresh_frozen_plasma = <?=json_encode($fresh_frozen_plasma)?>;
        if(fresh_frozen_plasma == "Y"){
            $("#fresh_frozen_plasma").attr('checked',true);
        }

        var platelets = <?=json_encode($platelets)?>;
        if(platelets == "Y"){
            $("#platelets").attr('checked',true);
        }

        var cryoprecipitate = <?=json_encode($cryoprecipitate)?>;
        if(cryoprecipitate == "Y"){
            $("#cryoprecipitate").attr('checked',true);
        }

        var whole_blood = <?=json_encode($whole_blood)?>;
        if(whole_blood == "Y"){
            $("#whole_blood").attr('checked',true);
        }

        var computer_tomography = <?=json_encode($computer_tomography)?>;
        if(computer_tomography == "Y"){
            $("#computer_tomography").attr('checked',true);
        }
        $("input[name=computer_tomography_text]").val(<?=json_encode($computer_tomography_text)?>);

        var chemotherapy = <?=json_encode($chemotherapy)?>;
        if(chemotherapy == "Y"){
            $("#chemotherapy").attr('checked',true);
        }

        var mri = <?=json_encode($mri)?>;
        if(mri == "Y"){
            $("#mri").attr('checked',true);
        }

        var hemodialysis = <?=json_encode($hemodialysis)?>;
        if(hemodialysis == "Y"){
            $("#hemodialysis").attr('checked',true);
        }

        var non_or_other = <?=json_encode($non_or_other)?>;
        if(non_or_other == "Y"){
            $("#non_or_other").attr('checked',true);
        }
        $("input[name=non_or_other_text]").val(<?=json_encode($non_or_other_text)?>);

        var discharge_status = <?=json_encode($discharge_status)?>;
        if(discharge_status != null || discharge_status!= ''){
            $("#discharge_status"+discharge_status).attr('checked',true);
        }

        var discharge_type = <?=json_encode($discharge_type)?>;
        if(discharge_type != null || discharge_type!= ''){
            $("#discharge_type"+discharge_type).attr('checked',true);
        }

        $("input[name=hospital_refer]").val(<?=json_encode($hospital_refer)?>);
    });
</script>