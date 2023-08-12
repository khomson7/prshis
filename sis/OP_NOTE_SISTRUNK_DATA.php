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
    $null=NULL;
     $sql = "insert into sis values('','".$login."','".$_REQUEST['doctorname']."','".$_REQUEST['an']."','".$_REQUEST['hn']."','".$text1."','".$text2."','".$text3."','".$text4."','".$text5."','".$text6."','".$text7."','".$text8."','".$text9."','".$text10."','".$text11."','".$text12."','".$text13."','".$text14."','".$text15."','".$text16."','".$text17."','".$text18."','".$text19."','".$text20."','".$check1."','".$check2."','".$text21."','".$text22."','".$check3."','".$check4."','".$text23."','".date("Y-m-d")."')";
      $query = mysqli_query($conn2,$sql);  
    $sql2 = "select * from sis where an like '".$_REQUEST['an']."' order by surgery_No DESC";
    $query2 = mysqli_query($conn2,$sql2);
    $result2=mysqli_fetch_assoc($query2);
   $sql3 = "insert into all_surgery values('".$_REQUEST['hn']."','".$_REQUEST['an']."','Sistrunk','".$result2['surgery_No']."','".$result2['sis_sur']."','sis',NULL,NULL,'".$result2["sis_doo"]."',NULL,NULL)";
   $query3 = mysqli_query($conn2,$sql3);
       alert("บันทึกเรียบร้อย");
     function alert($msg) {
       echo "<script type='text/javascript'>alert('$msg');</script>";
       echo '<script langauge="javascript">window.close();</script>';
     } 
?>