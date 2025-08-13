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
$query_parameters2 = ['an' => $an,'id' => $id];

Session::insertSystemAccessLog(json_encode(array(
    'report'=>'MENTAL-HEALTH1-PDF',
   // 'action'=>'PRINT',
    'an'=>$an,
),JSON_UNESCAPED_UNICODE));

$sql = "SELECT pn.*,date(create_datetime) as rxdate
FROM prs_signature_sign pn
WHERE pn.an = :an 
limit 1";
$stmt = $conn->prepare($sql);
$stmt->execute($query_parameters);
$row  = $stmt->fetch();



$sql2 = "SELECT s.*,u.signature,o.name as user_
FROM " . DbConstant::KPHIS_DBNAME . ".prs_signature_sign s
LEFT OUTER JOIN " . DbConstant::KPHIS_DBNAME . ".users u on u.username = s.create_user
LEFT OUTER JOIN " . DbConstant::HOSXP_DBNAME . ".opduser o on o.loginname = s.create_user
WHERE s.an=:an
ORDER BY s.id ASC";
$parameters['an'] = $an;
$stmt = $conn->prepare($sql2);
$stmt->execute($parameters);
$row2 = $stmt->fetch();

$signature  =  $row2['signature'];

//echo $signature;

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

        .container {
            display: flex;
            justify-content: space-between;
        }
        .column {
            flex: 1;
            padding: 10px;
            margin: 5px;
            border: 1px solid #ccc;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            border: 1px solid #ccc;
            padding: 10px;

        }

        
    </style>
    <h2 style="text-align:right;font-size:8pt;">&nbsp;</h2>
    
    <h2 style="text-align:center;font-size:11pt;">แบบลงลายมือชื่อ &nbsp;'.htmlspecialchars(DbConstant::HOSPITAL_NAME).$check_report.'</h2>'
    .'<div style="text-align: center; margin-top: 30px;">
    <div style="position: relative; display: inline-block;">
        <img src="'.$signature.'" width="100" style="position: absolute; top: -35px; left: 50%; transform: translateX(-50%);">
        
        <div style="font-weight:bold; font-size:13px;">แพทย์เจ้าของไข้</div>
    </div>
</div>'
    .'<br>'.'<footter> <h2 style="text-align:right;font-size:8pt;">ประกาศใช้ 29 มกราคม 2566 งานเอกสารคุณภาพ ศูนย์คุณภาพ</h2> </footer>' ;

    //แสดงข้อมุลอยู่ในช่วง ก่อน footer
$mpdf->setAutoTopMargin = 'stretch';
$mpdf->setAutoBottomMargin = 'stretch';

$mpdf->setFooter('HN: '.htmlspecialchars($hn).' AN: '.htmlspecialchars($an).' Page '.'{PAGENO}');
$mpdf->WriteHTML($head);
$mpdf->Output();