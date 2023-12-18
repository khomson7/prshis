<?php
// Include the Composer autoloader
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

use Endroid\QrCode\QrCode;

// Data to be encoded in the QR code
$data = '6600126'; // Replace this with your data

// Create a QR code instance
$qrCode = new QrCode($data);

// Save the QR code as a file (optional)

$qrCode->writeFile('./picture/aaa.png');



?>
