<?php
date_default_timezone_set("Asia/Bangkok");
include('setting/query.php');
include('setting/connect_db.php');
include('setting/connecttkp_db.php');
mysqli_query($conn, "SET character_set_results=utf8");
mysqli_query($conn, "SET character_set_client=utf8");
mysqli_query($conn, "SET character_set_connection=utf8");
header('Content-Type: text/html; charset=UTF-8');
if(isset($_REQUEST['login'])){
    
    $login = $_REQUEST['login'];
}
else{
    $login='';
}
if(isset($_REQUEST['text1'])){
    $text1 = $_REQUEST['text1'];
}
else{
    $text1='';
}
if(isset($_REQUEST['text2'])){
    $text2 = $_REQUEST['text2'];
}
else{
    $text2='';
}
if(isset($_REQUEST['text3'])){
    $text3 = $_REQUEST['text3'];
}
else{
    $text3='';
}
if(isset($_REQUEST['text4'])){
    $text4 = $_REQUEST['text4'];
}
else{
    $text4='';
}
if(isset($_REQUEST['text5'])){
    $text5 = $_REQUEST['text5'];
}
else{
    $text5='';
}
if(isset($_REQUEST['text6'])){
    $text6 = $_REQUEST['text6'];
}
else{
    $text6='';
}
if(isset($_REQUEST['text7'])){
    $text7 = $_REQUEST['text7'];
}
else{
    $text7='';
}
if(isset($_REQUEST['text8'])){
    $text8 = $_REQUEST['text8'];
}
else{
    $text8='';
}
if(isset($_REQUEST['text9'])){
    $text9 = $_REQUEST['text9'];
}
else{
    $text9='';
}
if(isset($_REQUEST['text10'])){
    $text10 = $_REQUEST['text10'];
}
else{
    $text10='';
}
if(isset($_REQUEST['text11'])){
    $text11 = $_REQUEST['text11'];
}
else{
    $text11='';
}
if(isset($_REQUEST['text12'])){
    $text12 = $_REQUEST['text12'];
}
else{
    $text12='';
}
if(isset($_REQUEST['text13'])){
    $text13 = $_REQUEST['text13'];
}
else{
    $text13='';
}
if(isset($_REQUEST['text14'])){
    $text14 = $_REQUEST['text14'];
}
else{
    $text14='';
}
if(isset($_REQUEST['text15'])){
    $text15 = $_REQUEST['text15'];
}
else{
    $text15='';
}
if(isset($_REQUEST['text16'])){
    $text16 = $_REQUEST['text16'];
}
else{
    $text16='';
}
if(isset($_REQUEST['text17'])){
    $text17 = $_REQUEST['text17'];
}
else{
    $text17='';
}
if(isset($_REQUEST['text18'])){
    $text18 = $_REQUEST['text18'];
}
else{
    $text18='';
}
if(isset($_REQUEST['text19'])){
    $text19 = $_REQUEST['text19'];
}
else{
    $text19='';
}
if(isset($_REQUEST['text20'])){
    $text20 = $_REQUEST['text20'];
}
else{
    $text20='';
}
if(isset($_REQUEST['text21'])){
    $text21 = $_REQUEST['text21'];
}
else{
    $text21='';
}
if(isset($_REQUEST['text22'])){
    $text22 = $_REQUEST['text22'];
}
else{
    $text22='';
}
if(isset($_REQUEST['text23'])){
    $text23 = $_REQUEST['text23'];
}
else{
    $text23='';
}
if(isset($_REQUEST['check1'])){
    $check1 = $_REQUEST['check1'];
}
else{
    $check1='';
}
if(isset($_REQUEST['check2'])){
    $check2 = $_REQUEST['check2'];
}
else{
    $check2='';
}
if(isset($_REQUEST['check3'])){
    $check3 = $_REQUEST['check3'];
}
else{
    $check3='';
}
if(isset($_REQUEST['check4'])){
    $check4 = $_REQUEST['check4'];
}
else{
    $check4='';
}
   echo  $sql = "update sis set 
   sis_doo = '".$text1."',
   sis_ts = '".$text2."',
   sis_te = '".$text3."',
   sis_sur = '".$text4."',
   sis_fa = '".$text5."',
   sis_sa = '".$text6."',
   sis_sn = '".$text7."',
   sis_cd = '".$text8."',
   sis_tdc = '".$text9."',
   sis_pod = '".$text10."',
   sis_tdc2 = '".$text11."',
   sis_operation = '".$text12."',
   sis_so = '".$text13."',
   sis_anesthesia = '".$text14."',
   sis_anesthesist = '".$text15."',
   sis_position = '".$text16."',
   sis_supine = '".$text17."',
   sis_incision = '".$text18."',
   sis_hijbtlothb = '".$text19."',
   sis_findings = '".$text20."',
   sis_pd = '".$cehck1."',
   sis_rdwp = '".$check2."',
   sis_ebl = '".$text21."',
   sis_special = '".$text22."',
   sis_yes = '".$check3."',
   sis_no = '".$check4."',
   sis_surr = '".$text23."'
    where an = '".$_REQUEST['an']."'";
     $query = mysqli_query($conn2,$sql);
    alert("บันทึกเรียบร้อย");
    function alert($msg) {
      echo "<script type='text/javascript'>alert('$msg');</script>";
      echo '<script langauge="javascript">window.close();</script>';
    }
?>