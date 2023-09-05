<?php  // require_once './project/function/Session.php';
       // Session::::checkPermissionAndShowMessage('ADMISSION_NOTE','VIEW');
       require_once '../include/Session.php';
       //ตรวจสอบว่า session login ตรงกันหรือไม่
              $login = empty($_REQUEST['loginname']) ? null : $_REQUEST['loginname'];
               $loginname = $_SESSION['loginname'];
               $values =['loginname'=>$loginname];
       
               //หากพบว่าไม่ตรงกันให้ ทำลาย session เดิมทิ้งไป
               if($login != $loginname){
                   session_start();
                   session_destroy();
                   
                       
                 }
       //ส่วนหัวหน้า
               require_once '../mains/main-report.php';
       //check session and permission  
        Session::checkLoginSessionAndShowMessage(); //เช็ค session
        Session::checkPermissionAndShowMessage('IPD_NURSE_ADDMISSION_NOTE','VIEW');
        require_once '../mains/ipd-show-patient-main.php'; //เป็นส่วนที่แสดง ข้อมูลผู้ป่วย เช่น รูป,hn,an,ชื่อ-สกุล,แพ้ยา ฯลฯ
        require_once '../mains/ipd-show-patient-sticky.php';
        require_once '../include/DbUtils.php';
        require_once '../include/KphisQueryUtils.php';

        Session::insertSystemAccessLog(json_encode(array(
            'form'=>'IPD-DR-ADMISSION-NOTE-FORM',
            'an'=>$an,
        ),JSON_UNESCAPED_UNICODE));
    
        $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
        $an = empty($_REQUEST['an']) ? null : $_REQUEST['an'];
       
        $hn = KphisQueryUtils::getHnByAn($an);
        $vn = KphisQueryUtils::getVnByAn($an);
        $an_parameters = ['an' => $an];
        $hn_parameters = ['hn' => $hn];



       

        if(isset($_POST['button1'])) {
           
            
            $sql = "SELECT  ".DbConstant::KPHIS_DBNAME.".DrAdmissionNote($an)";
            $stmt = $conn->query($sql);
            $stmt ->execute();
            
            echo $stmt->rowCount();

        }

        

        
        //-------------------------Doctor admission note
        $sql = "SELECT *
                FROM `ipd_dr_admission_note`
                WHERE an = :an";
        $stmt = $conn->prepare($sql);
        $stmt->execute($an_parameters);
        if($row  = $stmt->fetch()){
            $admission_note_id = $row['admission_note_id'];
        } else {
            $admission_note_id = null;
        }

        $sql_item ="SELECT dr_adm_item.admission_note_item_id,
                    dr_adm_item.admission_note_doctor,
                    doctor.`name` AS admission_note_doctorname
                    FROM ".DbConstant::KPHIS_DBNAME.".ipd_dr_admission_note_item dr_adm_item
                    LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".doctor ON doctor.code = dr_adm_item.admission_note_doctor
                    WHERE an=:an
                    ORDER BY dr_adm_item.admission_note_item_id ASC";
        $stmt_item = $conn->prepare($sql_item);
        $stmt_item->execute($an_parameters);
        $admission_note_count = 0;
        while ($row_item = $stmt_item->fetch()){
            $admission_note_item_id[] = $row_item['admission_note_item_id'];
            $admission_note_doctor[] = $row_item['admission_note_doctor'];
            $admission_note_doctorname[] = $row_item['admission_note_doctorname'];
            //$admission_note_doctorentryposition[] = $row_item['admission_note_doctorentryposition'];
            $admission_note_count++;
        }
        //------------------------Doctor admission note

        //cc,pi
        if ($admission_note_id == null || $admission_note_id != null) {
            $sql_opdscreen = "SELECT opdscreen.vn,opdscreen.hn,opdscreen.cc,opdscreen.hpi,concat(round(opdscreen.bpd,0),'/',round(opdscreen.bps,0)) as bp,
                                    pt.sex,round(opdscreen.bps,0) as sbp,round(opdscreen.bpd,0) as dbp,
                                    round(opdscreen.pulse,0) as pr,round(opdscreen.rr,0) as rr,round(opdscreen.temperature,1) as bt,
                                    round((opdscreen.bw)*1000,0) as bw2,
                                    round(opdscreen.bw,1) as bw,round(opdscreen.height,1) as height,
                                    opdscreen.pe_ga_text, opdscreen.pe_heent_text,opdscreen.hpi,
                                    opdscreen.pmh,opdscreen.fh,opdscreen.pe,
                                    opdscreen.pe_heart_text, opdscreen.pe_lung_text,
                                    opdscreen.pe_ab_text, opdscreen.pe_neuro_text,
                                    opdscreen.pe_ext_text, opdscreen.pe, pt.cid, pt.passport_no, pt.hn,pt.pname,pt.fname,pt.lname,
                                    vn.age_y,vn.age_m,vn.age_d,opdscreen.bw,opdscreen.height,(select oi.name from " . DbConstant::HOSXP_DBNAME . ".ovstist oi where oi.ovstist = ov.ovstist) as ovst_ist
                                    FROM " . DbConstant::HOSXP_DBNAME . ".opdscreen
                                    INNER JOIN " . DbConstant::HOSXP_DBNAME . ".ovst ov on ov.vn = opdscreen.vn
                                    INNER JOIN " . DbConstant::HOSXP_DBNAME . ".vn_stat vn on vn.vn = opdscreen.vn
                                    INNER JOIN " . DbConstant::HOSXP_DBNAME . ".patient pt on pt.hn = opdscreen.hn
                                    WHERE opdscreen.vn= :vn ";
            $stmt_opdscreen = $conn->prepare($sql_opdscreen);
            $stmt_opdscreen->execute(['vn' => $vn]);
            $row_opdscreen  = $stmt_opdscreen->fetch();
        
        }

        $sql_opduser = "SELECT opduser.entryposition,opduser.name
                        FROM ".DbConstant::HOSXP_DBNAME.".opduser
                        WHERE loginname = :loginname";
        $stmt_opduser = $conn->prepare($sql_opduser);
        $stmt_opduser->execute($values);
        $row_opduser  = $stmt_opduser->fetch();

        $sql_ipt = "select patient.sex,patient.hn,patient.pname,patient.fname,patient.lname,/*patient.drugallergy, */
        (select GROUP_CONCAT(concat(opd_allergy.agent,'=',if(opd_allergy.symptom is null,',',opd_allergy.symptom)/*,' [',if(note is null,',',note),']'*/)) as name
            from " . DbConstant::HOSXP_DBNAME . ".opd_allergy
            where opd_allergy.hn = ipt.hn /*and (opd_allergy.no_alert<>'Y' or opd_allergy.no_alert is null)*/
            order by display_order) as drugallergy,
        an_stat.age_y,an_stat.age_m,an_stat.age_d,
        concat(ipt.regdate,' ',ipt.regtime) as regdatetime,
        ipt.dchdate,ipt.dchtime,
        ipt.regdate,ipt.regtime,
        ipt.ward,ward.name,
        ipt.pttype, pttype.`name` as pttype_name,
        iptadm.bedno, (select vs.bw from ipd_vs_vital_sign vs where vs.an = ipt.an and vs.bw is not null and trim(vs.bw) <> '' order by vs_datetime desc limit 1) as latest_bw
        , (select vs.height from ipd_vs_vital_sign vs where vs.an = ipt.an and vs.bw is not null and trim(vs.bw) <> '' order by vs_datetime desc limit 1) as latest_height
        , (select vs.vs_datetime from ipd_vs_vital_sign vs where vs.an = ipt.an and vs.bw is not null and trim(vs.bw) <> '' order by vs_datetime desc limit 1) as latest_bw_datetime
        from " . DbConstant::HOSXP_DBNAME . ".ipt
        left outer join " . DbConstant::HOSXP_DBNAME . ".an_stat on an_stat.an=ipt.an
        left outer join " . DbConstant::HOSXP_DBNAME . ".patient on patient.hn=ipt.hn
        left outer join " . DbConstant::HOSXP_DBNAME . ".ward on ward.ward=ipt.ward
        LEFT OUTER JOIN " . DbConstant::HOSXP_DBNAME . ".pttype ON pttype.pttype = ipt.pttype
        LEFT OUTER JOIN " . DbConstant::HOSXP_DBNAME . ".iptadm ON iptadm.an = ipt.an
        WHERE ipt.an=:an
        order by ipt.an
        ";
$stmt_ipt = $conn->prepare($sql_ipt);
$stmt_ipt->execute(['an' => $an]);
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
        $sql_vs =   "SELECT ipd_vs_vital_sign.sbp,ipd_vs_vital_sign.dbp,ipd_vs_vital_sign.bt,ipd_vs_vital_sign.pr,ipd_vs_vital_sign.rr,
                    ipd_vs_vital_sign.eye,ipd_vs_vital_sign.verbal,ipd_vs_vital_sign.movement,ipd_vs_vital_sign.braden
                    FROM ".DbConstant::KPHIS_DBNAME.".ipd_vs_vital_sign
                    WHERE ipd_vs_vital_sign.an=:an
                    GROUP BY ipd_vs_vital_sign.vs_datetime ASC LIMIT 1";
        $stmt_vs = $conn->prepare($sql_vs);
        $stmt_vs->execute(['an'=>$an]);
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

<form id ="button1" method="post">
<div class="container-fluid">
        <div class="row">
            <div class="col-md-1">
        <input type="submit" name="button1"
                value="นำเข้าบันทึกรับใหม่"/>

</div>
</div>
    </form>
    
    <br>

