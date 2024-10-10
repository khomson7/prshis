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
$limit = 4;  // Show 7 days per page

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

//echo $page ;

$sql = "select t.*,t2.level_of_consciousness as shift from
(SELECT an,date(create_datetime) as date,'' as head_
,if(question1_1 = 1 ,'ล','-') as question1_1,if(question1_2 = 1 ,'ล','-') as question1_2,if(question1_3 > 0 ,'ด','-') as question1_3
,if(question1_4 > 0 ,'ด','-') as question1_4,if(question1_5 > 0 ,'ด','-') as question1_5
,case when variation1 = 0 then 'ข' when (variation1 > 0 and variation1 <=2) then 'ล' when (variation1 > 2 ) then 'ด' else '' end as variation1
,if(question2_1 = 1 ,'ล','-') as question2_1,if(question2_2 = 1 ,'ล','-') as question2_2,if(question2_3 > 0 ,'ด','-') as question2_3
,if(question2_4 > 0 ,'ด','-') as question2_4,if(question2_5 > 0 ,'ด','-') as question2_5
,case when variation2 = 0 then 'ข' when (variation2 > 0 and variation2 <=2) then 'ล' when (variation2 > 2 ) then 'ด' else '' end as variation2
,if(question3_1 = 1 ,'ล','-') as question3_1,if(question3_2 = 1 ,'ล','-') as question3_2,if(question3_3 > 0 ,'ด','-') as question3_3
,if(question3_4 > 0 ,'ด','-') as question3_4,if(question3_5 > 0 ,'ด','-') as question3_5
,case when variation3 = 0 then 'ข' when (variation3 > 0 and variation3 <=2) then 'ล' when (variation3 > 2 ) then 'ด' else '' end as variation3
,if(question4_1 = 1 ,'ล','-') as question4_1,if(question4_2 = 1 ,'ล','-') as question4_2,if(question4_3 > 0 ,'ด','-') as question4_3
,if(question4_4 > 0 ,'ด','-') as question4_4,if(question4_5 > 0 ,'ด','-') as question4_5
,case when variation4 = 0 then 'ข' when (variation4 > 0 and variation4 <=2) then 'ล' when (variation4 > 2 ) then 'ด' else '' end as variation4
,'abcdef' as create_
FROM prs_mental_health3
WHERE an = :an
GROUP BY date(create_datetime)
ORDER BY date ASC
LIMIT :limit OFFSET :offset)t
LEFT JOIN prs_mental_health3 t2 on date(t2.create_datetime) = t.date and t2.an = t.an
";
$stmt = $conn->prepare($sql);

