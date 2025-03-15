<?php
require_once '../mains/datethai.php';
require_once '../include/Session.php';


$login = empty($_REQUEST['loginname']) ? null : $_REQUEST['loginname'];
$loginname = $_SESSION['loginname'];
$values = ['loginname' => $loginname];

//หากพบว่าไม่ตรงกันให้ ทำลาย session เดิมทิ้งไป
if (!$loginname) {
  session_start();
  session_destroy();
}

Session::checkLoginSessionAndShowMessage(); //เช็ค session

if (!(
  Session::checkPermission('DOCUMENT', 'PRINT')
)) {
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
  'report' => 'LR-REPORT2-PDF',
  // 'action'=>'PRINT',
  'an' => $an,
), JSON_UNESCAPED_UNICODE));


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
$sql = "SELECT *
        FROM `prs_lr_report2`
        WHERE an = :an";
$stmt = $conn->prepare($sql);
$stmt->execute($query_parameters);
$row  = $stmt->fetch();

//echo $row['id'];

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


$rxDate = $row['rxdate']; //วันที่ Discharge
$rxdate = date($rxDate);
$rxTime = $row['rxtime']; //เวลาที่ Discharge
$rxtime = date($rxTime);
$strDate = ($rxdate . "  " . $rxtime);
// $dchtime  = date('H:i', strtotime($origTime));

$depart_1 = '( )';
if ($row['depart'] == 'OPD') {
  $depart_1 = '(' . $image_check . ')';
}

$depart_2 = '( )';
if ($row['depart'] == 'ER') {
  $depart_2 = '(' . $image_check . ')';
}

$depart_3 = '( )';
if ($row['depart'] != 'OPD' && $row['depart'] != 'ER' && $row['depart'] != null) {
  $depart_3 = '(' . $image_check . ')';
  $depart  =  htmlspecialchars($row['depart']);
}


$hospital_by_1 = '( )';
if ($row['hospital_by'] == 'เดินมา') {
  $hospital_by_1 = '(' . $image_check . ')';
}

$hospital_by_2 = '( )';
if ($row['hospital_by'] == 'รถนั่ง') {
  $hospital_by_2 = '(' . $image_check . ')';
}

$hospital_by_3 = '( )';
if ($row['hospital_by'] == 'รถนอน') {
  $hospital_by_3 = '(' . $image_check . ')';
}

$hospital_by_4 = '( )';
if ($row['hospital_by'] != 'เดินมา' && $row['hospital_by'] != 'รถนั่ง' && $row['hospital_by'] != 'รถนอน' && $row['hospital_by'] != null) {
  $hospital_by_4 = '(' . $image_check . ')';
  $hospital_by  =  htmlspecialchars($row['hospital_by']);
}

//โรคประจำคัว
$c_chronic_1 = '( )';
if ($row['c_chronic'] == 'ปฏิเสธ') {
  $c_chronic_1 = '(' . $image_check . ')';
}

$c_chronic_2 = '( )';
if ($row['c_chronic'] != 'ปฏิเสธ' && $row['c_chronic'] != null) {
  $c_chronic_2 = '(' . $image_check . ')';
  $c_chronic  =  htmlspecialchars($row['c_chronic']);
}

//การรัคษาในโรงพยาบาล
$hos_history_1 = '( )';
if ($row['hos_history'] == 'ปฏิเสธ') {
  $hos_history_1 = '(' . $image_check . ')';
}

$hos_history_2 = '( )';
if ($row['hos_history'] != 'ปฏิเสธ' && $row['hos_history'] != null) {
  $hos_history_2 = '(' . $image_check . ')';
  $hos_history =  htmlspecialchars($row['hos_history']);
}

//การรัคษาในโรงพยาบาล
$hos_history_1 = '( )';
if ($row['hos_history'] == 'ปฏิเสธ') {
  $hos_history_1 = '(' . $image_check . ')';
}

$hos_history_2 = '( )';
if ($row['hos_history'] != 'ปฏิเสธ' && $row['hos_history'] != null) {
  $hos_history_2 = '(' . $image_check . ')';
  $hos_history =  htmlspecialchars($row['hos_history']);
}







//-------------------------Doctor admission note
$sql_ipt = "select patient.sex,patient.hn,patient.pname,patient.fname,patient.lname,patient.drugallergy,
            (select GROUP_CONCAT(concat(opd_allergy.agent,'=',if(opd_allergy.symptom is null,',',opd_allergy.symptom)/*,' [',if(note is null,',',note),']'*/)) as name
                from " . DbConstant::HOSXP_DBNAME . ".opd_allergy
                where opd_allergy.hn = ipt.hn /*and (opd_allergy.no_alert<>'Y' or opd_allergy.no_alert is null)*/
                order by display_order) as allergy_symptom_string,
            an_stat.age_y,an_stat.age_m,an_stat.age_d,
            ipt.regdate,ipt.regtime,
            ipt.ward,ward.name,
            ipt.pttype, pttype.`name` as pttype_name,
            iptadm.bedno, (select vs.bw from ipd_vs_vital_sign vs where vs.an = ipt.an and vs.bw is not null and trim(vs.bw) <> '' order by vs_datetime desc limit 1) as latest_bw
            , (select vs.height from ipd_vs_vital_sign vs where vs.an = ipt.an and vs.bw is not null and trim(vs.bw) <> '' order by vs_datetime desc limit 1) as latest_height
            from " . DbConstant::HOSXP_DBNAME . ".ipt
            left outer join " . DbConstant::HOSXP_DBNAME . ".an_stat on an_stat.an=ipt.an
            left outer join " . DbConstant::HOSXP_DBNAME . ".patient on patient.hn=ipt.hn
            left outer join " . DbConstant::HOSXP_DBNAME . ".ward on ward.ward=ipt.ward
            LEFT OUTER JOIN " . DbConstant::HOSXP_DBNAME . ".pttype ON pttype.pttype = ipt.pttype
            LEFT OUTER JOIN " . DbConstant::HOSXP_DBNAME . ".iptadm ON iptadm.an = ipt.an
            WHERE ipt.an=:an";
