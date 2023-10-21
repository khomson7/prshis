<?php require_once '../include/Session.php';
// Session::::checkPermissionAndShowMessage('ADMISSION_NOTE','VIEW');
// require_once '../include/Session.php';
// Session::checkLoginSessionAndShowMessage(); //เช็ค session


// Session::checkPermissionAndShowMessage('IPD_NURSE_ADDMISSION_NOTE','VIEW');
require_once '../mains/main-report.php';
require_once '../mains/pre-opd-show-patient-main.php'; //เป็นส่วนที่แสดง ข้อมูลผู้ป่วย เช่น รูป,hn,an,ชื่อ-สกุล,แพ้ยา ฯลฯ
require_once '../mains/pre-opd-show-patient-main-sticky.php';
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';
require_once '../include/ReportQueryUtils.php';

$conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
$hn = empty($_REQUEST['hn']) ? null : $_REQUEST['hn'];
$vn = empty($_REQUEST['vn']) ? null : $_REQUEST['vn'];
$vstdate = empty($_REQUEST['order_for_date']) ? null : $_REQUEST['order_for_date'];
// $vn= '111';
$login = empty($_REQUEST['loginname']) ? null : $_REQUEST['loginname'];
//  $hn = '000000001';
// $hn = KphisQueryUtils::getHnByAn($an);
// $vn = KphisQueryUtils::getVnByAn($an);
 $vn = KphisQueryUtils::getVnByHn($hn);
//$vn = $_SESSION['vn'];
$an_parameters = ['vn' => $vn];
$hn_parameters = ['hn' => $hn];
$loginname = $_SESSION['loginname'];
$values = ['loginname' => $loginname];
//$orderdate = '2023-09-28';
$order_for_date = ReportQueryUtils::getpreOrderDate($hn);

$vn = ReportQueryUtils::getpreOrderVn($hn,$order_for_date);

//echo $order_for_date ;
//  echo  $loginname;

if ($login != $loginname) {
    session_start();
    session_destroy();
}

Session::insertSystemAccessLog(json_encode(array(
    'form'=>'PRE-DR-ADMISSION-NOTE-FORM2',
    'vn'=>$vn,
),JSON_UNESCAPED_UNICODE));


//-------------------------Doctor admission note
$sql = "SELECT *
                FROM `prs_dr_admission_note`
                WHERE an = :vn
                ORDER BY admission_note_id ASC";
$stmt = $conn->prepare($sql);
$stmt->execute(['vn' => $vn]);
if ($row  = $stmt->fetch()) {
    $admission_note_id = $row['admission_note_id'];
} else {
    $admission_note_id = null;
}

//  echo $admission_note_id;

/*
        $sql_item ="SELECT dr_adm_item.admission_note_item_id,
                    dr_adm_item.admission_note_doctor,
                    doctor.`name` AS admission_note_doctorname
                    FROM ".DbConstant::KPHIS_DBNAME.".prs_dr_admission_note_item dr_adm_item
                    LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".doctor ON doctor.code = dr_adm_item.admission_note_doctor
                    WHERE an=:vn
                    ORDER BY dr_adm_item.admission_note_item_id ASC";
        $stmt_item = $conn->prepare($sql_item);
        $stmt_item->execute($hn_parameters);
        $admission_note_count = 0;
        while ($row_item = $stmt_item->fetch()){
            $admission_note_item_id[] = $row_item['admission_note_item_id'];
            $admission_note_doctor[] = $row_item['admission_note_doctor'];
            $admission_note_doctorname[] = $row_item['admission_note_doctorname'];
            //$admission_note_doctorentryposition[] = $row_item['admission_note_doctorentryposition'];
            $admission_note_count++;
        }

*/

$sql_item = "SELECT dr_adm_item.admission_note_item_id,
            dr_adm_item.admission_note_doctor,
            doctor.`name` AS admission_note_doctorname
            FROM " . DbConstant::KPHIS_DBNAME . ".prs_dr_admission_note_item dr_adm_item
            LEFT OUTER JOIN " . DbConstant::HOSXP_DBNAME . ".doctor ON doctor.code = dr_adm_item.admission_note_doctor
            WHERE an = :vn
            ORDER BY dr_adm_item.admission_note_item_id ASC
            ";
$stmt_item = $conn->prepare($sql_item);
$stmt_item->execute(['vn' => $vn]);
$admission_note_count = 0;
while ($row_item = $stmt_item->fetch()) {
    $admission_note_item_id[] = $row_item['admission_note_item_id'];
    $admission_note_doctor[] = $row_item['admission_note_doctor'];
    $admission_note_doctorname[] = $row_item['admission_note_doctorname'];
    //$admission_note_doctorentryposition[] = $row_item['admission_note_doctorentryposition'];
    $admission_note_count++;
}

//------------------------Doctor admission note

//cc,pi
if ($admission_note_id == null || $admission_note_id != null) {
    $sql_opdscreen = "SELECT opdscreen.vn,opdscreen.hn,opdscreen.cc,opdscreen.hpi,concat(round(opdscreen.bpd,0),'/',round(opdscreen.bps,0)) as bp,
                            round(opdscreen.bps,0) as sbp,round(opdscreen.bpd,0) as dbp,
                            round(opdscreen.pulse,0) as pr,round(opdscreen.rr,0) as rr,round(opdscreen.temperature,1) as bt,
                            round(opdscreen.bw,1) as bw,round(opdscreen.height,1) as height,
                            opdscreen.pe_ga_text, opdscreen.pe_heent_text,opdscreen.hpi,
                            opdscreen.pmh,opdscreen.fh,opdscreen.pe,
                            opdscreen.pe_heart_text, opdscreen.pe_lung_text,
                            opdscreen.pe_ab_text, opdscreen.pe_neuro_text,
                            opdscreen.pe_ext_text, opdscreen.pe, pt.cid, pt.passport_no, pt.hn,pt.pname,pt.fname,pt.lname,
                            vn.age_y,vn.age_m,vn.age_d
                            FROM " . DbConstant::HOSXP_DBNAME . ".opdscreen
                            INNER JOIN " . DbConstant::HOSXP_DBNAME . ".vn_stat vn on vn.vn = opdscreen.vn
                            INNER JOIN " . DbConstant::HOSXP_DBNAME . ".patient pt on pt.hn = opdscreen.hn
                            WHERE opdscreen.vn= :vn ";
    $stmt_opdscreen = $conn->prepare($sql_opdscreen);
    $stmt_opdscreen->execute(['vn' => $vn]);
    $row_opdscreen  = $stmt_opdscreen->fetch();
}

//ipt
if ($admission_note_id == null) {
    $sql_ipt1 = "SELECT ipt.hn,ipt.regdate,ipt.regtime
                            FROM " . DbConstant::HOSXP_DBNAME . ".ipt
                            WHERE ipt.vn= :vn ";
    $stmt_ipt1 = $conn->prepare($sql_ipt1);
    $stmt_ipt1->execute(['vn' => $vn]);
    $row_ipt1  = $stmt_ipt1->fetch();
}


$sql_opduser = "SELECT opduser.entryposition,opduser.name
                        FROM " . DbConstant::HOSXP_DBNAME . ".opduser
                        WHERE loginname = :loginname";
$stmt_opduser = $conn->prepare($sql_opduser);
$stmt_opduser->execute($values);
$row_opduser  = $stmt_opduser->fetch();


//ipt ล่าสุดก่อน an ปัจจุบัน
$hnan_para = ['vn' => $vn, 'hn' => $hn];
$sql_ipt = "SELECT concat(ipt.regdate,' ',ipt.regtime) as old_regdatetime
                    FROM  " . DbConstant::HOSXP_DBNAME . ".ipt
                    where ipt.hn = :hn and ipt.vn < :vn
                    ORDER BY ipt.an DESC limit 1";
$stmt_old_ipt = $conn->prepare($sql_ipt);
$stmt_old_ipt->execute($hnan_para);
$row_old_ipt = $stmt_old_ipt->fetch();

$reg_parameters = ['hn' => $hn, 'regdatetime' => $regdatetime, 'hospital_name' => DbConstant::HOSPITAL_NAME];
$sql_ol = "SELECT CONCAT(ifnull(operation_list.enter_date,''),', ',ifnull(operation_list.operation_name,''),', ',ifnull(doctor.name,''),', ',:hospital_name) AS operation_list
                    FROM " . DbConstant::HOSXP_DBNAME . ".operation_list
                    left outer join " . DbConstant::HOSXP_DBNAME . ".doctor on doctor.code = operation_list.request_doctor
                    WHERE operation_list.hn= :hn
                    AND operation_list.status_id = 3
                    AND concat(operation_list.enter_date,' ',operation_list.enter_time) < :regdatetime
                    ORDER BY operation_list.enter_date,operation_list.enter_time";
$stmt_ol = $conn->prepare($sql_ol);
$stmt_ol->execute($reg_parameters);
$rows_ol  = $stmt_ol->fetchAll();
$operation_text = "";
foreach ($rows_ol as $row_ol) :
    $operation_text .= '<label>' . htmlspecialchars($row_ol["operation_list"]) . '</label><br>';
endforeach;


//ipd_nurse_addmission_note เรื่อง "ประจำเดือน","อาชีพ","สารเสพติด"
$sql_period =  "SELECT period, period_normal, period_disorders,period_lmp, period_menopause,
                        occupation,
                        no_risk,
                        smoking, smoke_year, smoke_frequency, smoke_stopped,
                        alcohol, alc_year, alc_frequency, alc_stopped,
                        medication_used, med_name, med_year, med_frequency, med_stopped
                        FROM " . DbConstant::KPHIS_DBNAME . ".ipd_nurse_admission_note
                        WHERE an=:an";
