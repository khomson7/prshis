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

Session::insertSystemAccessLog(json_encode(array(
    'form' => 'IPD-DR-ADMISSION-NOTE-FORM',
    'an' => $an,
), JSON_UNESCAPED_UNICODE));

$conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
$an = empty($_REQUEST['an']) ? null : $_REQUEST['an'];

$hn = KphisQueryUtils::getHnByAn($an);
$vn = KphisQueryUtils::getVnByAn($an);
$an_parameters = ['an' => $an];
$hn_parameters = ['hn' => $hn];




//-------------------------Doctor admission note
$sql = "SELECT *
                FROM `ipd_consultation_summary`
                WHERE an = :an";
$stmt = $conn->prepare($sql);
$stmt->execute($an_parameters);
if ($row  = $stmt->fetch()) {
    $id = $row['id'];
} else {
    $id = null;
}

$sql_item = "SELECT dr_adm_item.id,
                    dr_adm_item.doctor,
                    doctor.`name` AS admission_note_doctorname
                    FROM " . DbConstant::KPHIS_DBNAME . ".ipd_consultation_summary_item dr_adm_item
                    LEFT OUTER JOIN " . DbConstant::HOSXP_DBNAME . ".doctor ON doctor.code = dr_adm_item.doctor
                    WHERE an=:an
                    ORDER BY dr_adm_item.id ASC";
$stmt_item = $conn->prepare($sql_item);
$stmt_item->execute($an_parameters);
$admission_note_count = 0;
while ($row_item = $stmt_item->fetch()) {
    $admission_note_item_id[] = $row_item['admission_note_item_id'];
    $admission_note_doctor[] = $row_item['admission_note_doctor'];
    $admission_note_doctorname[] = $row_item['admission_note_doctorname'];
    //$admission_note_doctorentryposition[] = $row_item['admission_note_doctorentryposition'];
    $admission_note_count++;
}
//------------------------Doctor admission note



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



<br>

<form id="ipd_consultation" action="" method="post" enctype="multipart/form-data">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-1">
                <button type="button" class="btn btn-danger btn-block" onclick="self.close()"><i class="fa fa-window-close"></i> ปิด</button>
            </div>
            <div class="col-md-11">
                <h4>แบบบันทึกการปรึกษาผู้ป่วยเพื่อการดูแลต่อเนื่องในชุมชน <?= htmlspecialchars(DbConstant::HOSPITAL_NAME) ?></h4>
            </div>
        </div>

