<?php
error_reporting(0);
require_once __DIR__ . '/../vendor/autoload.php';
//require_once __DIR__ . '/DbConstant.php';
require_once __DIR__ . '/DbUtils.php';
session_start();
$root = $_SERVER['DOCUMENT_ROOT'];
class Session {

	public static function checklogin($conn, $username, $password){

		//session_start();
		session_destroy();

		$values =[
			'loginname'=>$username,
			'passweb'=>$password,
		];
		$sql = "select * from ".DbConstant::HOSXP_DBNAME.".opduser where loginname = :loginname and passweb = MD5(:passweb) and (account_disable is null or account_disable <> 'Y') ";

		$stmt = $conn->prepare($sql);
		$stmt->execute($values);

		if($row = $stmt->fetch()){
			//set session
			session_start();
//error_reporting(0);
			$_SESSION['loginname']=$row['loginname'];
			$_SESSION['name']=$row['name'];
			$_SESSION['doctorcode']=$row['doctorcode'];
			$_SESSION['groupname']=$row['groupname'];
			$_SESSION['accessright']=$row['accessright'];
			$_SESSION['entryposition']=$row['entryposition'];
			return true;
		} else {
			return false;
		}
	}

	public static function checkloginBoolean($conn, $username, $password){
		$values =[
			'loginname'=>$username,
			'passweb'=>$password,
		];
		$sql = "select * from ".DbConstant::HOSXP_DBNAME.".opduser where loginname = :loginname and passweb = MD5(:passweb) and (account_disable is null or account_disable <> 'Y') ";

		$stmt = $conn->prepare($sql);
		$stmt->execute($values);

		if($row = $stmt->fetch()){
			return true;
		} else {
			return false;
		}
	}


	public static function checkLoginSessionAndShowMessage(){
			if(!($result = Session::checkLoginSession())){
				//header('HTTP/1.1 401 Unauthorized');
				//header("Location: ./index.php");
				exit;
			}
			// header('HTTP/1.1 401 Unauthorized');
			// return $result;
			// exit;
		}

    public static function checkLoginSession(){
		return isset($_SESSION['loginname']);
	}

	public static function responsePermissionError($message){
		// if($message == null){
		// 	$message = 'Access Denied.';
		// }
		header('HTTP/1.1 403 Forbidden');
	}

	public static function responsePermissionErrorForJsonRequest($message){
		if($message == null){
			$message = 'Access Denied.';
		}
		// else {
		// 'ไม่สามารถเข้าใช้งานได้ เนื่องจากท่านขาดสิทธิ์การใช้งานดังต่อไปนี้: ' .
		// }
		header('HTTP/1.1 403 Forbidden');
        header('Content-Type: application/json; charset=UTF-8');
		// die(json_encode(array('message' => 'ERROR', 'code' => 1337)));
		die(json_encode(array('message' => $message)));
	}

	/**
	 * usage: SessionManager::checkPermissionAndShowMessage('[APPOINTMENT]');
	 */
	public static function checkPermissionAndShowMessage($resource, $operation){
		if(!Session::checkPermission($resource, $operation)){
			//set error message
			$error_message = 'ไม่สามารถเข้าใช้งานได้ เนื่องจากท่านขาดสิทธิ์การใช้งานดังต่อไปนี้: '.$resource." : ".$operation;
			header("Location: $root /mains/message.php?message=".$error_message);
			exit;
		}
	}

	public static function checkPermissionAndShowMessageWithoutHeader($resource, $operation){
		if(!Session::checkPermission($resource, $operation)){
			//set error message
			$error_message = 'ไม่สามารถเข้าใช้งานได้ เนื่องจากท่านขาดสิทธิ์การใช้งานดังต่อไปนี้: '.$resource." : ".$operation;
			header("Location: message-without-header.php?message=".$error_message);
			exit;
		}
	}


