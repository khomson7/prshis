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
    </style> ';
/*$head1 = '<h2 style="text-align:right;font-size:8pt;">FM-ICU-005-00</h2>
    <h2 style="text-align:center;font-size:11pt;">Form1 FANCAS &nbsp;';*/

// Add the content of the second file here
require_once '../ipdnurse2/pdf1.php';

$head2 = '
    <h2 style="text-align:right;font-size:8pt;">FM-ICU-005-00</h2>
    <h2 style="text-align:center;font-size:11pt;">Form2 FANCAS &nbsp;';

// Set the footer and write the content
$mpdf->setFooter('HN: '.htmlspecialchars($hn).' AN: '.htmlspecialchars($an).' Page '.'{PAGENO}');
$mpdf->WriteHTML($head0);
$mpdf->WriteHTML($head1);
// Add a page break or new page if necessary
$mpdf->AddPage();
$mpdf->WriteHTML($head2);

$mpdf->Output();
?>