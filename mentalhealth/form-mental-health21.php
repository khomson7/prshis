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

//Session::checkPermissionAndShowMessage('PRS_MENTAL_HEAL', 'VIEW');

require_once '../mains/main-report.php';

//Session::checkLoginSessionAndShowMessage(); //เช็ค session

$permissionCheck = Session::checkPermissionAndShowMessage('PRS_FORM_MENTAL_HEALTH2', 'VIEW');
$permissionCheckJson = json_encode($permissionCheck);

//Session::checkPermissionAndShowMessage('PRS_MENTAL_HEAL1', 'VIEW');
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


Session::insertSystemAccessLog(json_encode(array(
        'form' => 'MENTAL-HEALTH2-FORM',
        'an' => $an,
), JSON_UNESCAPED_UNICODE));


//echo $ids;

//----------------------เช็คว่า an นี้ มีข้อมูลหรือไม่




$sql = "SELECT *
                FROM `prs_mental_health2`
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
/*
$id = '21'; //ลำดับในตาราง prs_link_menu
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
*/
$id = '21'; //Link menu
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
                        <!--  <div class="row">
            <div class="col-auto">
                <button type="button" class="btn btn-sm btn-primary btn-block" onclick="window.close()"><i class="fas fa-arrow-left"></i> กลับ</button>
            </div>
            <div class="col-md-11">
                <h4><?= htmlspecialchars($menu_name) ?>
                    <?= htmlspecialchars(DbConstant::HOSPITAL_NAME) ?><?php if ($production == "2") { ?>

                    <font color="red">ช่วงทดลอง</font>
                <?php } else { ?>

                <? } ?>
                </h4>
            </div>

        </div> -->

                        <div class="row">
                                <div class="col-auto">
                                        <button type="button" class="btn btn-sm btn-primary btn-block" onclick="window.close()"><i class="fas fa-arrow-left"></i> กลับ</button>
                                </div>
                                <div class="col-auto p-1 font-weight-bold">
                                        <h5><B><?= htmlspecialchars($menu_name) ?> <?= htmlspecialchars(DbConstant::HOSPITAL_NAME) ?>
                                                        <?php if ($check_ == "1") { ?>

                                                                <font color="red">ช่วงทดลอง</font>
                                                        <?php } else { ?>

                                                        <? } ?>
                                                </B></h5>
                                </div>

                        </div>


                        <div class="row">
                                <div class="col-md-12">

                                        <table id="bg-table" width="100%" style="border-collapse: collapse;font-size:10pt;margin-top:8px;">
                                                <tr style="border:1px solid #000;margin: 45px;">
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;<b>หัวข้อ</b></td>
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;<b>อาการและอาการแสดงออก</b></td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;<b>ไม่มีอาการ</b></td>
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;<b>มีเล็กน้อยเป็นบางครั้ง</b></td>
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;<b>มีอาการเล็กน้อย</b></td>
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;<b>มีอาการปานกลาง</b></td>
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;<b>อาการค่อนข้างรุนแรง</b></td>
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;<b>อาการรุนแรง</b></td>
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;<b>อาการรุนแรงมาก</b></td>
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;<b>SCORE</b></td>


                                                </tr>


                                                <tr style="border:1px solid #000;margin: 45px;">
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;1</td>
                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Somatic_concern (G) คุณรู้สึกตนเองป่วยเป้นโรคทางกายภาพหรือไม่</td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['somatic_concern'] == '1') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="somatic_concern1" value="1" name="somatic_concern" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="somatic_concern1">1</label>
                                                                </div>

                                                                
                                                                
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['somatic_concern'] == '2') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="somatic_concern2" value="2" name="somatic_concern" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="somatic_concern2">2</label>
                                                                </div>
                                                        </td>
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['somatic_concern'] == '3') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="somatic_concern3" value="3" name="somatic_concern" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="somatic_concern3">3</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['somatic_concern'] == '4') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="somatic_concern4" value="4" name="somatic_concern" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="somatic_concern4">4</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['somatic_concern'] == '5') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="somatic_concern5" value="5" name="somatic_concern" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="somatic_concern5">5</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['somatic_concern'] == '6') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="somatic_concern6" value="6" name="somatic_concern" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="somatic_concern6">6</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['somatic_concern'] == '7') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="somatic_concern7" value="7" name="somatic_concern" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="somatic_concern7">7</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                        <div class="col-sm-1" id="somatic_concern_result"></div>
                                                        
                                                        </td>
                                                        

                                                </tr>

                                                <!-- 2 -->
                                                <tr style="border:1px solid #000;margin: 45px;">
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;2</td>
                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Anxiety (G) ใน 1 สัปดาห์ที่ผ่านมาคุณรู้สึกกังวลหรือกลัวอะไรบ้างไหม/ความคิดนี้รบกวนจตใจบ่อยไหม
                                                                /รู้สึกมีการใจสั่น เหงื่อออก/อาการที่บอก มีผลต่อการทำงานของคุณไหม</td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['anxiety'] == '1') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="anxiety1" value="1" name="anxiety" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="anxiety1">1</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['anxiety'] == '2') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="anxiety2" value="2" name="anxiety" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="anxiety2">2</label>
                                                                </div>
                                                        </td>
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['anxiety'] == '3') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="anxiety3" value="3" name="anxiety" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="anxiety3">3</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['anxiety'] == '4') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="anxiety4" value="4" name="anxiety" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="anxiety4">4</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['anxiety'] == '5') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="anxiety5" value="5" name="anxiety" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="anxiety5">5</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['anxiety'] == '6') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="anxiety6" value="6" name="anxiety" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="anxiety6">6</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['anxiety'] == '7') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="anxiety7" value="7" name="anxiety" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="anxiety7">7</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                        <div class="col-sm-1" id="anxiety_result"></div>
                                                        </td>


                                                </tr>


                                                <!-- 3 -->
                                                <tr style="border:1px solid #000;margin: 45px;">
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;3</td>
                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Emotional Withdrawal (N) มีลักษณะแยกตัว ไม่ค่อยมีปฏิกิริยาโต้ตอบกับ ผู้อื่น ไม่แสดงอารมณ์ หน้าเฉยเมย</td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['emotional'] == '1') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="emotional1" value="1" name="emotional" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="emotional1">1</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['emotional'] == '2') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="emotional2" value="2" name="emotional" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="emotional2">2</label>
                                                                </div>
                                                        </td>
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['emotional'] == '3') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="emotional3" value="3" name="emotional" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="emotional3">3</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['emotional'] == '4') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="emotional4" value="4" name="emotional" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="emotional4">4</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['emotional'] == '5') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="emotional5" value="5" name="emotional" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="emotional5">5</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['emotional'] == '6') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="emotional6" value="6" name="emotional" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="emotional6">6</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['emotional'] == '7') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="emotional7" value="7" name="emotional" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="emotional7">7</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                        <div class="col-sm-1" id="emotional_result"></div>
                                                        </td>


                                                </tr>

                                                <!-- 4 -->
                                                <tr style="border:1px solid #000;margin: 45px;">
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;4</td>
                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Conceptual disorganization (P) พูดไม่เป็นเรื่องราว ขาดการเชื่อโยง พูดอ้อมค้อม ไม่ค่อยต่อเนื่อง (ดูใน 15 นาทีแรก)</td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['conceptual'] == '1') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="conceptual1" value="1" name="conceptual" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="conceptual1">1</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['conceptual'] == '2') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="conceptual2" value="2" name="conceptual" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="conceptual2">2</label>
                                                                </div>
                                                        </td>
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['conceptual'] == '3') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="conceptual3" value="3" name="conceptual" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="conceptual3">3</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['conceptual'] == '4') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="conceptual4" value="4" name="conceptual" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="conceptual4">4</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['conceptual'] == '5') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="conceptual5" value="5" name="conceptual" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="conceptual5">5</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['conceptual'] == '6') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="conceptual6" value="6" name="conceptual" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="conceptual6">6</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['conceptual'] == '7') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="conceptual7" value="7" name="conceptual" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="conceptual7">7</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                        <div class="col-sm-1" id="conceptual_result"></div>
                                                        </td>


                                                </tr>

                                                <!-- 5 -->
                                                <tr style="border:1px solid #000;margin: 45px;">
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;5</td>
                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Guilt Feeling (G) รู้สึกตำหนิตนเองในสิ่งที่ทำไม่ดี หรือเสียใจต่อสิ่งที่ทำในอดีตหรือไม่</td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['guilt'] == '1') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="guilt1" value="1" name="guilt" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="guilt1">1</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['guilt'] == '2') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="guilt2" value="2" name="guilt" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="guilt2">2</label>
                                                                </div>
                                                        </td>
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['guilt'] == '3') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="guilt3" value="3" name="guilt" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="guilt3">3</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['guilt'] == '4') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="guilt4" value="4" name="guilt" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="guilt4">4</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['guilt'] == '5') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="guilt5" value="5" name="guilt" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="guilt5">5</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['guilt'] == '6') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="guilt6" value="6" name="guilt" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="guilt6">6</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['guilt'] == '7') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="guilt7" value="7" name="guilt" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="guilt7">7</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                        <div class="col-sm-1" id="guilt_result"></div>
                                                        </td>

                                                </tr>


                                                <!-- 6 -->
                                                <tr style="border:1px solid #000;margin: 45px;">
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;6</td>
                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Tension (G) มองจากท่านั่งรู้สึกตึงเครียด ขณะพูดอาจมีการกระดก เสียงสั่น</td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['tension'] == '1') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="tension1" value="1" name="tension" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="tension1">1</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['tension'] == '2') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="tension2" value="2" name="tension" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="tension2">2</label>
                                                                </div>
                                                        </td>
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['tension'] == '3') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="tension3" value="3" name="tension" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="tension3">3</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['tension'] == '4') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="tension4" value="4" name="tension" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="tension4">4</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['tension'] == '5') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="tension5" value="5" name="tension" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="tension5">5</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['tension'] == '6') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="tension6" value="6" name="tension" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="tension6">6</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['tension'] == '7') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="tension7" value="7" name="tension" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="tension7">7</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                        <div class="col-sm-1" id="tension_result"></div>
                                                        </td>

                                                </tr>

                                                <!-- 7 -->
                                                <tr style="border:1px solid #000;margin: 45px;">
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;7</td>
                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Mannerism and posturing (G) มีท่าทางการเคลื่อนไหวไม่เป็นธรรมชาติเก้งก้าง แข็ง ดู แปลกๆ</td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['mannerism'] == '1') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="mannerism1" value="1" name="mannerism" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="mannerism1">1</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['mannerism'] == '2') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="mannerism2" value="2" name="mannerism" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="mannerism2">2</label>
                                                                </div>
                                                        </td>
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['mannerism'] == '3') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="mannerism3" value="3" name="mannerism" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="mannerism3">3</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['mannerism'] == '4') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="mannerism4" value="4" name="mannerism" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="mannerism4">4</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['mannerism'] == '5') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="mannerism5" value="5" name="mannerism" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="mannerism5">5</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['mannerism'] == '6') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="mannerism6" value="6" name="mannerism" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="mannerism6">6</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['mannerism'] == '7') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="mannerism7" value="7" name="mannerism" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="mannerism7">7</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                        <div class="col-sm-1" id="mannerism_result"></div>
                                                        </td>


                                                </tr>

                                                <!-- 8 -->
                                                <tr style="border:1px solid #000;margin: 45px;">
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;8</td>
                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Grandiosity (P) คุณมีความรู้สึกมีอำนาจพิเศษบางอย่างหรือไม่/ที่ผ่านมาคิดเป็นใครที่มีชื่อเสียงหรือไม่</td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['grandiosity'] == '1') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="grandiosity1" value="1" name="grandiosity" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="grandiosity1">1</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['grandiosity'] == '2') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="grandiosity2" value="2" name="grandiosity" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="grandiosity2">2</label>
                                                                </div>
                                                        </td>
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['grandiosity'] == '3') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="grandiosity3" value="3" name="grandiosity" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="grandiosity3">3</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['grandiosity'] == '4') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="grandiosity4" value="4" name="grandiosity" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="grandiosity4">4</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['grandiosity'] == '5') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="grandiosity5" value="5" name="grandiosity" oninput="oninputCheckValue()"> 
                                                                        <label class="custom-control-label" for="grandiosity5">5</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['grandiosity'] == '6') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="grandiosity6" value="6" name="grandiosity" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="grandiosity6">6</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['grandiosity'] == '7') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="grandiosity7" value="7" name="grandiosity" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="grandiosity7">7</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                        <div class="col-sm-1" id="grandiosity_result"></div>
                                                        </td>


                                                </tr>

                                                <!-- 9 -->
                                                <tr style="border:1px solid #000;margin: 45px;">
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;9</td>
                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Depressive mood (G) คุณรู้สึกว่าไม่มีความสุขหรือความเศร้า/รู้สึกเศร้าไหม/รู้สึกเศร้าบ่อยแค่ไหน
                                                                สามารถเบนความสนใจไปในเรื่องที่ทำให้รู้สึกได้ไหม/ความรู้สึกรบกวนการทำงานของคุณไหม</td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['depressive'] == '1') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="depressive1" value="1" name="depressive" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="depressive1">1</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['depressive'] == '2') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="depressive2" value="2" name="depressive" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="depressive2">2</label>
                                                                </div>
                                                        </td>
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['depressive'] == '3') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="depressive3" value="3" name="depressive" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="depressive3">3</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['depressive'] == '4') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="depressive4" value="4" name="depressive" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="depressive4">4</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['depressive'] == '5') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="depressive5" value="5" name="depressive" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="depressive5">5</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['depressive'] == '6') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="depressive6" value="6" name="depressive" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="depressive6">6</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['depressive'] == '7') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="depressive7" value="7" name="depressive" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="depressive7">7</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                        <div class="col-sm-1" id="depressive_result"></div>
                                                        </td>

                                                </tr>

                                                <!-- 10 -->
                                                <tr style="border:1px solid #000;margin: 45px;">
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;10</td>
                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Hostility (P) ใน 1 สัปดาห์ที่ผ่านมา คุณรู้สึกหงุดหงิดหรืออารมณ์เสียบ่อยๆเคยมีปัญหาชกต่อย หรือทะเลาะกับคนอื่น/สัมพันธภาพกับคนอื่น คนในครอบครัว เพื่อนร่วมงานเป็นอย่างไร</td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['hostility'] == '1') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="hostility1" value="1" name="hostility" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="hostility1">1</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['hostility'] == '2') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="hostility2" value="2" name="hostility" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="hostility2">2</label>
                                                                </div>
                                                        </td>
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['hostility'] == '3') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="hostility3" value="3" name="hostility" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="hostility3">3</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['hostility'] == '4') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="hostility4" value="4" name="hostility" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="hostility4">4</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['hostility'] == '5') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="hostility5" value="5" name="hostility" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="hostility5">5</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['hostility'] == '6') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="hostility6" value="6" name="hostility" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="hostility6">6</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['hostility'] == '7') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="hostility7" value="7" name="hostility" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="hostility7">7</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                        <div class="col-sm-1" id="hostility_result"></div>
                                                        </td>

                                                </tr>

                                                <!-- 11 -->
                                                <tr style="border:1px solid #000;margin: 45px;">
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;11</td>
                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Suspiciousness (P) คุณรู้สึกมีคนคอยจับผิด มีคนคิดร้ายบ้างไหม/โดยวิธีใด/รู้สึกกังวลกับการคิดร้ายของใครบ้างไหม</td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['suspiciousness'] == '1') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="suspiciousness1" value="1" name="suspiciousness" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="suspiciousness1">1</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['suspiciousness'] == '2') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="suspiciousness2" value="2" name="suspiciousness" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="suspiciousness2">2</label>
                                                                </div>
                                                        </td>
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['suspiciousness'] == '3') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="suspiciousness3" value="3" name="suspiciousness" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="suspiciousness3">3</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['suspiciousness'] == '4') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="suspiciousness4" value="4" name="suspiciousness" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="suspiciousness4">4</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['suspiciousness'] == '5') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="suspiciousness5" value="5" name="suspiciousness" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="suspiciousness5">5</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['suspiciousness'] == '6') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="suspiciousness6" value="6" name="suspiciousness" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="suspiciousness6">6</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['suspiciousness'] == '7') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="suspiciousness7" value="7" name="suspiciousness" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="suspiciousness7">7</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                        <div class="col-sm-1" id="suspiciousness_result"></div>
                                                        </td>


                                                </tr>


                                                <!-- 12 -->
                                                <tr style="border:1px solid #000;margin: 45px;">
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;12</td>
                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Hallucinatory behavior(P) คุณได้ยินเสียงหรือมีคนพูดโดยไม่เห็นตัวตนหรือไม่ คุณมองเห็นหรือได้กลิ่นอะไรบางอย่างโดยคนอื่นไม่รู้สึก / ประสบการณ์นี้มีผลกระทบต่อชีวิตประจำวันไหม</td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['hallucinatory'] == '1') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="hallucinatory1" value="1" name="hallucinatory" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="hallucinatory1">1</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['hallucinatory'] == '2') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="hallucinatory2" value="2" name="hallucinatory" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="hallucinatory2">2</label>
                                                                </div>
                                                        </td>
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['hallucinatory'] == '3') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="hallucinatory3" value="3" name="hallucinatory" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="hallucinatory3">3</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['hallucinatory'] == '4') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="hallucinatory4" value="4" name="hallucinatory" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="hallucinatory4">4</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['hallucinatory'] == '5') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="hallucinatory5" value="5" name="hallucinatory" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="hallucinatory5">5</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['hallucinatory'] == '6') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="hallucinatory6" value="6" name="hallucinatory" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="hallucinatory6">6</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['hallucinatory'] == '7') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="hallucinatory7" value="7" name="hallucinatory" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="hallucinatory7">7</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                        <div class="col-sm-1" id="hallucinatory_result"></div>
                                                        </td>

                                                </tr>

                                                <!-- 13 -->
                                                <tr style="border:1px solid #000;margin: 45px;">
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;13</td>
                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Motor retardation (G) การพูด การเคลื่อนไหวเชื่องช้า (สังเกตพฤติกรรม)</td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['motor'] == '1') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="motor1" value="1" name="motor" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="motor1">1</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['motor'] == '2') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="motor2" value="2" name="motor" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="motor2">2</label>
                                                                </div>
                                                        </td>
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['motor'] == '3') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="motor3" value="3" name="motor" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="motor3">3</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['motor'] == '4') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="motor4" value="4" name="motor" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="motor4">4</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['motor'] == '5') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="motor5" value="5" name="motor" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="motor5">5</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['motor'] == '6') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="motor6" value="6" name="motor" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="motor6">6</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['motor'] == '7') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="motor7" value="7" name="motor" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="motor7">7</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                        <div class="col-sm-1" id="motor_result"></div>
                                                        </td>

                                                </tr>


                                                <!-- 14 -->
                                                <tr style="border:1px solid #000;margin: 45px;">
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;14</td>
                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Uncooperativeness (G) มีท่าทีต่อต้าน ระมัดระวัง ไม่เป้นมิตรต่อผู้อื่นและ ผู้ตรวจ</td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['uncooperativeness'] == '1') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="uncooperativeness1" value="1" name="uncooperativeness" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="uncooperativeness1">1</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['uncooperativeness'] == '2') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="uncooperativeness2" value="2" name="uncooperativeness" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="uncooperativeness2">2</label>
                                                                </div>
                                                        </td>
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['uncooperativeness'] == '3') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="uncooperativeness3" value="3" name="uncooperativeness" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="uncooperativeness3">3</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['uncooperativeness'] == '4') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="uncooperativeness4" value="4" name="uncooperativeness" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="uncooperativeness4">4</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['uncooperativeness'] == '5') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="uncooperativeness5" value="5" name="uncooperativeness" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="uncooperativeness5">5</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['uncooperativeness'] == '6') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="uncooperativeness6" value="6" name="uncooperativeness" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="uncooperativeness6">6</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['uncooperativeness'] == '7') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="uncooperativeness7" value="7" name="uncooperativeness" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="uncooperativeness7">7</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                        <div class="col-sm-1" id="uncooperativeness_result"></div>
                                                        </td>

                                              </tr>

                                                <!-- 15 -->
                                                <tr style="border:1px solid #000;margin: 45px;">
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;15</td>
                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Unusual thought content (G) ความคิดแปลก เช่น มีความคิดเชื่อเรื่องพลังจิต วิญญาณ หากพบในข้อ
                                                                Somatic Grandiosity Delusion จะพบในหัวข้อนี้ด้วย</td>

                                                                <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['unusual'] == '1') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="unusual1" value="1" name="unusual" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="unusual1">1</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['unusual'] == '2') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="unusual2" value="2" name="unusual" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="unusual2">2</label>
                                                                </div>
                                                        </td>
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['unusual'] == '3') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="unusual3" value="3" name="unusual" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="unusual3">3</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['unusual'] == '4') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="unusual4" value="4" name="unusual" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="unusual4">4</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['unusual'] == '5') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="unusual5" value="5" name="unusual" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="unusual5">5</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['unusual'] == '6') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="unusual6" value="6" name="unusual" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="unusual6">6</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['unusual'] == '7') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="unusual7" value="7" name="unusual" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="unusual7">7</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                        <div class="col-sm-1" id="unusual_result"></div>
                                                        </td>

                                                </tr>


                                                <!-- 16 -->
                                                <tr style="border:1px solid #000;margin: 45px;">
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;16</td>
                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Blunted affect (N) สีหน้าไม่ค่อยสดงความรู้สึก อารมณ์</td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['blunted'] == '1') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="blunted1" value="1" name="blunted" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="blunted1">1</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['blunted'] == '2') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="blunted2" value="2" name="blunted" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="blunted2">2</label>
                                                                </div>
                                                        </td>
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['blunted'] == '3') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="blunted3" value="3" name="blunted" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="blunted3">3</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['blunted'] == '4') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="blunted4" value="4" name="blunted" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="blunted4">4</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['blunted'] == '5') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="blunted5" value="5" name="blunted" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="blunted5">5</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['blunted'] == '6') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="blunted6" value="6" name="blunted" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="blunted6">6</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['blunted'] == '7') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="blunted7" value="7" name="blunted" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="blunted7">7</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                        <div class="col-sm-1" id="blunted_result"></div>
                                                        </td>


                                                </tr>

                                                <!-- 17 -->
                                                <tr style="border:1px solid #000;margin: 45px;">
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;17</td>
                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Excitement (P) มีท่าทีลุกลี้ลุกลน มีปฏิกิริยาโต้ตอบเร็ว อยู่ไม่เป็นสุข</td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['excitement'] == '1') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="excitement1" value="1" name="excitement" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="excitement1">1</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['excitement'] == '2') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="excitement2" value="2" name="excitement" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="excitement2">2</label>
                                                                </div>
                                                        </td>
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['excitement'] == '3') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="excitement3" value="3" name="excitement" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="excitement3">3</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['excitement'] == '4') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="excitement4" value="4" name="excitement" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="excitement4">4</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['excitement'] == '5') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="excitement5" value="5" name="excitement" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="excitement5">5</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['excitement'] == '6') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="excitement6" value="6" name="excitement" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="excitement6">6</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['excitement'] == '7') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="excitement7" value="7" name="excitement" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="excitement7">7</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                        <div class="col-sm-1" id="excitement_result"></div>
                                                        </td>

                                                </tr>

                                                <!-- 18 -->
                                                <tr style="border:1px solid #000;margin: 45px;">
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;18</td>
                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Disorientation (G) ถามวันที่ สถานที่ เวลา บุคคล</td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['disorientation'] == '1') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="disorientation1" value="1" name="disorientation" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="disorientation1">1</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['disorientation'] == '2') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="disorientation2" value="2" name="disorientation" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="disorientation2">2</label>
                                                                </div>
                                                        </td>
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['disorientation'] == '3') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="disorientation3" value="3" name="disorientation" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="disorientation3">3</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['disorientation'] == '4') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="disorientation4" value="4" name="disorientation" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="disorientation4">4</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['disorientation'] == '5') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="disorientation5" value="5" name="disorientation" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="disorientation5">5</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['disorientation'] == '6') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="disorientation6" value="6" name="disorientation" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="disorientation6">6</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['disorientation'] == '7') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="disorientation7" value="7" name="disorientation" oninput="oninputCheckValue()">
                                                                        <label class="custom-control-label" for="disorientation7">7</label>
                                                                </div>
                                                        </td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                        <div class="col-sm-1" id="disorientation_result"></div>
                                                        
                                                        </td>

                                                </tr>







                                        </table>

