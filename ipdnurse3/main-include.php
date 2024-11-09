<?php   require_once '../include/Session.php';
       // Session::checkLoginSessionAndShowMessage(); //เช็ค session
        require_once '../include/DbUtils.php';
        require_once '../include/KphisQueryUtils.php';
        require_once '../include/ReportQueryUtils.php';
        //require_once '../include/FormQueryUtils.php';
        $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
        $an = empty($_REQUEST['an']) ? null : $_REQUEST['an'];
        $hn = KphisQueryUtils::getHnByAn($an); 
        $login = $_SESSION['loginname']

?>