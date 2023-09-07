<?php
        require_once '../include/Session.php';
        Session::checkLoginSessionAndShowMessage(); //เช็ค session
        require_once '../include/DbUtils.php';
        require_once '../include/KphisQueryUtils.php';
        $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
        $an = $_REQUEST['an'];
        $hn = KphisQueryUtils::getHnByAn($an);   // function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา
        $id = $_REQUEST['id'];
        $query_parameters = [
                                ':id' => $id,
                                ':an' => $an
                            ];
        $sql = "SELECT * FROM ".DbConstant::KPHIS_DBNAME.".prs_labor_report1 WHERE an = :an AND id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute($query_parameters);
        $rowCount = 0;
        //fetch Data
        $row = $stmt->fetch();
        $an = $row['an'];
        $create_datetime = $row['create_datetime'];
        $id = $row['id'];
        $receive_date = $row['receive_date'];
        $receive_time = $row['receive_time'];
        $receive_from = $row['receive_from'];
        $transport = $row['transport'];
        $cc = $row['cc'];
        $ga = $row['ga'];
        $labor = $row['labor'];
        $indication = $row['indication'];
        $labor_date = $row['labor_date'];
        $labor_time = $row['labor_time'];
        $sex = $row['sex'];
        $weight = $row['weight'];
        $apgar_score_1 = $row['apgar_score_1'];
        $subtract_1 = $row['subtract_1'];
        $apgar_score_5 = $row['apgar_score_5'];
        $subtract_5 = $row['subtract_5'];
        $apgar_score_10 = $row['apgar_score_10'];
        $subtract_10 = $row['subtract_10'];
        $abnormal = $row['abnormal'];
        $g = $row['g'];
        $p = $row['p'];
        $serology = $row['serology'];
        $antepartum = $row['antepartum'];
        $dt_vaccine = $row['dt_vaccine'];
        $family = $row['family'];
        $bt = $row['bt'];
        $hr = $row['hr'];
        $rr = $row['rr'];
        $ofs =  $row['ofs'];
        $om =  $row['om'];
        $chest =  $row['chest'];
        $body_long =  $row['body_long'];
        $cord =  $row['cord'];
        $anus =  $row['anus'];
        $body =  $row['body'];
        $cry =  $row['cry'];

        
        $update_datetime = $row['update_datetime'];
        $version = $row['version'];

//echo $receive_from;

?>

<script>
    $("#lr_report1_form").each(function() {
$("input[name=version]").val(<?=json_encode($version)?>);
//ดึงวันที่และเวลาที่บันทึกมาแสดง
$("input[name=receive_date]").val(<?=json_encode($receive_date )?>);
$("input[name=receive_time]").val(<?=json_encode($receive_time )?>);
$("input[name=ga]").val(<?=json_encode($ga)?>);
$("input[name=labor]").val(<?=json_encode($labor)?>);
$("input[name=indication]").val(<?=json_encode($indication)?>);
$("input[name=labor_date]").val(<?=json_encode($labor_date)?>);
$("input[name=labor_time]").val(<?=json_encode($labor_time )?>);
$("input[name=weight]").val(<?=json_encode($weight)?>);
$("input[name=apgar_score_1]").val(<?=json_encode($apgar_score_1)?>);
$("input[name=subtract_1]").val(<?=json_encode($subtract_1)?>);
$("input[name=apgar_score_5]").val(<?=json_encode($apgar_score_5)?>);
$("input[name=subtract_5]").val(<?=json_encode($subtract_5)?>);
$("input[name=apgar_score_10]").val(<?=json_encode($apgar_score_10)?>);
$("input[name=subtract_10]").val(<?=json_encode($subtract_10)?>);
$("input[name=g]").val(<?=json_encode($g)?>);
$("input[name=p]").val(<?=json_encode($p)?>);
$("input[name=serology]").val(<?=json_encode($serology)?>);
$("input[name=antepartum]").val(<?=json_encode($antepartum)?>);
$("input[name=dt_vaccine]").val(<?=json_encode($dt_vaccine)?>);
$("input[name=bt]").val(<?=json_encode($bt)?>);
$("input[name=hr").val(<?=json_encode($hr)?>);
$("input[name=rr]").val(<?=json_encode($rr)?>);
$("input[name=ofs]").val(<?=json_encode($ofs)?>);
$("input[name=om]").val(<?=json_encode($om)?>);
$("input[name=chest]").val(<?=json_encode($chest)?>);
$("input[name=body_long]").val(<?=json_encode($body_long)?>);
$("input[name=cord]").val(<?=json_encode($cord)?>);
$("input[name=anus]").val(<?=json_encode($anus)?>);



        var receive_from = <?=json_encode($receive_from)?>;
        if(receive_from == "คลอดในโรงพยาบาล"){
           $("#receive_from1").attr('checked',true);
          //  $("#receive_from2").attr('disabled', 'disabled');
        } else if(receive_from != "คลอดในโรงพยาบาล"){
           $("#receive_from1").attr('checked',false);
           $("#receive_from2").attr('checked',true);
           $('#from_text').attr("disabled",false).val(<?=json_encode($receive_from)?>);
          //  $("#receive_from2").attr('disabled', 'disabled');
        }

        //transport  
        var transport = <?=json_encode($transport)?>;

        if(transport == "อุ้มมา"){
            $("#transport1").attr('checked',true);
        }else if(transport == "transport incubator"){
            $("#transport2").attr('checked',true);
        }else if(transport == "clib"){
            $("#transport3").attr('checked',true);
        }

        //sex
        var sex = <?=json_encode($sex)?>;

        if(sex == "1"){
            $("#sex1").attr('checked',true);
        }else if(sex == "2"){
            $("#sex2").attr('checked',true);
        }

        //body 
        var body = <?=json_encode($body)?>;

        if(body == "ปกติ"){
            $('#body_text').attr("disabled",true).val('');
            $("#body1").attr('checked',true);
        }else if(body != "ปกติ" && body != null ){
            $("#body2").attr('checked',true);
            $('#body_text').attr("disabled",false).val('');
            $("input[name=body]").val(<?=json_encode($body)?>);
        }

        //body 
        var cry = <?=json_encode($cry)?>;

        if(cry == "ไม่ร้อง"){
            $('#cry_text').attr("disabled",true).val('');
            $('#cry1').attr('checked',true);
        }else if(cry == "ร้องเสียงดัง"  ){
            $('#cry_text').attr("disabled",true).val('');
            $('#cry2').attr('checked',true);
        }
        else if((cry != "ร้องเสียงดัง" || cry != "ไม่ร้อง") && cry != null){
            $('#cry_text').attr("disabled",false).val('');
            $('#cry1').attr('checked',true);
            $("input[name=cry]").val(<?=json_encode($cry)?>);
            
        }




        //ดึงข้อมูลที่บันทึกมาแสดง
        $("textarea#cc").val(<?=json_encode($cc)?>);
        $("textarea#abnormal").val(<?=json_encode($abnormal)?>);
        $("textarea#family").val(<?=json_encode($family)?>);

        });


        

</script>
