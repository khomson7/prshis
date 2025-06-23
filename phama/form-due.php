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

//Session::checkLoginSessionAndShowMessage(); //เช็ค session

$permissionCheck = Session::checkPermissionAndShowMessage('PRS_FORM_DUE', 'VIEW');
$permissionCheckJson = json_encode($permissionCheck);

//$mylink = DbConstant::MAIN_LINK;


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

//echo $id;


Session::insertSystemAccessLog(json_encode(array(
    'form' => 'DUE-FORM',
    'an' => $an,
), JSON_UNESCAPED_UNICODE));




//----------------------เช็คว่า an นี้ มีข้อมูลหรือไม่

$sql = "SELECT pdc.*,d.name as dname,d2.name as drugname
                FROM ".DbConstant::KPHIS_DBNAME.".prs_due_check pdc
                LEFT JOIN ".DbConstant::HOSXP_DBNAME.".doctor d on d.code = pdc.physician_approved
                LEFT JOIN ".DbConstant::HOSXP_DBNAME.".drugitems d2 on d2.icode = pdc.icode
                WHERE pdc.an = :an and pdc.id = :id";
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

//echo $id;

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


if ($id == null || $id != null) {
                            $sql_cr = "select lo.lab_order_result as creatinine  
                                    FROM " . DbConstant::HOSXP_DBNAME . ".lab_head lh
                                    INNER JOIN " . DbConstant::HOSXP_DBNAME . ".lab_order lo on lo.lab_order_number = lh.lab_order_number
                                    WHERE lh.vn= :an and lo.lab_items_code in('1555')
                                    ORDER BY lo.lab_order_number desc limit 1";
    $stmt_cr = $conn->prepare($sql_cr);
    $stmt_cr->execute(['an' => $an]);
    $row_cr  = $stmt_cr->fetch();
}



if ($id == null || $id != null) {
    $sql_cr = "select lo.lab_order_result as creatinine  
            FROM " . DbConstant::HOSXP_DBNAME . ".lab_head lh
            INNER JOIN " . DbConstant::HOSXP_DBNAME . ".lab_order lo on lo.lab_order_number = lh.lab_order_number
            WHERE lh.vn= :an and lo.lab_items_code in('1555')
            ORDER BY lo.lab_order_number desc limit 1";
$stmt_cr = $conn->prepare($sql_cr);
$stmt_cr->execute(['an' => $an]);
$row_cr  = $stmt_cr->fetch();
}


if ($id == null || $id != null) {
    $sql_egfr = "select lo.lab_order_result as egfr  
            FROM " . DbConstant::HOSXP_DBNAME . ".lab_head lh
            INNER JOIN " . DbConstant::HOSXP_DBNAME . ".lab_order lo on lo.lab_order_number = lh.lab_order_number
            WHERE lh.vn= :an and lo.lab_items_code in('2457')
            ORDER BY lo.lab_order_number desc limit 1";
$stmt_egfr = $conn->prepare($sql_egfr);
$stmt_egfr->execute(['an' => $an]);
$row_egfr  = $stmt_egfr->fetch();
}

