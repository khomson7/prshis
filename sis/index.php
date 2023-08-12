
<?php
include("setting/head.php");
include("setting/query.php");
include("setting/connecttkp_db.php");
mysqli_query($conn, "SET character_set_results=utf8");
mysqli_query($conn, "SET character_set_client=utf8");
mysqli_query($conn, "SET character_set_connection=utf8");
//header('Content-Type: text/html; charset=UTF-8');
$checksql = 'select CONCAT(COUNT(surgery_No))num from sis where an = "' . $_REQUEST['an'] . '"';
$checkquery = mysqli_query($conn2, $checksql);
$checkresult = mysqli_fetch_array($checkquery);

if (isset($_REQUEST["login"])) {
    $login = $_REQUEST['login'];
} else {
    $login = '';
}
if (isset($_GET['paper'])) {
    echo '';
} else {
  //  header('location:index2.php?hn=' . $_GET["hn"] . '&an=' . $_GET["an"] . '&login=' . $_GET["login"] . '&surgery_No=' . $_GET["surgery_No"] . '');
}


$sql = "select * from sis where an ='".$_REQUEST['an']."' and surgery_No = '".$_REQUEST['surgery_No']."'";
$query=mysqli_query($conn2,$sql);
$result2=mysqli_fetch_assoc($query);

?>

<head>
    <!--     <meta http-equiv="refresh" content="2" /> -->
    <title>Sistrunk</title>
</head>

<style>
    th,
    td {
        color: black !important;
    }

    #contrainer {
        margin-left: 20% !important;
        margin-right: 20% !important;
    }

    #contrainer input[type="text"] {
        padding: 15px !important;
    }

    <?php  if($checkresult['num']==0){ ?>

</style>
<button style="margin-top: 5px !important;" class="primary" onclick="history.back()">ย้อนกลับ</button>
<form action="OP_NOTE_SISTRUNK_DATA.php" m ethod="post">
    <input type="hidden" name="an" value="<?php echo $result['an']; ?>">
    <input type="hidden" name="doctorname" value="<?php echo $result['doctorname']; ?>">
    <input type="hidden" name="hn" value="<?php echo $result['hn']; ?>">
    <input type="hidden" name="login" value="<?php echo $login; ?>">
    <div id="contrainer" class="clinical">
        <section>
            <h2 align="center">โรงพยาบาลชัยภูมิ</h2>
            <h3 align="center">บันทึกผ่าตัด (Sistrunk)</h3>
            <table class="alt">
                <tr>
                    <td>Date of operation</td>
                    <td><input type="date" name="text1" id="" class="w10" required></td>
                    <td>Time started</td>
                    <td><input type="time" name="text2" id="" class="w10" required></td>
                    <td>Time ended</td>
                    <td><input type="time" name="text3" id="" class="w10" required></td>
                </tr>
                <tr>
                    <td>Surgeon</td>
                    <td><input type="text" name="text4" value="<?php echo $result['doctorname']; ?>"></td>
                    <td>first assistant</td>
                    <td colspan="3"><input type="text" name="text5" id=""></td>
                </tr>
                <tr>
                    <td>Second assistant</td>
                    <td><input type="text" name="text6" id=""></td>
                    <td>Surgical nurse</td>
                    <td colspan="3"><input type="text" name="text7" id=""></td>
                </tr>
                <tr>
                    <td>Clinical diagnosis</td>
                    <td><input type="text" name="text8" id=""></td>
                    <td>Thyroglossal duct cyst</td>
                    <td colspan="5"><input type="text" name="text9" id=""></td>
                </tr>
                <tr>
                    <td>Post-operative diagnosis</td>
                    <td><input type="text" name="text10" id=""></td>
                    <td>Thyroglossal duct cyst</td>
                    <td colspan="5"><input type="text" name="text11" id=""></td>
                </tr>
                <tr>
                    <td>operation</td>
                    <td><input type="text" name="text12" id=""></td>
                    <td>Sistrunk’s operation</td>
                    <td colspan="5"><input type="text" name="text13" id=""></td>
                </tr>
                <tr>
                    <td>Anesthesia</td>
                    <td><input type="text" name="text14" id=""></td>
                    <td>Anesthesist</td>
                    <td colspan="4"><input type="text" name="text15" id=""></td>
                </tr>
            </table>
            <h3 align="center">Description of operation</h3>
            <table class="alt">
                <tr>
                    <td>Position</td>
                    <td>Supine with neck extended</td>
                </tr>
                <tr>
                    <td>Incision</td>
                    <td>Horizontal incision just below the level of the hyoid bone</td>
                </tr>
                <tr>
                    <td>Findings</td>
                    <td colspan="4"><textarea id="text20" name="text20" rows="4" cols="50"></textarea></td>
                </tr>
                <tr>
                    <td>Procedure</td>
                    <td colspan="4">- Incision was done.</td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">- The skin flap in the plane of the superficial layer of the deep cervical fascia was elevated.</td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">- The infrahyoid strap muscle was divided at their insertion into the body of the hyoid. </td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">- The body of the hyoid bone was divided at approximately the lesser cornu bilaterally.</td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">- The thyroglossal duct cyst was dissected, and the tract was followed up to the foramen cecum and then suture ligation was done.</td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">- Bleeding was stopped</td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">
                        <input type="checkbox" name="check1" id="check1">
                        <label for="check1">Penrose drain/</label>
                        <input type="checkbox" name="check2" id="check2">
                        <label for="check2">Radivac drain was placed.</label>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">- Incision was close layer by layer.</td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">
                        - EBL
                        <input class="w4" type="text" name="text21" id="">
                        cc
                    </td>
                </tr>
                <tr>
                    <td>Special note</td>
                    <td colspan="4"><input type="text" name="text22" id=""></td>
                </tr>
                <tr>
                    <td>Pathologic request exam</td>
                    <td colspan="4">
                        <input type="checkbox" name="check3" id="check3">
                        <label for="check3">Yes</label>
                        <input type="checkbox" name="check4" id="check4">
                        <label for="check4">No</label>
                        Surgeon <input class="w7" type="text" name="text23" id="text23" value="<?php echo $result['doctorname'] ?>">
                    </td>
                </tr>
            </table>
            <table class="alt">
                <tr>
                    <td>Name of patient : <input name="input7" type="text" value="<?php echo $result["pname"] . '' . $result["fname"] . ' ' . $result["lname"]; ?>" disabled></td>
                    <td>Age : <input name="input8" type="text" value="<?php echo $result["age_y"] . 'ปี ' . $result["age_m"] . 'เดือน ' . $result["age_d"] . 'วัน'; ?>" disabled></td>
                    <td>Hospital number : <input name="input8" type="text" value="<?php echo $result["hcode"]; ?>" disabled></td>
                </tr>
                <tr>
                    <td>Department of service <input name="input7" type="text" value="<?php echo $result['SPCLTY_NAME']; ?>"></td>
                    <td>Ward : <input name="input7" type="text" value="<?php echo $result["WARD_NAME"]; ?>" disabled></td>
                    <td>AN : <input name="input7" type="text" value="<?php echo $result["an"]; ?>" disabled></td>
                </tr>
            </table>
        </section>
        <input style="float:right !important;" class="Primary" type="submit" value="บันทึก">
        <br><br>
    </div>
</form>

<?php }  

