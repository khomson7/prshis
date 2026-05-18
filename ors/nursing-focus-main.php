<?php
require_once '../include/Session.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$loginname = isset($_SESSION['loginname']) ? $_SESSION['loginname'] : null;

require_once '../mains/main-report.php';
$permissionCheck     = Session::checkPermissionAndShowMessage('ORS_NURSING_FOCUS', 'VIEW');
$permissionCheckJson = json_encode($permissionCheck);

require_once '../mains/ipd-show-patient-main.php';
require_once '../mains/ipd-show-patient-sticky.php';
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';
require_once '../include/ReportQueryUtils.php';
require_once '../include/session-modal.php';

$an     = isset($_REQUEST['an']) ? trim($_REQUEST['an']) : '';
$new_id = isset($_REQUEST['new_id']) ? (int)$_REQUEST['new_id'] : 0;
$hn     = '';
$records = array();

try {
    $conn = DbUtils::get_hosxp_connection();
    $hn   = KphisQueryUtils::getHnByAn($an);

    Session::insertSystemAccessLog(json_encode(array(
        'form' => 'ORS-NURSING-FOCUS-MAIN',
        'an'   => $an,
    ), JSON_UNESCAPED_UNICODE));

    $stmt = $conn->prepare(
        "SELECT id, visit_date, shift, visit_time,
                visit_nurse, nurse_position,
                created_at, updated_at, created_by, updated_by
           FROM prs_ors_nursing_focus
          WHERE an = :an AND is_deleted = 0
          ORDER BY visit_date DESC, id DESC"
    );
    $stmt->execute(array('an' => $an));
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    echo '<div class="alert alert-danger m-3">Database Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
}

$canEdit = Session::checkPermission('ORS_NURSING_FOCUS', 'EDIT');
?>

<script src="../node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
<link  rel="stylesheet" href="../node_modules/sweetalert2/dist/sweetalert2.min.css">

