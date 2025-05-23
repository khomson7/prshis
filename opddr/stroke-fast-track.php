<?php require_once '../include/Session.php';

$login = empty($_REQUEST['loginname']) ? null : $_REQUEST['loginname'];
$loginname = $_SESSION['loginname'];
$values = ['loginname' => $loginname];

//หากพบว่าไม่ตรงกันให้ ทำลาย session เดิมทิ้งไป
if ($login != $loginname) {
        session_start();
        session_destroy();
}

require_once '../mains/main-report.php';

$permissionCheck = Session::checkPermissionAndShowMessage('PRS_STROKE_FAST_TRACK', 'VIEW');
$permissionCheckJson = json_encode($permissionCheck);
require_once '../mains/ipd-show-patient-main.php'; //เป็นส่วนที่แสดง ข้อมูลผู้ป่วย เช่น รูป,hn,an,ชื่อ-สกุล,แพ้ยา ฯลฯ
require_once '../mains/ipd-show-patient-sticky.php';
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';
require_once '../include/ReportQueryUtils.php';
require_once '../include/session-modal.php';
//require_once '../include/DbUtils.php';
//require_once '../include/KphisQueryUtils.php';
//require_once '../include/ReportQueryUtils.php';


$conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
$hn = empty($_REQUEST['hn']) ? null : $_REQUEST['hn'];
$vn = empty($_REQUEST['vn']) ? null : $_REQUEST['vn'];
// $vn= '111';
$login = empty($_REQUEST['loginname']) ? null : $_REQUEST['loginname'];

$an_parameters = ['vn' => $vn];
$hn_parameters = ['hn' => $hn];
//$loginname = $_SESSION['loginname'];
$values = ['loginname' => $loginname];


//echo $hn;




if ($login != $loginname) {
        session_start();
        session_destroy();
}



//-------------------------Doctor admission note
$sql = "SELECT *
                FROM `prs_stroke_fast_track`
                WHERE vn = :vn
                ORDER BY id ASC";
$stmt = $conn->prepare($sql);
$stmt->execute(['vn' => $vn]);
if ($row  = $stmt->fetch()) {
        $id = $row['id'];
} else {
        $id = null;
}

//echo  $id;

$id2 = '30'; //ลำดับในตาราง prs_link_menu
$sql = "SELECT *
                FROM `prs_link_menu`
                WHERE id = :id
                LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute(['id' => $id2]);
