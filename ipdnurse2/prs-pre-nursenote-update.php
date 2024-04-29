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
        $rxtime = empty($_REQUEST['rxtime']) ? null : $_REQUEST['rxtime'];
        $refer_from =  empty($_REQUEST['refer_from']) ? null : $_REQUEST['refer_from'];
        $depart =  empty($_REQUEST['depart']) ? null : $_REQUEST['depart'];
        $hospital_by =  empty($_REQUEST['hospital_by']) ? null : $_REQUEST['hospital_by'];
        $cc =  empty($_REQUEST['cc']) ? null : $_REQUEST['cc'];
        $hpi =  empty($_REQUEST['hpi']) ? null : $_REQUEST['hpi'];
        $current_illness =  empty($_REQUEST['current_illness']) ? null : $_REQUEST['current_illness'];
        $c_chronic =  empty($_REQUEST['c_chronic']) ? null : $_REQUEST['c_chronic'];
        $hos_history =  empty($_REQUEST['hos_history']) ? null : $_REQUEST['hos_history'];
        $h_sergery =  empty($_REQUEST['h_sergery']) ? null : $_REQUEST['h_sergery'];
        $h_allergy =  empty($_REQUEST['h_allergy']) ? null : $_REQUEST['h_allergy'];
        $vaccine_history =  empty($_REQUEST['vaccine_history']) ? null : $_REQUEST['vaccine_history'];
        $child_devilopment =  empty($_REQUEST['child_devilopment']) ? null : $_REQUEST['child_devilopment'];
        $history_of_drug =  empty($_REQUEST['history_of_drug']) ? null : $_REQUEST['history_of_drug'];
        $state_of_mind =  empty($_REQUEST['state_of_mind']) ? null : $_REQUEST['state_of_mind'];
        $first_symptoms =  empty($_REQUEST['first_symptoms']) ? null : $_REQUEST['first_symptoms'];
        $pmh_2 =  empty($_REQUEST['pmh2']) ? null : $_REQUEST['pmh2'];
        $hn =  empty($_REQUEST['hn']) ? null : $_REQUEST['hn'];
        $bt =  empty($_REQUEST['bt']) ? null : $_REQUEST['bt'];
        $pr =  empty($_REQUEST['pr']) ? null : $_REQUEST['pr'];
        $rr =  empty($_REQUEST['rr']) ? null : $_REQUEST['rr'];
        $bps =  empty($_REQUEST['bps']) ? null : $_REQUEST['bps'];
        $bpd =  empty($_REQUEST['bpd']) ? null : $_REQUEST['bpd'];
        $level_of_con=  empty($_REQUEST['level_of_con']) ? null : $_REQUEST['level_of_con'];
        $breathing=  empty($_REQUEST['breathing']) ? null : $_REQUEST['breathing'];
        $blood_circulation=  empty($_REQUEST['blood_circulation']) ? null : $_REQUEST['blood_circulation'];
        $swelling=  empty($_REQUEST['swelling']) ? null : $_REQUEST['swelling'];
        $skin=  empty($_REQUEST['skin']) ? null : $_REQUEST['skin'];
        $communication_ears = empty($_REQUEST['communication_ears']) ? null : $_REQUEST['communication_ears'];
        $hearing_aid = empty($_REQUEST['hearing_aid']) ? null : $_REQUEST['hearing_aid'];
        $communication_eyes = empty($_REQUEST['communication_eyes']) ? null : $_REQUEST['communication_eyes'];
        $glasses = empty($_REQUEST['glasses']) ? null : $_REQUEST['glasses'];
        $communication_speak = empty($_REQUEST['communication_speak']) ? null : $_REQUEST['communication_speak'];
                         
        //$family_history = 'aaa';
        
        
        $update_datetime= date('Y-m-d H:i:s');
        $create_user = $_SESSION['loginname'];
        $update_user = $_SESSION['loginname'];
        $version0 = $_REQUEST['version'];
        $version = $version0 + 1;

        try {
          //เรียกใช้งาน sql update
if ( $rxdate != '' && $rxtime !='' && $hospital_by !='' && $h_allergy !='' && $c_chronic !='' && $vaccine_history !=''  && $child_devilopment !='' && $pmh_2 !='' 
&& $history_of_drug != '' && $bt != '' && $pr != '' && $rr != '' && $bps != '' && $bpd != '' && $breathing != '' && $blood_circulation != '' && $swelling != ''
&& $skin != '' && $communication_ears != '' && $communication_eyes != '' && $communication_speak != ''
) {
        $output_error = '<script>
        NotificationMessage("บันทึกข้อมูลสำเร็จ", "success");     
        </script>';
$stmt = $conn->prepare("UPDATE ".DbConstant::KPHIS_DBNAME.".prs_pre_nursenote SET hn=:hn,an=:an,rxdate=:rxdate,rxtime=:rxtime,refer_from=:refer_from
          ,hospital_by=:hospital_by,depart=:depart,cc=:cc,hpi=:hpi,current_illness=:current_illness
          ,c_chronic=:c_chronic,hos_history=:hos_history,h_sergery=:h_sergery,h_allergy=:h_allergy
          ,vaccine_history=:vaccine_history,child_devilopment=:child_devilopment,history_of_drug=:history_of_drug
          ,bt=:bt,pr=:pr,rr=:rr,bps=:bps,bpd=:bpd,level_of_con=:level_of_con,breathing=:breathing,blood_circulation=:blood_circulation
          ,swelling=:swelling,skin=:skin,communication_ears=:communication_ears,hearing_aid=:hearing_aid
          ,communication_eyes=:communication_eyes,glasses=:glasses,communication_speak=:communication_speak
          ,pmh2=:pmh2,state_of_mind=:state_of_mind,first_symptoms=:first_symptoms
          ,update_user=:update_user,version=:version,update_datetime=:update_datetime
          WHERE id=:id");
          //execute array
          $stmt->execute(array('id'=>$id,'hn'=>$hn,'an'=>$an,'rxdate'=>$rxdate, 'rxtime'=>$rxtime,'refer_from'=>$refer_from
          ,'hospital_by'=>$hospital_by,'depart'=>$depart,'cc'=>$cc,'hpi'=>$hpi,'current_illness'=>$current_illness
          ,'c_chronic'=>$c_chronic,'hos_history'=>$hos_history,'h_sergery'=>$h_sergery,'h_allergy'=>$h_allergy,'history_of_drug'=>$history_of_drug
          ,'bt'=>$bt,'pr'=>$pr,'rr'=>$rr,'bps'=>$bps,'bpd'=>$bpd,'level_of_con'=>$level_of_con,'breathing'=>$breathing,'blood_circulation'=>$blood_circulation
          ,'swelling'=>$swelling,'skin'=>$skin,'communication_ears'=>$communication_ears,'hearing_aid'=>$hearing_aid
          ,'communication_eyes'=>$communication_eyes,'glasses'=>$glasses,'communication_speak'=>$communication_speak
          ,'vaccine_history'=>$vaccine_history,'child_devilopment'=>$child_devilopment
          ,'pmh2'=>$pmh_2,'state_of_mind'=>$state_of_mind,'first_symptoms'=>$first_symptoms
          ,'update_user'=>$update_user,'version'=>$version,'update_datetime' => $update_datetime
          ));


          if(!empty($_REQUEST['doctor'])){
                $stmt_item_check = "SELECT COUNT(*) AS count_item,max(id) AS count_id FROM ".DbConstant::KPHIS_DBNAME.".prs_pre_nursenote_item WHERE an=:an";
                $stmt_item_check = $conn->prepare($stmt_item_check);
                $stmt_item_check->execute(['an'=>$an]);
                $row_item_check = $stmt_item_check->fetch();
                $count_item = $row_item_check['count_item'];
                $count_id = $row_item_check['count_id'];
                if($count_item > 0){

                    $stmt_item_delete = "DELETE FROM ".DbConstant::KPHIS_DBNAME.".prs_pre_nursenote_item WHERE an=:an";
                    $stmt_item_delete = $conn->prepare($stmt_item_delete);
                    $stmt_item_delete->execute(['an'=>$an]);

                }


                foreach($_REQUEST['doctor'] as $doctor){
                        $stmt_item = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".prs_pre_nursenote_item(pre_nursenote_id,an,doctor,create_user,create_datetime,update_user,update_datetime,version)
                        VALUES (:pre_nursenote_id,:an,:doctor,:create_user,now(),:update_user,now(),:version)");
                        $stmt_item->execute(array('pre_nursenote_id'=>$id,'an'=>$an,'doctor'=>$doctor
                        ,'create_user'=>$create_user, 'update_user'=>$update_user, 'version'=>$version
                        ));
                    } 

              /* foreach($_REQUEST['admission_note_doctor'] as $admission_note_doctor){
                    $stmt_item = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".ipd_dr_admission_note_item
                    (admission_note_id,an,admission_note_doctor,create_user,create_datetime,update_user,update_datetime,version)
                    VALUES (:admission_note_id,:an,:admission_note_doctor,:create_user,now(),:update_user,now(),:version)");
                    $stmt_item->execute(array('admission_note_id'=>$admission_note_id, 'an'=>$an,
                    'admission_note_doctor'=>$admission_note_doctor,
                    'create_user'=>$create_user, 'update_user'=>$update_user, 'version'=>$version));
                }
                */
                
            }


} if ( $communication_eyes == 'เห็นไม่ชัดเจน' && $glasses == ''
) {
  
        echo   '<script>
        alert("กรุณากรอกข้อมูลให้ครบถ้วน", "error");     
        </script>'; 

}else {

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
            //เมื่อ update สำเร็จ
         // $output_error = '<div class="alert alert-success">บันทึกข้อมูลเรียบร้อยแล้วคะ</div>';
        

        }catch (PDOException  $e) {
//เมื่อเกิดข้อผิดพลาด
echo $e->getMessage();
//$output_error = '<div class="alert alert-danger">ERROR !!</div>';

$output_error = '<script>NotificationMessage("บันทึกข้อมูลไม่สำเร็จ", "error")</script>';

        }

        echo $output_error;


        ?>
        