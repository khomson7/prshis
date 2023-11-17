<?php   

require_once '../include/Session.php';
   //ตรวจสอบว่า session login ตรงกันหรือไม่
        
             
   $login = empty($_REQUEST['loginname']) ? null : $_REQUEST['loginname'];
   $loginname = $_SESSION['loginname'];
   $values =['loginname'=>$loginname];
   
   //หากพบว่าไม่ตรงกันให้ ทำลาย session เดิมทิ้งไป
   if(!$loginname){
    session_start();
    session_destroy();              
        
  } 

  Session::checkLoginSessionAndShowMessage(); //เช็ค session
  Session::checkPermissionAndShowMessage('DOCUMENT', 'PRINT');

  require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
  require_once '../include/DbUtils.php';
  require_once '../include/KphisQueryUtils.php';
        $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
        $an = $_REQUEST['an'];
        $hn = KphisQueryUtils::getHnByAn($an);   // function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา
       // require_once __DIR__ . '../vendor/autoload.php';
        date_default_timezone_set('asia/bangkok');
        $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
        $mpdf->AddPageByArray([
            'margin-left' => 8,
            'margin-right' => 8,
            'margin-top' => 8,
            'margin-bottom' => 5,
        ]);

        Session::insertSystemAccessLog(json_encode(array(
            'report'=>'IPD-NURSE-ADMISSION-NOTE-PDF',
           // 'action'=>'PRINT',
            'an'=>$an,
        ),JSON_UNESCAPED_UNICODE));

  

        //----------------------select ข้อมูล จากฐานข้อมูลเพื่อค้นหา ชื่อ - สกุล
        $image_uncheck = "<img src='../include/images/check-adm.jpg' width='1.6%' class='check_img'>";
        $image_check = "<img src='../include/images/check-adm-1.png' width='1.6%' class='check_img'>";
        $query_parameters_REQUEST = ['an'=>$an];
        $sql_ipt = "select patient.hn,patient.pname,patient.fname,patient.lname,patient.drugallergy,
                    an_stat.age_y,an_stat.age_m,an_stat.age_d,
                    ipt.regdate,ipt.regtime,ipt.ward,
                    ipt.pttype,
                    pttype.`name` as pttype_name,
                    ward.shortname
                    from ".DbConstant::HOSXP_DBNAME.".ipt
                    left outer join ".DbConstant::HOSXP_DBNAME.".an_stat on an_stat.an=ipt.an
                    left outer join ".DbConstant::HOSXP_DBNAME.".patient on patient.hn=ipt.hn
                    left outer join ".DbConstant::HOSXP_DBNAME.".ward on ward.ward=ipt.ward
                    LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".pttype ON pttype.pttype = ipt.pttype
                    WHERE ipt.an=:an";
        $stmt_ipt = $conn->prepare($sql_ipt);
        $stmt_ipt->execute($query_parameters_REQUEST);
        $row_iptCount = 0;
        while ($row_ipt = $stmt_ipt->fetch()){
            $hn_row_ipt = htmlspecialchars($row_ipt['hn']);
            $pname_row_ipt = htmlspecialchars($row_ipt['pname']);
            $fname_row_ipt = htmlspecialchars($row_ipt['fname']);
            $lname_row_ipt = htmlspecialchars($row_ipt['lname']);
        }
        $mpdf->setFooter(' (พิมพ์โดย '.$_SESSION['name'].' วันที่พิมพ์ '.date('d/m/Y H:i').' ) '.'<br>ผู้ป่วย : (hn : '.$hn_row_ipt.')(an : '.$an.')(ชื่อ - สกุล : '.$pname_row_ipt.' '.$fname_row_ipt.' '.$lname_row_ipt.')'.' ( Page {PAGENO} of {nb} )');
        $mpdf->WriteHTML('');
        //----------------------select ข้อมูล จากฐานข้อมูลเพื่อค้นหา ชื่อ - สกุล
        //----------------------ipd_nurse_admission_note
        $sql = "SELECT nurse_adm.* ,opduser.name AS name_full, entryposition
                FROM ".DbConstant::KPHIS_DBNAME.".ipd_nurse_admission_note nurse_adm
                LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".opduser ON nurse_adm.update_user = opduser.loginname
                WHERE an=:an";
        $parameters['an'] = $an;
        $stmt = $conn->prepare($sql);
        $stmt->execute($parameters);
        $row = $stmt->fetch();

        //ความรู้สึกตัว
        $concious       =  $row['concious'];
        if($concious == "รู้สึกตัวดี"){$concious_1 = $image_check;}else{$concious_1 = $image_uncheck;}
        if($concious == "สับสน"){$concious_2 = $image_check;}else{$concious_2 = $image_uncheck;}
        if($concious == "ง่วงซึม"){$concious_3 = $image_check;}else{$concious_3 = $image_uncheck;}
        if($concious == "ไม่รู้สึกตัว"){$concious_4 = $image_check;}else{$concious_4 = $image_uncheck;}
        //ลักษณะการหายใจ
        $normal_breath  =  $row['normal_breath'];
        if($normal_breath == "Y"){$normal_breath = $image_check;}else{$normal_breath = $image_uncheck;}
        $kussmaul       =  $row['kussmaul'];
        if($kussmaul == "Y"){$kussmaul = $image_check;}else{$kussmaul = $image_uncheck;}
        $tachypnea      =  $row['tachypnea'];
        if($tachypnea == "Y"){$tachypnea = $image_check;}else{$tachypnea = $image_uncheck;}
        $dyspnea        =  $row['dyspnea'];
        if($dyspnea == "Y"){$dyspnea = $image_check;}else{$dyspnea = $image_uncheck;}
        $apnea          =  $row['apnea'];
        if($apnea == "Y"){$apnea = $image_check;}else{$apnea = $image_uncheck;}
        $tube           =  $row['tube'];
        if($tube == "Y"){$tube = $image_check;}else{$tube = $image_uncheck;}
        //ระบบหัวใจ
        $normal_hr         =  $row['normal_hr'];
        if($normal_hr == "Y"){$normal_hr = $image_check;}else{$normal_hr = $image_uncheck;}
        $arregular         =  $row['arregular'];
        if($arregular == "Y"){$arregular = $image_check;}else{$arregular = $image_uncheck;}
        $weakness          =  $row['weakness'];
        if($weakness == "Y"){$weakness = $image_check;}else{$weakness = $image_uncheck;}
        $arrhythmia        =  $row['arrhythmia'];
        if($arrhythmia == "Y"){$arrhythmia = $image_check;}else{$arrhythmia = $image_uncheck;}
        $chestpain         =  $row['chestpain'];
        if($chestpain == "Y"){$chestpain = $image_check;}else{$chestpain = $image_uncheck;}
        $pacemaker         =  $row['pacemaker'];
        if($pacemaker == "Y"){$pacemaker = $image_check;}else{$pacemaker = $image_uncheck;}
        $cardio_other      =  $row['cardio_other'];
        if($cardio_other == "Y"){$cardio_other = $image_check;}else{$cardio_other = $image_uncheck;}
        $cardio_other_text =  $row['cardio_other_text'];
        //การไหลเวียนโลหิต
        $normal_cir               =  $row['normal_cir'];
        if($normal_cir == "Y"){$normal_cir = $image_check;}else{$normal_cir = $image_uncheck;}
        $pale                     =  $row['pale'];
        if($pale == "Y"){$pale = $image_check;}else{$pale = $image_uncheck;}
        $cyanosis                 =  $row['cyanosis'];
        if($cyanosis == "Y"){$cyanosis = $image_check;}else{$cyanosis = $image_uncheck;}
        $generalized_edema        =  $row['generalized_edema'];
        if($generalized_edema == "Y"){$generalized_edema = $image_check;}else{$generalized_edema = $image_uncheck;}
        $localized_edema          =  $row['localized_edema'];
        if($localized_edema == "Y"){$localized_edema = $image_check;}else{$localized_edema = $image_uncheck;}
        $localized_edema_text     =  $row['localized_edema_text'];
        $pitting_edema            =  $row['pitting_edema'];
        if($pitting_edema == "Y"){$pitting_edema = $image_check;}else{$pitting_edema = $image_uncheck;}
        $pitting_edema_text       =  $row['pitting_edema_text'];
        $circulation_orther       =  $row['circulation_orther'];
        if($circulation_orther == "Y"){$circulation_orther = $image_check;}else{$circulation_orther = $image_uncheck;}
        $circulation_orther_text  =  $row['circulation_orther_text'];
        //สภาพผิวหนัง
        $normal_skin     =  $row['normal_skin'];
        if($normal_skin == "Y"){$normal_skin = $image_check;}else{$normal_skin = $image_uncheck;}
        $dry             =  $row['dry'];
        if($dry == "Y"){$dry = $image_check;}else{$dry = $image_uncheck;}
        $bruise          =  $row['bruise'];
        if($bruise == "Y"){$bruise = $image_check;}else{$bruise = $image_uncheck;}
        $erythema        =  $row['erythema'];
        if($erythema == "Y"){$erythema = $image_check;}else{$erythema = $image_uncheck;}
        $abscess         =  $row['abscess'];
        if($abscess == "Y"){$abscess = $image_check;}else{$abscess = $image_uncheck;}
        $joudice         =  $row['joudice'];
        if($joudice == "Y"){$joudice = $image_check;}else{$joudice = $image_uncheck;}
        $skin_other      =  $row['skin_other'];
        if($skin_other == "Y"){$skin_other = $image_check;}else{$skin_other = $image_uncheck;}
        $skin_other_text =  $row['skin_other_text'];
        //ความเจ็บปวด
        $pain               =  $row['pain'];
        if($pain == "ไม่มี"){$pain_1 = $image_check;}else{$pain_1 = $image_uncheck;}
        if($pain == "มี"){$pain_2 = $image_check;}else{$pain_2 = $image_uncheck;}
        $location           =  $row['location'];
        $pain_charac        =  $row['pain_charac'];
        if($pain_charac == "ครั้งคราว"){$pain_charac_1 = $image_check;}else{$pain_charac_1 = $image_uncheck;}
        if($pain_charac == "ตลอดเวลา"){$pain_charac_2 = $image_check;}else{$pain_charac_2 = $image_uncheck;}
        if($pain_charac == "อื่นๆ"){$pain_charac_3 = $image_check;}else{$pain_charac_3 = $image_uncheck;}
        $pain_charac_text   =  $row['pain_charac_text'];
        $pain_score         =  $row['pain_score'];
        //ด้านพฤติกรรม
        $normal_behav           =  $row['normal_behav'];
        if($normal_behav == "Y"){$normal_behav = $image_check;}else{$normal_behav = $image_uncheck;}
        $agitate                =  $row['agitate'];
        if($agitate == "Y"){$agitate = $image_check;}else{$agitate = $image_uncheck;}
        $aggressive             =  $row['aggressive'];
        if($aggressive == "Y"){$aggressive = $image_check;}else{$aggressive = $image_uncheck;}
        $depression             =  $row['depression'];
        if($depression == "Y"){$depression = $image_check;}else{$depression = $image_uncheck;}
        $madness                =  $row['madness'];
        if($madness == "Y"){$madness = $image_check;}else{$madness = $image_uncheck;}
        $behaviour_other        =  $row['behaviour_other'];
        if($behaviour_other == "Y"){$behaviour_other = $image_check;}else{$behaviour_other = $image_uncheck;}
        $behaviour_other_text   =  $row['behaviour_other_text'];
        //ด้านอารมณ์
        $normal_emotional       =  $row['normal_emotional'];
        if($normal_emotional == "Y"){$normal_emotional = $image_check;}else{$normal_emotional = $image_uncheck;}
        $angry                  =  $row['angry'];
        if($angry == "Y"){$angry = $image_check;}else{$angry = $image_uncheck;}
        $moody                  =  $row['moody'];
        if($moody == "Y"){$moody = $image_check;}else{$moody = $image_uncheck;}
        $anxiety                =  $row['anxiety'];
        if($anxiety == "Y"){$anxiety = $image_check;}else{$anxiety = $image_uncheck;}
        $fear                   =  $row['fear'];
        if($fear == "Y"){$fear = $image_check;}else{$fear = $image_uncheck;}
        $emotional_other        =  $row['emotional_other'];
        if($emotional_other == "Y"){$emotional_other = $image_check;}else{$emotional_other = $image_uncheck;}
        $emotional_other_text   =  $row['emotional_other_text'];
        //ความกังวลใจ
        $no_anxiety =  $row['no_anxiety'];
        if($no_anxiety == "Y"){$no_anxiety = $image_check;}else{$no_anxiety = $image_uncheck;}
        $study      =  $row['study'];
        if($study == "Y"){$study = $image_check;}else{$study = $image_uncheck;}
        $family     =  $row['family'];
        if($family == "Y"){$family = $image_check;}else{$family = $image_uncheck;}
        $economy    =  $row['economy'];
        if($economy == "Y"){$economy = $image_check;}else{$economy = $image_uncheck;}
        $habitation =  $row['habitation'];
        if($habitation == "Y"){$habitation = $image_check;}else{$habitation = $image_uncheck;}
        $illness    =  $row['illness'];
        if($illness == "Y"){$illness = $image_check;}else{$illness = $image_uncheck;}
        //ความต้องการด้านจิตวิญญาณ
        $spiritual_no               =  $row['spiritual_no'];
        if($spiritual_no == "Y"){$spiritual_no = $image_check;}else{$spiritual_no = $image_uncheck;}
        $spiritual_back_home        =  $row['spiritual_back_home'];
        if($spiritual_back_home == "Y"){$spiritual_back_home = $image_check;}else{$spiritual_back_home = $image_uncheck;}
        $spiritual_need_family      =  $row['spiritual_need_family'];
        if($spiritual_need_family == "Y"){$spiritual_need_family = $image_check;}else{$spiritual_need_family = $image_uncheck;}
        $spiritual_other            =  $row['spiritual_other'];
        if($spiritual_other == "Y"){$spiritual_other = $image_check;}else{$spiritual_other = $image_uncheck;}
        $spiritual_other_text       =  $row['spiritual_other_text'];
        $spiritual_cant_rated       =  $row['spiritual_cant_rated'];
        if($spiritual_cant_rated == "Y"){$spiritual_cant_rated = $image_check;}else{$spiritual_cant_rated = $image_uncheck;}
        $spiritual_cant_rated_text  =  $row['spiritual_cant_rated_text'];
        $no_mental_state       =  $row['no_mental_state'];
        if($no_mental_state == "Y"){$no_mental_state = $image_check;}else{$no_mental_state = $image_uncheck;}
        $no_mental_state_text  =  $row['no_mental_state_text'];
        //การศึกษา
        $education          =  $row['education'];
        if($education == "ไม่ได้รับ"){$education_1 = $image_check;}else{$education_1 = $image_uncheck;}
        if($education == "ได้รับ"){$education_2 = $image_check;}else{$education_2 = $image_uncheck;}
        $education_result   =  $row['education_result'];

        $occupation             =  $row['occupation'];
        $income                 =  $row['income'];
        if($income == "เพียงพอ"){$income_1 = $image_check;}else{$income_1 = $image_uncheck;}
        if($income == "ไม่เพียงพอ"){$income_2 = $image_check;}else{$income_2 = $image_uncheck;}
        $self                   =  $row['self'];
        if($self == "Y"){$self = $image_check;}else{$self = $image_uncheck;}
        $person_family          =  $row['person_family'];
        if($person_family == "Y"){$person_family = $image_check;}else{$person_family = $image_uncheck;}
        $neighbor               =  $row['neighbor'];
        if($neighbor == "Y"){$neighbor = $image_check;}else{$neighbor = $image_uncheck;}
        $assistant_other        =  $row['assistant_other'];
        if($assistant_other == "Y"){$assistant_other = $image_check;}else{$assistant_other = $image_uncheck;}
        $assistant_other_text        =  $row['assistant_other_text'];
        $assistant_occupation   =  $row['assistant_occupation'];
        //การดูแลตนเอง
        $clinic         =  $row['clinic'];
        if($clinic == "Y"){$clinic = $image_check;}else{$clinic = $image_uncheck;}
        $buy_medicine   =  $row['buy_medicine'];
        if($buy_medicine == "Y"){$buy_medicine = $image_check;}else{$buy_medicine = $image_uncheck;}
        //พฤติกรรมเสี่ยง
        $no_risk            =  $row['no_risk'];
        if($no_risk == "Y"){$no_risk = $image_check;}else{$no_risk = $image_uncheck;}
        $smoking            =  $row['smoking'];
        if($smoking == "Y"){
            $smoking = $image_check;
            $smoke_year         =  $row['smoke_year'];
            $smoke_frequency    =  $row['smoke_frequency'];
            $smoke_stopped      =  $row['smoke_stopped'];
            $smoking_detail = '<U><I>'.htmlspecialchars($smoke_year).'</I></U> '.'ปี ปริมาณ '.'<U><I>'.htmlspecialchars($smoke_frequency).'</I></U>'.' /วัน  เลิกเมื่อ '.'<U><I>'.htmlspecialchars($smoke_stopped).'</I></U>';
        }else{
            $smoking = $image_uncheck;
        }

        $alcohol        =  $row['alcohol'];
        if($alcohol == "Y"){
            $alcohol = $image_check;
            $alc_year       =  $row['alc_year'];
            $alc_frequency  =  $row['alc_frequency'];
            $alc_stopped    =  $row['alc_stopped'];
            $alcohol_detail = '<U><I>'.htmlspecialchars($alc_year).'</I></U> '.'ปี ปริมาณ '.'<U><I>'.htmlspecialchars($alc_frequency).'</I></U>'.' /วัน  เลิกเมื่อ '.'<U><I>'.htmlspecialchars($alc_stopped).'</I></U>';
        }else{
            $alcohol = $image_uncheck;
        }

        $medication_used =  $row['medication_used'];
        if($medication_used == "Y"){
            $medication_used = $image_check;
            $med_name        =  $row['med_name'];
            $med_year        =  $row['med_year'];
            $med_frequency   =  $row['med_frequency'];
            $med_stopped     =  $row['med_stopped'];
            $medication_used_detail = '<U><I>'.htmlspecialchars($med_name).'</I></U> '.' ระยะเวลาที่ใช้ <U><I>'.htmlspecialchars($med_year).'</I></U> '.' ปริมาณ '.'<U><I>'.htmlspecialchars($med_frequency).'</I></U>'.' /วัน  เลิกเมื่อ '.'<U><I>'.htmlspecialchars($med_stopped).'</I></U>';
        }else{
            $medication_used = $image_uncheck;
        }
        //อาหาร และการเผาผลาญอาหาร
        $diet_regular           =  $row['diet_regular'];
        if($diet_regular == "อาหารทั่วไป"){$diet_regular_1 = $image_check;}else{$diet_regular_1 = $image_uncheck;}
        if($diet_regular == "อาหารเฉพาะโรค"){$diet_regular_2 = $image_check;}else{$diet_regular_2 = $image_uncheck;}
        $diet_spec              =  $row['diet_spec'];
        $nutrition_risk         =  $row['nutrition_risk'];
        if($nutrition_risk == "Y"){$nutrition_risk = $image_check;}else{$nutrition_risk = $image_uncheck;}
        $loss_appetite          =  $row['loss_appetite'];
        if($loss_appetite == "Y"){$loss_appetite = $image_check;}else{$loss_appetite = $image_uncheck;}
        $dysphagia              =  $row['dysphagia'];
        if($dysphagia == "Y"){$dysphagia = $image_check;}else{$dysphagia = $image_uncheck;}
        $loss_gustation         =  $row['loss_gustation'];
        if($loss_gustation == "Y"){$loss_gustation = $image_check;}else{$loss_gustation = $image_uncheck;}
        $denture                =  $row['denture'];
        if($denture == "Y"){$denture = $image_check;}else{$denture = $image_uncheck;}
        $nutrition_risk_other   =  $row['nutrition_risk_other'];
        if($nutrition_risk_other == "Y"){$nutrition_risk_other = $image_check;}else{$nutrition_risk_other = $image_uncheck;}
        $nutrition_risk_other_text   =  $row['nutrition_risk_other_text'];
        //ปัสสาวะ
        $normal_urine       =  $row['normal_urine'];
        if($normal_urine == "Y"){$normal_urine = $image_check;}else{$normal_urine = $image_uncheck;}
        $dysuria            =  $row['dysuria'];
        if($dysuria == "Y"){$dysuria = $image_check;}else{$dysuria = $image_uncheck;}
        $incontinence       =  $row['incontinence'];
        if($incontinence == "Y"){$incontinence = $image_check;}else{$incontinence = $image_uncheck;}
        $staining           =  $row['staining'];
        if($staining == "Y"){$staining = $image_check;}else{$staining = $image_uncheck;}
        $hematuria          =  $row['hematuria'];
        if($hematuria == "Y"){$hematuria = $image_check;}else{$hematuria = $image_uncheck;}
        $catheter           =  $row['catheter'];
        if($catheter == "Y"){$catheter = $image_check;}else{$catheter = $image_uncheck;}
        //อุจจาระ
        $normal_feces       =  $row['normal_feces'];
        if($normal_feces == "Y"){$normal_feces = $image_check;}else{$normal_feces = $image_uncheck;}
        $constipation       =  $row['constipation'];
        if($constipation == "Y"){$constipation = $image_check;}else{$constipation = $image_uncheck;}
        $diarrhea           =  $row['diarrhea'];
        if($diarrhea == "Y"){$diarrhea = $image_check;}else{$diarrhea = $image_uncheck;}
        $bowel_incontinence =  $row['bowel_incontinence'];
        if($bowel_incontinence == "Y"){$bowel_incontinence = $image_check;}else{$bowel_incontinence = $image_uncheck;}
        $hemorrhoid         =  $row['hemorrhoid'];
        if($hemorrhoid == "Y"){$hemorrhoid = $image_check;}else{$hemorrhoid = $image_uncheck;}
        $colostomy          =  $row['colostomy'];
        if($colostomy == "Y"){$colostomy = $image_check;}else{$colostomy = $image_uncheck;}
        //กิจกรรมและออกกําลังกาย
        $activity1          =  $row['activity1'];
        if($activity1 == "Y"){$activity1 = $image_check;}else{$activity1 = $image_uncheck;}
        $activity2          =  $row['activity2'];
        if($activity2 == "Y"){$activity2 = $image_check;}else{$activity2 = $image_uncheck;}
        $activity3          =  $row['activity3'];
        if($activity3 == "Y"){$activity3 = $image_check;}else{$activity3 = $image_uncheck;}
        $activity4          =  $row['activity4'];
        if($activity4 == "Y"){$activity4 = $image_check;}else{$activity4 = $image_uncheck;}
        $o_p_use            =  $row['o_p_use'];
        //การพักผ่อนนอนหลับ
        $sleep_per_day          =  $row['sleep_per_day'];
        if($sleep_per_day == "Y"){$sleep_per_day = $image_check;}else{$sleep_per_day = $image_uncheck;}
        $sleep_hour             =  $row['sleep_hour'];
        $sleep_problems         =  $row['sleep_problems'];
        if($sleep_problems == "Y"){$sleep_problems = $image_check;}else{$sleep_problems = $image_uncheck;}
        $sleep_problems_detail  =  $row['sleep_problems_detail'];

        $sleep_med_name         =  $row['sleep_med_name'];
        if($sleep_med_name == "ไม่เคย"){$sleep_med_name_1 = $image_check;}else{$sleep_med_name_1 = $image_uncheck;}
        if($sleep_med_name == "เป็นครั้งคราว"){$sleep_med_name_2 = $image_check;}else{$sleep_med_name_2 = $image_uncheck;}
        if($sleep_med_name == "เป็นประจำ"){$sleep_med_name_3 = $image_check;}else{$sleep_med_name_3 = $image_uncheck;}
        $sleep_med_name_detail  =  $row['sleep_med_name_detail'];
        //การรับรู้
        $cognitive          =  $row['cognitive'];
        if($cognitive == "ตรง"){$cognitive_1 = $image_check;}else{$cognitive_1 = $image_uncheck;}
        if($cognitive == "ไม่ตรง"){$cognitive_2 = $image_check;}else{$cognitive_2 = $image_uncheck;}
        //ความจำ
        $memory             =  $row['memory'];
        if($memory == "ปกติ"){$memory_1 = $image_check;}else{$memory_1 = $image_uncheck;}
        if($memory == "ผิดปกติ"){$memory_2 = $image_check;}else{$memory_2 = $image_uncheck;}
        $memory_detail      =  $row['memory_detail'];
        //การได้ยิน
        $hearing            =  $row['hearing'];
        if($hearing == "ปกติ"){$hearing_1 = $image_check;}else{$hearing_1 = $image_uncheck;}
        if($hearing == "ผิดปกติ"){$hearing_2 = $image_check;}else{$hearing_2 = $image_uncheck;}
        $hearing_detail     =  $row['hearing_detail'];
        $eartone            =  $row['eartone'];
        if($eartone == "Y"){$eartone = $image_check;}else{$eartone = $image_uncheck;}
        //การมองเห็น
        $vision             =  $row['vision'];
        if($vision == "ปกติ"){$vision_1 = $image_check;}else{$vision_1 = $image_uncheck;}
        if($vision == "ผิดปกติ"){$vision_2 = $image_check;}else{$vision_2 = $image_uncheck;}
        $vision_detail      =  $row['vision_detail'];
        $vision_eyeglasses  =  $row['vision_eyeglasses'];
        if($vision_eyeglasses == "Y"){$vision_eyeglasses = $image_check;}else{$vision_eyeglasses = $image_uncheck;}
        $vision_contactlens =  $row['vision_contactlens'];
        if($vision_contactlens == "Y"){$vision_contactlens = $image_check;}else{$vision_contactlens = $image_uncheck;}
        //การพูด
        $speech             =  $row['speech'];
        if($speech == "ปกติ"){$speech_1 = $image_check;}else{$speech_1 = $image_uncheck;}
        if($speech == "ผิดปกติ"){$speech_2 = $image_check;}else{$speech_2 = $image_uncheck;}
        $speech_detail      =  $row['speech_detail'];
        //กระทบต่อภาพลักษณ์
        $self_image             =  $row['self_image'];
        if($self_image == "ไม่มี"){$self_image_1 = $image_check;}else{$self_image_1 = $image_uncheck;}
        if($self_image == "มี"){$self_image_2 = $image_check;}else{$self_image_2 = $image_uncheck;}
        $self_image_detail      =  $row['self_image_detail'];
        //กระทบต่อความสามารถ
        $self_activity          =  $row['self_activity'];
        if($self_activity == "ไม่มี"){$self_activity_1 = $image_check;}else{$self_activity_1 = $image_uncheck;}
        if($self_activity == "มี"){$self_activity_2 = $image_check;}else{$self_activity_2 = $image_uncheck;}
        $self_activity_detail   =  $row['self_activity_detail'];
        //ความเจ็บป่วยมีผลกระทบ
        $sickness_effect        =  $row['sickness_effect'];
        if($sickness_effect == "ไม่มี"){$sickness_effect_1 = $image_check;}else{$sickness_effect_1 = $image_uncheck;}
        if($sickness_effect == "มีผลกระทบต่อ"){$sickness_effect_2 = $image_check;}else{$sickness_effect_2 = $image_uncheck;}
        $sickness_family        =  $row['sickness_family'];
        if($sickness_family == "Y"){$sickness_family = $image_check;}else{$sickness_family = $image_uncheck;}
        $sickness_occupation    =  $row['sickness_occupation'];
        if($sickness_occupation == "Y"){$sickness_occupation = $image_check;}else{$sickness_occupation = $image_uncheck;}
        $sickness_education     =  $row['sickness_education'];
        if($sickness_education == "Y"){$sickness_education = $image_check;}else{$sickness_education = $image_uncheck;}
        $sickness_other         =  $row['sickness_other'];
        if($sickness_other == "Y"){$sickness_other = $image_check;}else{$sickness_other = $image_uncheck;}
        $sickness_other_text    =  $row['sickness_other_text'];
        //ประจำเดือน
        $period             =  $row['period'];
        if($period == "ยังไม่มี"){$period_1 = $image_check;}else{$period_1 = $image_uncheck;}
        if($period == "มี"){$period_2 = $image_check;}else{$period_2 = $image_uncheck;}
        if($period == "หมดประจำเดือน"){$period_3 = $image_check;}else{$period_3 = $image_uncheck;}
        $period_normal      =  $row['period_normal'];
        if($period_normal == "ปกติ"){$period_normal_1 = $image_check;}else{$period_normal_1 = $image_uncheck;}
        if($period_normal == "ผิดปกติ"){$period_normal_2 = $image_check;}else{$period_normal_2 = $image_uncheck;}
        if($period_normal == "LMP"){$period_normal_3 = $image_check;}else{$period_normal_3 = $image_uncheck;}
        $period_disorders   =  $row['period_disorders'];
        $period_lmp         =  $row['period_lmp'];
        $period_menopause   =  $row['period_menopause'];
        //เต้านม
        $breast                     =  $row['breast'];
        if($breast == "ปกติ"){$breast_1 = $image_check;}else{$breast_1 = $image_uncheck;}
        if($breast == "ผิดปกติ"){$breast_2 = $image_check;}else{$breast_2 = $image_uncheck;}
        $breast_disorders           =  $row['breast_disorders'];
        //วิธีแก้ไขความไม่สบายใจ/กังวล/เคลียด/อื่นๆ
        $consult                    =  $row['consult'];
        if($consult == "Y"){$consult = $image_check;}else{$consult = $image_uncheck;}
        $seclude                    =  $row['seclude'];
        if($seclude == "Y"){$seclude = $image_check;}else{$seclude = $image_uncheck;}
        $medication                 =  $row['medication'];
        if($medication == "Y"){$medication = $image_check;}else{$medication = $image_uncheck;}
        $medication_detail          =  $row['medication_detail'];

        $religion                   =  $row['religion'];
        if($religion == "Y"){$religion = $image_check;}else{$religion = $image_uncheck;}
        $coping_stress_other        =  $row['coping_stress_other'];
        if($coping_stress_other == "Y"){$coping_stress_other = $image_check;}else{$coping_stress_other = $image_uncheck;}
        $coping_stress_other_detail =  $row['coping_stress_other_detail'];
        //เชื่อว่าการเจ็บป่วยครั้งนี้มีสาเหตุจาก
        $belief_sickness_behave     =  $row['belief_sickness_behave'];
        if($belief_sickness_behave == "Y"){$belief_sickness_behave = $image_check;}else{$belief_sickness_behave = $image_uncheck;}
        $belief_sickness_age        =  $row['belief_sickness_age'];
        if($belief_sickness_age == "Y"){$belief_sickness_age = $image_check;}else{$belief_sickness_age = $image_uncheck;}
        $belief_sickness_destiny    =  $row['belief_sickness_destiny'];
        if($belief_sickness_destiny == "Y"){$belief_sickness_destiny = $image_check;}else{$belief_sickness_destiny = $image_uncheck;}
        $belief_sickness_other      =  $row['belief_sickness_other'];
        if($belief_sickness_other == "Y"){$belief_sickness_other = $image_check;}else{$belief_sickness_other = $image_uncheck;}
        $belief_sickness_other_text =  $row['belief_sickness_other_text'];
        //สิ่งยึดเหนี่ยวด้านจิตใจ
        $belief_believe             =  $row['belief_believe'];
        if($belief_believe == "ไม่มี"){$belief_believe_1 = $image_check;}else{$belief_believe_1 = $image_uncheck;}
        if($belief_believe == "มี"){$belief_believe_2 = $image_check;}else{$belief_believe_2 = $image_uncheck;}
        $belief_believe_text        =  $row['belief_believe_text'];
        //ความต้องการปฏิบัติกิจกรรมทางศาสนา
        $religious_activity         =  $row['religious_activity'];
        if($religious_activity == "ไม่ต้องการ"){$religious_activity_1 = $image_check;}else{$religious_activity_1 = $image_uncheck;}
        if($religious_activity == "ต้องการ"){$religious_activity_2 = $image_check;}else{$religious_activity_2 = $image_uncheck;}
        $religious_activity_text    =  $row['religious_activity_text'];
        //ชื่อ-สกุล ตำแหน่งผู้บันทึกข้อมูล
        $name_full        =  $row['name_full'];
        $entryposition        =  $row['entryposition'];

       

        //----------------------ipd_nurse_admission_note
        $head = '
                <style>
                body{
                        font-family: "Garuda";//เรียกใช้font Garuda สำหรับแสดงผล ภาษาไทย
                }
                footer {
                        position: fixed;
                        bottom: -60px;
                        left: 0px;
                        right: 0px;
                        height: 50px;

                        /** Extra personal styles **/
                        line-height: 35px;
                }
                .check_img{
                    /*margin-top: 10px;*/
                }
                </style>
                <h4 style="text-align:right;">KPH-N1.1-Adm</h4>
                <table id="bg-table" width="100%" style="border-collapse: collapse;font-size:8pt;margin-top:7px;">
                    <tr style="border:1px solid #000;margin: 35px;">
                        <td style="border-right:0.5px solid #000;padding:4px; text-align:center; background-color:#C0C0C0;" colspan="6"><B>การประเมินสภาพผู้ป่วยแรกรับและแบบแผนสุขภาพ (ยกเว้นผู้ป่วยเด็กอายุ < 1 ปี)</B></td>
                    </tr>
                    <tr style="border:1px solid #000;margin: 35px;">
                        <td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="6"><B>สภาพร่างกายแรกรับ</B></td>
                    </tr>
                    <tr style="border:1px solid #000;margin: 35px;">
                        <td style="border-right:0.5px solid #000;padding:2px; text-align:center; background-color:#C0C0C0;" colspan="1"><B>ความรู้สึกตัว</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px; text-align:center; background-color:#C0C0C0;" colspan="1"><B>ลักษณะการหายใจ</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px; text-align:center; background-color:#C0C0C0;" colspan="1"><B>ระบบหัวใจ</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px; text-align:center; background-color:#C0C0C0;" colspan="1"><B>การไหลเวียนโลหิต</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px; text-align:center; background-color:#C0C0C0;" colspan="1"><B>สภาพผิวหนัง</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px; text-align:center; background-color:#C0C0C0;" colspan="1"><B>ความเจ็บปวด</B></td>
                    </tr>
                    <tr style="border:1px solid #000;margin: 45px;">
                        <td style="border-right:0.5px solid #000;padding:2px;" valign="top">
                            '.$concious_1.'&nbsp;รู้สึกตัวดี<br>
                            '.$concious_2.'&nbsp;สับสน<br>
                            '.$concious_3.'&nbsp;ง่วงซึม<br>
                            '.$concious_4.'&nbsp;ไม่รู้สึกตัว
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" valign="top">
                            '.$normal_breath.'&nbsp;ปกติ<br>
                            '.$kussmaul.'&nbsp;หอบลึก<br>
                            '.$tachypnea.'&nbsp;เร็วตื้น<br>
                            '.$dyspnea.'&nbsp;ลำบาก<br>
                            '.$apnea.'&nbsp;ไม่หายใจ<br>
                            '.$tube.'&nbsp;ใส่ท่อช่วยหายใจ
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" valign="top">
                            '.$normal_hr.'&nbsp;ปกติ<br>
                            '.$arregular.'&nbsp;อัตราการเต้นไม่สม่ำเสมอ<br>
                            '.$weakness.'&nbsp;ชีพจรเบา<br>
                            '.$arrhythmia.'&nbsp;ใจสั่น<br>
                            '.$chestpain.'&nbsp;เจ็บหน้าอก<br>
                            '.$pacemaker.'&nbsp;ใส่เครื่องกระตุ้นหัวใจ<br>
                            '.$cardio_other.'&nbsp;อื่นๆ&nbsp;<U><I>'.htmlspecialchars($cardio_other_text).'</I></U>
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" valign="top">
                            '.$normal_cir.'&nbsp;ปกติ<br>
                            '.$pale.'&nbsp;ซีด<br>
                            '.$cyanosis.'&nbsp;เขียวปลายมือ-เท้า<br>
                            '.$generalized_edema.'&nbsp;บวมทั่วตัว<br>
                            '.$localized_edema.'&nbsp;บวมเฉพาะที่&nbsp;<U><I>'.htmlspecialchars($localized_edema_text).'</I></U><br>
                            '.$pitting_edema.'&nbsp;บวมกดบุ๋ม&nbsp;<U><I>'.htmlspecialchars($pitting_edema_text).'</I></U><br>
                            '.$circulation_orther.'&nbsp;อื่นๆ&nbsp;<U><I>'.htmlspecialchars($circulation_orther_text).'</I></U>
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" valign="top">
                            '.$normal_skin.'&nbsp;ปกติ<br>
                            '.$dry.'&nbsp;แห้งแตก<br>
                            '.$bruise.'&nbsp;บาง ช้ำ หลุดลอกง่าย<br>
                            '.$erythema.'&nbsp;ผื่นแดง<br>
                            '.$abscess.'&nbsp;แผล ฝี<br>
                            '.$joudice.'&nbsp;เหลือง<br>
                            '.$skin_other.'&nbsp;อื่นๆ&nbsp;<U><I>'.htmlspecialchars($skin_other_text).'</I></U>
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" valign="top">
                            '.$pain_1.'&nbsp;ไม่มี<br>
                            '.$pain_2.'&nbsp;มี บริเวณ&nbsp;<U><I>'.htmlspecialchars($location).'</I></U><br>
                            <B>ลักษณะการเจ็บปวด</B><br>
                            '.$pain_charac_1.'&nbsp;ครั้งคราว<br>
                            '.$pain_charac_2.'&nbsp;ตลอดเวลา<br>
                            '.$pain_charac_3.'&nbsp;อื่นๆ&nbsp;<U><I>'.htmlspecialchars($pain_charac_text).'</I></U><br>
                            &nbsp;Pain Score&nbsp;<U><I>'.htmlspecialchars($pain_score).'</I></U>&nbsp;คะแนน<br>
                        </td>
                    </tr>
                    <tr style="border:1px solid #000;margin: 45px;">
                        <td style="border-right:0.5px solid #000;padding:2px; text-align:center; background-color:#C0C0C0;" colspan="4"><B>สภาพจิตใจแรกรับ</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px; text-align:center; background-color:#C0C0C0;" colspan="2"><B>สภาพสังคมและเศรษฐานะ</B></td>
                    </tr>
                    <tr style="border:1px solid #000;margin: 45px;">
                        <td style="border-right:0.5px solid #000;padding:2px; text-align:center; background-color:#C0C0C0;" colspan="1"><B>ด้านพฤติกรรม</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px; text-align:center; background-color:#C0C0C0;" colspan="1"><B>ด้านอารมณ์</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px; text-align:center; background-color:#C0C0C0;" colspan="1"><B>ความกังวลใจ</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px; text-align:center; background-color:#C0C0C0;" colspan="1"><B>ความต้องการด้านจิตวิญญาณ</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px; text-align:center; background-color:#C0C0C0;" colspan="2"><B>การศึกษา</B></td>
                    </tr>
                    <tr style="border:1px solid #000;margin: 45px;">
                        <td style="border-right:0.5px solid #000;padding:2px;" valign="top">
                            '.$normal_behav.'&nbsp;ร่วมมือดี<br>
                            '.$agitate.'&nbsp;กระวนกระวาย<br>
                            '.$aggressive.'&nbsp;ก้าวร้าว<br>
                            '.$depression.'&nbsp;ซึมเศร้า<br>
                            '.$madness.'&nbsp;เอะอะโวยวาย<br>
                            '.$behaviour_other.'&nbsp;อื่นๆ&nbsp;<U><I>'.htmlspecialchars($behaviour_other_text).'</I></U>
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" valign="top">
                            '.$normal_emotional.'&nbsp;สงบ<br>
                            '.$angry.'&nbsp;โกรธ<br>
                            '.$moody.'&nbsp;หงุดหงิด<br>
                            '.$anxiety.'&nbsp;กังวลใจ<br>
                            '.$fear.'&nbsp;หวาดกลัว<br>
                            '.$emotional_other.'&nbsp;อื่นๆ&nbsp;<U><I>'.htmlspecialchars($emotional_other_text).'</I></U>
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" valign="top">
                            '.$no_anxiety.'&nbsp;ปฎิเสธ<br>
                            '.$study.'&nbsp;การเรียน<br>
                            '.$family.'&nbsp;ครอบครัว<br>
                            '.$economy.'&nbsp;ค่าใช้จ่าย<br>
                            '.$habitation.'&nbsp;ที่อยู่อาศัย<br>
                            '.$illness.'&nbsp;ความเจ็บป่วย
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" valign="top">
                            '.$spiritual_no.'&nbsp;ไม่ต้องการ<br>
                            '.$spiritual_back_home.'&nbsp;บ่นอยากกลับบ้านมาก<br>
                            '.$spiritual_need_family.'&nbsp;ถามถึงบุคคลในครอบครัวบ่อยๆ<br>
                            '.$spiritual_other.'&nbsp;อื่นๆ&nbsp;<U><I>'.htmlspecialchars($spiritual_other_text).'</I></U><br>
                            '.$spiritual_cant_rated.'&nbsp;ประเมินไม่ได้&nbsp;<U><I>'.htmlspecialchars($spiritual_cant_rated_text).'</I></U>
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="2" valign="top">
                            '.$education_1.'&nbsp;ไม่ได้รับ/ยังไม่ได้รับ&nbsp;
                            '.$education_2.'&nbsp;ได้รับ(ระบุ)&nbsp;<U><I>'.htmlspecialchars($education_result).'</I></U><br>
                            <B>อาชีพ(ระบุ)</B>&nbsp;<U><I>'.htmlspecialchars($occupation).'</I></U><br>
                            <B>รายได้</B> '.$income_1.'&nbsp;เพียงพอ '.$income_2.'&nbsp;ไม่เพียงพอ<br>
                            <B>ผู้ให้ความช่วยเหลือดูแล</B><br>
                            '.$self.'&nbsp;ตนเอง '.$person_family.'&nbsp;บุคคลในครอบครัว '.$neighbor.'&nbsp;เพื่อนบ้าน<br>
                            '.$assistant_other.'&nbsp;อื่นๆ&nbsp;<U><I>'.htmlspecialchars($assistant_other_text).'</I></U><br>
                        </td>
                    </tr>
                    <tr style="border:1px solid #000;margin: 45px;">
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="4">
                            '.$no_mental_state.'&nbsp;ประเมินสภาพจิตใจไม่ได้เนื่องจาก&nbsp;&nbsp;<U><I>'.htmlspecialchars($no_mental_state_text).'</I></U>
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="2">
                            อาชีพผู้ดูแล(ระบุ)&nbsp;<U><I>'.htmlspecialchars($assistant_occupation).'</I></U>
                        </td>
                    </tr>
                    <tr style="border:1px solid #000;margin: 45px;">
                        <td style="border-right:0.5px solid #000;padding:2px; text-align:center; background-color:#C0C0C0;" colspan="6"><B>แผนสุขภาพ</B></td>
                    </tr>
                    <tr style="border:1px solid #000;margin: 45px;">
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="2" rowspan="2" valign="top"><B>การรับรู้สุขภาพ และการดูแลสุขภาพ</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top"><B>แผนสุขภาพ</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="3">
                            '.$clinic.'&nbsp;ไป รพ./คลินิก&nbsp;
                            '.$buy_medicine.'&nbsp;ซื้อยารับประทานเอง
                        </td>
                    </tr>
                    <tr style="border:1px solid #000;margin: 45px;">
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top"><B>พฤติกรรมเสี่ยง</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="3">
                            '.$no_risk.'&nbsp;ปฏิเสธ&nbsp;<br>
                            '.$smoking.'&nbsp;สูบบุหรี่
                            &nbsp;'.$smoking_detail.'<br>
                            '.$alcohol.'&nbsp;ดื่มสุรา
                            &nbsp;'.$alcohol_detail.'<br>
                            '.$medication_used.'&nbsp;ยา (ระบุ)&nbsp;'.$medication_used_detail.'
                        </td>
                    </tr>
                    <tr style="border:1px solid #000;margin: 45px;">
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="2" rowspan="2" valign="top"><B>อาหาร และการเผาผลาญอาหาร</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="4">
                            '.$diet_regular_1.'&nbsp;อาหารทั่วไป&nbsp;
                            '.$diet_regular_2.'&nbsp;อาหารเฉพาะโรค (ระบุ) <U><I>'.htmlspecialchars($diet_spec).'</I></U>
                        </td>
                    </tr>
                    <tr style="border:1px solid #000;margin: 45px;">
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="4">
                            &nbsp;ปัญหาการรับประทานอาหาร&nbsp;
                            '.$nutrition_risk.'&nbsp;ไม่มี&nbsp;
                            '.$loss_appetite.'&nbsp;เบื่ออาหาร&nbsp;
                            '.$dysphagia.'&nbsp;เคี้ยว/กลืนลำบาก&nbsp;
                            '.$loss_gustation.'&nbsp;ไม่รู้รสกลิ่น&nbsp;
                            '.$denture.'&nbsp;ใส่ฟันปลอม&nbsp;<br>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$nutrition_risk_other.'&nbsp;อื่นๆ (ระบุ)&nbsp;<U><I>'.htmlspecialchars($nutrition_risk_other_text).'</I></U>
                        </td>
                    </tr>
                    <tr style="border:1px solid #000;margin: 45px;">
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="2" rowspan="2" valign="top"><B>การขับถ่าย</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top"><B>ปัสสาวะ</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="3">
                            '.$normal_urine.'&nbsp;ปกติ&nbsp;
                            '.$dysuria.'&nbsp;แสบขัด&nbsp;
                            '.$incontinence.'&nbsp;กลั้นไม่ได้&nbsp;
                            '.$staining.'&nbsp;ลำบาก&nbsp;
                            '.$hematuria.'&nbsp;เป็นเลือด&nbsp;
                            '.$catheter.'&nbsp;สายสวนปัสสาวะ
                        </td>
                    </tr>
                    <tr style="border:1px solid #000;margin: 45px;">
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="1" valign="top"><B>อุจจาระ</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="3">
                            '.$normal_feces.'&nbsp;ปกติ&nbsp;
                            '.$constipation.'&nbsp;ท้องผูก&nbsp;
                            '.$diarrhea.'&nbsp;ท้องเสียบ่อย&nbsp;
                            '.$bowel_incontinence.'&nbsp;กลั้นไม่ได้&nbsp;
                            '.$hemorrhoid.'ริดสีดวงทวาร&nbsp;
                            '.$colostomy.'ถ่ายทางหน้าท้อง
                        </td>
                    </tr>
                    <tr style="border:1px solid #000;margin: 45px;">
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="2" valign="top"><B>กิจกรรมและออกกำลังกาย</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="4">
                            '.$activity1.'&nbsp;ทำได้เอง&nbsp;
                            '.$activity2.'&nbsp;ต้องมีคนช่วย&nbsp;
                            '.$activity3.'&nbsp;ทำเองไม่ได้&nbsp;
                            '.$activity4.'&nbsp;ใช้กายอุปกรณ์&nbsp;<U><I>'.htmlspecialchars($o_p_use).'</I></U>
                        </td>
                    </tr>
                    <tr style="border:1px solid #000;margin: 45px;">
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="2" rowspan="2" valign="top"><B>การพักผ่อนนอนหลับ</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="4">
                            '.$sleep_per_day.'&nbsp;วันละ&nbsp;
                            <U><I>'.htmlspecialchars($sleep_hour).'</I></U>&nbsp;ชม.&nbsp;
                            '.$sleep_problems.'&nbsp;ปัญหาการนอน (ระบุ)&nbsp;<U><I>'.htmlspecialchars($sleep_problems_detail).'</I></U>
                        </td>
                    </tr>
                    <tr style="border:1px solid #000;margin: 45px;">
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="4">
                            &nbsp;การใช้ยานอนหลับ&nbsp;
                            '.$sleep_med_name_1.'&nbsp;ไม่เคย&nbsp;
                            '.$sleep_med_name_2.'&nbsp;เป็นครั้งคราว&nbsp;
                            '.$sleep_med_name_3.'&nbsp;เป็นประจำ ยา (ระบุ)&nbsp;<U><I>'.htmlspecialchars($sleep_med_name_detail).'</I></U>
                        </td>
                    </tr>
                    <tr style="border:1px solid #000;margin: 45px;">
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="2" rowspan="5" valign="top"><B>สติปัญญาและการรับรู้</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="1"><B>การรับรู้</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="3">
                            '.$cognitive_1.'&nbsp;ตรง&nbsp;
                            '.$cognitive_2.'&nbsp;ไม่ตรง
                        </td>
                    </tr>
                    <tr style="border:1px solid #000;margin: 45px;">
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="1"><B>ความจำ</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="3">
                            '.$memory_1.'&nbsp;ปกติ&nbsp;
                            '.$memory_2.'&nbsp;ผิดปกติ (ระบุ)&nbsp;<U><I>'.htmlspecialchars($memory_detail).'</I></U>
                        </td>
                    </tr>
                    <tr style="border:1px solid #000;margin: 45px;">
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="1"><B>การได้ยิน</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="3">
                            '.$hearing_1.'&nbsp;ปกติ&nbsp;
                            '.$hearing_2.'&nbsp;ผิดปกติ (ระบุ)&nbsp;<U><I>'.htmlspecialchars($hearing_detail).'</I></U>
                            '.$eartone.'&nbsp;ใช้เครื่องช่วยฟัง
                        </td>
                    </tr>
                    <tr style="border:1px solid #000;margin: 45px;">
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="1"><B>การมองเห็น</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="3">
                            '.$vision_1.'&nbsp;ปกติ&nbsp;
                            '.$vision_2.'&nbsp;ผิดปกติ (ระบุ)&nbsp;<U><I>'.htmlspecialchars($vision_detail).'</I></U>
                            '.$vision_eyeglasses.'&nbsp;แว่นตา&nbsp;
                            '.$vision_contactlens.'&nbsp;คอนแทคเลนส์
                        </td>
                    </tr>
                    <tr style="border:1px solid #000;margin: 45px;">
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="1"><B>การพูด</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="3">
                            '.$speech_1.'&nbsp;ปกติ&nbsp;
                            '.$speech_2.'&nbsp;ผิดปกติ (ระบุ)&nbsp;<U><I>'.htmlspecialchars($speech_detail).'</I></U>
                        </td>
                    </tr>
                    <tr style="border:1px solid #000;margin: 45px;">
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="2" valign="top"><B>การรับรู้ตนเองและอัตมโนทัศน์</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="4">
                            กระทบต่อภาพลักษณ์&nbsp;
                            '.$self_image_1.'&nbsp;ไม่มี&nbsp;
                            '.$self_image_2.'&nbsp;มี&nbsp;<U><I>'.htmlspecialchars($self_image_detail).'</I></U>
                            กระทบต่อความสามารถ&nbsp;&nbsp;
                            '.$self_activity_1.'&nbsp;ไม่มี&nbsp;
                            '.$self_activity_2.'&nbsp;มี&nbsp;<U><I>'.htmlspecialchars($self_activity_detail).'</I></U>
                        </td>
                    </tr>
                    <tr style="border:1px solid #000;margin: 45px;">
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="2" valign="top"><B>บทบาทและสัมพันธภาพ</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="4">
                            ความเจ็บป่วยมีผลกระทบ&nbsp;
                            '.$sickness_effect_1.'&nbsp;ไม่มี&nbsp;
                            '.$sickness_effect_2.'&nbsp;มีผลกระทบต่อ&nbsp;
                            '.$sickness_family.'&nbsp;ครอบครัว&nbsp;
                            '.$sickness_occupation.'&nbsp;อาชีพ&nbsp;
                            '.$sickness_education.'&nbsp;การศึกษา&nbsp;
                            '.$sickness_other.'&nbsp;อื่นๆ&nbsp;<U><I>'.htmlspecialchars($sickness_other_text).'</I></U>
                        </td>
                    </tr>
                    <tr style="border:1px solid #000;margin: 45px;">
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="2" rowspan="2" valign="top"><B>เพศและการเจริญพันธุ์</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="1"  valign="top"><B>ประจำเดือน</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="3">
                            '.$period_1.'&nbsp;ยังไม่มี&nbsp;<br>
                            '.$period_2.'&nbsp;มี&nbsp;
                            '.$period_normal_1.'&nbsp;ปกติ&nbsp;
                            '.$period_normal_2.'&nbsp;ผิดปกติ&nbsp;<U><I>'.htmlspecialchars($period_disorders).'</I></U>
                            '.$period_normal_3.'&nbsp;LMP&nbsp;<U><I>'.htmlspecialchars($period_lmp).'</I></U><br>
                            '.$period_3.'&nbsp;หมดประจำเดือน เมื่ออายุ&nbsp;&nbsp;<U><I>'.htmlspecialchars($period_menopause).'</I></U>  ปี

                        </td>
                    </tr>
                    <tr style="border:1px solid #000;margin: 45px;">
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="1"><B>เต้านม</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="3">
                            '.$breast_1.'&nbsp;ปกติ&nbsp;
                            '.$breast_2.'&nbsp;ผิดปกติ(ระบุ)&nbsp;<U><I>'.htmlspecialchars($breast_disorders).'</I></U>
                        </td>
                    </tr>
                    <tr style="border:1px solid #000;margin: 45px;">
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="2" valign="top"><B>การปรับตัวและทนต่อความเคลียด</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="4">
                        วิธีแก้ไขความไม่สบายใจ/กังวล/เคลียด/อื่นๆ&nbsp;
                            '.$consult.'&nbsp;ปรึกษา&nbsp;
                            '.$seclude.'&nbsp;แยกตัว&nbsp;
                            '.$medication.'&nbsp;ใช้ยา&nbsp;<U><I>'.htmlspecialchars($medication_detail).'</I></U><br>
                            '.$religion.'&nbsp;ศาสนา&nbsp;
                            '.$coping_stress_other.'&nbsp;อื่นๆ&nbsp;<U><I>'.htmlspecialchars($coping_stress_other_detail).'</I></U>
                        </td>
                    </tr>
                    <tr style="border:1px solid #000;margin: 45px;">
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="2" rowspan="2" valign="top"><B>คุณค่าและความเชื่อ</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="4">
                            เชื่อว่าการเจ็บป่วยครั้งนี้มีสาเหตุจาก&nbsp;
                            '.$belief_sickness_behave.'&nbsp;ไปปฏิบัติตัวไม่ถูกต้อง&nbsp;
                            '.$belief_sickness_age.'&nbsp;ตามวัย&nbsp;
                            '.$belief_sickness_destiny.'&nbsp;เคราะห์กรรม&nbsp;
                            '.$belief_sickness_other.'&nbsp;อื่นๆ&nbsp;<U><I>'.htmlspecialchars($belief_sickness_other_text).'</I></U>
                        </td>
                    </tr>
                    <tr style="border:1px solid #000;margin: 45px;">
                        <td style="border-right:0.5px solid #000;padding:2px;" colspan="4">
                            สิ่งยึดเหนี่ยวด้านจิตใจ&nbsp;
                            '.$belief_believe_1.'&nbsp;ไม่มี&nbsp;
                            '.$belief_believe_2.'&nbsp;มี&nbsp;<U><I>'.htmlspecialchars($belief_believe_text).'</I></U><br>
                            ความต้องการปฏิบัติกิจกรรมทางศาสนา&nbsp;
                            '.$religious_activity_1.'&nbsp;ไม่ต้องการ&nbsp;
                            '.$religious_activity_2.'&nbsp;ต้องการ (ระบุ)&nbsp;<U><I>'.htmlspecialchars($religious_activity_text).'</I></U>
                        </td>
                    </tr>
                    <tr style="border:1px solid #000;margin: 45px;">
                        <td style="border-right:0.5px solid #000;padding:2px; background-color:#C0C0C0;" colspan="2" valign="top"><B>ข้อมูลที่ให้ขณะแรกรับ</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px; background-color:#C0C0C0;" colspan="4">
                            โรคและอาการปัจจุบัน, แพทย์ผู้ดูแล, แนวทางการรักษาพยาบาล, สิทธิการรักษา, การลงนามยินยอม<br>
                            อาคารสถานที่, การปฏิบัติตัวขณะเข้ารับการรักษา, กฎระเบียบการเยี่ยม, การติดต่อสอบถาม
                        </td>
                    </tr>
                    <tr style="border:0px solid #000;margin: 45px;">
                        <td style="border-right:0.5px solid #000;padding:2px; text-align:right;" colspan="6"><br>
                            '.$name_full.' / '.$entryposition.'
                        </td>
                    </tr>
                </table>
        ';
        $mpdf->WriteHTML($head);
        $mpdf->Output();
?>