<?php
    require_once './project/function/SessionManager.php';
    SessionManager::checkLoginSessionAndShowMessage();
    require_once './project/function/DbUtils.php';
    // require_once './vendor/autoload.php';

    $conn = DbUtils::get_hosxp_connection();
    $opd_er_order_master_id = $_REQUEST['opd_er_order_master_id'];//รับค่า opd_er_order_master_id

    $sql_opd_er = "select ovst.vn, patient.hn,patient.pname,patient.fname,patient.lname,
                (select GROUP_CONCAT(concat(opd_allergy.agent,'=',if(opd_allergy.symptom is null,',',opd_allergy.symptom)/*,' [',if(note is null,',',note),']'*/)) as name
                    from ".KphisConstant::HOSXP_CONNECTION_HOSXP_DBNAME.".opd_allergy
                    where opd_allergy.hn = ovst.hn /*and (opd_allergy.no_alert<>'Y' or opd_allergy.no_alert is null)*/
                    order by display_order) as drugallergy,
                (select GROUP_CONCAT(concat(er_allergy_history_agent,'=',if(er_allergy_history_symptom is null,',',er_allergy_history_symptom)/*,' [',if(note is null,',',note),']'*/)) as name
                    from ".KphisConstant::HOSXP_CONNECTION_KPHIS_DBNAME.".opd_er_allergy_history
                    where opd_er_allergy_history.opd_er_order_master_id = om.opd_er_order_master_id
                    order by er_allergy_history_id) as er_drugallergy_history,
                vn_stat.age_y,vn_stat.age_m,vn_stat.age_d,
                ovst.vstdate,ovst.vsttime,
                ovst.pttype, pttype.`name` as pttype_name,
                (select vs.bw from opd_er_vs_vital_sign vs where vs.opd_er_order_master_id = om.opd_er_order_master_id and vs.bw is not null and trim(vs.bw) <> '' order by vs_datetime desc limit 1) as latest_bw,
                (select vs.vs_datetime from opd_er_vs_vital_sign vs where vs.opd_er_order_master_id = om.opd_er_order_master_id and vs.bw is not null and trim(vs.bw) <> '' order by vs_datetime desc limit 1) as latest_bw_datetime
                from ".KphisConstant::HOSXP_CONNECTION_KPHIS_DBNAME.".opd_er_order_master om
                inner join ".KphisConstant::HOSXP_CONNECTION_HOSXP_DBNAME.".ovst on om.vn=ovst.vn
                left outer join ".KphisConstant::HOSXP_CONNECTION_HOSXP_DBNAME.".vn_stat on vn_stat.vn=ovst.vn
                left outer join ".KphisConstant::HOSXP_CONNECTION_HOSXP_DBNAME.".patient on patient.hn=ovst.hn
                LEFT OUTER JOIN ".KphisConstant::HOSXP_CONNECTION_HOSXP_DBNAME.".pttype ON pttype.pttype = ovst.pttype
                WHERE om.opd_er_order_master_id=:opd_er_order_master_id
                order by ovst.vn ";
    $stmt_opd_er = $conn->prepare($sql_opd_er);
    $stmt_opd_er->execute(['opd_er_order_master_id'=>$opd_er_order_master_id]);

    while ($row_opd_er = $stmt_opd_er->fetch()){
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
                <div class="pt-1 pl-1 pb-1 pr-3 flex">
                    <img src="get-patient-image.php?hn=<?=urlencode($row_opd_er['hn'])?>" class="img-thumbnail patient-info-patient-image" alt="รูปผู้ป่วย"/>
                </div>
                <div class="p-1 flex">
                    <h5 class="alert-heading patient-info-heading">ข้อมูลผู้ป่วย</h5>
                    <label class="patient-info-hn-vn">HN : <?=htmlspecialchars($row_opd_er['hn']);?> | VN : <?=htmlspecialchars($row_opd_er['vn'])?> | </label>
                    <label class="patient-info-name">ชื่อ - สกุล : <?=htmlspecialchars($row_opd_er['pname'].$row_opd_er['fname']." ".$row_opd_er['lname'])?> | </label>
                    <label class="patient-info-age">อายุ : <?=htmlspecialchars($row_opd_er['age_y']." ปี ".$row_opd_er['age_m']." เดือน ".$row_opd_er['age_d']." วัน ")?> | </label>
                    <label class="patient-info-pttype-name">สิทธิ : (<?=htmlspecialchars($row_opd_er['pttype'])?>) <?=htmlspecialchars($row_opd_er['pttype_name'])?></label>
                    <br class="patient-info-br">
                    <?php
                        $origDate = $row_opd_er['vstdate'];//วันที่ Admit
                        $newDate  = date("d/m/Y", strtotime($origDate));
                        $origTime = $row_opd_er['vsttime'];//เวลาที่ Admit
                        $newTime  = date('H:i', strtotime($origTime));
                    ?>
                    <label class="patient-info-regdate text-primary">วันที่ Visit : <?=htmlspecialchars($newDate." ".$newTime);?></label>
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