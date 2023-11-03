<?php 

require_once './header.php';
require_once './include/DbUtils.php';
require_once './include/KphisQueryUtils.php';
require_once './include/NutritionTracker.php';
$conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล


$an = '660016169';
$vn = '';
/*$hn = KphisQueryUtils::getHnByAn($an);
$vn = KphisQueryUtils::getVnByAn($an); */

$nutrition = NutritionTracker::get_nutrition1($an,$vn);

echo $nutrition

?>