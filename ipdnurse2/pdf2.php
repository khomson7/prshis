<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

date_default_timezone_set('asia/bangkok');

$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
$mpdf->AddPageByArray([
    'margin-left' => 6,
    'margin-right' => 6,
    'margin-top' => 6,
    'margin-bottom' => 6,
]);

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

        
    </style>

    <h2 style="text-align:right;font-size:8pt;">FM-ICU-005-00</h2>
    
    <h2 style="text-align:center;font-size:11pt;">Form2 FANCAS &nbsp;';

$mpdf->setFooter(' Page '.'{PAGENO}');
$mpdf->WriteHTML($head);
$mpdf->Output();

