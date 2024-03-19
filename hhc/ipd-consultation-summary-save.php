<?php   require_once '../include/Session.php';
        Session::checkLoginSessionAndShowMessage(); //เช็ค session
        if(!(
                Session::checkPermission('ADMISSION_NOTE','ADD')
            )){
            return;
        }
        require_once '../include/DbUtils.php';
        require_once '../include/KphisQueryUtils.php';

        $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล

        $an = $_REQUEST['an'];
        $hn = $_REQUEST['hn'];
        $rxdate = $_REQUEST['rxdate'];
        $ward = $_REQUEST['ward'];
        $problem_summary= $_REQUEST['problem_summary'];
        $plan_management = $_REQUEST['plan_management'];
        $care_plan = $_REQUEST['care_plan'];
        
   
        
        $create_user = $_SESSION['loginname'];
        $nurse_name = empty($_REQUEST['nurse_name']) ? null : $_REQUEST['nurse_name'];
        $nurse_pos = empty($_REQUEST['nurse_pos']) ? null : $_REQUEST['nurse_pos'];
        $update_user = $_SESSION['loginname'];
        $version = 1;
     
        try {

            $stmt = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".ipd_consultation_summary
                (hn,an,rxdate,ward,problem_summary,plan_management,care_plan)
                VALUES (:hn,:an,:rxdate,:ward,:problem_summary,:plan_management,:care_plan)");
                $stmt->execute(array('hn'=>$hn,'an'=>$an,'rxdate'=>$rxdate,'ward'=>$ward,'problem_summary'=>$problem_summary,'plan_management'=>$plan_management
            ,'care_plan'=>$care_plan
            ));
                /*$stmt = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".ipd_consultation_summary
                (hn,an,rxdate,create_user,nurse_name,nurse_pos,update_user,create_datetime,update_datetime,version
                )
                VALUES (:hn,:an,:rxdate,:create_user,:nurse_name,:nurse_pos,:update_user,:create_datetime,:update_datetime,:version
                )");
                $stmt->execute(array('hn'=>$hn, 'an'=>$an, 'rxdate'=>$rxdate, 'create_user'=>$create_user,'nurse_name'=>$nurse_name,'nurse_pos'=>$nurse_pos,'update_user'=>$update_user,'version'=>$version
            ));*/

               // $admission_note_id = $conn->lastInsertId();

          /*      if(!empty($_REQUEST['admission_note_doctor'])){
                    foreach($_REQUEST['admission_note_doctor'] as $admission_note_doctor){
                        $stmt_item = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".ipd_consultation_summary_item(id,an,doctor,create_user,create_datetime,update_user,update_datetime,version)
                        VALUES (:admission_note_id,:an,:admission_note_doctor,:create_user,now(),:update_user,now(),:version)");
                        $stmt_item->execute(array('admission_note_id'=>$admission_note_id, 'an'=>$an,
                        'admission_note_doctor'=>$admission_note_doctor,
                        'create_user'=>$create_user, 'update_user'=>$update_user, 'version'=>$version));
                    }
                }
*/
                $output_error = '<div class="alert alert-success">บันทึกข้อมูลเรียบร้อยแล้วคะ</div>';

            } catch (PDOException  $e) {
                echo $e->getMessage();
                $output_error = '<div class="alert alert-danger">ERROR !!</div>';
            }
        echo $output_error;
?>