<?php  // require_once './project/function/Session.php';
       // Session::::checkPermissionAndShowMessage('ADMISSION_NOTE','VIEW');
       require_once '../include/Session.php';
       // Session::checkLoginSessionAndShowMessage(); //เช็ค session


       // Session::checkPermissionAndShowMessage('IPD_NURSE_ADDMISSION_NOTE','VIEW');
        require_once '../mains/main-report.php';
        require_once '../mains/opd-show-patient-main.php'; //เป็นส่วนที่แสดง ข้อมูลผู้ป่วย เช่น รูป,hn,an,ชื่อ-สกุล,แพ้ยา ฯลฯ
        require_once '../mains/opd-show-patient-main-sticky.php';
        require_once '../include/DbUtils.php';
        require_once '../include/KphisQueryUtils.php';

        $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
        $hn = empty($_REQUEST['hn']) ? null : $_REQUEST['hn'];
      //  $hn = '000000001';
       // $hn = KphisQueryUtils::getHnByAn($an);
       // $vn = KphisQueryUtils::getVnByAn($an);
        $vn = KphisQueryUtils::getVnByHn($hn);
        $an_parameters = ['an' => $an];
        $hn_parameters = ['hn' => $hn];
        $loginname = $_SESSION['loginname'];
        $values =['loginname'=>$loginname];

        //echo $loginname ;

        //-------------------------Doctor admission note
        $sql = "SELECT *
                FROM `prs_dr_admission_note`
                WHERE hn = :hn
                ORDER BY admission_note_id ASC";
        $stmt = $conn->prepare($sql);
        $stmt->execute($hn_parameters);
        if($row  = $stmt->fetch()){
            $admission_note_id = $row['admission_note_id'];
        } else {
            $admission_note_id = null;
        }
    //    echo $admission_note_id;
