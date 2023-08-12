<?php 
require_once './include/Session.php';
echo json_encode(Session::checkLoginSession());
?>