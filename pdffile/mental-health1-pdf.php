<?php
require_once '../mains/datethai.php';
require_once '../include/Session.php';
 
                 
   $login = empty($_REQUEST['loginname']) ? null : $_REQUEST['loginname'];
   $loginname = $_SESSION['loginname'];
   $values =['loginname'=>$loginname];
   
   //หากพบว่าไม่ตรงกันให้ ทำลาย session เดิมทิ้งไป
   if(!$loginname){
    session_start();
    session_destroy();              
        
  }

  Session::checkLoginSessionAndShowMessage(); //เช็ค session

  if(!(
     Session::checkPermission('DOCUMENT', 'PRINT')
     )){
     return;
 }

 /*Session::checkLoginSessionAndShowMessage(); //เช็ค session
Session::checkPermissionAndShowMessage('DOCUMENT', 'PRINT');
*/
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
//require_once __DIR__ . '/vendor/autoload.php';
require_once '../include/DbUtils.php';
require_once '../include/Session.php';
require_once '../include/KphisQueryUtils.php';
require_once '../include/ReportQueryUtils.php';

date_default_timezone_set('asia/bangkok');

//echo $_SERVER['DOCUMENT_ROOT'] ;

$conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
$mpdf->AddPageByArray([
    'margin-left' => 6,
    'margin-right' => 6,
    'margin-top' => 6,
    'margin-bottom' => 6,
]);

$id = $_REQUEST['id'];
$an = empty($_REQUEST['an']) ? null : $_REQUEST['an'];
$hn = KphisQueryUtils::getHnByAn($an);
$query_parameters = ['an' => $an];
$query_parameters2 = ['an' => $an,'id' => $id];

Session::insertSystemAccessLog(json_encode(array(
    'report'=>'MENTAL-HEALTH1-PDF',
   // 'action'=>'PRINT',
    'an'=>$an,
),JSON_UNESCAPED_UNICODE));

//echo $id;

/*
$login = empty($_REQUEST['loginname']) ? null : $_REQUEST['loginname'];
$loginname = $_SESSION['loginname'];
$values = ['loginname' => $loginname];
if ($login != $loginname) {
    session_start();
    session_destroy();
}
*/
$image_uncheck = "<img src='../include/images/check-adm.jpg' width='1.6%' class='check_img'>";
$image_check = "<img src='../include/images/check-1.jpg' width='1.6%' class='check_img'>";
//-------------------------Doctor admission note
$sql = "SELECT pn.*,date(create_datetime) as rxdate
        FROM prs_mental_health1 pn
        WHERE pn.an = :an 
        limit 1";
$stmt = $conn->prepare($sql);
$stmt->execute($query_parameters);
$row  = $stmt->fetch();

$sql_item = "SELECT dr_adm_item.id,
                    dr_adm_item.doctor,
                    doctor.`name` AS admission_note_doctorname
                    FROM " . DbConstant::KPHIS_DBNAME . ".prs_pre_nursenote_item dr_adm_item
                    LEFT OUTER JOIN " . DbConstant::HOSXP_DBNAME . ".doctor ON doctor.code = dr_adm_item.doctor
                    WHERE an=:an
                    ORDER BY dr_adm_item.id ASC";