// Bind parameters
$stmt->bindParam(':an', $an); // Assuming $an is defined and contains the Admission Number
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT); // Bind limit
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
   // echo $date;
    // Group data by date and store shift values
    if (!isset($groupedData[$date])) {
        $groupedData[$date] = [
            '1' => ['question1_1' => '-', 'question1_2' => '-', 'question1_3' => '-', 'question1_4' => '-', 'question1_5' => '-', 'variation1' => '-'
            ,'question2_1' => '-', 'question2_2' => '-', 'question2_3' => '-', 'question2_4' => '-', 'question2_5' => '-', 'variation2' => '-'
            ,'question3_1' => '-', 'question3_2' => '-', 'question3_3' => '-', 'question3_4' => '-', 'question3_5' => '-', 'variation3' => '-'
            ,'question4_1' => '-', 'question4_2' => '-', 'question4_3' => '-', 'question4_4' => '-', 'question4_5' => '-', 'variation4' => '-' ,'create_' =>'-'   
        ],
            '2' => ['question1_1' => '-', 'question1_2' => '-', 'question1_3' => '-', 'question1_4' => '-', 'question1_5' => '-', 'variation1' => '-'
            ,'question2_1' => '-', 'question2_2' => '-', 'question2_3' => '-', 'question2_4' => '-', 'question2_5' => '-', 'variation2' => '-'
            ,'question3_1' => '-', 'question3_2' => '-', 'question3_3' => '-', 'question3_4' => '-', 'question3_5' => '-', 'variation3' => '-'
            ,'question4_1' => '-', 'question4_2' => '-', 'question4_3' => '-', 'question4_4' => '-', 'question4_5' => '-', 'variation4' => '-' ,'create_' =>'-'     
        ],
            '3' => ['question1_1' => '-', 'question1_2' => '-', 'question1_3' => '-', 'question1_4' => '-', 'question1_5' => '-', 'variation1' => '-'
            ,'question2_1' => '-', 'question2_2' => '-', 'question2_3' => '-', 'question2_4' => '-', 'question2_5' => '-', 'variation2' => '-'
            ,'question3_1' => '-', 'question3_2' => '-', 'question3_3' => '-', 'question3_4' => '-', 'question3_5' => '-', 'variation3' => '-'
            ,'question4_1' => '-', 'question4_2' => '-', 'question4_3' => '-', 'question4_4' => '-', 'question4_5' => '-', 'variation4' => '-','create_' =>'-'
            ]
        ];
    }
    $groupedData[$date][$shift] = [
        'question1_1' => $row['question1_1'],
        'question1_2' => $row['question1_2'],
        'question1_3' => $row['question1_3'],
        'question1_4' => $row['question1_4'],
        'question1_5' => $row['question1_5'],
        'variation1' => $row['variation1'],
        'question2_1' => $row['question2_1'],
        'question2_2' => $row['question2_2'],
        'question2_3' => $row['question2_3'],
        'question2_4' => $row['question2_4'],
        'question2_5' => $row['question2_5'],
        'variation2' => $row['variation2'],
        'question3_1' => $row['question3_1'],
        'question3_2' => $row['question3_2'],
        'question3_3' => $row['question3_3'],
        'question3_4' => $row['question3_4'],
        'question3_5' => $row['question3_5'],
        'variation3' => $row['variation3'],
        'question4_1' => $row['question4_1'],
        'question4_2' => $row['question4_2'],
        'question4_3' => $row['question4_3'],
        'question4_4' => $row['question4_4'],
        'question4_5' => $row['question4_5'],
        'variation4' => $row['variation4'],
        'create_' => $row['create_']
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
       
        .vertical-text {
            writing-mode: vertical-rl; /* Rotate text vertically */
            text-orientation: mixed;   /* Ensures characters remain upright */
            
          }

          .manual-vertical-text {
            line-height: 1.5em; /* Adjust spacing between characters */
            text-align: center; /* Optional: center the text in the cell */
            white-space: nowrap; /* Prevent unwanted line wrapping */
          }
    
    </style>
    <h2 style="text-align:right;font-size:8pt;">FM-PSY-002-00 ประกาศใช้ 8 พฤษภาคม 2567</h2>
    <h2 style="text-align:center;font-size:15pt;">แบบประเมินภาวะเสี่ยง (SAVE) &nbsp;'.htmlspecialchars(DbConstant::HOSPITAL_NAME).$check_report.'</h2>';

// Table header
$html .= '<table border="1" cellpadding="10" cellspacing="0">';
$html .= '<thead><tr><th rowspan="2">ภาวะเสี่ยง</th>';
foreach ($dates as $date) {
    $html .= '<th colspan="3">' . date('d/m/Y', strtotime($date)) . '</th>';
}
$html .= '</tr><tr>';
foreach ($dates as $date) {
    $html .= '<th>ด</th><th>ช</th><th>บ</th>';
}
$html .= '</tr></thead><tbody>';



    $html .= '<tr><td colspan="13"><b>เกณฑ์การประเมินผู้ป่วยเสี่ยงต่อการทำร้าย (S)</b></td></tr>'; // Adjust level as needed
// Table body
foreach (['question1_1', 'question1_2', 'question1_3', 'question1_4', 'question1_5', 'variation1'] as $questionIndex => $questionName) {
 
    $html .= '<tr>';
   if ($questionIndex === 0) {
        $html .= '<td>1.มีภาวะซึมเศร้า โดย 1 เดือนที่ผ่านมารวมถึงวันนี้รู้สึกหดหู่เศร้า หรือท้อแท้สิ้นหวัง รู้สึกไม่มีคุณค่า</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td>2. มีประวัติเคยพยายามฆ่าตัวตายภายใน 1 เดือน ก่อนมาโรงพยาบาล</td>';
    }elseif ($questionIndex === 2) {
        $html .= '<td>3. มีความคิด / พูดบ่นอยากตาย</td>';
    }elseif ($questionIndex === 3) {
        $html .= '<td>4.หลงผิดเกี่ยวกับการผิดบาป โทษตัวเองมีเสียงแว่วให้ทำร้ายตัวเอง</td>';
    }elseif ($questionIndex === 4) {
        $html .= '<td>5.มีพฤติกรรมทำร้ายตัวเอง</td>';
    }elseif ($questionIndex === 5) {
        $html .= '<td><b>ความรุนแรงระดับสี</b></td>';
    }  
    foreach ($dates as $date) {
        $html .= '<td>' . $groupedData[$date]['1'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['2'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['3'][$questionName] . '</td>';
    }

    $html .= '</tr>';  
}

$html .= '<tr><td colspan="13"><b>เกณฑ์การประเมินผู้ป่วยเสี่ยงต่อการได้รับอุบัติเหตุ (A)</b></td></tr>'; // Adjust level as needed
// Table body
foreach (['question2_1', 'question2_2', 'question2_3', 'question2_4', 'question2_5', 'variation2'] as $questionIndex => $questionName) {
 
    $html .= '<tr>';
   if ($questionIndex === 0) {
        $html .= '<td>1.ผู้ป่วยมีอายุ 60 ปีขึ้นไป และ/หรือ มีโรคประจำตัว</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td>2.ผู้ป่วยได้รับยา HAD หรือผู้ป่วยได้รับยาในกลุ่ม Benzodizepine ตามการประเมิน AWS Score หรือได้รับยาฉีด PRN มากกว่า 2 ครั้ง ใน 1 วัน</td>';
    }elseif ($questionIndex === 2) {
        $html .= '<td>3.ผู้ป่วยมีการถอนพิษสุรา</td>';
    }elseif ($questionIndex === 3) {
        $html .= '<td>4.ผู้ป่วยที่มีการทรงตัวไม่ดี มึนงง สับสน</td>';
    }elseif ($questionIndex === 4) {
        $html .= '<td>5.ผู้ป่วยมีอาการชักภายใน 1 เดือน</td>';
    }elseif ($questionIndex === 5) {
        $html .= '<td><b>ความรุนแรงระดับสี</b></td>';
    }  
    foreach ($dates as $date) {
        $html .= '<td>' . $groupedData[$date]['1'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['2'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['3'][$questionName] . '</td>';
    }

    $html .= '</tr>';  
}

$html .= '<tr><td colspan="13"><b>เกณฑ์การประเมินผู้ป่วยเสี่ยงต่อพฤติกรรมรุนแรง (V)</b></td></tr>'; // Adjust level as needed
// Table body
foreach (['question3_1', 'question3_2', 'question3_3', 'question3_4', 'question3_5', 'variation3'] as $questionIndex => $questionName) {
 
    $html .= '<tr>';
   if ($questionIndex === 0) {
        $html .= '<td>1.มีประวัติพฤติกรรมรุนแรง ปฏิเสธการเจ็บป่วย ไม่ให้ความร่วมมือในการรักษา</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td>2.ระแวง หลงผิดคิดว่ามีผู้อื่นมาทำร้าย</td>';
    }elseif ($questionIndex === 2) {
        $html .= '<td>3.มีการรับรู้ผิดปกติ เช่น มีหูแว่ว เห็นภาพหลอน</td>';
    }elseif ($questionIndex === 3) {
        $html .= '<td>4.มีพฤติกรรมรุนแรง</td>';
    }elseif ($questionIndex === 4) {
        $html .= '<td>5.ตาขวาง พูดเสียงดัง ดุด่าผู้อื่น ไม่รับฟัง</td>';
    }elseif ($questionIndex === 5) {
        $html .= '<td><b>ความรุนแรงระดับสี</b></td>';
    }  
    foreach ($dates as $date) {
        $html .= '<td>' . $groupedData[$date]['1'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['2'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['3'][$questionName] . '</td>';
    }

    $html .= '</tr>';  
}


$html .= '<tr><td colspan="13"><b> เกณฑ์การประเมินผู้ป่วยเสี่ยงต่อการหลบหนี (E)</b></td></tr>'; // Adjust level as needed
// Table body
foreach (['question4_1', 'question4_2', 'question4_3', 'question4_4', 'question4_5', 'variation4'] as $questionIndex => $questionName) {
 
    $html .= '<tr>';
   if ($questionIndex === 0) {
        $html .= '<td>1.มีประวัติพยายามหลบหนี ปฏิเสธการเจ็บป่วย ไม่อยู่โรงพยาบาล</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td>2.มีประวัติติดสารเสพติดและอยากยาเสพติด หรือ Admit ใน 7 วันแรก</td>';
    }elseif ($questionIndex === 2) {
        $html .= '<td>3.รบเร้าเรื่องกลับบ้านบ่อยๆ หรือ ขอให้โทรศัพท์ติดต่อญาติหรือไม่ได้กลับบ้านตามกำหนด พูดขู่ว่าจะหนี ขอออกนอกตึกบ่อยๆ</td>';
    }elseif ($questionIndex === 3) {
        $html .= '<td>4.มีพฤติกรรมบ่งชี้ถึงสัญญาณการเตือนว่าจะมีการหลบหนี้ เช่น จ้องมองประตูพยายามงัดแงะหาทางออก</td>';
    }elseif ($questionIndex === 4) {
        $html .= '<td>5.มีพฤติกรรมหลบหนี</td>';
    }elseif ($questionIndex === 5) {
        $html .= '<td><b>ความรุนแรงระดับสี</b></td>';
    } 
    foreach ($dates as $date) {
        $html .= '<td>' . $groupedData[$date]['1'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['2'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['3'][$questionName] . '</td>';
    }

    $html .= '</tr>';  
}

foreach (['create_'] as $questionIndex  => $questionName) {
    $html .= '<tr>';

    // Add a "Signature" label in the first row
    if ($questionIndex  === 0) {
        $html .= '<td>Signature</td>'; // Add the label for the signature
    }

    // Loop through the dates and insert the rotated signature values
    foreach ($dates as $date) {

        $value1 = $groupedData[$date]['1'][$questionName];
        $value2 = $groupedData[$date]['2'][$questionName];
        $value3 = $groupedData[$date]['3'][$questionName];

        $verticalText1 = implode('<br>', str_split($value1)); // Split the text into characters
        $verticalText2 = implode('<br>', str_split($value2));
        $verticalText3 = implode('<br>', str_split($value3));

        // Add the vertical text to the table cells
        $html .= '<td class="manual-vertical-text">' . $verticalText1 . '</td>';
        $html .= '<td class="manual-vertical-text">' . $verticalText2 . '</td>';
        $html .= '<td class="manual-vertical-text">' . $verticalText3 . '</td>';
    }

    $html .= '</tr>';  
}



$html .= '</tbody></table>';



$html .= '<h2 style="text-align:left;font-size:10pt;"><u>หมายเหตุ</u> กรณีไม่พบตามเกณฑ์ตามประเมินให้รับดับเขียว</h2>'; // Adjust level as needed



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
//แสดงข้อมุลอยู่ในช่วง ก่อน footer
$mpdf->setAutoTopMargin = 'stretch';
$mpdf->setAutoBottomMargin = 'stretch';

$mpdf->setFooter('HN: '.htmlspecialchars($hn).' AN: '.htmlspecialchars($an).' Page '.$page);
// Write the HTML content to the PDF
$mpdf->WriteHTML($html);

// Output the PDF to the browser
$mpdf->Output('Shift_Report_AN_' . $an . '_Page_' . $page . '.pdf', 'I'); // Inline display in browser