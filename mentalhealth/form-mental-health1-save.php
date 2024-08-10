<?php
    require_once '../include/DbUtils.php';
    require_once '../include/KphisQueryUtils.php';
    require_once '../include/Session.php';
    //เวลาตาม timezone
    date_default_timezone_set("Asia/Bangkok");

    $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล


    $an = $_REQUEST['an'];//รับค่า an
    $hn = KphisQueryUtils::getHnByAn($an);// function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา

    $create_datetime =  date('Y-m-d H:i:s');
    $create_user  = $_SESSION['loginname'];
    $update_user  = $_SESSION['loginname'];

    $version = 1;

    try {

        if ( $an != '' 
) {

            $stmt = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".prs_mental_health1(hn,an
            ,create_user,version,create_datetime)
            VALUES(:hn,:an
            ,:create_user,:version,:create_datetime)");
    
            $stmt->execute(array('hn'=>$hn,'an'=>$an
            ,'create_user'=>$create_user,'version'=>$version,'create_datetime' => $create_datetime));

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
