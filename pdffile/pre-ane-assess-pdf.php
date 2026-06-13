<?php
require_once '../mains/datethai.php';
require_once '../include/Session.php';

$login = empty($_REQUEST['loginname']) ? null : $_REQUEST['loginname'];
$loginname = $_SESSION['loginname'];
$values = ['loginname' => $loginname];

if ($login != $loginname) {
    session_start();
    session_destroy();
    require_once '../mains/main-report.php';
}

Session::checkLoginSessionAndShowMessage();

if (!(Session::checkPermission('DOCUMENT', 'PRINT'))) {
    return;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require_once '../include/DbUtils.php';
require_once '../include/Session.php';
require_once '../include/KphisQueryUtils.php';
require_once '../include/ReportQueryUtils.php';

date_default_timezone_set('asia/bangkok');

$conn = DbUtils::get_hosxp_connection();
$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
$mpdf->AddPageByArray([
    'margin-left' => 12,
    'margin-right' => 12,
    'margin-top' => 10,
    'margin-bottom' => 12,
]);

$an = empty($_REQUEST['an']) ? null : $_REQUEST['an'];
$id = empty($_REQUEST['id']) ? null : $_REQUEST['id'];
$hn = KphisQueryUtils::getHnByAn($an);

Session::insertSystemAccessLog(json_encode(array(
    'report' => 'PRE-ANE-ASSESS-PDF',
    'an' => $an,
), JSON_UNESCAPED_UNICODE));

// Helper
function sv($row, $key, $def = null)
{
    return (isset($row[$key]) && $row[$key] !== '') ? $row[$key] : $def;
}
function cbt($row, $key, $label)
{
    $char = (isset($row[$key]) && $row[$key] == '1') ? '&#9745;' : '&#9744;';
    return '<span style="font-family:dejavusans; font-size:11pt;">' . $char . '</span> ' . $label;
}
function rbt($row, $key, $val, $label)
{
    $char = (isset($row[$key]) && $row[$key] === $val) ? '&#9673;' : '&#9711;';
    return '<span style="font-family:dejavusans; font-size:11pt;">' . $char . '</span> ' . $label;
}
function dateThai2($dateStr)
{
    if (!$dateStr || $dateStr === '0000-00-00')
        return '-';
    $months = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
    $p = explode('-', $dateStr);
    if (count($p) !== 3)
        return $dateStr;
    return (int) $p[2] . ' ' . $months[(int) $p[1]] . ' ' . ((int) $p[0] + 543);
}

// --- ข้อมูลผู้ป่วย ---
$patientSql = "SELECT p.pname, p.fname, p.lname, i.an, i.hn, i.regdate, i.dchdate, w.name AS ward_name, v.age_y, v.age_m, v.age_d
FROM " . DbConstant::HOSXP_DBNAME . ".ipt i
LEFT JOIN " . DbConstant::HOSXP_DBNAME . ".patient p ON p.hn = i.hn
LEFT JOIN " . DbConstant::HOSXP_DBNAME . ".ward w ON w.ward = i.ward
LEFT JOIN " . DbConstant::HOSXP_DBNAME . ".vn_stat v ON v.vn = i.vn
WHERE i.an = :an LIMIT 1";
$patientStmt = $conn->prepare($patientSql);
$patientStmt->bindParam(':an', $an);
$patientStmt->execute();
$patient = $patientStmt->fetch(PDO::FETCH_ASSOC);

$patientName = '';
$wardName = '';
$ageStr = '';
if ($patient) {
    $patientName = ($patient['pname'] ?? '') . ($patient['fname'] ?? '') . ' ' . ($patient['lname'] ?? '');
    $wardName = $patient['ward_name'] ?? '';
    $ageStr = ($patient['age_y'] ?? '0') . ' ปี ' . ($patient['age_m'] ?? '0') . ' เดือน';
}

// --- ข้อมูล Assess ---
$sql = "SELECT * FROM " . DbConstant::KPHIS_DBNAME . ".prs_pre_ane_assess WHERE id = :id AND an = :an";
$stmt = $conn->prepare($sql);
$stmt->execute(['id' => $id, 'an' => $an]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    die('<div style="font-family:sans-serif;padding:20px;color:red;">ไม่พบข้อมูลแบบประเมิน</div>');
}

// วอร์ดเยี่ยม
$visitWardName = '';
if ($row['visit_place'] == 'Ward' && !empty($row['ward'])) {
    $stmt_ward = $conn->prepare("SELECT name FROM " . DbConstant::HOSXP_DBNAME . ".ward WHERE ward = :w LIMIT 1");
    $stmt_ward->execute(['w' => $row['ward']]);
    $vw = $stmt_ward->fetch();
    if ($vw)
        $visitWardName = $vw['name'];
}

$html = '
<style>
    body { font-family: "Garuda"; font-size: 10pt; line-height: 1.3; }
    table { width: 100%; border-collapse: collapse; }
    .hdr-table td { padding: 2px; }
    .box { border: 1px solid #333; padding: 4px; border-radius: 4px; margin-bottom: 4px; }
    .box-title { font-weight: bold; background-color: #eee; padding: 2px 4px; border-bottom: 1px solid #333; margin: -4px -4px 4px -4px; border-radius: 4px 4px 0 0; }
    .indent { margin-left: 15px; }
    .u { border-bottom: 1px dotted #000; display: inline-block; min-width: 30px; text-align: center; }
    td { vertical-align: top; }
</style>
';

$html .= '<div style="text-align:center; font-size:12pt; font-weight:bold;">' . htmlspecialchars(DbConstant::HOSPITAL_NAME) . '</div>';
$html .= '<div style="text-align:center; font-size:13pt; font-weight:bold; margin-bottom:5px;">แบบบันทึกทางการพยาบาลการเตรียมผู้ป่วยก่อนให้ยาระงับความรู้สึก</div>';

$html .= '<table class="hdr-table" style="margin-bottom:6px; font-size:10pt;">
    <tr>
        <td style="width:20%;"><b>HN:</b> ' . htmlspecialchars($patient['hn'] ?? '-') . '</td>
        <td style="width:40%;"><b>ชื่อ-สกุล:</b> ' . htmlspecialchars($patientName) . '</td>
        <td style="width:20%;"><b>อายุ:</b> ' . $ageStr . '</td>
        <td style="width:20%;"><b>AN:</b> ' . htmlspecialchars($an) . '</td>
    </tr>
    <tr>
        <td colspan="2"><b>หอผู้ป่วย:</b> ' . htmlspecialchars($wardName) . '</td>
        <td colspan="2"><b>วันที่รับ:</b> ' . dateThai2($patient['regdate'] ?? '') . '</td>
    </tr>
</table>';

$html .= '<table style="margin-bottom:6px; font-size:10pt;">
    <tr>
        <td style="width:40%;">
            <b>Visit Type:</b> 
            ' . rbt($row, 'visit_type', 'Elective', 'Elective') . ' 
            ' . rbt($row, 'visit_type', 'OPD', 'OPD') . ' 
            ' . rbt($row, 'visit_type', 'Set เพิ่ม', 'Set เพิ่ม') . ' 
            ' . rbt($row, 'visit_type', 'Emergency', 'Emergency') . '
        </td>
        <td style="width:35%;">
            <b>เยี่ยมที่:</b> 
            ' . rbt($row, 'visit_place', 'OR', 'OR') . ' 
            ' . rbt($row, 'visit_place', 'Ward', 'Ward') . ' 
            ' . ($row['visit_place'] == 'Ward' ? '(' . htmlspecialchars($visitWardName) . ')' : '') . '
        </td>
        <td style="width:25%;">
            <b>ผ่าตัดวันที่:</b> ' . dateThai2(sv($row, 'operation_date')) . '
        </td>
    </tr>
</table>';

// Section 1
$html .= '<div class="box">
    <div class="box-title">1. การซักประวัติ</div>
    <table style="font-size:9pt;">
        <tr>
            <td style="width:50%;">
                <b>1.1 Activity:</b> ' . rbt($row, 'activity', 'ทำได้ปกติ', 'ทำได้ปกติ') . ' ' . rbt($row, 'activity', 'มีข้อจำกัด', 'มีข้อจำกัด') . ' ' . rbt($row, 'activity', 'ต้องนอนบนเตียง', 'ต้องนอนบนเตียง') . '<br>
                <div class="indent"><b>FC:</b> <span >' . htmlspecialchars(sv($row, 'fc', '-')) . '</span></div>
            </td>
            <td style="width:50%;">
                <b>1.2 โรคประจำตัว:</b><br>
                <div class="indent">
                    ' . cbt($row, 'underlying_dm', 'DM') . ' (Tx: <span >' . sv($row, 'underlying_dm_tx', '-') . '</span>)
                    ' . cbt($row, 'underlying_ht', 'HT') . ' (Tx: <span >' . sv($row, 'underlying_ht_tx', '-') . '</span>)
                    ' . cbt($row, 'underlying_dlp', 'DLP') . '<br>
                    ' . cbt($row, 'underlying_asthma', 'Asthma/COPD') . ' (Tx: <span >' . sv($row, 'underlying_asthma_tx', '-') . '</span>)<br>
                    ' . cbt($row, 'underlying_heart', 'Heart dz.') . ' (Tx: <span >' . sv($row, 'underlying_heart_tx', '-') . '</span>)<br>
                    ' . cbt($row, 'underlying_other', 'อื่นๆ') . ' <span >' . sv($row, 'underlying_other_text', '-') . '</span>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <b>1.3 การดื่มสุรา:</b><br>
                <div class="indent">
                    ' . rbt($row, 'alcohol', 'ไม่ดื่ม', 'ไม่ดื่ม') . '
                    ' . rbt($row, 'alcohol', 'เคย,เลิกมา', 'เคย,เลิกมา') . ' <span >' . sv($row, 'alcohol_year', '-') . '</span> ปี<br>
                    ' . rbt($row, 'alcohol', 'ดื่มเล็กน้อย', 'ดื่มเล็กน้อย') . '
                    ' . rbt($row, 'alcohol', 'ดื่มทุกวัน', 'ดื่มทุกวัน') . '
                </div>
            </td>
            <td>
                <b>1.4 การสูบบุหรี่:</b><br>
                <div class="indent">
                    ' . rbt($row, 'smoking', 'ไม่สูบ', 'ไม่สูบ') . '
                    ' . rbt($row, 'smoking', 'เคย,เลิกมา', 'เคย,เลิกมา') . ' <span >' . sv($row, 'smoking_year', '-') . '</span> ปี<br>
                    ' . rbt($row, 'smoking', 'สูบ', 'สูบ') . ' <span >' . sv($row, 'smoking_per_day', '-') . '</span> มวน/วัน
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <b>1.5 การแพ้ยา อาหาร สารเคมี:</b><br>
                <div class="indent">
                    ' . rbt($row, 'allergy', 'ไม่มี', 'ไม่มี') . '
                    ' . rbt($row, 'allergy', 'มี', 'มี') . ' ระบุ: <span >' . sv($row, 'allergy_detail', '-') . '</span>
                </div>
            </td>
            <td>
                <b>1.6 ประวัติการผ่าตัด, การดมยา:</b><br>
                <div class="indent">
                    ' . rbt($row, 'prev_surgery', 'ไม่เคย', 'ไม่เคย') . '
                    ' . rbt($row, 'prev_surgery', 'เคย', 'เคย') . ' 
                    (' . cbt($row, 'prev_surgery_ga', 'GA') . ' ' . cbt($row, 'prev_surgery_ra', 'RA') . ')
                    เมื่อ ' . dateThai2(sv($row, 'prev_surgery_date')) . '<br>
                    ' . cbt($row, 'prev_complication', 'Complication') . ' <span >' . sv($row, 'prev_complication_text', '-') . '</span>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <b>1.7 ประวัติยา:</b><br><div class="indent">' . nl2br(htmlspecialchars(sv($row, 'medication_history', '-'))) . '</div>
            </td>
        </tr>
    </table>
</div>';

// Section 2
$html .= '<div class="box">
    <div class="box-title">2. การตรวจร่างกาย</div>
    <table style="font-size:9pt;">
        <tr>
            <td colspan="2">
                <b>BW</b> <span >' . sv($row, 'pe_bw', '-') . '</span> Kgs &nbsp;
                <b>Ht</b> <span >' . sv($row, 'pe_ht', '-') . '</span> Cms &nbsp;
                <b>BMI</b> <span >' . sv($row, 'pe_bmi', '-') . '</span> &nbsp;
                <b>T</b> <span >' . sv($row, 'pe_temp', '-') . '</span> °C &nbsp;
                <b>PR</b> <span >' . sv($row, 'pe_pr', '-') . '</span> /min &nbsp;
                <b>RR</b> <span >' . sv($row, 'pe_rr', '-') . '</span> /min &nbsp;
                <b>BP</b> <span >' . sv($row, 'pe_bp', '-') . '</span> mmHg &nbsp;
                <b>SpO2</b> <span >' . sv($row, 'pe_spo2', '-') . '</span> %
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <b>2.1 ตรวจร่างกายทั่วไป:</b> 
                ' . cbt($row, 'general_normal', 'ปกติ') . '
                ' . cbt($row, 'general_pale', 'ซีด') . '
                ' . cbt($row, 'general_jaundice', 'เหลือง') . '
                ' . cbt($row, 'general_dry_mouth', 'ปากแห้ง') . '
                ' . cbt($row, 'general_fatigue', 'อ่อนเพลีย') . '
            </td>
        </tr>
        <tr>
            <td style="width:50%;">
                <b>2.2 Consciousness:</b> 
                ' . rbt($row, 'consciousness', 'Alert', 'Alert') . '
                ' . rbt($row, 'consciousness', 'Drowsiness', 'Drowsiness') . '
                ' . rbt($row, 'consciousness', 'Stupor', 'Stupor') . '
                ' . rbt($row, 'consciousness', 'Coma', 'Coma') . '
            </td>
            <td style="width:50%;">
                <b>2.3 Airway อ้าปาก & ก้มเงย:</b> 
                ' . rbt($row, 'airway_mouth', 'ปกติ', 'ปกติ') . '
                ' . rbt($row, 'airway_mouth', 'ผิดปกติ', 'ผิดปกติ') . ' <span >' . sv($row, 'airway_mouth_detail', '-') . '</span>
                ' . cbt($row, 'airway_unable', 'ไม่สามารถประเมินได้') . '
            </td>
        </tr>
        <tr>
            <td>
                <b>Mallampati Class:</b> 
                ' . rbt($row, 'mallampati', '1', '1') . '
                ' . rbt($row, 'mallampati', '2', '2') . '
                ' . rbt($row, 'mallampati', '3', '3') . '
                ' . rbt($row, 'mallampati', '4', '4') . '
            </td>
            <td>
                <b>On ET/TT tube No.:</b> <span >' . sv($row, 'on_ett_no', '-') . '</span>
            </td>
        </tr>
        <tr>
            <td>
                <b>Thyromental distance:</b> 
                ' . rbt($row, 'thyromental', '>= 3FB, 6 cm', '>= 3FB, 6 cm') . '
                ' . rbt($row, 'thyromental', '< 3FB', '< 3FB') . '
            </td>
            <td>
                <b>2.4 ฟันปลอม:</b> 
                ' . rbt($row, 'denture', 'ไม่มี', 'ไม่มี') . '
                ' . rbt($row, 'denture', 'มี', 'มี') . '
            </td>
        </tr>
        <tr>
            <td>
                <b>2.5 Heart & Lungs:</b> 
                ' . rbt($row, 'heart_lungs', 'Normal', 'Normal') . '
                ' . rbt($row, 'heart_lungs', 'Abnormal', 'Abnormal') . ' <span >' . sv($row, 'heart_lungs_detail', '-') . '</span>
            </td>
            <td>
                <b>2.6 Motor Power:</b> 
                ' . rbt($row, 'motor_power', 'Normal', 'Normal') . '
                ' . rbt($row, 'motor_power', 'Abnormal', 'Abnormal') . ' <span >' . sv($row, 'motor_power_detail', '-') . '</span>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <b>2.7 Other:</b> 
                ' . rbt($row, 'other_exam', 'Normal', 'Normal') . '
                ' . rbt($row, 'other_exam', 'Abnormal', 'Abnormal') . ' <span >' . sv($row, 'other_exam_detail', '-') . '</span>
            </td>
        </tr>
    </table>
</div>';

// Section 3
$html .= '<div class="box">
    <div class="box-title">3. Lab: ' . rbt($row, 'lab_status', 'ได้แล้ว', 'ได้แล้ว') . ' ' . rbt($row, 'lab_status', 'ยังไม่ได้ผล Lab', 'ยังไม่ได้ผล Lab') . '</div>
    <table style="font-size:9pt;">
        <tr>
            <td colspan="2">
                <b>3.1 CBC:</b> 
                Hct <span >' . sv($row, 'lab_hct', '-') . '</span> Vol% &nbsp;
                Hb <span >' . sv($row, 'lab_hb', '-') . '</span> Gm% &nbsp;
                Plt <span >' . sv($row, 'lab_plt', '-') . '</span> &nbsp;
                Other <span >' . sv($row, 'lab_other_cbc', '-') . '</span>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <b>3.2:</b> 
                FBS <span >' . sv($row, 'lab_fbs', '-') . '</span> mg% &nbsp;
                BUN <span >' . sv($row, 'lab_bun', '-') . '</span> &nbsp;
                Cr <span >' . sv($row, 'lab_cr', '-') . '</span> &nbsp;
                PT <span >' . sv($row, 'lab_pt', '-') . '</span> &nbsp;
                PTT <span >' . sv($row, 'lab_ptt', '-') . '</span> &nbsp;
                INR <span >' . sv($row, 'lab_inr', '-') . '</span>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <b>3.3 Electrolyte:</b> 
                ' . rbt($row, 'lab_electrolyte_check', 'ไม่ตรวจ', 'ไม่ตรวจ') . '
                ' . rbt($row, 'lab_electrolyte_check', 'ตรวจ', 'ตรวจ') . ' &nbsp;
                Na <span >' . sv($row, 'lab_na', '-') . '</span> &nbsp;
                K <span >' . sv($row, 'lab_k', '-') . '</span> &nbsp;
                Cl <span >' . sv($row, 'lab_cl', '-') . '</span> &nbsp;
                HCO3 <span >' . sv($row, 'lab_hco3', '-') . '</span>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <b>3.4 UA:</b> 
                ' . rbt($row, 'lab_ua_check', 'ไม่ตรวจ', 'ไม่ตรวจ') . '
                ' . rbt($row, 'lab_ua_check', 'ตรวจ', 'ตรวจ') . ' &nbsp;
                Sp.gr <span >' . sv($row, 'lab_sp_gr', '-') . '</span> &nbsp;
                Prot. <span >' . sv($row, 'lab_prot', '-') . '</span> &nbsp;
                Sugar <span >' . sv($row, 'lab_sugar', '-') . '</span>
            </td>
        </tr>
        <tr>
            <td style="width:33%;">
                <b>3.5 CXR:</b> 
                ' . rbt($row, 'lab_cxr_check', 'ไม่ตรวจ', 'ไม่ตรวจ') . '
                ' . rbt($row, 'lab_cxr_check', 'ตรวจ', 'ตรวจ') . '<br>
                <div class="indent">
                    ' . rbt($row, 'lab_cxr_result', 'ไม่มีผลอ่าน', 'ไม่มีผลอ่าน') . '
                    ' . rbt($row, 'lab_cxr_result', 'ปกติ', 'ปกติ') . '<br>
                    ' . rbt($row, 'lab_cxr_result', 'ผิดปกติ', 'ผิดปกติ') . ' <span >' . sv($row, 'lab_cxr_detail', '-') . '</span>
                </div>
            </td>
            <td style="width:33%;">
                <b>3.6 EKG:</b> 
                ' . rbt($row, 'lab_ekg_check', 'ไม่ตรวจ', 'ไม่ตรวจ') . '
                ' . rbt($row, 'lab_ekg_check', 'ตรวจ', 'ตรวจ') . '<br>
                <div class="indent">
                    ' . rbt($row, 'lab_ekg_result', 'ไม่มีผลอ่าน', 'ไม่มีผลอ่าน') . '
                    ' . rbt($row, 'lab_ekg_result', 'ปกติ', 'ปกติ') . '<br>
                    ' . rbt($row, 'lab_ekg_result', 'ผิดปกติ', 'ผิดปกติ') . ' <span >' . sv($row, 'lab_ekg_detail', '-') . '</span>
                </div>
            </td>
            <td style="width:33%;">
                <b>3.7 Other:</b> 
                ' . cbt($row, 'lab_other_check', 'ตรวจ') . '<br>
                <div class="indent"><span >' . sv($row, 'lab_other_detail', '-') . '</span></div>
            </td>
        </tr>
    </table>
</div>';

// Section 4, 5, 6
$html .= '<div class="box" style="padding:0;">
    <table style="width:100%; font-size:9pt; border-collapse:collapse;">
        <tr>
            <td style="width:42%; border-right:1px solid #333; padding:0; vertical-align:top;">
                <div class="box-title" style="margin:0 0 6px 0; border-bottom:1px solid #333; border-radius:4px 0 0 0;">4. จองเลือด</div>
                <div>&nbsp;</div>
                <div style="padding:0 4px 4px 4px;">
                
                    <div style="margin-bottom:6px;">
                        ' . rbt($row, 'blood_reserve', 'ไม่จอง', 'ไม่จอง') . ' &nbsp;&nbsp;&nbsp;
                        ' . rbt($row, 'blood_reserve', 'จอง', 'จอง') . '
                    </div>
                    <table style="width:100%; font-size:9pt;">
                        <tr>
                            <td style="padding:1px;">WB <span  style="min-width:20px;">' . sv($row, 'blood_wb', '-') . '</span> U</td>
                            <td style="padding:1px;">PRC <span  style="min-width:20px;">' . sv($row, 'blood_prc', '-') . '</span> U</td>
                            <td style="padding:1px;">FFP <span  style="min-width:20px;">' . sv($row, 'blood_ffp', '-') . '</span> U</td>
                        </tr>
                        <tr>
                            <td style="padding:1px;">Plt <span  style="min-width:20px;">' . sv($row, 'blood_plt_unit', '-') . '</span> U</td>
                            <td style="padding:1px;">Cryo <span  style="min-width:20px;">' . sv($row, 'blood_cryo', '-') . '</span> U</td>
                            <td style="padding:1px;">Others <span  style="min-width:20px;">' . sv($row, 'blood_others', '-') . '</span></td>
                        </tr>
                    </table>
                </div>
            </td>
            <td style="width:23%; border-right:1px solid #333; padding:0; vertical-align:top;">
                <div class="box-title" style="margin:0 0 6px 0; border-bottom:1px solid #333; border-radius:0;">5. เซ็นยินยอม</div>
                <br><div style="padding:0 4px 4px 4px;">
                    ' . rbt($row, 'consent_signed', 'มี', 'มี') . '<br><br>
                    ' . rbt($row, 'consent_signed', 'ไม่มี', 'ไม่มี') . '
                </div>
            </td>
            <td style="width:35%; padding:0; vertical-align:top;">
                <div class="box-title" style="margin:0 0 6px 0; border-bottom:1px solid #333; border-radius:0 4px 0 0;">6. ASA Physical Status</div>
                <div>&nbsp;</div><div style="padding:0 4px 4px 4px; line-height: 1.8;">
                    ' . rbt($row, 'asa_class', '1', '1') . ' &nbsp;
                    ' . rbt($row, 'asa_class', '2', '2') . ' &nbsp;
                    ' . rbt($row, 'asa_class', '3', '3') . '<br>
                    ' . rbt($row, 'asa_class', '4', '4') . ' &nbsp;
                    ' . rbt($row, 'asa_class', '5', '5') . ' &nbsp;
                    ' . rbt($row, 'asa_class', '6', '6') . '<br>
                    ' . rbt($row, 'asa_class', 'E', 'E') . '
                </div>
            </td>
        </tr>
    </table>
</div>';

// Section 7, 8, 9
$html .= '<div class="box" style="padding:0;">
    <table style="width:100%; font-size:9pt; border-collapse:collapse;">
        <tr>
            <td style="width:33%; border-right:1px solid #333; padding:0; vertical-align:top;">
                <div class="box-title" style="margin:0 0 6px 0; border-bottom:1px solid #333; border-radius:4px 0 0 0;">7. Problem List</div>
                <div>&nbsp;</div><div style="padding:0 4px 4px 4px;">
                    ' . rbt($row, 'problem_list_status', 'ไม่มี', 'ไม่มี') . '
                    ' . rbt($row, 'problem_list_status', 'มี ระบุ', 'มี ระบุ') . '<br>
                    <ol style="margin-top:2px; margin-bottom:0; padding-left:20px; line-height:1.4;">
                        <li><span >' . sv($row, 'problem_1', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;') . '</span></li>
                        <li><span >' . sv($row, 'problem_2', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;') . '</span></li>
                        <li><span >' . sv($row, 'problem_3', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;') . '</span></li>
                        <li><span >' . sv($row, 'problem_4', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;') . '</span></li>
                    </ol>
                </div>
            </td>
            <td style="width:33%; border-right:1px solid #333; padding:0; vertical-align:top;">
                <div class="box-title" style="margin:0 0 6px 0; border-bottom:1px solid #333; border-radius:0;">8. การแก้ปัญหา/รักษา</div>
                <div style="padding:0 4px 4px 4px;">
                    <div class="indent" style="line-height:1.4;">
                        <span  style="display:block; min-width:90%; margin-bottom:2px;">' . sv($row, 'treatment_1', '&nbsp;') . '</span>
                        <span  style="display:block; min-width:90%; margin-bottom:2px;">' . sv($row, 'treatment_2', '&nbsp;') . '</span>
                        <span  style="display:block; min-width:90%; margin-bottom:2px;">' . sv($row, 'treatment_3', '&nbsp;') . '</span>
                        <span  style="display:block; min-width:90%; margin-bottom:2px;">' . sv($row, 'treatment_4', '&nbsp;') . '</span>
                    </div>
                    <b>Consult:</b> 
                    ' . rbt($row, 'consult_status', 'ไม่มี', 'ไม่มี') . '
                    ' . rbt($row, 'consult_status', 'มี', 'มี') . '
                    (' . cbt($row, 'consult_med', 'Med') . '
                    ' . cbt($row, 'consult_anesth', 'Anesth') . ')<br>
                    <b>Premedication:</b><br>
                    <div class="indent" style="line-height:1.4;">
                        <span  style="display:block; min-width:90%; margin-bottom:2px;">' . sv($row, 'premed_1', '&nbsp;') . '</span>
                        <span  style="display:block; min-width:90%; margin-bottom:2px;">' . sv($row, 'premed_2', '&nbsp;') . '</span>
                        <span  style="display:block; min-width:90%; margin-bottom:2px;">' . sv($row, 'premed_3', '&nbsp;') . '</span>
                    </div>
                </div>
            </td>
            <td style="width:34%; padding:0; vertical-align:top;">
                <div class="box-title" style="margin:0 0 6px 0; border-bottom:1px solid #333; border-radius:0 4px 0 0;">9. Planning</div>
                <div>&nbsp;</div><div style="padding:0 4px 4px 4px;">
                    ' . cbt($row, 'plan_ga', 'GA') . '
                    ' . cbt($row, 'plan_ra', 'RA') . '
                    ' . cbt($row, 'plan_ra_ga', 'RA+GA') . '
                    ' . cbt($row, 'plan_mac', 'MAC') . '<br>
                    ' . cbt($row, 'plan_tiva', 'TIVA') . '
                    ' . cbt($row, 'plan_icu', 'จอง ICU') . '<br><br>
                    <b>Spec. Technique:</b><br>
                    ' . cbt($row, 'spec_a_line', 'A-Line') . '
                    ' . cbt($row, 'spec_cvp', 'CVP') . '
                    ' . cbt($row, 'spec_pca', 'PCA') . '<br>
                    ' . cbt($row, 'spec_dlt', 'DLT') . '
                    ' . cbt($row, 'spec_fiberoptic', 'Fiberoptic') . '<br>
                    ' . cbt($row, 'spec_other', 'อื่นๆ') . ' <span >' . sv($row, 'spec_other_text', '-') . '</span>
                </div>
            </td>
        </tr>
    </table>
