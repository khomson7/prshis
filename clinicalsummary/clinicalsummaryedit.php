<?php
include("setting/head.php");
/* include("setting/query.php"); */
include("setting/connecttkp_db.php");
mysqli_query($conn, "SET character_set_results=utf8");
mysqli_query($conn, "SET character_set_client=utf8");
mysqli_query($conn, "SET character_set_connection=utf8");
header('Content-Type: text/html; charset=UTF-8');
$sql = "update clinicalsummary set c_date = '".$_REQUEST['input1']."',c_input1 = '".$_REQUEST['input2']."',doctorname = '".$_REQUEST['doctorname']."' where an = '".$_REQUEST['an']."'";
$query = mysqli_query($conn2,$sql);
alert("บันทึกเรียบร้อย");
function alert($msg) {
  echo "<script type='text/javascript'>alert('$msg');</script>";
  echo '<script langauge="javascript">window.close();</script>';
}
?>