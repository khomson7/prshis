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
$query_parameters2 = ['an' => $an,'id' => $id];

Session::insertSystemAccessLog(json_encode(array(
    'report'=>'BARTHEL-PDF',
   // 'action'=>'PRINT',
    'an'=>$an,
),JSON_UNESCAPED_UNICODE));

//echo $id;

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
$sql = "SELECT pn.*,date(create_datetime) as rxdate
        FROM prs_barthel_index pn
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

                       $chronic_check1 = '( )';
                       if ($row['chronic_check'] == '1') {
                         $chronic_check1 = '('.$image_check.')';
                       }
                      
                       $chronic_check2 = '( )';
                       if ($row['chronic_check'] == '2') {
                         $chronic_check2 = '('.$image_check.')';
                       }
                       $chronic_check3 = '( )';
                       if ($row['chronic_check'] == '3') {
                         $chronic_check3 = '('.$image_check.')';
                       }

                       $type_check1 = '( )';
                       if ($row['type_check'] == '1') {
                         $type_check1 = '('.$image_check.')';
                       }
                      
                       $type_check2 = '( )';
                       if ($row['type_check'] == '2') {
                         $type_check2 = '('.$image_check.')';
                       }

                       $feedings = '-';
                       if($row['feeding'] == '1'){
                        $feedings = 0;
                       }else if ($row['feeding'] > '1'){
                        $feedings =  $row['feeding'];
                       }

                       $tranfers = '-';
                       if($row['tranfer'] == '1'){
                        $tranfers = 0;
                       }else if ($row['tranfer'] > '1'){
                        $tranfers =  $row['tranfer'];
                       }

                       $groomings = '-';
                       if($row['grooming'] == '1'){
                        $groomings = 0;
                       }else if ($row['grooming'] > '1'){
                        $groomings =  $row['grooming'];
                       }

                       $toilet_uses = '-';
                       if($row['toilet_use'] == '1'){
                        $toilet_uses = 0;
                       }else if ($row['toilet_use'] > '1'){
                        $toilet_uses =  $row['toilet_use'];
                       }

                       $bathings = '-';
                       if($row['bathing'] == '1'){
                        $bathings = 0;
                       }else if ($row['bathing'] > '1'){
                        $bathings =  $row['bathing'];
                       }

                       $mobilitys = '-';
                       if($row['mobility'] == '1'){
                        $mobilitys = 0;
                       }else if ($row['mobility'] > '1'){
                        $mobilitys =  $row['mobility'];
                       }

                       $stairss = '-';
                       if($row['stairs'] == '1'){
                        $stairss = 0;
                       }else if ($row['stairs'] > '1'){
                        $stairss =  $row['stairs'];
                       }

                       $dressings = '-';
                       if($row['dressing'] == '1'){
                        $dressings = 0;
                       }else if ($row['dressing'] > '1'){
                        $dressings =  $row['dressing'];
                       }

                       $bowels = '-';
                       if($row['bowel'] == '1'){
                        $bowels = 0;
                       }else if ($row['bowel'] > '1'){
                        $bowels =  $row['bowel'];
                       }

                       $bladders = '-';
                       if($row['bladder'] == '1'){
                        $bladders = 0;
                       }else if ($row['bladder'] > '1'){
                        $bladders =  $row['bladder'];
                       }

                       $scores = '-';
                       if($row['score'] >= '0'){                       
                        $scores =  $row['score'];
                       }

                       $feedings_dc = '-';
                       if($row['feeding_dc'] == '1'){
                        $feedings_dc = 0;
                       }else if ($row['feeding_dc'] > '1'){
                        $feedings_dc =  $row['feeding_dc'];
                       }

                       $tranfers_dc = '-';
                       if($row['tranfer_dc'] == '1'){
                        $tranfers_dc = 0;
                       }else if ($row['tranfer_dc'] > '1'){
                        $tranfers_dc =  $row['tranfer_dc'];
                       }

                       $groomings_dc = '-';
                       if($row['grooming_dc'] == '1'){
                        $groomings_dc = 0;
                       }else if ($row['grooming_dc'] > '1'){
                        $groomings_dc =  $row['grooming_dc'];
                       }

                       $toilet_uses_dc = '-';
                       if($row['toilet_use_dc'] == '1'){
                        $toilet_uses_dc = 0;
                       }else if ($row['toilet_use_dc'] > '1'){
                        $toilet_uses_dc =  $row['toilet_use_dc'];
                       }

                       $bathings_dc = '-';
                       if($row['bathing_dc'] == '1'){
                        $bathings_dc = 0;
                       }else if ($row['bathing_dc'] > '1'){
                        $bathings_dc =  $row['bathing_dc'];
                       }

                       $mobilitys_dc = '-';
                       if($row['mobility_dc'] == '1'){
                        $mobilitys_dc = 0;
                       }else if ($row['mobility_dc'] > '1'){
                        $mobilitys_dc =  $row['mobility_dc'];
                       }

                       $stairss_dc = '-';
                       if($row['stairs_dc'] == '1'){
                        $stairss_dc = 0;
                       }else if ($row['stairs_dc'] > '1'){
                        $stairss_dc =  $row['stairs_dc'];
                       }

                       $dress_dc = '-';
                       if($row['dressing_dc'] == '1'){
                        $dress_dc= 0;
                       }else if ($row['dressing_dc'] > '1'){
                        $dress_dc =  $row['dressing_dc'];
                       }

