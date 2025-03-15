<?php  // require_once './project/function/Session.php';
require_once '../include/Session.php';
//ตรวจสอบว่า session login ตรงกันหรือไม่
$login = empty($_REQUEST['loginname']) ? null : $_REQUEST['loginname'];
$loginname = $_SESSION['loginname'];
$values = ['loginname' => $loginname];

//หากพบว่าไม่ตรงกันให้ ทำลาย session เดิมทิ้งไป
if ($login != $loginname) {
    session_start();
    session_destroy();
}
require_once '../mains/main-report.php';

//Session::checkLoginSessionAndShowMessage(); //เช็ค session

$permissionCheck = Session::checkPermissionAndShowMessage('PRS_FORM_REHAB', 'VIEW');
$permissionCheckJson = json_encode($permissionCheck);
require_once '../include/session-modal.php';
require_once '../mains/ipd-show-patient-main.php'; //เป็นส่วนที่แสดง ข้อมูลผู้ป่วย เช่น รูป,hn,an,ชื่อ-สกุล,แพ้ยา ฯลฯ
require_once '../mains/ipd-show-patient-sticky.php';

require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';
require_once '../include/ReportQueryUtils.php';

$conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
$an = empty($_REQUEST['an']) ? null : $_REQUEST['an'];

$hn = KphisQueryUtils::getHnByAn($an);
$vn = KphisQueryUtils::getVnByAn($an);
$an_parameters = ['an' => $an];
$hn_parameters = ['hn' => $hn];


Session::insertSystemAccessLog(json_encode(array(
    'form' => 'FORM-REHAB-HISTORY',
    'an' => $an,
), JSON_UNESCAPED_UNICODE));



/*$login = empty($_REQUEST['loginname']) ? null : $_REQUEST['loginname'];
$loginname = $_SESSION['loginname'];
$values = ['loginname' => $loginname];
if ($login != $loginname) {
    session_start();
    session_destroy();
}*/



//echo $_SESSION['name']; 

//----------------------เช็คว่า an นี้ มีข้อมูลหรือไม่
$sql = "SELECT *
                FROM `prs_rehab_history`
                WHERE an = :an";
$stmt = $conn->prepare($sql);
$stmt->execute($an_parameters);
if ($row  = $stmt->fetch()) {
    $admission_note_id = $row['id'];
} else {
    $admission_note_id = null;
}



$sql_ipt = "select patient.sex,patient.hn,patient.pname,patient.fname,patient.lname,/*patient.drugallergy, */
        (select GROUP_CONCAT(concat(opd_allergy.agent,'=',if(opd_allergy.symptom is null,',',opd_allergy.symptom)/*,' [',if(note is null,',',note),']'*/)) as name
            from " . DbConstant::HOSXP_DBNAME . ".opd_allergy
            where opd_allergy.hn = ipt.hn /*and (opd_allergy.no_alert<>'Y' or opd_allergy.no_alert is null)*/
            order by display_order) as drugallergy,
        an_stat.age_y,an_stat.age_m,an_stat.age_d,
        concat(ipt.regdate,' ',ipt.regtime) as regdatetime,
        ipt.dchdate,ipt.dchtime,
        ipt.regdate,ipt.regtime,
        ipt.ward,ward.name,
        ipt.pttype, pttype.`name` as pttype_name,
        iptadm.bedno, (select vs.bw from ipd_vs_vital_sign vs where vs.an = ipt.an and vs.bw is not null and trim(vs.bw) <> '' order by vs_datetime desc limit 1) as latest_bw
        , (select vs.height from ipd_vs_vital_sign vs where vs.an = ipt.an and vs.bw is not null and trim(vs.bw) <> '' order by vs_datetime desc limit 1) as latest_height
        , (select vs.vs_datetime from ipd_vs_vital_sign vs where vs.an = ipt.an and vs.bw is not null and trim(vs.bw) <> '' order by vs_datetime desc limit 1) as latest_bw_datetime
        from " . DbConstant::HOSXP_DBNAME . ".ipt
        left outer join " . DbConstant::HOSXP_DBNAME . ".an_stat on an_stat.an=ipt.an
        left outer join " . DbConstant::HOSXP_DBNAME . ".patient on patient.hn=ipt.hn
        left outer join " . DbConstant::HOSXP_DBNAME . ".ward on ward.ward=ipt.ward
        LEFT OUTER JOIN " . DbConstant::HOSXP_DBNAME . ".pttype ON pttype.pttype = ipt.pttype
        LEFT OUTER JOIN " . DbConstant::HOSXP_DBNAME . ".iptadm ON iptadm.an = ipt.an
        WHERE ipt.an=:an
        order by ipt.an
        ";
