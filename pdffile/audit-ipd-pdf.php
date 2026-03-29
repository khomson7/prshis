<?php
require_once '../mains/datethai.php';
require_once '../include/Session.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';
require_once '../include/ReportQueryUtils.php';

date_default_timezone_set('asia/bangkok');
$conn = DbUtils::get_hosxp_connection();
$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4-L']);
$mpdf->AddPageByArray([
    'margin-left' => 10,
    'margin-right' => 10,
    'margin-top' => 10,
    'margin-bottom' => 10,
]);

$an = $_REQUEST['an'];
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : null;

if (!$id) {
    $sql_check = "SELECT id FROM prs_audit_ipd WHERE an = :an ORDER BY id DESC LIMIT 1";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->execute(['an' => $an]);
    $id = $stmt_check->fetchColumn();
}

if (!$id) {
    die("No audit record found for this AN.");
}

// Fetch Master
$sql = "SELECT * FROM prs_audit_ipd WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute(['id' => $id]);
$row = $stmt->fetch();

// Fetch Items
$sql_item = "SELECT * FROM prs_audit_ipd_item WHERE audit_id = :id ORDER BY content_index ASC";
$stmt_item = $conn->prepare($sql_item);
$stmt_item->execute(['id' => $id]);
$items = [];
while($item_row = $stmt_item->fetch()) {
    $items[$item_row['content_index']] = $item_row;
}

// Patient Info
$sql_ipt = "SELECT p.hn, p.pname, p.fname, p.lname, i.regdate, i.dchdate, w.name as ward_name
            FROM " . DbConstant::HOSXP_DBNAME . ".ipt i
            LEFT JOIN " . DbConstant::HOSXP_DBNAME . ".patient p ON p.hn = i.hn
            LEFT JOIN " . DbConstant::HOSXP_DBNAME . ".ward w ON w.ward = i.ward
            WHERE i.an = :an";
$stmt_ipt = $conn->prepare($sql_ipt);
$stmt_ipt->execute(['an' => $an]);
$row_ipt = $stmt_ipt->fetch();

$contents = [
    1 => "1. Discharge summary: Dx,, OP",
    2 => "2. Discharge summary: Other",
    3 => "3. Informed consent",
    4 => "4. History",
    5 => "5. Physical exam",
    6 => "6. Progress note",
    7 => "7. Consultation record",
    8 => "8. Anesthetic record",
    9 => "9. Operative note",
    10 => "10. Labour record",
    11 => "11. Rehabilitation record",
    12 => "12. Nurses' note"
];

