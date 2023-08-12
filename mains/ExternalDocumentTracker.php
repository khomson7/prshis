<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/KphisConstant.php';
class ExternalDocumentTracker {
	public static function get_connection($servername, $username, $password, $dbname){
		$conn = null;
		try {
			$conn = new PDO("mysql:host=$servername;dbname=$dbname;", $username, $password);/*charset=utf8mb4;*/
			$conn->exec("set names utf8");
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			print "Error!: " . $e->getMessage() . "<br/>";
			die();
		}
		return $conn;
	}

    public static function get_neodms_connection(){
		return ExternalDocumentTracker::get_connection(
			KphisConstant::NEODMS_CONNECTION_SERVERNAME,
			KphisConstant::NEODMS_CONNECTION_USERNAME,
			KphisConstant::NEODMS_CONNECTION_PASSWORD,
			KphisConstant::NEODMS_CONNECTION_DBNAME);
	}

	public static function get_document_neodms_anes($vn, $an){
		if(KphisConstant::NEODMS_CONNECTION_USE != 'Y'){
			return 0;
		}
		if($an != null && trim($an) != ''){
			$conn = ExternalDocumentTracker::get_neodms_connection();
			$stmt = $conn->prepare("select count(*) as count_data
									from tb_m_index_1
									where 1=1 "//74 = 13.วิสัญญี, 81 = 03.วิสัญญี
									.($vn != null ? " and (c_9 = 81 and c_2 = :vn) " : "")
									.($an != null ? " and (c_9 = 74 and c_8 = :an) " : "")
								);
			if($an != null && trim($an) != ''){
				$stmt->execute(array('an'=>$an));
			}
			if($vn != null && trim($vn) != ''){
				$stmt->execute(array('vn'=>$vn));
			}
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}

	public static function get_document_neodms_er($vn, $an){
		if(KphisConstant::NEODMS_CONNECTION_USE != 'Y'){
			return 0;
		}
		if(($an != null && trim($an) != '')
		|| ($vn != null && trim($vn) != '')){
			$conn = ExternalDocumentTracker::get_neodms_connection();
			$stmt = $conn->prepare("select count(*) as count_data
									from tb_m_index_1
									where (c_9 = 93 or c_9 = 57)"//93 = 08.ER, 57 = สังเกตุอาการ ER
									.($vn != null ? " and c_2 = :vn " : "")
									.($an != null ? " and c_8 = :an " : "")
								);
			if($an != null && trim($an) != ''){
				$stmt->execute(array('an'=>$an));
			}
			if($vn != null && trim($vn) != ''){
				$stmt->execute(array('vn'=>$vn));
			}
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}

	public static function get_document_neodms_operative($an){
		if(KphisConstant::NEODMS_CONNECTION_USE != 'Y'){
			return 0;
		}
		if($an != null && trim($an) != ''){
			$conn = ExternalDocumentTracker::get_neodms_connection();
			$stmt = $conn->prepare("select count(*) as count_data
									from tb_m_index_1
									where 1=1 "//68 = 05.Report/Anesthetic Record/Labour record
									.($an != null ? " and (c_9 = 68 and c_8 = :an) " : "")
								);
			if($an != null && trim($an) != ''){
				$stmt->execute(array('an'=>$an));
			}
			$row = $stmt->fetch();
			$count_data = $row['count_data'];
			return $count_data > 0;
		}
	}
}