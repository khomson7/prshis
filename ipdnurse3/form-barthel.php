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

$permissionCheck = Session::checkPermissionAndShowMessage('PRS_FORM_BARTHEL', 'VIEW');
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
//$hn = KphisQueryUtils::getHnByAn($an); // function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา
$vn = KphisQueryUtils::getVnByAn($an);


Session::insertSystemAccessLog(json_encode(array(
    'form' => 'BARTHEL-FORM',
    'an' => $an,
), JSON_UNESCAPED_UNICODE));




//----------------------เช็คว่า an นี้ มีข้อมูลหรือไม่

$sql = "SELECT *
                FROM `prs_barthel_index`
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

$_id = '25'; //Link menu

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


                        <div class="row">


                            &nbsp;&nbsp;&nbsp;&nbsp;
                            <div class="custom-control custom-radio col-2 col-md-2">
                                &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['chronic_check'] == '1') {
                                                                                echo 'checked="checked"';
                                                                            } ?> class="custom-control-input" id="chronic_check1" name="chronic_check" value="1">
                                <label class="custom-control-label" for="chronic_check1" style="font-size:100%; background-color:yellow;"><strong>&nbsp;Stroke&nbsp;</strong></label>
                            </div>
                            <div class="custom-control custom-radio col-2 col-md-2">
                                <input type="radio" <?php if ($row['chronic_check'] == '2') {
                                                        echo 'checked="checked"';
                                                    } ?> class="custom-control-input" id="chronic_check2" name="chronic_check" value="2">
                                <label class="custom-control-label" for="chronic_check2" style="font-size:100%; background-color:orange;"><strong>&nbsp;TBI&nbsp;</strong></label>
                            </div>
                            <div class="custom-control custom-radio col-2 col-md-2">
                                <input type="radio" <?php if ($row['chronic_check'] == '3') {
                                                        echo 'checked="checked"';
                                                    } ?> class="custom-control-input" id="chronic_check3" name="chronic_check" value="3">
                                <label class="custom-control-label" for="chronic_check3" style="font-size:100%; background-color:gray;"><strong>&nbsp;SCI&nbsp;</strong></label>
                            </div>

                            <div class="row">
                                <div class="custom-control custom-radio">
                                    <input type="radio" <?php if ($row['type_check'] == '1') {
                                                                echo 'checked="checked"';
                                                            } ?> class="custom-control-input" id="type_check1" value="1" name="type_check">
                                    <label class="custom-control-label" for="type_check1"> IMC </label>
                                </div>
                                &nbsp;&nbsp;&nbsp;&nbsp;<div class="custom-control custom-radio">
                                    <input type="radio" <?php if ($row['type_check'] == '2') {
                                                                echo 'checked="checked"';
                                                            } ?> class="custom-control-input" id="type_check2" value="2" name="type_check">
                                    <label class="custom-control-label" for="type_check2"> Non-IMC </label>
                                </div>
                            </div>

                        </div>






                        <div class="row">

                            <div class="col-12 col-md-12">


                                <table lass="center" id="bg-table" width="100%" style="border-collapse: collapse;font-size:10pt;margin-top:8px;">
                                    <tr style="border:1px solid #000;margin: 45px;">
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;<b>กิจกรรม</b></td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;<b>แบบประเมินกิจวัตรประจำวันของผู้ป่วย (Barthel index)</b></td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;<b>Admit</b></td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;<b>D/C</b></td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;<b></b></td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;<b></b></td>



                                    </tr>

                                    <tr style="border:1px solid #000;margin: 45px;">
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%" rowspan="4">&nbsp;<b>1.Feeding รับประทานอาหารเมื่อเตรียมสำรับไว้ให้เรียบร้อยต่อหน้า</b></td>
                                    </tr>

                                    <tr style="border:1px solid #000;margin: 45px;">

                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;0 = ไม่สามารถตักอาหารเข้าปากเองได้</td>



                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['feeding'] == '1') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="feeding0" value='1' name="feeding" oninput="feedingCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="feeding0" style="font-size:100%;">0</label>
                                            </div>

                                        </td>

                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['feeding_dc'] == '1') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="feeding_dc0" value="1" name="feeding_dc" oninput="feedingDcCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="feeding_dc0" style="font-size:100%;">0</label>
                                            </div>

                                        </td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>



                                    </tr>

                                    
                                    <tr style="border:1px solid #000;margin: 45px;">

                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;5 = ตักอาหารเองได้ แต่ต้องมีคนช่วย เช่น ช่วยใช้ช้อนตักอาหารเตรียมไว้ / ตัดเป็นชิ้น</td>



                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['feeding'] == '5') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="feeding5" value="5" name="feeding" oninput="feedingCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="feeding5" style="font-size:100%;">5</label>
                                            </div>

                                        </td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['feeding_dc'] == '5') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="feeding_dc5" value="5" name="feeding_dc" oninput="feedingDcCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="feeding_dc5" style="font-size:100%;">5</label>
                                            </div>

                                        </td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>

                                    </tr>

                                  
                                    <tr style="border:1px solid #000;margin: 45px;">

                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;10 = ตักอาหารและช่วยตัวเองได้ปกติ</td>

                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['feeding'] == '10') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="feeding10" value="10" name="feeding" oninput="feedingCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="feeding10" style="font-size:100%;">10</label>
                                            </div>

                                        </td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['feeding_dc'] == '10') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="feeding_dc10" value="10" name="feeding_dc" oninput="feedingDcCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="feeding_dc10" style="font-size:100%;">10</label>
                                            </div>

                                        </td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>

                                    </tr>





                                    <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%" rowspan="5">&nbsp;<b>2.Transfer ลุกนั่งจากที่นอนหรือจากเตียงไปยังเก้าอี้
                                            </b></td>
                                    </tr>

                                    <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">

                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;0 = ไม่สามารถนั่งได้ (นั่งแล้วจะล้มเสมอ)หรือต้องใช้คน 2 คนช่วยกันยกขึ้น</td>



                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['tranfer'] == '1') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="tranfer0" value="1" name="tranfer" oninput="tranferCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="tranfer0" style="font-size:100%;">0</label>
                                            </div>

                                        </td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['tranfer_dc'] == '1') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="tranfer_dc0" value="1" name="tranfer_dc" oninput="tranferDcCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="tranfer_dc0" style="font-size:100%;">0</label>
                                            </div>

                                        </td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                    </tr>


                                    <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">

                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;5 = ต้องการความช่วยเหลืออย่างมากจึงจะนั่งได้ เช่น ต้องใช้คน 1-2คนพยุงจึงจะนั่งได้</td>



                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['tranfer'] == '5') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="tranfer5" value="5" name="tranfer" oninput="tranferCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="tranfer5" style="font-size:100%;">5</label>
                                            </div>

                                        </td>

                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['tranfer_dc'] == '5') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="tranfer_dc5" value="5" name="tranfer_dc" oninput="tranferDcCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="tranfer_dc5" style="font-size:100%;">5</label>
                                            </div>

                                        </td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>

                                    </tr>


                                    <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">

                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;10 = ต้องการความช่วยเหลือบ้าง เช่น บอกให้ทำตามช่วยพยุงเล็กน้อย</td>

                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['tranfer'] == '10') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="tranfer10" value="10" name="tranfer" oninput="WettingTheSkinCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="tranfer10" style="font-size:100%;">10</label>
                                            </div>

                                        </td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['tranfer_dc'] == '10') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="tranfer_dc10" value="10" name="tranfer_dc" oninput="tranferDcCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="tranfer_dc10" style="font-size:100%;">10</label>
                                            </div>

                                        </td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>

                                    </tr>


                                    <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">

                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;15 = ทำเองได้</td>

                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['tranfer'] == '15') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="tranfer15" value="15" name="tranfer" oninput="WettingTheSkinCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="tranfer15" style="font-size:100%;">15</label>
                                            </div>

                                        </td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['tranfer_dc'] == '15') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="tranfer_dc15" value="15" name="tranfer_dc" oninput="tranferDcCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="tranfer_dc15" style="font-size:100%;">15</label>
                                            </div>

                                        </td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>

                                    </tr>


                                    <tr style="border:1px solid #000;margin: 45px;">
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%" rowspan="3">&nbsp;<b>3.Grooming การล้างหน้า หวีผม แปรงฟัน โกนหนวด</b></td>
                                    </tr>

                                    <tr style="border:1px solid #000;margin: 45px;">

                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;0 = ต้องการความช่วยเหลือ</td>



                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['grooming'] == '1') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="grooming0" value="1" name="grooming" oninput="groomingCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="grooming0" style="font-size:100%;">0</label>
                                            </div>

                                        </td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['grooming_dc'] == '1') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="grooming_dc0" value="1" name="grooming_dc" oninput="groomingDcCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="grooming_dc0" style="font-size:100%;">0</label>
                                            </div>

                                        </td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                    </tr>


                                    <tr style="border:1px solid #000;margin: 45px;">

                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;5 = ทำได้เอง (รวมทั้งที่ทำได้เอง ถ้าเตรียมอุปกรณ์ไว้ให้)</td>



                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['grooming'] == '5') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="grooming5" value="5" name="grooming" oninput="groomingCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="grooming5" style="font-size:100%;">5</label>
                                            </div>

                                        </td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['grooming_dc'] == '5') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="grooming_dc5" value="5" name="grooming_dc" oninput="groomingDcCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="grooming_dc5" style="font-size:100%;">5</label>
                                            </div>

                                        </td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>

                                    </tr>


                                    <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%" rowspan="4">&nbsp;<b>4.Toilet use การใช้ห้องน้ำ</b></td>
                                    </tr>

                                    <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">

                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;0 = ช่วยตัวเองไม่ได้</td>



                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['toilet_use'] == '1') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="toilet_use0" value="1" name="toilet_use" oninput="toiletUseCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="toilet_use0" style="font-size:100%;">0</label>
                                            </div>

                                        </td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['toilet_use_dc'] == '1') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="toilet_use_dc0" value="1" name="toilet_use_dc" oninput="toiletUseDcCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="toilet_use_dc0" style="font-size:100%;">0</label>
                                            </div>

                                        </td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>

                                    </tr>


                                    <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">

                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;5 = ทำได้เอง (ต้องการความช่วยเหลือในบางสิ่ง)</td>



                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['toilet_use'] == '5') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="toilet_use5" value="5" name="toilet_use" oninput="toiletUseCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="toilet_use5" style="font-size:100%;">5</label>
                                            </div>

                                        </td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['toilet_use_dc'] == '5') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="toilet_use_dc5" value="5" name="toilet_use_dc" oninput="toiletUseDcCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="toilet_use_dc5" style="font-size:100%;">5</label>
                                            </div>

                                        </td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>

                                    </tr>


                                    <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">

                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;10 = ช่วยเหลือตัวเองได้ดี(ขึ้นนั่งและลงจากโถส้วมได้เอง ทำความสะอาดได้เรียบร้อยหลังเสร็จ)</td>

                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['toilet_use'] == '10') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="toilet_use10" value="10" name="toilet_use" oninput="toiletUseCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="toilet_use10" style="font-size:100%;">10</label>
                                            </div>

                                        </td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['toilet_use_dc'] == '10') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="toilet_use_dc10" value="10" name="toilet_use_dc" oninput="toiletUseDcCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="toilet_use_dc10" style="font-size:100%;">10</label>
                                            </div>

                                        </td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>

                                    </tr>

                                    <tr style="border:1px solid #000;margin: 45px;">
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%" rowspan="3">&nbsp;<b>5.Bathing การอาบน้ำ</b></td>
                                    </tr>

                                    <tr style="border:1px solid #000;margin: 45px;">

                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;0 = ต้องมีคนช่วยหรือทำให้</td>



                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['bathing'] == '1') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="bathing0" value="1" name="bathing" oninput="bathingcheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="bathing0" style="font-size:100%;">0</label>
                                            </div>

                                        </td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['bathing_dc'] == '1') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="bathing_dc0" value="1" name="bathing_dc" oninput="bathingDcCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="bathing_dc0" style="font-size:100%;">0</label>
                                            </div>

                                        </td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>

                                    </tr>

                                    <tr style="border:1px solid #000;margin: 45px;">

                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;5 = อาบน้ำได้เอง</td>



                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['bathing'] == '5') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="bathing5" value="5" name="bathing" oninput="bathingcheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="bathing5" style="font-size:100%;">5</label>
                                            </div>

                                        </td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['bathing_dc'] == '5') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="bathing_dc5" value="5" name="bathing_dc" oninput="bathingDcCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="bathing_dc5" style="font-size:100%;">5</label>
                                            </div>

                                        </td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>

                                    </tr>



                                    <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%" rowspan="5">&nbsp;<b>6.Mobility การเคลื่อนที่ภายในห้องหรือบ้าน</b></td>
                                    </tr>

                                    <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">

                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;0 = เคลื่อนที่ไปไหนไม่ได้</td>



                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['mobility'] == '1') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="mobility0" value="1" name="mobility" oninput="mobilitycheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="mobility0" style="font-size:100%;">0</label>
                                            </div>
                                        </td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['mobility_dc'] == '1') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="mobility_dc0" value="1" name="mobility_dc" oninput="mobilityDcCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="mobility_dc0" style="font-size:100%;">0</label>
                                            </div>

                                        </td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                    </tr>


                                    <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">

                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;5 = ต้องใช้รถเข็นช่วยตัวเองให้เคลื่อนที่ได้เอง(ไม่ต้องมีคนช่วยเข็น)</td>



                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['mobility'] == '5') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="mobility5" value="5" name="mobility" oninput="mobilitycheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="mobility5" style="font-size:100%;">5</label>
                                            </div>

                                        </td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['mobility_dc'] == '5') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="mobility_dc5" value="5" name="mobility_dc" oninput="mobilityDcCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="mobility_dc5" style="font-size:100%;">5</label>
                                            </div>

                                        </td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>

                                    </tr>


                                    <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">

                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;10 = เดินหรือเคลื่อนที่ได้โดยมีคนช่วย เช่น พยุง หรือบอกให้ทำตาม</td>

                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['mobility'] == '10') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="mobility10" value="10" name="mobility" oninput="mobilitycheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="mobility10" style="font-size:100%;">10</label>
                                            </div>

                                        </td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['mobility_dc'] == '10') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="mobility_dc10" value="10" name="mobility_dc" oninput="mobilityDcCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="mobility_dc10" style="font-size:100%;">10</label>
                                            </div>

                                        </td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>

                                    </tr>

                                    <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">

                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;15 = เดินหรือเคลื่อนที่ได้เอง</td>

                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['mobility'] == '15') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="mobility15" value="15" name="mobility" oninput="mobilitycheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="mobility15" style="font-size:100%;">15</label>
                                            </div>

                                        </td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['mobility_dc'] == '15') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="mobility_dc15" value="15" name="mobility_dc" oninput="mobilityDcCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="mobility_dc15" style="font-size:100%;">15</label>
                                            </div>

                                        </td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>

                                    </tr>

                                    <tr style="border:1px solid #000;margin: 45px;">
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%" rowspan="4">&nbsp;<b>7.Stairs การขึ้นลงบันได 1 ขั้น</b></td>
                                    </tr>

                                    <tr style="border:1px solid #000;margin: 45px;">

                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;0 = ไม่สามารถทำได้</td>



                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['stairs'] == '1') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="stairs0" value="1" name="stairs" oninput="stairscheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="stairs0" style="font-size:100%;">0</label>
                                            </div>

                                        </td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['stairs_dc'] == '1') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="stairs_dc0" value="1" name="stairs_dc" oninput="stairsDcCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="stairs_dc0" style="font-size:100%;">0</label>
                                            </div>

                                        </td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>

                                    </tr>

                                    <tr style="border:1px solid #000;margin: 45px;">

                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;5 = ต้องการคนช่วยเหลือ</td>



                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['stairs'] == '5') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="stairs5" value="5" name="stairs" oninput="stairscheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="stairs5" style="font-size:100%;">5</label>
                                            </div>

                                        </td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['stairs_dc'] == '5') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="stairs_dc5" value="5" name="stairs_dc" oninput="stairsDcCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="stairs_dc5" style="font-size:100%;">5</label>
                                            </div>

                                        </td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>

                                    </tr>
                                    <tr style="border:1px solid #000;margin: 45px;">

                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;10 = ขึ้นลงเองได้ (ถ้าต้องการใช้อุปกรณ์ เช่น walker)</td>

                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['stairs'] == '10') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="stairs10" value="10" name="stairs" oninput="foodcheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="stairs10" style="font-size:100%;">10</label>
                                            </div>

                                        </td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['stairs_dc'] == '10') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="stairs_dc10" value="10" name="stairs_dc" oninput="stairsDcCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="stairs_dc10" style="font-size:100%;">10</label>
                                            </div>

                                        </td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>

                                    </tr>


                                    <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%" rowspan="4">&nbsp;<b>8.Dressing การสวมใส่เสื้อผ้า
                                            </b></td>
                                    </tr>

                                    <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">

                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;0 = ต้องมีคนช่วยสวมใส่ให้ ช่วยตัวเองไม่ได้เลยหรือน้อย</td>



                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['dressing'] == '1') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="dressing0" value="1" name="dressing" oninput="dressingcheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="dressing0" style="font-size:100%;">0</label>
                                            </div>
                                        </td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['dressing_dc'] == '1') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="dressing_dc0" value="1" name="dressing_dc" oninput="dressingDcCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="dressing_dc0" style="font-size:100%;">0</label>
                                            </div>

                                        </td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                    </tr>

                                    <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">

                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;5 = ช่วยตัวเองได้ร้อยละ 50 ที่เหลือต้องมีคนช่วย</td>



                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['dressing'] == '5') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="dressing5" value="5" name="dressing" oninput="dressingcheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="dressing5" style="font-size:100%;">5</label>
                                            </div>

                                        </td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['dressing_dc'] == '5') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="dressing_dc5" value="5" name="dressing_dc" oninput="dressingDcCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="dressing_dc5" style="font-size:100%;">5</label>
                                            </div>

                                        </td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>

                                    </tr>


                                    <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">

                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;10 = ช่วยเหลือตัวเองได้ดี (รวมทั้งการติดกระดุม รูดซิป)</td>

                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['dressing'] == '10') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="dressing10" value="10" name="dressing" oninput="dressingcheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="dressing10" style="font-size:100%;">10</label>
                                            </div>

                                        </td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['dressing_dc'] == '10') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="dressing_dc10" value="10" name="dressing_dc" oninput="dressingDcCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="dressing_dc10" style="font-size:100%;">10</label>
                                            </div>

                                        </td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>

                                    </tr>

                                    <tr style="border:1px solid #000;margin: 45px;">
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%" rowspan="4">&nbsp;<b>9.Bowel การกลั้นการถ่ายอุจจาระ ใน 1 สัปดาห์ที่ผ่านมา
                                            </b></td>
                                    </tr>

                                    <tr style="border:1px solid #000;margin: 45px;">

                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;0 = กลั้นไม่ได้ หรือต้องการสวนอุจจาระอยู่เสมอ</td>



                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['bowel'] == '1') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="bowel0" value="1" name="bowel" oninput="bowelcheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="bowel0" style="font-size:100%;">0</label>
                                            </div>

                                        </td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['bowel_dc'] == '1') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="bowel_dc0" value="1" name="bowel_dc" oninput="bowelDcCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="bowel_dc0" style="font-size:100%;">0</label>
                                            </div>

                                        </td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>

                                    </tr>

                                    <tr style="border:1px solid #000;margin: 45px;">

                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;5 = กลั้นไม่ได้เป็นบางครั้ง(ไม่เกิน 1 สัปดาห์ต่อครั้ง)</td>



                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['bowel'] == '5') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="bowel5" value="5" name="bowel" oninput="bowelcheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="bowel5" style="font-size:100%;">5</label>
                                            </div>

                                        </td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['bowel_dc'] == '5') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="bowel_dc5" value="5" name="bowel_dc" oninput="bowelDcCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="bowel_dc5" style="font-size:100%;">5</label>
                                            </div>

                                        </td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>

                                    </tr>
                                    <tr style="border:1px solid #000;margin: 45px;">

                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;10 = กลั้นได้ปกติ</td>



                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['bowel'] == '10') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="bowel10" value="10" name="bowel" oninput="bowelcheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="bowel10" style="font-size:100%;">10</label>
                                            </div>

                                        </td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['bowel_dc'] == '10') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="bowel_dc10" value="10" name="bowel_dc" oninput="bowelDcCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="bowel_dc10" style="font-size:100%;">10</label>
                                            </div>

                                        </td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>

                                    </tr>

                                    <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%" rowspan="4">&nbsp;<b>10.Bladder การกลั้นปัสสาวะใน 1 สัปดาห์ที่ผ่านมา</b></td>
                                    </tr>

                                    <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">

                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;0 = กลั้นไม่ได้ หรือใส่สายสวนปัสสาวะ และไม่สามารถดูแลตัวเองได้</td>



                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['bladder'] == '1') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="bladder0" value="1" name="bladder" oninput="bladdercheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="bladder0" style="font-size:100%;">0</label>
                                            </div>
                                        </td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['bladder_dc'] == '1') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="bladder_dc0" value="1" name="bladder_dc" oninput="bladderDcCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="bladder_dc0" style="font-size:100%;">0</label>
                                            </div>

                                        </td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                    </tr>

                                    <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">

                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;5 = กลั้นไม่ได้เป็นบางครั้ง(ไม่เกินวันละ 1 ครั้ง)</td>



                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['bladder'] == '5') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="bladder5" value="5" name="bladder" oninput="bladdercheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="bladder5" style="font-size:100%;">5</label>
                                            </div>

                                        </td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['bladder_dc'] == '5') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="bladder_dc5" value="5" name="bladder_dc" oninput="bladderDcCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="bladder_dc5" style="font-size:100%;">5</label>
                                            </div>

                                        </td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>

                                    </tr>


                                    <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">

                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;10 = กลั้นได้ปกติ</td>

                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['bladder'] == '10') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="bladder10" value="10" name="bladder" oninput="bladdercheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="bladder10" style="font-size:100%;">10</label>
                                            </div>

                                        </td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['bladder_dc'] == '10') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="bladder_dc10" value="10" name="bladder_dc" oninput="bladderDcCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="bladder_dc10" style="font-size:100%;">10</label>
                                            </div>

                                        </td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>

                                    </tr>

                                    <tr style="border:1px solid #000;margin: 45px;">
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="5%" rowspan="2">&nbsp;<b>รวมคะแนน</b></td>
                                    </tr>

                                    <tr style="border:1px solid #000;margin: 45px;">

                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;</td>



                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px; font-size: 20px;" width="1%">
                                            <div>
                                                <b><?= htmlspecialchars($row['score']) ?></b>
                                            </div>

                                        </td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px; font-size: 20px;" width="1%">
                                            <div>
                                                <b><?= htmlspecialchars($row['score_dc']) ?></b>
                                            </div>

                                        </td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>

                                    </tr>

                                </table>

                                <br>

                            </div>

                            <hr>

                        </div>

                        <div class="row">

                            <div class="col-12 col-md-12">
                                <table lass="center" id="bg-table" width="100%" style="border-collapse: collapse;font-size:10pt;margin-top:8px;">

                                    <tr style="border:1px solid #000;margin: 45px;">
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="5%" >&nbsp;<b></b></td>  
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="10%" >&nbsp;<b>THE MODIFIED RANKIN SCALE</b></td>                  
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;<b>Admit</b></td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;<b>D/C</b></td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;<b></b></td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;<b></b></td>



                                    </tr>

                                    <tr style="border:1px solid #000;margin: 45px;">

                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;0</td>

                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;ไม่มีความผิดปกติเลย</td>

                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['rankin_scale'] == '9') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="rankin_scale0" value="9" name="rankin_scale" oninput="rankinScaleCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="rankin_scale0" style="font-size:100%;">0</label>
                                            </div>

                                        </td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['rankin_scale_dc'] == '9') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="rankin_scale_dc0" value="9" name="rankin_scale_dc" oninput="rankinScaleDcCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="rankin_scale_dc0" style="font-size:100%;">0</label>
                                            </div>

                                        </td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>

                                    </tr>

                                    <!-- 2 -->
                                    <tr style="border:1px solid #000;margin: 45px;">

                                    <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;1</td>

                                    <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;ไม่มีความผิดปกติที่รุนแรง สามารถทำกิจวัตรประจำวันได้ทุกอย่าง ทำงานอาชีพได้</td>


                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['rankin_scale'] == '1') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="rankin_scale1" value="1" name="rankin_scale" oninput="rankinScaleCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="rankin_scale1" style="font-size:100%;">1</label>
                                            </div>

                                        </td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['rankin_scale_dc'] == '1') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="rankin_scale_dc1" value="1" name="rankin_scale_dc" oninput="rankinScaleDcCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="rankin_scale_dc1" style="font-size:100%;">1</label>
                                            </div>

                                        </td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>

                                    </tr>


                                    <tr style="border:1px solid #000;margin: 45px;">
                                    <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;2</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;มีความผิดปกติเล็กน้อย สามารถทำกิจวัตรประจำวันได้ทุกอย่าง แต่ทำงานอาชีพไม่ได้</td>

                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['rankin_scale'] == '2') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="rankin_scale2" value="2" name="rankin_scale" oninput="rankinScaleCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="rankin_scale2" style="font-size:100%;">2</label>
                                            </div>

                                        </td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['rankin_scale_dc'] == '2') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="rankin_scale_dc2" value="2" name="rankin_scale_dc" oninput="rankinScaleDcCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="rankin_scale_dc2" style="font-size:100%;">2</label>
                                            </div>

                                        </td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>

                                    </tr>
                                    
                                    <tr style="border:1px solid #000;margin: 45px;">
                                    <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;3</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;มีความผิดปกติพอสมควร สามารถทำกิจวัตรประจำวันได้บางอย่าง เดินได้โดยไม่ต้องมีคนช่วย</td>

                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['rankin_scale'] == '3') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="rankin_scale3" value="3" name="rankin_scale" oninput="rankinScaleCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="rankin_scale3" style="font-size:100%;">3</label>
                                            </div>

                                        </td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['rankin_scale_dc'] == '3') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="rankin_scale_dc3" value="3" name="rankin_scale_dc" oninput="rankinScaleDcCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="rankin_scale_dc3" style="font-size:100%;">3</label>
                                            </div>

                                        </td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>

                                    </tr>

                                    <tr style="border:1px solid #000;margin: 45px;">
                                    <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;4</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;มีความผิดปกติมาก ไม่สามารถทำกิจวัตรประจำวันเองโดยไม่มีคนช่วยได้ เดินได้แต่ต้องพยุง</td>

                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['rankin_scale'] == '4') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="rankin_scale4" value="4" name="rankin_scale" oninput="rankinScaleCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="rankin_scale4" style="font-size:100%;">4</label>
                                            </div>

                                        </td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['rankin_scale_dc'] == '4') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="rankin_scale_dc4" value="4" name="rankin_scale_dc" oninput="rankinScaleDcCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="rankin_scale_dc4" style="font-size:100%;">4</label>
                                            </div>

                                        </td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>

                                    </tr>

                                    <tr style="border:1px solid #000;margin: 45px;">
                                    <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;5</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;มีความผิดปกติรุนแรง ต้องนอนบนเตียง ปัสสาวะราด ต้องการการดูแลอย่างใกล้ชิด</td>

                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['rankin_scale'] == '5') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="rankin_scale5" value="5" name="rankin_scale" oninput="rankinScaleCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="rankin_scale5" style="font-size:100%;">5</label>
                                            </div>

                                        </td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['rankin_scale_dc'] == '5') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="rankin_scale_dc5" value="5" name="rankin_scale_dc" oninput="rankinScaleDcCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="rankin_scale_dc5" style="font-size:100%;">5</label>
                                            </div>

                                        </td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>

                                    </tr>

                                    <tr style="border:1px solid #000;margin: 45px;">
                                    <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;6</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;เสียชีวิต</td>

                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['rankin_scale'] == '6') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="rankin_scale6" value="6" name="rankin_scale" oninput="rankinScaleCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="rankin_scale6" style="font-size:100%;">6</label>
                                            </div>

                                        </td>

                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                            <div class="custom-control custom-radio col-sm-1">
                                                <input type="radio" <?php if ($row['rankin_scale_dc'] == '6') {
                                                                        echo 'checked="checked"';
                                                                    } ?>class="custom-control-input" id="rankin_scale_dc6" value="6" name="rankin_scale_dc" oninput="rankinScaleDcCheckValue()">
                                                <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="rankin_scale_dc6" style="font-size:100%;">6</label>
                                            </div>

                                        </td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>

                                    </tr>
                                    



                                </table>


                            </div>
                        </div>
