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

$admission_note_id = $_REQUEST['admission_note_id'];
$an = empty($_REQUEST['an']) ? null : $_REQUEST['an'];
$hn = KphisQueryUtils::getHnByAn($an);
$query_parameters = ['an' => $an];

Session::insertSystemAccessLog(json_encode(array(
    'report'=>'IPD-DR-NEWBORN-ADMISSION-NOTE-PDF',
   // 'action'=>'PRINT',
    'an'=>$an,
),JSON_UNESCAPED_UNICODE));


//-------------------------Doctor admission note
$sql = "SELECT *,case 
when c_sex=1 then 'Male'
when c_sex=2 then 'Female'
end as sex_2
,case 
when c_born_type_in=1 then 'ในโรงพยาบาล'
when c_born_type_in=2 then 'นอกโรงพยาบาล'
end  as type_in
,case 
when c_labor_type=1 then 'Normal delivery'
when c_labor_type=2 then 'V/E'
when c_labor_type=3 then 'F/E'
when c_labor_type=4 then 'C/S'
when c_labor_type=5 then 'Breech assisting'
end  as c_labor_type2
        FROM `ipd_dr_admission_note`
        WHERE an = :an";
$stmt = $conn->prepare($sql);
$stmt->execute($query_parameters);
$row  = $stmt->fetch();

$sql_item = "SELECT dr_adm_item.admission_note_item_id,
            dr_adm_item.admission_note_doctor,
            doctor.`name` AS admission_note_doctorname,
            doctor.licenseno
            FROM " . DbConstant::KPHIS_DBNAME . ".ipd_dr_admission_note_item dr_adm_item
            LEFT OUTER JOIN " . DbConstant::HOSXP_DBNAME . ".doctor ON doctor.code = dr_adm_item.admission_note_doctor
            WHERE an=:an
            ORDER BY dr_adm_item.admission_note_item_id ASC";
