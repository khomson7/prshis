<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once '../mains/datethai.php';
require_once '../include/Session.php';

// =====================================================
// ป้องกันการเรียกผ่าน GET (บังคับใช้ POST เท่านั้น)
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo "<script>window.close();</script>";
    exit;
}

// =====================================================
// ระบบ Single Sign-On (SSO) ข้าม Port/Server (แบบ POST)
// =====================================================
if (isset($_POST['hash']) && isset($_POST['loginuser']) && isset($_POST['t']) && isset($_POST['an'])) {
    $secret_key = "PRSHIS_SECRET_2026"; // ต้องตั้งให้ตรงกับฝั่งที่สร้างปุ่มลิงก์
    
    // ขยายเวลาเป็น 1 ชั่วโมง (3600 วิ) และใช้ abs() เพื่อแก้ปัญหาเวลา 2 เซิร์ฟเวอร์เดินไม่เท่ากัน
    if (abs(time() - $_POST['t']) <= 3600) {
        $expected_hash = md5($_POST['loginuser'] . $_POST['t'] . $_POST['an'] . $secret_key);
        
        // ถ้ารหัสตรงกัน ให้สร้าง Session ใช้งานได้เลย
        if ($_POST['hash'] === $expected_hash) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['loginname'] = $_POST['loginuser'];
        }
    }
}

$login = empty($_REQUEST['loginname']) ? null : $_REQUEST['loginname'];
$loginname = $_SESSION['loginname'] ?? null;
$values = ['loginname' => $loginname];

if (!$loginname) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    session_destroy();
    require_once '../mains/main-report.php';
    exit;
}

Session::checkLoginSessionAndShowMessage();

