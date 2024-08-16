<?php   require_once '../include/Session.php';
       // Session::checkLoginSessionAndShowMessage(); //เช็ค session
        require_once '../include/DbUtils.php';
        require_once '../include/KphisQueryUtils.php';
        $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
     
?>

<div class="row">
    <div class="col-md-12">
        <h5><em class="fas fa-th-list"></em> รายการเอกสาร</h5>
        <table class="table table-bordered table-sm">
            <thead>
                <tr class="table-info">
                    <th scope="col">ชื่อเอกสาร</th>
                    <th scope="col">วันที่/เวลา (ที่บันทึก)</th>
                    <th scope="col">วันที่/เวลา (ที่แก้ไขล่าสุด)</th>
                </tr>
            </thead>
            <tbody id="Table_DocumentEdit">

            <?php 
            $sql = "SELECT id,doc_name  FROM ".DbConstant::KPHIS_DBNAME.".prs_document_tab";
            $stmt = $conn->prepare($sql);
    $stmt->execute();
    $rowCount = 0;
    while ($row = $stmt->fetch()){
        $rowCount++;  ?>
        <tr>
        <td class="text-center"><?php echo $rowCount;?> </td>
        <td><div class="text-truncate"><?php echo htmlspecialchars($row['doc_name']);?></div></td>

    
    </tr>
    <?php } ?>

            </tbody>
        </table>
    </div>
</div>

