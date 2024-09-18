<?php
    require_once '../include/DbUtils.php';
    require_once '../include/KphisQueryUtils.php';
    require_once '../include/Session.php';
    //เวลาตาม timezone
    date_default_timezone_set("Asia/Bangkok");

    $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล


    $an = $_REQUEST['an'];//รับค่า an
    $hn = KphisQueryUtils::getHnByAn($an);// function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา

    $appearance = empty($_REQUEST['appearance']) ? null : $_REQUEST['appearance'];
    $appearance_check = empty($_REQUEST['appearance_check']) ? null : $_REQUEST['appearance_check']; 
    $dress = empty($_REQUEST['dress']) ? null : $_REQUEST['dress']; 
    $body_movement_behavior = empty($_REQUEST['body_movement_behavior']) ? null : $_REQUEST['body_movement_behavior'];
    $attitude = empty($_REQUEST['attitude']) ? null : $_REQUEST['attitude'];  
    $rate = empty($_REQUEST['rate']) ? null : $_REQUEST['rate'];
    $rhythm = empty($_REQUEST['rhythm']) ? null : $_REQUEST['rhythm'];
    $speech_disorder = empty($_REQUEST['speech_disorder']) ? null : $_REQUEST['speech_disorder'];
    $stream_of_talk = empty($_REQUEST['stream_of_talk']) ? null : $_REQUEST['stream_of_talk'];
    $mood = empty($_REQUEST['mood']) ? null : $_REQUEST['mood'];
    $affect = empty($_REQUEST['affect']) ? null : $_REQUEST['affect'];
    $thought_process = empty($_REQUEST['thought_process']) ? null : $_REQUEST['thought_process'];
    $thought_content = empty($_REQUEST['thought_content']) ? null : $_REQUEST['thought_content'];
    $illution = empty($_REQUEST['illution']) ? null : $_REQUEST['illution'];
    $hallucination = empty($_REQUEST['hallucination']) ? null : $_REQUEST['hallucination'];
    $vision = empty($_REQUEST['vision']) ? null : $_REQUEST['vision'];
    $hearing = empty($_REQUEST['hearing']) ? null : $_REQUEST['hearing'];
    $tast_perception = empty($_REQUEST['tast_perception']) ? null : $_REQUEST['tast_perception'];
    $touch = empty($_REQUEST['touch']) ? null : $_REQUEST['touch'];
    $smell = empty($_REQUEST['smell']) ? null : $_REQUEST['smell'];
    $orientation = empty($_REQUEST['orientation']) ? null : $_REQUEST['orientation'];
    $orientation_time = empty($_REQUEST['orientation_time']) ? null : $_REQUEST['orientation_time'];
    $orientation_location = empty($_REQUEST['orientation_location']) ? null : $_REQUEST['orientation_location'];
    $orientation_person = empty($_REQUEST['orientation_person']) ? null : $_REQUEST['orientation_person'];
    $non_orientation= empty($_REQUEST['non_orientation']) ? null : $_REQUEST['non_orientation'];
    $non_orientation_time = empty($_REQUEST['non_orientation_time']) ? null : $_REQUEST['non_orientation_time'];
    $non_orientation_location = empty($_REQUEST['non_orientation_location']) ? null : $_REQUEST['non_orientation_location'];
    $non_orientation_person = empty($_REQUEST['non_orientation_person']) ? null : $_REQUEST['non_orientation_person'];
    $attention1  = empty($_REQUEST['attention1']) ? null : $_REQUEST['attention1'];
    $attention2  = empty($_REQUEST['attention2']) ? null : $_REQUEST['attention2'];
    $attention3  = empty($_REQUEST['attention3']) ? null : $_REQUEST['attention3'];
    $memory1 = empty($_REQUEST['memory1']) ? null : $_REQUEST['memory1'];
    $memory2 = empty($_REQUEST['memory2']) ? null : $_REQUEST['memory2'];
    $memory3 = empty($_REQUEST['memory3']) ? null : $_REQUEST['memory3'];
    $general_khowledge = empty($_REQUEST['general_khowledge']) ? null : $_REQUEST['general_khowledge'];
    $abstract_difference1 = empty($_REQUEST['abstract_difference1']) ? null : $_REQUEST['abstract_difference1'];
    $abstract_difference2 = empty($_REQUEST['abstract_difference2']) ? null : $_REQUEST['abstract_difference2'];
    $abstract_difference3 = empty($_REQUEST['abstract_difference3']) ? null : $_REQUEST['abstract_difference3'];
    $concrete_difference1 = empty($_REQUEST['concrete_difference1']) ? null : $_REQUEST['concrete_difference1'];
    $concrete_difference2 = empty($_REQUEST['concrete_difference2']) ? null : $_REQUEST['concrete_difference2'];
    $concrete_difference3 = empty($_REQUEST['concrete_difference3']) ? null : $_REQUEST['concrete_difference3'];
    $concrete_similarities1 = empty($_REQUEST['concrete_similarities1']) ? null : $_REQUEST['concrete_similarities1'];
    $concrete_similarities2 = empty($_REQUEST['concrete_similarities2']) ? null : $_REQUEST['concrete_similarities2'];
    $concrete_similarities3 = empty($_REQUEST['concrete_similarities3']) ? null : $_REQUEST['concrete_similarities3'];
    $abstract_similarities1 = empty($_REQUEST['abstract_similarities1']) ? null : $_REQUEST['abstract_similarities1'];
    $abstract_similarities2 = empty($_REQUEST['abstract_similarities2']) ? null : $_REQUEST['abstract_similarities2'];
    $abstract_similarities3 = empty($_REQUEST['abstract_similarities3']) ? null : $_REQUEST['abstract_similarities3'];
    $concrete_aphorisms1 = empty($_REQUEST['concrete_aphorisms1']) ? null : $_REQUEST['concrete_aphorisms1'];
    $concrete_aphorisms2 = empty($_REQUEST['concrete_aphorisms2']) ? null : $_REQUEST['concrete_aphorisms2'];
    $concrete_aphorisms3 = empty($_REQUEST['concrete_aphorisms3']) ? null : $_REQUEST['concrete_aphorisms3'];
    $abstract_aphorisms1 = empty($_REQUEST['abstract_aphorisms1']) ? null : $_REQUEST['abstract_aphorisms1'];
    $abstract_aphorisms2 = empty($_REQUEST['abstract_aphorisms2']) ? null : $_REQUEST['abstract_aphorisms2'];
    $abstract_aphorisms3 = empty($_REQUEST['abstract_aphorisms3']) ? null : $_REQUEST['abstract_aphorisms3'];
    $judment1 = empty($_REQUEST['judment1']) ? null : $_REQUEST['judment1'];
    $judment2 = empty($_REQUEST['judment2']) ? null : $_REQUEST['judment2'];
    $judment3 = empty($_REQUEST['judment3']) ? null : $_REQUEST['judment3'];
    $insight = empty($_REQUEST['insight']) ? null : $_REQUEST['insight'];

    $create_datetime =  date('Y-m-d H:i:s');
    $create_user  = $_SESSION['loginname'];
    $update_user  = $_SESSION['loginname'];
    $update_datetime =  date('Y-m-d H:i:s');

    $version = 1;

    try {

        if ( $appearance != '' && $dress != ''  
) {

            $stmt = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".prs_mental_health1(an,hn,appearance,dress,appearance_check,body_movement_behavior,attitude,rate
            ,rhythm,speech_disorder,stream_of_talk,mood,affect,thought_process,thought_content,illution,hallucination,vision,hearing
            ,tast_perception,touch,smell,orientation,orientation_time,non_orientation,non_orientation_time,non_orientation_location
            ,non_orientation_person,attention1,attention2,attention3,memory1,memory2,memory3,general_khowledge
            ,abstract_difference1,abstract_difference2,abstract_difference3,concrete_difference1,concrete_difference2,concrete_difference3
            ,concrete_similarities1,concrete_similarities2,concrete_similarities3,abstract_similarities1,abstract_similarities2,abstract_similarities3
            ,concrete_aphorisms1,concrete_aphorisms2,concrete_aphorisms3,abstract_aphorisms1,abstract_aphorisms2,abstract_aphorisms3
            ,judment1,judment2,judment3,insight,orientation_location,orientation_person
            ,create_user,create_datetime,update_user,version,update_datetime)
            VALUES(	:an,:hn,:appearance,:dress,:appearance_check,:body_movement_behavior,:attitude,:rate
            ,:rhythm,:speech_disorder,:stream_of_talk,:mood,:affect,:thought_process,:thought_content,:illution,:hallucination,:vision,:hearing
            ,:tast_perception,:touch,:smell,:orientation,:orientation_time,:non_orientation,:non_orientation_time,:non_orientation_location
            ,:non_orientation_person,:attention1,:attention2,:attention3,:memory1,:memory2,:memory3,:general_khowledge
            ,:abstract_difference1,:abstract_difference2,:abstract_difference3,:concrete_difference1,:concrete_difference2,:concrete_difference3
            ,:concrete_similarities1,:concrete_similarities2,:concrete_similarities3,:abstract_similarities1,:abstract_similarities2,:abstract_similarities3
            ,:concrete_aphorisms1,:concrete_aphorisms2,:concrete_aphorisms3,:abstract_aphorisms1,:abstract_aphorisms2,:abstract_aphorisms3
            ,:judment1,:judment2,:judment3,:insight,:orientation_location,:orientation_person
            ,:create_user,:create_datetime,:update_user,:version,:update_datetime)");
    
            $stmt->execute(array('an'=>$an,'hn'=>$hn,'appearance'=>$appearance
            ,'dress'=>$dress,'appearance_check'=>$appearance_check,'body_movement_behavior'=>$body_movement_behavior,'attitude'=>$attitude,'rate'=>$rate
            ,'rhythm'=>$rhythm,'speech_disorder'=>$speech_disorder,'stream_of_talk'=>$stream_of_talk,'mood'=>$mood,'affect'=>$affect,'thought_process'=>$thought_process
            ,'thought_content'=>$thought_content,'illution'=>$illution,'hallucination'=>$hallucination,'vision'=>$vision,'hearing'=>$hearing
            ,'tast_perception'=>$tast_perception,'touch'=>$touch,'smell'=>$smell,'orientation'=>$orientation,'orientation_time'=>$orientation_time
            ,'non_orientation'=>$non_orientation,'non_orientation_time'=>$non_orientation_time,'non_orientation_location'=>$non_orientation_location
            ,'non_orientation_person'=>$non_orientation_person,'attention1'=>$attention1,'attention2'=>$attention2,'attention3'=>$attention3
            ,'memory1'=>$memory1,'memory2'=>$memory2,'memory3'=>$memory3,'general_khowledge'=>$general_khowledge
            ,'abstract_difference1'=>$abstract_difference1,'abstract_difference2'=>$abstract_difference2,'abstract_difference3'=>$abstract_difference3
            ,'concrete_difference1'=>$concrete_difference1 ,'concrete_difference2'=>$concrete_difference2 ,'concrete_difference3'=>$concrete_difference3
            ,'concrete_similarities1'=>$concrete_similarities1,'concrete_similarities2'=>$concrete_similarities2,'concrete_similarities3'=>$concrete_similarities3
            ,'abstract_similarities1'=>$abstract_similarities1,'abstract_similarities2'=>$abstract_similarities2,'abstract_similarities3'=>$abstract_similarities3
            ,'concrete_aphorisms1'=>$concrete_aphorisms1,'concrete_aphorisms2'=>$concrete_aphorisms2,'concrete_aphorisms3'=>$concrete_aphorisms3
            ,'abstract_aphorisms1'=>$abstract_aphorisms1,'abstract_aphorisms2'=>$abstract_aphorisms2,'abstract_aphorisms3'=>$abstract_aphorisms3
            ,'judment1'=>$judment1,'judment2'=>$judment2,'judment3'=>$judment3,'insight'=>$insight,'orientation_location'=>$orientation_location,'orientation_person'=>$orientation_person
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
