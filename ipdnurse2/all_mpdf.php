<?php

require_once '../include/Session.php';
 
                 
   $login = empty($_REQUEST['loginname']) ? null : $_REQUEST['loginname'];
   $loginname = $_SESSION['loginname'];
   $values =['loginname'=>$loginname];
   
   //หากพบว่าไม่ตรงกันให้ ทำลาย session เดิมทิ้งไป
   if($login != $loginname){
       session_start();
       session_destroy();              
           
     } 

 Session::checkLoginSessionAndShowMessage(); //เช็ค session
Session::checkPermissionAndShowMessage('DOCUMENT', 'PRINT');
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
//require_once __DIR__ . '/vendor/autoload.php';
require_once '../include/DbUtils.php';
require_once '../include/Session.php';
require_once '../include/KphisQueryUtils.php';

date_default_timezone_set('asia/bangkok');

//echo $_SERVER['DOCUMENT_ROOT'] ;

$conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
$mpdf->AddPageByArray([
    'margin-left' => 6,
    'margin-right' => 6,
    'margin-top' => 6,
    'margin-bottom' => 6,
]);

$admission_note_id = $_REQUEST['admission_note_id'];
$an = empty($_REQUEST['an']) ? null : $_REQUEST['an'];
$hn = KphisQueryUtils::getHnByAn($an);
$query_parameters = ['an' => $an];

Session::insertSystemAccessLog(json_encode(array(
    'report'=>'LR-REPORT1-PDF',
   // 'action'=>'PRINT',
    'an'=>$an,
),JSON_UNESCAPED_UNICODE));

/*
$login = empty($_REQUEST['loginname']) ? null : $_REQUEST['loginname'];
$loginname = $_SESSION['loginname'];
$values = ['loginname' => $loginname];
if ($login != $loginname) {
    session_start();
    session_destroy();
}
*/
$image_uncheck = "<img src='../include/images/check-adm.jpg' width='1.6%' class='check_img'>";
$image_check = "<img src='../include/images/check-1.jpg' width='1.6%' class='check_img'>";
//-------------------------Doctor admission note
$sql = "SELECT *
        FROM `prs_labor_report1`
        WHERE an = :an";
$stmt = $conn->prepare($sql);
$stmt->execute($query_parameters);
$row  = $stmt->fetch();


//ความรู้สึกตัว
//$receive_from      =  $row['receive_from'];
//if($receive_from == "คลอดในโรงพยาบาล"){$receive_from_1  = $image_check;}else{$receive_from_1 = $image_uncheck;}
//if($receive_from != "สับสน"){$receive_from_2 = $image_check;}else{$receive_from_2 = $image_uncheck;}

 $receive_from_1 = '( )';
 if ($row['receive_from'] == 'คลอดในโรงพยาบาล') {$receive_from_1 = '('.$image_check.')';
 }

 $receive_from_2 = '( )';
 if ($row['receive_from'] != 'คลอดในโรงพยาบาล' && $row['receive_from'] != null) {$receive_from_2 = '('.$image_check.')';
    $receive_from  =  htmlspecialchars($row['receive_from']);
 }

 $transport_1 = '( )';
 if ($row['transport'] == 'อุ้มมา') {$transport_1 = '('.$image_check.')';
 }

 $transport_2 = '( )';
 if ($row['transport'] == 'transport incubator') {$transport_2 = '('.$image_check.')';
 }

 $transport_3 = '( )';
 if ($row['transport'] == 'clib') {$transport_3 = '('.$image_check.')';
 }

 $body_1 = '( )';
 if ($row['body'] == 'ปกติ') {$body_1 = '('.$image_check.')';
 }
 
 $body_2 = '( )';
 if ($row['body'] != 'ปกติ' && $row['body'] != null) {$body_2 = '('.$image_check.')';
    $body  =  htmlspecialchars($row['body']);
 }

 $cry_1 = '( )';
 if ($row['cry'] == 'ไม่ร้อง') {$cry_1 = '('.$image_check.')';
 }
 $cry_2 = '( )';
 if ($row['cry'] == 'ร้องเสียงดัง') {$cry_2 = '('.$image_check.')';
 }
 $cry_3 = '( )';
 if (!($row['cry'] == 'ไม่ร้อง' || $row['cry'] == 'ร้องเสียงดัง') && $row['cry'] != null) {$cry_3 = '('.$image_check.')';
    $cry  =  htmlspecialchars($row['cry']);
 }

 $movement_1 = '( )';
 if ($row['movement'] == 'ขยับได้') {$movement_1 = '('.$image_check.')';
 }
 $movement_2 = '( )';
 if ($row['movement'] == 'อ่อนปวกเปียก') {$movement_2 = '('.$image_check.')';
 }
 $movement_3 = '( )';
 if ($row['movement'] == 'ชักเกร็ง') {$movement_3 = '('.$image_check.')';
 }
 $movement_4 = '( )';
 if (!($row['movement'] == 'ขยับได้' || $row['movement'] == 'อ่อนปวกเปียก' || $row['movement'] == 'ชักเกร็ง') && $row['movement'] != null) {$movement_4 = '('.$image_check.')';
    $movement  =  htmlspecialchars($row['movement']);
 }

 $head_1 = '( )';
 if ($row['head'] == 'ปกติ') {$head_1 = '('.$image_check.')';
 }

 $head_2 = '( )';
 if ($row['head'] != 'ปกติ' && $row['head'] != null) {$head_2 = '('.$image_check.')';
    $head =  htmlspecialchars($row['head']);
 }

 $eyes_1 = '( )';
 if ($row['eyes'] == 'ปกติ') {$eyes_1 = '('.$image_check.')';
 }

 $eyes_2 = '( )';
 if ($row['eyes'] != 'ปกติ' && $row['eyes'] != null) {$eyes_2 = '('.$image_check.')';
    $eyes =  htmlspecialchars($row['eyes']);
 }

 $nose_1 = '( )';
 if ($row['nose'] == 'มีรูจมูก 2 ข้าง') {$nose_1 = '('.$image_check.')';
 }
 $nose_2 = '( )';
 if ($row['nose'] == 'รูจมูกตัน') {$nose_2 = '('.$image_check.')';
 }
 $nose_3 = '( )';
 if (!($row['nose'] == 'มีรูจมูก 2 ข้าง' || $row['nose'] == 'รูจมูกตัน') && $row['nose'] != null) {$nose_3 = '('.$image_check.')';
    $nose  =  htmlspecialchars($row['nose']);
 }

 $neck_1 = '( )';
 if ($row['neck'] == 'ปกติ') {$neck_1 = '('.$image_check.')';
 }

 $neck_2 = '( )';
 if ($row['neck'] != 'ปกติ' && $row['neck'] != null) {$neck_2 = '('.$image_check.')';
    $neck =  htmlspecialchars($row['neck']);
 }

 $mouth_1 = '( )';
 if ($row['mouth'] == 'ปกติ') {$mouth_1 = '('.$image_check.')';
 }
 $mouth_2 = '( )';
 if ($row['mouth'] == 'ปากแหว่ง') {$mouth_2 = '('.$image_check.')';
 }
 $mouth_3 = '( )';
 if ($row['mouth'] == 'เพดานโหว่') {$mouth_3 = '('.$image_check.')';
 }
 $mouth_4 = '( )';
 if (!($row['mouth'] == 'ปกติ' || $row['mouth'] == 'ปากแหว่ง' || $row['mouth'] == 'เพดานโหว่') && $row['mouth'] != null) {$mouth_4 = '('.$image_check.')';
    $mouth  =  htmlspecialchars($row['mouth']);
 }
 

 $abdomen_1 = '( )';
 if ($row['abdomen'] == 'ปกติ') {$abdomen_1 = '('.$image_check.')';
 }
 $abdomen_2 = '( )';
 if ($row['abdomen'] == 'ท้องอืด') {$abdomen_2 = '('.$image_check.')';
 }
 $abdomen_3 = '( )';
 if (!($row['abdomen'] == 'ปกติ' || $row['abdomen'] == 'ท้องอืด') && $row['abdomen'] != null) {$abdomen_3 = '('.$image_check.')';
    $abdomen  =  htmlspecialchars($row['abdomen']);
 }

 $navel_1 = '( )';
 if ($row['navel'] == 'ปกติ') {$navel_1 = '('.$image_check.')';
 }
 $navel_2 = '( )';
 if ($row['navel'] == 'Omphalocele') {$navel_2 = '('.$image_check.')';
 }
 $navel_3 = '( )';
 if ($row['navel'] == 'Gastroschisis') {$navel_3 = '('.$image_check.')';
 }
 $navel_4 = '( )';
 if (!($row['navel'] == 'ปกติ' || $row['navel'] == 'Omphalocele' || $row['navel'] == 'Gastroschisis') && $row['navel'] != null) {$navel_4 = '('.$image_check.')';
    $navel  =  htmlspecialchars($row['navel']);
 }

 $spine_1 = '( )';
 if ($row['spine'] == 'ปกติ') {$spine_1 = '('.$image_check.')';
 }

 $spine_2 = '( )';
 if ($row['spine'] != 'ปกติ' && $row['spine'] != null) {$spine_2 = '('.$image_check.')';
    $spine =  htmlspecialchars($row['spine']);
 }

 $limbs_1 = '( )';
 if ($row['limbs'] == 'ปกติ') {$limbs_1 = '('.$image_check.')';
 }

 $limbs_2 = '( )';
 if ($row['limbs'] != 'ปกติ' && $row['limbs'] != null) {$limbs_2 = '('.$image_check.')';
    $limbs =  htmlspecialchars($row['limbs']);
 }

 $genitalia_1 = '( )';
 if ($row['genitalia'] == 'ปกติ') {$genitalia_1 = '('.$image_check.')';
 }

 $genitalia_2 = '( )';
 if ($row['genitalia'] != 'ปกติ' && $row['genitalia'] != null) {$genitalia_2 = '('.$image_check.')';
    $genitalia =  htmlspecialchars($row['genitalia']);
 }

 $anuss_1 = '( )';
 if ($row['anuss'] == 'ปกติ') {$anuss_1 = '('.$image_check.')';
 }

 $anuss_2 = '( )';
 if ($row['anuss'] != 'ปกติ' && $row['anuss'] != null) {$anuss_2 = '('.$image_check.')';
   /* $anuss =  htmlspecialchars($row['anuss']);*/
 }



 $skin_color_1 = '( )';
 if ($row['skin_color'] == 'แดง') {$skin_color_1 = '('.$image_check.')';
 }
 $skin_color_2 = '( )';
 if ($row['skin_color'] == 'ซีด') {$skin_color_2 = '('.$image_check.')';
 }
 $skin_color_3 = '( )';
 if ($row['skin_color'] == 'เขียว') {$skin_color_3 = '('.$image_check.')';
 }
 $skin_color_4 = '( )';
 if (!($row['skin_color'] == 'แดง' || $row['skin_color'] == 'ซีด' || $row['skin_color'] == 'เขียว') && $row['skin_color'] != null) {$skin_color_4 = '('.$image_check.')';
    $skin_color  =  htmlspecialchars($row['skin_color']);
 }

 $behavior_1 = '( )';
 if ($row['behavior'] == 'เฉย') {$behavior_1 = '('.$image_check.')';
 }
 $behavior_2 = '( )';
 if ($row['behavior'] == 'ร้องไห้') {$behavior_2 = '('.$image_check.')';
 }
 $behavior_3 = '( )';
 if (!($row['behavior'] == 'เฉย' || $row['behavior'] == 'ร้องไห้') && $row['behavior'] != null) {$behavior_3 = '('.$image_check.')';
    $behavior  =  htmlspecialchars($row['behavior']);
 }

 $expression_1 = '( )';
 if ($row['expression'] == 'ประเมินไม่ได้') {$expression_1 = '('.$image_check.')';
 }
 $expression_2 = '( )';
 if ($row['expression'] == 'ร้องโกรธ') {$expression_2 = '('.$image_check.')';
 }
 $expression_3 = '( )';
 if (!($row['expression'] == 'ประเมินไม่ได้' || $row['expression'] == 'ร้องโกรธ') && $row['expression'] != null) {$expression_3 = '('.$image_check.')';
    $expression  =  htmlspecialchars($row['expression']);
 }


