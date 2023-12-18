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
        echo 'You can provide data in GET parameter: <a href="?data=like_that">like that</a><hr/>';    
        QRcode::png('PHP QR Code :)', $filename, $errorCorrectionLevel, $matrixPointSize, 2);    
        
    }    
        
    //display generated file
    echo '<img src="'.$PNG_WEB_DIR.basename($filename).'" /><hr/>';  
    

// Connect to MySQL Database
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "hos";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch data from MySQL database
$sql = "select * from nondrugitems limit 10";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Include QR Code library
  // Adjust the path to autoload.php

    // Loop through each row
    while ($row = $result->fetch_assoc()) {
        // Get the data you want to encode into QR code
        $data = $row['icode'];// Replace 'column_name' with the appropriate column from your table
        
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
    echo '<form action="qr2.php" method="get">
        Data:&nbsp;<input name="data" value="'.$data.'" />&nbsp;
        ECC:&nbsp;<select name="level">
            <option value="L"'.(($errorCorrectionLevel=='L')?' selected':'').'>L - smallest</option>
            <option value="M"'.(($errorCorrectionLevel=='M')?' selected':'').'>M</option>
            <option value="Q"'.(($errorCorrectionLevel=='Q')?' selected':'').'>Q</option>
            <option value="H"'.(($errorCorrectionLevel=='H')?' selected':'').'>H - best</option>
        </select>&nbsp;
        Size:&nbsp;<select name="size">';
        
    for($i=1;$i<=10;$i++)
        echo '<option value="'.$i.'"'.(($matrixPointSize==$i)?' selected':'').'>'.$i.'</option>';
        
    echo '</select>&nbsp;
        <input type="submit" value="GENERATE"></form><hr/>';
        
    // benchmark
    QRtools::timeBenchmark();    

    


    }

} else {
    echo "0 results";
}
$conn->close();
?>