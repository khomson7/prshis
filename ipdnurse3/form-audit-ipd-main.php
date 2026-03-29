<?php   
require_once '../include/Session.php';
$login = empty($_REQUEST['loginname']) ? null : $_REQUEST['loginname'];
$loginname = $_SESSION['loginname'];

if ($login != $loginname) {
    session_start();
    session_destroy();
}
require_once '../mains/main-report.php';
$permissionCheck = Session::checkPermissionAndShowMessage('PRS_FORM_AUDIT_IPD', 'VIEW');
$permissionCheckJson = json_encode($permissionCheck);

require_once '../mains/ipd-show-patient-main.php'; 
require_once '../mains/ipd-show-patient-sticky.php';
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';
require_once '../include/ReportQueryUtils.php';
require_once '../include/session-modal.php';

$conn = DbUtils::get_hosxp_connection();
$an = $_REQUEST['an'];
$hn = KphisQueryUtils::getHnByAn($an);
$vn = KphisQueryUtils::getVnByAn($an);
$menuname = "แบบตรวจประเมินคุณภาพการบันทึกเวชระเบียน (Audit IPD)";
?>

<div id="formContainer">        
    <div class="row">
        <div class="col-sm-12">
            <nav>
                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                    <a class="nav-item nav-link active" id="pills-document-tab" data-toggle="tab" href="#pills-document" role="tab" aria-controls="pills-document" aria-selected="true"><?= htmlspecialchars($menuname) ?></a>
                </div>
            </nav>
            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade show active" id="pills-document" role="tabpanel" aria-labelledby="pills-document-tab">
                    <?php require_once 'form-audit-ipd-document.php';?>
                </div>
            </div>
        </div>
    </div><br>
</div>

<script src="../node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
<link rel="stylesheet" href="../node_modules/sweetalert2/dist/sweetalert2.min.css">
