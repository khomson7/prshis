<?php
    require_once './project/function/DbUtils.php';
    require_once './project/function/KphisQueryUtils.php';
    require_once './project/function/SessionManager.php';

    $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
    SessionManager::checkLoginSessionAndShowMessage(); //เช็ค session
    if(!(
    // && SessionManager::checkPermission('IPD_NURSE_MAIN_PROGRAM','ACCESS')
    // && SessionManager::checkPermission('IO','ADD')
    SessionManager::checkPermission('IO','EDIT')
    // && SessionManager::checkPermission('IO','VIEW')
    // && SessionManager::checkPermission('IO','REMOVE')
    )){
    return;
}

    $an = $_REQUEST['an'];
    $io_id = $_REQUEST['io_id'];
    $query_parameters = [
                        ':io_id' => $io_id,
                        ':an' => $an
                        ];
    $sql = "SELECT * FROM ".KphisConstant::HOSXP_CONNECTION_KPHIS_DBNAME.".ipd_io io WHERE io.io_id = :io_id AND io.an = :an ";
    $stmt = $conn->prepare($sql);
    $stmt->execute($query_parameters);
    $rowCount = 0;
    $row = $stmt->fetch();
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

        $io_version = $row['version'];
?>
<script>
    $("#vital-sign-io-form-1").each(function() {
        $("input[name=io_id]").val(<?=json_encode($io_id )?>);//ฟิลด์ hidden
        $("input[name=io_version]").val(<?=json_encode($io_version )?>);//ฟิลด์ hidden

        $("input[name=io_date]").val(<?=json_encode($io_date)?>);
        $("input[name=io_time]").val(<?=json_encode($io_time )?>);

        $("select#io_parenteral_type").val(<?=json_encode($io_parenteral_type )?>);
        $("input[name=io_parenteral_name]").val(<?=json_encode($io_parenteral_name)?>);
        $("input[name=io_parenteral_amount]").val(<?=json_encode($io_parenteral_amount)?>);
        $("input[name=io_parenteral_absorb]").val(<?=json_encode($io_parenteral_absorb)?>);
        $("input[name=io_parenteral_carry_forward]").val(<?=json_encode($io_parenteral_carry_forward)?>);
        $("textarea#io_parenteral_remark").val(<?=json_encode($io_parenteral_remark)?>);

        $("input[name=io_oral_name]").val(<?=json_encode($io_oral_name)?>);
        $("input[name=io_oral_amount]").val(<?=json_encode($io_oral_amount)?>);
        $("input[name=io_oral_absorb]").val(<?=json_encode($io_oral_absorb)?>);
        $("input[name=io_oral_carry_forward]").val(<?=json_encode($io_oral_carry_forward)?>);
        $("textarea#io_oral_remark").val(<?=json_encode($io_oral_remark)?>);

        $("select#io_output_type").val(<?=json_encode($io_output_type )?>);
        $("input[name=io_output_amount]").val(<?=json_encode($io_output_amount)?>);
        $("textarea#io_output_remark").val(<?=json_encode($io_output_remark)?>);

        $("#btn_delete_io").attr('class','btn btn-danger btn-sm');
    });
</script>