<?php  // require_once './project/function/Session.php';
// Session::::checkPermissionAndShowMessage('ADMISSION_NOTE','VIEW');
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
//ส่วนหัวหน้า
require_once '../mains/main-report.php';
//check session and permission  
Session::checkLoginSessionAndShowMessage(); //เช็ค session
Session::checkPermissionAndShowMessage('IPD_NURSE_ADDMISSION_NOTE', 'VIEW');
require_once '../mains/ipd-show-patient-main.php'; //เป็นส่วนที่แสดง ข้อมูลผู้ป่วย เช่น รูป,hn,an,ชื่อ-สกุล,แพ้ยา ฯลฯ
require_once '../mains/ipd-show-patient-sticky.php';
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';
require_once '../include/ReportQueryUtils.php';

Session::insertSystemAccessLog(json_encode(array(
    'form' => 'IPD-DR-TRAUMA-NOTE',
    'an' => $an,
), JSON_UNESCAPED_UNICODE));

$conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
$an = empty($_REQUEST['an']) ? null : $_REQUEST['an'];

$hn = KphisQueryUtils::getHnByAn($an);
$vn = KphisQueryUtils::getVnByAn($an);
$an_parameters = ['an' => $an];
$hn_parameters = ['hn' => $hn];


ReportQueryUtils::getTraumaNoteFunction($an);


//echo $_SESSION['name']; 

//-------------------------Doctor admission note

$sql = "SELECT *
                FROM `prs_ipd_trauma_note`
                WHERE an = :an";
$stmt = $conn->prepare($sql);
$stmt->execute($an_parameters);
if ($row  = $stmt->fetch()) {
    $admission_note_id = $row['admission_note_id'];
} else {
    $admission_note_id = null;
}

$id = '19'; //ลำดับในตาราง prs_link_menu
$sql = "SELECT *
                FROM `prs_link_menu`
                WHERE id = :id
                LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute(['id' => $id]);
if ($row0  = $stmt->fetch()) {
    $menu_name = $row0['menu_name'];
    $production = $row0['production'];
} else {
    $menu_name = '-';
}

// echo $vn;

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
            FROM " . DbConstant::KPHIS_DBNAME . ".prs_trauma_note_item dr_adm_item
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

/*
        $sql_ipt = "select patient.sex,patient.hn,patient.pname,patient.fname,patient.lname,
            (select GROUP_CONCAT(concat(opd_allergy.agent,'=',if(opd_allergy.symptom is null,',',opd_allergy.symptom))) as name
                from ".DbConstant::HOSXP_DBNAME.".opd_allergy
                where opd_allergy.hn = ipt.hn
                order by display_order) as drugallergy,
            an_stat.age_y,an_stat.age_m,an_stat.age_d,
            concat(ipt.regdate,' ',ipt.regtime) as regdatetime,
            ipt.dchdate,ipt.dchtime,
            ipt.ward,ward.name,
            ipt.pttype, pttype.`name` as pttype_name,
            iptadm.bedno, (select vs.bw from ipd_vs_vital_sign vs where vs.an = ipt.an and vs.bw is not null and trim(vs.bw) <> '' order by vs_datetime desc limit 1) as latest_bw
            , (select vs.height from ipd_vs_vital_sign vs where vs.an = ipt.an and vs.bw is not null and trim(vs.bw) <> '' order by vs_datetime desc limit 1) as latest_height
            , (select vs.vs_datetime from ipd_vs_vital_sign vs where vs.an = ipt.an and vs.bw is not null and trim(vs.bw) <> '' order by vs_datetime desc limit 1) as latest_bw_datetime
            from ".DbConstant::HOSXP_DBNAME.".ipt
            left outer join ".DbConstant::HOSXP_DBNAME.".an_stat on an_stat.an=ipt.an
            left outer join ".DbConstant::HOSXP_DBNAME.".patient on patient.hn=ipt.hn
            left outer join ".DbConstant::HOSXP_DBNAME.".ward on ward.ward=ipt.ward
            LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".pttype ON pttype.pttype = ipt.pttype
            LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".iptadm ON iptadm.an = ipt.an
            WHERE ipt.an=:an
            order by ipt.an
            ";
        $stmt_ipt = $conn->prepare($sql_ipt);
        $stmt_ipt->execute(['an'=>$an]);
        $row_ipt = $stmt_ipt->fetch();
        $regdatetime = $row_ipt["regdatetime"];
        */

//sql_drug_allergy
// $sql_drug_allergy = "select (concat(opd_allergy.agent,'=',if(opd_allergy.symptom is null,',',opd_allergy.symptom)/*,' [',if(note is null,',',note),']'*/)) as name
//         from ".DbConstant::HOSXP_DBNAME.".opd_allergy
//         where opd_allergy.hn = :hn /*and (opd_allergy.no_alert<>'Y' or opd_allergy.no_alert is null)*/
//         order by display_order
//     ";
// $stmt_sql_drug_allergy = $conn->prepare($sql_drug_allergy);
// $stmt_sql_drug_allergy->execute(['hn'=>$hn]);
// $row_sql_drug_allergy = $stmt_sql_drug_allergy->fetch();

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

