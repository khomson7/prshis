<?php  // require_once './project/function/Session.php';
       // Session::checkPermissionAndShowMessage('IPD_DISCHARGE_SUMMARY','VIEW');
       require_once '../include/Session.php';
       
// Session::checkPermissionAndShowMessage('IPD_NURSE_ADDMISSION_NOTE','VIEW');
require_once '../mains/main-report.php';
require_once '../mains/opd-show-patient-main.php'; //เป็นส่วนที่แสดง ข้อมูลผู้ป่วย เช่น รูป,hn,an,ชื่อ-สกุล,แพ้ยา ฯลฯ
require_once '../mains/opd-show-patient-main-sticky.php';
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';


$conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
$hn = empty($_REQUEST['hn']) ? null : $_REQUEST['hn'];
$vn = empty($_REQUEST['vn']) ? null : $_REQUEST['vn'];
// $vn= '111';
$login = empty($_REQUEST['loginname']) ? null : $_REQUEST['loginname'];
//  $hn = '000000001';
// $hn = KphisQueryUtils::getHnByAn($an);
// $vn = KphisQueryUtils::getVnByAn($an);
// $vn = KphisQueryUtils::getVnByHn($hn);
//$vn = $_SESSION['vn'];
$an_parameters = ['vn' => $vn];
$hn_parameters = ['hn' => $hn];
$loginname = $_SESSION['loginname'];
$values = ['loginname' => $loginname];


//echo $vn;
//  echo  $loginname;



if ($login != $loginname) {
    session_start();
    session_destroy();
}

Session::insertSystemAccessLog(json_encode(array(
    'form'=>'NIHSS-SCORE-FORM',
    'vn'=>$vn,
),JSON_UNESCAPED_UNICODE));



//-------------------------Doctor admission note
$sql = "SELECT *
                FROM `prs_nihss_score`
                WHERE vn = :vn
                ORDER BY id ASC";
$stmt = $conn->prepare($sql);
$stmt->execute(['vn' => $vn]);
if ($row  = $stmt->fetch()) {
    $id = $row['id'];
    $version = $row['version'];
} else {
    $id = null;
    $version = null;
}


echo $vn ;
      
        //----------------------เช็คว่า an นี้ มีข้อมูลหรือไม่
        /*
        $sql = "SELECT * FROM ".DbConstant::KPHIS_DBNAME.".prs_nihss_score WHERE an = :an ";
        $id  = null;
        $parameters['an'] = $an;
        $stmt = $conn->prepare($sql);
        $stmt->execute($parameters);
        $row = $stmt->fetch();
        if($row['count_row'] > 0){
            $id = $row['id'];
        }

        */
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
    </style>




