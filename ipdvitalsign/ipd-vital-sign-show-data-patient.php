<?php
/*
require_once './project/function/SessionManager.php';
SessionManager::checkLoginSessionAndShowMessage(); //เช็ค session
// SessionManager::checkPermissionAndShowMessage('KPHIS_ACCESS_IPD_NURSE_PROGRAM');
if(!SessionManager::checkPermission('VITAL_SIGN','VIEW')){
    // SessionManager::showMessage();
    return;
} */

?>
<script>
$(function(){
    var vital_sign_main_table = $('#vital_sign_main_table').dataTable({
        "order": [[ 0, "asc" ]],
        "dom": 'f,t',
        paging:  false
    });
});
</script>
    <p></p>
    <table id="vital_sign_main_table" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th scope="col" class="th-sm">รายชื่อผู้ป่วย</th>
            </tr>
        </thead>
        <tbody>
        <?php
        require_once '../include/DbUtils.php';
        $conn = DbUtils::get_hosxp_connection();
        $ward = $_REQUEST['ward'];
        $query_parameters = ['ward'=>$ward];
        $sql = "SELECT
            ipt.*,substring(concat(spclty.name,' - ',w.name),1,200) as sname,w.name  as ward_name,
            iptadm.bedno,iptadm.bedtype,roomno.name as roomname,iptadm.roomno,iptdiag.icd10,concat(iptdiag.icd10,' - ',i1.name) as icdname,
            concat(patient.pname,patient.fname,' ',patient.lname) as pname,aa.income as income,ptt.pcode as rtcode,ptt.name as rtname,
            dt.name as admdoctor_name ,  ft.name as finance_status_name,aa.admdate,aa.age_y,aa.age_m,aa.age_d,aa.paid_money ,
            aa.rcpt_money,fs.finance_status  , dc1.name as dchtype_name,dc2.name as dchstts_name, aa.paid_money-aa.rcpt_money as wait_paid_money,
            aa.rcpt_money as ipt_rcpt_money,di.name as incharge_doctor_name , if(ipt.dw_hhc_list_id>0,'Y','N') as hhc_send_status
            from ".DbConstant::HOSXP_DBNAME.".ipt  left outer join ".DbConstant::HOSXP_DBNAME.".spclty on spclty.spclty=ipt.spclty
            left outer join ".DbConstant::HOSXP_DBNAME.".iptadm on iptadm.an=ipt.an
            left outer join ".DbConstant::HOSXP_DBNAME.".patient on patient.hn=ipt.hn
            left outer join ".DbConstant::HOSXP_DBNAME.".doctor dt on dt.code = ipt.admdoctor
            left outer join ".DbConstant::HOSXP_DBNAME.".roomno on roomno.roomno=iptadm.roomno
            left outer join ".DbConstant::HOSXP_DBNAME.".iptdiag on iptdiag.an=ipt.an and iptdiag.diagtype='1'
            left outer join ".DbConstant::HOSXP_DBNAME.".icd101 i1 on i1.code=substring(iptdiag.icd10,1,3)
            left outer join ".DbConstant::HOSXP_DBNAME.".an_stat aa on aa.an=ipt.an
            left outer join ".DbConstant::HOSXP_DBNAME.".ward w on w.ward = ipt.ward
            left outer join ".DbConstant::HOSXP_DBNAME.".dchtype dc1 on dc1.dchtype = ipt.dchtype
            left outer join ".DbConstant::HOSXP_DBNAME.".dchstts dc2 on dc2.dchstts = ipt.dchstts
            left outer join ".DbConstant::HOSXP_DBNAME.".ipt_finance_status fs on fs.an = ipt.an
            left outer join ".DbConstant::HOSXP_DBNAME.".finance_status ft on ft.finance_status = fs.finance_status
            left outer join ".DbConstant::HOSXP_DBNAME.".doctor di on di.code = ipt.incharge_doctor
            left outer join ".DbConstant::HOSXP_DBNAME.".pttype ptt on ptt.pttype=ipt.pttype
            where  ipt.ward = :ward and ipt.dchstts is null order by LEFT(iptadm.bedno,3),MID(iptadm.bedno,4,999), ipt.regdate,ipt.regtime";

        $stmt = $conn->prepare($sql);
        $stmt->execute($query_parameters);
        $rowCount = 0;
        while ($row = $stmt->fetch()){
            $rowCount++; ?>
            <tr onclick="onclick_vital_sign_form(event,this,'<?php echo $row['an'];?>')">
                <td>
                   
                    <div class="float-left">
                    <?php
                        echo "เตียง: ".$row['bedno']."<br>AN: ".$row['an']."<br>HN: ".$row['hn']."<br>".$row['pname'];
                    ?>
                    </div>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table><br>