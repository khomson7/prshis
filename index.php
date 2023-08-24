<?php
   require_once './include/Session.php';
   Session::checkLoginSessionAndShowMessage();
  Session::checkPermissionAndShowMessage('IPD_DOCTOR_MAIN_PROGRAM','ACCESS');

 //   require_once './menu.php'; //เป็นส่วนที่แสดง tab bar menu ด้านบนของแพทย์
 //   require_once 'ipd-show-patient-main-sticky.php';
 //   require_once 'ipd-show-patient-main.php'; //เป็นส่วนที่แสดง ข้อมูลผู้ป่วย เช่น รูป,hn,an,ชื่อ-สกุล,แพ้ยา ฯลฯ
 //   require_once './project/function/KphisQueryUtils.php';
 
   $an = empty($_REQUEST['an']) ? null : $_REQUEST['an'];
   $hn = KphisQueryUtils::getHnByAn($an);
   $vn = KphisQueryUtils::getVnByAn($an);
   $patient_name = KphisQueryUtils::getPatientName($hn);
   $login = $_SESSION['loginname'];

 require_once './header.php';
 require_once 'ipd-show-patient-main.php';



?>




    <div class="container-fluid">
        <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
       <?php  echo $login?>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo $mylink ?>ipd-dr-search-patient.php" role="tab" ><i class="fas fa-arrow-left"></i> กลับ</a>
            </li>

            <!--<li class="nav-item">
                    <a class="nav-link active" id="pills-document-tab" data-toggle="pill" href="#pills-document" role="tab" aria-controls="pills-document" aria-selected="true">เอกสาร</a>
                </li> -->
            
        </ul>
        <div class="tab-content" id="pills-tabContent"><hr>
            <?php

                ?>
                <div class="tab-pane fade" id="pills-med-reconciliation" role="tabpanel" aria-labelledby="pills-med-reconciliation-tab"><?php /*require_once 'ipd-dr-med-reconcile.php';*/ ?> </div>
                <?php

            ?>
            <div class="tab-pane fade" id="pills-lab" role="tabpanel" aria-labelledby="pills-lab-tab"><?php /*require_once 'ipd-dr-lab-result.php';*/ ?> </div>
     
            <div class="tab-pane fade" id="pills-x_ray" role="tabpanel" aria-labelledby="pills-x_ray-tab">...</div>
            <div class="tab-pane fade" id="pills-scan" role="tabpanel" aria-labelledby="pills-scan-tab">...</div>
            <?php
            // if($can_access_order_tab){ ?>
                <div class="tab-pane fade show active" id="pills-order" role="tabpanel" aria-labelledby="pills-order-tab">
                    <?php /*require_once 'ipd-dr-order.php';*/ ?>
                </div>
                <?php
            // }?>
            <div class="tab-pane fade" id="pills-vs" role="tabpanel" aria-labelledby="pills-vs-tab">
                <div>
                    <div class="" id="show-chart-table">
                        <?php /*require_once 'ipd-vital-sign-show-chart.php';*/?>
                        <?php /*require_once 'ipd-vital-sign-show-table.php'; */?>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="pills-io" role="tabpanel" aria-labelledby="pills-io-tab"><?php /*require_once 'ipd-vital-sign-io-1.php'; */?></div>
            <div class="tab-pane fade" id="pills-document" role="tabpanel" aria-labelledby="pills-document-tab"><?php require_once 'ipd-nurse-document.php'; ?></div>
            <div class="tab-pane fade" id="pills-consult" role="tabpanel" aria-labelledby="pills-consult-tab"><?php /*require_once 'ipd-dr-consult.php'; */?></div>    
            <div class="tab-pane fade" id="pills-his_or" role="tabpanel" aria-labelledby="pills-his_or-tab">...</div>
            <div class="tab-pane fade" id="pills-nurse_note" role="tabpanel" aria-labelledby="pills-nurse_note-tab"><div class="row"><?php /*require_once 'ipd-nurse-focus-note-table-all-searchDate.php'; */?></div></div>
        </div>
    </div>

