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


$permissionCheck = Session::checkPermissionAndShowMessage('PRS_PROGRESS_NOTE_EDIT', 'VIEW');
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
        'form' => 'PROGRESS-NOTE-EDIT',
        'an' => $an,
), JSON_UNESCAPED_UNICODE));


//echo $ids;

//----------------------เช็คว่า an นี้ มีข้อมูลหรือไม่




$sql = "SELECT *
                FROM `ipd_progress_note_item`
                WHERE an = :an and progress_note_item_id = :id";
$id  = null;
$parameters['an'] = $an;
$parameters['id'] = $ids;
$stmt = $conn->prepare($sql);
$stmt->execute($parameters);
if ($row  = $stmt->fetch()) {
        $id = $row['progress_note_item_id'];
} else {
        $id = null;
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
            <div class="col-auto p-1 font-weight-bold">
                <h5><B>แก้ไขรายการ Progress Note <?= htmlspecialchars(DbConstant::HOSPITAL_NAME) ?>
                        
                    </B></h5>
            </div>

        </div>

        <div class="card-group pb-3 ">
            <div class="card">
                <div class="card-body" style=" overflow-y: auto;">
                    <div class="row">
                        <div class="col-md-12">

                        <div class="form-group row">
                                <label class="col-sm-12"><B>progressnote</B></label>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-6">
                                    <textarea class="form-control" id="" name="progress_note_item_detail" rows="6"><?= (isset($row['progress_note_item_detail']) ? htmlspecialchars($row['progress_note_item_detail']) : '') ?></textarea>
                                </div>
                                <label class="col-sm-12"><B>progressnote2</B></label>
                                <div class="col-sm-6">
                                    <textarea class="form-control" id="" name="progress_note_item_detail_2" rows="6"><?= (isset($row['progress_note_item_detail_2']) ? htmlspecialchars($row['progress_note_item_detail_2']) : '') ?></textarea>
                                </div>

                            </div>


                        </div>
                    </div>

                    <div class="row">
                            <div id="show_check_save"></div>
                            <input type="hidden" id="an" name="an" value="<?= htmlspecialchars($an) ?>">
                            <input type="hidden" id="hn" name="hn" value="<?= htmlspecialchars($hn) ?>">
                            <input type="hidden" id="version" name="version" value="<?= htmlspecialchars($row['version']) ?>">
                            <input type="hidden" id="id" name="id" value="<?= htmlspecialchars($row['progress_note_item_id']) ?>">
                            <input type="hidden" id="create_user" name="create_user" value="<?= htmlspecialchars($_SESSION['name']) ?>">

                            <div class="col-md-12 text-right">
                            <?php
                                    if((
                                        Session::checkPermission('PPRS_PROGRESS_NOTE_EDIT','EDIT')
                                    ) && (ReportQueryUtils::checkReadOnly($an))) { ?>
                                    <button type="button" class="btn btn-primary" id="btn_progress_note" onclick="form_save()"><i class="fas fa-save"></i> บันทึก</button>
                                <?php } ?>
                                
                            </div>
                        </div>
<script>
    function form_save() {
                        var url_update = "form-progress-note-update.php";

                            var id = $("#id").val();
                            var my_form = $("#my_form").serialize();

                            if (id == "") {
                                $.post(url_save, my_form, function(data) {
                                        $("#show_check_save").html(data);

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