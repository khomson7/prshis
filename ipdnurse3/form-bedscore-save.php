<?php
    require_once '../include/DbUtils.php';
    require_once '../include/KphisQueryUtils.php';
    require_once '../include/Session.php';
    //เวลาตาม timezone
    date_default_timezone_set("Asia/Bangkok");

    $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล


    $an = $_REQUEST['an'];//รับค่า an
    $hn = KphisQueryUtils::getHnByAn($an);// function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา

        $work_shift= empty($_REQUEST['work_shift']) ? 0 : $_REQUEST['work_shift'];
        $perception = empty($_REQUEST['perception']) ? 0 : $_REQUEST['perception'];
        $wetting_the_skin = empty($_REQUEST['wetting_the_skin']) ? 0 : $_REQUEST['wetting_the_skin'];
        $doing_activities = empty($_REQUEST['doing_activities']) ? 0 : $_REQUEST['doing_activities'];
        $movement= empty($_REQUEST['movement']) ? 0 : $_REQUEST['movement'];
        $getting_food = empty($_REQUEST['getting_food']) ? 0 : $_REQUEST['getting_food'];
        $sarcasm = empty($_REQUEST['sarcasm']) ? 0 : $_REQUEST['sarcasm'];
        $score = $perception+$wetting_the_skin+$doing_activities+$movement+$getting_food+$sarcasm;


    $create_datetime =  date('Y-m-d H:i:s');
    $create_user  = $_SESSION['loginname'];
    $update_user  = $_SESSION['loginname'];
    $update_datetime =  date('Y-m-d H:i:s');

    $version = 1;

    try {

        if ( $work_shift != ''
) {


  $stmt = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".prs_bedsores(an,work_shift
            ,perception,wetting_the_skin,doing_activities,movement,getting_food,sarcasm,score
            ,create_user,create_datetime,update_user,version,update_datetime)
            VALUES(	:an,:work_shift
            ,:perception,:wetting_the_skin,:doing_activities,:movement,:getting_food,:sarcasm,:score
            ,:create_user,:create_datetime,:update_user,:version,:update_datetime)");
    
            $stmt->execute(array('an'=>$an,'work_shift'=>$work_shift
            ,'perception'=>$perception,'wetting_the_skin'=>$wetting_the_skin,'doing_activities'=>$doing_activities,'movement'=>$movement
           ,'getting_food'=>$getting_food,'sarcasm'=>$sarcasm,'score'=>$score
            ,'create_user'=>$create_user,'create_datetime' => $create_datetime,'update_user'=>$update_user,'version'=>$version,'update_datetime' => $update_datetime));

  /*          $stmt = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".prs_mental_health2(an
            ,perception4,perception3,perception2,perception1,sum_perception
            ,create_user,create_datetime,update_user,version,update_datetime)
            VALUES(	:an
            ,:perception4,:perception3,:perception2,:perception1,:sum_perception
            ,:create_user,:create_datetime,:update_user,:version,:update_datetime)");
    
            $stmt->execute(array('an'=>$an
            ,'perception4'=>$perception4,'perception3'=>$perception3,'perception2'=>$perception2,'perception1'=>$perception1,'sum_perception'=>$sum_perception
            ,'create_user'=>$create_user,'create_datetime' => $create_datetime,'update_user'=>$update_user,'version'=>$version,'update_datetime' => $update_datetime));
*/
            $output_error = '<script>
        NotificationMessage("บันทึกข้อมูลสำเร็จ", "success");
        </script>';




        }

    }catch (PDOException  $e) {
        echo $e->getMessage();
        $output_error = '<div class="alert alert-danger">ERROR !!</div>';
    }
    echo $output_error;
?>
