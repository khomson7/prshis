<?php
require_once __DIR__ . '/vendor/autoload.php';
if (class_exists('\setasign\Fpdi\Fpdi')) {
    echo "FPDI exists\n";
} else {
    echo "FPDI does not exist\n";
}
if (class_exists('\Mpdf\Mpdf')) {
    echo "Mpdf exists\n";
}
