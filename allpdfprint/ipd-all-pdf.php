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
  require_once '../include/KphisQueryUtils.php';
        $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
        $an = $_REQUEST['an'];
        $hn = KphisQueryUtils::getHnByAn($an);   // function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา
       // require_once __DIR__ . '../vendor/autoload.php';
        date_default_timezone_set('asia/bangkok');
        $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
        $mpdf->AddPageByArray([
            'margin-left' => 8,
            'margin-right' => 8,
            'margin-top' => 8,
            'margin-bottom' => 5,
        ]);

        Session::insertSystemAccessLog(json_encode(array(
            'report'=>'IPD-ALL-PDF',
           // 'action'=>'PRINT',
            'an'=>$an,
        ),JSON_UNESCAPED_UNICODE));

//echo $an ;
//require_once '../ipdnurse/ipd-dr-admission-note-pdf.php';

require_once '../allpdfprint/report1.php';

require_once '../allpdfprint/report2.php';




?>