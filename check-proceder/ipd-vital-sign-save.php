<?php


require_once '../include/Session.php';
Session::checkLoginSessionAndShowMessage(); //เช็ค session
// SessionManager::checkPermissionAndShowMessage('KPHIS_ACCESS_IPD_NURSE_PROGRAM');
/*if(!Session::checkPermission('VITAL_SIGN','ADD') &&
   !Session::checkPermission('VITAL_SIGN','EDIT')){
    Session::responsePermissionErrorForJsonRequest(null);
    exit;
}
*/

require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';

$conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล

$show_message = '';

//check for require field
if(StringUtils::isBlankOrNull($_REQUEST['data_mode'])
        || StringUtils::isBlankOrNull($_REQUEST['an'])
		|| StringUtils::isBlankOrNull($_REQUEST['vs_date'])
		|| StringUtils::isBlankOrNull($_REQUEST['vs_time'])
		) {
	exit;
}

$data_mode = $_REQUEST['data_mode'];
$vs_id = $_REQUEST['vs_id'];
$an = $_REQUEST['an'];
$hn = KphisQueryUtils::getHnByAn($an);
$vs_datetime = $_REQUEST['vs_date'].' '.$_REQUEST['vs_time'];
$bt = StringUtils::isBlankOrNull($_REQUEST['bt']) ? null : $_REQUEST['bt'];
$pr = StringUtils::isBlankOrNull($_REQUEST['pr']) ? null : $_REQUEST['pr'];
$rr = StringUtils::isBlankOrNull($_REQUEST['rr']) ? null : $_REQUEST['rr'];
$respirator = ($_REQUEST['respirator']) == null ?  'N':'Y';
$sbp = StringUtils::isBlankOrNull($_REQUEST['sbp']) ? null : $_REQUEST['sbp'];
$dbp = StringUtils::isBlankOrNull($_REQUEST['dbp']) ? null : $_REQUEST['dbp'];
$inotrope = ($_REQUEST['inotrope']) == null ?  'N':'Y';
$map = StringUtils::isBlankOrNull($_REQUEST['map']) ? null : $_REQUEST['map'];
$sat = StringUtils::isBlankOrNull($_REQUEST['sat']) ? null : $_REQUEST['sat'];
$cvp = $_REQUEST['cvp'];
$end_co2 = StringUtils::isBlankOrNull($_REQUEST['end_co2']) ? null : $_REQUEST['end_co2'];
$conscious_id = StringUtils::isBlankOrNull($_REQUEST['conscious_id']) ? null : $_REQUEST['conscious_id'];
$bw = StringUtils::isBlankOrNull($_REQUEST['bw']) ? null : $_REQUEST['bw'];
$height = StringUtils::isBlankOrNull($_REQUEST['height']) ? null : $_REQUEST['height'];
$urine = $_REQUEST['urine'];
$catheter = ($_REQUEST['catheter']) == null ?  'N':'Y';
$urine_amount = $_REQUEST['urine_amount'];
$urine_duration = $_REQUEST['urine_duration'];
$feces = $_REQUEST['feces'];
$head = StringUtils::isBlankOrNull($_REQUEST['head']) ? null : $_REQUEST['head'];
$t_inc = StringUtils::isBlankOrNull($_REQUEST['t_inc']) ? null : $_REQUEST['t_inc'];
$line_id = StringUtils::isBlankOrNull($_REQUEST['line_id']) ? null : $_REQUEST['line_id'];
$line_no = StringUtils::isBlankOrNull($_REQUEST['line_no']) ? null : $_REQUEST['line_no'];
$line_mark = StringUtils::isBlankOrNull($_REQUEST['line_mark']) ? null : $_REQUEST['line_mark'];
$braden = StringUtils::isBlankOrNull($_REQUEST['braden']) ? null : $_REQUEST['braden'];
$pain = StringUtils::isBlankOrNull($_REQUEST['pain']) ? null : $_REQUEST['pain'];
$eye = StringUtils::isBlankOrNull($_REQUEST['eye']) ? null : $_REQUEST['eye'];
$verbal = $_REQUEST['verbal'];
$movement = StringUtils::isBlankOrNull($_REQUEST['movement']) ? null : $_REQUEST['movement'];
$right_pupil = StringUtils::isBlankOrNull($_REQUEST['right_pupil']) ? null : $_REQUEST['right_pupil'];
$right_cha_id = StringUtils::isBlankOrNull($_REQUEST['right_cha_id']) ? null : $_REQUEST['right_cha_id'];
$left_pupil = StringUtils::isBlankOrNull($_REQUEST['left_pupil']) ? null : $_REQUEST['left_pupil'];
$left_cha_id = StringUtils::isBlankOrNull($_REQUEST['left_cha_id']) ? null : $_REQUEST['left_cha_id'];
$va_id = StringUtils::isBlankOrNull($_REQUEST['va_id']) ? null : $_REQUEST['va_id'];
$mass_id = StringUtils::isBlankOrNull($_REQUEST['mass_id']) ? null : $_REQUEST['mass_id'];
$lt_arm = StringUtils::isBlankOrNull($_REQUEST['lt_arm']) ? null : $_REQUEST['lt_arm'];
$lt_leg = StringUtils::isBlankOrNull($_REQUEST['lt_leg']) ? null : $_REQUEST['lt_leg'];
$rt_arm = StringUtils::isBlankOrNull($_REQUEST['rt_arm']) ? null : $_REQUEST['rt_arm'];
$rt_leg = StringUtils::isBlankOrNull($_REQUEST['rt_leg']) ? null : $_REQUEST['rt_leg'];
$severity = StringUtils::isBlankOrNull($_REQUEST['severity']) ? null : $_REQUEST['severity'];
$had_name = $_REQUEST['had_name'];
$had_drop = $_REQUEST['had_drop'];
$hct = StringUtils::isBlankOrNull($_REQUEST['hct']) ? null : $_REQUEST['hct'];
$dtx = StringUtils::isBlankOrNull($_REQUEST['dtx']) ? null : $_REQUEST['dtx'];
$bl = StringUtils::isBlankOrNull($_REQUEST['bl']) ? null : $_REQUEST['bl'];
$mcb = StringUtils::isBlankOrNull($_REQUEST['mcb']) ? null : $_REQUEST['mcb'];
$suction = ($_REQUEST['suction']) == null ?  'N':'Y';
$nb = ($_REQUEST['nb']) == null ?  'N':'Y';
$o2_id = StringUtils::isBlankOrNull($_REQUEST['o2_id']) ? null : $_REQUEST['o2_id'];
$o2_flow = StringUtils::isBlankOrNull($_REQUEST['o2_flow']) ? null : $_REQUEST['o2_flow'];
$tube_id = StringUtils::isBlankOrNull($_REQUEST['tube_id']) ? null : $_REQUEST['tube_id'];
$tube_no = StringUtils::isBlankOrNull($_REQUEST['tube_no']) ? null : $_REQUEST['tube_no'];
$tube_mark = StringUtils::isBlankOrNull($_REQUEST['tube_mark']) ? null : $_REQUEST['tube_mark'];
$ventilator_name = $_REQUEST['ventilator_name'];
$mode = $_REQUEST['mode'];
$tv = StringUtils::isBlankOrNull($_REQUEST['tv']) ? null : $_REQUEST['tv'];
$pip = StringUtils::isBlankOrNull($_REQUEST['pip']) ? null : $_REQUEST['pip'];
$r_rate = StringUtils::isBlankOrNull($_REQUEST['r_rate']) ? null : $_REQUEST['r_rate'];
$i_rate = StringUtils::isBlankOrNull($_REQUEST['i_rate']) ? null : $_REQUEST['i_rate'];
$e_rate = StringUtils::isBlankOrNull($_REQUEST['e_rate']) ? null : $_REQUEST['e_rate'];
$ti = StringUtils::isBlankOrNull($_REQUEST['ti']) ? null : $_REQUEST['ti'];
$ps = StringUtils::isBlankOrNull($_REQUEST['ps']) ? null : $_REQUEST['ps'];
$fio2 = StringUtils::isBlankOrNull($_REQUEST['fio2']) ? null : $_REQUEST['fio2'];
$peep = StringUtils::isBlankOrNull($_REQUEST['peep']) ? null : $_REQUEST['peep'];
$ft = StringUtils::isBlankOrNull($_REQUEST['ft']) ? null : $_REQUEST['ft'];
$delta_p = StringUtils::isBlankOrNull($_REQUEST['delta_p']) ? null : $_REQUEST['delta_p'];
$o2_map = StringUtils::isBlankOrNull($_REQUEST['o2_map']) ? null : $_REQUEST['o2_map'];
$intake_id = StringUtils::isBlankOrNull($_REQUEST['intake_id']) ? null : $_REQUEST['intake_id'];
$intake_type = $_REQUEST['intake_type'];
$intake_amount = StringUtils::isBlankOrNull($_REQUEST['intake_amount']) ? null : $_REQUEST['intake_amount'];
$intake_absorb = StringUtils::isBlankOrNull($_REQUEST['intake_absorb']) ? null : $_REQUEST['intake_absorb'];
$other = $_REQUEST['other'];
$output_id = StringUtils::isBlankOrNull($_REQUEST['output_id']) ? null : $_REQUEST['output_id'];
$output_amount = StringUtils::isBlankOrNull($_REQUEST['output_amount']) ? null : $_REQUEST['output_amount'];
$lr_int = $_REQUEST['lr_int'];
$lr_dur = StringUtils::isBlankOrNull($_REQUEST['lr_dur']) ? null : $_REQUEST['lr_dur'];
$lr_fsh = StringUtils::isBlankOrNull($_REQUEST['lr_fsh']) ? null : $_REQUEST['lr_fsh'];
$lr_sev = $_REQUEST['lr_sev'];
$lr_cer = $_REQUEST['lr_cer'];
$lr_eff = StringUtils::isBlankOrNull($_REQUEST['lr_eff']) ? null : $_REQUEST['lr_eff'];
$lr_sta = $_REQUEST['lr_sta'];
$lr_mem = $_REQUEST['lr_mem'];
$lr_af = $_REQUEST['lr_af'];

