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

        $work_shift= empty($_REQUEST['work_shift']) ? 0 : $_REQUEST['work_shift'];
        $perception = empty($_REQUEST['perception']) ? 0 : $_REQUEST['perception'];
        $wetting_the_skin = empty($_REQUEST['wetting_the_skin']) ? 0 : $_REQUEST['wetting_the_skin'];
        $doing_activities = empty($_REQUEST['doing_activities']) ? 0 : $_REQUEST['doing_activities'];
        $movement= empty($_REQUEST['movement']) ? 0 : $_REQUEST['movement'];
        $getting_food = empty($_REQUEST['getting_food']) ? 0 : $_REQUEST['getting_food'];
        $sarcasm = empty($_REQUEST['sarcasm']) ? 0 : $_REQUEST['sarcasm'];
        $score = $perception+$wetting_the_skin+$doing_activities+$movement+$getting_food+$sarcasm;
       
        //$family_history = 'aaa';    
        $update_datetime= date('Y-m-d H:i:s');
        $create_user = $_SESSION['loginname'];
        $update_user = $_SESSION['loginname'];
        $version0 = $_REQUEST['version'];
        $version = $version0 + 1;

        try {
          //เรียกใช้งาน sql update
if ( $work_shift != ''
) {
        $output_error = '<script>
        NotificationMessage("บันทึกข้อมูลสำเร็จ", "success");     
        </script>';
$stmt = $conn->prepare("UPDATE ".DbConstant::KPHIS_DBNAME.".prs_bedsores SET an=:an,work_shift=:work_shift
          ,perception=:perception,wetting_the_skin=:wetting_the_skin,doing_activities=:doing_activities,movement=:movement
          ,getting_food=:getting_food,sarcasm=:sarcasm,score=:score
          ,update_user=:update_user,version=:version,update_datetime=:update_datetime
          WHERE id=:id");
          //execute array
          $stmt->execute(array('id'=>$id,'an'=>$an,'work_shift'=>$work_shift
          ,'perception'=>$perception,'wetting_the_skin'=>$wetting_the_skin,'doing_activities'=>$doing_activities,'movement'=>$movement
          ,'getting_food'=>$getting_food,'sarcasm'=>$sarcasm,'score'=>$score
          ,'update_user'=>$update_user,'version'=>$version,'update_datetime' => $update_datetime
          ));


            Session::insertSystemAccessLog(json_encode(array(
                'form'=>'BEDSORES-FORM',
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
        