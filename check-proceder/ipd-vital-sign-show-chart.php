<?php
/*
require_once './project/function/SessionManager.php';
SessionManager::checkLoginSessionAndShowMessage(); //เช็ค session
// SessionManager::checkPermissionAndShowMessage('KPHIS_ACCESS_IPD_NURSE_PROGRAM');
if(!SessionManager::checkPermission('VITAL_SIGN','VIEW')){
    // SessionManager::showMessage();
    return;
} */
require_once '../mains/ipd-show-patient-main-vitalsign.php';
require_once '../include/KphisQueryUtils.php';
$an = $_REQUEST['an'];
$hn = KphisQueryUtils::getHnByAn($an);
?>
<style>
.vs-table-highlight {
    /* background-color: #e6b728 !important; */
    animation: fadeOut 2s forwards;
}
@keyframes fadeOut {
    100%   {background-color: white;}
    0%     {background-color: #53a3ff;}
}
</style>
<div class="container-fluid">
    <div class="row">
        <div class="col">
            <div class="col-auto float-right d-print-none">
            <!-- <a href="ipd-vital-sign-pdf.php?an=<?=$an?>" target="_blank" class="btn btn-secondary"><i class="fas fa-print"></i> ทั้งหมด</a> -->
            <!--<button type="button" class="btn btn-secondary" onclick="window.print();"><i class="fas fa-print"></i> พิมพ์</button> -->

            </div>
             <div class="form-inline">
                <div class="form-group mb-2">
                    <label for="display_vs_date_from" class="mr-2">แสดงข้อมูลวันที่</label>
                    <div>
                        <input type="date" class="form-control" id="display_vs_date_from" name="display_vs_date_from" oninput="oninputDisplayVsDate(event)">
                    </div>
                </div>
                <div class="form-group mb-2">
                    <label for="display_vs_date_to" class="ml-2 mr-2">ถึง</label>
                    <div>
                        <input type="date" class="form-control" id="display_vs_date_to" name="display_vs_date_to" oninput="oninputDisplayVsDate(event)">
                    </div>
                </div>
                <div class="d-print-none mb-2">
                    <button type="button" class="btn btn-secondary ml-2" onclick="onclickLastXDays(event, 1);">วันนี้</button>
                    <button type="button" class="btn btn-secondary ml-2" onclick="onclickLastXDays(event, 3);">3 วัน</button>
                    <button type="button" class="btn btn-secondary ml-2" onclick="onclickLastXDays(event, 7);">7 วัน</button>
                    <button type="button" class="btn btn-secondary ml-2" onclick="onclickLastXDays(event, 15);">15 วัน</button>
                    <button type="button" class="btn btn-secondary ml-2" onclick="onclickLastXDays(event, 30);">30 วัน</button>
                    <button type="button" class="btn btn-secondary ml-2" onclick="onclickLastXDays(event, -1);">ทั้งหมด</button>
                    <button type="button" class="btn btn-secondary ml-2" onclick="onclickShiftPeriod(event,'back');"><i class="fas fa-caret-left"></i></button>
                    <button type="button" class="btn btn-secondary ml-2" onclick="onclickShiftPeriod(event,'forward');"><i class="fas fa-caret-right"></i></button>
                </div>
            </div>
        </div>
    </div>
    <!--
    <div class="row">
        <div class="col">
            <div id="canvasDiv">
            <canvas id="canvas"></canvas>
            </div>
        </div>
    </div> -->


</div>
<script>
var VS_REGDATE = <?=json_encode(KphisQueryUtils::getRegdateByAn($an))?>;
var VS_DCHDATE = <?=json_encode(KphisQueryUtils::getDchdateByAn($an))?>;

// function onclickVsPrintButton(event){
//     let sbp_hidden = null;
//     let dbp_hidden = null;
//     for (var id in Chart.instances) {
//         let chart = Chart.instances[id];
//         chart.data.datasets.forEach(function(ds) {
//             if(ds.label == 'SBP'){
//                 sbp_hidden = ds.hidden;
//                 ds.hidden = false;
//             } else if(ds.label == 'DBP'){
//                 dbp_hidden = ds.hidden;
//                 ds.hidden = false;
//             }
//         });
//         chart.update();
//     }
//     window.print();
//     for (var id in Chart.instances) {
//         let chart = Chart.instances[id];
//         chart.data.datasets.forEach(function(ds) {
//             if(ds.label == 'SBP'){
//                 ds.hidden = sbp_hidden;
//             } else if(ds.label == 'DBP'){
//                 ds.hidden = dbp_hidden;
//             }
//         });
//         chart.update();
//     }
// }

function onclickShiftPeriod(event, direction){
    var display_vs_date_from = document.getElementById("display_vs_date_from").value;
    var display_vs_date_to = document.getElementById("display_vs_date_to").value;
    if(display_vs_date_from != null && display_vs_date_to != ''){
        let dateDiff = moment(display_vs_date_to, "YYYY-MM-DD").diff(moment(display_vs_date_from, "YYYY-MM-DD"), 'days') + 1;

        if(direction == 'back'){
            //'back'
            display_vs_date_from = moment(display_vs_date_from, "YYYY-MM-DD").subtract(dateDiff, 'days').format('YYYY-MM-DD');
            display_vs_date_to = moment(display_vs_date_to, "YYYY-MM-DD").subtract(dateDiff, 'days').format('YYYY-MM-DD');
        } else {
            //'forward'
            display_vs_date_from = moment(display_vs_date_from, "YYYY-MM-DD").add(dateDiff, 'days').format('YYYY-MM-DD');
            display_vs_date_to = moment(display_vs_date_to, "YYYY-MM-DD").add(dateDiff, 'days').format('YYYY-MM-DD');
        }
        document.getElementById("display_vs_date_from").value = display_vs_date_from;
        document.getElementById("display_vs_date_to").value = display_vs_date_to;
        oninputDisplayVsDate(event);
    } else {
        alert("กรุณากรอกช่วงวันที่ให้ครบถ้วน");
    }
}

function onclickLastXDays(event, days){
    if(days == -1){
        let form_date = moment(VS_REGDATE);
        let to_date;
        if(VS_DCHDATE != null && VS_DCHDATE != ''){
            to_date = moment(VS_DCHDATE);
        } else {
            to_date = moment();
        }
        document.getElementById("display_vs_date_from").value = form_date.format("YYYY-MM-DD");
        document.getElementById("display_vs_date_to").value = to_date.format("YYYY-MM-DD");
    } else {
        let display_vs_date = null;
        if(VS_DCHDATE != null && VS_DCHDATE != ''){
            display_vs_date = moment(VS_DCHDATE);
        } else {
            display_vs_date = moment();
        }
        document.getElementById("display_vs_date_to").value = display_vs_date.format("YYYY-MM-DD");
        document.getElementById("display_vs_date_from").value = display_vs_date.subtract(days-1, "days").format("YYYY-MM-DD");
    }
    oninputDisplayVsDate(event);
}

function oninputDisplayVsDate(event){
    var display_vs_date_from = document.getElementById("display_vs_date_from").value;
    var display_vs_date_to = document.getElementById("display_vs_date_to").value;
    getVitalSignDataForChart(display_vs_date_from, display_vs_date_to);
    getVitalSignDataForTable(display_vs_date_from, display_vs_date_to);
}
function onclickVsTableRow(vs_id, an){
    $("#show-vital-sign-form").html('');
    var url="ipd-vital-sign-show-form.php";
    $.get(url,{vs_id, an, 'data_mode':'U'},function(data_vital_sign){
        $("#show-vital-sign-form").html(data_vital_sign);
    });
}
var vs_chart_data_request = null;
var vital_sign_show_chart_table_data = null;
function getVitalSignDataForChart(display_vs_date_from, display_vs_date_to){
    if(vs_chart_data_request != null){
        vs_chart_data_request.abort();
    }
    moment.locale('th');
    var triangle_down = document.createElement("IMG");
    triangle_down.src = "picture/triangle-down.svg";
    // triangle_down.width = 20;
    // triangle_down.height = 20;
    var triangle_up = document.createElement("IMG");
    triangle_up.src = "picture/triangle-up.svg";
    // triangle_up.width = 20;
    // triangle_up.height = 20;

    //ให้ chart ดึงข้อมูลมากกว่าที่ขอมา โดยให้ดึงย้อนหลังอีก dateDiff-1 วันและไปข้างหน้าอีก dateDiff+1 วัน เพื่อให้สามารถแสดงเส้นกราฟไปโยงกันได้
    let dateDiff = moment(display_vs_date_to, "YYYY-MM-DD").diff(moment(display_vs_date_from, "YYYY-MM-DD"), 'days') + 1;
    let chart_data_from = (display_vs_date_from == null ? null : moment(display_vs_date_from, "YYYY-MM-DD").add((-1*dateDiff)-1, 'days').format('YYYY-MM-DD'));
    let chart_data_to = (display_vs_date_to == null ? null : moment(display_vs_date_to, "YYYY-MM-DD").add(dateDiff+1, 'days').format('YYYY-MM-DD'));
    let chart_pan_from = (display_vs_date_from == null ? null : moment(display_vs_date_from, "YYYY-MM-DD").add(-1*dateDiff, 'days').format('YYYY-MM-DD'));
    let chart_pan_to = (display_vs_date_to == null ? null : moment(display_vs_date_to, "YYYY-MM-DD").add(dateDiff, 'days').format('YYYY-MM-DD'));

    if(moment(display_vs_date_from, "YYYY-MM-DD").year() > 1000
        && moment(display_vs_date_to, "YYYY-MM-DD").year() > 1000){
            vs_chart_data_request = $.getJSON("ipd-vital-sign-show-chart-table-data.php",{'an': <?=json_encode($an)?>, 'start_vs_datetime': chart_data_from, 'end_vs_datetime': chart_data_to}, function(data) {
            vital_sign_show_chart_table_data = data;
            const btData = data/*.filter((v) => v.bt != null)*/.map((v)=>{
                return { x: moment(v.vs_datetime, "YYYY-MM-DD HH:mm:ss").toDate(), y: checkStringValue(v.bt,null,null,null) };
            });
            const pulseData = data/*.filter((v) => v.pr != null)*/.map((v)=>{
                return { x: moment(v.vs_datetime, "YYYY-MM-DD HH:mm:ss").toDate(), y: checkStringValue(v.pr,null,null,null) };
            });
            const sbpData = data/*.filter((v) => v.sbp != null)*/.map((v)=>{
                return { x: moment(v.vs_datetime, "YYYY-MM-DD HH:mm:ss").toDate(), y: checkStringValue(v.sbp,null,null,null) };
            });
            const dbpData = data/*.filter((v) => v.dbp != null)*/.map((v)=>{
                return { x: moment(v.vs_datetime, "YYYY-MM-DD HH:mm:ss").toDate(), y: checkStringValue(v.dbp,null,null,null) };
            });
            const rrData = data/*.filter((v) => v.rr != null)*/.map((v)=>{
                return { x: moment(v.vs_datetime, "YYYY-MM-DD HH:mm:ss").toDate(), y: checkStringValue(v.rr,null,null,null) };
            });
            const satData = data/*.filter((v) => v.sat != null)*/.map((v)=>{
                return { x: moment(v.vs_datetime, "YYYY-MM-DD HH:mm:ss").toDate(), y: checkStringValue(v.sat,null,null,null) };
            });
            const mapData = data/*.filter((v) => v.map != null)*/.map((v)=>{
                return { x: moment(v.vs_datetime, "YYYY-MM-DD HH:mm:ss").toDate(), y: checkStringValue(v.map,null,null,null) };
            });
            const lr_cerData = data/*.filter((v) => v.lr_cer != null)*/.map((v)=>{
                return { x: moment(v.vs_datetime, "YYYY-MM-DD HH:mm:ss").toDate(), y: checkStringValue(v.lr_cer,null,null,null) };
            });
            const lr_effData = data/*.filter((v) => v.lr_eff != null)*/.map((v)=>{
                return { x: moment(v.vs_datetime, "YYYY-MM-DD HH:mm:ss").toDate(), y: checkStringValue(v.lr_eff,null,null,null) };
            });
            const lr_sta_nameData = data/*.filter((v) => v.lr_sta_name != null)*/.map((v)=>{
                return { x: moment(v.vs_datetime, "YYYY-MM-DD HH:mm:ss").toDate(), y: checkStringValue(v.lr_sta_name,null,null,null) };
            });
            // const mapData = data/*.filter((v) => v.sbp != null && v.dbp != null)*/.map((v)=>{
            //     return { x: moment(v.vs_datetime, "YYYY-MM-DD HH:mm:ss").toDate(), y: roundNumber((((parseFloat(v.dbp,10)*2)+parseFloat(v.sbp,10))/3),2) };
            // });
            // console.log(btData);
            // console.log(pulseData);

            var chartStart = null;
            var chartEnd = null;
            var timeUnit = false;
            var unitStepSize = 1;
            if(display_vs_date_from != null && display_vs_date_from != '' && display_vs_date_to != null && display_vs_date_to != ''){
                if(display_vs_date_from != null && display_vs_date_from != ''){
                    chartStart = moment(display_vs_date_from).startOf('day');
                }
                if(display_vs_date_to != null && display_vs_date_to != ''){
                    chartEnd = moment(display_vs_date_to).endOf('day');
                }
                var dateDiff = moment(display_vs_date_to).diff(moment(display_vs_date_from), 'days')+1;
                // console.log(dateDiff);
                if(dateDiff > 30){
                    timeUnit = 'month';
                    unitStepSize = 1;
                // }
                // else if(dateDiff > 30){
                //     timeUnit = 'week';
                //     unitStepSize = 1;
                // } else if(dateDiff >= 15){
                //     timeUnit = 'day';
                //     unitStepSize = 1;
                // } else if(dateDiff >= 7){
                //     timeUnit = 'hour';
                //     unitStepSize = 6;
                // } else if(dateDiff >= 3){
                //     timeUnit = 'hour';
                //     unitStepSize = 3;
                }  else if(dateDiff >= 3){
                    timeUnit = 'day';
                    unitStepSize = 1;
                } else {
                    timeUnit = 'hour';
                    unitStepSize = 1;
                }
            }

            let datasets = [
                {
                    yAxisID: 'y-axis-2',
                    label: 'BT',
                    lineTension: 0,
                    backgroundColor: window.chartColors.blue,
                    borderColor: window.chartColors.blue,
                    data: btData,
                    fill: false,
                },
                {
                    yAxisID: 'y-axis-1',
                    label: 'PR',
                    lineTension: 0,
                    fill: false,
                    borderDash: [10, 10],
                    backgroundColor: window.chartColors.red,
                    borderColor: window.chartColors.red,
                    data: pulseData,
                },
                {
                    yAxisID: 'y-axis-3',
                    // yAxisID: 'y-axis-4',
                    label: 'RR',
                    lineTension: 0,
                    fill: false,
                    backgroundColor: 'green',
                    borderColor: 'green',
                    showLine: false,
                    pointStyle: 'rect',
                    pointRadius: 5,
                    pointHoverRadius: 5,
                    data: rrData,
                    hidden: true,
                },
                {
                    yAxisID: 'y-axis-1',
                    label: 'SBP',
                    lineTension: 0,
                    fill: false,
                    backgroundColor: "black",
                    borderColor: "black",
                    showLine: false,
                    pointStyle: triangle_down,
                    data: sbpData,
                    hidden: true,
                },
                {
                    yAxisID: 'y-axis-1',
                    label: 'DBP',
                    lineTension: 0,
                    fill: false,
                    backgroundColor: "black",
                    borderColor: "black",
                    showLine: false,
                    pointStyle: triangle_up,
                    data: dbpData,
                    hidden: true,
                },
                {
                    yAxisID: 'y-axis-1',
                    label: 'MAP',
                    lineTension: 0,
                    fill: false,
                    backgroundColor: "red",
                    borderColor: "red",
                    showLine: false,
                    pointStyle: 'crossRot',
                    pointRadius: 5,
                    pointHoverRadius: 5,
                    data: mapData,
                    hidden: true,
                },
                {
                    yAxisID: 'y-axis-3',
                    label: 'O2 Sat',
                    lineTension: 0,
                    fill: false,
                    backgroundColor: 'orange',
                    borderColor: 'orange',
                    showLine: false,
                    data: satData,
                    hidden: true,
                },
            ];
            if(lr_sta_nameData.length > 0 || lr_effData.length > 0 || lr_cerData.length > 0){
                datasets.push(
                    {
                        yAxisID: 'y-axis-5',
                        label: 'Station',
                        lineTension: 0,
                        fill: false,
                        backgroundColor: '#D0A9F5',
                        borderColor: '#D0A9F5',
                        //showLine: false,
                        data: lr_sta_nameData,
                        hidden: true,
                    },
                    {
                        yAxisID: 'y-axis-3',
                        label: 'Effacement',
                        lineTension: 0,
                        fill: false,
                        backgroundColor: '#81F7D8',
                        borderColor: '#81F7D8',
                        //showLine: false,
                        data: lr_effData,
                        hidden: true,
                    },
                    {
                        yAxisID: 'y-axis-3',
                        label: 'Cervix',
                        lineTension: 0,
                        fill: false,
                        backgroundColor: '#868A08',
                        borderColor: '#868A08',
                        //showLine: false,
                        data: lr_cerData,
                        hidden: true,
                    }
                );
            }

            //แสดงผลกราฟ
            var config = {
                type: 'line',
                data: {
                    // labels: ['24', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11',
                    //             '12', '13','14','15','16','17','18','18','20','21','22','23'],
                    datasets: datasets,
                },
                options: {
                    onClick: chartClickEvent,
                    legend: {
                        labels: {
                            usePointStyle: true
                        }
                    },
                    spanGaps: true,
                    aspectRatio: 2.75,
                    maintainAspectRatio: true,
                    responsive: true,
                    title: {
                        display: true,
                        text: 'Vital Sign'
                    },
                    tooltips: {
                        mode: 'index',
                        intersect: false,
                    },
                    hover: {
                        mode: 'index',
                        intersect: false,
                    },
                    annotation: {
                        drawTime: 'beforeDatasetsDraw',
                        annotations: [{
                            type: 'line',
                            mode: 'horizontal',
                            scaleID: 'y-axis-2',
                            value: 37,
                            borderColor: 'crimson',
                            borderWidth: 1,
                            label: {
                                enabled: false,
                                content: 'BT 37°C'
                            }
                        }]
                    },
                    scales: {
                        xAxes: [{
                            type: 'time',
                            time: {
                                // unit: 'minute',
                                unit: timeUnit,
                                unitStepSize: unitStepSize,
                                tooltipFormat: 'DD/MM/YYYY HH:mm',
                                displayFormats: {
                                    millisecond: 'HH:mm:ss.SSS',
                                    second: 'HH:mm:ss',
                                    minute: 'HH:mm',
                                    hour: '(Do MMM) HH:mm',
                                    day: 'Do MMM',
                                }
                            },
                            display: true,
                            scaleLabel: {
                                // display: true,
                                // labelString: 'Month'
                                display: true
                            },
                            ticks: {
                                min: chartStart,
                                max: chartEnd,
                            }
                        }],
                        yAxes: [
                        {
                            id: 'y-axis-1',
                            display: true,
                            gridLines: {
                                display: false
                            },
                            color: window.chartColors.red,
                            scaleLabel: {
                                display: true,
                                labelString: 'Pulse Rate, Blood Pressure'
                            },
                            ticks: {
                                suggestedMin: 40,
                                suggestedMax: 160
                                // min: 40,
                                // max: 160,
                            }
                        },
                        {
                            id: 'y-axis-2',
                            display: true,
                            color: window.chartColors.blue,
                            scaleLabel: {
                                display: true,
                                labelString: 'Body Temp (°C)'
                            },
                            ticks: {
                                suggestedMin: 35,
                                suggestedMax: 41
                                // min: 35,
                                // max: 41,
                            }
                        },
                        {
                            id: 'y-axis-3',
                            display: true,
                            gridLines: {
                                display: false
                            },
                            position: 'right',
                            color: window.chartColors.green,
                            scaleLabel: {
                                display: true,
                                labelString: 'O2 Sat, RR, Cervix, Effacement'
                            },
                            ticks: {
                                suggestedMin: 0,
                                suggestedMax: 100
                                // min: 0,
                                // max: 100,
                            }
                        },
                        {
                            id: 'y-axis-5',
                            display: true,
                            gridLines: {
                                display: false
                            },
                            position: 'right',
                            color: window.chartColors.green,
                            scaleLabel: {
                                display: true,
                                labelString: 'Station'
                            },
                            ticks: {
                                suggestedMin: -2,
                                suggestedMax: 8
                                // min: -2,
                                // max: 8,
                            }
                        },
                        // {
                        //     id: 'y-axis-4',
                        //     display: true,
                        //     gridLines: {
                        //         display: false
                        //     },
                        //     position: 'right',
                        //     color: window.chartColors.green,
                        //     scaleLabel: {
                        //         display: true,
                        //         labelString: 'Respiration Rate'
                        //     },
                        //     ticks: {
                        //         suggestedMin: 0,
                        //         suggestedMax: 100
                        //     }
                        // }
                        ]
                    },
                    // elements: {
                    //     point: {
                    //         pointStyle: 'cross'
                    //     }
                    // },
                    pan: {
                        enabled: true,
                        mode: "x",
                        speed: 10,
                        threshold: 10,
                        rangeMin: {
                            // Format of min pan range depends on scale type
                            x: moment(chart_pan_from, "YYYY-MM-DD").toDate(),
                        },
                        rangeMax: {
                            // Format of max pan range depends on scale type
                            x: moment(chart_pan_to, "YYYY-MM-DD").toDate(),
                        },
                        // onPanComplete: function({chart}) {
                        //     console.log(`I was panned!!!`);
                        // }
                    },
                    zoom: {
                        enabled: false,
                        drag: false,
                        mode: "xy",
                        limits: {
                            max: 10,
                            min: 0.5
                        }
                    },
                }
            };
            if(window.myLine != null){
                window.myLine.destroy();
                window.myLine = null;
            }
            for (var id in Chart.instances) {
                let chart = Chart.instances[id];
                chart.destroy();
            }

            // $('#canvasDiv').html('<canvas id="canvas"></canvas>');
            //ปรับแก้
          //  var ctx = document.getElementById('canvas').getContext('2d');
          //  window.myLine = new Chart(ctx, config);

        });
    } else {
        if(window.myLine != null){
            window.myLine.destroy();
            window.myLine = null;
        }
        $('#canvasDiv').html('<canvas id="canvas"></canvas>');
    }
}

function chartClickEvent(event, array){
    // console.log('event', event);
    // console.log('chartClickEvent', array);
    // if (event === 'undefined' || event == null) {
    //     return;
    // }
    // if (array === 'undefined' || array == null) {
    //     return;
    // }
    // if (array.length <= 0) {
    //     return;
    // }
    // let clickedData =  vital_sign_show_chart_table_data[array[0]._index];
    // console.log(array[0]._index);
    // console.log(clickedData);
    // console.log(clickedData.vs_id);
    // console.log(clickedData.an);
    // document.getElementById('vs_table_row_vs_id_' + clickedData.vs_id).scrollIntoView();
    // $('[id^=vs_table_row_vs_id_]').removeClass('vs-table-highlight');
    // $('#vs_table_row_vs_id_' + clickedData.vs_id).addClass('vs-table-highlight');
    // onclickVsTableRow(clickedData.vs_id, clickedData.an);
}

if ('matchMedia' in window) {
    window.matchMedia('print').addListener(function (media) {
        for (var id in Chart.instances) {
            Chart.instances[id].resize();
        }
    });
} else {
    window.addEventListener("beforeprint", function () {
        for (var id in Chart.instances) {
            Chart.instances[id].resize();
        }
    });
}

function resetZoom() {
    window.myLine.resetZoom();
}

$( document ).ready(function() {
    moment.locale('en');
    let display_vs_date = null;
    if(VS_DCHDATE != null && VS_DCHDATE != ''){
        display_vs_date = moment(VS_DCHDATE);
    } else {
        display_vs_date = moment();
    }
    let display_vs_date_to = display_vs_date.format("YYYY-MM-DD");
    let display_vs_date_from = display_vs_date.subtract(7-1, "days").format("YYYY-MM-DD");
    document.getElementById("display_vs_date_from").value = display_vs_date_from;
    document.getElementById("display_vs_date_to").value = display_vs_date_to;
    getVitalSignDataForChart(display_vs_date_from, display_vs_date_to);
});
</script>
