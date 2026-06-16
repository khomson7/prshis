<?php
require 'vendor/autoload.php';
try {
    $mpdf = new \Mpdf\Mpdf();
    $mpdf->watermark_font = 'Garuda';
    $text = iconv("TIS-620", "UTF-8", 'โรงพยาบาลปราสาท');
    // Let's test if mb_detect_encoding is needed, or just hardcode UTF-8 chars
    $mpdf->SetWatermarkText('โรงพยาบาลปราสาท'); // Literal UTF-8 string
    $mpdf->showWatermarkText = true;
    $mpdf->WriteHTML('Hello');
    $mpdf->Output('test.pdf', 'F');
    echo 'SUCCESS';
} catch(Exception $e) {
    echo $e->getMessage();
}