if ($id == null || $id != null ) {
    $sql_bun = "select lo.lab_order_result as  bun_lab
            FROM " . DbConstant::HOSXP_DBNAME . ".lab_head lh
            INNER JOIN " . DbConstant::HOSXP_DBNAME . ".lab_order lo on lo.lab_order_number = lh.lab_order_number
            WHERE lh.vn= :an and lo.lab_items_code in('1553')
            ORDER BY lo.lab_order_number desc limit 1";
$stmt_bun = $conn->prepare($sql_bun);
$stmt_bun->execute(['an' => $an]);
$row_bun  = $stmt_bun->fetch();
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


$sql_due_drug = "select patient.sex,patient.hn,patient.pname,patient.fname,patient.lname,/*patient.drugallergy, */
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
$stmt_due_drug = $conn->prepare($sql_due_drug);
$stmt_due_drug->execute(['an' => $an]);
$row_due_drug = $stmt_due_drug->fetch();

//$regdatetime = $row_ipt["regdatetime"];


//----------------------เช็คว่า an นี้ มีข้อมูลหรือไม่

date_default_timezone_set('asia/bangkok');

$id = '24'; //Link menu
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
    .tooltip-inner {
    white-space: pre;
    max-width: none;
    text-align: left;
}

.select2-container .select2-selection--single {
  height: 38px; /* match your input height */
}
</style>


<div id="formContainer">
    <form id="my_form">
        <div class="container-fluid">
            <div class="row">
                <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-primary btn-block" onclick="window.close()"><i class="fas fa-arrow-left"></i> กลับ</button>
                </div>
                <div class="col-auto p-1 font-weight-bold">
                    <h5><B>แบบประเมิน DUE <?= htmlspecialchars(DbConstant::HOSPITAL_NAME) ?>
                            <?php if ($check_ == "1") { ?>

                                <font color="red">ช่วงทดลอง</font>
                            <?php } else { ?>

                            <? } ?>
                        </B></h5>
                </div>

            </div>


            <div class="card-group pb-3 ">
                <div class="card">
                    <div class="card-body" style=" overflow-y: auto;">
                        <div class="row">
                            <div class="col-md-12">


                                <div class="form-group row alert alert-dark text-left">
                                    <B>ส่วนที่ 1</B>
                                </div>



                                <div class="row">

                                    &nbsp;&nbsp;&nbsp;&nbsp;<label>BUN:&nbsp;</label>
                                    <div>
                                    
                                        <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="เฉพาะตัวเลข" name="bun_lab" id="bun_lab" value="<?= (isset($row_bun['bun_lab'])  ? htmlspecialchars(round(($row_bun['bun_lab']), 2)) : htmlspecialchars(round(($row['bun_lab']), 2))) ?>" readonly>
                                    </div>&nbsp;mg/dL&nbsp; Creatinine:&nbsp;<div>
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="เฉพาะตัวเลข" name="creatinine" id="creatinine" value="<?= (isset($row_cr['creatinine'])  ? htmlspecialchars(round(($row_cr['creatinine']), 2)) : htmlspecialchars(round(($row['creatinine']), 2))) ?>" readonly>
                                    </div>&nbsp;mg/dL&nbsp; eGFR:&nbsp;<div>
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="เฉพาะตัวเลข" name="egfr" id="egfr" value="<?= (isset($row_egfr['egfr'])  ? htmlspecialchars(round(($row_egfr['egfr']), 2)) : htmlspecialchars(round(($row['egfr']), 2))) ?>" readonly>
                                    </div>
                                    
                                    <label>&nbsp;mL/min/1.73 sqM.&nbsp;อายุ:&nbsp;</label>
                                    <div>
                                    
                                        <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="อายุ" name="age" id="age" value="<?= (isset($row_ipt['age_y'])  ? htmlspecialchars(round(($row_ipt['age_y']), 1)) : htmlspecialchars(round(($row['age']), 1))) ?>" readonly>
                                    </div>
                                    <label>&nbsp;ปี&nbsp;BW:&nbsp;</label>
                                    <div>
                                    
                                        <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="bw" name="bw" id="bw" value="<?= (isset($row_opdscreen['bw'])  ? htmlspecialchars(round(($row_opdscreen['bw']), 1)) : htmlspecialchars(round(($row['bw']), 1))) ?>" readonly>
                                    </div><label>&nbsp;Kg&nbsp;</label>
                                </div>
                                <br>
                                <div class="row">
                                    <label>&nbsp;&nbsp;&nbsp;Gender:&nbsp;</label>
                                    &nbsp;&nbsp;
                                    <div class="custom-control custom-radio col-sm-1">
                                        <input type="radio" <?php if ($row_ipt['sex'] == '1' || $row['sex'] == '1') {
                                                                echo 'checked="checked"';
                                                            } ?> class="custom-control-input" id="sex1" name="sex" value="<?= (isset($row_opdscreen['sex'])  ? htmlspecialchars($row_opdscreen['sex']) : htmlspecialchars($row['sex'])) ?>" onchange="sex_check('off_checked');">
                                        <label class="custom-control-label" for="sex1">ชาย</label>
                                    </div>

                                    <div class="custom-control custom-radio col-sm-1">
                                        <input type="radio" <?php if ($row_ipt['sex'] == '2' || $row['sex'] == '2') {
                                                                echo 'checked="checked"';
                                                            } ?> class="custom-control-input" id="sex2" name="sex" value="<?= (isset($row_opdscreen['sex'])  ? htmlspecialchars($row_opdscreen['sex']) : htmlspecialchars($row['sex'])) ?>" onchange="sex_check('on_checked');">
                                        <label class="custom-control-label" for="sex2">หญิง</label>
                                    </div>
                                    CrCl:&nbsp;<div>
                                        <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="CrCl" name="crcl" id="crcl" value="<?= (isset($row['crcl']) ? htmlspecialchars(round(($row['crcl']), 2)) : '') ?>" readonly>
                                        
                                    </div>&nbsp;<div class="input-group-append">
                                            
                                        </div>&nbsp;mL/min (Cockcroft-Gault)&nbsp;&nbsp; <button class="btn btn-secondary" type="button" onclick="onclick_crcl_calculate_button(event)"><i class="fas fa-calculator"></i></button>
                                        &nbsp;<font color="red">(กรุณากดปุ่มเพื่อคำนวณ)</font>

                                </div>
                                <br>


                                <div class="form-group row alert alert-dark text-left">
                                    <B>ส่วนที่ 2</B>
                                </div>



                                <div class="row">

                                    &nbsp;&nbsp;&nbsp;&nbsp;<label>ผลการวินิจฉัย:&nbsp;</label>
                                    <div class="col-sm-5">
                                        <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="" name="diagnosis" id="diagnosis" value="<?= (isset($row['diagnosis']) ? htmlspecialchars($row['diagnosis']) : '') ?>">
                                    </div>
                                </div>
                                <br>

                                <div class="row">

                                    &nbsp;&nbsp;&nbsp;&nbsp;<label>ตำแหน่งที่ติดเชื้อ:&nbsp;</label>
                                    <div class="col-sm-5">
                                        <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="" name="infected_location" id="infected_location" value="<?= (isset($row['infected_location']) ? htmlspecialchars($row['infected_location']) : '') ?>">
                                    </div>
                                </div>
                                <br>

                                <div class="row">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<label class="col-form-label" for="specimen">สิ่งส่งตรวจ:&nbsp;</label>
                                    <div class="col-sm-5">
                                    <select class="form-control" id="specimen" name="specimen">
                                        <option value=""></option>
                                    </select>
                                    </div>
                                </div>

                                <br>

                                <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;<label>เชื้อก่อโรคที่พบ:&nbsp;</label>
                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="" name="found_pathogens" id="found_pathogens" value="<?= (isset($row['found_pathogens']) ? htmlspecialchars($row['found_pathogens']) : '') ?>">
                                </div>
                                </div>
                                <br>

                                <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;<label class="col-form-label" >ข้อบ่งชี้:&nbsp;</label>
                                <div class="col-sm-3">
                                <select class="form-control" name="indications" id="indications" required>
                <option value="">-- กรุณาเลือก --</option>
                <option value="Empirical" <?= (isset($row['indications']) && $row['indications'] == 'Empirical') ? 'selected' : '' ?>>Empirical</option>
                <option value="Document" <?= (isset($row['indications']) && $row['indications'] == 'Document') ? 'selected' : '' ?>>Document</option>
            </select>
                                </div>
                                </div>
                                <br>

                                

                                <div class="row">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<label class="col-form-label" for="icode">ยาที่สั่งใช้:&nbsp;</label>
                                    <div class="col-sm-5">
                                    <select class="form-control" id="icode" name="icode">
                                        <option value=""></option>
                                    </select>
                                    </div>
                                </div>
                   
                                <br>

                                <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;<label>วันที่เริ่มยา:&nbsp;</label>
                                <div class="col-sm-2">
                                    <input type="date" class="form-control form-control-sm" id="start_medication" name="start_medication" value="<?= (isset($row['start_medication']) ? htmlspecialchars($row['start_medication']) : '') ?>">
                                </div>
                                </div>
                                <br>

                                <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;<label class="col-form-label" for="physician_approved">แพทย์ผู้อนุมัติการสั่งใช้:&nbsp;</label>
                                <div class="col-sm-5">
                                <select class="form-control" id="physician_approved" name="physician_approved">
                                        <option value=""></option>
                                    </select>
                                    
                                </div>
                                </div>
                                <br>

                            </div>


                            <div class="row">
                                <div id="show_check_save"></div>
                                <input type="hidden" id="an" name="an" value="<?= htmlspecialchars($an) ?>">
                                <input type="hidden" id="hn" name="hn" value="<?= htmlspecialchars($hn) ?>">
                                <input type="hidden" id="version" name="version" value="<?= htmlspecialchars($row['version']) ?>">
                                <input type="hidden" id="id" name="id" value="<?= htmlspecialchars($row['id']) ?>">
                                <input type="hidden" id="create_user" name="create_user" value="<?= htmlspecialchars($_SESSION['name']) ?>">

                                <div class="col-md-12 text-right">
                                    <?php
                                    if ((
                                        Session::checkPermission('PRS_FORM_DUE', 'ADD')
                                    ) && (ReportQueryUtils::checkReadOnly($an))) { ?>
                                        <button type="button" class="btn btn-primary" id="btn_due" onclick="form_save()"><i class="fas fa-save"></i> บันทึก</button>
                                    <?php } ?>
                                    <?php
                                    if ($id != '') { ?>
                                  <!--  <a href="prs-icu1-pdf.php?an=<?php echo $an; ?>&id=<?php echo $ids; ?>&loginname=<?php echo $loginname; ?>" target="_blank" class="btn btn-secondary"><i class="fas fa-file-pdf"></i> Print <U>PDF</U> File</a>
                                    --> 
                                      <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>

                    <script src="../include/my_function.js"></script>
                    <script>

function onclick_crcl_calculate_button(event){
        var sex = $('#sex1').val();
        var age = $('#age').val();
       // alert(sex);
        var bw = $('#bw').val();
        var creatinine = $('#creatinine').val();
        var creatinine2 = (72 * creatinine);

        var crcl_2 = roundNumber(((140 - age) * bw * 0.85)/creatinine2,2);

        if(sex == 1) {
            var crcl = roundNumber(((140 - age) * bw )/creatinine2,2);
        }else {
            var crcl = crcl_2;
        }
        

        $('#crcl').val(Number.isNaN(crcl) ? '':crcl);

    }

$( document ).ready(function() {
        const queryString = window.location.search;
        const urlParams = new URLSearchParams(queryString);
        const an = urlParams.get('an');

        initSpecimenSelect2();
        initDrugSelect2();
        initphysician_approvedSelect2();

        $('[data-toggle="tooltip"]').tooltip();


})


function initSpecimenSelect2(){


        $('#specimen').select2({
            allowClear: true,
            placeholder: "",
            minimumInputLength: 1,
            ajax: {
                url: './specimen-data.php',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    var query = {
                        search: params.term,
                        page: params.page || 1
                    }

                    // Query parameters will be ?search=[term]&type=public
                    return query;
                }
            },
            templateResult: function (result) {
                // console.log('result',result);
                let html_result = $('<span/>',{
                    html:[
                        $('<span/>',{
                            text: result.text,
                        }),
                        ((isBlankOrNullOrWhiteSpace(result.addrname)) ?
                        '' : $('<div/>')),
                    ],
                });

                return html_result;
            },
        });
        //$('#specimen').empty().trigger('change');
    }

    function initDrugSelect2(){


$('#icode').select2({
    allowClear: true,
    placeholder: "",
    minimumInputLength: 1,
    ajax: {
        url: './selected-drug-data.php',
        dataType: 'json',
        delay: 250,
        data: function (params) {
            var query = {
                search: params.term,
                page: params.page || 1
            }

            // Query parameters will be ?search=[term]&type=public
            return query;
        }
    },
    templateResult: function (result) {
                // console.log('result',result);
                let html_result = $('<span/>',{
                    html:[
                        $('<span/>',{
                            text: result.text,
                        }),
                        ((isBlankOrNullOrWhiteSpace(result.addrname)) ?
                        '' : $('<div/>',{
                            html: [
                                $('<small/>',{
                                    text: result.addrname,
                                }),
                            ]
                        })),
                    ],
                });

                return html_result;
            },
});
//$('#specimen').empty().trigger('change');
}


function initphysician_approvedSelect2(){


$('#physician_approved').select2({
    allowClear: true,
    placeholder: "",
    minimumInputLength: 1,
    ajax: {
        url: './selected-doctor-data.php',
        dataType: 'json',
        delay: 250,
        data: function (params) {
            var query = {
                search: params.term,
                page: params.page || 1
            }

            // Query parameters will be ?search=[term]&type=public
            return query;
        }
    },
    templateResult: function (result) {
        // console.log('result',result);
        let html_result = $('<span/>',{
            html:[
                $('<span/>',{
                    text: result.text,
                }),
                ((isBlankOrNullOrWhiteSpace(result.addrname)) ?
                '' : $('<div/>')),
            ],
        });

        return html_result;
    },
});
//$('#specimen').empty().trigger('change');
}




                        //ควบคุมปุ่ม
                        function custom_check(value) {

                            if (value == "off_heart_disease_history") {
                                $('#heart_disease_history_text').attr("disabled", true).val('');
                                $('#heart_disease_history2').prop("checked", false);
                                // $("#check_1").attr("class","text-success fas fa-check-square");
                            } else if (value == "on_heart_disease_history") {
                                $('#heart_disease_history_text').attr("disabled", false).val('');
                                $('#heart_disease_history1').prop("checked", false);
                                //  $('#entered_by2').prop("checked", false);
                            }

                            if (value == "off_skin") {
                                $('#skin_text').attr("disabled", true).val('');
                                $('#skin6').prop("checked", false);
                                // $("#check_1").attr("class","text-success fas fa-check-square");
                            } else if (value == "on_skin") {
                                $('#skin_text').attr("disabled", false).val('');
                                $('#skin1').prop("checked", false);
                                $('#skin2').prop("checked", false);
                                $('#skin3').prop("checked", false);
                                $('#skin4').prop("checked", false);
                                $('#skin5').prop("checked", false);
                                //  $('#entered_by2').prop("checked", false);
                            }

                            if (value == "off_neck_vien_engorement") {
                                $('#neck_vien_engorement_text').attr("disabled", true).val('');
                                $('#neck_vien_engorement3').prop("checked", false);
                                // $("#check_1").attr("class","text-success fas fa-check-square");
                            } else if (value == "on_neck_vien_engorement") {
                                $('#neck_vien_engorement_text').attr("disabled", false).val('');
                                $('#neck_vien_engorement1').prop("checked", false);
                                $('#neck_vien_engorement2').prop("checked", false);

                            }

                            if (value == "off_kidney_disease_history") {
                                $('#kidney_disease_history_text').attr("disabled", true).val('');
                                $('#kidney_disease_history2').prop("checked", false);
                                // $("#check_1").attr("class","text-success fas fa-check-square");
                            } else if (value == "on_kidney_disease_history") {
                                $('#kidney_disease_history_text').attr("disabled", false).val('');
                                $('#kidney_disease_history1').prop("checked", false);
                                //  $('#entered_by2').prop("checked", false);
                            }

                            if (value == "off_history_of_lung_disease") {
                                $('#history_of_lung_disease_text').attr("disabled", true).val('');
                                $('#history_of_lung_disease2').prop("checked", false);

                            } else if (value == "on_history_of_lung_disease") {
                                $('#history_of_lung_disease_text').attr("disabled", false).val('');
                                $('#history_of_lung_disease1').prop("checked", false);

                            }

                            if (value == "off_on_icd") {
                                $('#on_icd_text').attr("disabled", true).val('');
                                $('#on_icd2').prop("checked", false);

                            } else if (value == "on_on_icd") {
                                $('#on_icd_text').attr("disabled", false).val('');
                                $('#on_icd1').prop("checked", false);

                            }

                            if (value == "off_history_of_gastrointestinal") {
                                $('#history_of_gastrointestinal_text').attr("disabled", true).val('');
                                $('#history_of_gastrointestinal2').prop("checked", false);

                            } else if (value == "on_history_of_gastrointestinal") {
                                $('#history_of_gastrointestinal_text').attr("disabled", false).val('');
                                $('#history_of_gastrointestinal1').prop("checked", false);

                            }

                            if (value == "off_communication_history") {
                                $('#communication_history_text').attr("disabled", true).val('');
                                $('#communication_history2').prop("checked", false);

                            } else if (value == "on_communication_history") {
                                $('#communication_history_text').attr("disabled", false).val('');
                                $('#communication_history1').prop("checked", false);

                            }

                            if (value == "off_speaking") {
                                $('#speaking_text').attr("disabled", true).val('');
                                $('#speaking4').prop("checked", false);

                            } else if (value == "on_speaking") {
                                $('#speaking_text').attr("disabled", false).val('');
                                $('#speaking1').prop("checked", false);
                                $('#speaking2').prop("checked", false);
                                $('#speaking3').prop("checked", false);

                            } else if (value == "on_speaking_reset") {
                                $('#speaking1').prop("checked", false);
                                $('#speaking2').prop("checked", false);
                                $('#speaking3').prop("checked", false);
                                $('#speaking4').prop("checked", false);
                                $('#speaking_text').attr("disabled", true).val('');

                            }

                            if (value == "off_communication") {
                                $('#communication_text').attr("disabled", true).val('');
                                $('#communication6').prop("checked", false);
                                // $("#check_1").attr("class","text-success fas fa-check-square");
                            } else if (value == "on_communication") {
                                $('#communication_text').attr("disabled", false).val('');
                                $('#communication1').prop("checked", false);
                                $('#communication2').prop("checked", false);
                                $('#communication3').prop("checked", false);
                                $('#communication4').prop("checked", false);
                                $('#communication5').prop("checked", false);
                                //  $('#entered_by2').prop("checked", false);
                            } else if (value == "on_communication_reset") {
                                $('#communication1').prop("checked", false);
                                $('#communication2').prop("checked", false);
                                $('#communication3').prop("checked", false);
                                $('#communication4').prop("checked", false);
                                $('#communication5').prop("checked", false);
                                $('#communication6').prop("checked", false);
                                $('#communication_text').attr("disabled", true).val('');

                            }

                            if (value == "off_vision") {
                                $('#vision_text').attr("disabled", true).val('');
                                $('#vision4').prop("checked", false);

                            } else if (value == "on_vision") {
                                $('#vision_text').attr("disabled", false).val('');
                                $('#vision1').prop("checked", false);
                                $('#vision2').prop("checked", false);
                                $('#vision3').prop("checked", false);

                            }

                            if (value == "off_history_affects_activities") {
                                $('#history_affects_activities_text').attr("disabled", true).val('');
                                $('#history_affects_activities2').prop("checked", false);

                            } else if (value == "on_history_affects_activities") {
                                $('#history_affects_activities_text').attr("disabled", false).val('');
                                $('#history_affects_activities1').prop("checked", false);

                            }

                            if (value == "on_fracture") {
                                $('#fracture_text').attr("disabled", false).val('');

                            }

                            if (value == "off_history_affects_stimulation") {
                                $('#history_affects_stimulation_text').attr("disabled", true).val('');
                                $('#history_affects_stimulation2').prop("checked", false);

                            } else if (value == "on_history_affects_stimulation") {
                                $('#history_affects_stimulation_text').attr("disabled", false).val('');
                                $('#history_affects_stimulation1').prop("checked", false);

                            }


                            if (value == "off_on_et") {
                                $('#et_tube_no_text').attr("disabled", false).val('');
                                $('#et_tube_no_text2').attr("disabled", false).val('');
                                $('#o2_hfnc_text').attr("disabled", true).val('');
                                $('#et_other1').prop("checked", true);

                            } else if (value == "off_on_tt") {
                                $('#et_tube_no_text').attr("disabled", false).val('');
                                $('#et_tube_no_text2').attr("disabled", true).val('');
                                $('#o2_hfnc_text').attr("disabled", true).val('');
                                $('#et_other2').prop("checked", true);

                            } else if (value == "off_on_ra") {
                                $('#et_tube_no_text').attr("disabled", true).val('');
                                $('#et_tube_no_text2').attr("disabled", true).val('');
                                $('#o2_hfnc_text').attr("disabled", true).val('');
                                $('#et_other6').prop("checked", true);

                            } else if (value == "off_on_o2h") {
                                $('#o2_hfnc_text').attr("disabled", false).val('');
                                $('#candular_text').attr("disabled", true).val('');
                                $('#mark_c_bag_text').attr("disabled", true).val('');
                                $('#et_tube_no_text').attr("disabled", true).val('');
                                $('#et_tube_no_text2').attr("disabled", true).val('');
                                $('#et_other3').prop("checked", true);

                            } else if (value == "off_on_candular") {
                                $('#candular_text').attr("disabled", false).val('');
                                $('#mark_c_bag_text').attr("disabled", true).val('');
                                $('#et_tube_no_text').attr("disabled", true).val('');
                                $('#et_tube_no_text2').attr("disabled", true).val('');
                                $('#o2_hfnc_text').attr("disabled", true).val('');
                                $('#et_other4').prop("checked", true);

                            } else if (value == "off_on_mark_c_bag") {
                                $('#mark_c_bag_text').attr("disabled", false).val('');
                                $('#et_tube_no_text').attr("disabled", true).val('');
                                $('#et_tube_no_text2').attr("disabled", true).val('');
                                $('#o2_hfnc_text').attr("disabled", true).val('');
                                $('#candular_text').attr("disabled", true).val('');
                                $('#et_other5').prop("checked", true);

                            }


                            if (value == "off_on_copt") {
                                $('#copt_text').attr("disabled", false).val('');
                                $('#nrs_text').attr("disabled", true).val('');
                                $('#pain_score1').prop("checked", true);

                            } else if (value == "off_on_nrs") {
                                $('#nrs_text').attr("disabled", false).val('');
                                $('#copt_text').attr("disabled", true).val('');
                                $('#pain_score2').prop("checked", true);

                            } else if (value == "on_pain_score_reset") {
                                $('#nrs_text').attr("disabled", true).val('');
                                $('#copt_text').attr("disabled", true).val('');
                                $('#pain_score1').prop("checked", false);
                                $('#pain_score2').prop("checked", false);
                                $('#pain_score0').prop("checked", false);

                            }



                        }

                        var specimen = <?= json_encode($row['specimen']) ?>;


                        //alert(specimen);

                        if(specimen != null){
                        $("#specimen").append(new Option(specimen, specimen, true, true));
                    }

                    var icode = <?= json_encode($row['icode']) ?>;
                    var drugname = <?= json_encode($row['drugname']) ?>;
                   

                    if(icode != null){
                        $("#icode").append(new Option(drugname, icode, true, true));
                    }
                    //alert(drugname);

                    var physician_approved = <?= json_encode($row['physician_approved']) ?>;
                    var dname = <?= json_encode($row['dname']) ?>;
                    //alert(dname);

                    if(physician_approved != null){
                        $("#physician_approved").append(new Option(dname, physician_approved, true, true));
                    }

                        function form_save() {

                           var diagnosis = $.trim($('[name="diagnosis"]').val());
                           var specimen = $.trim($('[name="specimen"]').val());
                           var indications = $.trim($('[name="indications"]').val());
                           var icode = $.trim($('[name="icode"]').val());
                           var physician_approved = $.trim($('[name="physician_approved"]').val());
                           // var rxtime = $.trim($('[name="rxtime"]').val());

                          /* if (diagnosis == "") {
                                $('[name="diagnosis"]').focus();
                                alert('กรุณาระบุสิ่งส่งตรวจ');
                            }

                           else*/ if (specimen == "") {
                                $('[name="specimen"]').focus();
                                alert('กรุณาระบุสิ่งส่งตรวจ');
                            }

                           else if (indications == "") {
                                $('[name="indications"]').focus();
                                alert('กรุณาระบุข้อบ่งชี้:');
                            } 
                            else if (icode == "") {
                                $('[name="icode"]').focus();
                                alert('กรุณาระบุข้อบ่งชี้:');
                            } else if (physician_approved == "") {
                                $('[name="physician_approved"]').focus();
                                alert('กรุณาระบุแพทย์ผู้อนุมัติการสั่งใช้:');
                            } /*else if (rxtime == "") {

                                $('[name="rxtime"]').focus();
                                alert('เลือกเวลา');
                            } */


                            var url_update = "form-due-update.php";
                            var url_save = "form-due-save.php";
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