<?php 
date_default_timezone_set('Asia/Bangkok');

$date=date_create();
$t=time();
date_timestamp_set($date,$t);
echo htmlspecialchars(date_format($date,"Y-m-d H:i:s"));

?>