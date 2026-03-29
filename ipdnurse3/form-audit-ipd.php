<?php
require_once '../include/Session.php';
// Simplified session check - ensure session is active via Session.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$loginname = isset($_SESSION['loginname']) ? $_SESSION['loginname'] : null;
// If no session, redirect or handle error (optional, keeping it simple for now)



require_once '../mains/main-report.php';
$permissionCheck = Session::checkPermissionAndShowMessage('PRS_FORM_AUDIT_IPD', 'VIEW');
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
    $vn = KphisQueryUtils::getVnByAn($an);
    $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : null;

    // If no ID is provided, try to find an existing record for this AN
    if (!$ids) {
        $sql_check = "SELECT id FROM `prs_audit_ipd` WHERE an = :an ORDER BY id DESC LIMIT 1";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->execute(['an' => $an]);
        $res_check = $stmt_check->fetch();
        if ($res_check) {
            $ids = $res_check['id'];
        }
    }

    Session::insertSystemAccessLog(json_encode(array(
        'form' => 'AUDIT-IPD-FORM',
        'an' => $an,
    ), JSON_UNESCAPED_UNICODE));

    $audit_row = null;
    $audit_items = array();
    if ($ids) {
        $sql = "SELECT * FROM `prs_audit_ipd` WHERE an = :an AND id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['an' => $an, 'id' => $ids]);
        $audit_row = $stmt->fetch();

        if ($audit_row) {
            $sql_item = "SELECT * FROM `prs_audit_ipd_item` WHERE audit_id = :audit_id ORDER BY content_index ASC";
            $stmt_item = $conn->prepare($sql_item);
            $stmt_item->execute(['audit_id' => $audit_row['id']]);
            while ($item_row = $stmt_item->fetch()) {
                $audit_items[$item_row['content_index']] = $item_row;
            }
        }
    }
} catch (Exception $e) {
    echo '<div class="alert alert-danger" style="margin:20px;">Database Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    // If table doesn't exist, we might want to continue with empty data if it's the first time
    // but usually we should stop and let the user fix the DB.
}

// Patient info for header
$sql_ipt = "SELECT p.pname, p.fname, p.lname, i.regdate, i.regtime, i.dchdate, i.dchtime
            FROM " . DbConstant::HOSXP_DBNAME . ".ipt i
            LEFT JOIN " . DbConstant::HOSXP_DBNAME . ".patient p ON p.hn = i.hn
            WHERE i.an = :an";
$stmt_ipt = $conn->prepare($sql_ipt);
$stmt_ipt->execute(['an' => $an]);
$row_ipt = $stmt_ipt->fetch();

$audit_contents = [
    1 => "1. Discharge summary: Dx,, OP",
    2 => "2. Discharge summary: Other",
    3 => "3. Informed consent",
    4 => "4. History",
    5 => "5. Physical exam",
    6 => "6. Progress note",
    7 => "7. Consultation record",
    8 => "8. Anesthetic record",
    9 => "9. Operative note",
    10 => "10. Labour record",
    11 => "11. Rehabilitation record",
    12 => "12. Nurses' note"
];

$check_ = ReportQueryUtils::getProduction(26);
?>

<style>
    .audit-table th,
    .audit-table td {
        border: 1px solid #000;
        padding: 5px;
        vertical-align: middle;
    }

    .audit-table th {
        background-color: #f2f2f2;
        text-align: center;
        font-weight: bold;
    }

    .text-center {
        text-align: center;
    }

    .font-weight-bold {
        font-weight: bold;
    }

    .bg-light-blue {
        background-color: #e6f3ff;
    }
</style>

<script src="../node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
<link rel="stylesheet" href="../node_modules/sweetalert2/dist/sweetalert2.min.css">

