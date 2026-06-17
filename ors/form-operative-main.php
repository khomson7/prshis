<?php
require_once '../include/Session.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$loginname = isset($_SESSION['loginname']) ? $_SESSION['loginname'] : null;

require_once '../mains/main-report.php';
Session::checkPermissionAndShowMessage('OPNOTE', 'VIEW');

require_once '../mains/ipd-show-patient-main.php';
require_once '../mains/ipd-show-patient-sticky.php';
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';
require_once '../include/ReportQueryUtils.php';

$conn = DbUtils::get_hosxp_connection();
$an = isset($_REQUEST['an']) ? trim($_REQUEST['an']) : '';
$hn = KphisQueryUtils::getHnByAn($an);

Session::insertSystemAccessLog(json_encode([
    'form' => 'OPERATIVE-NOTE-MAIN',
    'an'   => $an,
], JSON_UNESCAPED_UNICODE));

$stmt = $conn->prepare("SELECT * FROM prs_operative_note WHERE an = :an AND is_deleted = 0 ORDER BY create_datetime DESC");
$stmt->execute(['an' => $an]);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

function formatSurgeon($jsonStr) {
    if (empty($jsonStr)) return '';
    $arr = json_decode($jsonStr, true);
    if (is_array($arr)) {
        return implode(', ', $arr);
    }
    return $jsonStr;
}
?>
<script src="../node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
<link  rel="stylesheet" href="../node_modules/sweetalert2/dist/sweetalert2.min.css">
<div class="container-fluid mb-5">
    <div class="row align-items-center mb-3">
        <div class="col">
            <h5 class="mb-0"><i class="fas fa-list-alt text-primary"></i> รายการ Operative Note</h5>
        </div>
        <div class="col-auto">
            <?php if (Session::checkPermission('OPNOTE', 'ADD') && ReportQueryUtils::checkReadOnly($an)): ?>
            <a href="form-operative.php?an=<?= urlencode($an) ?>&loginname=<?= urlencode($loginname) ?>" class="btn btn-success shadow-sm">
                <i class="fas fa-plus"></i> เพิ่มบันทึกใหม่
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center" width="5%">#</th>
                            <th width="15%">วันที่ผ่าตัด</th>
                            <th width="15%">เวลา</th>
                            <th width="20%">Surgeon</th>
                            <th width="25%">Operation</th>
                            <th width="10%">ผู้บันทึก</th>
                            <th class="text-center" width="10%">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($records)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">ยังไม่มีประวัติการบันทึก Operative Note</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($records as $index => $row): ?>
                            <tr>
                                <td class="text-center align-middle"><?= $index + 1 ?></td>
                                <td class="align-middle"><?= htmlspecialchars($row['operation_date']) ?></td>
                                <td class="align-middle">
                                    <?= htmlspecialchars(substr($row['time_started'] ?? '', 0, 5)) ?> - 
                                    <?= htmlspecialchars(substr($row['time_ended'] ?? '', 0, 5)) ?>
                                </td>
                                <td class="align-middle"><?= htmlspecialchars(formatSurgeon($row['surgeon'])) ?></td>
                                <td class="align-middle"><?= htmlspecialchars($row['operation_name']) ?></td>
                                <td class="align-middle">
                                    <small><?= htmlspecialchars($row['created_by']) ?><br>
                                    <span class="text-muted"><?= htmlspecialchars($row['create_datetime']) ?></span></small>
                                </td>
                                <td class="text-center align-middle">
                                    <a href="form-operative.php?id=<?= $row['id'] ?>&an=<?= urlencode($an) ?>&loginname=<?= urlencode($loginname) ?>" 
                                       class="btn btn-sm btn-info" title="ดู/แก้ไข">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="../pdffile/operative-pdf.php?an=<?= urlencode($an) ?>&id=<?= $row['id'] ?>&loginname=<?= urlencode($loginname) ?>" 
                                       target="_blank" class="btn btn-sm btn-secondary" title="พิมพ์ PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                    <?php if (($row['created_by'] === $loginname || strtolower($loginname) === 'admin') && Session::checkPermission('OPNOTE', 'REMOVE') && ReportQueryUtils::checkReadOnly($an)): ?>
                                    <button type="button" class="btn btn-sm btn-danger" title="ลบ" onclick="deleteRecord(<?= $row['id'] ?>)">
                                        <i class="fas fa-trash"></i>
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
    </div>
</div>

<script>
function deleteRecord(id) {
    Swal.fire({
        title: 'ยืนยันการลบ',
        text: 'คุณต้องการลบข้อมูลนี้ใช่หรือไม่?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'ลบข้อมูล',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'form-operative-delete.php',
                type: 'POST',
                data: { id: id, an: '<?= htmlspecialchars($an) ?>' },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        Swal.fire({
                            title: 'ลบสำเร็จ',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('ข้อผิดพลาด', res.message || 'ไม่สามารถลบข้อมูลได้', 'error');
                    }
                },
                error: function() {
                    Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'error');
                }
            });
        }
    });
}
</script>
