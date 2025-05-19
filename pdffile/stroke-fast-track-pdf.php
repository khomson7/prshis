<?php   

require_once '../include/Session.php';
   //ตรวจสอบว่า session login ตรงกันหรือไม่
        
             
   $login = empty($_REQUEST['loginname']) ? null : $_REQUEST['loginname'];
   $loginname = $_SESSION['loginname'];
   $values =['loginname'=>$loginname];
   
   //หากพบว่าไม่ตรงกันให้ ทำลาย session เดิมทิ้งไป
   if(!$loginname){
    session_start();
    session_destroy();              
        
  } 

  Session::checkLoginSessionAndShowMessage(); //เช็ค session
  Session::checkPermissionAndShowMessage('DOCUMENT', 'PRINT');

  require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
  require_once '../include/DbUtils.php';
  require_once '../include/Session.php';
  require_once '../include/KphisQueryUtils.php';
        $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
        $an = $_REQUEST['an'];
        //$vn = $_REQUEST['vn'];
        $hn1 = KphisQueryUtils::getHnByVn($vn); 
        $hn = KphisQueryUtils::getHnByAn($an);   // function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา

        $vn = empty($_REQUEST['vn']) ? null : $_REQUEST['vn'];
$hn = KphisQueryUtils::getHnByAn($an);
$query_parameters = ['vn' => $vn];
       // require_once __DIR__ . '../vendor/autoload.php';
        date_default_timezone_set('asia/bangkok');
        $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
        $mpdf->AddPageByArray([
            'margin-left' => 8,
            'margin-right' => 8,
            'margin-top' => 8,
            'margin-bottom' => 5,
        ]);

        Session::insertSystemAccessLog(json_encode(array(
            'report'=>'IPD-NURSE-ADMISSION-NOTE-PDF',
           // 'action'=>'PRINT',
            'an'=>$an,
        ),JSON_UNESCAPED_UNICODE));

  

        //----------------------select ข้อมูล จากฐานข้อมูลเพื่อค้นหา ชื่อ - สกุล
        $image_uncheck = "<img src='../include/images/check-adm.jpg' width='1.6%' class='check_img'>";
        $image_check = "<img src='../include/images/check-adm-1.png' width='1.6%' class='check_img'>";
        $query_parameters_REQUEST = ['an'=>$an];
        $sql_ipt = "select patient.hn,patient.pname,patient.fname,patient.lname,patient.drugallergy,
                    an_stat.age_y,an_stat.age_m,an_stat.age_d,
                    ipt.regdate,ipt.regtime,ipt.ward,
                    ipt.pttype,
                    pttype.`name` as pttype_name,
                    ward.shortname
                    from ".DbConstant::HOSXP_DBNAME.".ipt
                    left outer join ".DbConstant::HOSXP_DBNAME.".an_stat on an_stat.an=ipt.an
                    left outer join ".DbConstant::HOSXP_DBNAME.".patient on patient.hn=ipt.hn
                    left outer join ".DbConstant::HOSXP_DBNAME.".ward on ward.ward=ipt.ward
                    LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".pttype ON pttype.pttype = ipt.pttype
                    WHERE ipt.an=:an";
        $stmt_ipt = $conn->prepare($sql_ipt);
        $stmt_ipt->execute($query_parameters_REQUEST);
        $row_iptCount = 0;
        while ($row_ipt = $stmt_ipt->fetch()){
            $hn_row_ipt = htmlspecialchars($row_ipt['hn']);
            $pname_row_ipt = htmlspecialchars($row_ipt['pname']);
            $fname_row_ipt = htmlspecialchars($row_ipt['fname']);
            $lname_row_ipt = htmlspecialchars($row_ipt['lname']);
        }
       // $mpdf->setFooter(' (พิมพ์โดย '.$_SESSION['name'].' วันที่พิมพ์ '.date('d/m/Y H:i').' ) '.'<br>ผู้ป่วย : (hn : '.$hn_row_ipt.')(an : '.$an.')(ชื่อ - สกุล : '.$pname_row_ipt.' '.$fname_row_ipt.' '.$lname_row_ipt.')'.' ( Page {PAGENO} of {nb} )');
        $mpdf->WriteHTML('');
        //----------------------select ข้อมูล จากฐานข้อมูลเพื่อค้นหา ชื่อ - สกุล
        //----------------------ipd_nurse_admission_note
        $sql = "SELECT nurse_adm.* ,opduser.name AS name_full, entryposition
                FROM ".DbConstant::KPHIS_DBNAME.".ipd_nurse_admission_note nurse_adm
                LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".opduser ON nurse_adm.update_user = opduser.loginname
                WHERE an=:an";
        $parameters['an'] = $an;
        $stmt = $conn->prepare($sql);
        $stmt->execute($parameters);
        $row = $stmt->fetch();

        //echo $hn;

   /*   $sql2 = "SELECT st.* 
        FROM ".DbConstant::KPHIS_DBNAME.".prs_stroke_fast_track st
        LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".opduser ON st.update_user = opduser.loginname
        WHERE vn = :vn";
$parameters['vn'] = $vn;
$stmt = $conn->prepare($sql2);
$stmt->execute($parameters);
$row2 = $stmt->fetch();
*/

$sql2 = "SELECT st.*,concat(patient.pname,patient.fname,' ',patient.lname) as ptname,vn.age_y,o.name as doctor_name
        FROM ".DbConstant::KPHIS_DBNAME.".prs_stroke_fast_track st
        left outer join ".DbConstant::HOSXP_DBNAME.".vn_stat vn on vn.vn=st.vn
        left outer join ".DbConstant::HOSXP_DBNAME.".patient on patient.hn=st.hn
        left outer join ".DbConstant::HOSXP_DBNAME.".opduser o on o.loginname=st.update_user
        WHERE st.vn = :vn
        limit 1";
$stmt = $conn->prepare($sql2);
$stmt->execute($query_parameters);
$row2  = $stmt->fetch();

$conscious  =  $row2['level_of_consciousness'];
if($conscious == "0"){$conscious_0 = $image_check;}else{$conscious_0 = $image_uncheck;}
if($conscious == "1"){$conscious_1 = $image_check;}else{$conscious_1 = $image_uncheck;}
if($conscious == "2"){$conscious_2 = $image_check;}else{$conscious_2 = $image_uncheck;}
if($conscious == "3"){$conscious_3 = $image_check;}else{$conscious_3 = $image_uncheck;}

