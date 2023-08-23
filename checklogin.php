<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once './include/DbConstant.php';
require_once './include/DbUtils.php';
require_once './include/Session.php';

$username = $_REQUEST['username'];
$password = $_REQUEST['password'];
$an  = $_REQUEST['an'];

$conn = DbUtils::get_hosxp_connection();

if(Session::checklogin($conn, $username, $password)){
	//header("Location: index.php");
	echo "<script>window.history.back();</script>";
} else {
	//header("Location: index.php");
	echo "<script>window.history.back();</script>";
}