<form id="admit_firsth" action=""  method="post"  enctype="multipart/form-data">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-1">
                <button type="button" class="btn btn-danger btn-block" onclick="self.close()"><i class="fa fa-window-close"></i> ปิด</button> 
            </div>
            <div class="col-md-11">
                <h4>แบบบันทึกการรับใหม่ผู้ป่วยใน <?=htmlspecialchars(DbConstant::HOSPITAL_NAME)?></h4>
            </div>
        </div><p></p>
        <div class="card-group pb-3 ">
            <div class="card" >
                <div class="card-body" style=" overflow-y: auto;">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group row">
                                <label class="col-sm-12"><B>วันที่รับไว้รักษา</B></label>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-1"></div>
                                <label >วันที่</label>
                                <div class="col-sm-4">
                                <input type="date" class="form-control form-control-sm" id="receiver_medication_date" name="receiver_medication_date" value="<?= (isset($row_ipt['regdate'])  ? htmlspecialchars($row_ipt['regdate']) : htmlspecialchars($row['receiver_medication_date'])) ?>">
                                </div>
                                <label>เวลา</label>
                                <div class="col-sm-4">
                                <input type="time" class="form-control form-control-sm" id="receiver_medication_time" name="receiver_medication_time" value="<?= (isset($row_ipt['regtime']) && $admission_note_id == null ? htmlspecialchars($row_ipt['regtime']) : htmlspecialchars($row['receiver_medication_time'])) ?>">
                                </div>
                            </div><hr>
                            <div class="form-group row">
                                <label class="col-sm-12"><B> มาถึงหอผู้ปวยโดย</B></label>
                            </div>
                            <div class="form-group row">
                                <!-- <input type="hidden" id="arrive_hidden" name="arrive_hidden" value=""> -->
                                <div class="col-sm-1"></div>
                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php   if ($row['arrive_by'] == 'เดินมา'
                                                               /* ||  $row['arrive_by'] == NULL*/)  {echo 'checked="checked"';} ?>
                                        class="custom-control-input" id="w1" name="arrive_by" value="เดินมา" onchange="custom_check('off_arrive');">
                                    <label class="custom-control-label" for="w1">เดินมา</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php if ($row['arrive_by'] == 'รถนั่ง')  {echo 'checked="checked"';} ?>
                                        class="custom-control-input" id="w2" name="arrive_by" value="รถนั่ง" onchange="custom_check('off_arrive');">
                                    <label class="custom-control-label" for="w2">รถนั่ง</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php if ($row['arrive_by'] == 'รถนอน')  {echo 'checked="checked"';} ?>
                                        class="custom-control-input" id="w3" name="arrive_by" value="รถนอน" onchange="custom_check('off_arrive');">
                                    <label class="custom-control-label" for="w3">รถนอน</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-3">
                                    <input type="radio" <?php if ($row['arrive_by'] == 'รถ Transfer')  {echo 'checked="checked"';} ?>
                                        class="custom-control-input" id="w4" name="arrive_by" value="รถ Transfer" onchange="custom_check('off_arrive');">
                                    <label class="custom-control-label" for="w4">รถ Transfer</label>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-1"></div>
                                <div class="custom-control custom-radio col-sm-2">
                                    <input type="radio" <?php if ($row['arrive_by'] !='เดินมา'
                                                                &&$row['arrive_by'] !='รถนั่ง'
                                                                &&$row['arrive_by'] !='รถนอน'
                                                                &&$row['arrive_by'] !='รถ Transfer'
                                                                &&$row['arrive_by'] != NULL) {echo 'checked="checked"';} ?>
                                        class="custom-control-input" id="w5" onchange="custom_check('on_arrive');">
                                    <label class="custom-control-label" for="w5">อื่น ๆ</label>
                                </div>
                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="w6" name="arrive_by"
                                        value="<?php if ($row['arrive_by'] !='เดินมา'
                                                        &&$row['arrive_by'] !='รถนั่ง'
                                                        &&$row['arrive_by'] !='รถนอน'
                                                        &&$row['arrive_by'] !='รถ Transfer') {echo htmlspecialchars($row['arrive_by']);}?>"
                                        <?php if (!($row['arrive_by'] !='เดินมา'
                                                        &&$row['arrive_by'] !='รถนั่ง'
                                                        &&$row['arrive_by'] !='รถนอน'
                                                        &&$row['arrive_by'] !='รถ Transfer'
                                                        &&$row['arrive_by'] != NULL)) {echo 'disabled';}?>
                                    >
                                </div>
                            </div><hr>
                            <div class="form-group row">
                                <label class="col-sm-12"><B> อาการสำคัญ</B></label>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <textarea class="form-control" id="" name="chief_complaints" rows="6" ><?=(isset($row_opdscreen['cc']) && $admission_note_id == null ? htmlspecialchars($row_opdscreen['cc']) : htmlspecialchars($row['chief_complaints']))?></textarea>
                                </div>
                            </div><hr>
                            <div class="form-group row">
                                <label class="col-sm-5"><B> ผู้ให้ข้อมูล</B></label>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-1"></div>
                                <div class="custom-control custom-checkbox col-sm-2">
                                    <input type="checkbox" <?php if ($row['informant_patient'] == 'ผู้ป่วย')  {echo 'checked="checked"';} ?>
                                        class="custom-control-input" id="e1" value="ผู้ป่วย" name="informant_patient"   >
                                    <label class="custom-control-label" for="e1">ผู้ป่วย</label>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-1"></div>
                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="checkbox" <?php if ($row['informant_relatives'] != null)  {echo 'checked="checked"';} ?>
                                        class="custom-control-input" id="e2"    onchange="custom_check('on_informant1');">
                                    <label class="custom-control-label" for="e2">ญาติ</label>
                                </div>
                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="e_informant1" name="informant_relatives"
                                            value="<?=(isset($row['informant_relatives']) ? htmlspecialchars($row['informant_relatives']) : '')?>"
                                            <?php if ($row['informant_relatives'] == null)  {echo 'disabled';} ?>>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-1"></div>
                                <div class="custom-control custom-checkbox col-sm-2">
                                    <input type="checkbox" <?php if ($row['informant_deliverer'] == 'ผู้นำส่ง')  {echo 'checked="checked"';} ?>
                                        class="custom-control-input" id="e3" value="ผู้นำส่ง" name="informant_deliverer"   >
                                    <label class="custom-control-label" for="e3">ผู้นำส่ง</label>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-1"></div>
                                <div class="custom-control custom-checkbox col-sm-1">
                                    <input type="checkbox" <?php if ($row['informant_etc'] != null)  {echo 'checked="checked"';} ?>
                                        class="custom-control-input" id="e4"    onchange="custom_check('on_informant2');">
                                    <label class="custom-control-label" for="e4">อื่นๆ</label>
                                </div>
                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="e_informant2" name="informant_etc"
                                            value="<?=(isset($row['informant_etc']) ? htmlspecialchars($row['informant_etc']) : '')?>"
                                            <?php if ($row['informant_etc'] == null)  {echo 'disabled';} ?>>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card" >
                <div class="card-body" style=" overflow-y: auto;">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group row">
                            <label class="col-sm-12"><B>เข้ารับการรักษาโดย</B></label>
                            </div>

                            <div class="form-group row">
                                        <div class="col-sm-1"></div>

                                        <div class="col-sm-4">
                                            <input type="text" class="form-control form-control-sm" id="take_medication_by" name="take_medication_by" value="<?= (isset($row_opdscreen['ovst_ist']) && $admission_note_id == null ? htmlspecialchars($row_opdscreen['ovst_ist']) : htmlspecialchars($row['take_medication_by'])) ?>">
                                        </div>


                                    </div>

                           <hr>
                            <div class="form-group row">
                                <label class="col-sm-12"><B> นำส่งผู้ป่วยโดย</B></label>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-1"></div>
                                <div class="custom-control custom-checkbox col-sm-2">
                                    <input type="checkbox" <?php if ($row['taken_by_relative'] == 'Y')  {echo 'checked="checked"';} ?>
                                        class="custom-control-input" id="r1" value="Y" name="taken_by_relative"    onchange="custom_check('off_taken');">
                                    <label class="custom-control-label" for="r1">ญาติ</label>
                                </div>
                                <div class="custom-control custom-checkbox col-sm-2">
                                    <input type="checkbox" <?php if ($row['taken_by_nurse'] == 'Y')  {echo 'checked="checked"';} ?>
                                        class="custom-control-input" id="r2" value="Y" name="taken_by_nurse"    onchange="custom_check('off_taken');">
                                    <label class="custom-control-label" for="r2">พยาบาล</label>
                                </div>
                                <div class="custom-control custom-checkbox col-sm-3">
                                    <input type="checkbox" <?php if ($row['taken_by_crib'] == 'Y')  {echo 'checked="checked"';} ?>
                                        class="custom-control-input" id="r3" value="Y" name="taken_by_crib"    onchange="custom_check('off_taken');">
                                    <label class="custom-control-label" for="r3">พนักงานเปล</label>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-1"></div>
                                <div class="custom-control custom-checkbox col-sm-2">
                                    <input type="checkbox" <?php if ($row['taken_by_etc'] == 'Y')  {echo 'checked="checked"';} ?>
                                        class="custom-control-input" value="Y" name="taken_by_etc" id="r4" onchange="custom_check('on_taken');">
                                    <label class="custom-control-label" for="r4">อื่น ๆ</label>
                                </div>
                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="r5" name="taken_by"
                                            value="<?php if ($row['taken_by_etc'] == 'Y') {echo htmlspecialchars($row['taken_by']);}?>"
                                            <?php if (!($row['taken_by_etc'] == 'Y')) {echo 'disabled';} ?>>
                                </div>
                            </div><hr>
                            <div class="form-group row">
                                <label class="col-sm-12"><B>ประวัติการเจ็บป่วยปัจจุบัน</B></label>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <textarea class="form-control" id="" name="medical_history" rows="6"><?=(isset($row_opdscreen['hpi']) && $admission_note_id == null ? htmlspecialchars($row_opdscreen['hpi']) : htmlspecialchars($row['medical_history']))?></textarea>
                                </div>
                            </div><hr>
                            <div class="form-group row">
                                <label class="col-sm-12"><B>สัญญาณชีพแรกรับ</B></label>
                            </div>
                            <div class="form-group row">
                                <label class="text-right col-sm-2">BP</label>
                                <div class="col-sm-3">
                                    <div class="input-group input-group-sm sm-3">
                                        <input type="text" class="form-control" id="" name="bp" disabled value="<?=(isset($row_vs['sbp']) ? htmlspecialchars($row_vs['sbp']).'/' : '')?><?=(isset($row_vs['dbp']) ? htmlspecialchars($row_vs['dbp']) : '')?>"
                                                aria-describedby="inputGroup-sizing-sm">
                                        <div class="input-group-append">
                                            <span class="input-group-text" id="inputGroup-sizing-sm">mmHg</span>
                                        </div>
                                    </div>
                                </div>
                                <label class="text-right col-sm-3">E</label>
                                <div class="col-sm-2">
                                    <div class="input-group input-group-sm sm-3">
                                        <input type="text" class="form-control" id="" name="e" disabled value="<?=(isset($row_vs['eye']) ? htmlspecialchars($row_vs['eye']) : '')?>" aria-describedby="inputGroup-sizing-sm">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="text-right col-sm-2">T</label>
                                <div class="col-sm-3">
                                    <div class="input-group input-group-sm sm-3">
                                        <input type="text" class="form-control" id="" name="t" disabled value="<?=(isset($row_vs['bt']) ? htmlspecialchars($row_vs['bt']) : '')?>"
                                                aria-describedby="inputGroup-sizing-sm">
                                        <div class="input-group-append">
                                            <span class="input-group-text" id="inputGroup-sizing-sm">  &#176; C    </span>
                                        </div>
                                    </div>
                                </div>
                                <label class="text-right col-sm-3">V</label>
                                <div class="col-sm-2">
                                    <div class="input-group input-group-sm sm-3">
                                        <input type="text" class="form-control" id="" name="v" disabled value="<?=(isset($row_vs['verbal']) ? htmlspecialchars($row_vs['verbal']) : '')?>" aria-describedby="inputGroup-sizing-sm">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="text-right col-sm-2">PR</label>
                                <div class="col-sm-3">
                                    <div class="input-group input-group-sm sm-3">
                                        <input type="text" class="form-control" id="" name="pr"  disabled value="<?=(isset($row_vs['pr']) ? htmlspecialchars($row_vs['pr']) : '')?>" aria-describedby="inputGroup-sizing-sm">
                                        <div class="input-group-append">
                                            <span class="input-group-text" id="inputGroup-sizing-sm"> /min  </span>
                                        </div>
                                    </div>
                                </div>
                                <label class="text-right col-sm-3">M</label>
                                <div class="col-sm-2">
                                    <div class="input-group input-group-sm sm-3">
                                        <input type="text" class="form-control" id="" name="m"  disabled value="<?=(isset($row_vs['movement']) ? htmlspecialchars($row_vs['movement']) : '')?>" aria-describedby="inputGroup-sizing-sm">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="text-right col-sm-2">RR</label>
                                <div class="col-sm-3">
                                    <div class="input-group input-group-sm sm-3">
                                        <input type="text" class="form-control" id="" name="rr"   disabled value="<?=(isset($row_vs['rr']) ? htmlspecialchars($row_vs['rr']) : '')?>" aria-describedby="inputGroup-sizing-sm">
                                        <div class="input-group-append">
                                            <span class="input-group-text" id="inputGroup-sizing-sm"> /min  </span>
                                        </div>
                                    </div>
                                </div>
                                <label class="text-right col-sm-3">Neuro sign GCS</label>
                                <div class="col-sm-3">
                                    <div class="input-group input-group-sm sm-3">
                                        <input type="text" class="form-control" id="" name="gcs"
                                                disabled value="<?php $i = 0;
                                                                    if (isset($row_vs['eye'])){
                                                                        if (is_numeric($row_vs['eye'])) {
                                                                            $i += $row_vs['eye'];
                                                                        }
                                                                    }else{'';}
                                                                    if (isset($row_vs['verbal'])){
                                                                        if (is_numeric($row_vs['verbal'])) {
                                                                            $i += $row_vs['verbal'];
                                                                        }
                                                                    }else{'';}
                                                                    if (isset($row_vs['movement'])){
                                                                        if (is_numeric($row_vs['movement'])) {
                                                                            $i += $row_vs['movement'];
                                                                        }
                                                                    }else{'';}
                                                                echo $i;
                                                                ?>"
                                                aria-describedby="inputGroup-sizing-sm">
                                        <div class="input-group-append">
                                            <span class="input-group-text" id="inputGroup-sizing-sm">คะแนน</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="text-right col-sm-2">Braden Scale</label>
                                <div class="col-sm-3">
                                    <div class="input-group input-group-sm sm-3">
                                        <input type="text" class="form-control" id="" name="braden_scale"  disabled value="<?=(isset($row_vs['braden']) ? htmlspecialchars($row_vs['braden']) : '')?>" aria-describedby="inputGroup-sizing-sm">
                                        <div class="input-group-append">
                                            <span class="input-group-text" id="inputGroup-sizing-sm">คะแนน</span>
                                        </div>
                                    </div>
                                </div>
                            </div><hr>
                            <div class="form-group row">
                                <label class="text-right col-sm-2">น้ำหนัก</label>
                                <div class="col-sm-2">
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control" id="" name=""  disabled value="<?=(isset($row_ipt['latest_bw']) ? htmlspecialchars($row_ipt['latest_bw']) : '')?>" aria-describedby="inputGroup-sizing-sm">
                                    </div>
                                </div>
                                <label class="text-right col-sm-1">ส่วนสูง</label>
                                <div class="col-sm-2">
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control" id="" name=""  disabled value="<?=(isset($row_ipt['latest_height']) ? htmlspecialchars($row_ipt['latest_height']) : '')?>" aria-describedby="inputGroup-sizing-sm">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-info text-center col-sm-12" role="alert">ประวัติการเจ็บป่วยในอดีต</div>
        <div class="form-group row">
            <div class="col-sm-12">
                <div class="card border-info">
                    <div class="card-body">
                        <div class="form-group row">
                            <div class="col-sm-2">
                                <h4><span class="badge badge-info">
                                <?php   if ($row_ipt['age_y'] < 1)  {echo 'ผู้ป่วยเด็กอายุ &lt;1 ปี';}
                                        else {echo 'ผู้ป่วยทั่วไป';}
                                ?></span></h4>
                            </div>
                            <label>( อายุ : <?php echo htmlspecialchars($row_ipt['age_y']." ปี ".$row_ipt['age_m']." เดือน ".$row_ipt['age_d']." วัน ");?> )</label>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12">โรคประจำตัว</label>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <div class="custom-control custom-radio col-sm-1">
                                <input type="radio" <?php if ($row['disease'] == 'ไม่มี'
                                                                ||$row['disease'] == NULL)  {echo 'checked="checked"';} ?>
                                    class="custom-control-input" id="t1" name="disease" value="ไม่มี"    onchange="custom_check('off_disease');">
                                <label class="custom-control-label" for="t1">ไม่มี</label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <div class="custom-control custom-radio col-sm-1">
                                <input type="radio" <?php if ($row['disease'] == 'มี'
                                                            &&$row['disease'] != NULL)  {echo 'checked="checked"';} ?>
                                    class="custom-control-input" id="t2" name="disease" value="มี" onchange="custom_check('on_disease');">
                                <label class="custom-control-label" for="t2">มี (ระบุ)</label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-2"></div>
                            <div class="custom-control custom-checkbox col-sm-3"><label class="text-right">
                                <a href="#"  data-toggle="modal" data-target="#myModal" class="signup-button gray-btn pl-pr-36" data-role="disabled" onclick='add()'>
                                <i class="fas fa-plus-square"></i></a>  โรค</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-3"><label class="text-right">จำนวนปี</label></div>
                            <div class="custom-control custom-checkbox col-sm-3"><label class="text-right">สถานพยาบาลที่รักษา</label></div>
                        </div>
                        <div class="form-group row"><?php  $disease_pos = explode(" ",$row['disease_detail']); ?>
                            <div class="col-sm-2"></div>
                            <div class="custom-control custom-checkbox col-sm-3">
                                <input type="text" class="form-control form-control-sm" value="<?=(isset($row['disease_detail']) ? htmlspecialchars($disease_pos[0]) : '')?>"id="disease_name1" name="disease_name1">
                            </div>
                            <div class="custom-control custom-checkbox col-sm-3">
                                <input type="text" class="form-control form-control-sm" value="<?=(isset($row['disease_detail']) ? htmlspecialchars($disease_pos[1]) : '')?>"id="disease_year1" name="disease_year1">
                            </div>
                            <div class="custom-control custom-checkbox col-sm-3">
                                <input type="text" class="form-control form-control-sm" value="<?=(isset($row['disease_detail']) ? htmlspecialchars($disease_pos[2]) : '')?>"id="disease_hospital1" name="disease_hospital1">
                            </div>
                            <div class="col-sm-1">
                                <a href="#"  data-toggle="modal" data-target="#myModal" class="signup-button gray-btn pl-pr-36" data-role="disabled" onclick='remove()'><i class="fas fa-trash-alt" style="color: Tomato;"></i></a>
                                <label> </label>
                            </div>
                        </div>

                        <?php  $y = 3;$z = 2;for ($x = 1; $x < (count($disease_pos)-1)/3; $x++) {
                                echo "<div id='disease_row".$z."' name='disease_row".$z."' class='form-group row'>
                                        <div class='col-sm-2'></div>
                                        <div class='custom-control custom-checkbox col-sm-3'>
                                        <input type='text' class='form-control form-control-sm'
                                                id='disease_name".$z."' name='disease_name".$z."' value='".htmlspecialchars($disease_pos[$y++])."'>
                                        </div>
                                        <div class='custom-control custom-checkbox col-sm-3'>
                                        <input type='text' class='form-control form-control-sm'
                                                id='disease_year".$z."' name='disease_year".$z."'value='".htmlspecialchars($disease_pos[$y++])."'>
                                        </div>
                                        <div class='custom-control custom-checkbox col-sm-3'>
                                            <input type='text' class='form-control form-control-sm'
                                                id='disease_hospital".$z."' name='disease_hospital".$z."' value='".htmlspecialchars($disease_pos[$y++])."'>
                                        </div>
                                        <div class='col-sm-1'>
                                            <a href='#'  data-toggle='modal' data-target='#myModal' class='signup-button gray-btn pl-pr-36' data-role='disabled' onclick='remove_pos(".$z.")'><i class='fas fa-trash-alt' style='color: Tomato;'></i></a>
                                            <label> </label>
                                        </div>
                                    </div>";$z++;
                                }
                        ?>
                        <script>
                            function add() {
                            var new_chq_no = parseInt($('#total_chq').val()) + 1;
                            var new_input = "<div id='disease_row"+ new_chq_no +"'class='form-group row'> <div class='col-sm-2'></div><div class='custom-control col-sm-3'><input type='text' class='form-control form-control-sm' id='disease_name"
                                            + new_chq_no +"'name='disease_name"+ new_chq_no + "'></div><div class='custom-control col-sm-3'><input type='text' class='form-control form-control-sm' id='disease_year"
                                            + new_chq_no +"'name='disease_year"+ new_chq_no + "'></div><div class='custom-control col-sm-3'><input type='text' class='form-control form-control-sm' id='disease_hospital"
                                            + new_chq_no +"'name='disease_hospital"
                                            + new_chq_no +"'></div><div class='col-sm-1'><a href='#'  data-toggle='modal' data-target='#myModal' class='signup-button gray-btn pl-pr-36' data-role='disabled' onclick='remove_pos("
                                            + new_chq_no +")'><i class='fas fa-trash-alt' style='color: Tomato;'></i></a><label> </label></div></div>";
                            $('#new_chq').append(new_input);
                            $('#total_chq').val(new_chq_no);
                            }
                            function remove_pos(last_chq_no) {
                                    $('#disease_row' + last_chq_no).remove();
                                    $('#disease_name' + last_chq_no).remove();
                                    $('#disease_year' + last_chq_no).remove();
                                    $('#disease_hospital' + last_chq_no).remove();
                            }
                            function remove() {
                                    $('#disease_name1').val('');
                                    $('#disease_year1').val('');
                                    $('#disease_hospital1').val('');
                            }
                        </script>
                <!--   ADD -->
                        <div id="new_chq"></div>
                        <input type="hidden" id="total_chq" value="<?php if($row['disease_detail']== null){echo 1;} else {echo (count($disease_pos)-1)/3;} ?>" >
                        <div class="form-group row"><textarea style="display:none;" name="disease_detail" id="disease_detail" cols="30" rows="10"></textarea></div>
                <!--   ADD -->

                        <div class="form-group row">
                            <label class="col-sm-12">การผ่าตัด</label>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <div class="custom-control custom-radio col-sm-1">
                                <input type="radio" <?=( (($row['operation_history'] == 'ไม่มี'
                                                        ||$row['operation_history'] == NULL )
                                                        &&($operation_text == "") ) ? 'checked="checked"' : '')?>
                                                    <?=(($operation_text != "") ? 'disabled' : '')?>
                                    class="custom-control-input" id="y1" name="operation_history" value="ไม่มี"   onchange="custom_check('off_operation');">
                                <label class="custom-control-label" for="y1">ไม่มี</label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <div class="custom-control custom-radio col-sm-2">
                                <input type="radio" <?=((!(($row['operation_history'] == 'ไม่มี'
                                                        ||$row['operation_history'] == NULL )
                                                        &&($operation_text == ""))) ? 'checked="checked"' : '')?>
                                    class="custom-control-input" id="y2"   onchange="custom_check('on_operation');">
                                <label class="custom-control-label" for="y2">มี (ระบุ)</label>
                            </div>
                            <div class="col-sm-2"></div>
                            <div class="col-sm-7">
                                <?php echo $operation_text ?>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <div class="custom-control custom-radio col-sm-2"></div>
                            <div class="col-sm-2"><label>รายละเอียด</label></div>
                            <div class="col-sm-6">
                                <textarea <?=((($row['operation_history'] == 'ไม่มี'
                                                ||$row['operation_history'] == NULL )
                                                &&($operation_text == "") ) ? 'disabled' : '')?>
                                    class="form-control" rows="3" id="y2_operation" name="operation_history"><?php if ($row['operation_history'] != 'ไม่มี') {echo htmlspecialchars($row['operation_history']);} ?></textarea>
                            </div>
                        </div>

                        <?php
                        if($admission_note_id == null){
                            $allergy_drug_pos = [];
                            $opd_er_allergy_history_list = KphisQueryUtils::getOpdErAllergyListByAn($an);
                            foreach($opd_er_allergy_history_list as $opd_er_allergy_history_item){
                                array_push($allergy_drug_pos, $opd_er_allergy_history_item['er_allergy_history_agent'], $opd_er_allergy_history_item['er_allergy_history_symptom']);
                            }
                            array_push($allergy_drug_pos, '');
                        } else {
                            $allergy_drug_pos = explode(" ",$row['allergy_drug_history']);
                        }
                        ?>
                        <div class="form-group row">
                            <label class="col-sm-12">ประวัติแพ้</label>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <div class="custom-control custom-radio col-sm-1">
                                <input type="radio" <?=($row['allergy_history'] == "ไม่มี"
                                                        ||$row['allergy_history'] == NULL ? 'checked="checked"' : '')?>
                                                    <?=(isset($row_ipt['drugallergy'])  ? 'disabled' : '')?>
                                        class="custom-control-input" id="u1" name="allergy_history" value="ไม่มี"   onchange="custom_check('off_allergy');">
                                <label class="custom-control-label" for="u1">ไม่มี</label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <div class="custom-control custom-radio col-sm-2">
                                <input type="radio" <?=($row['allergy_history'] == "มี"
                                                        ||(count($allergy_drug_pos) >= 2) ? 'checked="checked"' : '')?>
                                    class="custom-control-input" id="u2" name="allergy_history" value="มี"      onchange="custom_check('on_allergy');">
                                <label class="custom-control-label" for="u2">มี (ระบุ)</label>
                            </div>
                            <div class="col-sm-2">ประวัติการแพ้ยาใน HOSxP<br><small class="text-info">(ณ เวลาที่บันทึกแรกรับครั้งแรก)</small></div>
                            <div class="col-sm-6">
                                <textarea class="form-control text-danger" rows="3" readonly="readonly"
                                    id="allergy_drug_history_hosxp"
                                    name="allergy_drug_history_hosxp"><?=htmlspecialchars(($admission_note_id == null) ? ($row_ipt['drugallergy']) : ($row['allergy_drug_history_hosxp']))?></textarea>
                            </div>
                        </div>
                        <?php
                        $opd_er_allergy_history = KphisQueryUtils::getOpdErAllergyWithSymptomByAn($an, 'Y');
                        if($opd_er_allergy_history != null) { ?>
                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <div class="custom-control custom-radio col-sm-2"></div>
                            <div class="col-sm-2">แจ้งแพ้ยา (ER)<br><small class="text-info"></small></div>
                            <div class="col-sm-6">
                                <textarea class="form-control text-danger" rows="3" readonly="readonly"><?=htmlspecialchars($opd_er_allergy_history)?></textarea>
                            </div>
                        </div>
                        <?php } ?>
                        <div class="form-group row">
                            <div class="col-sm-3"></div>
                                <div class="custom-control col-sm-2">
                            </div>
                            <div class="col-sm-3"><label class="text-right">ชื่อ</label></div>
                            <div class="col-sm-2"><label class="text-right">อาการที่แพ้</label></div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-3"></div>
                            <div class="custom-control col-sm-2">
                                <a href="#"  data-toggle="modal" data-target="#myModal" class="signup-button gray-btn pl-pr-36" data-role="disabled" onclick='add_drug()'><i class="fas fa-plus-square"></i></a>
                                <label  for="uu1">   ยา</label>
                            </div>
                            <div class="col-sm-3">
                                <input type="text"  style='color: #E60000;' class="form-control form-control-sm" id="allergy_drug_pri1" name="allergy_drug_pri1" value="<?=((count($allergy_drug_pos) >= 2) ? htmlspecialchars($allergy_drug_pos[0]) : '')?>">
                            </div>
                            <div class="col-sm-3">
                                <input type="text"  style='color: #E60000;' class="form-control form-control-sm" id="allergy_drug_sec1" name="allergy_drug_sec1" value="<?=((count($allergy_drug_pos) >= 2) ? htmlspecialchars($allergy_drug_pos[1]) : '')?>">
                            </div>
                            <div class="col-sm-1">
                                <a href="#"  data-toggle="modal" data-target="#myModal" class="signup-button gray-btn pl-pr-36" data-role="disabled" onclick='remove_drug()'><i class="fas fa-trash-alt" style="color: Tomato;"></i></a>
                                <label> </label>
                            </div>
                        </div>
                        <?php
                            $y = 2;
                            $z = 2;
                            for ($x = 1; $x < (count($allergy_drug_pos)-1)/2; $x++) {
                                echo "<div id='allergy_drug_row".$z."' class='form-group row'>
                                        <div class='col-sm-5'></div>
                                        <div class='col-sm-3'>
                                            <input type='text'  style='color: #E60000;' class='form-control form-control-sm' id='allergy_drug_pri".$z."' name='allergy_drug_pri".$z."' value='".htmlspecialchars($allergy_drug_pos[$y++])."'>
                                        </div>
                                        <div class='col-sm-3'>
                                            <input type='text'  style='color: #E60000;' class='form-control form-control-sm' id='allergy_drug_sec".$z."' name='allergy_drug_sec".$z."' value='".htmlspecialchars($allergy_drug_pos[$y++])."'>
                                        </div>
                                        <div class='col-sm-1'>
                                            <a href='#'  data-toggle='modal' data-target='#myModal' class='signup-button gray-btn pl-pr-36' data-role='disabled' onclick='remove_pos_drug(".$z.")'><i class='fas fa-trash-alt' style='color: Tomato;'></i></a>
                                            <label> </label>
                                        </div>
                                    </div>";$z++;
                            }
                        ?>
                        <div id="new_chq_drug"></div><input  type="hidden" id="total_chq_drug" value="<?php if((count($allergy_drug_pos) < 2)){echo 1;} else {echo (count($allergy_drug_pos)-1)/2;} ?>" >
                        <div class="form-group row"><textarea style="display:none;" name="allergy_drug_history" id="allergy_drug_history" cols="30" rows="10"></textarea></div>
                            <script>
                                function add_drug() {
                                var new_chq_no_drug = parseInt($('#total_chq_drug').val()) + 1;
                                var new_input_drug = "<div id='allergy_drug_row"+ new_chq_no_drug +"'class='form-group row'> <div class='col-sm-3'></div><div class='custom-control col-sm-2'></div><div class='col-sm-3'><input type='text' style='color: #E60000;' class='form-control form-control-sm' id='allergy_drug_pri"
                                                + new_chq_no_drug +"'name='allergy_drug_pri"+ new_chq_no_drug + "'></div><div class='col-sm-3'><input type='text' style='color: #E60000;' class='form-control form-control-sm' id='allergy_drug_sec"
                                                + new_chq_no_drug + "'name='allergy_drug_sec"+ new_chq_no_drug + "'></div><div class='col-sm-1'><a href='#'  data-toggle='modal' data-target='#myModal' class='signup-button gray-btn pl-pr-36' data-role='disabled' onclick='remove_pos_drug("
                                                + new_chq_no_drug +")'><i class='fas fa-trash-alt' style='color: Tomato;'></i></a><label> </label></div></div>";
                                $('#new_chq_drug').append(new_input_drug);
                                $('#total_chq_drug').val(new_chq_no_drug);
                                }
                                function remove_pos_drug(last_chq_no) {
                                    $('#allergy_drug_row' + last_chq_no).remove();
                                    $('#allergy_drug_pri' + last_chq_no).remove();
                                    $('#allergy_drug_sec' + last_chq_no).remove();
                                }

                                function remove_drug() {
                                        $('#allergy_drug_pri1').val('');
                                        $('#allergy_drug_sec1').val('');
                                }
                            </script>
                        <div class="form-group row">
                            <div class="col-sm-3"></div>
                            <div class="custom-control col-sm-2">
                                <a href="#"  data-toggle="modal" data-target="#myModal" class="signup-button gray-btn pl-pr-36" data-role="disabled" onclick='add_food()'><i class="fas fa-plus-square"></i></a>
                                <label  for="uu2">   อาหาร</label><?php  $allergy_food_pos = explode(" ",$row['allergy_food_history']); ?>
                            </div>
                            <div class="col-sm-3">
                                <input type="text"  style='color: #E60000;' class="form-control form-control-sm" id="allergy_food_pri1" name="allergy_food_pri1" value="<?=(isset($row['allergy_food_history']) ? htmlspecialchars($allergy_food_pos[0]) : '')?>">
                            </div>
                            <div class="col-sm-3">
                                <input type="text"  style='color: #E60000;' class="form-control form-control-sm" id="allergy_food_sec1" name="allergy_food_sec1" value="<?=(isset($row['allergy_food_history']) ? htmlspecialchars($allergy_food_pos[1]) : '')?>">
                            </div>
                            <div class="col-sm-1">
                                <a href="#"  data-toggle="modal" data-target="#myModal" class="signup-button gray-btn pl-pr-36" data-role="disabled" onclick='remove_food()'><i class="fas fa-trash-alt" style="color: Tomato;"></i></a>
                                <label> </label>
                            </div>
                        </div>
                        <?php  $y = 2;$z = 2;for ($x = 1; $x < (count($allergy_food_pos)-1)/2; $x++) {
                                echo "<div id='allergy_food_row".$z."' class='form-group row'>
                                        <div class='col-sm-5'></div>
                                        <div class='col-sm-3'>
                                            <input type='text'  style='color: #E60000;' class='form-control form-control-sm' id='allergy_food_pri".$z."' name='allergy_food_pri".$z."' value='".htmlspecialchars($allergy_food_pos[$y++])."'>
                                        </div>
                                        <div class='col-sm-3'>
                                            <input type='text'  style='color: #E60000;' class='form-control form-control-sm' id='allergy_food_sec".$z."' name='allergy_food_sec".$z."' value='".htmlspecialchars($allergy_food_pos[$y++])."'>
                                        </div>
                                        <div class='col-sm-1'>
                                            <a href='#'  data-toggle='modal' data-target='#myModal' class='signup-button gray-btn pl-pr-36' data-role='disabled' onclick='remove_pos_food(".$z.")'><i class='fas fa-trash-alt' style='color: Tomato;'></i></a>
                                            <label> </label>
                                        </div>
                                    </div>";$z++;
                                }
                        ?>
                        <div id="new_chq_food"></div><input   type="hidden" id="total_chq_food" value="<?php if($row['allergy_food_history']== null){echo 1;} else {echo (count($allergy_food_pos)-1)/2;} ?>" >
                        <div class="form-group row"><textarea style="display:none;" name="allergy_food_history" id="allergy_food_history" cols="30" rows="10"></textarea></div>
                            <script>
                                function add_food() {
                                var new_chq_no_food = parseInt($('#total_chq_food').val()) + 1;
                                var new_input_food = "<div id='allergy_food_row"+ new_chq_no_food +"'class='form-group row'> <div class='col-sm-3'></div><div class='custom-control col-sm-2'></div><div class='col-sm-3'><input type='text' style='color: #E60000;' class='form-control form-control-sm' id='allergy_food_pri"
                                                + new_chq_no_food +"'name='allergy_food_pri"+ new_chq_no_food + "'></div><div class='col-sm-3'><input type='text' style='color: #E60000;' class='form-control form-control-sm' id='allergy_food_sec"
                                                + new_chq_no_food + "'name='allergy_food_sec"+ new_chq_no_food + "'></div><div class='col-sm-1'><a href='#'  data-toggle='modal' data-target='#myModal' class='signup-button gray-btn pl-pr-36' data-role='disabled' onclick='remove_pos_food("
                                                + new_chq_no_food +")'><i class='fas fa-trash-alt' style='color: Tomato;'></i></a><label> </label></div></div>";
                                $('#new_chq_food').append(new_input_food);
                                $('#total_chq_food').val(new_chq_no_food);
                                }
                                function remove_pos_food(last_chq_no) {
                                    $('#allergy_food_row' + last_chq_no).remove();
                                    $('#allergy_food_pri' + last_chq_no).remove();
                                    $('#allergy_food_sec' + last_chq_no).remove();
                                }
                                function remove_food() {
                                    $('#allergy_food_pri1').val('');
                                    $('#allergy_food_sec1').val('');
                                }
                            </script>
                        <div class="form-group row">
                            <div class="col-sm-3"></div>
                            <div class="custom-control col-sm-2">
                                <a href="#"  data-toggle="modal" data-target="#myModal" class="signup-button gray-btn pl-pr-36" data-role="disabled" onclick='add_etc()'><i class="fas fa-plus-square"></i></a>
                                <label  for="uu3">   อื่นๆ</label><?php  $allergy_etc_pos = explode(" ",$row['allergy_etc_history']); ?>
                            </div>
                            <div class="col-sm-3">
                                <input type="text"  style='color: #E60000;' class="form-control form-control-sm" id="allergy_etc_pri1" name="allergy_etc_pri1" value="<?=(isset($row['allergy_etc_history']) ? htmlspecialchars($allergy_etc_pos[0]) : '')?>">
                            </div>
                            <div class="col-sm-3">
                                <input type="text"  style='color: #E60000;' class="form-control form-control-sm" id="allergy_etc_sec1" name="allergy_etc_sec1" value="<?=(isset($row['allergy_etc_history']) ? htmlspecialchars($allergy_etc_pos[1]) : '')?>">
                            </div>
                            <div class="col-sm-1">
                                <a href="#"  data-toggle="modal" data-target="#myModal" class="signup-button gray-btn pl-pr-36" data-role="disabled" onclick='remove_etc()'><i class="fas fa-trash-alt" style="color: Tomato;"></i></a>
                                <label> </label>
                            </div>
                        </div>
                        <?php  $y = 2;$z = 2;for ($x = 1; $x < (count($allergy_etc_pos)-1)/2; $x++) {
                                echo "<div id='allergy_etc_row".$z."'class='form-group row'>
                                        <div class='col-sm-5'></div>
                                        <div class='col-sm-3'>
                                            <input type='text'  style='color: #E60000;' class='form-control form-control-sm' id='allergy_etc_pri".$z."' name='allergy_etc_pri".$z."' value='".htmlspecialchars($allergy_etc_pos[$y++])."'>
                                        </div>
                                        <div class='col-sm-3'>
                                            <input type='text'  style='color: #E60000;' class='form-control form-control-sm' id='allergy_etc_sec".$z."' name='allergy_etc_sec".$z."' value='".htmlspecialchars($allergy_etc_pos[$y++])."'>
                                        </div>
                                        <div class='col-sm-1'>
                                            <a href='#'  data-toggle='modal' data-target='#myModal' class='signup-button gray-btn pl-pr-36' data-role='disabled' onclick='remove_pos_etc(".$z.")'><i class='fas fa-trash-alt' style='color: Tomato;'></i></a>
                                            <label> </label>
                                        </div>
                                    </div>";$z++;
                                }
                        ?>
                        <div id="new_chq_etc"></div><input  type="hidden" id="total_chq_etc" value="<?php if($row['allergy_etc_history']== null){echo 1;} else {echo (count($allergy_etc_pos)-1)/2;} ?>" >
                        <div class="form-group row"><textarea style="display:none;" name="allergy_etc_history" id="allergy_etc_history" cols="30" rows="10"></textarea></div>
                            <script>
                                function add_etc() {
                                var new_chq_no_etc = parseInt($('#total_chq_etc').val()) + 1;
                                var new_input_etc = "<div id='allergy_etc_row"+ new_chq_no_etc +"'class='form-group row'> <div class='col-sm-3'></div><div class='custom-control col-sm-2'></div><div class='col-sm-3'><input type='text' style='color: #E60000;' class='form-control form-control-sm' id='allergy_etc_pri"
                                                + new_chq_no_etc +"'name='allergy_etc_pri"+ new_chq_no_etc + "'></div><div class='col-sm-3'><input type='text' style='color: #E60000;' class='form-control form-control-sm' id='allergy_etc_sec"
                                                + new_chq_no_etc + "'name='allergy_etc_sec"+ new_chq_no_etc + "'></div><div class='col-sm-1'><a href='#'  data-toggle='modal' data-target='#myModal' class='signup-button gray-btn pl-pr-36' data-role='disabled' onclick='remove_pos_etc("
                                                + new_chq_no_etc +")'><i class='fas fa-trash-alt' style='color: Tomato;'></i></a><label> </label></div></div>";
                                $('#new_chq_etc').append(new_input_etc);
                                $('#total_chq_etc').val(new_chq_no_etc);
                                }
                                function remove_pos_etc(last_chq_no) {
                                    $('#allergy_etc_row' + last_chq_no).remove();
                                    $('#allergy_etc_pri' + last_chq_no).remove();
                                    $('#allergy_etc_sec' + last_chq_no).remove();
                                }
                                function remove_etc() {
                                    $('#allergy_etc_pri1').val('');
                                    $('#allergy_etc_sec1').val('');
                                }
                            </script>
                        <!-- <div class="form-group row">
                            <div class="col-sm-3"></div>
                            <label class="col-sm-2">อาการที่แพ้</label>
                            <div class="col-sm-6">
                                <textarea class="form-control" id="uu4_in" rows="3" name="allergy_detail"><?=(isset($row['allergy_detail']) ? htmlspecialchars($row['allergy_detail']) : '')?></textarea>
                            </div>
                        </div> -->
                        <div class="form-group row">
                            <label class="col-sm-12">ประวัติการเจ็บป่วยในครอบครัว</label>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <div class="custom-control custom-radio col-sm-1">
                                <input type="radio" <?php if ($row['family_medical_history'] == 'ไม่มี'
                                                            ||$row['family_medical_history'] == NULL)  {echo 'checked="checked"';} ?>
                                    class="custom-control-input" id="i1" name="family_medical_history" value="ไม่มี"    onchange="custom_check('off_family_medical');">
                                <label class="custom-control-label" for="i1">ไม่มี</label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <div class="custom-control custom-radio col-sm-2">
                                <input type="radio" <?php if ($row['family_medical_history'] != 'ไม่มี'
                                                            &&$row['family_medical_history'] != NULL)  {echo 'checked="checked"';} ?>
                                    class="custom-control-input" id="i2" name="family_medical_history" value="มี" onchange="custom_check('on_family_medical');">
                                <label class="custom-control-label" for="i2">มี (ระบุ)</label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-3"></div>
                            <div class="col-sm-5"><a href="#"  data-toggle="modal" data-target="#myModal" class="signup-button gray-btn pl-pr-36" data-role="disabled" onclick='add_family_medical()'><i class="fas fa-plus-square"></i></a>
                                <label class="text-right">  โรค</label>
                            </div>
                            <div class="col-sm-3"><label class="text-right">เกี่ยวข้องเป็น</label></div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-3"></div><?php  $family_medical_pos = explode(" ",$row['family_medical_history_detail']); ?>
                            <div class="col-sm-5">
                                <input type="text" class="form-control form-control-sm" id="family_medical_pri1" name="family_medical_pri1"
                                    value="<?=(isset($row['family_medical_history_detail']) ? htmlspecialchars($family_medical_pos[0]) : '')?>">
                            </div>
                            <div class="col-sm-3">
                                <input type="text" class="form-control form-control-sm" id="family_medical_sec1" name="family_medical_sec1"
                                    value="<?=(isset($row['family_medical_history_detail']) ? htmlspecialchars($family_medical_pos[1]) : '')?>">
                            </div>
                            <div class="col-sm-1">
                                <a href="#"  data-toggle="modal" data-target="#myModal" class="signup-button gray-btn pl-pr-36" data-role="disabled" onclick='remove_family_medical()'><i class="fas fa-trash-alt" style="color: Tomato;"></i></a>
                                <label> </label>
                            </div>
                        </div>
                        <?php  $y = 2;$z = 2;for ($x = 1; $x < (count($family_medical_pos)-1)/2; $x++) {
                                echo "<div id='family_medical_row".$z."'class='form-group row'>
                                        <div class='col-sm-3'></div>
                                        <div class='col-sm-5'>
                                            <input type='text' class='form-control form-control-sm' id='family_medical_pri".$z."' name='family_medical_pri".$z."' value='".htmlspecialchars($family_medical_pos[$y++])."'>
                                        </div>
                                        <div class='col-sm-3'>
                                            <input type='text' class='form-control form-control-sm' id='family_medical_sec".$z."' name='family_medical_sec".$z."' value='".htmlspecialchars($family_medical_pos[$y++])."'>
                                        </div>
                                        <div class='col-sm-1'>
                                            <a href='#'  data-toggle='modal' data-target='#myModal' class='signup-button gray-btn pl-pr-36' data-role='disabled' onclick='remove_family_pos(".$z.")'><i class='fas fa-trash-alt' style='color: Tomato;'></i></a>
                                            <label> </label>
                                        </div>
                                    </div>";$z++;
                                }
                        ?>
                        <div id="new_chq_family_medical"></div><input  type="hidden" id="total_chq_family_medical" value="<?php if($row['family_medical_history_detail']== null){echo 1;} else {echo (count($family_medical_pos)-1)/2;} ?>" >
                        <div class="form-group row"><textarea style="display:none;" name="family_medical_history_detail" id="family_medical_history_detail" cols="30" rows="10"></textarea></div>
                            <script>
                                function add_family_medical() {
                                var new_chq_no_family_medical = parseInt($('#total_chq_family_medical').val()) + 1;
                                var new_input_family_medical = "<div id='family_medical_row"+ new_chq_no_family_medical +"'class='form-group row'> <div class='col-sm-3'></div><div class='col-sm-5'><input type='text' class='form-control form-control-sm' id='family_medical_pri"
                                                + new_chq_no_family_medical +"'name='family_medical_pri"+ new_chq_no_family_medical + "'></div><div class='col-sm-3'><input type='text' class='form-control form-control-sm' id='family_medical_sec"
                                                + new_chq_no_family_medical +"'name='family_medical_sec"+ new_chq_no_family_medical + "'></div><div class='col-sm-1'><a href='#'  data-toggle='modal' data-target='#myModal' class='signup-button gray-btn pl-pr-36' data-role='disabled' onclick='remove_family_pos("
                                                + new_chq_no_family_medical +")'><i class='fas fa-trash-alt' style='color: Tomato;'></i></a><label> </label></div></div>";
                                $('#new_chq_family_medical').append(new_input_family_medical);
                                $('#total_chq_family_medical').val(new_chq_no_family_medical);
                                }
                                function remove_family_medical() {
                                    $('#family_medical_pri1').val('');
                                    $('#family_medical_sec1').val('');
                                }
                                function remove_family_pos(last_chq_no) {
                                    $('#family_medical_row' + last_chq_no).remove();
                                    $('#family_medical_pri' + last_chq_no).remove();
                                    $('#family_medical_sec' + last_chq_no).remove();
                                }
                            </script>
                            <div class="row" style="<?php if ($row_ipt['age_y'] < 1){echo 'display:none;';}?>">
                                <label class="col-sm-1">อาชีพ(ระบุ)</label>
                                <div class="col-sm-4">
                                    <div class="row">
                                        <input type="text" class="form-control form-control-sm"
                                        id="occupation" name="occupation"
                                        value="<?=(isset($row_period['occupation']) ? htmlspecialchars($row_period['occupation']) : '')?>" disabled>
                                    </div>
                                </div>
                            </div>
                            <div style="<?php if ($row_ipt['age_y'] < 1){echo 'display:none;';}?>">
                                <div class="row">
                                    <label class="col-sm-12">พฤติกรรมเสี่ยง</label>
                                </div>
                                <div class="row">
                                    <div class="custom-control custom-checkbox col-sm-1 offset-sm-1">
                                        <input type="checkbox"  <?php   if (isset($row_period['no_risk'])) {
                                                                            if($row_period['no_risk'] == "Y"){echo 'checked="checked"';}
                                                                        }?>
                                        class="custom-control-input" id="no_risk" value="Y" name="no_risk" disabled>
                                        <label class="custom-control-label" for="no_risk">ปฏิเสธ</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="custom-control custom-checkbox col-sm-1 offset-sm-1">
                                        <input type="checkbox"  <?php   if (isset($row_period['smoking'])) {
                                                                            if($row_period['smoking'] == "Y"){echo 'checked="checked"';}
                                                                        }?>
                                        class="custom-control-input" id="smoking" value="Y" name="smoking" disabled>
                                        <label class="custom-control-label" for="smoking">สูบบุหรี่</label>
                                    </div>
                                    <div class="col-sm-1">
                                        <input type="text" value="<?=(isset($row_period['smoke_year']) ? htmlspecialchars($row_period['smoke_year']) : '')?>"
                                        class="form-control form-control-sm" id="smoke_year" name="smoke_year" disabled>
                                    </div>
                                    <label class="col-sm-1">ปี ปริมาณ</label>
                                    <div class="col-sm-1">
                                        <input type="text" value="<?=(isset($row_period['smoke_frequency']) ? htmlspecialchars($row_period['smoke_frequency']) : '')?>"
                                        class="form-control form-control-sm" id="smoke_frequency" name="smoke_frequency" disabled>
                                    </div>
                                    <label class="col-sm-1">/วัน เลิกเมื่อ</label>
                                    <div class="col-sm-2">
                                        <input type="text" value="<?=(isset($row_period['smoke_stopped']) ? htmlspecialchars($row_period['smoke_stopped']) : '')?>"
                                        class="form-control form-control-sm" id="smoke_stopped" name="smoke_stopped" disabled>
                                    </div>
                                </div><p></p>
                                <div class="row">
                                    <div class="custom-control custom-checkbox col-sm-1 offset-sm-1">
                                        <input type="checkbox"  <?php   if (isset($row_period['alcohol'])) {
                                                                            if($row_period['alcohol'] == "Y"){echo 'checked="checked"';}
                                                                        }?>
                                        class="custom-control-input" id="alcohol" value="Y" name="alcohol" disabled >
                                        <label class="custom-control-label" for="alcohol">ดื่มสุรา</label>
                                    </div>
                                    <div class="col-sm-1">
                                        <input type="text" value="<?=(isset($row_period['alc_year']) ? htmlspecialchars($row_period['alc_year']) : '')?>"
                                        class="form-control form-control-sm" id="alc_year" name="alc_year" disabled>
                                    </div>
                                    <label class="col-sm-1">ปี ปริมาณ</label>
                                    <div class="col-sm-1">
                                        <input type="text" value="<?=(isset($row_period['alc_frequency']) ? htmlspecialchars($row_period['alc_frequency']) : '')?>"
                                        class="form-control form-control-sm" id="alc_frequency" name="alc_frequency" disabled>
                                    </div>
                                    <label class="col-sm-1">/วัน เลิกเมื่อ</label>
                                    <div class="col-sm-2">
                                        <input type="text" value="<?=(isset($row_period['alc_stopped']) ? htmlspecialchars($row_period['alc_stopped']) : '')?>"
                                        class="form-control form-control-sm" id="alc_stopped" name="alc_stopped" disabled>
                                    </div>
                                </div><p></p>
                                <div class="row">
                                    <div class="custom-control custom-checkbox col-sm-1 offset-sm-1">
                                        <input type="checkbox"  <?php   if (isset($row_period['medication_used'])) {
                                                                            if($row_period['medication_used'] == "Y"){echo 'checked="checked"';}
                                                                        }?>
                                        class="custom-control-input" id="medication_used" value="Y" name="medication_used"disabled>
                                        <label class="custom-control-label" for="medication_used">ยา (ระบุ)</label>
                                    </div>
                                    <div class="col-sm-6">
                                        <textarea class="form-control" id="med_name" name="med_name" rows="3" disabled><?=(isset($row_period['med_name']) ? htmlspecialchars($row_period['med_name']) : '')?></textarea>
                                    </div>
                                    ระยะเวลาที่ใช้
                                    <div class="col-sm-2">
                                        <input type="text" value="<?=(isset($row_period['med_year']) ? htmlspecialchars($row_period['med_year']) : '')?>"
                                        class="form-control form-control-sm" id="med_year" name="med_year" disabled>
                                    </div>
                                </div><p></p>
                                <div class="row">
                                    <label class="col-sm-1 text-right offset-sm-2">ปริมาณ</label>
                                    <div class="col-sm-1">
                                        <input type="text" value="<?=(isset($row_period['med_frequency']) ? htmlspecialchars($row_period['med_frequency']) : '')?>"
                                        class="form-control form-control-sm" id="med_frequency" name="med_frequency" disabled>
                                    </div>
                                    <label class="col-sm-1">/วัน เลิกเมื่อ</label>
                                    <div class="col-sm-2">
                                        <input type="text" value="<?=(isset($row_period['med_stopped']) ? htmlspecialchars($row_period['med_stopped']) : '')?>"
                                        class="form-control form-control-sm" id="med_stopped" name="med_stopped" disabled>
                                    </div>
                                </div>
                            </div>
                            <div id="show_query_data_period" style="<?php if ($row_ipt['sex'] == 1 || $row_ipt['age_y'] < 9){echo 'display:none;';}?>">
                                <div class="row">
                                    <label class="col-sm-12">ประจำเดือน</label>
                                </div>
                                <div class="row">
                                    <div class="custom-control custom-radio col-sm-1 offset-sm-1">
                                        <input type="radio" <?php   if (isset($row_period['period'])) {
                                                                        if($row_period['period'] == "ยังไม่มี"){echo 'checked="checked"';}
                                                                    }?>
                                        class="custom-control-input" id="" value="ยังไม่มี" name="" disabled>
                                        <label class="custom-control-label" for="">ยังไม่มี</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="custom-control custom-radio col-sm-1 offset-sm-1">
                                        <input type="radio" <?php   if (isset($row_period['period'])) {
                                                                        if($row_period['period'] == "มี"){echo 'checked="checked"';}
                                                                    }?>
                                        class="custom-control-input" id="" value="มี" name="" disabled>
                                        <label class="custom-control-label" for="">มี</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="custom-control custom-radio col-sm-1 offset-sm-2">
                                        <input type="radio" <?php   if (isset($row_period['period_normal'])) {
                                                                        if($row_period['period_normal'] == "ปกติ"){echo 'checked="checked"';}
                                                                    }?>
                                        class="custom-control-input" id="" value="ปกติ" name="" disabled>
                                        <label class="custom-control-label" for="">ปกติ</label>
                                    </div>
                                </div><p></p>
                                <div class="row">
                                    <div class="custom-control custom-radio col-sm-1 offset-sm-2">
                                        <input type="radio" <?php   if (isset($row_period['period_normal'])) {
                                                                        if($row_period['period_normal'] == "ผิดปกติ"){echo 'checked="checked"';}
                                                                    }?>
                                        class="custom-control-input" id="" value="ผิดปกติ" name="" disabled>
                                        <label class="custom-control-label" for="">ผิดปกติ</label>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="row">
                                            <input type="text" value="<?=(isset($row_period['period_disorders']) ? htmlspecialchars($row_period['period_disorders']) : '')?>"
                                            class="form-control form-control-sm" id="" name="" disabled>
                                        </div>
                                    </div>
                                </div><p></p>
                                <div class="row">
                                    <div class="custom-control custom-radio col-sm-1 offset-sm-2">
                                        <input type="radio" <?php   if (isset($row_period['period_normal'])) {
                                                                        if($row_period['period_normal'] == "LMP"){echo 'checked="checked"';}
                                                                    }?>
                                        class="custom-control-input" id="" value="LMP" name="" disabled>
                                        <label class="custom-control-label" for="">LMP</label>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="row">
                                            <input type="text" value="<?=(isset($row_period['period_lmp']) ? htmlspecialchars($row_period['period_lmp']) : '')?>"
                                            class="form-control form-control-sm" id="" name="" disabled>
                                        </div>
                                    </div>
                                </div><p></p>
                                <div class="row">
                                    <div class="custom-control custom-radio col-sm-2 offset-sm-1">
                                        <input type="radio" <?php   if (isset($row_period['period'])) {
                                                                        if($row_period['period'] == "หมดประจำเดือน"){echo 'checked="checked"';}
                                                                    }?>
                                        class="custom-control-input" id="" value="หมดประจำเดือน" name="" disabled>
                                        <label class="custom-control-label" for="">หมดประจำเดือน เมื่ออายุ</label>
                                    </div>
                                    <div class="col-sm-1">
                                        <div class="row">
                                            <input type="text" value="<?=(isset($row_period['period_menopause']) ? htmlspecialchars($row_period['period_menopause']) : '')?>"
                                            class="form-control form-control-sm" id="" name="" disabled>
                                        </div>
                                    </div>
                                    <div class="col-sm-1">
                                        <label>ปี</label>
                                    </div>
                                </div><p></p>
                            </div>
