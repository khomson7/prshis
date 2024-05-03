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

/*Session::insertSystemAccessLog(json_encode(array(
    'report'=>'PRE-NURSENOTE-PDF',
   // 'action'=>'PRINT',
    'an'=>$an,
),JSON_UNESCAPED_UNICODE));

*/
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
        FROM `prs_lr_report2`
        WHERE an = :an";
$stmt = $conn->prepare($sql);
$stmt->execute($query_parameters);
$row  = $stmt->fetch();

//echo $row['id'];

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


                        $rxDate = $row['rxdate'];//วันที่ Discharge
                        $rxdate = date($rxDate);
                        $rxTime = $row['rxtime'];//เวลาที่ Discharge
                        $rxtime = date($rxTime);
                        $strDate =($rxdate."  ".$rxtime);
                       // $dchtime  = date('H:i', strtotime($origTime));

 $depart_1 = '( )';
 if ($row['depart'] == 'OPD') {
   $depart_1 = '('.$image_check.')';
 }

 $depart_2 = '( )';
 if ($row['depart'] == 'ER') {
   $depart_2 = '('.$image_check.')';
 }

 $depart_3 = '( )';
 if ($row['depart'] != 'OPD' && $row['depart'] != 'ER' && $row['depart'] != null) {$depart_3 = '('.$image_check.')';
    $depart  =  htmlspecialchars($row['depart']);
 }


 $hospital_by_1 = '( )';
 if ($row['hospital_by'] == 'เดินมา') {$hospital_by_1 = '('.$image_check.')';
 }

 $hospital_by_2 = '( )';
 if ($row['hospital_by'] == 'รถนั่ง') {$hospital_by_2 = '('.$image_check.')';
 }

 $hospital_by_3 = '( )';
 if ($row['hospital_by'] == 'รถนอน') {$hospital_by_3 = '('.$image_check.')';
 }

 $hospital_by_4 = '( )';
 if ($row['hospital_by'] != 'เดินมา' && $row['hospital_by'] != 'รถนั่ง' && $row['hospital_by'] != 'รถนอน' && $row['hospital_by'] != null) {$hospital_by_4 = '('.$image_check.')';
    $hospital_by  =  htmlspecialchars($row['hospital_by']);
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


      /*  $labor_history_top =  '<label> <b>ครรภ์ที่</b></label>


      /*  $labor_history_top =  '<label> <b>ครรภ์ที่</b></label>
        &emsp;<label> <b>ว/ด/ป คลอด/แท้ง</b></label>
        &emsp;<label> <b>GA</b></label>
        &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&ensp;<label> สถานพยาบาลที่รักษา</label>';
        */


        $labor_history_top =  '<table id="bg-table" width="100%" style="border-collapse: collapse;font-size:8pt;margin-top:2px;">
   
        <tr style="border:1px solid #000;margin: 35px;"> /* ชื่อ-สกุล */
            <td style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">
            <label> <b>ครรภ์ที่</b></label>
            </td>
            <td style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">
            <label> <b>วดป คลอด/แท้ง</b></label>
            </td>
            <td style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">
            <label> <b>GA</b></label>
            </td>
            <td style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">
            <label> <b>วิธีคลอด / แท้ง</b></label>
            </td>
            <td style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">
            <label> <b>น้ำหนักทารก</b></label>
            </td>
            <td style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">
            <label> <b>เพศ</b></label>
            </td>
            <td  style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">
            <label> <b>สถานที่คลอด</b></label>
            </td>
            <td style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">
            <label> <b>ภาวะแทรกซ้อน</b></label>
            </td>
            <td style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">
            <label> <b>ประวัติการคลอดติดไหล่/คลอดยาก</b></label>
            </td>
';

$labor_history_pos = explode(" ",$row['labor_history']);
$labor_history =  "";
$preg_num = "";
$labor_date ="";
$ga ="";
$y = 0;
for ($x = 1; $x < (count($labor_history_pos))/9; $x++) {
    $preg_num = htmlspecialchars($labor_history_pos[$y++]);
    $labor_date = htmlspecialchars($labor_history_pos[$y++]);
    $ga = htmlspecialchars($labor_history_pos[$y++]);
    $labor_history .= '
   
    <tr style="border:1px solid #000;margin: 35px;"> /* ชื่อ-สกุล */
        <td style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">
        <label>'.$preg_num.'</label>
        </td>
        <td style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">
        <label>'.$labor_date.'</label>
        </td>
        <td style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">
        <label>'.$labor_date.'</label>
        </td>
        <td style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">
        <label>'.$labor_date.'</label>
        </td>
        <td style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">
        <label>'.$labor_date.'</label>
        </td>
        <td style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">
        <label>'.$labor_date.'</label>
        </td>
        <td style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">
        <label>'.$labor_date.'</label>
        </td>
        <td style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">
        <label>'.$labor_date.'</label>
        </td>
        <td style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top;">
        <label>'.$ga.'</label>
        </td>
                 
            </tr>


    </table>';
}
 

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
    <h2 style="text-align:right;font-size:8pt;">FM-CLT-001</h2>
    
    <h2 style="text-align:center;font-size:11pt;">ใบบันทึกประวัติและประเมินสมรรถนะผู้ป่วยแรกรับ(เฉพาะผู้มาคลอด)&nbsp;<br>'.htmlspecialchars(DbConstant::HOSPITAL_NAME).'</h2>
    
    <label class="col-sm-12"><font color="red"> <b>รอปรับรายงานให้ถูกต้อง </b></font></label>
    <div class="form-group row">
                                <label class="col-sm-12">ข้อมูลทั่วไป</label>
                            </div>
<div class="f15"> รับใหม่วันที่ '.LongDateThai2($strDate).'<b> เวลา </b>'.htmlspecialchars($rxtime).'&nbsp;น.&nbsp;จาก&nbsp;'.
$depart_1.'&nbsp;OPD&nbsp;'.$depart_2.'&nbsp;ER&nbsp;'.$depart_3.'&nbsp;อื่นๆ&nbsp;'.$depart.'&nbsp;กรณีส่งต่อ ส่งต่อจาก&nbsp;'.$depart.'<br>'
.'รับไว้ในโรงพยาบาลโดย '.
$hospital_by_1.'&nbsp;เดินมา&nbsp;'.$hospital_by_2.'&nbsp;รถนั่ง&nbsp;'.$hospital_by_3.'&nbsp;รถนอน&nbsp;'.$hospital_by_4.'&nbsp;อื่นๆ&nbsp;'.$hospital_by.'<br>'
.'<B>อาการสำคัญที่นำมาโรงพยาบาล</B>&nbsp;'.nl2br(htmlspecialchars($row['cc']))
.'<br><B>ประวัติการเจ็บป่วยปัจจุบัน</B>&nbsp;'.nl2br(htmlspecialchars($row['current_illness']))
.'<br><B>ประวัติเจ็บป่วยในอดีต</B>'
.'<br>โรคประจำตัว '.
$hospital_by_1.'&nbsp;ปฏิเสธ&nbsp;'.$hospital_by_2.'&nbsp;มี ระบุ&nbsp;'.$hospital_by
.'<br>เคยรับการรักษาในโรงพยาบาล (ภายใน 1 ปี) '.
$hospital_by_1.'&nbsp;ปฏิเสธ&nbsp;'.$hospital_by_2.'&nbsp;เคย ระบุ&nbsp;'.$hospital_by
.'<br>ประวัติการผ่าตัด '.
$hospital_by_1.'&nbsp;ปฏิเสธ&nbsp;'.$hospital_by_2.'&nbsp;เคย ระบุ&nbsp;'.$hospital_by
.'<br>ประวัติการแพ้ (ยา/อาหาร/สารเคมี/เลือด) '.
$hospital_by_1.'&nbsp;ปฏิเสธ&nbsp;'.$hospital_by_2.'&nbsp;เคย ระบุ&nbsp;'.$hospital_by
.'<br>ประวัติการได้รับวัคซีน (เฉพาะ < 15 ปี) '.
$hospital_by_1.'&nbsp;ครบตามเกณฑ์&nbsp;'.$hospital_by_2.'&nbsp;ไม่ครบตามเกณฑ์ ระบุ&nbsp;'.$hospital_by
.'<br>การเจริญเติบโตและพัฒนาการ (เฉพาะ < 15 ปี) '.
$hospital_by_1.'&nbsp;สมวัย&nbsp;'.$hospital_by_2.'&nbsp;ไม่สมวัย ระบุ&nbsp;'.$hospital_by
.'<br>ประวัติการใช้ยาและผลิตภัณฑ์สุขภาพ '.
$hospital_by_1.'&nbsp;ปฏิเสธ&nbsp;'.$hospital_by_2.'&nbsp;มีระบุ ระบุ&nbsp;'.$hospital_by
.'<br>ประวัติการเจ็บป่วยในครอบครัว '.
$hospital_by_1.'&nbsp;ปฏิเสธ&nbsp;'.$hospital_by_2.'&nbsp;มีระบุ ระบุ&nbsp;'.$hospital_by
.'<br><B>สัญญาณชีพแรกรับ</B>&emsp;BT&emsp;'.round(($row['bt']),2)
.'&emsp;°C&emsp;PR&emsp;'.round(($row['hr']),2)
.'&emsp;/min&emsp;RR&emsp;'.round(($row['rr']),2).'&emsp;/min'
.'&emsp;BP&emsp;'.round(($row['bps']),2).' / '.round(($row['bpd']),2).'&emsp;mmHg'
.'<br><B>ประวัติการคลอด</B><br>'
.$labor_history_top.'
 '.$labor_history

.'<br><B>สภาพร่างกายผู้ป่วยแรกรับ</B>'
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ระดับความรู้สึกตัว&nbsp;'.
$hospital_by_1.'&nbsp;รู้สึกตัวดี&nbsp;'.$hospital_by_2.'&nbsp;สับสน&nbsp;'.$hospital_by_3.'&nbsp;ซึม&nbsp;'.$hospital_by_4.'&nbsp;ไม่รู้สึกตัว&nbsp;'
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;การหายใจ&nbsp;'.
$hospital_by_1.'&nbsp;ปกติ&nbsp;'.$hospital_by_2.'&nbsp;หายใจหอบ&nbsp;'.$hospital_by_3.'&nbsp;หายใจลำบาก&nbsp;'.$hospital_by_4.'&nbsp;ไม่หายใจ&nbsp;'
.$hospital_by_4.'&nbsp;อื่นๆ&nbsp;'.$hospital_by
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;การไหลเวียนโลหิต สีผิว&nbsp;'.
$hospital_by_1.'&nbsp;ปกติ&nbsp;'.$hospital_by_2.'&nbsp;ซีด&nbsp;'.$hospital_by_3.'&nbsp;ปลายมือปลายเท้าเขียว&nbsp;'.$hospital_by_4.'&nbsp;รอบปากเขียว&nbsp;'
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;อาการบวม&nbsp;'.$hospital_by_1.'&nbsp;ไม่มี&nbsp;'
.$hospital_by_4.'&nbsp;บวมบริวณ&nbsp;'.$hospital_by
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ผิวหนัง&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
$hospital_by_1.'&nbsp;ปกติ&nbsp;'.$hospital_by_2.'&nbsp;หนังแตก&nbsp;'.$hospital_by_3.'&nbsp;เขียวช้ำ&nbsp;'.$hospital_by_4.'&nbsp;ผื่นแดง&nbsp;'
.$hospital_by_3.'&nbsp;ผื่นคัน&nbsp;'.$hospital_by_4.'&nbsp;เหลือง&nbsp;'
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;การติดต่อสื่อสาร หู&nbsp;'.
$hospital_by_1.'&nbsp;ได้ยินชัดเจน&nbsp;'.$hospital_by_2.'&nbsp;ได้ยินไม่ชัดเจน : ใช้อุปกรณ์ช่วยฟัง&nbsp;'.$hospital_by_3.'&nbsp;มี&nbsp;'.$hospital_by_4.'&nbsp;ไม่มี&nbsp;'
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ตา&nbsp;'.
$hospital_by_1.'&nbsp;เห็นชัดเจน&nbsp;'.$hospital_by_2.'&nbsp;เห็นไม่ชัดเจน : สวมแว่นตา&nbsp;'.$hospital_by_3.'&nbsp;สวม&nbsp;'.$hospital_by_4.'&nbsp;ไม่สวม&nbsp;'
.'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;การพูด&nbsp;'.
$hospital_by_1.'&nbsp;เห็นชัดเจน&nbsp;'.$hospital_by_2.'&nbsp;พูดติดอ่าง&nbsp;'.$hospital_by_3.'&nbsp;เป็นใบ้&nbsp;'.$hospital_by_4.'&nbsp;อื่นๆ&nbsp;'.$hospital_by
.'<br><b>สภาพจิตใจแรกรับ (การแสดงออกทางพฤติกรรม, การแสดงออกทางอารมณ์, สิ่งที่กังวล)</b>&nbsp;'.nl2br(htmlspecialchars($row['state_of_mind']))
.'<br><B>อาการแรกรับ</B>&nbsp;'.nl2br(htmlspecialchars($row['first_symptoms']))
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
            <td  colspan="3" width="100%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;vertical-align:text-top; text-align: center;">
            <label>Attending physician : <br><br>'.htmlspecialchars($row['doctor_name']).'</label>
            </td>
        </tr>
    </table>
    <footter> <h2 style="text-align:right;font-size:8pt;">ประกาศใช้ 8 มีนาคม 2562 งานเอกสารคุณภาพ ศูนย์คุณภาพ</h2> </footer>
';
$mpdf->WriteHTML($head);
$mpdf->Output();
