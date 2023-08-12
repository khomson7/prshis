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
    if(isset($_REQUEST['an'])){
        
        $an = $_REQUEST['an'];
    }
    else{
        $an='';
    }
    if(isset($_REQUEST['hn'])){
        
        $hn = $_REQUEST['hn'];
    }
    else{
        $hn='';
    }
    if(isset($_REQUEST['doctorname'])){
        
        $doctorname = $_REQUEST['doctorname'];
    }
    else{
        $doctorname='';
    }
    if(isset($_REQUEST['input1'])){
        $input1 = $_REQUEST['input1'];
    }
    else{
        $input1='';
    }
    if(isset($_REQUEST['input2'])){
        $input2 = $_REQUEST['input2'];
    }
    else{
        $input2='';
    }

  $sql = "insert into clinicalsummary VALUES('','".$login."','".$doctorname."','".$an."','".$hn."','".$input1."','".$input2."','".date("Y-m-d")."')";
    $query = mysqli_query($conn2,$sql);
    alert("บันทึกเรียบร้อย");
   function alert($msg) {
     echo "<script type='text/javascript'>alert('$msg');</script>";
     echo '<script langauge="javascript">window.close();</script>';
   }
?>