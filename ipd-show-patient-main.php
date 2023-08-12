<?php
   require_once './include/Session.php';
   //Session::checkLoginSessionAndShowMessage();
   require_once './include/DbUtils.php';
   require_once './include/KphisQueryUtils.php';
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
        
        $covid_lab_results = KphisQueryUtils::getCovidLatestLabResult($row_ipt['cid'], $row_ipt['passport_no']);
        ?>
        <div class="patient-info-container alert alert-secondary" role="alert" style="z-index: 600;">
            <div class="d-flex">
               
                <div class="p-1 flex">
                    <h5 class="alert-heading">ข้อมูลผู้ป่วย</h5>
                    <label>HN : <?=htmlspecialchars($row_ipt['hn']);?> | AN : <?=htmlspecialchars($row_ipt['an'])?> | </label>
                    <label>ชื่อ - สกุล : <?=htmlspecialchars($row_ipt['pname'].$row_ipt['fname']." ".$row_ipt['lname'])?> | </label>
                    <label>อายุ : <?=htmlspecialchars($row_ipt['age_y']." ปี ".$row_ipt['age_m']." เดือน ".$row_ipt['age_d']." วัน ")?> | </label>
                    <label>ตึก : <?=htmlspecialchars($row_ipt['ward_name'])?> | </label>
                    <label>เตียง : <?=htmlspecialchars($row_ipt['bedno'])?> | </label>
                    <label>สิทธิ : (<?=htmlspecialchars($row_ipt['pttype'])?>) <?=htmlspecialchars($row_ipt['pttype_name'])?></label>
                    <br>
                    <?php
                        $origDate = $row_ipt['regdate'];//วันที่ Admit
                        $newDate  = date("d/m/Y", strtotime($origDate));
                        $origTime = $row_ipt['regtime'];//เวลาที่ Admit
                        $newTime  = date('H:i', strtotime($origTime));
                    ?>
                    <label class="text-primary">วันที่ Admit : <?=htmlspecialchars($newDate." ".$newTime);?></label>
                    <?php if($row_ipt['dchdate'] != ""){
                        $origDate = $row_ipt['dchdate'];//วันที่ Discharge
                        $dchdate  = date("d/m/Y", strtotime($origDate));
                        $origTime = $row_ipt['dchtime'];//เวลาที่ Discharge
                        $dchtime  = date('H:i', strtotime($origTime));
                        ?>
                        | <label class="text-warning">วันที่ Discharge : <?=htmlspecialchars($dchdate." ".$dchtime);?> - Discharge Status: (<?=htmlspecialchars($row_ipt['dchstts_name'])?>)</label>
                    <?php } ?>
                    <?php
                        if($row_ipt['drugallergy'] != "" || $row_ipt['allergy_drug_history'] != "" || $row_ipt['er_drugallergy_history'] != ""){
                            if($row_ipt['drugallergy'] != ""){?>
                                | <label class="text-danger font-weight-bold">แพ้ยา : <?=htmlspecialchars($row_ipt['drugallergy']);?></label><?php
                            }
                            if($row_ipt['er_drugallergy_history'] != ""){?>
                                | <label class="text-danger font-weight-bold allergyDrugHistoryFromAdmissionNoteLabel">แจ้งแพ้ยา (ER) : <?=htmlspecialchars($row_ipt['er_drugallergy_history']);?></label><?php
                            }
                            if($row_ipt['allergy_drug_history'] != "" && $row_ipt['allergy_drug_pharmacy_check_person'] == ""){
                                ?>
                                <span class="allergyDrugHistoryFromAdmissionNote">
                                | <label class="text-danger font-weight-bold allergyDrugHistoryFromAdmissionNoteLabel">
                                    แจ้งแพ้ยา (แรกรับ) : <?php
                                    $allergy_drug_history_array = explode(" ",$row_ipt['allergy_drug_history']);
                                    $y = 0;
                                    for ($x = 0; $x < (count($allergy_drug_history_array)-1)/2; $x++) {
                                        echo ($x>0) ? ',' : '';
                                        ?><?=htmlspecialchars($allergy_drug_history_array[$y++]);?>=<?=htmlspecialchars($allergy_drug_history_array[$y++]);?><?php
                                    }
                                    ?> (รอเภสัชประเมิน)
                                    </label>
                                </span>
                                <?php
                            }
                            if($row_ipt['admission_note_id'] != ""){
                                ?>
                                <script>
                                    const ADMISSION_NOTE_DRUG_ALLERGY_CHECK = <?=json_encode(SessionManager::checkPermission('ADMISSION_NOTE_DRUG_ALLERGY','CHECK'))?>;
                                    if(ADMISSION_NOTE_DRUG_ALLERGY_CHECK){
                                        $('.allergyDrugHistoryFromAdmissionNoteLabel').click(function (event) {
                                        allergyDrugHistoryCheck(event, this);
                                        }).css("cursor", "pointer");
                                    }
                                    function allergyDrugHistoryCheck(event){
                                        if(confirm("ยืนยันตรวจสอบการแพ้ยา")){
                                            $.ajax('ipd-dr-admission-note-pharmacy-check-save.php',{
                                                type: 'POST',
                                                data: {
                                                    an: <?=json_encode($row_ipt['an'])?>,
                                                },
                                                success: function(data){
                                                    $('.allergyDrugHistoryFromAdmissionNote').hide();
                                                }
                                            });
                                        }
                                    }
                                </script>
                                <?php
                            }
                        } else {?>
                            | <label>ยังไม่มีข้อมูลการแพ้ยา</label><?php
                        }
                        if($row_ipt['latest_bw'] != null && $row_ipt['latest_bw'] != ""){?>
                            | <label>น้ำหนักตัวล่าสุด : <?=htmlspecialchars((float)($row_ipt['latest_bw']));?> kg (<?=htmlspecialchars(date("d/m/Y H:i", strtotime($row_ipt['latest_bw_datetime'])))?>) </label><?php
                        }
                    ?>
                    <?php if($covid_lab_results != null && !empty($covid_lab_results)
                            && $covid_lab_results[0]['status'] != null){?>
                            |
                            <label class="font-weight-bold">
                                SARS-CoV-2 : <?php
                                if($covid_lab_results[0]['status'] == 'APPROVE'){
                                    echo htmlspecialchars("รายงานผลแล้ว");
                                } else if($covid_lab_results[0]['status'] == 'SENTDING'){
                                    echo htmlspecialchars("รอผล");
                                } else {
                                    echo htmlspecialchars("อื่นๆ");
                                }
                                ?>
                                <?php if($covid_lab_results[0]['sent_date'] != null){?>
                                (ส่งตรวจ: <?=htmlspecialchars(date("d/m/Y", strtotime($covid_lab_results[0]['sent_date'])))?>)
                                <?php } ?>
                            </label>
                    <?php } ?>
                </div>
            </div>
        </div>
    <?php } ?>