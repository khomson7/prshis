<?php
require_once './header.php';
?>
<div class=" h-100 d-flex justify-content-center align-items-center">
    <div style="font-size:23px;"> ขอใช้งาน ระบบประเมินภาวะโภชนาการ (username password เดียวกับ HOSxP)</div>
</div>

<br>
<div class=" h-100 d-flex justify-content-center align-items-center">

<br>
  <form action="check-nutrition-regist.php" method="post">

    <div class="container" style="text-align: center;">
        <div class="row">
            <div class="col-md-10">
                <input type="text" name="username" class="form-control form-control-lg" placeholder="username" autofocus><strong>USERNAME</strong>
            </div>
</div>
<br />
<div class="row">
            <div class="col-md-10">
                <input type="password" name="password" class="form-control form-control-lg" placeholder="password"><strong>PASSWORD</strong>
            </div>
</div>
<br />

<div class="row">
            <div class="col-md-10">
                <input type="text" name="fname" class="form-control form-control-lg" placeholder="ชื่อ" required><strong>ชื่อ</strong>
            </div>
</div>
<br />
<div class="row">
            <div class="col-md-10">
                <input type="text" name="lname" class="form-control form-control-lg" placeholder="นามสกุล" required><strong>นามสกุล</strong>
            </div>


            <div class="col-md-10 pt-2">
                <button type="submit" class="btn btn-primary btn-lg btn-block"><i class="fas fa-sign-in-alt "></i> ขอใช้งานระบบ </button>
            </div>
        </div>
        </div>
    </div>

</form>
</div>