<form id="hospital_nihss_score_form">
    <div class="container-fluid">
        <div class="row">
            <div class="col-auto">
                <button type="button" class="btn btn-sm btn-primary btn-block" onclick="window.close()"><i class="fas fa-arrow-left"></i> กลับ</button>
            </div>
            <div class="col-auto p-1 font-weight-bold">
                SURIN HOSPITAL NIHSS SCORE SHEET
            </div>
        </div><hr>

        <div class="row">
                        <div class="col-md-12">
                            
                            <div class="form-group row">
                                <div class="col-sm-1"></div>
                                <label>วันที่</label>
                                <div class="col-sm-2">
                                    <input type="date" class="form-control form-control-sm" id="rxdate" name="rxdate" value="<?= (isset($row['rxdate']) ? htmlspecialchars($row['rxdate']) : '') ?>">
                                </div>
                                <label>เวลา</label>
                                <div class="col-sm-2">
                                    <input type="time" class="form-control form-control-sm" id="rxtime" name="rxtime" value="<?= (isset($row['rxtime']) ? htmlspecialchars($row['rxtime']) : '') ?>">
                                </div>
                                <label>Onset time</label>
                                <div class="col-sm-1">
                                <input type="number" placeholder="" class="form-control form-control-sm" value="<?= (isset($row['onset_h']) ? htmlspecialchars($row['onset_h']) : '') ?>" id="onset_h" name="onset_h" min="0" max="59">
                                </div>
                                <label>ชั่วโมง</label>
                                <div class="col-sm-1">
                                <input type="number" placeholder="" class="form-control form-control-sm" value="<?= (isset($row['onset_m']) ? htmlspecialchars($row['onset_m']) : '') ?>" id="onset_m" name="onset_m" min="0" max="59">
                                </div>
                                <label>นาที</label>
                            </div>

    </br>

        <div class="col-md-4">
                <div class="form-group">
                    <label class="mb-3" for="action-person-nurse">ลงชื่อแพทย์เวร ER</label>
                    <div class="input-group">
                        <input type="text" class="form-control form-control" id="nurse_name"  name="nurse_name"  value="<?=htmlspecialchars($row['nurse_name'])?>" readonly>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-secondary" onclick="PersonAsCurrentUser_1()">ลงชื่อ</button>
                        </div>
                    </div>
                </div>
            </div>
   <div class="row">
        <div class="col-md-11">

            <div class="row">

        <div class="col-md-10">
           <!-- begin table -->
            <table id="bg-table" width="100%" style="border-collapse: collapse;font-size:10pt;margin-top:8px;">
            <!-- 1-->
            <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="5%"><div class="col-auto p-1 font-weight-bold">NIHSS ITEM</div></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="10%"><div class="col-auto p-1 font-weight-bold">SCORE DIFINITIONS</div></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%"><div class="col-auto p-1 font-weight-bold"> <span>ก่อนฉีดยา</span></br>.................. </div> </td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%"><div class="col-auto p-1 font-weight-bold"> <span>หลังฉีดยา</span></br>.................. </div> </td>
           

           <!-- 2 -->
           <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;1a. Consciousness</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;0 = รู้สึกตัวดี ตอบสนองเป็นปกติ</br>&nbsp;1 = ง่วงซึม ปลุกตื่นง่าย เมื่อตื่นตอบคำถามถูก ทำตามคำสั่งได้
            </br>&nbsp;2 = ซึมมากต้องการกระตุ้นแรงๆ หรือกระตุ้น pain ถึงเคลื่อนไหว
            </br>&nbsp;3 = ไม่ตอบสนองแต่สามารถตรวจพบ reflex ได้ 
        </div></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <input type="text" class="form-control form-control-sm CheckPer_2" value="<?= (isset($row['no1a']) ? htmlspecialchars($row['no1a']) : '') ?>" name="no1a" id="no1a">
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <input type="text" class="form-control form-control-sm CheckPer_2" value="<?= (isset($row['no1a_2']) ? htmlspecialchars($row['no1a_2']) : '') ?>" name="no1a_2" id="no1a_2">
           </td>
           
            
           <!-- 2 -->
           <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;1b. Question</br>&nbsp;ถามเดือนและอายุใช้คำตอบแรกที่ผู้ป่วยตอบ</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;0 = ตอบได้ถูกทั้ง 2 คำถาม</br>&nbsp;1 = ตอบถูกหนึ่งคำถาม,ET tube, serve dysarthria, trachea injury
            </br>&nbsp;2 = ไม่สามารถตอบได้หรือตอบผิดทั้ง 2 ข้อ, 1a:2 หรือ 3, aphasia
        </div></td>
        <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <input type="text" class="form-control form-control-sm CheckPer_2" value="<?= (isset($row['no1b']) ? htmlspecialchars($row['no1b']) : '') ?>" name="no1b" id="no1b">
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <input type="text" class="form-control form-control-sm CheckPer_2" value="<?= (isset($row['no1b_2']) ? htmlspecialchars($row['no1b_2']) : '') ?>" name="no1b_2" id="no1b_2">
           </td>
        

           <!-- 4 -->
           <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;1c. Commands </br>&nbsp;ให้หลับตา ลืมตาและกำมือ แบมือ </td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;0 = ทำได้ถูกต้องทั้ง 2 อย่าง</br>&nbsp;1 = ทำได้ถูกต้องเพียงอย่างเดียว
            </br>&nbsp;2 = ไม่ทำตามสั่ง หรือทำไม่ถูกต้อง
        </div></td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <input type="text" class="form-control form-control-sm CheckPer_2" name="no48_1_text" id="no48_1_text">
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <input type="text" class="form-control form-control-sm CheckPer_2" name="no48_1_text" id="no48_1_text">
           </td>
           
            
           <!-- 2 -->
           <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;2.Best Gaze</br>&nbsp;การเคลื่อนไหวลูกตา ให้กลอกตาไปมา มองซ้ายขวา บน-ล่าง</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;0 = มองตามได้ปกติ กลอกตาได้ปกติทุกทิศทาง</br>&nbsp;1 = ตาข้างใดข้างหนึ่งหรือทั้งสองข้างเหลือบมองไปด้านข้างไม่สุด
            </br>&nbsp;2 = กลอกตาไม่ได้ หรือตามองไปด้านใดด้านหนึ่งตลอดเวลา
        </div></td>
        <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <input type="text" class="form-control form-control-sm CheckPer_2" name="no48_1_text" id="no48_1_text">
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <input type="text" class="form-control form-control-sm CheckPer_2" name="no48_1_text" id="no48_1_text">
           </td>
          

        <!-- 5 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;3.Visual Field</br>&nbsp;การมองเห็น โดยให้มองนิ้วผู้ตรวจ ตาบอด
            </br>&nbsp;ให้ตรวจข้างที่ดีถ้าปกติให้ดู visual neglect</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;0 = มองเห็นปกติ ลานสายตาปกติ</br>&nbsp;1 = ลานสายตาผิดปกติบางส่วน (partial hemianopia),visual neglect
            </br>&nbsp;2 = ลานสายตาผิดปกติครึ่งซีก (complete hemianopia)
            </br>&nbsp;3 = มองไม่เห็นทั้ง 2 ข้าง (ตาบอด)
        </div></td>
        <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <input type="text" class="form-control form-control-sm CheckPer_2" name="no48_1_text" id="no48_1_text">
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <input type="text" class="form-control form-control-sm CheckPer_2" name="no48_1_text" id="no48_1_text">
           </td>
        

        <!-- 6 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;4.Facial Palsy</br>&nbsp;การเคลื่อนไหวกล้ามเนื้อใบหน้า
            </br>&nbsp;ให้หลับตาและยิงฟัน</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;0 = ไม่พบอาการอ่อนแรงของกล้ามเนื้อใบหน้า</br>&nbsp;1 = กล้ามเนื้อใบหน้าอ่อนแรงเล็กน้อย มุมปากตกหรือไม่เท่ากัน
            </br>&nbsp;2 = กล้ามเนื้อใบหน้าอ่อนแรงมาก แต่ยังพอเคลื่อนไหวได้บ้าง
            </br>&nbsp;3 = ไม่สามารถเคลื่อนไหวกล้ามเนื้อใบหน้าได้ หลับตาไม่สนิทยิงฟันไม่ได้
        </div></td>
        <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <input type="text" class="form-control form-control-sm CheckPer_2" name="no48_1_text" id="no48_1_text">
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <input type="text" class="form-control form-control-sm CheckPer_2" name="no48_1_text" id="no48_1_text">
           </td>
          

        <!-- 7 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;5.Moter Arm</br>&nbsp;ถ้านั่งได้ให้ยกแขน 90 องศา ถ้านอน
            </br>&nbsp;ยกแขน 45 องศา</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;0 = ปกติไม่อ่อนแรง สามารถยกแขนได้นาน 10 วินาที</br>&nbsp;1 = อ่อนแรงยกแขนไม่ได้ถึง 10 วินาที แต่แขนไม่ตกลงบนเตียง
            </br>&nbsp;2 = ยกแขนขึ้นได้บ้างแต่ไม่สามารถคงไว้ในตำแหน่งที่ต้องการได้จากนั้นตกลงเตียง
            </br>&nbsp;3 = ไม่สามารถยกแขนต้านแรงโน้มถ่วงของโลก
            </br>&nbsp;4 = ไม่มีการเคลื่อนไหวของกล้ามเนื้อแขน
            </br>&nbsp;UN = แขนพิการหรือถูกตัด หรือพบมีปัญหาข้อติดยึด
        </div></td>
        <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           Rt <input type="text" class="form-control form-control-sm CheckPer_2" name="no48_1_text" id="no48_1_text"></br>
           Lt <input type="text" class="form-control form-control-sm CheckPer_2" name="no48_1_text" id="no48_1_text">
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           Rt <input type="text" class="form-control form-control-sm CheckPer_2" name="no48_1_text" id="no48_1_text"></br>
           Lt <input type="text" class="form-control form-control-sm CheckPer_2" name="no48_1_text" id="no48_1_text">
           </td>
             
          

        <!-- 9 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;6.leg Strength</br>&nbsp;นอนหงายแล้วยกขาขึ้น 30 องศา นาน 5 วินาที
            </td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;0 = ปกติ ไม่อ่อนแรง ยกขาทำมุม 30 องศา นานตลอด 5 วินาที
            </br>&nbsp;1 = อ่อนแรงเล็กน้อย ยกขาได้ไม่ถึง 5 วินาที แต่ขาไม่ตกลงบนเตียง
            </br>&nbsp;2 = สามารถยกขาได้ แต่ตกลงมาอย่างรวดเร็วก่อน 5 วินาที
            </br>&nbsp;3 = ไม่สามารถยกขาขึ้นจากเตียงในท่านอนหงาย
            </br>&nbsp;4 = ไม่มีการเคลื่อนไหวของกล้ามเนื้อขา
            </br>&nbsp;UN = ขาพิการหรือถูกตัด หรือพบมีปัญหาข้อติดยึด
        </div></td>
        <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           Rt <input type="text" class="form-control form-control-sm CheckPer_2" name="no48_1_text" id="no48_1_text"></br>
           Lt <input type="text" class="form-control form-control-sm CheckPer_2" name="no48_1_text" id="no48_1_text">
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           Rt <input type="text" class="form-control form-control-sm CheckPer_2" name="no48_1_text" id="no48_1_text"></br>
           Lt <input type="text" class="form-control form-control-sm CheckPer_2" name="no48_1_text" id="no48_1_text">
           </td>
          
         

        <!-- 10 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;7.Ataxia</br>&nbsp;การประสานงานของแขน ขา
            </br>&nbsp;finger to nose or heel to shin test</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;0 = การประสานงานของกล้ามเนื้อแขนขา ทั้ง 2 ข้างปกติ
            </br>&nbsp;1 = มีปัญหาการประสานงานของแขนหรือขา 1 ข้าง
            </br>&nbsp;2 = มีปัญหาการประสานงานของแขนหรือขา 2 ข้าง
            </br>&nbsp;UN = แขนหรือขาพิการถูกตัด หรือพบมีปัญหาข้อติดยึด
        </div></td>
        <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           Rt <input type="text" class="form-control form-control-sm CheckPer_2" name="no48_1_text" id="no48_1_text"></br>
           Lt <input type="text" class="form-control form-control-sm CheckPer_2" name="no48_1_text" id="no48_1_text">
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           Rt <input type="text" class="form-control form-control-sm CheckPer_2" name="no48_1_text" id="no48_1_text"></br>
           Lt <input type="text" class="form-control form-control-sm CheckPer_2" name="no48_1_text" id="no48_1_text">
           </td>
         

        <!-- 11 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;8.Sensory</br>&nbsp;การรับความรู้สึก (วัตถุปลายแหลม)
      </td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;0 = การรับความรู้สึกปกติ
            </br>&nbsp;1 = สูญเสียการรับรู้สึกเล็กน้อยถึงปานกลาง เมื่อใช้ของแหลม
            </br>&nbsp;ทดสอบจะรู้สึกลดลงแต่สามารถบอกได้ถึงความรู้สึกบริเวณถูกกระตุ้น
            </br>&nbsp;2 = สูญเสียการรับความรู้สึกในระดับรุนแรง ไม่รู้สึกบริเวณถูกสัมผัส
            </br>&nbsp;1a: 3, quadriplegia
        </div></td>
        <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           Rt <input type="text" class="form-control form-control-sm CheckPer_2" name="no48_1_text" id="no48_1_text"></br>
           Lt <input type="text" class="form-control form-control-sm CheckPer_2" name="no48_1_text" id="no48_1_text">
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           Rt <input type="text" class="form-control form-control-sm CheckPer_2" name="no48_1_text" id="no48_1_text"></br>
           Lt <input type="text" class="form-control form-control-sm CheckPer_2" name="no48_1_text" id="no48_1_text">
           </td>
          

        <!-- 12 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;9.Language ความสามารถด้านการใช้
        </br>&nbsp;ภาษา โดยให้ดูภาพแล้วบรรยายและบอก
        </br>&nbsp;สิ่งของที่มองเห็น
      </td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;0 = การสื่อสารภาษาปกติ
            </br>&nbsp;1 = การสื่อภาษาสูญเสียในระดับน้อยถึงปานกลาง พูดตะกุกตะกัก ไม่
            </br>&nbsp;เข้าใจภาพ แต่ผู้ทดสอบยังพอเข้าใจว่ากำลังพูดถึงอะไร
            </br>&nbsp;2 = การสื่อสารทางถาษาสูญเสียอย่างรุนแรง ไม่สามารถสื่อสารให้
            </br>&nbsp;เข้าใจได้และผู้ทดสอบไม่สามารถทราบได้ว่ากำลังพูดถึงอะไร
            </br>&nbsp;3 = ไม่พูดหรือไม่เข้าใจภาษาที่ผู้ตรวจพยายามสื่อสารและไม่สามารถ
            </br>&nbsp;แสดงท่าทาง พูดหรือเขียนให้ผู้อื่นเข้าใจได้ม 1a:3
        </div></td>
        <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <input type="text" class="form-control form-control-sm CheckPer_2" name="no48_1_text" id="no48_1_text">
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <input type="text" class="form-control form-control-sm CheckPer_2" name="no48_1_text" id="no48_1_text">
           </td>
       

        <!-- 13 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;10.Dysarthria
        </br>&nbsp;การออกเสียงพูด      
    </td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;0 = การเปล่งเสียงพูดได้ชัดเจนปกติ
            </br>&nbsp;1 = พูดไม่ชัดเล็กน้อยถึงปานกลางแต่สามารถฟังรู้เรื่อง
            </br>&nbsp;2 = พูดไม่ชัดอย่างมาก หรือไม่พูด ไม่สามารถเข้าใจคำพูดของผู้ป่วย
            </br>&nbsp;ได้โดยไม่มีความผิดปกติของความเข้าใจภาษา, global aphasia, ไม่พูดเลย
            </br>&nbsp;UN = ผู้ป่วยใส่ท่อช่วยหายใจหรือมีภาวะอย่างอื่นที่ทำให้ไม่สามารถพูดได้
        </div></td>
        <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <input type="text" class="form-control form-control-sm CheckPer_2" name="no48_1_text" id="no48_1_text">
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <input type="text" class="form-control form-control-sm CheckPer_2" name="no48_1_text" id="no48_1_text">
           </td>

           <tr style="border:1px solid #000;margin: 45px;">
           <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;11.Inattenion
        </br>&nbsp;การขาดความสนใจด้านใดด้านหนึ่งของร่างกาย   
    </td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;0 = ไม่มีความผิดปกติ ทั้งการมองเห็น การได้ยิน หรือการสัมผัส
            </br>&nbsp;1 = พบความผิดปกติของการรับรู้ทั้งการมองเห็น สัมผัส ได้ยิน เมื่อมีการกระตุ้นทั้ง 2 ข้างพร้อมกัน
            </br>&nbsp;2 = มีความผิดปกติของการรับรู้มากกว่า 1 ชนิด หรือไม่รับรู้ว่าเป็นมือของตน หรือสนใจสิ่งกระตุ้นเพียงด้านเดียว

        </div></td>
        <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <input type="text" class="form-control form-control-sm CheckPer_2" name="no48_1_text" id="no48_1_text">
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <input type="text" class="form-control form-control-sm CheckPer_2" name="no48_1_text" id="no48_1_text">
           </td>
      
           <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="5%">&nbsp;รวมคะแนน
        </td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%"></br>&nbsp;</br>&nbsp;</br>
        </div></td>
        <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <input type="text" class="form-control form-control-sm CheckPer_2" name="no48_1_text" id="no48_1_text">
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <input type="text" class="form-control form-control-sm CheckPer_2" name="no48_1_text" id="no48_1_text">
           </td>
 

        </tr>

            </table>
         <!-- end table -->

         </div>

         <div class="col-md-11">
           <!-- begin table -->
            
            <h2 style="text-align:right;font-size:10pt;">Stroke 2:09/01/57</h2>

            
         <!-- end table -->

       
    </br>
       
                  

            


         </div>

    </div>


        </div>
    </div>


        <div class="row">
        <div id="show_check_save"></div>
            <input type="hidden" id="vn" name="vn" value="<?=$vn?>"><!-- ฟิลด์ hidden  "an"  -->
            <input type="hidden" id="id" name="id" value="<?=$id?>"><!-- ฟิลด์ hidden "id"  -->
            <input type="hidden" id="version" name="version" value="<?=$version?>">


            <div class="col-md-9">
                <div id="data_hospital_nihss_score_save"></div><!-- แสดงข้อความการบันมึก >> บันทึกข้อมูลสำเร็จ, EORROR -->
                <div id="data_hospital_nihss_score_edit"></div>
                <div id="data_hospital_nihss_score_update"></div>
            </div>
            <div class="col-md-12 text-right">
                <?php
                if((($id == null)) || (($id != null))){?>
                    <button type="button" class="btn btn-primary" id="btn_hospital_nihss_score" onclick="hospital_nihss_score_save()"><i class="fas fa-save"></i> บันทึก</button>
                <?php } ?>
                <a href="hospital-nihss-score-pdf.php?vn=<?php echo $vn;?>&loginname=<?php echo $loginname; ?>" target="_blank" class="btn btn-secondary"><i class="fas fa-file-pdf"></i> Print <U>PDF</U> File</a>
            </div>
        </div><br>
    </div>