</div>';

// Section 10 — page break before
$html .= '<pagebreak />';
$html .= '<div class="box">
    <div class="box-title">10. กิจกรรม การพยาบาลก่อนให้ยาระงับความรู้สึก</div>
    <table style="font-size:9pt;">
        <tr>
            <td style="width:50%;">
                ' . cbt($row, 'act_check_identity', 'ตรวจสอบความถูกต้องของผู้ป่วย&เวชระเบียน') . '<br>
                ' . cbt($row, 'act_assess_asa', 'ประเมินสภาพผู้ป่วยตาม ASA class') . '<br>
                ' . cbt($row, 'act_explain_anes', 'อธิบายถึงวิธีการให้ยาระงับความรู้สึกและความเสี่ยงที่อาจเกิดขึ้น') . '<br>
                ' . cbt($row, 'act_plan_anes', 'วางแผนเลือกวิธีให้ยาระงับความรู้สึกที่เหมาะสม') . '<br>
                ' . cbt($row, 'act_check_consent', 'ตรวจสอบการลงนามยินยอมการรับบริการทางวิสัญญี') . '<br>
                ' . cbt($row, 'act_pain_advice', 'ให้คำแนะนำเรื่องการระงับปวด') . '
            </td>
            <td style="width:50%;">
                <b>ให้คำแนะนำในเรื่อง:</b> <span >' . sv($row, 'act_advice', '-') . '</span><br>
                1. NPO เวลา <span >' . sv($row, 'act_npo_time', '-') . '</span> น. 
                ถอดฟันปลอมของมีค่า <span >' . sv($row, 'act_npo_denture', '-') . '</span><br>
                ' . cbt($row, 'act_teach_breathe', '2. สอนการหายใจ ไอ อย่างมีประสิทธิภาพ') . '<br>
                ' . cbt($row, 'act_prepare_body', '3. เตรียมร่างกาย จิตใจ การพักผ่อน') . '<br>
                4. อื่นๆ โปรดระบุ <span >' . sv($row, 'act_other_5', '-') . '</span>
            </td>
        </tr>
    </table>
