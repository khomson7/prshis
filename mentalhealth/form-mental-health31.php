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

$permissionCheck = Session::checkPermissionAndShowMessage('PRS_FORM_MENTAL_HEALTH3', 'VIEW');
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
        'form' => 'MENTAL-HEALTH3-FORM',
        'an' => $an,
), JSON_UNESCAPED_UNICODE));


//echo $ids;

//----------------------เช็คว่า an นี้ มีข้อมูลหรือไม่




$sql = "SELECT *
                FROM `prs_mental_health3`
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
$id = '22'; //Link menu
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

        table, th, td {
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
                        } elseif ($variation1 >= 3 ) {
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
                        } elseif ($variation2 >= 3 ) {
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
                        } elseif ($variation3 >= 3 ) {
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
                        } elseif ($variation4 >= 3 ) {
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


                                &nbsp;&nbsp;&nbsp;&nbsp;<label>เวร: </label>
                                <div class="custom-control custom-radio col-sm-1">
                                        &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['level_of_consciousness'] == '1') {
                                                                                                echo 'checked="checked"';
                                                                                        } ?> class="custom-control-input" id="level_of_consciousness1" name="level_of_consciousness" value="1">
                                        <label class="custom-control-label" for="level_of_consciousness1" style="font-size:100%; background-color:yellow;"><strong>&nbsp;ดึก&nbsp;</strong></label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                        <input type="radio" <?php if ($row['level_of_consciousness'] == '2') {
                                                                        echo 'checked="checked"';
                                                                } ?> class="custom-control-input" id="level_of_consciousness2" name="level_of_consciousness" value="2">
                                        <label class="custom-control-label" for="level_of_consciousness2" style="font-size:100%; background-color:orange;"><strong>&nbsp;เช้า&nbsp;</strong></label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                        <input type="radio" <?php if ($row['level_of_consciousness'] == '3') {
                                                                        echo 'checked="checked"';
                                                                } ?> class="custom-control-input" id="level_of_consciousness3" name="level_of_consciousness" value="3">
                                        <label class="custom-control-label" for="level_of_consciousness3" style="font-size:100%; background-color:gray;"><strong>&nbsp;บ่าย&nbsp;</strong></label>
                                </div>




                        </div>




                        <div class="row">

                        <div class="col-12 col-md-12">
                               

                                        <table lass="center" id="bg-table" width="100%" style="border-collapse: collapse;font-size:10pt;margin-top:8px;">
                                                <tr style="border:1px solid #000;margin: 45px;">

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;<b>ภาวะเสี่ยง</b></td>
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;<b>ระดับรุนแรง</b></td>



                                                </tr>

                                                <tr style="border:1px solid #000;margin: 45px;">
                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%" colspan="2">&nbsp;<b>เกณฑ์การประเมินผู้ป่วยเสี่ยงต่อการทำร้าย (S)</b></td>
                                                </tr>

                                                <tr style="border:1px solid #000;margin: 45px;">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;1.มีภาวะซึมเศร้า โดย 1 เดือนที่ผ่านมารวมถึงวันนี้รู้สึกหดหู่เศร้า หรือท้อแท้สิ้นหวัง รู้สึกไม่มีคุณค่า</td>



                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-checkbox  col-sm-1">
                                                                        <input type="checkbox" <?php if ($row['question1_1'] == '1') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="question1_1" value="1" name="question1_1" oninput="oninputcheckValue1()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="question1_1" style="font-size:100%; background-color:yellow;">เหลือง</label>
                                                                </div>

                                                        </td>





                                                </tr>

                                                <!-- 2 -->
                                                <tr style="border:1px solid #000;margin: 45px;">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;2. มีประวัติเคยพยายามฆ่าตัวตายภายใน 1 เดือน ก่อนมาโรงพยาบาล</td>



                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-checkbox  col-sm-1">
                                                                        <input type="checkbox" <?php if ($row['question1_2'] == '1') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="question1_2" value="1" name="question1_2" oninput="oninputcheckValue1()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="question1_2" style="font-size:100%; background-color:yellow;">เหลือง</label>
                                                                </div>

                                                        </td>

                                                </tr>

                                                <!-- 3 -->
                                                <tr style="border:1px solid #000;margin: 45px;">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;3. มีความคิด / พูดบ่นอยากตาย</td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-checkbox  col-sm-1">
                                                                        <input type="checkbox" <?php if ($row['question1_3'] == '3') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="question1_3" value="3" name="question1_3" oninput="oninputcheckValue1()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="question1_3" style="font-size:100%; background-color:red;">แดง</label>
                                                                </div>

                                                        </td>

                                                </tr>

                                                <!-- 4 -->
                                                <tr style="border:1px solid #000;margin: 45px;">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;4.หลงผิดเกี่ยวกับการผิดบาป โทษตัวเองมีเสียงแว่วให้ทำร้ายตัวเอง</td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-checkbox  col-sm-1">
                                                                        <input type="checkbox" <?php if ($row['question1_4'] == '4') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="question1_4" value="4" name="question1_4" oninput="oninputcheckValue1()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="question1_4" style="font-size:100%; background-color:red;">แดง</label>
                                                                </div>

                                                        </td>

                                                </tr>


                                                <!-- 5 -->
                                                <tr style="border:1px solid #000;margin: 45px;">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;5.มีพฤติกรรมทำร้ายตัวเอง</td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-checkbox  col-sm-1">
                                                                        <input type="checkbox" <?php if ($row['question1_5'] == '5') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="question1_5" value="5" name="question1_5" oninput="oninputcheckValue1()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="question1_5" style="font-size:100%; background-color:red;">แดง</label>
                                                                </div>

                                                        </td>




                                                </tr>

                                                <tr style="border:1px solid #000;margin: 45px;">
                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;<b>ความรุนแรงระดับสี</b><div class="col-sm-1" id="check_question"></div></td>
                                                        
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px; background-color: <?= htmlspecialchars($bg_color1) ?>; width:100%; colspan:5; display:flex; justify-content:center; align-items:center;">
                                                <span style="color: <?= htmlspecialchars($font_color) ?>; font-size: 20px;">S</span> 
                                                </td>
                                                
                                                </td>
                                                      
                                                </tr>


                                                <tr style="border:1px solid #000;margin: 45px;">
                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%" colspan="2">&nbsp;<b>เกณฑ์การประเมินผู้ป่วยเสี่ยงต่อการได้รับอุบัติเหตุ (A)</b></td>
                                                </tr>

                                                <!-- 1 -->
                                                <tr style="border:1px solid #000;margin: 45px;">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;1.ผู้ป่วยมีอายุ 60 ปีขึ้นไป และ/หรือ มีโรคประจำตัว</td>



                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-checkbox  col-sm-1">
                                                                        <input type="checkbox" <?php if ($row['question2_1'] == '1') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="question2_1" value="1" name="question2_1" oninput="oninputcheckValue2()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="question2_1" style="font-size:100%; background-color:yellow;">เหลือง</label>
                                                                </div>

                                                        </td>

                                                </tr>

                                                <!-- 2 -->
                                                <tr style="border:1px solid #000;margin: 45px;">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;2.ผู้ป่วยได้รับยา HAD หรือผู้ป่วยได้รับยาในกลุ่ม Benzodizepine ตามการประเมิน AWS Score
                                                                หรือได้รับยาฉีด PRN มากกว่า 2 ครั้ง ใน 1 วัน</td>



                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-checkbox  col-sm-1">
                                                                        <input type="checkbox" <?php if ($row['question2_2'] == '1') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="question2_2" value="1" name="question2_2" oninput="oninputcheckValue2()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="question2_2" style="font-size:100%; background-color:yellow;">เหลือง</label>
                                                                </div>

                                                        </td>

                                                </tr>


                                                <!-- 3 -->
                                                <tr style="border:1px solid #000;margin: 45px;">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;3.ผู้ป่วยมีการถอนพิษสุรา</td>



                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-checkbox  col-sm-1">
                                                                        <input type="checkbox" <?php if ($row['question2_3'] == '3') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="question2_3" value="3" name="question2_3" oninput="oninputcheckValue2()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="question2_3" style="font-size:100%; background-color:red;">แดง</label>
                                                                </div>

                                                        </td>

                                                </tr>


                                                <!-- 4 -->
                                                <tr style="border:1px solid #000;margin: 45px;">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;4.ผู้ป่วยที่มีการทรงตัวไม่ดี มึนงง สับสน</td>



                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-checkbox  col-sm-1">
                                                                        <input type="checkbox" <?php if ($row['question2_4'] == '4') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="question2_4" value="4" name="question2_4" oninput="oninputcheckValue2()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="question2_4" style="font-size:100%; background-color:red;">แดง</label>
                                                                </div>

                                                        </td>

                                                </tr>

                                                <!-- 5 -->
                                                <tr style="border:1px solid #000;margin: 45px;">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;5.ผู้ป่วยมีอาการชักภายใน 1 เดือน</td>



                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-checkbox  col-sm-1">
                                                                        <input type="checkbox" <?php if ($row['question2_5'] == '5') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="question2_5" value="5" name="question2_5" oninput="oninputcheckValue2()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="question2_5" style="font-size:100%; background-color:red;">แดง</label>
                                                                </div>

                                                        </td>

                                                </tr>

                                                <tr style="border:1px solid #000;margin: 45px;">
                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;<b>ความรุนแรงระดับสี</b><div class="col-sm-1" id="check_question2"></div></td>
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px; background-color: <?= htmlspecialchars($bg_color2) ?>; width:100%; colspan:5; display:flex; justify-content:center; align-items:center;">
                                                <span style="color: <?= htmlspecialchars($font_color) ?>; font-size: 20px;">A</span> 
                                                </td>
                                                
                                                </td>
                                                      
                                                </tr>


                                                <tr style="border:1px solid #000;margin: 45px;">
                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%" colspan="2">&nbsp;<b>เกณฑ์การประเมินผู้ป่วยเสี่ยงต่อพฤติกรรมรุนแรง (V)</b></td>
                                                </tr>

                                                <!-- 1 -->
                                                <tr style="border:1px solid #000;margin: 45px;">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;1.มีประวัติพฤติกรรมรุนแรง ปฏิเสธการเจ็บป่วย ไม่ให้ความร่วมมือในการรักษา</td>



                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-checkbox  col-sm-1">
                                                                        <input type="checkbox" <?php if ($row['question3_1'] == '1') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="question3_1" value="1" name="question3_1" oninput="oninputcheckValue3()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="question3_1" style="font-size:100%; background-color:yellow;">เหลือง</label>
                                                                </div>

                                                        </td>

                                                </tr>

                                                <!-- 2 -->
                                                <tr style="border:1px solid #000;margin: 45px;">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;2.ระแวง หลงผิดคิดว่ามีผู้อื่นมาทำร้าย</td>



                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-checkbox  col-sm-1">
                                                                        <input type="checkbox" <?php if ($row['question3_2'] == '1') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="question3_2" value="1" name="question3_2" oninput="oninputcheckValue3()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="question3_2" style="font-size:100%; background-color:yellow;">เหลือง</label>
                                                                </div>

                                                        </td>

                                                </tr>

                                                <!-- 3 -->
                                                <tr style="border:1px solid #000;margin: 45px;">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;3.มีการรับรู้ผิดปกติ เช่น มีหูแว่ว เห็นภาพหลอน</td>



                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-checkbox  col-sm-1">
                                                                        <input type="checkbox" <?php if ($row['question3_3'] == '3') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="question3_3" value="3" name="question3_3" oninput="oninputcheckValue3()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="question3_3" style="font-size:100%; background-color:red;">แดง</label>
                                                                </div>

                                                        </td>

                                                </tr>

                                                <!-- 4 -->
                                                <tr style="border:1px solid #000;margin: 45px;">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;4.มีพฤติกรรมรุนแรง</td>



                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-checkbox  col-sm-1">
                                                                        <input type="checkbox" <?php if ($row['question3_4'] == '4') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="question3_4" value="4" name="question3_4" oninput="oninputcheckValue3()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="question3_4" style="font-size:100%; background-color:red;">แดง</label>
                                                                </div>

                                                        </td>

                                                </tr>

                                                <!-- 5 -->
                                                <tr style="border:1px solid #000;margin: 45px;">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;5.ตาขวาง พูดเสียงดัง ดุด่าผู้อื่น ไม่รับฟัง</td>



                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-checkbox  col-sm-1">
                                                                        <input type="checkbox" <?php if ($row['question3_5'] == '5') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="question3_5" value="5" name="question3_5" oninput="oninputcheckValue3()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="question3_5" style="font-size:100%; background-color:red;">แดง</label>
                                                                </div>

                                                        </td>

                                                </tr>
                                               
                                                <tr style="border:1px solid #000;margin: 45px;">
                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;<b>ความรุนแรงระดับสี</b><div class="col-sm-1" id="check_question3"></div></td>
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px; background-color: <?= htmlspecialchars($bg_color3) ?>; width:100%; colspan:5; display:flex; justify-content:center; align-items:center;">
                                                <span style="color: <?= htmlspecialchars($font_color) ?>; font-size: 20px;">V</span> 
                                                </td>
                                                
                                                </td>
                                                      
                                                </tr>


                                                <tr style="border:1px solid #000;margin: 45px;">
                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%" colspan="2">&nbsp;<b>เกณฑ์การประเมินผู้ป่วยเสี่ยงต่อการหลบหนี (E)</b></td>
                                                </tr>

                                                <!-- 1 -->
                                                <tr style="border:1px solid #000;margin: 45px;">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;1.มีประวัติพยายามหลบหนี ปฏิเสธการเจ็บป่วย ไม่อยู่โรงพยาบาล</td>



                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-checkbox  col-sm-1">
                                                                        <input type="checkbox" <?php if ($row['question4_1'] == '1') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="question4_1" value="1" name="question4_1" oninput="oninputcheckValue4()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="question4_1" style="font-size:100%; background-color:yellow;">เหลือง</label>
                                                                </div>

                                                        </td>

                                                </tr>

                                                <!-- 2 -->
                                                <tr style="border:1px solid #000;margin: 45px;">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;2.มีประวัติติดสารเสพติดและอยากยาเสพติด หรือ Admit ใน 7 วันแรก</td>



                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-checkbox  col-sm-1">
                                                                        <input type="checkbox" <?php if ($row['question4_2'] == '1') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="question4_2" value="1" name="question4_2" oninput="oninputcheckValue4()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="question4_2" style="font-size:100%; background-color:yellow;">เหลือง</label>
                                                                </div>

                                                        </td>

                                                </tr>

                                                <!-- 3 -->
                                                <tr style="border:1px solid #000;margin: 45px;">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;3.รบเร้าเรื่องกลับบ้านบ่อยๆ หรือ ขอให้โทรศัพท์ติดต่อญาติหรือไม่ได้กลับบ้านตามกำหนด 
