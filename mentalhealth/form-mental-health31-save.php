<?php
    require_once '../include/DbUtils.php';
    require_once '../include/KphisQueryUtils.php';
    require_once '../include/Session.php';
    //เวลาตาม timezone
    date_default_timezone_set("Asia/Bangkok");

    $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล


    $an = $_REQUEST['an'];//รับค่า an
    $hn = KphisQueryUtils::getHnByAn($an);// function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา

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
        
    $create_datetime =  date('Y-m-d H:i:s');
    $create_user  = $_SESSION['loginname'];
    $update_user  = $_SESSION['loginname'];
    $update_datetime =  date('Y-m-d H:i:s');

    $version = 1;

    try {

        if ( $level_of_consciousness != '' 
) {

            $stmt = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".prs_mental_health3(an,hn,level_of_consciousness,question1_1,question1_2,question1_3,question1_4,question1_5,variation1
            ,question2_1,question2_2,question2_3,question2_4,question2_5,variation2 ,question3_1,question3_2,question3_3,question3_4,question3_5,variation3 
            ,question4_1,question4_2,question4_3,question4_4,question4_5,variation4     
            ,create_user,create_datetime,update_user,version,update_datetime)
            VALUES(	:an,:hn,:level_of_consciousness,:question1_1,:question1_2,:question1_3,:question1_4,:question1_5,:variation1
            ,:question2_1,:question2_2,:question2_3,:question2_4,:question2_5,:variation2
            ,:question3_1,:question3_2,:question3_3,:question3_4,:question3_5,:variation3
            ,:question4_1,:question4_2,:question4_3,:question4_4,:question4_5,:variation4
            ,:create_user,:create_datetime,:update_user,:version,:update_datetime)");
    
            $stmt->execute(array('an'=>$an,'hn'=>$hn,'level_of_consciousness'=>$level_of_consciousness
            ,'question1_1'=>$question1_1,'question1_2'=>$question1_2,'question1_3'=>$question1_3,'question1_4'=>$question1_4,'question1_5'=>$question1_5,'variation1'=>$variation1
            ,'question2_1'=>$question2_1,'question2_2'=>$question2_2,'question2_3'=>$question2_3,'question2_4'=>$question2_4,'question2_5'=>$question2_5,'variation2'=>$variation2
            ,'question3_1'=>$question3_1,'question3_2'=>$question3_2,'question3_3'=>$question3_3,'question3_4'=>$question3_4,'question3_5'=>$question3_5,'variation3'=>$variation3
            ,'question4_1'=>$question4_1,'question4_2'=>$question4_2,'question4_3'=>$question4_3,'question4_4'=>$question4_4,'question4_5'=>$question4_5,'variation4'=>$variation4
            ,'create_user'=>$create_user,'create_datetime' => $create_datetime,'update_user'=>$update_user,'version'=>$version,'update_datetime' => $update_datetime));

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
