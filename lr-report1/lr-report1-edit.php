<?php
        require_once '../include/Session.php';
        Session::checkLoginSessionAndShowMessage(); //เช็ค session
        require_once '../include/DbUtils.php';
        require_once '../include/KphisQueryUtils.php';
        $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
        $an = $_REQUEST['an'];
        $hn = KphisQueryUtils::getHnByAn($an);   // function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา
        $id = $_REQUEST['id'];
        $query_parameters = [
                                ':id' => $id,
                                ':an' => $an
                            ];
        $sql = "SELECT * FROM ".DbConstant::KPHIS_DBNAME.".prs_labor_report1 WHERE an = :an AND id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute($query_parameters);
        $rowCount = 0;
        //fetch Data
        $row = $stmt->fetch();
        $an = $row['an'];
$create_datetime = $row['create_datetime'];
$id = $row['id'];
$receive_date = $row['receive_date'];
$receive_time = $row['receive_time'];
$receive_from = $row['receive_from'];
$transport = $row['transport'];
$cc = $row['cc'];


$update_datetime = $row['update_datetime'];
$version = $row['version'];

//echo $receive_from;

?>

<script>
    $("#lr_report1_form").each(function() {
        $("input[name=version]").val(<?=json_encode($version)?>);
//ดึงวันที่และเวลาที่บันทึกมาแสดง
$("input[name=receive_date]").val(<?=json_encode($receive_date )?>);
$("input[name=receive_time]").val(<?=json_encode($receive_time )?>);

        var receive_from = <?=json_encode($receive_from)?>;
        if(receive_from == "คลอดในโรงพยาบาล"){
           $("#receive_from1").attr('checked',true);
          //  $("#receive_from2").attr('disabled', 'disabled');
        } else if(receive_from != "คลอดในโรงพยาบาล"){
           $("#receive_from1").attr('checked',false);
           $("#receive_from2").attr('checked',true);
           $('#from_text').attr("disabled",false).val(<?=json_encode($receive_from)?>);
          //  $("#receive_from2").attr('disabled', 'disabled');
        }

        //transport  
        var transport = <?=json_encode($transport)?>;

        if(transport == "อุ้มมา"){
            $("#transport1").attr('checked',true);
        }else if(transport == "transport incubator"){
            $("#transport2").attr('checked',true);
        }else if(transport == "clib"){
            $("#transport3").attr('checked',true);
        }

        //ดึงข้อมูลที่บันทึกมาแสดง
        $("textarea#cc").val(<?=json_encode($cc)?>);

        });


        

</script>