$stmt_item = $conn->prepare($sql_item);
$stmt_item->execute($query_parameters);
$pre_note_count = 0;
while ($row_item = $stmt_item->fetch()) {
    $id_pre_note[] = $row_item['id'];
    $doctor[] = $row_item['doctor'];
    $admission_note_doctorname[] = $row_item['admission_note_doctorname'];
    //$admission_note_doctorentryposition[] = $row_item['admission_note_doctorentryposition'];
    $pre_note_count++;
}

                       
                        $from_dep = $row['from_dep'];
                        $rxDate = $row['rxdate'];//วันที่ Discharge
                        $rxdate = date($rxDate);
                        $rxTime = $row['rxtime'];//เวลาที่ Discharge
                        $rxtime = date($rxTime);
                        $strDate =($rxdate."  ".$rxtime);
                       // $dchtime  = date('H:i', strtotime($origTime));



                       $appearance_1 = '( )';
                       if ($row['appearance'] == '1') {
                         $appearance_1 = '('.$image_check.')';
                       }
                      
                       $appearance_2 = '( )';
                       if ($row['appearance'] == '2') {
                         $appearance_2 = '('.$image_check.')';
                       }
                       $appearance_3 = '( )';
                       if ($row['appearance'] == '3') {
                         $appearance_3 = '('.$image_check.')';
                       }

                       $appearance_4 = '( )';
                       if ($row['appearance'] == '4') {
                         $appearance_4 = '('.$image_check.')';
                       }

                       $appearance_check1 = '( )';
                       if ($row['appearance_check'] == '1'  ) {$appearance_check1 = '('.$image_check.')';
                        $appearance1  =  htmlspecialchars($row['appearance']);
                       }

                       
                       $appearance_check2 = '( )';
                       if ($row['appearance_check'] == '2' ) {$appearance_check2 = '('.$image_check.')';
                        $appearance2  =  htmlspecialchars($row['appearance']);
                       }


                       $dress_1 = '( )';
                       if ($row['dress'] == '1') {
                         $dress_1 = '('.$image_check.')';
                       }
                      
                       $dress_2 = '( )';
                       if ($row['dress'] == '2') {
                         $dress_2 = '('.$image_check.')';
                       }
                       $dress_3 = '( )';
                       if ($row['dress'] == '3') {
                         $dress_3 = '('.$image_check.')';
                       }

                       $dress_4 = '( )';
                       if ($row['dress'] == '4') {
                         $dress_4 = '('.$image_check.')';
                       }

                       
                       $body_movement_behavior_1 = '( )';
                       if ($row['body_movement_behavior'] == 'ปกติ') {
                        $body_movement_behavior_1 = '('.$image_check.')';
                       }
                      
                       $body_movement_behavior_2 = '( )';
                       if ($row['body_movement_behavior'] == 'น้อยกว่าปกติ') {
                        $body_movement_behavior_2 = '('.$image_check.')';
                       }

                       $body_movement_behavior_3 = '( )';
                       if ($row['body_movement_behavior'] == 'มากกว่าปกติ') {
                        $body_movement_behavior_3 = '('.$image_check.')';
                       }

                       $body_movement_behavior_4 = '( )';
                       if ($row['body_movement_behavior'] != 'ปกติ'  && $row['body_movement_behavior'] != 'น้อยกว่าปกติ' && $row['body_movement_behavior'] != 'มากกว่าปกติ' 
                       && $row['body_movement_behavior'] != 'เคลื่อนไหวซ้ำๆ'  && $row['body_movement_behavior'] != 'กระตุก' && $row['body_movement_behavior'] != 'อยู่ไม่สุข' 
                       && $row['body_movement_behavior'] != 'กระสับกระส่าย' && $row['body_movement_behavior'] != null) {
                        $body_movement_behavior_4 = '('.$image_check.')';
                        $body_movement_behavior  =  htmlspecialchars($row['body_movement_behavior']);
                       }

                       $body_movement_behavior_5 = '( )';
                       if ($row['body_movement_behavior'] == 'เคลื่อนไหวซ้ำๆ') {
                        $body_movement_behavior_5 = '('.$image_check.')';
                       }

                       $body_movement_behavior_6 = '( )';
                       if ($row['body_movement_behavior'] == 'กระตุก') {
                        $body_movement_behavior_6 = '('.$image_check.')';
                       }

                       $body_movement_behavior_7 = '( )';
                       if ($row['body_movement_behavior'] == 'อยู่ไม่สุข') {
                        $body_movement_behavior_7 = '('.$image_check.')';
                       }

                       $body_movement_behavior_8 = '( )';
                       if ($row['body_movement_behavior'] == 'กระสับกระส่าย') {
                        $body_movement_behavior_8 = '('.$image_check.')';
                       }
                       
                       $attitude_1 = '( )';
                       if ($row['attitude'] == '1') {
                        $attitude_1 = '('.$image_check.')';
                       }
                       $attitude_2 = '( )';
                       if ($row['attitude'] == '2') {
                        $attitude_2 = '('.$image_check.')';
                       }
                       $attitude_3 = '( )';
                       if ($row['attitude'] == '3') {
                        $attitude_3 = '('.$image_check.')';
                       }
                       $attitude_4 = '( )';
                       if ($row['attitude'] == '4') {
                        $attitude_4 = '('.$image_check.')';
                       }
                       $attitude_5 = '( )';
                       if ($row['attitude'] == '5') {
                        $attitude_5 = '('.$image_check.')';
                       }
                       $attitude_6 = '( )';
                       if ($row['attitude'] == '6') {
                        $attitude_6 = '('.$image_check.')';
                       }

                       $rate_1 = '( )';
                       if ($row['rate'] == '1') {
                        $rate_1 = '('.$image_check.')';
                       }
                       $rate_2 = '( )';
                       if ($row['rate'] == '2') {
                        $rate_2 = '('.$image_check.')';
                       }
                       $rate_3 = '( )';
                       if ($row['rate'] == '3') {
                        $rate_3 = '('.$image_check.')';
                       }

                       $rhythm_1 = '( )';
                       if ($row['rhythm'] == '1') {
                        $rhythm_1 = '('.$image_check.')';
                       }
                       $rhythm_2 = '( )';
                       if ($row['rhythm'] == '2') {
                        $rhythm_2 = '('.$image_check.')';
                       }
                       $rhythm_3 = '( )';
                       if ($row['rhythm'] == '3') {
                        $rhythm_3 = '('.$image_check.')';
                       }
                       
                       $speech_disorder_1 = '( )';
                       if ($row['speech_disorder'] == 'ปกติ') {
                        $speech_disorder_1 = '('.$image_check.')';
                       }
                       $speech_disorder_2 = '( )';
                       if ($row['speech_disorder'] != 'ปกติ' && $row['speech_disorder'] != 'neologism' && $row['speech_disorder'] != 'world salad' && $row['speech_disorder'] != null) {
                        $speech_disorder_2 = '('.$image_check.')';
                        $speech_disorder  =  htmlspecialchars($row['speech_disorder']);
                       }
                       $speech_disorder_3 = '( )';
                       if ($row['speech_disorder'] == 'neologism') {
                        $speech_disorder_3 = '('.$image_check.')';
                       }
                       $speech_disorder_4 = '( )';
                       if ($row['speech_disorder'] == 'world salad') {
                        $speech_disorder_4 = '('.$image_check.')';
                       }

                       $stream_of_talk_1 = '( )';
                       if ($row['stream_of_talk'] == '1') {
                        $stream_of_talk_1 = '('.$image_check.')';
                       }
                       $stream_of_talk_2 = '( )';
                       if ($row['stream_of_talk'] == '2') {
                        $stream_of_talk_2 = '('.$image_check.')';
                       }
                       $stream_of_talk_3 = '( )';
                       if ($row['stream_of_talk'] == '3') {
                        $stream_of_talk_3 = '('.$image_check.')';
                       }
                       $stream_of_talk_4 = '( )';
                       if ($row['stream_of_talk'] == '4') {
                        $stream_of_talk_4 = '('.$image_check.')';
                       }
                       $stream_of_talk_5 = '( )';
                       if ($row['stream_of_talk'] == '5') {
                        $stream_of_talk_5 = '('.$image_check.')';
                       }
                       $stream_of_talk_6 = '( )';
                       if ($row['stream_of_talk'] == '6') {
                        $stream_of_talk_6 = '('.$image_check.')';
                       }
                       $stream_of_talk_7 = '( )';
                       if ($row['stream_of_talk'] == '7') {
                        $stream_of_talk_7 = '('.$image_check.')';
                       }
                       $stream_of_talk_8 = '( )';
                       if ($row['stream_of_talk'] == '8') {
                        $stream_of_talk_8 = '('.$image_check.')';
                       }

                       $mood_1 = '( )';
                       if ($row['mood'] == 'เศร้า') {
                        $mood_1 = '('.$image_check.')';
                       }
                       $mood_2 = '( )';
                       if ($row['mood'] == 'หงุดหงิด') {
                        $mood_2 = '('.$image_check.')';
                       }
                       $mood_3 = '( )';
                       if ($row['mood'] == 'กังวล') {
                        $mood_3 = '('.$image_check.')';
                       }
                       $mood_4 = '( )';
                       if ($row['mood'] == 'ครื้นเครง') {
                        $mood_4 = '('.$image_check.')';
                       }
                       $mood_5 = '( )';
                       if ($row['mood'] != 'เศร้า' && $row['mood'] != 'หงุดหงิด' && $row['mood'] != 'กังวล' && $row['mood'] != 'ครื้นเครง' && $row['mood'] != NULL) {
                        $mood_5 = '('.$image_check.')';
                        $mood =  htmlspecialchars($row['mood']);
                       }

                       $affect_1 = '( )';
                       if ($row['affect'] == '1') {
                        $affect_1 = '('.$image_check.')';
                       }
                       $affect_2 = '( )';
                       if ($row['affect'] == '2') {
                        $affect_2 = '('.$image_check.')';
                       }
                       $affect_3 = '( )';
                       if ($row['affect'] == '3') {
                        $affect_3 = '('.$image_check.')';
                       }
                       $affect_4 = '( )';
                       if ($row['affect'] == '4') {
                        $affect_4 = '('.$image_check.')';
                       }
                       $affect_5 = '( )';
                       if ($row['affect'] == '5') {
                        $affect_5 = '('.$image_check.')';
                       }
                       $affect_6 = '( )';
                       if ($row['affect'] == '6') {
                        $affect_6 = '('.$image_check.')';
                       }
                       $affect_7 = '( )';
                       if ($row['affect'] == '7') {
                        $affect_7 = '('.$image_check.')';
                       }
                       $affect_8 = '( )';
                       if ($row['affect'] == '8') {
                        $affect_8 = '('.$image_check.')';
                       }

                       $thought_process_1 = '( )';
                       if ($row['thought_process'] == '1') {
                        $thought_process_1 = '('.$image_check.')';
                       }
                       $thought_process_2 = '( )';
                       if ($row['thought_process'] == '2') {
                        $thought_process_2 = '('.$image_check.')';
                       }
                       $thought_process_3 = '( )';
                       if ($row['thought_process'] == '3') {
                        $thought_process_3 = '('.$image_check.')';
                       }
                       $thought_process_4 = '( )';
                       if ($row['thought_process'] == '4') {
                        $thought_process_4 = '('.$image_check.')';
                       }
                       $thought_process_5 = '( )';
                       if ($row['thought_process'] == '5') {
                        $thought_process_5 = '('.$image_check.')';
                       }
                       $thought_process_6 = '( )';
                       if ($row['thought_process'] == '6') {
                        $thought_process_6 = '('.$image_check.')';
                       }
                       $thought_process_7 = '( )';
                       if ($row['thought_process'] == '7') {
                        $thought_process_7 = '('.$image_check.')';
                       }
                       $thought_process_8 = '( )';
                       if ($row['thought_process'] == '8') {
                        $thought_process_8 = '('.$image_check.')';
                       }
                       $thought_process_9 = '( )';
                       if ($row['thought_process'] == '9') {
                        $thought_process_9 = '('.$image_check.')';
                       }
                       $thought_process_10 = '( )';
                       if ($row['thought_process'] == '10') {
                        $thought_process_10 = '('.$image_check.')';
                       }
                       $thought_process_11 = '( )';
                       if ($row['thought_process'] == '11') {
                        $thought_process_11 = '('.$image_check.')';
                       }

                       $thought_content_1 = '( )';
                       if ($row['thought_content'] == '1') {
                        $thought_content_1 = '('.$image_check.')';
                       }
                       $thought_content_2 = '( )';
                       if ($row['thought_content'] == '2') {
                        $thought_content_2 = '('.$image_check.')';
                       }
                       $thought_content_3 = '( )';
                       if ($row['thought_content'] == '3') {
                        $thought_content_3 = '('.$image_check.')';
                       }
                       $thought_content_4 = '( )';
                       if ($row['thought_content'] == '4') {
                        $thought_content_4 = '('.$image_check.')';
                       }
                       $thought_content_5 = '( )';
                       if ($row['thought_content'] == '5') {
                        $thought_content_5 = '('.$image_check.')';
                       }
                       $thought_content_6 = '( )';
                       if ($row['thought_content'] == '6') {
                        $thought_content_6 = '('.$image_check.')';
                       }

                       $illution_1 = '( )';
                       if ($row['illution'] == 'ไม่มี') {
                        $illution_1 = '('.$image_check.')';
                       }
                       $illution_2 = '( )';
                       if ($row['illution'] != 'ไม่มี' && $row['illution'] != null) {
                        $illution_2 = '('.$image_check.')';
                        $illution =  htmlspecialchars($row['illution']);
                       }

                       $hallucination_1 = '( )';
                       if ($row['hallucination'] == 'ไม่มี') {
                        $hallucination_1 = '('.$image_check.')';
                       }
                       $hallucination_2 = '( )';
                       if ($row['hallucination'] != 'ไม่มี' && $row['hallucination'] != null) {
                        $hallucination_2 = '('.$image_check.')';
                        $hallucination =  htmlspecialchars($row['hallucination']);
                       }

                       $vision_1 = '( )';
                       if ($row['vision'] != null) {
                        $vision_1 = '('.$image_check.')';
                        $vision =  htmlspecialchars($row['vision']);
                       }
                       $hearing_1 = '( )';
                       if ($row['hearing'] != null) {
                        $hearing_1 = '('.$image_check.')';
                        $hearing =  htmlspecialchars($row['hearing']);
                       }
                       $tast_perception_1 = '( )';
                       if ($row['tast_perception'] != null) {
                        $tast_perception_1 = '('.$image_check.')';
                        $tast_perception =  htmlspecialchars($row['tast_perception']);
                       }
                       $touch_1 = '( )';
                       if ($row['touch'] != null) {
                        $touch_1 = '('.$image_check.')';
                        $touch =  htmlspecialchars($row['touch']);
                       }
                       $smell_1 = '( )';
                       if ($row['smell'] != null) {
                        $smell_1 = '('.$image_check.')';
                        $smell =  htmlspecialchars($row['smell']);
                       }

                       $orientation = '( )';
                       if ($row['orientation'] == 'Y') {
                        $orientation = '('.$image_check.')';
                       }
                       $orientation_time = '( )';
                       if ($row['orientation_time'] == 'Y') {
                        $orientation_time = '('.$image_check.')';
                       }
                       $orientation_location = '( )';
                       if ($row['orientation_location'] == 'Y') {
                        $orientation_location = '('.$image_check.')';
                       }
                       $orientation_person = '( )';
                       if ($row['orientation_person'] == 'Y') {
                        $orientation_person = '('.$image_check.')';
                       }
                       $non_orientation = '( )';
                       if ($row['non_orientation'] == 'Y') {
                        $non_orientation = '('.$image_check.')';
                       }
                       $non_orientation_time = '( )';
                       if ($row['non_orientation_time'] == 'Y') {
                        $non_orientation_time = '('.$image_check.')';
                       }
                       $non_orientation_location = '( )';
                       if ($row['non_orientation_location'] == 'Y') {
                        $non_orientation_location = '('.$image_check.')';
                       }
                       $non_orientation_person = '( )';
                       if ($row['non_orientation_person'] == 'Y') {
                        $non_orientation_person = '('.$image_check.')';
                       }

                       $attention1_1 = '( )';
                       if ($row['attention1'] == '1') {
                        $attention1_1 = '('.$image_check.')';
                       }
                       $attention1_2 = '( )';
                       if ($row['attention1'] == '2') {
                        $attention1_2 = '('.$image_check.')';
                       }
                       $attention1_3 = '( )';
                       if ($row['attention1'] == '3') {
                        $attention1_3 = '('.$image_check.')';
                       }

                       $attention2_1 = '( )';
                       if ($row['attention2'] == '1') {
                        $attention2_1 = '('.$image_check.')';
                       }
                       $attention2_2 = '( )';
                       if ($row['attention2'] == '2') {
                        $attention2_2 = '('.$image_check.')';
                       }
                       $attention2_3 = '( )';
                       if ($row['attention2'] == '3') {
                        $attention2_3 = '('.$image_check.')';
                       }

                       $attention3_1 = '( )';
                       if ($row['attention3'] == '1') {
                        $attention3_1 = '('.$image_check.')';
                       }
                       $attention3_2 = '( )';
                       if ($row['attention3'] == '2') {
                        $attention3_2 = '('.$image_check.')';
                       }
                       $attention3_3 = '( )';
                       if ($row['attention3'] == '3') {
                        $attention3_3 = '('.$image_check.')';
                       }

                       $memory1_1 = '( )';
                       if ($row['memory1'] == '1') {
                        $memory1_1 = '('.$image_check.')';
                       }
                       $memory1_2 = '( )';
                       if ($row['memory1'] == '2') {
                        $memory1_2 = '('.$image_check.')';
                       }                     

                       $memory2_1 = '( )';
                       if ($row['memory2'] == '1') {
                        $memory2_1 = '('.$image_check.')';
                       }
                       $memory2_2 = '( )';
                       if ($row['memory2'] == '2') {
                        $memory2_2 = '('.$image_check.')';
                       }
                       
                       $memory3_1 = '( )';
                       if ($row['memory3'] == '1') {
                        $memory3_1 = '('.$image_check.')';
                       }
                       $memory3_2 = '( )';
                       if ($row['memory3'] == '2') {
                        $memory3_2 = '('.$image_check.')';
                       }

                       $general_khowledge_1 = '( )';
                       if ($row['general_khowledge'] == '1') {
                        $general_khowledge_1 = '('.$image_check.')';
                       }
                       $general_khowledge_2 = '( )';
                       if ($row['general_khowledge'] == '2') {
                        $general_khowledge_2 = '('.$image_check.')';
                       }


                       $concrete_difference1 = '( )';
                       if ($row['concrete_difference1'] == 'Y') {
                        $concrete_difference1 = '('.$image_check.')';
                       }
                       $concrete_difference2 = '( )';
                       if ($row['concrete_difference2'] == 'Y') {
                        $concrete_difference2 = '('.$image_check.')';
                       }
                       $concrete_difference3 = '( )';
                       if ($row['concrete_difference3'] == 'Y') {
                        $concrete_difference3 = '('.$image_check.')';
                       }
                       
                       $abstract_difference1 = '( )';
                       if ($row['abstract_difference1'] == 'Y') {
                        $abstract_difference1 = '('.$image_check.')';
                       }
                       $abstract_difference2 = '( )';
                       if ($row['abstract_difference2'] == 'Y') {
                        $abstract_difference2 = '('.$image_check.')';
                       }
                       $abstract_difference3 = '( )';
                       if ($row['abstract_difference3'] == 'Y') {
                        $abstract_difference3 = '('.$image_check.')';
                       }

                       $concrete_similarities1 = '( )';
                       if ($row['concrete_similarities1'] == 'Y') {
                        $concrete_similarities1 = '('.$image_check.')';
                       }
                       $concrete_similarities2 = '( )';
                       if ($row['concrete_similarities2'] == 'Y') {
                        $concrete_similarities2 = '('.$image_check.')';
                       }
                       $concrete_similarities3 = '( )';
                       if ($row['concrete_similarities3'] == 'Y') {
                        $concrete_similarities3 = '('.$image_check.')';
                       }

                       $abstract_similarities1 = '( )';
                       if ($row['abstract_similarities1'] == 'Y') {
                        $abstract_similarities1 = '('.$image_check.')';
                       }
                       $abstract_similarities2 = '( )';
                       if ($row['abstract_similarities2'] == 'Y') {
                        $abstract_similarities2 = '('.$image_check.')';
                       }
                       $abstract_similarities3 = '( )';
                       if ($row['abstract_similarities3'] == 'Y') {
                        $abstract_similarities3 = '('.$image_check.')';
                       }

                       $concrete_aphorisms1 = '( )';
                       if ($row['concrete_aphorisms1'] == 'Y') {
                        $concrete_aphorisms1 = '('.$image_check.')';
                       }
                       $concrete_aphorisms2 = '( )';
                       if ($row['concrete_aphorisms2'] == 'Y') {
                        $concrete_aphorisms2 = '('.$image_check.')';
                       }
                       $concrete_aphorisms3 = '( )';
                       if ($row['concrete_aphorisms3'] == 'Y') {
                        $concrete_aphorisms3 = '('.$image_check.')';
                       }
                       
                       $abstract_aphorisms1 = '( )';
                       if ($row['abstract_aphorisms1'] == 'Y') {
                        $abstract_aphorisms1 = '('.$image_check.')';
                       }
                       $abstract_aphorisms2 = '( )';
                       if ($row['abstract_aphorisms2'] == 'Y') {
                        $abstract_aphorisms2 = '('.$image_check.')';
                       }
                       $abstract_aphorisms3 = '( )';
                       if ($row['abstract_aphorisms3'] == 'Y') {
                        $abstract_aphorisms3 = '('.$image_check.')';
                       }

                       $judment1_1 = '( )';
                       if ($row['judment1'] == '1') {
                        $judment1_1 = '('.$image_check.')';
                       }
                       $judment1_2 = '( )';
                       if ($row['judment1'] == '2') {
                        $judment1_2 = '('.$image_check.')';
                       }

                       $judment2_1 = '( )';
                       if ($row['judment2'] == '1') {
                        $judment2_1 = '('.$image_check.')';
                       }
                       $judment2_2 = '( )';
                       if ($row['judment2'] == '2') {
                        $judment2_2 = '('.$image_check.')';
                       }

                       $judment3_1 = '( )';
                       if ($row['judment3'] == '1') {
                        $judment3_1 = '('.$image_check.')';
                       }
                       $judment3_2 = '( )';
                       if ($row['judment3'] == '2') {
                        $judment3_2 = '('.$image_check.')';
                       }

                       $insight_1 = '( )';
                       if ($row['insight'] == '1') {
                        $insight_1 = '('.$image_check.')';
                       }
                       $insight_2 = '( )';
                       if ($row['insight'] == '2') {
                        $insight_2 = '('.$image_check.')';
                       }
                       $insight_3 = '( )';
                       if ($row['insight'] == '3') {
                        $insight_3 = '('.$image_check.')';
                       }
                       $insight_4 = '( )';
                       if ($row['insight'] == '4') {
                        $insight_4 = '('.$image_check.')';
                       }
                       $insight_5 = '( )';
                       if ($row['insight'] == '5') {
                        $insight_5 = '('.$image_check.')';
                       }
                       $insight_6 = '( )';
                       if ($row['insight'] == '6') {
                        $insight_6 = '('.$image_check.')';
                       }
                       



