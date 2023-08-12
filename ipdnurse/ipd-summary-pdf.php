<?php
//require_once __DIR__ . '\vendor\autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require_once '../include/DbUtils.php';
require_once '../include/Session.php';
require_once '../include/KphisQueryUtils.php';

date_default_timezone_set('asia/bangkok');
$conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
//SessionManager::checkLoginSessionAndShowMessage(); //เช็ค session

/*if(!(
    // && SessionManager::checkPermission('IPD_NURSE_MAIN_PROGRAM','ACCESS')
    // && SessionManager::checkPermission('IPD_DISCHARGE_SUMMARY','ADD')
    // && SessionManager::checkPermission('IPD_DISCHARGE_SUMMARY','EDIT')
    Session::checkPermission('IPD_DISCHARGE_SUMMARY','VIEW')
    // && SessionManager::checkPermission('IPD_DISCHARGE_SUMMARY','REMOVE')
    )){
    return;
}
*/
$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
$mpdf->AddPageByArray([
    'margin-left' => 8,
    'margin-right' => 8,
    'margin-top' => 2,
    'margin-bottom' => 10,
]);

$an_REQUEST = $_REQUEST['an'];//รับค่า an
$hn_REQUEST = KphisQueryUtils::getHnByAn($an_REQUEST);// function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา
$query_parameters_REQUEST = ['an'=>$an_REQUEST,];

