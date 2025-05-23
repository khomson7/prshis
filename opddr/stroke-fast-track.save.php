<?php    
require_once '../include/DbUtils.php';
    require_once '../include/KphisQueryUtils.php';
    require_once '../include/Session.php';
    //เวลาตาม timezone
    date_default_timezone_set("Asia/Bangkok");

    $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล

    $vn = $_REQUEST['vn'];
   
    $hn = KphisQueryUtils::getHnByVn($vn);
    
    $bw = $_REQUEST['bw'];
    $level_of_consciousness = empty($_REQUEST['level_of_consciousness'])? 0 : $_REQUEST['level_of_consciousness'];
    $two_questions = empty($_REQUEST['two_questions'])? 0 :$_REQUEST['two_questions'];
    $two_commands = empty($_REQUEST['two_commands'])? 0 :$_REQUEST['two_commands'];
    $best_gaze = empty($_REQUEST['best_gaze'])? 0 :$_REQUEST['best_gaze'];
    $best_visual_field= empty($_REQUEST['best_visual_field'])? 0 :$_REQUEST['best_visual_field'];
    $facial_palsy= empty($_REQUEST['facial_palsy'])? 0 :$_REQUEST['facial_palsy'];
    $best_moter_left_arm= empty($_REQUEST['best_moter_left_arm'])? 0 :$_REQUEST['best_moter_left_arm'];
    $best_moter_right_arm = empty($_REQUEST['best_moter_right_arm'])? 0 :$_REQUEST['best_moter_right_arm'];
    $best_moter_left_leg = empty($_REQUEST['best_moter_left_leg'])? 0 :$_REQUEST['best_moter_left_leg'];
    $best_moter_right_leg = empty($_REQUEST['best_moter_right_leg'])? 0 :$_REQUEST['best_moter_right_leg'];
    $ataxia = empty($_REQUEST['ataxia'])? 0 :$_REQUEST['ataxia'];
    $sensory = empty($_REQUEST['sensory'])? 0 :$_REQUEST['sensory'];
    $best_language_aphasia = empty($_REQUEST['best_language_aphasia'])? 0 :$_REQUEST['best_language_aphasia'];
    $dysarthria = empty($_REQUEST['dysarthria'])? 0 :$_REQUEST['dysarthria'];
    $neglect = empty($_REQUEST['neglect'])? 0 :$_REQUEST['neglect'];
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

    $total_sum2 = $af_level_of_consciousness+$af_two_questions+$af_two_commands+$af_best_gaze+$af_best_visual_field+$af_facial_palsy+$af_best_moter_left_arm
    +$af_best_moter_right_arm+$af_best_moter_left_leg+$af_best_moter_right_leg
    +$af_ataxia+$af_sensory+$af_best_language_aphasia+$af_dysarthria+$af_neglect;


        
       



        $create_user = $_SESSION['loginname'];
        $nurse_name = empty($_REQUEST['nurse_name']) ? null : $_REQUEST['nurse_name'];
        // $doc_name = empty($_REQUEST['doc_name']) ? null : $_REQUEST['doc_name'];
        $nurse_pos = empty($_REQUEST['nurse_pos']) ? null : $_REQUEST['nurse_pos'];
        //$doc_pos = empty($_REQUEST['doc_pos']) ? null : $_REQUEST['doc_pos'];
        $create_datetime = $_REQUEST['create_datetime'];
        $update_user = $_SESSION['loginname'];
        $update_datetime = $_REQUEST['update_datetime'];
        $version = 1;

        try {

           /* if (  $create_user != ''
    ) */{
    
        $stmt = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".prs_stroke_fast_track(hn,vn,bw
        ,level_of_consciousness,two_questions,two_commands,best_gaze,best_visual_field,facial_palsy,best_moter_left_arm,best_moter_right_arm
        ,best_moter_left_leg,best_moter_right_leg,ataxia,sensory,best_language_aphasia,dysarthria,neglect,total_sum,af_level_of_consciousness,af_two_questions,af_two_commands
        ,af_best_gaze,af_best_visual_field,af_facial_palsy,af_best_moter_left_arm,af_best_moter_right_arm,af_best_moter_left_leg,af_best_moter_right_leg,af_ataxia
        ,af_sensory,af_best_language_aphasia,af_dysarthria,af_neglect,check_age_18,check_45_onset,nihss,ct_brain_no_hemo,unknown_time,bp,seizure,plasma_glucose
        ,minor,hx_of_ich,cva,bleeding,surgery,puncture,noacs,enoxaparin,inr,infective_endocarditis,aortic_dissection,ich,injury,total_sum2
        ,create_user,version,create_datetime,update_user,update_datetime)
        VALUES(:hn,:vn,:bw
        ,:level_of_consciousness,:two_questions,:two_commands,:best_gaze,:best_visual_field,:facial_palsy,:best_moter_left_arm,:best_moter_right_arm
        ,:best_moter_left_leg,:best_moter_right_leg,:ataxia,:sensory,:best_language_aphasia,:dysarthria,:neglect,:total_sum,:af_level_of_consciousness,:af_two_questions,:af_two_commands
       ,:af_best_gaze,:af_best_visual_field,:af_facial_palsy,:af_best_moter_left_arm,:af_best_moter_right_arm,:af_best_moter_left_leg,:af_best_moter_right_leg,:af_ataxia
       ,:af_sensory,:af_best_language_aphasia,:af_dysarthria,:af_neglect,:check_age_18,:check_45_onset,:nihss,:ct_brain_no_hemo,:unknown_time,:bp,:seizure,:plasma_glucose
       ,:minor,:hx_of_ich,:cva,:bleeding,:surgery,:puncture,:noacs,:enoxaparin,:inr,:infective_endocarditis,:aortic_dissection,:ich,:injury,:total_sum2
        ,:create_user,:version,now(),:update_user,now())");
        
                $stmt->execute(array('hn'=>$hn,'vn'=>$vn,'bw'=>$bw
                ,'level_of_consciousness'=>$level_of_consciousness,'two_questions'=>$two_questions,'two_commands'=>$two_commands,'best_gaze'=>$best_gaze
                ,'best_visual_field'=>$best_visual_field,'facial_palsy'=>$facial_palsy,'best_moter_left_arm'=>$best_moter_left_arm,'best_moter_right_arm'=>$best_moter_right_arm
                ,'best_moter_left_leg'=>$best_moter_left_leg,'best_moter_right_leg'=>$best_moter_right_leg,'ataxia'=>$ataxia,'sensory'=>$sensory,'best_language_aphasia'=>$best_language_aphasia
                ,'dysarthria'=>$dysarthria,'neglect'=>$neglect,'total_sum'=>$total_sum,'af_level_of_consciousness'=>$af_level_of_consciousness,'af_two_questions'=>$af_two_questions,'af_two_commands'=>$af_two_commands
                ,'af_best_gaze'=>$af_best_gaze,'af_best_visual_field'=>$af_best_visual_field,'af_facial_palsy'=>$af_facial_palsy,'af_best_moter_left_arm'=>$af_best_moter_left_arm
                ,'af_best_moter_right_arm'=>$af_best_moter_right_arm,'af_best_moter_left_leg'=>$af_best_moter_left_leg,'af_best_moter_right_leg'=>$af_best_moter_right_leg,'af_ataxia'=>$af_ataxia
                ,'af_sensory'=>$af_sensory,'af_best_language_aphasia'=>$af_best_language_aphasia,'af_dysarthria'=>$af_dysarthria,'af_neglect'=>$af_neglect,'check_age_18'=>$check_age_18
                ,'check_45_onset'=>$check_45_onset,'nihss'=>$nihss,'ct_brain_no_hemo'=>$ct_brain_no_hemo,'unknown_time'=>$unknown_time,'bp'=>$bp,'seizure'=>$seizure,'plasma_glucose'=>$plasma_glucose
                ,'minor'=>$minor,'hx_of_ich'=>$hx_of_ich,'cva'=>$cva,'bleeding'=>$bleeding,'surgery'=>$surgery,'puncture'=>$puncture,'noacs'=>$noacs,'enoxaparin'=>$enoxaparin,'inr'=>$inr
                ,'infective_endocarditis'=>$infective_endocarditis,'aortic_dissection'=>$aortic_dissection,'ich'=>$ich,'injury'=>$injury,'total_sum2'=>$total_sum2
                ,'create_user'=>$create_user,'version'=>$version,'update_user'=>$create_user));
    
                $output_error = '<script>
            NotificationMessage("บันทึกข้อมูลสำเร็จ", "success");
            </script>';
    
    
    
            }

              
               // $output_error = '<div class="alert alert-success">บันทึกข้อมูลเรียบร้อยแล้วคะ</div>';

            } catch (PDOException  $e) {
                echo $e->getMessage();
                $output_error = '<div class="alert alert-danger">ERROR !!</div>';
            }
        echo $output_error;
?>