$stmt_period = $conn->prepare($sql_period);
$stmt_period->execute(['an' => $an]);
$row_period  = $stmt_period->fetch();
?>
<script src="../include/fabric.js"></script>
<style type="text/css">
    #show_img_select {
        border: 2px dotted black;
        /*แก้ไข*/
        background-image: url("../include/images/allbody.jpg");
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
        position: relative;
        width: 700px;
        height: 200px;
    }

    #show_img_select1 {
        border: 2px dotted black;
        /*แก้ไข*/
        background-image: url("../include/images/ent1.jpg");
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
        position: relative;
        width: 700px;
        height: 200px;
    }

    #show_img_select2 {
        border: 2px dotted black;
        /*แก้ไข*/
        background-image: url("../include/images/ent2.jpg");
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
        position: relative;
        width: 700px;
        height: 200px;
    }

    #show_img_select3 {
        border: 2px dotted black;
        /*แก้ไข*/
        background-image: url("../include/images/ent3.jpg");
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
        position: relative;
        width: 700px;
        height: 200px;
    }

    #show_img_select4 {
        border: 2px dotted black;
        /*แก้ไข*/
        background-image: url("../include/images/ent4.jpg");
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
        position: relative;
        width: 700px;
        height: 200px;
    }

    #show_img_select5 {
        border: 2px dotted black;
        /*แก้ไข*/
        background-image: url("../include/images/ent5.jpg");
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
        position: relative;
        width: 700px;
        height: 200px;
    }

    canvas {
        padding-left: 0;
        padding-right: 0;
        margin-left: auto;
        margin-right: auto;
        display: block;
    }
</style>
<form id="admit_firsth" action="" method="post" enctype="multipart/form-data">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-1">
                <button type="button" class="btn btn-danger btn-block" onclick="self.close()"><i class="fa fa-window-close"></i> ปิด</button>
            </div>
            <div class="col-md-11">
                <h4>แบบบันทึกประวัติและตรวจร่างกายผู้ป่วยแรกรับ(ER Form) <?= htmlspecialchars(DbConstant::HOSPITAL_NAME) ?></h4>
            </div>
        </div>
        <p></p>
        <div class="card-group pb-3 ">
            <div class="card">

                <div class="card-body" style=" overflow-y: auto;">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group row">
                                <label class="col-sm-12"><B>History</B></label>
                            </div>


                            <div class="form-group row">
                                <label class="col-sm-1"><B> CC: </B></label>
                                <div class="col-sm-11">
                                    <textarea class="form-control" id="chief_complaints" name="chief_complaints" rows="3"><?= (isset($row_opdscreen['cc']) && $admission_note_id == null ? htmlspecialchars($row_opdscreen['cc']) : htmlspecialchars($row['chief_complaints'])) ?></textarea>
                                </div>
                            </div>


                            <div class="form-group row">
                                <label class="col-sm-1"><B> PI: </B></label>
                                <div class="col-sm-11">
                                    <textarea class="form-control" id="medical_history" name="medical_history" rows="3"><?= (isset($row_opdscreen['hpi']) && $admission_note_id == null ? htmlspecialchars($row_opdscreen['hpi']) : htmlspecialchars($row['medical_history'])) ?></textarea>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-12"><B>การรักษาที่ได้รับมาก่อน</B></label>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-1"></div>
                                <div class="custom-control custom-radio col-sm-9">
                                    <input type="radio" <?php if (
                                                            $row['req_hospital'] == 'ไม่ได้รับการรักษาจากที่ใด'
                                                            || $row['req_hospital'] == NULL
                                                        ) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="req_hospital1" name="req_hospital" value="ไม่ได้รับการรักษาจากที่ใด" onchange="req_hospital_check('off_checked');">
                                    <label class="custom-control-label" for="req_hospital1">ไม่ได้รับการรักษาจากที่ใด</label>
                                </div>
                                <div class="col-sm-1"></div>
                                <div class="custom-control custom-radio col-sm-3">
                                    <input type="radio" <?php if (
                                                            $row['req_hospital'] != 'ไม่ได้รับการรักษาจากที่ใด'
                                                            && $row['req_hospital'] != NULL
                                                        ) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="req_hospital2" name="req_hospital" onchange="req_hospital_check('on_checked');">
                                    <label class="custom-control-label" for="req_hospital2">ได้รับการรักษา ระบุ</label>
                                </div>


                                <div class="col-sm-6">
                                    <input type="text" class="form-control form-control-sm" id="req_hospital_text" name="req_hospital" value="<?php if ($row['req_hospital'] != 'ไม่ได้รับการรักษาจากที่ใด') {
                                                                                                                                                    echo htmlspecialchars($row['req_hospital']);
                                                                                                                                                } ?>" <?php if (!($row['req_hospital'] != 'ไม่ได้รับการรักษาจากที่ใด'
                                                                                                                                                                                                                                                                    && $row['req_hospital'] != NULL)) {
                                                                                                                                                                                                                                                                    echo 'disabled';
                                                                                                                                                                                                                                                                } ?>>
                                </div>







                            </div>
                            <div class="form-group row">
                                <label class="col-sm-12"><B>Review of System</B></label>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-1"></div>
                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php if (
                                                            $row['ros'] == 'ไม่พบความผิดปกติ'
                                                            || $row['ros'] == NULL
                                                        ) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="ros1" name="ros" value="ไม่พบความผิดปกติ" onchange="ros_check('off_checked');">
                                    <label class="custom-control-label" for="ros1">ไม่พบความผิดปกติ</label>
                                </div>
                                <div class="col-sm-1"></div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if (
                                                            $row['ros'] != 'ไม่พบความผิดปกติ'
                                                            && $row['ros'] != NULL
                                                        ) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="ros2" onchange="ros_check('on_checked');">
                                    <label class="custom-control-label" for="ros2"></label>
                                </div>

                                <div class="col-sm-6">
                                    <input type="text" class="form-control form-control-sm" id="ros_text" name="ros" value="<?php if ($row['ros'] != 'ไม่พบความผิดปกติ') {
                                                                                                                                echo htmlspecialchars($row['ros']);
                                                                                                                            } ?>" <?php if (!($row['ros'] != 'ไม่พบความผิดปกติ'
                                                                                                                                                                                                                        && $row['ros'] != NULL)) {
                                                                                                                                                                                                                        echo 'disabled';
                                                                                                                                                                                                                    } ?>>
                                </div>

                            </div>

                            <label><B> ที่มาของข้อมูล: &nbsp;</B></label>

                            <div class="form-group row">
                                <div class="col-sm-1"></div>
                                <div class="custom-control custom-checkbox col-sm-2">
                                    <input type="checkbox" <?php if ($row['informant_patient'] == 'ผู้ป่วย') {
                                                                echo 'checked="checked"';
                                                            } ?> class="custom-control-input" id="e1" value="ผู้ป่วย" name="informant_patient">
                                    <label class="custom-control-label" for="e1">ผู้ป่วย</label>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-1"></div>
                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="checkbox" <?php if ($row['informant_relatives'] != null) {
                                                                echo 'checked="checked"';
                                                            } ?> class="custom-control-input" id="e2" onchange="custom_check('on_informant1');">
                                    <label class="custom-control-label" for="e2">ญาติ</label>
                                </div>
                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="e_informant1" name="informant_relatives" value="<?= (isset($row['informant_relatives']) ? htmlspecialchars($row['informant_relatives']) : '') ?>" <?php if ($row['informant_relatives'] == null) {
                                                                                                                                                                                                                                                    echo 'disabled';
                                                                                                                                                                                                                                                } ?>>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-1"></div>
                                <div class="custom-control custom-checkbox col-sm-2">
                                    <input type="checkbox" <?php if ($row['informant_deliverer'] == 'ผู้นำส่ง') {
                                                                echo 'checked="checked"';
                                                            } ?> class="custom-control-input" id="e3" value="ผู้นำส่ง" name="informant_deliverer">
                                    <label class="custom-control-label" for="e3">ผู้นำส่ง</label>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-1"></div>
                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="checkbox" <?php if ($row['informant_etc'] != null) {
                                                                echo 'checked="checked"';
                                                            } ?> class="custom-control-input" id="e4" onchange="custom_check('on_informant2');">
                                    <label class="custom-control-label" for="e4">อื่นๆ</label>
                                </div>
                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="e_informant2" name="informant_etc" value="<?= (isset($row['informant_etc']) ? htmlspecialchars($row['informant_etc']) : '') ?>" <?php if ($row['informant_etc'] == null) {
                                                                                                                                                                                                                                    echo 'disabled';
                                                                                                                                                                                                                                } ?>>
                                </div>
                            </div>





                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body" style=" overflow-y: auto;">
                    <div class="row">
                        <div class="col-md-12">

                            <div class="form-group row">
                                <label class="col-sm-1"><B> PH: </B></label>
                                <div class="col-sm-11">
                                    <textarea class="form-control" id="" name="pmh" rows="3"><?= (isset($row_opdscreen['pmh']) && $admission_note_id == null ? htmlspecialchars($row_opdscreen['pmh']) : htmlspecialchars($row['pmh'])) ?></textarea>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-1"><B> FH: </B></label>
                                <div class="col-sm-11">
                                    <textarea class="form-control" id="" name="fh" rows="3"><?= (isset($row_opdscreen['fh']) && $admission_note_id == null ? htmlspecialchars($row_opdscreen['fh']) : htmlspecialchars($row['fh'])) ?></textarea>
                                </div>
                            </div>

                            <label><B> Vaccineation: </B></label>

                            <div class="form-group row">
                                <div class="col-sm-1"></div>
                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php if (
                                                            $row['vaccineation'] == 'Complete'
                                                            || $row['vaccineation'] == NULL
                                                        ) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="vaccineation1" name="vaccineation" value="Complete" onchange="vaccineation_check('off_checked');">
                                    <label class="custom-control-label" for="vaccineation1">Complete</label>
                                </div>
                                <div class="col-sm-1"></div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if (
                                                            $row['vaccineation'] != 'Complete'
                                                            && $row['vaccineation'] != NULL
                                                        ) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="vaccineation3" name="vaccineation" onchange="vaccineation_check('on_checked');">
                                    <label class="custom-control-label" for="vaccineation3"></label>
                                </div>

                                <div class="col-sm-6">
                                    <input type="text" class="form-control form-control-sm" id="vaccineation_text" name="vaccineation" value="<?php if ($row['vaccineation'] != 'Complete') {
                                                                                                                                                    echo htmlspecialchars($row['vaccineation']);
                                                                                                                                                } ?>" <?php if (!($row['vaccineation'] != 'Complete'
                                                                                                                                                                                                                                                    && $row['vaccineation'] != NULL)) {
                                                                                                                                                                                                                                                    echo 'disabled';
                                                                                                                                                                                                                                                } ?>>
                                </div>

                            </div>

                            <label><B> Growth & Development: </B></label>

                            <div class="form-group row">
                                <div class="col-sm-1"></div>
                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php if (
                                                            $row['gd'] == 'Normal'
                                                            || $row['gd'] == NULL
                                                        ) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="gd1" name="gd" value="Normal" onchange="gd_check('off_checked');">
                                    <label class="custom-control-label" for="gd1">Normal</label>
                                </div>
                                <div class="col-sm-1"></div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if (
                                                            $row['gd'] != 'Normal'
                                                            && $row['gd'] != NULL
                                                        ) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="gd2" name="gd" onchange="gd_check('on_checked');">
                                    <label class="custom-control-label" for="gd2"></label>
                                </div>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control form-control-sm" id="gd_text" name="gd" value="<?php if ($row['gd'] != 'Normal') {
                                                                                                                                echo htmlspecialchars($row['gd']);
                                                                                                                            } ?>" <?php if (!($row['gd'] != 'Normal'
                                                                                                                                                                                                        && $row['gd'] != NULL)) {
                                                                                                                                                                                                        echo 'disabled';
                                                                                                                                                                                                    } ?>>
                                </div>

                            </div>

                            <label><B> Food / Drug allergy Hx: </B></label>

                            <div class="form-group row">
                                <div class="col-sm-1"></div>
                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php if ($row['fdh'] == 'No' || $row['fdh'] == NULL) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="fdh1" name="fdh" value="No" onchange="fdh_check('off_checked');">
                                    <label class="custom-control-label" for="fdh1">No</label>
                                </div>
                                <div class="col-sm-1"></div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['fdh'] != 'No' && $row['fdh'] != null) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="fdh2" name="fdh" onchange="fdh_check('on_checked');">
                                    <label class="custom-control-label" for="fdh2"></label>
                                </div>

                                <div class="col-sm-6">
                                    <input type="text" class="form-control form-control-sm" id="fdh_text" name="fdh" value="<?php if ($row['fdh'] != 'No') {
                                                                                                                                echo htmlspecialchars($row['fdh']);
                                                                                                                            } ?>" <?php if (!($row['fdh'] != 'No'
                                                                                                                                                                                                        && $row['fdh'] != NULL)) {
                                                                                                                                                                                                        echo 'disabled';
                                                                                                                                                                                                    } ?>>
                                </div>
                                <!--  <div class="col-sm-6">
   <textarea <?php if (
                    $row['fdh'] == 'No'
                    || $row['fdh'] == NULL
                ) {
                    echo 'disabled';
                } ?>
          class="form-control"  rows="3" id="fdh_text" name="fdh">
          <?php if ($row['fdh'] != 'No') {
                echo htmlspecialchars($row['fdh']);
            } ?></textarea>

