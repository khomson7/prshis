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

$permissionCheck = Session::checkPermissionAndShowMessage('PRS_FORM_ICU1', 'VIEW');
$permissionCheckJson = json_encode($permissionCheck);




require_once '../mains/ipd-show-patient-main.php'; //เป็นส่วนที่แสดง ข้อมูลผู้ป่วย เช่น รูป,hn,an,ชื่อ-สกุล,แพ้ยา ฯลฯ
require_once '../mains/ipd-show-patient-sticky.php';
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';
require_once '../include/ReportQueryUtils.php';
require_once '../include/session-modal.php';
$conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
$an = $_REQUEST['an']; //รับค่า an
$ids = $_REQUEST['id']; //รับค่า an
$hn = KphisQueryUtils::getHnByAn($an); // function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา
$vn = KphisQueryUtils::getVnByAn($an);

//echo $id;


Session::insertSystemAccessLog(json_encode(array(
    'form' => 'ICU1-FORM',
    'an' => $an,
), JSON_UNESCAPED_UNICODE));




//----------------------เช็คว่า an นี้ มีข้อมูลหรือไม่

$sql = "SELECT *
                FROM `prs_icu_form`
                WHERE an = :an and id = :id";
$id  = null;
$parameters['an'] = $an;
$parameters['id'] = $ids;
$stmt = $conn->prepare($sql);
$stmt->execute($parameters);
if ($row  = $stmt->fetch()) {
    $id = $row['id'];
} else {
    $id = null;
}

//echo $id;

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

