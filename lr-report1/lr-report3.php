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

Session::checkLoginSessionAndShowMessage(); //เช็ค session
Session::checkPermissionAndShowMessage('IPD_NURSE_ADDMISSION_NOTE', 'VIEW');
require_once '../mains/ipd-show-patient-main.php'; //เป็นส่วนที่แสดง ข้อมูลผู้ป่วย เช่น รูป,hn,an,ชื่อ-สกุล,แพ้ยา ฯลฯ
require_once '../mains/ipd-show-patient-sticky.php';

require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';
$conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
$an = $_REQUEST['an']; //รับค่า an
$hn = KphisQueryUtils::getHnByAn($an); // function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา
$vn = KphisQueryUtils::getVnByAn($an);

/*
Session::insertSystemAccessLog(json_encode(array(
    'form' => 'LR-REPORT1-FORM',
    'an' => $an,
), JSON_UNESCAPED_UNICODE));

*/

/*$login = empty($_REQUEST['loginname']) ? null : $_REQUEST['loginname'];
$loginname = $_SESSION['loginname'];
$values = ['loginname' => $loginname];
if ($login != $loginname) {
    session_start();
    session_destroy();
}*/

// echo $an;

//----------------------เช็คว่า an นี้ มีข้อมูลหรือไม่
$sql = "SELECT count(*) AS count_row, id FROM " . DbConstant::KPHIS_DBNAME . ".prs_labor_report1 WHERE an = :an ";
$id  = null;
$parameters['an'] = $an;
$stmt = $conn->prepare($sql);
$stmt->execute($parameters);
$row = $stmt->fetch();
if ($row['count_row'] > 0) {
    $id = $row['id'];
}

