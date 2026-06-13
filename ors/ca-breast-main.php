<?php
require_once '../include/Session.php';
if (session_status() === PHP_SESSION_NONE)
    session_start();
$loginname = isset($_SESSION['loginname']) ? $_SESSION['loginname'] : null;

require_once '../mains/main-report.php';
$permissionCheck = Session::checkPermissionAndShowMessage('CA_BREAST', 'VIEW');
$permissionCheckJson = json_encode($permissionCheck);

require_once '../mains/ipd-show-patient-main.php';
require_once '../mains/ipd-show-patient-sticky.php';
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';
require_once '../include/ReportQueryUtils.php';
require_once '../include/session-modal.php';

$an      = isset($_REQUEST['an'])     ? trim($_REQUEST['an'])     : '';
$new_id  = isset($_REQUEST['new_id']) ? (int)$_REQUEST['new_id'] : 0;
$hn      = '';
$records = array();

try {
    $conn = DbUtils::get_hosxp_connection();
    $hn   = KphisQueryUtils::getHnByAn($an);

    Session::insertSystemAccessLog(json_encode(array(
        'form' => 'CA-BREAST-MAIN',
        'an'   => $an,
    ), JSON_UNESCAPED_UNICODE));

    $stmt = $conn->prepare(
        "SELECT id, order_date, order_type, cycle_no, bw, bsa,
                paclitaxel_dose, next_appt_date,
                created_name, created_at, updated_at, created_by
           FROM prs_ca_breast
          WHERE an = :an AND is_deleted = 0
          ORDER BY order_date DESC, id DESC"
    );
    $stmt->execute(array('an' => $an));
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    echo '<div class="alert alert-danger m-3">Database Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
}

$canEdit = Session::checkPermission('CA_BREAST', 'EDIT');
?>

<script src="../node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
<link rel="stylesheet" href="../node_modules/sweetalert2/dist/sweetalert2.min.css">

