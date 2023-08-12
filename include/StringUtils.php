<?php
//require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/DbConstant.php';
class StringUtils {

    public static function isBlankOrNull($value){
		return $value == null || $value == '';
	}
    public static function isBlankOrNullOrWhiteSpace($value){
		return $value == null || trim($value) == '';
	}
    // public static function arrayToString($array, $type, $separator){
	// 	$result = '';
	// 	if($separator == null){
	// 		$separator = ',';
	// 	}
	// 	foreach ($array as $key => $val) {
	// 		if($result != ''){
	// 			$result .= $separator;
	// 		}
	// 		if($type == 'key'){
	// 			$result .= $key;
	// 		} else {
	// 			$result .= $val;
	// 		}
	// 	}
	// 	return $result;
	// }
}