</div>';

// Section 11 & 12
$html .= '<div class="box" style="padding:0;">
    <table style="width:100%; font-size:9pt; border-collapse:collapse;">
        <tr>
            <td style="width:55%; border-right:1px solid #333; padding:0; vertical-align:top;">
                <div class="box-title" style="margin:0 0 6px 0; border-bottom:1px solid #333; border-radius:4px 0 0 0;">11. กิจกรรมการพยาบาล ห้อง Pre-op</div>
                <div>&nbsp;</div><div style="padding:0 4px 4px 4px;">
                    ผู้ป่วยมาถึงห้อง Pre-op เวลา <span>' . sv($row, 'preop_arrival_time', '-') . '</span> น.<br>
                    ' . cbt($row, 'preop_talk', 'พูดคุย อธิบายถึงขั้นตอนการรับบริการทางวิสัญญี') . '<br>
                    ' . cbt($row, 'preop_check_identity', 'ตรวจสอบความถูกต้องของเวชระเบียนตรงกับผู้ป่วย') . '<br>
                    ชนิดการผ่าตัด ถูกคน ถูกข้าง: <span>' . sv($row, 'preop_check_surgery', '-') . '</span><br>
                    ' . cbt($row, 'preop_check_fluid', 'ตรวจสอบความถูกต้องของสารน้ำและบริเวณที่ให้') . '<br>
                    ' . cbt($row, 'preop_no_infiltrate', 'สารน้ำไม่มีภาวะแทรกซ้อน') . '<br>
                    ' . cbt($row, 'preop_comfort', 'ดูแลความสุขสบายผู้ป่วย') . '<br>
                    ' . cbt($row, 'preop_o2_mask', 'ดูแลให้ O2 Mask') . '<br>
                    ' . cbt($row, 'preop_3way', 'ต่อ 3 way & extension') . '<br>
                    ' . cbt($row, 'preop_other', 'อื่นๆ') . ' <span >' . sv($row, 'preop_other_text', '-') . '</span><br><br>
                    <b>ผู้ประเมิน:</b> <span>' . sv($row, 'preop_assess_by', '-') . '</span>
                </div>
            </td>
            <td style="width:45%; padding:0; vertical-align:top;">
                <div class="box-title" style="margin:0 0 6px 0; border-bottom:1px solid #333; border-radius:0 4px 0 0;">12. สรุปประเมินผล</div>
                <div>&nbsp;</div><div style="padding:0 4px 4px 4px;">
                    ' . cbt($row, 'result_got_surgery', 'ได้รับการผ่าตัด') . '<br>
                    ' . cbt($row, 'result_postpone', 'งดเลื่อน เนื่องจาก') . '<br>
                    <div class="indent">
                        ' . cbt($row, 'result_postpone_reason1', '1. แพทย์ผ่าตัด') . '<br>
                        ' . cbt($row, 'result_postpone_reason2', '2. ผู้ป่วยปฏิเสธการผ่าตัด') . '<br>
                        ' . cbt($row, 'result_postpone_reason3', '3. ไม่มีเลือด') . '<br>
                        ' . cbt($row, 'result_postpone_reason4', '4. มีปัญหาอายุรกรรมยังไม่ได้แก้ไขจากอายุรแพทย์') . '<br>
                        ' . cbt($row, 'result_postpone_reason5', '5. ไม่ได้เตรียมเตียง ICU') . '<br>
                        6. อื่นๆ ระบุ <span >' . sv($row, 'result_postpone_reason6', '-') . '</span>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</div>';

