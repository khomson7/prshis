<?php
/**
 * ca-breast-pdf.php
 * พิมพ์ CA Breast Paclitaxel Regimen (AC-4T) — CHEMOTHERAPY ORDER SHEET
 * Layout 3 คอลัมน์: Progress Note | Order for Chemotherapy | Order for Continuation
 */
require_once '../mains/datethai.php';
require_once '../include/Session.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';

date_default_timezone_set('Asia/Bangkok');

$loginname = isset($_SESSION['loginname']) ? $_SESSION['loginname'] : '';
if (!$loginname)
  die('Unauthorized');

$conn = DbUtils::get_hosxp_connection();
$an = isset($_REQUEST['an']) ? trim($_REQUEST['an']) : '';
$id = (int) (isset($_REQUEST['id']) ? $_REQUEST['id'] : 0);
if (!$an || !$id)
  die('ไม่พบข้อมูล');

$stmt = $conn->prepare("SELECT * FROM prs_ca_breast WHERE id=:id AND an=:an AND is_deleted=0");
$stmt->execute(['id' => $id, 'an' => $an]);
$rec = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$rec)
  die('ไม่พบข้อมูล');

// ข้อมูลผู้ป่วย
$stmt_pt = $conn->prepare(
  "SELECT p.hn, p.pname, p.fname, p.lname, p.birthday,
            i.regdate, i.dchdate, i.ward, w.name AS ward_name
       FROM " . DbConstant::HOSXP_DBNAME . ".ipt i
       LEFT JOIN " . DbConstant::HOSXP_DBNAME . ".patient p ON p.hn = i.hn
       LEFT JOIN " . DbConstant::HOSXP_DBNAME . ".ward   w ON w.ward = i.ward
      WHERE i.an = :an"
);
$stmt_pt->execute(['an' => $an]);
$pt = $stmt_pt->fetch(PDO::FETCH_ASSOC);

// ---- Helpers ----
function thDate($d)
{
  if (!$d || $d === '0000-00-00')
    return '';
  $m = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
  $p = explode('-', $d);
  return (count($p) === 3) ? ((int) $p[2] . ' ' . $m[(int) $p[1]] . ' ' . ((int) $p[0] + 543)) : $d;
}
function chkBox($val)
{
  $c = $val ? '&#9745;' : '&#9744;';
  return '<span style="font-family:dejavusans;font-size:11px;">' . $c . '</span>';
}
function nv($rec, $key, $default = '')
{
  return (isset($rec[$key]) && $rec[$key] !== null && $rec[$key] !== '') ? $rec[$key] : $default;
}
function nvf($rec, $key, $dec = 2)
{
  $v = isset($rec[$key]) && $rec[$key] !== null && $rec[$key] !== '' ? (float) $rec[$key] : null;
  return $v !== null ? number_format($v, $dec) : '-';
}

$pt_name = trim(nv($pt, 'pname') . nv($pt, 'fname') . ' ' . nv($pt, 'lname'));
$hn = nv($pt, 'hn');
$ward_name = nv($pt, 'ward_name');
$reg_date = thDate(nv($pt, 'regdate'));
$dch_date = thDate(nv($pt, 'dchdate'));
$print_dt = thDate(date('Y-m-d')) . ' ' . date('H:i');

$order_date = thDate(nv($rec, 'order_date'));
$order_type_lbl = (nv($rec, 'order_type') === 'adjuvant') ? 'CBrC14 A (Adjuvant)' : ((nv($rec, 'order_type') === 'metastasis') ? 'CBrC24 (Metastasis)' : '-');
$cycle_no = nv($rec, 'cycle_no', '-');
$bw = nvf($rec, 'bw', 1);
$ht = nvf($rec, 'ht', 1);
$bsa = nvf($rec, 'bsa', 4);
$paclitaxel = nvf($rec, 'paclitaxel_dose', 2);
$next_appt = thDate(nv($rec, 'next_appt_date'));
$creator_name = nv($rec, 'created_name', nv($rec, 'created_by'));
$creator_pos = nv($rec, 'created_position');
$created_at = nv($rec, 'created_at') ? thDate(substr(nv($rec, 'created_at'), 0, 10)) . ' ' . substr(nv($rec, 'created_at'), 11, 5) : '';

