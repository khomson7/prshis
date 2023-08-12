<?php
require_once './project/function/SessionManager.php';
SessionManager::checkLoginSessionAndShowMessage(); //เช็ค session
// SessionManager::checkPermissionAndShowMessage('KPHIS_ACCESS_IPD_DOCTOR_ORDER_PROGRAM');
if(!SessionManager::checkPermission('ADMISSION_NOTE_DRUG_ALLERGY','CHECK')){
    SessionManager::responsePermissionErrorForJsonRequest(null);
    exit;
}

require_once './project/function/DbUtils.php';
require_once './project/function/KphisQueryUtils.php';

$conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล

//check for require field
if(empty($_REQUEST['an'])) {
	exit;
}

// print_r($_REQUEST);

$an = $_REQUEST['an'];
$allergy_drug_pharmacy_check_person = $_SESSION['doctorcode'];
$update_user = $_SESSION['loginname'];
$version = 1;

try {
    /**
     * ค้นดูว่ารายการนี้มีการยืนยันแล้วหรือยัง
     * หากมีแล้วให้ แจ้ง Error Message แล้ว reload front end
     */
    $check_sql = "SELECT an
            FROM ".KphisConstant::HOSXP_CONNECTION_KPHIS_DBNAME.".ipd_dr_admission_note
            WHERE an = :an and allergy_drug_pharmacy_check_person is not null ";
    $query_parameters['an'] = $an;
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->execute($query_parameters);

    if($mr_row = $check_stmt->fetch(PDO::FETCH_ASSOC)){
        header('HTTP/1.1 409 Conflict : Already Checked');
    } else {
        $stmt = $conn->prepare("UPDATE ".KphisConstant::HOSXP_CONNECTION_KPHIS_DBNAME.".ipd_dr_admission_note SET
            allergy_drug_pharmacy_check_person=:allergy_drug_pharmacy_check_person,
            allergy_drug_pharmacy_check_datetime=NOW(),
            update_user=:update_user, update_datetime=NOW(), version=(version+1)
            WHERE an=:an");
        $stmt->execute(array(
            'an'=>$an,
            'allergy_drug_pharmacy_check_person'=>$allergy_drug_pharmacy_check_person,
            'update_user'=>$update_user));
    }
} catch (PDOException $e) {
    echo $e->getMessage();
}

?>