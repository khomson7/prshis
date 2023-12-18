<?php
/*
require_once './project/function/SessionManager.php';
SessionManager::checkLoginSessionAndShowMessage(); //เช็ค session
// SessionManager::checkPermissionAndShowMessage('KPHIS_ACCESS_IPD_NURSE_PROGRAM');
if(!SessionManager::checkPermission('VITAL_SIGN','ADD') &&
   !SessionManager::checkPermission('VITAL_SIGN','EDIT')){
    // SessionManager::showMessage();
    return;
}
*/
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';
$an = empty($_REQUEST['an']) ? null : $_REQUEST['an'];
$vs_id = empty($_REQUEST['vs_id']) ? null : $_REQUEST['vs_id'];
$hn = KphisQueryUtils::getHnByAn($an);
$data_mode = empty($_REQUEST['data_mode']) ? 'I':$_REQUEST['data_mode'];

if($data_mode == 'U'){
    if(!empty($vs_id)){
        $conn = DbUtils::get_hosxp_connection();

        $sql = "SELECT vs_id,hn,an,vs_datetime, date(vs_datetime) as vs_date, time(vs_datetime) as vs_time,bt,pr,rr,respirator,sbp,dbp,inotrope,sat,cvp,
            end_co2,conscious_id,bw,height,urine,catheter,urine_amount,urine_duration,feces,head,t_inc,line_id,line_no,
            line_mark,braden,pain,eye,verbal,movement,right_pupil,right_cha_id,left_pupil,left_cha_id,
            va_id,lt_arm,lt_leg,rt_arm,rt_leg,concat(vs_id,an) as qrcode_,
            mass_id,severity,had_name,had_drop,hct,dtx,bl,mcb,suction,
            nb,o2_id,o2_flow,tube_id,tube_no,tube_mark,ventilator_name,mode,tv,pip,
            r_rate,i_rate,e_rate,ti,ps,fio2,peep,ft,delta_p,map,
            intake_id,intake_type,intake_amount,intake_absorb,output_id,output_amount,
            lr_int,lr_dur,lr_fsh,lr_sev,lr_cer,lr_eff,lr_sta,lr_mem,lr_af,other,
            create_user,create_datetime,update_user,update_datetime,version
            FROM ".DbConstant::KPHIS_DBNAME.".ipd_vs_vital_sign
            WHERE vs_id=:vs_id
            ORDER BY vs_datetime ";

        $parameters['vs_id'] = $vs_id;
        $stmt = $conn->prepare($sql);
        $stmt->execute($parameters);
        $row = $stmt->fetch();

        $an = $row['an'];
        $hn = $row['hn'];
        $qrcode_ = $row['qrcode_'];

    } else {
        exit;
    }
} else {
    $row = [];
}
?>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
    require_once '../include/SelectUtils.php';

    $PNG_TEMP_DIR = dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
    
    //html PNG location prefix
    $PNG_WEB_DIR = 'temp/';

    include "qrlib.php";    
    
    //ofcourse we need rights to create temp dir
    if (!file_exists($PNG_TEMP_DIR))
        mkdir($PNG_TEMP_DIR);
    
    
    $filename = $PNG_TEMP_DIR.'test.png';
    
    //processing form input
    //remember to sanitize user input in real-life solution !!!
    $errorCorrectionLevel = 'L';
    if (isset($_REQUEST['level']) && in_array($_REQUEST['level'], array('L','M','Q','H')))
        $errorCorrectionLevel = $_REQUEST['level'];    

    $matrixPointSize = 4;
    if (isset($_REQUEST['size']))
        $matrixPointSize = min(max((int)$_REQUEST['size'], 1), 10);


    if (isset($_REQUEST['data'])) { 
    
        //it's very important!
        if (trim($_REQUEST['data']) == '')
            die('data cannot be empty! <a href="?">back</a>');
            
        // user data
        $filename = $PNG_TEMP_DIR.'test'.md5($_REQUEST['data'].'|'.$errorCorrectionLevel.'|'.$matrixPointSize).'.png';
        QRcode::png($_REQUEST['data'], $filename, $errorCorrectionLevel, $matrixPointSize, 2);    
        
    } else {    
    
        //default data
       
      //  QRcode::png('PHP QR Code :)', $filename, $errorCorrectionLevel, $matrixPointSize, 2);    
        
    }    

    $data = $qrcode_;// Replace 'column_name' with the appropriate column from your table
        

    // user data
    $filename = $PNG_TEMP_DIR.'test'.md5($data.'|'.$errorCorrectionLevel.'|'.$matrixPointSize).'.png';
    QRcode::png($data, $filename, $errorCorrectionLevel, $matrixPointSize, 2);  

?>

<style>



img {
  border: 1px solid #ddd;
  border-radius: 4px;
  padding: 5px;
  width: 300px;
}
</style>