<br> 
<div class="card-group pb-3 ">
            <div class="card">
                <div class="card-body" style=" overflow-y: auto;">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group row">
                                <label class="col-sm-12"><B>วันที่</B></label>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-1"></div>
                                <label>วันที่</label>
                                <div class="col-sm-4">
                                    <input type="date" class="form-control form-control-sm" id="rxdate" name="rxdate" value="<?= (isset($row_ipt['regdate'])  ? htmlspecialchars($row_ipt['regdate']) : htmlspecialchars($row['rxdate'])) ?>">
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
                                <label class="col-sm-12"><B>WARD</B></label>
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-1"></div>

                                <div class="col-sm-4">
                                    <input type="text" class="form-control form-control-sm" id="ward" name="ward" value="<?= (isset($row_ipt['name']) && $admission_note_id == null ? htmlspecialchars($row_ipt['name']) : htmlspecialchars($row['ward'])) ?>">
                                </div>


                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-12"><B>Underlying Disease</B></label>
        </div>
    
        
        <div class="form-group row">
            <div class="col-sm-1"></div>
            <div class="custom-control custom-radio col-sm-1">
                <input type="radio" <?php if (
                                        $row['underlying_disease'] == 'NO'
                                        || $row['underlying_disease'] == NULL
                                    ) {
                                        echo 'checked="checked"';
                                    } ?> class="custom-control-input" id="disease1" name="underlying_disease" value="NO" onchange="custom_check('off_disease');">
                <label class="custom-control-label" for="disease1">NO</label>
            </div>

            <div class="custom-control custom-radio col-sm-1">
                <input type="radio" <?php if (
                                        $row['disease'] == 'YES'
                                        && $row['disease'] != NULL
                                    ) {
                                        echo 'checked="checked"';
                                    } ?> class="custom-control-input" id="disease2" name="underlying_disease" value="YES" onchange="custom_check('on_disease');">
                <label class="custom-control-label" for="disease2">Yes (ระบุ)</label>
            </div>
            <div class="col-sm-9">
                <input type="text" class="form-control form-control-sm" id="underlying_disease_text" name="underlying_disease" value="<?php if (
                                                                                                                                                    $row['underlying_disease'] != 'NO'
                                
                                                                                                                                                ) {
                                                                                                                                                    echo htmlspecialchars($row['underlying_disease']);
                                                                                                                                                } ?>" <?php if (!($row['underlying_disease'] != 'NO'
                                                                                                                                                                  
                                                                                                                                                                    && $row['underlying_disease'] != NULL)) {
                                                                                                                                                                    echo 'disabled';
                                                                                                                                                                } ?>>
            </div>
        </div>





        <div class="form-group row">
            <label class="col-sm-12"><B>Problem Summary</B></label>
        </div>
        <div class="form-group row">
            <div class="col-sm-12">
                <textarea class="form-control" id="" name="problem_summary" rows="6" ><?= htmlspecialchars($row['problem_summary']) ?></textarea>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-12"><B>Plan Management</B></label>
        </div>
        <div class="form-group row">
            <div class="col-sm-12">
                <textarea class="form-control" id="" name="plan_management" rows="6"><?= htmlspecialchars($row['plan_management']) ?></textarea>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-5"><B>Consultation for</B></label>
        </div>
        <div class="form-group row">
            <div class="col-sm-1"></div>
            <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" <?php if ($row['consultation_for1'] != null) {
                                            echo 'checked="checked"';
                                        } ?> class="custom-control-input" id="e2" onchange="custom_check('on_consultation_for1');">
                <label class="custom-control-label" for="e2">ยืมอุปกรณ์ทางการแพทย์ (ระบุ)</label>
            </div>
            <div class="col-sm-5">
                <input type="text" class="form-control form-control-sm" id="consultation_for1" name="consultation_for1" value="<?= (isset($row['consultation_for1']) ? htmlspecialchars($row['consultation_for1']) : '') ?>" <?php if ($row['consultation_for1'] == null) {
                                                                                                                                                                                                                                    echo 'disabled';
                                                                                                                                                                                                                                } ?>>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-sm-1"></div>
            <div class="custom-control custom-checkbox col-sm-2">
                <input type="checkbox" <?php if ($row['care_plan'] == 'Y') {
                                            echo 'checked="checked"';
                                        } ?> class="custom-control-input" id="care_plan" value="Y" name="care_plan">
                <label class="custom-control-label" for="care_plan">วางแผน Care Plan</label>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-sm-1"></div>
            <div class="custom-control custom-checkbox col-sm-2">
                <input type="checkbox" <?php if ($row['caregiver'] == 'Y') {
                                            echo 'checked="checked"';
                                        } ?> class="custom-control-input" id="caregiver" value="Y" name="caregiver">
                <label class="custom-control-label" for="caregiver">ติดตามหา Caregiver</label>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-sm-1"></div>
            <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" <?php if ($row['consulation_etc'] != null) {
                                            echo 'checked="checked"';
                                        } ?> class="custom-control-input" id="e4" onchange="custom_check('on_consulation_etc');">
                <label class="custom-control-label" for="e4">อื่นๆ</label>
            </div>
            <div class="col-sm-5">
                <input type="text" class="form-control form-control-sm" id="consulation_etc" name="consulation_etc" value="<?= (isset($row['consulation_etc']) ? htmlspecialchars($row['consulation_etc']) : '') ?>" <?php if ($row['consulation_etc'] == null) {
                                                                                                                                                                                                                echo 'disabled';
                                                                                                                                                                                                            } ?>>
            </div>

        </div>

        
