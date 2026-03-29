<?php 
require_once '../include/Session.php';
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';
$conn = DbUtils::get_hosxp_connection();
$an = empty($_REQUEST['an']) ? null : $_REQUEST['an'];
?>
<br>
<div class="row">
    <div class="col-md-auto">
        <nav class="navbar navbar-expand-sm btn btn-info btn-sm" onclick="onclick_check_AuditAdd()">
            <div class="collapse navbar-collapse" id="collapsibleNavId_audit">
                <ul class="navbar-nav mr-auto mt-2 mt-lg-0">
                    <li class="nav-item active dropdown">
                        <a class="nav-link dropdown-toggle font-weight-bold" href="#" id="dropdownId_Audit" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-plus"></i> บันทึกเพิ่ม</a>
                        <div class="dropdown-menu" aria-labelledby="dropdownId_Audit" id="dropdownId_AuditAdd"></div>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
    <div class="col-md-auto">
        <button class="btn btn-warning btn-sm" onclick="location.reload();"><i class="fas fa-undo"></i> Refresh</button>
    </div>
</div>

<br>
<div class="row">
    <div class="col-md-12">
        <h5><i class="fas fa-th-list"></i> รายการตรวจประเมิน (Audit)</h5>
        <table class="table table-bordered table-sm table-hover">
            <thead class="thead-dark">
                <tr>
                    <th class="text-center">#</th>
                    <th>วันที่ตรวจ</th>
                    <th class="text-center">คะแนนรวม</th>
                    <th class="text-center">ผู้ตรวจ</th>
                    <th>วันที่บันทึก</th>
                    <th class="text-center">จัดการ</th>
                </tr>
            </thead>
            <tbody id="Table_AuditEdit"></tbody>
        </table>
    </div>
</div>

<script>
function onclick_check_AuditAdd(){
    const url = "form-audit-ipd_check_DocumentAdd.php";
    const an = <?=json_encode($an)?>;
    $.post(url, {an}, function(data){
        $("#dropdownId_AuditAdd").html(data);
    });
}

function check_Table_AuditEdit(){
    const url = "form-audit-ipd_Table_DocumentEdit.php";
    const an = <?=json_encode($an)?>;
    $.post(url, {an}, function(data){
        $("#Table_AuditEdit").html(data);
    });
}

check_Table_AuditEdit();
</script>
