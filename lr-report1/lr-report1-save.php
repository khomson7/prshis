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
/*
    if(empty($an)

    ){
        exit;
    }
*/
   // echo $an;

    $receive_date = /*empty($_REQUEST['receive_date']) ? null :*/ $_REQUEST['receive_date'];
    $receive_time = /*empty($_REQUEST['receive_time']) ? null :*/ $_REQUEST['receive_time'];
    $receive_from = /*empty($_REQUEST['receive_from']) ? null :*/ $_REQUEST['receive_from'];
    $transport = /*empty($_REQUEST['transport']) ? null :*/ $_REQUEST['transport'];
    $cc = /*empty($_REQUEST['cc']) ? null :*/ $_REQUEST['cc'];
    $ga = $_REQUEST['ga'];
    $labor = $_REQUEST['labor'];
    $indication= $_REQUEST['indication'];
    $labor_date = $_REQUEST['labor_date'];
    $labor_time = $_REQUEST['labor_time'];
    $sex = $_REQUEST['sex'];
    $weight = $_REQUEST['weight'];
    $apgar_score_1 = $_REQUEST['apgar_score_1'];
    $subtract_1 = $_REQUEST['subtract_1'];
    $apgar_score_5 = $_REQUEST['apgar_score_5'];
    $subtract_5 = $_REQUEST['subtract_5'];
    $apgar_score_10 = $_REQUEST['apgar_score_10'];
    $subtract_10 = $_REQUEST['subtract_10'];
    $abnormal = $_REQUEST['abnormal'];
    $g = $_REQUEST['g'];
    $p = $_REQUEST['p'];
    $serology = $_REQUEST['serology'];
    $antepartum = $_REQUEST['antepartum'];
    $dt_vaccine = $_REQUEST['dt_vaccine'];
    $family = $_REQUEST['family'];
    $bt = $_REQUEST['bt'];
    $hr = $_REQUEST['hr'];
    $rr = $_REQUEST['rr'];
    $ofs = $_REQUEST['ofs'];
    $om = $_REQUEST['om'];
    $chest = $_REQUEST['chest'];
    $body_long = $_REQUEST['body_long'];
    $cord = $_REQUEST['cord'];
    $anus = $_REQUEST['anus'];
    $body = $_REQUEST['body'];
    $cry = $_REQUEST['cry'];
    $movement = $_REQUEST['movement'];
    $head = $_REQUEST['head'];
    $eyes = $_REQUEST['eyes'];
    $nose = $_REQUEST['nose'];
    $mouth = $_REQUEST['mouth'];
    $neck = $_REQUEST['neck'];
    $abdomen = $_REQUEST['abdomen'];
    $navel = $_REQUEST['navel'];
    $spine = $_REQUEST['spine'];
    $limbs = $_REQUEST['limbs'];
    $genitalia = $_REQUEST['genitalia'];
    $anuss = $_REQUEST['anuss'];
    $skin_color = $_REQUEST['skin_color'];
    $behavior = $_REQUEST['behavior'];
    $expression = $_REQUEST['expression'];
    $first_symptom= $_REQUEST['first_symptom'];
   

    //$create_datetime = ใช้ NOW()
    $create_datetime =  date('Y-m-d H:i:s');
    $create_user  = $_SESSION['loginname'];
    //$update_datetime = ใช้ NOW()
   // $update_datetime  = $datenow = date('Y-m-d H:i:s');
    $update_user  = $_SESSION['loginname'];

    $version = 1;

    try {
//บันทึกรายการ
        $stmt = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".prs_labor_report1(receive_date,receive_time,receive_from
       ,transport,cc,ga,labor,indication,labor_date,labor_time,sex,weight,apgar_score_1,subtract_1
       ,apgar_score_5,subtract_5,apgar_score_10,subtract_10,abnormal,g,p,serology,antepartum,dt_vaccine,family
       ,bt,hr,rr,ofs,om,chest,body_long,cord,anus,body,cry,movement,head,eyes,nose,mouth,neck,abdomen,navel,spine,limbs 
       ,genitalia,anuss,skin_color,behavior,expression,first_symptom
        ,an,create_user,update_user,version,create_datetime,update_datetime)
        VALUES(:receive_date,:receive_time,:receive_from
        ,:transport,:cc,:ga,:labor,:indication,:labor_date,:labor_time,:sex,:weight,:apgar_score_1,:subtract_1
        ,:apgar_score_5,:subtract_5,:apgar_score_10,:subtract_10,:abnormal,:g,:p,:serology,:antepartum,:dt_vaccine,:family
        ,:bt,:hr,:rr,:ofs,:om,:chest,:body_long,:cord,:anus,:body,:cry,:movement,:head,:eyes,:nose,:mouth,:neck,:abdomen,:navel,:spine,:limbs
        ,:genitalia,:anuss,:skin_color,:behavior,:expression,:first_symptom
        ,:an,:create_user,:update_user,:version,:create_datetime,:update_datetime)");

        $stmt->execute(array('receive_date'=>$receive_date,'receive_time'=>$receive_time,'receive_from'=>$receive_from
        ,'transport'=>$transport,'cc'=>$cc,'ga'=>$ga,'labor'=>$labor,'indication'=>$indication,'labor_date'=>$labor_date,'labor_time'=>$labor_time
        ,'sex'=>$sex,'weight'=>$weight,'apgar_score_1'=>$apgar_score_1,'subtract_1'=>$subtract_1,'apgar_score_5'=>$apgar_score_5,'subtract_5'=>$subtract_5,'apgar_score_10'=>$apgar_score_10,'subtract_10'=>$subtract_10
      ,'abnormal'=>$abnormal,'g'=>$g,'p'=>$p,'serology'=>$serology,'antepartum'=>$antepartum,'dt_vaccine'=>$dt_vaccine,'family'=>$family
      ,'bt'=>$bt,'hr'=>$hr,'rr'=>$rr,'ofs'=>$ofs,'om'=>$om,'chest'=>$chest,'body_long'=>$body_long,'cord'=>$cord,'anus'=>$anus,'body'=>$body,'cry'=>$cry,'movement'=>$movement
      ,'head'=>$head,'eyes'=>$eyes,'nose'=>$nose,'mouth'=>$mouth,'neck'=>$neck,'abdomen'=>$abdomen,'navel'=>$navel,'spine'=>$spine,'limbs'=>$limbs
      ,'genitalia'=>$genitalia,'anuss'=>$anuss,'skin_color'=>$skin_color,'behavior'=>$behavior,'expression'=>$expression,'first_symptom'=>$first_symptom
        ,'an'=>$an,'create_user'=>$create_user, 'update_user'=>$update_user, 'version'=>$version , 'create_datetime' =>$create_datetime , 'update_datetime' =>$create_datetime));

        $output_error = '<div class="alert alert-success">บันทึกข้อมูลสำเร็จ</div>';
    }catch (PDOException  $e) {
        echo $e->getMessage();
        $output_error = '<div class="alert alert-danger">ERROR !!</div>';
    }
    echo $output_error;
?>
