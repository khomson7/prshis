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

function formatSurgeon($jsonStr) {
    if (empty($jsonStr)) return '';
    $arr = json_decode($jsonStr, true);
    if (is_array($arr)) {
        return implode(', ', $arr);
    }
    return $jsonStr;
}

$pt_name   = ($pt['pname']??'').($pt['fname']??'').' '.($pt['lname']??'');
$hn        = $pt['hn'] ?? '';
$ward_name = $pt['ward_name'] ?? '';
$age_y     = $pt['birthday'] ? date_diff(date_create($pt['birthday']), date_create('today'))->y : '-';
$reg_date  = thaiDate($pt['regdate'] ?? '');
$print_dt  = thaiDate(date('Y-m-d')).' '.date('H:i');
$surgeon_display = formatSurgeon($rec['surgeon'] ?? '');

// Image generation block first
$combined_b64 = '';
$combined_bin = $rec['combined_data'] ?? null;
if (is_resource($combined_bin)) $combined_bin = stream_get_contents($combined_bin);

if ($combined_bin && strlen($combined_bin) > 0) {
    $combined_b64 = 'data:image/png;base64,' . base64_encode($combined_bin);
} else {
    // Generate combined image if not available
    $MAX_W = 500; $GAP = 8; $COLS = 1;
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

        if ($total_h > 0 && $canvas_w > 0) {
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
        }
    } elseif (!empty($gd_images)) {
        // Fallback to first image
        $combined_b64 = 'data:image/png;base64,' . base64_encode($gd_images[0]['bin']);
    }
}

$img_html = '';
if ($combined_b64) {
    // Explicit width inside fixed table layout helps mPDF scaling
    $img_html = '<div style="width: 100%; overflow: hidden;"><img src="' . $combined_b64 . '" style="width:100%; max-height:280px;" /></div>';
}

$chk_patho_y = (strpos(strtolower($rec['patho_status']??''), 'ส่ง')!==false && strpos(strtolower($rec['patho_status']??''), 'ไม่')===false) ? '☑' : '☐';
$chk_patho_n = (strpos(strtolower($rec['patho_status']??''), 'ไม่')!==false) ? '☑' : '☐';

$chk_w_clean = ($rec['wound_type']=='Clean wound') ? '☑' : '☐';
$chk_w_contam = ($rec['wound_type']=='Contaminate wound') ? '☑' : '☐';
$chk_w_clean_contam = ($rec['wound_type']=='Clean contaminate wound') ? '☑' : '☐';
$chk_w_dirty = ($rec['wound_type']=='Dirty wound') ? '☑' : '☐';