//-------------------------Doctor admission note
$sql_ipt = "select patient.sex,patient.hn,patient.pname,patient.fname,patient.lname,patient.drugallergy,
            (select GROUP_CONCAT(concat(opd_allergy.agent,'=',if(opd_allergy.symptom is null,',',opd_allergy.symptom)/*,' [',if(note is null,',',note),']'*/)) as name
                from ".DbConstant::HOSXP_DBNAME.".opd_allergy
                where opd_allergy.hn = ipt.hn /*and (opd_allergy.no_alert<>'Y' or opd_allergy.no_alert is null)*/
                order by display_order) as allergy_symptom_string,
            an_stat.age_y,an_stat.age_m,an_stat.age_d,
            ipt.regdate,ipt.regtime,
            ipt.ward,ward.name,
            ipt.pttype, pttype.`name` as pttype_name,
            iptadm.bedno, (select vs.bw from ipd_vs_vital_sign vs where vs.an = ipt.an and vs.bw is not null and trim(vs.bw) <> '' order by vs_datetime desc limit 1) as latest_bw
            , (select vs.height from ipd_vs_vital_sign vs where vs.an = ipt.an and vs.bw is not null and trim(vs.bw) <> '' order by vs_datetime desc limit 1) as latest_height
            from ".DbConstant::HOSXP_DBNAME.".ipt
            left outer join ".DbConstant::HOSXP_DBNAME.".an_stat on an_stat.an=ipt.an
            left outer join ".DbConstant::HOSXP_DBNAME.".patient on patient.hn=ipt.hn
            left outer join ".DbConstant::HOSXP_DBNAME.".ward on ward.ward=ipt.ward
            LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".pttype ON pttype.pttype = ipt.pttype
            LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".iptadm ON iptadm.an = ipt.an
            WHERE ipt.an=:an";
        $stmt_ipt = $conn->prepare($sql_ipt);
        $stmt_ipt->execute(['an'=>$an]);
        $row_ipt = $stmt_ipt->fetch();
        $regdatetime = $row_ipt['regdate'].' '.$row_ipt['regtime'];//ใช้ในการดึงข้อมูล ประวัติการผ่าตัด


  
        $receive_date        =  $row['receive_date'];
        $receive_time        =  $row['receive_time'];

        $ids = '20'; //Link menu
        $check_    = ReportQueryUtils::getProduction($ids);

        $check_report = '( )';
        if ($check_  == '1') 
        {$check_report = '&nbsp;<font color="red">รอปรับรายงาน</font>';
        } else {
            $check_report = '';
        }
       

      
        $icu_form1 = "<img src='../include/images/icu-form1.png' width='100%' >";
        $icu_form2 = "<img src='../include/images/icu-form2.png' width='100%' >";
        $icu_form3 = "<img src='../include/images/icu-form3.png' width='100%' >";
        
       // $maxNrOfPages = ceil($max/$itemsPerPage);

