<?php
        //เวลาตาม timezone
        date_default_timezone_set("Asia/Bangkok");
        require_once './include/Session.php';
        Session::checkLoginSessionAndShowMessage(); //เช็ค session

        require_once './include/DbUtils.php';
        require_once './include/KphisQueryUtils.php';

        $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
       // $an = '660005698';
        $an = $_REQUEST['an'];
        $hn = KphisQueryUtils::getHnByAn($an);   // function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา
        $id = $_REQUEST['id'];

         
        
        $update_datetime= date('Y-m-d H:i:s');
        $create_user = $_SESSION['loginname'];
        $update_user = $_SESSION['loginname'];
        $version0 = $_REQUEST['version'];
        $version = $version0 + 1;

        try {
          //เรียกใช้งาน sql update
if ( $update_user != '' 
) {
        $output_error = '<script>
        NotificationMessage("บันทึกข้อมูลสำเร็จ", "success");     
        </script>';

          if(!empty($_REQUEST['doctor'])){
                $stmt_item_check = "SELECT COUNT(*) AS count_item,max(id) AS count_id FROM ".DbConstant::KPHIS_DBNAME.".prs_signature WHERE an=:an";
                $stmt_item_check = $conn->prepare($stmt_item_check);
                $stmt_item_check->execute(['an'=>$an]);
                $row_item_check = $stmt_item_check->fetch();
                $count_item = $row_item_check['count_item'];
                $count_id = $row_item_check['count_id'];
                if($count_item > 0){

                    $stmt_item_delete = "DELETE FROM ".DbConstant::KPHIS_DBNAME.".prs_signature WHERE an=:an";
                    $stmt_item_delete = $conn->prepare($stmt_item_delete);
                    $stmt_item_delete->execute(['an'=>$an]);

                  //  print_r('aa');

                }

                print_r('aa');
                foreach($_REQUEST['doctor'] as $doctor){

                        $stmt_item = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".prs_signature(id,an,doctor,create_user,create_datetime,update_user,update_datetime,version)
                        VALUES (:id,:an,:doctor,:create_user,now(),:update_user,now(),:version)");
                        $stmt_item->execute(array('id'=>$id,'an'=>$an,'doctor'=>$doctor
                        ,'create_user'=>$create_user, 'update_user'=>$update_user, 'version'=>$version
                        ));
                    } 

                
            }

            Session::insertSystemAccessLog(json_encode(array(
                'form'=>'PRE-NURSENOTE-FORM',
                'action'=>'UPDATE',
                'version'=>$version,
                'an'=>$an,
            ),JSON_UNESCAPED_UNICODE));


} else {

       echo   '<script>
        alert("กรุณากรอกข้อมูลให้ครบถ้วน", "error");     
        </script>'; 
}

        

        }catch (PDOException  $e) {
//เมื่อเกิดข้อผิดพลาด
echo $e->getMessage();
//$output_error = '<div class="alert alert-danger">ERROR !!</div>';

$output_error = '<script>NotificationMessage("บันทึกข้อมูลไม่สำเร็จ", "error")</script>';

        }

        echo $output_error;


        ?>
        