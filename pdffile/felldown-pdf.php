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
$countQuery = "SELECT COUNT(DISTINCT date(create_datetime)) as total_days FROM " . DbConstant::KPHIS_DBNAME . ".prs_child_felldown WHERE an = :an";
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


        $ids = '26'; //Link menu
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
 
        font-size: 8px; 
        
      }
      div.line_dotted {
        text-decoration: underline dotted;  
        text-decoration-color: rgb(105,42,49); 
        font-size: 8px;
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
          table td, table th {
            font-size: 10pt;
        }
    
    </style>
    <h2 style="text-align:right;font-size:8pt;"></h2>
    <h2 style="text-align:center;font-size:12pt;">แบบประเมินภาวะเสี่ยงต่อการพลัดตก หกล้ม (ผู้ป่วยผู้ใหญ่) &nbsp;'.htmlspecialchars(DbConstant::HOSPITAL_NAME).$check_report.'</h2>';

// Table header
$html .= '<table border="1" cellpadding="10" cellspacing="0">';
$html .= '<thead><tr><th rowspan="2" >ปัจจัยเสี่ยง</th>';
$html .= '<th>ว.ด.ป</th>';

foreach ($dates as $date) {
    
    $html .= '<th colspan="3">' . date('d/m/Y', strtotime($date)) . '</th>';
}
$html .= '</tr><tr>';
$html .= '<th>เวลา <br /> คะแนน</th>';

foreach ($dates as $date) {
    $html .= '<th>ด</th><th>ช</th><th>บ</th>';
}
$html .= '</tr></thead><tbody>';

$html.=' <tr><td colspan="'.htmlspecialchars($colspan).'">&nbsp;<b>1. ผู้ป่วยมีระดับความรู้สึกตัว</b></td>';
// Table body
foreach (['consciousness1', 'consciousness2'] as $questionIndex => $questionName) {
 
    $html .= '<tr>';
   if ($questionIndex === 0) {
        $html .= '<td>ผู้ป่วยรับรู้ลดลง</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td>ผู้ป่วยรู้สึกตัวดี</td>';
    }


    if ($questionIndex === 0) {
        $html .= '<td style="text-align:center;">5</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td style="text-align:center;">0</td>';
    }

    foreach ($dates as $date) {
        $html .= '<td>' . $groupedData[$date]['1'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['2'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['3'][$questionName] . '</td>';
    }

    $html .= '</tr>';  

}

$html.=' <tr><td colspan="'.htmlspecialchars($colspan).'">&nbsp;<b>2. ประวัติชัก/ลื่นล้ม/ตกเตียง</b></td>';
// Table body
foreach (['slip_and_fall1', 'slip_and_fall2'] as $questionIndex => $questionName) {
 
    $html .= '<tr>';
   if ($questionIndex === 0) {
        $html .= '<td>มีประวัติชัก/ลื่นล้ม/ตกเตียงภายใน 1 ปี</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td>ไม่มีประวัติชัก/ลื่นล้ม/ตกเตียงภายใน 1 ปี</td>';
    }


    if ($questionIndex === 0) {
        $html .= '<td style="text-align:center;">2</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td style="text-align:center;">0</td>';
    }

    foreach ($dates as $date) {
        $html .= '<td>' . $groupedData[$date]['1'][$questionName] . '</td>';
        $html .= '<td >' . $groupedData[$date]['2'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['3'][$questionName] . '</td>';
    }

    $html .= '</tr>';  

}


$html.=' <tr><td colspan="'.htmlspecialchars($colspan).'">&nbsp;<b>3. อายุ</b></td>';
// Table body
foreach (['age_check1', 'age_check2'] as $questionIndex => $questionName) {
 
    $html .= '<tr>';
   if ($questionIndex === 0) {
        $html .= '<td>อายุ 65 ปี หรือมากกว่า/ผู้ป่วยเด็กและทารก</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td>อายุ 15 - 65 ปี</td>';
    }


    if ($questionIndex === 0) {
        $html .= '<td style="text-align:center;">2</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td style="text-align:center;">0</td>';
    }

    foreach ($dates as $date) {
        $html .= '<td>' . $groupedData[$date]['1'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['2'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['3'][$questionName] . '</td>';
    }

    $html .= '</tr>';  

}



$html.=' <tr><td colspan="'.htmlspecialchars($colspan).'">&nbsp;<b>4. การได้รับยา</b></td>';
// Table body
foreach (['get_medicine1', 'get_medicine2'] as $questionIndex => $questionName) {
 
    $html .= '<tr>';
   if ($questionIndex === 0) {
        $html .= '<td>ได้รับยาที่มีผลต่อความดันโลหิต ระดับความรู้สึกตัวมีผลทำให้ง่วง ซึม</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td>ไม่ได้รับยาที่มีผลต่อความดันโลหิต ระดับความรู้สึกตัวมีผลทำให้ง่วง ซึม</td>';
    }


    if ($questionIndex === 0) {
        $html .= '<td style="text-align:center;">3</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td style="text-align:center;">0</td>';
    }

    foreach ($dates as $date) {
        $html .= '<td>' . $groupedData[$date]['1'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['2'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['3'][$questionName] . '</td>';
    }

    $html .= '</tr>';  

}


$html.=' <tr><td colspan="'.htmlspecialchars($colspan).'">&nbsp;<b>5. มีสภาพการทำงานของร่างกาย</b></td>';
// Table body
foreach (['body1', 'body2'] as $questionIndex => $questionName) {
 
    $html .= '<tr>';
   if ($questionIndex === 0) {
        $html .= '<td>กล้ามเนื้ออ่อนแรง การทรงตัวไม่ดี ซีดจาก (Hct Hb) ต่ำ อ่อนเพลีย อีเล็กโตรไลท์ผิดปกติ มีปัญหาด้านสายตา มีปัญหาการเคลื่อนไหวรวมถึง การใช้อุปกรณ์ช่วยเดิน</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td>ผู้ป่วยมีการเคลื่อนไหวปกติ สติสัมปชัญญะปกติ</td>';
    }


    if ($questionIndex === 0) {
        $html .= '<td style="text-align:center;">3</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td style="text-align:center;">0</td>';
    }

    foreach ($dates as $date) {
        $html .= '<td>' . $groupedData[$date]['1'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['2'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['3'][$questionName] . '</td>';
    }

    $html .= '</tr>';  

}


$html.=' <tr><td colspan="'.htmlspecialchars($colspan).'">&nbsp;<b>6. การประเมินตนเอง</b></td>';
// Table body
foreach (['assessment1', 'assessment2'] as $questionIndex => $questionName) {
 
    $html .= '<tr>';
   if ($questionIndex === 0) {
        $html .= '<td>ประเมินตนเองไม่เหมาะสม/ไม่อยู่บนเตียง ตามคำแนะนำ</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td>ประเมินตนเองเหมาะสม/อยู่บนเตียง ตามคำแนะนำ</td>';
    }

    if ($questionIndex === 0) {
        $html .= '<td style="text-align:center;">2</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td style="text-align:center;">0</td>';
    }
    foreach ($dates as $date) {
        $html .= '<td>' . $groupedData[$date]['1'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['2'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['3'][$questionName] . '</td>';
    }

    $html .= '</tr>';  

}


$html.=' <tr><td colspan="'.htmlspecialchars($colspan).'">&nbsp;<b>7. การขับถ่าย</b></td>';
// Table body
foreach (['excretion1', 'excretion2'] as $questionIndex => $questionName) {
 
    $html .= '<tr>';
   if ($questionIndex === 0) {
        $html .= '<td>มีความต้องการการช่วยเหลือในการขับถ่าย</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td>ช่วยเหลือตนเองเกี่ยวกับการขับถ่ายได้ดี</td>';
    }

    if ($questionIndex === 0) {
        $html .= '<td style="text-align:center;">1</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td style="text-align:center;">0</td>';
    }
    foreach ($dates as $date) {
        $html .= '<td>' . $groupedData[$date]['1'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['2'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['3'][$questionName] . '</td>';
    }

    $html .= '</tr>';  

}

$html.=' <tr><td colspan="'.htmlspecialchars($colspan).'">&nbsp;<b>8. หลังคลอด</b></td>';
// Table body
foreach (['after_birth1', 'after_birth2'] as $questionIndex => $questionName) {
 
    $html .= '<tr>';
   if ($questionIndex === 0) {
        $html .= '<td>อยู่ในภาวะหลังคลอดภายใน 4 - 6 ชั่วโมง</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td>หลังคลอดมากกว่า 6 ชั่วโมง</td>';
    }

    if ($questionIndex === 0) {
        $html .= '<td style="text-align:center;">1</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td style="text-align:center;">0</td>';
    }
    foreach ($dates as $date) {
        $html .= '<td>' . $groupedData[$date]['1'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['2'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['3'][$questionName] . '</td>';
    }

    $html .= '</tr>';  

}

$html.=' <tr><td colspan="'.htmlspecialchars($colspan).'">&nbsp;<b>9. หลังผ่าตัด</b></td>';
// Table body
foreach (['surgery1', 'surgery2'] as $questionIndex => $questionName) {
 
    $html .= '<tr>';
   if ($questionIndex === 0) {
        $html .= '<td>อยู่ในภาวะหลังผ่าตัดภายใน 24 ชั่วโมง</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td>หลังผ่าตัดมากกว่า 24 ชั่วโมง</td>';
    }

    if ($questionIndex === 0) {
        $html .= '<td style="text-align:center;">1</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td style="text-align:center;">0</td>';
    }
    foreach ($dates as $date) {
        $html .= '<td>' . $groupedData[$date]['1'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['2'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['3'][$questionName] . '</td>';
    }

    $html .= '</tr>';  

}

foreach (['score'] as $questionIndex  => $questionName) {
    $html .= '<tr>';

    $html .= '<td><b>คะแนนรวม</b></td>';
    // Add a "Signature" label in the first row
    
    if ($questionIndex  === 0) {
        $html .= '<td style="text-align:center;"><b>20</b></td>'; // Add the label for the signature
    }

   

    // Loop through the dates and insert the rotated signature values
    foreach ($dates as $date) {

        $html .= '<td><b>' . $groupedData[$date]['1'][$questionName] . '</b></td>';
        $html .= '<td><b>' . $groupedData[$date]['2'][$questionName] . '</b></td>';
        $html .= '<td><b>' . $groupedData[$date]['3'][$questionName] . '</b></td>';
    }

    $html .= '</tr>';  
}




foreach (['create_'] as $questionIndex  => $questionName) {
    $html .= '<tr>';

    $html .= '<td>ผู้บันทึก</td>';
    // Add a "Signature" label in the first row
    
    if ($questionIndex  === 0) {
        $html .= '<td style="text-align:center;"></td>'; // Add the label for the signature
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



$html .= '
<div> คะแนน 0 - 1 = ไม่มีปัจจัยเสี่ยง (no risk) &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;   คะแนน 2 - 4 = มีปัจจัยเสี่ยงต่ำ (low risk)   
        </div>
        <div> คะแนน 2 5 = มีปัจจัยเสี่ยงสูง (high risk)  
        </div>
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