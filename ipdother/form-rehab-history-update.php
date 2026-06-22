<?php
        //เวลาตาม timezone
        date_default_timezone_set("Asia/Bangkok");
        require_once '../include/Session.php';
        require_once '../include/session-sso.php';
Session::checkLoginSessionAndShowMessage(); //เช็ค session

        require_once '../include/DbUtils.php';
        require_once '../include/KphisQueryUtils.php';

        $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
       // $an = '660005698';
        $an = $_REQUEST['an'];
        $hn = KphisQueryUtils::getHnByAn($an);   // function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา
        $id = $_REQUEST['id'];

        $rxdate = empty($_REQUEST['rxdate']) ? null : $_REQUEST['rxdate'];
        $cc = empty($_REQUEST['cc']) ? null : $_REQUEST['cc'];
        $hpi =  empty($_REQUEST['hpi']) ? null : $_REQUEST['hpi'];
        $past_history=  empty($_REQUEST['past_history']) ? null : $_REQUEST['past_history'];
        $phychosocial=  empty($_REQUEST['phychosocial']) ? null : $_REQUEST['phychosocial'];
        $disease=  empty($_REQUEST['disease']) ? null : $_REQUEST['disease'];
        $treatment_received =  empty($_REQUEST['treatment_received']) ? null : $_REQUEST['treatment_received'];
        $pe_1st =  empty($_REQUEST['pe_1st']) ? null : $_REQUEST['pe_1st'];
        $pe_1st_date =  empty($_REQUEST['pe_1st_date']) ? null : $_REQUEST['pe_1st_date'];
        $diagnosis =  empty($_REQUEST['diagnosis']) ? null : $_REQUEST['diagnosis'];
        $goal_date = empty($_REQUEST['goal_date']) ? null : $_REQUEST['goal_date'];
        $goal = empty($_REQUEST['goal']) ? null : $_REQUEST['goal'];
        $due_date = empty($_REQUEST['due_date']) ? null : $_REQUEST['due_date'];
        $treatment_plan = empty($_REQUEST['treatment_plan']) ? null : $_REQUEST['treatment_plan'];
        $summary_date = empty($_REQUEST['summary_date']) ? null : $_REQUEST['summary_date'];
        $summary_of_dc = empty($_REQUEST['summary_of_dc']) ? null : $_REQUEST['summary_of_dc'];
             
        $update_datetime= date('Y-m-d H:i:s');
        $update_user = $_SESSION['loginname'];
        $version0 = $_REQUEST['version'];
        $version = $version0 + 1;

        try {
          //เรียกใช้งาน sql update
if ( $rxdate != '' 
) {
        $output_error = '<script>
        NotificationMessage("บันทึกข้อมูลสำเร็จ", "success");     
        </script>';
$stmt = $conn->prepare("UPDATE ".DbConstant::KPHIS_DBNAME.".prs_rehab_history SET hn=:hn,an=:an,rxdate=:rxdate
          ,cc=:cc,hpi=:hpi,past_history=:past_history,phychosocial=:phychosocial,disease=:disease
          ,treatment_received=:treatment_received,pe_1st=:pe_1st,pe_1st_date=:pe_1st_date,diagnosis=:diagnosis,goal_date=:goal_date 
          ,goal=:goal,due_date=:due_date,treatment_plan=:treatment_plan,summary_date=:summary_date,summary_of_dc=:summary_of_dc
          ,update_user=:update_user,version=:version,update_datetime=:update_datetime
          WHERE id=:id");

          //execute array
          $stmt->execute(array('id'=>$id,'hn'=>$hn,'an'=>$an,'rxdate'=>$rxdate
          ,'cc'=>$cc,'hpi'=>$hpi,'past_history'=>$past_history,'phychosocial'=>$phychosocial,'disease'=>$disease
          ,'treatment_received'=>$treatment_received,'pe_1st'=>$pe_1st,'pe_1st_date'=>$pe_1st_date,'diagnosis'=>$diagnosis,'goal_date'=>$goal_date 
          ,'goal'=>$goal,'due_date'=>$due_date,'treatment_plan'=>$treatment_plan,'summary_date'=>$summary_date,'summary_of_dc'=>$summary_of_dc
          ,'update_user'=>$update_user,'version'=>$version,'update_datetime' => $update_datetime
          ));

            Session::insertSystemAccessLog(json_encode(array(
                'form'=>'REHAB-HISTORY-FORM',
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
        