// Footer Section
$html .= '<div class="box" style="margin-top:10px;">
    <table style="font-size:9.5pt;">
        <tr>
            <td style="width:50%;">
                <b>DIAGNOSIS:</b><br>
                <div class="indent">' . nl2br(htmlspecialchars(sv($row, 'diagnosis', '-'))) . '</div><br>
                <b>OPERATION PLAN:</b><br>
                <div class="indent">' . nl2br(htmlspecialchars(sv($row, 'operation_plan', '-'))) . '</div>
            </td>
            <td style="width:50%; text-align:right;">
                <b>Visit Date:</b> <span >' . dateThai2(sv($row, 'visit_date')) . '</span><br><br>
                <table style="width:100%; font-size:9.5pt;">
                    <tr>
                        <td style="text-align:right;"><b>ผู้เยี่ยม:</b></td>
                        <td style="text-align:left; padding-left:10px;">( <span style="min-width:150px;">' . sv($row, 'visitor_name', '&nbsp;') . '</span> )</td>
                    </tr>
                    <tr>
                        <td style="text-align:right; padding-top:10px;"><b>ผู้เตรียมอุปกรณ์+Machine:</b></td>
                        <td style="text-align:left; padding-left:10px; padding-top:10px;">( <span  style="min-width:150px;">' . sv($row, 'equip_staff', '&nbsp;') . '</span> )</td>
                    </tr>
                    <tr>
                        <td style="text-align:right; padding-top:10px;"><b>Attending physician:</b></td>
                        <td style="text-align:left; padding-left:10px; padding-top:10px;">( <span  style="min-width:150px;">' . sv($row, 'attending_physician', '&nbsp;') . '</span> )</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>';

$mpdf->WriteHTML($html);
$mpdf->Output('Pre_Ane_Assess_AN_' . $an . '.pdf', 'I');
