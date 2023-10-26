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

Session::checkLoginSessionAndShowMessage();
// Session::checkPermissionAndShowMessage('KPHIS_ACCESS_IPD_DOCTOR_ORDER_PROGRAM');
// Session::checkPermissionAndShowMessage('IPD_ORDER','VIEW');
if(!Session::checkPermission('IPD_NURSE_INDEX','VIEW')){
    Session::responsePermissionErrorForJsonRequest(null);
    exit;
}

require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';
$conn = DbUtils::get_hosxp_connection();

//check for require field
if(empty($_REQUEST['an'])) {
	exit;
}

$plan_id = empty($_REQUEST['plan_id']) ? null : $_REQUEST['plan_id'];
$action_id = empty($_REQUEST['action_id']) ? null : $_REQUEST['action_id'];
$order_item_id = empty($_REQUEST['order_item_id']) ? null : $_REQUEST['order_item_id'];
$an = empty($_REQUEST['an']) ? null : $_REQUEST['an'];
$plan_date = empty($_REQUEST['plan_date']) ? null : $_REQUEST['plan_date'];
$start_plan_date = empty($_REQUEST['start_plan_date']) ? null : $_REQUEST['start_plan_date'];
$end_plan_date = empty($_REQUEST['end_plan_date']) ? null : $_REQUEST['end_plan_date'];
$plan_sch_type = empty($_REQUEST['plan_sch_type']) ? null : $_REQUEST['plan_sch_type'];

Session::insertSystemAccessLog(json_encode(array(
    'report'=>'IPD-NURSE-INDEX-PLAN-PDF',
   // 'action'=>'PRINT',
    'an'=>$an,
),JSON_UNESCAPED_UNICODE));