</form>
<script>

function PersonAsCurrentUser_1(){
        const nurse_name = <?=json_encode($_SESSION['name'])?>;
        const entryposition = <?=json_encode($_SESSION['entryposition'])?>;
        $("#nurse_name").val(nurse_name);
        $("#nurse_pos").val(entryposition);
    }
    function PersonAsCurrentUser_2(){
        const nurse_name2 = <?=json_encode($_SESSION['name'])?>;
        const entryposition = <?=json_encode($_SESSION['entryposition'])?>;
        $("#nurse_name2").val(nurse_name2);
        $("#nurse_pos").val(entryposition);
    }
    function PersonAsCurrentUser_3(){
        const nurse_name3 = <?=json_encode($_SESSION['name'])?>;
        const entryposition = <?=json_encode($_SESSION['entryposition'])?>;
        $("#nurse_name3").val(nurse_name3);
        $("#nurse_pos").val(entryposition);
    }

    $( document ).ready(function() {
        var id =  <?=json_encode($id)?>;
        if(id != null && id != ""){
            or_complication_edit(<?=json_encode($id)?>,<?=json_encode($an)?>);
        }else{
           // import_DataOR_Hosxp(<?=json_encode($an)?>);
        }
        //summary_CheckPer();
    });



    function hospital_nihss_score_save(){
      //  var summary_plan_date = $("#summary_plan_date").val();
       // var summary_plan_time = $("#summary_plan_time").val();
        //var principal_diagnosis = $("#principal_diagnosis").val();
        var id = $("#id").val();
        //url
        var url_save = 'hospital-nihss-score-save.php';
       var url_update = 'hospital-nihss-score-update.php';
       var hospital_nihss_score_form = $("#hospital_nihss_score_form").serialize();

   
       if (id == "") {
                $.post(url_save, hospital_nihss_score_form, function(data) {
                        $("#show_check_save").html(data);
                        alert("บันทึกข้อมูลสำเร็จ");
                        window.location.reload(true);
                        //self.close();
                    })
                    .fail(function() {
                        alert("บันทึกข้อมูลไม่สำเร็จ" + error);
                    });
            } else {
                $.post(url_update, hospital_nihss_score_form, function(data) {
                        $("#show_check_save").html(data);
                        alert("บันทึกข้อมูลสำเร็จ");
                        window.location.reload(true);
                        //self.close();
                    })
                    .fail(function() {
                        alert("บันทึกข้อมูลไม่สำเร็จ" + error);
                    });
            }
       // }
    }

    function or_complication_edit(id,an){
        var url="or-complication-edit.php";
        $.post(url,{id,an},function(data_edit){
            $("#data_or_complication_edit").html(data_edit);
            //console.log(data_edit);
        });
    }


</script>
