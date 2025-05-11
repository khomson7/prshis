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


$permissionCheck = Session::checkPermissionAndShowMessage('PRS_FORM_BEDSORES', 'VIEW');
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
        'form' => 'BEDSORES-FORM',
        'an' => $an,
), JSON_UNESCAPED_UNICODE));


//echo $ids;

//----------------------เช็คว่า an นี้ มีข้อมูลหรือไม่




$sql = "SELECT *
                FROM `prs_bedsores`
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


$_id = '23'; //Link menu

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

                                <div><b>กลุ่มเป้าหมาย</b> ผู้ป่วยทุกรายที่เข้าพักการรักษาตัวโรงพยาบาล ยกเว้น ผู้ป่วยแผนกกุมารเวชกรรม สูติกรรม (ห้องคลอด,หลังคลอด) ต้องได้รับการประเมินคะแนนความเสี่ยงต่อการเกิดแผลกดทับตามแบบ Braden Scale โดยกำหนด ให้บันทึกค่าคะแนนความเสี่ยง<br />
                        ต่อการเกิดแผลในแบบประเมินสมรรถนะแรกรับหรือภายใน 24 ชั่วโมง<br />
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;คะแนนรวม  &le; 16 คะแนน จัดเป็นกลุ่มเสี่ยงต้องประเมินความเสี่ยงต่อการเกิดแผลทุกวันและได้รับการดูแลตามมาตรฐานที่กำหนด<br />
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;คะแนนรวม  &gt; 16 คะแนน ประเมินซ้ำเมื่อมีปัจจัยเสี่ยงอย่างใดอย่างหนึ่งใน 6 ปัจจัยลดลงอย่างน้อย 1 คะแนน<br />
                        ส่วนที่ 1 แบบประเมิน Braden scale &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ทำเครื่องหมาย / ในช่องและรวมคะแนน
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
                                                                                        } else if($id == null) {
                                                                                                echo $work_shift1_checked;
                                                                                        }?> class="custom-control-input" id="work_shift1" name="work_shift" value="1">
                                        <label class="custom-control-label" for="work_shift1" style="font-size:100%; background-color:yellow;"><strong>&nbsp;ดึก&nbsp;</strong></label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                        <input type="radio" <?php if ($row['work_shift'] == '2') {
                                                                                                echo 'checked="checked"';
                                                                                        } else if($id == null) {
                                                                                                echo $work_shift2_checked;
                                                                                        }?>  class="custom-control-input" id="work_shift2" name="work_shift" value="2">
                                        <label class="custom-control-label" for="work_shift2" style="font-size:100%; background-color:orange;"><strong>&nbsp;เช้า&nbsp;</strong></label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                        <input type="radio" <?php if ($row['work_shift'] == '3') {
                                                                                                echo 'checked="checked"';
                                                                                        } else if($id == null) {
                                                                                                echo $work_shift3_checked;
                                                                                        }?>  class="custom-control-input" id="work_shift3" name="work_shift" value="3">
                                        <label class="custom-control-label" for="work_shift3" style="font-size:100%; background-color:gray;"><strong>&nbsp;บ่าย&nbsp;</strong></label>
                                </div>

                                                        


                        </div>




                        <div class="row">

                        <div class="col-12 col-md-12">
                               

                                        <table lass="center" id="bg-table" width="100%" style="border-collapse: collapse;font-size:10pt;margin-top:8px;">
                                                <tr style="border:1px solid #000;margin: 45px;">

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="10%" colspan="2" >&nbsp;<b>คะแนน Braden Scale</b></td>
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;<b>คะแนน</b></td>



                                                </tr>

                                                <tr style="border:1px solid #000;margin: 45px;">
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="5%" rowspan="5">&nbsp;<b>การรับรู้</b></td>
                                                </tr>

                                                <tr style="border:1px solid #000;margin: 45px;">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;ปกติ</td>



                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                        <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['perception'] == '4') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="perception4" value="4" name="perception" oninput="perceptionCheckValue()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="perception4" style="font-size:100%;">4</label>
                                                                </div>

                                                        </td>





                                                </tr>

                                                <!-- 2 -->
                                                <tr style="border:1px solid #000;margin: 45px;">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;สับสน สื่อสารไม่ได้บางครั้ง</td>



                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                        <div class="custom-control custom-radio col-sm-1">
                                                                      <input type="radio" <?php if ($row['perception'] == '3') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="perception3" value="3" name="perception" oninput="perceptionCheckValue()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="perception3" style="font-size:100%;">3</label>
                                                                </div>

                                                        </td>

                                                </tr>

                                                <!-- 3 -->
                                                <tr style="border:1px solid #000;margin: 45px;">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;ตอบสนองความเจ็บปวด</td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                        <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['perception'] == '2') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="perception2" value="2" name="perception" oninput="perceptionCheckValue()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="perception2" style="font-size:100%;">2</label>
                                                                </div>

                                                        </td>

                                                </tr>

                                                <!-- 4 -->
                                                <tr style="border:1px solid #000;margin: 45px;">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;ไม่ตอบสนอง</td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                        <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['perception'] == '1') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="perception1" value="1" name="perception" oninput="perceptionCheckValue()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="perception1" style="font-size:100%;">1</label>
                                                                </div>

                                                        </td>

                                                </tr>



                                                <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="5%" rowspan="5">&nbsp;<b>การเปียกชุ่มของผิวหนัง</b></td>
                                                </tr>

                                                <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;ปกติ</td>



                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                        <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['wetting_the_skin'] == '4') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="wetting_the_skin4" value="4" name="wetting_the_skin" oninput="WettingTheSkinCheckValue()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="wetting_the_skin4" style="font-size:100%;">4</label>
                                                                </div>

                                                        </td>





                                                </tr>

                                                
                                                <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;เปียกชุ่มบางครั้ง</td>



                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                        <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['wetting_the_skin'] == '3') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="wetting_the_skin3" value="3" name="wetting_the_skin" oninput="WettingTheSkinCheckValue()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="wetting_the_skin3" style="font-size:100%;">3</label>
                                                                </div>

                                                        </td>

                                                </tr>

                                                
                                                <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;เปียกชุ่มบ่อยครั้ง</td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                        <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['wetting_the_skin'] == '2') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="wetting_the_skin2" value="2" name="wetting_the_skin" oninput="WettingTheSkinCheckValue()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="wetting_the_skin2" style="font-size:100%;">2</label>
                                                                </div>

                                                        </td>

                                                </tr>

                                                
                                                <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;เปียกชุ่มตลอดเวลา</td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                        <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['wetting_the_skin'] == '1') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="wetting_the_skin1" value="1" name="wetting_the_skin" oninput="WettingTheSkinCheckValue()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="wetting_the_skin1" style="font-size:100%;">1</label>
                                                                </div>

                                                        </td>

                                                </tr>

                                               
                                                <tr style="border:1px solid #000;margin: 45px;">
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="5%" rowspan="5">&nbsp;<b>การทำกิจกรรม</b></td>
                                                </tr>

                                                <tr style="border:1px solid #000;margin: 45px;">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;ปกติ</td>



                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                        <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['doing_activities'] == '4') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="doing_activities4" value="4" name="doing_activities" oninput="doingCheckValue()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="doing_activities4" style="font-size:100%;">4</label>
                                                                </div>

                                                        </td>





                                                </tr>

                                                
                                                <tr style="border:1px solid #000;margin: 45px;">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;เดินได้ระยะสั้น/ต้องพยุง</td>



                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                        <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['doing_activities'] == '3') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="doing_activities3" value="3" name="doing_activities" oninput="doingCheckValue()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="doing_activities3" style="font-size:100%;">3</label>
                                                                </div>

                                                        </td>

                                                </tr>

                                               
                                                <tr style="border:1px solid #000;margin: 45px;">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;ทรงตัวไม่อยู่ ใช้รถเข็น</td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                        <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['doing_activities'] == '2') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="doing_activities2" value="2" name="doing_activities" oninput="doingCheckValue()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="doing_activities2" style="font-size:100%;">2</label>
                                                                </div>

                                                        </td>

                                                </tr>

                                               
                                                <tr style="border:1px solid #000;margin: 45px;">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;อยู่บนเตียงตลอดเวลา</td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                        <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['doing_activities'] == '1') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="doing_activities1" value="1" name="doing_activities" oninput="doingCheckValue()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="doing_activities1" style="font-size:100%;">1</label>
                                                                </div>

                                                        </td>

                                                </tr>


                                            

                                                <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="5%" rowspan="5">&nbsp;<b>การเคลื่อนไหว</b></td>
                                                </tr>

                                                <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;ปกติ</td>



                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                        <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['movement'] == '4') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="movement4" value="4" name="movement" oninput="movementCheckValue()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="movement4" style="font-size:100%;">4</label>
                                                                </div>

                                                        </td>

                                                </tr>

                                                
                                                <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;เปลี่ยนท่าได้บ่อยครั้ง</td>



                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                        <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['movement'] == '3') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="movement3" value="3" name="movement" oninput="movementCheckValue()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="movement3" style="font-size:100%;">3</label>
                                                                </div>

                                                        </td>

                                                </tr>

                                               
                                                <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;เปลี่ยนท่าได้บางครั้ง</td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                        <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['movement'] == '2') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="movement2" value="2" name="movement" oninput="movementCheckValue()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="movement2" style="font-size:100%;">2</label>
                                                                </div>

                                                        </td>

                                                </tr>

                                                
                                                <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;เปลี่ยนท่าไม่ได้</td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                        <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['movement'] == '1') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="movement1" value="1" name="movement" oninput="movementCheckValue()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="movement1" style="font-size:100%;">1</label>
                                                                </div>

                                                        </td>

                                                </tr>


                                                <tr style="border:1px solid #000;margin: 45px;">
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="5%" rowspan="5">&nbsp;<b>การได้รับอาหาร</b></td>
                                                </tr>

                                                <tr style="border:1px solid #000;margin: 45px;">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;ปกติ</td>



                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                        <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['getting_food'] == '4') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="getting_food4" value="4" name="getting_food" oninput="foodcheckValue()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="getting_food4" style="font-size:100%;">4</label>
                                                                </div>

                                                        </td>





                                                </tr>

                                                
                                                <tr style="border:1px solid #000;margin: 45px;">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Feed ได้หมด/กินได้ &gt; 1/2 ถาด</td>



                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                        <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['getting_food'] == '3') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="getting_food3" value="3" name="getting_food" oninput="foodcheckValue()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="getting_food3" style="font-size:100%;">3</label>
                                                                </div>

                                                        </td>

                                                </tr>

                                                
                                                <tr style="border:1px solid #000;margin: 45px;">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Feed ได้บ้าง/กินได้ 1/2 ถาด</td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                        <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['getting_food'] == '2') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="getting_food2" value="2" name="getting_food" oninput="foodcheckValue()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="getting_food2" style="font-size:100%;">2</label>
                                                                </div>
                                                        </td>

                                                </tr>

                                                
                                                <tr style="border:1px solid #000;margin: 45px;">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;NPO / กินได้ &lt; 1/3 ถาด</td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                        <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['getting_food'] == '1') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="getting_food1" value="1" name="getting_food" oninput="foodcheckValue()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="getting_food1" style="font-size:100%;">1</label>
                                                                </div>

                                                        </td>

                                                </tr>




                                                <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="5%" rowspan="4">&nbsp;<b>การเสียดสี</b></td>
                                                </tr>

                                                <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;ไม่มีปัญหา</td>



                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                        <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['sarcasm'] == '3') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="sarcasm3" value="3" name="sarcasm" oninput="sarcasmcheckValue()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="sarcasm3" style="font-size:100%;">3</label>
                                                                </div>
                                                        </td>





                                                </tr>

                                                
                                                <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;เสียดสี / ลื่นไถลได้</td>



                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                        <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['sarcasm'] == '2') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="sarcasm2" value="2" name="sarcasm" oninput="sarcasmcheckValue()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="sarcasm2" style="font-size:100%;">2</label>
                                                                </div>

                                                        </td>

                                                </tr>

                                                
                                                <tr style="border:1px solid #000;margin: 45px; background-color:#d4fbfb">

                                                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;กล้ามเนื้อหดเกร็ง</td>

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">
                                                        <div class="custom-control custom-radio col-sm-1">
                                                                        <input type="radio" <?php if ($row['sarcasm'] == '1') {
                                                                                                        echo 'checked="checked"';
                                                                                                } ?>class="custom-control-input" id="sarcasm1" value="1" name="sarcasm" oninput="sarcasmcheckValue()">
                                                                        <label class="custom-control-label badge text-red mt-1 font-weight-bold" for="sarcasm1" style="font-size:100%;">1</label>
                                                                </div>

                                                        </td>

                                                </tr>

                                                <tr style="border:1px solid #000;margin: 45px;">
                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="5%" rowspan="2">&nbsp;<b>คะแนน</b></td>
                                                </tr>

                                                <tr style="border:1px solid #000;margin: 45px;">

                                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;( 5 - 23 คะแนน)</td>



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
                                                                              <!--  <a href="mental-health3-pdf.php?an=<?php echo $an; ?>&id=<?=$ids?>&loginname=<?php echo $loginname; ?>" target="_blank" class="btn btn-secondary"><i class="fas fa-file-pdf"></i> Print <U>PDF</U> File</a> -->
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



                                                function perceptionCheckValue() {
                                                        let perception4 = $('input[name="perception4"]:checked').val()?? 0; //0 if nul
                                                        let perception3 = $('input[name="perception3"]:checked').val()?? 0; //0 if nul
                                                        let perception2 = $('input[name="perception2"]:checked').val()?? 0; //0 if nul
                                                        let perception1 = $('input[name="perception1"]:checked').val()?? 0; //0 if nul
                                                        let sum_score = (parseFloat(perception4)+parseFloat(perception3)+parseFloat(perception2)+parseFloat(perception1))
                                                        display_scoreyellow(sum_score, "sum_perception");                                                        
                                                }


                                                function WettingTheSkinCheckValue() {
                                                        let wetting_the_skin4 = $('input[name="wetting_the_skin4"]:checked').val()?? 0; //0 if nul
                                                        let wetting_the_skin3 = $('input[name="wetting_the_skin3"]:checked').val()?? 0; //0 if nul
                                                        let wetting_the_skin2 = $('input[name="wetting_the_skin2"]:checked').val()?? 0; //0 if nul
                                                        let wetting_the_skin1 = $('input[name="wetting_the_skin1"]:checked').val()?? 0; //0 if nul                                                 
                                                       // display_scoreyellow(sum_score, "sum_perception");                                                        
                                                }

                                                function doingCheckValue() {
                                                        let doing_activities4 = $('input[name="doing_activities4"]:checked').val()?? 0; //0 if nul
                                                        let doing_activities3 = $('input[name="doing_activities3"]:checked').val()?? 0; //0 if nul
                                                        let doing_activities2 = $('input[name="doing_activities2"]:checked').val()?? 0; //0 if nul
                                                        let doing_activities1 = $('input[name="doing_activities1"]:checked').val()?? 0; //0 if nul
                                                }

                                                function movementCheckValue() {
                                                        let movement4 = $('input[name="movement4"]:checked').val()?? 0; //0 if nul
                                                        let movement3 = $('input[name="movement3"]:checked').val()?? 0; //0 if nul
                                                        let movement2 = $('input[name="movement2"]:checked').val()?? 0; //0 if nul
                                                        let movement1 = $('input[name="movement1"]:checked').val()?? 0; //0 if nul
                                                }

                                                function foodCheckValue() {
                                                        let getting_food4 = $('input[name="getting_food4"]:checked').val()?? 0; //0 if nul
                                                        let getting_foodt3 = $('input[name="getting_food3"]:checked').val()?? 0; //0 if nul
                                                        let getting_food2 = $('input[name="getting_food2"]:checked').val()?? 0; //0 if nul
                                                        let getting_food1 = $('input[name="getting_food1"]:checked').val()?? 0; //0 if nul
                                                }


                                                function sarcasmcheckValue() {

                                                        let sarcasm3 = $('input[name="sarcasm3"]:checked').val()?? 0; //0 if nul
                                                        let sarcasm2 = $('input[name="sarcasm2"]:checked').val()?? 0; //0 if nul
                                                        let sarcasm1 = $('input[name="sarcasm1"]:checked').val()?? 0; //0 if nul

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


                                                                var url_update = "form-bedscore-update.php";
                                                                var url_save = "form-bedscore-save.php";
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
                                                                                         }else{
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