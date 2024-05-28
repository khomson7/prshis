<?php
//เวลาตาม timezone
date_default_timezone_set("Asia/Bangkok");
require_once '../include/Session.php';
Session::checkLoginSessionAndShowMessage(); //เช็ค session

require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';

$conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
// $an = '660005698';
$an = $_REQUEST['an'];
$hn = KphisQueryUtils::getHnByAn($an);   // function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา
$id = $_REQUEST['id'];

$rxdate = $_REQUEST['rxdate'];
$rxtime = $_REQUEST['rxtime'];
$intime = $_REQUEST['intime'];
$cc = $_REQUEST['cc'];
$current_illness = $_REQUEST['current_illness'];

$labor_history = empty($_REQUEST['labor_history']) ? null : $_REQUEST['labor_history'];
$c_chronic =  empty($_REQUEST['c_chronic']) ? null : $_REQUEST['c_chronic'];
$hos_history =  empty($_REQUEST['hos_history']) ? null : $_REQUEST['hos_history'];
$h_sergery =  empty($_REQUEST['h_sergery']) ? null : $_REQUEST['h_sergery'];
$h_allergy =  empty($_REQUEST['h_allergy']) ? null : $_REQUEST['h_allergy'];
$history_of_drug =  empty($_REQUEST['history_of_drug']) ? null : $_REQUEST['history_of_drug'];
$pmh_2 =  empty($_REQUEST['pmh2']) ? null : $_REQUEST['pmh2'];
$g = $_REQUEST['g'];
$p = $_REQUEST['p'];
$l_ga = $_REQUEST['l_ga'];
$l_ga_by = $_REQUEST['l_ga_by'];
$prenatal_wks = $_REQUEST['prenatal_wks'];
$prenatral_count = $_REQUEST['prenatral_count'];
$k8 = $_REQUEST['k8'];
$k8_less = $_REQUEST['k8_less'];
$at_ = $_REQUEST['at_'];
$dt = $_REQUEST['dt'];
$dt_needle = $_REQUEST['dt_needle'];
$anc_lab_hiv1 = $_REQUEST['anc_lab_hiv1'];
$anc_lab_rpr1 = $_REQUEST['anc_lab_rpr1'];
$anc_lab_hbsag1 = $_REQUEST['anc_lab_hbsag1'];
$anc_lab_hct1 = $_REQUEST['anc_lab_hct1'];
$anc_lab_hb1 = $_REQUEST['anc_lab_hb1'];
$anc_lab_blgr = $_REQUEST['anc_lab_blgr'];
$anc_lab_blgr_rh = $_REQUEST['anc_lab_blgr_rh'];
$anc_lab_dcip1 = $_REQUEST['anc_lab_dcip1'];
$anc_lab_mvc1 = $_REQUEST['anc_lab_mvc1'];
$anc_lab_hb_typing1 = $_REQUEST['anc_lab_hb_typing1'];
$anc_lab_hiv2 = $_REQUEST['anc_lab_hiv2'];
$anc_lab_rpr2 = $_REQUEST['anc_lab_rpr2'];
$anc_lab_hct2 = $_REQUEST['anc_lab_hct2'];
$anc_lab_hb2 = $_REQUEST['anc_lab_hb2'];
$hus_lab_hiv = $_REQUEST['hus_lab_hiv'];
$lab_rpr2 = $_REQUEST['lab_rpr2'];
$lab_dcip2 = $_REQUEST['lab_dcip2'];
$lab_hb_typing2 = $_REQUEST['lab_hb_typing2'];
$ma_fa_school = $_REQUEST['ma_fa_school'];
$quad_test = $_REQUEST['quad_test'];
$other_lab = $_REQUEST['other_lab'];
$bt =  empty($_REQUEST['bt']) ? null : $_REQUEST['bt'];
$pr =  empty($_REQUEST['pr']) ? null : $_REQUEST['pr'];
$rr =  empty($_REQUEST['rr']) ? null : $_REQUEST['rr'];
$bps =  empty($_REQUEST['bps']) ? null : $_REQUEST['bps'];
$bpd =  empty($_REQUEST['bpd']) ? null : $_REQUEST['bpd'];
$sleep_hour =  empty($_REQUEST['sleep_hour']) ? null : $_REQUEST['sleep_hour'];
$pain_area =  empty($_REQUEST['pain_area']) ? null : $_REQUEST['pain_area'];
$pain_score =  empty($_REQUEST['pain_score']) ? null : $_REQUEST['pain_score'];
$education =  empty($_REQUEST['education']) ? null : $_REQUEST['education'];
$ocupation =  empty($_REQUEST['ocupation']) ? null : $_REQUEST['ocupation'];
$income =  empty($_REQUEST['income']) ? null : $_REQUEST['income'];
$income_enough =  empty($_REQUEST['income_enough']) ? null : $_REQUEST['income_enough'];
$caretaker =  empty($_REQUEST['caretaker']) ? null : $_REQUEST['caretaker'];
$caretaker_ocupation =  empty($_REQUEST['caretaker_ocupation']) ? null : $_REQUEST['caretaker_ocupation'];
$caretaker_income  =  empty($_REQUEST['caretaker_income']) ? null : $_REQUEST['caretaker_income'];
$first_symptoms  =  empty($_REQUEST['first_symptoms']) ? null : $_REQUEST['first_symptoms'];
$bw  =  empty($_REQUEST['bw']) ? null : $_REQUEST['bw'];
$hight  =  empty($_REQUEST['hight']) ? null : $_REQUEST['hight'];
$bw_befor_prenatal  =  empty($_REQUEST['bw_befor_prenatal']) ? null : $_REQUEST['bw_befor_prenatal'];
$bmi_befor_prenatal  =  empty($_REQUEST['bmi_befor_prenatal']) ? null : $_REQUEST['bmi_befor_prenatal'];
$leukorrhea_history  =  empty($_REQUEST['leukorrhea_history']) ? null : $_REQUEST['leukorrhea_history'];
$behaviors_risk_sexually   =  empty($_REQUEST['behaviors_risk_sexually']) ? null : $_REQUEST['behaviors_risk_sexually'];