//-------------------------Doctor admission note
$sql_ipt = "select patient.sex,patient.hn,patient.pname,patient.fname,patient.lname,patient.drugallergy,
            (select GROUP_CONCAT(concat(opd_allergy.agent,'=',if(opd_allergy.symptom is null,',',opd_allergy.symptom)/*,' [',if(note is null,',',note),']'*/)) as name
                from ".DbConstant::HOSXP_DBNAME.".opd_allergy
                where opd_allergy.hn = ipt.hn /*and (opd_allergy.no_alert<>'Y' or opd_allergy.no_alert is null)*/
                order by display_order) as allergy_symptom_string,
            an_stat.age_y,an_stat.age_m,an_stat.age_d,
            ipt.regdate,ipt.regtime,
            ipt.ward,ward.name,
            ipt.pttype, pttype.`name` as pttype_name,
            iptadm.bedno, (select vs.bw from ipd_vs_vital_sign vs where vs.an = ipt.an and vs.bw is not null and trim(vs.bw) <> '' order by vs_datetime desc limit 1) as latest_bw
            , (select vs.height from ipd_vs_vital_sign vs where vs.an = ipt.an and vs.bw is not null and trim(vs.bw) <> '' order by vs_datetime desc limit 1) as latest_height
            from ".DbConstant::HOSXP_DBNAME.".ipt
            left outer join ".DbConstant::HOSXP_DBNAME.".an_stat on an_stat.an=ipt.an
            left outer join ".DbConstant::HOSXP_DBNAME.".patient on patient.hn=ipt.hn
            left outer join ".DbConstant::HOSXP_DBNAME.".ward on ward.ward=ipt.ward
            LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".pttype ON pttype.pttype = ipt.pttype
            LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".iptadm ON iptadm.an = ipt.an
            WHERE ipt.an=:an";
        $stmt_ipt = $conn->prepare($sql_ipt);
        $stmt_ipt->execute(['an'=>$an]);
        $row_ipt = $stmt_ipt->fetch();
        $regdatetime = $row_ipt['regdate'].' '.$row_ipt['regtime'];//ใช้ในการดึงข้อมูล ประวัติการผ่าตัด


  
        $receive_date        =  $row['receive_date'];
        $receive_time        =  $row['receive_time'];

$head =
'
    <style>
    div.f15 {
 
        font-size: 12px; 
        
      }
      div.line_dotted {
        text-decoration: underline dotted;  
        text-decoration-color: rgb(105,42,49); 
        font-size: 12px;
        text-decoration-style: dotted;  
      }

        body{
            font-family: "Garuda";//เรียกใช้font Garuda สำหรับแสดงผล ภาษาไทย
        }
        footer {
            position: fixed;
            bottom: -60px;
            left: 0px;
            right: 0px;
            height: 70px;

            /** Extra personal styles **/
            line-height: 35px;
        }
        br {
            display: block;
            content: " ";
            margin: 10px 0;
            height:10pt;
            line-height: 150%;
        }
        #show_img_select  {
            background-image: url("../include/images/allbody.jpg");
            background-position: center;
            background-repeat: no-repeat;
            background-image-resize:5;
            height:180px;
        }
    </style>
    <h2 style="text-align:right;font-size:8pt;">FM-OBS-004</h2>
    
    <h2 style="text-align:center;font-size:11pt;">ใบบันทึกประวัติและประเมินสมรรถนะผู้ป่วยแรกรับ&nbsp;(เด็กแรกเกิด)&nbsp;'.htmlspecialchars(DbConstant::HOSPITAL_NAME).'</h2>

    <div class="form-group row">
                                <label class="col-sm-12">ข้อมูลทั่วไป</label>
                            </div>
