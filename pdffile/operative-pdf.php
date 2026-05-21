<?php
require_once '../mains/datethai.php';
require_once '../include/Session.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';

date_default_timezone_set('Asia/Bangkok');

$loginname = isset($_SESSION['loginname']) ? $_SESSION['loginname'] : '';
if (!$loginname) die('Unauthorized');

$conn = DbUtils::get_hosxp_connection();
$an   = trim($_REQUEST['an'] ?? '');
$id   = (int)($_REQUEST['id'] ?? 0);
if (!$an || !$id) die('ไม่พบข้อมูล');

$stmt_m = $conn->prepare("SELECT * FROM prs_operative_note WHERE id = :id AND an = :an AND is_deleted = 0");
$stmt_m->execute(['id'=>$id,'an'=>$an]);
$rec = $stmt_m->fetch(PDO::FETCH_ASSOC);
if (!$rec) die('ไม่พบข้อมูล');

$stmt_i = $conn->prepare("SELECT id, sort_order, image_type, original_name,
                                  annotated_data, image_data
                             FROM prs_operative_note_item
                            WHERE annot_id = :annot_id
                            ORDER BY sort_order ASC");
$stmt_i->execute(['annot_id' => $id]);
$items = $stmt_i->fetchAll(PDO::FETCH_ASSOC);

$stmt_pt = $conn->prepare("SELECT p.hn, p.pname, p.fname, p.lname, p.sex, p.birthday,
                                   i.regdate, i.dchdate, w.name AS ward_name
                              FROM " . DbConstant::HOSXP_DBNAME . ".ipt i
                              LEFT JOIN " . DbConstant::HOSXP_DBNAME . ".patient p ON p.hn = i.hn
                              LEFT JOIN " . DbConstant::HOSXP_DBNAME . ".ward   w ON w.ward = i.ward
                             WHERE i.an = :an");
$stmt_pt->execute(['an' => $an]);
$pt = $stmt_pt->fetch();

function thaiDate($d) {
    if (!$d || $d==='0000-00-00') return '-';
    $m=['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
    $p=explode('-',$d);
    return (count($p)===3) ? ((int)$p[2].' '.$m[(int)$p[1]].' '.((int)$p[0]+543)) : $d;
}

$pt_name   = ($pt['pname']??'').($pt['fname']??'').' '.($pt['lname']??'');
$hn        = $pt['hn'] ?? '';
$ward_name = $pt['ward_name'] ?? '';
$age_y     = $pt['birthday'] ? date_diff(date_create($pt['birthday']), date_create('today'))->y : '-';
$reg_date  = thaiDate($pt['regdate'] ?? '');
$print_dt  = thaiDate(date('Y-m-d')).' '.date('H:i');

$html = '
<style>
  body      { font-family: garuda, sans-serif; font-size: 14px; }
  .hdr      { width:100%; border-collapse:collapse; border-bottom:2px solid #333; margin-bottom:15px; padding-bottom:5px; }
  .hdr td   { padding:2px; vertical-align:top; }
  .pt-name  { font-size:16px; font-weight:bold; }
  .title    { text-align:center; font-size:18px; font-weight:bold; margin-bottom:15px; }
  .sec      { font-size:14px; font-weight:bold; background:#e9ecef; border:1px solid #ccc; padding:4px 8px; margin:10px 0 5px 0; }
  .row-tb   { width:100%; border-collapse:collapse; margin-bottom:5px; }
  .row-tb td { padding:2px 5px; vertical-align:top; }
  .label    { font-weight:bold; color:#444; width:150px; }
  .val      { border-bottom:1px dotted #888; }
  .box      { border:1px solid #ccc; padding:8px; min-height:40px; white-space:pre-wrap; font-size:13px; margin-bottom:10px; }
  .img-wrap { text-align:center; margin-bottom:15px; page-break-inside:avoid; }
  .img-wrap img { max-width:100%; height:auto; }
  .footer   { font-size:11px; color:#666; text-align:right; margin-top:15px; border-top:1px solid #ccc; padding-top:5px; }
</style>

<table class="hdr">
  <tr>
    <td width="70%">
      <div class="pt-name">' . htmlspecialchars($pt_name) . '</div>
      HN: <b>' . htmlspecialchars($hn) . '</b> &nbsp;&nbsp; AN: <b>' . htmlspecialchars($an) . '</b> &nbsp;&nbsp; อายุ: ' . $age_y . ' ปี<br>
      Ward: ' . htmlspecialchars($ward_name) . ' &nbsp; วันที่ Admit: ' . $reg_date . '
    </td>
  </tr>
</table>

<div class="title">OPERATIVE NOTE</div>

<table class="row-tb">
  <tr>
    <td class="label" style="width:120px">Date of operation:</td><td class="val">' . thaiDate($rec['operation_date']) . '</td>
    <td class="label" style="width:100px">Time started:</td><td class="val">' . substr($rec['time_started']??'',0,5) . '</td>
    <td class="label" style="width:100px">Time ended:</td><td class="val">' . substr($rec['time_ended']??'',0,5) . '</td>
  </tr>
</table>
<table class="row-tb">
  <tr><td class="label">Surgeon:</td><td class="val">' . htmlspecialchars($rec['surgeon']??'') . '</td></tr>
  <tr><td class="label">First assistant:</td><td class="val">' . htmlspecialchars($rec['first_assistant']??'') . '</td></tr>
  <tr><td class="label">Second assistant:</td><td class="val">' . htmlspecialchars($rec['second_assistant']??'') . '</td></tr>
  <tr><td class="label">Surgical nurse:</td><td class="val">' . htmlspecialchars($rec['surgical_nurse']??'') . '</td></tr>
</table>

<div class="sec">Diagnosis & Operation</div>
<table class="row-tb">
  <tr><td class="label">Clinical diagnosis:</td><td class="val">' . htmlspecialchars($rec['clinical_diagnosis']??'') . '</td></tr>
  <tr><td class="label">Post op diagnosis:</td><td class="val">' . htmlspecialchars($rec['post_op_diagnosis']??'') . '</td></tr>
  <tr><td class="label">Operation:</td><td class="val">' . htmlspecialchars($rec['operation_name']??'') . '</td></tr>
</table>
<table class="row-tb">
  <tr>
    <td class="label">Anesthetic techniques:</td><td class="val">' . htmlspecialchars($rec['anesthetic_technique']??'') . '</td>
    <td class="label" style="width:120px">Anesthesiologist:</td><td class="val">' . htmlspecialchars($rec['anesthesiologist']??'') . '</td>
  </tr>
</table>

<div class="sec">Descriptive of Operation</div>
<table class="row-tb">
  <tr><td class="label">Position:</td><td class="val">' . htmlspecialchars($rec['op_position']??'') . '</td></tr>
  <tr><td class="label">Incision:</td><td class="val">' . htmlspecialchars($rec['incision']??'') . '</td></tr>
</table>
<div style="font-weight:bold; margin-top:5px;">Finding:</div>
<div class="box">' . htmlspecialchars($rec['finding']??'') . '</div>
<div style="font-weight:bold; margin-top:5px;">Procedure:</div>
<div class="box">' . htmlspecialchars($rec['procedure_detail']??'') . '</div>

<table class="row-tb" style="margin-top:10px;">
  <tr>
    <td class="label">Estimate blood loss:</td><td class="val">' . htmlspecialchars($rec['estimate_blood_loss']??'') . ' ml</td>
    <td class="label" style="width:100px">Urine output:</td><td class="val">' . htmlspecialchars($rec['urine_output']??'') . ' ml</td>
  </tr>
</table>
<table class="row-tb">
  <tr>
    <td class="label">ส่ง Patho:</td><td class="val">' . htmlspecialchars($rec['patho_status']??'') . '</td>
    <td class="label" style="width:100px">Wound type:</td><td class="val">' . htmlspecialchars($rec['wound_type']??'') . '</td>
  </tr>
</table>

<div style="text-align:right; margin-top:30px; margin-right:30px;">
  (ลงชื่อ)........................................................................<br>
  (' . htmlspecialchars($rec['surgeon']??'') . ')
</div>
';

$combined_bin = $rec['combined_data'] ?? null;
if (is_resource($combined_bin)) $combined_bin = stream_get_contents($combined_bin);

if ($combined_bin && strlen($combined_bin) > 0) {
    $combined_b64 = 'data:image/png;base64,' . base64_encode($combined_bin);
    $html .= '<div style="page-break-before:always;"></div>';
    $html .= '<div class="title">ภาพประกอบการผ่าตัด</div>';
    $html .= '
    <div class="img-wrap">
      <img src="' . $combined_b64 . '" style="max-width:100%; height:auto;" />
    </div>';
} else {
    $MAX_W = 1200; $GAP = 8; $COLS = 2;
    $gd_images = [];
    foreach ($items as $item) {
        $bin = $item['annotated_data'];
        if (is_resource($bin)) $bin = stream_get_contents($bin);
        if (!$bin) {
            $bin = $item['image_data'];
            if (is_resource($bin)) $bin = stream_get_contents($bin);
        }
        if (!$bin) continue;
        $gd_images[] = ['bin' => $bin, 'name' => ($item['original_name'] ?? '')];
    }
    
    $useGD = function_exists('imagecreatefromstring') && !empty($gd_images);
    if ($useGD) {
        $count = count($gd_images);
        if ($count === 1) $COLS = 1;

        $col_w = (int)(($MAX_W - ($COLS - 1) * $GAP) / $COLS);
        $cells = [];

        for ($i = 0; $i < $count; $i++) {
            $img_gd = @imagecreatefromstring($gd_images[$i]['bin']);
            if (!$img_gd) continue;
            $orig_w = imagesx($img_gd); $orig_h = imagesy($img_gd);
            $scale  = min(1, $col_w / $orig_w);
            $new_w  = max(1, (int)($orig_w * $scale)); $new_h  = max(1, (int)($orig_h * $scale));

            $scaled = imagecreatetruecolor($new_w, $new_h);
            $white  = imagecolorallocate($scaled, 255, 255, 255);
            imagefill($scaled, 0, 0, $white);
            imagecopyresampled($scaled, $img_gd, 0, 0, 0, 0, $new_w, $new_h, $orig_w, $orig_h);
            imagedestroy($img_gd);

            $row = (int)floor($i / $COLS); $col = $i % $COLS;
            $cells[$row][$col] = ['img' => $scaled, 'w' => $new_w, 'h' => $new_h, 'name' => $gd_images[$i]['name']];
        }

        $row_heights = []; $total_h = 0;
        foreach ($cells as $r => $cols_arr) {
            $rh = 0; foreach ($cols_arr as $cell) $rh = max($rh, $cell['h']);
            $row_heights[$r] = $rh; $total_h += $rh + ($r > 0 ? $GAP : 0);
        }
        $canvas_w = $COLS === 1 ? ($cells[0][0]['w'] ?? $MAX_W) : ($col_w * $COLS + $GAP * ($COLS - 1));

        $combined = imagecreatetruecolor(max(1, $canvas_w), max(1, $total_h));
        $white = imagecolorallocate($combined, 255, 255, 255);
        imagefill($combined, 0, 0, $white);

        $y = 0;
        foreach ($cells as $r => $cols_arr) {
            foreach ($cols_arr as $c => $cell) {
                $x = $c * ($col_w + $GAP);
                imagecopy($combined, $cell['img'], $x, $y, 0, 0, $cell['w'], $cell['h']);
                imagedestroy($cell['img']);
            }
            $y += $row_heights[$r] + $GAP;
        }

        ob_start(); imagepng($combined); $combined_png = ob_get_clean(); imagedestroy($combined);
        $combined_b64 = 'data:image/png;base64,' . base64_encode($combined_png);
        $html .= '<div style="page-break-before:always;"></div><div class="title">ภาพประกอบการผ่าตัด</div>';
        $html .= '<div class="img-wrap"><img src="' . $combined_b64 . '" style="max-width:100%; height:auto;" /></div>';
    } else {
        $first = true;
        foreach ($gd_images as $idx => $imgData) {
            if ($first) { $html .= '<div style="page-break-before:always;"></div><div class="title">ภาพประกอบการผ่าตัด</div>'; $first = false; }
            $final_b64 = 'data:image/png;base64,' . base64_encode($imgData['bin']);
            $html .= '<div class="img-wrap"><img src="' . $final_b64 . '" style="max-width:100%; height:auto;" /></div>';
        }
    }
}

$html .= '<div class="footer">พิมพ์โดย: ' . htmlspecialchars($loginname) . ' | วันเวลาพิมพ์: ' . $print_dt . '</div>';

$mpdf = new \Mpdf\Mpdf(['mode'=>'utf-8','format'=>'A4']);
$mpdf->AddPageByArray(['margin-left'=>15,'margin-right'=>15,'margin-top'=>15,'margin-bottom'=>15]);
$mpdf->WriteHTML($html);
$mpdf->Output('OPERATIVE_NOTE_' . $an . '_' . $id . '_' . date('Ymd') . '.pdf', 'I');
