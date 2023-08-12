<?php
//require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/DbConstant.php';
require_once __DIR__ . '/StringUtils.php';
require_once __DIR__ . '/DbUtils.php';

class ReportQueryUtils {

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
   
public static function getMainReportCheck($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data  FROM ".DbConstant::KPHIS_DBNAME.".:table WHERE an = :an");
			$stmt->execute(array('an'=>$an));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}

	}

	public static function getDocumentOrComplication($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data  FROM ".DbConstant::KPHIS_DBNAME.".prs_or_complication WHERE an = :an");
			$stmt->execute(array('an'=>$an));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}

	public static function getDataOrComplication($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT  create_datetime, update_datetime FROM ".DbConstant::KPHIS_DBNAME.".prs_or_complication WHERE an = :an");
			$stmt->execute(array('an'=>$an));
			$rows = array();
			while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$rows[] = $r;
			}
			return $rows;
		}
	}



	public static function getDocumentLaborReport1($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data  FROM ".DbConstant::KPHIS_DBNAME.".prs_labor_report1 WHERE an = :an");
			$stmt->execute(array('an'=>$an));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}

	public static function getDataLaborReport1($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT  create_datetime, update_datetime  FROM ".DbConstant::KPHIS_DBNAME.".prs_labor_report1 WHERE an = :an");
			$stmt->execute(array('an'=>$an));
			$rows = array();
			while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$rows[] = $r;
			}
			return $rows;
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
			$stmt = $conn->prepare("SELECT  create_datetime, update_datetime  FROM ".DbConstant::KPHIS_DBNAME.".prs_labor_report2 WHERE an = :an");
			$stmt->execute(array('an'=>$an));
			$rows = array();
			while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$rows[] = $r;
			}
			return $rows;
		}
	}



}