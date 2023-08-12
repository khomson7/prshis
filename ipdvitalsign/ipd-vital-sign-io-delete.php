<?php
    require_once './project/function/SessionManager.php';
    SessionManager::checkLoginSessionAndShowMessage(); //เช็ค session
    // SessionManager::checkPermissionAndShowMessage('KPHIS_ACCESS_IPD_NURSE_ACTIVITY');
    if(!(
        // && SessionManager::checkPermission('IPD_NURSE_MAIN_PROGRAM','ACCESS')
        // && SessionManager::checkPermission('IO','ADD')
        // && SessionManager::checkPermission('IO','EDIT')
        // && SessionManager::checkPermission('IO','VIEW')
        SessionManager::checkPermission('IO','REMOVE') 
        )){
        return;
    }
    require_once './project/function/DbUtils.php';

    $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
  
    $output_error = '';
    $output_error_history = '';

    $io_id = $_REQUEST['io_id'];

    //$update_datetime = ใช้ NOW()
    $update_user  = $_SESSION['loginname'];
    
    //-------------ตรวจสอบเลข version
    $io_version = $_REQUEST['io_version'];//รับค่าเลข version
    $query_parameters = ['io_id' => $io_id];
    $sql_version = "SELECT ipd_io.version
                    FROM ".KphisConstant::HOSXP_CONNECTION_KPHIS_DBNAME.".ipd_io
                    WHERE io_id = :io_id";

    $stmt_version = $conn->prepare($sql_version);
    $stmt_version->execute($query_parameters);
    $row_version = $stmt_version->fetch();
    $version = $row_version['version']; 

    if($io_version == $version){
            try {
                $sql_history_focusnote = $conn->prepare("INSERT INTO ".KphisConstant::HOSXP_CONNECTION_KPHIS_DBNAME.".history_ipd_io
                                                        SELECT null,NOW(),'D','$update_user', ipd_io.* 
                                                        FROM ".KphisConstant::HOSXP_CONNECTION_KPHIS_DBNAME.".ipd_io
                                                        WHERE ipd_io.io_id = :io_id");
                $sql_history_focusnote->execute(array('io_id'=>$io_id));
                            try {                                    
                                $stmt = $conn->prepare("DELETE FROM ".KphisConstant::HOSXP_CONNECTION_KPHIS_DBNAME.".ipd_io WHERE io_id = :io_id");
                                
                                $stmt->execute(array('io_id'=>$io_id));   

                            } catch (PDOException  $e) {
                                echo $e->getMessage();  
                                $output_error = '<div class="alert alert-danger">ERROR !! IO</div>';  
                            }
            
            } catch (PDOException  $e) {
                    echo $e->getMessage();  
                    $output_error_history = '<div class="alert alert-danger">ERROR !! IO</div>';  
            } 

            echo $output_error;
            echo $output_error_history;  
    }else{

        exit;
    }
    
?>                               