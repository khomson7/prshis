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
    $slip_and_fall = empty($_REQUEST['slip_and_fall']) ? null : $_REQUEST['slip_and_fall'];
    $age_check = empty($_REQUEST['age_check']) ? null : $_REQUEST['age_check'];
    $get_medicine = empty($_REQUEST['get_medicine']) ? null : $_REQUEST['get_medicine'];
    $body = empty($_REQUEST['body']) ? null : $_REQUEST['body'];
    $assessment = empty($_REQUEST['assessment']) ? null : $_REQUEST['assessment'];
    $excretion = empty($_REQUEST['excretion']) ? null : $_REQUEST['excretion'];
    $after_birth = empty($_REQUEST['after_birth']) ? null : $_REQUEST['after_birth'];
    $surgery = empty($_REQUEST['surgery']) ? null : $_REQUEST['surgery'];

    if($consciousness=='1')
 {
        $consciousness_ = 0;
}else{
        $consciousness_ = $consciousness;
}
if($slip_and_fall=='1')
 {
        $slip_and_fall_ = 0;
}else{
        $slip_and_fall_ = $slip_and_fall;
}
if($age_check=='1')
 {
        $age_check_ = 0;
}else{
        $age_check_ = $age_check;
}
if($get_medicine=='1')
 {
        $get_medicine_ = 0;
}else{
        $get_medicine_ = $get_medicine;
}
if($body=='1')
 {
        $body_ = 0;
}else{
        $body_ = $body;
}
if($assessment=='1')
 {
        $assessment_ = 0;
}else{
        $assessment_ = $assessment;
}
if($excretion =='9')
 {
        $excretion_ = 0;
}else{
        $excretion_ = $excretion;
}
if($after_birth =='9')
 {
        $after_birth_ = 0;
}else{
        $after_birth_ = $after_birth;
}
if($surgery =='9')
 {
        $surgery_ = 0;
}else{
        $surgery_ = $surgery;
}

//echo $assessment_;

        $score = $consciousness_ + $slip_and_fall_ + $age_check_ + $get_medicine_ + $body_ + $assessment_ + $excretion_ +$after_birth_ + $surgery_;
        


    $create_datetime =  date('Y-m-d H:i:s');
    $create_user  = $_SESSION['loginname'];
    $update_user  = $_SESSION['loginname'];
    $update_datetime =  date('Y-m-d H:i:s');

    $version = 1;

    try {

        if ( $work_shift != ''
) {


  $stmt = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".prs_felldown(an,work_shift
            ,consciousness,slip_and_fall,age_check,get_medicine
            ,body,assessment,excretion,after_birth,surgery,score
            ,create_user,create_datetime,update_user,version,update_datetime)
            VALUES(:an,:work_shift
            ,:consciousness,:slip_and_fall,:age_check,:get_medicine
            ,:body,:assessment,:excretion,:after_birth,:surgery,:score
            ,:create_user,:create_datetime,:update_user,:version,:update_datetime)");
    
            $stmt->execute(array('an'=>$an,'work_shift'=>$work_shift
            ,'consciousness'=>$consciousness,'slip_and_fall'=>$slip_and_fall,'age_check'=>$age_check,'get_medicine'=>$get_medicine
            ,'body'=>$body,'assessment'=>$assessment,'excretion'=>$excretion,'after_birth'=>$after_birth,'surgery'=>$surgery,'score'=>$score
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
