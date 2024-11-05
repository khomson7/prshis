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
    'report'=>'MENTAL-HEALTH2-PDF',
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

$sql = "select t.*,'1' as shift from
(SELECT *,date(create_datetime) as date,'' as head_
FROM " . DbConstant::KPHIS_DBNAME . ".prs_mental_health2
WHERE an = :an
GROUP BY date(create_datetime)
ORDER BY date ASC
LIMIT :limit OFFSET :offset)t
LEFT JOIN " . DbConstant::KPHIS_DBNAME . ".prs_mental_health2 t2 on date(t2.create_datetime) = t.date and t2.an = t.an
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
$countQuery = "SELECT COUNT(DISTINCT date(create_datetime)) as total_days FROM " . DbConstant::KPHIS_DBNAME . ".prs_mental_health2 WHERE an = :an";
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
            '1' => ['somatic_concern' => '-' ,'anxiety' => '-','emotional'=>'-','conceptual'=>'-','guilt'=>'-','tension'=>'-','mannerism'=>'-','grandiosity'=>'-'
            ,'depressive'=>'-','hostility'=>'-','suspiciousness'=>'-','hallucinatory'=>'-','motor'=>'-','uncooperativeness'=>'-','unusual'=>'-','blunted'=>'-'
            ,'excitement'=>'-','disorientation'=>'-','total_sum'=>'-','create_user' =>'-'   
        ]
        ];
    }
    $groupedData[$date][$shift] = [
        'somatic_concern' => $row['somatic_concern'],   
        'anxiety' => $row['anxiety'],   
        'emotional' => $row['emotional'], 
        'conceptual' => $row['conceptual'],
        'guilt' => $row['guilt'],  
        'tension' => $row['tension'],
        'mannerism' => $row['mannerism'],
        'grandiosity' => $row['grandiosity'],
        'depressive' => $row['depressive'],
        'hostility' => $row['hostility'],
        'suspiciousness' => $row['suspiciousness'],
        'hallucinatory' => $row['hallucinatory'],
        'motor' => $row['motor'],
        'uncooperativeness' => $row['uncooperativeness'],
        'unusual' => $row['unusual'],
        'blunted' => $row['blunted'],
        'excitement' => $row['excitement'],
        'disorientation' => $row['disorientation'],
        'total_sum' => $row['total_sum'],
        'create_user' => $row['create_user']
    ];
}


        $ids = '21'; //Link menu
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
 
        font-size: 10px; 
        
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

          .fontsize {
            font-size:11px;
          }
    
    </style>
    <h3 style="text-align:right;font-size:8pt;">FM-PSY-002-00 ประกาศใช้ 8 พฤษภาคม 2567</h3>
    <h3 style="text-align:center;font-size:10pt;">แบบประเมินอาการทางจิต(Brief Phychiatric Rating Scale : BRPS) <br>'.htmlspecialchars(DbConstant::HOSPITAL_NAME).$check_report.'</h3>';