$id = '17'; //Link menu
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
                <h5><B>แบบประเมินผู้ป่วยวิกฤตแรกรับตามแนวคิด FANCAS <?= htmlspecialchars(DbConstant::HOSPITAL_NAME) ?>
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
                            <div class="form-group row">
                                <label class="col-sm-12"><B>ข้อมูลทั่วไป</B></label>
                            </div>
                            <div class="row">

                                <div class="col-sm-1"></div>
                                <label>รับใหม่วันที่</label>
                                <div class="col-sm-2">
                                    <input type="date" class="form-control form-control-sm" id="rxdate" name="rxdate" value="<?= (isset($row['rxdate']) ? htmlspecialchars($row['rxdate']) : '') ?>">
                                </div>
                                <label>รับใหม่/รับย้ายเวลา</label>
                                <div class="col-sm-2">
                                    <input type="time" class="form-control form-control-sm" id="rxtime" name="rxtime" value="<?= (isset($row['rxtime']) ? htmlspecialchars($row['rxtime']) : '') ?>">
                                </div>

                                &nbsp;<label>น. จากหน่วยงาน</label>
                                <div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm" id="from_dep" name="from_dep" value="<?= (isset($row['from_dep']) ? htmlspecialchars($row['from_dep']) : '') ?>">
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
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['heart_disease_history'] == 'ไม่มี') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="heart_disease_history1" name="heart_disease_history" value="ไม่มี" onchange="custom_check('off_heart_disease_history');">
                                    <label class="custom-control-label" for="heart_disease_history1">ไม่มี</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['heart_disease_history'] != 'ไม่มี' && $row['heart_disease_history'] != NULL) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="heart_disease_history2" onchange="custom_check('on_heart_disease_history');">
                                    <label class="custom-control-label" for="heart_disease_history2">มี ระบุ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="heart_disease_history_text" name="heart_disease_history" value="<?php if ($row['heart_disease_history'] != 'ไม่มี' && $row['heart_disease_history'] != NULL) {
                                                                                                                                                                    echo htmlspecialchars($row['heart_disease_history']);
                                                                                                                                                                } ?>" <?php if (!($row['heart_disease_history'] != 'ไม่มี' && $row['heart_disease_history'] != NULL)) {
                                                                                                                                                                            echo 'disabled';
                                                                                                                                                                        } ?>>
                                </div>


                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ผิวหนัง</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['skin'] == 'ปกติ') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="skin1" name="skin" value="ปกติ" onchange="custom_check('off_skin');">
                                    <label class="custom-control-label" for="skin1">ปกติ</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['skin'] == 'ซีด') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="skin2" name="skin" value="ซีด" onchange="custom_check('off_skin');">
                                    <label class="custom-control-label" for="skin2">ซีด</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['skin'] == 'เขียว') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="skin3" name="skin" value="เขียว" onchange="custom_check('off_skin');">
                                    <label class="custom-control-label" for="skin3">เขียว</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['skin'] == 'จุดจ้ำเลือด') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="skin4" name="skin" value="จุดจ้ำเลือด" onchange="custom_check('off_skin');">
                                    <label class="custom-control-label" for="skin4">จุดจ้ำเลือด</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['skin'] == 'แห้ง') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="skin5" name="skin" value="แห้ง" onchange="custom_check('off_skin');">
                                    <label class="custom-control-label" for="skin5">แห้ง</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if (
                                                            $row['skin'] != 'ปกติ'
                                                            && $row['skin'] != 'ซีด'
                                                            && $row['skin'] != 'เขียว'
                                                            && $row['skin'] != 'จุดจ้ำเลือด'
                                                            && $row['skin'] != 'แห้ง'
                                                            && $row['skin'] != NULL
                                                        ) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="skin6" value="บุ๋มกดบวม" onchange="custom_check('on_skin');">
                                    <label class="custom-control-label" for="skin6">บุ๋มกดบวม</label>
                                </div>

                                <div class="col-sm-4">
                                    <input type="text" class="form-control form-control-sm" id="skin_text" name="skin" value="<?php if (
                                                                                                                                    $row['skin'] != 'ปกติ' && $row['skin'] != 'ซีด'
                                                                                                                                    && $row['skin'] != 'เขียว'
                                                                                                                                    && $row['skin'] != 'จุดจ้ำเลือด'
                                                                                                                                    && $row['skin'] != 'แห้ง'
                                                                                                                                    && $row['skin'] != NULL
                                                                                                                                ) {
                                                                                                                                    echo htmlspecialchars($row['skin']);
                                                                                                                                } ?>" <?php if (!($row['skin'] != 'ปกติ' && $row['skin'] != 'ซีด'
                                                                                                                                            && $row['skin'] != 'เขียว'
                                                                                                                                            && $row['skin'] != 'จุดจ้ำเลือด'
                                                                                                                                            && $row['skin'] != 'แห้ง'
                                                                                                                                            && $row['skin'] != NULL)) {
                                                                                                                                            echo 'disabled';
                                                                                                                                        } ?>>
                                </div>


                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>Neck vien engorement</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['neck_vien_engorement'] == 'ไม่พบ') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="neck_vien_engorement1" name="neck_vien_engorement" value="ไม่พบ" onchange="custom_check('off_neck_vien_engorement');">
                                    <label class="custom-control-label" for="neck_vien_engorement1">ไม่พบ</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['neck_vien_engorement'] != 'ไม่พบ' && $row['neck_vien_engorement'] != 'ประเมินไม่ได้' && $row['neck_vien_engorement'] != NULL) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="neck_vien_engorement3" onchange="custom_check('on_neck_vien_engorement');">
                                    <label class="custom-control-label" for="neck_vien_engorement3">พบ ระบุ</label>
                                </div>

                                <div class="col-sm-4">
                                    <input type="text" class="form-control form-control-sm" id="neck_vien_engorement_text" name="neck_vien_engorement" value="<?php if ($row['neck_vien_engorement'] != 'ไม่พบ' && $row['neck_vien_engorement'] != 'ประเมินไม่ได้' && $row['neck_vien_engorement'] != NULL) {
                                                                                                                                                                    echo htmlspecialchars($row['neck_vien_engorement']);
                                                                                                                                                                } ?>" <?php if (!($row['neck_vien_engorement'] != 'ไม่พบ' && $row['neck_vien_engorement'] != 'ประเมินไม่ได้' && $row['neck_vien_engorement'] != NULL)) {
                                                                                                                                                                            echo 'disabled';
                                                                                                                                                                        } ?>>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['neck_vien_engorement'] == 'ประเมินไม่ได้') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="neck_vien_engorement2" name="neck_vien_engorement" value="ประเมินไม่ได้" onchange="custom_check('off_neck_vien_engorement');">
                                    <label class="custom-control-label" for="neck_vien_engorement2">ประเมินไม่ได้</label>
                                </div>


                            </div>
                            <br>

                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ฟังเสียงหัวใจ</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['listen_to_the_heart'] == '1') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="listen_to_the_heart1" name="listen_to_the_heart" value="1">
                                    <label class="custom-control-label" for="listen_to_the_heart1">Murmur</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <input type="radio" <?php if ($row['listen_to_the_heart'] == '2') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="listen_to_the_heart2" name="listen_to_the_heart" value="2">
                                    <label class="custom-control-label" for="listen_to_the_heart2">Rub</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <input type="radio" <?php if ($row['listen_to_the_heart'] == '3') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="listen_to_the_heart3" name="listen_to_the_heart" value="3">
                                    <label class="custom-control-label" for="listen_to_the_heart3">ไม่พบ</label>
                                </div>

                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>V/S&nbsp; BT&nbsp;</label>
                                <div>
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="เฉพาะตัวเลข" name="bt" id="bt" value="<?= (isset($row['bt']) ? htmlspecialchars(round(($row['bt']), 2)) : '') ?>">
                                </div>C&nbsp; PR&nbsp;<div>
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="เฉพาะตัวเลข" name="pr" id="pr" value="<?= (isset($row['pr']) ? htmlspecialchars(round(($row['pr']), 2)) : '') ?>">
                                </div>
                                <label>&nbsp;/min&nbsp;BP&nbsp;</label>
                                <div class="col-md-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="bps" name="bps" id="bps" value="<?= (isset($row['bps']) ? htmlspecialchars(round(($row['bps']), 2)) : '') ?>">

                                </div> /
                                <div class="col-md-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="bpd" name="bpd" id="bpd" value="<?= (isset($row['bpd']) ? htmlspecialchars(round(($row['bpd']), 2)) : '') ?>">

                                </div>
                                <label>&nbsp;mmHg</label>


                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ผลการตรวจ CBC : WBC&nbsp;</label>
                                <div>
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="cbc" id="cbc" value="<?= (isset($row['cbc']) ? htmlspecialchars($row['cbc']) : '') ?>">
                                </div>&nbsp; Hct&nbsp;<div>
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="hct" id="hct" value="<?= (isset($row['hct']) ? htmlspecialchars($row['hct']) : '') ?>">
                                </div> <label>% Hb&nbsp;</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="hb" id="hb" value="<?= (isset($row['hb']) ? htmlspecialchars($row['hb']) : '') ?>"> </div>
                                <label>Plt</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="plt" id="plt" value="<?= (isset($row['plt']) ? htmlspecialchars($row['plt']) : '') ?>"></div>
                                <label>PT</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="pt" id="pt" value="<?= (isset($row['pt']) ? htmlspecialchars($row['pt']) : '') ?>"></div>
                                <label>PTT</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="ptt" id="ptt" value="<?= (isset($row['ptt']) ? htmlspecialchars($row['ptt']) : '') ?>"></div>
                                <label>INR</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="inr" id="inr" value="<?= (isset($row['inr']) ? htmlspecialchars($row['inr']) : '') ?>"></div>

                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>Trop -T&nbsp;</label>
                                <div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="trop_t" id="trop_t" value="<?= (isset($row['trop_t']) ? htmlspecialchars($row['trop_t']) : '') ?>">
                                </div>&nbsp; CKMB&nbsp;<div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="ckmb" id="ckmb" value="<?= (isset($row['ckmb']) ? htmlspecialchars($row['ckmb']) : '') ?>">
                                </div> <label>CPK&nbsp;</label>
                                <div class="col-md-3"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="cpk" id="cpk" value="<?= (isset($row['cpk']) ? htmlspecialchars($row['cpk']) : '') ?>"> </div>


                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>Echo&nbsp;</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="echo" id="echo" value="<?= (isset($row['echo']) ? htmlspecialchars($row['echo']) : '') ?>">
                                </div>&nbsp; EKG&nbsp;<div class="col-sm-4">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="ekg" id="ekg" value="<?= (isset($row['ekg']) ? htmlspecialchars($row['ekg']) : '') ?>">
                                </div>


                            </div>
                            <br>

                            <div class="form-group row alert alert-dark text-left">
                                <B>1.2 Kidney system</B>
                            </div>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ประวัติโรคไต</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['kidney_disease_history'] == 'ไม่มี') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="kidney_disease_history1" name="kidney_disease_history" value="ไม่มี" onchange="custom_check('off_kidney_disease_history');">
                                    <label class="custom-control-label" for="kidney_disease_history1">ไม่มี</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['kidney_disease_history'] != 'ไม่มี' && $row['kidney_disease_history'] != NULL) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="kidney_disease_history2" onchange="custom_check('on_kidney_disease_history');">
                                    <label class="custom-control-label" for="kidney_disease_history2">มี ระบุ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="kidney_disease_history_text" name="kidney_disease_history" value="<?php if ($row['kidney_disease_history'] != 'ไม่มี' && $row['kidney_disease_history'] != NULL) {
                                                                                                                                                                        echo htmlspecialchars($row['kidney_disease_history']);
                                                                                                                                                                    } ?>" <?php if (!($row['kidney_disease_history'] != 'ไม่มี' && $row['kidney_disease_history'] != NULL)) {
                                                                                                                                                                                echo 'disabled';
                                                                                                                                                                            } ?>>
                                </div>


                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ลักษณะปัสสาวะ&nbsp;</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="urine_characteristics" id="urine_characteristics" value="<?= (isset($row['urine_characteristics']) ? htmlspecialchars($row['urine_characteristics']) : '') ?>">
                                </div>&nbsp; I/O ใน 24 ชม.&nbsp;<div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="io_1" id="io_1" value="<?= (isset($row['io_1']) ? htmlspecialchars($row['io_1']) : '') ?>">
                                </div> &nbsp; /&nbsp;<div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="io_2" id="io_2" value="<?= (isset($row['io_2']) ? htmlspecialchars($row['io_2']) : '') ?>">
                                </div>ซีซี

                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ผลการตรวจ LAB BUN&nbsp;</label>
                                <div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="bun" id="bun" value="<?= (isset($row['bun']) ? htmlspecialchars($row['bun']) : '') ?>">
                                </div>&nbsp;Cr&nbsp;<div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="cr" id="cr" value="<?= (isset($row['cr']) ? htmlspecialchars($row['cr']) : '') ?>">
                                </div> &nbsp;GFR&nbsp;<div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="gfr" id="gfr" value="<?= (isset($row['gfr']) ? htmlspecialchars($row['gfr']) : '') ?>">
                                </div>&nbsp;E'lyte&nbsp;Na:<div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="e_lyte_na" id="e_lyte_na" value="<?= (isset($row['e_lyte_na']) ? htmlspecialchars($row['e_lyte_na']) : '') ?>">
                                </div>&nbsp;K:<div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="e_lyte_k" id="e_lyte_k" value="<?= (isset($row['e_lyte_k']) ? htmlspecialchars($row['e_lyte_k']) : '') ?>">
                                </div>&nbsp;Cl:<div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="e_lyte_cl" id="e_lyte_cl" value="<?= (isset($row['e_lyte_cl']) ? htmlspecialchars($row['e_lyte_cl']) : '') ?>">
                                </div>&nbsp;<p>Co<sub>2</sub>:</p>
                                <div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="e_lyte_co2" id="e_lyte_co2" value="<?= (isset($row['e_lyte_co2']) ? htmlspecialchars($row['e_lyte_co2']) : '') ?>">
                                </div>&nbsp;Anien Gap:<div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="e_lyte_aniengap" id="e_lyte_aniengap" value="<?= (isset($row['e_lyte_aniengap']) ? htmlspecialchars($row['e_lyte_aniengap']) : '') ?>">
                                </div>


                            </div>
                            <br>


                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>Ca&nbsp;</label>
                                <div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="ca" id="ca" value="<?= (isset($row['ca']) ? htmlspecialchars($row['ca']) : '') ?>">
                                </div>&nbsp;<p>Po<sub>4</sub></p>&nbsp;<div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="po_4" id="po_4" value="<?= (isset($row['po_4']) ? htmlspecialchars($row['po_4']) : '') ?>">
                                </div> &nbsp;Mg&nbsp;<div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="mg" id="mg" value="<?= (isset($row['mg']) ? htmlspecialchars($row['mg']) : '') ?>">
                                </div>&nbsp;DTX&nbsp;<div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="dtx" id="dtx" value="<?= (isset($row['dtx']) ? htmlspecialchars($row['dtx']) : '') ?>">
                                </div>&nbsp;mg% Urine Sp.gr&nbsp;<div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="urine_sr_gr" id="urine_sr_gr" value="<?= (isset($row['urine_sr_gr']) ? htmlspecialchars($row['urine_sr_gr']) : '') ?>">
                                </div>

                            </div>
                            <br>


                            <div class="form-group row alert alert-dark text-left">
                                <B>2.ด้านการหายใจ (Aeration)</B>
                            </div>


                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ประวัติโรคปอด</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['history_of_lung_disease'] == 'ไม่มี') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="history_of_lung_disease1" name="history_of_lung_disease" value="ไม่มี" onchange="custom_check('off_history_of_lung_disease');">
                                    <label class="custom-control-label" for="history_of_lung_disease1">ไม่มี</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['history_of_lung_disease'] != 'ไม่มี' && $row['history_of_lung_disease'] != NULL) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="history_of_lung_disease2" value="มี ระบุ" onchange="custom_check('on_history_of_lung_disease');">
                                    <label class="custom-control-label" for="history_of_lung_disease2">มี ระบุ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="history_of_lung_disease_text" name="history_of_lung_disease" value="<?php if ($row['history_of_lung_disease'] != 'ไม่มี' && $row['history_of_lung_disease'] != NULL) {
                                                                                                                                                                        echo htmlspecialchars($row['history_of_lung_disease']);
                                                                                                                                                                    } ?>" <?php if (!($row['history_of_lung_disease'] != 'ไม่มี' && $row['history_of_lung_disease'] != NULL)) {
                                                                                                                                                                                echo 'disabled';
                                                                                                                                                                            } ?>>
                                </div>


                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>การหายใจ RR&nbsp;</label>
                                <div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="rr" id="rr" value="<?= (isset($row['rr']) ? htmlspecialchars($row['rr']) : '') ?>">
                                </div>&nbsp;/min&nbsp;O2Sat<div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="o2_sat" id="o2_sat" value="<?= (isset($row['o2_sat']) ? htmlspecialchars($row['o2_sat']) : '') ?>">
                                </div>

                                &nbsp;% &nbsp;&nbsp;&nbsp;&nbsp;<label><b>On</b></label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp; <input type="radio" <?php if ($row['et_other'] == 'ET-Tube') {
                                                                                        echo 'checked="checked"';
                                                                                    } ?> class="custom-control-input" id="et_other1" name="et_other" value="ET-Tube" onchange="custom_check('off_on_et');">
                                    <label class="custom-control-label" for="et_other1">ET-Tube</label>

                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['et_other'] == 'TT-Tube') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="et_other2" name="et_other" value="TT-Tube" onchange="custom_check('off_on_tt');">
                                    <label class="custom-control-label" for="et_other2">TT-Tube No</label>
                                </div>

                                <div class="col-sm-1">

                                    <input type="text" class="form-control form-control-sm" id="et_tube_no_text" name="et_tube_no" value="<?php if (($row['et_other'] != 'ET-Tube' || $row['et_other'] != 'TT-Tube')) {
                                                                                                                                                echo htmlspecialchars($row['et_tube_no']);
                                                                                                                                            } ?>" <?php if (!(($row['et_other'] != 'ET-Tube' || $row['et_other'] != 'TT-Tube') && $row['et_other'] != NULL)) {
                                                                                                                                                                                echo 'disabled';
                                                                                                                                                                            } ?> <?php if (($row['et_other'] == 'RA' || $row['et_other'] == 'O2HFNC')) {
                                                                                                                                                                                        echo 'disabled';
                                                                                                                                                                                    } ?>>

                                </div>&nbsp;ขีด&nbsp;
                                <div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm" id="et_tube_no_text2" name="et_tube_no2" value="<?php if (($row['et_other'] != 'TT-Tube')) {
                                                                                                                                                echo htmlspecialchars($row['et_tube_no2']);
                                                                                                                                            } ?>" <?php if (!(($row['et_other'] != 'TT-Tube') && $row['et_other'] != NULL)) {
                                                                                                                                                                                echo 'disabled';
                                                                                                                                                                            } ?> <?php if (($row['et_other'] == 'RA' || $row['et_other'] == 'O2HFNC')) {
                                                                                                                                                                                        echo 'disabled';
                                                                                                                                                                                    } ?>>


                                </div>&nbsp;cms.&nbsp;

                            </div>
                            <br>

                            <div class="row">



                                <div class="custom-control custom-radio">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['et_other'] == 'O2HFNC') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="et_other3" name="et_other" value="O2HFNC" onchange="custom_check('off_on_o2h');">
                                    <label class="custom-control-label" for="et_other3">
                                        <p>O<sub>2</sub>HFNC</p>&nbsp;
                                    </label>

                                </div>

                                <div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm" id="o2_hfnc_text" name="o2_hfnc" value="<?php if (($row['et_other'] == 'O2HFNC')) {
                                                                                                                                        echo htmlspecialchars($row['o2_hfnc']);
                                                                                                                                    } ?>" <?php if (($row['et_other'] == 'RA' || $row['et_other'] == 'ET-Tube'
                                                                                                                                                                                || $row['et_other'] == 'TT-Tube'
                                                                                                                                                                                || $row['et_other'] == 'Candular'
                                                                                                                                                                                || $row['et_other'] == 'Mark c bag' || $row['et_other'] == '')) {
                                                                                                                                                                                echo 'disabled';
                                                                                                                                                                            } ?>>



                                </div>

                                <div class="custom-control custom-radio">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['et_other'] == 'candular') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="et_other4" name="et_other" value="candular" onchange="custom_check('off_on_candular');">
                                    <label class="custom-control-label" for="et_other4">Candular</label>

                                </div>

                                <div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm" id="candular_text" name="candular" value="<?php if (($row['et_other'] == 'candular')) {
                                                                                                                                            echo htmlspecialchars($row['candular']);
                                                                                                                                        } ?>" <?php if (($row['et_other'] == 'RA' || $row['et_other'] == 'ET-Tube'
                                                                                                                                                                                || $row['et_other'] == 'TT-Tube'
                                                                                                                                                                                || $row['et_other'] == 'O2HFNC'
                                                                                                                                                                                || $row['et_other'] == 'Mark c bag' || $row['et_other'] == '')) {
                                                                                                                                                                                echo 'disabled';
                                                                                                                                                                            } ?>>



                                </div>

                                <div class="custom-control custom-radio">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['et_other'] == 'Mark c bag') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="et_other5" name="et_other" value="Mark c bag" onchange="custom_check('off_on_mark_c_bag');">
                                    <label class="custom-control-label" for="et_other5">Mark c bag</label>

                                </div>


                                <div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm" id="mark_c_bag_text" name="mark_c_bag" value="<?php if (($row['et_other'] == 'Mark c bag')) {
                                                                                                                                                echo htmlspecialchars($row['mark_c_bag']);
                                                                                                                                            } ?>" <?php if (($row['et_other'] == 'RA' || $row['et_other'] == 'ET-Tube'
                                                                                                                                                                                || $row['et_other'] == 'TT-Tube'
                                                                                                                                                                                || $row['et_other'] == 'O2HFNC'
                                                                                                                                                                                || $row['et_other'] == 'candular' || $row['et_other'] == '')) {
                                                                                                                                                                                echo 'disabled';
                                                                                                                                                                            } ?>>



                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['et_other'] == 'RA') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="et_other6" name="et_other" value="RA" onchange="custom_check('off_on_ra');">
                                    <label class="custom-control-label" for="et_other6">RA</label>

                                </div>

                            </div>


                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ลักษณะการหายใจ</label>
                                <div class="custom-control custom-radio col-sm-2">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <input type="radio" <?php if ($row['breathing_characteristics'] == '1') {
                                                                                                        echo 'checked="checked"';
                                                                                                    } ?> class="custom-control-input" id="breathing_characteristics1" name="breathing_characteristics" value="1">
                                    <label class="custom-control-label" for="breathing_characteristics1">หายใจหอบ</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <input type="radio" <input type="radio" <?php if ($row['breathing_characteristics'] == '2') {
                                                                                                    echo 'checked="checked"';
                                                                                                } ?> class="custom-control-input" id="breathing_characteristics2" name="breathing_characteristics" value="2">
                                    <label class="custom-control-label" for="breathing_characteristics2">หายใจลำบาก</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <input type="radio" <input type="radio" <?php if ($row['breathing_characteristics'] == '3') {
                                                                                                    echo 'checked="checked"';
                                                                                                } ?> class="custom-control-input" id="breathing_characteristics3" name="breathing_characteristics" value="3">
                                    <label class="custom-control-label" for="breathing_characteristics3">หายใจปกติ</label>
                                </div>

                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>On ICD</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['on_icd'] == 'ไม่มี') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="on_icd1" name="on_icd" value="ไม่มี" onchange="custom_check('off_on_icd');">
                                    <label class="custom-control-label" for="on_icd1">ไม่มี</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['on_icd'] != 'ไม่มี' && $row['on_icd'] != NULL) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="on_icd2" onchange="custom_check('on_on_icd');">
                                    <label class="custom-control-label" for="on_icd2">มี ข้าง</label>
                                </div>

                                <div class="col-sm-3">
                                    <input type="text" class="form-control form-control-sm" id="on_icd_text" name="on_icd" value="<?php if ($row['on_icd'] != 'ไม่มี' && $row['on_icd'] != NULL) {
                                                                                                                                        echo htmlspecialchars($row['on_icd']);
                                                                                                                                    } ?>" <?php if (!($row['on_icd'] != 'ไม่มี' && $row['on_icd'] != NULL)) {
                                                                                                                                                echo 'disabled';
                                                                                                                                            } ?>>
                                </div>

                                &nbsp;ขีด&nbsp;<div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="on_icd_2" id="on_icd_2" value="<?= (isset($row['on_icd_2']) ? htmlspecialchars($row['on_icd_2']) : '') ?>">
                                </div>


                            </div>
                            <br>

                            <div class="row">
                            &nbsp;&nbsp;&nbsp;&nbsp;<label>ฟังเสียงลมเข้าปอด</label>

                            <div class="custom-control custom-checkbox col-sm-1">
                            &nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" <?php if ($row['listen_sound_lungs_clear'] == 'Y') {
                                                                                                        echo 'checked="checked"';
                                                                                                    } ?>class="custom-control-input" id="listen_sound_lungs_clear" value="Y" name="listen_sound_lungs_clear">
                                <label class="custom-control-label" for="listen_sound_lungs_clear">Clear</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                            &nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" <?php if ($row['listen_sound_lungs_crepitation'] == 'Y') {
                                                                                                        echo 'checked="checked"';
                                                                                                    } ?>class="custom-control-input" id="listen_sound_lungs_crepitation" value="Y" name="listen_sound_lungs_crepitation">
                                <label class="custom-control-label" for="listen_sound_lungs_crepitation">Crepitation</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                            &nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" <?php if ($row['listen_sound_lungs_wheezing'] == 'Y') {
                                                                                                        echo 'checked="checked"';
                                                                                                    } ?>class="custom-control-input" id="listen_sound_lungs_wheezing" value="Y" name="listen_sound_lungs_wheezing">
                                <label class="custom-control-label" for="listen_sound_lungs_wheezing">Wheezing</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                            &nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" <?php if ($row['listen_sound_lungs_rhonchi'] == 'Y') {
                                                                                                        echo 'checked="checked"';
                                                                                                    } ?>class="custom-control-input" id="listen_sound_lungs_rhonchi" value="Y" name="listen_sound_lungs_rhonchi">
                                <label class="custom-control-label" for="listen_sound_lungs_rhonchi">Rhonchi</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                            &nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" <?php if ($row['listen_sound_lungs_stridor'] == 'Y') {
                                                                                                        echo 'checked="checked"';
                                                                                                    } ?>class="custom-control-input" id="listen_sound_lungs_stridor" value="Y" name="listen_sound_lungs_stridor">
                                <label class="custom-control-label" for="listen_sound_lungs_stridor">Stridor</label>
                            </div>




    <!--                            
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <input type="radio" <?php if ($row['listen_sound_lungs'] == '1') {
                                                                                                        echo 'checked="checked"';
                                                                                                    } ?> class="custom-control-input" id="listen_sound_lungs1" name="listen_sound_lungs" value="1">
                                    <label class="custom-control-label" for="listen_sound_lungs1">Clear</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <input type="radio" <?php if ($row['listen_sound_lungs'] == '2') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="listen_sound_lungs1" name="listen_sound_lungs" value="2">
                                    <label class="custom-control-label" for="listen_sound_lungs2">Crepitation</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <input type="radio" <?php if ($row['listen_sound_lungs'] == '3') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="listen_sound_lungs3" name="listen_sound_lungs" value="3">
                                    <label class="custom-control-label" for="listen_sound_lungs3">Wheezing</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <input type="radio" <?php if ($row['listen_sound_lungs'] == '4') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="listen_sound_lungs4" name="listen_sound_lungs" value="4">
                                    <label class="custom-control-label" for="listen_sound_lungs4">Rhonchi</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <input type="radio" <?php if ($row['listen_sound_lungs'] == '5') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="listen_sound_lungs5" name="listen_sound_lungs" value="5">
                                    <label class="custom-control-label" for="listen_sound_lungs5">Stridor</label>
                                </div>

