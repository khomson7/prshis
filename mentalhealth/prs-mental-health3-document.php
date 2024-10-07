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
    <div class="col-md-auto">
        <nav class="navbar navbar-expand-sm btn btn-info btn-sm" onclick="onclick_check_DocumentAdd()">
            <button class="navbar-toggler d-lg-none" type="button" data-toggle="collapse" data-target="#collapsibleNavId_d"
                aria-controls="collapsibleNavId_d" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="collapsibleNavId_d">
                <ul class="navbar-nav mr-auto mt-2 mt-lg-0">
                    <li class="nav-item active dropdown">
                        <li class="nav-item active dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="dropdownId" data-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false"><em class="fas fa-plus"></em> บันทึกเพิ่ม</a>
                            <div class="dropdown-menu" aria-labelledby="dropdownId" id="dropdownId_DocumentAdd">
                            </div>
                        </li>
                        </li>
             
                    
                </ul>

            </div>
            
        </nav>

        
    </div>

    <buton class="navbar navbar-expand-sm btn btn-warning btn-sm" onclick="onclick_check_DocumentAdd()">
         
        <a class="nav-link" onClick="javascript:location.reload();"  ><i class="fas fa-undo"></i> Refresh </a>
        
</buton>

<buton >
<a class="nav-link" href="mental-health3-pdf.php?an=<?php echo $an; ?>&loginname=<?php echo $loginname; ?>" target="_blank" class="btn btn-secondary"><i class="fas fa-file-pdf"></i> Print <U>PDF</U> File</a>
</buton> 
    

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
                    <th class = "center-align" scope="col">คะแนน</th>
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
        var url = "prs-health3_Table_DocumentEdit.php";
        var an  = <?=json_encode($an)?>;
        $.post(url,{an},function(data){
            $("#Table_DocumentEdit").html(data);
        });
    }
</script>