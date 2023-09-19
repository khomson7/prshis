<?php
    require_once '../include/Session.php';
    require_once '../include/SelectUtils.php';
    Session::checkLoginSessionAndShowMessage(); //เช็ค session
    // Session::checkPermissionAndShowMessage('KPHIS_ACCESS_IPD_NURSE_PROGRAM');
    if(!(
        Session::checkPermission('IPD_NURSE_MAIN_PROGRAM','ACCESS')
        // && Session::checkPermission('IPD_ORDER','ADD')
        // && Session::checkPermission('IPD_ORDER','EDIT')
        && Session::checkPermission('IPD_ORDER','VIEW')
        // && Session::checkPermission('IPD_ORDER','REMOVE')
        )){
        return;
    }

    $IS_TODAY_NOT_PASS_DCHDATE = KphisQueryUtils::isTodayNotPassDchDate($an);
    $IPD_ORDER_PRINT = Session::checkPermission('IPD_ORDER', 'PRINT');
?>
<style>
#order-table th {
    width: 30%;
}
td.order-table-row {
    height: 55px !important;
}
ul.dash {
    list-style: none;
    margin-left: 0;
    padding-left: 1em;
    margin-bottom: 0;
}
ul.dash > li:before {
    display: inline-block;
    content: "-";
    width: 1em;
    margin-left: -1em;
}
@media print{
    body{
        font-size: 20px;
    }
}
.tooltip-inner {
    white-space: pre;
    max-width: none;
    text-align: left;
}
</style>
<div class="order-content">
    <div class="row in-index-print dr-admission-note-view" style="display: none;">
        <div class="col-12 mb-3" id="index-print-header" style="white-space: pre-wrap;"></div>
    </div>
    <div class="row in-index-print" style="display: none;">
        <div class="col-12 mb-3">
            <div class="table table-bordered table-sm" style="height: 200px;">
                <div class="ml-2 mt-1 font-weight-bold">Note:</div>
            </div>
        </div>
    </div>
    <div class="row" id="order_date_row">
        <div class="col-auto mb-3" id="order_date_select_col">
            <select class="form-control" id="order_date_select" name="order_date_select" onchange="onchange_select_order_date(event)"></select>
        </div>
        <div class="col-auto mb-3 d-print-none" id="order_date_button_row_col">
            <div class="row" id="order_date_button_row"></div>
        </div>
        <div class="col-auto mb-3 er_order_view_button_col" style="display: none;">
            <a class="btn btn-secondary er_order_view_button" target="_blank" href="#">ER Order</a>
        </div>
        <div class="col-auto mb-3 d-print-none" id="print_col">
            <button type="button" class="btn btn-secondary" onclick="onclickPrintOrderButton(event);"><i class="fas fa-print"></i> Order</button>
            <?php if(Session::checkPermission('IPD_ORDER', 'PRINT')){ ?>
            <button type="button" class="btn btn-secondary" onclick="onclickPrintAllOrderButton(event);"><i class="fas fa-print"></i> All Orders</button>
            <?php } ?>
            <button type="button" class="btn btn-secondary d-none" onclick="onclickPrintIndex();"><i class="fas fa-print"></i> Index</button>
        </div>
        <div class="col-auto mb-3 d-print-none">
            <button type="button" class="btn btn-secondary" onclick="onclickViewDrAdmissionNoteButton();"><i class="fa fa-clipboard-list" aria-hidden="true"></i> ประวัติผู้ป่วย</button>
        </div>
        <?php
        if($IS_TODAY_NOT_PASS_DCHDATE){
        ?>
        <div class="col mb-3 d-print-none"  id="openTemplateButtonCol">
            <div class="float-right ml-3">
                <button class="btn btn-secondary" id="openPreOrderButton" data-toggle="modal" data-target="#selectPreOrderModal" onclick="onclickOpenPreOrderButton(event);">
                    <span class="spinner-grow spinner-grow-sm text-danger" role="status" aria-hidden="true" id="openPreOrderButtonSpinner" style="display: none;"></span>
                    <i class="fa fa-clipboard-list" aria-hidden="true"></i> เลือกใบ Order
                </button>
            </div>
        </div>
        <?php
        } ?>
    </div>
    <div class="row">
        <div class="col">
            <table class="table table-bordered" id="order-table" style="break-inside:auto;">
                <thead>
                    <tr>
                        <th scope="col" class="text-center" style="width: 25%;" id="one-day-column-header">One Day Order</th>
                        <th scope="col" class="text-center in-index-print">ลงชื่อ/เวลา</th>
                        <th scope="col" class="text-center" style="width: 25%;" id="continuous-column-header">Continuous Order</th>
                        <th scope="col" class="text-center not-in-index-print" style="width: 25%;" id="progress-note-column-header">Progress Note</th>
                        <th scope="col" class="text-center in-index-print">Lab</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="order-table-row" style="break-inside:auto;">
                        <td class="one-day-column today"></td>
                        <td class="text-center in-index-print" ></td>
                        <td class="continuous-column today"></td>
                        <td class="progress-note-column today not-in-index-print"></td>
                        <td class="text-center in-index-print" ></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>


<!-- <div class="modal" id="indexActionFormModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Action</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="indexActionFormModalBody">
                <form id="index-action-form">
                    <div id="index-action-plan-display-group">
                        <div class="form-group" id="index-action-plan-order-detail-form-group">
                            <label for="index-action-plan-order-detail">Order</label>
                            <textarea class="form-control" id="index-action-plan-order-detail" readonly></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-6">
                                <label for="index-action-plan-plan-date">Plan Date</label>
                                <input type="date" class="form-control" id="index-action-plan-plan-date" readonly>
                            </div>
                            <div class="form-group col-6">
                                <label for="index-action-plan-plan-time">Plan Time</label>
                                <input type="time" class="form-control" id="index-action-plan-plan-time" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="index-action-plan-plan-detail">Plan Detail</label>
                            <textarea type="text" class="form-control" id="index-action-plan-plan-detail" readonly></textarea>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label for="index-action-action-date">Action Date</label>
                            <input type="date" class="form-control" id="index-action-action-date" name="index-action-action-date">
                        </div>
                        <div class="form-group col-6">
                            <label for="index-action-action-time">Action Time</label>
                            <input type="time" class="form-control" id="index-action-action-time" name="index-action-action-time">
                        </div>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="index-action-blood-had" name="index-action-blood-had">
                        <label class="form-check-label" for="index-action-blood-had">Blood/HAD</label>
                    </div>
                    <div class="form-group">
                        <label for="index-action-result">ผลลัพธ์</label>
                        <textarea type="text" class="form-control" id="index-action-result" name="index-action-result"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="index-action-remark">หมายเหตุ</label>
                        <textarea type="text" class="form-control" id="index-action-remark" name="index-action-remark"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Save</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div> -->

<div class="modal" id="selectPreOrderModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">เลือกรายการ</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="selectPreOrderModalBody">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

<div class="all-order-print"></div>

