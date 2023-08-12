<?php
        require_once './project/function/SessionManager.php';
        SessionManager::checkLoginSessionAndShowMessage(); //เช็ค session
        // SessionManager::checkPermissionAndShowMessage('KPHIS_ACCESS_IPD_NURSE_ACTIVITY');
        if(!(
            // && SessionManager::checkPermission('IPD_NURSE_MAIN_PROGRAM','ACCESS')
            // && SessionManager::checkPermission('IO','ADD')
            // && SessionManager::checkPermission('IO','EDIT')
            SessionManager::checkPermission('IO','VIEW')
            // && SessionManager::checkPermission('IO','REMOVE') 
            )){
            return;
        }
        require_once './project/function/DbUtils.php';
        require_once './project/function/KphisQueryUtils.php';

        $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล

        $an = $_REQUEST['an'];//รับค่า an
        $hn = KphisQueryUtils::getHnByAn($an);// function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา
        $session_loginname = $_SESSION['loginname'];//session ผู้ที่ login เข้าใช้งาน
    
        $query_parameters_io = ['an'=>$an];

        $sql = "SELECT DISTINCT io.io_date 
                FROM ".KphisConstant::HOSXP_CONNECTION_KPHIS_DBNAME.".ipd_io io 
                WHERE io.an = :an 
                union
                select date(now()) as io_date
                order by io_date desc";
        $stmt = $conn->prepare($sql);
        $stmt->execute($query_parameters_io);
        ?>
        <select class="form-control form-control-sm" name="select_search_io_date" id="select_search_io_date" onchange="onclick_vital_sign_io_search()">
        <?php
        while ($row = $stmt->fetch()){

            $io_date = $row['io_date'];
            ?>
                <option value="<?=htmlspecialchars($io_date)?>"><?=htmlspecialchars(date("d/m/Y", strtotime($io_date)))?></option>
            <?php
       
        } 
        ?>
        </select>

                                