<br>
                                        <div class="col-sm-6 offset-md-4" id="score_total_result"></div>
                                        <div class="col-sm-1 offset-md-1">
                                        

                                        <?php





    // Assume $row['total_sum'] has the value you want to check
    $total_sum = isset($row['total_sum']) ? (int)$row['total_sum'] : 0;

    // Set the background color based on the value of total_sum
    if ($total_sum >= 1 && $total_sum <= 36) {
        $bg_color = 'green';
        $message = 'แนะนำประเมินต่อทุก 1 สัปดาห์';
        if ($id == null ){
          $date1 =  date('Y-m-d');
          $date_alert = date('Y-m-d',strtotime($date1 . "+1 days"));
          echo $date_alert;
        }else{

        }

    } elseif ($total_sum >= 37 && $total_sum <= 40) {
        $bg_color = 'orange';
        $message = 'แนะนำประเมินต่อทุก 2 วัน';
    } elseif ($total_sum > 40) {
        $bg_color = 'red';
        $message = 'แนะนำประเมินวันละ 1 ครั้ง';
    } else {
        $bg_color = ''; // default if the value is outside the range
    }
?>

<div class='badge text-white mt-1 font-weight-bold' style="font-size:100%; background-color: <?= htmlspecialchars($bg_color) ?>;">
    <!-- Your content here -->
    คะแนนรวม <?= htmlspecialchars($total_sum) ?> คะแนน
    <br>
    <?= htmlspecialchars($message) ?>
