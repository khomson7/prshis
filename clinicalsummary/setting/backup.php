<?PHP
    include("head.php");
    include("query.php");
?>
<style>
    tr,td{
        border: 1px solid black;
        color: black !important;
    }
</style>
<div id="contrainer2">
    <table>
        <tr>
            <td width="150">สิทธิรักษา</td>
            <td width="400"></td>
            <td width="500" rowspan="4" align="center" style="padding-top:10px;"><img src="setting/pic/image.jpg" width="150"></td>
            <td align="right">DISCHARGE SUMMARY SHEET</td>
        </tr>
        <tr>
            <td width="150">เลขที่</td>
            <td width="400"></td>
            <td align="right">CHAIYAPHUM HOSPITAL</td>
        </tr>
        <tr>
            <td width="150">วันออกบัตร</td>
            <td width="400"></td>
            <td width="500"></td>
        </tr>
        <tr>
            <td width="150">วันหมดอายุ</td>
            <td width="400"></td>
            <td width="500"></td>
        </tr>
    </table>
    <br>
    <table>
        <tr>
            <td class="head2">AN</td>
            <td class="data">650015108</td>
            <td class="head">ID NO.</td>
            <td class="data"><?php echo $result["CID_CODE"]?></td>
            <td class="head2">HN</td>
            <td class="data"><?php echo $result["hn"]?></td>
        </tr>
    </table>
    <table>
        <tr>
            <td>ชื่อผู้ป่วย</td>
            <td><?php echo $result["INFORMNAME"]?></td>
            <td>วันเดือนปีเกิด</td>
            <td><?php echo $result["birthday"]?></td>
            <td>อายุ</td>
            <td><?php echo $result["age_y"]." ปี ".$result["age_m"]." เดือน ".$result["age_d"]." วัน ";?></td>
            <td>เพศ</td>
            <td>BW.</td>
        </tr>
        <tr>
            <td>ที่อยู่</td>
            <td><?php echo $result["INFORMADDR"]?></td>
            <td>เบอร์โทรศัพท์</td>
            <td>อาชีพ</td>
        </tr>
        <tr>
            <td>สัญชาติ</td>
            <td>WARD</td>
            <td>Department</td>
            <td>สถานภาพ</td>
        </tr>
        <tr>
            <td>วันAdmit</td>
            <td>เวลาAdmit</td>
            <td>วันจำหน่าย</td>
            <td>เวลาจำหน่าย</td>
            <td>LOS</td>
        </tr>
    </table>
    <table>
        <tr>
            <td>ชื่อผู้ติดต่อได้</td>
            <td>ที่อยู่ผู้ติดต่อ</td>
        </tr>
        <tr>
            <td>เบอร์ผู้ติดต่อ</td>
            <td>ความสัมพันธ์</td>
        </tr>
    </table>
    <table>
        <tr>
            <td>(1)PRINCIPAL DIAGNOSIS</td>
        </tr>
        <tr>    
        <td><textarea>Some text...</textarea></td>     
    </tr>   
</table>
<table>
    <tr>
        <td>(2)COMORBIDITY  (s)</td>
    </tr>
    <tr>    
        <td><textarea>Some text...</textarea></td>     
    </tr>   
</table>
<table>
    <tr>
        <td>(3)COMPLICATION (s)</td>
    </tr>
    <tr>    
        <td><textarea>Some text...</textarea></td>     
    </tr>   
</table>
<table>
    <tr>
        <td class="tablesplite">(4)OTHER DIAGNOSIS</td>
        <td class="tablesplite">(6)Additional Code</td>
    </tr>
    <tr>    
        <td class="tablesplite"><textarea>Some text...</textarea></td>     
        <td class="tablesplite"><textarea>Some text...</textarea></td>     
    </tr>   
    <tr>
        <td class="tablesplite">(5)EXTERNAL CAUSE (s) OF INJURY</td>
        <td class="tablesplite">(7)Morphology Code</td>
    </tr>
    <tr>    
        <td class="tablesplite"><textarea>Some text...</textarea></td>     
        <td class="tablesplite"><textarea>Some text...</textarea></td>     
    </tr>   