<!-- display:none -->   <!-- </div> -->
<!-- display:none --><div style="<?php if (($row_ipt['age_y'] >  15 && $row_ipt['age_m'] >= 0 && $row_ipt['age_d'] >= 0)
                                            || ($row_ipt['age_y'] == 15 && $row_ipt['age_m'] >  0 && $row_ipt['age_d'] >  0)){echo 'display:none;';}?>">
                        <div class="form-group row">
                            <label class="col-sm-12">ประวัติการได้รับภูมิคุ้มกัน (เฉพาะเด็ก)</label>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <div class="custom-control custom-radio col-sm-2">
                                <input type="radio" <?php if ($row['receives_immunisation_history_kid'] == 'ครบตามวัย')  {echo 'checked="checked"';} ?>
                                    class="custom-control-input" id="o1" name="receives_immunisation_history_kid" value="ครบตามวัย"     onchange="custom_check('off_immunisation');">
                                <label class="custom-control-label" for="o1">ครบตามวัย</label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <div class="custom-control custom-radio col-sm-2">
                                <input type="radio" <?php if ($row['receives_immunisation_history_kid'] != 'ครบตามวัย'
                                                              && $row['receives_immunisation_history_kid'] != null
                                                              && $row['receives_immunisation_history_kid'] != '')  {echo 'checked="checked"';} ?>
                                    class="custom-control-input" id="o2" name="receives_immunisation_history_kid" onchange="custom_check('on_immunisation');" value="ไม่ครบตามวัย">
                                <label class="custom-control-label" for="o2">ไม่ครบ (ระบุ)</label>
                            </div>
                            <div class="col-sm-4">
                                <input type="text" class="form-control form-control-sm" id="o3"
                                    value="<?php if (!($row['receives_immunisation_history_kid'] == 'ครบตามวัย')) {echo htmlspecialchars($row['receives_immunisation_history_kid']);} ?>" name="receives_immunisation_history_kid_text">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12">การพัฒนาการ (เฉพาะเด็ก)</label>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <div class="custom-control custom-radio col-sm-2">
                                <input type="radio" <?php if ($row['developmentally_kid'] == 'ปกติ')  {echo 'checked="checked"';} ?>
                                    class="custom-control-input" id="p1" name="developmentally_kid" value="ปกติ"    onchange="custom_check('off_developmentally');">
                                <label class="custom-control-label" for="p1">ปกติ</label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <div class="custom-control custom-radio col-sm-2">
                                <input type="radio" <?php if ($row['developmentally_kid'] != 'ปกติ'
                                                            &&$row['developmentally_kid'] != NULL
                                                            &&$row['developmentally_kid'] != '')  {echo 'checked="checked"';} ?>
                                    class="custom-control-input" id="p2" name="developmentally_kid" value="ผิดปกติ" onchange="custom_check('on_developmentally');">
                                <label class="custom-control-label" for="p2">ผิดปกติ (ระบุ)</label>
                            </div>
                            <div class="col-sm-4">
                                <input type="text" class="form-control form-control-sm" id="p3"
                                    value="<?php if (!($row['developmentally_kid'] == 'ปกติ')) {echo htmlspecialchars($row['developmentally_kid']);} ?>" name="developmentally_kid_text">
                            </div>
                        </div>
