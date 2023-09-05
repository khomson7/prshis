<?php   require_once '../include/Session.php';
        Session::checkLoginSessionAndShowMessage(); //เช็ค session
        if(!(Session::checkPermission('ADMISSION_NOTE','ADD'))){
            return;
        }
        require_once '../include/DbUtils.php';
        require_once '../include/KphisQueryUtils.php';

        $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล

        $admission_note_id = $_REQUEST['admission_note_id'];

        $an = $_REQUEST['an'];
        $report_type_id = '1'; //from table prs_report_type
      //  $an = $_REQUEST['an'];
        $hn = $_REQUEST['hn'];
        $hpi = $_REQUEST['hpi'];
        $req_hospital = $_REQUEST['req_hospital'];
        $ros = $_REQUEST['ros'];
        //$history_from = $_REQUEST['history_from'];
        $history_from  = empty($_REQUEST['history_from'])? null : $_REQUEST['history_from'];
        $pmh = $_REQUEST['pmh'];
        $fh = $_REQUEST['fh'];
        $vaccineation = $_REQUEST['vaccineation'];
        $gd = $_REQUEST['gd'];
        $fdh = $_REQUEST['fdh'];
        $lmp = $_REQUEST['lmp'];

        $receiver_medication_date = $_REQUEST['receiver_medication_date'];
        $receiver_medication_time = $_REQUEST['receiver_medication_time'];
        $take_medication_by = $_REQUEST['take_medication_by'];
        $arrive_by = $_REQUEST['arrive_by'];
        $taken_by_relative  = empty($_REQUEST['taken_by_relative'])? null : $_REQUEST['taken_by_relative'];
        $taken_by_nurse  = empty($_REQUEST['taken_by_nurse'])? null : $_REQUEST['taken_by_nurse'];
        $taken_by_crib  = empty($_REQUEST['taken_by_crib'])? null : $_REQUEST['taken_by_crib'];
        $taken_by_etc  = empty($_REQUEST['taken_by_etc'])? null : $_REQUEST['taken_by_etc'];
        $taken_by  = empty($_REQUEST['taken_by'])? null : $_REQUEST['taken_by'];
        $informant_patient = empty($_REQUEST['informant_patient'])? null : $_REQUEST['informant_patient'];
        $informant_relatives = empty($_REQUEST['informant_relatives'])? null : $_REQUEST['informant_relatives'];
        $informant_deliverer = empty($_REQUEST['informant_deliverer'])? null : $_REQUEST['informant_deliverer'];
        $informant_etc = empty($_REQUEST['informant_etc'])? null : $_REQUEST['informant_etc'];
        $chief_complaints = $_REQUEST['chief_complaints'];
        $medical_history  = $_REQUEST['medical_history'];
        $bp = empty($_REQUEST['bp']) ? null : $_REQUEST['bp'];
        $t  = empty($_REQUEST['t']) ? null : $_REQUEST['t'];
        $pr = empty($_REQUEST['pr']) ? null : $_REQUEST['pr'];
        $rr = empty($_REQUEST['rr']) ? null : $_REQUEST['rr'];
        $gcs = empty($_REQUEST['gcs']) ? null : $_REQUEST['gcs'];
        $e = empty($_REQUEST['e']) ? null : $_REQUEST['e'];
        $v = empty($_REQUEST['v']) ? null : $_REQUEST['v'];
        $m = empty($_REQUEST['m']) ? null : $_REQUEST['m'];
        $braden_scale = empty($_REQUEST['braden_scale']) ? null : $_REQUEST['braden_scale'];
        $disease = empty($_REQUEST['disease']) ? null : $_REQUEST['disease'];
        $disease_detail = empty($_REQUEST['disease_detail']) ? null : $_REQUEST['disease_detail'];
        $disease_etc = empty($_REQUEST['disease_etc']) ? null : $_REQUEST['disease_etc'];
        $operation_history = empty($_REQUEST['operation_history'])? null : $_REQUEST['operation_history'];
        $allergy_history = $_REQUEST['allergy_history'];
        $allergy_drug_history_hosxp = $_REQUEST['allergy_drug_history_hosxp'];
        $allergy_drug_history = empty($_REQUEST['allergy_drug_history']) ? null : $_REQUEST['allergy_drug_history'];
        $allergy_food_history = empty($_REQUEST['allergy_food_history']) ? null : $_REQUEST['allergy_food_history'];
        $allergy_etc_history = empty($_REQUEST['allergy_etc_history']) ? null : $_REQUEST['allergy_etc_history'];
        $allergy_detail = empty($_REQUEST['allergy_detail']) ? null : $_REQUEST['allergy_detail'];
        $family_medical_history = empty($_REQUEST['family_medical_history']) ? null : $_REQUEST['family_medical_history'];
        $family_medical_history_detail = empty($_REQUEST['family_medical_history_detail']) ? null : $_REQUEST['family_medical_history_detail'];
        $receives_immunisation_history_kid  = empty($_REQUEST['receives_immunisation_history_kid']) ? null : $_REQUEST['receives_immunisation_history_kid'];
        if($receives_immunisation_history_kid == 'ครบตามวัย'){
            $receives_immunisation_history_kid  = $_REQUEST['receives_immunisation_history_kid'];
        }else{
            $receives_immunisation_history_kid  = empty($_REQUEST['receives_immunisation_history_kid_text']) ? null : $_REQUEST['receives_immunisation_history_kid_text'];
        }
        $developmentally_kid  = empty($_REQUEST['developmentally_kid']) ? null : $_REQUEST['developmentally_kid'];
        if($developmentally_kid == 'ปกติ'){
            $developmentally_kid  = $_REQUEST['developmentally_kid'];
        }else{
            $developmentally_kid  = empty($_REQUEST['developmentally_kid_text']) ? null : $_REQUEST['developmentally_kid_text'];
        }
        $g = StringUtils::isBlankOrNull($_REQUEST['g']) ? null : $_REQUEST['g'];
        $p = StringUtils::isBlankOrNull($_REQUEST['p']) ? null : $_REQUEST['p'];
        $anc = $_REQUEST['anc'];
        $tt = empty($_REQUEST['tt']) ? null : $_REQUEST['tt'];
        $gestational_age = $_REQUEST['gestational_age'];
        $gestational_day = empty($_REQUEST['gestational_day']) ? null : $_REQUEST['gestational_day'];
        $last_child = empty($_REQUEST['last_child']) ? null : $_REQUEST['last_child'];
        $last_abort = empty($_REQUEST['last_abort']) ? null : $_REQUEST['last_abort'];
        $curette = empty($_REQUEST['curette']) ? null : $_REQUEST['curette'];
        $lmp = empty($_REQUEST['lmp']) ? null : $_REQUEST['lmp'];
        $edc = empty($_REQUEST['edc']) ? null : $_REQUEST['edc'];
        $pb_no = empty($_REQUEST['pb_no']) ? null : $_REQUEST['pb_no'];
        $giant_baby = empty($_REQUEST['giant_baby']) ? null : $_REQUEST['giant_baby'];
        $distocia = empty($_REQUEST['distocia']) ? null : $_REQUEST['distocia'];
        $extraction = empty($_REQUEST['extraction']) ? null : $_REQUEST['extraction'];
        $pph = empty($_REQUEST['pph']) ? null : $_REQUEST['pph'];
        $pb_etc = empty($_REQUEST['pb_etc']) ? null : $_REQUEST['pb_etc'];
        $hf = empty($_REQUEST['hf']) ? null : $_REQUEST['hf'];
        $hf_position = empty($_REQUEST['hf_position']) ? null : $_REQUEST['hf_position'];
        $hiv = empty($_REQUEST['hiv']) ? null : $_REQUEST['hiv'];
        $vdrl = empty($_REQUEST['vdrl']) ? null : $_REQUEST['vdrl'];
        $hbs_ag = empty($_REQUEST['hbs_ag']) ? null : $_REQUEST['hbs_ag'];
        $hct = empty($_REQUEST['hct']) ? null : $_REQUEST['hct'];
        $hiv2 = empty($_REQUEST['hiv2']) ? null : $_REQUEST['hiv2'];
        $vdrl2 = empty($_REQUEST['vdrl2']) ? null : $_REQUEST['vdrl2'];
        $hbs_ag2 = empty($_REQUEST['hbs_ag2']) ? null : $_REQUEST['hbs_ag2'];
        $hct2 = empty($_REQUEST['hct2']) ? null : $_REQUEST['hct2'];
        $gr = empty($_REQUEST['gr']) ? null : $_REQUEST['gr'];
        $thalassemia = empty($_REQUEST['thalassemia']) ? null : $_REQUEST['thalassemia'];
        $husband = empty($_REQUEST['husband']) ? null : $_REQUEST['husband'];
        $condition_pregnant  = empty($_REQUEST['condition_pregnant']) ? null : $_REQUEST['condition_pregnant'];
        if($condition_pregnant == 'ปกติ'){
            $condition_pregnant  = $_REQUEST['condition_pregnant'];
        }else{
            $condition_pregnant  = empty($_REQUEST['condition_pregnant_text']) ? null : $_REQUEST['condition_pregnant_text'];
        }
        $deliver_anomalies  = empty($_REQUEST['deliver_anomalies']) ? null : $_REQUEST['deliver_anomalies'];
        if($deliver_anomalies == 'ปกติ'){
            $deliver_anomalies  = $_REQUEST['deliver_anomalies'];
        }else{
            $deliver_anomalies  = empty($_REQUEST['deliver_anomalies_text']) ? null : $_REQUEST['deliver_anomalies_text'];
        }
        $deliver_anomalies_means = empty($_REQUEST['deliver_anomalies_means']) ? null : $_REQUEST['deliver_anomalies_means'];
        $deliver_location = empty($_REQUEST['deliver_location']) ? null : $_REQUEST['deliver_location'];
        $deliver_first_weight = empty($_REQUEST['deliver_first_weight']) ? null : $_REQUEST['deliver_first_weight'];
        $deliver_first_health = empty($_REQUEST['deliver_first_health']) ? null : $_REQUEST['deliver_first_health'];
        $fant_breast_feeding_end_age_month = empty($_REQUEST['fant_breast_feeding_end_age_month']) ? null : $_REQUEST['fant_breast_feeding_end_age_month'];
        $fant_artificial_feeding_start_age_month = empty($_REQUEST['fant_artificial_feeding_start_age_month']) ? null : $_REQUEST['fant_artificial_feeding_start_age_month'];
        $fant_feeding_etc = $_REQUEST['fant_feeding_etc'];
        $supplementary_feeding = empty($_REQUEST['supplementary_feeding']) ? null : $_REQUEST['supplementary_feeding'];
        $supplementary_feeding_start_age_month = empty($_REQUEST['supplementary_feeding_start_age_month']) ? null : $_REQUEST['supplementary_feeding_start_age_month'];
        $disease_operation_allergy = empty($_REQUEST['disease_operation_allergy']) ? null : $_REQUEST['disease_operation_allergy'];
        $inpatient_history = empty($_REQUEST['inpatient_history']) ? null : $_REQUEST['inpatient_history'];
        $inpatient_last_date = empty($_REQUEST['inpatient_last_date']) ? null : $_REQUEST['inpatient_last_date'];
        $inpatient_location = empty($_REQUEST['inpatient_location']) ? null : $_REQUEST['inpatient_location'];
        $inpatient_because = empty($_REQUEST['inpatient_because']) ? null : $_REQUEST['inpatient_because'];
        $pe_general = $_REQUEST['pe_general'];
        $pe_skin = $_REQUEST['pe_skin'];
        $pe_heent = $_REQUEST['pe_heent'];
        $pe_neck = $_REQUEST['pe_neck'];
        $pe_breastthorax = $_REQUEST['pe_breastthorax'];
        $pe_heart = $_REQUEST['pe_heart'];
        $pe_lungs = $_REQUEST['pe_lungs'];
        $pe_abdomen = $_REQUEST['pe_abdomen'];
        $pe_rectalgenitalia = $_REQUEST['pe_rectalgenitalia'];
        $pe_extremities = $_REQUEST['pe_extremities'];
        $pe_neurological = $_REQUEST['pe_neurological'];
        $pe_ob_gynexam = $_REQUEST['pe_ob_gynexam'];
        $pe_other = $_REQUEST['pe_other'];
        $pe_text = $_REQUEST['pe_text'];
        $pe_cvs = $_REQUEST['pe_cvs'];
        $pe_cns = $_REQUEST['pe_cns'];
        $svg_tag = $_REQUEST['svg_tag'];
        $impression = $_REQUEST['impression'];
        $problem_list = $_REQUEST['problem_list'];
        $diff_dx = $_REQUEST['diff_dx'];
        $plan_management = $_REQUEST['plan_management'];
        $nurse_name = empty($_REQUEST['nurse_name']) ? null : $_REQUEST['nurse_name'];
        $nurse_pos = empty($_REQUEST['nurse_pos']) ? null : $_REQUEST['nurse_pos'];
        //$doc_name = empty($_REQUEST['doc_name']) ? null : $_REQUEST['doc_name'];
        //$doc_pos = empty($_REQUEST['doc_pos']) ? null : $_REQUEST['doc_pos'];
        $create_user = $_SESSION['loginname'];
        // $create_datetime = $_REQUEST['create_datetime'];
        $update_user = $_SESSION['loginname'];
        // $update_datetime = $_REQUEST['update_datetime'];
        $version = $_REQUEST['version'];
        $version = $version + 1;
            // empty($_REQUEST['']) ? null : $_REQUEST[''];

            try {
                $stmt = $conn->prepare("UPDATE ".DbConstant::KPHIS_DBNAME.".prs_dr_admission_note SET hn=:hn, an=:an,
                chief_complaints=:chief_complaints,medical_history=:medical_history,req_hospital=:req_hospital
                ,informant_patient=:informant_patient,informant_relatives=:informant_relatives, informant_deliverer=:informant_deliverer
                ,informant_etc=:informant_etc
                ,ros=:ros,vaccineation=:vaccineation,history_from=:history_from,pmh=:pmh,fh=:fh
                ,gd=:gd,fdh=:fdh,lmp=:lmp,
                inpatient_history=:inpatient_history,inpatient_last_date=:inpatient_last_date,inpatient_location=:inpatient_location,inpatient_because=:inpatient_because,
                pe_general=:pe_general, pe_skin=:pe_skin,
                pe_heent=:pe_heent, pe_neck=:pe_neck,pe_breastthorax=:pe_breastthorax,
                pe_heart=:pe_heart, pe_lungs=:pe_lungs, pe_abdomen=:pe_abdomen,
                pe_rectalgenitalia=:pe_rectalgenitalia,pe_extremities=:pe_extremities,
                pe_neurological=:pe_neurological, pe_ob_gynexam=:pe_ob_gynexam,
                pe_other=:pe_other,pe_text=:pe_text,pe_cvs=:pe_cvs, pe_cns=:pe_cns,
                svg_tag=:svg_tag,impression=:impression,diff_dx=:diff_dx,problem_list=:problem_list,plan_management=:plan_management,
                update_user=:update_user,version=:version
                WHERE admission_note_id=:admission_note_id");
                $stmt->execute(array('admission_note_id'=>$admission_note_id, 'hn'=>$hn, 'an'=>$an,
                'chief_complaints'=>$chief_complaints,'medical_history'=>$medical_history,'req_hospital'=>$req_hospital,
                'informant_patient'=>$informant_patient,
                'informant_relatives'=>$informant_relatives, 'informant_deliverer'=>$informant_deliverer,
                'informant_etc'=>$informant_etc,
                'ros'=>$ros,'vaccineation'=>$vaccineation,'history_from'=>$history_from,'pmh'=>$pmh,'fh'=>$fh,
                'gd'=>$gd,'fdh'=>$fdh,'lmp'=>$lmp,
                'inpatient_history'=>$inpatient_history,'inpatient_last_date'=>$inpatient_last_date
                ,'inpatient_location'=>$inpatient_location,'inpatient_because'=>$inpatient_because,
                'pe_general'=>$pe_general, 'pe_skin'=>$pe_skin,
                'pe_heent'=>$pe_heent, 'pe_neck'=>$pe_neck,'pe_breastthorax'=>$pe_breastthorax,
                'pe_heart'=>$pe_heart, 'pe_lungs'=>$pe_lungs, 'pe_abdomen'=>$pe_abdomen,
                'pe_rectalgenitalia'=>$pe_rectalgenitalia,'pe_extremities'=>$pe_extremities,
                'pe_neurological'=>$pe_neurological, 'pe_ob_gynexam'=>$pe_ob_gynexam,
                'pe_other'=>$pe_other,'pe_text'=>$pe_text,'pe_cvs'=>$pe_cvs, 'pe_cns'=>$pe_cns,
                'svg_tag'=>$svg_tag,'impression'=>$impression,'diff_dx'=>$diff_dx,'problem_list'=>$problem_list,'plan_management'=>$plan_management,
                'update_user'=>$update_user,'version'=>$version));

             if(!empty($_REQUEST['admission_note_doctor'])){
                    $stmt_item_check = "SELECT COUNT(*) AS count_item  FROM ".DbConstant::KPHIS_DBNAME.".prs_dr_admission_note_item WHERE an=:an";
                    $stmt_item_check = $conn->prepare($stmt_item_check);
                    $stmt_item_check->execute(['an'=>$an]);
                    $row_item_check = $stmt_item_check->fetch();
                    $count_item = $row_item_check['count_item'];
                    if($count_item > 0){
                        $stmt_item_delete = "DELETE FROM ".DbConstant::KPHIS_DBNAME.".prs_dr_admission_note_item WHERE an=:an";
                        $stmt_item_delete = $conn->prepare($stmt_item_delete);
                        $stmt_item_delete->execute(['an'=>$an]);
                    }
                    foreach($_REQUEST['admission_note_doctor'] as $admission_note_doctor){
                        $stmt_item = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".prs_dr_admission_note_item
                        (admission_note_id,an,admission_note_doctor,create_user,create_datetime,update_user,update_datetime,version)
                        VALUES (:admission_note_id,:an,:admission_note_doctor,:create_user,now(),:update_user,now(),:version)");
                        $stmt_item->execute(array('admission_note_id'=>$admission_note_id, 'an'=>$an,
                        'admission_note_doctor'=>$admission_note_doctor,
                        'create_user'=>$create_user, 'update_user'=>$update_user, 'version'=>$version));
                    }
                } 

                Session::insertSystemAccessLog(json_encode(array(
                    'form'=>'PRE-ER-DR-ADMISSION-NOTE-FORM',
                    'action'=>'UPDATE',
                    'vn'=>$an,
                ),JSON_UNESCAPED_UNICODE));


                $output_error = '<div class="alert alert-success">บันทึกข้อมูลเรียบร้อยแล้วคะ</div>';

                } catch (PDOException  $e) {
                    echo $e->getMessage();
                    $output_error = '<div class="alert alert-danger">ERROR !!</div>';
                }

            echo $output_error;

    ?>