if (!(Session::checkPermission('DOCUMENT', 'PRINT'))) {
    return;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require_once '../include/DbUtils.php';
require_once '../include/Session.php';
require_once '../include/KphisQueryUtils.php';
require_once '../include/ReportQueryUtils.php';

date_default_timezone_set('asia/bangkok');

$conn = DbUtils::get_hosxp_connection();
$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4-L']); // Landscape for wide table
$mpdf->shrink_tables_to_fit = 0; // ปิดการย่อขนาดตารางอัตโนมัติ
$mpdf->AddPageByArray([
    'margin-left' => 6,
    'margin-right' => 6,
    'margin-top' => 6,
    'margin-bottom' => 6,
]);

$an = empty($_REQUEST['an']) ? null : $_REQUEST['an'];

// ดึง vn จาก ovst ด้วย an แล้วหา opd_er_order_master_id จาก opd_er_order_master
$masterSql = "SELECT ovst.vn, ovst.an, ovst.hn, om.opd_er_order_master_id
  FROM " . DbConstant::HOSXP_DBNAME . ".ovst
  LEFT JOIN " . DbConstant::KPHIS_DBNAME . ".opd_er_order_master om ON om.vn = ovst.vn
  WHERE ovst.an = :an
  ORDER BY om.opd_er_order_master_id DESC
  LIMIT 1";
$masterStmt = $conn->prepare($masterSql);
$masterStmt->bindParam(':an', $an);
$masterStmt->execute();
$masterRow = $masterStmt->fetch(PDO::FETCH_ASSOC);

$vn = $masterRow['vn'] ?? '';
$hn = $masterRow['hn'] ?? '';
$opd_er_order_master_id = $masterRow['opd_er_order_master_id'] ?? '';

Session::insertSystemAccessLog(json_encode(array(
    'report' => 'ER-DRUG-ADMIN-REPORT-PDF',
    'an' => $an,
    'vn' => $vn,
    'opd_er_order_master_id' => $opd_er_order_master_id,
), JSON_UNESCAPED_UNICODE));

// =====================================================
// 1. ดึงข้อมูลการบริหารยา (ER)
// =====================================================
$sql = "SELECT di.sticker_short_name, io.order_item_detail,
       act.action_date, act.action_time,
       d.name AS dname1, d2.name AS dname2
FROM " . DbConstant::KPHIS_DBNAME . ".opd_er_nurse_index_action act
 INNER JOIN " . DbConstant::KPHIS_DBNAME . ".opd_er_nurse_index_plan ap ON ap.plan_id = act.plan_id
 INNER JOIN " . DbConstant::KPHIS_DBNAME . ".opd_er_order_item io ON io.order_item_id = ap.order_item_id
 LEFT OUTER JOIN " . DbConstant::HOSXP_DBNAME . ".doctor d ON d.code = act.action_person_1
 LEFT OUTER JOIN " . DbConstant::HOSXP_DBNAME . ".doctor d2 ON d2.code = act.action_person_2
 LEFT OUTER JOIN " . DbConstant::HOSXP_DBNAME . ".drugitems di ON di.icode = io.icode
WHERE act.opd_er_order_master_id = :opd_er_order_master_id
AND act.action_time IS NOT NULL AND act.action_time != ''
ORDER BY io.order_item_detail, act.action_date, act.action_time";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':opd_er_order_master_id', $opd_er_order_master_id);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// =====================================================
// 2. จัดกลุ่มข้อมูล: รวบรวมวันที่ทั้งหมด และจัดกลุ่มตาม order_item_detail
// =====================================================
$allDates = [];       // วันที่ทั้งหมดที่มีการบริหารยา (unique, sorted)
$drugData = [];       // [drug_group_key => ['name'=>..., 'detail'=>..., 'times'=>[date => [...]]]]

foreach ($rows as $row) {
    $date = $row['action_date'];
    $detail = $row['order_item_detail'] ?? '';
    $drugName = !empty($row['sticker_short_name']) ? $row['sticker_short_name'] : '';
    $groupKey = $drugName . $detail;

    // เก็บวันที่ทั้งหมด
    if (!in_array($date, $allDates)) {
        $allDates[] = $date;
    }

    // จัดกลุ่มตาม concat(sticker_short_name, order_item_detail)
    if (!isset($drugData[$groupKey])) {
        $drugData[$groupKey] = [
            'name' => $drugName,
            'detail' => $detail,
            'times' => []
        ];
    }

    if (!isset($drugData[$groupKey]['times'][$date])) {
        $drugData[$groupKey]['times'][$date] = [];
    }

    $drugData[$groupKey]['times'][$date][] = [
        'time' => substr($row['action_time'], 0, 5), // HH:MM
        'dname1' => $row['dname1'] ?? '',
        'dname2' => $row['dname2'] ?? '',
    ];
}

sort($allDates); // เรียงวันที่

// =====================================================
// 3. หาจำนวน row สูงสุดต่อยาแต่ละตัว (จำนวนเวลาที่มากที่สุดในแต่ละวัน)
// =====================================================
$drugMaxRows = [];
foreach ($drugData as $groupKey => $info) {
    $allTimesForDrug = [];
    foreach ($info['times'] as $date => $entries) {
        foreach ($entries as $entry) {
            if (!in_array($entry['time'], $allTimesForDrug)) {
                $allTimesForDrug[] = $entry['time'];
            }
        }
    }
    sort($allTimesForDrug);
    $drugMaxRows[$groupKey] = $allTimesForDrug;
}

// =====================================================
// 4. Pagination - แบ่งหน้าตามจำนวนวัน
// =====================================================
// =====================================================
// 4. Pagination - แบ่งหน้าตามจำนวนวัน เพื่อแสดงหลายๆ แผ่นใน PDF เดียว
// =====================================================
$daysPerPage = 3; // จำนวนวันต่อหน้า (ปรับลดลงเพื่อให้ตัวอักษรใหญ่ขึ้น)
$totalDates = count($allDates);
$totalPages = max(1, ceil($totalDates / $daysPerPage));

// --- ข้อมูลผู้ป่วย (ER) ดึงจาก ovst เชื่อม vn → an ---
$patientSql = "SELECT p.pname, p.fname, p.lname, o.vn, o.an, o.hn, o.vstdate, o.vsttime
FROM " . DbConstant::HOSXP_DBNAME . ".ovst o
LEFT JOIN " . DbConstant::HOSXP_DBNAME . ".patient p ON p.hn = o.hn
WHERE o.vn = :vn LIMIT 1";
$patientStmt = $conn->prepare($patientSql);
$patientStmt->bindParam(':vn', $vn);
$patientStmt->execute();
$patient = $patientStmt->fetch(PDO::FETCH_ASSOC);

$patientName = '';
$vstDate = '';
if ($patient) {
    $patientName = ($patient['pname'] ?? '') . ($patient['fname'] ?? '') . ' ' . ($patient['lname'] ?? '');
    $vstDate = !empty($patient['vstdate']) ? date('d/m/', strtotime($patient['vstdate'])) . (date('Y', strtotime($patient['vstdate'])) + 543) : '';
    // อัปเดต an จาก ovst ถ้ายังว่าง
    if (empty($an))
        $an = $patient['an'] ?? '';
}

$html = '
<style>
    body {
        font-family: "Garuda";
    }
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 16px;
    }
    th, td {
        border: 1px solid #000;
        padding: 6px 8px;
        text-align: center;
        vertical-align: middle;
        font-size: 16px;
    }
    th {
        background-color: #f0f0f0;
    }
    td.drug-name {
        text-align: left;
        font-weight: bold;
        word-wrap: break-word;
    }
    .header-info {
        font-size: 14px;
        margin-bottom: 5px;
    }
