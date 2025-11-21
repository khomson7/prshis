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
,if(age_check = 4 ,4,'') as age_check4
,if(age_check = 3 ,3,'') as age_check3
,if(age_check = 2 ,2,'') as age_check2
,if(age_check = 1 ,1,'') as age_check1
,if(sex = 2 ,2,'') as sex2
,if(sex = 1 ,1,'') as sex1
,if(diag = 4 ,4,'') as diag4
,if(diag = 3 ,3,'') as diag3
,if(diag = 2 ,2,'') as diag2
,if(diag = 1 ,1,'') as diag1
,if(knowledge = 3 ,3,'') as knowledge3
,if(knowledge = 2 ,2,'') as knowledge2
,if(knowledge = 1 ,1,'') as knowledge1
,if(environmental = 4 ,4,'') as environmental4
,if(environmental = 3 ,3,'') as environmental3
,if(environmental = 2 ,2,'') as environmental2
,if(environmental = 1 ,1,'') as environmental1
,if(after_surgery = 3 ,3,'') as after_surgery3
,if(after_surgery = 2 ,2,'') as after_surgery2
,if(after_surgery = 1 ,1,'') as after_surgery1
,if(drug_use = 3 ,3,'') as drug_use3
,if(drug_use = 2 ,2,'') as drug_use2
,if(drug_use = 1 ,1,'') as drug_use1,score
,create_user as create_
FROM " . DbConstant::KPHIS_DBNAME . ".prs_child_felldown
WHERE an = :an
GROUP BY date(create_datetime)
ORDER BY date ASC
LIMIT :limit OFFSET :offset)t
LEFT JOIN " . DbConstant::KPHIS_DBNAME . ".prs_child_felldown t2 on date(t2.create_datetime) = t.date and t2.an = t.an
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
            '1' => ['age_check4' => '','age_check3' => '','age_check2' => '','age_check1' => ''
            ,'sex2' => '','sex1' => '' ,'diag4' => '','diag3' => '' ,'diag2' => '','diag1' => ''
            ,'knowledge3'=>'','knowledge2'=>'','knowledge1'=>'','environmental4'=>'','environmental3'=>'','environmental2'=>'','environmental1'=>''
            ,'after_surgery3'=>'','after_surgery2'=>'','after_surgery1'=>'','drug_use3'=>'','drug_use2'=>'','drug_use1'=>'','score'=>''
        ],
            '2' => ['age_check4' => '','age_check3' => '','age_check2' => '','age_check1' => ''
            ,'sex2' => '','sex1' => '' ,'diag4' => '','diag3' => '' ,'diag2' => '','diag1' => ''
            ,'knowledge3'=>'','knowledge2'=>'','knowledge1'=>'','environmental4'=>'','environmental3'=>'','environmental2'=>'','environmental1'=>''
            ,'after_surgery3'=>'','after_surgery2'=>'','after_surgery1'=>'','drug_use3'=>'','drug_use2'=>'','drug_use1'=>'','score'=>''
        ],
            '3' => ['age_check4' => '','age_check3' => '','age_check2' => '','age_check1' => ''
            ,'sex2' => '','sex1' => '' ,'diag4' => '','diag3' => '' ,'diag2' => '','diag1' => ''
            ,'knowledge3'=>'','knowledge2'=>'','knowledge1'=>'','environmental4'=>'','environmental3'=>'','environmental2'=>'','environmental1'=>''
            ,'after_surgery3'=>'','after_surgery2'=>'','after_surgery1'=>'','drug_use3'=>'','drug_use2'=>'','drug_use1'=>'','score'=>''
            ]
        ];
    }
    $groupedData[$date][$shift] = [
        'age_check4' => $row['age_check4'],
        'age_check3' => $row['age_check3'],
        'age_check2' => $row['age_check2'],
        'age_check1' => $row['age_check1'],
        'sex1' => $row['sex1'],
        'sex2' => $row['sex2'],
        'diag4' => $row['diag4'],
        'diag3' => $row['diag3'],
        'diag2' => $row['diag2'],
        'diag1' => $row['diag1'],
        'knowledge3' => $row['knowledge3'],
        'knowledge2' => $row['knowledge2'],
        'knowledge1' => $row['knowledge1'],
        'environmental4' => $row['environmental4'],
        'environmental3' => $row['environmental3'],
        'environmental2' => $row['environmental2'],
        'environmental1' => $row['environmental1'],
        'after_surgery3' => $row['after_surgery3'],
        'after_surgery2' => $row['after_surgery2'],
        'after_surgery1' => $row['after_surgery1'],
        'drug_use3' => $row['drug_use3'],
        'drug_use2' => $row['drug_use2'],
        'drug_use1' => $row['drug_use1'],
        'score' => $row['score'],
        'create_' => $row['create_']
    ];
}


        $ids = '33'; //Link menu
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
    <h2 style="text-align:center;font-size:12pt;">แบบประเมินภาวะพลัดตกหกล้มผู้ป่วยทารกและเด็ก<br>(Fall Assessment Tool The Humpty Dumpty Scale)
    <br>หอผู้ป่วยกุมารเวชกรรม กลุ่มงานการพยาบาลผู้ป่วยกุมารเวชกรรม  &nbsp;'.htmlspecialchars(DbConstant::HOSPITAL_NAME).$check_report.'</h2>';

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