/*
        $sql_item ="SELECT dr_adm_item.admission_note_item_id,
                    dr_adm_item.admission_note_doctor,
                    doctor.`name` AS admission_note_doctorname
                    FROM ".DbConstant::KPHIS_DBNAME.".ipd_dr_admission_note_item dr_adm_item
                    LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".doctor ON doctor.code = dr_adm_item.admission_note_doctor
                    WHERE an=:an
                    ORDER BY dr_adm_item.admission_note_item_id ASC";
        $stmt_item = $conn->prepare($sql_item);
        $stmt_item->execute($hn_parameters);
        $admission_note_count = 0;
        while ($row_item = $stmt_item->fetch()){
            $admission_note_item_id[] = $row_item['admission_note_item_id'];
            $admission_note_doctor[] = $row_item['admission_note_doctor'];
            $admission_note_doctorname[] = $row_item['admission_note_doctorname'];
            //$admission_note_doctorentryposition[] = $row_item['admission_note_doctorentryposition'];
            $admission_note_count++;
        }

        */
        //------------------------Doctor admission note

        //cc,pi
        if($admission_note_id == null){
            $sql_opdscreen="SELECT opdscreen.vn,opdscreen.hn,opdscreen.cc,opdscreen.hpi,concat(round(opdscreen.bpd,0),'/',round(opdscreen.bps,0)) as bp,
                            opdscreen.pe_ga_text, opdscreen.pe_heent_text,opdscreen.hpi,
                            opdscreen.pmh,opdscreen.fh,
                            opdscreen.pe_heart_text, opdscreen.pe_lung_text,
                            opdscreen.pe_ab_text, opdscreen.pe_neuro_text,
                            opdscreen.pe_ext_text, opdscreen.pe, pt.cid, pt.passport_no, pt.hn,pt.pname,pt.fname,pt.lname,
                            vn.age_y,vn.age_m,vn.age_d
                            FROM ".DbConstant::HOSXP_DBNAME.".opdscreen
                            INNER JOIN ".DbConstant::HOSXP_DBNAME.".vn_stat vn on vn.vn = opdscreen.vn
                            INNER JOIN ".DbConstant::HOSXP_DBNAME.".patient pt on pt.hn = opdscreen.hn
                            WHERE opdscreen.vn= :vn ";
            $stmt_opdscreen = $conn->prepare($sql_opdscreen);
            $stmt_opdscreen->execute(['vn'=>$vn]);
            $row_opdscreen  = $stmt_opdscreen->fetch();
        }

        //ipt
        if($admission_note_id == null){
            $sql_ipt1="SELECT ipt.hn,ipt.regdate,ipt.regtime
                            FROM ".DbConstant::HOSXP_DBNAME.".ipt
                            WHERE ipt.vn= :vn ";
            $stmt_ipt1 = $conn->prepare($sql_ipt1);
            $stmt_ipt1->execute(['vn'=>$vn]);
            $row_ipt1  = $stmt_ipt1->fetch();
        }


        $sql_opduser = "SELECT opduser.entryposition,opduser.name
                        FROM ".DbConstant::HOSXP_DBNAME.".opduser
                        WHERE loginname = :loginname";
        $stmt_opduser = $conn->prepare($sql_opduser);
        $stmt_opduser->execute($values);
        $row_opduser  = $stmt_opduser->fetch();

        $sql_ipt = "select patient.sex,patient.hn,patient.pname,patient.fname,patient.lname,/*patient.drugallergy, */
            (select GROUP_CONCAT(concat(opd_allergy.agent,'=',if(opd_allergy.symptom is null,',',opd_allergy.symptom)/*,' [',if(note is null,',',note),']'*/)) as name
                from ".DbConstant::HOSXP_DBNAME.".opd_allergy
                where opd_allergy.hn = ipt.hn /*and (opd_allergy.no_alert<>'Y' or opd_allergy.no_alert is null)*/
                order by display_order) as drugallergy,
            an_stat.age_y,an_stat.age_m,an_stat.age_d,
            concat(ipt.regdate,' ',ipt.regtime) as regdatetime,
            ipt.dchdate,ipt.dchtime,
            ipt.ward,ward.name,
            ipt.pttype, pttype.`name` as pttype_name,
            iptadm.bedno, (select vs.bw from ipd_vs_vital_sign vs where vs.an = ipt.an and vs.bw is not null and trim(vs.bw) <> '' order by vs_datetime desc limit 1) as latest_bw
            , (select vs.height from ipd_vs_vital_sign vs where vs.an = ipt.an and vs.bw is not null and trim(vs.bw) <> '' order by vs_datetime desc limit 1) as latest_height
            , (select vs.vs_datetime from ipd_vs_vital_sign vs where vs.an = ipt.an and vs.bw is not null and trim(vs.bw) <> '' order by vs_datetime desc limit 1) as latest_bw_datetime
            from ".DbConstant::HOSXP_DBNAME.".ipt
            left outer join ".DbConstant::HOSXP_DBNAME.".an_stat on an_stat.an=ipt.an
            left outer join ".DbConstant::HOSXP_DBNAME.".patient on patient.hn=ipt.hn
            left outer join ".DbConstant::HOSXP_DBNAME.".ward on ward.ward=ipt.ward
            LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".pttype ON pttype.pttype = ipt.pttype
            LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".iptadm ON iptadm.an = ipt.an
            WHERE ipt.an=:an
            order by ipt.an
            ";
        $stmt_ipt = $conn->prepare($sql_ipt);
        $stmt_ipt->execute(['an'=>$an]);
        $row_ipt = $stmt_ipt->fetch();
        $regdatetime = $row_ipt["regdatetime"];

        //sql_drug_allergy
        // $sql_drug_allergy = "select (concat(opd_allergy.agent,'=',if(opd_allergy.symptom is null,',',opd_allergy.symptom)/*,' [',if(note is null,',',note),']'*/)) as name
        //         from ".DbConstant::HOSXP_DBNAME.".opd_allergy
        //         where opd_allergy.hn = :hn /*and (opd_allergy.no_alert<>'Y' or opd_allergy.no_alert is null)*/
        //         order by display_order
        //     ";
        // $stmt_sql_drug_allergy = $conn->prepare($sql_drug_allergy);
        // $stmt_sql_drug_allergy->execute(['hn'=>$hn]);
        // $row_sql_drug_allergy = $stmt_sql_drug_allergy->fetch();

        //ipt ล่าสุดก่อน an ปัจจุบัน
        $hnan_para = ['an' => $an,'hn'=>$hn];
        $sql_ipt = "SELECT concat(ipt.regdate,' ',ipt.regtime) as old_regdatetime
                    FROM  ".DbConstant::HOSXP_DBNAME.".ipt
                    where ipt.hn = :hn and ipt.an < :an
                    ORDER BY ipt.an DESC limit 1";
        $stmt_old_ipt = $conn->prepare($sql_ipt);
        $stmt_old_ipt->execute($hnan_para);
        $row_old_ipt = $stmt_old_ipt->fetch();

        $reg_parameters =['hn' => $hn,'regdatetime'=>$regdatetime,'hospital_name'=>DbConstant::HOSPITAL_NAME];
        $sql_ol = "SELECT CONCAT(ifnull(operation_list.enter_date,''),', ',ifnull(operation_list.operation_name,''),', ',ifnull(doctor.name,''),', ',:hospital_name) AS operation_list
                    FROM ".DbConstant::HOSXP_DBNAME.".operation_list
                    left outer join ".DbConstant::HOSXP_DBNAME.".doctor on doctor.code = operation_list.request_doctor
                    WHERE operation_list.hn= :hn
                    AND operation_list.status_id = 3
                    AND concat(operation_list.enter_date,' ',operation_list.enter_time) < :regdatetime
                    ORDER BY operation_list.enter_date,operation_list.enter_time";
        $stmt_ol = $conn->prepare($sql_ol);
        $stmt_ol->execute($reg_parameters);
        $rows_ol  = $stmt_ol->fetchAll();
        $operation_text = "";
        foreach($rows_ol as $row_ol):
			$operation_text .= '<label>'.htmlspecialchars($row_ol["operation_list"]).'</label><br>';
        endforeach;

        //Vital Sign
        $sql_vs =   "SELECT opd_vs_vital_sign.sbp,opd_vs_vital_sign.dbp,opd_vs_vital_sign.bt,opd_vs_vital_sign.pr,opd_vs_vital_sign.rr,
                    opd_vs_vital_sign.eye,opd_vs_vital_sign.verbal,opd_vs_vital_sign.movement,opd_vs_vital_sign.braden
                    FROM ".DbConstant::KPHIS_DBNAME.".opd_vs_vital_sign
                    WHERE opd_vs_vital_sign.an=:vn
                    GROUP BY opd_vs_vital_sign.vs_datetime ASC LIMIT 1";
        $stmt_vs = $conn->prepare($sql_vs);
        $stmt_vs->execute(['vn'=>$vn]);
        $row_vs  = $stmt_vs->fetch();

        //ipd_nurse_addmission_note เรื่อง "ประจำเดือน","อาชีพ","สารเสพติด"
        $sql_period =  "SELECT period, period_normal, period_disorders,period_lmp, period_menopause,
                        occupation,
                        no_risk,
                        smoking, smoke_year, smoke_frequency, smoke_stopped,
                        alcohol, alc_year, alc_frequency, alc_stopped,
                        medication_used, med_name, med_year, med_frequency, med_stopped
                        FROM ".DbConstant::KPHIS_DBNAME.".ipd_nurse_admission_note
                        WHERE an=:an";
        $stmt_period = $conn->prepare($sql_period);
        $stmt_period->execute(['an'=>$an]);
        $row_period  = $stmt_period->fetch();
