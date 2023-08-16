<?php
require_once '../mains/datethai.php';
   //require_once __DIR__ . '/vendor/autoload.php';
   //$_SERVER['DOCUMENT_ROOT'] .
   require_once '../include/Session.php';
    Session::checkLoginSessionAndShowMessage();
   //Session::checkLoginSessionAndShowMessage();
   require_once '../include/DbUtils.php';
   require_once '../include/KphisQueryUtils.php';
// require_once './vendor/autoload.php';
// use \Fuko\Masked\Protect;
// use \Fuko\Masked\Redact;

$conn = DbUtils::get_hosxp_connection();
$an = $_REQUEST['an'];//รับค่า an

$sql_ipt = "select ipt.an, patient.cid, patient.passport_no, patient.hn,patient.pname,patient.fname,patient.lname,/*patient.drugallergy, */
            (select GROUP_CONCAT(concat(opd_allergy.agent,'=',if(opd_allergy.symptom is null,',',opd_allergy.symptom)/*,' [',if(note is null,',',note),']'*/)) as name
                from ".DbConstant::HOSXP_DBNAME.".opd_allergy
                where opd_allergy.hn = ipt.hn /*and (opd_allergy.no_alert<>'Y' or opd_allergy.no_alert is null)*/
                order by display_order) as drugallergy,
            (select GROUP_CONCAT(concat(er_allergy_history_agent,'=',if(er_allergy_history_symptom is null,',',er_allergy_history_symptom)/*,' [',if(note is null,',',note),']'*/)) as name
                from ".DbConstant::KPHIS_DBNAME.".opd_er_allergy_history
                where opd_er_allergy_history.opd_er_order_master_id = om.opd_er_order_master_id
                order by er_allergy_history_id) as er_drugallergy_history,
            dan.admission_note_id,
            dan.allergy_drug_history,
            dan.allergy_drug_history_hosxp,
            dan.allergy_drug_pharmacy_check_person,
            dan.allergy_drug_pharmacy_check_datetime,
            an_stat.age_y,an_stat.age_m,an_stat.age_d,
            ipt.regdate,ipt.regtime,
            ipt.dchdate,ipt.dchtime,ipt.dchstts, dchstts.`name` as dchstts_name,
            ipt.ward,ward.name as ward_name,
            ipt.pttype, pttype.`name` as pttype_name,
            iptadm.bedno, (select vs.bw from ipd_vs_vital_sign vs where vs.an = ipt.an and vs.bw is not null and trim(vs.bw) <> '' order by vs_datetime desc limit 1) as latest_bw,
            (select vs.vs_datetime from ipd_vs_vital_sign vs where vs.an = ipt.an and vs.bw is not null and trim(vs.bw) <> '' order by vs_datetime desc limit 1) as latest_bw_datetime
            from ".DbConstant::HOSXP_DBNAME.".ipt
            left outer join ".DbConstant::HOSXP_DBNAME.".an_stat on an_stat.an=ipt.an
            left outer join ".DbConstant::HOSXP_DBNAME.".patient on patient.hn=ipt.hn
            left outer join ".DbConstant::HOSXP_DBNAME.".ward on ward.ward=ipt.ward
            LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".pttype ON pttype.pttype = ipt.pttype
            LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".iptadm ON iptadm.an = ipt.an
            LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".dchstts ON dchstts.dchstts = ipt.dchstts
            left outer join ".DbConstant::KPHIS_DBNAME.".ipd_dr_admission_note dan on dan.an=ipt.an
            left outer join ".DbConstant::KPHIS_DBNAME.".opd_er_order_master om on om.vn = ipt.vn
            WHERE ipt.an=:an
            order by ipt.an
            ";

            $stmt_ipt = $conn->prepare($sql_ipt);
            $stmt_ipt->execute(['an'=>$an]);

while ($row_ipt = $stmt_ipt->fetch()){

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
                    <label class="patient-info-hn-vn">HN : <?=htmlspecialchars($row_ipt['hn']);?> | AN : <?=htmlspecialchars($row_ipt['an'])?> | </label>
                    <label class="patient-info-name">ชื่อ - สกุล : <?=htmlspecialchars($row_ipt['pname'].$row_ipt['fname']." ".$row_ipt['lname'])?> | </label>
                    <label class="patient-info-age">อายุ : <?=htmlspecialchars($row_ipt['age_y']." ปี ".$row_ipt['age_m']." เดือน ".$row_ipt['age_d']." วัน ")?> | </label>
                    <label>ตึก : <?=htmlspecialchars($row_ipt['ward_name'])?> | </label>
                    <label>เตียง : <?=htmlspecialchars($row_ipt['bedno'])?> | </label>
                    <label>สิทธิ : (<?=htmlspecialchars($row_ipt['pttype'])?>) <?=htmlspecialchars($row_ipt['pttype_name'])?></label>
                    <br class="patient-info-br">

                   <?php
                  

                        $origDate = $row_ipt['regdate'];//วันที่ Admit
                        $newDate  = date("d/m/Y", strtotime($origDate));
                        $origTime = $row_ipt['regtime'];//เวลาที่ Admit
                        $newTime  = date('H:i', strtotime($origTime));
                        $newDate2 = date($origDate);
                        $newTime2 = date($origTime);
                        $strDate =($newDate2." ".$newTime2);
                       // echo "ThaiCreate.Com Time now : ".DateThai($strDate);
                    ?>
                    <label class="text-primary">วันที่ Admit : <?=LongDateThai($strDate);?></label>
                    <?php if($row_ipt['dchdate'] != ""){
                        $origDate = $row_ipt['dchdate'];//วันที่ Discharge
                        $dchdate  = date("d/m/Y", strtotime($origDate));
                        $origTime = $row_ipt['dchtime'];//เวลาที่ Discharge
                        $dchtime  = date('H:i', strtotime($origTime));
                        
                        ?>

                        | <label class="text-warning">วันที่ Discharge : <?=htmlspecialchars($dchdate." ".$dchtime);?> - Discharge Status: (<?=htmlspecialchars($row_ipt['dchstts_name'])?>)</label>
                    <?php } ?>

                   
                    <?php
                        if($row_ipt['drugallergy'] != "" ){
                            if($row_ipt['drugallergy'] != ""){?>
                                | <label class="text-danger font-weight-bold">แพ้ยา : <?=htmlspecialchars($row_ipt['drugallergy']);?></label><?php
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