$name_session = $_SESSION['name'];
$sql_ipt = "SELECT ipt.an, ipt.hn as ipt_hn,patient.pname,patient.fname,patient.lname,patient.drugallergy,
    patient.cid,patient.birthday,
    patient.informaddr,patient.informname,patient.informrelation,patient.informtel,

    -- an_stat.age_y,an_stat.age_m,an_stat.age_d,
    timestampdiff(year,patient.birthday,ipt.regdate) as age_y,
    timestampdiff(month,patient.birthday,ipt.regdate)-(timestampdiff(year,patient.birthday,ipt.regdate)*12) as age_m,
    timestampdiff(day,date_add(patient.birthday,interval (timestampdiff(month,patient.birthday,ipt.regdate)) month),ipt.regdate) as age_d,

    an_stat.admdate,
    ipt.regdate,ipt.regtime,ipt.ward,
    ipt.dchdate,ipt.dchtime,
    ipt.pttype,
    ipt.gravidity,ipt.parity,ipt.living_children,
    pttype.`name` as pttype_name,
    ward.name ward_name, ward.shortname, spclty.`name` as spclty_name,
    occupation.`name` as occupation_name,
    sex.`name` as sex_name,
    religion.`name` as religion_name,
    nationality.`name` as citizenship_name,
    nationality2.`name` as nationality_name,
    marrystatus.`name` as marrystatus_name,
    ipd_summary.*,
    opduser.name as user_create_user, opduser.entryposition,
    doctor.licenseno,
    ipt_newborn.birth_weight,
    ipt.bw
    FROM ".DbConstant::HOSXP_DBNAME.".ipt
    LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".an_stat ON an_stat.an=ipt.an
    LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".patient ON patient.hn=ipt.hn
    LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".ward ON ward.ward=ipt.ward
    LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".pttype ON pttype.pttype = ipt.pttype
    LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".occupation ON occupation.occupation = patient.occupation
    LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".sex ON sex.code = patient.sex
    LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".religion ON religion.religion = patient.religion
    LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".nationality ON nationality.nationality = patient.citizenship
    LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".nationality as nationality2  ON nationality2.nationality = patient.nationality
    LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".marrystatus ON marrystatus.code = patient.marrystatus
    LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".spclty ON spclty.spclty = an_stat.spclty
    LEFT OUTER JOIN ".DbConstant::KPHIS_DBNAME.".ipd_summary ON ipd_summary.an = an_stat.an
    LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".opduser  on opduser.loginname = ipd_summary.create_user
    LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".doctor ON doctor.code =  opduser.doctorcode AND doctor.licenseno != '-99999'
    LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".ipt_newborn ON ipt_newborn.an = ipd_summary.an
    WHERE ipt.an=:an ORDER BY summary_id DESC";
    $stmt_ipt = $conn->prepare($sql_ipt);
    $stmt_ipt->execute($query_parameters_REQUEST);
    $row_iptCount = 0;
    if ($row_ipt = $stmt_ipt->fetch()){
        $an_row_ipt = $row_ipt['an'];
        $hn_row_ipt = $row_ipt['ipt_hn'];

        $pname_row_ipt = $row_ipt['pname'];
        $fname_row_ipt = $row_ipt['fname'];
        $lname_row_ipt = $row_ipt['lname'];
        $fullname_row_ipt = $pname_row_ipt.$fname_row_ipt.' '.$lname_row_ipt;//คำนำหน้า ชื่อ-สกุล

        $age_y_row_ipt = $row_ipt['age_y'];
        $age_m_row_ipt = $row_ipt['age_m'];
        $age_d_row_ipt = $row_ipt['age_d'];
        $admdate_row_ipt = $row_ipt['admdate'];//จำนวนวันที่ Admit

        $pttype_name_row_ipt = $row_ipt['pttype_name'];
        $cid_row_ipt = $row_ipt['cid'];
        $ward_name_row_ipt = $row_ipt['ward_name'];
        $shortname_row_ipt = $row_ipt['shortname'];
        $spclty_name_row_ipt = $row_ipt['spclty_name'];

        $informaddr_row_ipt = $row_ipt['informaddr'];
        $informname_row_ipt = $row_ipt['informname'];
        $informrelation_row_ipt = $row_ipt['informrelation'];
        $informtel_row_ipt = $row_ipt['informtel'];

        $occupation_name_row_ipt = $row_ipt['occupation_name'];
        $sex_name_row_ipt = $row_ipt['sex_name'];
        $religion_name_row_ipt = $row_ipt['religion_name'];
        $citizenship_name_row_ipt = $row_ipt['citizenship_name'];
        $nationality_name_row_ipt = $row_ipt['nationality_name'];

        $marrystatus_name_row_ipt = $row_ipt['marrystatus_name'];

        $user_create_user = $row_ipt['user_create_user'];
        $entryposition = $row_ipt['entryposition'];

        function DateThai($datetime)
        {
                $strYear = date("Y",strtotime($datetime))+543;
                $strMonth= date("n",strtotime($datetime));
                $strDay= date("j",strtotime($datetime));
                $strMonthCut = Array("","มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน","กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม");
                $strMonthThai=$strMonthCut[$strMonth];
                return "$strDay"." "."$strMonthThai"." "."$strYear";
        }

        $birthday = $row_ipt['birthday'];
        $birthday_now = date($birthday);
        $birthday_datethai = DateThai($birthday_now);

        $regdate = $row_ipt['regdate'];
        $regdate_now = date($regdate);
        $regdate_datethai = DateThai($regdate_now);

        $regtime_row_ipt = $row_ipt['regtime'];

        $dchdate_datethai = "";
        $dchdate = $row_ipt['dchdate'];
        if($dchdate != "" || $dchdate != null){
                $dchdate_now = date($dchdate);
                $dchdate_datethai = DateThai($dchdate_now);
        }

        $dchtime_row_ipt = $row_ipt['dchtime'];

        $gravidity_row_ipt = $row_ipt['gravidity'];
        $parity_row_ipt = $row_ipt['parity'];
        $living_children_row_ipt = $row_ipt['living_children'];
        $licenseno = $row_ipt['licenseno'];//เลข ว แพทย์
        $birth_weight = $row_ipt['birth_weight'];//น้ำหนัก

        //เป็นการแสดงผล ipd_summary
        $principal_diagnosis = $row_ipt['principal_diagnosis'];
        $pre_admission_comorbidity = $row_ipt['pre_admission_comorbidity'];
        $post_admission_comorbidity = $row_ipt['post_admission_comorbidity'];
        $other_diagnosis = $row_ipt['other_diagnosis'];
        $external_cause = $row_ipt['external_cause'];
        $operating_room = $row_ipt['operating_room'];

        $image_check = "<img src='../include/images/check-mark.png' width='1.2%'>";

        $tracheostomy = $row_ipt['tracheostomy'];
        $tracheostomy_check = "&nbsp;&nbsp;&nbsp;";
        if($tracheostomy == "Y"){$tracheostomy_check = $image_check;}

        $mechanical_ventilation = $row_ipt['mechanical_ventilation'];
        $mechanical_ventilation_check = "&nbsp;&nbsp;&nbsp;";
        if($mechanical_ventilation == "Y"){$mechanical_ventilation_check = $image_check;}

        $mechanical_ventilation1 = $row_ipt['mechanical_ventilation1'];
        $mechanical_ventilation1_check = "&nbsp;&nbsp;&nbsp;";
        if($mechanical_ventilation1 == "Y"){$mechanical_ventilation1_check = $image_check;}

        $mechanical_ventilation2 = $row_ipt['mechanical_ventilation2'];
        $mechanical_ventilation2_check = "&nbsp;&nbsp;&nbsp;";
        if($mechanical_ventilation2 == "Y"){$mechanical_ventilation2_check = $image_check;}

        $packed_redcells = $row_ipt['packed_redcells'];
        $packed_redcells_check = "&nbsp;&nbsp;&nbsp;";
        if($packed_redcells == "Y"){$packed_redcells_check = $image_check;}

        $fresh_frozen_plasma = $row_ipt['fresh_frozen_plasma'];
        $fresh_frozen_plasma_check = "&nbsp;&nbsp;&nbsp;";
        if($fresh_frozen_plasma == "Y"){$fresh_frozen_plasma_check = $image_check;}

        $platelets = $row_ipt['platelets'];
        $platelets_check = "&nbsp;&nbsp;&nbsp;";
        if($platelets == "Y"){$platelets_check = $image_check;}

        $cryoprecipitate = $row_ipt['cryoprecipitate'];
        $cryoprecipitate_check = "&nbsp;&nbsp;&nbsp;";
        if($cryoprecipitate == "Y"){$cryoprecipitate_check = $image_check;}

        $whole_blood = $row_ipt['whole_blood'];
        $whole_blood_check = "&nbsp;&nbsp;&nbsp;";
        if($whole_blood == "Y"){$whole_blood_check = $image_check;}

        $computer_tomography = $row_ipt['computer_tomography'];
        $computer_tomography_check = "&nbsp;&nbsp;&nbsp;";
        if($computer_tomography == "Y"){$computer_tomography_check = $image_check;}

        $computer_tomography_text = $row_ipt['computer_tomography_text'];

        $chemotherapy = $row_ipt['chemotherapy'];
        $chemotherapy_check = "&nbsp;&nbsp;&nbsp;";
        if($chemotherapy == "Y"){$chemotherapy_check = $image_check;}

        $mri = $row_ipt['mri'];
        $mri_check = "&nbsp;&nbsp;&nbsp;";
        if($mri == "Y"){$mri_check = $image_check;}

        $hemodialysis = $row_ipt['hemodialysis'];
        $hemodialysis_check = "&nbsp;&nbsp;&nbsp;";
        if($hemodialysis == "Y"){$hemodialysis_check = $image_check;}

        $non_or_other = $row_ipt['non_or_other'];
        $non_or_other_check = "&nbsp;&nbsp;&nbsp;";
        if($non_or_other == "Y"){$non_or_other_check = $image_check;}

        $non_or_other_text = $row_ipt['non_or_other_text'];

        $discharge_status = $row_ipt['discharge_status'];//discharge_status
        $discharge_status01_check = "&nbsp;&nbsp;&nbsp;";
        $discharge_status02_check = "&nbsp;&nbsp;&nbsp;";
        $discharge_status03_check = "&nbsp;&nbsp;&nbsp;";
        $discharge_status04_check = "&nbsp;&nbsp;&nbsp;";
        $discharge_status05_check = "&nbsp;&nbsp;&nbsp;";
        $discharge_status06_check = "&nbsp;&nbsp;&nbsp;";
        $discharge_status07_check = "&nbsp;&nbsp;&nbsp;";
        $discharge_status09_check = "&nbsp;&nbsp;&nbsp;";
        if($discharge_status == "01"){$discharge_status01_check = $image_check;}
        else if($discharge_status == "02"){$discharge_status02_check = $image_check;}
        else if($discharge_status == "03"){$discharge_status03_check = $image_check;}
        else if($discharge_status == "04"){$discharge_status04_check = $image_check;}
        else if($discharge_status == "05"){$discharge_status05_check = $image_check;}
        else if($discharge_status == "06"){$discharge_status06_check = $image_check;}
        else if($discharge_status == "07"){$discharge_status07_check = $image_check;}
        else if($discharge_status == "09"){$discharge_status09_check = $image_check;}

        $discharge_type = $row_ipt['discharge_type'];//discharge_type
        $discharge_type01_check = "&nbsp;&nbsp;&nbsp;";
        $discharge_type02_check = "&nbsp;&nbsp;&nbsp;";
        $discharge_type03_check = "&nbsp;&nbsp;&nbsp;";
        $discharge_type04_check = "&nbsp;&nbsp;&nbsp;";
        $discharge_type05_check = "&nbsp;&nbsp;&nbsp;";
        $discharge_type08_check = "&nbsp;&nbsp;&nbsp;";
        $discharge_type09_check = "&nbsp;&nbsp;&nbsp;";
        if($discharge_type == "01"){$discharge_type01_check = $image_check;}
        else if($discharge_type == "02"){$discharge_type02_check = $image_check;}
        else if($discharge_type == "03"){$discharge_type03_check = $image_check;}
        else if($discharge_type == "04"){$discharge_type04_check = $image_check;}
        else if($discharge_type == "05"){$discharge_type05_check = $image_check;}
        else if($discharge_type == "08"){$discharge_type08_check = $image_check;}
        else if($discharge_type == "09"){$discharge_type09_check = $image_check;}

        $hospital_refer = $row_ipt['hospital_refer'];
        $bw = '';
        if(($age_y_row_ipt < 1) || ($age_y_row_ipt == 1 && $age_m_row_ipt == 0 && $age_d_row_ipt == 0)){
            $bw = $row_ipt['bw'];
        }
    }

