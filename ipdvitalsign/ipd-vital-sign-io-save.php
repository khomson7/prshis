<?php
    require_once './project/function/DbUtils.php';
    require_once './project/function/KphisQueryUtils.php';
    require_once './project/function/SessionManager.php';

    $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
    SessionManager::checkLoginSessionAndShowMessage(); //เช็ค session
    if(!(
        // && SessionManager::checkPermission('IPD_NURSE_MAIN_PROGRAM','ACCESS')
        SessionManager::checkPermission('IO','ADD')
        // && SessionManager::checkPermission('IO','EDIT')
        // && SessionManager::checkPermission('IO','VIEW')
        // && SessionManager::checkPermission('IO','REMOVE')
        )){
        return;
    }

    $an = $_REQUEST['io_an'];//รับค่า an
    $hn = KphisQueryUtils::getHnByAn($an);// function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา

    $output_error = '';
    $output_error_history = '';
    //$create_datetime = ใช้ NOW()
    $create_user  = $_SESSION['loginname'];
    //$update_datetime = ใช้ NOW()
    $update_user  = $_SESSION['loginname'];

    $io_date = StringUtils::isBlankOrNull($_REQUEST['io_date']) ? null : $_REQUEST['io_date'];
    $io_time = StringUtils::isBlankOrNull($_REQUEST['io_time']) ? null : $_REQUEST['io_time'];

    //----------รับค่า Parenteral
    $io_parenteral_type = $_REQUEST['io_parenteral_type'];
    $io_parenteral_name = $_REQUEST['io_parenteral_name'];
    $io_parenteral_amount = StringUtils::isBlankOrNull($_REQUEST['io_parenteral_amount']) ? null : $_REQUEST['io_parenteral_amount'];
    $io_parenteral_absorb = StringUtils::isBlankOrNull($_REQUEST['io_parenteral_absorb']) ? null : $_REQUEST['io_parenteral_absorb'];
    $io_parenteral_carry_forward = StringUtils::isBlankOrNull($_REQUEST['io_parenteral_carry_forward']) ? null : $_REQUEST['io_parenteral_carry_forward'];
    $io_parenteral_remark = $_REQUEST['io_parenteral_remark'];
    //----------รับค่า Parenteral

    //----------รับค่า Oral
    $io_oral_name = $_REQUEST['io_oral_name'];
    $io_oral_amount = StringUtils::isBlankOrNull($_REQUEST['io_oral_amount']) ? null : $_REQUEST['io_oral_amount'];
    $io_oral_absorb = StringUtils::isBlankOrNull($_REQUEST['io_oral_absorb']) ? null : $_REQUEST['io_oral_absorb'];
    $io_oral_carry_forward = StringUtils::isBlankOrNull($_REQUEST['io_oral_carry_forward']) ? null : $_REQUEST['io_oral_carry_forward'];
    $io_oral_remark = $_REQUEST['io_oral_remark'];
    //----------รับค่า Oral

    //----------รับค่า Output
    $io_output_type = $_REQUEST['io_output_type'];
    $io_output_amount = StringUtils::isBlankOrNull($_REQUEST['io_output_amount']) ? null : $_REQUEST['io_output_amount'];
    $io_output_remark = $_REQUEST['io_output_remark'];
    //----------รับค่า Output
    $io_version = 1;
    try {
        $stmt = $conn->prepare("INSERT INTO ".KphisConstant::HOSXP_CONNECTION_KPHIS_DBNAME.".ipd_io (io_date,io_time,io_parenteral_type,io_parenteral_name,io_parenteral_amount,io_parenteral_absorb,io_parenteral_carry_forward,io_parenteral_remark,io_oral_name,io_oral_amount,
                                io_oral_absorb,io_oral_carry_forward,io_oral_remark,io_output_type,io_output_amount,io_output_remark,an,create_user,create_datetime,update_user,
                                update_datetime,version)
                                VALUES (:io_date,:io_time,:io_parenteral_type,:io_parenteral_name,:io_parenteral_amount,:io_parenteral_absorb,:io_parenteral_carry_forward,:io_parenteral_remark,:io_oral_name,:io_oral_amount,
                                :io_oral_absorb,:io_oral_carry_forward,:io_oral_remark,:io_output_type,:io_output_amount,:io_output_remark,:an,:create_user,NOW(),:update_user,
                                NOW(),:version)");
        $stmt->execute(array('io_date'=>$io_date, 'io_time'=>$io_time, 'io_parenteral_type'=>$io_parenteral_type, 'io_parenteral_name'=>$io_parenteral_name, 'io_parenteral_amount'=>$io_parenteral_amount,
                                'io_parenteral_absorb'=>$io_parenteral_absorb, 'io_parenteral_carry_forward'=>$io_parenteral_carry_forward, 'io_parenteral_remark'=>$io_parenteral_remark, 'io_oral_name'=>$io_oral_name, 'io_oral_amount'=>$io_oral_amount,
                                'io_oral_absorb'=>$io_oral_absorb, 'io_oral_carry_forward'=>$io_oral_carry_forward, 'io_oral_remark'=>$io_oral_remark, 'io_output_type'=>$io_output_type, 'io_output_amount'=>$io_output_amount,
                                'io_output_remark'=>$io_output_remark, 'an'=>$an, 'create_user'=>$create_user, 'update_user'=>$update_user,
                                'version'=>$io_version));

            $io_id = $conn->lastInsertId();
            try {
                $sql_history_io = $conn->prepare("INSERT INTO history_ipd_io
                                                        SELECT null,NOW(),'I','$update_user', ipd_io.*
                                                        FROM ipd_io
                                                        WHERE io_id = :io_id");
                $sql_history_io->execute(array('io_id'=>$io_id));
                $output_error_history = '<div class="alert alert-success">บันทึกข้อมูลเรียบร้อยแล้วคะ HISTORY</div>';
            } catch (PDOException  $e) {
                echo $e->getMessage();
                $output_error_history = '<div class="alert alert-danger">ERROR !!IO HISTORY</div>';
            }

    }catch (PDOException  $e) {
        echo $e->getMessage();
        $output_error = '<div class="alert alert-danger">ERROR !!</div>';
    }
    echo $output_error;
    echo $output_error_history;
?>