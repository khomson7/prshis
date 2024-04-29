<?php
    require_once '../include/DbUtils.php';
    require_once '../include/KphisQueryUtils.php';
    require_once '../include/Session.php';
    //เวลาตาม timezone
    date_default_timezone_set("Asia/Bangkok");

    $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล


    $an = $_REQUEST['an'];//รับค่า an
    $hn = KphisQueryUtils::getHnByAn($an);// function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา

   // $output_error = '';

  /*  if(empty($an)

    ){
        exit;
    }
*/


$attending_physician = $_REQUEST['attending_physician'];
$blood_circulation = $_REQUEST['blood_circulation'];
$bpd = $_REQUEST['bpd'];
$bps = $_REQUEST['bps'];
$breathing = $_REQUEST['breathing'];
$bt = $_REQUEST['bt'];
$cc = $_REQUEST['cc'];
$child_devilopment = $_REQUEST['child_devilopment'];
$communication_ears = $_REQUEST['communication_ears'];
$communication_eyes = $_REQUEST['communication_eyes'];
$communication_speak = $_REQUEST['communication_speak'];
$current_illness = $_REQUEST['current_illness'];
$c_chronic = $_REQUEST['c_chronic'];
$depart = $_REQUEST['depart'];
$first_symptoms = $_REQUEST['first_symptoms'];
$glasses = $_REQUEST['glasses'];
$hearing_aid = $_REQUEST['hearing_aid'];
$history_of_drug = $_REQUEST['history_of_drug'];
$hospital_by = $_REQUEST['hospital_by'];
$hos_history = $_REQUEST['hos_history'];
$hpi = $_REQUEST['hpi'];
$h_allergy = $_REQUEST['h_allergy'];
$h_sergery = $_REQUEST['h_sergery'];
$id = $_REQUEST['id'];
$level_of_con = $_REQUEST['level_of_con'];
$pmh = $_REQUEST['pmh'];
$pmh2 = $_REQUEST['pmh2'];
$pr = $_REQUEST['pr'];
$refer_from = $_REQUEST['refer_from'];
$rr = $_REQUEST['rr'];
$rxdate = $_REQUEST['rxdate'];
$rxtime = $_REQUEST['rxtime'];
$skin = $_REQUEST['skin'];
$state_of_mind = $_REQUEST['state_of_mind'];
$swelling = $_REQUEST['swelling'];
$update_datetime = $_REQUEST['update_datetime'];
$vaccine_history = $_REQUEST['vaccine_history'];

    //$create_datetime = ใช้ NOW()
    $create_datetime =  date('Y-m-d H:i:s');
    $create_user  = $_SESSION['loginname'];
    //$update_datetime = ใช้ NOW()
   // $update_datetime  = $datenow = date('Y-m-d H:i:s');
    $update_user  = $_SESSION['loginname'];

    $version = 1;

    try {

        if ( $rxdate != '' && $rxtime !='' && $hospital_by !='' && $depart !='' && $cc !='' && $current_illness !=''  && $c_chronic !=''  && $hos_history !=''
        && $h_sergery !='' && $h_allergy !='' && $history_of_drug != '' && $pmh2 != '' && $communication_eyes != '' && $communication_speak != '' && $bps != ''
        && $bpd != ''  && $pr != '' && $rr != '' && $level_of_con != '' && $swelling != '' && $skin != '' && $communication_ears != '' && $state_of_mind != ''
        && $first_symptoms != '' && $breathing != ''
) {

            $stmt = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".prs_pre_nursenote(hn,an,rxdate,rxtime,refer_from,hospital_by,depart,cc,hpi
            ,current_illness,c_chronic,hos_history,h_sergery,h_allergy,vaccine_history,child_devilopment,history_of_drug
            ,pmh2,bt,pr,rr,bps,bpd,level_of_con,breathing,blood_circulation,swelling,skin,communication_ears,hearing_aid
            ,communication_eyes,glasses,communication_speak,state_of_mind,first_symptoms
            ,create_user,version,create_datetime)
            VALUES(:hn,:an,:rxdate,:rxtime,:refer_from,:hospital_by,:depart,:cc,:hpi
            ,:current_illness,:c_chronic,:hos_history,:h_sergery,:h_allergy,:vaccine_history,:child_devilopment,:history_of_drug
            ,:pmh2,:bt,:pr,:rr,:bps,:bpd,:level_of_con,:breathing,:blood_circulation,:swelling,:skin,:communication_ears,:hearing_aid
            ,:communication_eyes,:glasses,:communication_speak,:state_of_mind,:first_symptoms
            ,:create_user,:version,:create_datetime)");
    
            $stmt->execute(array('hn'=>$hn,'an'=>$an,'rxdate'=>$rxdate, 'rxtime'=>$rxtime,'refer_from'=>$refer_from
            ,'hospital_by'=>$hospital_by,'depart'=>$depart,'cc'=>$cc,'hpi'=>$hpi
            ,'current_illness'=>$current_illness,'c_chronic'=>$c_chronic,'hos_history'=>$hos_history,'h_sergery'=>$h_sergery,'h_allergy'=>$h_allergy
            ,'vaccine_history'=>$vaccine_history,'child_devilopment'=>$child_devilopment,'history_of_drug'=>$history_of_drug
            ,'pmh2'=>$pmh2,'bt'=>$bt,'pr'=>$pr,'rr'=>$rr,'bps'=>$bps,'bpd'=>$bpd
            ,'level_of_con'=>$level_of_con,'breathing'=>$breathing,'blood_circulation'=>$blood_circulation,'swelling'=>$swelling
            ,'skin'=>$skin,'communication_ears'=>$communication_ears,'hearing_aid'=>$hearing_aid
            ,'communication_eyes'=>$communication_eyes,'glasses'=>$glasses,'communication_speak'=>$communication_speak,'state_of_mind'=>$state_of_mind,'first_symptoms'=>$first_symptoms
            ,'create_user'=>$create_user,'version'=>$version,'create_datetime' => $create_datetime));

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