$head = '
    <style>
    body{
        font-family: "Garuda";//เรียกใช้font Garuda สำหรับแสดงผล ภาษาไทย
    }

    footer {
        position: fixed;
        bottom: -50px;
        left: 0px;
        right: 0px;
        height: 30px;

        /** Extra personal styles **/
        line-height: 35px;
    }
    </style>

    <table id="bg-table" width="100%" style="border-collapse: collapse;font-size:6pt;margin-top:8px;">
    <tr style="border:0px solid #000;margin: 45px;">
    <td style="border-right:0px solid #000;padding:4px; text-align:left; font-size:9pt;" colspan="6" valign="bottom">MINISTRY OF PUBLIC HEALTH<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;THAILAND</td>
    <td style="border-right:0px solid #000;padding:4px; text-align:center;" colspan="2" valign="bottom"><img src="../include/images/summary.jpg" width="15%" /></td>
    <td style="border-right:0px solid #000;padding:4px; text-align:right; font-size:9pt;" colspan="2" valign="bottom"><b>แบบ รง. 501</b><br><br>'.htmlspecialchars(DbConstant::HOSPITAL_NAME).'<br>IN-PATIENT SUMMARY</td>
    </tr>
    <tr style="border:1px solid #000;margin: 45px;">
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="10"><B>&nbsp;สิทธิการรักษา&nbsp;&nbsp;&nbsp;'.htmlspecialchars($pttype_name_row_ipt).'</B></td>
    </tr>
    <tr style="border:1px solid #000;margin: 45px;">
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="5"fullname_row_ipt>&nbsp;3 HOSPITAL NUMBER<br><div style="font-size:10pt;"><B>&nbsp;&nbsp;'.htmlspecialchars($hn_row_ipt).'</B></div></td>
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="4" valign="top">&nbsp;2 ID. NO เลขบัตรประจำตัวประชาชน<br><div style="font-size:9pt;"><B>&nbsp;&nbsp;'.htmlspecialchars($cid_row_ipt).'</B></div></td>
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="1" valign="top">&nbsp;1 ADMISSION NUMBER<br><div style="font-size:9pt;"><B>&nbsp;&nbsp;'.htmlspecialchars($an_REQUEST).'</B></div></td>
    </tr>
    <tr style="border:1px solid #000;margin: 45px;">
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="7" valign="top">&nbsp;4 PATIENT NAME<br><div style="font-size:9pt;"><B>&nbsp;&nbsp;'.htmlspecialchars($fullname_row_ipt).'</B></div></td>
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="3" valign="top">&nbsp;5 PATIENT ADDRESS &nbsp;&nbsp;&nbsp;&nbsp;TEL. '.htmlspecialchars($informtel_row_ipt).'<br><div style="font-size:8pt;"><B>&nbsp;&nbsp;'.htmlspecialchars($informaddr_row_ipt).'</B></div></td>
    </tr>
    </tr>
    <tr style="border:1px solid #000;margin: 45px;">
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="7" rowspan="2" valign="top">&nbsp;6 PERSON TO BE NOTIFIED<br>
        <div style="font-size:6pt;">&nbsp;&nbsp;NAME&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<B>'.htmlspecialchars($informname_row_ipt).' Relation Type : '.htmlspecialchars($informrelation_row_ipt).'</B></div>
        <div style="font-size:6pt;">&nbsp;&nbsp;ADDRESS&nbsp;&nbsp;<B>'.htmlspecialchars($informaddr_row_ipt).'</B></div>
    </td>
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="1">&nbsp;7 ETHNIC GROUP</td>
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="1">&nbsp;NATIONALITY</td>
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="1">&nbsp;RELIGION</td>
    </tr>
    <tr style="border:1px solid #000;margin: 45px;">
    <td style="border-right:0.5px solid #000;padding:4px; font-size:9pt;" colspan="1">&nbsp;<B>'.htmlspecialchars($citizenship_name_row_ipt).'</B></td>
    <td style="border-right:0.5px solid #000;padding:4px; font-size:9pt;" colspan="1">&nbsp;<B>'.htmlspecialchars($nationality_name_row_ipt).'</B></td>
    <td style="border-right:0.5px solid #000;padding:4px; font-size:9pt;" colspan="1">&nbsp;<B>'.htmlspecialchars($religion_name_row_ipt).'</B></td>
    </tr>
    <tr style="border:1px solid #000;margin: 45px;">
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="3" valign="top">&nbsp;8 SEX<br><div style="font-size:8pt;"><B>&nbsp;&nbsp;&nbsp;&nbsp;'.htmlspecialchars($sex_name_row_ipt).'</B></div></td>
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="2" valign="top">&nbsp;9 MARRY STATUS<br><div style="font-size:8pt;"><B>&nbsp;&nbsp;&nbsp;&nbsp;'.htmlspecialchars($marrystatus_name_row_ipt).'</B></div></td>
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="5" valign="top">&nbsp;10 OCCUPATION<br><div style="font-size:8pt;"><B>&nbsp;&nbsp;&nbsp;&nbsp;'.htmlspecialchars($occupation_name_row_ipt).'</B></div></td>
    </tr>
    <tr style="border:1px solid #000;margin: 45px;">
    <td style="border-right:0.5px solid #000;padding:4px; width:83;" colspan="2" valign="top">11 DATE OF BIRTH</td>
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="3" valign="top">&nbsp;12 AGE AT ADMISSION</td>
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="1" valign="top">&nbsp;13 GRAVIDITY</td>
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="1" valign="top">&nbsp;14 PARITY</td>
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="1" valign="top">&nbsp;15 LIVING CHILDREN</td>
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="1" valign="top">&nbsp;16 CONDITION CHILD AT BIRTH</td>
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="1" valign="top">&nbsp;17 BIRTHWEIGHT</td>
    </tr>
    <tr style="border:1px solid #000;margin: 45px;">
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="2" rowspan="2"><div style="font-size:6.8pt;"><B>'.htmlspecialchars($birthday_datethai).'</B></div></td>
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="1"><div style="font-size:9pt;"><B>&nbsp;&nbsp;'.htmlspecialchars($age_y_row_ipt).'</B></div></td>
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="1"><div style="font-size:9pt;"><B>&nbsp;&nbsp;'.htmlspecialchars($age_m_row_ipt).'</B></div></td>
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="1"><div style="font-size:9pt;"><B>&nbsp;&nbsp;'.htmlspecialchars($age_d_row_ipt).'</B></div></td>
    <td style="border-right:0.5px solid #000;padding:4px; text-align:center;" colspan="1" rowspan="2"><div style="font-size:9pt;"><B>'.htmlspecialchars($gravidity_row_ipt).'</B></div></td>
    <td style="border-right:0.5px solid #000;padding:4px; text-align:center;" colspan="1" rowspan="2"><div style="font-size:9pt;"><B>'.htmlspecialchars($parity_row_ipt).'</B></div></td>
    <td style="border-right:0.5px solid #000;padding:4px; text-align:center;" colspan="1" rowspan="2"><div style="font-size:9pt;"><B>'.htmlspecialchars($living_children_row_ipt).'</B></div></td>
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="1" rowspan="2">&nbsp;</td>
    <td style="border-right:0.5px solid #000;padding:4px; text-align:center;" colspan="1" rowspan="2">&nbsp;'.htmlspecialchars($bw).'<br>GRAMS</td>
    </tr>
    </tr>
    <tr style="border:1px solid #000;margin: 45px;">
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="1">&nbsp;YEARS</td>
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="1">&nbsp;MO.</td>
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="1">&nbsp;DAYS</td>
    </tr>
    <tr style="border:1px solid #000;margin: 45px;">
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="3" rowspan="3" valign="top">&nbsp;18 WARD<br><br><div style="font-size:8pt;"><B>'.htmlspecialchars($ward_name_row_ipt).'</B></div></td>
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="2" rowspan="3" valign="top">&nbsp;19 DEPARTMENT<br><br><div style="font-size:8pt;"><B>'.htmlspecialchars($spclty_name_row_ipt).'</B></div></td>
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="1">&nbsp;20 DATE OF</td>
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="1">&nbsp;DAY-MONTH-YEAR</td>
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="1">&nbsp;TIME</td>
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="1" rowspan="3" valign="top">&nbsp;21 LENGTH OF STAY<br><br><div style="font-size:8pt;"><B>'.htmlspecialchars($admdate_row_ipt).'</B></div></td>
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="1" rowspan="3" valign="top">&nbsp;ICD CODING BY CODER<br></td>
    </tr>
    <tr style="border:1px solid #000;margin: 45px;">
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="1">&nbsp;ADMISSION</td>
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="1"><B>'.htmlspecialchars($regdate_datethai).'</B></td>
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="1"><B>'.htmlspecialchars($regtime_row_ipt).'</B></td>
    </tr>
    <tr style="border:1px solid #000;margin: 45px;">
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="1">&nbsp;DISCHARGE</td>
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="1"><B>'.htmlspecialchars($dchdate_datethai).'</B></td>
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="1"><B>'.htmlspecialchars($dchtime_row_ipt).'</B></td>
    </tr>
    <tr style="border:1px solid #000;margin: 45px;">
    <td style="border-right:0.5px solid #000;padding:4px; width:10; position:relative; text-rotate:90;" colspan="1" rowspan="5">22 DIAGNOSIS</td>
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="8" valign="top">&nbsp;(1) PRINCIPAL DIAGNOSIS บันทึกได้เพียงโรคเดียวเท่านั้น<br><div style="font-size:8pt;"><B>&nbsp;&nbsp;&nbsp;&nbsp;'.nl2br(htmlspecialchars($principal_diagnosis)).'</B></div></td>
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="1" valign="top">&nbsp;Main<br><br><br></td>
    </tr>
    <tr style="border:1px solid #000;margin: 45px;">
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="8" valign="top">&nbsp;(2) PRE ADMISSION COMORBIDITY (S)<br><div style="font-size:7pt;"><B>'.nl2br(htmlspecialchars($pre_admission_comorbidity)).'</B></div></td>
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="1" valign="top">&nbsp;Comorobidity<br><br><br><br><br><br><br><br></td>
    </tr>
    </tr>
    <tr style="border:1px solid #000;margin: 45px;">
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="8" valign="top">&nbsp;(3) COMPLICATION (S) (POST ADMISSION COMORBIDITY)<br><div style="font-size:7pt;"><B>&nbsp;&nbsp;&nbsp;&nbsp;'.nl2br(htmlspecialchars($post_admission_comorbidity)).'</B></div></td>
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="1" valign="top">&nbsp;Complication<br><br><br><br></td>
    </tr>
    </tr>
    <tr style="border:1px solid #000;margin: 45px;">
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="8" valign="top">&nbsp;(4) OTHER DIAGNOSIS<br><div style="font-size:7pt;"><B>&nbsp;&nbsp;&nbsp;&nbsp;'.nl2br(htmlspecialchars($other_diagnosis)).'</B></div></td>
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="1" valign="top">&nbsp;Other<br><br><br><br></td>
    </tr>
    </tr>
    <tr style="border:1px solid #000;margin: 45px;">
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="8" valign="top">&nbsp;(5) EXTERNAL CAUSE (S) OF INJURY<br><div style="font-size:7pt;"><B>&nbsp;&nbsp;&nbsp;&nbsp;'.nl2br(htmlspecialchars($external_cause)).'</B></div></td>
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="1" valign="top">&nbsp;E-Code<br><br><br><br></td>
    </tr>
    <tr style="border:1px solid #000;margin: 45px;">
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="1" valign="top">&nbsp;23</td>
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="8" valign="top">&nbsp;OPERATING ROOM PROCEDURES<br><div style="font-size:7pt;"><B>&nbsp;'.nl2br(htmlspecialchars($operating_room)).'</B></div></td>
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="1" valign="top">&nbsp;Procedures icd coding Main<br><br><br><br><br><br></td>
    </tr>
    <tr style="border:1px solid #000;margin: 45px;">
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="1" valign="top">&nbsp;24</td>9
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="9" valign="top">
        &nbsp;NON OPERATING ROOM PROCEDURES<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 1. ('.$tracheostomy_check.') TRACHEOSTOMY<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 2. ('.$mechanical_ventilation_check .') MECHANICAL VENTILATION
        &nbsp;&nbsp;&nbsp;('.$mechanical_ventilation1_check.') มากกว่า 96 ชม.
        &nbsp;&nbsp;&nbsp;('.$mechanical_ventilation2_check.') น้อยกว่า 96 ชม.<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 3. ('.$packed_redcells_check.') PACKED RED CELLS
        &nbsp;&nbsp;&nbsp;('.$fresh_frozen_plasma_check.') FRESH FROZEN PLASMA
        &nbsp;&nbsp;&nbsp;('.$platelets_check.') PLATELETS
        &nbsp;&nbsp;&nbsp;('.$cryoprecipitate_check.') CRYOPRECIPITATE
        &nbsp;&nbsp;&nbsp;('.$whole_blood_check.') Whole Blood<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 4. ('.$computer_tomography_check.') Computer Tomography &nbsp;&nbsp;<b>'.htmlspecialchars($computer_tomography_text).'</b><br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 5. ('.$chemotherapy_check.') CHEMOTHERAPY<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 6. ('.$mri_check.') MRI<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 7. ('.$hemodialysis_check.') Hemodialysis<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 8. ('.$non_or_other_check.') อื่นๆ &nbsp;&nbsp;<b>'.htmlspecialchars($non_or_other_text).'</b><br>
    </td>
    </tr>
    <tr style="border:1px solid #000;margin: 45px;">
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="6" valign="top">
        &nbsp;25 DISCHARGE STATUS<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 1. ('.$discharge_status01_check .') COMPLETE RECOVERED<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 2. ('.$discharge_status02_check .') IMPROVED<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 3. ('.$discharge_status03_check .') NOT IMPROVED<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 4. ('.$discharge_status04_check .') DELIVERED<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 5. ('.$discharge_status05_check .') UNDELIVERED<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 6. ('.$discharge_status06_check .') NORMAL CHILD DISCHARGE WITH MOTHER<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 7. ('.$discharge_status07_check .') NORMAL CHILD DISCHARGE SEPARATELY<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 9. ('.$discharge_status09_check .') DEAD
    </td>
    <td style="border-right:0.5px solid #000;padding:4px;" colspan="5" valign="top">
        &nbsp;26 DISCHARGE TYPE<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 1. ('.$discharge_type01_check .') WITH APPROVAL<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 2. ('.$discharge_type02_check .') AGAINST ADVICE<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 3. ('.$discharge_type03_check .') ESCAPE<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 4. ('.$discharge_type04_check .') BY TRANSFER &nbsp;&nbsp;&nbsp; ชื่อสถานพยาบาลที่ส่งต่อ &nbsp;&nbsp;<b>'.htmlspecialchars($hospital_refer).'</b><br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 5. ('.$discharge_type05_check .') OTHER<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 8. ('.$discharge_type08_check .') DEAD, AUTOPSY<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 9. ('.$discharge_type09_check .') DEAD, NO AUTOPSY<br>
    </td>
    </tr>
    <tr style="border:1px solid #000;margin: 45px;">
    <td style="border-right:0.5px solid #000;padding:4px; text-align:center;" colspan="10">&nbsp;INCASE OF PERINATAL DEATH COMPLETE DEATHE CERTIFICATE ON OTHER SIDE OF FORM</td>
    </tr>
    </table>

