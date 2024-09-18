<?php
require_once '../mains/datethai.php';
require_once '../include/Session.php';
 
                 
   $login = empty($_REQUEST['loginname']) ? null : $_REQUEST['loginname'];
   $loginname = $_SESSION['loginname'];
   $values =['loginname'=>$loginname];
   
   //หากพบว่าไม่ตรงกันให้ ทำลาย session เดิมทิ้งไป
   if(!$loginname){
    session_start();
    session_destroy();              
        
  }

  Session::checkLoginSessionAndShowMessage(); //เช็ค session

  if(!(
     Session::checkPermission('DOCUMENT', 'PRINT')
     )){
     return;
 }

 /*Session::checkLoginSessionAndShowMessage(); //เช็ค session
Session::checkPermissionAndShowMessage('DOCUMENT', 'PRINT');
*/
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
//require_once __DIR__ . '/vendor/autoload.php';
require_once '../include/DbUtils.php';
require_once '../include/Session.php';
require_once '../include/KphisQueryUtils.php';
require_once '../include/ReportQueryUtils.php';

date_default_timezone_set('asia/bangkok');

//echo $_SERVER['DOCUMENT_ROOT'] ;

$conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
$mpdf->AddPageByArray([
    'margin-left' => 6,
    'margin-right' => 6,
    'margin-top' => 6,
    'margin-bottom' => 6,
]);

$id = $_REQUEST['id'];
$an = empty($_REQUEST['an']) ? null : $_REQUEST['an'];
$hn = KphisQueryUtils::getHnByAn($an);
$query_parameters = ['an' => $an];
$query_parameters2 = ['an' => $an,'id' => $id];

Session::insertSystemAccessLog(json_encode(array(
    'report'=>'PRE-NURSENOTE-PDF',
   // 'action'=>'PRINT',
    'an'=>$an,
),JSON_UNESCAPED_UNICODE));

//echo $id;

/*
$login = empty($_REQUEST['loginname']) ? null : $_REQUEST['loginname'];
$loginname = $_SESSION['loginname'];
$values = ['loginname' => $loginname];
if ($login != $loginname) {
    session_start();
    session_destroy();
}
*/
$image_uncheck = "<img src='../include/images/check-adm.jpg' width='1.6%' class='check_img'>";
$image_check = "<img src='../include/images/check-1.jpg' width='1.6%' class='check_img'>";
//-------------------------Doctor admission note
$sql = "SELECT pn.*
        FROM prs_icu_form pn
        WHERE pn.an = :an and pn.id = :id
        limit 1";
$stmt = $conn->prepare($sql);
$stmt->execute($query_parameters2);
$row  = $stmt->fetch();

$sql_item = "SELECT dr_adm_item.id,
                    dr_adm_item.doctor,
                    doctor.`name` AS admission_note_doctorname
                    FROM " . DbConstant::KPHIS_DBNAME . ".prs_pre_nursenote_item dr_adm_item
                    LEFT OUTER JOIN " . DbConstant::HOSXP_DBNAME . ".doctor ON doctor.code = dr_adm_item.doctor
                    WHERE an=:an
                    ORDER BY dr_adm_item.id ASC";