// Table header
$html .= '<table class="fontsize" border="1" cellpadding="10" cellspacing="0">';
$html .= '<thead><tr><th>หัวข้อ</th> <th>อาการและการแสดงออก</th>';
foreach ($dates as $date) {
    $html .= '<th>' . date('d/m/Y', strtotime($date)) . '</th>';
}
/*$html .= '</tr><tr>';
foreach ($dates as $date) {
    $html .= '<th></th>';
} */
$html .= '</tr></thead><tbody>';



    
// Table body
foreach (['somatic_concern', 'anxiety', 'emotional', 'conceptual', 'guilt', 'tension', 'mannerism','grandiosity','depressive','hostility','suspiciousness'
,'hallucinatory','motor','uncooperativeness','unusual','blunted','excitement','disorientation','total_sum'] as $questionIndex => $questionName) {
 
    $html .= '<tr>';
   if ($questionIndex === 0) {
        $html .= '<td style="text-align:center;">1</td><td>Somatic_concern (G) คุณรู้สึกตนเองป่วยเป็นโรคทางกายภาพหรือไม่</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td style="text-align:center;">2</td><td> Anxiety (G) ใน 1 สัปดาห์ที่ผ่านมาคุณรู้สึกกังวลหรือกลัวอะไรบ้างไหม/ความคิดนี้รบกวนจตใจบ่อยไหม /รู้สึกมีการใจสั่น เหงื่อออก/อาการที่บอก มีผลต่อการทำงานของคุณไหม</td>';
    }elseif ($questionIndex === 2) {
        $html .= '<td style="text-align:center;">3</td><td> Emotional Withdrawal (N) มีลักษณะแยกตัว ไม่ค่อยมีปฏิกิริยาโต้ตอบกับ ผู้อื่น ไม่แสดงอารมณ์ หน้าเฉยเมย</td>';
    }elseif ($questionIndex === 3) {
        $html .= '<td style="text-align:center;">4</td><td>Conceptual disorganization (P) พูดไม่เป็นเรื่องราว ขาดการเชื่อโยง พูดอ้อมค้อม ไม่ค่อยต่อเนื่อง (ดูใน 15 นาทีแรก)</td>';
    }elseif ($questionIndex === 4) {
        $html .= '<td style="text-align:center;">5</td><td>Guilt Feeling (G) รู้สึกตำหนิตนเองในสิ่งที่ทำไม่ดี หรือเสียใจต่อสิ่งที่ทำในอดีตหรือไม่</td>';
    }elseif ($questionIndex === 5) {
        $html .= '<td style="text-align:center;">6</td><td>Tension (G) มองจากท่านั่งรู้สึกตึงเครียด ขณะพูดอาจมีการกระดก เสียงสั่น</td>';
    }elseif ($questionIndex === 6) {
        $html .= '<td style="text-align:center;">7</td><td>Mannerism and posturing (G) มีท่าทางการเคลื่อนไหวไม่เป็นธรรมชาติเก้งก้าง แข็ง ดู แปลกๆ</td>';
    }elseif ($questionIndex === 7) {
        $html .= '<td style="text-align:center;">8</td><td>Grandiosity (P) คุณมีความรู้สึกมีอำนาจพิเศษบางอย่างหรือไม่/ที่ผ่านมาคิดเป็นใครที่มีชื่อเสียงหรือไม่</td>';
    }elseif ($questionIndex === 8) {
        $html .= '<td style="text-align:center;">9</td><td>Depressive mood (G) คุณรู้สึกว่าไม่มีความสุขหรือความเศร้า/รู้สึกเศร้าไหม/รู้สึกเศร้าบ่อยแค่ไหน สามารถเบนความสนใจไปในเรื่องที่ทำให้รู้สึกได้ไหม/ความรู้สึกรบกวนการทำงานของคุณไหม</td>';
    }elseif ($questionIndex === 9) {
        $html .= '<td style="text-align:center;">10</td><td>Hostility (P) ใน 1 สัปดาห์ที่ผ่านมา คุณรู้สึกหงุดหงิดหรืออารมณ์เสียบ่อยๆเคยมีปัญหาชกต่อย หรือทะเลาะกับคนอื่น/สัมพันธภาพกับคนอื่น คนในครอบครัว เพื่อนร่วมงานเป็นอย่างไร</td>';
    }elseif ($questionIndex === 10) {
        $html .= '<td style="text-align:center;">11</td><td>Suspiciousness (P) คุณรู้สึกมีคนคอยจับผิด มีคนคิดร้ายบ้างไหม/โดยวิธีใด/รู้สึกกังวลกับการคิดร้ายของใครบ้างไหม</td>';
    }elseif ($questionIndex === 11) {
        $html .= '<td style="text-align:center;">12</td><td>Hallucinatory behavior(P) คุณได้ยินเสียงหรือมีคนพูดโดยไม่เห็นตัวตนหรือไม่ คุณมองเห็นหรือได้กลิ่นอะไรบางอย่างโดยคนอื่นไม่รู้สึก / ประสบการณ์นี้มีผลกระทบต่อชีวิตประจำวันไหม</td>';
    }elseif ($questionIndex === 12) {
        $html .= '<td style="text-align:center;">13</td><td>Motor retardation (G) การพูด การเคลื่อนไหวเชื่องช้า (สังเกตพฤติกรรม)</td>';
    }elseif ($questionIndex === 13) {
        $html .= '<td style="text-align:center;">14</td><td>Uncooperativeness (G) มีท่าทีต่อต้าน ระมัดระวัง ไม่เป้นมิตรต่อผู้อื่นและ ผู้ตรวจ</td>';
    }elseif ($questionIndex === 14) {
        $html .= '<td style="text-align:center;">15</td><td>Unusual thought content (G) ความคิดแปลก เช่น มีความคิดเชื่อเรื่องพลังจิต วิญญาณ หากพบในข้อ Somatic Grandiosity Delusion จะพบในหัวข้อนี้ด้วย</td>';
    }elseif ($questionIndex === 15) {
        $html .= '<td style="text-align:center;">16</td><td>Blunted affect (N) สีหน้าไม่ค่อยสดงความรู้สึก อารมณ์</td>';
    }elseif ($questionIndex === 16) {
        $html .= '<td style="text-align:center;">17</td><td>Excitement (P) มีท่าทีลุกลี้ลุกลน มีปฏิกิริยาโต้ตอบเร็ว อยู่ไม่เป็นสุข</td>';
    }elseif ($questionIndex === 17) {
        $html .= '<td style="text-align:center;">18</td><td>Disorientation (G) ถามวันที่ สถานที่ เวลา บุคคล</td>';
    }elseif ($questionIndex === 18) {
        $html .= '<td style="text-align:right;"></td><td style="text-align:right;">คะแนนรวม</td>';
    }elseif ($questionIndex === 19) {
        $html .= '<td></td><td><b>ss</b></td>';
    }  
    foreach ($dates as $date) {
        $html .= '<td style="text-align:center;">' . $groupedData[$date]['1'][$questionName] . '</td>';
    }

    $html .= '</tr>';  
}



