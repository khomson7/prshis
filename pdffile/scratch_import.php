<?php
require_once __DIR__ . '/../vendor/autoload.php';

try {
    $mpdf = new \Mpdf\Mpdf();
    $pdfData = "%PDF-1.4\n%..."; // dummy
    // Check which class exists
    if (class_exists('\setasign\Fpdi\PdfParser\StreamReader')) {
        echo "setasign StreamReader exists.\n";
    } else {
        echo "setasign StreamReader MISSING.\n";
    }
    
    if (class_exists('\Mpdf\Pdf\PdfParser\StreamReader')) {
        echo "Mpdf StreamReader exists.\n";
    } else {
        echo "Mpdf StreamReader MISSING.\n";
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
