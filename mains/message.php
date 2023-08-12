<?php 
require_once '../include/Session.php';
require_once '../mains/main-report.php';
if(Session::checkLoginSession()){
  
 // require_once '../main.php';//เป็นส่วนที่แสดง tab bar menu ด้านบน
?>
<div class="container pt-3">
  <div class="row pt-3">
    <div class="col">
      <div class="alert alert-info" role="alert">
        <?=htmlspecialchars($_REQUEST['message'])?>
      </div>
    </div>
  </div>
</div>
<?php
}
?>