<!-- display:none --></div>
<!-- display:none --><div style="<?php if ($row_ipt['age_y'] >  14 ){echo 'display:none;';}?>">
                        <div class="form-group row">
                            <label class="col-sm-12">มารดา</label>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <label class="text-right col-sm-1">G</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control form-control-sm" value="<?=(isset($row['g']) ? htmlspecialchars($row['g']) : '')?>" id="" name="g">
                            </div>
                            <label class="text-right col-sm-1">P</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control form-control-sm" value="<?=(isset($row['p']) ? htmlspecialchars($row['p']) : '')?>"  id="" name="p">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <label class="text-right col-sm-1">ANC ที่</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control form-control-sm" value="<?=(isset($row['anc']) ? htmlspecialchars($row['anc']) : '')?>"  id="" name="anc">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <label class="text-right col-sm-1">ได้ TT</label>
                            <div class="col-sm-2">
                                <div class="input-group input-group-sm sm-3">
                                    <input type="text" class="form-control" value="<?=(isset($row['tt']) ? htmlspecialchars($row['tt']) : '')?>"  id="" name="tt" aria-describedby="inputGroup-sizing-sm">
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="inputGroup-sizing-sm">   เข็ม  </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="text-right col-sm-2">อายุครรภ์</label>
                            <div class="col-sm-2">
                                <div class="input-group input-group-sm sm-2">
                                    <input type="text" class="form-control"  value="<?=(isset($row['gestational_age']) ? htmlspecialchars($row['gestational_age']) : '')?>"  id="" name="gestational_age" aria-describedby="inputGroup-sizing-sm">
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="inputGroup-sizing-sm"> สัปดาห์  </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="input-group input-group-sm sm-2">
                                    <input type="text" class="form-control"  value="<?=(isset($row['gestational_day']) ? htmlspecialchars($row['gestational_day']) : '')?>"  id="" name="gestational_day" aria-describedby="inputGroup-sizing-sm">
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="inputGroup-sizing-sm"> วัน  </span>
                                    </div>
                                </div>
                            </div>
                        </div>
