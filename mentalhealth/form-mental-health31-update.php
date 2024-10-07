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

        $level_of_consciousness= empty($_REQUEST['level_of_consciousness']) ? 0 : $_REQUEST['level_of_consciousness'];

        $question1_1= empty($_REQUEST['question1_1']) ? 0 : $_REQUEST['question1_1'];
        $question1_2= empty($_REQUEST['question1_2']) ? 0 : $_REQUEST['question1_2'];
        $question1_3= empty($_REQUEST['question1_3']) ? 0 : $_REQUEST['question1_3'];
        $question1_4= empty($_REQUEST['question1_4']) ? 0 : $_REQUEST['question1_4'];
        $question1_5= empty($_REQUEST['question1_5']) ? 0 : $_REQUEST['question1_5'];
        $variation1 = $question1_1 + $question1_2 + $question1_3 + $question1_4 + $question1_5;
        $question2_1= empty($_REQUEST['question2_1']) ? 0 : $_REQUEST['question2_1'];
        $question2_2= empty($_REQUEST['question2_2']) ? 0 : $_REQUEST['question2_2'];
        $question2_3= empty($_REQUEST['question2_3']) ? 0 : $_REQUEST['question2_3'];
        $question2_4= empty($_REQUEST['question2_4']) ? 0 : $_REQUEST['question2_4'];
        $question2_5= empty($_REQUEST['question2_5']) ? 0 : $_REQUEST['question2_5'];
        $variation2 = $question2_1 + $question2_2 + $question2_3 + $question2_4 + $question2_5;
        $question3_1= empty($_REQUEST['question3_1']) ? 0 : $_REQUEST['question3_1'];
        $question3_2= empty($_REQUEST['question3_2']) ? 0 : $_REQUEST['question3_2'];
        $question3_3= empty($_REQUEST['question3_3']) ? 0 : $_REQUEST['question3_3'];
        $question3_4= empty($_REQUEST['question3_4']) ? 0 : $_REQUEST['question3_4'];
        $question3_5= empty($_REQUEST['question3_5']) ? 0 : $_REQUEST['question3_5'];
        $variation3 = $question3_1 + $question3_2 + $question3_3 + $question3_4 + $question3_5;
        $question4_1= empty($_REQUEST['question4_1']) ? 0 : $_REQUEST['question4_1'];
        $question4_2= empty($_REQUEST['question4_2']) ? 0 : $_REQUEST['question4_2'];
        $question4_3= empty($_REQUEST['question4_3']) ? 0 : $_REQUEST['question4_3'];
        $question4_4= empty($_REQUEST['question4_4']) ? 0 : $_REQUEST['question4_4'];
        $question4_5= empty($_REQUEST['question4_5']) ? 0 : $_REQUEST['question4_5'];
        $variation4 = $question4_1 + $question4_2 + $question4_3 + $question4_4 + $question4_5;
        

        //$family_history = 'aaa';    
        $update_datetime= date('Y-m-d H:i:s');
        $create_user = $_SESSION['loginname'];
        $update_user = $_SESSION['loginname'];
        $version0 = $_REQUEST['version'];
        $version = $version0 + 1;

        try {
          //เรียกใช้งาน sql update
if ( $level_of_consciousness != ''
) {
        $output_error = '<script>
        NotificationMessage("บันทึกข้อมูลสำเร็จ", "success");     
        </script>';
$stmt = $conn->prepare("UPDATE ".DbConstant::KPHIS_DBNAME.".prs_mental_health3 SET an=:an,hn=:hn,level_of_consciousness=:level_of_consciousness
          ,question1_1=:question1_1,question1_2=:question1_2,question1_3=:question1_3,question1_4=:question1_4,question1_5=:question1_5,variation1=:variation1
          ,question2_1=:question2_1,question2_2=:question2_2,question2_3=:question2_3,question2_4=:question2_4,question2_5=:question2_5,variation2=:variation2
          ,question3_1=:question3_1,question3_2=:question3_2,question3_3=:question3_3,question3_4=:question3_4,question3_5=:question3_5,variation3=:variation3
          ,question4_1=:question4_1,question4_2=:question4_2,question4_3=:question4_3,question4_4=:question4_4,question4_5=:question4_5,variation4=:variation4
          ,update_user=:update_user,version=:version,update_datetime=:update_datetime
          WHERE id=:id");
          //execute array
          $stmt->execute(array('id'=>$id,'an'=>$an,'hn'=>$hn,'level_of_consciousness'=>$level_of_consciousness
          ,'question1_1'=>$question1_1,'question1_2'=>$question1_2,'question1_3'=>$question1_3,'question1_4'=>$question1_4,'question1_5'=>$question1_5,'variation1'=>$variation1
          ,'question2_1'=>$question2_1,'question2_2'=>$question2_2,'question2_3'=>$question2_3,'question2_4'=>$question2_4,'question2_5'=>$question2_5,'variation2'=>$variation2
          ,'question3_1'=>$question3_1,'question3_2'=>$question3_2,'question3_3'=>$question3_3,'question3_4'=>$question3_4,'question3_5'=>$question3_5,'variation3'=>$variation3
          ,'question4_1'=>$question4_1,'question4_2'=>$question4_2,'question4_3'=>$question4_3,'question4_4'=>$question4_4,'question4_5'=>$question4_5,'variation4'=>$variation4
          ,'update_user'=>$update_user,'version'=>$version,'update_datetime' => $update_datetime
          ));


            Session::insertSystemAccessLog(json_encode(array(
                'form'=>'MENTAL-HEALTH3-FORM',
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
        