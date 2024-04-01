<?php  // require_once './project/function/Session.php';
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
require_once '../mains/main-report.php';

Session::checkLoginSessionAndShowMessage(); //เช็ค session
Session::checkPermissionAndShowMessage('IPD_NURSE_ADDMISSION_NOTE', 'VIEW');
require_once '../mains/ipd-show-patient-main.php'; //เป็นส่วนที่แสดง ข้อมูลผู้ป่วย เช่น รูป,hn,an,ชื่อ-สกุล,แพ้ยา ฯลฯ
require_once '../mains/ipd-show-patient-sticky.php';

require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';
$conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
$an = $_REQUEST['an']; //รับค่า an
$hn = KphisQueryUtils::getHnByAn($an); // function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา
$vn = KphisQueryUtils::getVnByAn($an);

Session::insertSystemAccessLog(json_encode(array(
    'form'=>'LR-REPORT1-FORM',
    'an'=>$an,
),JSON_UNESCAPED_UNICODE));

/*$login = empty($_REQUEST['loginname']) ? null : $_REQUEST['loginname'];
$loginname = $_SESSION['loginname'];
$values = ['loginname' => $loginname];
if ($login != $loginname) {
    session_start();
    session_destroy();
}*/

// echo $an;

//----------------------เช็คว่า an นี้ มีข้อมูลหรือไม่
$sql = "SELECT count(*) AS count_row, id FROM " . DbConstant::KPHIS_DBNAME . ".prs_labor_report1 WHERE an = :an ";
$id  = null;
$parameters['an'] = $an;
$stmt = $conn->prepare($sql);
$stmt->execute($parameters);
$row = $stmt->fetch();
if ($row['count_row'] > 0) {
    $id = $row['id'];
}

if ($id == null || $id != null) {
    $sql_opdscreen = "SELECT opdscreen.vn,opdscreen.hn,opdscreen.cc,opdscreen.hpi,concat(round(opdscreen.bpd,0),'/',round(opdscreen.bps,0)) as bp,
                                    pt.sex,round(opdscreen.bps,0) as sbp,round(opdscreen.bpd,0) as dbp,
                                    round(opdscreen.pulse,0) as pr,round(opdscreen.rr,0) as rr,round(opdscreen.temperature,1) as bt,
                                    round((opdscreen.bw)*1000,0) as bw2,
                                    round(opdscreen.bw,1) as bw,round(opdscreen.height,1) as height,
                                    opdscreen.pe_ga_text, opdscreen.pe_heent_text,opdscreen.fh,
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

//----------------------เช็คว่า an นี้ มีข้อมูลหรือไม่

date_default_timezone_set('asia/bangkok');


?>

<style>
    .main {
        border: 1px solid #4287f5;
        height: 180px;
        width: 500px;
        position: relative;
    }

    .column1 {
        color: #4287f5;
        text-align: center;
    }

    .column2 {
        text-align: center;
    }

    #bottom {
        position: absolute;
        bottom: 0;
        left: 0;
    }

    .top-container {
        background-color: #f1f1f1;
        padding: 30px;
        text-align: center;
    }

    .header {
        padding: 10px 16px;
        background: #555;
        color: #f1f1f1;
    }

    .content {
        padding: 16px;
    }

    .sticky {
        position: fixed;
        top: 0;
        width: 100%;
    }

    .sticky+.content {
        padding-top: 102px;
    }
</style>