$stmt_ipt = $conn->prepare($sql_ipt);
$stmt_ipt->execute(['an' => $an]);
$row_ipt = $stmt_ipt->fetch();
$regdatetime = $row_ipt['regdate'] . ' ' . $row_ipt['regtime']; //ใช้ในการดึงข้อมูล ประวัติการผ่าตัด



$receive_date        =  $row['receive_date'];
$receive_time        =  $row['receive_time'];
$intime        =  $row['intime'];


//โรคประจำคัว
$c_chronic_1 = '( )';
if ($row['c_chronic'] == 'ปฏิเสธ') {
  $c_chronic_1 = '(' . $image_check . ')';
}

$c_chronic_2 = '( )';
if ($row['c_chronic'] != 'ปฏิเสธ' && $row['c_chronic'] != null) {
  $c_chronic_2 = '(' . $image_check . ')';
  $c_chronic  =  htmlspecialchars($row['c_chronic']);
}

//การรัคษาในโรงพยาบาล
$hos_history_1 = '( )';
if ($row['hos_history'] == 'ปฏิเสธ') {
  $hos_history_1 = '(' . $image_check . ')';
}

$hos_history_2 = '( )';
if ($row['hos_history'] != 'ปฏิเสธ' && $row['hos_history'] != null) {
  $hos_history_2 = '(' . $image_check . ')';
  $hos_history =  htmlspecialchars($row['hos_history']);
}

//ประวัติการผ่าตัด
$h_sergery_1 = '( )';
if ($row['h_sergery'] == 'ปฏิเสธ') {
  $h_sergery_1 = '(' . $image_check . ')';
}

$h_sergery_2 = '( )';
if ($row['h_sergery'] != 'ปฏิเสธ' && $row['h_sergery'] != null) {
  $h_sergery_2 = '(' . $image_check . ')';
  $h_sergery =  htmlspecialchars($row['h_sergery']);
}

//ประวัติกแพ้ยา
$h_allergy_1 = '( )';
if ($row['h_allergy'] == 'ปฏิเสธ') {
  $h_allergy_1 = '(' . $image_check . ')';
}

$h_allergy_2 = '( )';
if ($row['h_allergy'] != 'ปฏิเสธ' && $row['h_allergy'] != null) {
  $h_allergy_2 = '(' . $image_check . ')';
  $h_allergy =  htmlspecialchars($row['h_allergy']);
}



//ประวัติกแพ้ยา
$history_of_drug_1 = '( )';
if ($row['history_of_drug'] == 'ปฏิเสธ') {
  $history_of_drug_1 = '(' . $image_check . ')';
}

$history_of_drug_2 = '( )';
if ($row['history_of_drug'] != 'ปฏิเสธ' && $row['history_of_drug'] != null) {
  $history_of_drug_2 = '(' . $image_check . ')';
  $history_of_drug =  htmlspecialchars($row['history_of_drug']);
}

//ประวัติกแพ้ยา
$vaccine_history_1 = '( )';
if ($row['vaccine_history'] == 'ครบตามเกณฑ์') {
  $vaccine_history_1 = '(' . $image_check . ')';
}

$vaccine_history_2 = '( )';
if ($row['vaccine_history'] != 'ครบตามเกณฑ์' && $row['vaccine_history'] != null) {
  $vaccine_history_2 = '(' . $image_check . ')';
  $vaccine_history =  htmlspecialchars($row['vaccine_history']);
}

//ประวัติกแพ้ยา
$child_devilopment_1 = '( )';
if ($row['child_devilopment'] == 'สมวัย') {
  $child_devilopment_1 = '(' . $image_check . ')';
}

$child_devilopment_2 = '( )';
if ($row['child_devilopment'] != 'สมวัย' && $row['child_devilopment'] != null) {
  $child_devilopment_2 = '(' . $image_check . ')';
  $child_devilopment =  htmlspecialchars($row['child_devilopment']);
}

//ประวัติกแพ้ยา
$pmh2_1 = '( )';
if ($row['pmh2'] == 'ปฏิเสธ') {
  $pmh2_1 = '(' . $image_check . ')';
}

$pmh2_2 = '( )';
if ($row['pmh2'] != 'ปฏิเสธ' && $row['pmh2'] != null) {
  $pmh2_2 = '(' . $image_check . ')';
  $pmh2 =  htmlspecialchars($row['pmh2']);
}


