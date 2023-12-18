<?php
/*
require_once './project/function/SessionManager.php';
SessionManager::checkLoginSessionAndShowMessage(); //เช็ค session
// SessionManager::checkPermissionAndShowMessage('KPHIS_ACCESS_IPD_NURSE_PROGRAM');
if(!SessionManager::checkPermission('VITAL_SIGN','VIEW')){
    // SessionManager::showMessage();
    return;
}

*/
require_once '../include/KphisQueryUtils.php';
$an = $_REQUEST['an'];
$hn = KphisQueryUtils::getHnByAn($an);
$operation_list_his = KphisQueryUtils::getOperationAdmit($an);//ข้อมูลการผ่าตัด 'Admit WHERE = an'
?>
<div class="container-fluid">
  
    <div class="row d-print-none" style="width: fit-content;">
        <div class="col" style="z-index: 1; margin-bottom: -30px; width: fit-content;">
            <button class="btn btn-secondary" onclick="onclickVitalSignTableFilterButton('[BT]')">BT</button>
            <button class="btn btn-secondary" onclick="onclickVitalSignTableFilterButton('[PR]')">PR</button>
            <button class="btn btn-secondary" onclick="onclickVitalSignTableFilterButton('[RR]')">RR</button>
            <button class="btn btn-secondary" onclick="onclickVitalSignTableFilterButton('[BP]')">BP</button>
            <button class="btn btn-secondary" onclick="onclickVitalSignTableFilterButton('[SAT]')">SAT</button>
            <button class="btn btn-secondary" onclick="onclickVitalSignTableFilterButton('[PS]')">PS</button>
            <button class="btn btn-secondary" onclick="onclickVitalSignTableFilterButton('[MEWS]')">MEWS</button>
            <button class="btn btn-secondary" onclick="onclickVitalSignTableFilterButton('[DTX]')">DTX</button>
            <button class="btn btn-secondary" onclick="onclickVitalSignTableFilterButton('[HCT]')">HCT</button>
            <!-- <button class="btn btn-secondary" onclick="onclickVitalSignTableFilterButton('[อื่นๆ]')">อื่นๆ</button> -->
            <button class="btn btn-secondary" onclick="onclickVitalSignTableFilterButton('[U]')">U</button>
            <button class="btn btn-secondary" onclick="onclickVitalSignTableFilterButton('[F]')">F</button>
            <button class="btn btn-secondary" onclick="onclickVitalSignTableFilterButton('')">Clear Filter</button>
        </div>
    </div>
    <div class="row">
        <div class="col" id="vital_sign_result_table_div">
            <table id="vital_sign_result_table" class="table table-striped table-bordered table-sm" style="width: 100%;">
                <thead>
                <tr>
                    <th scope="col" class="d-none">#</th>
                    <th scope="col">เวลา</th>
                    <th scope="col" class="d-none">TAG</th>
                    <th scope="col">BT</th>
                    <th scope="col">PR</th>
                    <th scope="col">RR</th>
                    <th scope="col">SBP</th>
                    <th scope="col">DBP</th>
                    <th scope="col">SAT</th>
                    <th scope="col">MAP</th>
                    <th scope="col">PS</th>
                    <th scope="col">MEWS</th>
                    <th scope="col">DTX</th>
                    <th scope="col">HCT</th>
                    <th scope="col">อื่นๆ</th>
                    <th scope="col">U</th>
                    <th scope="col">F</th>
                </tr>
                </thead>
                <tbody id="vs_table_body">
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
function onclickVsTableRow(vs_id, an){
    $("#show-vital-sign-form").html('');
    var url="ipd-vital-sign-show-form.php";
    $.get(url,{vs_id, an, 'data_mode':'U'},function(data_vital_sign){
        $("#show-vital-sign-form").html(data_vital_sign);
    });
}
function onclickVitalSignTableFilterButton(filter_type){
    if(vital_sign_result_datatable != null){
        vital_sign_result_datatable.search(filter_type).draw();
    }
}
var vs_table_data_request = null;
var vital_sign_result_datatable = null;
function getVitalSignDataForTable(display_vs_date_from, display_vs_date_to){
    if(vs_table_data_request != null){
        vs_table_data_request.abort();
    }
    // if(display_vs_date == null || display_vs_date == ''){
    //     moment.locale('en');
    //     display_vs_date = moment().format("YYYY-MM-DD");
    // }
    moment.locale('th');
    if(vital_sign_result_datatable != null){
        vital_sign_result_datatable.destroy();
        $('#vs_table_body').html('');
    }
    vs_table_data_request = $.getJSON("ipd-vital-sign-show-chart-table-data.php",{'an': <?=json_encode($an)?>, 'start_vs_datetime': display_vs_date_from, 'end_vs_datetime': display_vs_date_to}, function(data) {
        $('#vital_sign_result_table_div').html(vital_sign_result_table_div_html);
        $.each(data, function(i, v) {
            var vsDatetime = moment(v.vs_datetime, "YYYY-MM-DD HH:mm:ss");
            const age_y = <?=json_encode(KphisQueryUtils::checkPatienAge($an))?>;
            let total = mews(age_y, v.bt, v.pr, v.rr, v.respirator, v.sbp, v.inotrope, v.conscious_id, v.urine_amount, v.urine_duration);
            total_color = 'inherit';
            total_text = '';
            if(total === 0){
                total_color = '#45c351';
            }else if(total > 0 && total <= 3){
                total_color = '#e6b728';
            }else if(total >= 4){
                total_color = '#e51616';
            }else{
                total = '';
            }
            total_text = "<div class='badge text-white font-weight-bold' style='font-size:100%; background-color: " + total_color + ";'> " + total + "</div>";
            var tablerow = $('<tr/>', {
                id: 'vs_table_row_vs_id_' + v.vs_id,
                html: [
                    $('<td/>', {
                        class: "d-none",
                        text: i+1,
                    }),
                    $('<td/>', {
                        html: '<span class="d-none">'+ vsDatetime.format('YYYYMMDDHHmm') + '</span>' + vsDatetime.format('DD/MM/') + (vsDatetime.year() + 543) + vsDatetime.format(' HH:mm'),
                        class: "text-nowrap",
                        title: `บันทึกโดย: ${v.create_opduser_name} (${v.create_datetime})\nแก้ไขล่าสุด: ${v.update_opduser_name} (${v.update_datetime})`,
                    }),
                    $('<td/>', {
                        class: "d-none",
                        text:     (isBlankOrNullOrWhiteSpace(v.bt) ? '' : '[BT]')
                                + (isBlankOrNullOrWhiteSpace(v.pr) ? '' : '[PR]')
                                + (isBlankOrNullOrWhiteSpace(v.rr) ? '' : '[RR]')
                                + (isBlankOrNullOrWhiteSpace(v.sbp) ? '' : '[BP]')
                                + (isBlankOrNullOrWhiteSpace(v.dbp) ? '' : '[BP]')
                                + (isBlankOrNullOrWhiteSpace(v.map) ? '' : '[BP]')
                                + (isBlankOrNullOrWhiteSpace(v.sat) ? '' : '[SAT]')
                                + (isBlankOrNullOrWhiteSpace(v.pain) ? '' : '[PS]')
                                + (isBlankOrNullOrWhiteSpace(total) ? '' : '[MEWS]')
                                + (isBlankOrNullOrWhiteSpace(v.dtx) ? '' : '[DTX]')
                                + (isBlankOrNullOrWhiteSpace(v.hct) ? '' : '[HCT]')
                                // + (isBlankOrNullOrWhiteSpace(v.bt) ? '' : '[อื่นๆ]')
                                + (isBlankOrNullOrWhiteSpace(v.urine) ? '' : '[U]')
                                + (isBlankOrNullOrWhiteSpace(v.feces) ? '' : '[F]')
                    }),
                    $('<td/>', {
                        text: v.bt,
                        class: "text-right",
                    }),
                    $('<td/>', {
                        text: v.pr,
                        class: "text-right",
                    }),
                    $('<td/>', {
                        text: v.rr,
                        class: "text-right",
                    }),
                    $('<td/>', {
                        text: v.sbp,
                        class: "text-right",
                    }),
                    $('<td/>', {
                        text: v.dbp,
                        class: "text-right",
                    }),
                    $('<td/>', {
                        text: v.sat,
                        class: "text-right",
                    }),
                    $('<td/>', {
                        text: v.map,
                        class: "text-right",
                    }),
                    $('<td/>', {
                        text: v.pain,
                        class: "text-right",
                    }),
                    $('<td/>', {
                        html: total_text,
                        class: "text-center",
                    }),
                    $('<td/>', {
                        text: v.dtx,
                        class: "text-right",
                    }),
                    $('<td/>', {
                        text: v.hct,
                        class: "text-right",
                    }),
                    $('<td/>', {
                        html: [
                            ((!isBlankOrNullOrWhiteSpace(v.cvp)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">CVP:</span>&nbsp;' + v.cvp+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.end_co2)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">END CO2:</span>&nbsp;' + v.end_co2+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.conscious_id)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">CONSCIOUS:</span>&nbsp;' + v.conscious_name+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.bw)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">BW:</span>&nbsp;' + new Intl.NumberFormat().format(v.bw)+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.height)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">HEIGHT:</span>&nbsp;' + v.height+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.head)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">HEAD:</span>&nbsp;' + v.head+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.t_inc)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">T INC:</span>&nbsp;' + v.t_inc+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.line_id)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">LINE:</span>&nbsp;' + v.line_name+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.line_no)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">LINE NO:</span>&nbsp;' + v.line_no+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.line_mark)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">LINE MARK:</span>&nbsp;' + v.line_mark+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.braden)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">BRADEN:</span>&nbsp;' + v.braden+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.eye) || !isBlankOrNullOrWhiteSpace(v.verbal) || !isBlankOrNullOrWhiteSpace(v.movement)) ?
                                ($('<span/>', {
                                    class: 'mr-1',
                                    html: [
                                        ((!isBlankOrNullOrWhiteSpace(v.eye)) ? ($('<span/>', {
                                            class: '',
                                            html: '<span class="text-primary">E</span><sub>' + v.eye + '</sub>',
                                        })) : ''),
                                        ((!isBlankOrNullOrWhiteSpace(v.verbal)) ? ($('<span/>', {
                                            class: '',
                                            html: '<span class="text-primary">V</span><sub>' + v.verbal + '</sub>',
                                        })) : ''),
                                        ((!isBlankOrNullOrWhiteSpace(v.movement)) ? ($('<span/>', {
                                            class: '',
                                            html: '<span class="text-primary">M</span><sub>' + v.movement + '</sub>',
                                        })) : ''),
                                    ]
                                })) : ''
                            ),
                            ((!isBlankOrNullOrWhiteSpace(v.right_pupil)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">RIGHT PUPIL:</span>&nbsp;' + v.right_pupil+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.right_cha_id)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">RIGHT CHA:</span>&nbsp;' + v.right_cha_name+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.left_pupil)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">LEFT PUPIL:</span>&nbsp;' + v.left_pupil+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.left_cha_id)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">LEFT CHA:</span>&nbsp;' + v.left_cha_name+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.va_id)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">VA:</span>&nbsp;' + v.va_name+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.mass_id)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">MASS:</span>&nbsp;' + v.mass_name+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.lt_arm)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">Lt Arm:</span>&nbsp;' + v.lt_arm_name+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.lt_leg)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">Lt Leg:</span>&nbsp;' + v.lt_leg_name+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.rt_arm)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">Rt Arm:</span>&nbsp;' + v.rt_arm_name+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.rt_leg)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">Rt Leg:</span>&nbsp;' + v.rt_leg_name+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.severity)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">SEVERITY:</span>&nbsp;' + v.severity+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.had_name)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">HAD:</span> <span class="text-danger">' + v.had_name + '</span>'+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.had_drop)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">HAD DROP:</span> <span class="text-danger">' + v.had_drop + '</span>'+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.bl)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">BL:</span>&nbsp;' + v.bl+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.mcb)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">MCB:</span>&nbsp;' + v.mcb+' ',
                            })) : ''),
                            ((v.suction == 'Y') ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">SUCTION:</span>&nbsp;' + v.suction+' ',
                            })) : ''),
                            ((v.nb == 'Y') ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">NB:</span>&nbsp;' + v.nb+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.o2_id)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">O2:</span>&nbsp;' + v.o2_name+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.o2_flow)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">O2 FLOW:</span>&nbsp;' + v.o2_flow+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.tube_id)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">TUBE:</span>&nbsp;' + v.tube_name+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.tube_no)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">TUBE NO:</span>&nbsp;' + v.tube_no+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.tube_mark)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">TUBE MARK:</span>&nbsp;' + v.tube_mark+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.ventilator_name)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">VENTILATOR NAME:</span>&nbsp;' + v.ventilator_name+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.mode)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">MODE:</span>&nbsp;' + v.mode+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.tv)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">TV:</span>&nbsp;' + v.tv+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.pip)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">PIP:</span>&nbsp;' + v.pip+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.r_rate)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">R RATE:</span>&nbsp;' + v.r_rate+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.i_rate)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">I RATE:</span>&nbsp;' + v.i_rate+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.e_rate)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">E RATE:</span>&nbsp;' + v.e_rate+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.ti)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">TI:</span>&nbsp;' + v.ti+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.ps)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">PS:</span>&nbsp;' + v.ps+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.fio2)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">FIO2:</span>&nbsp;' + v.fio2+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.peep)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">PEEP:</span>&nbsp;' + v.peep+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.ft)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">FT:</span>&nbsp;' + v.ft+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.delta_p)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">DELTA P:</span>&nbsp;' + v.delta_p+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.o2_map)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">MAP:</span>&nbsp;' + v.o2_map+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.intake_id)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">INTAKE:</span>&nbsp;' + v.intake_name+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.intake_type)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">INTAKE TYPE:</span>&nbsp;' + v.intake_type+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.intake_amount)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">INTAKE AMOUNT:</span>&nbsp;' + v.intake_amount+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.intake_absorb)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">INTAKE ABSORB:</span>&nbsp;' + v.intake_absorb+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.output_id)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">OUTPUT:</span>&nbsp;' + v.output_name+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.output_amount)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">OUTPUT AMOUNT:</span>&nbsp;' + v.output_amount+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.lr_int)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">INTERVAL:</span>&nbsp;' + v.lr_int+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.lr_dur)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">DURATION:</span>&nbsp;' + v.lr_dur+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.lr_fsh)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">FETAL HEART SOUND:</span>&nbsp;' + v.lr_fsh+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.lr_sev)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">LR SEVERITY:</span>&nbsp;' + v.lr_sev+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.lr_cer)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">CERVIX:</span>&nbsp;' + v.lr_cer+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.lr_eff)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">EFFACEMENT:</span>&nbsp;' + v.lr_eff+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.lr_sta_name)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">LR STATION:</span>&nbsp;' + v.lr_sta_name+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.lr_mem_name)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">MEMBRANE:</span>&nbsp;' + v.lr_mem_name+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.lr_af)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">ลักษณะ MEMBRANE:</span>&nbsp;' + v.lr_af+' ',
                            })) : ''),
                            ((!isBlankOrNullOrWhiteSpace(v.other)) ? ($('<span/>', {
                                class: 'mr-1',
                                html: '<span class="text-primary">OTHER:</span>&nbsp;' + v.other+' ',
                            })) : ''),
                        ]
                    }),
                    $('<td/>', {
                        text: v.urine,
                        class: "text-right text-nowrap",
                    }),
                    $('<td/>', {
                        text: v.feces,
                        class: "text-right",
                    }),
                ]
            }).click(function() {
                onclickVsTableRow(v.vs_id, v.an);
            });
            tablerow.appendTo('#vs_table_body');
        });

        vital_sign_result_datatable = $('#vital_sign_result_table').DataTable({
            paging:  false,
            // "autoWidth": false,
            // scrollY:        '50vh',
            // scrollCollapse: true,
            dom:    "<'row d-print-none'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row d-print-none'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            "columnDefs": [
                { "width": "25px", "targets": 0 },
                { "width": "120px", "targets": 1 },
            ]
        });

        // display_vs_date_from, display_vs_date_to
        // if(display_vs_date != null && display_vs_date != ''){
        //     var display_vs_date_moment = moment(display_vs_date, "YYYY-MM-DD");
        //     vital_sign_result_datatable.search(display_vs_date_moment.format('DD/MM/') + (display_vs_date_moment.year() + 543)).draw();
        // }

    });
}

function beforePrintVitalSign(){
    var currentOrdering = vital_sign_result_datatable.order();
    vital_sign_result_datatable.order( [ 1, 'asc' ] ).draw();
    setTimeout(function(){ vital_sign_result_datatable.order( currentOrdering ).draw(); }, 0);
}
var vital_sign_result_table_div_html;
$( document ).ready(function() {
    vital_sign_result_table_div_html = $('#vital_sign_result_table_div').html();
    moment.locale('en');
    getVitalSignDataForTable(document.getElementById("display_vs_date_from").value, document.getElementById("display_vs_date_to").value);
    // getVitalSignDataForTable(moment().format("YYYY-MM-DD"), moment().format("YYYY-MM-DD"));

    if ('matchMedia' in window) {//safari
        window.matchMedia('print').addListener(function (media) {
            beforePrintVitalSign();
        });
    } else {//other
        window.addEventListener("beforeprint", function () {
            beforePrintVitalSign();
        });
    }

});

</script>