<?php   require_once '../include/Session.php';
        Session::checkLoginSessionAndShowMessage(); //เช็ค session
        if(!(
                Session::checkPermission('ADMISSION_NOTE','ADD')
            )){
            return;
        }
        require_once '../include/DbUtils.php';
        require_once '../include/KphisQueryUtils.php';

        $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล

        $an = $_REQUEST['an'];
       // $report_type_id = '1'; //from table prs_report_type
      //  $an = $_REQUEST['an'];
        $hn = $_REQUEST['hn'];
        $informant_patient = empty($_REQUEST['informant_patient'])? null : $_REQUEST['informant_patient'];
        $informant_relatives = empty($_REQUEST['informant_relatives'])? null : $_REQUEST['informant_relatives'];
        $informant_deliverer = empty($_REQUEST['informant_deliverer'])? null : $_REQUEST['informant_deliverer'];
        $informant_etc = empty($_REQUEST['informant_etc'])? null : $_REQUEST['informant_etc'];
        $chief_complaints = $_REQUEST['chief_complaints'];
        $medical_history  = $_REQUEST['medical_history'];
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


        $bp = empty($_REQUEST['bp']) ? null : $_REQUEST['bp'];
        $t  = empty($_REQUEST['t']) ? null : $_REQUEST['t'];
        $pr = empty($_REQUEST['pr']) ? null : $_REQUEST['pr'];
        $rr = empty($_REQUEST['rr']) ? null : $_REQUEST['rr'];
        $bw = empty($_REQUEST['bw']) ? null : $_REQUEST['bw'];
        $height = empty($_REQUEST['height']) ? null : $_REQUEST['height'];

      //  $gcs = empty($_REQUEST['gcs']) ? null : $_REQUEST['gcs'];
      //  $e = empty($_REQUEST['e']) ? null : $_REQUEST['e'];
      //  $v = empty($_REQUEST['v']) ? null : $_REQUEST['v'];
      //  $m = empty($_REQUEST['m']) ? null : $_REQUEST['m'];

        $developmentally_kid  = empty($_REQUEST['developmentally_kid']) ? null : $_REQUEST['developmentally_kid'];
        if($developmentally_kid == 'ปกติ'){
            $developmentally_kid  = $_REQUEST['developmentally_kid'];
        }else{
            $developmentally_kid  = empty($_REQUEST['developmentally_kid_text']) ? null : $_REQUEST['developmentally_kid_text'];
        }

        $g = StringUtils::isBlankOrNull($_REQUEST['g']) ? null : $_REQUEST['g'];
        $p = StringUtils::isBlankOrNull($_REQUEST['p']) ? null : $_REQUEST['p'];
      //  $lmp = empty($_REQUEST['lmp']) ? null : $_REQUEST['lmp'];
      //  $edc = empty($_REQUEST['edc']) ? null : $_REQUEST['edc'];

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
        $svg_tag1 = $_REQUEST['svg_tag1'];
        $svg_tag2 = $_REQUEST['svg_tag2'];
        $svg_tag3 = $_REQUEST['svg_tag3'];
        $svg_tag4 = $_REQUEST['svg_tag4'];
        $svg_tag5 = $_REQUEST['svg_tag5'];
        $impression = $_REQUEST['impression'];
        $diff_dx = $_REQUEST['diff_dx'];
        $plan_management = $_REQUEST['plan_management'];
        $diff_dx = $_REQUEST['diff_dx'];
        $problem_list = $_REQUEST['problem_list'];
        $create_user = $_SESSION['loginname'];
        $nurse_name = empty($_REQUEST['nurse_name']) ? null : $_REQUEST['nurse_name'];
        // $doc_name = empty($_REQUEST['doc_name']) ? null : $_REQUEST['doc_name'];
        $nurse_pos = empty($_REQUEST['nurse_pos']) ? null : $_REQUEST['nurse_pos'];
        //$doc_pos = empty($_REQUEST['doc_pos']) ? null : $_REQUEST['doc_pos'];
        // $create_datetime = $_REQUEST['create_datetime'];
        $update_user = $_SESSION['loginname'];
        // $update_datetime = $_REQUEST['update_datetime'];
        $version = 1;

        try {
                $stmt = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".prs_er_trauma_note
                (hn,an,chief_complaints,medical_history,req_hospital
                ,informant_patient,informant_relatives, informant_deliverer,informant_etc
                ,ros,vaccineation,history_from,
                inpatient_history,inpatient_last_date,inpatient_location,inpatient_because,
                pmh,fh,gd,fdh,lmp,bp,t,pr,rr,pe_general,pe_skin,pe_heent,
                pe_neck,pe_breastthorax,pe_heart,pe_lungs,pe_abdomen,pe_rectalgenitalia,pe_extremities,
                pe_neurological,pe_ob_gynexam,pe_other,pe_text,pe_cvs,pe_cns,svg_tag,svg_tag1,svg_tag2,svg_tag3,svg_tag4,svg_tag5,plan_management,problem_list,
                impression,diff_dx,
                create_user,nurse_name,nurse_pos,update_user,create_datetime,update_datetime,version)
                VALUES (:hn,:an,:chief_complaints,:medical_history,:req_hospital
                ,:informant_patient,:informant_relatives,:informant_deliverer,:informant_etc
                ,:ros,:vaccineation,:history_from,
                :inpatient_history,:inpatient_last_date,:inpatient_location,:inpatient_because,
                :pmh,:fh,:gd,:fdh,:lmp,:bp,:t,:pr,:rr,:pe_general,
                :pe_skin,:pe_heent,:pe_neck,:pe_breastthorax,:pe_heart,:pe_lungs,:pe_abdomen,:pe_rectalgenitalia,
                :pe_extremities,:pe_neurological,:pe_ob_gynexam,:pe_other,:pe_text,:pe_cvs,:pe_cns,:svg_tag,:svg_tag1,:svg_tag2,:svg_tag3,:svg_tag4,:svg_tag5,:plan_management,:problem_list,
                :impression,:diff_dx,
                :create_user,:nurse_name,:nurse_pos,:update_user,now(),now(),:version)");
                $stmt->execute(array('hn'=>$hn,'an'=>$an ,'chief_complaints'=>$chief_complaints,
                'medical_history'=>$medical_history,'req_hospital'=>$req_hospital
                ,'informant_patient'=>$informant_patient,
                'informant_relatives'=>$informant_relatives, 'informant_deliverer'=>$informant_deliverer,'informant_etc'=>$informant_etc
                ,'ros'=>$ros,'vaccineation'=>$vaccineation,'history_from'=>$history_from,
                'inpatient_history'=>$inpatient_history,'inpatient_last_date'=>$inpatient_last_date, 'inpatient_location'=>$inpatient_location,
                'inpatient_because'=>$inpatient_because,
                'pmh'=>$pmh,'fh'=>$fh,'gd'=>$gd,'fdh'=>$fdh,'lmp'=>$lmp,'bp'=>$bp,'t'=>$t,'pr'=>$pr,'rr'=>$rr,'pe_general'=>$pe_general, 'pe_skin'=>$pe_skin,
                'pe_heent'=>$pe_heent, 'pe_neck'=>$pe_neck,'pe_breastthorax'=>$pe_breastthorax,
                'pe_heart'=>$pe_heart, 'pe_lungs'=>$pe_lungs, 'pe_abdomen'=>$pe_abdomen,
                'pe_rectalgenitalia'=>$pe_rectalgenitalia,'pe_extremities'=>$pe_extremities,
                'pe_neurological'=>$pe_neurological, 'pe_ob_gynexam'=>$pe_ob_gynexam,
                'pe_other'=>$pe_other,'pe_text'=>$pe_text,'pe_cvs'=>$pe_cvs, 'pe_cns'=>$pe_cns, 'svg_tag'=>$svg_tag,'svg_tag1'=>$svg_tag1,'svg_tag2'=>$svg_tag2,'svg_tag3'=>$svg_tag3,'svg_tag4'=>$svg_tag4,'svg_tag5'=>$svg_tag5,'plan_management'=>$plan_management,'problem_list'=>$problem_list,
                'impression'=>$impression,'diff_dx'=>$diff_dx,
                'create_user'=>$create_user,'nurse_name'=>$nurse_name,'nurse_pos'=>$nurse_pos,'update_user'=>$update_user,'version'=>$version));

              $admission_note_id = $conn->lastInsertId();

             /*   if(!empty($_REQUEST['admission_note_doctor'])){
                    foreach($_REQUEST['admission_note_doctor'] as $admission_note_doctor){
                        $stmt_item = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".prs_dr_admission_note_item (admission_note_id,an,admission_note_doctor,create_user,create_datetime,update_user,update_datetime,version)
                        VALUES (:admission_note_id,:an,:admission_note_doctor,:create_user,now(),:update_user,now(),:version)");
                        $stmt_item->execute(array('admission_note_id'=>$admission_note_id, 'an'=>$an,
                        'admission_note_doctor'=>$admission_note_doctor,
                        'create_user'=>$create_user, 'update_user'=>$update_user, 'version'=>$version));
                    }
                }

                */

                $output_error = '<div class="alert alert-success">บันทึกข้อมูลเรียบร้อยแล้วคะ</div>';

            } catch (PDOException  $e) {
                echo $e->getMessage();
                $output_error = '<div class="alert alert-danger">ERROR !!</div>';
            }
        echo $output_error;
?>
