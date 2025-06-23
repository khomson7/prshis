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
                    <th scope="col">ชื่อเอกสาร2</th>
                    <th scope="col">ชื่อ-รายการ</th>
                    <th scope="col">วันที่/เวลา (ที่บันทึก)</th>
                    <th scope="col">วันที่/เวลา (ที่แก้ไขล่าสุด)</th>
                </tr>
            </thead>
            <tbody id="Table_DocumentEdit2">
            </tbody>
        </table>
    </div>
</div>
<script>
    
    check_Table_DocumentEdit2();

    function check_Table_DocumentEdit2(){
        var url = "form-due_Table_DocumentEdit2.php";
        var an  = <?=json_encode($an)?>;
        $.post(url,{an},function(data){
            $("#Table_DocumentEdit2").html(data);
        });
    }
</script>