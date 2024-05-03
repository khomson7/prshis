<?php
    require_once '../include/DbUtils.php';
    require_once '../include/KphisQueryUtils.php';
    require_once '../include/Session.php';
    //เวลาตาม timezone
    date_default_timezone_set("Asia/Bangkok");

    $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล


    $an = $_REQUEST['an'];//รับค่า an
    $hn = KphisQueryUtils::getHnByAn($an);// function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา

    $rxdate = $_REQUEST['rxdate'];
    $rxtime = $_REQUEST['rxtime'];

   // $output_error = '';

  /*  if(empty($an)

    ){
        exit;
    }
*/


$labor_history = empty($_REQUEST['labor_history']) ? null : $_REQUEST['labor_history'];

    //$create_datetime = ใช้ NOW()
    $create_datetime =  date('Y-m-d H:i:s');
    $create_user  = $_SESSION['loginname'];
    //$update_datetime = ใช้ NOW()
   // $update_datetime  = $datenow = date('Y-m-d H:i:s');
    $update_user  = $_SESSION['loginname'];

    $version = 1;

    try {

        if ( $rxdate != '' && $rxtime !='' && $labor_history !=''
) {

            $stmt = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".prs_lr_report2(an,rxdate,rxtime,labor_history)
            VALUES(:an,:rxdate,:rxtime,:labor_history)");
    
            $stmt->execute(array('an'=>$an,'rxdate'=>$rxdate, 'rxtime'=>$rxtime,'labor_history'=>$labor_history));

            $output_error = '<script>
        NotificationMessage("บันทึกข้อมูลสำเร็จ", "success");
        </script>';


$output_error = $labor_history;

        }

     
        
    }catch (PDOException  $e) {
        echo $e->getMessage();
        $output_error = '<div class="alert alert-danger">ERROR !!</div>';
    }
    echo $output_error;
?>
