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

     if(!(
        Session::checkPermission('ADMISSION_NOTE','VIEW')
        )){
        return;
    }
 
//Session::checkPermissionAndShowMessage('DOCUMENT', 'PRINT');


require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
//require_once __DIR__ . '/vendor/autoload.php';
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



//-------------------------Doctor admission note
$sql = "SELECT *
        FROM `ipd_dr_admission_note`
        WHERE an = :an";
$stmt = $conn->prepare($sql);
$stmt->execute($query_parameters);
$row  = $stmt->fetch();

$sql_item ="SELECT dr_adm_item.admission_note_item_id,
            dr_adm_item.admission_note_doctor,
            doctor.`name` AS admission_note_doctorname,
            doctor.licenseno
            FROM ".DbConstant::KPHIS_DBNAME.".ipd_dr_admission_note_item dr_adm_item
            LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".doctor ON doctor.code = dr_adm_item.admission_note_doctor
            WHERE an=:an
            ORDER BY dr_adm_item.admission_note_item_id ASC";
            $stmt_item = $conn->prepare($sql_item);
            $stmt_item->execute(['an'=>$an]);
            $admission_note_count = 0;
            $admission_note_doctorString = '';
            $licenseno_String = '';
                while ($row_item = $stmt_item->fetch()){
                    if($row_item['licenseno'] != '' && $row_item['licenseno'] != null){
                        $licenseno_String = " (".$row_item['licenseno'].")";
                    }
                    $admission_note_doctorString .= $row_item['admission_note_doctorname'].$licenseno_String."<br>";
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

//-------------------------ประวัติการผ่าตัด
        $reg_parameters =['hn' => $hn,'regdatetime'=>$regdatetime,'hospital_name'=>DbConstant::HOSPITAL_NAME];
        $sql_ol = "SELECT CONCAT(ifnull(operation_list.enter_date,''),', ',ifnull(operation_list.operation_name,''),', ',ifnull(doctor.name,''),', ',:hospital_name) AS operation_list
                    FROM ".DbConstant::HOSXP_DBNAME.".operation_list
                    left outer join ".DbConstant::HOSXP_DBNAME.".doctor on doctor.code = operation_list.request_doctor
                    WHERE operation_list.hn= :hn
                    AND operation_list.status_id = 3
                    AND concat(operation_list.enter_date,' ',operation_list.enter_time) < :regdatetime
                    ORDER BY operation_list.enter_date,operation_list.enter_time";
        $stmt_ol = $conn->prepare($sql_ol);
        $stmt_ol->execute($reg_parameters);
        $rows_ol  = $stmt_ol->fetchAll();
        $operation_text = "";
        foreach($rows_ol as $row_ol):
            $operation_text .= '<label>&nbsp;&nbsp;&ensp;&nbsp;&nbsp;&ensp;&nbsp;&nbsp;&ensp;&nbsp;&nbsp;&ensp;
                                &nbsp;&nbsp;&ensp;&nbsp;&nbsp;&ensp;&nbsp;&nbsp;&ensp;&nbsp;&nbsp;&ensp;'
                                .htmlspecialchars($row_ol["operation_list"]).'</label><br>';
        endforeach;
//-------------------------ประวัติการผ่าตัด

//-------------------Vital Sign
$sql_vs =  "SELECT ipd_vs_vital_sign.sbp,ipd_vs_vital_sign.dbp,ipd_vs_vital_sign.bt,ipd_vs_vital_sign.pr,ipd_vs_vital_sign.rr,
            ipd_vs_vital_sign.eye,ipd_vs_vital_sign.verbal,ipd_vs_vital_sign.movement,ipd_vs_vital_sign.braden
            FROM ".DbConstant::KPHIS_DBNAME.".ipd_vs_vital_sign
            WHERE ipd_vs_vital_sign.an=:an
            GROUP BY ipd_vs_vital_sign.vs_datetime ASC LIMIT 1";
$stmt_vs = $conn->prepare($sql_vs);
$stmt_vs->execute(['an'=>$an]);
$row_vs  = $stmt_vs->fetch();
$NeuroSignGCS=0;
if (isset($row_vs['eye'])){
    if (is_numeric($row_vs['eye'])) {
        $NeuroSignGCS += $row_vs['eye'];
    }
}
if (isset($row_vs['verbal'])){
    if (is_numeric($row_vs['verbal'])) {
        $NeuroSignGCS += $row_vs['verbal'];
    }
}
if (isset($row_vs['movement'])){
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
                FROM ".DbConstant::KPHIS_DBNAME.".ipd_nurse_admission_note
                WHERE an=:an";
$stmt_period = $conn->prepare($sql_period);
$stmt_period->execute(['an'=>$an]);
$row_period  = $stmt_period->fetch();
//------------------ipd_nurse_addmission_note เรื่อง "ประจำเดือน","อาชีพ","สารเสพติด"

//$image_check = "<img src='picture/check-png.png'>";
$image_check = "<img src='../include/images/check-1.jpg' width='1.6%' class='check_img'>";
//$image_check = ' / ';



    /* เข้ารับการรักษาโดย */
    if ($row['take_medication_by'] == 'มาเอง')
    {$textbox_medication_3 = htmlspecialchars($row['take_medication_by']);}
    if ($row['take_medication_by'] == 'แพทย์นัด')
    {$textbox_medication_3 = htmlspecialchars($row['take_medication_by']);}
    if ($row['take_medication_by'] != 'มาเอง' && $row['take_medication_by'] != 'แพทย์นัด')
    {$textbox_medication_3 = htmlspecialchars($row['take_medication_by']);}

     /*  มาถึงหอผู้ป่วยโดย */
    $checkbox_arrive_1 = '<input type="checkbox">';
    if ($row['arrive_by'] == 'เดินมา')  {$checkbox_arrive_1 = '<input type="checkbox" checked="checked">';}
    $checkbox_arrive_2 = '<input type="checkbox">';
    if ($row['arrive_by'] == 'รถนั่ง')   {$checkbox_arrive_2 = '<input type="checkbox" checked="checked">';}
    $checkbox_arrive_3 = '<input type="checkbox">';
    if ($row['arrive_by'] == 'รถนอน')  {$checkbox_arrive_3 = '<input type="checkbox" checked="checked">';}
    $checkbox_arrive_4 = '<input type="checkbox">';
    if ($row['arrive_by'] == 'รถ Transfer')  {$checkbox_arrive_4 = '<input type="checkbox" checked="checked">';}
    $checkbox_arrive_5 = '<input type="checkbox">';
    if   ($row['arrive_by'] !='เดินมา'
        &&$row['arrive_by'] !='รถนั่ง'
        &&$row['arrive_by'] !='รถนอน'
        &&$row['arrive_by'] !='รถ Transfer')
    {$checkbox_arrive_5 = '<input type="checkbox" checked="checked">';
        $textbox_arrive_5 = htmlspecialchars($row['arrive_by']);}

    /*  นำส่งผู้ป่วยโดย */
    $taken_by_text = "";
    if ($row['taken_by_relative'] == 'Y') {$taken_by_text .= "ญาติ";}
    if ($row['taken_by_nurse']    == 'Y') {$taken_by_text .= " "."พยาบาล";}
    if ($row['taken_by_crib']     == 'Y') {$taken_by_text .= " "."พนักงานเปล";}
    if ($row['taken_by_etc']      == 'Y') {$taken_by_text .= " ".$row['taken_by'];}

    /* ผู้ให้ข้อมูล */
    $textbox_informant = "";
    if ($row['informant_patient'] == 'ผู้ป่วย')    {$textbox_informant .= htmlspecialchars($row['informant_patient']);}
    if ($row['informant_relatives'] != null)    {$textbox_informant .= htmlspecialchars($row['informant_relatives']);}
    if ($row['informant_deliverer'] == 'ผู้นำส่ง') {$textbox_informant .= htmlspecialchars($row['informant_deliverer']);}
    if ($row['informant_etc'] != null)          {$textbox_informant .= htmlspecialchars($row['informant_etc']);}

    /* โรคประจำตัว */
    $checkbox_disease_1 = '( )';
        if ($row['disease'] == 'ไม่มี') {$checkbox_disease_1 = '('.$image_check.')';
            $disease_top =  "";
            $disease_detail =  "";
        }
    $checkbox_disease_2 = '( )';
        if ($row['disease'] == 'มี')   {$checkbox_disease_2 = '('.$image_check.')';
            $disease_top =  '&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&ensp;<label> โรค</label>
                    &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&ensp;
                    &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&ensp;<label> จำนวนปี</label>
                    &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&ensp;<label> สถานพยาบาลที่รักษา</label>';
            $disease_pos = explode(" ",$row['disease_detail']);
            $disease_detail =  "";
            $y = 0;
            for ($x = 1; $x < (count($disease_pos))/3; $x++) {
                $disease_name = htmlspecialchars($disease_pos[$y++]);
                $disease_year = htmlspecialchars($disease_pos[$y++]);
                $disease_hospital = htmlspecialchars($disease_pos[$y++]);
                $disease_detail .= '<br>&nbsp;&nbsp;
                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&ensp;
                        &emsp;&emsp;&emsp;&emsp;&emsp;&nbsp;&nbsp;
                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
                        <label> '.$disease_name.'</label>
                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&ensp;
                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&ensp;
                        <label> '.$disease_year.'</label>
                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&ensp;&emsp;
                        <label> '.$disease_hospital.'</label>';
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
    $checkbox_allergy_1 = '( )';
        if ($row['allergy_history'] == 'ไม่มี') {$checkbox_allergy_1 = '('.$image_check.')';
            $allergy_drug_detail =  "";
            $allergy_food_detail =  "";
            $allergy_etc_detail  =  "";
            $allergy_top =  "";
        }
    $checkbox_allergy_2 = '( )';
        if ($row['allergy_history'] == 'มี')   {$checkbox_allergy_2 = '('.$image_check.')';
            $allergy_drug_pos = explode(" ",$row['allergy_drug_history']);
            $allergy_food_pos = explode(" ",$row['allergy_food_history']);
            $allergy_etc_pos  = explode(" ",$row['allergy_etc_history']);
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
            for ($x = 1; $x < (count($allergy_drug_pos))/2; $x++) {
                $allergy_drug_name = htmlspecialchars($allergy_drug_pos[$allergy_drug_count++]);
                $allergy_drug = htmlspecialchars($allergy_drug_pos[$allergy_drug_count++]);
                $allergy_drug_detail .= '<br>&nbsp;&nbsp;
                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&ensp;
                        &emsp;&emsp;&emsp;&emsp;&emsp;&nbsp;&nbsp;
                        &emsp;&emsp;<label>ยา</label>&emsp;&emsp;
                        <label> <font color="red">'.$allergy_drug_name.'</font></label>
                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
                        <label> <font color="red">'.$allergy_drug.'</font></label>';
            }
            for ($x = 1; $x < (count($allergy_food_pos))/2; $x++) {
                $allergy_food_name = htmlspecialchars($allergy_food_pos[$allergy_food_count++]);
                $allergy_food = htmlspecialchars($allergy_food_pos[$allergy_food_count++]);
                $allergy_food_detail .= ' <br>
                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
                        &emsp;&emsp;&emsp;<label>อาหาร</label>&nbsp;&nbsp;
                        <label> <font color="red">'.$allergy_food_name.'</font></label>
                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&ensp;
                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&nbsp;
                        <label> <font color="red">'.$allergy_food.'</font></label>';
            }
            for ($x = 1; $x < (count($allergy_etc_pos))/2; $x++) {
                $allergy_etc_name = htmlspecialchars($allergy_etc_pos[$allergy_etc_count++]);
                $allergy_etc = htmlspecialchars($allergy_etc_pos[$allergy_etc_count++]);
                $allergy_etc_detail .= '<br>&nbsp;&nbsp;
                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&ensp;
                        &emsp;&emsp;&emsp;&emsp;&emsp;&nbsp;&nbsp;
                        &emsp;&emsp;<span>อื่นๆ&nbsp;&nbsp;&nbsp;&nbsp;
                        <font color="red">'.$allergy_etc_name.'</font>
                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&ensp;
                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&nbsp;
                        <font color="red">'.$allergy_etc.'</font></span>
                        <br>';
            }
        }

        /* การผ่าตัด */
        $checkbox_operation_history1 = '( )';
        if ($row['operation_history'] == 'ไม่มี') {$checkbox_operation_history1 = '('.$image_check.')';}
        $checkbox_operation_history2 = '( )';
        if ($row['operation_history'] != 'ไม่มี')   {$checkbox_operation_history2 = '('.$image_check.')';
            $textbox_operation_history = htmlspecialchars($row['operation_history']);
        }

        /* ประวัติการเจ็บปวยในครอบครัว */
        $family_medical_history_1 = '( )';
        if ($row['family_medical_history'] == 'ไม่มี' || $row['family_medical_history'] == null) {$family_medical_history_1  = '('.$image_check.')';}
        $family_medical_history_2 = '( )';
        if ($row['family_medical_history'] == 'มี') {$family_medical_history_2  = '('.$image_check.')';
            $family_medical_pos = explode(" ",$row['family_medical_history_detail']);
            $family_medical_detail =  "";
                $family_medical_top   = '&emsp;<label> โรค</label>
                                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&ensp;
                                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;<label> เกี่ยวข้องเป็น</label>';
            $family_medical_count  = 0;
            for ($x = 1; $x < (count($family_medical_pos))/2; $x++) {
                $family_medical_name = htmlspecialchars($family_medical_pos[$family_medical_count++]);
                $family_medical = htmlspecialchars($family_medical_pos[$family_medical_count++]);
                $family_medical_detail .= '<br>&nbsp;&nbsp;
                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&ensp;
                        &emsp;&emsp;&emsp;&emsp;&emsp;&nbsp;&nbsp;
                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
                        <label> '.$family_medical_name.'</label>
                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&ensp;
                        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
                        <label> '.$family_medical.'</label>';
            }
        }

               /* ประวัติการเจ็บปวยในครอบครัว */
               $review_system1 = '( )';
               if ($row['review_of_system'] == 'ปกติ' || $row['review_of_system'] == null) {$review_system1  = '('.$image_check.')';}
               $review_system2 = '( )';
               if ($row['review_of_system'] != 'ปกติ' && $row['review_of_system'] != null) {$review_system2  = '('.$image_check.')';
                $textbox_review_system = htmlspecialchars($row['review_of_system']);
               }


        if($row_period != null){
            /* ประจำเดือน */
            $style_period = '';
            if($row_period['period'] == null || $row_ipt['sex'] == 1 || $row_ipt['age_y'] < 9){
                $style_period = 'display: none;';
            }
            $period             =  $row_period['period'];
            if($period == "ยังไม่มี"){$period_1 = '('.$image_check.')';}else{$period_1 = '( )';}
            if($period == "มี"){$period_2 = '('.$image_check.')';}else{$period_2 = '( )';}
            if($period == "หมดประจำเดือน"){$period_3 = '('.$image_check.')';}else{$period_3 = '( )';}
            $period_normal      =  $row_period['period_normal'];
            if($period_normal == "ปกติ"){$period_normal_1 = '('.$image_check.')';}else{$period_normal_1 = '( )';}
            if($period_normal == "ผิดปกติ"){$period_normal_2 = '('.$image_check.')';}else{$period_normal_2 = '( )';}
            if($period_normal == "LMP"){$period_normal_3 = '('.$image_check.')';}else{$period_normal_3 = '( )';}
            $period_disorders   =  $row_period['period_disorders'];
            $period_lmp         =  $row_period['period_lmp'];
            $period_menopause   =  $row_period['period_menopause'];

            /* อาชีพ(ระบุ) */
            $style_occupation = '';
            if(isset($row_period['occupation']) == null){
                $style_occupation = 'display: none;';
            }
            $occupation = $row_period['occupation'];

            /* พฤติกรรมเสี่ยง*/
            $style_risk = '';
            if(($row_period['no_risk'] == null)
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
                && ($row_period['med_stopped'] == null)){
                $style_risk = 'display: none;';
            }
            $no_risk            =  $row_period['no_risk'];
            if($no_risk == "Y"){$no_risk = '('.$image_check.')';}else{$no_risk = '( )';}
            $smoking            =  $row_period['smoking'];
            if($smoking == "Y"){
                $smoking = '('.$image_check.')';
                $smoke_year         =  $row_period['smoke_year'];
                $smoke_frequency    =  $row_period['smoke_frequency'];
                $smoke_stopped      =  $row_period['smoke_stopped'];
                $smoking_detail = '<U><I>'.htmlspecialchars($smoke_year).'</I></U> '.'ปี ปริมาณ '.'<U><I>'.htmlspecialchars($smoke_frequency).'</I></U>'.' /วัน  เลิกเมื่อ '.'<U><I>'.htmlspecialchars($smoke_stopped).'</I></U>';
            }else{
                $smoking = '( )';
            }

            $alcohol        =  $row_period['alcohol'];
            if($alcohol == "Y"){
                $alcohol = '('.$image_check.')';
                $alc_year       =  $row_period['alc_year'];
                $alc_frequency  =  $row_period['alc_frequency'];
                $alc_stopped    =  $row_period['alc_stopped'];
                $alcohol_detail = '<U><I>'.htmlspecialchars($alc_year).'</I></U> '.'ปี ปริมาณ '.'<U><I>'.htmlspecialchars($alc_frequency).'</I></U>'.' /วัน  เลิกเมื่อ '.'<U><I>'.htmlspecialchars($alc_stopped).'</I></U>';
            }else{
                $alcohol = '( )';
            }

            $medication_used =  $row_period['medication_used'];
            if($medication_used == "Y"){
                $medication_used = '('.$image_check.')';
                $med_name        =  $row_period['med_name'];
                $med_year        =  $row_period['med_year'];
                $med_frequency   =  $row_period['med_frequency'];
                $med_stopped     =  $row_period['med_stopped'];
                $medication_used_detail = '<U><I>'.htmlspecialchars($med_name).'</I></U> '.' ระยะเวลาที่ใช้ <U><I>'.htmlspecialchars($med_year).'</I></U> '.' ปริมาณ '.'<U><I>'.htmlspecialchars($med_frequency).'</I></U>'.' /วัน  เลิกเมื่อ '.'<U><I>'.htmlspecialchars($med_stopped).'</I></U>';
            }else{
                $medication_used = '( )';
            }
        }else {
            $style_occupation='display: none;';
            $style_risk='display: none;';
            $no_risk='';
            $smoking='';
            $smoking_detail='';
            $alcohol='';
            $alcohol_detail='';
            $medication_used='';
            $medication_used_detail='';
            $style_period='display: none;';
            $period_1='';
            $period_2='';
            $period_normal_1='';
            $period_normal_2='';
            $period_normal_3='';
            $period_disorders='';
            $period_lmp='';
            $period_3='';
            $period_menopause='';
        }

        /* ประวัติการได้รับภูมิคุ้มกัน (เฉพาะเด็ก) */
        $textbox_receives_immunisation_history_kid = '';
        $checkbox_receives_immunisation_history_kid1 = '( )';
        $style_receives_immunisation_history_kid = '';
        if($row['receives_immunisation_history_kid'] == null){
            $style_receives_immunisation_history_kid = 'display: none;';
        }
        if ($row['receives_immunisation_history_kid'] == 'ครบตามวัย') {$checkbox_receives_immunisation_history_kid1 = '('.$image_check.')';}
        $checkbox_receives_immunisation_history_kid2 = '( )';
        if ($row['receives_immunisation_history_kid'] != 'ครบตามวัย') {$checkbox_receives_immunisation_history_kid2 = '('.$image_check.')';
            $textbox_receives_immunisation_history_kid = htmlspecialchars($row['receives_immunisation_history_kid']);
        }

         /* การพัฒนาการ (เฉพาะเด็ก) */
        $textbox_developmentally_kid = '';
        $checkbox_developmentally_kid1 = '( )';
        $style_developmentally_kid = '';
        if($row['developmentally_kid'] == null){
            $style_developmentally_kid = 'display: none;';
        }
        if ($row['developmentally_kid'] == 'ปกติ') {$checkbox_developmentally_kid1 = '('.$image_check.')';}
        $checkbox_developmentally_kid2 = '( )';
        if ($row['developmentally_kid'] != 'ปกติ') {$checkbox_developmentally_kid2 = '('.$image_check.')';
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
        if(($row['g'] == null || $row['g'] == '')
        && ($row['p'] == null || $row['p'] == '')
        && ($row['anc'] == null || $row['anc'] == '')
        && ($row['tt'] == null || $row['tt'] == '')
        && ($row['gestational_age'] == null || $row['gestational_age'] == '')
        && ($row['gestational_day'] == null || $row['gestational_day'] == '')){
            $style_mother  = 'display: none;';
        }
        if($row['g'] == null || $row['g'] == ''){ $style_g = 'display: none;';}
        if($row['p'] == null || $row['p'] == ''){ $style_p = 'display: none;';}
        if($row['anc'] == null || $row['anc'] == ''){ $style_anc = 'display: none;';}
        if($row['tt'] == null || $row['tt'] == ''){ $style_tt = 'display: none;';}
        if($row['gestational_age'] == null || $row['gestational_age'] == ''){ $style_gestational_age = 'display: none;';}
        if($row['gestational_day'] == null || $row['gestational_day'] == ''){ $style_gestational_day = 'display: none;';}

        /* ประวัติด้านสูตินรีเวชกรรม OB-GYN ย่อมาจาก OBSTRETIC GYNECOLOGY สูติ-นรีเวชกรรม*/
        $style_OB_GYN = '';
        $style_last_child = '';
        $style_last_abort = '';
        $style_curette = '';
        $style_lmp = '';
        $style_edc = '';
        if(($row['last_child'] == null || $row['last_child'] == '')
        && ($row['last_abort'] == null || $row['last_abort'] == '')
        && ($row['curette'] == null || $row['curette'] == '')
        && ($row['lmp'] == null || $row['lmp'] == '')
        && ($row['edc'] == null || $row['edc'] == '')){
            $style_OB_GYN  = 'display: none;';
        }
        if($row['last_child'] == null || $row['last_child'] == ''){ $style_last_child = 'display: none;';}
        if($row['last_abort'] == null || $row['last_abort'] == ''){ $style_last_abort = 'display: none;';}
        if($row['curette'] == null || $row['curette'] == ''){ $style_curette = 'display: none;';}
        if($row['lmp'] == null || $row['lmp'] == ''){ $style_lmp = 'display: none;';}
        if($row['edc'] == null || $row['edc'] == ''){ $style_edc = 'display: none;';}

        /* อาการระหว่างตั้งครรภ์ */
        $textbox_condition_pregnant = '';
        $checkbox_condition_pregnant1 = '( )';
        $style_condition_pregnant = '';
        if ($row['condition_pregnant'] == null) {
            $style_condition_pregnant = 'display: none;';
        }
        if ($row['condition_pregnant'] == 'ปกติ') {$checkbox_condition_pregnant1 = '('.$image_check.')';}
        $checkbox_condition_pregnant2 = '( )';
        if ($row['condition_pregnant'] != 'ปกติ') {$checkbox_condition_pregnant2 = '('.$image_check.')';
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
        if(($row['hiv'] == null || $row['hiv'] == '') && ($row['vdrl'] == null || $row['vdrl'] == '')
        && ($row['hbs_ag'] == null || $row['hbs_ag'] == '') && ($row['hct'] == null || $row['hct'] == '')
        && ($row['hiv2'] == null || $row['hiv2'] == '') && ($row['vdrl2'] == null || $row['vdrl2'] == '')
        && ($row['hbs_ag2'] == null || $row['hbs_ag2'] == '') && ($row['hct2'] == null || $row['hct2'] == '')
        && ($row['gr'] == null || $row['gr'] == '')
        && ($row['thalassemia'] == null || $row['thalassemia'] == '')
        && ($row['husband'] == null || $row['husband'] == '')){
            $style_lab_result = 'display: none;';
        }
        if($row['hiv'] == null || $row['hiv'] == ''){ $style_hiv = 'display: none;';}
        if($row['vdrl'] == null || $row['vdrl'] == ''){ $style_vdrl = 'display: none;';}
        if($row['hbs_ag'] == null || $row['hbs_ag'] == ''){ $style_hbs_ag = 'display: none;';}
        if($row['hct'] == null || $row['hct'] == ''){ $style_hct = 'display: none;';}
        if($row['hiv2'] == null || $row['hiv2'] == ''){ $style_hiv2 = 'display: none;';}
        if($row['vdrl2'] == null || $row['vdrl2'] == ''){ $style_vdrl2 = 'display: none;';}
        if($row['hbs_ag2'] == null || $row['hbs_ag2'] == ''){ $style_hbs_ag2 = 'display: none;';}
        if($row['hct2'] == null || $row['hct2'] == ''){ $style_hct2 = 'display: none;';}
        if($row['gr'] == null || $row['gr'] == ''){ $style_gr = 'display: none;';}
        if($row['thalassemia'] == null || $row['thalassemia'] == ''){ $style_thalassemia = 'display: none;';}
        if($row['husband'] == null || $row['husband'] == ''){ $style_husband = 'display: none;';}

        /* วิธีคลอด */
        $textbox_deliver_anomalies = '';
        $checkbox_deliver_anomalies1 = '( )';
        $style_deliver_anomalies = '';
        if ($row['deliver_anomalies'] == null) {
            $style_deliver_anomalies = 'display: none;';
        }
        if ($row['deliver_anomalies'] == 'ปกติ') {$checkbox_deliver_anomalies1 = '('.$image_check.')';}
        $checkbox_deliver_anomalies2 = '( )';
        if ($row['deliver_anomalies'] != 'ปกติ') {$checkbox_deliver_anomalies2 = '('.$image_check.')';
            $textbox_deliver_anomalies = htmlspecialchars($row['deliver_anomalies']);
        }

        /* คลอดที่ */
        $style_data_deliver = '';
        $style_deliver_location = '';
        $style_deliver_first_weight = '';
        $style_deliver_first_health = '';
        if(($row['deliver_location'] == null ||$row['deliver_location'] == '')
        && ($row['deliver_first_weight'] == null || $row['deliver_first_weight'] == '')
        && ($row['deliver_first_health'] == null || $row['deliver_first_health'] == '')){
            $style_data_deliver = 'display: none;';
        }
        if($row['deliver_location'] == null || $row['deliver_location'] == ''){ $style_deliver_location = 'display: none;';}
        if($row['deliver_first_weight'] == null || $row['deliver_first_weight'] == ''){ $style_deliver_first_weight = 'display: none;';}
        if($row['deliver_first_health'] == null || $row['deliver_first_health'] == ''){ $style_deliver_first_health = 'display: none;';}

        /* การเลี้ยงทารก */
        $style_infant_feeding = '';
        if(($row['fant_breast_feeding_end_age_month'] == null)
        && (($row['fant_artificial_feeding_start_age_month'] == null))
        && ($row['fant_feeding_etc'] == null)){
            $style_infant_feeding = 'display: none;';
        }

        $checkbox_fant_breast = '( )';
        if ($row['fant_breast_feeding_end_age_month'] != null) {$checkbox_fant_breast = '('.$image_check.')';}
        $checkbox_fant_artificial = '( )';
        if ($row['fant_artificial_feeding_start_age_month'] != null) {$checkbox_fant_artificial = '('.$image_check.')';}
        $checkbox_fant_etc = '( )';
        if ($row['fant_feeding_etc'] != null) {$checkbox_fant_etc = '('.$image_check.')';}

        /* การให้อาหารเสริม */
        $style_supplementary_feeding = '';
        if($row['supplementary_feeding'] == null || $row['supplementary_feeding'] == '' || $row_ipt['age_y'] >  15){
            $style_supplementary_feeding = 'display: none;';
        }

        $textbox_supplementary_feeding = '';
        $checkbox_supplementary_feeding1 = '( )';
        if ($row['supplementary_feeding'] == 'ยังไม่ได้รับ') {$checkbox_supplementary_feeding1 = '('.$image_check.')';}
        $checkbox_supplementary_feeding2 = '( )';
        if ($row['supplementary_feeding'] == 'ได้รับ') {$checkbox_supplementary_feeding2 = '('.$image_check.')';
            $textbox_supplementary_feeding = htmlspecialchars($row['supplementary_feeding_start_age_month']);
        }

        /* ประวัติแพ้/โรคประจำตัว/ผ่าตัด */
        $textbox_operation_allergy = '';
        $checkbox_operation_allergy1 = '( )';
        if ($row['disease_operation_allergy'] == 'ไม่มี') {$checkbox_operation_allergy1 = '('.$image_check.')';}
        $checkbox_operation_allergy2 = '( )';
        if ($row['disease_operation_allergy'] != 'ไม่มี') {$checkbox_operation_allergy2 = '('.$image_check.')';
            $textbox_operation_allergy = htmlspecialchars($row['disease_operation_allergy']);
        }

        /* การเข้ารับการรักษาในโรงพยาบาล */
        $checkbox_inpatient_history1 = '( )';
        if ($row['inpatient_history'] == 'ไม่เคย') {$checkbox_inpatient_history1 = '('.$image_check.')';}
        $checkbox_inpatient_history2 = '( )';
        if ($row['inpatient_history'] == 'เคย') {$checkbox_inpatient_history2 = '('.$image_check.')';}

        /* ประวัติการคลอด */
        $style_brith_history = '';
        if(($row['pb_no'] == null)
        && ($row['giant_baby'] == null)
        && ($row['distocia'] == null)
        && ($row['extraction'] == null)
        && ($row['pph'] == null)
        && ($row['pb_etc'] == null)){
            $style_brith_history = 'display: none;';
        }

        $checkbox_pb_no   = '( )';
        if ($row['pb_no'] == 'Y') {$checkbox_pb_no = '('.$image_check.')';}
        $checkbox_giant_baby = '( )';
        if ($row['giant_baby'] == 'Y') {$checkbox_giant_baby = '('.$image_check.')';}
        $checkbox_distocia = '( )';
        if ($row['distocia'] == 'Y') {$checkbox_distocia = '('.$image_check.')';}
        $checkbox_extraction = '( )';
        $textbox_extraction = '';
        if ($row['extraction'] != null)
        {
            $checkbox_extraction = '('.$image_check.')';
            $textbox_extraction = htmlspecialchars($row['extraction']);
        }
        $checkbox_pph = '( )';
        if ($row['pph'] == 'Y') {$checkbox_pph = '('.$image_check.')';}
        $checkbox_pb_etc = '( )';
        $textbox_pb_etc = '';
        if ($row['pb_etc'] != null)
        {
            $checkbox_pb_etc = '('.$image_check.')';
            $textbox_pb_etc = htmlspecialchars($row['pb_etc']);
        }

        /* ตรวจหน้าท้อง */
        $style_high_of_fundus = '';
        $style_hf = '';
        $style_hf_position = '';
        if(($row['hf'] == null || $row['hf'] == '')
        && ($row['hf_position'] == null || $row['hf_position'] == '')){
            $style_high_of_fundus = 'display: none;';
        }
        if($row['hf'] == null || $row['hf'] == ''){ $style_hf = 'display: none;';}
        if($row['hf_position'] == null || $row['hf_position'] == ''){ $style_hf_position = 'display: none;';}

        $svg_tag = str_replace('<?xml version="1.0" encoding="UTF-8" standalone="no" ?>','',$row['svg_tag']);
        $svg_tag = str_replace('width="700" height="500"',' height="180"',$svg_tag); 




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
    <h2 style="text-align:center;font-size:11pt;">แบบบันทึกการรับใหม่ผู้ป่วยใน '.htmlspecialchars(DbConstant::HOSPITAL_NAME).'</h2>

    <table id="bg-table" width="100%" style="border-collapse: collapse;font-size:8pt;margin-top:2px;">
        <tr style="border:1px solid #000;margin: 45px;">
            <td  colspan="2" style="border-right:0.5px solid #000;padding:4px;"width="50%">&nbsp;
            <B>วันที่รับไว้รักษา</B>&nbsp;&nbsp;'.htmlspecialchars($row['receiver_medication_date']).' <B>&nbsp;  &nbsp;เวลา</B>&nbsp;&nbsp;'.htmlspecialchars($row['receiver_medication_time']).'&nbsp;น. </td>
            <td  colspan="1" width="50%" style="margin: 35px;padding:4px;">&nbsp;
                <B>เข้ารับการรักษาโดย</B>&nbsp;
                <label><B style="text-decoration: underline;"> '.$textbox_medication_3.' <B></label>
            </td>
        </tr>

        <tr style="border:1px solid #000;margin: 35px;">
        <td colspan="3" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:5px;text-align:left;">&nbsp;
            <B>ผู้ให้ข้อมูล</B>&nbsp;
            <B style="text-decoration: underline;"> '.$textbox_informant.' </B>&nbsp;</td>
    </tr>

        <tr style="border:1px solid #000;margin: 35px;">
            <td colspan="3" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;">&nbsp;
                <B>อาการสำคัญ</B><br>&emsp;&emsp;&emsp;&ensp;&nbsp;'.nl2br(htmlspecialchars($row['chief_complaints'])).'
            </td>
        </tr>

        <tr style="border:1px solid #000;margin: 35px;">
            <td colspan="3" width="100%" style="margin: 35px;padding:4px;">&nbsp;
                <B>ประวัติการเจ็บป่วยปัจจุบัน</B><br>&emsp;&emsp;&emsp;&ensp;&nbsp;'.nl2br(htmlspecialchars($row['medical_history'])).'
            </td>
        </tr>

        <tr style="border:1px solid #000;margin: 35px;">
            <td colspan="3" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;">&nbsp;
                <B>สัญญาณชีพแรกรับ</B><br>&nbsp;&nbsp;BP = &nbsp;'.htmlspecialchars($row_vs['sbp']).'/'.htmlspecialchars($row_vs['dbp']).'&nbsp;mmHg&nbsp;&nbsp;
                T = &nbsp;'.htmlspecialchars($row_vs['bt']).'&nbsp;*C&nbsp;&nbsp;&emsp; RR = '.htmlspecialchars($row_vs['rr']).'&nbsp;/min&nbsp;&nbsp;&nbsp;&nbsp;&emsp; PR = '.htmlspecialchars($row_vs['pr']).'&nbsp;/min&nbsp;&nbsp;
                &emsp;Neuro sign GCS &nbsp;&nbsp;'.htmlspecialchars($NeuroSignGCS).'&nbsp;&nbsp;&emsp;คะแนน
                &emsp;(&nbsp;E&nbsp;&nbsp;'.htmlspecialchars($row_vs['eye']).'&nbsp;&nbsp;V&nbsp;&nbsp;'.htmlspecialchars($row_vs['verbal']).'&nbsp;&nbsp;M&nbsp;&nbsp;'.htmlspecialchars($row_vs['movement']).'&nbsp;&nbsp;)
                &emsp;Braden Scale &nbsp;&nbsp;'.htmlspecialchars($row_vs['braden']).'&nbsp;&nbsp;&emsp;คะแนน<br>
                &nbsp;&nbsp;น้ำหนัก&emsp;'.htmlspecialchars($row_ipt['latest_bw']).'&emsp;Kg&emsp;ส่วนสูง&emsp;'.htmlspecialchars($row_ipt['latest_height']).'&emsp;cm.
            </td>
        </tr>

        <tr style="border:1px solid #000;margin: 35px;">
            <td colspan="3" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:5px;text-align:center;">&nbsp;
                <B>ประวัติการเจ็บป่วยในอดีต</B>
            </td>
        </tr>

        <tr style="border:1px solid #000;margin: 35px;">
            <td colspan="3" width="50%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;">
                โรคประจำตัว
                <label>'.$checkbox_disease_1.' ไม่มี</label>
                <label>'.$checkbox_disease_2.' มี (ระบุ)</label>
                '.$disease_top.'
                '.$disease_detail.'
                <br><br>
                การผ่าตัด
                <label>&nbsp;&nbsp;&nbsp;&nbsp;'.$checkbox_operation_history1.' ไม่มี</label>
                <label>'.$checkbox_operation_history2.' มี (ระบุ)<br>
                '.$operation_text.'
                &nbsp;&nbsp;&ensp;&nbsp;&nbsp;&ensp;&nbsp;&nbsp;&ensp;
                &nbsp;&nbsp;&ensp;&nbsp;&nbsp;&ensp;&nbsp;&nbsp;&ensp;
                &nbsp;&nbsp;&ensp;&nbsp;&nbsp;'.nl2br($textbox_operation_history).'
                <br>
                ประวัติการแพ้ยาและการแพ้อื่นๆ &nbsp;&ensp;
                <label>'.$checkbox_allergy_1.' ไม่มี</label>
                <label>'.$checkbox_allergy_2.' มี (ระบุ)</label>
                &nbsp;&nbsp;&ensp;&nbsp;<font color="red">'.$row['allergy_drug_history_hosxp'].'</font>
                '.$allergy_top.'
                '.$allergy_drug_detail.'
                '.$allergy_food_detail.'
                '.$allergy_etc_detail.'
                <br><br>
                ประวัติการเจ็บป่วยในครอบครัว&nbsp;
                <label>'.$family_medical_history_1.' ไม่มี</label>
                <label>'.$family_medical_history_2.' มี (ระบุ)</label>
                '.$family_medical_top.'
                '.$family_medical_detail.'
                <br>
                <p style="'.$style_occupation.'"><br>
                    อาชีพ&nbsp;&nbsp;
                    '.htmlspecialchars($occupation).'
                </p>
                <p style="'.$style_risk .'"><br>
                    พฤติกรรมเสี่ยง&nbsp;
                    '.$no_risk.'&nbsp;ปฏิเสธ&nbsp;<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    '.$smoking.'&nbsp;สูบบุหรี่
                    &nbsp;'.$smoking_detail.'<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    '.$alcohol.'&nbsp;ดื่มสุรา
                    &nbsp;'.$alcohol_detail.'<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    '.$medication_used.'&nbsp;ยา (ระบุ)&nbsp;'.$medication_used_detail.'
                </p>
                <p style="'.$style_period.'"><br>
                    ประจำเดือน&nbsp;&nbsp;
                    '.$period_1.'&nbsp;ยังไม่มี<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    '.$period_2.'&nbsp;มี&nbsp;<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    '.$period_normal_1.'&nbsp;ปกติ&nbsp;
                    '.$period_normal_2.'&nbsp;ผิดปกติ&nbsp;'.htmlspecialchars($period_disorders).'
                    '.$period_normal_3.'&nbsp;LMP&nbsp;'.htmlspecialchars($period_lmp).'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    '.$period_3.'&nbsp;หมดประจำเดือน เมื่ออายุ&nbsp;&nbsp;'.htmlspecialchars($period_menopause).'  ปี
                </p>
                <p style="'.$style_receives_immunisation_history_kid.'"><br>
                    ประวัติการได้รับภูมิคุ้มกัน (เฉพาะเด็ก)&nbsp;&nbsp;
                    <label>'.$checkbox_receives_immunisation_history_kid1.' ครบตามวัย</label>
                    <label>'.$checkbox_receives_immunisation_history_kid2.' ไม่ครบ (ระบุ)</label>
                    &nbsp;&nbsp;&ensp;&nbsp;&nbsp;&ensp;'.$textbox_receives_immunisation_history_kid.'
                </p>
                <p style="'.$style_developmentally_kid.'"><br>
                    การพัฒนาการ (เฉพาะเด็ก)&nbsp;
                    <label>'.$checkbox_developmentally_kid1.' ปกติ</label>
                    <label>'.$checkbox_developmentally_kid2.' ผิดปกติ (ระบุ)</label>
                    &nbsp;&nbsp;&ensp;&nbsp;&nbsp;&ensp;'.$textbox_developmentally_kid.'
                </p>
                <p style="'.$style_mother.'"><br>
                    มารดา
                    <var style="'.$style_g.'"> G  '.htmlspecialchars($row['g']).'  </var>
                    <var style="'.$style_p.'"> P  '.htmlspecialchars($row['p']).' </var>
                    <var style="'.$style_anc.'"> ANC ที่  '.htmlspecialchars($row['anc']).'  </var>
                    <var style="'.$style_tt.'"> ได้ TT   '.htmlspecialchars($row['tt']).'  เข็ม</var>
                    <var style="'.$style_gestational_age.'"> อายุครรภ์  '.htmlspecialchars($row['gestational_age']).'</var>
                    <var style="'.$style_gestational_day.'"> สัปดาห์   '.htmlspecialchars($row['gestational_day']).' วัน</var>
                </p>
                <p style="'.$style_OB_GYN.'"><br>
                    <var style="'.$style_last_child.'">last child   '.htmlspecialchars($row['last_child']).'  ปี</var>
                    <var style="'.$style_last_abort.'"> last abort   '.htmlspecialchars($row['last_abort']).'  เดือน/ปี</var>
                    <var style="'.$style_curette.'"> ประวัติการขูดมดลูก   '.htmlspecialchars($row['curette']).'  </var>
                    <var style="'.$style_lmp.'"> ประจําเดือนครั้งสุดท้าย   '.htmlspecialchars($row['lmp']).'  </var>
                    <var style="'.$style_edc.'"> กําหนดการคลอด   '.htmlspecialchars($row['edc']).'  </var>
                </p>
                <p style="'. $style_brith_history.'"><br>
                    ประวัติการคลอด&nbsp;&nbsp;
                    <label>'.$checkbox_pb_no.' ปฎิเสธ</label>
                    <label>'.$checkbox_giant_baby.' เคยคลอดบุตร นน. > 4000 กรัม</label>
                    <label>'.$checkbox_distocia.' มีประวัติคลอดยาก</label>
                    <label>'.$checkbox_extraction.' มีประวัติคลอดหัตถการ (ระบุ)</label>
                    &ensp;'.$textbox_extraction.'
                    <br>&ensp;&ensp;&nbsp;&nbsp;&ensp;&nbsp;&nbsp;&ensp;&ensp;&nbsp;&nbsp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;
                    <label>'.$checkbox_pph.' มีประวัติตกเลือดหลังคลอด</label>
                    &nbsp;&nbsp;&ensp;&nbsp;&nbsp;&ensp;
                    <label>'.$checkbox_pb_etc.' อื่นๆ</label>
                    &ensp;'.$textbox_pb_etc.'
                </p>
                <p style="'.$style_high_of_fundus.'"><br>
                    ตรวจหน้าท้อง
                    <var style="'.$style_hf.'"> high of fundus  '.htmlspecialchars($row['hf']).'  </var>
                    <var style="'.$style_hf_position.'"> position  '.htmlspecialchars($row['hf_position']).' </var>
                </p>
                <p style="'.$style_condition_pregnant.'"><br>
                    อาการระหว่างตั้งครรภ์&nbsp;&nbsp;
                    <label>'.$checkbox_condition_pregnant1.' ปกติ</label>
                    <label>'.$checkbox_condition_pregnant2.' ผิดปกติ (ระบุ)</label>
                    &nbsp;&nbsp;&ensp;&nbsp;&nbsp;&ensp;'.nl2br($textbox_condition_pregnant).'
                </p>
                <p style="'.$style_lab_result.'"><br>
                    &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&nbsp;
                    <var style="'.$style_hiv.'"> HIV   '.htmlspecialchars($row['hiv']).'  </var>
                    <var style="'.$style_vdrl.'"> VDRL   '.htmlspecialchars($row['vdrl']).'  </var>
                    <var style="'.$style_hbs_ag.'"> HBsAg   '.htmlspecialchars($row['hbs_ag']).'  </var>
                    <var style="'.$style_hct.'"> HCT   '.htmlspecialchars($row['hct']).'  </var>
                    <br>&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&nbsp;
                    <var style="'.$style_hiv2.'"> HIV   '.htmlspecialchars($row['hiv2']).'  </var>
                    <var style="'.$style_vdrl2.'"> VDRL   '.htmlspecialchars($row['vdrl2']).'  </var>
                    <var style="'.$style_hbs_ag2.'"> HBsAg   '.htmlspecialchars($row['hbs_ag2']).'  </var>
                    <var style="'.$style_hct2.'"> HCT   '.htmlspecialchars($row['hct2']).'  </var>
                    <br>&nbsp;&nbsp;&ensp;&nbsp;&nbsp;&ensp;&nbsp;&nbsp;&ensp;&nbsp;&nbsp;&ensp;
                    &nbsp;&nbsp;&ensp;&nbsp;&nbsp;&ensp;
                    <var style="'.$style_gr.'"> Bllod group    '.htmlspecialchars($row['gr']).'  </var>
                    <var style="'.$style_thalassemia.'"> ผล thalassemia ตัวเอง    '.htmlspecialchars($row['thalassemia']).'  </var>
                    <var style="'.$style_husband.'"> ผล thalassemia สามี    '.htmlspecialchars($row['husband']).'  </var>
                </P>
                <p style="'.$style_deliver_anomalies.'"><br>
                    วิธีคลอด&nbsp;
                    <label>'.$checkbox_deliver_anomalies1.' ปกติ</label>
                    <label>'.$checkbox_deliver_anomalies2.' ผิดปกติ (ระบุ)</label>
                    &nbsp;&nbsp;&ensp;&nbsp;&nbsp;&ensp;'.$textbox_deliver_anomalies.'
                    <label>เนื่องจาก '.htmlspecialchars($row['deliver_anomalies_means']).' </label>
                </p>
                <p style="'.$style_data_deliver.'"><br>
                    <var style="'.$style_deliver_location.'">คลอดที่  '.htmlspecialchars($row['deliver_location']).'     </var>
                    <var style="'.$style_deliver_first_weight.'">น้ำหนักแรกคลอด '.htmlspecialchars($row['deliver_first_weight']).'     กรัม    </var>
                    <var style="'.$style_deliver_first_health.'">สุขภาพแรกเกิด '.htmlspecialchars($row['deliver_first_health']).' </var>
                </p>
                <p style="'.$style_infant_feeding.'"><br>
                    การเลี้ยงทารก&nbsp;
                    <label>'.$checkbox_fant_breast.' นมมารดา ถึงอายุ    '.htmlspecialchars($row['fant_breast_feeding_end_age_month']).' เดือน    </label>
                    <label>'.$checkbox_fant_artificial.' นมผสม เริ่มอายุ    '.htmlspecialchars($row['fant_artificial_feeding_start_age_month']).' เดือน    </label>
                    <label>'.$checkbox_fant_etc.' อื่นๆ    '.htmlspecialchars($row['fant_feeding_etc']).' เดือน    </label>
                </p>
                <p style="'.$style_supplementary_feeding.'"><br>
                    การให้อาหารเสริม&nbsp;&nbsp;
                    <label>'.$checkbox_supplementary_feeding1.' ยังไม่ได้รับ</label>
                    <label>'.$checkbox_supplementary_feeding2.' ได้รับ เริ่มอายุ '.$textbox_supplementary_feeding.' เดือน    </label>
                </p>
                <p><br>
                    การเข้ารับการรักษาในโรงพยาบาล&nbsp;
                    <label>'.$checkbox_inpatient_history1.' ไม่เคย</label>
                    <label>'.$checkbox_inpatient_history2.' เคย</label>
                    <label> ครั้งสุดท้ายเมื่อ '.htmlspecialchars($row['inpatient_last_date']).' รพ '.htmlspecialchars($row['inpatient_location']).' เนื่องจาก '.htmlspecialchars($row['inpatient_because']).' </label>
                </p>

                <p><br>
                Review Of System&nbsp;
                <label>'.$review_system1.' ปกติ</label>
                <label>'.$review_system2.' ผิดปกติ(ระบุ)</label>
                '.$textbox_review_system.'
                </p>
            </td>
        </tr>

        <tr style="border:1px solid #000;margin: 35px;">
            <td colspan="3" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;text-align:center;">&nbsp;
                Physical examination
            </td>
        </tr>

        <tr style="border:1px solid #000;margin: 35px;">
            <td colspan="2" width="50%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;">&nbsp;
            <br>&nbsp;
                General : '.htmlspecialchars($row['pe_general']).'
                <br>&nbsp;
                Skin      : '.htmlspecialchars($row['pe_skin']).'
                <br>&nbsp;
                HEENT  : '.htmlspecialchars($row['pe_heent']).'
                <br>&nbsp;
                Lungs : '.htmlspecialchars($row['pe_lungs']).'
                <br>&nbsp;
                CVS : '.htmlspecialchars($row['pe_cvs']).'
                <br>&nbsp;
                Abdomen : '.htmlspecialchars($row['pe_abdomen']).'
                <br>&nbsp;
                Rectal & Genitalia : '.htmlspecialchars($row['pe_rectalgenitalia']).'
                <br>&nbsp;
                Extremities     : '.htmlspecialchars($row['pe_extremities']).'
                <br>&nbsp;
                CNS  : '.htmlspecialchars($row['pe_cns']).'
                <br>&nbsp;
                OB/Gyn exam : '.htmlspecialchars($row['pe_ob_gynexam']).'
                <br>&nbsp;
                Other : '.htmlspecialchars($row['pe_other']).'
                <br>&nbsp;
                PE Text : '.htmlspecialchars($row['pe_text']).'
                <br>&nbsp;
            </td>
            <td  width="50%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;text-align:center;" id="show_img_select">&nbsp;
                '.$svg_tag.'
            </td>
        </tr>

        <tr style="border:1px solid #000;margin: 35px;">
            <td colspan="2" width="50%" style="margin: 35px;padding:4px;">&nbsp;
                Problem List :&nbsp;'.htmlspecialchars($row['problem_list']).'<br>&nbsp;
                Impression :&nbsp;'.htmlspecialchars($row['impression']).'<br>&nbsp;  
                Plan Management :&nbsp;'.htmlspecialchars($row['plan_management']).'<br>
            </td>
            <td width="50%" style="border-left:0.5px solid #000;margin: 35px;padding:4px;">
                แพทย์ผู้บันทึก  <br>'.$admission_note_doctorString.'
                พยาบาลผู้บันทึก '.htmlspecialchars($row['nurse_name']).'  '.htmlspecialchars($row['nurse_pos']).'
            </td>
        </tr>
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
';
$mpdf->WriteHTML($head);
$mpdf->Output();
?>