$stmt_ipt = $conn->prepare($sql_ipt);
$stmt_ipt->execute(['an' => $an]);
$row_ipt = $stmt_ipt->fetch();
$regdatetime = $row_ipt["regdatetime"];



if ($admission_note_id == null || $admission_note_id != null) {
    $sql_opdscreen = "SELECT opdscreen.vn,opdscreen.hn,opdscreen.cc,opdscreen.hpi,concat(round(opdscreen.bpd,0),'/',round(opdscreen.bps,0)) as bp,
                                    pt.sex,round(opdscreen.bps,0) as sbp,round(opdscreen.bpd,0) as dbp,
                                    round(opdscreen.pulse,0) as pr,round(opdscreen.rr,0) as rr,round(opdscreen.temperature,1) as bt,
                                    round((opdscreen.bw)*1000,0) as bw2,
                                    round(opdscreen.bw,1) as bw,round(opdscreen.height,1) as height,
                                    opdscreen.pe_ga_text, opdscreen.pe_heent_text,opdscreen.hpi,
                                    opdscreen.pmh,opdscreen.fh,opdscreen.pe,
                                    opdscreen.pe_heart_text, opdscreen.pe_lung_text,
                                    opdscreen.pe_ab_text, opdscreen.pe_neuro_text,
                                    opdscreen.pe_ext_text, opdscreen.pe, pt.cid, pt.passport_no, pt.hn,pt.pname,pt.fname,pt.lname,
                                    vn.age_y,vn.age_m,vn.age_d,opdscreen.bw,opdscreen.height,(select oi.name from " . DbConstant::HOSXP_DBNAME . ".ovstist oi where oi.ovstist = ov.ovstist) as ovst_ist
                                    FROM " . DbConstant::HOSXP_DBNAME . ".opdscreen
                                    INNER JOIN " . DbConstant::HOSXP_DBNAME . ".ovst ov on ov.vn = opdscreen.vn
                                    INNER JOIN " . DbConstant::HOSXP_DBNAME . ".vn_stat vn on vn.vn = opdscreen.vn
                                    INNER JOIN " . DbConstant::HOSXP_DBNAME . ".patient pt on pt.hn = opdscreen.hn
                                    WHERE opdscreen.vn= :vn ";
    $stmt_opdscreen = $conn->prepare($sql_opdscreen);
    $stmt_opdscreen->execute(['vn' => $vn]);
    $row_opdscreen  = $stmt_opdscreen->fetch();
}


date_default_timezone_set('asia/bangkok');
//แก้ไข
$id = '27'; //Link menu
$check_    = ReportQueryUtils::getProduction($id)


?>

<style>
    .main {
        border: 1px solid #4287f5;
        height: 180px;
        width: 500px;
        position: relative;
    }

    .column1 {
        color: #4287f5;
        text-align: center;
    }

    .column2 {
        text-align: center;
    }

    #bottom {
        position: absolute;
        bottom: 0;
        left: 0;
    }

    .top-container {
        background-color: #f1f1f1;
        padding: 30px;
        text-align: center;
    }

    .header {
        padding: 10px 16px;
        background: #555;
        color: #f1f1f1;
    }

    .content {
        padding: 16px;
    }

    .sticky {
        position: fixed;
        top: 0;
        width: 100%;
    }

    .sticky+.content {
        padding-top: 102px;
    }
