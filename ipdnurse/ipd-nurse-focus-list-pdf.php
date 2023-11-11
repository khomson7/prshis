<?php
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
Session::checkPermissionAndShowMessage('DOCUMENT', 'PRINT');

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';

date_default_timezone_set('asia/bangkok');
$conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล


$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
$mpdf->AddPageByArray([
        'margin-left' => 12,
        'margin-right' => 12,
        'margin-top' => 12,
        'margin-bottom' => 25,]);
$head = '
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

        <h4 style="text-align:right;">KPH-N3-FL</h4>
        <h3 style="text-align:center;">Focus list<br>'.htmlspecialchars(DbConstant::HOSPITAL_NAME).'</h3>

        <table id="bg-table" width="100%" style="border-collapse: collapse;font-size:10pt;margin-top:8px;">
        <tr style="border:1px solid #000;margin: 45px;">
        <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="5%">&nbsp;ลำดับ</td>
        <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="25%">&nbsp;focus</td>
        <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="40%">&nbsp;เป้าหมาย/ผลลัพธ์ที่ต้องการ</td>
        <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="15%">&nbsp;วันที่เริ่มต้นปัญหา</td>
        <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="15%">&nbsp;วันที่สิ้นสุดปัญหา</td>
        </tr>
';
        $an_REQUEST = $_REQUEST['an'];//รับค่า an
        $query_parameters_REQUEST = ['an'=>$an_REQUEST,];
        $search_startdate = $_REQUEST['search_startdate'];//รับค่า วันที่่เริ่มต้นปัญหา ช่องที่ 1
        $search_enddate = $_REQUEST['search_enddate'];//รับค่า วันที่่เริ่มต้นปัญหา ช่องที่ 2
        $search_status = $_REQUEST['search_status'];//รับค่า สถานะ

        Session::insertSystemAccessLog(json_encode(array(
                'report'=>'IPD-NURSE-FOCUS-LIST-PDF',
               // 'action'=>'PRINT',
                'an'=>$an_REQUEST,
            ),JSON_UNESCAPED_UNICODE));

        
   
        //----------------------รับค่า an และ select ข้อมูล จากฐานข้อมูลเพื่อค้นหา hn

        $hn_REQUEST = KphisQueryUtils::getHnByAn($an_REQUEST);// function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา

        //----------------------รับค่า an และ select ข้อมูล จากฐานข้อมูลเพื่อค้นหา hn

        $query_parameters = [
                                'an'=>$an_REQUEST
                        ];
        $sql = "SELECT fc_l.*, fc_l.create_user as create_user_fclist, t_fc.focus_name,
                dchtype.`name` as dchtype_name, dchtype.dchtype, ipt.dchdate as dchdate
                FROM ".DbConstant::KPHIS_DBNAME.".ipd_focus_list fc_l
                LEFT JOIN ".DbConstant::KPHIS_DBNAME.".ipd_tmp_focus t_fc ON t_fc.focus_id = fc_l.focus_id
                LEFT JOIN ".DbConstant::HOSXP_DBNAME.".ipt ipt ON ipt.an = fc_l.an
                LEFT JOIN ".DbConstant::HOSXP_DBNAME.".dchtype dchtype ON dchtype.dchtype=ipt.dchtype
                WHERE fc_l.an = :an";

                if(($search_startdate != "" || $search_startdate != null) && ($search_enddate == "" || $search_enddate == null)) {
                        $sql .= " AND fc_l.fclist_stdate = :search_startdate ";
                        $query_parameters = [
                                                'an'=>$an_REQUEST,
                                                'search_startdate' => $search_startdate
                                        ];
                }else if(($search_startdate == "" || $search_startdate == null) && ($search_enddate != "" || $search_enddate != null)) {
                        $sql .= " AND fc_l.fclist_stdate = :search_enddate ";
                        $query_parameters = [
                                                'an'=>$an_REQUEST,
                                                'search_enddate' => $search_enddate
                                        ];
                }else if(($search_startdate != "" || $search_startdate != null) && ($search_enddate != "" || $search_enddate != null)) {
                        $sql .= " AND fc_l.fclist_stdate BETWEEN :search_startdate AND :search_enddate ";
                        $query_parameters = [
                                                'an'=>$an_REQUEST,
                                                'search_startdate' => $search_startdate,
                                                'search_enddate' => $search_enddate
                                        ];
                }

                if(!empty($search_status)) {
                        $sql .= " AND fc_l.fclist_status = :search_status ";
                        $query_parameters['search_status'] = $search_status;
                }

        $sql .= " ORDER BY fc_l.fclist_stdate , fc_l.fclist_sttime ASC";
        $stmt = $conn->prepare($sql);
        $stmt->execute($query_parameters);
        $rowCount = 0;
        while ($row = $stmt->fetch()){
                $rowCount++;
                $fclist_id = $row['fclist_id'];
                $focus_name = $row['focus_name'];
                $focus_text = $row['focus_text'];
                $goal_text = $row['goal_text']; //ในกรณีที่ผู้ใช้งานเลือก "อื่นๆ >> และใส่ข้อมูลในช่อง textarea"
                $dchtype = $row['dchtype'];
                if($row['fclist_stdate'] != null){
                        $startdate = $row['fclist_stdate'];
                        $new_startdate = date("d/m/Y", strtotime($startdate));
                }else{
                        $new_startdate = '';
                }

                $new_enddate = '';
                if(($row['dchdate'] != null || $row['dchdate'] !="") && $row['fclist_status'] == "1"){
                   //เช็ควันที่ dischart ถ้าผู้ป่วย dischartแล้ว แต่สถานะ >> 'ปัญหายังคงอยู่'
                   //ให้ดึงข้อมูลการ dischart จาก Hosxp มาแสดง >> วันที่ dischart, type ที่ dischart
                        if($dchtype == '01'){
                                $dchdate = htmlspecialchars($row['dchdate']);
                                $new_enddate = date("d/m/Y", strtotime($dchdate)).'<br>'.htmlspecialchars($row['dchtype_name']);
                        }else{
                                $dchdate = htmlspecialchars($row['dchdate']);
                                $new_enddate = "<font color='red'>".date("d/m/Y", strtotime($dchdate)).'<br>'.htmlspecialchars($row['dchtype_name'])."</font>";
                        }

                }else{
                        if($row['fclist_enddate'] != null){
                                $enddate = htmlspecialchars($row['fclist_enddate']);
                                $new_enddate = date("d/m/Y", strtotime($enddate));
                        }else{
                                $new_enddate = '';
                        }
                }

                $query_goal = ['fclist_id'=>$fclist_id];
                $sql_goal ="SELECT item_g.goal_id, tmp_g.goal_name
                            FROM ".DbConstant::KPHIS_DBNAME.".ipd_focus_list_goal_item item_g
                            LEFT JOIN ".DbConstant::KPHIS_DBNAME.".ipd_tmp_goal tmp_g ON tmp_g.goal_id = item_g.goal_id
                            WHERE item_g.fclist_id =:fclist_id
                            ORDER BY item_g.goal_id ASC";

                $all_goal = "";
                $stmt_goal = $conn->prepare($sql_goal);
                $stmt_goal->execute($query_goal);
                while ($row_goal = $stmt_goal->fetch()){
                        if($row_goal['goal_id'] == 999){
                                $all_goal .= "- ".htmlspecialchars($row_goal['goal_name'])." : ".htmlspecialchars($goal_text).'<br>';
                        }else {
                                $all_goal .= "- ".htmlspecialchars($row_goal['goal_name']).'<br>';
                        }
                }


                //----------------------select ข้อมูล จากฐานข้อมูลเพื่อค้นหา ชื่อ - สกุล
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
                }
                $mpdf->setFooter(' (พิมพ์โดย '.$_SESSION['name'].' วันที่พิมพ์ '.date('d/m/Y H:i').' ) '.'<br>ผู้ป่วย : (hn : '.$hn_row_ipt.')(an : '.$an_REQUEST.')(ชื่อ - สกุล : '.$pname_row_ipt.' '.$fname_row_ipt.' '.$lname_row_ipt.')'.' ( Page {PAGENO} of {nb} )');
                $mpdf->WriteHTML('');
                //----------------------select ข้อมูล จากฐานข้อมูลเพื่อค้นหา ชื่อ - สกุล

