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

        $progress_note_item_detail =  empty($_REQUEST['progress_note_item_detail']) ? null : $_REQUEST['progress_note_item_detail'];
        $progress_note_item_detail_2 =  empty($_REQUEST['progress_note_item_detail_2']) ? null : $_REQUEST['progress_note_item_detail_2'];
             
        $update_datetime= date('Y-m-d H:i:s');
        $update_user = $_SESSION['loginname'];
        $version0 = $_REQUEST['version'];
        $version = $version0 + 1;

        try {
          //เรียกใช้งาน sql update
if ( $progress_note_item_detail != '' 
) {
        $output_error = '<script>
        alert("บันทึกข้อมูลสำเร็จ", "success");  
        </script>';

$stmt = $conn->prepare("UPDATE ".DbConstant::KPHIS_DBNAME.".ipd_progress_note_item SET progress_note_item_detail=:progress_note_item_detail
,progress_note_item_detail_2=:progress_note_item_detail_2
,update_user=:update_user,version=:version,update_datetime=:update_datetime
          WHERE progress_note_item_id=:id");

          //execute array
          $stmt->execute(array('id'=>$id
          ,'progress_note_item_detail'=>$progress_note_item_detail,'progress_note_item_detail_2'=>$progress_note_item_detail_2
          ,'update_user'=>$update_user,'version'=>$version,'update_datetime' => $update_datetime
          ));


          

            Session::insertSystemAccessLog(json_encode(array(
                'form'=>'PROGRESS-NOT-EDIT-FORM',
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

        

        }catch (PDOException  $e) {
//เมื่อเกิดข้อผิดพลาด
echo $e->getMessage();
//$output_error = '<div class="alert alert-danger">ERROR !!</div>';

$output_error = '<script>NotificationMessage("บันทึกข้อมูลไม่สำเร็จ", "error")</script>';

        }

        echo $output_error;


        ?>
        