$html = '
<style>
  body { font-family: garuda, sans-serif; font-size: 13px; }
  .doc-header { width: 100%; margin-bottom: 2px; border-collapse: collapse; }
  .doc-title { text-align: center; font-size: 16px; font-weight: bold; }
  .doc-form-no { text-align: right; font-size: 13px; font-weight: bold; }
  .main-box { border: 1.5px solid #000; padding: 6px; width: 100%; box-sizing: border-box; }
  .row-tb { width: 100%; border-collapse: collapse; margin-bottom: 3px; }
  .row-tb td { padding: 1px 2px; vertical-align: top; }
  .lbl { font-size: 13px; white-space: nowrap; }
  .val { border-bottom: 1px dotted #000; font-weight: bold; font-size: 13px; }
  
  .desc-title { text-align: center; font-size: 13px; font-weight: bold; text-decoration: underline; margin: 8px 0 5px 0; }
  
  .desc-layout { width: 100%; border-collapse: collapse; margin-bottom: 5px; table-layout: fixed; }
  .desc-left { width: 65%; vertical-align: top; padding-right: 5px; font-size: 13px; overflow-wrap: break-word; }
  .desc-right { width: 35%; vertical-align: top; text-align: center; overflow: hidden; }
  
  .desc-lbl { margin-top: 4px; }
  
  .chk-tb { width: 100%; margin: 8px 0; font-size: 13px; border-collapse: collapse; }
  .chk-tb td { vertical-align: top; }
  .chk-box { font-family: "DejaVu Sans", sans-serif; font-size: 16px; margin-right: 3px; }
  
  .footer-tb { width: 100%; border-collapse: collapse; border: 1.5px solid #000; margin-top: 10px; }
  .footer-tb td { border: 1.5px solid #000; padding: 4px; vertical-align: top; font-size: 13px; }
  .doc-footer { text-align: right; font-size: 11px; margin-top: 2px; font-weight: bold; }
</style>

<table class="doc-header">
  <tr>
    <td width="20%"></td>
    <td width="60%" class="doc-title">แบบบันทึกการผ่าตัด</td>
    <td width="20%" class="doc-form-no">แบบ ร.บ.02 ต.05</td>
  </tr>
</table>

<div class="main-box">
  <table class="row-tb">
    <tr>
      <td class="lbl" style="width:120px">Date of operation</td><td class="val" style="width:30%">' . thaiDate($rec['operation_date']) . '</td>
      <td class="lbl" style="width:80px">Time started</td><td class="val" style="width:20%">' . substr($rec['time_started']??'',0,5) . '</td>
      <td class="lbl" style="width:60px">Time end</td><td class="val">' . substr($rec['time_ended']??'',0,5) . '</td>
    </tr>
  </table>
  <table class="row-tb">
    <tr>
      <td class="lbl" style="width:60px">Surgeon</td><td class="val" style="width:40%">' . htmlspecialchars($surgeon_display) . '</td>
      <td class="lbl" style="width:90px">First assistant</td><td class="val">' . htmlspecialchars($rec['first_assistant']??'') . '</td>
    </tr>
    <tr>
      <td class="lbl" style="width:110px">Second assistant</td><td class="val" style="width:35%">' . htmlspecialchars($rec['second_assistant']??'') . '</td>
      <td class="lbl" style="width:90px">Surgical nurse</td><td class="val">' . htmlspecialchars($rec['surgical_nurse']??'') . '</td>
    </tr>
  </table>
  <table class="row-tb">
    <tr><td class="lbl" style="width:110px">Clinical diagnosis</td><td class="val">' . htmlspecialchars($rec['clinical_diagnosis']??'') . '</td></tr>
    <tr><td class="lbl" style="width:160px">Post operation diagnosis</td><td class="val">' . htmlspecialchars($rec['post_op_diagnosis']??'') . '</td></tr>
    <tr><td class="lbl" style="width:70px">Operation</td><td class="val">' . htmlspecialchars($rec['operation_name']??'') . '</td></tr>
  </table>
  <table class="row-tb">
    <tr>
      <td class="lbl" style="width:150px">Anesthetic techniques</td><td class="val" style="width:30%">' . htmlspecialchars($rec['anesthetic_technique']??'') . '</td>
      <td class="lbl" style="width:110px">Anesthesiologist</td><td class="val">' . htmlspecialchars($rec['anesthesiologist']??'') . '</td>
    </tr>
  </table>

  <div class="desc-title">DESCRIPTIVE OF OPERATION</div>
  
  <table class="desc-layout">
    <tr>
      <td class="desc-left">
        <div><span class="desc-lbl">Position:</span> <b>' . htmlspecialchars($rec['op_position']??'') . '</b></div>
        <div style="margin-top:10px"><span class="desc-lbl">Incision:</span> <b>' . htmlspecialchars($rec['incision']??'') . '</b></div>
        <div style="margin-top:10px"><span class="desc-lbl">Finding:</span> <b>' . nl2br(htmlspecialchars($rec['finding']??'')) . '</b></div>
        <div style="margin-top:10px"><span class="desc-lbl">Procedure:</span><br><br><b>' . nl2br(htmlspecialchars($rec['procedure_detail']??'')) . '</b></div>
      </td>
      <td class="desc-right">
        ' . $img_html . '
      </td>
    </tr>
  </table>

  <table class="row-tb" style="margin-top:20px; width: 70%;">
    <tr>
      <td class="lbl" style="width:130px">Estimate blood loss</td><td class="val">' . htmlspecialchars($rec['estimate_blood_loss']??'') . '</td><td class="lbl" style="width:30px">ml</td>
      <td class="lbl" style="width:80px">Urine output</td><td class="val">' . htmlspecialchars($rec['urine_output']??'') . '</td><td class="lbl" style="width:30px">ml</td>
    </tr>
  </table>

  <table class="chk-tb">
    <tr>
      <td width="20%">
        <span class="chk-box">' . $chk_patho_y . '</span> ส่ง Patho
      </td>
      <td width="25%">
        <span class="chk-box">' . $chk_patho_n . '</span> ไม่ส่ง Patho
      </td>
      <td width="25%">
        <div style="margin-bottom:8px"><span class="chk-box">' . $chk_w_clean . '</span> Clean wound</div>
        <div><span class="chk-box">' . $chk_w_contam . '</span> Contaminate wound</div>
      </td>
      <td width="30%">
        <div style="margin-bottom:8px"><span class="chk-box">' . $chk_w_clean_contam . '</span> Clean contaminate wound</div>
        <div><span class="chk-box">' . $chk_w_dirty . '</span> Dirty wound</div>
      </td>
    </tr>
  </table>

  <table class="footer-tb">
    <tr>
      <td width="40%">Name <br><br><b>' . htmlspecialchars($pt_name) . '</b></td>
      <td width="20%">Age <br><br><b>' . $age_y . ' ปี</b></td>
      <td width="40%">Hospital number <br><br><b>' . htmlspecialchars($hn) . '</b></td>
    </tr>
    <tr>
      <td>Department <br><br><b>ศัลยกรรม</b></td>
      <td>Ward <br><br><b>' . htmlspecialchars($ward_name) . '</b></td>
      <td>Signature <br><br><b>' . htmlspecialchars($surgeon_display) . '</b></td>
    </tr>
  </table>

</div>
<div class="doc-footer">FM-OPR-01 แก้ไขครั้งที่ 01 วันที่ 7 สิงหาคม 2548</div>
';

$mpdf = new \Mpdf\Mpdf(['mode'=>'utf-8','format'=>'A4']);
$mpdf->shrink_tables_to_fit = 0;
$mpdf->AddPageByArray(['margin-left'=>15,'margin-right'=>15,'margin-top'=>15,'margin-bottom'=>15]);
$mpdf->WriteHTML($html);
$mpdf->Output('OPERATIVE_NOTE_' . $an . '_' . $id . '_' . date('Ymd') . '.pdf', 'I');