foreach (['create_user'] as $questionIndex  => $questionName) {
    $html .= '<tr>';

    // Add a "Signature" label in the first row
    if ($questionIndex  === 0) {
        $html .= '<td style="text-align:right;font-size:8pt;" colspan="2">ผู้บันทึก</td>'; // Add the label for the signature
    }

    // Loop through the dates and insert the rotated signature values
    foreach ($dates as $date) {

        $value1 = $groupedData[$date]['1'][$questionName];

        $verticalText1 = implode('<br>', str_split($value1)); // Split the text into characters
        // Add the vertical text to the table cells
        $html .= '<td class="manual-vertical-text">' . $verticalText1 . '</td>';

    }

    $html .= '</tr>';  
}



$html .= '</tbody></table>';



$html .= '<div style="text-align:left;font-size:8pt;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Rating risk 1 = ไม่มีอาการ
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2 = มีเล็กน้อยเป็นบางครั้ง
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3 = มีอาการเล็กน้อย
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;4 = อาการปานกลาง
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;5 = อาการค่อนข้างรุนแรง
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;6 = อาการรุนแรง
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;7 = อาการรุนแรงมาก
</div>'; // Adjust level as needed



// Pagination links in the PDF (optional)
$html .= '<div style="text-align: center; margin-top: 20px;">';
if ($page > 1) {
    $html .= '<a href="mental-health2-pdf.php?an=' . $an . '&page=' . ($page - 1) . '">Previous Page</a> | ';
}
$html .= 'Page ' . $page . ' of ' . $totalPages;
if ($page < $totalPages) {
    $html .= ' | <a href="mental-health2-pdf.php?an=' . $an . '&page=' . ($page + 1) . '">Next Page</a>';
    
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