</div> -->
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-1"></div>
                                <label><B> LMP: </B></label>
                                <div class="col-sm-4">
                                    <input type="date" class="form-control form-control-sm" id="lmp" name="lmp" value="<?= (isset($row['lmp']) ? htmlspecialchars($row['lmp']) : '') ?>">


                                </div>
                            </div>
                            <hr>


                        </div>
                    </div>
                </div>
            </div>
        </div>



        <div class="form-group row">
            <label class="col-sm-12">การเข้ารับการรักษาในโรงพยาบาล</label>
        </div>
        <div class="form-group row">
            <div class="col-sm-1"></div>
            <div class="custom-control custom-radio col-sm-2">
                <input type="radio" <?php if ($admission_note_id != null) {
                                        if ($row['inpatient_history'] == "ไม่เคย") {
                                            echo 'checked="checked"';
                                        }
                                        if (isset($row_old_ipt['old_regdatetime']) != null) {
                                            echo 'disabled="disabled"';
                                        }
                                    } else {
                                        if (!isset($row_old_ipt['old_regdatetime'])) {
                                            echo 'checked="checked"';
                                        }
                                        if (isset($row_old_ipt['old_regdatetime']) != null) {
                                            echo 'disabled="disabled"';
                                        }
                                    } ?> class="custom-control-input" id="h1" name="inpatient_history" value="ไม่เคย" onchange="custom_check('off_inpatient');">
                <label class="custom-control-label" for="h1">ไม่เคย</label>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-sm-1"></div>
            <div class="custom-control custom-radio col-sm-1">
                <input type="radio" <?php if ($admission_note_id != null) {
                                        if ($row['inpatient_history'] == "เคย") {
                                            echo 'checked="checked"';
                                        }
                                    } else {
                                        if (isset($row_old_ipt['old_regdatetime'])) {
                                            echo 'checked="checked"';
                                        }
                                    } ?> class="custom-control-input" id="h2" value="เคย" name="inpatient_history" onchange="custom_check('on_inpatient');">
                <label class="custom-control-label" for="h2">เคย</label>
            </div>
            <label class="text-right col-sm-2">ครั้งสุดท้ายเมื่อ</label>
            <div class="col-sm-4">
                <input type="text" class="form-control form-control-sm" id="h3" name="inpatient_last_date" value="<?php if ($admission_note_id != null) {
                                                                                                                        echo htmlspecialchars($row['inpatient_last_date']);
                                                                                                                    } else {
                                                                                                                        if (isset($row_old_ipt['old_regdatetime'])) {
                                                                                                                            echo htmlspecialchars($row_old_ipt['old_regdatetime']);
                                                                                                                        }
                                                                                                                    } ?>">
            </div>
        </div>
        <div class="form-group row">
            <label class="text-right col-sm-4">รพ.</label>
            <div class="col-sm-4">
                <input type="text" class="form-control form-control-sm" id="h4" name="inpatient_location" value="<?php if ($admission_note_id != null) {
                                                                                                                        echo htmlspecialchars($row['inpatient_location']);
                                                                                                                    } else {
                                                                                                                        if (isset($row_old_ipt['old_regdatetime'])) {
                                                                                                                            echo htmlspecialchars(DbConstant::HOSPITAL_NAME);
                                                                                                                        }
                                                                                                                    } ?>">
            </div>
        </div>
        <div class="form-group row">
            <label class="text-right col-sm-4">เนื่องจาก</label>
            <div class="col-sm-4">
                <input type="text" class="form-control form-control-sm" id="h5" name="inpatient_because" value="<?= (isset($row['inpatient_because']) ? htmlspecialchars($row['inpatient_because']) : '') ?>">
            </div>
        </div>






        <div class="patient-info-container alert alert-secondary" role="alert" style="z-index: 600;">
            <div class="d-flex">

                <div class="p-1 flex">

                    <label class="col-sm-12"><B>Vital Sign</B></label>

                    <div class="row">
                        <div class="col-sm-3">
                            <div class="input-group input-group-sm sm-1">
                                BP: &nbsp;<input type="text" class="form-control" id="bp" name="bp" value="<?= (isset($row_opdscreen['sbp']) ? htmlspecialchars($row_opdscreen['sbp']) . '/' : '') ?><?= (isset($row_opdscreen['dbp']) ? htmlspecialchars($row_opdscreen['dbp']) : '') ?>" aria-describedby="inputGroup-sizing-sm" readonly>
                                <div class="input-group-append">
                                    <span class="input-group-text" id="inputGroup-sizing-sm">mmHg</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="input-group input-group-sm sm-1">
                                PR: &nbsp;<input type="text" class="form-control" id="pr" name="pr" value="<?= (isset($row_opdscreen['pr']) ? htmlspecialchars($row_opdscreen['pr']) : '') ?>" aria-describedby="inputGroup-sizing-sm" readonly>
                                <div class="input-group-append">
                                    <span class="input-group-text" id="inputGroup-sizing-sm"> /min  </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-3">
                            <div class="input-group input-group-sm sm-1">
                                RR: &nbsp; <input type="text" class="form-control" id="rr" name="rr" value="<?= (isset($row_opdscreen['rr']) ? htmlspecialchars($row_opdscreen['rr']) : '') ?>" aria-describedby="inputGroup-sizing-sm" readonly>
                                <div class="input-group-append">
                                    <span class="input-group-text" id="inputGroup-sizing-sm"> /min  </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="input-group input-group-sm sm-1">
                                BT: &nbsp; <input type="text" class="form-control" id="t" name="t" value="<?= (isset($row_opdscreen['bt']) ? htmlspecialchars($row_opdscreen['bt']) : '') ?>" aria-describedby="inputGroup-sizing-sm" readonly>
                                <div class="input-group-append">
                                    <span class="input-group-text" id="inputGroup-sizing-sm">  &#176; C    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <br>
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="input-group input-group-sm sm-1">
                                น้ำหนัก: &nbsp; <input type="text" class="form-control" id="bw" name="bw" disabled value="<?= (isset($row_opdscreen['bw']) ? htmlspecialchars($row_opdscreen['bw']) : '') ?>" aria-describedby="inputGroup-sizing-sm" readonly>
                                <div class="input-group-append">
                                    <span class="input-group-text" id="inputGroup-sizing-sm">Kg.</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="input-group input-group-sm sm-1">
                                ส่วนสูง: &nbsp;<input type="text" class="form-control" id="height" name="height" disabled value="<?= (isset($row_opdscreen['height']) ? htmlspecialchars($row_opdscreen['height']) : '') ?>" aria-describedby="inputGroup-sizing-sm">
                                <div class="input-group-append">
                                    <span class="input-group-text" id="inputGroup-sizing-sm"> cm  </span>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div>

                    </div>
                </div>
            </div>

        </div>









        <!-- card -->
        <div class="card-group pb-3 ">
            <!-- card -->
            <div class="card">
                <!-- card -->
                <div class="card-body" style=" overflow-y: auto;">

                    <div class="alert alert-success text-center col-sm-12" role="alert">Physical examination</div>
                    <div class="form-group row">
                        <div class="col-sm-12">
                            <div class="card border-success">
                                <div class="card-body">
                                    <div class="form-group row">
                                        <div class="col-sm-10"></div>
                                        <div class="col-sm-2"><i class="fas fa-user"></i> ผู้ใหญ่       <i class="fas fa-baby"></i> เด็ก</div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="text-right col-sm-3">General</label>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="<?= (isset($row_opdscreen['pe_ga_text']) && $admission_note_id == null ? htmlspecialchars($row_opdscreen['pe_ga_text']) : htmlspecialchars($row['pe_general'])) ?>" id="pe_general" name="pe_general">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_general','Good consciousness')"><i class="fas fa-user"></i> Normal</button>
                                            <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_general','Active crying')"><i class="fas fa-baby"></i> Normal</button>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="text-right col-sm-3">Skin</label>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="<?= (isset($row['pe_skin']) ? htmlspecialchars($row['pe_skin']) : '') ?>" id="pe_skin" name="pe_skin">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_skin','no rash, not pale, no jaundice')"><i class="fas fa-user"></i> Normal</button>
                                            <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_skin','Pink, no rash')"><i class="fas fa-baby"></i> Normal</button>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="text-right col-sm-3">HEENT</label>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="<?= (isset($row_opdscreen['pe_heent_text']) && $admission_note_id == null ? htmlspecialchars($row_opdscreen['pe_heent_text']) : htmlspecialchars($row['pe_heent'])) ?>" id="pe_heent" name="pe_heent">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_heent','not pale conjunctiva, no icteric sclera')"><i class="fas fa-user"></i> Normal</button>
                                            <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_heent','AF 2*2 cm, no cephalhematoma')"><i class="fas fa-baby"></i> Normal</button>
                                        </div>
                                    </div>

                                    <!--    <div class="form-group row">
                            <label class="text-right col-sm-3">Neck</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="" id="pe_neck" name="pe_neck">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_neck','no mass, no LN enlargement, full ROM')"><i class="fas fa-user"></i> Normal</button>
                                <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_neck','No webbed neck')"><i class="fas fa-baby"></i> Normal</button>
                            </div>
                        </div> -->
                                    <!--
                        <div class="form-group row">
                            <label class="text-right col-sm-3">Breast & Thorax</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="" id="pe_breastthorax" name="pe_breastthorax">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_breastthorax','no mass')"><i class="fas fa-user"></i> Normal</button>
                                <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_breastthorax','Normal chest contour')"><i class="fas fa-baby"></i> Normal</button>
                            </div>
                        </div>
                                            -->
                                    <!--
                        <div class="form-group row">
                            <label class="text-right col-sm-3">Heart</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="" id="pe_heart" name="pe_heart">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_heart','normal S1 S2, no murmur')"><i class="fas fa-user"></i> Normal</button>
                                <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_heart','No murmur')"><i class="fas fa-baby"></i> Normal</button>
                            </div>
                        </div>
                                            -->
                                    <div class="form-group row">
                                        <label class="text-right col-sm-3">Lungs</label>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="<?= (isset($row_opdscreen['pe_lung_text']) && $admission_note_id == null ? htmlspecialchars($row_opdscreen['pe_lung_text']) : htmlspecialchars($row['pe_lungs'])) ?>" id="pe_lungs" name="pe_lungs">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_lungs','equal breath sounds both lung, clear, no wheezing')"><i class="fas fa-user"></i> Normal</button>
                                            <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_lungs','No adventitious sound, no subcostal retraction')"><i class="fas fa-baby"></i> Normal</button>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="text-right col-sm-3">CVS</label>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="<?= (isset($row['pe_cvs']) ? htmlspecialchars($row['pe_cvs']) : '') ?>" id="pe_cvs" name="pe_cvs">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_cvs','Regular rhythm Normal s1 s2 no murmur Normal PMI')"><i class="fas fa-user"></i> Normal</button>
                                            <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_cvs','No hepatosplenomegaly')"><i class="fas fa-baby"></i> Normal</button>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="text-right col-sm-3">Abdomen</label>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="<?= (isset($row_opdscreen['pe_ab_text']) && $admission_note_id == null ? htmlspecialchars($row_opdscreen['pe_ab_text']) : htmlspecialchars($row['pe_abdomen'])) ?>" id="pe_abdomen" name="pe_abdomen">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_abdomen','soft, not tender,  no mass palpation')"><i class="fas fa-user"></i> Normal</button>
                                            <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_abdomen','No hepatosplenomegaly')"><i class="fas fa-baby"></i> Normal</button>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="text-right col-sm-3">Rectal&Genitalia</label>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="<?= (isset($row['pe_rectalgenitalia']) ? htmlspecialchars($row['pe_rectalgenitalia']) : '') ?>" id="pe_rectalgenitalia" name="pe_rectalgenitalia">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_rectalgenitalia','PR no mass , Good sphincter tone , normal genital appearance')"><i class="fas fa-user"></i> Normal</button>
                                            <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_rectalgenitalia','Patent anus, no ambiguous genitalia')"><i class="fas fa-baby"></i> Normal</button>
                                        </div>
                                    </div>


                                    <div class="form-group row">
                                        <label class="text-right col-sm-3">Extremities</label>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="<?= (isset($row_opdscreen['pe_ext_text']) && $admission_note_id == null ? htmlspecialchars($row_opdscreen['pe_ext_text']) : htmlspecialchars($row['pe_extremities'])) ?>" id="pe_extremities" name="pe_extremities">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_extremities','No deformity No edema')"><i class="fas fa-user"></i> Normal</button>
                                            <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_extremities','No deformity')"><i class="fas fa-baby"></i> Normal</button>
                                        </div>
                                    </div>


                                    <div class="form-group row">
                                        <label class="text-right col-sm-3">CNS</label>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="<?= (isset($row['pe_cns']) ? htmlspecialchars($row['pe_cns']) : '') ?>" id="pe_cns" name="pe_cns">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_cns','E4V5N6 Pupil 3 mm RTLBE Motor and Sensory grossly intact')"><i class="fas fa-user"></i> Normal</button>
                                            <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_cns','No hepatosplenomegaly')"><i class="fas fa-baby"></i> Normal</button>
                                        </div>
                                    </div>
