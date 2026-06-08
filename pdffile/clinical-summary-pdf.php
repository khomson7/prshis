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
$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']); // Portrait A4
$mpdf->AddPageByArray([
    'margin-left' => 15,
    'margin-right' => 15,
    'margin-top' => 10,
    'margin-bottom' => 15,
]);

$an = empty($_REQUEST['an']) ? null : $_REQUEST['an'];
$hn = KphisQueryUtils::getHnByAn($an);

Session::insertSystemAccessLog(json_encode(array(
    'report' => 'CLINICAL-SUMMARY-PDF',
    'an' => $an,
), JSON_UNESCAPED_UNICODE));

// =====================================================
// 1. ดึงข้อมูลผู้ป่วย
// =====================================================
$patientSql = "SELECT p.pname, p.fname, p.lname, p.sex, p.birthday,
       i.an, i.hn, i.regdate, i.regtime, i.dchdate, i.dchtime,
       null AS admit_diag,
       w.name AS ward_name,
       TIMESTAMPDIFF(YEAR, p.birthday, i.regdate) AS age_year,
       TIMESTAMPDIFF(MONTH, p.birthday, i.regdate) % 12 AS age_month,
       TIMESTAMPDIFF(DAY, DATE_ADD(p.birthday, INTERVAL (TIMESTAMPDIFF(MONTH, p.birthday, i.regdate)) MONTH), i.regdate) AS age_day,
       IF(i.dchdate IS NOT NULL, DATEDIFF(i.dchdate, i.regdate), DATEDIFF(CURDATE(), i.regdate)) AS admit_days
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
$ageText = '';
$regDateText = '';
$dchDateText = '';
$admitDays = '';

if ($patient) {
    $patientName = ($patient['pname'] ?? '') . ($patient['fname'] ?? '') . ' ' . ($patient['lname'] ?? '');
    $wardName = $patient['ward_name'] ?? '';
    $ageText = ($patient['age_year'] ?? '0') . ' ปี ' . ($patient['age_month'] ?? '0') . ' เดือน ' . ($patient['age_day'] ?? '0') . ' วัน';
    $regDateText = !empty($patient['regdate']) ? ShortDateThai($patient['regdate']) : '-';
    $dchDateText = !empty($patient['dchdate']) ? ShortDateThai($patient['dchdate']) : '-';
    $admitDays = $patient['admit_days'] ?? '0';
    $regTimeText = !empty($patient['regtime']) ? substr($patient['regtime'], 0, 5) . ' น.' : '';
    $dchTimeText = !empty($patient['dchtime']) ? substr($patient['dchtime'], 0, 5) . ' น.' : '';
}

// =====================================================
// 2. ดึงข้อมูล Clinical Summary จาก prs_clinical_summary
// =====================================================
$chiefComplaint = '';
$presentIllness = '';
$diagnosis = '';
$treatment = '';
$planNote = '';

try {
    $summarySql = "SELECT progression,follow_up
    FROM " . DbConstant::KPHIS_DBNAME . ".prs_clinical_summary
    WHERE an = :an LIMIT 1";

    $summaryStmt = $conn->prepare($summarySql);
    $summaryStmt->bindParam(':an', $an);
    $summaryStmt->execute();
    $summary = $summaryStmt->fetch(PDO::FETCH_ASSOC);

    if ($summary) {
        $progression = $summary['progression'] ?? '';
        $chiefComplaint = $summary['chief_complaint'] ?? '';
        $presentIllness = $summary['present_illness'] ?? '';
        $diagnosis = $summary['diagnosis'] ?? '';
        $treatment = $summary['treatment'] ?? '';
        $planNote = $summary['plan_note'] ?? '';
        $follow_up = $summary['follow_up'] ?? '';
    }
} catch (Exception $e) {
    // ตาราง prs_clinical_summary อาจยังไม่ได้สร้าง - ข้ามไป
}

