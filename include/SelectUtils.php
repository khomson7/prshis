<?php
require_once $_SERVER['DOCUMENT_ROOT']. '/vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT']. '/include/DbUtils.php';
class SelectUtils {

	public static function getSelectOption($sql, $conn, $selectedValue){
		$stmt = $conn->query($sql);
		$keyValueArray = $stmt->fetchAll();
		return SelectUtils::getSelectOptionFromArray($keyValueArray, $selectedValue);
	}

	/** keyValueArray must have a `key` and a `value` columns */
	public static function getSelectOptionFromArray($keyValueArray, $selectedValue){
		$resultText = "";
		foreach($keyValueArray as $row):
			$resultText .= '<option value="'.htmlspecialchars($row["key"]).'"';
			if($selectedValue  != '' && $selectedValue == $row["key"]){
				$resultText .=  " selected ";
			}
			$resultText .=  ">".htmlspecialchars($row["value"])."</option>";
		endforeach;
		return $resultText;
	}

	public static function getColorSelectOption($sql, $conn, $selectedValue){
		$stmt = $conn->query($sql);
		$keyValueArray = $stmt->fetchAll();
		return SelectUtils::getColorSelectOptionFromArray($keyValueArray, $selectedValue);
	}

	/** keyValueArray must have a `key` and a `value` columns */
	public static function getColorSelectOptionFromArray($keyValueArray, $selectedValue){
		$resultText = "";
		foreach($keyValueArray as $row):
			$resultText .= '<option value="'.htmlspecialchars($row["key"]).'"';
			if($row["color"] != null){
				$resultText .=  ' data-color="'.$row["color"].'" ';
			}
			if($row["color"] != null){
				$resultText .=  ' style="color: white;
				background-color: '.$row["color"].';
				font-weight: bold;" ';
			}
			if($selectedValue  != '' && $selectedValue == $row["key"]){
				$resultText .=  " selected ";
			}
			$resultText .=  ">".htmlspecialchars($row["value"])."</option>";
		endforeach;
		return $resultText;
	}

	public static function getConsciousSelectOption($selectedValue){
		$sql = "select conscious_id as `key`, `conscious_name` as `value` from ipd_vs_conscious order by conscious_id";
		return SelectUtils::getSelectOption($sql, DbUtils::get_hosxp_connection(), $selectedValue);
	}

	public static function getLineSelectOption($selectedValue){
		$sql = "select line_id as `key`, `line_name` as `value` from ipd_vs_line order by line_id";
		return SelectUtils::getSelectOption($sql, DbUtils::get_hosxp_connection(), $selectedValue);
	}

	public static function getChaSelectOption($selectedValue){
		$sql = "select cha_id as `key`, `cha_name` as `value` from ipd_vs_cha order by cha_id";
		return SelectUtils::getSelectOption($sql, DbUtils::get_hosxp_connection(), $selectedValue);
	}

	public static function getMassSelectOption($selectedValue){
		$sql = "select mass_id as `key`, `mass_name` as `value` from ipd_vs_mass order by mass_id";
		return SelectUtils::getSelectOption($sql, DbUtils::get_hosxp_connection(), $selectedValue);
	}

	public static function getVaSelectOption($selectedValue){
		$sql = "select va_id as `key`, `va_name` as `value` from ipd_vs_va order by va_id";
		return SelectUtils::getSelectOption($sql, DbUtils::get_hosxp_connection(), $selectedValue);
	}

	public static function getIntakeSelectOption($selectedValue){
		$sql = "select intake_id as `key`, `intake_name` as `value` from ipd_vs_intake order by intake_id";
		return SelectUtils::getSelectOption($sql, DbUtils::get_hosxp_connection(), $selectedValue);
	}

	public static function getOutputSelectOption($selectedValue){
		$sql = "select output_id as `key`, `output_name` as `value` from ipd_vs_output order by output_id";
		return SelectUtils::getSelectOption($sql, DbUtils::get_hosxp_connection(), $selectedValue);
	}

	public static function getO2SelectOption($selectedValue){
		$sql = "select o2_id as `key`, `o2_name` as `value` from ipd_vs_o2 order by o2_id";
		return SelectUtils::getSelectOption($sql, DbUtils::get_hosxp_connection(), $selectedValue);
	}

	public static function getTubeSelectOption($selectedValue){
		$sql = "select tube_id as `key`, `tube_name` as `value` from ipd_vs_tube order by tube_id";
		return SelectUtils::getSelectOption($sql, DbUtils::get_hosxp_connection(), $selectedValue);
	}

