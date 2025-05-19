<?php require_once '../include/Session.php';
Session::checkLoginSessionAndShowMessage(); //เช็ค session
if (!(Session::checkPermission('ADMISSION_NOTE', 'ADD'))) {
    return;
}
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';

$conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล

$id = $_REQUEST['id'];
$vn = $_REQUEST['vn'];
$hn = KphisQueryUtils::getHnByVn($vn);

$bw = $_REQUEST['bw'];
$level_of_consciousness = $_REQUEST['level_of_consciousness'];
$two_questions = $_REQUEST['two_questions'];
$two_commands = $_REQUEST['two_commands'];
$best_gaze = $_REQUEST['best_gaze'];
$best_visual_field= $_REQUEST['best_visual_field'];
$facial_palsy= $_REQUEST['facial_palsy'];
$best_moter_left_arm= $_REQUEST['best_moter_left_arm'];
$best_moter_right_arm = $_REQUEST['best_moter_right_arm'];
$best_moter_left_leg = $_REQUEST['best_moter_left_leg'];
$best_moter_right_leg = $_REQUEST['best_moter_right_leg'];
$ataxia = $_REQUEST['ataxia'];
$sensory = $_REQUEST['sensory'];
$best_language_aphasia = $_REQUEST['best_language_aphasia'];
$dysarthria = $_REQUEST['dysarthria'];
$neglect = $_REQUEST['neglect'];
$af_level_of_consciousness = $_REQUEST['af_level_of_consciousness'];
$af_two_questions = $_REQUEST['af_two_questions'];
$af_two_commands = $_REQUEST['af_two_commands'];
$af_best_gaze = $_REQUEST['af_best_gaze'];
$af_best_visual_field= $_REQUEST['af_best_visual_field'];
$af_facial_palsy= $_REQUEST['af_facial_palsy'];
$af_best_moter_left_arm= $_REQUEST['af_best_moter_left_arm'];
$af_best_moter_right_arm = $_REQUEST['af_best_moter_right_arm'];
$af_best_moter_left_leg = $_REQUEST['af_best_moter_left_leg'];
$af_best_moter_right_leg = $_REQUEST['af_best_moter_right_leg'];
$af_ataxia = $_REQUEST['af_ataxia'];
$af_sensory = $_REQUEST['af_sensory'];
$af_best_language_aphasia = $_REQUEST['af_best_language_aphasia'];
$af_dysarthria = $_REQUEST['af_dysarthria'];
$af_neglect = $_REQUEST['af_neglect'];
$check_age_18 = $_REQUEST['check_age_18'];
$check_45_onset = $_REQUEST['check_45_onset'];
$nihss = $_REQUEST['nihss'];
$ct_brain_no_hemo = $_REQUEST['ct_brain_no_hemo'];
$unknown_time = $_REQUEST['unknown_time'];
$bp = $_REQUEST['bp'];
$seizure = $_REQUEST['seizure'];
$plasma_glucose = $_REQUEST['plasma_glucose'];
$minor = $_REQUEST['minor'];
$hx_of_ich = $_REQUEST['hx_of_ich'];
$cva = $_REQUEST['cva'];
$bleeding = $_REQUEST['bleeding'];
$surgery = $_REQUEST['surgery'];
$puncture = $_REQUEST['puncture'];
$noacs = $_REQUEST['noacs'];
$enoxaparin = $_REQUEST['enoxaparin'];
$inr = $_REQUEST['inr']; 
$infective_endocarditis = $_REQUEST['infective_endocarditis'];
$aortic_dissection = $_REQUEST['aortic_dissection'];
$ich = $_REQUEST['ich'];
$injury = $_REQUEST['injury'];

$total_sum = $level_of_consciousness+$two_questions+$two_commands+$best_gaze+$best_visual_field+$facial_palsy+$best_moter_left_arm+$best_moter_right_arm+$best_moter_left_leg+$best_moter_right_leg
+$ataxia+$sensory+$best_language_aphasia+$dysarthria+$neglect;


