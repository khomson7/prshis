<?php
require_once '../include/Session.php';
require_once '../include/session-sso.php';
$loginname = isset($_SESSION['loginname']) ? $_SESSION['loginname'] : null;

require_once '../mains/main-report.php';
$permissionCheck = Session::checkPermissionAndShowMessage('FORM_CAPRINI', 'VIEW');

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
        'form' => 'CAPRINI-SCORE-MAIN',
        'an'   => $an,
    ), JSON_UNESCAPED_UNICODE));

    $stmt = $conn->prepare(
        "SELECT id, assessment_date, assessment_time, total_score, risk_level 
           FROM prs_caprini_score
          WHERE an = :an
          ORDER BY id DESC"
    );
    $stmt->execute(array('an' => $an));
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    echo '<div class="alert alert-danger m-3">Database Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
}

$canEdit = Session::checkPermission('FORM_CAPRINI', 'EDIT');
// If no specific edit permission exists, allow it based on view or just true for now
if (is_null($canEdit) || $canEdit === false) {
    $canEdit = true; // Temporary fallback based on form-caprini-score.php where there was only 'VIEW' check
}
?>

<script src="../node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
<link  rel="stylesheet" href="../node_modules/sweetalert2/dist/sweetalert2.min.css">

<style>
.caprini-header {
    background: linear-gradient(135deg, #1976D2, #1565C0);
    color: #fff;
    padding: 10px 16px;
    border-radius: 4px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.caprini-header h5 { margin: 0; font-weight: bold; font-size: 1rem; }
.caprini-header small { opacity: .8; font-size: 0.78rem; }
.caprini-table th {
    background: #1976D2;
    color: #fff;
    font-size: 0.85rem;
    white-space: nowrap;
    vertical-align: middle;
}
.caprini-table td { vertical-align: middle; font-size: 0.88rem; }
</style>

<div id="formContainer">
<div class="container-fluid py-2">

    <!-- Header -->
    <div class="caprini-header">
        <div>
            <h5><i class="fas fa-clipboard-check mr-2"></i>ประวัติการประเมิน Caprini Score</h5>
            <small>AN: <?= htmlspecialchars($an) ?></small>
        </div>
        <div>
            <?php if ($canEdit && \ReportQueryUtils::checkReadOnly($an)): ?>
            <a href="form-caprini-score.php?an=<?= urlencode($an) ?>"
               onclick="window.open(this.href, 'caprini_window'); return false;"
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
                <table class="table table-bordered table-sm table-hover mb-0 caprini-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:50px;">#</th>
                            <th>วันที่ประเมิน</th>
                            <th class="text-center">คะแนนรวม</th>
                            <th>ระดับความเสี่ยง</th>
                            <th class="text-center" style="width:120px;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($records)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                ยังไม่มีข้อมูล — กดปุ่ม "เพิ่มรายการใหม่" เพื่อบันทึก
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($records as $row): ?>
                        <tr id="row-<?= $row['id'] ?>"
                            <?= ($new_id && $row['id'] == $new_id) ? 'class="table-info new-record"' : '' ?>>
                            <td class="text-center text-muted"><?= $no++ ?></td>
                            <td>
                                <?= $row['assessment_date'] ? date('d/m/Y', strtotime($row['assessment_date'])) : '-' ?>
                                <?= $row['assessment_time'] ? date('H:i', strtotime($row['assessment_time'])) . ' น.' : '' ?>
                            </td>
                            <td class="text-center font-weight-bold"><?= htmlspecialchars($row['total_score'] !== null ? $row['total_score'] : '-') ?></td>
                            <td>
                                <?php 
                                    $risk = htmlspecialchars($row['risk_level'] ?: '-'); 
                                    if ($risk == 'Very Low') echo '<span class="badge badge-success">Very Low</span>';
                                    elseif ($risk == 'Low-Moderate') echo '<span class="badge badge-warning">Low-Moderate</span>';
                                    elseif ($risk == 'High') echo '<span class="badge" style="background:#fd7e14; color:#fff;">High</span>';
                                    elseif ($risk == 'Highest') echo '<span class="badge badge-danger">Highest</span>';
                                    else echo $risk;
                                ?>
                            </td>
                            <td class="text-center">
                                <a href="form-caprini-score.php?an=<?= urlencode($an) ?>&id=<?= $row['id'] ?>"
                                   onclick="window.open(this.href, 'caprini_window_<?= $row['id'] ?>'); return false;"
                                   class="btn btn-xs btn-outline-primary" style="font-size:0.78rem; padding:2px 8px;">
                                    <i class="fas fa-edit"></i> เปิด/แก้ไข
                                </a>
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