$two_questions  =  $row2['two_questions'];
if($two_questions == "0"){$two_questions_0 = $image_check;}else{$two_questions_0 = $image_uncheck;}
if($two_questions== "1"){$two_questions_1 = $image_check;}else{$two_questions_1 = $image_uncheck;}
if($two_questions== "2"){$two_questions_2 = $image_check;}else{$two_questions_2 = $image_uncheck;}

$two_commands  =  $row2['two_commands'];
if($two_commands == "0"){$two_commands_0 = $image_check;}else{$two_commands_0 = $image_uncheck;}
if($two_commands== "1"){$two_commands_1 = $image_check;}else{$two_commands_1 = $image_uncheck;}
if($two_commands== "2"){$two_commands_2 = $image_check;}else{$two_commands_2 = $image_uncheck;}

$best_gaze =  $row2['best_gaze'];
if($best_gaze == "0"){$best_gaze_0 = $image_check;}else{$best_gaze_0 = $image_uncheck;}
if($best_gaze== "1"){$best_gaze_1 = $image_check;}else{$best_gaze_1 = $image_uncheck;}
if($best_gaze== "2"){$best_gaze_2 = $image_check;}else{$best_gaze_2 = $image_uncheck;}

$best_visual_field =  $row2['best_visual_field'];
if($best_visual_field == "0"){$best_visual_field_0 = $image_check;}else{$best_visual_field_0 = $image_uncheck;}
if($best_visual_field== "1"){$best_visual_field_1 = $image_check;}else{$best_visual_field_1 = $image_uncheck;}
if($best_visual_field== "2"){$best_visual_field_2 = $image_check;}else{$best_visual_field_2 = $image_uncheck;}
if($best_visual_field== "3"){$best_visual_field_3 = $image_check;}else{$best_visual_field_3 = $image_uncheck;}

$facial_palsy =  $row2['facial_palsy'];
if($facial_palsy == "0"){$facial_palsy_0 = $image_check;}else{$facial_palsy_0 = $image_uncheck;}
if($facial_palsy== "1"){$facial_palsy_1 = $image_check;}else{$facial_palsy_1 = $image_uncheck;}
if($facial_palsy== "2"){$facial_palsy_2 = $image_check;}else{$facial_palsy_2 = $image_uncheck;}
if($facial_palsy== "3"){$facial_palsy_3 = $image_check;}else{$facial_palsy_3 = $image_uncheck;}

$best_moter_left_arm =  $row2['best_moter_left_arm'];
if($best_moter_left_arm  == "0"){$best_moter_left_arm_0 = $image_check;}else{$best_moter_left_arm_0 = $image_uncheck;}
if($best_moter_left_arm == "1"){$best_moter_left_arm_1 = $image_check;}else{$best_moter_left_arm_1 = $image_uncheck;}
if($best_moter_left_arm == "2"){$best_moter_left_arm_2 = $image_check;}else{$best_moter_left_arm_2 = $image_uncheck;}
if($best_moter_left_arm == "3"){$best_moter_left_arm_3 = $image_check;}else{$best_moter_left_arm_3 = $image_uncheck;}

$best_moter_right_arm =  $row2['best_moter_right_arm'];
if($best_moter_right_arm  == "0"){$best_moter_right_arm_0 = $image_check;}else{$best_moter_right_arm_0 = $image_uncheck;}
if($best_moter_right_arm == "1"){$best_moter_right_arm_1 = $image_check;}else{$best_moter_right_arm_1 = $image_uncheck;}
if($best_moter_right_arm == "2"){$best_moter_right_arm_2 = $image_check;}else{$best_moter_right_arm_2 = $image_uncheck;}
if($best_moter_right_arm == "3"){$best_moter_right_arm_3 = $image_check;}else{$best_moter_right_arm_3 = $image_uncheck;}

$best_moter_left_leg =  $row2['best_moter_left_leg'];
if($best_moter_left_leg  == "0"){$best_moter_left_leg_0 = $image_check;}else{$best_moter_left_leg_0 = $image_uncheck;}
if($best_moter_left_leg == "1"){$best_moter_left_leg_1 = $image_check;}else{$best_moter_left_leg_1 = $image_uncheck;}
if($best_moter_left_leg == "2"){$best_moter_left_leg_2 = $image_check;}else{$best_moter_left_leg_2 = $image_uncheck;}
if($best_moter_left_leg == "3"){$best_moter_left_leg_3 = $image_check;}else{$best_moter_left_leg_3 = $image_uncheck;}

$best_moter_right_leg =  $row2['best_moter_right_leg'];
if($best_moter_right_leg  == "0"){$best_moter_right_leg_0 = $image_check;}else{$best_moter_right_leg_0 = $image_uncheck;}
if($best_moter_right_leg == "1"){$best_moter_right_leg_1 = $image_check;}else{$best_moter_right_leg_1 = $image_uncheck;}
if($best_moter_right_leg == "2"){$best_moter_right_leg_2 = $image_check;}else{$best_moter_right_leg_2 = $image_uncheck;}
if($best_moter_right_leg == "3"){$best_moter_right_leg_3 = $image_check;}else{$best_moter_right_leg_3 = $image_uncheck;}

$ataxia =  $row2['ataxia'];
if($ataxia  == "0"){$ataxia_0 = $image_check;}else{$ataxia_0 = $image_uncheck;}
if($ataxia == "1"){$ataxia_1 = $image_check;}else{$ataxia_1 = $image_uncheck;}
if($ataxia == "2"){$ataxia_2 = $image_check;}else{$ataxia_2 = $image_uncheck;}
if($ataxia == "3"){$ataxia_3 = $image_check;}else{$ataxia_3 = $image_uncheck;}

$sensory =  $row2['sensory'];
if($sensory  == "0"){$sensory_0 = $image_check;}else{$sensory_0 = $image_uncheck;}
if($sensory == "1"){$sensory_1 = $image_check;}else{$sensory_1 = $image_uncheck;}
if($sensory == "2"){$sensory_2 = $image_check;}else{$sensory_2 = $image_uncheck;}
if($sensory == "3"){$sensory_3 = $image_check;}else{$sensory_3 = $image_uncheck;}

$best_language_aphasia =  $row2['best_language_aphasia'];
if($best_language_aphasia  == "0"){$best_language_aphasia_0 = $image_check;}else{$best_language_aphasia_0 = $image_uncheck;}
if($best_language_aphasia == "1"){$best_language_aphasia_1 = $image_check;}else{$best_language_aphasia_1 = $image_uncheck;}
if($best_language_aphasia == "2"){$best_language_aphasia_2 = $image_check;}else{$best_language_aphasia_2 = $image_uncheck;}
if($best_language_aphasia == "3"){$best_language_aphasia_3 = $image_check;}else{$best_language_aphasia_3 = $image_uncheck;}