<!--
                                    <div class="form-group row">
                                        <label class="text-right col-sm-3">Neurological</label>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="" id="pe_neurological" name="pe_neurological">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_neurological','E4V5M6, Grade V all extremities')"><i class="fas fa-user"></i> Normal</button>
                                            <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_neurological','Moro reflex positive')"><i class="fas fa-baby"></i> Normal</button>
                                        </div>
                                    </div>
                                                                                                                -->
                                    <div class="form-group row">
                                        <label class="text-right col-sm-3">OB/Gyn exam</label>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="<?= (isset($row['pe_ob_gynexam']) ? htmlspecialchars($row['pe_ob_gynexam']) : '') ?>" id="pe_ob_gynexam" name="pe_ob_gynexam">
                                        </div>
                                        <div class="col-md-1">
                                            <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_ob_gynexam','no mass , no discharge')"><i class="fas fa-user"></i> Normal</button>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="text-right col-sm-3">Other</label>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="<?= (isset($row['pe_other']) ? htmlspecialchars($row['pe_other']) : '') ?>" id="" name="pe_other">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="text-right col-sm-3">PE Text</label>
                                        <div class="col-sm-7">
                                            <textarea class="form-control" id="pe_text" name="pe_text" rows="6"><?= (isset($row_opdscreen['pe']) && $admission_note_id == null ? htmlspecialchars($row_opdscreen['pe']) : htmlspecialchars($row['pe_text'])) ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center">
        
        <h3>ENT Examination</h3>
        
        </div>



        <!--Ear and Hearing< -->
        <div class="form-group row">
            <div class="col-sm-12">
                <div class="card border-success">
                    <div class="card-body">
                        <h5 class="card-title"></h5>
                        <div class="text-left">
<label>1. Ear and Hearing</label>
        </div>
                        <!-- <div class="text-right">
                            <button type="button" class="btn btn-outline-success" onclick="onclick_img_button()"><i class='fas fa-edit'></i> แก้ไขรูปภาพ</button>
                        </div> -->
                     
                        <div class="col-sm-8 offset-sm-3 mb-3">
                            <div id="show_img_select1"  class="text-center">
                                <canvas id="c1" width="700" height="190"></canvas>
                            </div>
                        </div>
                        <div class="col-sm-11 offset-sm-1" style="display: inline-block;">
                            <div class="text-center">
                            <button id="" type="button" class="btn btn-outline-danger PhysicalExaminationBtn" onclick="Draw()"><i class='fas fa-edit'></i> วาด</button>
                            <button id="" type="button" class="btn btn-outline-danger PhysicalExaminationBtn" onclick="Addtext()"><i class='fas fa-edit'></i> เพิ่มข้อความ</button>
                                <a href="javascript:undo1();"><button id="" type="button" class="btn btn-outline-danger PhysicalExaminationBtn"><i class="far fa-arrow-alt-circle-left"></i> Undo</button></a>
                                <a href="javascript:redo1();"><button id="" type="button" class="btn btn-outline-danger PhysicalExaminationBtn">Redo <i class="far fa-arrow-alt-circle-right"></i></button></a>
                                <button id="clear-canvas" type="button" class="btn btn-outline-danger PhysicalExaminationBtn" onclick="onclick_clear1()"><i class='fas fa-edit'></i> Clear</button>
                            </div>
                        </div>
                        <div>
                            <div class="form-group">
                                <label for=""></label>
                                <textarea style="display:none;" class="form-control" name="svg_tag1" id="svg_tag1" rows="10"><?=(isset($row['svg_tag1']) ? htmlspecialchars($row['svg_tag1']) : '')?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--Ear and Hearing< -->
        <div class="form-group row">
            <div class="col-sm-12">
                <div class="card border-success">
                    <div class="card-body">
                        <h5 class="card-title"></h5>
                        <div class="text-left">
