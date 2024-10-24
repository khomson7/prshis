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
(SELECT an,date(create_datetime) as date,'' as head_
,if(perception = 4 ,'/','') as perception4
,if(perception = 3 ,'/','') as perception3
,if(perception = 2 ,'/','') as perception2
,if(perception = 1 ,'/','') as perception1
,if(wetting_the_skin = 4 ,'/','') as wetting_the_skin4
,if(wetting_the_skin = 3 ,'/','') as wetting_the_skin3
,if(wetting_the_skin = 2 ,'/','') as wetting_the_skin2
,if(wetting_the_skin = 1 ,'/','') as wetting_the_skin1
,if(doing_activities = 4 ,'/','') as doing_activities4
,if(doing_activities = 3 ,'/','') as doing_activities3
,if(doing_activities = 2 ,'/','') as doing_activities2
,if(doing_activities = 1 ,'/','') as doing_activities1
,if(movement = 4 ,'/','') as movement4
,if(movement = 3 ,'/','') as movement3
,if(movement = 2 ,'/','') as movement2
,if(movement = 1 ,'/','') as movement1
,if(getting_food = 4 ,'/','') as getting_food4
,if(getting_food = 3 ,'/','') as getting_food3
,if(getting_food = 2 ,'/','') as getting_food2
,if(getting_food = 1 ,'/','') as getting_food1
,if(sarcasm = 3 ,'/','') as sarcasm3
,if(sarcasm = 2 ,'/','') as sarcasm2
,if(sarcasm = 1 ,'/','') as sarcasm1
,score
,create_user as create_
FROM " . DbConstant::KPHIS_DBNAME . ".prs_bedsores
WHERE an = :an
GROUP BY date(create_datetime)
ORDER BY date ASC
LIMIT :limit OFFSET :offset)t
LEFT JOIN " . DbConstant::KPHIS_DBNAME . ".prs_bedsores t2 on date(t2.create_datetime) = t.date and t2.an = t.an
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
$countQuery = "SELECT COUNT(DISTINCT date(create_datetime)) as total_days FROM " . DbConstant::KPHIS_DBNAME . ".prs_bedsores WHERE an = :an";
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
            '1' => ['perception4' => '-', 'perception3' => '-', 'perception2' => '-', 'perception1' => '-', 'getting_food' => '-', 'sarcasm' => '-','create_' =>'-'   
            ,'wetting_the_skin4' => '-', 'wetting_the_skin3' => '-', 'wetting_the_skin2' => '-', 'wetting_the_skin1' => '-'
            ,'doing_activities4' => '-', 'doing_activities3' => '-', 'doing_activities2' => '-', 'doing_activities1' => '-'
            ,'wetting_the_skin4' => '-', 'wetting_the_skin3' => '-', 'wetting_the_skin2' => '-', 'wetting_the_skin1' => '-'
            ,'movement4' => '-', 'movement3' => '-', 'movement2' => '-', 'movement1' => '-'
            ,'getting_food4' => '-', 'getting_food3' => '-', 'getting_food2' => '-', 'getting_food1' => '-'
            ,'sarcasm4' => '-', 'sarcasm3' => '-', 'sarcasm2' => '-', 'sarcasm1' => '-','score'=>''
        ],
            '2' => ['perception4' => '-', 'perception3' => '-', 'perception2' => '-', 'perception1' => '-', 'getting_food' => '-', 'sarcasm' => '-','create_' =>'-' 
            ,'wetting_the_skin4' => '-', 'wetting_the_skin3' => '-', 'wetting_the_skin2' => '-', 'wetting_the_skin1' => '-'
            ,'doing_activities4' => '-', 'doing_activities3' => '-', 'doing_activities2' => '-', 'doing_activities1' => '-'
            ,'movement4' => '-', 'movement3' => '-', 'movement2' => '-', 'movement1' => '-'
            ,'getting_food4' => '-', 'getting_food3' => '-', 'getting_food2' => '-', 'getting_food1' => '-'
            ,'sarcasm4' => '-', 'sarcasm3' => '-', 'sarcasm2' => '-', 'sarcasm1' => '-','score'=>''
        ],
            '3' => ['perception4' => '-', 'perception3' => '-', 'perception2' => '-', 'perception1' => '-', 'getting_food' => '-', 'sarcasm' => '-','create_' =>'-'
            ,'wetting_the_skin4' => '-', 'wetting_the_skin3' => '-', 'wetting_the_skin2' => '-', 'wetting_the_skin1' => '-'
            ,'doing_activities4' => '-', 'doing_activities3' => '-', 'doing_activities2' => '-', 'doing_activities1' => '-'
            ,'movement4' => '-', 'movement3' => '-', 'movement2' => '-', 'movement1' => '-'
            ,'getting_food4' => '-', 'getting_food3' => '-', 'getting_food2' => '-', 'getting_food1' => '-'
            ,'sarcasm4' => '-', 'sarcasm3' => '-', 'sarcasm2' => '-', 'sarcasm1' => '-','score'=>''
            ]
        ];
    }
    $groupedData[$date][$shift] = [
        'perception4' => $row['perception4'],
        'perception3' => $row['perception3'],
        'perception2' => $row['perception2'],
        'perception1' => $row['perception1'],
        'wetting_the_skin4' => $row['wetting_the_skin4'],
        'wetting_the_skin3' => $row['wetting_the_skin3'],
        'wetting_the_skin2' => $row['wetting_the_skin2'],
        'wetting_the_skin1' => $row['wetting_the_skin1'],
        'doing_activities4' => $row['doing_activities4'],
        'doing_activities3' => $row['doing_activities3'],
        'doing_activities2' => $row['doing_activities2'],
        'doing_activities1' => $row['doing_activities1'],
        'movement4' => $row['movement4'],
        'movement3' => $row['movement3'],
        'movement2' => $row['movement2'],
        'movement1' => $row['movement1'],
        'getting_food4' => $row['getting_food4'],
        'getting_food3' => $row['getting_food3'],
        'getting_food2' => $row['getting_food2'],
        'getting_food1' => $row['getting_food1'],
        'sarcasm3' => $row['sarcasm3'],
        'sarcasm2' => $row['sarcasm2'],
        'sarcasm1' => $row['sarcasm1'],
        'score' => $row['score'],
        'create_' => $row['create_']
    ];
}


        $ids = '23'; //Link menu
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
    <h2 style="text-align:right;font-size:8pt;"></h2>
    <h2 style="text-align:center;font-size:15pt;">แบบประเมินความเสี่ยงต่อการเกิดแผลกดทับ &nbsp;'.htmlspecialchars(DbConstant::HOSPITAL_NAME).$check_report.'</h2>'
    .'<div><b>กลุ่มเป้าหมาย</b> ผู้ป่วยทุกรายที่เข้าพักการรักษาตัวโรงพยาบาล ยกเว้น ผู้ป่วยแผนกกุมารเวชกรรม สูติกรรม (ห้องคลอด,<br />หลังคลอด) ต้องได้รับการประเมินคะแนนความเสี่ยงต่อการเกิดแผลกดทับตามแบบ Braden Scale โดยกำหนด ให้บันทึกค่าคะแนนความเสี่ยง
    ต่อการเกิดแผลในแบบประเมินสมรรถนะแรกรับหรือภายใน 24 ชั่วโมง<br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;คะแนนรวม  <= 16 คะแนน จัดเป็นกลุ่มเสี่ยงต้องประเมินความเสี่ยงต่อการเกิดแผลทุกวันและได้รับการดูแลตามมาตรฐานที่กำหนด<br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;คะแนนรวม  &gt; 16 คะแนน ประเมินซ้ำเมื่อมีปัจจัยเสี่ยงอย่างใดอย่างหนึ่งใน 6 ปัจจัยลดลงอย่างน้อย 1 คะแนน<br />
    ส่วนที่ 1 แบบประเมิน Braden scale &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ทำเครื่องหมาย / ในช่องและรวมคะแนน
    </div>';