$stmt_item = $conn->prepare($sql_item);
$stmt_item->execute($query_parameters);
$pre_note_count = 0;
while ($row_item = $stmt_item->fetch()) {
    $id_pre_note[] = $row_item['id'];
    $doctor[] = $row_item['doctor'];
    $admission_note_doctorname[] = $row_item['admission_note_doctorname'];
    //$admission_note_doctorentryposition[] = $row_item['admission_note_doctorentryposition'];
    $pre_note_count++;
}

                       
                        $from_dep = $row['from_dep'];
                        $rxDate = $row['rxdate'];//วันที่ Discharge
                        $rxdate = date($rxDate);
                        $rxTime = $row['rxtime'];//เวลาที่ Discharge
                        $rxtime = date($rxTime);
                        $strDate =($rxdate."  ".$rxtime);
                       // $dchtime  = date('H:i', strtotime($origTime));






 //โรคประจำคัว
 $heart_disease_history_1 = '( )';
 if ($row['heart_disease_history'] == 'ไม่มี') {$heart_disease_history_1 = '('.$image_check.')';
 }
 $heart_disease_history_2 = '( )';
 if ($row['heart_disease_history'] != 'ไม่มี' && $row['heart_disease_history'] != null) {$heart_disease_history_2 = '('.$image_check.')';
    $heart_disease_history  =  htmlspecialchars($row['heart_disease_history']);
 }

 $neck_vien_engorement_1 = '( )';
 if ($row['neck_vien_engorement'] == 'ไม่พบ') {$neck_vien_engorement_1 = '('.$image_check.')';
 }
 $neck_vien_engorement_2 = '( )';
 if ($row['neck_vien_engorement'] == 'ประเมินไม่ได้') {$neck_vien_engorement_2 = '('.$image_check.')';
 }
 $neck_vien_engorement_3 = '( )';
 if ($row['neck_vien_engorement'] != 'ไม่พบ' && $row['neck_vien_engorement'] != 'ประเมินไม่ได้' && $row['neck_vien_engorement'] != null) {$neck_vien_engorement_3 = '('.$image_check.')';
    $neck_vien_engorement  =  htmlspecialchars($row['neck_vien_engorement']);
 }

  //โรคประจำคัว
  $skin_1 = '( )';
  if ($row['skin'] == 'ปกติ') {$skin_1 = '('.$image_check.')';
  }
  $skin_2 = '( )';
  if ($row['skin'] == 'ซีด') {$skin_2 = '('.$image_check.')';
  }
  $skin_3 = '( )';
  if ($row['skin'] == 'เขียว') {$skin_3 = '('.$image_check.')';
  }
  $skin_4 = '( )';
  if ($row['skin'] == 'จุดจ้ำเลือด') {$skin_4 = '('.$image_check.')';
  }
  $skin_5 = '( )';
  if ($row['skin'] == 'แห้ง') {$skin_5 = '('.$image_check.')';
  }
  $skin_6 = '( )';
  if ($row['skin'] != 'จุดจ้ำเลือด' && $row['skin'] != 'ซีด' 
  && $row['skin'] != 'เขียว' && $row['skin'] != 'จุดจ้ำเลือด' && $row['skin'] != 'แห้ง' 
  && $row['skin'] != null) {$skin_6 = '('.$image_check.')';
     $skin =  htmlspecialchars($row['skin']);
  }

  $listen_to_the_heart_1 = '( )';
  if ($row['listen_to_the_heart'] == '1') {$listen_to_the_heart_1 = '('.$image_check.')';
  }

  $listen_to_the_heart_2 = '( )';
  if ($row['listen_to_the_heart'] == '2') {$listen_to_the_heart_2 = '('.$image_check.')';
  }

  $listen_to_the_heart_3 = '( )';
  if ($row['listen_to_the_heart'] == '3') {$listen_to_the_heart_3 = '('.$image_check.')';
  }


  $kidney_disease_history_1 = '( )';
  if ($row['kidney_disease_history'] == 'ไม่มี') {$kidney_disease_history_1 = '('.$image_check.')';
  }
  $kidney_disease_history_2 = '( )';
  if ($row['kidney_disease_history'] != 'ไม่มี' && $row['kidney_disease_history'] != null) {$kidney_disease_history_2 = '('.$image_check.')';
     $kidney_disease_history  =  htmlspecialchars($row['kidney_disease_history']);
  }

  $history_of_lung_disease_1 = '( )';
  if ($row['history_of_lung_disease'] == 'ไม่มี') {$history_of_lung_disease_1 = '('.$image_check.')';
  }
  $history_of_lung_disease_2 = '( )';
  if ($row['history_of_lung_disease'] != 'ไม่มี' && $row['history_of_lung_disease'] != null) {$history_of_lung_disease_2 = '('.$image_check.')';
     $history_of_lung_disease =  htmlspecialchars($row['history_of_lung_disease']);
  }

  $et_other_1 = '( )';
  if ($row['et_other'] == 'ET-Tube') {$et_other_1 = '('.$image_check.')';
  }

  $et_other_2 = '( )';
  if ($row['et_other'] == 'TT-Tube') {$et_other_2 = '('.$image_check.')';
  }

  $et_other_3 = '( )';
  if ($row['et_other'] == 'O2HFNC') {$et_other_3 = '('.$image_check.')';
  }

  $et_other_4 = '( )';
  if ($row['et_other'] == 'candular') {$et_other_4 = '('.$image_check.')';
  }
  $et_other_5 = '( )';
  if ($row['et_other'] == 'Mark c bag') {$et_other_5 = '('.$image_check.')';
  }
  $et_other_6 = '( )';
  if ($row['et_other'] == 'RA') {$et_other_6 = '('.$image_check.')';
  }

  $breathing_characteristics_1 = '( )';
  if ($row['breathing_characteristics'] == '1') {$breathing_characteristics_1 = '('.$image_check.')';
  }

  $breathing_characteristics_2 = '( )';
  if ($row['breathing_characteristics'] == '2') {$breathing_characteristics_2 = '('.$image_check.')';
  }

  $breathing_characteristics_3 = '( )';
  if ($row['breathing_characteristics'] == '3') {$breathing_characteristics_3 = '('.$image_check.')';
  }


  $on_icd_1 = '( )';
  if ($row['on_icd'] == 'ไม่มี') {$on_icd_1 = '('.$image_check.')';
  }
  $on_icd_2 = '( )';
  if ($row['on_icd'] != 'ไม่มี' && $row['on_icd'] != null) {$on_icd_2 = '('.$image_check.')';
     $on_icd  =  htmlspecialchars($row['on_icd']);
  }

  $listen_sound_lungs_1 = '( )';
  if ($row['listen_sound_lungs'] == '1') {$listen_sound_lungs_1 = '('.$image_check.')';
  }

  $listen_sound_lungs_2 = '( )';
  if ($row['listen_sound_lungs'] == '2') {$listen_sound_lungs_2 = '('.$image_check.')';
  }

  $listen_sound_lungs_3 = '( )';
  if ($row['listen_sound_lungs'] == '3') {$listen_sound_lungs_3 = '('.$image_check.')';
  }

  $listen_sound_lungs_4 = '( )';
  if ($row['listen_sound_lungs'] == '4') {$listen_sound_lungs_4 = '('.$image_check.')';
  }

  $listen_sound_lungs_5 = '( )';
  if ($row['listen_sound_lungs'] == '5') {$listen_sound_lungs_5 = '('.$image_check.')';
  }


  $history_of_gastrointestinal_1 = '( )';
  if ($row['history_of_gastrointestinal'] == 'ไม่มี') {$history_of_gastrointestinal_1 = '('.$image_check.')';
  }
  $history_of_gastrointestinal_2 = '( )';
  if ($row['history_of_gastrointestinal'] != 'ไม่มี' && $row['history_of_gastrointestinal'] != null) {$history_of_gastrointestinal_2 = '('.$image_check.')';
     $history_of_gastrointestinal  =  htmlspecialchars($row['history_of_gastrointestinal']);
  }

  $communication_history_1 = '( )';
  if ($row['communication_history'] == 'ไม่มี') {$communication_history_1 = '('.$image_check.')';
  }
  $communication_history_2 = '( )';
  if ($row['communication_history'] != 'ไม่มี' && $row['communication_history'] != null) {$communication_history_2 = '('.$image_check.')';
     $communication_history  =  htmlspecialchars($row['communication_history']);
  }


  $speaking_check = '( )';
  if ($row['speaking'] != null) {$speaking_check = '('.$image_check.')';
  }

  $speaking_2 = '( )';
  if ($row['speaking'] == 'พูดได้เองชัดเจน') {$speaking_2 = '('.$image_check.')';
  }

  $speaking_3 = '( )';
  if ($row['speaking'] == 'พูดไม่ชัด') {$speaking_3 = '('.$image_check.')';
  }

  $speaking_4= '( )';
  if ($row['speaking'] != 'พูดได้เองชัดเจน' && $row['speaking'] != 'พูดไม่ชัด' && $row['speaking'] != null) {$speaking_4 = '('.$image_check.')';
     $speaking  =  htmlspecialchars($row['speaking']);
  }


  $communication_check = '( )';
  if ($row['communication'] != null) {$communication_check = '('.$image_check.')';
  }

  $communication_2 = '( )';
  if ($row['communication'] == 'สื่อสารด้วยการเขียน') {$communication_2 = '('.$image_check.')';
  }

  $communication_3 = '( )';
  if ($row['communication'] == 'สื่อสารโดยการใช้สายตา') {$communication_3 = '('.$image_check.')';
  }

  $communication_4 = '( )';
  if ($row['communication'] == 'สื่อสารโดยใช้ท่าทาง') {$communication_4 = '('.$image_check.')';
  }

  $communication_5 = '( )';
  if ($row['communication'] == 'ประเมินไม่ได้') {$communication_5 = '('.$image_check.')';
  }

  $communication_6= '( )';
  if ($row['communication'] != 'สื่อสารด้วยการเขียน' 
  && $row['communication'] != 'สื่อสารโดยการใช้สายตา' 
  && $row['communication'] != 'สื่อสารโดยใช้ท่าทาง' 
  && $row['communication'] != 'ประเมินไม่ได้' 
  && $row['communication'] != null) {$communication_4 = '('.$image_check.')';
     $communication  =  htmlspecialchars($row['communication']);
  }
  


 $vision_1 = '( )';
 if ($row['vision'] == 'เห็นชัดเจน') {$vision_1 = '('.$image_check.')';
 }
 $vision_2 = '( )';
 if ($row['vision'] == 'เห็นไม่ชัดเจน') {$vision_2 = '('.$image_check.')';
 }

 $vision_3 = '( )';
 if ($row['vision'] == 'ประเมินไม่ได้') {$vision_3 = '('.$image_check.')';
 }


 $vision_4 = '( )';
 if ($row['vision'] != 'เห็นชัดเจน' && $row['vision'] != 'เห็นไม่ชัดเจน' && $row['vision'] != 'ประเมินไม่ได้'  && $row['vision'] != null) {$vision_4 = '('.$image_check.')';
    $vision  =  htmlspecialchars($row['vision']);
 }
 
 $hearing_aids_1 = '( )';
 if ($row['hearing_aids'] == '1') {$hearing_aids_1 = '('.$image_check.')';
 }

 $hearing_aids_2 = '( )';
 if ($row['hearing_aids'] == '2') {$hearing_aids_2 = '('.$image_check.')';
 }

 $listening_1 = '( )';
 if ($row['listening'] == '1') {$listening_1 = '('.$image_check.')';
 }
 $listening_2 = '( )';
 if ($row['listening'] == '2') {$listening_2 = '('.$image_check.')';
 }
 $listening_3 = '( )';
 if ($row['listening'] == '3') {$listening_3 = '('.$image_check.')';
 }

 $listening_4 = '( )';
 if ($row['listening'] == '4') {$listening_4 = '('.$image_check.')';
 }

 $history_affects_activities_1 = '( )';
 if ($row['history_affects_activities'] == 'ไม่มี') {$history_affects_activities_1 = '('.$image_check.')';
 }
 $history_affects_activities_2 = '( )';
 if ($row['history_affects_activities'] != 'ไม่มี' && $row['history_affects_activities'] != null) {$history_affects_activities_2 = '('.$image_check.')';
    $history_affects_activities =  htmlspecialchars($row['history_affects_activities']);
 }

 $daily_activities_1 = '( )';
 if ($row['daily_activities'] == '1') {$daily_activities_1 = '('.$image_check.')';
 }
 $daily_activities_2 = '( )';
 if ($row['daily_activities'] == '2') {$daily_activities_2 = '('.$image_check.')';
 }
 $daily_activities_3 = '( )';
 if ($row['daily_activities'] == '3') {$daily_activities_3 = '('.$image_check.')';
 }
 $daily_activities_4 = '( )';
 if ($row['daily_activities'] == '4') {$daily_activities_4 = '('.$image_check.')';
 }

 $history_affects_stimulation_1 = '( )';
 if ($row['history_affects_stimulation'] == 'ไม่มี') {$history_affects_stimulation_1 = '('.$image_check.')';
 }
 $history_affects_stimulation_2 = '( )';
 if ($row['history_affects_stimulation'] != 'ไม่มี' && $row['history_affects_stimulation'] != null) {$history_affects_stimulation_2 = '('.$image_check.')';
    $history_affects_stimulation =  htmlspecialchars($row['history_affects_stimulation']);
 }

 $level_of_consciousness_1 = '( )';
 if ($row['level_of_consciousness'] == '1') {$level_of_consciousness_1 = '('.$image_check.')';
 }

 $level_of_consciousness_2 = '( )';
 if ($row['level_of_consciousness'] == '2') {$level_of_consciousness_2 = '('.$image_check.')';
 }

 $level_of_consciousness_3 = '( )';
 if ($row['level_of_consciousness'] == '3') {$level_of_consciousness_3 = '('.$image_check.')';
 }

 $level_of_consciousness_4 = '( )';
 if ($row['level_of_consciousness'] == '4') {$level_of_consciousness_4 = '('.$image_check.')';
 }

 $level_of_consciousness_5 = '( )';
 if ($row['level_of_consciousness'] == '5') {$level_of_consciousness_5 = '('.$image_check.')';
 } 

 $pain_score_1 = '( )';
 if ($row['pain_score'] == '1') {$pain_score_1 = '('.$image_check.')';
 }

 $pain_score_2 = '( )';
 if ($row['pain_score'] == '2') {$pain_score_2 = '('.$image_check.')';
 }

 $fluid_balance = '( )';
 if ($row['fluid_balance'] == 'Y') {$fluid_balance = '('.$image_check.')';
 }

 $aeration = '( )';
 if ($row['aeration'] == 'Y') {$aeration = '('.$image_check.')';
 }

 $nutrition = '( )';
 if ($row['nutrition'] == 'Y') {$nutrition = '('.$image_check.')';
 }

 $communication_problem = '( )';
 if ($row['communication_problem'] == 'Y') {$communication_problem = '('.$image_check.')';
 }

 $activity = '( )';
 if ($row['activity'] == 'Y') {$activity = '('.$image_check.')';
 }

 $stimulation = '( )';
 if ($row['stimulation'] == 'Y') {$stimulation = '('.$image_check.')';
 }