	public static function checkPermission($resource, $operation){
		$conn = DbUtils::get_hosxp_connection();
		$values = [
			'loginname'=>$_SESSION['loginname'],
			'resource'=>$resource,
			'operation'=>$operation,
		];
		//ตรวจสอบสิทธิ์ของกลุ่มย้อนขึ้นไปได้ 5 ระดับ
		$sql = "select u.loginname, ru.role, r.role_desc, rp.permission, p.resource, p.operation
		from ".DbConstant::HOSXP_DBNAME.".opduser u
		join ".DbConstant::KPHIS_DBNAME.".system_ac_role_user ru on u.loginname = ru.loginname
		join ".DbConstant::KPHIS_DBNAME.".system_ac_role r on r.role in (
			SELECT r1.role
			from ".DbConstant::KPHIS_DBNAME.".system_ac_role r1
			where r1.role = ru.role
			union
			SELECT r2.role
			from ".DbConstant::KPHIS_DBNAME.".system_ac_role r1
			join ".DbConstant::KPHIS_DBNAME.".system_ac_role r2 on r1.parent_role = r2.role
			where r1.role = ru.role
			union
			SELECT r3.role
			from ".DbConstant::KPHIS_DBNAME.".system_ac_role r1
			join ".DbConstant::KPHIS_DBNAME.".system_ac_role r2 on r1.parent_role = r2.role
			join ".DbConstant::KPHIS_DBNAME.".system_ac_role r3 on r2.parent_role = r3.role
			where r1.role = ru.role
			union
			SELECT r4.role
			from ".DbConstant::KPHIS_DBNAME.".system_ac_role r1
			join ".DbConstant::KPHIS_DBNAME.".system_ac_role r2 on r1.parent_role = r2.role
			join ".DbConstant::KPHIS_DBNAME.".system_ac_role r3 on r2.parent_role = r3.role
			join ".DbConstant::KPHIS_DBNAME.".system_ac_role r4 on r3.parent_role = r4.role
			where r1.role = ru.role
			union
			SELECT r5.role
			from ".DbConstant::KPHIS_DBNAME.".system_ac_role r1
			join ".DbConstant::KPHIS_DBNAME.".system_ac_role r2 on r1.parent_role = r2.role
			join ".DbConstant::KPHIS_DBNAME.".system_ac_role r3 on r2.parent_role = r3.role
			join ".DbConstant::KPHIS_DBNAME.".system_ac_role r4 on r3.parent_role = r4.role
			join ".DbConstant::KPHIS_DBNAME.".system_ac_role r5 on r4.parent_role = r5.role
			where r1.role = ru.role
		)
		join ".DbConstant::KPHIS_DBNAME.".system_ac_role_permission rp
			on r.role = rp.role
		join ".DbConstant::KPHIS_DBNAME.".system_ac_permission p on p.permission = rp.permission
		where u.loginname = :loginname and p.resource = :resource and p.operation = :operation ";

		$stmt = $conn->prepare($sql);
		$stmt->execute($values);

		if($row = $stmt->fetch()){
			return true;
		} else {
			return false;
		}
	}

