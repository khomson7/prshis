<?php
require_once '../include/Session.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$loginname = isset($_SESSION['loginname']) ? $_SESSION['loginname'] : null;

require_once '../mains/main-report.php';
$permissionCheck     = Session::checkPermissionAndShowMessage('IMAGE_ANNOT', 'VIEW');
$permissionCheckJson = json_encode($permissionCheck);

require_once '../mains/ipd-show-patient-main.php';
require_once '../mains/ipd-show-patient-sticky.php';
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';
require_once '../include/ReportQueryUtils.php';
require_once '../include/session-modal.php';

$an     = isset($_REQUEST['an'])     ? trim($_REQUEST['an']) : '';
$new_id = isset($_REQUEST['new_id']) ? (int)$_REQUEST['new_id'] : 0;
$records = [];

try {
    $conn = DbUtils::get_hosxp_connection();

    Session::insertSystemAccessLog(json_encode([
        'form' => 'IMAGE-ANNOT-MAIN',
        'an'   => $an,
    ], JSON_UNESCAPED_UNICODE));

    $stmt = $conn->prepare(
        "SELECT id, note, created_at, created_by, updated_at, updated_by
           FROM prs_image_annot
          WHERE an = :an AND is_deleted = 0
          ORDER BY id DESC"
    );
    $stmt->execute(['an' => $an]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    echo '<div class="alert alert-danger m-3">Database Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
}

$canEdit = Session::checkPermission('IMAGE_ANNOT', 'EDIT');
?>

<script src="../node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
<link  rel="stylesheet" href="../node_modules/sweetalert2/dist/sweetalert2.min.css">

<style>
.iam-header {
    background: #1a6b3a;
    color: #fff;
    padding: 10px 16px;
    border-radius: 4px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.iam-header h5 { margin: 0; font-weight: bold; font-size: 1rem; }
.iam-header small { opacity: .8; font-size: 0.78rem; }
.iam-table th {
    background: #1a6b3a;
    color: #fff;
    font-size: 0.85rem;
    white-space: nowrap;
    vertical-align: middle;
}
.iam-table td { vertical-align: middle; font-size: 0.88rem; }
.note-preview {
    max-width: 260px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: #495057;
}
</style>

<div id="formContainer">
<div class="container-fluid py-2">

    <!-- Header -->
    <div class="iam-header">
        <div>
            <h5><i class="fas fa-images mr-2"></i>บันทึกภาพและ Annotation</h5>
            <small>AN: <?= htmlspecialchars($an) ?></small>
        </div>
        <div>
            <?php if ($canEdit && ReportQueryUtils::checkReadOnly($an)): ?>
            <a href="form-image-annot.php?an=<?= urlencode($an) ?>&loginname=<?= urlencode($loginname) ?>"
               target="_blank"
               class="btn btn-sm btn-light shadow-sm">
                <i class="fas fa-plus"></i> เพิ่มรายการใหม่
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-sm table-hover mb-0 iam-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:46px;">#</th>
                            <th>บันทึกโดย</th>
                            <th>วันที่บันทึก</th>
                            <th>แก้ไขล่าสุด</th>
                            <th>หมายเหตุ</th>
                            <th class="text-center" style="width:150px;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($records)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-images fa-2x mb-2 d-block"></i>
                                ยังไม่มีข้อมูล — กดปุ่ม "เพิ่มรายการใหม่" เพื่อบันทึกภาพ
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($records as $row): ?>
                        <tr id="row-<?= $row['id'] ?>"
                            <?= ($new_id && $row['id'] == $new_id) ? 'class="table-success new-record"' : '' ?>>
                            <td class="text-center text-muted"><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['created_by'] ?: '-') ?></td>
                            <td><?= $row['created_at'] ? date('d/m/Y H:i', strtotime($row['created_at'])) : '-' ?></td>
                            <td>
                                <?php if ($row['updated_at']): ?>
                                    <?= date('d/m/Y H:i', strtotime($row['updated_at'])) ?>
                                    <?php if ($row['updated_by']): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($row['updated_by']) ?></small>
                                    <?php endif; ?>
                                <?php else: echo '-'; endif; ?>
                            </td>
                            <td>
                                <div class="note-preview" title="<?= htmlspecialchars($row['note'] ?? '') ?>">
                                    <?= $row['note'] ? htmlspecialchars($row['note']) : '<span class="text-muted">-</span>' ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <a href="form-image-annot.php?an=<?= urlencode($an) ?>&id=<?= $row['id'] ?>&loginname=<?= urlencode($loginname) ?>"
                                   target="_blank"
                                   class="btn btn-xs btn-outline-primary" style="font-size:0.78rem; padding:2px 8px;">
                                    <i class="fas fa-edit"></i> เปิด/แก้ไข
                                </a>
                                <a href="../pdffile/image-annot-pdf.php?an=<?= urlencode($an) ?>&id=<?= $row['id'] ?>&loginname=<?= urlencode($loginname) ?>"
                                   target="_blank"
                                   class="btn btn-xs btn-outline-danger mt-1" style="font-size:0.78rem; padding:2px 8px;">
                                    <i class="fas fa-file-pdf"></i> PDF
                                </a>
                                <?php if ($canEdit && ReportQueryUtils::checkReadOnly($an) && $row['created_by'] === $loginname): ?>
                                <button type="button"
                                        onclick="deleteRecord(<?= $row['id'] ?>, '<?= date('d/m/Y H:i', strtotime($row['created_at'])) ?>')"
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
        html: 'ต้องการลบรายการ บันทึกเมื่อ <b>' + label + '</b> ใช่หรือไม่?<br>' +
              '<small class="text-danger">การกระทำนี้ไม่สามารถยกเลิกได้</small>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-trash-alt"></i> ลบ',
        cancelButtonText: 'ยกเลิก'
    }).then(function(result) {
        if (result.isConfirmed) {
            $.post('form-image-annot-delete.php',
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
        $('html, body').animate({ scrollTop: $row.offset().top - 120 }, 400);
        $row.css({ transition: 'background-color 0.5s' });
        setTimeout(function() { $row.css('background-color', '#b7e4c7'); }, 100);
        setTimeout(function() { $row.css('background-color', ''); }, 2500);
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