</div>

<!---
                                        <input type="text" class="form-control form-control-sm" id="total_sum" name="total_sum" value="<?= (isset($row['total_sum']) ? htmlspecialchars($row['total_sum']) : '') ?>" readonly>
                                        
-->                                     

                                </div>
                                <br>
                                </div>

                                <div class="form-group row">
                                
                            </div><hr>

                        </div>

                        <div class="card-group pb-3 ">
                                <div class="card">
                                        <div class="card-body" style=" overflow-y: auto;">
                                                <div class="row">
                                                        <div class="col-md-12">





                                                                <div class="row">
                                                                        <div id="show_check_save"></div>
                                                                        <input type="hidden" id="an" name="an" value="<?= htmlspecialchars($an) ?>">
                                                                        <input type="hidden" id="hn" name="hn" value="<?= htmlspecialchars($hn) ?>">
                                                                        <input type="hidden" id="version" name="version" value="<?= htmlspecialchars($row['version']) ?>">
                                                                        <input type="hidden" id="id" name="id" value="<?= htmlspecialchars($row['id']) ?>">
                                                                        <input type="hidden" id="create_user" name="create_user" value="<?= htmlspecialchars($_SESSION['name']) ?>">
                                                                        <input type="hidden" id="create_datetime" name="create_datetime" value="<?= htmlspecialchars($row['create_datetime']) ?>">
                                                                        <!-- <input type="hidden" id="score_total_result" name="total_sum" value="10">-->


                                                                        <div class="col-md-12 text-right">
                                                                                <?php
                                                                                if ((
                                                                                        Session::checkPermission('IPD_NURSE_NOTE', 'ADD')
                                                                                ) && (ReportQueryUtils::checkReadOnly($an))) { ?>
                                                                                        <button type="button" class="btn btn-primary" id="btn_lr_report1" onclick="form_save()"><i class="fas fa-save"></i> บันทึก</button>
                                                                                <?php } ?>
                                                                                <a href="mental-health1-pdf.php?an=<?php echo $an; ?>&loginname=<?php echo $loginname; ?>" target="_blank" class="btn btn-secondary"><i class="fas fa-file-pdf"></i> Print <U>PDF</U> File</a>
                                                                        </div>
                                                                </div>
                                                        </div>
                                                </div>
                                                <br>
                                                
                                                <script src="../include/my_function.js"></script>
                                                <script type = "text/javascript">