//-------------------------Doctor admission note
$sql_ipt = "select patient.sex,patient.hn,patient.pname,patient.fname,patient.lname,patient.drugallergy,
            (select GROUP_CONCAT(concat(opd_allergy.agent,'=',if(opd_allergy.symptom is null,',',opd_allergy.symptom)/*,' [',if(note is null,',',note),']'*/)) as name
                from ".DbConstant::HOSXP_DBNAME.".opd_allergy
                where opd_allergy.hn = ipt.hn /*and (opd_allergy.no_alert<>'Y' or opd_allergy.no_alert is null)*/
                order by display_order) as allergy_symptom_string,
            an_stat.age_y,an_stat.age_m,an_stat.age_d,
            ipt.regdate,ipt.regtime,
            ipt.ward,ward.name,
            ipt.pttype, pttype.`name` as pttype_name,
            iptadm.bedno, (select vs.bw from ipd_vs_vital_sign vs where vs.an = ipt.an and vs.bw is not null and trim(vs.bw) <> '' order by vs_datetime desc limit 1) as latest_bw
            , (select vs.height from ipd_vs_vital_sign vs where vs.an = ipt.an and vs.bw is not null and trim(vs.bw) <> '' order by vs_datetime desc limit 1) as latest_height
            from ".DbConstant::HOSXP_DBNAME.".ipt
            left outer join ".DbConstant::HOSXP_DBNAME.".an_stat on an_stat.an=ipt.an
            left outer join ".DbConstant::HOSXP_DBNAME.".patient on patient.hn=ipt.hn
            left outer join ".DbConstant::HOSXP_DBNAME.".ward on ward.ward=ipt.ward
            LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".pttype ON pttype.pttype = ipt.pttype
            LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".iptadm ON iptadm.an = ipt.an
            WHERE ipt.an=:an";
        $stmt_ipt = $conn->prepare($sql_ipt);
        $stmt_ipt->execute(['an'=>$an]);
        $row_ipt = $stmt_ipt->fetch();
        $regdatetime = $row_ipt['regdate'].' '.$row_ipt['regtime'];//ใช้ในการดึงข้อมูล ประวัติการผ่าตัด


  
        $receive_date        =  $row['receive_date'];
        $receive_time        =  $row['receive_time'];

        $ids = '20'; //Link menu
        $check_    = ReportQueryUtils::getProduction($ids);

        $check_report = '( )';
        if ($check_  == '1') 
        {$check_report = '&nbsp;<font color="red">รอปรับรายงาน</font>';
        } else {
            $check_report = '';
        }
       

      
        $icu_form1 = "<img src='../include/images/icu-form1.png' width='100%' >";
        $icu_form2 = "<img src='../include/images/icu-form2.png' width='100%' >";
        $icu_form3 = "<img src='../include/images/icu-form3.png' width='100%' >";
        
       // $maxNrOfPages = ceil($max/$itemsPerPage);