<div class="f15"> รับใหม่วันที่ '.htmlspecialchars($receive_date).'<b> เวลา </b>'.htmlspecialchars($receive_time).'&nbsp;น.&nbsp;จาก&nbsp;'.
$receive_from_1.'&nbsp;คลอดในโรงพยาบาล&nbsp;'.$receive_from_2.'&nbsp;'.$receive_from.'<br>'
.'รับไว้ในโรงพยาบาลโดย '.
$transport_1.'&nbsp;อุ้มมา&nbsp;'.$transport_2.'&nbsp;transport incubator&nbsp;'.$transport_3.'&nbsp;clib&nbsp;<br>'
.'<B>อาการสำคัญที่นำมาโรงพยาบาล</B>&nbsp;'.nl2br(htmlspecialchars($row['cc']))
.'<br><B>ประวัติการเจ็บป่วยปัจจุบัน</B>&nbsp;'.nl2br(htmlspecialchars($row['hpi']))
.'<br><B>ประวัติการเจ็บป่วยของสมาชิกในครอบครัว</B>&nbsp;'.nl2br(htmlspecialchars($row['family']))
.'<br><B>สัญญาณชีพ</B>&emsp;BT&emsp;'.htmlspecialchars($row['bt'])
.'&emsp;°C&emsp;HR&emsp;'.htmlspecialchars($row['hr'])
.'&emsp;bpm&emsp;RR&emsp;'.htmlspecialchars($row['rr']).'&emsp;/min'
.'<br><B>สภาพร่างกายผู้ป่วยแรกรับ</B>&emsp;OF&emsp;'.htmlspecialchars($row['ofs'])
.'&emsp;cms&emsp;OM&emsp;'.htmlspecialchars($row['om'])
.'&emsp;cms&emsp;รอบอก&emsp;'.htmlspecialchars($row['chest']).'&emsp;cms&emsp;ตัวยาว&emsp;'.htmlspecialchars($row['body_long']).'&emsp;cms&emsp;<br>Cord&emsp;'
.htmlspecialchars($row['cord']).'&emsp;Anus&emsp;'.htmlspecialchars($row['anus'])
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;รูปร่างทั่วไป&nbsp;'.
$body_1.'&nbsp;ปกติ&nbsp;'.$body_2.'&nbsp;ผิดปกติ&nbsp;ระบุ&nbsp;'.$body
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;การร้อง&nbsp;'.
$cry_1.'&nbsp;ไม่ร้อง&nbsp;'.$cry_2.'&nbsp;ร้องเสียงดัง&nbsp;'.$cry_3.'&nbsp;ร้องผิดปกติ&nbsp;'.$cry
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;การเคลื่อนไหว&nbsp;'.
$movement_1.'&nbsp;ขยับได้&nbsp;'.$movement_2.'&nbsp;อ่อนปวกเปียก&nbsp;'.$movement_3.'&nbsp;ชักเกร็ง&nbsp;'.$movement_4.'&nbsp;ผิดปกติ&nbsp;ระบุ&nbsp;&nbsp;'.$movement
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ศรีษะ&nbsp;'.
$head_1.'&nbsp;ปกติ&nbsp;'.$head_2.'&nbsp;ผิดปกติ&nbsp;ระบุ&nbsp;'.$head
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ตา&nbsp;'.
$eyes_1.'&nbsp;ปกติ&nbsp;'.$eyes_2.'&nbsp;ผิดปกติ&nbsp;ระบุ&nbsp;'.$eyes
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;จมูก&nbsp;'.
$nose_1.'&nbsp;มีรูจมูก2ข้าง&nbsp;'.$nose_2.'&nbsp;รูจมูกตัน&nbsp;'.$nose_3.'&nbsp;ผิดปกติ&nbsp;ระบุ&nbsp;'.$nose
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ปาก ลิ้น&nbsp;'.
$mouth_1.'&nbsp;ปกติ&nbsp;'.$mouth_2.'&nbsp;ปากแหว่ง&nbsp;'.$mouth_3.'&nbsp;เพดานโหว่&nbsp;'.$mouth_4.'&nbsp;ผิดปกติ&nbsp;ระบุ&nbsp;'.$mouth
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;คอ&nbsp;'.
$neck_1.'&nbsp;ปกติ&nbsp;'.$neck_2.'&nbsp;ผิดปกติ&nbsp;ระบุ&nbsp;'.$neck
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ท้อง&nbsp;'.
$abdomen_1.'&nbsp;ปกติ&nbsp;'.$abdomen_2.'&nbsp;ท้องอืด&nbsp;'.$abdomen_3.'&nbsp;ผิดปกติ&nbsp;ระบุ&nbsp;'.$abdomen
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;สะดือ&nbsp;'.
$navel_1.'&nbsp;ปกติ&nbsp;'.$navel_2.'&nbsp;Omphalocele&nbsp;'.$navel_3.'&nbsp;Gastroschisis&nbsp;'.$navel_4.'&nbsp;ผิดปกติ&nbsp;ระบุ&nbsp;'.$navel
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;กระดูกสันหลัง&nbsp;'.
$spine_1.'&nbsp;ปกติ&nbsp;'.$spine_2.'&nbsp;ผิดปกติ&nbsp;ระบุ&nbsp;'.$spine
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;แขนขา&nbsp;'.
$limbs_1.'&nbsp;ปกติ&nbsp;'.$limbs_2.'&nbsp;ผิดปกติ&nbsp;ระบุ&nbsp;'.$limbs
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;อวัยวะเพศ&nbsp;'.
$genitalia_1.'&nbsp;ปกติ&nbsp;'.$genitalia_2.'&nbsp;ผิดปกติ&nbsp;ระบุ&nbsp;'.$genitalia
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ทวารหนัก&nbsp;'.
$anuss_1.'&nbsp;ปกติ&nbsp;'.$anuss_2.'&nbsp;ไม่มีรูก้น'
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;สีผิว&nbsp;'.
$skin_color_1.'&nbsp;แดง&nbsp;'.$skin_color_2.'&nbsp;ซีด&nbsp;'.$skin_color_3.'&nbsp;เขียว&nbsp;'.$skin_color_4.'&nbsp;อื่นๆ&nbsp;ระบุ&nbsp;&nbsp;'.$skin_color
.'<br><B>สภาพจิตใจทารกเมื่อแรกรับ</B>'
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;การแสดงออกด้านพฤติกรรม&nbsp;'.
$behavior_1.'&nbsp;เฉย&nbsp;'.$behavior_2.'&nbsp;ร้องไห้&nbsp;'.$behavior_3.'&nbsp;อื่นๆ&nbsp;ระบุ&nbsp;'.$behavior
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;การแสดงออกด้านอารมณ์&nbsp;'.
$expression_1.'&nbsp;ประเมินไม่ได้&nbsp;'.$expression_2.'&nbsp;ร้องโกรธ&nbsp;'.$expression_2.'&nbsp;อื่นๆ&nbsp;'.$expression
.'<br><B>อาการแรกรับ</B>&nbsp;'.nl2br(htmlspecialchars($row['first_symptom']))
.'</div>

    <table id="bg-table" width="100%" style="border-collapse: collapse;font-size:8pt;margin-top:2px;">
   
        <tr style="border:1px solid #000;margin: 35px;"> /* ชื่อ-สกุล */
            <td  colspan="3" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">
            <label>HN : '.htmlspecialchars($row_ipt['hn']).' | AN : '.htmlspecialchars($an).'</label>
            <label>ชื่อ - สกุล : '.htmlspecialchars($row_ipt['pname'].$row_ipt['fname']." ".$row_ipt['lname']).' | </label>
            <label>อายุ : '.htmlspecialchars($row_ipt['age_y']." ปี ".$row_ipt['age_m']." เดือน ".$row_ipt['age_d']." วัน ").' | </label>
            <label>ตึก : '.htmlspecialchars($row_ipt['name']).' | </label>
            <label>เตียง : '.htmlspecialchars($row_ipt['bedno']).' | </label>
            <label>สิทธิ : ('.htmlspecialchars($row_ipt['pttype']).') '.htmlspecialchars($row_ipt['pttype_name']).$loginname.'</label>
            </td>
        </tr>
    </table>
    <footter> <h2 style="text-align:right;font-size:8pt;">FM-OBS-004 แก้ไขครั้งที่ 01 ประกาศใช้ 15 กรกฏาคม 2562</h2> </footer>
';
$mpdf->WriteHTML($head);
$mpdf->Output();

<?php
require_once '../mains/datethai.php';
require_once '../include/Session.php';
 
                 
   $login = empty($_REQUEST['loginname']) ? null : $_REQUEST['loginname'];
   $loginname = $_SESSION['loginname'];
   $values =['loginname'=>$loginname];
   
   //หากพบว่าไม่ตรงกันให้ ทำลาย session เดิมทิ้งไป
   if(!$loginname){
    session_start();
    session_destroy();              
        
  }

  Session::checkLoginSessionAndShowMessage(); //เช็ค session

  if(!(
     Session::checkPermission('DOCUMENT', 'PRINT')
     )){
     return;
 }

 /*Session::checkLoginSessionAndShowMessage(); //เช็ค session
Session::checkPermissionAndShowMessage('DOCUMENT', 'PRINT');
*/
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
//require_once __DIR__ . '/vendor/autoload.php';
require_once '../include/DbUtils.php';
require_once '../include/Session.php';
require_once '../include/KphisQueryUtils.php';
require_once '../include/ReportQueryUtils.php';

date_default_timezone_set('asia/bangkok');

//echo $_SERVER['DOCUMENT_ROOT'] ;

$conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
$mpdf->AddPageByArray([
    'margin-left' => 6,
    'margin-right' => 6,
    'margin-top' => 6,
    'margin-bottom' => 6,
]);

$id = $_REQUEST['id'];
$an = empty($_REQUEST['an']) ? null : $_REQUEST['an'];
$hn = KphisQueryUtils::getHnByAn($an);
$query_parameters = ['an' => $an];

Session::insertSystemAccessLog(json_encode(array(
    'report'=>'PRE-NURSENOTE-PDF',
   // 'action'=>'PRINT',
    'an'=>$an,
),JSON_UNESCAPED_UNICODE));

