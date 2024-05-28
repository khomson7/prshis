<?php
//require_once './project/function/Session.php';
//Session::checkLoginSessionAndShowMessage(); //เช็ค session
//require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require_once './include/Session.php';

require_once './include/DbUtils.php';
require_once './include/KphisQueryUtils.php';
require_once './include/ReportQueryUtils.php';
require_once 'ExternalDocumentTracker.php';
date_default_timezone_set('asia/bangkok');
$conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
$an = $_REQUEST['an'];
$hn = KphisQueryUtils::getHnByAn($an);   // function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา
$vn = KphisQueryUtils::getVnByAn($an);
$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
$image_check = "<img src='../include/images/check-1.jpg' width='1.6%' class='check_img'>";
//$image_check = '/';
$getDocumentSummary = KphisQueryUtils::getDocumentSummary($an);
$image_checkSummary = '';
if(($getDocumentSummary)){
    $image_checkSummary = $image_check;
}

$getDocumentAddmissionDoctor = (KphisQueryUtils::getDocumentAddmissionDoctor1($an));
$image_checkAddmissionDoctor = '';
if(($getDocumentAddmissionDoctor)){
    $image_checkAddmissionDoctor = $image_check;
}

$getDocumentAddmissionDoctor2 = (KphisQueryUtils::getDocumentAddmissionDoctor2($an));
$image_checkAddmissionDoctor2 = '';
if(($getDocumentAddmissionDoctor2)){
    $image_checkAddmissionDoctor2 = $image_check;
}
//from kphis
$getDocumentAddmissionNurse = KphisQueryUtils::getDocumentAddmissionNurse($an);

//from prhis

$getDocumentPreNurseNote = ReportQueryUtils::getDocumentPreNurseNote($an);

$getDocumentLrReport2 = ReportQueryUtils::getDocumentLrReport2($an);

$image_checkAddmissionNurse = '';

if(($getDocumentAddmissionNurse || $getDocumentPreNurseNote || $getDocumentLrReport2)){
    $image_checkAddmissionNurse = $image_check;
}
$getDocumentOrder = KphisQueryUtils::getDocumentOrder($an);
$getDocumentOrderProgressNote = KphisQueryUtils::getDocumentOrderProgressNote($an);
$image_checkOrder_ProgressNote = '';
if((($getDocumentOrder)) || (($getDocumentOrderProgressNote))){
    $image_checkOrder_ProgressNote = $image_check;
}
$getDocumentConsult = KphisQueryUtils::getDocumentConsult($an);
$image_checkConsult = '';
if(($getDocumentConsult)){
    $image_checkConsult = $image_check;
}
$getDocumentIndex = KphisQueryUtils::getDocumentIndex($an);
$image_checkIndex = '';
if(($getDocumentIndex)){
    $image_checkIndex = $image_check;
}
$getDocumentFocusList = KphisQueryUtils::getDocumentFocusList($an);
$image_checkFocusList = '';
if(($getDocumentFocusList)){
    $image_checkFocusList = $image_check;
}
$getDocumentFocusNote = KphisQueryUtils::getDocumentFocusNote($an);
$image_checkFocusNote = '';
if(($getDocumentFocusNote)){
    $image_checkFocusNote = $image_check;
}
$getDocumentVitalSign = KphisQueryUtils::getDocumentVitalSign($an);
$image_checkVitalSign = '';
if(($getDocumentVitalSign)){
    $image_checkVitalSign = $image_check;
}
$getDocumentIO = KphisQueryUtils::getDocumentIO($an);
$image_checkIO = '';
if(($getDocumentIO)){
    $image_checkIO = $image_check;
}
$image_checkNursingSection = '';
if(($getDocumentFocusList || $getDocumentFocusNote) || ($getDocumentVitalSign) || ($getDocumentIO)){
    $image_checkNursingSection = $image_check;
}
$getDocumentMedReconciliation = KphisQueryUtils::getDocumentMedReconciliation($an);
$image_checkMedReconciliation = '';
if(($getDocumentMedReconciliation)){
    $image_checkMedReconciliation = $image_check;
}
$getDocumentMedReconciliationHOSXP = KphisQueryUtils::getDocumentMedReconciliationHOSXP($an);
$image_checkMedReconciliationHOSXP = '';
if(($getDocumentMedReconciliationHOSXP)){
    $image_checkMedReconciliationHOSXP = $image_check;
}
$getDocumentLab = KphisQueryUtils::getDocumentLab($an);
$image_checkLab = '';
if(($getDocumentLab)){
    $image_checkLab = $image_check;
}
$getDocumentXray = KphisQueryUtils::getDocumentXray($an);
$image_checkXray = '';
if(($getDocumentXray)){
    $image_checkXray = $image_check;
}
$getDocumentCTscan = KphisQueryUtils::getDocumentCTscan($an);
$image_checkCTscan = '';
if(($getDocumentCTscan)){
    $image_checkCTscan = $image_check;
}
$getDocumentMRI = KphisQueryUtils::getDocumentMRI($an);
$image_checkMRI = '';
if(($getDocumentMRI)){
    $image_checkMRI = $image_check;
}
$image_PathologyLabXray = '';
if(($getDocumentLab) || ($getDocumentXray) || ($getDocumentCTscan) || ($getDocumentMRI)){
    $image_PathologyLabXray = $image_check;
}
$getDocumentERFromOpdErMasterId = KphisQueryUtils::getDocumentERFromOpdErMasterId($vn);
$image_checkER = '';
if(($getDocumentERFromOpdErMasterId)){
    $image_checkER = $image_check;
}
$get_document_neodms_anes = ExternalDocumentTracker::get_document_neodms_anes(null,$an);
$image_checkneodms_anes = '';
if(($get_document_neodms_anes)){
    $image_checkneodms_anes = $image_check;
}
$getDocumentOperative = KphisQueryUtils::getDocumentOperative($an);
$image_checkOperative = '';
if(($getDocumentOperative)){
    $image_checkOperative = $image_check;
}
$get_document_neodms_operative = ExternalDocumentTracker::get_document_neodms_operative($an);
$image_checkneodms_operative = '';
if(($get_document_neodms_operative)){
    $image_checkneodms_operative = $image_check;
}
$query_parameters_REQUEST = ['an'=>$an];
        $sql_ipt = "select patient.hn,patient.pname,patient.fname,patient.lname,patient.drugallergy,
                    an_stat.age_y,an_stat.age_m,an_stat.age_d,
                    ipt.regdate,ipt.regtime,ipt.ward,
                    ipt.pttype,ipt.dchdate,
                    pttype.`name` as pttype_name,
                    ward.shortname,ward.name as wardname
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
            $regdate_row_ipt = htmlspecialchars($row_ipt['regdate']);
            $dchdate_row_ipt = htmlspecialchars($row_ipt['dchdate']);
            $wardname_row_ipt = htmlspecialchars($row_ipt['wardname']);
        }
