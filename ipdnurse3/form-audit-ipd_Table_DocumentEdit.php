<?php
require_once '../include/Session.php';
require_once '../include/DbUtils.php';
$conn = DbUtils::get_hosxp_connection();
$an = $_POST['an'];

$sql = "SELECT id, audit_date, sum_score, full_score, audit_by, created_at 
        FROM prs_audit_ipd 
        WHERE an = :an 
        ORDER BY audit_date DESC, id DESC";
$stmt = $conn->prepare($sql);
$stmt->execute(['an' => $an]);

$i = 1;
while($row = $stmt->fetch()):
?>
<tr>
    <td class="text-center"><?= $i++ ?></td>
    <td><?= htmlspecialchars($row['audit_date']) ?></td>
    <td class="text-center"><b><?= htmlspecialchars($row['sum_score']) ?> / <?= htmlspecialchars($row['full_score']) ?></b></td>
    <td class="text-center"><?= htmlspecialchars($row['audit_by']) ?></td>
    <td><?= htmlspecialchars($row['created_at']) ?></td>
    <td class="text-center">
        <a href="form-audit-ipd.php?an=<?= htmlspecialchars($an) ?>&id=<?= $row['id'] ?>&loginname=<?= $_SESSION['loginname'] ?>" target="_blank" class="btn btn-primary btn-sm">
            <i class="fas fa-edit"></i> แก้ไข
        </a>
    </td>
</tr>
<?php endwhile; 
if ($i == 1):
?>
<tr><td colspan="6" class="text-center text-muted">ไม่พบข้อมูลการตรวจประเมิน</td></tr>
<?php endif; ?>
