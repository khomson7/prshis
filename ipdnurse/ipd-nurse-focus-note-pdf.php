<?php

ini_set('memory_limit','512M');
ini_set("pcre.backtrack_limit", "10000000");
set_time_limit(300);
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
    'margin-left' => 10,
    'margin-right' => 10,
    'margin-top' => 10,
    'margin-bottom' => 25,
]);
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
                height: 50px;

                /** Extra personal styles **/
                line-height: 35px;
        }
        </style>
        <h4 style="text-align:right;">KPH-N4-IPD</h4>
        <h3 style="text-align:center;">บันทึกความก้าวหน้าทางการพยาบาล<br>'.htmlspecialchars(DbConstant::HOSPITAL_NAME).'</h3>

        <table id="bg-table" width="100%" style="border-collapse: collapse;font-size:10pt;margin-top:8px;">
        <tr style="border:1px solid #000;margin: 45px;">
        <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="13%">&nbsp;วัน เดือน ปี<br>/เวลา</td>
        <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="9%">&nbsp;ประเภทผู้ป่วย</td>
        <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="23%">&nbsp;focus</td>
        <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="39%">&nbsp;บันทึกความก้าวหน้าทางการพยาบาล<br> A : Assessment I : Intervention E : Evaluation</td>
        <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="16%">&nbsp;ผู้บันทึก<br>/ตำแหน่ง</td>
        </tr>
