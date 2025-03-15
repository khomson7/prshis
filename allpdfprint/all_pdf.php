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

$head0 = '
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
    .footer {
        position: absolute;
        bottom: 0;
        right: 0;
        left: 0;
        margin: 0 auto;
        height: 80px;
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
   
    </style> ';
/*$head1 = '<h2 style="text-align:right;font-size:8pt;">FM-ICU-005-00</h2>
    <h2 style="text-align:center;font-size:11pt;">Form1 FANCAS &nbsp;';*/

// Add the content of the second file here
require_once '../allpdfprint/ipd-dr-admission-note-pdf.php';
require_once '../allpdfprint/ipd-dr-newborn-admission-note-pdf.php';
require_once '../allpdfprint/ipd-nurse-admission-note-pdf.php';
require_once '../allpdfprint/prs-pre-nursenote-pdf.php';
require_once '../allpdfprint/lr-report2-pdf.php';
//require_once '../allpdfprint/ipd-dr-admission-note-pdf.php';

//$heads = '<h2 style="text-align:right;font-size:8pt;">FM-ICU-005-00</h2>
//    <h2 style="text-align:center;font-size:11pt;">Form2 FANCAS &nbsp;';

// Set the footer and write the content
//$mpdf->setFooter(' Page '.'{PAGENO}');
$mpdf->setFooter(' (พิมพ์โดย '.$_SESSION['name'].' วันที่พิมพ์ '.date('d/m/Y H:i').' ) '.'<br>ผู้ป่วย : (hn : '.$hn_row_ipt.')(an : '.$an.')(ชื่อ - สกุล : '.$pname_row_ipt.' '.$fname_row_ipt.' '.$lname_row_ipt.')'.' ( Page {PAGENO} of {nb} )');
//$mpdf->WriteHTML($head0);
//$mpdf->WriteHTML($head1);
//-------------------Vital Sign

$sql_vs =  "SELECT c_form_type
            FROM ".DbConstant::KPHIS_DBNAME.".ipd_dr_admission_note
            WHERE ipd_dr_admission_note.an=:an  LIMIT 1";
$stmt_vs = $conn->prepare($sql_vs);
$stmt_vs->execute(['an'=>$an]);
$row_vs  = $stmt_vs->fetch();

//echo $row_vs['c_form_type'];

if ($row_vs['c_form_type'] == '1'){
    //$mpdf->WriteHTML($head0);
   $mpdf->WriteHTML($head11);
} else if ($row_vs['c_form_type'] === '2'){
   // $mpdf->WriteHTML($head0);
   $mpdf->WriteHTML($head1);
}

$sql2 = "SELECT *
                FROM `ipd_nurse_admission_note`
                WHERE an = :an";
$stmt = $conn->prepare($sql2);
$stmt->execute(['an'=>$an]);
if ($row  = $stmt->fetch()) {
    //$admission_note_id = $row['admission_note_id'];
    $mpdf->WriteHTML($head2);
} else {
   // $admission_note_id = null;
}

$sql3 = "SELECT *
                FROM `prs_pre_nursenote`
                WHERE an = :an";
$stmt = $conn->prepare($sql3);
$stmt->execute(['an'=>$an]);
if ($row  = $stmt->fetch()) {
    //$admission_note_id = $row['admission_note_id'];
    $mpdf->WriteHTML($head3);
} else {
   // $admission_note_id = null;
}

$sql4 = "SELECT *
                FROM `prs_lr_report2`
                WHERE an = :an";
$stmt = $conn->prepare($sql4);
$stmt->execute(['an'=>$an]);
if ($row  = $stmt->fetch()) {
    //$admission_note_id = $row['admission_note_id'];
    $mpdf->WriteHTML($head4);
} else {
   // $admission_note_id = null;
}


//$mpdf->WriteHTML($head2);
//$mpdf->WriteHTML($head3);
//$mpdf->WriteHTML($head4);
//$mpdf->WriteHTML($head4);
// Add a page break or new page if necessary
//$mpdf->AddPage();
//$mpdf->WriteHTML($head3);

$mpdf->Output();
?>