if ($row0  = $stmt->fetch()) {
        $menu_name = $row0['menu_name'];
        $production = $row0['production'];
} else {
        $menu_name = '-';
}



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
if ($id == null || $id != null) {
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
if ($id == null) {
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
                height: 500px;
        }

        canvas {
                padding-left: 0;
                padding-right: 0;
                margin-left: auto;
                margin-right: auto;
                display: block;
        }

        .label-width {
                width: 100px;
                display: inline-block;
                /* Needed for width to apply */
        }
</style>
<form id="my_form" action="" method="post" enctype="multipart/form-data">
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

                <!-- card -->
                <div class="card-group pb-3 ">
                        <!-- card -->
                        <div class="card">
                                <!-- card -->
                                <div class="card-body" style=" overflow-y: auto;">

                                        <div class="alert alert-success text-center col-sm-12" role="alert"><b>📋 General Information</b></div>
                                        <div class="row">

                                                &nbsp;&nbsp;&nbsp;&nbsp;<label>อายุ (ปี) :&nbsp;</label>
                                                <div>

                                                        <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="เฉพาะตัวเลข" name="age_y" id="age_y" value="<?= (isset($row_opdscreen['age_y'])  ? htmlspecialchars(round(($row_opdscreen['age_y']), 1)) : htmlspecialchars(round(($row['age_y']), 1))) ?>" readonly>
                                                </div> &nbsp;ปี&nbsp; น้ำหนักจริง (ABW) (kg) :&nbsp;<div>
                                                        <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="เฉพาะตัวเลข" name="bw" id="bw" value="<?= (isset($row_opdscreen['bw'])  ? htmlspecialchars(round(($row_opdscreen['bw']), 1)) : htmlspecialchars(round(($row['bw']), 1))) ?>" readonly>
                                                </div>&nbsp;Kg&nbsp;
                                        </div>
                                        <br>

                                        <div class="alert alert-success text-center col-sm-12" role="alert"><b>📋 NIHSS Evaluation</b></div>
                                        <div class="row">

                                                <div class="col-md-12">

                                                        <table id="bg-table" width="100%" style="border-collapse: collapse;font-size:13pt;margin-top:8px;">
                                                                <tr style="border:1px solid #000;margin: 45px;">

                                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;<b>Items</b></td>
                                                                </tr>

                                                                <tr style="border:1px solid #000;margin: 45px;">

                                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;<span>1a. Level of consciousness</span>

                                                                                <button style="background-color: green; color: white; border: none; padding: 4px 8px; border-radius: 4px; margin-left: 10px;">
                                                                                        <a data-toggle="collapse" data-parent="#accordion1" href="#collapse_1a" style="color: white; text-decoration: none;">
                                                                                                See Note
                                                                                        </a>
                                                                                </button>

                                                                                <div class="panel-group" id="accordion1" style="margin-top: 10px;">
                                                                                        <div class="panel panel-default">
                                                                                                <div id="collapse_1a" class="panel-collapse collapse in">
                                                                                                        <div class="panel-body">
                                                                                                                <div class="card-group pb-3 ">
                                                                                                                        <!-- card -->
                                                                                                                        <div class="card">
                                                                                                                                <!-- card -->
                                                                                                                                <div class="card-body" style=" overflow-y: auto;">
                                                                                                                                        <b>คำอธิบาย</b> <br>

                                                                                                                                        <b>ความหมาย</b> <br>

                                                                                                                                        ทดสอบ <br>
                                                                                                                                        1. <br>
                                                                                                                                        2. <br>

                                                                                                                                </div>
                                                                                                                        </div>
                                                                                                                </div>


                                                                                                        </div>
                                                                                                </div>
                                                                                        </div>
                                                                                </div>

                                                                        </td>



                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="level_of_consciousness0"
                                                                                                name="level_of_consciousness"
                                                                                                value="0"
                                                                                                <?php if ($row['level_of_consciousness'] == '0') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="level_of_consciousness0" style="display:inline-block; width: 120px;">
                                                                                                0 - Alert (A)
                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="level_of_consciousness1"
                                                                                                name="level_of_consciousness"
                                                                                                value="1"
                                                                                                <?php if ($row['level_of_consciousness'] == '1') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="level_of_consciousness1" style="display:inline-block; width: 120px;">
                                                                                                1 - Drowsy (V)
                                                                                        </label>
                                                                                </div>
                                                                        </td>


                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="level_of_consciousness2"
                                                                                                name="level_of_consciousness"
                                                                                                value="2"
                                                                                                <?php if ($row['level_of_consciousness'] == '2') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="level_of_consciousness2" style="display:inline-block; width: 120px;">
                                                                                                2 - Stupor (P)
                                                                                        </label>
                                                                                </div>
                                                                        </td>
                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="level_of_consciousness3"
                                                                                                name="level_of_consciousness"
                                                                                                value="3"
                                                                                                <?php if ($row['level_of_consciousness'] == '3') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="level_of_consciousness3" style="display:inline-block; width: 120px;">
                                                                                                3 - Coma (U)
                                                                                        </label>
                                                                                </div>
                                                                        </td>
                                                                </tr>



                                                                <tr style="border:1px solid #000;margin: 45px;">

                                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;1b. Two questions : อายุเท่าไหร่ เดือนอะไร</td>



                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="two_questions0"
                                                                                                name="two_questions"
                                                                                                value="0"
                                                                                                <?php if ($row['two_questions'] == '0') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="two_questions0" style="display:inline-block; width: 130px;">
                                                                                                0 - Both correct
                                                                                        </label>
                                                                                </div>
                                                                        </td>
                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="two_questions1"
                                                                                                name="two_questions"
                                                                                                value="1"
                                                                                                <?php if ($row['two_questions'] == '1') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="two_questions1" style="display:inline-block; width: 130px;">
                                                                                                1 - One correct
                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="two_questions2"
                                                                                                name="two_questions"
                                                                                                value="2"
                                                                                                <?php if ($row['two_questions'] == '2') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="two_questions2" style="display:inline-block; width: 140px;">
                                                                                                2 - None correct
                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                </tr>

                                                                <tr style="border:1px solid #000;margin: 45px;">

                                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;1c. Two commands : หลับตา-ลืมตา กำมือ-แบมือ</td>



                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="two_commands0"
                                                                                                name="two_commands"
                                                                                                value="0"
                                                                                                <?php if ($row['two_commands'] == '0') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="two_commands0" style="display:inline-block; width: 130px;">
                                                                                                0 - Both correct
                                                                                        </label>
                                                                                </div>
                                                                        </td>
                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="two_commands1"
                                                                                                name="two_commands"
                                                                                                value="1"
                                                                                                <?php if ($row['two_commands'] == '1') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="two_commands1" style="display:inline-block; width: 130px;">
                                                                                                1 - One correct
                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="two_commands2"
                                                                                                name="two_commands"
                                                                                                value="2"
                                                                                                <?php if ($row['two_commands'] == '2') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="two_commands2" style="display:inline-block; width: 140px;">
                                                                                                2 - None correct
                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                </tr>


                                                                <tr style="border:1px solid #000;margin: 45px;">

                                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;2. Best gaze</td>



                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="best_gaze0"
                                                                                                name="best_gaze"
                                                                                                value="0"
                                                                                                <?php if ($row['best_gaze'] == '0') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="best_gaze0" style="display:inline-block; width: 130px;">
                                                                                                0 - Normal
                                                                                        </label>
                                                                                </div>
                                                                        </td>
                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="best_gaze1"
                                                                                                name="best_gaze"
                                                                                                value="1"
                                                                                                <?php if ($row['best_gaze'] == '1') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="best_gaze1" style="display:inline-block; width: 160px;">
                                                                                                1 - Partial gaze palsy
                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="best_gaze2"
                                                                                                name="best_gaze"
                                                                                                value="2"
                                                                                                <?php if ($row['best_gaze'] == '2') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="best_gaze2" style="display:inline-block; width: 160px;">
                                                                                                2 - Forced deviation
                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                </tr>

                                                                <tr style="border:1px solid #000;margin: 45px;">

                                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;3. Best visual field</td>



                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="best_visual_field0"
                                                                                                name="best_visual_field"
                                                                                                value="0"
                                                                                                <?php if ($row['best_visual_field'] == '0') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="best_visual_field0" style="display:inline-block; width: 140px;">
                                                                                                0 - No visual loss
                                                                                        </label>
                                                                                </div>
                                                                        </td>
                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="best_visual_field1"
                                                                                                name="best_visual_field"
                                                                                                value="1"
                                                                                                <?php if ($row['best_visual_field'] == '1') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="best_visual_field1" style="display:inline-block; width: 180px;">
                                                                                                1 - Partial hemianopia
                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="best_visual_field2"
                                                                                                name="best_visual_field"
                                                                                                value="2"
                                                                                                <?php if ($row['best_visual_field'] == '2') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="best_visual_field2" style="display:inline-block; width: 200px;">
                                                                                                2 - Complete hemianopia
                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="best_visual_field3"
                                                                                                name="best_visual_field"
                                                                                                value="3"
                                                                                                <?php if ($row['best_visual_field'] == '3') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="best_visual_field3" style="display:inline-block; width: 250px;">
                                                                                                3 - Bilateral hemianopia/ Blind
                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                </tr>

                                                                <tr style="border:1px solid #000;margin: 45px;">

                                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;4. Facial palsy</td>



                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="facial_palsy0"
                                                                                                name="facial_palsy"
                                                                                                value="0"
                                                                                                <?php if ($row['facial_palsy'] == '0') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="facial_palsy0" style="display:inline-block; width: 140px;">
                                                                                                0 - Normal
                                                                                        </label>
                                                                                </div>
                                                                        </td>
                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="facial_palsy1"
                                                                                                name="facial_palsy"
                                                                                                value="1"
                                                                                                <?php if ($row['facial_palsy'] == '1') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="facial_palsy1" style="display:inline-block; width: 250px;">
                                                                                                1 - Minor มุมปากตก/ ไม่เท่ากันเมื่อยิ้ม
                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="facial_palsy2"
                                                                                                name="facial_palsy"
                                                                                                value="2"
                                                                                                <?php if ($row['facial_palsy'] == '2') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="facial_palsy2" style="display:inline-block; width: 250px;">
                                                                                                2 - Partial อ่อนแรงมาก แต่พอเคลื่อนไหวได้บ้าง
                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="facial_palsy3"
                                                                                                name="facial_palsy"
                                                                                                value="3"
                                                                                                <?php if ($row['facial_palsy'] == '3') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="facial_palsy3" style="display:inline-block; width: 250px;">
                                                                                                3 - Complete เคลื่อนไหวไม่ได้เลย
                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                </tr>

                                                                <tr style="border:1px solid #000;margin: 45px;">

                                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;5a. Best moter LEFT arm : ท่านั่ง เหยียดแขนออกในท่าคว่ำมือ 90 องศา, ท่านอน 45 องศา ค้างนาน 10 วินาที *</td>



                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="best_moter_left_arm0"
                                                                                                name="best_moter_left_arm"
                                                                                                value="0"
                                                                                                <?php if ($row['best_moter_left_arm'] == '0') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="best_moter_left_arm0" style="display:inline-block; width: 140px;">
                                                                                                0 - No drift ยกแขนค้างไม่ตก นาน 10 วินาที
                                                                                        </label>
                                                                                </div>
                                                                        </td>
                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="best_moter_left_arm1"
                                                                                                name="best_moter_left_arm"
                                                                                                value="1"
                                                                                                <?php if ($row['best_moter_left_arm'] == '1') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="best_moter_left_arm1" style="display:inline-block; width: 250px;">
                                                                                                1 - Drift ยกแขนได้ ไม่ถึง 10 วินาที
                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="best_moter_left_arm2"
                                                                                                name="best_moter_left_arm"
                                                                                                value="2"
                                                                                                <?php if ($row['best_moter_left_arm'] == '2') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="best_moter_left_arm2" style="display:inline-block; width: 250px;">
                                                                                                2 - Fall ยกแขนได้บ้าง แต่ไม่สามารถคงในตำแหน่งที่ต้องการ
                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="best_moter_left_arm3"
                                                                                                name="best_moter_left_arm"
                                                                                                value="3"
                                                                                                <?php if ($row['best_moter_left_arm'] == '3') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="best_moter_left_arm3" style="display:inline-block; width: 250px;">
                                                                                                3 - No effort against gravity ไม่สามารถยกแขนขึ้นได้
                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="best_moter_left_arm4"
                                                                                                name="best_moter_left_arm"
                                                                                                value="4"
                                                                                                <?php if ($row['best_moter_left_arm'] == '4') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="best_moter_left_arm4" style="display:inline-block; width: 250px;">
                                                                                                4 - No movement ไม่ขยับแขนเลย
                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                </tr>

                                                                <tr style="border:1px solid #000;margin: 45px;">

                                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;5b. Best moter RIGHT arm</td>



                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="best_moter_right_arm0"
                                                                                                name="best_moter_right_arm"
                                                                                                value="0"
                                                                                                <?php if ($row['best_moter_right_arm'] == '0') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="best_moter_right_arm0" style="display:inline-block; width: 140px;">
                                                                                                0 - No drift ยกแขนค้างไม่ตก นาน 10 วินาที
                                                                                        </label>
                                                                                </div>
                                                                        </td>
                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="best_moter_right_arm1"
                                                                                                name="best_moter_right_arm"
                                                                                                value="1"
                                                                                                <?php if ($row['best_moter_right_arm'] == '1') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="best_moter_right_arm1" style="display:inline-block; width: 250px;">
                                                                                                1 - Drift ยกแขนได้ ไม่ถึง 10 วินาที
                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="best_moter_right_arm2"
                                                                                                name="best_moter_right_arm"
                                                                                                value="2"
                                                                                                <?php if ($row['best_moter_right_arm'] == '2') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="best_moter_right_arm2" style="display:inline-block; width: 250px;">
                                                                                                2 - Fall ยกแขนได้บ้าง แต่ไม่สามารถคงในตำแหน่งที่ต้องการ
                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="best_moter_right_arm3"
                                                                                                name="best_moter_right_arm"
                                                                                                value="3"
                                                                                                <?php if ($row['best_moter_right_arm'] == '3') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="best_moter_right_arm3" style="display:inline-block; width: 250px;">
                                                                                                3 - No effort against gravity ไม่สามารถยกแขนขึ้นได้
                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="best_moter_right_arm4"
                                                                                                name="best_moter_right_arm"
                                                                                                value="4"
                                                                                                <?php if ($row['best_moter_right_arm'] == '4') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="best_moter_right_arm4" style="display:inline-block; width: 250px;">
                                                                                                4 - No movement ไม่ขยับแขนเลย
                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                </tr>

                                                                <tr style="border:1px solid #000;margin: 45px;">

                                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;6a. Best moter LEFT leg : ท่านอนยกขา 45 องศา ค้างนาน 5 วินาที *</td>

                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="best_moter_left_leg0"
                                                                                                name="best_moter_left_leg"
                                                                                                value="0"
                                                                                                <?php if ($row['best_moter_left_leg'] == '0') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="best_moter_left_leg0" style="display:inline-block; width: 140px;">
                                                                                                0 - No drift ยกขาค้างได้นาน 5 วินาที
                                                                                        </label>
                                                                                </div>
                                                                        </td>
                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="best_moter_left_leg1"
                                                                                                name="best_moter_left_leg"
                                                                                                value="1"
                                                                                                <?php if ($row['best_moter_left_leg'] == '1') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="best_moter_left_leg1" style="display:inline-block; width: 250px;">
                                                                                                1 - Drift ยกขาค้างได้ แต่ไม่ถึง 5 วินาที
                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="best_moter_left_leg2"
                                                                                                name="best_moter_left_leg"
                                                                                                value="2"
                                                                                                <?php if ($row['best_moter_left_leg'] == '2') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="best_moter_left_leg2" style="display:inline-block; width: 250px;">
                                                                                                2 - Fall ยกขาได้บ้าง แต่ไม่สามารถคงในตำแหน่งที่ต้องการ
                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="best_moter_left_leg3"
                                                                                                name="best_moter_left_leg"
                                                                                                value="3"
                                                                                                <?php if ($row['best_moter_left_leg'] == '3') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="best_moter_left_leg3" style="display:inline-block; width: 250px;">
                                                                                                3 - No effort against gravity ไม่สามารถยกขาขึ้นได้
                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="best_moter_left_leg4"
                                                                                                name="best_moter_left_leg"
                                                                                                value="4"
                                                                                                <?php if ($row['best_moter_left_leg'] == '4') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="best_moter_left_leg4" style="display:inline-block; width: 250px;">
                                                                                                4 - No movement ไม่ขยับขาเลย
                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                </tr>

                                                                <tr style="border:1px solid #000;margin: 45px;">

                                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;6b. Best moter RIGHT leg</td>



                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="best_moter_right_leg0"
                                                                                                name="best_moter_right_leg"
                                                                                                value="0"
                                                                                                <?php if ($row['best_moter_right_leg'] == '0') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="best_moter_right_leg0" style="display:inline-block; width: 140px;">
                                                                                                0 - No drift ยกขาค้างได้นาน 5 วินาที
                                                                                        </label>
                                                                                </div>
                                                                        </td>
                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="best_moter_right_leg1"
                                                                                                name="best_moter_right_leg"
                                                                                                value="1"
                                                                                                <?php if ($row['best_moter_right_leg'] == '1') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="best_moter_right_leg1" style="display:inline-block; width: 250px;">
                                                                                                1 - Drift ยกขาค้างได้ แต่ไม่ถึง 5 วินาที
                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="best_moter_right_leg2"
                                                                                                name="best_moter_right_leg"
                                                                                                value="2"
                                                                                                <?php if ($row['best_moter_right_leg'] == '2') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="best_moter_right_leg2" style="display:inline-block; width: 250px;">
                                                                                                2 - Fall ยกขาได้บ้าง แต่ไม่สามารถคงในตำแหน่งที่ต้องการ
                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="best_moter_right_leg3"
                                                                                                name="best_moter_right_leg"
                                                                                                value="3"
                                                                                                <?php if ($row['best_moter_right_leg'] == '3') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="best_moter_right_leg3" style="display:inline-block; width: 250px;">
                                                                                                3 - No effort against gravity ไม่สามารถยกขาขึ้นได้
                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="best_moter_right_leg4"
                                                                                                name="best_moter_right_leg"
                                                                                                value="4"
                                                                                                <?php if ($row['best_moter_right_leg'] == '4') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="best_moter_right_leg4" style="display:inline-block; width: 250px;">
                                                                                                4 - No movement ไม่ขยับขาเลย
                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                </tr>

                                                                <tr style="border:1px solid #000;margin: 45px;">

                                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;7. Ataxia</td>



                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="ataxia0"
                                                                                                name="ataxia"
                                                                                                value="0"
                                                                                                <?php if ($row['ataxia'] == '0') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="ataxia0" style="display:inline-block; width: 140px;">
                                                                                                0 - No ataxia
                                                                                        </label>
                                                                                </div>
                                                                        </td>
                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="ataxia1"
                                                                                                name="ataxia"
                                                                                                value="1"
                                                                                                <?php if ($row['ataxia'] == '1') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="ataxia1" style="display:inline-block; width: 250px;">
                                                                                                1 - Ataxia one limb
                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="ataxia2"
                                                                                                name="ataxia"
                                                                                                value="2"
                                                                                                <?php if ($row['ataxia'] == '2') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="ataxia2" style="display:inline-block; width: 250px;">
                                                                                                2 - Ataxia two limbs
                                                                                        </label>
                                                                                </div>
                                                                        </td>
                                                                </tr>

                                                                <tr style="border:1px solid #000;margin: 45px;">

                                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;8. Sensory</td>



                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="sensory0"
                                                                                                name="sensory"
                                                                                                value="0"
                                                                                                <?php if ($row['sensory'] == '0') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="sensory0" style="display:inline-block; width: 140px;">
                                                                                                0 - Normal
                                                                                        </label>
                                                                                </div>
                                                                        </td>
                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="sensory1"
                                                                                                name="sensory"
                                                                                                value="1"
                                                                                                <?php if ($row['sensory'] == '1') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="sensory1" style="display:inline-block; width: 250px;">
                                                                                                1 - Partial loss รู้สึกบ้าง แต่ไม่เท่ากัน
                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="sensory2"
                                                                                                name="sensory"
                                                                                                value="2"
                                                                                                <?php if ($row['sensory'] == '2') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="sensory2" style="display:inline-block; width: 250px;">
                                                                                                2 - Dense loss ไม่รู้สึกเลย
                                                                                        </label>
                                                                                </div>
                                                                        </td>
                                                                </tr>


                                                                <tr style="border:1px solid #000;margin: 45px;">

                                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;9. Best language aphasia</td>



                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="best_language_aphasia0"
                                                                                                name="best_language_aphasia"
                                                                                                value="0"
                                                                                                <?php if ($row['best_language_aphasia'] == '0') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="best_language_aphasia0" style="display:inline-block; width: 140px;">
                                                                                                0 - No aphasia
                                                                                        </label>
                                                                                </div>
                                                                        </td>
                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="best_language_aphasia1"
                                                                                                name="best_language_aphasia"
                                                                                                value="1"
                                                                                                <?php if ($row['best_language_aphasia'] == '1') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="best_language_aphasia1" style="display:inline-block; width: 250px;">
                                                                                                1 - Mild to moderate
                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="best_language_aphasia2"
                                                                                                name="best_language_aphasia"
                                                                                                value="2"
                                                                                                <?php if ($row['best_language_aphasia'] == '2') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="best_language_aphasia2" style="display:inline-block; width: 250px;">
                                                                                                2 - Severe
                                                                                        </label>
                                                                                </div>
                                                                        </td>
                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="best_language_aphasia3"
                                                                                                name="best_language_aphasia"
                                                                                                value="3"
                                                                                                <?php if ($row['best_language_aphasia'] == '3') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="best_language_aphasia3" style="display:inline-block; width: 250px;">
                                                                                                3 - Mute gobal aphasia
                                                                                        </label>
                                                                                </div>
                                                                        </td>
                                                                </tr>

                                                                <tr style="border:1px solid #000;margin: 45px;">

                                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;10. Dysarthria</td>



                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="dysarthria0"
                                                                                                name="dysarthria"
                                                                                                value="0"
                                                                                                <?php if ($row['dysarthria'] == '0') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="dysarthria0" style="display:inline-block; width: 140px;">
                                                                                                0 - Normal articulation
                                                                                        </label>
                                                                                </div>
                                                                        </td>
                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="dysarthria1"
                                                                                                name="dysarthria"
                                                                                                value="1"
                                                                                                <?php if ($row['dysarthria'] == '1') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="dysarthria1" style="display:inline-block; width: 250px;">
                                                                                                1 - Mild to moderate พอเข้าใจได้
                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="dysarthria2"
                                                                                                name="dysarthria"
                                                                                                value="2"
                                                                                                <?php if ($row['dysarthria'] == '2') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="dysarthria2" style="display:inline-block; width: 250px;">
                                                                                                2 - Severe ไม่สามารถเข้าใจได้
                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                </tr>

                                                                <tr style="border:1px solid #000;margin: 45px;">

                                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;11. Neglect</td>



                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="neglect0"
                                                                                                name="neglect"
                                                                                                value="0"
                                                                                                <?php if ($row['neglect'] == '0') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="neglect0" style="display:inline-block; width: 140px;">
                                                                                                0 - No neglect
                                                                                        </label>
                                                                                </div>
                                                                        </td>
                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="neglect1"
                                                                                                name="neglect"
                                                                                                value="1"
                                                                                                <?php if ($row['neglect'] == '1') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="neglect1" style="display:inline-block; width: 250px;">
                                                                                                1 - Partial neglect ไม่สนสิ่งกระตุ้นบางอย่าง
                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="neglect2"
                                                                                                name="neglect"
                                                                                                value="2"
                                                                                                <?php if ($row['neglect'] == '2') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                        <label class="custom-control-label" for="neglect2" style="display:inline-block; width: 250px;">
                                                                                                2 - Complete neglect ไม่สนสิ่งกระตุ้นทั้งหมด
                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                </tr>


                                                        </table>
                                                </div>

                                                <br>
                                                <div class="col-sm-6 offset-md-4" id="score_total_result"></div>
                                                <div class="col-sm-1 offset-md-1">
                                                </div>
                                                <br>

                                                <div class="alert alert-success text-center col-sm-12">
                                                        <button style="background-color: green; color: white; border: none; padding: 4px 8px; border-radius: 4px; margin-left: 10px;">
                                                                <a data-toggle="collapse" data-parent="#accordion11" href="#collapse_1nihss" style="color: white; text-decoration: none;">
                                                                        📋 NIHSS Evaluation (After)
                                                                </a>
                                                        </button>

                                                </div>


                                                <div class="panel-group" id="accordion11" style="margin-top: 10px;">
                                                        <div class="panel panel-default">
                                                                <div id="collapse_1nihss" class="panel-collapse collapse in">
                                                                        <div class="panel-body">
                                                                                <div class="card-group pb-3 ">
                                                                                        <!-- card -->
                                                                                        <div class="card">
                                                                                                <!-- card -->
                                                                                                <div class="card-body" style=" overflow-y: auto;">

                                                                                                        <div class="col-md-12">

                                                                                                                <table id="bg-table" width="100%" style="border-collapse: collapse;font-size:13pt;margin-top:8px;">
                                                                                                                        <tr style="border:1px solid #000;margin: 45px;">

                                                                                                                                <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;<b>Items</b></td>
                                                                                                                        </tr>

                                                                                                                        <tr style="border:1px solid #000;margin: 45px;">

                                                                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;<span>1a. Level of consciousness</span>

                                                                                                                                        <button style="background-color: green; color: white; border: none; padding: 4px 8px; border-radius: 4px; margin-left: 10px;">
                                                                                                                                                <a data-toggle="collapse" data-parent="#accordion1" href="#collapse_1a" style="color: white; text-decoration: none;">
                                                                                                                                                        See Note
                                                                                                                                                </a>
                                                                                                                                        </button>

                                                                                                                                        <div class="panel-group" id="accordion1" style="margin-top: 10px;">
                                                                                                                                                <div class="panel panel-default">
                                                                                                                                                        <div id="collapse_1a" class="panel-collapse collapse in">
                                                                                                                                                                <div class="panel-body">
                                                                                                                                                                        <div class="card-group pb-3 ">
                                                                                                                                                                                <!-- card -->
                                                                                                                                                                                <div class="card">
                                                                                                                                                                                        <!-- card -->
                                                                                                                                                                                        <div class="card-body" style=" overflow-y: auto;">
                                                                                                                                                                                                <b>คำอธิบาย</b> <br>

                                                                                                                                                                                                <b>ความหมาย</b> <br>

                                                                                                                                                                                                ทดสอบ <br>
                                                                                                                                                                                                1. <br>
                                                                                                                                                                                                2. <br>

                                                                                                                                                                                        </div>
                                                                                                                                                                                </div>
                                                                                                                                                                        </div>


                                                                                                                                                                </div>
                                                                                                                                                        </div>
                                                                                                                                                </div>
                                                                                                                                        </div>

                                                                                                                                </td>



                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_level_of_consciousness0"
                                                                                                                                                        name="af_level_of_consciousness"
                                                                                                                                                        value="0"
                                                                                                                                                        <?php if ($row['af_level_of_consciousness'] == '0') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_level_of_consciousness0" style="display:inline-block; width: 120px;">
                                                                                                                                                        0 - Alert (A)
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>

                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_level_of_consciousness1"
                                                                                                                                                        name="af_level_of_consciousness"
                                                                                                                                                        value="1"
                                                                                                                                                        <?php if ($row['af_level_of_consciousness'] == '1') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_level_of_consciousness1" style="display:inline-block; width: 120px;">
                                                                                                                                                        1 - Drowsy (V)
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>


                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_level_of_consciousness2"
                                                                                                                                                        name="af_level_of_consciousness"
                                                                                                                                                        value="2"
                                                                                                                                                        <?php if ($row['af_level_of_consciousness'] == '2') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_level_of_consciousness2" style="display:inline-block; width: 120px;">
                                                                                                                                                        2 - Stupor (P)
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>
                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_level_of_consciousness3"
                                                                                                                                                        name="af_level_of_consciousness"
                                                                                                                                                        value="3"
                                                                                                                                                        <?php if ($row['af_level_of_consciousness'] == '3') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_level_of_consciousness3" style="display:inline-block; width: 120px;">
                                                                                                                                                        3 - Coma (U)
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>
                                                                                                                        </tr>



                                                                                                                        <tr style="border:1px solid #000;margin: 45px;">

                                                                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;1b. Two questions : อายุเท่าไหร่ เดือนอะไร</td>



                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_two_questions0"
                                                                                                                                                        name="af_two_questions"
                                                                                                                                                        value="0"
                                                                                                                                                        <?php if ($row['af_two_questions'] == '0') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_two_questions0" style="display:inline-block; width: 130px;">
                                                                                                                                                        0 - Both correct
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>
                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_two_questions1"
                                                                                                                                                        name="af_two_questions"
                                                                                                                                                        value="1"
                                                                                                                                                        <?php if ($row['af_two_questions'] == '1') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_two_questions1" style="display:inline-block; width: 130px;">
                                                                                                                                                        1 - One correct
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>

                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_two_questions2"
                                                                                                                                                        name="af_two_questions"
                                                                                                                                                        value="2"
                                                                                                                                                        <?php if ($row['af_two_questions'] == '2') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_two_questions2" style="display:inline-block; width: 140px;">
                                                                                                                                                        2 - None correct
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>

                                                                                                                        </tr>

                                                                                                                        <tr style="border:1px solid #000;margin: 45px;">

                                                                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;1c. Two commands : หลับตา-ลืมตา กำมือ-แบมือ</td>



                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_two_commands0"
                                                                                                                                                        name="af_two_commands"
                                                                                                                                                        value="0"
                                                                                                                                                        <?php if ($row['af_two_commands'] == '0') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_two_commands0" style="display:inline-block; width: 130px;">
                                                                                                                                                        0 - Both correct
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>
                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_two_commands1"
                                                                                                                                                        name="af_two_commands"
                                                                                                                                                        value="1"
                                                                                                                                                        <?php if ($row['af_two_commands'] == '1') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_two_commands1" style="display:inline-block; width: 130px;">
                                                                                                                                                        1 - One correct
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>

                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_two_commands2"
                                                                                                                                                        name="af_two_commands"
                                                                                                                                                        value="2"
                                                                                                                                                        <?php if ($row['af_two_commands'] == '2') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_two_commands2" style="display:inline-block; width: 140px;">
                                                                                                                                                        2 - None correct
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>

                                                                                                                        </tr>


                                                                                                                        <tr style="border:1px solid #000;margin: 45px;">

                                                                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;2. Best gaze</td>



                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_best_gaze0"
                                                                                                                                                        name="af_best_gaze"
                                                                                                                                                        value="0"
                                                                                                                                                        <?php if ($row['af_best_gaze'] == '0') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_best_gaze0" style="display:inline-block; width: 130px;">
                                                                                                                                                        0 - Normal
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>
                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_best_gaze1"
                                                                                                                                                        name="af_best_gaze"
                                                                                                                                                        value="1"
                                                                                                                                                        <?php if ($row['af_best_gaze'] == '1') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_best_gaze1" style="display:inline-block; width: 160px;">
                                                                                                                                                        1 - Partial gaze palsy
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>

                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_best_gaze2"
                                                                                                                                                        name="af_best_gaze"
                                                                                                                                                        value="2"
                                                                                                                                                        <?php if ($row['af_best_gaze'] == '2') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_best_gaze2" style="display:inline-block; width: 160px;">
                                                                                                                                                        2 - Forced deviation
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>

                                                                                                                        </tr>

                                                                                                                        <tr style="border:1px solid #000;margin: 45px;">

                                                                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;3. Best visual field</td>



                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_best_visual_field0"
                                                                                                                                                        name="af_best_visual_field"
                                                                                                                                                        value="0"
                                                                                                                                                        <?php if ($row['af_best_visual_field'] == '0') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_best_visual_field0" style="display:inline-block; width: 140px;">
                                                                                                                                                        0 - No visual loss
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>
                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_best_visual_field1"
                                                                                                                                                        name="af_best_visual_field"
                                                                                                                                                        value="1"
                                                                                                                                                        <?php if ($row['af_best_visual_field'] == '1') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_best_visual_field1" style="display:inline-block; width: 180px;">
                                                                                                                                                        1 - Partial hemianopia
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>

                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_best_visual_field2"
                                                                                                                                                        name="af_best_visual_field"
                                                                                                                                                        value="2"
                                                                                                                                                        <?php if ($row['af_best_visual_field'] == '2') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_best_visual_field2" style="display:inline-block; width: 200px;">
                                                                                                                                                        2 - Complete hemianopia
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>

                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_best_visual_field3"
                                                                                                                                                        name="af_best_visual_field"
                                                                                                                                                        value="3"
                                                                                                                                                        <?php if ($row['af_best_visual_field'] == '3') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_best_visual_field3" style="display:inline-block; width: 250px;">
                                                                                                                                                        3 - Bilateral hemianopia/ Blind
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>

                                                                                                                        </tr>

                                                                                                                        <tr style="border:1px solid #000;margin: 45px;">

                                                                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;4. Facial palsy</td>



                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_facial_palsy0"
                                                                                                                                                        name="af_facial_palsy"
                                                                                                                                                        value="0"
                                                                                                                                                        <?php if ($row['af_facial_palsy'] == '0') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_facial_palsy0" style="display:inline-block; width: 140px;">
                                                                                                                                                        0 - Normal
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>
                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_facial_palsy1"
                                                                                                                                                        name="af_facial_palsy"
                                                                                                                                                        value="1"
                                                                                                                                                        <?php if ($row['af_facial_palsy'] == '1') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_facial_palsy1" style="display:inline-block; width: 250px;">
                                                                                                                                                        1 - Minor มุมปากตก/ ไม่เท่ากันเมื่อยิ้ม
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>

                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_facial_palsy2"
                                                                                                                                                        name="af_facial_palsy"
                                                                                                                                                        value="2"
                                                                                                                                                        <?php if ($row['af_facial_palsy'] == '2') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_facial_palsy2" style="display:inline-block; width: 250px;">
                                                                                                                                                        2 - Partial อ่อนแรงมาก แต่พอเคลื่อนไหวได้บ้าง
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>

                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_facial_palsy3"
                                                                                                                                                        name="af_facial_palsy"
                                                                                                                                                        value="3"
                                                                                                                                                        <?php if ($row['af_facial_palsy'] == '3') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_facial_palsy3" style="display:inline-block; width: 250px;">
                                                                                                                                                        3 - Complete เคลื่อนไหวไม่ได้เลย
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>

                                                                                                                        </tr>

                                                                                                                        <tr style="border:1px solid #000;margin: 45px;">

                                                                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;5a. Best moter LEFT arm : ท่านั่ง เหยียดแขนออกในท่าคว่ำมือ 90 องศา, ท่านอน 45 องศา ค้างนาน 10 วินาที *</td>



                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_best_moter_left_arm0"
                                                                                                                                                        name="af_best_moter_left_arm"
                                                                                                                                                        value="0"
                                                                                                                                                        <?php if ($row['af_best_moter_left_arm'] == '0') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_best_moter_left_arm0" style="display:inline-block; width: 140px;">
                                                                                                                                                        0 - No drift ยกแขนค้างไม่ตก นาน 10 วินาที
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>
                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_best_moter_left_arm1"
                                                                                                                                                        name="af_best_moter_left_arm"
                                                                                                                                                        value="1"
                                                                                                                                                        <?php if ($row['af_best_moter_left_arm'] == '1') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_best_moter_left_arm1" style="display:inline-block; width: 250px;">
                                                                                                                                                        1 - Drift ยกแขนได้ ไม่ถึง 10 วินาที
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>

                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_best_moter_left_arm2"
                                                                                                                                                        name="af_best_moter_left_arm"
                                                                                                                                                        value="2"
                                                                                                                                                        <?php if ($row['af_best_moter_left_arm'] == '2') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_best_moter_left_arm2" style="display:inline-block; width: 250px;">
                                                                                                                                                        2 - Fall ยกแขนได้บ้าง แต่ไม่สามารถคงในตำแหน่งที่ต้องการ
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>

                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_best_moter_left_arm3"
                                                                                                                                                        name="af_best_moter_left_arm"
                                                                                                                                                        value="3"
                                                                                                                                                        <?php if ($row['af_best_moter_left_arm'] == '3') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_best_moter_left_arm3" style="display:inline-block; width: 250px;">
                                                                                                                                                        3 - No effort against gravity ไม่สามารถยกแขนขึ้นได้
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>

                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_best_moter_left_arm4"
                                                                                                                                                        name="af_best_moter_left_arm"
                                                                                                                                                        value="4"
                                                                                                                                                        <?php if ($row['af_best_moter_left_arm'] == '4') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_best_moter_left_arm4" style="display:inline-block; width: 250px;">
                                                                                                                                                        4 - No movement ไม่ขยับแขนเลย
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>

                                                                                                                        </tr>

                                                                                                                        <tr style="border:1px solid #000;margin: 45px;">

                                                                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;5b. Best moter RIGHT arm</td>



                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_best_moter_right_arm0"
                                                                                                                                                        name="af_best_moter_right_arm"
                                                                                                                                                        value="0"
                                                                                                                                                        <?php if ($row['af_best_moter_right_arm'] == '0') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_best_moter_right_arm0" style="display:inline-block; width: 140px;">
                                                                                                                                                        0 - No drift ยกแขนค้างไม่ตก นาน 10 วินาที
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>
                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_best_moter_right_arm1"
                                                                                                                                                        name="af_best_moter_right_arm"
                                                                                                                                                        value="1"
                                                                                                                                                        <?php if ($row['af_best_moter_right_arm'] == '1') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_best_moter_right_arm1" style="display:inline-block; width: 250px;">
                                                                                                                                                        1 - Drift ยกแขนได้ ไม่ถึง 10 วินาที
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>

                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_best_moter_right_arm2"
                                                                                                                                                        name="af_best_moter_right_arm"
                                                                                                                                                        value="2"
                                                                                                                                                        <?php if ($row['af_best_moter_right_arm'] == '2') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_best_moter_right_arm2" style="display:inline-block; width: 250px;">
                                                                                                                                                        2 - Fall ยกแขนได้บ้าง แต่ไม่สามารถคงในตำแหน่งที่ต้องการ
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>

                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_best_moter_right_arm3"
                                                                                                                                                        name="af_best_moter_right_arm"
                                                                                                                                                        value="3"
                                                                                                                                                        <?php if ($row['af_best_moter_right_arm'] == '3') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_best_moter_right_arm3" style="display:inline-block; width: 250px;">
                                                                                                                                                        3 - No effort against gravity ไม่สามารถยกแขนขึ้นได้
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>

                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_best_moter_right_arm4"
                                                                                                                                                        name="af_best_moter_right_arm"
                                                                                                                                                        value="4"
                                                                                                                                                        <?php if ($row['af_best_moter_right_arm'] == '4') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_best_moter_right_arm4" style="display:inline-block; width: 250px;">
                                                                                                                                                        4 - No movement ไม่ขยับแขนเลย
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>

                                                                                                                        </tr>

                                                                                                                        <tr style="border:1px solid #000;margin: 45px;">

                                                                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;6a. Best moter LEFT leg : ท่านอนยกขา 45 องศา ค้างนาน 5 วินาที *</td>

                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_best_moter_left_leg0"
                                                                                                                                                        name="af_best_moter_left_leg"
                                                                                                                                                        value="0"
                                                                                                                                                        <?php if ($row['af_best_moter_left_leg'] == '0') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_best_moter_left_leg0" style="display:inline-block; width: 140px;">
                                                                                                                                                        0 - No drift ยกขาค้างได้นาน 5 วินาที
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>
                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_best_moter_left_leg1"
                                                                                                                                                        name="af_best_moter_left_leg"
                                                                                                                                                        value="1"
                                                                                                                                                        <?php if ($row['af_best_moter_left_leg'] == '1') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_best_moter_left_leg1" style="display:inline-block; width: 250px;">
                                                                                                                                                        1 - Drift ยกขาค้างได้ แต่ไม่ถึง 5 วินาที
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>

                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_best_moter_left_leg2"
                                                                                                                                                        name="af_best_moter_left_leg"
                                                                                                                                                        value="2"
                                                                                                                                                        <?php if ($row['af_best_moter_left_leg'] == '2') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_best_moter_left_leg2" style="display:inline-block; width: 250px;">
                                                                                                                                                        2 - Fall ยกขาได้บ้าง แต่ไม่สามารถคงในตำแหน่งที่ต้องการ
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>

                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_best_moter_left_leg3"
                                                                                                                                                        name="af_best_moter_left_leg"
                                                                                                                                                        value="3"
                                                                                                                                                        <?php if ($row['af_best_moter_left_leg'] == '3') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_best_moter_left_leg3" style="display:inline-block; width: 250px;">
                                                                                                                                                        3 - No effort against gravity ไม่สามารถยกขาขึ้นได้
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>

                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_best_moter_left_leg4"
                                                                                                                                                        name="af_best_moter_left_leg"
                                                                                                                                                        value="4"
                                                                                                                                                        <?php if ($row['af_best_moter_left_leg'] == '4') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_best_moter_left_leg4" style="display:inline-block; width: 250px;">
                                                                                                                                                        4 - No movement ไม่ขยับขาเลย
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>

                                                                                                                        </tr>

                                                                                                                        <tr style="border:1px solid #000;margin: 45px;">

                                                                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;6b. Best moter RIGHT leg</td>



                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_best_moter_right_leg0"
                                                                                                                                                        name="af_best_moter_right_leg"
                                                                                                                                                        value="0"
                                                                                                                                                        <?php if ($row['af_best_moter_right_leg'] == '0') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_best_moter_right_leg0" style="display:inline-block; width: 140px;">
                                                                                                                                                        0 - No drift ยกขาค้างได้นาน 5 วินาที
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>
                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_best_moter_right_leg1"
                                                                                                                                                        name="af_best_moter_right_leg"
                                                                                                                                                        value="1"
                                                                                                                                                        <?php if ($row['af_best_moter_right_leg'] == '1') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_best_moter_right_leg1" style="display:inline-block; width: 250px;">
                                                                                                                                                        1 - Drift ยกขาค้างได้ แต่ไม่ถึง 5 วินาที
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>

                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_best_moter_right_leg2"
                                                                                                                                                        name="af_best_moter_right_leg"
                                                                                                                                                        value="2"
                                                                                                                                                        <?php if ($row['af_best_moter_right_leg'] == '2') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_best_moter_right_leg2" style="display:inline-block; width: 250px;">
                                                                                                                                                        2 - Fall ยกขาได้บ้าง แต่ไม่สามารถคงในตำแหน่งที่ต้องการ
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>

                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_best_moter_right_leg3"
                                                                                                                                                        name="af_best_moter_right_leg"
                                                                                                                                                        value="3"
                                                                                                                                                        <?php if ($row['af_best_moter_right_leg'] == '3') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_best_moter_right_leg3" style="display:inline-block; width: 250px;">
                                                                                                                                                        3 - No effort against gravity ไม่สามารถยกขาขึ้นได้
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>

                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_best_moter_right_leg4"
                                                                                                                                                        name="af_best_moter_right_leg"
                                                                                                                                                        value="4"
                                                                                                                                                        <?php if ($row['af_best_moter_right_leg'] == '4') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_best_moter_right_leg4" style="display:inline-block; width: 250px;">
                                                                                                                                                        4 - No movement ไม่ขยับขาเลย
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>

                                                                                                                        </tr>

                                                                                                                        <tr style="border:1px solid #000;margin: 45px;">

                                                                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;7. Ataxia</td>



                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_ataxia0"
                                                                                                                                                        name="af_ataxia"
                                                                                                                                                        value="0"
                                                                                                                                                        <?php if ($row['af_ataxia'] == '0') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_ataxia0" style="display:inline-block; width: 140px;">
                                                                                                                                                        0 - No ataxia
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>
                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_ataxia1"
                                                                                                                                                        name="af_ataxia"
                                                                                                                                                        value="1"
                                                                                                                                                        <?php if ($row['af_ataxia'] == '1') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_ataxia1" style="display:inline-block; width: 250px;">
                                                                                                                                                        1 - Ataxia one limb
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>

                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_ataxia2"
                                                                                                                                                        name="af_ataxia"
                                                                                                                                                        value="2"
                                                                                                                                                        <?php if ($row['af_ataxia'] == '2') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_ataxia2" style="display:inline-block; width: 250px;">
                                                                                                                                                        2 - Ataxia two limbs
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>
                                                                                                                        </tr>

                                                                                                                        <tr style="border:1px solid #000;margin: 45px;">

                                                                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;8. Sensory</td>



                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_sensory0"
                                                                                                                                                        name="af_sensory"
                                                                                                                                                        value="0"
                                                                                                                                                        <?php if ($row['af_sensory'] == '0') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_sensory0" style="display:inline-block; width: 140px;">
                                                                                                                                                        0 - Normal
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>
                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_sensory1"
                                                                                                                                                        name="af_sensory"
                                                                                                                                                        value="1"
                                                                                                                                                        <?php if ($row['af_sensory'] == '1') echo 'checked="checked"'; ?> oninput="oninputCheckValue()">
                                                                                                                                                <label class="custom-control-label" for="af_sensory1" style="display:inline-block; width: 250px;">
                                                                                                                                                        1 - Partial loss รู้สึกบ้าง แต่ไม่เท่ากัน
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>

                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_sensory2"
                                                                                                                                                        name="af_sensory"
                                                                                                                                                        value="2"
                                                                                                                                                        <?php if ($row['af_sensory'] == '2') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_sensory2" style="display:inline-block; width: 250px;">
                                                                                                                                                        2 - Dense loss ไม่รู้สึกเลย
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>
                                                                                                                        </tr>


                                                                                                                        <tr style="border:1px solid #000;margin: 45px;">

                                                                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;9. Best language aphasia</td>



                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_best_language_aphasia0"
                                                                                                                                                        name="af_best_language_aphasia"
                                                                                                                                                        value="0"
                                                                                                                                                        <?php if ($row['af_best_language_aphasia'] == '0') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_best_language_aphasia0" style="display:inline-block; width: 140px;">
                                                                                                                                                        0 - No aphasia
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>
                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_best_language_aphasia1"
                                                                                                                                                        name="af_best_language_aphasia"
                                                                                                                                                        value="1"
                                                                                                                                                        <?php if ($row['af_best_language_aphasia'] == '1') echo 'checked="checked"'; ?> oninput="oninputCheckValu2()">
                                                                                                                                                <label class="custom-control-label" for="af_best_language_aphasia1" style="display:inline-block; width: 250px;">
                                                                                                                                                        1 - Mild to moderate
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>

                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_best_language_aphasia2"
                                                                                                                                                        name="af_best_language_aphasia"
                                                                                                                                                        value="2"
                                                                                                                                                        <?php if ($row['af_best_language_aphasia'] == '2') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_best_language_aphasia2" style="display:inline-block; width: 250px;">
                                                                                                                                                        2 - Severe
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>
                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_best_language_aphasia3"
                                                                                                                                                        name="af_best_language_aphasia"
                                                                                                                                                        value="3"
                                                                                                                                                        <?php if ($row['af_best_language_aphasia'] == '3') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_best_language_aphasia3" style="display:inline-block; width: 250px;">
                                                                                                                                                        3 - Mute gobal aphasia
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>
                                                                                                                        </tr>

                                                                                                                        <tr style="border:1px solid #000;margin: 45px;">

                                                                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;10. Dysarthria</td>



                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_dysarthria0"
                                                                                                                                                        name="af_dysarthria"
                                                                                                                                                        value="0"
                                                                                                                                                        <?php if ($row['af_dysarthria'] == '0') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_dysarthria0" style="display:inline-block; width: 140px;">
                                                                                                                                                        0 - Normal articulation
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>
                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_dysarthria1"
                                                                                                                                                        name="af_dysarthria"
                                                                                                                                                        value="1"
                                                                                                                                                        <?php if ($row['af_dysarthria'] == '1') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_dysarthria1" style="display:inline-block; width: 250px;">
                                                                                                                                                        1 - Mild to moderate พอเข้าใจได้
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>

                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_dysarthria2"
                                                                                                                                                        name="af_dysarthria"
                                                                                                                                                        value="2"
                                                                                                                                                        <?php if ($row['af_dysarthria'] == '2') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_dysarthria2" style="display:inline-block; width: 250px;">
                                                                                                                                                        2 - Severe ไม่สามารถเข้าใจได้
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>

                                                                                                                        </tr>

                                                                                                                        <tr style="border:1px solid #000;margin: 45px;">

                                                                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;11. Neglect</td>



                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_neglect0"
                                                                                                                                                        name="af_neglect"
                                                                                                                                                        value="0"
                                                                                                                                                        <?php if ($row['af_neglect'] == '0') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_neglect0" style="display:inline-block; width: 140px;">
                                                                                                                                                        0 - No neglect
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>
                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_neglect1"
                                                                                                                                                        name="af_neglect"
                                                                                                                                                        value="1"
                                                                                                                                                        <?php if ($row['af_neglect'] == '1') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_neglect1" style="display:inline-block; width: 250px;">
                                                                                                                                                        1 - Partial neglect ไม่สนสิ่งกระตุ้นบางอย่าง
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>

                                                                                                                                <td style="text-align:left; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                                                                        <div class="custom-control custom-radio">
                                                                                                                                                <input type="radio"
                                                                                                                                                        class="custom-control-input"
                                                                                                                                                        id="af_neglect2"
                                                                                                                                                        name="af_neglect"
                                                                                                                                                        value="2"
                                                                                                                                                        <?php if ($row['af_neglect'] == '2') echo 'checked="checked"'; ?> oninput="oninputCheckValue2()">
                                                                                                                                                <label class="custom-control-label" for="af_neglect2" style="display:inline-block; width: 250px;">
                                                                                                                                                        2 - Complete neglect ไม่สนสิ่งกระตุ้นทั้งหมด
                                                                                                                                                </label>
                                                                                                                                        </div>
                                                                                                                                </td>

                                                                                                                        </tr>


                                                                                                                </table>
                                                                                                        </div>
                                                                                                        <br>
                                                                                                        <div class="col-sm-6 offset-md-4" id="af_score_total_result"></div>




                                                                                                </div>
                                                                                        </div>
                                                                                </div>


                                                                        </div>
                                                                </div>
                                                        </div>
                                                </div>

                                                <br>

                                                <div class="alert alert-success text-center col-sm-12" role="alert"><b>💉 IV rtPA Dosing Calculator</b></div>



                                                <!-- card -->
                                                <div class="card">
                                                        <!-- card -->
                                                        <div class="card-body" style=" overflow-y: auto;">
                                                                <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="ปรับขนาด" name="set_cal" id="set_cal" value="0.9">
                                                        </div>
                                                </div>
                                                <!-- card -->
                                                <div class="card-body" style=" overflow-y: auto;">
                                                        <button class="btn btn-success" type="button" onclick="onclick_calculate_button(event)"><i class="fas fa-calculator"></i> Calculate Dose</button> <br>
                                                        <br>
                                                        <input type="text" name="value1" id="value1" value="" readonly style="width: 300px;"><br>
                                                        <input type="text" name="value2" id="value2" value="" readonl style="width: 300px;"><br>
                                                        <input type="text" name="value3" id="value3" value="" readonly style="width: 300px;">

                                                </div>






                                                <script>
                                                        function onclick_calculate_button(event) {

                                                                // alert(sex);
                                                                var bw = $('#bw').val();
                                                                var set_cal = $('#set_cal').val();


                                                                var calculate = roundNumber(bw * set_cal, 2);
                                                                var calculate2 = roundNumber(((bw * set_cal) * (10 / 100)), 2);
                                                                var calculate3 = roundNumber(((bw * set_cal) * (90 / 100)), 2);
                                                                var value1 = '💊 rtPA Total Dose: ' + calculate + ' mg';
                                                                var value2 = '💉 Bolus (10%): ' + calculate2 + ' mg';
                                                                var value3 = '🕒 Infusion (90%): ' + calculate3 + ' mg over 60 min';
                                                                $('#value1').val(Number.isNaN(value1) ? '' : value1);
                                                                $('#value2').val(Number.isNaN(value2) ? '' : value2);
                                                                $('#value3').val(Number.isNaN(value3) ? '' : value3);

                                                        }
                                                </script>







                                                <div class="alert alert-success text-center col-sm-12" role="alert"><b><br>✅ Indications for IV thrombolysis ( must be all "Yes" )</b></div>

                                                <div class="col-md-12">

                                                        <table id="bg-table" width="100%" style="border-collapse: collapse;font-size:13pt;margin-top:8px;">
                                                                <tr style="border:1px solid #000;margin: 45px;">

                                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;<b>Items</b></td>
                                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;<b>No</b></td>
                                                                        <th><input type="radio" name="my_check" onclick="checkAllYes()">CheckAll ✅ Yes <input type="radio" name="my_check" onclick="uncheckAll()">UncheckAll</th>
                                                                </tr>

                                                                <tr style="border:1px solid #000;margin: 45px;">

                                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;Age > 18 y</td>



                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                name="check_age_18"
                                                                                                value="1"
                                                                                                <?php if ($row['check_age_18'] == '1') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="check_age_18_1" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input yes-radio"
                                                                                                name="check_age_18"
                                                                                                value="2"
                                                                                                <?php if ($row['check_age_18'] == '2') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="check_age_18_2" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                </tr>

                                                                <tr style="border:1px solid #000;margin: 45px;">

                                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;Onset < 4.5 h</td>



                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="check_45_onset1"
                                                                                                name="check_45_onset"
                                                                                                value="1"
                                                                                                <?php if ($row['check_45_onset'] == '1') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="check_45_onset1" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input yes-radio"
                                                                                                id="check_45_onset2"
                                                                                                name="check_45_onset"
                                                                                                value="2"
                                                                                                <?php if ($row['check_45_onset'] == '2') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="check_45_onset2" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                </tr>

                                                                <tr style="border:1px solid #000;margin: 45px;">

                                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;อาการทางระบบประสาทสามารถวัดได้โดยใช้ NIHSS</td>



                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="nihss1"
                                                                                                name="nihss"
                                                                                                value="1"
                                                                                                <?php if ($row['nihss'] == '1') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="nihss1" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input yes-radio"
                                                                                                id="nihss2"
                                                                                                name="nihss"
                                                                                                value="2"
                                                                                                <?php if ($row['nihss'] == '2') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="nihss2" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                </tr>


                                                                <tr style="border:1px solid #000;margin: 45px;">

                                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;CT brain no hemorrhage</td>



                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="ct_brain_no_hemo1"
                                                                                                name="ct_brain_no_hemo"
                                                                                                value="1"
                                                                                                <?php if ($row['ct_brain_no_hemo'] == '1') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="ct_brain_no_hemo1" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input yes-radio"
                                                                                                id="ct_brain_no_hemo2"
                                                                                                name="ct_brain_no_hemo"
                                                                                                value="2"
                                                                                                <?php if ($row['ct_brain_no_hemo'] == '2') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="ct_brain_no_hemo2" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                </tr>

                                                        </table>
                                                        <br>
                                                        <div class="alert alert-success text-center col-sm-12" role="alert"><b>🚫 Contraindications for IV thrombolysis ( must be all "No" )</b></div>
                                                </div>



                                                <script>
                                                        /*
function checkAllYes2() {
  document.querySelectorAll('.yes-radio').forEach(radio => {
    radio.checked = true;
  });
}

function uncheckAll2() {
  // Get all radio inputs and clear checked state
  document.querySelectorAll('input[type="radio"]').forEach(radio => {
    radio.checked = false;
  });
}*/
                                                </script>



                                                <div class="col-md-12">

                                                        <table id="bg-table" width="100%" style="border-collapse: collapse;font-size:13pt;margin-top:8px;">
                                                                <tr style="border:1px solid #000;margin: 45px;">

                                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;<b>Items</b></td>
                                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;<b>Yes</b></td>
                                                                        
                                                                </td>
                                                                <th><input type="radio" name="my_check2" onclick="checkAllYes2()">CheckAll <b>🚫 No</b> <input type="radio" name="my_check2" onclick="uncheckAll2()">UncheckAll</th>

                                                                </tr>

                                                                <tr style="border:1px solid #000;margin: 45px;">

                                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;ไม่ทราบระยะเวลาที่เริ่มเป็นชัดเจน หรือมีอาการหลังตื่นนอน</td>



                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="unknown_time1"
                                                                                                name="unknown_time"
                                                                                                value="1"
                                                                                                <?php if ($row['unknown_time'] == '1') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="unknown_time1" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input no-radio"
                                                                                                id="unknown_time2"
                                                                                                name="unknown_time"
                                                                                                value="2"
                                                                                                <?php if ($row['unknown_time'] == '2') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="unknown_time2" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                </tr>

                                                                <tr style="border:1px solid #000;margin: 45px;">

                                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">
                                                                                <span>SBP ≥ 185 or DBP ≥ 110 mmHg *</span>

                                                                                <button style="background-color: green; color: white; border: none; padding: 4px 8px; border-radius: 4px; margin-left: 10px;">
                                                                                        <a data-toggle="collapse" data-parent="#accordion" href="#collapse1" style="color: white; text-decoration: none;">
                                                                                                See Note
                                                                                        </a>
                                                                                </button>

                                                                                <div class="panel-group" id="accordion" style="margin-top: 10px;">
                                                                                        <div class="panel panel-default">
                                                                                                <div id="collapse1" class="panel-collapse collapse in">
                                                                                                        <div class="panel-body">
                                                                                                                <div class="card-group pb-3 ">
                                                                                                                        <!-- card -->
                                                                                                                        <div class="card">
                                                                                                                                <!-- card -->
                                                                                                                                <div class="card-body" style=" overflow-y: auto;">
                                                                                                                                        <b>BP Management Notes</b>
                                                                                                                                        <br>
                                                                                                                                        <u>Nicardipine Protocol</u> <br>
                                                                                                                                        • Initial : 1 mg IV push over 1 min <br>
                                                                                                                                        • Maintenance : Nicardipine (1:5 dilution) at 10 mL/hr (2 mg/hr) <br>
                                                                                                                                        • Titration: Increase by 5 mL/hr every 5–15 min <br>
                                                                                                                                        • Max: 75 mL/hr (15 mg/hr) <br>
                                                                                                                                        <u>Labetalol Protocol</u> <br>
                                                                                                                                        • Initial: 10 mg IV push over 2 min (100mg/20ml vial) <br>
                                                                                                                                        • If SBP ≥ 230: Give 20 mg, wait 10–15 min, repeat if needed <br>
                                                                                                                                        • Infusion: 1 vial + NSS 100 mL = 1:1 dilution, at 30 mL/hr (0.5 mg/min) <br>
                                                                                                                                        • Titrate every 10–15 min up to 2–8 mg/min <br>
                                                                                                                                        <b>Contraindications :</b> AV block (2nd/3rd), Asthma/COPD, Cardiogenic shock, Severe bradycardia <br>

                                                                                                                                        <b>Post rTPA Goal :</b> Maintain BP ≤ 180/105 mmHg ✅
                                                                                                                                </div>
                                                                                                                        </div>
                                                                                                                </div>


                                                                                                        </div>
                                                                                                </div>
                                                                                        </div>
                                                                                </div>
                                                                        </td>



                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="bp1"
                                                                                                name="bp"
                                                                                                value="1"
                                                                                                <?php if ($row['bp'] == '1') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="bp1" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input no-radio"
                                                                                                id="bp2"
                                                                                                name="bp"
                                                                                                value="2"
                                                                                                <?php if ($row['bp'] == '2') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="bp2" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                </tr>

                                                                <tr style="border:1px solid #000;margin: 45px;">

                                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;Seizure with postictal neurological deficit</td>



                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="seizure1"
                                                                                                name="seizure"
                                                                                                value="1"
                                                                                                <?php if ($row['seizure'] == '1') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="seizure1" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input no-radio"
                                                                                                id="seizure2"
                                                                                                name="seizure"
                                                                                                value="2"
                                                                                                <?php if ($row['seizure'] == '2') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="seizure2" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                </tr>


                                                                <tr style="border:1px solid #000;margin: 45px;">

                                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;Plasma glucose < 50, or> 400 mg/dL (Correct glucose ก่อน)</td>



                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="plasma_glucose1"
                                                                                                name="plasma_glucose"
                                                                                                value="1"
                                                                                                <?php if ($row['plasma_glucose'] == '1') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="plasma_glucose1" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input no-radio"
                                                                                                id="plasma_glucose2"
                                                                                                name="plasma_glucose"
                                                                                                value="2"
                                                                                                <?php if ($row['plasma_glucose'] == '2') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="plasma_glucose2" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                </tr>

                                                                <tr style="border:1px solid #000;margin: 45px;">

                                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">
                                                                                <span>Minor symptoms (NIHSS ≤ 4) *</span>

                                                                                <button style="background-color: green; color: white; border: none; padding: 4px 8px; border-radius: 4px; margin-left: 10px;">
                                                                                        <a data-toggle="collapse" data-parent="#accordion1" href="#collapse2" style="color: white; text-decoration: none;">
                                                                                                See Note
                                                                                        </a>
                                                                                </button>

                                                                                <div class="panel-group" id="accordion1" style="margin-top: 10px;">
                                                                                        <div class="panel panel-default">
                                                                                                <div id="collapse2" class="panel-collapse collapse in">
                                                                                                        <div class="panel-body">
                                                                                                                <div class="card-group pb-3 ">
                                                                                                                        <!-- card -->
                                                                                                                        <div class="card">
                                                                                                                                <!-- card -->
                                                                                                                                <div class="card-body" style=" overflow-y: auto;">
                                                                                                                                        <b>Disabling Stroke Criteria</b> <br>

                                                                                                                                        • อาการใดๆ ที่ส่งผลต่อการดำเนินชีวิตประจำวันหรือกิจวัตร <br>
                                                                                                                                        • เช่น นักร้อง : Mild dysarthria, นักเปียโน : Mild weakness of fingers <br>
                                                                                                                                        • กลุ่มนี้ NIHSS ≤ 4 แต่อาจพิจารณาให้ rTPA เนื่องจากเป็น Disabling stroke <br>

                                                                                                                                        <b>Clinical Pearl :</b> Functional impact > NIHSS score
                                                                                                                                </div>
                                                                                                                        </div>
                                                                                                                </div>


                                                                                                        </div>
                                                                                                </div>
                                                                                        </div>
                                                                                </div>
                                                                        </td>



                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="minor1"
                                                                                                name="minor"
                                                                                                value="1"
                                                                                                <?php if ($row['minor'] == '1') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="minor1" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input no-radio"
                                                                                                id="minor2"
                                                                                                name="minor"
                                                                                                value="2"
                                                                                                <?php if ($row['minor'] == '2') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="minor2" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                </tr>

                                                                <tr style="border:1px solid #000;margin: 45px;">

                                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;Previous Hx of ICH</td>



                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="hx_of_ich1"
                                                                                                name="hx_of_ich"
                                                                                                value="1"
                                                                                                <?php if ($row['hx_of_ich'] == '1') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="hx_of_ich1" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input no-radio"
                                                                                                id="hx_of_ich2"
                                                                                                name="hx_of_ich"
                                                                                                value="2"
                                                                                                <?php if ($row['hx_of_ich'] == '2') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="hx_of_ich2" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                </tr>

                                                                <tr style="border:1px solid #000;margin: 45px;">

                                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">
                                                                                <span>3 mo; Old CVA, Intracranial/Spinal Sx, Head Trauma, MI *</span>

                                                                                <button style="background-color: green; color: white; border: none; padding: 4px 8px; border-radius: 4px; margin-left: 10px;">
                                                                                        <a data-toggle="collapse" data-parent="#accordion1" href="#collapse3" style="color: white; text-decoration: none;">
                                                                                                See Note
                                                                                        </a>
                                                                                </button>

                                                                                <div class="panel-group" id="accordion1" style="margin-top: 10px;">
                                                                                        <div class="panel panel-default">
                                                                                                <div id="collapse3" class="panel-collapse collapse in">
                                                                                                        <div class="panel-body">
                                                                                                                <div class="card-group pb-3 ">
                                                                                                                        <!-- card -->
                                                                                                                        <div class="card">
                                                                                                                                <!-- card -->
                                                                                                                                <div class="card-body" style=" overflow-y: auto;">
                                                                                                                                        <b>Myocardial Infarction Notes</b> <br>

                                                                                                                                        <b>• NSTEMI :</b> ✅ OK with rTPA (Class IIb recommendation) <br>

                                                                                                                                        <b>• STEMI :</b> <br>
                                                                                                                                        ✔ Right or inferior wall : Class IIa recommendation <br>
                                                                                                                                        ✔ Left anterior wall : Class IIb recommendation <br>
                                                                                                                                        ✖ > 6 hours from onset : ❌ No IV thrombolysis <br>
                                                                                                                                        ✔ > 1 week from onset : ✅ OK for IV thrombolysis
                                                                                                                                </div>
                                                                                                                        </div>
                                                                                                                </div>


                                                                                                        </div>
                                                                                                </div>
                                                                                        </div>
                                                                                </div>
                                                                        </td>



                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="cva1"
                                                                                                name="cva"
                                                                                                value="1"
                                                                                                <?php if ($row['cva'] == '1') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="cva1" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input no-radio"
                                                                                                id="cva2"
                                                                                                name="cva"
                                                                                                value="2"
                                                                                                <?php if ($row['cva'] == '2') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="cva2" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                </tr>

                                                                <tr style="border:1px solid #000;margin: 45px;">

                                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;3 wk ; GI, GU bleeding</td>



                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="bleeding1"
                                                                                                name="bleeding"
                                                                                                value="1"
                                                                                                <?php if ($row['bleeding'] == '1') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="bleeding1" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input no-radio"
                                                                                                id="bleeding2"
                                                                                                name="bleeding"
                                                                                                value="2"
                                                                                                <?php if ($row['bleeding'] == '2') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="bleeding2" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                </tr>




                                                                <tr style="border:1px solid #000;margin: 45px;">

                                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;2 wk ; Major surgery</td>



                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="surgery1"
                                                                                                name="surgery"
                                                                                                value="1"
                                                                                                <?php if ($row['surgery'] == '1') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="surgery1" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input no-radio"
                                                                                                id="surgery2"
                                                                                                name="surgery"
                                                                                                value="2"
                                                                                                <?php if ($row['surgery'] == '2') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="surgery2" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                </tr>


                                                                <tr style="border:1px solid #000;margin: 45px;">

                                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;1 wk ; Lumbar puncture/ Arterial puncture (non-compressible site)</td>



                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="puncture1"
                                                                                                name="puncture"
                                                                                                value="1"
                                                                                                <?php if ($row['puncture'] == '1') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="puncture1" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input no-radio"
                                                                                                id="puncture2"
                                                                                                name="puncture"
                                                                                                value="2"
                                                                                                <?php if ($row['puncture'] == '2') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="puncture2" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                </tr>




                                                                <tr style="border:1px solid #000;margin: 45px;">

                                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">
                                                                                <span>2 days ; NOACs * (Dabigatran, Apixaban, Rivaroxaban, Edoxaban), heparin, warfarin</span>

                                                                                <button style="background-color: green; color: white; border: none; padding: 4px 8px; border-radius: 4px; margin-left: 10px;">
                                                                                        <a data-toggle="collapse" data-parent="#accordion1" href="#collapse4" style="color: white; text-decoration: none;">
                                                                                                See Note
                                                                                        </a>
                                                                                </button>

                                                                                <div class="panel-group" id="accordion1" style="margin-top: 10px;">
                                                                                        <div class="panel panel-default">
                                                                                                <div id="collapse4" class="panel-collapse collapse in">
                                                                                                        <div class="panel-body">
                                                                                                                <div class="card-group pb-3 ">
                                                                                                                        <!-- card -->
                                                                                                                        <div class="card">
                                                                                                                                <!-- card -->
                                                                                                                                <div class="card-body" style=" overflow-y: auto;">
                                                                                                                                        <b>NOACs Notes</b> <br>

                                                                                                                                        <b>Dabigatran</b> <br>
                                                                                                                                        ✔ > 48 hr + normal GFR <br>
                                                                                                                                        ✔ ≤ 48 hr + check TT < 60 sec <br>
                                                                                                                                                if TT ≥ 60 sec, refer for Idarucizumab <br>
                                                                                                                                                <br>
                                                                                                                                                <b>Xa Inhibitors (Apixaban, Rivaroxaban, Edoxaban)</b> <br>
                                                                                                                                                ✔ > 48 hr + normal GFR <br>
                                                                                                                                                ✔ ≤ 48 hr check Anti-Xa activity < 0.5 U/ml
                                                                                                                                                        </div>
                                                                                                                                </div>
                                                                                                                        </div>


                                                                                                                </div>
                                                                                                        </div>
                                                                                                </div>
                                                                                        </div>
                                                                        </td>



                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="noacs1"
                                                                                                name="noacs"
                                                                                                value="1"
                                                                                                <?php if ($row['noacs'] == '1') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="noacs1" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input no-radio"
                                                                                                id="noacs2"
                                                                                                name="noacs"
                                                                                                value="2"
                                                                                                <?php if ($row['noacs'] == '2') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="noacs2" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                </tr>

                                                                <tr style="border:1px solid #000;margin: 45px;">

                                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;1 day ; Enoxaparin</td>



                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="enoxaparin1"
                                                                                                name="enoxaparin"
                                                                                                value="1"
                                                                                                <?php if ($row['enoxaparin'] == '1') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="enoxaparin1" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input no-radio"
                                                                                                id="enoxaparin2"
                                                                                                name="enoxaparin"
                                                                                                value="2"
                                                                                                <?php if ($row['enoxaparin'] == '2') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="enoxaparin2" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                </tr>

                                                                <tr style="border:1px solid #000;margin: 45px;">

                                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;INR > 1.7, Plt <100000, aPTT < 40 sec, PT> 15 sec</td>



                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="inr1"
                                                                                                name="inr"
                                                                                                value="1"
                                                                                                <?php if ($row['inr'] == '1') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="inr1" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input no-radio"
                                                                                                id="inr2"
                                                                                                name="inr"
                                                                                                value="2"
                                                                                                <?php if ($row['inr'] == '2') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="inr2" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                </tr>


                                                                <tr style="border:1px solid #000;margin: 45px;">

                                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;Infective Endocarditis (New murmur + Prolong fever)</td>



                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="infective_endocarditis1"
                                                                                                name="infective_endocarditis"
                                                                                                value="1"
                                                                                                <?php if ($row['infective_endocarditis'] == '1') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="infective_endocarditis1" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input no-radio"
                                                                                                id="infective_endocarditis2"
                                                                                                name="infective_endocarditis"
                                                                                                value="2"
                                                                                                <?php if ($row['infective_endocarditis'] == '2') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="infective_endocarditis2" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                </tr>


                                                                <tr style="border:1px solid #000;margin: 45px;">

                                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;Aortic dissection (BP 4 ext, unequal pulse, Chest/Back pain)</td>



                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="aortic_dissection1"
                                                                                                name="aortic_dissection"
                                                                                                value="1"
                                                                                                <?php if ($row['aortic_dissection'] == '1') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="aortic_dissection1" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input no-radio"
                                                                                                id="aortic_dissection2"
                                                                                                name="aortic_dissection"
                                                                                                value="2"
                                                                                                <?php if ($row['aortic_dissection'] == '2') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="aortic_dissection2" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                </tr>

                                                                <tr style="border:1px solid #000;margin: 45px;">

                                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;CT : ICH, SAH, multilobar infarction (Hypodensity > ⅓ MCA)</td>



                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="ich1"
                                                                                                name="ich"
                                                                                                value="1"
                                                                                                <?php if ($row['ich'] == '1') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="ich1" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input no-radio"
                                                                                                id="ich2"
                                                                                                name="ich"
                                                                                                value="2"
                                                                                                <?php if ($row['ich'] == '2') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="ich2" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                </tr>

                                                                <tr style="border:1px solid #000;margin: 45px;">

                                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;ตรวจพบเลือดออก หรือมีการบาดเจ็บ (กระดูกหัก)</td>



                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input"
                                                                                                id="injury1"
                                                                                                name="injury"
                                                                                                value="1"
                                                                                                <?php if ($row['injury'] == '1') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="injury1" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                        <td style="text-align:center; border: 0.5px solid #000; padding: 4px;" width="1%">
                                                                                <div class="custom-control custom-radio">
                                                                                        <input type="radio"
                                                                                                class="custom-control-input no-radio"
                                                                                                id="injury2"
                                                                                                name="injury"
                                                                                                value="2"
                                                                                                <?php if ($row['injury'] == '2') echo 'checked="checked"'; ?>>
                                                                                        <label class="custom-control-label" for="injury2" style="display:inline-block; width: 120px;">

                                                                                        </label>
                                                                                </div>
                                                                        </td>

                                                                </tr>


                                                        </table>


                                                        <br>
                                                        <div class="alert alert-success text-center col-sm-12" role="alert"><b>👨‍⚕️ ตรวจสอบโดยแพทย์</b></div>

                                                        <br>
                                                        <div class="alert alert-success text-center col-sm-12" role="alert"><b>📚 Important Clinical Trials</b></div>

                                                        <br>




                                                        <span>❤ CHANCE Trial</span>

                                                        <button style="background-color: green; color: white; border: none; padding: 4px 8px; border-radius: 4px; margin-left: 10px;">
                                                                <a data-toggle="collapse" data-parent="#accordion1" href="#collapse5" style="color: white; text-decoration: none;">
                                                                        See Note
                                                                </a>
                                                        </button>

                                                        <div class="panel-group" id="accordion1" style="margin-top: 10px;">
                                                                <div class="panel panel-default">
                                                                        <div id="collapse5" class="panel-collapse collapse in">
                                                                                <div class="panel-body">
                                                                                        <div class="card-group pb-3 ">
                                                                                                <!-- card -->
                                                                                                <div class="card">
                                                                                                        <!-- card -->
                                                                                                        <div class="card-body" style=" overflow-y: auto;">
                                                                                                                <b>CHANCE Trial Notes</b> <br>

                                                                                                                <u>Inclusion</u><br>
                                                                                                                ✔ Minor : NIHSS ≤ 3 or <br>
                                                                                                                ✔ TIA : ABCD2 ≥ 4 <br>
                                                                                                                ✔ Age ≥ 40 <br>
                                                                                                                ✔ Onset ≤ 24 hour <br>

                                                                                                                <u>Exclusion</u> <br>
                                                                                                                ✖ Need OAC <br>
                                                                                                                ✖ Prone bleed : AVM, Hemorrhage, Tumor, Abscess, Long term use of NSAIDs <br>
                                                                                                                ✖ Isolated symptom : Sensory, Visual, Vertigo <br>
                                                                                                                ✖ mRS > 2 <br>

                                                                                                                <u>CHANCE Protocol </u> <br>
                                                                                                                ❤ Day 1 : ASA (75–300) + Clopidogrel (300) <br>
                                                                                                                ❤ Day 2–21 : ASA (75) + Clopidogrel (75) <br>
                                                                                                                ❤ Day 22–90 : Clopidogrel (75) <br>
                                                                                                                ❤ After 90 days : Any single antiplatelet
                                                                                                        </div>
                                                                                                </div>
                                                                                        </div>


                                                                                </div>
                                                                        </div>
                                                                </div>
                                                        </div>

                                                        <br>
                                                        <span>❤ SAMMPRIS Trial</span>

                                                        <button style="background-color: green; color: white; border: none; padding: 4px 8px; border-radius: 4px; margin-left: 10px;">
                                                                <a data-toggle="collapse" data-parent="#accordion1" href="#collapse6" style="color: white; text-decoration: none;">
                                                                        See Note
                                                                </a>
                                                        </button>

                                                        <div class="panel-group" id="accordion1" style="margin-top: 10px;">
                                                                <div class="panel panel-default">
                                                                        <div id="collapse6" class="panel-collapse collapse in">
                                                                                <div class="panel-body">
                                                                                        <div class="card-group pb-3 ">
                                                                                                <!-- card -->
                                                                                                <div class="card">
                                                                                                        <!-- card -->
                                                                                                        <div class="card-body" style=" overflow-y: auto;">
                                                                                                                <b>SAMMPRIS Trial Notes</b> <br>

                                                                                                                <u>Inclusion</u> <br>
                                                                                                                ✔ Age 30-80<br>
                                                                                                                ✔ mRS ≤ 3 <br>
                                                                                                                ✔ TIA or Non-disabling stroke within 30 days <br>
                                                                                                                ✔ Severe intracranial stenosis ≥ 70-99% : ICA, MCA stem (M1), VA, BA <br>

                                                                                                                <u>SAMMPRIS Protocol</u> <br>
                                                                                                                ❤ For 90 days : ASA (325) + Clopidogrel (75)
                                                                                                        </div>
                                                                                                </div>
                                                                                        </div>


                                                                                </div>
                                                                        </div>
                                                                </div>
                                                        </div>

                                                        <br>
                                                        <span>❤ CSPS Trial</span>

                                                        <button style="background-color: green; color: white; border: none; padding: 4px 8px; border-radius: 4px; margin-left: 10px;">
                                                                <a data-toggle="collapse" data-parent="#accordion1" href="#collapse7" style="color: white; text-decoration: none;">
                                                                        See Note
                                                                </a>
                                                        </button>

                                                        <div class="panel-group" id="accordion1" style="margin-top: 10px;">
                                                                <div class="panel panel-default">
                                                                        <div id="collapse7" class="panel-collapse collapse in">
                                                                                <div class="panel-body">
                                                                                        <div class="card-group pb-3 ">
                                                                                                <!-- card -->
                                                                                                <div class="card">
                                                                                                        <!-- card -->
                                                                                                        <div class="card-body" style=" overflow-y: auto;">
                                                                                                                <b>CSPS Trial Notes</b> <br>

                                                                                                                <u>Inclusion (1 in 3)</u> <br>
                                                                                                                ✔ ≥ 50% stenosis intracranial : A1, A2; M1, M2; P1, P2<br>
                                                                                                                ✔ ≥ 50% stenosis extracranial : CCA, ICA, VA, Brachiocephalic, Subclavian <br>
                                                                                                                ✔ ≥ 2 risk factors : Age ≥ 65, HT, DM, CKD, PAD <br>
                                                                                                                ✔ Plus either : Hx of ischemic stroke, Ischemic heart disease, Current smoking <br>

                                                                                                                <u>CSPS Protocol</u> <br>
                                                                                                                ❤ Day 1–7 : ASA (81–100) + Clopidogrel (50–75) <br>
                                                                                                                ❤ Day 8–∞ (3.5 yr) : ASA (81–100) + Cilostazol (200)
                                                                                                        </div>
                                                                                                </div>
                                                                                        </div>


                                                                                </div>
                                                                        </div>
                                                                </div>
                                                        </div>



                                                        <div class="row">
                                                                <div id="show_check_save"></div>
                                                                <input type="hidden" id="vn" name="vn" value="<?= $vn ?>"><!-- ฟิลด์ hidden  "an"  -->
                                                                <input type="hidden" id="id" name="id" value="<?= $id ?>"><!-- ฟิลด์ hidden "id"  -->
                                                                <input type="hidden" id="version" name="version" value="<?= htmlspecialchars($row['version']) ?>">
                                                                <input type="hidden" id="create_user" name="create_user" value="<?= htmlspecialchars($_SESSION['name']) ?>">


                                                                <!-- <div class="col-md-9">
                                                                        <div id="data_hospital_nihss_score_save"></div>
                                                                        <div id="data_hospital_nihss_score_edit"></div>
                                                                        <div id="data_hospital_nihss_score_update"></div>
                                                                </div> -->
                                                                <div class="col-md-12 text-right">
                                                                        <?php
                                                                        if ((($id == null)) || (($id != null))) { ?>
                                                                                <button type="button" class="btn btn-primary" id="btn_hospital_nihss_score" onclick="stroke_save()"><i class="fas fa-save"></i> บันทึก</button>
                                                                        <?php } ?>
                                                                        <a href="/pdffile/stroke-fast-track-pdf.php?vn=<?php echo $vn; ?>&loginname=<?php echo $loginname; ?>" target="_blank" class="btn btn-secondary"><i class="fas fa-file-pdf"></i> Print <U>PDF</U> File</a>
                                                                </div>
                                                        </div><br>

                                                </div>


                                                </br>

                                                <script type="text/javascript">
                                                        function checkAllYes() {
                                                                // Select all elements with class 'yes-radio'
                                                                const radios = document.querySelectorAll('.yes-radio');
                                                                radios.forEach(radio => radio.checked = true);
                                                        }

                                                        function uncheckAll() {
                                                                // Select all elements with class 'yes-radio'
                                                                const radios = document.querySelectorAll('.yes-radio');
                                                                radios.forEach(radio => radio.checked = false);
                                                        }

                                                        function checkAllYes2() {
                                                                // Select all elements with class 'yes-radio'
                                                                const radios = document.querySelectorAll('.no-radio');
                                                                radios.forEach(radio => radio.checked = true);
                                                        }

                                                        function uncheckAll2() {
                                                                // Select all elements with class 'yes-radio'
                                                                const radios = document.querySelectorAll('.no-radio');
                                                                radios.forEach(radio => radio.checked = false);
                                                        }

                                                        function display_score(sum_score, score_display_id) {

                                                                //  console.log(score_display_id)
                                                                if (sum_score === "" || sum_score === null) {
                                                                        $('#' + score_display_id).html("");
                                                                } else {

                                                                        if (sum_score != null) {
                                                                                let MEWS_COLOR = ['#45c351', '#45c351', '#45c351', '#e6b728', '#e8832a', '#e8832a', '#e51616', '#e51616'];
                                                                                $('#' + score_display_id).html("<div class='badge text-white mt-1 font-weight-bold' style='class='badge text-white mt-1 font-weight-bold' background-color: " + MEWS_COLOR[sum_score] + ";'>" + sum_score + "</div>");
                                                                        }
                                                                }
                                                        }

                                                        function display_score_total(sum_score, score_display_id) {
                                                                if (sum_score === "" || sum_score === null) {
                                                                        $('#' + score_display_id).html("");
                                                                } else {
                                                                        color = 'inherit';
                                                                        if (sum_score >= 0 && sum_score <= 4) {
                                                                                color = '#45c351';
                                                                                Level = 'Minor stroke';
                                                                        } else if (sum_score >= 5 && sum_score <= 15) {
                                                                                color = '#e6b728';
                                                                                Level = 'Moderate stroke';
                                                                        } else if (sum_score >= 16 && sum_score <= 20) {
                                                                                color = '#e8832a';
                                                                                Level = 'Moderate to Severe stroke';
                                                                        } else if (sum_score >= 21) {
                                                                                color = '#e51616';
                                                                                Level = 'Severe stroke';

                                                                        }
                                                                        $('#' + score_display_id).html("<div class='alert text-white text-center font-weight-bold' style='font-size:100%;  background-color: " + color + ";'> Total NIHSS Score: " + sum_score + "<br>🔎 Severity Level: " + Level + "</div>");
                                                                }
                                                        }

                                                        function oninputCheckValue() {

                                                                // let somatic_concern = bp_rs1($('input[name="somatic_concern"]:checked').val()/*$("#somatic_concern").val()*/);

                                                                let level_of_consciousness = $('input[name="level_of_consciousness"]:checked').val() ?? 0; //0 if null
                                                                let two_questions = $('input[name="two_questions"]:checked').val() ?? 0; //0 if null
                                                                let two_commands = $('input[name="two_commands"]:checked').val() ?? 0; //0 if null
                                                                let best_gaze = $('input[name="best_gaze"]:checked').val() ?? 0; //0 if null
                                                                let best_visual_field = $('input[name="best_visual_field"]:checked').val() ?? 0; //0 if null
                                                                let facial_palsy = $('input[name="facial_palsy"]:checked').val() ?? 0; //0 if null
                                                                let best_moter_left_arm = $('input[name="best_moter_left_arm"]:checked').val() ?? 0; //0 if null
                                                                let best_moter_right_arm = $('input[name="best_moter_right_arm"]:checked').val() ?? 0; //0 if null
                                                                let best_moter_left_leg = $('input[name="best_moter_left_leg"]:checked').val() ?? 0; //0 if null
                                                                let best_moter_right_leg = $('input[name="best_moter_right_leg"]:checked').val() ?? 0; //0 if null
                                                                let ataxia = $('input[name="ataxia"]:checked').val() ?? 0; //0 if null
                                                                let sensory = $('input[name="sensory"]:checked').val() ?? 0; //0 if null
                                                                let best_language_aphasia = $('input[name="best_language_aphasia"]:checked').val() ?? 0; //0 if null
                                                                let dysarthria = $('input[name="dysarthria"]:checked').val() ?? 0; //0 if null
                                                                let neglect = $('input[name="neglect"]:checked').val() ?? 0; //0 if null


                                                                let sum_score = (parseFloat(level_of_consciousness) + parseFloat(two_questions) + parseFloat(two_commands) + parseFloat(best_gaze) + parseFloat(best_visual_field) +
                                                                        parseFloat(facial_palsy) + parseFloat(best_moter_left_arm) + parseFloat(best_moter_right_arm) + parseFloat(best_moter_left_leg) + parseFloat(best_moter_right_leg) +
                                                                        parseFloat(ataxia) + parseFloat(sensory) + parseFloat(best_language_aphasia) + parseFloat(dysarthria) + parseFloat(neglect)

                                                                )



                                                                display_score(level_of_consciousness, "level_of_consciousness_result");
                                                                display_score(two_questions, "two_questions_result");
                                                                display_score(two_commands, "two_commands_result");
                                                                display_score(best_gaze, "best_gaze_result");
                                                                display_score(best_visual_field, "best_visual_field_result");
                                                                display_score(facial_palsy, "facial_palsy_result");
                                                                display_score(best_moter_left_arm, "best_moter_left_arm_result");
                                                                display_score(best_moter_right_arm, "best_moter_right_arm_result");
                                                                display_score(best_moter_left_leg, "best_moter_left_leg_result");
                                                                display_score(best_moter_left_leg, "best_moter_right_leg_result");
                                                                display_score(ataxia, "ataxia");
                                                                display_score(sensory, "sensory");
                                                                display_score(best_language_aphasia, "best_language_aphasia");
                                                                display_score(dysarthria, "dysarthria");
                                                                display_score(neglect, "neglect");
                                                                display_score_total(sum_score, "score_total_result");


                                                        }

                                                        function oninputCheckValue2() {

                                                                // let somatic_concern = bp_rs1($('input[name="somatic_concern"]:checked').val()/*$("#somatic_concern").val()*/);

                                                                let af_level_of_consciousness = $('input[name="af_level_of_consciousness"]:checked').val() ?? 0; //0 if null
                                                                let af_two_questions = $('input[name="af_two_questions"]:checked').val() ?? 0; //0 if null
                                                                let af_two_commands = $('input[name="af_two_commands"]:checked').val() ?? 0; //0 if null
                                                                let af_best_gaze = $('input[name="af_best_gaze"]:checked').val() ?? 0; //0 if null
                                                                let af_best_visual_field = $('input[name="af_best_visual_field"]:checked').val() ?? 0; //0 if null
                                                                let af_facial_palsy = $('input[name="af_facial_palsy"]:checked').val() ?? 0; //0 if null
                                                                let af_best_moter_left_arm = $('input[name="af_best_moter_left_arm"]:checked').val() ?? 0; //0 if null
                                                                let af_best_moter_right_arm = $('input[name="af_best_moter_right_arm"]:checked').val() ?? 0; //0 if null
                                                                let af_best_moter_left_leg = $('input[name="af_best_moter_left_leg"]:checked').val() ?? 0; //0 if null
                                                                let af_best_moter_right_leg = $('input[name="af_best_moter_right_leg"]:checked').val() ?? 0; //0 if null
                                                                let af_ataxia = $('input[name="af_ataxia"]:checked').val() ?? 0; //0 if null
                                                                let af_sensory = $('input[name="af_sensory"]:checked').val() ?? 0; //0 if null
                                                                let af_best_language_aphasia = $('input[name="af_best_language_aphasia"]:checked').val() ?? 0; //0 if null
                                                                let af_dysarthria = $('input[name="dysarthria"]:checked').val() ?? 0; //0 if null
                                                                let af_neglect = $('input[name="af_neglect"]:checked').val() ?? 0; //0 if null


                                                                let sum_score = (parseFloat(af_level_of_consciousness) + parseFloat(af_two_questions) + parseFloat(af_two_commands) +
                                                                        parseFloat(af_best_gaze) + parseFloat(af_best_visual_field) +
                                                                        parseFloat(af_facial_palsy) + parseFloat(af_best_moter_left_arm) + parseFloat(af_best_moter_right_arm) + parseFloat(af_best_moter_left_leg) + parseFloat(af_best_moter_right_leg) +
                                                                        parseFloat(af_ataxia) + parseFloat(af_sensory) + parseFloat(af_best_language_aphasia) + parseFloat(af_dysarthria) + parseFloat(af_neglect)

                                                                )



                                                                display_score(af_level_of_consciousness, "af_level_of_consciousness_result");
                                                                display_score(af_two_questions, "af_two_questions_result");
                                                                display_score(af_two_commands, "af_two_commands");
                                                                display_score(af_best_gaze, "af_best_gaze_result");
                                                                display_score(af_best_visual_field, "af_best_visual_field_result");
                                                                display_score(af_facial_palsy, "af_facial_palsy_result");
                                                                display_score(af_best_moter_left_arm, "af_best_moter_left_arm_result");
                                                                display_score(af_best_moter_right_arm, "af_best_moter_right_arm_result");
                                                                display_score(af_best_moter_left_leg, "af_best_moter_left_leg_result");
                                                                display_score(af_best_moter_left_leg, "af_best_moter_right_leg_result");
                                                                display_score(af_ataxia, "af_ataxia");
                                                                display_score(af_sensory, "af_sensory");
                                                                display_score(af_best_language_aphasia, "af_best_language_aphasia");
                                                                display_score(af_dysarthria, "af_dysarthria");
                                                                display_score(af_neglect, "af_neglect");
                                                                display_score_total(sum_score, "af_score_total_result");


                                                        }


                                                        function stroke_save() {
                                                                //  var summary_plan_date = $("#summary_plan_date").val();
                                                                // var summary_plan_time = $("#summary_plan_time").val();
                                                                //var principal_diagnosis = $("#principal_diagnosis").val();
                                                                var $id = $("#id").val();
                                                                //url
                                                                var url_save = 'stroke-fast-track.save.php';
                                                                var url_update = 'stroke-fast-track-update.php';
                                                                var my_form = $("#my_form").serialize();


                                                                if ($id == "") {
                                                                        $.post(url_save, my_form, function(data) {
                                                                                        $("#show_check_save").html(data);
                                                                                       // alert("บันทึกข้อมูลสำเร็จ");
                                                                                        //window.location.reload(true);
                                                                                        //self.close();
                                                                                })
                                                                                .fail(function() {
                                                                                        alert("บันทึกข้อมูลไม่สำเร็จ" + error);
                                                                                });
                                                                } else {
                                                                        $.post(url_update, my_form, function(data) {
                                                                                        $("#show_check_save").html(data);
                                                                                       // alert("บันทึกข้อมูลสำเร็จ");
                                                                                       // window.location.reload(true);
                                                                                        //self.close();
                                                                                })
                                                                                .fail(function() {
                                                                                        alert("บันทึกข้อมูลไม่สำเร็จ" + error);
                                                                                });
                                                                }
                                                                // }
                                                        }
                                                </script>
                                                <script src="../include/my_function.js"></script>
                                                <script src="../node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
                                                <link rel="stylesheet" href="../node_modules/sweetalert2/dist/sweetalert2.min.css">