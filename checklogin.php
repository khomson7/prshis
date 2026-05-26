<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once './include/DbConstant.php';
require_once './include/DbUtils.php';
require_once './include/Session.php';

$username = $_REQUEST['username'];
$password = $_REQUEST['password'];
//$an  = $_REQUEST['an'];
//echo $username;
$conn = DbUtils::get_hosxp_connection();

if(Session::checklogin($conn, $username, $password)){
	// ถ้ามี redirect parameter ให้ redirect ไปหน้าที่ต้องการ
	$redirect = isset($_REQUEST['redirect']) ? trim($_REQUEST['redirect']) : '';

	// ป้องกัน open redirect: อนุญาตเฉพาะ relative path เท่านั้น
	if ($redirect !== '' && strpos($redirect, '://') === false && strpos($redirect, '//') !== 0) {
		header("Location: " . $redirect);
	} else {
		header("Location: index.php?loginname=".$_REQUEST['username']);
	}
	//header("Location: index.php");
	//echo "<script>window.history.back();</script>";
} else {
	//header("Location: index.php");
	echo "<script>window.history.back();</script>";
}