$dysarthria =  $row2['dysarthria'];
if($dysarthria  == "0"){$dysarthria_0 = $image_check;}else{$dysarthria_0 = $image_uncheck;}
if($dysarthria == "1"){$dysarthria_1 = $image_check;}else{$dysarthria_1 = $image_uncheck;}
if($dysarthria == "2"){$dysarthria_2 = $image_check;}else{$dysarthria_2 = $image_uncheck;}
if($dysarthria == "3"){$dysarthria_3 = $image_check;}else{$dysarthria_3 = $image_uncheck;}

$neglect=  $row2['neglect'];
if($neglect  == "0"){$neglect_0 = $image_check;}else{$neglect_0 = $image_uncheck;}
if($neglect == "1"){$neglect_1 = $image_check;}else{$neglect_1 = $image_uncheck;}
if($neglect == "2"){$neglect_2 = $image_check;}else{$neglect_2 = $image_uncheck;}
if($neglect == "3"){$neglect_3 = $image_check;}else{$neglect_3 = $image_uncheck;}

$af_conscious  =  $row2['af_level_of_consciousness'];
if($af_conscious == "0"){$af_conscious_0 = $image_check;}else{$af_conscious_0 = $image_uncheck;}
if($af_conscious == "1"){$af_conscious_1 = $image_check;}else{$af_conscious_1 = $image_uncheck;}
if($af_conscious == "2"){$af_conscious_2 = $image_check;}else{$af_conscious_2 = $image_uncheck;}
if($af_conscious == "3"){$af_conscious_3 = $image_check;}else{$af_conscious_3 = $image_uncheck;}

$af_two_questions  =  $row2['af_two_questions'];
if($af_two_questions == "0"){$af_two_questions_0 = $image_check;}else{$af_two_questions_0 = $image_uncheck;}
if($af_two_questions== "1"){$af_two_questions_1 = $image_check;}else{$af_two_questions_1 = $image_uncheck;}
if($af_two_questions== "2"){$af_two_questions_2 = $image_check;}else{$af_two_questions_2 = $image_uncheck;}

$af_two_commands  =  $row2['af_two_commands'];
if($af_two_commands == "0"){$af_two_commands_0 = $image_check;}else{$af_two_commands_0 = $image_uncheck;}
if($af_two_commands== "1"){$af_two_commands_1 = $image_check;}else{$af_two_commands_1 = $image_uncheck;}
if($af_two_commands== "2"){$af_two_commands_2 = $image_check;}else{$af_two_commands_2 = $image_uncheck;}

$af_best_gaze =  $row2['af_best_gaze'];
if($af_best_gaze == "0"){$af_best_gaze_0 = $image_check;}else{$af_best_gaze_0 = $image_uncheck;}
if($af_best_gaze== "1"){$af_best_gaze_1 = $image_check;}else{$af_best_gaze_1 = $image_uncheck;}
if($af_best_gaze== "2"){$af_best_gaze_2 = $image_check;}else{$af_best_gaze_2 = $image_uncheck;}

$af_best_visual_field =  $row2['af_best_visual_field'];
if($af_best_visual_field == "0"){$af_best_visual_field_0 = $image_check;}else{$af_best_visual_field_0 = $image_uncheck;}
if($af_best_visual_field== "1"){$af_best_visual_field_1 = $image_check;}else{$af_best_visual_field_1 = $image_uncheck;}
if($af_best_visual_field== "2"){$af_best_visual_field_2 = $image_check;}else{$af_best_visual_field_2 = $image_uncheck;}
if($af_best_visual_field== "3"){$af_best_visual_field_3 = $image_check;}else{$af_best_visual_field_3 = $image_uncheck;}

$af_facial_palsy =  $row2['af_facial_palsy'];
if($af_facial_palsy == "0"){$af_facial_palsy_0 = $image_check;}else{$af_facial_palsy_0 = $image_uncheck;}
if($af_facial_palsy== "1"){$af_facial_palsy_1 = $image_check;}else{$af_facial_palsy_1 = $image_uncheck;}
if($af_facial_palsy== "2"){$af_facial_palsy_2 = $image_check;}else{$af_facial_palsy_2 = $image_uncheck;}
if($af_facial_palsy== "3"){$af_facial_palsy_3 = $image_check;}else{$af_facial_palsy_3 = $image_uncheck;}

$af_best_moter_left_arm =  $row2['af_best_moter_left_arm'];
if($af_best_moter_left_arm  == "0"){$af_best_moter_left_arm_0 = $image_check;}else{$af_best_moter_left_arm_0 = $image_uncheck;}
if($af_best_moter_left_arm == "1"){$af_best_moter_left_arm_1 = $image_check;}else{$af_best_moter_left_arm_1 = $image_uncheck;}
if($af_best_moter_left_arm == "2"){$af_best_moter_left_arm_2 = $image_check;}else{$af_best_moter_left_arm_2 = $image_uncheck;}
if($af_best_moter_left_arm == "3"){$af_best_moter_left_arm_3 = $image_check;}else{$af_best_moter_left_arm_3 = $image_uncheck;}

$af_best_moter_right_arm =  $row2['af_best_moter_right_arm'];
if($af_best_moter_right_arm  == "0"){$af_best_moter_right_arm_0 = $image_check;}else{$af_best_moter_right_arm_0 = $image_uncheck;}
if($af_best_moter_right_arm == "1"){$af_best_moter_right_arm_1 = $image_check;}else{$af_best_moter_right_arm_1 = $image_uncheck;}
if($af_best_moter_right_arm == "2"){$af_best_moter_right_arm_2 = $image_check;}else{$af_best_moter_right_arm_2 = $image_uncheck;}
if($af_best_moter_right_arm == "3"){$af_best_moter_right_arm_3 = $image_check;}else{$af_best_moter_right_arm_3 = $image_uncheck;}

$af_best_moter_left_leg =  $row2['af_best_moter_left_leg'];
if($af_best_moter_left_leg  == "0"){$af_best_moter_left_leg_0 = $image_check;}else{$af_best_moter_left_leg_0 = $image_uncheck;}
if($af_best_moter_left_leg == "1"){$af_best_moter_left_leg_1 = $image_check;}else{$af_best_moter_left_leg_1 = $image_uncheck;}
if($af_best_moter_left_leg == "2"){$af_best_moter_left_leg_2 = $image_check;}else{$af_best_moter_left_leg_2 = $image_uncheck;}
if($af_best_moter_left_leg == "3"){$af_best_moter_left_leg_3 = $image_check;}else{$af_best_moter_left_leg_3 = $image_uncheck;}

