<?php
require_once '../include/Session.php';
require_once '../include/session-sso.php';
$an = $_POST['an'];
$loginname = $_SESSION['loginname'];
?>
<a class="dropdown-item" href="form-audit-ipd.php?an=<?= htmlspecialchars($an) ?>&loginname=<?= htmlspecialchars($loginname) ?>" target="_blank">
    สร้างรายการใหม่ (New Audit)
</a>

