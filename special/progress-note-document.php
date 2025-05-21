<?php 
//หน้าหลักเอกสาร
require_once '../include/Session.php';
       // Session::checkLoginSessionAndShowMessage(); //เช็ค session
        require_once '../include/DbUtils.php';
        require_once '../include/KphisQueryUtils.php';
        $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
        $an = empty($_REQUEST['an']) ? null : $_REQUEST['an'];
        $hn = KphisQueryUtils::getHnByAn($an);
       
?><br>
<div class="row">
    
    


    

</div>

<br>
<style>
    /* CSS for table header alignment */
    th.left-align {
      text-align: left;
    }

    th.center-align {
      text-align: center;
    }

    th.right-align {
      text-align: right;
    }
  </style>

<div class="row">
    <div class="col-md-12">
        <h5><em class="fas fa-th-list"></em> รายการเอกสาร</h5>
        <table class="table table-bordered table-sm">
            <thead>
                <tr class="table-info">
                    <th scope="col">ชื่อเอกสาร</th>
                    <th class = "center-align" scope="col">รายละเอียด</th>
                    <th scope="col">วันที่/เวลา (ที่บันทึก)</th>
                    <th scope="col">วันที่/เวลา (ที่แก้ไขล่าสุด)</th>
                </tr>
            </thead>
            <tbody id="Table_DocumentEdit">
            </tbody>
        </table>
    </div>
</div>
<script>
    function onclick_check_DocumentAdd(){
        var url = "prs-health3_check_DocumentAdd.php";
        var an  = <?=json_encode($an)?>;
        $.post(url,{an},function(data){
            $("#dropdownId_DocumentAdd").html(data);
        });
    }
    check_Table_DocumentEdit();

    function check_Table_DocumentEdit(){
        var url = "progress-note_Table_DocumentEdit.php";
        var an  = <?=json_encode($an)?>;
        $.post(url,{an},function(data){
            $("#Table_DocumentEdit").html(data);
        });
    }
</script>