$af_best_moter_right_leg =  $row2['af_best_moter_right_leg'];
if($af_best_moter_right_leg  == "0"){$af_best_moter_right_leg_0 = $image_check;}else{$af_best_moter_right_leg_0 = $image_uncheck;}
if($af_best_moter_right_leg == "1"){$af_best_moter_right_leg_1 = $image_check;}else{$af_best_moter_right_leg_1 = $image_uncheck;}
if($af_best_moter_right_leg == "2"){$af_best_moter_right_leg_2 = $image_check;}else{$af_best_moter_right_leg_2 = $image_uncheck;}
if($af_best_moter_right_leg == "3"){$af_best_moter_right_leg_3 = $image_check;}else{$af_best_moter_right_leg_3 = $image_uncheck;}

$af_ataxia =  $row2['af_ataxia'];
if($af_ataxia  == "0"){$af_ataxia_0 = $image_check;}else{$af_ataxia_0 = $image_uncheck;}
if($af_ataxia == "1"){$af_ataxia_1 = $image_check;}else{$af_ataxia_1 = $image_uncheck;}
if($af_ataxia == "2"){$af_ataxia_2 = $image_check;}else{$af_ataxia_2 = $image_uncheck;}
if($af_ataxia == "3"){$af_ataxia_3 = $image_check;}else{$af_ataxia_3 = $image_uncheck;}

$af_sensory =  $row2['af_sensory'];
if($af_sensory  == "0"){$af_sensory_0 = $image_check;}else{$af_sensory_0 = $image_uncheck;}
if($af_sensory == "1"){$af_sensory_1 = $image_check;}else{$af_sensory_1 = $image_uncheck;}
if($af_sensory == "2"){$af_sensory_2 = $image_check;}else{$af_sensory_2 = $image_uncheck;}
if($af_sensory == "3"){$af_sensory_3 = $image_check;}else{$af_sensory_3 = $image_uncheck;}

$af_best_language_aphasia =  $row2['af_best_language_aphasia'];
if($af_best_language_aphasia  == "0"){$af_best_language_aphasia_0 = $image_check;}else{$af_best_language_aphasia_0 = $image_uncheck;}
if($af_best_language_aphasia == "1"){$af_best_language_aphasia_1 = $image_check;}else{$af_best_language_aphasia_1 = $image_uncheck;}
if($af_best_language_aphasia == "2"){$af_best_language_aphasia_2 = $image_check;}else{$af_best_language_aphasia_2 = $image_uncheck;}
if($af_best_language_aphasia == "3"){$af_best_language_aphasia_3 = $image_check;}else{$af_best_language_aphasia_3 = $image_uncheck;}

$af_dysarthria =  $row2['af_dysarthria'];
if($af_dysarthria  == "0"){$af_dysarthria_0 = $image_check;}else{$af_dysarthria_0 = $image_uncheck;}
if($af_dysarthria == "1"){$af_dysarthria_1 = $image_check;}else{$af_dysarthria_1 = $image_uncheck;}
if($af_dysarthria == "2"){$af_dysarthria_2 = $image_check;}else{$af_dysarthria_2 = $image_uncheck;}
if($af_dysarthria == "3"){$af_dysarthria_3 = $image_check;}else{$af_dysarthria_3 = $image_uncheck;}

$af_neglect=  $row2['af_neglect'];
if($af_neglect  == "0"){$af_neglect_0 = $image_check;}else{$af_neglect_0 = $image_uncheck;}
if($af_neglect == "1"){$af_neglect_1 = $image_check;}else{$af_neglect_1 = $image_uncheck;}
if($af_neglect == "2"){$af_neglect_2 = $image_check;}else{$af_neglect_2 = $image_uncheck;}
if($af_neglect == "3"){$af_neglect_3 = $image_check;}else{$af_neglect_3 = $image_uncheck;}

$check_age_18=  $row2['check_age_18'];
if($check_age_18  == "1"){$check_age_18_0 = $image_check;}else{$check_age_18_0 = $image_uncheck;}
if($check_age_18 == "2"){$check_age_18_1 = $image_check;}else{$check_age_18_1 = $image_uncheck;}

$check_45_onset=  $row2['check_45_onset'];
if($check_45_onset  == "1"){$check_45_onset_0 = $image_check;}else{$check_45_onset_0 = $image_uncheck;}
if($check_45_onset == "2"){$check_45_onset_1 = $image_check;}else{$check_45_onset_1 = $image_uncheck;}

$nihss=  $row2['nihss'];
if($nihss  == "1"){$nihss_0 = $image_check;}else{$nihss_0 = $image_uncheck;}
if($nihss == "2"){$nihss_1 = $image_check;}else{$nihss_1 = $image_uncheck;}

$ct_brain_no_hemo=  $row2['ct_brain_no_hemo'];
if($ct_brain_no_hemo  == "1"){$ct_brain_no_hemo_0 = $image_check;}else{$ct_brain_no_hemo_0 = $image_uncheck;}
if($ct_brain_no_hemo == "2"){$ct_brain_no_hemo_1 = $image_check;}else{$ct_brain_no_hemo_1 = $image_uncheck;}

$unknown_time=  $row2['unknown_time'];
if($unknown_time  == "1"){$unknown_time_0 = $image_check;}else{$unknown_time_0 = $image_uncheck;}
if($unknown_time == "2"){$unknown_time_1 = $image_check;}else{$unknown_time_1 = $image_uncheck;}

$bp=  $row2['bp'];
if($bp  == "1"){$bp_0 = $image_check;}else{$bp_0 = $image_uncheck;}
if($bp == "2"){$bp_1 = $image_check;}else{$bp_1 = $image_uncheck;}

$seizure=  $row2['seizure'];
if($seizure  == "1"){$seizure_0 = $image_check;}else{$seizure_0 = $image_uncheck;}
if($seizure == "2"){$seizure_1 = $image_check;}else{$seizure_1 = $image_uncheck;}

$plasma_glucose=  $row2['plasma_glucose'];
if($plasma_glucose  == "1"){$plasma_glucose_0 = $image_check;}else{$plasma_glucose_0 = $image_uncheck;}
if($plasma_glucose == "2"){$plasma_glucose_1 = $image_check;}else{$plasma_glucose_1 = $image_uncheck;}

$inr=  $row2['inr'];
if($inr  == "1"){$inr_0 = $image_check;}else{$inr_0 = $image_uncheck;}
if($inr == "2"){$inr_1 = $image_check;}else{$inr_1 = $image_uncheck;}

