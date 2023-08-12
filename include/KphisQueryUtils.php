<?php
//require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/DbConstant.php';
require_once __DIR__ . '/StringUtils.php';
require_once __DIR__ . '/DbUtils.php';
class KphisQueryUtils {

    public static function getHnByVn($vn){
		if($vn != null && trim($vn) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("select hn from ".DbConstant::HOSXP_DBNAME.".ovst where vn=:vn");
			$stmt->execute(array('vn'=>$vn));
			if ($row = $stmt->fetch()){
				return $row['hn'];
            }
		}
	}

	public static function getVnByHn($hn){
		if($hn != null && trim($hn) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("select vn from ".DbConstant::HOSXP_DBNAME.".ovst where hn=:hn ORDER BY vstdate DESC limit 1");
			$stmt->execute(array('hn'=>$hn));
			if ($row = $stmt->fetch()){
				return $row['vn'];
            }
		}
	}

    public static function getHnByAn($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("select hn from ".DbConstant::HOSXP_DBNAME.".ipt where an=:an");
			$stmt->execute(array('an'=>$an));
			if ($row = $stmt->fetch()){
				return $row['hn'];
            }
		}
	}

    public static function getPatientName($hn){
		if($hn != null && trim($hn) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("select concat(patient.pname,patient.fname,' ',patient.lname) as patient_name from ".DbConstant::HOSXP_DBNAME.".patient where hn=:hn");
			$stmt->execute(array('hn'=>$hn));
			if ($row = $stmt->fetch()){
				return $row['patient_name'];
            }
		}
	}

	public static function getPatientCidAndPassportNo($hn){
		if($hn != null && trim($hn) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("select cid, passport_no from ".DbConstant::HOSXP_DBNAME.".patient where hn=:hn");
			$stmt->execute(array('hn'=>$hn));
			return $stmt->fetch();
		}
	}

    public static function getPatientCid($hn){
		if($hn != null && trim($hn) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("select cid from ".DbConstant::HOSXP_DBNAME.".patient where hn=:hn");
			$stmt->execute(array('hn'=>$hn));
			if ($row = $stmt->fetch()){
				return $row['cid'];
            }
		}
	}

    public static function getPatientPassportNo($hn){
		if($hn != null && trim($hn) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("select passport_no from ".DbConstant::HOSXP_DBNAME.".patient where hn=:hn");
			$stmt->execute(array('hn'=>$hn));
			if ($row = $stmt->fetch()){
				return $row['passport_no'];
            }
		}
	}

    public static function getVnByAn($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("select vn from ".DbConstant::HOSXP_DBNAME.".ipt where an=:an");
			$stmt->execute(array('an'=>$an));
			if ($row = $stmt->fetch()){
				return $row['vn'];
            }
		}
	}

    public static function getAnByVn($vn){
		if($vn != null && trim($vn) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("select an from ".DbConstant::HOSXP_DBNAME.".ovst where vn=:vn");
			$stmt->execute(array('vn'=>$vn));
			if ($row = $stmt->fetch()){
				return $row['an'];
            }
		}
	}

	public static function getRegdateByAn($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("select regdate from ".DbConstant::HOSXP_DBNAME.".ipt where an=:an");
			$stmt->execute(array('an'=>$an));
			if ($row = $stmt->fetch()){
				return $row['regdate'];
            }
		}
	}

	public static function getDchdateByAn($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("select dchdate from ".DbConstant::HOSXP_DBNAME.".ipt where an=:an");
			$stmt->execute(array('an'=>$an));
			if ($row = $stmt->fetch()){
				return $row['dchdate'];
            }
		}
	}

	public static function getMinOpdErVsDate($opd_er_order_master_id){
		if($opd_er_order_master_id != null && trim($opd_er_order_master_id) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("select min(date(vs_datetime)) as min_vs_date from ".DbConstant::KPHIS_DBNAME.".opd_er_vs_vital_sign where opd_er_order_master_id=:opd_er_order_master_id");
			$stmt->execute(array('opd_er_order_master_id'=>$opd_er_order_master_id));
			if ($row = $stmt->fetch()){
				return $row['min_vs_date'];
            }
		}
	}

	public static function getMaxOpdErVsDate($opd_er_order_master_id){
		if($opd_er_order_master_id != null && trim($opd_er_order_master_id) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("select max(date(vs_datetime)) as max_vs_date from ".DbConstant::KPHIS_DBNAME.".opd_er_vs_vital_sign where opd_er_order_master_id=:opd_er_order_master_id");
			$stmt->execute(array('opd_er_order_master_id'=>$opd_er_order_master_id));
			if ($row = $stmt->fetch()){
				return $row['max_vs_date'];
            }
		}
	}

    public static function getHnByPreOrderMasterId($pre_order_master_id){
		if($pre_order_master_id != null && trim($pre_order_master_id) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("select hn from ".DbConstant::KPHIS_DBNAME.".ipd_pre_order_master where pre_order_master_id=:pre_order_master_id");
			$stmt->execute(array('pre_order_master_id'=>$pre_order_master_id));
			if ($row = $stmt->fetch()){
				return $row['hn'];
            }
		}
	}

	public static function getVnByOpdErOrderMasterId($opd_er_order_master_id){
		if($opd_er_order_master_id != null && trim($opd_er_order_master_id) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("select vn from ".DbConstant::KPHIS_DBNAME.".opd_er_order_master where opd_er_order_master_id=:opd_er_order_master_id");
			$stmt->execute(array('opd_er_order_master_id'=>$opd_er_order_master_id));
			if ($row = $stmt->fetch()){
				return $row['vn'];
            }
		}
	}

    public static function getDoctorName($code){
		if($code != null && trim($code) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("select `name` from ".DbConstant::HOSXP_DBNAME.".doctor where `code` = :code");
			$stmt->execute(array('code'=>$code));
			if ($row = $stmt->fetch()){
				return $row['name'];
            }
		}
	}

	public static function isTodayNotPassDchDate($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT not(dchdate is not null and date(now()) > dchdate) as isTodayNotPassDchDate FROM ".DbConstant::HOSXP_DBNAME.".ipt WHERE an=:an");
			$stmt->execute(array('an'=>$an));
			if ($row = $stmt->fetch()){
				return $row['isTodayNotPassDchDate'];
            }
			return false;
		}
		return false;
	}

	public static function isNowPass24HrsFromOpdErDischarge($opd_er_order_master_id){
		if($opd_er_order_master_id != null && trim($opd_er_order_master_id) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT if(om.discharge_date is null OR om.discharge_time is null,false,if((DATE_ADD(now(),INTERVAL -1 DAY) > concat(om.discharge_date,' ',om.discharge_time)),true,false)) as pass_24_hour_from_dch
									FROM ".DbConstant::KPHIS_DBNAME.".opd_er_order_master om
									WHERE opd_er_order_master_id=:opd_er_order_master_id");
			$stmt->execute(array('opd_er_order_master_id'=>$opd_er_order_master_id));
			if ($row = $stmt->fetch()){
				return $row['pass_24_hour_from_dch'];
            }
			return false;
		}
		return false;
	}

	public static function getFromOpdErDischarge($opd_er_order_master_id){
		if($opd_er_order_master_id != null && trim($opd_er_order_master_id) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT if(om.discharge_date is null OR om.discharge_time is null,false,true) as opd_er_from_dch
									FROM ".DbConstant::KPHIS_DBNAME.".opd_er_order_master om
									WHERE opd_er_order_master_id=:opd_er_order_master_id");
			$stmt->execute(array('opd_er_order_master_id'=>$opd_er_order_master_id));
			if ($row = $stmt->fetch()){
				return $row['opd_er_from_dch'];
            }
			return false;
		}
		return false;
	}

	public static function getDateTimeFromOpdErDischarge($opd_er_order_master_id){
		if($opd_er_order_master_id != null && trim($opd_er_order_master_id) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT om.discharge_date, om.discharge_time
									FROM ".DbConstant::KPHIS_DBNAME.".opd_er_order_master om
									WHERE opd_er_order_master_id=:opd_er_order_master_id");
			$stmt->execute(array('opd_er_order_master_id'=>$opd_er_order_master_id));
			$rows = array();
			while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$rows[] = $r;
			}
			return $rows;
		}
	}

	public static function getOrderDateByOpdErOrderMasterId($opd_er_order_master_id){
		if($opd_er_order_master_id != null && trim($opd_er_order_master_id) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT order_date FROM ".DbConstant::KPHIS_DBNAME.".opd_er_order_master where opd_er_order_master_id=:opd_er_order_master_id");
			$stmt->execute(array('opd_er_order_master_id'=>$opd_er_order_master_id));
			if ($row = $stmt->fetch()){
				return $row['order_date'];
            }
		}
	}

	public static function getDbTodayDate(){
		$conn = DbUtils::get_hosxp_connection();
		$stmt = $conn->prepare("SELECT date(now()) as todayDate");
		if ($row = $stmt->fetch()){
			return $row['todayDate'];
		}
	}

	/**
	 * $dateToCheck string 'yyyy-mm-dd' or mysql supported date format
	 */
	public static function isDbTodayDate($dateToCheck){
		if($dateToCheck != null && trim($dateToCheck) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT date(now()) = :dateToCheck as isDbTodayDate");
			$stmt->execute(array('dateToCheck'=>$dateToCheck));
			if ($row = $stmt->fetch()){
				return $row['isDbTodayDate'];
            }
			return false;
		}
		return false;
	}

	public static function checkForeignKeyUsage($tablename,$fieldname,$value){
		if($value != null && trim($value) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT count(*) as cnt FROM  $tablename WHERE $fieldname = :value ");
			$stmt->execute(array('value'=>$value));
			if ($row = $stmt->fetch()){
				return $row['cnt'];
            }
		}
	}

	public static function sql_to_json($conn,$sql,$parameters){
		$stmt = $conn->prepare($sql);
		$stmt->execute($parameters);
		return KphisQueryUtils::stmt_to_json($stmt);
	}

	public static function stmt_to_json($stmt){
		$rows = array();
		while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$rows[] = $r;
		}
		return json_encode($rows, JSON_UNESCAPED_UNICODE );
	}

	public static function sql_to_array($conn,$sql,$parameters){
		$stmt = $conn->prepare($sql);
		$stmt->execute($parameters);
		$rows = array();
		while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$rows[] = $r;
		}
		return $rows;
	}

	//ข้อมูลการผ่าตัด 'ก่อนวันที่ Admit'
	public static function getOperationHis($hn,$an){
		if(($an != null && trim($an) != '') || ($hn != null && trim($hn) != '')){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT CONCAT(operation_list.enter_date,', ',operation_list.operation_name,', ',doctor.name,', ',:hospital_name) AS operation_list,
									timestampdiff(year,operation_list.enter_date,NOW()) AS or_concat_year,
									mod(timestampdiff(month,operation_list.enter_date,NOW()),12) AS or_concat_month,
									timestampdiff(day,date_add(operation_list.enter_date,interval (timestampdiff(month,operation_list.enter_date,NOW())) month),NOW()) AS or_concat_day
									FROM ".DbConstant::HOSXP_DBNAME.".operation_list
									LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".doctor ON doctor.code = operation_list.request_doctor
									LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".ipt ON ipt.an = operation_list.an
									WHERE operation_list.hn= :hn
									AND concat(operation_list.enter_date,' ',operation_list.enter_time) < (SELECT CONCAT(ipt.regdate,' ',ipt.regtime) FROM ".DbConstant::HOSXP_DBNAME.".ipt WHERE an=:an)");
			$stmt->execute(array('hn'=>$hn, 'an'=>$an,'hospital_name'=>DbConstant::KPHIS_HOSPITAL_NAME));
			$rows = array();
			while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$rows[] = $r;
			}
			return $rows;
		}
	}

	public static function getHosxpOpdAllergy($hn){
		$result = '';
		if($hn != null && trim($hn) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$sql_drug_allergy =  "select GROUP_CONCAT(concat(opd_allergy.agent)) as drugallergy
						from ".DbConstant::HOSXP_DBNAME.".opd_allergy
						where opd_allergy.hn = :hn /*and (opd_allergy.no_alert<>'Y' or opd_allergy.no_alert is null)*/
						order by display_order";
			$stmt_drug_allergy = $conn->prepare($sql_drug_allergy);
			$stmt_drug_allergy->execute(['hn'=>$hn]);
			while($r = $stmt_drug_allergy->fetch(PDO::FETCH_ASSOC)) {
				$result = $r['drugallergy'];
			}
		}
		return $result;
	}

	public static function getHosxpOpdAllergyWithSymptom($hn){
		$result = '';
		if($hn != null && trim($hn) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$sql_drug_allergy =  "select GROUP_CONCAT(concat(opd_allergy.agent,'=',if(opd_allergy.symptom is null,',',opd_allergy.symptom)/*,' [',if(note is null,',',note),']'*/)) as drugallergy
						from ".DbConstant::HOSXP_DBNAME.".opd_allergy
						where opd_allergy.hn = :hn /*and (opd_allergy.no_alert<>'Y' or opd_allergy.no_alert is null)*/
						order by display_order";
			$stmt_drug_allergy = $conn->prepare($sql_drug_allergy);
			$stmt_drug_allergy->execute(['hn'=>$hn]);
			while($r = $stmt_drug_allergy->fetch(PDO::FETCH_ASSOC)) {
				$result = $r['drugallergy'];
			}
		}
		return $result;
	}

	public static function getDrAdmissionNoteDrugAllergy($an, $with_symptom){
		$result = '';
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$sql_drug_allergy =  "select
						allergy_drug_history,
						allergy_drug_pharmacy_check_person,
						allergy_drug_pharmacy_check_datetime
						from ".DbConstant::KPHIS_DBNAME.".ipd_dr_admission_note
						where an = :an ";
			$stmt_drug_allergy = $conn->prepare($sql_drug_allergy);
			$stmt_drug_allergy->execute(['an'=>$an]);
			while($r = $stmt_drug_allergy->fetch(PDO::FETCH_ASSOC)) {
				if($r['allergy_drug_pharmacy_check_person'] == ""){
					$allergy_drug_history_array = explode(" ",$r['allergy_drug_history']);
					$y = 0;
					for ($x = 0; $x < (count($allergy_drug_history_array)-1)/2; $x++) {
						$result .= (($x>0) ? ',' : '');
						if($with_symptom){
							$result .= $allergy_drug_history_array[$y++].'='.$allergy_drug_history_array[$y++];
						} else {
							$result .= $allergy_drug_history_array[$y++];
							$y++;
						}
					}
				}
			}
		}
		return $result;
	}

	public static function getOpdErAllergyWithSymptom($opd_er_order_master_id){
		$result = '';
		if($opd_er_order_master_id != null && trim($opd_er_order_master_id) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$sql_drug_allergy =  "select GROUP_CONCAT(concat(er_allergy_history_agent,'=',if(er_allergy_history_symptom is null,',',er_allergy_history_symptom))) as drugallergy
			from ".DbConstant::KPHIS_DBNAME.".opd_er_allergy_history
			where opd_er_allergy_history.opd_er_order_master_id = :opd_er_order_master_id
			order by er_allergy_history_id";
			$stmt_drug_allergy = $conn->prepare($sql_drug_allergy);
			$stmt_drug_allergy->execute(['opd_er_order_master_id'=>$opd_er_order_master_id]);
			while($r = $stmt_drug_allergy->fetch(PDO::FETCH_ASSOC)) {
				$result = $r['drugallergy'];
			}
		}
		return $result;
	}

	public static function getOpdErAllergyWithSymptomByAn($an, $with_symptom){
		$result = '';
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			if($with_symptom){
				$sql_drug_allergy =  "select GROUP_CONCAT(concat(er_allergy_history_agent,'=',if(er_allergy_history_symptom is null,',',er_allergy_history_symptom))) as drugallergy
				from ".DbConstant::KPHIS_DBNAME.".opd_er_allergy_history
				join opd_er_order_master om on om.opd_er_order_master_id = opd_er_allergy_history.opd_er_order_master_id
				join ".DbConstant::HOSXP_DBNAME.".ipt on ipt.vn = om.vn
				where ipt.an = :an
				order by er_allergy_history_id";
			} else {
				$sql_drug_allergy =  "select GROUP_CONCAT(concat(er_allergy_history_agent)) as drugallergy
				from ".DbConstant::KPHIS_DBNAME.".opd_er_allergy_history
				join opd_er_order_master om on om.opd_er_order_master_id = opd_er_allergy_history.opd_er_order_master_id
				join ".DbConstant::HOSXP_DBNAME.".ipt on ipt.vn = om.vn
				where ipt.an = :an
				order by er_allergy_history_id";
			}
			$stmt_drug_allergy = $conn->prepare($sql_drug_allergy);
			$stmt_drug_allergy->execute(['an'=>$an]);
			while($r = $stmt_drug_allergy->fetch(PDO::FETCH_ASSOC)) {
				$result = $r['drugallergy'];
			}
		}
		return $result;
	}

	public static function getOpdErAllergyListByAn($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
				$sql_drug_allergy =  "select er_allergy_history_agent,er_allergy_history_symptom
				from ".DbConstant::KPHIS_DBNAME.".opd_er_allergy_history
				join opd_er_order_master om on om.opd_er_order_master_id = opd_er_allergy_history.opd_er_order_master_id
				join ".DbConstant::HOSXP_DBNAME.".ipt on ipt.vn = om.vn
				where ipt.an = :an
				order by er_allergy_history_id";
			$stmt_drug_allergy = $conn->prepare($sql_drug_allergy);
			$stmt_drug_allergy->execute(['an'=>$an]);
			$rows = array();
			while($r = $stmt_drug_allergy->fetch(PDO::FETCH_ASSOC)) {
				$rows[] = $r;
			}
			return $rows;
		}
	}

	//ข้อมูลการผ่าตัด 'Admit >> WHERE = an'
	public static function getOperationAdmit($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT CONCAT(ifnull(operation_list.enter_date,''),', ',ifnull(operation_list.operation_name,''),', ',doctor.name,', ':hospital_name) AS operation_list,
									timestampdiff(year,operation_list.enter_date,NOW()) AS or_concat_year,
									mod(timestampdiff(month,operation_list.enter_date,NOW()),12) AS or_concat_month,
									timestampdiff(day,date_add(operation_list.enter_date,interval (timestampdiff(month,operation_list.enter_date,NOW())) month),NOW()) AS or_concat_day
									FROM ".DbConstant::HOSXP_DBNAME.".operation_list
									LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".doctor ON doctor.code = operation_list.request_doctor
									LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".ipt ON ipt.an = operation_list.an
									WHERE operation_list.an= :an AND operation_list.status_id = 3
									ORDER BY operation_list.enter_date,operation_list.enter_time");
			$stmt->execute(array('an'=>$an,'hospital_name'=>DbConstant::HOSPITAL_NAME));
			$rows = array();
			while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$rows[] = $r;
			}
			return $rows;
		}
	}

	//เช็คค่าจาก loginname ว่าเป็นแพทย์หรือไม่ (ถ้าใช่จะแสดง code, ชื่อ-สกุล, เลข ว.)
	// public static function getShowDataDoctor($loginname){
	// 	if($loginname != null || $loginname != ""){
	// 		$conn = DbUtils::get_hosxp_connection();
	// 		$stmt = $conn->prepare("SELECT doctor.code, opduser.name,  doctor.regist_no
	// 								FROM ".DbConstant::HOSXP_DBNAME.".opduser
	// 								LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".doctor ON opduser.doctorcode = doctor.code
	// 								WHERE opduser.loginname = :loginname
	// 								-- AND doctor.provider_type_code = '01'
	// 								AND doctor.licenseno is not null
	// 								-- AND trim(licenseno) <> ''
	// 								-- AND doctor.licenseno <> '-99999'
	// 								");
	// 		$stmt->execute(array('loginname'=>$loginname));
	// 		$rows = array();
	// 		while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
	// 			$rows[] = $r;
	// 		}
	// 		return $rows;
	// 	}
	// }

	public static function getDocumentAddmissionNurse($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data  FROM ".DbConstant::KPHIS_DBNAME.".ipd_nurse_admission_note WHERE an = :an");
			$stmt->execute(array('an'=>$an));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}

	}

	
	public static function getDocumentAddmissionDoctor($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data  FROM ".DbConstant::KPHIS_DBNAME.".ipd_dr_admission_note WHERE an = :an");
			$stmt->execute(array('an'=>$an));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}

	public static function getDocumentSummary($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data  FROM ".DbConstant::KPHIS_DBNAME.".ipd_summary WHERE an = :an");
			$stmt->execute(array('an'=>$an));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}

	public static function getDocumentNurseSummary($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data  FROM ".DbConstant::KPHIS_DBNAME.".ipd_nurse_summary WHERE an = :an");
			$stmt->execute(array('an'=>$an));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}


	public static function getDocumentOrder($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data  FROM ".DbConstant::KPHIS_DBNAME.".ipd_order WHERE an = :an");
			$stmt->execute(array('an'=>$an));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}

	public static function getDocumentOrderProgressNote($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data  FROM ".DbConstant::KPHIS_DBNAME.".ipd_progress_note WHERE an = :an");
			$stmt->execute(array('an'=>$an));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}
	public static function getDocumentFocusList($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data  FROM ".DbConstant::KPHIS_DBNAME.".ipd_focus_list WHERE an = :an");
			$stmt->execute(array('an'=>$an));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}
	public static function getDocumentFocusNote($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data  FROM ".DbConstant::KPHIS_DBNAME.".ipd_focus_note WHERE an = :an");
			$stmt->execute(array('an'=>$an));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}
	public static function getDocumentVitalSign($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data  FROM ".DbConstant::KPHIS_DBNAME.".ipd_vs_vital_sign WHERE an = :an");
			$stmt->execute(array('an'=>$an));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}
	public static function getDocumentIO($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data  FROM ".DbConstant::KPHIS_DBNAME.".ipd_io WHERE an = :an");
			$stmt->execute(array('an'=>$an));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}
	public static function getDocumentConsult($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data  FROM ".DbConstant::KPHIS_DBNAME.".ipd_dr_consult WHERE an = :an");
			$stmt->execute(array('an'=>$an));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}
	public static function getDocumentOperative($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT count(*) count_data
									from ".DbConstant::HOSXP_DBNAME.".operation_list ol
									left outer join ".DbConstant::HOSXP_DBNAME.".an_stat an1 on an1.an=ol.an
									left outer join ".DbConstant::HOSXP_DBNAME.".ovst ov on ov.an=ol.an
									left outer join ".DbConstant::HOSXP_DBNAME.".vn_stat v on v.vn=ov.vn
									left outer join ".DbConstant::HOSXP_DBNAME.".patient pt on  pt.hn=ol.hn
									where ov.an = :an and ol.status_id=3");

									// inner join ".DbConstant::HOSXP_DBNAME.".universal_head uh on if(ol.patient_department='OPD',ol.vn,ol.an)=uh.vn
									// left outer join ".DbConstant::HOSXP_DBNAME.".universal_form uf on uf.universal_form_id=uh.universal_form_id and uf.universal_form_id=4
			$stmt->execute(array('an'=>$an));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}
	public static function getDocumentIndex($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data  FROM ".DbConstant::KPHIS_DBNAME.".ipd_nurse_index_plan WHERE an = :an");
			$stmt->execute(array('an'=>$an));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}
	public static function getDataDocumentAddmissionNurse($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT  create_datetime AS create_datetimeAddmissionNurse, update_datetime AS update_datetimeAddmissionNurse FROM ".DbConstant::KPHIS_DBNAME.".ipd_nurse_admission_note WHERE an = :an");
			$stmt->execute(array('an'=>$an));
			$rows = array();
			while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$rows[] = $r;
			}
			return $rows;
		}
	}
	public static function getDataDocumentAddmissionDoctor($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT  create_datetime AS create_datetimeAddmissionDoctor, update_datetime AS update_datetimeAddmissionDoctor FROM ".DbConstant::KPHIS_DBNAME.".ipd_dr_admission_note WHERE an = :an");
			$stmt->execute(array('an'=>$an));
			$rows = array();
			while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$rows[] = $r;
			}
			return $rows;
		}
	}

	public static function getDataDocumentSummary($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT  create_datetime AS create_datetimeSummary, update_datetime AS update_datetimeSummary FROM ".DbConstant::KPHIS_DBNAME.".ipd_summary WHERE an = :an");
			$stmt->execute(array('an'=>$an));
			$rows = array();
			while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$rows[] = $r;
			}
			return $rows;
		}
	}

	public static function getDocumentLab($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data
									from ".DbConstant::HOSXP_DBNAME.".lab_head h
									inner join ".DbConstant::HOSXP_DBNAME.".lab_order o on h.lab_order_number=o.lab_order_number
									where h.vn=:an and o.confirm = 'Y'
									-- group by o.confirm = 'Y'
									");
			$stmt->execute(array('an'=>$an));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}
	public static function getDocumentOPDLab($vn){
		if($vn != null && trim($vn) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data
									from ".DbConstant::HOSXP_DBNAME.".lab_head h
									inner join ".DbConstant::HOSXP_DBNAME.".lab_order o on h.lab_order_number=o.lab_order_number
									where h.vn=:vn and o.confirm = 'Y'
									-- group by o.confirm = 'Y'
									");
			$stmt->execute(array('vn'=>$vn));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}
	public static function getDocumentMedReconciliation($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT count(*) as count_data FROM ".DbConstant::KPHIS_DBNAME.".ipd_med_reconciliation WHERE an=:an");
			$stmt->execute(array('an'=>$an));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}

	public static function getDocumentMedReconciliationHOSXP($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT count(*) as count_data from ".DbConstant::HOSXP_DBNAME.".medication_reconciliation_detail t2
									join  ".DbConstant::HOSXP_DBNAME.".medication_reconciliation t1 on t1.medication_reconciliation_id = t2.medication_reconciliation_id
									where t1.an=:an
									order by medication_reconciliation_detail_id");
			$stmt->execute(array('an'=>$an));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}

	public static function checkPatienAge($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT age_y FROM ".DbConstant::HOSXP_DBNAME.".an_stat WHERE an = :an LIMIT 1");
			$stmt->execute(array('an'=>$an));
			$row = $stmt->fetch();
			return $row['age_y'];
		}
	}
	public static function checkPatienAgeByVn($vn){
		if($vn != null && trim($vn) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT age_y FROM ".DbConstant::HOSXP_DBNAME.".vn_stat WHERE vn = :vn LIMIT 1");
			$stmt->execute(array('vn'=>$vn));
			$row = $stmt->fetch();
			return $row['age_y'];
		}
	}
	public static function getVnFromOpdErMasterId($opd_er_order_master_id){
		if($opd_er_order_master_id != null && trim($opd_er_order_master_id) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT vn FROM ".DbConstant::KPHIS_DBNAME.".opd_er_order_master WHERE opd_er_order_master_id = :opd_er_order_master_id LIMIT 1");
			$stmt->execute(array('opd_er_order_master_id'=>$opd_er_order_master_id));
			$row = $stmt->fetch();
			return $row['vn'];
		}
	}
	public static function getHnFromOpdErMasterId($opd_er_order_master_id){
		if($opd_er_order_master_id != null && trim($opd_er_order_master_id) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT ovst.hn FROM ".DbConstant::KPHIS_DBNAME.".opd_er_order_master left outer join ".DbConstant::HOSXP_DBNAME.".ovst on opd_er_order_master.vn = ovst.vn WHERE opd_er_order_master_id = :opd_er_order_master_id LIMIT 1");
			$stmt->execute(array('opd_er_order_master_id'=>$opd_er_order_master_id));
			$row = $stmt->fetch();
			return $row['hn'];
		}
	}
	public static function getDataHosxpWard($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT ward FROM ".DbConstant::HOSXP_DBNAME.".ipt WHERE an=:an LIMIT 1");
			$stmt->execute(array('an'=>$an));
			$row = $stmt->fetch();
			return $row['ward'];
		}
	}
	public static function getVstdate_timeByVn($vn){
		if($vn != null && trim($vn) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT CONCAT(vstdate,' ',vsttime) as vstdate_time FROM ".DbConstant::HOSXP_DBNAME.".opdscreen WHERE vn=:vn");
			$stmt->execute(array('vn'=>$vn));
			if ($row = $stmt->fetch()){
				return $row['vstdate_time'];
            }
		}
	}

	public static function canChangeWardPasscode($loginname){
		if($loginname != null && trim($loginname) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT *
									FROM ".DbConstant::KPHIS_DBNAME.".ipd_ward_passcode_user
									WHERE loginname=:loginname");
			$stmt->execute(array('loginname'=>$loginname));
			if ($stmt->fetch()){
				return true;
            }
			return false;
		}
		return false;
	}

	public static function getDocumentAnesthetic($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT count(*) as count_data
									from ".DbConstant::HOSXP_DBNAME.".operation_list ol
									left outer join ".DbConstant::HOSXP_DBNAME.".operation_anes os on os.operation_id=ol.operation_id
									where ol.an=:an
									and os.operation_id is not NULL");
			$stmt->execute(array('an'=>$an));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}

	public static function getDocumentXray($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT count(*) as count_data
									from ".DbConstant::HOSXP_DBNAME.".xray_report x
									left outer join ".DbConstant::HOSXP_DBNAME.".xray_items xi on xi.xray_items_code=x.xray_items_code
									where x.an =:an
									and xi.xray_items_group in (1)");
			$stmt->execute(array('an'=>$an));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}

	public static function getDocumentCTscan($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT count(*) as count_data
									from ".DbConstant::HOSXP_DBNAME.".xray_report x
									left outer join ".DbConstant::HOSXP_DBNAME.".xray_items xi on xi.xray_items_code=x.xray_items_code
									where x.an =:an
									and xi.xray_items_group in (3)");
			$stmt->execute(array('an'=>$an));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}

	public static function getDocumentMRI($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT count(*) as count_data
									from ".DbConstant::HOSXP_DBNAME.".xray_report x
									left outer join ".DbConstant::HOSXP_DBNAME.".xray_items xi on xi.xray_items_code=x.xray_items_code
									where x.an =:an
									and xi.xray_items_group in (4)");
			$stmt->execute(array('an'=>$an));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}

	public static function canAccessTable($table_schema, $table_name){
		if($table_schema != null && trim($table_schema) != '' && $table_name != null && trim($table_name) != ''){
			$conn = DbUtils::get_hosxp_connection();

			$check_stmt = $conn->prepare("  SELECT TABLE_NAME
											FROM information_schema.TABLES
											WHERE (TABLE_SCHEMA = :table_schema) AND (TABLE_NAME = :table_name)");
			$check_stmt->execute(array('table_schema'=>$table_schema, 'table_name'=>$table_name));

			if($check_stmt->fetch(PDO::FETCH_ASSOC)) {
				return true;
			}

			return false;
		}
	}

	public static function getCovidLatestLabResult($cid,$passport_no){
		if((($cid != null && trim($cid) != '')
			|| ($passport_no != null && trim($passport_no) != ''))
			&& (KphisQueryUtils::canAccessTable('kph_covid', 'lab_result_covid'))){
			$conn = DbUtils::get_hosxp_connection();
			$sql = "SELECT sent_date, `status`, results, results_date, approve_date, approve_results
			FROM kph_covid.lab_result_covid
			WHERE false ";
			if($cid != null && trim($cid) != ''){
				$sql .= " OR cid=:cid ";
				// $sql .= " OR passport=:cid ";
				$query_parameter['cid'] = $cid;
			}
			if($passport_no != null && trim($passport_no) != ''){
				$sql .= " OR passport=:passport_no ";
				$query_parameter['passport_no'] = $passport_no;
			}
			$sql .= " ORDER BY sent_date DESC LIMIT 1";
			$stmt = $conn->prepare($sql);

			$stmt->execute($query_parameter);
			$rows = array();
			while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$rows[] = $r;
			}
			return $rows;
		}
	}

	public static function getCovidLabResults($cid,$passport_no){
		if((($cid != null && trim($cid) != '')
			|| ($passport_no != null && trim($passport_no) != ''))
			&& (KphisQueryUtils::canAccessTable('kph_covid', 'lab_result_covid'))){
			$conn = DbUtils::get_hosxp_connection();
			// $sql = "SELECT sent_date,status,results,ct_1,ct_2,ct_3,gene_code_1,gene_code_2,gene_code_3
			$sql = "SELECT *
			FROM kph_covid.lab_result_covid
			WHERE false ";
			if($cid != null && trim($cid) != ''){
				$sql .= " OR cid=:cid ";
				// $sql .= " OR passport=:cid ";
				$query_parameter['cid'] = $cid;
			}
			if($passport_no != null && trim($passport_no) != ''){
				$sql .= " OR passport=:passport_no ";
				$query_parameter['passport_no'] = $passport_no;
			}
			$sql .= " ORDER BY sent_date DESC ";
			$stmt = $conn->prepare($sql);

			$stmt->execute($query_parameter);
			$rows = array();
			while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$rows[] = $r;
			}
			return $rows;
		}
	}
	public static function getDocumentERFromOpdErMasterId($vn){
		if($vn != null && trim($vn) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT count(*) as count_data
									FROM ".DbConstant::KPHIS_DBNAME.".opd_er_order_master
									WHERE vn = :vn
									and (delete_flag is null or delete_flag <> 'Y')
									order by opd_er_order_master_id");
			$stmt->execute(array('vn'=>$vn));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}
	public static function getDocumentERDoctorTrauma($opd_er_order_master_id){
		if($opd_er_order_master_id != null && trim($opd_er_order_master_id) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data
									FROM ".DbConstant::KPHIS_DBNAME.".opd_er_dr_pe
									WHERE opd_er_order_master_id = :opd_er_order_master_id");
			$stmt->execute(array('opd_er_order_master_id'=>$opd_er_order_master_id));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}
	public static function getDocumentERNurseScreening($opd_er_order_master_id){
		if($opd_er_order_master_id != null && trim($opd_er_order_master_id) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data
									FROM ".DbConstant::KPHIS_DBNAME.".opd_er_nurse_screening
									WHERE opd_er_order_master_id = :opd_er_order_master_id");
			$stmt->execute(array('opd_er_order_master_id'=>$opd_er_order_master_id));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}
	public static function getDocumentERConsult($opd_er_order_master_id){
		if($opd_er_order_master_id != null && trim($opd_er_order_master_id) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data
									FROM ".DbConstant::KPHIS_DBNAME.".opd_er_consult
									WHERE opd_er_order_master_id = :opd_er_order_master_id");
			$stmt->execute(array('opd_er_order_master_id'=>$opd_er_order_master_id));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}
	public static function getDocumentERSetFT($opd_er_order_master_id){
		if($opd_er_order_master_id != null && trim($opd_er_order_master_id) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data
									FROM ".DbConstant::KPHIS_DBNAME.".opd_er_set_fast_track
									WHERE opd_er_order_master_id = :opd_er_order_master_id");
			$stmt->execute(array('opd_er_order_master_id'=>$opd_er_order_master_id));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}
	public static function getDocumentEROrder($opd_er_order_master_id){
		if($opd_er_order_master_id != null && trim($opd_er_order_master_id) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data
									FROM ".DbConstant::KPHIS_DBNAME.".opd_er_order
									WHERE opd_er_order_master_id = :opd_er_order_master_id");
			$stmt->execute(array('opd_er_order_master_id'=>$opd_er_order_master_id));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}
	public static function getDocumentERNurseIndexPlan($opd_er_order_master_id){
		if($opd_er_order_master_id != null && trim($opd_er_order_master_id) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data
									FROM ".DbConstant::KPHIS_DBNAME.".opd_er_nurse_index_plan
									WHERE opd_er_order_master_id = :opd_er_order_master_id");
			$stmt->execute(array('opd_er_order_master_id'=>$opd_er_order_master_id));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}
	public static function getDocumentERVitalSign($opd_er_order_master_id){
		if($opd_er_order_master_id != null && trim($opd_er_order_master_id) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data
									FROM ".DbConstant::KPHIS_DBNAME.".opd_er_vs_vital_sign
									WHERE opd_er_order_master_id = :opd_er_order_master_id");
			$stmt->execute(array('opd_er_order_master_id'=>$opd_er_order_master_id));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}
	public static function getDocumentERIO($opd_er_order_master_id){
		if($opd_er_order_master_id != null && trim($opd_er_order_master_id) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data
									FROM ".DbConstant::KPHIS_DBNAME.".opd_er_io
									WHERE opd_er_order_master_id = :opd_er_order_master_id");
			$stmt->execute(array('opd_er_order_master_id'=>$opd_er_order_master_id));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}
	public static function getDocumentERFocusList($opd_er_order_master_id){
		if($opd_er_order_master_id != null && trim($opd_er_order_master_id) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data
									FROM ".DbConstant::KPHIS_DBNAME.".opd_er_focus_list
									WHERE opd_er_order_master_id = :opd_er_order_master_id");
			$stmt->execute(array('opd_er_order_master_id'=>$opd_er_order_master_id));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}
	public static function getDocumentERFocusNote($opd_er_order_master_id){
		if($opd_er_order_master_id != null && trim($opd_er_order_master_id) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data
									FROM ".DbConstant::KPHIS_DBNAME.".opd_er_focus_note
									WHERE opd_er_order_master_id = :opd_er_order_master_id");
			$stmt->execute(array('opd_er_order_master_id'=>$opd_er_order_master_id));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}
	public static function getIPDCheckRowSmp($smp_id){
		if($smp_id != null && trim($smp_id) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data FROM ".DbConstant::KPHIS_DBNAME.".ipd_focus_list WHERE smp_id = :smp_id");
			$stmt->execute(array('smp_id'=>$smp_id));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}
	public static function getOPDERCheckRowSmp($smp_id){
		if($smp_id != null && trim($smp_id) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data FROM ".DbConstant::KPHIS_DBNAME.".opd_er_focus_list WHERE smp_id = :smp_id");
			$stmt->execute(array('smp_id'=>$smp_id));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}
	public static function getIPDCheckRowFocus($focus_id){
		if($focus_id != null && trim($focus_id) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data FROM ".DbConstant::KPHIS_DBNAME.".ipd_focus_list WHERE focus_id = :focus_id");
			$stmt->execute(array('focus_id'=>$focus_id));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}
	public static function getOPDERCheckRowFocus($focus_id){
		if($focus_id != null && trim($focus_id) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data FROM ".DbConstant::KPHIS_DBNAME.".opd_er_focus_list WHERE focus_id = :focus_id");
			$stmt->execute(array('focus_id'=>$focus_id));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}
	public static function getIPDCheckRowGoal($goal_id){
		if($goal_id != null && trim($goal_id) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data FROM ".DbConstant::KPHIS_DBNAME.".ipd_focus_list_goal_item WHERE goal_id = :goal_id");
			$stmt->execute(array('goal_id'=>$goal_id));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}
	public static function getOPDERCheckRowGoal($goal_id){
		if($goal_id != null && trim($goal_id) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data FROM ".DbConstant::KPHIS_DBNAME.".opd_er_focus_list_goal_item WHERE goal_id = :goal_id");
			$stmt->execute(array('goal_id'=>$goal_id));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}
	public static function getIPDCheckRowIntvt($intvt_id){
		if($intvt_id != null && trim($intvt_id) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data FROM ".DbConstant::KPHIS_DBNAME.".ipd_focus_note_intvt_item WHERE intvt_id = :intvt_id");
			$stmt->execute(array('intvt_id'=>$intvt_id));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}
	public static function getOPDERCheckRowIntvt($intvt_id){
		if($intvt_id != null && trim($intvt_id) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data FROM ".DbConstant::KPHIS_DBNAME.".opd_er_focus_note_intvt_item WHERE intvt_id = :intvt_id");
			$stmt->execute(array('intvt_id'=>$intvt_id));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}
	public static function getDocumentERAllergyHistory($opd_er_order_master_id){//แพ้ยา ER
		if($opd_er_order_master_id != null && trim($opd_er_order_master_id) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data
									FROM ".DbConstant::KPHIS_DBNAME.".opd_er_allergy_history
									WHERE opd_er_order_master_id = :opd_er_order_master_id");
			$stmt->execute(array('opd_er_order_master_id'=>$opd_er_order_master_id));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}

	
	public static function getDocumentLaborReport2($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data  FROM ".DbConstant::KPHIS_DBNAME.".prs_labor_report2 WHERE an = :an");
			$stmt->execute(array('an'=>$an));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}

	public static function getDataLaborReport2($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT  create_datetime AS create_, update_datetime AS update_ FROM ".DbConstant::KPHIS_DBNAME.".prs_labor_report2 WHERE an = :an");
			$stmt->execute(array('an'=>$an));
			$rows = array();
			while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$rows[] = $r;
			}
			return $rows;
		}
	}


	public static function getPrsDocumentTab($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data  FROM ".DbConstant::KPHIS_DBNAME.".prs_document_tab");
			$stmt->execute(array('an'=>$an));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}



	}

public static function getMainReportCheck($an,$table){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data  FROM ".DbConstant::KPHIS_DBNAME.".:table WHERE an = :an");
			$stmt->execute(array('an'=>$an));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}

	}




}