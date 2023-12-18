<!DOCTYPE html>
<html>
<head>
<title>Bootstrap Example</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<style>
body {background-color: powderblue;}
h1   {color: blue;}
p    {color: red;}

input[type="text"] {
width: 100%;
border: 0px;
outline: 0px;
}

img {
  border: 1px solid #ddd;
  border-radius: 4px;
  padding: 5px;
  width: 150px;
}
</style>
</head>
<body>

<?php
  require './vendor/autoload.php'; 

 

  $PNG_TEMP_DIR = dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
    
    //html PNG location prefix
    $PNG_WEB_DIR = 'temp/';

    include "qrlib.php";    
    
    //ofcourse we need rights to create temp dir
    if (!file_exists($PNG_TEMP_DIR))
        mkdir($PNG_TEMP_DIR);
    
    
    $filename = $PNG_TEMP_DIR.'test.png';
    
    //processing form input
    //remember to sanitize user input in real-life solution !!!
    $errorCorrectionLevel = 'L';
    if (isset($_REQUEST['level']) && in_array($_REQUEST['level'], array('L','M','Q','H')))
        $errorCorrectionLevel = $_REQUEST['level'];    

    $matrixPointSize = 4;
    if (isset($_REQUEST['size']))
        $matrixPointSize = min(max((int)$_REQUEST['size'], 1), 10);


    if (isset($_REQUEST['data'])) { 
    
        //it's very important!
        if (trim($_REQUEST['data']) == '')
            die('data cannot be empty! <a href="?">back</a>');
            
        // user data
        $filename = $PNG_TEMP_DIR.'test'.md5($_REQUEST['data'].'|'.$errorCorrectionLevel.'|'.$matrixPointSize).'.png';
        QRcode::png($_REQUEST['data'], $filename, $errorCorrectionLevel, $matrixPointSize, 2);    
        
    } else {    
    
        //default data
       
      //  QRcode::png('PHP QR Code :)', $filename, $errorCorrectionLevel, $matrixPointSize, 2);    
        
    }    
        
    //display generated file
    //echo '<img src="'.$PNG_WEB_DIR.basename($filename).'" /><hr/>';  
    

// Connect to MySQL Database
$servername = "192.168.3.3";
$username = "slave";
$password = "slave10918";
$dbname = "hos";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// Fetch data from MySQL database
$sql = "select * from nondrugitems where icode in('3000011','3000001','3000002','3001479','3001488','3001473','3004427')";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Include QR Code library
  // Adjust the path to autoload.php

    // Loop through each row
    while ($row = $result->fetch_assoc()) {
        // Get the data you want to encode into QR code
        $data = $row['name'];// Replace 'column_name' with the appropriate column from your table
        

        // user data
        $filename = $PNG_TEMP_DIR.'test'.md5($data.'|'.$errorCorrectionLevel.'|'.$matrixPointSize).'.png';
        QRcode::png($data, $filename, $errorCorrectionLevel, $matrixPointSize, 2);   

        // Generate QR code
       // $qrCode = new BaconQrCode\Encoder\QrCode($data);
    //   $qrcode = new \BaconQrCode\Encoder\QrCode($data);
        
        // Output QR code image (You can save it to a file, display on the page, etc.)
       // header('Content-Type: ' . $qrCode->getMimeType());
        //echo $qrCode->writeString();
       // echo "<br>";


       //display generated file
    echo '<center><img src="'.$PNG_WEB_DIR.basename($filename).'" /></center><hr/>';  

    
    
    //config form
   echo '<style>
   input[type="text"] {
   width: 100%;
   border: 0px;
   outline: 0px;
   }
   </style>
   
   
   <form action="qr3.php" method="get">
        Data:&nbsp;<input type="text" name="data"  value="'.$data.'" />&nbsp;';
        
    
        
   echo '</form><hr/>'; 
        
    // benchmark
   // QRtools::timeBenchmark();    

   


    }

} else {
    echo "0 results";
}
$conn->close();
?>

</body>
</html>