/*
$login = empty($_REQUEST['loginname']) ? null : $_REQUEST['loginname'];
$loginname = $_SESSION['loginname'];
$values = ['loginname' => $loginname];
if ($login != $loginname) {
    session_start();
    session_destroy();
}
*/
$image_uncheck = "<img src='../include/images/check-adm.jpg' width='1.6%' class='check_img'>";
$image_check = "<img src='../include/images/check-1.jpg' width='1.6%' class='check_img'>";
//-------------------------Doctor admission note
$sql = "SELECT pn.*
        FROM prs_icu_form pn
        WHERE pn.an = :an
        limit 1";
$stmt = $conn->prepare($sql);
$stmt->execute($query_parameters);
$row  = $stmt->fetch();

$sql_item = "SELECT dr_adm_item.id,
                    dr_adm_item.doctor,
                    doctor.`name` AS admission_note_doctorname
                    FROM " . DbConstant::KPHIS_DBNAME . ".prs_pre_nursenote_item dr_adm_item
                    LEFT OUTER JOIN " . DbConstant::HOSXP_DBNAME . ".doctor ON doctor.code = dr_adm_item.doctor
                    WHERE an=:an
                    ORDER BY dr_adm_item.id ASC";
$stmt_item = $conn->prepare($sql_item);
$stmt_item->execute($query_parameters);
$pre_note_count = 0;
while ($row_item = $stmt_item->fetch()) {
    $id_pre_note[] = $row_item['id'];
    $doctor[] = $row_item['doctor'];
    $admission_note_doctorname[] = $row_item['admission_note_doctorname'];
    //$admission_note_doctorentryposition[] = $row_item['admission_note_doctorentryposition'];
    $pre_note_count++;
}

                       
                        $from_dep = $row['from_dep'];
                        $rxDate = $row['rxdate'];//วันที่ Discharge
                        $rxdate = date($rxDate);
                        $rxTime = $row['rxtime'];//เวลาที่ Discharge
                        $rxtime = date($rxTime);
                        $strDate =($rxdate."  ".$rxtime);
                       // $dchtime  = date('H:i', strtotime($origTime));






 //โรคประจำคัว
 $heart_disease_history_1 = '( )';
 if ($row['heart_disease_history'] == 'ไม่มี') {$heart_disease_history_1 = '('.$image_check.')';
 }
 $heart_disease_history_2 = '( )';
 if ($row['heart_disease_history'] != 'ไม่มี' && $row['heart_disease_history'] != null) {$heart_disease_history_2 = '('.$image_check.')';
    $heart_disease_history  =  htmlspecialchars($row['heart_disease_history']);
 }

 $neck_vien_engorement_1 = '( )';
 if ($row['neck_vien_engorement'] == 'ไม่พบ') {$neck_vien_engorement_1 = '('.$image_check.')';
 }
 $neck_vien_engorement_2 = '( )';
 if ($row['neck_vien_engorement'] == 'ประเมินไม่ได้') {$neck_vien_engorement_2 = '('.$image_check.')';
 }
 $neck_vien_engorement_3 = '( )';
 if ($row['neck_vien_engorement'] != 'ไม่พบ' && $row['neck_vien_engorement'] != 'ประเมินไม่ได้' && $row['neck_vien_engorement'] != null) {$neck_vien_engorement_3 = '('.$image_check.')';
    $neck_vien_engorement  =  htmlspecialchars($row['neck_vien_engorement']);
 }

  //โรคประจำคัว
  $skin_1 = '( )';
  if ($row['skin'] == 'ปกติ') {$skin_1 = '('.$image_check.')';
  }
  $skin_2 = '( )';
  if ($row['skin'] == 'ซีด') {$skin_2 = '('.$image_check.')';
  }
  $skin_3 = '( )';
  if ($row['skin'] == 'เขียว') {$skin_3 = '('.$image_check.')';
  }
  $skin_4 = '( )';
  if ($row['skin'] == 'จุดจ้ำเลือด') {$skin_4 = '('.$image_check.')';
  }
  $skin_5 = '( )';
  if ($row['skin'] == 'แห้ง') {$skin_5 = '('.$image_check.')';
  }
  $skin_6 = '( )';
  if ($row['skin'] != 'จุดจ้ำเลือด' && $row['skin'] != 'ซีด' 
  && $row['skin'] != 'เขียว' && $row['skin'] != 'จุดจ้ำเลือด' && $row['skin'] != 'แห้ง' 
  && $row['skin'] != null) {$skin_6 = '('.$image_check.')';
     $skin =  htmlspecialchars($row['skin']);
  }

  $listen_to_the_heart_1 = '( )';
  if ($row['listen_to_the_heart'] == '1') {$listen_to_the_heart_1 = '('.$image_check.')';
  }

  $listen_to_the_heart_2 = '( )';
  if ($row['listen_to_the_heart'] == '2') {$listen_to_the_heart_2 = '('.$image_check.')';
  }

  $listen_to_the_heart_3 = '( )';
  if ($row['listen_to_the_heart'] == '3') {$listen_to_the_heart_3 = '('.$image_check.')';
  }


  $kidney_disease_history_1 = '( )';
  if ($row['kidney_disease_history'] == 'ไม่มี') {$kidney_disease_history_1 = '('.$image_check.')';
  }
  $kidney_disease_history_2 = '( )';
  if ($row['kidney_disease_history'] != 'ไม่มี' && $row['kidney_disease_history'] != null) {$kidney_disease_history_2 = '('.$image_check.')';
     $kidney_disease_history  =  htmlspecialchars($row['kidney_disease_history']);
  }

  $history_of_lung_disease_1 = '( )';
  if ($row['history_of_lung_disease'] == 'ไม่มี') {$history_of_lung_disease_1 = '('.$image_check.')';
  }
  $history_of_lung_disease_2 = '( )';
  if ($row['history_of_lung_disease'] != 'ไม่มี' && $row['history_of_lung_disease'] != null) {$history_of_lung_disease_2 = '('.$image_check.')';
     $history_of_lung_disease =  htmlspecialchars($row['history_of_lung_disease']);
  }

  $et_other_1 = '( )';
  if ($row['et_other'] == 'ET-Tube') {$et_other_1 = '('.$image_check.')';
  }

  $et_other_2 = '( )';
  if ($row['et_other'] == 'TT-Tube') {$et_other_2 = '('.$image_check.')';
  }

  $et_other_3 = '( )';
  if ($row['et_other'] == 'O2HFNC') {$et_other_3 = '('.$image_check.')';
  }

  $et_other_4 = '( )';
  if ($row['et_other'] == 'candular') {$et_other_4 = '('.$image_check.')';
  }
  $et_other_5 = '( )';
  if ($row['et_other'] == 'Mark c bag') {$et_other_5 = '('.$image_check.')';
  }
  $et_other_6 = '( )';
  if ($row['et_other'] == 'RA') {$et_other_6 = '('.$image_check.')';
  }

  $breathing_characteristics_1 = '( )';
  if ($row['breathing_characteristics'] == '1') {$breathing_characteristics_1 = '('.$image_check.')';
  }

  $breathing_characteristics_2 = '( )';
  if ($row['breathing_characteristics'] == '2') {$breathing_characteristics_2 = '('.$image_check.')';
  }

  $breathing_characteristics_3 = '( )';
  if ($row['breathing_characteristics'] == '3') {$breathing_characteristics_3 = '('.$image_check.')';
  }


  $on_icd_1 = '( )';
  if ($row['on_icd'] == 'ไม่มี') {$on_icd_1 = '('.$image_check.')';
  }
  $on_icd_2 = '( )';
  if ($row['on_icd'] != 'ไม่มี' && $row['on_icd'] != null) {$on_icd_2 = '('.$image_check.')';
     $on_icd  =  htmlspecialchars($row['on_icd']);
  }

  $listen_sound_lungs_1 = '( )';
  if ($row['listen_sound_lungs'] == '1') {$listen_sound_lungs_1 = '('.$image_check.')';
  }

  $listen_sound_lungs_2 = '( )';
  if ($row['listen_sound_lungs'] == '2') {$listen_sound_lungs_2 = '('.$image_check.')';
  }

  $listen_sound_lungs_3 = '( )';
  if ($row['listen_sound_lungs'] == '3') {$listen_sound_lungs_3 = '('.$image_check.')';
  }

  $listen_sound_lungs_4 = '( )';
  if ($row['listen_sound_lungs'] == '4') {$listen_sound_lungs_4 = '('.$image_check.')';
  }

  $listen_sound_lungs_5 = '( )';
  if ($row['listen_sound_lungs'] == '5') {$listen_sound_lungs_5 = '('.$image_check.')';
  }


  $history_of_gastrointestinal_1 = '( )';
  if ($row['history_of_gastrointestinal'] == 'ไม่มี') {$history_of_gastrointestinal_1 = '('.$image_check.')';
  }
  $history_of_gastrointestinal_2 = '( )';
  if ($row['history_of_gastrointestinal'] != 'ไม่มี' && $row['history_of_gastrointestinal'] != null) {$history_of_gastrointestinal_2 = '('.$image_check.')';
     $history_of_gastrointestinal  =  htmlspecialchars($row['history_of_gastrointestinal']);
  }

  $communication_history_1 = '( )';
  if ($row['communication_history'] == 'ไม่มี') {$communication_history_1 = '('.$image_check.')';
  }
  $communication_history_2 = '( )';
  if ($row['communication_history'] != 'ไม่มี' && $row['communication_history'] != null) {$communication_history_2 = '('.$image_check.')';
     $communication_history  =  htmlspecialchars($row['communication_history']);
  }


  $speaking_check = '( )';
  if ($row['speaking'] != null) {$speaking_check = '('.$image_check.')';
  }

  $speaking_2 = '( )';
  if ($row['speaking'] == 'พูดได้เองชัดเจน') {$speaking_2 = '('.$image_check.')';
  }

  $speaking_3 = '( )';
  if ($row['speaking'] == 'พูดไม่ชัด') {$speaking_3 = '('.$image_check.')';
  }

  $speaking_4= '( )';
  if ($row['speaking'] != 'พูดได้เองชัดเจน' && $row['speaking'] != 'พูดไม่ชัด' && $row['speaking'] != null) {$speaking_4 = '('.$image_check.')';
     $speaking  =  htmlspecialchars($row['speaking']);
  }


  $communication_check = '( )';
  if ($row['communication'] != null) {$communication_check = '('.$image_check.')';
  }

  $communication_2 = '( )';
  if ($row['communication'] == 'สื่อสารด้วยการเขียน') {$communication_2 = '('.$image_check.')';
  }

  $communication_3 = '( )';
  if ($row['communication'] == 'สื่อสารโดยการใช้สายตา') {$communication_3 = '('.$image_check.')';
  }

  $communication_4 = '( )';
  if ($row['communication'] == 'สื่อสารโดยใช้ท่าทาง') {$communication_4 = '('.$image_check.')';
  }

  $communication_5 = '( )';
  if ($row['communication'] == 'ประเมินไม่ได้') {$communication_5 = '('.$image_check.')';
  }

  $communication_6= '( )';
  if ($row['communication'] != 'สื่อสารด้วยการเขียน' 
  && $row['communication'] != 'สื่อสารโดยการใช้สายตา' 
  && $row['communication'] != 'สื่อสารโดยใช้ท่าทาง' 
  && $row['communication'] != 'ประเมินไม่ได้' 
  && $row['communication'] != null) {$communication_4 = '('.$image_check.')';
     $communication  =  htmlspecialchars($row['communication']);
  }
  


 $vision_1 = '( )';
 if ($row['vision'] == 'เห็นชัดเจน') {$vision_1 = '('.$image_check.')';
 }
 $vision_2 = '( )';
 if ($row['vision'] == 'เห็นไม่ชัดเจน') {$vision_2 = '('.$image_check.')';
 }

 $vision_3 = '( )';
 if ($row['vision'] == 'ประเมินไม่ได้') {$vision_3 = '('.$image_check.')';
 }


 $vision_4 = '( )';
 if ($row['vision'] != 'เห็นชัดเจน' && $row['vision'] != 'เห็นไม่ชัดเจน' && $row['vision'] != 'ประเมินไม่ได้'  && $row['vision'] != null) {$vision_4 = '('.$image_check.')';
    $vision  =  htmlspecialchars($row['vision']);
 }
 
 $hearing_aids_1 = '( )';
 if ($row['hearing_aids'] == '1') {$hearing_aids_1 = '('.$image_check.')';
 }

 $hearing_aids_2 = '( )';
 if ($row['hearing_aids'] == '2') {$hearing_aids_2 = '('.$image_check.')';
 }

 $listening_1 = '( )';
 if ($row['listening'] == '1') {$listening_1 = '('.$image_check.')';
 }
 $listening_2 = '( )';
 if ($row['listening'] == '2') {$listening_2 = '('.$image_check.')';
 }
 $listening_3 = '( )';
 if ($row['listening'] == '3') {$listening_3 = '('.$image_check.')';
 }

 $listening_4 = '( )';
 if ($row['listening'] == '4') {$listening_4 = '('.$image_check.')';
 }

 $history_affects_activities_1 = '( )';
 if ($row['history_affects_activities'] == 'ไม่มี') {$history_affects_activities_1 = '('.$image_check.')';
 }
 $history_affects_activities_2 = '( )';
 if ($row['history_affects_activities'] != 'ไม่มี' && $row['history_affects_activities'] != null) {$history_affects_activities_2 = '('.$image_check.')';
    $history_affects_activities =  htmlspecialchars($row['history_affects_activities']);
 }

 $daily_activities_1 = '( )';
 if ($row['daily_activities'] == '1') {$daily_activities_1 = '('.$image_check.')';
 }
 $daily_activities_2 = '( )';
 if ($row['daily_activities'] == '2') {$daily_activities_2 = '('.$image_check.')';
 }
 $daily_activities_3 = '( )';
 if ($row['daily_activities'] == '3') {$daily_activities_3 = '('.$image_check.')';
 }
 $daily_activities_4 = '( )';
 if ($row['daily_activities'] == '4') {$daily_activities_4 = '('.$image_check.')';
 }

 $history_affects_stimulation_1 = '( )';
 if ($row['history_affects_stimulation'] == 'ไม่มี') {$history_affects_stimulation_1 = '('.$image_check.')';
 }
 $history_affects_stimulation_2 = '( )';
 if ($row['history_affects_stimulation'] != 'ไม่มี' && $row['history_affects_stimulation'] != null) {$history_affects_stimulation_2 = '('.$image_check.')';
    $history_affects_stimulation =  htmlspecialchars($row['history_affects_stimulation']);
 }

 $level_of_consciousness_1 = '( )';
 if ($row['level_of_consciousness'] == '1') {$level_of_consciousness_1 = '('.$image_check.')';
 }

 $level_of_consciousness_2 = '( )';
 if ($row['level_of_consciousness'] == '2') {$level_of_consciousness_2 = '('.$image_check.')';
 }

 $level_of_consciousness_3 = '( )';
 if ($row['level_of_consciousness'] == '3') {$level_of_consciousness_3 = '('.$image_check.')';
 }

 $level_of_consciousness_4 = '( )';
 if ($row['level_of_consciousness'] == '4') {$level_of_consciousness_4 = '('.$image_check.')';
 }

 $level_of_consciousness_5 = '( )';
 if ($row['level_of_consciousness'] == '5') {$level_of_consciousness_5 = '('.$image_check.')';
 } 

 $pain_score_1 = '( )';
 if ($row['pain_score'] == '1') {$pain_score_1 = '('.$image_check.')';
 }

 $pain_score_2 = '( )';
 if ($row['pain_score'] == '2') {$pain_score_2 = '('.$image_check.')';
 }

 $fluid_balance = '( )';
 if ($row['fluid_balance'] == 'Y') {$fluid_balance = '('.$image_check.')';
 }

 $aeration = '( )';
 if ($row['aeration'] == 'Y') {$aeration = '('.$image_check.')';
 }

 $nutrition = '( )';
 if ($row['nutrition'] == 'Y') {$nutrition = '('.$image_check.')';
 }

 $communication_problem = '( )';
 if ($row['communication_problem'] == 'Y') {$communication_problem = '('.$image_check.')';
 }

 $activity = '( )';
 if ($row['activity'] == 'Y') {$activity = '('.$image_check.')';
 }

 $stimulation = '( )';
 if ($row['stimulation'] == 'Y') {$stimulation = '('.$image_check.')';
 }