<div class="container-fluid">
    <div class="card border-primary row">
        <div class="card-body ">
            <div class="row pt-3">
                <div class="col-md-12">
                <form id="vital-sign-form" onsubmit="onclickVitalSignFormSaveButton(event)">
                    <input type="hidden" id="vs_id" name="vs_id" value="<?=htmlspecialchars($vs_id)?>">
                    <input type="hidden" id="vs_an" name="an" value="<?=htmlspecialchars($an)?>">
                    <input type="hidden" id="hn" name="hn" value="<?=htmlspecialchars($hn)?>">
                    <input type="hidden" id="data_mode" name="data_mode" value="<?=htmlspecialchars($data_mode)?>">
                   
                  
                    <nav class="pb-3">
                        <div class="nav nav-tabs" id="nav-tab" role="tablist">
                            <a class="nav-item nav-link active" id="nav-vitalsign-tab" data-toggle="tab" href="#nav-vitalsign"
                                role="tab" aria-controls="nav-vitalsign" aria-selected="true">QRCODE</a>
                        </div>
                    </nav>

                    <center><img src="<?=$PNG_WEB_DIR.basename($filename)?>" /></center><hr/>

                    <div class="tab-content" id="nav-vs-tabContent">
                        <div class="tab-pane fade show active" id="nav-vitalsign" role="tabpanel" aria-labelledby="nav-vitalsign-tab">
                                  
                        
                            <div class="form-group row">
                                <label for="sbp" class="col-sm-3 text-right col-form-label">ชื่อรายการ</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="sbp" name="sbp" value="<?=(isset($row['qrcode_']) ? htmlspecialchars($row['qrcode_']) : '')?>" oninput="oninputVitalSignValue()" readonly="true">
                                </div>
                                
                            </div>
        
                     
           
                            
  
                        </div>
 
                      
                    </div>
                  
                </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function onclick_map_calculate_button(event){
        var dbp = $('#dbp').val();
        var sbp = $('#sbp').val();
        var map = roundNumber((((parseFloat(dbp,10)*2)+parseFloat(sbp,10))/3),0);
        $('#map').val(Number.isNaN(map) ? '':map);
    }
    function onclickVitalSignFormSaveButton(event){
        event.preventDefault();
        saveDataAndReload();
    }
    function onclickVitalSignFormDeleteButton(event){
        if(confirm('ยืนยันการลบข้อมูล')){
            $('#data_mode').val('D');
            saveDataAndReload();
        }
    }
    function onclickVitalSignFormNewButton(event){
        var an = $('#vs_an').val();
        reload(an);
    }
    function reload(an){
        $("#show-vital-sign-form").html('');
        $.get("ipd-vital-sign-show-form.php",{an,'data_mode':'I'},function(data_vital_sign){
            $("#show-vital-sign-form").html(data_vital_sign);
        });

        $("#ipd-show-patient-main").html('');
        var url="ipd-show-patient-main.php";
        $.get(url,{an},function(data){
            $("#ipd-show-patient-main").html(data);
        });
    }

    function saveDataAndReload(){
        if($("#vs_date").val() != '' && $("#vs_time").val() != ''){
            // if($("#vs_date").val()){

            // }
            $.post("ipd-vital-sign-save.php",$("#vital-sign-form").serialize(),function(html){
                var an = $('#vs_an').val();
                $("#show-chart-table").html('');
                var url="ipd-vital-sign-show-chart.php";
                $.get(url,{an},function(data){
                    $("#show-chart-table").html(data);
                    var url="ipd-vital-sign-show-table.php";
                    $.get(url,{an},function(data){
                        $("#show-chart-table").append(data);
                    });
                });
                reload(an);
            });
        } else {
            alert('กรุณากรอกเวลาและวันที่ให้ครบถ้วน');
        }
    }
    $(document).ready(function() {
        if($('#data_mode').val() == 'I'){
            var now = moment();
            document.getElementById("vs_date").value = now.format("YYYY-MM-DD");
            // document.getElementById("vs_time").value = now.format("HH:mm");
        }
        oninputVitalSignValue();
    });
    function display_score(score, score_display_id){
        if(score === "" || score === null) {
            $('#'+score_display_id).html("");
        }else{
            if(score != null){
                let MEWS_COLOR = ['#45c351','#e6b728','#e8832a','#e51616'];
                $('#'+score_display_id).html("<div class='badge text-white mt-1 font-weight-bold' style='font-size:120%; background-color: " + MEWS_COLOR[score] + ";'>" + score + "</div>");
            }
        }
    }
    function display_score_total(score, score_display_id){
        if(score === "" || score === null) {
            $('#'+score_display_id).html("");
        }else{
            color = 'inherit';
            if(score === 0){
                color = '#45c351';
            }else if(score > 0 && score <= 3){
                color = '#e6b728';
            }else if(score >= 4){
                color = '#e51616';
            }
            $('#'+score_display_id).html("<div class='alert text-white text-center font-weight-bold' style='font-size:100%;  background-color: " + color + ";'> MEWS SCORE : " + score + "</div>");
        }
    }
    function oninputVitalSignValue(){
        let age_y = <?=json_encode(KphisQueryUtils::checkPatienAge($an))?>;
        let xc  = score_bt(age_y, $("#bt").val());
        let pr = score_pr(age_y, $("#pr").val());
        let rr = score_rr(age_y, $("#rr").val(), $('#respirator:checked').val());
        let sbp = score_sbp(age_y, $("#sbp").val(), $('#inotrope:checked').val());
        let conscious_id = score_conscious_id(age_y, $("#conscious_id").val());
        let urine = score_urine(age_y, $("#urine_amount").val(), $("#urine_duration").val());
        let total = score_total(age_y, bt, pr, rr, sbp, conscious_id, urine);

        display_score(bt, "score_bt_result");
        display_score(pr, "score_pr_result");
        display_score(rr, "score_rr_result");
        display_score(sbp, "score_sbp_result");
        display_score(conscious_id, "score_conscious_id_result");
        display_score(urine, "score_urine_result");

        display_score_total(total, "score_total_result");
    }
    function onchange_catheter(){
        let catheter = $('#catheter:checked').val();
        if(catheter){
            $("#urine").val('ใส่สายสวนฯ');
        }
    }
</script>