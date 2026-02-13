<?php
require_once '../mains/datethai.php';
require_once '../include/Session.php';

$login = empty($_REQUEST['loginname']) ? null : $_REQUEST['loginname'];
$loginname = $_SESSION['loginname'];
$values = ['loginname' => $loginname];

if (!$loginname) {
    session_start();
    session_destroy();
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
$mpdf->AddPageByArray([
    'margin-left' => 6,
    'margin-right' => 6,
    'margin-top' => 6,
    'margin-bottom' => 6,
]);

$an = empty($_REQUEST['an']) ? null : $_REQUEST['an'];
$hn = KphisQueryUtils::getHnByAn($an);

Session::insertSystemAccessLog(json_encode(array(
    'report' => 'DRUG-ADMIN-REPORT-PDF',
    'an' => $an,
), JSON_UNESCAPED_UNICODE));

// =====================================================
// 1. ดึงข้อมูลการบริหารยา
// =====================================================
$sql = "SELECT di.sticker_short_name, io.order_item_detail, act.action_date, act.action_time,
               d.name AS dname1, d2.name AS dname2
               FROM " . DbConstant::KPHIS_DBNAME . ".ipd_nurse_index_action act
                INNER JOIN " . DbConstant::KPHIS_DBNAME . ".ipd_nurse_index_plan ap ON ap.plan_id = act.plan_id
                INNER JOIN " . DbConstant::KPHIS_DBNAME . ".ipd_order_item io ON io.order_item_id = ap.order_item_id
                LEFT OUTER JOIN " . DbConstant::HOSXP_DBNAME . ".doctor d ON d.code = act.action_person_1
                LEFT OUTER JOIN " . DbConstant::HOSXP_DBNAME . ".doctor d2 ON d2.code = act.action_person_2
                LEFT OUTER JOIN " . DbConstant::HOSXP_DBNAME . ".drugitems di ON di.icode = io.icode
        WHERE act.an = :an 
          AND act.action_time IS NOT NULL AND act.action_time != ''
        GROUP BY io.order_item_detail, act.action_time
        ORDER BY io.order_item_detail, act.action_date, act.action_time";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':an', $an);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// =====================================================
// 2. จัดกลุ่มข้อมูล: รวบรวมวันที่ทั้งหมด และจัดกลุ่มตาม order_item_detail
// =====================================================
$allDates = [];       // วันที่ทั้งหมดที่มีการบริหารยา (unique, sorted)
$drugData = [];       // [order_item_detail => ['name'=>..., 'detail'=>..., 'times'=>[date => [...]]]]

foreach ($rows as $row) {
    $date = $row['action_date'];
    $detail = $row['order_item_detail'] ?? '';
   // $drugName = $row['sticker_short_name'] ?? $detail;
   $drugName = $row['sticker_short_name'] ?? '';

    // เก็บวันที่ทั้งหมด
    if (!in_array($date, $allDates)) {
        $allDates[] = $date;
    }

    // จัดกลุ่มตาม order_item_detail
    if (!isset($drugData[$detail])) {
        $drugData[$detail] = [
            'name' => $drugName,
            'detail' => $detail,
            'times' => []
        ];
    }

    if (!isset($drugData[$detail]['times'][$date])) {
        $drugData[$detail]['times'][$date] = [];
    }

    $drugData[$detail]['times'][$date][] = [
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
foreach ($drugData as $detail => $info) {
    $allTimesForDrug = [];
    foreach ($info['times'] as $date => $entries) {
        foreach ($entries as $entry) {
            if (!in_array($entry['time'], $allTimesForDrug)) {
                $allTimesForDrug[] = $entry['time'];
            }
        }
    }
    sort($allTimesForDrug);
    $drugMaxRows[$detail] = $allTimesForDrug;
}

// =====================================================
// 4. Pagination - แบ่งหน้าตามจำนวนวัน
// =====================================================
$daysPerPage = 4; // จำนวนวันต่อหน้า (ปรับได้)
$totalDates = count($allDates);
$totalPages = max(1, ceil($totalDates / $daysPerPage));
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
if ($page > $totalPages) $page = $totalPages;

$offset = ($page - 1) * $daysPerPage;
$pageDates = array_slice($allDates, $offset, $daysPerPage);
$colCount = count($pageDates);

// =====================================================
// 5. สร้าง HTML
// =====================================================

// --- ข้อมูลผู้ป่วย ---
$patientSql = "SELECT p.pname, p.fname, p.lname, i.an, i.hn, i.regdate, i.dchdate, w.name AS ward_name
FROM " . DbConstant::HOSXP_DBNAME . ".ipt i
LEFT JOIN " . DbConstant::HOSXP_DBNAME . ".patient p ON p.hn = i.hn
LEFT JOIN " . DbConstant::HOSXP_DBNAME . ".ward w ON w.ward = i.ward
WHERE i.an = :an LIMIT 1";
$patientStmt = $conn->prepare($patientSql);
$patientStmt->bindParam(':an', $an);
$patientStmt->execute();
$patient = $patientStmt->fetch(PDO::FETCH_ASSOC);

$patientName = '';
$wardName = '';
if ($patient) {
    $patientName = ($patient['pname'] ?? '') . ($patient['fname'] ?? '') . ' ' . ($patient['lname'] ?? '');
    $wardName = $patient['ward_name'] ?? '';
}

$html = '
<style>
    body {
        font-family: "Garuda";
    }
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 10px;
    }
    th, td {
        border: 1px solid #000;
        padding: 3px 4px;
        text-align: center;
        vertical-align: middle;
    }
    th {
        background-color: #f0f0f0;
    }
    td.drug-name {
        text-align: left;
        font-weight: bold;
        white-space: nowrap;
    }
    .header-info {
        font-size: 11px;
        margin-bottom: 5px;
    }
</style>';

$html .= '<h2 style="text-align:center; font-size:14pt; margin-bottom:5px;">ใบแจ้งการให้ยา โรงพยาบาลปราสาท</h2>';

// ข้อมูลผู้ป่วย
$html .= '<div class="header-info">';
$html .= 'ชื่อผู้ป่วย: <b>' . htmlspecialchars($patientName) . '</b>';
$html .= ' &nbsp;&nbsp; HN: <b>' . htmlspecialchars($hn) . '</b>';
$html .= ' &nbsp;&nbsp; AN: <b>' . htmlspecialchars($an) . '</b>';
$html .= ' &nbsp;&nbsp; หอผู้ป่วย: <b>' . htmlspecialchars($wardName) . '</b>';
$html .= '</div>';

// =====================================================
// 6. สร้างตาราง
// =====================================================
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
    $html .= '<th style="font-size:8px;">เวลา / ผู้บริหารยา</th>';
}
$html .= '</tr>';
$html .= '</thead>';

// --- Body ---
$html .= '<tbody>';
$seq = 0;

foreach ($drugData as $detailKey => $info) {
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

    // =====================================================
    // คำนวณจำนวนแถวสูงสุดของยาตัวนี้ (max entries ในทุกวันของหน้านี้)
    // =====================================================
    $maxRowsForDrug = 1; // อย่างน้อย 1 แถว
    foreach ($pageDates as $date) {
        $count = isset($info['times'][$date]) ? count($info['times'][$date]) : 0;
        if ($count > $maxRowsForDrug) {
            $maxRowsForDrug = $count;
        }
    }

     // =====================================================
    // สร้างแถวตาม maxRowsForDrug
    // =====================================================
    for ($rowIdx = 0; $rowIdx < $maxRowsForDrug; $rowIdx++) {
        $html .= '<tr>';

        // คอลัมน์ ลำดับ + ชื่อยา (rowspan ในแถวแรก)
        if ($rowIdx === 0) {
            $html .= '<td rowspan="' . $maxRowsForDrug . '" style="vertical-align:middle;">' . $seq . '</td>';
            $drugNameSub = !empty($info['name']) ? '<br><span style="font-size:10px; font-weight:bold; color:#0000CC;">(' . htmlspecialchars($info['name']) . ')</span>' : '';
            $html .= '<td rowspan="' . $maxRowsForDrug . '" class="drug-name" style="vertical-align:middle;">' . htmlspecialchars($info['detail']) . $drugNameSub . '</td>';
        }

        // แต่ละวัน
        foreach ($pageDates as $date) {
            $entries = isset($info['times'][$date]) ? $info['times'][$date] : [];
            $dateCount = count($entries);

            if ($dateCount === 0) {
                // วันนี้ไม่มีข้อมูลเลย → merge ทั้งหมดในแถวแรก
                if ($rowIdx === 0) {
                    $html .= '<td rowspan="' . $maxRowsForDrug . '"></td>';
                }
                // แถวอื่นไม่ต้องเขียน td (ถูก merge แล้ว)
            } elseif ($dateCount === $maxRowsForDrug) {
                // จำนวนเท่ากับ maxRows → แสดง 1 entry ต่อ 1 แถวพอดี
                $entry = $entries[$rowIdx];
                $displayText = $entry['time'];
                $names = [];
                if (!empty($entry['dname1'])) $names[] = $entry['dname1'];
                if (!empty($entry['dname2'])) $names[] = $entry['dname2'];
                if (!empty($names)) {
                    $displayText .= ' ' . implode(',', $names);
                }
                $html .= '<td style="font-size:9px; text-align:left;">' . htmlspecialchars($displayText) . '</td>';
            } else {
                // dateCount < maxRowsForDrug → แสดง entries แล้ว merge แถวที่เหลือ
                if ($rowIdx < $dateCount) {
                    // แถวที่มีข้อมูล
                    $entry = $entries[$rowIdx];
                    $displayText = $entry['time'];
                    $names = [];
                    if (!empty($entry['dname1'])) $names[] = $entry['dname1'];
                    if (!empty($entry['dname2'])) $names[] = $entry['dname2'];
                    if (!empty($names)) {
                        $displayText .= ' ' . implode(',', $names);
                    }

                    // ถ้าเป็น entry สุดท้ายของวันนี้ → merge แถวที่เหลือ
                    if ($rowIdx === $dateCount - 1) {
                        $remainingRows = $maxRowsForDrug - $dateCount;
                        $html .= '<td rowspan="' . ($remainingRows + 1) . '" style="font-size:9px; text-align:left;">' . htmlspecialchars($displayText) . '</td>';
                    } else {
                        $html .= '<td style="font-size:9px; text-align:left;">' . htmlspecialchars($displayText) . '</td>';
                    }
                }
                // แถวที่ถูก merge แล้วไม่ต้องเขียน td
            }
        }

        $html .= '</tr>';
    }
}

$html .= '</tbody></table>';

// =====================================================
// 7. Pagination
// =====================================================
$html .= '<div style="text-align: center; margin-top: 10px; font-size:10px;">';
if ($page > 1) {
    $html .= '<a href="med-action-pdf.php?an=' . $an . '&page=' . ($page - 1) . '">หน้าก่อน</a> | ';
}
$html .= 'หน้า ' . $page . ' จาก ' . $totalPages;
if ($page < $totalPages) {
    $html .= ' | <a href="med-action-pdf.php?an=' . $an . '&page=' . ($page + 1) . '">หน้าถัดไป</a>';
}
$html .= '</div>';

// =====================================================
// 8. Output PDF
// =====================================================
$mpdf->setAutoTopMargin = 'stretch';
$mpdf->setAutoBottomMargin = 'stretch';
$mpdf->setFooter('HN: ' . htmlspecialchars($hn) . ' AN: ' . htmlspecialchars($an) . ' หน้า ' . $page);
$mpdf->WriteHTML($html);
$mpdf->Output('Drug_Admin_Report_AN_' . $an . '_Page_' . $page . '.pdf', 'I');