if ($id == null || $id != null) {
    $sql_opdscreen = "SELECT opdscreen.vn,opdscreen.hn,opdscreen.cc,opdscreen.hpi,concat(round(opdscreen.bpd,0),'/',round(opdscreen.bps,0)) as bp,
                                    pt.sex,round(opdscreen.bps,0) as sbp,round(opdscreen.bpd,0) as dbp,
                                    round(opdscreen.pulse,0) as pr,round(opdscreen.rr,0) as rr,round(opdscreen.temperature,1) as bt,
                                    round((opdscreen.bw)*1000,0) as bw2,
                                    round(opdscreen.bw,1) as bw,round(opdscreen.height,1) as height,
                                    opdscreen.pe_ga_text, opdscreen.pe_heent_text,opdscreen.fh,
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

//----------------------เช็คว่า an นี้ มีข้อมูลหรือไม่

date_default_timezone_set('asia/bangkok');


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




<form id="lr_report1_form">
    <div class="container-fluid">
        <div class="row">
            <div class="col-auto">
                <button type="button" class="btn btn-sm btn-primary btn-block" onclick="window.close()"><i class="fas fa-arrow-left"></i> กลับ</button>
            </div>
            <div class="col-auto p-1 font-weight-bold">
                <h5><B>ใบบันทึกประวัติและประเมินสมรรถนะผู้ป่วยแรกรับ <?= htmlspecialchars(DbConstant::HOSPITAL_NAME) ?></B></h5>
            </div>

        </div>


        <div class="card-group pb-3 ">
            <div class="card">
                <div class="card-body" style=" overflow-y: auto;">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group row">
                                <label class="col-sm-12"><B>ข้อมูลทั่วไป</B></label>
                            </div>
                            <div class="row">

                                <div class="col-sm-1"></div>
                                <label>รับใหม่วันที่</label>
                                <div class="col-sm-2">
                                    <input type="date" class="form-control form-control-sm" id="receive_date" name="receive_date" value="<?= (isset($row_ipt['regdate']) && $id == null ? htmlspecialchars($row_ipt['regdate']) : htmlspecialchars($row['receive_date'])) ?>">
                                </div>
                                <label>เวลา</label>
                                <div class="col-sm-2">
                                    <input type="time" class="form-control form-control-sm" id="receive_time" name="receive_time" value="<?= (isset($row_ipt['regtime']) && $id == null ? htmlspecialchars($row_ipt['regtime']) : htmlspecialchars($row['receive_time'])) ?>">
                                </div>


                                <div class="form-group row">
                                    <div class="col-sm-1"></div>
                                    <div class="custom-control custom-radio col-sm-2">
                                        <input type="radio" <?php if (
                                                                $row['take_medication_by'] == 'มาเอง'
                                                                || $row['take_medication_by'] == NULL
                                                            ) {
                                                                echo 'checked="checked"';
                                                            } ?> class="custom-control-input" id="entered_by1" name="take_medication_by" value="มาเอง" onchange="custom_check('off_entered');">
                                        <label class="custom-control-label" for="entered_by1">OPD</label>
                                    </div>
                                    <div class="custom-control custom-radio col-sm-2">
                                        <input type="radio" <?php if ($row['take_medication_by'] == 'แพทย์นัด') {
                                                                echo 'checked="checked"';
                                                            } ?> class="custom-control-input" id="entered_by2" name="take_medication_by" value="แพทย์นัด" onchange="custom_check('off_entered');">
                                        <label class="custom-control-label" for="entered_by2">ER</label>
                                    </div>
                                    <div class="custom-control custom-radio col-sm-2">
                                        <input type="radio" <?php if (
                                                                $row['take_medication_by'] != 'มาเอง'
                                                                && $row['take_medication_by'] != 'แพทย์นัด'
                                                                && $row['take_medication_by'] != NULL
                                                            ) {
                                                                echo 'checked="checked"';
                                                            } ?> class="custom-control-input" id="entered_by3" onchange="custom_check('on_entered');">
                                        <label class="custom-control-label" for="entered_by3">อื่นๆ</label>
                                    </div>
                                    <div class="col-sm-5">
                                        <input type="text" class="form-control form-control-sm" id="entered_hos" name="take_medication_by" value="<?php if (
                                                                                                                                                        $row['take_medication_by'] != 'มาเอง'
                                                                                                                                                        && $row['take_medication_by'] != 'แพทย์นัด'
                                                                                                                                                    ) {
                                                                                                                                                        echo htmlspecialchars($row['take_medication_by']);
                                                                                                                                                    } ?>" <?php if (!($row['take_medication_by'] != 'มาเอง'
                                                                                                                                                            && $row['take_medication_by'] != 'แพทย์นัด'
                                                                                                                                                            && $row['take_medication_by'] != NULL)) {
                                                                                                                                                            echo 'disabled';
                                                                                                                                                        } ?>>
                                    </div>
                                </div>



                                &nbsp;&nbsp; <label>กรณีส่งต่อ ส่งต่อจาก</label>
                                <div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm" id="receive_time" name="receive_time" value="<?= (isset($row_ipt['regtime']) && $id == null ? htmlspecialchars($row_ipt['regtime']) : htmlspecialchars($row['receive_time'])) ?>">
                                </div>

                            </div>

                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>รับไว้ในโรงพยาบาลโดย</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                    <label class="custom-control-label" for="body1">เดินมา</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                    <label class="custom-control-label" for="body1">รถนั่ง</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                    <label class="custom-control-label" for="body1">รถนอน</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body'] != 'ปกติ' && $row['body'] != NULL) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="body2" onchange="body_check('on_entered');">
                                    <label class="custom-control-label" for="body2">อื่นๆ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="body_text" name="body" value="<?php if ($row['body'] != 'ปกติ' && $row['body'] != NULL) {
                                                                                                                                    echo htmlspecialchars($row['body']);
                                                                                                                                } ?>" <?php if (!($row['body'] != 'ปกติ' && $row['body'] != NULL)) {
                                                                                                            echo 'disabled';
                                                                                                        } ?>>
                                </div>


                            </div>
                            <br>


                            <div class="form-group row">
                                <label class="col-sm-12"><B> อาการสำคัญที่นำมาโรงพยาบาล</B></label>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <textarea class="form-control" id="cc" name="cc" rows="4"><?= (isset($row_opdscreen['cc']) && $id == null ? htmlspecialchars($row_opdscreen['cc']) : htmlspecialchars($row['cc'])) ?></textarea>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-12"><B> HPI </B></label>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <textarea class="form-control" id="hpi" name="hpi" rows="4"><?= (isset($row_opdscreen['hpi']) && $id == null ? htmlspecialchars($row_opdscreen['hpi']) : htmlspecialchars($row['hpi'])) ?></textarea>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-12"><B> ประวัติการเจ็บป่วยปัจจุบัน </B></label>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <textarea class="form-control" id="hpi" name="hpi" rows="4"><?= (isset($row_opdscreen['hpi']) && $id == null ? htmlspecialchars($row_opdscreen['hpi']) : htmlspecialchars($row['hpi'])) ?></textarea>
                                </div>
                            </div>


                            <div class="form-group row">
                                <label class="col-sm-12"><B> ประวัติการเจ็บป่วยในอดีต </B></label>
                            </div>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>โรคประจำตัว</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                    <label class="custom-control-label" for="body1">ปฏิเสธ</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body'] != 'ปกติ' && $row['body'] != NULL) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="body2" onchange="body_check('on_entered');">
                                    <label class="custom-control-label" for="body2">มี ระบุ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="body_text" name="body" value="<?php if ($row['body'] != 'ปกติ' && $row['body'] != NULL) {
                                                                                                                                    echo htmlspecialchars($row['body']);
                                                                                                                                } ?>" <?php if (!($row['body'] != 'ปกติ' && $row['body'] != NULL)) {
                                                                                                                                            echo 'disabled';
                                                                                                                                        } ?>>
                                </div>


                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>เคยรับการรักษาในโรงพยาบาล ภายใน 1 ปี</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                    <label class="custom-control-label" for="body1">ปฏิเสธ</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body'] != 'ปกติ' && $row['body'] != NULL) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="body2" onchange="body_check('on_entered');">
                                    <label class="custom-control-label" for="body2">เคย ระบุ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="body_text" name="body" value="<?php if ($row['body'] != 'ปกติ' && $row['body'] != NULL) {
                                                                                                                                    echo htmlspecialchars($row['body']);
                                                                                                                                } ?>" <?php if (!($row['body'] != 'ปกติ' && $row['body'] != NULL)) {
                                                                                                                                            echo 'disabled';
                                                                                                                                        } ?>>
                                </div>


                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ประวัติการผ่าตัด</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                    <label class="custom-control-label" for="body1">ปฏิเสธ</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body'] != 'ปกติ' && $row['body'] != NULL) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="body2" onchange="body_check('on_entered');">
                                    <label class="custom-control-label" for="body2">เคย ระบุ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="body_text" name="body" value="<?php if ($row['body'] != 'ปกติ' && $row['body'] != NULL) {
                                                                                                                                    echo htmlspecialchars($row['body']);
                                                                                                                                } ?>" <?php if (!($row['body'] != 'ปกติ' && $row['body'] != NULL)) {
                                                                                                                                            echo 'disabled';
                                                                                                                                        } ?>>
                                </div>


                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ประวัติการแพ้ยาหรือการแพ้อื่นๆ</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                    <label class="custom-control-label" for="body1">ปฏิเสธ</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body'] != 'ปกติ' && $row['body'] != NULL) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="body2" onchange="body_check('on_entered');">
                                    <label class="custom-control-label" for="body2">มี ระบุ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="body_text" name="body" value="<?php if ($row['body'] != 'ปกติ' && $row['body'] != NULL) {
                                                                                                                                    echo htmlspecialchars($row['body']);
                                                                                                                                } ?>" <?php if (!($row['body'] != 'ปกติ' && $row['body'] != NULL)) {
                                                                                                                                            echo 'disabled';
                                                                                                                                        } ?>>
                                </div>


                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ประวัติการใช้ยาและผลิตภัณฑ์สุขภาพ</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                    <label class="custom-control-label" for="body1">ปฏิเสธ</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body'] != 'ปกติ' && $row['body'] != NULL) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="body2" onchange="body_check('on_entered');">
                                    <label class="custom-control-label" for="body2">มี ระบุ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="body_text" name="body" value="<?php if ($row['body'] != 'ปกติ' && $row['body'] != NULL) {
                                                                                                                                    echo htmlspecialchars($row['body']);
                                                                                                                                } ?>" <?php if (!($row['body'] != 'ปกติ' && $row['body'] != NULL)) {
                                                                                                                                            echo 'disabled';
                                                                                                                                        } ?>>
                                </div>


                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ประวัติการเจ็บป่วยในครอบครัว</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                    <label class="custom-control-label" for="body1">ปฏิเสธ</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body'] != 'ปกติ' && $row['body'] != NULL) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="body2" onchange="body_check('on_entered');">
                                    <label class="custom-control-label" for="body2">มี ระบุ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="body_text" name="body" value="<?php if ($row['body'] != 'ปกติ' && $row['body'] != NULL) {
                                                                                                                                    echo htmlspecialchars($row['body']);
                                                                                                                                } ?>" <?php if (!($row['body'] != 'ปกติ' && $row['body'] != NULL)) {
                                                                                                                                            echo 'disabled';
                                                                                                                                        } ?>>
                                </div>


                            </div>
                            <br>

                            <div class="form-group row">
                                <label class="col-sm-12"><B> ประวัติการเจ็บป่วยของสมาชิกในครอบครัว</B></label>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <textarea class="form-control" id="family" name="family" rows="3"><?= (isset($row_opdscreen['fh']) && $id == null ? htmlspecialchars($row_opdscreen['fh']) : htmlspecialchars($row['family'])) ?></textarea>
                                </div>
                            </div>



                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>สัญญาณชีพแรกรับ&nbsp; BT&nbsp;</label>
                                <div>
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="g" id="g">
                                </div>C&nbsp; PR&nbsp;<div>
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="p" id="p">
                                </div> /min&nbsp;<label>RR</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="เฉพาะตัวเลข" name="serology" id="serology"> </div>
                                <label>/min&nbsp;BP</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxx" name="p" id="p"></div>
                                <label>mmHg</label>
                                

                            </div>
                            <br>

                            <div class="form-group row">
                                <label class="col-sm-12"><B>สภาพร่างกายผู้ป่วยแรกรับ</B></label>
                            </div>
                            

                            <div class="row">
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label></label>
                            <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row_ipt['sex'] == '1' || $row['sex'] == '1') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="sex1" name="sex" value="<?= (isset($row_opdscreen['sex'])  ? htmlspecialchars($row_opdscreen['sex']) : htmlspecialchars($row['sex'])) ?>" onchange="sex_check('off_checked');">
                                    <label class="custom-control-label" for="sex1">รู้สึกตัวดี</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row_ipt['sex'] == '2' || $row['sex'] == '2') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="sex2" name="sex" value="<?= (isset($row_opdscreen['sex'])  ? htmlspecialchars($row_opdscreen['sex']) : htmlspecialchars($row['sex'])) ?>" onchange="sex_check('on_checked');">
                                    <label class="custom-control-label" for="sex2">สับสน</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row_ipt['sex'] == '2' || $row['sex'] == '2') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="sex2" name="sex" value="<?= (isset($row_opdscreen['sex'])  ? htmlspecialchars($row_opdscreen['sex']) : htmlspecialchars($row['sex'])) ?>" onchange="sex_check('on_checked');">
                                    <label class="custom-control-label" for="sex2">ซึม</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row_ipt['sex'] == '2' || $row['sex'] == '2') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="sex2" name="sex" value="<?= (isset($row_opdscreen['sex'])  ? htmlspecialchars($row_opdscreen['sex']) : htmlspecialchars($row['sex'])) ?>" onchange="sex_check('on_checked');">
                                    <label class="custom-control-label" for="sex2">ไม่รู้สึกตัว</label>
                                </div>


                            </div>
                            <br>

                            <div class="row">

                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label>การหายใจ</label>
                            <div class="custom-control custom-radio col-sm-1">
                                &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                <label class="custom-control-label" for="body1">ปกติ</label>
                            </div>

                            <div class="custom-control custom-radio col-sm-1">
                                &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                <label class="custom-control-label" for="body1">หายใจหอบ</label>
                            </div>

                            <div class="custom-control custom-radio col-sm-1">
                                &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                <label class="custom-control-label" for="body1">หายใจลำบาก</label>
                            </div>

                            <div class="custom-control custom-radio col-sm-1">
                                &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                <label class="custom-control-label" for="body1">ไม่หายใจ</label>
                            </div>

                            <div class="custom-control custom-radio col-sm-1">
                                &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body'] != 'ปกติ' && $row['body'] != NULL) {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="body2" onchange="body_check('on_entered');">
                                <label class="custom-control-label" for="body2">อื่นๆ</label>
                            </div>

                            <div class="col-sm-5">
                                <input type="text" class="form-control form-control-sm" id="body_text" name="body" value="<?php if ($row['body'] != 'ปกติ' && $row['body'] != NULL) {
                                                                                                                                echo htmlspecialchars($row['body']);
                                                                                                                            } ?>" <?php if (!($row['body'] != 'ปกติ' && $row['body'] != NULL)) {
                                                                                                                                        echo 'disabled';
                                                                                                                                    } ?>>
                            </div>


                            </div>
                            <br>

                            <div class="row">
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label>การไหลเวียนโลหิต สีผิว</label>
                            <div class="custom-control custom-radio col-sm-1">
                            &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row_ipt['sex'] == '1' || $row['sex'] == '1') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="sex1" name="sex" value="<?= (isset($row_opdscreen['sex'])  ? htmlspecialchars($row_opdscreen['sex']) : htmlspecialchars($row['sex'])) ?>" onchange="sex_check('off_checked');">
                                    <label class="custom-control-label" for="sex1">ปกติ</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row_ipt['sex'] == '2' || $row['sex'] == '2') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="sex2" name="sex" value="<?= (isset($row_opdscreen['sex'])  ? htmlspecialchars($row_opdscreen['sex']) : htmlspecialchars($row['sex'])) ?>" onchange="sex_check('on_checked');">
                                    <label class="custom-control-label" for="sex2">ซีด</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php if ($row_ipt['sex'] == '2' || $row['sex'] == '2') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="sex2" name="sex" value="<?= (isset($row_opdscreen['sex'])  ? htmlspecialchars($row_opdscreen['sex']) : htmlspecialchars($row['sex'])) ?>" onchange="sex_check('on_checked');">
                                    <label class="custom-control-label" for="sex2">ปลายมือปลายเท้าเขียว</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row_ipt['sex'] == '2' || $row['sex'] == '2') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="sex2" name="sex" value="<?= (isset($row_opdscreen['sex'])  ? htmlspecialchars($row_opdscreen['sex']) : htmlspecialchars($row['sex'])) ?>" onchange="sex_check('on_checked');">
                                    <label class="custom-control-label" for="sex2">รอบปากเขียว</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row_ipt['sex'] == '2' || $row['sex'] == '2') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="sex2" name="sex" value="<?= (isset($row_opdscreen['sex'])  ? htmlspecialchars($row_opdscreen['sex']) : htmlspecialchars($row['sex'])) ?>" onchange="sex_check('on_checked');">
                                    <label class="custom-control-label" for="sex2">เขียวทั่วตัว</label>
                                </div>


                            </div>
                            <br>

                            <div class="row">

                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label></label>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label>อาการบวม</label>
                            <div class="custom-control custom-radio col-sm-1">
                                &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                <label class="custom-control-label" for="body1">ไม่มี</label>
                            </div>

                            
                            <div class="custom-control custom-radio col-sm-1">
                                &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body'] != 'ปกติ' && $row['body'] != NULL) {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="body2" onchange="body_check('on_entered');">
                                <label class="custom-control-label" for="body2">บวมบริเวณ</label>
                            </div>

                            <div class="col-sm-5">
                                <input type="text" class="form-control form-control-sm" id="body_text" name="body" value="<?php if ($row['body'] != 'ปกติ' && $row['body'] != NULL) {
                                                                                                                                echo htmlspecialchars($row['body']);
                                                                                                                            } ?>" <?php if (!($row['body'] != 'ปกติ' && $row['body'] != NULL)) {
                                                                                                                                        echo 'disabled';
                                                                                                                                    } ?>>
                            </div>


                            </div>
                            <br>

                            <div class="row">
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label>ผิวหนัง</label>
                            <div class="custom-control custom-radio col-sm-1">
                            &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row_ipt['sex'] == '1' || $row['sex'] == '1') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="sex1" name="sex" value="<?= (isset($row_opdscreen['sex'])  ? htmlspecialchars($row_opdscreen['sex']) : htmlspecialchars($row['sex'])) ?>" onchange="sex_check('off_checked');">
                                    <label class="custom-control-label" for="sex1">ปกติ</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row_ipt['sex'] == '2' || $row['sex'] == '2') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="sex2" name="sex" value="<?= (isset($row_opdscreen['sex'])  ? htmlspecialchars($row_opdscreen['sex']) : htmlspecialchars($row['sex'])) ?>" onchange="sex_check('on_checked');">
                                    <label class="custom-control-label" for="sex2">หนังแตก</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row_ipt['sex'] == '2' || $row['sex'] == '2') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="sex2" name="sex" value="<?= (isset($row_opdscreen['sex'])  ? htmlspecialchars($row_opdscreen['sex']) : htmlspecialchars($row['sex'])) ?>" onchange="sex_check('on_checked');">
                                    <label class="custom-control-label" for="sex2">เขียวช้ำ</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row_ipt['sex'] == '2' || $row['sex'] == '2') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="sex2" name="sex" value="<?= (isset($row_opdscreen['sex'])  ? htmlspecialchars($row_opdscreen['sex']) : htmlspecialchars($row['sex'])) ?>" onchange="sex_check('on_checked');">
                                    <label class="custom-control-label" for="sex2">ผื่นแดง</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row_ipt['sex'] == '2' || $row['sex'] == '2') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="sex2" name="sex" value="<?= (isset($row_opdscreen['sex'])  ? htmlspecialchars($row_opdscreen['sex']) : htmlspecialchars($row['sex'])) ?>" onchange="sex_check('on_checked');">
                                    <label class="custom-control-label" for="sex2">ผื่นคัน</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row_ipt['sex'] == '2' || $row['sex'] == '2') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="sex2" name="sex" value="<?= (isset($row_opdscreen['sex'])  ? htmlspecialchars($row_opdscreen['sex']) : htmlspecialchars($row['sex'])) ?>" onchange="sex_check('on_checked');">
                                    <label class="custom-control-label" for="sex2">เหลือง</label>
                                </div>


                            </div>
                            <br>
                            <div class="row">
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label>การติดต่อสื่อสาร หู</label>
                            <div class="custom-control custom-radio col-sm-1">
                            &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row_ipt['sex'] == '1' || $row['sex'] == '1') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="sex1" name="sex" value="<?= (isset($row_opdscreen['sex'])  ? htmlspecialchars($row_opdscreen['sex']) : htmlspecialchars($row['sex'])) ?>" onchange="sex_check('off_checked');">
                                    <label class="custom-control-label" for="sex1">ได้ยินชัดเจน</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php if ($row_ipt['sex'] == '2' || $row['sex'] == '2') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="sex2" name="sex" value="<?= (isset($row_opdscreen['sex'])  ? htmlspecialchars($row_opdscreen['sex']) : htmlspecialchars($row['sex'])) ?>" onchange="sex_check('on_checked');">
                                    <label class="custom-control-label" for="sex2">ได้ยินไม่ชัดเจน : ใช้อุปกรณ์ช่วยฟัง</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row_ipt['sex'] == '2' || $row['sex'] == '2') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="sex2" name="sex" value="<?= (isset($row_opdscreen['sex'])  ? htmlspecialchars($row_opdscreen['sex']) : htmlspecialchars($row['sex'])) ?>" onchange="sex_check('on_checked');">
                                    <label class="custom-control-label" for="sex2">มี</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row_ipt['sex'] == '2' || $row['sex'] == '2') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="sex2" name="sex" value="<?= (isset($row_opdscreen['sex'])  ? htmlspecialchars($row_opdscreen['sex']) : htmlspecialchars($row['sex'])) ?>" onchange="sex_check('on_checked');">
                                    <label class="custom-control-label" for="sex2">ไม่มี</label>
                                </div>
                                


                            </div>
                            <br>

                            <div class="row">
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label>ตา</label>
                            <div class="custom-control custom-radio col-sm-1">
                            &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row_ipt['sex'] == '1' || $row['sex'] == '1') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="sex1" name="sex" value="<?= (isset($row_opdscreen['sex'])  ? htmlspecialchars($row_opdscreen['sex']) : htmlspecialchars($row['sex'])) ?>" onchange="sex_check('off_checked');">
                                    <label class="custom-control-label" for="sex1">เห็นชัดเจน</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php if ($row_ipt['sex'] == '2' || $row['sex'] == '2') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="sex2" name="sex" value="<?= (isset($row_opdscreen['sex'])  ? htmlspecialchars($row_opdscreen['sex']) : htmlspecialchars($row['sex'])) ?>" onchange="sex_check('on_checked');">
                                    <label class="custom-control-label" for="sex2">เห็นไม่ชัดเจน : สวมแว่นตา</label>

                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row_ipt['sex'] == '2' || $row['sex'] == '2') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="sex2" name="sex" value="<?= (isset($row_opdscreen['sex'])  ? htmlspecialchars($row_opdscreen['sex']) : htmlspecialchars($row['sex'])) ?>" onchange="sex_check('on_checked');">
                                    <label class="custom-control-label" for="sex2">สวม</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row_ipt['sex'] == '2' || $row['sex'] == '2') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="sex2" name="sex" value="<?= (isset($row_opdscreen['sex'])  ? htmlspecialchars($row_opdscreen['sex']) : htmlspecialchars($row['sex'])) ?>" onchange="sex_check('on_checked');">
                                    <label class="custom-control-label" for="sex2">ไม่สวม</label>
                                </div>
                                


                            </div>
                            <br>

                            <div class="row">

                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label></label>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label>การพูด</label>
                            <div class="custom-control custom-radio col-sm-1">
                                &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                <label class="custom-control-label" for="body1">ชัดเจน</label>
                            </div>
                            <div class="custom-control custom-radio col-sm-1">
                                <input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                <label class="custom-control-label" for="body1">พูดติดอ่าง</label>
                            </div>

                            <div class="custom-control custom-radio col-sm-1">
                                <input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                <label class="custom-control-label" for="body1">เป็นใบ้</label>
                            </div>

                            
                            <div class="custom-control custom-radio col-sm-1">
                                <input type="radio" <?php if ($row['body'] != 'ปกติ' && $row['body'] != NULL) {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="body2" onchange="body_check('on_entered');">
                                <label class="custom-control-label" for="body2">อื่นๆ</label>
                            </div>

                            <div class="col-sm-5">
                                <input type="text" class="form-control form-control-sm" id="body_text" name="body" value="<?php if ($row['body'] != 'ปกติ' && $row['body'] != NULL) {
                                                                                                                                echo htmlspecialchars($row['body']);
                                                                                                                            } ?>" <?php if (!($row['body'] != 'ปกติ' && $row['body'] != NULL)) {
                                                                                                                                        echo 'disabled';
                                                                                                                                    } ?>>
                            </div>


                            </div>
                            <br>

                            <div class="form-group row">
                                <label class="col-sm-12">สภาพจิตใจแรกรับ (การแสดงออกทางพฤติกรรม, การแสดงออกทางอารมณ์, สิ่งที่วิตกกังวล)</label>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <textarea class="form-control" id="family" name="family" rows="2"><?= (isset($row_opdscreen['fh']) && $id == null ? htmlspecialchars($row_opdscreen['fh']) : htmlspecialchars($row['family'])) ?></textarea>
                                </div>
                            </div>
                              
                              
                            <div class="form-group row">
                                <label class="col-sm-12">อาการแรกรับ</label>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <textarea class="form-control" id="family" name="family" rows="3"><?= (isset($row_opdscreen['fh']) && $id == null ? htmlspecialchars($row_opdscreen['fh']) : htmlspecialchars($row['family'])) ?></textarea>
                                </div>
                            </div>
                                                                                                                            

                            <hr>



                        </div>


                        <div class="row">
                            <input type="hidden" id="an" name="an" value="<?= $an ?>"><!-- ฟิลด์ hidden  "an"  -->
                            <input type="hidden" id="id" name="id" value="<?= $id ?>"><!-- ฟิลด์ hidden "id"  -->
                            <input type="hidden" id="version" name="version" value="<?= $row['version'] ?>"><!-- ฟิลด์ hidden "id"  -->
                            <div class="col-md-9">
                                <div id="data_lr_report1_save"></div><!-- แสดงข้อความการบันมึก >> บันทึกข้อมูลสำเร็จ, EORROR -->

                                <div id="data_lr_report1_edit"></div>
                                <div id="data_lr_report1_update"></div>

                            </div>
                            <div class="col-md-12 text-right">
                                <?php
                                if ((($id == null)) || (($id != null))) { ?>
                                    <button type="button" class="btn btn-primary" id="btn_lr_report1" onclick="lr_report1_save()"><i class="fas fa-save"></i> บันทึก</button>
                                <?php } ?>
                                <a href="lr-report1-pdf.php?an=<?php echo $an; ?>&loginname=<?php echo $loginname; ?>" target="_blank" class="btn btn-secondary"><i class="fas fa-file-pdf"></i> Print <U>PDF</U> File</a>
                            </div>
                        </div>
                    </div>

                    <br>


                    <script>
                        //ควบคุมปุ่ม
                        function custom_check(value) {

                            if (value == "off_entered") {
                                $('#from_text').attr("disabled", true).val('');
                                $('#receive_from2').prop("checked", false);
                                // $("#check_1").attr("class","text-success fas fa-check-square");
                            } else if (value == "on_entered") {
                                $('#from_text').attr("disabled", false).val('');
                                $('#receive_from1').prop("checked", false);
                                //  $('#entered_by2').prop("checked", false);
                            }

                            if (value == "off_value") {
                                $('#v3').attr("disabled", true).val('');
                                $('#v2').prop("checked", false);
                            } else if (value == "on_value") {
                                $('#v3').attr("disabled", false).val('');
                                $('#v1').prop("checked", false);
                                //$('#entered_by2').prop("checked", false);
                            }

                            if (value == "off_cry") {
                                $('#cry_text').attr("disabled", true).val('');
                                $('#cry3').prop("checked", false);
                            } else if (value == "on_cry") {
                                $('#cry_text').attr("disabled", false).val('');
                                $('#cry1').prop("checked", false);
                                $('#cry2').prop("checked", false);
                            }

                        }

                        function body_check(value) {

                            if (value == "off_entered") {
                                $('#body_text').attr("disabled", true).val('');
                                $('#body2').prop("checked", false);
                            } else if (value == "on_entered") {
                                $('#body_text').attr("disabled", false).val('');
                                $('#body1').prop("checked", false);
                                //  $('#entered_by2').prop("checked", false);
                            }

                        }







                        function movement_check(value) {
                            if (value == "off_checked") {
                                $('#movement_text').attr("disabled", true).val('');
                                $('#movement4').prop("checked", false);
                            } else if (value == "on_checked") {
                                $('#movement_text').attr("disabled", false).val('');
                                $('#movement1').prop("checked", false);
                                $('#movement2').prop("checked", false);
                                $('#movement3').prop("checked", false);
                            }
                        }

                        function head_check(value) {

                            if (value == "off_checked") {
                                $('#head_text').attr("disabled", true).val('');
                                $('#head2').prop("checked", false);
                            } else if (value == "on_checked") {
                                $('#head_text').attr("disabled", false).val('');
                                $('#head1').prop("checked", false);
                                //  $('#entered_by2').prop("checked", false);
                            }

                        }

                        function eyes_check(value) {

                            if (value == "off_checked") {
                                $('#eyes_text').attr("disabled", true).val('');
                                $('#eyes2').prop("checked", false);
                            } else if (value == "on_checked") {
                                $('#eyes_text').attr("disabled", false).val('');
                                $('#eyes1').prop("checked", false);
                                //  $('#entered_by2').prop("checked", false);
                            }

                        }

                        function nose_check(value) {

                            if (value == "off_checked") {
                                $('#nose_text').attr("disabled", true).val('');
                                $('#nose3').prop("checked", false);
                            } else if (value == "on_checked") {
                                $('#nose_text').attr("disabled", false).val('');
                                $('#nose1').prop("checked", false);
                                $('#nose2').prop("checked", false);
                                //  $('#entered_by2').prop("checked", false);
                            }

                        }

                        function mouth_check(value) {
                            if (value == "off_checked") {
                                $('#mouth_text').attr("disabled", true).val('');
                                $('#mouth4').prop("checked", false);
                            } else if (value == "on_checked") {
                                $('#mouth_text').attr("disabled", false).val('');
                                $('#mouth1').prop("checked", false);
                                $('#mouth2').prop("checked", false);
                                $('#mouth3').prop("checked", false);
                            }
                        }


                        function neck_check(value) {

                            if (value == "off_checked") {
                                $('#neck_text').attr("disabled", true).val('');
                                $('#neck2').prop("checked", false);
                            } else if (value == "on_checked") {
                                $('#neck_text').attr("disabled", false).val('');
                                $('#neck1').prop("checked", false);
                                //  $('#entered_by2').prop("checked", false);
                            }

                        }

                        function abdomen_check(value) {

                            if (value == "off_checked") {
                                $('#abdomen_text').attr("disabled", true).val('');
                                $('#abdomen3').prop("checked", false);
                            } else if (value == "on_checked") {
                                $('#abdomen_text').attr("disabled", false).val('');
                                $('#abdomen1').prop("checked", false);
                                $('#abdomen2').prop("checked", false);
                                //  $('#entered_by2').prop("checked", false);
                            }

                        }

                        function navel_check(value) {

                            if (value == "off_checked") {
                                $('#navel_text').attr("disabled", true).val('');
                                $('#navel4').prop("checked", false);
                            } else if (value == "on_checked") {
                                $('#navel_text').attr("disabled", false).val('');
                                $('#navel1').prop("checked", false);
                                $('#navel2').prop("checked", false);
                                $('#navel3').prop("checked", false);
                                //  $('#entered_by2').prop("checked", false);
                            }

                        }

                        function spine_check(value) {

                            if (value == "off_checked") {
                                $('#spine_text').attr("disabled", true).val('');
                                $('#spine2').prop("checked", false);
                            } else if (value == "on_checked") {
                                $('#spine_text').attr("disabled", false).val('');
                                $('#spine1').prop("checked", false);
                                //  $('#entered_by2').prop("checked", false);
                            }

                        }


                        function limbs_check(value) {

                            if (value == "off_checked") {
                                $('#limbs_text').attr("disabled", true).val('');
                                $('#limbs2').prop("checked", false);
                            } else if (value == "on_checked") {
                                $('#limbs_text').attr("disabled", false).val('');
                                $('#limbs1').prop("checked", false);
                                //  $('#entered_by2').prop("checked", false);
                            }

                        }

                        function genitalia_check(value) {

                            if (value == "off_checked") {
                                $('#genitalia_text').attr("disabled", true).val('');
                                $('#genitalia2').prop("checked", false);
                            } else if (value == "on_checked") {
                                $('#genitalia_text').attr("disabled", false).val('');
                                $('#genitalia1').prop("checked", false);
                                //  $('#entered_by2').prop("checked", false);
                            }

                        }

                        function skin_color_check(value) {

                            if (value == "off_checked") {
                                $('#skin_color_text').attr("disabled", true).val('');
                                $('#skin_color4').prop("checked", false);
                            } else if (value == "on_checked") {
                                $('#skin_color_text').attr("disabled", false).val('');
                                $('#skin_color1').prop("checked", false);
                                $('#skin_color2').prop("checked", false);
                                $('#skin_color3').prop("checked", false);
                                //  $('#entered_by2').prop("checked", false);
                            }

                        }

                        function behavior_check(value) {

                            if (value == "off_checked") {
                                $('#behavior_text').attr("disabled", true).val('');
                                $('#behavior3').prop("checked", false);
                            } else if (value == "on_checked") {
                                $('#behavior_text').attr("disabled", false).val('');
                                $('#behavior1').prop("checked", false);
                                $('#behavior2').prop("checked", false);
                                //  $('#entered_by2').prop("checked", false);
                            }

                        }

                        function expression_check(value) {

                            if (value == "off_checked") {
                                $('#expression_text').attr("disabled", true).val('');
                                $('#expression3').prop("checked", false);

                            } else if (value == "on_checked") {
                                $('#expression_text').attr("disabled", false).val('');
                                $('#expression1').prop("checked", false);
                                $('#expression2').prop("checked", false);
                                //  $('#entered_by2').prop("checked", false);
                            } else if (value == "on_aa") {
                                $('#expression_text').attr("disabled", false).val('');
                                $('#expression1').prop("checked", true);
                                //  $('#entered_by2').prop("checked", false);
                            }

                            function sex_check(value) {
                                if (value == "off_checked") {
                                    // $('#ros_text').attr("disabled",true).val('');
                                    $('#sex2').prop("checked", false);
                                } else if (value == "on_checked") {
                                    // $('#ros_text').attr("disabled",false).val('');
                                    $('#sex1').prop("checked", false);
                                }
                            }

                        }




                        $(document).ready(function() {
                            var id = <?= json_encode($id) ?>;
                            if (id != null && id != "") {
                                lr_report1_edit(<?= json_encode($id) ?>, <?= json_encode($an) ?>);
                            } else {
                                // import_DataOR_Hosxp(<?= json_encode($an) ?>);
                            }
                            //summary_CheckPer();
                        });

                        function lr_report1_edit(id, an) {
                            var url = "lr-report1-edit.php";
                            $.post(url, {
                                id,
                                an
                            }, function(data_edit) {
                                $("#data_lr_report1_edit").html(data_edit);
                                //console.log(data_edit);
                            });
                        }

                        function lr_report1_save() {

                            var id = $("#id").val();
                            //บันทึก / แก้ไข PHP File
                            var url_save = 'lr-report1-save.php';
                            var url_update = 'lr-report1-update.php';

                            $("#btn_lr_report1").attr('disabled', 'disabled');

                            if (id == "") {
                                $.post(url_save, $("#lr_report1_form").serialize(), function(data_save) {
                                        $("#data_lr_report1_save").html(data_save);
                                        window.location.reload(true);
                                    })
                                    .fail(function() {
                                        alert("บันทึกข้อมูลไม่สำเร็จ");
                                        $("#btn_lr_report1").removeAttr("disabled");
                                    });
                            } else
                            //เมื่อมีการแก้ไขเรียกใช้งาน update
                            {
                                $.post(url_update, $("#lr_report1_form").serialize(), function(data_update) {
                                        $("#data_lr_report1_update").html(data_update);
                                        window.location.reload(true);
                                    })
                                    .fail(function() {
                                        alert("บันทึกข้อมูลไม่สำเร็จ");
                                        $("#btn_lr_report1").removeAttr("disabled");
                                    });
                            }

                        }

                        /*
                        window.onscroll = function() {myFunction()};

                        var header = document.getElementById("myHeader");
                        var sticky = header.offsetTop;

                        function myFunction() {
                          if (window.pageYOffset > sticky) {
                            header.classList.add("sticky");
                          } else {
                            header.classList.remove("sticky");
                          }
                        }
                        */
                    </script>