<?php
/**
 * image-annot-pdf.php
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
$stmt_m = $conn->prepare("SELECT * FROM prs_image_annot WHERE id = :id AND an = :an AND is_deleted = 0");
$stmt_m->execute(['id'=>$id,'an'=>$an]);
$rec = $stmt_m->fetch(PDO::FETCH_ASSOC);
if (!$rec) die('ไม่พบข้อมูล');

// ---- Items ----
$stmt_i = $conn->prepare("SELECT id, sort_order, image_type, original_name,
                                  annotated_data, image_data
                             FROM prs_image_annot_item
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
      Ward: ' . htmlspecialchars($ward_name) . ' &nbsp; วันรับ: ' . $reg_date . ' &nbsp; วันจำหน่าย: ' . $dch_date . '
    </td>
    <td width="30%" style="text-align:right">
      พิมพ์: ' . $print_dt . '<br>
      บันทึกโดย: ' . htmlspecialchars($rec['created_by']) . '<br>
      วันที่บันทึก: ' . $rec_dt . '
    </td>
  </tr>
</table>
';

// ---- วนภาพแต่ละรูป ----
// ใช้ annotated_data (PNG แบน image+annotation จาก canvas.toDataURL) ถ้ามี
// ถ้าไม่มี (ไม่ได้วาด) ใช้ image_data ต้นฉบับ
foreach ($items as $idx => $item) {
    // ให้ความสำคัญ annotated_data (มีเส้นวาดรวมแล้ว) ก่อน
    $bin = $item['annotated_data'];
    if (is_resource($bin)) $bin = stream_get_contents($bin);
    if (!$bin) {
        $bin = $item['image_data'];
        if (is_resource($bin)) $bin = stream_get_contents($bin);
    }
    if (!$bin) continue;

    $mime      = $item['image_type'] ?: 'image/png';
    $final_b64 = 'data:image/png;base64,' . base64_encode($bin);

    $name = htmlspecialchars($item['original_name'] ?? '');
    $html .= '
    <div class="img-wrap">
      <div class="img-num">ภาพที่ ' . ($idx + 1) . ($name ? ' — ' . $name : '') . '</div>
      <img src="' . $final_b64 . '" />
    </div>';
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
