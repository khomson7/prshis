<?php
session_start();
session_destroy();

$root = $_SERVER['DOCUMENT_ROOT']; 

header("Location: $root/index.php");
?>