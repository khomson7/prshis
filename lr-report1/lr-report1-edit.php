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
        $movement =  $row['movement'];
        $head =  $row['head'];
        $eyes =  $row['eyes'];
        $nose =  $row['nose'];
        $mouth =  $row['mouth'];
        $neck =  $row['neck'];
        $abdomen =  $row['abdomen'];
        $navel =  $row['navel'];
        $spine =  $row['spine'];
        $limbs =  $row['limbs'];
        $genitalia =  $row['genitalia'];
        $anuss =  $row['anuss'];
        $skin_color =  $row['skin_color'];
        $behavior =  $row['behavior'];
        $expression =  $row['expression'];
      

        $first_symptom = $row['first_symptom'];
        $check_value = $row['check_value'];

        
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



var check_value = <?=json_encode($check_value)?>;
        if(receive_from == "ปกติ"){
           $("#v1").attr('checked',true);
          //  $("#receive_from2").attr('disabled', 'disabled');
        } else if(check_value != "ปกติ"){
           $("#v2").attr('checked',true);
           $('#v3').attr("disabled",false).val(<?=json_encode($check_value)?>);
          //  $("#receive_from2").attr('disabled', 'disabled');
        }


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


          //ประจำเดือน
          var period = <?=json_encode($period)?>;
        if(period == "ยังไม่มี"){
            $("#period1").attr('checked',true);
            $("input[name='period_normal']").prop("checked", false).attr("disabled", true);
            $('#period_disorders').attr("disabled", true);
            $('#period_lmp').attr("disabled", true);
        }else if(period == "มี"){
            $("#period2").attr('checked',true);
            $('#period_disorders').removeAttr("disabled");
            $('#period_lmp').removeAttr("disabled");
        }else if(period == "หมดประจำเดือน"){
            $("input[name='period_normal']").prop("checked", false).attr("disabled", true);
            $("#period3").attr('checked',true);
            $('#period_disorders').attr("disabled", true);
            $('#period_lmp').attr("disabled", true);
        }

        

        //body 
        var body = <?=json_encode($body)?>;

        if(body == "ปกติ"){
            $("#body1").attr('checked',true);
        }else if(!(body == "ปกติ") && body != null ){
            $("#body2").attr('checked',true);
            $('#body_text').attr("disabled",false).val(<?=json_encode($body)?>);
        }

        //cry 
        var cry = <?=json_encode($cry)?>;
        if(cry == "ไม่ร้อง"){
            $('#cry1').attr('checked',true);
        }else if(cry == "ร้องเสียงดัง"  ){
            $('#cry2').attr('checked',true);
        }
        else if(!(cry == "ร้องเสียงดัง" || cry == "ไม่ร้อง") && cry != null){
            $('#cry3').attr('checked',true);
            $('#cry_text').attr("disabled",false).val(<?=json_encode($cry)?>);
            
        }

        //movement 
        var movement = <?=json_encode($movement)?>;
        if(movement == "ขยับได้"){
            $('#movement1').attr('checked',true);
        }else if(movement == "อ่อนปวกเปียก"  ){
            $('#movement2').attr('checked',true);
        }else if(movement == "ชักเกร็ง"  ){
            $('#movement3').attr('checked',true);
        }
        else if(!(movement == "ขยับได้" || movement == "อ่อนปวกเปียก" || movement == "ชักเกร็ง") && movement != null){
            $('#movement4').attr('checked',true);
           // $('#movement4').attr("disabled",false).val('');
            $('#movement_text').attr("disabled",false).val(<?=json_encode($movement)?>);
            
        }

  //head 
  var head = <?=json_encode($head)?>;
if(head == "ปกติ"){
    $("#head1").attr('checked',true);
}else if(!(head == "ปกติ") && head != null ){
    $("#head2").attr('checked',true);
    $('#head_text').attr("disabled",false).val(<?=json_encode($head)?>);
}

  //eyes 
  var eyes = <?=json_encode($eyes)?>;
if(eyes == "ปกติ"){
    $("#eyes1").attr('checked',true);
}else if(!(eyes == "ปกติ") && eyes != null ){
    $("#eyes2").attr('checked',true);
    $('#eyes_text').attr("disabled",false).val(<?=json_encode($eyes)?>);
}

//nose 
var nose = <?=json_encode($nose)?>;

if(nose == "มีรูจมูก 2 ข้าง"){
    $('#nose1').attr('checked',true);
}else if(nose == "รูจมูกตัน"  ){
    $('#nose2').attr('checked',true);
}
else if(!(nose == "มีรูจมูก 2 ข้าง" || nose == "รูจมูกตัน") && nose != null){
    $('#nose3').attr('checked',true);
    $('#nose_text').attr("disabled",false).val(<?=json_encode($nose)?>);
  
}

  //mouth
  var mouth = <?=json_encode($mouth)?>;
