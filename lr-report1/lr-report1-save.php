<?php
    require_once '../include/DbUtils.php';
    require_once '../include/KphisQueryUtils.php';
    require_once '../include/Session.php';
    //เวลาตาม timezone
    date_default_timezone_set("Asia/Bangkok");

    $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล


    $an = $_REQUEST['an'];//รับค่า an
    $hn = KphisQueryUtils::getHnByAn($an);// function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา

    $output_error = '';

    if(empty($an)

    ){
        exit;
    }

    //echo $an;

    $receive_date = empty($_REQUEST['receive_date']) ? null : $_REQUEST['receive_date'];
    $receive_time = empty($_REQUEST['receive_time']) ? null : $_REQUEST['receive_time'];
    $receive_from = empty($_REQUEST['receive_from']) ? null : $_REQUEST['receive_from'];
    $cc = empty($_REQUEST['cc']) ? null : $_REQUEST['cc'];

    //$create_datetime = ใช้ NOW()
    $create_datetime =  date('Y-m-d H:i:s');
    $create_user  = $_SESSION['loginname'];
    //$update_datetime = ใช้ NOW()
   // $update_datetime  = $datenow = date('Y-m-d H:i:s');
    $update_user  = $_SESSION['loginname'];

    $version = 1;

    try {
//บันทึกรายการ
        $stmt = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".prs_labor_report1(receive_date,receive_time,receive_from
        ,cc
        ,an,create_user,update_user,version,create_datetime,update_datetime)
        VALUES(:receive_date,:receive_time,:receive_from,:an,:create_user,:update_user,:version,:create_datetime,:update_datetime)");

        $stmt->execute(array('receive_date'=>$receive_date,'receive_time'=>$receive_time,'receive_from'=>$receive_from
        ,'cc'=>$cc
      ,'an'=>$an,'create_user'=>$create_user, 'update_user'=>$update_user, 'version'=>$version , 'create_datetime' =>$create_datetime , 'update_datetime' =>$create_datetime));

        $output_error = '<div class="alert alert-success">บันทึกข้อมูลสำเร็จ</div>';
    }catch (PDOException  $e) {
        echo $e->getMessage();
        $output_error = '<div class="alert alert-danger">ERROR !!</div>';
    }
    echo $output_error;
?>