function myFunction() {
  alert("Page is loaded");
}


function display_score(sum_score, score_display_id){

      //  console.log(score_display_id)
        if(sum_score === "" || sum_score === null) {
            $('#'+score_display_id).html("");
        }else{

            if(sum_score != null){
                let MEWS_COLOR = ['#45c351','#45c351','#45c351','#e6b728','#e8832a','#e8832a','#e51616','#e51616'];
                $('#'+score_display_id).html("<div class='badge text-white mt-1 font-weight-bold' style='class='badge text-white mt-1 font-weight-bold' background-color: " + MEWS_COLOR[sum_score] + ";'>" + sum_score + "</div>");
            }
        }
    }

    function display_score_total(sum_score, score_display_id){
        if(sum_score === "" || sum_score === null) {
            $('#'+score_display_id).html("");
        }else{
            color = 'inherit';
            if(sum_score > 0 && sum_score <= 36){
                color = '#45c351';
            }else if(sum_score >= 37 && sum_score <= 40){
                color = '#e6b728';
            }else if(sum_score >= 41){
                color = '#e51616';
            }
            $('#'+score_display_id).html("<div class='alert text-white text-center font-weight-bold' style='font-size:100%;  background-color: " + color + ";'> ผลรวม : " + sum_score + "</div>");
        }
    }

 
