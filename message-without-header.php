<?php 
require_once './project/function/SessionManager.php';
if(SessionManager::checkLoginSession()){
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