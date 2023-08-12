<?php
require_once  '../vendor/autoload.php';
require_once '../include/KphisConstant.php';
require_once '../include/DbUtils.php';
require_once '../include/Session.php';

if(Session::checkLoginSession()){
	$hn = $_REQUEST['hn'];
	if($hn != null || $hn != ''){
		$conn = DbUtils::get_hosxp_connection();
		getPatientImageStream($conn, $hn);
	} else {
		readfile("include/images/user1.png");
	}
} else {
	readfile("include/images/user1.png");
}

function getPatientImageStream($conn, $hn){
	$values =[
		'hn'=>$hn,
	];

	$sql = "select hn, image from ".DbConstant::HOSXP_DBNAME.".patient_image where hn=:hn ";

	$stmt = $conn->prepare($sql);
	$stmt->execute($values);

	if($row = $stmt->fetch()){
		echo $row['image'];
	} else {
		readfile("include/images/user1.png");
	}
}