$html.=' <tr><td colspan="'.htmlspecialchars($colspan).'">&nbsp;<b>1. อายุ</b></td>';
// Table body
foreach (['age_check4', 'age_check3', 'age_check2', 'age_check1'] as $questionIndex => $questionName) {
 
    $html .= '<tr>';
   if ($questionIndex === 0) {
        $html .= '<td>ต่ำกว่า 3 ปี</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td>อายุ 3 - 7 ปี</td>';
    }
    elseif ($questionIndex === 2) {
        $html .= '<td>อายุ 7 - 13 ปี</td>';
    }
    elseif ($questionIndex === 3) {
        $html .= '<td>มากกว่า 13 ปี</td>';
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

$html.=' <tr><td colspan="'.htmlspecialchars($colspan).'">&nbsp;<b>2. เพศ</b></td>';
// Table body
foreach (['sex2', 'sex1'] as $questionIndex => $questionName) {
 
    $html .= '<tr>';
   if ($questionIndex === 0) {
        $html .= '<td>ชาย</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td>หญิง</td>';
    }


    if ($questionIndex === 0) {
        $html .= '<td style="text-align:center;">2</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td style="text-align:center;">1</td>';
    }

    foreach ($dates as $date) {
        $html .= '<td>' . $groupedData[$date]['1'][$questionName] . '</td>';
        $html .= '<td >' . $groupedData[$date]['2'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['3'][$questionName] . '</td>';
    }

    $html .= '</tr>';  

}


$html.=' <tr><td colspan="'.htmlspecialchars($colspan).'">&nbsp;<b>3. การวินิจฉัยโรค</b></td>';
// Table body
foreach (['diag4', 'diag3', 'diag2', 'diag1'] as $questionIndex => $questionName) {
 
    $html .= '<tr>';
   if ($questionIndex === 0) {
        $html .= '<td>ผู้ป่วยที่มีโรค/อาการทางระบบประสาท หรือมีปัญหาด้านการมองเห็นการรับพัง และการเคลื่อนไหว</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td>ผู้ป่วยพร่องการได้รับออกซิเจน เช่น มีปัญหาทางระบบทางเดินหายใจ การขาดน้ำ เกลือแร่ ซีด นอนไม่หลับ เป็นลม มึนงง</td>';
    }
    elseif ($questionIndex === 2) {
        $html .= '<td>ผู้ป่วยที่เจ็บป่วยทางจิตหรือมีความเปลี่ยนแปลงด้านพฤติกรรม</td>';
    }
    elseif ($questionIndex === 3) {
        $html .= '<td>โรคอื่น ๆ</td>';
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



$html.=' <tr><td colspan="'.htmlspecialchars($colspan).'">&nbsp;<b>4. ความสามารถในการรับรู้</b></td>';
// Table body
foreach (['knowledge3', 'knowledge2','knowledge1'] as $questionIndex => $questionName) {
 
    $html .= '<tr>';
   if ($questionIndex === 0) {
        $html .= '<td>การรับรู้บกพร่องหรือประเมินความสามารถตนเองไม่เหมาะสม รวมถึงทารก</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td>รับรู้และไม่ปฏิบัติตามคำแนะนำ</td>';
    }elseif ($questionIndex === 2) {
        $html .= '<td>รับรู้และปฏิบัติตามคำแนะนำ</td>';
    }


    if ($questionIndex === 0) {
        $html .= '<td style="text-align:center;">3</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td style="text-align:center;">0</td>';
    }elseif ($questionIndex === 2) {
        $html .= '<td style="text-align:center;">0</td>';
    }

    foreach ($dates as $date) {
        $html .= '<td>' . $groupedData[$date]['1'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['2'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['3'][$questionName] . '</td>';
    }

    $html .= '</tr>';  

}


$html.=' <tr><td colspan="'.htmlspecialchars($colspan).'">&nbsp;<b>5. ปัจจัยและสิ่งแวดล้อม</b></td>';
// Table body
foreach (['environmental4', 'environmental3','environmental2','environmental1'] as $questionIndex => $questionName) {
 
    $html .= '<tr>';
   if ($questionIndex === 0) {
        $html .= '<td>ผู้ป่วยที่มีประวัติพลัดตกหกลัม หรือทารก-วัยหัดเดินที่ต้องนอนบนเตียง</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td>ผู้ป่วยที่ต้องใช้กายอุปกรณ์ช่วยเหลือหรือผู้ป่วยที่ต้องคาสายท่อระบายต่าง ๆ</td>';
    }
    elseif ($questionIndex === 2) {
        $html .= '<td>ผู้ป่วยที่นอนกับเตียง</td>';
    }
    elseif ($questionIndex === 3) {
        $html .= '<td>ผู้ป่วยที่สามารถเดินไป - มาได้ด้วยตนเอง</td>';
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


$html.=' <tr><td colspan="'.htmlspecialchars($colspan).'">&nbsp;<b>6. หลังผ่าตัด</b></td>';
// Table body
foreach (['after_surgery3', 'after_surgery2','after_surgery1'] as $questionIndex => $questionName) {
 
    $html .= '<tr>';
   if ($questionIndex === 0) {
        $html .= '<td>ผู้ป่วยหลังผ่าตัดใน 24 ชั่วโมง</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td>ภายใน 48 ชั่วโมง</td>';
    }elseif ($questionIndex === 2) {
        $html .= '<td>มากกว่า 48 ชั่วโมงหรือไม่ได้รับการผ่าตัด</td>';
    }

    if ($questionIndex === 0) {
        $html .= '<td style="text-align:center;">3</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td style="text-align:center;">2</td>';
    }elseif ($questionIndex === 2) {
        $html .= '<td style="text-align:center;">1</td>';
    }
    foreach ($dates as $date) {
        $html .= '<td>' . $groupedData[$date]['1'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['2'][$questionName] . '</td>';
        $html .= '<td>' . $groupedData[$date]['3'][$questionName] . '</td>';
    }

    $html .= '</tr>';  

}


$html.=' <tr><td colspan="'.htmlspecialchars($colspan).'">&nbsp;<b>7. การได้รับยาและขนาดของยา หมายถึงการใด้รับยาที่มีผลต่อความดันโลหิต</b>
<br><div style="font-size:9pt;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ระดับความรู้สึกตัวและมีผลทำให้ง่วงซึม เช่น ยาในกลุ่ม Sedative, DiureticsTranquilizer (Psychotherapeutic) Antihypertensive drugs,
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Anticonvulsants, Cardiovascular drugs, Hypotonic, Barbiturates, Phenothiazine, Antidepressants, Laxative, Narcotics</div>
</td>';
// Table body
foreach (['drug_use3', 'drug_use2','drug_use1'] as $questionIndex => $questionName) {
 
    $html .= '<tr>';
   if ($questionIndex === 0) {
        $html .= '<td>ผู้ป่วยได้รับยาข้างต้นมากกว่า 1 ชนิด</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td>ผู้ป่วยได้รับยาข้างต้น 1 ชนิด</td>';
    }elseif ($questionIndex === 2) {
        $html .= '<td>ผู้ป่วยได้รับยาชนิดอื่นนอกเหนือจากยาข้างต้นหรือไม่ได้รับยา</td>';
    }

    if ($questionIndex === 0) {
        $html .= '<td style="text-align:center;">3</td>'; // Adjust level as needed
    } elseif ($questionIndex === 1) {
        $html .= '<td style="text-align:center;">2</td>';
    } elseif ($questionIndex === 2) {
        $html .= '<td style="text-align:center;">1</td>';
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
<div class="col-md-12 text-left">
<b style="font-size: 12px;">หมายเหตุ :</b><span style="font-size: 12px;"> แบบประเมินนี้ ใช้กับผู้ป่วยทารกและเด็ก ตั้งแต่แรกเกิดถึงอายุ 15 ปี</span><br>
<b style="font-size: 12px;">การแปลผล</b><span style="font-size: 12px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ความเสี่ยงต่ำ 7-11 คะแนน </span><br>
<b style="font-size: 12px;"></b><span style="font-size: 12px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ความเสี่ยงปานกลาง 12-16 คะแนน </span><br>
<b style="font-size: 12px;"></b><span style="font-size: 12px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ความเสี่ยงสูง >= 17 คะแนน </span>
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
//$html .= 'Page ' . $page . ' of ' . $totalPages;
if ($page < $totalPages) {
    $html .= ' | <a href="bedscore-pdf.php?an=' . $an . '&page=' . ($page + 1) . '">Next Page</a>';
    
}
$html .= '</div>';
//แสดงข้อมุลอยู่ในช่วง ก่อน footer
$mpdf->setAutoTopMargin = 'stretch';
$mpdf->setAutoBottomMargin = 'stretch';

$mpdf->setFooter('ชื่อ - สกุล : '.htmlspecialchars($row_ipt['pname'].$row_ipt['fname']." ".$row_ipt['lname']).' HN: '.htmlspecialchars($hn).' AN: '.htmlspecialchars($an));
// Write the HTML content to the PDF
$mpdf->WriteHTML($html);

// Output the PDF to the browser
$mpdf->Output('Shift_Report_AN_' . $an . '_Page_' . $page . '.pdf', 'I'); // Inline display in browser