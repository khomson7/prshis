<?php
/**
 * ors-nursing-focus-pdf.php
 * พิมพ์ NURSING FOCUS CHARTTING (FM-NSO-ANE-006-07) เป็น PDF A4
 * Layout ตามต้นแบบ: DATE/SHIFT | TIME | FOCUS | PROGRESS NOTE
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

$stmt = $conn->prepare("SELECT * FROM prs_ors_nursing_focus WHERE id = :id AND an = :an AND is_deleted = 0");
$stmt->execute(array('id' => $id, 'an' => $an));
$rec = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$rec)
  die('ไม่พบข้อมูล');

// ข้อมูลผู้ป่วย
$stmt_pt = $conn->prepare(
  "SELECT p.hn, p.pname, p.fname, p.lname, p.birthday,
            i.regdate, i.dchdate, i.ward,
            w.name AS ward_name
       FROM " . DbConstant::HOSXP_DBNAME . ".ipt i
       LEFT JOIN " . DbConstant::HOSXP_DBNAME . ".patient p ON p.hn = i.hn
       LEFT JOIN " . DbConstant::HOSXP_DBNAME . ".ward   w ON w.ward = i.ward
      WHERE i.an = :an"
);
$stmt_pt->execute(array('an' => $an));
$pt = $stmt_pt->fetch(PDO::FETCH_ASSOC);

// ---- helpers ----
function thDate($d)
{
  if (!$d || $d === '0000-00-00')
    return '';
  $m = array(
    '',
    'ม.ค.',
    'ก.พ.',
    'มี.ค.',
    'เม.ย.',
    'พ.ค.',
    'มิ.ย.',
    'ก.ค.',
    'ส.ค.',
    'ก.ย.',
    'ต.ค.',
    'พ.ย.',
    'ธ.ค.'
  );
  $p = explode('-', $d);
  return (count($p) === 3) ? ((int) $p[2] . ' ' . $m[(int) $p[1]] . ' ' . ((int) $p[0] + 543)) : $d;
}

// Checkbox — ใช้ dejavusans เพื่อให้ Unicode checkbox แสดงผลได้ใน mPDF
function chkBox($val)
{
  $char = $val ? '&#9745;' : '&#9744;';
  return '<span style="font-family:dejavusans; font-size:11px;">' . $char . '</span>';
}

function nv($rec, $key, $default = '')
{
  return (isset($rec[$key]) && $rec[$key] !== null && $rec[$key] !== '') ? $rec[$key] : $default;
}
function fv($rec, $key)
{
  $v = isset($rec[$key]) && $rec[$key] !== null && $rec[$key] !== '' ? (float) $rec[$key] : null;
  return $v !== null ? number_format($v, 1) : '';
}

$pt_name = trim((isset($pt['pname']) ? $pt['pname'] : '')
  . (isset($pt['fname']) ? $pt['fname'] : '')
  . ' ' . (isset($pt['lname']) ? $pt['lname'] : ''));
$hn = isset($pt['hn']) ? $pt['hn'] : '';
$ward_name = isset($pt['ward_name']) ? $pt['ward_name'] : '';
$reg_date = thDate(isset($pt['regdate']) ? $pt['regdate'] : '');
$dch_date = thDate(isset($pt['dchdate']) ? $pt['dchdate'] : '');
$print_dt = thDate(date('Y-m-d')) . ' ' . date('H:i');

$rec_date  = thDate(nv($rec, 'visit_date'));
$rec_time  = nv($rec, 'visit_time');
$rec_shift = nv($rec, 'shift');

// วันเวลาเยี่ยมผู้ป่วย
$pt_visit_date = nv($rec, 'patient_visit_date') ? thDate(nv($rec, 'patient_visit_date')) : '';
$pt_visit_time = nv($rec, 'patient_visit_time', '');

// ===== FOCUS TEXT =====
$focus_text = nv($rec, 'focus_text', 'มีโอกาสเกิดภาวะแทรกซ้อนหลังการให้ยาระดับความรู้สึกภายใน 24 - 48 ชั่วโมง');

// ===== CSS =====
$css = '<style>
body        { font-family: garuda, sans-serif; font-size: 10.5px; margin:0; padding:0; }
.cb         { font-family: dejavusans; font-size: 11px; }
table       { border-collapse: collapse; }
.main-tbl   { width: 100%; }
.main-tbl th, .main-tbl td {
    border: 1px solid #444;
    padding: 3px 5px;
    vertical-align: middle;
}
.col-date   { width: 60px;  text-align: center; }
.col-time   { width: 45px;  text-align: center; }
.col-focus  { width: 140px; font-size: 9.5px; vertical-align: top; padding: 3px 4px; }
.col-note   { vertical-align: top; padding: 3px 5px; }
.th-bg      { background: #e0e0e0; font-weight: bold; font-size: 10px; text-align: center; }
.bold       { font-weight: bold; }

/* fluid nested table */
.fluid-tbl  { width: 100%; border-collapse: collapse; font-size: 9.5px; }
.fluid-tbl th, .fluid-tbl td {
    border: 1px solid #888;
    padding: 2px 4px;
    vertical-align: middle;
}
.fluid-tbl thead th { background: #d8d8d8; text-align: center; font-weight: bold; }
.fluid-hdr-v {
    width: 16px;
    background: #ececec;
    font-weight: bold;
    text-align: center;
    font-size: 8.5px;
}
.fluid-label { width: 70px; }
.fluid-val   { text-align: right; width: 55px; }

.score-box  { display: inline-block; border: 1px solid #333; padding: 1px 6px;
              min-width: 22px; text-align: center; font-weight: bold; }
.line-box   { border: 1px solid #555; min-height: 16px; padding: 2px 4px; margin: 1px 0; }
.footer-tbl { width: 100%; border-collapse: collapse; margin-top: 4px; }
.footer-tbl td { border: 1px solid #444; padding: 3px 5px; vertical-align: middle; }
</style>';

// ===== DATE/SHIFT cell =====
$op_date_content = '<span style="font-size:8.5px; color:#555;">วันผ่าตัด</span><br>'
                 . '<span class="bold">' . htmlspecialchars($rec_date) . '</span>';
if ($rec_shift)
  $op_date_content .= '<br>' . htmlspecialchars($rec_shift);

if ($pt_visit_date) {
  $visit_date_content = '<span style="font-size:8.5px; color:#555;">วันเยี่ยม</span><br>'
                      . '<span class="bold">' . htmlspecialchars($pt_visit_date) . '</span>';
  $ds_cell = '<table style="width:100%; border-collapse:collapse; margin:0; padding:0;">'
           . '<tr><td style="border:none; border-bottom:1px solid #888; padding:2px 0; text-align:center;">' . $op_date_content . '</td></tr>'
           . '<tr><td style="border:none; padding:2px 0; text-align:center;">' . $visit_date_content . '</td></tr>'
           . '</table>';
} else {
  $ds_cell = $op_date_content;
}

// ===== ANESTHETIC TECHNIQUE =====
$anes_other = nv($rec, 'anes_other');
$anes_row = 'A:- Anesthetic Technique:&nbsp;'
  . chkBox($rec['anes_ga']) . '&nbsp;GA&nbsp;&nbsp;&nbsp;'
  . chkBox($rec['anes_tiva']) . '&nbsp;TIVA<br>'
  . chkBox($rec['anes_ra']) . '&nbsp;RA&nbsp;&nbsp;&nbsp;'
  . chkBox($rec['anes_mac']) . '&nbsp;MAC&nbsp;&nbsp;&nbsp;'
  . chkBox($anes_other !== '') . '&nbsp;Other:&nbsp;' . htmlspecialchars($anes_other);

// ===== POST OPERATION =====
$post_op_note = nv($rec, 'post_op_note');
$wound_row = 'Post Operation :-<br>';
if ($post_op_note !== '') {
  $wound_row .= nl2br(htmlspecialchars($post_op_note)) . '<br>';
}
$wound_row .= 'ตำแหน่งแผล :&nbsp;'
  . chkBox($rec['wound_right']) . '&nbsp;ขวา&nbsp;&nbsp;'
  . chkBox($rec['wound_left']) . '&nbsp;ซ้าย<br>'
  . 'ลักษณะแผล :&nbsp;'
  . chkBox($rec['wound_dry'])  . '&nbsp;แผลแห้ง&nbsp;&nbsp;'
  . chkBox($rec['wound_wet'])  . '&nbsp;แผลซึม&nbsp;&nbsp;'
  . chkBox($rec['not_wound'])  . '&nbsp;ไม่มีแผล';

// ===== SUMMARY FLUID nested table =====
$fluid_html = '
<table class="fluid-tbl">
  <thead>
    <tr>
      <th colspan="2">Summary Fluid</th>
      <th>Intra-op (mL)</th>
      <th>PACU (mL)</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td class="fluid-hdr-v" rowspan="5">I<br>n<br>t<br>a<br>k<br>e</td>
      <td class="fluid-label">Crystalloid</td>
      <td class="fluid-val">' . fv($rec, 'in_crystalloid_io') . '</td>
      <td class="fluid-val">' . fv($rec, 'in_crystalloid_pacu') . '</td>
    </tr>
    <tr>
      <td class="fluid-label">Colloid</td>
      <td class="fluid-val">' . fv($rec, 'in_colloid_io') . '</td>
      <td class="fluid-val">' . fv($rec, 'in_colloid_pacu') . '</td>
    </tr>
    <tr>
      <td class="fluid-label">PRC</td>
      <td class="fluid-val">' . fv($rec, 'in_prc_io') . '</td>
      <td class="fluid-val">' . fv($rec, 'in_prc_pacu') . '</td>
    </tr>
    <tr>
      <td class="fluid-label">FFP</td>
      <td class="fluid-val">' . fv($rec, 'in_ffp_io') . '</td>
      <td class="fluid-val">' . fv($rec, 'in_ffp_pacu') . '</td>
    </tr>
    <tr>
      <td class="fluid-label">Other,</td>
      <td class="fluid-val">' . fv($rec, 'in_other_io') . '</td>
      <td class="fluid-val">' . fv($rec, 'in_other_pacu') . '</td>
    </tr>
    <tr>
      <td class="fluid-hdr-v" rowspan="4">O<br>u<br>t<br><br>p<br>u<br>t</td>
      <td class="fluid-label">Blood loss</td>
      <td class="fluid-val">' . fv($rec, 'out_bloodloss_io') . '</td>
      <td class="fluid-val">' . fv($rec, 'out_bloodloss_pacu') . '</td>
    </tr>
    <tr>
      <td class="fluid-label">Drain</td>
      <td class="fluid-val">' . fv($rec, 'out_drain_io') . '</td>
      <td class="fluid-val">' . fv($rec, 'out_drain_pacu') . '</td>
    </tr>
    <tr>
      <td class="fluid-label">Urine</td>
      <td class="fluid-val">' . fv($rec, 'out_urine_io') . '</td>
      <td class="fluid-val">' . fv($rec, 'out_urine_pacu') . '</td>
    </tr>
    <tr>
      <td class="fluid-label">สีปัสสาวะ</td>
      <td class="fluid-val">' . fv($rec, 'out_other_io') . '</td>
      <td class="fluid-val">' . fv($rec, 'out_other_pacu') . '</td>
    </tr>
  </tbody>
</table>';

// ===== SCORES =====
$aldrete = nv($rec, 'aldrete_score', '-');
$pain = nv($rec, 'pain_score', '-');
$sedation = nv($rec, 'sedation_score', '-');
$scores_row = 'Modified\'s Aldrete Scoring System :&nbsp;'
  . '<span>' . htmlspecialchars($aldrete) . '</span>&nbsp;คะแนน<br>'
  . 'Pain Score :&nbsp;'
  . '<span>' . htmlspecialchars($pain) . '</span>&nbsp;คะแนน,'
  . '&nbsp;&nbsp;Sedation Score :&nbsp;'
  .'<span>' . htmlspecialchars($sedation) . '</span>&nbsp;คะแนน';
 // . '<span class="score-box">' . htmlspecialchars($sedation) . '</span>&nbsp;คะแนน';

// ===== RESPIRATORY =====
$o2_txt = nv($rec, 'resp_o2_with');
$resp_row = 'Respiratory Status :-&nbsp;'
  . chkBox($rec['resp_room_air']) . '&nbsp;Room air&nbsp;&nbsp;'
  . chkBox($o2_txt !== '') . '&nbsp;O2 with&nbsp;' . htmlspecialchars($o2_txt);

// ===== DISCHARGE =====
$dis_to = nv($rec, 'discharge_to');
$trans = nv($rec, 'transfer_by');
$assess = nv($rec, 'assess_note');
$discharge_row = ':- เคลื่อนย้ายผู้ป่วยกลับ&nbsp;'
  . chkBox($dis_to === 'หอผู้ป่วย') . '&nbsp;หอผู้ป่วย&nbsp;'
  . chkBox($dis_to === 'ICU') . '&nbsp;ICU&nbsp;'
  . chkBox($dis_to === 'ห้องสังเกตอาการ') . '&nbsp;ห้องสังเกตอาการ<br>'
  . ':- เคลื่อนย้ายโดย&nbsp;'
  . chkBox($trans === 'รถนอน') . '&nbsp;รถนอน&nbsp;&nbsp;'
  . chkBox($trans === 'รถนั่ง') . '&nbsp;รถนั่ง<br>'
  . '<hr style="border:none; border-top:1px solid #aaa; margin:3px 0;">'
  .'<b>กิจกรรมการพยาบาล</b><br>'
  . 'I :- ประเมินผู้ป่วยหลังการให้ยาระดับความรู้สึกภายใน 24-48 ชั่วโมง<br>'
  . nl2br(htmlspecialchars($assess));

// ===== COMPLICATIONS =====
$comp_detail = nv($rec, 'complication_detail');
$comp_row = 'E :-&nbsp;'
  . chkBox($rec['no_complication']) . '&nbsp;ไม่มีภาวะแทรกซ้อนหลังให้ยาระดับความรู้สึก<br>'
  . chkBox($rec['has_complication']) . '&nbsp;มีภาวะแทรกซ้อนหลังให้ยาระดับความรู้สึก คือ<br>';
if ($rec['has_complication']) {
  $comp_row .= nl2br(htmlspecialchars($comp_detail));
} else {
  $comp_row .= '';
}

// ===== REMARK =====
$remark = nv($rec, 'remark');
$remark_html = '<span style="font-weight:bold;">REMARK:</span><br>' . (nl2br(htmlspecialchars($remark)) ?: '&nbsp;');

// ===== VISIT INFO =====
$v_nurse    = nv($rec, 'visit_nurse');
$v_pos      = nv($rec, 'nurse_position');
$v_date     = thDate(nv($rec, 'patient_visit_date'));
$v_time     = nv($rec, 'patient_visit_time');

// ผู้บันทึก
$cr_name    = nv($rec, 'created_name',     nv($rec, 'created_by'));
$cr_pos     = nv($rec, 'created_position', '');
$cr_date    = thDate(substr(nv($rec, 'visit_date'), 0, 10));
$cr_time    = nv($rec, 'visit_time', '');
$cr2_date    = thDate(substr(nv($rec, 'patient_visit_date'), 0, 10));
$cr2_time    = nv($rec, 'patient_visit_time', '');

// ===== BUILD HTML =====
$html = $css;

// --- PAGE TITLE ---
$html .= '
<table style="width:100%; border-collapse:collapse; margin-bottom:4px;">
  <tr>
    <td style="width:15%;"></td>
    <td style="width:70%; text-align:center;">
      <span style="font-size:13px; font-weight:bold; letter-spacing:1px;">NURSING FOCUS CHARTHING</span>
    </td>
    <td style="width:15%; text-align:right; vertical-align:middle;">
      <span style="border:1px solid #555; padding:2px 5px; font-size:10px;">FM-NSO-ANE-006-07</span>
    </td>
  </tr>
</table>';

// --- PATIENT HEADER ---
$html .= '
<table style="width:100%; border-collapse:collapse; border-bottom:1.5px solid #333; margin-bottom:5px;">
  <tr>
    <td style="padding:2px 4px; font-size:10px;">
      ชื่อ-สกุล: <b>' . htmlspecialchars($pt_name) . '</b>
      &nbsp;&nbsp; HN: <b>' . htmlspecialchars($hn) . '</b>
      &nbsp;&nbsp; AN: <b>' . htmlspecialchars($an) . '</b>
      &nbsp;&nbsp; Ward: ' . htmlspecialchars($ward_name) . '<br>
      วันที่รับ: ' . $reg_date . '&nbsp;&nbsp; วันที่จำหน่าย: ' . $dch_date . '
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    </td>
  </tr>
</table>';

// --- MAIN TABLE ---
// Total data rows for rowspan: anes(1) + post_op(1) + fluid(1) + scores(1) + resp(1) + discharge(1) + comp(1) + remark(1) = 8
$RS = 8;
$RS_FOCUS = 8; // FOCUS column spans all 8 rows

$html .= '
<table class="main-tbl">
  <thead>
    <tr>
      <th class="th-bg col-date">DATE/SHIFT</th>
      <th class="th-bg col-time">TIME</th>
      <th class="th-bg col-focus">FOCUS</th>
      <th class="th-bg col-note">PROGRESS NOTE</th>
    </tr>
  </thead>
  <tbody>

    <!-- Row 1: Anesthetic Technique -->
    <tr>
      <td class="col-date" rowspan="' . $RS . '" style="vertical-align:top; text-align:center;">
        ' . $ds_cell . '
      </td>
      <td class="col-time" rowspan="' . $RS . '" style="vertical-align:top; text-align:center;">
        ' . ($pt_visit_time
          ? '<table style="width:100%; border-collapse:collapse; margin:0; padding:0;">'
          . '<tr><td style="border:none; border-bottom:1px solid #888; padding:2px 0; text-align:center;"><span style="font-size:8.5px; color:#555;">เวลาผ่าตัด</span><br>' . htmlspecialchars($rec_time) . '</td></tr>'
          . '<tr><td style="border:none; padding:2px 0; text-align:center;"><span style="font-size:8.5px; color:#555;">เวลา<br>เยี่ยม</span><br>' . htmlspecialchars($pt_visit_time) . '</td></tr>'
          . '</table>'
          : '<span style="font-size:8.5px; color:#555;">เวลาผ่าตัด</span><br>' . htmlspecialchars($rec_time)
        ) . '
      </td>
      <td class="col-focus" rowspan="' . $RS_FOCUS . '" style="font-size:9px; vertical-align:top; text-align:left;">
        ' . nl2br(htmlspecialchars($focus_text)) . '
      </td>
      <td class="col-note">' . $anes_row . '</td>
    </tr>

    <!-- Row 2: Post Operation / Wound -->
    <tr>
      <td class="col-note">' . $wound_row . '</td>
    </tr>

    <!-- Row 3: Summary Fluid -->
    <tr>
      <td class="col-note" style="padding:3px;">' . $fluid_html . '</td>
    </tr>

    <!-- Row 4: Scores -->
    <tr>
      <td class="col-note">' . $scores_row . '</td>
    </tr>

    <!-- Row 5: Respiratory -->
    <tr>
      <td class="col-note">' . $resp_row . '</td>
    </tr>

    <!-- Row 6: Discharge -->
    <tr>
      <td class="col-note">' . $discharge_row . '</td>
    </tr>

    <!-- Row 7: Complications -->
    <tr>
      <td class="col-note">' . $comp_row . '</td>
    </tr>

    <!-- Row 8: Remark -->
    <tr>
      <td class="col-note">
        ' . $remark_html . '
      </td>
    </tr>

  </tbody>
</table>';

// --- FOOTER: STICKER + VISIT INFO ---
$html .= '
<table class="footer-tbl" style="margin-top:4px;">
  <tr>
    <td style="width:35%; height:70px; text-align:center; vertical-align:middle; color:#aaa; font-size:9px;">
      STICKER
    </td>
    <!-- Visit Nurse (ผู้เยี่ยม) -->
    <td style="width:32%; vertical-align:top;">
      <table style="width:100%; border-collapse:collapse;">
        <tr>
          <td style="padding:2px 4px; border-bottom:1px solid #aaa; font-size:9px; color:#555; font-weight:bold; background:#f5f5f5;">
            Visit Nurse (ผู้เยี่ยม)
          </td>
        </tr>
        <tr>
          <td style="padding:2px 4px; border-bottom:1px solid #aaa; font-size:10px;">
            ชื่อ:&nbsp;<b>' . htmlspecialchars($v_nurse ?: '-') . '</b>
          </td>
        </tr>
        <tr>
          <td style="padding:2px 4px; border-bottom:1px solid #aaa; font-size:10px;">
            ตำแหน่ง:&nbsp;' . htmlspecialchars($v_pos ?: '-') . '
          </td>
        </tr>
        <tr>
          <td style="padding:2px 4px; font-size:10px;">
            วันที่เยี่ยม:&nbsp;' . $v_date . '&nbsp;' . htmlspecialchars($v_time) . '
          </td>
        </tr>
      </table>
    </td>
    <!-- ผู้บันทึก -->
    <td style="width:33%; vertical-align:top;">
      <table style="width:100%; border-collapse:collapse;">
        <tr>
          <td style="padding:2px 4px; border-bottom:1px solid #aaa; font-size:9px; color:#555; font-weight:bold; background:#f5f5f5;">
            ผู้บันทึก
          </td>
        </tr>
        <tr>
          <td style="padding:2px 4px; border-bottom:1px solid #aaa; font-size:10px;">
            ชื่อ:&nbsp;<b>' . htmlspecialchars($cr_name ?: '-') . '</b>
          </td>
        </tr>
        <tr>
          <td style="padding:2px 4px; border-bottom:1px solid #aaa; font-size:10px;">
            ตำแหน่ง:&nbsp;' . htmlspecialchars($cr_pos ?: '-') . '
          </td>
        </tr>
        <tr>
          <td style="padding:2px 4px; font-size:10px;">
            บันทึกเมื่อ:&nbsp;' . $cr_date . '&nbsp;' . $cr_time . '
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>';

$html .= '<div style="font-size:8px; color:#aaa; text-align:right; margin-top:3px;">
  AN: ' . htmlspecialchars($an) . ' | พิมพ์: ' . $print_dt . '
</div>';

// ===== mPDF Output =====
$mpdf = new \Mpdf\Mpdf(array(
  'mode' => 'utf-8',
  'format' => 'A4',
  'margin_left' => 12,
  'margin_right' => 12,
  'margin_top' => 10,
  'margin_bottom' => 10,
));

$mpdf->autoScriptToLang = true;
$mpdf->autoLangToFont = true;

$mpdf->WriteHTML($html);
$mpdf->Output('ORS_NF_' . $an . '_' . date('Ymd') . '.pdf', 'I');