$minor=  $row2['minor'];
if($minor  == "1"){$minor_0 = $image_check;}else{$minor_0 = $image_uncheck;}
if($minor == "2"){$minor_1 = $image_check;}else{$minor_1 = $image_uncheck;}

$hx_of_ich=  $row2['hx_of_ich'];
if($hx_of_ich  == "1"){$hx_of_ich_0 = $image_check;}else{$hx_of_ich_0 = $image_uncheck;}
if($hx_of_ich == "2"){$hx_of_ich_1 = $image_check;}else{$hx_of_ich_1 = $image_uncheck;}

$cva=  $row2['cva'];
if($cva  == "1"){$cva_0 = $image_check;}else{$cva_0 = $image_uncheck;}
if($cva == "2"){$cva_1 = $image_check;}else{$cva_1 = $image_uncheck;}

$bleeding=  $row2['bleeding'];
if($bleeding  == "1"){$bleeding_0 = $image_check;}else{$bleeding_0 = $image_uncheck;}
if($bleeding == "2"){$bleeding_1 = $image_check;}else{$bleeding_1 = $image_uncheck;}

$surgery=  $row2['surgery'];
if($surgery  == "1"){$surgery_0 = $image_check;}else{$surgery_0 = $image_uncheck;}
if($surgery == "2"){$surgery_1 = $image_check;}else{$surgery_1 = $image_uncheck;}

$puncture=  $row2['puncture'];
if($puncture  == "1"){$puncture_0 = $image_check;}else{$puncture_0 = $image_uncheck;}
if($puncture == "2"){$puncture_1 = $image_check;}else{$puncture_1 = $image_uncheck;}

$noacs=  $row2['noacs'];
if($noacs  == "1"){$noacs_0 = $image_check;}else{$noacs_0 = $image_uncheck;}
if($noacs == "2"){$noacs_1 = $image_check;}else{$noacs_1 = $image_uncheck;}

$enoxaparin=  $row2['enoxaparin'];
if($enoxaparin  == "1"){$enoxaparin_0 = $image_check;}else{$enoxaparin_0 = $image_uncheck;}
if($enoxaparin == "2"){$enoxaparin_1 = $image_check;}else{$enoxaparin_1 = $image_uncheck;}

$infective_endocarditis=  $row2['infective_endocarditis'];
if($infective_endocarditis  == "1"){$infective_endocarditis_0 = $image_check;}else{$infective_endocarditis_0 = $image_uncheck;}
if($infective_endocarditis == "2"){$infective_endocarditis_1 = $image_check;}else{$infective_endocarditis_1 = $image_uncheck;}

$aortic_dissection=  $row2['aortic_dissection'];
if($aortic_dissection  == "1"){$aortic_dissection_0 = $image_check;}else{$aortic_dissection_0 = $image_uncheck;}
if($aortic_dissection == "2"){$aortic_dissection_1 = $image_check;}else{$aortic_dissection_1 = $image_uncheck;}

$ich=  $row2['ich'];
if($ich  == "1"){$ich_0 = $image_check;}else{$ich_0 = $image_uncheck;}
if($ich == "2"){$ich_1 = $image_check;}else{$ich_1 = $image_uncheck;}

$injury=  $row2['injury'];
if($injury  == "1"){$injury_0 = $image_check;}else{$injury_0 = $image_uncheck;}
if($injury == "2"){$injury_1 = $image_check;}else{$injury_1 = $image_uncheck;}

       

        //----------------------ipd_nurse_admission_note
        $head = '
                <style>
                body{
                        font-family: "Garuda";//เรียกใช้font Garuda สำหรับแสดงผล ภาษาไทย
                }
                footer {
                        position: fixed;
                        bottom: -60px;
                        left: 0px;
                        right: 0px;
                        height: 50px;

                        /** Extra personal styles **/
                        line-height: 35px;
                }
                .check_img{
                    /*margin-top: 10px;*/
                }
                .page-break {
                    page-break-before: always;
                }
                </style>
                <h4  style="text-align:center;font-size:11pt;">Stroke Fast Track WebApp by Ntp '.htmlspecialchars(DbConstant::HOSPITAL_NAME).'</h4>
                <table id="bg-table" width="100%" style="border-collapse: collapse;font-size:8pt;margin-top:7px;">
                    <tr style="border:1px solid #000;margin: 35px;">
                        <td style="border-right:0.5px solid #000;padding:4px; text-align:center; background-color:#C0C0C0;" colspan="6"><B>General Information</B></td>
                    </tr>
                    <tr style="border:1px solid #000; margin: 35px;">
  <td style="border-right:0.5px solid #000; padding:2px; text-align:left; height:30px;" colspan="6">
    <b>&nbsp;&nbsp;ผู้ป่วย : hn : '.$row2['hn'].' | VN : '.$row2['vn'].' | ชื่ิอ - สกุล : '.$row2['ptname'].'  | อายุ : '.$row2['age_y'].' ปี |&nbsp;&nbsp;น้ำหนัก</b>&nbsp;' .$row2['bw'] .' Kg.</td>
                    </tr>
                    <tr style="border:1px solid #000;margin: 35px;">
                    <td style="border-right:0.5px solid #000;padding:4px; text-align:center; background-color:#C0C0C0;" colspan="6"><B>NIHSS Evaluation</B></td>
                </tr>

                <tr style="border:1px solid #000;margin: 45px;">
                <td style="border-right:0.5px solid #000;padding:2px; text-align:center; background-color:#C0C0C0;" colspan="1"><B>Item</B></td>
                <td style="border-right:0.5px solid #000;padding:2px; text-align:center; background-color:#C0C0C0;" colspan="5"><B></B></td>
            </tr>
          
            <tr style="border:1px solid #000;margin: 45px;">
            <td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">1a. Level of consciousness</td>
            <td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$conscious_0.' 0 - Alert (A)</td>
            <td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$conscious_1.' 1 - Drowsy (V)</td>
            <td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$conscious_2.' 2 - Stupor (P)</td>
            <td style="border-right:0.5px solid #000;padding:2px;" colspan="2" valign="top">'.$conscious_3.' 3 - Coma (U)</td>

        </tr>

        <tr style="border:1px solid #000;margin: 45px;">
        <td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">1b. Two questions : อายุเท่าไหร่ เดือนอะไร</td>
        <td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$two_questions_0.' 0 - Both correct</td>
        <td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$two_questions_1.' 1 - One correct</td>
        <td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$two_questions_2.' 2 - None correct</td>
        <td style="border-right:0.5px solid #000;padding:2px;" colspan="2" valign="top"></td>
    </tr>

    <tr style="border:1px solid #000;margin: 45px;">
    <td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">1c. Two commands : หลับตา-ลืมตา กำมือ-แบมือ</td>
    <td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$two_commands_0.' 0 - Both correct</td>
    <td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$two_commands_1.' 1 - One correct</td>
    <td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$two_commands_2.' 2 - None correct</td>
    <td style="border-right:0.5px solid #000;padding:2px;" colspan="2" valign="top"></td>