//ระดับความรู้สึกตัว
$level_of_con_1 = '( )';
if ($row['level_of_con'] == 'รู้สึกตัวดี') {
  $level_of_con_1 = '(' . $image_check . ')';
}

$level_of_con_2 = '( )';
if ($row['level_of_con'] == 'สับสน') {
  $level_of_con_2 = '(' . $image_check . ')';
}

$level_of_con_3 = '( )';
if ($row['level_of_con'] == 'ซึม') {
  $level_of_con_3 = '(' . $image_check . ')';
}

$level_of_con_4 = '( )';
if ($row['level_of_con'] == 'ไม่รู้สึกตัว') {
  $level_of_con_4 = '(' . $image_check . ')';
}

$breathing_1 = '( )';
if ($row['breathing'] == 'ปกติ') {
  $breathing_1 = '(' . $image_check . ')';
}
$breathing_2 = '( )';
if ($row['breathing'] == 'หายใจหอบ') {
  $breathing_2 = '(' . $image_check . ')';
}

$breathing_3 = '( )';
if ($row['breathing'] == 'หายใจลำบาก') {
  $breathing_3 = '(' . $image_check . ')';
}

$breathing_4 = '( )';
if ($row['breathing'] == 'ไม่หายใจ') {
  $breathing_4 = '(' . $image_check . ')';
}

$breathing_5 = '( )';
if ($row['breathing'] != 'ปกติ' && $row['breathing'] != 'หายใจหอบ' && $row['breathing'] != 'หายใจลำบาก' && $row['breathing'] != 'ไม่หายใจ' && $row['breathing'] != null) {
  $breathing_5 = '(' . $image_check . ')';
  $breathing  =  htmlspecialchars($row['breathing']);
}

$blood_circulation_1 = '( )';
if ($row['blood_circulation'] == 'ปกติ') {
  $blood_circulation_1 = '(' . $image_check . ')';
}

$blood_circulation_2 = '( )';
if ($row['blood_circulation'] == 'ซีด') {
  $blood_circulation_2 = '(' . $image_check . ')';
}

$blood_circulation_3 = '( )';
if ($row['blood_circulation'] == 'ปลายมือปลายเท้าเขียว') {
  $blood_circulation_3 = '(' . $image_check . ')';
}

$blood_circulation_4 = '( )';
if ($row['blood_circulation'] == 'รอบปากเขียว') {
  $blood_circulation_4 = '(' . $image_check . ')';
}

$blood_circulation_5 = '( )';
if ($row['blood_circulation'] == 'เขียวทั่วตัว') {
  $blood_circulation_5 = '(' . $image_check . ')';
}

$swelling_1 = '( )';
if ($row['swelling'] == 'ไม่มี') {
  $swelling_1 = '(' . $image_check . ')';
}

$swelling_2 = '( )';
if ($row['swelling'] != 'ไม่มี' && $row['swelling'] != null) {
  $swelling_2 = '(' . $image_check . ')';
  $swelling =  htmlspecialchars($row['swelling']);
}

//ผิวหนัง
$skin_1 = '( )';
if ($row['skin'] == 'ปกติ') {
  $skin_1 = '(' . $image_check . ')';
}

$skin_2 = '( )';
if ($row['skin'] == 'หนังแตก') {
  $skin_2 = '(' . $image_check . ')';
}

$skin_3 = '( )';
if ($row['skin'] == 'เขียวช้ำ') {
  $skin_3 = '(' . $image_check . ')';
}

$skin_4 = '( )';
if ($row['skin'] == 'ผื่นแดง') {
  $skin_4 = '(' . $image_check . ')';
}

$skin_5 = '( )';
if ($row['skin'] == 'ผื่นคัน') {
  $skin_5 = '(' . $image_check . ')';
}

$skin_6 = '( )';
if ($row['skin'] == 'เหลือง') {
  $skin_6 = '(' . $image_check . ')';
}

$communication_ears_1 = '( )';
if ($row['communication_ears'] == 'ได้ยินชัดเจน') {
  $communication_ears_1 = '(' . $image_check . ')';
}

$communication_ears_2 = '( )';
if ($row['communication_ears'] == 'ได้ยินไม่ชัดเจน') {
  $communication_ears_2 = '(' . $image_check . ')';
}

$hearing_aid_1 = '( )';
if ($row['hearing_aid'] == 'มี') {
  $hearing_aid_1 = '(' . $image_check . ')';
}

$hearing_aid_2 = '( )';
if ($row['hearing_aid'] == 'ไม่มี') {
  $hearing_aid_2 = '(' . $image_check . ')';
}

$communication_eyes_1 = '( )';
if ($row['communication_eyes'] == 'เห็นชัดเจน') {
  $communication_eyes_1 = '(' . $image_check . ')';
}

$communication_eyes_2 = '( )';
if ($row['communication_eyes'] == 'เห็นไม่ชัดเจน') {
  $communication_eyes_2 = '(' . $image_check . ')';
}

$glasses_1 = '( )';
if ($row['glasses'] == 'สวม') {
  $glasses_1 = '(' . $image_check . ')';
}

$glasses_2 = '( )';
if ($row['glasses'] == 'ไม่สวม') {
  $glasses_2 = '(' . $image_check . ')';
}