<!-- <script src="node_modules\jspdf\dist\polyfills.umd.js"></script>
<script src="node_modules\dompurify\dist\purify.min.js"></script>
<script src="node_modules\html2canvas\dist\html2canvas.min.js"></script>
<script src="node_modules\jspdf\dist\jspdf.umd.min.js"></script> -->
<script>
    const IPD_ORDER_VIEW = <?=json_encode(Session::checkPermission('IPD_ORDER', 'VIEW'))?>;
    const IPD_ORDER_ADD = <?=json_encode(Session::checkPermission('IPD_ORDER', 'ADD'))?>;
    const IPD_ORDER_REMOVE = <?=json_encode(Session::checkPermission('IPD_ORDER', 'REMOVE'))?>;
    const IPD_ORDER_OFF = <?=json_encode(Session::checkPermission('IPD_ORDER', 'OFF'))?>;
    const IPD_ORDER_CONFIRM = <?=json_encode(Session::checkPermission('IPD_ORDER', 'CONFIRM'))?>;
    const IPD_ORDER_EDIT = <?=json_encode(Session::checkPermission('IPD_ORDER', 'EDIT'))?>;
    const IPD_ORDER_ACCEPT = <?=json_encode(Session::checkPermission('IPD_ORDER', 'ACCEPT'))?>;

    // const DATA_TYPE_NURSE_USE = <?=json_encode(Session::checkPermission('DATA_TYPE_NURSE', 'USE'))?>;

    const PROGRESS_NOTE_VIEW = <?=json_encode(Session::checkPermission('PROGRESS_NOTE', 'VIEW'))?>;
    const PROGRESS_NOTE_ADD = <?=json_encode(Session::checkPermission('PROGRESS_NOTE', 'ADD'))?>;
    const PROGRESS_NOTE_REMOVE = <?=json_encode(Session::checkPermission('PROGRESS_NOTE', 'REMOVE'))?>;
    const PROGRESS_NOTE_EDIT = <?=json_encode(Session::checkPermission('PROGRESS_NOTE', 'EDIT'))?>;

    const IPD_NURSE_INDEX_VIEW = <?=json_encode(Session::checkPermission('IPD_NURSE_INDEX', 'VIEW'))?>;
    const IPD_NURSE_INDEX_ADD = <?=json_encode(Session::checkPermission('IPD_NURSE_INDEX', 'ADD'))?>;
    const IPD_NURSE_INDEX_REMOVE = <?=json_encode(Session::checkPermission('IPD_NURSE_INDEX', 'REMOVE'))?>;
    const IPD_NURSE_INDEX_EDIT = <?=json_encode(Session::checkPermission('IPD_NURSE_INDEX', 'EDIT'))?>;

    const IPD_ORDER_AN = <?=json_encode($an)?>;
    const IPD_ORDER_VN = <?=json_encode(KphisQueryUtils::getVnByAn($an))?>;
    const IPD_ORDER_HN = <?=json_encode(KphisQueryUtils::getHnByAn($an))?>;
    const IS_TODAY_NOT_PASS_DCHDATE = <?=json_encode($IS_TODAY_NOT_PASS_DCHDATE)?>;
    const IPD_ORDER_DCHDATE = <?=json_encode(KphisQueryUtils::getDchdateByAn($an))?>;
    const IPD_ORDER_DOCTORCODE = <?=json_encode($_SESSION['doctorcode'])?>;
    const ORDER_VIEW_BY = 'nurse';
    const ORDER_OWNER_TYPE = 'nurse';

    $( document ).ready(function() {
        $('.not-in-index-print').show();
        $('.in-index-print').hide();
        initSearchBox();
        load_order_date();
        opdErOrderCheck();
        preOrderCheck();
    });

    // function onclickPrintAllOrderButton(event){
    //     event.preventDefault();
    //     const { jsPDF } = window.jspdf;
    //     const doc = new jsPDF();
    //     console.log(doc)
    //     //$('#pills-order').children(":visible")
    //     doc.html(document.getElementById('pills-order'), {
    //         callback: function (d) {
    //             d.output("dataurlnewwindow")
    //         },
    //     })
    // }

    function onclickPrintOrderButton(event){
        let all_page = '';
        let order_date_select = $('#order_date_select').val();
        console.log(order_date_select);
        if(order_date_select != null){
            let order_date = moment(order_date_select, "YYYY-MM-DD");
            all_page += '<div class="font-weight-bold">วันที่ ' + order_date.format("DD/MM/") + (parseInt(order_date.format("YYYY"),10)+543) + '</div>';
            onchange_select_order_date(event);
            all_page += $('#order-table').prop('outerHTML');
        }
        // console.log(all_page);
        $('.all-order-print').html(all_page);
        $('.order-content').hide();
        window.print();
        $('.order-content').show();
        $('.all-order-print').html('');
    }

    function onclickPrintAllOrderButton(event){
        $.getJSON("./ipd-dr-order-date.php", {an: IPD_ORDER_AN, order_by: 'ASC'})
            .done(function(json) {
                let all_page = '';
                $.each(json, function(i, v) {
                    // console.log(i);
                    $('#order_date_select').val(v.order_date);
                    let order_date = moment(v.order_date, "YYYY-MM-DD");
                    all_page += '<div class="font-weight-bold" '
                                    + ((i == 0) ? '' : ' " style="break-before: page;" ')
                                    + '>วันที่ ' + order_date.format("DD/MM/") + (parseInt(order_date.format("YYYY"),10)+543) + '</div>';
                    onchange_select_order_date(event);
                    all_page += $('#order-table').prop('outerHTML');
                });
                // console.log(all_page);
                $('.all-order-print').html(all_page);
                $('.order-content').hide();
                window.print();
                $('.order-content').show();
                $('.all-order-print').html('');
            })
            .fail(function() {
                alert( "error" );
            });
    }

    function onclickOpenPreOrderButton(event){
        event.preventDefault();
        $('#selectPreOrderModalBody').html(
            $('<iframe/>', {
                src: 'ipd-dr-pre-order-list-searchbox.php?pre_order_type=pre_order&hn=' + IPD_ORDER_HN,
                style: 'width: 100%; height: 500px; border: none;'
            })
        );
    }

    function opdErOrderCheck(){
        $.ajax("./opd-er-order-master-check.php",{
            dataType: "json",
            // async: false,
            data: {vn: IPD_ORDER_VN},
        })
        .done(function(json) {
            // console.log(json);
            $.each(json, function(i, v) {
                if(v.opd_er_order_master_id != null){
                    // console.log(v.opd_er_order_master_id);
                    // console.log(v.order_date);
                    let order_date = (v.order_date != null ? moment(v.order_date, "YYYY-MM-DD") : null);
                    $('.er_order_view_button_col').show();
                    $('.er_order_view_button').html(`ER (${toThaiDateString(order_date)}) <i class="fas fa-external-link-alt"></i>`).attr('href',`opd-er-order.php?view_by=nurse&opd_er_order_master_id=${v.opd_er_order_master_id}`);
                }
            });
        })
        .fail(function() {
            alert( "error" );
        });
    }

    function preOrderCheck(){
        $.getJSON("./ipd-dr-pre-order-check.php", {hn: IPD_ORDER_HN})
            .done(function(pre_order_count) {
                if(pre_order_count > 0){
                    // $('#openPreOrderButton').removeClass('btn-secondary').addClass('btn-danger');
                    $('#openPreOrderButtonSpinner').show();
                } else {
                    $('#openPreOrderButtonSpinner').hide();
                }
            })
            .fail(function() {
                alert( "error" );
            });
    }

    function usePreOrderMaster(selected_ipd_pre_order_master_id, pre_order_type){
        // console.log('selected_ipd_pre_order_master_id',selected_ipd_pre_order_master_id);
        // console.log('pre_order_type',pre_order_type);
        if(pre_order_type == 'appointment' || pre_order_type == 'opd'){
            $.post("./ipd-dr-pre-order-to-order.php", { pre_order_master_id: selected_ipd_pre_order_master_id, an: IPD_ORDER_AN })
            .done(function(pre_order_master_id) {
                onchange_select_order_date();
                $('#selectPreOrderModal').modal('hide');
                preOrderCheck();
            })
            .fail(function(response) {
                // console.log(response);
                if(response.status == '409'){
                    alert("รายการนี้มีการใช้งานแล้ว");
                    $('#selectPreOrderModal').modal('hide');
                    onchange_select_order_date();
                    preOrderCheck();
                } else {
                    alert("error");
                }
            });
        } else {
            // console.log('pre_order_type ?');
        }
    }

    function mewsCalForViewDrAdmissionNoteButton(age_y, input_bt, input_pr, input_rr, input_respirator, input_sbp, input_inotope, input_conscious_id, input_urine_amount, input_urine_duration) {
        let total = mews(age_y, input_bt, input_pr, input_rr, input_respirator, input_sbp, input_inotope, input_conscious_id, input_urine_amount, input_urine_duration);
        let total_color = 'inherit';
        let total_text = '';
        if(total === 0){
            total_color = '#45c351';
        }else if(total > 0 && total <= 3){
            total_color = '#e6b728';
        }else if(total >= 4){
            total_color = '#e51616';
        }else{
            total = '';
        }
        total_text = "<div class='badge text-white' style='font-weight:bold; font-size:100%; background-color: " + total_color + ";'> " + total + "</div>";
        return total_text;
    }

    function after_save_nurse_index_note(event) {
        viewDrAdmissionNoteAndNurseIndexNote();
    }

    function after_delete_nurse_index_note(event) {

    }

    function viewDrAdmissionNoteAndNurseIndexNote(){
        $.getJSON("./ipd-nurse-index-print-data.php", {an: IPD_ORDER_AN})
            .done(function(json) {
                let hosxp_operation_history = json.hosxp_operation_history.reduce(function(total, currentValue, currentIndex, arr){
                    total += checkStringValue(currentValue.operation_list,'<br>',undefined,undefined,true);
                    return total;
                }, '');

                let first_vitalsign = json.first_vitalsign.reduce(function(total, currentValue, currentIndex, arr){
                    total +=  '<table class="table table-sm table-bordered" style="width: 300px;">'
                            + '<tr><td><span class="text-primary">BP:</span> ' + checkStringValue(currentValue.sbp) + '/' + checkStringValue(currentValue.dbp) + '</td><td><span class="text-primary">E:</span> ' + checkStringValue(currentValue.eye) + '</td></tr>'
                            + '<tr><td><span class="text-primary">BT:</span> ' + checkStringValue(currentValue.bt) + '</td><td><span class="text-primary">V:</span> ' + checkStringValue(currentValue.verbal) + '</td></tr>'
                            + '<tr><td><span class="text-primary">PR:</span> ' + checkStringValue(currentValue.pr) + '</td><td><span class="text-primary">M:</span> ' + checkStringValue(currentValue.movement) + '</td></tr>'
                            + '<tr><td><span class="text-primary">RR:</span> ' + checkStringValue(currentValue.rr) + '</td><td><span class="text-primary">MEWS:</span> '+ mewsCalForViewDrAdmissionNoteButton(currentValue.age_y, currentValue.bt, currentValue.pr, currentValue.rr, currentValue.respirator, currentValue.sbp, currentValue.inotope, currentValue.conscious_id, currentValue.urine_amount, currentValue.urine_duration) + '</td></tr>'
                            + '<tr><td colspan="2"><span class="text-primary">Braden Scale:</span> ' + checkStringValue(currentValue.braden) + '</td></tr>'
                            + '</table>';
                    return total;
                }, '');

                let hosxp_drugallergy = json.hosxp_drugallergy.reduce(function(total, currentValue, currentIndex, arr){
                    if(currentValue != null){
                        total += checkStringValue(currentValue.drugallergy,'<br>',undefined,undefined,true);
                    }
                    return total;
                }, '');

                let nurse_index_note = json.nurse_index_note.length > 0 ? json.nurse_index_note[0] : null;

                const LEFT_BRACKET = '[';
                const RIGHT_BRACKET = ']';
                let nurse_index_note_card_div = $('<div>',{
                    id: 'nurse_index_note_card_div',
                    class: 'card mt-3',
                    html: [
                        $('<div>',{
                            class: 'card-header font-weight-bold',
                            html: [
                                $('<span>',{
                                    text: 'Note',
                                }),
                                $('<button>',{
                                    class: 'btn btn-secondary btn-sm float-right d-print-none',
                                    text: 'แก้ไข',
                                    click: function(event) {
                                        if(nurse_index_note != null && nurse_index_note.nurse_index_note_id != null){
                                            onclickEditNurseIndexNote(event, nurse_index_note.nurse_index_note_id);
                                        } else {
                                            onclickAddNurseIndexNote(event);
                                        }
                                    },
                                }),
                            ],
                        }),
                        $('<div>',{
                            id: 'nurse_index_note_display',
                            class: 'card-body',
                            html: ((nurse_index_note != null && nurse_index_note.nurse_index_note != null) ?
                                (escapeHtmlString(nurse_index_note.nurse_index_note)).replaceAll(LEFT_BRACKET,'<span style="color:red;">').replaceAll(RIGHT_BRACKET,'</span>') : ''),
                        }),
                    ],
                });

                // var doctor_in_charge_div = null;
                // if(json.doctor_in_charge.length > 0){
                //     let doctor_in_charge = json.doctor_in_charge[0];
                //     doctor_in_charge_div = $('<div>',{
                //                                 class: 'card mt-3',
                //                                 html: [
                //                                     $('<div>',{
                //                                         class: 'card-header font-weight-bold',
                //                                         text: "แพทย์เจ้าของไข้",
                //                                     }),
                //                                     $('<div>',{
                //                                         class: 'card-body',
                //                                         text: doctor_in_charge.kphis_incharge_doctor_name,
                //                                     }),
                //                                 ],
                //                             });
                // }
                var doctor_in_charge_div = null;
                if(json.doctor_in_charge.length > 0){
                    let doctor_in_charge = json.doctor_in_charge[0];
                    doctor_in_charge_div =  $('<div>',{
                                                html: "<span class='font-weight-bold'>แพทย์เจ้าของไข้:</span> " + document.createTextNode(checkStringValue(doctor_in_charge.kphis_incharge_doctor_name)).textContent
                                            });
                }

                var dr_admission_note_div = null;
                if(json.ipd_dr_admission_note.length == 0){
                    dr_admission_note_div = $('<div>',{
                                                class: 'card',
                                                html: [
                                                    $('<div>',{
                                                        class: 'card-body',
                                                        text: 'ยังไม่มีการบันทึก "แบบบันทึกการรับใหม่ผู้ป่วยใน"',
                                                    }),
                                                ],
                                            });
                }
                for(var i = 0; i < json.ipd_dr_admission_note.length; i++) {
                    var v = json.ipd_dr_admission_note[i];
                    dr_admission_note_div = $('<div>',{
                        class: 'card',
                        html: [
                            $('<div>',{
                                class: 'card-header font-weight-bold',
                                text: "ประวัติผู้ป่วย (จากแบบบันทึกการรับใหม่ผู้ป่วยใน)",
                            }),
                            $('<div>',{
                                class: 'card-body',
                                html: [
                                    $('<div>',{
                                        html: "<span class='font-weight-bold'>Dx:</span> " + document.createTextNode(checkStringValue(v.impression)).textContent,
                                    }),
                                    $('<div>',{
                                        html: "<span class='font-weight-bold'>CC:</span> " + document.createTextNode(checkStringValue(v.chief_complaints)).textContent,
                                    }),
                                    $('<div>',{
                                        html: "<span class='font-weight-bold'>PI:</span> " + document.createTextNode(checkStringValue(v.medical_history)).textContent,
                                    }),
                                    $('<div>',{
                                        html: "<span class='font-weight-bold'>PH:</span> " + checkStringValue(document.createTextNode(v.disease).textContent)
                                        + checkStringValue(checkStringValue(v.disease_detail).split(" ").reduce(function(total, currentValue, currentIndex, arr){
                                            if(!isBlankOrNullOrWhiteSpace(currentValue)){
                                                let prefix = '';
                                                let suffix = '';
                                                if((currentIndex%3) == 0){
                                                    prefix = '<br><span class="text-primary">โรค:</span> ';
                                                } else if((currentIndex%3) == 1){
                                                    prefix = ' <span class="text-primary">จำนวนปี:</span> '
                                                    // suffix = ' ปี';
                                                } else if((currentIndex%3) == 2){
                                                    prefix = ' <span class="text-primary">สถานพยาบาลที่รักษา:</span> '
                                                }
                                                total += prefix + document.createTextNode(currentValue).textContent + suffix;
                                            }
                                            return total;
                                        },'')),
                                    }),
                                    $('<div>',{
                                        html: "<span class='font-weight-bold'>สัญญาณชีพแรกรับ:</span> " + first_vitalsign,
                                    }),
                                    $('<div>',{
                                        html: "<span class='font-weight-bold'>ประวัติการผ่าตัด:</span> " + hosxp_operation_history,
                                    }),
                                    $('<div>',{
                                        html: "<span class='font-weight-bold'>ประวัติการแพ้:</span> "
                                        + "<span class='font-weight-bold text-danger'>"
                                        + checkStringValue(hosxp_drugallergy)
                                        + checkStringValue(checkStringValue(v.allergy_drug_history).split(" ").reduce(function(total, currentValue, currentIndex, arr){
                                            if(!isBlankOrNullOrWhiteSpace(currentValue)){
                                                let prefix = '';
                                                let suffix = '';
                                                if(currentIndex%2 == 0){
                                                    prefix = '<br>ชื่อ: ';
                                                } else if(currentIndex%2 == 1){
                                                    prefix = ' อาการที่แพ้: '
                                                }
                                                total += prefix + document.createTextNode(currentValue).textContent + suffix;
                                            }
                                            return total;
                                        },''), "<br>ยา:")
                                        + checkStringValue(checkStringValue(v.allergy_food_history).split(" ").reduce(function(total, currentValue, currentIndex, arr){
                                            if(!isBlankOrNullOrWhiteSpace(currentValue)){
                                                let prefix = '';
                                                let suffix = '';
                                                if(currentIndex%2 == 0){
                                                    prefix = '<br>ชื่อ: ';
                                                } else if(currentIndex%2 == 1){
                                                    prefix = ' อาการที่แพ้: '
                                                }
                                                total += prefix + document.createTextNode(currentValue).textContent + suffix;
                                            }
                                            return total;
                                        },''), "<br>อาหาร:")
                                        + checkStringValue(checkStringValue(v.allergy_etc_history).split(" ").reduce(function(total, currentValue, currentIndex, arr){
                                            if(!isBlankOrNullOrWhiteSpace(currentValue)){
                                                let prefix = '';
                                                let suffix = '';
                                                if(currentIndex%2 == 0){
                                                    prefix = '<br>ชื่อ: ';
                                                } else if(currentIndex%2 == 1){
                                                    prefix = ' อาการที่แพ้: '
                                                }
                                                total += prefix + document.createTextNode(currentValue).textContent + suffix;
                                            }
                                            return total;
                                        },''), "<br>อื่นๆ:")
                                        + "</span>"
                                        ,
                                    }),
                                    ((!isBlankOrNull(v.last_child)
                                    || !isBlankOrNull(v.last_abort)
                                    || !isBlankOrNull(v.curette)
                                    || !isBlankOrNull(v.lmp)
                                    || !isBlankOrNull(v.edc)) ?
                                    $('<div>',{
                                        html: [
                                            "<span class='font-weight-bold'>ประวัติด้านสูตินรีเวชกรรม:</span> ",
                                            (!isBlankOrNull(v.last_child) ? $('<div>',{html: [
                                                $('<span>',{
                                                    class: 'text-primary mr-1',
                                                    text: 'Last child:',
                                                }),
                                                $('<span>',{
                                                    text: v.last_child,
                                                }),
                                                $('<span>',{
                                                    class: 'ml-1',
                                                    text: 'ปี',
                                                }),
                                            ],}) : ''),
                                            (!isBlankOrNull(v.last_abort) ? $('<div>',{html: [
                                                $('<span>',{
                                                    class: 'text-primary mr-1',
                                                    text: 'Last abort:',
                                                }),
                                                $('<span>',{
                                                    text: v.last_abort,
                                                }),
                                            ],}) : ''),
                                            (!isBlankOrNull(v.curette) ? $('<div>',{html: [
                                                $('<span>',{
                                                    class: 'text-primary mr-1',
                                                    text: 'ประวัติการขูดมดลูก:',
                                                }),
                                                $('<span>',{
                                                    html: (v.curette == 'Y' ? 'เคย' : 'ไม่เคย'),
                                                }),
                                            ],}) : ''),
                                            (!isBlankOrNull(v.lmp) ? $('<div>',{html: [
                                                $('<span>',{
                                                    class: 'text-primary mr-1',
                                                    text: 'ประจําเดือนครั้งสุดท้าย:',
                                                }),
                                                $('<span>',{
                                                    text: toThaiDateString(v.lmp),
                                                }),
                                            ],}) : ''),
                                            (!isBlankOrNull(v.edc) ? $('<div>',{html: [
                                                $('<span>',{
                                                    class: 'text-primary mr-1',
                                                    text: 'กําหนดการคลอด:',
                                                }),
                                                $('<span>',{
                                                    text: toThaiDateString(v.edc),
                                                }),
                                            ],}) : ''),
                                        ],
                                    }) : ''),
                                    ((!isBlankOrNull(v.pb_no)
                                    || !isBlankOrNull(v.giant_baby)
                                    || !isBlankOrNull(v.distocia)
                                    || !isBlankOrNull(v.extraction)
                                    || !isBlankOrNull(v.pph)
                                    || !isBlankOrNull(v.pb_etc)) ?
                                    $('<div>',{
                                        html: [
                                            "<span class='font-weight-bold'>ประวัติการคลอด:</span> ",
                                            (!isBlankOrNull(v.pb_no) ? $('<div>',{html: [
                                                $('<span>',{
                                                    class: 'mr-1',
                                                    text: 'ปฎิเสธ',
                                                }),
                                            ],}) : ''),
                                            (!isBlankOrNull(v.giant_baby) ? $('<div>',{html: [
                                                $('<span>',{
                                                    class: 'mr-1',
                                                    text: 'เคยคลอดบุตร นน. > 4000 กรัม',
                                                }),
                                            ],}) : ''),
                                            (!isBlankOrNull(v.distocia) ? $('<div>',{html: [
                                                $('<span>',{
                                                    class: 'mr-1',
                                                    text: 'มีประวัติคลอดยาก',
                                                }),
                                            ],}) : ''),
                                            (!isBlankOrNull(v.extraction) ? $('<div>',{html: [
                                                $('<span>',{
                                                    class: 'mr-1',
                                                    text: 'มีประวัติคลอดหัตถการ (ระบุ):',
                                                }),
                                                $('<span>',{
                                                    text: v.extraction,
                                                }),
                                            ],}) : ''),
                                            (!isBlankOrNull(v.pph) ? $('<div>',{html: [
                                                $('<span>',{
                                                    class: 'mr-1',
                                                    text: 'มีประวัติตกเลือดหลังคลอด',
                                                }),
                                            ],}) : ''),
                                            (!isBlankOrNull(v.pb_etc) ? $('<div>',{html: [
                                                $('<span>',{
                                                    class: 'mr-1',
                                                    text: 'อื่นๆ:',
                                                }),
                                                $('<span>',{
                                                    text: v.pb_etc,
                                                }),
                                            ],}) : ''),
                                        ],
                                    }) : ''),
                                    ((!isBlankOrNull(v.hf)
                                    || !isBlankOrNull(v.hf_position)) ?
                                    $('<div>',{
                                        html: [
                                            "<span class='font-weight-bold'>ตรวจหน้าท้อง:</span> ",
                                            (!isBlankOrNull(v.hf) ? $('<div>',{html: [
                                                $('<span>',{
                                                    class: 'text-primary mr-1',
                                                    text: 'High of fundus:',
                                                }),
                                                $('<span>',{
                                                    text: v.hf,
                                                }),
                                            ],}) : ''),
                                            (!isBlankOrNull(v.hf_position) ? $('<div>',{html: [
                                                $('<span>',{
                                                    class: 'text-primary mr-1',
                                                    text: 'Position:',
                                                }),
                                                $('<span>',{
                                                    text: v.hf_position,
                                                }),
                                            ],}) : ''),
                                        ],
                                    }) : ''),
                                    ((!isBlankOrNull(v.condition_pregnant)) ?
                                    $('<div>',{
                                        html: [
                                            "<span class='font-weight-bold'>อาการระหว่างตั้งครรภ์:</span> ",
                                            (!isBlankOrNull(v.hf) ? $('<span>',{html: [
                                                $('<span>',{
                                                    text: v.condition_pregnant,
                                                }),
                                            ],}) : ''),
                                        ],
                                    }) : ''),
                                    ((!isBlankOrNull(v.hiv)
                                    || !isBlankOrNull(v.vdrl)
                                    || !isBlankOrNull(v.hbs_ag)
                                    || !isBlankOrNull(v.hct)
                                    || !isBlankOrNull(v.hiv2)
                                    || !isBlankOrNull(v.vdrl2)
                                    || !isBlankOrNull(v.hbs_ag2)
                                    || !isBlankOrNull(v.hct2)
                                    || !isBlankOrNull(v.gr)
                                    || !isBlankOrNull(v.thalassemia)
                                    || !isBlankOrNull(v.husband)) ?
                                    $('<div>',{
                                        html: [
                                            "<span class='font-weight-bold'>ผลเลือด:</span> ",
                                            (!isBlankOrNull(v.hiv) ? $('<div>',{html: [
                                                $('<span>',{
                                                    class: 'text-primary mr-1',
                                                    text: 'HIV 1:',
                                                }),
                                                $('<span>',{
                                                    class: 'mr-1',
                                                    text: v.hiv,
                                                }),
                                            ],}) : ''),
                                            (!isBlankOrNull(v.hiv2) ? $('<div>',{html: [
                                                $('<span>',{
                                                    class: 'text-primary mr-1',
                                                    text: 'HIV 2:',
                                                }),
                                                $('<span>',{
                                                    class: 'mr-1',
                                                    text: v.hiv2,
                                                }),
                                            ],}) : ''),
                                            (!isBlankOrNull(v.vdrl) ? $('<div>',{html: [
                                                $('<span>',{
                                                    class: 'text-primary mr-1',
                                                    text: 'VDRL 1:',
                                                }),
                                                $('<span>',{
                                                    class: 'mr-1',
                                                    text: v.vdrl,
                                                }),
                                            ],}) : ''),
                                            (!isBlankOrNull(v.vdrl2) ? $('<div>',{html: [
                                                $('<span>',{
                                                    class: 'text-primary mr-1',
                                                    text: 'VDRL 2:',
                                                }),
                                                $('<span>',{
                                                    class: 'mr-1',
                                                    text: v.vdrl2,
                                                }),
                                            ],}) : ''),
                                            (!isBlankOrNull(v.hbs_ag) ? $('<div>',{html: [
                                                $('<span>',{
                                                    class: 'text-primary mr-1',
                                                    text: 'HBsAg 1:',
                                                }),
                                                $('<span>',{
                                                    class: 'mr-1',
                                                    text: v.hbs_ag,
                                                }),
                                            ],}) : ''),
                                            (!isBlankOrNull(v.hbs_ag2) ? $('<div>',{html: [
                                                $('<span>',{
                                                    class: 'text-primary mr-1',
                                                    text: 'HBsAg 2:',
                                                }),
                                                $('<span>',{
                                                    class: 'mr-1',
                                                    text: v.hbs_ag2,
                                                }),
                                            ],}) : ''),
                                            (!isBlankOrNull(v.hct) ? $('<div>',{html: [
                                                $('<span>',{
                                                    class: 'text-primary mr-1',
                                                    text: 'HCT 1:',
                                                }),
                                                $('<span>',{
                                                    text: v.hct,
                                                }),
                                                $('<span>',{
                                                    class: 'ml-1',
                                                    text: '%',
                                                }),
                                            ],}) : ''),
                                            (!isBlankOrNull(v.hct2) ? $('<div>',{html: [
                                                $('<span>',{
                                                    class: 'text-primary mr-1',
                                                    text: 'HCT 2:',
                                                }),
                                                $('<span>',{
                                                    text: v.hct2,
                                                }),
                                                $('<span>',{
                                                    class: 'ml-1',
                                                    text: '%',
                                                }),
                                            ],}) : ''),
                                            (!isBlankOrNull(v.gr) ? $('<div>',{html: [
                                                $('<span>',{
                                                    class: 'text-primary mr-1',
                                                    text: 'Blood group:',
                                                }),
                                                $('<span>',{
                                                    class: 'mr-1',
                                                    text: v.gr,
                                                }),
                                            ],}) : ''),
                                            (!isBlankOrNull(v.thalassemia) ? $('<div>',{html: [
                                                $('<span>',{
                                                    class: 'text-primary mr-1',
                                                    text: 'ผล thalassemia ตัวเอง:',
                                                }),
                                                $('<span>',{
                                                    class: 'mr-1',
                                                    text: v.thalassemia,
                                                }),
                                            ],}) : ''),
                                            (!isBlankOrNull(v.husband) ? $('<div>',{html: [
                                                $('<span>',{
                                                    class: 'text-primary mr-1',
                                                    text: 'ผล thalassemia สามี:',
                                                }),
                                                $('<span>',{
                                                    class: 'mr-1',
                                                    text: v.husband,
                                                }),
                                            ],}) : ''),
                                        ],
                                    }) : ''),
                                    doctor_in_charge_div,
                                ]
                            }),
                        ],
                    });
                }

                $('#index-print-header').html(
                    [
                        dr_admission_note_div,
                        // doctor_in_charge_div,
                        nurse_index_note_card_div,
                    ],
                );
            })
            .fail(function() { alert( "error" ); });

    }

    function onclickViewDrAdmissionNoteButton(){
        if(!$('.dr-admission-note-view').is(":hidden")){
            $('.dr-admission-note-view').toggle();
        } else {
            $('.dr-admission-note-view').toggle();
            //ดึงข้อมูลแรกรับของ an นี้มาแสดง
            viewDrAdmissionNoteAndNurseIndexNote();
        }
    }

    function onclickPrintIndex(){
        $('#nurse_index_note_card_div').hide();
        //ดึงข้อมูลแรกรับของ an นี้มาแสดง
        $.ajaxSetup({ async: false });
        $.getJSON("./ipd-nurse-index-print-data.php", {an: IPD_ORDER_AN})
            .done(function(json) {
                let hosxp_operation_history = '';
                $.each(json.hosxp_operation_history, function(i, v) {
                    hosxp_operation_history = v.hosxp_operation_history;
                });
                $.each(json.ipd_dr_admission_note, function(i, v) {
                    let allergy_history = '';
                    if(v.allergy_history == 'ไม่มี'){
                        allergy_history = v.allergy_history;
                    } else {
                        allergy_history = v.allergy_history;
                    }
                    $('#index-print-header').html(
                        [
                            $('<div>',{
                                html: "<span class='font-weight-bold'>CC:</span> " + checkStringValue(v.chief_complaints),
                            }),
                            $('<div>',{
                                html: "<span class='font-weight-bold'>PI:</span> " + checkStringValue(v.medical_history),
                            }),
                        ],
                    );
                });
            })
            .fail(function() { alert( "error" ); });
        $.ajaxSetup({ async: true });

        $('.not-in-index-print').hide();
        $('.in-index-print').show();
        window.print();
        $('.not-in-index-print').show();
        $('.in-index-print').hide();
    }

    function load_order_date(){
        $('#order_date_select').html('');
        $('#order_date_button_row').html('');
        $.getJSON("./ipd-dr-order-date.php", {an: IPD_ORDER_AN})
            .done(function(json) {
                if(json.length == 0){
                    // $('#order_date_row').hide();
                    $('#order_date_select_col').hide();
                    $('#order_date_button_row_col').hide();
                } else {
                    // $('#order_date_row').show();
                    $('#order_date_select_col').show();
                    $('#order_date_button_row_col').show();
                }
                $.each(json, function(i, v) {
                    let order_date = moment(v.order_date, "YYYY-MM-DD");
                    $('<option/>', {
                        value: v.order_date,
                        text: order_date.format("DD/MM/") + (parseInt(order_date.format("YYYY"),10)+543) + ((v.is_today == 1) ? ' (วันนี้)':''),
                        "data-is-today": v.is_today,
                    })
                    .appendTo('#order_date_select');
                    if(i==0){
                        $('#order_date_select').val(v.order_date);
                    }
                    if(i < 7){
                        $('#order_date_button_row').append(
                            $('<div/>',{
                                class: 'col-auto mb-3',
                                html: $('<button/>',{
                                    id: 'select-order-date-button-' + order_date.format("YYYY-MM-DD"),
                                    class: 'btn' + ((v.is_today == 1) ? ' btn-primary':' btn-secondary'),
                                    text: order_date.format("DD/MM/") + (parseInt(order_date.format("YYYY"),10)+543) + ((v.is_today == 1) ? ' (วันนี้)':''),
                                    click: function(event){
                                        $('#order_date_select').val(v.order_date);
                                        onchange_select_order_date(event);
                                    },
                                }),
                            })
                        );
                    }
                });
                onchange_select_order_date(event);
            })
            .fail(function() {
                alert( "error" );
            });
    }

    function onchange_select_order_date(event){
        var allowChangeOrderDate = true;
        if( $('#addOneDayFormContainer').length > 0
            || $('#addContinuousFormContainer').length > 0
            || $('#addProgressNoteFormContainer').length > 0 ){

            if(confirm('มีการเปิดฟอร์มบันทึกข้อมูลอยู่ ต้องการเปลี่ยนวันหรือไม่')){
                allowChangeOrderDate = true;
            } else {
                allowChangeOrderDate = false;
                $('#order_date_select').val($('#order_date_select').data('lastSelected'));
            }
        }
        if(allowChangeOrderDate){
            var order_date = $('#order_date_select').val();
            $('#order_date_select').data('lastSelected', order_date);
            // console.log('order_date',order_date);
            if(order_date != null){
                $('#order_date_button_row_col button').addClass('btn-secondary').removeClass('btn-primary');
                $('#select-order-date-button-' + order_date).addClass('btn-primary').removeClass('btn-secondary');
                //TODO will change this [moment().format("YYYY-MM-DD")] to current date from server (v.is_today) for consistency
                var isToday = (order_date == moment().format("YYYY-MM-DD"));
                $('.one-day-column.today').html('');
                $('#addOneDayColumnInputHeaderLink').remove();
                $('.continuous-column.today').html('');
                $('#addContinuousColumnInputHeaderLink').remove();
                $('.progress-note-column.today').html('');
                $('#addProgressNoteColumnInputHeaderLink').remove();
                $.ajaxSetup({
                    async: false
                });
                if(IPD_ORDER_VIEW){
                    $.getJSON("./ipd-dr-order-one-day-data.php", {an: IPD_ORDER_AN, order_date: order_date, view_by: ORDER_VIEW_BY})
                        .done(function(json) {
                            $.each(json, function(i, v) {
                                $('.one-day-column.today').append(oneday_data_to_text(v));
                            });
                        })
                        .fail(function() {
                            alert( "error" );
                        });
                    $.getJSON("./ipd-dr-order-continuous-previous-data.php", {an: IPD_ORDER_AN, order_date: order_date, view_by: ORDER_VIEW_BY})
                        .done(function(json) {
                            if(json.length > 0){
                                $('.continuous-column.today').append('<div>Current treatment</div>');
                                var current_treatment_ul = $('<ul/>', {
                                    class: "dash",
                                    style: "white-space: pre-wrap;",
                                });
                                $.each(json, function(i, order_item) {
                                    if(order_item.order_item_type != 'med'){
                                        var li = $('<li/>',{
                                            class: 'clearfix',
                                        });
                                        li.append($('<span/>',{
                                            text: (order_item.icode == null ? '' : (order_item.med_name + (order_item.order_item_detail != '' ? '\n' : ''))) + order_item.order_item_detail,
                                            style: ((order_item.off_by_order_item_id != null) ? 'text-decoration: line-through;' : '') + ((order_item.order_item_type == 'med' || order_item.order_item_type == 'ivfluid') ? " color: blue;":"")
                                        }));
                                        if(order_item.stat == 'Y'){
                                            li.append(' <span class="font-weight-bold">(Stat)</span>');
                                        }
                                        if(order_item.off_by_order_item_id != null){
                                            li.append(' <span class="font-weight-bold">(Off)</span>');
                                        }
                                        if(order_item.index_plan_actions != null && order_item.index_plan_actions.length > 0){
                                            order_item.index_plan_actions.forEach(function(index_plan_action){
                                                index_plan_action_to_text(order_date, li, index_plan_action);
                                            });
                                        }
                                        // if(IPD_ORDER_OFF && isToday && order_item.off_by_order_item_id == null && order_item.order_owner_type == 'nurse'){
                                        //     li.append($('<a/>', {
                                        //         text: 'Off',
                                        //         class: "float-right",
                                        //         href: '#off_'+order_item.order_item_id/*+'_'+order_item.order_item_detail*/,
                                        //         click: function(event) {
                                        //             onclickOffContinuousOrderItem(event, order_item.order_item_id, (order_item.icode == null ? '' : (order_item.med_name + (order_item.order_item_detail != '' ? '\n' : ''))) + order_item.order_item_detail);
                                        //         },
                                        //     }));
                                        // }
                                        if(IPD_NURSE_INDEX_ADD && /*IPD_NURSE_INDEX_ADD && *//*order_item.off_by_order_item_id == null && */(order_item.order_owner_type == 'doctor' || order_item.order_owner_type == 'nurse')){
                                            li.append($('<a/>', {
                                                text: '+Plan',
                                                class: "float-right d-print-none",
                                                href: '#index_'+order_item.order_item_id/*+'_'+order_item.order_item_detail*/,
                                                click: function(event) {
                                                    onclickAddIndexPlanOrderItem(event, order_item.order_item_id, order_item.order_item_detail);
                                                },
                                            }));
                                        }
                                        current_treatment_ul.append(li);
                                    }
                                });
                                $('.continuous-column.today').append(current_treatment_ul);
                                $('.continuous-column.today').append('<div class="d-print-none">Medication</div>');
                                var med_ul = $('<ul/>', {
                                    class: "dash d-print-none",
                                    style: "white-space: pre-wrap;",
                                });
                                $.each(json, function(i, order_item) {
                                    if(order_item.order_item_type == 'med'){
                                        var li = $('<li/>',{
                                            class: 'clearfix',
                                        });
                                        li.append($('<span/>',{
                                            text: (order_item.icode == null ? '' : (order_item.med_name + (order_item.order_item_detail != '' ? '\n' : ''))) + order_item.order_item_detail,
                                            style: ((order_item.off_by_order_item_id != null) ? 'text-decoration: line-through;' : '') + ((order_item.order_item_type == 'med' || order_item.order_item_type == 'ivfluid') ? " color: blue;":"")
                                        }));
                                        if(order_item.stat == 'Y'){
                                            li.append(' <span class="font-weight-bold">(Stat)</span>');
                                        }
                                        if(order_item.off_by_order_item_id != null){
                                            li.append(' <span class="font-weight-bold">(Off)</span>');
                                        }
                                        if(order_item.allergy_agent_symptom != null){
                                            li.append('<br class="d-print-none"/>');
                                            li.append($('<small/>',{
                                                class: 'font-weight-bold text-danger d-print-none',
                                                role: "button",
                                                html: 'แพ้ยา/เฝ้าระวัง',
                                                title: order_item.allergy_agent_symptom,
                                            }));
                                        }
                                        if(order_item.index_plan_actions != null && order_item.index_plan_actions.length > 0){
                                            order_item.index_plan_actions.forEach(function(index_plan_action){
                                                index_plan_action_to_text(order_date, li, index_plan_action);
                                            });
                                        }
                                        // if(IPD_ORDER_OFF && isToday && order_item.off_by_order_item_id == null && order_item.order_owner_type == 'nurse'){
                                        //     li.append($('<a/>', {
                                        //         text: 'Off',
                                        //         class: "float-right",
                                        //         href: '#off_'+order_item.order_item_id/*+'_'+order_item.order_item_detail*/,
                                        //         click: function(event) {
                                        //             onclickOffContinuousOrderItem(event, order_item.order_item_id, (order_item.icode == null ? '' : (order_item.med_name + (order_item.order_item_detail != '' ? '\n' : ''))) + order_item.order_item_detail);
                                        //         },
                                        //     }));
                                        // }
                                        if(IPD_NURSE_INDEX_ADD && /*IPD_NURSE_INDEX_ADD && *//*order_item.off_by_order_item_id == null && */(order_item.order_owner_type == 'doctor' || order_item.order_owner_type == 'nurse')){
                                            li.append($('<a/>', {
                                                text: '+Plan',
                                                class: "float-right d-print-none",
                                                href: '#index_'+order_item.order_item_id/*+'_'+order_item.order_item_detail*/,
                                                click: function(event) {
                                                    onclickAddIndexPlanOrderItem(event, order_item.order_item_id, order_item.order_item_detail);
                                                },
                                            }));
                                        }
                                        med_ul.append(li);
                                    }
                                });
                                $('.continuous-column.today').append(med_ul);
                                $('.continuous-column.today').append('<hr>');
                            }
                        })
                        .fail(function() {
                            alert( "error" );
                        });
                    $.getJSON("./ipd-dr-order-continuous-data.php", {an: IPD_ORDER_AN, order_date: order_date, view_by: ORDER_VIEW_BY})
                        .done(function(json) {
                            $.each(json, function(i, v) {
                                $('.continuous-column.today').append(continuous_data_to_text(v));
                            });
                        })
                        .fail(function() {
                            alert( "error" );
                        });
                }
                if(PROGRESS_NOTE_VIEW){
                    $.getJSON("./ipd-dr-order-progress-note-data.php", {an: IPD_ORDER_AN, progress_note_date: order_date})
                        .done(function(json) {
                            $.each(json, function(i, v) {
                                $('.progress-note-column.today').append(progress_note_data_to_text(v));
                            });
                        })
                        .fail(function(result) {
                            if(result != null ){
                                // $('.progress-note-column.today').append(result.responseJSON.message);
                                alert(result.responseJSON.message);
                            } else {
                                alert( "error" );
                            }
                        });
                }
                $.ajaxSetup({
                    async: true
                });

                if(isToday && IPD_ORDER_ADD){
                    $('.one-day-column.today').append('<div class="text-right d-print-none" id="one-day-column-add-link"><a href="#" onclick="event.preventDefault(); addOneDayColumnInput();">+Add</a></div>');
                    $('#one-day-column-header').append('<a id="addOneDayColumnInputHeaderLink" href="#" class="float-right d-print-none" onclick="event.preventDefault(); addOneDayColumnInput();">+Add</a>');
                    // $('.continuous-column.today').append('<div class="text-right d-print-none" id="continuous-column-add-link"><a href="#" onclick="event.preventDefault(); addContinuousColumnInput();">+Add</a></div>');
                    // $('#continuous-column-header').append('<a id="addContinuousColumnInputHeaderLink" href="#" class="float-right d-print-none" onclick="event.preventDefault(); addContinuousColumnInput();">+Add</a>');
                } else {
                    $('#addOneDayColumnInputHeaderLink').remove();
                    $('#one-day-column-add-link').remove();
                    // $('#addContinuousColumnInputHeaderLink').remove();
                    // $('#continuous-column-add-link').remove();
                }

                if(/*isToday && */PROGRESS_NOTE_ADD){
                    $('.progress-note-column.today').append('<div class="text-right d-print-none" id="progress-note-column-add-link"><a href="#" onclick="event.preventDefault(); addProgressNoteColumnInput();">+Add</a></div>');
                    $('#progress-note-column-header').append('<a id="addProgressNoteColumnInputHeaderLink" href="#" class="float-right d-print-none" onclick="event.preventDefault(); addProgressNoteColumnInput();">+Add</a>');
                } else {
                    $('#addProgressNoteColumnInputHeaderLink').remove();
                    $('#progress-note-column-add-link').remove();
                }
            }
        }
    }

    function onclick_search_button_oneDayForm(event, element, addNewInput, searchbox_type, type, separator) {
        openSearchBox(event, element, 'common-searchbox-'+searchbox_type+'.php', null,
            function(event, result){ onclickOneDayOptionButton(event, addNewInput, type, separator, result); })
    }

    function onclick_search_button_continuousForm(event, element, addNewInput, searchbox_type, type, separator) {
        openSearchBox(event, element, 'common-searchbox-'+searchbox_type+'.php', null,
            function(event, result){ onclickContinuousOptionButton(event, addNewInput, type, separator, result); })
    }

    function onclick_add_button_oneDayForm(event, type) {
        $('#oneDayForm-'+type+'-input-div').append(oneDayForm_input_div['#oneDayForm-'+type+'-input-div']);
        $('[name="oneDayForm-'+type+'-text"].last-focus-input').removeClass('last-focus-input');
        $('[name="oneDayForm-'+type+'-text"]').last().addClass('last-focus-input').focus();
    }

    function onclick_add_button_continuousForm(event, type) {
        $('#continuousForm-'+type+'-input-div').append(continuousForm_input_div['#continuousForm-'+type+'-input-div']);
        $('[name="continuousForm-'+type+'-text"].last-focus-input').removeClass('last-focus-input');
        $('[name="continuousForm-'+type+'-text"]').last().addClass('last-focus-input').focus();
    }

    function onclick_add_button_progressNoteForm(event, type) {
        $('#progressNoteForm-'+type+'-input-div').append(progressNoteForm_input_div['#progressNoteForm-'+type+'-input-div']);
        $('[name="progressNoteForm-'+type+'-text"].last-focus-input').removeClass('last-focus-input');
        $('[name="progressNoteForm-'+type+'-text"]').last().addClass('last-focus-input').focus();
    }

    function onclick_remove_button_oneDayForm(event, button) {
        var inputText = $(button).closest('.order-input-group').find('textarea');
        if($.trim(inputText.val()) != ''){
            if(confirm('ยืนยันลบรายการ')){
                $(button).closest('.order-input-group').remove();
            }
        } else {
            $(button).closest('.order-input-group').remove();
        }
    }

    function onclick_remove_button_continuousForm(event, button) {
        onclick_remove_button_oneDayForm(event, button);
    }

    function onclick_remove_button_progressNoteForm(event, button) {
        onclick_remove_button_oneDayForm(event, button);
    }

    function onfocusOnedayText(event, type, input){
        $('[name="oneDayForm-'+type+'-text"].last-focus-input').removeClass('last-focus-input');
        $(input).addClass('last-focus-input');
    }

    function onfocusContinuousText(event, type, input){
        $('[name="continuousForm-'+type+'-text"].last-focus-input').removeClass('last-focus-input');
        $(input).addClass('last-focus-input');
    }

    function onfocusProgressNoteText(event, type, input){
        $('[name="progressNoteForm-'+type+'-text"].last-focus-input').removeClass('last-focus-input');
        $(input).addClass('last-focus-input');
    }

    function onclickOneDayOptionButton(event, addNewInput, type, separator, value, cursorMove){
        var targetInput = $('[name="oneDayForm-'+type+'-text"].last-focus-input');
        // console.log('targetInput',targetInput);
        var icode_targetInput = targetInput.closest('.order-input-group').find('[name=oneDayForm-icode]');
        // console.log('icode_targetInput',icode_targetInput);
        // if(icode_targetInput.length > 0){
        //     console.log('icode_targetInput.get(0).value',icode_targetInput.get(0).value);
        // }
        if((targetInput.length == 0 || (
                (addNewInput
                    && (($.trim(targetInput.get(0).value).length > 0)
                    || (icode_targetInput.length > 0 && icode_targetInput.get(0).value.length > 0)) )
                ))
            ){
            onclick_add_button_oneDayForm(event, type);
            targetInput = $('[name="oneDayForm-'+type+'-text"].last-focus-input');
        }
        insertAtCursor(targetInput.get(0), value.text_value, cursorMove, separator);
        autoGrowTextArea(targetInput.get(0));
        if(value != null && value.icode != null){
            targetInput.closest('.order-input-group').find('[name=oneDayForm-icode]').val(value.icode);
            targetInput.closest('.order-input-group').find('[name=oneDayForm-med-name]').val(value.med_name);
        }
    }

    function onclickContinuousOptionButton(event, addNewInput, type, separator, value, cursorMove){
        var targetInput = $('[name="continuousForm-'+type+'-text"].last-focus-input');
        var icode_targetInput = targetInput.closest('.order-input-group').find('[name=continuousForm-icode]');
        if((targetInput.length == 0 || (
                (addNewInput
                    && (($.trim(targetInput.get(0).value).length > 0)
                    || (icode_targetInput.length > 0 && icode_targetInput.get(0).value.length > 0)) )
                ))
            ){
            onclick_add_button_continuousForm(event, type);
            targetInput = $('[name="continuousForm-'+type+'-text"].last-focus-input');
        }
        insertAtCursor(targetInput.get(0), value.text_value, cursorMove, separator);
        autoGrowTextArea(targetInput.get(0));
        if(value != null && value.icode != null){
            targetInput.closest('.order-input-group').find('[name=continuousForm-icode]').val(value.icode);
            targetInput.closest('.order-input-group').find('[name=continuousForm-med-name]').val(value.med_name);
        }
    }

    function onclickProgressNoteOptionButton(event, addNewInput, type, separator, value, cursorMove){
        var targetInput = $('[name="progressNoteForm-'+type+'-text"].last-focus-input');
        if(targetInput.length == 0 || (addNewInput && $.trim(targetInput.get(0).value).length > 0)){
            onclick_add_button_progressNoteForm(event, type);
            targetInput = $('[name="progressNoteForm-'+type+'-text"].last-focus-input');
        }
        insertAtCursor(targetInput.get(0), value.text_value, cursorMove, separator);
        autoGrowTextArea(targetInput.get(0));
    }

    var editing_oneday_order_inner_div = '';
    function closeOneDayColumnInput(){
        var order_id = $('#oneDayForm_order_id').val();
        $('#addOneDayFormContainer').remove();
        //reload current order_id data
        $('#order_id_'+order_id+'_inner_div').html(editing_oneday_order_inner_div);
        $('#order_id_'+order_id+'_action_row_div').show();
        var order_div = document.getElementById('order_id_'+order_id+'_div');
        if(order_div != null){
            order_div.scrollIntoView();
        }
    }

    var editing_continuous_order_inner_div = '';
    function closeContinuousColumnInput(){
        var order_id = $('#continuousForm_order_id').val();
        $('#addContinuousFormContainer').remove();
        //reload current order_id data
        $('#order_id_'+order_id+'_inner_div').html(editing_continuous_order_inner_div);
        $('#order_id_'+order_id+'_action_row_div').show();
        var order_div = document.getElementById('order_id_'+order_id+'_div');
        if(order_div != null){
            order_div.scrollIntoView();
        }
    }

    var editing_progress_note_inner_div = '';
    function closeProgressNoteColumnInput(){
        var progress_note_id = $('#progress_note_id').val();
        $('#addProgressNoteFormContainer').remove();
        //reload current progress_note_id data
        $('#progress_note_id_'+progress_note_id+'_inner_div').html(editing_progress_note_inner_div);
        $('#progress_note_id_'+progress_note_id+'_action_row_div').show();
        var progress_note_div = document.getElementById('progress_note_id_'+progress_note_id+'_div');
        if(progress_note_div != null){
            progress_note_div.scrollIntoView();
        }
    }

    function deleteOneDayOrder(order_id){
        if(confirm('ยืนยันลบรายการ')){
            $.post("./ipd-dr-order-one-day-delete.php", {order_id: order_id})
                .done(function(response) {
                    document.getElementById('order_id_'+order_id+'_div').remove();
                    onchange_select_order_date(event);
                })
                .fail(function(response) {
                    if(response.status == '409'){
                        alert("รายการนี้ได้รับการยืนยันไปก่อนหน้านี้แล้ว");
                        onchange_select_order_date(event);
                    } else if(response.status == '403'){
                        if(response.statusText == 'Access Denied'){
                            alert("ไม่สามารถลบรายการได้เนื่องจากไม่มีสิทธิ์");
                        } else {
                            alert("ไม่สามารถลบรายการได้");
                        }
                        //onchange_select_order_date(event);
                        load_order_date();
                    } else {
                        alert("error");
                    }
                });
        }
    }

    function deleteContinuousOrder(order_id){
        if(confirm('ยืนยันลบรายการ')){
            $.post("./ipd-dr-order-continuous-delete.php", {order_id: order_id})
                .done(function(response) {
                    document.getElementById('order_id_'+order_id+'_div').remove();
                    onchange_select_order_date(event);
                })
                .fail(function(response) {
                    if(response.status == '409'){
                        alert("รายการนี้ได้รับการยืนยันไปก่อนหน้านี้แล้ว");
                        onchange_select_order_date(event);
                    } else if(response.status == '403'){
                        if(response.statusText == 'Access Denied'){
                            alert("ไม่สามารถลบรายการได้เนื่องจากไม่มีสิทธิ์");
                        } else {
                            alert("ไม่สามารถลบรายการได้");
                        }
                        //onchange_select_order_date(event);
                        load_order_date();
                    } else {
                        alert("error");
                    }
                });
        }
    }

    function deleteProgressNoteOrder(progress_note_id){
        if(confirm('ยืนยันลบรายการ')){
            $.post("./ipd-dr-order-progress-note-delete.php", {progress_note_id: progress_note_id})
                .done(function(response) {
                    document.getElementById('progress_note_id_'+progress_note_id+'_div').remove();
                    onchange_select_order_date(event);
                })
                .fail(function(response) {
                    if(response.status == '403'){
                        if(response.statusText == 'Access Denied'){
                            alert("ไม่สามารถลบรายการได้เนื่องจากไม่มีสิทธิ์");
                        } else {
                            alert("ไม่สามารถลบรายการได้");
                        }
                        //onchange_select_order_date(event);
                        load_order_date();
                    } else {
                        alert("error");
                    }
                });
        }
    }

    function confirmOneDayOrder(order_id){
        if(confirm('ยืนยันรายการ')){
            $.post("./ipd-dr-order-one-day-confirm.php", {order_id: order_id})
                .done(function(response) {
                    $('[href="#confirm_order_id='+order_id+'"]').remove();
                    $('[href="#edit_order_id='+order_id+'"]').remove();
                    $('[href="#delete_order_id='+order_id+'"]').remove();
                    onchange_select_order_date(event);
                })
                .fail(function(response) {
                    // console.log(response);
                    if(response.status == '409'){
                        alert("รายการนี้ได้รับการยืนยันไปก่อนหน้านี้แล้ว");
                        onchange_select_order_date(event);
                    } else if(response.status == '403'){
                        if(response.statusText == 'Access Denied'){
                            alert("ไม่สามารถยืนยันรายการได้เนื่องจากไม่มีสิทธิ์");
                        } else if(response.statusText == "Forbidden : Confirm previous day's order is not allowed"){
                            alert("ไม่สามารถยืนยันรายการได้เนื่องจากข้ามวันมาแล้ว");
                        } else {
                            alert("ไม่สามารถยืนยันรายการได้");
                        }
                        //onchange_select_order_date(event);
                        load_order_date();
                    } else if(response.status == '404'){
                        //response.statusText == "Not Found"
                        alert("ไม่สามารถยืนยันรายการได้เนื่องจากรายการนี้ได้ถูกลบไปแล้ว");
                        load_order_date();
                    } else {
                        alert("error");
                    }
                });
        }
    }

    function confirmContinuousOrder(order_id){
        if(confirm('ยืนยันรายการ')){
            $.post("./ipd-dr-order-continuous-confirm.php", {order_id: order_id})
                .done(function(response) {
                    $('[href="#confirm_order_id='+order_id+'"]').remove();
                    $('[href="#edit_order_id='+order_id+'"]').remove();
                    $('[href="#delete_order_id='+order_id+'"]').remove();
                    onchange_select_order_date(event);
                })
                .fail(function(response) {
                    if(response.status == '409'){
                        alert("รายการนี้ได้รับการยืนยันไปก่อนหน้านี้แล้ว");
                        onchange_select_order_date(event);
                    } else if(response.status == '403'){
                        if(response.statusText == 'Access Denied'){
                            alert("ไม่สามารถยืนยันรายการได้เนื่องจากไม่มีสิทธิ์");
                        } else if(response.statusText == "Forbidden : Confirm previous day's order is not allowed"){
                            alert("ไม่สามารถยืนยันรายการได้เนื่องจากข้ามวันมาแล้ว");
                        } else {
                            alert("ไม่สามารถยืนยันรายการได้");
                        }
                        //onchange_select_order_date(event);
                        load_order_date();
                    } else if(response.status == '404'){
                        //response.statusText == "Not Found"
                        alert("ไม่สามารถยืนยันรายการได้เนื่องจากรายการนี้ได้ถูกลบไปแล้ว");
                        load_order_date();
                    } else {
                        alert("error");
                    }
                });
        }
    }

    function acceptOneDayOrder(order_id){
        if(confirm('ยืนยันรับทราบรายการ')){
            $.post("./ipd-dr-order-one-day-nurse-accept.php", {order_id: order_id})
                .done(function(response) {
                    $('[href="#accept_order_id='+order_id+'"]').remove();
                    onchange_select_order_date(event);
                })
                .fail(function(response) {
                    if(response.status == '409'){
                        alert("รายการนี้ได้รับการยืนยันรับทราบรายการไปก่อนหน้านี้แล้ว");
                        onchange_select_order_date(event);
                    } else if(response.status == '403'){
                        if(response.statusText == 'Access Denied'){
                            alert("ไม่สามารถยืนยันรับทราบรายการได้เนื่องจากไม่มีสิทธิ์");
                        } else {
                            alert("ไม่สามารถยืนยันรับทราบรายการได้");
                        }
                        //onchange_select_order_date(event);
                        load_order_date();
                    } else {
                        alert("error");
                    }
                });
        }
    }

    function acceptContinuousOrder(order_id){
        if(confirm('ยืนยันรับทราบรายการ')){
            $.post("./ipd-dr-order-continuous-nurse-accept.php", {order_id: order_id})
                .done(function(response) {
                    $('[href="#accept_order_id='+order_id+'"]').remove();
                    onchange_select_order_date(event);
                })
                .fail(function(response) {
                    if(response.status == '409'){
                        alert("รายการนี้ได้รับการยืนยันรับทราบรายการไปก่อนหน้านี้แล้ว");
                        onchange_select_order_date(event);
                    } else if(response.status == '403'){
                        if(response.statusText == 'Access Denied'){
                            alert("ไม่สามารถยืนยันรับทราบรายการได้เนื่องจากไม่มีสิทธิ์");
                        } else {
                            alert("ไม่สามารถยืนยันรับทราบรายการได้");
                        }
                        //onchange_select_order_date(event);
                        load_order_date();
                    } else {
                        alert("error");
                    }
                });
        }
    }

    function editOneDayColumnInput(order_id){
        $.getJSON("./ipd-dr-order-one-day-data.php", {order_id: order_id})
            .done(function(orders) {
                if(orders.length > 0){
                    addOneDayColumnInput(orders[0], (order)=>{
                        $('#oneDayForm_order_id').val(order.order_id);
                        $('#oneDayForm_order_date').val(order.order_date);
                        $('#oneDayForm_order_time').val(order.order_time);
                        $('#oneDayForm_order_confirm').val(order.order_confirm);
                        $.each(order.order_item_types, function(order_item_type_index, order_item_type) {
                            $.each(order_item_type.order_items, function(order_item_index, order_item) {
                                if(order_item.off_order_item_id != null){
                                    onclickOneDayOptionButton(event, true, order_item.order_item_type, '', {text_value: order_item.off_order_item_detail});
                                    $('[name=oneDayForm-off-order-item-id]').last().val(order_item.off_order_item_id);
                                } else {
                                    onclickOneDayOptionButton(event, true, order_item.order_item_type, '', {icode: order_item.icode, med_name: order_item.med_name, text_value: order_item.order_item_detail});
                                }
                                if(order_item.stat == 'Y'){
                                    $('[name=oneDayForm-stat-checkbox]').last().prop('checked', true);
                                }
                            });
                        });
                        $('#order_id_'+order.order_id+'_div').attr('tabindex','-1').focus();
                        document.getElementById('order_id_'+order.order_id+'_div').scrollIntoView();
                    });
                } else {
                    alert("ไม่สามารถแก้ไขรายการได้เนื่องจากรายการนี้ได้ถูกลบไปแล้ว");
                    load_order_date();
                }
            })
            .fail(function() {
                alert( "error" );
            });
    }

    function editContinuousColumnInput(order_id){
        $.getJSON("./ipd-dr-order-continuous-data.php", {order_id: order_id})
            .done(function(orders) {
                if(orders.length > 0){
                    addContinuousColumnInput(orders[0], (order)=>{
                        $('#continuousForm_order_id').val(order.order_id);
                        $('#continuousForm_order_date').val(order.order_date);
                        $('#continuousForm_order_time').val(order.order_time);
                        $('#continuousForm_order_confirm').val(order.order_confirm);
                        $.each(order.order_item_types, function(order_item_type_index, order_item_type) {
                            $.each(order_item_type.order_items, function(order_item_index, order_item) {
                                if(order_item.off_order_item_id != null){
                                    onclickContinuousOptionButton(event, true, order_item.order_item_type, '', {text_value: order_item.off_order_item_detail});
                                    $('[name=continuousForm-off-order-item-id]').last().val(order_item.off_order_item_id);
                                } else {
                                    onclickContinuousOptionButton(event, true, order_item.order_item_type, '', {icode: order_item.icode, med_name: order_item.med_name, text_value: order_item.order_item_detail});
                                }
                                if(order_item.stat == 'Y'){
                                    $('[name=continuousForm-stat-checkbox]').last().prop('checked', true);
                                }
                            });
                        });
                        $('#order_id_'+order.order_id+'_div').attr('tabindex','-1').focus();
                        document.getElementById('order_id_'+order.order_id+'_div').scrollIntoView();
                    });
                } else {
                    alert("ไม่สามารถแก้ไขรายการได้เนื่องจากรายการนี้ได้ถูกลบไปแล้ว");
                    load_order_date();
                }
            })
            .fail(function() {
                alert( "error" );
            });
    }

    function editProgressNoteColumnInput(progress_note_id){
        $.getJSON("./ipd-dr-order-progress-note-data.php", {progress_note_id: progress_note_id})
            .done(function(progress_notes) {
                if(progress_notes.length > 0){
                    addProgressNoteColumnInput(progress_notes[0], (progress_note)=>{
                        $('#progress_note_id').val(progress_note.progress_note_id);
                        $('#progress_note_date').val(progress_note.progress_note_date);
                        $('#progress_note_time').val(progress_note.progress_note_time);
                        $.each(progress_note.progress_note_item_types, function(progress_note_item_type_index, progress_note_item_type) {
                            $.each(progress_note_item_type.progress_note_items, function(progress_note_item_index, progress_note_item) {
                                onclickProgressNoteOptionButton(event, true, progress_note_item.progress_note_item_type, '', {text_value: progress_note_item.progress_note_item_detail});
                            });
                        });
                        $('#progress_note_id_'+progress_note.progress_note_id+'_div').attr('tabindex','-1').focus();
                        document.getElementById('progress_note_id_'+progress_note.progress_note_id+'_div').scrollIntoView();
                    });
                } else {
                    alert("ไม่สามารถแก้ไขรายการได้เนื่องจากรายการนี้ได้ถูกลบไปแล้ว");
                    load_order_date();
                }
            })
            .fail(function() {
                alert( "error" );
            });
    }

    function onclickOffOneDayOrderItem(event, order_item_id, order_item_detail){
        event.preventDefault();
        if($('[name=oneDayForm-off-order-item-id][value='+order_item_id+']').length == 0){
            if($('#addOneDayFormContainer').length == 0){
                addOneDayColumnInput(null, (order)=>{
                    onclickOneDayOptionButton(event, true, 'off', '', {text_value: order_item_detail});
                    $('[name=oneDayForm-off-order-item-id]').last().val(order_item_id);
                });
            } else {
                onclickOneDayOptionButton(event, true, 'off', '', {text_value: order_item_detail});
                $('[name=oneDayForm-off-order-item-id]').last().val(order_item_id);
            }
        }
    }

    function onclickOffContinuousOrderItem(event, order_item_id, order_item_detail){
        event.preventDefault();
        if($('[name=continuousForm-off-order-item-id][value='+order_item_id+']').length == 0){
            if($('#addContinuousFormContainer').length == 0){
                addContinuousColumnInput(null, (order)=>{
                    onclickContinuousOptionButton(event, true, 'off', '', {text_value: order_item_detail});
                    $('[name=continuousForm-off-order-item-id]').last().val(order_item_id);
                });
            } else {
                onclickContinuousOptionButton(event, true, 'off', '', {text_value: order_item_detail});
                $('[name=continuousForm-off-order-item-id]').last().val(order_item_id);
            }
        }
    }

    var oneDayForm_input_div = [];
    function addOneDayColumnInput(order, callback){
        // var order_item_types = [
        //     { order_item_type: 'Lab', option: ['CBC','BUN','Cr','Elyte','Ca','Mg','PO','PT','PTT','LFT','H/C x2','UA','UC','Trop-I','CK-MB','Other'] },
        //     { order_item_type: 'X-Ray', option: ['CXR','CXR include abdomen','Film acute abdomen','Abdomen supine','Abdomen upright','Film KUB','Pelvis','Other'] },
        //     { order_item_type: 'IV fluid',
        //         option: ['0.9%NaCl','0.45% NaCl','5%D/N/2','5%D/W','5%D/N/3','5%D/N/4','5%D/N/5','Acetar','Ringer lactate','10%D/N/2','10%D/N/3','10%D/N/4','10%D/N/5'],
        //         data: 'rate …… ml/hr' },
        //     { order_item_type: 'Record', option: [
        //         { record_type: 'V/S', q: ['2 hr','4 hr','8 hr'], keep: ['MAP > 65 mmHg','BP > 90/60','Other'] },
        //         { record_type: 'I/O', q: ['2 hr','4 hr','8 hr'], keep: ['Urine > 50 ml','>100 ml','200 ml','Other'] },
        //         { record_type: 'CVP', q: ['2 hr','4 hr','8 hr'], keep: ['8-12 mmHg','Other'] },
        //         { record_type: 'Other', q: ['2 hr','4 hr','8 hr'] },
        //         ] },
        //     { order_item_type: 'Med', check: {text: 'Stat', type: 'check'} },
        //     { order_item_type: 'Retain', option: ['Foley cath','NG'] },
        //     { order_item_type: 'Home Medication' },
        //     { order_item_type: 'Other' },
        //     ];
        let length = $('#addOneDayFormContainer').length;
        if(length == 0){
            $.get("./ipd-nurse-order-one-day-form.php")
            .done(function(html) {
                if(order != null) {
                    editing_oneday_order_inner_div = $('#order_id_'+order.order_id+'_inner_div').html();
                    $('#order_id_'+order.order_id+'_inner_div').html(html);
                    $('#order_id_'+order.order_id+'_action_row_div').hide();
                } else {
                    $('.one-day-column.today').append(html);
                }

                oneDayForm_input_div = {
                    // '#oneDayForm-note-input-div'            : $('#oneDayForm-note-input-div').html(),
                    // '#oneDayForm-off-input-div'             : $('#oneDayForm-off-input-div').html(),
                    // '#oneDayForm-lab-input-div'             : $('#oneDayForm-lab-input-div').html(),
                    // '#oneDayForm-xray-input-div'            : $('#oneDayForm-xray-input-div').html(),
                    // '#oneDayForm-ivfluid-input-div'         : $('#oneDayForm-ivfluid-input-div').html(),
                    // '#oneDayForm-record-input-div'          : $('#oneDayForm-record-input-div').html(),
                    // '#oneDayForm-med-input-div'             : $('#oneDayForm-med-input-div').html(),
                    // '#oneDayForm-retain-input-div'          : $('#oneDayForm-retain-input-div').html(),
                    // '#oneDayForm-home-medication-input-div' : $('#oneDayForm-home-medication-input-div').html(),
                    '#oneDayForm-other-input-div'           : $('#oneDayForm-other-input-div').html(),
                    // '#oneDayForm-discharge-input-div'       : $('#oneDayForm-discharge-input-div').html(),
                };

                // $('#oneDayForm-note-input-div').html('');
                // $('#oneDayForm-off-input-div').html('');
                // $('#oneDayForm-lab-input-div').html('');
                // $('#oneDayForm-xray-input-div').html('');
                // $('#oneDayForm-ivfluid-input-div').html('');
                // $('#oneDayForm-record-input-div').html('');
                // $('#oneDayForm-med-input-div').html('');
                // $('#oneDayForm-retain-input-div').html('');
                // $('#oneDayForm-home-medication-input-div').html('');
                $('#oneDayForm-other-input-div').html('');
                // $('#oneDayForm-discharge-input-div').html('');

                if(callback != null) {
                    callback(order);
                }
            })
            .fail(function() {
                alert( "error" );
            });
        } else if(length > 0){
            if(callback != null) {
                callback(order);
            }
        }
    }

    var continuousForm_input_div = [];
    function addContinuousColumnInput(order, callback){
        // var order_item_types = [
        //     { order_item_type: 'Food', option: [
        //         { record_type: 'Diet', option: ['Regular','Soft','liquid','NPO'] },
        //         { record_type: 'Request', option: ['DM','HT','CKD'] },
        //         { record_type: 'BD', option: ['1:1','1.5:1','2:1','100x4','150x4','200x4','300x4','400x4','Other'] },
        //         { record_type: 'Other' },
        //         ] },
        //     { order_item_type: 'Activity', option: [
        //         { record_type: 'Dressing wound', option: ['OD','BID'] },
        //         { record_type: 'Ambulate', option: ['Absolute bedrest','ข้างเตียง'] },
        //         { record_type: 'BD', option: ['1:1','1.5:1','2:1','100x4','150x4','200x4','300x4','400x4','Other'] },
        //         { record_type: 'Other' },
        //         ] },
        //     { order_item_type: 'Record', option: [
        //         { record_type: 'V/S', q: ['2 hr','4 hr','8 hr'], keep: ['MAP > 65 mmHg','BP > 90/60','Other'] },
        //         { record_type: 'I/O', q: ['2 hr','4 hr','8 hr'], keep: ['Urine > 50 ml','>100 ml','200 ml','Other'] },
        //         { record_type: 'CVP', q: ['2 hr','4 hr','8 hr'], keep: ['8-12 mmHg','Other'] },
        //         { record_type: 'Other', q: ['2 hr','4 hr','8 hr'] },
        //         ] },
        //     { order_item_type: 'Med' },
        //     { order_item_type: 'Other' },
        //     ];
        let length = $('#addContinuousFormContainer').length;
        if(length == 0){
            $.get("./ipd-nurse-order-continuous-form.php")
            .done(function(html) {
                if(order != null) {
                    editing_continuous_order_inner_div = $('#order_id_'+order.order_id+'_inner_div').html();
                    $('#order_id_'+order.order_id+'_inner_div').html(html);
                    $('#order_id_'+order.order_id+'_action_row_div').hide();
                } else {
                    $('.continuous-column.today').append(html);
                }

                continuousForm_input_div = {
                    // '#continuousForm-note-input-div'     : $('#continuousForm-note-input-div').html(),
                    // '#continuousForm-off-input-div'      : $('#continuousForm-off-input-div').html(),
                    // '#continuousForm-food-input-div'     : $('#continuousForm-food-input-div').html(),
                    // '#continuousForm-activity-input-div' : $('#continuousForm-activity-input-div').html(),
                    // '#continuousForm-record-input-div'   : $('#continuousForm-record-input-div').html(),
                    // '#continuousForm-med-input-div'      : $('#continuousForm-med-input-div').html(),
                    '#continuousForm-other-input-div'    : $('#continuousForm-other-input-div').html(),
                };

                // $('#continuousForm-note-input-div').html('');
                // $('#continuousForm-off-input-div').html('');
                // $('#continuousForm-food-input-div').html('');
                // $('#continuousForm-activity-input-div').html('');
                // $('#continuousForm-record-input-div').html('');
                // $('#continuousForm-med-input-div').html('');
                $('#continuousForm-other-input-div').html('');

                if(callback != null) {
                    callback(order);
                }
            })
            .fail(function() {
                alert( "error" );
            });
        } else if(length > 0){
            if(callback != null) {
                callback(order);
            }
        }
    }

    var progressNoteForm_input_div = [];
    function addProgressNoteColumnInput(progress_note, callback){
        // var order_item_types = [
        //     { order_item_type: 'Anemia', option: ['Due to Blood Loss','Chronic Disease','Neoplasm'] },
        //     { order_item_type: 'Hypo', option: ['HypoK','HypoMg','HypoNa','Metabolic Acidosis'] },
        //     { order_item_type: 'Other' },
        //     ];
        let length = $('#addProgressNoteFormContainer').length;
        if(length == 0){
            $.get("./ipd-dr-order-progress-note-form.php")
            .done(function(html) {
                if(callback != null) {
                    editing_progress_note_inner_div = $('#progress_note_id_'+progress_note.progress_note_id+'_inner_div').html();
                    $('#progress_note_id_'+progress_note.progress_note_id+'_inner_div').html(html);
                    $('#progress_note_id_'+progress_note.progress_note_id+'_action_row_div').hide();
                } else {
                    $('.progress-note-column.today').append(html);
                }

                progressNoteForm_input_div = {
                    '#progressNoteForm-note-input-div'     : $('#progressNoteForm-note-input-div').html(),
                };

                $('#progressNoteForm-note-input-div').html('');

                if(callback != null) {
                    callback(progress_note);
                }
            })
            .fail(function() {
                alert( "error" );
            });
        } else if(length > 0){
            if(callback != null) {
                callback(order);
            }
        }
    }

    const ONE_DAY_ORDER_ITEM_TYPES = ['note','off','lab','xray','ivfluid','record','med','retain','other', 'discharge','home-medication'];
    const ONE_DAY_ORDER_ITEM_TYPE_NAMES = {
        'note': 'Note',
        'off': 'Off',
        'lab': 'Lab',
        'xray': 'X-Ray',
        'ivfluid': 'IV Fluid',
        'record': 'Record',
        'med': 'Medication',
        'retain': 'Retain',
        'other': 'Other',
        'discharge': 'Discharge',
        'home-medication': 'Home Medication',
    };

    function addOneDayOrder(event){
        event.preventDefault();
        var order_id = $('#oneDayForm_order_id').val();
        if(order_id != null && order_id != ''){
            // order_date = $('#oneDayForm_order_date').val();
            // order_time = $('#oneDayForm_order_time').val();
            order_confirm = $('#oneDayForm_order_confirm').val();
        } else {
            // var now = moment();
            // order_date = now.format("YYYY-MM-DD");
            // order_time = now.format("HH:mm");
            order_confirm = 'N';
        }
        var now = moment();
        order_date = now.format("YYYY-MM-DD");
        order_time = now.format("HH:mm");

        var order_item_types = [];
        ONE_DAY_ORDER_ITEM_TYPES.forEach(function(order_item_type){
            var order_items = [];
            $('[name="oneDayForm-'+order_item_type+'-text"]').each(function(){
                let icodeInput = $(this).closest('.order-input-group').find('[name=oneDayForm-icode]');
                if($.trim(this.value) != '' || (icodeInput.length > 0 && icodeInput.get(0).value.length > 0)){
                    order_items.push(
                        {
                            'order_item_id': 0,
                            'order_item_type': order_item_type,
                            'order_item_detail': ((order_item_type == 'off') ? null : $.trim(this.value)),
                            'off_order_item_detail': ((order_item_type == 'off') ? this.value : null),
                            'stat': (($(this).closest('.order-input-group').find('[name=oneDayForm-stat-checkbox]').is(":checked")) ? 'Y':'N'),
                            'off_order_item_id': $(this).closest('.order-input-group').find('[name=oneDayForm-off-order-item-id]').val(),
                            'icode': icodeInput.val(),
                        }
                    );
                }
            });
            if(order_items.length > 0){
                order_item_types.push({order_item_type: order_item_type, order_items: order_items});
            }
        });

        if(order_item_types.length > 0){
            var one_day_order = {
                'order_id': order_id,
                'an': IPD_ORDER_AN,
                'order_date': order_date,
                'order_time': order_time,
                'order_confirm': order_confirm,
                'order_type': 'oneday',
                'order_owner_type': ORDER_OWNER_TYPE,
                'order_doctor': IPD_ORDER_DOCTORCODE,
                'order_item_types': order_item_types
            };
            // console.log('one_day_order',one_day_order);

            $.post("./ipd-dr-order-one-day-save.php", one_day_order)
                .done(function(result) {
                    // result = JSON.parse(result);
                    // one_day_order = result[0];
                    $('#addOneDayFormContainer').remove();
                    // if($('#order_id_'+one_day_order.order_id+'_div').length > 0){
                    //     $('#order_id_'+one_day_order.order_id+'_div').html(oneday_data_to_text(one_day_order));
                    // } else {
                    //     $('#one-day-column-add-link').before(oneday_data_to_text(one_day_order));
                    // }
                    // document.getElementById('order_id_'+one_day_order.order_id+'_div').scrollIntoView();

                    // $('.one-day-column.today').append('<div class="text-right d-print-none" id="one-day-column-add-link"><a href="#" onclick="event.preventDefault(); addOneDayColumnInput();">+Add</a></div>');
                    onchange_select_order_date(event);
                })
                .fail(function(response) {
                    // console.log(response);
                    if(response.status == '409'){
                        alert("รายการนี้ได้รับการยืนยันไปก่อนหน้านี้แล้ว");
                        $('#addOneDayFormContainer').remove();
                        onchange_select_order_date(event);
                        //load_order_date();
                    } else {
                        alert("error");
                    }
                });
        } else {
            closeOneDayColumnInput();
        }
        return false;
    }

    const CONTINUOUS_ORDER_ITEM_TYPES = ['note','off','food','activity','record','med','other'];
    const CONTINUOUS_ORDER_ITEM_TYPE_NAMES = {
        'note': 'Note',
        'off': 'Off',
        'food': 'Food',
        'activity': 'Activity',
        'record': 'Record',
        'med': 'Medication',
        'other': 'Other',
    };

    function addContinuousOrder(event){
        event.preventDefault();
        var order_id = $('#continuousForm_order_id').val();
        if(order_id != null && order_id != ''){
            // order_date = $('#continuousForm_order_date').val();
            // order_time = $('#continuousForm_order_time').val();
            order_confirm = $('#continuousForm_order_confirm').val();
        } else {
            // var now = moment();
            // order_date = now.format("YYYY-MM-DD");
            // order_time = now.format("HH:mm");
            order_confirm = 'N';
        }

        var now = moment();
        order_date = now.format("YYYY-MM-DD");
        order_time = now.format("HH:mm");

        var order_item_types = [];
        CONTINUOUS_ORDER_ITEM_TYPES.forEach(function(order_item_type){
            var order_items = [];
            $('[name="continuousForm-'+order_item_type+'-text"]').each(function(){
                let icodeInput = $(this).closest('.order-input-group').find('[name=continuousForm-icode]');
                if($.trim(this.value) != '' || (icodeInput.length > 0 && icodeInput.get(0).value.length > 0)){
                    order_items.push(
                        {
                            'order_item_id': 0,
                            'order_item_type': order_item_type,
                            'order_item_detail': ((order_item_type == 'off') ? null : $.trim(this.value)),
                            'off_order_item_detail': ((order_item_type == 'off') ? this.value : null),
                            'stat': (($(this).closest('.order-input-group').find('[name=continuousForm-stat-checkbox]').is(":checked")) ? 'Y':'N'),
                            'off_order_item_id': $(this).closest('.order-input-group').find('[name=continuousForm-off-order-item-id]').val(),
                            'icode': icodeInput.val(),
                        }
                    );
                }
            });
            if(order_items.length > 0){
                order_item_types.push({order_item_type: order_item_type, order_items: order_items});
            }
        });

        if(order_item_types.length > 0){
            var continuous_order = {
                'order_id': order_id,
                'an': IPD_ORDER_AN,
                'order_date': order_date,
                'order_time': order_time,
                'order_confirm': order_confirm,
                'order_type': 'continuous',
                'order_owner_type': ORDER_OWNER_TYPE,
                'order_doctor': IPD_ORDER_DOCTORCODE,
                'order_item_types': order_item_types
            };
            // console.log('continuous_order',continuous_order);

            $.post("./ipd-dr-order-continuous-save.php", continuous_order)
                .done(function(result) {
                    // result = JSON.parse(result);
                    // continuous_order = result[0];
                    // // continuous_order.order_id = order_id;
                    $('#addContinuousFormContainer').remove();
                    // if($('#order_id_'+continuous_order.order_id+'_div').length > 0){
                    //     $('#order_id_'+continuous_order.order_id+'_div').html(continuous_data_to_text(continuous_order));
                    // } else {
                    //     $('#continuous-column-add-link').before(continuous_data_to_text(continuous_order));
                    // }
                    // document.getElementById('order_id_'+continuous_order.order_id+'_div').scrollIntoView();

                    // $('.continuous-column.today').append('<div class="text-right d-print-none" id="continuous-column-add-link"><a href="#" onclick="event.preventDefault(); addContinuousColumnInput();">+Add</a></div>');
                    onchange_select_order_date(event);
                })
                .fail(function(response) {
                    if(response.status == '409'){
                        alert("รายการนี้ได้รับการยืนยันไปก่อนหน้านี้แล้ว");
                        $('#addContinuousFormContainer').remove();
                        onchange_select_order_date(event);
                    } else {
                        alert("error");
                    }
                });
        } else {
            closeContinuousColumnInput();
        }
        return false;
    }

    const PROGRESS_NOTE_ITEM_TYPES = ['note'];
    const PROGRESS_NOTE_ITEM_TYPE_NAMES = {
        'note': 'Note',
    };

    function addProgressNoteOrder(event){
        event.preventDefault();
        var progress_note_id = $('#progress_note_id').val();
        let order_date_select = $('#order_date_select');
        var isToday = (order_date_select.find(':selected').data('isToday') == 1);
        // if(progress_note_id != null && progress_note_id != ''){
        //     progress_note_date = $('#progress_note_date').val();
        //     progress_note_time = $('#progress_note_time').val();
        // } else {
            var now = moment();
            var progress_note_date = now.format("YYYY-MM-DD");
            var progress_note_time = now.format("HH:mm");
            if(!isToday){
                //ให้วันที่เป็นวันเดียวกันกับที่เลือกอยู่บนหน้าจอ
                progress_note_date = order_date_select.val();
                progress_note_time = '23:59:59';
            }
        // }

        var progress_note_item_types = [];
        PROGRESS_NOTE_ITEM_TYPES.forEach(function(progress_note_item_type){
            var progress_note_items = [];
            $('[name="progressNoteForm-'+progress_note_item_type+'-text"]').each(function(){
                if($.trim(this.value) != ''){
                    progress_note_items.push(
                        {
                            'progress_note_item_id': 0,
                            'progress_note_item_type': progress_note_item_type,
                            'progress_note_item_detail': $.trim(this.value),
                        }
                    );
                }
            });
            if(progress_note_items.length > 0){
                progress_note_item_types.push({progress_note_item_type: progress_note_item_type, progress_note_items: progress_note_items});
            }
        });

        if(progress_note_item_types.length > 0){
            var progress_note = {
                'progress_note_id': progress_note_id,
                'an': IPD_ORDER_AN,
                'progress_note_date': progress_note_date,
                'progress_note_time': progress_note_time,
                'progress_note_owner_type': ORDER_OWNER_TYPE,
                'progress_note_doctor': IPD_ORDER_DOCTORCODE,
                'progress_note_item_types': progress_note_item_types,
                'is_progress_note_for_past_date': !isToday,
            };
            // console.log('progress_note',progress_note);

            $.post("./ipd-dr-order-progress-note-save.php", progress_note)
                .done(function(progress_note_id) {
                    // progress_note.progress_note_id = progress_note_id;
                    $('#addProgressNoteFormContainer').remove();
                    // if($('#progress_note_id_'+progress_note.progress_note_id+'_div').length > 0){
                    //     $('#progress_note_id_'+progress_note.progress_note_id+'_div').html(progress_note_data_to_text(progress_note));
                    // } else {
                    //     $('#progress-note-column-add-link').before(progress_note_data_to_text(progress_note));
                    // }
                    // document.getElementById('progress_note_id_'+progress_note.progress_note_id+'_div').scrollIntoView();

                    // $('.progress-note-column.today').append('<div class="text-right d-print-none" id="progress-note-column-add-link"><a href="#" onclick="event.preventDefault(); addProgressNoteColumnInput();">+Add</a></div>');
                    onchange_select_order_date(event);
                })
                .fail(function() {
                    alert( "error" );
                });
        } else {
            closeProgressNoteColumnInput();
        }
        return false;
    }

    function index_plan_action_to_text(order_date, li, index_plan_action){
        let plan_datetime = moment(index_plan_action.plan_date + ' ' + index_plan_action.plan_time, "YYYY-MM-DD HH:mm");
        let action_datetime = moment(index_plan_action.action_date + ' ' + index_plan_action.action_time, "YYYY-MM-DD HH:mm");
        let plan_datetime_display = index_plan_action.plan_date != null ? plan_datetime.format("DD/MM/") + (parseInt(plan_datetime.format("YYYY"),10)+543) + (index_plan_action.plan_time != null ? ' ' + plan_datetime.format("HH:mm") : '') : '';
        let plan_time_display = (index_plan_action.plan_date != null && index_plan_action.plan_time != null) ? plan_datetime.format("HH:mm") : 'ไม่ระบุเวลา';
        let action_datetime_display = index_plan_action.action_date != null ? action_datetime.format("DD/MM/") + (parseInt(action_datetime.format("YYYY"),10)+543) + (index_plan_action.action_time != null ? ' ' + action_datetime.format("HH:mm") : '') : '';
        let action_time_display = (index_plan_action.action_date != null && index_plan_action.action_time != null) ? action_datetime.format("HH:mm") : 'ไม่ระบุเวลา';

        let plan_action_span = $('<span/>',{
            style: 'cursor: pointer;',
            class: 'badge ml-1 badge-secondary'/* + (index_plan_action.action_time != null ? 'badge-success' : 'badge-warning') */,
            html: [
                $('<span/>',{
                    // style: 'color:blue;',
                    text: ((order_date == index_plan_action.plan_date) ? plan_time_display : plan_datetime_display),
                }),
                $('<span/>',{
                    // class: 'text-danger',
                    style: 'color:red;',
                    text: (index_plan_action.action_time != null ? ' (' + ((order_date == index_plan_action.action_date) ? action_time_display : action_datetime_display) + ')' : ''),
                }),
                $('<span/>',{
                    html: (isBlankOrNullOrWhiteSpace(index_plan_action.action_result) ? '' : ' <i class="fa fa-info-circle" aria-hidden="true"></i>'),
                }),
                // $('<span/>',{
                //     html: (isBlankOrNullOrWhiteSpace(index_plan_action.action_report_back) ? '' : ' <i class="fas fa-user-md" aria-hidden="true"></i>'),
                // }),
            ],
            // html: ((order_date == index_plan_action.plan_date) ? plan_time_display : plan_datetime_display) +
            //         (action_time_display != '' ? ' (' + ((order_date == index_plan_action.action_date) ? action_time_display : action_datetime_display) + ')' : ''),
            // html: (action_time_display != '' ? ((order_date == index_plan_action.action_date) ? action_time_display : action_datetime_display) : ((order_date == index_plan_action.plan_date) ? plan_time_display : plan_datetime_display)),
            // title: plan_datetime_display + (action_datetime_display != '' ? ' (' + action_datetime_display + ')' : '') + '\n' + index_plan_action.action_result,
            title: (isBlankOrNullOrWhiteSpace(index_plan_action.action_result) ? '' : index_plan_action.action_result),
            // "data-toggle":"tooltip",
            "data-placement":"right",
            // "data-container":"body",
            // "data-html":true,
            click: function(event) {
                onclickEditIndexPlan(event, index_plan_action.plan_id);
            },
        });
        plan_action_span.tooltip();

        li.append(plan_action_span);
    }

    function oneday_data_to_text(one_day_order){
        order_datetime = moment(one_day_order.order_date + ' ' + one_day_order.order_time, "YYYY-MM-DD HH:mm");
        let pre_order_date_time = ((one_day_order.pre_order_date != null) ? moment(one_day_order.pre_order_date + ' ' + one_day_order.pre_order_time, "YYYY-MM-DD HH:mm") : null);
        var isToday = moment().format("YYYY-MM-DD") == order_datetime.format("YYYY-MM-DD");
        var order_display = $('<div/>', {
            id: 'order_id_'+one_day_order.order_id+'_div',
            // class: (one_day_order.order_owner_type == 'nurse' ? "d-print-none":""),
            style: (((one_day_order.order_owner_type == 'nurse' && one_day_order.order_confirm != 'Y')
                || (one_day_order.order_owner_type == 'doctor' && one_day_order.nurse_accept_time == null)) ? 'background-color: #ffeeba;' : ''),
            // class: (one_day_order.order_confirm != 'Y') ? 'bg-secondary' : '',
            html: [
                $('<span/>', {
                    text: order_datetime.format("DD/MM/") + (parseInt(order_datetime.format("YYYY"),10)+543) + order_datetime.format(", HH:mm") + ' น.',
                }),
                $('<span/>', {
                    class: 'ml-1 ' + ((one_day_order.order_owner_type == 'nurse') ? 'bg-secondary' : ''),
                    text: (one_day_order.order_owner_type == 'doctor' ? "(Doctor's Order)":"") + (one_day_order.order_owner_type == 'nurse' ? "(Nurse's Order)":""),
                }),
                $('<span/>', {
                    text: ((pre_order_date_time != null) ? ' (บันทึกไว้เมื่อ: ' + pre_order_date_time.format("DD/MM/") + (parseInt(pre_order_date_time.format("YYYY"),10)+543) + pre_order_date_time.format(", HH:mm") + ' น.)' : ''),
                    class: 'small text-info',
                }),
            ]
        });

        var inner_div = $('<div/>', { id: 'order_id_'+one_day_order.order_id+'_inner_div'});
        if(one_day_order.order_item_types.length > 0){
            one_day_order.order_item_types.forEach(function(order_item_type){
                if(order_item_type.order_item_type == 'home-medication'
                    || order_item_type.order_item_type == 'discharge' ){
                    inner_div.append($('<div/>', { class: 'font-weight-bold', text: ONE_DAY_ORDER_ITEM_TYPE_NAMES[order_item_type.order_item_type] }));
                }
                var ul = $('<ul/>', {
                    class: "dash",
                    style: "white-space: pre-wrap;",
                });
                order_item_type.order_items.forEach(function(order_item){
                    // ul.append(
                    // $('<li/>', {
                    //     html: ((order_item.order_item_type == 'off') ? ('<b>Off</b> ' + order_item.off_order_item_detail) : (order_item.order_item_detail + ((order_item.stat == 'Y') ? ' <b>(Stat)</b>' : '') + '<a class="float-right" href="#" onclick="onclickOffOneDayOrderItem(\''+order_item.order_item_id+'\',\''+order_item.order_item_detail+'\')">off</a>')),
                    // }));
                    var li = $('<li/>',{
                        class: 'clearfix',
                    });
                    if(order_item.order_item_type == 'off'){
                        li.append('<span class="font-weight-bold">Off</span> ');
                        li.append((order_item.off_icode == null ? '' : (order_item.off_med_name + (order_item.off_order_item_detail != '' ? '\n' : ''))) + order_item.off_order_item_detail);
                    } else {
                        li.append($('<span/>',{
                            text: (order_item.icode == null ? '' : (order_item.med_name + (order_item.order_item_detail != '' ? '\n' : ''))) + order_item.order_item_detail,
                            style: ((order_item.off_by_order_item_id != null) ? ' text-decoration: line-through;' : '') + ((order_item.order_item_type == 'med' || order_item.order_item_type == 'home-medication' || order_item.order_item_type == 'ivfluid') ? " color: blue;":"")
                        }));
                        if(order_item.stat == 'Y'){
                            li.append(' <span class="font-weight-bold">(Stat)</span>');
                        }
                        if(order_item.off_by_order_item_id != null){
                            li.append(' <span class="font-weight-bold">(Off)</span>');
                        }
                        if(order_item.allergy_agent_symptom != null){
                            li.append('<br class="d-print-none"/>');
                            li.append($('<small/>',{
                                class: 'font-weight-bold text-danger d-print-none',
                                role: "button",
                                html: 'แพ้ยา/เฝ้าระวัง',
                                title: order_item.allergy_agent_symptom,
                            }));
                        }
                        // if(IPD_ORDER_OFF && isToday && order_item.off_by_order_item_id == null && one_day_order.order_confirm == 'Y' && one_day_order.order_owner_type == 'nurse'){
                        //     li.append($('<a/>', {
                        //         text: 'Off',
                        //         class: "float-right",
                        //         href: '#off_'+order_item.order_item_id/*+'_'+order_item.order_item_detail*/,
                        //         click: function(event) {
                        //             onclickOffOneDayOrderItem(event, order_item.order_item_id, (order_item.icode == null ? '' : (order_item.med_name + (order_item.order_item_detail != '' ? '\n' : ''))) + order_item.order_item_detail);
                        //         },
                        //     }));
                        // }
                    }
                    if(order_item.index_plan_actions != null && order_item.index_plan_actions.length > 0){
                        order_item.index_plan_actions.forEach(function(index_plan_action){
                            index_plan_action_to_text(one_day_order.order_date, li, index_plan_action);
                        });
                    }
                    if(IPD_NURSE_INDEX_ADD && /*IPD_NURSE_INDEX_ADD && */ /*order_item.off_by_order_item_id == null && */(one_day_order.order_owner_type == 'doctor' || one_day_order.order_owner_type == 'nurse')){
                        li.append($('<a/>', {
                            text: '+Plan',
                            class: "float-right d-print-none",
                            href: '#index_'+order_item.order_item_id/*+'_'+order_item.order_item_detail*/,
                            click: function(event) {
                                onclickAddIndexPlanOrderItem(event, order_item.order_item_id, order_item.order_item_detail);
                            },
                        }));
                    }
                    ul.append(li);
                });
                inner_div.append(ul);
            });
        }
        order_display.append(inner_div);
        order_display.append($('<div/>', {
            html:
                (one_day_order.order_owner_type == 'doctor') ?
                [
                    $('<span/>', { class: "d-print-none", text: `${one_day_order.order_doctor_name}, `}),
                    $('<span/>', { class: "d-none d-print-block", text: `${one_day_order.order_doctor_name}${(one_day_order.order_owner_type == 'doctor' ? ` (${one_day_order.doctor_licenseno})`:'')}, `}),
                    $('<span/>', { class: "text-nowrap", text: order_datetime.format("DD/MM/") + (parseInt(order_datetime.format("YYYY"),10)+543) + order_datetime.format(", HH:mm") + ' น.', }),
                ] :
                [
                    $('<span/>', { text: `${one_day_order.order_doctor_name}, `}),
                    $('<span/>', { class: "text-nowrap", text: order_datetime.format("DD/MM/") + (parseInt(order_datetime.format("YYYY"),10)+543) + order_datetime.format(", HH:mm") + ' น.', }),
                ]
            ,
            class: "text-right small",
        }));
        if(one_day_order.nurse_accept_time != null){
            nurse_accept_time = moment(one_day_order.nurse_accept_time + ' ' + one_day_order.nurse_accept_time, "YYYY-MM-DD HH:mm");
            order_display.append($('<div/>', {
                html: [
                    $('<span/>', { text: '(RN) ' + one_day_order.nurse_accept_name + ', ', }),
                    $('<span/>', { class: "text-nowrap", text: nurse_accept_time.format("DD/MM/") + (parseInt(nurse_accept_time.format("YYYY"),10)+543) + nurse_accept_time.format(", HH:mm") + ' น.', }),
                ],
                class: "text-right small",
            }));
        }
        if(one_day_order.pharmacist_done_time != null){
            pharmacist_done_time = moment(one_day_order.pharmacist_done_time + ' ' + one_day_order.pharmacist_done_time, "YYYY-MM-DD HH:mm");
            order_display.append($('<div/>', {
                html: [
                    $('<span/>', { text: '(RX) ' + one_day_order.pharmacist_done_name + ', ', }),
                    $('<span/>', { class: "text-nowrap", text: pharmacist_done_time.format("DD/MM/") + (parseInt(pharmacist_done_time.format("YYYY"),10)+543) + pharmacist_done_time.format(", HH:mm") + ' น.', }),
                ],
                class: "text-right small",
            }));
        } else if(one_day_order.pharmacist_accept_time != null){
            pharmacist_accept_time = moment(one_day_order.pharmacist_accept_time + ' ' + one_day_order.pharmacist_accept_time, "YYYY-MM-DD HH:mm");
            order_display.append($('<div/>', {
                html: [
                    $('<span/>', { text: '(ห้องยารับรายการ) ' + one_day_order.pharmacist_accept_name + ', ', }),
                    $('<span/>', { class: "text-nowrap", text: pharmacist_accept_time.format("DD/MM/") + (parseInt(pharmacist_accept_time.format("YYYY"),10)+543) + pharmacist_accept_time.format(", HH:mm") + ' น.', }),
                ],
                class: "text-right small",
            }));
        }
        if(isToday){
            if(one_day_order.order_confirm != 'Y' && one_day_order.order_owner_type == ORDER_OWNER_TYPE){
                var action_row = $('<div/>', { id: 'order_id_'+one_day_order.order_id+'_action_row_div', class: "text-right font-weight-bold d-print-none" });
                if(IPD_ORDER_CONFIRM){
                    action_row.append(
                        $('<a/>', {
                            class: 'ml-1',
                            href: '#confirm_order_id='+one_day_order.order_id,
                            text: '[Confirm]',
                        }).click(function(event){
                            event.preventDefault();
                            confirmOneDayOrder(one_day_order.order_id);
                        })
                    );
                }
                if(IPD_ORDER_EDIT){
                    action_row.append(
                        $('<a/>', {
                            class: 'ml-1',
                            href: '#edit_order_id='+one_day_order.order_id,
                            text: '[Edit]',
                        }).click(function(event){
                            event.preventDefault();
                            editOneDayColumnInput(one_day_order.order_id);
                        })
                    );
                }
                if(IPD_ORDER_REMOVE){
                    action_row.append(
                        $('<a/>', {
                            class: 'ml-1',
                            href: '#delete_order_id='+one_day_order.order_id,
                            text: '[Delete]',
                        }).click(function(event){
                            event.preventDefault();
                            deleteOneDayOrder(one_day_order.order_id);
                        }),
                    );
                }
                order_display.append(action_row);
            }
        } else {
            if(one_day_order.order_confirm != 'Y'){
                var action_row = $('<div/>', { class: "text-right" });
                action_row.append(
                    $('<span/>', {
                        class: 'ml-1 text-warning',
                        text: '[Not Confirmed]',
                    })
                );
                order_display.append(action_row);
            }
        }

        if(IPD_ORDER_ACCEPT && one_day_order.order_owner_type == 'doctor' && one_day_order.nurse_accept_time == null){
            var action_row = $('<div/>', { class: "text-right font-weight-bold d-print-none" });
            action_row.append(
                $('<a/>', {
                    class: 'ml-1',
                    href: '#accept_order_id='+one_day_order.order_id,
                    text: '[รับรายการ]',
                }).click(function(event){
                    event.preventDefault();
                    acceptOneDayOrder(one_day_order.order_id);
                })
            );
            order_display.append(action_row);
        }
        order_display.append($('<hr/>'));
        return order_display;
    }

    function continuous_data_to_text(continuous_order){
        order_datetime = moment(continuous_order.order_date + ' ' + continuous_order.order_time, "YYYY-MM-DD HH:mm");
        let pre_order_date_time = ((continuous_order.pre_order_date != null) ? moment(continuous_order.pre_order_date + ' ' + continuous_order.pre_order_time, "YYYY-MM-DD HH:mm") : null);
        var isToday = moment().format("YYYY-MM-DD") == order_datetime.format("YYYY-MM-DD");
        var order_display = $('<div/>', {
            id: 'order_id_'+continuous_order.order_id+'_div',
            style: (((continuous_order.order_owner_type == 'nurse' && continuous_order.order_confirm != 'Y')
                || (continuous_order.order_owner_type == 'doctor' && continuous_order.nurse_accept_time == null)) ? 'background-color: #ffeeba;' : ''),
            // class: (continuous_order.order_confirm != 'Y') ? 'bg-secondary' : '',
            html: [
                $('<span/>', {
                    text: order_datetime.format("DD/MM/") + (parseInt(order_datetime.format("YYYY"),10)+543) + order_datetime.format(", HH:mm") + ' น.'
                    + (continuous_order.order_owner_type == 'doctor' ? " (Doctor's Order)":"") + (continuous_order.order_owner_type == 'nurse' ? " (Nurse's Order)":""),
                }),
                $('<span/>', {
                    text: ((pre_order_date_time != null) ? ' (บันทึกไว้เมื่อ: ' + pre_order_date_time.format("DD/MM/") + (parseInt(pre_order_date_time.format("YYYY"),10)+543) + pre_order_date_time.format(", HH:mm") + ' น.)' : ''),
                    class: 'small text-info',
                }),
            ]
        });

        var inner_div = $('<div/>', { id: 'order_id_'+continuous_order.order_id+'_inner_div'});
        if(continuous_order.order_item_types.length > 0){
            continuous_order.order_item_types.forEach(function(order_item_type){
                // if(order_item_type.order_item_type == 'home-medication'
                //     || order_item_type.order_item_type == 'discharge' ){
                //     inner_div.append($('<div/>', { /*class: 'font-weight-bold',*/ text: CONTINUOUS_ORDER_ITEM_TYPE_NAMES[order_item_type.order_item_type] }));
                // }
                var ul = $('<ul/>', {
                    class: "dash",
                    style: "white-space: pre-wrap;",
                });
                order_item_type.order_items.forEach(function(order_item){
                    // var li = $('<li/>', {
                    //     html: ((order_item.order_item_type == 'off') ? ('<b>Off</b> ' + order_item.off_order_item_detail)
                    //             : (order_item.order_item_detail + ((order_item.stat == 'Y') ? ' <b>(Stat)</b>' : '')
                    //                 + (isToday ? ('<a class="float-right" href="#" onclick="onclickOffContinuousOrderItem(\''+order_item.order_item_id+'\',\''+order_item.order_item_detail+'\')">off</a>') : '')
                    //     )),
                    // });
                    var li = $('<li/>',{
                        class: 'clearfix',
                    });
                    if(order_item.order_item_type == 'off'){
                        li.append('<span class="font-weight-bold">Off</span> ');
                        li.append((order_item.off_icode == null ? '' : (order_item.off_med_name + (order_item.off_order_item_detail != '' ? '\n' : ''))) + order_item.off_order_item_detail);
                    } else {
                        li.append($('<span/>',{
                            text: (order_item.icode == null ? '' : (order_item.med_name + (order_item.order_item_detail != '' ? '\n' : ''))) + order_item.order_item_detail,
                            style: ((order_item.off_by_order_item_id != null) ? 'text-decoration: line-through;' : '') + ((order_item.order_item_type == 'med' || order_item.order_item_type == 'ivfluid') ? " color: blue;":"")
                        }));
                        if(order_item.stat == 'Y'){
                            li.append(' <span class="font-weight-bold">(Stat)</span>');
                        }
                        if(order_item.off_by_order_item_id != null){
                            li.append(' <span class="font-weight-bold">(Off)</span>');
                        }
                        if(order_item.allergy_agent_symptom != null){
                            li.append('<br class="d-print-none"/>');
                            li.append($('<small/>',{
                                class: 'font-weight-bold text-danger d-print-none',
                                role: "button",
                                html: 'แพ้ยา/เฝ้าระวัง',
                                title: order_item.allergy_agent_symptom,
                            }));
                        }
                        // if(IPD_ORDER_OFF && isToday && order_item.off_by_order_item_id == null && continuous_order.order_confirm == 'Y' && continuous_order.order_owner_type == 'nurse'){
                        //     li.append($('<a/>', {
                        //         text: 'Off',
                        //         class: "float-right",
                        //         href: '#off_'+order_item.order_item_id/*+'_'+order_item.order_item_detail*/,
                        //         click: function(event) {
                        //             onclickOffContinuousOrderItem(event, order_item.order_item_id, (order_item.icode == null ? '' : (order_item.med_name + (order_item.order_item_detail != '' ? '\n' : ''))) + order_item.order_item_detail);
                        //         },
                        //     }));
                        // }
                    }
                    if(order_item.index_plan_actions != null && order_item.index_plan_actions.length > 0){
                        order_item.index_plan_actions.forEach(function(index_plan_action){
                            index_plan_action_to_text(continuous_order.order_date, li, index_plan_action);
                        });
                    }
                    if(IPD_NURSE_INDEX_ADD && /*IPD_NURSE_INDEX_ADD && */ /*order_item.off_by_order_item_id == null && */(continuous_order.order_owner_type == 'doctor' || continuous_order.order_owner_type == 'nurse')){
                        li.append($('<a/>', {
                            text: '+Plan',
                            class: "float-right d-print-none",
                            href: '#index_'+order_item.order_item_id/*+'_'+order_item.order_item_detail*/,
                            click: function(event) {
                                onclickAddIndexPlanOrderItem(event, order_item.order_item_id, order_item.order_item_detail);
                            },
                        }));
                    }
                    ul.append(li);
                });
                inner_div.append(ul);
            });
        }
        order_display.append(inner_div);

        order_display.append($('<div/>', {
            html:
                (continuous_order.order_owner_type == 'doctor') ?
                [
                    $('<span/>', { class: "d-print-none", text: `${continuous_order.order_doctor_name}, `}),
                    $('<span/>', { class: "d-none d-print-block", text: `${continuous_order.order_doctor_name}${(continuous_order.order_owner_type == 'doctor' ? ` (${continuous_order.doctor_licenseno})`:'')}, `}),
                    $('<span/>', { class: "text-nowrap", text: order_datetime.format("DD/MM/") + (parseInt(order_datetime.format("YYYY"),10)+543) + order_datetime.format(", HH:mm") + ' น.', }),
                ] :
                [
                    $('<span/>', { text: `${continuous_order.order_doctor_name}, `}),
                    $('<span/>', { class: "text-nowrap", text: order_datetime.format("DD/MM/") + (parseInt(order_datetime.format("YYYY"),10)+543) + order_datetime.format(", HH:mm") + ' น.', }),
                ]
            ,
            class: "text-right small",
        }));
        if(continuous_order.nurse_accept_time != null){
            nurse_accept_time = moment(continuous_order.nurse_accept_time + ' ' + continuous_order.nurse_accept_time, "YYYY-MM-DD HH:mm");
            order_display.append($('<div/>', {
                html: [
                    $('<span/>', { text: '(RN) ' + continuous_order.nurse_accept_name + ', ', }),
                    $('<span/>', { class: "text-nowrap", text: nurse_accept_time.format("DD/MM/") + (parseInt(nurse_accept_time.format("YYYY"),10)+543) + nurse_accept_time.format(", HH:mm") + ' น.', }),
                ],
                class: "text-right small",
            }));
        }
        if(continuous_order.pharmacist_done_time != null){
            pharmacist_done_time = moment(continuous_order.pharmacist_done_time + ' ' + continuous_order.pharmacist_done_time, "YYYY-MM-DD HH:mm");
            order_display.append($('<div/>', {
                html: [
                    $('<span/>', { text: '(RX) ' + continuous_order.pharmacist_done_name + ', ', }),
                    $('<span/>', { class: "text-nowrap", text: pharmacist_done_time.format("DD/MM/") + (parseInt(pharmacist_done_time.format("YYYY"),10)+543) + pharmacist_done_time.format(", HH:mm") + ' น.', }),
                ],
                class: "text-right small",
            }));
        } else if(continuous_order.pharmacist_accept_time != null){
            pharmacist_accept_time = moment(continuous_order.pharmacist_accept_time + ' ' + continuous_order.pharmacist_accept_time, "YYYY-MM-DD HH:mm");
            order_display.append($('<div/>', {
                html: [
                    $('<span/>', { text: '(ห้องยารับรายการ) ' + continuous_order.pharmacist_accept_name + ', ', }),
                    $('<span/>', { class: "text-nowrap", text: pharmacist_accept_time.format("DD/MM/") + (parseInt(pharmacist_accept_time.format("YYYY"),10)+543) + pharmacist_accept_time.format(", HH:mm") + ' น.', }),
                ],
                class: "text-right small",
            }));
        }
        if(isToday){
            if(continuous_order.order_confirm != 'Y' && continuous_order.order_owner_type == ORDER_OWNER_TYPE){
                var action_row = $('<div/>', { id: 'order_id_'+continuous_order.order_id+'_action_row_div', class: "text-right font-weight-bold d-print-none" });
                if(IPD_ORDER_CONFIRM){
                    action_row.append(
                        $('<a/>', {
                            class: 'ml-1',
                            href: '#confirm_order_id='+continuous_order.order_id,
                            text: '[Confirm]',
                        }).click(function(event){
                            event.preventDefault();
                            confirmContinuousOrder(continuous_order.order_id);
                        })
                    );
                }
                if(IPD_ORDER_EDIT){
                    action_row.append(
                        $('<a/>', {
                            class: 'ml-1',
                            href: '#edit_order_id='+continuous_order.order_id,
                            text: '[Edit]',
                        }).click(function(event){
                            event.preventDefault();
                            editContinuousColumnInput(continuous_order.order_id);
                        })
                    );
                }
                if(IPD_ORDER_REMOVE){
                    action_row.append(
                        $('<a/>', {
                            class: 'ml-1',
                            href: '#delete_order_id='+continuous_order.order_id,
                            text: '[Delete]',
                        }).click(function(event){
                            event.preventDefault();
                            deleteContinuousOrder(continuous_order.order_id);
                        }),
                    );
                }
                order_display.append(action_row);
            }
        } else {
            if(continuous_order.order_confirm != 'Y'){
                var action_row = $('<div/>', { class: "text-right" });
                action_row.append(
                    $('<span/>', {
                        class: 'ml-1 text-warning',
                        text: '[Not Confirmed]',
                    })
                );
                order_display.append(action_row);
            }
        }

        if(IPD_ORDER_ACCEPT && continuous_order.order_owner_type == 'doctor' && continuous_order.nurse_accept_time == null){
            var action_row = $('<div/>', { class: "text-right font-weight-bold d-print-none" });
            action_row.append(
                $('<a/>', {
                    class: 'ml-1',
                    href: '#accept_order_id='+continuous_order.order_id,
                    text: '[รับรายการ]',
                }).click(function(event){
                    event.preventDefault();
                    acceptContinuousOrder(continuous_order.order_id);
                })
            );
            order_display.append(action_row);
        }
        order_display.append($('<hr/>'));
        return order_display;
    }

    function progress_note_data_to_text(progress_note){
        let progress_note_datetime = moment(progress_note.progress_note_date + ' ' + progress_note.progress_note_time, "YYYY-MM-DD HH:mm");
        let progress_note_create_datetime = moment(progress_note.create_datetime, "YYYY-MM-DD HH:mm");
        let pre_order_progress_note_datetime = ((progress_note.pre_order_progress_note_date != null) ? moment(progress_note.pre_order_progress_note_date + ' ' + progress_note.pre_order_progress_note_time, "YYYY-MM-DD HH:mm") : null);
        let progress_note_enter_datetime = moment(progress_note.progress_note_enter_datetime, "YYYY-MM-DD HH:mm");
        let isToday = moment().format("YYYY-MM-DD") == progress_note_datetime.format("YYYY-MM-DD");

        let mainDisplayDate;
        if(progress_note.progress_note_enter_datetime != null){
            mainDisplayDate = progress_note_enter_datetime;
        } else if(progress_note.progress_note_owner_type == 'auditor'){
            mainDisplayDate = progress_note_create_datetime;
        } else {
            mainDisplayDate = progress_note_datetime;
        }

        let IS_PROGRESS_NOTE_DATE_BEFORE_OR_ON_DCHDATE = (IPD_ORDER_DCHDATE != null && (IPD_ORDER_DCHDATE >= mainDisplayDate.format("YYYY-MM-DD")));

        var progress_note_display = $('<div/>', {
            id: 'progress_note_id_'+progress_note.progress_note_id+'_div',
            html: [
                $('<span/>', {
                    text: (
                        (mainDisplayDate.format("DD/MM/") + (parseInt(mainDisplayDate.format("YYYY"),10)+543) + mainDisplayDate.format(", HH:mm") + ' น. ')
                        + (progress_note.progress_note_owner_type == 'doctor' ? " (Doctor)":"")
                        + (progress_note.progress_note_owner_type == 'nurse' ? " (Nurse)":"")
                        + (progress_note.progress_note_owner_type == 'pharmacist' ? " (Pharmacist)":"")
                        + (progress_note.progress_note_owner_type == 'other' ? " (Other)":"")
                        + (progress_note.progress_note_owner_type == 'auditor' ? " (Auditor)":"")
                    ),
                }),
                $('<span/>', {
                    text: ((pre_order_progress_note_datetime != null) ? ' (บันทึกไว้เมื่อ: ' + pre_order_progress_note_datetime.format("DD/MM/") + (parseInt(pre_order_progress_note_datetime.format("YYYY"),10)+543) + pre_order_progress_note_datetime.format(", HH:mm") + ' น.)' : ''),
                    class: 'small text-info',
                }),
                ((progress_note.progress_note_owner_type == 'auditor' || progress_note.progress_note_enter_datetime != null) ?
                $('<span/>', {
                    text: ((progress_note_datetime != null) ? ' (สำหรับวันที่: ' + progress_note_datetime.format("DD/MM/") + (parseInt(progress_note_datetime.format("YYYY"),10)+543) + ')' : ''),
                    class: 'small text-warning',
                }) : ''),
            ]
        });

        if(progress_note.progress_note_item_types.length > 0){
            var ul = $('<ul/>', {
                class: "dash",
                style: "white-space: pre-wrap;",
            });
            progress_note.progress_note_item_types.forEach(function(progress_note_item_type){
                // progress_note_display.append($('<div/>', { text: PROGRESS_NOTE_ITEM_TYPE_NAMES[progress_note_item_type.progress_note_item_type] }));
                progress_note_item_type.progress_note_items.forEach(function(progress_note_item){
                    ul.append(
                    $('<li/>', {
                        text: progress_note_item.progress_note_item_detail,
                    }));
                });
            });
            progress_note_display.append($('<div/>', { id: 'progress_note_id_'+progress_note.progress_note_id+'_inner_div',html: ul}));
        } else {
            progress_note_display.append($('<div/>', { id: 'progress_note_id_'+progress_note.progress_note_id+'_inner_div'}));
        }
        progress_note_display.append($('<div/>', {
            html:
                (progress_note.progress_note_owner_type == 'doctor') ?
                [
                    $('<span/>', { class: "d-print-none", text: `${progress_note.order_doctor_name}, `}),
                    $('<span/>', { class: "d-none d-print-block", text: `${progress_note.order_doctor_name}${(progress_note.progress_note_owner_type == 'doctor' ? ` (${progress_note.doctor_licenseno})`:'')}, `}),
                    $('<span/>', { class: "text-nowrap", text: (mainDisplayDate.format("DD/MM/") + (parseInt(mainDisplayDate.format("YYYY"),10)+543) + mainDisplayDate.format(", HH:mm") + ' น. '), })
                ] :
                [
                    $('<span/>', { text: `${progress_note.order_doctor_name}, `}),
                    $('<span/>', { class: "text-nowrap", text: (mainDisplayDate.format("DD/MM/") + (parseInt(mainDisplayDate.format("YYYY"),10)+543) + mainDisplayDate.format(", HH:mm") + ' น. '), })
                ]
            ,
            class: "text-right small",
        }));

        if(
            // isToday &&
            progress_note.progress_note_doctor == IPD_ORDER_DOCTORCODE
            && progress_note.progress_note_owner_type == ORDER_OWNER_TYPE){
            var action_row = $('<div/>', { id: 'progress_note_id_'+progress_note.progress_note_id+'_action_row_div', class: "text-right font-weight-bold d-print-none" });
            //ไม่ให้แก้ไขหรือลบข้อมูลที่บันทึกไว้ก่อนหรือจนถึงวันที่ discharge, หลังจากผ่านวันที่ discharge แล้ว
            if(PROGRESS_NOTE_EDIT && (isToday || !IS_PROGRESS_NOTE_DATE_BEFORE_OR_ON_DCHDATE)){
                action_row.append(
                    $('<a/>', {
                        href: '#edit_progress_note_id='+progress_note.progress_note_id,
                        text: '[Edit]',
                    }).click(function(event){
                        event.preventDefault();
                        editProgressNoteColumnInput(progress_note.progress_note_id);
                    })
                );
            }
            if(PROGRESS_NOTE_REMOVE && (isToday || !IS_PROGRESS_NOTE_DATE_BEFORE_OR_ON_DCHDATE)){
                action_row.append(
                    $('<a/>', {
                        class: 'ml-1',
                        href: '#delete_progress_note_id='+progress_note.progress_note_id,
                        text: '[Delete]',
                    }).click(function(event){
                        event.preventDefault();
                        deleteProgressNoteOrder(progress_note.progress_note_id);
                    }),
                );
            }
            progress_note_display.append(action_row);
        }
        progress_note_display.append($('<hr/>'));
        return progress_note_display;
    }

</script>

<?php require_once 'ipd-nurse-index-note-form.php'; ?>