$head =
'

    <style>
    div.f15 {
 
        font-size: 12px; 
        
      }
      div.line_dotted {
        text-decoration: underline dotted;  
        text-decoration-color: rgb(105,42,49); 
        font-size: 12px;
        text-decoration-style: dotted;  
      }

        body{
            font-family: "Garuda";//เรียกใช้font Garuda สำหรับแสดงผล ภาษาไทย
        }
        footer {
            position: fixed;
            bottom: -60px;
            left: 0px;
            right: 0px;
            height: 70px;

            /** Extra personal styles **/
            line-height: 35px;
        }
        br {
            display: block;
            content: " ";
            margin: 10px 0;
            height:10pt;
            line-height: 150%;
        }
        #show_img_select  {
            background-image: url("../include/images/allbody.jpg");
            background-position: center;
            background-repeat: no-repeat;
            background-image-resize:5;
            height:180px;
        }

        .container {
            display: flex;
            justify-content: space-between;
        }
        .column {
            flex: 1;
            padding: 10px;
            margin: 5px;
            border: 1px solid #ccc;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            border: 1px solid #ccc;
            padding: 10px;

        }

        
    </style>
    <h2 style="text-align:right;font-size:8pt;">&nbsp;</h2>
    
    <h2 style="text-align:center;font-size:11pt;">แบบประเมินสภาพจิต &nbsp;'.htmlspecialchars(DbConstant::HOSPITAL_NAME).$check_report.'</h2>
    
   
