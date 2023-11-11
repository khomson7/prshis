<?php
require_once '../include/Session.php';

$login = empty($_REQUEST['loginname']) ? null : $_REQUEST['loginname'];
$loginname = $_SESSION['loginname'];
$values = ['loginname' => $loginname];
if (!$loginname) {
    session_start();
    session_destroy();
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require_once '../include/DbUtils.php';
require_once '../include/Session.php';
require_once '../include/KphisQueryUtils.php';

date_default_timezone_set('asia/bangkok');
$conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
Session::checkLoginSessionAndShowMessage(); //เช็ค session
if(!(
        // && Session::checkPermission('IPD_NURSE_MAIN_PROGRAM','ACCESS')
        // && Session::checkPermission('IO','ADD')
        // && Session::checkPermission('IO','EDIT')
        Session::checkPermission('IO','VIEW')
        // && Session::checkPermission('IO','REMOVE')
        )){
        return;
}
$an = $_REQUEST['an_io_pdf'];//รับค่า an
$io_date_start = null;
$io_date_end = null;
$io_date_start = $_REQUEST['io_date_start'];//รับค่า วันที่เริ่มต้น
$io_date_end = $_REQUEST['io_date_end'];//รับค่า วันที่สิ้นสุด
$hn = KphisQueryUtils::getHnByAn($an);// function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา
$name_session = $_SESSION['name'];


Session::insertSystemAccessLog(json_encode(array(
        'report'=>'IPD-VITAL-SIGN-IO-PDF',
       // 'action'=>'PRINT',
        'an'=>$an_REQUEST,
    ),JSON_UNESCAPED_UNICODE));




//----------------------select ข้อมูล จากฐานข้อมูลเพื่อค้นหา ชื่อ - สกุล
$query_parameters_REQUEST = ['an'=>$an];
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
//----------------------select ข้อมูล จากฐานข้อมูลเพื่อค้นหา ชื่อ - สกุล

$mpdf = new \Mpdf\Mpdf();
$head = '
        <style>
        body{
                font-family: "Garuda";//เรียกใช้font Garuda สำหรับแสดงผล ภาษาไทย
                height: 400px;
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
        <h4 style="text-align:right;">KPH-N8-I/O</h4>
        <h4 style="text-align:center;">'.htmlspecialchars(DbConstant::HOSPITAL_NAME).'<br>ใบ Record Intake - Output for one day</h4>

        <table id="bg-table" width="100%" style="border-collapse: collapse;font-size:10pt;margin-top:8px;">
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="8%" rowspan="2">วันที่</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="5%" rowspan="2">เวลา</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="37%" colspan="6">Parenteral fluid</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="30%" colspan="5">Oral fluid</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="15%" colspan="3">Output</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="5%" rowspan="2"></td>
        </tr>
        <tr style="border:1px solid #000;margin: 45px;">
            <td style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="5%">Type</td>
            <td style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="12%">Name</td>
            <td style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="5%">Amount</td>
            <td style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="5%">Absorb</td>
            <td style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="5%">ยกไป</td>
            <td style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="5%">Remark</td>
            <td style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="6%">Name</td>
            <td style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="6%">Amount</td>
            <td style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="6%">Absorb</td>
            <td style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="6%">ยกไป</td>
            <td style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="6%">Remark</td>
            <td style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="5%">Type</td>
            <td style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="5%">Amount</td>
            <td style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="5%">Remark</td>
        </tr>
';
        $query_parameters_group_date = ['an'=>$an];
        $sql_group_date = " SELECT io.io_date FROM ".DbConstant::KPHIS_DBNAME.".ipd_io io WHERE io.an = :an ";

        if(($io_date_start != "" || $io_date_start != null) && ($io_date_end == "" || $io_date_end == null)){
                $sql_group_date .= " AND io.io_date = :io_date_start ";
                $query_parameters_group_date =  ['an'=>$an,
                                                 'io_date_start'=>$io_date_start
                                                ];

        }elseif(($io_date_start == "" || $io_date_start == null) && ($io_date_end != "" || $io_date_end != null)){
                $sql_group_date .= " AND io.io_date = :io_date_end ";
                $query_parameters_group_date =  ['an'=>$an,
                                                 'io_date_end'=>$io_date_end
                                                ];

        }elseif(($io_date_start != "" || $io_date_start != null) && ($io_date_end != "" || $io_date_end != null)){
                $sql_group_date .= " AND io.io_date BETWEEN :io_date_start AND :io_date_end ";
                $query_parameters_group_date =  ['an'=>$an,
                                                 'io_date_start'=>$io_date_start,
                                                 'io_date_end'=>$io_date_end
                                                ];
        }

        $sql_group_date .= " GROUP BY io.io_date
                             ORDER BY io.io_date ASC";
        $stmt_group_date = $conn->prepare($sql_group_date);
        $stmt_group_date->execute($query_parameters_group_date);
        $first_page = true;
        $mpdf->AddPage('L','A4');
        $mpdf->setFooter(' (พิมพ์โดย '.$name_session.' วันที่พิมพ์ '.date('d/m/Y H:i').' ) '.'<br>ผู้ป่วย : (hn : '.$hn_row_ipt.')(an : '.$an.')(ชื่อ - สกุล : '.$pname_row_ipt.' '.$fname_row_ipt.' '.$lname_row_ipt.')'.' ( Page {PAGENO} of {nb} )');
        $mpdf->WriteHTML($head);
        while ($row_group_date = $stmt_group_date->fetch()){
                if($first_page){
                        $first_page = false;
                }else{
                        $mpdf->AddPage('L','A4');
                        $mpdf->setFooter(' (พิมพ์โดย '.$name_session.' วันที่พิมพ์ '.date('d/m/Y H:i').' ) '.'<br>ผู้ป่วย : (hn : '.$hn_row_ipt.')(an : '.$an.')(ชื่อ - สกุล : '.$pname_row_ipt.' '.$fname_row_ipt.' '.$lname_row_ipt.')'.' ( Page {PAGENO} of {nb} )');
                        $mpdf->WriteHTML($head);
                }
                $Page = '';
                $sum24_io_parenteral_absorb = 0;
                $sum24_io_oral_absorb = 0;
                $sum24_io_output_amount = 0;
                $sum24_parenteral_oral_absorb = 0;

                $row_group_date_io_date =  $row_group_date['io_date'];
                $query_parameters = ['an'=>$an,
                                     'row_group_date_io_date'=>$row_group_date_io_date
                                    ];
                $sql_shift="SELECT io.an, io.io_date,
                        SUM(io.io_parenteral_absorb) AS sum8_io_parenteral_absorb,
                        SUM(io.io_oral_absorb) AS sum8_io_oral_absorb,
                        SUM(io.io_output_amount) AS sum8_io_output_amount,
                        CASE
                        WHEN io.io_time BETWEEN '00:00:00.000' AND '07:59:59' THEN 'ดึก'
                        WHEN io.io_time BETWEEN '08:00:00.000' AND '15:59:59' THEN 'เช้า'
                        WHEN io.io_time BETWEEN '16:00:00.000' AND '23:59:59' THEN 'บ่าย'
                        ELSE null
                        END AS shift
                        FROM ".DbConstant::KPHIS_DBNAME.".ipd_io io
                        WHERE io.an = :an AND io.io_date = :row_group_date_io_date
                        GROUP BY io.io_date,shift
                        ORDER BY io.io_date,io.io_time ASC";

                $stmt_shift = $conn->prepare($sql_shift);
                $stmt_shift->execute($query_parameters);
                while ($row_shift = $stmt_shift->fetch()){

                        $sum8_io_parenteral_absorb = $row_shift['sum8_io_parenteral_absorb'];
                        $sum8_io_oral_absorb = $row_shift['sum8_io_oral_absorb'];
                        $sum8_io_output_amount = $row_shift['sum8_io_output_amount'];

                        $sql="SELECT io.*,kphuser.name as user_name,kphuser.entryposition
                        FROM ".DbConstant::KPHIS_DBNAME.".ipd_io io
                        LEFT JOIN ".DbConstant::HOSXP_DBNAME.".opduser kphuser on kphuser.loginname = io.update_user
                        WHERE io.an = :an AND io.io_date = :row_group_date_io_date ";
                        if($row_shift['shift'] == 'ดึก'){
                        $sql.= " AND io.io_time BETWEEN '00:00:00.000' AND '07:59:59' ";
                        } else if($row_shift['shift'] == 'เช้า'){
                        $sql.= " AND io.io_time BETWEEN '08:00:00.000' AND '15:59:59' ";
                        } else if($row_shift['shift'] == 'บ่าย'){
                        $sql.= " AND io.io_time BETWEEN '16:00:00.000' AND '23:59:59' ";
                        }
                        $sql.= " ORDER BY io.io_time ASC ";

                        $stmt = $conn->prepare($sql);
                        $stmt->execute($query_parameters);
                        $rowCount = 0;

                        while ($row = $stmt->fetch()){
                                $rowCount++;
                                $io_date = $row['io_date'];
                                $io_time = $row['io_time'];

                                $io_parenteral_type = $row['io_parenteral_type'];
                                $io_parenteral_name = $row['io_parenteral_name'];
                                $io_parenteral_amount = $row['io_parenteral_amount'];
                                $io_parenteral_absorb = $row['io_parenteral_absorb'];
                                $io_parenteral_carry_forward = $row['io_parenteral_carry_forward'];
                                $io_parenteral_remark = $row['io_parenteral_remark'];

                                $io_oral_name = $row['io_oral_name'];
                                $io_oral_amount = $row['io_oral_amount'];
                                $io_oral_absorb = $row['io_oral_absorb'];
                                $io_oral_carry_forward = $row['io_oral_carry_forward'];
                                $io_oral_remark = $row['io_oral_remark'];

                                $io_output_type = $row['io_output_type'];
                                $io_output_amount = $row['io_output_amount'];
                                $io_output_remark = $row['io_output_remark'];

                                $io_update_user_fullnam = $row['user_name'];

                                $sum24_io_parenteral_absorb   = $sum24_io_parenteral_absorb + $io_parenteral_absorb;//ผลรวม 24 ชั่วโมง parenteral >> absorb
                                $sum24_io_oral_absorb         = $sum24_io_oral_absorb + $io_oral_absorb;//ผลรวม 24 ชั่วโมง oral >> absorb
                                $sum24_io_output_amount       = $sum24_io_output_amount + $io_output_amount;//ผลรวม 24 ชั่วโมง output >> amount
                                $sum24_parenteral_oral_absorb = $sum24_io_parenteral_absorb + $sum24_io_oral_absorb;//ผลรวม 24 ชั่วโมง parenteral>> absorb + oral >> absorb

                                $Page .= '

                                        <tr style="border:1px solid #000;margin: 45px;">
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="8%">'.date("d/m/Y", strtotime($io_date)).'</td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="5%">'.substr($io_time, 0, -3).'</td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="5%">'.htmlspecialchars($io_parenteral_type).'</td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="12%">'.htmlspecialchars($io_parenteral_name).'</td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="5%">'.htmlspecialchars($io_parenteral_amount).'</td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="5%">'.htmlspecialchars($io_parenteral_absorb).'</td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="5%">'.htmlspecialchars($io_parenteral_carry_forward).'</td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="5%">'.htmlspecialchars($io_parenteral_remark).'</td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="6%">'.htmlspecialchars($io_oral_name).'</td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="6%">'.htmlspecialchars($io_oral_amount).'</td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="6%">'.htmlspecialchars($io_oral_absorb).'</td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="6%">'.htmlspecialchars($io_oral_carry_forward).'</td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="6%">'.htmlspecialchars($io_oral_remark).'</td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="5%">'.htmlspecialchars($io_output_type).'</td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="5%">'.htmlspecialchars($io_output_amount).'</td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="5%">'.htmlspecialchars($io_output_remark).'</td>
                                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px; font-size:7pt;"width="5%">'.htmlspecialchars($io_update_user_fullnam).'</td>
                                        </tr>
                                ';
                        }
                        if($rowCount > 0){
                                $Page .= '
                                        <tr style="border:1px solid #000;margin: 45px;">
                                                <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" colspan="2" width="13%">(เวร'.htmlspecialchars($row_shift['shift']).')</td>
                                                <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" colspan="6" width="37%">'.htmlspecialchars($sum8_io_parenteral_absorb).' c.c.</td>
                                                <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" colspan="5" width="30%">'.htmlspecialchars($sum8_io_oral_absorb).' c.c.</td>
                                                <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" colspan="3" width="15%">'.htmlspecialchars($sum8_io_output_amount).' c.c.</td>
                                                <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="5%"></td>
                                        </tr>
                                        ';
                        }
                }
                $sql_sum24="SELECT
                    SUM(io.io_parenteral_absorb) AS sum24_io_parenteral_absorb,
                    SUM(io.io_oral_absorb) AS sum24_io_oral_absorb,
                    SUM(io.io_output_amount) AS sum24_io_output_amount,
                    SUM(IFNULL(io.io_parenteral_absorb,0)+IFNULL(io.io_oral_absorb,0)) AS sum24_parenteral_oral_absorb
                    FROM ".DbConstant::KPHIS_DBNAME.".ipd_io io
                    WHERE io.an = :an AND io.io_date = :row_group_date_io_date ";

                        $stmt_sum24 = $conn->prepare($sql_sum24);
                        $stmt_sum24->execute($query_parameters);
                        while ($row_sum24 = $stmt_sum24->fetch()){
                        $sum24_io_parenteral_absorb = $row_sum24['sum24_io_parenteral_absorb'];
                        $sum24_io_oral_absorb = $row_sum24['sum24_io_oral_absorb'];
                        $sum24_io_output_amount = $row_sum24['sum24_io_output_amount'];
                        $sum24_parenteral_oral_absorb = $row_sum24['sum24_parenteral_oral_absorb'];
                $Page .= '
                <tr style="border:1px solid #000;margin: 45px;">
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" colspan="2" width="13%">(24 ชั่วโมง)</td>
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" colspan="6" width="37%">'.htmlspecialchars($sum24_io_parenteral_absorb).' c.c.</td>
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" colspan="5" width="30%">'.htmlspecialchars($sum24_io_oral_absorb).' c.c.</td>
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" colspan="3" width="15%">'.htmlspecialchars($sum24_io_output_amount).' c.c.</td>
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="5%"></td>
                </tr>
                <tr style="border:1px solid #000;margin: 45px;">
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" colspan="2" width="13%">(24 ชั่วโมง)</td>
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" colspan="11" width="67%"><font color="red">'.htmlspecialchars($sum24_parenteral_oral_absorb).' c.c.</font></td>
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" colspan="3" width="15%"><font color="red">'.htmlspecialchars($sum24_io_output_amount).' c.c.</font></td>
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="5%"></td>
                </tr>
                ';
                }
                $Page .=  '
                           </table>
                           </body>
                          ';
                $mpdf->WriteHTML($Page);
        }

$mpdf->Output();
?>