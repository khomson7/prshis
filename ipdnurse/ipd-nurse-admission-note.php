<?php
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
       // Session::checkLoginSessionAndShowMessage(); //เช็ค session
       // Session::checkPermissionAndShowMessage('IPD_NURSE_ADDMISSION_NOTE','VIEW');
        //require_once '../mains/main-report.php';
        require_once '../mains/ipd-show-patient-main.php'; //เป็นส่วนที่แสดง ข้อมูลผู้ป่วย เช่น รูป,hn,an,ชื่อ-สกุล,แพ้ยา ฯลฯ
        require_once '../mains/ipd-show-patient-sticky.php';
        require_once '../include/DbUtils.php';
        require_once '../include/KphisQueryUtils.php';
        date_default_timezone_set("Asia/Bangkok");
        $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
        $an = empty($_REQUEST['an']) ? null : $_REQUEST['an'];
        $hn = KphisQueryUtils::getHnByAn($an);

      

        //----------------------เช็คว่า an นี้ มีข้อมูลหรือไม่
        $sql = "SELECT count(*) AS count_row, nurse_admission_note_id, version FROM ".DbConstant::KPHIS_DBNAME.".ipd_nurse_admission_note WHERE an = :an ";
        $nurse_admission_note_id = null;
        $version = null;
        $parameters['an'] = $an;
        $stmt = $conn->prepare($sql);
        $stmt->execute($parameters);
        $row = $stmt->fetch();
        $count_row = $row['count_row'];
        if($count_row > 0){
            $nurse_admission_note_id = $row['nurse_admission_note_id'];
            $version = $row['version'];
        }
        //----------------------เช็คว่า an นี้ มีข้อมูลหรือไม่

        //----------------------queryเพศผู้ป่วย
        $parameters_sex['hn'] = $hn;
        $sql_sex = "SELECT sex from ".DbConstant::HOSXP_DBNAME.".patient where hn=:hn";
        $stmt_sex = $conn->prepare($sql_sex);
        $stmt_sex->execute($parameters_sex);
        $row_sex = $stmt_sex->fetch();
        $sex = $row_sex['sex'];
        //----------------------queryเพศผู้ป่วย