<div class="f15"> วันที่ <b>'.LongDateThai2($strDate).'<br>'
.'<table id="bg-table" width="100%" style="border-collapse: collapse;font-size:8pt;margin-top:2px;">'.

'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
' <div class="form-group row alert alert-dark text-left">
<B>1 ลักษณะโดยทั่วไป</B><br><br>
<B>&nbsp;&nbsp;&nbsp;1.1 Generation appearance</B>

</div>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label><b>1.1.1 รูปร่างลักษณะ</b></label>&nbsp;'.
$appearance_1.'&nbsp;อ้วน&nbsp;'
.$appearance_2.'&nbsp;สันทัด&nbsp;'
.$appearance_3.'&nbsp;สันทัด&nbsp;'
.$appearance_4.'&nbsp;พิการ&nbsp;'
.$appearance_check1.'&nbsp;มีแผลเป็น&nbsp;'
.'<u>'.$appearance1.'</u>&nbsp;'
.$appearance_check2.'&nbsp;&nbsp;อื่นๆ&nbsp;'
.'<u>'.$appearance2.'</u>'.
'</div>'.
'<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label><b>1.1.2 การแต่งกาย</b></label>&nbsp;'.
$dress_1.'&nbsp;สะอาด เหมาะสมกับวัย&nbsp;&nbsp;&nbsp;&nbsp;'
.$dress_2.'&nbsp;สะอาด ไม่เหมาะสมกับวัย&nbsp;&nbsp;&nbsp;&nbsp;'
.$dress_3.'&nbsp;สกปรก เหมาะสมกับวัย&nbsp;&nbsp;&nbsp;&nbsp;'
.$dress_4.'&nbsp;สกปรก ไม่เหมาะสมกับวัย&nbsp;&nbsp;&nbsp;&nbsp;'
.'</div>'.
'<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label><b>1.1.3 พฤติกรรมการเคลื่อนไหวร่างการ(Psychomotor)</label></b>&nbsp;'.
$body_movement_behavior_1.'&nbsp;ปกติ&nbsp;'
.$body_movement_behavior_2.'&nbsp;น้อยกว่าปกติ&nbsp;'
.$body_movement_behavior_3.'&nbsp;มากกว่าปกติ&nbsp;'
.$body_movement_behavior_4.'&nbsp;ผิดปกติ&nbsp;'
.'<u>'.$body_movement_behavior.'</u>&nbsp;'
.$body_movement_behavior_5.'&nbsp;เคลื่อนไหวซ้ำๆ&nbsp;'
.$body_movement_behavior_6.'&nbsp;กระตุก&nbsp;'
.$body_movement_behavior_7.'&nbsp;อยู่ไม่สุข&nbsp;'
.$body_movement_behavior_8.'&nbsp;กระสับกระส่าย'
.'</div>'.
'<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label><b>1.1.4 ท่าทีต่อผู้ตรวจ(Attitude)</b></label>&nbsp;'.
$attitude_1.'&nbsp;เป็นมิตร&nbsp;&nbsp;&nbsp;&nbsp;'
.$attitude_2.'&nbsp;ต่อต้าน&nbsp;&nbsp;&nbsp;&nbsp;'
.$attitude_3.'&nbsp;ไม่ไว้วางใจ&nbsp;&nbsp;&nbsp;&nbsp;'
.$attitude_4.'&nbsp;ไม่เชื่อถือ&nbsp;&nbsp;&nbsp;&nbsp;'
.$attitude_5.'&nbsp;ยียวน&nbsp;&nbsp;&nbsp;&nbsp;'
.$attitude_6.'&nbsp;ปิดบังข้อมูล&nbsp;&nbsp;&nbsp;&nbsp;'
.'</div>'.

