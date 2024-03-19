<?php   require_once '../include/Session.php';
        Session::checkLoginSessionAndShowMessage(); //เช็ค session
        if(!(Session::checkPermission('ADMISSION_NOTE','EDIT'))){
            return;
        }
        require_once '../include/DbUtils.php';
        require_once '../include/KphisQueryUtils.php';

        $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล

        $id = $_REQUEST['id'];
 
        $an = $_REQUEST['an'];
        $hn = $_REQUEST['hn'];
        $rxdate = $_REQUEST['rxdate'];
        $ward = $_REQUEST['ward'];
        $problem_summary= $_REQUEST['problem_summary'];
        $plan_management = $_REQUEST['plan_management'];



        
        $create_user = $_SESSION['loginname'];
        $nurse_name = empty($_REQUEST['nurse_name']) ? null : $_REQUEST['nurse_name'];
        $nurse_pos = empty($_REQUEST['nurse_pos']) ? null : $_REQUEST['nurse_pos'];
        $update_user = $_SESSION['loginname'];
        $version = $_REQUEST['version'];
        $version = $version + 1;
            // empty($_REQUEST['']) ? null : $_REQUEST[''];

             

        try {
            $stmt = $conn->prepare("UPDATE ".DbConstant::KPHIS_DBNAME.".ipd_consultation_summary SET hn=:hn, an=:an,
            rxdate=:rxdate, ward=:ward,problem_summary=:problem_summary,
            plan_management=:plan_management
            WHERE id=:id");
            $stmt->execute(array('id'=>$id, 'hn'=>$hn, 'an'=>$an,
            'rxdate'=>$rxdate, 'ward'=>$ward,
            'problem_summary'=>$problem_summary,'plan_management'=>$plan_management
        ));

      /*      if(!empty($_REQUEST['admission_note_doctor'])){
                $stmt_item_check = "SELECT COUNT(*) AS count_item  FROM ".DbConstant::KPHIS_DBNAME.".ipd_dr_admission_note_item WHERE an=:an";
                $stmt_item_check = $conn->prepare($stmt_item_check);
                $stmt_item_check->execute(['an'=>$an]);
                $row_item_check = $stmt_item_check->fetch();
                $count_item = $row_item_check['count_item'];
                if($count_item > 0){
                    $stmt_item_delete = "DELETE FROM ".DbConstant::KPHIS_DBNAME.".ipd_dr_admission_note_item WHERE an=:an";
                    $stmt_item_delete = $conn->prepare($stmt_item_delete);
                    $stmt_item_delete->execute(['an'=>$an]);
                }
                foreach($_REQUEST['admission_note_doctor'] as $admission_note_doctor){
                    $stmt_item = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".ipd_dr_admission_note_item
                    (admission_note_id,an,admission_note_doctor,create_user,create_datetime,update_user,update_datetime,version)
                    VALUES (:admission_note_id,:an,:admission_note_doctor,:create_user,now(),:update_user,now(),:version)");
                    $stmt_item->execute(array('admission_note_id'=>$admission_note_id, 'an'=>$an,
                    'admission_note_doctor'=>$admission_note_doctor,
                    'create_user'=>$create_user, 'update_user'=>$update_user, 'version'=>$version));
                }
            }
*/
            Session::insertSystemAccessLog(json_encode(array(
                'form'=>'IPD-DR-ADMISSION-NOTE-FORM',
                'action'=>'UPDATE',
                'an'=>$an,
            ),JSON_UNESCAPED_UNICODE));

            $output_error = '<div class="alert alert-success">บันทึกข้อมูลเรียบร้อยแล้วคะ</div>';

            } catch (PDOException  $e) {
                echo $e->getMessage();
                $output_error = '<div class="alert alert-danger">ERROR !!</div>';
            }

        echo $output_error;

?>