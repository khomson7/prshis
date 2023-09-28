<?php

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


$login = empty($_REQUEST['loginname']) ? null : $_REQUEST['loginname'];
$loginname = $_SESSION['loginname'];
$values = ['loginname' => $loginname];
if ($login != $loginname) {
    session_start();
    session_destroy();
}

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
$receive_from_1.'&nbsp;ปกติ&nbsp;'.$receive_from_2.'&nbsp;ปากแหว่ง&nbsp;'.$receive_from_2.'&nbsp;เพดานโหว่&nbsp;'.$receive_from_2.'&nbsp;ผิดปกติ&nbsp;ระบุ&nbsp;'.$receive_from
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;คอ&nbsp;'.
$neck_1.'&nbsp;ปกติ&nbsp;'.$neck_2.'&nbsp;ผิดปกติ&nbsp;ระบุ&nbsp;'.$neck
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ท้อง&nbsp;'.
$receive_from_1.'&nbsp;ปกติ&nbsp;'.$receive_from_2.'&nbsp;ท้องอืด&nbsp;'.$receive_from_2.'&nbsp;ผิดปกติ&nbsp;ระบุ&nbsp;'.$receive_from
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;สะดือ&nbsp;'.
$receive_from_1.'&nbsp;ปกติ&nbsp;'.$receive_from_2.'&nbsp;Omphalocele&nbsp;'.$receive_from_2.'&nbsp;Gastroschisis&nbsp;'.$receive_from_2.'&nbsp;ผิดปกติ&nbsp;ระบุ&nbsp;'.$receive_from
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;กระดูกสันหลัง&nbsp;'.
$receive_from_1.'&nbsp;ปกติ&nbsp;'.$receive_from_2.'&nbsp;ผิดปกติ&nbsp;ระบุ&nbsp;'.$receive_from
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;แขนขา&nbsp;'.
$receive_from_1.'&nbsp;ปกติ&nbsp;'.$receive_from_2.'&nbsp;ผิดปกติ&nbsp;ระบุ&nbsp;'.$receive_from
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;อวัยวะเพศ&nbsp;'.
$receive_from_1.'&nbsp;ปกติ&nbsp;'.$receive_from_2.'&nbsp;ผิดปกติ&nbsp;ระบุ&nbsp;'.$receive_from
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ทวารหนัก&nbsp;'.
$receive_from_1.'&nbsp;ปกติ&nbsp;'.$receive_from_2.'&nbsp;ไม่มีรูก้น'
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;สีผิว&nbsp;'.
$receive_from_1.'&nbsp;แดง&nbsp;'.$receive_from_2.'&nbsp;ซีด&nbsp;'.$receive_from_2.'&nbsp;เขียว&nbsp;'.$receive_from_2.'&nbsp;อื่นๆ&nbsp;ระบุ&nbsp;&nbsp;'.$receive_from
.'<br><B>สภาพจิตใจทารกเมื่อแรกรับ</B>'
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;การแสดงออกด้านพฤติกรรม&nbsp;'.
$receive_from_1.'&nbsp;เฉย&nbsp;'.$receive_from_2.'&nbsp;ร้องไห้&nbsp;'.$receive_from_2.'&nbsp;อื่นๆ&nbsp;ระบุ&nbsp;'.$receive_from
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;การแสดงออกด้านอารมณ์&nbsp;'.
$receive_from_1.'&nbsp;ประเมินไม่ได้&nbsp;'.$receive_from_2.'&nbsp;ร้องโกรธ&nbsp;'.$receive_from_2.'&nbsp;อื่นๆ&nbsp;'.$receive_from
.'<br><B>อาการแรกรับ</B>&nbsp;'.nl2br(htmlspecialchars($row['family']))
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
    </table>
    <footter> <h2 style="text-align:right;font-size:8pt;">FM-OBS-004 แก้ไขครั้งที่ 01 ประกาศใช้ 15 กรกฏาคม 2562</h2> </footer>
';
$mpdf->WriteHTML($head);
$mpdf->Output();