// ===== CSS =====
$css = '<style>
body        { font-family: garuda, sans-serif; font-size: 10px; margin:0; padding:0; }
.cb         { font-family: dejavusans; font-size: 11px; }
table       { border-collapse: collapse; }
.main-tbl   { width:100%; }
.main-tbl th, .main-tbl td { border:1px solid #444; padding:3px 5px; vertical-align:top; }
.th-hdr     { background:#d0d0d0; font-weight:bold; font-size:10px; text-align:center; padding:4px; }
.th-sub     { background:#e8e8e8; font-weight:bold; font-size:9px; padding:2px 4px; }
.lbl        { font-weight:bold; font-size:9.5px; color:#222; }
.val        { font-size:10px; }
.center     { text-align:center; }
.bold       { font-weight:bold; }
.lab-tbl    { width:100%; border-collapse:collapse; font-size:9px; }
.lab-tbl td { border:1px solid #bbb; padding:2px 3px; }
.lab-lbl    { background:#f0f0f0; font-weight:bold; width:40%; }
.footer-tbl { width:100%; border-collapse:collapse; margin-top:4px; }
.footer-tbl td { border:1px solid #444; padding:3px 5px; vertical-align:top; }
.sig-lbl    { font-size:9px; color:#555; font-weight:bold; background:#f5f5f5; border-bottom:1px solid #aaa; padding:2px 4px; }
.sig-row    { font-size:10px; border-bottom:1px solid #ddd; padding:2px 4px; }
</style>';

// ===== SECTION: Progress Note =====
$ecog = nv($rec, 'ecog');
$prog_html = '<span class="lbl">ECOG:</span>&nbsp;';
for ($i = 1; $i <= 4; $i++)
  $prog_html .= chkBox($ecog == $i) . '&nbsp;' . $i . '&nbsp;&nbsp;';
$prog_html .= '<br>';

$prog_html .= '<span class="lbl">Diagnosis:</span><br>' . nl2br(htmlspecialchars(nv($rec, 'diagnosis'))) . '<br><br>';
$prog_html .= '<span class="lbl">Operation:</span><br>' . nl2br(htmlspecialchars(nv($rec, 'operation'))) . '<br><br>';

// Lab CBC
$prog_html .= '<br><span class="lbl">Lab — CBC</span><br>';
$prog_html .= '<table class="lab-tbl">';
$cbc = [['lab_hb', 'Hb'], ['lab_wbc', 'WBC'], ['lab_hct', 'Hct'], ['lab_plt', 'Plt'], ['lab_anc', 'ANC']];
foreach ($cbc as [$f, $l]):
  $prog_html .= '<tr><td class="lab-lbl">' . $l . '</td><td class="val">' . nvf($rec, $f) . '</td>';
endforeach;
// Differential
$diff = [['lab_n', 'N%'], ['lab_l', 'L%'], ['lab_m', 'M%'], ['lab_e', 'E%'], ['lab_b', 'B%']];
foreach ($diff as [$f, $l]):
  $prog_html .= '<tr><td class="lab-lbl">' . $l . '</td><td class="val">' . nvf($rec, $f) . '</td>';
endforeach;
$prog_html .= '</table>';

// Lab Electrolyte
$prog_html .= '<br><span class="lbl">Lab — Electrolyte / Renal</span><br>';
$prog_html .= '<table class="lab-tbl">';
$elec = [['lab_na', 'Na⁺'], ['lab_k', 'K⁺'], ['lab_bun', 'BUN'], ['lab_hco3', 'HCO₃⁻'], ['lab_cl', 'Cl⁻'], ['lab_scr', 'Scr']];
foreach ($elec as [$f, $l]):
  $prog_html .= '<tr><td class="lab-lbl">' . $l . '</td><td class="val">' . nvf($rec, $f) . '</td>';
endforeach;
$prog_html .= '</table>';

// Lab LFT
$prog_html .= '<br><span class="lbl">Lab — LFT</span><br>';
$prog_html .= '<table class="lab-tbl">';
$lft = [['lab_alb', 'Alb'], ['lab_glob', 'Glob'], ['lab_tb', 'TB'], ['lab_db', 'DB'], ['lab_ast', 'AST'], ['lab_alt', 'ALT'], ['lab_alp', 'ALP']];
foreach ($lft as [$f, $l]):
  $prog_html .= '<tr><td class="lab-lbl">' . $l . '</td><td class="val">' . nvf($rec, $f) . '</td>';
endforeach;
$prog_html .= '</table>';

// ===== SECTION: Order for Chemotherapy =====
$chem_html = chkBox(nv($rec, 'order_type') === 'adjuvant') . '&nbsp;<b>CBrC14 A (Adjuvant)</b>&nbsp;&nbsp;';
$chem_html .= chkBox(nv($rec, 'order_type') === 'metastasis') . '&nbsp;<b>CBrC24 (Metastasis)</b><br><br>';

$chem_html .= 'Date: <span class="bold">' . $order_date . '</span><br>';
$chem_html .= 'Cycle: <span class="bold">' . $cycle_no . '</span>/4 cycles (q 21 days)<br>';
$chem_html .= 'BW: <span class="bold">' . $bw . '</span> kg &nbsp;&nbsp; Ht: <span class="bold">' . $ht . '</span> cm<br>';
$chem_html .= 'BSA: <span class="bold">' . $bsa . '</span> m&sup2;<br><br>';

$chem_html .= chkBox(nv($rec, 'order_ac4')) . '&nbsp;AC 4 cycles Before start Paclitaxel<br>';
$chem_html .= chkBox(nv($rec, 'order_cbc_lab')) . '&nbsp;CBC, LFT, BUN, Electrolyte<br>';
$chem_html .= chkBox(nv($rec, 'order_nss1000')) . '&nbsp;NSS 1000 mL IV drip 80 cc/hr<br><br>';

$chem_html .= '<span class="lbl">Pre-medication</span> <span style="font-size:8.5px;">(30 min Before chemotherapy)</span><br>';
$chem_html .= '&nbsp;&nbsp;&bull; ' . chkBox(nv($rec, 'premed_dexa_ondan', 1)) . ' Dexamethasone 20 mg + Ondansetron 8 mg in D5W 100 mL IV drip in 30 min<br>';
$chem_html .= '&nbsp;&nbsp;&bull; ' . chkBox(nv($rec, 'premed_cpm', 1)) . ' CPM 10 mg IV push<br>';
$chem_html .= '&nbsp;&nbsp;&bull; ' . chkBox(nv($rec, 'premed_famotidine', 1)) . ' Famotidine (20) 1 tab oral stat<br>';
if (nv($rec, 'premed_other'))
  $chem_html .= '&nbsp;&nbsp;&bull; ' . htmlspecialchars(nv($rec, 'premed_other')) . '<br>';

$chem_html .= '<br><span class="lbl">Chemotherapy Order</span><br>';
$chem_html .= '1. <b>Paclitaxel</b> ............. <span class="bold">' . $paclitaxel . '</span> mg<br>';
$chem_html .= '<span style="font-size:9px;">&nbsp;&nbsp;&nbsp;(175 mg/m&sup2;) in NSS 500 mL IV drip in 3 hr via Infusion pump</span><br><br>';

$chem_html .= '<div style="text-align:center; font-weight:bold; font-size:10px; margin:8px 0;">
  ***<u>Monitor vital signs first 15 min</u><br>
  <u>and every 15 min &times; 4 times after starting Paclitaxel</u>***
</div><br>';

$chem_html .= '<span class="lbl">นัดครั้งต่อไปวันที่:</span> <b>' . ($next_appt ?: '....................') . '</b><br><br>';

// Follow-up LAB
$chem_html .= '<span class="lbl">LAB:</span>&nbsp;';
$fu_labs = [
  ['fu_lab_cbc', 'CBC'],
  ['fu_lab_electrolyte', 'Electrolyte'],
  ['fu_lab_lft', 'LFT'],
  ['fu_lab_bun', 'BUN'],
  ['fu_lab_scr', 'Scr'],
  ['fu_lab_ua', 'UA'],
  ['fu_lab_cea', 'CEA'],
  ['fu_lab_cxrpa', 'CXR PA']
];
foreach ($fu_labs as [$f, $l]):
  $chem_html .= chkBox(nv($rec, $f)) . '&nbsp;' . $l . '&nbsp;&nbsp;';
endforeach;
if (nv($rec, 'fu_lab_other')):
  $chem_html .= '<br>Other: ' . htmlspecialchars(nv($rec, 'fu_lab_other'));
endif;

// ===== SECTION: Order for Continuation =====
$cont_html = '&#8226; Regular diet<br>';
$cont_html .= '&#8226; Record V/S as usual<br><br>';

// VS Monitoring Grid
$cont_html .= '<span class="lbl">บันทึก Vital Signs (ทุก 15 นาที)</span><br>';
$cont_html .= '<table style="width:100%; border-collapse:collapse; font-size:8px; margin-bottom:8px; text-align:center;">
    <thead>
        <tr>
            <th style="border:1px solid #888; background:#e8e8e8;">เวลา</th>
            <th style="border:1px solid #888; background:#e8e8e8;">BP</th>
            <th style="border:1px solid #888; background:#e8e8e8;">PR</th>
            <th style="border:1px solid #888; background:#e8e8e8;">RR</th>
            <th style="border:1px solid #888; background:#e8e8e8;">SpO₂</th>
            <th style="border:1px solid #888; background:#e8e8e8;">T</th>
        </tr>
    </thead>
    <tbody>';
$vs_labels = ['ก่อนให้ยา', '15 นาที', '30 นาที', '45 นาที', '60 นาที', '75 นาที'];
foreach ($vs_labels as $vl) {
  $cont_html .= '<tr>
        <td style="border:1px solid #888; text-align:left; padding:2px;">' . $vl . '</td>
        <td style="border:1px solid #888;">&nbsp;</td>
        <td style="border:1px solid #888;">&nbsp;</td>
        <td style="border:1px solid #888;">&nbsp;</td>
        <td style="border:1px solid #888;">&nbsp;</td>
        <td style="border:1px solid #888;">&nbsp;</td>
    </tr>';
}
$cont_html .= '</tbody></table>';

$cont_html .= '<span class="lbl">Medication / Home Med</span><br>';
$cont_html .= '<table style="width:100%; border-collapse:collapse; font-size:9.5px;">';

// Fixed meds
$fixed_meds = [
  ['hmed_dexa4', 'Dexamethasone 4 mg <b>#6</b>', 'Sig 1x2 po pc Day 2–4'],
  ['hmed_ondan8', 'Ondansetron 8 mg <b>#6</b>', 'Sig 1x2 po ac Day 2–4'],
  ['hmed_metoclo', 'Metoclopramide 10 mg <b>#20</b>', 'Sig 1x3 po ac prn for N/V'],
  ['hmed_tramadol', 'Tramadol 50 mg <b>#20</b>', 'Sig 1&times;prn q 6 hr for pain'],
  ['hmed_senokot', 'Senokot <b>#20</b>', 'Sig 2&times;hs prn for constipation'],
];
foreach ($fixed_meds as [$f, $label, $sig]) {
  $cont_html .= '<tr>
        <td style="border-bottom:1px dotted #ccc; padding:2px 0;">
            ' . chkBox(nv($rec, $f)) . '&nbsp;' . $label . '<br>
            <span style="font-size:8.5px;color:#555;">&nbsp;&nbsp;&nbsp;&nbsp;' . $sig . '</span>
        </td>
    </tr>';
}

// Qty meds
$qty_meds = [
  ['hmed_multivit', 'hmed_multivit_qty', 'Multivitamin', 'Sig 1x3 po pc'],
  ['hmed_ff200', 'hmed_ff200_qty', 'FF 200 mg', 'Sig 1x3 po pc'],
  ['hmed_lorazepam', 'hmed_lorazepam_qty', 'Lorazepam 0.5 mg', 'Sig 1x1 po hs for insomnia'],
];
foreach ($qty_meds as [$f, $fq, $label, $sig]) {
  $qty = nv($rec, $fq) ? '#' . nv($rec, $fq) : '#......';
  $cont_html .= '<tr>
        <td style="border-bottom:1px dotted #ccc; padding:2px 0;">
            ' . chkBox(nv($rec, $f)) . '&nbsp;' . $label . '&nbsp;' . $qty . '<br>
            <span style="font-size:8.5px;color:#555;">&nbsp;&nbsp;&nbsp;&nbsp;' . $sig . '</span>
        </td>
    </tr>';
}

// Extra meds
for ($x = 1; $x <= 3; $x++) {
  $extra = nv($rec, "hmed_extra$x");
  $sig = nv($rec, "hmed_extra{$x}_sig");
  $cont_html .= '<tr>
        <td style="border-bottom:1px dotted #ccc; padding:2px 0;">
            ' . chkBox($extra != '') . '&nbsp;' . ($extra ? htmlspecialchars($extra) : '............................................') . '<br>
            <span style="font-size:8.5px;color:#555;">&nbsp;&nbsp;&nbsp;&nbsp;Sig: ' . ($sig ? htmlspecialchars($sig) : '...............') . '</span>
        </td>
    </tr>';
}
$cont_html .= '</table>';

// ===== BUILD FULL HTML =====
$html = $css;

// Title
$html .= '
<table style="width:100%; border-collapse:collapse; margin-bottom:4px;">
  <tr>
    <td style="width:15%;"></td>
    <td style="width:70%; text-align:center;">
      <span style="font-size:15px; font-weight:bold;">CA Breast <u>Paclitaxel</u> Regimen (AC-<u>4T</u>)</span><br>
      <span style="font-size:11px; font-weight:bold; letter-spacing:.5px;">CHEMOTHERAPY ORDER SHEET PRASAT HOSPITAL</span>
    </td>
    <td style="width:15%; text-align:right; vertical-align:middle;"></td>
  </tr>
</table>';

// Patient header
$html .= '
<table style="width:100%; border-collapse:collapse; border:1px solid #444; margin-bottom:5px;">
  <tr>
    <td style="padding:3px 6px; font-size:10px;">
      ชื่อ-สกุล: <b>' . htmlspecialchars($pt_name) . '</b>
      &nbsp;&nbsp; HN: <b>' . htmlspecialchars($hn) . '</b>
      &nbsp;&nbsp; AN: <b>' . htmlspecialchars($an) . '</b>
      &nbsp;&nbsp; Ward: ' . htmlspecialchars($ward_name) . '<br>
      วันที่รับ: ' . $reg_date . '&nbsp;&nbsp; วันที่จำหน่าย: ' . $dch_date . '
    </td>
  </tr>
</table>';

// Main 3-column table
$html .= '
<table class="main-tbl">
  <thead>
    <tr>
      <th class="th-hdr" style="width:33%;">PROGRESS NOTE</th>
      <th class="th-hdr" style="width:34%;">ORDER FOR CHEMOTHERAPY</th>
      <th class="th-hdr" style="width:33%;">ORDER FOR CONTINUATION</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td style="vertical-align:top; padding:5px;">' . $prog_html . '</td>
      <td style="vertical-align:top; padding:5px;">' . $chem_html . '</td>
      <td style="vertical-align:top; padding:5px;">' . $cont_html . '</td>
    </tr>
  </tbody>
</table>';

// Footer: 3 signatures
$sig_style = 'style="width:33%; vertical-align:top;"';
$html .= '
<table class="footer-tbl" style="margin-top:6px;">
  <tr>
    <td ' . $sig_style . '>
      <div class="sig-lbl">แพทย์ผู้สั่งใช้ (Progress Note)</div>
      <div style="padding:4px 6px; font-size:10px; line-height:1.6;">
        <br><br>
        ชื่อ: <b>' . htmlspecialchars($creator_name ?: '....................') . '</b><br>
        ตำแหน่ง: ' . htmlspecialchars($creator_pos ?: '....................') . '<br>
        วันที่: ' . $order_date . '
      </div>
    </td>
    <td ' . $sig_style . '>
      <div class="sig-lbl">แพทย์ผู้สั่งใช้ (Chemotherapy Order)</div>
      <div style="padding:4px 6px; font-size:10px; line-height:1.6;">
        <br><br>
         ชื่อ: <b>' . htmlspecialchars($creator_name ?: '....................') . '</b><br>
        ตำแหน่ง: ' . htmlspecialchars($creator_pos ?: '....................') . '<br>
        วันที่: ' . $order_date . '
      </div>
    </td>
    <td ' . $sig_style . '>
      <div class="sig-lbl">แพทย์ผู้สั่งใช้ (Continuation)</div>
      <div style="padding:4px 6px; font-size:10px; line-height:1.6;">
        <br><br>
         ชื่อ: <b>' . htmlspecialchars($creator_name ?: '....................') . '</b><br>
        ตำแหน่ง: ' . htmlspecialchars($creator_pos ?: '....................') . '<br>
        วันที่: ' . $order_date . '
      </div>
    </td>
  </tr>
</table>';

$html .= '<div style="font-size:8px; color:#aaa; text-align:right; margin-top:3px;">
  AN: ' . htmlspecialchars($an) . ' | พิมพ์: ' . $print_dt . ' | บันทึกโดย: ' . htmlspecialchars($creator_name) . '
</div>';

// ===== mPDF Output =====
$mpdf = new \Mpdf\Mpdf([
  'mode' => 'utf-8',
  'format' => 'A4',
  'margin_left' => 12,
  'margin_right' => 12,
  'margin_top' => 10,
  'margin_bottom' => 10,
]);
$mpdf->autoScriptToLang = true;
$mpdf->autoLangToFont = true;
$mpdf->WriteHTML($html);
$mpdf->Output('CABreast_' . $an . '_' . date('Ymd') . '.pdf', 'I');