$create_user = $_SESSION['loginname'];

$update_user = $_SESSION['loginname'];

$version = $_REQUEST['version'];

$version = $version + 1;


//echo $id;

try {

    if ( $vn != '' 
) {
        $output_error = '<script>
        NotificationMessage("บันทึกข้อมูลสำเร็จ", "success");     
        </script>';

        $stmt = $conn->prepare("UPDATE " . DbConstant::KPHIS_DBNAME . ".prs_stroke_fast_track SET hn=:hn, vn=:vn,bw=:bw
        ,level_of_consciousness=:level_of_consciousness,two_questions=:two_questions,two_commands=:two_commands,best_gaze=:best_gaze
        ,best_visual_field=:best_visual_field,facial_palsy=:facial_palsy,best_moter_left_arm=:best_moter_left_arm,best_moter_right_arm=:best_moter_right_arm
        ,best_moter_left_leg=:best_moter_left_leg,best_moter_right_leg=:best_moter_right_leg,ataxia=:ataxia,sensory=:sensory
        ,best_language_aphasia=:best_language_aphasia,dysarthria=:dysarthria,neglect=:neglect
        ,af_level_of_consciousness=:af_level_of_consciousness,af_two_questions=:af_two_questions,af_two_commands=:af_two_commands,af_best_gaze=:af_best_gaze
        ,af_best_visual_field=:af_best_visual_field,af_facial_palsy=:af_facial_palsy ,af_best_moter_left_arm=:af_best_moter_left_arm,af_best_moter_right_arm=:af_best_moter_right_arm
        ,af_best_moter_left_leg=:af_best_moter_left_leg,af_best_moter_right_leg=:af_best_moter_right_leg,af_ataxia=:af_ataxia,af_sensory=:af_sensory
        ,af_best_language_aphasia=:af_best_language_aphasia,af_dysarthria=:af_dysarthria,af_neglect=:af_neglect,check_age_18=:check_age_18
        ,check_45_onset=:check_45_onset,nihss=:nihss,ct_brain_no_hemo=:ct_brain_no_hemo,unknown_time=:unknown_time,bp=:bp
        ,seizure=:seizure,plasma_glucose=:plasma_glucose,minor=:minor,hx_of_ich=:hx_of_ich,cva=:cva,bleeding=:bleeding,surgery=:surgery
        ,puncture=:puncture,noacs=:noacs,enoxaparin=:enoxaparin,inr=:inr,infective_endocarditis=:infective_endocarditis,aortic_dissection=:aortic_dissection
        ,ich=:ich,injury=:injury,total_sum=:total_sum 
        ,update_user=:update_user,version=:version,update_datetime = NOW()
                WHERE id=:id");
        $stmt->execute(array('id' => $id,'hn' => $hn,'vn' => $vn,'vn' => $vn, 'bw' =>$bw
        , 'level_of_consciousness' =>$level_of_consciousness,'two_questions'=>$two_questions,'two_commands'=>$two_commands,'best_gaze'=>$best_gaze
        ,'best_visual_field'=>$best_visual_field,'facial_palsy'=>$facial_palsy,'best_moter_left_arm'=>$best_moter_left_arm,'best_moter_right_arm'=>$best_moter_right_arm
        ,'best_moter_left_leg'=>$best_moter_left_leg,'best_moter_right_leg'=>$best_moter_right_leg,'ataxia'=>$ataxia,'sensory'=>$sensory
        ,'best_language_aphasia' => $best_language_aphasia,'dysarthria'=>$dysarthria,'neglect'=>$neglect
        , 'af_level_of_consciousness' =>$af_level_of_consciousness,'af_two_questions'=>$af_two_questions,'af_two_commands'=>$af_two_commands,'af_best_gaze'=>$af_best_gaze
        ,'af_best_visual_field'=>$af_best_visual_field,'af_facial_palsy'=>$af_facial_palsy ,'af_best_moter_left_arm'=>$af_best_moter_left_arm,'af_best_moter_right_arm'=>$af_best_moter_right_arm
        ,'af_best_moter_left_leg'=>$af_best_moter_left_leg,'af_best_moter_right_leg'=>$af_best_moter_right_leg,'af_ataxia'=>$af_ataxia,'af_sensory'=>$af_sensory
        ,'af_best_language_aphasia' => $af_best_language_aphasia,'af_dysarthria'=>$af_dysarthria,'af_neglect'=>$af_neglect,'check_age_18'=>$check_age_18
        ,'check_45_onset'=>$check_45_onset,'nihss'=>$nihss,'ct_brain_no_hemo'=>$ct_brain_no_hemo,'unknown_time'=>$unknown_time,'bp'=>$bp
        ,'seizure'=>$seizure,'plasma_glucose'=>$plasma_glucose,'minor'=>$minor,'hx_of_ich'=>$hx_of_ich,'cva'=>$cva,'bleeding'=>$bleeding,'surgery'=>$surgery
        ,'puncture'=>$puncture,'noacs'=>$noacs,'enoxaparin'=>$enoxaparin,'inr'=>$inr,'infective_endocarditis'=>$infective_endocarditis,'aortic_dissection'=>$aortic_dissection
        ,'ich'=>$ich,'injury'=>$injury,'total_sum'=>$total_sum
        ,'update_user' => $update_user,'version' => $version
        ));
    } else {


    echo   '<script>
     alert("กรุณากรอกข้อมูลให้ครบถ้วน", "error");     
     </script>'; 
}




    /*          if(!empty($_REQUEST['admission_note_doctor'])){
                    $stmt_item_check = "SELECT COUNT(*) AS count_item  FROM ".DbConstant::KPHIS_DBNAME.".prs_trauma_note_item WHERE an=:an";
                    $stmt_item_check = $conn->prepare($stmt_item_check);
                    $stmt_item_check->execute(['an'=>$an]);
                    $row_item_check = $stmt_item_check->fetch();
                    $count_item = $row_item_check['count_item'];
                    if($count_item > 0){
                        $stmt_item_delete = "DELETE FROM ".DbConstant::KPHIS_DBNAME.".prs_trauma_note_item WHERE an=:an";
                        $stmt_item_delete = $conn->prepare($stmt_item_delete);
                        $stmt_item_delete->execute(['an'=>$an]);
                    }
                    foreach($_REQUEST['admission_note_doctor'] as $admission_note_doctor){
                        $stmt_item = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".prs_trauma_note_item
                        (admission_note_id,an,admission_note_doctor,create_user,create_datetime,update_user,update_datetime,version)
                        VALUES (:admission_note_id,:an,:admission_note_doctor,:create_user,now(),:update_user,now(),:version)");
                        $stmt_item->execute(array('admission_note_id'=>$admission_note_id, 'an'=>$an,
                        'admission_note_doctor'=>$admission_note_doctor,
                        'create_user'=>$create_user, 'update_user'=>$update_user, 'version'=>$version));
                    }
                } 
*/


   
                Session::insertSystemAccessLog(json_encode(array(
                    'form'=>'STROKE-FAST-TRACK-FORM',
                    'action'=>'UPDATE',
                    'vn'=>$vn,
                ),JSON_UNESCAPED_UNICODE));


    //  $output_error = '<div class="alert alert-success">บันทึกข้อมูลเรียบร้อยแล้วคะ</div>';

} catch (PDOException  $e) {
    echo $e->getMessage();
    $output_error = '<div class="alert alert-danger">ERROR !!</div>';
    // $output_error = '<script>NotificationMessage("บันทึกข้อมูลไม่สำเร็จ", "error")</script>';
}

echo $output_error;
