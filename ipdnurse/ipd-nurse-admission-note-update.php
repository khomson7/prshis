<?php
        require_once './project/function/SessionManager.php';
        SessionManager::checkLoginSessionAndShowMessage(); //เช็ค session
        // SessionManager::checkPermissionAndShowMessage('KPHIS_ACCESS_IPD_NURSE_ADMISSION_NOTE');
        if(!(SessionManager::checkPermission('IPD_NURSE_ADDMISSION_NOTE','EDIT'))){
            return;
        }
        require_once './project/function/DbUtils.php';
        require_once './project/function/KphisQueryUtils.php';

        $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล

        $an = $_REQUEST['an'];
        $hn = KphisQueryUtils::getHnByAn($an);   // function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา
        $nurse_admission_note_id = $_REQUEST['nurse_admission_note_id'];

        $concious       = empty($_REQUEST['concious']) ? null : $_REQUEST['concious'];
        $normal_breath  = empty($_REQUEST['normal_breath']) ? null : $_REQUEST['normal_breath'];
        $kussmaul       = empty($_REQUEST['kussmaul']) ? null : $_REQUEST['kussmaul'];
        $tachypnea      = empty($_REQUEST['tachypnea']) ? null : $_REQUEST['tachypnea'];
        $dyspnea        = empty($_REQUEST['dyspnea']) ? null : $_REQUEST['dyspnea'];
        $apnea          = empty($_REQUEST['apnea']) ? null : $_REQUEST['apnea'];
        $tube           = empty($_REQUEST['tube']) ? null : $_REQUEST['tube'];

        $normal_hr         = empty($_REQUEST['normal_hr']) ? null : $_REQUEST['normal_hr'];
        $arregular         = empty($_REQUEST['arregular']) ? null : $_REQUEST['arregular'];
        $weakness          = empty($_REQUEST['weakness']) ? null : $_REQUEST['weakness'];
        $arrhythmia        = empty($_REQUEST['arrhythmia']) ? null : $_REQUEST['arrhythmia'];
        $chestpain         = empty($_REQUEST['chestpain']) ? null : $_REQUEST['chestpain'];
        $pacemaker         = empty($_REQUEST['pacemaker']) ? null : $_REQUEST['pacemaker'];
        $cardio_other      = empty($_REQUEST['cardio_other']) ? null : $_REQUEST['cardio_other'];
        $cardio_other_text = empty($_REQUEST['cardio_other_text']) ? null : $_REQUEST['cardio_other_text'];

        $normal_cir               = empty($_REQUEST['normal_cir']) ? null : $_REQUEST['normal_cir'];
        $pale                     = empty($_REQUEST['pale']) ? null : $_REQUEST['pale'];
        $cyanosis                 = empty($_REQUEST['cyanosis']) ? null : $_REQUEST['cyanosis'];
        $generalized_edema        = empty($_REQUEST['generalized_edema']) ? null : $_REQUEST['generalized_edema'];
        $localized_edema          = empty($_REQUEST['localized_edema']) ? null : $_REQUEST['localized_edema'];
        $localized_edema_text     = empty($_REQUEST['localized_edema_text']) ? null : $_REQUEST['localized_edema_text'];
        $pitting_edema            = empty($_REQUEST['pitting_edema']) ? null : $_REQUEST['pitting_edema'];
        $pitting_edema_text       = empty($_REQUEST['pitting_edema_text']) ? null : $_REQUEST['pitting_edema_text'];
        $circulation_orther       = empty($_REQUEST['circulation_orther']) ? null : $_REQUEST['circulation_orther'];
        $circulation_orther_text  = empty($_REQUEST['circulation_orther_text']) ? null : $_REQUEST['circulation_orther_text'];
        $no_mental_state  = empty($_REQUEST['no_mental_state']) ? null : $_REQUEST['no_mental_state'];
        $no_mental_state_text  = empty($_REQUEST['no_mental_state_text']) ? null : $_REQUEST['no_mental_state_text'];

        $normal_skin     = empty($_REQUEST['normal_skin']) ? null : $_REQUEST['normal_skin'];
        $dry             = empty($_REQUEST['dry']) ? null : $_REQUEST['dry'];
        $bruise          = empty($_REQUEST['bruise']) ? null : $_REQUEST['bruise'];
        $erythema        = empty($_REQUEST['erythema']) ? null : $_REQUEST['erythema'];
        $abscess         = empty($_REQUEST['abscess']) ? null : $_REQUEST['abscess'];
        $joudice         = empty($_REQUEST['joudice']) ? null : $_REQUEST['joudice'];
        $skin_other      = empty($_REQUEST['skin_other']) ? null : $_REQUEST['skin_other'];
        $skin_other_text = empty($_REQUEST['skin_other_text']) ? null : $_REQUEST['skin_other_text'];

        $pain = empty($_REQUEST['pain']) ? null : $_REQUEST['pain'];
        $location = empty($_REQUEST['location']) ? null : $_REQUEST['location'];
        $pain_charac = empty($_REQUEST['pain_charac']) ? null : $_REQUEST['pain_charac'];
        $pain_charac_text = empty($_REQUEST['pain_charac_text']) ? null : $_REQUEST['pain_charac_text'];
        $pain_score = empty($_REQUEST['pain_score']) ? null : $_REQUEST['pain_score'];

        $normal_behav = empty($_REQUEST['normal_behav']) ? null : $_REQUEST['normal_behav'];
        $agitate = empty($_REQUEST['agitate']) ? null : $_REQUEST['agitate'];
        $aggressive = empty($_REQUEST['aggressive']) ? null : $_REQUEST['aggressive'];
        $depression = empty($_REQUEST['depression']) ? null : $_REQUEST['depression'];
        $madness = empty($_REQUEST['madness']) ? null : $_REQUEST['madness'];
        $behaviour_other = empty($_REQUEST['behaviour_other']) ? null : $_REQUEST['behaviour_other'];
        $behaviour_other_text = empty($_REQUEST['behaviour_other_text']) ? null : $_REQUEST['behaviour_other_text'];

        $normal_emotional = empty($_REQUEST['normal_emotional']) ? null : $_REQUEST['normal_emotional'];
        $angry = empty($_REQUEST['angry']) ? null : $_REQUEST['angry'];
        $moody = empty($_REQUEST['moody']) ? null : $_REQUEST['moody'];
        $anxiety = empty($_REQUEST['anxiety']) ? null : $_REQUEST['anxiety'];
        $fear = empty($_REQUEST['fear']) ? null : $_REQUEST['fear'];
        $emotional_other = empty($_REQUEST['emotional_other']) ? null : $_REQUEST['emotional_other'];
        $emotional_other_text = empty($_REQUEST['emotional_other_text']) ? null : $_REQUEST['emotional_other_text'];

        $no_anxiety = empty($_REQUEST['no_anxiety']) ? null : $_REQUEST['no_anxiety'];
        $study = empty($_REQUEST['study']) ? null : $_REQUEST['study'];
        $family = empty($_REQUEST['family']) ? null : $_REQUEST['family'];
        $economy = empty($_REQUEST['economy']) ? null : $_REQUEST['economy'];
        $habitation = empty($_REQUEST['habitation']) ? null : $_REQUEST['habitation'];
        $illness = empty($_REQUEST['illness']) ? null : $_REQUEST['illness'];

        $spiritual_no = empty($_REQUEST['spiritual_no']) ? null : $_REQUEST['spiritual_no'];
        $spiritual_back_home = empty($_REQUEST['spiritual_back_home']) ? null : $_REQUEST['spiritual_back_home'];
        $spiritual_need_family = empty($_REQUEST['spiritual_need_family']) ? null : $_REQUEST['spiritual_need_family'];
        $spiritual_other = empty($_REQUEST['spiritual_other']) ? null : $_REQUEST['spiritual_other'];
        $spiritual_other_text = empty($_REQUEST['spiritual_other_text']) ? null : $_REQUEST['spiritual_other_text'];
        $spiritual_cant_rated = empty($_REQUEST['spiritual_cant_rated']) ? null : $_REQUEST['spiritual_cant_rated'];
        $spiritual_cant_rated_text = empty($_REQUEST['spiritual_cant_rated_text']) ? null : $_REQUEST['spiritual_cant_rated_text'];

        $education = empty($_REQUEST['education']) ? null : $_REQUEST['education'];
        $education_result = empty($_REQUEST['education_result']) ? null : $_REQUEST['education_result'];

        $occupation = empty($_REQUEST['occupation']) ? null : $_REQUEST['occupation'];
        $income = empty($_REQUEST['income']) ? null : $_REQUEST['income'];
        $self = empty($_REQUEST['self']) ? null : $_REQUEST['self'];
        $person_family = empty($_REQUEST['person_family']) ? null : $_REQUEST['person_family'];
        $neighbor = empty($_REQUEST['neighbor']) ? null : $_REQUEST['neighbor'];
        $assistant_other = empty($_REQUEST['assistant_other']) ? null : $_REQUEST['assistant_other'];
        $assistant_other_text = empty($_REQUEST['assistant_other_text']) ? null : $_REQUEST['assistant_other_text'];
        $assistant_occupation = empty($_REQUEST['assistant_occupation']) ? null : $_REQUEST['assistant_occupation'];

        $clinic = empty($_REQUEST['clinic']) ? null : $_REQUEST['clinic'];
        $buy_medicine = empty($_REQUEST['buy_medicine']) ? null : $_REQUEST['buy_medicine'];

        $no_risk = empty($_REQUEST['no_risk']) ? null : $_REQUEST['no_risk'];
        $smoking = empty($_REQUEST['smoking']) ? null : $_REQUEST['smoking'];
        $smoke_year = empty($_REQUEST['smoke_year']) ? null : $_REQUEST['smoke_year'];
        $smoke_frequency = empty($_REQUEST['smoke_frequency']) ? null : $_REQUEST['smoke_frequency'];
        $smoke_stopped = empty($_REQUEST['smoke_stopped']) ? null : $_REQUEST['smoke_stopped'];

        $alcohol = empty($_REQUEST['alcohol']) ? null : $_REQUEST['alcohol'];
        $alc_year = empty($_REQUEST['alc_year']) ? null : $_REQUEST['alc_year'];
        $alc_frequency = empty($_REQUEST['alc_frequency']) ? null : $_REQUEST['alc_frequency'];
        $alc_stopped = empty($_REQUEST['alc_stopped']) ? null : $_REQUEST['alc_stopped'];

        $medication_used = empty($_REQUEST['medication_used']) ? null : $_REQUEST['medication_used'];
        $med_name = empty($_REQUEST['med_name']) ? null : $_REQUEST['med_name'];
        $med_year = empty($_REQUEST['med_year']) ? null : $_REQUEST['med_year'];
        $med_frequency = empty($_REQUEST['med_frequency']) ? null : $_REQUEST['med_frequency'];
        $med_stopped = empty($_REQUEST['med_stopped']) ? null : $_REQUEST['med_stopped'];

        $diet_regular = empty($_REQUEST['diet_regular']) ? null : $_REQUEST['diet_regular'];
        $diet_spec = empty($_REQUEST['diet_spec']) ? null : $_REQUEST['diet_spec'];
        $nutrition_risk = empty($_REQUEST['nutrition_risk']) ? null : $_REQUEST['nutrition_risk'];
        $loss_appetite = empty($_REQUEST['loss_appetite']) ? null : $_REQUEST['loss_appetite'];
        $dysphagia = empty($_REQUEST['dysphagia']) ? null : $_REQUEST['dysphagia'];
        $loss_gustation = empty($_REQUEST['loss_gustation']) ? null : $_REQUEST['loss_gustation'];
        $denture = empty($_REQUEST['denture']) ? null : $_REQUEST['denture'];
        $nutrition_risk_other = empty($_REQUEST['nutrition_risk_other']) ? null : $_REQUEST['nutrition_risk_other'];
        $nutrition_risk_other_text = empty($_REQUEST['nutrition_risk_other_text']) ? null : $_REQUEST['nutrition_risk_other_text'];

        $normal_urine = empty($_REQUEST['normal_urine']) ? null : $_REQUEST['normal_urine'];
        $dysuria = empty($_REQUEST['dysuria']) ? null : $_REQUEST['dysuria'];
        $incontinence = empty($_REQUEST['incontinence']) ? null : $_REQUEST['incontinence'];
        $staining = empty($_REQUEST['staining']) ? null : $_REQUEST['staining'];
        $hematuria = empty($_REQUEST['hematuria']) ? null : $_REQUEST['hematuria'];
        $catheter = empty($_REQUEST['catheter']) ? null : $_REQUEST['catheter'];
        $normal_feces = empty($_REQUEST['normal_feces']) ? null : $_REQUEST['normal_feces'];
        $constipation = empty($_REQUEST['constipation']) ? null : $_REQUEST['constipation'];
        $diarrhea = empty($_REQUEST['diarrhea']) ? null : $_REQUEST['diarrhea'];
        $bowel_incontinence = empty($_REQUEST['bowel_incontinence']) ? null : $_REQUEST['bowel_incontinence'];
        $hemorrhoid = empty($_REQUEST['hemorrhoid']) ? null : $_REQUEST['hemorrhoid'];
        $colostomy = empty($_REQUEST['colostomy']) ? null : $_REQUEST['colostomy'];
        $activity1 = empty($_REQUEST['activity1']) ? null : $_REQUEST['activity1'];
        $activity2 = empty($_REQUEST['activity2']) ? null : $_REQUEST['activity2'];
        $activity3 = empty($_REQUEST['activity3']) ? null : $_REQUEST['activity3'];
        $activity4 = empty($_REQUEST['activity4']) ? null : $_REQUEST['activity4'];
        $o_p_use = empty($_REQUEST['o_p_use']) ? null : $_REQUEST['o_p_use'];

        $sleep_per_day = empty($_REQUEST['sleep_per_day']) ? null : $_REQUEST['sleep_per_day'];
        $sleep_hour = empty($_REQUEST['sleep_hour']) ? null : $_REQUEST['sleep_hour'];
        $sleep_problems = empty($_REQUEST['sleep_problems']) ? null : $_REQUEST['sleep_problems'];
        $sleep_problems_detail = empty($_REQUEST['sleep_problems_detail']) ? null : $_REQUEST['sleep_problems_detail'];
        $sleep_med_name = empty($_REQUEST['sleep_med_name']) ? null : $_REQUEST['sleep_med_name'];
        $sleep_med_name_detail = empty($_REQUEST['sleep_med_name_detail']) ? null : $_REQUEST['sleep_med_name_detail'];

        $cognitive = empty($_REQUEST['cognitive']) ? null : $_REQUEST['cognitive'];
        $memory = empty($_REQUEST['memory']) ? null : $_REQUEST['memory'];
        $memory_detail = empty($_REQUEST['memory_detail']) ? null : $_REQUEST['memory_detail'];
        $hearing = empty($_REQUEST['hearing']) ? null : $_REQUEST['hearing'];
        $hearing_detail = empty($_REQUEST['hearing_detail']) ? null : $_REQUEST['hearing_detail'];
        $eartone = empty($_REQUEST['eartone']) ? null : $_REQUEST['eartone'];

        $vision = empty($_REQUEST['vision']) ? null : $_REQUEST['vision'];
        $vision_detail = empty($_REQUEST['vision_detail']) ? null : $_REQUEST['vision_detail'];
        $vision_eyeglasses = empty($_REQUEST['vision_eyeglasses']) ? null : $_REQUEST['vision_eyeglasses'];
        $vision_contactlens = empty($_REQUEST['vision_contactlens']) ? null : $_REQUEST['vision_contactlens'];
        $speech = empty($_REQUEST['speech']) ? null : $_REQUEST['speech'];
        $speech_detail = empty($_REQUEST['speech_detail']) ? null : $_REQUEST['speech_detail'];

        $self_image = empty($_REQUEST['self_image']) ? null : $_REQUEST['self_image'];
        $self_image_detail = empty($_REQUEST['self_image_detail']) ? null : $_REQUEST['self_image_detail'];
        $self_activity = empty($_REQUEST['self_activity']) ? null : $_REQUEST['self_activity'];
        $self_activity_detail = empty($_REQUEST['self_activity_detail']) ? null : $_REQUEST['self_activity_detail'];

        $sickness_effect = empty($_REQUEST['sickness_effect']) ? null : $_REQUEST['sickness_effect'];
        $sickness_family = empty($_REQUEST['sickness_family']) ? null : $_REQUEST['sickness_family'];
        $sickness_occupation = empty($_REQUEST['sickness_occupation']) ? null : $_REQUEST['sickness_occupation'];
        $sickness_education = empty($_REQUEST['sickness_education']) ? null : $_REQUEST['sickness_education'];
        $sickness_other = empty($_REQUEST['sickness_other']) ? null : $_REQUEST['sickness_other'];
        $sickness_other_text = empty($_REQUEST['sickness_other_text']) ? null : $_REQUEST['sickness_other_text'];

        $period = empty($_REQUEST['period']) ? null : $_REQUEST['period'];
        $period_normal = empty($_REQUEST['period_normal']) ? null : $_REQUEST['period_normal'];
        $period_disorders = empty($_REQUEST['period_disorders']) ? null : $_REQUEST['period_disorders'];
        $period_lmp = empty($_REQUEST['period_lmp']) ? null : $_REQUEST['period_lmp'];
        $period_menopause = empty($_REQUEST['period_menopause']) ? null : $_REQUEST['period_menopause'];

        $breast = empty($_REQUEST['breast']) ? null : $_REQUEST['breast'];
        $breast_disorders = empty($_REQUEST['breast_disorders']) ? null : $_REQUEST['breast_disorders'];
        $consult = empty($_REQUEST['consult']) ? null : $_REQUEST['consult'];
        $seclude = empty($_REQUEST['seclude']) ? null : $_REQUEST['seclude'];
        $medication = empty($_REQUEST['medication']) ? null : $_REQUEST['medication'];
        $medication_detail = empty($_REQUEST['medication_detail']) ? null : $_REQUEST['medication_detail'];
        $religion = empty($_REQUEST['religion']) ? null : $_REQUEST['religion'];
        $coping_stress_other = empty($_REQUEST['coping_stress_other']) ? null : $_REQUEST['coping_stress_other'];
        $coping_stress_other_detail = empty($_REQUEST['coping_stress_other_detail']) ? null : $_REQUEST['coping_stress_other_detail'];

        $belief_sickness_behave = empty($_REQUEST['belief_sickness_behave']) ? null : $_REQUEST['belief_sickness_behave'];
        $belief_sickness_age = empty($_REQUEST['belief_sickness_age']) ? null : $_REQUEST['belief_sickness_age'];
        $belief_sickness_destiny = empty($_REQUEST['belief_sickness_destiny']) ? null : $_REQUEST['belief_sickness_destiny'];
        $belief_sickness_other = empty($_REQUEST['belief_sickness_other']) ? null : $_REQUEST['belief_sickness_other'];
        $belief_sickness_other_text = empty($_REQUEST['belief_sickness_other_text']) ? null : $_REQUEST['belief_sickness_other_text'];
        $belief_believe = empty($_REQUEST['belief_believe']) ? null : $_REQUEST['belief_believe'];
        $belief_believe_text = empty($_REQUEST['belief_believe_text']) ? null : $_REQUEST['belief_believe_text'];

        $religious_activity = empty($_REQUEST['religious_activity']) ? null : $_REQUEST['religious_activity'];
        $religious_activity_text = empty($_REQUEST['religious_activity_text']) ? null : $_REQUEST['religious_activity_text'];

        $update_user = $_SESSION['loginname'];

        //-----------------เช็คเลข version ว่าตรงกับฐานข้อมูลหรือไม่
        $query_parameters_version = ['an' => $an];
        $version_request = $_REQUEST['version'];//รับค่าเลข version
        $sql_version = "SELECT version FROM ".KphisConstant::HOSXP_CONNECTION_KPHIS_DBNAME.".ipd_nurse_admission_note WHERE an=:an";
        $stmt_version = $conn->prepare($sql_version);
        $stmt_version->execute($query_parameters_version);
        $row_version = $stmt_version->fetch();
        $version = $row_version['version'];
        if($version_request == $version){
            $version = $version+1;

            try {
                $stmt = $conn->prepare("UPDATE ".KphisConstant::HOSXP_CONNECTION_KPHIS_DBNAME.".ipd_nurse_admission_note SET hn=:hn, an=:an, concious=:concious, normal_breath=:normal_breath, kussmaul=:kussmaul,
                tachypnea=:tachypnea, dyspnea=:dyspnea, apnea=:apnea, tube=:tube, normal_hr=:normal_hr,
                arregular=:arregular, weakness=:weakness, arrhythmia=:arrhythmia, chestpain=:chestpain, pacemaker=:pacemaker,
                cardio_other=:cardio_other, cardio_other_text=:cardio_other_text, normal_cir=:normal_cir, pale=:pale, cyanosis=:cyanosis,
                generalized_edema=:generalized_edema, localized_edema=:localized_edema, localized_edema_text=:localized_edema_text, pitting_edema=:pitting_edema, pitting_edema_text=:pitting_edema_text,
                circulation_orther=:circulation_orther, circulation_orther_text=:circulation_orther_text, no_mental_state=:no_mental_state, no_mental_state_text=:no_mental_state_text,
                normal_skin=:normal_skin, dry=:dry, bruise=:bruise,
                erythema=:erythema, abscess=:abscess, joudice=:joudice, skin_other=:skin_other, skin_other_text=:skin_other_text,
                pain=:pain, location=:location, pain_charac=:pain_charac, pain_charac_text=:pain_charac_text, pain_score=:pain_score,
                normal_behav=:normal_behav, agitate=:agitate, aggressive=:aggressive, depression=:depression, madness=:madness,
                behaviour_other=:behaviour_other, behaviour_other_text=:behaviour_other_text, normal_emotional=:normal_emotional, angry=:angry, moody=:moody,
                anxiety=:anxiety, fear=:fear, emotional_other=:emotional_other, emotional_other_text=:emotional_other_text, no_anxiety=:no_anxiety,
                study=:study, family=:family, economy=:economy, habitation=:habitation, illness=:illness,
                spiritual_no=:spiritual_no, spiritual_back_home=:spiritual_back_home, spiritual_need_family=:spiritual_need_family, spiritual_other=:spiritual_other, spiritual_other_text=:spiritual_other_text,
                spiritual_cant_rated=:spiritual_cant_rated, spiritual_cant_rated_text=:spiritual_cant_rated_text, education=:education, education_result=:education_result, occupation=:occupation,
                income=:income, self=:self, person_family=:person_family, neighbor=:neighbor, assistant_other=:assistant_other, assistant_other_text=:assistant_other_text,
                assistant_occupation=:assistant_occupation, clinic=:clinic, buy_medicine=:buy_medicine, no_risk=:no_risk, smoking=:smoking,
                smoke_year=:smoke_year, smoke_frequency=:smoke_frequency, smoke_stopped=:smoke_stopped, alcohol=:alcohol, alc_year=:alc_year,
                alc_frequency=:alc_frequency, alc_stopped=:alc_stopped, medication_used=:medication_used, med_name=:med_name, med_year=:med_year,
                med_frequency=:med_frequency, med_stopped=:med_stopped, diet_regular=:diet_regular, diet_spec=:diet_spec, nutrition_risk=:nutrition_risk,
                loss_appetite=:loss_appetite, dysphagia=:dysphagia, loss_gustation=:loss_gustation, denture=:denture, nutrition_risk_other=:nutrition_risk_other, nutrition_risk_other_text=:nutrition_risk_other_text,
                normal_urine=:normal_urine, dysuria=:dysuria, incontinence=:incontinence, staining=:staining, hematuria=:hematuria,
                catheter=:catheter, normal_feces=:normal_feces, constipation=:constipation, diarrhea=:diarrhea, bowel_incontinence=:bowel_incontinence,
                hemorrhoid=:hemorrhoid, colostomy=:colostomy, activity1=:activity1, activity2=:activity2, activity3=:activity3, activity4=:activity4,
                o_p_use=:o_p_use, sleep_per_day=:sleep_per_day,
                sleep_hour=:sleep_hour, sleep_problems=:sleep_problems, sleep_problems_detail=:sleep_problems_detail, sleep_med_name=:sleep_med_name, sleep_med_name_detail=:sleep_med_name_detail,
                cognitive=:cognitive, memory=:memory, memory_detail=:memory_detail, hearing=:hearing, hearing_detail=:hearing_detail,
                eartone=:eartone, vision=:vision, vision_detail=:vision_detail, vision_eyeglasses=:vision_eyeglasses, vision_contactlens=:vision_contactlens,
                speech=:speech, speech_detail=:speech_detail, self_image=:self_image, self_image_detail=:self_image_detail, self_activity=:self_activity,
                self_activity_detail=:self_activity_detail, sickness_effect=:sickness_effect, sickness_family=:sickness_family, sickness_occupation=:sickness_occupation, sickness_education=:sickness_education,
                sickness_other=:sickness_other, sickness_other_text=:sickness_other_text, period=:period, period_normal=:period_normal, period_disorders=:period_disorders,
                period_lmp=:period_lmp, period_menopause=:period_menopause, breast=:breast, breast_disorders=:breast_disorders, consult=:consult,
                seclude=:seclude, medication=:medication, medication_detail=:medication_detail, religion=:religion, coping_stress_other=:coping_stress_other,
                coping_stress_other_detail=:coping_stress_other_detail, belief_sickness_behave=:belief_sickness_behave, belief_sickness_age=:belief_sickness_age, belief_sickness_destiny=:belief_sickness_destiny, belief_sickness_other=:belief_sickness_other,
                belief_sickness_other_text=:belief_sickness_other_text, belief_believe=:belief_believe, belief_believe_text=:belief_believe_text, religious_activity=:religious_activity, religious_activity_text=:religious_activity_text,
                update_user=:update_user, update_datetime=now(), version=:version
                WHERE nurse_admission_note_id=:nurse_admission_note_id");
                $stmt->execute(array('nurse_admission_note_id'=>$nurse_admission_note_id, 'hn'=>$hn, 'an'=>$an, 'concious'=>$concious, 'normal_breath'=>$normal_breath, 'kussmaul'=>$kussmaul,
                'tachypnea'=>$tachypnea, 'dyspnea'=>$dyspnea, 'apnea'=>$apnea, 'tube'=>$tube, 'normal_hr'=>$normal_hr,
                'arregular'=>$arregular, 'weakness'=>$weakness, 'arrhythmia'=>$arrhythmia, 'chestpain'=>$chestpain, 'pacemaker'=>$pacemaker,
                'cardio_other'=>$cardio_other, 'cardio_other_text'=>$cardio_other_text, 'normal_cir'=>$normal_cir, 'pale'=>$pale, 'cyanosis'=>$cyanosis,
                'generalized_edema'=>$generalized_edema, 'localized_edema'=>$localized_edema, 'localized_edema_text'=>$localized_edema_text, 'pitting_edema'=>$pitting_edema, 'pitting_edema_text'=>$pitting_edema_text,
                'circulation_orther'=>$circulation_orther, 'circulation_orther_text'=>$circulation_orther_text, 'no_mental_state'=>$no_mental_state, 'no_mental_state_text'=>$no_mental_state_text,
                'normal_skin'=>$normal_skin, 'dry'=>$dry, 'bruise'=>$bruise,
                'erythema'=>$erythema, 'abscess'=>$abscess, 'joudice'=>$joudice, 'skin_other'=>$skin_other, 'skin_other_text'=>$skin_other_text,
                'pain'=>$pain, 'location'=>$location, 'pain_charac'=>$pain_charac, 'pain_charac_text'=>$pain_charac_text, 'pain_score'=>$pain_score,
                'normal_behav'=>$normal_behav, 'agitate'=>$agitate, 'aggressive'=>$aggressive, 'depression'=>$depression, 'madness'=>$madness,
                'behaviour_other'=>$behaviour_other, 'behaviour_other_text'=>$behaviour_other_text, 'normal_emotional'=>$normal_emotional, 'angry'=>$angry, 'moody'=>$moody,
                'anxiety'=>$anxiety, 'fear'=>$fear, 'emotional_other'=>$emotional_other, 'emotional_other_text'=>$emotional_other_text, 'no_anxiety'=>$no_anxiety,
                'study'=>$study, 'family'=>$family, 'economy'=>$economy, 'habitation'=>$habitation, 'illness'=>$illness,
                'spiritual_no'=>$spiritual_no, 'spiritual_back_home'=>$spiritual_back_home, 'spiritual_need_family'=>$spiritual_need_family, 'spiritual_other'=>$spiritual_other,
                'spiritual_other_text'=>$spiritual_other_text,
                'spiritual_cant_rated'=>$spiritual_cant_rated, 'spiritual_cant_rated_text'=>$spiritual_cant_rated_text, 'education'=>$education, 'education_result'=>$education_result, 'occupation'=>$occupation,
                'income'=>$income, 'self'=>$self, 'person_family'=>$person_family, 'neighbor'=>$neighbor, 'assistant_other'=>$assistant_other, 'assistant_other_text'=>$assistant_other_text,
                'assistant_occupation'=>$assistant_occupation, 'clinic'=>$clinic, 'buy_medicine'=>$buy_medicine, 'no_risk'=>$no_risk, 'smoking'=>$smoking,
                'smoke_year'=>$smoke_year, 'smoke_frequency'=>$smoke_frequency, 'smoke_stopped'=>$smoke_stopped, 'alcohol'=>$alcohol, 'alc_year'=>$alc_year,
                'alc_frequency'=>$alc_frequency, 'alc_stopped'=>$alc_stopped, 'medication_used'=>$medication_used, 'med_name'=>$med_name, 'med_year'=>$med_year,
                'med_frequency'=>$med_frequency, 'med_stopped'=>$med_stopped, 'diet_regular'=>$diet_regular, 'diet_spec'=>$diet_spec, 'nutrition_risk'=>$nutrition_risk,
                'loss_appetite'=>$loss_appetite, 'dysphagia'=>$dysphagia, 'loss_gustation'=>$loss_gustation, 'denture'=>$denture, 'nutrition_risk_other'=>$nutrition_risk_other, 'nutrition_risk_other_text'=>$nutrition_risk_other_text,
                'normal_urine'=>$normal_urine, 'dysuria'=>$dysuria, 'incontinence'=>$incontinence, 'staining'=>$staining, 'hematuria'=>$hematuria,
                'catheter'=>$catheter, 'normal_feces'=>$normal_feces, 'constipation'=>$constipation, 'diarrhea'=>$diarrhea, 'bowel_incontinence'=>$bowel_incontinence,
                'hemorrhoid'=>$hemorrhoid, 'colostomy'=>$colostomy, 'activity1'=>$activity1,  'activity2'=>$activity2, 'activity3'=>$activity3, 'activity4'=>$activity4,
                'o_p_use'=>$o_p_use, 'sleep_per_day'=>$sleep_per_day,
                'sleep_hour'=>$sleep_hour, 'sleep_problems'=>$sleep_problems, 'sleep_problems_detail'=>$sleep_problems_detail, 'sleep_med_name'=>$sleep_med_name, 'sleep_med_name_detail'=>$sleep_med_name_detail,
                'cognitive'=>$cognitive, 'memory'=>$memory, 'memory_detail'=>$memory_detail, 'hearing'=>$hearing, 'hearing_detail'=>$hearing_detail,
                'eartone'=>$eartone, 'vision'=>$vision, 'vision_detail'=>$vision_detail, 'vision_eyeglasses'=>$vision_eyeglasses, 'vision_contactlens'=>$vision_contactlens,
                'speech'=>$speech, 'speech_detail'=>$speech_detail, 'self_image'=>$self_image, 'self_image_detail'=>$self_image_detail, 'self_activity'=>$self_activity,
                'self_activity_detail'=>$self_activity_detail, 'sickness_effect'=>$sickness_effect, 'sickness_family'=>$sickness_family, 'sickness_occupation'=>$sickness_occupation, 'sickness_education'=>$sickness_education,
                'sickness_other'=>$sickness_other, 'sickness_other_text'=>$sickness_other_text, 'period'=>$period, 'period_normal'=>$period_normal, 'period_disorders'=>$period_disorders,
                'period_lmp'=>$period_lmp, 'period_menopause'=>$period_menopause, 'breast'=>$breast, 'breast_disorders'=>$breast_disorders, 'consult'=>$consult,
                'seclude'=>$seclude, 'medication'=>$medication, 'medication_detail'=>$medication_detail, 'religion'=>$religion, 'coping_stress_other'=>$coping_stress_other,
                'coping_stress_other_detail'=>$coping_stress_other_detail, 'belief_sickness_behave'=>$belief_sickness_behave, 'belief_sickness_age'=>$belief_sickness_age, 'belief_sickness_destiny'=>$belief_sickness_destiny, 'belief_sickness_other'=>$belief_sickness_other,
                'belief_sickness_other_text'=>$belief_sickness_other_text, 'belief_believe'=>$belief_believe, 'belief_believe_text'=>$belief_believe_text, 'religious_activity'=>$religious_activity, 'religious_activity_text'=>$religious_activity_text,
                'update_user'=>$update_user, 'version'=>$version
                ));

            $output_error = '<div class="alert alert-success">บันทึกข้อมูลเรียบร้อยแล้วคะ</div>';

                } catch (PDOException  $e) {
                    echo $e->getMessage();
                    $output_error = '<div class="alert alert-danger">ERROR !!</div>';
                }

            echo $output_error;
        }else{

            exit;
        }
?>