<?php
include("setting/head.php");
include("setting/query.php");
include("setting/connecttkp_db.php");
mysqli_query($conn, "SET character_set_results=utf8");
mysqli_query($conn, "SET character_set_client=utf8");
mysqli_query($conn, "SET character_set_connection=utf8");
header('Content-Type: text/html; charset=UTF-8');
$checksql ='select * from clinicalsummary where an = "'.$_REQUEST['an'].'"';
$checkquery = mysqli_query($conn2,$checksql);
$checkresult = mysqli_fetch_assoc($checkquery);

if(isset($_REQUEST["login"])){
    $login = $_REQUEST['login'];
}
else{
    $login = '';
}
?>
<style>
    body {
        font-size: 14px;
    }

    #contrainer {
        margin-left: 20% !important;
        margin-right: 20% !important;
    }

    th,
    td {
        color: black !important;
    }
    input[type="date"]::-webkit-datetime-edit,
    input[type="date"]::-webkit-inner-spin-button,
    input[type="date"]::-webkit-clear-button {
        color: #fff;
        position: relative;
    }

    input[type="date"]::-webkit-datetime-edit-year-field {
        position: absolute !important;
        border-left: 1px solid #8c8c8c;
        padding: 2px;
        color: #000;
        left: 56px;
    }

    input[type="date"]::-webkit-datetime-edit-month-field {
        position: absolute !important;
        border-left: 1px solid #8c8c8c;
        padding: 2px;
        color: #000;
        left: 26px;
    }


    input[type="date"]::-webkit-datetime-edit-day-field {
        position: absolute !important;
        color: #000;
        padding: 2px;
        left: 4px;

    }
</style>
<button style="margin-top: 5px !important;" class="primary" onclick="history.back()">ย้อนกลับ</button>
    <div id="contrainer" class="clinical">
    <form action="clinicalsummaryedit.php" method="post">
    <input type="hidden" name="an" value="<?php echo $_REQUEST['an']; ?>">
    <input type="hidden" name="hn" value="<?php echo $_REQUEST['hn']; ?>">
    <input type="hidden" name="loginname" value="<?php echo $login; ?>">
        <section>
            <h2 align="center">โรงพยาบาลชัยภูมิ</h2>
            <h3 align="center">แบบสรุปการจำหน่าย (Clinical summary)</h3>
            <table>
                <tr>
                    <th colspan="2">Clinical summary</th>
                </tr>
                <tr>
                    <td colspan="2">วัน เดือน ปี ที่จำหน่าย : <input name="input1" type="date" value="<?php echo $checkresult['c_date']?>"></td>
                </tr>
                <tr>
                    <td colspan="2">แพทย์<input type="text" name="doctorname" data-date="" data-date-format="DD MMMM YYYY" value="<?php echo $checkresult['doctorname'];?>" required></td>
                </tr>
            </table>
            <textarea name="input2" id="oneperwidth"><?php echo $checkresult['c_input1']?></textarea>
        </section>
        <input style="float:right !important;" class="Primary" type="submit" value="แก้ไข">
        <br><br>
        </form>
    </div>