$head =
'

    <style>
    div.f15 {
 
        font-size: 12px; 
        
      }
      div.line_dotted {
        text-decoration: underline dotted;  
        text-decoration-color: rgb(105,42,49); 
        font-size: 12px;
        text-decoration-style: dotted;  
      }

        body{
            font-family: "Garuda";//เรียกใช้font Garuda สำหรับแสดงผล ภาษาไทย
        }
        footer {
            position: fixed;
            bottom: -60px;
            left: 0px;
            right: 0px;
            height: 70px;

            /** Extra personal styles **/
            line-height: 35px;
        }
        br {
            display: block;
            content: " ";
            margin: 10px 0;
            height:10pt;
            line-height: 150%;
        }
        #show_img_select  {
            background-image: url("../include/images/allbody.jpg");
            background-position: center;
            background-repeat: no-repeat;
            background-image-resize:5;
            height:180px;
        }

        
    </style>
    <h2 style="text-align:right;font-size:8pt;">&nbsp;</h2>
    
    <h2 style="text-align:center;font-size:11pt;">แบบประเมินสภาพจิต &nbsp;'.htmlspecialchars(DbConstant::HOSPITAL_NAME).$check_report.'</h2>
    
   
<div class="f15"> วันที่ <b>'.LongDateThai2($strDate).'<br>'
.'<table id="bg-table" width="100%" style="border-collapse: collapse;font-size:8pt;margin-top:2px;">'.

'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
' <div class="form-group row alert alert-dark text-left">
<B>1 ลักษณะโดยทั่วไป</B><br><br>
<B>&nbsp;&nbsp;&nbsp;1.1 Generation appearance</B>

</div>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label><b>1.1.1 รูปร่างลักษณะ</b></label>&nbsp;'.
$heart_disease_history_1.'&nbsp;อ้วน&nbsp;'
.$heart_disease_history_1.'&nbsp;สันทัด&nbsp;'
.$heart_disease_history_1.'&nbsp;พิการ&nbsp;'
.$heart_disease_history_2.'&nbsp;มีแผลเป็น&nbsp;'
.'<u>'.$heart_disease_history.'</u>'
.$heart_disease_history_2.'&nbsp;อื่นๆ&nbsp;'
.'<u>'.$heart_disease_history.'</u>'.
'</div>'.
'<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label><b>1.1.2 การแต่งกาย</b></label>&nbsp;'.
$heart_disease_history_1.'&nbsp;สะอาด เหมาะสมกับวัย&nbsp;&nbsp;&nbsp;&nbsp;'
.$heart_disease_history_1.'&nbsp;สะอาด ไม่เหมาะสมกับวัย&nbsp;&nbsp;&nbsp;&nbsp;'
.$heart_disease_history_1.'&nbsp;สกปรก เหมาะสมกับวัย&nbsp;&nbsp;&nbsp;&nbsp;'
.$heart_disease_history_1.'&nbsp;สกปรก ไม่เหมาะสมกับวัย&nbsp;&nbsp;&nbsp;&nbsp;'
.'</div>'.
'<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label><b>1.1.3 พฤติกรรมการเคลื่อนไหวร่างการ(Psychomotor)</label></b>&nbsp;'.
$heart_disease_history_1.'&nbsp;ปกติ&nbsp;'
.$heart_disease_history_1.'&nbsp;น้อยกว่าปกติ&nbsp;'
.$heart_disease_history_1.'&nbsp;มากกว่า&nbsp;'
.$heart_disease_history_2.'&nbsp;ผิดปกติ&nbsp;'
.'<u>'.$heart_disease_history.'</u>'
.$heart_disease_history_2.'&nbsp;เคลื่อนไหวซ้ำๆ&nbsp;'
.$heart_disease_history_2.'&nbsp;กระตุก&nbsp;'
.$heart_disease_history_2.'&nbsp;อยู่ไม่สุข&nbsp;'
.$heart_disease_history_2.'&nbsp;กระสับกระส่าย&nbsp;'
.'</div>'.
'<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label><b>1.1.4 ท่าทีต่อผู้ตรวจ(Attitude)</b></label>&nbsp;'.
$heart_disease_history_1.'&nbsp;เป็นมิตร&nbsp;&nbsp;&nbsp;&nbsp;'
.$heart_disease_history_1.'&nbsp;ต่อต้าน&nbsp;&nbsp;&nbsp;&nbsp;'
.$heart_disease_history_1.'&nbsp;ไม่ไว้วางใจ&nbsp;&nbsp;&nbsp;&nbsp;'
.$heart_disease_history_1.'&nbsp;ไม่เชื่อถือ&nbsp;&nbsp;&nbsp;&nbsp;'
.$heart_disease_history_1.'&nbsp;ยียวน&nbsp;&nbsp;&nbsp;&nbsp;'
.$heart_disease_history_1.'&nbsp;ปิดบังข้อมูล&nbsp;&nbsp;&nbsp;&nbsp;'
.'</div>'.

'</td>'.
'</tr>'.

'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
' <div class="form-group row alert alert-dark text-left">
<B>2. คำพูดและกระแสคำพูด (speech and stream talk)</B><br>
</div>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label><b>2.1 อัตราการพูด (Rate)</b></label>&nbsp;'.
$heart_disease_history_1.'&nbsp;ปกติ&nbsp;'
.$heart_disease_history_1.'&nbsp;เร็ว&nbsp;'
.$heart_disease_history_1.'&nbsp;ช้า&nbsp;'
.'</div>'.
'<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label><b>2.2 จังหวะ (Rhythm)</b></label>&nbsp;'.
$heart_disease_history_1.'&nbsp;พูดราบเรียบ&nbsp;'
.$heart_disease_history_1.'&nbsp;ติดขัด&nbsp;'
.$heart_disease_history_1.'&nbsp;ติดอ่าง&nbsp;'
.'</div>'.
'<br>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label><b>2.3 ความผิดปกติของคำพูด</label></b>&nbsp;'.
$heart_disease_history_1.'&nbsp;ปกติ&nbsp;'
.$heart_disease_history_2.'&nbsp;ผิดปกติ&nbsp;'
.'<u>'.$heart_disease_history.'</u>'
.$heart_disease_history_2.'&nbsp;คำพูดฟังแล้วไม่รู้ความหมาย (neologism)&nbsp;'
.$heart_disease_history_2.'&nbsp;เอาคำหรือวลีมารวมกันแต่ไม่มีความหมาย (word salad)&nbsp;'
.'</div>'.
'<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label><b>2.4 กระแสคำพูด (stream of talk)</b></label>&nbsp;'.
$heart_disease_history_1.'&nbsp;สมเหตุสมผล&nbsp;'
.$heart_disease_history_1.'&nbsp;ไม่สมเหตุสมผล (illogical)&nbsp;'
.$heart_disease_history_1.'&nbsp;ประติดประต่อ&nbsp;'
.$heart_disease_history_1.'&nbsp;ไม่ประติดประต่อ (incoherrence)&nbsp;'
.$heart_disease_history_1.'&nbsp;ตรงคำถาม&nbsp;'
.$heart_disease_history_1.'&nbsp;ไม่ตรงคำถาม (irelevance)&nbsp;'
.$heart_disease_history_1.'&nbsp;พูดวกวน&nbsp;'
.$heart_disease_history_1.'&nbsp;ไม่พูดเลย (mutism)&nbsp;'
.'</div>'.