';

        //----------------------รับค่า an และ select ข้อมูล จากฐานข้อมูลเพื่อค้นหา hn

        $an_REQUEST = $_REQUEST['an_fcnote_pdf'];//รับค่า an
       // $an_REQUEST = '11';

       Session::insertSystemAccessLog(json_encode(array(
        'report'=>'IPD-NURSE-FOCUS-NOTE-PDF',
       // 'action'=>'PRINT',
        'an'=>$an_REQUEST,
    ),JSON_UNESCAPED_UNICODE));

        $hn_REQUEST = KphisQueryUtils::getHnByAn($an_REQUEST);// function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา
        $search_startdate = $_REQUEST['search_startdate'];//รับค่า วันที่่เริ่มต้นปัญหา ช่องที่ 1
        $search_enddate = $_REQUEST['search_enddate'];//รับค่า วันที่่เริ่มต้นปัญหา ช่องที่ 2

       // echo $search_startdate;

        //----------------------รับค่า an และ select ข้อมูล จากฐานข้อมูลเพื่อค้นหา hn

        //----------------------select ข้อมูล จากฐานข้อมูลเพื่อค้นหา ชื่อ - สกุล

        $query_parameters_REQUEST = ['an'=>$an_REQUEST,];
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

        //----------------------select ข้อมูล focus note จากฐานข้อมูลเพื่อค้นหา วันที่,เวลา,focus,ผู้บันทึก
        $query_parameters_focusnote = [
                                        'an'=>$an_REQUEST,
                                    ];

        $focus_name_select = '';
        $sql = "SELECT f.*,k.name as user_name,k.entryposition
                from ".DbConstant::KPHIS_DBNAME.".ipd_focus_note f
                left outer join ".DbConstant::HOSXP_DBNAME.".opduser k on k.loginname = f.create_user
                WHERE f.an=:an";

                if(($search_startdate != "" || $search_startdate != null) && ($search_enddate == "" || $search_enddate == null)) {
                    $sql .= " AND f.fcnote_date = :search_startdate ";
                    $query_parameters_focusnote = [
                                                    'an'=>$an_REQUEST,
                                                    'search_startdate' =>  $search_startdate
                                                ];
                }else if(($search_startdate == "" || $search_startdate == null) && ($search_enddate != "" || $search_enddate != null)) {
                    $sql .= " AND f.fcnote_date = :search_enddate ";
                    $query_parameters_focusnote = [
                                                    'an'=>$an_REQUEST,
                                                    'search_enddate' => $search_enddate
                                                ];
                }else if(($search_startdate != "" || $search_startdate != null) && ($search_enddate != "" || $search_enddate != null)){
                    $sql .= " AND f.fcnote_date BETWEEN :search_startdate AND :search_enddate";
                    $query_parameters_focusnote = [
                                                    'an'=>$an_REQUEST,
                                                    'search_startdate' =>  $search_startdate,
                                                    'search_enddate' => $search_enddate
                                                ];
                }

        $sql .= " order by f.fcnote_date asc, f.fcnote_time asc , f.fcnote_id asc";
        $stmt = $conn->prepare($sql);
        $stmt->execute($query_parameters_focusnote);
        while ($row = $stmt->fetch()){
                $fcnote_id = $row['fcnote_id'];

                $fcnote_date = $row['fcnote_date'];
                $new_fcnote_date = date("d/m/Y", strtotime($fcnote_date));
                $fcnote_time = substr($row['fcnote_time'], 0, -3);

                $user_name = $row['user_name'];
                $entryposition = $row['entryposition'];

                if($row['fclist_id'] != null && $row['fclist_id'] != ""){

                    $fclist_id = $row['fclist_id'];
                    $query_focus_list = ['fclist_id'=>$fclist_id];
                    $sql_focus_list = "SELECT ipd_focus_list.focus_id  as focus_id_tmp,ipd_focus_list.focus_text
                                        from ".DbConstant::KPHIS_DBNAME.".ipd_focus_list
                                        WHERE ipd_focus_list.fclist_id = :fclist_id";

                    $stmt_focus_list = $conn->prepare($sql_focus_list);
                    $stmt_focus_list->execute($query_focus_list);
                    $row_focus_list = $stmt_focus_list->fetch();

                        $focus_id_tmp = $row_focus_list['focus_id_tmp'];

                        $query_tmp_focus = ['focus_id_tmp'=>$focus_id_tmp];
                        $sql_tmp_focus = "SELECT ipd_tmp_focus.focus_name
                                            from ".DbConstant::KPHIS_DBNAME.".ipd_tmp_focus
                                            WHERE ipd_tmp_focus.focus_id = :focus_id_tmp";

                        $stmt_tmp_focus = $conn->prepare($sql_tmp_focus);
                        $stmt_tmp_focus->execute($query_tmp_focus);
                        $row_tmp_focus = $stmt_tmp_focus->fetch();


                        $focus_name_select = $row_tmp_focus['focus_name']." ".$row_focus_list['focus_text']."<br>";


                    }else{
                        $focus_name_select = '';
                    }
        //----------------------select ข้อมูล focus note จากฐานข้อมูลเพื่อค้นหา วันที่,เวลา,focus,ผู้บันทึก


        //----------------------select ข้อมูล focus note จากฐานข้อมูลเพื่อค้นหา  บันทึกความก้าวหน้าทางการพยาบาล A,I,E
            $general_symptoms_all1 = '';
            $general_symptoms_all2 = '';
            $assessment_all1 = '';
            $assessment_all2 = '';
            $intvt_all = '';
            $intvt_all_str1 = '';
            $intvt_all_str2 = '';
            $intvt_all_str3 = '';
            $evalution_all1 = '';
            $evalution_all2 = '';
            $dlc_text_all1 = '';
            $dlc_text_all2 = '';
            $dlc_all = '';
            $other_all1 = '';
            $other_all2 = '';

            $fcnote_patient_type_all = ''; //ประเภทผู้ป่วย
            if($row['fcnote_patient_type'] != null && $row['fcnote_patient_type'] != ""){
                $fcnote_patient_type_all = $row['fcnote_patient_type'];
            }

            if($row['general_symptoms'] != null && trim($row['general_symptoms']) != ""){
                //$general_symptoms_all = htmlspecialchars($row['general_symptoms']).'<br>';
                $general_symptoms_all1 = str_replace("[","<font color='red'>",htmlspecialchars($row['general_symptoms']));
                $general_symptoms_all2 = str_replace("]","</font>",$general_symptoms_all1)."<br>";
            }

            if($row['fclist_id'] != null && trim($row['fclist_id']) != ""){
                if($row['assessment'] != null && trim($row['assessment']) != ""){
                    //$assessment_all =  "<B>"."A : "."</B>".htmlspecialchars($row['assessment'])."<br>";
                    $assessment_all1 = str_replace("[","<font color='red'>",htmlspecialchars($row['assessment']));
                    $assessment_all2 = "<B>"."A : "."</B>".str_replace("]","</font>",$assessment_all1)."<br>";
                }

                $query_count_item_intvt = ['fcnote_id'=>$fcnote_id];
                $sql_count_item_intvt = "SELECT COUNT(*) AS count_data_item_intvt
                                        FROM ".DbConstant::KPHIS_DBNAME.".ipd_focus_note_intvt_item
                                        WHERE fcnote_id =:fcnote_id";
                $stmt_count_item_intvt = $conn->prepare($sql_count_item_intvt);
                $stmt_count_item_intvt->execute($query_count_item_intvt);
                $row_count_item_intvt = $stmt_count_item_intvt->fetch();
                $count_data_item_intvt = $row_count_item_intvt['count_data_item_intvt'];

                if($count_data_item_intvt > "0"){

                    $intvt_text = $row['intvt_text'];
                    $query_intvt = ['fcnote_id'=>$fcnote_id];
                    $sql_intvt="SELECT item_in.intvt_id, tmp_in.intvt_name
                                FROM ".DbConstant::KPHIS_DBNAME.".ipd_focus_note_intvt_item item_in
                                LEFT JOIN ".DbConstant::KPHIS_DBNAME.".ipd_tmp_intvt tmp_in ON tmp_in.intvt_id = item_in.intvt_id
                                WHERE item_in.fcnote_id =:fcnote_id
                                ORDER BY item_in.intvt_id ASC";

                    $stmt_intvt = $conn->prepare($sql_intvt);
                    $stmt_intvt->execute($query_intvt);
                    while ($row_intvt = $stmt_intvt->fetch()){
                        if($row_intvt['intvt_id'] == 9999){
                            $intvt_all .= htmlspecialchars($intvt_text).", ";
                        }else {
                            $intvt_all .= htmlspecialchars($row_intvt['intvt_name']).", ";
                        }
                    }
                    $intvt_all_str1 = substr($intvt_all, 0, -2);//ตัดข้อความ >> สองตัวสุดท้าย คือ ", " >> (ตัว , และช่องว่าง)
                    $intvt_all_str2 = str_replace("[","<font color='red'>",$intvt_all_str1);
                    $intvt_all_str3 = "<B>"."I : "."</B>".str_replace("]","</font>",$intvt_all_str2)."<br>";
                }

            }

            if($row['evalution'] != null && trim($row['evalution']) != ""){
                //$evalution_all =   "<br>"."<B>"."E : "."</B>".$row['evalution'];
                $evalution_all1 = str_replace("[","<font color='red'>",htmlspecialchars($row['evalution']));
                $evalution_all2 = "<B>"."E : "."</B>".str_replace("]","</font>",$evalution_all1)."<br>";
            }

            if($row['dlc_text'] != null && trim($row['dlc_text']) != ""){

                $dlc_text_all1 = str_replace("[","<font color='red'>",htmlspecialchars($row['dlc_text']));
                $dlc_text_all2= str_replace("]","</font>",$dlc_text_all1)."<br>";
            }

            $query_count_item_dlc = ['fcnote_id'=>$fcnote_id];
            $sql_count_item_dlc = "SELECT COUNT(*) AS count_data_item_dlc
                                    FROM ".DbConstant::KPHIS_DBNAME.".ipd_focus_note_dlc_item
                                    WHERE fcnote_id =:fcnote_id";
            $stmt_count_item_dlc = $conn->prepare($sql_count_item_dlc);
            $stmt_count_item_dlc->execute($query_count_item_dlc);
            $row_count_item_dlc = $stmt_count_item_dlc->fetch();
            $count_data_item_dlc = $row_count_item_dlc['count_data_item_dlc'];

            if($count_data_item_dlc > "0"){
                $query_dlc = ['fcnote_id'=>$fcnote_id];
                $sql_dlc = "SELECT item_d.dlc_id, tmp_d.dlc_name
                            FROM ".DbConstant::KPHIS_DBNAME.".ipd_focus_note_dlc_item item_d
                            LEFT JOIN ".DbConstant::KPHIS_DBNAME.".ipd_tmp_dlc tmp_d ON tmp_d.dlc_id = item_d.dlc_id
                            WHERE item_d.fcnote_id =:fcnote_id
                            ORDER BY item_d.dlc_id ASC";

                $stmt_dlc = $conn->prepare($sql_dlc);
                $stmt_dlc->execute($query_dlc);
                while ($row_dlc = $stmt_dlc->fetch()){
                    if($row_dlc['dlc_id'] == 999){
                        $dlc_all .=  "- ".$row_dlc['dlc_name']." "."<U>".$dlc_text."</U>"."<br>";
                    }else {
                        $dlc_all .= "- ".$row_dlc['dlc_name']."<br>";
                    }
                }
            }

            if($row['other'] != null && trim($row['other']) != ""){
                //$other_all = $row['other']."<br>";
                $other_all1 = str_replace("[","<font color='red'>",htmlspecialchars($row['other']));
                $other_all2= str_replace("]","</font>",$other_all1)."<br>";
            }

        //----------------------select ข้อมูล focus note จากฐานข้อมูลเพื่อค้นหา  บันทึกความก้าวหน้าทางการพยาบาล A,I,E


$head .= '

        <tr style="border:1px solid #000;margin: 45px;">
        <td  style="vertical-align: top; border-right:0.5px solid #000;padding:4px;">'.$new_fcnote_date.'<br>'.$fcnote_time.'</td>
        <td  style="vertical-align: top; text-align:center; border-right:0.5px solid #000;padding:4px;">'.$fcnote_patient_type_all.'</td>
        <td  style="vertical-align: top; border-right:0.5px solid #000;padding:4px;">'.$focus_name_select.'</td>
        <td  style="vertical-align: top; border-right:0.5px solid #000;padding:4px;">'.$general_symptoms_all2.$assessment_all2.$intvt_all_str3.$evalution_all2.$dlc_text_all2.$dlc_all.$other_all2.'</td>
        <td  style="vertical-align: top; border-right:0.5px solid #000;padding:4px;">'.$user_name.'<br>'.$entryposition.'</td>
        </tr>
';
}

$head .=  '
        </table>
';
$html_header='
<table id="bg-table_header" width="100%" style="border-collapse: collapse;font-size:10pt;margin-top:-5px;">
<tr style="border:1px solid #000;margin: 45px;">
<td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="13%">&nbsp;วัน เดือน ปี<br>/เวลา</td>
<td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="9%">&nbsp;ประเภทผู้ป่วย</td>
<td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="23%">&nbsp;focus</td>
<td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="39%">&nbsp;บันทึกความก้าวหน้าทางการพยาบาล<br> A : Assessment I : Intervention E : Evaluation</td>
<td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="16%">&nbsp;ผู้บันทึก<br>/ตำแหน่ง</td>
</tr>
</table>';
$mpdf->setAutoTopMargin='stretch';
$mpdf->setHTMLHeader($html_header);
$mpdf->WriteHTML($head);
$mpdf->Output();
?>