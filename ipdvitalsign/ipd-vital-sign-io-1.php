<?php
require_once './project/function/DbUtils.php';
require_once './project/function/KphisQueryUtils.php';
require_once './project/function/SessionManager.php';
$conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
SessionManager::checkLoginSessionAndShowMessage(); //เช็ค session
if(!(
    // && SessionManager::checkPermission('IPD_NURSE_MAIN_PROGRAM','ACCESS')
    // && SessionManager::checkPermission('IO','ADD')
    // && SessionManager::checkPermission('IO','EDIT')
    SessionManager::checkPermission('IO','VIEW')
    // && SessionManager::checkPermission('IO','REMOVE')
    )){
    return;
}

$an = empty($_REQUEST['an']) ? null : $_REQUEST['an'];
$hn = KphisQueryUtils::getHnByAn($an);
date_default_timezone_set('asia/bangkok');
?>
<div class="row">
    <div class="col-md-2">
        <div class="row">
            <div class="col-md-auto">
                <div id="show_select_date_io"></div>
            </div>
        </div>
    </div>
    <div class="col-md-4 offset-md-2 text-right">
        <?php if(SessionManager::checkPermission('IPD_ORDER', 'PRINT')){ ?>
            <a href="ipd-vital-sign-io-pdf.php?an_io_pdf=<?=$an?>" target="_blank" class="btn btn-secondary"><i class="fas fa-print"></i> ทั้งหมด</a>
        <?php } ?>
    </div>
    <div class="col-md-4 text-right">
        <form action="ipd-vital-sign-io-pdf.php" name="form_io_print_pdf" target="_blank"><!-- ส่วนที่ส่งค่าเพื่อ Print PDF -->
            <div class="alert alert-secondary" role="alert">
                <div class="row">
                    <div class="col-md-4 ml-md-auto text-right">
                        <input type="date" class="form-control form-control-sm" id="io_date_start" name="io_date_start" value="<?=date('Y-m-d')?>">
                    </div>
                    <div class="col-md-4 ml-md-auto text-right">
                        <input type="date" class="form-control form-control-sm" id="io_date_end" name="io_date_end" value="<?=date('Y-m-d')?>">
                    </div>
                    <input type="hidden" id="an_io_pdf" name="an_io_pdf" value="<?=$an?>">
                    <div class="col-md-2 ml-md-auto">
                        <input type="submit" class="btn btn-outline-primary btn-sm" value="Print PDF">
                    </div>
                </div>
            </div>
        </form><!-- ส่วนที่ส่งค่าเพื่อ Print PDF -->
    </div>