<label>2. Nose and Nasopharynx</label>
        </div>
                        <!-- <div class="text-right">
                            <button type="button" class="btn btn-outline-success" onclick="onclick_img_button()"><i class='fas fa-edit'></i> แก้ไขรูปภาพ</button>
                        </div> -->
                        <div class="col-sm-8 offset-sm-3 mb-3">
                            <div id="show_img_select2"  class="text-center">
                                <canvas id="c2" width="700" height="180"></canvas>
                            </div>
                        </div>
                        <div class="col-sm-11 offset-sm-1" style="display: inline-block;">
                            <div class="text-center">
                            <button id="" type="button" class="btn btn-outline-danger PhysicalExaminationBtn" onclick="Draw2()"><i class='fas fa-edit'></i> วาด</button>
                            <button id="" type="button" class="btn btn-outline-danger PhysicalExaminationBtn" onclick="Addtext2()"><i class='fas fa-edit'></i> เพิ่มข้อความ</button>
                                <a href="javascript:undo2();"><button id="" type="button" class="btn btn-outline-danger PhysicalExaminationBtn"><i class="far fa-arrow-alt-circle-left"></i> Undo</button></a>
                                <a href="javascript:redo2();"><button id="" type="button" class="btn btn-outline-danger PhysicalExaminationBtn">Redo <i class="far fa-arrow-alt-circle-right"></i></button></a>
                                <button id="clear-canvas" type="button" class="btn btn-outline-danger PhysicalExaminationBtn" onclick="onclick_clear2()"><i class='fas fa-edit'></i> Clear</button>
                            </div>
                        </div>
                        <div>
                            <div class="form-group">
                                <label for=""></label>
                                <textarea style="display:none;" class="form-control" name="svg_tag2" id="svg_tag2" rows="10"><?=(isset($row['svg_tag2']) ? htmlspecialchars($row['svg_tag2']) : '')?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

            <!--3< -->
            <div class="form-group row">
            <div class="col-sm-12">
                <div class="card border-success">
                    <div class="card-body">
                        <h5 class="card-title"></h5>
                        <div class="text-left">
<label>3. Oral Cavity and Oropharynx</label>
        </div>
                        <!-- <div class="text-right">
                            <button type="button" class="btn btn-outline-success" onclick="onclick_img_button()"><i class='fas fa-edit'></i> แก้ไขรูปภาพ</button>
                        </div> -->
                        <div class="col-sm-8 offset-sm-3 mb-3">
                            <div id="show_img_select3"  class="text-center">
                                <canvas id="c3" width="700" height="180"></canvas>
                            </div>
                        </div>
                        <div class="col-sm-11 offset-sm-1" style="display: inline-block;">
                            <div class="text-center">
                            <button id="" type="button" class="btn btn-outline-danger PhysicalExaminationBtn" onclick="Draw3()"><i class='fas fa-edit'></i> วาด</button>
                            <button id="" type="button" class="btn btn-outline-danger PhysicalExaminationBtn" onclick="Addtext3()"><i class='fas fa-edit'></i> เพิ่มข้อความ</button>
                                <a href="javascript:undo3();"><button id="" type="button" class="btn btn-outline-danger PhysicalExaminationBtn"><i class="far fa-arrow-alt-circle-left"></i> Undo</button></a>
                                <a href="javascript:redo3();"><button id="" type="button" class="btn btn-outline-danger PhysicalExaminationBtn">Redo <i class="far fa-arrow-alt-circle-right"></i></button></a>
                                <button id="clear-canvas" type="button" class="btn btn-outline-danger PhysicalExaminationBtn" onclick="onclick_clear3()"><i class='fas fa-edit'></i> Clear</button>
                            </div>
                        </div>
                        <div>
                            <div class="form-group">
                                <label for=""></label>
                                <textarea style="display:none;" class="form-control" name="svg_tag3" id="svg_tag3" rows="10"><?=(isset($row['svg_tag3']) ? htmlspecialchars($row['svg_tag3']) : '')?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--4 -->
        <div class="form-group row">
            <div class="col-sm-12">
                <div class="card border-success">
                    <div class="card-body">
                        <h5 class="card-title"></h5>
                        <div class="text-left">
<label>4. Larynx and Hypopharynx</label>
        </div>
                        <!-- <div class="text-right">
                            <button type="button" class="btn btn-outline-success" onclick="onclick_img_button()"><i class='fas fa-edit'></i> แก้ไขรูปภาพ</button>
                        </div> -->
                        <div class="col-sm-8 offset-sm-3 mb-3">
                            <div id="show_img_select4"  class="text-center">
                                <canvas id="c4" width="700" height="180"></canvas>
                            </div>
                        </div>
                        <div class="col-sm-11 offset-sm-1" style="display: inline-block;">
                            <div class="text-center">
                            <button id="" type="button" class="btn btn-outline-danger PhysicalExaminationBtn" onclick="Draw4()"><i class='fas fa-edit'></i> วาด</button>
                            <button id="" type="button" class="btn btn-outline-danger PhysicalExaminationBtn" onclick="Addtext4()"><i class='fas fa-edit'></i> เพิ่มข้อความ</button>
                                <a href="javascript:undo4();"><button id="" type="button" class="btn btn-outline-danger PhysicalExaminationBtn"><i class="far fa-arrow-alt-circle-left"></i> Undo</button></a>
                                <a href="javascript:redo4();"><button id="" type="button" class="btn btn-outline-danger PhysicalExaminationBtn">Redo <i class="far fa-arrow-alt-circle-right"></i></button></a>
                                <button id="clear-canvas" type="button" class="btn btn-outline-danger PhysicalExaminationBtn" onclick="onclick_clear4()"><i class='fas fa-edit'></i> Clear</button>
                            </div>
                        </div>
                        <div>
                            <div class="form-group">
                                <label for=""></label>
                                <textarea style="display:none;" class="form-control" name="svg_tag4" id="svg_tag4" rows="10"><?=(isset($row['svg_tag4']) ? htmlspecialchars($row['svg_tag4']) : '')?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

          <!--5 -->
          <div class="form-group row">
            <div class="col-sm-12">
                <div class="card border-success">
                    <div class="card-body">
                        <h5 class="card-title"></h5>
                        <div class="text-left">
<label>5. Face and Neck</label>
        </div>
                        <!-- <div class="text-right">
                            <button type="button" class="btn btn-outline-success" onclick="onclick_img_button()"><i class='fas fa-edit'></i> แก้ไขรูปภาพ</button>
                        </div> -->
                        <div class="col-sm-8 offset-sm-3 mb-3">
                            <div id="show_img_select5"  class="text-center">
                                <canvas id="c5" width="700" height="180"></canvas>
                            </div>
                        </div> 
                        <div class="col-sm-11 offset-sm-1" style="display: inline-block;">
                            <div class="text-center">
                            <button id="" type="button" class="btn btn-outline-danger PhysicalExaminationBtn" onclick="Draw5()"><i class='fas fa-edit'></i> วาด</button>
                            <button id="" type="button" class="btn btn-outline-danger PhysicalExaminationBtn" onclick="Addtext5()"><i class='fas fa-edit'></i> เพิ่มข้อความ</button>
                                <a href="javascript:undo5();"><button id="" type="button" class="btn btn-outline-danger PhysicalExaminationBtn"><i class="far fa-arrow-alt-circle-left"></i> Undo</button></a>
                                <a href="javascript:redo5();"><button id="" type="button" class="btn btn-outline-danger PhysicalExaminationBtn">Redo <i class="far fa-arrow-alt-circle-right"></i></button></a>
                                <button id="clear-canvas" type="button" class="btn btn-outline-danger PhysicalExaminationBtn" onclick="onclick_clear5()"><i class='fas fa-edit'></i> Clear</button>
                            </div>
                        </div>
                        <div>
                            <div class="form-group">
                                <label for=""></label>
                                <textarea style="display:none;" class="form-control" name="svg_tag5" id="svg_tag5" rows="10"><?=(isset($row['svg_tag5']) ? htmlspecialchars($row['svg_tag5']) : '')?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

  

                    <div class="form-group row">
                        <div class="col-sm-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="form-group row">
                                        <div class="col-sm-9">
                                            <div class="form-group row">
                                                <label class="text-right col-sm-3">Problemlist</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="<?= (isset($row['problem_list']) ? htmlspecialchars($row['problem_list']) : '') ?>" id="" name="problem_list">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="text-right col-sm-3">Impression</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="<?= (isset($row['impression']) ? htmlspecialchars($row['impression']) : '') ?>" id="" name="impression">
                                                </div>
                                            </div>
                                            <!--
                                            <div class="form-group row">
                                                <label class="text-right col-sm-3">Diff. Dx</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="" id="" name="diff_dx">
                                                </div>
                                            </div>
                                                                                                                -->
                                            <div class="form-group row">
                                                <label class="text-right col-sm-3">Plan Management</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="<?= (isset($row['plan_management']) ? htmlspecialchars($row['plan_management']) : '') ?>" id="" name="plan_management">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>





                    <!--    <div class="patient-info-container alert alert-secondary" role="alert">
            <div class="d-flex">

                <div class="p-1 flex">

