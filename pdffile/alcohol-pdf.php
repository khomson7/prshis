<?php
/**
 * alcohol-pdf.php
 * พิมพ์แบบประเมินปัญหาการดื่มสุรา AUDIT เป็น PDF
 * รูปแบบการแสดงผลเดียวกับ form-alcohol.php
 * อ้างอิงรูปแบบจาก audit-ipd-pdf.php
 */
require_once '../mains/datethai.php';
require_once '../include/Session.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';

date_default_timezone_set('Asia/Bangkok');

$conn = DbUtils::get_hosxp_connection();
$an   = isset($_REQUEST['an']) ? trim($_REQUEST['an']) : '';
$id   = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;

// ---- หาก id ไม่ถูกส่งมา ให้ดึงล่าสุดของ AN นี้ ----
if (!$id) {
    $sql_check = "SELECT id FROM prs_alcohol WHERE an = :an ORDER BY id DESC LIMIT 1";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->execute(['an' => $an]);
    $id = (int)$stmt_check->fetchColumn();
}

if (!$id) {
    die('<div style="font-family:sans-serif;padding:20px;color:red;">ไม่พบข้อมูลการประเมินสำหรับ AN นี้</div>');
}

// ---- ดึงข้อมูลหลัก ----
$sql_master = "SELECT * FROM prs_alcohol WHERE id = :id AND an = :an";
$stmt_master = $conn->prepare($sql_master);
$stmt_master->execute(['id' => $id, 'an' => $an]);
$row = $stmt_master->fetch();

if (!$row) {
    die('<div style="font-family:sans-serif;padding:20px;color:red;">ไม่พบข้อมูล (id=' . $id . ', an=' . htmlspecialchars($an) . ')</div>');
}

// ---- ดึงคะแนนรายข้อ ----
$sql_item = "SELECT * FROM prs_alcohol_item WHERE alcohol_id = :alcohol_id ORDER BY content_index ASC";
$stmt_item = $conn->prepare($sql_item);
$stmt_item->execute(['alcohol_id' => $id]);
$items = [];
while ($item_row = $stmt_item->fetch()) {
    $items[$item_row['content_index']] = $item_row;
}

// ---- ดึงข้อมูลผู้ป่วย ----
$sql_ipt = "SELECT p.hn, p.pname, p.fname, p.lname,
                   p.birthday,
                   i.regdate, i.regtime, i.dchdate, i.dchtime,
                   w.name AS ward_name
            FROM " . DbConstant::HOSXP_DBNAME . ".ipt i
            LEFT JOIN " . DbConstant::HOSXP_DBNAME . ".patient p ON p.hn = i.hn
            LEFT JOIN " . DbConstant::HOSXP_DBNAME . ".ward   w ON w.ward = i.ward
            WHERE i.an = :an";
$stmt_ipt = $conn->prepare($sql_ipt);
$stmt_ipt->execute(['an' => $an]);
$row_ipt = $stmt_ipt->fetch();

