<?php
date_default_timezone_set('Asia/Bangkok');
require_once '../include/Session.php';
if (session_status() === PHP_SESSION_NONE)
    session_start();
$loginname = isset($_SESSION['loginname']) ? $_SESSION['loginname'] : null;

require_once '../mains/main-report.php';
$permissionCheck = Session::checkPermissionAndShowMessage('ORS_NURSING_FOCUS', 'VIEW');
$permissionCheckJson = json_encode($permissionCheck);

require_once '../mains/ipd-show-patient-main.php';
require_once '../mains/ipd-show-patient-sticky.php';
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';
require_once '../include/ReportQueryUtils.php';
require_once '../include/session-modal.php';

try {
    $conn = DbUtils::get_hosxp_connection();
    $an = isset($_REQUEST['an']) ? trim($_REQUEST['an']) : '';
    $hn = KphisQueryUtils::getHnByAn($an);
    $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : null;

    // ไม่ auto-load — ต้องระบุ id จาก main page เสมอ (ถ้าไม่มี id = สร้างใหม่)

    Session::insertSystemAccessLog(json_encode([
        'form' => 'ORS-NURSING-FOCUS',
        'an' => $an,
    ], JSON_UNESCAPED_UNICODE));

    $rec = null;
    if ($ids) {
        $stmt = $conn->prepare("SELECT * FROM prs_ors_nursing_focus WHERE id = :id AND an = :an");
        $stmt->execute(['id' => $ids, 'an' => $an]);
        $rec = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    echo '<div class="alert alert-danger m-3">Database Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
}

// helper: ดึงค่าจาก record หรือ default
function v($rec, $key, $default = '')
{
    return isset($rec[$key]) && $rec[$key] !== null ? $rec[$key] : $default;
}
function chk($rec, $key)
{
    return isset($rec[$key]) && $rec[$key] == 1 ? 'checked' : '';
}
function sel($rec, $key, $val)
{
    return isset($rec[$key]) && $rec[$key] == $val ? 'selected' : '';
}
function rad($rec, $key, $val)
{
    return isset($rec[$key]) && $rec[$key] == $val ? 'checked' : '';
}

// คำนวณลักษณะแผลจาก boolean เดิม → radio value
$wound_condition = '';
if ($rec) {
    if ($rec['wound_dry']  == 1) $wound_condition = 'แผลแห้ง';
    elseif ($rec['wound_wet']  == 1) $wound_condition = 'แผลซึม';
    elseif ($rec['not_wound']  == 1) $wound_condition = 'ไม่มีแผล';
}

// FOCUS preset options
$focus_presets = [
    'มีโอกาสเกิดภาวะแทรกซ้อนหลังการให้ยาระดับความรู้สึกภายใน   24 - 48 ชั่วโมง'
];
$saved_focus     = v($rec, 'focus_text');
// ถ้าเป็น record ใหม่ (ไม่มี $rec) ให้ default = preset แรก
if ($saved_focus === '' && !$rec) $saved_focus = $focus_presets[0];
$focus_is_preset = in_array($saved_focus, $focus_presets);
$focus_custom    = (!$focus_is_preset && $saved_focus !== '') ? $saved_focus : '';
?>
<script src="../node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
<link rel="stylesheet" href="../node_modules/sweetalert2/dist/sweetalert2.min.css">

<style>
    .nf-section-title {
        background: #1a6b3a;
        color: #fff;
        font-weight: bold;
        padding: 6px 12px;
        border-radius: 4px 4px 0 0;
        font-size: 0.92rem;
        letter-spacing: .3px;
    }

    .nf-card {
        border: 1px solid #c8e6c9;
        border-radius: 4px;
        margin-bottom: 14px;
    }

    .nf-card .nf-body {
        padding: 12px 14px;
    }

    .fluid-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.85rem;
    }

    .fluid-table th,
    .fluid-table td {
        border: 1px solid #adb5bd;
        padding: 5px 8px;
        vertical-align: middle;
    }

    .fluid-table thead th {
        background: #1a6b3a;
        color: #fff;
        text-align: center;
        font-weight: bold;
    }

    .fluid-table .sub-hdr {
        background: #e8f5e9;
        font-weight: bold;
        font-size: 0.8rem;
        color: #1a6b3a;
        text-align: center;
    }

    .fluid-table .row-label {
        font-weight: 500;
        width: 130px;
    }

    .fluid-table input[type="number"] {
        width: 100%;
        min-width: 80px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 3px 6px;
        text-align: right;
        font-size: 0.85rem;
        background: #fff;
        box-sizing: border-box;
    }

    .fluid-table input[type="number"]:focus {
        background: #fff9c4;
        border-color: #1a6b3a;
        outline: none;
        box-shadow: 0 0 0 2px rgba(26, 107, 58, 0.15);
    }

    .fluid-table td:nth-child(3),
    .fluid-table td:nth-child(4) {
        padding: 4px 6px;
    }

    .score-input {
        width: 70px;
        text-align: center;
        font-size: 1rem;
        font-weight: bold;
        border: 2px solid #1a6b3a;
        border-radius: 5px;
        padding: 2px 6px;
    }

    .score-box {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .chk-group label {
        margin-right: 18px;
        font-size: 0.9rem;
        cursor: pointer;
    }

    .chk-group input {
        margin-right: 4px;
        cursor: pointer;
    }

    .form-label-sm {
        font-size: 0.85rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 3px;
    }

    .is-invalid {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.15rem rgba(220, 53, 69, .2) !important;
    }

    /* FOCUS preset options */
    .focus-option label {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        padding: 7px 10px;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        cursor: pointer;
        font-size: 0.88rem;
        background: #fff;
        transition: .15s;
        margin-bottom: 5px;
    }

    .focus-option label:hover {
        background: #f0faf4;
        border-color: #1a6b3a;
    }

    .focus-option input[type="radio"]:checked+span {
        color: #1a6b3a;
        font-weight: 600;
    }

    .focus-option label:has(input:checked) {
        background: #f0faf4;
        border-color: #1a6b3a;
    }
</style>

<div id="formContainer">
    <form id="ors_nursing_form">
        <input type="hidden" name="an" value="<?= htmlspecialchars($an) ?>">
        <input type="hidden" name="id" id="rec_id" value="<?= htmlspecialchars($ids ?? '') ?>">

        <div class="container-fluid">

            <!-- ===== Top bar ===== -->
            <div class="row align-items-center mb-3">
                <div class="col-auto">
                    <a href="nursing-focus-main.php?an=<?= urlencode($an) ?>&loginname=<?= urlencode($loginname) ?>"
                        class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> กลับหน้าหลัก
                    </a>
                </div>
                <div class="col">
                    <h5 class="mb-0">
                        <b>NURSING FOCUS CHARTHING</b>
                        <small class="text-muted ml-2" style="font-size:0.8rem;">FM-NSO-ANE-006-07</small>
                        <?php if ($ids): ?>
                            <span class="badge badge-secondary ml-1" style="font-size:0.72rem;">ID: <?= $ids ?></span>
                        <?php else: ?>
                            <span class="badge badge-success ml-1" style="font-size:0.72rem;">รายการใหม่</span>
                        <?php endif; ?>
                    </h5>
                </div>
                <div class="col-auto">
                    <?php if ($ids): ?>
                        <a href="../pdffile/ors-nursing-focus-pdf.php?an=<?= urlencode($an) ?>&id=<?= $ids ?>&loginname=<?= urlencode($loginname) ?>"
                            target="_blank" class="btn btn-sm btn-info px-3 shadow-sm">
                            <i class="fas fa-file-pdf"></i> พิมพ์ PDF
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ===== DATE / SHIFT / TIME ===== -->
            <div class="nf-card">
                <div class="nf-section-title"><i class="fas fa-calendar-alt mr-1"></i> DATE / SHIFT / TIME</div>
                <div class="nf-body">
                    <!-- แถว 1: วันเวลาผ่าตัด -->
                    <div class="row mb-1">
                        <div class="col-12">
                            <small class="text-muted" style="font-size:0.78rem;">
                                <i class="fas fa-scalpel mr-1"></i><b>วันเวลาผ่าตัด</b>
                            </small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="form-label-sm">วันที่ผ่าตัด (DATE) <span class="text-danger">*</span></div>
                            <input type="date" name="visit_date" class="form-control form-control-sm"
                                value="<?= v($rec, 'visit_date', date('Y-m-d')) ?>">
                        </div>
                        <div class="col-md-3">
                            <div class="form-label-sm">กะ (SHIFT) <span class="text-danger">*</span></div>
                            <select name="shift" class="form-control form-control-sm">
                                <option value="">-- เลือกกะ --</option>
                                <option value="เช้า" <?= sel($rec, 'shift', 'เช้า') ?>>เช้า (08:00-16:00)</option>
                                <option value="บ่าย" <?= sel($rec, 'shift', 'บ่าย') ?>>บ่าย (16:00-00:00)</option>
                                <option value="ดึก" <?= sel($rec, 'shift', 'ดึก') ?>>ดึก (00:00-08:00)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="form-label-sm">เวลาผ่าตัด (TIME) <span class="text-danger">*</span></div>
                            <input type="time" name="visit_time" class="form-control form-control-sm"
                                value="<?= v($rec, 'visit_time', date('H:i')) ?>">
                        </div>
                    </div>
                    <!-- เส้นคั่น -->
                    <hr class="my-2">
                    <!-- แถว 2: วันเวลาเยี่ยมผู้ป่วย -->
                    <div class="row mb-1">
                        <div class="col-12">
                            <small class="text-muted" style="font-size:0.78rem;">
                                <i class="fas fa-user-nurse mr-1"></i><b>วันเวลาเยี่ยมผู้ป่วย</b>
                            </small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-label-sm">วันที่เยี่ยมผู้ป่วย</div>
                            <input type="date" name="patient_visit_date" class="form-control form-control-sm"
                                value="<?= v($rec, 'patient_visit_date') ?>">
                        </div>
                        <div class="col-md-3">
                            <div class="form-label-sm">เวลาเยี่ยมผู้ป่วย</div>
                            <input type="time" name="patient_visit_time" class="form-control form-control-sm"
                                value="<?= v($rec, 'patient_visit_time') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== FOCUS ===== -->
            <div class="nf-card">
                <div class="nf-section-title"><i class="fas fa-crosshairs mr-1"></i> FOCUS</div>
                <div class="nf-body">
                    <div class="form-label-sm mb-1">ข้อความ FOCUS ที่แสดงในตาราง PDF</div>
                    <input type="text" name="focus_text" class="form-control form-control-sm"
                           readonly
                           value="<?= htmlspecialchars($saved_focus) ?>"
                           style="background:#f8f9fa; color:#333; cursor:default;"
                           title="<?= htmlspecialchars($saved_focus) ?>">
                </div>
            </div>

            <!-- ===== Anesthetic Technique ===== -->
            <div class="nf-card">
                <div class="nf-section-title"><i class="fas fa-syringe mr-1"></i> A: Anesthetic Technique</div>
                <div class="nf-body">
                    <div class="chk-group mb-2">
                        <label><input type="checkbox" name="anes_ga" value="1" <?= chk($rec, 'anes_ga') ?>> GA</label>
                        <label><input type="checkbox" name="anes_tiva" value="1" <?= chk($rec, 'anes_tiva') ?>>
                            TIVA</label>
                        <label><input type="checkbox" name="anes_ra" value="1" <?= chk($rec, 'anes_ra') ?>> RA</label>
                        <label><input type="checkbox" name="anes_mac" value="1" <?= chk($rec, 'anes_mac') ?>> MAC</label>
                        <label>
                            <input type="checkbox" name="anes_other_chk" id="anes_other_chk" value="1"
                                <?= (v($rec, 'anes_other') !== '') ? 'checked' : '' ?>
                                onchange="toggleOther(this,'anes_other_text')">
                            Combine:
                        </label>
                        <input type="text" name="anes_other" id="anes_other_text"
                            class="form-control form-control-sm d-inline-block"
                            style="width:180px; <?= (v($rec, 'anes_other') !== '') ? '' : 'display:none!important' ?>"
                            value="<?= htmlspecialchars(v($rec, 'anes_other')) ?>" placeholder="ระบุ...">
                    </div>
                </div>
            </div>

            <!-- ===== Post Operation ===== -->
            <div class="nf-card">
                <div class="nf-section-title"><i class="fas fa-procedures mr-1"></i> Post Operation</div>
                <div class="nf-body">

                    <div class="row mt-2">
                        <div class="col-12">
                            <div class="form-label-sm">ข้อความเพิ่มเติม Post Operation</div>
                            <textarea name="post_op_note" class="form-control form-control-sm" rows="2"
                                placeholder="เช่น อาการแผล, Drain, อื่นๆ..."><?= htmlspecialchars(v($rec, 'post_op_note')) ?></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-label-sm">ตำแหน่งแผล</div>
                            <div class="chk-group">
                                <label><input type="checkbox" name="wound_right" value="1" <?= chk($rec, 'wound_right') ?>> ขวา</label>
                                <label><input type="checkbox" name="wound_left" value="1" <?= chk($rec, 'wound_left') ?>>
                                    ซ้าย</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-label-sm">ลักษณะแผล <small class="text-muted">(เลือก 1)</small></div>
                            <div class="chk-group">
                                <label>
                                    <input type="radio" name="wound_condition" value="แผลแห้ง"
                                        <?= ($wound_condition === 'แผลแห้ง') ? 'checked' : '' ?>>
                                    แผลแห้ง
                                </label>
                                <label>
                                    <input type="radio" name="wound_condition" value="แผลซึม"
                                        <?= ($wound_condition === 'แผลซึม') ? 'checked' : '' ?>>
                                    แผลซึม
                                </label>
                                <label>
                                    <input type="radio" name="wound_condition" value="ไม่มีแผล"
                                        <?= ($wound_condition === 'ไม่มีแผล') ? 'checked' : '' ?>>
                                    ไม่มีแผล
                                </label>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ===== Summary Fluid ===== -->
            <div class="nf-card">
                <div class="nf-section-title"><i class="fas fa-tint mr-1"></i> Summary Fluid</div>
                <div class="nf-body p-0">
                    <div class="table-responsive">
                        <table class="fluid-table">
                            <thead>
                                <tr>
                                    <th colspan="2" style="width:180px;">Summary Fluid</th>
                                    <th>Intra-op (mL)</th>
                                    <th>PACU (mL)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- INTAKE -->
                                <tr>
                                    <td rowspan="5" class="sub-hdr"
                                        style="width:30px; writing-mode:vertical-rl; transform:rotate(180deg);">
                                        Intake
                                    </td>
                                    <td class="row-label">Crystalloid</td>
                                    <td><input type="number" name="in_crystalloid_io" step="0.1" min="0" placeholder="0"
                                            value="<?= v($rec, 'in_crystalloid_io') ?>"></td>
                                    <td><input type="number" name="in_crystalloid_pacu" step="0.1" min="0"
                                            placeholder="0" value="<?= v($rec, 'in_crystalloid_pacu') ?>"></td>
                                </tr>
                                <tr>
                                    <td class="row-label">Colloid</td>
                                    <td><input type="number" name="in_colloid_io" step="0.1" min="0" placeholder="0"
                                            value="<?= v($rec, 'in_colloid_io') ?>"></td>
                                    <td><input type="number" name="in_colloid_pacu" step="0.1" min="0" placeholder="0"
                                            value="<?= v($rec, 'in_colloid_pacu') ?>"></td>
                                </tr>
                                <tr>
                                    <td class="row-label">PRC</td>
                                    <td><input type="number" name="in_prc_io" step="0.1" min="0" placeholder="0"
                                            value="<?= v($rec, 'in_prc_io') ?>"></td>
                                    <td><input type="number" name="in_prc_pacu" step="0.1" min="0" placeholder="0"
                                            value="<?= v($rec, 'in_prc_pacu') ?>"></td>
                                </tr>
                                <tr>
                                    <td class="row-label">FFP</td>
                                    <td><input type="number" name="in_ffp_io" step="0.1" min="0" placeholder="0"
                                            value="<?= v($rec, 'in_ffp_io') ?>"></td>
                                    <td><input type="number" name="in_ffp_pacu" step="0.1" min="0" placeholder="0"
                                            value="<?= v($rec, 'in_ffp_pacu') ?>"></td>
                                </tr>
                                <tr>
                                    <td class="row-label">Other</td>
                                    <td><input type="number" name="in_other_io" step="0.1" min="0" placeholder="0"
                                            value="<?= v($rec, 'in_other_io') ?>"></td>
                                    <td><input type="number" name="in_other_pacu" step="0.1" min="0" placeholder="0"
                                            value="<?= v($rec, 'in_other_pacu') ?>"></td>
                                </tr>
                                <!-- OUTPUT -->
                                <tr>
                                    <td rowspan="4" class="sub-hdr"
                                        style="writing-mode:vertical-rl; transform:rotate(180deg);">
                                        Output
                                    </td>
                                    <td class="row-label">Blood loss</td>
                                    <td><input type="number" name="out_bloodloss_io" step="0.1" min="0" placeholder="0"
                                            value="<?= v($rec, 'out_bloodloss_io') ?>"></td>
                                    <td><input type="number" name="out_bloodloss_pacu" step="0.1" min="0"
                                            placeholder="0" value="<?= v($rec, 'out_bloodloss_pacu') ?>"></td>
                                </tr>
                                <tr>
                                    <td class="row-label">Drain</td>
                                    <td><input type="number" name="out_drain_io" step="0.1" min="0" placeholder="0"
                                            value="<?= v($rec, 'out_drain_io') ?>"></td>
                                    <td><input type="number" name="out_drain_pacu" step="0.1" min="0" placeholder="0"
                                            value="<?= v($rec, 'out_drain_pacu') ?>"></td>
                                </tr>
                                <tr>
                                    <td class="row-label">Urine</td>
                                    <td><input type="number" name="out_urine_io" step="0.1" min="0" placeholder="0"
                                            value="<?= v($rec, 'out_urine_io') ?>"></td>
                                    <td><input type="number" name="out_urine_pacu" step="0.1" min="0" placeholder="0"
                                            value="<?= v($rec, 'out_urine_pacu') ?>"></td>
                                </tr>
                                <tr>
                                    <td class="row-label">สิ่งสาระ</td>
                                    <td><input type="number" name="out_other_io" step="0.1" min="0" placeholder="0"
                                            value="<?= v($rec, 'out_other_io') ?>"></td>
                                    <td><input type="number" name="out_other_pacu" step="0.1" min="0" placeholder="0"
                                            value="<?= v($rec, 'out_other_pacu') ?>"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ===== Scores ===== -->
            <div class="nf-card">
                <div class="nf-section-title"><i class="fas fa-clipboard-check mr-1"></i> การประเมิน</div>
                <div class="nf-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="form-label-sm">Modified's Aldrete Scoring System</div>
                            <div class="score-box">
                                <input type="number" name="aldrete_score" class="score-input" min="0" max="10"
                                    value="<?= v($rec, 'aldrete_score') ?>" placeholder="0-10">
                                <span class="text-muted small">คะแนน</span>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-label-sm">Pain Score</div>
                            <div class="score-box">
                                <input type="number" name="pain_score" class="score-input" min="0" max="10"
                                    value="<?= v($rec, 'pain_score') ?>" placeholder="0-10">
                                <span class="text-muted small">คะแนน</span>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-label-sm">Sedation Score</div>
                            <div class="score-box">
                                <input type="number" name="sedation_score" class="score-input" min="0" max="5"
                                    value="<?= v($rec, 'sedation_score') ?>" placeholder="0-5">
                                <span class="text-muted small">คะแนน</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== Respiratory Status ===== -->
            <div class="nf-card">
                <div class="nf-section-title"><i class="fas fa-lungs mr-1"></i> Respiratory Status</div>
                <div class="nf-body">
                    <div class="chk-group d-flex align-items-center flex-wrap" style="gap:10px;">
                        <label>
                            <input type="checkbox" name="resp_room_air" value="1" <?= chk($rec, 'resp_room_air') ?>>
                            Room air
                        </label>
                        <label class="d-flex align-items-center gap-2 mb-0">
                            <input type="checkbox" name="resp_o2_chk" id="resp_o2_chk" value="1"
                                <?= (v($rec, 'resp_o2_with') !== '') ? 'checked' : '' ?>
                                onchange="toggleOther(this,'resp_o2_text')">
                            O<sub>2</sub> with
                        </label>
                        <input type="text" name="resp_o2_with" id="resp_o2_text"
                            class="form-control form-control-sm d-inline-block"
                            style="width:200px; <?= (v($rec, 'resp_o2_with') !== '') ? '' : 'display:none!important' ?>"
                            value="<?= htmlspecialchars(v($rec, 'resp_o2_with')) ?>"
                            placeholder="ระบุ L/min, mask type...">
                    </div>
                </div>
            </div>

            <!-- ===== Discharge ===== -->
            <div class="nf-card">
                <div class="nf-section-title"><i class="fas fa-ambulance mr-1"></i> การเคลื่อนย้ายผู้ป่วย</div>
                <div class="nf-body">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <div class="form-label-sm">เคลื่อนย้ายผู้ป่วยกลับ</div>
                            <div class="chk-group">
                                <label><input type="radio" name="discharge_to" value="หอผู้ป่วย"
                                        <?= rad($rec, 'discharge_to', 'หอผู้ป่วย') ?>> หอผู้ป่วย</label>
                                <label><input type="radio" name="discharge_to" value="ICU"
                                        <?= rad($rec, 'discharge_to', 'ICU') ?>> ICU</label>
                                <label><input type="radio" name="discharge_to" value="ห้องสังเกตอาการ"
                                        <?= rad($rec, 'discharge_to', 'ห้องสังเกตอาการ') ?>> ห้องสังเกตอาการ</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="form-label-sm">เคลื่อนย้ายโดย</div>
                            <div class="chk-group">
                                <label><input type="radio" name="transfer_by" value="รถนอน"
                                        <?= rad($rec, 'transfer_by', 'รถนอน') ?>> รถนอน</label>
                                <label><input type="radio" name="transfer_by" value="รถนั่ง"
                                        <?= rad($rec, 'transfer_by', 'รถนั่ง') ?>> รถนั่ง</label>
                            </div>
                        </div>
                    </div>
                   
                </div>
            </div>


                 <!-- ===== E: Complications ===== -->
                 <div class="nf-card">
                <div class="nf-section-title"><i class="fas fa-exclamation-triangle mr-1"></i> I: ประเมินผู้ป่วยหลังการให้ยาระยะ ระดับความรู้สึกภายใน 24-48 ชั่วโมง</div>
                <div class="nf-body">
                   
                <div class="row mt-2">
                        <div class="col-12">
                          
                            <textarea name="assess_note" class="form-control form-control-sm" rows="2"
                                placeholder="บันทึกการประเมิน..."><?= htmlspecialchars(v($rec, 'assess_note')) ?></textarea>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ===== E: Complications ===== -->
            <div class="nf-card">
                <div class="nf-section-title"><i class="fas fa-exclamation-triangle mr-1"></i> E: ภาวะแทรกซ้อน</div>
                <div class="nf-body">
                    <div class="mb-2">
                        <label class="d-flex align-items-center" style="gap:8px; cursor:pointer;">
                            <input type="checkbox" name="no_complication" value="1" id="chk_no_comp"
                                <?= chk($rec, 'no_complication') ?>
                                onchange="if(this.checked){ document.getElementById('chk_has_comp').checked=false; document.getElementById('comp_detail_wrap').style.display='none'; }">
                            <span>ไม่มีภาวะแทรกซ้อนหลังให้ยาระดับความรู้สึก</span>
                        </label>
                    </div>
                    <div class="mb-2">
                        <label class="d-flex align-items-center" style="gap:8px; cursor:pointer;">
                            <input type="checkbox" name="has_complication" value="1" id="chk_has_comp"
                                <?= chk($rec, 'has_complication') ?> onchange="if(this.checked){ document.getElementById('chk_no_comp').checked=false; document.getElementById('comp_detail_wrap').style.display=''; }
                                         else { document.getElementById('comp_detail_wrap').style.display='none'; }">
                            <span>มีภาวะแทรกซ้อนหลังให้ยาระดับความรู้สึก คือ</span>
                        </label>
                    </div>
                    <div id="comp_detail_wrap" style="<?= v($rec, 'has_complication', 0) ? '' : 'display:none;' ?>">
                        <textarea name="complication_detail" class="form-control form-control-sm" rows="2"
                            placeholder="ระบุภาวะแทรกซ้อน..."><?= htmlspecialchars(v($rec, 'complication_detail')) ?></textarea>
                    </div>
                </div>
            </div>

            <!-- ===== Remark ===== -->
            <div class="nf-card">
                <div class="nf-section-title"><i class="fas fa-sticky-note mr-1"></i> REMARK</div>
                <div class="nf-body">
                    <textarea name="remark" class="form-control form-control-sm" rows="3"
                        placeholder="บันทึกเพิ่มเติม..."><?= htmlspecialchars(v($rec, 'remark')) ?></textarea>
                </div>
            </div>

            <!-- ===== Sign / ผู้บันทึก ===== -->
            <?php
            // ผู้บันทึก (creator) — อิงจาก created_by
            $creator_name = v($rec, 'created_name',
                isset($_SESSION['name']) ? $_SESSION['name'] : $loginname);
            $creator_pos  = v($rec, 'created_position',
                isset($_SESSION['entryposition']) ? $_SESSION['entryposition'] : '');

            // Visit Nurse (ผู้เยี่ยม) — กด SIGN เพื่อบันทึก
            $visit_nurse_val = v($rec, 'visit_nurse');
            $visit_pos_val   = v($rec, 'nurse_position');

            // ค่า session สำหรับ auto-fill ปุ่ม SIGN
            $session_name = isset($_SESSION['name'])          ? $_SESSION['name']          : $loginname;
            $session_pos  = isset($_SESSION['entryposition']) ? $_SESSION['entryposition'] : '';
            ?>
            <div class="nf-card">
                <div class="nf-section-title"><i class="fas fa-pen-nib mr-1"></i> ลายเซ็น / ผู้บันทึก</div>
                <div class="nf-body">

                    <!-- ผู้บันทึก (readonly) -->
                    <div class="form-label-sm mb-1">ผู้บันทึก</div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control form-control-sm" readonly
                                   value="<?= htmlspecialchars($creator_name) ?>"
                                   style="background:#f8f9fa; color:#333;"
                                   placeholder="ชื่อผู้บันทึก">
                            <input type="hidden" name="created_name"     value="<?= htmlspecialchars($creator_name) ?>">
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control form-control-sm" readonly
                                   value="<?= htmlspecialchars($creator_pos) ?>"
                                   style="background:#f8f9fa; color:#333;"
                                   placeholder="ตำแหน่ง">
                            <input type="hidden" name="created_position"  value="<?= htmlspecialchars($creator_pos) ?>">
                        </div>
                        <div class="col-md-4 d-flex align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-info-circle mr-1"></i>
                                บันทึกโดย: <b><?= htmlspecialchars($loginname) ?></b>
                            </small>
                        </div>
                    </div>

                    <hr class="my-2">

                    <!-- Visit Nurse (กด SIGN) -->
                    <div class="form-label-sm mb-1">Visit Nurse <small class="text-muted">(กดปุ่ม SIGN เพื่อลงชื่อเยี่ยม)</small></div>
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <label class="form-label-sm mb-1">ชื่อผู้เยี่ยม</label>
                            <input type="text" name="visit_nurse" id="visit_nurse"
                                   class="form-control form-control-sm"
                                   value="<?= htmlspecialchars($visit_nurse_val) ?>"
                                   placeholder="ชื่อ Visit Nurse">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-sm mb-1">ตำแหน่ง</label>
                            <input type="text" name="nurse_position" id="nurse_position"
                                   class="form-control form-control-sm"
                                   value="<?= htmlspecialchars($visit_pos_val) ?>"
                                   placeholder="ตำแหน่ง">
                        </div>
                        <div class="col-md-auto">
                            <button type="button" class="btn btn-success btn-sm px-3"
                                    onclick="signVisit()"
                                    title="ลงชื่อผู้เยี่ยมอัตโนมัติจาก session ปัจจุบัน">
                                <i class="fas fa-signature mr-1"></i> SIGN
                            </button>
                            <?php if ($visit_nurse_val): ?>
                            <button type="button" class="btn btn-outline-secondary btn-sm ml-1"
                                    onclick="clearSign()" title="ล้างลายเซ็น">
                                <i class="fas fa-times"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($visit_nurse_val): ?>
                    <div class="mt-2">
                        <span class="badge badge-success px-2 py-1" style="font-size:0.78rem;">
                            <i class="fas fa-check-circle mr-1"></i>
                            ลงชื่อแล้ว: <?= htmlspecialchars($visit_nurse_val) ?>
                            <?= $visit_pos_val ? '(' . htmlspecialchars($visit_pos_val) . ')' : '' ?>
                        </span>
                    </div>
                    <?php endif; ?>

                </div>
            </div>

            <!-- ===== Save Button ===== -->
            <div class="row mb-5">
                <div class="col text-center">
                    <?php if (Session::checkPermission('ORS_NURSING_FOCUS', 'EDIT') && ReportQueryUtils::checkReadOnly($an)): ?>
                        <button type="submit" class="btn btn-primary btn-lg px-5 shadow">
                            <i class="fas fa-save"></i> บันทึกข้อมูล
                        </button>
                    <?php endif; ?>
                </div>
            </div>

        </div><!-- /container-fluid -->
    </form>
</div><!-- /formContainer -->

<script>
    function toggleOther(chk, targetId) {
        var el = document.getElementById(targetId);
        if (!el) return;
        el.style.display = chk.checked ? '' : 'none';
        if (!chk.checked) el.value = '';
    }

    // ---- SIGN handler ----
    var _sessionName = <?= json_encode($session_name) ?>;
    var _sessionPos  = <?= json_encode($session_pos) ?>;

    function signVisit() {
        var nameEl = document.getElementById('visit_nurse');
        var posEl  = document.getElementById('nurse_position');
        // ถ้ายังว่างอยู่ → auto-fill จาก session; ถ้ามีอยู่แล้ว → ไม่ทับ
        if (!nameEl.value.trim()) nameEl.value = _sessionName;
        if (!posEl.value.trim())  posEl.value  = _sessionPos;
        Swal.fire({
            toast: true, position: 'top-end', icon: 'success',
            title: 'ลงชื่อเยี่ยมแล้ว — ' + nameEl.value,
            showConfirmButton: false, timer: 2000
        });
    }

    function clearSign() {
        Swal.fire({
            title: 'ล้างลายเซ็นผู้เยี่ยม?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'ล้าง',
            cancelButtonText: 'ยกเลิก'
        }).then(function(r) {
            if (r.isConfirmed) {
                document.getElementById('visit_nurse').value    = '';
                document.getElementById('nurse_position').value = '';
            }
        });
    }

    // ---- FOCUS choice handler ----
    function onFocusChoice(radio) {
        var wrap = document.getElementById('focus_custom_wrap');
        var input = document.getElementById('focus_custom_input');
        var hidden = document.getElementById('focus_text');
        if (radio.value === '__other__') {
            wrap.style.display = '';
            input.focus();
            hidden.value = input.value;
        } else {
            wrap.style.display = 'none';
            hidden.value = radio.value;
        }
    }

    // sync custom text → hidden field on input
    document.addEventListener('DOMContentLoaded', function() {
        var customInput = document.getElementById('focus_custom_input');
        var hidden = document.getElementById('focus_text');
        if (customInput) {
            customInput.addEventListener('input', function() {
                hidden.value = this.value;
            });
        }
    });

    // ---- Validation ----
    function validateForm() {
        var errors = [];

        var visitDate = $('[name="visit_date"]').val();
        if (!visitDate) errors.push('• วันที่ (DATE) ยังไม่ได้กรอก');

        var shift = $('[name="shift"]').val();
        if (!shift) errors.push('• กะ (SHIFT) ยังไม่ได้เลือก');

        var visitTime = $('[name="visit_time"]').val();
        if (!visitTime) errors.push('• เวลา (TIME) ยังไม่ได้กรอก');

        if (errors.length > 0) {
            Swal.fire({
                title: 'กรุณากรอกข้อมูลให้ครบถ้วน',
                html: errors.join('<br>'),
                icon: 'warning',
                confirmButtonText: 'ตกลง',
                confirmButtonColor: '#1a6b3a'
            });
            // Highlight empty fields
            if (!visitDate) $('[name="visit_date"]').addClass('is-invalid').focus();
            if (!shift) $('[name="shift"]').addClass('is-invalid');
            if (!visitTime) $('[name="visit_time"]').addClass('is-invalid');
            return false;
        }
        // Clear invalid state
        $('[name="visit_date"], [name="shift"], [name="visit_time"]').removeClass('is-invalid');
        return true;
    }

    // Clear invalid highlight on change
    $('[name="visit_date"], [name="shift"], [name="visit_time"]').on('change input', function() {
        $(this).removeClass('is-invalid');
    });

    $("#ors_nursing_form").on("submit", function(e) {
        e.preventDefault();

        if (!validateForm()) return;

        var formData = $(this).serialize();
        var isNew = !$("#rec_id").val(); // true = บันทึกครั้งแรก
        var url = isNew ? "nursing-focus-save.php" : "nursing-focus-update.php";
        var an = '<?= addslashes($an) ?>';
        var loginname = '<?= addslashes($loginname) ?>';

        Swal.fire({
            title: 'กำลังบันทึก...',
            allowOutsideClick: false,
            didOpen: function() {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: url,
            type: "POST",
            data: formData,
            success: function(resp) {
                try {
                    var data = (typeof resp === 'string') ? JSON.parse(resp) : resp;
                    if (data.status === "success") {
                        if (isNew) {
                            // บันทึกครั้งแรก → กลับหน้าหลัก พร้อม highlight record ใหม่
                            Swal.fire({
                                title: 'บันทึกสำเร็จ',
                                text: 'บันทึกข้อมูลเรียบร้อยแล้ว',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(function() {
                                window.location.href = 'nursing-focus-main.php?an=' +
                                    encodeURIComponent(an) +
                                    '&loginname=' + encodeURIComponent(loginname) +
                                    '&new_id=' + (data.id || '');
                            });
                        } else {
                            // แก้ไข → reload หน้าฟอร์มเดิม
                            Swal.fire({
                                title: 'บันทึกสำเร็จ',
                                text: 'อัปเดตข้อมูลเรียบร้อยแล้ว',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(function() {
                                window.location.reload(true);
                            });
                        }
                    } else {
                        Swal.fire("ข้อผิดพลาด", data.message || "เกิดข้อผิดพลาด", "error");
                    }
                } catch (ex) {
                    Swal.fire("ข้อผิดพลาด", "ไม่สามารถอ่านผลลัพธ์จากเซิร์ฟเวอร์", "error");
                }
            },
            error: function() {
                Swal.fire("ข้อผิดพลาด", "ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้", "error");
            }
        });
    });
</script>