// =====================================================
// 3. ดึงข้อมูลการวินิจฉัย ICD จาก HOSxP
// =====================================================
$icdRows = [];
try {
    $icdSql = "SELECT i.icd10, ic.name, i.diagtype
    FROM " . DbConstant::HOSXP_DBNAME . ".iptdiag i
    LEFT JOIN " . DbConstant::HOSXP_DBNAME . ".icd101 ic ON ic.code = i.icd10
    WHERE i.an = :an
    ORDER BY i.diagtype, i.icd10";

    $icdStmt = $conn->prepare($icdSql);
    $icdStmt->bindParam(':an', $an);
    $icdStmt->execute();
    $icdRows = $icdStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // ข้ามถ้า query ผิดพลาด
}

// =====================================================
// 4. ดึงข้อมูลยา Discharge (Home Medication)
// =====================================================
$hmRows = [];
try {
    $hmSql = "SELECT op.icode, d.name AS drug_name, d.sticker_short_name,
           op.qty, d.units, op.unitprice,
           COALESCE(du.name, op.drugusage) AS usage_name
    FROM " . DbConstant::HOSXP_DBNAME . ".opitemrece op
    LEFT JOIN " . DbConstant::HOSXP_DBNAME . ".drugitems d ON d.icode = op.icode
    LEFT JOIN " . DbConstant::HOSXP_DBNAME . ".drugusage du ON du.drugusage = op.drugusage
    WHERE op.an = :an
    AND d.name IS NOT NULL
    ORDER BY op.rxdate, d.name";

    $hmStmt = $conn->prepare($hmSql);
    $hmStmt->bindParam(':an', $an);
    $hmStmt->execute();
    $hmRows = $hmStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // ข้ามถ้า query ผิดพลาด
}

// =====================================================
// 5. สร้าง HTML
// =====================================================
$html = '
<style>
    body {
        font-family: "Garuda";
        font-size: 12px;
        line-height: 1.5;
    }
    .report-title {
        text-align: center;
        font-size: 16pt;
        font-weight: bold;
        margin-bottom: 5px;
    }
    .report-subtitle {
        text-align: center;
        font-size: 10pt;
        margin-bottom: 10px;
        color: #444;
    }
    .patient-header {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #000;
        margin-bottom: 10px;
    }
    .patient-header td {
        padding: 3px 8px;
        font-size: 11px;
        border: 1px solid #ccc;
    }
    .patient-header .label {
        font-weight: bold;
        background-color: #f5f5f5;
        width: 80px;
        white-space: nowrap;
    }
    .section-title {
        font-weight: bold;
        font-size: 12px;
        background-color: #e8e8e8;
        border: 1px solid #999;
        padding: 3px 8px;
        margin-top: 8px;
        margin-bottom: 3px;
    }
    .section-content {
        border: 1px solid #ccc;
        border-top: none;
        padding: 5px 10px;
        min-height: 25px;
        font-size: 11px;
        white-space: pre-wrap;
        word-wrap: break-word;
    }
    .section-content-line {
        padding: 2px 0;
        border-bottom: 1px dotted #ccc;
        min-height: 18px;
        font-size: 11px;
    }
    .dx-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
        margin-top: 3px;
    }
    .dx-table th, .dx-table td {
        border: 1px solid #999;
        padding: 2px 6px;
        text-align: left;
    }
    .dx-table th {
        background-color: #e8e8e8;
        font-weight: bold;
        text-align: center;
    }
    .hm-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 10px;
        margin-top: 3px;
    }
    .hm-table th, .hm-table td {
        border: 1px solid #999;
        padding: 2px 5px;
    }
    .hm-table th {
        background-color: #e8e8e8;
        font-weight: bold;
        text-align: center;
    }
    .hm-table td.drug-name {
        text-align: left;
    }
    .hm-table td.center {
        text-align: center;
    }
    .hm-table td.right {
        text-align: right;
    }
    .checkbox-row {
        padding: 2px 0;
        font-size: 11px;
    }
    .sign-section {
        margin-top: 30px;
        text-align: right;
        padding-right: 30px;
        font-size: 11px;
    }
    .two-col {
        width: 100%;
        border-collapse: collapse;
    }
    .two-col td {
        padding: 2px 5px;
        vertical-align: top;
    }
    .inline-section {
        border: 1px solid #ccc;
        padding: 3px 8px;
        min-height: 20px;
        font-size: 11px;
    }
    .footer-info {
        font-size: 9px;
        color: #888;
        text-align: right;
        margin-top: 15px;
        border-top: 1px solid #ccc;
        padding-top: 3px;
    }
