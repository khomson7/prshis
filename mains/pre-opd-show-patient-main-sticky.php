<?php
require_once '../include/Session.php';
Session::checkLoginSessionAndShowMessage();
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';
// require_once './vendor/autoload.php';
// use \Fuko\Masked\Protect;
// use \Fuko\Masked\Redact;

$conn = DbUtils::get_hosxp_connection();
$hn = $_REQUEST['hn'];

$vn = KphisQueryUtils::getVnByHn($hn);
$an_parameters = ['an' => $an];
$hn_parameters = ['hn' => $hn];
$loginname = $_SESSION['loginname'];
$values =['loginname'=>$loginname];

$sql_opd = "select ovst.vn, ovst.an, patient.cid, patient.passport_no, patient.hn,patient.pname,patient.fname,patient.lname
,(select GROUP_CONCAT(concat(opd_allergy.agent,'=',if(opd_allergy.symptom is null,',',opd_allergy.symptom))) as name
from ".DbConstant::HOSXP_DBNAME.".opd_allergy
where opd_allergy.hn = ovst.hn
order by display_order) as drugallergy,
vn_stat.age_y,vn_stat.age_m,vn_stat.age_d,
ovst.vstdate,ovst.vsttime,
ovst.pttype, pttype.`name` as pttype_name,patient.cid,patient.passport_no
from   ".DbConstant::HOSXP_DBNAME.".ovst
left outer join ".DbConstant::HOSXP_DBNAME.".vn_stat on vn_stat.vn=ovst.vn
left outer join ".DbConstant::HOSXP_DBNAME.".patient on patient.hn=ovst.hn
LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".pttype ON pttype.pttype = ovst.pttype
WHERE ovst.hn=:hn and concat(ovst.hn,ovst.vstdate) in(select concat(om.hn,om.order_for_date) from ".DbConstant::KPHIS_DBNAME.".ipd_pre_order_master om)
order by ovst.vn desc limit 1";

$stmt_opd = $conn->prepare($sql_opd);
$stmt_opd->execute(['hn'=>$hn]);

while ($row_opd = $stmt_opd->fetch()){

    ?>
        <style>
            .patient-info-patient-image {
                width: 120px;
                transition: 0.2s;
            }
            .patient-info-patient-image.small-header {
                width: 30px !important;
                transition: 0.2s;
            }
        </style>
        <div class="patient-info-container-mini d-none d-print-none alert alert-secondary fixed-top" role="alert" style="z-index: 601;">
            <div class="d-flex">

                <div class="p-1 flex">
                    <h5 class="alert-heading patient-info-heading">ข้อมูลผู้ป่วย</h5>
                    <label class="patient-info-hn-vn">HN : <?=htmlspecialchars($row_opd['hn']);?> | VN : <?=htmlspecialchars($row_opd['vn'])?> | </label>
                    <label class="patient-info-name">ชื่อ - สกุล : <?=htmlspecialchars($row_opd['pname'].$row_opd['fname']." ".$row_opd['lname'])?> | </label>
                    <label class="patient-info-age">อายุ : <?=htmlspecialchars($row_opd['age_y']." ปี ".$row_opd['age_m']." เดือน ".$row_opd['age_d']." วัน ")?> | </label>
                    <label class="patient-info-pttype-name">สิทธิ : (<?=htmlspecialchars($row_opd['pttype'])?>) <?=htmlspecialchars($row_opd['pttype_name'])?></label>
                    <br class="patient-info-br">
                    <?php
                        $origDate = $row_opd['vstdate'];//วันที่ Admit
                        $newDate  = date("d/m/Y", strtotime($origDate));
                        $origTime = $row_opd['vsttime'];//เวลาที่ Admit
                        $newTime  = date('H:i', strtotime($origTime));
                    ?>
                    <label class="patient-info-regdate text-primary">วันที่ Visit : <?=htmlspecialchars($newDate." ".$newTime);?></label>
                    <?php
                        if($row_opd['drugallergy'] != "" ){
                            if($row_opd['drugallergy'] != ""){?>
                                | <label class="text-danger font-weight-bold">แพ้ยา : <?=htmlspecialchars($row_opd['drugallergy']);?></label><?php
                            }

                        } else {?>
                            | <label>ยังไม่มีข้อมูลการแพ้ยา</label><?php
                        }

                    ?>
                </div>
            </div>
        </div>
        <script>
            $(window).scroll(function() {
                // if (document.documentElement.scrollTop > $('.patient-info-container').position().top + $('.patient-info-container').outerHeight(true)) {}
                // if (document.documentElement.scrollTop > $('.patient-info-container').offset().top) {
                if (window.pageYOffset > $('.patient-info-container').offset().top) {
                    $('.patient-info-container-mini').removeClass('d-none');
                    $('.patient-info-patient-image').addClass('small-header');
                    $('.patient-info-heading').addClass('d-none');
                    $('.patient-info-pttype-name').addClass('d-none');
                    $('.patient-info-br').addClass('d-none');
                } else {
                    $('.patient-info-container-mini').addClass('d-none');
                    $('.patient-info-patient-image').removeClass('small-header');
                    $('.patient-info-heading').removeClass('d-none');
                    $('.patient-info-pttype-name').removeClass('d-none');
                    $('.patient-info-br').removeClass('d-none');
                }
            });
        </script>
    <?php } ?>