</style>


<div id="formContainer">
    <form id="my_form">
        <div class="container-fluid">
            <div class="row">
                <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-primary btn-block" onclick="window.close()"><i class="fas fa-arrow-left"></i> กลับ</button>
                </div>
                <div class="col-auto p-1 font-weight-bold">
                    <h5><B>แบบบันทึกการให้บริการทางกายภาพบำบัด <?= htmlspecialchars(DbConstant::HOSPITAL_NAME) ?>
                            <?php if ($check_ == "1") { ?>

                                <font color="red">ช่วงทดลอง</font>
                            <?php } else { ?>

                            <? } ?>
                        </B></h5>
                </div>

            </div>


            <div class="card-group pb-3 ">
                <div class="card">
                    <div class="card-body" style=" overflow-y: auto;">
                        <div class="row">
                            <div class="col-md-12">




                                <div class="form-group row alert alert-dark text-left">
                                    <B>ส่วนที่1</B>
                                </div>

                                <div class="row">

                                    <div class="col-sm-1"></div>
                                    <label>วันเดือนปี</label>
                                    <div class="col-sm-2">
                                        <input type="date" class="form-control form-control-sm" id="rxdate" name="rxdate" value="<?= (isset($row['rxdate']) ? htmlspecialchars($row['rxdate']) : '') ?>">
                                    </div>


                                </div>
                                <br>

                                <div class="row">

                                    &nbsp;&nbsp;&nbsp;&nbsp;<label>อาการสำคัญ&nbsp;</label>

                                    <div class="col-sm-10">
                                        <textarea class="form-control" id="cc" name="cc" rows="2"><?= (isset($row_opdscreen['cc'])  && $admission_note_id == null ? htmlspecialchars($row_opdscreen['cc']) : htmlspecialchars($row['cc'])) ?></textarea>
                                    </div>


                                </div>
                                <br>
                                <div class="row">

                                    &nbsp;&nbsp;&nbsp;&nbsp;<label>ประวัติปัจจุบัน&nbsp;</label>
                                    <div class="col-sm-10">
                                        <textarea class="form-control" id="hpi" name="hpi" rows="4"><?= (isset($row_opdscreen['hpi'])  && $admission_note_id == null ? htmlspecialchars($row_opdscreen['hpi']) : htmlspecialchars($row['hpi'])) ?></textarea>
                                    </div>

                                </div>
                                <br>

                                <div class="row">

                                    &nbsp;&nbsp;&nbsp;&nbsp;<label>ประวัติการเจ็บป่วยในอดีต&nbsp;</label>
                                    <div class="col-sm-10">
                                        <textarea class="form-control" id="past_history" name="past_history" rows="2"><?= (isset($row['past_history']) ? htmlspecialchars($row['past_history']) : '') ?></textarea>
                                    </div>

                                </div>
                                <br>

                                <div class="row">

                                    &nbsp;&nbsp;&nbsp;&nbsp;<label>สภาพจิตใจและสังคม&nbsp;</label>
                                    
                                    <div class="col-sm-10">
                                        <textarea class="form-control" id="phychosocial" name="phychosocial" rows="2"><?= (isset($row['phychosocial']) ? htmlspecialchars($row['phychosocial']) : '') ?></textarea>
                                    </div>

                                </div>
                                <br>

                                <div class="row">

                                    &nbsp;&nbsp;&nbsp;&nbsp;<label>โรคประจำตัว&nbsp;</label>
                                    <div class="col-sm-5">
                                        <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="disease" id="disease" value="<?= (isset($row['disease']) ? htmlspecialchars($row['disease']) : '') ?>">
                                    </div>

                                </div>
                                <br>

                                <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;<label>การรักษาที่เคยได้รับ&nbsp;</label>
                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="treatment_received" id="treatment_received" value="<?= (isset($row['treatment_received']) ? htmlspecialchars($row['treatment_received']) : '') ?>">
                                </div>
                                </div>
                                <br>

                                <div class="row">

                                    &nbsp;&nbsp;&nbsp;&nbsp;<label>การตรวจประเมินแรกรับ วัน-เดือน-ปี&nbsp;</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="pe_1st" id="pe_1st" value="<?= (isset($row['pe_1st']) ? htmlspecialchars($row['pe_1st']) : '') ?>">
                                    </div>

                                </div>
                                <br>

                                <div class="row">

                                    &nbsp;&nbsp;&nbsp;&nbsp;<label>การวินิจฉัยโรคทางกายภาพบำบัด&nbsp;</label>
                                    <div class="col-sm-5">
                                        <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="diagnosis" id="diagnosis" value="<?= (isset($row['diagnosis']) ? htmlspecialchars($row['diagnosis']) : '') ?>">
                                    </div>

                                </div>
                                <br>
                                <label><b>เป้าหมายและการวางแผนการรักษา</b>&nbsp;</label>
                                <div class="form-group row">
                                    <div class="col-sm-0"></div>

                                    <div class="custom-control custom-checkbox col-sm-2"><label class="text-right"><b>วัน-เดือน-ปี</b></label></div>
                                    <div class="custom-control custom-checkbox col-sm-4"><label class="text-right"><b>Goal</b></label></div>
                                    <div class="custom-control custom-checkbox col-sm-2"><label class="text-right"><b>Due date</b></label></div>
                                    <div class="custom-control custom-checkbox col-sm-4"><label class="text-right"><b>Treatment plan</b></label></div>

                                </div>

                                <div class="form-group row">
                                    <div class="col-sm-0"></div>

                                    <div class="custom-control custom-checkbox col-sm-2">
                                        <input type="date" class="form-control form-control-sm" id="goal_date" name="goal_date" value="<?= (isset($row['goal_date']) ? htmlspecialchars($row['goal_date']) : '') ?>">
                                    </div>
                                    <div class="custom-control custom-checkbox col-sm-4">
                                        <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="goal" id="goal" value="<?= (isset($row['goal']) ? htmlspecialchars($row['goal']) : '') ?>">
                                    </div>
                                    <div class="custom-control custom-checkbox col-sm-2">
                                        <input type="date" class="form-control form-control-sm" id="due_date" name="due_date" value="<?= (isset($row['due_date']) ? htmlspecialchars($row['due_date']) : '') ?>">
                                    </div>
                                    <div class="custom-control custom-checkbox col-sm-4">
                                        <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="treatment_plan" id="treatment_plan" value="<?= (isset($row['treatment_plan']) ? htmlspecialchars($row['treatment_plan']) : '') ?>">
                                    </div>

                                </div>




                            </div>



                        </div>

                        <div class="form-group row alert alert-dark text-left">
                            <B>
                                <font color="red">สรุปผลการรักษา และการจำหน่ายผู้ป่วย</font>
                            </B>
                        </div>

                        <div class="form-group row">
                            <div class="custom-control custom-checkbox col-sm-2"><label>วัน-เดือน-ปี</label>
                                <input type="date" class="form-control form-control-sm" id="summary_date" name="summary_date" value="<?= (isset($row['summary_date']) ? htmlspecialchars($row['summary_date']) : '') ?>">
                            </div>
                        </div>

                        <div class="row">

                            &nbsp;&nbsp;&nbsp;&nbsp;<label>สรุปผลการรักษา</label>
                            <div class="custom-control custom-radio col-sm-1">
                                &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['summary_of_dc'] == '1') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="summary_of_dc1" name="summary_of_dc" value="1">
                                <label class="custom-control-label" for="summary_of_dc1">หายแล้ว</label>
                            </div>
                            <div class="custom-control custom-radio col-sm-1">
                                <input type="radio" <?php if ($row['summary_of_dc'] == '2') {
                                                        echo 'checked="checked"';
                                                    } ?> class="custom-control-input" id="summary_of_dc2" name="summary_of_dc" value="2">
                                <label class="custom-control-label" for="summary_of_dc2">อาการดีขึ้นเป็นที่น่าพอใจ</label>
                            </div>
                            <div class="custom-control custom-radio col-sm-1">
                                <input type="radio" <?php if ($row['summary_of_dc'] == '3') {
                                                        echo 'checked="checked"';
                                                    } ?> class="custom-control-input" id="summary_of_dc3" name="summary_of_dc" value="3">
                                <label class="custom-control-label" for="summary_of_dc3">ส่งต่อไปทำ PT ที่อื่น</label>
                            </div>

                            <div class="custom-control custom-radio col-sm-2">
                                <input type="radio" <?php if ($row['summary_of_dc'] == '4') {
                                                        echo 'checked="checked"';
                                                    } ?> class="custom-control-input" id="summary_of_dc4" name="summary_of_dc" value="4">
                                <label class="custom-control-label" for="summary_of_dc4">จำเป็นต้องได้รับการรักษาด้วยวิธีอื่น</label>
                            </div>
                            <div class="custom-control custom-radio col-sm-2">
                                <input type="radio" <?php if ($row['summary_of_dc'] == '5') {
                                                        echo 'checked="checked"';
                                                    } ?> class="custom-control-input" id="summary_of_dc5" name="summary_of_dc" value="5">
                                <label class="custom-control-label" for="summary_of_dc5">ถูกจำหน่ายออกจาก รพ. ก่อนสิ้นสุดการรักษา</label>
                            </div>

                            <div class="custom-control custom-radio col-sm-2">
                                <input type="radio" <?php if ($row['summary_of_dc'] == '6') {
                                                        echo 'checked="checked"';
                                                    } ?> class="custom-control-input" id="summary_of_dc6" name="summary_of_dc" value="6">
                                <label class="custom-control-label" for="summary_of_dc6">อาการคงเดิมหลังจากได้รับการักษา</label>
                            </div>

                            <div class="custom-control custom-radio col-sm-2">
                                <input type="radio" <?php if ($row['summary_of_dc'] == '7') {
                                                        echo 'checked="checked"';
                                                    } ?> class="custom-control-input" id="summary_of_dc7" name="summary_of_dc" value="7">
                                <label class="custom-control-label" for="summary_of_dc7">แพทย์ให้หยุดการรักษา</label>
                            </div>

                            <div class="custom-control custom-radio col-sm-2">
                                <input type="radio" <?php if ($row['summary_of_dc'] == '8') {
                                                        echo 'checked="checked"';
                                                    } ?> class="custom-control-input" id="summary_of_dc8" name="summary_of_dc" value="8">
                                <label class="custom-control-label" for="summary_of_dc8">เสียชีวิต</label>
                            </div>

                        </div>


                        <div class="row">
                            <div id="show_check_save"></div>
                            <input type="hidden" id="an" name="an" value="<?= htmlspecialchars($an) ?>">
                            <input type="hidden" id="hn" name="hn" value="<?= htmlspecialchars($hn) ?>">
                            <input type="hidden" id="version" name="version" value="<?= htmlspecialchars($row['version']) ?>">
                            <input type="hidden" id="id" name="id" value="<?= htmlspecialchars($row['id']) ?>">
                            <input type="hidden" id="create_user" name="create_user" value="<?= htmlspecialchars($_SESSION['name']) ?>">

                            <div class="col-md-12 text-right">
                                <?php
                                if ((
                                    Session::checkPermission('PRS_FORM_ICU1', 'ADD')
                                ) && (ReportQueryUtils::checkReadOnly($an))) { ?>
                                    <button type="button" class="btn btn-primary" id="btn_lr_report1" onclick="form_save()"><i class="fas fa-save"></i> บันทึก</button>
                                <?php } ?>

                            </div>
                        </div>



                    </div>
                    <br>

                    <div id="formContainer">
                        <div class="row">
                            <div class="col-sm-12">
                                <nav>
                                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                        <a class="nav-item nav-link active" id="pills-document-tab" data-toggle="tab" href="#pills-document" role="tab" aria-controls="pills-document" aria-selected="true"><?= htmlspecialchars($menuname) ?></a>

                                    </div> <!-- style="display: none" -->
                                </nav>
                                <div class="tab-content" id="nav-tabContent">

                                    <div class="tab-pane fade show active" id="pills-document" role="tabpanel" aria-labelledby="pills-document-tab"><?php require_once 'form-rehab-document.php'; ?></div>

                                </div>
                            </div>
                        </div><br>
                    </div>


                    <script src="../include/my_function.js"></script>
                    <script>
                        //ควบคุมปุ่ม
                        function custom_check(value) {

                            if (value == "off_heart_disease_history") {
                                $('#heart_disease_history_text').attr("disabled", true).val('');
                                $('#heart_disease_history2').prop("checked", false);
                                // $("#check_1").attr("class","text-success fas fa-check-square");
                            } else if (value == "on_heart_disease_history") {
                                $('#heart_disease_history_text').attr("disabled", false).val('');
                                $('#heart_disease_history1').prop("checked", false);
                                //  $('#entered_by2').prop("checked", false);
                            }

                            if (value == "off_skin") {
                                $('#skin_text').attr("disabled", true).val('');
                                $('#skin6').prop("checked", false);
                                // $("#check_1").attr("class","text-success fas fa-check-square");
                            } else if (value == "on_skin") {
                                $('#skin_text').attr("disabled", false).val('');
                                $('#skin1').prop("checked", false);
                                $('#skin2').prop("checked", false);
                                $('#skin3').prop("checked", false);
                                $('#skin4').prop("checked", false);
                                $('#skin5').prop("checked", false);
                                //  $('#entered_by2').prop("checked", false);
                            }

                            if (value == "off_neck_vien_engorement") {
                                $('#neck_vien_engorement_text').attr("disabled", true).val('');
                                $('#neck_vien_engorement3').prop("checked", false);
                                // $("#check_1").attr("class","text-success fas fa-check-square");
                            } else if (value == "on_neck_vien_engorement") {
                                $('#neck_vien_engorement_text').attr("disabled", false).val('');
                                $('#neck_vien_engorement1').prop("checked", false);
                                $('#neck_vien_engorement2').prop("checked", false);

                            }

                            if (value == "off_kidney_disease_history") {
                                $('#kidney_disease_history_text').attr("disabled", true).val('');
                                $('#kidney_disease_history2').prop("checked", false);
                                // $("#check_1").attr("class","text-success fas fa-check-square");
                            } else if (value == "on_kidney_disease_history") {
                                $('#kidney_disease_history_text').attr("disabled", false).val('');
                                $('#kidney_disease_history1').prop("checked", false);
                                //  $('#entered_by2').prop("checked", false);
                            }

                            if (value == "off_history_of_lung_disease") {
                                $('#history_of_lung_disease_text').attr("disabled", true).val('');
                                $('#history_of_lung_disease2').prop("checked", false);

                            } else if (value == "on_history_of_lung_disease") {
                                $('#history_of_lung_disease_text').attr("disabled", false).val('');
                                $('#history_of_lung_disease1').prop("checked", false);

                            }

                            if (value == "off_on_icd") {
                                $('#on_icd_text').attr("disabled", true).val('');
                                $('#on_icd2').prop("checked", false);

                            } else if (value == "on_on_icd") {
                                $('#on_icd_text').attr("disabled", false).val('');
                                $('#on_icd1').prop("checked", false);

                            }

                            if (value == "off_history_of_gastrointestinal") {
                                $('#history_of_gastrointestinal_text').attr("disabled", true).val('');
                                $('#history_of_gastrointestinal2').prop("checked", false);

                            } else if (value == "on_history_of_gastrointestinal") {
                                $('#history_of_gastrointestinal_text').attr("disabled", false).val('');
                                $('#history_of_gastrointestinal1').prop("checked", false);

                            }

                            if (value == "off_communication_history") {
                                $('#communication_history_text').attr("disabled", true).val('');
                                $('#communication_history2').prop("checked", false);

                            } else if (value == "on_communication_history") {
                                $('#communication_history_text').attr("disabled", false).val('');
                                $('#communication_history1').prop("checked", false);

                            }

                            if (value == "off_speaking") {
                                $('#speaking_text').attr("disabled", true).val('');
                                $('#speaking4').prop("checked", false);

                            } else if (value == "on_speaking") {
                                $('#speaking_text').attr("disabled", false).val('');
                                $('#speaking1').prop("checked", false);
                                $('#speaking2').prop("checked", false);
                                $('#speaking3').prop("checked", false);

                            } else if (value == "on_speaking_reset") {
                                $('#speaking1').prop("checked", false);
                                $('#speaking2').prop("checked", false);
                                $('#speaking3').prop("checked", false);
                                $('#speaking4').prop("checked", false);
                                $('#speaking_text').attr("disabled", true).val('');

                            }

                            if (value == "off_communication") {
                                $('#communication_text').attr("disabled", true).val('');
                                $('#communication6').prop("checked", false);
                                // $("#check_1").attr("class","text-success fas fa-check-square");
                            } else if (value == "on_communication") {
                                $('#communication_text').attr("disabled", false).val('');
                                $('#communication1').prop("checked", false);
                                $('#communication2').prop("checked", false);
                                $('#communication3').prop("checked", false);
                                $('#communication4').prop("checked", false);
                                $('#communication5').prop("checked", false);
                                //  $('#entered_by2').prop("checked", false);
                            } else if (value == "on_communication_reset") {
                                $('#communication1').prop("checked", false);
                                $('#communication2').prop("checked", false);
                                $('#communication3').prop("checked", false);
                                $('#communication4').prop("checked", false);
                                $('#communication5').prop("checked", false);
                                $('#communication6').prop("checked", false);
                                $('#communication_text').attr("disabled", true).val('');

                            }

                            if (value == "off_vision") {
                                $('#vision_text').attr("disabled", true).val('');
                                $('#vision4').prop("checked", false);

                            } else if (value == "on_vision") {
                                $('#vision_text').attr("disabled", false).val('');
                                $('#vision1').prop("checked", false);
                                $('#vision2').prop("checked", false);
                                $('#vision3').prop("checked", false);

                            }

                            if (value == "off_history_affects_activities") {
                                $('#history_affects_activities_text').attr("disabled", true).val('');
                                $('#history_affects_activities2').prop("checked", false);

                            } else if (value == "on_history_affects_activities") {
                                $('#history_affects_activities_text').attr("disabled", false).val('');
                                $('#history_affects_activities1').prop("checked", false);

                            }

                            if (value == "on_fracture") {
                                $('#fracture_text').attr("disabled", false).val('');

                            }

                            if (value == "off_history_affects_stimulation") {
                                $('#history_affects_stimulation_text').attr("disabled", true).val('');
                                $('#history_affects_stimulation2').prop("checked", false);

                            } else if (value == "on_history_affects_stimulation") {
                                $('#history_affects_stimulation_text').attr("disabled", false).val('');
                                $('#history_affects_stimulation1').prop("checked", false);

                            }


                            if (value == "off_on_et") {
                                $('#et_tube_no_text').attr("disabled", false).val('');
                                $('#et_tube_no_text2').attr("disabled", false).val('');
                                $('#o2_hfnc_text').attr("disabled", true).val('');
                                $('#et_other1').prop("checked", true);

                            } else if (value == "off_on_tt") {
                                $('#et_tube_no_text').attr("disabled", false).val('');
                                $('#et_tube_no_text2').attr("disabled", true).val('');
                                $('#o2_hfnc_text').attr("disabled", true).val('');
                                $('#et_other2').prop("checked", true);

                            } else if (value == "off_on_ra") {
                                $('#et_tube_no_text').attr("disabled", true).val('');
                                $('#et_tube_no_text2').attr("disabled", true).val('');
                                $('#o2_hfnc_text').attr("disabled", true).val('');
                                $('#et_other6').prop("checked", true);

                            } else if (value == "off_on_o2h") {
                                $('#o2_hfnc_text').attr("disabled", false).val('');
                                $('#candular_text').attr("disabled", true).val('');
                                $('#mark_c_bag_text').attr("disabled", true).val('');
                                $('#et_tube_no_text').attr("disabled", true).val('');
                                $('#et_tube_no_text2').attr("disabled", true).val('');
                                $('#et_other3').prop("checked", true);

                            } else if (value == "off_on_candular") {
                                $('#candular_text').attr("disabled", false).val('');
                                $('#mark_c_bag_text').attr("disabled", true).val('');
                                $('#et_tube_no_text').attr("disabled", true).val('');
                                $('#et_tube_no_text2').attr("disabled", true).val('');
                                $('#o2_hfnc_text').attr("disabled", true).val('');
                                $('#et_other4').prop("checked", true);

                            } else if (value == "off_on_mark_c_bag") {
                                $('#mark_c_bag_text').attr("disabled", false).val('');
                                $('#et_tube_no_text').attr("disabled", true).val('');
                                $('#et_tube_no_text2').attr("disabled", true).val('');
                                $('#o2_hfnc_text').attr("disabled", true).val('');
                                $('#candular_text').attr("disabled", true).val('');
                                $('#et_other5').prop("checked", true);

                            }


                            if (value == "off_on_copt") {
                                $('#copt_text').attr("disabled", false).val('');
                                $('#nrs_text').attr("disabled", true).val('');
                                $('#pain_score1').prop("checked", true);

                            } else if (value == "off_on_nrs") {
                                $('#nrs_text').attr("disabled", false).val('');
                                $('#copt_text').attr("disabled", true).val('');
                                $('#pain_score2').prop("checked", true);

                            } else if (value == "on_pain_score_reset") {
                                $('#nrs_text').attr("disabled", true).val('');
                                $('#copt_text').attr("disabled", true).val('');
                                $('#pain_score1').prop("checked", false);
                                $('#pain_score2').prop("checked", false);
                                $('#pain_score0').prop("checked", false);

                            }







                        }


                        function form_save() {

                            var rxdate = $.trim($('[name="rxdate"]').val());
                            var rxtime = $.trim($('[name="rxtime"]').val());
                            if (rxdate == "") {

                                $('[name="rxdate"]').focus();
                                alert('เลือกวันที่');
                            } else if (rxtime == "") {

                                $('[name="rxtime"]').focus();
                                alert('เลือกเวลา');
                            }


                            var url_update = "form-icu1-update.php";
                            var url_save = "form-icu1-save.php";
                            var id = $("#id").val();
                            var my_form = $("#my_form").serialize();

                            if (id == "") {
                                $.post(url_save, my_form, function(data) {
                                        $("#show_check_save").html(data);

                                        // alert("บันทึกข้อมูลสำเร็จ");
                                        // self.close();
                                        // window.location.reload(true);
                                    })
                                    .fail(function() {
                                        alert("บันทึกข้อมูลไม่สำเร็จ" + error);
                                    });
                            } else {
                                $.post(url_update, my_form, function(data) {
                                        $("#show_check_save").html(data);


                                    })
                                    .fail(function() {
                                        alert("บันทึกข้อมูลไม่สำเร็จ" + error);
                                        //NotificationMessage('บันทึกข้อมูลไม่สำเร็จ', 'danger');
                                    });
                            }


                        }
                    </script>

                    <script src="../node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
                    <link rel="stylesheet" href="../node_modules/sweetalert2/dist/sweetalert2.min.css">