<?php
        //เวลาตาม timezone
        date_default_timezone_set("Asia/Bangkok");
        require_once '../include/Session.php';
        Session::checkLoginSessionAndShowMessage(); //เช็ค session

        require_once '../include/DbUtils.php';
        require_once '../include/KphisQueryUtils.php';

        $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
       // $an = '660005698';
        $an = $_REQUEST['an'];
        $hn = KphisQueryUtils::getHnByAn($an);   // function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา
        $id = $_REQUEST['id'];

        $appearance = empty($_REQUEST['appearance']) ? null : $_REQUEST['appearance'];
        $dress = empty($_REQUEST['dress']) ? null : $_REQUEST['dress']; 
       // $appearance_check  = empty($_REQUEST['appearance_check']) ? null : $_REQUEST['appearance_check']; 

        $appearance_check = '1';
                   
        //$family_history = 'aaa';    
        $update_datetime= date('Y-m-d H:i:s');
        $create_user = $_SESSION['loginname'];
        $update_user = $_SESSION['loginname'];
        $version0 = $_REQUEST['version'];
        $version = $version0 + 1;

        try {
          //เรียกใช้งาน sql update
if ( $an != '' 
) {
        $output_error = '<script>
        NotificationMessage("บันทึกข้อมูลสำเร็จ", "success");     
        </script>';
$stmt = $conn->prepare("UPDATE ".DbConstant::KPHIS_DBNAME.".prs_mental_health1 SET an=:an,hn=:hn,appearance=:appearance
         ,dress=:dress,appearance_check=:appearance_check
          ,update_user=:update_user,version=:version,update_datetime=:update_datetime
          WHERE id=:id");
          //execute array
          $stmt->execute(array('id'=>$id,'an'=>$an,'hn'=>$hn,'appearance'=>$appearance
          ,'dress'=>$dress,'appearance_check'=>$appearance_check
          ,'update_user'=>$update_user,'version'=>$version,'update_datetime' => $update_datetime
          ));


            Session::insertSystemAccessLog(json_encode(array(
                'form'=>'MENTAL-HEALTH1-FORM',
                'action'=>'UPDATE',
                'version'=>$version,
                'an'=>$an,
            ),JSON_UNESCAPED_UNICODE));


} else {

       echo   '<script>
        alert("กรุณากรอกข้อมูลให้ครบถ้วน", "error");     
        </script>'; 
}

          


     /*     Session::insertSystemAccessLog(json_encode(array(
            'form'=>'LR-REPORT1-FORM',
            'action'=>'UPDATE',
            'version'=>$version,
            'an'=>$an,
        ),JSON_UNESCAPED_UNICODE));
*/
            //เมื่อ update สำเร็จ
         // $output_error = '<div class="alert alert-success">บันทึกข้อมูลเรียบร้อยแล้วคะ</div>';
        

        }catch (PDOException  $e) {
//เมื่อเกิดข้อผิดพลาด
echo $e->getMessage();
//$output_error = '<div class="alert alert-danger">ERROR !!</div>';

$output_error = '<script>NotificationMessage("บันทึกข้อมูลไม่สำเร็จ", "error")</script>';

        }

        echo $output_error;


        ?>
        