<?php   require_once '../include/Session.php';
        Session::checkLoginSessionAndShowMessage(); //เช็ค session
        if(!(
                Session::checkPermission('ADMISSION_NOTE','ADD')
            )){
            return;
        }
        require_once '../include/DbUtils.php';
        require_once '../include/KphisQueryUtils.php';

        $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล

        $an = $_REQUEST['an'];
        $hn = $_REQUEST['hn'];
        $receiver_medication_date = $_REQUEST['receiver_medication_date'];
        $receiver_medication_time = $_REQUEST['receiver_medication_time'];
        $take_medication_by = $_REQUEST['take_medication_by'];
        $arrive_by = $_REQUEST['arrive_by'];
        $taken_by_relative  = empty($_REQUEST['taken_by_relative'])? null : $_REQUEST['taken_by_relative'];
        $taken_by_nurse     = empty($_REQUEST['taken_by_nurse'])   ? null : $_REQUEST['taken_by_nurse'];
        $taken_by_crib      = empty($_REQUEST['taken_by_crib'])    ? null : $_REQUEST['taken_by_crib'];
        $taken_by_etc       = empty($_REQUEST['taken_by_etc'])     ? null : $_REQUEST['taken_by_etc'];
        $taken_by           = empty($_REQUEST['taken_by'])         ? null : $_REQUEST['taken_by'];
        $informant_patient   = empty($_REQUEST['informant_patient'])  ? null : $_REQUEST['informant_patient'];
        $informant_relatives = empty($_REQUEST['informant_relatives'])? null : $_REQUEST['informant_relatives'];
        $informant_deliverer = empty($_REQUEST['informant_deliverer'])? null : $_REQUEST['informant_deliverer'];
        $informant_etc = empty($_REQUEST['informant_etc'])? null : $_REQUEST['informant_etc'];
        $chief_complaints = $_REQUEST['chief_complaints'];
        $medical_history  = $_REQUEST['medical_history'];
        $bp = empty($_REQUEST['bp']) ? null : $_REQUEST['bp'];
        $t  = empty($_REQUEST['t']) ? null : $_REQUEST['t'];
        $pr = empty($_REQUEST['pr']) ? null : $_REQUEST['pr'];
        $rr = empty($_REQUEST['rr']) ? null : $_REQUEST['rr'];
        $gcs = empty($_REQUEST['gcs']) ? null : $_REQUEST['gcs'];
        $e = empty($_REQUEST['e']) ? null : $_REQUEST['e'];
        $v = empty($_REQUEST['v']) ? null : $_REQUEST['v'];
        $m = empty($_REQUEST['m']) ? null : $_REQUEST['m'];
        $braden_scale = empty($_REQUEST['braden_scale']) ? null : $_REQUEST['braden_scale'];
        $disease = empty($_REQUEST['disease']) ? null : $_REQUEST['disease'];
        $disease_detail = empty($_REQUEST['disease_detail']) ? null : $_REQUEST['disease_detail'];
        $disease_etc = empty($_REQUEST['disease_etc']) ? null : $_REQUEST['disease_etc'];
        $operation_history = empty($_REQUEST['operation_history'])? null : $_REQUEST['operation_history'];
        $allergy_history = $_REQUEST['allergy_history'];
        $allergy_drug_history_hosxp = $_REQUEST['allergy_drug_history_hosxp'];
        $allergy_drug_history = empty($_REQUEST['allergy_drug_history']) ? null : $_REQUEST['allergy_drug_history'];
        $allergy_food_history = empty($_REQUEST['allergy_food_history']) ? null : $_REQUEST['allergy_food_history'];
        $allergy_etc_history = empty($_REQUEST['allergy_etc_history']) ? null : $_REQUEST['allergy_etc_history'];
        $allergy_detail = empty($_REQUEST['allergy_detail']) ? null : $_REQUEST['allergy_detail'];
        $family_medical_history = empty($_REQUEST['family_medical_history']) ? null : $_REQUEST['family_medical_history'];
        $family_medical_history_detail = empty($_REQUEST['family_medical_history_detail']) ? null : $_REQUEST['family_medical_history_detail'];
        $receives_immunisation_history_kid  = empty($_REQUEST['receives_immunisation_history_kid']) ? null : $_REQUEST['receives_immunisation_history_kid'];
        if($receives_immunisation_history_kid == 'ครบตามวัย'){
            $receives_immunisation_history_kid  = $_REQUEST['receives_immunisation_history_kid'];
        }else{
            $receives_immunisation_history_kid  = empty($_REQUEST['receives_immunisation_history_kid_text']) ? null : $_REQUEST['receives_immunisation_history_kid_text'];
        }
        $developmentally_kid  = empty($_REQUEST['developmentally_kid']) ? null : $_REQUEST['developmentally_kid'];
        if($developmentally_kid == 'ปกติ'){
            $developmentally_kid  = $_REQUEST['developmentally_kid'];
        }else{
            $developmentally_kid  = empty($_REQUEST['developmentally_kid_text']) ? null : $_REQUEST['developmentally_kid_text'];
        }
        $g = StringUtils::isBlankOrNull($_REQUEST['g']) ? null : $_REQUEST['g'];
        $p = StringUtils::isBlankOrNull($_REQUEST['p']) ? null : $_REQUEST['p'];
        $anc = $_REQUEST['anc'];
        $tt = empty($_REQUEST['tt']) ? null : $_REQUEST['tt'];
        $gestational_age = $_REQUEST['gestational_age'];
        $gestational_day = empty($_REQUEST['gestational_day']) ? null : $_REQUEST['gestational_day'];
        $last_child = empty($_REQUEST['last_child']) ? null : $_REQUEST['last_child'];
        $last_abort = empty($_REQUEST['last_abort']) ? null : $_REQUEST['last_abort'];
        $curette = empty($_REQUEST['curette']) ? null : $_REQUEST['curette'];
        $lmp = empty($_REQUEST['lmp']) ? null : $_REQUEST['lmp'];
        $edc = empty($_REQUEST['edc']) ? null : $_REQUEST['edc'];
        $pb_no = empty($_REQUEST['pb_no']) ? null : $_REQUEST['pb_no'];
        $giant_baby = empty($_REQUEST['giant_baby']) ? null : $_REQUEST['giant_baby'];
        $distocia = empty($_REQUEST['distocia']) ? null : $_REQUEST['distocia'];
        $extraction = empty($_REQUEST['extraction']) ? null : $_REQUEST['extraction'];
        $pph = empty($_REQUEST['pph']) ? null : $_REQUEST['pph'];
        $pb_etc = empty($_REQUEST['pb_etc']) ? null : $_REQUEST['pb_etc'];
        $hf = empty($_REQUEST['hf']) ? null : $_REQUEST['hf'];
        $hf_position = empty($_REQUEST['hf_position']) ? null : $_REQUEST['hf_position'];
        $hiv = empty($_REQUEST['hiv']) ? null : $_REQUEST['hiv'];
        $vdrl = empty($_REQUEST['vdrl']) ? null : $_REQUEST['vdrl'];
        $hbs_ag = empty($_REQUEST['hbs_ag']) ? null : $_REQUEST['hbs_ag'];
        $hct = empty($_REQUEST['hct']) ? null : $_REQUEST['hct'];
        $hiv2 = empty($_REQUEST['hiv2']) ? null : $_REQUEST['hiv2'];
        $vdrl2 = empty($_REQUEST['vdrl2']) ? null : $_REQUEST['vdrl2'];
        $hbs_ag2 = empty($_REQUEST['hbs_ag2']) ? null : $_REQUEST['hbs_ag2'];
        $hct2 = empty($_REQUEST['hct2']) ? null : $_REQUEST['hct2'];
        $gr = empty($_REQUEST['gr']) ? null : $_REQUEST['gr'];
        $thalassemia = empty($_REQUEST['thalassemia']) ? null : $_REQUEST['thalassemia'];
        $husband = empty($_REQUEST['husband']) ? null : $_REQUEST['husband'];
        $condition_pregnant  = empty($_REQUEST['condition_pregnant']) ? null : $_REQUEST['condition_pregnant'];
        if($condition_pregnant == 'ปกติ'){
            $condition_pregnant  = $_REQUEST['condition_pregnant'];
        }else{
            $condition_pregnant  = empty($_REQUEST['condition_pregnant_text']) ? null : $_REQUEST['condition_pregnant_text'];
        }
        $deliver_anomalies  = empty($_REQUEST['deliver_anomalies']) ? null : $_REQUEST['deliver_anomalies'];
        if($deliver_anomalies == 'ปกติ'){
            $deliver_anomalies  = $_REQUEST['deliver_anomalies'];
        }else{
            $deliver_anomalies  = empty($_REQUEST['deliver_anomalies_text']) ? null : $_REQUEST['deliver_anomalies_text'];
        }
        $deliver_anomalies_means = empty($_REQUEST['deliver_anomalies_means']) ? null : $_REQUEST['deliver_anomalies_means'];
        $deliver_location = empty($_REQUEST['deliver_location']) ? null : $_REQUEST['deliver_location'];
        $deliver_first_weight = empty($_REQUEST['deliver_first_weight']) ? null : $_REQUEST['deliver_first_weight'];
        $deliver_first_health = empty($_REQUEST['deliver_first_health']) ? null : $_REQUEST['deliver_first_health'];
        $fant_breast_feeding_end_age_month = empty($_REQUEST['fant_breast_feeding_end_age_month']) ? null : $_REQUEST['fant_breast_feeding_end_age_month'];
        $fant_artificial_feeding_start_age_month = empty($_REQUEST['fant_artificial_feeding_start_age_month']) ? null : $_REQUEST['fant_artificial_feeding_start_age_month'];
        $fant_feeding_etc = $_REQUEST['fant_feeding_etc'];
        $supplementary_feeding = empty($_REQUEST['supplementary_feeding']) ? null : $_REQUEST['supplementary_feeding'];
        $supplementary_feeding_start_age_month = empty($_REQUEST['supplementary_feeding_start_age_month']) ? null : $_REQUEST['supplementary_feeding_start_age_month'];
        $disease_operation_allergy = empty($_REQUEST['disease_operation_allergy']) ? null : $_REQUEST['disease_operation_allergy'];
        $inpatient_history = empty($_REQUEST['inpatient_history']) ? null : $_REQUEST['inpatient_history'];
        $inpatient_last_date = empty($_REQUEST['inpatient_last_date']) ? null : $_REQUEST['inpatient_last_date'];
        $inpatient_location = empty($_REQUEST['inpatient_location']) ? null : $_REQUEST['inpatient_location'];
        $inpatient_because = empty($_REQUEST['inpatient_because']) ? null : $_REQUEST['inpatient_because'];
        $pe_abdomen = $_REQUEST['pe_abdomen'];
        $pe_anus = $_REQUEST['pe_anus'];
        $pe_breastthorax = $_REQUEST['pe_breastthorax'];
        $pe_chest = $_REQUEST['pe_chest'];
        $pe_cns = $_REQUEST['pe_cns'];
        $pe_cvs = $_REQUEST['pe_cvs'];
        $pe_ears = $_REQUEST['pe_ears'];
        $pe_extremities = $_REQUEST['pe_extremities'];
        $pe_eyes = $_REQUEST['pe_eyes'];
        $pe_face = $_REQUEST['pe_face'];
        $pe_general = $_REQUEST['pe_general'];
        $pe_genitalia = $_REQUEST['pe_genitalia'];
        $pe_head = $_REQUEST['pe_head'];
        $pe_heart = $_REQUEST['pe_heart'];
        $pe_heent = $_REQUEST['pe_heent'];
        $pe_lungs = $_REQUEST['pe_lungs'];
        $pe_mouth = $_REQUEST['pe_mouth'];
        $pe_neck = $_REQUEST['pe_neck'];
        $pe_nervose = $_REQUEST['pe_nervose'];
        $pe_neurological = $_REQUEST['pe_neurological'];
        $pe_nose = $_REQUEST['pe_nose'];
        $pe_ob_gynexam = $_REQUEST['pe_ob_gynexam'];
        $pe_other = $_REQUEST['pe_other'];
        $pe_rectalgenitalia = $_REQUEST['pe_rectalgenitalia'];
        $pe_skin = $_REQUEST['pe_skin'];
        $pe_text = $_REQUEST['pe_text'];
        $pe_trunk_spine = $_REQUEST['pe_trunk_spine'];
        $svg_tag = $_REQUEST['svg_tag'];
        $impression = $_REQUEST['impression'];
        $diff_dx = $_REQUEST['diff_dx'];
        $plan_management = $_REQUEST['plan_management'];
        $create_user = $_SESSION['loginname'];
        $nurse_name = empty($_REQUEST['nurse_name']) ? null : $_REQUEST['nurse_name'];
        // $doc_name = empty($_REQUEST['doc_name']) ? null : $_REQUEST['doc_name'];
        $nurse_pos = empty($_REQUEST['nurse_pos']) ? null : $_REQUEST['nurse_pos'];
        //$doc_pos = empty($_REQUEST['doc_pos']) ? null : $_REQUEST['doc_pos'];
        // $create_datetime = $_REQUEST['create_datetime'];
        $update_user = $_SESSION['loginname'];
        // $update_datetime = $_REQUEST['update_datetime'];
        $version = 1;

        //เพิ่มรายการ
        $problem_list = $_REQUEST['problem_list'];
        $c_a = empty($_REQUEST['c_a']) ? 0 : $_REQUEST['c_a'];
        $c_anterpartum = empty($_REQUEST['c_anterpartum']) ? null : $_REQUEST['c_anterpartum'];
        $c_apgar1 = empty($_REQUEST['c_apgar1']) ? null : $_REQUEST['c_apgar1'];
        $c_apgar10 = empty($_REQUEST['c_apgar10']) ? null : $_REQUEST['c_apgar10'];
        $c_apgar5 = empty($_REQUEST['c_apgar5']) ? null : $_REQUEST['c_apgar5'];
        $c_bcg = empty($_REQUEST['c_bcg']) ? null : $_REQUEST['c_bcg'];
        $c_born_date = empty($_REQUEST['c_born_date']) ? null : $_REQUEST['c_born_date'];
        $c_born_time = empty($_REQUEST['c_born_time']) ? null : $_REQUEST['c_born_time'];
        $c_born_type_in = empty($_REQUEST['c_born_type_in']) ? null : $_REQUEST['c_born_type_in'];
        $c_bw = empty($_REQUEST['c_bw']) ? null : $_REQUEST['c_bw'];
        $c_drug_allergy = empty($_REQUEST['c_drug_allergy']) ? null : $_REQUEST['c_drug_allergy'];
        $c_g = empty($_REQUEST['c_g']) ? 0 : $_REQUEST['c_g'];
        $c_ga = empty($_REQUEST['c_ga']) ? null : $_REQUEST['c_ga'];
        $c_hbv = empty($_REQUEST['c_hbv']) ? null : $_REQUEST['c_hbv'];
        $c_hc = empty($_REQUEST['c_hc']) ? null : $_REQUEST['c_hc'];
        $c_indication = empty($_REQUEST['c_indication']) ? null : $_REQUEST['c_indication'];
        $c_inform_etc = empty($_REQUEST['c_inform_etc']) ? null : $_REQUEST['c_inform_etc'];
        $c_inform_etc_text = empty($_REQUEST['c_inform_etc_text']) ? null : $_REQUEST['c_inform_etc_text'];
        $c_inform_mother = empty($_REQUEST['c_inform_mother']) ? null : $_REQUEST['c_inform_mother'];
        $c_inform_officer = empty($_REQUEST['c_inform_officer']) ? null : $_REQUEST['c_inform_officer'];
        $c_intrapartum = empty($_REQUEST['c_intrapartum']) ? null : $_REQUEST['c_intrapartum'];
        $c_l = empty($_REQUEST['c_l']) ? 0 : $_REQUEST['c_l'];
        $c_labor_normal = empty($_REQUEST['c_labor_normal']) ? null : $_REQUEST['c_labor_normal'];
        $c_labor_type = empty($_REQUEST['c_labor_type']) ? null : $_REQUEST['c_labor_type'];
        $c_length = empty($_REQUEST['c_length']) ? null : $_REQUEST['c_length'];
        $c_pre = empty($_REQUEST['c_pre']) ? 0 : $_REQUEST['c_pre'];
        $c_serology = empty($_REQUEST['c_serology']) ? null : $_REQUEST['c_serology'];
        $c_sex = empty($_REQUEST['c_sex']) ? null : $_REQUEST['c_sex'];
        $c_tp = empty($_REQUEST['c_tp']) ? 0 : $_REQUEST['c_tp'];
        $c_vaccination_dt = empty($_REQUEST['c_vaccination_dt']) ? null : $_REQUEST['c_vaccination_dt'];
        $c_mother_his = empty($_REQUEST['c_mother_his']) ? null : $_REQUEST['c_mother_his'];


        try {
                $stmt = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".ipd_dr_admission_note
                (hn,an,receiver_medication_date,receiver_medication_time,take_medication_by,
                arrive_by,taken_by_relative,taken_by_nurse,taken_by_crib,taken_by_etc,taken_by,informant_patient,informant_relatives,informant_deliverer,
                informant_etc,chief_complaints,medical_history,bp,t,pr,rr,gcs,e,v,m,braden_scale,
                disease,disease_detail,disease_etc,operation_history,
                allergy_history,
                allergy_drug_history,
                allergy_drug_history_hosxp,
                allergy_food_history,allergy_etc_history,allergy_detail,
                family_medical_history,family_medical_history_detail,receives_immunisation_history_kid,developmentally_kid,g,p,anc,tt,
                gestational_age,gestational_day,last_child,last_abort,curette,lmp,edc,pb_no,giant_baby,distocia,extraction,pph,pb_etc,
                hf,hf_position,hiv,vdrl,hbs_ag,hct,hiv2,vdrl2,hbs_ag2,hct2,gr,thalassemia,husband,
                condition_pregnant,deliver_anomalies,deliver_anomalies_means,deliver_location,
                deliver_first_weight,deliver_first_health,fant_breast_feeding_end_age_month,
                fant_artificial_feeding_start_age_month,fant_feeding_etc,supplementary_feeding,
                supplementary_feeding_start_age_month,disease_operation_allergy,inpatient_history,
                inpatient_last_date,inpatient_location,inpatient_because,pe_general,pe_skin,pe_heent,
                pe_neck,pe_breastthorax,pe_heart,pe_lungs,pe_cvs,pe_cns,pe_abdomen,pe_rectalgenitalia,pe_extremities,
                pe_neurological,pe_ob_gynexam,pe_other,pe_text,svg_tag,impression,diff_dx,plan_management,problem_list,
                create_user,nurse_name,nurse_pos,update_user,create_datetime,update_datetime,version
                ,c_born_date,c_born_time,c_born_type_in,c_labor_type,c_indication,c_intrapartum
                ,c_labor_normal,c_drug_allergy,c_g,c_tp,c_pre,c_a,c_l,c_serology,c_anterpartum
                ,c_vaccination_dt,c_hbv,c_bcg,c_inform_officer,c_inform_mother,c_inform_etc,c_inform_etc_text,c_ga,c_apgar1,c_apgar5,c_apgar10,c_sex,c_bw,c_hc,c_length
                ,pe_head,pe_face,pe_ears,pe_eyes,pe_nose,pe_mouth,pe_chest,pe_genitalia,pe_anus,pe_trunk_spine,pe_nervose,c_mother_his
                )
                VALUES (:hn,:an,:receiver_medication_date,:receiver_medication_time,:take_medication_by,
                :arrive_by,:taken_by_relative,:taken_by_nurse,:taken_by_crib,:taken_by_etc,:taken_by,:informant_patient,:informant_relatives,:informant_deliverer,
                :informant_etc,:chief_complaints,:medical_history,:bp,:t,:pr,:rr,:gcs,:e,:v,:m,
                :braden_scale,:disease,:disease_detail,:disease_etc,
                :operation_history,:allergy_history,
                :allergy_drug_history,
                :allergy_drug_history_hosxp,
                :allergy_food_history,
                :allergy_etc_history,:allergy_detail,:family_medical_history,:family_medical_history_detail,:receives_immunisation_history_kid,
                :developmentally_kid,:g,:p,:anc,:tt,:gestational_age,
                :gestational_day,:last_child,:last_abort,:curette,:lmp,:edc,:pb_no,:giant_baby,:distocia,:extraction,:pph,:pb_etc,
                :hf,:hf_position,:hiv,:vdrl,:hbs_ag,:hct,:hiv2,:vdrl2,:hbs_ag2,:hct2,:gr,:thalassemia,:husband,
                :condition_pregnant,:deliver_anomalies,
                :deliver_anomalies_means,:deliver_location,:deliver_first_weight,:deliver_first_health,
                :fant_breast_feeding_end_age_month,:fant_artificial_feeding_start_age_month,:fant_feeding_etc,
                :supplementary_feeding,:supplementary_feeding_start_age_month,:disease_operation_allergy,
                :inpatient_history,:inpatient_last_date,:inpatient_location,:inpatient_because,:pe_general,
                :pe_skin,:pe_heent,:pe_neck,:pe_breastthorax,:pe_heart,:pe_lungs,:pe_cvs,:pe_cns,:pe_abdomen,:pe_rectalgenitalia,
                :pe_extremities,:pe_neurological,:pe_ob_gynexam,:pe_other,:pe_text,:svg_tag,:impression,:diff_dx,
                :plan_management,:problem_list,:create_user,:nurse_name,:nurse_pos,:update_user,now(),now(),:version
                ,:c_born_date,:c_born_time,:c_born_type_in,:c_labor_type,:c_indication,:c_intrapartum
                ,:c_labor_normal,:c_drug_allergy,:c_g,:c_tp,:c_pre,:c_a,:c_l,:c_serology,:c_anterpartum
                ,:c_vaccination_dt,:c_hbv,:c_bcg,:c_inform_officer,:c_inform_mother,:c_inform_etc,:c_inform_etc_text,:c_ga,:c_apgar1,:c_apgar5,:c_apgar10,:c_sex,:c_bw,:c_hc,:c_length   
                ,:pe_head,:pe_face,:pe_ears,:pe_eyes,:pe_nose,:pe_mouth,:pe_chest,:pe_genitalia,:pe_anus,:pe_trunk_spine,:pe_nervose,:c_mother_his
                )");
                $stmt->execute(array('hn'=>$hn, 'an'=>$an, 'receiver_medication_date'=>$receiver_medication_date,
                'receiver_medication_time'=>$receiver_medication_time, 'take_medication_by'=>$take_medication_by,
                'arrive_by'=>$arrive_by, 'taken_by_relative'=>$taken_by_relative, 'taken_by_nurse'=>$taken_by_nurse,
                'taken_by_crib'=>$taken_by_crib, 'taken_by_etc'=>$taken_by_etc, 'taken_by'=>$taken_by, 'informant_patient'=>$informant_patient,
                'informant_relatives'=>$informant_relatives, 'informant_deliverer'=>$informant_deliverer,
                'informant_etc'=>$informant_etc, 'chief_complaints'=>$chief_complaints,
                'medical_history'=>$medical_history, 'bp'=>$bp, 't'=>$t,'pr'=>$pr, 'rr'=>$rr, 'gcs'=>$gcs,
                'e'=>$e, 'v'=>$v,'m'=>$m, 'braden_scale'=>$braden_scale, 'disease'=>$disease,
                'disease_detail'=>$disease_detail,
                'disease_etc'=>$disease_etc, 'operation_history'=>$operation_history,
                'allergy_history'=>$allergy_history,
                'allergy_drug_history'=>$allergy_drug_history,
                'allergy_drug_history_hosxp'=>$allergy_drug_history_hosxp,
                'allergy_food_history'=>$allergy_food_history,'allergy_etc_history'=>$allergy_etc_history,
                'allergy_detail'=>$allergy_detail, 'family_medical_history'=>$family_medical_history, 'family_medical_history_detail'=>$family_medical_history_detail,
                'receives_immunisation_history_kid'=>$receives_immunisation_history_kid,
                'developmentally_kid'=>$developmentally_kid,'g'=>$g, 'p'=>$p, 'anc'=>$anc, 'tt'=>$tt,
                'gestational_age'=>$gestational_age,'gestational_day'=>$gestational_day,'last_child'=>$last_child,'last_abort'=>$last_abort,
                'curette'=>$curette,'lmp'=>$lmp,'edc'=>$edc,'pb_no'=>$pb_no,'giant_baby'=>$giant_baby,'distocia'=>$distocia,
                'extraction'=>$extraction,'pph'=>$pph,'pb_etc'=>$pb_etc,'hf'=>$hf,'hf_position'=>$hf_position,'hiv'=>$hiv,'vdrl'=>$vdrl,
                'hbs_ag'=>$hbs_ag,'hct'=>$hct,'hiv2'=>$hiv2,'vdrl2'=>$vdrl2,'hbs_ag2'=>$hbs_ag2,'hct2'=>$hct2,
                'gr'=>$gr,'thalassemia'=>$thalassemia,'husband'=>$husband,
                'condition_pregnant'=>$condition_pregnant,
                'deliver_anomalies'=>$deliver_anomalies, 'deliver_anomalies_means'=>$deliver_anomalies_means,
                'deliver_location'=>$deliver_location, 'deliver_first_weight'=>$deliver_first_weight,
                'deliver_first_health'=>$deliver_first_health, 'fant_breast_feeding_end_age_month'=>$fant_breast_feeding_end_age_month,
                'fant_artificial_feeding_start_age_month'=>$fant_artificial_feeding_start_age_month,
                'fant_feeding_etc'=>$fant_feeding_etc, 'supplementary_feeding'=>$supplementary_feeding,
                'supplementary_feeding_start_age_month'=>$supplementary_feeding_start_age_month,
                'disease_operation_allergy'=>$disease_operation_allergy, 'inpatient_history'=>$inpatient_history,
                'inpatient_last_date'=>$inpatient_last_date, 'inpatient_location'=>$inpatient_location,
                'inpatient_because'=>$inpatient_because, 'pe_general'=>$pe_general, 'pe_skin'=>$pe_skin,
                'pe_heent'=>$pe_heent, 'pe_neck'=>$pe_neck,'pe_breastthorax'=>$pe_breastthorax,
                'pe_heart'=>$pe_heart, 'pe_lungs'=>$pe_lungs,'pe_cvs'=>$pe_cvs, 'pe_cns'=>$pe_cns,'pe_abdomen'=>$pe_abdomen,
                'pe_rectalgenitalia'=>$pe_rectalgenitalia,'pe_extremities'=>$pe_extremities,
                'pe_neurological'=>$pe_neurological, 'pe_ob_gynexam'=>$pe_ob_gynexam,
                'pe_other'=>$pe_other, 'pe_text'=>$pe_text, 'svg_tag'=>$svg_tag,'impression'=>$impression, 'diff_dx'=>$diff_dx,
                'plan_management'=>$plan_management,'problem_list'=>$problem_list, 'create_user'=>$create_user,
                'nurse_name'=>$nurse_name,'nurse_pos'=>$nurse_pos,'update_user'=>$update_user,'version'=>$version
                ,'c_born_date'=>$c_born_date,'c_born_time'=>$c_born_time,'c_born_type_in'=>$c_born_type_in
                ,'c_labor_type'=>$c_labor_type,'c_indication'=>$c_indication,'c_intrapartum'=>$c_intrapartum
                ,'c_labor_normal'=>$c_labor_normal,'c_drug_allergy'=>$c_drug_allergy
                ,'c_g'=>$c_g,'c_tp'=>$c_tp,'c_pre'=>$c_pre,'c_a'=>$c_a,'c_l'=>$c_l
                ,'c_serology'=>$c_serology,'c_anterpartum'=>$c_anterpartum
                ,'c_vaccination_dt'=>$c_vaccination_dt,'c_hbv'=>$c_hbv,'c_bcg'=>$c_bcg,'c_inform_officer'=>$c_inform_officer
                ,'c_inform_mother'=>$c_inform_mother,'c_inform_etc'=>$c_inform_etc,'c_inform_etc_text'=>$c_inform_etc_text
                ,'c_ga'=>$c_ga,'c_apgar1'=>$c_apgar1,'c_apgar5'=>$c_apgar5,'c_apgar10'=>$c_apgar10,'c_sex'=>$c_sex,'c_bw'=>$c_bw,'c_hc'=>$c_hc,'c_length'=>$c_length
                , 'pe_head'=>$pe_head,'pe_face'=>$pe_face,'pe_ears'=>$pe_ears,'pe_eyes'=>$pe_eyes,'pe_nose'=>$pe_nose,'pe_mouth'=>$pe_mouth,'pe_chest'=>$pe_chest,'pe_genitalia'=>$pe_genitalia
                ,'pe_anus'=>$pe_anus,'pe_trunk_spine'=>$pe_trunk_spine,'pe_nervose'=>$pe_nervose,'c_mother_his'=>$c_mother_his
            ));

           

                $admission_note_id = $conn->lastInsertId();
                if(!empty($_REQUEST['admission_note_doctor'])){
                    foreach($_REQUEST['admission_note_doctor'] as $admission_note_doctor){
                        $stmt_item = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".ipd_dr_admission_note_item (admission_note_id,an,admission_note_doctor,create_user,create_datetime,update_user,update_datetime,version)
                        VALUES (:admission_note_id,:an,:admission_note_doctor,:create_user,now(),:update_user,now(),:version)");
                        $stmt_item->execute(array('admission_note_id'=>$admission_note_id, 'an'=>$an,
                        'admission_note_doctor'=>$admission_note_doctor,
                        'create_user'=>$create_user, 'update_user'=>$update_user, 'version'=>$version));
                    }
                }

                $output_error = '<div class="alert alert-success">บันทึกข้อมูลเรียบร้อยแล้วคะ</div>';

            } catch (PDOException  $e) {
                echo $e->getMessage();
                $output_error = '<div class="alert alert-danger">ERROR !!</div>';
            }
        echo $output_error;
?>