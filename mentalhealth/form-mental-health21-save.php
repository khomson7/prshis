<?php
    require_once '../include/DbUtils.php';
    require_once '../include/KphisQueryUtils.php';
    require_once '../include/Session.php';
    //เวลาตาม timezone
    date_default_timezone_set("Asia/Bangkok");

    $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล


    $an = $_REQUEST['an'];//รับค่า an
    $hn = KphisQueryUtils::getHnByAn($an);// function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา

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

        $date1 =  date('Y-m-d');
        if ($total_sum >= 1 && $total_sum <= 36) {

          $date_alert = date('Y-m-d',strtotime($date1 . "+7 days"));
          //echo $date_alert;
    }elseif ($total_sum >= 37 && $total_sum <= 40) {
        
          $date_alert = date('Y-m-d',strtotime($date1 . "+2 days"));
    }elseif ($total_sum > 40) {
        
        $date_alert = date('Y-m-d',strtotime($date1 . "+1 days"));
    } 
      

    $create_datetime =  date('Y-m-d H:i:s');
    $create_user  = $_SESSION['loginname'];
    $update_user  = $_SESSION['loginname'];
    $update_datetime =  date('Y-m-d H:i:s');

    $version = 1;

    try {

        if ( $somatic_concern != '' 
) {

            $stmt = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".prs_mental_health2(an,hn,somatic_concern
            ,anxiety,emotional,conceptual,guilt,tension,mannerism,depressive,grandiosity,hostility,suspiciousness,hallucinatory
          ,motor,uncooperativeness,unusual,blunted,excitement,disorientation,total_sum,date_alert
            ,create_user,create_datetime,update_user,version,update_datetime)
            VALUES(	:an,:hn,:somatic_concern
            ,:anxiety,:emotional,:conceptual,:guilt,:tension,:mannerism,:depressive,:grandiosity,:hostility,:suspiciousness,:hallucinatory
            ,:motor,:uncooperativeness,:unusual,:blunted,:excitement,:disorientation,:total_sum,:date_alert
            ,:create_user,:create_datetime,:update_user,:version,:update_datetime)");
    
            $stmt->execute(array('an'=>$an,'hn'=>$hn,'somatic_concern'=>$somatic_concern
            ,'anxiety'=>$anxiety,'emotional'=>$emotional,'conceptual'=>$conceptual,'guilt'=>$guilt,'tension'=>$tension,'mannerism'=>$mannerism
          ,'depressive'=>$depressive,'grandiosity'=>$grandiosity,'hostility'=>$hostility,'suspiciousness'=>$suspiciousness,'hallucinatory'=>$hallucinatory
          ,'motor'=>$motor,'uncooperativeness'=>$uncooperativeness,'unusual'=>$unusual,'blunted'=>$blunted,'excitement'=>$excitement,'disorientation'=>$disorientation
            ,'total_sum'=>$total_sum,'date_alert'=>$date_alert
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