	public static function getLRstaSelectOption($selectedValue){
		$sql = "select lr_sta_id as `key`, `lr_sta_name` as `value` from ipd_vs_lr_sta order by lr_sta_id";
		return SelectUtils::getSelectOption($sql, DbUtils::get_hosxp_connection(), $selectedValue);
	}

	public static function getLRmemSelectOption($selectedValue){
		$sql = "select lr_mem_id as `key`, `lr_mem_name` as `value` from ipd_vs_lr_mem order by lr_mem_id";
		return SelectUtils::getSelectOption($sql, DbUtils::get_hosxp_connection(), $selectedValue);
	}

	public static function getUrinDurationSelectOption($selectedValue){
		$sql = "select urine_d_id as `key`, `urine_d_name` as `value` from ipd_vs_urine_duration order by urine_d_id";
		return SelectUtils::getSelectOption($sql, DbUtils::get_hosxp_connection(), $selectedValue);
	}

	public static function getUrinAmountSelectOption($selectedValue){
		$sql = "select urine_amount_id as `key`, `urine_amount_name` as `value` from ipd_vs_urine_amount order by urine_amount_id";
		return SelectUtils::getSelectOption($sql, DbUtils::get_hosxp_connection(), $selectedValue);
	}

	public static function getWardSelectOption($selectedValue){
		$sql = "select ward as `key`, `name` as `value` from ".DbConstant::HOSXP_DBNAME.".ward order by name";
		return SelectUtils::getSelectOption($sql, DbUtils::get_hosxp_connection(), $selectedValue);
	}

	// public static function getWardSelectOptionForWardPasscode($loginname){
	// 	$sql = "select ward.ward as `key`, ward.`name` as `value` from ".DbConstant::HOSXP_DBNAME.".ward inner join ".DbConstant::KPHIS_DBNAME.".ipd_ward_passcode_user wpu on wpu.ward = ward.ward where wpu.loginname = :loginname order by name";
	// 	$conn = DbUtils::get_hosxp_connection();
	// 	$stmt = $conn->prepare($sql);
    //     $parameters['loginname'] = $loginname;
	// 	$stmt->execute($parameters);
	// 	$keyValueArray = $stmt->fetchAll();
	// 	return SelectUtils::getSelectOptionFromArray($keyValueArray, null);
	// }

	public static function getDoctorSelectOption($selectedValue){
		$sql = "select code as `key`, `name` as `value` from ".DbConstant::HOSXP_DBNAME.".doctor
				where provider_type_code IN ('01','02')
				AND licenseno is not null
				AND trim(licenseno) <> ''
				AND licenseno <> '-99999'
				order by name";
		//provider_type_code = '01' เฉพาะแพทย์
		return SelectUtils::getSelectOption($sql, DbUtils::get_hosxp_connection(), $selectedValue);
	}

	public static function getAllDoctorSelectOption($selectedValue){
		$sql = "select code as `key`, `name` as `value` from ".DbConstant::HOSXP_DBNAME.".doctor order by name";
		return SelectUtils::getSelectOption($sql, DbUtils::get_hosxp_connection(), $selectedValue);
	}

	public static function getSpcltySelectOption($selectedValue){
		$sql = "select spclty as `key`, `name` as `value` from ".DbConstant::HOSXP_DBNAME.".spclty order by name";
		return SelectUtils::getSelectOption($sql, DbUtils::get_hosxp_connection(), $selectedValue);
	}

	public static function getTempsmpSelectOption($selectedValue){
		//template กลุ่มอาการ
		$sql = "select smp_id as `key`, `smp_name` as `value` from ".DbConstant::KPHIS_DBNAME.".ipd_tmp_group_smp order by smp_order";
		return SelectUtils::getSelectOption($sql, DbUtils::get_hosxp_connection(), $selectedValue);
	}

	// public static function getErBedSelectOption($selectedValue){
	// 	$sql = "SELECT `bedno` as `key`, `bedno` as `value` FROM `kph`.`bedno` WHERE `bedtype` = '14' order by `bedno`";
	// 	return SelectUtils::getSelectOption($sql, DbUtils::get_hosxp_connection(), $selectedValue);
	// }

	public static function getErBedSelectOption($selectedValue){
		$sql = "SELECT `opd_er_bed_id` as `key`, concat(opd_er_bed_type.`bed_type_name`,' ',opd_er_bed.`bedno`) as `value`, opd_er_bed_type.bed_type_color as color
				FROM `kphis`.`opd_er_bed`
				left outer join ".DbConstant::KPHIS_DBNAME.".opd_er_bed_type on opd_er_bed.bed_type=opd_er_bed_type.bed_type
				WHERE opd_er_bed.active <> 'N'
				order by opd_er_bed_type.display_order,opd_er_bed.display_order, `bedno`";
		return SelectUtils::getColorSelectOption($sql, DbUtils::get_hosxp_connection(), $selectedValue);
	}

