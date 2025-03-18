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
    'report'=>'PRE-BEDSCORE-PDF',
   // 'action'=>'PRINT',
    'an'=>$an,
),JSON_UNESCAPED_UNICODE));


$image_uncheck = "<img src='../include/images/check-adm.jpg' width='1.6%' class='check_img'>";
$image_check = "<img src='../include/images/check-1.jpg' width='1.6%' class='check_img'>";

$sql = "SELECT *
        FROM `prs_rehab_history`
        WHERE an = :an";
$stmt = $conn->prepare($sql);
$stmt->execute($query_parameters);
$row  = $stmt->fetch();


$sql1 = "SELECT *
        FROM `prs_rehab_progression`
        WHERE an = :an";
$stmt = $conn->prepare($sql1);
$stmt->execute($query_parameters);
$row1  = $stmt->fetch();


$sql_ipt = "select patient.sex,patient.hn,patient.pname,patient.fname,patient.lname,patient.drugallergy,
            (select GROUP_CONCAT(concat(opd_allergy.agent,'=',if(opd_allergy.symptom is null,',',opd_allergy.symptom)/*,' [',if(note is null,',',note),']'*/)) as name
                from ".DbConstant::HOSXP_DBNAME.".opd_allergy
                where opd_allergy.hn = ipt.hn /*and (opd_allergy.no_alert<>'Y' or opd_allergy.no_alert is null)*/
                order by display_order) as allergy_symptom_string,
            an_stat.age_y,an_stat.age_m,an_stat.age_d,
            ipt.regdate,ipt.regtime,
            ipt.ward,ward.name,
            ipt.pttype, pttype.`name` as pttype_name,
            iptadm.bedno, (select vs.bw from ipd_vs_vital_sign vs where vs.an = ipt.an and vs.bw is not null and trim(vs.bw) <> '' order by vs_datetime desc limit 1) as latest_bw
            , (select vs.height from ipd_vs_vital_sign vs where vs.an = ipt.an and vs.bw is not null and trim(vs.bw) <> '' order by vs_datetime desc limit 1) as latest_height
            from ".DbConstant::HOSXP_DBNAME.".ipt
            left outer join ".DbConstant::HOSXP_DBNAME.".an_stat on an_stat.an=ipt.an
            left outer join ".DbConstant::HOSXP_DBNAME.".patient on patient.hn=ipt.hn
            left outer join ".DbConstant::HOSXP_DBNAME.".ward on ward.ward=ipt.ward
            LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".pttype ON pttype.pttype = ipt.pttype
            LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".iptadm ON iptadm.an = ipt.an
            WHERE ipt.an=:an";
        $stmt_ipt = $conn->prepare($sql_ipt);
        $stmt_ipt->execute(['an'=>$an]);
        $row_ipt = $stmt_ipt->fetch();
        $regdatetime = $row_ipt['regdate'].' '.$row_ipt['regtime'];//ใช้ในการดึงข้อมูล ประวัติการผ่าตัด
//-------------------------Doctor admission note

// Pagination variables
$limit = 4;  // Show 7 days per page

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

//echo $page ;

$sql = "select t.*,t2.work_shift as shift from
(SELECT an,date(create_datetime) as date
,if(consciousness = 5 ,5,'') as consciousness1
,if(consciousness = 1 ,0,'') as consciousness2
,if(slip_and_fall = 2 ,2,'') as slip_and_fall1
,if(slip_and_fall = 1 ,0,'') as slip_and_fall2
,if(age_check = 2 ,2,'') as age_check1
,if(age_check = 1 ,0,'') as age_check2
,if(get_medicine = 3 ,3,'') as get_medicine1
,if(get_medicine = 1 ,0,'') as get_medicine2
,if(body = 3 ,3,'') as body1
,if(body = 1 ,0,'') as body2
,if(assessment = 2 ,2,'') as assessment1
,if(assessment = 1 ,0,'') as assessment2
,if(excretion = 1,1,'') as excretion1
,if(excretion = 9 ,0,'') as excretion2
,if(after_birth = 1 ,1,'') as after_birth1
,if(after_birth = 9 ,0,'') as after_birth2
,if(surgery = 1 ,1,'') as surgery1
,if(surgery = 9 ,0,'') as surgery2
,score
,create_user as create_
FROM " . DbConstant::KPHIS_DBNAME . ".prs_felldown
WHERE an = :an
GROUP BY date(create_datetime)
ORDER BY date ASC
LIMIT :limit OFFSET :offset)t
LEFT JOIN " . DbConstant::KPHIS_DBNAME . ".prs_felldown t2 on date(t2.create_datetime) = t.date and t2.an = t.an
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