//Vital Sign
/*        $sql_vs =   "SELECT opd_vs_vital_sign.sbp,opd_vs_vital_sign.dbp,opd_vs_vital_sign.bt,opd_vs_vital_sign.pr,opd_vs_vital_sign.rr,
                    opd_vs_vital_sign.eye,opd_vs_vital_sign.verbal,opd_vs_vital_sign.movement,opd_vs_vital_sign.braden
                    FROM ".DbConstant::KPHIS_DBNAME.".opd_vs_vital_sign
                    WHERE opd_vs_vital_sign.an=:vn
                    GROUP BY opd_vs_vital_sign.vs_datetime ASC LIMIT 1";
        $stmt_vs = $conn->prepare($sql_vs);
        $stmt_vs->execute(['vn'=>$vn]);
        $row_vs  = $stmt_vs->fetch();

*/
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
        height: 500px;
    }

    canvas {
        padding-left: 0;
        padding-right: 0;
        margin-left: auto;
        margin-right: auto;
        display: block;
    }
</style>
<form id="er_trauma" action="" method="post" enctype="multipart/form-data">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-1">
                <button type="button" class="btn btn-danger btn-block" onclick="self.close()"><i class="fa fa-window-close"></i> ปิด</button>
            </div>
            <div class="col-md-11">
                <h4><?= htmlspecialchars($menu_name) ?> 
                <?= htmlspecialchars(DbConstant::HOSPITAL_NAME) ?><?php if ($production == "2") { ?>

                    <font color="red">ช่วงทดลอง</font>
                <?php } else { ?>

                <? } ?>
                </h4>
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
                                    <textarea class="form-control" id="hpi" name="hpi" rows="3"><?= (isset($row_opdscreen['hpi']) && $admission_note_id == null ? htmlspecialchars($row_opdscreen['hpi']) : htmlspecialchars($row['hpi'])) ?></textarea>
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
                                                        } ?> class="custom-control-input" id="vaccineation3" name="vaccigdneation" onchange="vaccineation_check('on_checked');">
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




        <div class="card-group pb-3 ">

            <div class="card">

                <div class="card-body" style=" overflow-y: auto;">
                    <div class="alert alert-primary text-center col-sm-12" role="alert">PRIMARY SURVEY</div>
                    <div class="row">
                        <div class="col-md-12">

                            <div class="form-group row">
                                <div class="col-sm-1"></div>
                                <label>&nbsp;&nbsp;A:&nbsp;&nbsp;</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['primary_a'] == 'patient') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="pa1" value="patient" name="primary_a">
                                    <label class="custom-control-label" for="pa1">Patient</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['primary_a'] == 'stidor') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="pa2" value="stidor" name="primary_a">
                                    <label class="custom-control-label" for="pa2">Stidor</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php if ($row['primary_a'] == 'apnea') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="pa3" value="apnea" name="primary_a">
                                    <label class="custom-control-label" for="pa3">Apnea, C-Spine</label>
                                </div>


                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php if ($row['c_spine'] == 'Notpain') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="c_spine1" value="Notpain" name="c_spine">
                                    <label class="custom-control-label" for="c_spine1">Not pain</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php if ($row['c_spine'] == 'Onhardcollar') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="c_spine2" value="Onhardcollar" name="c_spine">
                                    <label class="custom-control-label" for="c_spine2">On hard collar</label>
                                </div>


                            </div>


                            <div class="form-group row">
                                <div class="col-sm-1"></div>
                                <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Trachea&nbsp;&nbsp;</label>

                                <div class="custom-control custom-radio col-sm-2">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <input type="radio" <?php if (
                                                                                                        $row['trachea'] == 'Midline'

                                                                                                    ) {
                                                                                                        echo 'checked="checked"';
                                                                                                    } ?> class="custom-control-input" id="trachea1" name="trachea" value="Midline" onchange="custom_check('off_trachea');">
                                    <label class="custom-control-label" for="trachea1">Midline</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-2">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['trachea'] != 'Midline' && $row['trachea'] != NULL) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="trachea2" name="trachea" value="Deviate" onchange="custom_check('on_trachea');">
                                    <label class="custom-control-label" for="trachea2">Deviate</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="trachea_text" name="trachea" value="<?php if ($row['trachea'] != 'Midline' && $row['trachea'] != NULL) {
                                                                                                                                        echo htmlspecialchars($row['trachea']);
                                                                                                                                    } ?>" <?php if (!($row['trachea'] != 'Midline' && $row['trachea'] != NULL)) {
                                                                                                                                                echo 'disabled';
                                                                                                                                            } ?>>
                                </div>


                            </div>






                            <div class="form-group row">
                                <div class="col-sm-1"></div>
                                <label>&nbsp;&nbsp;B: Chest wound&nbsp;&nbsp;&nbsp;&nbsp;</label>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if (
                                                            $row['chest_wound'] == 'No'
                                                            || $row['chest_wound'] == NULL
                                                        ) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="chest_wound1" name="chest_wound" value="No" onchange="custom_check('off_chest_wound');">
                                    <label class="custom-control-label" for="chest_wound1">No</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if (
                                                            $row['chest_wound'] != 'No'
                                                            && $row['chest_wound'] != NULL
                                                        ) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="chest_wound2" name="chest_wound" onchange="custom_check('on_chest_wound');">
                                    <label class="custom-control-label" for="chest_wound2">Yes</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="chest_wound_text" name="chest_wound" value="<?php if ($row['chest_wound'] != 'No') {
                                                                                                                                                echo htmlspecialchars($row['chest_wound']);
                                                                                                                                            } ?>" <?php if (!($row['chest_wound'] != 'No'
                                                                                                                                                        && $row['chest_wound'] != NULL)) {
                                                                                                                                                        echo 'disabled';
                                                                                                                                                    } ?>>
                                </div>


                            </div>



                            <div class="form-group row">
                                <div class="col-sm-1"></div>
                                <label class="col-sm-3 text-right">Subcutaneouse emphysema</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['subcu_emp'] == 'No') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="subcu_emp1" value="No" name="subcu_emp">
                                    <label class="custom-control-label" for="subcu_emp1">No</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['subcu_emp'] == 'Yes') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="subcu_emp2" value="Yes" name="subcu_emp">
                                    <label class="custom-control-label" for="subcu_emp2">Yes</label>
                                </div>

                            </div>

                            <div class="form-group row">
                                <div class="col-sm-1"></div>
                                &nbsp;&nbsp;&nbsp;<label class="col-sm-1 text-left">CCT</label>


                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php if (
                                                            $row['cct'] == 'Negative'
                                                            || $row['cct'] == NULL
                                                        ) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="cct1" name="cct" value="Negative" onchange="custom_check('off_cct');">
                                    <label class="custom-control-label" for="cct1">Negative</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if (
                                                            $row['cct'] != 'Negative'
                                                            && $row['cct'] != NULL
                                                        ) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="cct2" name="cct" onchange="custom_check('on_cct');">
                                    <label class="custom-control-label" for="cct2">Positive</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="cct_text" name="cct" value="<?php if ($row['cct'] != 'Negative') {
                                                                                                                                echo htmlspecialchars($row['cct']);
                                                                                                                            } ?>" <?php if (!($row['cct'] != 'Negative'
                                                                                                                                        && $row['cct'] != NULL)) {
                                                                                                                                        echo 'disabled';
                                                                                                                                    } ?>>
                                </div>

                            </div>

                            <div class="form-group row">
                                <div class="col-sm-1"></div>
                                &nbsp;&nbsp;&nbsp;<label class="col-sm-2 text-left">Lung sound</label>
                                <div class="custom-control custom-checkbox col-sm-2">
                                    <input type="radio" <?php if ($row['lung_sound'] == 'clear') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="l1" value="clear" name="lung_sound">
                                    <label class="custom-control-label" for="l1">clear & equal</label>
                                </div>
                                <div class="custom-control custom-checkbox col-sm-2">
                                    <input type="radio" <?php if ($row['lung_sound'] == 'decrease') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="l2" value="decrease" name="lung_sound">
                                    <label class="custom-control-label" for="l2">decrease</label>
                                </div>
                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="radio" <?php if ($row['lung_sound'] == 'rt') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="l3" value="rt" name="lung_sound">
                                    <label class="custom-control-label" for="l3">Rt.</label>
                                </div>
                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="radio" <?php if ($row['lung_sound'] == 'lt') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="l4" value="lt" name="lung_sound">
                                    <label class="custom-control-label" for="l4">Lt.</label>
                                </div>

                            </div>



                            <div class="form-group row">
                                <div class="col-sm-1"></div>
                                <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;O<sub>2</sub>sat RA</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control form-control-sm" id="o2sat" name="o2sat" value="<?= (isset($row['o2sat']) ? htmlspecialchars($row['o2sat']) : '') ?>">
                                </div><label> %</label>
                            </div>


                            <div class="form-group row">
                                <div class="col-sm-1"></div>
                                <label>&nbsp;&nbsp;C: BP&nbsp;&nbsp;&nbsp;&nbsp;</label>

                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php if (
                                                            $row['bp_check'] == 'Normal'
                                                            /*|| $row['bp_check'] == NULL*/
                                                        ) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="bp_check1" name="bp_check" value="Normal" onchange="custom_check('off_bp_check');">
                                    <label class="custom-control-label" for="bp_check1">Normal</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php if (
                                                            $row['bp_check'] != 'Normal'
                                                            && $row['bp_check'] != NULL
                                                        ) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="bp_check2" name="bp_check" onchange="custom_check('on_bp_check');">
                                    <label class="custom-control-label" for="bp_check2">AbNormal</label>
                                </div>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control form-control-sm" id="bp_check_text" name="bp_check" value="<?php if ($row['bp_check'] != 'Normal') {
                                                                                                                                                    echo htmlspecialchars($row['bp_check']);
                                                                                                                                                } ?>" <?php if (!($row['bp_check'] != 'Normal'
                                                                                                                                                            && $row['bp_check'] != NULL)) {
                                                                                                                                                            echo 'disabled';
                                                                                                                                                        } ?>>
                                </div>


                            </div>

                            <div class="form-group row">
                                <div class="col-sm-1"></div>
                                <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;PR&nbsp;&nbsp;&nbsp;&nbsp;</label>

                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php if (
                                                            $row['pr_check'] == 'Normal'
                                                            /*|| $row['ext_act_bleed'] == NULL*/
                                                        ) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="pr_check1" name="pr_check" value="Normal" onchange="custom_check('off_pr_check');">
                                    <label class="custom-control-label" for="pr_check1">Normal</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php if (
                                                            $row['pr_check'] != 'Normal'
                                                            && $row['pr_check'] != NULL
                                                        ) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="pr_check2" name="pr_check" onchange="custom_check('on_pr_check');">
                                    <label class="custom-control-label" for="pr_check2">AbNormal</label>
                                </div>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control form-control-sm" id="pr_check_text" name="pr_check" value="<?php if ($row['pr_check'] != 'Normal') {
                                                                                                                                                    echo htmlspecialchars($row['pr_check']);
                                                                                                                                                } ?>" <?php if (!($row['pr_check'] != 'Normal'
                                                                                                                                                            && $row['pr_check'] != NULL)) {
                                                                                                                                                            echo 'disabled';
                                                                                                                                                        } ?>>
                                </div>


                            </div>



                         <!--   <div class="form-group row">
                                <div class="col-sm-1"></div>
                                <label>&nbsp;&nbsp;C: BP</label>

                                <div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm" id="sbp" name="sbp" placeholder="sbp" value="<?= (isset($row['sbp']) ? htmlspecialchars($row['sbp']) : '') ?>">
                                </div> /
                                <div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm" id="dbp" name="dbp" placeholder="dbp" value="<?= (isset($row['dbp']) ? htmlspecialchars($row['dbp']) : '') ?>">
                                </div>mmHg,
                                <label>&nbsp;&nbsp;PR</label>

                                <div class="col-sm-1">
                                    <input type="text" class="form-control form-control-sm" id="pr2" name="pr2" placeholder="pr" value="<?= (isset($row['pr2']) ? htmlspecialchars($row['pr2']) : '') ?>">
                                </div>/min

                            </div>

                                                                                                                                                    -->

                            <div class="form-group row">
                                <div class="col-sm-1"></div>
                                <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;External active bleeding&nbsp;&nbsp;&nbsp;&nbsp;</label>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if (
                                                            $row['ext_act_bleed'] == 'No'
                                                            || $row['ext_act_bleed'] == NULL
                                                        ) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="ext_act_bleed1" name="ext_act_bleed" value="No" onchange="custom_check('off_ext_act_bleed');">
                                    <label class="custom-control-label" for="ext_act_bleed1">No</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if (
                                                            $row['ext_act_bleed'] != 'No'
                                                            && $row['ext_act_bleed'] != NULL
                                                        ) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="ext_act_bleed2" name="ext_act_bleed" onchange="custom_check('on_ext_act_bleed');">
                                    <label class="custom-control-label" for="ext_act_bleed2">Yes</label>
                                </div>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control form-control-sm" id="ext_act_bleed_text" name="ext_act_bleed" value="<?php if ($row['ext_act_bleed'] != 'No') {
                                                                                                                                                    echo htmlspecialchars($row['ext_act_bleed']);
                                                                                                                                                } ?>" <?php if (!($row['ext_act_bleed'] != 'No'
                                                                                                                                                            && $row['ext_act_bleed'] != NULL)) {
                                                                                                                                                            echo 'disabled';
                                                                                                                                                        } ?>>
                                </div>


                            </div>


                            <div class="form-group row">
                                <div class="col-sm-1"></div>
                                <label>&nbsp;&nbsp;D: GCS E</label>

                                <div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm" id="gcs_e" name="gcs_e" placeholder="E" value="<?= (isset($row['gcs_e']) ? htmlspecialchars($row['gcs_e']) : '') ?>">
                                </div> V
                                <div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm" id="gcs_v" name="gcs_v" placeholder="V" value="<?= (isset($row['gcs_v']) ? htmlspecialchars($row['gcs_v']) : '') ?>">
                                </div>M
                                <div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm" id="gcs_m" name="gcs_m" placeholder="M" value="<?= (isset($row['gcs_m']) ? htmlspecialchars($row['gcs_m']) : '') ?>">
                                </div>, Pupil Rt
                                <div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm" id="pupil_rt" name="pupil_rt" placeholder="Rt" value="<?= (isset($row['pupil_rt']) ? htmlspecialchars($row['pupil_rt']) : '') ?>">
                                </div>

                            </div>

                            <div class="form-group row">
                                <div class="col-sm-1"></div>
                                <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Lt</label>

                                <div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm" id="pupil_lt" name="pupil_lt" placeholder="Lt" value="<?= (isset($row['pupil_lt']) ? htmlspecialchars($row['pupil_lt']) : '') ?>">
                                </div>

                            </div>

                            <div class="form-group row">
                                <div class="col-sm-1"></div>
                                <label>&nbsp;&nbsp;E:</label>
                                <div class="col-sm-10">
                                    <textarea class="form-control" id="e_text" name="e_text" rows="4"><?= (isset($row['e_text']) ? htmlspecialchars($row['e_text']) : '') ?></textarea>
                                </div>
                            </div>



                        </div>
                    </div>
                </div>
            </div>





            <div class="card">

                <div class="card-body" style=" overflow-y: auto;">
                    <div class="alert alert-warning text-center col-sm-12" role="alert">SECONDARY SURVEY</div>
                    <div class="row">

                        <div class="col-md-12">
                            <div class="form-group row">
                                <label class="col-sm-2"><B>Allergy:</B></label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control form-control-sm" id="tr_allergy" name="tr_allergy" value="<?= (isset($row['tr_allergy']) ? htmlspecialchars($row['tr_allergy']) : '') ?>">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-2"><B>Medication:</B></label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control form-control-sm" id="tr_meddication" name="tr_meddication" value="<?= (isset($row['tr_meddication']) ? htmlspecialchars($row['tr_meddication']) : '') ?>">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-2"><B>Past illness / Pregnancy:</B></label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control form-control-sm" id="part_illness" name="part_illness" value="<?= (isset($row['part_illness']) ? htmlspecialchars($row['part_illness']) : '') ?>">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-2"><B>Last meal:</B></label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control form-control-sm" id="last_meal" name="last_meal" value="<?= (isset($row['last_meal']) ? htmlspecialchars($row['last_meal']) : '') ?>">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-2"><B>Event / Environment:</B></label>
                                <div class="col-sm-10">
                                    <textarea class="form-control" id="event_environment" name="event_environment" rows="4"><?= (isset($row['event_environment']) ? htmlspecialchars($row['event_environment']) : '') ?></textarea>
                                </div>
                            </div>


                        </div>
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

                    <div class="alert alert-success text-center col-sm-12" role="alert">Head-To-Toe Exam</div>

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
                                            <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_general','Good consciousness ,co-operative')"><i class="fas fa-user"></i> Normal</button>
                                            <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_general','Active crying')"><i class="fas fa-baby"></i> Normal</button>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="text-right col-sm-3">Skin</label>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="<?= (isset($row['pe_skin']) ? htmlspecialchars($row['pe_skin']) : '') ?>" id="pe_skin" name="pe_skin">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_skin','No Abdominal lesion')"><i class="fas fa-user"></i> Normal</button>
                                            <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_skin','Pink, no rash')"><i class="fas fa-baby"></i> Normal</button>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="text-right col-sm-3">HEENT</label>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="<?= (isset($row_opdscreen['pe_heent_text']) && $admission_note_id == null ? htmlspecialchars($row_opdscreen['pe_heent_text']) : htmlspecialchars($row['pe_heent'])) ?>" id="pe_heent" name="pe_heent">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_heent','Not pale conjunctiva, anicteric sclera, pharynx and tonsil not injected')"><i class="fas fa-user"></i> Normal</button>
                                            <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_heent','AF 2*2 cm, no cephalhematoma')"><i class="fas fa-baby"></i> Normal</button>
                                        </div>
                                    </div>


                                    <div class="form-group row">
                                        <label class="text-right col-sm-3">Lungs</label>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="<?= (isset($row_opdscreen['pe_lung_text']) && $admission_note_id == null ? htmlspecialchars($row_opdscreen['pe_lung_text']) : htmlspecialchars($row['pe_lungs'])) ?>" id="pe_lungs" name="pe_lungs">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_lungs','Normal chest movement , Normal breath sound Lt=Rt')"><i class="fas fa-user"></i> Normal</button>
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
                                            <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_abdomen','Normoactive bowel sound, soft, not tender, no hepato-spleeno megal')"><i class="fas fa-user"></i> Normal</button>
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
                                            <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_extremities','No deformity, No edema, Nojoint swelling')"><i class="fas fa-user"></i> Normal</button>
                                            <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_extremities','No deformity')"><i class="fas fa-baby"></i> Normal</button>
                                        </div>
                                    </div>


                                    <div class="form-group row">
                                        <label class="text-right col-sm-3">CNS</label>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="<?= (isset($row['pe_cns']) ? htmlspecialchars($row['pe_cns']) : '') ?>" id="pe_cns" name="pe_cns">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_cns','E4V5M6 Pupil 2 mm RTLBE Motor and Sensory grossly intact')"><i class="fas fa-user"></i> Normal</button>
                                            <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_cns','No hepatosplenomegaly')"><i class="fas fa-baby"></i> Normal</button>
                                        </div>
                                    </div>

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
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_NormalAll('pe_general','Good consciousness ,co-operative','pe_skin','No Abdominal lesion'
                                ,'pe_heent','Not pale conjunctiva, anicteric sclera, pharynx and tonsil not injected','pe_lungs','Normal chest movement , Normal breath sound Lt=Rt'
                                ,'pe_cvs','Regular rhythm Normal s1 s2 no murmur Normal PMI','pe_abdomen','Normoactive bowel sound, soft, not tender, no hepato-spleeno megal'
                                ,'pe_rectalgenitalia','PR no mass , Good sphincter tone , normal genital appearance','pe_extremities','No deformity, No edema, Nojoint swelling'
                                ,'pe_ob_gynexam','no mass , no discharge','pe_cns','E4V5M6, Pupil 2 mm RTLBE Motor and Sensory intact')"><i class="fas fa-user-plus"></i> NormalAll</button>
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

                    <div class="form-group row">
                        <div class="col-sm-12">
                            <div class="card border-success">
                                <div class="card-body">
                                    <h5 class="card-title"></h5>
                                    <!-- <div class="text-right">
                            <button type="button" class="btn btn-outline-success" onclick="onclick_img_button()"><i class='fas fa-edit'></i> แก้ไขรูปภาพ</button>
                        </div> -->
                                    <div class="col-sm-8 offset-sm-3 mb-3">
                                        <div id="show_img_select" class="text-center">
                                            <canvas id="c" width="700" height="500"></canvas>
                                        </div>
                                    </div>
                                    <div class="col-sm-11 offset-sm-1" style="display: inline-block;">
                                        <div class="text-center">
                                            <a href="javascript:undo();"><button id="" type="button" class="btn btn-outline-danger PhysicalExaminationBtn"><i class="far fa-arrow-alt-circle-left"></i> Undo</button></a>
                                            <a href="javascript:redo();"><button id="" type="button" class="btn btn-outline-danger PhysicalExaminationBtn">Redo <i class="far fa-arrow-alt-circle-right"></i></button></a>
                                            <button id="clear-canvas" type="button" class="btn btn-outline-danger PhysicalExaminationBtn" onclick="onclick_clear()"><i class='fas fa-edit'></i> Clear</button>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="form-group">
                                            <label for=""></label>
                                            <textarea style="display:none;" class="form-control" name="svg_tag" id="svg_tag" rows="10"><?= (isset($row['svg_tag']) ? htmlspecialchars($row['svg_tag']) : '') ?></textarea>
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
                                                <div class="col-sm-1"></div>
                                                <label class="text-left col-sm-1">Problemlist</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="<?= (isset($row['problem_list']) ? htmlspecialchars($row['problem_list']) : '') ?>" id="" name="problem_list">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-sm-1"></div>
                                                <label>&nbsp;&nbsp;<b style='text-decoration: underline;'>Provisional Diagnosis</b>&nbsp;&nbsp;</label>
                                            </div>

                                            <div class="form-group row">

                                            
                                                    <input type="radio" <?php if ($row['mild_tbi'] == '' && $row['mild_tbi'] != null) {
                                                                            echo 'checked="checked"';
                                                                        } ?> class="custom-control-input" id="mild_tbi0" value="" name="mild_tbi">
                                                    <label class="custom-control-label" for="mild_tbi0">reset</label>
                                             
                     
                                                <label class="text-right col-sm-3" >Mild TBI</label>

                                                <div class="custom-control custom-checkbox col-sm-2">
                                                    <input type="radio" <?php
                                                                        if ($row['mild_tbi'] == 'Low risk' && $row['mild_tbi'] != null) {
                                                                            echo 'checked="checked"';
                                                                        }
                                                                        ?> class="custom-control-input" id="mild_tbi1" value="Low risk" name="mild_tbi">
                                                    <label class="custom-control-label" for="mild_tbi1">Low risk</label>
                                                </div>

                                                <div class="custom-control custom-checkbox col-sm-2">
                                                    <input type="radio" <?php if ($row['mild_tbi'] == 'Moderate risk' && $row['mild_tbi'] != null) {
                                                                            echo 'checked="checked"';
                                                                        } ?> class="custom-control-input" id="mild_tbi2" value="Moderate risk" name="mild_tbi">
                                                    <label class="custom-control-label" for="mild_tbi2">Moderate risk</label>
                                                </div>

                                                <div class="custom-control custom-checkbox col-sm-2">
                                                    <input type="radio" <?php if ($row['mild_tbi'] == 'High risk' && $row['mild_tbi'] != null) {
                                                                            echo 'checked="checked"';
                                                                        } ?> class="custom-control-input" id="mild_tbi3" value="High risk" name="mild_tbi">
                                                    <label class="custom-control-label" for="mild_tbi3">High risk</label>
                                                </div>

                                                <div class="custom-control custom-checkbox col-sm-2">
                                                    <input type="radio" <?php if ($row['mild_tbi'] == 'Moderate TBI' && $row['mild_tbi'] != null) {
                                                                            echo 'checked="checked"';
                                                                        } ?> class="custom-control-input" id="mild_tbi4" value="Moderate TBI" name="mild_tbi">
                                                    <label class="custom-control-label" for="mild_tbi4">Moderate TBI</label>
                                                </div>




                                            </div>

                                            <div class="form-group row">

                                            <input type="radio" <?php if ($row['abdomen'] == '' && $row['abdomen'] != null) {
                                                                            echo 'checked="checked"';
                                                                        } ?> class="custom-control-input" id="abdomen0" value="" name="abdomen">
                                                    <label class="custom-control-label" for="abdomen0">reset</label>

                                                <label class="text-right col-sm-3">Abdomen</label>
                                                <div class="custom-control custom-checkbox col-sm-1">
                                                    <input type="radio" <?php if ($row['abdomen'] == 'blunt') {
                                                                            echo 'checked="checked"';
                                                                        } ?> class="custom-control-input" id="abdomen1" value="blunt" name="abdomen">
                                                    <label class="custom-control-label" for="abdomen1">blunt</label>
                                                </div>

                                                <div class="custom-control custom-checkbox col-sm-2">
                                                    <input type="radio" <?php if ($row['abdomen'] == 'Penetrating') {
                                                                            echo 'checked="checked"';
                                                                        } ?> class="custom-control-input" id="abdomen2" value="Penetrating" name="abdomen">
                                                    <label class="custom-control-label" for="abdomen2">Penetrating</label>
                                                </div>

                                            </div>

                                            <div class="form-group row">

                                            <input type="radio" <?php if ($row['chest'] == '' && $row['chest'] != null) {
                                                                            echo 'checked="checked"';
                                                                        } ?> class="custom-control-input" id="chest0" value="" name="chest">
                                                    <label class="custom-control-label" for="chest0">reset</label>

                                                <label class="text-right col-sm-3">Chest</label>
                                                <div class="custom-control custom-checkbox col-sm-1">
                                                    <input type="radio" <?php if ($row['chest'] == 'blunt') {
                                                                            echo 'checked="checked"';
                                                                        } ?> class="custom-control-input" id="chest1" value="blunt" name="chest">
                                                    <label class="custom-control-label" for="chest1">blunt</label>
                                                </div>

                                                <div class="custom-control custom-checkbox col-sm-2">
                                                    <input type="radio" <?php if ($row['chest'] == 'Penetrating') {
                                                                            echo 'checked="checked"';
                                                                        } ?> class="custom-control-input" id="chest2" value="Penetrating" name="chest">
                                                    <label class="custom-control-label" for="chest2">Penetrating</label>
                                                </div>

                                                <div class="custom-control custom-checkbox col-sm-2">
                                                    <input type="radio" <?php if ($row['chest'] == 'Lung contusion') {
                                                                            echo 'checked="checked"';
                                                                        } ?> class="custom-control-input" id="chest3" value="Lung contusion" name="chest">
                                                    <label class="custom-control-label" for="chest3">Lung contusion</label>
                                                </div>

                                            </div>

                                            <div class="form-group row">

                                            <input type="radio" <?php if ($row['pneumothorax'] == '' && $row['pneumothorax'] != null) {
                                                                            echo 'checked="checked"';
                                                                        } ?> class="custom-control-input" id="pneumothorax0" value="" name="pneumothorax">
                                                    <label class="custom-control-label" for="pneumothorax0">reset</label>

                                                <label class="text-right col-sm-3">Pneumothorax</label>
                                                <div class="custom-control custom-checkbox col-sm-2">
                                                    <input type="radio" <?php if ($row['pneumothorax'] == 'Spontaneous') {
                                                                            echo 'checked="checked"';
                                                                        } ?> class="custom-control-input" id="pneumothorax1" value="Spontaneous" name="pneumothorax">
                                                    <label class="custom-control-label" for="pneumothorax1">Spontaneous</label>
                                                </div>

                                                <div class="custom-control custom-checkbox col-sm-1">
                                                    <input type="radio" <?php if ($row['pneumothorax'] == 'Tension') {
                                                                            echo 'checked="checked"';
                                                                        } ?> class="custom-control-input" id="pneumothorax2" value="Tension" name="pneumothorax">
                                                    <label class="custom-control-label" for="pneumothorax2">Tension</label>
                                                </div>

                                                <div class="custom-control custom-checkbox col-sm-3">
                                                    <input type="radio" <?php if ($row['pneumothorax'] == 'Hemothorax') {
                                                                            echo 'checked="checked"';
                                                                        } ?> class="custom-control-input" id="pneumothorax3" value="Hemothorax" name="pneumothorax">
                                                    <label class="custom-control-label" for="pneumothorax3">Hemothorax (massive / Non-massive)</label>
                                                </div>

                                                <div class="custom-control custom-checkbox col-sm-1">
                                                    <input type="radio" <?php if ($row['pneumothorax'] == 'Open') {
                                                                            echo 'checked="checked"';
                                                                        } ?> class="custom-control-input" id="pneumothorax4" value="Open" name="pneumothorax">
                                                    <label class="custom-control-label" for="pneumothorax4">Open</label>
                                                </div>

                                            </div>





                                            <div class="form-group row">
                                                <label class="text-right col-sm-3">Fracture</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="<?= (isset($row['fracture']) ? htmlspecialchars($row['fracture']) : '') ?>" id="fracture" name="fracture">
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="text-right col-sm-3">Other</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="<?= (isset($row['other_text']) ? htmlspecialchars($row['other_text']) : '') ?>" id="" name="other_text">
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
                                                <label class="text-right col-sm-3">Plan of treatment</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="<?= (isset($row['plan_of_treatment']) ? htmlspecialchars($row['plan_of_treatment']) : '') ?>" id="plan_of_treatment" name="plan_of_treatment">
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
    }
    //--------------------------------------------canvas----------------------------------------------


    
    function custom_check(value) {


        if (value == "off_trachea") {
            $('#trachea_text').attr("disabled", true).val('');
            $('#trachea2').prop("checked", false);
        } else if (value == "on_trachea") {
            $('#trachea_text').attr("disabled", false).val('');
            $('#trachea1').prop("checked", false);
        }

        if (value == "off_chest_wound") {
            $('#chest_wound_text').attr("disabled", true).val('');
            $('#chest_wound2').prop("checked", false);
        } else if (value == "on_chest_wound") {
            $('#chest_wound_text').attr("disabled", false).val('');
            $('#chest_wound1').prop("checked", false);
        }

        if (value == "off_cct") {
            $('#cct_text').attr("disabled", true).val('');
            $('#cct2').prop("checked", false);
        } else if (value == "on_cct") {
            $('#cct_text').attr("disabled", false).val('');
            $('#cct1').prop("checked", false);
        }

        if (value == "off_ext_act_bleed") {
            $('#ext_act_bleed_text').attr("disabled", true).val('');
            $('#ext_act_bleed2').prop("checked", false);
        } else if (value == "on_ext_act_bleed") {
            $('#ext_act_bleed_text').attr("disabled", false).val('');
            $('#ext_act_bleed1').prop("checked", false);
        }




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

        if (value == "off_bp_check") {
            $('#bp_check_text').attr("disabled", true).val('');
            $('#bp_check2').prop("checked", false);
        } else if (value == "on_bp_check") {
            $('#bp_check_text').attr("disabled", false).val('');
            $('#bp_check1').prop("checked", false);
        }

        if (value == "off_pr_check") {
            $('#pr_check_text').attr("disabled", true).val('');
            $('#pr_check2').prop("checked", false);
        } else if (value == "on_pr_check") {
            $('#pr_check_text').attr("disabled", false).val('');
            $('#pr_check1').prop("checked", false);
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

        var trsvg = canvas.toSVG();
        $('#svg_tag').html(trsvg);

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
            var url_update = "er-trauma-note-form-update.php";
            var url_save = "er-trauma-note-form-save.php";
            var admission_note_id = $("#admission_note_id").val();
            var er_trauma = $("#er_trauma").serialize();

            if (admission_note_id == "") {
                $.post(url_save, er_trauma, function(data) {
                        $("#show_check_save").html(data);
                      ///  alert("บันทึกข้อมูลสำเร็จ");
                      //  window.location.reload(true);
                        //self.close();
                    })
                    .fail(function() {
                        alert("บันทึกข้อมูลไม่สำเร็จ" + error);
                    });
            } else {
                $.post(url_update, er_trauma, function(data) {
                        $("#show_check_save").html(data);
                       // alert("บันทึกข้อมูลสำเร็จ");
                        // self.close();
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

    function onclick_NormalAll(id, value, id2, value2, id3, value3, id4, value4, id5, value5, id6, value6, id7, value7, id8, value8, id9, value9, id10, value10) {
        $('#' + id).val(value);
        $('#' + id2).val(value2);
        $('#' + id3).val(value3);
        $('#' + id4).val(value4);
        $('#' + id5).val(value5);
        $('#' + id6).val(value6);
        $('#' + id7).val(value7);
        $('#' + id8).val(value8);
        $('#' + id9).val(value9);
        $('#' + id10).val(value10);
    }

    $('.reset').click(function() {
        var name = $(this).data('name');
        $('input[name=' + name + ']').prop('checked', false);
    });
</script>

<script src="../node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
<link rel="stylesheet" href="../node_modules/sweetalert2/dist/sweetalert2.min.css">