<?php 
//หน้าหลักเอกสาร
require_once '../include/Session.php';
        // Session::checkLoginSessionAndShowMessage(); //เช็ค session
        require_once '../include/session-sso.php';
require_once '../include/DbUtils.php';
        require_once '../include/KphisQueryUtils.php';
        $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
        $an = empty($_REQUEST['an']) ? null : $_REQUEST['an'];
        $hn = KphisQueryUtils::getHnByAn($an);
?>

<style>
    .nfm-header {
        background: #007bff;
        color: #fff;
        padding: 10px 16px;
        border-radius: 4px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .nfm-header h5 {
        margin: 0;
        font-weight: bold;
        font-size: 1rem;
    }
    .nfm-header small {
        opacity: .8;
        font-size: 0.78rem;
    }
    .nfm-table th {
        background: #007bff;
        color: #fff;
        font-size: 0.85rem;
        white-space: nowrap;
        vertical-align: middle;
    }
    .nfm-table td {
        vertical-align: middle;
        font-size: 0.88rem;
    }
</style>

<div class="container-fluid py-2">
    <!-- Header -->
    <div class="nfm-header">
        <div>
            <h5><i class="fas fa-notes-medical mr-2"></i><?= htmlspecialchars(isset($menuname) ? $menuname : 'แบบประเมินภาวะเสี่ยงต่อการพลัดตก หกล้ม') ?></h5>
            <small>AN: <?= htmlspecialchars($an) ?></small>
        </div>
        <div>
            <div class="dropdown d-inline-block">
                <button class="btn btn-sm btn-light shadow-sm mr-2 dropdown-toggle" type="button" id="dropdownId" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" onclick="onclick_check_DocumentAdd()">
                    <i class="fas fa-plus"></i> บันทึกเพิ่ม
                </button>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownId" id="dropdownId_DocumentAdd">
                </div>
            </div>
            <button class="btn btn-sm btn-light shadow-sm mr-2" onClick="javascript:location.reload();">
                <i class="fas fa-undo"></i> Refresh
            </button>
            <a href="/pdffile/felldown-pdf.php?an=<?= urlencode($an) ?>&loginname=<?= urlencode($loginname) ?>" target="_blank" class="btn btn-sm btn-danger shadow-sm">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
        </div>
    </div>

    <!-- Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-sm table-hover mb-0 nfm-table">
                    <thead>
                        <tr>
                            <th>ชื่อเอกสาร</th>
                            <th class="text-center" style="width: 100px;">คะแนน</th>
                            <th style="width: 200px;">วันที่/เวลา (ที่บันทึก)</th>
                            <th style="width: 200px;">วันที่/เวลา (ที่แก้ไขล่าสุด)</th>
                        </tr>
                    </thead>
                    <tbody id="Table_DocumentEdit">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function onclick_check_DocumentAdd(){
        var url = "form-felldown_check_DocumentAdd.php";
        var an  = <?=json_encode($an)?>;
        $.post(url,{an},function(data){
            $("#dropdownId_DocumentAdd").html(data);
        });
    }
    check_Table_DocumentEdit();

    function check_Table_DocumentEdit(){
        var url = "form-felldown_Table_DocumentEdit.php";
        var an  = <?=json_encode($an)?>;
        $.post(url,{an},function(data){
            $("#Table_DocumentEdit").html(data);
        });
    }
</script>