//echo $dressings_dc;
                     
                       $bowelss_dc = '-';
                       if($row['bowel_dc'] == '1'){
                        $bowelss_dc = 0;
                       }else if ($row['bowel_dc'] > '1'){
                        $bowelss_dc =  $row['bowel_dc'];
                       }

                       $bladders_dc = '-';
                       if($row['bladder_dc'] == '1'){
                        $bladders_dc = 0;
                       }else if ($row['bladder_dc'] > '1'){
                        $bladders_dc =  $row['bladder_dc'];
                       }

                       $scores_dc = '';
                       if ($feedings_dc == '-' && $tranfers_dc == '-' && $groomings_dc == '-' && $toilet_uses_dc == '-' && $bathings_dc == '-' 
                       && $mobilitys_dc == '-' && $stairss_dc == '-' && $dressings_dc = '-' && $bowels_dc = '-' && $bladders_dc = '-') {
                        $scores_dc = '-';
                       } else if($row['score_dc'] >= '0'){                       
                        $scores_dc =  $row['score_dc'];
                       }
//echo $scores_dc;

                       $rankin_scale = '-';
                       if ($row['rankin_scale'] == '9') {
                        $rankin_scale = '0';
                       } else if ($row['rankin_scale'] > '0') {
                        $rankin_scale = htmlspecialchars($row['rankin_scale']);
                       }

                       $rankin_scale_dc = '-';
                       if ($row['rankin_scale_dc'] == '9') {
                        $rankin_scale_dc = '0';
                       } else if ($row['rankin_scale_dc'] > '0') {
                        $rankin_scale_dc = htmlspecialchars($row['rankin_scale_dc']);
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

        $ids = '20'; //Link menu
        $check_    = ReportQueryUtils::getProduction($ids);

        $check_report = '( )';
        if ($check_  == '1') 
        {$check_report = '&nbsp;<font color="red">รอปรับรายงาน</font>';
        } else {
            $check_report = '';
        }
       
        
       // $maxNrOfPages = ceil($max/$itemsPerPage);

$head =
'

    <style>
    div.f15 {
 
        font-size: 10px; 
        
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

        .container {
            display: flex;
            justify-content: space-between;
        }
        .column {
            flex: 1;
            padding: 10px;
            margin: 5px;
            border: 1px solid #ccc;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            border: 1px solid #ccc;
            padding: 10px;

        }

        
    </style>
    <h2 style="text-align:right;font-size:8pt;">&nbsp;</h2>
    
    <h2 style="text-align:center;font-size:10pt;">แบบประเมิน BARTHEL INDEX & THE MODIFIED RANKIN SCALE '.htmlspecialchars(DbConstant::HOSPITAL_NAME).$check_report.'</h2>
    
   
<div class="f15">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$chronic_check1.'<b>&nbsp;Stroke</b>'
.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$chronic_check2.'<b>&nbsp;TBI</b>'
.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$chronic_check3.'<b>&nbsp;SCI</b>'
.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$type_check1.'<b>&nbsp;IMC</b>'
.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$type_check2.'<b>&nbsp;Non-IMC</b></div>'

.'<table lass="center" id="bg-table" width="100%" style="border-collapse: collapse;font-size:8pt;margin-top:8px;">'
.'<tr style="border:1px solid #000;margin: 45px;">'
.'<td style="text-align:center; border-right:1px solid #000;padding:4px;" width="8%">&nbsp;<b>กิจกรรม</b></td>'
.'<td style="text-align:center; border-right:1px solid #000;padding:4px;" width="10%">&nbsp;<b>แบบประเมินกิจวัตรประจำวันของผู้ป่วย (Barthel index)</b></td>'
.'<td style="text-align:center; border-right:1px solid #000;padding:4px;" width="4%">&nbsp;<b>Admit</b></td>'
.'<td style="text-align:center; border-right:1px solid #000;padding:4px;" width="3%">&nbsp;<b>D/C</b></td>'
.'<td style="text-align:center; border-right:1px solid #000;padding:4px;" width="2%">&nbsp;<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>'
.'<td style="text-align:center; border-right:1px solid #000;padding:4px;" width="2%">&nbsp;<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>'
.'</tr>'

.'<tr>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;" rowspan="3"><b>1.Feeding รับประทานอาหารเมื่อเตรียมสำรับไว้ให้เรียบร้อยต่อหน้า</b></td>'
.'<td style="text-align:left; border-top:1px solid #000;padding:4px;">&nbsp;0 = ไม่สามารถตักอาหารเข้าปากเองได้</td>'
.'<td style="text-align:center; border:1px solid #000;padding:4px;" rowspan="3"><b>'.htmlspecialchars($feedings).'</b></td>'
.'<td style="text-align:center; border:1px solid #000;padding:4px;" rowspan="3"><b>'.htmlspecialchars($feedings_dc).'</b></td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;" rowspan="3"></td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;" rowspan="3"></td>'
.'</tr>'
.'<tr>'
.'<td style="text-align:left; border-top:1px solid #000;padding:4px;">&nbsp;5 = ตักอาหารเองได้ แต่ต้องมีคนช่วย เช่น ช่วยใช้ช้อนตักอาหารเตรียมไว้ / ตัดเป็นชิ้น</td>'
.'</tr>'
.'<tr>'
.'<td style="text-align:left; border-bottom:1px solid #000;padding:4px;">&nbsp;10 = ตักอาหารและช่วยตัวเองได้ปกติ</td>'
.'</tr>'

.'<tr>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;" rowspan="4"><b>2.Transfer ลุกนั่งจากที่นอนหรือจากเตียงไปยังเก้าอี้</b></td>'
.'<td style="text-align:left; border-top:1px solid #000;padding:4px;">&nbsp;0 = ไม่สามารถนั่งได้ (นั่งแล้วจะล้มเสมอ)หรือต้องใช้คน 2 คนช่วยกันยกขึ้น</td>'
.'<td style="text-align:center; border:1px solid #000;padding:4px;" rowspan="4"><b>'.htmlspecialchars($tranfers).'</b></td>'
.'<td style="text-align:center; border:1px solid #000;padding:4px;" rowspan="4"><b>'.htmlspecialchars($tranfers_dc).'</b></td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;" rowspan="4"></td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;" rowspan="4"></td>'
.'</tr>'
.'<tr>'
.'<td style="text-align:left; border-top:1px solid #000;padding:4px;">&nbsp;5 = ต้องการความช่วยเหลืออย่างมากจึงจะนั่งได้ เช่น ต้องใช้คน 1-2คนพยุงจึงจะนั่งได้</td>'
.'</tr>'
.'<tr>'
.'<td style="text-align:left; border:0.5px solid #000;padding:4px;">10 = ต้องการความช่วยเหลือบ้าง เช่น บอกให้ทำตามช่วยพยุงเล็กน้อย</td>'
.'</tr>'
.'<tr>'
.'<td style="text-align:left; border-bottom:1px solid #000;padding:4px;">15 = ทำเองได้</td>'
.'</tr>'

.'<tr>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;" rowspan="2"><b>3.Grooming การล้างหน้า หวีผม แปรงฟัน โกนหนวด</b></td>'
.'<td style="text-align:left; border-top:1px solid #000;padding:4px;">&nbsp;0 = ต้องการความช่วยเหลือ</td>'
.'<td style="text-align:center; border:1px solid #000;padding:4px;" rowspan="2"><b>'.htmlspecialchars($groomings).'</b></td>'
.'<td style="text-align:center; border:1px solid #000;padding:4px;" rowspan="2"><b>'.htmlspecialchars($groomings_dc).'</b></td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;" rowspan="2"></td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;" rowspan="2"></td>'
.'</tr>'
.'<tr>'
.'<td style="text-align:left; border-bottom:1px solid #000;padding:4px;">&nbsp;5 = ทำได้เอง (รวมทั้งที่ทำได้เอง ถ้าเตรียมอุปกรณ์ไว้ให้)</td>'
.'</tr>'


.'<tr>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;" rowspan="3"><b>4.Toilet use การใช้ห้องน้ำ</b></td>'
.'<td style="text-align:left; border-top:1px solid #000;padding:4px;">&nbsp;0 = ช่วยตัวเองไม่ได้</td>'
.'<td style="text-align:center; border:1px solid #000;padding:4px;" rowspan="3"><b>'.htmlspecialchars($toilet_uses).'</b></td>'
.'<td style="text-align:center; border:1px solid #000;padding:4px;" rowspan="3"><b>'.htmlspecialchars($toilet_uses_dc).'</b></td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;" rowspan="3"></td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;" rowspan="3"></td>'
.'</tr>'
.'<tr>'
.'<td style="text-align:left; border-top:1px solid #000;padding:4px;">&nbsp;5 = ทำได้เอง (ต้องการความช่วยเหลือในบางสิ่ง)</td>'
.'</tr>'
.'<tr>'
.'<td style="text-align:left; border-bottom:1px solid #000;padding:4px;">&nbsp;10 = ช่วยเหลือตัวเองได้ดี(ขึ้นนั่งและลงจากโถส้วมได้เอง ทำความสะอาดได้เรียบร้อยหลังเสร็จ)</td>'
.'</tr>'


.'<tr>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;" rowspan="2"><b>5.Bathing การอาบน้ำ</b></td>'
.'<td style="text-align:left; border-top:1px solid #000;padding:4px;">&nbsp;0 = ต้องมีคนช่วยหรือทำให้</td>'
.'<td style="text-align:center; border:1px solid #000;padding:4px;" rowspan="2"><b>'.htmlspecialchars($bathings).'</b></td>'
.'<td style="text-align:center; border:1px solid #000;padding:4px;" rowspan="2"><b>'.htmlspecialchars($bathings_dc).'</b></td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;" rowspan="2"></td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;" rowspan="2"></td>'
.'</tr>'
.'<tr>'
.'<td style="text-align:left; border-bottom:1px solid #000;padding:4px;">&nbsp;5 = อาบน้ำได้เอง</td>'
.'</tr>'

.'<tr>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;" rowspan="4"><b>6.Mobility การเคลื่อนที่ภายในห้องหรือ<br />บ้าน</b></td>'
.'<td style="text-align:left; border-top:1px solid #000;padding:4px;">&nbsp;0 = เคลื่อนที่ไปไหนไม่ได้</td>'
.'<td style="text-align:center; border:1px solid #000;padding:4px;" rowspan="4"><b>'.htmlspecialchars($mobilitys).'</b></td>'
.'<td style="text-align:center; border:1px solid #000;padding:4px;" rowspan="4"><b>'.htmlspecialchars($mobilitys_dc).'</b></td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;" rowspan="4"></td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;" rowspan="4"></td>'
.'</tr>'
.'<tr>'
.'<td style="text-align:left; border-top:1px solid #000;padding:4px;">&nbsp;5 = ต้องใช้รถเข็นช่วยตัวเองให้เคลื่อนที่ได้เอง(ไม่ต้องมีคนช่วยเข็น)</td>'
.'</tr>'
.'<tr>'
.'<td style="text-align:left; border:0.5px solid #000;padding:4px;">10 = เดินหรือเคลื่อนที่ได้โดยมีคนช่วย เช่น พยุง หรือบอกให้ทำตาม</td>'
.'</tr>'
.'<tr>'
.'<td style="text-align:left; border-bottom:1px solid #000;padding:4px;">15 = เดินหรือเคลื่อนที่ได้เอง</td>'
.'</tr>'

.'<tr>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;" rowspan="3"><b>7.Stairs การขึ้นลงบันได 1 ขั้น</b></td>'
.'<td style="text-align:left; border-top:1px solid #000;padding:4px;">&nbsp;0 = ไม่สามารถทำได้</td>'
.'<td style="text-align:center; border:1px solid #000;padding:4px;" rowspan="3"><b>'.htmlspecialchars($stairss).'</b></td>'
.'<td style="text-align:center; border:1px solid #000;padding:4px;" rowspan="3"><b>'.htmlspecialchars($stairss_dc).'</b></td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;" rowspan="3"></td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;" rowspan="3"></td>'
.'</tr>'
.'<tr>'
.'<td style="text-align:left; border-top:1px solid #000;padding:4px;">&nbsp;5 = ต้องการคนช่วยเหลือ</td>'
.'</tr>'
.'<tr>'
.'<td style="text-align:left; border-bottom:1px solid #000;padding:4px;">&nbsp;10 = ขึ้นลงเองได้ (ถ้าต้องการใช้อุปกรณ์ เช่น walker)</td>'
.'</tr>'

.'<tr>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;" rowspan="3"><b>8.Dressing การสวมใส่เสื้อผ้า</b></td>'
.'<td style="text-align:left; border-top:1px solid #000;padding:4px;">&nbsp;0 = ไม่สามารถทำได้</td>'
.'<td style="text-align:center; border:1px solid #000;padding:4px;" rowspan="3"><b>'.htmlspecialchars($dressings).'</b></td>'
.'<td style="text-align:center; border:1px solid #000;padding:4px;" rowspan="3"><b>'.htmlspecialchars($dress_dc).'</b></td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;" rowspan="3"></td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;" rowspan="3"></td>'
.'</tr>'
.'<tr>'
.'<td style="text-align:left; border-top:1px solid #000;padding:4px;">&nbsp;5 = ต้องการคนช่วยเหลือ</td>'
.'</tr>'
.'<tr>'
.'<td style="text-align:left; border-bottom:1px solid #000;padding:4px;">&nbsp;10 = ขึ้นลงเองได้ (ถ้าต้องการใช้อุปกรณ์ เช่น walker)</td>'
.'</tr>'

.'<tr>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;" rowspan="3"><b>9.Bowel การกลั้นการถ่ายอุจจาระ ใน 1 สัปดาห์ที่ผ่านมา</b></td>'
.'<td style="text-align:left; border-top:1px solid #000;padding:4px;">&nbsp;0 = ไม่สามารถทำได้</td>'
.'<td style="text-align:center; border:1px solid #000;padding:4px;" rowspan="3"><b>'.htmlspecialchars($bowels).'</b></td>'
.'<td style="text-align:center; border:1px solid #000;padding:4px;" rowspan="3"><b>'.htmlspecialchars($bowelss_dc).'</b></td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;" rowspan="3"></td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;" rowspan="3"></td>'
.'</tr>'
.'<tr>'
.'<td style="text-align:left; border-top:1px solid #000;padding:4px;">&nbsp;5 = ต้องการคนช่วยเหลือ</td>'
.'</tr>'
.'<tr>'
.'<td style="text-align:left; border-bottom:1px solid #000;padding:4px;">&nbsp;10 = ขึ้นลงเองได้ (ถ้าต้องการใช้อุปกรณ์ เช่น walker)</td>'
.'</tr>'

.'<tr>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;" rowspan="3"><b>10.Bladder การกลั้นปัสสาวะใน 1 สัปดาห์ที่ผ่านมา</b></td>'
.'<td style="text-align:left; border-top:1px solid #000;padding:4px;">&nbsp;0 = ไม่สามารถทำได้</td>'
.'<td style="text-align:center; border:1px solid #000;padding:4px;" rowspan="3"><b>'.htmlspecialchars($bladders).'</b></td>'
.'<td style="text-align:center; border:1px solid #000;padding:4px;" rowspan="3"><b>'.htmlspecialchars($bladders_dc).'</b></td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;" rowspan="3"></td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;" rowspan="3"></td>'
.'</tr>'
.'<tr>'
.'<td style="text-align:left; border-top:1px solid #000;padding:4px;">&nbsp;5 = ต้องการคนช่วยเหลือ</td>'
.'</tr>'
.'<tr>'
.'<td style="text-align:left; border-bottom:1px solid #000;padding:4px;">&nbsp;10 = ขึ้นลงเองได้ (ถ้าต้องการใช้อุปกรณ์ เช่น walker)</td>'
.'</tr>'

.'<tr>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;" rowspan="3"><b>รวมคะแนน</b></td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;">&nbsp;</td>'
.'<td style="text-align:center; border:1px solid #000;padding:4px;"><b>'.htmlspecialchars($scores).'</b></td>'
.'<td style="text-align:center; border:1px solid #000;padding:4px;"><b>'.htmlspecialchars($scores_dc).'</b></td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;"></td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;"></td>'
.'</tr>'

.'</table>'

.'<table lass="center" id="bg-table" width="100%" style="border-collapse: collapse;font-size:8pt;margin-top:8px;">'
.'<tr style="border:1px solid #000;margin: 45px;">'
.'<td style="text-align:center; border-right:1px solid #000;padding:4px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>'
.'<td style="text-align:center; border-right:1px solid #000;padding:4px;">&nbsp;<b>THE MODIFIED RANKIN SCALE</b></td>'
.'<td style="text-align:center; border-right:1px solid #000;padding:4px;">&nbsp;<b>Admit</b></td>'
.'<td style="text-align:center; border-right:1px solid #000;padding:4px;">&nbsp;<b>D/C</b></td>'
.'<td style="text-align:center; border-right:1px solid #000;padding:4px;">&nbsp;<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>'
.'<td style="text-align:center; border-right:1px solid #000;padding:4px;">&nbsp;<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>'
.'</tr>'

.'<tr>
<td style="text-align:left; border:1px solid #000;padding:4px;"><b>0</b></td>
<td style="text-align:left; border:1px solid #000;padding:4px;">&nbsp;ไม่มีความผิดปกติเลย</td>'
.'<td style="text-align:center; border:1px solid #000;padding:4px;" rowspan="7"><b>'.htmlspecialchars($rankin_scale).'</b></td>'
.'<td style="text-align:center; border:1px solid #000;padding:4px;" rowspan="7"><b>'.htmlspecialchars($rankin_scale_dc).'</b></td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;"><b></b></td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;"><b></b></td>'
.'</tr>'

.'<tr>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;"><b>1</b></td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;">&nbsp;ไม่มีความผิดปกติที่รุนแรง สามารถทำกิจวัตรประจำวันได้ทุกอย่าง ทำงานอาชีพได้</td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;"><b></b></td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;"><b></b></td>'
.'</tr>'

.'<tr>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;"><b>2</b></td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;">&nbsp;มีความผิดปกติเล็กน้อย สามารถทำกิจวัตรประจำวันได้ทุกอย่าง แต่ทำงานอาชีพไม่ได้</td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;"><b></b></td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;"><b></b></td>'
.'</tr>'

.'<tr>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;"><b>3</b></td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;">&nbsp;มีความผิดปกติพอสมควร สามารถทำกิจวัตรประจำวันได้บางอย่าง เดินได้โดยไม่ต้องมีคนช่วย</td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;"><b></b></td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;"><b></b></td>'
.'</tr>'

.'<tr>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;"><b>4</b></td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;">&nbsp;มีความผิดปกติมาก ไม่สามารถทำกิจวัตรประจำวันเองโดยไม่มีคนช่วยได้ เดินได้แต่ต้องพยุง</td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;"><b></b></td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;"><b></b></td>'
.'</tr>'

.'<tr>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;"><b>5</b></td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;">&nbsp;มีความผิดปกติรุนแรง ต้องนอนบนเตียง ปัสสาวะราด ต้องการการดูแลอย่างใกล้ชิด</td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;"><b></b></td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;"><b></b></td>'
.'</tr>'

.'<tr>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;"><b>6</b></td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;">&nbsp;เสียชีวิต</td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;"><b></b></td>'
.'<td style="text-align:left; border:1px solid #000;padding:4px;"><b></b></td>'
.'</tr>'

.'</table>'

.'<table id="bg-table" width="100%" style="border-collapse: collapse;font-size:8pt;margin-top:2px;">
   
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
    </table>' ;
//$mpdf->SetColumns(2);
// Table header
$head .= '<table border="1" cellpadding="10" cellspacing="0">';
$html .= '<thead><tr><th rowspan="2">ภาวะเสี่ยง</th>';
//แสดงข้อมุลอยู่ในช่วง ก่อน footer
$mpdf->setAutoTopMargin = 'stretch';
$mpdf->setAutoBottomMargin = 'stretch';

$mpdf->setFooter('HN: '.htmlspecialchars($hn).' AN: '.htmlspecialchars($an).' Page '.'{PAGENO}');
$mpdf->WriteHTML($head);
$mpdf->Output();
