<?php
   require_once './include/Session.php';
  //Session::checkLoginSessionAndShowMessage();
    //Session::checkPermissionAndShowMessage('IPD_DOCTOR_MAIN_PROGRAM','ACCESS');

 //   require_once './menu.php'; //เป็นส่วนที่แสดง tab bar menu ด้านบนของแพทย์
 //   require_once 'ipd-show-patient-main-sticky.php';
 //   require_once 'ipd-show-patient-main.php'; //เป็นส่วนที่แสดง ข้อมูลผู้ป่วย เช่น รูป,hn,an,ชื่อ-สกุล,แพ้ยา ฯลฯ
 //   require_once './project/function/KphisQueryUtils.php';
   $an = empty($_REQUEST['an']) ? null : $_REQUEST['an'];
   //$hn = KphisQueryUtils::getHnByAn($an);
   //$vn = KphisQueryUtils::getVnByAn($an);
   //$patient_name = KphisQueryUtils::getPatientName($hn);
 //  echo $an;

 require_once './header.php';

 $values =['loginname'=>$loginname];

 if(!$an){
 // session_start();
 // session_destroy();  
 //exit();            
      
}

  if($sess == 'true'){ 
 require_once 'ipd-show-patient-main.php';
  }
  
?>

<?php  if( $sess != 'true'){ ?>

    <form action="checklogin.php" method="post">
            <div class="form-row">
              <div class="col-md-12">
                <input type="text" name="username" class="form-control form-control-lg" placeholder="username" autofocus>
              </div>
              <div class="col-md-12">
                <input type="password" name="password" class="form-control form-control-lg" placeholder="password">
              </div>
              
              <div class="col-md-12 pt-2">
                <button type="submit" class="btn btn-primary btn-lg btn-block"><i class="fas fa-sign-in-alt "></i> เข้าสู่ระบบ</button>
              </div>
            </div>
          </form>


<?php } ?>

    <div class="container-fluid">
        <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">

        <?php  if($an != '' && $sess == 'true'){ ?>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo $mylink ?>ipd-dr-search-patient.php" role="tab" ><i class="fas fa-arrow-left"></i> กลับ</a>
            </li>

            <li class="nav-item">
                    <a class="nav-link" id="pills-document-tab" data-toggle="pill" href="#pills-document" role="tab" aria-controls="pills-document" aria-selected="true">เอกสาร</a>
                </li>

                <?php }  ?>

               <!-- <li class="nav-item active dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false"><em class="fas fa-folder-open"></em> เอกสาร</a>
                    <div class="dropdown-menu" aria-labelledby="dropdownId">
                        <a class="dropdown-item" href="summ/index.php?an=">SummaryDischart</a>
                        <a class="dropdown-item" href="sis/index.php?an=">บันทึกผ่าตัด (Sistrunk)</a>
                        
                    </div>
                </li> -->
            
        </ul>
        <div class="tab-content" id="pills-tabContent"><hr>
            
       
            
            <div class="tab-pane fade show active" id="pills-document" role="tabpanel" aria-labelledby="pills-document-tab"><?php require_once 'ipd-nurse-document.php'; ?></div>
            <div class="tab-pane fade" id="pills-consult" role="tabpanel" aria-labelledby="pills-consult-tab"><?php /*require_once 'ipd-dr-consult.php'; */?></div>    
            <div class="tab-pane fade" id="pills-his_or" role="tabpanel" aria-labelledby="pills-his_or-tab">...</div>
            <div class="tab-pane fade" id="pills-nurse_note" role="tabpanel" aria-labelledby="pills-nurse_note-tab"><div class="row"><?php /*require_once 'ipd-nurse-focus-note-table-all-searchDate.php'; */?></div></div>
        </div>
    </div>

