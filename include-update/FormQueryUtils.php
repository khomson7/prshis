<?php
//require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/DbConstant.php';
require_once __DIR__ . '/StringUtils.php';
require_once __DIR__ . '/DbUtils.php';

class FormQueryUtils {

	public static function getDocumentDue($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data  FROM ".DbConstant::KPHIS_DBNAME.".prs_due_check WHERE an = :an");
			$stmt->execute(array('an'=>$an));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}

	}


	public static function getDataDue($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT  pd.id as ids,pd.create_datetime AS create_datetimeAddmissionNurse, pd.update_datetime AS update_datetimeAddmissionNurse 
			,d.name as drugname,case when (physician_approved is null or physician_approved = '') then '-'
			else physician_approved end
			 as physicain_Approved,case when TIMESTAMPDIFF(day,date(create_datetime),CURDATE()) > 3 then '1'
ELSE '0' end as check_
			FROM ".DbConstant::KPHIS_DBNAME.".prs_due_check pd
			left join ".DbConstant::HOSXP_DBNAME.".drugitems d on d.icode = pd.icode
			WHERE pd.an = :an  and (pd.cancle_status is null or pd.cancle_status = '') order by pd.id desc");
			$stmt->execute(array('an'=>$an));
			$rows = array();
			while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$rows[] = $r;
			}
			return $rows;
		}
	}

	public static function getDocumentDue2($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data  FROM ".DbConstant::KPHIS_DBNAME.".prs_due_check WHERE an = :an");
			$stmt->execute(array('an'=>$an));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}

	}

	public static function getDataDue2($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT  pd.id as ids,pd.create_datetime AS create_datetimeAddmissionNurse, pd.update_datetime AS update_datetimeAddmissionNurse 
			,d.name as drugname,case when (physician_approved is null or physician_approved = '') then '-'
			else physician_approved end
			 as physicain_Approved,case when TIMESTAMPDIFF(day,date(create_datetime),CURDATE()) > 3 then '1'
ELSE '0' end as check_
			FROM ".DbConstant::KPHIS_DBNAME.".prs_due_check pd
			left join ".DbConstant::HOSXP_DBNAME.".drugitems d on d.icode = pd.icode
			WHERE pd.an = :an  /*and (pd.cancle_status is null or pd.cancle_status = '') */ order by pd.id desc");
			$stmt->execute(array('an'=>$an));
			$rows = array();
			while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$rows[] = $r;
			}
			return $rows;
		}
	}


	public static function getDocumentIcu1Form($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data  FROM ".DbConstant::KPHIS_DBNAME.".prs_icu_form WHERE an = :an");
			$stmt->execute(array('an'=>$an));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}

	}


	public static function getDataIcu1Form($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT  id as ids,create_datetime AS create_datetimeAddmissionNurse, update_datetime AS update_datetimeAddmissionNurse FROM ".DbConstant::KPHIS_DBNAME.".prs_icu_form WHERE an = :an");
			$stmt->execute(array('an'=>$an));
			$rows = array();
			while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$rows[] = $r;
			}
			return $rows;
		}
	}


	public static function getDocumentMental2Form($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data  FROM ".DbConstant::KPHIS_DBNAME.".prs_mental_health2 WHERE an = :an");
			$stmt->execute(array('an'=>$an));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}

	}


	public static function getDataMental2Form($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT  id as ids,create_datetime AS create_datetimeAddmissionNurse, update_datetime AS update_datetimeAddmissionNurse,total_sum as total_Sum,date_alert as date_alert FROM ".DbConstant::KPHIS_DBNAME.".prs_mental_health2 WHERE an = :an order by create_datetime desc");
			$stmt->execute(array('an'=>$an));
			$rows = array();
			while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$rows[] = $r;
			}
			return $rows;
		}
	}


	public static function getDocumentMental3Form($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data  FROM ".DbConstant::KPHIS_DBNAME.".prs_mental_health3 WHERE an = :an");
			$stmt->execute(array('an'=>$an));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}

	}


	public static function getDataMental3Form($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT  id as ids,create_datetime AS create_datetimeAddmissionNurse, update_datetime AS update_datetimeAddmissionNurse,variation1 AS variation_1,variation2,variation3,variation4 
			FROM ".DbConstant::KPHIS_DBNAME.".prs_mental_health3 WHERE an = :an order by create_datetime desc");
			$stmt->execute(array('an'=>$an));
			$rows = array();
			while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$rows[] = $r;
			}
			return $rows;
		}
	}


	public static function getDocumentBedscoreForm($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data  FROM ".DbConstant::KPHIS_DBNAME.".prs_bedsores WHERE an = :an");
			$stmt->execute(array('an'=>$an));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}

	}

	public static function getDataBedscoreForm($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT  id as ids,create_datetime AS create_datetimeAddmissionNurse, update_datetime AS update_datetimeAddmissionNurse
			,score as my_score
			FROM ".DbConstant::KPHIS_DBNAME.".prs_bedsores WHERE an = :an order by create_datetime desc");
			$stmt->execute(array('an'=>$an));
			$rows = array();
			while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$rows[] = $r;
			}
			return $rows;
		}
	}


	public static function getDocumentFellDownForm($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data  FROM ".DbConstant::KPHIS_DBNAME.".prs_felldown WHERE an = :an");
			$stmt->execute(array('an'=>$an));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}

	}

	public static function getDataFellDownForm($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT  id as ids,create_datetime AS create_datetimeAddmissionNurse, update_datetime AS update_datetimeAddmissionNurse
			,score as my_score
			FROM ".DbConstant::KPHIS_DBNAME.".prs_felldown WHERE an = :an order by create_datetime desc");
			$stmt->execute(array('an'=>$an));
			$rows = array();
			while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$rows[] = $r;
			}
			return $rows;
		}
	}

	public static function getDocumentRehabProForm($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data  FROM ".DbConstant::KPHIS_DBNAME.".prs_rehab_progression WHERE an = :an");
			$stmt->execute(array('an'=>$an));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}

	}

	public static function getDataRehabProForm($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT  id as ids,create_datetime AS create_datetimeAddmissionNurse, update_datetime AS update_datetimeAddmissionNurse		
			FROM ".DbConstant::KPHIS_DBNAME.".prs_rehab_progression WHERE an = :an order by create_datetime desc");
			$stmt->execute(array('an'=>$an));
			$rows = array();
			while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$rows[] = $r;
			}
			return $rows;
		}
	}

	public static function getDocumentPrsProgressNoteEdit($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT COUNT(*) AS count_data  FROM ".DbConstant::KPHIS_DBNAME.".ipd_progress_note_item WHERE an = :an
			and an in (SELECT an from ".DbConstant::KPHIS_DBNAME.".prs_an_on_edit where status_ = 'Y')");
			$stmt->execute(array('an'=>$an));
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}

	}


	public static function getDataPrsProgressNoteEdit($an){
		if($an != null && trim($an) != ''){
			$conn = DbUtils::get_hosxp_connection();
			$stmt = $conn->prepare("SELECT  progress_note_item_id as ids,progress_note_item_detail as prodetail,create_datetime AS create_datetimeAddmissionNurse, update_datetime AS update_datetimeAddmissionNurse
			FROM ".DbConstant::KPHIS_DBNAME.".ipd_progress_note_item WHERE an = :an and (an in (SELECT an from ".DbConstant::KPHIS_DBNAME.".prs_an_on_edit where status_ = 'Y')
			AND date(create_datetime) in(SELECT date_on_edit from ".DbConstant::KPHIS_DBNAME.".prs_an_on_edit where status_ = 'Y')) and (progress_note_item_detail not in('') or progress_note_item_detail_2 not in(''))
			order by create_datetime desc");
			$stmt->execute(array('an'=>$an));
			$rows = array();
			while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$rows[] = $r;
			}
			return $rows;
		}
	}


}