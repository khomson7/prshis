<?php
require_once '../include/Session.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$loginname = isset($_SESSION['loginname']) ? $_SESSION['loginname'] : null;

require_once '../mains/main-report.php';
$permissionCheck = Session::checkPermissionAndShowMessage('PRS_FORM_NUTRITION', 'VIEW');
$permissionCheckJson = json_encode($permissionCheck);

require_once '../mains/ipd-show-patient-main.php';
require_once '../mains/ipd-show-patient-sticky.php';
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';
require_once '../include/ReportQueryUtils.php';
require_once '../include/session-modal.php';

try {
    $conn = DbUtils::get_hosxp_connection();
    $an   = $_REQUEST['an'];
    $hn   = KphisQueryUtils::getHnByAn($an);
    $vn   = KphisQueryUtils::getVnByAn($an);
    $ids  = isset($_REQUEST['id']) ? $_REQUEST['id'] : null;

    if (!$ids) {
        $sql_check = "SELECT id FROM `prs_check_vitalsign` WHERE an = :an ORDER BY id DESC LIMIT 1";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->execute(['an' => $an]);
        $res_check = $stmt_check->fetch();
        if ($res_check) {
            $ids = $res_check['id'];
        }
    }

    Session::insertSystemAccessLog(json_encode(['form' => 'NUTRITION-FORM', 'an' => $an], JSON_UNESCAPED_UNICODE));

    $row = null;
    if ($ids) {
        $sql  = "SELECT * FROM `prs_check_vitalsign` WHERE an = :an AND id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['an' => $an, 'id' => $ids]);
        $row  = $stmt->fetch();
    }

    // Patient age
    $sql_ipt = "SELECT s.age_y FROM " . DbConstant::HOSXP_DBNAME . ".ipt i
                LEFT JOIN " . DbConstant::HOSXP_DBNAME . ".an_stat s ON s.an = i.an
                WHERE i.an = :an";
    $stmt_ipt = $conn->prepare($sql_ipt);
    $stmt_ipt->execute(['an' => $an]);
    $row_ipt = $stmt_ipt->fetch();

    $check_ = ReportQueryUtils::getProduction(26);
} catch (Exception $e) {
    echo '<div class="alert alert-danger" style="margin:20px;">Database Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
}

// Values from DB
$f_vstdate      = $row ? $row['vstdate']      : date('Y-m-d');
$f_height       = $row ? $row['height']       : '';
$f_bw           = $row ? $row['bw']           : '';
$f_bmi          = $row ? $row['bmi']          : '';
$f_age_y        = $row ? $row['age_y']        : ($row_ipt ? $row_ipt['age_y'] : '');
$f_check_bmi    = $row ? $row['check_bmi']    : '';
$f_bw_1week     = $row ? $row['bw_1week']     : '';
$f_bw_2_3week   = $row ? $row['bw_2_3week']   : '';
$f_bw_1month    = $row ? $row['bw_1month']    : '';
$f_bw_3month    = $row ? $row['bw_3month']    : '';
$f_bw_5month    = $row ? $row['bw_5month']    : '';
$f_percen_1week   = $row && isset($row['percen_1week'])   ? $row['percen_1week']   : '';
$f_percen_2_3week = $row && isset($row['percen_2_3week']) ? $row['percen_2_3week'] : '';
$f_percen_1month  = $row && isset($row['percen_1month'])  ? $row['percen_1month']  : '';
$f_percen_3month  = $row && isset($row['percen_3month'])  ? $row['percen_3month']  : '';
$f_percen_5month  = $row && isset($row['percen_5month'])  ? $row['percen_5month']  : '';
?>