//แก้ไข
//$create_user = 'khom';
$create_user = $_SESSION['loginname'];
//$create_datetime = $_REQUEST['create_datetime'];
//$update_user = 'khom';
$update_user = $_SESSION['loginname'];
//$update_datetime = $_REQUEST['update_datetime'];
$version = 1;

try {
    if($data_mode == "I"){
        $stmt = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".ipd_vs_vital_sign (hn,an,vs_datetime,bt,pr,rr,respirator,sbp,dbp,inotrope,
                map,sat,cvp,end_co2,conscious_id,bw,height,urine,catheter,urine_amount,urine_duration,
                feces,head,t_inc,line_id,line_no,line_mark,braden,pain,eye,verbal,
                movement,right_pupil,right_cha_id,left_pupil,left_cha_id,va_id,mass_id,
                lt_arm,lt_leg,rt_arm,rt_leg,
                severity,had_name,had_drop,
                hct,dtx,bl,mcb,suction,nb,o2_id,o2_flow,tube_id,tube_no,
                tube_mark,ventilator_name,mode,tv,pip,r_rate,i_rate,e_rate,ti,ps,
                fio2,peep,ft,delta_p,o2_map,intake_id,intake_type,intake_amount,intake_absorb,other,
                output_id,output_amount,lr_int,lr_dur,lr_fsh,lr_sev,lr_cer,lr_eff,lr_sta,lr_mem,
                lr_af,create_user,create_datetime,update_user,update_datetime,version)
                VALUES (:hn,:an,:vs_datetime,:bt,:pr,:rr,:respirator,:sbp,:dbp,:inotrope,
                :map,:sat,:cvp,:end_co2,:conscious_id,:bw,:height,:urine,:catheter,:urine_amount,:urine_duration,
                :feces,:head,:t_inc,:line_id,:line_no,:line_mark,:braden,:pain,:eye,:verbal,
                :movement,:right_pupil,:right_cha_id,:left_pupil,:left_cha_id,:va_id,:mass_id,
                :lt_arm,:lt_leg,:rt_arm,:rt_leg,
                :severity,:had_name,:had_drop,
                :hct,:dtx,:bl,:mcb,:suction,:nb,:o2_id,:o2_flow,:tube_id,:tube_no,
                :tube_mark,:ventilator_name,:mode,:tv,:pip,:r_rate,:i_rate,:e_rate,:ti,:ps,
                :fio2,:peep,:ft,:delta_p,:o2_map,:intake_id,:intake_type,:intake_amount,:intake_absorb,:other,
                :output_id,:output_amount,:lr_int,:lr_dur,:lr_fsh,:lr_sev,:lr_cer,:lr_eff,:lr_sta,:lr_mem,
                :lr_af,
                :create_user,now(),:update_user,now(),
                :version)");

        $stmt->execute(array('hn'=>$hn, 'an'=>$an, 'vs_datetime'=>$vs_datetime, 'bt'=>$bt, 'pr'=>$pr,
                'rr'=>$rr, 'respirator'=>$respirator, 'sbp'=>$sbp, 'dbp'=>$dbp, 'inotrope'=>$inotrope,
                'map'=>$map, 'sat'=>$sat, 'cvp'=>$cvp, 'end_co2'=>$end_co2, 'conscious_id'=>$conscious_id,
                'bw'=>$bw, 'height'=>$height, 'urine'=>$urine, 'catheter'=>$catheter, 'urine_amount'=>$urine_amount, 'urine_duration'=>$urine_duration,
                'feces'=>$feces, 'head'=>$head, 't_inc'=>$t_inc, 'line_id'=>$line_id, 'line_no'=>$line_no,
                'line_mark'=>$line_mark, 'braden'=>$braden, 'pain'=>$pain, 'eye'=>$eye, 'verbal'=>$verbal,
                'movement'=>$movement, 'right_pupil'=>$right_pupil, 'right_cha_id'=>$right_cha_id, 'left_pupil'=>$left_pupil, 'left_cha_id'=>$left_cha_id,
                'va_id'=>$va_id, 'mass_id'=>$mass_id,
                'lt_arm'=>$lt_arm, 'lt_leg'=>$lt_leg, 'rt_arm'=>$rt_arm, 'rt_leg'=>$rt_leg,
                'severity'=>$severity, 'had_name'=>$had_name, 'had_drop'=>$had_drop,
                'hct'=>$hct, 'dtx'=>$dtx, 'bl'=>$bl, 'mcb'=>$mcb, 'suction'=>$suction,
                'nb'=>$nb, 'o2_id'=>$o2_id, 'o2_flow'=>$o2_flow, 'tube_id'=>$tube_id, 'tube_no'=>$tube_no,
                'tube_mark'=>$tube_mark, 'ventilator_name'=>$ventilator_name, 'mode'=>$mode, 'tv'=>$tv, 'pip'=>$pip,
                'r_rate'=>$r_rate, 'i_rate'=>$i_rate, 'e_rate'=>$e_rate, 'ti'=>$ti, 'ps'=>$ps,
                'fio2'=>$fio2, 'peep'=>$peep, 'ft'=>$ft, 'delta_p'=>$delta_p, 'o2_map'=>$o2_map,
                'intake_id'=>$intake_id, 'intake_type'=>$intake_type, 'intake_amount'=>$intake_amount, 'intake_absorb'=>$intake_absorb, 'other'=>$other,
                'output_id'=>$output_id, 'output_amount'=>$output_amount, 'lr_int'=>$lr_int, 'lr_dur'=>$lr_dur, 'lr_fsh'=>$lr_fsh,
                'lr_sev'=>$lr_sev, 'lr_cer'=>$lr_cer, 'lr_eff'=>$lr_eff, 'lr_sta'=>$lr_sta, 'lr_mem'=>$lr_mem,
                'lr_af'=>$lr_af,
                'create_user'=>$create_user, 'update_user'=>$update_user,
                'version'=>$version));
        $show_message = '<div class="alert alert-success">บันทึกข้อมูลเรียบร้อยแล้วค่ะ</div>';
    } else if($data_mode == "U"){
        $stmt = $conn->prepare("UPDATE ".DbConstant::KPHIS_DBNAME.".ipd_vs_vital_sign SET hn=:hn, an=:an, vs_datetime=:vs_datetime, bt=:bt, pr=:pr,
                rr=:rr, respirator=:respirator, sbp=:sbp, dbp=:dbp, inotrope=:inotrope,
                map=:map, sat=:sat, cvp=:cvp, end_co2=:end_co2, conscious_id=:conscious_id,
                bw=:bw, height=:height, urine=:urine, catheter=:catheter, urine_amount=:urine_amount, urine_duration=:urine_duration,
                feces=:feces, head=:head, t_inc=:t_inc, line_id=:line_id, line_no=:line_no,
                line_mark=:line_mark, braden=:braden, pain=:pain, eye=:eye, verbal=:verbal,
                movement=:movement, right_pupil=:right_pupil, right_cha_id=:right_cha_id, left_pupil=:left_pupil, left_cha_id=:left_cha_id,
                va_id=:va_id, mass_id=:mass_id,
                lt_arm=:lt_arm, lt_leg=:lt_leg, rt_arm=:rt_arm, rt_leg=:rt_leg,
                severity=:severity, had_name=:had_name, had_drop=:had_drop,
                hct=:hct, dtx=:dtx, bl=:bl, mcb=:mcb, suction=:suction,
                nb=:nb, o2_id=:o2_id, o2_flow=:o2_flow, tube_id=:tube_id, tube_no=:tube_no,
                tube_mark=:tube_mark, ventilator_name=:ventilator_name, mode=:mode, tv=:tv, pip=:pip,
                r_rate=:r_rate, i_rate=:i_rate, e_rate=:e_rate, ti=:ti, ps=:ps,
                fio2=:fio2, peep=:peep, ft=:ft, delta_p=:delta_p, o2_map=:o2_map,
                intake_id=:intake_id, intake_type=:intake_type, intake_amount=:intake_amount, intake_absorb=:intake_absorb, other=:other,
                output_id=:output_id, output_amount=:output_amount, lr_int=:lr_int, lr_dur=:lr_dur, lr_fsh=:lr_fsh,
                lr_sev=:lr_sev, lr_cer=:lr_cer, lr_eff=:lr_eff, lr_sta=:lr_sta, lr_mem=:lr_mem,
                lr_af=:lr_af,
                update_user=:update_user, update_datetime=now(),
                version=(version+1) WHERE vs_id=:vs_id");

        $stmt->execute(array('vs_id'=>$vs_id, 'hn'=>$hn, 'an'=>$an, 'vs_datetime'=>$vs_datetime, 'bt'=>$bt, 'pr'=>$pr,
                'rr'=>$rr, 'respirator'=>$respirator, 'sbp'=>$sbp, 'dbp'=>$dbp, 'inotrope'=>$inotrope,
                'map'=>$map, 'sat'=>$sat, 'cvp'=>$cvp, 'end_co2'=>$end_co2, 'conscious_id'=>$conscious_id,
                'bw'=>$bw, 'height'=>$height, 'urine'=>$urine, 'catheter'=>$catheter, 'urine_amount'=>$urine_amount, 'urine_duration'=>$urine_duration,
                'feces'=>$feces, 'head'=>$head, 't_inc'=>$t_inc, 'line_id'=>$line_id, 'line_no'=>$line_no,
                'line_mark'=>$line_mark, 'braden'=>$braden, 'pain'=>$pain, 'eye'=>$eye, 'verbal'=>$verbal,
                'movement'=>$movement, 'right_pupil'=>$right_pupil, 'right_cha_id'=>$right_cha_id, 'left_pupil'=>$left_pupil, 'left_cha_id'=>$left_cha_id,
                'va_id'=>$va_id, 'mass_id'=>$mass_id,
                'lt_arm'=>$lt_arm, 'lt_leg'=>$lt_leg, 'rt_arm'=>$rt_arm, 'rt_leg'=>$rt_leg,
                'severity'=>$severity, 'had_name'=>$had_name, 'had_drop'=>$had_drop,
                'hct'=>$hct, 'dtx'=>$dtx, 'bl'=>$bl, 'mcb'=>$mcb, 'suction'=>$suction,
                'nb'=>$nb, 'o2_id'=>$o2_id, 'o2_flow'=>$o2_flow, 'tube_id'=>$tube_id, 'tube_no'=>$tube_no,
                'tube_mark'=>$tube_mark, 'ventilator_name'=>$ventilator_name, 'mode'=>$mode, 'tv'=>$tv, 'pip'=>$pip,
                'r_rate'=>$r_rate, 'i_rate'=>$i_rate, 'e_rate'=>$e_rate, 'ti'=>$ti, 'ps'=>$ps,
                'fio2'=>$fio2, 'peep'=>$peep, 'ft'=>$ft, 'delta_p'=>$delta_p, 'o2_map'=>$o2_map,
                'intake_id'=>$intake_id, 'intake_type'=>$intake_type, 'intake_amount'=>$intake_amount, 'intake_absorb'=>$intake_absorb, 'other'=>$other,
                'output_id'=>$output_id, 'output_amount'=>$output_amount, 'lr_int'=>$lr_int, 'lr_dur'=>$lr_dur, 'lr_fsh'=>$lr_fsh,
                'lr_sev'=>$lr_sev, 'lr_cer'=>$lr_cer, 'lr_eff'=>$lr_eff, 'lr_sta'=>$lr_sta, 'lr_mem'=>$lr_mem,
                'lr_af'=>$lr_af,
                'update_user'=>$update_user));
        $show_message = '<div class="alert alert-success">บันทึกข้อมูลเรียบร้อยแล้วค่ะ</div>';
    } else if($data_mode == "D"){
        $stmt = $conn->prepare("DELETE FROM ".DbConstant::KPHIS_DBNAME.".ipd_vs_vital_sign WHERE vs_id=:vs_id");
        $stmt->execute(array('vs_id'=>$vs_id));
        $show_message = '<div class="alert alert-success">ลบข้อมูลเรียบร้อยแล้วค่ะ</div>';
    }

} catch (PDOException  $e) {
    echo $e->getMessage();
    $show_message = '<div class="alert alert-danger">ERROR !!VITAL SIGN</div>';
}

echo $show_message;
?>