'</td>'.
'</tr>'.

'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
' <div class="form-group row alert alert-dark text-left">
<B>3. อารมณ์และการแสดงออก (Mood and Affect)</B><br>
</div>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label><b>3.1 พื้นฐานอารมณ์ (Mood)</b></label>&nbsp;'.
$heart_disease_history_1.'&nbsp;เศร้า&nbsp;'
.$heart_disease_history_1.'&nbsp;หงุดหงิด&nbsp;'
.$heart_disease_history_1.'&nbsp;กังวล&nbsp;'
.$heart_disease_history_1.'&nbsp;ครื้นเครง&nbsp;'
.$heart_disease_history_2.'&nbsp;อื่นๆ&nbsp;'
.'<u>'.$heart_disease_history.'</u>'
.'</div>'.
'<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label><b>3.2 อารมณ์ที่แสดงออกขณะนั้น (Affect)</b></label>&nbsp;'.
$heart_disease_history_1.'&nbsp;อารมณ์ดี&nbsp;'
.$heart_disease_history_1.'&nbsp;เศร้า&nbsp;'
.$heart_disease_history_1.'&nbsp;แสดงออกเล็กน้อย&nbsp;'
.$heart_disease_history_2.'&nbsp;ปราศจากอารมณ์&nbsp;'
.$heart_disease_history_2.'&nbsp;เหมาะสมกับสิ่งที่เล่า&nbsp;'
.$heart_disease_history_2.'&nbsp;ไม่เหมาะสมกับสิ่งที่เล่า&nbsp;'
.$heart_disease_history_2.'&nbsp;คงที่&nbsp;'
.$heart_disease_history_2.'&nbsp;เปลี่ยนแปลงง่าย&nbsp;'
.'</div>'.

'</td>'.
'</tr>'.

'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
' <div class="form-group row alert alert-dark text-left">
<B>4. ความคิด (Thought)</B><br>
</div>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label><b>4.1 กระบวนความคิด </b></label>&nbsp;'.
$heart_disease_history_1.'&nbsp;คิดช้า&nbsp;'
.$heart_disease_history_1.'&nbsp;คิดเร็ว&nbsp;'
.$heart_disease_history_1.'&nbsp;คิดเร็วมากเปลี่ยนเรื่องคุยบ่อย&nbsp;'
.$heart_disease_history_1.'&nbsp;ความคิดต่อเนื่อง&nbsp;'
.$heart_disease_history_1.'&nbsp;ความคิดไม่ต่อเนื่อง&nbsp;'
.$heart_disease_history_1.'&nbsp;ตรงคำถาม&nbsp;'
.$heart_disease_history_1.'&nbsp;ไม่ตรงคำถาม&nbsp;'
.$heart_disease_history_1.'&nbsp;ได้เรื่องราว&nbsp;'
.$heart_disease_history_1.'&nbsp;ไม่ได้เรื่องราว&nbsp;'
.$heart_disease_history_1.'&nbsp;มีเหตุผล&nbsp;'
.$heart_disease_history_1.'&nbsp;ไม่มีเหตุผล&nbsp;'
.'</div>'.
'<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label><b>4.2 เนื้อหาความคิด</b></label>&nbsp;'.
$heart_disease_history_1.'&nbsp;ปกติ&nbsp;'
.$heart_disease_history_1.'&nbsp;หมกมุ่น&nbsp;'
.$heart_disease_history_1.'&nbsp;ย้ำคิดย้ำทำ&nbsp;'
.$heart_disease_history_2.'&nbsp;กลัวผิคปกติ&nbsp;'
.$heart_disease_history_2.'&nbsp;หลงผิด&nbsp;'
.$heart_disease_history_2.'&nbsp;คิดฆ่าตัวตาย&nbsp;'

.'</div>'.

'</td>'.
'</tr>'.


'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
' <div class="form-group row alert alert-dark text-left">
<B>5. การรับรู้ (Perception)</B><br>
</div>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label><b>5.1 อาการแปลสิ่งเร้าผิด (illution) </b></label>&nbsp;'.
$heart_disease_history_1.'&nbsp;ไม่มี&nbsp;'
.$heart_disease_history_1.'&nbsp;มี ระบุ&nbsp;'
.'<u>'.$heart_disease_history.'</u>'

.'</div>'.
'<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label><b>5.2 อาการประสาทหลอน (Hallucination)</b></label>&nbsp;'.
$heart_disease_history_1.'&nbsp;ไม่มี&nbsp;'
.$heart_disease_history_1.'&nbsp;มี ระบุ&nbsp;'
.'<u>'.$heart_disease_history.'</u>'

.'</div>'.
'<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label></label>&nbsp;'.
$heart_disease_history_1.'&nbsp;การมองเห็น&nbsp;'
.'<u>'.$heart_disease_history.'</u>'
.$heart_disease_history_1.'&nbsp;การได้ยิน&nbsp;'
.'<u>'.$heart_disease_history.'</u>'
.$heart_disease_history_1.'&nbsp;การรับรู้รส&nbsp;'
.'<u>'.$heart_disease_history.'</u>'
.$heart_disease_history_1.'&nbsp;การสัมผัส&nbsp;'
.'<u>'.$heart_disease_history.'</u>'
.$heart_disease_history_1.'&nbsp;การได้กลิ่น&nbsp;'
.'<u>'.$heart_disease_history.'</u>'
.'</div>'.

'</td>'.
'</tr>'.


'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
' <div class="form-group row alert alert-dark text-left">
<B>6. Cognitive Function</B><br>
</div>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label><b>6.1 Orientation</b></label>&nbsp;'.
$heart_disease_history_1.'&nbsp;<b>รับรู้</b>&nbsp;'
.$heart_disease_history_1.'&nbsp;เวลา&nbsp;'
.$heart_disease_history_1.'&nbsp;สถานที่&nbsp;'
.$heart_disease_history_1.'&nbsp;บุคคล&nbsp;'
.$heart_disease_history_1.'&nbsp;<b>ไม่รับรู้</b>&nbsp;'
.$heart_disease_history_1.'&nbsp;เวลา&nbsp;'
.$heart_disease_history_1.'&nbsp;สถานที่&nbsp;'
.$heart_disease_history_1.'&nbsp;บุคคล&nbsp;'