<form id="lr_report1_form">
    <div class="container-fluid">
        <div class="row">
            <div class="col-auto">
                <button type="button" class="btn btn-sm btn-primary btn-block" onclick="window.close()"><i class="fas fa-arrow-left"></i> กลับ</button>
            </div>
            <div class="col-auto p-1 font-weight-bold">
                ใบบันทึกประวัติและประเมินสมรรถนะผู้ป่วยแรกรับ (เด็กแรกเกิด) <?= htmlspecialchars(DbConstant::HOSPITAL_NAME) ?>
            </div>
            <label class="col-sm-7 text-right">FM-OBS-004 แก้ไขครั้งที่ 01 ประกาศใช้ 15 กรกฎาคม 2562</label>
        </div>


        <div class="card-group pb-3 ">
            <div class="card">
                <div class="card-body" style=" overflow-y: auto;">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group row">
                                <label class="col-sm-12"><B>ข้อมูลทั่วไป</B></label>
                            </div>
                            <div class="row">



                                <div class="col-sm-1"></div>
                                <label>รับใหม่วันที่</label>
                                <div class="col-sm-2">
                                    <input type="date" class="form-control form-control-sm" id="receive_date" name="receive_date" value="<?= (isset($row_ipt['regdate']) && $id == null ? htmlspecialchars($row_ipt['regdate']) : htmlspecialchars($row['receive_date'])) ?>">
                                </div>
                                <label>เวลา</label>
                                <div class="col-sm-2">
                                    <input type="time" class="form-control form-control-sm" id="receive_time" name="receive_time" value="<?= (isset($row_ipt['regtime']) && $id == null ? htmlspecialchars($row_ipt['regtime']) : htmlspecialchars($row['receive_time'])) ?>">
                                </div>

                                <div class="custom-control custom-radio col-sm-5">
                                    <label>จาก</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if (
                                                                                                                        $row['receive_from'] == 'คลอดในโรงพยาบาล'
                                                                                                                        || $row['receive_from'] == NULL
                                                                                                                    ) {
                                                                                                                        echo 'checked="checked"';
                                                                                                                    } ?> class="custom-control-input" id="receive_from1" name="receive_from" value="คลอดในโรงพยาบาล" onchange="custom_check('off_entered');">
                                    <label class="custom-control-label" for="receive_from1">คลอดในโรงพยาบาล</label>

                                </div>
                                <br>

                                &nbsp;<div class="custom-control custom-radio col-sm-2">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    <input type="radio" <?php if ($row['receive_from'] != 'คลอดในโรงพยาบาล' && $row['receive_from'] != NULL) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="receive_from2" onchange="custom_check('on_entered');">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    &nbsp;&nbsp;&nbsp;&nbsp;
                                    <label class="custom-control-label" for="receive_from2"> ระบุ... </label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="from_text" name="receive_from" value="<?php if ($row['receive_from'] != 'คลอดในโรงพยาบาล' && $row['receive_from'] != NULL) {
                                                                                                                                            echo htmlspecialchars($row['receive_from']);
                                                                                                                                        } ?>" <?php if (!($row['receive_from'] != 'คลอดในโรงพยาบาล' && $row['receive_from'] != NULL)) {
                                                                                                                                                    echo 'disabled';
                                                                                                                                                } ?>>
                                </div>


                            </div>

                            <br>





                            <div class="row">
                                <label class="col-sm-2 text-left"><B>รับไว้ในโรงพยาบาลโดย</B></label>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" class="custom-control-input" id="transport1" value="อุ้มมา" name="transport">
                                    <label class="custom-control-label" for="transport1">อุ้มมา</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" class="custom-control-input" id="transport2" value="transport incubator" name="transport">
                                    <label class="custom-control-label" for="transport2">transport incubator</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" class="custom-control-input" id="transport3" value="clib" name="transport">
                                    <label class="custom-control-label" for="transport3">clib</label>
                                </div>

                            </div>



                            <div class="form-group row">
                                <label class="col-sm-12"><B> อาการสำคัญที่นำมาโรงพยาบาล</B></label>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <textarea class="form-control" id="cc" name="cc" rows="4"><?= (isset($row_opdscreen['cc']) && $id == null ? htmlspecialchars($row_opdscreen['cc']) : htmlspecialchars($row['cc'])) ?></textarea>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-12"><B> HPI </B></label>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <textarea class="form-control" id="hpi" name="hpi" rows="4"><?= (isset($row_opdscreen['hpi']) && $id == null ? htmlspecialchars($row_opdscreen['hpi']) : htmlspecialchars($row['hpi'])) ?></textarea>
                                </div>
                            </div>

                            <div class="row">
                                <label class="col-sm-2 text-left"><B>ประวัติการเจ็บป่วยปัจจุบัน</B></label>

                                <label>GA</label>
                                <div class="col-md-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="0" name="ga" id="ga">
                                </div> &nbsp;<label>wks</label> &nbsp;&nbsp;<label>คลอดวิธี</label>
                                <div class="col-md-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxxxxxxx" name="labor" id="labor">
                                </div>&nbsp;&nbsp;<label>indication</label>
                                <div class="col-md-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxxxxxxx" name="indication" id="indication">
                                </div>


                            </div>
                            <br>
                            <div class="row">
                                <div class="col-sm-1"></div>
                                <label>วันที่</label>
                                <div class="col-sm-2">
                                    <input type="date" class="form-control form-control-sm" id="labor_date" name="labor_date" value="<?= (isset($row['labor_date']) ? htmlspecialchars($row['labor_date']) : '') ?>">
                                </div>
                                <label>เวลา</label>
                                <div class="col-sm-2">
                                    <input type="time" class="form-control form-control-sm" id="labor_time" name="labor_time" value="<?= (isset($row['labor_time']) ? htmlspecialchars($row['labor_time']) : '') ?>">
                                </div>
                                <label>เพศ</label>

                                &nbsp;&nbsp;
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row_ipt['sex'] == '1' || $row['sex'] == '1') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="sex1" name="sex" value="<?= (isset($row_opdscreen['sex'])  ? htmlspecialchars($row_opdscreen['sex']) : htmlspecialchars($row['sex'])) ?>" onchange="sex_check('off_checked');">
                                    <label class="custom-control-label" for="sex1">ชาย</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if ($row_ipt['sex'] == '2' || $row['sex'] == '2') {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="sex2" name="sex" value="<?= (isset($row_opdscreen['sex'])  ? htmlspecialchars($row_opdscreen['sex']) : htmlspecialchars($row['sex'])) ?>" onchange="sex_check('on_checked');">
                                    <label class="custom-control-label" for="sex2">หญิง</label>
                                </div>


                                <label>น้ำหนัก</label>
                                <div class="col-md-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" value="<?= (isset($row_opdscreen['bw2'])  ? htmlspecialchars($row_opdscreen['bw2']) : htmlspecialchars($row['weight'])) ?>" placeholder="00.00" pattern="[0-9]{3}-[0-9]{2}-[0-9]{3}" name="weight" id="weight">
                                </div> &nbsp;<label>gms

                            </div>
                            <br>
                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>Apgar score นาทีที่1</label>
                                <div class="col-md-1">
                                    <input type="text" style="width: 100%;
  box-sizing: border-box;
  border: none;
  background-color: #FF7F50;
  color: white;" class="form-control form-control-sm CheckPer_2" placeholder="00" pattern="[0-9]{3}-[0-9]{2}-[0-9]{3}" name="apgar_score_1" id="apgar_score_1">
                                </div> &nbsp;<label> ( </label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="หักคะแนน" pattern="[0-9]{3}-[0-9]{2}-[0-9]{3}" name="subtract_1" id="subtract_1"> </div><label>)</label>




                                &nbsp;&nbsp;&nbsp;&nbsp;<label>Apgar score นาทีที่5</label>
                                <div class="col-md-1">
                                    <input type="text" style="width: 100%;
  box-sizing: border-box;
  border: none;
  background-color: #FF7F50;" class="form-control form-control-sm CheckPer_2" placeholder="00" pattern="[0-9]{3}-[0-9]{2}-[0-9]{3}" name="apgar_score_5" id="apgar_score_5">
                                </div> &nbsp;<label> ( </label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="หักคะแนน" pattern="[0-9]{3}-[0-9]{2}-[0-9]{3}" name="subtract_5" id="subtract_5"> </div><label>)</label>

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>Apgar score นาทีที่15</label>
                                <div class="col-md-1">
                                    <input type="text" style="width: 100%;
  box-sizing: border-box;
  border: none;
  background-color: #FF7F50;" class="form-control form-control-sm CheckPer_2" placeholder="00" pattern="[0-9]{3}-[0-9]{2}-[0-9]{3}" name="apgar_score_10" id="apgar_score_10">
                                </div> &nbsp;<label> ( </label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="หักคะแนน" pattern="[0-9]{3}-[0-9]{2}-[0-9]{3}" name="subtract_10" id="subtract_10"> </div><label>)</label>


                            </div>

                            <div class="form-group row">
                                <label class="col-sm-12"><B> ความผิดปกติระหว่างการคลอด</B></label>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <textarea class="form-control" id="abnormal" placeholder=" xxxxxxxxxxxxxxxx" name="abnormal" rows="2"></textarea>
                                </div>
                            </div>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ประวัติมารดา&nbsp; G&nbsp;</label>
                                <div>
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="G" name="g" id="g">
                                </div>&nbsp; P&nbsp;<div>
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="P" name="p" id="p">
                                </div> &nbsp;<label>Serology</label>
                                <div class="col-md-3"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxxxxxxx" name="serology" id="serology"> </div><label></label>
                            </div>
                            <br>
                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>Antepartum complication</label>
                                <div class="col-md-2">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="xxxxxxxxxxx" name="antepartum" id="antepartum">
                                </div> &nbsp;<label>ประวัติวัคซีน dt มารดา</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="0" pattern="[0-9]{3}-[0-9]{2}-[0-9]{3}" name="dt_vaccine" id="dt_vaccine"> </div><label> เข็ม</label>
                            </div>
                            <br>
                            <div class="form-group row">
                                <label class="col-sm-12"><B> ประวัติการเจ็บป่วยของสมาชิกในครอบครัว</B></label>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12">
                                <textarea class="form-control" id="family" name="family" rows="3"><?= (isset($row_opdscreen['fh']) && $id == null ? htmlspecialchars($row_opdscreen['fh']) : htmlspecialchars($row['family'])) ?></textarea>            
                                </div>
                            </div>

                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>สัญญาณชีพ BT</label>
                                <div class="col-md-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="0.0" pattern="[0-9]{3}-[0-9]{2}-[0-9]{3}" name="bt" id="bt">
                                </div> &nbsp;<label>C &nbsp;HR</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="0" pattern="[0-9]{3}-[0-9]{2}-[0-9]{3}" name="hr" id="hr"> </div><label>bpm &nbsp;RR</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="0" pattern="[0-9]{3}-[0-9]{2}-[0-9]{3}" name="rr" id="rr"> </div>/min
                            </div>
                            <br>
                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>สภาพร่างกายผู้ป่วยแรกรับ OF</label>
                                <div class="col-md-1">
                                    <input type="text" class="form-control form-control-sm CheckPer_2" placeholder="0.0" pattern="[0-9]{3}-[0-9]{2}-[0-9]{3}" name="ofs" id="ofs">
                                </div><label>cms, OM</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="0" pattern="[0-9]{3}-[0-9]{2}-[0-9]{3}" name="om" id="om"> </div><label>Cms, รอบอก</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="0" pattern="[0-9]{3}-[0-9]{2}-[0-9]{3}" name="chest" id="chest"> </div><label>cms ตัวยาว</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="0" pattern="[0-9]{3}-[0-9]{2}-[0-9]{3}" name="body_long" id="body_long"> </div><label>cms Cord</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="0" pattern="[0-9]{3}-[0-9]{2}-[0-9]{3}" name="cord" id="cord"> </div><label> Anus</label>
                                <div class="col-md-1"><input type="text" class="form-control form-control-sm CheckPer_2" placeholder="0" pattern="[0-9]{3}-[0-9]{2}-[0-9]{3}" name="anus" id="anus"> </div>
                            </div>
                            <br>
                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>รูปร่างทั่วไป</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body'] == 'ปกติ') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="body1" name="body" value="ปกติ" onchange="body_check('off_entered');">
                                    <label class="custom-control-label" for="body1">ปกติ</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['body'] != 'ปกติ' && $row['body'] != NULL) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="body2" onchange="body_check('on_entered');">
                                    <label class="custom-control-label" for="body2">ผิดปกติ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="body_text" name="body" value="<?php if ($row['body'] != 'ปกติ' && $row['body'] != NULL) {
                                                                                                                                    echo htmlspecialchars($row['body']);
                                                                                                                                } ?>" <?php if (!($row['body'] != 'ปกติ' && $row['body'] != NULL)) {
                                                                                                                                            echo 'disabled';
                                                                                                                                        } ?>>
                                </div>


                            </div>
                            <br>
                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>การร้อง</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['cry'] == 'ไม่ร้อง') {echo 'checked="checked"';} ?> 
                                    class="custom-control-input" id="cry1" name="cry" value="ไม่ร้อง" onchange="custom_check('off_cry');">                                   
                                    <label class="custom-control-label" for="cry1">ไม่ร้อง</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['cry'] == 'ร้องเสียงดัง') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="cry2" name="cry" value="ร้องเสียงดัง" onchange="custom_check('off_cry');">
                                    <label class="custom-control-label" for="cry2">ร้องเสียงดัง</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if (
                                                            $row['cry'] != 'ไม่ร้อง'
                                                            && $row['cry'] != 'ร้องเสียงดัง'
                                                            && $row['cry'] != NULL
                                                        ) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="cry3" onchange="custom_check('on_cry');">
                                    <label class="custom-control-label" for="cry3">ร้องผิดปกติ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="cry_text" name="cry" value="<?php if (
                                                                                                                                $row['cry'] != 'ไม่ร้อง'
                                                                                                                                && $row['cry'] != 'ร้องเสียงดัง'
                                                                                                                            ) {
                                                                                                                                echo htmlspecialchars($row['cry']);
                                                                                                                            } ?>" <?php if (!($row['cry'] != 'ไม่ร้อง'
                                                                                                                                        && $row['cry'] != 'ร้องเสียงดัง'
                                                                                                                                        && $row['cry'] != NULL)) {
                                                                                                                                        echo 'disabled';
                                                                                                                                    } ?>>
                                </div>


                            </div>

                            <br>
                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;<label>การเคลื่อนไหว</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['movement'] == 'ขยับได้') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="movement1" name="movement" value="ขยับได้" onchange="movement_check('off_checked');">
                                    <label class="custom-control-label" for="movement1">ขยับได้</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['movement'] == 'อ่อนปวกเปียก') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="movement2" name="movement" value="อ่อนปวกเปียก" onchange="movement_check('off_checked');">
                                    <label class="custom-control-label" for="movement2">อ่อนปวกเปียก</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['movement'] == 'ชักเกร็ง') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="movement3" name="movement" value="ชักเกร็ง" onchange="movement_check('off_checked');">
                                    <label class="custom-control-label" for="movement3">ชักเกร็ง</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if (
                                                            $row['movement'] != 'ขยับได้'
                                                            && $row['movement'] != 'อ่อนปวกเปียก'
                                                            && $row['movement'] != 'ชักเกร็ง'
                                                            && $row['movement'] != NULL
                                                        ) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="movement4" onchange="movement_check('on_checked');">
                                    <label class="custom-control-label" for="movement4">ผิดปกติ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="movement_text" name="movement" value="<?php if (
                                                                                                                                            $row['movement'] != 'ขยับได้'
                                                                                                                                            && $row['movement'] != 'อ่อนปวกเปียก'
                                                                                                                                            && $row['movement'] != 'ชักเกร็ง'
                                                                                                                                        ) {
                                                                                                                                            echo htmlspecialchars($row['movement']);
                                                                                                                                        } ?>" <?php if (!($row['movement'] != 'ขยับได้'
                                                                                                                                                    && $row['movement'] != 'อ่อนปวกเปียก'
                                                                                                                                                    && $row['movement'] != 'ชักเกร็ง'
                                                                                                                                                    && $row['movement'] != NULL)) {
                                                                                                                                                    echo 'disabled';
                                                                                                                                                } ?>>
                                </div>


                            </div>

                            <br>
                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ศรีษะ</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['head'] == 'ปกติ') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="head1" name="head" value="ปกติ" onchange="head_check('off_checked');">
                                    <label class="custom-control-label" for="head1">ปกติ</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['head'] != 'ปกติ' && $row['head'] != NULL) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="head2" onchange="head_check('on_checked');">
                                    <label class="custom-control-label" for="head2">ผิดปกติ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="head_text" name="head" value="<?php if ($row['head'] != 'ปกติ' && $row['head'] != NULL) {
                                                                                                                                    echo htmlspecialchars($row['head']);
                                                                                                                                } ?>" <?php if (!($row['head'] != 'ปกติ' && $row['head'] != NULL)) {
                                                                                                                                            echo 'disabled';
                                                                                                                                        } ?>>
                                </div>


                            </div>

                            <br>
                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ตา</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['eyes'] == 'ปกติ') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="eyes1" name="eyes" value="ปกติ" onchange="eyes_check('off_checked');">
                                    <label class="custom-control-label" for="eyes1">ปกติ</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['eyes'] != 'ปกติ' && $row['eyes'] != NULL) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="eyes2" onchange="eyes_check('on_checked');">
                                    <label class="custom-control-label" for="eyes2">ผิดปกติ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="eyes_text" name="eyes" value="<?php if ($row['eyes'] != 'ปกติ' && $row['eyes'] != NULL) {
                                                                                                                                    echo htmlspecialchars($row['eyes']);
                                                                                                                                } ?>" <?php if (!($row['eyes'] != 'ปกติ' && $row['eyes'] != NULL)) {
                                                                                                                                            echo 'disabled';
                                                                                                                                        } ?>>
                                </div>


                            </div>

                            <br>
                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;<label>จมูก</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['nose'] == 'มีรูจมูก 2 ข้าง') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="nose1" name="nose" value="มีรูจมูก 2 ข้าง" onchange="nose_check('off_checked');">
                                    <label class="custom-control-label" for="nose1">มีรูจมูก 2 ข้าง</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['nose'] == 'รูจมูกตัน') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="nose2" name="nose" value="รูจมูกตัน" onchange="nose_check('off_checked');">
                                    <label class="custom-control-label" for="nose2">รูจมูกตัน</label>
                                </div>


                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if (
                                                            $row['nose'] != 'มีรูจมูก 2 ข้าง'
                                                            && $row['nose'] != 'รูจมูกตัน'
                                                            && $row['nose'] != NULL
                                                        ) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="nose3" onchange="nose_check('on_checked');">
                                    <label class="custom-control-label" for="nose3">ผิดปกติ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="nose_text" name="nose" value="<?php if (
                                                                                                                                    $row['nose'] != 'มีรูจมูก 2 ข้าง'
                                                                                                                                    && $row['nose'] != 'รูจมูกตัน'
                                                                                                                                ) {
                                                                                                                                    echo htmlspecialchars($row['nose']);
                                                                                                                                } ?>" <?php if (!($row['nose'] != 'มีรูจมูก 2 ข้าง'
                                                                                                                                            && $row['nose'] != 'รูจมูกตัน'
                                                                                                                                            && $row['nose'] != NULL)) {
                                                                                                                                            echo 'disabled';
                                                                                                                                        } ?>>
                                </div>


                            </div>

                            <br>
                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ปาก ลิ้น</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['mouth'] == 'ปกติ') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="mouth1" name="mouth" value="ปกติ" onchange="mouth_check('off_checked');">
                                    <label class="custom-control-label" for="mouth1">ปกติ</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['mouth'] == 'ปากแหว่ง') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="mouth2" name="mouth" value="ปากแหว่ง" onchange="mouth_check('off_checked');">
                                    <label class="custom-control-label" for="mouth2">ปากแหว่ง</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['mouth'] == 'เพดานโหว่') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="mouth3" name="mouth" value="เพดานโหว่" onchange="mouth_check('off_checked');">
                                    <label class="custom-control-label" for="mouth3">เพดานโหว่</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if (
                                                            $row['mouth'] != 'ปกติ'
                                                            && $row['mouth'] != 'ปากแหว่ง'
                                                            && $row['mouth'] != 'เพดานโหว่'
                                                            && $row['mouth'] != NULL
                                                        ) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="mouth4" onchange="mouth_check('on_checked');">
                                    <label class="custom-control-label" for="mouth4">ผิดปกติ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="mouth_text" name="mouth" value="<?php if (
                                                                                                                                    $row['mouth'] != 'ปกติ'
                                                                                                                                    && $row['mouth'] != 'ปากแหว่ง'
                                                                                                                                    && $row['mouth'] != 'เพดานโหว่'
                                                                                                                                ) {
                                                                                                                                    echo htmlspecialchars($row['mouth']);
                                                                                                                                } ?>" <?php if (!($row['mouth'] != 'ปกติ'
                                                                                                                                            && $row['mouth'] != 'ปากแหว่ง'
                                                                                                                                            && $row['mouth'] != 'เพดานโหว่'
                                                                                                                                            && $row['mouth'] != NULL)) {
                                                                                                                                            echo 'disabled';
                                                                                                                                        } ?>>
                                </div>


                            </div>


                            <br>
                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>คอ</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['neck'] == 'ปกติ') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="neck1" name="neck" value="ปกติ" onchange="neck_check('off_checked');">
                                    <label class="custom-control-label" for="neck1">ปกติ</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['neck'] != 'ปกติ' && $row['neck'] != NULL) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="neck2" onchange="neck_check('on_checked');">
                                    <label class="custom-control-label" for="neck2">ผิดปกติ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="neck_text" name="neck" value="<?php if ($row['neck'] != 'ปกติ' && $row['neck'] != NULL) {
                                                                                                                                    echo htmlspecialchars($row['neck']);
                                                                                                                                } ?>" <?php if (!($row['neck'] != 'ปกติ' && $row['neck'] != NULL)) {
                                                                                                                                            echo 'disabled';
                                                                                                                                        } ?>>
                                </div>


                            </div>

                            <br>
                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;<label>ท้อง</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['abdomen'] == 'ปกติ') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="abdomen1" name="abdomen" value="ปกติ" onchange="abdomen_check('off_checked');">
                                    <label class="custom-control-label" for="abdomen1">ปกติ</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['abdomen'] == 'ท้องอืด') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="abdomen2" name="abdomen" value="ท้องอืด" onchange="abdomen_check('off_checked');">
                                    <label class="custom-control-label" for="abdomen2">ท้องอืด</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if (
                                                            $row['abdomen'] != 'ปกติ'
                                                            && $row['abdomen'] != 'ท้องอืด'
                                                            && $row['abdomen'] != NULL
                                                        ) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="abdomen3" onchange="abdomen_check('on_checked');">
                                    <label class="custom-control-label" for="abdomen3">ผิดปกติ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="abdomen_text" name="abdomen" value="<?php if (
                                                                                                                                        $row['abdomen'] != 'ปกติ'
                                                                                                                                        && $row['abdomen'] != 'ท้องอืด'
                                                                                                                                    ) {
                                                                                                                                        echo htmlspecialchars($row['abdomen']);
                                                                                                                                    } ?>" <?php if (!($row['abdomen'] != 'ปกติ'
                                                                                                                                                && $row['abdomen'] != 'ท้องอืด'
                                                                                                                                                && $row['abdomen'] != NULL)) {
                                                                                                                                                echo 'disabled';
                                                                                                                                            } ?>>
                                </div>


                            </div>

                            <br>
                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;<label>สะดือ</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['navel'] == 'ปกติ') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="navel1" name="navel" value="ปกติ" onchange="navel_check('off_checked');">
                                    <label class="custom-control-label" for="navel1">ปกติ</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['navel'] == 'Omphalocele') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="navel2" name="navel" value="Omphalocele" onchange="navel_check('off_checked');">
                                    <label class="custom-control-label" for="navel2">Omphalocele</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['navel'] == 'Gastroschisis') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="navel3" name="navel" value="Gastroschisis" onchange="navel_check('off_checked');">
                                    <label class="custom-control-label" for="navel3">Gastroschisis</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if (
                                                            $row['navel'] != 'ปกติ'
                                                            && $row['navel'] != 'Omphalocele'
                                                            && $row['navel'] != 'Gastroschisis'
                                                            && $row['navel'] != NULL
                                                        ) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="navel4" onchange="navel_check('on_checked');">
                                    <label class="custom-control-label" for="navel4">ผิดปกติ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="navel_text" name="navel" value="<?php if (
                                                                                                                                    $row['navel'] != 'ปกติ'
                                                                                                                                    && $row['navel'] != 'Omphalocele'
                                                                                                                                    && $row['navel'] != 'Gastroschisis'
                                                                                                                                ) {
                                                                                                                                    echo htmlspecialchars($row['navel']);
                                                                                                                                } ?>" <?php if (!($row['navel'] != 'ปกติ'
                                                                                                                                            && $row['navel'] != 'Omphalocele'
                                                                                                                                            && $row['navel'] != 'Gastroschisis'
                                                                                                                                            && $row['navel'] != NULL)) {
                                                                                                                                            echo 'disabled';
                                                                                                                                        } ?>>
                                </div>


                            </div>

                            <br>
                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>กระดูกสันหลัง</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['spine'] == 'ปกติ') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="spine1" name="spine" value="ปกติ" onchange="spine_check('off_checked');">
                                    <label class="custom-control-label" for="spine1">ปกติ</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['spine'] != 'ปกติ' && $row['spine'] != NULL) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="spine2" onchange="spine_check('on_checked');">
                                    <label class="custom-control-label" for="spine2">ผิดปกติ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="spine_text" name="spine" value="<?php if ($row['spine'] != 'ปกติ' && $row['spine'] != NULL) {
                                                                                                                                    echo htmlspecialchars($row['spine']);
                                                                                                                                } ?>" <?php if (!($row['spine'] != 'ปกติ' && $row['spine'] != NULL)) {
                                                                                                                                            echo 'disabled';
                                                                                                                                        } ?>>
                                </div>


                            </div>


                            <br>
                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>แขนขา</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['limbs'] == 'ปกติ') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="limbs1" name="limbs" value="ปกติ" onchange="limbs_check('off_checked');">
                                    <label class="custom-control-label" for="limbs1">ปกติ</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['limbs'] != 'ปกติ' && $row['limbs'] != NULL) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="limbs2" onchange="limbs_check('on_checked');">
                                    <label class="custom-control-label" for="limbs2">ผิดปกติ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="limbs_text" name="limbs" value="<?php if ($row['limbs'] != 'ปกติ' && $row['limbs'] != NULL) {
                                                                                                                                    echo htmlspecialchars($row['limbs']);
                                                                                                                                } ?>" <?php if (!($row['limbs'] != 'ปกติ' && $row['limbs'] != NULL)) {
                                                                                                                                            echo 'disabled';
                                                                                                                                        } ?>>
                                </div>


                            </div>


                            <br>
                            <div class="row">

                                &nbsp;&nbsp;&nbsp;&nbsp;<label>อวัยวะเพศ</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['genitalia'] == 'ปกติ') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="genitalia1" name="genitalia" value="ปกติ" onchange="genitalia_check('off_checked');">
                                    <label class="custom-control-label" for="genitalia1">ปกติ</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['genitalia'] != 'ปกติ' && $row['genitalia'] != NULL) {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="genitalia2" onchange="genitalia_check('on_checked');">
                                    <label class="custom-control-label" for="genitalia2">ผิดปกติ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="genitalia_text" name="genitalia" value="<?php if ($row['genitalia'] != 'ปกติ' && $row['genitalia'] != NULL) {
                                                                                                                                            echo htmlspecialchars($row['genitalia']);
                                                                                                                                        } ?>" <?php if (!($row['genitalia'] != 'ปกติ' && $row['genitalia'] != NULL)) {
                                                                                                                                                    echo 'disabled';
                                                                                                                                                } ?>>
                                </div>


                            </div>



                            <br>
                            <div class="row">
                                <label class="col-sm-2 text-left">ทวารหนัก</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" class="custom-control-input" id="anuss1" value="ปกติ" name="anuss">
                                    <label class="custom-control-label" for="anuss1">ปกติ</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" class="custom-control-input" id="anuss2" value="ไม่มีรูก้น" name="anuss">
                                    <label class="custom-control-label" for="anuss2">ไม่มีรูก้น</label>
                                </div>

                            </div>

                            <br>
                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;<label>สีผิว</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['skin_color'] == 'แดง') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="skin_color1" name="skin_color" value="แดง" onchange="skin_color_check('off_checked');">
                                    <label class="custom-control-label" for="skin_color1">แดง</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['skin_color'] == 'ซีด') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="skin_color2" name="skin_color" value="ซีด" onchange="skin_color_check('off_checked');">
                                    <label class="custom-control-label" for="skin_color2">ซีด</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['skin_color'] == 'เขียว') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="skin_color3" name="skin_color" value="เขียว" onchange="skin_color_check('off_checked');">
                                    <label class="custom-control-label" for="skin_color3">เขียว</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if (
                                                            $row['skin_color'] != 'แดง'
                                                            && $row['skin_color'] != 'ซีด'
                                                            && $row['skin_color'] != 'เขียว'
                                                            && $row['skin_color'] != NULL
                                                        ) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="skin_color4" onchange="skin_color_check('on_checked');">
                                    <label class="custom-control-label" for="skin_color4">ผิดปกติ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="skin_color_text" name="skin_color" value="<?php if (
                                                                                                                                                $row['skin_color'] != 'แดง'
                                                                                                                                                && $row['skin_color'] != 'ซีด'
                                                                                                                                                && $row['skin_color'] != 'เขียว'
                                                                                                                                            ) {
                                                                                                                                                echo htmlspecialchars($row['skin_color']);
                                                                                                                                            } ?>" <?php if (!($row['skin_color'] != 'แดง'
                                                                                                                                                        && $row['skin_color'] != 'ซีด'
                                                                                                                                                        && $row['skin_color'] != 'เขียว'
                                                                                                                                                        && $row['skin_color'] != NULL)) {
                                                                                                                                                        echo 'disabled';
                                                                                                                                                    } ?>>
                                </div>


                            </div>

                            <br>
                            <div class="form-group row">
                                <label class="col-sm-12"><B> สภาพจิตใจทารกเมื่อแรกรับ</B></label>
                            </div>

                            <br>
                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;<label>การแสดงออกด้านพฤติกรรม</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['behavior'] == 'เฉย') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="behavior1" name="behavior" value="เฉย" onchange="behavior_check('off_checked');">
                                    <label class="custom-control-label" for="behavior1">เฉย</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['behavior'] == 'ร้องไห้') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="behavior2" name="behavior" value="ร้องไห้" onchange="behavior_check('off_checked');">
                                    <label class="custom-control-label" for="behavior2">ร้องไห้</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if (
                                                            $row['behavior'] != 'เฉย'
                                                            && $row['behavior'] != 'ร้องไห้'
                                                            && $row['behavior'] != NULL
                                                        ) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="behavior3" onchange="behavior_check('on_checked');">
                                    <label class="custom-control-label" for="behavior3">อื่นๆ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="behavior_text" name="behavior" value="<?php if (
                                                                                                                                            $row['behavior'] != 'เฉย'
                                                                                                                                            && $row['behavior'] != 'ร้องไห้'
                                                                                                                                        ) {
                                                                                                                                            echo htmlspecialchars($row['behavior']);
                                                                                                                                        } ?>" <?php if (!($row['behavior'] != 'เฉย'
                                                                                                                                                    && $row['behavior'] != 'ร้องไห้'
                                                                                                                                                    && $row['behavior'] != NULL)) {
                                                                                                                                                    echo 'disabled';
                                                                                                                                                } ?>>
                                </div>


                            </div>

                            <br>
                            <div class="row">
                                &nbsp;&nbsp;&nbsp;&nbsp;<label>การแสดงออกด้านอารมณ์</label>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['expression'] == 'ประเมินไม่ได้') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="expression1" name="expression" value="ประเมินไม่ได้" onchange="expression_check('off_checked');">
                                    <label class="custom-control-label" for="expression1">ประเมินไม่ได้</label>
                                </div>
                                <div class="custom-control custom-radio col-sm-1">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" <?php if ($row['expression'] == 'ร้องโกรธ') {
                                                                                    echo 'checked="checked"';
                                                                                } ?> class="custom-control-input" id="expression2" name="expression" value="ร้องโกรธ" onchange="expression_check('off_checked');">
                                    <label class="custom-control-label" for="expression2">ร้องโกรธ</label>
                                </div>

                                <div class="custom-control custom-radio col-sm-1">
                                    <input type="radio" <?php if (
                                                            $row['expression'] != 'ประเมินไม่ได้'
                                                            && $row['expression'] != 'ร้องโกรธ'
                                                            && $row['expression'] != NULL
                                                        ) {
                                                            echo 'checked="checked"';
                                                        } ?> class="custom-control-input" id="expression3" onchange="expression_check('on_checked');">
                                    <label class="custom-control-label" for="expression3">อื่นๆ</label>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" class="form-control form-control-sm" id="expression_text" name="expression" value="<?php if (
                                                                                                                                                $row['expression'] != 'ประเมินไม่ได้'
                                                                                                                                                && $row['expression'] != 'ร้องโกรธ'
                                                                                                                                            ) {
                                                                                                                                                echo htmlspecialchars($row['expression']);
                                                                                                                                            } ?>" <?php if (!($row['expression'] != 'ประเมินไม่ได้'
                                                                                                                                                        && $row['expression'] != 'ร้องโกรธ'
                                                                                                                                                        && $row['expression'] != NULL)) {
                                                                                                                                                        echo 'disabled';
                                                                                                                                                    } ?>>
                                </div>


                            </div>


                            <br>
                            <div class="form-group row">
                                <label class="col-sm-12"><B> อาการแรกรับ</B></label>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12">
                                <textarea class="form-control" id="first_symptom" placeholder=" xxxxxxxxxxxxxxxx" name="first_symptom" rows="2"></textarea>
                                </div>
                            </div>




                            <hr>



                        </div>


                        <div class="row">
                            <input type="hidden" id="an" name="an" value="<?= $an ?>"><!-- ฟิลด์ hidden  "an"  -->
                            <input type="hidden" id="id" name="id" value="<?= $id ?>"><!-- ฟิลด์ hidden "id"  -->
                            <input type="hidden" id="version" name="version" value="<?= $row['version'] ?>"><!-- ฟิลด์ hidden "id"  -->
                            <div class="col-md-9">
                                <div id="data_lr_report1_save"></div><!-- แสดงข้อความการบันมึก >> บันทึกข้อมูลสำเร็จ, EORROR -->

                                <div id="data_lr_report1_edit"></div>
                                <div id="data_lr_report1_update"></div>

                            </div>
                            <div class="col-md-12 text-right">
                                <?php
                                if ((($id == null)) || (($id != null))) { ?>
                                    <button type="button" class="btn btn-primary" id="btn_lr_report1" onclick="lr_report1_save()"><i class="fas fa-save"></i> บันทึก</button>
                                <?php } ?>
                                <a href="lr-report1-pdf.php?an=<?php echo $an; ?>&loginname=<?php echo $loginname; ?>" target="_blank" class="btn btn-secondary"><i class="fas fa-file-pdf"></i> Print <U>PDF</U> File</a>
                            </div>
                        </div>
                    </div>

                    <br>


                    <script>
                        //ควบคุมปุ่ม
                        function custom_check(value) {

                            if (value == "off_entered") {
                                $('#from_text').attr("disabled", true).val('');
                                $('#receive_from2').prop("checked", false);
                                // $("#check_1").attr("class","text-success fas fa-check-square");
                            } else if (value == "on_entered") {
                                $('#from_text').attr("disabled", false).val('');
                                $('#receive_from1').prop("checked", false);
                                //  $('#entered_by2').prop("checked", false);
                            }

                            if (value == "off_value") {
                                $('#v3').attr("disabled", true).val('');
                                $('#v2').prop("checked", false);
                            } else if (value == "on_value") {
                                $('#v3').attr("disabled", false).val('');
                                $('#v1').prop("checked", false);
                                //$('#entered_by2').prop("checked", false);
                            }

                            if (value == "off_cry") {
                                $('#cry_text').attr("disabled", true).val('');
                                $('#cry3').prop("checked", false);
                            } else if (value == "on_cry") {
                                $('#cry_text').attr("disabled", false).val('');
                                $('#cry1').prop("checked", false);
                                $('#cry2').prop("checked", false);
                            }

                        }

                        function body_check(value) {

                            if (value == "off_entered") {
                                $('#body_text').attr("disabled", true).val('');
                                $('#body2').prop("checked", false);
                            } else if (value == "on_entered") {
                                $('#body_text').attr("disabled", false).val('');
                                $('#body1').prop("checked", false);
                                //  $('#entered_by2').prop("checked", false);
                            }

                        }



                      



                        function movement_check(value) {
                            if (value == "off_checked") {
                                $('#movement_text').attr("disabled", true).val('');
                                $('#movement4').prop("checked", false);
                            } else if (value == "on_checked") {
                                $('#movement_text').attr("disabled", false).val('');
                                $('#movement1').prop("checked", false);
                                $('#movement2').prop("checked", false);
                                $('#movement3').prop("checked", false);
                            }
                        }

                        function head_check(value) {

                            if (value == "off_checked") {
                                $('#head_text').attr("disabled", true).val('');
                                $('#head2').prop("checked", false);
                            } else if (value == "on_checked") {
                                $('#head_text').attr("disabled", false).val('');
                                $('#head1').prop("checked", false);
                                //  $('#entered_by2').prop("checked", false);
                            }

                        }

                        function eyes_check(value) {

                            if (value == "off_checked") {
                                $('#eyes_text').attr("disabled", true).val('');
                                $('#eyes2').prop("checked", false);
                            } else if (value == "on_checked") {
                                $('#eyes_text').attr("disabled", false).val('');
                                $('#eyes1').prop("checked", false);
                                //  $('#entered_by2').prop("checked", false);
                            }

                        }

                        function nose_check(value) {

                            if (value == "off_checked") {
                                $('#nose_text').attr("disabled", true).val('');
                                $('#nose3').prop("checked", false);
                            } else if (value == "on_checked") {
                                $('#nose_text').attr("disabled", false).val('');
                                $('#nose1').prop("checked", false);
                                $('#nose2').prop("checked", false);
                                //  $('#entered_by2').prop("checked", false);
                            }

                        }

                        function mouth_check(value) {
                            if (value == "off_checked") {
                                $('#mouth_text').attr("disabled", true).val('');
                                $('#mouth4').prop("checked", false);
                            } else if (value == "on_checked") {
                                $('#mouth_text').attr("disabled", false).val('');
                                $('#mouth1').prop("checked", false);
                                $('#mouth2').prop("checked", false);
                                $('#mouth3').prop("checked", false);
                            }
                        }


                        function neck_check(value) {

                            if (value == "off_checked") {
                                $('#neck_text').attr("disabled", true).val('');
                                $('#neck2').prop("checked", false);
                            } else if (value == "on_checked") {
                                $('#neck_text').attr("disabled", false).val('');
                                $('#neck1').prop("checked", false);
                                //  $('#entered_by2').prop("checked", false);
                            }

                        }

                        function abdomen_check(value) {

                            if (value == "off_checked") {
                                $('#abdomen_text').attr("disabled", true).val('');
                                $('#abdomen3').prop("checked", false);
                            } else if (value == "on_checked") {
                                $('#abdomen_text').attr("disabled", false).val('');
                                $('#abdomen1').prop("checked", false);
                                $('#abdomen2').prop("checked", false);
                                //  $('#entered_by2').prop("checked", false);
                            }

                        }

                        function navel_check(value) {

                            if (value == "off_checked") {
                                $('#navel_text').attr("disabled", true).val('');
                                $('#navel4').prop("checked", false);
                            } else if (value == "on_checked") {
                                $('#navel_text').attr("disabled", false).val('');
                                $('#navel1').prop("checked", false);
                                $('#navel2').prop("checked", false);
                                $('#navel3').prop("checked", false);
                                //  $('#entered_by2').prop("checked", false);
                            }

                        }

                        function spine_check(value) {

                            if (value == "off_checked") {
                                $('#spine_text').attr("disabled", true).val('');
                                $('#spine2').prop("checked", false);
                            } else if (value == "on_checked") {
                                $('#spine_text').attr("disabled", false).val('');
                                $('#spine1').prop("checked", false);
                                //  $('#entered_by2').prop("checked", false);
                            }

                        }


                        function limbs_check(value) {

                            if (value == "off_checked") {
                                $('#limbs_text').attr("disabled", true).val('');
                                $('#limbs2').prop("checked", false);
                            } else if (value == "on_checked") {
                                $('#limbs_text').attr("disabled", false).val('');
                                $('#limbs1').prop("checked", false);
                                //  $('#entered_by2').prop("checked", false);
                            }

                        }

                        function genitalia_check(value) {

                            if (value == "off_checked") {
                                $('#genitalia_text').attr("disabled", true).val('');
                                $('#genitalia2').prop("checked", false);
                            } else if (value == "on_checked") {
                                $('#genitalia_text').attr("disabled", false).val('');
                                $('#genitalia1').prop("checked", false);
                                //  $('#entered_by2').prop("checked", false);
                            }

                        }

                        function skin_color_check(value) {

                            if (value == "off_checked") {
                                $('#skin_color_text').attr("disabled", true).val('');
                                $('#skin_color4').prop("checked", false);
                            } else if (value == "on_checked") {
                                $('#skin_color_text').attr("disabled", false).val('');
                                $('#skin_color1').prop("checked", false);
                                $('#skin_color2').prop("checked", false);
                                $('#skin_color3').prop("checked", false);
                                //  $('#entered_by2').prop("checked", false);
                            }

                        }

                        function behavior_check(value) {

                            if (value == "off_checked") {
                                $('#behavior_text').attr("disabled", true).val('');
                                $('#behavior3').prop("checked", false);
                            } else if (value == "on_checked") {
                                $('#behavior_text').attr("disabled", false).val('');
                                $('#behavior1').prop("checked", false);
                                $('#behavior2').prop("checked", false);
                                //  $('#entered_by2').prop("checked", false);
                            }

                        }

                        function expression_check(value) {

                            if (value == "off_checked") {
                                $('#expression_text').attr("disabled", true).val('');
                                $('#expression3').prop("checked", false);

                            } else if (value == "on_checked") {
                                $('#expression_text').attr("disabled", false).val('');
                                $('#expression1').prop("checked", false);
                                $('#expression2').prop("checked", false);
                                //  $('#entered_by2').prop("checked", false);
                            } else if (value == "on_aa") {
                                $('#expression_text').attr("disabled", false).val('');
                                $('#expression1').prop("checked", true);
                                //  $('#entered_by2').prop("checked", false);
                            }

                            function sex_check(value) {
                                if (value == "off_checked") {
                                    // $('#ros_text').attr("disabled",true).val('');
                                    $('#sex2').prop("checked", false);
                                } else if (value == "on_checked") {
                                    // $('#ros_text').attr("disabled",false).val('');
                                    $('#sex1').prop("checked", false);
                                }
                            }

                        }




                        $(document).ready(function() {
                            var id = <?= json_encode($id) ?>;
                            if (id != null && id != "") {
                                lr_report1_edit(<?= json_encode($id) ?>, <?= json_encode($an) ?>);
                            } else {
                                // import_DataOR_Hosxp(<?= json_encode($an) ?>);
                            }
                            //summary_CheckPer();
                        });

                        function lr_report1_edit(id, an) {
                            var url = "lr-report1-edit.php";
                            $.post(url, {
                                id,
                                an
                            }, function(data_edit) {
                                $("#data_lr_report1_edit").html(data_edit);
                                //console.log(data_edit);
                            });
                        }

                        function lr_report1_save() {

                            var id = $("#id").val();
                            //บันทึก / แก้ไข PHP File
                            var url_save = 'lr-report1-save.php';
                            var url_update = 'lr-report1-update.php';

                            $("#btn_lr_report1").attr('disabled', 'disabled');

                            if (id == "") {
                                $.post(url_save, $("#lr_report1_form").serialize(), function(data_save) {
                                        $("#data_lr_report1_save").html(data_save);
                                        window.location.reload(true);
                                    })
                                    .fail(function() {
                                        alert("บันทึกข้อมูลไม่สำเร็จ");
                                        $("#btn_lr_report1").removeAttr("disabled");
                                    });
                            } else
                            //เมื่อมีการแก้ไขเรียกใช้งาน update
                            {
                                $.post(url_update, $("#lr_report1_form").serialize(), function(data_update) {
                                        $("#data_lr_report1_update").html(data_update);
                                        window.location.reload(true);
                                    })
                                    .fail(function() {
                                        alert("บันทึกข้อมูลไม่สำเร็จ");
                                        $("#btn_lr_report1").removeAttr("disabled");
                                    });
                            }

                        }

                        /*
                        window.onscroll = function() {myFunction()};

                        var header = document.getElementById("myHeader");
                        var sticky = header.offsetTop;

                        function myFunction() {
                          if (window.pageYOffset > sticky) {
                            header.classList.add("sticky");
                          } else {
                            header.classList.remove("sticky");
                          }
                        }
                        */
                    </script>