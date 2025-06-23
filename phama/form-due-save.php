<?php
    require_once '../include/DbUtils.php';
    require_once '../include/KphisQueryUtils.php';
    require_once '../include/Session.php';
    //เวลาตาม timezone
    date_default_timezone_set("Asia/Bangkok");

    $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล


    $an = $_REQUEST['an'];//รับค่า an
    $hn = KphisQueryUtils::getHnByAn($an);// function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา

        $specimen =  empty($_REQUEST['specimen']) ? null : $_REQUEST['specimen'];
        $physician_approved =  empty($_REQUEST['physician_approved']) ? null : $_REQUEST['physician_approved'];
        $bw =  empty($_REQUEST['bw']) ? null : $_REQUEST['bw'];
        $sex =  empty($_REQUEST['sex']) ? null : $_REQUEST['sex'];
        $age =  empty($_REQUEST['age']) ? null : $_REQUEST['age'];
        $creatinine =  empty($_REQUEST['creatinine']) ? null : $_REQUEST['creatinine'];
        $crcl =  empty($_REQUEST['crcl']) ? null : $_REQUEST['crcl'];
        $start_medication =  empty($_REQUEST['start_medication']) ? null : $_REQUEST['start_medication'];
        $bun_lab =  empty($_REQUEST['bun_lab']) ? null : $_REQUEST['bun_lab'];
        $diagnosis =  empty($_REQUEST['diagnosis']) ? null : $_REQUEST['diagnosis'];
        $infected_location =  empty($_REQUEST['infected_location']) ? null : $_REQUEST['infected_location'];
        $found_pathogens = empty($_REQUEST['found_pathogens']) ? null : $_REQUEST['found_pathogens'];
        $icode = empty($_REQUEST['icode']) ? null : $_REQUEST['icode'];
        $cancle_status = empty($_REQUEST['cancle_status']) ? null : $_REQUEST['cancle_status'];

    //$create_datetime = ใช้ NOW()
    $create_datetime =  date('Y-m-d H:i:s');
    $create_user  = $_SESSION['loginname'];
    //$update_datetime = ใช้ NOW()
   // $update_datetime  = $datenow = date('Y-m-d H:i:s');
    $update_user  = $_SESSION['loginname'];

    $version = 1;

    try {

        if ( $diagnosis != '' && $indications != '' && $icode != '' && $physician_approved != '' && $cancle_status != '' /*&& $rxtime !='' */
) {

    $stmt = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".prs_due_check(hn,an,icode
    ,bw,age,sex,creatinine,crcl,bun_lab,diagnosis,infected_location
    ,start_medication,found_pathogens,specimen,indications,physician_approved,cancle_status
    ,create_user,version,create_datetime,update_user,update_datetime)
    VALUES(:hn,:an,:icode
    ,:bw,:age,:sex,:creatinine,:crcl,:bun_lab,:diagnosis,:infected_location
    ,:start_medication,:found_pathogens,:specimen,:indications,:physician_approved,:cancle_status
    ,:create_user,:version,:create_datetime,:update_user,:update_datetime)");
    
            $stmt->execute(array('hn'=>$hn,'an'=>$an,'icode'=>$icode
         ,'bw'=>$bw,'age'=>$age,'sex'=>$sex,'creatinine'=>$creatinine,'crcl'=>$crcl
          ,'bun_lab'=>$bun_lab,'diagnosis'=>$diagnosis,'infected_location'=>$infected_location
          ,'start_medication'=>$start_medication,'found_pathogens'=>$found_pathogens
          ,'specimen'=>$specimen,'indications'=>$indications
          ,'physician_approved'=>$physician_approved,'cancle_status'=>$cancle_status
            ,'create_user'=>$create_user,'version'=>$version,'create_datetime' => $create_datetime,'update_user'=>$create_user,'update_datetime' => $create_datetime));

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
