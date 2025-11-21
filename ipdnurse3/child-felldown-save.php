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
    $age_check = empty($_REQUEST['age_check']) ? null : $_REQUEST['age_check'];
    $sex = empty($_REQUEST['sex']) ? null : $_REQUEST['sex'];
    $diag = empty($_REQUEST['diag']) ? null : $_REQUEST['diag'];
    $knowledge = empty($_REQUEST['knowledge']) ? null : $_REQUEST['knowledge'];
    $environmental = empty($_REQUEST['environmental']) ? null : $_REQUEST['environmental'];
    $after_surgery = empty($_REQUEST['after_surgery']) ? null : $_REQUEST['after_surgery'];
    $drug_use = empty($_REQUEST['drug_use']) ? null : $_REQUEST['drug_use'];


    
//echo $assessment_;

$score = $age_check + $sex + $diag + $knowledge + $environmental + $after_surgery + $drug_use;
       
        


    $create_datetime =  date('Y-m-d H:i:s');
    $create_user  = $_SESSION['loginname'];
    $update_user  = $_SESSION['loginname'];
    $update_datetime =  date('Y-m-d H:i:s');

    $version = 1;

    try {

        if ( $work_shift != '' and $age_check != ''
) {


  $stmt = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".prs_child_felldown(an,work_shift
            ,age_check,sex,diag,knowledge,environmental,after_surgery,drug_use,score
            ,create_user,create_datetime,update_user,version,update_datetime)
            VALUES(:an,:work_shift
            ,:age_check,:sex,:diag,:knowledge,:environmental,:after_surgery,:drug_use,:score
            ,:create_user,:create_datetime,:update_user,:version,:update_datetime)");
    
            $stmt->execute(array('an'=>$an,'work_shift'=>$work_shift
            ,'age_check'=>$age_check,'sex'=>$sex,'diag'=>$diag,'knowledge'=>$knowledge,'environmental'=>$environmental
          ,'after_surgery'=>$after_surgery,'drug_use'=>$drug_use,'score'=>$score
          ,'create_user'=>$create_user,'create_datetime' => $create_datetime
          ,'update_user'=>$update_user,'version'=>$version,'update_datetime' => $update_datetime));


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
