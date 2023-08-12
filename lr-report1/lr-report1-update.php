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

        $receive_date = empty($_REQUEST['receive_date']) ? null : $_REQUEST['receive_date'];
        $receive_time= empty($_REQUEST['receive_time']) ? null : $_REQUEST['receive_time'];
        $receive_from = empty($_REQUEST['receive_from']) ? null : $_REQUEST['receive_from'];
        $cc = empty($_REQUEST['cc']) ? null : $_REQUEST['cc'];

        $update_datetime= date('Y-m-d H:i:s');
        $update_user = $_SESSION['loginname'];
        $version = $_REQUEST['version'];
        $version = $version + 1;

        try {
          //เรียกใช้งาน sql update
          $stmt = $conn->prepare("UPDATE ".DbConstant::KPHIS_DBNAME.".prs_labor_report1 SET an=:an,receive_date=:receive_date,receive_time=:receive_time,receive_from=:receive_from
          ,cc=:cc
          ,update_user=:update_user,version=:version,update_datetime=:update_datetime
          WHERE id=:id");
          //execute array
          $stmt->execute(array('id'=>$id,'an'=>$an,'receive_date'=>$receive_date, 'receive_time'=>$receive_time,'receive_from'=>$receive_from
          ,'cc'=>$cc
          ,'update_user'=>$update_user,'version'=>$version,'update_datetime' => $update_datetime
          ));

            //เมื่อ update สำเร็จ
          $output_error = '<div class="alert alert-success">บันทึกข้อมูลเรียบร้อยแล้วคะ</div>';

        }catch (PDOException  $e) {
//เมื่อเกิดข้อผิดพลาด
echo $e->getMessage();
$output_error = '<div class="alert alert-danger">ERROR !!</div>';

        }

        echo $output_error;


        ?>