foreach ($rows as $row) {

$check1 = '( )';
if ($row['score'] > 0) {
    $check1 = '('.$image_check.')';
    $check1_value  =  htmlspecialchars($row['score']);
}
$check2 = '( )';
if ($row['score'] == 0) {
    $check2 = '('.$image_check.')';
    
}

}
// Output the results (for debugging or display)
/*foreach ($rows as $row) {
    echo "AN: {$row['an']}, Date: {$row['date']}, Shift: {$row['shift']}, Question1: {$row['question1_1']}, Question2: {$row['question1_2']}, Question3: {$row['question1_3']}<br>";
}
*/

// Get total number of days for this AN
$total_colspan = 0;
$countQuery = "SELECT COUNT(DISTINCT date(create_datetime)) as total_days FROM " . DbConstant::KPHIS_DBNAME . ".prs_felldown WHERE an = :an";
$countStmt = $conn->prepare($countQuery);
$countStmt->execute($query_parameters);
$totalDays = $countStmt->fetchColumn();
$totalPages = ceil($totalDays / $limit);
$total_colspan = ceil($totalDays * 3);

$colspan = 2 + $total_colspan;


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
            '1' => ['consciousness1' => '','consciousness2' => '', 'slip_and_fall1' => '','slip_and_fall2' => '', 'age_check1' => '', 'age_check2' => '','get_medicine1' => '','get_medicine2' => '', 'body1' => '', 'body2' => '','create_' =>'-'   
            ,'assessment1' => '','assessment2' => '', 'excretion1' => '', 'excretion2' => '','after_birth1' => '','after_birth2' => '', 'surgery1' => '','surgery2' => '','score'=>''
        ],
            '2' => ['consciousness1' => '','consciousness2' => '', 'slip_and_fall1' => '','slip_and_fall2' => '', 'age_check1' => '', 'age_check2' => '', 'get_medicine1' => '','get_medicine2' => '', 'body1' => '', 'body2' => '','create_' =>'-' 
            ,'assessment1' => '','assessment2' => '', 'excretion1' => '', 'excretion2' => '','after_birth1' => '','after_birth2' => '', 'surgery1' => '','surgery2' => '','score'=>''
        ],
            '3' => ['consciousness1' => '','consciousness2' => '', 'slip_and_fall1' => '','slip_and_fall2' => '', 'age_check1' => '', 'age_check2' => '', 'get_medicine1' => '','get_medicine2' => '', 'body1' => '', 'body2' => '','create_' =>'-'
            ,'assessment1' => '','assessment2' => '', 'excretion1' => '', 'excretion2' => '', 'after_birth1' => '','after_birth2' => '', 'surgery1' => '','surgery2' => '','score'=>''
            ]
        ];
    }
    $groupedData[$date][$shift] = [
        'consciousness1' => $row['consciousness1'],
        'consciousness2' => $row['consciousness2'],
        'slip_and_fall1' => $row['slip_and_fall1'],
        'slip_and_fall2' => $row['slip_and_fall2'],
        'age_check1' => $row['age_check1'],
        'age_check2' => $row['age_check2'],
        'get_medicine1' => $row['get_medicine1'],
        'get_medicine2' => $row['get_medicine2'],
        'body1' => $row['body1'],
        'body2' => $row['body2'],
        'assessment1' => $row['assessment1'],
        'assessment2' => $row['assessment2'],
        'excretion1' => $row['excretion1'],
        'excretion2' => $row['excretion2'],
        'after_birth1' => $row['after_birth1'],
        'after_birth2' => $row['after_birth2'],
        'surgery1' => $row['surgery1'],
        'surgery2' => $row['surgery2'],
        'score' => $row['score'],
        'create_' => $row['create_']
    ];
}


        $ids = '27'; //Link menu
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
 
        font-size: 7px; 
        
      }
      div.line_dotted {
        text-decoration: underline dotted;  
        text-decoration-color: rgb(105,42,49); 
        font-size: 8px;
        text-decoration-style: dotted;  
      }

        body{
            font-family: "Garuda";//เรียกใช้font Garuda สำหรับแสดงผล ภาษาไทย
            font-size: 8px;  /* Set the font size for the entire body */
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
          table td, table th {
            font-size: 9pt;
        }
    
    </style>
    <h2 style="text-align:right;font-size:8pt;"></h2>
    <h2 style="text-align:center;font-size:12pt;">แบบบันทึกการให้บริการทางกายภาพบำบัด &nbsp;'.htmlspecialchars(DbConstant::HOSPITAL_NAME).$check_report.'</h2>';

$html .= '<div><label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ชื่อ - สกุล : '.htmlspecialchars($row_ipt['pname'].$row_ipt['fname']." ".$row_ipt['lname']).' HN : '.htmlspecialchars($row_ipt['hn']).  

'</label><br>'

.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;อาการสำคัญ :&nbsp;'.nl2br(htmlspecialchars($row['cc']))
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ประวัติปัจจุบัน :&nbsp;'.nl2br(htmlspecialchars($row['hpi']))
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ประวัติการเจ็บป่วยในอดีต :&nbsp;'.nl2br(htmlspecialchars($row['past_history']))
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;สภาพจิตใจและสังคม :&nbsp;'.nl2br(htmlspecialchars($row['phychosocial']))
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;โรคประจำตัว :&nbsp;'.nl2br(htmlspecialchars($row['disease']))
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;การรักษาที่เคยได้รับ :&nbsp;'.nl2br(htmlspecialchars($row['treatment_received']))
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;การตรวจประเมินแรกรับ วัน-เดือน-ปี :&nbsp;'.nl2br(htmlspecialchars($row['pe_1st']))
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;การวินิจฉัยโรคทางกายภาพบำบัด :&nbsp;'.nl2br(htmlspecialchars($row['diagnosis']))
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>เป้าหมายและการวางแผนการรักษา :</b>&nbsp;'
.'</div>'
.'<table border="1" cellpadding="10" cellspacing="0">
<thead>
<tr>   
    <th>
    <label> <b>วัน-เดือน-ปี</b></label>
    </th>
    <th>
    <label> <b>Goal</b></label>
    </th>
    <th>
    <label> <b>Due date</b></label>
    </th>
    <th>
    <label> <b>Treatment plan</b></label>
    </th>
</tr> 
<thead>
<tbody>
<tr style="border:1px solid #000;margin: 35px;"> /* ชื่อ-สกุล */
    
    <th style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">
    <label>'.nl2br(htmlspecialchars($row['goal_date'])).'</label>
    </th>
    <td style="border-right:0.5px solid #000;margin: 20px;padding:4px;vertical-align:text-top;">
    <label>'.nl2br(htmlspecialchars($row['goal'])).'</label>
    </td>
    <td style="border-right:0.5px solid #000;margin: 20px;padding:4px;vertical-align:text-top;">
    <label>'.nl2br(htmlspecialchars($row['due_date'])).'</label>
    </td>
    <td style="border-right:0.5px solid #000;margin: 20px;padding:4px;vertical-align:text-top;">
    <label>'.nl2br(htmlspecialchars($row['treatment_plan'])).'</label>
    </td>
</tr> 
</tbody>
</table>'
.'<div><label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>การรักษา</b>';


    try {

        $sql1 = "SELECT ph.*,o.name as user_fullname FROM  ".DbConstant::KPHIS_DBNAME.".prs_rehab_progression ph
                left outer join ".DbConstant::HOSXP_DBNAME.".opduser o on o.loginname = ph.create_user
            WHERE ph.an=:an"; // Your SQL query
    $stmt = $conn->prepare($sql1);
    
    // Bind parameters (replace `:an` with actual value)
    $query_parameters = array(':an' => $an);  // For example, $an is your value
    
    $stmt->execute($query_parameters);
    
    // 3. Fetch data
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all rows as associative array
    

    if (count($rows) > 0) {
        // 4. Build the HTML table string
       // $html = '';  // Initialize HTML string
        $html .= '<table style="width:100%" border="1" cellpadding="10" cellspacing="0">';  // Start table

        // Display first date
        $html .= '<thead><tr>';
        $html .= '<th>วัน-เดือน-ปี</th>';
        $html .= '<th>PE-Rx- Progression note - Home/Ward program</th>';
        $html .= '</tr></thead>';


        for ($i = 0; $i < count($rows); $i++) {
       
        // Display phone numbers
        $html .= '<tr>';
        $html .= '<td rowspan="4">'.  htmlspecialchars($rows[$i]['rxdate']) .'</td>';  // Phone rowspan = 2
        $html .= '<td> PE: ' . htmlspecialchars($rows[$i]['pe']) . '</td>';

        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td> RX: ' . htmlspecialchars($rows[$i]['rx']) . '</td>';  // Second phone
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td> Progression note: ' . htmlspecialchars($rows[$i]['progress_note']) . '</td>';  // Second phone
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td> Home/Ward program: ' . htmlspecialchars($rows[$i]['home_ward_program']) . ' <br><br><span style="text-align: right; font-size: 9pt;"> ลายมือชื่อ: ' . htmlspecialchars($rows[$i]['user_fullname']) . '</span></td>';
        $html .= '</tr>';

    }

        $html .= '</table>';  // End of table

        // 5. Output the HTML table
       // echo $html;
    } else {
        echo "No results found";
    }



    

} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
    
    // Close connection (optional with PDO)
    $conn = null;


//แสดงข้อมุลอยู่ในช่วง ก่อน footer
$mpdf->setAutoTopMargin = 'stretch';
$mpdf->setAutoBottomMargin = 'stretch';

$mpdf->setFooter('ชื่อ - สกุล : '.htmlspecialchars($row_ipt['pname'].$row_ipt['fname']." ".$row_ipt['lname']).' HN: '.htmlspecialchars($hn).' AN: '.htmlspecialchars($an).' Page '.$page);
// Write the HTML content to the PDF
$mpdf->WriteHTML($html);

// Output the PDF to the browser
$mpdf->Output('Shift_Report_AN_' . $an . '_Page_' . $page . '.pdf', 'I'); // Inline display in browser