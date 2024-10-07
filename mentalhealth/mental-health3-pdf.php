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
    'report'=>'PRE-NURSENOTE-PDF',
   // 'action'=>'PRINT',
    'an'=>$an,
),JSON_UNESCAPED_UNICODE));


$image_uncheck = "<img src='../include/images/check-adm.jpg' width='1.6%' class='check_img'>";
$image_check = "<img src='../include/images/check-1.jpg' width='1.6%' class='check_img'>";
//-------------------------Doctor admission note

// Pagination variables
$limit = 3;  // Show 7 days per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;



$sql = "SELECT an,create_datetime as date,level_of_consciousness as shift
,if(question1_1 = 1 ,'/','-') as question1_1,if(question1_2 = 1 ,'/','-') as question1_2,if(question1_3 > 0 ,'/','-') as question1_3
FROM prs_mental_health3
WHERE an = :an
ORDER BY date ASC
LIMIT 1000 OFFSET :offset
";
$stmt = $conn->prepare($sql);

// Bind parameters
$stmt->bindParam(':an', $an); // Assuming $an is defined and contains the Admission Number
//$stmt->bindParam(':limit', $limit, PDO::PARAM_INT); // Bind limit
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT); // Bind offset

// Execute the statement
$stmt->execute();

// Fetch all rows for the current page
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Output the results (for debugging or display)
/*foreach ($rows as $row) {
    echo "AN: {$row['an']}, Date: {$row['date']}, Shift: {$row['shift']}, Question1: {$row['question1_1']}, Question2: {$row['question1_2']}, Question3: {$row['question1_3']}<br>";
}
*/

// Get total number of days for this AN
$countQuery = "SELECT COUNT(DISTINCT create_datetime) as total_days FROM prs_mental_health3 WHERE an = :an";
$countStmt = $conn->prepare($countQuery);
$countStmt->execute($query_parameters);
$totalDays = $countStmt->fetchColumn();
$totalPages = ceil($totalDays / $limit);

//echo $totalDays;
// Group data by date and shift
$groupedData = [];
$dates = [];

foreach ($rows as $row) {
    $date = $row['date'];
    $shift = $row['shift'];
    
    // Store date for headers
    if (!in_array($date, $dates)) {
        $dates[] = $date;
    }
    
    // Group data by date and store shift values
    if (!isset($groupedData[$date])) {
        $groupedData[$date] = [
            '1' => ['question1_1' => '-', 'question1_2' => '-', 'question1_3' => '-'],
            '2' => ['question1_1' => '-', 'question1_2' => '-', 'question1_3' => '-'],
            '3' => ['question1_1' => '-', 'question1_2' => '-', 'question1_3' => '-']
        ];
    }
    $groupedData[$date][$shift] = [
        'question1_1' => $row['question1_1'],
        'question1_2' => $row['question1_2'],
        'question1_3' => $row['question1_3']
    ];
}


        $ids = '22'; //Link menu
        $check_    = ReportQueryUtils::getProduction($ids);

        $check_report = '( )';
        if ($check_  == '1') 
        {$check_report = '&nbsp;<font color="red">รอปรับรายงาน</font>';
        } else {
            $check_report = '';
        }



      
        $icu_form1 = "<img src='../include/images/icu-form1.png' width='100%' >";
        $icu_form2 = "<img src='../include/images/icu-form2.png' width='100%' >";
        $icu_form3 = "<img src='../include/images/icu-form3.png' width='100%' >";
        
       // $maxNrOfPages = ceil($max/$itemsPerPage);
     

$html0 =
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
    <h2 style="text-align:right;font-size:8pt;">&nbsp;</h2>
    
    <h2 style="text-align:center;font-size:11pt;">แบบประเมินภาวะเสี่ยง (SAVE) &nbsp;'.htmlspecialchars(DbConstant::HOSPITAL_NAME).$check_report.'</h2>
    
   