<!-- display:none --></div>
<!-- display:none --><div style="<?php if ($row_ipt['age_y'] <  9 || $row_ipt['sex'] == 1){echo 'display:none;';}?>">
                        <div class="form-group row">
                            <label class="col-sm-3">ประวัติด้านสูตินรีเวชกรรม</label>
                            <button class="btn btn-primary" onclick="display_pb(event)">View <i class="fa fa-eye" aria-hidden="true"></i></button>
                                <script>
                                    function display_pb(event) {
                                        event.preventDefault();
                                        var pb = document.getElementById("show_pb");
                                        if (pb.style.display === "none") {
                                            pb.style.display = "block";
                                        } else {
                                            pb.style.display = "none";
                                        }
                                    }
                                </script>
                        </div>
<!-- display:none --></div>
<!-- display -->    <div id="show_pb" style="display:none;">
                        <div class="form-group row">
                            <div class="col-sm-2"></div>
                            <div class="col-sm-1">
                                <label> last child</label>
                            </div>
                            <div class="col-sm-2">
                                <div class="input-group input-group-sm sm-3">
                                    <input type="text" class="form-control" value="<?=(isset($row['last_child']) ? htmlspecialchars($row['last_child']) : '')?>"  id="" name="last_child" maxlength="2" aria-describedby="inputGroup-sizing-sm">
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="inputGroup-sizing-sm">            ปี  </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-2"></div>
                            <div class="col-sm-1">
                                <label> last abort</label>
                            </div>
                            <div class="col-sm-2">
                                <div class="input-group input-group-sm sm-3">
                                    <input type="text" class="form-control" value="<?=(isset($row['last_abort']) ? htmlspecialchars($row['last_abort']) : '')?>"  id="" name="last_abort" aria-describedby="inputGroup-sizing-sm">
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-2"></div>
                            <div class="col-sm-2">
                                <label> ประวัติการขูดมดลูก </label>
                            </div>
                            <div class="col-sm-2">
                                <select class="custom-select mr-sm-2" id="" name="curette">
                                    <option selected value="">Choose...</option>
                                    <option value="Y" <?=( $row['curette']=='Y'?  'selected' : '')?> >เคย</option>
                                    <option value="N" <?=( $row['curette']=='N'?  'selected' : '')?> >ไม่เคย</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-2"></div>
                            <div class="col-sm-2">
                                <label> ประจําเดือนครั้งสุดท้าย </label>
                            </div>
                            <div class="col-sm-2">
                                <input type="date" class="form-control form-control-sm" id="lmp" name="lmp"
                                        value="<?=(isset($row['lmp']) ? htmlspecialchars($row['lmp']) : '')?>">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-2"></div>
                            <div class="col-sm-2">
                                <label> กําหนดการคลอด </label>
                            </div>
                            <div class="col-sm-2">
                                <input type="date" class="form-control form-control-sm" id="edc" name="edc"
                                        value="<?=(isset($row['edc']) ? htmlspecialchars($row['edc']) : '')?>">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12">ประวัติการคลอด</label>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-2"></div>
                            <div class="custom-control custom-checkbox col-sm-2">
                                <input type="checkbox" <?php if ($row['pb_no'] == 'Y')  {echo 'checked="checked"';} ?>
                                    class="custom-control-input" id="pb_no" value="Y" name="pb_no"   >
                                <label class="custom-control-label" for="pb_no">ปฎิเสธ</label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-2"></div>
                            <div class="custom-control custom-checkbox col-sm-5">
                                <input type="checkbox" <?php if ($row['giant_baby'] == 'Y')  {echo 'checked="checked"';} ?>
                                    class="custom-control-input"  id="giant_baby" value="Y" name="giant_baby"   >
                                <label class="custom-control-label" for="giant_baby">เคยคลอดบุตร นน. > 4000 กรัม</label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-2"></div>
                            <div class="custom-control custom-checkbox col-sm-2">
                                <input type="checkbox" <?php if ($row['distocia'] == 'Y')  {echo 'checked="checked"';} ?>
                                    class="custom-control-input" id="distocia" value="Y" name="distocia"   >
                                <label class="custom-control-label" for="distocia">มีประวัติคลอดยาก</label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-2"></div>
                            <div class="custom-control custom-checkbox col-sm-3">
                                <input type="checkbox" <?php if ($row['extraction'] != null)  {echo 'checked="checked"';} ?>
                                    class="custom-control-input" id="extraction" onchange="extraction_check();">
                                <label class="custom-control-label" for="extraction">มีประวัติคลอดหัตถการ (ระบุ)</label>
                            </div>
                            <div class="col-sm-5">
                                <input type="text" class="form-control form-control-sm" id="extraction_text" name="extraction"
                                        value="<?=(isset($row['extraction']) ? htmlspecialchars($row['extraction']) : '')?>"
                                        <?php if ($row['extraction'] == null)  {echo 'disabled';} ?>>
                                        <script>
                                            function extraction_check(){
                                                    if (!($('#extraction').is(':checked'))) {$('#extraction_text').attr("disabled",true).val('');}
                                                    else{$('#extraction_text').attr("disabled",false).val('');}
                                            }
                                        </script>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-2"></div>
                            <div class="custom-control custom-checkbox col-sm-3">
                                <input type="checkbox" <?php if ($row['pph'] == 'Y')  {echo 'checked="checked"';} ?>
                                    class="custom-control-input" id="pph" value="Y" name="pph"   >
                                <label class="custom-control-label" for="pph">มีประวัติตกเลือดหลังคลอด</label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-2"></div>
                            <div class="custom-control custom-checkbox col-sm-3">
                                <input type="checkbox" <?php if ($row['pb_etc'] != null)  {echo 'checked="checked"';} ?>
                                    class="custom-control-input" id="pb_etc" onchange="pb_check();">
                                <label class="custom-control-label" for="pb_etc">อื่นๆ</label>
                            </div>
                            <div class="col-sm-5">
                                <input type="text" class="form-control form-control-sm" id="pb_etc_text" name="pb_etc"
                                        value="<?=(isset($row['pb_etc']) ? htmlspecialchars($row['pb_etc']) : '')?>"
                                        <?php if ($row['pb_etc'] == null)  {echo 'disabled';} ?>>
                                        <script>
                                            function pb_check(){
                                                    if (!($('#pb_etc').is(':checked'))) {$('#pb_etc_text').attr("disabled",true).val('');}
                                                    else{$('#pb_etc_text').attr("disabled",false).val('');}
                                            }
                                        </script>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12">ตรวจหน้าท้อง</label>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-2"></div>
                            <div class="col-sm-2">
                                <label> high of fundus</label>
                            </div>
                            <div class="col-sm-2">
                                <div class="input-group input-group-sm sm-3">
                                    <input type="text" class="form-control" id="hf" name="hf" maxlength="2" aria-describedby="inputGroup-sizing-sm"
                                            value="<?=(isset($row['hf']) ? htmlspecialchars($row['hf']) : '')?>">
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="inputGroup-sizing-sm">  cm.  </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-2"></div>
                            <div class="col-sm-2">
                                <label> position </label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" class="form-control"id="hf_position" name="hf_position"
                                        value="<?=(isset($row['hf_position']) ? htmlspecialchars($row['hf_position']) : '')?>">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12">อาการระหว่างตั้งครรภ์</label>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-2"></div>
                            <div class="custom-control custom-radio col-sm-2">
                                <input type="radio" <?php if ($row['condition_pregnant'] == 'ปกติ')  {echo 'checked="checked"';} ?>
                                    class="custom-control-input" id="a1" name="condition_pregnant" value="ปกติ"    onchange="custom_check('off_condition');">
                                <label class="custom-control-label" for="a1">ปกติ</label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-2"></div>
                            <div class="custom-control custom-radio col-sm-2">
                                <input type="radio" <?php if ($row['condition_pregnant'] != 'ปกติ'
                                                            &&$row['condition_pregnant'] != NULL
                                                            &&$row['condition_pregnant'] != '')  {echo 'checked="checked"';} ?>
                                    class="custom-control-input" id="a2" name="condition_pregnant" value="ผิดปกติ" onchange="custom_check('on_condition');">
                                <label class="custom-control-label" for="a2"> ผิดปกติ (ระบุ)</label>
                            </div>
                            <div class="col-sm-8">
                                <textarea class="form-control" rows="3" id="a3" name="condition_pregnant_text"><?php if (!($row['condition_pregnant'] == 'ปกติ')) {echo htmlspecialchars($row['condition_pregnant']);} ?></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-2"></div>
                            <div class="col-sm-1">
                                <label> HIV </label>
                            </div>
                            <div class="col-sm-2">
                                <select class="custom-select mr-sm-2" id="hiv" name="hiv">
                                    <option selected value="">Choose...</option>
                                    <option value="Negative" <?=( $row['hiv']=='Negative'?  'selected' : '')?> >Negative</option>
                                    <option value="P"        <?=( $row['hiv']=='P'?  'selected' : '')?>        >P</option>
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <select class="custom-select mr-sm-2" id="hiv2" name="hiv2">
                                    <option selected value="">Choose...</option>
                                    <option value="Negative" <?=( $row['hiv2']=='Negative'?  'selected' : '')?> >Negative</option>
                                    <option value="P"        <?=( $row['hiv2']=='P'?  'selected' : '')?>        >P</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-2"></div>
                            <div class="col-sm-1">
                                <label> VDRL </label>
                            </div>
                            <div class="col-sm-2">
                                <select class="custom-select mr-sm-2"  id="vdrl" name="vdrl">
                                    <option selected value="">Choose...</option>
                                    <option value="Reaxtive"       <?=( $row['vdrl']=='Reaxtive'?  'selected' : '')?>      >Reactive</option>
                                    <option value="Non reactiive"  <?=( $row['vdrl']=='Non reactiive'?  'selected' : '')?> >Non reactive</option>
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <select class="custom-select mr-sm-2"  id="vdrl2" name="vdrl2">
                                    <option selected value="">Choose...</option>
                                    <option value="Reaxtive"       <?=( $row['vdrl2']=='Reaxtive'?  'selected' : '')?>      >Reactive</option>
                                    <option value="Non reactiive"  <?=( $row['vdrl2']=='Non reactiive'?  'selected' : '')?> >Non reactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-2"></div>
                            <div class="col-sm-1">
                                <label> HBsAg</label>
                            </div>
                            <div class="col-sm-2">
                                <select class="custom-select mr-sm-2" id="hbs_ag" name="hbs_ag">
                                    <option selected value="">Choose...</option>
                                    <option value="Negative" <?=( $row['hbs_ag']=='Negative'?  'selected' : '')?> >Negative</option>
                                    <option value="Positive" <?=( $row['hbs_ag']=='Positive'?  'selected' : '')?> >Positive</option>
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <select class="custom-select mr-sm-2" id="hbs_ag2" name="hbs_ag2">
                                    <option selected value="">Choose...</option>
                                    <option value="Negative" <?=( $row['hbs_ag2']=='Negative'?  'selected' : '')?> >Negative</option>
                                    <option value="Positive" <?=( $row['hbs_ag2']=='Positive'?  'selected' : '')?> >Positive</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-2"></div>
                            <div class="col-sm-1">
                                <label> HCT </label>
                            </div>
                            <div class="col-sm-2">
                                <div class="input-group input-group-sm sm-3">
                                    <input type="text" class="form-control" id="hct" name="hct" maxlength="4" aria-describedby="inputGroup-sizing-sm"
                                        value="<?=(isset($row['hct']) ? htmlspecialchars($row['hct']) : '')?>" >
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="inputGroup-sizing-sm"> %  </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="input-group input-group-sm sm-3">
                                    <input type="text" class="form-control" id="hct2" name="hct2" maxlength="4" aria-describedby="inputGroup-sizing-sm"
                                        value="<?=(isset($row['hct2']) ? htmlspecialchars($row['hct2']) : '')?>" >
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="inputGroup-sizing-sm"> %  </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-2"></div>
                            <div class="col-sm-2">
                                <label> Blood group </label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" class="form-control"id="gr" name="gr"
                                        value="<?=(isset($row['gr']) ? htmlspecialchars($row['gr']) : '')?>">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-2"></div>
                            <div class="col-sm-2">
                                <label> ผล thalassemia ตัวเอง </label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" class="form-control"id="thalassemia" name="thalassemia"
                                        value="<?=(isset($row['thalassemia']) ? htmlspecialchars($row['thalassemia']) : '')?>">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-2"></div>
                            <div class="col-sm-2">
                                <label> ผล thalassemia สามี </label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" class="form-control"id="husband" name="husband"
                                        value="<?=(isset($row['husband']) ? htmlspecialchars($row['husband']) : '')?>">
                            </div>
                        </div><hr>
