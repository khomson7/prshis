<?php
require_once '../mains/datethai.php';
require_once '../include/Session.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';

date_default_timezone_set('Asia/Bangkok');

$conn = DbUtils::get_hosxp_connection();
$an = isset($_REQUEST['an']) ? trim($_REQUEST['an']) : '';

if (!$an) {
    die('<div style="font-family:sans-serif;padding:20px;color:red;">ไม่พบ AN</div>');
}

// ---- ดึงข้อมูลแบบประเมิน ----
$sql = "SELECT * FROM `prs_latch_postnatal` WHERE an = :an LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute(['an' => $an]);
$row = $stmt->fetch();

if (!$row) {
    die('<div style="font-family:sans-serif;padding:20px;color:red;">ไม่พบข้อมูลการประเมิน LATCH สำหรับ AN นี้</div>');
}

// ---- ดึงข้อมูลผู้ป่วย ----
$sql_ipt = "SELECT p.hn, p.pname, p.fname, p.lname,
                   i.regdate, i.dchdate,
                   w.name AS ward_name
            FROM " . DbConstant::HOSXP_DBNAME . ".ipt i
            LEFT JOIN " . DbConstant::HOSXP_DBNAME . ".patient p ON p.hn = i.hn
            LEFT JOIN " . DbConstant::HOSXP_DBNAME . ".ward   w ON w.ward = i.ward
            WHERE i.an = :an";
$stmt_ipt = $conn->prepare($sql_ipt);
$stmt_ipt->execute(['an' => $an]);
$row_ipt = $stmt_ipt->fetch();

// Helper
function sv($row, $key, $def = null)
{
    return isset($row[$key]) ? $row[$key] : $def;
}
function thaiDate($dateStr)
{
    if (!$dateStr || $dateStr === '0000-00-00')
        return '-';
    $months = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
    $p = explode('-', $dateStr);
    if (count($p) !== 3)
        return $dateStr;
    return (int) $p[2] . ' ' . $months[(int) $p[1]] . ' ' . ((int) $p[0] + 543);
}