</tr>

<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">2. Best gaze</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$best_gaze_0.' 0 - Normal</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$best_gaze_1.' 1 - Partial gaze palsy</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$best_gaze_2.' 2 - Forced deviation</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="2" valign="top"></td>
</tr>

<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">3. Best visual field</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$best_visual_field_0.' 0 - No visual loss</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$best_visual_field_1.' 1 - Partial hemianopia</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$best_visual_field_2.' 2 - Complete hemianopia</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="2" valign="top">'.$best_visual_field_3.' 3 - Bilateral hemianopia/ Blind</td>
</tr>

<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">4. Facial palsy</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$facial_palsy_0.' 0 - Normal</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$facial_palsy_1.' 1 - Minor มุมปากตก/ ไม่เท่ากันเมื่อยิ้ม</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$facial_palsy_2.' 2 - Partial อ่อนแรงมาก แต่พอเคลื่อนไหวได้บ้าง</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="2" valign="top">'.$facial_palsy_3.' 3 - Complete เคลื่อนไหวไม่ได้เลย</td>
</tr>

<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">5a. Best moter LEFT arm : ท่านั่ง เหยียดแขนออกในท่าคว่ำมือ 90 องศา, ท่านอน 45 องศา ค้างนาน 10 วินาที *</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$best_moter_left_arm_0.' 0 - No drift ยกแขนค้างไม่ตก นาน 10 วินาที</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$best_moter_left_arm_1.' 1 - Drift ยกแขนได้ ไม่ถึง 10 วินาที</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$best_moter_left_arm_2.' 2 - Fall ยกแขนได้บ้าง แต่ไม่สามารถคงในตำแหน่งที่ต้องการ</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$best_moter_left_arm_3.' 3 - No effort against gravity ไม่สามารถยกแขนขึ้นได้</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$best_moter_left_arm_4.' 4 - No movement ไม่ขยับแขนเลย</td>
</tr>

<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">5b. Best moter RIGHT arm</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$best_moter_right_arm_0.' 0 - No drift ยกแขนค้างไม่ตก นาน 10 วินาที</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$best_moter_right_arm_1.' 1 - Drift ยกแขนได้ ไม่ถึง 10 วินาที</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$best_moter_right_arm_2.' 2 - Fall ยกแขนได้บ้าง แต่ไม่สามารถคงในตำแหน่งที่ต้องการ</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$best_moter_right_arm_3.' 3 - No effort against gravity ไม่สามารถยกแขนขึ้นได้</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$best_moter_right_arm_4.' 4 - No movement ไม่ขยับแขนเลย</td>
</tr>

<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">6a. Best moter LEFT leg : ท่านอนยกขา 45 องศา ค้างนาน 5 วินาที *</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$best_moter_left_leg_0.' 0 - No drift ยกขาค้างได้นาน 5 วินาที</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$best_moter_left_leg_1.' 1 - Drift ยกขาค้างได้ แต่ไม่ถึง 5 วินาที</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$best_moter_left_leg_2.' 2 - Fall ยกขาได้บ้าง แต่ไม่สามารถคงในตำแหน่งที่ต้องการ</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$best_moter_left_leg_3.' 3 - No effort against gravity ไม่สามารถยกขาขึ้นได้</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$best_moter_left_leg_4.' 4 - No movement ไม่ขยับขาเลย</td>
</tr>

<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">6b. Best moter RIGHT leg</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$best_moter_right_leg_0.' 0 - No drift ยกขาค้างได้นาน 5 วินาที</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$best_moter_right_leg_1.' 1 - Drift ยกขาค้างได้ แต่ไม่ถึง 5 วินาที</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$best_moter_right_leg_2.' 2 - Fall ยกขาได้บ้าง แต่ไม่สามารถคงในตำแหน่งที่ต้องการ</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$best_moter_right_leg_3.' 3 - No effort against gravity ไม่สามารถยกขาขึ้นได้</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$best_moter_right_leg_4.' 4 - No movement ไม่ขยับขาเลย</td>
</tr>

<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">7. Ataxia</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$ataxia_0.' 0 - No ataxia</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$ataxia_1.' 1 - Ataxia one limb</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$ataxia_2.' 2 - Ataxia two limbs</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="2" valign="top"></td>
</tr>

<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">8. Sensory</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$sensory_0.' 0 - Normal</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$sensory_1.' 1 - Partial loss รู้สึกบ้าง แต่ไม่เท่ากัน</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$sensory_2.' 2 - Dense loss ไม่รู้สึกเลย</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="2" valign="top"></td>
</tr>

<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">9. Best language aphasia</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$best_language_aphasia_0.' 0 - No aphasia</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$best_language_aphasia_1.' 1 - Mild to moderate</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$best_language_aphasia_2.' 2 - Severe</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="2" valign="top">'.$best_language_aphasia_3.' 3 - Mute gobal aphasia</td>
</tr>

<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">10. Dysarthria</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$dysarthria_0.' 0 - Normal articulation</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$dysarthria_1.' 1 - Mild to moderate พอเข้าใจได้</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$dysarthria_2.' 2 - Severe ไม่สามารถเข้าใจได้</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="2" valign="top"></td>
</tr>


<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">11. Neglect</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$neglect_0.' 0 - No neglect</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$neglect_1.' 1 - Partial neglect ไม่สนสิ่งกระตุ้นบางอย่าง</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$neglect_2.' 2 - Complete neglect ไม่สนสิ่งกระตุ้นทั้งหมด</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="2" valign="top"></td>
</tr>

