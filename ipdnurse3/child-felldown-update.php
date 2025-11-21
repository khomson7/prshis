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

        $work_shift= empty($_REQUEST['work_shift']) ? null : $_REQUEST['work_shift'];
        $age_check = empty($_REQUEST['age_check']) ? null : $_REQUEST['age_check'];
        $sex = empty($_REQUEST['sex']) ? null : $_REQUEST['sex'];
        $diag = empty($_REQUEST['diag']) ? null : $_REQUEST['diag'];
        $knowledge = empty($_REQUEST['knowledge']) ? null : $_REQUEST['knowledge'];
        $environmental = empty($_REQUEST['environmental']) ? null : $_REQUEST['environmental'];
        $after_surgery = empty($_REQUEST['after_surgery']) ? null : $_REQUEST['after_surgery'];
        $drug_use = empty($_REQUEST['drug_use']) ? null : $_REQUEST['drug_use'];


/*
if($age_check=='1')
 {
        $age_check_ = 0;
}else{
        $age_check_ = $age_check;
}
if($diag=='1')
 {
        $diag_ = 0;
}else{
        $age_check_ = $age_check;
}
*/

//echo $assessment_;

        $score = $age_check + $sex + $diag + $knowledge + $environmental + $after_surgery + $drug_use;
       
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
$stmt = $conn->prepare("UPDATE ".DbConstant::KPHIS_DBNAME.".prs_child_felldown SET an=:an,work_shift=:work_shift
          ,age_check=:age_check,sex=:sex,diag=:diag,knowledge=:knowledge,environmental=:environmental
          ,after_surgery=:after_surgery,drug_use=:drug_use,score=:score
          ,update_user=:update_user,version=:version,update_datetime=:update_datetime
          WHERE id=:id");
          //execute array
          $stmt->execute(array('id'=>$id,'an'=>$an,'work_shift'=>$work_shift
          ,'age_check'=>$age_check,'sex'=>$sex,'diag'=>$diag,'knowledge'=>$knowledge,'environmental'=>$environmental
          ,'after_surgery'=>$after_surgery,'drug_use'=>$drug_use,'score'=>$score
          ,'update_user'=>$update_user,'version'=>$version,'update_datetime' => $update_datetime
          ));

            Session::insertSystemAccessLog(json_encode(array(
                'form'=>'FELLDOWN-FORM',
                'action'=>'UPDATE',
                'version'=>$version,
                'an'=>$an,
            ),JSON_UNESCAPED_UNICODE));


} else {

       echo   '<script>
        alert("กรุณากรอกข้อมูลให้ครบถ้วน", "error");     
        </script>'; 
}


        }catch (PDOException  $e) {
//เมื่อเกิดข้อผิดพลาด
echo $e->getMessage();
//$output_error = '<div class="alert alert-danger">ERROR !!</div>';

$output_error = '<script>NotificationMessage("บันทึกข้อมูลไม่สำเร็จ", "error")</script>';

        }

        echo $output_error;


        ?>
        