<hr>
        <div class="form-row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="action-person-dr-admission">ลงชื่อแพทย์</label>
                                <button type="button" class="btn btn-secondary btn-sm mb-2" onclick="AddDoctorSignature()"><i class="fas fa-plus"></i> ลงชื่อ</button>
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
                            <a href="ipd-dr-admission-note-pdf.php?an=<?php echo $an; ?>&admission_note_id=<?php echo $admission_note_id; ?>&loginname=<?php echo $loginname; ?>" target="_blank" class="btn btn-secondary"><i class="fas fa-file-pdf"></i> Print <U>PDF</U> File</a>
                            <?php
                            //รอแก้ไข
                            // $a = 1;
                            if (Session::checkPermission('ADMISSION_NOTE', 'EDIT')) {
                            ?>
                                <button type="button" class="btn btn-primary" onclick="consultation_save()">บันทึก</button>
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



    </div>
    <!-- card -->
    </div>

    <div class="form-group text-center">
        <div id="show_check_save"></div>
        <input type="hidden" id="an" name="an" value="<?= htmlspecialchars($an) ?>">
        <input type="hidden" id="hn" name="hn" value="<?= htmlspecialchars($hn) ?>">
       <!-- <input type="hidden" id="c_form_type" name="c_form_type" value="2"> -->
        <input type="hidden" id="version" name="version" value="<?= htmlspecialchars($row['version']) ?>">
        <input type="hidden" id="id" name="id" value="<?= htmlspecialchars($row['id']) ?>">
        <input type="hidden" id="create_user" name="create_user" value="<?= htmlspecialchars($_SESSION['name']) ?>">
        
    </div>

</form>
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

    function consultation_save() {
{
            var url_update = "ipd-consultation-summary-update.php";
            var url_save = "ipd-consultation-summary-save.php";
            var id = $("#id").val();
            var ipd_consultation = $("#ipd_consultation").serialize();

            if (id == "") {
                $.post(url_save, ipd_consultation, function(data) {
                        $("#show_check_save").html(data);
                        alert("บันทึกข้อมูลสำเร็จ");
                        // self.close();
                        window.location.reload(true);
                    })
                    .fail(function() {
                        alert("บันทึกข้อมูลไม่สำเร็จ" + error);
                    });
            } else {
                $.post(url_update, ipd_consultation, function(data) {
                        $("#show_check_save").html(data);
                        alert("บันทึกข้อมูลสำเร็จ");
                        // self.close();
                        window.location.reload(true);
                    })
                    .fail(function() {
                        alert("บันทึกข้อมูลไม่สำเร็จ" + error);
                    });
            }

        }

    }
    
    function custom_check(value) {

        if (value == "on_consultation_for1") {
            if (!($('#e2').is(':checked'))) {
                $('#consultation_for1').attr("disabled", true).val('');
            } else {
                $('#consultation_for1').attr("disabled", false).val('');
            }
        } else if (value == "on_consulation_etc") {
            if (!($('#e4').is(':checked'))) {
                $('#consulation_etc').attr("disabled", true).val('');
            } else {
                $('#consulation_etc').attr("disabled", false).val('');
            }

    }

    if (value == "off_disease") {
            $('#underlying_disease_text').attr("disabled", true).val('');
            $('#disease2').prop("checked", false);
        } else if (value == "on_disease") {
           $('#underlying_disease_text').attr("disabled", false).val('');
           $('#disease1').prop("checked", false);
           // $('#entered_by1').prop("checked", false);
          //  $('#entered_by2').prop("checked", false);
        }

}


</script>