<style>
    .nutrition-input {
        border: 1px solid #aaa;
        border-radius: 4px;
        padding: 4px 8px;
        font-size: 0.9rem;
        width: 100%;
        transition: background .2s;
    }
    .nutrition-input[readonly] {
        background-color: #f0f4f8;
        color: #495057;
        cursor: default;
    }
    .nutrition-input:not([readonly]):not([disabled]) {
        background-color: #fff9e6;
        border-color: #f0a500;
    }
    .nutrition-input[disabled] {
        background-color: #e9ecef;
        color: #6c757d;
        cursor: not-allowed;
    }
    .percen-box {
        background: #f0fff4;
        border: 1px solid #b2dfdb;
        border-radius: 4px;
        padding: 4px 8px;
        font-size: 0.85rem;
        font-weight: bold;
        width: 100%;
        min-height: 31px;
        text-align: center;
    }
    .bmi-result-box {
        background: #e6f3ff;
        border-radius: 6px;
        padding: 5px 10px;
        font-weight: bold;
        font-size: 0.9rem;
        min-height: 32px;
    }
    .section-label {
        background-color: #d9edf7;
        font-weight: bold;
        padding: 5px 10px;
        border-radius: 4px;
        margin-bottom: 8px;
        display: block;
    }
    .bw-row {
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 8px 10px;
        margin-bottom: 8px;
    }
    .bw-row:hover { border-color: #adb5bd; }
    .unlock-btn {
        cursor: pointer;
        color: #007bff;
        font-size: 1rem;
        background: none;
        border: none;
        padding: 0 4px;
    }
    .unlock-btn.unlocked { color: #dc3545; }
</style>

<script src="../node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
<link rel="stylesheet" href="../node_modules/sweetalert2/dist/sweetalert2.min.css">

<div id="formContainer">
<form id="audit_form">
    <input type="hidden" name="an"      id="an"      value="<?= htmlspecialchars($an) ?>">
    <input type="hidden" name="id"      id="id"      value="<?= htmlspecialchars($ids) ?>">
    <input type="hidden" name="version" id="version" value="<?= htmlspecialchars($row ? $row['version'] : '1') ?>">

    <div class="container-fluid">

        <!-- Header -->
        <div class="row align-items-center mb-3">
            <div class="col-auto">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.close()">
                    <i class="fas fa-times"></i> ปิดหน้านี้
                </button>
            </div>
            <div class="col">
                <h5 class="mb-0"><b>แบบประเมิน Nutrition (Malnutrition) Meaningful in DRG-6</b></h5>
            </div>
            <div class="col-auto">
                <?php if ($ids): ?>
                <a href="../ipdnurse3/form-nutrition.php?an=<?= htmlspecialchars($an) ?>&id=<?= htmlspecialchars($ids) ?>&loginname=<?= htmlspecialchars($loginname) ?>"
                   target="_blank" class="btn btn-sm btn-info px-4 shadow-sm">
                    <i class="fas fa-file-pdf"></i> พิมพ์ PDF
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Criteria card -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex gap-3 flex-wrap">
                    <div class="flex-fill">
                        <b>เกณฑ์การวินิจฉัย SCG 2017 &amp; NHSO 2026</b><br>
                        <b>&nbsp; มีหลักฐานข้อใดข้อหนึ่ง ต่อไปนี้</b><br>
                        <b>&nbsp;&nbsp;&nbsp;* Body Mass Index [BMI] น้อยกว่า 18.5</b><br>
                        <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-17.00 - 18.49 เรียกว่า Mild protein-energy nutrition</b><br>
                        <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-16.00 - 16.99 เรียกว่า Moderate protein-energy nutrition</b><br>
                        <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;น้อยกว่า 16.00 เรียกว่า Serve protein-energy nutrition</b><br>
                        <b>&nbsp;&nbsp;&nbsp;* น้ำหนักลดลงจากเดิม ตามเกณฑ์ความรุนแรงของภาวะทุพโภชนาการ [ตาราง]</b><br>
                        <b>&nbsp;มีการดูแลรักษา เช่น อาหารที่มีโปรตีนสูง เพิ่มไข่ขาว รวมทั้ง Enter nutrition หรือ Parenteral nutrition</b><br>
                    </div>
                    <div class="flex-fill">
                        <table class="table table-bordered table-sm text-center" style="font-size:0.85rem;">
                            <thead>
                                <tr class="table-secondary">
                                    <th rowspan="2">น้ำหนักลดลง<br>ในช่วงเวลา</th>
                                    <th colspan="3">ภาวะทุพโภชนาการ</th>
                                </tr>
                                <tr class="table-secondary">
                                    <th>เล็กน้อย</th><th>ปานกลาง</th><th>รุนแรง</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>1 สัปดาห์</td><td>1%</td><td>1.1-2%</td><td>&gt;2%</td></tr>
                                <tr><td>2-3 สัปดาห์</td><td>2%</td><td>2.1-3%</td><td>&gt;3%</td></tr>
                                <tr><td>1 เดือน</td><td>4%</td><td>4.1-5%</td><td>&gt;5%</td></tr>
                                <tr><td>3 เดือน</td><td>7%</td><td>7.1-8%</td><td>&gt;8%</td></tr>
                                <tr><td>&gt;5 เดือน</td><td>8%</td><td>8.1-10%</td><td>&gt;10%</td></tr>
                            </tbody>
                        </table>
                        <b>ไม่ควรบันทึก Mild protein-energy nutrition</b>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main form card -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
                <b><i class="fas fa-clipboard-list"></i> ข้อมูลการประเมิน Nutrition</b>
                <button type="button" class="btn btn-warning btn-sm px-3" onclick="runSpUpdateBwAll()">
                    <i class="fas fa-sync-alt"></i> ประมวลผล sp_update_bw_all
                </button>
            </div>
            <div class="card-body">

                <!-- Disabled display fields -->
                <div class="row mb-3">
                    <div class="col-12 mb-1">
                        <span class="section-label"><i class="fas fa-info-circle"></i> ข้อมูลผู้ป่วย (ดึงจากระบบ — ไม่สามารถแก้ไขได้)</span>
                    </div>
                    <div class="col-md-2">
                        <label class="font-weight-bold">วันที่ประเมิน</label>
                        <input type="text" class="nutrition-input" id="vstdate" name="vstdate"
                               value="<?= htmlspecialchars($f_vstdate) ?>" disabled>
                    </div>
                    <div class="col-md-2">
                        <label class="font-weight-bold">ส่วนสูง (cm)</label>
                        <input type="text" class="nutrition-input" id="height" name="height"
                               value="<?= htmlspecialchars($f_height) ?>" disabled>
                    </div>
                    <div class="col-md-2">
                        <label class="font-weight-bold">น้ำหนักปัจจุบัน (kg)</label>
                        <input type="text" class="nutrition-input" id="bw" name="bw"
                               value="<?= htmlspecialchars($f_bw) ?>" disabled>
                    </div>
                    <div class="col-md-2">
                        <label class="font-weight-bold">BMI</label>
                        <input type="text" class="nutrition-input" id="bmi" name="bmi"
                               value="<?= htmlspecialchars($f_bmi) ?>" disabled>
                    </div>
                    <div class="col-md-2">
                        <label class="font-weight-bold">อายุ (ปี)</label>
                        <input type="text" class="nutrition-input" id="age_y" name="age_y"
                               value="<?= htmlspecialchars($f_age_y) ?>" disabled>
                    </div>
                    <div class="col-md-2">
                        <label class="font-weight-bold">ผลการประเมิน BMI</label>
                        <div class="bmi-result-box" id="bmi_result_display">
                            <?php
                            if ($f_bmi) {
                                $bmi_v = (float)$f_bmi;
                                if ($bmi_v < 16) echo '<span style="color:#dc3545;">Severe (' . number_format($bmi_v, 2) . ')</span>';
                                elseif ($bmi_v < 17) echo '<span style="color:#fd7e14;">Moderate (' . number_format($bmi_v, 2) . ')</span>';
                                elseif ($bmi_v < 18.5) echo '<span style="color:#e0a000;">Mild (' . number_format($bmi_v, 2) . ')</span>';
                                else echo '<span style="color:#28a745;">Normal (' . number_format($bmi_v, 2) . ')</span>';
                            }
                            ?>
                        </div>
                        <input type="hidden" id="check_bmi" name="check_bmi" value="<?= htmlspecialchars($f_check_bmi) ?>">
                    </div>
                </div>

                <hr>

                <!-- Weight history with readonly + unlock toggle + percent display -->
                <div class="row mb-2">
                    <div class="col-12 mb-2">
                        <span class="section-label">
                            <i class="fas fa-weight"></i> น้ำหนักก่อนรับไว้ (kg) — คลิก <i class="fas fa-lock-open text-primary"></i> เพื่อปลดล็อกแก้ไข
                        </span>
                    </div>
                </div>

                <?php
                // Period definition: [fieldId, percentFieldId, label]
                $periods = [
                    ['bw_1week',   'percen_1week',   '1 สัปดาห์'],
                    ['bw_2_3week', 'percen_2_3week', '2-3 สัปดาห์'],
                    ['bw_1month',  'percen_1month',  '1 เดือน'],
                    ['bw_3month',  'percen_3month',  '3 เดือน'],
                    ['bw_5month',  'percen_5month',  '>5 เดือน'],
                ];
                $bw_vals = [
                    'bw_1week'   => $f_bw_1week,
                    'bw_2_3week' => $f_bw_2_3week,
                    'bw_1month'  => $f_bw_1month,
                    'bw_3month'  => $f_bw_3month,
                    'bw_5month'  => $f_bw_5month,
                ];
                $pct_vals = [
                    'percen_1week'   => $f_percen_1week,
                    'percen_2_3week' => $f_percen_2_3week,
                    'percen_1month'  => $f_percen_1month,
                    'percen_3month'  => $f_percen_3month,
                    'percen_5month'  => $f_percen_5month,
                ];
                ?>

                <div class="row">
                    <?php foreach ($periods as [$fid, $pid, $plabel]): ?>
                    <?php
                        $hasVal = ($bw_vals[$fid] !== '' && $bw_vals[$fid] !== null);
                    ?>
                    <div class="col-md-4 mb-3">
                        <div class="bw-row">
                            <!-- Label + unlock button -->
                            <div class="d-flex align-items-center mb-1">
                                <span class="font-weight-bold flex-fill" style="font-size:0.85rem;">น้ำหนัก <?= $plabel ?></span>
                                <button type="button" class="unlock-btn" id="btn_<?= $fid ?>"
                                        onclick="toggleReadonly('<?= $fid ?>')"
                                        title="คลิกเพื่อแก้ไข">
                                    <i class="fas fa-lock" id="icon_<?= $fid ?>"></i>
                                </button>
                            </div>
                            <!-- BW input (readonly by default, unlock to edit) -->
                            <div class="d-flex gap-1 mb-1">
                                <input type="number"
                                       class="nutrition-input"
                                       id="<?= $fid ?>"
                                       name="<?= $fid ?>"
                                       value="<?= ($bw_vals[$fid] !== '' && $bw_vals[$fid] !== null) ? number_format((float)$bw_vals[$fid], 2, '.', '') : '' ?>"
                                       step="0.01" min="0" max="999.99"
                                       placeholder="0.00"
                                       readonly
                                       oninput="enforceTwoDecimal(this); calcPercent('<?= $fid ?>', '<?= $pid ?>')"
                                       onchange="if(parseFloat(this.value)<0||isNaN(parseFloat(this.value)))this.value='0.00';">
                                <span class="input-group-text" style="font-size:0.8rem;">kg</span>
                            </div>
                            <!-- Percent display (readonly) -->
                            <div class="d-flex align-items-center gap-1">
                                <span style="font-size:0.8rem; color:#555;">% ลดลง:</span>
                                <div class="percen-box" id="display_<?= $pid ?>">
                                    <?php
                                    if ($pct_vals[$pid] !== '' && $pct_vals[$pid] !== null) {
                                        $pv = (float)$pct_vals[$pid];
                                        $col = $pv <= 0 ? '#6c757d' : ($pv <= 2 ? '#28a745' : ($pv <= 5 ? '#fd7e14' : '#dc3545'));
                                        echo '<span style="color:' . $col . ';">' . number_format($pv, 2) . '%</span>';
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </div>
                                <input type="hidden" id="<?= $pid ?>" name="<?= $pid ?>"
                                       value="<?= htmlspecialchars($pct_vals[$pid]) ?>">
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <!-- Summary: Max % loss -->
                    <div class="col-md-4 mb-3">
                        <div class="bw-row" style="background:#fffde7;">
                            <div class="font-weight-bold mb-1" style="font-size:0.85rem;">
                                <i class="fas fa-chart-line text-danger"></i> % น้ำหนักลดสูงสุด
                            </div>
                            <div class="percen-box" id="max_percent_display" style="font-size:1rem; min-height:40px; display:flex; align-items:center; justify-content:center;">
                                <?php
                                // Calculate max on PHP side
                                $bw_now = (float)$f_bw;
                                $maxPct = 0; $maxLabel = '';
                                if ($bw_now > 0) {
                                    foreach ($periods as [$fid, $pid, $plabel]) {
                                        $prev = (float)$bw_vals[$fid];
                                        if ($prev > 0) {
                                            $pct = (($prev - $bw_now) / $prev) * 100;
                                            if ($pct > $maxPct) { $maxPct = $pct; $maxLabel = $plabel; }
                                        }
                                    }
                                }
                                if ($maxPct > 0) {
                                    $col = $maxPct <= 2 ? '#28a745' : ($maxPct <= 5 ? '#fd7e14' : '#dc3545');
                                    echo '<span style="color:' . $col . ';">' . number_format($maxPct, 2) . '%<br><small>(' . $maxLabel . ')</small></span>';
                                } else {
                                    echo '-';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Save row -->
                <div class="row mt-2">
                    <div id="show_check_save" class="col-12"></div>
                    <div class="col-md-12 text-right">
                        <?php if (
                            Session::checkPermission('IPD_NURSE_NOTE', 'ADD')
                            && ReportQueryUtils::checkReadOnly($an)
                        ): ?>
                        <button type="button" class="btn btn-primary px-5" onclick="form_save()">
                            <i class="fas fa-save"></i> บันทึก
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

            </div><!-- /card-body -->
        </div><!-- /card -->
    </div><!-- /container-fluid -->
</form>
</div>


<script src="../include/my_function.js"></script>
<script type="text/javascript">


    // CURRENT_BW: อ่านจาก PHP ก่อน ถ้าว่างให้พยายามอ่านจาก input
    var CURRENT_BW = <?= ($f_bw !== '' && $f_bw !== null) ? (float)$f_bw : 0 ?>;

    // ---- ฟังก์ชันบังคับทศนิยม 2 ตำแหน่งขณะพิมพ์ ----
    function enforceTwoDecimal(el) {
        var val = el.value;
        if (val.indexOf('.') !== -1) {
            var parts = val.split('.');
            if (parts[1].length > 2) {
                el.value = parts[0] + '.' + parts[1].slice(0, 2);
            }
        }
    }

    // ---- Toggle readonly on bw period input ----
    function toggleReadonly(fieldId) {
        var inp  = $('#' + fieldId);
        var icon = $('#icon_' + fieldId);
        var btn  = $('#btn_' + fieldId);
        if (inp.prop('readonly')) {
            inp.prop('readonly', false).focus();
            icon.removeClass('fa-lock').addClass('fa-lock-open');
            btn.addClass('unlocked');
        } else {
            inp.prop('readonly', true);
            icon.removeClass('fa-lock-open').addClass('fa-lock');
            btn.removeClass('unlocked');
        }
    }

    // ---- Calculate % for a period and update display ----
    function calcPercent(bwFieldId, pctFieldId) {
        var bw_prev = parseFloat($('#' + bwFieldId).val());
        // อ่าน CURRENT_BW จาก PHP value หรือจาก input ที่ disabled
        var bw_now  = CURRENT_BW > 0 ? CURRENT_BW : parseFloat($('#bw').val()) || 0;

        if (bw_prev > 0 && bw_now > 0) {
            var pct   = ((bw_prev - bw_now) / bw_prev) * 100;
            var pct_r = Math.round(pct * 100) / 100;
            $('#' + pctFieldId).val(pct_r);

            var col = pct_r <= 0 ? '#6c757d' : (pct_r <= 2 ? '#28a745' : (pct_r <= 5 ? '#fd7e14' : '#dc3545'));
            $('#display_' + pctFieldId).html(
                '<span style="color:' + col + ';">' + pct_r.toFixed(2) + '%</span>'
            );
        } else {
            $('#' + pctFieldId).val('');
            $('#display_' + pctFieldId).html('-');
        }
        updateMaxPercent();
    }

    // ---- Recalculate max % across all periods ----
    function updateMaxPercent() {
        var periods = [
            {bwId: 'bw_1week',   label: '1 สัปดาห์'},
            {bwId: 'bw_2_3week', label: '2-3 สัปดาห์'},
            {bwId: 'bw_1month',  label: '1 เดือน'},
            {bwId: 'bw_3month',  label: '3 เดือน'},
            {bwId: 'bw_5month',  label: '>5 เดือน'},
        ];
        var bw_now = CURRENT_BW > 0 ? CURRENT_BW : parseFloat($('#bw').val()) || 0;
        var maxPct = 0, maxLabel = '';
        if (bw_now <= 0) { $('#max_percent_display').html('-'); return; }

        periods.forEach(function(p) {
            var prev = parseFloat($('#' + p.bwId).val());
            if (prev > 0) {
                var pct = ((prev - bw_now) / prev) * 100;
                if (pct > maxPct) { maxPct = pct; maxLabel = p.label; }
            }
        });

        if (maxPct > 0) {
            var col = maxPct <= 2 ? '#28a745' : (maxPct <= 5 ? '#fd7e14' : '#dc3545');
            $('#max_percent_display').html(
                '<span style="color:' + col + ';">' + maxPct.toFixed(2) +
                '%<br><small>(' + maxLabel + ')</small></span>'
            );
        } else {
            $('#max_percent_display').html('-');
        }
    }

    // ---- Call Stored Procedure ----
    function runSpUpdateBwAll() {
        var an = $('#an').val();
        Swal.fire({
            title: 'ประมวลผล',
            text: 'ยืนยันการรัน sp_update_bw_all สำหรับ AN: ' + an + ' ?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'ยืนยัน',
            cancelButtonText: 'ยกเลิก',
        }).then(function(result) {
            if (result.isConfirmed) {
                $.post('form-nutrition-sp.php', {an: an}, function(data) {
                    $('#show_check_save').html(data);
                    setTimeout(function() { window.location.reload(true); }, 1500);
                }).fail(function() {
                    Swal.fire('ผิดพลาด', 'ไม่สามารถเรียก stored procedure ได้', 'error');
                });
            }
        });
    }

    // ---- AJAX Save / Update ----
    function form_save() {
        var url_save   = "form-nutrition-save.php";
        var url_update = "form-nutrition-update.php";
        var id         = $("#id").val();

        var my_form = $("#audit_form").serialize();
        // ส่ง disabled fields แยกต่างหาก
        my_form += '&vstdate=' + encodeURIComponent($('#vstdate').val());
        my_form += '&height='  + encodeURIComponent($('#height').val());
        my_form += '&bw='      + encodeURIComponent($('#bw').val());
        my_form += '&bmi='     + encodeURIComponent($('#bmi').val());
        my_form += '&age_y='   + encodeURIComponent($('#age_y').val());

        if (id == "") {
            $.post(url_save, my_form, function(data) {
                $("#show_check_save").html(data);
                setTimeout(function() { window.location.reload(true); }, 1200);
            }).fail(function() { alert("บันทึกข้อมูลไม่สำเร็จ"); });
        } else {
            $.post(url_update, my_form, function(data) {
                $("#show_check_save").html(data);
            }).fail(function() { alert("บันทึกข้อมูลไม่สำเร็จ"); });
        }
    }

    // ---- INIT: คำนวณ % ทุก period ตอน page load ----
    $(function() {
        // อัปเดต CURRENT_BW จาก input ถ้า PHP ไม่ได้ส่งมา
        if (CURRENT_BW <= 0) {
            CURRENT_BW = parseFloat($('#bw').val()) || 0;
        }

        var periodMap = [
            ['bw_1week',   'percen_1week'],
            ['bw_2_3week', 'percen_2_3week'],
            ['bw_1month',  'percen_1month'],
            ['bw_3month',  'percen_3month'],
            ['bw_5month',  'percen_5month'],
        ];

        periodMap.forEach(function(p) {
            var bwVal = parseFloat($('#' + p[0]).val());
            if (bwVal > 0) {
                // คำนวณและแสดง % (ถ้ายังไม่มีค่าจาก DB)
                var storedPct = parseFloat($('#' + p[1]).val());
                if (!storedPct || storedPct == 0) {
                    // คำนวณใหม่
                    calcPercent(p[0], p[1]);
                } else {
                    // มีค่าจาก DB แล้ว แค่ render สี
                    var col = storedPct <= 0 ? '#6c757d' : (storedPct <= 2 ? '#28a745' : (storedPct <= 5 ? '#fd7e14' : '#dc3545'));
                    $('#display_' + p[1]).html(
                        '<span style="color:' + col + ';">' + storedPct.toFixed(2) + '%</span>'
                    );
                }
            }
        });

        updateMaxPercent();
    });

</script>
