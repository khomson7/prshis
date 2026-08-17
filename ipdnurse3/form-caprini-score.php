<?php
require_once '../include/Session.php';
require_once '../include/session-sso.php';
date_default_timezone_set('Asia/Bangkok');

// =====================================================
// ระบบ Single Sign-On (SSO) ข้าม Port/Server จัดการโดย session-sso.php
// =====================================================

$loginname = isset($_SESSION['loginname']) ? $_SESSION['loginname'] : '';
if (!$loginname) {
    $redirect = 'ipdnurse3/form-caprini-score.php';
    if (!empty($_REQUEST['an'])) {
        $redirect .= '?an=' . urlencode($_REQUEST['an']);
    }
    header('Location: ../login.php?redirect=' . urlencode($redirect));
    exit;
}

require_once '../mains/main-report.php';
$permissionCheck = Session::checkPermissionAndShowMessage('FORM_CAPRINI', 'VIEW');
$permissionCheckJson = json_encode($permissionCheck);

require_once '../mains/ipd-show-patient-main.php';
require_once '../mains/ipd-show-patient-sticky.php';
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';
require_once '../include/ReportQueryUtils.php';
require_once '../include/session-modal.php';

try {
    $conn = DbUtils::get_hosxp_connection();
    $an = $_REQUEST['an'];
    $hn = KphisQueryUtils::getHnByAn($an);
    $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : null;

    // Removed logic that automatically selects the latest ID if not provided,
    // to allow creating new records.
    


    Session::insertSystemAccessLog(json_encode([
        'form' => 'CAPRINI-FORM',
        'an' => $an,
    ], JSON_UNESCAPED_UNICODE));

    $caprini_row = null;
    if ($ids) {
        $stmt = $conn->prepare("SELECT * FROM prs_caprini_score WHERE an = :an AND id = :id");
        $stmt->execute(['an' => $an, 'id' => $ids]);
        $caprini_row = $stmt->fetch();
    }
    $default_age_range = '<41';
    $stmt_age = $conn->prepare("SELECT age_y FROM " . DbConstant::HOSXP_DBNAME . ".an_stat WHERE an = :an");
    $stmt_age->execute(['an' => $an]);
    $res_age = $stmt_age->fetch();
    if ($res_age) {
        $age = (int)$res_age['age_y'];
        if ($age < 41) {
            $default_age_range = '<41';
        } elseif ($age <= 60) {
            $default_age_range = '41-60';
        } elseif ($age <= 74) {
            $default_age_range = '61-74';
        } else {
            $default_age_range = '>=75';
        }
    }

} catch (Exception $e) {
    echo '<div class="alert alert-danger" style="margin:20px;">Database Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
}

function isChecked($field, $row)
{
    return (isset($row[$field]) && $row[$field] == 1) ? 'checked' : '';
}
function isRadioSelected($field, $value, $row, $default_val = '<41')
{
    if (!$row && $value === $default_val)
        return 'checked';
    return (isset($row[$field]) && $row[$field] === $value) ? 'checked' : '';
}
?>

