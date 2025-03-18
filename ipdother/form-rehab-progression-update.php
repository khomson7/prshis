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

        $rxdate = empty($_REQUEST['rxdate']) ? null : $_REQUEST['rxdate'];
        $pe = empty($_REQUEST['pe']) ? null : $_REQUEST['pe'];
        $rx =  empty($_REQUEST['rx']) ? null : $_REQUEST['rx'];
        $rx_use_time =  empty($_REQUEST['rx_use_time']) ? null : $_REQUEST['rx_use_time'];
        $progress_note=  empty($_REQUEST['progress_note']) ? null : $_REQUEST['progress_note'];
        $home_ward_program=  empty($_REQUEST['home_ward_program']) ? null : $_REQUEST['home_ward_program'];
        $update_datetime= date('Y-m-d H:i:s');
        $update_user = $_SESSION['loginname'];
        $version0 = $_REQUEST['version'];
        $version = $version0 + 1;

        try {
          //เรียกใช้งาน sql update
if ( $rxdate != '' 
) {
        $output_error = '<script>
        NotificationMessage("บันทึกข้อมูลสำเร็จ", "success");     
        </script>';
$stmt = $conn->prepare("UPDATE ".DbConstant::KPHIS_DBNAME.".prs_rehab_progression SET an=:an,rxdate=:rxdate
          ,pe=:pe,rx=:rx,rx_use_time=:rx_use_time,progress_note=:progress_note,home_ward_program=:home_ward_program
          ,update_user=:update_user,version=:version,update_datetime=:update_datetime
          WHERE id=:id");

          //execute array
          $stmt->execute(array('id'=>$id,'an'=>$an,'rxdate'=>$rxdate
          ,'pe'=>$pe,'rx'=>$rx,'rx_use_time'=>$rx_use_time,'progress_note'=>$progress_note,'home_ward_program'=>$home_ward_program
          ,'update_user'=>$update_user,'version'=>$version,'update_datetime' => $update_datetime
          ));

            Session::insertSystemAccessLog(json_encode(array(
                'form'=>'REHAB-PROGRESSION-FORM',
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
        