<?php 
require_once '../include/Session.php';
require_once '../include/session-sso.php';
echo json_encode(Session::checkLoginSession());
?>