<div class="f15"> วันที่ <b>'.LongDateThai2($strDate).'<br>'
.'<div class="row">

                        <div class="col-12 col-md-12">                              
                                        <table lass="center" id="bg-table" width="100%" style="border-collapse: collapse;font-size:8pt;margin-top:2px;">                                              
                                        <tr style="border:1px solid #000;margin: 35px;">
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;<b>ภาวะเสี่ยง</b></td>
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;<b>ระดับรุนแรง</b></td>
                                                </tr>

                                                <tr style="border:1px solid #000;margin: 45px;">
                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%" colspan="2">&nbsp;<b>เกณฑ์การประเมินผู้ป่วยเสี่ยงต่อการทำร้าย (S)</b></td>
                                                </tr>

                                                <tr style="border:1px solid #000;margin: 45px;">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;1.มีภาวะซึมเศร้า โดย 1 เดือนที่ผ่านมารวมถึงวันนี้รู้สึกหดหู่เศร้า หรือท้อแท้สิ้นหวัง รู้สึกไม่มีคุณค่า</td>



                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-checkbox  col-sm-1">
                                                                        <input type="checkbox" class="custom-control-input" id="question1_1" value="1" name="question1_1" oninput="oninputcheckValue1()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="question1_1" style="font-size:100%; background-color:yellow;">เหลือง</label>
                                                                </div>

                                                        </td>





                                                </tr>


                                                </table></div></div>'




      .'<br>'                     
    .'<footter> <h2 style="text-align:right;font-size:8pt;">ประกาศใช้ 29 มกราคม 2566 งานเอกสารคุณภาพ ศูนย์คุณภาพ</h2> </footer>' ;
//$mpdf->SetColumns(2);


$html =
'<style>
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

        
    </style><h2 style="text-align:right;font-size:8pt;">&nbsp;</h2>
    
    <h2 style="text-align:center;font-size:11pt;">แบบประเมินภาวะเสี่ยง (SAVE) &nbsp;'.htmlspecialchars(DbConstant::HOSPITAL_NAME).$check_report.'</h2>
    
   
<div class="f15"> วันที่ <b>'.LongDateThai2($strDate).'<br>';
// Start generating the HTML content for the PDF
$html = '
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
    <h2 style="text-align:right;font-size:8pt;">&nbsp;</h2>
    <h2 style="text-align:center;font-size:15pt;">แบบประเมินภาวะเสี่ยง (SAVE) &nbsp;'.htmlspecialchars(DbConstant::HOSPITAL_NAME).$check_report.'</h2>'.
'<h2 style="text-align:center;font-size:11pt;">ทดสอบtest SAVE AN ' . $an . '</h2>';

// Table header
$html .= '<table border="1" cellpadding="10" cellspacing="0">';
$html .= '<thead><tr><th rowspan="2">Level</th>';
foreach ($dates as $date) {
    $html .= '<th colspan="3">' . date('d/m/Y', strtotime($date)) . '</th>';
}
$html .= '</tr><tr>';
foreach ($dates as $date) {
    $html .= '<th>ด</th><th>ช</th><th>บ</th>';
}
$html .= '</tr></thead><tbody>';

// Table body
foreach (['question1_1', 'question1_2', 'question1_3'] as $questionIndex => $questionName) {
    $html .= '<tr>';
    if ($questionIndex === 0) {
        $html .= '<td>A</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td>B</td>';
    } else {
        $html .= '<td>C</td>';
    }


    foreach ($dates as $date) {
        $html .= '<td>' . $groupedData[$date]['1'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['2'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['3'][$questionName] . '</td>';
    }

    $html .= '</tr>';
}

$html .= '</tbody></table>';

// Pagination links in the PDF (optional)
$html .= '<div style="text-align: center; margin-top: 20px;">';
if ($page > 1) {
    $html .= '<a href="mental-health3-pdf.php?an=' . $an . '&page=' . ($page - 1) . '">Previous Page</a> | ';
}
$html .= 'Page ' . $page . ' of ' . $totalPages;
if ($page < $totalPages) {
    $html .= ' | <a href="mental-health3-pdf.php?an=' . $an . '&page=' . ($page + 1) . '">Next Page</a>';
    
}
$html .= '</div>';

$mpdf->setFooter('HN: '.htmlspecialchars($hn).' AN: '.htmlspecialchars($an).' Page '.$page);
// Write the HTML content to the PDF
$mpdf->WriteHTML($html);

// Output the PDF to the browser
$mpdf->Output('Shift_Report_AN_' . $an . '_Page_' . $page . '.pdf', 'I'); // Inline display in browser