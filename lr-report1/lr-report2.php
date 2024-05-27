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


Session::insertSystemAccessLog(json_encode(array(
    'form' => 'LR-REPORT2-FORM',
    'an' => $an,
), JSON_UNESCAPED_UNICODE));



/*$login = empty($_REQUEST['loginname']) ? null : $_REQUEST['loginname'];
$loginname = $_SESSION['loginname'];
$values = ['loginname' => $loginname];
if ($login != $loginname) {
    session_start();
    session_destroy();
}*/

// echo $an;




//----------------------เช็คว่า an นี้ มีข้อมูลหรือไม่
$sql = "SELECT *
                FROM `prs_lr_report2`
                WHERE an = :an";
$id  = null;
$parameters['an'] = $an;
$stmt = $conn->prepare($sql);
$stmt->execute($parameters);
if ($row  = $stmt->fetch()) {
    $id = $row['id'];
} else {
    $id = null;
}


$educate = "SELECT * FROM prs_education";
$stmt0 = $conn->prepare($educate);
$stmt0->execute();
$rs = $stmt0->fetchAll();



/*
$rs = [
    ['id' => 1, 'education' => 'Manager'],
    ['id' => 2, 'education' => 'Developer'],
    ['id' => 3, 'education' => 'Designer']
];

// Function to get the position name by p_id
function getPositionName($id, $rs) {
    foreach ($rs as $row0) {
        if ($row0['id'] == $id) {
            return $row0['education'];
        }
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $education= getPositionName($id, $rs);

    if ($education !== null) {
        echo "Selected Position: " . htmlspecialchars($education);
    } else {
        echo "Invalid position selected.";
    }
}
*/

/*$sql = "SELECT count(*) AS count_row, id FROM " . DbConstant::KPHIS_DBNAME . ".prs_lr_report2 WHERE an = :an ";
$id  = null;
$parameters['an'] = $an;
$stmt = $conn->prepare($sql);
$stmt->execute($parameters);
$row = $stmt->fetch();
if ($row['count_row'] > 0) {
    $id = $row['id'];
}
*/
/*
$sql = "SELECT *
                FROM `prs_lr_report2`
                WHERE an = :an";
$stmt = $conn->prepare($sql);
$stmt->execute($parameters);
if ($row  = $stmt->fetch()) {
    $id = $row['id'];
} else {
    $id = null;
}
*/
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




<form id="my_form" method="post">
    <div class="container-fluid">
        <div class="row">
            <div class="col-auto">
                <button type="button" class="btn btn-sm btn-primary btn-block" onclick="window.close()"><i class="fas fa-arrow-left"></i> กลับ</button>
            </div>
            <div class="col-auto p-1 font-weight-bold">
                ใบบันทึกประวัติและประเมินสมรรถนะผู้ป่วยแรกรับ (เฉพาะผู้มาคลอด) <?= htmlspecialchars(DbConstant::HOSPITAL_NAME) ?>
                <B>
                    <font color="red"> (รอคุยรายละเอียดเพื่อออกแบบการเก็บข้อมูล) </font>
                </B>
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
                                <label>เวลา</label>
                                <div class="col-sm-2">
                                    <input type="time" class="form-control form-control-sm" id="rxtime" name="rxtime" value="<?= (isset($row['rxtime']) ? htmlspecialchars($row['rxtime']) : '') ?>">
                                </div>

                                <label>กรณี admit จากผู้ป่วยนอก ถึงห้องคลอดเวลา</label>
                                <div class="col-sm-2">
                                    <input type="time" class="form-control form-control-sm" id="intime" name="intime" value="<?= (isset($row['intime']) ? htmlspecialchars($row['intime']) : '') ?>">
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
                            <!--
                            <div class="form-group row">
                                <label class="col-sm-12"><B> HPI </B></label>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <textarea class="form-control" id="hpi" name="hpi" rows="4"><?= (isset($row_opdscreen['hpi']) && $id == null ? htmlspecialchars($row_opdscreen['hpi']) : htmlspecialchars($row['hpi'])) ?></textarea>
                                </div>
                            </div>
