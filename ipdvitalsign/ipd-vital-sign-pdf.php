<?php
ini_set('memory_limit','512M');
ini_set("pcre.backtrack_limit", "10000000");
set_time_limit(300);
require_once './project/function/SessionManager.php';
SessionManager::checkLoginSessionAndShowMessage(); //เช็ค session
// SessionManager::checkPermissionAndShowMessage('KPHIS_ACCESS_IPD_NURSE_ACTIVITY');
if(!(
        SessionManager::checkPermission('IPD_NURSE_MAIN_PROGRAM','ACCESS')
        //SessionManager::checkPermission('IPD_NURSE_NOTE','ADD')
        // && SessionManager::checkPermission('IPD_NURSE_NOTE','EDIT')
        && SessionManager::checkPermission('IPD_NURSE_NOTE','VIEW')
        // && SessionManager::checkPermission('IPD_NURSE_NOTE','REMOVE')
        )){
        return;
}
require_once __DIR__ . '/vendor/autoload.php';
require_once './project/function/DbUtils.php';
require_once './project/function/KphisQueryUtils.php';

function getVitalSignPDFText($row,$fieldName,$labelFieldName){
    $value = $row[$fieldName];
    $text ='';
    if($value != null && $value != ''){
        $text ='<font color="blue">'.$labelFieldName.':</font>'.$value.' ';
    }
    return $text;
}
date_default_timezone_set('asia/bangkok');
$conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
$mpdf = new \Mpdf\Mpdf();
$mpdf->AddPageByArray([
    'margin-left' => 8,
    'margin-right' => 8,
    'margin-top' => 8,
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
                height: 70px;

                /** Extra personal styles **/
                line-height: 35px;
        }
        </style>

        <h3 style="text-align:center;">'.htmlspecialchars(KphisConstant::KPHIS_HOSPITAL_NAME).'</h3>

        <table id="bg-table" width="100%" style="border-collapse: collapse;font-size:9pt;margin-top:8px;">
        <tr style="border:1px solid #000;margin: 45px;">
        <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="9%">&nbsp;เวลา</td>
        <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="5%">&nbsp;BT</td>
        <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="5%">&nbsp;PR</td>
        <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="5%">&nbsp;RR</td>
        <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="5%">&nbsp;SBP</td>
        <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="5%">&nbsp;DBP</td>
        <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="5%">&nbsp;SAT</td>
        <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="5%">&nbsp;MAP</td>
        <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="5%">&nbsp;PS</td>
        <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="7%">&nbsp;MEWS</td>
        <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="5%">&nbsp;DTX</td>
        <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="5%">&nbsp;HCT</td>
        <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="24%">&nbsp;อื่นๆ</td>
        <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="5%">&nbsp;U</td>
        <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;"width="5%">&nbsp;F</td>
        </tr>
';
        $an = $_REQUEST['an'];//รับค่า an
        //----------------------รับค่า an และ select ข้อมูล จากฐานข้อมูลเพื่อค้นหา hn
        $hn_REQUEST = KphisQueryUtils::getHnByAn($an);// function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา
        //----------------------รับค่า an และ select ข้อมูล จากฐานข้อมูลเพื่อค้นหา hn

        $query_parameters = ['an'=>$an];
        $sql = "SELECT vs.vs_id, vs.an, vs.vs_datetime, vs.bt, vs.pr, vs.rr, vs.respirator, vs.sbp, vs.dbp, vs.inotrope, vs.map,  vs.sat, vs.cvp,
        vs.end_co2, vs.conscious_id, vs.bw, vs.height, vs.urine, vs.catheter, vs.urine_amount, vs.urine_duration, vs.feces, vs.head, vs.t_inc, vs.line_id, vs.line_no,
        vs.line_mark, vs.braden, vs.pain, vs.eye, vs.verbal, vs.movement, vs.right_pupil, vs.right_cha_id, vs.left_pupil, vs.left_cha_id,
        vs.va_id, vs.mass_id, vs.lt_arm, vs.lt_leg, vs.rt_arm, vs.rt_leg,
        vs.severity, vs.had_name, vs.had_drop, vs.hct, vs.dtx, vs.bl, vs.mcb, vs.suction,
        vs.nb, vs.o2_id, vs.o2_flow, vs.tube_id, vs.tube_no, vs.tube_mark, vs.ventilator_name, vs.mode, vs.tv, vs.pip,
        vs.r_rate, vs.i_rate, vs.e_rate, vs.ti, vs.ps, vs.fio2, vs.peep, vs.ft, vs.delta_p, vs.o2_map,
        vs.intake_id, vs.intake_type, vs.intake_amount, vs.intake_absorb, vs.output_id, vs.output_amount,
        vs.lr_int, vs.lr_dur, vs.lr_fsh, vs.lr_sev, vs.lr_cer, vs.lr_eff, vs.lr_sta, vs.lr_mem, vs.lr_af, vs.other,
        vs.create_user, vs.create_datetime, vs.update_user, vs.update_datetime, vs.version,
        ipd_vs_conscious.conscious_name,
        ipd_vs_line.line_name,
        left_cha.cha_name as left_cha_name,
        right_cha.cha_name as right_cha_name,
        ipd_vs_va.va_name,
        ipd_vs_mass.mass_name,
        ipd_vs_lt_arm.lt_arm_name,
        ipd_vs_lt_leg.lt_leg_name,
        ipd_vs_rt_arm.rt_arm_name,
        ipd_vs_rt_leg.rt_leg_name,
        ipd_vs_o2.o2_name,
        ipd_vs_tube.tube_name,
        ipd_vs_intake.intake_name,
        ipd_vs_output.output_name,
        ipd_vs_lr_sta.lr_sta_name,
        ipd_vs_lr_mem.lr_mem_name,
        score_total(
        score_bt(an_stat.age_y,vs.bt),
        score_pr(an_stat.age_y,vs.pr),
        score_rr(an_stat.age_y,vs.rr,vs.respirator),
        score_sbp(an_stat.age_y,vs.sbp,vs.inotrope),
        score_conscious_id(an_stat.age_y,vs.conscious_id),
        score_urine(an_stat.age_y,vs.urine_amount,vs.urine_duration)
        ) as mews_score
        FROM ".KphisConstant::HOSXP_CONNECTION_KPHIS_DBNAME.".ipd_vs_vital_sign vs
        LEFT OUTER JOIN ".KphisConstant::HOSXP_CONNECTION_KPHIS_DBNAME.".ipd_vs_conscious on vs.conscious_id = ipd_vs_conscious.conscious_id
        LEFT OUTER JOIN ".KphisConstant::HOSXP_CONNECTION_KPHIS_DBNAME.".ipd_vs_line on vs.line_id = ipd_vs_line.line_id
        LEFT OUTER JOIN ".KphisConstant::HOSXP_CONNECTION_KPHIS_DBNAME.".ipd_vs_cha left_cha on vs.left_cha_id = left_cha.cha_id
        LEFT OUTER JOIN ".KphisConstant::HOSXP_CONNECTION_KPHIS_DBNAME.".ipd_vs_cha right_cha on vs.right_cha_id = right_cha.cha_id
        LEFT OUTER JOIN ".KphisConstant::HOSXP_CONNECTION_KPHIS_DBNAME.".ipd_vs_va on vs.va_id = ipd_vs_va.va_id
        LEFT OUTER JOIN ".KphisConstant::HOSXP_CONNECTION_KPHIS_DBNAME.".ipd_vs_mass on vs.mass_id = ipd_vs_mass.mass_id
        LEFT OUTER JOIN ".KphisConstant::HOSXP_CONNECTION_KPHIS_DBNAME.".ipd_vs_o2 on vs.o2_id =ipd_vs_o2.o2_id
        LEFT OUTER JOIN ".KphisConstant::HOSXP_CONNECTION_KPHIS_DBNAME.".ipd_vs_tube on vs.tube_id =ipd_vs_tube.tube_id
        LEFT OUTER JOIN ".KphisConstant::HOSXP_CONNECTION_KPHIS_DBNAME.".ipd_vs_intake on vs.intake_id =ipd_vs_intake.intake_id
        LEFT OUTER JOIN ".KphisConstant::HOSXP_CONNECTION_KPHIS_DBNAME.".ipd_vs_output on vs.output_id =ipd_vs_output.output_id
        LEFT OUTER JOIN ".KphisConstant::HOSXP_CONNECTION_KPHIS_DBNAME.".ipd_vs_lr_sta on vs.lr_sta =ipd_vs_lr_sta.lr_sta_id
        LEFT OUTER JOIN ".KphisConstant::HOSXP_CONNECTION_KPHIS_DBNAME.".ipd_vs_lr_mem on vs.lr_mem =ipd_vs_lr_mem.lr_mem_id
        LEFT OUTER JOIN ".KphisConstant::HOSXP_CONNECTION_KPHIS_DBNAME.".ipd_vs_lt_arm on vs.lt_arm =ipd_vs_lt_arm.lt_arm
        LEFT OUTER JOIN ".KphisConstant::HOSXP_CONNECTION_KPHIS_DBNAME.".ipd_vs_lt_leg on vs.lt_leg =ipd_vs_lt_leg.lt_leg
        LEFT OUTER JOIN ".KphisConstant::HOSXP_CONNECTION_KPHIS_DBNAME.".ipd_vs_rt_arm on vs.rt_arm =ipd_vs_rt_arm.rt_arm
        LEFT OUTER JOIN ".KphisConstant::HOSXP_CONNECTION_KPHIS_DBNAME.".ipd_vs_rt_leg on vs.rt_leg =ipd_vs_rt_leg.rt_leg
        LEFT OUTER JOIN ".KphisConstant::HOSXP_CONNECTION_HOSXP_DBNAME.".an_stat ON an_stat.an = vs.an
        WHERE vs.an=:an
        ORDER BY vs.vs_datetime desc";
        $stmt = $conn->prepare($sql);
        $stmt->execute($query_parameters);
        $rowCount = 0;
        while ($row = $stmt->fetch()){
            $vs_datetime = $row['vs_datetime'];

            $bt = $row['bt'];
            $pr = $row['pr'];
            $rr = $row['rr'];
            $respirator = $row['respirator'];
            $sbp = $row['sbp'];
            $dbp = $row['dbp'];
            $inotrope = $row['inotrope'];
            $map = $row['map'];
            $sat = $row['sat'];
            $hct = $row['hct'];
            $dtx = $row['dtx'];
            $urine = $row['urine'];
            $feces = $row['feces'];
            $mews_score = $row['mews_score'];
            $total_text = '';
            $total_text .= getVitalSignPDFText($row,"cvp","CVP");
            $total_text .= getVitalSignPDFText($row,"end_co2","END CO2");
            $total_text .= getVitalSignPDFText($row,"conscious_name","CONSCIOUS");
            $total_text .= getVitalSignPDFText($row,"bw","BW");
            $total_text .= getVitalSignPDFText($row,"height","HEIGHT");
            $total_text .= getVitalSignPDFText($row,"head","HEAD");
            $total_text .= getVitalSignPDFText($row,"t_inc","T INC");
            $total_text .= getVitalSignPDFText($row,"line_name","LINE");
            $total_text .= getVitalSignPDFText($row,"line_no","LINE NO");
            $total_text .= getVitalSignPDFText($row,"line_mark","LINE MARK");
            $total_text .= getVitalSignPDFText($row,"braden","BRADEN");
            $total_text .= getVitalSignPDFText($row,"eye","E");
            $total_text .= getVitalSignPDFText($row,"verbal","V");
            $total_text .= getVitalSignPDFText($row,"movement","M");
            $total_text .= getVitalSignPDFText($row,"right_pupil","RIGHT PUPIL");
            $total_text .= getVitalSignPDFText($row,"right_cha_name","RIGHT CHA");
            $total_text .= getVitalSignPDFText($row,"left_pupil","LEFT PUPIL");
            $total_text .= getVitalSignPDFText($row,"left_cha_name","LEFT CHA");
            $total_text .= getVitalSignPDFText($row,"va_name","VA");
            $total_text .= getVitalSignPDFText($row,"mass_name","MASS");
            $total_text .= getVitalSignPDFText($row,"lt_arm_name","Lt Arm");
            $total_text .= getVitalSignPDFText($row,"lt_leg_name","Lt Leg");
            $total_text .= getVitalSignPDFText($row,"rt_arm_name","Rt Arm");
            $total_text .= getVitalSignPDFText($row,"rt_leg_name","Rt Leg");
            $total_text .= getVitalSignPDFText($row,"severity","SEVERITY");
            $total_text .= getVitalSignPDFText($row,"had_name","HAD");
            $total_text .= getVitalSignPDFText($row,"had_drop","HAD DROP");
            $total_text .= getVitalSignPDFText($row,"bl","BL");
            $total_text .= getVitalSignPDFText($row,"mcb","MCB");
            if($row['suction'] == 'Y'){
                $total_text .= getVitalSignPDFText($row,"suction","SUCTION");
            }
            if($row['nb'] == 'Y'){
                $total_text .= getVitalSignPDFText($row,"nb","NB");
            }
            $total_text .= getVitalSignPDFText($row,"o2_id","O2");
            $total_text .= getVitalSignPDFText($row,"o2_flow","O2 FLOW");
            $total_text .= getVitalSignPDFText($row,"tube_name","TUBE");
            $total_text .= getVitalSignPDFText($row,"tube_no","TUBE NO");
            $total_text .= getVitalSignPDFText($row,"tube_mark","TUBE MARK");
            $total_text .= getVitalSignPDFText($row,"ventilator_name","VENTILATOR NAME");
            $total_text .= getVitalSignPDFText($row,"mode","MODE");
            $total_text .= getVitalSignPDFText($row,"tv","TV");
            $total_text .= getVitalSignPDFText($row,"pip","PIP");
            $total_text .= getVitalSignPDFText($row,"r_rate","R RATE");
            $total_text .= getVitalSignPDFText($row,"i_rate","I RATE");
            $total_text .= getVitalSignPDFText($row,"e_rate","E RATE");
            $total_text .= getVitalSignPDFText($row,"ti","TI");
            $total_text .= getVitalSignPDFText($row,"ps","PS");
            $total_text .= getVitalSignPDFText($row,"fio2","FIO2");
            $total_text .= getVitalSignPDFText($row,"peep","PEEP");
            $total_text .= getVitalSignPDFText($row,"ft","FT");
            $total_text .= getVitalSignPDFText($row,"delta_p","DELTA P");
            $total_text .= getVitalSignPDFText($row,"o2_map","MAP");
            $total_text .= getVitalSignPDFText($row,"intake_name","INTAKE");
            $total_text .= getVitalSignPDFText($row,"intake_type","INTAKE TYPE");
            $total_text .= getVitalSignPDFText($row,"intake_amount","INTAKE AMOUNT");
            $total_text .= getVitalSignPDFText($row,"intake_absorb","INTAKE ABSORB");
            $total_text .= getVitalSignPDFText($row,"output_amount","OUTPUT AMOUNT");
            $total_text .= getVitalSignPDFText($row,"lr_int","INTERVAL");
            $total_text .= getVitalSignPDFText($row,"lr_dur","DURATION");
            $total_text .= getVitalSignPDFText($row,"lr_fsh","FETAL HEART SOUND");
            $total_text .= getVitalSignPDFText($row,"lr_sev","LR SEVERITY");
            $total_text .= getVitalSignPDFText($row,"lr_cer","CERVIX");
            $total_text .= getVitalSignPDFText($row,"lr_eff","EFFACEMENT");
            $total_text .= getVitalSignPDFText($row,"lr_sta","LR STATION");
            $total_text .= getVitalSignPDFText($row,"lr_mem","MEMBRANE");
            $total_text .= getVitalSignPDFText($row,"lr_af","ลักษณะ MEMBRAN");
            $total_text .= getVitalSignPDFText($row,"other","OTHER");
                //----------------------select ข้อมูล จากฐานข้อมูลเพื่อค้นหา ชื่อ - สกุล
                $sql_ipt = "select patient.hn,patient.pname,patient.fname,patient.lname,patient.drugallergy,
                        an_stat.age_y,an_stat.age_m,an_stat.age_d,
                        ipt.regdate,ipt.regtime,ipt.ward,
                        ipt.pttype,
                        pttype.`name` as pttype_name,
                        ward.shortname
                        from ".KphisConstant::HOSXP_CONNECTION_HOSXP_DBNAME.".ipt
                        left outer join ".KphisConstant::HOSXP_CONNECTION_HOSXP_DBNAME.".an_stat on an_stat.an=ipt.an
                        left outer join ".KphisConstant::HOSXP_CONNECTION_HOSXP_DBNAME.".patient on patient.hn=ipt.hn
                        left outer join ".KphisConstant::HOSXP_CONNECTION_HOSXP_DBNAME.".ward on ward.ward=ipt.ward
                        LEFT OUTER JOIN ".KphisConstant::HOSXP_CONNECTION_HOSXP_DBNAME.".pttype ON pttype.pttype = ipt.pttype
                        WHERE ipt.an=:an";
                $stmt_ipt = $conn->prepare($sql_ipt);
                $stmt_ipt->execute($query_parameters);
                $row_iptCount = 0;
                while ($row_ipt = $stmt_ipt->fetch()){
                        $hn_row_ipt = htmlspecialchars($row_ipt['hn']);
                        $pname_row_ipt = htmlspecialchars($row_ipt['pname']);
                        $fname_row_ipt = htmlspecialchars($row_ipt['fname']);
                        $lname_row_ipt = htmlspecialchars($row_ipt['lname']);
                }
                $mpdf->setFooter(' (พิมพ์โดย '.$_SESSION['name'].' วันที่พิมพ์ '.date('d/m/Y H:i').' ) '.'<br>ผู้ป่วย : (hn : '.$hn_row_ipt.')(an : '.$an.')(ชื่อ - สกุล : '.$pname_row_ipt.' '.$fname_row_ipt.' '.$lname_row_ipt.')'.' ( Page {PAGENO} of {nb} )');
                $mpdf->WriteHTML('');
                //----------------------select ข้อมูล จากฐานข้อมูลเพื่อค้นหา ชื่อ - สกุล

$head .= '

        <tr style="border:1px solid #000;margin: 45px;">
        <td  style="vertical-align: top; border-right:0.5px solid #000;padding:4px;">'.htmlspecialchars($vs_datetime).'</td>
        <td  style="vertical-align: top; border-right:0.5px solid #000;padding:4px;">'.htmlspecialchars($bt).'</td>
        <td  style="vertical-align: top; border-right:0.5px solid #000;padding:4px;">'.htmlspecialchars($pr).'</td>
        <td  style="vertical-align: top; border-right:0.5px solid #000;padding:4px;">'.htmlspecialchars($rr).'</td>
        <td  style="vertical-align: top; border-right:0.5px solid #000;padding:4px;">'.htmlspecialchars($sbp).'</td>
        <td  style="vertical-align: top; border-right:0.5px solid #000;padding:4px;">'.htmlspecialchars($dbp).'</td>
        <td  style="vertical-align: top; border-right:0.5px solid #000;padding:4px;">'.htmlspecialchars($sat).'</td>
        <td  style="vertical-align: top; border-right:0.5px solid #000;padding:4px;">'.htmlspecialchars($map).'</td>
        <td  style="vertical-align: top; border-right:0.5px solid #000;padding:4px;">'.htmlspecialchars($pain).'</td>
        <td  style="vertical-align: top; border-right:0.5px solid #000;padding:4px; text-align:center;">'.htmlspecialchars($mews_score).'</td>
        <td  style="vertical-align: top; border-right:0.5px solid #000;padding:4px;">'.htmlspecialchars($dtx).'</td>
        <td  style="vertical-align: top; border-right:0.5px solid #000;padding:4px;">'.htmlspecialchars($hct).'</td>
        <td  style="vertical-align: top; border-right:0.5px solid #000;padding:4px;">'.$total_text.'</td>
        <td  style="vertical-align: top; border-right:0.5px solid #000;padding:4px;">'.htmlspecialchars($urine).'</td>
        <td  style="vertical-align: top; border-right:0.5px solid #000;padding:4px;">'.htmlspecialchars($feces).'</td>
        </tr>
';
}

$head .=  '
        </table>
';

$mpdf->WriteHTML($head);
$mpdf->Output();
?>