<style>
.nfm-header {
    background: #1a6b3a;
    color: #fff;
    padding: 10px 16px;
    border-radius: 4px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.nfm-header h5 { margin: 0; font-weight: bold; font-size: 1rem; }
.nfm-header small { opacity: .8; font-size: 0.78rem; }
.nfm-table th {
    background: #1a6b3a;
    color: #fff;
    font-size: 0.85rem;
    white-space: nowrap;
    vertical-align: middle;
}
.nfm-table td { vertical-align: middle; font-size: 0.88rem; }
.badge-shift {
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 0.78rem;
    font-weight: bold;
    color: #fff;
}
.badge-shift.เช้า  { background: #f39c12; }
.badge-shift.บ่าย  { background: #8e44ad; }
.badge-shift.ดึก   { background: #2c3e50; }
</style>

<div id="formContainer">
<div class="container-fluid py-2">

    <!-- Header -->
    <div class="nfm-header">
        <div>
            <h5><i class="fas fa-notes-medical mr-2"></i>NURSING FOCUS CHARTHING</h5>
            <small>FM-NSO-ANE-006-07 &nbsp;|&nbsp; AN: <?= htmlspecialchars($an) ?></small>
        </div>
        <div>
            <?php if ($canEdit && \ReportQueryUtils::checkReadOnly($an)): ?>
            <a href="nursing-focus-form.php?an=<?= urlencode($an) ?>&loginname=<?= urlencode($loginname) ?>"
               target="_blank"
               class="btn btn-sm btn-light shadow-sm mr-2">
                <i class="fas fa-plus"></i> เพิ่มรายการใหม่
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-sm table-hover mb-0 nfm-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:50px;">#</th>
                            <th>วันที่</th>
                            <th class="text-center">กะ</th>
                            <th class="text-center">เวลา</th>
                            <th>ผู้บันทึก</th>
                            <th>บันทึกเมื่อ</th>
                            <th>แก้ไขล่าสุด</th>
                            <th class="text-center" style="width:130px;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($records)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                ยังไม่มีข้อมูล — กดปุ่ม "เพิ่มรายการใหม่" เพื่อบันทึก
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($records as $row): ?>
                        <tr id="row-<?= $row['id'] ?>"
                            <?= ($new_id && $row['id'] == $new_id) ? 'class="table-success new-record"' : '' ?>>
                            <td class="text-center text-muted"><?= $no++ ?></td>
                            <td><?= $row['visit_date'] ? date('d/m/Y', strtotime($row['visit_date'])) : '-' ?></td>
                            <td class="text-center">
                                <?php if ($row['shift']): ?>
                                <span class="badge-shift <?= htmlspecialchars($row['shift']) ?>">
                                    <?= htmlspecialchars($row['shift']) ?>
                                </span>
                                <?php else: echo '-'; endif; ?>
                            </td>
                            <td class="text-center"><?= htmlspecialchars($row['visit_time'] ?: '-') ?></td>
                            <td>
                                <?= htmlspecialchars($row['visit_nurse'] ?: $row['created_by'] ?: '-') ?>
                                <?php if ($row['nurse_position']): ?>
                                <br><small class="text-muted"><?= htmlspecialchars($row['nurse_position']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= $row['created_at'] ? date('d/m/Y H:i', strtotime($row['created_at'])) : '-' ?></td>
                            <td><?= $row['updated_at'] ? date('d/m/Y H:i', strtotime($row['updated_at'])) : '-' ?></td>
                            <td class="text-center">
                                <a href="nursing-focus-form.php?an=<?= urlencode($an) ?>&id=<?= $row['id'] ?>&loginname=<?= urlencode($loginname) ?>"
                                   target="_blank"
                                   class="btn btn-xs btn-outline-primary" style="font-size:0.78rem; padding:2px 8px;">
                                    <i class="fas fa-edit"></i> เปิด/แก้ไข
                                </a>
                                <a href="../pdffile/ors-nursing-focus-pdf.php?an=<?= urlencode($an) ?>&id=<?= $row['id'] ?>&loginname=<?= urlencode($loginname) ?>"
                                   target="_blank"
                                   class="btn btn-xs btn-outline-danger mt-1" style="font-size:0.78rem; padding:2px 8px;">
                                    <i class="fas fa-file-pdf"></i> PDF
                                </a>
                                <?php if ($canEdit && \ReportQueryUtils::checkReadOnly($an) && $row['created_by'] === $loginname): ?>
                                <button type="button" onclick="deleteRecord(<?= $row['id'] ?>, '<?= date('d/m/Y', strtotime($row['visit_date'] ?: 'now')) ?> <?= htmlspecialchars($row['shift'] ?: '') ?>')"
                                        class="btn btn-xs btn-outline-secondary mt-1" style="font-size:0.78rem; padding:2px 8px; color:#dc3545; border-color:#dc3545;">
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
    }).then(function(result) {
        if (result.isConfirmed) {
            $.post('nursing-focus-delete.php',
                { id: id, an: '<?= addslashes($an) ?>' },
                function(resp) {
                    try {
                        var data = (typeof resp === 'string') ? JSON.parse(resp) : resp;
                        if (data.status === 'success') {
                            Swal.fire('ลบแล้ว', 'ลบรายการเรียบร้อยแล้ว', 'success').then(function(){
                                location.reload();
                            });
                        } else {
                            Swal.fire('ข้อผิดพลาด', data.message || 'ไม่สามารถลบได้', 'error');
                        }
                    } catch(e) {
                        Swal.fire('ข้อผิดพลาด', 'ไม่สามารถอ่านผลลัพธ์จากเซิร์ฟเวอร์', 'error');
                    }
                }
            );
        }
    });
}

// ---- Highlight & scroll to newly saved record ----
<?php if ($new_id): ?>
$(document).ready(function() {
    var $row = $('#row-<?= $new_id ?>');
    if ($row.length) {
        // Scroll into view
        $('html, body').animate({ scrollTop: $row.offset().top - 120 }, 400);
        // Flash animation
        $row.css({ transition: 'background-color 0.5s' });
        setTimeout(function() { $row.css('background-color', '#b7e4c7'); }, 100);
        setTimeout(function() { $row.css('background-color', ''); }, 2000);
        // Toast notification
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'บันทึกรายการใหม่เรียบร้อยแล้ว',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    }
});
<?php endif; ?>
</script>