<!-- display:none --></div>

<!-- display:none --><div style="<?php if (($row_ipt['age_y'] >  15 && $row_ipt['age_m'] >= 0 && $row_ipt['age_d'] >= 0)
                                        || ($row_ipt['age_y'] == 15 && $row_ipt['age_m'] >  0 && $row_ipt['age_d'] >  0)){echo 'display:none;';}?>">

                        <div class="form-group row">
                            <label class="col-sm-12">วิธีคลอด</label>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <div class="custom-control custom-radio col-sm-2">
                                <input type="radio" <?php if ($row['deliver_anomalies'] == 'ปกติ')  {echo 'checked="checked"';} ?>
                                    class="custom-control-input" id="s1" name="deliver_anomalies" value="ปกติ" onchange="custom_check('off_deliver');">
                                <label class="custom-control-label" for="s1">ปกติ</label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <div class="custom-control custom-radio col-sm-2">
                                <input type="radio" <?php if ($row['deliver_anomalies'] != 'ปกติ'
                                                            && $row['deliver_anomalies'] != null
                                                            && $row['deliver_anomalies'] != '')  {echo 'checked="checked"';} ?>
                                    class="custom-control-input" id="s2" name="deliver_anomalies" value="ผิดปกติ" onchange="custom_check('on_deliver');">
                                <label class="custom-control-label" for="s2"> ผิดปกติ (ระบุ)</label>
                            </div>
                            <div class="col-sm-3">
                                <input type="text" class="form-control form-control-sm" id="s3"
                                value="<?php if (!($row['deliver_anomalies'] == 'ปกติ')) {echo htmlspecialchars($row['deliver_anomalies']);} ?>" name="deliver_anomalies_text">
                            </div>
                            <label class="text-right col-sm-2">เนื่องจาก</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control form-control-sm" id="s4"
                                value="<?=(isset($row['deliver_anomalies_means']) ? htmlspecialchars($row['deliver_anomalies_means']) : '')?>" name="deliver_anomalies_means">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="text-right col-sm-3">คลอดที่</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control form-control-sm" id="s5"
                                value="<?=(isset($row['deliver_location']) ? htmlspecialchars($row['deliver_location']) : '')?>" name="deliver_location">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="text-right col-sm-3">น้ำหนักแรกคลอด</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control form-control-sm" id="s6"
                                value="<?=(isset($row['deliver_first_weight']) ? htmlspecialchars($row['deliver_first_weight']) : '')?>" name="deliver_first_weight">
                            </div>
                            <label class="col-sm-2">กรัม</label>
                        </div>
                        <div class="form-group row">
                            <label class="text-right col-sm-3">สุขภาพแรกเกิด</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control form-control-sm" id="s7"
                                value="<?=(isset($row['deliver_first_health']) ? htmlspecialchars($row['deliver_first_health']) : '')?>" name="deliver_first_health">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12">การเลี้ยงทารก</label>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <div class="custom-control custom-checkbox col-sm-2">
                                <input type="checkbox" <?php if ($row['fant_breast_feeding_end_age_month'] != null)  {echo 'checked="checked"';} ?>
                                    class="custom-control-input" id="d1" name="fant_feeding" value="นมมารดา"   >
                                <label class="custom-control-label" for="d1">นมมารดา ถึงอายุ</label>
                            </div>
                            <div class="col-sm-2">
                                <input type="text" class="form-control form-control-sm" id=""
                                value="<?=(isset($row['fant_breast_feeding_end_age_month']) ? htmlspecialchars($row['fant_breast_feeding_end_age_month']) : '')?>" name="fant_breast_feeding_end_age_month">
                            </div>
                            <label class="col-sm-2">เดือน</label>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <div class="custom-control custom-checkbox col-sm-2">
                                <input type="checkbox" <?php if ($row['fant_artificial_feeding_start_age_month'] != null)  {echo 'checked="checked"';} ?>
                                    class="custom-control-input" id="d2" name="fant_feeding" value="นมผสม"   >
                                <label class="custom-control-label" for="d2">นมผสม เริ่มอายุ</label>
                            </div>
                            <div class="col-sm-2">
                                <input type="text" class="form-control form-control-sm" id="" name="fant_artificial_feeding_start_age_month"
                                value="<?=(isset($row['fant_artificial_feeding_start_age_month']) ? htmlspecialchars($row['fant_artificial_feeding_start_age_month']) : '')?>">
                            </div>
                            <label class="col-sm-2">เดือน</label>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <div class="custom-control custom-checkbox col-sm-2">
                                <input type="checkbox" <?php if ($row['fant_feeding_etc'] != null)  {echo 'checked="checked"';} ?>
                                    class="custom-control-input" id="d3" name="fant_feeding"   >
                                <label class="custom-control-label" for="d3">อื่นๆ</label>
                            </div>
                            <div class="col-sm-4">
                                <input type="text" class="form-control form-control-sm" id="" name="fant_feeding_etc"
                                value="<?=(isset($row['fant_feeding_etc']) ? htmlspecialchars($row['fant_feeding_etc']) : '')?>">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12">การให้อาหารเสริม</label>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <div class="custom-control custom-radio col-sm-2">
                                <input type="radio" <?php if ($row['supplementary_feeding'] != 'ได้รับ'
                                                            && $row['supplementary_feeding'] != NULL
                                                            && $row['supplementary_feeding'] != '')  {echo 'checked="checked"';} ?>
                                    class="custom-control-input" id="f1" name="supplementary_feeding"  value="ยังไม่ได้รับ">
                                <label class="custom-control-label" for="f1">ยังไม่ได้รับ</label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <div class="custom-control custom-radio col-sm-2">
                                <input type="radio" <?php if ($row['supplementary_feeding'] == 'ได้รับ')  {echo 'checked="checked"';} ?>
                                    class="custom-control-input" id="f2" name="supplementary_feeding"  value="ได้รับ"   >
                                <label class="custom-control-label" for="f2">ได้รับ เริ่มอายุ</label>
                            </div>
                            <div class="col-sm-2">
                                <input type="text" class="form-control form-control-sm" id="" name="supplementary_feeding_start_age_month"
                                value="<?=(isset($row['supplementary_feeding_start_age_month']) ? htmlspecialchars($row['supplementary_feeding_start_age_month']) : '')?>">
                            </div>
                            <label class="col-sm-2">เดือน</label>
                        </div>
