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
require_once '../include/ReportQueryUtils.php';

$conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
$an = empty($_REQUEST['an']) ? null : $_REQUEST['an'];

$hn = KphisQueryUtils::getHnByAn($an);
$vn = KphisQueryUtils::getVnByAn($an);
$an_parameters = ['an' => $an];
$hn_parameters = ['hn' => $hn];


Session::insertSystemAccessLog(json_encode(array(
    'form' => 'PRE-NURSENOTE-FORM',
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
                FROM `prs_pre_nursenote`
                WHERE an = :an";
$stmt = $conn->prepare($sql);
$stmt->execute($an_parameters);
if ($row  = $stmt->fetch()) {
    $id = $row['id'];
} else {
    $id = null;
}



$sql_item = "SELECT dr_adm_item.id,
                    dr_adm_item.doctor,
                    doctor.`name` AS admission_note_doctorname
                    FROM " . DbConstant::KPHIS_DBNAME . ".prs_pre_nursenote_item dr_adm_item
                    LEFT OUTER JOIN " . DbConstant::HOSXP_DBNAME . ".doctor ON doctor.code = dr_adm_item.doctor
                    WHERE an=:an
                    ORDER BY dr_adm_item.id ASC";
$stmt_item = $conn->prepare($sql_item);
$stmt_item->execute($an_parameters);
$pre_note_count = 0;
while ($row_item = $stmt_item->fetch()) {
    $id_pre_note[] = $row_item['id'];
    $doctor[] = $row_item['doctor'];
    $admission_note_doctorname[] = $row_item['admission_note_doctorname'];
    //$admission_note_doctorentryposition[] = $row_item['admission_note_doctorentryposition'];
    $pre_note_count++;
}



//echo $id;
//echo $vn;
// opdscreen

if ($id == null || $id != null) {
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


    //$stmt_item_check = "SELECT max(id) AS count_item  FROM " . DbConstant::KPHIS_DBNAME . ".prs_pre_nursenote_item";
    // $stmt_item_check = $conn->prepare($stmt_item_check);
    // $stmt_item_check->execute(['an' => $an]);
    // $row_item_check = $stmt_item_check->fetch();
    // $count_item = $row_item_check['count_item'];

    //echo $count_item + 1;
}



date_default_timezone_set('asia/bangkok');

//echo $_SESSION['name']; 

$id = '16'; //Link menu
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

    .error {
        color: #FF0000;
    }
</style>





<form id="my_form">
    <div class="container-fluid">
        <div class="row">
            <div class="col-auto">
                <button type="button" class="btn btn-sm btn-primary btn-block" onclick="window.close()"><i class="fas fa-arrow-left"></i> กลับ</button>
            </div>
            <div class="col-auto p-1 font-weight-bold">
                <h5><B>ใบบันทึกประวัติและประเมินสมรรถนะผู้ป่วยแรกรับ <?= htmlspecialchars(DbConstant::HOSPITAL_NAME) ?><?php if ($check_ == "1") { ?>

<font color="red">ช่วงทดลอง</font>
<?php } else { ?>

<? } ?></B></h5>
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


                                <div class="form-group row">
                                    <div class="col-sm-1"></div>
                                    <div class="custom-control custom-radio col-sm-2">
                                        <input type="radio" <?php if (
                                                                $row['depart'] == 'OPD'
                                                                /*|| $row['depart'] == NULL*/
                                                            ) {
                                                                echo 'checked="checked"';
                                                            } ?> class="custom-control-input" id="depart1" name="depart" value="OPD" onchange="custom_check('off_depart');">
                                        <label class="custom-control-label" for="depart1">OPD</label>
                                    </div>
                                    <div class="custom-control custom-radio col-sm-2">
                                        <input type="radio" <?php if ($row['depart'] == 'ER') {
                                                                echo 'checked="checked"';
                                                            } ?> class="custom-control-input" id="depart2" name="depart" value="ER" onchange="custom_check('off_depart');">
                                        <label class="custom-control-label" for="depart2">ER</label>
                                    </div>
                                    <div class="custom-control custom-radio col-sm-2">
                                        <input type="radio" <?php if (
                                                                $row['depart'] != 'OPD'
                                                                && $row['depart'] != 'ER'
                                                                && $row['depart'] != NULL
                                                            ) {
                                                                echo 'checked="checked"';
                                                            } ?> class="custom-control-input" id="depart3"  name="depart" value="อื่นๆ" onchange="custom_check('on_depart');">
                                        <label class="custom-control-label" for="depart3">อื่นๆ</label>
                                    </div>
                                    <div class="col-sm-5">
                                        <input type="text" class="form-control form-control-sm" id="other" name="depart" value="<?php if (
                                                                                                                                    $row['depart'] != 'OPD'
                                                                                                                                    && $row['depart'] != 'ER'
                                                                                                                                ) {
                                                                                                                                    echo htmlspecialchars($row['depart']);
                                                                                                                                } ?>" <?php if (!($row['depart'] != 'OPD'
                                                                                                                                            && $row['depart'] != 'ER'
                                                                                                                                            && $row['depart'] != NULL)) {
                                                                                                                                            echo 'disabled';
                                                                                                                                        } ?>>
                                    </div>
                                </div>



                                &nbsp;&nbsp; <label>กรณีส่งต่อ ส่งต่อจาก</label>
                                <div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxx" name="refer_from" id="refer_from" value="<?= (isset($row['refer_from']) ? htmlspecialchars($row['refer_from']) : '') ?>" required>

                                </div>

                            </div>

                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>รับไว้ในโรงพยาบาลโดย</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['hospital_by'] == 'เดินมา') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="hospital_by1" name="hospital_by" value="เดินมา" onchange="custom_check('off_hospital_by');">
                                    <label class="custom-control-label" for="hospital_by1">เดินมา</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['hospital_by'] == 'รถนั่ง') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="hospital_by2" name="hospital_by" value="รถนั่ง" onchange="custom_check('off_hospital_by');">
                                    <label class="custom-control-label" for="hospital_by2">รถนั่ง</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['hospital_by'] == 'รถนอน') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="hospital_by3" name="hospital_by" value="รถนอน" onchange="custom_check('off_hospital_by');">
                                    <label class="custom-control-label" for="hospital_by3">รถนอน</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if (
                                                                                    $row['hospital_by'] != 'เดินมา'
                                                                                    && $row['hospital_by'] != 'รถนั่ง'
                                                                                    && $row['hospital_by'] != 'รถนอน'
                                                                                    && $row['hospital_by'] != NULL
                                                                                ) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="hospital_by4" name="hospital_by" value="อื่นๆ" onchange="custom_check('on_hospital_by');">
                                    <label class="custom-control-label" for="hospital_by4">อื่นๆ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="hospital_by_text" name="hospital_by" value="<?php if (
                                                                                                                                                $row['hospital_by'] != 'เดินมา'
                                                                                                                                                && $row['hospital_by'] != 'รถนั่ง'
                                                                                                                                                && $row['hospital_by'] != 'รถนอน'
                                                                                                                                                && $row['hospital_by'] != NULL
                                                                                                                                            ) {
                                                                                                                                                echo htmlspecialchars($row['hospital_by']);
                                                                                                                                            } ?>" <?php if (!($row['hospital_by'] != 'เดินมา'
                                                                                                                                                        && $row['hospital_by'] != 'รถนั่ง'
                                                                                                                                                        && $row['hospital_by'] != 'รถนอน'
                                                                                                                                                        && $row['hospital_by'] != NULL
                                                                                                                                                    )) {
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

                                    <!-- <textarea class="form-control" id="current_illness" name="current_illness" rows="4"><?= (isset($row_opdscreen['hpi']) && $id == null ? htmlspecialchars($row_opdscreen['hpi']) : htmlspecialchars($row['current_illness'])) ?></textarea> -->
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

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ประวัติการแพ้(ยา/อาหาร/สารเคมี/เลือด)</label>
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

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ประวัติการได้รับวัคซีน(เฉพาะ < 15 ปี)</label>
                                        <div class="custom-control custom-radio col-sm-1">
                                            &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <input type="radio" <?php if (
                                                                                                                $row['vaccine_history'] == 'ครบตามเกณฑ์'

                                                                                                            ) {
                                                                                                                echo 'checked="checked"';
                                                                                                            } ?> class="custom-control-input" id="vaccine_history1" name="vaccine_history" value="ปฏิเสธ" onchange="custom_check('off_vaccine_history');">
                                            <label class="custom-control-label" for="vaccine_history1">ครบตามเกณฑ์</label>
                                        </div>

                                        <div class="custom-control custom-radio col-sm-2">
                                            &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['vaccine_history'] != 'ปฏิเสธ' && $row['vaccine_history'] != NULL) {
                                                                                            echo 'checked="checked"';
                                                                                        } ?> class="custom-control-input" id="vaccine_history2" name="vaccine_history" value="ไม่ครบตามเกณฑ์" onchange="custom_check('on_vaccine_history');">
                                            <label class="custom-control-label" for="vaccine_history2">ไม่ครบตามเกณฑ์ ระบุ</label>
                                        </div>

                                        <div class="col-sm-5">
                                            <input type="text" class="form-control form-control-sm" id="vaccine_history_text" name="vaccine_history" value="<?php if ($row['vaccine_history'] != 'ปฏิเสธ' && $row['vaccine_history'] != NULL) {
                                                                                                                                                                echo htmlspecialchars($row['vaccine_history']);
                                                                                                                                                            } ?>" <?php if (!($row['vaccine_history'] != 'ปฏิเสธ' && $row['vaccine_history'] != NULL)) {
                                                                                                                                                                        echo 'disabled';
                                                                                                                                                                    } ?>>
                                        </div>


                            </div>
                            <br>


                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>การเจริญเติบโตและพัฒนาการ (เฉพาะ < 15 ปี)</label>
                                        <div class="custom-control custom-radio col-sm-1">
                                            &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <input type="radio" <?php if (
                                                                                                                $row['child_devilopment'] == 'สมวัย'

                                                                                                            ) {
                                                                                                                echo 'checked="checked"';
                                                                                                            } ?> class="custom-control-input" id="child_devilopment1" name="child_devilopment" value="สมวัย" onchange="custom_check('off_child_devilopment');">
                                            <label class="custom-control-label" for="child_devilopment1">สมวัย</label>
                                        </div>

                                        <div class="custom-control custom-radio col-sm-1">
                                            &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['child_devilopment'] != 'สมวัย' && $row['child_devilopment'] != NULL) {
                                                                                            echo 'checked="checked"';
                                                                                        } ?> class="custom-control-input" id="child_devilopment2" name="child_devilopment" value="ไม่สมวัย" onchange="custom_check('on_child_devilopment');">
                                            <label class="custom-control-label" for="child_devilopment2">ไม่สมวัย ระบุ</label>
                                        </div>

                                        <div class="col-sm-5">
                                            <input type="text" class="form-control form-control-sm" id="child_devilopment_text" name="child_devilopment" value="<?php if ($row['child_devilopment'] != 'สมวัย' && $row['child_devilopment'] != NULL) {
                                                                                                                                                                    echo htmlspecialchars($row['child_devilopment']);
                                                                                                                                                                } ?>" <?php if (!($row['child_devilopment'] != 'สมวัย' && $row['child_devilopment'] != NULL)) {
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
                                    <textarea class="form-control" id="pmh2" name="pmh2" rows="3"><?= (isset($row_opdscreen['pmh']) && $id == null ? htmlspecialchars($row_opdscreen['pmh']) : htmlspecialchars($row['pmh2'])) ?></textarea>
                                </div>
                            </div>
                                                                                                                                    -->


                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>สัญญาณชีพแรกรับ&nbsp; BT&nbsp;</label>
                                <div>
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="เฉพาะตัวเลข" name="bt" id="bt" value="<?= (isset($row['bt']) ? htmlspecialchars(round(($row['bt']), 2)) : '') ?>">
                                </div>C&nbsp; PR&nbsp;<div>
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="เฉพาะตัวเลข" name="pr" id="pr" value="<?= (isset($row['pr']) ? htmlspecialchars(round(($row['pr']), 2)) : '') ?>">
                                </div> /min&nbsp;<label>RR</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="เฉพาะตัวเลข" name="rr" id="rr" value="<?= (isset($row['rr']) ? htmlspecialchars(round(($row['rr']), 2)) : '') ?>"> </div>
                                <label>/min&nbsp;BP</label>
                                <div class="col-md-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="bps" name="bps" id="bps" value="<?= (isset($row['bps']) ? htmlspecialchars(round(($row['bps']), 2)) : '') ?>">

                                </div> /
                                <div class="col-md-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="bpd" name="bpd" id="bpd" value="<?= (isset($row['bpd']) ? htmlspecialchars(round(($row['bpd']), 2)) : '') ?>">

                                </div>
                                <label>mmHg</label>


                            </div>
                            <br>

                            <div class="form-group row">
                                <label class="col-sm-12"><B>สภาพร่างกายผู้ป่วยแรกรับ</B></label>
                            </div>


                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label>ระดับความรู้สึกตัว</label>&nbsp;&nbsp;&nbsp;&nbsp;


                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['level_of_con'] == 'รู้สึกตัวดี') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="level_of_con1" name="level_of_con" value="รู้สึกตัวดี">
                                    <label class="custom-control-label" for="level_of_con1">รู้สึกตัวดี</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">

                                    <input type="radio" <?php if ($row['level_of_con'] == 'สับสน') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="level_of_con2" name="level_of_con" value="สับสน">
                                    <label class="custom-control-label" for="level_of_con2">สับสน</label>


                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['level_of_con'] == 'ซึม') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="level_of_con3" name="level_of_con" value="ซึม">
                                    <label class="custom-control-label" for="level_of_con3">ซึม</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['level_of_con'] == 'ไม่รู้สึกตัว') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="level_of_con4" name="level_of_con" value="ไม่รู้สึกตัว">
                                    <label class="custom-control-label" for="level_of_con4">ไม่รู้สึกตัว</label>
                                </div>


                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label>การหายใจ</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['breathing'] == 'ปกติ') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="breathing1" name="breathing" value="ปกติ" onchange="custom_check('off_breathing');">
                                    <label class="custom-control-label" for="breathing1">ปกติ</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['breathing'] == 'หายใจหอบ') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="breathing2" name="breathing" value="หายใจหอบ" onchange="custom_check('off_breathing');">
                                    <label class="custom-control-label" for="breathing2">หายใจหอบ</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['breathing'] == 'หายใจลำบาก') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="breathing3" name="breathing" value="หายใจลำบาก" onchange="custom_check('off_breathing');">
                                    <label class="custom-control-label" for="breathing3">หายใจลำบาก</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['breathing'] == 'ไม่หายใจ') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="breathing4" name="breathing" value="ไม่หายใจ" onchange="custom_check('off_breathing');">
                                    <label class="custom-control-label" for="breathing4">ไม่หายใจ</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if (
                                                                                    $row['breathing'] != 'ปกติ'
                                                                                    && $row['breathing'] != 'หายใจหอบ'
                                                                                    && $row['breathing'] != 'หายใจลำบาก'
                                                                                    && $row['breathing'] != 'ไม่หายใจ'
                                                                                    && $row['breathing'] != NULL
                                                                                ) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="breathing5" name="breathing" value="อื่น" onchange="custom_check('on_breathing');">
                                    <label class="custom-control-label" for="breathing5">อื่นๆ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="breathing_text" name="breathing" value="<?php if (
                                                                                                                                            $row['breathing'] != 'ปกติ'
                                                                                                                                            && $row['breathing'] != 'หายใจหอบ'
                                                                                                                                            && $row['breathing'] != 'หายใจลำบาก'
                                                                                                                                            && $row['breathing'] != 'ไม่หายใจ'
                                                                                                                                            && $row['breathing'] != NULL
                                                                                                                                        ) {
                                                                                                                                            echo htmlspecialchars($row['breathing']);
                                                                                                                                        } ?>" <?php if (!($row['breathing'] != 'ปกติ'
                                                                                                                                                    && $row['breathing'] != 'หายใจหอบ'
                                                                                                                                                    && $row['breathing'] != 'หายใจลำบาก'
                                                                                                                                                    && $row['breathing'] != 'ไม่หายใจ'
                                                                                                                                                    && $row['breathing'] != NULL)) {
                                                                                                                                                    echo 'disabled';
                                                                                                                                                } ?>>
                                </div>


                            </div>
                            <br>

                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label>การไหลเวียนโลหิต สีผิว</label>&nbsp;&nbsp;&nbsp;&nbsp;


                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['blood_circulation'] == 'ปกติ') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="blood_circulation1" name="blood_circulation" value="ปกติ">
                                    <label class="custom-control-label" for="blood_circulation1">ปกติ</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">

                                    <input type="radio" <?php if ($row['blood_circulation'] == 'ซีด') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="blood_circulation2" name="blood_circulation" value="ซีด">
                                    <label class="custom-control-label" for="blood_circulation2">ซีด</label>


                                </div>

                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php if ($row['blood_circulation'] == 'ปลายมือปลายเท้าเขียว') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="blood_circulation3" name="blood_circulation" value="ปลายมือปลายเท้าเขียว">
                                    <label class="custom-control-label" for="blood_circulation3">ปลายมือปลายเท้าเขียว</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['blood_circulation'] == 'รอบปากเขียว') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="blood_circulation4" name="blood_circulation" value="รอบปากเขียว">
                                    <label class="custom-control-label" for="blood_circulation4">รอบปากเขียว</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['blood_circulation'] == 'เขียวทั่วตัว') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="blood_circulation5" name="blood_circulation" value="เขียวทั่วตัว">
                                    <label class="custom-control-label" for="blood_circulation5">เขียวทั่วตัว</label>
                                </div>


                            </div>
                            <br>



                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label></label>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label>อาการบวม</label>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <input type="radio" <?php if (
                                                                                                        $row['swelling'] == 'ไม่มี'

                                                                                                    ) {
                                                                                                        echo 'checked="checked"';
                                                                                                    } ?> class="custom-control-input" id="swelling1" name="swelling" value="ไม่มี" onchange="custom_check('off_swelling');">
                                    <label class="custom-control-label" for="swelling1">ไม่มี</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['swelling'] != 'ไม่มี' && $row['swelling'] != NULL) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="swelling2" name="swelling" value="บวมบริเวณ" onchange="custom_check('on_swelling');">
                                    <label class="custom-control-label" for="swelling2">บวมบริเวณ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="swelling_text" name="swelling" value="<?php if ($row['swelling'] != 'ไม่มี' && $row['swelling'] != NULL) {
                                                                                                                                            echo htmlspecialchars($row['swelling']);
                                                                                                                                        } ?>" <?php if (!($row['swelling'] != 'ไม่มี' && $row['swelling'] != NULL)) {
                                                                                                                                                    echo 'disabled';
                                                                                                                                                } ?>>
                                </div>


                            </div>
                            <br>

                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label>ผิวหนัง</label>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['skin'] == 'ปกติ') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="skin1" name="skin" value="ปกติ">
                                    <label class="custom-control-label" for="skin1">ปกติ</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">

                                    <input type="radio" <?php if ($row['skin'] == 'หนังแตก') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="skin2" name="skin" value="หนังแตก">
                                    <label class="custom-control-label" for="skin2">หนังแตก</label>


                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['skin'] == 'เขียวช้ำ') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="skin3" name="skin" value="เขียวช้ำ">
                                    <label class="custom-control-label" for="skin3">เขียวช้ำ</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['skin'] == 'ผื่นแดง') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="skin4" name="skin" value="ผื่นแดง">
                                    <label class="custom-control-label" for="skin4">ผื่นแดง</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['skin'] == 'ผื่นคัน') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="skin5" name="skin" value="ผื่นคัน">
                                    <label class="custom-control-label" for="skin5">ผื่นคัน</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['skin'] == 'เหลือง') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="skin6" name="skin" value="เหลือง">
                                    <label class="custom-control-label" for="skin6">เหลือง</label>
                                </div>

                            </div>
                            <br>
                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label>การติดต่อสื่อสาร หู</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php if ($row['communication_ears'] == 'ได้ยินชัดเจน') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="communication_ears1" name="communication_ears" value="ได้ยินชัดเจน">
                                    <label class="custom-control-label" for="communication_ears1">ได้ยินชัดเจน</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php if ($row['communication_ears'] == 'ได้ยินไม่ชัดเจน') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="communication_ears2" name="communication_ears" value="ได้ยินไม่ชัดเจน">
                                    <label class="custom-control-label" for="communication_ears2">ได้ยินไม่ชัดเจน : ใช้อุปกรณ์ช่วยฟัง</label>
                                </div>


                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['hearing_aid'] == 'มี') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="hearing_aid1" name="hearing_aid" value="มี">
                                    <label class="custom-control-label" for="hearing_aid1">มี</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['hearing_aid'] == 'ไม่มี') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="hearing_aid2" name="hearing_aid" value="ไม่มี">
                                    <label class="custom-control-label" for="hearing_aid2">ไม่มี</label>
                                </div>



                            </div>
                            <br>

                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label>ตา</label>
                                <div class="custom-control custom-radio col-sm-2">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['communication_eyes'] == 'เห็นชัดเจน') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="communication_eyes1" name="communication_eyes" value="เห็นชัดเจน">
                                    <label class="custom-control-label" for="communication_eyes1">เห็นชัดเจน</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php if ($row['communication_eyes'] == 'เห็นไม่ชัดเจน') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="communication_eyes2" name="communication_eyes" value="เห็นไม่ชัดเจน">
                                    <label class="custom-control-label" for="communication_eyes2">เห็นไม่ชัดเจน : สวมแว่นตา</label>

                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['glasses'] == 'สวม') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="glasses1" name="glasses" value="สวม">
                                    <label class="custom-control-label" for="glasses1">สวม</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['glasses'] == 'ไม่สวม') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="glasses2" name="glasses" value="ไม่สวม">
                                    <label class="custom-control-label" for="glasses2">ไม่สวม</label>
                                </div>



                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label></label>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label>การพูด</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['communication_speak'] == 'ชัดเจน') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="communication_speak1" name="communication_speak" value="ชัดเจน" onchange="custom_check('off_communication_speak');">
                                    <label class="custom-control-label" for="communication_speak1">ชัดเจน</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['communication_speak'] == 'พูดติดอ่าง') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="communication_speak2" name="communication_speak" value="พูดติดอ่าง" onchange="custom_check('off_communication_speak');">
                                    <label class="custom-control-label" for="communication_speak2">พูดติดอ่าง</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['communication_speak'] == 'เป็นใบ้') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="communication_speak3" name="communication_speak" value="เป็นใบ้" onchange="custom_check('off_communication_speak');">
                                    <label class="custom-control-label" for="communication_speak3">เป็นใบ้</label>
                                </div>



                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if (
                                                                                    $row['communication_speak'] != 'ชัดเจน'
                                                                                    && $row['communication_speak'] != 'พูดติดอ่าง'
                                                                                    && $row['communication_speak'] != 'เป็นใบ้'
                                                                                    && $row['communication_speak'] != NULL
                                                                                ) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="communication_speak4" name="communication_speak" value="อื่นๆ" onchange="custom_check('on_communication_speak');">
                                    <label class="custom-control-label" for="communication_speak4">อื่นๆ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="communication_speak_text" name="communication_speak" value="<?php if (
                                                                                                                                                                $row['communication_speak'] != 'ชัดเจน'
                                                                                                                                                                && $row['communication_speak'] != 'พูดติดอ่าง'
                                                                                                                                                                && $row['communication_speak'] != 'เป็นใบ้'
                                                                                                                                                                && $row['communication_speak'] != NULL
                                                                                                                                                            ) {
                                                                                                                                                                echo htmlspecialchars($row['breathing']);
                                                                                                                                                            } ?>" <?php if (!($row['communication_speak'] != 'ชัดเจน'
                                                                                                                                                                        && $row['communication_speak'] != 'พูดติดอ่าง'
                                                                                                                                                                        && $row['communication_speak'] != 'เป็นใบ้'
                                                                                                                                                                        && $row['communication_speak'] != NULL)) {
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
                                    <textarea class="form-control" id="state_of_mind" name="state_of_mind" rows="1"><?= (isset($row['state_of_mind']) ? htmlspecialchars($row['state_of_mind']) : '') ?></textarea>
                                </div>
                            </div>


                            <div class="form-group row">
                                <label class="col-sm-12">อาการแรกรับ</label>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <textarea class="form-control" id="first_symptoms" name="first_symptoms" rows="1"><?= (isset($row['first_symptoms']) ? htmlspecialchars($row['first_symptoms']) : '') ?></textarea>
                                </div>
                            </div>


                            <hr>




                            <div class="form-row">
                                <div class="col-md-4">
                                    <div class="form-group text-right">
                                        <label for="action-person-dr-admission">Attending physician</label>
                                        <button type="button" class="btn btn-secondary btn-sm mb-2" onclick="AddDoctorSignature()"><i class="fas fa-plus"></i> ลงชื่อ</button>
                                        <div id="dr-admission-group-input-div">
                                            <template id="template_dr_admission_input_div">
                                                <div class="dr_admission_input_div">
                                                    <div class="input-group mb-2">
                                                        <input type="hidden" class="form-control form-control" name="doctor[]">
                                                        <input type="text" class="form-control form-control" name="doc_name[]" readonly>

                                                    </div>
                                                </div>
                                            </template>
                                            <?php $start_count = 0;
                                            while ($start_count < $pre_note_count) { ?>
                                                <div class="dr_admission_input_div">
                                                    <div class="input-group mb-2">
                                                        <input type="hidden" class="form-control form-control" name="doctor[]" value="<?= $doctor[$start_count] ?>">
                                                        <input type="text" class="form-control form-control" name="doc_name[]" value="<?= $admission_note_doctorname[$start_count] ?>" readonly>

                                                    </div>
                                                </div>
                                            <?php $start_count++;
                                            } ?>
                                        </div>
                                    </div>
                                </div>





                            </div>



                        </div>

                        <div class="form-group text-center">
                            <div id="show_check_save"></div>
                            <input type="hidden" id="an" name="an" value="<?= htmlspecialchars($an) ?>">
                            <input type="hidden" id="hn" name="hn" value="<?= htmlspecialchars($hn) ?>">
                            <input type="hidden" id="c_form_type" name="c_form_type" value="2">
                            <input type="hidden" id="version" name="version" value="<?= htmlspecialchars($row['version']) ?>">
                            <input type="hidden" id="id" name="id" value="<?= htmlspecialchars($row['id']) ?>">
                            <input type="hidden" id="create_user" name="create_user" value="<?= htmlspecialchars($_SESSION['name']) ?>">

                        </div>




                        <div class="row">

                            <div class="col-md-12 text-right">
                                <?php
                                if ((($id == null)) || (($id != null))) { ?>
                                    <button type="button" class="btn btn-primary" id="btn_save_report" onclick="pre_nursenote_save()"><i class="fas fa-save"></i> บันทึก</button>
                                <?php } ?>
                                <a href="prs-pre-nursenote-pdf.php?an=<?php echo $an; ?>&loginname=<?php echo $loginname; ?>" target="_blank" class="btn btn-secondary"><i class="fas fa-file-pdf"></i> Print <U>PDF</U> File</a>
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






                        function pre_nursenote_save() {
                            // var rxdate = $('input[type=date][name="rxdate]').val();
                            var rxdate = $.trim($('[name="rxdate"]').val());
                            var rxtime = $.trim($('[name="rxtime"]').val());
                            var depart = $('input[name="depart"]:checked').val();
                            var hospital_by = $('input[name="hospital_by"]:checked').val();
                            var cc = $.trim($('[name="cc"]').val());
                            var current_illness = $.trim($('[name="current_illness"]').val());
                            var c_chronic = $('input[name="c_chronic"]:checked').val();
                            var hos_history = $('input[name="hos_history"]:checked').val();
                            var h_sergery = $('input[name="h_sergery"]:checked').val();
                            var h_allergy = $('input[name="h_allergy"]:checked').val();
                            var child_devilopment = $('input[name="child_devilopment"]:checked').val();
                            var history_of_drug = $('input[name="history_of_drug"]:checked').val();
                            var pmh2 = $('input[name="pmh2"]:checked').val();
                            var bt = $.trim($('[name="bt"]').val());
                            var pr = $.trim($('[name="pr"]').val());
                            var rr = $.trim($('[name="rr"]').val());
                            var bps = $.trim($('[name="bps"]').val());
                            var bpd = $.trim($('[name="bpd"]').val());
                            var breathing = $('input[name="breathing"]:checked').val();
                            var blood_circulation = $('input[name="blood_circulation"]:checked').val();
                            var swelling = $('input[name="swelling"]:checked').val();
                            var skin = $('input[name="skin"]:checked').val();
                            var communication_ears = $('input[name="communication_ears"]:checked').val();
                            var hearing_aid = $('input[name="hearing_aid"]:checked').val();
                            var communication_eyes = $('input[name="communication_eyes"]:checked').val();
                            var glasses = $('input[name="glasses"]:checked').val();
                            var communication_speak = $('input[name="communication_speak"]:checked').val();
                            var state_of_mind = $.trim($('[name="state_of_mind"]').val());
                            var first_symptoms = $.trim($('[name="first_symptoms"]').val());
                            var level_of_con = $('input[name="level_of_con"]:checked').val();


                            // var depart = $('input[type=radio][name=depart]:checked').val();
                            console.log(depart)

                            if (rxdate == "") {

                                $('[name="rxdate"]').focus();
                                alert('เลือกวันที่');
                            } else if (rxtime == "") {

                                $('[name="rxtime"]').focus();
                                alert('เลือกเวลา');
                            } else if (depart == undefined) {

                                $('[name="depart"]').focus();
                                //alert(depart)
                                alert('หน่วยงาน');
                            } else if (hospital_by == undefined) {

                                $('[name="hospital_by"]').focus();
                                alert('รับไว้ในโรงพยาบาลโดย');
                            } else if (current_illness == "") {

                                $('[name="current_illness"]').focus();
                                alert('บันทึกประวัติเจ็บป่วยปัจจุบัน');
                                // console.log(h_sergery);
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
                            }
                            /*else if (child_devilopment == undefined) {

                                                           $('[name="child_devilopment"]').focus();
                                                           // console.log(h_sergery);
                                                       } */
                            else if (history_of_drug == undefined) {

                                $('[name="history_of_drug"]').focus();
                                alert('ประวัติการใช้ยาและผลิตภัณฑ์สุขภาพ');
                                // console.log(h_sergery);
                            } else if (pmh2 == undefined) {

                                $('[name="pmh2"]').focus();
                                alert('ประวัติการเจ็บป่วยในครอบครัว');
                                // console.log(h_sergery);
                            } else if (bt == '') {

                                $('[name="bt"]').focus();
                                alert('กรุณากรอกสัญญาณชีพ BT');
                            } else if (pr == '') {

                                $('[name="pr"]').focus();
                                alert('กรุณากรอกสัญญาณชีพ PR');
                            } else if (rr == '') {

                                $('[name="rr"]').focus();
                                alert('กรุณากรอกสัญญาณชีพ RR');
                            } else if (bps == '' || bpd == '') {

                                $('[name="bps"]').focus();
                                alert('กรุณากรอกสัญญาณชีพ BPS/BPD');
                            } else if (level_of_con == undefined) {

                                $('[name="level_of_con"]').focus();
                                alert('เลือกระดับความรู้สึกตัว');
                                // console.log(h_sergery);
                            } else if (breathing == undefined) {

                                $('[name="breathing"]').focus();
                                alert('การหายใจ');
                                // console.log(h_sergery);
                            } else if (blood_circulation == undefined) {

                                $('[name="blood_circulation"]').focus();
                                alert('การไหลเวียนโลหิต');
                                // console.log(h_sergery);
                            } else if (swelling == undefined) {

                                $('[name="swelling"]').focus();
                                alert('อาการบวม');
                                // console.log(h_sergery);
                            } else if (skin == undefined) {

                                $('[name="skin"]').focus();
                                alert('ผิวหนัง');
                                // console.log(h_sergery);
                            } else if (communication_ears == undefined) {

                                $('[name="communication_ears"]').focus();
                                alert('การติดต่อสื่อสาร หู');
                                // console.log(h_sergery);
                            } else if (communication_ears == 'ได้ยินไม่ชัดเจน' && hearing_aid == undefined) {

                                $('[name="hearing_aid"]').focus();
                                alert('การใช้อุปกรณ์ช่วยฟัง');
                            } else if (communication_eyes == undefined) {

                                $('[name="communication_eyes"]').focus();

                                alert('การติดต่อสื่อสาร ตา');

                                // console.log(h_sergery);
                            } else if (communication_eyes == 'เห็นไม่ชัดเจน' && glasses == undefined) {

                                $('[name="glasses"]').focus();
                                alert('การสวมแว่นตา');
                            } else if (communication_speak == undefined) {

                                $('[name="communication_speak"]').focus();
                                alert('การพูด');
                            } else if (state_of_mind == '') {

                                $('[name="state_of_mind"]').focus();
                                alert('สภาพจิตใจแรกรับ');
                            } else if (first_symptoms == '') {

                                $('[name="first_symptoms"]').focus();
                                alert('อาการแรกรับ');
                            }




                            var url_update = "prs-pre-nursenote-update.php";
                            var url_save = "prs-pre-nursenote-save.php";
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
                    <script>
                        function custom_check(value) {

                            if (value == "off_depart") {
                                $('#other').attr("disabled", true).val('');
                                $('#depart3').prop("checked", false);
                            } else if (value == "on_depart") {
                                $('#other').attr("disabled", false).val('');
                                $('#depart1').prop("checked", false);
                                $('#depart2').prop("checked", false);
                            }


                            if (value == "off_hospital_by") {
                                $('#hospital_by_text').attr("disabled", true).val('');
                                $('#hospital_by4').prop("checked", false);
                            } else if (value == "on_hospital_by") {
                                $('#hospital_by_text').attr("disabled", false).val('');
                                $('#hospital_by1').prop("checked", false);
                                $('#hospital_by2').prop("checked", false);
                                $('#hospital_by3').prop("checked", false);
                            }

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

                            if (value == "off_vaccine_history") {
                                $('#vaccine_history_text').attr("disabled", true).val('');
                                $('#vaccine_history2').prop("checked", false);
                            } else if (value == "on_vaccine_history") {
                                $('#vaccine_history_text').attr("disabled", false).val('');
                                $('#vaccine_history1').prop("checked", false);
                            }

                            if (value == "off_child_devilopment") {
                                $('#child_devilopment_text').attr("disabled", true).val('');
                                $('#child_devilopment2').prop("checked", false);
                            } else if (value == "on_child_devilopment") {
                                $('#child_devilopment_text').attr("disabled", false).val('');
                                $('#child_devilopment1').prop("checked", false);
                            }

                            if (value == "off_pmh2") {
                                $('#pmh2_text').attr("disabled", true).val('');
                                $('#pmh22').prop("checked", false);
                            } else if (value == "on_pmh2") {
                                $('#pmh2_text').attr("disabled", false).val('');
                                $('#pmh21').prop("checked", false);
                            }

                            if (value == "off_history_of_drug") {
                                $('#history_of_drug_text').attr("disabled", true).val('');
                                $('#history_of_drug2').prop("checked", false);
                            } else if (value == "on_history_of_drug") {
                                $('#history_of_drug_text').attr("disabled", false).val('');
                                $('#history_of_drug1').prop("checked", false);
                            }

                            if (value == "off_breathing") {
                                $('#breathing_text').attr("disabled", true).val('');
                                $('#breathing5').prop("checked", false);
                            } else if (value == "on_breathing") {
                                $('#breathing_text').attr("disabled", false).val('');
                                $('#breathing1').prop("checked", false);
                                $('#breathing2').prop("checked", false);
                                $('#breathing3').prop("checked", false);
                                $('#breathing4').prop("checked", false);
                            }
                            if (value == "off_swelling") {
                                $('#swelling_text').attr("disabled", true).val('');
                                $('#swelling2').prop("checked", false);
                            } else if (value == "on_swelling") {
                                $('#swelling_text').attr("disabled", false).val('');
                                $('#swelling1').prop("checked", false);
                            }


                            if (value == "off_communication_speak") {
                                $('#communication_speak_text').attr("disabled", true).val('');
                                $('#communication_speak4').prop("checked", false);
                            } else if (value == "on_communication_speak") {
                                $('#communication_speak_text').attr("disabled", false).val('');
                                $('#communication_speak1').prop("checked", false);
                                $('#communication_speak2').prop("checked", false);
                                $('#communication_speak3').prop("checked", false);

                            }


                        }
                    </script>

                    <script src="../node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
                    <link rel="stylesheet" href="../node_modules/sweetalert2/dist/sweetalert2.min.css">