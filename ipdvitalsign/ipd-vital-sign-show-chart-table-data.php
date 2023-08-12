<?php
/*
require_once './project/function/SessionManager.php';
SessionManager::checkLoginSessionAndShowMessage(); //เช็ค session
// SessionManager::checkPermissionAndShowMessage('KPHIS_ACCESS_IPD_NURSE_PROGRAM');
if(!SessionManager::checkPermission('VITAL_SIGN','VIEW')){
    SessionManager::responsePermissionErrorForJsonRequest(null);
    exit;
}
*/
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';

$conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล

//check for require field
if(empty($_REQUEST['vs_id'])
        && empty($_REQUEST['an'])
		&& empty($_REQUEST['hn'])
		&& empty($_REQUEST['start_vs_datetime'])
		&& empty($_REQUEST['end_vs_datetime'])
		) {
	exit;
}

$vs_id = empty($_REQUEST['vs_id']) ? null : $_REQUEST['vs_id'];
$an = empty($_REQUEST['an']) ? null : $_REQUEST['an'];
$start_vs_datetime = empty($_REQUEST['start_vs_datetime']) ? null : $_REQUEST['start_vs_datetime'];
$end_vs_datetime = empty($_REQUEST['end_vs_datetime']) ? null : $_REQUEST['end_vs_datetime'];

