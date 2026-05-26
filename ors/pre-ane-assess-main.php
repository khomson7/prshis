<?php
require_once '../include/Session.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$loginname = isset($_SESSION['loginname']) ? $_SESSION['loginname'] : null;

require_once '../mains/main-report.php';
$permissionCheck = Session::checkPermissionAndShowMessage('IPD_NURSE_ADDMISSION_NOTE', 'VIEW');

require_once '../mains/ipd-show-patient-main.php';
require_once '../mains/ipd-show-patient-sticky.php';
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';
require_once '../include/ReportQueryUtils.php';
require_once '../include/session-modal.php';

$an      = isset($_REQUEST['an']) ? trim($_REQUEST['an']) : '';
$new_id  = isset($_REQUEST['new_id']) ? (int)$_REQUEST['new_id'] : 0;
$hn      = '';
$records = array();

try {
    $conn = DbUtils::get_hosxp_connection();
    $hn   = KphisQueryUtils::getHnByAn($an);

    Session::insertSystemAccessLog(json_encode(array(
        'form' => 'PRE-ANE-ASSESS-MAIN',
        'an'   => $an,
    ), JSON_UNESCAPED_UNICODE));

    $stmt = $conn->prepare(
        "SELECT id, create_datetime, update_datetime, create_user, update_user,
                visitor_name, attending_physician, asa_class
           FROM " . DbConstant::KPHIS_DBNAME . ".prs_pre_ane_assess
          WHERE an = :an AND (is_deleted = 0 OR is_deleted IS NULL)
          ORDER BY create_datetime DESC, id DESC"
    );
    $stmt->execute(array('an' => $an));
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    echo '<div class="alert alert-danger m-3">Database Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
}

$canEdit = Session::checkPermission('IPD_NURSE_ADDMISSION_NOTE', 'EDIT');
?>

<script src="../node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
<link  rel="stylesheet" href="../node_modules/sweetalert2/dist/sweetalert2.min.css">

<style>
.paa-header {
    background: linear-gradient(135deg, #2c5282, #2b6cb0);
    color: #fff;
    padding: 10px 16px;
    border-radius: 4px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.paa-header h5 { margin: 0; font-weight: bold; font-size: 1rem; }
.paa-header small { opacity: .8; font-size: 0.78rem; }
.paa-table th {
    background: #2c5282;
    color: #fff;
    font-size: 0.85rem;
    white-space: nowrap;
    vertical-align: middle;
}
.paa-table td { vertical-align: middle; font-size: 0.88rem; }
</style>

<div id="formContainer">
<div class="container-fluid py-2">

    <!-- Header -->
    <div class="paa-header">
        <div>
            <h5><i class="fas fa-clipboard-check mr-2"></i>แบบบันทึกการเตรียมผู้ป่วยก่อนให้ยาระงับความรู้สึก (FM-NSO-ANE-001-05)</h5>
            <small>AN: <?= htmlspecialchars($an) ?></small>
        </div>
        <div>
            <?php if ($canEdit && \ReportQueryUtils::checkReadOnly($an)): ?>
            <a href="pre-ane-assess.php?an=<?= urlencode($an) ?>&loginname=<?= urlencode($loginname) ?>"
               onclick="window.open(this.href, 'pre_ane_window'); return false;"
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
                <table class="table table-bordered table-sm table-hover mb-0 paa-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:50px;">#</th>
                            <th>บันทึกเมื่อ</th>
                            <th>ASA Class</th>
                            <th>ผู้เยี่ยม</th>
                            <th>Attending Physician</th>
                            <th>ผู้บันทึก</th>
                            <th class="text-center" style="width:160px;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($records)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                ยังไม่มีข้อมูล — กดปุ่ม "เพิ่มรายการใหม่" เพื่อบันทึก
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($records as $row): ?>
                        <tr id="row-<?= $row['id'] ?>"
                            <?= ($new_id && $row['id'] == $new_id) ? 'class="table-info new-record"' : '' ?>>
                            <td class="text-center text-muted"><?= $no++ ?></td>
                            <td><?= $row['create_datetime'] ? date('d/m/Y H:i', strtotime($row['create_datetime'])) : '-' ?></td>
                            <td class="text-center"><?= htmlspecialchars($row['asa_class'] ?: '-') ?></td>
                            <td><?= htmlspecialchars($row['visitor_name'] ?: '-') ?></td>
                            <td><?= htmlspecialchars($row['attending_physician'] ?: '-') ?></td>
                            <td><?= htmlspecialchars($row['create_user'] ?: '-') ?></td>
                            <td class="text-center">
                                <a href="pre-ane-assess.php?an=<?= urlencode($an) ?>&id=<?= $row['id'] ?>&loginname=<?= urlencode($loginname) ?>"
                                   onclick="window.open(this.href, 'pre_ane_window_<?= $row['id'] ?>'); return false;"
                                   class="btn btn-xs btn-outline-primary" style="font-size:0.78rem; padding:2px 8px;">
                                    <i class="fas fa-edit"></i> เปิด/แก้ไข
                                </a>
                                <?php if ($canEdit && \ReportQueryUtils::checkReadOnly($an)): ?>
                                <button type="button" onclick="deleteRecord(<?= $row['id'] ?>, '<?= date('d/m/Y H:i', strtotime($row['create_datetime'] ?: 'now')) ?>')"
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
        html: 'ต้องการลบรายการ <b>' + label + '</b> ใช่หรือไม่?<br><small class="text-danger">ข้อมูลจะถูกซ่อน (Soft Delete)</small>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-trash-alt"></i> ลบ',
        cancelButtonText: 'ยกเลิก'
    }).then(function(result) {
        if (result.isConfirmed) {
            $.post('pre-ane-assess-delete.php',
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
</script>