</div>
<div style="height : calc(100vh - 390px); overflow-y: auto;">
    <div class="table-responsive">
        <table class="table table-bordered table-sm">
            <thead class="text-center bg-secondary">
                <tr>
                    <th rowspan="2">#</th>
                    <th rowspan="2">วันที่</th>
                    <th rowspan="2">เวลา</th>
                    <th colspan="6">Parenteral fluid</th>
                    <th colspan="5" style="background-color:#A9C8C7;">Oral fluid</th>
                    <th colspan="3">Output</th>
                    <th rowspan="2"></th>
                </tr>
                <tr>
                    <th>Type</th>
                    <th>Name</th>
                    <th>Amount</th>
                    <th>Absorb</th>
                    <th>ยกไป</th>
                    <th>Remark</th>
                    <th style="background-color:#A9C8C7;">Name</th>
                    <th style="background-color:#A9C8C7;">Amount</th>
                    <th style="background-color:#A9C8C7;">Absorb</th>
                    <th style="background-color:#A9C8C7;">ยกไป</th>
                    <th style="background-color:#A9C8C7;">Remark</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Remark</th>
                </tr>
                <form id="vital-sign-io-form-1">
                    <input type="hidden" id="io_an" name="io_an" value="<?=htmlspecialchars($an)?>">
                    <input type="hidden" id="io_id" name="io_id" value="">
                    <input type="hidden" id="io_version" name="io_version" value="">
                    <?php
                    if((
                        // && SessionManager::checkPermission('IPD_NURSE_MAIN_PROGRAM','ACCESS')
                        SessionManager::checkPermission('IO','ADD')
                        // && SessionManager::checkPermission('IO','EDIT')
                        // SessionManager::checkPermission('IO','VIEW')
                        // && SessionManager::checkPermission('IO','REMOVE')
                    )){?>
                        <tr class="table-secondary">
                            <td></td>
                            <td>
                                <input type="date" class="form-control form-control-sm" id="io_date" name="io_date" value="<?=date('Y-m-d')?>" style="max-width: 150px;">
                            </td>
                            <td>
                                <input type="time" class="form-control  form-control-sm" id="io_time" name="io_time" value="<?=date('H:i')?>">
                            </td>
                            <td>
                                <select class="form-control  form-control-sm" name="io_parenteral_type" id="io_parenteral_type" style="min-width: 150px;">
                                    <option value="">เลือก</option>
                                    <option value="iv">IV</option>
                                    <option value="medication">Medication</option>
                                    <option value="blood_component">Blood component</option>
                                    <option value="other">Other</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm" id="io_parenteral_name" name="io_parenteral_name" style="min-width: 150px;">
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm" id="io_parenteral_amount" name="io_parenteral_amount" style="min-width: 70px;" onchange="onchange_Parenteral_fluid()">
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm" id="io_parenteral_absorb" name="io_parenteral_absorb" style="min-width: 70px;" onchange="onchange_Parenteral_fluid()">
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm" id="io_parenteral_carry_forward" name="io_parenteral_carry_forward" style="min-width: 70px;" onchange="onchange_Parenteral_carry_forward()">
                            </td>
                            <td>
                                <textarea class="form-control form-control-sm" id="io_parenteral_remark" name="io_parenteral_remark" rows="1" style="min-width: 80px;"></textarea>
                            </td>
                            <td style="background-color:#ECF3F3;">
                                <input type="text" class="form-control form-control-sm" id="io_oral_name" name="io_oral_name" style="min-width: 150px;">
                            </td>
                            <td style="background-color:#ECF3F3;">
                                <input type="text" class="form-control form-control-sm" id="io_oral_amount" name="io_oral_amount" style="min-width: 70px;" oninput="oninput_Oral_fluid()">
                            </td>
                            <td style="background-color:#ECF3F3;">
                                <input type="text" class="form-control form-control-sm" id="io_oral_absorb" name="io_oral_absorb" style="min-width: 70px;" oninput="oninput_Oral_fluid()">
                            </td>
                            <td style="background-color:#ECF3F3;">
                                <input type="text" class="form-control form-control-sm" id="io_oral_carry_forward" name="io_oral_carry_forward" style="min-width: 70px;">
                            </td>
                            <td style="background-color:#ECF3F3;">
                                <textarea class="form-control form-control-sm" id="io_oral_remark" name="io_oral_remark" rows="1" style="min-width: 80px;"></textarea>
                            </td>
                            <td>
                                <select class="form-control form-control-sm" name="io_output_type" id="io_output_type" style="min-width: 130px;">
                                    <option value="">เลือก</option>
                                    <option value="vomit">Vomit</option>
                                    <option value="gastric_content">Gastric content</option>
                                    <option value="drain_tube">Drain tube</option>
                                    <option value="urine">Urine</option>
                                    <option value="dyalysis">Dyalysis</option>
                                    <option value="other">Other</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm" name="io_output_amount" id="io_output_amount" style="min-width: 70px;">
                            </td>
                            <td>
                                <textarea class="form-control form-control-sm" name="io_output_remark" id="io_output_remark" rows="1" style="min-width: 80px;"></textarea>
                            </td>
                            <td>
                                <div style="min-width: 130px;">
                                    <button type="button" class="btn btn-primary btn-sm" onclick="save_vital_sign_io_form()">บันทึก</button>
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="clear_form_vital_sign_io()"><i class="fas fa-undo"></i></button>
                                    <button type="button" class="btn btn-danger btn-sm invisible" id="btn_delete_io" onclick="delete_vital_sign_io()">ลบ</button>
                                </div>
                            </td>
                        </tr><?php
                    }?>
                    <div id="data_io_save"></div>
                    <div id="data_io_edit"></div>
                    <div id="data_io_update"></div>
                    <div id="data_io_delete"></div>
                </form>
            </thead>
            <tbody id="data_io_table">
            </tbody>
        </table>
    </div>