?>


<?php  if($checkresult['num']!=0){ ?>

<form action="OP_NOTE_SISTRUNK_edit.php" m ethod="post">
    <input type="hidden" name="an" value="<?php echo $result['an']; ?>">
    <input type="hidden" name="doctorname" value="<?php echo $result['doctorname']; ?>">
    <input type="hidden" name="hn" value="<?php echo $result['hn']; ?>">
    <input type="hidden" name="login" value="<?php echo $login; ?>">
    <div id="contrainer" class="clinical">
        <section>
            <h2 align="center">โรงพยาบาลชัยภูมิ</h2>
            <h3 align="center">บันทึกผ่าตัด (Sistrunk)</h3>
            <table class="alt">
                <tr>
                    <td>Date of operation</td>
                    <td><input type="date" name="text1" id="" class="w10" value="<?php echo $result2['sis_doo'];?>"></td>
                    <td>Time started</td>
                    <td><input type="time" name="text2" id="" class="w10" value="<?php echo $result2['sis_ts'];?>"></td>
                    <td>Time ended</td>
                    <td><input type="time" name="text3" id="" class="w10" value="<?php echo $result2['sis_te'];?>"></td>
                </tr>
                <tr>
                    <td>Surgeon</td>
                    <td><input type="text" name="text4" value="<?php echo $result2['sis_sur']; ?>"></td>
                    <td>first assistant</td>
                    <td colspan="3"><input type="text" name="text5" id="" value="<?php echo $result2['sis_fa'];?>"></td>
                </tr>
                <tr>
                    <td>Second assistant</td>
                    <td><input type="text" name="text6" id="" value="<?php echo $result2['sis_sa'];?>"></td>
                    <td>Surgical nurse</td>
                    <td colspan="3"><input type="text" name="text7" id="" value="<?php echo $result2['sis_sn'];?>"></td>
                </tr>
                <tr>
                    <td>Clinical diagnosis</td>
                    <td><input type="text" name="text8" id="" value="<?php echo $result2['sis_cd'];?>"></td>
                    <td>Thyroglossal duct cyst</td>
                    <td colspan="5"><input type="text" name="text9" id="" value="<?php echo $result2['sis_tdc'];?>"></td>
                </tr>
                <tr>
                    <td>Post-operative diagnosis</td>
                    <td><input type="text" name="text10" id="" value="<?php echo $result2['sis_pod'];?>"></td>
                    <td>Thyroglossal duct cyst</td>
                    <td colspan="5"><input type="text" name="text11" id="" value="<?php echo $result2['sis_tdc2'];?>"></td>
                </tr>
                <tr>
                    <td>operation</td>
                    <td><input type="text" name="text12" id="" value="<?php echo $result2['sis_operation'];?>"></td>
                    <td>Sistrunk’s operation</td>
                    <td colspan="5"><input type="text" name="text13" id="" value="<?php echo $result2['sis_so'];?>"></td>
                </tr>
                <tr>
                    <td>Anesthesia</td>
                    <td><input type="text" name="text14" id="" value="<?php echo $result2['sis_anesthesia'];?>"></td>
                    <td>Anesthesist</td>
                    <td colspan="4"><input type="text" name="text15" id="" value="<?php echo $result2['sis_anesthesist'];?>"></td>
                </tr>
            </table>
            <h3 align="center">Description of operation</h3>
            <table class="alt">
            <tr>
                    <td>Position</td>
                    <td>Supine with neck extended</td>
                </tr>
                <tr>
                    <td>Incision</td>
                    <td>Horizontal incision just below the level of the hyoid bone</td>
                </tr>
                <tr>
                    <td>Findings</td>
                    <td colspan="4"><textarea name="text20" id="text20" cols="30" rows="10"><?php echo $result2['sis_findings'];?></textarea></td>
                </tr>
                <tr>
                    <td>Procedure</td>
                    <td colspan="4">- Incision was done.</td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">- The skin flap in the plane of the superficial layer of the deep cervical fascia was elevated.</td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">- The infrahyoid strap muscle was divided at their insertion into the body of the hyoid. </td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">- The body of the hyoid bone was divided at approximately the lesser cornu bilaterally.</td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">- The thyroglossal duct cyst was dissected, and the tract was followed up to the foramen cecum and then suture ligation was done.</td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">- Bleeding was stopped</td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">
                        <input type="checkbox" name="check1" id="check1" <?php if($result2['sis_pd']=='on'){?> checked <?php } else{echo '';}; ?>>
                        <label for="check1">Penrose drain/</label>
                        <input type="checkbox" name="check2" id="check2" <?php if($result2['sis_rdwp']=='on'){?> checked <?php } else{echo '';}; ?>>
                        <label for="check2">Radivac drain was placed.</label>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">- Incision was close layer by layer.</td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">
                        - EBL
                        <input class="w4" type="text" name="text21" id="" value="<?php echo $result2['sis_ebl'];?>">
                        cc
                    </td>
                </tr>
                <tr>
                    <td>Special note</td>
                    <td colspan="4"><input type="text" name="text22" id="" value="<?php echo $result2['sis_special'];?>"></td>
                </tr>
                <tr>
                    <td>Pathologic request exam</td>
                    <td colspan="4">
                        <input type="checkbox" name="check3" id="check3" <?php if($result2['sis_yes']=='on'){?> checked <?php } else{echo '';}; ?>>
                        <label for="check3">Yes</label>
                        <input type="checkbox" name="check4" id="check4" <?php if($result2['sis_no']=='on'){?> checked <?php } else{echo '';}; ?>>
                        <label for="check4">No</label>
                        Surgeon <input class="w7" type="text" name="text23" id="" value="<?php echo $result2['sis_surr'];?>">
                    </td>
                </tr>
            </table>
            <table class="alt">
                <tr>
                    <td>Name of patient : <input name="input7" type="text" value="<?php echo $result["pname"] . '' . $result["fname"] . ' ' . $result["lname"]; ?>" disabled></td>
                    <td>Age : <input name="input8" type="text" value="<?php echo $result["age_y"] . 'ปี ' . $result["age_m"] . 'เดือน ' . $result["age_d"] . 'วัน'; ?>" disabled></td>
                    <td>Hospital number : <input name="input8" type="text" value="<?php echo $result["hcode"]; ?>" disabled></td>
                </tr>
                <tr>
                    <td>Department of service <input name="input7" type="text" value="<?php echo $result['SPCLTY_NAME']; ?>"></td>
                    <td>Ward : <input name="input7" type="text" value="<?php echo $result["WARD_NAME"]; ?>" disabled></td>
                    <td>AN : <input name="input7" type="text" value="<?php echo $result["an"]; ?>" disabled></td>
                </tr>
            </table>
        </section>
        <input style="float:right !important;" class="Primary" type="submit" value="แก้ไข">
        <br><br>
    </div>
</form>

<?php }  

?>