<label>ชื่อ - สกุล : <?= htmlspecialchars($row_opdscreen['pname'] . $row_opdscreen['fname'] . " " . $row_opdscreen['lname']) ?> | </label>
<label>อายุ : <?= htmlspecialchars($row_opdscreen['age_y'] . " ปี " . $row_opdscreen['age_m'] . " เดือน " . $row_opdscreen['age_d'] . " วัน ") ?> | </label>
<label>HN : <?= htmlspecialchars($row_opdscreen['hn']); ?> | VN : <?= htmlspecialchars($row_opdscreen['vn']) ?></label>

                    </div>
                  </div>
                </div> -->



                    <div class="form-row ">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="action-person-dr-admission">ลงชื่อแพทย์</label>
                                <button type="button" class="btn btn-secondary btn-sm mb-2t" onclick="AddDoctorSignature()"><i class="fas fa-plus"></i> ลงชื่อ</button>
                                <div id="dr-admission-group-input-div">
                                    <template id="template_dr_admission_input_div">
                                        <div class="dr_admission_input_div">
                                            <div class="input-group mb-2">
                                                <input type="hidden" class="form-control form-control" name="admission_note_doctor[]">
                                                <input type="text" class="form-control form-control" name="doc_name[]" readonly>
                                                <!-- <input type="text" class="form-control form-control" name="doc_pos[]" readonly> -->
                                            </div>
                                        </div>
                                    </template>
                                    <?php $start_count = 0;
                                    while ($start_count < $admission_note_count) { ?>
                                        <div class="dr_admission_input_div">
                                            <div class="input-group mb-2">
                                                <input type="hidden" class="form-control form-control" name="admission_note_doctor[]" value="<?= $admission_note_doctor[$start_count] ?>">
                                                <input type="text" class="form-control form-control" name="doc_name[]" value="<?= $admission_note_doctorname[$start_count] ?>" readonly>
                                                <!-- <input type="text" class="form-control form-control" name="doc_pos[]"  value="<?= $admission_note_doctorentryposition[$start_count] ?>" readonly> -->
                                            </div>
                                        </div>
                                    <?php $start_count++;
                                    } ?>
                                </div>
                            </div>
                        </div>

                        <!--  <div class="col-md-4">
                <div class="form-group">
                    <label class="mb-3" for="action-person-nurse">ลงชื่อพยาบาล</label>
                    <div class="input-group">
                        <input type="text" class="form-control form-control" id="nurse_name"  name="nurse_name"  value="<?= htmlspecialchars($row['nurse_name']) ?>" readonly>
                        <input type="text" class="form-control form-control" id="nurse_pos"   name="nurse_pos"   value="<?= htmlspecialchars($row['nurse_pos']) ?>" readonly>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-secondary" onclick="PersonAsCurrentUser_1()">ลงชื่อ</button>
                        </div>
                    </div>
                </div>
            </div> -->

                    </div>
                    <div class="form-group row">
                        <!-- <div class="col-sm-4 text-left">
                <label class="text-right"><?= (isset($row['doc_name']) ? 'แพทย์ผู้บันทึก   ' . htmlspecialchars($row['doc_name']) : '') ?></label><br>
                <label class="text-right"> <?= (isset($row['doc_pos']) ? htmlspecialchars($row['doc_pos']) : '') ?></label>
            </div>
            <div class="col-sm-4 text-left">
                <label class="text-right"><?= (isset($row['nurse_name']) ? 'พยาบาลผู้บันทึก  ' . htmlspecialchars($row['nurse_name']) : '') ?></label><br>
                <label class="text-right"> <?= (isset($row['nurse_pos']) ? htmlspecialchars($row['nurse_pos']) : '') ?></label>
            </div> -->
                        <div class="col-sm-12 text-right">

                            <!-- ปรับแก้-->
                            <?php
                            if (Session::checkPermission('ADMISSION_NOTE', 'EDIT')) {
                            ?>
                                <button type="button" class="btn btn-primary" onclick="admission_save()">บันทึก</button>
                            <?php
                            }
                            ?>
                        </div>
                    </div>
                    <!-- card -->
                </div>
                <!-- card -->
            </div>
            <!-- card -->
        </div>
        <!-- card -->
    </div>
    <div class="form-group text-center">
        <div id="show_check_save"></div>
        <input type="hidden" id="an" name="an" value="<?= htmlspecialchars($vn) ?>">
        <input type="hidden" id="hn" name="hn" value="<?= htmlspecialchars($hn) ?>">
        <input type="hidden" id="version" name="version" value="<?= htmlspecialchars($row['version']) ?>">
        <input type="hidden" id="admission_note_id" name="admission_note_id" value="<?= htmlspecialchars($row['admission_note_id']) ?>">
        <input type="hidden" id="create_user" name="create_user" value="<?= htmlspecialchars($_SESSION['name']) ?>">
        <!-- <input type="hidden" id="nurse_name"  name="nurse_name"  value="<?= (htmlspecialchars($_SESSION['groupname']) == 'พยาบาล' ? htmlspecialchars($_SESSION['name']) : '') ?>">
    <input type="hidden" id="nurse_pos"   name="nurse_pos"   value="<?= (htmlspecialchars($_SESSION['groupname']) == 'พยาบาล' ? htmlspecialchars($row_opduser['entryposition']) : '') ?>">
    <input type="hidden" id="doc_name"    name="doc_name"    value="<?= (htmlspecialchars($_SESSION['groupname']) == 'แพทย์' ? htmlspecialchars($_SESSION['name']) : '') ?>">
    <input type="hidden" id="doc_pos"     name="doc_pos"     value="<?= (htmlspecialchars($_SESSION['groupname']) == 'แพทย์' ? htmlspecialchars($row_opduser['entryposition']) : '') ?>"> -->
    </div>
</form>
<script>
    function AddDoctorSignature() {
        const doc_name = <?= json_encode($_SESSION['name']) ?>;
        const doc_entryposition = <?= json_encode($_SESSION['entryposition']) ?>;
        const doctorcode = <?= json_encode($_SESSION['doctorcode']) ?>;
        const clone_template_dr_admission_input_div = document.querySelector('#template_dr_admission_input_div').content.cloneNode(true);
        if (CheckDoctorSignature()) {
            $('#dr-admission-group-input-div').append(clone_template_dr_admission_input_div);
            $('[name="admission_note_doctor[]"].last-focus-input').removeClass('last-focus-input');
            $('[name="doc_name[]"].last-focus-input').removeClass('last-focus-input');
            // $('[name="doc_pos[]"].last-focus-input').removeClass('last-focus-input');
            $('[name="admission_note_doctor[]"]').last().addClass('last-focus-input').val(doctorcode);
            $('[name="doc_name[]"]').last().addClass('last-focus-input').val(doc_name);
            // $('[name="doc_pos[]"]').last().addClass('last-focus-input').val(doc_entryposition);
        }
    }

    function CheckDoctorSignature() {
        const doctorcode_check = <?= json_encode($_SESSION['doctorcode']) ?>;
        let return_checkdoctorSignature = true;
        $.each($("input:hidden[name='admission_note_doctor[]']"), function(index, value) {
            //console.log({index,value})
            if (doctorcode_check == $(this).val()) {
                alert("คุณได้ลงชื่อบันทึกข้อมูลไว้แล้ว");
                return_checkdoctorSignature = false;
                return false;
            }
        });
        return return_checkdoctorSignature;
    }

    function PersonAsCurrentUser_1() {
        const nurse_name = <?= json_encode($_SESSION['name']) ?>;
        const entryposition = <?= json_encode($_SESSION['entryposition']) ?>;
        $("#nurse_name").val(nurse_name);
        $("#nurse_pos").val(entryposition);
    }
    //--------------------------------------------canvas----------------------------------------------
    /*
    var canvas = new fabric.Canvas('c');
    // var message = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent($('#svg_tag').val());
    var message = $('#svg_tag').val();
    fabric.loadSVGFromString(message, function(objects, options) {
        var loadedObjects = fabric.util.groupSVGElements(objects, options);
        loadedObjects.set('selectable', false);
        canvas.add(loadedObjects);
        canvas.renderAll();
    });

    canvas.isDrawingMode = true;
    canvas.on('object:added', function() {
        if (!isRedoing) {
            h = [];
        }
        isRedoing = false;
    });
    var isRedoing = false;
    var h = [];

    function undo() {
        if (canvas._objects.length > 0) {
            h.push(canvas._objects.pop());
            canvas.renderAll();
        }
    }

    function redo() {
        if (h.length > 0) {
            isRedoing = true;
            canvas.add(h.pop());
        }
    }

    function onclick_clear() {
        canvas.clear();
    } */
    //--------------------------------------------canvas----------------------------------------------

    //--------------------------------------------canvas----------------------------------------------
 var canvas1 = new fabric.Canvas('c1');
    // var message = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent($('#svg_tag').val());
    var message = $('#svg_tag1').val();
    fabric.loadSVGFromString(message,function(objects,options) {
        var loadedObjects = fabric.util.groupSVGElements(objects, options);
        loadedObjects.set('selectable', false);
        canvas1.add(loadedObjects);
        canvas1.renderAll();
    });

        // Add text
function Addtext() {
  var text = new fabric.IText("Tap & type", {
    fontSize: 15,
    top: 10,
    left: 10,
    width: 200,
    height: 200,
    textAlign: "center"
  });
  canvas1.add(text);
  canvas1.setActiveObject(text);
  text.enterEditing();
  text.selectAll();
  text.renderCursorOrSelection();  // or canvas.renderAll();
  canvas1.isDrawingMode = false;
}

function Draw() {
  canvas1.isDrawingMode = true;
}

    canvas1.isDrawingMode = true;
   // canvas1.freeDrawingBrush.width = 25;
    canvas1.freeDrawingBrush.color = "rgb(255, 0, 0)";
    canvas1.on('object:added',function(){
        if(!isRedoing){
            h = [];
        }
        isRedoing = false;
    });
    var isRedoing = false;
    var h = [];
    function undo1(){
        if(canvas1._objects.length>0){
            h.push(canvas1._objects.pop());
            canvas1.renderAll();
        }
    }
    function redo1(){
        if(h.length>0){
            isRedoing = true;
            canvas1.add(h.pop());
        }
    }
    function onclick_clear1(){
        canvas1.clear();
    }
    //--------------------------------------------canvas----------------------------------------------

    //--------------------------------------------canvas----------------------------------------------
 var canvas2 = new fabric.Canvas('c2');
    // var message = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent($('#svg_tag').val());
    var message = $('#svg_tag2').val();
    fabric.loadSVGFromString(message,function(objects,options) {
        var loadedObjects = fabric.util.groupSVGElements(objects, options);
        loadedObjects.set('selectable', false);
        canvas2.add(loadedObjects);
        canvas2.renderAll();
    });

            // Add text
            function Addtext2() {
  var text = new fabric.IText("Tap & type", {
    fontSize: 15,
    top: 10,
    left: 10,
    width: 200,
    height: 200,
    textAlign: "center"
  });
  canvas2.add(text);
  canvas2.setActiveObject(text);
  text.enterEditing();
  text.selectAll();
  text.renderCursorOrSelection();  // or canvas.renderAll();
  canvas2.isDrawingMode = false;
}