</div>
<script>
    function save_vital_sign_io_form(){
        var url_io_save = 'ipd-vital-sign-io-save.php';
        var url_io_update = 'ipd-vital-sign-io-update.php';
        var io_id = $("#io_id").val();
        var io_date = $("#io_date").val();
        var io_time = $("#io_time").val();
        if(io_date == ""){
            alert("กรุณากรอกวันที่");
            $("#io_date").focus();
        }else if(io_time == ""){
            alert("กรุณากรอกเวลา");
            $("#io_time").focus();
        }else{
            if(io_id == ""){
                $.post(url_io_save,$("#vital-sign-io-form-1").serialize(),function(data_io_save){
                    $("#data_io_save").html(data_io_save);
                    $("#io_date").focus();
                    onclick_vital_sign_io_search();
                    clear_form_vital_sign_io();
                    show_select_date_io(<?=json_encode($an)?>);
                });
            }else if(io_id != ""){
                $.post(url_io_update,$("#vital-sign-io-form-1").serialize(),function(data_io_update){
                    $("#data_io_update").html(data_io_update);
                    $("#io_date").focus();
                    onclick_vital_sign_io_search();
                    clear_form_vital_sign_io();
                    show_select_date_io(<?=json_encode($an)?>);
                });
            }
        }
    }
    show_select_date_io(<?=json_encode($an)?>);
    function show_select_date_io(an){
        var url="ipd-vital-sign-io-select-date.php";
        $.ajax(url,{
            async: false,
            data: {'an': an},
        })
        .done(function(date_select_io){
            $("#show_select_date_io").html(date_select_io);
            onclick_vital_sign_io_search();
        });
    }
    function onclick_vital_sign_io_search(){ //โชว์ข้อมูลของ IO ในรูปแบบตาราง
        var select_search_io_date = $("#select_search_io_date").val();
        var an = $("#io_an").val();
        var url = "ipd-vital-sign-io-table.php";
        $.post(url,{an,select_search_io_date},function(data_io_table){
            $("#data_io_table").html(data_io_table);
        });
    }
    function edit_vital_sign_io(io_id,an){
        var url="ipd-vital-sign-io-edit.php";
        $.post(url,{io_id,an},function(data_io_edit){
            $("#data_io_edit").html(data_io_edit);
            $("#io_date").focus();
        });
    }
    function delete_vital_sign_io(){
        var io_version = $("#io_version").val();
        var io_id = $("#io_id").val();
        var url_io_delete = "ipd-vital-sign-io-delete.php";
        if (confirm("คุณต้องการลบข้อมูล ใช่หรือไม่?")) {
            $.post(url_io_delete,{io_id,io_version},function(data_io_delete){
                $("#data_io_delete").html(data_io_delete);
                onclick_vital_sign_io_search();
                clear_form_vital_sign_io()
                show_select_date_io(<?=json_encode($an)?>);
            });
        }
    }
    function onchange_Parenteral_fluid(){
        var SIGDIG = 100000;
        var io_parenteral_sum = 0;
        var io_parenteral_amount = parseFloat($("#io_parenteral_amount").val(),10);
        var io_parenteral_absorb = parseFloat($("#io_parenteral_absorb").val(),10);
        io_parenteral_sum = ((io_parenteral_amount*SIGDIG)-(io_parenteral_absorb*SIGDIG))/SIGDIG;
        $("#io_parenteral_carry_forward").val(io_parenteral_sum);
    }
    function onchange_Parenteral_carry_forward(){
        var SIGDIG = 100000;
        var io_oral_sum = 0;
        var io_parenteral_amount = parseFloat($("#io_parenteral_amount").val(),10);
        var io_parenteral_carry_forward =  parseFloat($("#io_parenteral_carry_forward").val(),10);
        io_oral_sum = ((io_parenteral_amount*SIGDIG)-(io_parenteral_carry_forward*SIGDIG))/SIGDIG;
        $("#io_parenteral_absorb").val(io_oral_sum);
    }
    function oninput_Oral_fluid(){
        var SIGDIG = 100000;
        var io_oral_sum = 0;
        var io_oral_amount = parseFloat($("#io_oral_amount").val(),10);
        var io_oral_absorb = parseFloat($("#io_oral_absorb").val(),10);
        io_oral_sum = ((io_oral_amount*SIGDIG)-(io_oral_absorb*SIGDIG))/SIGDIG;
        $("#io_oral_carry_forward").val(io_oral_sum);
    }
    function clear_form_vital_sign_io(){
        $("#vital-sign-io-form-1")[0].reset();
        $("#io_id").val('');
        $("#io_version").val('');
    }
</script>