	public static function getErPatientStatusSelectOption($selectedValue){
		$sql = "SELECT `er_patient_status_id` as `key`, `er_patient_status_name` as `value` FROM `kphis`.`opd_er_patient_status` order by display_order";
		return SelectUtils::getSelectOption($sql, DbUtils::get_hosxp_connection(), $selectedValue);
	}

	public static function getErDchTypeSelectOption($selectedValue){
		$sql = "SELECT `er_dch_type_id` as `key`, `er_dch_type_name` as `value` FROM `kphis`.`opd_er_dch_type` order by display_order";
		return SelectUtils::getSelectOption($sql, DbUtils::get_hosxp_connection(), $selectedValue);
	}

	public static function getConsultTypeSelectOption($selectedValue){
		$sql = "select consult_type_id as `key`, `consult_type_name` as `value` from ".DbConstant::KPHIS_DBNAME.".ipd_dr_consult_type order by consult_type_order";
		return SelectUtils::getSelectOption($sql, DbUtils::get_hosxp_connection(), $selectedValue);
	}

	public static function getEmergencySelectOption($selectedValue){
		$sql = "select emergency_id as `key`, emergency_name as `value` from ".DbConstant::KPHIS_DBNAME.".ipd_emergency order by emergency_id";
		return SelectUtils::getSelectOption($sql, DbUtils::get_hosxp_connection(), $selectedValue);
	}

	public static function getKphisSpcltySelectOption($selectedValue){
		$sql = "select spclty_id as `key`, spclty_name as `value` from ".DbConstant::KPHIS_DBNAME.".kphis_spclty order by spclty_order";
		return SelectUtils::getSelectOption($sql, DbUtils::get_hosxp_connection(), $selectedValue);
	}

	public static function getKphisEmergencyLevelSelectOption($selectedValue){
		$sql = "select emergency_level_id as `key`, emergency_level_name as `value` from ".DbConstant::KPHIS_DBNAME.".opd_er_emergency_level order by emergency_level_order";
		return SelectUtils::getSelectOption($sql, DbUtils::get_hosxp_connection(), $selectedValue);
	}

	public static function getEmergencyLevelSelectOption($selectedValue){
		$sql = "select er_emergency_level_id as `key`, er_emergency_level_name as `value` from ".DbConstant::HOSXP_DBNAME.".er_emergency_level order by er_emergency_level_id";
		return SelectUtils::getSelectOption($sql, DbUtils::get_hosxp_connection(), $selectedValue);
	}
	public static function getKphisLtArmSelectOption($selectedValue){
		$sql = "select lt_arm as `key`, lt_arm_name as `value` from ".DbConstant::KPHIS_DBNAME.".ipd_vs_lt_arm order by lt_arm";
		return SelectUtils::getSelectOption($sql, DbUtils::get_hosxp_connection(), $selectedValue);
	}
	public static function getKphisLtLegSelectOption($selectedValue){
		$sql = "select lt_leg as `key`, lt_leg_name as `value` from ".DbConstant::KPHIS_DBNAME.".ipd_vs_lt_leg order by lt_leg";
		return SelectUtils::getSelectOption($sql, DbUtils::get_hosxp_connection(), $selectedValue);
	}
	public static function getKphisRtArmSelectOption($selectedValue){
		$sql = "select rt_arm as `key`, rt_arm_name as `value` from ".DbConstant::KPHIS_DBNAME.".ipd_vs_rt_arm order by rt_arm";
		return SelectUtils::getSelectOption($sql, DbUtils::get_hosxp_connection(), $selectedValue);
	}
	public static function getKphisRtLegSelectOption($selectedValue){
		$sql = "select rt_leg as `key`, rt_leg_name as `value` from ".DbConstant::KPHIS_DBNAME.".ipd_vs_rt_leg order by rt_leg";
		return SelectUtils::getSelectOption($sql, DbUtils::get_hosxp_connection(), $selectedValue);
	}
	public static function getSystemAcRoleSelectOption($selectedValue){
		$sql = "select role as `key`, `role_desc` as `value` from ".DbConstant::KPHIS_DBNAME.".system_ac_role order by role";
		return SelectUtils::getSelectOption($sql, DbUtils::get_hosxp_connection(), $selectedValue);
	}

}