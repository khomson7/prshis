<?php
    require_once '../include/DbUtils.php';
    require_once '../include/KphisQueryUtils.php';
    require_once '../include/Session.php';
    //เวลาตาม timezone
    date_default_timezone_set("Asia/Bangkok");

    $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล


    $an = $_REQUEST['an'];//รับค่า an
    $hn = KphisQueryUtils::getHnByAn($an);// function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา

        $work_shift= empty($_REQUEST['work_shift']) ? null : $_REQUEST['work_shift'];
        $consciousness = empty($_REQUEST['consciousness']) ? null : $_REQUEST['consciousness'];
        


    $create_datetime =  date('Y-m-d H:i:s');
    $create_user  = $_SESSION['loginname'];
    $update_user  = $_SESSION['loginname'];
    $update_datetime =  date('Y-m-d H:i:s');

    $version = 1;

    try {

        if ( $work_shift != ''
) {


  $stmt = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".prs_felldown(an,work_shift
            ,consciousness
            ,create_user,create_datetime,update_user,version,update_datetime)
            VALUES(:an,:work_shift
            ,:consciousness
            ,:create_user,:create_datetime,:update_user,:version,:update_datetime)");
    
            $stmt->execute(array('an'=>$an,'work_shift'=>$work_shift
            ,'consciousness'=>$consciousness
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
