<?php
    require_once '../include/DbUtils.php';
    require_once '../include/Session.php';
    require_once '../include/KphisQueryUtils.php';

    $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
    Session::checkLoginSessionAndShowMessage(); //เช็ค session
    if(!(
        // && SessionManager::checkPermission('IPD_NURSE_MAIN_PROGRAM','ACCESS')
        // && SessionManager::checkPermission('IPD_DISCHARGE_SUMMARY','ADD')
        Session::checkPermission('IPD_DISCHARGE_SUMMARY','EDIT')
        // && SessionManager::checkPermission('IPD_DISCHARGE_SUMMARY','VIEW')
        // && SessionManager::checkPermission('IPD_DISCHARGE_SUMMARY','REMOVE')
        )){
        return;
    }

    $an = $_REQUEST['summary_an'];//รับค่า an
    $hn = KphisQueryUtils::getHnByAn($an);// function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา
    $summary_id = $_REQUEST['summary_id'];
    $output_error = '';

    //check for require field
    if(empty($an)
    || empty($summary_id)
    || empty($_REQUEST['summary_plan_date'])
    || empty($_REQUEST['summary_plan_time'])
    ){
        exit;
    }

    $summary_plan_date = empty($_REQUEST['summary_plan_date']) ? null : $_REQUEST['summary_plan_date'];
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
                                    hospital_refer=:hospital_refer,
                                    update_user=:update_user, update_datetime=NOW()
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
                                    'hospital_refer'=>$hospital_refer,
                                    'update_user'=>$update_user,
                                    'summary_id'=>$summary_id, 'an'=>$an,

                                ));

                                Session::insertSystemAccessLog(json_encode(array(
                                    'form'=>'IPD-SUMMARY',
                                    'action'=>'UPDATE',
                                    'an'=>$an,
                                ),JSON_UNESCAPED_UNICODE));

            $output_error = '<div class="alert alert-success">บันทึกข้อมูลสำเร็จ</div>';

        } catch (PDOException  $e) {
            echo $e->getMessage();
            $output_error = '<div class="alert alert-danger">ERROR !!FOCUS LIST</div>';
        }

        echo $output_error;
?>