?>
<script src="../include/fabric.js"></script>
<style type="text/css">
    #show_img_select  {
        border: 2px dotted black;
        /*แก้ไข*/
		background-image: url("../include/images/allbody.jpg");
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
        position: relative;
        width:700px;
        height:500px;
    }
    canvas {
        padding-left: 0;
        padding-right: 0;
        margin-left: auto;
        margin-right: auto;
        display: block;
    }
</style>
<form id="admit_firsth" action=""  method="post"  enctype="multipart/form-data">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-1">
              <!--  <button type="button" class="btn btn-primary btn-block" onclick="javascript:history.go(-1)"><i class="fas fa-arrow-left"></i> กลับ</button> -->
            </div>
            <div class="col-md-11">
                <h4>แบบบันทึกประวัติและตรวจร่างกายผู้ป่วยแรกรับ(ER Form) <?=htmlspecialchars(DbConstant::HOSPITAL_NAME)?></h4>
            </div>
        </div><p></p>
        <div class="card-group pb-3 ">
            <div class="card" >
Comming soon..............

<!-- card --></div>
<!-- card --></div>
<div class="form-group text-center">
    <div id="show_check_save"></div>
    <input type="hidden" id="an" name="an" value="<?=htmlspecialchars($an)?>">
    <input type="hidden" id="hn" name="hn" value="<?=htmlspecialchars($hn)?>">
    <input type="hidden" id="version" name="version" value="<?=htmlspecialchars($row['version'])?>">
    <input type="hidden" id="admission_note_id" name="admission_note_id" value="<?=htmlspecialchars($row['admission_note_id'])?>">
    <input type="hidden" id="create_user" name="create_user" value="<?=htmlspecialchars($_SESSION['name'])?>">
    <!-- <input type="hidden" id="nurse_name"  name="nurse_name"  value="<?=(htmlspecialchars($_SESSION['groupname'])=='พยาบาล' ? htmlspecialchars($_SESSION['name']) : '')?>">
    <input type="hidden" id="nurse_pos"   name="nurse_pos"   value="<?=(htmlspecialchars($_SESSION['groupname'])=='พยาบาล' ? htmlspecialchars($row_opduser['entryposition']) : '')?>">
    <input type="hidden" id="doc_name"    name="doc_name"    value="<?=(htmlspecialchars($_SESSION['groupname'])=='แพทย์' ? htmlspecialchars($_SESSION['name']) : '')?>">
    <input type="hidden" id="doc_pos"     name="doc_pos"     value="<?=(htmlspecialchars($_SESSION['groupname'])=='แพทย์' ? htmlspecialchars($row_opduser['entryposition']) : '')?>"> -->
