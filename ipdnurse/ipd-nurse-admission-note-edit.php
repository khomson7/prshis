<?php
        require_once '../include/Session.php';
        Session::checkLoginSessionAndShowMessage(); //เช็ค session
        // SessionManager::checkPermissionAndShowMessage('KPHIS_ACCESS_IPD_NURSE_ADMISSION_NOTE');
        if(!(Session::checkPermission('IPD_NURSE_ADDMISSION_NOTE','EDIT'))){
            return;
        }
        require_once '../include/DbUtils.php';
        require_once '../include/KphisQueryUtils.php';

        $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล

        $an = $_REQUEST['an'];
        $hn = KphisQueryUtils::getHnByAn($an);   // function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา
        $nurse_admission_note_id = $_REQUEST['nurse_admission_note_id'];
        $query_parameters = [
                                ':nurse_admission_note_id' => $nurse_admission_note_id,
                                ':an' => $an
                            ];
        $sql = "SELECT * FROM ".DbConstant::KPHIS_DBNAME.".ipd_nurse_admission_note WHERE an = :an AND nurse_admission_note_id = :nurse_admission_note_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute($query_parameters);
        $rowCount = 0;
        $row = $stmt->fetch();
        $concious       =  $row['concious'];
        $normal_breath  =  $row['normal_breath'];
        $kussmaul       =  $row['kussmaul'];
        $tachypnea      =  $row['tachypnea'];
        $dyspnea        =  $row['dyspnea'];
        $apnea          =  $row['apnea'];
        $tube           =  $row['tube'];

        $normal_hr         =  $row['normal_hr'];
        $arregular         =  $row['arregular'];
        $weakness          =  $row['weakness'];
        $arrhythmia        =  $row['arrhythmia'];
        $chestpain         =  $row['chestpain'];
        $pacemaker         =  $row['pacemaker'];
        $cardio_other      =  $row['cardio_other'];
        $cardio_other_text =  $row['cardio_other_text'];

        $normal_cir               =  $row['normal_cir'];
        $pale                     =  $row['pale'];
        $cyanosis                 =  $row['cyanosis'];
        $generalized_edema        =  $row['generalized_edema'];
        $localized_edema          =  $row['localized_edema'];
        $localized_edema_text     =  $row['localized_edema_text'];
        $pitting_edema            =  $row['pitting_edema'];
        $pitting_edema_text       =  $row['pitting_edema_text'];
        $circulation_orther       =  $row['circulation_orther'];
        $circulation_orther_text  =  $row['circulation_orther_text'];
        $no_mental_state          =  $row['no_mental_state'];
        $no_mental_state_text     =  $row['no_mental_state_text'];

        $normal_skin     =  $row['normal_skin'];
        $dry             =  $row['dry'];
        $bruise          =  $row['bruise'];
        $erythema        =  $row['erythema'];
        $abscess         =  $row['abscess'];
        $joudice         =  $row['joudice'];
        $skin_other      =  $row['skin_other'];
        $skin_other_text =  $row['skin_other_text'];

        $pain               =  $row['pain'];
        $location           =  $row['location'];
        $pain_charac        =  $row['pain_charac'];
        $pain_charac_text   =  $row['pain_charac_text'];
        $pain_score         =  $row['pain_score'];

        $normal_behav           =  $row['normal_behav'];
        $agitate                =  $row['agitate'];
        $aggressive             =  $row['aggressive'];
        $depression             =  $row['depression'];
        $madness                =  $row['madness'];
        $behaviour_other        =  $row['behaviour_other'];
        $behaviour_other_text   =  $row['behaviour_other_text'];

        $normal_emotional       =  $row['normal_emotional'];
        $angry                  =  $row['angry'];
        $moody                  =  $row['moody'];
        $anxiety                =  $row['anxiety'];
        $fear                   =  $row['fear'];
        $emotional_other        =  $row['emotional_other'];
        $emotional_other_text   =  $row['emotional_other_text'];

        $no_anxiety =  $row['no_anxiety'];
        $study      =  $row['study'];
        $family     =  $row['family'];
        $economy    =  $row['economy'];
        $habitation =  $row['habitation'];
        $illness    =  $row['illness'];

        $spiritual_no               =  $row['spiritual_no'];
        $spiritual_back_home        =  $row['spiritual_back_home'];
        $spiritual_need_family      =  $row['spiritual_need_family'];
        $spiritual_other            =  $row['spiritual_other'];
        $spiritual_other_text       =  $row['spiritual_other_text'];
        $spiritual_cant_rated       =  $row['spiritual_cant_rated'];
        $spiritual_cant_rated_text  =  $row['spiritual_cant_rated_text'];

        $education          =  $row['education'];
        $education_result   =  $row['education_result'];

        $occupation             =  $row['occupation'];
        $income                 =  $row['income'];
        $self                   =  $row['self'];
        $person_family          =  $row['person_family'];
        $neighbor               =  $row['neighbor'];
        $assistant_other        =  $row['assistant_other'];
        $assistant_other_text   =  $row['assistant_other_text'];
        $assistant_occupation   =  $row['assistant_occupation'];

        $clinic         =  $row['clinic'];
        $buy_medicine   =  $row['buy_medicine'];

        $no_risk            =  $row['no_risk'];
        $smoking            =  $row['smoking'];
        $smoke_year         =  $row['smoke_year'];
        $smoke_frequency    =  $row['smoke_frequency'];
        $smoke_stopped      =  $row['smoke_stopped'];

        $alcohol        =  $row['alcohol'];
        $alc_year       =  $row['alc_year'];
        $alc_frequency  =  $row['alc_frequency'];
        $alc_stopped    =  $row['alc_stopped'];

        $medication_used =  $row['medication_used'];
        $med_name        =  $row['med_name'];
        $med_year        =  $row['med_year'];
        $med_frequency   =  $row['med_frequency'];
        $med_stopped     =  $row['med_stopped'];

        $diet_regular           =  $row['diet_regular'];
        $diet_spec              =  $row['diet_spec'];
        $nutrition_risk         =  $row['nutrition_risk'];
        $loss_appetite          =  $row['loss_appetite'];
        $dysphagia              =  $row['dysphagia'];
        $loss_gustation         =  $row['loss_gustation'];
        $denture                =  $row['denture'];
        $nutrition_risk_other   =  $row['nutrition_risk_other'];
        $nutrition_risk_other_text   =  $row['nutrition_risk_other_text'];

        $normal_urine       =  $row['normal_urine'];
        $dysuria            =  $row['dysuria'];
        $incontinence       =  $row['incontinence'];
        $staining           =  $row['staining'];
        $hematuria          =  $row['hematuria'];
        $catheter           =  $row['catheter'];
        $normal_feces       =  $row['normal_feces'];
        $constipation       =  $row['constipation'];
        $diarrhea           =  $row['diarrhea'];
        $bowel_incontinence =  $row['bowel_incontinence'];
        $hemorrhoid         =  $row['hemorrhoid'];
        $colostomy          =  $row['colostomy'];
        $activity1          =  $row['activity1'];
        $activity2          =  $row['activity2'];
        $activity3          =  $row['activity3'];
        $activity4          =  $row['activity4'];
        $o_p_use            =  $row['o_p_use'];

        $sleep_per_day          =  $row['sleep_per_day'];
        $sleep_hour             =  $row['sleep_hour'];
        $sleep_problems         =  $row['sleep_problems'];
        $sleep_problems_detail  =  $row['sleep_problems_detail'];
        $sleep_med_name         =  $row['sleep_med_name'];
        $sleep_med_name_detail  =  $row['sleep_med_name_detail'];

        $cognitive          =  $row['cognitive'];
        $memory             =  $row['memory'];
        $memory_detail      =  $row['memory_detail'];
        $hearing            =  $row['hearing'];
        $hearing_detail     =  $row['hearing_detail'];
        $eartone            =  $row['eartone'];

        $vision             =  $row['vision'];
        $vision_detail      =  $row['vision_detail'];
        $vision_eyeglasses  =  $row['vision_eyeglasses'];
        $vision_contactlens =  $row['vision_contactlens'];
        $speech             =  $row['speech'];
        $speech_detail      =  $row['speech_detail'];

        $self_image             =  $row['self_image'];
        $self_image_detail      =  $row['self_image_detail'];
        $self_activity          =  $row['self_activity'];
        $self_activity_detail   =  $row['self_activity_detail'];

        $sickness_effect        =  $row['sickness_effect'];
        $sickness_family        =  $row['sickness_family'];
        $sickness_occupation    =  $row['sickness_occupation'];
        $sickness_education     =  $row['sickness_education'];
        $sickness_other         =  $row['sickness_other'];
        $sickness_other_text    =  $row['sickness_other_text'];

        $period             =  $row['period'];
        $period_normal      =  $row['period_normal'];
        $period_disorders   =  $row['period_disorders'];
        $period_lmp         =  $row['period_lmp'];
        $period_menopause   =  $row['period_menopause'];

        $breast                     =  $row['breast'];
        $breast_disorders           =  $row['breast_disorders'];
        $consult                    =  $row['consult'];
        $seclude                    =  $row['seclude'];
        $medication                 =  $row['medication'];
        $medication_detail          =  $row['medication_detail'];
        $religion                   =  $row['religion'];
        $coping_stress_other        =  $row['coping_stress_other'];
        $coping_stress_other_detail =  $row['coping_stress_other_detail'];

        $belief_sickness_behave     =  $row['belief_sickness_behave'];
        $belief_sickness_age        =  $row['belief_sickness_age'];
        $belief_sickness_destiny    =  $row['belief_sickness_destiny'];
        $belief_sickness_other      =  $row['belief_sickness_other'];
        $belief_sickness_other_text =  $row['belief_sickness_other_text'];
        $belief_believe             =  $row['belief_believe'];
        $belief_believe_text        =  $row['belief_believe_text'];

        $religious_activity         =  $row['religious_activity'];
        $religious_activity_text    =  $row['religious_activity_text'];

        $version                    =  $row['version'];
        $nurse_admission_note_id    =  $row['nurse_admission_note_id'];?>