.'</div>'.
'<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label><b>6.2 Attention and Concentation</b></label>&nbsp;<br>'.
'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- เอา 20 ลบทีละ3&nbsp;'.$heart_disease_history_1.'&nbsp;ทำได้&nbsp;'
.$heart_disease_history_1.'&nbsp;ทำไม่ได้&nbsp;'
.$heart_disease_history_1.'&nbsp;ทำได้บางส่วน&nbsp;'
.'</div>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label></label>&nbsp;<br>'.
'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- เอา 100 ลบทีละ7&nbsp;'.$heart_disease_history_1.'&nbsp;ทำได้&nbsp;'
.$heart_disease_history_1.'&nbsp;ทำไม่ได้&nbsp;'
.$heart_disease_history_1.'&nbsp;ทำได้บางส่วน&nbsp;'
.'</div>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label></label>&nbsp;<br>'.
'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- อ่านเลขแล้วให้พูดตาม พูดทวน (ปกติจะพูดตามได้ 6-7 หลัก พูดทวน 4-5 หลัก)&nbsp;'.$heart_disease_history_1.'&nbsp;ทำได้&nbsp;'
.$heart_disease_history_1.'&nbsp;ทำไม่ได้&nbsp;'
.$heart_disease_history_1.'&nbsp;ทำได้บางส่วน&nbsp;'
.'</div>'.


'<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label><b>6.3 Memory</b></label>&nbsp;<br>'.
'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- ความจำในช่วงเวลา เป็น นาที ชั่วโมง หรือ วัน (Recent memory)&nbsp;'.$heart_disease_history_1.'&nbsp;บอกถูก&nbsp;'
.$heart_disease_history_1.'&nbsp;บอกไม่ถูก&nbsp;'
.'</div>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label></label>&nbsp;<br>'.
'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- ความจำระยะสั้น (Recall memory) (พูดคำว่า ดอกไม้ เก้าอี้ รถไฟ แล้วคุยเรื่องอื่นนาน 5 นาที แล้วถามผู้ป่วย)&nbsp;'.$heart_disease_history_1.'&nbsp;บอกถูก&nbsp;'
.$heart_disease_history_1.'&nbsp;บอกไม่ได้&nbsp;'

.'</div>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label></label>&nbsp;<br>'.
'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>- ความจำในอดีต (Remote memory)</b> &nbsp;'.$heart_disease_history_1.'&nbsp;บอกถูก&nbsp;'
.$heart_disease_history_1.'&nbsp;บอกไม่ได้&nbsp;'
.'</div>'.

'<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label><b>6.4 General Knowledge ถามความรู้ทั่วไป เช่น สัปดาห์หนึ่งมีกี่วัน</b></label>&nbsp;'
.$heart_disease_history_1.'&nbsp;บอกถูก&nbsp;'
.$heart_disease_history_1.'&nbsp;บอกไม่ถูก&nbsp;'
.'</div>'.

'<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label><b>6.5 Abtstract thinking</b></label>&nbsp;'
.'</div>'.

'<div class="row">

<div class="col-md-4 col-sm-4">
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label><b>1. ถามความแตกต่าง
        </b></label>
    <div class="custom-control custom-checkbox col-sm-5">
        <label class="custom-control">กลางวันกับกลางคืน</label>
    </div>
    <div class="custom-control custom-checkbox col-sm-5">
        <label class="custom-control">เด็กกับคนแคระ</label>
    </div>
    <div class="custom-control custom-checkbox col-sm-5">
        <label class="custom-control">ต้นมะเขือกับต้นโพธิ์</label>
    </div>


</div>

<div class="col-md-4 col-sm-4">
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label><b>concrete
        </b></label>

    &nbsp;&nbsp;&nbsp;&nbsp;
    <div class="custom-control custom-checkbox col-sm-5">

        <input type="checkbox" class="custom-control-input" id="concrete_difference1" value="Y" name="concrete_difference1">
        <label class="custom-control-label" for="concrete_difference1">พระอาทิตย์กับพระจันทร์</label>

    </div>

    <div class="custom-control custom-checkbox col-sm-5">
        <input type="checkbox" class="custom-control-input" id="concrete_difference2" value="Y" name="concrete_difference2">
        <label class="custom-control-label" for="concrete_difference2">สูงไม่เท่ากัน</label>
    </div>
    <div class="custom-control custom-checkbox col-sm-5">
        <input type="checkbox" class="custom-control-input" id="concrete_difference3" value="Y" name="concrete_difference3">
        <label class="custom-control-label" for="concrete_difference3">ต้นเล็กกับต้นใหญ่</label>
    </div>

</div>

<div class="col-md-4 col-sm-4">
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label><b>Abstract
        </b></label>

    &nbsp;&nbsp;&nbsp;&nbsp;
    <div class="custom-control custom-checkbox col-sm-5">

        <input type="checkbox"  class="custom-control-input" id="abstract_difference1" value="Y" name="abstract_difference1">
        <label class="custom-control-label" for="abstract_difference1">สว่างกับมืด</label>

    </div>

    <div class="custom-control custom-checkbox col-sm-5">
        <input type="checkbox" class="custom-control-input" id="abstract_difference2" value="Y" name="abstract_difference2">
        <label class="custom-control-label" for="abstract_difference2">เด็กกับผู้ใหญ่</label>
    </div>
    <div class="custom-control custom-checkbox col-sm-5">
        <input type="checkbox" class="custom-control-input" id="abstract_difference3" value="Y" name="abstract_difference3">
        <label class="custom-control-label" for="abstract_difference3">ไม้ล้มลุกกับไม้ยืนต้น</label>
    </div>

</div>


</div>
'.

'</td>'.
'</tr>'.


'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
' <div class="form-group row alert alert-dark text-left">
<B>2.ด้านการหายใจ (Aeration)</B>
</div>'.
'</td>'.
'</tr>'.