// Table header
$html .= '<table border="1" cellpadding="10" cellspacing="0">';
$html .= '<thead><<tr><th rowspan="2" >คะแนน Braden Scale</th>';
$html .= '<th>ว.ด.ป</th>';

foreach ($dates as $date) {
    
    $html .= '<th colspan="3">' . date('d/m/Y', strtotime($date)) . '</th>';
}
$html .= '</tr><tr>';
$html .= '<th>เวลา</th>';

foreach ($dates as $date) {
    $html .= '<th>ด</th><th>ช</th><th>บ</th>';
}
$html .= '</tr></thead><tbody>';

$html.=' <tr><td colspan="'.htmlspecialchars($colspan).'">&nbsp;<b>การรับรู้</b></td>';
// Table body
foreach (['perception4', 'perception3', 'perception2', 'perception1'] as $questionIndex => $questionName) {
 
    $html .= '<tr>';
   if ($questionIndex === 0) {
        $html .= '<td>ปกติ</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td>สับสน สื่อสารไม่ได้บางครั้ง</td>';
    }elseif ($questionIndex === 2) {
        $html .= '<td>ตอบสนองความเจ็บปวด</td>';
    }elseif ($questionIndex === 3) {
        $html .= '<td>ไม่ตอบสนอง</td>';
    }


    if ($questionIndex === 0) {
        $html .= '<td style="text-align:center;">4</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td style="text-align:center;">3</td>';
    }elseif ($questionIndex === 2) {
        $html .= '<td style="text-align:center;">2</td>';
    }elseif ($questionIndex === 3) {
        $html .= '<td style="text-align:center;">1</td>';
    }

    foreach ($dates as $date) {
        $html .= '<td>' . $groupedData[$date]['1'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['2'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['3'][$questionName] . '</td>';
    }

    $html .= '</tr>';  

}

$html.=' <tr><td colspan="'.htmlspecialchars($colspan).'">&nbsp;<b>การเปียกชุ่มของผิวหนัง</b></td>';
// Table body
foreach (['wetting_the_skin4', 'wetting_the_skin3', 'wetting_the_skin2', 'wetting_the_skin1'] as $questionIndex => $questionName) {
 
    $html .= '<tr>';
   if ($questionIndex === 0) {
        $html .= '<td>ปกติ</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td>เปียกชุ่มบางครั้ง</td>';
    }elseif ($questionIndex === 2) {
        $html .= '<td>เปียกชุ่มบ่อยครั้ง</td>';
    }elseif ($questionIndex === 3) {
        $html .= '<td>เปียกชุ่มตลอดเวลา</td>';
    }


    if ($questionIndex === 0) {
        $html .= '<td style="text-align:center;">4</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td style="text-align:center;">3</td>';
    }elseif ($questionIndex === 2) {
        $html .= '<td style="text-align:center;">2</td>';
    }elseif ($questionIndex === 3) {
        $html .= '<td style="text-align:center;">1</td>';
    }

    foreach ($dates as $date) {
        $html .= '<td>' . $groupedData[$date]['1'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['2'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['3'][$questionName] . '</td>';
    }

    $html .= '</tr>';  

}


$html.=' <tr><td colspan="'.htmlspecialchars($colspan).'">&nbsp;<b>การทำกิจกรรม</b></td>';
// Table body
foreach (['doing_activities4', 'doing_activities3', 'doing_activities2', 'doing_activities1'] as $questionIndex => $questionName) {
 
    $html .= '<tr>';
   if ($questionIndex === 0) {
        $html .= '<td>ปกติ</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td>เดินได้ระยะสั้น/ต้องพยุง</td>';
    }elseif ($questionIndex === 2) {
        $html .= '<td>ทรงตัวไม่อยู่ ใช้รถเข็น</td>';
    }elseif ($questionIndex === 3) {
        $html .= '<td>อยู่บนเตียงตลอดเวลา</td>';
    }


    if ($questionIndex === 0) {
        $html .= '<td style="text-align:center;">4</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td style="text-align:center;">3</td>';
    }elseif ($questionIndex === 2) {
        $html .= '<td style="text-align:center;">2</td>';
    }elseif ($questionIndex === 3) {
        $html .= '<td style="text-align:center;">1</td>';
    }

    foreach ($dates as $date) {
        $html .= '<td>' . $groupedData[$date]['1'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['2'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['3'][$questionName] . '</td>';
    }

    $html .= '</tr>';  

}



$html.=' <tr><td colspan="'.htmlspecialchars($colspan).'">&nbsp;<b>การเคลื่อนไหว</b></td>';
// Table body
foreach (['movement4', 'movement3', 'movement2', 'movement1'] as $questionIndex => $questionName) {
 
    $html .= '<tr>';
   if ($questionIndex === 0) {
        $html .= '<td>ปกติ</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td>เปลี่ยนท่าได้บ่อยครั้ง</td>';
    }elseif ($questionIndex === 2) {
        $html .= '<td>เปลี่ยนท่าได้บางครั้ง</td>';
    }elseif ($questionIndex === 3) {
        $html .= '<td>เปลี่ยนท่าไม่ได้</td>';
    }


    if ($questionIndex === 0) {
        $html .= '<td style="text-align:center;">4</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td style="text-align:center;">3</td>';
    }elseif ($questionIndex === 2) {
        $html .= '<td style="text-align:center;">2</td>';
    }elseif ($questionIndex === 3) {
        $html .= '<td style="text-align:center;">1</td>';
    }

    foreach ($dates as $date) {
        $html .= '<td>' . $groupedData[$date]['1'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['2'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['3'][$questionName] . '</td>';
    }

    $html .= '</tr>';  

}


$html.=' <tr><td colspan="'.htmlspecialchars($colspan).'">&nbsp;<b>การได้รับอาหาร</b></td>';
// Table body
foreach (['getting_food4', 'getting_food3', 'getting_food2', 'getting_food1'] as $questionIndex => $questionName) {
 
    $html .= '<tr>';
   if ($questionIndex === 0) {
        $html .= '<td>ปกติ</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td>Feed ได้หมด/กินได้ > 1/2 ถาด</td>';
    }elseif ($questionIndex === 2) {
        $html .= '<td>Feed ได้บ้าง/กินได้ 1/2 ถาด</td>';
    }elseif ($questionIndex === 3) {
        $html .= '<td>NPO / กินได้ < 1/3 ถาด</td>';
    }


    if ($questionIndex === 0) {
        $html .= '<td style="text-align:center;">4</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td style="text-align:center;">3</td>';
    }elseif ($questionIndex === 2) {
        $html .= '<td style="text-align:center;">2</td>';
    }elseif ($questionIndex === 3) {
        $html .= '<td style="text-align:center;">1</td>';
    }

    foreach ($dates as $date) {
        $html .= '<td>' . $groupedData[$date]['1'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['2'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['3'][$questionName] . '</td>';
    }

    $html .= '</tr>';  

}


$html.=' <tr><td colspan="'.htmlspecialchars($colspan).'">&nbsp;<b>การเสียดสี</b></td>';
// Table body
foreach (['sarcasm3', 'sarcasm2', 'sarcasm1','score'] as $questionIndex => $questionName) {
 
    $html .= '<tr>';
   if ($questionIndex === 0) {
        $html .= '<td>ไม่มีปัญหา</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td>เสียดสี / ลื่นไถลได้</td>';
    }elseif ($questionIndex === 2) {
        $html .= '<td>กล้ามเนื้อหดเกร็ง</td>';
    }elseif ($questionIndex === 3) {
        $html .= '<td> คะแนน ( 5 -23)</td>';
    }


    if ($questionIndex === 0) {
        $html .= '<td style="text-align:center;">3</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td style="text-align:center;">2</td>';
    }elseif ($questionIndex === 2) {
        $html .= '<td style="text-align:center;">1</td>';
    }elseif ($questionIndex === 3) {
        $html .= '<td style="text-align:center;"></td>';
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

    $html .= '<td>ผู้ประเมิน</td>';
    // Add a "Signature" label in the first row
    
    if ($questionIndex  === 0) {
        $html .= '<td></td>'; // Add the label for the signature
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



$html .= '<h2 style="text-align:left;font-size:10pt;"><u>สรุปก่อนจำหน่าย / ย้าย</u> ผู้ป่วยรายนี้ '.$check1.' ไม่เกิดแผล '.$check2.' เกิดแผลระดับ '.$check1_value.'.............จำนวน......................<br> 
</h2>
<tr style="border:1px solid #000;margin: 35px;">
            <td  colspan="3" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">
            <label>HN : '.htmlspecialchars($row_ipt['hn']).' | AN : '.htmlspecialchars($an).'</label>
            <label>ชื่อ - สกุล : '.htmlspecialchars($row_ipt['pname'].$row_ipt['fname']." ".$row_ipt['lname']).' | </label>
            <label>อายุ : '.htmlspecialchars($row_ipt['age_y']." ปี ".$row_ipt['age_m']." เดือน ".$row_ipt['age_d']." วัน ").' | </label>
            <label>ตึก : '.htmlspecialchars($row_ipt['name']).' | </label>
            <label>เตียง : '.htmlspecialchars($row_ipt['bedno']).' | </label>
            <label>สิทธิ : ('.htmlspecialchars($row_ipt['pttype']).') '.htmlspecialchars($row_ipt['pttype_name']).'</label>
            </td>
        </tr>
'; // Adjust level as needed



// Pagination links in the PDF (optional)
$html .= '<div style="text-align: center; margin-top: 20px;">';
if ($page > 1) {
    $html .= '<a href="bedscore-pdf.php?an=' . $an . '&page=' . ($page - 1) . '">Previous Page</a> | ';
}
$html .= 'Page ' . $page . ' of ' . $totalPages;
if ($page < $totalPages) {
    $html .= ' | <a href="bedscore-pdf.php?an=' . $an . '&page=' . ($page + 1) . '">Next Page</a>';
    
}
$html .= '</div>';
//แสดงข้อมุลอยู่ในช่วง ก่อน footer
$mpdf->setAutoTopMargin = 'stretch';
$mpdf->setAutoBottomMargin = 'stretch';

$mpdf->setFooter('ชื่อ - สกุล : '.htmlspecialchars($row_ipt['pname'].$row_ipt['fname']." ".$row_ipt['lname']).' HN: '.htmlspecialchars($hn).' AN: '.htmlspecialchars($an).' Page '.$page);
// Write the HTML content to the PDF
$mpdf->WriteHTML($html);

// Output the PDF to the browser
$mpdf->Output('Shift_Report_AN_' . $an . '_Page_' . $page . '.pdf', 'I'); // Inline display in browser