<br />
                        <!--  <div class="card-group pb-3 ">
                                <div class="card">
                                        <div class="card-body" style=" overflow-y: auto;"> -->
                        <div class="row">
                            <div class="col-md-12">





                                <div class="row">
                                    <div id="show_check_save"></div>
                                    <input type="hidden" id="an" name="an" value="<?= htmlspecialchars($an) ?>">
                                    <input type="hidden" id="version" name="version" value="<?= htmlspecialchars($row['version']) ?>">
                                    <input type="hidden" id="id" name="id" value="<?= htmlspecialchars($row['id']) ?>">
                                    <input type="hidden" id="create_user" name="create_user" value="<?= htmlspecialchars($_SESSION['name']) ?>">
                                    <input type="hidden" id="create_datetime" name="create_datetime" value="<?= htmlspecialchars($row['create_datetime']) ?>">
                                    <!-- <input type="hidden" id="score_total_result" name="total_sum" value="10">-->


                                    <div class="col-md-12 text-right">
                                        <?php
                                        if ((
                                            Session::checkPermission('PRS_FORM_BARTHEL', 'ADD')
                                        ) && (ReportQueryUtils::checkReadOnly($an))) { ?>
                                            <button type="button" class="btn btn-primary" id="btn_lr_report1" onclick="form_save()"><i class="fas fa-save"></i> บันทึก</button>
                                        <?php } ?>
                                          <a href="/pdffile/barthel-pdf.php?an=<?php echo $an; ?>&id=<?= $ids ?>&loginname=<?php echo $loginname; ?>" target="_blank" class="btn btn-secondary"><i class="fas fa-file-pdf"></i> Print <U>PDF</U> File</a>
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


                            function form_save() {
                                var chronic_check = $('input[name="chronic_check"]:checked').val();
                                var type_check = $('input[name="type_check"]:checked').val();

                                if (chronic_check== undefined) {
                                    $('[name="chronic_check"]').focus();
                                    alert('Stroke / TBI / SCI');
                                } 
                                else if (type_check == undefined) {
                                    $('[name="type_check"]').focus();
                                    alert('IMC / Non-IMC');
                                }
                                

                                var url_update = "form-barthel-update.php";
                                var url_save = "form-barthel-save.php";
                                var id = $("#id").val();
                                var my_form = $("#my_form").serialize();

                                if (id == "") {
                                    $.post(url_save, my_form, function(data) {
                                            $("#show_check_save").html(data);
                                            // window.history.back();
                                            // alert("บันทึกข้อมูลสำเร็จ");
                                            //self.close();
                                            window.location.reload(true);

                                          /*  if (chronic_check == undefined) {
                                                window.location.reload(true);
                                            } else {
                                                self.close();
                                            }  */

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