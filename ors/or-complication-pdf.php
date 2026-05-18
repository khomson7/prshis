<?php require_once '../include/Session.php';
// Session::checkLoginSessionAndShowMessage(); //เช็ค session
// if(!(Session::checkPermission('IPD_NURSE_ADDMISSION_NOTE','VIEW'))){
//   return;
// }


Session::checkLoginSessionAndShowMessage(); //เช็ค session
Session::checkPermissionAndShowMessage('DOCUMENT', 'PRINT');

Session::insertSystemAccessLog(json_encode(array(
    'report' => 'IPD-DR-ADMISSION-NOTE-PDF',
    // 'action'=>'PRINT',
    'an' => $an,
), JSON_UNESCAPED_UNICODE));

include('../mains/datethai.php');
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';
$conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
$an = $_REQUEST['an'];
$hn = KphisQueryUtils::getHnByAn($an);   // function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา
// require_once __DIR__ . '../../vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
// require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
date_default_timezone_set('asia/bangkok');
$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
$mpdf->AddPageByArray([
    'margin-left' => 8,
    'margin-right' => 8,
    'margin-top' => 8,
    'margin-bottom' => 5,
]);
//----------------------select ข้อมูล จากฐานข้อมูลเพื่อค้นหา ชื่อ - สกุล
$image_uncheck = "<img src='../include/images/check-adm.jpg' width='1.6%' class='check_img'>";
$image_check = "<img src='../include/images/check-adm-1.png' width='1.6%' class='check_img'>";
$query_parameters_REQUEST = ['an' => $an];

$sql_ipt = "select patient.hn,patient.pname,patient.fname,patient.lname,patient.drugallergy,
                    an_stat.age_y,an_stat.age_m,an_stat.age_d,an_stat.an,
                    ipt.regdate,ipt.regtime,ipt.ward,ward.name as ward_name,
                    ipt.pttype,
                    pttype.`name` as pttype_name,
                    ward.shortname
                    from " . DbConstant::HOSXP_DBNAME . ".ipt
                    left outer join " . DbConstant::HOSXP_DBNAME . ".an_stat on an_stat.an=ipt.an
                    left outer join " . DbConstant::HOSXP_DBNAME . ".patient on patient.hn=ipt.hn
                    left outer join " . DbConstant::HOSXP_DBNAME . ".ward on ward.ward=ipt.ward
                    LEFT OUTER JOIN " . DbConstant::HOSXP_DBNAME . ".pttype ON pttype.pttype = ipt.pttype
                    WHERE ipt.an=:an";

$stmt_ipt = $conn->prepare($sql_ipt);
$stmt_ipt->execute($query_parameters_REQUEST);
$row_iptCount = 0;
while ($row_ipt = $stmt_ipt->fetch()) {
    $hn_row_ipt = htmlspecialchars($row_ipt['hn']);
    $pname_row_ipt = htmlspecialchars($row_ipt['pname']);
    $fname_row_ipt = htmlspecialchars($row_ipt['fname']);
    $lname_row_ipt = htmlspecialchars($row_ipt['lname']);
    $age_y_row_ipt = htmlspecialchars($row_ipt['age_y']);
    $an_row_ipt = htmlspecialchars($row_ipt['an']);
    $wardname_row_ipt = htmlspecialchars($row_ipt['ward_name']);
}

$strDate = date("Y-m-d H:i:s");

$mpdf->setFooter(' (พิมพ์โดย ' . $_SESSION['name'] . ' วันที่พิมพ์ ' . DateThai($strDate) . ' ) ' . '<br>ผู้ป่วย : (hn : ' . $hn_row_ipt . ')(an : ' . $an . ')(ชื่อ - สกุล : ' . $pname_row_ipt . ' ' . $fname_row_ipt . ' ' . $lname_row_ipt . ')' . ' ( Page {PAGENO} of {nb} )');
$mpdf->WriteHTML('');
//----------------------select ข้อมูล จากฐานข้อมูลเพื่อค้นหา ชื่อ - สกุล
//----------------------ipd_nurse_admission_note
$sql = "SELECT t.* ,opduser.name AS name_full, entryposition
                FROM " . DbConstant::KPHIS_DBNAME . ".prs_or_complication t
                LEFT OUTER JOIN " . DbConstant::HOSXP_DBNAME . ".opduser ON t.update_user = opduser.loginname
                WHERE an=:an";
