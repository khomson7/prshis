<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once './include/DbConstant.php';
require_once './include/DbUtils.php';
require_once './include/Session.php';
require_once './include/KphisQueryUtils.php';
require_once './include/NutritionTracker.php';


$username = $_REQUEST['username'];
$password = $_REQUEST['password'];
$fname = $_REQUEST['fname'];
$lname = $_REQUEST['lname'];

//$an  = $_REQUEST['an'];
//echo $username;
$conn = DbUtils::get_hosxp_connection();


if(Session::checklogin($conn, $username, $password)){


	if (isset($_POST)) {
		// The $_POST variable exists

		$nutrition = NutritionTracker::checkuser($username);
		//$password2 = NutritionTracker::secure_pass('123456');
		$pass = $password;
		$passweb = $password;
		$fname =$fname;
		$lname = $lname;

		if($nutrition==1) {

			
		//	echo $nutrition ;
			header("Location: update-nutrition-user.php");

		} else {

			function  encrypt_md5_salt($pass)
	{
		// admin
		// 123456 ($2y$11$7E1Dw5fgB1FifW0apMj8meNHQG9janZMxtnaWPC4niyulskCov5sa)
        $key1 = 'RTy4$58/*tdr#t';	//default = RTy4$58/*tdr#t
        $key2 = 'ci@gen#$_sdf';		//default = ci@mania#$_sdf

        $key_md5 = md5($key1 . $pass . $key2);
        $key_md5 = md5($key2 . $key_md5 . $key1);
        $sub1 = substr($key_md5, 0, 7);
        $sub2 = substr($key_md5, 7, 10);
        $sub3 = substr($key_md5, 17, 12);
        $sub4 = substr($key_md5, 29, 3);
      return md5($sub3 . $sub1 . $sub4 . $sub2);
       // echo md5($sub3 . $sub1 . $sub4 . $sub2);
	}

	//$users = $username;

	function secure_pass($username,$pass,$fname,$lname)
    {
		$key_encrypt = encrypt_md5_salt($pass);
		$options = array('cost' => 11);
      // return password_hash($key_encrypt, PASSWORD_BCRYPT, $options);
    $aa = password_hash($key_encrypt, PASSWORD_BCRYPT, $options);

	 NutritionTracker::insertNutritionUser($aa,$username,$fname,$lname);
	 
    }

			
	//secure_pass('##Prasat10918##');
	secure_pass($username,$pass,$fname,$lname);
	

			$sMessage = 'User: '.$username.' มีการขอเข้าใช้ระบบประเมินภาวะโภชนาการ';
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
				//echo "status : ".$result_['status']; echo "message : ". $result_['message'];
				header("Location: register-success.php?loginname=".$_REQUEST['username']);
			} 
			curl_close( $chOne );   
			header("Location: register-success.php?loginname=".$_REQUEST['username']);
			//header("Location: update-nutrition-user.php");

		}

		//echo $nutrition;

	/*	$sMessage = 'ทดสอบแจ้ง ขอใช้งาน ';
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
				//echo "status : ".$result_['status']; echo "message : ". $result_['message'];
				header("Location: register-success.php?loginname=".$_REQUEST['username']);
			} 
			curl_close( $chOne );   

*/




		
		//echo 'The $_POST variable exists';
		
	} else {
		// The $_POST variable does not exist
		echo 'The $_POST variable does not exist';
	}

	//header("Location: index.php?loginname=".$_REQUEST['username']);
	//header("Location: register-success.php?loginname=".$_REQUEST['username']);
	//header("Location: index.php");
	//echo "<script>window.history.back();</script>";
} else {
	//header("Location: index.php");
	echo "<script>window.history.back();</script>";
}