//-------------------------Doctor admission note
$sql_ipt = "select patient.sex,patient.hn,patient.pname,patient.fname,patient.lname,patient.drugallergy,
            (select GROUP_CONCAT(concat(opd_allergy.agent,'=',if(opd_allergy.symptom is null,',',opd_allergy.symptom)/*,' [',if(note is null,',',note),']'*/)) as name
                from ".DbConstant::HOSXP_DBNAME.".opd_allergy
                where opd_allergy.hn = ipt.hn /*and (opd_allergy.no_alert<>'Y' or opd_allergy.no_alert is null)*/
                order by display_order) as allergy_symptom_string,
            an_stat.age_y,an_stat.age_m,an_stat.age_d,
            ipt.regdate,ipt.regtime,
            ipt.ward,ward.name,
            ipt.pttype, pttype.`name` as pttype_name,
            iptadm.bedno, (select vs.bw from ipd_vs_vital_sign vs where vs.an = ipt.an and vs.bw is not null and trim(vs.bw) <> '' order by vs_datetime desc limit 1) as latest_bw
            , (select vs.height from ipd_vs_vital_sign vs where vs.an = ipt.an and vs.bw is not null and trim(vs.bw) <> '' order by vs_datetime desc limit 1) as latest_height
            from ".DbConstant::HOSXP_DBNAME.".ipt
            left outer join ".DbConstant::HOSXP_DBNAME.".an_stat on an_stat.an=ipt.an
            left outer join ".DbConstant::HOSXP_DBNAME.".patient on patient.hn=ipt.hn
            left outer join ".DbConstant::HOSXP_DBNAME.".ward on ward.ward=ipt.ward
            LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".pttype ON pttype.pttype = ipt.pttype
            LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".iptadm ON iptadm.an = ipt.an
            WHERE ipt.an=:an";
        $stmt_ipt = $conn->prepare($sql_ipt);
        $stmt_ipt->execute(['an'=>$an]);
        $row_ipt = $stmt_ipt->fetch();
        $regdatetime = $row_ipt['regdate'].' '.$row_ipt['regtime'];//ใช้ในการดึงข้อมูล ประวัติการผ่าตัด


  
        $receive_date        =  $row['receive_date'];
        $receive_time        =  $row['receive_time'];

        $id = '17'; //Link menu
        $check_    = ReportQueryUtils::getProduction($id);

        $check_report = '( )';
        if ($check_  == '1') 
        {$check_report = '&nbsp;<font color="red">รอปรับรายงาน</font>';
        } else {
            $check_report = '';
        }
       

      
        $icu_form1 = "<img src='../include/images/icu-form1.png' width='100%' >";
        $icu_form2 = "<img src='../include/images/icu-form2.png' width='100%' >";
        $icu_form3 = "<img src='../include/images/icu-form3.png' width='100%' >";
        
       // $maxNrOfPages = ceil($max/$itemsPerPage);