$parameters['an'] = $an;
$stmt = $conn->prepare($sql);
$stmt->execute($parameters);
$row = $stmt->fetch();

//ความรู้สึกตัว
$concious = $row['concious'];
/*if($concious == "รู้สึกตัวดี"){$concious_1 = $image_check;}else{$concious_1 = $image_uncheck;}
if($concious == "สับสน"){$concious_2 = $image_check;}else{$concious_2 = $image_uncheck;}
if($concious == "ง่วงซึม"){$concious_3 = $image_check;}else{$concious_3 = $image_uncheck;}
if($concious == "ไม่รู้สึกตัว"){$concious_4 = $image_check;}else{$concious_4 = $image_uncheck;} */
//ลักษณะการหายใจ


// Loop for checkboxes 1 to 51
for ($i = 1; $i <= 51; $i++) {
    $k1 = "no{$i}";
    $k2 = "no{$i}_2";
    $k3 = "no{$i}_3";

    ${$k1} = (isset($row[$k1]) && $row[$k1] == "Y") ? $image_check : $image_uncheck;
    ${$k2} = (isset($row[$k2]) && $row[$k2] == "Y") ? $image_check : $image_uncheck;
    ${$k3} = (isset($row[$k3]) && $row[$k3] == "Y") ? $image_check : $image_uncheck;
}

$no19_1 = (isset($row['no19_1']) && $row['no19_1'] == "Y") ? $image_check : $image_uncheck;
$no19_1_2 = (isset($row['no19_1_2']) && $row['no19_1_2'] == "Y") ? $image_check : $image_uncheck;
$no19_1_3 = (isset($row['no19_1_3']) && $row['no19_1_3'] == "Y") ? $image_check : $image_uncheck;

$no42_2_1 = (isset($row['no42_2_1']) && $row['no42_2_1'] == "Y") ? $image_check : $image_uncheck;
$no42_2_2 = (isset($row['no42_2_2']) && $row['no42_2_2'] == "Y") ? $image_check : $image_uncheck;
$no42_2_3 = (isset($row['no42_2_3']) && $row['no42_2_3'] == "Y") ? $image_check : $image_uncheck;

$no42_3_1 = (isset($row['no42_3_1']) && $row['no42_3_1'] == "Y") ? $image_check : $image_uncheck;
$no42_3_2 = (isset($row['no42_3_2']) && $row['no42_3_2'] == "Y") ? $image_check : $image_uncheck;
$no42_3_3 = (isset($row['no42_3_3']) && $row['no42_3_3'] == "Y") ? $image_check : $image_uncheck;

$no43_text = htmlspecialchars($row['no43_text']);
$no47_text = htmlspecialchars($row['no47_text']);
$no51_text = htmlspecialchars($row['no51_text']);

$normal_breath = $row['normal_breath'];
if ($normal_breath == "Y") {
    $normal_breath = $image_check;
} else {
    $normal_breath = $image_uncheck;
}
$emotional_other_text = $row['emotional_other_text'];

$no431 = $row['no431'];
if ($no431 == "Y") {
    $no431 = $image_check . '&nbsp; 1';
} else {
    $no431 = '-';
}
$no432 = $row['no432'];
if ($no432 == "Y") {
    $no432 = $image_check . '&nbsp; 2';
} else {
    $no432 = '-';
}
$no433 = $row['no433'];
if ($no433 == "Y") {
    $no433 = $image_check . '&nbsp; 3';
} else {
    $no433 = '-';
}

//ชื่อ-สกุล ตำแหน่งผู้บันทึกข้อมูล
$name_full = $row['name_full'];
$entryposition = $row['entryposition'];

