<?php
require_once __DIR__ . '/vendor/autoload.php';
try {
    $mpdf = new \Mpdf\Mpdf();
    if (method_exists($mpdf, 'setSourceFile')) {
        echo "setSourceFile exists!\n";
    } else {
        echo "setSourceFile missing!\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
