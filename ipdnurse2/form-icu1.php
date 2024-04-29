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
                <h5><B>แบบประเมินผู้ป่วยวิกฤตแรกรับตามแนวคิด FANCAS <?= htmlspecialchars(DbConstant::HOSPITAL_NAME) ?><font color="red"> (รอคุยรายละเอียดเพื่อออกแบบการเก็บข้อมูล) </font></B></h5>
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
                                <label>รับใหม่/รับย้ายเวลา</label>
                                <div class="col-sm-2">
                                    <input type="time" class="form-control form-control-sm" id="receive_time" name="receive_time" value="<?= (isset($row_ipt['regtime']) && $id == null ? htmlspecialchars($row_ipt['regtime']) : htmlspecialchars($row['receive_time'])) ?>">
                                </div>


                                &nbsp;<label>น. จากหน่วยงาน</label>
                                <div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm" id="receive_time" name="receive_time" value="<?= (isset($row_ipt['regtime']) && $id == null ? htmlspecialchars($row_ipt['regtime']) : htmlspecialchars($row['receive_time'])) ?>">
                                </div>

                            </div>

                            <br>

                            <div class="form-group row alert alert-dark text-left">
                                <B>1. ด้านสมดุลของน้ำ(Fluid balance)</B>
                            </div>
                            <div class="form-group row alert alert-dark text-left">
                                <B>1.1 Cardiovascalar system</B>
                            </div>


                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ประวัติโรคหัวใจ/หลอดเลือด</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                    <label class="custom-control-label" for="body1">ไม่มี</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['body'] != 'ปกติ' && $row['body'] != NULL) {
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

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ผิวหนัง</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                    <label class="custom-control-label" for="body1">ปกติ</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                    <label class="custom-control-label" for="body1">ซีด</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                    <label class="custom-control-label" for="body1">เขียว</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                    <label class="custom-control-label" for="body1">จุดจ้ำเลือด</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                    <label class="custom-control-label" for="body1">แห้ง</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['body'] != 'ปกติ' && $row['body'] != NULL) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="body2" onchange="body_check('on_entered');">
                                    <label class="custom-control-label" for="body2">บุ๋มกดบวม</label>
                                </div>

                                <div class="col-sm-4">
                                    <input type="text" class="form-control form-control-sm" id="body_text" name="body" value="<?php if ($row['body'] != 'ปกติ' && $row['body'] != NULL) {
                                                                                                                                    echo htmlspecialchars($row['body']);
                                                                                                                                } ?>" <?php if (!($row['body'] != 'ปกติ' && $row['body'] != NULL)) {
                                                                                                                                            echo 'disabled';
                                                                                                                                        } ?>>
                                </div>


                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>Neck vien engorement</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                    <label class="custom-control-label" for="body1">ไม่มี</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['body'] != 'ปกติ' && $row['body'] != NULL) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="body2" onchange="body_check('on_entered');">
                                    <label class="custom-control-label" for="body2">มี ระบุ</label>
                                </div>

                                <div class="col-sm-4">
                                    <input type="text" class="form-control form-control-sm" id="body_text" name="body" value="<?php if ($row['body'] != 'ปกติ' && $row['body'] != NULL) {
                                                                                                                                    echo htmlspecialchars($row['body']);
                                                                                                                                } ?>" <?php if (!($row['body'] != 'ปกติ' && $row['body'] != NULL)) {
                                                                                                                                        echo 'disabled';
                                                                                                                                    } ?>>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                    <label class="custom-control-label" for="body1">ประเมินไม่ได้</label>
                                </div>


                            </div>
                            <br>

                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ฟังเสียงหัวใจ</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row_ipt['sex'] == '1' || $row['sex'] == '1') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="sex1" name="sex" value="<?= (isset($row_opdscreen['sex'])  ? htmlspecialchars($row_opdscreen['sex']) : htmlspecialchars($row['sex'])) ?>" onchange="sex_check('off_checked');">
                                    <label class="custom-control-label" for="sex1">Murmur</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row_ipt['sex'] == '2' || $row['sex'] == '2') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="sex2" name="sex" value="<?= (isset($row_opdscreen['sex'])  ? htmlspecialchars($row_opdscreen['sex']) : htmlspecialchars($row['sex'])) ?>" onchange="sex_check('on_checked');">
                                    <label class="custom-control-label" for="sex2">Rub</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php if ($row_ipt['sex'] == '2' || $row['sex'] == '2') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="sex2" name="sex" value="<?= (isset($row_opdscreen['sex'])  ? htmlspecialchars($row_opdscreen['sex']) : htmlspecialchars($row['sex'])) ?>" onchange="sex_check('on_checked');">
                                    <label class="custom-control-label" for="sex2">ไม่พบ</label>
                                </div>

                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>V/S&nbsp; BT&nbsp;</label>
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

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ผลการตรวจ CBC : WBC&nbsp;</label>
                                <div>
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="g" id="g">
                                </div>&nbsp; Hct&nbsp;<div>
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="p" id="p">
                                </div> <label>% Hb&nbsp;</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="เฉพาะตัวเลข" name="serology" id="serology"> </div>
                                <label>Plt</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxx" name="p" id="p"></div>
                                <label>PT</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxx" name="p" id="p"></div>
                                <label>PTT</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxx" name="p" id="p"></div>
                                <label>INR</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxx" name="p" id="p"></div>

                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>Trop -T&nbsp;</label>
                                <div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="g" id="g">
                                </div>&nbsp; CKMB&nbsp;<div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="p" id="p">
                                </div> <label>CPK&nbsp;</label>
                                <div class="col-md-3"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxx" name="serology" id="serology"> </div>


                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>Echo&nbsp;</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="g" id="g">
                                </div>&nbsp; EKG&nbsp;<div class="col-sm-4">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="p" id="p">
                                </div>


                            </div>
                            <br>

                            <div class="form-group row alert alert-dark text-left">
                                <B>1.2 Kidney system</B>
                            </div>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ประวัติโรคไต</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                    <label class="custom-control-label" for="body1">ไม่มี</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['body'] != 'ปกติ' && $row['body'] != NULL) {
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

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ลักษณะปัสสาวะ&nbsp;</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="g" id="g">
                                </div>&nbsp; I/O ใน 24 ชม.&nbsp;<div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="p" id="p">
                                </div> &nbsp; /&nbsp;<div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="p" id="p">
                                </div>ซีซี

                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ผลการตรวจ LAB BUN&nbsp;</label>
                                <div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="g" id="g">
                                </div>&nbsp;Cr&nbsp;<div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="p" id="p">
                                </div> &nbsp;GFR&nbsp;<div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="p" id="p">
                                </div>&nbsp;E'lyte&nbsp;<div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="p" id="p">
                                </div>

                            </div>
                            <br>


                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>Ca&nbsp;</label>
                                <div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="g" id="g">
                                </div>&nbsp;<p>Po<sub>4</sub></p>&nbsp;<div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="p" id="p">
                                </div> &nbsp;Mg&nbsp;<div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="p" id="p">
                                </div>&nbsp;DTX&nbsp;<div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="p" id="p">
                                </div>&nbsp;mg% Urine Sp.gr&nbsp;<div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="p" id="p">
                                </div>

                            </div>
                            <br>


                            <div class="form-group row alert alert-dark text-left">
                                <B>2.ด้านการหายใจ (Aeration)</B>
                            </div>


                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ประวัติโรคปอด</label>
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

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>การหายใจ RR&nbsp;</label>
                                <div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="g" id="g">
                                </div>&nbsp;/min&nbsp;O2Sat<div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="p" id="p">
                                </div> &nbsp;% On ( ) ET-Tube or TT-Tube No&nbsp;<div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="p" id="p">
                                </div>&nbsp;ขีด&nbsp;<div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="p" id="p">
                                </div>&nbsp;cms.&nbsp;

                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>
                                    <p>( ) O<sub>2</sub>HFNC</p>&nbsp;
                                </label>
                                <div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="g" id="g">
                                </div>&nbsp;( )&nbsp;Candular<div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="p" id="p">
                                </div> &nbsp; ( ) Mark c bag&nbsp;<div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="p" id="p">
                                </div>&nbsp;( )&nbsp;RA

                            </div>


                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ลักษณการหายใจ</label>
                                <div class="custom-control custom-radio col-sm-2">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row_ipt['sex'] == '1' || $row['sex'] == '1') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="sex1" name="sex" value="<?= (isset($row_opdscreen['sex'])  ? htmlspecialchars($row_opdscreen['sex']) : htmlspecialchars($row['sex'])) ?>" onchange="sex_check('off_checked');">
                                    <label class="custom-control-label" for="sex1">หายใจหอบ</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php if ($row_ipt['sex'] == '2' || $row['sex'] == '2') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="sex2" name="sex" value="<?= (isset($row_opdscreen['sex'])  ? htmlspecialchars($row_opdscreen['sex']) : htmlspecialchars($row['sex'])) ?>" onchange="sex_check('on_checked');">
                                    <label class="custom-control-label" for="sex2">หายใจลำบาก</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row_ipt['sex'] == '2' || $row['sex'] == '2') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="sex2" name="sex" value="<?= (isset($row_opdscreen['sex'])  ? htmlspecialchars($row_opdscreen['sex']) : htmlspecialchars($row['sex'])) ?>" onchange="sex_check('on_checked');">
                                    <label class="custom-control-label" for="sex2">หายใจปกติ</label>
                                </div>

                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>On ICD</label>
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
                                    <label class="custom-control-label" for="body2">มี ข้าง</label>
                                </div>

                                <div class="col-sm-3">
                                    <input type="text" class="form-control form-control-sm" id="body_text" name="body" value="<?php if ($row['body'] != 'ปกติ' && $row['body'] != NULL) {
                                                                                                                                    echo htmlspecialchars($row['body']);
                                                                                                                                } ?>" <?php if (!($row['body'] != 'ปกติ' && $row['body'] != NULL)) {
                                                                                                                                            echo 'disabled';
                                                                                                                                        } ?>>
                                </div>

                                &nbsp;ขีด&nbsp;<div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="p" id="p">
                                </div>


                            </div>
                            <br>

                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ฟังเสียงลมเข้าปอด</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row_ipt['sex'] == '1' || $row['sex'] == '1') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="sex1" name="sex" value="<?= (isset($row_opdscreen['sex'])  ? htmlspecialchars($row_opdscreen['sex']) : htmlspecialchars($row['sex'])) ?>" onchange="sex_check('off_checked');">
                                    <label class="custom-control-label" for="sex1">Clear</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row_ipt['sex'] == '2' || $row['sex'] == '2') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="sex2" name="sex" value="<?= (isset($row_opdscreen['sex'])  ? htmlspecialchars($row_opdscreen['sex']) : htmlspecialchars($row['sex'])) ?>" onchange="sex_check('on_checked');">
                                    <label class="custom-control-label" for="sex2">Crepitation</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row_ipt['sex'] == '2' || $row['sex'] == '2') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="sex2" name="sex" value="<?= (isset($row_opdscreen['sex'])  ? htmlspecialchars($row_opdscreen['sex']) : htmlspecialchars($row['sex'])) ?>" onchange="sex_check('on_checked');">
                                    <label class="custom-control-label" for="sex2">Wheezing</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row_ipt['sex'] == '2' || $row['sex'] == '2') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="sex2" name="sex" value="<?= (isset($row_opdscreen['sex'])  ? htmlspecialchars($row_opdscreen['sex']) : htmlspecialchars($row['sex'])) ?>" onchange="sex_check('on_checked');">
                                    <label class="custom-control-label" for="sex2">Rhonchi</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row_ipt['sex'] == '2' || $row['sex'] == '2') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="sex2" name="sex" value="<?= (isset($row_opdscreen['sex'])  ? htmlspecialchars($row_opdscreen['sex']) : htmlspecialchars($row['sex'])) ?>" onchange="sex_check('on_checked');">
                                    <label class="custom-control-label" for="sex2">Stridor</label>
                                </div>

                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>CXR</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="g" id="g">
                                </div>Sputum G/S<div class="col-sm-3">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="p" id="p">
                                </div>

                            </div>

                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ABG/VBG:PH</label>
                                <div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="g" id="g">
                                </div>PaCO2<div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="p" id="p">
                                </div>HCO3<div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="p" id="p">
                                </div>PaO2<div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="p" id="p">
                                </div>BE<div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="p" id="p">
                                </div>

                            </div>

                            <br>
                            <div class="form-group row alert alert-dark text-left">
                                <B>3.ด้านภาวะโภชนาการ (Nutrition)</B>
                            </div>


                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ประวัติโรคระบบทางเดินอาหาร</label>
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

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ส่วนสูง</label>
                                <div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="g" id="g">
                                </div>cms น้ำหนัก<div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="p" id="p">
                                </div>kg BMI:<div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="p" id="p">
                                </div>Kg/m<sub>2</sub> Alb:<div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="p" id="p">
                                </div>mmol

                            </div>

                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>BEE:</label>
                                <div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="g" id="g">
                                </div>TEE:<div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="p" id="p">
                                </div>SPENT Nutrition Screening Tool<div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="p" id="p">
                                </div>/ 4 คะแนน

                            </div>

                            <br>

                            <div class="form-group row alert alert-dark text-left">
                                <B>4.ด้านการติดต่อสื่อสาร (Communication)</B>
                            </div>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ประวัติโรคทางการสื่อสาร</label>
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

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>การพูด:</label>
                                <div class="custom-control custom-radio col-sm-2">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                    <label class="custom-control-label" for="body1">ไม่ได้ On ET-Tube</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                    <label class="custom-control-label" for="body1">พูดได้เองชัดเจน</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                    <label class="custom-control-label" for="body1">พูดไม่ชัด</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body'] != 'ปกติ' && $row['body'] != NULL) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="body2" onchange="body_check('on_entered');">
                                    <label class="custom-control-label" for="body2">อื่นๆ</label>
                                </div>

                                <div class="col-sm-3">
                                    <input type="text" class="form-control form-control-sm" id="body_text" name="body" value="<?php if ($row['body'] != 'ปกติ' && $row['body'] != NULL) {
                                                                                                                                    echo htmlspecialchars($row['body']);
                                                                                                                                } ?>" <?php if (!($row['body'] != 'ปกติ' && $row['body'] != NULL)) {
                                                                                                                                        echo 'disabled';
                                                                                                                                    } ?>>
                                </div>
                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label></label>
                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                    <label class="custom-control-label" for="body1">On ET-Tube or TT</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                    <label class="custom-control-label" for="body1">สื่อสารด้วยการเขียน</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                    <label class="custom-control-label" for="body1">สื่อสารโดยการใช้สายตา</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                    <label class="custom-control-label" for="body1">สื่อสารโดยใช้ท่าทาง</label>
                                </div>


                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label></label>


                                <div class="custom-control custom-radio col-sm-3">
                                    <input type="radio" <?php if ($row['body'] != 'ปกติ' && $row['body'] != NULL) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="body2" onchange="body_check('on_entered');">
                                    <label class="custom-control-label" for="body2">ไม่สามารถสื่อสารได้ เนื่องจาก</label>
                                </div>

                                <div class="col-sm-3">
                                    <input type="text" class="form-control form-control-sm" id="body_text" name="body" value="<?php if ($row['body'] != 'ปกติ' && $row['body'] != NULL) {
                                                                                                                                    echo htmlspecialchars($row['body']);
                                                                                                                                } ?>" <?php if (!($row['body'] != 'ปกติ' && $row['body'] != NULL)) {
                                                                                                                                        echo 'disabled';
                                                                                                                                    } ?>>
                                </div>
                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                    <label class="custom-control-label" for="body1">ประเมินไม่ได้</label>
                                </div>
                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>การมองเห็น: ตา</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                    <label class="custom-control-label" for="body1">เห็นชัดเจน</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                    <label class="custom-control-label" for="body1">เห็นไม่ชัดเจน</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body'] != 'ปกติ' && $row['body'] != NULL) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="body2" onchange="body_check('on_entered');">
                                    <label class="custom-control-label" for="body2">ตาบอด</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="body_text" name="body" value="<?php if ($row['body'] != 'ปกติ' && $row['body'] != NULL) {
                                                                                                                                    echo htmlspecialchars($row['body']);
                                                                                                                                } ?>" <?php if (!($row['body'] != 'ปกติ' && $row['body'] != NULL)) {
                                                                                                                                            echo 'disabled';
                                                                                                                                        } ?>>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                    <label class="custom-control-label" for="body1">ประเมินไม่ได้</label>
                                </div>
                            </div>
                            <br>

                            <div class="form-group row alert alert-dark text-left">
                                <B>5.ด้านการทำกิจกรรม (Activity)</B>
                            </div>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>มีประวัติโรคที่ส่งผลต่อการทำกิจกรรม</label>
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

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>การทำกิจวัตรประจำวัน</label>
                                <div class="custom-control custom-radio col-sm-2">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                    <label class="custom-control-label" for="body1">ช่วยเหลือตัวเองได้ดี</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                    <label class="custom-control-label" for="body1">Bed ridden</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                    <label class="custom-control-label" for="body1">หอบ เหนื่อย</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                    <label class="custom-control-label" for="body1">ถูกจำกัดกิจกรรมบนเตียง</label>
                                </div>

                            </div>
                            <br>
                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label></label>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['body'] != 'ปกติ' && $row['body'] != NULL) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="body2" onchange="body_check('on_entered');">
                                    <label class="custom-control-label" for="body2">มี ระบุ</label>
                                </div>

                                <div class="col-sm-4">
                                    <input type="text" class="form-control form-control-sm" id="body_text" name="body" value="<?php if ($row['body'] != 'ปกติ' && $row['body'] != NULL) {
                                                                                                                                    echo htmlspecialchars($row['body']);
                                                                                                                                } ?>" <?php if (!($row['body'] != 'ปกติ' && $row['body'] != NULL)) {
                                                                                                                                        echo 'disabled';
                                                                                                                                    } ?>>
                                </div>

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>Braden score</label>
                                <div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="g" id="g">
                                </div>/23 คะแนน


                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>Mortor power</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="g" id="g">
                                </div>MASS<div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="p" id="p">
                                </div> /6 คะแนน

                            </div>

                            <br>

                            <div class="form-group row alert alert-dark text-left">
                                <B>6.ด้านการกระตุ้น (Stimulation)</B>
                            </div>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>มีประวัติโรคที่ส่งผลต่อการกระตุ้น</label>
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

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>GCS: E</label>
                                <div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="g" id="g">
                                </div>V<div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="p" id="p">
                                </div>M<div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="p" id="p">
                                </div>Pupil<div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="p" id="p">
                                </div>RE<div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="p" id="p">
                                </div>mm.&nbsp;LE<div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="p" id="p">
                                </div>mm.

                            </div>

                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ระดับความรู้สึกตัว</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                    <label class="custom-control-label" for="body1">Alert</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                    <label class="custom-control-label" for="body1">Confuse</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                    <label class="custom-control-label" for="body1">Drowsiness</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                    <label class="custom-control-label" for="body1">Stupors</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                    <label class="custom-control-label" for="body1">Coma</label>
                                </div>

                            </div>
                            <br>

                            <div class="form-group row">
                                <label class="col-sm-12"><B> ผล CT-Brain</B></label>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <textarea class="form-control" id="family" name="family" rows="2"><?= (isset($row_opdscreen['fh']) && $id == null ? htmlspecialchars($row_opdscreen['fh']) : htmlspecialchars($row['family'])) ?></textarea>
                                </div>
                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>Pain score: ( ) COPT</label>
                                <div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="g" id="g">
                                </div>/8 คะแนน ( ) NRS<div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xx" name="p" id="p">
                                </div>/10 คะแนน

                            </div>

                            <br>

                            <div class="form-group row alert alert-dark text-left">
                                <B>สรุปปัญหา</B>
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-1"></div>
                                <div class="custom-control custom-checkbox col-sm-2">
                                    <input type="checkbox" <?php if ($row['taken_by_relative'] == 'Y') {
                                                                echo 'checked="checked"';
                                                            } ?> class="custom-control-input" id="r1" value="Y" name="taken_by_relative" onchange="custom_check('off_taken');">
                                    <label class="custom-control-label" for="r1">ด้านสมดุลของสารน้ำ(Fluid balance)</label>
                                </div>
                                <div class="custom-control custom-checkbox col-sm-2">
                                    <input type="checkbox" <?php if ($row['taken_by_nurse'] == 'Y') {
                                                                echo 'checked="checked"';
                                                            } ?> class="custom-control-input" id="r2" value="Y" name="taken_by_nurse" onchange="custom_check('off_taken');">
                                    <label class="custom-control-label" for="r2">ด้านการหายใจ(Aeration)</label>
                                </div>
                                <div class="custom-control custom-checkbox col-sm-3">
                                    <input type="checkbox" <?php if ($row['taken_by_crib'] == 'Y') {
                                                                echo 'checked="checked"';
                                                            } ?> class="custom-control-input" id="r3" value="Y" name="taken_by_crib" onchange="custom_check('off_taken');">
                                    <label class="custom-control-label" for="r3">ด้านภาวะโภชนาการ(Nutrition)</label>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-1"></div>
                                <div class="custom-control custom-checkbox col-sm-2">
                                    <input type="checkbox" <?php if ($row['taken_by_relative'] == 'Y') {
                                                                echo 'checked="checked"';
                                                            } ?> class="custom-control-input" id="r1" value="Y" name="taken_by_relative" onchange="custom_check('off_taken');">
                                    <label class="custom-control-label" for="r1">ด้านการติดต่อสื่อสาร(Communication)</label>
                                </div>
                                <div class="custom-control custom-checkbox col-sm-2">
                                    <input type="checkbox" <?php if ($row['taken_by_nurse'] == 'Y') {
                                                                echo 'checked="checked"';
                                                            } ?> class="custom-control-input" id="r2" value="Y" name="taken_by_nurse" onchange="custom_check('off_taken');">
                                    <label class="custom-control-label" for="r2">ด้านการทำกิจจกรรม(Activity)</label>
                                </div>
                                <div class="custom-control custom-checkbox col-sm-3">
                                    <input type="checkbox" <?php if ($row['taken_by_crib'] == 'Y') {
                                                                echo 'checked="checked"';
                                                            } ?> class="custom-control-input" id="r3" value="Y" name="taken_by_crib" onchange="custom_check('off_taken');">
                                    <label class="custom-control-label" for="r3">ด้านการกระตุ้น(Stimulation)</label>
                                </div>
                            </div>


                            <hr>

                            <div class="row">

                                <div class="col-md-6">
                                    <!-- begin table -->
                                    <table id="bg-table" width="100%" style="border-collapse: collapse;font-size:10pt;margin-top:8px;">
                                        <div style="text-align:center;"><b>MASS (Motor activity assessment scele)</b></div>
                                        <!-- 1-->
                                        <tr style="border:1px solid #000;margin: 45px;">
                                            <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;<b>Score</b></td>
                                            <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;<b>อาการ</b></td>
                                            <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="20%">&nbsp;<b>คำอธิบาย</b></td>

                                            <!-- 1-->
                                        <tr style="border:1px solid #000;margin: 45px;">
                                            <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;๐</td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;ไม่ตอบสนอง</td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="20%">&nbsp;ไม่เคลื่อนไหว หรือไม่ตอบสนองต่อการกระตุ้นอย่างรุนแรง</td>

                                            <!-- 1-->
                                        <tr style="border:1px solid #000;margin: 45px;">
                                            <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;๑</td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;ตอบสนองต่อการกระตุ้นอย่างรุนแรงเท่านั้น</td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="20%">&nbsp;ไม่เคลื่อนไหวหรือไม่ตอบสนองต่อสิ่งกระตุ้น</td>

                                            <!-- 1-->
                                        <tr style="border:1px solid #000;margin: 45px;">
                                            <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;๒</td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;ตอบสนองต่อการสัมผัสหรือการเรียกชื่อ</td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="20%">&nbsp;ลืมตาหรือเลิกคิ้วหรือหันศรีษะหรือขยับแขนขาเมื่อได้รับสิ่งกระตุ้นที่รุนแรง เช่นการดูดเสมหะหรือการกระตุ้นหน้าอก</td>

                                            <!-- 1-->
                                        <tr style="border:1px solid #000;margin: 45px;">
                                            <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;๓</td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;สงบและให้ความร่วมมือ</td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="20%">&nbsp;รู้ตัวดี สงบและให้ความร่วมมือ</td>

                                            <!-- 1-->
                                        <tr style="border:1px solid #000;margin: 45px;">
                                            <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;๔</td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;กระสับกระส่ายแต่ยังให้ความร่วมมือ</td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="20%">&nbsp;รู้ตัวทำตามคำสั่งได้ อยู่ไม่นิ่งและเอามือจับท่อช่วยหายใจ ดึงท่อหรือพลาสเตอร์ออก หรือเชือกผูกท่อ ช่วยตัวเองไม่ได้</td>

                                            <!-- 1-->
                                        <tr style="border:1px solid #000;margin: 45px;">
                                            <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;๕</td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;กระวนกระวาย</td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="20%">&nbsp;ทำตามคำสั่งได้แต่อยู่ไม่นิ่ง และพยายามลุกนั่งหรือยืน เมื่อขอร้องก็นอนลง แต่ไม่ช้าก็ลุกขึ้นขยับแขนขา</td>

                                            <!-- 1-->
                                        <tr style="border:1px solid #000;margin: 45px;">
                                            <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;๖</td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;กระวนกระวายมากจนอาจเป็นอันตราย</td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="20%">&nbsp;ไม่ทำตามคำสั่งและพยายามลุกนั่งหรือดิ้นไปมาพยายามลงจากเตียง ดึงท่อช่วยหายใจ สายและอุปกรณ์ต่างๆ หรือทำร้ายเจ้าหน้าที่</td>


                                    </table>

                                    <br>

                                </div>

                                <div class="col-md-6">
                                    <!-- begin table -->
                                    <table id="bg-table" width="100%" style="border-collapse: collapse;font-size:10pt;margin-top:8px;">
                                        <div style="text-align:center;">&nbsp;</div>
                                        <!-- 1-->
                                        <tr style="border:1px solid #000;margin: 45px;">
                                            <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="7%">&nbsp;<b>หมวด</b></td>
                                            <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;<b>คำอธิบาย</b></td>
                                            <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%">&nbsp;<b>คะแนน</b></td>

                                            <!-- 1-->
                                        <tr style="border:1px solid #000;margin: 45px;">
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="7%">&nbsp;การแสดงออกทางสีหน้า (Facial expression)</td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;ผ่อนคลายหน้าเรียบเฉย (relaxed)
                                                <br>&nbsp;หน้านิ่วคิ้วขมวด/ตึงเครียด
                                                <br>&nbsp;หน้านิ่วคิ้วขมวด/บึ้งตึงมาก เปลือกตาปิดแน่น
                                            </td>
                                            <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%">&nbsp;0
                                                <br>1
                                                <br>2
                                            </td>

                                            <!-- 1-->
                                        <tr style="border:1px solid #000;margin: 45px;">
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="7%">&nbsp;การเคลื่อนไหวของร่างกาย (Body movement)</td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;ไม่มีการเคลื่อนไหว
                                                <br>&nbsp;ปกป้องบริเวณที่ปวด
                                                <br>&nbsp;กระสับกระส่าย
                                            </td>
                                            <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%">&nbsp;0
                                                <br>1
                                                <br>2
                                            </td>

                                            <!-- 1-->
                                        <tr style="border:1px solid #000;margin: 45px;">
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="7%">&nbsp;การเกร็งของกล้ามเนื้อ (Muscle tention) ประเมินจากการเหยียดและการงอแขน</td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;ผ่อนคลาย
                                                <br>&nbsp;ตึงแข็ง
                                                <br>&nbsp;ตึงแข็งเป็นอย่างมาก
                                            </td>
                                            <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%">&nbsp;0
                                                <br>1
                                                <br>2
                                            </td>

                                            <!-- 1-->
                                        <tr style="border:1px solid #000;margin: 45px;">
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="7%">&nbsp;การหายใจสอดคล้องกับเครืองช่วยหายใจ
                                                สำหรับผู้ป่วยที่คาท่อหายใจ หรือการเปล่งเสียง สำหรับผู้ป่วยที่ไม่ได้ใส่ท่อช่วยหายใจ
                                            </td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;หายใจสอดคล้องกับเครื่องช่วยหายใจ
                                                <br>&nbsp;มีอาการไอ แต่สามารถหายใจขณะที่ใช้ เครื่องช่วยหายใจได้
                                                <br>&nbsp;มีการต้านเครื่องช่วยหายใจ
                                                <br>&nbsp;พูดด้วยน้ำเสียงปกติ
                                                <br>&nbsp;ถอนหายใจ ร้องคราง
                                                <br>&nbsp;ร้องไห้ สะอื้น
                                            </td>
                                            <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%">&nbsp;0
                                                <br>1
                                                <br>2
                                                <br>0
                                                <br>1
                                                <br>2
                                            </td>



                                    </table>

                                    <br>

                                </div>



                            </div>


                            <div class="row">

                                <div class="col-md-6">
                                    <div style="text-align:left; padding:5px;"><b>Total energy expenditure (TEE) = BEE x AF x SF</b></div>

                                    <hr>
                                    <div style="text-align:left; padding:5px;"><b>BEE = Basal energy expenditure, AF = Activity facter, SF = Stress facter</b></div>
                                    <div style="text-align:left; font-size: 12px;"><b>BEE: Harris-Benedict Equation</b></div>
                                    <div style="text-align:left; font-size: 12px;"><b>Mele: 66.5+13.8 W + 5.0 H - 6.8 A</b></div>
                                    <div style="text-align:left; font-size: 12px;"><b>Femele: 66.1+9.6 W + 1.8 H - 4.7 A</b></div>
                                </div>

                                <div class="col-md-6">
                                    <div style="text-align:left; padding:5px;"><b>SPENT 4 คะแนน</b></div>

                                    <div style="text-align:left; padding:5px;">( ) 1. นน.ลดโดยไม่ได้ตั้งใจในช่วง 6 เดือนที่ผ่านมา</div>
                                    <div style="text-align:left; padding:5px;">( ) 2. ผู้ป่วยได้รับอาหาร ที่เคยได้มากกว่า 7 วัน</div>
                                    <div style="text-align:left; padding:5px;">( ) 3. BMI < 18.5 or >= 25.0 kg/m<sup>2</sup></div>
                                    <div style="text-align:left; padding:5px;">( ) 4. ผู้ป่วยมีภาวะโรควิกฤต หรือกึ่งวิกฤต</div>
                                    
                                </div>

                            </div>
                            <br>

                            <div class="row">

                                <div class="col-md-12">
                                    <!-- begin table -->
                                    <table id="bg-table" width="100%" style="border-collapse: collapse;font-size:10pt;margin-top:8px;">

                                        <!-- 1-->
                                        <tr style="border:1px solid #000;margin: 45px;">
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="15%">&nbsp;<b>AF= Activity factor</b>
                                            </td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="3%">&nbsp;<b>ค่าคะแนน</b>
                                            </td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="15%">&nbsp;<b>SF= Stress factor</b>
                                            </td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="3%">&nbsp;<b>ค่าคะแนน</b>
                                            </td>

                                            <tr style="border:1px solid #000;margin: 45px;">
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="15%">&nbsp;On ventilator
                                            </td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="3%">&nbsp;0.9 - 1.0
                                            </td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="15%">&nbsp;<b>Fever</b>
                                            </td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="3%">&nbsp;<b>1 + 0.13/<sup>๐</sup>C</b>
                                            </td>

                                            <tr style="border:1px solid #000;margin: 45px;">
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="15%">&nbsp;นอนนบนเตียง ไม่เคลื่อนไหว
                                            </td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="3%">&nbsp;0.7 - 0.9
                                            </td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="15%">&nbsp;<b>Elective surgery</b>
                                            </td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="3%">&nbsp;<b>1.2</b>
                                            </td>

                                            <tr style="border:1px solid #000;margin: 45px;">
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="15%">&nbsp;เคลื่อนไหว ช่วยเหลือตัวเองได้บนเตียง
                                            </td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="3%">&nbsp;1.2
                                            </td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="15%">&nbsp;<b>Major sepsis</b>
                                            </td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="3%">&nbsp;<b>1.4 - 1.6</b>
                                            </td>

                                            <tr style="border:1px solid #000;margin: 45px;">
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="15%">&nbsp;ลุกจากเตียง + มี Activity
                                            </td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="3%">&nbsp;1.2
                                            </td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="15%">&nbsp;<b>Burn (20-40% body surface area)</b>
                                            </td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="3%">&nbsp;<b>1.5 - 2.0</b>
                                            </td>

                                            <tr style="border:1px solid #000;margin: 45px;">
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="15%">&nbsp;
                                            </td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="3%">&nbsp;
                                            </td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="15%">&nbsp;<b>Moderate infection</b>
                                            </td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="3%">&nbsp;<b>1.2 - 1.4</b>
                                            </td>

                                            <tr style="border:1px solid #000;margin: 45px;">
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="15%">&nbsp;
                                            </td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="3%">&nbsp;
                                            </td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="15%">&nbsp;<b>Mild infection</b>
                                            </td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="3%">&nbsp;<b>1.0 - 1.25</b>
                                            </td>

                                            <tr style="border:1px solid #000;margin: 45px;">
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="15%">&nbsp;
                                            </td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="3%">&nbsp;
                                            </td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="15%">&nbsp;<b>cancer</b>
                                            </td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="3%">&nbsp;<b>1.0 - 1.25</b>
                                            </td>

                                            <tr style="border:1px solid #000;margin: 45px;">
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="15%">&nbsp;
                                            </td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="3%">&nbsp;
                                            </td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="15%">&nbsp;<b>peritiontis</b>
                                            </td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="3%">&nbsp;<b>1.05 - 1.25</b>
                                            </td>

                                            <tr style="border:1px solid #000;margin: 45px;">
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="15%">&nbsp;
                                            </td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="3%">&nbsp;
                                            </td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="15%">&nbsp;<b>Soft tissue trauma</b>
                                            </td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="3%">&nbsp;<b>1.0 - 1.3</b>
                                            </td>


                                    </table>
                                </div>
                            </div>

<br>
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