</style>';

// --- หัวเรื่อง ---
$html .= '<div class="report-title">Summary Discharge ' . htmlspecialchars(DbConstant::HOSPITAL_NAME) . '</div>';
//$html .= '<div class="report-subtitle">' . htmlspecialchars(DbConstant::HOSPITAL_NAME) . '</div>';

// --- ข้อมูลผู้ป่วย ---
$html .= '<table class="patient-header">';
$html .= '<tr>';
$html .= '<td class="label">ชื่อ-สกุล</td>';
$html .= '<td colspan="3"><b>' . htmlspecialchars($patientName) . '</b></td>';
$html .= '<td class="label">HN</td>';
$html .= '<td><b>' . htmlspecialchars($hn) . '</b></td>';
$html .= '<td class="label">AN</td>';
$html .= '<td><b>' . htmlspecialchars($an) . '</b></td>';
$html .= '</tr>';
$html .= '<tr>';
$html .= '<td class="label">Ward</td>';
$html .= '<td>' . htmlspecialchars($wardName) . '</td>';
$html .= '<td class="label">อายุ</td>';
$html .= '<td>' . htmlspecialchars($ageText) . '</td>';
$html .= '<td class="label">Date Adm</td>';
$html .= '<td>' . $regDateText . ' ' . ($regTimeText ?? '') . '</td>';
$html .= '<td class="label">Date D/C</td>';
$html .= '<td>' . $dchDateText . ' ' . ($dchTimeText ?? '') . '</td>';
$html .= '</tr>';
$html .= '<tr>';
$html .= '<td class="label">จำนวนวันนอน</td>';
$html .= '<td colspan="7">' . htmlspecialchars($admitDays) . ' วัน</td>';
$html .= '</tr>';
$html .= '</table>';

// --- CC (Chief Complaint) ---
$html .= '<div class="section-title">ข้อมูล</div>';
$html .= '<div class="section-content">' . nl2br(htmlspecialchars($progression)) . '&nbsp;</div>';

// --- PI (Present Illness) ---
$html .= '<div class="section-title">F/U</div>';
$html .= '<div class="section-content">' . nl2br(htmlspecialchars($follow_up)) . '&nbsp;</div>';

// --- Lab / X-ray / U/S ---
//$html .= '<table class="two-col">';
//$html .= '<tr>';
//$html .= '<td width="33%"><b>Lab</b> <span class="inline-section">&nbsp;</span></td>';
//$html .= '<td width="33%"><b>X-ray</b> <span class="inline-section">&nbsp;</span></td>';
//$html .= '<td width="34%"><b>U/S</b> <span class="inline-section">&nbsp;</span></td>';
//$html .= '</tr>';
//$html .= '</table>';

// --- Treatment ---
//$html .= '<div class="section-title">Treatment (การรักษา)</div>';
//$html .= '<div class="section-content">' . nl2br(htmlspecialchars($treatment)) . '&nbsp;</div>';

