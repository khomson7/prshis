<?php
require_once '../include/DbUtils.php';
    require_once '../include/KphisQueryUtils.php';
    require_once '../include/Session.php';
    //เวลาตาม timezone
    date_default_timezone_set("Asia/Bangkok");

    $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
   // $username  = $_SESSION['loginname'];
  //  $signature = $_POST['signature'];
      $username  = 'khom';
      $signature = $_POST['signature'];
   // $stmt = $pdo->prepare("UPDATE users SET signature = :signature WHERE id = :id");
echo $username;
   $stmt = $conn->prepare("UPDATE ".DbConstant::KPHIS_DBNAME.".users set signature = :signature WHERE username = :username ");
$stmt->execute(['signature' => $signature, 'username' => $username]);
echo "<script>window.location.href='../index.php';</script>";