$communication_speak_1 = '( )';
if ($row['communication_speak'] == 'ชัดเจน') {
  $communication_speak_1 = '(' . $image_check . ')';
}
$communication_speak_2 = '( )';
if ($row['communication_speak'] == 'พูดติดอ่าง') {
  $communication_speak_2 = '(' . $image_check . ')';
}

$communication_speak_3 = '( )';
if ($row['communication_speak'] == 'เป็นใบ้') {
  $communication_speak_3 = '(' . $image_check . ')';
}


$communication_speak_4 = '( )';
if ($row['communication_speak'] != 'ชัดเจน' && $row['communication_speak'] != 'พูดติดอ่าง' && $row['communication_speak'] != 'เป็นใบ้'  && $row['communication_speak'] != null) {
  $communication_speak_4 = '(' . $image_check . ')';
  $communication_speak  =  htmlspecialchars($row['communication_speak']);
}

$education = '';
if ($row['education'] == '1') {
  $education = 'ก่อนประถมศึกษา';
}
if ($row['education'] == '2') {
  $education = 'ประถมศึกษา';
}
if ($row['education'] == '3') {
  $education = 'มัธยมศึกษาตอนต้น';
}
if ($row['education'] == '4') {
  $education = 'มัธยมศึกษาตอนปลาย หรือ ปวช.';
}
if ($row['education'] == '5') {
  $education = 'อนุปริญญา';
}
if ($row['education'] == '6') {
  $education = 'ระดับปริญญาตรี';
}
if ($row['education'] == '7') {
  $education = 'ปริญญาโท';
}
if ($row['education'] == '8') {
  $education = 'ปริญญาเอก';
}
if ($row['education'] == '9') {
  $education = 'ไม่ทราบ';
}
if ($row['education'] == '99') {
  $education = 'ไม่ได้รับการศึกษา';
}

$income_enough_1 = '( )';
if ($row['income_enough'] == '1') {
  $income_enough_1 = '(' . $image_check . ')';
}

$income_enough_2 = '( )';
if ($row['income_enough'] == '2') {
  $income_enough_2 = '(' . $image_check . ')';
}

$leukorrhea_history_1 = '( )';
if ($row['leukorrhea_history'] == 'ปฏิเสธ') {
  $leukorrhea_history_1 = '(' . $image_check . ')';
}

$leukorrhea_history_2 = '( )';
if ($row['leukorrhea_history'] != 'ปฏิเสธ' && $row['leukorrhea_history'] != null) {
  $leukorrhea_history_2 = '(' . $image_check . ')';
  $leukorrhea_history  =  htmlspecialchars($row['leukorrhea_history']);
}

$behaviors_risk_sexually_1 = '( )';
if ($row['behaviors_risk_sexually'] == '1') {
  $behaviors_risk_sexually_1 = '(' . $image_check . ')';
}

$behaviors_risk_sexually_2 = '( )';
if ($row['behaviors_risk_sexually'] == '2') {
  $behaviors_risk_sexually_2 = '(' . $image_check . ')';
}

$behaviors_risk_sexually_3 = '( )';
if ($row['behaviors_risk_sexually'] == '3') {
  $behaviors_risk_sexually_3 = '(' . $image_check . ')';
}

$behaviors_risk_sexually_4 = '( )';
if ($row['behaviors_risk_sexually'] == '4') {
  $behaviors_risk_sexually_4 = '(' . $image_check . ')';
}


$behaviors_risk_sexually_5 = '( )';
if ($row['behaviors_risk_sexually'] == '5') {
  $behaviors_risk_sexually_5 = '(' . $image_check . ')';
}



$id = '15'; //Link menu
$check_    = ReportQueryUtils::getProduction($id);

$check_report = '( )';
if ($check_  == '1') 
{$check_report = '&nbsp;<font color="red">รอปรับรายงาน</font>';
} else {
    $check_report = '';
}


/*  $labor_history_top =  '<label> <b>ครรภ์ที่</b></label>


      /*  $labor_history_top =  '<label> <b>ครรภ์ที่</b></label>
        &emsp;<label> <b>ว/ด/ป คลอด/แท้ง</b></label>
        &emsp;<label> <b>GA</b></label>
        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&ensp;<label> สถานพยาบาลที่รักษา</label>';
        */
$labor_history_top1 = '<style> 
        #example1 {
          box-sizing: content-box;  
          width: 300px;
          height: 100px;
          padding: 30px;  
          border: 10px solid blue;
        }
        
        #example2 {
          box-sizing: border-box;
          width: 20px;
          height: 10px;
          padding: 30px;  
          
        }
        #example3 {
            box-sizing: border-box;
            width: 80px;
            height: 10px;
            padding: 30px; 
            border-right:0.5px solid #000;
            
            padding:4px;
            vertical-align:text-top; 
            
          }
        </style>
        <label> <b>ครรภ์ที่ |</b></label>&nbsp;<label> <b>วดป&nbsp;&nbsp; <br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;คลอด/แท้ง</b></label>
        &nbsp;&nbsp;<label> <b>GA</b></label>
        &nbsp;&nbsp;<label> <b>วิธีคลอด/แท้ง</b></label>
        &nbsp;&nbsp;<label> <b>น้ำหนักทารก</b></label>
        &nbsp;&nbsp;<label> <b>เพศ</b></label>
        &nbsp;&nbsp;<label> <b>สถานที่คลอด</b></label>
        &nbsp;<label> <b>ภาวะแทรกซ้อน</b></label>
        &nbsp;<label> <b>ประวัติการคลอดติดไหล่</b></label>
        ';

