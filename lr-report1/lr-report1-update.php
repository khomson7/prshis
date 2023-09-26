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

        $receive_date = empty($_REQUEST['receive_date']) ? null : $_REQUEST['receive_date'];
        $receive_time= empty($_REQUEST['receive_time']) ? null : $_REQUEST['receive_time'];
        $receive_from = empty($_REQUEST['receive_from']) ? null : $_REQUEST['receive_from'];
        $transport = empty($_REQUEST['transport']) ? null : $_REQUEST['transport'];
        $cc = empty($_REQUEST['cc']) ? null : $_REQUEST['cc'];
        $ga = empty($_REQUEST['ga']) ? null : $_REQUEST['ga'];
        $labor = empty($_REQUEST['labor']) ? null : $_REQUEST['labor'];
        $indication = empty($_REQUEST['indication']) ? null : $_REQUEST['indication'];
        $labor_date = empty($_REQUEST['labor_date']) ? null : $_REQUEST['labor_date'];
        $labor_time= empty($_REQUEST['labor_time']) ? null : $_REQUEST['labor_time'];
        $sex = empty($_REQUEST['sex']) ? null : $_REQUEST['sex'];
//$sex = '1';
        $weight =  empty($_REQUEST['weight']) ? null : $_REQUEST['weight'];
        $apgar_score_1 =  empty($_REQUEST['apgar_score_1']) ? null : $_REQUEST['apgar_score_1'];
        $subtract_1 =  empty($_REQUEST['subtract_1']) ? null : $_REQUEST['subtract_1'];
        $apgar_score_5=  empty($_REQUEST['apgar_score_5']) ? null : $_REQUEST['apgar_score_5'];
        $subtract_5 =  empty($_REQUEST['subtract_5']) ? null : $_REQUEST['subtract_5'];
        $apgar_score_10 =  empty($_REQUEST['apgar_score_10']) ? null : $_REQUEST['apgar_score_10'];
        $subtract_10 =  empty($_REQUEST['subtract_10']) ? null : $_REQUEST['subtract_10'];
        $abnormal =  empty($_REQUEST['abnormal']) ? null : $_REQUEST['abnormal'];
        $g =  empty($_REQUEST['g']) ? null : $_REQUEST['g'];
        $p =  empty($_REQUEST['p']) ? null : $_REQUEST['p'];
        $serology =  empty($_REQUEST['serology']) ? null : $_REQUEST['serology'];
        $antepartum =  empty($_REQUEST['antepartum']) ? null : $_REQUEST['antepartum'];
        $dt_vaccine =  empty($_REQUEST['dt_vaccine']) ? null : $_REQUEST['dt_vaccine'];
        $family =  $_REQUEST['family'];
        $bt =  empty($_REQUEST['bt']) ? null : $_REQUEST['bt'];
        $hr =  empty($_REQUEST['hr']) ? null : $_REQUEST['hr'];
        $rr =  empty($_REQUEST['rr']) ? null : $_REQUEST['rr'];
        $ofs =  empty($_REQUEST['ofs']) ? null : $_REQUEST['ofs'];
        $om =  empty($_REQUEST['om']) ? null : $_REQUEST['om'];
        $chest =  empty($_REQUEST['chest']) ? null : $_REQUEST['chest'];
        $body_long =  empty($_REQUEST['body_long']) ? null : $_REQUEST['body_long'];
        $cord =  empty($_REQUEST['cord']) ? null : $_REQUEST['cord'];
        $anus =  empty($_REQUEST['anus']) ? null : $_REQUEST['anus'];
        $body =  empty($_REQUEST['body']) ? null : $_REQUEST['body'];
      // $body = 'ปกติ';
        $cry =  empty($_REQUEST['cry']) ? null : $_REQUEST['cry'];
        $movement =  empty($_REQUEST['movement']) ? null : $_REQUEST['movement'];
        $head =  empty($_REQUEST['head']) ? null : $_REQUEST['head'];
        $eyes =  empty($_REQUEST['eyes']) ? null : $_REQUEST['eyes'];
        $nose =  empty($_REQUEST['nose']) ? null : $_REQUEST['nose'];
        $mouth =  empty($_REQUEST['mouth']) ? null : $_REQUEST['mouth'];
        $neck =  empty($_REQUEST['neck']) ? null : $_REQUEST['neck'];
        $abdomen =  empty($_REQUEST['abdomen']) ? null : $_REQUEST['abdomen'];
        $navel =  empty($_REQUEST['navel']) ? null : $_REQUEST['navel'];
        $spine =  empty($_REQUEST['spine']) ? null : $_REQUEST['spine'];
        $limbs =  empty($_REQUEST['limbs']) ? null : $_REQUEST['limbs'];
        $genitalia =  empty($_REQUEST['genitalia']) ? null : $_REQUEST['genitalia'];
        $anuss =  empty($_REQUEST['anuss']) ? null : $_REQUEST['anuss'];
        $skin_color =  empty($_REQUEST['skin_color']) ? null : $_REQUEST['skin_color'];
        $behavior =  empty($_REQUEST['behavior']) ? null : $_REQUEST['behavior'];
        $expression=  $_REQUEST['expression'];
        $first_symptom=  empty($_REQUEST['first_symptom']) ? null : $_REQUEST['first_symptom'];
        $hn =  empty($_REQUEST['hn']) ? null : $_REQUEST['hn'];
        

        //$family_history = 'aaa';
        
        
        $update_datetime= date('Y-m-d H:i:s');
        $update_user = $_SESSION['loginname'];
        $version0 = $_REQUEST['version'];
        $version = $version0 + 1;

        try {
          //เรียกใช้งาน sql update
          $stmt = $conn->prepare("UPDATE ".DbConstant::KPHIS_DBNAME.".prs_labor_report1 SET an=:an,receive_date=:receive_date,receive_time=:receive_time,receive_from=:receive_from
          ,transport=:transport,cc=:cc,ga=:ga,labor=:labor,indication=:indication,labor_date=:labor_date,labor_time=:labor_time,sex=:sex,weight=:weight
          ,apgar_score_1=:apgar_score_1,subtract_1=:subtract_1,apgar_score_5=:apgar_score_5,subtract_5=:subtract_5,apgar_score_10=:apgar_score_10,subtract_10=:subtract_10
          ,abnormal=:abnormal,g=:g,p=:p,serology=:serology,antepartum=:antepartum,dt_vaccine=:dt_vaccine,family=:family,bt=:bt,hr=:hr,rr=:rr,ofs=:ofs,om=:om
          ,chest=:chest,body_long=:body_long,cord=:cord,anus=:anus,body=:body,cry=:cry,expression=:expression,movement=:movement,head=:head,eyes=:eyes,nose=:nose
          ,mouth=:mouth,neck=:neck,abdomen=:abdomen,navel=:navel,spine=:spine,limbs=:limbs,genitalia=:genitalia,anuss=:anuss
          ,skin_color=:skin_color,behavior=:behavior
          ,update_user=:update_user,version=:version,update_datetime=:update_datetime,check_value=:check_value
          WHERE id=:id");
          //execute array
          $stmt->execute(array('id'=>$id,'an'=>$an,'receive_date'=>$receive_date, 'receive_time'=>$receive_time,'receive_from'=>$receive_from
          ,'transport'=>$transport,'cc'=>$cc,'ga'=>$ga,'labor'=>$labor,'indication'=>$indication,'labor_date'=>$labor_date,'labor_time'=>$labor_time,'sex'=>$sex,'weight'=>$weight
          ,'apgar_score_1'=>$apgar_score_1,'subtract_1'=>$subtract_1,'apgar_score_5'=>$apgar_score_5,'subtract_5'=>$subtract_5,'apgar_score_10'=>$apgar_score_10,'subtract_10'=>$subtract_10
          ,'abnormal'=>$abnormal,'g'=>$g,'p'=>$p,'serology'=>$serology,'antepartum'=>$antepartum,'dt_vaccine'=>$dt_vaccine,'family'=>$family,'bt'=>$bt,'hr'=>$hr,'rr'=>$rr
          ,'ofs'=>$ofs,'om'=>$om,'chest'=>$chest,'body_long'=>$body_long,'cord'=>$cord,'anus'=>$anus,'body'=>$body,'cry'=>$cry,'expression'=>$expression,'movement'=>$movement
          ,'head'=>$head,'eyes'=>$eyes,'nose'=>$nose,'mouth'=>$mouth,'neck'=>$neck,'abdomen'=>$abdomen,'navel'=>$navel,'spine'=>$spine,'limbs'=>$limbs,'genitalia'=>$genitalia,'anuss'=>$anuss
          ,'skin_color'=>$skin_color,'behavior'=>$behavior
          ,'update_user'=>$update_user,'version'=>$version,'update_datetime' => $update_datetime,'check_value'=>$check_value
          ));

          Session::insertSystemAccessLog(json_encode(array(
            'form'=>'LR-REPORT1-FORM',
            'action'=>'UPDATE',
            'version'=>$version,
            'an'=>$an,
        ),JSON_UNESCAPED_UNICODE));

            //เมื่อ update สำเร็จ
          $output_error = '<div class="alert alert-success">บันทึกข้อมูลเรียบร้อยแล้วคะ</div>';

        }catch (PDOException  $e) {
//เมื่อเกิดข้อผิดพลาด
echo $e->getMessage();
$output_error = '<div class="alert alert-danger">ERROR !!</div>';

        }

        echo $output_error;


        ?>