?>
<form id="nurse_admission_note" action=""  method="post"  enctype="multipart/form-data">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-1">
                <button type="button" class="btn btn-primary btn-block" onclick="window.close()"><i class="fas fa-arrow-left"></i> กลับ</button>
            </div>
            <div class="col-md-11">
                <h4>การประเมินสภาพผู้ป่วยแรกรับและแบบแผนสุขภาพ (ยกเว้นผู้ป่วยเด็กอายุ < 1 ปี)</h4>
            </div>
        </div><p></p>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <h5><p class="text-center"><B><i class="fas fa-child"></i> สภาพร่างกายแรกรับ</B></p></h5>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-2 text-right"><B>ความรู้สึกตัว</B></label>
                            <div class="custom-control custom-radio col-sm-1">
                                <input type="radio" checked="checked" class="custom-control-input" id="concious1" value="รู้สึกตัวดี"  name="concious">
                                <label class="custom-control-label" for="concious1">รู้สึกตัวดี</label>
                            </div>
                            <div class="custom-control custom-radio col-sm-1">
                                <input type="radio" class="custom-control-input" id="concious2" value="สับสน"    name="concious">
                                <label class="custom-control-label" for="concious2">สับสน</label>
                            </div>
                            <div class="custom-control custom-radio col-sm-1">
                                <input type="radio" class="custom-control-input" id="concious3" value="ง่วงซึม"   name="concious">
                                <label class="custom-control-label" for="concious3">ง่วงซึม</label>
                            </div>
                            <div class="custom-control custom-radio col-sm-1">
                                <input type="radio" class="custom-control-input" id="concious4" value="ไม่รู้สึกตัว" name="concious">
                                <label class="custom-control-label" for="concious4">ไม่รู้สึกตัว</label>
                            </div>
                        </div><hr>
                        <div class="row">
                            <label class="col-sm-2 text-right"><B>ลักษณะการหายใจ</B></label>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" checked="checked" class="custom-control-input" id="normal_breath" value="Y" name="normal_breath">
                                <label class="custom-control-label" for="normal_breath">ปกติ</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input" id="kussmaul" value="Y" name="kussmaul">
                                <label class="custom-control-label" for="kussmaul">หอบลึก</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input" id="tachypnea" value="Y" name="tachypnea">
                                <label class="custom-control-label" for="tachypnea">เร็วตื้น</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input" id="dyspnea" value="Y" name="dyspnea">
                                <label class="custom-control-label" for="dyspnea">ลำบาก</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input" id="apnea" value="Y" name="apnea">
                                <label class="custom-control-label" for="apnea">ไม่หายใจ</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-3">
                                <input type="checkbox" class="custom-control-input" id="tube" value="Y" name="tube">
                                <label class="custom-control-label" for="tube">ใส่ท่อช่วยหายใจ</label>
                            </div>
                        </div><hr>
                        <div class="row">
                            <label class="col-sm-2 text-right"><B>ระบบหัวใจ</B></label>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" checked="checked" class="custom-control-input" id="normal_hr" value="Y" name="normal_hr">
                                <label class="custom-control-label" for="normal_hr">ปกติ</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-2">
                                <input type="checkbox" class="custom-control-input" id="arregular" value="Y" name="arregular">
                                <label class="custom-control-label" for="arregular">อัตราการเต้นไม่สม่ำเสมอ</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input" id="weakness" value="Y" name="weakness">
                                <label class="custom-control-label" for="weakness">ชีพจรเบา</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input" id="arrhythmia" value="Y" name="arrhythmia">
                                <label class="custom-control-label" for="arrhythmia">ใจสั่น</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input" id="chestpain" value="Y" name="chestpain" >
                                <label class="custom-control-label" for="chestpain">เจ็บหน้าอก</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-2">
                                <input type="checkbox" class="custom-control-input" id="pacemaker" value="Y" name="pacemaker"  >
                                <label class="custom-control-label" for="pacemaker">ใส่เครื่องกระตุ้นหัวใจ</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1 offset-sm-2">
                                <input type="checkbox" class="custom-control-input" id="cardio_other" value="Y" name="cardio_other"  >
                                <label class="custom-control-label" for="cardio_other">อื่นๆ</label>
                            </div>
                            <div class="col-sm-3">
                                <div class="row">
                                    <input type="text" class="form-control form-control-sm" id="cardio_other_text" name="cardio_other_text">
                                </div>
                            </div>
                        </div><hr>
                        <div class="row">
                            <label class="col-sm-2 text-right"><B>การไหลเวียนโลหิต</B></label>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" checked="checked" class="custom-control-input" id="normal_cir" value="Y" name="normal_cir"  >
                                <label class="custom-control-label" for="normal_cir">ปกติ</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input" id="pale" value="Y" name="pale"  >
                                <label class="custom-control-label" for="pale">ซีด</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-2">
                                <input type="checkbox" class="custom-control-input" id="cyanosis" value="Y" name="cyanosis"  >
                                <label class="custom-control-label" for="cyanosis"> เขียวปลายมือ-เท้า</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input" id="generalized_edema" value="Y" name="generalized_edema"  >
                                <label class="custom-control-label" for="generalized_edema">บวมทั่วตัว</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input" id="localized_edema" value="Y" name="localized_edema"  >
                                <label class="custom-control-label" for="localized_edema">บวมเฉพาะที่</label>
                            </div>
                            <div class="col-sm-3">
                                <div class="row">
                                    <input type="text" class="form-control form-control-sm" id="localized_edema_text" name="localized_edema_text">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="custom-control custom-checkbox col-sm-1 offset-sm-2">
                                <input type="checkbox" class="custom-control-input" id="pitting_edema" value="Y" name="pitting_edema"  >
                                <label class="custom-control-label" for="pitting_edema">บวมกดบุ๋ม</label>
                            </div>
                            <div class="col-sm-3">
                                <div class="row">
                                    <input type="text" class="form-control form-control-sm" id="pitting_edema_text" name="pitting_edema_text">
                                </div>
                            </div>
                        </div><p></p>
                        <div class="row">
                            <div class="custom-control custom-checkbox col-sm-1 offset-sm-2">
                                <input type="checkbox" class="custom-control-input" id="circulation_orther" value="Y" name="circulation_orther"  >
                                <label class="custom-control-label" for="circulation_orther">อื่นๆ</label>
                            </div>
                            <div class="col-sm-3">
                                <div class="row">
                                    <input type="text" class="form-control form-control-sm" id="circulation_orther_text" name="circulation_orther_text">
                                </div>
                            </div>
                        </div><hr>
                        <div class="row">
                            <label class="col-sm-2 text-right"><B>สภาพผิวหนัง</B></label>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" checked="checked" class="custom-control-input" id="normal_skin" value="Y" name="normal_skin"  >
                                <label class="custom-control-label" for="normal_skin">ปกติ</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input" id="dry" value="Y" name="dry"  >
                                <label class="custom-control-label" for="dry">แห้งแตก</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-2">
                                <input type="checkbox" class="custom-control-input" id="bruise" value="Y" name="bruise"  >
                                <label class="custom-control-label" for="bruise"> บาง ช้ำ หลุดลอกง่าย</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input" id="erythema" value="Y" name="erythema"  >
                                <label class="custom-control-label" for="erythema">ผื่นแดง</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input" id="abscess" value="Y" name="abscess"  >
                                <label class="custom-control-label" for="abscess">แผล ฝี</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input" id="joudice" value="Y" name="joudice"  >
                                <label class="custom-control-label" for="joudice"> เหลือง</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="custom-control custom-checkbox col-sm-1 offset-sm-2">
                                <input type="checkbox" class="custom-control-input" id="skin_other" value="Y" name="skin_other"  >
                                <label class="custom-control-label" for="skin_other">อื่นๆ</label>
                            </div>
                            <div class="col-sm-3">
                                <div class="row">
                                    <input type="text" class="form-control form-control-sm" id="skin_other_text" name="skin_other_text">
                                </div>
                            </div>
                        </div><hr>
                        <div class="row">
                            <label class="col-sm-2 text-right"><B>ความเจ็บปวด</B></label>
                            <div class="custom-control custom-radio col-sm-1">
                                <input type="radio" checked="checked" class="custom-control-input" id="pain_n" value="ไม่มี" name="pain">
                                <label class="custom-control-label" for="pain_n">ไม่มี</label>
                            </div>
                            <div class="custom-control custom-radio col-sm-1">
                                <input type="radio" class="custom-control-input" id="pain_y" value="มี" name="pain">
                                <label class="custom-control-label" for="pain_y">มี บริเวณ</label>
                            </div>
                            <div class="col-sm-3">
                                <div class="row">
                                    <input type="text" class="form-control form-control-sm" id="location" name="location">
                                </div>
                            </div>
                        </div><p></p>
                        <div class="row">
                            <label class="col-sm-2 offset-sm-3"><B>ลักษณะการเจ็บปวด</B></label>
                            <div class="custom-control custom-radio col-sm-1">
                                <input type="radio" class="custom-control-input" id="pain_charac_s" value="ครั้งคราว" name="pain_charac"> <!-- sometime -->
                                <label class="custom-control-label" for="pain_charac_s">ครั้งคราว</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="custom-control custom-radio col-sm-1 offset-sm-5">
                                <input type="radio" class="custom-control-input" id="pain_charac_a" value="ตลอดเวลา" name="pain_charac"> <!-- always -->
                                <label class="custom-control-label" for="pain_charac_a">ตลอดเวลา</label>
                            </div>
                        </div><p></p>
                        <div class="row">
                            <div class="custom-control custom-radio col-sm-1  offset-sm-5">
                                <input type="radio" class="custom-control-input" id="pain_charac_o" value="อื่นๆ" name="pain_charac"> <!-- orther -->
                                <label class="custom-control-label" for="pain_charac_o">อื่นๆ</label>
                            </div>
                            <div class="col-sm-3">
                                <div class="row">
                                    <input type="text" class="form-control form-control-sm" id="pain_charac_text" name="pain_charac_text">
                                </div>
                            </div>
                        </div><p></p>
                        <div class="row">
                            <label class="col-sm-1 offset-sm-5"><B>Pain Score</B></label>
                            <div class="col-sm-1">
                                <div class="row">
                                    <input type="text" class="form-control form-control-sm" id="pain_score" name="pain_score">
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <label>คะแนน</label>
                            </div>
                        </div><hr>
                        <div class="row">
                            <div class="col-sm-12">
                                <h5><p class="text-center"><B><i class="fas fa-heart"></i> สภาพจิตใจแรกรับ</B></p></h5>
                            </div>
                        </div><p></p>
                        <div class="row">
                            <label class="col-sm-2 text-right"><B>ด้านพฤติกรรม</B></label>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" checked="checked" class="custom-control-input onchange_no_mental_state_disabled" id="normal_behav" value="Y" name="normal_behav">
                                <label class="custom-control-label" for="normal_behav">ร่วมมือดี</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-2">
                                <input type="checkbox" class="custom-control-input onchange_no_mental_state_disabled" id="agitate" value="Y" name="agitate"  >
                                <label class="custom-control-label" for="agitate">กระวนกระวาย</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input onchange_no_mental_state_disabled" id="aggressive" value="Y" name="aggressive"  >
                                <label class="custom-control-label" for="aggressive">ก้าวร้าว</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input onchange_no_mental_state_disabled" id="depression" value="Y" name="depression"  >
                                <label class="custom-control-label" for="depression">ซึมเศร้า</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-2">
                                <input type="checkbox" class="custom-control-input onchange_no_mental_state_disabled" id="madness" value="Y" name="madness"  >
                                <label class="custom-control-label" for="madness">เอะอะโวยวาย</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="custom-control custom-checkbox col-sm-1 offset-sm-2">
                                <input type="checkbox" class="custom-control-input onchange_no_mental_state_disabled" id="behaviour_other" value="Y" name="behaviour_other"  >
                                <label class="custom-control-label" for="behaviour_other">อื่นๆ</label>
                            </div>
                            <div class="col-sm-3">
                                <div class="row">
                                    <input type="text" class="form-control form-control-sm" id="behaviour_other_text" name="behaviour_other_text">
                                </div>
                            </div>
                        </div><hr>
                        <div class="row">
                            <label class="col-sm-2 text-right"><B>ด้านอารมณ์</B></label>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" checked="checked" class="custom-control-input onchange_no_mental_state_disabled" id="normal_emotional"  value="Y" name="normal_emotional"  >
                                <label class="custom-control-label" for="normal_emotional">สงบ</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input onchange_no_mental_state_disabled" id="angry"  value="Y" name="angry"  >
                                <label class="custom-control-label" for="angry">โกรธ</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input onchange_no_mental_state_disabled" id="moody"  value="Y" name="moody"  >
                                <label class="custom-control-label" for="moody">หงุดหงิด</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input onchange_no_mental_state_disabled" id="anxiety"  value="Y" name="anxiety"  >
                                <label class="custom-control-label" for="anxiety">กังวลใจ</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input onchange_no_mental_state_disabled" id="fear"  value="Y" name="fear"  >
                                <label class="custom-control-label" for="fear">หวาดกลัว</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="custom-control custom-checkbox col-sm-1 offset-sm-2">
                                <input type="checkbox" class="custom-control-input onchange_no_mental_state_disabled" id="emotional_other"  value="Y" name="emotional_other"  >
                                <label class="custom-control-label" for="emotional_other">อื่นๆ</label>
                            </div>
                            <div class="col-sm-3">
                                <div class="row">
                                    <input type="text" class="form-control form-control-sm" id="emotional_other_text" name="emotional_other_text">
                                </div>
                            </div>
                        </div><hr>
                        <div class="row">
                            <label class="col-sm-2 text-right"><B>ความกังวลใจ</B></label>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" checked="checked" class="custom-control-input onchange_no_mental_state_disabled" id="no_anxiety" value="Y" name="no_anxiety"  >
                                <label class="custom-control-label" for="no_anxiety">ปฎิเสธ</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input onchange_no_mental_state_disabled" id="study" value="Y" name="study"  >
                                <label class="custom-control-label" for="study">การเรียน</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input onchange_no_mental_state_disabled" id="family" value="Y" name="family"  >
                                <label class="custom-control-label" for="family">ครอบครัว</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input onchange_no_mental_state_disabled" id="economy" value="Y" name="economy"  >
                                <label class="custom-control-label" for="economy">ค่าใช้จ่าย</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input onchange_no_mental_state_disabled" id="habitation" value="Y" name="habitation"  >
                                <label class="custom-control-label" for="habitation">ที่อยู่อาศัย</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-2">
                                <input type="checkbox" class="custom-control-input onchange_no_mental_state_disabled" id="illness" value="Y" name="illness"  >
                                <label class="custom-control-label" for="illness">ความเจ็บป่วย</label>
                            </div>
                        </div><hr>
                        <div class="row">
                            <label class="col-sm-3 text-right"><B>ความต้องการด้านจิตวิญญาณ</B></label>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" checked="checked" class="custom-control-input onchange_no_mental_state_disabled" id="spiritual_no" value="Y" name="spiritual_no" >
                                <label class="custom-control-label" for="spiritual_no">ไม่ต้องการ</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-2">
                                <input type="checkbox" class="custom-control-input onchange_no_mental_state_disabled" id="spiritual_back_home" value="Y" name="spiritual_back_home" >
                                <label class="custom-control-label" for="spiritual_back_home">บ่นอยากกลับบ้านมาก</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-3">
                                <input type="checkbox" class="custom-control-input onchange_no_mental_state_disabled" id="spiritual_need_family" value="Y" name="spiritual_need_family" >
                                <label class="custom-control-label" for="spiritual_need_family">ถามถึงบุคคลในครอบครัวบ่อยๆ</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="custom-control custom-checkbox col-sm-2 offset-sm-3">
                                <input type="checkbox" class="custom-control-input onchange_no_mental_state_disabled" id="spiritual_other" value="Y" name="spiritual_other" >
                                <label class="custom-control-label" for="spiritual_other">อื่นๆ</label>
                            </div>
                            <div class="col-sm-3">
                                <div class="row">
                                    <input type="text" class="form-control form-control-sm" id="spiritual_other_text" name="spiritual_other_text">
                                </div>
                            </div>
                        </div><p></p>
                        <div class="row">
                            <div class="custom-control custom-checkbox col-sm-2 offset-sm-3">
                                <input type="checkbox" class="custom-control-input onchange_no_mental_state_disabled" id="spiritual_cant_rated" value="Y" name="spiritual_cant_rated"  >
                                <label class="custom-control-label" for="spiritual_cant_rated">ประเมินไม่ได้</label>
                            </div>
                            <div class="col-sm-3">
                                <div class="row">
                                    <input type="text" class="form-control form-control-sm" id="spiritual_cant_rated_text" name="spiritual_cant_rated_text">
                                </div>
                            </div>
                        </div><hr>
                        <div class="row">
                            <div class="custom-control custom-checkbox col-sm-2 offset-sm-3" onchange="onchange_no_mental_state()">
                                <input type="checkbox" class="custom-control-input" id="no_mental_state" value="Y" name="no_mental_state">
                                <label class="custom-control-label font-weight-bold" for="no_mental_state">ประเมินสภาพจิตใจไม่ได้เนื่องจาก</label>
                            </div>
                            <div class="col-sm-3">
                                <div class="row">
                                    <textarea class="form-control" id="no_mental_state_text" name="no_mental_state_text" rows="3" disabled></textarea>
                                </div>
                            </div>
                        </div><hr>
                        <div class="row">
                            <div class="col-sm-12">
                                <h5><p class="text-center"><B><i class="fas fa-users"></i> สภาพสังคมและเศรษฐานะ</B></p></h5>
                            </div>
                        </div><p></p>
                        <div class="row">
                            <label class="col-sm-2 text-right"><B>การศึกษา</B></label>
                            <div class="custom-control custom-radio col-sm-2">
                                <input type="radio" checked="checked" class="custom-control-input" id="education_n" name="education"  value="ไม่ได้รับ" >
                                <label class="custom-control-label" for="education_n">ไม่ได้รับ/ยังไม่ได้รับ</label>
                            </div>
                            <div class="custom-control custom-radio col-sm-1">
                                <input type="radio" class="custom-control-input" id="education_y" name="education"  value="ได้รับ" >
                                <label class="custom-control-label" for="education_y">ได้รับ(ระบุ)</label>
                            </div>
                            <div class="col-sm-3">
                                <div class="row">
                                    <input type="text" class="form-control form-control-sm" id="education_result" name="education_result">
                                </div>
                            </div>
                        </div><p></p>
                        <div class="row">
                            <label class="col-sm-2 text-right"><B>อาชีพ(ระบุ)</B></label>
                            <div class="col-sm-6">
                                <div class="row">
                                    <input type="text" class="form-control form-control-sm" id="occupation" name="occupation">
                                </div>
                            </div>
                        </div><p></p>
                        <div class="row">
                            <label class="col-sm-2 text-right"><B>รายได้</B></label>
                            <div class="custom-control custom-radio col-sm-1">
                                <input type="radio" checked="checked" class="custom-control-input" id="income_y" value="เพียงพอ" name="income"  >
                                <label class="custom-control-label" for="income_y">เพียงพอ</label>
                            </div>
                            <div class="custom-control custom-radio col-sm-2">
                                <input type="radio" class="custom-control-input" id="income_n" value="ไม่เพียงพอ" name="income"  >
                                <label class="custom-control-label" for="income_n">ไม่เพียงพอ</label>
                            </div>
                        </div><p></p>
                        <div class="row">
                            <label class="col-sm-2 text-right"><B>ผู้ให้ความช่วยเหลือดูแล</B></label>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" checked="checked" class="custom-control-input" id="self" value="Y" name="self"  >
                                <label class="custom-control-label" for="self">ตนเอง</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-2">
                                <input type="checkbox" class="custom-control-input" id="person_family" value="Y" name="person_family"  >
                                <label class="custom-control-label" for="person_family">บุคคลในครอบครัว</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input" id="neighbor" value="Y" name="neighbor"  >
                                <label class="custom-control-label" for="neighbor">เพื่อนบ้าน</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="custom-control custom-checkbox col-sm-1 offset-sm-2">
                                <input type="checkbox" class="custom-control-input" id="assistant_other" value="Y"  name="assistant_other">
                                <label class="custom-control-label" for="assistant_other">อื่นๆ</label>
                            </div>
                            <div class="col-sm-3">
                                <div class="row">
                                    <input type="text" class="form-control form-control-sm" id="assistant_other_text" name="assistant_other_text">
                                </div>
                            </div>
                        </div><p></p>
                        <div class="row">
                            <label class="col-sm-2 text-right"><B>อาชีพผู้ดูแล(ระบุ)</B></label>
                            <div class="col-sm-6">
                                <div class="row">
                                    <input type="text" class="form-control form-control-sm" id="assistant_occupation" name="assistant_occupation">
                                </div>
                            </div>
                        </div><hr>
                        <div class="row">
                            <div class="col-sm-12">
                                <h5><p class="text-center"><B><i class="fas fa-paste"></i> แบบแผนสุขภาพ</B></p></h5>
                            </div>
                        </div><p></p>
                        <div class="row">
                            <label class="col-sm-12"><B>การรับรู้สุขภาพ และการดูแลสุขภาพ</B></label>
                        </div><p></p>
                        <div class="row">
                            <label class="col-sm-2 text-right"><B>การดูแลตนเอง</B></label>
                            <div class="custom-control custom-checkbox col-sm-2">
                                <input type="checkbox" checked="checked" class="custom-control-input" id="clinic" value="Y" name="clinic"  >
                                <label class="custom-control-label" for="clinic">ไป รพ./คลินิก</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-4">
                                <input type="checkbox" class="custom-control-input" id="buy_medicine" value="Y" name="buy_medicine"  >
                                <label class="custom-control-label" for="buy_medicine">ซื้อยารับประทานเอง</label>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-2 text-right"><B>พฤติกรรมเสี่ยง</B></label>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" checked="checked" class="custom-control-input" id="no_risk" value="Y" name="no_risk"  >
                                <label class="custom-control-label" for="no_risk">ปฏิเสธ</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="custom-control custom-checkbox col-sm-1 offset-sm-2">
                                <input type="checkbox" class="custom-control-input" id="smoking" value="Y" name="smoking"  >
                                <label class="custom-control-label" for="smoking">สูบบุหรี่</label>
                            </div>
                            <div class="col-sm-1">
                                <input type="text" class="form-control form-control-sm" id="smoke_year" name="smoke_year">
                            </div>
                            <label class="col-sm-1">ปี ปริมาณ</label>
                            <div class="col-sm-1">
                                <input type="text" class="form-control form-control-sm" id="smoke_frequency" name="smoke_frequency">
                            </div>
                            <label class="col-sm-1">/วัน เลิกเมื่อ</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control form-control-sm" id="smoke_stopped" name="smoke_stopped">
                            </div>
                        </div><p></p>
                        <div class="row">
                            <div class="custom-control custom-checkbox col-sm-1 offset-sm-2">
                                <input type="checkbox" class="custom-control-input" id="alcohol" value="Y" name="alcohol"  >
                                <label class="custom-control-label" for="alcohol">ดื่มสุรา</label>
                            </div>
                            <div class="col-sm-1">
                                <input type="text" class="form-control form-control-sm" id="alc_year" name="alc_year">
                            </div>
                            <label class="col-sm-1">ปี ปริมาณ</label>
                            <div class="col-sm-1">
                                <input type="text" class="form-control form-control-sm" id="alc_frequency" name="alc_frequency">
                            </div>
                            <label class="col-sm-1">/วัน เลิกเมื่อ</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control form-control-sm" id="alc_stopped" name="alc_stopped">
                            </div>
                        </div><p></p>
                        <div class="row">
                            <div class="custom-control custom-checkbox col-sm-1 offset-sm-2">
                                <input type="checkbox" class="custom-control-input" id="medication_used" value="Y" name="medication_used"  >
                                <label class="custom-control-label" for="medication_used">ยา (ระบุ)</label>
                            </div>
                            <div class="col-sm-6">
                                <textarea class="form-control" id="med_name" name="med_name" rows="3"></textarea>
                            </div>
                            ระยะเวลาที่ใช้
                            <div class="col-sm-2">
                                <input type="text" class="form-control form-control-sm" id="med_year" name="med_year">
                            </div>
                        </div><p></p>
                        <div class="row">
                            <label class="col-sm-1 text-right offset-sm-2">ปริมาณ</label>
                            <div class="col-sm-1">
                                <input type="text" class="form-control form-control-sm" id="med_frequency" name="med_frequency">
                            </div>
                            <label class="col-sm-1">/วัน เลิกเมื่อ</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control form-control-sm" id="med_stopped" name="med_stopped">
                            </div>
                        </div><hr>
                        <div class="row">
                            <label class="col-sm-12"><B>อาหาร และการเผาผลาญอาหาร</B></label>
                        </div><p></p>
                        <div class="row">
                            <div class="custom-control custom-radio col-sm-2 offset-sm-2">
                                <input type="radio" checked="checked" class="custom-control-input" id="diet_regu" value="อาหารทั่วไป" name="diet_regular"  >
                                <label class="custom-control-label" for="diet_regu">อาหารทั่วไป</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="custom-control custom-radio col-sm-2 offset-sm-2">
                                <input type="radio" class="custom-control-input" id="diet_sp" value="อาหารเฉพาะโรค" name="diet_regular"  >
                                <label class="custom-control-label" for="diet_sp">อาหารเฉพาะโรค (ระบุ)</label>
                            </div>
                            <div class="col-sm-5">
                                <div class="row">
                                    <input type="text" class="form-control form-control-sm" id="diet_spec" name="diet_spec">
                                </div>
                            </div>
                        </div><p></p>
                        <div class="row">
                            <label class="col-sm-3 text-right"><B>ปัญหาการรับประทานอาหาร</B></label>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" checked="checked" class="custom-control-input" id="nutrition_risk" value="Y" name="nutrition_risk" >
                                <label class="custom-control-label" for="nutrition_risk">ไม่มี</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input" id="loss_appetite" value="Y" name="loss_appetite" >
                                <label class="custom-control-label" for="loss_appetite">เบื่ออาหาร</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-2">
                                <input type="checkbox" class="custom-control-input" id="dysphagia" value="Y" name="dysphagia" >
                                <label class="custom-control-label" for="dysphagia">เคี้ยว/กลืนลำบาก</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-2">
                                <input type="checkbox" class="custom-control-input" id="loss_gustation" value="Y" name="loss_gustation" >
                                <label class="custom-control-label" for="loss_gustation">ไม่รู้รสกลิ่น</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-2">
                                <input type="checkbox" class="custom-control-input" id="denture" value="Y" name="denture" >
                                <label class="custom-control-label" for="denture">ใส่ฟันปลอม</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="custom-control custom-checkbox col-sm-1 offset-sm-3">
                                <input type="checkbox" class="custom-control-input" id="nutrition_risk_other" value="Y" name="nutrition_risk_other">
                                <label class="custom-control-label" for="nutrition_risk_other">อื่นๆ (ระบุ)</label>
                            </div>
                            <div class="col-sm-5">
                                <div class="row">
                                    <input type="text" class="form-control form-control-sm" id="nutrition_risk_other_text" name="nutrition_risk_other_text">
                                </div>
                            </div>
                        </div><hr>
                        <div class="row">
                            <label class="col-sm-12"><B>การขับถ่าย</B></label>
                        </div><p></p>
                        <div class="row">
                            <label class="col-sm-2 text-right"><B>ปัสสาวะ</B></label>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" checked="checked" class="custom-control-input" id="normal_urine" value="Y" name="normal_urine" >
                                <label class="custom-control-label" for="normal_urine">ปกติ</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input" id="dysuria" value="Y" name="dysuria" >
                                <label class="custom-control-label" for="dysuria">แสบขัด</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input" id="incontinence" value="Y" name="incontinence" >
                                <label class="custom-control-label" for="incontinence">กลั้นไม่ได้</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input" id="staining" value="Y" name="staining" >
                                <label class="custom-control-label" for="staining">ลำบาก</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input" id="hematuria" value="Y" name="hematuria" >
                                <label class="custom-control-label" for="hematuria">เป็นเลือด</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-2">
                                <input type="checkbox" class="custom-control-input" id="catheter" value="Y" name="catheter"  >
                                <label class="custom-control-label" for="catheter">สายสวนปัสสาวะ</label>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-2 text-right"><B>อุจจาระ</B></label>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" checked="checked" class="custom-control-input" id="normal_feces" value="Y" name="normal_feces"  >
                                <label class="custom-control-label" for="normal_feces">ปกติ</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input" id="constipation" value="Y" name="constipation"  >
                                <label class="custom-control-label" for="constipation">ท้องผูก</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-2">
                                <input type="checkbox" class="custom-control-input" id="diarrhea"  value="Y" name="diarrhea"  >
                                <label class="custom-control-label" for="diarrhea" >ท้องเสียบ่อย</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input" id="bowel_incontinence"  value="Y" name="bowel_incontinence"  >
                                <label class="custom-control-label" for="bowel_incontinence" >กลั้นไม่ได้</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-2">
                                <input type="checkbox" class="custom-control-input" id="hemorrhoid"  value="Y" name="hemorrhoid"  >
                                <label class="custom-control-label" for="hemorrhoid" >ริดสีดวงทวาร</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-2">
                                <input type="checkbox" class="custom-control-input" id="colostomy"  value="Y" name="colostomy"  >
                                <label class="custom-control-label" for="colostomy" >ถ่ายทางหน้าท้อง</label>
                            </div>
                        </div><hr>
                        <div class="row">
                            <label class="col-sm-12"><B>กิจกรรมและออกกำลังกาย</B></label>
                        </div><p></p>
                        <div class="row">
                            <div class="custom-control custom-checkbox col-sm-1 offset-sm-2">
                                <input type="checkbox" checked="checked" class="custom-control-input" id="activity1"  value="Y" name="activity1">
                                <label class="custom-control-label" for="activity1">ทำได้เอง</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input" id="activity2"  value="Y" name="activity2">
                                <label class="custom-control-label" for="activity2">ต้องมีคนช่วย</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input" id="activity3"  value="Y" name="activity3">
                                <label class="custom-control-label" for="activity3">ทำเองไม่ได้</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input" id="activity4"  value="Y" name="activity4">
                                <label class="custom-control-label" for="activity4">ใช้กายอุปกรณ์</label>
                            </div>
                            <div class="col-sm-3">
                                <div class="row">
                                    <input type="text" class="form-control form-control-sm" id="o_p_use" name="o_p_use">
                                </div>
                            </div>
                        </div><hr>
                        <div class="row">
                            <label class="col-sm-12"><B>การพักผ่อนนอนหลับ</B></label>
                        </div><p></p>
                        <div class="row">
                            <div class="custom-control custom-checkbox col-sm-1 offset-sm-2">
                                <input type="checkbox" class="custom-control-input" id="sleep_per_day" value="Y" name="sleep_per_day"  >
                                <label class="custom-control-label" for="sleep_per_day">วันละ</label>
                            </div>
                            <div class="col-sm-1">
                                <div class="row">
                                    <input type="text" class="form-control form-control-sm" id="sleep_hour" name="sleep_hour">
                                </div>
                            </div>
                            <div class="col-sm-1">
                                <label>ชม.</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-2">
                                <input type="checkbox" class="custom-control-input" id="sleep_problems" value="Y" name="sleep_problems"  >
                                <label class="custom-control-label" for="sleep_problems">ปัญหาการนอน (ระบุ)</label>
                            </div>
                            <div class="col-sm-4">
                                <div class="row">
                                    <input type="text" class="form-control form-control-sm" id="sleep_problems_detail" name="sleep_problems_detail">
                                </div>
                            </div>
                        </div><p></p>
                        <div class="row">
                            <label class="col-sm-2 text-right"><B>การใช้ยานอนหลับ</B></label>
                            <div class="custom-control custom-radio col-sm-1">
                                <input type="radio" checked="checked" class="custom-control-input" id="sleep_med_name1" value="ไม่เคย" name="sleep_med_name"  >
                                <label class="custom-control-label" for="sleep_med_name1">ไม่เคย</label>
                            </div>
                            <div class="custom-control custom-radio col-sm-2">
                                <input type="radio" class="custom-control-input" id="sleep_med_name2" value="เป็นครั้งคราว" name="sleep_med_name"  >
                                <label class="custom-control-label" for="sleep_med_name2">เป็นครั้งคราว</label>
                            </div>
                            <div class="custom-control custom-radio col-sm-2">
                                <input type="radio" class="custom-control-input" id="sleep_med_name3" value="เป็นประจำ" name="sleep_med_name"  >
                                <label class="custom-control-label" for="sleep_med_name3">เป็นประจำ ยา (ระบุ)</label>
                            </div>
                            <div class="col-sm-4">
                                <div class="row">
                                    <input type="text" class="form-control form-control-sm" id="sleep_med_name_detail" name="sleep_med_name_detail">
                                </div>
                            </div>
                        </div><hr>
                        <div class="row">
                            <label class="col-sm-12"><B>สติปัญญาและการรับรู้</B></label>
                        </div><p></p>
                        <div class="row">
                            <label class="col-sm-2 text-right"><B>การรับรู้</B></label>
                            <div class="custom-control custom-radio col-sm-1">
                                <input type="radio" checked="checked" class="custom-control-input" id="cognitive1" value="ตรง" name="cognitive"  >
                                <label class="custom-control-label" for="cognitive1">ตรง</label>
                            </div>
                            <div class="custom-control custom-radio col-sm-2">
                                <input type="radio" class="custom-control-input" id="cognitive2" value="ไม่ตรง" name="cognitive"  >
                                <label class="custom-control-label" for="cognitive2">ไม่ตรง</label>
                            </div>
                        </div><p></p>
                        <div class="row">
                            <label class="col-sm-2 text-right"><B>ความจำ</B></label>
                            <div class="custom-control custom-radio col-sm-1">
                                <input type="radio" checked="checked" class="custom-control-input" id="memory1" value="ปกติ" name="memory"  >
                                <label class="custom-control-label" for="memory1">ปกติ</label>
                            </div>
                            <div class="custom-control custom-radio col-sm-2">
                                <input type="radio" class="custom-control-input" id="memory2" value="ผิดปกติ" name="memory"  >
                                <label class="custom-control-label" for="memory2">ผิดปกติ (ระบุ)</label>
                            </div>
                            <div class="col-sm-3">
                                <div class="row">
                                    <input type="text" class="form-control form-control-sm" id="memory_detail" name="memory_detail">
                                </div>
                            </div>
                        </div><p></p>
                        <div class="row">
                            <label class="col-sm-2 text-right"><B>การได้ยิน</B></label>
                            <div class="custom-control custom-radio col-sm-1">
                                <input type="radio" checked="checked" class="custom-control-input" id="hearing1" value="ปกติ" name="hearing"  >
                                <label class="custom-control-label" for="hearing1">ปกติ</label>
                            </div>
                            <div class="custom-control custom-radio col-sm-2">
                                <input type="radio" class="custom-control-input" id="hearing2" value="ผิดปกติ" name="hearing"  >
                                <label class="custom-control-label" for="hearing2">ผิดปกติ (ระบุ)</label>
                            </div>
                            <div class="col-sm-3">
                                <div class="row">
                                    <input type="text" class="form-control form-control-sm" id="hearing_detail" name="hearing_detail">
                                </div>
                            </div>&nbsp;&nbsp;
                            <div class="custom-control custom-checkbox col-sm-2">
                                <input type="checkbox" class="custom-control-input" id="eartone" value="Y" name="eartone"  >
                                <label class="custom-control-label" for="eartone">ใช้เครื่องช่วยฟัง</label>
                            </div>
                        </div><p></p>
                        <div class="row">
                            <label class="col-sm-2 text-right"><B>การมองเห็น</B></label>
                            <div class="custom-control custom-radio col-sm-1">
                                <input type="radio" checked="checked" class="custom-control-input" id="vision1" value="ปกติ" name="vision"  >
                                <label class="custom-control-label" for="vision1">ปกติ</label>
                            </div>
                            <div class="custom-control custom-radio col-sm-2">
                                <input type="radio" class="custom-control-input" id="vision2" value="ผิดปกติ" name="vision"  >
                                <label class="custom-control-label" for="vision2">ผิดปกติ (ระบุ)</label>
                            </div>
                            <div class="col-sm-3">
                                <div class="row">
                                    <input type="text" class="form-control form-control-sm" id="vision_detail" name="vision_detail">
                                </div>
                            </div>&nbsp;&nbsp;
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input" id="vision_eyeglasses"  value="Y" name="vision_eyeglasses"  >
                                <label class="custom-control-label" for="vision_eyeglasses">แว่นตา</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-2">
                                <input type="checkbox" class="custom-control-input" id="vision_contactlens" value="Y" name="vision_contactlens"  >
                                <label class="custom-control-label" for="vision_contactlens">คอนแทคเลนส์</label>
                            </div>
                        </div><p></p>
                        <div class="row">
                            <label class="col-sm-2 text-right"><B>การพูด</B></label>
                            <div class="custom-control custom-radio col-sm-1">
                                <input type="radio" checked="checked" class="custom-control-input" id="speech1" value="ปกติ" name="speech"  >
                                <label class="custom-control-label" for="speech1">ปกติ</label>
                            </div>
                            <div class="custom-control custom-radio col-sm-2">
                                <input type="radio" class="custom-control-input" id="speech2" value="ผิดปกติ" name="speech"  >
                                <label class="custom-control-label" for="speech2">ผิดปกติ (ระบุ)</label>
                            </div>
                            <div class="col-sm-3">
                                <div class="row">
                                    <input type="text" class="form-control form-control-sm" id="speech_detail" name="speech_detail">
                                </div>
                            </div>
                        </div><hr>
                        <div class="row">
                            <label class="col-sm-12"><B>การรับรู้ตนเองและอัตมโนทัศน์</B></label>
                        </div><p></p>
                        <div class="row">
                            <label class="col-sm-2 text-right"><B>กระทบต่อภาพลักษณ์</B></label>
                            <div class="custom-control custom-radio col-sm-1">
                                <input type="radio" checked="checked" class="custom-control-input" id="self_image1" value="ไม่มี" name="self_image"  >
                                <label class="custom-control-label" for="self_image1">ไม่มี</label>
                            </div>
                            <div class="custom-control custom-radio col-sm-1">
                                <input type="radio" class="custom-control-input" id="self_image2" value="มี" name="self_image"  >
                                <label class="custom-control-label" for="self_image2">มี</label>
                            </div>
                            <div class="col-sm-4">
                                <div class="row">
                                    <input type="text" class="form-control form-control-sm" id="self_image_detail" name="self_image_detail">
                                </div>
                            </div>
                        </div><p></p>
                        <div class="row">
                            <label class="col-sm-2 text-right"><B>กระทบต่อความสามารถ</B></label>
                            <div class="custom-control custom-radio col-sm-1">
                                <input type="radio" checked="checked" class="custom-control-input" id="self_activity1" value="ไม่มี" name="self_activity"  >
                                <label class="custom-control-label" for="self_activity1">ไม่มี</label>
                            </div>
                            <div class="custom-control custom-radio col-sm-1">
                                <input type="radio" class="custom-control-input" id="self_activity2" value="มี" name="self_activity"  >
                                <label class="custom-control-label" for="self_activity2">มี</label>
                            </div>
                            <div class="col-sm-4">
                                <div class="row">
                                    <input type="text" class="form-control form-control-sm" id="self_activity_detail" name="self_activity_detail">
                                </div>
                            </div>
                        </div><hr>
                        <div class="row">
                            <label class="col-sm-12"><B>บทบาทและสัมพันธภาพ</B></label>
                        </div><p></p>
                        <div class="row">
                            <label class="col-sm-2 text-right"><B>ความเจ็บป่วยมีผลกระทบ</B></label>
                            <div class="custom-control custom-radio col-sm-1">
                                <input type="radio" checked="checked" class="custom-control-input" id="sickness_effect1" value="ไม่มี" name="sickness_effect"  >
                                <label class="custom-control-label" for="sickness_effect1">ไม่มี</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="custom-control custom-radio col-sm-2 offset-sm-2">
                                <input type="radio" class="custom-control-input" id="sickness_effect2" value="มีผลกระทบต่อ" name="sickness_effect"  >
                                <label class="custom-control-label" for="sickness_effect2">มีผลกระทบต่อ</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input" id="sickness_family" value="Y" name="sickness_family"  >
                                <label class="custom-control-label" for="sickness_family">ครอบครัว</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input" id="sickness_occupation" value="Y" name="sickness_occupation"  >
                                <label class="custom-control-label" for="sickness_occupation">อาชีพ</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input" id="sickness_education" value="Y" name="sickness_education"  >
                                <label class="custom-control-label" for="sickness_education">การศึกษา</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input" id="sickness_other" value="Y" name="sickness_other"  >
                                <label class="custom-control-label" for="sickness_other">อื่นๆ</label>
                            </div>
                            <div class="col-sm-3">
                                <div class="row">
                                    <input type="text" class="form-control form-control-sm" id="sickness_other_text" name="sickness_other_text">
                                </div>
                            </div>
                        </div><hr>
                        <div class="row">
                            <label class="col-sm-12"><B>เพศและการเจริญพันธุ์</B></label>
                        </div><p></p>
                        <div id="CheckSexFormInput">
                            <div class="row">
                                <label class="col-sm-2 text-right"><B>ประจำเดือน</B></label>
                                <div class="custom-control custom-radio col-sm-1" onchange="onchange_period()">
                                    <input type="radio" checked="checked" class="custom-control-input" id="period1" value="ยังไม่มี" name="period" >
                                    <label class="custom-control-label" for="period1">ยังไม่มี</label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="custom-control custom-radio col-sm-1 offset-sm-2" onchange="onchange_period()">
                                    <input type="radio" class="custom-control-input" id="period2" value="มี" name="period" >
                                    <label class="custom-control-label" for="period2">มี</label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="custom-control custom-radio col-sm-1 offset-sm-3">
                                    <input type="radio" class="custom-control-input" id="period_normal1" value="ปกติ" name="period_normal" >
                                    <label class="custom-control-label" for="period_normal1">ปกติ</label>
                                </div>
                            </div><p></p>
                            <div class="row">
                                <div class="custom-control custom-radio col-sm-1 offset-sm-3">
                                    <input type="radio" class="custom-control-input" id="period_normal2" value="ผิดปกติ" name="period_normal" >
                                    <label class="custom-control-label" for="period_normal2">ผิดปกติ</label>
                                </div>
                                <div class="col-sm-4">
                                    <div class="row">
                                        <input type="text" class="form-control form-control-sm" id="period_disorders" name="period_disorders">
                                    </div>
                                </div>
                            </div><p></p>
                            <div class="row">
                                <div class="custom-control custom-radio col-sm-1 offset-sm-3">
                                    <input type="radio" class="custom-control-input" id="period_normal3" value="LMP" name="period_normal"  >
                                    <label class="custom-control-label" for="period_normal3">LMP</label>
                                </div>
                                <div class="col-sm-4">
                                    <div class="row">
                                        <input type="text" class="form-control form-control-sm" id="period_lmp" name="period_lmp">
                                    </div>
                                </div>
                            </div><p></p>
                            <div class="row">
                                <div class="custom-control custom-radio col-sm-2 offset-sm-2" onchange="onchange_period()">
                                    <input type="radio" class="custom-control-input" id="period3" value="หมดประจำเดือน" name="period" >
                                    <label class="custom-control-label" for="period3">หมดประจำเดือน เมื่ออายุ</label>
                                </div>
                                <div class="col-sm-1">
                                    <div class="row">
                                        <input type="text" class="form-control form-control-sm" id="period_menopause" name="period_menopause">
                                    </div>
                                </div>
                                <div class="col-sm-1">
                                    <label>ปี</label>
                                </div>
                            </div><p></p>
                        </div>
                        <div class="row">
                            <label class="col-sm-2 text-right"><B>เต้านม</B></label>
                            <div class="custom-control custom-radio col-sm-1">
                                <input type="radio" checked="checked" class="custom-control-input" id="breast1" value="ปกติ" name="breast"  >
                                <label class="custom-control-label" for="breast1">ปกติ</label>
                            </div>
                        </div><p></p>
                        <div class="row">
                            <div class="custom-control custom-radio col-sm-2 offset-sm-2">
                                <input type="radio" class="custom-control-input" id="breast2" value="ผิดปกติ" name="breast"  >
                                <label class="custom-control-label" for="breast2">ผิดปกติ(ระบุ)</label>
                            </div>
                            <div class="col-sm-4">
                                <div class="row">
                                    <input type="text" class="form-control form-control-sm" id="breast_disorders" name="breast_disorders">
                                </div>
                            </div>
                        </div><hr>
                        <div class="row">
                            <label class="col-sm-12"><B>การปรับตัวและทนต่อความเคลียด</B></label>
                        </div><p></p>
                        <div class="row">
                            <label class="col-sm-4 text-right"><B>วิธีแก้ไขความไม่สบายใจ/กังวล/เคลียด/อื่นๆ</B></label>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" checked="checked" class="custom-control-input" id="consult" value="Y" name="consult"  >
                                <label class="custom-control-label" for="consult">ปรึกษา</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input" id="seclude" value="Y" name="seclude"  >
                                <label class="custom-control-label" for="seclude">แยกตัว</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input" id="medication" value="Y" name="medication"  >
                                <label class="custom-control-label" for="medication">ใช้ยา</label>
                            </div>
                            <div class="col-sm-3">
                                <div class="row">
                                    <input type="text" class="form-control form-control-sm" id="medication_detail" name="medication_detail">
                                </div>
                            </div>&nbsp;&nbsp;
                        </div><p></p>
                        <div class="row">
                            <div class="custom-control custom-checkbox col-sm-1 offset-sm-4">
                                <input type="checkbox" class="custom-control-input" id="religion" value="Y" name="religion"  >
                                <label class="custom-control-label" for="religion">ศาสนา</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input" id="coping_stress_other" value="Y" name="coping_stress_other"  >
                                <label class="custom-control-label" for="coping_stress_other">อื่นๆ</label>
                            </div>
                            <div class="col-sm-4">
                                <div class="row">
                                    <input type="text" class="form-control form-control-sm" id="coping_stress_other_detail" name="coping_stress_other_detail">
                                </div>
                            </div>
                        </div><hr>
                        <div class="row">
                            <label class="col-sm-12"><B>คุณค่าและความเชื่อ</B></label>
                        </div><p></p>
                        <div class="row">
                            <label class="col-sm-4 text-right"><B>เชื่อว่าการเจ็บป่วยครั้งนี้มีสาเหตุจาก</B></label>
                            <div class="custom-control custom-checkbox col-sm-2">
                                <input type="checkbox" checked="checked" class="custom-control-input" id="belief_sickness_behave" value="Y" name="belief_sickness_behave"  >
                                <label class="custom-control-label" for="belief_sickness_behave">ไปปฏิบัติตัวไม่ถูกต้อง</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-1">
                                <input type="checkbox" class="custom-control-input" id="belief_sickness_age" value="Y" name="belief_sickness_age"  >
                                <label class="custom-control-label" for="belief_sickness_age">ตามวัย</label>
                            </div>
                            <div class="custom-control custom-checkbox col-sm-2">
                                <input type="checkbox" class="custom-control-input" id="belief_sickness_destiny" value="Y" name="belief_sickness_destiny"  >
                                <label class="custom-control-label" for="belief_sickness_destiny">เคราะห์กรรม</label>
                            </div>
                        </div><p></p>
                        <div class="row">
                            <div class="custom-control custom-checkbox col-sm-1  offset-sm-4">
                                <input type="checkbox" class="custom-control-input" id="belief_sickness_other" value="Y" name="belief_sickness_other"  >
                                <label class="custom-control-label" for="belief_sickness_other">อื่นๆ</label>
                            </div>
                            <div class="col-sm-5">
                                <div class="row">
                                    <input type="text" class="form-control form-control-sm" id="belief_sickness_other_text" name="belief_sickness_other_text">
                                </div>
                            </div>
                        </div><p></p>
                        <div class="row">
                            <label class="col-sm-4 text-right"><B>สิ่งยึดเหนี่ยวด้านจิตใจ</B></label>
                            <div class="custom-control custom-radio col-sm-1">
                                <input type="radio" checked="checked" class="custom-control-input" id="belief_believe1" value="ไม่มี" name="belief_believe"  >
                                <label class="custom-control-label" for="belief_believe1">ไม่มี</label>
                            </div>
                            <div class="custom-control custom-radio col-sm-1">
                                <input type="radio" class="custom-control-input" id="belief_believe2" value="มี" name="belief_believe"  >
                                <label class="custom-control-label" for="belief_believe2">มี</label>
                            </div>
                            <div class="col-sm-4">
                                <div class="row">
                                    <input type="text" class="form-control form-control-sm" id="belief_believe_text" name="belief_believe_text">
                                </div>
                            </div>
                        </div><p></p>
                        <div class="row">
                            <label class="col-sm-4 text-right"><B>ความต้องการปฏิบัติกิจกรรมทางศาสนา</B></label>
                            <div class="custom-control custom-radio col-sm-1">
                                <input type="radio" checked="checked" class="custom-control-input" id="religious_activity1" value="ไม่ต้องการ" name="religious_activity"  >
                                <label class="custom-control-label" for="religious_activity1">ไม่ต้องการ</label>
                            </div>
                            <div class="custom-control custom-radio col-sm-2">
                                <input type="radio" class="custom-control-input" id="religious_activity2" value="ต้องการ" name="religious_activity"  >
                                <label class="custom-control-label" for="religious_activity2">ต้องการ (ระบุ)</label>
                            </div>
                            <div class="col-sm-3">
                                <div class="row">
                                    <input type="text" class="form-control form-control-sm" id="religious_activity_text" name="religious_activity_text">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <?php if( (($nurse_admission_note_id == null)) || ( ($nurse_admission_note_id != null)) ){?>
                                        <button type="button" class="btn btn-primary" onclick="onclick_save_nurse_admission_note()" ><i class="fas fa-save"></i> บันทึก</button>
                                <?php }?>
                                <a href="ipd-nurse-admission-note-pdf.php?an=<?php echo $an;?>&loginname=<?php echo $loginname; ?>" target="_blank" class="btn btn-secondary"><i class="fas fa-file-pdf"></i> Print <U>PDF</U> File</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div><br>
    </div>
    <div id="data_nurse_admission_note_edit"></div>
    <div id="show_check_save"></div>
    <div class="form-group text-center">
        <input type="hidden" id="an" name="an" value="<?=htmlspecialchars($an)?>">
        <input type="hidden" id="hn" name="hn" value="<?=htmlspecialchars($hn)?>">
        <input type="hidden" id="nurse_admission_note_id" name="nurse_admission_note_id" value="<?=htmlspecialchars($nurse_admission_note_id)?>">
        <input type="hidden" id="version" name="version" value="<?=htmlspecialchars($version)?>">
    </div>
