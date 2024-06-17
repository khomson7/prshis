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

Session::insertSystemAccessLog(json_encode(array(
    'report'=>'PRE-NURSENOTE-PDF',
   // 'action'=>'PRINT',
    'an'=>$an,
),JSON_UNESCAPED_UNICODE));

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
        WHERE pn.an = :an
        limit 1";
$stmt = $conn->prepare($sql);
$stmt->execute($query_parameters);
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

  
  


 $depart_1 = '( )';
 if ($row['depart'] == 'OPD') {
   $depart_1 = '('.$image_check.')';
 }

 $depart_2 = '( )';
 if ($row['depart'] == 'ER') {
   $depart_2 = '('.$image_check.')';
 }

 $depart_3 = '( )';
 if ($row['depart'] != 'OPD' && $row['depart'] != 'ER' && $row['depart'] != null) {$depart_3 = '('.$image_check.')';
    $depart  =  htmlspecialchars($row['depart']);
 }


 $hospital_by_1 = '( )';
 if ($row['hospital_by'] == 'เดินมา') {$hospital_by_1 = '('.$image_check.')';
 }

 $hospital_by_2 = '( )';
 if ($row['hospital_by'] == 'รถนั่ง') {$hospital_by_2 = '('.$image_check.')';
 }

 $hospital_by_3 = '( )';
 if ($row['hospital_by'] == 'รถนอน') {$hospital_by_3 = '('.$image_check.')';
 }

 $hospital_by_4 = '( )';
 if ($row['hospital_by'] != 'เดินมา' && $row['hospital_by'] != 'รถนั่ง' && $row['hospital_by'] != 'รถนอน' && $row['hospital_by'] != null) {$hospital_by_4 = '('.$image_check.')';
    $hospital_by  =  htmlspecialchars($row['hospital_by']);
 }

 //โรคประจำคัว
 $c_chronic_1 = '( )';
 if ($row['c_chronic'] == 'ปฏิเสธ') {$c_chronic_1 = '('.$image_check.')';
 }

 $c_chronic_2 = '( )';
 if ($row['c_chronic'] != 'ปฏิเสธ' && $row['c_chronic'] != null) {$c_chronic_2 = '('.$image_check.')';
    $c_chronic  =  htmlspecialchars($row['c_chronic']);
 }

 //การรัคษาในโรงพยาบาล
 $hos_history_1 = '( )';
 if ($row['hos_history'] == 'ปฏิเสธ') {$hos_history_1 = '('.$image_check.')';
 }

 $hos_history_2 = '( )';
 if ($row['hos_history'] != 'ปฏิเสธ' && $row['hos_history'] != null) {$hos_history_2 = '('.$image_check.')';
    $hos_history =  htmlspecialchars($row['hos_history']);
 }

  //ประวัติการผ่าตัด
  $h_sergery_1 = '( )';
  if ($row['h_sergery'] == 'ปฏิเสธ') {$h_sergery_1 = '('.$image_check.')';
  }
 
  $h_sergery_2 = '( )';
  if ($row['h_sergery'] != 'ปฏิเสธ' && $row['h_sergery'] != null) {$h_sergery_2 = '('.$image_check.')';
     $h_sergery =  htmlspecialchars($row['h_sergery']);
  }

  //ประวัติกแพ้ยา
  $h_allergy_1 = '( )';
  if ($row['h_allergy'] == 'ปฏิเสธ') {$h_allergy_1 = '('.$image_check.')';
  }
 
  $h_allergy_2 = '( )';
  if ($row['h_allergy'] != 'ปฏิเสธ' && $row['h_allergy'] != null) {$h_allergy_2 = '('.$image_check.')';
     $h_allergy =  htmlspecialchars($row['h_allergy']);
  }

  

     //ประวัติกแพ้ยา
     $history_of_drug_1 = '( )';
     if ($row['history_of_drug'] == 'ปฏิเสธ') {$history_of_drug_1 = '('.$image_check.')';
     }
    
     $history_of_drug_2 = '( )';
     if ($row['history_of_drug'] != 'ปฏิเสธ' && $row['history_of_drug'] != null) {$history_of_drug_2 = '('.$image_check.')';
        $history_of_drug =  htmlspecialchars($row['history_of_drug']);
     }

      //ประวัติกแพ้ยา
      $vaccine_history_1 = '( )';
      if ($row['vaccine_history'] == 'ครบตามเกณฑ์') {$vaccine_history_1 = '('.$image_check.')';
      }
     
      $vaccine_history_2 = '( )';
      if ($row['vaccine_history'] != 'ครบตามเกณฑ์' && $row['vaccine_history'] != null) {$vaccine_history_2 = '('.$image_check.')';
         $vaccine_history =  htmlspecialchars($row['vaccine_history']);
      }

        //ประวัติกแพ้ยา
        $child_devilopment_1 = '( )';
        if ($row['child_devilopment'] == 'สมวัย') {$child_devilopment_1 = '('.$image_check.')';
        }
       
        $child_devilopment_2 = '( )';
        if ($row['child_devilopment'] != 'สมวัย' && $row['child_devilopment'] != null) {$child_devilopment_2 = '('.$image_check.')';
           $child_devilopment =  htmlspecialchars($row['child_devilopment']);
        }

          //ประวัติกแพ้ยา
  $pmh2_1 = '( )';
  if ($row['pmh2'] == 'ปฏิเสธ') {$pmh2_1 = '('.$image_check.')';
  }
 
  $pmh2_2 = '( )';
  if ($row['pmh2'] != 'ปฏิเสธ' && $row['pmh2'] != null) {$pmh2_2 = '('.$image_check.')';
     $pmh2=  htmlspecialchars($row['pmh2']);
  }
        

           //ระดับความรู้สึกตัว
           $level_of_con_1 = '( )';
           if ($row['level_of_con'] == 'รู้สึกตัวดี') {$level_of_con_1 = '('.$image_check.')';
           }

           $level_of_con_2 = '( )';
           if ($row['level_of_con'] == 'สับสน') {$level_of_con_2 = '('.$image_check.')';
           }

           $level_of_con_3 = '( )';
           if ($row['level_of_con'] == 'ซึม') {$level_of_con_3 = '('.$image_check.')';
           }

           $level_of_con_4 = '( )';
           if ($row['level_of_con'] == 'ไม่รู้สึกตัว') {$level_of_con_4 = '('.$image_check.')';
           }

           $breathing_1 = '( )';
 if ($row['breathing'] == 'ปกติ') {$breathing_1 = '('.$image_check.')';
 }
           $breathing_2 = '( )';
 if ($row['breathing'] == 'หายใจหอบ') {$breathing_2 = '('.$image_check.')';
 }

 $breathing_3 = '( )';
 if ($row['breathing'] == 'หายใจลำบาก') {$breathing_3 = '('.$image_check.')';
 }

 $breathing_4 = '( )';
 if ($row['breathing'] == 'ไม่หายใจ') {$breathing_4 = '('.$image_check.')';
 }

 $breathing_5 = '( )';
 if ($row['breathing'] != 'ปกติ' && $row['breathing'] != 'หายใจหอบ' && $row['breathing'] != 'หายใจลำบาก' && $row['breathing'] != 'ไม่หายใจ' && $row['breathing'] != null) {$breathing_5 = '('.$image_check.')';
    $breathing  =  htmlspecialchars($row['breathing']);
 }

 $blood_circulation_1 = '( )';
 if ($row['blood_circulation'] == 'ปกติ') {$blood_circulation_1 = '('.$image_check.')';
 }

 $blood_circulation_2 = '( )';
 if ($row['blood_circulation'] == 'ซีด') {$blood_circulation_2 = '('.$image_check.')';
 }

 $blood_circulation_3 = '( )';
 if ($row['blood_circulation'] == 'ปลายมือปลายเท้าเขียว') {$blood_circulation_3 = '('.$image_check.')';
 }

 $blood_circulation_4 = '( )';
 if ($row['blood_circulation'] == 'รอบปากเขียว') {$blood_circulation_4 = '('.$image_check.')';
 }

 $blood_circulation_5 = '( )';
 if ($row['blood_circulation'] == 'เขียวทั่วตัว') {$blood_circulation_5 = '('.$image_check.')';
 }
   
 $swelling_1 = '( )';
 if ($row['swelling'] == 'ไม่มี') {$swelling_1 = '('.$image_check.')';
 }

 $swelling_2 = '( )';
 if ($row['swelling'] != 'ไม่มี' && $row['swelling'] != null) {$swelling_2 = '('.$image_check.')';
    $swelling=  htmlspecialchars($row['swelling']);
 }

 $communication_ears_1 = '( )';
 if ($row['communication_ears'] == 'ได้ยินชัดเจน') {$communication_ears_1 = '('.$image_check.')';
 }

 $communication_ears_2 = '( )';
 if ($row['communication_ears'] == 'ได้ยินไม่ชัดเจน') {$communication_ears_2 = '('.$image_check.')';
 }

 $hearing_aid_1 = '( )';
 if ($row['hearing_aid'] == 'มี') {$hearing_aid_1 = '('.$image_check.')';
 }

 $hearing_aid_2 = '( )';
 if ($row['hearing_aid'] == 'ไม่มี') {$hearing_aid_2 = '('.$image_check.')';
 }

 $communication_eyes_1 = '( )';
 if ($row['communication_eyes'] == 'เห็นชัดเจน') {$communication_eyes_1 = '('.$image_check.')';
 }

 $communication_eyes_2 = '( )';
 if ($row['communication_eyes'] == 'เห็นไม่ชัดเจน') {$communication_eyes_2 = '('.$image_check.')';
 }

 $glasses_1 = '( )';
 if ($row['glasses'] == 'สวม') {$glasses_1 = '('.$image_check.')';
 }

 $glasses_2 = '( )';
 if ($row['glasses'] == 'ไม่สวม') {$glasses_2 = '('.$image_check.')';
 }


 $communication_speak_1 = '( )';
 if ($row['communication_speak'] == 'ชัดเจน') {$communication_speak_1 = '('.$image_check.')';
 }
           $communication_speak_2 = '( )';
 if ($row['communication_speak'] == 'พูดติดอ่าง') {$communication_speak_2 = '('.$image_check.')';
 }

 $communication_speak_3 = '( )';
 if ($row['communication_speak'] == 'เป็นใบ้') {$communication_speak_3 = '('.$image_check.')';
 }


 $communication_speak_4 = '( )';
 if ($row['communication_speak'] != 'ชัดเจน' && $row['communication_speak'] != 'พูดติดอ่าง' && $row['communication_speak'] != 'เป็นใบ้'  && $row['communication_speak'] != null) {$communication_speak_4 = '('.$image_check.')';
    $communication_speak  =  htmlspecialchars($row['communication_speak']);
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

        $id = '17'; //Link menu
        $check_    = ReportQueryUtils::getProduction($id);

        $check_report = '( )';
        if ($check_  == '1') 
        {$check_report = '&nbsp;<font color="red">รอปรับรายงาน</font>';
        } else {
            $check_report = '';
        }
       
        
       

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
    <h2 style="text-align:right;font-size:8pt;">FM-ICU-005-00</h2>
    
    <h2 style="text-align:center;font-size:11pt;">แบบประเมินผู้ป่วยวิกฤตแรกรับตามแนวคิด FANCAS &nbsp;'.htmlspecialchars(DbConstant::HOSPITAL_NAME).$check_report.'</h2>
    
   
<div class="f15"> วันที่ <b>'.LongDateThai2($strDate).'</b>&nbsp;รับใหม่/รับย้ายเวลา <b>'.htmlspecialchars($rxtime).'</b>&nbsp;น.&nbsp;จากหน่วยงาน&nbsp;'.
$from_dep.'<br>'
.'<table id="bg-table" width="100%" style="border-collapse: collapse;font-size:8pt;margin-top:2px;">'.
'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
' <div class="form-group row alert alert-dark text-left">
<B>1. ด้านสมดุลของน้ำ(Fluid balance)</B>
</div>'.
'</td>'.
'</tr>'.
'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
' <div class="form-group row alert alert-dark text-left">
<B>1.1 Cardiovascalar system</B>
</div>'.
'</td>'.
'</tr>'.
'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>ประวัติโรคหัวใจ/หลอดเลือด</label>&nbsp;'.
$heart_disease_history_1.'&nbsp;ไม่มี&nbsp;'.$heart_disease_history_2.'&nbsp;มี ระบุ&nbsp;'
.'<u>'.$heart_disease_history.'</u>'.
'</div>
<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>ผิวหนัง</label>&nbsp;'.
$skin_1.'&nbsp;ปกติ&nbsp;'
.$skin_2.'&nbsp;ซีด&nbsp;'
.$skin_3.'&nbsp;เขียว&nbsp;'
.$skin_4.'&nbsp;จุดจ้ำเลือด&nbsp;'
.$skin_5.'&nbsp;แห้ง&nbsp;'
.$skin_6.'&nbsp;บวมกดบุ๋ม&nbsp;'
.'<u>'.$skin.'</u>'.
'</div>
<br>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>Neck vien engorement</label>&nbsp;'.
$neck_vien_engorement_1.'&nbsp;ไม่พบ&nbsp;'
.$neck_vien_engorement_3.'&nbsp;พบระบุ&nbsp;'
.'<u>'.$neck_vien_engorement.'</u>&nbsp;'
.$neck_vien_engorement_2.'&nbsp;ประเมินไม่ได้&nbsp;'
.'</div>
<br>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>ฟังเสียงหัวใจ</label>&nbsp;'.
$listen_to_the_heart_1.'&nbsp;Murmur&nbsp;'
.$listen_to_the_heart_2.'&nbsp;Rub&nbsp;'
.$listen_to_the_heart_3.'&nbsp;ไม่พบ&nbsp;'
.'</div>
<br>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>V/S&nbsp;BT</label>&nbsp;<u>'.
nl2br(htmlspecialchars($row['bt'])).'</u>&nbsp;<sup>๐</sup>C&nbsp;'
.'<label>HR&nbsp;</label><u>'.nl2br(htmlspecialchars($row['pr'])).'</u>&nbsp;/min&nbsp;'
.'<label>BP&nbsp;</label><u>'.nl2br(htmlspecialchars($row['bps'])).' / '.nl2br(htmlspecialchars($row['bpd'])).'</u>&nbsp;mmHg&nbsp;'
.'</div>
<br>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>ผลการตรวจ&nbsp;CBC : WBC</label>&nbsp;<u>'.
nl2br(htmlspecialchars($row['cbc'])).'</u>'
.'<label>&nbsp;Hct&nbsp;</label><u>'.nl2br(htmlspecialchars($row['hct'])).'</u>%'
.'<label>&nbsp;Hb&nbsp;</label><u>'.nl2br(htmlspecialchars($row['hb'])).'</u>'
.'<label>&nbsp;Plt&nbsp;</label><u>'.nl2br(htmlspecialchars($row['plt'])).'</u>'
.'<label>&nbsp;PT&nbsp;</label><u>'.nl2br(htmlspecialchars($row['pt'])).'</u>'
.'<label>&nbsp;PTT&nbsp;</label><u>'.nl2br(htmlspecialchars($row['ptt'])).'</u>'
.'<label>&nbsp;INR&nbsp;</label><u>'.nl2br(htmlspecialchars($row['inr'])).'</u>'
.'</div>
<br>'.
'<div class="row">'
.'&nbsp;&nbsp;&nbsp;&nbsp;<label>Trop -T&nbsp;</label><u>'.nl2br(htmlspecialchars($row['trop_t'])).'</u>'
.'<label>&nbsp;CKMB&nbsp;</label><u>'.nl2br(htmlspecialchars($row['ckmb'])).'</u>'
.'<label>&nbsp;CPK&nbsp;</label><u>'.nl2br(htmlspecialchars($row['cpk'])).'</u>'
.'&nbsp;&nbsp;&nbsp;&nbsp;<label>Echo&nbsp;</label><u>'.nl2br(htmlspecialchars($row['echo'])).'</u>'
.'<label>&nbsp;EKG&nbsp;</label><u>'.nl2br(htmlspecialchars($row['ekg'])).'</u>'
.'</div>
<br>'.


'</td>'.
'</tr>'.

'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
' <div class="form-group row alert alert-dark text-left">
<B>1.2 Kidney system</B>
</div>'.
'</td>'.
'</tr>'.

'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>ประวัติโรคไต</label>&nbsp;'.
$kidney_disease_history_1.'&nbsp;ไม่มี&nbsp;'
.$kidney_disease_history_2.'&nbsp;มี ระบุ&nbsp;'
.'<u>'.$kidney_disease_history.'</u>'.
'</div>
<br>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>ลักษณะปัสสาวะ</label>&nbsp;<u>'.
nl2br(htmlspecialchars($row['urine_characteristics'])).'</u>'
.'<label>&nbsp;I/O ใน 24 ชม.&nbsp;</label><u>'.nl2br(htmlspecialchars($row['io_1'])).' / '.nl2br(htmlspecialchars($row['io_2'])).'</u>&nbsp;ซีซี&nbsp;'
.'</div>
<br>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>ผลการตรวจ LAB BUN</label>&nbsp;<u>'.
nl2br(htmlspecialchars($row['bun'])).'</u>'
.'<label>&nbsp;Cr&nbsp;</label><u>'.nl2br(htmlspecialchars($row['cr'])).'</u>'
.'<label>&nbsp;GFR&nbsp;</label><u>'.nl2br(htmlspecialchars($row['gfr'])).'</u>'
.'<label>&nbsp;Elyte&nbsp;Na&nbsp;</label><u>'.nl2br(htmlspecialchars($row['e_lyte_na'])).'</u>'
.'<label>&nbsp;K&nbsp;</label><u>'.nl2br(htmlspecialchars($row['e_lyte_k'])).'</u>'
.'<label>&nbsp;Cl&nbsp;</label><u>'.nl2br(htmlspecialchars($row['e_lyte_cl'])).'</u>'
.'<label>&nbsp;Co<sub>2</sub>&nbsp;</label><u>'.nl2br(htmlspecialchars($row['e_lyte_co2'])).'</u>'
.'<label>&nbsp;Anien Gap&nbsp;</label><u>'.nl2br(htmlspecialchars($row['e_lyte_aniengap'])).'</u>'
.'<label>&nbsp;Ca&nbsp;</label><u>'.nl2br(htmlspecialchars($row['ca'])).'</u>'
.'<label>&nbsp;Po<sub>4</sub>&nbsp;</label><u>'.nl2br(htmlspecialchars($row['po_4'])).'</u>'
.'<label>&nbsp;Mg&nbsp;</label><u>'.nl2br(htmlspecialchars($row['mg'])).'</u>'
.'<label>&nbsp;DTX&nbsp;</label><u>'.nl2br(htmlspecialchars($row['dtx'])).'</u>mg%'
.'<label>&nbsp;Urine Sp.gr&nbsp;</label><u>'.nl2br(htmlspecialchars($row['urine_sr_gr'])).'</u>'
.'</div>
<br>'.

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
$breathing_characteristics_1.'&nbsp;Clear&nbsp;'
.$breathing_characteristics_2.'&nbsp;Crepitation&nbsp;'
.$breathing_characteristics_3.'&nbsp;Wheezing&nbsp;'
.$breathing_characteristics_4.'&nbsp;Rhonchi&nbsp;'
.$breathing_characteristics_5.'&nbsp;Stridor&nbsp;'
.'</div>
<br>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>ผลการตรวจ LAB BUN</label>&nbsp;<u>'.
nl2br(htmlspecialchars($row['bun'])).'</u>'
.'<label>&nbsp;Cr&nbsp;</label><u>'.nl2br(htmlspecialchars($row['cr'])).'</u>'
.'<label>&nbsp;GFR&nbsp;</label><u>'.nl2br(htmlspecialchars($row['gfr'])).'</u>'
.'<label>&nbsp;Elyte&nbsp;Na&nbsp;</label><u>'.nl2br(htmlspecialchars($row['e_lyte_na'])).'</u>'
.'<label>&nbsp;K&nbsp;</label><u>'.nl2br(htmlspecialchars($row['e_lyte_k'])).'</u>'
.'<label>&nbsp;Cl&nbsp;</label><u>'.nl2br(htmlspecialchars($row['e_lyte_cl'])).'</u>'
.'<label>&nbsp;Co<sub>2</sub>&nbsp;</label><u>'.nl2br(htmlspecialchars($row['e_lyte_co2'])).'</u>'
.'<label>&nbsp;Anien Gap&nbsp;</label><u>'.nl2br(htmlspecialchars($row['e_lyte_aniengap'])).'</u>'
.'<label>&nbsp;Ca&nbsp;</label><u>'.nl2br(htmlspecialchars($row['ca'])).'</u>'
.'<label>&nbsp;Po<sub>4</sub>&nbsp;</label><u>'.nl2br(htmlspecialchars($row['po_4'])).'</u>'
.'<label>&nbsp;Mg&nbsp;</label><u>'.nl2br(htmlspecialchars($row['mg'])).'</u>'
.'<label>&nbsp;DTX&nbsp;</label><u>'.nl2br(htmlspecialchars($row['dtx'])).'</u>mg%'
.'<label>&nbsp;Urine Sp.gr&nbsp;</label><u>'.nl2br(htmlspecialchars($row['urine_sr_gr'])).'</u>'
.'</div>
<br>'.

'</td>'.
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
    </table>
    <footter> <h2 style="text-align:right;font-size:8pt;">ประกาศใช้ 29 มกราคม 2566 งานเอกสารคุณภาพ ศูนย์คุณภาพ</h2> </footer>
';
$mpdf->WriteHTML($head);
$mpdf->Output();