<!-- none display  --></div>
                        <div class="form-group row">
                            <label class="col-sm-12">การเข้ารับการรักษาในโรงพยาบาล</label>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <div class="custom-control custom-radio col-sm-2">
                                <input type="radio"
                                    <?php   if ($admission_note_id != null){
                                                if ($row['inpatient_history'] == "ไม่เคย")  {echo 'checked="checked"';}
                                                if (isset($row_old_ipt['old_regdatetime']) != null){echo 'disabled="disabled"';}
                                            }else{
                                                if (!isset($row_old_ipt['old_regdatetime']))  {echo 'checked="checked"';}
                                                if (isset($row_old_ipt['old_regdatetime']) != null){echo 'disabled="disabled"';}
                                            }?>
                                    class="custom-control-input" id="h1" name="inpatient_history" value="ไม่เคย" onchange="custom_check('off_inpatient');">
                                <label class="custom-control-label" for="h1">ไม่เคย</label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <div class="custom-control custom-radio col-sm-1">
                                <input type="radio"
                                    <?php   if ($admission_note_id != null){
                                                if ($row['inpatient_history'] == "เคย")  {echo 'checked="checked"';}
                                            }else{
                                                if (isset($row_old_ipt['old_regdatetime']))  {echo 'checked="checked"';}
                                            }?>
                                    class="custom-control-input" id="h2" value="เคย" name="inpatient_history" onchange="custom_check('on_inpatient');">
                                <label class="custom-control-label" for="h2">เคย</label>
                            </div>
                            <label class="text-right col-sm-2">ครั้งสุดท้ายเมื่อ</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control form-control-sm" id="h3" name="inpatient_last_date"
                                value="<?php    if ($admission_note_id != null){
                                                    echo htmlspecialchars($row['inpatient_last_date']);
                                                }else{
                                                    if (isset($row_old_ipt['old_regdatetime'])) {echo htmlspecialchars($row_old_ipt['old_regdatetime']);}
                                                }?>">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="text-right col-sm-4">เนื่องจาก</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control form-control-sm" id="h5" name="inpatient_because"
                                value="<?=(isset($row['inpatient_because']) ? htmlspecialchars($row['inpatient_because']) : '')?>">
                            </div>
                            </div>
    <!-- display:none --></div>
                    </div>
                </div>
            </div>

<!-- card --><div class="card-group pb-3 ">
<!-- card --><div class="card">
<!-- card --><div class="card-body" style=" overflow-y: auto;">

