<?php
@session_start();
error_reporting(0);
   
?>

<?php 

require_once './header.php';
require_once './include/DbUtils.php';
require_once './include/KphisQueryUtils.php';
require_once './include/NutritionTracker.php';
$conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล


$an = '660016169';
$vn = '';
/*$hn = KphisQueryUtils::getHnByAn($an);
$vn = KphisQueryUtils::getVnByAn($an); */

$nutrition = NutritionTracker::get_nutrition1($an,$vn);



/*
$sMessage = 'ทดสอบแจ้ง ขอใช้งาน ';
$sToken ='PaxKnAdzWtMOY2zs9gUkIEGeAY58gTdEyuLOzbR4YoP';
 //$sMessage = 'แจ้งหมดอายุ:';

   $chOne = curl_init(); 
	curl_setopt( $chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify"); 
	curl_setopt( $chOne, CURLOPT_SSL_VERIFYHOST, 0); 
	curl_setopt( $chOne, CURLOPT_SSL_VERIFYPEER, 0); 
	curl_setopt( $chOne, CURLOPT_POST, 1); 
	curl_setopt( $chOne, CURLOPT_POSTFIELDS, "message=".$sMessage); 
	$headers = array( 'Content-type: application/x-www-form-urlencoded', 'Authorization: Bearer '.$sToken.'', );
	curl_setopt($chOne, CURLOPT_HTTPHEADER, $headers); 
	curl_setopt( $chOne, CURLOPT_RETURNTRANSFER, 1); 
	$result = curl_exec( $chOne ); 

    if(curl_error($chOne)) 
	{ 
		echo 'error:' . curl_error($chOne); 
	} 
	else { 
		$result_ = json_decode($result, true); 
		echo "status : ".$result_['status']; echo "message : ". $result_['message'];
	} 
	curl_close( $chOne );   

	*/

?>