$labor_history_top =  '<style> 
        #example1 {
          box-sizing: content-box;  
          width: 300px;
          height: 100px;
          padding: 30px;  
          border: 10px solid blue;
        }
        
        #example2 {
          box-sizing: border-box;
          width: 20px;
          height: 10px;
          padding: 30px;  
          
        }
        #example3 {
            box-sizing: border-box;
            width: 80px;
            height: 10px;
            padding: 30px; 
            border-right:0.5px solid #000;
            
            padding:4px;
            vertical-align:text-top; 
            
          }
        </style>

        <table style="border-collapse: collapse;font-size:8pt;margin-top:2px;">
   
        <tr style="border:1px solid #000;margin: 35px;"> /* ชื่อ-สกุล */
            <td style="box-sizing: border-box; width: 20px; border-right:0.5px solid #000;margin: 10px;padding:1px;vertical-align:text-top;">
            <label> <b>ครรภ์ที่</b></label>
            </td>
            <td style="box-sizing: border-box; width: 90px; border-right:0.5px solid #000;margin: 10px;padding:1px;vertical-align:text-top;" >
            <label> <b>วดป&nbsp;&nbsp; คลอด/แท้ง</b></label>
            </td>
            <td style="box-sizing: border-box; width: 20px; border-right:0.5px solid #000;margin: 10px;padding:1px;vertical-align:text-top;">
            <label> <b>GA</b></label>
            </td>
            <td style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">
            <label> <b>วิธีคลอด / แท้ง</b></label>
            </td>
            <td style="border-right:0.5px solid #000;margin: 20px;padding:4px;vertical-align:text-top;">
            <label> <b>น้ำหนักทารก</b></label>
            </td>
            <td style="border-right:0.5px solid #000;margin: 40px;padding:4px;vertical-align:text-top;">
            <label> <b>เพศ</b></label>
            </td>
            <td style="border-right:0.5px solid #000;margin: 40px;padding:4px;vertical-align:text-top;">
            <label> <b>&nbsp;&nbsp;&nbsp;&nbsp;สถานที่คลอด&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></label>
            </td>
            <td style="border-right:0.5px solid #000;margin: 40px;padding:4px;vertical-align:text-top;">
            <label> <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ภาวะแทรกซ้อน&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></label>
            </td>
            <td style="border-right:0.5px solid #000;margin: 40px;padding:4px;vertical-align:text-top;">
            <label> <b>ประวัติการคลอดติดไหล่/คลอดยาก</b></label>
            </td>
            

        </tr> 
        </table>   

';

$labor_history_pos = explode(" ", $row['labor_history']);
$labor_history =  "";
//$preg_num = "";
//$labor_date ="";
//$ga ="";
//$labor_by ="";
$y = 0;
for ($x = 1; $x < (count($labor_history_pos)) / 9; $x++) {
  $preg_num = htmlspecialchars($labor_history_pos[$y++]);
  $labor_date = htmlspecialchars($labor_history_pos[$y++]);
  $ga = htmlspecialchars($labor_history_pos[$y++]);
  $labor_by = htmlspecialchars($labor_history_pos[$y++]);
  $labor_weight = htmlspecialchars($labor_history_pos[$y++]);
  $sex = htmlspecialchars($labor_history_pos[$y++]);
  $location = htmlspecialchars($labor_history_pos[$y++]);
  $complications = htmlspecialchars($labor_history_pos[$y++]);
  $l_history = htmlspecialchars($labor_history_pos[$y++]);

  $labor_history .= '

   
    <table style="border-collapse: collapse;font-size:8pt;margin-top:2px;">
   
        <tr style="border:1px solid #000;margin: 35px;">
    <td style="box-sizing: border-box; width: 30px; border-right:0.5px solid #000;margin: 10px;padding:1px;vertical-align:text-top;">
            <label> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $preg_num . '&nbsp;&nbsp;&nbsp;&nbsp;</label>
            </td>
            <td style="box-sizing: border-box; width: 90px; border-right:0.5px solid #000;margin: 10px;padding:1px;vertical-align:text-top;" >
            <label> ' . $labor_date . '</label>
            </td>
            <td  style="box-sizing: border-box; width: 20px; border-right:0.5px solid #000;margin: 10px;padding:1px;vertical-align:text-top;" >
            <label> ' . $ga . '</label>
            </td>
            <td style="box-sizing: border-box; width: 84px; border-right:0.5px solid #000;margin: 10px;padding:1px;vertical-align:text-top;" >
            <label> ' . $labor_by . '</label>
            </td>
            <td style="box-sizing: border-box; width: 70px; border-right:0.5px solid #000;margin: 10px;padding:1px;vertical-align:text-top;">
            <label> ' . $labor_weight . '</label>
            </td>
            <td style="box-sizing: border-box; width: 28px; border-right:0.5px solid #000;margin: 10px;padding:1px;vertical-align:text-top;" >
            <label> ' . $sex . '</label>
            </td>
            <td style="box-sizing: border-box; width: 90px; border-right:0.5px solid #000;margin: 10px;padding:1px;vertical-align:text-top;" >
            <label> ' . $location . '</label>
            </td>
            <td style="box-sizing: border-box; width: 100px; border-right:0.5px solid #000;margin: 10px;padding:1px;vertical-align:text-top;" >
            <label> ' . $complications . '</label>
            </td>
            <td style="box-sizing: border-box; width: 180px; border-right:0.5px solid #000;margin: 10px;padding:1px;vertical-align:text-top;" >
            <label> ' . $l_history . '</label>
            </td>
          

            </tr>
   
    </table>';
  $labor_history11 .= '
    <br>
                        <label > ' . $preg_num . '</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label> ' . $labor_date . '</label>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <label> ' . $ga . '</label> 
                        &nbsp;&nbsp;&nbsp;&nbsp;
                        ' . wordwrap($labor_by, 5, "<br />\n") . '
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <label> ' . $labor_weight . '</label> 
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <label> ' . $sex . '</label>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <label> ' . $location . '</label>  
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <label> ' . $complications . '</label>  
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <label> ' . $history . '</label> 
                        ';

  $labor_history0 .= '
    <table  width="100%" style="border-collapse: collapse;font-size:8pt;margin-top:2px;">
    <tr style="border:1px solid #000;margin: 35px;"> 
        <td style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">
        <label>' . $preg_num . '</label>
        </td>
        <td style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">
        <label>' . $labor_date . '</label>
        </td>
        <td style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">
        <label>' . $labor_date . '</label>
        </td>
        <td style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">
        <label>' . $labor_date . '</label>
        </td>
        <td style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">
        <label>' . $labor_date . '</label>
        </td>
        <td style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">
        <label>' . $labor_date . '</label>
        </td>
        <td style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">
        <label>' . $labor_date . '</label>
        </td>
        <td style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">
        <label>' . $labor_date . '</label>
        </td>
        <td style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">
        <label>' . $ga . '</label>
        </td>
                 
            </tr>


    </table>';
}