$head .= '

        <tr style="border:1px solid #000;margin: 45px;">
        <td  style="vertical-align: top; text-align:center; border-right:0.5px solid #000;padding:4px;"width="5%">'.htmlspecialchars($rowCount).'</td>
        <td  style="vertical-align: top; border-right:0.5px solid #000;padding:4px;"width="25%">'.htmlspecialchars($focus_name).' '.htmlspecialchars($focus_text).'</td>
        <td  style="vertical-align: top; border-right:0.5px solid #000;padding:4px;"width="40%">'.$all_goal.'</td>
        <td  style="vertical-align: top; border-right:0.5px solid #000;padding:4px;"width="15%">'.htmlspecialchars($new_startdate).'</td>
        <td  style="vertical-align: top; border-right:0.5px solid #000;padding:4px;"width="15%">'.$new_enddate.'</td>
        </tr>
';
}

$head .=  '
        </table>
';
$html_header='
<table id="bg-table" width="100%" style="border-collapse: collapse;font-size:10pt;margin-top:11px;">
<tr style="border:1px solid #000;margin: 45px;">
<td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="5%">&nbsp;ลำดับ</td>
<td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="25%">&nbsp;focus</td>
<td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="40%">&nbsp;เป้าหมาย/ผลลัพธ์ที่ต้องการ</td>
<td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="15%">&nbsp;วันที่เริ่มต้นปัญหา</td>
<td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="15%">&nbsp;วันที่สิ้นสุดปัญหา</td>
</tr>
</table>';
$mpdf->setAutoTopMargin='stretch';
$mpdf->setHTMLHeader($html_header);
$mpdf->WriteHTML($head);
$mpdf->Output();
?>