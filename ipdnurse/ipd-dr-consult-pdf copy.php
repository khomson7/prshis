<?php
require_once '../include/Session.php';
//ตรวจสอบว่า session login ตรงกันหรือไม่
     
          
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
     Session::checkPermission('ADMISSION_NOTE','VIEW')
     )){
     return;
 }

//Session::checkPermissionAndShowMessage('DOCUMENT', 'PRINT');


require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
//require_once __DIR__ . '/vendor/autoload.php';
require_once '../include/DbUtils.php';
require_once '../include/Session.php';
require_once '../include/KphisQueryUtils.php';

date_default_timezone_set('asia/bangkok');
$conn = DbUtils::get_hosxp_connection(); //เชื่อ

$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
$mpdf->AddPageByArray([
    'margin-left' => 10,
    'margin-right' => 10,
    'margin-top' => 6,
    'margin-bottom' => 6,
]);
$head='';

        $an_REQUEST = $_REQUEST['an_consult'];//รับค่า an
        $hn_REQUEST = KphisQueryUtils::getHnByAn($an_REQUEST);// function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา
        //----------------------select ข้อมูล จากฐานข้อมูลเพื่อค้นหา ชื่อ - สกุล
        $query_parameters_REQUEST = ['an'=>$an_REQUEST];
        $sql_ipt = "select patient.hn,patient.pname,patient.fname,patient.lname,patient.drugallergy,
                    an_stat.age_y,an_stat.age_m,an_stat.age_d,
                    ipt.regdate,ipt.regtime,ipt.ward,
                    ipt.pttype,
                    pttype.`name` as pttype_name,
                    ward.shortname
                    from ".DbConstant::HOSXP_DBNAME.".ipt
                    left outer join ".DbConstant::HOSXP_DBNAME.".an_stat on an_stat.an=ipt.an
                    left outer join ".DbConstant::HOSXP_DBNAME.".patient on patient.hn=ipt.hn
                    left outer join ".DbConstant::HOSXP_DBNAME.".ward on ward.ward=ipt.ward
                    LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".pttype ON pttype.pttype = ipt.pttype
                    WHERE ipt.an=:an";
        $stmt_ipt = $conn->prepare($sql_ipt);
        $stmt_ipt->execute($query_parameters_REQUEST);
        $row_iptCount = 0;
        while ($row_ipt = $stmt_ipt->fetch()){
                $hn_row_ipt = htmlspecialchars($row_ipt['hn']);
                $pname_row_ipt = htmlspecialchars($row_ipt['pname']);
                $fname_row_ipt = htmlspecialchars($row_ipt['fname']);
                $lname_row_ipt = htmlspecialchars($row_ipt['lname']);
                $age_y_row_ipt = htmlspecialchars($row_ipt['age_y']);
                $age_m_row_ipt = htmlspecialchars($row_ipt['age_m']);
                $age_d_row_ipt = htmlspecialchars($row_ipt['age_d']);
        }
        //----------------------select ข้อมูล จากฐานข้อมูลเพื่อค้นหา ชื่อ - สกุล
        $query_parameters_REQUEST = ['an'=>$an_REQUEST,];
        $name_session = $_SESSION['name'];
        $query_parameters = ['an'=>$an_REQUEST];
        $sql="  SELECT ipt.hn,ipt.an,iptadm.bedno,
                concat(patient.pname,patient.fname,' ',patient.lname) as pname,aa.income as income,
                aa.admdate,aa.age_y,aa.age_m,aa.age_d,
                (SELECT
                GROUP_CONCAT(concat('<div  class=\"text-sm text-truncate\" style=\"max-width:130px\">',doctor_request.`name`,if(doctor_request.licenseno = '-99999' or doctor_request.licenseno is null ,'',concat(' (',doctor_request.licenseno,')')),if(doctor_request2.`name` is null ,'',concat(' / ',doctor_request2.`name`,if(doctor_request2.licenseno = '-99999' or doctor_request2.licenseno is null ,'',concat(' (',doctor_request2.licenseno,')')))),'</div>')
                ORDER BY idc_request.consult_signature_id ASC SEPARATOR '') AS string_consult_request_name
                FROM ".DbConstant::KPHIS_DBNAME.".ipd_dr_consult ipc
                LEFT OUTER JOIN ".DbConstant::KPHIS_DBNAME.".ipd_dr_consult_signature_request idc_request ON idc_request.consult_id = ipc.consult_id
                LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".doctor doctor_request ON doctor_request.`code` = idc_request.consult_doctorcode_request
                LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".doctor doctor_request2 ON doctor_request2.`code` = idc_request.consult_doctorcode_request_person2
                WHERE ipc.consult_id=ipd_c.consult_id) AS string_consult_request_name,
                (SELECT
                GROUP_CONCAT(concat('<div  class=\"text-sm text-truncate\" style=\"max-width:130px\">',doctor_reply.`name`,if(doctor_reply.licenseno = '-99999' or doctor_reply.licenseno is null ,'',concat(' (',doctor_reply.licenseno,')')),if(doctor_reply2.`name` is null ,'',concat(' / ',doctor_reply2.`name`,if(doctor_reply2.licenseno = '-99999' or doctor_reply2.licenseno is null ,'',concat(' (',doctor_reply2.licenseno,')')))),'</div>')
                ORDER BY idc_reply.consult_reply_id ASC SEPARATOR '') AS string_consult_reply_name
                FROM ".DbConstant::KPHIS_DBNAME.".ipd_dr_consult ipc
                LEFT OUTER JOIN ".DbConstant::KPHIS_DBNAME.".ipd_dr_consult_signature_reply idc_reply ON idc_reply.consult_id = ipc.consult_id
                LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".doctor doctor_reply ON doctor_reply.`code` = idc_reply.consult_doctorcode_reply
                LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".doctor doctor_reply2 ON doctor_reply2.`code` = idc_reply.consult_doctorcode_reply_person2
                WHERE ipc.consult_id=ipd_c.consult_id) AS string_consult_reply_name,
                s.spclty_name as spclty_name,
                d4.`name` as consult_doctorcode_mention_name,
                w.`name` as consult_ward_name,
                ipd_c.consult_data,
                ipd_c.consult_date,
                ipd_c.consult_time,
                ipd_c.consult_finding,
                ipd_c.consult_diagnosis,
                ipd_c.consult_recommendation,
                emr.emergency_name AS consult_emergency_name,
                ipd_c.consult_datetime_create_reply,ipd_c.consult_datetime_update_reply
                from ".DbConstant::HOSXP_DBNAME.".ipt
                left outer join ".DbConstant::HOSXP_DBNAME.".iptadm on iptadm.an=ipt.an
                left outer join ".DbConstant::HOSXP_DBNAME.".patient on patient.hn=ipt.hn
                left outer join ".DbConstant::HOSXP_DBNAME.".roomno on roomno.roomno=iptadm.roomno
                left outer join ".DbConstant::HOSXP_DBNAME.".an_stat aa on aa.an=ipt.an
                left outer join ".DbConstant::KPHIS_DBNAME.".ipd_dr_consult ipd_c on ipd_c.an = ipt.an
                left outer join ".DbConstant::HOSXP_DBNAME.".doctor d4 on d4.`code` = ipd_c.consult_doctorcode_mention
                left outer join ".DbConstant::KPHIS_DBNAME.".kphis_spclty s on s.spclty_id = ipd_c.consult_spclty
                left outer join ".DbConstant::HOSXP_DBNAME.".ward w on w.ward = ipt.ward
                left outer join ".DbConstant::KPHIS_DBNAME.".ipd_dr_consult_signature_reply idc_reply ON idc_reply.consult_id = ipd_c.consult_id
                left outer join ".DbConstant::KPHIS_DBNAME.".ipd_dr_consult_signature_request idc_request ON idc_request.consult_id = ipd_c.consult_id
                LEFT OUTER JOIN ".DbConstant::KPHIS_DBNAME.".ipd_emergency emr ON emr.emergency_id = ipd_c.consult_emergency
                where ipd_c.an = :an
                GROUP BY ipt.an,ipd_c.consult_id
                order by ipd_c.consult_date ASC, ipd_c.consult_time ASC";
        $stmt = $conn->prepare($sql);
        $stmt->execute($query_parameters);
        $first_page = true;
        $mpdf->setFooter(' (พิมพ์โดย '.$name_session.' วันที่พิมพ์ '.date('d/m/Y H:i').' ) '.'<br>ผู้ป่วย : (hn : '.$hn_row_ipt.')(an : '.$an_REQUEST.')(ชื่อ - สกุล : '.$pname_row_ipt.' '.$fname_row_ipt.' '.$lname_row_ipt.')'.' ( Page {PAGENO} of {nb} )');
        $mpdf->WriteHTML($head);
        while ($row = $stmt->fetch()){
            if($first_page){
                $first_page = false;
            }else{
                    $mpdf->AddPage('');
                    $mpdf->setFooter(' (พิมพ์โดย '.$name_session.' วันที่พิมพ์ '.date('d/m/Y H:i').' ) '.'<br>ผู้ป่วย : (hn : '.$hn_row_ipt.')(an : '.$an_REQUEST.')(ชื่อ - สกุล : '.$pname_row_ipt.' '.$fname_row_ipt.' '.$lname_row_ipt.')'.' ( Page {PAGENO} of {nb} )');
                    $mpdf->WriteHTML($head);
            }
            $an = $row['an'];
            $consult_id = $row['consult_id'];
            $consult_type = htmlspecialchars($row['consult_type']);
            $consult_doctorcode_mention_name = htmlspecialchars($row['consult_doctorcode_mention_name']);
            $bedno = htmlspecialchars($row['bedno']);
            $consult_ward_name = htmlspecialchars($row['consult_ward_name']);
            $consult_emergency_name = htmlspecialchars($row['consult_emergency_name']);
            $consult_spclty = htmlspecialchars($row['consult_spclty']);
            $consult_data = htmlspecialchars($row['consult_data']);
            $string_consult_request_name = $row['string_consult_request_name'];
            $string_consult_reply_name = $row['string_consult_reply_name'];//reply
            $consult_finding = htmlspecialchars($row['consult_finding']);//reply
            $consult_diagnosis = htmlspecialchars($row['consult_diagnosis']);//reply
            $consult_recommendation = htmlspecialchars($row['consult_recommendation']);//reply
            $consult_status = htmlspecialchars($row['consult_status']);
            $consult_date = htmlspecialchars($row['consult_date']);
            $consult_time = htmlspecialchars($row['consult_time']);
            if($consult_date != null){
                $consult_date = date("d/m/Y", strtotime($consult_date));
            }
            if($consult_time != null){
                $consult_time = substr($consult_time,0,5);
            }
            $consult_datetime_create_reply = $row['consult_datetime_create_reply'];
            $consult_datetime_create_reply_fotmat = '';
            if($consult_datetime_create_reply != null || $consult_datetime_create_reply != ""){
                $consult_datetime_create_reply_fotmat = "<B>DATE : </B>".date("d/m/Y H:i", strtotime($consult_datetime_create_reply));
            }
            $consult_datetime_update_reply = $row['consult_datetime_update_reply'];
            $consult_datetime_update_reply_fotmat = '';
            if($consult_datetime_update_reply != null || $consult_datetime_update_reply != ""){
                $consult_datetime_update_reply_fotmat = "<B>LAST UPDATE : </B>".date("d/m/Y H:i", strtotime($consult_datetime_update_reply));
            }
$Page = '
        <style>
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
        </style>
        <body>
        <h3 style="text-align:center;"><br>'.htmlspecialchars(DbConstant::HOSPITAL_NAME).'<br>CONSULTATION</h3>
        <table id="table1" width="100%" style="border-collapse: collapse;font-size:12pt;margin-top:9px;">
        <tr style="border:0px solid #000;margin: 45px;">
        <td  style="border-right:0px solid #000;padding:4px;"width="50%"><B>NAME :</B> '.$pname_row_ipt.' '.$fname_row_ipt.' '.$lname_row_ipt.'</td>
        <td  style="border-right:0px solid #000;padding:4px;"width="40%"><B>AGE :</B> '.$age_y_row_ipt.' ปี '.$age_m_row_ipt.' เดือน '.$age_d_row_ipt.' วัน </td>
        <td  style="border-right:0px solid #000;padding:4px;"width="10%"></td>
        </tr>
        <tr style="border:0px solid #000;margin: 45px;">
        <td  style="border-right:0px solid #000;padding:4px;"width="50%"><B>HN :</B> '.$hn_REQUEST.'</td>
        <td  style="border-right:0px solid #000;padding:4px;"width="40%"><B>WARD  :</B> '.$consult_ward_name.'  <B>BED : </B>'.$bedno.'</td>
        <td  style="border-right:0px solid #000;padding:4px;"width="10%"></td>
        </tr>
        <tr style="border:0px;border-bottom: 1px solid #000;margin: 45px;">
        <td  style="border-right:0px solid #000;padding:4px;"width="50%"><B>DATE :</B> '.$consult_date.' '.$consult_time.'</td>
        <td  style="border-right:0px solid #000;padding:4px;"width="40%"><B>EMERGENCY  :</B> '.$consult_emergency_name.'</td>
        <td  style="border-right:0px solid #000;padding:4px;"width="10%"></td>
        </tr>
        </table>

        <table id="table2" width="100%" style="border-collapse: collapse;font-size:12pt;margin-top:9px;">
        <tr style="border:0px solid #000;margin: 45px;">
        <td  style="border-right:0px solid #000;padding:4px;"width="100%"><B>CONSULT TO DEPARTMENT OF <BR>PURPOSE OF CONSULTATION <BR> HISTORY AND PHYSICAL EXAMINATION AND LAB.FINDING</B></td>
        </tr>
        <tr style="border:0px solid #000;margin: 45px;">
        <td colspan="4" style="border-right:0px solid #000;padding:4px;"width="100%">'.nl2br($consult_data).'</td>
        </tr>
        <tr style="border:0px;border-bottom: 1px solid #000;margin: 45px;">
        <td  style="border-right:0px solid #000;padding:4px; text-align:right;"width="100%"><BR>'.$string_consult_request_name.'<BR>PHYSICTAN ATTENDING</td>
        </tr>
        <tr style="border:0px solid #000;margin: 45px;">
        <td  style="border-right:0px solid #000;padding:4px; text-align:center;"width="100%"><BR><B>CONSULTATION REPORT</B></td>
        </tr>
        <tr style="border:0px solid #000;margin: 45px;">
        <td  style="border-right:0px solid #000;padding:4px;"width="100%">'.$consult_datetime_create_reply_fotmat.' '.$consult_datetime_update_reply_fotmat.'</td>
        </tr>
        <tr style="border:0px solid #000;margin: 45px;">
        <td  style="border-right:0px solid #000;padding:4px;"width="100%"><B>FINDING</B></td>
        </tr>
        <tr style="border:0px solid #000;margin: 45px;">
        <td colspan="4" style="border-right:0px solid #000;padding:4px;"width="100%">'.nl2br($consult_finding).'</td>
        </tr>
        <tr style="border:0px solid #000;margin: 45px;">
        <td  style="border-right:0px solid #000;padding:4px;"width="100%"><B>DIAGNOSIS</B></td>
        </tr>
        <tr style="border:0px solid #000;margin: 45px;">
        <td colspan="4" style="border-right:0px solid #000;padding:4px;"width="100%">'.nl2br($consult_diagnosis).'</td>
        </tr>
        <tr style="border:0px solid #000;margin: 45px;">
        <td  style="border-right:0px solid #000;padding:4px;"width="100%"><B>RECOMMENDATION</B></td>
        </tr>
        <tr style="border:0px solid #000;margin: 45px;">
        <td colspan="4" style="border-right:0px solid #000;padding:4px;"width="100%">'.nl2br($consult_recommendation).'</td>
        </tr>
        <tr style="border:0px solid #000;margin: 45px;">
        <td  style="border-right:0px solid #000;padding:4px;text-align:right;"width="100%"><BR>'.$string_consult_reply_name.'<BR>CONSUTANT</td>
        </tr>
        </table>
';
$Page .=  '
        </body>
        ';
$mpdf->WriteHTML($Page);
}
$mpdf->Output();
?>