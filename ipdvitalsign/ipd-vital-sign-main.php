<?php
    
    require_once '../include/Session.php';
    Session::checkLoginSessionAndShowMessage(); //เช็ค session
    // SessionManager::checkPermissionAndShowMessage('KPHIS_ACCESS_IPD_NURSE_PROGRAM');
    //$check=Session::checkPermissionAndShowMessage('VITAL_SIGN','VIEW');
    // if(!SessionManager::checkPermission('VITAL_SIGN','VIEW')){
    //     // SessionManager::showMessage();
    //     return;
    // }

    require_once '../mains/main-report.php'; //เรียกใช้งาน เมนู
    require_once '../include/SelectUtils.php';

?>
<script src="..\node_modules\js-cookie\src\js.cookie.js"></script>
<script src="../vendor/moment/moment/min/moment-with-locales.min.js"></script>
<script src="../vendor/nnnick/chartjs/dist/Chart.min.js"></script>
<script src="../vendor/nnnick/chartjs/samples/utils.js"></script>
<script src="../node_modules/chartjs-plugin-annotation/chartjs-plugin-annotation.min.js"></script>
<script src="../node_modules/hammerjs/hammer.min.js"></script>
<script src="../node_modules/chartjs-plugin-zoom/chartjs-plugin-zoom.min.js"></script>
<script>
$(function(){
    $('#vital_sign_result').dataTable({
        "dom": 'ltrip'
    });
});
</script>
<style>
    canvas{
        -moz-user-select: none;
        -webkit-user-select: none;
        -ms-user-select: none;
    }
</style>
<div class="container-fluid pt-3">
    <div class="row">
        <div class="col-3 d-print-none">
            <div class="pb-3" id="show_ward">
                <select class="form-control" id="ward" name="ward">
                    <option value="">เลือกหน่วยงาน</option>
                    <?=SelectUtils::getWardSelectOption(null)?>
                </select>
            </div>
            <div id="show-patient" style="height : calc(100vh - 150px); box-sizing: border-box; overflow-y: auto;"></div>
        </div>
        <div class="col" style="height : calc(100vh - 90px); box-sizing: border-box; overflow-y: auto;">
            <div id="ipd-show-patient-main"></div>
            <div id="show-chart-table"></div>
        </div>
        <?php
         $a = 1;

        if($a == 1/*SessionManager::checkPermission('VITAL_SIGN','ADD')
            || SessionManager::checkPermission('VITAL_SIGN','EDIT')*/){?>
        <div class="col-4 d-print-none">
            <div id="show-vital-sign-form" style="height : calc(100vh - 90px); box-sizing: border-box; overflow-y: auto;"></div>
        </div>
        <?php
        }?>
    </div>
</div>
<script>
    $("#show-chart-table").hide(); //แก้ไข
    $( document ).ready(function() {
        var ward_select = localStorage.getItem("ward_select");
        if(ward_select == null){
            ward_select = Cookies.get('ward_select', { path: '.' });
            localStorage.setItem("ward_select", ward_select);
        }
        if(ward_select != null){
            $("#ward").val(ward_select);
            onchangeWardSelect();
        }
        $("#ward").change(onchangeWardSelect);
        $("#ward").select2();
    });

    function onchangeWardSelect(){
        var ward = $("#ward").val();
        localStorage.setItem("ward_select", ward);
        Cookies.set('ward_select', ward, { path: '.', expires: 365 });
        var url="ipd-vital-sign-show-data-patient.php?ward=" + ward;
        $.get(url,function(data){
            $("#show-patient").html(data);
        });
    }

    var selected_row = null;
    function onclick_vital_sign_form(event,row,an){
        if ( $(selected_row).hasClass('bg-info') ) {
            $(selected_row).removeClass('bg-info');
        }
        $(row).addClass('bg-info');
        selected_row = row;

        $("#show-chart-table").show();
        $("#show-vital-sign-form").html('');
        var url="ipd-vital-sign-show-form.php";//แก้ไข
        $.get(url,{an,'data_mode':'I'},function(data_vital_sign){
            $("#show-vital-sign-form").html(data_vital_sign);
        });

        $("#ipd-show-patient-main").html('');
        var url="ipd-show-patient-main.php";
        $.get(url,{an},function(data){
            $("#ipd-show-patient-main").html(data);
        });

        $("#show-chart-table").html('');
        var url="ipd-vital-sign-show-chart.php";
        $.get(url,{an},function(data){
            $("#show-chart-table").html(data);
            var url="ipd-vital-sign-show-table.php";
            $.get(url,{an},function(data){
                $("#show-chart-table").append(data);
            });
        });
    }
</script>