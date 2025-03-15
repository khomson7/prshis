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

$permissionCheck = Session::checkPermissionAndShowMessage('PRS_FORM_ICU1', 'VIEW');
$permissionCheckJson = json_encode($permissionCheck);




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
    'form' => 'ICU1-FORM',
    'an' => $an,
), JSON_UNESCAPED_UNICODE));




//----------------------เช็คว่า an นี้ มีข้อมูลหรือไม่

$sql = "SELECT *
                FROM `prs_icu_form`
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

$id = '17'; //Link menu
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
        <div class="row">
            <div class="col-auto">
                <button type="button" class="btn btn-sm btn-primary btn-block" onclick="window.close()"><i class="fas fa-arrow-left"></i> กลับ</button>
            </div>
            <div class="col-auto p-1 font-weight-bold">
                <h5><B>แบบบันทึกการให้บริการรักษาทางกายภาพบำบัด <?= htmlspecialchars(DbConstant::HOSPITAL_NAME) ?>
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
                                <B>การรักษา</B>
                            </div>

                            <div class="row">
                            <div class="col-sm-2">
                                    <b>วัน-เดือน-ปี</b>
                                </div>
                            </div>
                            <div class="row">
                            <div class="col-sm-2">
                                    <input type="date" class="form-control form-control-sm" id="rxdate" name="rxdate" value="<?= (isset($row['rxdate']) ? htmlspecialchars($row['rxdate']) : '') ?>">
                                </div>
                            </div>
                            <br>

                            <div class="row">
                            <div class="col-sm-6">
                                    <b>PE-Rx Progression note Home/Ward program</b>
                                </div>
                            </div>
                            <br>

                            <div class="row">

&nbsp;&nbsp;&nbsp;&nbsp;<label>PE:&nbsp;</label>
<div class="col-sm-11">
    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="urine_characteristics" id="urine_characteristics" value="<?= (isset($row['urine_characteristics']) ? htmlspecialchars($row['urine_characteristics']) : '') ?>">
</div>

</div>
<br>

<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>RX:&nbsp;</label>
<div class="col-sm-11">
    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="urine_characteristics" id="urine_characteristics" value="<?= (isset($row['urine_characteristics']) ? htmlspecialchars($row['urine_characteristics']) : '') ?>">
</div>
</div>
<br>

<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>Progression note:&nbsp;</label>
<div class="col-sm-11">
    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="urine_characteristics" id="urine_characteristics" value="<?= (isset($row['urine_characteristics']) ? htmlspecialchars($row['urine_characteristics']) : '') ?>">
</div>
</div>
<br>

<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>Home/Ward program:&nbsp;</label>
<div class="col-sm-11">
    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxx" name="urine_characteristics" id="urine_characteristics" value="<?= (isset($row['urine_characteristics']) ? htmlspecialchars($row['urine_characteristics']) : '') ?>">
</div>
</div>
<br>


                           

                           

                            

                                </div>



                            </div>


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
                                    if((
                                        Session::checkPermission('PRS_FORM_ICU1','ADD')
                                    ) && (ReportQueryUtils::checkReadOnly($an))) { ?>
                                    <button type="button" class="btn btn-primary" id="btn_lr_report1" onclick="form_save()"><i class="fas fa-save"></i> บันทึก</button>
                                <?php } ?>
                                <?php
                                    if($id != '') { ?>
                                <a href="prs-icu1-pdf.php?an=<?php echo $an; ?>&id=<?php echo $ids; ?>&loginname=<?php echo $loginname; ?>" target="_blank" class="btn btn-secondary"><i class="fas fa-file-pdf"></i> Print <U>PDF</U> File</a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                                    </div>
                    <br>

                    <script src="../include/my_function.js"></script>
                    <script>
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

                            }else if (value == "on_speaking_reset") {
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
                            }else if (value == "on_communication_reset") {
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


                        function form_save() {

                            var rxdate = $.trim($('[name="rxdate"]').val());
                            var rxtime = $.trim($('[name="rxtime"]').val());
                            if (rxdate == "") {

                                $('[name="rxdate"]').focus();
                                alert('เลือกวันที่');
                            } else if (rxtime == "") {

                                $('[name="rxtime"]').focus();
                                alert('เลือกเวลา');
                            }


                            var url_update = "form-icu1-update.php";
                            var url_save = "form-icu1-save.php";
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