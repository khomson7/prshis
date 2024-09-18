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

        $somatic_concern= empty($_REQUEST['somatic_concern']) ? null : $_REQUEST['somatic_concern'];
        $anxiety = empty($_REQUEST['anxiety']) ? null : $_REQUEST['anxiety'];
        $emotional = empty($_REQUEST['emotional']) ? null : $_REQUEST['emotional'];
        $conceptual = empty($_REQUEST['conceptual']) ? null : $_REQUEST['conceptual'];
        $guilt = empty($_REQUEST['guilt']) ? null : $_REQUEST['guilt'];
        $tension = empty($_REQUEST['tension']) ? null : $_REQUEST['tension'];
        $mannerism = empty($_REQUEST['mannerism']) ? null : $_REQUEST['mannerism'];
        $depressive = empty($_REQUEST['depressive']) ? null : $_REQUEST['depressive'];
        $grandiosity = empty($_REQUEST['grandiosity']) ? null : $_REQUEST['grandiosity'];
        $hostility = empty($_REQUEST['hostility']) ? null : $_REQUEST['hostility'];
        $suspiciousness = empty($_REQUEST['suspiciousness']) ? null : $_REQUEST['suspiciousness'];
        $hallucinatory = empty($_REQUEST['hallucinatory']) ? null : $_REQUEST['hallucinatory'];
        $motor = empty($_REQUEST['motor']) ? null : $_REQUEST['motor'];
        $uncooperativeness = empty($_REQUEST['uncooperativeness']) ? null : $_REQUEST['uncooperativeness'];
        $unusual = empty($_REQUEST['unusual']) ? null : $_REQUEST['unusual'];
        $blunted = empty($_REQUEST['blunted']) ? null : $_REQUEST['blunted'];
        $excitement = empty($_REQUEST['excitement']) ? null : $_REQUEST['excitement'];
        $disorientation = empty($_REQUEST['disorientation']) ? null : $_REQUEST['disorientation'];

        $total_sum = $somatic_concern + $anxiety + $emotional + $conceptual + $guilt + $tension + $mannerism 
        + $depressive + $grandiosity + $hostility +$suspiciousness + $hallucinatory + $motor + $uncooperativeness
        + $unusual + $blunted + $excitement + $disorientation ;

               $date1 = $_REQUEST['create_datetime'];
        if ($total_sum >= 1 && $total_sum <= 36) {
                $bg_color = 'green';
                $message = 'แนะนำประเมินต่อทุก 1 สัปดาห์';                
                $date_alert = date('Y-m-d',strtotime($date1 . "+7 days"));

            } elseif ($total_sum >= 37 && $total_sum <= 40) {
                $bg_color = 'orange';
                $message = 'แนะนำประเมินต่อทุก 2 วัน';
                $date_alert = date('Y-m-d',strtotime($date1 . "+2 days"));
            } elseif ($total_sum > 40) {
                $bg_color = 'red';
                $message = 'แนะนำประเมินวันละ 1 ครั้ง';
                $date_alert = date('Y-m-d',strtotime($date1 . "+1 days"));
            } else {
                $bg_color = ''; // default if the value is outside the range
            }
                 
        //$family_history = 'aaa';    
        $update_datetime= date('Y-m-d H:i:s');
        $create_user = $_SESSION['loginname'];
        $update_user = $_SESSION['loginname'];
        $version0 = $_REQUEST['version'];
        $version = $version0 + 1;

        try {
          //เรียกใช้งาน sql update
if ( $somatic_concern != ''
) {
        $output_error = '<script>
        NotificationMessage("บันทึกข้อมูลสำเร็จ", "success");     
        </script>';
$stmt = $conn->prepare("UPDATE ".DbConstant::KPHIS_DBNAME.".prs_mental_health2 SET an=:an,hn=:hn,somatic_concern=:somatic_concern
          ,anxiety=:anxiety,emotional=:emotional,conceptual=:conceptual,guilt=:guilt,tension=:tension,mannerism=:mannerism
          ,depressive=:depressive,grandiosity=:grandiosity,hostility=:hostility,suspiciousness=:suspiciousness,hallucinatory=:hallucinatory
          ,motor=:motor,uncooperativeness=:uncooperativeness,unusual=:unusual,blunted=:blunted,excitement=:excitement,disorientation=:disorientation
          ,total_sum=:total_sum,date_alert=:date_alert
          ,update_user=:update_user,version=:version,update_datetime=:update_datetime
          WHERE id=:id");
          //execute array
          $stmt->execute(array('id'=>$id,'an'=>$an,'hn'=>$hn,'somatic_concern'=>$somatic_concern
          ,'anxiety'=>$anxiety,'emotional'=>$emotional,'conceptual'=>$conceptual,'guilt'=>$guilt,'tension'=>$tension,'mannerism'=>$mannerism
          ,'depressive'=>$depressive,'grandiosity'=>$grandiosity,'hostility'=>$hostility,'suspiciousness'=>$suspiciousness,'hallucinatory'=>$hallucinatory
          ,'motor'=>$motor,'uncooperativeness'=>$uncooperativeness,'unusual'=>$unusual,'blunted'=>$blunted,'excitement'=>$excitement,'disorientation'=>$disorientation
          ,'total_sum'=>$total_sum,'date_alert'=>$date_alert
          ,'update_user'=>$update_user,'version'=>$version,'update_datetime' => $update_datetime
          ));


            Session::insertSystemAccessLog(json_encode(array(
                'form'=>'MENTAL-HEALTH2-FORM',
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
        