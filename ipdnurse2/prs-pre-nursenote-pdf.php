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
$sql = "SELECT pn.*,d.name as doctor_name
        FROM prs_pre_nursenote_item pni
        INNER JOIN prs_pre_nursenote pn on pn.an = pni.an
        LEFT JOIN hos.doctor d on d.code = pni.doctor
        WHERE pn.an = :an
        ORDER BY pni.id desc 
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


                        $rxDate = $row['rxdate'];//วันที่ Discharge
                        $rxdate = date($rxDate);
                        $rxTime = $row['rxtime'];//เวลาที่ Discharge
                        $rxtime = date($rxTime);
                        $strDate =($rxdate."  ".$rxtime);
                       // $dchtime  = date('H:i', strtotime($origTime));

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

 //ผิวหนัง
 $skin_1 = '( )';
 if ($row['skin'] == 'ปกติ') {$skin_1 = '('.$image_check.')';
 }

 $skin_2 = '( )';
 if ($row['skin'] == 'หนังแตก') {$skin_2 = '('.$image_check.')';
 }

 $skin_3 = '( )';
 if ($row['skin'] == 'เขียวช้ำ') {$skin_3 = '('.$image_check.')';
 }

 $skin_4 = '( )';
 if ($row['skin'] == 'ผื่นแดง') {$skin_4 = '('.$image_check.')';
 }

 $skin_5 = '( )';
 if ($row['skin'] == 'ผื่นคัน') {$skin_5 = '('.$image_check.')';
 }

 $skin_6 = '( )';
 if ($row['skin'] == 'เหลือง') {$skin_6 = '('.$image_check.')';
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

        $id = '16'; //Link menu
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
    <h2 style="text-align:right;font-size:8pt;">FM-CLT-001</h2>
    
    <h2 style="text-align:center;font-size:11pt;">ใบบันทึกประวัติและประเมินสมรรถนะผู้ป่วยแรกรับ&nbsp;'.htmlspecialchars(DbConstant::HOSPITAL_NAME).$check_report.'</h2>
    
    <div class="form-group row">
                                <label class="col-sm-12">ข้อมูลทั่วไป</label>
                            </div>
<div class="f15"> รับใหม่วันที่ '.LongDateThai2($strDate).'<b> เวลา </b>'.htmlspecialchars($rxtime).'&nbsp;น.&nbsp;จาก&nbsp;'.
$depart_1.'&nbsp;OPD&nbsp;'.$depart_2.'&nbsp;ER&nbsp;'.$depart_3.'&nbsp;อื่นๆ&nbsp;'.$depart.'&nbsp;กรณีส่งต่อ ส่งต่อจาก&nbsp;'.nl2br(htmlspecialchars($row['refer_from'])).'<br>'
.'รับไว้ในโรงพยาบาลโดย '.
$hospital_by_1.'&nbsp;เดินมา&nbsp;'.$hospital_by_2.'&nbsp;รถนั่ง&nbsp;'.$hospital_by_3.'&nbsp;รถนอน&nbsp;'.$hospital_by_4.'&nbsp;อื่นๆ&nbsp;'.$hospital_by.'<br>'
.'<B>อาการสำคัญที่นำมาโรงพยาบาล</B>&nbsp;'.nl2br(htmlspecialchars($row['cc']))
.'<br><B>ประวัติการเจ็บป่วยปัจจุบัน</B>&nbsp;'.nl2br(htmlspecialchars($row['current_illness']))
.'<br><B>ประวัติเจ็บป่วยในอดีต</B>'
.'<br>โรคประจำตัว '.
$c_chronic_1.'&nbsp;ปฏิเสธ&nbsp;'.$c_chronic_2.'&nbsp;มี ระบุ&nbsp;'.$c_chronic
.'<br>เคยรับการรักษาในโรงพยาบาล (ภายใน 1 ปี) '.
$hos_history_1.'&nbsp;ปฏิเสธ&nbsp;'.$hos_history_2.'&nbsp;เคย ระบุ&nbsp;'.$hos_history
.'<br>ประวัติการผ่าตัด '.
$h_sergery_1.'&nbsp;ปฏิเสธ&nbsp;'.$h_sergery_2.'&nbsp;เคย ระบุ&nbsp;'.$h_sergery
.'<br>ประวัติการแพ้ (ยา/อาหาร/สารเคมี/เลือด) '.
$h_allergy_1.'&nbsp;ปฏิเสธ&nbsp;'.$h_allergy_2.'&nbsp;เคย ระบุ&nbsp;'.$h_allergy
.'<br>ประวัติการได้รับวัคซีน (เฉพาะ < 15 ปี) '.
$vaccine_history_1.'&nbsp;ครบตามเกณฑ์&nbsp;'.$vaccine_history_2.'&nbsp;ไม่ครบตามเกณฑ์ ระบุ&nbsp;'.$vaccine_history
.'<br>การเจริญเติบโตและพัฒนาการ (เฉพาะ < 15 ปี) '.
$child_devilopment_1.'&nbsp;สมวัย&nbsp;'.$child_devilopment_2.'&nbsp;ไม่สมวัย ระบุ&nbsp;'.$child_devilopment
.'<br>ประวัติการใช้ยาและผลิตภัณฑ์สุขภาพ '.
$history_of_drug_1.'&nbsp;ปฏิเสธ&nbsp;'.$history_of_drug_2.'&nbsp;มีระบุ ระบุ&nbsp;'.$history_of_drug
.'<br>ประวัติการเจ็บป่วยในครอบครัว '.
$pmh2_1.'&nbsp;ปฏิเสธ&nbsp;'.$pmh2_2.'&nbsp;มีระบุ ระบุ&nbsp;'.$pmh2
.'<br><B>สัญญาณชีพแรกรับ</B>&emsp;BT&emsp;'.round(($row['bt']),2)
.'&emsp;°C&emsp;PR&emsp;'.round(($row['pr']),2)
.'&emsp;/min&emsp;RR&emsp;'.round(($row['rr']),2).'&emsp;/min'
.'&emsp;BP&emsp;'.round(($row['bps']),2).' / '.round(($row['bpd']),2).'&emsp;mmHg'



.'<br><B>สภาพร่างกายผู้ป่วยแรกรับ</B>'
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ระดับความรู้สึกตัว&nbsp;'.
$level_of_con_1.'&nbsp;รู้สึกตัวดี&nbsp;'.$level_of_con_2.'&nbsp;สับสน&nbsp;'.$level_of_con_3.'&nbsp;ซึม&nbsp;'.$level_of_con_4.'&nbsp;ไม่รู้สึกตัว&nbsp;'
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;การหายใจ&nbsp;'.
$breathing_1.'&nbsp;ปกติ&nbsp;'.$breathing_2.'&nbsp;หายใจหอบ&nbsp;'.$breathing_3.'&nbsp;หายใจลำบาก&nbsp;'.$breathing_4.'&nbsp;ไม่หายใจ&nbsp;'
.$breathing_5.'&nbsp;อื่นๆ&nbsp;'.$breathing
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;การไหลเวียนโลหิต สีผิว&nbsp;'.
$blood_circulation_1.'&nbsp;ปกติ&nbsp;'.$blood_circulation_2.'&nbsp;ซีด&nbsp;'.$blood_circulation_3.'&nbsp;ปลายมือปลายเท้าเขียว&nbsp;'.$blood_circulation_4.'&nbsp;รอบปากเขียว&nbsp;'.$blood_circulation_5.'&nbsp;เขียวทั่วตัว&nbsp;'
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;อาการบวม&nbsp;'.$swelling_1.'&nbsp;ไม่มี&nbsp;'
.$swelling_2.'&nbsp;บวมบริวณ&nbsp;'.$swelling
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ผิวหนัง&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
$skin_1.'&nbsp;ปกติ&nbsp;'.$skiny_2.'&nbsp;หนังแตก&nbsp;'.$skin_3.'&nbsp;เขียวช้ำ&nbsp;'.$skin_4.'&nbsp;ผื่นแดง&nbsp;'
.$skin_5.'&nbsp;ผื่นคัน&nbsp;'.$skin_6.'&nbsp;เหลือง&nbsp;'
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;การติดต่อสื่อสาร หู&nbsp;'.
$communication_ears_1.'&nbsp;ได้ยินชัดเจน&nbsp;'.$communication_ears_2.'&nbsp;ได้ยินไม่ชัดเจน : ใช้อุปกรณ์ช่วยฟัง&nbsp;'.$hearing_aid_1.'&nbsp;มี&nbsp;'.$hearing_aid_2.'&nbsp;ไม่มี&nbsp;'
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ตา&nbsp;'.
$communication_eyes_1.'&nbsp;เห็นชัดเจน&nbsp;'.$communication_eyes_2.'&nbsp;เห็นไม่ชัดเจน : สวมแว่นตา&nbsp;'.$glasses_1.'&nbsp;สวม&nbsp;'.$glasses_2.'&nbsp;ไม่สวม&nbsp;'
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;การพูด&nbsp;'.
$communication_speak_1.'&nbsp;ชัดเจน&nbsp;'.$communication_speak_2.'&nbsp;พูดติดอ่าง&nbsp;'.$communication_speak_3.'&nbsp;เป็นใบ้&nbsp;'.$communication_speak_4.'&nbsp;อื่นๆ&nbsp;'.$communication_speak
.'<br><b>สภาพจิตใจแรกรับ (การแสดงออกทางพฤติกรรม, การแสดงออกทางอารมณ์, สิ่งที่กังวล)</b>&nbsp;'.nl2br(htmlspecialchars($row['state_of_mind']))
.'<br><B>อาการแรกรับ</B>&nbsp;'.nl2br(htmlspecialchars($row['first_symptoms']))
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
            <td  colspan="3" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top; text-align: center;">
            <label>Attending physician : <br><br>'.htmlspecialchars($row['doctor_name']).'</label>
            </td>
        </tr>
    </table>
    <footter> <h2 style="text-align:right;font-size:8pt;">ประกาศใช้ 8 มีนาคม 2562 งานเอกสารคุณภาพ ศูนย์คุณภาพ</h2> </footer>
';
$mpdf->WriteHTML($head);
$mpdf->Output();