if(mouth == "ปกติ"){
    $('#mouth1').attr('checked',true);
}else if(mouth == "ปากแหว่ง"  ){
    $('#mouth2').attr('checked',true);
}else if(mouth == "เพดานโหว่"  ){
    $('#mouth3').attr('checked',true);
}
else if(!(mouth == "ปกติ" || mouth == "ปากแหว่ง" || mouth == "เพดานโหว่") && mouth != null){
    $('#mouth4').attr('checked',true);
    $('#mouth_text').attr("disabled",false).val(<?=json_encode($mouth)?>);
    
}

  //neck 
  var neck = <?=json_encode($neck)?>;

if(neck == "ปกติ"){
    $("#neck1").attr('checked',true);
}else if(!(neck == "ปกติ") && neck != null ){
    $("#neck2").attr('checked',true);
    $('#neck_text').attr("disabled",false).val(<?=json_encode($neck)?>);
}

    //abdomen 
    var abdomen = <?=json_encode($abdomen)?>;
        if(abdomen == "ปกติ"){
            $('#abdomen1').attr('checked',true);
        }else if(abdomen == "ท้องอืด"  ){
            $('#abdomen2').attr('checked',true);
        }
        else if(!(abdomen == "ปกติ" || abdomen == "ท้องอืด") && abdomen != null){
            $('#abdomen3').attr('checked',true);
            $('#abdomen_text').attr("disabled",false).val(<?=json_encode($abdomen)?>);
            
        }

         //navel 
  var navel = <?=json_encode($navel)?>;
if(navel == "ปกติ"){
    $('#navel1').attr('checked',true);
}else if(navel == "Omphalocele"  ){
    $('#navel2').attr('checked',true);
}else if(navel == "Gastroschisis"  ){
    $('#navel3').attr('checked',true);
}
else if(!(navel == "ปกติ" || navel == "Omphalocele" || navel == "Gastroschisis") && navel != null){
    $('#navel4').attr('checked',true);
    $('#navel_text').attr("disabled",false).val(<?=json_encode($navel)?>);
    
}

  //spine 
  var spine = <?=json_encode($spine)?>;

if(spine == "ปกติ"){
    $("#spine1").attr('checked',true);
}else if(!(spine == "ปกติ") && spine != null ){
    $("#spine2").attr('checked',true);
    $('#spine_text').attr("disabled",false).val(<?=json_encode($spine)?>);
}

  //limbs 
  var limbs = <?=json_encode($limbs)?>;

if(limbs == "ปกติ"){
    $("#limbs1").attr('checked',true);
}else if(!(limbs == "ปกติ") && limbs != null ){
    $("#limbs2").attr('checked',true);
    $('#limbs_text').attr("disabled",false).val(<?=json_encode($limbs)?>);
}

  //genitalia 
  var genitalia = <?=json_encode($genitalia)?>;

if(genitalia == "ปกติ"){
    $("#genitalia1").attr('checked',true);
}else if(!(genitalia == "ปกติ") && genitalia != null ){
    $("#genitalia2").attr('checked',true);
    $('#genitalia_text').attr("disabled",false).val(<?=json_encode($genitalia)?>);
}

 //anuss
 var anuss = <?=json_encode($anuss)?>;
if(anuss == "ปกติ"){
    $("#anuss1").attr('checked',true);
}else if(anuss == "ไม่มีรูก้น"){
    $("#anuss2").attr('checked',true);
}

         //skin_color 
         var skin_color = <?=json_encode($skin_color)?>;

if(skin_color == "แดง"){
    $('#skin_color1').attr('checked',true);
}else if(skin_color == "ซีด"  ){
    $('#skin_color2').attr('checked',true);
}else if(skin_color == "เขียว"  ){
    $('#skin_color3').attr('checked',true);
}
else if(!(skin_color == "แดง" || skin_color == "ซีด" || skin_color == "เขียว") && skin_color != null){
    $('#skin_color4').attr('checked',true);
    $('#skin_color_text').attr("disabled",false).val(<?=json_encode($skin_color)?>);
    
}

//behavior 
var behavior = <?=json_encode($behavior)?>;

if(behavior == "เฉย"){
    $('#behavior1').attr('checked',true);
}else if(behavior == "ร้องไห้"  ){
    $('#behavior2').attr('checked',true);
}
else if(!(behavior == "เฉย" || behavior == "ร้องไห้") && behavior != null){
    $('#behavior3').attr('checked',true);
    $('#behavior_text').attr("disabled",false).val(<?=json_encode($behavior)?>);
    
}

//expression 
var expression = <?=json_encode($expression)?>;

if(expression == "ประเมินไม่ได้"){
    $('#expression1').attr('checked',true);
}else if(expression == "ร้องโกรธ"  ){
    $('#expression2').attr('checked',true);
}
else if(!(expression == "ประเมินไม่ได้" || expression == "ร้องโกรธ") && expression != null){
    $('#expression3').attr('checked',true);
    $('#expression_text').attr("disabled",false).val(<?=json_encode($expression)?>);
    
}


        //ดึงข้อมูลที่บันทึกมาแสดง
        $("textarea#cc").val(<?=json_encode($cc)?>);
        $("textarea#abnormal").val(<?=json_encode($abnormal)?>);
        $("textarea#family").val(<?=json_encode($family)?>);
        $("textarea#first_symptom").val(<?=json_encode($first_symptom)?>);

        });


        

</script>
