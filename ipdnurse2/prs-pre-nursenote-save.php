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

  /*  if(empty($an)

    ){
        exit;
    }
*/

    $rxdate =  $_REQUEST['rxdate'];
    $rxtime = $_REQUEST['rxtime'];
    $refer_from =  $_REQUEST['refer_from'];
    $cc =  $_REQUEST['cc'];
    $hpi =  $_REQUEST['hpi'];
    $depart =  $_REQUEST['depart'];
    $hospital_by =  $_REQUEST['hospital_by'];
    $c_chronic =  $_REQUEST['c_chronic'];
    $hos_history =  $_REQUEST['hos_history'];
    $h_sergery =  $_REQUEST['h_sergery'];
    
    //$create_datetime = ใช้ NOW()
    $create_datetime =  date('Y-m-d H:i:s');
    $create_user  = $_SESSION['loginname'];
    //$update_datetime = ใช้ NOW()
   // $update_datetime  = $datenow = date('Y-m-d H:i:s');
    $update_user  = $_SESSION['loginname'];

    $version = 1;

    try {

        if ($rxdate != '' && $rxtime !='' && $depart !='' && $hospital_by !='' /*&& $c_chronic !='' && $hos_history !='' && $h_sergery !=''*/) {

            $stmt = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".prs_pre_nursenote(rxdate,rxtime,refer_from,cc,hpi
            ,an,create_user,update_user,version,create_datetime,update_datetime)
            VALUES(:rxdate,:rxtime,:refer_from,:cc,:hpi
            ,:an,:create_user,:update_user,:version,:create_datetime,:update_datetime)");
    
            $stmt->execute(array('rxdate'=>$rxdate,'rxtime'=>$rxtime,'refer_from'=>$refer_from ,'cc'=>$cc ,'hpi'=>$hpi      
            ,'an'=>$an,'create_user'=>$create_user, 'update_user'=>$update_user, 'version'=>$version , 'create_datetime' =>$create_datetime , 'update_datetime' =>$create_datetime));

            $output_error = '<script>
        NotificationMessage("บันทึกข้อมูลสำเร็จ", "success");
        </script>';

        $pre_nursenote_id = $conn->lastInsertId();
//echo $pre_nursenote_id ;
       // echo $_REQUEST['doctor'];
      if(!empty($_REQUEST['doctor'])){
            foreach($_REQUEST['doctor'] as $doctor){
                $stmt_item = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".prs_pre_nursenote_item(pre_nursenote_id,an,doctor,create_user,create_datetime,update_user,update_datetime,version)
                VALUES (:pre_nursenote_id,:an,:doctor,:create_user,now(),:update_user,now(),:version)");
                $stmt_item->execute(array('pre_nursenote_id'=>$pre_nursenote_id,'an'=>$an,'doctor'=>$doctor
                ,'create_user'=>$create_user, 'update_user'=>$update_user, 'version'=>$version
                ));
            }
        }  

        }
//บันทึกรายการ
     /*   $stmt = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".prs_pre_nursenote(rxdate,rxtime,refer_from,cc,hpi
        ,an,create_user,update_user,version,create_datetime,update_datetime)
        VALUES(:rxdate,:rxtime,:refer_from,:cc,:hpi
        ,:an,:create_user,:update_user,:version,:create_datetime,:update_datetime)");

        $stmt->execute(array('rxdate'=>$rxdate,'rxtime'=>$rxtime,'refer_from'=>$refer_from ,'cc'=>$cc ,'hpi'=>$hpi      
        ,'an'=>$an,'create_user'=>$create_user, 'update_user'=>$update_user, 'version'=>$version , 'create_datetime' =>$create_datetime , 'update_datetime' =>$create_datetime));

        $output_error = '<div class="alert alert-success">บันทึกข้อมูลสำเร็จ</div>';

        */
    }catch (PDOException  $e) {
        echo $e->getMessage();
        $output_error = '<div class="alert alert-danger">ERROR !!</div>';
    }
    echo $output_error;
?>