$head4 =
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
        .page-break {
          page-break-before: always;
      }

    </style>
    <h2 class="page-break"  style="text-align:right;font-size:8pt;">&nbsp;</h2>
    
    <h2 style="text-align:center;font-size:11pt;">ใบบันทึกประวัติและประเมินสมรรถนะผู้ป่วยแรกรับ(เฉพาะผู้มาคลอด)&nbsp;<br>' . htmlspecialchars(DbConstant::HOSPITAL_NAME) .$check_report. '</h2>
   
    <div class="form-group row">
                                <label class="col-sm-12">ข้อมูลทั่วไป</label>
                            </div>
<div class="f15"> รับใหม่วันที่ <u>' . LongDateThai2($strDate) . '</u><b> เวลา </b><u>' . htmlspecialchars($rxtime) . '</u>&nbsp;น. กรณี Admit จากผู้ป่วยนอก ถึงห้องคลอดเวลา&nbsp;<u>' . htmlspecialchars($intime) . '</u>&nbsp;น.' . '<br>'
  . '<B>อาการสำคัญที่นำมาโรงพยาบาล</B>&nbsp;<u>' . nl2br(htmlspecialchars($row['cc']))
  . '</u><br><B>ประวัติการเจ็บป่วยปัจจุบัน</B>&nbsp;<u>' . nl2br(htmlspecialchars($row['current_illness']))
  . '</u><br><B>ประวัติเจ็บป่วยในอดีต</B>'
  . '<br>โรคประจำตัว ' .
  $c_chronic_1 . '&nbsp;ปฏิเสธ&nbsp;' . $c_chronic_2 . '&nbsp;มี ระบุ&nbsp;<u>' . $c_chronic
  . '</u><br>เคยรับการรักษาในโรงพยาบาล (ภายใน 1 ปี) ' .
  $hos_history_1 . '&nbsp;ปฏิเสธ&nbsp;' . $hos_history_2 . '&nbsp;เคย ระบุ&nbsp;<u>' . $hos_history
  . '</u><br>ประวัติการผ่าตัด ' .
  $h_sergery_1 . '&nbsp;ปฏิเสธ&nbsp;' . $h_sergery_2 . '&nbsp;เคย ระบุ&nbsp;<u>' . $h_sergery
  . '</u><br>ประวัติการแพ้ยาหรือการแพ้อื่นๆ ' .
  $h_allergy_1 . '&nbsp;ปฏิเสธ&nbsp;' . $h_allergy_2 . '&nbsp;เคย ระบุ&nbsp;<u>' . $h_allergy
  . '</u><br>ประวัติการใช้ยาและผลิตภัณฑ์สุขภาพ ' .
  $history_of_drug_1 . '&nbsp;ปฏิเสธ&nbsp;' . $history_of_drug_2 . '&nbsp;มีระบุ ระบุ&nbsp;<u>' . $history_of_drug
  . '</u><br>ประวัติการเจ็บป่วยในครอบครัว ' .
  $pmh2_1 . '&nbsp;ปฏิเสธ&nbsp;' . $pmh2_2 . '&nbsp;มีระบุ ระบุ&nbsp;<u>' . $pmh2
  . '</u><br>ประวัติการตั้งครรภ์&nbsp; G&nbsp;<u>' . nl2br(htmlspecialchars($row['g'])) .
  '</u>&nbsp; P&nbsp;
                                <u>' . nl2br(htmlspecialchars($row['p'])) .
  '</u>&nbsp;GA <u>' . nl2br(htmlspecialchars($row['l_ga'])) . '   
                                </u>&nbsp;wks by&nbsp;
                                <u>' . nl2br(htmlspecialchars($row['l_ga_by'])) . ' 
                                </u>&nbsp;ฝากครรภ์ครั้งแรก&nbsp;<u>' . nl2br(htmlspecialchars($row['prenatal_wks'])) .
  '</u>&nbsp;wks&nbsp;ฝากครรภ์&nbsp;<u>' . nl2br(htmlspecialchars($row['prenatral_count'])) .
  '</u>&nbsp;ครั้ง&nbsp;'

  . '<br>ค 8&nbsp;<u>' . nl2br(htmlspecialchars($row['k8'])) .
  '</u>&nbsp;( ขาด&nbsp;
                                <u>' . nl2br(htmlspecialchars($row['k8_less'])) .
  '</u> )&nbsp;ที่ <u>' . nl2br(htmlspecialchars($row['at_'])) . '   
                                </u>&nbsp;dT&nbsp;
                                <u>' . nl2br(htmlspecialchars($row['dt'])) . ' 
                                </u>&nbsp;เข็ม'
  . '<br>Lab ANC ครั้งที่1 Anti HIV&nbsp;<u>' . nl2br(htmlspecialchars($row['anc_lab_hiv1'])) .
  '</u>&nbsp;RPR&nbsp;
                                                              <u>' . nl2br(htmlspecialchars($row['anc_lab_rpr1'])) .
  '</u>&nbsp;HBsAg&nbsp;<u>' . nl2br(htmlspecialchars($row['anc_lab_hbsag1'])) . '   
                                                              </u>&nbsp;Hct&nbsp;
                                                              <u>' . nl2br(htmlspecialchars($row['anc_lab_hct1'])) . '</u> % ' .
  '&nbsp;Hb&nbsp;<u>' . nl2br(htmlspecialchars($row['anc_lab_hb1'])) .
  '</u> Bl.gr&nbsp;<u>' . nl2br(htmlspecialchars($row['anc_lab_blgr'])) .
  '</u> Rh&nbsp;<u>' . nl2br(htmlspecialchars($row['anc_lab_blgr_rh'])) .
  '</u> DCIP&nbsp;<u>' . nl2br(htmlspecialchars($row['anc_lab_dcip1'])) .
  '</u> MCV&nbsp;<u>' . nl2br(htmlspecialchars($row['anc_lab_mvc1'])) .
  '</u> Hb typing&nbsp;<u>' . nl2br(htmlspecialchars($row['anc_lab_hb_typing1'])) . '</ul>'

  . 'Lab ANC ครั้งที่2 Anti HIV&nbsp;<u>' . nl2br(htmlspecialchars($row['anc_lab_hiv2'])) .
  '</u>&nbsp;RPR&nbsp;
                                                                                           <u>' . nl2br(htmlspecialchars($row['anc_lab_rpr2'])) .
  '</u>&nbsp;Hct&nbsp;
                                                                                           <u>' . nl2br(htmlspecialchars($row['anc_lab_hct2'])) . '</u> % ' .
  '&nbsp;Hb&nbsp;<u>' . nl2br(htmlspecialchars($row['anc_lab_hb2'])) .
  '</u> Lab สามี Anti HIV&nbsp;<u>' . nl2br(htmlspecialchars($row['hus_lab_hiv'])) .
  '</u> RPR&nbsp;<u>' . nl2br(htmlspecialchars($row['lab_rpr2'])) .
  '</u> DCIP&nbsp;<u>' . nl2br(htmlspecialchars($row['lab_dcip2'])) .
  '</u> Hb typing&nbsp;<u>' . nl2br(htmlspecialchars($row['lab_hb_typing2'])) . '</ul>'
  . 'โรงเรียนพ่อแม่&nbsp;<u>' . nl2br(htmlspecialchars($row['ma_fa_school'])) .
  '</u>&nbsp;ครั้ง Quad test&nbsp;<u>' . nl2br(htmlspecialchars($row['quad_test'])) .
  '</u>&nbsp;Lab อื่นๆ&nbsp;<u>' . nl2br(htmlspecialchars($row['other_lab'])) . '</u>'

  . '<br><B>ประวัติการคลอด</B><br>'
  . $labor_history_top . '
 ' . $labor_history
  . '<B>สัญญาณชีพ</B>&emsp;BT&emsp;<u>' . round(($row['bt']), 2)
  . '</u>&emsp;°C&emsp;PR&emsp;<u>' . round(($row['pr']), 2)
  . '</u>&emsp;bpm,&emsp;RR&emsp;<u>' . round(($row['rr']), 2)
  . '</u>&emsp;bpm,&emsp;BP&emsp;<u>' . round(($row['bps']), 2) . ' / ' . round(($row['bpd']), 2) . '</u>&emsp;mmHg'

  . '<br>นอนวันละ&nbsp;<u>' . nl2br(htmlspecialchars($row['sleep_hour'])) .
  '</u>&nbsp;ชม. ปวดบริเวณ&nbsp;
                                                              <u>' . nl2br(htmlspecialchars($row['pain_area'])) .
  '</u>&nbsp;Pain score&nbsp;<u>' . nl2br(htmlspecialchars($row['pain_score'])) . '   
                                                              </u>&nbsp;/10 คะแนน&nbsp;' 

  . '<br>ระดับการศึกษา&nbsp;<u>' . $education .
  '</u>&nbsp;อาชีพ&nbsp;
                                                              <u>' . nl2br(htmlspecialchars($row['ocupation'])) .
  '</u>&nbsp;รายได้&nbsp;<u>' . nl2br(htmlspecialchars($row['income'])) . '   
                                                              </u>&nbsp;บาท/เดือน&nbsp;' 
                                                              .$income_enough_1.'&nbsp;เพียงพอ&nbsp;'.$income_enough_2.'&nbsp;ไม่เพียงพอ&nbsp;'
  . '<br>ผู้ดูแล&nbsp;<u>' . nl2br(htmlspecialchars($row['caretaker'])) .
  '</u>&nbsp;อาชีพ&nbsp;
                                                              <u>' . nl2br(htmlspecialchars($row['caretaker_ocupation'])) .
  '</u>&nbsp;รายได้&nbsp;<u>' . nl2br(htmlspecialchars($row['caretaker_income'])) . '   
                                                              </u>&nbsp;บาท/เดือน&nbsp;' 

  . '<br><B>อาการแรกรับ</B>&nbsp;<u>' . nl2br(htmlspecialchars($row['first_symptoms']))
  . '</ul>'
  .'ข้อมูลเพิ่มเติมตามแบบแผนสุขภาพ ( นอกเหนือจากในระบบ KPHIS )&nbsp;'
  .'<br>ภาวะโภชนาการและเมตาบอลิซึม&nbsp;'
  . '<br>BW&emsp;<u>' . round(($row['bw']), 2)
  . '</u>&emsp;kgs.&emsp;Hight&emsp;<u>' . round(($row['hight']), 2)
  . '</u>&emsp;cms.,&emsp;BW ก่อนการตั้งครรภ์&emsp;<u>' . round(($row['bw_befor_prenatal']), 2)
  . '</u>&emsp;kgs.,&emsp;BMI ก่อนการตั้งครรภ์&emsp;<u>' . round(($row['bmi_befor_prenatal']), 2) . '</u>&emsp;kg/m<sup>2</sup>'
  . '<br>ประวัติตกขาว คันช่องคลอด&emsp;' .
  $leukorrhea_history_1 . '&nbsp;ปฏิเสธ&nbsp;' . $leukorrhea_history_2 . '&nbsp;มี ระบุ&nbsp;<u>' . $leukorrhea_history
  .'</u>'
  .'<br>ประวัติพฤติกรรมเสี่ยงต่อการติดเชื้อโรคติดต่อ (เฉพาะอายุ 14-49ปี)&nbsp;<br>'
  .$behaviors_risk_sexually_1.'&nbsp;คู่เพศสัมพันธ์เป็โรคติดต่อทางเพศสัมพันธ์&nbsp;'.$behaviors_risk_sexually_2.'&nbsp;มีเพศสัมพันธ์ชายกับชาย/หญิงให้บริการไม่ใช้ถุงยาง&nbsp;<br>'
  .$behaviors_risk_sexually_3.'&nbsp;มีเพศสัมพันธ์มากกว่า 1 คน&nbsp;'.$behaviors_risk_sexually_4.'&nbsp;มีเพศสัมพันธ์กับคนใหม่&nbsp;'.$behaviors_risk_sexually_5.'&nbsp;ไม่ใช้ถุงยางอนามัยหรือแตก รั่ว หลุด&nbsp;'
  .'</div>'

   .'<table id="bg-table" width="100%" style="border-collapse: collapse;font-size:8pt;margin-top:2px;">
   
        <tr style="border:1px solid #000;margin: 35px;"> /* ชื่อ-สกุล */
            <td  colspan="3" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">
            <label>HN : ' . htmlspecialchars($row_ipt['hn']) . ' | AN : ' . htmlspecialchars($an) . '</label>
            <label>ชื่อ - สกุล : ' . htmlspecialchars($row_ipt['pname'] . $row_ipt['fname'] . " " . $row_ipt['lname']) . ' | </label>
            <label>อายุ : ' . htmlspecialchars($row_ipt['age_y'] . " ปี " . $row_ipt['age_m'] . " เดือน " . $row_ipt['age_d'] . " วัน ") . ' | </label>
            <label>ตึก : ' . htmlspecialchars($row_ipt['name']) . ' | </label>
            <label>เตียง : ' . htmlspecialchars($row_ipt['bedno']) . ' | </label>
            <label>สิทธิ : (' . htmlspecialchars($row_ipt['pttype']) . ') ' . htmlspecialchars($row_ipt['pttype_name']) . '</label>
            </td>
            
        </tr>
    </table>
    <footter> <h2 style="text-align:right;font-size:8pt;">ประกาศใช้ 8 มีนาคม 2562 งานเอกสารคุณภาพ ศูนย์คุณภาพ</h2> </footer>
';

//$mpdf->WriteHTML($head);
//$mpdf->Output();
