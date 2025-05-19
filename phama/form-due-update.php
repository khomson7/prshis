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

 
        $specimen =  empty($_REQUEST['specimen']) ? null : $_REQUEST['specimen'];
        $physician_approved =  empty($_REQUEST['physician_approved']) ? null : $_REQUEST['physician_approved'];
        $indications =  empty($_REQUEST['indications']) ? null : $_REQUEST['indications'];
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
        $egfr = empty($_REQUEST['egfr']) ? null : $_REQUEST['egfr'];
        $icode = empty($_REQUEST['icode']) ? null : $_REQUEST['icode'];
        
        $update_datetime= date('Y-m-d H:i:s');
        $update_user = $_SESSION['loginname'];
        $version0 = $_REQUEST['version'];
        $version = $version0 + 1;

        try {
          //เรียกใช้งาน sql update
if ( $an != ''/*$physician_approved != '' && $rxtime !='' */
) {
        $output_error = '<script>
        NotificationMessage("บันทึกข้อมูลสำเร็จ", "success");     
        </script>';
$stmt = $conn->prepare("UPDATE ".DbConstant::KPHIS_DBNAME.".prs_due_check SET hn=:hn,an=:an,icode=:icode
        ,bw=:bw,age=:age,sex=:sex,creatinine=:creatinine,crcl=:crcl
        ,bun_lab=:bun_lab,diagnosis=:diagnosis,infected_location=:infected_location
        ,start_medication=:start_medication,found_pathogens=:found_pathogens,egfr=:egfr
        ,specimen=:specimen,indications=:indications
        ,physician_approved=:physician_approved
        ,version=:version,update_user=:update_user,update_datetime=:update_datetime
          WHERE id=:id");

          //execute array
          $stmt->execute(array('id'=>$id,'hn'=>$hn,'an'=>$an,'icode'=>$icode
          ,'bw'=>$bw,'age'=>$age,'sex'=>$sex,'creatinine'=>$creatinine,'crcl'=>$crcl
           ,'bun_lab'=>$bun_lab,'diagnosis'=>$diagnosis,'infected_location'=>$infected_location
           ,'start_medication'=>$start_medication,'found_pathogens'=>$found_pathogens,'egfr'=>$egfr
           ,'specimen'=>$specimen,'indications'=>$indications
           ,'physician_approved'=>$physician_approved
           ,'version'=>$version,'update_user'=>$update_user,'update_datetime' => $update_datetime
          ));


          

            Session::insertSystemAccessLog(json_encode(array(
                'form'=>'DUE-FORM',
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
        