-->

                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>CXR</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="cxr" id="cxr" value="<?= (isset($row['cxr']) ? htmlspecialchars($row['cxr']) : '') ?>">
                                </div>Sputum G/S<div class="col-sm-3">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="sputum" id="sputum" value="<?= (isset($row['sputum']) ? htmlspecialchars($row['sputum']) : '') ?>">
                                </div>

                            </div>

                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ABG/VBG:PH</label>
                                <div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="abg" id="abg" value="<?= (isset($row['abg']) ? htmlspecialchars($row['abg']) : '') ?>">
                                </div>PaCO2<div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="pa_co2" id="pa_co2" value="<?= (isset($row['pa_co2']) ? htmlspecialchars($row['pa_co2']) : '') ?>">
                                </div>HCO3<div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="hco3" id="hco3" value="<?= (isset($row['hco3']) ? htmlspecialchars($row['hco3']) : '') ?>">
                                </div>PaO2<div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="pao2" id="pao2" value="<?= (isset($row['pao2']) ? htmlspecialchars($row['pao2']) : '') ?>">
                                </div>BE<div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="be" id="be" value="<?= (isset($row['be']) ? htmlspecialchars($row['be']) : '') ?>">
                                </div>

                            </div>

                            <br>
                            <div class="form-group row alert alert-dark text-left">
                                <B>3.ด้านภาวะโภชนาการ (Nutrition)</B>
                            </div>


                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ประวัติโรคระบบทางเดินอาหาร</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['history_of_gastrointestinal'] == 'ไม่มี') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="history_of_gastrointestinal1" name="history_of_gastrointestinal" value="ไม่มี" onchange="custom_check('off_history_of_gastrointestinal');">
                                    <label class="custom-control-label" for="history_of_gastrointestinal1">ไม่มี</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['history_of_gastrointestinal'] != 'ไม่มี' && $row['history_of_gastrointestinal'] != NULL) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="history_of_gastrointestinal2" onchange="custom_check('on_history_of_gastrointestinal');">
                                    <label class="custom-control-label" for="history_of_gastrointestinal2">มี ระบุ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="history_of_gastrointestinal_text" name="history_of_gastrointestinal" value="<?php if ($row['history_of_gastrointestinal'] != 'ไม่มี' && $row['history_of_gastrointestinal'] != NULL) {
                                                                                                                                                                                echo htmlspecialchars($row['history_of_gastrointestinal']);
                                                                                                                                                                            } ?>" <?php if (!($row['history_of_gastrointestinal'] != 'ไม่มี' && $row['history_of_gastrointestinal'] != NULL)) {
                                                                                                                                                                                        echo 'disabled';
                                                                                                                                                                                    } ?>>
                                </div>


                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ส่วนสูง</label>
                                <div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="เฉพาะตัวเลข" name="hight" id="hight" value="<?= (isset($row['hight']) ? htmlspecialchars($row['hight']) : '') ?>">
                                </div>cms น้ำหนัก<div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="เฉพาะตัวเลข" name="bw" id="bw" value="<?= (isset($row['bw']) ? htmlspecialchars($row['bw']) : '') ?>">
                                </div>kg BMI:<div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="เฉพาะตัวเลข" name="bmi" id="bmi" value="<?= (isset($row['bmi']) ? htmlspecialchars($row['bmi']) : '') ?>">
                                </div>Kg/m<sub>2</sub> Alb:<div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="เฉพาะตัวเลข" name="alb" id="alb" value="<?= (isset($row['alb']) ? htmlspecialchars($row['alb']) : '') ?>">
                                </div>mmol

                            </div>

                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>BEE:</label>
                                <div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="เฉพาะตัวเลข" name="bee" id="bee" value="<?= (isset($row['bee']) ? htmlspecialchars($row['bee']) : '') ?>">
                                </div>TEE:<div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="เฉพาะตัวเลข" name="tee" id="tee" value="<?= (isset($row['tee']) ? htmlspecialchars($row['tee']) : '') ?>">
                                </div>SPENT Nutrition Screening Tool<div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="เฉพาะตัวเลข" name="spent" id="spent" value="<?= (isset($row['spent']) ? htmlspecialchars($row['spent']) : '') ?>">
                                </div>/ 4 คะแนน

                            </div>

                            <br>

                            <div class="form-group row alert alert-dark text-left">
                                <B>4.ด้านการติดต่อสื่อสาร (Communication)</B>
                            </div>

                            <div class="row">
                           
                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ประวัติโรคทางการสื่อสาร</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['communication_history'] == 'ไม่มี') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="communication_history1" name="communication_history" value="ไม่มี" onchange="custom_check('off_communication_history');">
                                    <label class="custom-control-label" for="communication_history1">ไม่มี</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['communication_history'] != 'ไม่มี' && $row['communication_history'] != NULL) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="communication_history2" onchange="custom_check('on_communication_history');">
                                    <label class="custom-control-label" for="communication_history2">มี ระบุ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="communication_history_text" name="communication_history" value="<?php if ($row['communication_history'] != 'ไม่มี' && $row['communication_history'] != NULL) {
                                                                                                                                                                    echo htmlspecialchars($row['communication_history']);
                                                                                                                                                                } ?>" <?php if (!($row['communication_history'] != 'ไม่มี' && $row['communication_history'] != NULL)) {
                                                                                                                                                                            echo 'disabled';
                                                                                                                                                                        } ?>>
                                </div>


                            </div>
                            <br>

                            <div class="row">
                            <div class="custom-control custom-checkbox">
                                    <input type="radio" class="custom-control-input" id="speaking0" name="speaking" value="" onchange="custom_check('on_speaking_reset');">
                                    <label class="custom-control-label" for="speaking0">reset</label>

                                </div>
                                &nbsp;&nbsp;&nbsp;&nbsp;<label>การพูด:</label>
                                <div class="custom-control col-sm-2">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['speaking'] == 'ไม่ได้ On ET-Tube') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="speaking1" name="speaking" value="ไม่ได้ On ET-Tube" onchange="custom_check('off_speaking');">
                                    <label class="custom-control-label" for="speaking1">ไม่ได้ On ET-Tube</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php if ($row['speaking'] == 'พูดได้เองชัดเจน') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="speaking2" name="speaking" value="พูดได้เองชัดเจน" onchange="custom_check('off_speaking');">
                                    <label class="custom-control-label" for="speaking2">พูดได้เองชัดเจน</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['speaking'] == 'พูดไม่ชัด') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="speaking3" name="speaking" value="พูดไม่ชัด" onchange="custom_check('off_speaking');">
                                    <label class="custom-control-label" for="speaking3">พูดไม่ชัด</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if (
                                                                                    $row['speaking'] != 'ไม่ได้ On ET-Tube'
                                                                                    && $row['speaking'] != 'พูดได้เองชัดเจน' && $row['speaking'] != 'พูดไม่ชัด' && $row['speaking'] != NULL
                                                                                ) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="speaking4" onchange="custom_check('on_speaking');">
                                    <label class="custom-control-label" for="speaking4">อื่นๆ</label>
                                </div>

                                <div class="col-sm-3">
                                    <input type="text" class="form-control form-control-sm" id="speaking_text" name="speaking" value="<?php if (
                                                                                                                                            $row['speaking'] != 'ไม่ได้ On ET-Tube'
                                                                                                                                            && $row['speaking'] != 'พูดได้เองชัดเจน' && $row['speaking'] != 'พูดไม่ชัด' && $row['speaking'] != NULL
                                                                                                                                        ) {
                                                                                                                                            echo htmlspecialchars($row['speaking']);
                                                                                                                                        } ?>" <?php if (!($row['speaking'] != 'ไม่ได้ On ET-Tube'
                                                                                                                                                    && $row['speaking'] != 'พูดได้เองชัดเจน' && $row['speaking'] != 'พูดไม่ชัด' && $row['speaking'] != NULL)) {
                                                                                                                                                    echo 'disabled';
                                                                                                                                                } ?>>
                                </div>
                            </div>
                            <br>

                            <div class="row">
                            <div class="custom-control custom-checkbox">
                                    <input type="radio" class="custom-control-input" id="communication0" name="communication" value="" onchange="custom_check('on_communication_reset');">
                                    <label class="custom-control-label" for="communication0">reset</label>

                                </div>
                                &nbsp;&nbsp;&nbsp;&nbsp;<label></label>
                                <div class="custom-control col-sm-2">
                                <input type="radio" <?php if ($row['communication'] == 'On ET-Tube or TT') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="communication1" name="communication" value="On ET-Tube or TT" onchange="custom_check('off_communication');">
                                    <label class="custom-control-label" for="communication1">On ET-Tube or TT</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php if ($row['communication'] == 'สื่อสารด้วยการเขียน') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="communication2" name="communication" value="สื่อสารด้วยการเขียน" onchange="custom_check('off_communication');">
                                    <label class="custom-control-label" for="communication2">สื่อสารด้วยการเขียน</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php if ($row['communication'] == 'สื่อสารโดยการใช้สายตา') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="communication3" name="communication" value="สื่อสารโดยการใช้สายตา" onchange="custom_check('off_communication');">
                                    <label class="custom-control-label" for="communication3">สื่อสารโดยการใช้สายตา</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php if ($row['communication'] == 'สื่อสารโดยใช้ท่าทาง') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="communication4" name="communication" value="สื่อสารโดยใช้ท่าทาง" onchange="custom_check('off_communication');">
                                    <label class="custom-control-label" for="communication4">สื่อสารโดยใช้ท่าทาง</label>
                                </div>


                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label></label>


                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if (
                                                            $row['communication'] != 'On ET-Tube or TT'
                                                            && $row['communication'] != 'สื่อสารด้วยการเขียน'
                                                            && $row['communication'] != 'สื่อสารโดยการใช้สายตา'
                                                            && $row['communication'] != 'สื่อสารโดยใช้ท่าทาง'
                                                            && $row['communication'] != 'ประเมินไม่ได้'
                                                            && $row['communication'] != NULL
                                                        ) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="communication6" onchange="custom_check('on_communication');">
                                    <label class="custom-control-label" for="communication6">ไม่สามารถสื่อสารได้ เนื่องจาก</label>
                                </div>

                                <div class="col-sm-3">
                                    <input type="text" class="form-control form-control-sm" id="communication_text" name="communication" value="<?php if (
                                                                                                                                                    $row['communication'] != 'On ET-Tube or TT'
                                                                                                                                                    && $row['communication'] != 'สื่อสารด้วยการเขียน'
                                                                                                                                                    && $row['communication'] != 'สื่อสารโดยการใช้สายตา'
                                                                                                                                                    && $row['communication'] != 'สื่อสารโดยใช้ท่าทาง'
                                                                                                                                                    && $row['communication'] != 'ประเมินไม่ได้'
                                                                                                                                                    && $row['communication'] != NULL
                                                                                                                                                ) {
                                                                                                                                                    echo htmlspecialchars($row['communication']);
                                                                                                                                                } ?>" <?php if (!($row['communication'] != 'On ET-Tube or TT'
                                                                                                                                                            && $row['communication'] != 'สื่อสารด้วยการเขียน'
                                                                                                                                                            && $row['communication'] != 'สื่อสารโดยการใช้สายตา'
                                                                                                                                                            && $row['communication'] != 'สื่อสารโดยใช้ท่าทาง'
                                                                                                                                                            && $row['communication'] != 'ประเมินไม่ได้'
                                                                                                                                                            && $row['communication'] != NULL)) {
                                                                                                                                                            echo 'disabled';
                                                                                                                                                        } ?>>
                                </div>
                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php if ($row['communication'] == 'ประเมินไม่ได้') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="communication5" name="communication" value="ประเมินไม่ได้" onchange="custom_check('off_communication');">
                                    <label class="custom-control-label" for="communication5">ประเมินไม่ได้</label>
                                </div>
                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>การมองเห็น: ตา</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['vision'] == 'เห็นชัดเจน') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="vision1" name="vision" value="เห็นชัดเจน" onchange="custom_check('off_vision');">
                                    <label class="custom-control-label" for="vision1">เห็นชัดเจน</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['vision'] == 'เห็นไม่ชัดเจน') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="vision2" name="vision" value="เห็นไม่ชัดเจน" onchange="custom_check('off_vision');">
                                    <label class="custom-control-label" for="vision2">เห็นไม่ชัดเจน</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if (
                                                                                    $row['vision'] != 'เห็นชัดเจน'
                                                                                    && $row['vision'] != 'เห็นไม่ชัดเจน'
                                                                                    && $row['vision'] != 'ประเมินไม่ได้'
                                                                                    && $row['vision'] != NULL
                                                                                ) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="vision4" onchange="custom_check('on_vision');">
                                    <label class="custom-control-label" for="vision4">ตาบอด</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="vision_text" name="vision" value="<?php if (
                                                                                                                                        $row['vision'] != 'เห็นชัดเจน'
                                                                                                                                        && $row['vision'] != 'เห็นไม่ชัดเจน'
                                                                                                                                        && $row['vision'] != 'ประเมินไม่ได้'
                                                                                                                                        && $row['vision'] != NULL
                                                                                                                                    ) {
                                                                                                                                        echo htmlspecialchars($row['vision']);
                                                                                                                                    } ?>" <?php if (!($row['vision'] != 'เห็นชัดเจน'
                                                                                                                                                && $row['vision'] != 'เห็นไม่ชัดเจน'
                                                                                                                                                && $row['vision'] != 'ประเมินไม่ได้'
                                                                                                                                                && $row['vision'] != NULL)) {
                                                                                                                                                echo 'disabled';
                                                                                                                                            } ?>>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['vision'] == 'ประเมินไม่ได้') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="vision3" name="vision" value="ประเมินไม่ได้" onchange="custom_check('off_vision');">
                                    <label class="custom-control-label" for="vision3">ประเมินไม่ได้</label>
                                </div>
                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>การได้ยิน: หู</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['listening'] == '1') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="listening1" name="listening" value="1">
                                    <label class="custom-control-label" for="listening1">ได้ยินชัดเจน</label>
                                </div>


                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['listening'] == '2') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="listening2" name="listening" value="2">
                                    <label class="custom-control-label" for="listening2">หูหนวก</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php if ($row['listening'] == '3') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="listening3" name="listening" value="3">
                                    <label class="custom-control-label" for="listening3">ได้ยินไม่ชัด : ใช้อุปกรณ์ช่วยฟัง</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['hearing_aids'] == '1') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="hearing_aids1" name="hearing_aids" value="1">
                                    <label class="custom-control-label" for="hearing_aids1">มี</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['hearing_aids'] == '2') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="hearing_aids2" name="hearing_aids" value="2">
                                    <label class="custom-control-label" for="hearing_aids2">ไม่มี</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['listening'] == '4') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="listening4" name="listening" value="4">
                                    <label class="custom-control-label" for="listening4">ประเมินไม่ได้</label>
                                </div>


                            </div>

                            <br>

                            <div class="form-group row alert alert-dark text-left">
                                <B>5.ด้านการทำกิจกรรม (Activity)</B>
                            </div>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>มีประวัติโรคที่ส่งผลต่อการทำกิจกรรม</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['history_affects_activities'] == 'ไม่มี') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="history_affects_activities1" name="history_affects_activities" value="ไม่มี" onchange="custom_check('off_history_affects_activities');">
                                    <label class="custom-control-label" for="history_affects_activities1">ไม่มี</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['history_affects_activities'] != 'ไม่มี' && $row['history_affects_activities'] != NULL) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="history_affects_activities2" onchange="custom_check('on_history_affects_activities');">
                                    <label class="custom-control-label" for="history_affects_activities2">มี ระบุ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="history_affects_activities_text" name="history_affects_activities" value="<?php if ($row['history_affects_activities'] != 'ไม่มี' && $row['history_affects_activities'] != NULL) {
                                                                                                                                                                                echo htmlspecialchars($row['history_affects_activities']);
                                                                                                                                                                            } ?>" <?php if (!($row['history_affects_activities'] != 'ไม่มี' && $row['history_affects_activities'] != NULL)) {
                                                                                                                                            echo 'disabled';
                                                                                                                                        } ?>>
                                </div>




                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>การทำกิจวัตรประจำวัน</label>
                                <div class="custom-control custom-radio col-sm-2">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['daily_activities'] == '1') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="daily_activities1" name="daily_activities" value="1">
                                    <label class="custom-control-label" for="daily_activities1">ช่วยเหลือตัวเองได้ดี</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['daily_activities'] == '2') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="daily_activities2" name="daily_activities" value="2">
                                    <label class="custom-control-label" for="daily_activities2">Bed ridden</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['daily_activities'] == '3') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="daily_activities3" name="daily_activities" value="3">
                                    <label class="custom-control-label" for="daily_activities3">หอบ เหนื่อย</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php if ($row['daily_activities'] == '4') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="daily_activities4" name="daily_activities" value="4">
                                    <label class="custom-control-label" for="daily_activities4">ถูกจำกัดกิจกรรมบนเตียง</label>
                                </div>

                            </div>
                            <br>
                            <div class="row">


                                &nbsp;&nbsp;&nbsp;&nbsp;<label></label>

                                <div class="custom-control custom-checkbox col-sm-2">
                                    <input type="radio" <?php if ($row['fracture'] != 'มี' && $row['fracture'] != NULL) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="fracture1" onchange="custom_check('on_fracture');">
                                    <label class="custom-control-label" for="fracture1">มี Fracture ตำแหน่ง</label>
                                </div>

                                <div class="col-sm-4">
                                    <input type="text" class="form-control form-control-sm" id="fracture_text" name="fracture" value="<?php if ($row['fracture'] != 'มี' && $row['fracture'] != NULL) {
                                                                                                                                            echo htmlspecialchars($row['fracture']);
                                                                                                                                        } ?>" <?php if (!($row['fracture'] != 'มี' && $row['fracture'] != NULL)) {
                                                                                                                                            echo 'disabled';
                                                                                                                                        } ?>>
                                </div>

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>Braden score</label>
                                <div class="col-sm-2">
                                    <input type="number" class="form-control form-control-sm CheckPer_2" placeholder="เฉพาะตัวเลข" name="braden_score" id="braden_score" value="<?= (isset($row['braden_score']) ? htmlspecialchars($row['braden_score']) : '') ?>" min="0" max="23">
                                </div>/23 คะแนน


                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>Mortor power</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="mortor_power" id="mortor_power" value="<?= (isset($row['mortor_power']) ? htmlspecialchars($row['mortor_power']) : '') ?>">
                                </div>MASS<div class="col-sm-2">
                                    <input type="number" class="form-control form-control-sm CheckPer_2" placeholder="เฉพาะตัวเลข" name="mass" id="mass" value="<?= (isset($row['mass']) ? htmlspecialchars($row['mass']) : '') ?>" min="0" max="6">
                                </div> /6 คะแนน

                            </div>

                            <br>

                            <div class="form-group row alert alert-dark text-left">
                                <B>6.ด้านการกระตุ้น (Stimulation)</B>
                            </div>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>มีประวัติโรคที่ส่งผลต่อการกระตุ้น</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['history_affects_stimulation'] == 'ไม่มี') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="history_affects_stimulation1" name="history_affects_stimulation" value="ไม่มี" onchange="custom_check('off_history_affects_stimulation');">
                                    <label class="custom-control-label" for="history_affects_stimulation1">ไม่มี</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['history_affects_stimulation'] != 'ไม่มี' && $row['history_affects_stimulation'] != NULL) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="history_affects_stimulation2" onchange="custom_check('on_history_affects_stimulation');">
                                    <label class="custom-control-label" for="history_affects_stimulation2">มี ระบุ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="history_affects_stimulation_text" name="history_affects_stimulation" value="<?php if ($row['history_affects_stimulation'] != 'ไม่มี' && $row['history_affects_stimulation'] != NULL) {
                                                                                                                                                                                echo htmlspecialchars($row['history_affects_stimulation']);
                                                                                                                                                                            } ?>" <?php if (!($row['history_affects_stimulation'] != 'ไม่มี' && $row['history_affects_stimulation'] != NULL)) {
                                                                                                                                            echo 'disabled';
                                                                                                                                        } ?>>
                                </div>
                            </div>
                            <br>


                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>GCS: E</label>
                                <div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="gcs_e" id="gcs_e" value="<?= (isset($row['gcs_e']) ? htmlspecialchars($row['gcs_e']) : '') ?>">
                                </div>V<div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="gcs_v" id="gcs_v" value="<?= (isset($row['gcs_v']) ? htmlspecialchars($row['gcs_v']) : '') ?>">
                                </div>M<div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="gcs_m" id="gcs_m" value="<?= (isset($row['gcs_m']) ? htmlspecialchars($row['gcs_m']) : '') ?>">
                                </div>Pupil<div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="pupil" id="pupil" value="<?= (isset($row['pupil']) ? htmlspecialchars($row['pupil']) : '') ?>">
                                </div>RE<div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="pupil_rt" id="pupil_rt" value="<?= (isset($row['pupil_rt']) ? htmlspecialchars($row['pupil_rt']) : '') ?>">
                                </div>mm.&nbsp;LE<div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="pupil_lt" id="pupil_lt" value="<?= (isset($row['pupil_lt']) ? htmlspecialchars($row['pupil_lt']) : '') ?>">
                                </div>mm.

                            </div>

                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ระดับความรู้สึกตัว</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['level_of_consciousness'] == '1') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="level_of_consciousness1" name="level_of_consciousness" value="1">
                                    <label class="custom-control-label" for="level_of_consciousness1">Alert</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['level_of_consciousness'] == '2') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="level_of_consciousness2" name="level_of_consciousness" value="2">
                                    <label class="custom-control-label" for="level_of_consciousness2">Confuse</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['level_of_consciousness'] == '3') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="level_of_consciousness3" name="level_of_consciousness" value="3">
                                    <label class="custom-control-label" for="level_of_consciousness3">Drowsiness</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['level_of_consciousness'] == '4') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="level_of_consciousness4" name="level_of_consciousness" value="4">
                                    <label class="custom-control-label" for="level_of_consciousness4">Stupors</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['level_of_consciousness'] == '5') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="level_of_consciousness5" name="level_of_consciousness" value="5">
                                    <label class="custom-control-label" for="level_of_consciousness5">Coma</label>
                                </div>

                            </div>
                            <br>

                            <div class="form-group row">
                                <label class="col-sm-12"><B> ผล CT-Brain</B></label>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <textarea class="form-control" id="ct_brain" name="ct_brain" rows="2"><?= (isset($row['ct_brain']) ? htmlspecialchars($row['ct_brain']) : '') ?></textarea>
                                </div>
                            </div>
                            <br>

                            <div class="row">
                                <div class="custom-control custom-checkbox">
                                    <input type="radio" class="custom-control-input" id="pain_score0" name="pain_score" value="" onchange="custom_check('on_pain_score_reset');">
                                    <label class="custom-control-label" for="pain_score0">reset</label>

                                </div>

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>Pain score:</label>
                                <div class="custom-control custom-radio">
                                    &nbsp;&nbsp;&nbsp;&nbsp; <input type="radio" <?php if ($row['pain_score'] == '1') {
                                                                                        echo 'checked="checked"';
                                                                                    } ?> class="custom-control-input" id="pain_score1" name="pain_score" value="1" onchange="custom_check('off_on_copt');">
                                    <label class="custom-control-label" for="pain_score1">COPT</label>

                                </div>
                                <div class="col-sm-1">
                                    <input type="number" class="form-control form-control-sm" placeholder="เฉพาะตัวเลข" id="copt_text" name="copt" value="<?php if (($row['pain_score'] == '1')) {
                                                                                                                                                                echo htmlspecialchars($row['copt']);
                                                                                                                                                            } ?>" <?php if (($row['pain_score'] == '2' || $row['pain_score'] == '')) {
                                                                                                                                                                                echo 'disabled';
                                                                                                                                                                            } ?>>

                                </div>/8 คะแนน
                                <div class="custom-control custom-radio">
                                    &nbsp;&nbsp;&nbsp;&nbsp; <input type="radio" <?php if ($row['pain_score'] == '2') {
                                                                                        echo 'checked="checked"';
                                                                                    } ?> class="custom-control-input" id="pain_score2" name="pain_score" value="2" onchange="custom_check('off_on_nrs');">
                                    <label class="custom-control-label" for="pain_score2">NRS</label>

                                </div>
                                <div class="col-sm-1">
                                    <input type="number" class="form-control form-control-sm" placeholder="เฉพาะตัวเลข" id="nrs_text" name="nrs" value="<?php if (($row['pain_score'] == '2')) {
                                                                                                                                                            echo htmlspecialchars($row['nrs']);
                                                                                                                                                        } ?>" <?php if (($row['pain_score'] == '1' || $row['pain_score'] == '')) {
                                                                                                                                                                                echo 'disabled';
                                                                                                                                                                            } ?>>

                                </div>/10 คะแนน



                            </div>

                            <br>

                            <div class="form-group row alert alert-dark text-left">
                                <B>สรุปปัญหา</B>
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-1"></div>

                                <div class="custom-control custom-checkbox col-sm-2">
                                    <input type="checkbox" <?php if ($row['fluid_balance'] == 'Y') {
                                                                echo 'checked="checked"';
                                                            } ?> class="custom-control-input" id="fluid_balance" value="Y" name="fluid_balance">
                                    <label class="custom-control-label" for="fluid_balance">ด้านสมดุลของสารน้ำ(Fluid balance)</label>
                                </div>

                                <div class="custom-control custom-checkbox col-sm-2">
                                    <input type="checkbox" <?php if ($row['aeration'] == 'Y') {
                                                                echo 'checked="checked"';
                                                            } ?>class="custom-control-input" id="aeration" value="Y" name="aeration">
                                    <label class="custom-control-label" for="aeration">ด้านการหายใจ(Aeration)</label>
                                </div>

                                <div class="custom-control custom-checkbox col-sm-2">
                                    <input type="checkbox" <?php if ($row['nutrition'] == 'Y') {
                                                                echo 'checked="checked"';
                                                            } ?>class="custom-control-input" id="nutrition" value="Y" name="nutrition">
                                    <label class="custom-control-label" for="nutrition">ด้านภาวะโภชนาการ(Nutrition)</label>
                                </div>



                            </div>

                            <div class="form-group row">

                                <div class="col-sm-1"></div>

                                <div class="custom-control custom-checkbox col-sm-2">
                                    <input type="checkbox" <?php if ($row['communication_problem'] == 'Y') {
                                                                echo 'checked="checked"';
                                                            } ?>class="custom-control-input" id="communication_problem" value="Y" name="communication_problem">
                                    <label class="custom-control-label" for="communication_problem">ด้านการติดต่อสื่อสาร(Communication)</label>
                                </div>

                                <div class="custom-control custom-checkbox col-sm-2">
                                    <input type="checkbox" <?php if ($row['activity'] == 'Y') {
                                                                echo 'checked="checked"';
                                                            } ?>class="custom-control-input" id="activity" value="Y" name="activity">
                                    <label class="custom-control-label" for="activity">ด้านการทำกิจจกรรม(Activity)</label>
                                </div>

                                <div class="custom-control custom-checkbox col-sm-2">
                                    <input type="checkbox" <?php if ($row['stimulation'] == 'Y') {
                                                                echo 'checked="checked"';
                                                            } ?>class="custom-control-input" id="stimulation" value="Y" name="stimulation">
                                    <label class="custom-control-label" for="stimulation">ด้านการกระตุ้น(Stimulation)</label>
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
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="7%">&nbsp;การแสดงออกทางสีหน้า</td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;ผ่อนคลาย หน้าเรียบเฉย
                                                <br>&nbsp;หน้านิ่วคิ้วขมวด ตึงเครียด
                                                <br>&nbsp;หน้านิ่วคิ้วขมวด บึ้งตึงมาก เปลือกตาปิดแน่น
                                            </td>
                                            <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%">&nbsp;0
                                                <br>1
                                                <br>2
                                            </td>

                                            <!-- 1-->
                                        <tr style="border:1px solid #000;margin: 45px;">
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="7%">&nbsp;การเคลื่อนไหวของร่างกาย</td>
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
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="7%">&nbsp;การเกร็งของกล้ามเนื้อ(ประเมินจากการเหยียดและการงอแขน)</td>
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
                                                (มี Tube) หรือการเปล่งเสียง สำหรับผู้ป่วยที่ไม่ได้ใส่ท่อช่วยหายใจ
                                            </td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;หายใจสอดคล้องกับเครื่องช่วยหายใจ
                                                <br>&nbsp;มีอาการไอ แต่สามารถหายใจขณะที่ใช้ เครื่องช่วยหายใจได้
                                                <br>&nbsp;มีการต้านเครื่องช่วยหายใจ
                                            </td>
                                            <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%">&nbsp;0
                                                <br>1
                                                <br>2
                                            </td>

                                            <tr style="border:1px solid #000;margin: 45px;">
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="7%">&nbsp;การเปล่งเสียง
                                                (ไม่ได้มี Tube)
                                            </td>
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;พูดด้วยน้ำเสียงปกติ
                                                <br>&nbsp;ถอนหายใจ ร้องคราง
                                                <br>&nbsp;ร้องไห้ สะอื้น
                                            </td>
                                            <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%">&nbsp;0
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
                                    <div style="text-align:left; padding:5px;">( ) 2. ผู้ป่วยได้รับอาหาร < ที่เคยได้(>7 วัน)</div>
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
                                            <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="3%">&nbsp;1.3
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
                            <div id="show_check_save"></div>
                            <input type="hidden" id="an" name="an" value="<?= htmlspecialchars($an) ?>">
                            <input type="hidden" id="hn" name="hn" value="<?= htmlspecialchars($hn) ?>">
                            <input type="hidden" id="version" name="version" value="<?= htmlspecialchars($row['version']) ?>">
                            <input type="hidden" id="id" name="id" value="<?= htmlspecialchars($row['id']) ?>">
                            <input type="hidden" id="create_user" name="create_user" value="<?= htmlspecialchars($_SESSION['name']) ?>">

                            <div class="col-md-12 text-right">
                            <?php
                                    if((
                                        Session::checkPermission('PRS_FORM_ICU1','ADD')
                                    ) && (ReportQueryUtils::checkReadOnly($an))) { ?>
                                    <button type="button" class="btn btn-primary" id="btn_lr_report1" onclick="form_save()"><i class="fas fa-save"></i> บันทึก</button>
                                <?php } ?>
                                <?php
                                    if($id != '') { ?>
                                <a href="prs-icu1-pdf.php?an=<?php echo $an; ?>&id=<?php echo $ids; ?>&loginname=<?php echo $loginname; ?>" target="_blank" class="btn btn-secondary"><i class="fas fa-file-pdf"></i> Print <U>PDF</U> File</a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                                    </div>
                    <br>

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

                            }else if (value == "on_speaking_reset") {
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
                            }else if (value == "on_communication_reset") {
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