<tr style="border:1px solid #000;margin: 35px;">
                    <td style="border-right:0.5px solid #000;padding:4px; text-align:center; background-color:#C0C0C0;" colspan="6"><B>NIHSS Evaluation(After)</B></td>
                </tr>

                <tr style="border:1px solid #000;margin: 45px;">
                <td style="border-right:0.5px solid #000;padding:2px; text-align:center; background-color:#C0C0C0;" colspan="1"><B>Item</B></td>
                <td style="border-right:0.5px solid #000;padding:2px; text-align:center; background-color:#C0C0C0;" colspan="5"><B></B></td>
            </tr>
          
            <tr style="border:1px solid #000;margin: 45px;">
            <td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">1a. Level of consciousness</td>
            <td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_conscious_0.' 0 - Alert (A)</td>
            <td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_conscious_1.' 1 - Drowsy (V)</td>
            <td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_conscious_2.' 2 - Stupor (P)</td>
            <td style="border-right:0.5px solid #000;padding:2px;" colspan="2" valign="top">'.$af_conscious_3.' 3 - Coma (U)</td>

        </tr>

        <tr style="border:1px solid #000;margin: 45px;">
        <td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">1b. Two questions : อายุเท่าไหร่ เดือนอะไร</td>
        <td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_two_questions_0.' 0 - Both correct</td>
        <td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_two_questions_1.' 1 - One correct</td>
        <td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_two_questions_2.' 2 - None correct</td>
        <td style="border-right:0.5px solid #000;padding:2px;" colspan="2" valign="top"></td>
    </tr>

    <tr style="border:1px solid #000;margin: 45px;">
    <td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">1c. Two commands : หลับตา-ลืมตา กำมือ-แบมือ</td>
    <td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_two_commands_0.' 0 - Both correct</td>
    <td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_two_commands_1.' 1 - One correct</td>
    <td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_two_commands_2.' 2 - None correct</td>
    <td style="border-right:0.5px solid #000;padding:2px;" colspan="2" valign="top"></td>
</tr>

<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">2. Best gaze</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_best_gaze_0.' 0 - Normal</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_best_gaze_1.' 1 - Partial gaze palsy</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_best_gaze_2.' 2 - Forced deviation</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="2" valign="top"></td>
</tr>

<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">3. Best visual field</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_best_visual_field_0.' 0 - No visual loss</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_best_visual_field_1.' 1 - Partial hemianopia</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_best_visual_field_2.' 2 - Complete hemianopia</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="2" valign="top">'.$af_best_visual_field_3.' 3 - Bilateral hemianopia/ Blind</td>
</tr>

<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">4. Facial palsy</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_facial_palsy_0.' 0 - Normal</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_facial_palsy_1.' 1 - Minor มุมปากตก/ ไม่เท่ากันเมื่อยิ้ม</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_facial_palsy_2.' 2 - Partial อ่อนแรงมาก แต่พอเคลื่อนไหวได้บ้าง</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="2" valign="top">'.$af_facial_palsy_3.' 3 - Complete เคลื่อนไหวไม่ได้เลย</td>
</tr>

<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">5a. Best moter LEFT arm : ท่านั่ง เหยียดแขนออกในท่าคว่ำมือ 90 องศา, ท่านอน 45 องศา ค้างนาน 10 วินาที *</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_best_moter_left_arm_0.' 0 - No drift ยกแขนค้างไม่ตก นาน 10 วินาที</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_best_moter_left_arm_1.' 1 - Drift ยกแขนได้ ไม่ถึง 10 วินาที</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_best_moter_left_arm_2.' 2 - Fall ยกแขนได้บ้าง แต่ไม่สามารถคงในตำแหน่งที่ต้องการ</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_best_moter_left_arm_3.' 3 - No effort against gravity ไม่สามารถยกแขนขึ้นได้</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_best_moter_left_arm_4.' 4 - No movement ไม่ขยับแขนเลย</td>
</tr>

<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">5b. Best moter RIGHT arm</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_best_moter_right_arm_0.' 0 - No drift ยกแขนค้างไม่ตก นาน 10 วินาที</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_best_moter_right_arm_1.' 1 - Drift ยกแขนได้ ไม่ถึง 10 วินาที</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_best_moter_right_arm_2.' 2 - Fall ยกแขนได้บ้าง แต่ไม่สามารถคงในตำแหน่งที่ต้องการ</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_best_moter_right_arm_3.' 3 - No effort against gravity ไม่สามารถยกแขนขึ้นได้</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_best_moter_right_arm_4.' 4 - No movement ไม่ขยับแขนเลย</td>
</tr>

<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">6a. Best moter LEFT leg : ท่านอนยกขา 45 องศา ค้างนาน 5 วินาที *</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_best_moter_left_leg_0.' 0 - No drift ยกขาค้างได้นาน 5 วินาที</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_best_moter_left_leg_1.' 1 - Drift ยกขาค้างได้ แต่ไม่ถึง 5 วินาที</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_best_moter_left_leg_2.' 2 - Fall ยกขาได้บ้าง แต่ไม่สามารถคงในตำแหน่งที่ต้องการ</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_best_moter_left_leg_3.' 3 - No effort against gravity ไม่สามารถยกขาขึ้นได้</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_best_moter_left_leg_4.' 4 - No movement ไม่ขยับขาเลย</td>
</tr>

<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">6b. Best moter RIGHT leg</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_best_moter_right_leg_0.' 0 - No drift ยกขาค้างได้นาน 5 วินาที</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_best_moter_right_leg_1.' 1 - Drift ยกขาค้างได้ แต่ไม่ถึง 5 วินาที</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_best_moter_right_leg_2.' 2 - Fall ยกขาได้บ้าง แต่ไม่สามารถคงในตำแหน่งที่ต้องการ</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_best_moter_right_leg_3.' 3 - No effort against gravity ไม่สามารถยกขาขึ้นได้</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_best_moter_right_leg_4.' 4 - No movement ไม่ขยับขาเลย</td>
</tr>

<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">7. Ataxia</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_ataxia_0.' 0 - No ataxia</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_ataxia_1.' 1 - Ataxia one limb</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_ataxia_2.' 2 - Ataxia two limbs</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="2" valign="top"></td>
</tr>

<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">8. Sensory</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_sensory_0.' 0 - Normal</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_sensory_1.' 1 - Partial loss รู้สึกบ้าง แต่ไม่เท่ากัน</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_sensory_2.' 2 - Dense loss ไม่รู้สึกเลย</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="2" valign="top"></td>
</tr>

<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">9. Best language aphasia</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_best_language_aphasia_0.' 0 - No aphasia</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_best_language_aphasia_1.' 1 - Mild to moderate</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_best_language_aphasia_2.' 2 - Severe</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="2" valign="top">'.$af_best_language_aphasia_3.' 3 - Mute gobal aphasia</td>
</tr>