//    


$update_datetime = date('Y-m-d H:i:s');
$create_user = $_SESSION['loginname'];
$update_user = $_SESSION['loginname'];
$version0 = $_REQUEST['version'];
$version = $version0 + 1;

try {
        //เรียกใช้งาน sql update
        if (
                $rxdate != '' && $rxtime != '' && $labor_history != '' && $c_chronic !='' && $h_allergy !=''&& $history_of_drug != ''
                && $pmh_2 !='' && $g !='' && $p !='' && $l_ga !='' && $l_ga_by !='' && $prenatal_wks !='' && $prenatral_count != '' /*&& $dt != ''
                && $dt_needle != '' && $first_symptoms != ''*/
        ) {
                $output_error = '<script>
        NotificationMessage("บันทึกข้อมูลสำเร็จ", "success");     
        </script>';
                $stmt = $conn->prepare("UPDATE " . DbConstant::KPHIS_DBNAME . ".prs_lr_report2 SET an=:an,rxdate=:rxdate,rxtime=:rxtime,labor_history=:labor_history
                ,intime=:intime,cc=:cc,current_illness=:current_illness,c_chronic=:c_chronic,hos_history=:hos_history,h_sergery=:h_sergery
                ,h_allergy=:h_allergy,history_of_drug=:history_of_drug,pmh2=:pmh2,g=:g,p=:p,l_ga=:l_ga,l_ga_by=:l_ga_by,prenatal_wks=:prenatal_wks,prenatral_count=:prenatral_count
                ,k8=:k8,k8_less=:k8_less,at_=:at_,dt=:dt,dt_needle=:dt_needle,anc_lab_hiv1 =:anc_lab_hiv1,anc_lab_rpr1 =:anc_lab_rpr1
                ,anc_lab_hbsag1 =:anc_lab_hbsag1,anc_lab_hct1 =:anc_lab_hct1,anc_lab_hb1 =:anc_lab_hb1,anc_lab_blgr =:anc_lab_blgr,anc_lab_blgr_rh =:anc_lab_blgr_rh
                ,anc_lab_dcip1=:anc_lab_dcip1,anc_lab_mvc1=:anc_lab_mvc1,anc_lab_hb_typing1=:anc_lab_hb_typing1,anc_lab_hiv2=:anc_lab_hiv2
                ,anc_lab_rpr2=:anc_lab_rpr2,anc_lab_hct2=:anc_lab_hct2,anc_lab_hb2=:anc_lab_hb2,hus_lab_hiv=:hus_lab_hiv,lab_rpr2=:lab_rpr2
                ,lab_dcip2=:lab_dcip2,lab_hb_typing2=:lab_hb_typing2,ma_fa_school=:ma_fa_school,quad_test=:quad_test,other_lab=:other_lab
                ,bt=:bt,pr=:pr,rr=:rr,bps=:bps,bpd=:bpd,sleep_hour=:sleep_hour,pain_area=:pain_area,pain_score=:pain_score
                ,education=:education,ocupation=:ocupation,income=:income,income_enough=:income_enough,caretaker=:caretaker
                ,caretaker_ocupation=:caretaker_ocupation,caretaker_income=:caretaker_income,first_symptoms=:first_symptoms 
                ,bw =:bw,hight=:hight,bw_befor_prenatal=:bw_befor_prenatal,bmi_befor_prenatal=:bmi_befor_prenatal,leukorrhea_history=:leukorrhea_history,behaviors_risk_sexually=:behaviors_risk_sexually 
                ,update_user=:update_user,update_datetime=:update_datetime,version=:version
          WHERE id=:id");
                //execute array
                $stmt->execute(array(
                        'id' => $id, 'an' => $an, 'rxdate' => $rxdate, 'rxtime' => $rxtime, 'labor_history' => $labor_history, 'intime' => $intime, 'cc' => $cc
                        ,'current_illness'=>$current_illness,'c_chronic'=>$c_chronic,'hos_history'=>$hos_history,'h_sergery'=>$h_sergery
                        ,'h_allergy'=>$h_allergy,'history_of_drug'=>$history_of_drug,'pmh2'=>$pmh_2,'g'=>$g,'p'=>$p,'l_ga'=>$l_ga,'l_ga_by'=>$l_ga_by,'prenatal_wks'=>$prenatal_wks,'prenatral_count'=>$prenatral_count
                        ,'k8'=>$k8,'k8_less'=>$k8_less,'at_'=>$at_,'dt'=>$dt,'dt_needle'=>$dt_needle,'anc_lab_hiv1'=>$anc_lab_hiv1,'anc_lab_rpr1' => $anc_lab_rpr1
                        ,'anc_lab_hbsag1' => $anc_lab_hbsag1,'anc_lab_hct1' => $anc_lab_hct1,'anc_lab_hb1' => $anc_lab_hb1,'anc_lab_blgr' => $anc_lab_blgr,'anc_lab_blgr_rh' => $anc_lab_blgr_rh
                        ,'anc_lab_dcip1'=>$anc_lab_dcip1,'anc_lab_mvc1'=>$anc_lab_mvc1,'anc_lab_hb_typing1'=>$anc_lab_hb_typing1,'anc_lab_hiv2'=>$anc_lab_hiv2
                        ,'anc_lab_rpr2'=>$anc_lab_rpr2,'anc_lab_hct2'=>$anc_lab_hct2,'anc_lab_hb2'=>$anc_lab_hb2,'hus_lab_hiv'=>$hus_lab_hiv,'lab_rpr2'=>$lab_rpr2
                        ,'lab_dcip2'=>$lab_dcip2,'lab_hb_typing2'=>$lab_hb_typing2,'ma_fa_school'=>$ma_fa_school,'quad_test'=>$quad_test,'other_lab'=>$other_lab
                        ,'bt'=>$bt,'pr'=>$pr,'rr'=>$rr,'bps'=>$bps,'bpd'=>$bpd,'sleep_hour'=>$sleep_hour,'pain_area'=>$pain_area,'pain_score'=>$pain_score
                        ,'education'=>$education,'ocupation'=>$ocupation,'income'=>$income,'income_enough'=>$income_enough,'caretaker'=>$caretaker
                        ,'caretaker_ocupation'=>$caretaker_ocupation,'caretaker_income'=>$caretaker_income,'first_symptoms'=>$first_symptoms 
                        ,'bw'=>$bw,'hight'=>$hight,'bw_befor_prenatal'=>$bw_befor_prenatal,'bmi_befor_prenatal'=>$bmi_befor_prenatal,'leukorrhea_history'=>$leukorrhea_history,'behaviors_risk_sexually'=>$behaviors_risk_sexually  
                        , 'update_user' => $update_user,  'update_datetime' => $update_datetime, 'version' => $version
                ));

                //  $output_error = $labor_history;

                /*
          if(!empty($_REQUEST['doctor'])){
                $stmt_item_check = "SELECT COUNT(*) AS count_item,max(id) AS count_id FROM ".DbConstant::KPHIS_DBNAME.".prs_pre_nursenote_item WHERE an=:an";
                $stmt_item_check = $conn->prepare($stmt_item_check);
                $stmt_item_check->execute(['an'=>$an]);
                $row_item_check = $stmt_item_check->fetch();
                $count_item = $row_item_check['count_item'];
                $count_id = $row_item_check['count_id'];
                if($count_item > 0){

                    $stmt_item_delete = "DELETE FROM ".DbConstant::KPHIS_DBNAME.".prs_pre_nursenote_item WHERE an=:an";
                    $stmt_item_delete = $conn->prepare($stmt_item_delete);
                    $stmt_item_delete->execute(['an'=>$an]);

                }


                foreach($_REQUEST['doctor'] as $doctor){
                        $stmt_item = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".prs_pre_nursenote_item(pre_nursenote_id,an,doctor,create_user,create_datetime,update_user,update_datetime,version)
                        VALUES (:pre_nursenote_id,:an,:doctor,:create_user,now(),:update_user,now(),:version)");
                        $stmt_item->execute(array('pre_nursenote_id'=>$id,'an'=>$an,'doctor'=>$doctor
                        ,'create_user'=>$create_user, 'update_user'=>$update_user, 'version'=>$version
                        ));
                    } 

                    */

                /* foreach($_REQUEST['admission_note_doctor'] as $admission_note_doctor){
                    $stmt_item = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".ipd_dr_admission_note_item
                    (admission_note_id,an,admission_note_doctor,create_user,create_datetime,update_user,update_datetime,version)
                    VALUES (:admission_note_id,:an,:admission_note_doctor,:create_user,now(),:update_user,now(),:version)");
                    $stmt_item->execute(array('admission_note_id'=>$admission_note_id, 'an'=>$an,
                    'admission_note_doctor'=>$admission_note_doctor,
                    'create_user'=>$create_user, 'update_user'=>$update_user, 'version'=>$version));
                }
                
                
            } */
        } else {

                echo   '<script>
        alert("กรุณากรอกข้อมูลให้ครบถ้วน", "error");     
        </script>';
        }




           Session::insertSystemAccessLog(json_encode(array(
            'form'=>'LR-REPORT2-FORM',
            'action'=>'UPDATE',
            'version'=>$version,
            'an'=>$an,
        ),JSON_UNESCAPED_UNICODE));

        //เมื่อ update สำเร็จ
        // $output_error = '<div class="alert alert-success">บันทึกข้อมูลเรียบร้อยแล้วคะ</div>';


} catch (PDOException  $e) {
        //เมื่อเกิดข้อผิดพลาด
        echo $e->getMessage();
        //$output_error = '<div class="alert alert-danger">ERROR !!</div>';

        $output_error = '<script>NotificationMessage("บันทึกข้อมูลไม่สำเร็จ", "error")</script>';
}

echo $output_error;