'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>ประวัติโรคปอด</label>&nbsp;'.
$history_of_lung_disease_1.'&nbsp;ไม่มี&nbsp;'.$history_of_lung_disease_2.'&nbsp;มี ระบุ&nbsp;'
.'<u>'.$history_of_lung_disease.'</u>'.
'</div>
<br>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>การหายใจ RR</label>&nbsp;<u>'.
nl2br(htmlspecialchars($row['rr'])).'</u>&nbsp;/min'
.'<label>&nbsp;O2Sat&nbsp;</label><u>'.nl2br(htmlspecialchars($row['o2_sat'])).'</u>&nbsp;%&nbsp;'
.'<label>On</label>&nbsp;'.
$et_other_1.'&nbsp;ET-Tube&nbsp;'
.$et_other_2.'&nbsp;TT-Tube No&nbsp;'
.'<u>'.nl2br(htmlspecialchars($row['et_tube_no'])).'</u> ขีด'
.'<u>'.nl2br(htmlspecialchars($row['et_tube_no2'])).'</u> cms.&nbsp;'
.$et_other_3.'&nbsp;O<sub>2</sub>HFNC&nbsp;'
.'<u>'.nl2br(htmlspecialchars($row['o2_hfnc'])).'</u>'
.$et_other_4.'&nbsp;Candular&nbsp;'
.'<u>'.nl2br(htmlspecialchars($row['candular'])).'</u>'
.$et_other_5.'&nbsp;Mark c bag&nbsp;'
.'<u>'.nl2br(htmlspecialchars($row['mark_c_bag'])).'</u>'
.$et_other_6.'&nbsp;RA&nbsp;'
.'</div>
<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>ลักษณะการหายใจ</label>&nbsp;'.
$breathing_characteristics_1.'&nbsp;หายใจหอบ&nbsp;'
.$breathing_characteristics_2.'&nbsp;หายใจลำบาก&nbsp;'
.$breathing_characteristics_3.'&nbsp;หายใจปกติ&nbsp;'
.'</div>
<br>'.
'&nbsp;&nbsp;&nbsp;&nbsp;<label>On ICD</label>&nbsp;'.
$on_icd_1.'&nbsp;ไม่มี&nbsp;'.$on_icd_2.'&nbsp;มี ข้าง&nbsp;'
.'<u>'.$on_icd.'</u> ขีด'
.'&nbsp;<u>'.nl2br(htmlspecialchars($row['on_icd_2'])).'</u>'
.'</div>
<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>ฟังเสียงลมเข้าปอด</label>&nbsp;'.
$listen_sound_lungs_1.'&nbsp;Clear&nbsp;'
.$listen_sound_lungs_2.'&nbsp;Crepitation&nbsp;'
.$listen_sound_lungs_3.'&nbsp;Wheezing&nbsp;'
.$listen_sound_lungs_4.'&nbsp;Rhonchi&nbsp;'
.$listen_sound_lungs_5.'&nbsp;Stridor&nbsp;'
.'</div>
<br>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>CXR</label>&nbsp;<u>'.
nl2br(htmlspecialchars($row['cxr'])).'</u>'
.'<label>&nbsp;Sputum G/S&nbsp;</label><u>'.nl2br(htmlspecialchars($row['sputum'])).'</u>'
.'<label>&nbsp;ABG/VBG:PH&nbsp;</label><u>'.nl2br(htmlspecialchars($row['abg'])).'</u>'
.'<label>&nbsp;PaCO2&nbsp;</label><u>'.nl2br(htmlspecialchars($row['pa_co2'])).'</u>'
.'<label>&nbsp;HCO3&nbsp;</label><u>'.nl2br(htmlspecialchars($row['hco3'])).'</u>'
.'<label>&nbsp;PaO2&nbsp;</label><u>'.nl2br(htmlspecialchars($row['pao2'])).'</u>'
.'<label>&nbsp;BE&nbsp;</label><u>'.nl2br(htmlspecialchars($row['be'])).'</u>'
.'</div>
<br>'.

'</td>'.
'</tr>'.

'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
' <div class="form-group row alert alert-dark text-left">
<B>3.ด้านภาวะโภชนาการ (Nutrition)</B>
</div>'.
'</td>'.
'</tr>'.

'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>ประวัติโรคระบบทางเดินอาหาร</label>&nbsp;'.
$history_of_gastrointestinal_1.'&nbsp;ไม่มี&nbsp;'.$history_of_gastrointestinal_2.'&nbsp;มี ระบุ&nbsp;'
.'<u>'.$history_of_gastrointestinal.'</u>'.
'</div>
<br>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>ส่วนสูง</label>&nbsp;<u>'.
nl2br(htmlspecialchars($row['hight'])).'</u> cms'
.'<label>&nbsp;น้ำหนัก&nbsp;</label><u>'.nl2br(htmlspecialchars($row['bw'])).'</u> kg'
.'<label>&nbsp;BMI:&nbsp;</label><u>'.nl2br(htmlspecialchars($row['bmi'])).'</u> Kg/m<sup>2</sup>'
.'<label>&nbsp;Alb&nbsp;</label><u>'.nl2br(htmlspecialchars($row['alb'])).'</u> mmol'
.'<label>&nbsp;BEE:&nbsp;</label><u>'.nl2br(htmlspecialchars($row['bee'])).'</u>'
.'<label>&nbsp;TEE:&nbsp;</label><u>'.nl2br(htmlspecialchars($row['tee'])).'</u>'
.'<label>&nbsp;SPENT Nutrition Screening Tool&nbsp;</label><u>'.nl2br(htmlspecialchars($row['spent'])).'</u> / 4 คะแนน'
.'</div>
<br>'.

'</td>'.
'</tr>'.

'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
' <div class="form-group row alert alert-dark text-left">
<B>4.ด้านการติดต่อสื่อสาร (Communication)</B>
</div>'.
'</td>'.
'</tr>'.

'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>ประวัติโรคทางการสื่อสาร</label>&nbsp;'.
$communication_history_1.'&nbsp;ไม่มี&nbsp;'.$communication_history_2.'&nbsp;มี ระบุ&nbsp;'
.'<u>'.$communication_history.'</u>'.
'</div>
<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>การพูด:</label>&nbsp;'.
$speaking_check.'&nbsp;ไม่ได้ On ET-Tube&nbsp;'
.$speaking_2.'&nbsp;พูดได้เองชัดเจน&nbsp;'
.$speaking_3.'&nbsp;พูดไม่ชัด&nbsp;'
.$speaking_4.'&nbsp;อื่นๆ&nbsp;'
.'<u>'.$speaking.'</u>'.
'</div>
<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
$communication_check.'&nbsp;On ET-Tube or TT&nbsp;'
.$communication_2.'&nbsp;สื่อสารด้วยการเขียน&nbsp;'
.$communication_3.'&nbsp;สื่อสารโดยการใช้สายตา&nbsp;'
.$communication_4.'&nbsp;สื่อสารโดยใช้ท่าทาง&nbsp;'
.$communication_6.'&nbsp;ไม่สามารถสื่อสารได้ เนื่องจาก&nbsp;'
.'<u>'.$communication.'</u>&nbsp;'
.$communication_5.'&nbsp;ประเมินไม่ได้&nbsp;'.
'</div>
<br>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>การมองเห็น : ตา</label>&nbsp;'.
$vision_1.'&nbsp;เห็นได้ชัดเจน&nbsp;'
.$vision_2.'&nbsp;เห็นไม่ชัดเจน&nbsp;'
.$vision_4.'&nbsp;ตาบอด&nbsp;'
.'<u>'.$vision.'</u>&nbsp;'
.$vision_3.'&nbsp;ประเมินไม่ได้&nbsp;'.
'</div>
<br>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>การได้ยิน : หู</label>&nbsp;'.
$listening_1.'&nbsp;ได้ยินชัดเจน&nbsp;'
.$listening_2.'&nbsp;หูหนวก&nbsp;'
.$listening_3.'&nbsp;ได้ยินไม่ชัด&nbsp;:&nbsp;ใช้อุปกรณ์ช่วยฟัง'
.$hearing_aids_1.'&nbsp;มี&nbsp;'
.$hearing_aids_2.'&nbsp;ไม่มี&nbsp;'
.$listening_4.'&nbsp;ประเมินไม่ได้&nbsp;'
.'</div>
<br>'.