พูดขู่ว่าจะหนี ขอออกนอกตึกบ่อยๆ</td>



                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-checkbox  col-sm-1">
                                                                        <input type="checkbox" <?php if ($row['question4_3'] == '3') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="question4_3" value="3" name="question4_3" oninput="oninputcheckValue4()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="question4_3" style="font-size:100%; background-color:red;">แดง</label>
                                                                </div>

                                                        </td>

                                                </tr>

                                                <!-- 4 -->
                                                <tr style="border:1px solid #000;margin: 45px;">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;4.มีพฤติกรรมบ่งชี้ถึงสัญญาณการเตือนว่าจะมีการหลบหนี้ เช่น จ้องมองประตูพยายามงัดแงะหาทางออก</td>



                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-checkbox  col-sm-1">
                                                                        <input type="checkbox" <?php if ($row['question4_4'] == '4') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="question4_4" value="4" name="question4_4" oninput="oninputcheckValue4()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="question4_4" style="font-size:100%; background-color:red;">แดง</label>
                                                                </div>

                                                        </td>

                                                </tr>

                                                <!-- 5 -->
                                                <tr style="border:1px solid #000;margin: 45px;">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;5.มีพฤติกรรมหลบหนี</td>



                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                                <div class="custom-control custom-checkbox  col-sm-1">
                                                                        <input type="checkbox" <?php if ($row['question4_5'] == '5') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="question4_5" value="5" name="question4_5" oninput="oninputcheckValue4()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="question4_5" style="font-size:100%; background-color:red;">แดง</label>
                                                                </div>

                                                        </td>

                                                </tr>
                                                <tr style="border:1px solid #000;margin: 45px;">
                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;<b>ความรุนแรงระดับสี</b><div class="col-sm-1" id="check_question4"></div></td>
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px; background-color: <?= htmlspecialchars($bg_color4) ?>; width:100%; colspan:5; display:flex; justify-content:center; align-items:center;">
                                                <span style="color: <?= htmlspecialchars($font_color) ?>; font-size: 20px;">E</span> 
                                                </td>
                                                
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
                                                                                <a href="mental-health3-pdf.php?an=<?php echo $an; ?>&id=<?=$ids?>&loginname=<?php echo $loginname; ?>" target="_blank" class="btn btn-secondary"><i class="fas fa-file-pdf"></i> Print <U>PDF</U> File</a>
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



                                                function oninputcheckValue1() {
                                                        let question1_1 = $('input[name="question1_1"]:checked').val()?? 0; //0 if nul
                                                        let question1_2 = $('input[name="question1_2"]:checked').val()?? 0; //0 if nul
                                                        let question1_3 = $('input[name="question1_3"]:checked').val()?? 0; //0 if nul
                                                        let question1_4 = $('input[name="question1_4"]:checked').val()?? 0; //0 if nul
                                                        let question1_5 = $('input[name="question1_5"]:checked').val()?? 0; //0 if nul
                                                        let sum_score = (parseFloat(question1_1)+parseFloat(question1_2)+parseFloat(question1_3)+parseFloat(question1_4)+parseFloat(question1_5))
                                                        display_scoreyellow(sum_score, "check_question");                                                        
                                                }


                                                function oninputcheckValue2() {
                                                let question2_1 = $('input[name="question2_1"]:checked').val()?? 0; //0 if nul
                                                let question2_2 = $('input[name="question2_2"]:checked').val()?? 0; //0 if nul
                                                let question2_3 = $('input[name="question2_3"]:checked').val()?? 0; //0 if nul
                                                let question2_4 = $('input[name="question2_4"]:checked').val()?? 0; //0 if nul
                                                let question2_5 = $('input[name="question2_5"]:checked').val()?? 0; //0 if nul
                                                let sum_score = (parseFloat(question2_1)+parseFloat(question2_2)+parseFloat(question2_3)+parseFloat(question2_4)+parseFloat(question2_5))
                                                display_scoreyellow(sum_score, "check_question2");
                                                }

                                                function oninputcheckValue3() {
                                                        let question3_1 = $('input[name="question3_1"]:checked').val()?? 0; //0 if nul
                                                        let question3_2 = $('input[name="question3_2"]:checked').val()?? 0; //0 if nul
                                                        let question3_3 = $('input[name="question3_3"]:checked').val()?? 0; //0 if nul
                                                        let question3_4 = $('input[name="question3_4"]:checked').val()?? 0; //0 if nul
                                                        let question3_5 = $('input[name="question3_5"]:checked').val()?? 0; //0 if nul
                                                        let sum_score = (parseFloat(question3_1)+parseFloat(question3_2)+parseFloat(question3_3)+parseFloat(question3_4)+parseFloat(question3_5))
                                                        display_scoreyellow(sum_score, "check_question3");
                                                        }



                                                        function oninputcheckValue4() {
                                                        let question4_1 = $('input[name="question4_1"]:checked').val()?? 0; //0 if nul
                                                        let question4_2 = $('input[name="question4_2"]:checked').val()?? 0; //0 if nul
                                                        let question4_3 = $('input[name="question4_3"]:checked').val()?? 0; //0 if nul
                                                        let question4_4 = $('input[name="question4_4"]:checked').val()?? 0; //0 if nul
                                                        let question4_5 = $('input[name="question4_5"]:checked').val()?? 0; //0 if nul

                                                        let sum_score = (parseFloat(question4_1)+parseFloat(question4_2)+parseFloat(question4_3)+parseFloat(question4_4)+parseFloat(question4_5))
                                                        display_scoreyellow(sum_score, "check_question4");
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

                                                                var level_of_consciousness = $('input[name="level_of_consciousness"]:checked').val();

                                                               if (level_of_consciousness == undefined) {
                                                                        $('[name="level_of_consciousness"]').focus();
                                                                        //alert(depart)
                                                                        alert('level_of_consciousness');
                                                                }


                                                                var url_update = "form-mental-health31-update.php";
                                                                var url_save = "form-mental-health31-save.php";
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