// ---- คำถาม AUDIT ทั้ง 10 ข้อ ----
$audit_questions = [
    1  => [
        'text'    => '1. คุณดื่มสุราบ่อยเพียงไร',
        'type'    => 'standard',
        'options' => [0 => 'ไม่เคยเลย', 1 => 'เดือนละครั้ง หรือน้อยกว่า', 2 => '2-4 ครั้งต่อเดือน', 3 => '2-3 ครั้งต่อสัปดาห์', 4 => '4 ครั้งขึ้นไปต่อสัปดาห์'],
    ],
    2  => [
        'text'    => '2. เวลาที่คุณดื่มสุรา โดยทั่วไปแล้วคุณดื่มประมาณเท่าไรต่อวัน',
        'type'    => 'q2',
        'options' => [0 => '1-2 ดื่มมาตรฐาน', 1 => '3-4 ดื่มมาตรฐาน', 2 => '5-6 ดื่มมาตรฐาน', 3 => '7-9 ดื่มมาตรฐาน', 4 => 'ตั้งแต่ 10 ดื่มมาตรฐานขึ้นไป'],
    ],
    3  => [
        'text'    => '3. บ่อยครั้งเพียงไรที่คุณดื่มตั้งแต่ 6 ดื่มมาตรฐานขึ้นไป หรือเบียร์ 4 กระป๋อง/2 ขวดใหญ่ขึ้นไป หรือเหล้าวิสกี้ 3 เป๊กขึ้นไป',
        'type'    => 'standard',
        'options' => [0 => 'ไม่เคยเลย', 1 => 'น้อยกว่าเดือนละครั้ง', 2 => 'เดือนละครั้ง', 3 => 'สัปดาห์ละครั้ง', 4 => 'ทุกวัน หรือเกือบทุกวัน'],
    ],
    4  => [
        'text'    => '4. ในช่วงหนึ่งปีที่แล้ว มีบ่อยครั้งเพียงไรที่คุณพบว่าคุณไม่สามารถหยุดดื่มได้ หากคุณได้เริ่มดื่มไปแล้ว',
        'type'    => 'standard',
        'options' => [0 => 'ไม่เคยเลย', 1 => 'น้อยกว่าเดือนละครั้ง', 2 => 'เดือนละครั้ง', 3 => 'สัปดาห์ละครั้ง', 4 => 'ทุกวัน หรือเกือบทุกวัน'],
    ],
    5  => [
        'text'    => '5. ในช่วงหนึ่งปีที่แล้ว มีบ่อยเพียงไรที่คุณไม่ได้ทำสิ่งที่คุณควรจะทำตามปกติ เพราะคุณมัวแต่ไปดื่มสุรา',
        'type'    => 'standard',
        'options' => [0 => 'ไม่เคยเลย', 1 => 'น้อยกว่าเดือนละครั้ง', 2 => 'เดือนละครั้ง', 3 => 'สัปดาห์ละครั้ง', 4 => 'ทุกวัน หรือเกือบทุกวัน'],
    ],
    6  => [
        'text'    => '6. ในช่วงหนึ่งปีที่แล้ว มีบ่อยเพียงไรที่คุณต้องรีบดื่มสุราทันทีในตอนเช้า เพื่อจะได้ดำเนินชีวิตตามปกติหรือถอนอาการเมาค้าง',
        'type'    => 'standard',
        'options' => [0 => 'ไม่เคยเลย', 1 => 'น้อยกว่าเดือนละครั้ง', 2 => 'เดือนละครั้ง', 3 => 'สัปดาห์ละครั้ง', 4 => 'ทุกวัน หรือเกือบทุกวัน'],
    ],
    7  => [
        'text'    => '7. ในช่วงหนึ่งปีที่แล้ว มีบ่อยเพียงไรที่คุณรู้สึกไม่ดี โกรธหรือเสียใจ เนื่องจากคุณได้ทำบางสิ่งขณะที่ดื่มสุรา',
        'type'    => 'standard',
        'options' => [0 => 'ไม่เคยเลย', 1 => 'น้อยกว่าเดือนละครั้ง', 2 => 'เดือนละครั้ง', 3 => 'สัปดาห์ละครั้ง', 4 => 'ทุกวัน หรือเกือบทุกวัน'],
    ],
    8  => [
        'text'    => '8. ในช่วงหนึ่งปีที่แล้ว มีบ่อยเพียงไรที่คุณไม่สามารถจำได้ว่าเกิดอะไรขึ้นในคืนที่ผ่านมา เพราะว่าคุณได้ดื่มสุรา',
        'type'    => 'standard',
        'options' => [0 => 'ไม่เคยเลย', 1 => 'น้อยกว่าเดือนละครั้ง', 2 => 'เดือนละครั้ง', 3 => 'สัปดาห์ละครั้ง', 4 => 'ทุกวัน หรือเกือบทุกวัน'],
    ],
    9  => [
        'text'    => '9. ตัวคุณเองหรือคนอื่น เคยได้รับบาดเจ็บซึ่งเป็นผลจากการดื่มสุราของคุณหรือไม่',
        'type'    => 'q9q10',
        'options' => [0 => 'ไม่เคยเลย', 2 => 'เคย แต่ไม่ได้เกิดขึ้นในปีที่แล้ว', 4 => 'เคยเกิดขึ้นในช่วงหนึ่งปีที่แล้ว'],
    ],
    10 => [
        'text'    => '10. เคยมีแพทย์ หรือบุคลากรทางการแพทย์หรือเพื่อนฝูงหรือญาติพี่น้องแสดงความเป็นห่วงต่อการดื่มสุราของคุณหรือไม่',
        'type'    => 'q9q10',
        'options' => [0 => 'ไม่เคยเลย', 2 => 'เคย แต่ไม่ได้เกิดขึ้นในปีที่แล้ว', 4 => 'เคยเกิดขึ้นในช่วงหนึ่งปีที่แล้ว'],
    ],
];