<div id="formContainer">
    <form id="audit_form">
        <input type="hidden" name="an" value="<?= htmlspecialchars($an) ?>">
        <input type="hidden" name="id" value="<?= htmlspecialchars($ids) ?>">

        <div class="container-fluid">
            <div class="row align-items-center mb-3">
                <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.close()"><i
                            class="fas fa-times"></i> ปิดหน้านี้</button>
                </div>
                <div class="col">
                    <h5 class="mb-0"><b>แบบตรวจประเมินคุณภาพการบันทึกเวชระเบียนผู้ป่วยใน Medical Record Audit Form
                            (IPD)</b></h5>
                </div>
                <div class="col-auto">
                    <?php if ($ids): ?>
                        <a href="../pdffile/audit-ipd-pdf.php?an=<?= htmlspecialchars($an) ?>&id=<?= htmlspecialchars($ids) ?>&loginname=<?= htmlspecialchars($loginname) ?>"
                            target="_blank" class="btn btn-sm btn-info px-4 shadow-sm"><i class="fas fa-file-pdf"></i> พิมพ์
                            PDF</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-1"><b>Hcode:</b> <?= htmlspecialchars(DbConstant::HOSPITAL_CODE) ?></div>
                        <div class="col-md-3"><b>Hname:</b> <?= htmlspecialchars(DbConstant::HOSPITAL_NAME) ?></div>
                        <div class="col-md-2"><b>HN:</b> <?= htmlspecialchars($hn) ?></div>
                        <div class="col-md-2"><b>AN:</b> <?= htmlspecialchars($an) ?></div>
                    </div>
                    <div class="row">
                        <div class="col-md-3"><b>Date admitted:</b> <?= htmlspecialchars($row_ipt['regdate']) ?></div>
                        <div class="col-md-3"><b>Date discharged:</b> <?= htmlspecialchars($row_ipt['dchdate']) ?></div>
                        <div class="col-md-3">
                            <label><b>Audit Date:</b></label>
                            <input type="date" name="audit_date"
                                class="form-control form-control-sm d-inline-block w-auto"
                                value="<?= isset($audit_row['audit_date']) ? $audit_row['audit_date'] : date('Y-m-d') ?>">
                        </div>
                    </div>
                    <b>การบันทึกช่อง NA:</b> กรณีไม่จำเป็นต้องมีเอกสาร Content ลำดับที่ 7,8,9,10,11
                    เนื่องจากไม่มีการบริการให้กากบาท ในช่อง NA <br>
                    <b>การบันทึกช่อง Missing:</b> กรณีไม่มีเอกสารให้ตรวจสอบ เวชระเบียนไม่ครบ หรือหายไป ให้ กากบาท ในช่อง
                    Missing <br>
                    <b>การบันทึกช่อง No:</b> กรณีมีเอกสารแต่ไม่มีการบันทึกในเอกสารนั้น ให้กากบาท ในช่อง No <br>
                    <b>การบันทึกคะแนน:</b> (1) กรณีที่ผ่านเกณฑ์ในแต่ละข้อให้ 1 คะแนน (2)
                    กรณีที่ไม่ผ่านเกณฑ์ในแต่ละข้อให้ 0
                    คะแนน (3) กรณีที่ไม่จำเป็นต้องมีการบันทึก/ไม่มีข้อมูล <u>ในเกณฑ์ข้อที่ระบุให้มี NA ได้ ให้ NA</u>
                    <br>
                </div>





            </div>

            <div class="table-responsive">
                <table class="audit-table w-100" style="font-size: 0.9rem;">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 20%;">Content</th>
                            <th rowspan="2" style="width: 4%;">NA</th>
                            <th rowspan="2" style="width: 4%;">Missing</th>
                            <th rowspan="2" style="width: 4%;">No</th>
                            <th colspan="9">เกณฑ์ (Criteria)</th>
                            <th rowspan="2" style="width: 5%;">หักคะแนน</th>
                            <th rowspan="2" style="width: 5%;">รวมคะแนน</th>
                            <th rowspan="2" style="width: 15%;">หมายเหตุ (Remarks)</th>
                        </tr>
                        <tr>
                            <?php for ($i = 1; $i <= 9; $i++): ?>
                                <th style="width: 3%;">ข้อ <?= $i ?></th>
                            <?php endfor; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($audit_contents as $content_idx => $title):
                            $item = isset($audit_items[$content_idx]) ? $audit_items[$content_idx] : null;
                            ?>
                            <tr class="<?= $content_idx % 2 == 0 ? 'bg-light-blue' : '' ?>">
                                <td><b><?= htmlspecialchars($title) ?></b></td>
                                <td class="text-center"><input type="checkbox" name="item[<?= $content_idx ?>][na]"
                                        value="1" <?= (isset($item['is_na']) && $item['is_na']) ? 'checked' : '' ?>
                                        onchange="calcScore(<?= $content_idx ?>)"></td>
                                <td class="text-center"><input type="checkbox" name="item[<?= $content_idx ?>][missing]"
                                        value="1" <?= (isset($item['is_missing']) && $item['is_missing']) ? 'checked' : '' ?>
                                        onchange="calcScore(<?= $content_idx ?>)"></td>
                                <td class="text-center"><input type="checkbox" name="item[<?= $content_idx ?>][no_val]"
                                        value="1" <?= (isset($item['is_no']) && $item['is_no']) ? 'checked' : '' ?>
                                        onchange="calcScore(<?= $content_idx ?>)"></td>
                                <?php for ($c = 1; $c <= 9; $c++): ?>
                                    <td class="text-center">
                                        <input type="checkbox" name="item[<?= $content_idx ?>][c<?= $c ?>]" value="1"
                                            <?= (isset($item['c' . $c]) && $item['c' . $c]) ? 'checked' : '' ?>
                                            onchange="calcScore(<?= $content_idx ?>)">
                                    </td>
                                <?php endfor; ?>
                                <td class="text-center"><input type="number" name="item[<?= $content_idx ?>][deduct_score]"
                                        class="form-control form-control-sm text-center"
                                        value="<?= isset($item['deduct_score']) ? $item['deduct_score'] : 0 ?>" readonly>
                                </td>
                                <td class="text-center"><input type="number" name="item[<?= $content_idx ?>][total_score]"
                                        class="form-control form-control-sm text-center"
                                        value="<?= isset($item['total_score']) ? $item['total_score'] : 0 ?>" readonly></td>
                                <td><input type="text" name="item[<?= $content_idx ?>][remark]"
                                        class="form-control form-control-sm"
                                        value="<?= isset($item['remark']) ? htmlspecialchars($item['remark']) : '' ?>"></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="font-weight-bold">
                            <td colspan="15" class="text-right">คะแนนเต็ม (Full score) รวม: (ต้องไม่น้อยกว่า 56 คะแนน)
                            </td>
                            <td class="text-center"><input type="number" name="full_score" id="full_score"
                                    class="form-control form-control-sm text-center font-weight-bold"
                                    value="<?= isset($audit_row['full_score']) ? $audit_row['full_score'] : 0 ?>"
                                    readonly></td>
                            <td rowspan="2"></td>
                        </tr>
                        <tr class="font-weight-bold">
                            <td colspan="15" class="text-right">คะแนนที่ได้ (Sum score):</td>
                            <td class="text-center"><input type="number" name="sum_score" id="sum_score"
                                    class="form-control form-control-sm text-center font-weight-bold text-primary"
                                    style="font-size: 1.2rem;"
                                    value="<?= isset($audit_row['sum_score']) ? $audit_row['sum_score'] : 0 ?>"
                                    readonly></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="card mt-3">
                <div class="card-header bg-secondary text-white font-weight-bold text-center">
                    ประเมินคุณภาพการบันทึกเวชระเบียนในภาพรวม Overall finding</div>
                <div class="card-body">
                    <div class="custom-control custom-checkbox mb-2">
                        <input type="checkbox" class="custom-control-input" id="of1" name="overall_finding_1" value="1"
                            <?= (isset($audit_row['overall_finding_1']) && $audit_row['overall_finding_1']) ? 'checked' : '' ?>>
                        <label class="custom-control-label" for="of1">
                            การจัดเรียงเวชระเบียนไม่เป็นไปตามมาตรฐานที่กำหนด</label>
                    </div>
                    <div class="custom-control custom-checkbox mb-2">
                        <input type="checkbox" class="custom-control-input" id="of2" name="overall_finding_2" value="1"
                            <?= (isset($audit_row['overall_finding_2']) && $audit_row['overall_finding_2']) ? 'checked' : '' ?>>
                        <label class="custom-control-label" for="of2">เอกสารบางแผ่นไม่มีข้อบ่งชี้บริการ HN AN
                            ทำให้ไม่สามารถระบุได้ว่า เอกสารแผ่นนี้เป็นของใครจึงไม่สามารถทบทวนเอกสารแผ่นนั้นได้
                        </label>
                    </div>
                    <b>(เลือกได้เพียง 1 ข้อ)</b>
                    <div class="custom-control custom-checkbox mb-2">
                        <input type="checkbox" class="custom-control-input overall-finding-check" id="of3"
                            name="overall_finding_3" value="1" <?= (isset($audit_row['overall_finding_3']) && $audit_row['overall_finding_3']) ? 'checked' : '' ?>>
                        <label class="custom-control-label" for="of3"> Documentation inadequate for meaningful
                            review (ไม่มีข้อมูลเพียงพอสำหรับการทบทวน)</label>
                    </div>
                    <div class="custom-control custom-checkbox mb-2">
                        <input type="checkbox" class="custom-control-input overall-finding-check" id="of4"
                            name="overall_finding_4" value="1" <?= (isset($audit_row['overall_finding_4']) && $audit_row['overall_finding_4']) ? 'checked' : '' ?>>
                        <label class="custom-control-label" for="of4">No significant medical record issue
                            identified (ไม่มีปัญหาสำคัญจากการทบทวน)</label>
                    </div>
                    <div class="custom-control custom-checkbox mb-2">
                        <input type="checkbox" class="custom-control-input overall-finding-check" id="of5"
                            name="overall_finding_5" value="1" <?= (isset($audit_row['overall_finding_5']) && $audit_row['overall_finding_5']) ? 'checked' : '' ?>>
                        <label class="custom-control-label" for="of5">Certain issues in question specify:
                            (มีปัญหาจากการทบทวนที่ต้องค้นต่อระบุ)</label>
                    </div>
                    <textarea name="overall_finding_text" id="overall_finding_text" class="form-control" rows="3"
                        placeholder="ระบุรายละเอียดเพิ่มเติม..."><?= isset($audit_row['overall_finding_text']) ? htmlspecialchars($audit_row['overall_finding_text']) : '' ?></textarea>
                </div>
            </div>

            <div class="row mt-4 mb-5">
                <div class="col text-center">
                    <button type="submit" class="btn btn-success btn-lg px-5 shadow"><i class="fas fa-save"></i>
                        บันทึกข้อมูล</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function calcScore(idx) {
        var na = document.querySelector('input[name="item[' + idx + '][na]"]').checked;
        var missing = document.querySelector('input[name="item[' + idx + '][missing]"]').checked;
        var isNo = document.querySelector('input[name="item[' + idx + '][no_val]"]').checked;

        // Exact name fields for this row
        var deductInput = document.querySelector('input[name="item[' + idx + '][deduct_score]"]');
        var totalInput = document.querySelector('input[name="item[' + idx + '][total_score]"]');

        var total = 0;
        var deduct = 0;

        if (na) {
            // Disable all criteria AND status checkboxes (missing/no) when NA is set
            var statusInputs = ['missing', 'no_val'];
            for (var s = 0; s < statusInputs.length; s++) {
                var sInp = document.querySelector('input[name="item[' + idx + '][' + statusInputs[s] + ']"]');
                if (sInp) {
                    sInp.disabled = true;
                    sInp.checked = false;
                }
            }
            for (var c = 1; c <= 9; c++) {
                var cInpRow = document.querySelector('input[name="item[' + idx + '][c' + c + ']"]');
                if (cInpRow) {
                    cInpRow.disabled = true;
                    cInpRow.checked = false;
                }
            }
        } else {
            // Re-enable status inputs
            var statusInputs2 = ['missing', 'no_val'];
            for (var s2 = 0; s2 < statusInputs2.length; s2++) {
                var sInp2 = document.querySelector('input[name="item[' + idx + '][' + statusInputs2[s2] + ']"]');
                if (sInp2) sInp2.disabled = false;
            }

            if (missing || isNo) {
                deduct = (idx <= 2) ? 3 : 1;
                total = 0;
                for (var c3 = 1; c3 <= 9; c3++) {
                    var cInpRow3 = document.querySelector('input[name="item[' + idx + '][c' + c3 + ']"]');
                    if (cInpRow3) {
                        cInpRow3.disabled = false;
                        cInpRow3.checked = false;
                    }
                }
            } else {
                for (var k = 1; k <= 9; k++) {
                    var cInpRow3 = document.querySelector('input[name="item[' + idx + '][c' + k + ']"]');
                    if (cInpRow3) {
                        cInpRow3.disabled = false;
                    }
                }

                // Count checked criteria as total score (fixing prefix match bug)
                var checkedCriteria = 0;
                for (var i = 1; i <= 9; i++) {
                    var cInp = document.querySelector('input[name="item[' + idx + '][c' + i + ']"]');
                    if (cInp && cInp.checked) checkedCriteria++;
                }
                total = checkedCriteria;
                deduct = 0;
            }
        }

        document.querySelector('input[name="item[' + idx + '][deduct_score]"]').value = deduct;
        document.querySelector('input[name="item[' + idx + '][total_score]"]').value = total;

        updateGrandTotal();
    }

    function updateGrandTotal() {
        var full = 0;
        var sum = 0;

        for (var i = 1; i <= 12; i++) {
            var naInput = document.querySelector('input[name="item[' + i + '][na]"]');
            var na = naInput ? naInput.checked : false;
            if (!na) {
                full += 9;
            }
            var totalInput = document.querySelector('input[name="item[' + i + '][total_score]"]');
            var deductInput = document.querySelector('input[name="item[' + i + '][deduct_score]"]');
            var tVal = parseInt(totalInput ? totalInput.value : 0) || 0;
            var dVal = parseInt(deductInput ? deductInput.value : 0) || 0;
            sum += (tVal - dVal);
        }

        document.getElementById('full_score').value = full;
        document.getElementById('sum_score').value = sum;

        // Visual feedback for sum score
        var sumInput = document.getElementById('sum_score');
        if (sum < 56 && full > 0) {
            sumInput.classList.add('text-danger');
            sumInput.classList.remove('text-primary');
        } else {
            sumInput.classList.remove('text-danger');
            sumInput.classList.add('text-primary');
        }
    }

    $("#audit_form").on("submit", function (e) {
        e.preventDefault();
        var formData = $(this).serialize();
        var id = $("input[name='id']").val();
        var url = id ? "form-audit-ipd-update.php" : "form-audit-ipd-save.php";

        $.ajax({
            url: url,
            type: "POST",
            data: formData,
            success: function (resp) {
                var data = JSON.parse(resp);
                if (data.status === "success") {
                    Swal.fire("สำเร็จ", "บันทึกข้อมูลเรียบร้อยแล้ว", "success").then(function () {
                        window.opener.location.reload();
                        window.close();
                    });
                } else {
                    Swal.fire("ข้อผิดพลาด", data.message, "error");
                }
            }
        });
    });

    // Overall finding logic
    $('.overall-finding-check').on('change', function () {
        if ($(this).is(':checked')) {
            $('.overall-finding-check').not(this).prop('checked', false);
        }
        toggleOverallText();
    });

    function toggleOverallText() {
        var isOf5 = $('#of5').is(':checked');
        $('#overall_finding_text').prop('disabled', !isOf5);
    }

    // Initial calc
    window.onload = function () {
        for (var x = 1; x <= 12; x++) {
            calcScore(x);
        }
        toggleOverallText();
    };
</script>