try {
    $sql = "SELECT
                index_action.action_id,
                index_action.plan_id,
                index_action.an,
                index_action.action_result,
                index_action.action_remark,
                index_action.action_date,
                index_action.action_time,
                index_action.action_report_back,
                index_action.action_blood_had,
                index_action.action_person_1,
                index_action.action_person_2,
                index_plan.order_item_id,
                index_plan.plan_detail,
                index_plan.plan_date,
                index_plan.plan_time,
                index_plan.plan_sch_type,
                order_item.order_id,
                -- order_item.an,
                order_item.order_item_detail,
                order_item.stat,
                order_item.icode,
                order_item.off_order_item_id,
                off_order_item.order_item_type as off_order_item_type,
                off_order_item.order_item_detail as off_order_item_detail,
                order_item.order_item_type,
                order_item.create_user,
                order_item.create_datetime,
                order_item.update_user,
                order_item.update_datetime,
                order_item.version,
                ipd_order.order_date,
                ipd_order.order_time,
                ipd_order.order_doctor,
                ipd_order.order_type,
                ipd_order.order_owner_type,
                ipd_order.order_confirm,
                concat(di.`name`, ' ', di.strength, ' ',di.units) as med_name, di.displaycolor,
                concat(off_di.`name`, ' ', off_di.strength, ' ',off_di.units) as off_med_name, off_di.displaycolor as off_displaycolor,
                action_person_1_doctor.`name` as action_person_1_doctor_name,
                action_person_2_doctor.`name` as action_person_2_doctor_name,
                /* off_by_order_item.order_item_id as off_by_order_item_id */
                (select off_by_order_item.order_item_id
                from ".DbConstant::KPHIS_DBNAME.".ipd_order_item off_by_order_item
                join ".DbConstant::KPHIS_DBNAME.".ipd_order off_by_order on off_by_order_item.order_id = off_by_order.order_id and off_by_order.an = off_by_order.an
                where off_by_order.order_confirm = 'Y'
                and off_by_order.an = order_item.an
                and off_by_order_item.off_order_item_id = order_item.order_item_id
                limit 1
                ) as off_by_order_item_id
            FROM ipd_nurse_index_action AS index_action
            INNER JOIN ipd_nurse_index_plan AS index_plan on index_action.plan_id = index_plan.plan_id
            LEFT JOIN ipd_order_item AS order_item on order_item.order_item_id = index_plan.order_item_id
            LEFT JOIN ipd_order ON order_item.order_id = ipd_order.order_id
            LEFT JOIN ipd_order_item AS off_order_item ON order_item.off_order_item_id = off_order_item.order_item_id
            left outer join ".DbConstant::HOSXP_DBNAME.".drugitems di on di.icode = order_item.icode
            left outer join ".DbConstant::HOSXP_DBNAME.".drugitems off_di on off_di.icode = off_order_item.icode
            left outer join ".DbConstant::HOSXP_DBNAME.".doctor action_person_1_doctor on action_person_1_doctor.code = index_action.action_person_1
            left outer join ".DbConstant::HOSXP_DBNAME.".doctor action_person_2_doctor on action_person_2_doctor.code = index_action.action_person_2
            /* left outer join (select off_by_order_item.* from ".DbConstant::KPHIS_DBNAME.".ipd_order_item off_by_order_item
                            join ".DbConstant::KPHIS_DBNAME.".ipd_order off_by_order on off_by_order_item.order_id = off_by_order.order_id
                            and off_by_order.order_confirm = 'Y'
                            ) as off_by_order_item on off_by_order_item.off_order_item_id = order_item.order_item_id */
            WHERE 1=1 ";
    if($plan_id != null){
        $sql .= " AND index_plan.plan_id=:plan_id ";
        $parameters['plan_id'] = $plan_id;
    }
    if($action_id != null){
        $sql .= " AND index_action.action_id=:action_id ";
        $parameters['action_id'] = $action_id;
    }
    if($order_item_id != null && $order_item_id != '') {
        $sql .= " AND index_plan.order_item_id=:order_item_id ";
        $parameters['order_item_id'] = $order_item_id;
    }
    if($an != null && $an != '') {
        $sql .= " AND index_plan.an=:an ";
        $parameters['an'] = $an;
    }
    if($plan_date != null && $plan_date != '') {
        $sql .= " AND index_plan.plan_date=:plan_date ";
        $parameters['plan_date'] = $plan_date;
    }
    if($start_plan_date != null){
        $sql .= " AND index_plan.plan_date>=:start_plan_date ";
        $parameters['start_plan_date'] = $start_plan_date;
    }
    if($end_plan_date != null){
        $sql .= " AND index_plan.plan_date<=:end_plan_date ";
        $parameters['end_plan_date'] = $end_plan_date;
    }
    if($plan_sch_type != null && $plan_sch_type != '') {
        $sql .= " AND index_plan.plan_sch_type=:plan_sch_type ";
        $parameters['plan_sch_type'] = $plan_sch_type;
    }
    $sql .= " having (off_by_order_item_id is null or (off_by_order_item_id is not null and action_date is not null)) ";
    $sql .= " order by
                index_plan.plan_date, index_plan.plan_time,
                ipd_order.order_date, ipd_order.order_time, order_item.order_item_id,
                index_action.action_date, index_action.action_time ";

    $stmt = $conn->prepare($sql);
    $stmt->execute($parameters);

    date_default_timezone_set('asia/bangkok');

    $mpdf = new \Mpdf\Mpdf();
    // $mpdf->AddPage('L');

    $mpdf->AddPageByArray(array(
        'orientation' => 'L',
        // 'mgl' => '50',
        // 'mgr' => '50',
        // 'mgt' => '50',
        'mgb' => '25',
        // 'mgh' => '10',
        // 'mgf' => '10',
    ));

    // $mpdf->AddPageByArray([
    //     'margin-left' => 8,
    //     'margin-right' => 8,
    //     'margin-top' => 8,
    //     'margin-bottom' => 25,
    // ]);
    $output = '
        <style>
        @page *{
            margin-bottom: 3.54cm;
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
        </style>
        <h3 style="text-align:center;">'.htmlspecialchars(DbConstant::HOSPITAL_NAME).'<br/>Nurse Planning</h3>
        <table id="bg-table" width="100%" style="border-collapse: collapse;font-size:8pt;margin-top:8px;">

        <tr style="border:1px solid #000;margin: 45px;">
            <th scope="col" style="border:0.5px solid #000;padding:4px;" valign="top">Order</th>
            <th scope="col" style="border:0.5px solid #000;padding:4px;" valign="top">Plan Time</th>
            <th scope="col" style="border:0.5px solid #000;padding:4px;" valign="top">Plan Detail</th>
            <th scope="col" style="border:0.5px solid #000;padding:4px;" valign="top">Action Time</th>
            <th scope="col" style="border:0.5px solid #000;padding:4px;" valign="top">ลงชื่อ</th>
            <th scope="col" style="border:0.5px solid #000;padding:4px;" valign="top">Result</th>
            <th scope="col" style="border:0.5px solid #000;padding:4px;" valign="top">Remark</th>
        </tr>';

    $PLAN_SCH_TYPE_NAMES = array(
        'time' => 'แบบระบุเวลา',
        'date' => 'แบบไม่ระบุเวลา',
        'stat' => 'Stat',
    );

    $ORDER_TYPE_NAMES = array(
        'oneday' => 'One Day Order',
        'continuous' => 'Continuous Order',
    );

    while ($row = $stmt->fetch()){

        $order_item_detail = htmlspecialchars(date("d/m/Y H:i", strtotime($row['order_date'].' '.$row['order_time'])).' น.');
        $order_item_detail .= htmlspecialchars(' ('.$ORDER_TYPE_NAMES[$row['order_type']]).')<br>';
        if($row['order_item_type'] == 'off'){
            $order_item_detail .= 'Off : '.($row['off_med_name'] != null ? htmlspecialchars($row['off_med_name']).'<br>' : '').htmlspecialchars($row['off_order_item_detail']);
        } else {
            $order_item_detail .= ($row['med_name'] != null ? htmlspecialchars($row['med_name']).'<br>' : '').htmlspecialchars($row['order_item_detail']);
        }

        $output .= '
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="vertical-align: top; border-right:0.5px solid #000;padding:4px;width:25%;white-space: pre-wrap;">'.($row['order_item_id'] != null ? $order_item_detail : htmlspecialchars('*รายการนี้ไม่ได้ผูกกับ Order*')).'</td>
            <td  style="vertical-align: top; border-right:0.5px solid #000;padding:4px;width:11%;">'.($row['plan_date'] != null ? htmlspecialchars(date("d/m/Y H:i", strtotime($row['plan_date'].' '.$row['plan_time']))).' น.' : '').' <br>('.$PLAN_SCH_TYPE_NAMES[$row['plan_sch_type']].')'.'</td>
            <td  style="vertical-align: top; border-right:0.5px solid #000;padding:4px;">'.htmlspecialchars($row['plan_detail']).'</td>
            <td  style="vertical-align: top; border-right:0.5px solid #000;padding:4px;width:11%;">'.($row['action_date'] != null ? htmlspecialchars(date("d/m/Y H:i", strtotime($row['action_date'].' '.$row['action_time']))).' น.' : '').'</td>
            <td  style="vertical-align: top; border-right:0.5px solid #000;padding:4px;">'.
                ($row['action_person_1_doctor_name'] != null ? '<div>'.htmlspecialchars('- '.$row['action_person_1_doctor_name']).'</div>' : '')
                .' '.
                ($row['action_person_2_doctor_name'] != null ? '<div>'.htmlspecialchars('- '.$row['action_person_2_doctor_name']).'</div>' : '')
            .'</td>
            <td  style="vertical-align: top; border-right:0.5px solid #000;padding:4px;">'.htmlspecialchars($row['action_result']).'</td>
            <td  style="vertical-align: top; border-right:0.5px solid #000;padding:4px;">'.htmlspecialchars($row['action_remark']).'</td>
        </tr>';
    }
    $output .= '</table>';

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
    $ipt_parameters['an'] = $an;
    $stmt_ipt->execute($ipt_parameters);
    $row_iptCount = 0;
    while ($row_ipt = $stmt_ipt->fetch()){
            $hn_row_ipt = htmlspecialchars($row_ipt['hn']);
            $pname_row_ipt = htmlspecialchars($row_ipt['pname']);
            $fname_row_ipt = htmlspecialchars($row_ipt['fname']);
            $lname_row_ipt = htmlspecialchars($row_ipt['lname']);
    }
    $mpdf->setFooter(' (พิมพ์โดย '.$_SESSION['name'].' วันที่พิมพ์ '.date('d/m/Y H:i').' ) '.'<br>ผู้ป่วย : (hn : '.$hn_row_ipt.')(an : '.$an.')(ชื่อ - สกุล : '.$pname_row_ipt.' '.$fname_row_ipt.' '.$lname_row_ipt.')'.' ( Page {PAGENO} of {nb} )');

    $mpdf->WriteHTML($output);
    $mpdf->Output();
    // echo $output;
} catch (PDOException  $e) {
    echo $e->getMessage();
}

?>