</table>
<table class="alt">
    <tr>
        <td class="tdborder">OPERATION ROOM PROCEDURES</td>
        <td class="tdborder">DATE</td>
        <td class="tdborder">TIME IN</td>
        <td class="tdborder">TIME OUT</td>
    </tr>
    <tr>    
        <td> 1 <input type="text" class="td95"></td>     
        <td><input type="date"></td>    
        <td><input type="time"></td>    
        <td><input type="time"></td>     
    </tr>   
    <tr>    
        <td> 2 <input type="text" class="td95"></td>
        <td><input type="date"></td>    
        <td><input type="time"></td>    
        <td><input type="time"></td>          
    </tr>   
    <tr>    
        <td> 3 <input type="text" class="td95"></td>
        <td><input type="date"></td>    
        <td><input type="time"></td>    
        <td><input type="time"></td>          
    </tr>   
    <tr>    
        <td> 4 <input type="text" class="td95"></td> 
        <td class="tablesplite2"><input type="date"></td>    
        <td class="tablesplite2"><input type="time"></td>    
        <td class="tablesplite2"><input type="time"></td>         
    </tr>   
</table>
<table>
    <tr>
        <td>NON OPERAING ROOM PROCEDIRES</td>
    </tr>
    <tr>
        <td class="td95" colspan="6"><textarea>Some text...</textarea></td>
    </tr>
    <tr>
        <td>***Respirator</td>
        <td><input type="checkbox" id="check1"><label for="check1">on < 96 hrs</label></td>   
        <td><input type="checkbox" id="check2"><label for="check2">on ≥ 96 hrs</td>
        <td>***Respirator</td>    
        <td><input type="checkbox" id="check3"><label for="check3">Yes</label></td>  
        <td><input type="checkbox" id="check4"><label for="check4">No</label></td>    
    </tr>
    <tr>
        <td></td>
        <td><input type="checkbox" id="check5"><label for="check5">PRC</label></td>    
        <td><input type="checkbox" id="check6"><label for="check6">FFP</label></td>
        <td><input type="checkbox" id="check7"><label for="check7">PLT</label></td>  
    </tr>
</table>
<table>
    <tr>
    <td colspan="2">DISCHARGE STATUS</td>
    <td colspan="2">DISCHARGE TYPE</td>
    </tr>
</table>
<table>
<tr>
    <td>1.COMPLETE RECOVERY</td>
    <td>6.NORMAL CHILD DISCHARGED WITH MOTHER</td>
    <td>1.WITH APPROVAL</td>
    <td>5.OTHER</td>
    </tr>
    <tr>
    <td>2.IMPROVED</td>
    <td>7.NORMAL CHILD DISCHARGED SEPARATELY</td>
    <td>2.AGAINST ADVICE</td>
    <td>8.DEAD, AUTOPSY</td>
    </tr>
    <tr>
    <td>3.NOT IMPROVED</td>
    <td>8.STILLBIRTH</td>
    <td>3.ESCAPE</td>
    <td>9.DEAD, NO AUTOPSY</td>
    </tr>
    <tr>
    <td>4.DELIVERED</td>
    <td>9.DEAD</td>
    <td>4.BY TRANSFER</td>
    <td></td>
    </tr>
    <tr>
    <td>5.UNDELIVERED</td>
    <td></td>
    <td colspan="2">ชื่อสถานพยาบาลที่ส่งต่อ <input type="text" class="Width70"></td>
    </tr>
</table>
<table>
    <tr>
        <td align="center">IN CASE OF DEATH COMPLETE DEATH CERTIFICATE ON OTHER SIDE OF FORM     (กรณีผู้ป่วยตาย กรุณาเขียนรายละเอียดด้านหลัง)</td>
    </tr>
</table>
<div class="grid-container">
<div class="grid-item">ATTENDING</div>
  <div class="grid-item">APPROVED</div>
  <div class="grid-item"></div>
  <div class="grid-item">SIGNATURE<br>พญ.ภริตา ศิริอนันต์<br>ว45864</div>
  <div class="grid-item">by<br>SIGNATURE<br>พญ.ภริตา ศิริอนันต์<br>ว45864</div>
  <div class="grid-item">INTERNAL AUDITOR</div>
</div>
</div>