<style>
    .caprini-table th,
    .caprini-table td {
        border: 1px solid #aaa;
        padding: 6px 8px;
        vertical-align: middle;
    }

    .caprini-table thead th {
        background-color: var(--bright-blue);
        color: #fff;
        text-align: center;
        font-weight: bold;
    }

    .caprini-table tbody tr:nth-child(even) {
        background-color: var(--bright-blue-light);
    }

    .caprini-table tbody tr:hover {
        background-color: #B3E5FC;
    }

    .caprini-table td.col-label {
        font-size: 0.88rem;
        padding: 5px 8px;
    }

    .caprini-table td label {
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        margin: 0;
        font-size: 0.88rem;
    }

    .caprini-table input[type="checkbox"],
    .caprini-table input[type="radio"] {
        cursor: pointer;
        width: 16px;
        height: 16px;
        accent-color: var(--bright-blue);
        flex-shrink: 0;
    }

    .score-group-header {
        background-color: var(--bright-blue) !important;
        color: #fff;
        font-weight: bold;
        text-align: center;
        font-size: 0.9rem;
        padding: 6px !important;
    }

    .total-score-box {
        font-size: 2rem;
        font-weight: bold;
        border-radius: 50%;
        width: 70px;
        height: 70px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        transition: color 0.3s, border-color 0.3s;
    }

    .score-color-green {
        color: #28a745;
        border: 3px solid #28a745;
    }

    .score-color-yellow {
        color: #856404;
        border: 3px solid #ffc107;
    }

    .score-color-orange {
        color: #7d3900;
        border: 3px solid #fd7e14;
    }

    .score-color-red {
        color: #721c24;
        border: 3px solid #dc3545;
    }

    .result-card {
        border: 2px solid;
        border-radius: 8px;
        padding: 14px 20px;
        transition: opacity 0.3s, transform 0.3s, box-shadow 0.3s;
    }

    .result-low {
        border-color: #28a745;
        background: #f0fff4;
        color: #155724;
    }

    .result-moderate {
        border-color: #ffc107;
        background: #fffbea;
        color: #856404;
    }

    .result-high {
        border-color: #fd7e14;
        background: #fff4e6;
        color: #7d3900;
    }

    .result-highest {
        border-color: #dc3545;
        background: #fff0f0;
        color: #721c24;
    }

    .nursing-box {
        border-radius: 8px;
        padding: 14px 18px;
        font-size: 0.9rem;
        line-height: 1.7;
        border-left: 5px solid;
    }

    .nursing-box ul {
        margin: 4px 0 0 0;
        padding-left: 18px;
    }

    .nursing-box li {
        margin-bottom: 4px;
    }

    .nursing-low {
        background: #f0fff4;
        border-color: #28a745;
        color: #155724;
    }

    .nursing-mod {
        background: #fffbea;
        border-color: #ffc107;
        color: #856404;
    }

    .nursing-high {
        background: #fff4e6;
        border-color: #fd7e14;
        color: #7d3900;
    }

    .nursing-highest {
        background: #fff0f0;
        border-color: #dc3545;
        color: #721c24;
    }

    .age-radio-group {
        display: flex;
        flex-wrap: wrap;
        gap: 10px 24px;
    }

    .age-radio-group label {
        display: flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        font-size: 0.9rem;
    }

    .age-radio-group input[type="radio"] {
        width: 16px;
        height: 16px;
        accent-color: var(--bright-blue);
        cursor: pointer;
    }
</style>

<script src="../node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
<link rel="stylesheet" href="../node_modules/sweetalert2/dist/sweetalert2.min.css">