</form>
<script>
    $( document ).ready(function() {
        var nurse_admission_note_id =  <?=json_encode($nurse_admission_note_id)?>;
        if(nurse_admission_note_id != null && nurse_admission_note_id != ""){
            nurse_admission_note_id_edit(<?=json_encode($nurse_admission_note_id)?>,<?=json_encode($an)?>);
        }
        functionCheckSexFormInput();
    });

    function nurse_admission_note_id_edit(nurse_admission_note_id,an){
        var url="ipd-nurse-admission-note-edit.php";
        $.post(url,{nurse_admission_note_id,an},function(data_edit){
            $("#data_nurse_admission_note_edit").html(data_edit);
            //console.log(data_edit);
        });
    }

    function onclick_save_nurse_admission_note(){
        var nurse_admission_note_id =  <?=json_encode($nurse_admission_note_id)?>;
        var url_save = "ipd-nurse-admission-note-save.php";
        var url_update = "ipd-nurse-admission-note-update.php";
        var admit_firsth = $("#nurse_admission_note").serialize();
        if(nurse_admission_note_id != null && nurse_admission_note_id != ""){
            $.post(url_update,admit_firsth,function(data_update){
                $("#show_check_update").html(data_update);
                alert("บันทึกข้อมูลสำเร็จ");
                window.location.reload(true);
            }).fail(function(xhr, status, error) {
                alert(error);
            });
        }else{
            $.post(url_save,admit_firsth,function(data){
                $("#show_check_save").html(data);
                alert("บันทึกข้อมูลสำเร็จ");
                window.location.reload(true);
            }).fail(function(xhr, status, error) {
                alert(error);
            });
        }

    }

    function onchange_no_mental_state(){
        if($("#no_mental_state").is(':checked')){
            $('.onchange_no_mental_state_disabled').attr("disabled", true);
            $('.onchange_no_mental_state_disabled').prop("checked", false );
            $('#behaviour_other_text').val('').attr("disabled", true);
            $('#emotional_other_text').val('').attr("disabled", true);
            $('#spiritual_other_text').val('').attr("disabled", true);
            $('#spiritual_cant_rated_text').val('').attr("disabled", true);
            $('#no_mental_state_text').removeAttr("disabled"); //ประเมินสภาพจิตใจไม่ได้เนื่องจาก
        }else{
            $('.onchange_no_mental_state_disabled').removeAttr("disabled");
            $('#behaviour_other_text').removeAttr("disabled");
            $('#emotional_other_text').removeAttr("disabled");
            $('#spiritual_other_text').removeAttr("disabled");
            $('#spiritual_cant_rated_text').removeAttr("disabled");
            $('#no_mental_state_text').val('').attr("disabled", true);//ประเมินสภาพจิตใจไม่ได้เนื่องจาก

            $('#normal_behav').prop("checked", true );
            $('#normal_emotional').prop("checked", true );
            $('#no_anxiety').prop("checked", true );
            $('#spiritual_no').prop("checked", true );
        }
    }

    function onchange_period(){
        var period = $("input[name='period']:checked").val();
        if(period != "มี"){
            $("input[name='period_normal']").prop("checked", false).attr("disabled", true);
            $('#period_disorders').val('');
            $('#period_lmp').val('');
            $('#period_disorders').val('').attr("disabled", true);
            $('#period_lmp').val('').attr("disabled", true);
        }else{
            $("input[name='period_normal']").removeAttr("disabled");
            $('#period_disorders').removeAttr("disabled");
            $('#period_lmp').removeAttr("disabled");
        }
    }

    function functionCheckSexFormInput(){
        var sex =  <?=json_encode($sex)?>;
        if(sex == '1'){
            $("#CheckSexFormInput").hide();
            $('#period1').prop("checked", false );
        }else{
            $("#CheckSexFormInput").show();
            onchange_period();
        }
    }
</script>