function oninputCheckValue(){
        
       // let somatic_concern = bp_rs1($('input[name="somatic_concern"]:checked').val()/*$("#somatic_concern").val()*/);
       
       let somatic_concern = $('input[name="somatic_concern"]:checked').val() ?? 0; //0 if null
       let anxiety = $('input[name="anxiety"]:checked').val() ?? 0;
       let emotional = $('input[name="emotional"]:checked').val() ?? 0;
       let conceptual = $('input[name="conceptual"]:checked').val() ?? 0;
       let guilt = $('input[name="guilt"]:checked').val() ?? 0;
       let tension = $('input[name="tension"]:checked').val() ?? 0;
       let mannerism = $('input[name="mannerism"]:checked').val() ?? 0;
       let depressive = $('input[name="depressive"]:checked').val() ?? 0;
       let grandiosity = $('input[name="grandiosity"]:checked').val() ?? 0;
       let hostility = $('input[name="hostility"]:checked').val() ?? 0;
       let suspiciousness = $('input[name="suspiciousness"]:checked').val() ?? 0;
       let hallucinatory = $('input[name="hallucinatory"]:checked').val() ?? 0;
       let motor = $('input[name="motor"]:checked').val() ?? 0;
       let uncooperativeness = $('input[name="uncooperativeness"]:checked').val() ?? 0;
       let unusual = $('input[name="unusual"]:checked').val() ?? 0;
       let blunted = $('input[name="blunted"]:checked').val() ?? 0;
       let excitement = $('input[name="excitement"]:checked').val() ?? 0;
       let disorientation = $('input[name="disorientation"]:checked').val() ?? 0;

        let sum_score = (parseFloat(somatic_concern)+parseFloat(anxiety)+parseFloat(emotional)+parseFloat(conceptual)+parseFloat(guilt)+parseFloat(tension)
        +parseFloat(mannerism)+parseFloat(depressive)+parseFloat(grandiosity)+parseFloat(hostility)+parseFloat(suspiciousness)+parseFloat(hallucinatory)
        +parseFloat(motor)+parseFloat(uncooperativeness)+parseFloat(unusual)+parseFloat(blunted)+parseFloat(excitement)+parseFloat(disorientation)
        )
 
        
  
  
        if (sum_score >= 37 && sum_score <= 40) {

                //document.getElementById('total_score').value = sum_score;
        // Create a nicely formatted message
        let message = `
        Total Score: 
         ${sum_score}
        แนะนำประเมินทุก 2 วัน
        `;

        // Display the alert
        alert(message);
    }else if(sum_score >= 41 ) {
let message = `
Total Score: 
${sum_score}
แนะนำประเมินทุกวัน
`;

// Display the alert
alert(message);
}

    display_score(somatic_concern, "somatic_concern_result");
    display_score(anxiety, "anxiety_result");
    display_score(emotional, "emotional_result");
    display_score(conceptual, "conceptual_result");
    display_score(guilt, "guilt_result");
    display_score(tension, "tension_result");
    display_score(mannerism, "mannerism_result");
    display_score(depressive, "depressive_result");
    display_score(grandiosity, "grandiosity_result");
    display_score(hostility, "hostility_result");
    display_score(suspiciousness, "suspiciousness_result");
    display_score(hallucinatory, "hallucinatory_result");
    display_score(motor, "motor_result");
    display_score(uncooperativeness, "uncooperativeness_result");
    display_score(unusual, "unusual_result");
    display_score(blunted, "blunted_result");
    display_score(excitement, "excitement_result");
    display_score(disorientation, "disorientation_result");
    display_score_total(sum_score, "score_total_result");
        
    }

                                                        

                                                        function form_save() {

                                                                var somatic_concern = $('input[name="somatic_concern"]:checked').val();

                                                                if (somatic_concern == undefined) {
                                                                        $('[name="somatic_concern"]').focus();
                                                                        //alert(depart)
                                                                        alert('somatic_concern');
                                                                }
                                                                

                                                                var url_update = "form-mental-health21-update.php";
                                                                var url_save = "form-mental-health21-save.php";
                                                                var id = $("#id").val();
                                                                var my_form = $("#my_form").serialize();

                                                                if (id == "") {
                                                                        $.post(url_save, my_form, function(data) {
                                                                                        $("#show_check_save").html(data);
                                                                                        // window.history.back();
                                                                                        // alert("บันทึกข้อมูลสำเร็จ");
                                                                                        self.close();
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