// --- DX (Diagnosis) ---
//$html .= '<div class="section-title">DX (การวินิจฉัยโรค)</div>';
if (!empty($diagnosis)) {
    $html .= '<div class="section-content">' . nl2br(htmlspecialchars($diagnosis)) . '</div>';
}
if (!empty($icdRows)) {
    $html .= '<table class="dx-table">';
    $html .= '<tr><th style="width:5%;">#</th><th style="width:15%;">ICD-10</th><th style="width:55%;">คำอธิบาย</th><th style="width:25%;">ประเภท</th></tr>';
    $diagTypes = [
        '1' => 'Principal Diagnosis',
        '2' => 'Comorbidity',
        '3' => 'Complication',
        '4' => 'Other',
        '5' => 'External Cause',
    ];
    $seq = 0;
    foreach ($icdRows as $icd) {
        $seq++;
        $diagLabel = $diagTypes[$icd['diagtype'] ?? ''] ?? ($icd['diagtype'] ?? '-');
        $html .= '<tr>';
        $html .= '<td class="center">' . $seq . '</td>';
        $html .= '<td>' . htmlspecialchars($icd['icd10'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($icd['name'] ?? '') . '</td>';
        $html .= '<td class="center">' . htmlspecialchars($diagLabel) . '</td>';
        $html .= '</tr>';
    }
    $html .= '</table>';
} else {
    if (empty($diagnosis)) {
        $html .= '<div class="section-content">&nbsp;</div>';
    }
}

// --- Operation ---
//$html .= '<div class="section-title">Operation</div>';
//$html .= '<div class="section-content">&nbsp;</div>';

// --- Plan ---
//$html .= '<div class="section-title">Plan (แผนการรักษา/จำหน่าย)</div>';
//$html .= '<div class="section-content">';
//$html .= '<div class="checkbox-row">☐ นัดตรวจตามแพทย์สั่ง</div>';
//$html .= '<div class="checkbox-row">☐ ส่งต่อดูแลต่อเนื่อง</div>';
//$html .= '<div class="checkbox-row">☐ ส่ง/ลงชุมชน สาธารณสุขอำเภอ/จังหวัด</div>';
//$html .= '<div class="checkbox-row">☐ อื่นๆ ' . nl2br(htmlspecialchars($planNote)) . '</div>';
//$html .= '</div>';

// --- Result ---
//$html .= '<div class="section-title">Result</div>';
//$html .= '<div class="section-content">Improved&nbsp;</div>';

// --- F/U (Follow-up) ---
//$html .= '<div class="section-title">F/U (นัดติดตาม)</div>';
//$html .= '<div class="section-content">&nbsp;</div>';

// --- HM (Home Medication) ---
//$html .= '<div class="section-title">HM (ยากลับบ้าน)</div>';
/*if (!empty($hmRows)) {
    $html .= '<table class="hm-table">';
    $html .= '<tr>';
    $html .= '<th style="width:4%;">#</th>';
    $html .= '<th style="width:12%;">รหัสยา</th>';
    $html .= '<th style="width:35%;">ชื่อยา</th>';
    $html .= '<th style="width:30%;">วิธีใช้</th>';
    $html .= '<th style="width:9%;">จำนวน</th>';
    $html .= '<th style="width:10%;">หน่วย</th>';
    $html .= '</tr>';
    $hmSeq = 0;
    foreach ($hmRows as $hm) {
        $hmSeq++;
        $drugDisplayName = !empty($hm['sticker_short_name']) ? $hm['sticker_short_name'] : ($hm['drug_name'] ?? '');
        $html .= '<tr>';
        $html .= '<td class="center">' . $hmSeq . '</td>';
        $html .= '<td class="center">' . htmlspecialchars($hm['icode'] ?? '') . '</td>';
        $html .= '<td class="drug-name">' . htmlspecialchars($drugDisplayName) . '</td>';
        $html .= '<td>' . htmlspecialchars($hm['usage_name'] ?? '') . '</td>';
        $html .= '<td class="right">' . htmlspecialchars($hm['qty'] ?? '') . '</td>';
        $html .= '<td class="center">' . htmlspecialchars($hm['units'] ?? '') . '</td>';
        $html .= '</tr>';
    }
    $html .= '</table>';
} else {
    $html .= '<div class="section-content">- ไม่มีรายการยา -</div>';
} */

// --- ลายเซ็น ---
$html .= '<div class="sign-section">';
$html .= '<br><br>';
$html .= 'ลงชื่อแพทย์ผู้รักษา........................................................................<br>';
$html .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(........................................................................)<br>';
$html .= '<br>';
$html .= 'วันที่ ......./......./.........&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; เวลา .............';
$html .= '</div>';

// --- Footer ---
$printDate = ShortDateThai(date('Y-m-d')) . ' ' . date('H:i');
$html .= '<div class="footer-info">';
$html .= 'พิมพ์โดย: ' . htmlspecialchars($loginname) . ' | วันเวลาพิมพ์: ' . $printDate;
$html .= '</div>';

// =====================================================
// 6. Output PDF
// =====================================================
$mpdf->setAutoTopMargin = 'stretch';
$mpdf->setAutoBottomMargin = 'stretch';
$mpdf->setFooter('HN: ' . htmlspecialchars($hn) . ' AN: ' . htmlspecialchars($an) . ' | {PAGENO}/{nbpg}');
$mpdf->WriteHTML($html);
$mpdf->Output('Summary_Discharge_AN_' . $an . '.pdf', 'I');