// ---- HTML สำหรับ mPDF ----
$html = '
<style>
    body { font-family: "Garuda"; font-size: 9pt; color: #222; }
    h1 { font-size: 11pt; text-align: center; margin: 0 0 4px 0; }
    h2 { font-size: 9pt; text-align: center; margin: 0 0 8px 0; font-weight: normal; }
    table { width: 100%; border-collapse: collapse; margin-top: 5px; }
    th, td { border: 1px solid #555; padding: 4px 4px; vertical-align: middle; }
    th { background-color: #f0f0f0; text-align: center; font-weight: bold; }
    .no-border { border: none !important; }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .hdr-table td { border: none; padding: 2px 3px; font-size: 9pt; }
    .bg-light { background-color: #f9f9f9; }
    .section-title { font-weight: bold; background-color: #d9edf7; padding: 4px; border: 1px solid #555; margin-top: 8px; font-size: 9.5pt; }
</style>

<h1>แบบประเมิน LATCH score และสุขภาพจิตมารดาหลังคลอด (FM-NSO-OBG-015-01)</h1>
<div style="text-align:center; font-size:8pt; margin-bottom:6px;">' . htmlspecialchars(DbConstant::HOSPITAL_NAME) . '</div>

<table class="hdr-table" style="margin-bottom:6px;">
    <tr>
        <td style="width:25%;"><b>HN:</b> ' . htmlspecialchars($row_ipt['hn'] ?? '-') . '</td>
        <td style="width:50%;"><b>ชื่อ-สกุล:</b> ' . htmlspecialchars(($row_ipt['pname'] ?? '') . ($row_ipt['fname'] ?? '') . ' ' . ($row_ipt['lname'] ?? '')) . '</td>
        <td style="width:25%;"><b>AN:</b> ' . htmlspecialchars($an) . '</td>
    </tr>
    <tr>
        <td><b>หอผู้ป่วย:</b> ' . htmlspecialchars($row_ipt['ward_name'] ?? '-') . '</td>
        <td><b>วันที่รับ:</b> ' . thaiDate($row_ipt['regdate'] ?? '') . '</td>
        <td><b>วันที่จำหน่าย:</b> ' . thaiDate($row_ipt['dchdate'] ?? '') . '</td>
    </tr>
</table>

<div class="section-title">ส่วนที่ 1 — การประเมิน LATCH และประสิทธิภาพการไหลของน้ำนม (4 ครั้ง × 3 เวลา)</div>
<table>
    <thead>
        <tr>
            <th rowspan="2" style="width:14%">ประเมิน</th>';
for ($d = 1; $d <= 4; $d++) {
    $dateText = thaiDate(sv($row, "assess_date_$d"));
    $html .= '<th colspan="3" style="width:21.5%">' . ($dateText === '-' ? 'วัน/เดือน/ปี: ________' : $dateText) . '</th>';
}
$html .= '</tr><tr>';
for ($d = 1; $d <= 4; $d++) {
    for ($t = 1; $t <= 3; $t++) {
        $val = sv($row, "assess_time_{$d}_{$t}");
        $html .= '<th style="font-size:7.5pt; font-weight:normal; background-color:#f9f9f9;">เวลา ' . ($val ?: '-') . '</th>';
    }
}
$html .= '</tr>
    </thead>
    <tbody>';

$latchItems = [
    ['key' => 'l', 'label' => 'L = Latch'],
    ['key' => 'a', 'label' => 'A = Audible'],
    ['key' => 't', 'label' => 'T = Type of nipple'],
    ['key' => 'c', 'label' => 'C = Comfort'],
    ['key' => 'h', 'label' => 'H = Hold'],
    ['key' => 'milk_level', 'label' => 'ระดับน้ำนม']
];
foreach ($latchItems as $item) {
    $html .= '<tr><td style="font-size:8pt;">' . $item['label'] . '</td>';
    $fkey = ($item['key'] === 'milk_level') ? 'milk_level' : 'latch_' . $item['key'];
    for ($d = 1; $d <= 4; $d++) {
        for ($t = 1; $t <= 3; $t++) {
            $val = sv($row, "{$fkey}_{$d}_{$t}");
            $html .= '<td class="text-center">' . ($val !== null ? $val : '-') . '</td>';
        }
    }
    $html .= '</tr>';
}

$html .= '<tr style="background:#e8f5e9;">
            <td><b>คะแนนรวม</b></td>';
for ($d = 1; $d <= 4; $d++) {
    for ($t = 1; $t <= 3; $t++) {
        $val = sv($row, "latch_total_{$d}_{$t}");
        $html .= '<td class="text-center"><b>' . ($val !== null ? $val : '-') . '</b></td>';
    }
}
$html .= '</tr>
    </tbody>
</table>

<div class="section-title">ส่วนที่ 2 — การประเมินสุขภาพจิตมารดาหลังคลอด</div>
<table style="margin-top:0;">
    <tr>
        <td style="width:50%; vertical-align:top; border-right:none;">
            <b>การประเมินความเครียด</b>
            <table style="width:100%; margin-top:4px;">
                <tr><th style="width:80%">อาการ</th><th style="width:20%">คะแนน</th></tr>';
$sItems = [1 => '1. มีปัญหาการนอน นอนไม่หลับหรือนอนมากเกินไป', 2 => '2. มีสมาธิน้อยลง', 3 => '3. หงุดหงิด/กระวนกระวาย/ว้าวุ่นใจ', 4 => '4. รู้สึกเบื่อ เซ็ง', 5 => '5. ไม่อยากพบปะผู้คน'];
foreach ($sItems as $qn => $ql) {
    $val = sv($row, "stress_q$qn");
    $html .= '<tr><td>' . $ql . '</td><td class="text-center">' . ($val !== null ? $val : '-') . '</td></tr>';
}
$st = sv($row, 'stress_total');
$interp = '-';
if ($st !== null) {
    if ($st <= 4)
        $interp = '0-4 หมายถึง ไม่มีความเครียดในระดับที่ก่อให้เกิดปัญหากับตัวเอง ยังสามารถจัดการกับความเครียดที่เกิดขึ้นในชีวิตประจำวันได้ และปรับตัวกับสถานการณ์ได้';
    else if ($st <= 7)
        $interp = '5-7 หมายถึง สงสัยว่ามีปัญหาความเครียด ควรผ่อนคลายด้วยการพูด ปรีกษาคนใกล้ชิดใช้หลักการทางศาสนาเพื่อผ่อนคลายความกังวล';
    else
        $interp = '8 ขึ้นไป หมายถึง มีความเครียดสูงอาจส่งผลต่อร่างกาย เช่น ปวดหัว ปวดหลังนอนไม่หลับ ควรได้รับคำปรึกษาจากบุคลากรสาธารณสุข';
}
$html .= '<tr class="bg-light">
            <td class="text-right"><b>คะแนนรวม:</b></td>
            <td class="text-center"><b>' . ($st !== null ? $st : '-') . '</b></td>
          </tr>
          </table>
        </td>
        <td style="width:50%; vertical-align:top; border-left:none;">
            <b>การคัดกรองโรคซึมเศร้า</b>
            <div style="margin-top:8px;">
                1. หดหู่ เศร้า หรือท้อแท้สิ้นหวังหรือไม่?<br>
                <b>ตอบ:</b> ' . (sv($row, 'depression_q1') === '1' ? 'มี' : 'ไม่มี') . '
            </div>
            <div style="margin-top:12px;">
                2. เบื่อเอือมหรือทำอะไรไม่เพลิดเพลินหรือไม่?<br>
                <b>ตอบ:</b> ' . (sv($row, 'depression_q2') === '1' ? 'มี' : 'ไม่มี') . '
            </div>
        </td>
    </tr>
    <tr>
        <td colspan="2" style="font-size:8.5pt; padding:4px 6px;">
            <b>แปลผลความเครียด:</b> ' . $interp . '
        </td>
    </tr>
</table>

<div class="section-title">ส่วนที่ 3 — การดื่มสุรา</div>
<div style="border:1px solid #555; padding:6px; margin-top:0;">
    <b>ในรอบ 1 ปีที่ผ่านมา คุณเคยดื่มสุราหรือไม่?</b><br>
    <b>ตอบ:</b> ' . (sv($row, 'alcohol_ever') === '1' ? '<span style="color:red;font-weight:bold;">เคย</span>' : 'ไม่เคย') . '<br>
    <div style="margin-top:6px;">
        <b>การส่งต่อข้อมูลเพื่อพบแพทย์ภาวะผิดปกติ:</b><br>
        ' . nl2br(htmlspecialchars(sv($row, 'alcohol_refer', '-'))) . '
    </div>
</div>

<div style="margin-top:20px; text-align:right; font-size:9pt;">
    <b>ผู้บันทึก:</b> ' . htmlspecialchars($row['updated_by'] ?: $row['created_by']) . '<br>
    <b>วันที่บันทึก:</b> ' . thaiDate(substr($row['updated_at'] ?: $row['created_at'], 0, 10)) . '
</div>';

$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
$mpdf->AddPageByArray([
    'margin-left' => 12,
    'margin-right' => 12,
    'margin-top' => 12,
    'margin-bottom' => 12,
]);
$mpdf->WriteHTML($html);
$mpdf->Output('LATCH_POSTNATAL_' . $an . '_' . date('Ymd') . '.pdf', 'I');