</style>';

// =====================================================
// Loop สร้างตารางทีละหน้า แล้วนำมาต่อกัน
// =====================================================
for ($page = 1; $page <= $totalPages; $page++) {
    $offset = ($page - 1) * $daysPerPage;
    $pageDates = array_slice($allDates, $offset, $daysPerPage);
    
    if ($page > 1) {
        $html .= '<pagebreak />';
    }

    $html .= '<h2 style="text-align:center; font-size:16pt; margin-bottom:5px;">ใบแจ้งการให้ยา (ER) โรงพยาบาลปราสาท</h2>';

    // ข้อมูลผู้ป่วย
    $html .= '<div class="header-info">';
    $html .= 'ชื่อผู้ป่วย: <b>' . htmlspecialchars($patientName) . '</b>';
    $html .= ' &nbsp;&nbsp; HN: <b>' . htmlspecialchars($hn) . '</b>';
    $html .= ' &nbsp;&nbsp; VN: <b>' . htmlspecialchars($vn) . '</b>';
    if (!empty($an)) {
        $html .= ' &nbsp;&nbsp; AN: <b>' . htmlspecialchars($an) . '</b>';
    }
    $html .= ' &nbsp;&nbsp; วันที่ Visit: <b>' . htmlspecialchars($vstDate) . '</b>';
    $html .= ' &nbsp;&nbsp; แผ่นที่: <b>' . $page . '/' . $totalPages . '</b>';
    $html .= '</div>';

    $html .= '<table border="1" cellpadding="3" cellspacing="0">';

    // --- Header Row 1: ชื่อคอลัมน์ + วันที่ ---
    $html .= '<thead>';
    $html .= '<tr>';
    $html .= '<th rowspan="2" style="width:5%;">ลำดับ</th>';
    $html .= '<th rowspan="2" style="width:15%;">ยา</th>';
    foreach ($pageDates as $date) {
        $dateFormatted = date('d/m/', strtotime($date)) . (date('Y', strtotime($date)) + 543);
        $html .= '<th colspan="1">' . $dateFormatted . '</th>';
    }
    $html .= '</tr>';

    // --- Header Row 2: (ใต้วันที่ อาจเขียนว่า เวลา/ผู้ให้ยา) ---
    $html .= '<tr>';
    foreach ($pageDates as $date) {
        $html .= '<th style="font-size:14px;">เวลา / ผู้บริหารยา</th>';
    }
    $html .= '</tr>';
    $html .= '</thead>';

    // --- Body ---
    $html .= '<tbody>';
    $seq = 0;

    foreach ($drugData as $groupKey => $info) {
        // ตรวจสอบว่ายาตัวนี้มีเวลาบริหารยาในหน้าปัจจุบันหรือไม่
        $hasDataInPage = false;
        foreach ($pageDates as $date) {
            if (isset($info['times'][$date]) && count($info['times'][$date]) > 0) {
                $hasDataInPage = true;
                break;
            }
        }
        if (!$hasDataInPage) {
            continue; // ข้ามยาที่ไม่มีข้อมูลในหน้านี้
        }

        $seq++;

        // คำนวณจำนวนแถวสูงสุดของยาตัวนี้ในหน้านี้
        $maxRowsForDrug = 1;
        foreach ($pageDates as $date) {
            $count = isset($info['times'][$date]) ? count($info['times'][$date]) : 0;
            if ($count > $maxRowsForDrug) {
                $maxRowsForDrug = $count;
            }
        }

        // สร้างแถวตาม maxRowsForDrug
        for ($rowIdx = 0; $rowIdx < $maxRowsForDrug; $rowIdx++) {
            $html .= '<tr>';

            // คอลัมน์ ลำดับ + ชื่อยา
            if ($rowIdx === 0) {
                $html .= '<td rowspan="' . $maxRowsForDrug . '" style="vertical-align:middle;">' . $seq . '</td>';
                if (!empty($info['name'])) {
                    $drugDetailSub = '<br><span style="font-size:14px; font-weight:normal; color:#333;">' . htmlspecialchars($info['detail']) . '</span>';
                    $html .= '<td rowspan="' . $maxRowsForDrug . '" class="drug-name" style="vertical-align:middle;">' . htmlspecialchars($info['name']) . $drugDetailSub . '</td>';
                } else {
                    $html .= '<td rowspan="' . $maxRowsForDrug . '" class="drug-name" style="vertical-align:middle;">' . htmlspecialchars($info['detail']) . '</td>';
                }
            }

            // แต่ละวัน
            foreach ($pageDates as $date) {
                $entries = isset($info['times'][$date]) ? $info['times'][$date] : [];
                $dateCount = count($entries);

                if ($dateCount === 0) {
                    if ($rowIdx === 0) {
                        $html .= '<td rowspan="' . $maxRowsForDrug . '"></td>';
                    }
                } elseif ($dateCount === $maxRowsForDrug) {
                    $entry       = $entries[$rowIdx];
                    $displayText = $entry['time'];
                    $names       = [];
                    if (!empty($entry['dname1']))
                        $names[] = $entry['dname1'];
                    if (!empty($entry['dname2']))
                        $names[] = $entry['dname2'];
                    if (!empty($names)) {
                        $displayText .= ' ' . implode(',', $names);
                    }
                    $html .= '<td style="font-size:15px; text-align:left;">' . htmlspecialchars($displayText) . '</td>';
                } else {
                    if ($rowIdx < $dateCount) {
                        $entry       = $entries[$rowIdx];
                        $displayText = $entry['time'];
                        $names       = [];
                        if (!empty($entry['dname1']))
                            $names[] = $entry['dname1'];
                        if (!empty($entry['dname2']))
                            $names[] = $entry['dname2'];
                        if (!empty($names)) {
                            $displayText .= ' ' . implode(',', $names);
                        }

                        if ($rowIdx === $dateCount - 1) {
                            $remainingRows = $maxRowsForDrug - $dateCount;
                            $html .= '<td rowspan="' . ($remainingRows + 1) . '" style="font-size:15px; text-align:left;">' . htmlspecialchars($displayText) . '</td>';
                        } else {
                            $html .= '<td style="font-size:15px; text-align:left;">' . htmlspecialchars($displayText) . '</td>';
                        }
                    }
                }
            }

            $html .= '</tr>';
        }
    }

    $html .= '</tbody></table>';
}

// =====================================================
// 8. Output PDF
// =====================================================
$mpdf->setAutoTopMargin = 'stretch';
$mpdf->setAutoBottomMargin = 'stretch';
$mpdf->setFooter('HN: ' . htmlspecialchars($hn) . ' AN: ' . htmlspecialchars($an) . ' VN: ' . htmlspecialchars($vn) . ' หน้า {PAGENO}/{nbpg}');
$mpdf->WriteHTML($html);
$mpdf->Output('ER_Drug_Admin_Report_AN_' . $an . '.pdf', 'I');
