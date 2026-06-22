<?php
require_once '../include/Session.php';
require_once '../include/session-sso.php';
Session::checkLoginSessionAndShowMessage();

// สิทธิ์สำหรับการพิมพ์เอกสาร แนะนำให้ตั้งแยกจากสิทธิ์การตั้งค่า (SETUP_PRINT_GROUP)
$permissionCheck = Session::checkPermissionAndShowMessage('PRINT_GROUP', 'VIEW');
if (!$permissionCheck['hasPermission']) {
    require_once '../mains/main-report.php'; // โหลด Navbar ก่อนแสดง Error
    echo '<div class="container mt-5"><div class="alert alert-danger text-center shadow-sm"><i class="fas fa-lock fa-2x mb-3"></i><br><h5>' . htmlspecialchars($permissionCheck['message']) . '</h5></div></div>';
    exit;
}

$loginname = isset($_SESSION['loginname']) ? $_SESSION['loginname'] : null;

require_once '../mains/main-report.php'; // For top nav/layout if needed
require_once '../mains/ipd-show-patient-main.php'; // Patient header
require_once '../mains/ipd-show-patient-sticky.php'; // Sticky header
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';

$an = isset($_REQUEST['an']) ? trim($_REQUEST['an']) : '';
$hn = KphisQueryUtils::getHnByAn($an);

if (!$an) {
    echo "กรุณาระบุ AN";
    exit;
}

$conn = DbUtils::get_hosxp_connection();

// ตรวจสอบและสร้างตารางถ้ายังไม่มี
try {
    $conn->exec("
    CREATE TABLE IF NOT EXISTS prs_group_print_index (
        group_print INT PRIMARY KEY,
        group_name VARCHAR(255) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

    $conn->exec("
    CREATE TABLE IF NOT EXISTS prs_group_print_item (
        id INT AUTO_INCREMENT PRIMARY KEY,
        group_print INT,
        pdf_script VARCHAR(255) NOT NULL,
        document_name VARCHAR(255) NOT NULL,
        sort_order INT DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

    // เพิ่มข้อมูลพื้นฐานถ้าตารางว่าง
    $check = $conn->query("SELECT COUNT(*) FROM prs_group_print_index")->fetchColumn();
    if ($check == 0) {
        $conn->exec("INSERT INTO prs_group_print_index (group_print, group_name) VALUES (1, 'กลุ่มประเมินทั่วไป'), (2, 'กลุ่มเฉพาะทาง')");
        $conn->exec("INSERT INTO prs_group_print_item (group_print, pdf_script, document_name, sort_order) VALUES 
            (1, 'alcohol-pdf.php', 'แบบประเมิน Alcohol', 1),
            (1, 'barthel-pdf.php', 'แบบประเมิน Barthel', 2),
            (1, 'bedscore-pdf.php', 'แบบประเมิน Bed score', 3),
            (2, 'ca-breast-pdf.php', 'รายงาน CA Breast', 1),
            (2, 'felldown-pdf.php', 'รายงาน Felldown', 2),
            (2, 'med-action-pdf.php', 'รายงาน Med Action', 3)
        ");
    }
} catch (PDOException $e) {
    // Ignore error if permissions are denied
}

// ดึงข้อมูลกลุ่ม
$groups = [];
$stmt = $conn->query("
    SELECT i.group_print, i.group_name, t.pdf_script, t.document_name 
    FROM prs_group_print_index i
    LEFT JOIN prs_group_print_item t ON i.group_print = t.group_print
    ORDER BY i.group_print, t.sort_order
");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if (!isset($groups[$row['group_print']])) {
        $groups[$row['group_print']] = [
            'group_name' => $row['group_name'],
            'items' => []
        ];
    }
    if ($row['pdf_script']) {
        $groups[$row['group_print']]['items'][] = [
            'pdf_script' => $row['pdf_script'],
            'document_name' => $row['document_name']
        ];
    }
}
?>
<script src="../node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
<link  rel="stylesheet" href="../node_modules/sweetalert2/dist/sweetalert2.min.css">

<div class="container-fluid mb-5">
    <div class="row align-items-center mb-3">
        <div class="col">
            <h5 class="mb-0"><i class="fas fa-print text-primary"></i> จัดการพิมพ์เอกสารแบบกลุ่ม</h5>
        </div>
    </div>

    <div class="row">
        <?php foreach ($groups as $groupId => $group): ?>
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center py-2">
                    <h6 class="mb-0 font-weight-bold">กลุ่ม: <?= htmlspecialchars($group['group_name']) ?></h6>
                    <button class="btn btn-light btn-sm text-info font-weight-bold" onclick="printGroup(<?= $groupId ?>)">
                        <i class="fas fa-print"></i> พิมพ์เอกสารที่เลือก
                    </button>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover table-bordered mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th width="10%" class="text-center">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="checkAll_<?= $groupId ?>" checked onclick="toggleGroup(<?= $groupId ?>, this.checked)">
                                        <label class="custom-control-label" for="checkAll_<?= $groupId ?>"></label>
                                    </div>
                                </th>
                                <th>ชื่อเอกสาร (PDF)</th>
                                <th width="20%" class="text-center">พิมพ์เดี่ยว</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($group['items'])): foreach($group['items'] as $index => $item): ?>
                            <tr>
                                <td class="text-center align-middle">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input chk-group-<?= $groupId ?>" id="chk_<?= $groupId ?>_<?= $index ?>" value="<?= htmlspecialchars($item['pdf_script']) ?>" checked>
                                        <label class="custom-control-label" for="chk_<?= $groupId ?>_<?= $index ?>"></label>
                                    </div>
                                </td>
                                <td class="align-middle">
                                    <?= htmlspecialchars($item['document_name']) ?> <br>
                                    <small class="text-muted"><?= htmlspecialchars($item['pdf_script']) ?></small>
                                </td>
                                <td class="text-center align-middle">
                                    <a href="../<?= htmlspecialchars($item['pdf_script']) ?>?an=<?= urlencode($an) ?>&loginname=<?= urlencode($loginname) ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="พิมพ์เดี่ยว">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="3" class="text-center text-muted py-3">ไม่มีเอกสารในกลุ่มนี้</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function toggleGroup(groupId, isChecked) {
    $('.chk-group-' + groupId).prop('checked', isChecked);
}

function printGroup(groupId) {
    var selected = [];
    $('.chk-group-' + groupId + ':checked').each(function() {
        selected.push($(this).val());
    });
    
    if(selected.length === 0) {
        Swal.fire('แจ้งเตือน', 'กรุณาเลือกเอกสารที่ต้องการพิมพ์อย่างน้อย 1 รายการ', 'warning');
        return;
    }

    var scripts = selected.join(',');
    var an = encodeURIComponent("<?= htmlspecialchars($an) ?>");
    var url = '../pdffile/print-group-merge-pdf.php?an=' + an + '&group_id=' + groupId + '&scripts=' + encodeURIComponent(scripts);
    
    // เปิดหน้า PDF ที่ทำการ Merge แล้วใน Tab ใหม่
    window.open(url, '_blank');
}
</script>

