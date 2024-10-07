<?php   require_once '../include/main-include.php';
        Session::checkLoginSessionAndShowMessage(); //เช็ค session
        require_once '../include/DbUtils.php';
        require_once '../include/KphisQueryUtils.php';
        $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
        $an = empty($_REQUEST['an']) ? null : $_REQUEST['an'];
        $hn = KphisQueryUtils::getHnByAn($an);
        $getDocumentAddmissionNurse = KphisQueryUtils::getDocumentAddmissionNurse($an);
        $loginname = $_SESSION['loginname'];


        if(($an)){
            ?><a class="dropdown-item" href="form-mental-health31.php?an=<?=$an?>&loginname=<?php echo $loginname; ?>" target="_blank"><em class="fas fa-file-alt"></em> แบบประเมินอาการทางจิต(Save)</a><?php
        }

?>