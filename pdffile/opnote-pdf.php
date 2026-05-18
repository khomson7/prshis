<?php
/**
 * opnote-pdf.php
 * พิมพ์ภาพ Annotation ทั้งหมดของ AN เป็น PDF A4
 */
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

// ---- Master ----
$stmt_m = $conn->prepare("SELECT * FROM prs_opnote WHERE id = :id AND an = :an AND is_deleted = 0");
$stmt_m->execute(['id'=>$id,'an'=>$an]);
$rec = $stmt_m->fetch(PDO::FETCH_ASSOC);
if (!$rec) die('ไม่พบข้อมูล');

// ---- Items (fallback only) ----
$stmt_i = $conn->prepare("SELECT id, sort_order, image_type, original_name,
                                  annotated_data, image_data
                             FROM prs_opnote_item
                            WHERE annot_id = :annot_id
                            ORDER BY sort_order ASC");
$stmt_i->execute(['annot_id' => $id]);
$items = $stmt_i->fetchAll(PDO::FETCH_ASSOC);

// ---- ข้อมูลผู้ป่วย ----
$stmt_pt = $conn->prepare("SELECT p.hn, p.pname, p.fname, p.lname,
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
$reg_date  = thaiDate($pt['regdate'] ?? '');
$dch_date  = thaiDate($pt['dchdate'] ?? '');
$print_dt  = thaiDate(date('Y-m-d')).' '.date('H:i');
$rec_dt    = thaiDate(substr($rec['created_at'],0,10)).' '.substr($rec['created_at'],11,5);

// ---- สร้าง HTML ----
$html = '
<style>
  body      { font-family: garuda, sans-serif; font-size: 12px; }
  .hdr      { width:100%; border-collapse:collapse; border-bottom:2px solid #1a6b3a;
               margin-bottom:8px; padding-bottom:4px; }
  .hdr td   { padding:2px 4px; vertical-align:top; font-size:12px; }
  .pt-name  { font-size:15px; font-weight:bold; }
  .sec      { font-size:12px; font-weight:bold; background:#1a6b3a; color:#fff;
               padding:3px 8px; margin:8px 0 6px 0; }
  .img-wrap { text-align:center; margin-bottom:12px; page-break-inside:avoid; }
  .img-wrap img { max-width:100%; height:auto; }
  .img-num  { font-size:11px; color:#555; margin-bottom:3px; }
  .note-box { border:1px solid #dee2e6; padding:8px; min-height:30px;
               font-size:12px; white-space:pre-wrap; background:#fafafa; }
  .footer   { font-size:10px; color:#888; text-align:right; margin-top:8px;
               border-top:1px solid #ccc; padding-top:3px; }
</style>

<table class="hdr">
  <tr>
    <td width="70%">
      <div class="pt-name">' . htmlspecialchars($pt_name) . '</div>
      HN: <b>' . htmlspecialchars($hn) . '</b> &nbsp;&nbsp; AN: <b>' . htmlspecialchars($an) . '</b><br>
      Ward: ' . htmlspecialchars($ward_name) . ' &nbsp; วันรับ Admit: ' . $reg_date . ' &nbsp; วันจำหน่าย: ' . $dch_date . '
    </td>
   <!-- <td width="30%" style="text-align:right">
      พิมพ์: ' . $print_dt . '<br>
      บันทึกโดย: ' . htmlspecialchars($rec['created_by']) . '<br>
      วันที่บันทึก: ' . $rec_dt . '
    </td> -->
  </tr>
</table>
';

// ---- แสดงภาพรวม ----
// ลำดับความสำคัญ: combined_data (จาก client JS) → GD stitch จาก items
$combined_bin = $rec['combined_data'] ?? null;
if (is_resource($combined_bin)) $combined_bin = stream_get_contents($combined_bin);

if ($combined_bin && strlen($combined_bin) > 0) {
    // ใช้ combined_data ที่ client ส่งมา (แม่นยำ 100%)
    $combined_b64 = 'data:image/png;base64,' . base64_encode($combined_bin);
    $html .= '
    <div class="img-wrap">
      <img src="' . $combined_b64 . '" style="max-width:100%; height:auto;" />
    </div>';

} else {
    // Fallback: GD stitch จาก items (กรณีเก่าที่ไม่มี combined_data)
    $MAX_W = 1200;
    $GAP   = 8;
    $COLS  = 2;

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
            $orig_w = imagesx($img_gd);
            $orig_h = imagesy($img_gd);
            $scale  = min(1, $col_w / $orig_w);
            $new_w  = max(1, (int)($orig_w * $scale));
            $new_h  = max(1, (int)($orig_h * $scale));

            $scaled = imagecreatetruecolor($new_w, $new_h);
            $white  = imagecolorallocate($scaled, 255, 255, 255);
            imagefill($scaled, 0, 0, $white);
            imagecopyresampled($scaled, $img_gd, 0, 0, 0, 0, $new_w, $new_h, $orig_w, $orig_h);
            imagedestroy($img_gd);

            $row = (int)floor($i / $COLS);
            $col = $i % $COLS;
            $cells[$row][$col] = ['img' => $scaled, 'w' => $new_w, 'h' => $new_h,
                                   'name' => $gd_images[$i]['name']];
        }

        $row_heights = [];
        $total_h = 0;
        foreach ($cells as $r => $cols_arr) {
            $rh = 0;
            foreach ($cols_arr as $cell) $rh = max($rh, $cell['h']);
            $row_heights[$r] = $rh;
            $total_h += $rh + ($r > 0 ? $GAP : 0);
        }
        $canvas_w = $COLS === 1 ? ($cells[0][0]['w'] ?? $MAX_W)
                                 : ($col_w * $COLS + $GAP * ($COLS - 1));

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

        ob_start();
        imagepng($combined);
        $combined_png = ob_get_clean();
        imagedestroy($combined);

        $combined_b64 = 'data:image/png;base64,' . base64_encode($combined_png);
        $html .= '
    <div class="img-wrap">
      <img src="' . $combined_b64 . '" style="max-width:100%; height:auto;" />
    </div>';

    } else {
        // Fallback สุดท้าย: แสดงภาพแยกกัน (ถ้าไม่มี GD)
        foreach ($gd_images as $idx => $imgData) {
            $final_b64 = 'data:image/png;base64,' . base64_encode($imgData['bin']);
            $name = htmlspecialchars($imgData['name']);
            $html .= '
    <div class="img-wrap">
      <div class="img-num">ภาพที่ ' . ($idx + 1) . ($name ? ' — ' . $name : '') . '</div>
      <img src="' . $final_b64 . '" style="max-width:100%; height:auto;" />
    </div>';
        }
    }
}

// ---- Note ----
if ($rec['note']) {
    $html .= '
    <div class="sec">บันทึกเพิ่มเติม</div>
    <div class="note-box">' . nl2br(htmlspecialchars($rec['note'])) . '</div>';
}

$html .= '<div class="footer">AN: ' . htmlspecialchars($an) . ' | พิมพ์: ' . $print_dt . '</div>';

// ---- mPDF ----
$mpdf = new \Mpdf\Mpdf(['mode'=>'utf-8','format'=>'A4']);
$mpdf->AddPageByArray(['margin-left'=>15,'margin-right'=>15,'margin-top'=>15,'margin-bottom'=>15]);
$mpdf->WriteHTML($html);
$mpdf->Output('IMG_ANNOT_' . $an . '_' . $id . '_' . date('Ymd') . '.pdf', 'I');