	public static function checkPermissionByName($permission){
		$conn = DbUtils::get_hosxp_connection();
		$values = [
			'loginname'=>$_SESSION['loginname'],
			'permission'=>$permission,
		];
		//ตรวจสอบสิทธิ์ของกลุ่มย้อนขึ้นไปได้ 5 ระดับ
		$sql = "select u.loginname, ru.role, r.role_desc, rp.permission, p.resource, p.operation
		from ".DbConstant::HOSXP_DBNAME.".opduser u
		join ".DbConstant::KPHIS_DBNAME.".system_ac_role_user ru on u.loginname = ru.loginname
		join ".DbConstant::KPHIS_DBNAME.".system_ac_role r on r.role in (
			SELECT r1.role
			from ".DbConstant::KPHIS_DBNAME.".system_ac_role r1
			where r1.role = ru.role
			union
			SELECT r2.role
			from ".DbConstant::KPHIS_DBNAME.".system_ac_role r1
			join ".DbConstant::KPHIS_DBNAME.".system_ac_role r2 on r1.parent_role = r2.role
			where r1.role = ru.role
			union
			SELECT r3.role
			from ".DbConstant::KPHIS_DBNAME.".system_ac_role r1
			join ".DbConstant::KPHIS_DBNAME.".system_ac_role r2 on r1.parent_role = r2.role
			join ".DbConstant::KPHIS_DBNAME.".system_ac_role r3 on r2.parent_role = r3.role
			where r1.role = ru.role
			union
			SELECT r4.role
			from ".DbConstant::KPHIS_DBNAME.".system_ac_role r1
			join ".DbConstant::KPHIS_DBNAME.".system_ac_role r2 on r1.parent_role = r2.role
			join ".DbConstant::KPHIS_DBNAME.".system_ac_role r3 on r2.parent_role = r3.role
			join ".DbConstant::KPHIS_DBNAME.".system_ac_role r4 on r3.parent_role = r4.role
			where r1.role = ru.role
			union
			SELECT r5.role
			from ".DbConstant::KPHIS_DBNAME.".system_ac_role r1
			join ".DbConstant::KPHIS_DBNAME.".system_ac_role r2 on r1.parent_role = r2.role
			join ".DbConstant::KPHIS_DBNAME.".system_ac_role r3 on r2.parent_role = r3.role
			join ".DbConstant::KPHIS_DBNAME.".system_ac_role r4 on r3.parent_role = r4.role
			join ".DbConstant::KPHIS_DBNAME.".system_ac_role r5 on r4.parent_role = r5.role
			where r1.role = ru.role
		)
		join ".DbConstant::KPHIS_DBNAME.".system_ac_role_permission rp
			on r.role = rp.role
		join ".DbConstant::KPHIS_DBNAME.".system_ac_permission p on p.permission = rp.permission
		where u.loginname = :loginname and p.permission = :permission ";

		$stmt = $conn->prepare($sql);
		$stmt->execute($values);

		if($row = $stmt->fetch()){
			return true;
		} else {
			return false;
		}
	}

	public static function checkHosxpPermissionAndShowMessage($accessright){
		if(!SessionManager::checkHosxpPermission($accessright)){
			//set error message
			$error_message = 'ไม่สามารถเข้าใช้งานได้ เนื่องจากท่านขาดสิทธิ์การใช้งานดังต่อไปนี้: '.$accessright;
			header("Location: message.php?message=".$error_message);
			exit;
		}
	}

	public static function checkHosxpPermission($accessright){
		return strpos($_SESSION['accessright'], $accessright);
	}

	public static function getCurrentUserRoles(){
		$conn = DbUtils::get_hosxp_connection();
		$values = [
			'loginname'=>$_SESSION['loginname'],
		];
		$sql = "select u.loginname, ru.role, r.role_desc
		from ".DbConstant::HOSXP_DBNAME.".opduser u
		join ".DbConstant::KPHIS_DBNAME.".system_ac_role_user ru on u.loginname = ru.loginname
		join ".DbConstant::KPHIS_DBNAME.".system_ac_role r on r.role = ru.role
		where u.loginname = :loginname
		order by r.role_desc
		";

		$stmt = $conn->prepare($sql);
		$stmt->execute($values);

		$rows = array();
		while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$rows[] = $r;
		}
		return $rows;
	}






	public static function insertSystemAccessLog($access_detail){
		$conn = DbUtils::get_hosxp_connection();

		$access_host = $_SERVER['REMOTE_ADDR'];

		$values = [
			'access_user'=>$_SESSION['loginname'],
			'access_host'=>$access_host,
			'access_detail'=>$access_detail,
		];

		$sql = "INSERT INTO ".DbConstant::KPHIS_DBNAME.".system_access_log(access_datetime,access_user,access_host,access_detail)
				VALUES (now(),:access_user,:access_host,:access_detail)";

		$stmt = $conn->prepare($sql);
		$stmt->execute($values);
	}

}

?>