$stmt_item = $conn->prepare($sql_item);
$stmt_item->execute(['an' => $an]);
$admission_note_count = 0;
$admission_note_doctorString = '';
$licenseno_String = '';
while ($row_item = $stmt_item->fetch()) {
    if ($row_item['licenseno'] != '' && $row_item['licenseno'] != null) {
        $licenseno_String = " (" . $row_item['licenseno'] . ")";
    }
    $admission_note_doctorString .= $row_item['admission_note_doctorname'] . $licenseno_String . "<br>";
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

//-------------------------ประวัติการผ่าตัด
$reg_parameters = ['hn' => $hn, 'regdatetime' => $regdatetime, 'hospital_name' => DbConstant::HOSPITAL_NAME];
$sql_ol = "SELECT CONCAT(ifnull(operation_list.enter_date,''),', ',ifnull(operation_list.operation_name,''),', ',ifnull(doctor.name,''),', ',:hospital_name) AS operation_list
                    FROM " . DbConstant::HOSXP_DBNAME . ".operation_list
                    left outer join " . DbConstant::HOSXP_DBNAME . ".doctor on doctor.code = operation_list.request_doctor
                    WHERE operation_list.hn= :hn
                    AND operation_list.status_id = 3
                    AND concat(operation_list.enter_date,' ',operation_list.enter_time) < :regdatetime
                    ORDER BY operation_list.enter_date,operation_list.enter_time";
$stmt_ol = $conn->prepare($sql_ol);
$stmt_ol->execute($reg_parameters);
$rows_ol  = $stmt_ol->fetchAll();
$operation_text = "";
foreach ($rows_ol as $row_ol) :
    $operation_text .= '<label>&nbsp;&nbsp;&ensp;&nbsp;&nbsp;&ensp;&nbsp;&nbsp;&ensp;&nbsp;&nbsp;&ensp;
                                &nbsp;&nbsp;&ensp;&nbsp;&nbsp;&ensp;&nbsp;&nbsp;&ensp;&nbsp;&nbsp;&ensp;'
        . htmlspecialchars($row_ol["operation_list"]) . '</label><br>';
endforeach;
//-------------------------ประวัติการผ่าตัด

//-------------------Vital Sign
$sql_vs =  "SELECT ipd_vs_vital_sign.sbp,ipd_vs_vital_sign.dbp,ipd_vs_vital_sign.bt,ipd_vs_vital_sign.pr,ipd_vs_vital_sign.rr,
            ipd_vs_vital_sign.eye,ipd_vs_vital_sign.verbal,ipd_vs_vital_sign.movement,ipd_vs_vital_sign.braden
            FROM " . DbConstant::KPHIS_DBNAME . ".ipd_vs_vital_sign
            WHERE ipd_vs_vital_sign.an=:an
            GROUP BY ipd_vs_vital_sign.vs_datetime ASC LIMIT 1";
$stmt_vs = $conn->prepare($sql_vs);
$stmt_vs->execute(['an' => $an]);
$row_vs  = $stmt_vs->fetch();
$NeuroSignGCS = 0;
if (isset($row_vs['eye'])) {
    if (is_numeric($row_vs['eye'])) {
        $NeuroSignGCS += $row_vs['eye'];
    }
}
if (isset($row_vs['verbal'])) {
    if (is_numeric($row_vs['verbal'])) {
        $NeuroSignGCS += $row_vs['verbal'];
    }
}
if (isset($row_vs['movement'])) {
    if (is_numeric($row_vs['movement'])) {
        $NeuroSignGCS += $row_vs['movement'];
    }
}
//-------------------Vital Sign

//-------------------ipd_nurse_addmission_note เรื่อง "ประจำเดือน","อาชีพ","สารเสพติด"
$sql_period =   "SELECT period, period_normal, period_disorders,period_lmp, period_menopause,
                occupation,
                no_risk,
                smoking, smoke_year, smoke_frequency, smoke_stopped,
                alcohol, alc_year, alc_frequency, alc_stopped,
                medication_used, med_name, med_year, med_frequency, med_stopped
                FROM " . DbConstant::KPHIS_DBNAME . ".ipd_nurse_admission_note
                WHERE an=:an";
$stmt_period = $conn->prepare($sql_period);
$stmt_period->execute(['an' => $an]);
$row_period  = $stmt_period->fetch();
//------------------ipd_nurse_addmission_note เรื่อง "ประจำเดือน","อาชีพ","สารเสพติด"

//$image_check = "<img src='picture/check-png.png'>";
$image_check = "<img src='../include/images/check-1.jpg' width='1.6%' class='check_img'>";
//$image_check = ' / ';



/* เข้ารับการรักษาโดย */
if ($row['take_medication_by'] == 'มาเอง') {
    $textbox_medication_3 = htmlspecialchars($row['take_medication_by']);
}
if ($row['take_medication_by'] == 'แพทย์นัด') {
    $textbox_medication_3 = htmlspecialchars($row['take_medication_by']);
}
if ($row['take_medication_by'] != 'มาเอง' && $row['take_medication_by'] != 'แพทย์นัด') {
    $textbox_medication_3 = htmlspecialchars($row['take_medication_by']);
}

/*  มาถึงหอผู้ป่วยโดย */
$checkbox_arrive_1 = '<input type="checkbox">';
if ($row['arrive_by'] == 'เดินมา') {
    $checkbox_arrive_1 = '<input type="checkbox" checked="checked">';
}
$checkbox_arrive_2 = '<input type="checkbox">';
if ($row['arrive_by'] == 'รถนั่ง') {
    $checkbox_arrive_2 = '<input type="checkbox" checked="checked">';
}
$checkbox_arrive_3 = '<input type="checkbox">';
if ($row['arrive_by'] == 'รถนอน') {
    $checkbox_arrive_3 = '<input type="checkbox" checked="checked">';
}
$checkbox_arrive_4 = '<input type="checkbox">';
if ($row['arrive_by'] == 'รถ Transfer') {
    $checkbox_arrive_4 = '<input type="checkbox" checked="checked">';
}
$checkbox_arrive_5 = '<input type="checkbox">';
if (
    $row['arrive_by'] != 'เดินมา'
    && $row['arrive_by'] != 'รถนั่ง'
    && $row['arrive_by'] != 'รถนอน'
    && $row['arrive_by'] != 'รถ Transfer'
) {
    $checkbox_arrive_5 = '<input type="checkbox" checked="checked">';
    $textbox_arrive_5 = htmlspecialchars($row['arrive_by']);
}

/*  นำส่งผู้ป่วยโดย */
$taken_by_text = "";
if ($row['taken_by_relative'] == 'Y') {
    $taken_by_text .= "ญาติ";
}
if ($row['taken_by_nurse']    == 'Y') {
    $taken_by_text .= " " . "พยาบาล";
}
if ($row['taken_by_crib']     == 'Y') {
    $taken_by_text .= " " . "พนักงานเปล";
}
if ($row['taken_by_etc']      == 'Y') {
    $taken_by_text .= " " . $row['taken_by'];
}

/* ผู้ให้ข้อมูล */
$textbox_informant = "";
if ($row['informant_patient'] == 'ผู้ป่วย') {
    $textbox_informant .= htmlspecialchars($row['informant_patient']);
}
if ($row['informant_relatives'] != null) {
    $textbox_informant .= htmlspecialchars($row['informant_relatives']);
}
if ($row['informant_deliverer'] == 'ผู้นำส่ง') {
    $textbox_informant .= htmlspecialchars($row['informant_deliverer']);
}
if ($row['informant_etc'] != null) {
    $textbox_informant .= htmlspecialchars($row['informant_etc']);
}

/* โรคประจำตัว */
$checkbox_disease_1 = '( )';
if ($row['disease'] == 'ไม่มี') {
    $checkbox_disease_1 = '(' . $image_check . ')';
    $disease_top =  "";
    $disease_detail =  "";
}
$checkbox_disease_2 = '( )';
if ($row['disease'] == 'มี') {
    $checkbox_disease_2 = '(' . $image_check . ')';
    $disease_top =  '&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&ensp;<label> โรค</label>
                    &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&ensp;
                    &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&ensp;<label> จำนวนปี</label>
                    &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&ensp;<label> สถานพยาบาลที่รักษา</label>';
    $disease_pos = explode(" ", $row['disease_detail']);
    $disease_detail =  "";
    $y = 0;
    for ($x = 1; $x < (count($disease_pos)) / 3; $x++) {
        $disease_name = htmlspecialchars($disease_pos[$y++]);
        $disease_year = htmlspecialchars($disease_pos[$y++]);
        $disease_hospital = htmlspecialchars($disease_pos[$y++]);
        $disease_detail .= '<br>&nbsp;&nbsp;
                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&ensp;
                        &emsp;&emsp;&emsp;&emsp;&emsp;&nbsp;&nbsp;
                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
                        <label> ' . $disease_name . '</label>
                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&ensp;
                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&ensp;
                        <label> ' . $disease_year . '</label>
                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&ensp;&emsp;
                        <label> ' . $disease_hospital . '</label>';
    }
}

/* อาการสําคัญ */
$text = htmlspecialchars($row['chief_complaints']);
$middle = strrpos(substr($text, 0, floor(460)), ' ') + 1;
$chief_complaints_string1 = substr($text, 0, $middle);  // "The Quick : Brown Fox "
$chief_complaints_string2 = substr($text, $middle);

/*  ประวัติการเจ็บป่วยในอดีต */

/* if   ($row_ipt['age_y'] < 1)  {$past_history_age = '<h4>&nbsp;ผู้ป่วยเด็กอายุ &lt;1 ปี</h4>';}
    else {$past_history_age = '<h4>&nbsp;ผู้ป่วยทั่วไป</h4>';$past_history_age_1 = 'display:none;';}
    if  ($row_ipt['age_y'] < 9)  {$past_history_age_9 = 'display:none;';}*/

/* if (($row_ipt['age_y'] <  15 && $row_ipt['age_y'] >= 1) ||
        ($row_ipt['age_y'] == 15 && $row_ipt['age_m'] == 0 && $row_ipt['age_d'] == 0))
        {$past_history_age_1_15 = 'display:none;';} */
$past_history_age_15 = '';
/* if (($row_ipt['age_y'] >  15 && $row_ipt['age_m'] >= 0 && $row_ipt['age_d'] >= 0)||
        ($row_ipt['age_y'] == 15 && $row_ipt['age_m'] >  0 && $row_ipt['age_d'] >  0))
        {$past_history_age_15 = 'display:none;';}*/

/* ประวัติแพ้ */

$checkbox_c_labor_normal = '( )';
if ($row['c_labor_normal'] == 'Y') {
    $checkbox_c_labor_normal = '(' . $image_check . ')';
}

$checkbox_hbv = '( )';
if ($row['c_hbv'] == 'Y') {
    $checkbox_hbv = '(' . $image_check . ')';
}

$checkbox_bcg = '( )';
if ($row['c_bcg'] == 'Y') {
    $checkbox_bcg = '(' . $image_check . ')';
}

$checkbox_inf_office = '( )';
if ($row['c_inform_officer'] == 'Y') {
    $checkbox_inf_office = '(' . $image_check . ')';
}

$checkbox_inf_mother = '( )';
if ($row['c_inform_mother'] == 'Y') {
    $checkbox_inf_mother = '(' . $image_check . ')';
}

$checkbox_inf_etc = '( )';
if ($row['c_inform_etc'] == 'Y') {
    $checkbox_inf_etc = '(' . $image_check . ')';
}



$checkbox_allergy_1 = '( )';
if ($row['allergy_history'] == 'ไม่มี') {
    $checkbox_allergy_1 = '(' . $image_check . ')';
    $allergy_drug_detail =  "";
    $allergy_food_detail =  "";
    $allergy_etc_detail  =  "";
    $allergy_top =  "";
}

$checkbox_allergy_2 = '( )';
if ($row['allergy_history'] == 'มี') {
    $checkbox_allergy_2 = '(' . $image_check . ')';
    $allergy_drug_pos = explode(" ", $row['allergy_drug_history']);
    $allergy_food_pos = explode(" ", $row['allergy_food_history']);
    $allergy_etc_pos  = explode(" ", $row['allergy_etc_history']);
    $allergy_drug_detail =  "";
    $allergy_food_detail =  "";
    $allergy_etc_detail  =  "";
    $allergy_top = '<br>&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&ensp;&ensp;
                            &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&ensp;&emsp;&ensp;
                            &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&ensp;&ensp;&ensp;
                            &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&ensp;&ensp;&ensp;
                            &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
                            <label>อาการที่แพ้</label>';
    $allergy_drug_count  = 0;
    $allergy_food_count  = 0;
    $allergy_etc_count   = 0;
    for ($x = 1; $x < (count($allergy_drug_pos)) / 2; $x++) {
        $allergy_drug_name = htmlspecialchars($allergy_drug_pos[$allergy_drug_count++]);
        $allergy_drug = htmlspecialchars($allergy_drug_pos[$allergy_drug_count++]);
        $allergy_drug_detail .= '<br>&nbsp;&nbsp;
                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&ensp;
                        &emsp;&emsp;&emsp;&emsp;&emsp;&nbsp;&nbsp;
                        &emsp;&emsp;<label>ยา</label>&emsp;&emsp;
                        <label> <font color="red">' . $allergy_drug_name . '</font></label>
                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
                        <label> <font color="red">' . $allergy_drug . '</font></label>';
    }
    for ($x = 1; $x < (count($allergy_food_pos)) / 2; $x++) {
        $allergy_food_name = htmlspecialchars($allergy_food_pos[$allergy_food_count++]);
        $allergy_food = htmlspecialchars($allergy_food_pos[$allergy_food_count++]);
        $allergy_food_detail .= ' <br>
                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
                        &emsp;&emsp;&emsp;<label>อาหาร</label>&nbsp;&nbsp;
                        <label> <font color="red">' . $allergy_food_name . '</font></label>
                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&ensp;
                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&nbsp;
                        <label> <font color="red">' . $allergy_food . '</font></label>';
    }
    for ($x = 1; $x < (count($allergy_etc_pos)) / 2; $x++) {
        $allergy_etc_name = htmlspecialchars($allergy_etc_pos[$allergy_etc_count++]);
        $allergy_etc = htmlspecialchars($allergy_etc_pos[$allergy_etc_count++]);
        $allergy_etc_detail .= '<br>&nbsp;&nbsp;
                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&ensp;
                        &emsp;&emsp;&emsp;&emsp;&emsp;&nbsp;&nbsp;
                        &emsp;&emsp;<span>อื่นๆ&nbsp;&nbsp;&nbsp;&nbsp;
                        <font color="red">' . $allergy_etc_name . '</font>
                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&ensp;
                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&nbsp;
                        <font color="red">' . $allergy_etc . '</font></span>
                        <br>';
    }
}

/* การผ่าตัด */
$checkbox_operation_history1 = '( )';
if ($row['operation_history'] == 'ไม่มี') {
    $checkbox_operation_history1 = '(' . $image_check . ')';
}
$checkbox_operation_history2 = '( )';
if ($row['operation_history'] != 'ไม่มี') {
    $checkbox_operation_history2 = '(' . $image_check . ')';
    $textbox_operation_history = htmlspecialchars($row['operation_history']);
}

/* ประวัติการเจ็บปวยในครอบครัว */
$family_medical_history_1 = '( )';
if ($row['family_medical_history'] == 'ไม่มี' || $row['family_medical_history'] == null) {
    $family_medical_history_1  = '(' . $image_check . ')';
}
$family_medical_history_2 = '( )';
if ($row['family_medical_history'] == 'มี') {
    $family_medical_history_2  = '(' . $image_check . ')';
    $family_medical_pos = explode(" ", $row['family_medical_history_detail']);
    $family_medical_detail =  "";
    $family_medical_top   = '&emsp;<label> โรค</label>
                                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&ensp;
                                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;<label> เกี่ยวข้องเป็น</label>';
    $family_medical_count  = 0;
    for ($x = 1; $x < (count($family_medical_pos)) / 2; $x++) {
        $family_medical_name = htmlspecialchars($family_medical_pos[$family_medical_count++]);
        $family_medical = htmlspecialchars($family_medical_pos[$family_medical_count++]);
        $family_medical_detail .= '<br>&nbsp;&nbsp;
                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&ensp;
                        &emsp;&emsp;&emsp;&emsp;&emsp;&nbsp;&nbsp;
                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
                        <label> ' . $family_medical_name . '</label>
                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&ensp;
                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
                        <label> ' . $family_medical . '</label>';
    }
}
if ($row_period != null) {
    /* ประจำเดือน */
    $style_period = '';
    if ($row_period['period'] == null || $row_ipt['sex'] == 1 || $row_ipt['age_y'] < 9) {
        $style_period = 'display: none;';
    }
    $period             =  $row_period['period'];
    if ($period == "ยังไม่มี") {
        $period_1 = '(' . $image_check . ')';
    } else {
        $period_1 = '( )';
    }
    if ($period == "มี") {
        $period_2 = '(' . $image_check . ')';
    } else {
        $period_2 = '( )';
    }
    if ($period == "หมดประจำเดือน") {
        $period_3 = '(' . $image_check . ')';
    } else {
        $period_3 = '( )';
    }
    $period_normal      =  $row_period['period_normal'];
    if ($period_normal == "ปกติ") {
        $period_normal_1 = '(' . $image_check . ')';
    } else {
        $period_normal_1 = '( )';
    }
    if ($period_normal == "ผิดปกติ") {
        $period_normal_2 = '(' . $image_check . ')';
    } else {
        $period_normal_2 = '( )';
    }
    if ($period_normal == "LMP") {
        $period_normal_3 = '(' . $image_check . ')';
    } else {
        $period_normal_3 = '( )';
    }
    $period_disorders   =  $row_period['period_disorders'];
    $period_lmp         =  $row_period['period_lmp'];
    $period_menopause   =  $row_period['period_menopause'];

    /* อาชีพ(ระบุ) */
    $style_occupation = '';
    if (isset($row_period['occupation']) == null) {
        $style_occupation = 'display: none;';
    }
    $occupation = $row_period['occupation'];

    /* พฤติกรรมเสี่ยง*/
    $style_risk = '';
    if (($row_period['no_risk'] == null)
        && ($row_period['smoking'] == null)
        && ($row_period['smoke_year'] == null)
        && ($row_period['smoke_frequency'] == null)
        && ($row_period['smoke_stopped'] == null)
        && ($row_period['alcohol'] == null)
        && ($row_period['alc_year'] == null)
        && ($row_period['alc_frequency'] == null)
        && ($row_period['alc_stopped'] == null)
        && ($row_period['medication_used'] == null)
        && ($row_period['med_name'] == null)
        && ($row_period['med_year'] == null)
        && ($row_period['med_frequency'] == null)
        && ($row_period['med_stopped'] == null)
    ) {
        $style_risk = 'display: none;';
    }
    $no_risk            =  $row_period['no_risk'];
    if ($no_risk == "Y") {
        $no_risk = '(' . $image_check . ')';
    } else {
        $no_risk = '( )';
    }
    $smoking            =  $row_period['smoking'];
    if ($smoking == "Y") {
        $smoking = '(' . $image_check . ')';
        $smoke_year         =  $row_period['smoke_year'];
        $smoke_frequency    =  $row_period['smoke_frequency'];
        $smoke_stopped      =  $row_period['smoke_stopped'];
        $smoking_detail = '<U><I>' . htmlspecialchars($smoke_year) . '</I></U> ' . 'ปี ปริมาณ ' . '<U><I>' . htmlspecialchars($smoke_frequency) . '</I></U>' . ' /วัน  เลิกเมื่อ ' . '<U><I>' . htmlspecialchars($smoke_stopped) . '</I></U>';
    } else {
        $smoking = '( )';
    }

    $alcohol        =  $row_period['alcohol'];
    if ($alcohol == "Y") {
        $alcohol = '(' . $image_check . ')';
        $alc_year       =  $row_period['alc_year'];
        $alc_frequency  =  $row_period['alc_frequency'];
        $alc_stopped    =  $row_period['alc_stopped'];
        $alcohol_detail = '<U><I>' . htmlspecialchars($alc_year) . '</I></U> ' . 'ปี ปริมาณ ' . '<U><I>' . htmlspecialchars($alc_frequency) . '</I></U>' . ' /วัน  เลิกเมื่อ ' . '<U><I>' . htmlspecialchars($alc_stopped) . '</I></U>';
    } else {
        $alcohol = '( )';
    }

    $medication_used =  $row_period['medication_used'];
    if ($medication_used == "Y") {
        $medication_used = '(' . $image_check . ')';
        $med_name        =  $row_period['med_name'];
        $med_year        =  $row_period['med_year'];
        $med_frequency   =  $row_period['med_frequency'];
        $med_stopped     =  $row_period['med_stopped'];
        $medication_used_detail = '<U><I>' . htmlspecialchars($med_name) . '</I></U> ' . ' ระยะเวลาที่ใช้ <U><I>' . htmlspecialchars($med_year) . '</I></U> ' . ' ปริมาณ ' . '<U><I>' . htmlspecialchars($med_frequency) . '</I></U>' . ' /วัน  เลิกเมื่อ ' . '<U><I>' . htmlspecialchars($med_stopped) . '</I></U>';
    } else {
        $medication_used = '( )';
    }
} else {
    $style_occupation = 'display: none;';
    $style_risk = 'display: none;';
    $no_risk = '';
    $smoking = '';
    $smoking_detail = '';
    $alcohol = '';
    $alcohol_detail = '';
    $medication_used = '';
    $medication_used_detail = '';
    $style_period = 'display: none;';
    $period_1 = '';
    $period_2 = '';
    $period_normal_1 = '';
    $period_normal_2 = '';
    $period_normal_3 = '';
    $period_disorders = '';
    $period_lmp = '';
    $period_3 = '';
    $period_menopause = '';
}

/* ประวัติการได้รับภูมิคุ้มกัน (เฉพาะเด็ก) */
$textbox_receives_immunisation_history_kid = '';
$checkbox_receives_immunisation_history_kid1 = '( )';
$style_receives_immunisation_history_kid = '';
if ($row['receives_immunisation_history_kid'] == null) {
    $style_receives_immunisation_history_kid = 'display: none;';
}
if ($row['receives_immunisation_history_kid'] == 'ครบตามวัย') {
    $checkbox_receives_immunisation_history_kid1 = '(' . $image_check . ')';
}
$checkbox_receives_immunisation_history_kid2 = '( )';
if ($row['receives_immunisation_history_kid'] != 'ครบตามวัย') {
    $checkbox_receives_immunisation_history_kid2 = '(' . $image_check . ')';
    $textbox_receives_immunisation_history_kid = htmlspecialchars($row['receives_immunisation_history_kid']);
}

/* การพัฒนาการ (เฉพาะเด็ก) */
$textbox_developmentally_kid = '';
$checkbox_developmentally_kid1 = '( )';
$style_developmentally_kid = '';
if ($row['developmentally_kid'] == null) {
    $style_developmentally_kid = 'display: none;';
}
if ($row['developmentally_kid'] == 'ปกติ') {
    $checkbox_developmentally_kid1 = '(' . $image_check . ')';
}
$checkbox_developmentally_kid2 = '( )';
if ($row['developmentally_kid'] != 'ปกติ') {
    $checkbox_developmentally_kid2 = '(' . $image_check . ')';
    $textbox_developmentally_kid = htmlspecialchars($row['developmentally_kid']);
}

/* มารดา */
$style_mother = '';
$style_g = '';
$style_p = '';
$style_anc = '';
$style_tt = '';
$style_gestational_age = '';
$style_gestational_day = '';
$style_c_apgar1 = '';
$style_c_apgar5 = '';
$style_c_apgar10 = '';
$style_sex_2 = '';
$style_type_in = '';
$style_c_born_date = '';
$style_c_born_time = '';
$style_c_labor_type = '';
$style_c_indication = '';
$style_c_intrapartum = '';
$style_c_labor_normal = '';
$style_c_serology = '';
$style_c_anterpartum = '';
$style_c_hbv = '';
$style_c_bcg = '';
$style_c_inform_officer = '';
$style_c_inform_mother = '';
$style_c_inform_etc = '';
$style_c_inform_etc_text = '';

if (($row['g'] == null || $row['g'] == '')
    && ($row['p'] == null || $row['p'] == '')
    && ($row['anc'] == null || $row['anc'] == '')
    && ($row['tt'] == null || $row['tt'] == '')
    && ($row['gestational_age'] == null || $row['gestational_age'] == '')
    && ($row['gestational_day'] == null || $row['gestational_day'] == '')
) {
    $style_mother  = 'display: none;';
}
if ($row['g'] == null || $row['g'] == '') {
    $style_g = 'display: none;';
}
if ($row['p'] == null || $row['p'] == '') {
    $style_p = 'display: none;';
}
if ($row['anc'] == null || $row['anc'] == '') {
    $style_anc = 'display: none;';
}
if ($row['tt'] == null || $row['tt'] == '') {
    $style_tt = 'display: none;';
}
if ($row['gestational_age'] == null || $row['gestational_age'] == '') {
    $style_gestational_age = 'display: none;';
}
if ($row['gestational_day'] == null || $row['gestational_day'] == '') {
    $style_gestational_day = 'display: none;';
}
if ($row['c_apgar1'] == null || $row['c_apgar1'] == '') {
    $style_c_apgar1 = 'display: none;';
}
if ($row['c_apgar5'] == null || $row['c_apgar5'] == '') {
    $style_c_apgar5 = 'display: none;';
}
if ($row['c_apgar10'] == null || $row['c_apgar10'] == '') {
    $style_c_apgar10 = 'display: none;';
}
if ($row['sex_2'] == null || $row['sex_2'] == '') {
    $style_sex_2 = 'display: none;';
}
if ($row['type_in'] == null || $row['type_in'] == '') {
    $style_type_in = 'display: none;';
}
if ($row['c_born_date'] == null || $row['c_born_date'] == '') {
    $style_c_born_date = 'display: none;';
}
if ($row['c_born_time'] == null || $row['c_born_time'] == '') {
    $style_c_born_time = 'display: none;';
}
if ($row['c_labor_type2'] == null || $row['c_labor_type2'] == '') {
    $style_c_labor_type = 'display: none;';
}
if ($row['c_indication'] == null || $row['c_indication'] == '') {
    $style_c_indication = 'display: none;';
}
if ($row['c_intrapartum'] == null || $row['c_intrapartum'] == '') {
    $style_c_intrapartum = 'display: none;';
}
if ($row['c_labor_normal'] == null || $row['c_labor_normal'] == '') {
    $style_c_labor_normal = 'display: none;';
}
if ($row['c_serology'] == null || $row['c_serology'] == '') {
    $style_c_serology = 'display: none;';
}
if ($row['c_anterpartum'] == null || $row['c_anterpartum'] == '') {
    $style_c_anterpartum = 'display: none;';
}

if ($row['c_hbv'] == null || $row['c_hbv'] == '') {
    $style_c_hbv = 'display: none;';
}
if ($row['c_bcg'] == null || $row['c_bcg'] == '') {
    $style_c_bcg = 'display: none;';
}

if ($row['c_inform_officer'] == null || $row['c_inform_officer'] == '') {
    $style_c_inform_officer = 'display: none;';
}

if ($row['c_inform_mother'] == null || $row['c_inform_mother'] == '') {
    $style_c_inform_mother = 'display: none;';
}

if ($row['c_inform_etc'] == null || $row['c_inform_etc'] == '') {
    $style_c_inform_etc = 'display: none;';
}

if ($row['c_inform_etc_text'] == null || $row['c_inform_etc_text'] == '') {
    $style_c_inform_etc_text = 'display: none;';
}





/* ประวัติด้านสูตินรีเวชกรรม OB-GYN ย่อมาจาก OBSTRETIC GYNECOLOGY สูติ-นรีเวชกรรม*/
$style_OB_GYN = '';
$style_last_child = '';
$style_last_abort = '';
$style_curette = '';
$style_lmp = '';
$style_edc = '';
if (($row['last_child'] == null || $row['last_child'] == '')
    && ($row['last_abort'] == null || $row['last_abort'] == '')
    && ($row['curette'] == null || $row['curette'] == '')
    && ($row['lmp'] == null || $row['lmp'] == '')
    && ($row['edc'] == null || $row['edc'] == '')
) {
    $style_OB_GYN  = 'display: none;';
}
if ($row['last_child'] == null || $row['last_child'] == '') {
    $style_last_child = 'display: none;';
}
if ($row['last_abort'] == null || $row['last_abort'] == '') {
    $style_last_abort = 'display: none;';
}
if ($row['curette'] == null || $row['curette'] == '') {
    $style_curette = 'display: none;';
}
if ($row['lmp'] == null || $row['lmp'] == '') {
    $style_lmp = 'display: none;';
}
if ($row['edc'] == null || $row['edc'] == '') {
    $style_edc = 'display: none;';
}

/* อาการระหว่างตั้งครรภ์ */
$textbox_condition_pregnant = '';
$checkbox_condition_pregnant1 = '( )';
$style_condition_pregnant = '';
if ($row['condition_pregnant'] == null) {
    $style_condition_pregnant = 'display: none;';
}
if ($row['condition_pregnant'] == 'ปกติ') {
    $checkbox_condition_pregnant1 = '(' . $image_check . ')';
}
$checkbox_condition_pregnant2 = '( )';
if ($row['condition_pregnant'] != 'ปกติ') {
    $checkbox_condition_pregnant2 = '(' . $image_check . ')';
    $textbox_condition_pregnant = htmlspecialchars($row['condition_pregnant']);
}

/* ผลเลือด */
$style_lab_result = '';
$style_hiv = '';
$style_vdrl = '';
$style_hbs_ag = '';
$style_hct = '';
$style_hiv2 = '';
$style_vdrl2 = '';
$style_hbs_ag2 = '';
$style_hct2 = '';
$style_gr = '';
$style_thalassemia = '';
$style_husband = '';
if (($row['hiv'] == null || $row['hiv'] == '') && ($row['vdrl'] == null || $row['vdrl'] == '')
    && ($row['hbs_ag'] == null || $row['hbs_ag'] == '') && ($row['hct'] == null || $row['hct'] == '')
    && ($row['hiv2'] == null || $row['hiv2'] == '') && ($row['vdrl2'] == null || $row['vdrl2'] == '')
    && ($row['hbs_ag2'] == null || $row['hbs_ag2'] == '') && ($row['hct2'] == null || $row['hct2'] == '')
    && ($row['gr'] == null || $row['gr'] == '')
    && ($row['thalassemia'] == null || $row['thalassemia'] == '')
    && ($row['husband'] == null || $row['husband'] == '')
) {
    $style_lab_result = 'display: none;';
}
if ($row['hiv'] == null || $row['hiv'] == '') {
    $style_hiv = 'display: none;';
}
if ($row['vdrl'] == null || $row['vdrl'] == '') {
    $style_vdrl = 'display: none;';
}
if ($row['hbs_ag'] == null || $row['hbs_ag'] == '') {
    $style_hbs_ag = 'display: none;';
}
if ($row['hct'] == null || $row['hct'] == '') {
    $style_hct = 'display: none;';
}
if ($row['hiv2'] == null || $row['hiv2'] == '') {
    $style_hiv2 = 'display: none;';
}
if ($row['vdrl2'] == null || $row['vdrl2'] == '') {
    $style_vdrl2 = 'display: none;';
}
if ($row['hbs_ag2'] == null || $row['hbs_ag2'] == '') {
    $style_hbs_ag2 = 'display: none;';
}
if ($row['hct2'] == null || $row['hct2'] == '') {
    $style_hct2 = 'display: none;';
}
if ($row['gr'] == null || $row['gr'] == '') {
    $style_gr = 'display: none;';
}
if ($row['thalassemia'] == null || $row['thalassemia'] == '') {
    $style_thalassemia = 'display: none;';
}
if ($row['husband'] == null || $row['husband'] == '') {
    $style_husband = 'display: none;';
}

/* วิธีคลอด */
$textbox_deliver_anomalies = '';
$checkbox_deliver_anomalies1 = '( )';
$style_deliver_anomalies = '';
if ($row['deliver_anomalies'] == null) {
    $style_deliver_anomalies = 'display: none;';
}
if ($row['deliver_anomalies'] == 'ปกติ') {
    $checkbox_deliver_anomalies1 = '(' . $image_check . ')';
}
$checkbox_deliver_anomalies2 = '( )';
if ($row['deliver_anomalies'] != 'ปกติ') {
    $checkbox_deliver_anomalies2 = '(' . $image_check . ')';
    $textbox_deliver_anomalies = htmlspecialchars($row['deliver_anomalies']);
}

/* คลอดที่ */
$style_data_deliver = '';
$style_deliver_location = '';
$style_deliver_first_weight = '';
$style_deliver_first_health = '';
if (($row['deliver_location'] == null || $row['deliver_location'] == '')
    && ($row['deliver_first_weight'] == null || $row['deliver_first_weight'] == '')
    && ($row['deliver_first_health'] == null || $row['deliver_first_health'] == '')
) {
    $style_data_deliver = 'display: none;';
}
if ($row['deliver_location'] == null || $row['deliver_location'] == '') {
    $style_deliver_location = 'display: none;';
}
if ($row['deliver_first_weight'] == null || $row['deliver_first_weight'] == '') {
    $style_deliver_first_weight = 'display: none;';
}
if ($row['deliver_first_health'] == null || $row['deliver_first_health'] == '') {
    $style_deliver_first_health = 'display: none;';
}

/* การเลี้ยงทารก */
$style_infant_feeding = '';
if (($row['fant_breast_feeding_end_age_month'] == null)
    && (($row['fant_artificial_feeding_start_age_month'] == null))
    && ($row['fant_feeding_etc'] == null)
) {
    $style_infant_feeding = 'display: none;';
}

$checkbox_fant_breast = '( )';
if ($row['fant_breast_feeding_end_age_month'] != null) {
    $checkbox_fant_breast = '(' . $image_check . ')';
}
$checkbox_fant_artificial = '( )';
if ($row['fant_artificial_feeding_start_age_month'] != null) {
    $checkbox_fant_artificial = '(' . $image_check . ')';
}
$checkbox_fant_etc = '( )';
if ($row['fant_feeding_etc'] != null) {
    $checkbox_fant_etc = '(' . $image_check . ')';
}

/* การให้อาหารเสริม */
$style_supplementary_feeding = '';
if ($row['supplementary_feeding'] == null || $row['supplementary_feeding'] == '' || $row_ipt['age_y'] >  15) {
    $style_supplementary_feeding = 'display: none;';
}

$textbox_supplementary_feeding = '';
$checkbox_supplementary_feeding1 = '( )';
if ($row['supplementary_feeding'] == 'ยังไม่ได้รับ') {
    $checkbox_supplementary_feeding1 = '(' . $image_check . ')';
}
$checkbox_supplementary_feeding2 = '( )';
if ($row['supplementary_feeding'] == 'ได้รับ') {
    $checkbox_supplementary_feeding2 = '(' . $image_check . ')';
    $textbox_supplementary_feeding = htmlspecialchars($row['supplementary_feeding_start_age_month']);
}

/* ประวัติแพ้/โรคประจำตัว/ผ่าตัด */
$textbox_operation_allergy = '';
$checkbox_operation_allergy1 = '( )';
if ($row['disease_operation_allergy'] == 'ไม่มี') {
    $checkbox_operation_allergy1 = '(' . $image_check . ')';
}
$checkbox_operation_allergy2 = '( )';
if ($row['disease_operation_allergy'] != 'ไม่มี') {
    $checkbox_operation_allergy2 = '(' . $image_check . ')';
    $textbox_operation_allergy = htmlspecialchars($row['disease_operation_allergy']);
}

/* การเข้ารับการรักษาในโรงพยาบาล */
$checkbox_inpatient_history1 = '( )';
if ($row['inpatient_history'] == 'ไม่เคย') {
    $checkbox_inpatient_history1 = '(' . $image_check . ')';
}
$checkbox_inpatient_history2 = '( )';
if ($row['inpatient_history'] == 'เคย') {
    $checkbox_inpatient_history2 = '(' . $image_check . ')';
}

/* ประวัติการคลอด */
$style_brith_history = '';
if (($row['pb_no'] == null)
    && ($row['giant_baby'] == null)
    && ($row['distocia'] == null)
    && ($row['extraction'] == null)
    && ($row['pph'] == null)
    && ($row['pb_etc'] == null)
) {
    $style_brith_history = 'display: none;';
}

$checkbox_pb_no   = '( )';
if ($row['pb_no'] == 'Y') {
    $checkbox_pb_no = '(' . $image_check . ')';
}
$checkbox_giant_baby = '( )';
if ($row['giant_baby'] == 'Y') {
    $checkbox_giant_baby = '(' . $image_check . ')';
}
$checkbox_distocia = '( )';
if ($row['distocia'] == 'Y') {
    $checkbox_distocia = '(' . $image_check . ')';
}
$checkbox_extraction = '( )';
$textbox_extraction = '';
if ($row['extraction'] != null) {
    $checkbox_extraction = '(' . $image_check . ')';
    $textbox_extraction = htmlspecialchars($row['extraction']);
}
$checkbox_pph = '( )';
if ($row['pph'] == 'Y') {
    $checkbox_pph = '(' . $image_check . ')';
}
$checkbox_pb_etc = '( )';
$textbox_pb_etc = '';
if ($row['pb_etc'] != null) {
    $checkbox_pb_etc = '(' . $image_check . ')';
    $textbox_pb_etc = htmlspecialchars($row['pb_etc']);
}

/* ตรวจหน้าท้อง */
$style_high_of_fundus = '';
$style_hf = '';
$style_hf_position = '';
if (($row['hf'] == null || $row['hf'] == '')
    && ($row['hf_position'] == null || $row['hf_position'] == '')
) {
    $style_high_of_fundus = 'display: none;';
}
if ($row['hf'] == null || $row['hf'] == '') {
    $style_hf = 'display: none;';
}
if ($row['hf_position'] == null || $row['hf_position'] == '') {
    $style_hf_position = 'display: none;';
}

$svg_tag = str_replace('<?xml version="1.0" encoding="UTF-8" standalone="no" ?>', '', $row['svg_tag']);
$svg_tag = str_replace('width="700" height="500"', ' height="180"', $svg_tag);
$head =
    '
    <style>
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

    <h2 style="text-align:right;font-size:8pt;">KPH-N1.1-Adm</h2>
    <h2 style="text-align:center;font-size:11pt;">History - Physical Examination Of Newborn ' . '</h2>

    <table id="bg-table" width="100%" style="border-collapse: collapse;font-size:8pt;margin-top:2px;">
        <tr style="border:1px solid #000;margin: 45px;">
            <td  colspan="2" style="border-right:0.5px solid #000;padding:4px;"width="50%">&nbsp;
            <B>วันที่รับไว้รักษา</B>&nbsp;&nbsp;' . htmlspecialchars($row['receiver_medication_date']) . ' <B>&nbsp;  &nbsp;เวลา</B>&nbsp;&nbsp;' . htmlspecialchars($row['receiver_medication_time']) . '&nbsp;น. </td>
            <td  colspan="1" width="50%" style="margin: 35px;padding:4px;">&nbsp;
                <B>เข้ารับการรักษาโดย</B>&nbsp;
                <label><B style="text-decoration: underline;"> ' . $textbox_medication_3 . ' <B></label>
            </td>
        </tr>

        <tr style="border:1px solid #000;margin: 35px;">
        <td colspan="3" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:5px;text-align:left;">&nbsp;
            <B>ผู้ให้ข้อมูล</B>&nbsp;
            <B style="text-decoration: underline;"> '.$textbox_informant.' </B>&nbsp;</td>
    </tr>

        <tr style="border:1px solid #000;margin: 35px;">
            <td colspan="3" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;">&nbsp;
                <B>อาการสำคัญ</B><br>&emsp;&emsp;&emsp;&ensp;&nbsp;' . nl2br(htmlspecialchars($row['chief_complaints'])) . '
            </td>
        </tr>

        <tr style="border:1px solid #000;margin: 35px;">
            <td colspan="3" width="100%" style="margin: 35px;padding:4px;">&nbsp;
                <B>ประวัติการเจ็บป่วยปัจจุบัน</B><br>&emsp;&emsp;&emsp;&ensp;&nbsp;' . nl2br(htmlspecialchars($row['medical_history'])) . '
            </td>
        </tr>

        <tr style="border:1px solid #000;margin: 35px;">
            <td colspan="3" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;">&nbsp;
                <B>สัญญาณชีพแรกรับ</B><br>&nbsp;&nbsp;BP = &nbsp;' . htmlspecialchars($row_vs['sbp']) . '/' . htmlspecialchars($row_vs['dbp']) . '&nbsp;mmHg&nbsp;&nbsp;
                T = &nbsp;' . htmlspecialchars($row['t']) . '&nbsp;*C&nbsp;&nbsp;&emsp; RR = ' . htmlspecialchars($row['rr']) . '&nbsp;/min&nbsp;&nbsp;&nbsp;&nbsp;&emsp; PR = ' . htmlspecialchars($row['pr']) . '&nbsp;/min&nbsp;&nbsp;
                <br>
                &nbsp;&nbsp;BW&emsp;' . htmlspecialchars($row['c_bw']) . '&emsp;gms.&emsp;HC&emsp;' . htmlspecialchars($row['c_hc']) . '&emsp;cms.&emsp;Length&emsp;' . htmlspecialchars($row['c_length']) . '&emsp;cms.
            </td>
        </tr>

        <tr style="border:1px solid #000;margin: 35px;">
            <td colspan="3" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:5px;text-align:center;">&nbsp;
                <B>ประวัติการเจ็บป่วยในอดีต</B>
            </td>
        </tr>

        

        <tr style="border:1px solid #000;margin: 35px;">
            <td colspan="3" width="50%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;">
            <var style="' . $style_type_in . '"> <b>Chief complaint &nbsp;&nbsp; เด็กแรกคลอด:</b> ' . htmlspecialchars($row['type_in']) . ' </var>
            <var style="' . $style_c_born_date . '">&nbsp;&nbsp; <b>วันที่:</b> ' . htmlspecialchars($row['c_born_date']) . ' </var>
            <var style="' . $style_type_in . '">&nbsp;&nbsp; <b>เวลา:</b> ' . htmlspecialchars($row['c_born_time']) . ' </var>
            <br><br>
            <var style="' . $style_c_labor_type . '"> <b>Present illness &nbsp;&nbsp; เด็กแรกเกิด คลอดวิธี:</b> ' . htmlspecialchars($row['c_labor_type2']) . ' </var>
            <var style="' . $style_c_indication . '"> <b>Indication:</b> ' . htmlspecialchars($row['c_indication']) . ' </var>
            <var style="' . $style_c_intrapartum . '"> <b>Intrapartum complication:</b> ' . htmlspecialchars($row['c_intrapartum']) . ' </var>
            <br><br>
            <var style="' . $style_c_labor_normal . '"> <label>'. $checkbox_c_labor_normal .' ไม่มีความผิดปกติระหว่างการคลอด</label> ' . ' </var>
            
            

            <br><br>
            ประวัติการแพ้ยาและการแพ้อื่นๆ &nbsp;&ensp;
                <label>' . $checkbox_allergy_1 . ' ไม่มี</label>
                <label>' . $checkbox_allergy_2 . ' มี (ระบุ)</label>
                &nbsp;&nbsp;&ensp;&nbsp;<font color="red">' . $row['allergy_drug_history_hosxp'] . '</font>
                ' . $allergy_top . '
                ' . $allergy_drug_detail . '
                ' . $allergy_food_detail . '
                ' . $allergy_etc_detail . '
                <br>
                
                <p style="' . $style_mother . '"><br>
                    มารดา
                    <var style="' . $style_g . '"> G  ' . htmlspecialchars($row['g']) . '  </var>
                    <var style="' . $style_p . '"> P  ' . htmlspecialchars($row['p']) . ' </var>
                    <var style="' . $style_c_serology . '"> &nbsp;Serology:  ' . htmlspecialchars($row['c_serology']) . ' </var>
                    <var style="' . $style_c_anterpartum . '"> &nbsp;Anterpartum Complication:  ' . htmlspecialchars($row['c_anterpartum']) . ' </var>
                    <var style="' . $style_tt . '"> &nbsp;ได้ TT:   ' . htmlspecialchars($row['tt']) . '  เข็ม</var><br><br>
                    <var style="' .  '"> <b>Newborn vaccination : </b> '. ' </var>
                    <var style="' . $style_c_hbv . '"> <label>'. $checkbox_hbv .' HBV</label> ' . ' </var>
                    <var style="' . $style_c_bcg . '"> <label>'. $checkbox_bcg .' BCG</label> ' . ' </var><br><br>
                    <var style="' .  '"> <b>ผู้ให้ข้อมูล : </b> '. ' </var>
                    <var style="' . $style_c_inform_officer . '"> <label>'. $checkbox_inf_office .' เจ้าหน้าที่</label> ' . ' </var>
                    <var style="' . $style_c_inform_mother . '"> <label>'. $checkbox_inf_mother .' มารดา</label> ' . ' </var>
                    <var style="' . $style_c_inform_etc . '"> <label>'. $checkbox_inf_etc .' อื่นๆ ระบุ</label> ' . ' </var>
                    <var style="' . $style_c_inform_etc_text . '"> &nbsp;' . htmlspecialchars($row['c_inform_etc_text']) . ' </var>
                    
                </p>
                               
            </td>
        </tr>

        <tr style="border:1px solid #000;margin: 35px;">
            <td colspan="3" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;text-align:center;">&nbsp;
                Physical examination
            </td>
            
        </tr>
        <tr style="border:1px solid #000;margin: 35px;">
            <td colspan="3" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;">&nbsp;
                <var style="' . $style_gestational_age . '"> อายุครรภ์  ' . htmlspecialchars($row['gestational_age']) . ' สัปดาห์ </var>
                <var style="' . $style_c_apgar1 . '"> Apgar score: ' . htmlspecialchars($row['c_apgar1']) . ' /</var>
                <var style="' . $style_c_apgar5 . '">' . htmlspecialchars($row['c_apgar5']) . ' /</var>
                <var style="' . $style_c_apgar10 . '"> ' . htmlspecialchars($row['c_apgar10']) . ' </var>
                <var style="' . $style_sex_2 . '"> Sex: ' . htmlspecialchars($row['sex_2']) . ' </var>
            </p>
            
                </td>
        </tr>

        <tr style="border:1px solid #000;margin: 35px;">
            <td colspan="3" width="50%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;">&nbsp;
            <br>&nbsp;
                General : ' . htmlspecialchars($row['pe_general']) . '
                <br>&nbsp;
                Skin      : ' . htmlspecialchars($row['pe_skin']) . '
                <br>&nbsp;
                Head  : ' . htmlspecialchars($row['pe_head']) . '
                <br>&nbsp;
                Neck  : ' . htmlspecialchars($row['pe_neck']) . '
                <br>&nbsp;
                Face  : ' . htmlspecialchars($row['pe_face']) . '
                <br>&nbsp;
                Ears  : ' . htmlspecialchars($row['pe_ears']) . '
                <br>&nbsp;
                Eyes  : ' . htmlspecialchars($row['pe_eyes']) . '
                <br>&nbsp;
                Nose  : ' . htmlspecialchars($row['pe_nose']) . '
                <br>&nbsp;
                Mouth : ' . htmlspecialchars($row['pe_mouth']) . '
                <br>&nbsp;
                Chest : ' . htmlspecialchars($row['pe_chest']) . '
                <br>&nbsp;
                Lungs : ' . htmlspecialchars($row['pe_lungs']) . '
                <br>&nbsp;
                Heart : ' . htmlspecialchars($row['pe_heart']) . '
                <br>&nbsp;  
               Abdomen : ' . htmlspecialchars($row['pe_abdomen']) . '
               <br>&nbsp;
               Anus : ' . htmlspecialchars($row['pe_anus']) . '
               <br>&nbsp; 
               Extremities     : ' . htmlspecialchars($row['pe_extremities']) . '
                <br>&nbsp;
                Trunk & Spine : ' . htmlspecialchars($row['pe_trunk_spine']) . '
                <br>&nbsp;
                Nervous : ' . htmlspecialchars($row['pe_nervose']) . '
                <br>&nbsp;
                Genitalia : ' . htmlspecialchars($row['pe_rectalgenitalia']) . '
                <br>&nbsp;
                Other : ' . htmlspecialchars($row['pe_other']) . '
                <br>&nbsp;
                PE Text : ' . htmlspecialchars($row['pe_text']) . '
                <br>&nbsp;      
            </td>
           
        </tr>

       

        <tr style="border:1px solid #000;margin: 35px;">
            <td colspan="2" width="50%" style="margin: 35px;padding:4px;">&nbsp;
                Problem List :&nbsp;' . htmlspecialchars($row['problem_list']) . '<br>&nbsp;
                Impression :&nbsp;' . htmlspecialchars($row['impression']) . '<br>&nbsp;  
                Plan Management :&nbsp;' . htmlspecialchars($row['plan_management']) . '<br>
            </td>
            <td width="50%" style="border-left:0.5px solid #000;margin: 35px;padding:4px;">
                แพทย์ผู้บันทึก  <br>' . $admission_note_doctorString . '
                พยาบาลผู้บันทึก ' . htmlspecialchars($row['nurse_name']) . '  ' . htmlspecialchars($row['nurse_pos']) . '
            </td>
        </tr>
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
    <h2 style="text-align:right;font-size:8pt;">FM-OBS-007 แก้ไขครั้งที่ 2 ประกาศใช้ 16 พฤศจิกายน 2558</h2>
';
$mpdf->WriteHTML($head);
$mpdf->Output();
