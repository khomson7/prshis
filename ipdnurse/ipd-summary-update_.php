<?php

    require_once '../include/Session.php';
    require_once '../include/DbUtils.php';
    require_once '../include/KphisQueryUtils.php';

    $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
    Session::checkLoginSessionAndShowMessage(); //เช็ค session
    /*(!(
        // && SessionManager::checkPermission('IPD_NURSE_MAIN_PROGRAM','ACCESS')
        // && SessionManager::checkPermission('IPD_DISCHARGE_SUMMARY','ADD')
        Session::checkPermission('IPD_DOCTOR_SUMMAR','EDIT')
        // && SessionManager::checkPermission('IPD_DISCHARGE_SUMMARY','VIEW')
        // && SessionManager::checkPermission('IPD_DISCHARGE_SUMMARY','REMOVE')
        )){
        return;
    } 
    */

    $an = $_REQUEST['summary_an'];//รับค่า an
    $hn = KphisQueryUtils::getHnByAn($an);// function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา
    $summary_id = $_REQUEST['summary_id'];
    $output_error = '';

    //check for require field
    if(empty($an)
    || empty($summary_id)
    /*|| empty($_REQUEST['summary_plan_date'])
    || empty($_REQUEST['summary_plan_time'])*/
    ){
        exit;
    }

    $summary_plan_date = /*empty($_REQUEST['summary_plan_date']) ? null : */$_REQUEST['summary_plan_date'];
    $summary_plan_time = empty($_REQUEST['summary_plan_time']) ? null : $_REQUEST['summary_plan_time'];
    $principal_diagnosis = empty($_REQUEST['principal_diagnosis']) ? null : $_REQUEST['principal_diagnosis'];
    $pre_admission_comorbidity = empty($_REQUEST['pre_admission_comorbidity']) ? null : $_REQUEST['pre_admission_comorbidity'];
    $post_admission_comorbidity = empty($_REQUEST['post_admission_comorbidity']) ? null : $_REQUEST['post_admission_comorbidity'];
    $other_diagnosis = empty($_REQUEST['other_diagnosis']) ? null : $_REQUEST['other_diagnosis'];
    $external_cause = empty($_REQUEST['external_cause']) ? null : $_REQUEST['external_cause'];
    $operating_room = empty($_REQUEST['operating_room']) ? null : $_REQUEST['operating_room'];
    $tracheostomy = empty($_REQUEST['tracheostomy']) ? null : $_REQUEST['tracheostomy'];
    $mechanical_ventilation = empty($_REQUEST['mechanical_ventilation']) ? null : $_REQUEST['mechanical_ventilation'];
    $mechanical_ventilation1 = empty($_REQUEST['mechanical_ventilation1']) ? null : $_REQUEST['mechanical_ventilation1'];
    $mechanical_ventilation2 = empty($_REQUEST['mechanical_ventilation2']) ? null : $_REQUEST['mechanical_ventilation2'];
    $packed_redcells = empty($_REQUEST['packed_redcells']) ? null : $_REQUEST['packed_redcells'];
    $fresh_frozen_plasma = empty($_REQUEST['fresh_frozen_plasma']) ? null : $_REQUEST['fresh_frozen_plasma'];
    $platelets = empty($_REQUEST['platelets']) ? null : $_REQUEST['platelets'];
    $cryoprecipitate = empty($_REQUEST['cryoprecipitate']) ? null : $_REQUEST['cryoprecipitate'];
    $whole_blood = empty($_REQUEST['whole_blood']) ? null : $_REQUEST['whole_blood'];
    $computer_tomography = empty($_REQUEST['computer_tomography']) ? null : $_REQUEST['computer_tomography'];
    $computer_tomography_text = empty($_REQUEST['computer_tomography_text']) ? null : $_REQUEST['computer_tomography_text'];
    $chemotherapy = empty($_REQUEST['chemotherapy']) ? null : $_REQUEST['chemotherapy'];
    $mri = empty($_REQUEST['mri']) ? null : $_REQUEST['mri'];
    $hemodialysis = empty($_REQUEST['hemodialysis']) ? null : $_REQUEST['hemodialysis'];
    $non_or_other = empty($_REQUEST['non_or_other']) ? null : $_REQUEST['non_or_other'];
    $non_or_other_text = empty($_REQUEST['non_or_other_text']) ? null : $_REQUEST['non_or_other_text'];
    $discharge_status = empty($_REQUEST['discharge_status']) ? null : $_REQUEST['discharge_status'];
    $discharge_type = empty($_REQUEST['discharge_type']) ? null : $_REQUEST['discharge_type'];
    $hospital_refer = empty($_REQUEST['hospital_refer']) ? null : $_REQUEST['hospital_refer'];
    $additional_code = empty($_REQUEST['additional_code']) ? null : $_REQUEST['additional_code'];
    $morphology_code = empty($_REQUEST['morphology_code']) ? null : $_REQUEST['morphology_code'];
    $cause_of_death_a = empty($_REQUEST['cause_of_death_a']) ? null : $_REQUEST['cause_of_death_a'];
    $cause_of_death_b= empty($_REQUEST['cause_of_death_b']) ? null : $_REQUEST['cause_of_death_b'];
    $cause_of_death_c = empty($_REQUEST['cause_of_death_c']) ? null : $_REQUEST['cause_of_death_c'];
    $onset_and_death = empty($_REQUEST['onset_and_death']) ? null : $_REQUEST['onset_and_death'];
    $child_was_born_live = empty($_REQUEST['child_was_born_live']) ? null : $_REQUEST['child_was_born_live'];
    $child_was_stilborn = empty($_REQUEST['child_was_stilborn']) ? null : $_REQUEST['child_was_stilborn'];
    $died_before_labour = empty($_REQUEST['died_before_labour']) ? null : $_REQUEST['died_before_labour'];
    $during_labour = empty($_REQUEST['during_labour']) ? null : $_REQUEST['during_labour'];
    $not_know = empty($_REQUEST['not_know']) ? null : $_REQUEST['not_know'];
    $was_born_live_date = empty($_REQUEST['was_born_live_date']) ? null : $_REQUEST['was_born_live_date'];
    $was_born_live_hours = empty($_REQUEST['was_born_live_hours']) ? null : $_REQUEST['was_born_live_hours'];
    $died_on_date = empty($_REQUEST['died_on_date']) ? null : $_REQUEST['died_on_date'];
    $died_on_hours = empty($_REQUEST['died_on_hours']) ? null : $_REQUEST['died_on_hours'];
    $was_stilborn_date = empty($_REQUEST['was_stilborn_date']) ? null : $_REQUEST['was_stilborn_date'];
    $was_stilborn_hours = empty($_REQUEST['was_stilborn_hours']) ? null : $_REQUEST['was_stilborn_hours'];
    $died_on_date2 = empty($_REQUEST['died_on_date2']) ? null : $_REQUEST['died_on_date2'];
    $died_on_age = empty($_REQUEST['died_on_age']) ? null : $_REQUEST['died_on_age'];
    $last_menstrual_period = empty($_REQUEST['last_menstrual_period']) ? null : $_REQUEST['last_menstrual_period'];
    $estimated_duration_of_pregnacy = empty($_REQUEST['estimated_duration_of_pregnacy']) ? null : $_REQUEST['estimated_duration_of_pregnacy'];
    $live_births = empty($_REQUEST['live_births']) ? null : $_REQUEST['live_births'];
    $stillbirths = empty($_REQUEST['stillbirths']) ? null : $_REQUEST['stillbirths'];
    $abortions = empty($_REQUEST['abortions']) ? null : $_REQUEST['abortions'];
    $antenatal_care = empty($_REQUEST['antenatal_care']) ? null : $_REQUEST['antenatal_care'];
    $outcome_last_preg = empty($_REQUEST['outcome_last_preg']) ? null : $_REQUEST['outcome_last_preg'];
    $outcome_last_preg_date = empty($_REQUEST['outcome_last_preg_date']) ? null : $_REQUEST['outcome_last_preg_date'];
    $delivery = empty($_REQUEST['delivery']) ? null : $_REQUEST['delivery'];
    $delivery_text = empty($_REQUEST['delivery_text']) ? null : $_REQUEST['delivery_text'];
    
    //$update_datetime = ใช้ NOW()
    $update_user  = $_SESSION['loginname'];

        try {
            $stmt = $conn->prepare("UPDATE ".DbConstant::KPHIS_DBNAME.".ipd_summary
                                    SET summary_plan_date=:summary_plan_date, summary_plan_time=:summary_plan_time,
                                    principal_diagnosis=:principal_diagnosis, pre_admission_comorbidity=:pre_admission_comorbidity,
                                    post_admission_comorbidity=:post_admission_comorbidity,other_diagnosis=:other_diagnosis,
                                    external_cause=:external_cause,operating_room=:operating_room,
                                    tracheostomy=:tracheostomy,mechanical_ventilation=:mechanical_ventilation,
                                    mechanical_ventilation1=:mechanical_ventilation1,mechanical_ventilation2=:mechanical_ventilation2,
                                    packed_redcells=:packed_redcells,fresh_frozen_plasma=:fresh_frozen_plasma,
                                    platelets=:platelets,cryoprecipitate=:cryoprecipitate,
                                    whole_blood=:whole_blood,computer_tomography=:computer_tomography,
                                    computer_tomography_text=:computer_tomography_text,chemotherapy=:chemotherapy,
                                    mri=:mri,hemodialysis=:hemodialysis,
                                    non_or_other=:non_or_other,non_or_other_text=:non_or_other_text,
                                    discharge_status=:discharge_status,discharge_type=:discharge_type,
                                    hospital_refer=:hospital_refer,additional_code=:additional_code,morphology_code=:morphology_code,
                                    cause_of_death_a=:cause_of_death_a,cause_of_death_b=:cause_of_death_b,cause_of_death_c=:cause_of_death_c,onset_and_death=:onset_and_death,
                                    update_user=:update_user, update_datetime=NOW(),child_was_born_live=:child_was_born_live
                                    ,child_was_stilborn=:child_was_stilborn,died_before_labour=:died_before_labour,during_labour=:during_labour,not_know=:not_know
                                    ,was_born_live_date=:was_born_live_date,was_born_live_hours=:was_born_live_hours,died_on_date=:died_on_date,died_on_hours=:died_on_hours
                                    ,was_stilborn_date=:was_stilborn_date,was_stilborn_hours=:was_stilborn_hours,died_on_date2=:died_on_date2
                                    ,died_on_age=:died_on_age,last_menstrual_period=:last_menstrual_period,estimated_duration_of_pregnacy=:estimated_duration_of_pregnacy
                                    ,live_births=:live_births,stillbirths=:stillbirths,abortions=:abortions,antenatal_care=:antenatal_care
                                    ,outcome_last_preg=:outcome_last_preg,outcome_last_preg_date=:outcome_last_preg_date,delivery=:delivery,delivery_text=:delivery_text
                                    WHERE summary_id = :summary_id AND an = :an
                                    ");
            $stmt->execute(array('summary_plan_date'=>$summary_plan_date, 'summary_plan_time'=>$summary_plan_time,
                                    'principal_diagnosis'=>$principal_diagnosis, 'pre_admission_comorbidity'=>$pre_admission_comorbidity,
                                    'post_admission_comorbidity'=>$post_admission_comorbidity, 'other_diagnosis'=>$other_diagnosis,
                                    'external_cause'=>$external_cause, 'operating_room'=>$operating_room,
                                    'tracheostomy'=>$tracheostomy, 'mechanical_ventilation'=>$mechanical_ventilation,
                                    'mechanical_ventilation1'=>$mechanical_ventilation1, 'mechanical_ventilation2'=>$mechanical_ventilation2,
                                    'packed_redcells'=>$packed_redcells, 'fresh_frozen_plasma'=>$fresh_frozen_plasma,
                                    'platelets'=>$platelets, 'cryoprecipitate'=>$cryoprecipitate,
                                    'whole_blood'=>$whole_blood, 'computer_tomography'=>$computer_tomography,
                                    'computer_tomography_text'=>$computer_tomography_text, 'chemotherapy'=>$chemotherapy,
                                    'mri'=>$mri, 'hemodialysis'=>$hemodialysis,
                                    'non_or_other'=>$non_or_other, 'non_or_other_text'=>$non_or_other_text,
                                    'discharge_status'=>$discharge_status, 'discharge_type'=>$discharge_type,
                                    'hospital_refer'=>$hospital_refer,'additional_code'=>$additional_code,'morphology_code'=>$morphology_code,
                                    'cause_of_death_a'=>$cause_of_death_a,'cause_of_death_b'=>$cause_of_death_b,'cause_of_death_c'=>$cause_of_death_c,'onset_and_death'=>$onset_and_death,
                                    'update_user'=>$update_user,'child_was_born_live'=>$child_was_born_live,'child_was_stilborn'=>$child_was_stilborn,
                                    'died_before_labour'=>$died_before_labour,'during_labour'=>$during_labour,'not_know'=>$not_know,
                                    'was_born_live_date'=>$was_born_live_date,'was_born_live_hours'=>$was_born_live_hours,'died_on_date'=>$died_on_date,
                                    'died_on_hours'=>$died_on_hours,'was_stilborn_date'=>$was_stilborn_date,'was_stilborn_hours'=>$was_stilborn_hours
                                    ,'died_on_date2'=>$died_on_date2,'died_on_age'=>$died_on_age,'last_menstrual_period'=>$last_menstrual_period
                                    ,'estimated_duration_of_pregnacy'=>$estimated_duration_of_pregnacy,'live_births'=>$live_births
                                    ,'stillbirths'=>$stillbirths,'abortions'=>$abortions,'antenatal_care'=>$antenatal_care,'outcome_last_preg'=>$outcome_last_preg
                                    ,'outcome_last_preg_date'=>$outcome_last_preg_date,'delivery'=>$delivery,'delivery_text'=>$delivery_text
                                    ,'summary_id'=>$summary_id, 'an'=>$an

                                ));

            $output_error = '<div class="alert alert-success">บันทึกข้อมูลสำเร็จ</div>';

        } catch (PDOException  $e) {
            echo $e->getMessage();
            $output_error = '<div class="alert alert-danger">ERROR !!FOCUS LIST</div>';
        }

        echo $output_error;
?>