$nurse_name = htmlspecialchars($row['nurse_name']);
$nurse_name2 = htmlspecialchars($row['nurse_name2']);
$nurse_name3 = htmlspecialchars($row['nurse_name3']);

$nodata = '.';

//----------------------ipd_nurse_admission_note
ob_start();
?>
<style>
    body {
        font-family: "Garuda"; //เรียกใช้font Garuda สำหรับแสดงผล ภาษาไทย
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

    .check_img {
        /*margin-top: 10px;*/
    }

    table,
    th,
    td {
        border: 1px solid black;
        border-collapse: collapse;
        font-size: 10.4px;
    }
</style>
<!--<h6 style="text-align:right;">Anes01</h6>-->
<table id="bg-table" width="100%" style="border-collapse: collapse;font-size:8pt;margin-top:7px;">

    <tr style="border:1px solid #000;margin: 35px;">
        <td style="border-right:0.5px solid #000;padding:4px; text-align:center; background-color:#C0C0C0;"
            colspan="10"><B>ใบประเมินภาวะแทรกซ้อนหลังการระงับความรู้สึกใน 24-48 ชั่วโมง</B></td>
    </tr>
    <tr style="border:1px solid #000;margin: 35px;">
        <td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="10"><B>Complication 1 =
                Intra-op &emsp;&emsp;&emsp;&emsp;&ensp; 2= PACU
                &emsp;&emsp;&emsp;&emsp;&ensp; 3= Post-op 24 hrs.</B></td>
    </tr>
    <tr style="border:1px solid #000;margin: 45px;">
        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>1</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Retained ET tube /
                Tracheostomy tube</B></td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no1 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no1_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no1_3 ?>&nbsp; 3
        </td>

        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>36</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Hypothermia (Temp < 35
                    °C)</B>
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no36 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no36_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no36_3 ?>&nbsp; 3
        </td>

    </tr>

    <tr style="border:1px solid #000;margin: 45px;">
        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>2</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Ventilatory support</B>
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no2 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no2_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no2_3 ?>&nbsp; 3
        </td>

        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>37</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Fever (Temp > 38 °C) ,
                MH</B></td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no37 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no37_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no37_3 ?>&nbsp; 3
        </td>

    </tr>

    <tr style="border:1px solid #000;margin: 45px;">
        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>3</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Sore throat</B></td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no3 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no3_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no3_3 ?>&nbsp; 3
        </td>

        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>38</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Transfusion reaction /
                Mismatch</B></td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no38 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no38_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no38_3 ?>&nbsp; 3
        </td>

    </tr>

    <tr style="border:1px solid #000;margin: 45px;">
        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>4</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Upper airway
                obstruction</B></td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no4 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no4_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no4_3 ?>&nbsp; 3
        </td>

        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>39</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Coagulopathy</B></td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no39 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no39_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no39_3 ?>&nbsp; 3
        </td>

    </tr>

    <tr style="border:1px solid #000;margin: 45px;">
        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>5</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Lower airway
                obstuction</B></td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no5 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no5_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no5_3 ?>&nbsp; 3
        </td>

        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>40</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Massive blood loss</B>
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no40 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no40_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no40_3 ?>&nbsp; 3
        </td>

    </tr>

    <tr style="border:1px solid #000;margin: 45px;">
        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>6</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Unpredicted difficult
                intubation</B></td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no6 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no6_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no6_3 ?>&nbsp; 3
        </td>

        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>41</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Allergic reaction /
                Anaphylactic shock</B></td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no41 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no41_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no41_3 ?>&nbsp; 3
        </td>

    </tr>

    <tr style="border:1px solid #000;margin: 45px;">
        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>7</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Aspiration</B></td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no7 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no7_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no7_3 ?>&nbsp; 3
        </td>

        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>42</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Burn</B></td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no42 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no42_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no42_3 ?>&nbsp; 3
        </td>

    </tr>

    <tr style="border:1px solid #000;margin: 45px;">
        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>8</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Airway injury</B></td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no8 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no8_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no8_3 ?>&nbsp; 3
        </td>

        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B></B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Shivering</B></td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no42_2_1 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no42_2_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no42_2_3 ?>&nbsp; 3
        </td>

    </tr>

    <tr style="border:1px solid #000;margin: 45px;">
        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>9</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Dental injury</B></td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no9 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no9_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no9_3 ?>&nbsp; 3
        </td>

        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"></td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%"></td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%"></td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%"></td>

    </tr>

    <tr style="border:1px solid #000;margin: 45px;">
        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>10</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Esophageal intubation
                (เขียว หรือ SpO2 < 90 %)</B>
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no10 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no10_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no10_3 ?>&nbsp; 3
        </td>

        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>43</B></td>
        <td width="25%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;">

            <B>ใช้ยา</B>&nbsp;
            <B style="text-decoration: underline;"> <?= htmlspecialchars($row['no43_text']) ?></B>&nbsp; <br>

            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<B
                style="text-decoration: underline;"> <?= htmlspecialchars($row['no43_tet']) ?></B>&nbsp;

        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no43 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no43_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no43_3 ?>&nbsp; 3
        </td>


    </tr>

    <tr style="border:1px solid #000;margin: 45px;">
        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>11</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Pneumothorax</B></td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no11 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no11_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no11_3 ?>&nbsp; 3
        </td>

        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>44</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Urinary retenion</B></td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no44 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no44_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no44_3 ?>&nbsp; 3
        </td>

    </tr>

    <tr style="border:1px solid #000;margin: 45px;">
        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>12</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Hypoxaemia</B></td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no12 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no12_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no12_3 ?>&nbsp; 3
        </td>

        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>45</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Alcohol withdrawal</B>
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no45 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no45_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no45_3 ?>&nbsp; 3
        </td>

    </tr>

    <tr style="border:1px solid #000;margin: 45px;">
        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>13</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Hypoventilation</B></td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no13 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no13_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no13_3 ?>&nbsp; 3
        </td>

        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>46</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Drug error / Human
                error</B></td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no46 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no46_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no46_3 ?>&nbsp; 3
        </td>

    </tr>

    <tr style="border:1px solid #000;margin: 45px;">
        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>14</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Reintubation</B></td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no14 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no14_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no14_3 ?>&nbsp; 3
        </td>

        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>47</B></td>

        <td width="25%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;">
            <B>Other (specify).</B>&nbsp;
            <B style="text-decoration: underline;"> <?= htmlspecialchars($row['no47_text']) ?></B>
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no47 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no47_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no47_3 ?>&nbsp; 3
        </td>

    </tr>

    <tr style="border:1px solid #000;margin: 45px;">
        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>15</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Atelectasis</B></td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no15 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no15_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no15_3 ?>&nbsp; 3
        </td>

        <td style="text-align:top; border-right:0.5px solid #000;padding:4px;" width="3%" rowspan="5"><B>48</B></td>
        <td width="25%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;" rowspan="5">
            <B>Nausea & Vomitting</B><br>
            &nbsp;&nbsp;&nbsp;<B>premed</B>&nbsp;
            <B style="text-decoration: underline;"> <?= htmlspecialchars($row['no48_1_text']) ?></B>&nbsp; <br>

            &nbsp;&nbsp;&nbsp;<B>intraop. prophylaxis</B>&nbsp;<B
                style="text-decoration: underline;"> <?= htmlspecialchars($row['no48_2_text']) ?></B>&nbsp;<br>
            <B>อาการ N / V</B><br>
            <B>ใช้ยา</B>&nbsp;
            <B style="text-decoration: underline;"> <?= htmlspecialchars($row['no48_3_text']) ?></B>
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%" rowspan="5">
            <?= $nodata ?><br>
            <?= $nodata ?><br>
            <?= $nodata ?><br>
            <?= $no48 ?>&nbsp; 1

        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%" rowspan="5">
            <?= $nodata ?><br>
            <?= $nodata ?><br>
            <?= $nodata ?><br>
            <?= $no48_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%" rowspan="5">
            <?= $nodata ?><br>
            <?= $nodata ?><br>
            <?= $nodata ?><br>
            <?= $no48_3 ?>&nbsp; 3
        </td>


    </tr>

    <tr style="border:1px solid #000;margin: 45px;">
        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>16</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Pulmonary edema /
                effusion</B></td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no16 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no16_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no16_3 ?>&nbsp; 3
        </td>


    </tr>

    <tr style="border:1px solid #000;margin: 45px;">
        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>17</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Sig. hypertension (SBP >
                180 mmHg)</B></td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no17 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no17_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no17_3 ?>&nbsp; 3
        </td>

    </tr>

    <tr style="border:1px solid #000;margin: 45px;">
        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>18</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Sig. hypertension (SBP <
                    80 mmHg)</B>
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no18 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no18_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no18_3 ?>&nbsp; 3
        </td>

    </tr>

    <tr style="border:1px solid #000;margin: 45px;">
        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>19</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Sig. arrhythmia
                (includeing techycardia > 120)</B></td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no19 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no19_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no19_3 ?>&nbsp; 3
        </td>

    </tr>

    <tr style="border:1px solid #000;margin: 45px;">
        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B></B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Sig. arrhythmia
                (bradycardia < 40)</B>
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no19_1 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no19_1_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no19_1_3 ?>&nbsp; 3
        </td>

        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B></B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>NONE</B></td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no49 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no49_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no49_3 ?>&nbsp; 3
        </td>

    </tr>

    <tr style="border:1px solid #000;margin: 45px;">
        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>20</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Myocardia ischaenia /
                MI</B></td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no20 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no20_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no20_3 ?>&nbsp; 3
        </td>

        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B></B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Direct tranfered to</B>
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no50 ?>&nbsp; ward
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no50_2 ?>&nbsp; ICU
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no50_3 ?>&nbsp; Refer
        </td>

    </tr>

    <tr style="border:1px solid #000;margin: 45px;">
        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>21</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Cardiac failure</B></td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no21 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no21_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no21_3 ?>&nbsp; 3
        </td>

        <td style="text-align:left; border-right:0.5px solid #000;padding:4px; vertical-align:top;" colspan="5"
            rowspan="15">
            <div style="color:blue;font-weight:bold;font-size:13px;"> &nbsp;กิจกรรมการพยาบาล</div>
            <div style="color:blue;font-weight:bold;font-size:13px;"> &nbsp;ประเมินผู้ป่วยหลังการระงับความรู้สึกใน 24
                ชั่วโมง</div><br>
            <div style="color:black;font-weight:bold;font-size:14px;"><?= $no51 ?>&nbsp; No incidented anesthesia</div>
            <br>
            <div style="color:black;font-weight:bold;font-size:12px;">&nbsp;&nbsp;&nbsp;<?= $no51_text ?></div>
            <br>&nbsp;&nbsp;&nbsp;.....................................................................................................................<br><br><br><br><br><br>


            <div style="text-align:center;font-weight:bold;font-size:12px;">ลงชื่อผู้ตรวจสอบ (Intra-op) &nbsp;&nbsp;
                <?= $nurse_name ?>
            </div>

            <br><br>


            <div style="text-align:center;font-weight:bold;font-size:12px;">ลงชื่อผู้ตรวจสอบ (PACU) &nbsp;&nbsp;
                <?= $nurse_name2 ?>
            </div>

            <br><br>


            <div style="text-align:center;font-weight:bold;font-size:12px;">ลงชื่อผู้ตรวจสอบ (Post-op 24 hrs.)
                &nbsp;&nbsp; <?= $nurse_name3 ?></div>

        </td>



    </tr>

    <tr style="border:1px solid #000;margin: 45px;">
        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>22</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Cardiac arrest</B></td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no22 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no22_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no22_3 ?>&nbsp; 3
        </td>



    </tr>

    <tr style="border:1px solid #000;margin: 45px;">
        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>23</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Shock</B></td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no23 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no23_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no23_3 ?>&nbsp; 3
        </td>

    </tr>

    <tr style="border:1px solid #000;margin: 45px;">
        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>24</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Delayed emergence
                (ตื่นช้า >= 1 ชั่วโมง)</B></td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no24 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no24_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no24_3 ?>&nbsp; 3
        </td>

    </tr>

    <tr style="border:1px solid #000;margin: 45px;">
        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>25</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Coma / CVA</B></td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no25 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no25_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no25_3 ?>&nbsp; 3
        </td>

    </tr>

    <tr style="border:1px solid #000;margin: 45px;">
        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>26</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Awareness under GA</B>
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no26 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no26_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no26_3 ?>&nbsp; 3
        </td>

    </tr>

    <tr style="border:1px solid #000;margin: 45px;">
        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>27</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>High block / Total
                block</B></td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no27 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no27_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no27_3 ?>&nbsp; 3
        </td>

    </tr>

    <tr style="border:1px solid #000;margin: 45px;">
        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>28</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Post dural headache</B>
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no28 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no28_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no28_3 ?>&nbsp; 3
        </td>

    </tr>

    <tr style="border:1px solid #000;margin: 45px;">
        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>29</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Peripheral nerve
                injury</B></td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no29 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no29_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no29_3 ?>&nbsp; 3
        </td>

    </tr>

    <tr style="border:1px solid #000;margin: 45px;">
        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>30</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Volume overload
                Delirium</B></td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no30 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no30_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no30_3 ?>&nbsp; 3
        </td>

    </tr>

    <tr style="border:1px solid #000;margin: 45px;">
        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>31</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Back pain</B>
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no31 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no31_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no31_3 ?>&nbsp; 3
        </td>

    </tr>

    <tr style="border:1px solid #000;margin: 45px;">
        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>32</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Convulsion</B></td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no32 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no32_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no32_3 ?>&nbsp; 3
        </td>

    </tr>

    <tr style="border:1px solid #000;margin: 45px;">
        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>33</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>LA toxicity</B></td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no33 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no33_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no33_3 ?>&nbsp; 3
        </td>

    </tr>

    <tr style="border:1px solid #000;margin: 45px;">
        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>34</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Hypoglycemia</B></td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no34 ?>&nbsp; 1
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no34_2 ?>&nbsp; 2
        </td>
        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
            <?= $no34_3 ?>&nbsp; 3
        </td>

    </tr>

    <tr style="border:1px solid #000;margin: 45px;">
        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>35</B></td>
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%"><B>Electrolyte / Acid-base
                imbalance</B></td>
        <td style="height:10px;" width="4%">
            <?= $no35 ?>&nbsp; 1
        </td>
        <td style="height:10px;" width="4%">
            <?= $no35_2 ?>&nbsp; 2
        </td>
        <td style="height:10px;" width="4%">
            <?= $no35_3 ?>&nbsp; 3
        </td>

    </tr>



    <tr style="border:1px solid #000;margin: 45px;">
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px; vertical-align:top;" colspan="5"
            rowspan="1">
        <td style="text-align:left; border-right:0.5px solid #000;padding:4px; vertical-align:top;" colspan="5"
            rowspan="15">
            <div style="font-weight:bold;font-size:12px;"> &nbsp;ชื่อ...<?= $pname_row_ipt ?> <?= $fname_row_ipt ?>
                <?= $lname_row_ipt ?>.....อายุ...<?= $age_y_row_ipt ?>...ปี'.'
            </div>
            <div style="font-weight:bold;font-size:13px;">
                &nbsp;HN...<?= $hn_row_ipt ?>......AN...<?= $an_row_ipt ?>......
            </div>
            <div style="font-weight:bold;font-size:13px;"> &nbsp;Ward...<?= $wardname_row_ipt ?>....'.'</div><br>
            <div style="font-weight:bold;font-size:13px;">
                &nbsp;แพทย์เจ้าของไข้...'.'.....................................'.'....'.'</div>


        </td>



    </tr>




</table>
<?php
$head = ob_get_clean();
$mpdf->WriteHTML($head);
$mpdf->Output();
?>