</div>
</form>
<script>
    function AddDoctorSignature() {
        const doc_name = <?=json_encode($_SESSION['name'])?>;
        const doc_entryposition = <?=json_encode($_SESSION['entryposition'])?>;
        const doctorcode = <?=json_encode($_SESSION['doctorcode'])?>;
        const clone_template_dr_admission_input_div = document.querySelector('#template_dr_admission_input_div').content.cloneNode(true);
        if(CheckDoctorSignature()){
            $('#dr-admission-group-input-div').append(clone_template_dr_admission_input_div);
            $('[name="admission_note_doctor[]"].last-focus-input').removeClass('last-focus-input');
            $('[name="doc_name[]"].last-focus-input').removeClass('last-focus-input');
            // $('[name="doc_pos[]"].last-focus-input').removeClass('last-focus-input');
            $('[name="admission_note_doctor[]"]').last().addClass('last-focus-input').val(doctorcode);
            $('[name="doc_name[]"]').last().addClass('last-focus-input').val(doc_name);
            // $('[name="doc_pos[]"]').last().addClass('last-focus-input').val(doc_entryposition);
        }
    }
    function CheckDoctorSignature(){
        const doctorcode_check = <?=json_encode($_SESSION['doctorcode'])?>;
        let return_checkdoctorSignature = true;
        $.each($("input:hidden[name='admission_note_doctor[]']"), function(index,value){
            //console.log({index,value})
            if(doctorcode_check == $(this).val()){
                alert("คุณได้ลงชื่อบันทึกข้อมูลไว้แล้ว");
                return_checkdoctorSignature = false;
                return false;
            }
        });
        return return_checkdoctorSignature;
    }
    function PersonAsCurrentUser_1(){
        const nurse_name = <?=json_encode($_SESSION['name'])?>;
        const entryposition = <?=json_encode($_SESSION['entryposition'])?>;
        $("#nurse_name").val(nurse_name);
        $("#nurse_pos").val(entryposition);
    }
    //--------------------------------------------canvas----------------------------------------------
    var canvas = new fabric.Canvas('c');
    // var message = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent($('#svg_tag').val());
    var message = $('#svg_tag').val();
    fabric.loadSVGFromString(message,function(objects,options) {
        var loadedObjects = fabric.util.groupSVGElements(objects, options);
        loadedObjects.set('selectable', false);
        canvas.add(loadedObjects);
        canvas.renderAll();
    });

    canvas.isDrawingMode = true;
    canvas.on('object:added',function(){
        if(!isRedoing){
            h = [];
        }
        isRedoing = false;
    });
    var isRedoing = false;
    var h = [];
    function undo(){
        if(canvas._objects.length>0){
            h.push(canvas._objects.pop());
            canvas.renderAll();
        }
    }
    function redo(){
        if(h.length>0){
            isRedoing = true;
            canvas.add(h.pop());
        }
    }
    function onclick_clear(){
        canvas.clear();
    }
    //--------------------------------------------canvas----------------------------------------------

    function plan_management_check(value){
        if (value == "off_checked") {
            $('#plan_management_text').attr("disabled",true).val('');
            $('#plan_management4').prop("checked", false);
        }else if (value == "on_checked"){
            $('#plan_management_text').attr("disabled",false).val('');
            $('#plan_management1').prop("checked", false);
            $('#plan_management2').prop("checked", false);
            $('#plan_management3').prop("checked", false);

        }
      }

    function custom_check(value){
        if (value == "off_arrive") {
            $('#w6').attr("disabled",true).val('');
            $('#w5').prop("checked", false);
        }else if (value == "on_arrive"){
            $('#w6').attr("disabled",false).val('');
            $('#w1').prop("checked", false);
            $('#w2').prop("checked", false);
            $('#w3').prop("checked", false);
            $('#w4').prop("checked", false);
        }


        if (value == "off_entered") {
            $('#entered_hos').attr("disabled",true).val('');
            $('#entered_by3').prop("checked", false);
        }else if (value == "on_entered"){
            $('#entered_hos').attr("disabled",false).val('');
            $('#entered_by1').prop("checked", false);
            $('#entered_by2').prop("checked", false);
        }

        if (value == "on_informant1") {
            if (!($('#e2').is(':checked'))) {
                $('#e_informant1').attr("disabled",true).val('');
            }else{
                $('#e_informant1').attr("disabled",false).val('');
            }
        }else if (value == "on_informant2"){
            if (!($('#e4').is(':checked'))) {
                $('#e_informant2').attr("disabled",true).val('');
            }else{
                $('#e_informant2').attr("disabled",false).val('');
            }
        }

        if (value == "on_taken"){
            if (!($('#r4').is(':checked'))) {
                $('#r5').attr("disabled",true).val('');
            }else{
                $('#r5').attr("disabled",false).val('');
            }
        }

        if (value == "off_disease") {
            $('#tt1').attr("disabled",true).prop("checked", false);
            $('#t_text1').attr("disabled",true).val('');
            $('#tt2').attr("disabled",true).prop("checked", false);
            $('#t_text2').attr("disabled",true).val('');
            $('#tt3').attr("disabled",true).prop("checked", false);
            $('#t_text3').attr("disabled",true).val('');
        }else if (value == "on_disease"){
            $('#tt1').attr("disabled",false).val('');
            $('#t_text1').attr("disabled",false).val('');
            $('#tt2').attr("disabled",false).val('');
            $('#t_text2').attr("disabled",false).val('');
            $('#tt3').attr("disabled",false).val('');
            $('#t_text3').attr("disabled",false).val('');
        }

        if (value == "off_operation") {
            $('#y2_operation').attr("disabled",true).val('');
            $('#y2').prop("checked", false);
        }else if (value == "on_operation"){
            $('#y2_operation').attr("disabled",false).val('');
            $('#y1').prop("checked", false);
        }

        if (value == "off_allergy") {
            $('#uu1').attr("disabled",true).prop("checked", false);
            $('#uu1_in').attr("disabled",true).val('');
            $('#uu2').attr("disabled",true).prop("checked", false);
            $('#uu2_in').attr("disabled",true).val('');
            $('#uu3').attr("disabled",true).prop("checked", false);
            $('#uu3_in').attr("disabled",true).val('');
            $('#uu4_in').attr("disabled",true).val('');
        }else if (value == "on_allergy"){
            $('#uu1').attr("disabled",false).val('');
            $('#uu1_in').attr("disabled",false).val('');
            $('#uu2').attr("disabled",false).val('');
            $('#uu2_in').attr("disabled",false).val('');
            $('#uu3').attr("disabled",false).val('');
            $('#uu3_in').attr("disabled",false).val('');
            $('#uu4_in').attr("disabled",false).val('');
        }

        if (value == "off_immunisation") {
            $('#o3').attr("disabled",true).val('');
            $('#o2').prop("checked", false);
        }else if (value == "on_immunisation"){
            $('#o3').attr("disabled",false).val('');
            $('#o1').prop("checked", false);
        }

        if (value == "off_developmentally") {
            $('#p3').attr("disabled",true).val('');
            $('#p2').prop("checked", false);
        }else if (value == "on_developmentally"){
            $('#p3').attr("disabled",false).val('');
            $('#p1').prop("checked", false);
        }

        if (value == "off_deliver") {
            $('#s3').attr("disabled",true).val('');
            $('#s4').attr("disabled",true).val('');
            $('#s2').prop("checked", false);
        }else if (value == "on_deliver"){
            $('#s3').attr("disabled",false).val('');
            $('#s4').attr("disabled",false).val('');
            $('#s1').prop("checked", false);
        }

        if (value == "off_condition") {
            $('#a3').attr("disabled",true).val('');
            $('#a2').prop("checked", false);
        }else if (value == "on_condition"){
            $('#a3').attr("disabled",false).val('');
            $('#a1').prop("checked", false);
        }

        if (value == "off_family_medical") {
            $('#i3').attr("disabled",true).val('');
            $('#i2').prop("checked", false);
        }else if (value == "on_family_medical"){
            $('#i3').attr("disabled",false).val('');
            $('#i1').prop("checked", false);
        }

        if (value == "off_disease_operation_allergy") {
            $('#g2_text').attr("disabled",true).val('');
            $('#g2').prop("checked", false);
        }else if (value == "on_disease_operation_allergy"){
            $('#g2_text').attr("disabled",false).val('');
            $('#g1').prop("checked", false);
        }

           $('#regdate').attr("disabled",false).val('');
        if (value == "off_inpatient") {
            $('#h3').attr("disabled",true).val('');
            $('#h4').attr("disabled",true).val('');
            $('#h5').attr("disabled",true).val('');
            $('#h2').prop("checked", false);
        }else if (value == "on_inpatient"){
            $('#h3').attr("disabled",false).val('');
            $('#h4').attr("disabled",false).val('');
            $('#h5').attr("disabled",false).val('');
            $('#h1').prop("checked", false);
        }
    }

    function admission_save(){
        var allergy_drug_history_all = "";
        var total_drug = $('#total_chq_drug').val();
        for (i = 1; i <= total_drug; i++) {
            if(typeof $('#allergy_drug_pri'+ i).val() === 'undefined'){
                allergy_drug_history_all += "";
            }else{allergy_drug_history_all += ($('#allergy_drug_pri'+ i).val())+' '+($('#allergy_drug_sec'+ i).val())+' ';}
        }
        if( allergy_drug_history_all != "  " ){ $('#allergy_drug_history').val(allergy_drug_history_all);}

        var allergy_food_history_all = "";
        var total_chq_food = $('#total_chq_food').val();
        for (i = 1; i <= total_chq_food; i++) {
            if(typeof $('#allergy_food_pri'+ i).val() === 'undefined'){
                allergy_food_history_all += "";
            }else{allergy_food_history_all += ($('#allergy_food_pri'+ i).val())+' '+($('#allergy_food_sec'+ i).val())+' ';}
        }
        if( allergy_food_history_all != "  " ){ $('#allergy_food_history').val(allergy_food_history_all);}

        var allergy_etc_history_all = "";
        var total_chq_etc = $('#total_chq_etc').val();
        for (i = 1; i <= total_chq_etc; i++) {
            if(typeof $('#allergy_etc_pri'+ i).val() === 'undefined'){
                allergy_etc_history_all += "";
            }else{allergy_etc_history_all += ($('#allergy_etc_pri'+ i).val())+' '+($('#allergy_etc_sec'+ i).val())+' ';}
        }
        if( allergy_etc_history_all != "  " ){ $('#allergy_etc_history').val(allergy_etc_history_all);}

        var disease_all = "";
        var total = $('#total_chq').val();
        for (i = 1; i <= total; i++) {
            if(typeof $('#disease_name'+ i).val() === 'undefined'){
                disease_all += "";
            }else{disease_all += ($('#disease_name'+ i).val())+' '+($('#disease_year'+ i).val())+' '+($('#disease_hospital'+ i).val())+' ';}
        }
        if( disease_all != "   " ){ $('#disease_detail').val(disease_all);}

        var family_medical_history_all = "";
        var total_chq_family_medical = $('#total_chq_family_medical').val();
        for (i = 1; i <= total_chq_family_medical; i++) {

            if(typeof $('#family_medical_pri'+ i).val() === 'undefined'){
                family_medical_history_all += "";
            }else{family_medical_history_all += ($('#family_medical_pri'+ i).val())+' '+($('#family_medical_sec'+ i).val())+' ';}
        }
        if( family_medical_history_all != "  " ){ $('#family_medical_history_detail').val(family_medical_history_all);}

        var trsvg = canvas.toSVG();
        $('#svg_tag').html(trsvg);

        const age_y = <?=json_encode($row_ipt['age_y'])?>;
        const sex = <?=json_encode($row_ipt['sex'])?>;
        var receives_immunisation_history_kid = $('input[type=radio][name=receives_immunisation_history_kid]:checked').val();
        var receives_immunisation_history_kid_text = $.trim($('[name="receives_immunisation_history_kid_text"]').val());
        var developmentally_kid = $('input[type=radio][name=developmentally_kid]:checked').val();
        var developmentally_kid_text = $.trim($('[name="developmentally_kid_text"]').val());
        var deliver_anomalies = $('input[type=radio][name=deliver_anomalies]:checked').val();
        var deliver_anomalies_text = $.trim($('[name="deliver_anomalies_text"]').val());
        var condition_pregnant = $('input[type=radio][name=condition_pregnant]:checked').val();
        var condition_pregnant_text = $.trim($('textarea[name=condition_pregnant_text]').val());
        if(age_y < 15 && receives_immunisation_history_kid == "ไม่ครบตามวัย" && receives_immunisation_history_kid_text == ""){
            alert("กรุณากรอกข้อมูล ประวัติการได้รับภูมิคุ้มกัน (เฉพาะเด็ก) ให้ครบถ้วน");
            $('[name="receives_immunisation_history_kid_text"]').focus();
        }else if(age_y < 15 && developmentally_kid == "ผิดปกติ" && developmentally_kid_text == ""){
            alert("กรุณากรอกข้อมูล การพัฒนาการ (เฉพาะเด็ก) ให้ครบถ้วน");
            $('[name="developmentally_kid_text"]').focus();
        }else if(age_y < 15 && deliver_anomalies == "ผิดปกติ" && deliver_anomalies_text == ""){
            alert("กรุณากรอกข้อมูล วิธีคลอด ให้ครบถ้วน");
            $('[name="deliver_anomalies_text"]').focus();
        }else if((age_y > 9) && (sex == '2') && (condition_pregnant == "ผิดปกติ") && (condition_pregnant_text  == "")){
            alert("กรุณากรอกข้อมูล อาการระหว่างตั้งครรภ์ ให้ครบถ้วน");
            $('[name="condition_pregnant_text"]').focus();
        }else{
            var url_update = "ipd-dr-admission-note-update.php";
            var url_save   = "ipd-dr-admission-note-save.php";
            var admission_note_id = $("#admission_note_id").val();
            var admit_firsth = $("#admit_firsth").serialize();

            if(admission_note_id == ""){
                $.post(url_save,admit_firsth,function(data){
                    $("#show_check_save").html(data);
                    alert("บันทึกข้อมูลสำเร็จ");
                    self.close();
                })
                .fail(function(){
                    alert("บันทึกข้อมูลไม่สำเร็จ"+error);
                });
            }else{
                $.post(url_update,admit_firsth,function(data){
                    $("#show_check_save").html(data);
                    alert("บันทึกข้อมูลสำเร็จ");
                    self.close();
                })
                .fail(function(){
                    alert("บันทึกข้อมูลไม่สำเร็จ"+error);
                });
            }
        }
    }
    function onclick_Normal(id,value) {
        $('#'+id).val(value);
    }

    function onclick_Normal2(id,value) {
        $('#'+id).val(value);
    }


</script>
