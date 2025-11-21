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


$permissionCheck = Session::checkPermissionAndShowMessage('PRS_CHILD_FELDOWN', 'VIEW');
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
        'form' => 'CHILD-FELLDOWN-FORM',
        'an' => $an,
), JSON_UNESCAPED_UNICODE));


//echo $ids;

//----------------------เช็คว่า an นี้ มีข้อมูลหรือไม่




$sql = "SELECT *
                FROM `prs_child_felldown`
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

$_id = '33'; //Link menu

$sql = "SELECT *
                FROM `prs_link_menu`
                WHERE id = :id
                LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute(['id' => $_id]);
if ($row0  = $stmt->fetch()) {
        $menu_name = $row0['menu_name'];
        $production = $row0['production'];
} else {
        $menu_name = '-';
}

$check_    = ReportQueryUtils::getProduction($_id)


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

        table,
        th,
        td {
                border: 1px solid black;
                border-collapse: collapse;
        }

        table.center {
                margin-right: 150px;
                margin-left: 80px;
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



                        <?php
                        $checked = 'checked="checked"';

                        $variation1 = isset($row['variation1']) ? (int)$row['variation1'] : 0;
                        $variation2 = isset($row['variation2']) ? (int)$row['variation2'] : 0;
                        $variation3 = isset($row['variation3']) ? (int)$row['variation3'] : 0;
                        $variation4 = isset($row['variation4']) ? (int)$row['variation4'] : 0;



                        $font_color = 'white';
                        // Set the background color based on the value of total_sum
                        if ($variation1 == 0) {

                                $bg_color1 = 'green';
                                //$message = 'แนะนำประเมินต่อทุก 1 สัปดาห์';

                        } elseif ($variation1 >= 1 && $variation1 <= 2) {

                                $bg_color1 = 'yellow';
                                $font_color = 'black';
                                //$message = 'แนะนำประเมินต่อทุก 2 วัน';
                        } elseif ($variation1 >= 3) {
                                $bg_color1 = 'red';

                                //$message = 'แนะนำประเมินวันละ 1 ครั้ง';
                        } else {
                                $bg_color1 = ''; // default if the value is outside the range
                        }


                        if ($variation2 == 0) {

                                $bg_color2 = 'green';
                                //$message = 'แนะนำประเมินต่อทุก 1 สัปดาห์';

                        } elseif ($variation2 >= 1 && $variation2 <= 2) {

                                $bg_color2 = 'yellow';
                                $font_color = 'black';
                                //$message = 'แนะนำประเมินต่อทุก 2 วัน';
                        } elseif ($variation2 >= 3) {
                                $bg_color2 = 'red';

                                //$message = 'แนะนำประเมินวันละ 1 ครั้ง';
                        } else {
                                $bg_color2 = ''; // default if the value is outside the range
                        }

                        if ($variation3 == 0) {

                                $bg_color3 = 'green';
                                //$message = 'แนะนำประเมินต่อทุก 1 สัปดาห์';

                        } elseif ($variation3 >= 1 && $variation3 <= 2) {

                                $bg_color3 = 'yellow';
                                $font_color = 'black';
                                //$message = 'แนะนำประเมินต่อทุก 2 วัน';
                        } elseif ($variation3 >= 3) {
                                $bg_color3 = 'red';

                                //$message = 'แนะนำประเมินวันละ 1 ครั้ง';
                        } else {
                                $bg_color3 = ''; // default if the value is outside the range
                        }

                        if ($variation4 == 0) {

                                $bg_color4 = 'green';
                                //$message = 'แนะนำประเมินต่อทุก 1 สัปดาห์';

                        } elseif ($variation4 >= 1 && $variation4 <= 2) {

                                $bg_color4 = 'yellow';
                                $font_color = 'black';
                                //$message = 'แนะนำประเมินต่อทุก 2 วัน';
                        } elseif ($variation4 >= 3) {
                                $bg_color4 = 'red';

                                //$message = 'แนะนำประเมินวันละ 1 ครั้ง';
                        } else {
                                $bg_color4 = ''; // default if the value is outside the range
                        }



                        //echo $variation1;

                        ?>

                        <div class="card-group pb-3 ">
                                <div class="card">
                                        <div class="card-body" style=" overflow-y: auto;">


                                                <?php
                                                // Get the current hour in 24-hour format
                                                $current_hour = date('H:i');

                                                // Determine which radio should be checked based on the time range
                                                $work_shift1_checked = '';
                                                $work_shift2_checked = '';
                                                $work_shift3_checked = '';



                                                if ($current_hour >= '00:00' && $current_hour <= '07:59') {

                                                        $work_shift1_checked = 'checked="checked"'; // Morning shift (08:00 - 16:00)

                                                } elseif ($current_hour > '08:00' && $current_hour <= '15:59') {
                                                        $work_shift2_checked = 'checked="checked"'; // Evening shift (16:01 - 23:59)
                                                } elseif ($current_hour >= '16:00' && $current_hour <= '23:59') {
                                                        $work_shift3_checked = 'checked="checked"'; // Night shift (00:00 - 07:00)
                                                }
                                                ?>

                                                <!--<div class="custom-control custom-radio col-sm-1">
    <input type="radio"  <?= (isset($work_shift1_checked)  ? htmlspecialchars($work_shift1_checked) : htmlspecialchars($row['work_shift'])) ?>  class="custom-control-input" id="work_shift1" name="work_shift" value="1">
    <label class="custom-control-label" for="work_shift1" style="font-size:100%; background-color:yellow;">
        <strong>&nbsp;ดึก&nbsp;</strong>
    </label>
</div>

<div class="custom-control custom-radio col-sm-1">
    <input type="radio" <?= (isset($work_shift2_checked)  ? htmlspecialchars($work_shift2_checked) : htmlspecialchars($row['work_shift'])) ?> class="custom-control-input" id="work_shift2" name="work_shift" value="2">
    <label class="custom-control-label" for="work_shift2" style="font-size:100%; background-color:yellow;">
        <strong>&nbsp;เช้า&nbsp;</strong>
    </label>
</div>

<div class="custom-control custom-radio col-sm-1">
    <input type="radio" <?= (isset($work_shift3_checked)  ? htmlspecialchars($work_shift3_checked) : htmlspecialchars($row['work_shift'])) ?> class="custom-control-input" id="work_shift3" name="work_shift" value="3">
    <label class="custom-control-label" for="work_shift3" style="font-size:100%; background-color:yellow;">
        <strong>&nbsp;บ่าย&nbsp;</strong>
    </label>
</div>-->


                                                <div class="row">


                                                        &nbsp;&nbsp;&nbsp;&nbsp;<label>เวร: </label>
                                                        <div class="custom-control custom-radio col-sm-1">
                                                                &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['work_shift'] == '1') {
                                                                                                                        echo 'checked="checked"';
                                                                                                                } else if ($id == null) {
                                                                                                                        echo $work_shift1_checked;
                                                                                                                } ?> class="custom-control-input" id="work_shift1" name="work_shift" value="1">
                                                                <label class="custom-control-label" for="work_shift1" style="font-size:100%; background-color:yellow;"><strong>&nbsp;ดึก&nbsp;</strong></label>
                                                        </div>
                                                        <div class="custom-control custom-radio col-sm-1">
                                                                <input type="radio" <?php if ($row['work_shift'] == '2') {
                                                                                                echo 'checked="checked"';
                                                                                        } else if ($id == null) {
                                                                                                echo $work_shift2_checked;
                                                                                        } ?> class="custom-control-input" id="work_shift2" name="work_shift" value="2">
                                                                <label class="custom-control-label" for="work_shift2" style="font-size:100%; background-color:orange;"><strong>&nbsp;เช้า&nbsp;</strong></label>
                                                        </div>
                                                        <div class="custom-control custom-radio col-sm-1">
                                                                <input type="radio" <?php if ($row['work_shift'] == '3') {
                                                                                                echo 'checked="checked"';
                                                                                        } else if ($id == null) {
                                                                                                echo $work_shift3_checked;
                                                                                        } ?> class="custom-control-input" id="work_shift3" name="work_shift" value="3">
                                                                <label class="custom-control-label" for="work_shift3" style="font-size:100%; background-color:gray;"><strong>&nbsp;บ่าย&nbsp;</strong></label>
                                                        </div>




                                                </div>




                                                <div class="row">

                                                        <div class="col-12 col-md-12">


                                                                <table lass="center" id="bg-table" width="100%" style="border-collapse: collapse;font-size:10pt;margin-top:8px;">
                                                                        <tr style="border:1px solid #000;margin: 45px;">

                                                                                <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" colspan="2">&nbsp;<b>ปัจจัยเสี่ยง</b></td>
                                                                                <td style="text-align:center; border-right:0.5px solid #000;padding:4px;">&nbsp;<b>คะแนน</b></td>
                                                                        </tr>
                                                                        <?php
                                                // Get the current hour in 24-hour format
                                                $age_y = $row_ipt['age_y'];

                                                //echo $age_y;

                                                $age_y03_checked = '';
                                                $age_y37_checked = '';
                                                $age_y713_checked = '';
                                                $age_y13_checked = '';



                                                if ($age_y >= '0' && $age_y <= '2') {
                                                        $age_y03_checked= 'checked="checked"'; 
                                                } elseif ($age_y >= '3' && $age_y <= '6') {
                                                        $age_y37_checked= 'checked="checked"'; 
                                                } elseif ($age_y >= '7' && $age_y <= '13') {
                                                        $age_y713_checked= 'checked="checked"'; 
                                                }elseif ($age_y > '13') {
                                                        $age_y13_checked= 'checked="checked"'; 
                                                }
                                                ?>
                                                                        <tr style="border:1px solid #000;margin: 45px;">
                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" rowspan="5">&nbsp;<b>1. อายุ</b></td>
                                                                        </tr>

                                                                        <tr style="border:1px solid #000;margin: 45px;">

                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;">&nbsp;ต่ำกว่า 3 ปี</td>
                                                                                <td style="text-align:center; border-right:0.5px solid #000;padding:4px;">
                                                                                        <div class="custom-control custom-radio col-sm-1">
                                                                                                <input type="radio" <?php if ($row['age_check'] == '4') {
                                                                                                echo 'checked="checked"';
                                                                                        } else if ($id == null || $row['age_check'] == null) {
                                                                                                echo $age_y03_checked;
                                                                                        } ?>class="custom-control-input" id="age_y4" value="4" name="age_check" oninput="ageCheckValue()">
                                                                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="age_y4" style="font-size:100%;">4</label>
                                                                                        </div>
                                                                                </td>
                                                                        </tr>

                                                                        <tr style="border:1px solid #000;margin: 45px;">

                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;">&nbsp;อายุ 3 - 7 ปี</td>



                                                                                <td style="text-align:center; border-right:0.5px solid #000;padding:4px;">
                                                                                        <div class="custom-control custom-radio col-sm-1">
                                                                                                <input type="radio" <?php if ($row['age_check'] == '3') {
                                                                                                echo 'checked="checked"';
                                                                                        } else if ($id == null || $row['age_check'] == null) {
                                                                                                echo $age_y37_checked;
                                                                                        } ?>class="custom-control-input" id="age_check3" value="3" name="age_check" oninput="ageCheckValue()">
                                                                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="age_check3" style="font-size:100%;">3</label>
                                                                                        </div>

                                                                                </td>

                                                                        </tr>

                                                                        <tr style="border:1px solid #000;margin: 45px;">

                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;">&nbsp;อายุ 7 - 13 ปี</td>
                                                                                <td style="text-align:center; border-right:0.5px solid #000;padding:4px;">
                                                                                        <div class="custom-control custom-radio col-sm-1">
                                                                                                <input type="radio" <?php if ($row['age_check'] == '2') {
                                                                                                echo 'checked="checked"';
                                                                                        } else if ($id == null || $row['age_check'] == null) {
                                                                                                echo $age_y713_checked;
                                                                                        } ?>class="custom-control-input" id="age_check2" value="2" name="age_check" oninput="ageCheckValue()">
                                                                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="age_check2" style="font-size:100%;">2</label>
                                                                                        </div>

                                                                                </td>

                                                                        </tr>

                                                                        <tr style="border:1px solid #000;margin: 45px;">

                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;">&nbsp;มากกว่า 13 ปี</td>



                                                                                <td style="text-align:center; border-right:0.5px solid #000;padding:4px;">
                                                                                        <div class="custom-control custom-radio col-sm-1">
                                                                                                <input type="radio" <?php if ($row['age_check'] == '1') {
                                                                                                echo 'checked="checked"';
                                                                                        } else if ($id == null || $row['age_check'] == null) {
                                                                                                echo $age_y713_checked;
                                                                                        } ?>class="custom-control-input" id="age_check1" value="1" name="age_check" oninput="ageCheckValue()">
                                                                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="age_check1" style="font-size:100%;">1</label>
                                                                                        </div>

                                                                                </td>

                                                                        </tr>

                                                                        <?php
                                                // Get the current hour in 24-hour format
                                                $sex = $row_ipt['sex'];

                                                //echo $sex;

                                                // Determine which radio should be checked based on the time range
                                                $sex1_checked = '';
                                                $sex2_checked = '';



                                                if ($sex == '2' ) {

                                                        $sex1_checked = 'checked="checked"'; // Morning shift (08:00 - 16:00)

                                                } elseif ($sex == '1' ) {
                                                        $sex2_checked = 'checked="checked"'; // Evening shift (16:01 - 23:59)
                                                } 
                                                ?>

                                                                        <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">
                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" rowspan="3">&nbsp;<b>2.เพศ</b></td>
                                                                        </tr>

                                                                        <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">

                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;">&nbsp;เพศชาย</td>



                                                                                <td style="text-align:center; border-right:0.5px solid #000;padding:4px;">
                                                                                        <div class="custom-control custom-radio col-sm-1">
                                                                                                <input type="radio" <?php if ($row['sex'] == '2') {
                                                                                                echo 'checked="checked"';
                                                                                        } else if ($id == null || $row['sex']== null) {
                                                                                                echo $sex2_checked;
                                                                                        } ?>class="custom-control-input" id="sex2" value="2" name="sex" oninput="SexCheckValue()">
                                                                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="sex2" style="font-size:100%;">2</label>
                                                                                        </div>

                                                                                </td>

                                                                        </tr>


                                                                        <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">

                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;">&nbsp;เพศหญิง</td>



                                                                                <td style="text-align:center; border-right:0.5px solid #000;padding:4px;">
                                                                                        <div class="custom-control custom-radio col-sm-1">
                                                                                                <input type="radio" <?php if ($row['sex'] == '1') {
                                                                                                echo 'checked="checked"';
                                                                                        } else if ($id == null || $row['sex']== null) {
                                                                                                echo $sex1_checked;
                                                                                        } ?>class="custom-control-input" id="sex1" value="1" name="sex" oninput="SexCheckValue()">
                                                                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="sex1" style="font-size:100%;">1</label>
                                                                                        </div>

                                                                                </td>

                                                                        </tr>




                                                                        <tr style="border:1px solid #000;margin: 45px;">
                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" rowspan="5">&nbsp;<b>3.การวินิจฉัยโรค</b></td>
                                                                        </tr>

                                                                        <tr style="border:1px solid #000;margin: 45px;">

                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;">&nbsp;ผู้ป่วยที่มีโรค/อาการทางระบบประสาท หรือมีปัญหาด้าน
                                                                                        การมองเห็นการรับพัง และการเคลื่อนไหว
                                                                                </td>



                                                                                <td style="text-align:center; border-right:0.5px solid #000;padding:4px;">
                                                                                        <div class="custom-control custom-radio col-sm-1">
                                                                                                <input type="radio" <?php if ($row['diag'] == '4') {
                                                                                                                                echo 'checked="checked"';
                                                                                                                        } ?>class="custom-control-input" id="diag4" value="4" name="diag" oninput="diagCheckValue()">
                                                                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="diag4" style="font-size:100%;">4</label>
                                                                                        </div>

                                                                                </td>





                                                                        </tr>


                                                                        <tr style="border:1px solid #000;margin: 45px;">

                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;">&nbsp;ผู้ป่วยพร่องการได้รับออกซิเจน เช่น มีปัญหาทางระบบ
                                                                                        ทางเดินหายใจ การขาดน้ำ เกลือแร่ ซีด นอนไม่หลับ เป็นลม มึนงง</td>



                                                                                <td style="text-align:center; border-right:0.5px solid #000;padding:4px;">
                                                                                        <div class="custom-control custom-radio col-sm-1">
                                                                                                <input type="radio" <?php if ($row['diag'] == '3') {
                                                                                                                                echo 'checked="checked"';
                                                                                                                        } ?>class="custom-control-input" id="diag3" value="3" name="diag" oninput="diagCheckValue()">
                                                                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="diag3" style="font-size:100%;">3</label>
                                                                                        </div>

                                                                                </td>

                                                                        </tr>

                                                                        <tr style="border:1px solid #000;margin: 45px;">

                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;">&nbsp;ผู้ป่วยที่เจ็บป่วยทางจิตหรือมีความเปลี่ยนแปลงด้านพฤติกรรม</td>



                                                                                <td style="text-align:center; border-right:0.5px solid #000;padding:4px;">
                                                                                        <div class="custom-control custom-radio col-sm-1">
                                                                                                <input type="radio" <?php if ($row['diag'] == '2') {
                                                                                                                                echo 'checked="checked"';
                                                                                                                        } ?>class="custom-control-input" id="diag2" value="2" name="diag" oninput="diagCheckValue()">
                                                                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="diag2" style="font-size:100%;">2</label>
                                                                                        </div>

                                                                                </td>

                                                                        </tr>

                                                                        <tr style="border:1px solid #000;margin: 45px;">

                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;">&nbsp;โรคอื่น ๆ</td>



                                                                                <td style="text-align:center; border-right:0.5px solid #000;padding:4px;">
                                                                                        <div class="custom-control custom-radio col-sm-1">
                                                                                                <input type="radio" <?php if ($row['diag'] == '1') {
                                                                                                                                echo 'checked="checked"';
                                                                                                                        } ?>class="custom-control-input" id="diag1" value="1" name="diag" oninput="diagCheckValue()">
                                                                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="diag1" style="font-size:100%;">1</label>
                                                                                        </div>

                                                                                </td>

                                                                        </tr>


                                                                        <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">
                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" rowspan="4">&nbsp;<b>4. ความสามารถในการรับรู้</b></td>
                                                                        </tr>

                                                                        <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">

                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;">&nbsp;การรับรู้บกพร่องหรือประเมินความสามารถตนเองไม่เหมาะสม รวมถึงทารก</td>



                                                                                <td style="text-align:center; border-right:0.5px solid #000;padding:4px;">
                                                                                        <div class="custom-control custom-radio col-sm-1">
                                                                                                <input type="radio" <?php if ($row['knowledge'] == '3') {
                                                                                                                                echo 'checked="checked"';
                                                                                                                        } ?>class="custom-control-input" id="knowledge3" value="3" name="knowledge" oninput="knowledgeCheckValue()">
                                                                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="knowledge3" style="font-size:100%;">3</label>
                                                                                        </div>

                                                                                </td>

                                                                        </tr>


                                                                        <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">

                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;">&nbsp;รับรู้และไม่ปฏิบัติตามคำแนะนำ</td>



                                                                                <td style="text-align:center; border-right:0.5px solid #000;padding:4px;">
                                                                                        <div class="custom-control custom-radio col-sm-1">
                                                                                                <input type="radio" <?php if ($row['knowledge'] == '2') {
                                                                                                                                echo 'checked="checked"';
                                                                                                                        } ?>class="custom-control-input" id="knowledge2" value="2" name="knowledge" oninput="knowledgeCheckValue()">
                                                                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="knowledge2" style="font-size:100%;">2</label>
                                                                                        </div>

                                                                                </td>

                                                                        </tr>

                                                                        <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">

                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;">&nbsp;รับรู้และปฏิบัติตามคำแนะนำ</td>



                                                                                <td style="text-align:center; border-right:0.5px solid #000;padding:4px;">
                                                                                        <div class="custom-control custom-radio col-sm-1">
                                                                                                <input type="radio" <?php if ($row['knowledge'] == '1') {
                                                                                                                                echo 'checked="checked"';
                                                                                                                        } ?>class="custom-control-input" id="knowledge1" value="1" name="knowledge" oninput="knowledgeCheckValue()">
                                                                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="knowledge1" style="font-size:100%;">1</label>
                                                                                        </div>

                                                                                </td>

                                                                        </tr>

                                                                        <tr style="border:1px solid #000;margin: 45px;">
                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" rowspan="5">&nbsp;<b>5. ปัจจัยและสิ่งแวดล้อม</b></td>
                                                                        </tr>

                                                                        <tr style="border:1px solid #000;margin: 45px;">

                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;">&nbsp;ผู้ป่วยที่มีประวัติพลัดตกหกลัม หรือทารก-วัยหัดเดินที่ต้อง
                                                                                        นอนบนเตียง</td>
                                                                                <td style="text-align:center; border-right:0.5px solid #000;padding:4px;">
                                                                                        <div class="custom-control custom-radio col-sm-1">
                                                                                                <input type="radio" <?php if ($row['environmental'] == '4') {
                                                                                                                                echo 'checked="checked"';
                                                                                                                        } ?>class="custom-control-input" id="environmental4" value="4" name="environmental" oninput="environmentalcheckValue()">
                                                                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="environmental4" style="font-size:100%;">4</label>
                                                                                        </div>
                                                                                </td>
                                                                        </tr>

                                                                        <tr style="border:1px solid #000;margin: 45px;">

                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;">&nbsp;ผู้ป่วยที่ต้องใช้กายอุปกรณ์ช่วยเหลือหรือผู้ป่วยที่ต้องคาสาย
                                                                                        ท่อระบายต่าง ๆ</td>
                                                                                <td style="text-align:center; border-right:0.5px solid #000;padding:4px;">
                                                                                        <div class="custom-control custom-radio col-sm-1">
                                                                                                <input type="radio" <?php if ($row['environmental'] == '3') {
                                                                                                                                echo 'checked="checked"';
                                                                                                                        } ?>class="custom-control-input" id="environmental3" value="3" name="environmental" oninput="environmentalcheckValue()">
                                                                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="environmental3" style="font-size:100%;">3</label>
                                                                                        </div>
                                                                                </td>
                                                                        </tr>

                                                                        <tr style="border:1px solid #000;margin: 45px;">

                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;">&nbsp;ผู้ป่วยที่นอนกับเตียง</td>
                                                                                <td style="text-align:center; border-right:0.5px solid #000;padding:4px;">
                                                                                        <div class="custom-control custom-radio col-sm-1">
                                                                                                <input type="radio" <?php if ($row['environmental'] == '2') {
                                                                                                                                echo 'checked="checked"';
                                                                                                                        } ?>class="custom-control-input" id="environmental2" value="2" name="environmental" oninput="environmentalcheckValue()">
                                                                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="environmental2" style="font-size:100%;">2</label>
                                                                                        </div>
                                                                                </td>
                                                                        </tr>


                                                                        <tr style="border:1px solid #000;margin: 45px;">

                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;">&nbsp;ผู้ป่วยที่สามารถเดินไป - มาได้ด้วยตนเอง</td>
                                                                                <td style="text-align:center; border-right:0.5px solid #000;padding:4px;">
                                                                                        <div class="custom-control custom-radio col-sm-1">
                                                                                                <input type="radio" <?php if ($row['environmental'] == '1') {
                                                                                                                                echo 'checked="checked"';
                                                                                                                        } ?>class="custom-control-input" id="environmental1" value="1" name="environmental" oninput="environmentalcheckValue()">
                                                                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="environmental1" style="font-size:100%;">1</label>
                                                                                        </div>
                                                                                </td>
                                                                        </tr>



                                                                        <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">
                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" rowspan="4">&nbsp;<b>6. หลังผ่าตัด</b></td>
                                                                        </tr>

                                                                        <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">

                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;">&nbsp;ผู้ป่วยหลังผ่าตัดใน 24 ชั่วโมง</td>



                                                                                <td style="text-align:center; border-right:0.5px solid #000;padding:4px;">
                                                                                        <div class="custom-control custom-radio col-sm-1">
                                                                                                <input type="radio" <?php if ($row['after_surgery'] == '3') {
                                                                                                                                echo 'checked="checked"';
                                                                                                                        } ?>class="custom-control-input" id="after_surgery3" value="3" name="after_surgery" oninput="aftersurgerycheckValue()">
                                                                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="after_surgery3" style="font-size:100%;">3</label>
                                                                                        </div>
                                                                                </td>
                                                                        </tr>


                                                                        <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">

                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;">&nbsp;ภายใน 48 ชั่วโมง</td>



                                                                                <td style="text-align:center; border-right:0.5px solid #000;padding:4px;">
                                                                                        <div class="custom-control custom-radio col-sm-1">
                                                                                                <input type="radio" <?php if ($row['after_surgery'] == '2') {
                                                                                                                                echo 'checked="checked"';
                                                                                                                        } ?>class="custom-control-input" id="after_surgery2" value="2" name="after_surgery" oninput="aftersurgerycheckValue()">
                                                                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="after_surgery2" style="font-size:100%;">2</label>
                                                                                        </div>
                                                                                </td>
                                                                        </tr>

                                                                        <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">

                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;">&nbsp;มากกว่า 48 ชั่วโมงหรือไม่ได้รับการผ่าตัด</td>



                                                                                <td style="text-align:center; border-right:0.5px solid #000;padding:4px;">
                                                                                        <div class="custom-control custom-radio col-sm-1">
                                                                                                <input type="radio" <?php if ($row['after_surgery'] == '1') {
                                                                                                                                echo 'checked="checked"';
                                                                                                                        } ?>class="custom-control-input" id="after_surgery1" value="1" name="after_surgery" oninput="aftersurgerycheckValue()">
                                                                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="after_surgery1" style="font-size:100%;">1</label>
                                                                                        </div>
                                                                                </td>
                                                                        </tr>


                                                                        <tr style="border:1px solid #000;margin: 45px;">
                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" rowspan="4">&nbsp;<b>7. การได้รับยาและขนาดของยา หมายถึงการใด้รับยาที่มีผลต่อความดันโลหิต</b>
                                                                                        <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ระดับความรู้สึกตัวและมีผลทำให้ง่วงซึม เช่น ยาในกลุ่ม Sedative, Diuretics
                                                                                        <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tranquilizer (Psychotherapeutic) Antihypertensive drugs, Anticonvulsants, Cardiovascular drugs, Hypotonic, Barbiturates, Phenothiazine, Antidepressants, Laxative, Narcotics
                                                                                </td>
                                                                        </tr>

                                                                        <tr style="border:1px solid #000;margin: 45px;">

                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;">&nbsp;ผู้ป่วยได้รับยาข้างต้นมากกว่า 1 ชนิด</td>



                                                                                <td style="text-align:center; border-right:0.5px solid #000;padding:4px;">
                                                                                        <div class="custom-control custom-radio col-sm-1">
                                                                                                <input type="radio" <?php if ($row['drug_use'] == '3') {
                                                                                                                                echo 'checked="checked"';
                                                                                                                        } ?>class="custom-control-input" id="drug_use3" value="3" name="drug_use" oninput="drugusecheckValue()">
                                                                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="drug_use3" style="font-size:100%;">3</label>
                                                                                        </div>

                                                                                </td>

                                                                        </tr>


                                                                        <tr style="border:1px solid #000;margin: 45px;">

                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;">&nbsp;ผู้ป่วยได้รับยาข้างต้น 1 ชนิด</td>



                                                                                <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                                        <div class="custom-control custom-radio col-sm-1">
                                                                                                <input type="radio" <?php if ($row['drug_use'] == '2') {
                                                                                                                                echo 'checked="checked"';
                                                                                                                        } ?>class="custom-control-input" id="drug_use2" value="2" name="drug_use" oninput="drugusecheckValue()">
                                                                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="drug_use2" style="font-size:100%;">2</label>
                                                                                        </div>
                                                                                </td>
                                                                        </tr>

                                                                        <tr style="border:1px solid #000;margin: 45px;">

                                                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;">&nbsp;ผู้ป่วยได้รับยาชนิดอื่นนอกเหนือจากยาข้างต้นหรือไม่ได้รับยา</td>



                                                                                <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                                        <div class="custom-control custom-radio col-sm-1">
                                                                                                <input type="radio" <?php if ($row['drug_use'] == '1') {
                                                                                                                                echo 'checked="checked"';
                                                                                                                        } ?>class="custom-control-input" id="drug_use1" value="1" name="drug_use" oninput="drugusecheckValue()">
                                                                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="drug_use1" style="font-size:100%;">1</label>
                                                                                        </div>
                                                                                </td>
                                                                        </tr>

                                                                        <tr style="border:1px solid #000;margin: 45px;">
                                                                                <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="5%" rowspan="2">&nbsp;<b>คะแนน</b></td>
                                                                        </tr>

                                                                        <tr style="border:1px solid #000;margin: 45px;">

                                                                                <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;</td>



                                                                                <td style="text-align:center; border-right:0.5px solid #000;padding:4px; font-size: 20px;" width="1%">
                                                                                        <div>
                                                                                                <b><?= htmlspecialchars($row['score']) ?></b>
                                                                                        </div>
                                                                                </td>
                                                                        </tr>
                                                                </table>

                                                                <br>



                                                        </div>





                                                        <hr>

                                                </div>

                                                <!--  <div class="card-group pb-3 ">
                                <div class="card">
                                        <div class="card-body" style=" overflow-y: auto;"> -->
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
                                                                                <!--  <a href="mental-health3-pdf.php?an=<?php echo $an; ?>&id=<?= $ids ?>&loginname=<?php echo $loginname; ?>" target="_blank" class="btn btn-secondary"><i class="fas fa-file-pdf"></i> Print <U>PDF</U> File</a> -->
                                                                        </div>

                                                                        <div class="col-md-12 text-left">
                                                                                <b style="font-size: 15px;">หมายเหตุ :</b><span style="font-size: 15px;"> แบบประเมินนี้ ใช้กับผู้ป่วยทารกและเด็ก ตั้งแต่แรกเกิดถึงอายุ 15 ปี</span><br>
                                                                                <b style="font-size: 15px;">การแปลผล</b><span style="font-size: 15px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ความเสี่ยงต่ำ 7-11 คะแนน </span><br>
                                                                                <b style="font-size: 15px;"></b><span style="font-size: 15px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ความเสี่ยงปานกลาง 12-16 คะแนน </span><br>
                                                                                <b style="font-size: 15px;"></b><span style="font-size: 15px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ความเสี่ยงสูง ≥ 17 คะแนน </span>
                                                                        </div>
                                                                </div>
                                                        </div>
                                                </div>
                                                <br>

                                                <script src="../include/my_function.js"></script>
                                                <script type="text/javascript">
                                                        function myFunction() {
                                                                alert("Page is loaded");
                                                        }





                                                        function display_scoreyellow(sum_score, score_display_id) {
                                                                if (sum_score === "" || sum_score === null) {
                                                                        $('#' + score_display_id).html("");
                                                                } else {
                                                                        color = 'inherit';
                                                                        if (sum_score > 0 && sum_score <= 2) {
                                                                                color = '#e6b728';
                                                                        } else if (sum_score == 0) {
                                                                                color = '#45c351';
                                                                        } else if (sum_score >= '3') {
                                                                                color = '#e51616';
                                                                        }
                                                                        $('#' + score_display_id).html("<div class='alert text-white text-center font-weight-bold' style='font-size:100%;  background-color: " + color + ";'>" + "</div>");
                                                                }
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
                                                                        if (sum_score > 0 && sum_score <= 36) {
                                                                                color = '#45c351';
                                                                        } else if (sum_score >= 37 && sum_score <= 40) {
                                                                                color = '#e6b728';
                                                                        } else if (sum_score >= 41) {
                                                                                color = '#e51616';
                                                                        }
                                                                        $('#' + score_display_id).html("<div class='alert text-white text-center font-weight-bold' style='font-size:100%;  background-color: " + color + ";'> ผลรวม : " + sum_score + "</div>");
                                                                }
                                                        }



                                                        function form_save() {

                                                                var work_shift = $('input[name="work_shift"]:checked').val();

                                                                if (work_shift == undefined) {
                                                                        $('[name="work_shift"]').focus();
                                                                        //alert(depart)
                                                                        alert('work_shift');

                                                                        // window.location.reload(true);
                                                                }


                                                                var url_update = "child-felldown-update.php";
                                                                var url_save = "child-felldown-save.php";
                                                                var id = $("#id").val();
                                                                var my_form = $("#my_form").serialize();

                                                                if (id == "") {
                                                                        $.post(url_save, my_form, function(data) {
                                                                                        $("#show_check_save").html(data);
                                                                                        // window.history.back();
                                                                                        // alert("บันทึกข้อมูลสำเร็จ");
                                                                                        //self.close();
                                                                                        window.location.reload(true);

                                                                                        if (work_shift == undefined) {
                                                                                                window.location.reload(true);
                                                                                        } else {
                                                                                                self.close();
                                                                                        } 

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