$html = '
<style>
    body { font-family: "Garuda"; font-size: 8pt; }
    table { width: 100%; border-collapse: collapse; margin-top: 5px; }
    th, td { border: 1px solid #000; padding: 2px; vertical-align: middle; }
    th { background-color: #f2f2f2; text-align: center; }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .header-table td { border: none; padding: 1px; }
</style>

<div style="text-align:center; font-weight:bold; font-size:10pt;">
    แบบตรวจประเมินคุณภาพการบันทึกเวชระเบียนผู้ป่วยใน<br>
    Medical Record Audit Form (IPD)
</div>

<table class="header-table">
    <tr>
        <td><b>Hcode:</b> ' . htmlspecialchars(DbConstant::HOSPITAL_CODE) . '</td>
        <td><b>Hname:</b> ' . htmlspecialchars(DbConstant::HOSPITAL_NAME) . '</td>
        <td><b>HN:</b> ' . htmlspecialchars($row_ipt['hn']) . '</td>
        <td><b>AN:</b> ' . htmlspecialchars($an) . '</td>
    </tr>
    <tr>
        <td colspan="2"><b>Date admitted:</b> ' . htmlspecialchars($row_ipt['regdate']) . '</td>
        <td><b>Date discharged:</b> ' . htmlspecialchars($row_ipt['dchdate']) . '</td>
        <td><b>Audit Date:</b> ' . htmlspecialchars($row['audit_date']) . '</td>
    </tr>
</table>

<table>
    <thead>
        <tr>
            <th rowspan="2" style="width: 25%;">หัวข้อการประเมิน</th>
            <th rowspan="2" style="width: 3%;">NA</th>
            <th rowspan="2" style="width: 4%;">Missing</th>
            <th rowspan="2" style="width: 3%;">No</th>
            <th colspan="9">เกณฑ์ (Criteria)</th>
            <th rowspan="2" style="width: 5%;">หัก</th>
            <th rowspan="2" style="width: 5%;">รวม</th>
            <th rowspan="2" style="width: 15%;">หมายเหตุ</th>
        </tr>
        <tr>
            <th>1</th><th>2</th><th>3</th><th>4</th><th>5</th><th>6</th><th>7</th><th>8</th><th>9</th>
        </tr>
    </thead>
    <tbody>';

foreach ($contents as $idx => $title) {
    $item = $items[$idx] ?? null;
    $html .= '<tr>
        <td>' . htmlspecialchars($title) . '</td>
        <td class="text-center">' . ($item['is_na'] ? '/' : '') . '</td>
        <td class="text-center">' . ($item['is_missing'] ? '/' : '') . '</td>
        <td class="text-center">' . ($item['is_no'] ? '/' : '') . '</td>';
    
    for ($c = 1; $c <= 9; $c++) {
        $html .= '<td class="text-center">';
        if (!$item['is_na']) {
            $html .= ($item['c'.$c] ? '1' : '0');
        }
        $html .= '</td>';
    }
    
    $html .= '<td class="text-center">' . ($item['deduct_score'] ?: '0') . '</td>
        <td class="text-center">' . ($item['total_score'] ?: '0') . '</td>
        <td><span style="font-size:7pt;">' . htmlspecialchars($item['remark'] ?: '') . '</span></td>
    </tr>';
}

$html .= '
    </tbody>
    <tfoot>
        <tr>
            <td colspan="14" class="text-right"><b>คะแนนเต็ม (Full score) รวม: (ต้องไม่น้อยกว่า 56 คะแนน)</b></td>
            <td class="text-center"><b>' . $row['full_score'] . '</b></td>
            <td></td>
        </tr>
        <tr>
            <td colspan="14" class="text-right"><b>คะแนนที่ได้ (Sum score):</b></td>
            <td class="text-center" style="font-size:12pt;"><b>' . $row['sum_score'] . '</b></td>
            <td></td>
        </tr>
    </tfoot>
</table>

<div style="margin-top:10px; font-weight:bold;">ประเมินคุณภาพการบันทึกเวชระเบียนในภาพรวม Overall finding</div>
<div style="margin-left:10px;">
    ' . ($row['overall_finding_1'] ? '[/] ' : '[ ] ') . 'การจัดเรียงเวชระเบียนไม่เป็นไปตามมาตรฐานที่กำหนด<br>
    ' . ($row['overall_finding_2'] ? '[/] ' : '[ ] ') . 'เอกสารบางแผ่นไม่มีข้อบ่งชี้บริการ HN AN ทำให้ไม่สามารถระบุได้ว่าเป็นของใครหรือเอกสารแผ่นนั้นใช้ไม่ได้ (ข้อมูลไม่เพียงพอสำหรับการทบทวน)<br>
    ' . ($row['overall_finding_3'] ? '[/] ' : '[ ] ') . 'Documentation inadequate for meaningful review (ไม่มีข้อมูลเพียงพอสำหรับการทบทวน)<br>
    ' . ($row['overall_finding_4'] ? '[/] ' : '[ ] ') . 'No significant medical record issue identified (ไม่มีปัญหาสำคัญจากการทบทวน)<br>
    ' . ($row['overall_finding_5'] ? '[/] ' : '[ ] ') . 'Certain issues in question specify: (มีปัญหาจากการทบทวนที่ต้องค้นต่อระบุ) ' . htmlspecialchars($row['overall_finding_text']) . '
</div>

<div style="margin-top:15px; text-align:right;">
    ลงชื่อ.............................................................. ผู้ตรวจประเมิน<br>
    ( ' . htmlspecialchars($row['audit_by']) . ' )<br>
    วันที่ ' . htmlspecialchars($row['audit_date']) . '
</div>
';

$mpdf->WriteHTML($html);
$mpdf->Output('Audit_IPD_' . $an . '.pdf', 'I');