$head =
'

    <style>
    div.f15 {
 
        font-size: 12px; 
        
      }
      div.line_dotted {
        text-decoration: underline dotted;  
        text-decoration-color: rgb(105,42,49); 
        font-size: 12px;
        text-decoration-style: dotted;  
      }

        body{
            font-family: "Garuda";//เรียกใช้font Garuda สำหรับแสดงผล ภาษาไทย
        }
        footer {
            position: fixed;
            bottom: -60px;
            left: 0px;
            right: 0px;
            height: 70px;

            /** Extra personal styles **/
            line-height: 35px;
        }
        br {
            display: block;
            content: " ";
            margin: 10px 0;
            height:10pt;
            line-height: 150%;
        }
        #show_img_select  {
            background-image: url("../include/images/allbody.jpg");
            background-position: center;
            background-repeat: no-repeat;
            background-image-resize:5;
            height:180px;
        }

        
    </style>
    <h2 style="text-align:right;font-size:8pt;">FM-ICU-005-00</h2>
    
    <h2 style="text-align:center;font-size:11pt;">แบบประเมินผู้ป่วยวิกฤตแรกรับตามแนวคิด FANCAS &nbsp;'.htmlspecialchars(DbConstant::HOSPITAL_NAME).$check_report.'</h2>
    
   
<div class="f15"> วันที่ <b>'.LongDateThai2($strDate).'</b>&nbsp;รับใหม่/รับย้ายเวลา <b>'.htmlspecialchars($rxtime).'</b>&nbsp;น.&nbsp;จากหน่วยงาน&nbsp;'.
$from_dep.'<br>'
.'<table id="bg-table" width="100%" style="border-collapse: collapse;font-size:8pt;margin-top:2px;">'.
'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
' <div class="form-group row alert alert-dark text-left">
<B>1. ด้านสมดุลของน้ำ(Fluid balance)</B>
</div>'.
'</td>'.
'</tr>'.
'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
' <div class="form-group row alert alert-dark text-left">
<B>1.1 Cardiovascalar system</B>
</div>'.
'</td>'.
'</tr>'.
'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>ประวัติโรคหัวใจ/หลอดเลือด</label>&nbsp;'.
$heart_disease_history_1.'&nbsp;ไม่มี&nbsp;'.$heart_disease_history_2.'&nbsp;มี ระบุ&nbsp;'
.'<u>'.$heart_disease_history.'</u>'.
'</div>
<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>ผิวหนัง</label>&nbsp;'.
$skin_1.'&nbsp;ปกติ&nbsp;'
.$skin_2.'&nbsp;ซีด&nbsp;'
.$skin_3.'&nbsp;เขียว&nbsp;'
.$skin_4.'&nbsp;จุดจ้ำเลือด&nbsp;'
.$skin_5.'&nbsp;แห้ง&nbsp;'
.$skin_6.'&nbsp;บวมกดบุ๋ม&nbsp;'
.'<u>'.$skin.'</u>'.
'</div>
<br>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>Neck vien engorement</label>&nbsp;'.
$neck_vien_engorement_1.'&nbsp;ไม่พบ&nbsp;'
.$neck_vien_engorement_3.'&nbsp;พบระบุ&nbsp;'
.'<u>'.$neck_vien_engorement.'</u>&nbsp;'
.$neck_vien_engorement_2.'&nbsp;ประเมินไม่ได้&nbsp;'
.'</div>
<br>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>ฟังเสียงหัวใจ</label>&nbsp;'.
$listen_to_the_heart_1.'&nbsp;Murmur&nbsp;'
.$listen_to_the_heart_2.'&nbsp;Rub&nbsp;'
.$listen_to_the_heart_3.'&nbsp;ไม่พบ&nbsp;'
.'</div>
<br>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>V/S&nbsp;BT</label>&nbsp;<u>'.
nl2br(htmlspecialchars($row['bt'])).'</u>&nbsp;<sup>๐</sup>C&nbsp;'
.'<label>HR&nbsp;</label><u>'.nl2br(htmlspecialchars($row['pr'])).'</u>&nbsp;/min&nbsp;'
.'<label>BP&nbsp;</label><u>'.nl2br(htmlspecialchars($row['bps'])).' / '.nl2br(htmlspecialchars($row['bpd'])).'</u>&nbsp;mmHg&nbsp;'
.'</div>
<br>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>ผลการตรวจ&nbsp;CBC : WBC</label>&nbsp;<u>'.
nl2br(htmlspecialchars($row['cbc'])).'</u>'
.'<label>&nbsp;Hct&nbsp;</label><u>'.nl2br(htmlspecialchars($row['hct'])).'</u>%'
.'<label>&nbsp;Hb&nbsp;</label><u>'.nl2br(htmlspecialchars($row['hb'])).'</u>'
.'<label>&nbsp;Plt&nbsp;</label><u>'.nl2br(htmlspecialchars($row['plt'])).'</u>'
.'<label>&nbsp;PT&nbsp;</label><u>'.nl2br(htmlspecialchars($row['pt'])).'</u>'
.'<label>&nbsp;PTT&nbsp;</label><u>'.nl2br(htmlspecialchars($row['ptt'])).'</u>'
.'<label>&nbsp;INR&nbsp;</label><u>'.nl2br(htmlspecialchars($row['inr'])).'</u>'
.'</div>
<br>'.
'<div class="row">'
.'&nbsp;&nbsp;&nbsp;&nbsp;<label>Trop -T&nbsp;</label><u>'.nl2br(htmlspecialchars($row['trop_t'])).'</u>'
.'<label>&nbsp;CKMB&nbsp;</label><u>'.nl2br(htmlspecialchars($row['ckmb'])).'</u>'
.'<label>&nbsp;CPK&nbsp;</label><u>'.nl2br(htmlspecialchars($row['cpk'])).'</u>'
.'&nbsp;&nbsp;&nbsp;&nbsp;<label>Echo&nbsp;</label><u>'.nl2br(htmlspecialchars($row['echo'])).'</u>'
.'<label>&nbsp;EKG&nbsp;</label><u>'.nl2br(htmlspecialchars($row['ekg'])).'</u>'
.'</div>
<br>'.


'</td>'.
'</tr>'.

'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
' <div class="form-group row alert alert-dark text-left">
<B>1.2 Kidney system</B>
</div>'.
'</td>'.
'</tr>'.