<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">10. Dysarthria</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_dysarthria_0.' 0 - Normal articulation</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_dysarthria_1.' 1 - Mild to moderate พอเข้าใจได้</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_dysarthria_2.' 2 - Severe ไม่สามารถเข้าใจได้</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="2" valign="top"></td>
</tr>


<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">11. Neglect</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_neglect_0.' 0 - No neglect</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_neglect_1.' 1 - Partial neglect ไม่สนสิ่งกระตุ้นบางอย่าง</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top">'.$af_neglect_2.' 2 - Complete neglect ไม่สนสิ่งกระตุ้นทั้งหมด</td>
<td style="border-right:0.5px solid #000;padding:2px;" colspan="2" valign="top"></td>
</tr>

<tr style="border:1px solid #000;margin: 35px;">
    <td style="border-right:0.5px solid #000;padding:4px; text-align:center; background-color:#C0C0C0;" colspan="6"><B>Indications for IV thrombolysis</B></td>
 </tr>

                <tr style="border:1px solid #000;margin: 45px;">
                <td style="border-right:0.5px solid #000;padding:2px; text-align:center; background-color:#C0C0C0;" colspan="4"><B>Item</B></td>
                <td style="border-right:0.5px solid #000;padding:2px; text-align:center; background-color:#C0C0C0;" colspan="1"><B>No</B></td>
                <td style="border-right:0.5px solid #000;padding:2px; text-align:center; background-color:#C0C0C0;" colspan="1"><B>Yes</B></td>
            </tr>

            <tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="4" valign="top">Age > 18 y</td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$check_age_18_0.'</td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$check_age_18_1.'</td>
</tr>

<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="4" valign="top">Onset < 4.5 h</td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$check_45_onset_0.'</td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$check_45_onset_1.'</td>
</tr>

<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="4" valign="top">อาการทางระบบประสาทสามารถวัดได้โดยใช้ NIHSS</td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$nihss_0.'</td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$nihss_1.'</td>
</tr>

<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="4" valign="top">CT brain no hemorrhage</td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$ct_brain_no_hemo_0.'</td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$ct_brain_no_hemo_1.'</td>
</tr>

<tr style="border:1px solid #000;margin: 35px;">
    <td style="border-right:0.5px solid #000;padding:4px; text-align:center; background-color:#C0C0C0;" colspan="6"><B>Contraindications for IV thrombolysis</B></td>
 </tr>

                <tr style="border:1px solid #000;margin: 45px;">
                <td style="border-right:0.5px solid #000;padding:2px; text-align:center; background-color:#C0C0C0;" colspan="4"><B>Item</B></td>
                <td style="border-right:0.5px solid #000;padding:2px; text-align:center; background-color:#C0C0C0;" colspan="1"><B>Yes</B></td>
                <td style="border-right:0.5px solid #000;padding:2px; text-align:center; background-color:#C0C0C0;" colspan="1"><B>No</B></td>
            </tr>

            <tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="4" valign="top">ไม่ทราบระยะเวลาที่เริ่มเป็นชัดเจน หรือมีอาการหลังตื่นนอน</td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$unknown_time_0.'</td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$unknown_time_1.'</td>
</tr>

<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="4" valign="top">SBP >= 185 or DBP >= 110 mmHg *</td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$bp_0.'</td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$bp_1.'</td>
</tr>

<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="4" valign="top">Seizure with postictal neurological deficit</td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$seizure_0.'</td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$seizure_1.'</td>
</tr>

<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="4" valign="top">Plasma glucose < 50, or> 400 mg/dL (Correct glucose ก่อน)</td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$plasma_glucose_0.'</td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$plasma_glucose_1.'</td>
</tr>

<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="4" valign="top">Minor symptoms (NIHSS <= 4) * </td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$minor_0.'</td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$minor_1.'</td>
</tr>

<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="4" valign="top">Previous Hx of ICH</td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$hx_of_ich_0.'</td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$hx_of_ich_1.'</td>
</tr>

<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="4" valign="top">3 mo; Old CVA, Intracranial/Spinal Sx, Head Trauma, MI *</td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$cva_0.'</td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$cva_1.'</td>
</tr>

<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="4" valign="top">3 wk ; GI, GU bleeding</td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$bleeding_0.'</td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$bleeding_1.'</td>
</tr>

<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="4" valign="top">2 wk ; Major surgery</td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$surgery_0.'</td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$surgery_1.'</td>
</tr>

<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="4" valign="top">1 wk ; Lumbar puncture/ Arterial puncture (non-compressible site)</td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$puncture_0.'</td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$puncture_1.'</td>
</tr>

<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="4" valign="top">2 days ; NOACs * (Dabigatran, Apixaban, Rivaroxaban, Edoxaban), heparin, warfarin</td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$noacs_0.'</td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$noacs_1.'</td>
</tr>

<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="4" valign="top">1 day ; Enoxaparin</td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$enoxaparin_0.'</td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$enoxaparin_1.'</td>
</tr>

<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="4" valign="top">INR > 1.7, Plt <100000, aPTT < 40 sec, PT> 15 sec</td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$inr_0.'</td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$inr_1.'</td>
</tr>

<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="4" valign="top">Infective Endocarditis (New murmur + Prolong fever)</td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$infective_endocarditis_0.'</td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$infective_endocarditis_1.'</td>
</tr>

<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="4" valign="top">Aortic dissection (BP 4 ext, unequal pulse, Chest/Back pain)</td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$aortic_dissection_0.'</td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$aortic_dissection_1.'</td>
</tr>

<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="4" valign="top">CT : ICH, SAH, multilobar infarction (Hypodensity > 1/3 MCA)</td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$ich_0.'</td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$ich_1.'</td>
</tr>

<tr style="border:1px solid #000;margin: 45px;">
<td style="border-right:0.5px solid #000;padding:2px;" colspan="4" valign="top">ตรวจพบเลือดออก หรือมีการบาดเจ็บ (กระดูกหัก)</td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$injury_0.'</td>
<td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="1" valign="top">'.$injury_1.'</td>
</tr>

<tr style="border:1px solid #000;margin: 35px;">
                    <td style="border-right:0.5px solid #000;padding:4px; text-align:center; background-color:#C0C0C0;" colspan="6"><B>ตรวจสอบโดยแพทย์</B></td>
                </tr>

                <tr style="border:1px solid #000; margin: 35px;">
  <td style="border-right:0.5px solid #000; padding:2px; text-align:left; height:30px;" colspan="6">
   &nbsp;&nbsp;ชื่อแพทย์ : '.$row2['doctor_name'].'</td>
                    </tr>


                </table>
        ';
        $mpdf->WriteHTML($head);
        $mpdf->Output();
?>