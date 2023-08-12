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
    WHERE ovst.hn=:hn
    order by ovst.vn desc limit 1";

    $stmt_opd = $conn->prepare($sql_opd);
    $stmt_opd->execute(['hn'=>$hn]);

    while ($row_opd = $stmt_opd->fetch()){

        $covid_lab_results = KphisQueryUtils::getCovidLatestLabResult($row_opd['cid'], $row_opd['passport_no']);
        ?>
        <div class="patient-info-container alert alert-secondary" role="alert">
            <div class="d-flex">

                <div class="p-1 flex">
                    <h5 class="alert-heading">ข้อมูลผู้ป่วย</h5>
                    <label>HN : <?=htmlspecialchars($row_opd['hn']);?> | VN : <?=htmlspecialchars($row_opd['vn'])?></label>

                    | <label>ชื่อ - สกุล : <?=htmlspecialchars($row_opd['pname'].$row_opd['fname']." ".$row_opd['lname'])?> | </label>
                    <label>อายุ : <?=htmlspecialchars($row_opd['age_y']." ปี ".$row_opd['age_m']." เดือน ".$row_opd['age_d']." วัน ")?> | </label>
                    <label>สิทธิ : (<?=htmlspecialchars($row_opd['pttype'])?>) <?=htmlspecialchars($row_opd['pttype_name'])?></label>
                    <br>
                    <?php
                        $origDate = $row_opd['vstdate'];//วันที่ Admit
                        $newDate  = date("d/m/Y", strtotime($origDate));
                        $origTime = $row_opd['vsttime'];//เวลาที่ Admit
                        $newTime  = date('H:i', strtotime($origTime));
                    ?>
                    <label class="text-primary">วันที่ Visit : <?=htmlspecialchars($newDate." ".$newTime);?></label>
                    <?php
                        if($row_opd['drugallergy'] != ""){
                            if($row_opd['drugallergy'] != ""){?>
                                | <label class="text-danger font-weight-bold">แพ้ยา : <?=htmlspecialchars($row_opd['drugallergy']);?></label><?php
                            }

                        } else {?>
                            | <label>ยังไม่มีข้อมูลการแพ้ยา</label><?php
                        }
                        if($row_opd_er['latest_bw'] != null && $row_opd['latest_bw'] != ""){?>
                            | <label>น้ำหนักตัวล่าสุด : <?=htmlspecialchars((float)($row_opd['latest_bw']));?> kg (<?=htmlspecialchars(date("d/m/Y H:i", strtotime($row_opd['latest_bw_datetime'])))?>) </label><?php
                        }
                    ?>

                </div>
            </div>
        </div>
    <?php } ?>