<style>
    .cab-header {
        background: linear-gradient(135deg, #7b1fa2 0%, #4a148c 100%);
        color: #fff;
        padding: 12px 16px;
        border-radius: 6px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .cab-header h5 {
        margin: 0;
        font-weight: bold;
        font-size: 1rem;
    }
    .cab-header small { opacity: .8; font-size: 0.78rem; }

    .cab-table th {
        background: #6a1b9a;
        color: #fff;
        font-size: 0.85rem;
        white-space: nowrap;
        vertical-align: middle;
    }
    .cab-table td {
        vertical-align: middle;
        font-size: 0.88rem;
    }
    .badge-cycle {
        background: #7b1fa2;
        color: #fff;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.78rem;
        font-weight: bold;
    }
    .badge-adj  { background: #1565c0; }
    .badge-meta { background: #e65100; }
</style>

<div id="formContainer">
    <div class="container-fluid py-2">

        <!-- Header -->
        <div class="cab-header">
            <div>
                <h5><i class="fas fa-pills mr-2"></i>CA Breast — Paclitaxel Regimen (AC-4T)</h5>
                <small>CHEMOTHERAPY ORDER SHEET &nbsp;|&nbsp; AN: <?= htmlspecialchars($an) ?></small>
            </div>
            <div>
                <?php if ($canEdit && \ReportQueryUtils::checkReadOnly($an)): ?>
                    <a href="ca-breast-form.php?an=<?= urlencode($an) ?>&loginname=<?= urlencode($loginname) ?>"
                       target="_blank" class="btn btn-sm btn-light shadow-sm mr-2">
                        <i class="fas fa-plus"></i> เพิ่มรายการใหม่
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Table -->
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm table-hover mb-0 cab-table">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:45px;">#</th>
                                <th>วันที่สั่ง</th>
                                <th class="text-center">ประเภท</th>
                                <th class="text-center">Cycle</th>
                                <th class="text-center">BW (kg)</th>
                                <th class="text-center">BSA (m²)</th>
                                <th class="text-center">Paclitaxel (mg)</th>
                                <th>นัดครั้งต่อไป</th>
                                <th>ผู้บันทึก</th>
                                <th>บันทึกเมื่อ</th>
                                <th class="text-center" style="width:130px;">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($records)): ?>
                                <tr>
                                    <td colspan="11" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        ยังไม่มีข้อมูล — กดปุ่ม "เพิ่มรายการใหม่" เพื่อบันทึก
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1;
                                foreach ($records as $row): ?>
                                    <tr id="row-<?= $row['id'] ?>" <?= ($new_id && $row['id'] == $new_id) ? 'class="table-success new-record"' : '' ?>>
                                        <td class="text-center text-muted"><?= $no++ ?></td>
                                        <td><?= $row['order_date'] ? date('d/m/Y', strtotime($row['order_date'])) : '-' ?></td>
                                        <td class="text-center">
                                            <?php if ($row['order_type'] === 'adjuvant'): ?>
                                                <span class="badge-cycle badge-adj">Adjuvant</span>
                                            <?php elseif ($row['order_type'] === 'metastasis'): ?>
                                                <span class="badge-cycle badge-meta">Metastasis</span>
                                            <?php else: echo '-'; endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?= $row['cycle_no'] ? '<span class="badge-cycle">' . $row['cycle_no'] . '/4</span>' : '-' ?>
                                        </td>
                                        <td class="text-center"><?= $row['bw'] ?: '-' ?></td>
                                        <td class="text-center"><?= $row['bsa'] ?: '-' ?></td>
                                        <td class="text-center font-weight-bold"><?= $row['paclitaxel_dose'] ? number_format($row['paclitaxel_dose'], 2) : '-' ?></td>
                                        <td><?= $row['next_appt_date'] ? date('d/m/Y', strtotime($row['next_appt_date'])) : '-' ?></td>
                                        <td><?= htmlspecialchars($row['created_name'] ?: $row['created_by'] ?: '-') ?></td>
                                        <td><?= $row['created_at'] ? date('d/m/Y H:i', strtotime($row['created_at'])) : '-' ?></td>
                                        <td class="text-center">
                                            <a href="ca-breast-form.php?an=<?= urlencode($an) ?>&id=<?= $row['id'] ?>&loginname=<?= urlencode($loginname) ?>"
                                               target="_blank" class="btn btn-xs btn-outline-primary"
                                               style="font-size:0.78rem; padding:2px 8px;">
                                               <i class="fas fa-edit"></i> เปิด/แก้ไข
                                            </a>
                                            <a href="../pdffile/ca-breast-pdf.php?an=<?= urlencode($an) ?>&id=<?= $row['id'] ?>&loginname=<?= urlencode($loginname) ?>"
                                               target="_blank" class="btn btn-xs btn-outline-danger mt-1"
                                               style="font-size:0.78rem; padding:2px 8px;">
                                               <i class="fas fa-file-pdf"></i> PDF
                                            </a>
                                            <?php if ($canEdit && \ReportQueryUtils::checkReadOnly($an) && $row['created_by'] === $loginname): ?>
                                                <button type="button"
                                                    onclick="deleteRecord(<?= $row['id'] ?>, '<?= date('d/m/Y', strtotime($row['order_date'] ?: 'now')) ?> Cycle<?= $row['cycle_no'] ?>')"
                                                    class="btn btn-xs btn-outline-secondary mt-1"
                                                    style="font-size:0.78rem; padding:2px 8px; color:#dc3545; border-color:#dc3545;">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-muted small">
                พบ <?= count($records) ?> รายการ
            </div>
        </div>

    </div>
</div>

<script>
    function deleteRecord(id, label) {
        Swal.fire({
            title: 'ยืนยันการลบ?',
            html: 'ต้องการลบรายการ <b>' + label + '</b> ใช่หรือไม่?<br><small class="text-danger">การกระทำนี้ไม่สามารถยกเลิกได้</small>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash-alt"></i> ลบ',
            cancelButtonText: 'ยกเลิก'
        }).then(function (result) {
            if (result.isConfirmed) {
                $.post('ca-breast-delete.php',
                    { id: id, an: '<?= addslashes($an) ?>' },
                    function (resp) {
                        try {
                            var data = (typeof resp === 'string') ? JSON.parse(resp) : resp;
                            if (data.status === 'success') {
                                Swal.fire('ลบแล้ว', 'ลบรายการเรียบร้อยแล้ว', 'success').then(function () {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('ข้อผิดพลาด', data.message || 'ไม่สามารถลบได้', 'error');
                            }
                        } catch (e) {
                            Swal.fire('ข้อผิดพลาด', 'ไม่สามารถอ่านผลลัพธ์จากเซิร์ฟเวอร์', 'error');
                        }
                    }
                );
            }
        });
    }

    <?php if ($new_id): ?>
        $(document).ready(function () {
            var $row = $('#row-<?= $new_id ?>');
            if ($row.length) {
                $('html, body').animate({ scrollTop: $row.offset().top - 120 }, 400);
                $row.css({ transition: 'background-color 0.5s' });
                setTimeout(function () { $row.css('background-color', '#e1bee7'); }, 100);
                setTimeout(function () { $row.css('background-color', ''); }, 2500);
                Swal.fire({
                    toast: true, position: 'top-end', icon: 'success',
                    title: 'บันทึกรายการใหม่เรียบร้อยแล้ว',
                    showConfirmButton: false, timer: 3000, timerProgressBar: true
                });
            }
        });
    <?php endif; ?>
</script>
