<?php
    
    require_once '../include/DbUtils.php';
    require_once '../include/KphisQueryUtils.php';
    require_once '../include/Session.php';

    $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
   // Session::checkLoginSessionAndShowMessage(); //เช็ค session
    
   /*if(!(
            // && SessionManager::checkPermission('IPD_NURSE_MAIN_PROGRAM','ACCESS')
            // && SessionManager::checkPermission('IPD_DISCHARGE_SUMMARY','ADD')
            // && SessionManager::checkPermission('IPD_DISCHARGE_SUMMARY','EDIT')
            Session::checkPermission('IPD_DISCHARGE_SUMMARY','VIEW')
            // && SessionManager::checkPermission('IPD_DISCHARGE_SUMMARY','REMOVE')
            )){
            return;
    } */


    $an = $_REQUEST['an'];
    $query_parameters = ['an' => $an];
    // $sql = "SELECT ol.operation_id,ol.an,oi.name,oi.icd9,ol.enter_date,ol.enter_time,ol.leave_date,ol.leave_time
    $sql = "SELECT ol.operation_id,ol.an,oi.name,oi.icd9,od.begin_datetime,od.end_datetime
            from ".DbConstant::HOSXP_DBNAME.".operation_detail od
            left outer join ".DbConstant::HOSXP_DBNAME.".operation_list ol on ol.operation_id=od.operation_id
            left outer join ".DbConstant::HOSXP_DBNAME.".operation_item oi on oi.operation_item_id=od.operation_item_id
            WHERE ol.an = :an
            order by ol.enter_date,ol.enter_time";
    $stmt = $conn->prepare($sql);
    $stmt->execute($query_parameters);

    while ($row = $stmt->fetch()){
        // echo $oitem = $row['name'].($row['icd9'] != null ? ' [ ICD9 : '.$row['icd9']." ] " : "")." (".date("d/m/Y", strtotime($row['enter_date']))." ".substr($row['enter_time'], 0, -3).") - "." (".date("d/m/Y", strtotime($row['leave_date']))." ".substr($row['leave_time'], 0, -3).")"."\n";
        echo $row['name'].($row['icd9'] != null ? ' [ ICD9 : '.$row['icd9']." ] " : "").
            " (".($row['begin_datetime'] == null ? " " : date("d/m/Y H:i", strtotime($row['begin_datetime']))).") - ".
            " (".($row['end_datetime'] == null ? " " : date("d/m/Y H:i", strtotime($row['end_datetime']))).")"."\n";
    }

    // echo KphisQueryUtils::sql_to_json($conn,$sql,$query_parameters);
?>