';

$mpdf->setFooter('
<table id="bg-table" width="100%" style="border-collapse: collapse;font-size:6pt;margin-top:8px;">
<tr style="border:0px solid #000;margin: 45px;">
<td style="border-right:0px solid #000;padding:4px;" colspan="7" valign="bottom">ATTENDING</td>
<td style="border-right:0px solid #000;padding:4px;" colspan="3" valign="bottom">APPROVED</td>
</tr>
<tr style="border:0px solid #000;margin: 45px;">
<td style="border-right:0px solid #000;padding:4px;" colspan="7" valign="bottom">PHYSICIAN...................'.$user_create_user.'....'.$licenseno.'....</td>
<td style="border-right:0px solid #000;padding:4px;" colspan="3" valign="bottom">BY................'.$user_create_user.'....'.$licenseno.'.................</td>
</tr>
<tr style="border:0px solid #000;margin: 45px;">
<td style="border-right:0px solid #000;padding:4px; text-align:center;" colspan="7" valign="bottom">Signature</td>
<td style="border-right:0px solid #000;padding:4px; text-align:center;" colspan="3" valign="bottom">Signature</td>
</tr>
</table>
หมายเหตุ : งดคำย่อ คำกำกวม '.' '.'( Page {PAGENO} of {nb} )
');
$mpdf->WriteHTML('');
$mpdf->WriteHTML($head);

$mpdf->SetTitle('IN-PATIENT SUMMARY - AN '.$an_REQUEST);
$mpdf->Output('IN-PATIENT SUMMARY - AN '.$an_REQUEST.'.pdf', 'I');
?>