<div class="alert alert-success text-center col-sm-12" role="alert">Physical examination</div>
        <div class="form-group row">
            <div class="col-sm-12">
                <div class="card border-success">
                    <div class="card-body">
                        <div class="form-group row">
                            <div class="col-sm-10"></div>
                            <div class="col-sm-2"><i class="fas fa-user"></i> ผู้ใหญ่       <i class="fas fa-baby"></i> เด็ก</div>
                        </div>
                        <div class="form-group row">
                            <label class="text-right col-sm-3">General</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="<?=(isset($row_opdscreen['pe_ga_text']) && $admission_note_id == null ? htmlspecialchars($row_opdscreen['pe_ga_text']) : htmlspecialchars($row['pe_general']))?>" id="pe_general" name="pe_general">
                            </div>

                            <div class="col-md-2">

                            <a class="btn btn-secondary btn-sm PhysicalExaminationBtn" href="#" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false"><em class="fas fa-user"></em> General</a>
                        <div class="dropdown-menu" aria-labelledby="dropdownId">
                 <?php
                 //  General
                     $sql_phy = "SELECT pe_general_text,name_select
                    FROM ".DbConstant::KPHIS_DBNAME.".prs_phy_exam_list where exam_group_id = '1' and type_of_person = 'A' and active_ = 'Y' and user_ in('master','$loginname')";
        $stmt_phy = $conn->prepare($sql_phy);
        $stmt_phy->execute($reg_parameters);
        $rows_phy  = $stmt_phy->fetchAll();
      //  $operation_text = "";
            foreach ($rows_phy as $ds) {
                ?>
                <a class="dropdown-item" onclick="onclick_Normal('pe_general','<?= $ds['pe_general_text'] ?>')"> <?= $ds['name_select'] ?></a>
                <?php
                 $i++;
            }

            ?>

                    </div>

                    <span>
                    <a class="btn btn-secondary btn-sm PhysicalExaminationBtn" href="#"  data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-baby" aria-hidden="true"></i> General</a>
                    <div class="dropdown-menu" aria-labelledby="DropdownId">

                    <?php
                 //  General
                     $sql_phy = "SELECT pe_general_text,name_select
                    FROM ".DbConstant::KPHIS_DBNAME.".prs_phy_exam_list where exam_group_id = '1' and type_of_person = 'B' and active_ = 'Y' and user_ in('master','$loginname')";
        $stmt_phy = $conn->prepare($sql_phy);
        $stmt_phy->execute($reg_parameters);
        $rows_phy  = $stmt_phy->fetchAll();
      //  $operation_text = "";
            foreach ($rows_phy as $ds) {
                ?>
                <a class="dropdown-item" onclick="onclick_Normal('pe_general','<?= $ds['pe_general_text'] ?>')"> <?= $ds['name_select'] ?></a>
                <?php
                 $i++;
            }

            ?>


                    </div>
                </span>








                </div>

                        </div>

                        <div class="form-group row">
                            <label class="text-right col-sm-3">Skin</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="<?=(isset($row['pe_skin']) ? htmlspecialchars($row['pe_skin']) : '')?>" id="pe_skin" name="pe_skin">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_skin','no rash, not pale, no jaundice')"><i class="fas fa-user"></i> Normal</button>
                                <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_skin','Pink, no rash')"><i class="fas fa-baby"></i> Normal</button>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="text-right col-sm-3">HEENT</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="<?=(isset($row_opdscreen['pe_heent_text']) && $admission_note_id == null ? htmlspecialchars($row_opdscreen['pe_heent_text']) : htmlspecialchars($row['pe_heent']))?>" id="pe_heent" name="pe_heent">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_heent','not pale conjunctiva, no icteric sclera')"><i class="fas fa-user"></i> Normal</button>
                                <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_heent','AF 2*2 cm, no cephalhematoma')"><i class="fas fa-baby"></i> Normal</button>
                            </div>
                        </div>
                        <!--
                        <div class="form-group row">
                            <label class="text-right col-sm-3">Neck</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="" id="pe_neck" name="pe_neck">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_neck','no mass, no LN enlargement, full ROM')"><i class="fas fa-user"></i> Normal</button>
                                <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_neck','No webbed neck')"><i class="fas fa-baby"></i> Normal</button>
                            </div>
                        </div>
        -->
<!--                        <div class="form-group row">
                            <label class="text-right col-sm-3">Breast & Thorax</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="" id="pe_breastthorax" name="pe_breastthorax">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_breastthorax','no mass')"><i class="fas fa-user"></i> Normal</button>
                                <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_breastthorax','Normal chest contour')"><i class="fas fa-baby"></i> Normal</button>
                            </div>
                        </div>
        -->
         <!--               <div class="form-group row">
                            <label class="text-right col-sm-3">Heart</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="" id="pe_heart" name="pe_heart">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_heart','normal S1 S2, no murmur')"><i class="fas fa-user"></i> Normal</button>
                                <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_heart','No murmur')"><i class="fas fa-baby"></i> Normal</button>
                            </div>
                        </div>
        -->
                        <div class="form-group row">
                            <label class="text-right col-sm-3">Lungs</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="<?=(isset($row_opdscreen['pe_lung_text']) && $admission_note_id == null ? htmlspecialchars($row_opdscreen['pe_lung_text']) : htmlspecialchars($row['pe_lungs']))?>" id="pe_lungs" name="pe_lungs">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_lungs','equal breath sounds both lung, clear, no wheezing')"><i class="fas fa-user"></i> Normal</button>
                                <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_lungs','No adventitious sound, no subcostal retraction')"><i class="fas fa-baby"></i> Normal</button>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="text-right col-sm-3">CVS</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="<?=(isset($row['pe_cvs']) ? htmlspecialchars($row['pe_cvs']) : '')?>" id="pe_cvs" name="pe_cvs">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_cvs','Regular rhythm Normal s1 s2 no murmur Normal PMI')"><i class="fas fa-user"></i> Normal</button>
                                <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_cvs','No hepatosplenomegaly')"><i class="fas fa-baby"></i> Normal</button>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="text-right col-sm-3">Abdomen</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="<?=(isset($row_opdscreen['pe_ab_text']) && $admission_note_id == null ? htmlspecialchars($row_opdscreen['pe_ab_text']) : htmlspecialchars($row['pe_abdomen']))?>" id="pe_abdomen" name="pe_abdomen">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_abdomen','soft, not tender,  no mass palpation')"><i class="fas fa-user"></i> Normal</button>
                                <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_abdomen','No hepatosplenomegaly')"><i class="fas fa-baby"></i> Normal</button>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="text-right col-sm-3">Rectal&Genitalia</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="<?=(isset($row['pe_rectalgenitalia']) ? htmlspecialchars($row['pe_rectalgenitalia']) : '')?>" id="pe_rectalgenitalia" name="pe_rectalgenitalia">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_rectalgenitalia','PR no mass , Good sphincter tone , normal genital appearance')"><i class="fas fa-user"></i> Normal</button>
                                <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_rectalgenitalia','Patent anus, no ambiguous genitalia')"><i class="fas fa-baby"></i> Normal</button>
                            </div>
                        </div>


                        <div class="form-group row">
                            <label class="text-right col-sm-3">Extremities</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="<?=(isset($row_opdscreen['pe_ext_text']) && $admission_note_id == null ? htmlspecialchars($row_opdscreen['pe_ext_text']) : htmlspecialchars($row['pe_extremities']))?>" id="pe_extremities" name="pe_extremities">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_extremities','No deformity No edema')"><i class="fas fa-user"></i> Normal</button>
                                <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_extremities','No deformity')"><i class="fas fa-baby"></i> Normal</button>
                            </div>
                        </div>


                        <div class="form-group row">
                            <label class="text-right col-sm-3">CNS</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="<?=(isset($row['pe_cns']) ? htmlspecialchars($row['pe_cns']) : '')?>" id="pe_cns" name="pe_cns">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_cns','E4V5N6 Pupil 3 mm RTLBE Motor and Sensory grossly intact')"><i class="fas fa-user"></i> Normal</button>
                                <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_cns','No hepatosplenomegaly')"><i class="fas fa-baby"></i> Normal</button>
                            </div>
                        </div>
                        <!--
                        <div class="form-group row">
                            <label class="text-right col-sm-3">Neurological</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="" id="pe_neurological" name="pe_neurological">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_neurological','E4V5M6, Grade V all extremities')"><i class="fas fa-user"></i> Normal</button>
                                <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_neurological','Moro reflex positive')"><i class="fas fa-baby"></i> Normal</button>
                            </div>
                        </div>
        -->
                        <div class="form-group row">
                            <label class="text-right col-sm-3">OB/Gyn exam</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="<?=(isset($row['pe_ob_gynexam']) ? htmlspecialchars($row['pe_ob_gynexam']) : '')?>" id="pe_ob_gynexam" name="pe_ob_gynexam">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-secondary btn-sm PhysicalExaminationBtn" onclick="onclick_Normal('pe_ob_gynexam','no mass , no discharge')"><i class="fas fa-user"></i> Normal</button>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="text-right col-sm-3">Other</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="<?=(isset($row['pe_other']) ? htmlspecialchars($row['pe_other']) : '')?>" id="" name="pe_other">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="text-right col-sm-3">PE Text</label>
                            <div class="col-sm-7">
                                <textarea class="form-control" id="pe_text" name="pe_text" rows="6" ><?=(isset($row_opdscreen['pe']) && $admission_note_id == null ? htmlspecialchars($row_opdscreen['pe']) : htmlspecialchars($row['pe_text']))?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-sm-12">
                <div class="card border-success">
                    <div class="card-body">
                        <h5 class="card-title"></h5>
                        <!-- <div class="text-right">
                            <button type="button" class="btn btn-outline-success" onclick="onclick_img_button()"><i class='fas fa-edit'></i> แก้ไขรูปภาพ</button>
                        </div> -->
                        <div class="col-sm-8 offset-sm-3 mb-3">
                            <div id="show_img_select"  class="text-center">
                                <canvas id="c" width="700" height="500"></canvas>
                            </div>
                        </div>
                        <div class="col-sm-11 offset-sm-1" style="display: inline-block;">
                            <div class="text-center">
                                <a href="javascript:undo();"><button id="" type="button" class="btn btn-outline-danger PhysicalExaminationBtn"><i class="far fa-arrow-alt-circle-left"></i> Undo</button></a>
                                <a href="javascript:redo();"><button id="" type="button" class="btn btn-outline-danger PhysicalExaminationBtn">Redo <i class="far fa-arrow-alt-circle-right"></i></button></a>
                                <button id="clear-canvas" type="button" class="btn btn-outline-danger PhysicalExaminationBtn" onclick="onclick_clear()"><i class='fas fa-edit'></i> Clear</button>
                            </div>
                        </div>
                        <div>
                            <div class="form-group">
                                <label for=""></label>
                                <textarea style="display:none;" class="form-control" name="svg_tag" id="svg_tag" rows="10"><?=(isset($row['svg_tag']) ? htmlspecialchars($row['svg_tag']) : '')?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="form-group row">
                            <div class="col-sm-9">
                            <div class="form-group row">
                                            <label class="text-right col-sm-3">Problemlist</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="<?= (isset($row['problem_list']) ? htmlspecialchars($row['problem_list']) : '') ?>" id="problem_list" name="problem_list">
                                            </div>
                                        </div>
                                <div class="form-group row">
                                    <label class="text-right col-sm-3">Impression</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="<?=(isset($row['impression']) ? htmlspecialchars($row['impression']) : '')?>" id="" name="impression">
                                    </div>
                                </div>
<!--
                                <div class="form-group row">
                                    <label class="text-right col-sm-3">Diff. Dx</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="" id="" name="diff_dx">
                                    </div>
                                </div>
        -->
                                <div class="form-group row">
                                    <label class="text-right col-sm-3">Plan Management</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control form-control-sm PhysicalExaminationInput" value="<?=(isset($row['plan_management']) ? htmlspecialchars($row['plan_management']) : '')?>" id="" name="plan_management">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="action-person-dr-admission">ลงชื่อแพทย์</label>
                    <button type="button"  class="btn btn-secondary btn-sm mb-2" onclick="AddDoctorSignature()"><i class="fas fa-plus"></i> ลงชื่อ</button>
                    <div id="dr-admission-group-input-div">
                    <template id="template_dr_admission_input_div">
                        <div class="dr_admission_input_div">
                            <div class="input-group mb-2">
                                <input type="hidden" class="form-control form-control" name="admission_note_doctor[]">
                                <input type="text" class="form-control form-control" name="doc_name[]" readonly>
                                <!-- <input type="text" class="form-control form-control" name="doc_pos[]" readonly> -->
                            </div>
                        </div>
                    </template>
                        <?php $start_count = 0; while ($start_count < $admission_note_count) {?>
                        <div class="dr_admission_input_div">
                            <div class="input-group mb-2">
                                <input type="hidden" class="form-control form-control" name="admission_note_doctor[]" value="<?=$admission_note_doctor[$start_count]?>">
                                <input type="text" class="form-control form-control" name="doc_name[]" value="<?=$admission_note_doctorname[$start_count]?>" readonly>
                                <!-- <input type="text" class="form-control form-control" name="doc_pos[]"  value="<?=$admission_note_doctorentryposition[$start_count]?>" readonly> -->
                            </div>
                        </div>
                        <?php $start_count++;} ?>
                    </div>
                </div>
            </div>

                                            
            <div class="col-md-4">
                <div class="form-group">
                    <label class="mb-3" for="action-person-nurse">ลงชื่อพยาบาล</label>
                    <div class="input-group">
                        <input type="text" class="form-control form-control" id="nurse_name"  name="nurse_name"  value="<?=htmlspecialchars($row['nurse_name'])?>" readonly>
                        <input type="text" class="form-control form-control" id="nurse_pos"   name="nurse_pos"   value="<?=htmlspecialchars($row['nurse_pos'])?>" readonly>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-secondary" onclick="PersonAsCurrentUser_1()">ลงชื่อ</button>
                        </div>
                    </div>
                </div>
            </div>
                        

        </div>
        <div class="form-group row">
            <!-- <div class="col-sm-4 text-left">
                <label class="text-right"><?=(isset($row['doc_name']) ? 'แพทย์ผู้บันทึก   '.htmlspecialchars($row['doc_name']) : '')?></label><br>
                <label class="text-right"> <?=(isset($row['doc_pos']) ? htmlspecialchars($row['doc_pos']) : '')?></label>
            </div>
            <div class="col-sm-4 text-left">
                <label class="text-right"><?=(isset($row['nurse_name']) ? 'พยาบาลผู้บันทึก  '.htmlspecialchars($row['nurse_name']) : '')?></label><br>
                <label class="text-right"> <?=(isset($row['nurse_pos']) ? htmlspecialchars($row['nurse_pos']) : '')?></label>
            </div> -->
            <div class="col-sm-12 text-right">
                <a href="ipd-dr-admission-note-pdf.php?an=<?php echo $an;?>&admission_note_id=<?php echo $admission_note_id;?>" target="_blank" class="btn btn-secondary"><i class="fas fa-file-pdf"></i> Print <U>PDF</U> File</a>
                <?php
                //รอแก้ไข
               // $a = 1;
                if(Session::checkPermission('ADMISSION_NOTE','EDIT') ){
                ?>
                    <button type="button" class="btn btn-primary" onclick="admission_save()">บันทึก</button>
                <?php
                }
                ?>
            </div>
        </div>
<!-- card --></div>
<!-- card --></div>
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
</script>