'</td>'.
'</tr>'.

'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
' <div class="form-group row alert alert-dark text-left">
<B>2. คำพูดและกระแสคำพูด (speech and stream talk)</B><br>
</div>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label><b>2.1 อัตราการพูด (Rate)</b></label>&nbsp;'.
$rate_1.'&nbsp;ปกติ&nbsp;'
.$rate_2.'&nbsp;เร็ว&nbsp;'
.$rate_3.'&nbsp;ช้า&nbsp;'
.'</div>'.
'<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label><b>2.2 จังหวะ (Rhythm)</b></label>&nbsp;'.
$rhythm_1.'&nbsp;พูดราบเรียบ&nbsp;'
.$rhythm_2.'&nbsp;ติดขัด&nbsp;'
.$rhythm_3.'&nbsp;ติดอ่าง&nbsp;'
.'</div>'.
'<br>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label><b>2.3 ความผิดปกติของคำพูด</label></b>&nbsp;'.
$speech_disorder_1.'&nbsp;ปกติ&nbsp;'
.$speech_disorder_2.'&nbsp;ผิดปกติ&nbsp;'
.'<u>'.$speech_disorder.'</u>&nbsp;'
.$speech_disorder_3.'&nbsp;คำพูดฟังแล้วไม่รู้ความหมาย (neologism)&nbsp;'
.$speech_disorder_4.'&nbsp;เอาคำหรือวลีมารวมกันแต่ไม่มีความหมาย (word salad)&nbsp;'
.'</div>'.
'<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label><b>2.4 กระแสคำพูด (stream of talk)</b></label>&nbsp;'.
$stream_of_talk_1.'&nbsp;สมเหตุสมผล&nbsp;'
.$stream_of_talk_2.'&nbsp;ไม่สมเหตุสมผล (illogical)&nbsp;'
.$stream_of_talk_3.'&nbsp;ประติดประต่อ&nbsp;'
.$stream_of_talk_4.'&nbsp;ไม่ประติดประต่อ (incoherrence)&nbsp;'
.$stream_of_talk_5.'&nbsp;ตรงคำถาม&nbsp;'
.$stream_of_talk_6.'&nbsp;ไม่ตรงคำถาม (irelevance)&nbsp;'
.$stream_of_talk_7.'&nbsp;พูดวกวน&nbsp;'
.$stream_of_talk_8.'&nbsp;ไม่พูดเลย (mutism)&nbsp;'
.'</div>'.

'</td>'.
'</tr>'.

'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
' <div class="form-group row alert alert-dark text-left">
<B>3. อารมณ์และการแสดงออก (Mood and Affect)</B><br>
</div>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label><b>3.1 พื้นฐานอารมณ์ (Mood)</b></label>&nbsp;'.
$mood_1.'&nbsp;เศร้า&nbsp;'
.$mood_2.'&nbsp;หงุดหงิด&nbsp;'
.$mood_3.'&nbsp;กังวล&nbsp;'
.$mood_4.'&nbsp;ครื้นเครง&nbsp;'
.$mood_5.'&nbsp;อื่นๆ&nbsp;'
.'<u>'.$mood.'</u>'
.'</div>'.
'<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label><b>3.2 อารมณ์ที่แสดงออกขณะนั้น (Affect)</b></label>&nbsp;'.
$affect_1.'&nbsp;อารมณ์ดี&nbsp;'
.$affect_2.'&nbsp;เศร้า&nbsp;'
.$affect_3.'&nbsp;แสดงออกเล็กน้อย&nbsp;'
.$affect_4.'&nbsp;ปราศจากอารมณ์&nbsp;'
.$affect_5.'&nbsp;เหมาะสมกับสิ่งที่เล่า&nbsp;<br />'
.$affect_6.'&nbsp;ไม่เหมาะสมกับสิ่งที่เล่า&nbsp;'
.$affect_7.'&nbsp;คงที่&nbsp;'
.$affect_8.'&nbsp;เปลี่ยนแปลงง่าย&nbsp;'
.'</div>'.

'</td>'.
'</tr>'.

'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
' <div class="form-group row alert alert-dark text-left">
<B>4. ความคิด (Thought)</B><br>
</div>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label><b>4.1 กระบวนความคิด </b></label>&nbsp;'.
$thought_process_1.'&nbsp;คิดช้า&nbsp;'
.$thought_process_2.'&nbsp;คิดเร็ว&nbsp;'
.$thought_process_3.'&nbsp;คิดเร็วมากเปลี่ยนเรื่องคุยบ่อย&nbsp;'
.$thought_process_4.'&nbsp;ความคิดต่อเนื่อง&nbsp;'
.$thought_process_5.'&nbsp;ความคิดไม่ต่อเนื่อง&nbsp;'
.$thought_process_6.'&nbsp;ตรงคำถาม&nbsp;'
.$thought_process_7.'&nbsp;ไม่ตรงคำถาม&nbsp;<br />'
.$thought_process_8.'&nbsp;ได้เรื่องราว&nbsp;'
.$thought_process_9.'&nbsp;ไม่ได้เรื่องราว&nbsp;'
.$thought_process_10.'&nbsp;มีเหตุผล&nbsp;'
.$thought_process_11.'&nbsp;ไม่มีเหตุผล&nbsp;'
.'</div>'.
'<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label><b>4.2 เนื้อหาความคิด</b></label>&nbsp;'.
$thought_content_1.'&nbsp;ปกติ&nbsp;'
.$thought_content_2.'&nbsp;หมกมุ่น&nbsp;'
.$thought_content_3.'&nbsp;ย้ำคิดย้ำทำ&nbsp;'
.$thought_content_4.'&nbsp;กลัวผิคปกติ&nbsp;'
.$thought_content_5.'&nbsp;หลงผิด&nbsp;'
.$thought_content_6.'&nbsp;คิดฆ่าตัวตาย&nbsp;'

.'</div>'.

'</td>'.
'</tr>'.


'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
' <div class="form-group row alert alert-dark text-left">
<B>5. การรับรู้ (Perception)</B><br>
</div>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label><b>5.1 อาการแปลสิ่งเร้าผิด (illution) </b></label>&nbsp;'.
$illution_1.'&nbsp;ไม่มี&nbsp;'
.$illution_2.'&nbsp;มี ระบุ&nbsp;'
.'<u>'.$illution.'</u>'