function Draw2() {
  canvas2.isDrawingMode = true;
}


    canvas2.isDrawingMode = true;
    canvas2.freeDrawingBrush.color = "rgb(255, 0, 0)";
    canvas2.on('object:added',function(){
        if(!isRedoing){
            h = [];
        }
        isRedoing = false;
    });
    var isRedoing = false;
    var h = [];
    function undo2(){
        if(canvas2._objects.length>0){
            h.push(canvas2._objects.pop());
            canvas2.renderAll();
        }
    }
    function redo2(){
        if(h.length>0){
            isRedoing = true;
            canvas2.add(h.pop());
        }
    }
    function onclick_clear2(){
        canvas2.clear();
    }
    //--------------------------------------------canvas----------------------------------------------

    //--------------------------------------------canvas----------------------------------------------
 var canvas3 = new fabric.Canvas('c3');
    // var message = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent($('#svg_tag').val());
    var message = $('#svg_tag3').val();
    fabric.loadSVGFromString(message,function(objects,options) {
        var loadedObjects = fabric.util.groupSVGElements(objects, options);
        loadedObjects.set('selectable', false);
        canvas3.add(loadedObjects);
        canvas3.renderAll();
    });
            // Add text
            function Addtext3() {
  var text = new fabric.IText("Tap & type", {
    fontSize: 15,
    top: 10,
    left: 10,
    width: 200,
    height: 200,
    textAlign: "center"
  });
  canvas3.add(text);
  canvas3.setActiveObject(text);
  text.enterEditing();
  text.selectAll();
  text.renderCursorOrSelection();  // or canvas.renderAll();
  canvas3.isDrawingMode = false;
}

function Draw3() {
  canvas3.isDrawingMode = true;
}


    canvas3.isDrawingMode = true;
    canvas3.freeDrawingBrush.color = "rgb(255, 0, 0)";
    canvas3.on('object:added',function(){
        if(!isRedoing){
            h = [];
        }
        isRedoing = false;
    });
    var isRedoing = false;
    var h = [];
    function undo3(){
        if(canvas3._objects.length>0){
            h.push(canvas3._objects.pop());
            canvas3.renderAll();
        }
    }
    function redo3(){
        if(h.length>0){
            isRedoing = true;
            canvas3.add(h.pop());
        }
    }
    function onclick_clear3(){
        canvas3.clear();
    }
    //--------------------------------------------canvas----------------------------------------------

    //--------------------------------------------canvas----------------------------------------------
 var canvas4 = new fabric.Canvas('c4');
    // var message = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent($('#svg_tag').val());
    var message = $('#svg_tag4').val();
    fabric.loadSVGFromString(message,function(objects,options) {
        var loadedObjects = fabric.util.groupSVGElements(objects, options);
        loadedObjects.set('selectable', false);
        canvas4.add(loadedObjects);
        canvas4.renderAll();
    });

                // Add text
function Addtext4() {
  var text = new fabric.IText("Tap & type", {
    fontSize: 15,
    top: 10,
    left: 10,
    width: 200,
    height: 200,
    textAlign: "center"
  });
  canvas4.add(text);
  canvas4.setActiveObject(text);
  text.enterEditing();
  text.selectAll();
  text.renderCursorOrSelection();  // or canvas.renderAll();
  canvas4.isDrawingMode = false;
}

function Draw4() {
  canvas4.isDrawingMode = true;
}

    canvas4.isDrawingMode = true;
    canvas4.freeDrawingBrush.color = "rgb(255, 0, 0)";
    canvas4.on('object:added',function(){
        if(!isRedoing){
            h = [];
        }
        isRedoing = false;
    });
    var isRedoing = false;
    var h = [];
    function undo4(){
        if(canvas4._objects.length>0){
            h.push(canvas4._objects.pop());
            canvas4.renderAll();
        }
    }
    function redo4(){
        if(h.length>0){
            isRedoing = true;
            canvas4.add(h.pop());
        }
    }
    function onclick_clear4(){
        canvas4.clear();
    }
    //--------------------------------------------canvas----------------------------------------------

    //--------------------------------------------canvas----------------------------------------------
 var canvas5 = new fabric.Canvas('c5');
    // var message = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent($('#svg_tag').val());
    var message = $('#svg_tag5').val();
    fabric.loadSVGFromString(message,function(objects,options) {
        var loadedObjects = fabric.util.groupSVGElements(objects, options);
        loadedObjects.set('selectable', false);
        canvas5.add(loadedObjects);
        canvas5.renderAll();
    });

                // Add text
function Addtext5() {
  var text = new fabric.IText("Tap & type", {
    fontSize: 15,
    top: 10,
    left: 10,
    width: 200,
    height: 200,
    textAlign: "center"
  });
  canvas5.add(text);
  canvas5.setActiveObject(text);
  text.enterEditing();
  text.selectAll();
  text.renderCursorOrSelection();  // or canvas.renderAll();
  canvas5.isDrawingMode = false;
}

