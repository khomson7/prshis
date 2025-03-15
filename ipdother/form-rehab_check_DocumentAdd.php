<?php   require_once '../include/main-include.php';
        Session::checkLoginSessionAndShowMessage(); //เช็ค session
        require_once '../include/DbUtils.php';
        require_once '../include/KphisQueryUtils.php';
        require_once '../include/ReportQueryUtils.php';
        $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
        $an = empty($_REQUEST['an']) ? null : $_REQUEST['an'];
        $hn = KphisQueryUtils::getHnByAn($an);
        $getDocumentAddmissionNurse = KphisQueryUtils::getDocumentAddmissionNurse($an);
        $loginname = $_SESSION['loginname'];
        //แก้ไข
        $menuname = ReportQueryUtils::getLinkMenu(28);

        if(($an)){
            ?><a class="dropdown-item" href="form-rehab-progression.php?an=<?=$an?>&loginname=<?php echo $loginname; ?>" target="_blank"><em class="fas fa-file-alt"></em> <?= htmlspecialchars($menuname) ?></a><?php
        }

?>