.'</div>'.
'<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label><b>5.2 อาการประสาทหลอน (Hallucination)</b></label>&nbsp;'.
$hallucination_1.'&nbsp;ไม่มี&nbsp;'
.$hallucination_2.'&nbsp;มี ระบุ&nbsp;'
.'<u>'.$hallucination.'</u>'

.'</div>'.
'<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label></label>&nbsp;'.
$vision_1.'&nbsp;การมองเห็น&nbsp;'
.'<u>'.$vision.'</u>&nbsp;'
.$hearing_1.'&nbsp;การได้ยิน&nbsp;'
.'<u>'.$hearing.'</u>&nbsp;'
.$tast_perception_1.'&nbsp;การรับรู้รส&nbsp;'
.'<u>'.$tast_perception.'</u>&nbsp;'
.$touch_1.'&nbsp;การสัมผัส&nbsp;'
.'<u>'.$touch.'</u>&nbsp;'
.$smell_1.'&nbsp;การได้กลิ่น&nbsp;'
.'<u>'.$smell.'</u>&nbsp;'
.'</div>'.

'</td>'.
'</tr>'.


'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
' <div class="form-group row alert alert-dark text-left">
<B>6. Cognitive Function</B><br>
</div>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label><b>6.1 Orientation</b></label>&nbsp;'.
$orientation.'&nbsp;<b>รับรู้</b>&nbsp;'
.$orientation_time.'&nbsp;เวลา&nbsp;'
.$orientation_location.'&nbsp;สถานที่&nbsp;'
.$orientation_person.'&nbsp;บุคคล&nbsp;'
.$non_orientation.'&nbsp;<b>ไม่รับรู้</b>&nbsp;'
.$non_orientation_time.'&nbsp;เวลา&nbsp;'
.$non_orientation_location.'&nbsp;สถานที่&nbsp;'
.$non_orientation_person.'&nbsp;บุคคล&nbsp;'

.'</div>'.
'<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label><b>6.2 Attention and Concentation</b></label>&nbsp;<br>'.
'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- เอา 20 ลบทีละ3&nbsp;'.$attention1_1.'&nbsp;ทำได้&nbsp;'
.$attention1_2.'&nbsp;ทำไม่ได้&nbsp;'
.$attention1_3.'&nbsp;ทำได้บางส่วน&nbsp;'
.'</div>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label></label>&nbsp;<br>'.
'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- เอา 100 ลบทีละ7&nbsp;'.$attention2_1.'&nbsp;ทำได้&nbsp;'
.$attention2_2.'&nbsp;ทำไม่ได้&nbsp;'
.$attention2_3.'&nbsp;ทำได้บางส่วน&nbsp;'
.'</div>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label></label>&nbsp;<br>'.
'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- อ่านเลขแล้วให้พูดตาม พูดทวน (ปกติจะพูดตามได้ 6-7 หลัก พูดทวน 4-5 หลัก)&nbsp;'.$attention3_1.'&nbsp;ทำได้&nbsp;'
.$attention3_2.'&nbsp;ทำไม่ได้&nbsp;'
.$attention3_3.'&nbsp;ทำได้บางส่วน&nbsp;'
.'</div>'.


'<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label><b>6.3 Memory</b></label>&nbsp;<br>'.
'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- ความจำในช่วงเวลา เป็น นาที ชั่วโมง หรือ วัน (Recent memory)&nbsp;'.$memory1_1.'&nbsp;บอกถูก&nbsp;'
.$memory1_2.'&nbsp;บอกไม่ถูก&nbsp;'
.'</div>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label></label>&nbsp;<br>'.
'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- ความจำระยะสั้น (Recall memory) (พูดคำว่า ดอกไม้ เก้าอี้ รถไฟ แล้วคุยเรื่องอื่นนาน 5 นาที แล้วถามผู้ป่วย)&nbsp;'.$memory2_1.'&nbsp;บอกถูก&nbsp;'
.$memory2_2.'&nbsp;บอกไม่ได้&nbsp;'

.'</div>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label></label>&nbsp;<br>'.
'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>- ความจำในอดีต (Remote memory)</b> &nbsp;'.$memory3_1.'&nbsp;บอกถูก&nbsp;'
.$memory3_2.'&nbsp;บอกไม่ได้&nbsp;'
.'</div>'.

'<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label><b>6.4 General Knowledge ถามความรู้ทั่วไป เช่น สัปดาห์หนึ่งมีกี่วัน</b></label>&nbsp;'
.$general_khowledge_1.'&nbsp;บอกถูก&nbsp;'
.$general_khowledge_2.'&nbsp;บอกไม่ถูก&nbsp;'
.'</div>'.

'<br>'.

'</td>'.
'</tr>'

.'</table>'


.'<div style="font-size:8pt;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>6.5 Abtstract thinking</b></div>
<table width="100%" style="font-size:8pt;">
<tr>
    <td style="text-align:center;"><b>1.​ ถาม​ความ​แตก​ต่าง</b>​</td>
    <td style="text-align:center;"><b>concrete</b>​</td>
    <td style="text-align:center;"><b>Abstract</b>​</td>
</tr>
<tr>
    <td>กลาง​วัน​กับ​กลาง​คืน​</td>
    <td><div class="custom-control custom-checkbox col-sm-5">'
    .$concrete_difference1.'&nbsp;พระอาทิตย์กับพระจันทร์
</div></td>
    <td><div class="custom-control custom-checkbox col-sm-5">'
    .$abstract_difference1.'&nbsp;สว่างกับมืด
</div></td>
</tr>
<tr>
    <td>เด็กกับคนแคระ​</td>
    <td><div class="custom-control custom-checkbox col-sm-5">'
    .$concrete_difference2.'&nbsp;สูงไม่เท่ากัน
</div></td>
    <td><div class="custom-control custom-checkbox col-sm-5">'
    .$abstract_difference2.'&nbsp;เด็กกับผู้ใหญ่
</div></td>
</tr>

<tr>
    <td>ต้นมะเขือกับต้นโพธิ์​</td>
    <td><div class="custom-control custom-checkbox col-sm-5">'
    .$concrete_difference3.'&nbsp;ต้นเล็กกับต้นใหญ่
</div></td>
    <td><div class="custom-control custom-checkbox col-sm-5">'
    .$abstract_difference3.'&nbsp;ไม้ล้มลุกกับไม้ยืนต้น
</div></td>
</tr>
'
.'<tr>
<td style="text-align:center;"><b>2.​ ถามถึงความเหมือน</b>​</td>
<td style="text-align:center;"><b>concrete</b>​</td>
<td style="text-align:center;"><b>Abstract</b>​</td>
</tr>
<tr>
    <td>ส้มกับกล้วย​</td>
    <td><div class="custom-control custom-checkbox col-sm-5">'
    .$concrete_similarities1.'&nbsp;เปลือกสีเหมือนกัน
</div></td>
    <td><div class="custom-control custom-checkbox col-sm-5">'
    .$abstract_similarities1.'&nbsp;เป็นผลไม้เหมือนกัน