<script>
    $("#nurse_admission_note").each(function() {
        $("input[name=version]").val(<?=json_encode($version)?>);

        //ความรู้สึกตัว
        var concious = <?=json_encode($concious)?>;
        if(concious == "รู้สึกตัวดี"){
            $("#concious1").attr('checked',true);
        }else if(concious == "สับสน"){
            $("#concious2").attr('checked',true);
        }else if(concious == "ง่วงซึม"){
            $("#concious3").attr('checked',true);
        }else if(concious == "ไม่รู้สึกตัว"){
            $("#concious4").attr('checked',true);
        }

        //ลักษณะการหายใจ
        var normal_breath = <?=json_encode($normal_breath)?>;
        if(normal_breath == "Y"){
            $("#normal_breath").attr('checked',true);
        }else{
            $("#normal_breath").attr('checked',false);
        }
        var kussmaul = <?=json_encode($kussmaul)?>;
        if(kussmaul == "Y"){
            $("#kussmaul").attr('checked',true);
        }else{
            $("#kussmaul").removeAttr('checked');
        }
        var tachypnea = <?=json_encode($tachypnea)?>;
        if(tachypnea == "Y"){
            $("#tachypnea").attr('checked',true);
        }else{
            $("#tachypnea").removeAttr('checked');
        }
        var dyspnea = <?=json_encode($dyspnea)?>;
        if(dyspnea == "Y"){
            $("#dyspnea").attr('checked',true);
        }else{
            $("#dyspnea").removeAttr('checked');
        }
        var apnea = <?=json_encode($apnea)?>;
        if(apnea == "Y"){
            $("#apnea").attr('checked',true);
        }else{
            $("#apnea").removeAttr('checked');
        }
        var tube = <?=json_encode($tube)?>;
        if(tube == "Y"){
            $("#tube").attr('checked',true);
        }else{
            $("#tube").removeAttr('checked');
        }

        //ระบบหัวใจ
        var normal_hr = <?=json_encode($normal_hr)?>;
        if(normal_hr == "Y"){
            $("#normal_hr").attr('checked',true);
        }else{
            $("#normal_hr").removeAttr('checked');
        }
        var arregular = <?=json_encode($arregular)?>;
        if(arregular == "Y"){
            $("#arregular").attr('checked',true);
        }else{
            $("#arregular").removeAttr('checked');
        }
        var weakness = <?=json_encode($weakness)?>;
        if(weakness == "Y"){
            $("#weakness").attr('checked',true);
        }else{
            $("#weakness").removeAttr('checked');
        }
        var arrhythmia = <?=json_encode($arrhythmia)?>;
        if(arrhythmia == "Y"){
            $("#arrhythmia").attr('checked',true);
        }else{
            $("#arrhythmia").removeAttr('checked');
        }
        var chestpain = <?=json_encode($chestpain)?>;
        if(chestpain == "Y"){
            $("#chestpain").attr('checked',true);
        }else{
            $("#chestpain").removeAttr('checked');
        }
        var pacemaker = <?=json_encode($pacemaker)?>;
        if(pacemaker == "Y"){
            $("#pacemaker").attr('checked',true);
        }else{
            $("#pacemaker").removeAttr('checked');
        }
        var cardio_other = <?=json_encode($cardio_other)?>;
        if(cardio_other == "Y"){
            $("#cardio_other").attr('checked',true);
        }else{
            $("#cardio_other").removeAttr('checked');
        }
        $("input[name=cardio_other_text]").val(<?=json_encode($cardio_other_text)?>);

        //การไหลเวียนโลหิต
        var normal_cir = <?=json_encode($normal_cir)?>;
        if(normal_cir == "Y"){
            $("#normal_cir").attr('checked',true);
        }else{
            $("#normal_cir").removeAttr('checked');
        }
        var pale = <?=json_encode($pale)?>;
        if(pale == "Y"){
            $("#pale").attr('checked',true);
        }else{
            $("#pale").removeAttr('checked');
        }
        var cyanosis = <?=json_encode($cyanosis)?>;
        if(cyanosis == "Y"){
            $("#cyanosis").attr('checked',true);
        }else{
            $("#cyanosis").removeAttr('checked');
        }
        var generalized_edema = <?=json_encode($generalized_edema)?>;
        if(generalized_edema == "Y"){
            $("#generalized_edema").attr('checked',true);
        }else{
            $("#generalized_edema").removeAttr('checked');
        }
        var localized_edema = <?=json_encode($localized_edema)?>;
        if(localized_edema == "Y"){
            $("#localized_edema").attr('checked',true);
        }else{
            $("#localized_edema").removeAttr('checked');
        }
        $("input[name=localized_edema_text]").val(<?=json_encode($localized_edema_text)?>);
        var pitting_edema = <?=json_encode($pitting_edema)?>;
        if(pitting_edema == "Y"){
            $("#pitting_edema").attr('checked',true);
        }else{
            $("#pitting_edema").removeAttr('checked');
        }
        $("input[name=pitting_edema_text]").val(<?=json_encode($pitting_edema_text)?>);
        var circulation_orther = <?=json_encode($circulation_orther)?>;
        if(circulation_orther == "Y"){
            $("#circulation_orther").attr('checked',true);
        }else{
            $("#circulation_orther").removeAttr('checked');
        }
        $("input[name=circulation_orther_text]").val(<?=json_encode($circulation_orther_text)?>);
        
        //สภาพผิวหนัง
        var normal_skin = <?=json_encode($normal_skin)?>;
        if(normal_skin == "Y"){
            $("#normal_skin").attr('checked',true);
        }else{
            $("#normal_skin").removeAttr('checked');
        }
        var dry = <?=json_encode($dry)?>;
        if(dry == "Y"){
            $("#dry").attr('checked',true);
        }else{
            $("#dry").removeAttr('checked');
        }
        var bruise = <?=json_encode($bruise)?>;
        if(bruise == "Y"){
            $("#bruise").attr('checked',true);
        }else{
            $("#bruise").removeAttr('checked');
        }
        var erythema = <?=json_encode($erythema)?>;
        if(erythema == "Y"){
            $("#erythema").attr('checked',true);
        }else{
            $("#erythema").removeAttr('checked');
        }
        var abscess = <?=json_encode($abscess)?>;
        if(abscess == "Y"){
            $("#abscess").attr('checked',true);
        }else{
            $("#abscess").removeAttr('checked');
        }
        var joudice = <?=json_encode($joudice)?>;
        if(joudice == "Y"){
            $("#joudice").attr('checked',true);
        }else{
            $("#joudice").removeAttr('checked');
        }
        var skin_other = <?=json_encode($skin_other)?>;
        if(skin_other == "Y"){
            $("#skin_other").attr('checked',true);
        }else{
            $("#skin_other").removeAttr('checked');
        }
        $("input[name=skin_other_text]").val(<?=json_encode($skin_other_text)?>);

        //ความเจ็บปวด
        var pain = <?=json_encode($pain)?>;
        if(pain == "ไม่มี"){
            $("#pain_n").attr('checked',true);
        }else if(pain == "มี"){
            $("#pain_y").attr('checked',true);
        }
        $("input[name=location]").val(<?=json_encode($location)?>);

        var pain_charac = <?=json_encode($pain_charac)?>;
        if(pain_charac == "ครั้งคราว"){
            $("#pain_charac_s").attr('checked',true);
        }else if(pain_charac == "ตลอดเวลา"){
            $("#pain_charac_a").attr('checked',true);
        }else if(pain_charac == "อื่นๆ"){
            $("#pain_charac_o").attr('checked',true);
        }
        $("input[name=pain_charac_text]").val(<?=json_encode($pain_charac_text)?>);
        $("input[name=pain_score]").val(<?=json_encode($pain_score)?>);

        //ด้านพฤติกรรม
        var normal_behav = <?=json_encode($normal_behav)?>;
        if(normal_behav == "Y"){
            $("#normal_behav").attr('checked',true);
        }else{
            $("#normal_behav").removeAttr('checked');
        }
        var agitate = <?=json_encode($agitate)?>;
        if(agitate == "Y"){
            $("#agitate").attr('checked',true);
        }else{
            $("#agitate").removeAttr('checked');
        }
        var aggressive = <?=json_encode($aggressive)?>;
        if(aggressive == "Y"){
            $("#aggressive").attr('checked',true);
        }else{
            $("#aggressive").removeAttr('checked');
        }
        var depression = <?=json_encode($depression)?>;
        if(depression == "Y"){
            $("#depression").attr('checked',true);
        }else{
            $("#depression").removeAttr('checked');
        }
        var madness = <?=json_encode($madness)?>;
        if(madness == "Y"){
            $("#madness").attr('checked',true);
        }else{
            $("#madness").removeAttr('checked');
        }
        var behaviour_other = <?=json_encode($behaviour_other)?>;
        if(behaviour_other == "Y"){
            $("#behaviour_other").attr('checked',true);
        }else{
            $("#behaviour_other").removeAttr('checked');
        }
        $("input[name=behaviour_other_text]").val(<?=json_encode($behaviour_other_text)?>);

        //ด้านอารมณ์
        var normal_emotional = <?=json_encode($normal_emotional)?>;
        if(normal_emotional == "Y"){
            $("#normal_emotional").attr('checked',true);
        }else{
            $("#normal_emotional").removeAttr('checked');
        }
        var angry = <?=json_encode($angry)?>;
        if(angry == "Y"){
            $("#angry").attr('checked',true);
        }else{
            $("#angry").removeAttr('checked');
        }
        var moody = <?=json_encode($moody)?>;
        if(moody == "Y"){
            $("#moody").attr('checked',true);
        }else{
            $("#moody").removeAttr('checked');
        }
        var anxiety = <?=json_encode($anxiety)?>;
        if(anxiety == "Y"){
            $("#anxiety").attr('checked',true);
        }else{
            $("#anxiety").removeAttr('checked');
        }
        var fear = <?=json_encode($fear)?>;
        if(fear == "Y"){
            $("#fear").attr('checked',true);
        }else{
            $("#fear").removeAttr('checked');
        }
        var emotional_other = <?=json_encode($emotional_other)?>;
        if(emotional_other == "Y"){
            $("#emotional_other").attr('checked',true);
        }else{
            $("#emotional_other").removeAttr('checked');
        }
        $("input[name=emotional_other_text]").val(<?=json_encode($emotional_other_text)?>);

        //ความกังวลใจ
        var no_anxiety = <?=json_encode($no_anxiety)?>;
        if(no_anxiety == "Y"){
            $("#no_anxiety").attr('checked',true);
        }else{
            $("#no_anxiety").removeAttr('checked');
        }
        var study = <?=json_encode($study)?>;
        if(study == "Y"){
            $("#study").attr('checked',true);
        }else{
            $("#study").removeAttr('checked');
        }
        var family = <?=json_encode($family)?>;
        if(family == "Y"){
            $("#family").attr('checked',true);
        }else{
            $("#family").removeAttr('checked');
        }
        var economy = <?=json_encode($economy)?>;
        if(economy == "Y"){
            $("#economy").attr('checked',true);
        }else{
            $("#economy").removeAttr('checked');
        }
        var habitation = <?=json_encode($habitation)?>;
        if(habitation == "Y"){
            $("#habitation").attr('checked',true);
        }else{
            $("#habitation").removeAttr('checked');
        }
        var illness = <?=json_encode($illness)?>;
        if(illness == "Y"){
            $("#illness").attr('checked',true);
        }else{
            $("#illness").removeAttr('checked');
        }

        //ความต้องการด้านจิตวิญญาณ
        var spiritual_no = <?=json_encode($spiritual_no)?>;
        if(spiritual_no == "Y"){
            $("#spiritual_no").attr('checked',true);
        }else{
            $("#spiritual_no").removeAttr('checked');
        }
        var spiritual_back_home = <?=json_encode($spiritual_back_home)?>;
        if(spiritual_back_home == "Y"){
            $("#spiritual_back_home").attr('checked',true);
        }else{
            $("#spiritual_back_home").removeAttr('checked');
        }
        var spiritual_need_family = <?=json_encode($spiritual_need_family)?>;
        if(spiritual_need_family == "Y"){
            $("#spiritual_need_family").attr('checked',true);
        }else{
            $("#spiritual_need_family").removeAttr('checked');
        }
        var spiritual_other = <?=json_encode($spiritual_other)?>;
        if(spiritual_other == "Y"){
            $("#spiritual_other").attr('checked',true);
        }else{
            $("#spiritual_other").removeAttr('checked');
        }
        $("input[name=spiritual_other_text]").val(<?=json_encode($spiritual_other_text)?>);
        var spiritual_cant_rated = <?=json_encode($spiritual_cant_rated)?>;
        if(spiritual_cant_rated == "Y"){
            $("#spiritual_cant_rated").attr('checked',true);
        }else{
            $("#spiritual_cant_rated").removeAttr('checked');
        }
        $("input[name=spiritual_cant_rated_text]").val(<?=json_encode($spiritual_cant_rated_text)?>);

        var no_mental_state = <?=json_encode($no_mental_state)?>; //ประเมินสภาพจิตใจไม่ได้เนื่องจาก
        if(no_mental_state == "Y"){
            $("#no_mental_state").attr('checked',true);
            $('#behaviour_other_text').val('').attr("disabled", true);
            $('#emotional_other_text').val('').attr("disabled", true);
            $('#spiritual_other_text').val('').attr("disabled", true);
            $('#spiritual_cant_rated_text').val('').attr("disabled", true);
            $("textarea#no_mental_state_text").val(<?=json_encode($no_mental_state_text)?>);
            $('.onchange_no_mental_state_disabled').attr("disabled", true);
            $('#no_mental_state_text').removeAttr("disabled"); //ประเมินสภาพจิตใจไม่ได้เนื่องจาก
        }else{
            $("#no_mental_state").removeAttr('checked');
            $('#behaviour_other_text').removeAttr("disabled");
            $('#emotional_other_text').removeAttr("disabled");
            $('#spiritual_other_text').removeAttr("disabled");
            $('.onchange_no_mental_state_disabled').removeAttr("disabled");
            $('#no_mental_state_text').val('').attr("disabled", true);//ประเมินสภาพจิตใจไม่ได้เนื่องจาก
        }

        //การศึกษา
        var education = <?=json_encode($education)?>;
        if(education == "ไม่ได้รับ"){
            $("#education_n").attr('checked',true);
        }else if(education == "ได้รับ"){
            $("#education_y").attr('checked',true);
        }
        $("input[name=education_result]").val(<?=json_encode($education_result)?>);

        //อาชีพ(ระบุ)
        $("input[name=occupation]").val(<?=json_encode($occupation)?>);

        //รายได้
        var income = <?=json_encode($income)?>;
        if(income == "เพียงพอ"){
            $("#income_y").attr('checked',true);
        }else if(income == "ไม่เพียงพอ"){
            $("#income_n").attr('checked',true);
        }

        //ผู้ให้ความช่วยเหลือดูแล
        var self = <?=json_encode($self)?>;
        if(self == "Y"){
            $("#self").attr('checked',true);
        }else{
            $("#self").removeAttr('checked');
        }
        var person_family = <?=json_encode($person_family)?>;
        if(person_family == "Y"){
            $("#person_family").attr('checked',true);
        }else{
            $("#person_family").removeAttr('checked');
        }
        var neighbor = <?=json_encode($neighbor)?>;
        if(neighbor == "Y"){
            $("#neighbor").attr('checked',true);
        }else{
            $("#neighbor").removeAttr('checked');
        }
        var assistant_other = <?=json_encode($assistant_other)?>;
        if(assistant_other == "Y"){
            $("#assistant_other").attr('checked',true);
        }else{
            $("#assistant_other").removeAttr('checked');
        }
        $("input[name=assistant_other_text]").val(<?=json_encode($assistant_other_text)?>);

        //อาชีพผู้ดูแล(ระบุ)
        $("input[name=assistant_occupation]").val(<?=json_encode($assistant_occupation)?>);

        //การดูแลตนเอง
        var clinic = <?=json_encode($clinic)?>;
        if(clinic == "Y"){
            $("#clinic").attr('checked',true);
        }else{
            $("#clinic").removeAttr('checked');
        }
        var buy_medicine = <?=json_encode($buy_medicine)?>;
        if(buy_medicine == "Y"){
            $("#buy_medicine").attr('checked',true);
        }else{
            $("#buy_medicine").removeAttr('checked');
        }

        //พฤติกรรมเสี่ยง
        var no_risk = <?=json_encode($no_risk)?>;
        if(no_risk == "Y"){
            $("#no_risk").attr('checked',true);
        }else{
            $("#no_risk").removeAttr('checked');
        }
        var smoking = <?=json_encode($smoking)?>;
        if(smoking == "Y"){
            $("#smoking").attr('checked',true);
        }else{
            $("#smoking").removeAttr('checked');
        }
        $("input[name=smoke_year]").val(<?=json_encode($smoke_year)?>);
        $("input[name=smoke_frequency]").val(<?=json_encode($smoke_frequency)?>);
        $("input[name=smoke_stopped]").val(<?=json_encode($smoke_stopped)?>);
        var alcohol = <?=json_encode($alcohol)?>;
        if(alcohol == "Y"){
            $("#alcohol").attr('checked',true);
        }else{
            $("#alcohol").removeAttr('checked');
        }
        $("input[name=alc_year]").val(<?=json_encode($alc_year)?>);
        $("input[name=alc_frequency]").val(<?=json_encode($alc_frequency)?>);
        $("input[name=alc_stopped]").val(<?=json_encode($alc_stopped)?>);
        var medication_used = <?=json_encode($medication_used)?>;
        if(medication_used == "Y"){
            $("#medication_used").attr('checked',true);
        }else{
            $("#medication_used").removeAttr('checked');
        }
        $("textarea#med_name").val(<?=json_encode($med_name)?>);
        $("input[name=med_year]").val(<?=json_encode($med_year)?>);
        $("input[name=med_frequency]").val(<?=json_encode($med_frequency)?>);
        $("input[name=med_stopped]").val(<?=json_encode($med_stopped)?>);

        //อาหาร และการเผาผลาญอาหาร
        var diet_regular = <?=json_encode($diet_regular)?>;
        if(diet_regular == "อาหารทั่วไป"){
            $("#diet_regu").attr('checked',true);
        }else if(diet_regular == "อาหารเฉพาะโรค"){
            $("#diet_sp").attr('checked',true);
        }
        $("input[name=diet_spec]").val(<?=json_encode($diet_spec)?>);

        //ปัญหาการรับประทานอาหาร
        var nutrition_risk = <?=json_encode($nutrition_risk)?>;
        if(nutrition_risk == "Y"){
            $("#nutrition_risk").attr('checked',true);
        }else{
            $("#nutrition_risk").removeAttr('checked');
        }
        var loss_appetite = <?=json_encode($loss_appetite)?>;
        if(loss_appetite == "Y"){
            $("#loss_appetite").attr('checked',true);
        }else{
            $("#loss_appetite").removeAttr('checked');
        }
        var dysphagia = <?=json_encode($dysphagia)?>;
        if(dysphagia == "Y"){
            $("#dysphagia").attr('checked',true);
        }else{
            $("#dysphagia").removeAttr('checked');
        }
        var loss_gustation = <?=json_encode($loss_gustation)?>;
        if(loss_gustation == "Y"){
            $("#loss_gustation").attr('checked',true);
        }else{
            $("#loss_gustation").removeAttr('checked');
        }
        var denture = <?=json_encode($denture)?>;
        if(denture == "Y"){
            $("#denture").attr('checked',true);
        }else{
            $("#denture").removeAttr('checked');
        }
        var nutrition_risk_other = <?=json_encode($nutrition_risk_other)?>;
        if(nutrition_risk_other == "Y"){
            $("#nutrition_risk_other").attr('checked',true);
        }else{
            $("#nutrition_risk_other").removeAttr('checked');
        }
        $("input[name=nutrition_risk_other_text]").val(<?=json_encode($nutrition_risk_other_text)?>);

        //ปัสสาวะ
        var normal_urine = <?=json_encode($normal_urine)?>;
        if(normal_urine == "Y"){
            $("#normal_urine").attr('checked',true);
        }else{
            $("#normal_urine").removeAttr('checked');
        }
        var dysuria = <?=json_encode($dysuria)?>;
        if(dysuria == "Y"){
            $("#dysuria").attr('checked',true);
        }else{
            $("#dysuria").removeAttr('checked');
        }
        var incontinence = <?=json_encode($incontinence)?>;
        if(incontinence == "Y"){
            $("#incontinence").attr('checked',true);
        }else{
            $("#incontinence").removeAttr('checked');
        }
        var staining = <?=json_encode($staining)?>;
        if(staining == "Y"){
            $("#staining").attr('checked',true);
        }else{
            $("#staining").removeAttr('checked');
        }
        var hematuria = <?=json_encode($hematuria)?>;
        if(hematuria == "Y"){
            $("#hematuria").attr('checked',true);
        }else{
            $("#hematuria").removeAttr('checked');
        }
        var catheter = <?=json_encode($catheter)?>;
        if(catheter == "Y"){
            $("#catheter").attr('checked',true);
        }else{
            $("#catheter").removeAttr('checked');
        }

        //อุจจาระ
        var normal_feces = <?=json_encode($normal_feces)?>;
        if(normal_feces == "Y"){
            $("#normal_feces").attr('checked',true);
        }else{
            $("#normal_feces").removeAttr('checked');
        }
        var constipation = <?=json_encode($constipation)?>;
        if(constipation == "Y"){
            $("#constipation").attr('checked',true);
        }else{
            $("#constipation").removeAttr('checked');
        }
        var diarrhea = <?=json_encode($diarrhea)?>;
        if(diarrhea == "Y"){
            $("#diarrhea").attr('checked',true);
        }else{
            $("#diarrhea").removeAttr('checked');
        }
        var bowel_incontinence = <?=json_encode($bowel_incontinence)?>;
        if(bowel_incontinence == "Y"){
            $("#bowel_incontinence").attr('checked',true);
        }else{
            $("#bowel_incontinence").removeAttr('checked');
        }
        var hemorrhoid = <?=json_encode($hemorrhoid)?>;
        if(hemorrhoid == "Y"){
            $("#hemorrhoid").attr('checked',true);
        }else{
            $("#hemorrhoid").removeAttr('checked');
        }
        var colostomy = <?=json_encode($colostomy)?>;
        if(colostomy == "Y"){
            $("#colostomy").attr('checked',true);
        }else{
            $("#colostomy").removeAttr('checked');
        }

        //กิจกรรมและออกกำลังกาย
        var activity1 = <?=json_encode($activity1)?>;
        if(activity1 == "Y"){
            $("#activity1").attr('checked',true);
        }else{
            $("#activity1").removeAttr('checked');
        }
        var activity2 = <?=json_encode($activity2)?>;
        if(activity2 == "Y"){
            $("#activity2").attr('checked',true);
        }else{
            $("#activity2").removeAttr('checked');
        }
        var activity3 = <?=json_encode($activity3)?>;
        if(activity3 == "Y"){
            $("#activity3").attr('checked',true);
        }else{
            $("#activity3").removeAttr('checked');
        }
        var activity4 = <?=json_encode($activity4)?>;
        if(activity4 == "Y"){
            $("#activity4").attr('checked',true);
        }else{
            $("#activity4").removeAttr('checked');
        }
        $("input[name=o_p_use]").val(<?=json_encode($o_p_use)?>);

        //การพักผ่อนนอนหลับ
        var sleep_per_day = <?=json_encode($sleep_per_day)?>;
        if(sleep_per_day == "Y"){
            $("#sleep_per_day").attr('checked',true);
        }else{
            $("#sleep_per_day").removeAttr('checked');
        }
        $("input[name=sleep_hour]").val(<?=json_encode($sleep_hour)?>);
        var sleep_problems = <?=json_encode($sleep_problems)?>;
        if(sleep_problems == "Y"){
            $("#sleep_problems").attr('checked',true);
        }else{
            $("#sleep_problems").removeAttr('checked');
        }
        $("input[name=sleep_problems_detail]").val(<?=json_encode($sleep_problems_detail)?>);

        //การใช้ยานอนหลับ
        var sleep_med_name = <?=json_encode($sleep_med_name)?>;
        if(sleep_med_name == "ไม่เคย"){
            $("#sleep_med_name1").attr('checked',true);
        }else if(sleep_med_name == "เป็นครั้งคราว"){
            $("#sleep_med_name2").attr('checked',true);
        }else if(sleep_med_name == "เป็นประจำ"){
            $("#sleep_med_name3").attr('checked',true);
        }
        $("input[name=sleep_med_name_detail]").val(<?=json_encode($sleep_med_name_detail)?>);

        //การรับรู้
        var cognitive = <?=json_encode($cognitive)?>;
        if(cognitive == "ตรง"){
            $("#cognitive1").attr('checked',true);
        }else if(cognitive == "ไม่ตรง"){
            $("#cognitive2").attr('checked',true);
        }

        //ความจำ
        var memory = <?=json_encode($memory)?>;
        if(memory == "ปกติ"){
            $("#memory1").attr('checked',true);
        }else if(memory == "ผิดปกติ"){
            $("#memory2").attr('checked',true);
        }
        $("input[name=memory_detail]").val(<?=json_encode($memory_detail)?>);

        //การได้ยิน
        var hearing = <?=json_encode($hearing)?>;
        if(hearing == "ปกติ"){
            $("#hearing1").attr('checked',true);
        }else if(hearing == "ผิดปกติ"){
            $("#hearing2").attr('checked',true);
        }
        $("input[name=hearing_detail]").val(<?=json_encode($hearing_detail)?>);
        var eartone = <?=json_encode($eartone)?>;
        if(eartone == "Y"){
            $("#eartone").attr('checked',true);
        }else{
            $("#eartone").removeAttr('checked');
        }

        //การมองเห็น
        var vision = <?=json_encode($vision)?>;
        if(vision == "ปกติ"){
            $("#vision1").attr('checked',true);
        }else if(vision == "ผิดปกติ"){
            $("#vision2").attr('checked',true);
        }
        $("input[name=vision_detail]").val(<?=json_encode($vision_detail)?>);
        var vision_eyeglasses = <?=json_encode($vision_eyeglasses)?>;
        if(vision_eyeglasses == "Y"){
            $("#vision_eyeglasses").attr('checked',true);
        }else{
            $("#vision_eyeglasses").removeAttr('checked');
        }
        var vision_contactlens = <?=json_encode($vision_contactlens)?>;
        if(vision_contactlens == "Y"){
            $("#vision_contactlens").attr('checked',true);
        }else{
            $("#vision_contactlens").removeAttr('checked');
        }

        //การพูด
        var speech = <?=json_encode($speech)?>;
        if(speech == "ปกติ"){
            $("#speech1").attr('checked',true);
        }else if(speech == "ผิดปกติ"){
            $("#speech2").attr('checked',true);
        }
        $("input[name=speech_detail]").val(<?=json_encode($speech_detail)?>);

        //กระทบต่อภาพลักษณ์
        var self_image = <?=json_encode($self_image)?>;
        if(self_image == "ไม่มี"){
            $("#self_image1").attr('checked',true);
        }else if(self_image == "มี"){
            $("#self_image2").attr('checked',true);
        }
        $("input[name=self_image_detail]").val(<?=json_encode($self_image_detail)?>);

        //กระทบต่อความสามารถ
        var self_activity = <?=json_encode($self_activity)?>;
        if(self_activity == "ไม่มี"){
            $("#self_activity1").attr('checked',true);
        }else if(self_activity == "มี"){
            $("#self_activity2").attr('checked',true);
        }
        $("input[name=self_activity_detail]").val(<?=json_encode($self_activity_detail)?>);

        //ความเจ็บป่วยมีผลกระทบ
        var sickness_effect = <?=json_encode($sickness_effect)?>;
        if(sickness_effect == "ไม่มี"){
            $("#sickness_effect1").attr('checked',true);
        }else if(sickness_effect == "มีผลกระทบต่อ"){
            $("#sickness_effect2").attr('checked',true);
        }
        var sickness_family = <?=json_encode($sickness_family)?>;
        if(sickness_family == "Y"){
            $("#sickness_family").attr('checked',true);
        }else{
            $("#sickness_family").removeAttr('checked');
        }
        var sickness_occupation = <?=json_encode($sickness_occupation)?>;
        if(sickness_occupation == "Y"){
            $("#sickness_occupation").attr('checked',true);
        }else{
            $("#sickness_occupation").removeAttr('checked');
        }
        var sickness_education = <?=json_encode($sickness_education)?>;
        if(sickness_education == "Y"){
            $("#sickness_education").attr('checked',true);
        }else{
            $("#sickness_education").removeAttr('checked');
        }
        var sickness_other = <?=json_encode($sickness_other)?>;
        if(sickness_other == "Y"){
            $("#sickness_other").attr('checked',true);
        }else{
            $("#sickness_other").removeAttr('checked');
        }
        $("input[name=sickness_other_text]").val(<?=json_encode($sickness_other_text)?>);

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
        $("input[name=period_disorders]").val(<?=json_encode($period_disorders)?>);
        $("input[name=period_lmp]").val(<?=json_encode($period_lmp)?>);

        $("input[name=period_menopause]").val(<?=json_encode($period_menopause)?>);
        var period_normal = <?=json_encode($period_normal)?>;
        if(period_normal == "ปกติ"){
            $("#period_normal1").attr('checked',true);
        }else if(period_normal == "ผิดปกติ"){
            $("#period_normal2").attr('checked',true);
        }else if(period_normal == "LMP"){
            $("#period_normal3").attr('checked',true);
        }

        //เต้านม
        var breast = <?=json_encode($breast)?>;
        if(breast == "ปกติ"){
            $("#breast1").attr('checked',true);
        }else if(breast == "ผิดปกติ"){
            $("#breast2").attr('checked',true);
        }
        $("input[name=breast_disorders]").val(<?=json_encode($breast_disorders)?>);

        //วิธีแก้ไขความไม่สบายใจ/กังวล/เคลียด/อื่นๆ
        var consult = <?=json_encode($consult)?>;
        if(consult == "Y"){
            $("#consult").attr('checked',true);
        }else{
            $("#consult").removeAttr('checked');
        }
        var seclude = <?=json_encode($seclude)?>;
        if(seclude == "Y"){
            $("#seclude").attr('checked',true);
        }else{
            $("#seclude").removeAttr('checked');
        }
        var medication = <?=json_encode($medication)?>;
        if(medication == "Y"){
            $("#medication").attr('checked',true);
        }else{
            $("#medication").removeAttr('checked');
        }
        $("input[name=medication_detail]").val(<?=json_encode($medication_detail)?>);
        var religion = <?=json_encode($religion)?>;
        if(religion == "Y"){
            $("#religion").attr('checked',true);
        }else{
            $("#religion").removeAttr('checked');
        }
        var coping_stress_other = <?=json_encode($coping_stress_other)?>;
        if(coping_stress_other == "Y"){
            $("#coping_stress_other").attr('checked',true);
        }else{
            $("#coping_stress_other").removeAttr('checked');
        }
        $("input[name=coping_stress_other_detail]").val(<?=json_encode($coping_stress_other_detail)?>);

        //เชื่อว่าการเจ็บป่วยครั้งนี้มีสาเหตุจาก
        var belief_sickness_behave = <?=json_encode($belief_sickness_behave)?>;
        if(belief_sickness_behave == "Y"){
            $("#belief_sickness_behave").attr('checked',true);
        }else{
            $("#belief_sickness_behave").removeAttr('checked');
        }
        var belief_sickness_age = <?=json_encode($belief_sickness_age)?>;
        if(belief_sickness_age == "Y"){
            $("#belief_sickness_age").attr('checked',true);
        }else{
            $("#belief_sickness_age").removeAttr('checked');
        }
        var belief_sickness_destiny = <?=json_encode($belief_sickness_destiny)?>;
        if(belief_sickness_destiny == "Y"){
            $("#belief_sickness_destiny").attr('checked',true);
        }else{
            $("#belief_sickness_destiny").removeAttr('checked');
        }
        var belief_sickness_other = <?=json_encode($belief_sickness_other)?>;
        if(belief_sickness_other == "Y"){
            $("#belief_sickness_other").attr('checked',true);
        }else{
            $("#belief_sickness_other").removeAttr('checked');
        }
        $("input[name=belief_sickness_other_text]").val(<?=json_encode($belief_sickness_other_text)?>);

        //สิ่งยึดเหนี่ยวด้านจิตใจ
        var belief_believe = <?=json_encode($belief_believe)?>;
        if(belief_believe == "ไม่มี"){
            $("#belief_believe1").attr('checked',true);
        }else if(belief_believe == "มี"){
            $("#belief_believe2").attr('checked',true);
        }
        $("input[name=belief_believe_text]").val(<?=json_encode($belief_believe_text)?>);

        //ความต้องการปฏิบัติกิจกรรมทางศาสนา
        var religious_activity = <?=json_encode($religious_activity)?>;
        if(religious_activity == "ไม่ต้องการ"){
            $("#religious_activity1").attr('checked',true);
        }else if(religious_activity == "ต้องการ"){
            $("#religious_activity2").attr('checked',true);
        }
        $("input[name=religious_activity_text]").val(<?=json_encode($religious_activity_text)?>);
    });
</script>