// ---- ฟังก์ชันช่วย: แปลงวันที่เป็นภาษาไทย พ.ศ. ----
function thaiDate($dateStr) {
    if (!$dateStr || $dateStr === '0000-00-00') return '-';
    $months = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
    $parts = explode('-', $dateStr);
    if (count($parts) !== 3) return $dateStr;
    return (int)$parts[2] . ' ' . $months[(int)$parts[1]] . ' ' . ((int)$parts[0] + 543);
}

// ---- คำนวณ interpretation ----
$sum = (int)$row['sum_score'];
if ($sum <= 7)       { $interp_th = 'ผู้ดื่มแบบเสี่ยงต่ำ';  $interp_en = 'Low risk drinker';   $interp_range = '0-7';    $interp_color = '#155724'; $interp_bg = '#d4edda'; $interp_idx = 0; }
elseif ($sum <= 15)  { $interp_th = 'ผู้ดื่มแบบเสี่ยง';      $interp_en = 'Hazardous drinker'; $interp_range = '8-15';   $interp_color = '#856404'; $interp_bg = '#fff3cd'; $interp_idx = 1; }
elseif ($sum <= 19)  { $interp_th = 'ผู้ดื่มแบบอันตราย';    $interp_en = 'Harmful use';        $interp_range = '16-19';  $interp_color = '#7d3900'; $interp_bg = '#ffe8cc'; $interp_idx = 2; }
else                 { $interp_th = 'ผู้ดื่มแบบติด';         $interp_en = 'Alcohol dependence'; $interp_range = '20 ขึ้นไป'; $interp_color = '#721c24'; $interp_bg = '#f8d7da'; $interp_idx = 3; }

// ข้อมูลทั้ง 4 ระดับสำหรับตารางแปลผล
$interp_levels = [
    0 => ['range'=>'0-7',      'th'=>'ผู้ดื่มแบบเสี่ยงต่ำ',   'en'=>'Low risk drinker',   'border'=>'#28a745', 'bg'=>'#d4edda', 'color'=>'#155724'],
    1 => ['range'=>'8-15',     'th'=>'ผู้ดื่มแบบเสี่ยง',       'en'=>'Hazardous drinker',  'border'=>'#d39e00', 'bg'=>'#fff3cd', 'color'=>'#856404'],
    2 => ['range'=>'16-19',    'th'=>'ผู้ดื่มแบบอันตราย',     'en'=>'Harmful use',         'border'=>'#e07b00', 'bg'=>'#ffe8cc', 'color'=>'#7d3900'],
    3 => ['range'=>'20 ขึ้นไป','th'=>'ผู้ดื่มแบบติด',         'en'=>'Alcohol dependence',  'border'=>'#dc3545', 'bg'=>'#f8d7da', 'color'=>'#721c24'],
];