</div></td>
</tr>
<tr>
    <td>หนูกับแมว</td>
    <td><div class="custom-control custom-checkbox col-sm-5">'
    .$concrete_similarities2.'&nbsp;มีหนวด มีหาง เหมือนกัน
</div></td>
    <td><div class="custom-control custom-checkbox col-sm-5">'
    .$abstract_similarities2.'&nbsp;เป็นสัตว์เหมือนกัน
</div></td>
</tr>

<tr>
    <td>รถกับเรือ​</td>
    <td><div class="custom-control custom-checkbox col-sm-5">'
    .$concrete_similarities3.'&nbsp;วิ่งเหมือนกันใช้น้ำมัน
</div></td>
    <td><div class="custom-control custom-checkbox col-sm-5">'
    .$abstract_similarities3.'&nbsp;เป็นพาหนะเหมือนกัน
</div></td>
</tr>'

.'<tr>
<td style="text-align:center;"><b>3. ถามถึงคำพังเพย</b>​</td>
<td style="text-align:center;"><b>concrete</b>​</td>
<td style="text-align:center;"><b>Abstract</b>​</td>
</tr>
<tr>
    <td>น้ำขึ้นให้รีบตัก​</td>
    <td><div class="custom-control custom-checkbox col-sm-5">'
    .$concrete_aphorisms1.'&nbsp;น้ำลงจะตักลำบาก
</div></td>
    <td><div class="custom-control custom-checkbox col-sm-5">'
    .$abstract_aphorisms1.'&nbsp;เมื่อมีโอกาสให้รีบฉวย
</div></td>
</tr>
<tr>
    <td>หนีเสือปะจระเข้</td>
    <td><div class="custom-control custom-checkbox col-sm-5">'
    .$concrete_aphorisms2.'&nbsp;หนีเสือแล้วยังจะเจอสัตว์ร้ายอีก
</div></td>
    <td><div class="custom-control custom-checkbox col-sm-5">'
    .$abstract_aphorisms2.'&nbsp;หนีสิ่งเลวร้ายแล้วยังเจอสิ่งที่เลวร้ายกว่า
</div></td>
</tr>

<tr>
    <td>ขี่ช้างจับตั๊กแตน​</td>
    <td><div class="custom-control custom-checkbox col-sm-5">'
    .$concrete_aphorisms3.'&nbsp;ขี่ช้างสูงไปจับตั๊กแตนไม่ได้
</div></td>
    <td><div class="custom-control custom-checkbox col-sm-5">'
    .$abstract_aphorisms3.'&nbsp;ลงทุนเกินตัว
</div></td>
</tr>

</table>'

.'<table id="bg-table" width="100%" style="border-collapse: collapse;font-size:8pt;margin-top:2px;">'.


'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label><b>6.6 การตัดสินใจ (Judment)</b></label>&nbsp;'
.'</div>'.
'<div class="row">
<label>ถามทุกข้อดังนี้ พบซองจดหมายจ่าหน้าซองติดแสตมป์เรียบร้อยหล่นอยู่ข้างทาง</label>&nbsp;'
.$judment1_1.'&nbsp;เหมาะสม&nbsp;'
.$judment1_2.'&nbsp;ไม่เหมาะสม&nbsp;'
.'</div><br />'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;<label>เป็นคนแรกที่เห็นไฟไหม้ขณะดูภาพยนต์ในโรงภาพยนต์</label>&nbsp;'
.$judment2_1.'&nbsp;เหมาะสม&nbsp;'
.$judment2_2.'&nbsp;ไม่เหมาะสม&nbsp;'
.'</div><br />'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;<label>ออกจากบ้านใส่กุญแจแล้วนึกขึ้นได้ว่าลืมกุญแจทิ้งไว้ในบ้าน</label>&nbsp;'
.$judment3_1.'&nbsp;เหมาะสม&nbsp;'
.$judment3_2.'&nbsp;ไม่เหมาะสม&nbsp;'
.'</div>'.
'</td>'.
'</tr>'.



'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
' <div class="form-group row alert alert-dark text-left">
<B>7. ความตระหนักต่อการเจ็บป่วย (Insight)</B><br>
</div>'.

'<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label></label>&nbsp;'.
$insight_1.'&nbsp;ปฏิเสธการเจ็บป่วย&nbsp;'
.$insight_2.'&nbsp;พอจะทราบว่าตนเองผิดปกติ ปฏิเสธการรักษา&nbsp;'
.$insight_3.'&nbsp;ทราบว่าตนเองผิดปกติ แต่โทษว่าเกิดจากสิ่งอื่น<br /><br />'
.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$insight_4.'&nbsp;ทราบว่าตนเองผิดปกติจากปัญหาบางประการในตนเองแต่ไม่ทราบว่าปัญหาอะไร&nbsp;<br /><br />'
.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$insight_5.'&nbsp;ยอมรับว่าตนเองผิดปกติ แต่ไม่แก้ปัญหา&nbsp;<br /><br />'
.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$insight_6.'&nbsp;ยอมรับการเจ็บป่วยและยอมรับการรักษา&nbsp;'
.'</div>'.

'</td>'.
'</tr>'.


 '</table>'

.'</div>

    <table id="bg-table" width="100%" style="border-collapse: collapse;font-size:8pt;margin-top:2px;">
   
        <tr style="border:1px solid #000;margin: 35px;"> /* ชื่อ-สกุล */
            <td  colspan="3" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">
            <label>HN : '.htmlspecialchars($row_ipt['hn']).' | AN : '.htmlspecialchars($an).'</label>
            <label>ชื่อ - สกุล : '.htmlspecialchars($row_ipt['pname'].$row_ipt['fname']." ".$row_ipt['lname']).' | </label>
            <label>อายุ : '.htmlspecialchars($row_ipt['age_y']." ปี ".$row_ipt['age_m']." เดือน ".$row_ipt['age_d']." วัน ").' | </label>
            <label>ตึก : '.htmlspecialchars($row_ipt['name']).' | </label>
            <label>เตียง : '.htmlspecialchars($row_ipt['bedno']).' | </label>
            <label>สิทธิ : ('.htmlspecialchars($row_ipt['pttype']).') '.htmlspecialchars($row_ipt['pttype_name']).'</label>
            </td>
           
        </tr>
    </table>'
   

      .'<br>'.'<footter> <h2 style="text-align:right;font-size:8pt;">ประกาศใช้ 29 มกราคม 2566 งานเอกสารคุณภาพ ศูนย์คุณภาพ</h2> </footer>' ;
//$mpdf->SetColumns(2);

//แสดงข้อมุลอยู่ในช่วง ก่อน footer
$mpdf->setAutoTopMargin = 'stretch';
$mpdf->setAutoBottomMargin = 'stretch';

$mpdf->setFooter('HN: '.htmlspecialchars($hn).' AN: '.htmlspecialchars($an).' Page '.'{PAGENO}');
$mpdf->WriteHTML($head);
$mpdf->Output();
