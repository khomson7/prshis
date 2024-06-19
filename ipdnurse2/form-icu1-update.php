<?php
        //เวลาตาม timezone
        date_default_timezone_set("Asia/Bangkok");
        require_once '../include/Session.php';
        Session::checkLoginSessionAndShowMessage(); //เช็ค session

        require_once '../include/DbUtils.php';
        require_once '../include/KphisQueryUtils.php';

        $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
       // $an = '660005698';
        $an = $_REQUEST['an'];
        $hn = KphisQueryUtils::getHnByAn($an);   // function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา
        $id = $_REQUEST['id'];

        $rxdate = empty($_REQUEST['rxdate']) ? null : $_REQUEST['rxdate'];
        $rxtime = empty($_REQUEST['rxtime']) ? null : $_REQUEST['rxtime'];
        $from_dep =  empty($_REQUEST['from_dep']) ? null : $_REQUEST['from_dep'];
        $heart_disease_history=  empty($_REQUEST['heart_disease_history']) ? null : $_REQUEST['heart_disease_history'];
        $skin=  empty($_REQUEST['skin']) ? null : $_REQUEST['skin'];
        $neck_vien_engorement=  empty($_REQUEST['neck_vien_engorement']) ? null : $_REQUEST['neck_vien_engorement'];
        $listen_to_the_heart=  empty($_REQUEST['listen_to_the_heart']) ? null : $_REQUEST['listen_to_the_heart'];
        $bt =  empty($_REQUEST['bt']) ? null : $_REQUEST['bt'];
        $pr =  empty($_REQUEST['pr']) ? null : $_REQUEST['pr'];
        $rr =  empty($_REQUEST['rr']) ? null : $_REQUEST['rr'];
        $bps =  empty($_REQUEST['bps']) ? null : $_REQUEST['bps'];
        $bpd =  empty($_REQUEST['bpd']) ? null : $_REQUEST['bpd'];
        $bpd =  empty($_REQUEST['bpd']) ? null : $_REQUEST['bpd'];
        $cbc =  empty($_REQUEST['cbc']) ? null : $_REQUEST['cbc'];
        $hct =  empty($_REQUEST['hct']) ? null : $_REQUEST['hct'];
        $hb =  empty($_REQUEST['hb']) ? null : $_REQUEST['hb'];
        $plt =  empty($_REQUEST['plt']) ? null : $_REQUEST['plt'];
        $pt =  empty($_REQUEST['pt']) ? null : $_REQUEST['pt'];
        $ptt =  empty($_REQUEST['ptt']) ? null : $_REQUEST['ptt'];
        $inr =  empty($_REQUEST['inr']) ? null : $_REQUEST['inr'];
        $trop_t =  empty($_REQUEST['trop_t']) ? null : $_REQUEST['trop_t'];
        $ckmb =  empty($_REQUEST['ckmb']) ? null : $_REQUEST['ckmb'];
        $cpk =  empty($_REQUEST['cpk']) ? null : $_REQUEST['cpk'];
        $echo =  empty($_REQUEST['echo']) ? null : $_REQUEST['echo'];
        $ekg =  empty($_REQUEST['ekg']) ? null : $_REQUEST['ekg'];
        $kidney_disease_history =  empty($_REQUEST['kidney_disease_history']) ? null : $_REQUEST['kidney_disease_history'];
        $urine_characteristics =  empty($_REQUEST['urine_characteristics']) ? null : $_REQUEST['urine_characteristics'];
        $io_1 =  empty($_REQUEST['io_1']) ? null : $_REQUEST['io_1'];
        $io_2 =  empty($_REQUEST['io_2']) ? null : $_REQUEST['io_2'];
        $bun =  empty($_REQUEST['bun']) ? null : $_REQUEST['bun'];
        $cr =  empty($_REQUEST['cr']) ? null : $_REQUEST['cr'];
        $gfr =  empty($_REQUEST['gfr']) ? null : $_REQUEST['gfr'];
        $e_lyte_na =  empty($_REQUEST['e_lyte_na']) ? null : $_REQUEST['e_lyte_na'];
        $e_lyte_k =  empty($_REQUEST['e_lyte_k']) ? null : $_REQUEST['e_lyte_k'];
        $e_lyte_cl =  empty($_REQUEST['e_lyte_cl']) ? null : $_REQUEST['e_lyte_cl'];
        $e_lyte_co2 =  empty($_REQUEST['e_lyte_co2']) ? null : $_REQUEST['e_lyte_co2'];
        $e_lyte_aniengap =  empty($_REQUEST['e_lyte_aniengap']) ? null : $_REQUEST['e_lyte_aniengap'];
        $ca =  empty($_REQUEST['ca']) ? null : $_REQUEST['ca'];
        $po_4 =  empty($_REQUEST['po_4']) ? null : $_REQUEST['po_4'];
        $mg =  empty($_REQUEST['mg']) ? null : $_REQUEST['mg'];
        $dtx =  empty($_REQUEST['dtx']) ? null : $_REQUEST['dtx'];
        $urine_sr_gr =  empty($_REQUEST['urine_sr_gr']) ? null : $_REQUEST['urine_sr_gr'];
        $history_of_lung_disease =  empty($_REQUEST['history_of_lung_disease']) ? null : $_REQUEST['history_of_lung_disease'];
        $rr =  empty($_REQUEST['rr']) ? null : $_REQUEST['rr'];
        $o2_sat =  empty($_REQUEST['o2_sat']) ? null : $_REQUEST['o2_sat'];
        $et_other =  empty($_REQUEST['et_other']) ? null : $_REQUEST['et_other'];
        $et_tube_no =  empty($_REQUEST['et_tube_no']) ? null : $_REQUEST['et_tube_no'];
        $et_tube_no2 =  empty($_REQUEST['et_tube_no2']) ? null : $_REQUEST['et_tube_no2'];
        $o2_hfnc =  empty($_REQUEST['o2_hfnc']) ? null : $_REQUEST['o2_hfnc'];
        $candular =  empty($_REQUEST['candular']) ? null : $_REQUEST['candular'];
        $mark_c_bag =  empty($_REQUEST['mark_c_bag']) ? null : $_REQUEST['mark_c_bag'];
        $breathing_characteristics =  empty($_REQUEST['breathing_characteristics']) ? null : $_REQUEST['breathing_characteristics'];
        $on_icd =  empty($_REQUEST['on_icd']) ? null : $_REQUEST['on_icd'];
        $on_icd_2 =  empty($_REQUEST['on_icd_2']) ? null : $_REQUEST['on_icd_2'];
        $listen_sound_lungs =  empty($_REQUEST['listen_sound_lungs']) ? null : $_REQUEST['listen_sound_lungs'];
        $cxr =  empty($_REQUEST['cxr']) ? null : $_REQUEST['cxr'];
        $sputum =  empty($_REQUEST['sputum']) ? null : $_REQUEST['sputum'];
        $abg =  empty($_REQUEST['abg']) ? null : $_REQUEST['abg'];
        $pa_co2 =  empty($_REQUEST['pa_co2']) ? null : $_REQUEST['pa_co2'];
        $hco3 =  empty($_REQUEST['hco3']) ? null : $_REQUEST['hco3'];
        $pao2 =  empty($_REQUEST['pao2']) ? null : $_REQUEST['pao2'];
        $be =  empty($_REQUEST['be']) ? null : $_REQUEST['be'];
        $history_of_gastrointestinal =  empty($_REQUEST['history_of_gastrointestinal']) ? null : $_REQUEST['history_of_gastrointestinal'];
        $bw =  empty($_REQUEST['bw']) ? null : $_REQUEST['bw'];
        $hight =  empty($_REQUEST['hight']) ? null : $_REQUEST['hight'];
        $bmi =  empty($_REQUEST['bmi']) ? null : $_REQUEST['bmi'];
        $alb =  empty($_REQUEST['alb']) ? null : $_REQUEST['alb'];
        $bee =  empty($_REQUEST['bee']) ? null : $_REQUEST['bee'];
        $tee =  empty($_REQUEST['tee']) ? null : $_REQUEST['tee'];
        $spent =  empty($_REQUEST['spent']) ? null : $_REQUEST['spent'];
        $communication_history =  empty($_REQUEST['communication_history']) ? null : $_REQUEST['communication_history'];
        $speaking =  empty($_REQUEST['speaking']) ? null : $_REQUEST['speaking'];
        $communication =  empty($_REQUEST['communication']) ? null : $_REQUEST['communication'];
        $vision =  empty($_REQUEST['vision']) ? null : $_REQUEST['vision'];
        $listening =  empty($_REQUEST['listening']) ? null : $_REQUEST['listening'];
        $hearing_aids =  empty($_REQUEST['hearing_aids']) ? null : $_REQUEST['hearing_aids'];
        $history_affects_activities =  empty($_REQUEST['history_affects_activities']) ? null : $_REQUEST['history_affects_activities'];
        $daily_activities =  empty($_REQUEST['daily_activities']) ? null : $_REQUEST['daily_activities'];
        $fracture =  empty($_REQUEST['fracture']) ? null : $_REQUEST['fracture'];
        $braden_score =  empty($_REQUEST['braden_score']) ? null : $_REQUEST['braden_score'];
        $mortor_power =  empty($_REQUEST['mortor_power']) ? null : $_REQUEST['mortor_power'];
        $mass =  empty($_REQUEST['mass']) ? null : $_REQUEST['mass'];
        $history_affects_stimulation =  empty($_REQUEST['history_affects_stimulation']) ? null : $_REQUEST['history_affects_stimulation'];
        $gcs_e =  empty($_REQUEST['gcs_e']) ? null : $_REQUEST['gcs_e'];
        $gcs_v =  empty($_REQUEST['gcs_v']) ? null : $_REQUEST['gcs_v'];
        $gcs_m =  empty($_REQUEST['gcs_m']) ? null : $_REQUEST['gcs_m'];
        $pupil =  empty($_REQUEST['pupil']) ? null : $_REQUEST['pupil'];
        $pupil_rt =  empty($_REQUEST['pupil_rt']) ? null : $_REQUEST['pupil_rt'];
        $pupil_lt =  empty($_REQUEST['pupil_lt']) ? null : $_REQUEST['pupil_lt'];
        $level_of_consciousness =  empty($_REQUEST['level_of_consciousness']) ? null : $_REQUEST['level_of_consciousness'];
        $ct_brain =  empty($_REQUEST['ct_brain']) ? null : $_REQUEST['ct_brain'];
        $pain_score =  empty($_REQUEST['pain_score']) ? null : $_REQUEST['pain_score'];
        $copt =  empty($_REQUEST['copt']) ? null : $_REQUEST['copt'];
        $nrs =  empty($_REQUEST['nrs']) ? null : $_REQUEST['nrs'];
        $summary_of_the_problem =  empty($_REQUEST['summary_of_the_problem']) ? null : $_REQUEST['summary_of_the_problem'];
        $fluid_balance =  empty($_REQUEST['fluid_balance']) ? null : $_REQUEST['fluid_balance'];
        $aeration =  empty($_REQUEST['aeration']) ? null : $_REQUEST['aeration'];
        $nutrition =  empty($_REQUEST['nutrition']) ? null : $_REQUEST['nutrition'];
        $communication_problem =  empty($_REQUEST['communication_problem']) ? null : $_REQUEST['communication_problem'];
        $activity =  empty($_REQUEST['activity']) ? null : $_REQUEST['activity'];
        $stimulation =  empty($_REQUEST['stimulation']) ? null : $_REQUEST['stimulation'];

       /*


       */ 
        
        $update_datetime= date('Y-m-d H:i:s');
        $update_user = $_SESSION['loginname'];
        $version0 = $_REQUEST['version'];
        $version = $version0 + 1;

        try {
          //เรียกใช้งาน sql update
if ( $rxdate != '' && $rxtime !='' 
) {
        $output_error = '<script>
        NotificationMessage("บันทึกข้อมูลสำเร็จ", "success");     
        </script>';
$stmt = $conn->prepare("UPDATE ".DbConstant::KPHIS_DBNAME.".prs_icu_form SET hn=:hn,an=:an,rxdate=:rxdate,rxtime=:rxtime,from_dep=:from_dep
          ,heart_disease_history=:heart_disease_history,skin=:skin,neck_vien_engorement=:neck_vien_engorement,listen_to_the_heart=:listen_to_the_heart
          ,bt=:bt,pr=:pr,rr=:rr,bps=:bps,bpd=:bpd,cbc=:cbc,hct=:hct,hb=:hb,plt=:plt,pt=:pt,ptt=:ptt,inr=:inr,trop_t=:trop_t,ckmb=:ckmb
          ,cpk=:cpk,echo=:echo,ekg=:ekg,kidney_disease_history=:kidney_disease_history,urine_characteristics=:urine_characteristics,io_1=:io_1,io_2=:io_2
          ,bun=:bun,cr=:cr,gfr=:gfr,e_lyte_na=:e_lyte_na,e_lyte_k=:e_lyte_k,e_lyte_cl=:e_lyte_cl,e_lyte_co2=:e_lyte_co2,e_lyte_aniengap=:e_lyte_aniengap
          ,ca=:ca,po_4=:po_4,mg=:mg,dtx=:dtx,urine_sr_gr=:urine_sr_gr,history_of_lung_disease=:history_of_lung_disease
          ,rr=:rr,o2_sat=:o2_sat,et_tube_no=:et_tube_no,et_tube_no2=:et_tube_no2,o2_hfnc=:o2_hfnc,candular=:candular,mark_c_bag=:mark_c_bag
          ,breathing_characteristics=:breathing_characteristics,on_icd=:on_icd,on_icd_2=:on_icd_2,listen_sound_lungs=:listen_sound_lungs
          ,cxr=:cxr,sputum=:sputum,abg=:abg,pa_co2=:pa_co2,hco3=:hco3,pao2=:pao2,be=:be,history_of_gastrointestinal=:history_of_gastrointestinal
          ,bw=:bw,hight=:hight,bmi=:bmi,alb=:alb,bee=:bee,tee=:tee,spent=:spent,communication_history=:communication_history,speaking=:speaking
          ,communication=:communication,vision=:vision,listening=:listening,hearing_aids=:hearing_aids,history_affects_activities=:history_affects_activities
          ,daily_activities=:daily_activities,fracture=:fracture,braden_score=:braden_score,mortor_power=:mortor_power,mass=:mass,history_affects_stimulation=:history_affects_stimulation
          ,gcs_e=:gcs_e,gcs_v=:gcs_v,gcs_m=:gcs_m,pupil=:pupil,pupil_rt=:pupil_rt,pupil_lt=:pupil_lt,level_of_consciousness=:level_of_consciousness
          ,ct_brain=:ct_brain,pain_score=:pain_score,copt=:copt,nrs=:nrs,summary_of_the_problem=:summary_of_the_problem,fluid_balance=:fluid_balance,aeration=:aeration
          ,nutrition=:nutrition,communication_problem=:communication_problem,activity=:activity,stimulation=:stimulation
          ,et_other=:et_other,update_user=:update_user,version=:version,update_datetime=:update_datetime
          WHERE id=:id");

          //execute array
          $stmt->execute(array('id'=>$id,'hn'=>$hn,'an'=>$an,'rxdate'=>$rxdate, 'rxtime'=>$rxtime,'from_dep'=>$from_dep
          ,'heart_disease_history'=>$heart_disease_history,'skin'=>$skin,'neck_vien_engorement'=>$neck_vien_engorement,'listen_to_the_heart'=>$listen_to_the_heart
          ,'bt'=>$bt,'pr'=>$pr,'rr'=>$rr,'bps'=>$bps,'bpd'=>$bpd,'cbc'=>$cbc,'hct'=>$hct,'hb'=>$hb,'plt'=>$plt,'pt'=>$pt,'ptt'=>$ptt,'inr'=>$inr,'trop_t'=>$trop_t,'ckmb'=>$ckmb
          ,'cpk'=>$cpk,'echo'=>$echo,'ekg'=>$ekg,'kidney_disease_history'=>$kidney_disease_history,'urine_characteristics'=>$urine_characteristics,'io_1'=>$io_1,'io_2'=>$io_2
          ,'bun'=>$bun,'cr'=>$cr,'gfr'=>$gfr,'e_lyte_na'=>$e_lyte_na,'e_lyte_k'=>$e_lyte_k,'e_lyte_cl'=>$e_lyte_cl,'e_lyte_co2'=>$e_lyte_co2,'e_lyte_aniengap'=>$e_lyte_aniengap
          ,'ca'=>$ca,'po_4'=>$po_4,'mg'=>$mg,'dtx'=>$dtx,'urine_sr_gr'=>$urine_sr_gr,'history_of_lung_disease'=>$history_of_lung_disease
          ,'rr'=>$rr,'o2_sat'=>$o2_sat,'et_tube_no'=>$et_tube_no,'et_tube_no2'=>$et_tube_no2,'o2_hfnc'=>$o2_hfnc,'candular'=>$candular,'mark_c_bag'=>$mark_c_bag
          ,'breathing_characteristics'=>$breathing_characteristics,'on_icd'=>$on_icd,'on_icd_2'=>$on_icd_2,'listen_sound_lungs'=>$listen_sound_lungs
          ,'cxr'=>$cxr,'sputum'=>$sputum,'abg'=>$abg,'pa_co2'=>$pa_co2,'hco3'=>$hco3,'pao2'=>$pao2,'be'=>$be,'history_of_gastrointestinal'=>$history_of_gastrointestinal
          ,'bw'=>$bw,'hight'=>$hight,'bmi'=>$bmi,'alb'=>$alb,'bee'=>$bee,'tee'=>$tee,'spent'=>$spent,'communication_history'=>$communication_history,'speaking'=>$speaking
          ,'communication'=>$communication,'vision'=>$vision,'listening'=>$listening,'hearing_aids'=>$hearing_aids,'history_affects_activities'=>$history_affects_activities
          ,'daily_activities'=>$daily_activities,'fracture'=>$fracture,'braden_score'=>$braden_score,'mortor_power'=>$mortor_power,'mass'=>$mass,'history_affects_stimulation'=>$history_affects_stimulation
          ,'gcs_e'=>$gcs_e,'gcs_v'=>$gcs_v,'gcs_m'=>$gcs_m,'pupil'=>$pupil,'pupil_rt'=>$pupil_rt,'pupil_lt'=>$pupil_lt,'level_of_consciousness'=>$level_of_consciousness
          ,'ct_brain'=>$ct_brain,'pain_score'=>$pain_score,'copt'=>$copt,'nrs'=>$nrs,'summary_of_the_problem'=>$summary_of_the_problem,'fluid_balance'=>$fluid_balance,'aeration'=>$aeration
          ,'nutrition'=>$nutrition,'communication_problem'=>$communication_problem,'activity'=>$activity,'stimulation'=>$stimulation
          ,'et_other'=>$et_other,'update_user'=>$update_user,'version'=>$version,'update_datetime' => $update_datetime
          ));


          

            Session::insertSystemAccessLog(json_encode(array(
                'form'=>'PRE-NURSENOTE-FORM',
                'action'=>'UPDATE',
                'version'=>$version,
                'an'=>$an,
            ),JSON_UNESCAPED_UNICODE));


} else {

       echo   '<script>
        alert("กรุณากรอกข้อมูลให้ครบถ้วน", "error");     
        </script>'; 
}

          


     /*     Session::insertSystemAccessLog(json_encode(array(
            'form'=>'LR-REPORT1-FORM',
            'action'=>'UPDATE',
            'version'=>$version,
            'an'=>$an,
        ),JSON_UNESCAPED_UNICODE));
*/

        

        }catch (PDOException  $e) {
//เมื่อเกิดข้อผิดพลาด
echo $e->getMessage();
//$output_error = '<div class="alert alert-danger">ERROR !!</div>';

$output_error = '<script>NotificationMessage("บันทึกข้อมูลไม่สำเร็จ", "error")</script>';

        }

        echo $output_error;


        ?>
        