'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>ประวัติโรคไต</label>&nbsp;'.
$kidney_disease_history_1.'&nbsp;ไม่มี&nbsp;'
.$kidney_disease_history_2.'&nbsp;มี ระบุ&nbsp;'
.'<u>'.$kidney_disease_history.'</u>'.
'</div>
<br>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>ลักษณะปัสสาวะ</label>&nbsp;<u>'.
nl2br(htmlspecialchars($row['urine_characteristics'])).'</u>'
.'<label>&nbsp;I/O ใน 24 ชม.&nbsp;</label><u>'.nl2br(htmlspecialchars($row['io_1'])).' / '.nl2br(htmlspecialchars($row['io_2'])).'</u>&nbsp;ซีซี&nbsp;'
.'</div>
<br>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>ผลการตรวจ LAB BUN</label>&nbsp;<u>'.
nl2br(htmlspecialchars($row['bun'])).'</u>'
.'<label>&nbsp;Cr&nbsp;</label><u>'.nl2br(htmlspecialchars($row['cr'])).'</u>'
.'<label>&nbsp;GFR&nbsp;</label><u>'.nl2br(htmlspecialchars($row['gfr'])).'</u>'
.'<label>&nbsp;Elyte&nbsp;Na&nbsp;</label><u>'.nl2br(htmlspecialchars($row['e_lyte_na'])).'</u>'
.'<label>&nbsp;K&nbsp;</label><u>'.nl2br(htmlspecialchars($row['e_lyte_k'])).'</u>'
.'<label>&nbsp;Cl&nbsp;</label><u>'.nl2br(htmlspecialchars($row['e_lyte_cl'])).'</u>'
.'<label>&nbsp;Co<sub>2</sub>&nbsp;</label><u>'.nl2br(htmlspecialchars($row['e_lyte_co2'])).'</u>'
.'<label>&nbsp;Anien Gap&nbsp;</label><u>'.nl2br(htmlspecialchars($row['e_lyte_aniengap'])).'</u>'
.'<label>&nbsp;Ca&nbsp;</label><u>'.nl2br(htmlspecialchars($row['ca'])).'</u>'
.'<label>&nbsp;Po<sub>4</sub>&nbsp;</label><u>'.nl2br(htmlspecialchars($row['po_4'])).'</u>'
.'<label>&nbsp;Mg&nbsp;</label><u>'.nl2br(htmlspecialchars($row['mg'])).'</u>'
.'<label>&nbsp;DTX&nbsp;</label><u>'.nl2br(htmlspecialchars($row['dtx'])).'</u>mg%'
.'<label>&nbsp;Urine Sp.gr&nbsp;</label><u>'.nl2br(htmlspecialchars($row['urine_sr_gr'])).'</u>'
.'</div>
<br>'.

'</td>'.
'</tr>'.

'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
' <div class="form-group row alert alert-dark text-left">
<B>2.ด้านการหายใจ (Aeration)</B>
</div>'.
'</td>'.
'</tr>'.

'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>ประวัติโรคปอด</label>&nbsp;'.
$history_of_lung_disease_1.'&nbsp;ไม่มี&nbsp;'.$history_of_lung_disease_2.'&nbsp;มี ระบุ&nbsp;'
.'<u>'.$history_of_lung_disease.'</u>'.
'</div>
<br>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>การหายใจ RR</label>&nbsp;<u>'.
nl2br(htmlspecialchars($row['rr'])).'</u>&nbsp;/min'
.'<label>&nbsp;O2Sat&nbsp;</label><u>'.nl2br(htmlspecialchars($row['o2_sat'])).'</u>&nbsp;%&nbsp;'
.'<label>On</label>&nbsp;'.
$et_other_1.'&nbsp;ET-Tube&nbsp;'
.$et_other_2.'&nbsp;TT-Tube No&nbsp;'
.'<u>'.nl2br(htmlspecialchars($row['et_tube_no'])).'</u> ขีด'
.'<u>'.nl2br(htmlspecialchars($row['et_tube_no2'])).'</u> cms.&nbsp;'
.$et_other_3.'&nbsp;O<sub>2</sub>HFNC&nbsp;'
.'<u>'.nl2br(htmlspecialchars($row['o2_hfnc'])).'</u>'
.$et_other_4.'&nbsp;Candular&nbsp;'
.'<u>'.nl2br(htmlspecialchars($row['candular'])).'</u>'
.$et_other_5.'&nbsp;Mark c bag&nbsp;'
.'<u>'.nl2br(htmlspecialchars($row['mark_c_bag'])).'</u>'
.$et_other_6.'&nbsp;RA&nbsp;'
.'</div>
<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>ลักษณะการหายใจ</label>&nbsp;'.
$breathing_characteristics_1.'&nbsp;หายใจหอบ&nbsp;'
.$breathing_characteristics_2.'&nbsp;หายใจลำบาก&nbsp;'
.$breathing_characteristics_3.'&nbsp;หายใจปกติ&nbsp;'
.'</div>
<br>'.
'&nbsp;&nbsp;&nbsp;&nbsp;<label>On ICD</label>&nbsp;'.
$on_icd_1.'&nbsp;ไม่มี&nbsp;'.$on_icd_2.'&nbsp;มี ข้าง&nbsp;'
.'<u>'.$on_icd.'</u> ขีด'
.'&nbsp;<u>'.nl2br(htmlspecialchars($row['on_icd_2'])).'</u>'
.'</div>
<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>ฟังเสียงลมเข้าปอด</label>&nbsp;'.
$listen_sound_lungs_1.'&nbsp;Clear&nbsp;'
.$listen_sound_lungs_2.'&nbsp;Crepitation&nbsp;'
.$listen_sound_lungs_3.'&nbsp;Wheezing&nbsp;'
.$listen_sound_lungs_4.'&nbsp;Rhonchi&nbsp;'
.$listen_sound_lungs_5.'&nbsp;Stridor&nbsp;'
.'</div>
<br>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>CXR</label>&nbsp;<u>'.
nl2br(htmlspecialchars($row['cxr'])).'</u>'
.'<label>&nbsp;Sputum G/S&nbsp;</label><u>'.nl2br(htmlspecialchars($row['sputum'])).'</u>'
.'<label>&nbsp;ABG/VBG:PH&nbsp;</label><u>'.nl2br(htmlspecialchars($row['abg'])).'</u>'
.'<label>&nbsp;PaCO2&nbsp;</label><u>'.nl2br(htmlspecialchars($row['pa_co2'])).'</u>'
.'<label>&nbsp;HCO3&nbsp;</label><u>'.nl2br(htmlspecialchars($row['hco3'])).'</u>'
.'<label>&nbsp;PaO2&nbsp;</label><u>'.nl2br(htmlspecialchars($row['pao2'])).'</u>'
.'<label>&nbsp;BE&nbsp;</label><u>'.nl2br(htmlspecialchars($row['be'])).'</u>'
.'</div>
<br>'.

'</td>'.
'</tr>'.

'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
' <div class="form-group row alert alert-dark text-left">
<B>3.ด้านภาวะโภชนาการ (Nutrition)</B>
</div>'.
'</td>'.
'</tr>'.

'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>ประวัติโรคระบบทางเดินอาหาร</label>&nbsp;'.
$history_of_gastrointestinal_1.'&nbsp;ไม่มี&nbsp;'.$history_of_gastrointestinal_2.'&nbsp;มี ระบุ&nbsp;'
.'<u>'.$history_of_gastrointestinal.'</u>'.
'</div>
<br>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>ส่วนสูง</label>&nbsp;<u>'.
nl2br(htmlspecialchars($row['hight'])).'</u> cms'
.'<label>&nbsp;น้ำหนัก&nbsp;</label><u>'.nl2br(htmlspecialchars($row['bw'])).'</u> kg'
.'<label>&nbsp;BMI:&nbsp;</label><u>'.nl2br(htmlspecialchars($row['bmi'])).'</u> Kg/m<sup>2</sup>'
.'<label>&nbsp;Alb&nbsp;</label><u>'.nl2br(htmlspecialchars($row['alb'])).'</u> mmol'
.'<label>&nbsp;BEE:&nbsp;</label><u>'.nl2br(htmlspecialchars($row['bee'])).'</u>'
.'<label>&nbsp;TEE:&nbsp;</label><u>'.nl2br(htmlspecialchars($row['tee'])).'</u>'
.'<label>&nbsp;SPENT Nutrition Screening Tool&nbsp;</label><u>'.nl2br(htmlspecialchars($row['spent'])).'</u> / 4 คะแนน'
.'</div>
<br>'.

'</td>'.
'</tr>'.

'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
' <div class="form-group row alert alert-dark text-left">
<B>4.ด้านการติดต่อสื่อสาร (Communication)</B>
</div>'.
'</td>'.
'</tr>'.