function Draw5() {
  canvas5.isDrawingMode = true;
}

    canvas5.isDrawingMode = true;
    canvas5.freeDrawingBrush.color = "rgb(255, 0, 0)";
    canvas5.on('object:added',function(){
        if(!isRedoing){
            h = [];
        }
        isRedoing = false;
    });
    var isRedoing = false;
    var h = [];
    function undo5(){
        if(canvas5._objects.length>0){
            h.push(canvas5._objects.pop());
            canvas5.renderAll();
        }
    }
    function redo5(){
        if(h.length>0){
            isRedoing = true;
            canvas5.add(h.pop());
        }
    }
    function onclick_clear5(){
        canvas5.clear();
    }
    //--------------------------------------------canvas----------------------------------------------



    function custom_check(value) {
        if (value == "off_arrive") {
            $('#w6').attr("disabled", true).val('');
            $('#w5').prop("checked", false);
        } else if (value == "on_arrive") {
            $('#w6').attr("disabled", false).val('');
            $('#w1').prop("checked", false);
            $('#w2').prop("checked", false);
            $('#w3').prop("checked", false);
            $('#w4').prop("checked", false);
        }

        if (value == "off_entered") {
            $('#entered_hos').attr("disabled", true).val('');
            $('#entered_by3').prop("checked", false);
        } else if (value == "on_entered") {
            $('#entered_hos').attr("disabled", false).val('');
            $('#entered_by1').prop("checked", false);
            $('#entered_by2').prop("checked", false);
        }

        if (value == "on_informant1") {
            if (!($('#e2').is(':checked'))) {
                $('#e_informant1').attr("disabled", true).val('');
            } else {
                $('#e_informant1').attr("disabled", false).val('');
            }
        } else if (value == "on_informant2") {
            if (!($('#e4').is(':checked'))) {
                $('#e_informant2').attr("disabled", true).val('');
            } else {
                $('#e_informant2').attr("disabled", false).val('');
            }
        }

        if (value == "on_taken") {
            if (!($('#r4').is(':checked'))) {
                $('#r5').attr("disabled", true).val('');
            } else {
                $('#r5').attr("disabled", false).val('');
            }
        }

        if (value == "off_disease") {
            $('#tt1').attr("disabled", true).prop("checked", false);
            $('#t_text1').attr("disabled", true).val('');
            $('#tt2').attr("disabled", true).prop("checked", false);
            $('#t_text2').attr("disabled", true).val('');
            $('#tt3').attr("disabled", true).prop("checked", false);
            $('#t_text3').attr("disabled", true).val('');
        } else if (value == "on_disease") {
            $('#tt1').attr("disabled", false).val('');
            $('#t_text1').attr("disabled", false).val('');
            $('#tt2').attr("disabled", false).val('');
            $('#t_text2').attr("disabled", false).val('');
            $('#tt3').attr("disabled", false).val('');
            $('#t_text3').attr("disabled", false).val('');
        }

        if (value == "off_operation") {
            $('#y2_operation').attr("disabled", true).val('');
            $('#y2').prop("checked", false);
        } else if (value == "on_operation") {
            $('#y2_operation').attr("disabled", false).val('');
            $('#y1').prop("checked", false);
        }

        if (value == "off_allergy") {
            $('#uu1').attr("disabled", true).prop("checked", false);
            $('#uu1_in').attr("disabled", true).val('');
            $('#uu2').attr("disabled", true).prop("checked", false);
            $('#uu2_in').attr("disabled", true).val('');
            $('#uu3').attr("disabled", true).prop("checked", false);
            $('#uu3_in').attr("disabled", true).val('');
            $('#uu4_in').attr("disabled", true).val('');
        } else if (value == "on_allergy") {
            $('#uu1').attr("disabled", false).val('');
            $('#uu1_in').attr("disabled", false).val('');
            $('#uu2').attr("disabled", false).val('');
            $('#uu2_in').attr("disabled", false).val('');
            $('#uu3').attr("disabled", false).val('');
            $('#uu3_in').attr("disabled", false).val('');
            $('#uu4_in').attr("disabled", false).val('');
        }

        if (value == "off_immunisation") {
            $('#o3').attr("disabled", true).val('');
            $('#o2').prop("checked", false);
        } else if (value == "on_immunisation") {
            $('#o3').attr("disabled", false).val('');
            $('#o1').prop("checked", false);
        }

        if (value == "off_developmentally") {
            $('#p3').attr("disabled", true).val('');
            $('#p2').prop("checked", false);
        } else if (value == "on_developmentally") {
            $('#p3').attr("disabled", false).val('');
            $('#p1').prop("checked", false);
        }

        if (value == "off_deliver") {
            $('#s3').attr("disabled", true).val('');
            $('#s4').attr("disabled", true).val('');
            $('#s2').prop("checked", false);
        } else if (value == "on_deliver") {
            $('#s3').attr("disabled", false).val('');
            $('#s4').attr("disabled", false).val('');
            $('#s1').prop("checked", false);
        }

        if (value == "off_condition") {
            $('#a3').attr("disabled", true).val('');
            $('#a2').prop("checked", false);
        } else if (value == "on_condition") {
            $('#a3').attr("disabled", false).val('');
            $('#a1').prop("checked", false);
        }

        if (value == "off_family_medical") {
            $('#i3').attr("disabled", true).val('');
            $('#i2').prop("checked", false);
        } else if (value == "on_family_medical") {
            $('#i3').attr("disabled", false).val('');
            $('#i1').prop("checked", false);
        }

        if (value == "off_disease_operation_allergy") {
            $('#g2_text').attr("disabled", true).val('');
            $('#g2').prop("checked", false);
        } else if (value == "on_disease_operation_allergy") {
            $('#g2_text').attr("disabled", false).val('');
            $('#g1').prop("checked", false);
        }

        if (value == "off_inpatient") {
            $('#h3').attr("disabled", true).val('');
            $('#h4').attr("disabled", true).val('');
            $('#h5').attr("disabled", true).val('');
            $('#h2').prop("checked", false);
        } else if (value == "on_inpatient") {
            $('#h3').attr("disabled", false).val('');
            $('#h4').attr("disabled", false).val('');
            $('#h5').attr("disabled", false).val('');
            $('#h1').prop("checked", false);
        }
    }

    function plan_management_check(value) {
        if (value == "off_checked") {
            $('#plan_management_text').attr("disabled", true).val('');
            $('#plan_management4').prop("checked", false);
        } else if (value == "on_checked") {
            $('#plan_management_text').attr("disabled", false).val('');
            $('#plan_management1').prop("checked", false);
            $('#plan_management2').prop("checked", false);
            $('#plan_management3').prop("checked", false);

        }
    }

    function req_hospital_check(value) {
        if (value == "off_checked") {
            $('#req_hospital_text').attr("disabled", true).val('');
            $('#req_hospital2').prop("checked", false);
        } else if (value == "on_checked") {
            $('#req_hospital_text').attr("disabled", false).val('');
            $('#req_hospital1').prop("checked", false);
        }
    }

    function ros_check(value) {
        if (value == "off_checked") {
            $('#ros_text').attr("disabled", true).val('');
            $('#ros2').prop("checked", false);
        } else if (value == "on_checked") {
            $('#ros_text').attr("disabled", false).val('');
            $('#ros1').prop("checked", false);
        }
    }

    function history_from_check(value) {
        if (value == "off_checked") {
            $('#history_from_text').attr("disabled", true).val('');
            $('#history_from3').prop("checked", false);
        } else if (value == "on_checked") {
            $('#history_from_text').attr("disabled", false).val('');
            $('#history_from1').prop("checked", false);
            $('#history_from2').prop("checked", false);
        }
    }

    function vaccineation_check(value) {
        if (value == "off_checked") {
            $('#vaccineation_text').attr("disabled", true).val('');
            $('#vaccineation2').prop("checked", false);
        } else if (value == "on_checked") {
            $('#vaccineation_text').attr("disabled", false).val('');
            $('#vaccineation1').prop("checked", false);
        }
    }

    function gd_check(value) {
        if (value == "off_checked") {
            $('#gd_text').attr("disabled", true).val('');
            $('#gd2').prop("checked", false);
        } else if (value == "on_checked") {
            $('#gd_text').attr("disabled", false).val('');
            $('#gd1').prop("checked", false);
        }
    }

    function fdh_check(value) {
        if (value == "off_checked") {
            $('#fdh_text').attr("disabled", true).val('');
            $('#fdh2').prop("checked", false);
        } else if (value == "on_checked") {
            $('#fdh_text').attr("disabled", false).val('');
            $('#fdh1').prop("checked", false);
        }
    }


    function admission_save() {
        var allergy_drug_history_all = "";
        var total_drug = $('#total_chq_drug').val();
        for (i = 1; i <= total_drug; i++) {
            if (typeof $('#allergy_drug_pri' + i).val() === 'undefined') {
                allergy_drug_history_all += "";
            } else {
                allergy_drug_history_all += ($('#allergy_drug_pri' + i).val()) + ' ' + ($('#allergy_drug_sec' + i).val()) + ' ';
            }
        }
        if (allergy_drug_history_all != "  ") {
            $('#allergy_drug_history').val(allergy_drug_history_all);
        }

        var allergy_food_history_all = "";
        var total_chq_food = $('#total_chq_food').val();
        for (i = 1; i <= total_chq_food; i++) {
            if (typeof $('#allergy_food_pri' + i).val() === 'undefined') {
                allergy_food_history_all += "";
            } else {
                allergy_food_history_all += ($('#allergy_food_pri' + i).val()) + ' ' + ($('#allergy_food_sec' + i).val()) + ' ';
            }
        }
        if (allergy_food_history_all != "  ") {
            $('#allergy_food_history').val(allergy_food_history_all);
        }

        var allergy_etc_history_all = "";
        var total_chq_etc = $('#total_chq_etc').val();
        for (i = 1; i <= total_chq_etc; i++) {
            if (typeof $('#allergy_etc_pri' + i).val() === 'undefined') {
                allergy_etc_history_all += "";
            } else {
                allergy_etc_history_all += ($('#allergy_etc_pri' + i).val()) + ' ' + ($('#allergy_etc_sec' + i).val()) + ' ';
            }
        }
        if (allergy_etc_history_all != "  ") {
            $('#allergy_etc_history').val(allergy_etc_history_all);
        }

        var disease_all = "";
        var total = $('#total_chq').val();
        for (i = 1; i <= total; i++) {
            if (typeof $('#disease_name' + i).val() === 'undefined') {
                disease_all += "";
            } else {
                disease_all += ($('#disease_name' + i).val()) + ' ' + ($('#disease_year' + i).val()) + ' ' + ($('#disease_hospital' + i).val()) + ' ';
            }
        }
        if (disease_all != "   ") {
            $('#disease_detail').val(disease_all);
        }

        var problem_list_all = "";
        var total_chq_problem_list = $('#total_chq_problem_list').val();
        for (i = 1; i <= total_chq_problem_list; i++) {

            if (typeof $('#problem_list_pri' + i).val() === 'undefined') {
                problem_list_all += "";
            } else {
                problem_list_all += ($('#problem_list_pri' + i).val()) + ' ';
            }
        }
        if (problem_list_all != "  ") {
            $('#problem_list').val(problem_list_all);
        }
/*
        var trsvg = canvas.toSVG();
        $('#svg_tag').html(trsvg);
        */

        var trsvg1 = canvas1.toSVG();
        $('#svg_tag1').html(trsvg1);

        var trsvg2 = canvas2.toSVG();
        $('#svg_tag2').html(trsvg2);

        var trsvg3 = canvas3.toSVG();
        $('#svg_tag3').html(trsvg3);

        var trsvg4 = canvas4.toSVG();
        $('#svg_tag4').html(trsvg4);

        var trsvg5 = canvas5.toSVG();
        $('#svg_tag5').html(trsvg5);

        const age_y = <?= json_encode($row_ipt['age_y']) ?>;
        const sex = <?= json_encode($row_ipt['sex']) ?>;
        var receives_immunisation_history_kid = $('input[type=radio][name=receives_immunisation_history_kid]:checked').val();
        var receives_immunisation_history_kid_text = $.trim($('[name="receives_immunisation_history_kid_text"]').val());
        var developmentally_kid = $('input[type=radio][name=developmentally_kid]:checked').val();
        var developmentally_kid_text = $.trim($('[name="developmentally_kid_text"]').val());
        var deliver_anomalies = $('input[type=radio][name=deliver_anomalies]:checked').val();
        var deliver_anomalies_text = $.trim($('[name="deliver_anomalies_text"]').val());
        var condition_pregnant = $('input[type=radio][name=condition_pregnant]:checked').val();
        var condition_pregnant_text = $.trim($('textarea[name=condition_pregnant_text]').val());
        if (age_y < 15 && receives_immunisation_history_kid == "ไม่ครบตามวัย" && receives_immunisation_history_kid_text == "") {
            alert("กรุณากรอกข้อมูล ประวัติการได้รับภูมิคุ้มกัน (เฉพาะเด็ก) ให้ครบถ้วน");
            $('[name="receives_immunisation_history_kid_text"]').focus();
        } else if (age_y < 15 && developmentally_kid == "ผิดปกติ" && developmentally_kid_text == "") {
            alert("กรุณากรอกข้อมูล การพัฒนาการ (เฉพาะเด็ก) ให้ครบถ้วน");
            $('[name="developmentally_kid_text"]').focus();
        } else if (age_y < 15 && deliver_anomalies == "ผิดปกติ" && deliver_anomalies_text == "") {
            alert("กรุณากรอกข้อมูล วิธีคลอด ให้ครบถ้วน");
            $('[name="deliver_anomalies_text"]').focus();
        } else if ((age_y > 9) && (sex == '2') && (condition_pregnant == "ผิดปกติ") && (condition_pregnant_text == "")) {
            alert("กรุณากรอกข้อมูล อาการระหว่างตั้งครรภ์ ให้ครบถ้วน");
            $('[name="condition_pregnant_text"]').focus();
        } else {
            var url_update = "er-dr-admission-note-form1-update.php";
            var url_save = "er-dr-admission-note-form1-save.php";
            var admission_note_id = $("#admission_note_id").val();
            var admit_firsth = $("#admit_firsth").serialize();

            if (admission_note_id == "") {
                $.post(url_save, admit_firsth, function(data) {
                        $("#show_check_save").html(data);
                        alert("บันทึกข้อมูลสำเร็จ");
                      //  self.close();
                      window.location.reload(true);
                    })
                    .fail(function() {
                        alert("บันทึกข้อมูลไม่สำเร็จ" + error);
                    });
            } else {
                $.post(url_update, admit_firsth, function(data) {
                        $("#show_check_save").html(data);
                        alert("บันทึกข้อมูลสำเร็จ");
                        //self.close();
                        window.location.reload(true);
                    })
                    .fail(function() {
                        alert("บันทึกข้อมูลไม่สำเร็จ" + error);
                    });
            }
        }
    }

    function onclick_Normal(id, value) {
        $('#' + id).val(value);
    }

    function onclick_Normal2(id, value) {
        $('#' + id).val(value);
    }
</script>