<div id="formContainer">
    <form id="caprini_form">
        <input type="hidden" name="an" value="<?= htmlspecialchars($an) ?>">
        <input type="hidden" name="hn" value="<?= htmlspecialchars($hn) ?>">
        <input type="hidden" name="id" value="<?= htmlspecialchars($ids) ?>">

        <div class="container-fluid">
            <div class="row align-items-center mb-3">
                <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.close()">
                        <i class="fas fa-times"></i> ปิดหน้านี้
                    </button>
                </div>
                <div class="col">
                    <h5 class="mb-0"><b>แบบประเมินปัจจัยเสี่ยงต่อการเกิดภาวะหลอดเลือดดำอุดกั้น : Caprini Score</b></h5>
                </div>
            </div>

            <!-- Patient Info & Date -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label><b>วันที่ประเมิน</b></label>
                            <input type="date" name="assessment_date"
                                class="form-control form-control-sm d-inline-block w-auto" value="<?= isset($caprini_row['assessment_date'])
                                    ? date('Y-m-d', strtotime($caprini_row['assessment_date']))
                                    : date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-3">
                            <label><b>เวลาที่ประเมิน</b></label>
                            <input type="time" name="assessment_time"
                                class="form-control form-control-sm d-inline-block w-auto" value="<?= isset($caprini_row['assessment_time'])
                                    ? date('H:i', strtotime($caprini_row['assessment_time']))
                                    : date('H:i') ?>">
                        </div>
                    </div>
                    <hr>
                    <!-- Age Range -->
                    <div class="mb-2">
                        <b>ช่วงอายุ (เลือกได้ 1 ข้อ)</b>
                        <div class="age-radio-group mt-2">
                            <label>
                                <input type="radio" name="age_range" value="<41" data-score="0"
                                    <?= isRadioSelected('age_range', '<41', $caprini_row, $default_age_range) ?>>
                                อายุน้อยกว่า 41 ปี <small class="text-muted">(0 คะแนน)</small>
                            </label>
                            <label>
                                <input type="radio" name="age_range" value="41-60" data-score="1"
                                    <?= isRadioSelected('age_range', '41-60', $caprini_row, $default_age_range) ?>>
                                อายุ 41–60 ปี <small class="text-muted">(1 คะแนน)</small>
                            </label>
                            <label>
                                <input type="radio" name="age_range" value="61-74" data-score="2"
                                    <?= isRadioSelected('age_range', '61-74', $caprini_row, $default_age_range) ?>>
                                อายุ 61–74 ปี <small class="text-muted">(2 คะแนน)</small>
                            </label>
                            <label>
                                <input type="radio" name="age_range" value=">=75" data-score="3"
                                    <?= isRadioSelected('age_range', '>=75', $caprini_row, $default_age_range) ?>>
                                อายุ &ge; 75 ปี <small class="text-muted">(3 คะแนน)</small>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Risk Factor Table -->
            <div class="table-responsive">
                <table class="caprini-table w-100" style="font-size: 0.88rem;">
                    <thead>
                        <tr>
                            <th class="score-group-header" style="width:25%;">ข้อละ 1 คะแนน</th>
                            <th class="score-group-header" style="width:25%;">ข้อละ 2 คะแนน</th>
                            <th class="score-group-header" style="width:25%;">ข้อละ 3 คะแนน</th>
                            <th class="score-group-header" style="width:25%;">ข้อละ 5 คะแนน</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $col1 = [
                            ['minor_surgery', 'เข้ารับการผ่าตัดเล็ก'],
                            ['bmi_over_25', 'ดัชนีมวลกาย &gt;25 กก/ตร.ม.'],
                            ['swollen_legs', 'ขาบวม'],
                            ['varicose_veins', 'เส้นเลือดขอด'],
                            ['pregnancy_postpartum', 'หญิงตั้งครรภ์หรือหลังคลอด'],
                            ['history_unexplained_miscarriage', 'มีประวัติแท้งไม่ทราบสาเหตุหรือแท้งซ้ำ'],
                            ['oral_contraceptives_hrt', 'ใช้ยาคุมกำเนิด/ฮอร์โมนเสริม'],
                            ['sepsis_1_month', 'ภาวะติดเชื้อ (sepsis) ใน 1 เดือนที่ผ่านมา'],
                            ['severe_lung_disease', 'โรคปอดรุนแรงใน 1 เดือนที่ผ่านมา'],
                            ['abnormal_pulmonary_function', 'การทำงานของปอดผิดปกติ'],
                            ['ischemic_heart_disease', 'โรคหัวใจขาดเลือด'],
                            ['chf_1_month', 'ภาวะหัวใจวาย (congestive heart failure) ใน 1 เดือนที่ผ่านมา'],
                            ['inflammatory_bowel_disease', 'อักเสบของทางเดินอาหาร (inflammatory bowel disease)'],
                            ['bed_rest', 'ผู้ป่วยที่ต้องนอน (bed rest)'],
                        ];
                        $col2 = [
                            ['arthroscopic_surgery', 'ผ่าตัดข้อ (arthroscopic surgery)'],
                            ['major_surgery_over_45m', 'ผ่าตัดใหญ่ &gt;45 นาที'],
                            ['laparoscopic_surgery_over_45m', 'ผ่าตัดส่องกล้อง &gt;45 นาที'],
                            ['cancer_patient', 'ผู้ป่วยมะเร็ง'],
                            ['bedridden_over_72h', 'นอนติดเตียง &gt;72 ชั่วโมง'],
                            ['patient_in_cast', 'ผู้ป่วยเข้าเฝือก'],
                            ['central_venous_access', 'มีสายสวนหลอดเลือดดำส่วนกลาง'],
                        ];
                        $col3 = [
                            ['history_vte', 'เคยมีประวัติ VTE'],
                            ['family_history_vte', 'ประวัติครอบครัวเป็น VTE'],
                            ['factor_v_leiden', 'โรค factor V Leiden'],
                            ['prothrombin_20210a', 'พบ prothrombin 20210A'],
                            ['lupus_anticoagulant', 'พบ lupus anticoagulant'],
                            ['anticardiolipin_antibodies', 'พบ anticardiolipin antibodies'],
                            ['increased_homocysteine', 'การเพิ่มขึ้นของ homocysteine'],
                            ['heparin_induced_thrombocytopenia', 'ภาวะ heparin-induced thrombocytopenia'],
                            ['thrombophilia', 'เลือดแข็งตัวผิดปกติ (thrombophilia)'],
                        ];
                        $col4 = [
                            ['stroke_1_month', 'Stroke ใน 1 เดือนที่ผ่านมา'],
                            ['elective_major_lower_extremity_arthroplasty', 'ผ่าตัดใหญ่ข้อบริเวณขา (arthroplasty)'],
                            ['hip_pelvis_leg_fracture', 'กระดูกสะโพก เชิงกราน หรือขาหัก'],
                            ['acute_spinal_cord_injury_1_month', 'บาดเจ็บไขสันหลัง ใน 1 เดือนที่ผ่านมา'],
                        ];

                        $maxRows = max(count($col1), count($col2), count($col3), count($col4));
                        for ($i = 0; $i < $maxRows; $i++):
                            ?>
                            <tr>
                                <td class="col-label">
                                    <?php if (isset($col1[$i])): ?>
                                        <label>
                                            <input type="checkbox" name="<?= $col1[$i][0] ?>" data-score="1"
                                                <?= isChecked($col1[$i][0], $caprini_row) ?>>
                                            <?= $col1[$i][1] ?>
                                        </label>
                                    <?php endif; ?>
                                </td>
                                <td class="col-label">
                                    <?php if (isset($col2[$i])): ?>
                                        <label>
                                            <input type="checkbox" name="<?= $col2[$i][0] ?>" data-score="2"
                                                <?= isChecked($col2[$i][0], $caprini_row) ?>>
                                            <?= $col2[$i][1] ?>
                                        </label>
                                    <?php endif; ?>
                                </td>
                                <td class="col-label">
                                    <?php if (isset($col3[$i])): ?>
                                        <label>
                                            <input type="checkbox" name="<?= $col3[$i][0] ?>" data-score="3"
                                                <?= isChecked($col3[$i][0], $caprini_row) ?>>
                                            <?= $col3[$i][1] ?>
                                        </label>
                                    <?php endif; ?>
                                </td>
                                <td class="col-label">
                                    <?php if (isset($col4[$i])): ?>
                                        <label>
                                            <input type="checkbox" name="<?= $col4[$i][0] ?>" data-score="5"
                                                <?= isChecked($col4[$i][0], $caprini_row) ?>>
                                            <?= $col4[$i][1] ?>
                                        </label>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background:#E1F5FE;">
                            <td colspan="3" class="text-right font-weight-bold" style="padding:10px;">
                                <span style="font-size:1rem;">คะแนนรวม Caprini Score:</span>
                            </td>
                            <td class="text-center">
                                <div class="total-score-box" id="total_score_display">
                                    <?= isset($caprini_row['total_score']) ? $caprini_row['total_score'] : '0' ?>
                                </div>
                                <input type="hidden" name="total_score" id="total_score"
                                    value="<?= isset($caprini_row['total_score']) ? $caprini_row['total_score'] : 0 ?>">
                                <input type="hidden" name="risk_level" id="risk_level"
                                    value="<?= isset($caprini_row['risk_level']) ? htmlspecialchars($caprini_row['risk_level']) : 'Low' ?>">
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Score Interpretation -->
            <div class="card mt-3" id="score_result_card">
                <div class="card-header font-weight-bold" style="background:var(--bright-blue); color:#fff;">
                    <i class="fas fa-chart-bar"></i> การแปลผล Caprini Score
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <div class="result-card result-low">
                                <b>0 คะแนน</b><br>
                                <span>ความเสี่ยงต่ำมาก</span><br>
                                <small><i>Very Low Risk</i></small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <div class="result-card result-moderate">
                                <b>1–2 คะแนน</b><br>
                                <span>ความเสี่ยงต่ำ–ปานกลาง</span><br>
                                <small><i>Low–Moderate Risk</i></small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <div class="result-card result-high">
                                <b>3–4 คะแนน</b><br>
                                <span>ความเสี่ยงสูง</span><br>
                                <small><i>High Risk</i></small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <div class="result-card result-highest">
                                <b>&ge;5 คะแนน</b><br>
                                <span>ความเสี่ยงสูงมาก</span><br>
                                <small><i>Highest Risk</i></small>
                            </div>
                        </div>
                    </div>
                    <!-- Nursing Recommendation Box -->
                    <div class="mt-3" id="nursing_box"></div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="row mt-4 mb-5">
                <div class="col text-center">
                    <button type="submit" class="btn btn-primary btn-lg px-5 shadow">
                        <i class="fas fa-save"></i> บันทึกข้อมูล
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function updateTotal() {
        var total = 0;

        // Age radio
        var ageRadio = document.querySelector('input[name="age_range"]:checked');
        if (ageRadio) {
            total += parseInt(ageRadio.getAttribute('data-score') || 0);
        }

        // Checkboxes
        document.querySelectorAll('input[type="checkbox"]:checked').forEach(function (cb) {
            total += parseInt(cb.getAttribute('data-score') || 0);
        });

        document.getElementById('total_score_display').textContent = total;
        document.getElementById('total_score').value = total;

        showResult(total);
    }

    var nursingData = {
        green: {
            colorClass: 'score-color-green',
            cardIdx: 0, risk: 'Very Low',
            nursingClass: 'nursing-low',
            title: '<i class="fas fa-check-circle"></i> Caprini Score = 0 — ความเสี่ยงต่ำมาก (Very Low Risk)',
            items: [
                '1. Education : ให้ความรู้เกี่ยวกับภาวะหลอดเลือดดำอุดกั้นเพื่อใหผู้ป่วยร่วมมือในการรักษา ซึ่งประกอบด้วย ความหมาย ภาวะเลือดแข็งตัวภายในหลอดเลือดดำส่วนลึกที่ส่วนใดส่วนหนึ่งหรือหลายส่วน ' +
                '(Deep veinthromboembolism - DVT) รวมถึงลิ่มเลือดที่หลุดอุดหลอดเลือดดำในปอด หรือภาวะเส้นเลือดอุดกั้นในปอด (Pulmonary embolism - PE)<br> '
                + '<b><font color="blue"><u>สาเหตุ</u></font></b><br>'
                + '1) การหยุดนิ่งของเลือดดำ <br>'
                + '2) ผนังภายในหลอดเลือดดำได้รับอันตราย <br>'
                + '3) มีการเปลี่ยนแปลงปัจจัยในการแข็งตัวของเลือด <br>'
                + '<b><font color="red"><u>ปัจจัยเสี่ยง</u></font></b><br>'
                + '1) การเคลื่อนไหวร่างกายลดลง (decreased mobility)  <br>'
                + '2) การไหลเวียนเลือดการลดลงและโรคของหลอด เลือด เช่น มีประวัติของเส้นเลือดขอด  <br>'
                + '3) สูงอายุ โดยเฉพาะผู้ป่วยที่มีอายุมากกว่า 75 ปี <br>'
                + '4) อ้วน เป็นปัจจัยที่ทําให้เกิดโรคร่วมอื่นๆตามมา อีกทั้งยังอาจทําใหผู้ป่วยมีการเคลื่อนไหวลดลง <br>'
                + '5) ระยะของการดำเนินโรค เช่น มะเร็ง การให้ยาเคมีบําบัด โรคSLE โรคติดเชื้อเฉียบพลัน การรักษาด้วย ฮอร์โมนเอสโตรเจน ภาวะหัวใจวายและโรคปอดเรื้อรัง เป็นต้น <br>'
                + '6) การใส่สายสวนเข้าไปในหลอดเลือดดำส่วนกลาง ซึ่งส่งผลให้เกิดการบาดเจ็บของผนังหลอดเลือดดำ <br>'
                + '<b><font color="orange"><u>ภาวะแทรกซ้อน</u></font></b><br>'
                + 'ภาวะแทรกซ้อนที่รุนแรงของหลอดเลือดดำอุดกั้น คือ ลิ่มเลือดไปอุดที่หลอดเลือดดำของปอด (pulmonary embolism) ซึ่งทําใหผู้ป่วยเสียชีวิตได้ ภาวะดังกล่าวเกิดจาก การที่มีลิ่มเลือดบางส่วนถูกขับออกมาและลอยไปอยู่ใน ระบบ ไหลเวียนของ'
                + 'เลือดดำ (venous system) เมื่อลิ่มเลือด ไปอุดกั้นหลอดเลือดที่ปอดจึงเกิดการอุดตันขึ้น ภาวะแทรกซ่อนอื่นๆ ที่พบได้ เช่น '
                + 'Chronic venous insufficiency ซึ่งเป็นผลมาจากการถูกทําลายของลิ้นใน หลอดเลือดดำ ซึ่งเป็นสาเหตุของการหยุดนิ่งของเลือดดำ <br> '
                + '<b><font color="green"><u>การป้องกันภาวะหลอดเลือดดำอุดกั้น</u></font></b><br>'
                + '1. ส่งเสริมใหผู้ป่วยดื่มน้ำในปริมาณที่เพียงพอ อย่างน้อย 2 ลิตร/วัน ในระยะที่ไม่ได้จํากัดน้ำ เพราะการขาดน้ำ เป็นการเพิ่มความหนืดของเลือด (blood viscosity) ซึ่งเป็นปัจจัยส่งเสริมให้เลือดแข็งตัวเร็วขึ้น <br>'
                + '2. ดูแลใหผู้ป่วยยกขาสูงกว่าหัวใจ เพื่อเพิ่มการไหลกลับของเลือดดำ (venous return)<br>'
                + '3. สอนการบริหารการหายใจอย่างมีประสิทธิภาพ (deep breathing exercise) และแนะนําตามข้อ 2., 3. ดังต่อไปนี้  <br>',
                '2. Early ambulation: กระตุ้นใหผู้ป่วยลุกจากเตียงโดยเร็วที่สุดหากไม่มีข้อจํากัด เพื่อช่วยป้องกัน/ลดการหยุด นิ่ง และการคั่งของเลือดดำที่ขา (venous stasis & pooling)',
                '3. กระตุ้นใหผู้ป่วยบริหารเท้าและข้อเท้า (foot & ankle exercise) ในผู้ป่วยที่สามารถปฏิบัติได้เอง (passive exercise)สําหรับผู้ป่วยที่ไมสามารถปฏิบัติได้เองพยาบาล ควรบริหารให้ผู้ป่วย (active exercise) Foot & ankle Exercise 5 นาที3 รอบ/วัน (เช้า-กลางวัน-เย็น): <br>'
                + '3.1 กระดกข้อเท้าขึ้น-ลง ข้างละ 5 นาที จํานวน 15 ครั้ง/นาที (นับ 1-4) <br>'
                + '3.2 หมุนข้อเท้าเป็นวงกลม จํานวน 15 ครั้ง/นาที (นับ 1-4)'
            ]
        },
        yellow: {
            colorClass: 'score-color-yellow',
            cardIdx: 1, risk: 'Low-Moderate',
            nursingClass: 'nursing-mod',
            title: '<i class="fas fa-exclamation-circle"></i> Caprini Score 1–2 — ความเสี่ยงต่ำถึงปานกลาง (Low–Moderate Risk)',
            items: [
                '1. ดูแลให้การพยาบาลเหมือน Caprini score = 0 (Education, Early ambulation, Foot & ankle Exercise)',
                '2. ดูแล On Pneumatic pump (intermittent pneumatic compression device (IPCD)): เป็นวิธีป้องกันเชิงกล (mechanical prophylaxis) ที่มีหลักฐานยืนยันประสิทธิภาพมากที่สุด IPCD ใช้เพื่อรัดบริเวณน่องเพื่อป้องกันการเกิด venous stasis ผ่านทางการใช้ pneumatic sleeve <br>'
                + '<font color="red"><u>ข้อห้าม (contraindication) ของการใช้ IPCD ได้แก่</u></font></b><br>'
                + '- ภาวะบวมแบบกดไม่บุ๋มจากท่อน้ำเหลืองอุดตัน (non-pitting chronic lymphedema) <br>'
                + '- มีหรือสงสัยว่ามีภาวะ DVT หรือ PE <br>'
                + '- บริเวณที่ใส่มีหลอดเลือดดำอักเสบ (thrombophlebitis)<br>'
                + '- ผิวหนังอักเสบแบบเฉียบพลัน (acute inflammation of the skin)<br>'
                + '- ขาผิดรูปอย่างรุนแรง (extreme deformity of leg)<br>'
                + '- กระดูกหัก (fracture of lower leg)<br>'
                + '- มีแผล (wound at leg)<br>'
                + '- อวัยวะส่วนปลายขาดเลือด (ischemic vascular disease) <br>'
                + '- ภาวะ compartment syndrome  <br>'
                + '- มีการบวมที่ลำตัวหรือรยางค์ส่วนเหนือบริเวณที่จะใส่ (edema at the root of the extremity or truncal edema)  <br>'
                + '- โรคหัวใจล้มเหลวที่รุนแรง หรือยังควบคุมไม่ได้ (severe/uncontrolled cardiac failure)  <br>'
                + '- น้ำท่วมปอด (pulmonary edema)  <br>'
            ]
        },
        orange: {
            colorClass: 'score-color-orange',
            cardIdx: 2, risk: 'High',
            nursingClass: 'nursing-high',
            title: '<i class="fas fa-exclamation-triangle"></i> Caprini Score 3–4 — ความเสี่ยงสูง (High Risk)',
            items: [
                '1. ดูแลให้การพยาบาลเหมือน Caprini score = 0, 1-2 (Education, Early ambulation, Foot & ankle Exercise, on IPCD) ',
                '2. ดูแลให้ยา LMWH (low-molecular weight heparin ยากลุ่มเฮพารินที่มีน้ำหนักโมเลกุลต่ำ เช่น Enoxaparin) หรือ LDUH (low-dose unfractionated heparin'
                + 'ยา Heparin (เป็นสาร glycosaminoglycan ออกฤทธิ์ โดยการกระตุ้น antithrombin III (AT III) ไปยับยั้ง clotting factor)'
                + 'หรือIPCD ตามแผนการรักษาของแพทย์มีรายละเอียด ยาที่นิยมใช้ดังต่อไปนี้ <br>'
                + '2.1 ยา enoxaparin เป็นยาในกลุ่ม LMWH สังเคราะห์มาจากยา heparin ออกฤทธิ์เร่งปฏิกิริยาการยับยั้ง factor Xa '
                + 'มีระยะเวลาครึ่งชีพนานกว่า และให้ประสิทธิภาพดีกว่ากลุ่มเฮพาริน โดยใช้ในการป้องกันการเกิดลิ่มเลือดในหลอดเลือด ดำ '
                + 'รักษาภาวะกล้ามเนื้อหัวใจขาดเลือดชนิดนอนเอสที(non ST elevated) สามารถให้ยาในขนาดคงที่ วันละ 1-2 ครั้ง '
                + 'โดย ไม่ต้องติดตามระดับยาขนาดทั่วไปที่นิยมใช้คือ ยา enoxaparin ขนาด 1 มิลลิกรัมต่อน้ำหนักตัว (กิโลกรัม) วันละ 1 -2 ครั้ง ซึ่งยามีขนาด 20, 40, 60, 80, และ 100 มิลลิกรัม หรือ '
                + 'ยา fondaparinux ขนาด 2.5 มิลลิกรัม วันละ 1 ครั้ง </b>'
            ]
        },
        red: {
            colorClass: 'score-color-red',
            cardIdx: 3, risk: 'Highest',
            nursingClass: 'nursing-highest',
            title: '<i class="fas fa-times-circle"></i> Caprini Score &ge; 5 — ความเสี่ยงสูงมาก (Highest Risk)',
            items: [
                '1. ให้การพยาบาลเหมือน Caprini score = 0, 1-2, 3-4 (Education, Early ambulation, Foot & ankle Exercise, on IPCD, ดูแลให้ยา LMWH หรือ LDUH)',
                '2. ดูแลให้ยา LMWH หรือ LDUH ร่วมกับการใช้ IPCD และให้ LMWH เป็นเวลานาน 4 สัปดาห์ (ในผู้ป่วยที่เข้ารับการผ่าตัดมะเร็งในช่องท้องหรืออุ้งเชิงกราน) หลังออกจากโรงพยาบาลตามแผนการรักษาของแพทย์',
                '3. ดูแลให้ยา low-dose aspirin (160 มิลลิกรัม) (ในผู้ป่วยที่มีข้อห้ามของการใช้ LMWH และ LDUH) หรือใช้อุปกรณ์ IPCD หรือใช้ทั้งสองวิธีร่วมกันตามแผนการรักษาของแพทย์'
            ]
        }
    };

    function showResult(score) {
        var nursingBox = document.getElementById('nursing_box');
        var resultCards = document.querySelectorAll('.result-card');
        var scoreBox = document.getElementById('total_score_display');

        resultCards.forEach(function (c) {
            c.style.opacity = '0.4';
            c.style.transform = 'scale(0.97)';
            c.style.boxShadow = '';
        });

        var key = (score === 0) ? 'green'
            : (score <= 2) ? 'yellow'
                : (score <= 4) ? 'orange'
                    : 'red';
        var d = nursingData[key];

        // Update circle color
        scoreBox.className = 'total-score-box ' + d.colorClass;

        // Highlight active result card
        if (resultCards[d.cardIdx]) {
            resultCards[d.cardIdx].style.opacity = '1';
            resultCards[d.cardIdx].style.transform = 'scale(1.03)';
            resultCards[d.cardIdx].style.boxShadow = '0 0 10px rgba(0,0,0,0.18)';
        }

        // Render nursing recommendation
        var listItems = d.items.map(function (it) { return '<li>' + it + '</li>'; }).join('');
        nursingBox.innerHTML =
            '<div class="nursing-box ' + d.nursingClass + '">' +
            '  <b>' + d.title + '</b>' +
            '  <ul class="mt-2">' + listItems + '</ul>' +
            '</div>';

        document.getElementById('risk_level').value = d.risk;
    }

    $("#caprini_form").on("submit", function (e) {
        e.preventDefault();

        var formData = $(this).serialize();
        var id = $("input[name='id']").val();
        var url = id ? "form-caprini-score-update.php" : "form-caprini-score-save.php";

        $.ajax({
            url: url,
            type: "POST",
            data: formData,
            success: function (resp) {
                var data = (typeof resp === 'string') ? JSON.parse(resp) : resp;
                if (data.status === "success") {
                    if (data.id) {
                        $("input[name='id']").val(data.id);
                    }
                    Swal.fire("สำเร็จ", "บันทึกข้อมูลเรียบร้อยแล้ว", "success").then(function () {
                        if (window.opener && !window.opener.closed) {
                            window.opener.location.reload(true);
                        }
                        window.close();
                    });
                } else {
                    Swal.fire("ข้อผิดพลาด", data.message, "error");
                }
            },
            error: function () {
                Swal.fire("ข้อผิดพลาด", "ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้", "error");
            }
        });
    });

    window.onload = function () {
        updateTotal();
    };

    // Recalculate on any change
    document.getElementById('caprini_form').addEventListener('change', updateTotal);
</script>