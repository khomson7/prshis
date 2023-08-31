<?php

    require_once '../include/Session.php';
    Session::checkLoginSessionAndShowMessage();
    require_once '../include/DbUtils.php';
    require_once '../include/KphisQueryUtils.php';
    // require_once './vendor/autoload.php';
    // use \Fuko\Masked\Protect;
    // use \Fuko\Masked\Redact;

    $conn = DbUtils::get_hosxp_connection();
    $opd_er_order_master_id = $_REQUEST['opd_er_order_master_id'];//รับค่า opd_er_order_master_id

    

    $sql_opd_er = "select om.opd_er_order_master_id,ovst.vn, ovst.an, patient.cid, patient.passport_no, patient.hn,patient.pname,patient.fname,patient.lname,
                (select GROUP_CONCAT(concat(opd_allergy.agent,'=',if(opd_allergy.symptom is null,',',opd_allergy.symptom)/*,' [',if(note is null,',',note),']'*/)) as name
                    from ".DbConstant::HOSXP_DBNAME.".opd_allergy
                    where opd_allergy.hn = ovst.hn /*and (opd_allergy.no_alert<>'Y' or opd_allergy.no_alert is null)*/
                    order by display_order) as drugallergy,
                (select GROUP_CONCAT(concat(er_allergy_history_agent,'=',if(er_allergy_history_symptom is null,',',er_allergy_history_symptom)/*,' [',if(note is null,',',note),']'*/)) as name
                    from ".DbConstant::KPHIS_DBNAME.".opd_er_allergy_history
                    where opd_er_allergy_history.opd_er_order_master_id = om.opd_er_order_master_id
                    order by er_allergy_history_id) as er_drugallergy_history,
                vn_stat.age_y,vn_stat.age_m,vn_stat.age_d,
                ovst.vstdate,ovst.vsttime,
                ovst.pttype, pttype.`name` as pttype_name,
                (select vs.bw from opd_er_vs_vital_sign vs where vs.opd_er_order_master_id = om.opd_er_order_master_id and vs.bw is not null and trim(vs.bw) <> '' order by vs_datetime desc limit 1) as latest_bw,
                (select vs.vs_datetime from opd_er_vs_vital_sign vs where vs.opd_er_order_master_id = om.opd_er_order_master_id and vs.bw is not null and trim(vs.bw) <> '' order by vs_datetime desc limit 1) as latest_bw_datetime
                from ".DbConstant::KPHIS_DBNAME.".opd_er_order_master om
                inner join ".DbConstant::HOSXP_DBNAME.".ovst on om.vn=ovst.vn
                left outer join ".DbConstant::HOSXP_DBNAME.".vn_stat on vn_stat.vn=ovst.vn
                left outer join ".DbConstant::HOSXP_DBNAME.".patient on patient.hn=ovst.hn
                LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".pttype ON pttype.pttype = ovst.pttype
                WHERE om.opd_er_order_master_id=:opd_er_order_master_id
                order by ovst.vn ";
    $stmt_opd_er = $conn->prepare($sql_opd_er);
    $stmt_opd_er->execute(['opd_er_order_master_id'=>$opd_er_order_master_id]);

    while ($row_opd_er = $stmt_opd_er->fetch()){
        $covid_lab_results = KphisQueryUtils::getCovidLatestLabResult($row_opd_er['cid'], $row_opd_er['passport_no']);
        ?>
        <div class="patient-info-container alert alert-secondary" role="alert">
            <div class="d-flex">
                
                <div class="p-1 flex">
                    <h5 class="alert-heading">ข้อมูลผู้ป่วย</h5>
                    <label>HN : <?=htmlspecialchars($row_opd_er['hn']);?> | VN : <?=htmlspecialchars($row_opd_er['vn'])?> <?=htmlspecialchars($row_opd_er['opd_er_order_master_id']);?></label>
                    <?php if($row_opd_er['an'] != null && $row_opd_er['an'] != ""){ ?>
                        | <label>AN : <?=htmlspecialchars($row_opd_er['an']);?></label>
                    <?php } ?>
                    | <label>ชื่อ - สกุล : <?=htmlspecialchars($row_opd_er['pname'].$row_opd_er['fname']." ".$row_opd_er['lname'])?> | </label>
                    <label>อายุ : <?=htmlspecialchars($row_opd_er['age_y']." ปี ".$row_opd_er['age_m']." เดือน ".$row_opd_er['age_d']." วัน ")?> | </label>
                    <label>สิทธิ : (<?=htmlspecialchars($row_opd_er['pttype'])?>) <?=htmlspecialchars($row_opd_er['pttype_name'])?></label>
                    <br>
                    <?php
                        $origDate = $row_opd_er['vstdate'];//วันที่ Admit
                        $newDate  = date("d/m/Y", strtotime($origDate));
                        $origTime = $row_opd_er['vsttime'];//เวลาที่ Admit
                        $newTime  = date('H:i', strtotime($origTime));
                    ?>
                    <label class="text-primary">วันที่ Visit : <?=htmlspecialchars($newDate." ".$newTime);?></label>
                    <?php
                        if($row_opd_er['drugallergy'] != "" || $row_opd_er['er_drugallergy_history'] != ""){
                            if($row_opd_er['drugallergy'] != ""){?>
                                | <label class="text-danger font-weight-bold">แพ้ยา : <?=htmlspecialchars($row_opd_er['drugallergy']);?></label><?php
                            }
                            if($row_opd_er['er_drugallergy_history'] != ""){?>
                                    | <label class="text-danger font-weight-bold">แจ้งแพ้ยา (ER) : <?=htmlspecialchars($row_opd_er['er_drugallergy_history']);?></label><?php
                            }
                        } else {?>
                            | <label>ยังไม่มีข้อมูลการแพ้ยา</label><?php
                        }
                        if($row_opd_er['latest_bw'] != null && $row_opd_er['latest_bw'] != ""){?>
                            | <label>น้ำหนักตัวล่าสุด : <?=htmlspecialchars((float)($row_opd_er['latest_bw']));?> kg (<?=htmlspecialchars(date("d/m/Y H:i", strtotime($row_opd_er['latest_bw_datetime'])))?>) </label><?php
                        }
                    ?>
                   
                </div>
            </div>
        </div>
    <?php } ?>