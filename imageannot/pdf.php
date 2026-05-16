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

// ---- ดึงข้อมูล ----
$stmt = $conn->prepare("SELECT title, doc_group, image_type, annotated_image, image_data,
                                form_data, form_note, created_by, created_at
                           FROM prs_image_annot
                          WHERE id = :id AND an = :an AND is_deleted = 0");
$stmt->execute(['id' => $id, 'an' => $an]);
$rec = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$rec) die('ไม่พบข้อมูล');

$img_bin = $rec['annotated_image'] ?: $rec['image_data'];
if (is_resource($img_bin)) $img_bin = stream_get_contents($img_bin);
if (!$img_bin) die('ไม่พบข้อมูลภาพ');

$img_src = 'data:' . ($rec['image_type'] ?: 'image/png') . ';base64,' . base64_encode($img_bin);

$form_data = $rec['form_data'] ? (json_decode($rec['form_data'], true) ?: []) : [];
$form_note = $rec['form_note'] ?? '';

// ---- ดึงข้อมูลผู้ป่วย ----
$stmt_pt = $conn->prepare("SELECT p.hn, p.pname, p.fname, p.lname,
                                   i.regdate, i.dchdate, w.name AS ward_name
                              FROM " . DbConstant::HOSXP_DBNAME . ".ipt i
                              LEFT JOIN " . DbConstant::HOSXP_DBNAME . ".patient p ON p.hn = i.hn
                              LEFT JOIN " . DbConstant::HOSXP_DBNAME . ".ward   w ON w.ward = i.ward
                             WHERE i.an = :an");
$stmt_pt->execute(['an' => $an]);
$pt = $stmt_pt->fetch();

function thaiDate($d) {
    if (!$d || $d === '0000-00-00') return '-';
    $m = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
    $p = explode('-', $d);
    return (count($p)===3) ? ((int)$p[2].' '.$m[(int)$p[1]].' '.((int)$p[0]+543)) : $d;
}

function chk($arr, $key) {
    return (!empty($arr[$key])) ? '<b style="color:#2c6e49">&#10003;</b>' : '<span style="color:#ccc">&#9633;</span>';
}

$pt_name   = ($pt['pname']??'').($pt['fname']??'').' '.($pt['lname']??'');
$ward_name = $pt['ward_name'] ?? '';
$hn        = $pt['hn'] ?? '';
$reg_date  = thaiDate($pt['regdate'] ?? '');
$dch_date  = thaiDate($pt['dchdate'] ?? '');
$print_dt  = thaiDate(date('Y-m-d')).' '.date('H:i');
$rec_dt    = thaiDate(substr($rec['created_at'],0,10)).' '.substr($rec['created_at'],11,5);

// ---- HTML ----
$html = '
<style>
  body        { font-family: garuda, sans-serif; font-size: 12px; }
  .hdr-tbl    { width:100%; border-collapse:collapse; margin-bottom:8px;
                border-bottom:2px solid #2c6e49; padding-bottom:6px; }
  .hdr-tbl td { padding:2px 4px; vertical-align:top; }
  .pt-name    { font-size:15px; font-weight:bold; color:#1a1a1a; }
  .sec-title  { font-size:12px; font-weight:bold; background:#2c6e49; color:#fff;
                padding:3px 8px; margin:8px 0 5px 0; }
  .data-tbl   { width:100%; border-collapse:collapse; margin-bottom:4px; }
  .data-tbl td{ padding:3px 6px; font-size:12px; border:1px solid #dee2e6; vertical-align:top; }
  .data-tbl .label { background:#f8f9fa; font-weight:bold; width:30%; }
  .chk-tbl    { width:100%; border-collapse:collapse; }
  .chk-tbl td { padding:3px 8px; font-size:12px; width:25%; }
  .img-wrap   { text-align:center; margin:8px 0; }
  .img-wrap img { max-width:100%; height:auto; }
  .note-box   { border:1px solid #dee2e6; border-radius:4px; padding:8px;
                min-height:40px; font-size:12px; white-space:pre-wrap; background:#fafafa; }
  .footer     { font-size:10px; color:#888; text-align:right; margin-top:10px;
                border-top:1px solid #ccc; padding-top:3px; }
</style>

<!-- Header ผู้ป่วย -->
<table class="hdr-tbl">
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

<!-- ชื่อเอกสาร -->
<div class="sec-title">'
    . htmlspecialchars($rec['title'])
    . ($rec['doc_group'] ? ' &nbsp;<span style="font-size:11px;font-weight:normal;">['.htmlspecialchars($rec['doc_group']).']</span>' : '')
. '</div>

<!-- ประเภทและลักษณะ -->
<table class="data-tbl">
  <tr>
    <td class="label">ประเภทการบันทึก</td>
    <td>
      ' . chk($form_data,'type_wound').' แผล/บาดเจ็บ &nbsp;
      ' . chk($form_data,'type_skin') .' ผิวหนัง &nbsp;
      ' . chk($form_data,'type_xray') .' X-Ray &nbsp;
      ' . chk($form_data,'type_lab')  .' ผลแลป &nbsp;
      ' . chk($form_data,'type_equipment').' อุปกรณ์ &nbsp;
      ' . chk($form_data,'type_other').' อื่นๆ
    </td>
  </tr>
  <tr>
    <td class="label">ลักษณะ</td>
    <td>
      ' . chk($form_data,'char_clean')    .' แผลสะอาด &nbsp;
      ' . chk($form_data,'char_infected') .' ติดเชื้อ &nbsp;
      ' . chk($form_data,'char_swelling') .' บวม &nbsp;
      ' . chk($form_data,'char_redness')  .' แดง &nbsp;
      ' . chk($form_data,'char_discharge').' มีหนอง &nbsp;
      ' . chk($form_data,'char_dry')      .' แห้งดี &nbsp;
      ' . chk($form_data,'char_healing')  .' หายดี &nbsp;
      ' . chk($form_data,'char_necrosis') .' Necrosis
    </td>
  </tr>
  <tr>
    <td class="label">ขนาด / ตำแหน่ง</td>
    <td>
      กว้าง ' . htmlspecialchars($form_data['wound_w'] ?? '-') . ' cm &nbsp;
      ยาว '  . htmlspecialchars($form_data['wound_l'] ?? '-') . ' cm &nbsp;|&nbsp;
      ตำแหน่ง: ' . htmlspecialchars($form_data['wound_loc'] ?? '-') . '
    </td>
  </tr>
  <tr>
    <td class="label">การรักษา</td>
    <td>
      ' . chk($form_data,'tx_clean')   .' ทำแผล &nbsp;
      ' . chk($form_data,'tx_stitch')  .' เย็บแผล &nbsp;
      ' . chk($form_data,'tx_drain')   .' ใส่ Drain &nbsp;
      ' . chk($form_data,'tx_dressing').' เปลี่ยน Dressing &nbsp;
      ' . chk($form_data,'tx_remove')  .' ตัดไหม &nbsp;
      ' . chk($form_data,'tx_photo')   .' ถ่ายภาพติดตาม
    </td>
  </tr>
</table>

<!-- บันทึกเพิ่มเติม -->
' . ($form_note ? '
<div class="sec-title">บันทึกเพิ่มเติม</div>
<div class="note-box">' . nl2br(htmlspecialchars($form_note)) . '</div>
' : '') . '

<!-- ภาพ -->
<div class="sec-title">ภาพ</div>
<div class="img-wrap">
  <img src="' . $img_src . '" />
</div>

<div class="footer">AN: ' . htmlspecialchars($an) . ' | พิมพ์: ' . $print_dt . '</div>
';

// ---- mPDF ----
$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
$mpdf->AddPageByArray([
    'margin-left'=>15,'margin-right'=>15,'margin-top'=>15,'margin-bottom'=>15,
]);
$mpdf->WriteHTML($html);
$mpdf->Output('IMG_ANNOT_'.$an.'_'.$id.'_'.date('Ymd').'.pdf', 'I');