-->
                            <div class="form-group row">
                                <label class="col-sm-12"><B> ประวัติการเจ็บป่วยปัจจุบัน </B></label>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <textarea class="form-control" id="current_illness" name="current_illness" rows="4"><?= (isset($row['current_illness']) ? htmlspecialchars($row['current_illness']) : '') ?></textarea>
                                </div>
                            </div>


                            <div class="form-group row">
                                <label class="col-sm-12"><B> ประวัติการเจ็บป่วยในอดีต </B></label>
                            </div>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>โรคประจำตัว</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if (
                                                                                    $row['c_chronic'] == 'ปฏิเสธ'
                                                                                    /*|| $row['depart'] == NULL*/
                                                                                ) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="c_chronic1" name="c_chronic" value="ปฏิเสธ" onchange="custom_check('off_c_chronic');">
                                    <label class="custom-control-label" for="c_chronic1">ปฏิเสธ</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if (
                                                                                    $row['c_chronic'] != 'ปฏิเสธ'
                                                                                    && $row['c_chronic'] != NULL
                                                                                ) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="c_chronic2" name="c_chronic" value="มี" onchange="custom_check('on_c_chronic');">
                                    <label class="custom-control-label" for="c_chronic2">มี ระบุ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="c_chronic_text" name="c_chronic" value="<?php if ($row['c_chronic'] != 'ปฏิเสธ' && $row['c_chronic'] != NULL) {
                                                                                                                                            echo htmlspecialchars($row['c_chronic']);
                                                                                                                                        } ?>" <?php if (!($row['c_chronic'] != 'ปฏิเสธ' && $row['c_chronic'] != NULL)) {
                                                                                                                                                    echo 'disabled';
                                                                                                                                                } ?>>
                                </div>


                            </div>
                            <br>
                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>เคยรับการรักษาในโรงพยาบาล ภายใน 1 ปี</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <input type="radio" <?php if (
                                                                                                        $row['hos_history'] == 'ปฏิเสธ'

                                                                                                    ) {
                                                                                                        echo 'checked="checked"';
                                                                                                    } ?> class="custom-control-input" id="hos_history1" name="hos_history" value="ปฏิเสธ" onchange="custom_check('off_hos_history');">
                                    <label class="custom-control-label" for="hos_history1">ปฏิเสธ</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if (
                                                                                    $row['hos_history'] != 'ปฏิเสธ'
                                                                                    && $row['hos_history'] != NULL
                                                                                ) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="hos_history2" name="hos_history" value="เคย" onchange="custom_check('on_hos_history');">
                                    <label class="custom-control-label" for="hos_history2">เคย ระบุ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="hos_history_text" name="hos_history" value="<?php if ($row['hos_history'] != 'ปฏิเสธ' && $row['hos_history'] != NULL) {
                                                                                                                                                echo htmlspecialchars($row['hos_history']);
                                                                                                                                            } ?>" <?php if (!($row['hos_history'] != 'ปฏิเสธ' && $row['hos_history'] != NULL)) {
                                                                                                                                                        echo 'disabled';
                                                                                                                                                    } ?>>
                                </div>


                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ประวัติการผ่าตัด</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <input type="radio" <?php if (
                                                                                                        $row['h_sergery'] == 'ปฏิเสธ'

                                                                                                    ) {
                                                                                                        echo 'checked="checked"';
                                                                                                    } ?> class="custom-control-input" id="h_sergery1" name="h_sergery" value="ปฏิเสธ" onchange="custom_check('off_h_sergery');">
                                    <label class="custom-control-label" for="h_sergery1">ปฏิเสธ</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['h_sergery'] != 'ปฏิเสธ' && $row['h_sergery'] != NULL) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="h_sergery2" name="h_sergery" value="เคย" onchange="custom_check('on_h_sergery');">
                                    <label class="custom-control-label" for="h_sergery2">เคย ระบุ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="h_sergery_text" name="h_sergery" value="<?php if ($row['h_sergery'] != 'ปฏิเสธ' && $row['h_sergery'] != NULL) {
                                                                                                                                            echo htmlspecialchars($row['h_sergery']);
                                                                                                                                        } ?>" <?php if (!($row['h_sergery'] != 'ปฏิเสธ' && $row['h_sergery'] != NULL)) {
                                                                                                                                                    echo 'disabled';
                                                                                                                                                } ?>>
                                </div>


                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ประวัติการแพ้ยาหรือการแพ้อื่นๆ</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <input type="radio" <?php if (
                                                                                                        $row['h_allergy'] == 'ปฏิเสธ'

                                                                                                    ) {
                                                                                                        echo 'checked="checked"';
                                                                                                    } ?> class="custom-control-input" id="h_allergy1" name="h_allergy" value="ปฏิเสธ" onchange="custom_check('off_h_allergy');">
                                    <label class="custom-control-label" for="h_allergy1">ปฏิเสธ</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['h_allergy'] != 'ปฏิเสธ' && $row['h_allergy'] != NULL) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="h_allergy2" name="h_allergy" value="มี" onchange="custom_check('on_h_allergy');">
                                    <label class="custom-control-label" for="h_allergy2">มี ระบุ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="h_allergy_text" name="h_allergy" value="<?php if ($row['h_allergy'] != 'ปฏิเสธ' && $row['h_allergy'] != NULL) {
                                                                                                                                            echo htmlspecialchars($row['h_allergy']);
                                                                                                                                        } ?>" <?php if (!($row['h_allergy'] != 'ปฏิเสธ' && $row['h_allergy'] != NULL)) {
                                                                                                                                                    echo 'disabled';
                                                                                                                                                } ?>>
                                </div>


                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ประวัติการใช้ยาและผลิตภัณฑ์สุขภาพ</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <input type="radio" <?php if (
                                                                                                        $row['history_of_drug'] == 'ปฏิเสธ'

                                                                                                    ) {
                                                                                                        echo 'checked="checked"';
                                                                                                    } ?> class="custom-control-input" id="history_of_drug1" name="history_of_drug" value="ปฏิเสธ" onchange="custom_check('off_history_of_drug');">
                                    <label class="custom-control-label" for="history_of_drug1">ปฏิเสธ</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['history_of_drug'] != 'ปฏิเสธ' && $row['history_of_drug'] != NULL) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="history_of_drug2" name="history_of_drug" value="มี" onchange="custom_check('on_history_of_drug');">
                                    <label class="custom-control-label" for="history_of_drug2">มี ระบุ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="history_of_drug_text" name="history_of_drug" value="<?php if ($row['history_of_drug'] != 'ปฏิเสธ' && $row['history_of_drug'] != NULL) {
                                                                                                                                                        echo htmlspecialchars($row['history_of_drug']);
                                                                                                                                                    } ?>" <?php if (!($row['history_of_drug'] != 'ปฏิเสธ' && $row['history_of_drug'] != NULL)) {
                                                                                                                                                                echo 'disabled';
                                                                                                                                                            } ?>>
                                </div>


                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ประวัติการเจ็บป่วยในครอบครัว</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <input type="radio" <?php if (
                                                                                                        $row['pmh2'] == 'ปฏิเสธ'

                                                                                                    ) {
                                                                                                        echo 'checked="checked"';
                                                                                                    } ?> class="custom-control-input" id="pmh21" name="pmh2" value="ปฏิเสธ" onchange="custom_check('off_pmh2');">
                                    <label class="custom-control-label" for="pmh21">ปฏิเสธ</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['pmh2'] != 'ปฏิเสธ' && $row['pmh2'] != NULL) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="pmh22" name="pmh2" value="มี" onchange="custom_check('on_pmh2');">
                                    <label class="custom-control-label" for="pmh22">มี ระบุ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="pmh2_text" name="pmh2" value="<?php if ($row['pmh2'] != 'ปฏิเสธ' && $row['pmh2'] != NULL) {
                                                                                                                                    echo htmlspecialchars($row['pmh2']);
                                                                                                                                } ?>" <?php if (!($row['pmh2'] != 'ปฏิเสธ' && $row['pmh2'] != NULL)) {
                                                                                                                                            echo 'disabled';
                                                                                                                                        } ?>>
                                </div>


                            </div>
                            <br>

                            <!--     <div class="form-group row">
                                <label class="col-sm-12"><B> ประวัติการเจ็บป่วยของสมาชิกในครอบครัว</B></label>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <textarea class="form-control" id="family" name="family" rows="3"><?= (isset($row_opdscreen['fh']) && $id == null ? htmlspecialchars($row_opdscreen['fh']) : htmlspecialchars($row['family'])) ?></textarea>
                                </div>
                            </div> -->


                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ประวัติการตั้งครรภ์&nbsp; G&nbsp;</label>
                                <div>
                                    <input type="number" class="form-control form-control-sm CheckPer_2" placeholder="G" name="g" id="g" value="<?= (isset($row['g']) ? htmlspecialchars($row['g']) : '') ?>" min="0">
                                </div>&nbsp; P&nbsp;<div>
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="P" name="p" id="p" value="<?= (isset($row['p']) ? htmlspecialchars($row['p']) : '') ?>">
                                </div> &nbsp;<label>GA</label>
                                <div class="col-md-1"><input type="number" class="form-control form-control-sm CheckPer_2" placeholder="เฉพาะตัวเลข" name="l_ga" id="l_ga" value="<?= (isset($row['l_ga']) ? htmlspecialchars($row['l_ga']) : '') ?>" min="0"> </div>
                                <label>wks by&nbsp;</label>
                               <div class="col-md-1">
                                    <select class="form-control form-control-sm CheckPer_2" id="l_ga_by" name="l_ga_by" value="">
                                        <option value="">- เลือก -</option>

                                        <option value="V/S" <?php if ($row['l_ga_by'] == 'V/S') echo ' selected="selected"'; ?>>V/S</option>
                                        <option value="LMP" <?php if ($row['l_ga_by'] == 'LMP') echo ' selected="selected"'; ?>>LMP</option>
                                        <option value="SIZE" <?php if ($row['l_ga_by'] == 'SIZE') echo ' selected="selected"'; ?>>SIZE</option>
                                        
                                    </select> 


                                </div> 

                             <!--   <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="กำหนดจาก" name="l_ga_by" id="l_ga_by" value="<?= (isset($row['l_ga_by']) ? htmlspecialchars($row['l_ga_by']) : '') ?>"></div>
                                                                                                                                    -->                                                                                                  
                                <label>ฝากครรภ์ครั้งแรก</label>
                                <div class="col-md-1"><input type="number" class="form-control form-control-sm CheckPer_2" placeholder="เฉพาะตัวเลข" name="prenatal_wks" id="prenatal_wks" value="<?= (isset($row['prenatal_wks']) ? htmlspecialchars($row['prenatal_wks']) : '') ?>" min="0"> </div>
                                <label>wks&nbsp;ฝากครรภ์</label>
                                <div class="col-md-1"><input type="number" class="form-control form-control-sm CheckPer_2" placeholder="เฉพาะตัวเลข" name="prenatral_count" id="prenatral_count" value="<?= (isset($row['prenatral_count']) ? htmlspecialchars($row['prenatral_count']) : '') ?>" min="0"> </div>
                                <label>ครั้ง</label>

                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label></label>
                                <div>

                                </div> &nbsp;<label>ค 8</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="ค8" name="k8" id="k8" value="<?= (isset($row['k8']) ? htmlspecialchars($row['k8']) : '') ?>"></div>
                                <label>( ขาด&nbsp;</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="ขาด" name="k8_less" id="k8_less" value="<?= (isset($row['k8_less']) ? htmlspecialchars($row['k8_less']) : '') ?>"></div>
                                <label>) ที่</label>
                                <div class="col-md-2"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="at_" id="at_" value="<?= (isset($row['at_']) ? htmlspecialchars($row['at_']) : '') ?>"></div>
                                <label>dt</label>
                                <div class="col-md-1">
                                    <select class="form-control form-control-sm CheckPer_2" id="dt" name="dt" value="">
                                        <option value="">- เลือก -</option>

                                        <option value="1" <?php if ($row['dt'] == '1') echo ' selected="selected"'; ?>>1</option>
                                        <option value="2" <?php if ($row['dt'] == '2') echo ' selected="selected"'; ?>>2</option>
                                        <option value="3" <?php if ($row['dt'] == '3') echo ' selected="selected"'; ?>>3</option>
                                        <option value="คุ้มครอง" <?php if ($row['dt'] == 'คุ้มครอง') echo ' selected="selected"'; ?>>คุ้มครอง</option>
                                        
                                    </select> 
                                </div>
                                <label>เข็ม</label>

                 <!--               <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="dt" id="dt" value="<?= (isset($row['dt']) ? htmlspecialchars($row['dt']) : '') ?>"></div>
                                <label>เข็ม</label>
                                <div class="col-md-1"><input type="number" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="dt_needle" id="dt_needle" value="<?= (isset($row['dt_needle']) ? htmlspecialchars($row['dt_needle']) : '') ?>"></div>
                                                                                                                                    -->
                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label></label>
                                <div>

                                </div> &nbsp;<label>Lab ANC ครั้งที่1 Anti HIV</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="anc_lab_hiv1" id="anc_lab_hiv1" value="<?= (isset($row['anc_lab_hiv1']) ? htmlspecialchars($row['anc_lab_hiv1']) : '') ?>"></div>
                                <label>RPR</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="anc_lab_rpr1" id="anc_lab_rpr1" value="<?= (isset($row['anc_lab_rpr1']) ? htmlspecialchars($row['anc_lab_rpr1']) : '') ?>"></div>
                                <label>HBsAg</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="anc_lab_hbsag1" id="anc_lab_hbsag1" value="<?= (isset($row['anc_lab_hbsag1']) ? htmlspecialchars($row['anc_lab_hbsag1']) : '') ?>"></div>
                                <label>Hct</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="anc_lab_hct1" id="anc_lab_hct1" value="<?= (isset($row['anc_lab_hct1']) ? htmlspecialchars($row['anc_lab_hct1']) : '') ?>"></div>
                                <label>% Hb</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="anc_lab_hb1" id="anc_lab_hb1" value="<?= (isset($row['anc_lab_hb1']) ? htmlspecialchars($row['anc_lab_hb1']) : '') ?>"></div>
                                <label>Bl.gr</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="anc_lab_blgr" id="anc_lab_blgr" value="<?= (isset($row['anc_lab_blgr']) ? htmlspecialchars($row['anc_lab_blgr']) : '') ?>"></div>
                                <label>Rh</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="anc_lab_blgr_rh" id="anc_lab_blgr_rh" value="<?= (isset($row['anc_lab_blgr_rh']) ? htmlspecialchars($row['anc_lab_blgr_rh']) : '') ?>"></div>

                            </div>

                            <br>
                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;<label></label>
                                <div>

                                </div> &nbsp;<label>DCIP</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="anc_lab_dcip1" id="anc_lab_dcip1" value="<?= (isset($row['anc_lab_dcip1']) ? htmlspecialchars($row['anc_lab_dcip1']) : '') ?>"></div>
                                <label>MCV</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="anc_lab_mvc1" id="anc_lab_mvc1" value="<?= (isset($row['anc_lab_mvc1']) ? htmlspecialchars($row['anc_lab_mvc1']) : '') ?>"></div>
                                <label>Hb typing</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="anc_lab_hb_typing1" id="anc_lab_hb_typing1" value="<?= (isset($row['anc_lab_hb_typing1']) ? htmlspecialchars($row['anc_lab_hb_typing1']) : '') ?>"></div>
                            </div>
                            <br>




                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label></label>
                                <div>

                                </div> &nbsp;<label>Lab ANC ครั้งที่2 Anti HIV</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="anc_lab_hiv2" id="anc_lab_hiv2" value="<?= (isset($row['anc_lab_hiv2']) ? htmlspecialchars($row['anc_lab_hiv2']) : '') ?>"></div>
                                <label>RPR</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="anc_lab_rpr2" id="anc_lab_rpr2" value="<?= (isset($row['anc_lab_rpr2']) ? htmlspecialchars($row['anc_lab_rpr2']) : '') ?>"></div>
                                <label>Hct</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="anc_lab_hct2" id="anc_lab_hct2" value="<?= (isset($row['anc_lab_hct2']) ? htmlspecialchars($row['anc_lab_hct2']) : '') ?>"></div>
                                <label>% Hb สามี Anti HIV</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="hus_lab_hiv" id="hus_lab_hiv" value="<?= (isset($row['hus_lab_hiv']) ? htmlspecialchars($row['hus_lab_hiv']) : '') ?>"></div>
                                <label>RPR</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="lab_rpr2" id="lab_rpr2" value="<?= (isset($row['lab_rpr2']) ? htmlspecialchars($row['lab_rpr2']) : '') ?>"></div>
                                <label>DCIP</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="lab_dcip2" id="lab_dcip2" value="<?= (isset($row['lab_dcip2']) ? htmlspecialchars($row['lab_dcip2']) : '') ?>"></div>
                                <label>Hb typing</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="lab_hb_typing2" id="lab_hb_typing2" value="<?= (isset($row['lab_hb_typing2']) ? htmlspecialchars($row['lab_hb_typing2']) : '') ?>"></div>

                            </div>
                            <br>



                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;<label></label>
                                <div>

                                </div> &nbsp;<label>โรงเรียนพ่อแม่</label>
                                <div class="col-md-1"><input type="number" class="form-control form-control-sm CheckPer_2" placeholder="เฉพาะตัวเลข" name="ma_fa_school" id="ma_fa_school" value="<?= (isset($row['ma_fa_school']) ? htmlspecialchars($row['ma_fa_school']) : '') ?>" min="0"> </div>
                                <label>ครั้ง Quad test</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="quad_test" id="quad_test" value="<?= (isset($row['quad_test']) ? htmlspecialchars($row['quad_test']) : '') ?>"></div>
                                <label>Lab อื่นๆ</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="other_lab" id="other_lab" value="<?= (isset($row['other_lab']) ? htmlspecialchars($row['other_lab']) : '') ?>"></div>
                            </div>
                            <br>

                            <div class="form-group row">
                                <label class="col-sm-12"><B> ประวัติการคลอด</B></label>
                            </div>




                            <div class="form-group row">
                                <div class="col-sm-0"></div>
                                <div class="custom-control custom-checkbox col-sm-1"><label class="text-right">
                                        <a href="#" data-toggle="modal" data-target="#myModal" class="signup-button gray-btn pl-pr-36" data-role="disabled" onclick='add_labor()'>
                                            <i class="fas fa-plus-square"></i></a> ครรภ์ที่</label>
                                </div>
                                <div class="custom-control custom-checkbox col-sm-1"><label class="text-right">วดป คลอด/แท้ง</label></div>
                                <div class="custom-control custom-checkbox col-sm-1"><label class="text-right">GA</label></div>
                                <div class="custom-control custom-checkbox col-sm-1"><label class="text-right">วิธีคลอด/แท้ง</label></div>
                                <div class="custom-control custom-checkbox col-sm-1"><label class="text-right">น้ำหนักทารก</label></div>
                                <div class="custom-control custom-checkbox col-sm-1"><label class="text-right">เพศ</label></div>
                                <div class="custom-control custom-checkbox col-sm-1"><label class="text-right">สถานที่คลอด</label></div>
                                <div class="custom-control custom-checkbox col-sm-2"><label class="text-right">ภาวะแทรกซ้อน</label></div>
                                <div class="custom-control custom-checkbox col-sm-2"><label class="text-right">ประวัติการคลอดติดไหล่/คลอดไหล่ยาก</label></div>

                            </div>
                            <div class="form-group row"><?php $labor_history_pos = explode(" ", $row['labor_history']); ?>
                                <div class="col-sm-0"></div>
                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="number" class="form-control form-control-sm" value="<?= (isset($row['labor_history']) ? htmlspecialchars($labor_history_pos[0]) : '') ?>" id="preg_num1" name="preg_num1">
                                </div>
                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="text" placeholder="วว/ดด/ปปปป" class="form-control form-control-sm" value="<?= (isset($row['labor_history']) ? htmlspecialchars($labor_history_pos[1]) : '') ?>" id="labor_date1" name="labor_date1">
                                </div>
                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="number" class="form-control form-control-sm" value="<?= (isset($row['labor_history']) ? htmlspecialchars($labor_history_pos[2]) : '') ?>" id="ga1" name="ga1">
                                </div>
                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="text" class="form-control form-control-sm" placeholder="ห้ามเกิน 15 ตัวอักษร" value="<?= (isset($row['labor_history']) ? htmlspecialchars($labor_history_pos[3]) : '') ?>" id="labor_by1" name="labor_by1">

                                </div>
                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="number" class="form-control form-control-sm" value="<?= (isset($row['labor_history']) ? htmlspecialchars($labor_history_pos[4]) : '') ?>" id="labor_weight1" name="labor_weight1">
                                </div>
                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="text" class="form-control form-control-sm" value="<?= (isset($row['labor_history']) ? htmlspecialchars($labor_history_pos[5]) : '') ?>" id="l_sex1" name="l_sex1">
                                </div>
                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="text" class="form-control form-control-sm" value="<?= (isset($row['labor_history']) ? htmlspecialchars($labor_history_pos[6]) : '') ?>" id="location1" name="location1">
                                </div>
                                <div class="custom-control custom-checkbox col-sm-2">
                                    <input type="text" class="form-control form-control-sm" value="<?= (isset($row['labor_history']) ? htmlspecialchars($labor_history_pos[7]) : '') ?>" id="complications1" name="complications1">
                                </div>
                                <div class="custom-control custom-checkbox col-sm-2">
                                    <input type="text" class="form-control form-control-sm" value="<?= (isset($row['labor_history']) ? htmlspecialchars($labor_history_pos[8]) : '') ?>" id="l_history1" name="l_history1">
                                </div>

                                <div class="col-sm-1">
                                    <a href="#" data-toggle="modal" data-target="#myModal" class="signup-button gray-btn pl-pr-36" data-role="disabled" onclick='remove()'><i class="fas fa-trash-alt" style="color: Tomato;"></i></a>
                                    <label> </label>
                                </div>
                            </div>

                            <?php $y = 9;
                            $z = 2;
                            for ($x = 1; $x < (count($labor_history_pos) - 1) / 9; $x++) {
                                echo "<div id='labor_row" . $z . "' name='labor_row" . $z . "' class='form-group row'>
                                        <div class='col-sm-0'></div>
                                        <div class='custom-control custom-checkbox col-sm-1'>
                                        <input type='number' class='form-control form-control-sm'
                                                id='preg_num" . $z . "' name='preg_num" . $z . "' placeholder='ระบุตัวเลข' value='" . htmlspecialchars($labor_history_pos[$y++]) . "'>
                                        </div>
                                        <div class='custom-control custom-checkbox col-sm-1'>
                                        <input type='text' class='form-control form-control-sm'
                                                id='labor_date" . $z . "' name='labor_date" . $z . "'value='" . htmlspecialchars($labor_history_pos[$y++]) . "'>
                                        </div>
                                        <div class='custom-control custom-checkbox col-sm-1'>
                                            <input type='number' class='form-control form-control-sm'
                                                id='ga" . $z . "' name='ga" . $z . "' placeholder='ระบุตัวเลข' value='" . htmlspecialchars($labor_history_pos[$y++]) . "'>
                                        </div>
                                        <div class='custom-control custom-checkbox col-sm-1'>
                                        <input type='text' class='form-control form-control-sm' 
                                            id='labor_by" . $z . "' name='labor_by" . $z . "' placeholder='ห้ามเกิน 15 ตัวอักษร' value='" . htmlspecialchars($labor_history_pos[$y++]) . "'>
                                    </div>
                                    <div class='custom-control custom-checkbox col-sm-1'>
                                        <input type='number' class='form-control form-control-sm' 
                                            id='labor_weight" . $z . "' name='labor_weight" . $z . "' placeholder='ระบุตัวเลข' value='" . htmlspecialchars($labor_history_pos[$y++]) . "'>
                                    </div>

                                    <div class='custom-control custom-checkbox col-sm-1'>
                                        <input type='text' class='form-control form-control-sm' 
                                            id='l_sex" . $z . "' name='l_sex" . $z . "' placeholder='' value='" . htmlspecialchars($labor_history_pos[$y++]) . "'>
                                    </div>

                                    <div class='custom-control custom-checkbox col-sm-1'>
                                    <input type='text' class='form-control form-control-sm'
                                        id='location" . $z . "' name='location" . $z . "' value='" . htmlspecialchars($labor_history_pos[$y++]) . "'>
                                </div>

                                <div class='custom-control custom-checkbox col-sm-2'>
                                    <input type='text' class='form-control form-control-sm'
                                        id='complications" . $z . "' name='complications" . $z . "' value='" . htmlspecialchars($labor_history_pos[$y++]) . "'>
                                </div>

                                <div class='custom-control custom-checkbox col-sm-2'>
                                    <input type='text' class='form-control form-control-sm'
                                        id='l_history" . $z . "' name='l_history" . $z . "' value='" . htmlspecialchars($labor_history_pos[$y++]) . "'>
                                </div>

                               
                                        <div class='col-sm-1'>
                                            <a href='#'  data-toggle='modal' data-target='#myModal' class='signup-button gray-btn pl-pr-36' data-role='disabled' onclick='remove_pos(" . $z . ")'><i class='fas fa-trash-alt' style='color: Tomato;'></i></a>
                                            <label> </label>
                                        </div>
                                    </div>";
                                $z++;
                            }
                            ?>
                            <script>
                                /*function add_labor() {
                                    var new_chq_no = parseInt($('#total_chq').val()) + 1;

                                    var new_input = "<div id='labor_row" + new_chq_no + "'class='form-group row'> <div class='col-sm-0'></div><div class='custom-control col-sm-1'><input type='text' class='form-control form-control-sm' id='preg_num" +
                                        new_chq_no + "'name='preg_num" + new_chq_no + "'></div>'"

                                    $('#new_chq').append(new_input);
                                    $('#total_chq').val(new_chq_no);

                                 } */

                                function add_labor() {
                                    var new_chq_no = parseInt($('#total_chq').val()) + 1;
                                    var new_input = "<div id='labor_row" + new_chq_no + "'class='form-group row'> <div class='col-sm-0'></div><div class='custom-control col-sm-1'><input type='number' class='form-control form-control-sm' id='preg_num" +
                                        new_chq_no + "'name='preg_num" + new_chq_no + "'></div><div class='custom-control col-sm-1'><input type='text' class='form-control form-control-sm' id='labor_date" +
                                        new_chq_no + "'name='labor_date" + new_chq_no + "'></div><div class='custom-control col-sm-1'><input type='number' class='form-control form-control-sm' id='ga" +
                                        new_chq_no + "'name='ga" + new_chq_no + "'></div><div class='custom-control col-sm-1'><input type='text' class='form-control form-control-sm' id='labor_by" +
                                        new_chq_no + "'name='labor_by" + new_chq_no + "'></div><div class='custom-control col-sm-1'><input type='number' class='form-control form-control-sm' id='labor_weight" +
                                        new_chq_no + "'name='labor_weight" + new_chq_no + "'></div><div class='custom-control col-sm-1'><input type='text' class='form-control form-control-sm' id='l_sex" +
                                        new_chq_no + "'name='l_sex" + new_chq_no + "'></div><div class='custom-control col-sm-1'><input type='text' class='form-control form-control-sm' id='location" +
                                        new_chq_no + "'name='location" + new_chq_no + "'></div><div class='custom-control col-sm-2'><input type='text' class='form-control form-control-sm' id='complications" +
                                        new_chq_no + "'name='complications" + new_chq_no + "'></div><div class='custom-control col-sm-2'><input type='text' class='form-control form-control-sm' id='l_history" +
                                        new_chq_no + "'name='l_history" +
                                        new_chq_no + "'></div><div class='col-sm-1'><a href='#'  data-toggle='modal' data-target='#myModal' class='signup-button gray-btn pl-pr-36' data-role='disabled' onclick='remove_pos(" +
                                        new_chq_no + ")'><i class='fas fa-trash-alt' style='color: Tomato;'></i></a><label> </label></div></div>";
                                    $('#new_chq').append(new_input);
                                    $('#total_chq').val(new_chq_no);
                                }

                                function remove_pos(last_chq_no) {
                                    $('#labor_row' + last_chq_no).remove();
                                    $('#preg_num' + last_chq_no).remove();
                                    $('#labor_date' + last_chq_no).remove();
                                    $('#ga' + last_chq_no).remove();
                                    $('#labor_by' + last_chq_no).remove();
                                    $('#labor_weight' + last_chq_no).remove();
                                    $('#l_sex' + last_chq_no).remove();
                                    $('#location' + last_chq_no).remove();
                                    $('#complications' + last_chq_no).remove();
                                    $('#l_history' + last_chq_no).remove();
                                }

                                function remove() {
                                    $('#preg_num1').val('');
                                    $('#labor_date1').val('');
                                    $('#ga1').val('');
                                    $('#labor_by1').val('');
                                    $('#labor_weight1').val('');
                                    $('#l_sex1').val('');
                                    $('#location1').val('');
                                    $('#complications1').val('');
                                    $('#l_history1').val('');


                                }
                            </script>
                            <!--   ADD -->
                            <div id="new_chq"></div>
                            <input type="hidden" id="total_chq" value="<?php if ($row['labor_history'] == null) {
                                                                            echo 1;
                                                                        } else {
                                                                            echo (count($labor_history_pos) - 1) / 9;
                                                                        } ?>">
                            <div class="form-group row"><textarea style="display:none;" name="labor_history" id="labor_history" cols="30" rows="10"></textarea></div>
                            <!--   ADD -->


                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;<label></label>
                                <div>

                                </div> &nbsp;<label>สัญญาณชีพ&nbsp;&nbsp;BT</label>
                                <div class="col-md-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="เฉพาะตัวเลข" name="bt" id="bt" value="<?= (isset($row['bt']) ? htmlspecialchars(round(($row['bt']), 2)) : '') ?>">
                                </div>
                                <label>C, PR</label>
                                <div class="col-md-1"><input type="number" class="form-control form-control-sm CheckPer_2" placeholder="เฉพาะตัวเลข" name="pr" id="pr" value="<?= (isset($row['pr']) ? htmlspecialchars(round(($row['pr']), 2)) : '') ?>">
                                </div>
                                <label>bpm, RR</label>
                                <div class="col-md-1">
                                    <input type="number" class="form-control form-control-sm CheckPer_2" placeholder="เฉพาะตัวเลข" name="rr" id="rr" value="<?= (isset($row['rr']) ? htmlspecialchars(round(($row['rr']), 2)) : '') ?>">
                                </div>
                                <label>bpm, BP</label>
                                <div class="col-md-1">
                                    <input type="number" class="form-control form-control-sm CheckPer_2" placeholder="bps" name="bps" id="bps" value="<?= (isset($row['bps']) ? htmlspecialchars(round(($row['bps']), 2)) : '') ?>">

                                </div> /
                                <div class="col-md-1">
                                    <input type="number" class="form-control form-control-sm CheckPer_2" placeholder="bpd" name="bpd" id="bpd" value="<?= (isset($row['bpd']) ? htmlspecialchars(round(($row['bpd']), 2)) : '') ?>">

                                </div>
                                <label>mmHg</label>

                            </div>
                            <br>

                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;<label></label>
                                <div>

                                </div> &nbsp;<label>นอนวันละ</label>
                                <div class="col-md-1"><input type="number" class="form-control form-control-sm CheckPer_2" placeholder="เฉพาะตัวเลข" name="sleep_hour" id="sleep_hour" value="<?= (isset($row['sleep_hour']) ? htmlspecialchars($row['sleep_hour']) : '') ?>" min="0" max="24"> </div>
                                <label>ชม. ปวดบริเวณ</label>
                                <div class="col-md-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxx" name="pain_area" id="pain_area" value="<?= (isset($row['pain_area']) ? htmlspecialchars($row['pain_area']) : '') ?>">
                                </div>
                                <label>Pain score</label>
                                <div class="col-md-1"><input type="number" class="form-control form-control-sm CheckPer_2" placeholder="เฉพาะตัวเลข" name="pain_score" id="pain_score" value="<?= (isset($row['pain_score']) ? htmlspecialchars($row['pain_score']) : '') ?>" min="0" max="10"> </div> <label>/10 คะแนน</label>


                            </div>
                            <br>
                            <!-- education  ocupation  income income_enough -->

                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;<label></label>
                                <div>

                                </div> &nbsp;<label for="education">ระดับการศึกษา</label>
                                <div class="col-md-1">
                                    <select class="form-control form-control-sm CheckPer_2" id="education" name="education" value="">
                                        <option value="">- เลือก -</option>

                                        <option value="1" <?php if ($row['education'] == '1') echo ' selected="selected"'; ?>>ก่อนประถมศึกษา</option>
                                        <option value="2" <?php if ($row['education'] == '2') echo ' selected="selected"'; ?>>ประถมศึกษา</option>
                                        <option value="3" <?php if ($row['education'] == '3') echo ' selected="selected"'; ?>>มัธยมศึกษาตอนต้น</option>
                                        <option value="4" <?php if ($row['education'] == '4') echo ' selected="selected"'; ?>>มัธยมศึกษาตอนปลาย หรือ ปวช.</option>
                                        <option value="5" <?php if ($row['education'] == '5') echo ' selected="selected"'; ?>>อนุปริญญา</option>
                                        <option value="6" <?php if ($row['education'] == '6') echo ' selected="selected"'; ?>>ระดับปริญญาตรี</option>
                                        <option value="7" <?php if ($row['education'] == '7') echo ' selected="selected"'; ?>>ปริญญาโท</option>
                                        <option value="8" <?php if ($row['education'] == '8') echo ' selected="selected"'; ?>>ปริญญาเอก</option>
                                        <option value="9" <?php if ($row['education'] == '9') echo ' selected="selected"'; ?>>ไม่ทราบ</option>
                                        <option value="99" <?php if ($row['education'] == '99') echo ' selected="selected"'; ?>>ไม่ได้รับการศึกษา</option>

                                    </select>


                                </div>




                                <label>อาชีพ</label>
                                <div class="col-md-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxx" name="ocupation" id="ocupation" value="<?= (isset($row['ocupation']) ? htmlspecialchars($row['ocupation']) : '') ?>">
                                </div>
                                <label>รายได้</label>
                                <div class="col-md-1"><input type="number" class="form-control form-control-sm CheckPer_2" placeholder="เฉพาะตัวเลข" name="income" id="income" value="<?= (isset($row['income']) ? htmlspecialchars($row['income']) : '') ?>" min="0"> </div>
                                <label>บาท/เดือน</label>&nbsp;&nbsp;&nbsp;

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['income_enough'] == '1') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="income_enough1" name="income_enough" value="1">
                                    <label class="custom-control-label" for="income_enough1">เพียงพอ</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['income_enough'] == '2') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="income_enough2" name="income_enough" value="2">
                                    <label class="custom-control-label" for="income_enough2">ไม่เพียงพอ</label>
                                </div>


                            </div>
                            <br>



                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;<label></label>
                                <div>

                                </div> &nbsp;<label>ผู้ดูแล</label>
                                <div class="col-md-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxx" name="caretaker" id="caretaker" value="<?= (isset($row['caretaker']) ? htmlspecialchars($row['caretaker']) : '') ?>">
                                </div>
                                <label>อาชีพ</label>
                                <div class="col-md-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxx" name="caretaker_ocupation" id="caretaker_ocupation" value="<?= (isset($row['caretaker_ocupation']) ? htmlspecialchars($row['caretaker_ocupation']) : '') ?>">
                                </div>
                                <label>รายได้</label>
                                <div class="col-md-1"><input type="number" class="form-control form-control-sm CheckPer_2" placeholder="เฉพาะตัวเลข" name="caretaker_income" id="caretaker_income" value="<?= (isset($row['caretaker_income']) ? htmlspecialchars($row['caretaker_income']) : '') ?>" min="0"> </div>
                                <label>บาท/เดือน</label>&nbsp;&nbsp;&nbsp;

                            </div>
                            <br>
                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;<label></label>
                                <div>
                                </div> &nbsp;<label>อาการแรกรับ</label>

                                <div class="col-sm-12">
                                    <textarea class="form-control" id="first_symptoms" name="first_symptoms" rows="4"><?= (isset($row['first_symptoms']) ? htmlspecialchars($row['first_symptoms']) : '') ?></textarea>
                                </div>

                            </div>
                            <br>

                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label>ข้อมูลเพิ่มเติมตามแบบแผนสุขภาพ (นอกเหนือจากระบบ KPHIS)</label>
                            </div>

                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label>ภาวะโภชนาการและเมตาบอลิซึม</label>
                            </div>
                            <br>
                            <!-- bw  hight bw_befor_prenatal bmi_befor_prenatal -->

                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;<label></label>
                                <div>

                                </div> &nbsp;<label>BW</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="เฉพาะตัวเลข" name="bw" id="bw" value="<?= (isset($row['bw']) ? htmlspecialchars($row['bw']) : '') ?>"> </div>
                                <label>kgs. Hight</label>
                                <div class="col-md-1"><input type="number" class="form-control form-control-sm CheckPer_2" placeholder="เฉพาะตัวเลข" name="hight" id="hight" value="<?= (isset($row['hight']) ? htmlspecialchars($row['hight']) : '') ?>" min="0" min="200"> </div>
                                <label>cms. BW ก่อนการตั้งครรภ์</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="เฉพาะตัวเลข" name="bw_befor_prenatal" id="bw_befor_prenatal" value="<?= (isset($row['bw_befor_prenatal']) ? htmlspecialchars($row['bw_befor_prenatal']) : '') ?>"> </div>
                                <label>kgs. BMI ก่อนตั้งครรภ์</label>&nbsp;&nbsp;&nbsp;
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="เฉพาะตัวเลข" name="bmi_befor_prenatal" id="bmi_befor_prenatal" value="<?= (isset($row['bmi_befor_prenatal']) ? htmlspecialchars($row['bmi_befor_prenatal']) : '') ?>"> </div>
                                <label>kg/m<sup>2</sup></label>&nbsp;&nbsp;&nbsp;

                            </div>

                            <br>
                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label>เพศสัมพันธ์และการเจริญพันธ์</label>
                            </div>
                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ประวัติตกขาว คันช่องคลอด</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['leukorrhea_history'] == 'ปฏิเสธ') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="leukorrhea_history1" name="leukorrhea_history" value="ปฏิเสธ" onchange="custom_check('off_leukorrhea_history');">
                                    <label class="custom-control-label" for="leukorrhea_history1">ปฏิเสธ</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['leukorrhea_history'] != 'ปฏิเสธ' && $row['leukorrhea_history'] != NULL) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="leukorrhea_history2" name="leukorrhea_history" value="มี" onchange="custom_check('on_leukorrhea_history');">
                                    <label class="custom-control-label" for="leukorrhea_history2">มี ระบุ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="leukorrhea_history_text" name="leukorrhea_history" value="<?php if ($row['leukorrhea_history'] != 'ปฏิเสธ' && $row['leukorrhea_history'] != NULL) {
                                                                                                                                                                echo htmlspecialchars($row['leukorrhea_history']);
                                                                                                                                                            } ?>" <?php if (!($row['leukorrhea_history'] != 'ปฏิเสธ' && $row['leukorrhea_history'] != NULL)) {
                                                                                                                                            echo 'disabled';
                                                                                                                                        } ?>>
                                </div>



                            </div>
                            <br>




                            <div class="panel-group" id="accordion">
                                <div class="panel panel-default">
                                    <div class="panel-heading">

                                        <a data-toggle="collapse" data-parent="#accordion" href="#collapse1"><i class="fas fa-plus"></i> ประวัติพฤติกรรมเสี่ยงต่อการติดเชื้อโรคติด (เฉพาะอายุ 14-49ปี) </a>

                                    </div>
                                    <div id="collapse1" class="panel-collapse collapse in">
                                        <div class="panel-body">
                                            <br>
                                            <div class="row">
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<div class="custom-control custom-radio col-sm-3">
                                                    <input type="radio" <?php if ($row['behaviors_risk_sexually'] == '1') {
                                                                            echo 'checked="checked"';
                                                                        } ?> class="custom-control-input" id="behaviors_risk_sexually1" name="behaviors_risk_sexually" value="1">
                                                    <label class="custom-control-label" for="behaviors_risk_sexually1">คู่เพศสัมพันธ์เป็โรคติดต่อทางเพศสัมพันธ์</label>

                                                </div>

                                                <div class="custom-control custom-radio col-sm-3">
                                                    <input type="radio" <?php if ($row['behaviors_risk_sexually'] == '2') {
                                                                            echo 'checked="checked"';
                                                                        } ?> class="custom-control-input" id="behaviors_risk_sexually2" name="behaviors_risk_sexually" value="2">
                                                    <label class="custom-control-label" for="behaviors_risk_sexually2">มีเพศสัมพันธ์ชายกับชาย/หญิงให้บริการไม่ใช้ถุงยาง</label>
                                                </div>

                                            </div>
                                            <br>

                                            <div class="row">
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<div class="custom-control custom-radio col-sm-2">
                                                    <input type="radio" <?php if ($row['behaviors_risk_sexually'] == '3') {
                                                                            echo 'checked="checked"';
                                                                        } ?> class="custom-control-input" id="behaviors_risk_sexually3" name="behaviors_risk_sexually" value="3">
                                                    <label class="custom-control-label" for="behaviors_risk_sexually3">มีเพศสัมพันธ์มากกว่า 1 คน</label>

                                                </div>

                                                <div class="custom-control custom-radio col-sm-2">
                                                    <input type="radio" <?php if ($row['behaviors_risk_sexually'] == '4') {
                                                                            echo 'checked="checked"';
                                                                        } ?> class="custom-control-input" id="behaviors_risk_sexually4" name="behaviors_risk_sexually" value="4">
                                                    <label class="custom-control-label" for="behaviors_risk_sexually4">มีเพศสัมพันธ์กับคนใหม่</label>
                                                </div>
                                                <div class="custom-control custom-radio col-sm-2">
                                                    <input type="radio" <?php if ($row['behaviors_risk_sexually'] == '5') {
                                                                            echo 'checked="checked"';
                                                                        } ?> class="custom-control-input" id="behaviors_risk_sexually5" name="behaviors_risk_sexually" value="5">
                                                    <label class="custom-control-label" for="behaviors_risk_sexually5">ไม่ใช้ถุงยางอนามัยหรือแตก รั่ว หลุด</label>
                                                </div>

                                            </div>
                                            <br>


                                        </div>
                                    </div>
                                </div>

                            </div>






                            <hr>



                        </div>

                        <div class="form-group text-center">
                            <div id="show_check_save"></div>
                            <input type="hidden" id="an" name="an" value="<?= htmlspecialchars($an) ?>">
                            <input type="hidden" id="hn" name="hn" value="<?= htmlspecialchars($hn) ?>">
                            <input type="hidden" id="version" name="version" value="<?= htmlspecialchars($row['version']) ?>">
                            <input type="hidden" id="id" name="id" value="<?= htmlspecialchars($row['id']) ?>">
                            <input type="hidden" id="create_user" name="create_user" value="<?= htmlspecialchars($_SESSION['name']) ?>">

                        </div>

                        <div class="row">

                            <div class="col-md-12 text-right">
                                <?php
                                if ((($id == null)) || (($id != null))) { ?>
                                    <button type="button" class="btn btn-primary" id="btn_save_report" onclick="lr_report2_save()"><i class="fas fa-save"></i> บันทึก</button>
                                <?php } ?>
                                <a href="lr-report2-pdf.php?an=<?php echo $an; ?>&loginname=<?php echo $loginname; ?>" target="_blank" class="btn btn-secondary"><i class="fas fa-file-pdf"></i> Print <U>PDF</U> File</a>
                            </div>
                        </div>
                    </div>
                </div>

                <br>

                <script src="../include/my_function.js"></script>

                <script type="text/javascript">
                    function sendPost() {
                        var value = $('input[name="ans"]:checked').val();

                        if (value == undefined) {
                            console.log('no_value');
                        } else {
                            console.log(value);
                        }
                        //window.location.href = "sendpost.php?ans="+value;
                    };
                </script>

                <script>
                    function AddDoctorSignature() {
                        const doc_name = <?= json_encode($_SESSION['name']) ?>;
                        const doctorcode = <?= json_encode($_SESSION['doctorcode']) ?>;
                        const clone_template_dr_admission_input_div = document.querySelector('#template_dr_admission_input_div').content.cloneNode(true);
                        if (CheckDoctorSignature()) {
                            $('#dr-admission-group-input-div').append(clone_template_dr_admission_input_div);
                            $('[name="doctor[]"].last-focus-input').removeClass('last-focus-input');
                            $('[name="doc_name[]"].last-focus-input').removeClass('last-focus-input');

                            $('[name="doctor[]"]').last().addClass('last-focus-input').val(doctorcode);
                            $('[name="doc_name[]"]').last().addClass('last-focus-input').val(doc_name);

                        }
                    }

                    function CheckDoctorSignature() {
                        const doctorcode_check = <?= json_encode($_SESSION['doctorcode']) ?>;
                        let return_checkdoctorSignature = true;
                        $.each($("input:hidden[name='doctor[]']"), function(index, value) {
                            //console.log({index,value})
                            if (doctorcode_check == $(this).val()) {
                                alert("คุณได้ลงชื่อบันทึกข้อมูลไว้แล้ว");
                                return_checkdoctorSignature = false;
                                return false;
                            }
                        });
                        return return_checkdoctorSignature;
                    }

                    //ควบคุมปุ่ม
                    function custom_check(value) {

                        if (value == "off_c_chronic") {
                            $('#c_chronic_text').attr("disabled", true).val('');
                            $('#c_chronic2').prop("checked", false);
                        } else if (value == "on_c_chronic") {
                            $('#c_chronic_text').attr("disabled", false).val('');
                            $('#c_chronic1').prop("checked", false);
                        }

                        if (value == "off_hos_history") {
                            $('#hos_history_text').attr("disabled", true).val('');
                            $('#hos_history2').prop("checked", false);
                        } else if (value == "on_hos_history") {
                            $('#hos_history_text').attr("disabled", false).val('');
                            $('#hos_history').prop("checked", false);
                        }

                        if (value == "off_h_sergery") {
                            $('#h_sergery_text').attr("disabled", true).val('');
                            $('#h_sergery2').prop("checked", false);
                        } else if (value == "on_h_sergery") {
                            $('#h_sergery_text').attr("disabled", false).val('');
                            $('#h_sergery1').prop("checked", false);
                        }

                        if (value == "off_h_allergy") {
                            $('#h_allergy_text').attr("disabled", true).val('');
                            $('#h_allergy2').prop("checked", false);
                        } else if (value == "on_h_allergy") {
                            $('#h_allergy_text').attr("disabled", false).val('');
                            $('#h_allergy1').prop("checked", false);
                        }

                        if (value == "off_history_of_drug") {
                            $('#history_of_drug_text').attr("disabled", true).val('');
                            $('#history_of_drug2').prop("checked", false);
                        } else if (value == "on_history_of_drug") {
                            $('#history_of_drug_text').attr("disabled", false).val('');
                            $('#history_of_drug1').prop("checked", false);
                        }

                        if (value == "off_pmh2") {
                            $('#pmh2_text').attr("disabled", true).val('');
                            $('#pmh22').prop("checked", false);
                        } else if (value == "on_pmh2") {
                            $('#pmh2_text').attr("disabled", false).val('');
                            $('#pmh21').prop("checked", false);
                        }

                        if (value == "off_leukorrhea_history") {
                            $('#leukorrhea_history_text').attr("disabled", true).val('');
                            $('#leukorrhea_history').prop("checked", false);
                        } else if (value == "on_leukorrhea_history") {
                            $('#leukorrhea_history_text').attr("disabled", false).val('');
                            $('#leukorrhea_history').prop("checked", false);
                        }



                    }





                    function lr_report2_save() {


                        var labor_history_all = "";
                        var total = $('#total_chq').val();
                        for (i = 1; i <= total; i++) {
                            if (typeof $('#preg_num' + i).val() === 'undefined') {
                                labor_history_all += "";
                                // console.log('check');
                            } else {
                                labor_history_all += ($('#preg_num' + i).val()) + ' ' + ($('#labor_date' + i).val()) + ' ' + ($('#ga' + i).val()) + ' ' +
                                    ($('#labor_by' + i).val()) + ' ' + ($('#labor_weight' + i).val()) + ' ' + ($('#l_sex' + i).val()) + ' ' + ($('#location' + i).val()) + ' ' + ($('#complications' + i).val()) + ' ' + ($('#l_history' + i).val()) + ' '
                                /* ($('#location' + i).val()) + ' ' +
                                                                   ($('#complications' + i).val()) + ' ' + ($('#history' + i).val()) + ' '*/
                                ;
                            }
                        }
                        if (labor_history_all != "   ") {
                            $('#labor_history').val(labor_history_all);

                        }



                        var rxdate = $.trim($('[name="rxdate"]').val());
                        var rxtime = $.trim($('[name="rxtime"]').val());

                        var labor_history = $.trim($('[name="labor_history"]').val());
                        var c_chronic = $('input[name="c_chronic"]:checked').val();
                        var hos_history = $('input[name="hos_history"]:checked').val();
                        var h_sergery = $('input[name="h_sergery"]:checked').val();
                        var h_allergy = $('input[name="h_allergy"]:checked').val();
                        var history_of_drug = $('input[name="history_of_drug"]:checked').val();
                        var pmh2 = $('input[name="pmh2"]:checked').val();


                        var prenatal_wks = $.trim($('[name="prenatal_wks"]').val());

                        if (rxdate == "") {

                            $('[name="rxdate"]').focus();
                            alert('เลือกวันที่');
                        } else if (rxtime == "") {

                            $('[name="rxtime"]').focus();
                            alert('เลือกเวลา');
                        } else if (c_chronic == undefined) {
                            $('[name="c_chronic"]').focus();
                            alert('โรคประจำตัว');
                        } else if (hos_history == undefined) {

                            $('[name="hos_history"]').focus();
                            alert('เคยรับการรักษาในโรงพยาบาล');
                        } else if (h_sergery == undefined) {

                            $('[name="h_sergery"]').focus();
                            alert('ประวัติการผ่าตัด');
                            // console.log(h_sergery);
                        } else if (h_allergy == undefined) {

                            $('[name="h_allergy"]').focus();
                            alert('ประวัติการแพ้');
                            // console.log(h_sergery);
                        } else if (history_of_drug == undefined) {

                            $('[name="history_of_drug"]').focus();
                            alert('ประวัติการใช้ยาและผลิตภัณฑ์สุขภาพ');
                            // console.log(h_sergery);
                        } else if (pmh2 == undefined) {

                            $('[name="pmh2"]').focus();
                            alert('ประวัติการเจ็บป่วยในครอบครัว');
                            // console.log(h_sergery);
                        } else if (prenatal_wks == undefined) {

                            $('[name="prenatal_wks"]').focus();
                            alert('ฝากครรภ์ครั้งแรก');
                            // console.log(h_sergery);
                        } else if (labor_history == "") {

                            $('[name="labor_history"]').focus();
                            alert('บันทึกประวัติคลอด');

                        }


                        // console.log(labor_history);


                        var url_update = "lr-report2-update.php";
                        var url_save = "lr-report2-save.php";
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


                <script src="../node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
                <link rel="stylesheet" href="../node_modules/sweetalert2/dist/sweetalert2.min.css">

                <?php
                if (isset($_POST['submit'])) {
                    $selected_val = $_POST['education'];  // Storing Selected Value In Variable
                    echo "You have selected :" . $selected_val;  // Displaying Selected Value
                }
                ?>