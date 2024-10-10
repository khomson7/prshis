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

$permissionCheck = Session::checkPermissionAndShowMessage('PRS_MENTAL_HEAL', 'VIEW');
$permissionCheckJson = json_encode($permissionCheck);
require_once '../include/session-modal.php';

//Session::checkPermissionAndShowMessage('PRS_MENTAL_HEAL1', 'VIEW');

require_once '../mains/ipd-show-patient-main.php'; //เป็นส่วนที่แสดง ข้อมูลผู้ป่วย เช่น รูป,hn,an,ชื่อ-สกุล,แพ้ยา ฯลฯ
require_once '../mains/ipd-show-patient-sticky.php';

require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';
require_once '../include/ReportQueryUtils.php';
$conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
$an = $_REQUEST['an']; //รับค่า an
$hn = KphisQueryUtils::getHnByAn($an); // function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา
$vn = KphisQueryUtils::getVnByAn($an);


Session::insertSystemAccessLog(json_encode(array(
    'form' => 'ICU1-FORM',
    'an' => $an,
), JSON_UNESCAPED_UNICODE));




//----------------------เช็คว่า an นี้ มีข้อมูลหรือไม่

$sql = "SELECT *
                FROM `prs_mental_health1`
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

$id = '20'; //ลำดับในตาราง prs_link_menu
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
            <div class="col-md-11">
                <h4><?= htmlspecialchars($menu_name) ?>
                    <?= htmlspecialchars(DbConstant::HOSPITAL_NAME) ?><?php if ($production == "2") { ?>

                    <font color="red">ช่วงทดลอง</font>
                <?php } else { ?>

                <? } ?>
                </h4>
            </div>

        </div>


        <div class="card-group pb-3 ">
            <div class="card">
                <div class="card-body" style=" overflow-y: auto;">
                    <div class="row">
                        <div class="col-md-12">


                            <div class="form-group row alert alert-dark text-left">
                                <B>1.ลักษณะโดยทั่วไป</B>
                            </div>
                            <div class="form-group row alert alert-dark text-left">
                                <B>1.1 Generation appearance</B>
                            </div>


                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>1.1.1 รูปร่างลักษณะ</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['appearance'] == '1') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="appearance1" name="appearance" value="1" onchange="custom_check('off_appearance');">
                                    <label class="custom-control-label" for="appearance1">อ้วน</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['appearance'] == '2') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="appearance2" name="appearance" value="2" onchange="custom_check('off_appearance');">
                                    <label class="custom-control-label" for="appearance2">สันทัด</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['appearance'] == '3') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="appearance3" name="appearance" value="3" onchange="custom_check('off_appearance');">
                                    <label class="custom-control-label" for="appearance3">ผอม</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['appearance'] == '4') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="appearance4" name="appearance" value="4" onchange="custom_check('off_appearance');">
                                    <label class="custom-control-label" for="appearance4">พิการ</label>
                                </div>


                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if (
                                                            $row['appearance'] != '1'
                                                            && $row['appearance'] != '2' && $row['appearance'] != '3' && $row['appearance'] != '4'
                                                            && $row['appearance_check'] == '1'
                                                            && $row['appearance'] != NULL
                                                        ) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="appearance_check1" name="appearance_check" value="1"  onchange="custom_check('off_appearance_check1');">
                                                        
                                    <label class="custom-control-label" for="appearance_check1">มีแผลเป็น</label>
                                </div>

                                <div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm" id="appearance5_text" name="appearance" value="<?php if (
                                                                                                                                                $row['appearance'] != '1'
                                                                                                                                                && $row['appearance'] != '2' && $row['appearance'] != '3' && $row['appearance'] != '4'
                                                                                                                                                && $row['appearance_check'] == '1'
                                                                                                                                                && $row['appearance'] != NULL
                                                                                                                                            ) {
                                                                                                                                                echo htmlspecialchars($row['appearance']);
                                                                                                                                            } ?>" <?php if (!($row['appearance'] != '1'
                                                                                                                                                        && $row['appearance'] != '2' && $row['appearance'] != '3' && $row['appearance'] != '4'
                                                                                                                                                        && $row['appearance_check'] == '1'
                                                                                                                                                        && $row['appearance'] != NULL)) {
                                                                                                                                                        echo 'disabled';
                                                                                                                                                    } ?>>
                                </div>

                              <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if (
                                                            $row['appearance'] != '1'
                                                            && $row['appearance'] != '2' && $row['appearance'] != '3' && $row['appearance'] != '4'
                                                            && $row['appearance_check'] == '2'
                                                            && $row['appearance'] != NULL
                                                        ) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="appearance_check2" name="appearance_check" value ="2" onchange="custom_check('on_appearance_check');">
                                                        
                                    <label class="custom-control-label" for="appearance_check2">อื่นๆ</label>
                                </div> 

                                <div class="col-sm-2">
                                    <input type="text" class="form-control form-control-sm" id="appearance6_text" name="appearance" value="<?php if (
                                                                                                                                                $row['appearance'] != '1'
                                                                                                                                                && $row['appearance'] != '2' && $row['appearance'] != '3' && $row['appearance'] != '4'
                                                                                                                                                && $row['appearance_check'] == '2'
                                                                                                                                                && $row['appearance'] != NULL
                                                                                                                                            ) {
                                                                                                                                                echo htmlspecialchars($row['appearance']);
                                                                                                                                            } ?>" <?php if (!($row['appearance'] != '1'
                                                                                                                                                        && $row['appearance'] != '2' && $row['appearance'] != '3' && $row['appearance'] != '4'
                                                                                                                                                        && $row['appearance_check'] == '2'
                                                                                                                                                        && $row['appearance'] != NULL)) {
                                                                                                                                                        echo 'disabled';
                                                                                                                                                    } ?>>
                                </div>


                            </div>
                            <br>

                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;<label>1.1.2 การแต่งกาย</label>
                                <div class="custom-control custom-radio col-sm-2">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['dress'] == '1') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="dress1" name="dress" value="1">
                                    <label class="custom-control-label" for="dress1">สะอาด เหมาะสมกับวัย</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <input type="radio" <?php if ($row['dress'] == '2') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="dress" name="dress" value="2">
                                    <label class="custom-control-label" for="dress2">สะอาด ไม่เหมาะสมกับวัย</label>
                                </div>



                            </div>
                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <div class="custom-control custom-radio col-sm-2">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    <input type="radio" <?php if ($row['dress'] == '3') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="dress3" name="dress" value="3">
                                    <label class="custom-control-label" for="dress3">สกปรก เหมาะสมกับวัย</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <input type="radio" <?php if ($row['dress'] == '4') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="dress4" name="dress" value="4">
                                    <label class="custom-control-label" for="dress4">สกปรก ไม่เหมาะสมกับวัย</label>
                                </div>



                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>1.1.3 พฤติกรรมการเคลื่อนไหวร่างกาย(Psychomotor)</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body_movement_behavior'] == 'ปกติ') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="body_movement_behavior1" name="body_movement_behavior" value="ปกติ" onchange="custom_check('off_body_movement_behavior');">
                                    <label class="custom-control-label" for="body_movement_behavior1">ปกติ</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body_movement_behavior'] == 'น้อยกว่าปกติ') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="body_movement_behavior2" name="body_movement_behavior" value="น้อยกว่าปกติ" onchange="custom_check('off_body_movement_behavior');">
                                    <label class="custom-control-label" for="body_movement_behavior2">น้อยกว่าปกติ</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body_movement_behavior'] == 'มากกว่าปกติ') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="body_movement_behavior3" name="body_movement_behavior" value="มากกว่าปกติ" onchange="custom_check('off_body_movement_behavior');">
                                    <label class="custom-control-label" for="body_movement_behavior3">มากกว่าปกติ</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if (
                                                            $row['body_movement_behavior'] != 'ปกติ'
                                                            && $row['body_movement_behavior'] != 'น้อยกว่าปกติ'
                                                            && $row['body_movement_behavior'] != 'มากกว่าปกติ'
                                                            && $row['body_movement_behavior'] != 'เคลื่อนไหวซ้ำๆ'
                                                            && $row['body_movement_behavior'] != 'กระตุก'
                                                            && $row['body_movement_behavior'] != 'อยู่ไม่สุข'
                                                            && $row['body_movement_behavior'] != 'กระสับกระส่าย'
                                                            && $row['body_movement_behavior'] != NULL
                                                        ) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="body_movement_behavior4" name="body_movement_behavior" onchange="custom_check('on_body_movement_behavior');">
                                    <label class="custom-control-label" for="body_movement_behavior4">ผิดปกติ</label>
                                </div>

                                <div class="col-sm-3">
                                    <input type="text" class="form-control form-control-sm" id="body_movement_behavior_text" name="body_movement_behavior" value="<?php if (
                                                                                                                                                                        $row['body_movement_behavior'] != 'ปกติ'
                                                                                                                                                                        && $row['body_movement_behavior'] != 'น้อยกว่าปกติ'
                                                                                                                                                                        && $row['body_movement_behavior'] != 'มากกว่าปกติ'
                                                                                                                                                                        && $row['body_movement_behavior'] != 'เคลื่อนไหวซ้ำๆ'
                                                                                                                                                                        && $row['body_movement_behavior'] != 'กระตุก'
                                                                                                                                                                        && $row['body_movement_behavior'] != 'อยู่ไม่สุข'
                                                                                                                                                                        && $row['body_movement_behavior'] != 'กระสับกระส่าย'
                                                                                                                                                                        && $row['body_movement_behavior'] != NULL
                                                                                                                                                                    ) {
                                                                                                                                                                        echo htmlspecialchars($row['body_movement_behavior']);
                                                                                                                                                                    } ?>" <?php if (!($row['body_movement_behavior'] != 'ปกติ'
                                                                                                                                                                                && $row['body_movement_behavior'] != 'น้อยกว่าปกติ'
                                                                                                                                                                                && $row['body_movement_behavior'] != 'มากกว่าปกติ'
                                                                                                                                                                                && $row['body_movement_behavior'] != 'เคลื่อนไหวซ้ำๆ'
                                                                                                                                                                                && $row['body_movement_behavior'] != 'กระตุก'
                                                                                                                                                                                && $row['body_movement_behavior'] != 'อยู่ไม่สุข'
                                                                                                                                                                                && $row['body_movement_behavior'] != 'กระสับกระส่าย'
                                                                                                                                                                                && $row['body_movement_behavior'] != NULL)) {
                                                                                                                                                                                echo 'disabled';
                                                                                                                                                                            } ?>>
                                </div>


                            </div>
                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                &nbsp;&nbsp;&nbsp;<div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['body_movement_behavior'] == 'เคลื่อนไหวซ้ำๆ') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="body_movement_behavior5" name="body_movement_behavior" value="เคลื่อนไหวซ้ำๆ" onchange="custom_check('off_body_movement_behavior');">
                                    <label class="custom-control-label" for="body_movement_behavior5">เคลื่อนไหวซ้ำๆ</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body_movement_behavior'] == 'กระตุก') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="body_movement_behavior6" name="body_movement_behavior" value="กระตุก" onchange="custom_check('off_body_movement_behavior');">
                                    <label class="custom-control-label" for="body_movement_behavior6">กระตุก</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body_movement_behavior'] == 'อยู่ไม่สุข') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="body_movement_behavior7" name="body_movement_behavior" value="อยู่ไม่สุข" onchange="custom_check('off_body_movement_behavior');">
                                    <label class="custom-control-label" for="body_movement_behavior7">อยู่ไม่สุข</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-2">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body_movement_behavior'] == 'กระสับกระส่าย') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="body_movement_behavior8" name="body_movement_behavior" value="กระสับกระส่าย" onchange="custom_check('off_body_movement_behavior');">
                                    <label class="custom-control-label" for="body_movement_behavior8">กระสับกระส่าย</label>
                                </div>

                            </div>
                            <br>

                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;<label>1.1.4 ท่าทีต่อผู้ตรวจ(Attitude)</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['attitude'] == '1') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="attitude1" name="attitude" value="1">
                                    <label class="custom-control-label" for="attitude1">เป็นมิตร</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <input type="radio" <?php if ($row['attitude'] == '2') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="attitude2" name="attitude" value="2">
                                    <label class="custom-control-label" for="attitude2">ต่อต้าน</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <input type="radio" <?php if ($row['attitude'] == '3') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="attitude3" name="attitude" value="3">
                                    <label class="custom-control-label" for="attitude3">ไม่ไว้วางใจ</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <input type="radio" <?php if ($row['attitude'] == '4') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="attitude4" name="attitude" value="4">
                                    <label class="custom-control-label" for="attitude4">ไม่เชื่อถือ</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <input type="radio" <?php if ($row['attitude'] == '5') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="attitude5" name="attitude" value="5">
                                    <label class="custom-control-label" for="attitude5">ยียวน</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <input type="radio" <?php if ($row['attitude'] == '6') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="attitude6" name="attitude" value="6">
                                    <label class="custom-control-label" for="attitude6">ปิดบังข้อมูล</label>
                                </div>



                            </div>
                            <br>


                            <div class="form-group row alert alert-dark text-left">
                                <B>2. การพูดและกระแสคำพูด (speech and stream of talk)</B>
                            </div>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>2.1 อัตราการพูด(Rate)</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['rate'] == '1') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="rate1" name="rate" value="1">
                                    <label class="custom-control-label" for="rate1">ปกติ</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <input type="radio" <?php if ($row['rate'] == '2') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="rate2" name="rate" value="2">
                                    <label class="custom-control-label" for="rate2">เร็ว</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <input type="radio" <?php if ($row['rate'] == '3') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="rate3" name="rate" value="3">
                                    <label class="custom-control-label" for="rate3">ช้า</label>
                                </div>


                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>2.2 จังหวะ(Rhythm)</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['rhythm'] == '1') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="rhythm1" name="rhythm" value="1">
                                    <label class="custom-control-label" for="rhythm1">พูดราบเรียบ</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <input type="radio" <?php if ($row['rhythm'] == '2') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="rhythm2" name="rhythm" value="2">
                                    <label class="custom-control-label" for="rhythm2">ติดขัด</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <input type="radio" <?php if ($row['rhythm'] == '3') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="rhythm3" name="rhythm" value="3">
                                    <label class="custom-control-label" for="rhythm3">ติดอ่าง</label>
                                </div>


                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>2.3 ความผิดปกติของคำพูด</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['speech_disorder'] == 'ปกติ') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="speech_disorder1" name="speech_disorder" value="ปกติ" onchange="custom_check('off_speech_disorder');">
                                    <label class="custom-control-label" for="speech_disorder1">ปกติ</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if (
                                                            $row['speech_disorder'] != 'ปกติ'
                                                            && $row['speech_disorder'] != 'neologism'
                                                            && $row['speech_disorder'] != 'world salad'
                                                            && $row['speech_disorder'] != NULL
                                                        ) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="speech_disorder2" onchange="custom_check('on_speech_disorder');">
                                    <label class="custom-control-label" for="speech_disorder2">ผิดปกติ</label>
                                </div>

                                <div class="col-sm-3">
                                    <input type="text" class="form-control form-control-sm" id="speech_disorder_text" name="speech_disorder" value="<?php if (
                                                                                                                                                        $row['speech_disorder'] != 'ปกติ'
                                                                                                                                                        && $row['speech_disorder'] != 'neologism'
                                                                                                                                                        && $row['speech_disorder'] != 'world salad'
                                                                                                                                                        && $row['speech_disorder'] != NULL
                                                                                                                                                    ) {
                                                                                                                                                        echo htmlspecialchars($row['speech_disorder']);
                                                                                                                                                    } ?>" <?php if (!($row['speech_disorder'] != 'ปกติ'
                                                                                                                                                                && $row['speech_disorder'] != 'neologism'
                                                                                                                                                                && $row['speech_disorder'] != 'world salad'
                                                                                                                                                                && $row['speech_disorder'] != NULL)) {
                                                                                                                                                                echo 'disabled';
                                                                                                                                                            } ?>>
                                </div>

                                <div class="custom-control custom-radio col-sm-3">
                                    <input type="radio" <?php if ($row['speech_disorder'] == 'neologism') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="speech_disorder3" name="speech_disorder" value="neologism" onchange="custom_check('off_speech_disorder');">
                                    <label class="custom-control-label" for="speech_disorder3">คำพูดฟังแล้วไม่รู้ความหมาย( neologism )</label>
                                </div>



                            </div>
                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <div class="custom-control custom-radio col-sm-3">
                                    <input type="radio" <?php if ($row['speech_disorder'] == 'world salad') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="speech_disorder4" name="speech_disorder" value="world salad" onchange="custom_check('off_speech_disorder');">
                                    <label class="custom-control-label" for="speech_disorder4">เอาคำหรือวลีมารวมกันแต่ไม่มีความหมาย (world salad)</label>
                                </div>
                            </div>
                            <br>

                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;<label>2.4 กระแสคำพูด(stream of talk)</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['stream_of_talk'] == '1') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="stream_of_talk1" name="stream_of_talk" value="1">
                                    <label class="custom-control-label" for="stream_of_talk1">สมเหตุสมผล</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <input type="radio" <?php if ($row['stream_of_talk'] == '2') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="stream_of_talk2" name="stream_of_talk" value="2">
                                    <label class="custom-control-label" for="stream_of_talk2">ไม่สมเหตุสมผล(illogicall)</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <input type="radio" <?php if ($row['stream_of_talk'] == '3') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="stream_of_talk3" name="stream_of_talk" value="3">
                                    <label class="custom-control-label" for="stream_of_talk3">ประติดประต่อ</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <input type="radio" <?php if ($row['stream_of_talk'] == '4') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="stream_of_talk4" name="stream_of_talk" value="4">
                                    <label class="custom-control-label" for="stream_of_talk4">ไม่ประติดประต่อ( incoherrance )</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <input type="radio" <?php if ($row['stream_of_talk'] == '5') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="stream_of_talk5" name="stream_of_talk" value="5">
                                    <label class="custom-control-label" for="stream_of_talk5">ตรงคำถาม</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <input type="radio" <?php if ($row['stream_of_talk'] == '6') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="stream_of_talk6" name="stream_of_talk" value="6">
                                    <label class="custom-control-label" for="stream_of_talk6">ไม่ตรงคำถาม( irrelevance )</label>
                                </div>

                            </div>
                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <input type="radio" <?php if ($row['stream_of_talk'] == '7') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="stream_of_talk7" name="stream_of_talk" value="7">
                                    <label class="custom-control-label" for="stream_of_talk7">พูดวกวน</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <input type="radio" <?php if ($row['stream_of_talk'] == '8') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="stream_of_talk8" name="stream_of_talk" value="8">
                                    <label class="custom-control-label" for="stream_of_talk8">ไม่พูดเลย ( mutism)</label>
                                </div>


                            </div>
                            <br>


                            <div class="form-group row alert alert-dark text-left">
                                <B>3. อารมณ์และการแสดงออก (Mood and affect)</B>
                            </div>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>3.1 พื้นฐานอารมณ์(Mood)</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['mood'] == 'เศร้า') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="mood1" name="mood" value="เศร้า" onchange="custom_check('off_mood');">
                                    <label class="custom-control-label" for="mood1">เศร้า</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['mood'] == 'หงุดหงิด') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="mood2" name="mood" value="หงุดหงิด" onchange="custom_check('off_mood');">
                                    <label class="custom-control-label" for="mood2">หงุดหงิด</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['mood'] == 'กังวล') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="mood3" name="mood" value="กังวล" onchange="custom_check('off_mood');">
                                    <label class="custom-control-label" for="mood3">กังวล</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['mood'] == 'ครื้นเครง') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="mood4" name="mood" value="ครื้นเครง" onchange="custom_check('off_mood');">
                                    <label class="custom-control-label" for="mood4">ครื้นเครง</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if (
                                                            $row['mood'] != 'เศร้า'
                                                            && $row['mood'] != 'หงุดหงิด'
                                                            && $row['mood'] != 'กังวล'
                                                            && $row['mood'] != 'ครื้นเครง'
                                                            && $row['mood'] != NULL
                                                        ) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="mood5" name="mood" onchange="custom_check('on_mood');">
                                    <label class="custom-control-label" for="mood5">อื่นๆ</label>
                                </div>

                                <div class="col-sm-3">
                                    <input type="text" class="form-control form-control-sm" id="mood_text" name="mood" value="<?php if (
                                                                                                                                    $row['mood'] != 'เศร้า'
                                                                                                                                    && $row['mood'] != 'หงุดหงิด'
                                                                                                                                    && $row['mood'] != 'กังวล'
                                                                                                                                    && $row['mood'] != 'ครื้นเครง'
                                                                                                                                    && $row['mood'] != NULL
                                                                                                                                ) {
                                                                                                                                    echo htmlspecialchars($row['mood']);
                                                                                                                                } ?>" <?php if (!($row['mood'] != 'เศร้า'
                                                                                                                                            && $row['mood'] != 'หงุดหงิด'
                                                                                                                                            && $row['mood'] != 'กังวล'
                                                                                                                                            && $row['mood'] != 'ครื้นเครง'
                                                                                                                                            && $row['mood'] != NULL)) {
                                                                                                                                            echo 'disabled';
                                                                                                                                        } ?>>
                                </div>





                            </div>
                            <br>

                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;<label>3.2 อารมณ์ที่แสดงออกขณะนั้น(Affect)</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['affect'] == '1') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="affect1" name="affect" value="1">
                                    <label class="custom-control-label" for="affect1">อารมณ์ดี</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <input type="radio" <?php if ($row['affect'] == '2') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="affect2" name="affect" value="2">
                                    <label class="custom-control-label" for="affect2">เศร้า</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <input type="radio" <?php if ($row['affect'] == '3') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="affect3" name="affect" value="3">
                                    <label class="custom-control-label" for="affect3">แสดงออกเล็กน้อย</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <input type="radio" <?php if ($row['affect'] == '4') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="affect4" name="affect" value="4">
                                    <label class="custom-control-label" for="affect4">ปราศจากอารมณ์</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <input type="radio" <?php if ($row['affect'] == '5') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="affect5" name="affect" value="5">
                                    <label class="custom-control-label" for="affect5">เหมาะสมกับสิ่งที่เล่า</label>
                                </div>




                            </div>

                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <div class="custom-control custom-radio col-sm-2">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['affect'] == '6') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="affect6" name="affect" value="6">
                                    <label class="custom-control-label" for="affect6">ไม่เหมาะสมกับสิ่งที่เล่า</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <input type="radio" <?php if ($row['affect'] == '7') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="affect7" name="affect" value="7">
                                    <label class="custom-control-label" for="affect7">คงที่</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <input type="radio" <?php if ($row['affect'] == '8') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="affect8" name="affect" value="8">
                                    <label class="custom-control-label" for="affect8">เปลี่ยนแปลงง่าย</label>
                                </div>


                            </div>

                            <br>



                            <div class="form-group row alert alert-dark text-left">
                                <B>4. ความคิด (Thought)</B>
                            </div>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>4.1 กระบวนความคิด</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['thought_process'] == '1') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="thought_process1" name="thought_process" value="1">
                                    <label class="custom-control-label" for="thought_process1">คิดช้า</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['thought_process'] == '2') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="thought_process2" name="thought_process" value="2">
                                    <label class="custom-control-label" for="thought_process2">คิดเร็ว</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php if ($row['thought_process'] == '3') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="thought_process3" name="thought_process" value="3">
                                    <label class="custom-control-label" for="thought_process3">คิดเร็วมากเปลี่ยนเรื่องคุยบ่อย</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['thought_process'] == '4') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="thought_process4" name="thought_process" value="4">
                                    <label class="custom-control-label" for="thought_process4">ความคิดต่อเนื่อง</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php if ($row['thought_process'] == '5') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="thought_process5" name="thought_process" value="5">
                                    <label class="custom-control-label" for="thought_process5">ความคิดไม่ต่อเนื่อง</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['thought_process'] == '6') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="thought_process6" name="thought_process" value="6">
                                    <label class="custom-control-label" for="thought_process6">ตรงคำถาม</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['thought_process'] == '7') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="thought_process7" name="thought_process" value="7">
                                    <label class="custom-control-label" for="thought_process7">ไม่ตรงคำถาม</label>
                                </div>



                            </div>
                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['thought_process'] == '8') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="thought_process8" name="thought_process" value="8">
                                    <label class="custom-control-label" for="thought_process8">ได้เรื่องราว</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['thought_process'] == '9') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="thought_process9" name="thought_process" value="9">
                                    <label class="custom-control-label" for="thought_process9">ไม่ได้เรื่องราว</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['thought_process'] == '10') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="thought_process10" name="thought_process" value="10">
                                    <label class="custom-control-label" for="thought_process10">มีเหตุผล</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row['thought_process'] == '11') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="thought_process11" name="thought_process" value="11">
                                    <label class="custom-control-label" for="thought_process11">ไม่มีเหตุผล</label>
                                </div>
                            </div>
                            <br>

                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;<label>4.2 เนื้อหาความคิด</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['thought_content'] == '1') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="thought_content1" name="thought_content" value="1">
                                    <label class="custom-control-label" for="thought_content1">ปกติ</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <input type="radio" <?php if ($row['thought_content'] == '2') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="thought_content2" name="thought_content" value="2">
                                    <label class="custom-control-label" for="thought_content2">หมกมุ่น</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <input type="radio" <?php if ($row['thought_content'] == '3') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="thought_content3" name="thought_content" value="3">
                                    <label class="custom-control-label" for="thought_content3">ย้ำคิดย้ำทำ</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <input type="radio" <?php if ($row['thought_content'] == '4') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="thought_content4" name="thought_content" value="4">
                                    <label class="custom-control-label" for="thought_content4">กลัวผิดปกติ</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <input type="radio" <?php if ($row['thought_content'] == '5') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="thought_content5" name="thought_content" value="5">
                                    <label class="custom-control-label" for="thought_content5">หลงผิด</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <input type="radio" <?php if ($row['thought_content'] == '6') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="thought_content6" name="thought_content" value="6">
                                    <label class="custom-control-label" for="thought_content6">คิดฆ่าตัวตาย</label>
                                </div>


                            </div>


                            <br>

                            <div class="form-group row alert alert-dark text-left">
                                <B>5.การรับรู้ (Perception)</B>
                            </div>


                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>5.1 อาการแปลสิ่งเร้าผิด(Illution)</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['illution'] == 'ไม่มี') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="illution1" name="illution" value="ไม่มี" onchange="custom_check('off_illution');">
                                    <label class="custom-control-label" for="illution1">ไม่มี</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if (
                                                                                    $row['illution'] != 'ไม่มี'
                                                                                    && $row['illution'] != NULL
                                                                                ) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="illution2" name="illution" onchange="custom_check('on_illution');">
                                    <label class="custom-control-label" for="illution2">มี ระบุ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="illution_text" name="illution" value="<?php if (
                                                                                                                                            $row['illution'] != 'ไม่มี'
                                                                                                                                            && $row['illution'] != NULL
                                                                                                                                        ) {
                                                                                                                                            echo htmlspecialchars($row['illution']);
                                                                                                                                        } ?>" <?php if (!($row['illution'] != 'ไม่มี'
                                                                                                                                                    && $row['illution'] != NULL)) {
                                                                                                                                                    echo 'disabled';
                                                                                                                                                } ?>>
                                </div>


                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>5.2 อาการประสาทหลอน(Hallucination)</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['hallucination'] == 'ไม่มี') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="hallucination1" name="hallucination" value="ไม่มี" onchange="custom_check('off_hallucination');">
                                    <label class="custom-control-label" for="hallucination1">ไม่มี</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['hallucination'] != 'ไม่มี' && $row['hallucination'] != NULL) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="hallucination2" onchange="custom_check('on_hallucination');">
                                    <label class="custom-control-label" for="hallucination2">มี ระบุ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="hallucination_text" name="hallucination" value="<?php if ($row['hallucination'] != 'ไม่มี' && $row['hallucination'] != NULL) {
                                                                                                                                                    echo htmlspecialchars($row['hallucination']);
                                                                                                                                                } ?>" <?php if (!($row['hallucination'] != 'ไม่มี' && $row['hallucination'] != NULL)) {
                                                echo 'disabled';
                                            } ?>>
                                </div>


                            </div>
                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label></label>


                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['vision'] != NULL) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="vision1" name="vision" onchange="custom_check('on_vision');">
                                    <label class="custom-control-label" for="vision1">การมองเห็น</label>
                                </div>

                                <div class="col-sm-2">
                                    <input type="text" placeholder="XXXXX" class="form-control form-control-sm" id="vision_text" name="vision" value="<?php if ($row['vision'] != NULL) {
                                                                                                                                                            echo htmlspecialchars($row['vision']);
                                                                                                                                                        } ?>" <?php if (!($row['vision'] != NULL)) {
                                                echo 'disabled';
                                            } ?>>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['hearing'] != NULL) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="hearing1" onchange="custom_check('on_hearing');">
                                    <label class="custom-control-label" for="hearing1">การได้ยิน</label>
                                </div>

                                <div class="col-sm-2">
                                    <input type="text" placeholder="XXXXX" class="form-control form-control-sm" id="hearing_text" name="hearing" value="<?php if ($row['hearing'] != NULL) {
                                                                                                                                                            echo htmlspecialchars($row['hearing']);
                                                                                                                                                        } ?>" <?php if (!($row['hearing'] != NULL)) {
                                                echo 'disabled';
                                            } ?>>
                                </div>


                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['tast_perception'] != NULL) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="tast_perception1" name="tast_perception"  onchange="custom_check('on_tast_perception');">
                                    <label class="custom-control-label" for="tast_perception1">การรับรู้รส</label>
                                </div>

                                <div class="col-sm-2">
                                    <input type="text" placeholder="XXXXX" class="form-control form-control-sm" id="tast_perception_text" name="tast_perception" value="<?php if ($row['tast_perception'] != NULL) {
                                                                                                                                                                            echo htmlspecialchars($row['tast_perception']);
                                                                                                                                                                        } ?>" <?php if (!($row['tast_perception'] != NULL)) {
                                                echo 'disabled';
                                            } ?>>
                                </div>



                            </div>

                            <br>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label></label>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['touch'] != NULL) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="touch1" name="touch" onchange="custom_check('on_touch');">
                                    <label class="custom-control-label" for="touch1">การสัมผัส</label>
                                </div>

                                <div class="col-sm-2">
                                    <input type="text" placeholder="XXXXX" class="form-control form-control-sm" id="touch_text" name="touch" value="<?php if ($row['touch'] != NULL) {
                                                                                                                                                        echo htmlspecialchars($row['touch']);
                                                                                                                                                    } ?>" <?php if (!($row['touch'] != NULL)) {
                                                echo 'disabled';
                                            } ?>>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['smell'] != NULL) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="smell1" name="smell" onchange="custom_check('on_smell');">
                                    <label class="custom-control-label" for="smell1">การได้กลิ่น</label>
                                </div>

                                <div class="col-sm-2">
                                    <input type="text" placeholder="XXXXX" class="form-control form-control-sm" id="smell_text" name="smell" value="<?php if ($row['smell'] != NULL) {
                                                                                                                                                        echo htmlspecialchars($row['smell']);
                                                                                                                                                    } ?>" <?php if (!($row['smell'] != NULL)) {
                                                echo 'disabled';
                                            } ?>>
                                </div>


                            </div>

                            <br>

                            <div class="form-group row alert alert-dark text-left">
                                <B>6. Cognitive Function</B>
                            </div>


                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;<label><b>6.1 Orientation</b></label>

                                &nbsp;&nbsp;&nbsp;&nbsp;<div class="custom-control custom-checkbox col-sm-1">
                                    <input type="checkbox" <?php if ($row['orientation'] == 'Y') {
                                                                echo 'checked="checked"';
                                                            } ?> class="custom-control-input" id="orientation" value="Y" name="orientation">
                                    <label class="custom-control-label" for="orientation"><B>รับรู้</B></label>
                                </div>

                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="checkbox" <?php if ($row['orientation_time'] == 'Y') {
                                                                echo 'checked="checked"';
                                                            } ?>class="custom-control-input" id="orientation_time" value="Y" name="orientation_time">
                                    <label class="custom-control-label" for="orientation_time">เวลา</label>
                                </div>

                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="checkbox" <?php if ($row['orientation_location'] == 'Y') {
                                                                echo 'checked="checked"';
                                                            } ?>class="custom-control-input" id="orientation_location" value="Y" name="orientation_location">
                                    <label class="custom-control-label" for="orientation_location">สถานที่</label>
                                </div>
                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="checkbox" <?php if ($row['orientation_person'] == 'Y') {
                                                                echo 'checked="checked"';
                                                            } ?>class="custom-control-input" id="orientation_person" value="Y" name="orientation_person">
                                    <label class="custom-control-label" for="orientation_person">บุคคล</label>
                                </div>

                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="checkbox" <?php if ($row['non_orientation'] == 'Y') {
                                                                echo 'checked="checked"';
                                                            } ?> class="custom-control-input" id="non_orientation" value="Y" name="non_orientation">
                                    <label class="custom-control-label" for="non_orientation"><B>
                                            <font color="red">ไม่รับรู้</font>
                                        </B></label>
                                </div>

                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="checkbox" <?php if ($row['non_orientation_time'] == 'Y') {
                                                                echo 'checked="checked"';
                                                            } ?>class="custom-control-input" id="non_orientation_time" value="Y" name="non_orientation_time">
                                    <label class="custom-control-label" for="non_orientation_time">เวลา</label>
                                </div>

                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="checkbox" <?php if ($row['non_orientation_location'] == 'Y') {
                                                                echo 'checked="checked"';
                                                            } ?>class="custom-control-input" id="non_orientation_location" value="Y" name="non_orientation_location">
                                    <label class="custom-control-label" for="non_orientation_location">สถานที่</label>
                                </div>
                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="checkbox" <?php if ($row['non_orientation_person'] == 'Y') {
                                                                echo 'checked="checked"';
                                                            } ?>class="custom-control-input" id="non_orientation_person" value="Y" name="non_orientation_person">
                                    <label class="custom-control-label" for="non_orientation_person">บุคคล</label>
                                </div>



                            </div>
                            <br>


                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;<label><b>6.2 Attention and Concentation</b></label>
                            </div>
                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label>- เอา 20 ลบทีละ 3 </label>

                                &nbsp;&nbsp;&nbsp;&nbsp;
                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="radio" <?php if ($row['attention1'] == '1') {
                                                                echo 'checked="checked"';
                                                            } ?>class="custom-control-input" id="attention11" value="1" name="attention1">
                                    <label class="custom-control-label" for="attention11">ทำได้</label>
                                </div>

                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="radio" <?php if ($row['attention1'] == '2') {
                                                                echo 'checked="checked"';
                                                            } ?>class="custom-control-input" id="attention12" value="2" name="attention1">
                                    <label class="custom-control-label" for="attention12">ทำไม่ได้</label>
                                </div>
                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="radio" <?php if ($row['attention1'] == '3') {
                                                                echo 'checked="checked"';
                                                            } ?>class="custom-control-input" id="attention13" value="3" name="attention1">
                                    <label class="custom-control-label" for="attention13">ทำได้บางส่วน</label>
                                </div>

                            </div>

                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label>- เอา 100 ลบทีละ 7 </label>

                                &nbsp;&nbsp;&nbsp;&nbsp;
                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="radio" <?php if ($row['attention2'] == '1') {
                                                                echo 'checked="checked"';
                                                            } ?>class="custom-control-input" id="attention21" value="1" name="attention2" >
                                    <label class="custom-control-label" for="attention21">ทำได้</label>
                                </div>

                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="radio" <?php if ($row['attention2'] == '2') {
                                                                echo 'checked="checked"';
                                                            } ?>class="custom-control-input" id="attention22" value="2" name="attention2">
                                    <label class="custom-control-label" for="attention22">ทำไม่ได้</label>
                                </div>
                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="radio" <?php if ($row['attention2'] == '3') {
                                                                echo 'checked="checked"';
                                                            } ?>class="custom-control-input" id="attention23" value="3" name="attention2">
                                    <label class="custom-control-label" for="attention23">ทำได้บางส่วน</label>
                                </div>

                            </div>

                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label>- อ่านเลขแล้วให้พูดตาม พูดวน (ปกติจะพูดตามได้ 6 - 7 หลัก พูดทวนได้ 4 - 5 หลัก)</label>

                                &nbsp;&nbsp;&nbsp;&nbsp;
                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="radio" <?php if ($row['attention3'] == '1') {
                                                                echo 'checked="checked"';
                                                            } ?>class="custom-control-input" id="attention31" value="1" name="attention3">
                                    <label class="custom-control-label" for="attention31">ทำได้</label>
                                </div>

                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="radio" <?php if ($row['attention3'] == '2') {
                                                                echo 'checked="checked"';
                                                            } ?>class="custom-control-input" id="attention32" value="2" name="attention3">
                                    <label class="custom-control-label" for="attention32">ทำไม่ได้</label>
                                </div>
                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="radio" <?php if ($row['attention3'] == '3') {
                                                                echo 'checked="checked"';
                                                            } ?>class="custom-control-input" id="attention33" value="3" name="attention3">
                                    <label class="custom-control-label" for="attention33">ทำได้บางส่วน</label>
                                </div>

                            </div>
                            <br>


                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;<label><b>6.3 Memory</b></label>
                            </div>
                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label>- ความจำในช่วงเวลา เป็น นาที ชั่วโมง หรือ วัน (Recent memory) </label>

                                &nbsp;&nbsp;&nbsp;&nbsp;
                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="radio" <?php if ($row['memory1'] == '1') {
                                                                echo 'checked="checked"';
                                                            } ?>class="custom-control-input" id="memory11" value="1" name="memory1">
                                    <label class="custom-control-label" for="memory11">บอกถูก</label>
                                </div>

                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="radio" <?php if ($row['memory1'] == '2') {
                                                                echo 'checked="checked"';
                                                            } ?>class="custom-control-input" id="memory12" value="2" name="memory1">
                                    <label class="custom-control-label" for="memory12">บอกไม่ได้</label>
                                </div>

                            </div>

                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label>- ความจำระยะสั้น (Recall memory) (พูดคำว่า ดอกไม้ เก้าอี้ รถไฟ แล้วคุยเรื่องอื่น นาน 5 นาที แล้วถามผู้ป่วย) </label>

                                &nbsp;&nbsp;&nbsp;&nbsp;
                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="radio" <?php if ($row['memory2'] == '1') {
                                                                echo 'checked="checked"';
                                                            } ?>class="custom-control-input" id="memory21" value="1" name="memory2">
                                    <label class="custom-control-label" for="memory21">บอกถูก</label>
                                </div>

                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="radio" <?php if ($row['memory2'] == '2') {
                                                                echo 'checked="checked"';
                                                            } ?>class="custom-control-input" id="memory22" value="2" name="memory2">
                                    <label class="custom-control-label" for="memory22">บอกไม่ได้</label>
                                </div>

                            </div>
                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label><b>- ความจำในอดีต ( Remote memory) </b></label>

                                &nbsp;&nbsp;&nbsp;&nbsp;
                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="radio" <?php if ($row['memory3'] == '1') {
                                                                echo 'checked="checked"';
                                                            } ?>class="custom-control-input" id="memory31" value="1" name="memory3">
                                    <label class="custom-control-label" for="memory31">บอกถูก</label>
                                </div>

                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="radio" <?php if ($row['memory3'] == '2') {
                                                                echo 'checked="checked"';
                                                            } ?>class="custom-control-input" id="memory32" value="2" name="memory3">
                                    <label class="custom-control-label" for="memory32">บอกไม่ได้</label>
                                </div>

                            </div>
                            <br>

                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;<label><b>6.4 General Knowledge ถามความรู้ทั่วไป เช่น สัปดาห์หนึ่งมีกี่วัน</b></label>

                                &nbsp;&nbsp;&nbsp;&nbsp;

                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="radio" <?php if ($row['general_khowledge'] == '1') {
                                                                echo 'checked="checked"';
                                                            } ?>class="custom-control-input" id="general_khowledge1" value="1" name="general_khowledge">
                                    <label class="custom-control-label" for="general_khowledge1">บอกถูก</label>
                                </div>

                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="radio" <?php if ($row['general_khowledge'] == '2') {
                                                                echo 'checked="checked"';
                                                            } ?>class="custom-control-input" id="general_khowledge2" value="2" name="general_khowledge">
                                    <label class="custom-control-label" for="general_khowledge2">บอกไม่ได้</label>
                                </div>

                            </div>
                            <br>

                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;<label><b>6.5 Abstract Thinking</b></label>
                            </div>


                            <div class="row">

                                <div class="col-md-4 col-sm-4">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label><b>1. ถามความแตกต่าง
                                        </b></label>
                                    <div class="custom-control custom-checkbox col-sm-5">
                                        <label class="custom-control">กลางวันกับกลางคืน</label>
                                    </div>
                                    <div class="custom-control custom-checkbox col-sm-5">
                                        <label class="custom-control">เด็กกับคนแคระ</label>
                                    </div>
                                    <div class="custom-control custom-checkbox col-sm-5">
                                        <label class="custom-control">ต้นมะเขือกับต้นโพธิ์</label>
                                    </div>


                                </div>

                                <div class="col-md-4 col-sm-4">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label><b>concrete
                                        </b></label>

                                    &nbsp;&nbsp;&nbsp;&nbsp;
                                    <div class="custom-control custom-checkbox col-sm-5">

                                        <input type="checkbox" <?php if ($row['concrete_difference1'] == 'Y') {
                                                                    echo 'checked="checked"';
                                                                } ?>class="custom-control-input" id="concrete_difference1" value="Y" name="concrete_difference1">
                                        <label class="custom-control-label" for="concrete_difference1">พระอาทิตย์กับพระจันทร์</label>

                                    </div>

                                    <div class="custom-control custom-checkbox col-sm-5">
                                        <input type="checkbox" <?php if ($row['concrete_difference2'] == 'Y') {
                                                                    echo 'checked="checked"';
                                                                } ?>class="custom-control-input" id="concrete_difference2" value="Y" name="concrete_difference2">
                                        <label class="custom-control-label" for="concrete_difference2">สูงไม่เท่ากัน</label>
                                    </div>
                                    <div class="custom-control custom-checkbox col-sm-5">
                                        <input type="checkbox" <?php if ($row['concrete_difference3'] == 'Y') {
                                                                    echo 'checked="checked"';
                                                                } ?>class="custom-control-input" id="concrete_difference3" value="Y" name="concrete_difference3">
                                        <label class="custom-control-label" for="concrete_difference3">ต้นเล็กกับต้นใหญ่</label>
                                    </div>

                                </div>

                                <div class="col-md-4 col-sm-4">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label><b>Abstract
                                        </b></label>

                                    &nbsp;&nbsp;&nbsp;&nbsp;
                                    <div class="custom-control custom-checkbox col-sm-5">

                                        <input type="checkbox" <?php if ($row['abstract_difference1'] == 'Y') {
                                                                    echo 'checked="checked"';
                                                                } ?>class="custom-control-input" id="abstract_difference1" value="Y" name="abstract_difference1">
                                        <label class="custom-control-label" for="abstract_difference1">สว่างกับมืด</label>

                                    </div>

                                    <div class="custom-control custom-checkbox col-sm-5">
                                        <input type="checkbox" <?php if ($row['abstract_difference2'] == 'Y') {
                                                                    echo 'checked="checked"';
                                                                } ?>class="custom-control-input" id="abstract_difference2" value="Y" name="abstract_difference2">
                                        <label class="custom-control-label" for="abstract_difference2">เด็กกับผู้ใหญ่</label>
                                    </div>
                                    <div class="custom-control custom-checkbox col-sm-5">
                                        <input type="checkbox" <?php if ($row['abstract_difference3'] == 'Y') {
                                                                    echo 'checked="checked"';
                                                                } ?>class="custom-control-input" id="abstract_difference3" value="Y" name="abstract_difference3">
                                        <label class="custom-control-label" for="abstract_difference3">ไม้ล้มลุกกับไม้ยืนต้น</label>
                                    </div>

                                </div>


                            </div>

                            <br>

                            <div class="row">

                                <div class="col-md-4 col-sm-4">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label><b>2. ถามถึงความเหมือน
                                        </b></label>
                                    <div class="custom-control custom-checkbox col-sm-5">
                                        <label class="custom-control">ส้มกับกล้วย</label>
                                    </div>
                                    <div class="custom-control custom-checkbox col-sm-5">
                                        <label class="custom-control">หนูกับแมว</label>
                                    </div>
                                    <div class="custom-control custom-checkbox col-sm-5">
                                        <label class="custom-control">รถกับเรือ</label>
                                    </div>


                                </div>

                                <div class="col-md-4 col-sm-4">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label><b>concrete
                                        </b></label>

                                    &nbsp;&nbsp;&nbsp;&nbsp;
                                    <div class="custom-control custom-checkbox col-sm-5">

                                        <input type="checkbox" <?php if ($row['concrete_similarities1'] == 'Y') {
                                                                    echo 'checked="checked"';
                                                                } ?>class="custom-control-input" id="concrete_similarities1" value="Y" name="concrete_similarities1">
                                        <label class="custom-control-label" for="concrete_similarities1">เปลือกสีเหมือนกัน</label>

                                    </div>

                                    <div class="custom-control custom-checkbox col-sm-5">
                                        <input type="checkbox" <?php if ($row['concrete_similarities2'] == 'Y') {
                                                                    echo 'checked="checked"';
                                                                } ?>class="custom-control-input" id="concrete_similarities2" value="Y" name="concrete_similarities2">
                                        <label class="custom-control-label" for="concrete_similarities2">มีหนวด มีหาง เหมือนกัน</label>
                                    </div>
                                    <div class="custom-control custom-checkbox col-sm-5">
                                        <input type="checkbox" <?php if ($row['concrete_similarities3'] == 'Y') {
                                                                    echo 'checked="checked"';
                                                                } ?>class="custom-control-input" id="concrete_similarities3" value="Y" name="concrete_similarities3">
                                        <label class="custom-control-label" for="concrete_similarities3">วิ่งเหมือนกันใช้น้ำมัน</label>
                                    </div>

                                </div>

                                <div class="col-md-4 col-sm-4">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label><b>Abstract
                                        </b></label>

                                    &nbsp;&nbsp;&nbsp;&nbsp;
                                    <div class="custom-control custom-checkbox col-sm-5">

                                        <input type="checkbox" <?php if ($row['abstract_similarities1'] == 'Y') {
                                                                    echo 'checked="checked"';
                                                                } ?>class="custom-control-input" id="abstract_similarities1" value="Y" name="abstract_similarities1">
                                        <label class="custom-control-label" for="abstract_similarities1">เป็นผลไม้เหมือนกัน</label>

                                    </div>

                                    <div class="custom-control custom-checkbox col-sm-5">
                                        <input type="checkbox" <?php if ($row['abstract_similarities2'] == 'Y') {
                                                                    echo 'checked="checked"';
                                                                } ?>class="custom-control-input" id="abstract_similarities2" value="Y" name="abstract_similarities2">
                                        <label class="custom-control-label" for="abstract_similarities2">เป็นสัตว์เหมือนกัน</label>
                                    </div>
                                    <div class="custom-control custom-checkbox col-sm-5">
                                        <input type="checkbox" <?php if ($row['abstract_similarities3'] == 'Y') {
                                                                    echo 'checked="checked"';
                                                                } ?>class="custom-control-input" id="abstract_similarities3" value="Y" name="abstract_similarities3">
                                        <label class="custom-control-label" for="abstract_similarities3">เป็นพาหนะเหมือนกัน</label>
                                    </div>

                                </div>


                            </div>

                            <br>

                            <div class="row">

                                <div class="col-md-4 col-sm-4">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label><b>3. ถามถึงคำพังเพย
                                        </b></label>
                                    <div class="custom-control custom-checkbox col-sm-5">
                                        <label class="custom-control">น้ำขึ้นให้รีบตัก</label>
                                    </div>
                                    <div class="custom-control custom-checkbox col-sm-5">
                                        <label class="custom-control">หนีเสือปะจระเข้</label>
                                    </div>
                                    <div class="custom-control custom-checkbox col-sm-5">
                                        <label class="custom-control">ขี่ช้างจับตั๊กแตน</label>
                                    </div>


                                </div>

                                <div class="col-md-4 col-sm-4">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label><b>concrete
                                        </b></label>

                                    &nbsp;&nbsp;&nbsp;&nbsp;
                                    <div class="custom-control custom-checkbox col-sm-5">

                                        <input type="checkbox" <?php if ($row['concrete_aphorisms1'] == 'Y') {
                                                                    echo 'checked="checked"';
                                                                } ?>class="custom-control-input" id="concrete_aphorisms1" value="Y" name="concrete_aphorisms1">
                                        <label class="custom-control-label" for="concrete_aphorisms1">น้ำลงจะตักลำบาก</label>

                                    </div>

                                    <div class="custom-control custom-checkbox col-sm-5">
                                        <input type="checkbox" <?php if ($row['concrete_aphorisms2'] == 'Y') {
                                                                    echo 'checked="checked"';
                                                                } ?>class="custom-control-input" id="concrete_aphorisms2" value="Y" name="concrete_aphorisms2">
                                        <label class="custom-control-label" for="concrete_aphorisms2">หนีเสือแล้วยังจะเจอสัตว์ร้ายอีก</label>
                                    </div>
                                    <div class="custom-control custom-checkbox col-sm-5">
                                        <input type="checkbox" <?php if ($row['concrete_aphorisms3'] == 'Y') {
                                                                    echo 'checked="checked"';
                                                                } ?>class="custom-control-input" id="concrete_aphorisms3" value="Y" name="concrete_aphorisms3">
                                        <label class="custom-control-label" for="concrete_aphorisms3">ขี่ช้างสูงไปจับตั๊กแตนไม่ได้</label>
                                    </div>

                                </div>

                                <div class="col-md-4 col-sm-4">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label><b>Abstract
                                        </b></label>

                                    &nbsp;&nbsp;&nbsp;&nbsp;
                                    <div class="custom-control custom-checkbox col-sm-5">

                                        <input type="checkbox" <?php if ($row['abstract_aphorisms1'] == 'Y') {
                                                                    echo 'checked="checked"';
                                                                } ?>class="custom-control-input" id="abstract_aphorisms1" value="Y" name="abstract_aphorisms1">
                                        <label class="custom-control-label" for="abstract_aphorisms1">เมื่อมีโอกาสให้รีบฉวย</label>

                                    </div>

                                    <div class="custom-control custom-checkbox col-sm-7">
                                        <input type="checkbox" <?php if ($row['abstract_aphorisms2'] == 'Y') {
                                                                    echo 'checked="checked"';
                                                                } ?>class="custom-control-input" id="abstract_aphorisms2" value="Y" name="abstract_aphorisms2">
                                        <label class="custom-control-label" for="abstract_aphorisms2">หนีสิ่งเลวร้ายแล้วยังเจอสิ่งที่เลวร้ายกว่า</label>
                                    </div>
                                    <div class="custom-control custom-checkbox col-sm-5">
                                        <input type="checkbox" <?php if ($row['abstract_aphorisms3'] == 'Y') {
                                                                    echo 'checked="checked"';
                                                                } ?>class="custom-control-input" id="abstract_aphorisms3" value="Y" name="abstract_aphorisms3">
                                        <label class="custom-control-label" for="abstract_aphorisms3">ลงทุนเกินตัว</label>
                                    </div>

                                </div>


                            </div>

                            <br>

                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;<label><b>6.6 การตัดสินใจ (Judment)</b></label>
                            </div>
                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label><b>ถามทุกข้อดังนี้ พบซองจดหมายจ่าหน้าซองติดแสตมป์เรียบร้อยหล่นอยู่ข้างทาง</b></label>

                                &nbsp;&nbsp;&nbsp;&nbsp;
                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="radio" <?php if ($row['judment1'] == '1') {
                                                                echo 'checked="checked"';
                                                            } ?>class="custom-control-input" id="judment11" value="1" name="judment1">
                                    <label class="custom-control-label" for="judment11">เหมาะสม</label>
                                </div>

                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="radio" <?php if ($row['judment1'] == '2') {
                                                                echo 'checked="checked"';
                                                            } ?>class="custom-control-input" id="judment12" value="2" name="judment1">
                                    <label class="custom-control-label" for="judment12">ไม่เหมาะสม</label>
                                </div>

                            </div>

                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label><b>เป็นคนแรกที่เห็นไฟไหม้ขณะดูภาพยนต์อยู่ในโรงภาพยนต์</b></label>

                                &nbsp;&nbsp;&nbsp;&nbsp;
                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="radio" <?php if ($row['judment2'] == '1') {
                                                                echo 'checked="checked"';
                                                            } ?>class="custom-control-input" id="judment21" value="1" name="judment2">
                                    <label class="custom-control-label" for="judment21">เหมาะสม</label>
                                </div>

                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="radio" <?php if ($row['judment2'] == '2') {
                                                                echo 'checked="checked"';
                                                            } ?>class="custom-control-input" id="judment22" value="2" name="judment2">
                                    <label class="custom-control-label" for="judment22">ไม่เหมาะสม</label>
                                </div>

                            </div>

                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label><b>ออกจากบ้านใส่กุญแจแล้วนึกขึ้นได้ว่าลืมกุญแจทิ้งไว้ในบ้าน</b></label>

                                &nbsp;&nbsp;&nbsp;&nbsp;
                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="radio" <?php if ($row['judment3'] == '1') {
                                                                echo 'checked="checked"';
                                                            } ?>class="custom-control-input" id="judment31" value="1" name="judment3">
                                    <label class="custom-control-label" for="judment31">เหมาะสม</label>
                                </div>

                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="radio" <?php if ($row['judment3'] == '2') {
                                                                echo 'checked="checked"';
                                                            } ?>class="custom-control-input" id="judment32" value="2" name="judment3">
                                    <label class="custom-control-label" for="judment32">ไม่เหมาะสม</label>
                                </div>

                            </div>


                            <br>


                            <div class="form-group row alert alert-dark text-left">
                                <B>7.ความตระหนักต่อการเจ็บป่วย(Insight)</B>
                            </div>


                            <div class="row">


                                &nbsp;&nbsp;&nbsp;&nbsp;<label></label>
                                <div class="custom-control custom-radio col-sm-2">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['insight'] == '1') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="insight1" name="insight" value="1" onchange="custom_check('off_insight');">
                                    <label class="custom-control-label" for="insight1">ปฏิเสธการเจ็บป่วย</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-3">
                                    <input type="radio" <?php if ($row['insight'] == '2') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="insight2" name="insight" value="2" onchange="custom_check('off_insight');">
                                    <label class="custom-control-label" for="insight2">พอจะทาบว่าตนเองผิดปกติ ปฏิเสธการรักษา</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-3">
                                    <input type="radio" <?php if ($row['insight'] == '3') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="insight3" name="insight" value="3" onchange="custom_check('off_insight');">
                                    <label class="custom-control-label" for="insight3">ทราบว่าตนเองผิดปกติ แต่โทษว่าเกิดจากสิ่งอื่น</label>
                                </div>





                            </div>

                            <div class="row">


                                &nbsp;&nbsp;&nbsp;&nbsp;<label></label>
                                <div class="custom-control custom-radio col-sm-4">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['insight'] == '4') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="insight4" name="insight" value="4" onchange="custom_check('off_insight');">
                                    <label class="custom-control-label" for="insight4">ทราบว่าตนเองผิดปกติจากปัญหาบางประการในตนเองแต่ไม่ทราบว่าปัญหาอะไร</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-3">
                                    <input type="radio" <?php if ($row['insight'] == '5') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="insight5" name="insight" value="5" onchange="custom_check('off_insight');">
                                    <label class="custom-control-label" for="insight5">ยอมรับว่าตนเองผิดปกติ แต่ไม่ได้แก้ปัญหา</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-3">
                                    <input type="radio" <?php if ($row['insight'] == '6') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="insight6" name="insight" value="6" onchange="custom_check('off_insight');">
                                    <label class="custom-control-label" for="insight6">ยอมรับการเจ็บป่วยและยอมรับการรักษา</label>
                                </div>


             <!--                   <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if (
                                                                                    $row['insight'] != '1'
                                                                                    && $row['insight'] != '2'
                                                                                    && $row['insight'] != '3'
                                                                                    && $row['insight'] != '4'
                                                                                    && $row['insight'] != '5'
                                                                                    && $row['insight'] != NULL
                                                                                ) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="insight6" name="insight" onchange="custom_check('on_insight');">
                                    <label class="custom-control-label" for="insight6">มี ระบุ</label>
                                </div>
                                                                            -->
                       <!--         <div class="col-sm-3">
                                    <input type="text" class="form-control form-control-sm" id="insight_text" name="insight" value="<?php if (
                                                                                                                                        $row['insight'] != '1'
                                                                                                                                        && $row['insight'] != '2'
                                                                                                                                        && $row['insight'] != '3'
                                                                                                                                        && $row['insight'] != '4'
                                                                                                                                        && $row['insight'] != '5'
                                                                                                                                        && $row['insight'] != NULL
                                                                                                                                    ) {
                                                                                                                                        echo htmlspecialchars($row['insight']);
                                                                                                                                    } ?>" <?php if (!($row['insight'] != '1'
                                            && $row['insight'] != '2'
                                            && $row['insight'] != '3'
                                            && $row['insight'] != '4'
                                            && $row['insight'] != '5'
                                            && $row['insight'] != NULL)) {
                                            echo 'disabled';
                                        } ?>>
                                </div>
                                    -->


                            </div>
                            <br>



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
                                        Session::checkPermission('IPD_NURSE_NOTE','ADD')
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
                        <script>
                            //ควบคุมปุ่ม
                            function custom_check(value) {

                                if (value == "off_appearance") {
                                    $('#appearance5_text').attr("disabled", true).val('');
                                    $('#appearance6_text').attr("disabled", true).val('');
                                    $('#appearance').prop("checked", false);
                                    $('#appearance_check1').prop("checked", false);
                                    $('#appearance_check2').prop("checked", false);

                                } else if (value == "off_appearance_check1") {
                                    $('#appearance5_text').attr("disabled", false).val('');
                                    $('#appearance6_text').attr("disabled", true).val('');
                                   // $('#appearance_check1').prop("checked", false);
                                    $('#appearance1').prop("checked", false);
                                    $('#appearance2').prop("checked", false);
                                    $('#appearance3').prop("checked", false);
                                    $('#appearance4').prop("checked", false);
                                    $('#appearance6').prop("checked", false);

                                } else if (value == "on_appearance_check") {

                                    $('#appearance5_text').attr("disabled", true).val('');
                                    $('#appearance6_text').attr("disabled", false).val('');
                                   // $('#appearance_check2').prop("checked", false);
                                    $('#appearance1').prop("checked", false);
                                    $('#appearance2').prop("checked", false);
                                    $('#appearance3').prop("checked", false);
                                    $('#appearance4').prop("checked", false);
                                    $('#appearance5').prop("checked", false);

                                }


                             /*   if (value == "off_appearance_check1") {
                                $('#appearance5_text').attr("disabled", false).val('');
                                $('#appearance6_text').attr("disabled", true).val('');
                                $('#appearance1').prop("checked", false);
                                $('#appearance2').prop("checked", false);
                                $('#appearance3').prop("checked", false);
                                $('#appearance4').prop("checked", false);
                                $('#appearance6').prop("checked", false);
        
                            } else if (value == "on_appearance_check") {
                                $('#appearance5_text').attr("disabled", true).val('');
                                    $('#appearance6_text').attr("disabled", false).val('');
                                    $('#appearance1').prop("checked", false);
                                    $('#appearance2').prop("checked", false);
                                    $('#appearance3').prop("checked", false);
                                    $('#appearance4').prop("checked", false);
                                    $('#appearance5').prop("checked", false);
                            } */



                                if (value == "off_skin") {
                                    $('#skin_text').attr("disabled", true).val('');
                                    $('#skin6').prop("checked", false);

                                } else if (value == "on_skin") {
                                    $('#skin_text').attr("disabled", false).val('');
                                    $('#skin1').prop("checked", false);
                                    $('#skin2').prop("checked", false);
                                    $('#skin3').prop("checked", false);
                                    $('#skin4').prop("checked", false);
                                    $('#skin5').prop("checked", false);

                                }

                                    if (value == "off_body_movement_behavior") {
                                    $('#body_movement_behavior_text').attr("disabled", true).val('');
                                    $('#body_movement_behavior4').prop("checked", false);
                                    } else if (value == "on_body_movement_behavior") {
                                    $('#body_movement_behavior_text').attr("disabled", false).val('');
                                    $('#body_movement_behavior1').prop("checked", false);
                                    $('#body_movement_behavior2').prop("checked", false);
                                    $('#body_movement_behavior3').prop("checked", false);
                                    $('#body_movement_behavior5').prop("checked", false);
                                    $('#body_movement_behavior6').prop("checked", false);
                                    $('#body_movement_behavior7').prop("checked", false);
                                    $('#body_movement_behavior8').prop("checked", false);
                                
                                }

                                if (value == "off_speech_disorder") {
                                    $('#speech_disorder_text').attr("disabled", true).val('');
                                    $('#speech_disorder2').prop("checked", false);
                                    } else if (value == "on_speech_disorder") {
                                    $('#speech_disorder_text').attr("disabled", false).val('');
                                    $('#speech_disorder1').prop("checked", false);
                                    $('#speech_disorder3').prop("checked", false);
                                    $('#speech_disorder4').prop("checked", false);
                          
                                }

                                if (value == "off_mood") {
                                    $('#mood_text').attr("disabled", true).val('');
                                    $('#mood5').prop("checked", false);
                                    } else if (value == "on_mood") {
                                    $('#mood_text').attr("disabled", false).val('');
                                    $('#mood1').prop("checked", false);
                                    $('#mood2').prop("checked", false);
                                    $('#mood3').prop("checked", false);
                                    $('#mood4').prop("checked", false);

                                }

                                if (value == "off_illution") {
                                    $('#illution_text').attr("disabled", true).val('');
                                    $('#illution2').prop("checked", false);
                                    } else if (value == "on_illution") {
                                    $('#illution_text').attr("disabled", false).val('');
                                    $('#illution1').prop("checked", false);
                                }

                                if (value == "off_hallucination") {
                                    $('#hallucination_text').attr("disabled", true).val('');
                                    $('#hallucination2').prop("checked", false);
                                    } else if (value == "on_hallucination") {
                                    $('#hallucination_text').attr("disabled", false).val('');
                                    $('#hallucination1').prop("checked", false);
                                }

                                if (value == "on_vision") {
                                    $('#vision_text').attr("disabled", false).val('');
                                    } 

                                    if (value == "on_hearing") {
                                    $('#hearing_text').attr("disabled", false).val('');
                                    } 

                                    if (value == "on_tast_perception") {
                                    $('#tast_perception_text').attr("disabled", false).val('');
                                    } 

                                    if (value == "on_touch") {
                                    $('#touch_text').attr("disabled", false).val('');
                                    } 

                                    if (value == "on_smell") {
                                    $('#smell_text').attr("disabled", false).val('');
                                    } 

                                    if (value == "off_insight") {
                                    $('#insight_text').attr("disabled", true).val('');
                                    $('#insight7').prop("checked", false);
                                    } else if (value == "on_insight") {
                                    $('#insight_text').attr("disabled", false).val('');
                                    $('#insight1').prop("checked", false);
                                    $('#insight2').prop("checked", false);
                                    $('#insight3').prop("checked", false);
                                    $('#insight4').prop("checked", false);
                                    $('#insight5').prop("checked", false);
                                    $('#insight6').prop("checked", false);
                          
                                }


                            }

                                //on_insight
                                    

                            

                      /*      $(document).ready(function() {
                         var appearance6 = $('input[name="appearance_check"]:checked').val();

            // Set the value of the hidden input field
            $('#appearance_check_value').val(appearance6);
            console.log(appearance6) 

       
        }); */


                            function form_save() {
                                
                            var appearance = $('input[name="appearance"]:checked').val();
                            var dress = $('input[name="dress"]:checked').val();
                            var cc = $.trim($('[name="cc"]').val());
                            var current_illness = $.trim($('[name="current_illness"]').val());
                            var c_chronic = $('input[name="c_chronic"]:checked').val();
                            var hos_history = $('input[name="hos_history"]:checked').val();
                            var h_sergery = $('input[name="h_sergery"]:checked').val();
                            var h_allergy = $('input[name="h_allergy"]:checked').val();
                            var child_devilopment = $('input[name="child_devilopment"]:checked').val();
                            var history_of_drug = $('input[name="history_of_drug"]:checked').val();

                                 if (appearance == undefined) {
                                $('[name="appearance"]').focus();
                                //alert(depart)
                                alert('กรุณาเลือกรูปร่างลักษณะ');
                                } else if (dress == undefined) {

                                $('[name="dress"]').focus();
                                alert('กรุณาเลือกการแต่งกาย');
                                } /*else if (current_illness == "") {

                                $('[name="current_illness"]').focus();
                                alert('บันทึกประวัติเจ็บป่วยปัจจุบัน');
                                // console.log(h_sergery);
                                } */

                              //  var appearance5 = $('input[name="appearance_check"]:checked').val();
                               // $('#appearance_check_value').val(appearance5);
                              //  var appearance6 = $('input[name="appearance_check"]:checked').val();
                               // $('#appearance_check_value').val(appearance6);
                               // console.log(appearance6)
                                
                               // var appearance6 = $('input[name="appearance_check"]:checked').val();

                               // console.log(appearance6)
                                /*                       var rxdate = $.trim($('[name="rxdate"]').val());
                            var rxtime = $.trim($('[name="rxtime"]').val());
                            if (rxdate == "") {

                                $('[name="rxdate"]').focus();
                                alert('เลือกวันที่');
                            } else if (rxtime == "") {

                                $('[name="rxtime"]').focus();
                                alert('เลือกเวลา');
                            }
*/

                                var url_update = "form-mental-health1-update.php";
                                var url_save = "form-mental-health1-save.php";
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

                        