// ---- สร้าง HTML สำหรับ mPDF ----
$html = '
<style>
    body        { font-family: "Garuda"; font-size: 9pt; color: #222; }
    h1          { font-size: 12pt; text-align: center; margin: 0 0 4px 0; }
    h2          { font-size: 10pt; text-align: center; margin: 0 0 8px 0; font-weight: normal; }
    table       { width: 100%; border-collapse: collapse; margin-top: 5px; }
    th, td      { border: 1px solid #555; padding: 3px 4px; vertical-align: middle; }
    th          { background-color: #2c6e49; color: #fff; text-align: center; font-weight: bold; }
    .no-border  { border: none !important; }
    .text-center{ text-align: center; }
    .text-right { text-align: right; }
    .hdr-table td { border: none; padding: 1px 3px; font-size: 9pt; }
    .score-checked { font-weight: bold; font-size: 8pt; color: #fff; }
    .cell-selected { background-color: #2c6e49 !important; color: #fff; font-weight: bold; }
    .cell-empty    { background-color: #f0f0f0; color: #bbb; }
    .even-row   { background-color: #f0f7f0; }
    .tfoot-row  { background-color: #e8f5e9; }
    .interp-box { border: 2px solid; border-radius: 4px; padding: 6px 10px; margin-top: 10px; }
</style>

<!-- หัวเรื่อง -->
<h1>แบบประเมินปัญหาการดื่มสุรา</h1>
<h2>AUDIT : Alcohol Use Disorders Identification Test</h2>
<div style="text-align:center; font-size:8pt; margin-bottom:6px;">
    ' . htmlspecialchars(DbConstant::HOSPITAL_NAME) . ' | รหัสสถานพยาบาล: ' . htmlspecialchars(DbConstant::HOSPITAL_CODE) . '
</div>

<!-- ข้อมูลผู้ป่วย -->
<table class="hdr-table" style="margin-bottom:6px;">
    <tr>
        <td style="width:20%;"><b>HN:</b> ' . htmlspecialchars($row_ipt['hn'] ?? '-') . '</td>
        <td style="width:40%;"><b>ชื่อ-สกุล:</b> ' . htmlspecialchars(($row_ipt['pname'] ?? '') . ($row_ipt['fname'] ?? '') . ' ' . ($row_ipt['lname'] ?? '')) . '</td>
        <td style="width:20%;"><b>วันที่ประเมิน:</b> ' . thaiDate($row['audit_date']) . '</td>
    </tr>
    <tr>
        <td><b>หอผู้ป่วย:</b> ' . htmlspecialchars($row_ipt['ward_name'] ?? '-') . '</td>
        <td><b>วันที่รับ:</b> ' . thaiDate($row_ipt['regdate'] ?? '') . '</td>
        <td><b>วันที่จำหน่าย:</b> ' . thaiDate($row_ipt['dchdate'] ?? '') . '</td>
        
    </tr>
</table>

<div style="background:#e8f4fd; border:1px solid #bee5eb; border-radius:3px; padding:5px 8px; font-size:8pt; margin-bottom:6px;">
    <b>คำชี้แจง:</b> คำถามแต่ละข้อต่อไปนี้จะถามถึงประสบการณ์การดื่มสุราในรอบ 1 ปีที่ผ่านมา
    โดยสุรา หมายถึงเครื่องดื่มที่มีแอลกอฮอล์ทุกชนิด ได้แก่ เบียร์ เหล้า สาโท กระแช่ วิสกี้ สปายไวน์ เป็นต้น
</div>

<!-- ตาราง AUDIT -->
<table>
    <thead>
        <tr>
            <th style="width:36%; text-align:left; padding:5px 6px;">ข้อคำถาม</th>
            <th style="width:11%;">0</th>
            <th style="width:11%;">1</th>
            <th style="width:11%;">2</th>
            <th style="width:11%;">3</th>
            <th style="width:11%;">4</th>
            <th style="width:9%;">คะแนน</th>
        </tr>
    </thead>
    <tbody>';

$row_idx = 0;
foreach ($audit_questions as $qn => $q) {
    $row_idx++;
    $bg         = ($row_idx % 2 === 0) ? ' style="background-color:#f0f7f0;"' : '';
    $saved_score = isset($items[$qn]) ? (int)$items[$qn]['total_score'] : null;

    // กำหนด score columns ตามประเภทคำถาม
    if ($q['type'] === 'q9q10') {
        $cols = [0, 1, 2, 3, 4]; // แสดงครบ 5 column แต่ 1,3 จะว่าง
        $valid_scores = [0, 2, 4];
    } else {
        $cols = [0, 1, 2, 3, 4];
        $valid_scores = [0, 1, 2, 3, 4];
    }

    $html .= '<tr' . $bg . '>
        <td style="padding:4px 6px; font-size:8.5pt;">' . $q['text'] . '</td>';

    foreach ($cols as $col_score) {
        if ($q['type'] === 'q9q10' && !in_array($col_score, [0, 2, 4])) {
            // คอลัมน์ที่ไม่ใช้สำหรับ Q9/Q10 (score 1 และ 3)
            $html .= '<td class="text-center cell-empty" style="font-size:8pt;">—</td>';
        } else {
            $label   = isset($q['options'][$col_score]) ? $q['options'][$col_score] : '';
            $checked = ($saved_score === $col_score);

            if ($checked) {
                // ช่องที่เลือก: ไฮไลต์ background เขียวเข้ม + ข้อความสีขาว
                $html .= '<td class="text-center cell-selected" style="font-size:8pt;">'
                       . '[/]<br><span style="font-size:7pt;">' . $label . '</span>'
                       . '</td>';
            } else {
                // ช่องที่ไม่ได้เลือก: ข้อความสีเทา
                $html .= '<td class="text-center" style="font-size:7.5pt; color:#777;">'
                       . $label
                       . '</td>';
            }
        }
    }

    // คอลัมน์คะแนน
    $html .= '<td class="text-center" style="font-weight:bold; font-size:11pt; color:#2c6e49;">'
           . ($saved_score !== null ? $saved_score : '-')
           . '</td>';

    $html .= '</tr>';
}

$html .= '
    </tbody>
    <tfoot>
        <tr class="tfoot-row">
            <td colspan="6" class="text-right" style="padding:6px; font-weight:bold; background:#e8f5e9;">
                คะแนนรวม AUDIT :
            </td>
            <td class="text-center" style="font-weight:bold; font-size:14pt; color:#2c6e49; background:#e8f5e9;">'
          . $sum .
         '</td>
        </tr>
    </tfoot>
</table>

<!-- การแปลผล -->
<table style="margin-top:8px; border:2px solid ' . $interp_color . ';">
    <tr>
        <td colspan="4" style="background:' . $interp_color . '; color:#fff; padding:5px 8px; font-weight:bold; font-size:9pt; border:none;">
            การแปลผลคะแนน AUDIT  |  ' . $interp_range . ' คะแนน: ' . $interp_th . '  (' . $interp_en . ')
        </td>
    </tr>
    <tr>';

foreach ($interp_levels as $idx => $lv) {
    $is_active = ($idx === $interp_idx);
    $cell_bg     = $is_active ? $lv['color']  : $lv['bg'];
    $cell_color  = $is_active ? '#ffffff'      : $lv['color'];
    $cell_border = $lv['border'];
    $font_size   = $is_active ? '9pt'          : '8pt';
    $html .= '
        <td style="border:2px solid ' . $cell_border . ';
                   background:' . $cell_bg . ';
                   color:' . $cell_color . ';
                   padding:5px 4px;
                   width:25%;
                   text-align:center;
                   font-size:' . $font_size . ';">
            <b>' . $lv['range'] . ' คะแนน</b><br>' . $lv['th'] . '<br><i>' . $lv['en'] . '</i>
        </td>';
}

$html .= '
    </tr>
</table>

<!-- ลายเซ็น -->
<div style="margin-top:20px; text-align:right; font-size:9pt;">
    ลงชื่อ ................................................................ ผู้ประเมิน<br>
    ( ' . htmlspecialchars($row['audit_by'] ?? '') . ' )<br>
    วันที่ ' . thaiDate($row['audit_date']) . '
</div>
';

// ---- สร้าง PDF ด้วย mPDF ----
$mpdf = new \Mpdf\Mpdf([
    'mode'   => 'utf-8',
    'format' => 'A4',
]);
$mpdf->AddPageByArray([
    'margin-left'   => 15,
    'margin-right'  => 15,
    'margin-top'    => 15,
    'margin-bottom' => 15,
]);

$mpdf->WriteHTML($html);
$mpdf->Output('AUDIT_' . $an . '_' . date('Ymd') . '.pdf', 'I');