try {
    $parameters = [];
    $sql = "SELECT vs.vs_id, vs.an, vs.vs_datetime, vs.bt, vs.pr, vs.rr, vs.respirator, vs.sbp, vs.dbp, vs.inotrope, vs.map,  vs.sat, vs.cvp,
        vs.end_co2, vs.conscious_id, vs.bw, vs.height, vs.urine, vs.catheter, vs.urine_amount, vs.urine_duration, vs.feces, vs.head, vs.t_inc, vs.line_id, vs.line_no,
        vs.line_mark, vs.braden, vs.pain, vs.eye, vs.verbal, vs.movement, vs.right_pupil, vs.right_cha_id, vs.left_pupil, vs.left_cha_id,
        vs.va_id, vs.mass_id, vs.lt_arm, vs.lt_leg, vs.rt_arm, vs.rt_leg,
        vs.severity, vs.had_name, vs.had_drop, vs.hct, vs.dtx, vs.bl, vs.mcb, vs.suction,
        vs.nb, vs.o2_id, vs.o2_flow, vs.tube_id, vs.tube_no, vs.tube_mark, vs.ventilator_name, vs.mode, vs.tv, vs.pip,
        vs.r_rate, vs.i_rate, vs.e_rate, vs.ti, vs.ps, vs.fio2, vs.peep, vs.ft, vs.delta_p, vs.o2_map,
        vs.intake_id, vs.intake_type, vs.intake_amount, vs.intake_absorb, vs.output_id, vs.output_amount,
        vs.lr_int, vs.lr_dur, vs.lr_fsh, vs.lr_sev, vs.lr_cer, vs.lr_eff, vs.lr_sta, vs.lr_mem, vs.lr_af, vs.other,
        vs.create_user, vs.create_datetime, vs.update_user, vs.update_datetime, vs.version,
        create_opduser.name create_opduser_name, update_opduser.name update_opduser_name,
        ipd_vs_conscious.conscious_name,
        ipd_vs_line.line_name,
        left_cha.cha_name as left_cha_name,
        right_cha.cha_name as right_cha_name,
        ipd_vs_va.va_name,
        ipd_vs_mass.mass_name,
        ipd_vs_lt_arm.lt_arm_name,
        ipd_vs_lt_leg.lt_leg_name,
        ipd_vs_rt_arm.rt_arm_name,
        ipd_vs_rt_leg.rt_leg_name,
        ipd_vs_o2.o2_name,
        ipd_vs_tube.tube_name,
        ipd_vs_intake.intake_name,
        ipd_vs_output.output_name,
        ipd_vs_lr_sta.lr_sta_name,
        ipd_vs_lr_mem.lr_mem_name
    FROM ".DbConstant::KPHIS_DBNAME.".ipd_vs_vital_sign vs
        LEFT OUTER JOIN ".DbConstant::KPHIS_DBNAME.".ipd_vs_conscious on vs.conscious_id = ipd_vs_conscious.conscious_id
        LEFT OUTER JOIN ".DbConstant::KPHIS_DBNAME.".ipd_vs_line on vs.line_id = ipd_vs_line.line_id
        LEFT OUTER JOIN ".DbConstant::KPHIS_DBNAME.".ipd_vs_cha left_cha on vs.left_cha_id = left_cha.cha_id
        LEFT OUTER JOIN ".DbConstant::KPHIS_DBNAME.".ipd_vs_cha right_cha on vs.right_cha_id = right_cha.cha_id
        LEFT OUTER JOIN ".DbConstant::KPHIS_DBNAME.".ipd_vs_va on vs.va_id = ipd_vs_va.va_id
        LEFT OUTER JOIN ".DbConstant::KPHIS_DBNAME.".ipd_vs_mass on vs.mass_id = ipd_vs_mass.mass_id
        LEFT OUTER JOIN ".DbConstant::KPHIS_DBNAME.".ipd_vs_o2 on vs.o2_id = ipd_vs_o2.o2_id
        LEFT OUTER JOIN ".DbConstant::KPHIS_DBNAME.".ipd_vs_tube on vs.tube_id = ipd_vs_tube.tube_id
        LEFT OUTER JOIN ".DbConstant::KPHIS_DBNAME.".ipd_vs_intake on vs.intake_id = ipd_vs_intake.intake_id
        LEFT OUTER JOIN ".DbConstant::KPHIS_DBNAME.".ipd_vs_output on vs.output_id = ipd_vs_output.output_id
        LEFT OUTER JOIN ".DbConstant::KPHIS_DBNAME.".ipd_vs_lr_sta on vs.lr_sta = ipd_vs_lr_sta.lr_sta_id
        LEFT OUTER JOIN ".DbConstant::KPHIS_DBNAME.".ipd_vs_lr_mem on vs.lr_mem = ipd_vs_lr_mem.lr_mem_id
        LEFT OUTER JOIN ".DbConstant::KPHIS_DBNAME.".ipd_vs_lt_arm on vs.lt_arm = ipd_vs_lt_arm.lt_arm
        LEFT OUTER JOIN ".DbConstant::KPHIS_DBNAME.".ipd_vs_lt_leg on vs.lt_leg = ipd_vs_lt_leg.lt_leg
        LEFT OUTER JOIN ".DbConstant::KPHIS_DBNAME.".ipd_vs_rt_arm on vs.rt_arm = ipd_vs_rt_arm.rt_arm
        LEFT OUTER JOIN ".DbConstant::KPHIS_DBNAME.".ipd_vs_rt_leg on vs.rt_leg = ipd_vs_rt_leg.rt_leg
        LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".opduser create_opduser on vs.create_user = create_opduser.loginname
        LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".opduser update_opduser on vs.update_user = update_opduser.loginname
        WHERE 1=1 ";
    if(!empty($vs_id)) {
        $sql .= " AND vs.vs_id=:vs_id ";
        $parameters['vs_id'] = $vs_id;
    }
    if(!empty($an)) {
        $sql .= " AND vs.an=:an ";
        $parameters['an'] = $an;
    }
    if(!empty($start_vs_datetime)) {
        $sql .= " AND vs.vs_datetime>=concat(:start_vs_datetime,' 00:00:00.000') ";
        $parameters['start_vs_datetime'] = $start_vs_datetime;
    }
    if(!empty($end_vs_datetime)) {
        $sql .= " AND vs.vs_datetime<=concat(:end_vs_datetime,' 23:59:59') ";
        $parameters['end_vs_datetime'] = $end_vs_datetime;
    }
    $sql .= " ORDER BY vs.vs_datetime desc";
    $stmt = $conn->prepare($sql);
    $stmt->execute($parameters);
    // echo $sql;
    // print_r($parameters);
	$rows = array();
	while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$rows[] = $r;
	}
    // print_r($rows);
	echo json_encode($rows, JSON_UNESCAPED_UNICODE );
} catch (PDOException  $e) {
    echo $e->getMessage();
    http_response_code(500);
}

?>