'</td>'.
'</tr>'.

'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
' <div class="form-group row alert alert-dark text-left">
<B>5.ด้านการทำกิจกรรม (Activity)</B>
</div>'.
'</td>'.
'</tr>'.
'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>มีประวัติโรคที่ส่งผลต่อการทำกิจกรรม</label>&nbsp;'.
$history_affects_activities_1.'&nbsp;ไม่มี&nbsp;'.$history_affects_activities_2.'&nbsp;มี ระบุ&nbsp;'
.'<u>'.$history_affects_activities.'</u>'.
'</div>
<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>การทำกิจวัตรประจำวัน</label>&nbsp;'.
$daily_activities_1.'&nbsp;ช่วยเหลือตัวเองได้ดี&nbsp;'
.$daily_activities_2.'&nbsp;Bed ridden&nbsp;'
.$daily_activities_3.'&nbsp;หอบ เหนื่อย&nbsp;'
.$daily_activities_4.'&nbsp;ถูกจำกัดกิจกรรมบนเตียง&nbsp;'
.'</div>
<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$daily_activities_1.'&nbsp;มี Fracture ตำแหน่ง&nbsp;<u>'.
nl2br(htmlspecialchars($row['fracture'])).'</u>'
.'<label>&nbsp;Braden score&nbsp;</label><u>'.nl2br(htmlspecialchars($row['braden_score'])).'</u> / 23 คะแนน'
.'<label>&nbsp;Mortor power&nbsp;</label><u>'.nl2br(htmlspecialchars($row['mortor_power'])).'</u>'
.'<label>&nbsp;MASS&nbsp;</label><u>'.nl2br(htmlspecialchars($row['mass'])).'</u> / 6 คะแนน'
.'</div>
<br>'.

'</td>'.
'</tr>'.

'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
' <div class="form-group row alert alert-dark text-left">
<B>6.ด้านการกระตุ้น (Stimulation)</B>
</div>'.
'</td>'.
'</tr>'.

'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>มีประวัติโรคที่ส่งผลต่อการกระตุ้น</label>&nbsp;'.
$history_affects_stimulation_1.'&nbsp;ไม่มี&nbsp;'.$history_affects_stimulation_2.'&nbsp;มี ระบุ&nbsp;'
.'<u>'.$history_affects_stimulation.'</u>'.
'</div>
<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>GCS: E</label>&nbsp;<u>'.
nl2br(htmlspecialchars($row['gcs_e'])).'</u>'
.'<label>&nbsp;V:&nbsp;</label><u>'.nl2br(htmlspecialchars($row['gcs_v'])).'</u>'
.'<label>&nbsp;M:&nbsp;</label><u>'.nl2br(htmlspecialchars($row['gcs_m'])).'</u>'
.'<label>&nbsp;Pupil:&nbsp;</label><u>'.nl2br(htmlspecialchars($row['pupil'])).'</u>'
.'<label>&nbsp;RE:&nbsp;</label><u>'.nl2br(htmlspecialchars($row['pupil_rt'])).'</u> mm'
.'<label>&nbsp;LE:&nbsp;</label><u>'.nl2br(htmlspecialchars($row['pupil_lt'])).'</u> mm'
.'</div>
<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>ระดับความรู้สึกตัว</label>&nbsp;'.
$level_of_consciousness_1.'&nbsp;Alert&nbsp;'
.$level_of_consciousness_2.'&nbsp;Confuse&nbsp;'
.$level_of_consciousness_3.'&nbsp;Drowsiness&nbsp;'
.$level_of_consciousness_4.'&nbsp;Stupors&nbsp;'
.$level_of_consciousness_5.'&nbsp;Coma&nbsp;'
.'</div>
'.
'<br><B>ผล CT-Brain</B>&nbsp;'.nl2br(htmlspecialchars($row['ct_brain']))
.'</br>'
.'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>Pain score</label>&nbsp;'
.$pain_score_1.'&nbsp;COPT&nbsp;'
.'<u>'.nl2br(htmlspecialchars($row['copt'])).'</u> / 8 คะแนน'
.$pain_score_2.'&nbsp;NRS&nbsp;'
.'<u>'.nl2br(htmlspecialchars($row['nrs'])).'</u> / 10 คะแนน'

.'</div>
<br>'.

'</td>'.
'</tr>'.

'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 40px;padding:4px;vertical-align:text-top;">'.
' <div class="form-group row  text-left">
<h3><B>สรุปปัญหา</B></h3>
</div><br>'

.'<div class="row">

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
.$fluid_balance.'&nbsp;ด้านสมดุลของสารน้ำ(Fluid balance)'
.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$aeration.'&nbsp;ด้านการหายใจ(Aeration)'
.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$nutrition.'&nbsp;ด้านภาวะโภชนาการ(Nutrition)'
.'</div>'
.'<br>'
.'<div class="row">

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
.$communication_problem.'&nbsp;ด้านการติดต่อสื่อสาร(Communication)'
.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$activity.'&nbsp;ด้านการทำกิจจกรรม(Activity)'
.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$stimulation.'&nbsp;ด้านการกระตุ้น(Stimulation)'
.'</div>'



.'</td>'.
'</tr>'.

 '</table>'

.'</div>

    <table id="bg-table" width="100%" style="border-collapse: collapse;font-size:8pt;margin-top:2px;">
   
        <tr style="border:1px solid #000;margin: 35px;"> /* ชื่อ-สกุล */
            <td  colspan="3" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">
            <label>HN : '.htmlspecialchars($row_ipt['hn']).' | AN : '.htmlspecialchars($an).'</label>
            <label>ชื่อ - สกุล : '.htmlspecialchars($row_ipt['pname'].$row_ipt['fname']." ".$row_ipt['lname']).' | </label>
            <label>อายุ : '.htmlspecialchars($row_ipt['age_y']." ปี ".$row_ipt['age_m']." เดือน ".$row_ipt['age_d']." วัน ").' | </label>
            <label>ตึก : '.htmlspecialchars($row_ipt['name']).' | </label>
            <label>เตียง : '.htmlspecialchars($row_ipt['bedno']).' | </label>
            <label>สิทธิ : ('.htmlspecialchars($row_ipt['pttype']).') '.htmlspecialchars($row_ipt['pttype_name']).'</label>
            </td>
           
        </tr>
    </table>'
   

      .'<br>'                     
      .$icu_form1.'</br>'.$icu_form2.'</br>'.$icu_form3.'<footter> <h2 style="text-align:right;font-size:8pt;">ประกาศใช้ 29 มกราคม 2566 งานเอกสารคุณภาพ ศูนย์คุณภาพ</h2> </footer>' ;
//$mpdf->SetColumns(2);

$mpdf->setFooter('HN: '.htmlspecialchars($hn).' AN: '.htmlspecialchars($an).' Page '.'{PAGENO}');
$mpdf->WriteHTML($head);
$mpdf->Output();
