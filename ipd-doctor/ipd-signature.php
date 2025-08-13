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

$permissionCheck = Session::checkPermissionAndShowMessage('ADMISSION_NOTE', 'VIEW');
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
    'form' => 'TEST-FORM',
    'an' => $an,
), JSON_UNESCAPED_UNICODE));




//----------------------เช็คว่า an นี้ มีข้อมูลหรือไม่

$sql = "SELECT *
                FROM `prs_signature_sign`
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


//----------------------เช็คว่า an นี้ มีข้อมูลหรือไม่

date_default_timezone_set('asia/bangkok');

$id = '32'; //ลำดับในตาราง prs_link_menu
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
                                    <B>Generation appearance</B>
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
                                                            } ?> class="custom-control-input" id="appearance_check1" name="appearance_check" value="1" onchange="custom_check('off_appearance_check1');">

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
                                                            } ?> class="custom-control-input" id="appearance_check2" name="appearance_check" value="2" onchange="custom_check('on_appearance_check');">

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
                                    <div id="show_check_save"></div>
                                    <input type="hidden" id="an" name="an" value="<?= htmlspecialchars($an) ?>">
                                    <input type="hidden" id="hn" name="hn" value="<?= htmlspecialchars($hn) ?>">
                                    <input type="hidden" id="version" name="version" value="<?= htmlspecialchars($row['version']) ?>">
                                    <input type="hidden" id="id" name="id" value="<?= htmlspecialchars($row['id']) ?>">
                                    <input type="hidden" id="create_user" name="create_user" value="<?= htmlspecialchars($_SESSION['name']) ?>">


                                    <div class="col-md-12 text-right">
                                        <?php
                                        if ((
                                            Session::checkPermission('IPD_NURSE_NOTE', 'ADD')
                                        ) && (ReportQueryUtils::checkReadOnly($an))) { ?>
                                            <button type="button" class="btn btn-primary" id="btn_lr_report1" onclick="form_save()"><i class="fas fa-save"></i> บันทึก</button>
                                        <?php } ?>
                                        <a href="/pdffile/signature-pdf.php?an=<?php echo $an; ?>&loginname=<?php echo $loginname; ?>" target="_blank" class="btn btn-secondary"><i class="fas fa-file-pdf"></i> Print <U>PDF</U> File</a>
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
                                }

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