'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>ประวัติโรคทางการสื่อสาร</label>&nbsp;'.
$communication_history_1.'&nbsp;ไม่มี&nbsp;'.$communication_history_2.'&nbsp;มี ระบุ&nbsp;'
.'<u>'.$communication_history.'</u>'.
'</div>
<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>การพูด:</label>&nbsp;'.
$speaking_check.'&nbsp;ไม่ได้ On ET-Tube&nbsp;'
.$speaking_2.'&nbsp;พูดได้เองชัดเจน&nbsp;'
.$speaking_3.'&nbsp;พูดไม่ชัด&nbsp;'
.$speaking_4.'&nbsp;อื่นๆ&nbsp;'
.'<u>'.$speaking.'</u>'.
'</div>
<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
$communication_check.'&nbsp;On ET-Tube or TT&nbsp;'
.$communication_2.'&nbsp;สื่อสารด้วยการเขียน&nbsp;'
.$communication_3.'&nbsp;สื่อสารโดยการใช้สายตา&nbsp;'
.$communication_4.'&nbsp;สื่อสารโดยใช้ท่าทาง&nbsp;'
.$communication_6.'&nbsp;ไม่สามารถสื่อสารได้ เนื่องจาก&nbsp;'
.'<u>'.$communication.'</u>&nbsp;'
.$communication_5.'&nbsp;ประเมินไม่ได้&nbsp;'.
'</div>
<br>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>การมองเห็น : ตา</label>&nbsp;'.
$vision_1.'&nbsp;เห็นได้ชัดเจน&nbsp;'
.$vision_2.'&nbsp;เห็นไม่ชัดเจน&nbsp;'
.$vision_4.'&nbsp;ตาบอด&nbsp;'
.'<u>'.$vision.'</u>&nbsp;'
.$vision_3.'&nbsp;ประเมินไม่ได้&nbsp;'.
'</div>
<br>'.

'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>การได้ยิน : หู</label>&nbsp;'.
$listening_1.'&nbsp;ได้ยินชัดเจน&nbsp;'
.$listening_2.'&nbsp;หูหนวก&nbsp;'
.$listening_3.'&nbsp;ได้ยินไม่ชัด&nbsp;:&nbsp;ใช้อุปกรณ์ช่วยฟัง'
.$hearing_aids_1.'&nbsp;มี&nbsp;'
.$hearing_aids_2.'&nbsp;ไม่มี&nbsp;'
.$listening_4.'&nbsp;ประเมินไม่ได้&nbsp;'
.'</div>
<br>'.

'</td>'.
'</tr>'.

'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
' <div class="form-group row alert alert-dark text-left">
<B>5.ด้านการทำกิจกรรม (Activity)</B>
</div>'.
'</td>'.
'</tr>'.
'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>มีประวัติโรคที่ส่งผลต่อการทำกิจกรรม</label>&nbsp;'.
$history_affects_activities_1.'&nbsp;ไม่มี&nbsp;'.$history_affects_activities_2.'&nbsp;มี ระบุ&nbsp;'
.'<u>'.$history_affects_activities.'</u>'.
'</div>
<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>การทำกิจวัตรประจำวัน</label>&nbsp;'.
$daily_activities_1.'&nbsp;ช่วยเหลือตัวเองได้ดี&nbsp;'
.$daily_activities_2.'&nbsp;Bed ridden&nbsp;'
.$daily_activities_3.'&nbsp;หอบ เหนื่อย&nbsp;'
.$daily_activities_4.'&nbsp;ถูกจำกัดกิจกรรมบนเตียง&nbsp;'
.'</div>
<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$daily_activities_1.'&nbsp;มี Fracture ตำแหน่ง&nbsp;<u>'.
nl2br(htmlspecialchars($row['fracture'])).'</u>'
.'<label>&nbsp;Braden score&nbsp;</label><u>'.nl2br(htmlspecialchars($row['braden_score'])).'</u> / 23 คะแนน'
.'<label>&nbsp;Mortor power&nbsp;</label><u>'.nl2br(htmlspecialchars($row['mortor_power'])).'</u>'
.'<label>&nbsp;MASS&nbsp;</label><u>'.nl2br(htmlspecialchars($row['mass'])).'</u> / 6 คะแนน'
.'</div>
<br>'.

'</td>'.
'</tr>'.

'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
' <div class="form-group row alert alert-dark text-left">
<B>6.ด้านการกระตุ้น (Stimulation)</B>
</div>'.
'</td>'.
'</tr>'.

'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>มีประวัติโรคที่ส่งผลต่อการกระตุ้น</label>&nbsp;'.
$history_affects_stimulation_1.'&nbsp;ไม่มี&nbsp;'.$history_affects_stimulation_2.'&nbsp;มี ระบุ&nbsp;'
.'<u>'.$history_affects_stimulation.'</u>'.
'</div>
<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>GCS: E</label>&nbsp;<u>'.
nl2br(htmlspecialchars($row['gcs_e'])).'</u>'
.'<label>&nbsp;V:&nbsp;</label><u>'.nl2br(htmlspecialchars($row['gcs_v'])).'</u>'
.'<label>&nbsp;M:&nbsp;</label><u>'.nl2br(htmlspecialchars($row['gcs_m'])).'</u>'
.'<label>&nbsp;Pupil:&nbsp;</label><u>'.nl2br(htmlspecialchars($row['pupil'])).'</u>'
.'<label>&nbsp;RE:&nbsp;</label><u>'.nl2br(htmlspecialchars($row['pupil_rt'])).'</u> mm'
.'<label>&nbsp;LE:&nbsp;</label><u>'.nl2br(htmlspecialchars($row['pupil_lt'])).'</u> mm'
.'</div>
<br>'.
'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>ระดับความรู้สึกตัว</label>&nbsp;'.
$level_of_consciousness_1.'&nbsp;Alert&nbsp;'
.$level_of_consciousness_2.'&nbsp;Confuse&nbsp;'
.$level_of_consciousness_3.'&nbsp;Drowsiness&nbsp;'
.$level_of_consciousness_4.'&nbsp;Stupors&nbsp;'
.$level_of_consciousness_5.'&nbsp;Coma&nbsp;'
.'</div>
'.
'<br><B>ผล CT-Brain</B>&nbsp;'.nl2br(htmlspecialchars($row['ct_brain']))
.'</br>'
.'<div class="row">
&nbsp;&nbsp;&nbsp;&nbsp;<label>Pain score</label>&nbsp;'
.$pain_score_1.'&nbsp;COPT&nbsp;'
.'<u>'.nl2br(htmlspecialchars($row['copt'])).'</u> / 8 คะแนน'
.$pain_score_2.'&nbsp;NRS&nbsp;'
.'<u>'.nl2br(htmlspecialchars($row['nrs'])).'</u> / 10 คะแนน'

.'</div>
<br>'.

'</td>'.
'</tr>'.

'<tr style="border:1px solid #000;margin: 35px;">'.
'<td  colspan="4" width="100%" style="border-right:0.5px solid #000;margin: 40px;padding:4px;vertical-align:text-top;">'.
' <div class="form-group row  text-left">
<h3><B>สรุปปัญหา</B></h3>
</div><br>'

.'<div class="row">

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
.$fluid_balance.'&nbsp;ด้านสมดุลของสารน้ำ(Fluid balance)'
.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$aeration.'&nbsp;ด้านการหายใจ(Aeration)'
.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$nutrition.'&nbsp;ด้านภาวะโภชนาการ(Nutrition)'
.'</div>'
.'<br>'
.'<div class="row">

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
.$communication_problem.'&nbsp;ด้านการติดต่อสื่อสาร(Communication)'
.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$activity.'&nbsp;ด้านการทำกิจจกรรม(Activity)'
.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$stimulation.'&nbsp;ด้านการกระตุ้น(Stimulation)'
.'</div>'



.'</td>'.
'</tr>'.

 '</table>'

.'</div>

    <table id="bg-table" width="100%" style="border-collapse: collapse;font-size:8pt;margin-top:2px;">
   
        <tr style="border:1px solid #000;margin: 35px;"> /* ชื่อ-สกุล */
            <td  colspan="3" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">
            <label>HN : '.htmlspecialchars($row_ipt['hn']).' | AN : '.htmlspecialchars($an).'</label>
            <label>ชื่อ - สกุล : '.htmlspecialchars($row_ipt['pname'].$row_ipt['fname']." ".$row_ipt['lname']).' | </label>
            <label>อายุ : '.htmlspecialchars($row_ipt['age_y']." ปี ".$row_ipt['age_m']." เดือน ".$row_ipt['age_d']." วัน ").' | </label>
            <label>ตึก : '.htmlspecialchars($row_ipt['name']).' | </label>
            <label>เตียง : '.htmlspecialchars($row_ipt['bedno']).' | </label>
            <label>สิทธิ : ('.htmlspecialchars($row_ipt['pttype']).') '.htmlspecialchars($row_ipt['pttype_name']).'</label>
            </td>
           
        </tr>
    </table>'
   

      .'<br>'                     
      .$icu_form1.'</br>'.$icu_form2.'</br>'.$icu_form3.'<footter> <h2 style="text-align:right;font-size:8pt;">ประกาศใช้ 29 มกราคม 2566 งานเอกสารคุณภาพ ศูนย์คุณภาพ</h2> </footer>' ;
//$mpdf->SetColumns(2);

$mpdf->setFooter('HN: '.htmlspecialchars($hn).' AN: '.htmlspecialchars($an).' Page '.'{PAGENO}');
$mpdf->WriteHTML($head);
$mpdf->Output();