// $mpdf->SetFooter(' (พิมพ์โดย '.$_SESSION['name'].' วันที่พิมพ์ '.date('d/m/Y H:i').' )');
// $mpdf->WriteHTML('');

$image_check_an = "<img src='../include/images/an_qr/".$an.".png' width='65'>";

$head =
'   <style>
        body{
            font-family: "Garuda";//เรียกใช้font Garuda สำหรับแสดงผล ภาษาไทย
        }
        footer {
            position: fixed;
            bottom: -60px;
            left: 0px;
            right: 0px;
            height: 100px;

            /** Extra personal styles **/
            line-height: 35px;
        }
        br {
            display: block;
            content: " ";
            margin: 10px 0;
            height:30pt;
            line-height: 150%;
        }

      
* {
  box-sizing: border-box;
}

/* Create two equal columns that floats next to each other */
.column {
  float: left;
  width: 50%;
  
  height: 0px; /* Should be removed. Only for demonstration */
}
.column0 {
    float: left;

  
    height: 0px; /* Should be removed. Only for demonstration */
  }

/* Clear floats after the columns */
.row:after {
  content: "";
  display: table;
  clear: both;
}


    </style>
  
    <div class="row">
    <div class="column"><strong><span style="text-align:right;">'.'</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    เอกสารใบปะหน้า</strong></div>
   
  </div>
   
    <p>
        วันที่รับไว้ : '.$regdate_row_ipt.'
        HN : '.$hn_row_ipt.' AN : '.$an.'&nbsp;&nbsp;WARD : '.$wardname_row_ipt.'<br> ชื่อ - สกุล : '.$pname_row_ipt.' '.$fname_row_ipt.' '.$lname_row_ipt.' วันที่จำหน่าย : '.$dchdate_row_ipt.'
    </p>

   

    <div class="qr-code" style="display: none;"></div>

    <table id="bg-table" width="100%" style="border-collapse: collapse;font-size:10pt;margin-top:8px;">
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="8%">&nbsp;ในแฟ้ม</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="13%">&nbsp;ในระบบScan</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="15%">&nbsp;ในระบบHOSxP</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="15%">&nbsp;ในระบบKPHIS</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;">&nbsp;ชื่อเอกสาร</td>
        </tr>
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;">'.$image_checkSummary.'</td>
            <td  style="border-right:0.5px solid #000;padding:4px;">&nbsp;Discharge Summary</td>
        </tr>
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="border-right:0.5px solid #000;padding:4px;">&nbsp;Referring Letter Sheet</td>
        </tr>
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="border-right:0.5px solid #000;padding:4px;">&nbsp;Informed Consent</td>
        </tr>
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;">'.$image_checkAddmissionDoctor.$image_checkAddmissionDoctor2.'</td>
            <td  style="border-right:0.5px solid #000;padding:4px;">&nbsp;แบบประเมินแรกรับใหม่ผู้ป่วยใน</td>
        </tr>
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;">'.$image_checkAddmissionNurse.'</td>
            <td  style="border-right:0.5px solid #000;padding:4px;">&nbsp;ใบการประเมินสภาพผู้ป่วยแรกรับและแผนสุขภาพ</td>
        </tr>
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;">'.$image_checkOrder_ProgressNote.'</td>
            <td  style="border-right:0.5px solid #000;padding:4px;">&nbsp;Progress Note, Order</td>
        </tr>
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;">'.$image_checkMedReconciliationHOSXP.'</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;">'.$image_checkMedReconciliation.'</td>
            <td  style="border-right:0.5px solid #000;padding:4px;">&nbsp;Med Reconciliation</td>
        </tr>
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;">'.$image_checkConsult.'</td>
            <td  style="border-right:0.5px solid #000;padding:4px;">&nbsp;Consulation Report</td>
        </tr>
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;">'.$image_checkneodms_anes.'</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="border-right:0.5px solid #000;padding:4px;">&nbsp;Anesthetic Record</td>
        </tr>
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;">'.$image_checkneodms_operative.'</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;">'.$image_checkOperative.'</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="border-right:0.5px solid #000;padding:4px;">&nbsp;Operative Report</td>
        </tr>
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="border-right:0.5px solid #000;padding:4px;">&nbsp;Labour Record</td>
        </tr>
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;">'.$image_PathologyLabXray.'</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="border-right:0.5px solid #000;padding:4px;">&nbsp;Pathology Report/ Laboratory Report/ X-rays Report</td>
        </tr>
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;">'.$image_checkLab.'</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="border-right:0.5px solid #000;padding:4px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Laboratory Report</td>
        </tr>
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;">'.$image_checkXray.'</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="border-right:0.5px solid #000;padding:4px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;X-rays Report</td>
        </tr>
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;">'.$image_checkCTscan.'</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="border-right:0.5px solid #000;padding:4px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;CT scan</td>
        </tr>
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;">'.$image_checkMRI.'</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="border-right:0.5px solid #000;padding:4px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;MRI</td>
        </tr>
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="border-right:0.5px solid #000;padding:4px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Blood transfusion Report(ใบของห้องเลือด)</td>
        </tr>
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="border-right:0.5px solid #000;padding:4px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Electrocardiogram Report</td>
        </tr>
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="border-right:0.5px solid #000;padding:4px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Other Special Clinical Report</td>
        </tr>
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="border-right:0.5px solid #000;padding:4px;">&nbsp;Physiotherapy Sheet (กายภาพบำบัด)</td>
        </tr>
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;">'.$image_checkNursingSection.'</td>
            <td  style="border-right:0.5px solid #000;padding:4px;">&nbsp;Nursing Section</td>
        </tr>
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;">'.$image_checkFocusList.'</td>
            <td  style="border-right:0.5px solid #000;padding:4px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Focus List</td>
        </tr>
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;">'.$image_checkFocusNote.'</td>
            <td  style="border-right:0.5px solid #000;padding:4px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nurses Notes</td>
        </tr>
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;">'.$image_checkVitalSign.'</td>
            <td  style="border-right:0.5px solid #000;padding:4px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Graphic Record</td>
        </tr>
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;">'.$image_checkIO.'</td>
            <td  style="border-right:0.5px solid #000;padding:4px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fluid Balance Summary</td>
        </tr>
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;">'.$image_checkIndex.'</td>
            <td  style="border-right:0.5px solid #000;padding:4px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Index (Nurse Planning)</td>
        </tr>
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="border-right:0.5px solid #000;padding:4px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;บันทึกอื่นๆ ที่เกี่ยวกับพยาบาล ระบุ</td>
        </tr>
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="border-right:0.5px solid #000;padding:4px;">&nbsp;Medication Administration Records</td>
        </tr>
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="border-right:0.5px solid #000;padding:4px;">&nbsp;เอกสารอื่นๆ ระบุ</td>
        </tr>
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;">'.$image_checkER .'</td>
            <td  style="border-right:0.5px solid #000;padding:4px;">&nbsp;เอกสาร ER</td>
        </tr>
    </table>
    <p style="text-align:right;">(พิมพ์โดย '.$_SESSION['name'].' วันที่พิมพ์ '.date('d/m/Y H:i').' ) </p>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"> </script>
    <script src="./include/js/script.js"> </script>
';
$mpdf->WriteHTML($head);
$mpdf->Output();
?>