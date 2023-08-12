<?php
        require_once '../include/Session.php';
        Session::checkLoginSessionAndShowMessage(); //เช็ค session
        // SessionManager::checkPermissionAndShowMessage('KPHIS_ACCESS_IPD_NURSE_ADMISSION_NOTE');
        //ตรวจสอบ sessions
      /*  if(!(Session::checkPermission('IPD_NURSE_ADDMISSION_NOTE','EDIT'))){
            return;
        }*/

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
        $sql = "SELECT * FROM ".DbConstant::KPHIS_DBNAME.".prs_or_complication WHERE an = :an AND id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute($query_parameters);
        $rowCount = 0;
        $row = $stmt->fetch();
        $an = $row['an'];
$create_datetime = $row['create_datetime'];
$id = $row['id'];
$no1 = $row['no1'];
$no10 = $row['no10'];
$no10_2 = $row['no10_2'];
$no10_3 = $row['no10_3'];
$no11 = $row['no11'];
$no11_2 = $row['no11_2'];
$no11_3 = $row['no11_3'];
$no12 = $row['no12'];
$no12_2 = $row['no12_2'];
$no12_3 = $row['no12_3'];
$no13 = $row['no13'];
$no13_2 = $row['no13_2'];
$no13_3 = $row['no13_3'];
$no14 = $row['no14'];
$no14_2 = $row['no14_2'];
$no14_3 = $row['no14_3'];
$no15 = $row['no15'];
$no15_2 = $row['no15_2'];
$no15_3 = $row['no15_3'];
$no16 = $row['no16'];
$no16_2 = $row['no16_2'];
$no16_3 = $row['no16_3'];
$no17 = $row['no17'];
$no17_2 = $row['no17_2'];
$no17_3 = $row['no17_3'];
$no18 = $row['no18'];
$no18_2 = $row['no18_2'];
$no18_3 = $row['no18_3'];
$no19 = $row['no19'];
$no19_1 = $row['no19_1'];
$no19_1_2 = $row['no19_1_2'];
$no19_1_3 = $row['no19_1_3'];
$no19_2 = $row['no19_2'];
$no19_3 = $row['no19_3'];
$no1_2 = $row['no1_2'];
$no1_3 = $row['no1_3'];
$no2 = $row['no2'];
$no20 = $row['no20'];
$no20_2 = $row['no20_2'];
$no20_3 = $row['no20_3'];
$no21 = $row['no21'];
$no21_2 = $row['no21_2'];
$no21_3 = $row['no21_3'];
$no22 = $row['no22'];
$no22_2 = $row['no22_2'];
$no22_3 = $row['no22_3'];
$no23 = $row['no23'];
$no23_2 = $row['no23_2'];
$no23_3 = $row['no23_3'];
$no24 = $row['no24'];
$no24_2 = $row['no24_2'];
$no24_3 = $row['no24_3'];
$no25 = $row['no25'];
$no25_2 = $row['no25_2'];
$no25_3 = $row['no25_3'];
$no26 = $row['no26'];
$no26_2 = $row['no26_2'];
$no26_3 = $row['no26_3'];
$no27 = $row['no27'];
$no27_2 = $row['no27_2'];
$no27_3 = $row['no27_3'];
$no28 = $row['no28'];
$no28_2 = $row['no28_2'];
$no28_3 = $row['no28_3'];
$no29 = $row['no29'];
$no29_2 = $row['no29_2'];
$no29_3 = $row['no29_3'];
$no2_2 = $row['no2_2'];
$no2_3 = $row['no2_3'];
$no3 = $row['no3'];
$no30 = $row['no30'];
$no30_2 = $row['no30_2'];
$no30_3 = $row['no30_3'];
$no31 = $row['no31'];
$no31_2 = $row['no31_2'];
$no31_3 = $row['no31_3'];
$no32 = $row['no32'];
$no32_2 = $row['no32_2'];
$no32_3 = $row['no32_3'];
$no33 = $row['no33'];
$no33_2 = $row['no33_2'];
$no33_3 = $row['no33_3'];
$no34 = $row['no34'];
$no34_2 = $row['no34_2'];
$no34_3 = $row['no34_3'];
$no35 = $row['no35'];
$no35_2 = $row['no35_2'];
$no35_3 = $row['no35_3'];
$no36 = $row['no36'];
$no36_2 = $row['no36_2'];
$no36_3 = $row['no36_3'];
$no37 = $row['no37'];
$no37_2 = $row['no37_2'];
$no37_3 = $row['no37_3'];
$no38 = $row['no38'];
$no38_2 = $row['no38_2'];
$no38_3 = $row['no38_3'];
$no39 = $row['no39'];
$no39_2 = $row['no39_2'];
$no39_3 = $row['no39_3'];
$no3_2 = $row['no3_2'];
$no3_3 = $row['no3_3'];
$no4 = $row['no4'];
$no40 = $row['no40'];
$no40_2 = $row['no40_2'];
$no40_3 = $row['no40_3'];
$no41 = $row['no41'];
$no41_2 = $row['no41_2'];
$no41_3 = $row['no41_3'];
$no42 = $row['no42'];
$no42_2 = $row['no42_2'];
$no42_2_1 = $row['no42_2_1'];
$no42_2_2 = $row['no42_2_2'];
$no42_2_3 = $row['no42_2_3'];
$no42_3 = $row['no42_3'];
$no42_3_1 = $row['no42_3_1'];
$no42_3_2 = $row['no42_3_2'];
$no42_3_3 = $row['no42_3_3'];
$no43 = $row['no43'];
$no43_2 = $row['no43_2'];
$no43_3 = $row['no43_3'];
$no43_text = $row['no43_text'];
$no44 = $row['no44'];
$no44_2 = $row['no44_2'];
$no44_3 = $row['no44_3'];
$no45 = $row['no45'];
$no45_2 = $row['no45_2'];
$no45_3 = $row['no45_3'];
$no46 = $row['no46'];
$no46_2 = $row['no46_2'];
$no46_3 = $row['no46_3'];
$no47 = $row['no47'];
$no47_2 = $row['no47_2'];
$no47_3 = $row['no47_3'];
$no47_text = $row['no47_text'];
$no48 = $row['no48'];
$no48_1_text = $row['no48_1_text'];
$no48_2 = $row['no48_2'];
$no48_2_text = $row['no48_2_text'];
$no48_3 = $row['no48_3'];
$no48_3_text = $row['no48_3_text'];
$no49 = $row['no49'];
$no49_2 = $row['no49_2'];
$no49_3 = $row['no49_3'];
$no4_2 = $row['no4_2'];
$no4_3 = $row['no4_3'];
$no5 = $row['no5'];
$no50 = $row['no50'];
$no50_2 = $row['no50_2'];
$no50_3 = $row['no50_3'];
$no51 = $row['no51'];
$no51_text = $row['no51_text'];
$no5_2 = $row['no5_2'];
$no5_3 = $row['no5_3'];
$no6 = $row['no6'];
$no6_2 = $row['no6_2'];
$no6_3 = $row['no6_3'];
$no7 = $row['no7'];
$no7_2 = $row['no7_2'];
$no7_3 = $row['no7_3'];
$no8 = $row['no8'];
$no8_2 = $row['no8_2'];
$no8_3 = $row['no8_3'];
$no9 = $row['no9'];
$no9_2 = $row['no9_2'];
$no9_3 = $row['no9_3'];
$nurse_name = $row['nurse_name'];
$nurse_name2 = $row['nurse_name2'];
$nurse_name3 = $row['nurse_name3'];
$update_datetime = $row['update_datetime'];
$version = $row['version'];
                   
        ?>
<script>
    $("#or_complication_form").each(function() {
        $("input[name=version]").val(<?=json_encode($version)?>);

        //ความรู้สึกตัว
       /* var concious = <?=json_encode($concious)?>;
        if(concious == "รู้สึกตัวดี"){
            $("#concious1").attr('checked',true);
        }else if(concious == "สับสน"){
            $("#concious2").attr('checked',true);
        }else if(concious == "ง่วงซึม"){
            $("#concious3").attr('checked',true);
        }else if(concious == "ไม่รู้สึกตัว"){
            $("#concious4").attr('checked',true);
        }*/

        //ลักษณะการหายใจ
        var no1 = <?=json_encode($no1)?>;if(no1== "Y"){$("#no1").attr('checked',true);}else{$("#no1").attr('checked',false);}
var no10 = <?=json_encode($no10)?>;if(no10== "Y"){$("#no10").attr('checked',true);}else{$("#no10").attr('checked',false);}
var no10_2 = <?=json_encode($no10_2)?>;if(no10_2== "Y"){$("#no10_2").attr('checked',true);}else{$("#no10_2").attr('checked',false);}
var no10_3 = <?=json_encode($no10_3)?>;if(no10_3== "Y"){$("#no10_3").attr('checked',true);}else{$("#no10_3").attr('checked',false);}
var no11 = <?=json_encode($no11)?>;if(no11== "Y"){$("#no11").attr('checked',true);}else{$("#no11").attr('checked',false);}
var no11_2 = <?=json_encode($no11_2)?>;if(no11_2== "Y"){$("#no11_2").attr('checked',true);}else{$("#no11_2").attr('checked',false);}
var no11_3 = <?=json_encode($no11_3)?>;if(no11_3== "Y"){$("#no11_3").attr('checked',true);}else{$("#no11_3").attr('checked',false);}
var no12 = <?=json_encode($no12)?>;if(no12== "Y"){$("#no12").attr('checked',true);}else{$("#no12").attr('checked',false);}
var no12_2 = <?=json_encode($no12_2)?>;if(no12_2== "Y"){$("#no12_2").attr('checked',true);}else{$("#no12_2").attr('checked',false);}
var no12_3 = <?=json_encode($no12_3)?>;if(no12_3== "Y"){$("#no12_3").attr('checked',true);}else{$("#no12_3").attr('checked',false);}
var no13 = <?=json_encode($no13)?>;if(no13== "Y"){$("#no13").attr('checked',true);}else{$("#no13").attr('checked',false);}
var no13_2 = <?=json_encode($no13_2)?>;if(no13_2== "Y"){$("#no13_2").attr('checked',true);}else{$("#no13_2").attr('checked',false);}
var no13_3 = <?=json_encode($no13_3)?>;if(no13_3== "Y"){$("#no13_3").attr('checked',true);}else{$("#no13_3").attr('checked',false);}
var no14 = <?=json_encode($no14)?>;if(no14== "Y"){$("#no14").attr('checked',true);}else{$("#no14").attr('checked',false);}
var no14_2 = <?=json_encode($no14_2)?>;if(no14_2== "Y"){$("#no14_2").attr('checked',true);}else{$("#no14_2").attr('checked',false);}
var no14_3 = <?=json_encode($no14_3)?>;if(no14_3== "Y"){$("#no14_3").attr('checked',true);}else{$("#no14_3").attr('checked',false);}
var no15 = <?=json_encode($no15)?>;if(no15== "Y"){$("#no15").attr('checked',true);}else{$("#no15").attr('checked',false);}
var no15_2 = <?=json_encode($no15_2)?>;if(no15_2== "Y"){$("#no15_2").attr('checked',true);}else{$("#no15_2").attr('checked',false);}
var no15_3 = <?=json_encode($no15_3)?>;if(no15_3== "Y"){$("#no15_3").attr('checked',true);}else{$("#no15_3").attr('checked',false);}
var no16 = <?=json_encode($no16)?>;if(no16== "Y"){$("#no16").attr('checked',true);}else{$("#no16").attr('checked',false);}
var no16_2 = <?=json_encode($no16_2)?>;if(no16_2== "Y"){$("#no16_2").attr('checked',true);}else{$("#no16_2").attr('checked',false);}
var no16_3 = <?=json_encode($no16_3)?>;if(no16_3== "Y"){$("#no16_3").attr('checked',true);}else{$("#no16_3").attr('checked',false);}
var no17 = <?=json_encode($no17)?>;if(no17== "Y"){$("#no17").attr('checked',true);}else{$("#no17").attr('checked',false);}
var no17_2 = <?=json_encode($no17_2)?>;if(no17_2== "Y"){$("#no17_2").attr('checked',true);}else{$("#no17_2").attr('checked',false);}
var no17_3 = <?=json_encode($no17_3)?>;if(no17_3== "Y"){$("#no17_3").attr('checked',true);}else{$("#no17_3").attr('checked',false);}
var no18 = <?=json_encode($no18)?>;if(no18== "Y"){$("#no18").attr('checked',true);}else{$("#no18").attr('checked',false);}
var no18_2 = <?=json_encode($no18_2)?>;if(no18_2== "Y"){$("#no18_2").attr('checked',true);}else{$("#no18_2").attr('checked',false);}
var no18_3 = <?=json_encode($no18_3)?>;if(no18_3== "Y"){$("#no18_3").attr('checked',true);}else{$("#no18_3").attr('checked',false);}
var no19 = <?=json_encode($no19)?>;if(no19== "Y"){$("#no19").attr('checked',true);}else{$("#no19").attr('checked',false);}
var no19_1 = <?=json_encode($no19_1)?>;if(no19_1== "Y"){$("#no19_1").attr('checked',true);}else{$("#no19_1").attr('checked',false);}
var no19_1_2 = <?=json_encode($no19_1_2)?>;if(no19_1_2== "Y"){$("#no19_1_2").attr('checked',true);}else{$("#no19_1_2").attr('checked',false);}
var no19_1_3 = <?=json_encode($no19_1_3)?>;if(no19_1_3== "Y"){$("#no19_1_3").attr('checked',true);}else{$("#no19_1_3").attr('checked',false);}
var no19_2 = <?=json_encode($no19_2)?>;if(no19_2== "Y"){$("#no19_2").attr('checked',true);}else{$("#no19_2").attr('checked',false);}
var no19_3 = <?=json_encode($no19_3)?>;if(no19_3== "Y"){$("#no19_3").attr('checked',true);}else{$("#no19_3").attr('checked',false);}
var no1_2 = <?=json_encode($no1_2)?>;if(no1_2== "Y"){$("#no1_2").attr('checked',true);}else{$("#no1_2").attr('checked',false);}
var no1_3 = <?=json_encode($no1_3)?>;if(no1_3== "Y"){$("#no1_3").attr('checked',true);}else{$("#no1_3").attr('checked',false);}
var no2 = <?=json_encode($no2)?>;if(no2== "Y"){$("#no2").attr('checked',true);}else{$("#no2").attr('checked',false);}
var no20 = <?=json_encode($no20)?>;if(no20== "Y"){$("#no20").attr('checked',true);}else{$("#no20").attr('checked',false);}
var no20_2 = <?=json_encode($no20_2)?>;if(no20_2== "Y"){$("#no20_2").attr('checked',true);}else{$("#no20_2").attr('checked',false);}
var no20_3 = <?=json_encode($no20_3)?>;if(no20_3== "Y"){$("#no20_3").attr('checked',true);}else{$("#no20_3").attr('checked',false);}
var no21 = <?=json_encode($no21)?>;if(no21== "Y"){$("#no21").attr('checked',true);}else{$("#no21").attr('checked',false);}
var no21_2 = <?=json_encode($no21_2)?>;if(no21_2== "Y"){$("#no21_2").attr('checked',true);}else{$("#no21_2").attr('checked',false);}
var no21_3 = <?=json_encode($no21_3)?>;if(no21_3== "Y"){$("#no21_3").attr('checked',true);}else{$("#no21_3").attr('checked',false);}
var no22 = <?=json_encode($no22)?>;if(no22== "Y"){$("#no22").attr('checked',true);}else{$("#no22").attr('checked',false);}
var no22_2 = <?=json_encode($no22_2)?>;if(no22_2== "Y"){$("#no22_2").attr('checked',true);}else{$("#no22_2").attr('checked',false);}
var no22_3 = <?=json_encode($no22_3)?>;if(no22_3== "Y"){$("#no22_3").attr('checked',true);}else{$("#no22_3").attr('checked',false);}
var no23 = <?=json_encode($no23)?>;if(no23== "Y"){$("#no23").attr('checked',true);}else{$("#no23").attr('checked',false);}
var no23_2 = <?=json_encode($no23_2)?>;if(no23_2== "Y"){$("#no23_2").attr('checked',true);}else{$("#no23_2").attr('checked',false);}
var no23_3 = <?=json_encode($no23_3)?>;if(no23_3== "Y"){$("#no23_3").attr('checked',true);}else{$("#no23_3").attr('checked',false);}
var no24 = <?=json_encode($no24)?>;if(no24== "Y"){$("#no24").attr('checked',true);}else{$("#no24").attr('checked',false);}
var no24_2 = <?=json_encode($no24_2)?>;if(no24_2== "Y"){$("#no24_2").attr('checked',true);}else{$("#no24_2").attr('checked',false);}
var no24_3 = <?=json_encode($no24_3)?>;if(no24_3== "Y"){$("#no24_3").attr('checked',true);}else{$("#no24_3").attr('checked',false);}
var no25 = <?=json_encode($no25)?>;if(no25== "Y"){$("#no25").attr('checked',true);}else{$("#no25").attr('checked',false);}
var no25_2 = <?=json_encode($no25_2)?>;if(no25_2== "Y"){$("#no25_2").attr('checked',true);}else{$("#no25_2").attr('checked',false);}
var no25_3 = <?=json_encode($no25_3)?>;if(no25_3== "Y"){$("#no25_3").attr('checked',true);}else{$("#no25_3").attr('checked',false);}
var no26 = <?=json_encode($no26)?>;if(no26== "Y"){$("#no26").attr('checked',true);}else{$("#no26").attr('checked',false);}
var no26_2 = <?=json_encode($no26_2)?>;if(no26_2== "Y"){$("#no26_2").attr('checked',true);}else{$("#no26_2").attr('checked',false);}
var no26_3 = <?=json_encode($no26_3)?>;if(no26_3== "Y"){$("#no26_3").attr('checked',true);}else{$("#no26_3").attr('checked',false);}
var no27 = <?=json_encode($no27)?>;if(no27== "Y"){$("#no27").attr('checked',true);}else{$("#no27").attr('checked',false);}
var no27_2 = <?=json_encode($no27_2)?>;if(no27_2== "Y"){$("#no27_2").attr('checked',true);}else{$("#no27_2").attr('checked',false);}
var no27_3 = <?=json_encode($no27_3)?>;if(no27_3== "Y"){$("#no27_3").attr('checked',true);}else{$("#no27_3").attr('checked',false);}
var no28 = <?=json_encode($no28)?>;if(no28== "Y"){$("#no28").attr('checked',true);}else{$("#no28").attr('checked',false);}
var no28_2 = <?=json_encode($no28_2)?>;if(no28_2== "Y"){$("#no28_2").attr('checked',true);}else{$("#no28_2").attr('checked',false);}
var no28_3 = <?=json_encode($no28_3)?>;if(no28_3== "Y"){$("#no28_3").attr('checked',true);}else{$("#no28_3").attr('checked',false);}
var no29 = <?=json_encode($no29)?>;if(no29== "Y"){$("#no29").attr('checked',true);}else{$("#no29").attr('checked',false);}
var no29_2 = <?=json_encode($no29_2)?>;if(no29_2== "Y"){$("#no29_2").attr('checked',true);}else{$("#no29_2").attr('checked',false);}
var no29_3 = <?=json_encode($no29_3)?>;if(no29_3== "Y"){$("#no29_3").attr('checked',true);}else{$("#no29_3").attr('checked',false);}
var no2_2 = <?=json_encode($no2_2)?>;if(no2_2== "Y"){$("#no2_2").attr('checked',true);}else{$("#no2_2").attr('checked',false);}
var no2_3 = <?=json_encode($no2_3)?>;if(no2_3== "Y"){$("#no2_3").attr('checked',true);}else{$("#no2_3").attr('checked',false);}
var no3 = <?=json_encode($no3)?>;if(no3== "Y"){$("#no3").attr('checked',true);}else{$("#no3").attr('checked',false);}
var no30 = <?=json_encode($no30)?>;if(no30== "Y"){$("#no30").attr('checked',true);}else{$("#no30").attr('checked',false);}
var no30_2 = <?=json_encode($no30_2)?>;if(no30_2== "Y"){$("#no30_2").attr('checked',true);}else{$("#no30_2").attr('checked',false);}
var no30_3 = <?=json_encode($no30_3)?>;if(no30_3== "Y"){$("#no30_3").attr('checked',true);}else{$("#no30_3").attr('checked',false);}
var no31 = <?=json_encode($no31)?>;if(no31== "Y"){$("#no31").attr('checked',true);}else{$("#no31").attr('checked',false);}
var no31_2 = <?=json_encode($no31_2)?>;if(no31_2== "Y"){$("#no31_2").attr('checked',true);}else{$("#no31_2").attr('checked',false);}
var no31_3 = <?=json_encode($no31_3)?>;if(no31_3== "Y"){$("#no31_3").attr('checked',true);}else{$("#no31_3").attr('checked',false);}
var no32 = <?=json_encode($no32)?>;if(no32== "Y"){$("#no32").attr('checked',true);}else{$("#no32").attr('checked',false);}
var no32_2 = <?=json_encode($no32_2)?>;if(no32_2== "Y"){$("#no32_2").attr('checked',true);}else{$("#no32_2").attr('checked',false);}
var no32_3 = <?=json_encode($no32_3)?>;if(no32_3== "Y"){$("#no32_3").attr('checked',true);}else{$("#no32_3").attr('checked',false);}
var no33 = <?=json_encode($no33)?>;if(no33== "Y"){$("#no33").attr('checked',true);}else{$("#no33").attr('checked',false);}
var no33_2 = <?=json_encode($no33_2)?>;if(no33_2== "Y"){$("#no33_2").attr('checked',true);}else{$("#no33_2").attr('checked',false);}
var no33_3 = <?=json_encode($no33_3)?>;if(no33_3== "Y"){$("#no33_3").attr('checked',true);}else{$("#no33_3").attr('checked',false);}
var no34 = <?=json_encode($no34)?>;if(no34== "Y"){$("#no34").attr('checked',true);}else{$("#no34").attr('checked',false);}
var no34_2 = <?=json_encode($no34_2)?>;if(no34_2== "Y"){$("#no34_2").attr('checked',true);}else{$("#no34_2").attr('checked',false);}
var no34_3 = <?=json_encode($no34_3)?>;if(no34_3== "Y"){$("#no34_3").attr('checked',true);}else{$("#no34_3").attr('checked',false);}
var no35 = <?=json_encode($no35)?>;if(no35== "Y"){$("#no35").attr('checked',true);}else{$("#no35").attr('checked',false);}
var no35_2 = <?=json_encode($no35_2)?>;if(no35_2== "Y"){$("#no35_2").attr('checked',true);}else{$("#no35_2").attr('checked',false);}
var no35_3 = <?=json_encode($no35_3)?>;if(no35_3== "Y"){$("#no35_3").attr('checked',true);}else{$("#no35_3").attr('checked',false);}
var no36 = <?=json_encode($no36)?>;if(no36== "Y"){$("#no36").attr('checked',true);}else{$("#no36").attr('checked',false);}
var no36_2 = <?=json_encode($no36_2)?>;if(no36_2== "Y"){$("#no36_2").attr('checked',true);}else{$("#no36_2").attr('checked',false);}
var no36_3 = <?=json_encode($no36_3)?>;if(no36_3== "Y"){$("#no36_3").attr('checked',true);}else{$("#no36_3").attr('checked',false);}
var no37 = <?=json_encode($no37)?>;if(no37== "Y"){$("#no37").attr('checked',true);}else{$("#no37").attr('checked',false);}
var no37_2 = <?=json_encode($no37_2)?>;if(no37_2== "Y"){$("#no37_2").attr('checked',true);}else{$("#no37_2").attr('checked',false);}
var no37_3 = <?=json_encode($no37_3)?>;if(no37_3== "Y"){$("#no37_3").attr('checked',true);}else{$("#no37_3").attr('checked',false);}
var no38 = <?=json_encode($no38)?>;if(no38== "Y"){$("#no38").attr('checked',true);}else{$("#no38").attr('checked',false);}
var no38_2 = <?=json_encode($no38_2)?>;if(no38_2== "Y"){$("#no38_2").attr('checked',true);}else{$("#no38_2").attr('checked',false);}
var no38_3 = <?=json_encode($no38_3)?>;if(no38_3== "Y"){$("#no38_3").attr('checked',true);}else{$("#no38_3").attr('checked',false);}
var no39 = <?=json_encode($no39)?>;if(no39== "Y"){$("#no39").attr('checked',true);}else{$("#no39").attr('checked',false);}
var no39_2 = <?=json_encode($no39_2)?>;if(no39_2== "Y"){$("#no39_2").attr('checked',true);}else{$("#no39_2").attr('checked',false);}
var no39_3 = <?=json_encode($no39_3)?>;if(no39_3== "Y"){$("#no39_3").attr('checked',true);}else{$("#no39_3").attr('checked',false);}
var no3_2 = <?=json_encode($no3_2)?>;if(no3_2== "Y"){$("#no3_2").attr('checked',true);}else{$("#no3_2").attr('checked',false);}
var no3_3 = <?=json_encode($no3_3)?>;if(no3_3== "Y"){$("#no3_3").attr('checked',true);}else{$("#no3_3").attr('checked',false);}
var no4 = <?=json_encode($no4)?>;if(no4== "Y"){$("#no4").attr('checked',true);}else{$("#no4").attr('checked',false);}
var no40 = <?=json_encode($no40)?>;if(no40== "Y"){$("#no40").attr('checked',true);}else{$("#no40").attr('checked',false);}
var no40_2 = <?=json_encode($no40_2)?>;if(no40_2== "Y"){$("#no40_2").attr('checked',true);}else{$("#no40_2").attr('checked',false);}
var no40_3 = <?=json_encode($no40_3)?>;if(no40_3== "Y"){$("#no40_3").attr('checked',true);}else{$("#no40_3").attr('checked',false);}
var no41 = <?=json_encode($no41)?>;if(no41== "Y"){$("#no41").attr('checked',true);}else{$("#no41").attr('checked',false);}
var no41_2 = <?=json_encode($no41_2)?>;if(no41_2== "Y"){$("#no41_2").attr('checked',true);}else{$("#no41_2").attr('checked',false);}
var no41_3 = <?=json_encode($no41_3)?>;if(no41_3== "Y"){$("#no41_3").attr('checked',true);}else{$("#no41_3").attr('checked',false);}
var no42 = <?=json_encode($no42)?>;if(no42== "Y"){$("#no42").attr('checked',true);}else{$("#no42").attr('checked',false);}
var no42_2 = <?=json_encode($no42_2)?>;if(no42_2== "Y"){$("#no42_2").attr('checked',true);}else{$("#no42_2").attr('checked',false);}
var no42_2_1 = <?=json_encode($no42_2_1)?>;if(no42_2_1== "Y"){$("#no42_2_1").attr('checked',true);}else{$("#no42_2_1").attr('checked',false);}
var no42_2_2 = <?=json_encode($no42_2_2)?>;if(no42_2_2== "Y"){$("#no42_2_2").attr('checked',true);}else{$("#no42_2_2").attr('checked',false);}
var no42_2_3 = <?=json_encode($no42_2_3)?>;if(no42_2_3== "Y"){$("#no42_2_3").attr('checked',true);}else{$("#no42_2_3").attr('checked',false);}
var no42_3 = <?=json_encode($no42_3)?>;if(no42_3== "Y"){$("#no42_3").attr('checked',true);}else{$("#no42_3").attr('checked',false);}
var no42_3_1 = <?=json_encode($no42_3_1)?>;if(no42_3_1== "Y"){$("#no42_3_1").attr('checked',true);}else{$("#no42_3_1").attr('checked',false);}
var no42_3_2 = <?=json_encode($no42_3_2)?>;if(no42_3_2== "Y"){$("#no42_3_2").attr('checked',true);}else{$("#no42_3_2").attr('checked',false);}
var no42_3_3 = <?=json_encode($no42_3_3)?>;if(no42_3_3== "Y"){$("#no42_3_3").attr('checked',true);}else{$("#no42_3_3").attr('checked',false);}
var no43 = <?=json_encode($no43)?>;if(no43== "Y"){$("#no43").attr('checked',true);}else{$("#no43").attr('checked',false);}
var no43_2 = <?=json_encode($no43_2)?>;if(no43_2== "Y"){$("#no43_2").attr('checked',true);}else{$("#no43_2").attr('checked',false);}
var no43_3 = <?=json_encode($no43_3)?>;if(no43_3== "Y"){$("#no43_3").attr('checked',true);}else{$("#no43_3").attr('checked',false);}
var no44 = <?=json_encode($no44)?>;if(no44== "Y"){$("#no44").attr('checked',true);}else{$("#no44").attr('checked',false);}
var no44_2 = <?=json_encode($no44_2)?>;if(no44_2== "Y"){$("#no44_2").attr('checked',true);}else{$("#no44_2").attr('checked',false);}
var no44_3 = <?=json_encode($no44_3)?>;if(no44_3== "Y"){$("#no44_3").attr('checked',true);}else{$("#no44_3").attr('checked',false);}
var no45 = <?=json_encode($no45)?>;if(no45== "Y"){$("#no45").attr('checked',true);}else{$("#no45").attr('checked',false);}
var no45_2 = <?=json_encode($no45_2)?>;if(no45_2== "Y"){$("#no45_2").attr('checked',true);}else{$("#no45_2").attr('checked',false);}
var no45_3 = <?=json_encode($no45_3)?>;if(no45_3== "Y"){$("#no45_3").attr('checked',true);}else{$("#no45_3").attr('checked',false);}
var no46 = <?=json_encode($no46)?>;if(no46== "Y"){$("#no46").attr('checked',true);}else{$("#no46").attr('checked',false);}
var no46_2 = <?=json_encode($no46_2)?>;if(no46_2== "Y"){$("#no46_2").attr('checked',true);}else{$("#no46_2").attr('checked',false);}
var no46_3 = <?=json_encode($no46_3)?>;if(no46_3== "Y"){$("#no46_3").attr('checked',true);}else{$("#no46_3").attr('checked',false);}
var no47 = <?=json_encode($no47)?>;if(no47== "Y"){$("#no47").attr('checked',true);}else{$("#no47").attr('checked',false);}
var no47_2 = <?=json_encode($no47_2)?>;if(no47_2== "Y"){$("#no47_2").attr('checked',true);}else{$("#no47_2").attr('checked',false);}
var no47_3 = <?=json_encode($no47_3)?>;if(no47_3== "Y"){$("#no47_3").attr('checked',true);}else{$("#no47_3").attr('checked',false);}
var no48 = <?=json_encode($no48)?>;if(no48== "Y"){$("#no48").attr('checked',true);}else{$("#no48").attr('checked',false);}
var no48_2 = <?=json_encode($no48_2)?>;if(no48_2== "Y"){$("#no48_2").attr('checked',true);}else{$("#no48_2").attr('checked',false);}
var no48_3 = <?=json_encode($no48_3)?>;if(no48_3== "Y"){$("#no48_3").attr('checked',true);}else{$("#no48_3").attr('checked',false);}
var no49 = <?=json_encode($no49)?>;if(no49== "Y"){$("#no49").attr('checked',true);}else{$("#no49").attr('checked',false);}
var no49_2 = <?=json_encode($no49_2)?>;if(no49_2== "Y"){$("#no49_2").attr('checked',true);}else{$("#no49_2").attr('checked',false);}
var no49_3 = <?=json_encode($no49_3)?>;if(no49_3== "Y"){$("#no49_3").attr('checked',true);}else{$("#no49_3").attr('checked',false);}
var no4_2 = <?=json_encode($no4_2)?>;if(no4_2== "Y"){$("#no4_2").attr('checked',true);}else{$("#no4_2").attr('checked',false);}
var no4_3 = <?=json_encode($no4_3)?>;if(no4_3== "Y"){$("#no4_3").attr('checked',true);}else{$("#no4_3").attr('checked',false);}
var no5 = <?=json_encode($no5)?>;if(no5== "Y"){$("#no5").attr('checked',true);}else{$("#no5").attr('checked',false);}
var no50 = <?=json_encode($no50)?>;if(no50== "Y"){$("#no50").attr('checked',true);}else{$("#no50").attr('checked',false);}
var no50_2 = <?=json_encode($no50_2)?>;if(no50_2== "Y"){$("#no50_2").attr('checked',true);}else{$("#no50_2").attr('checked',false);}
var no50_3 = <?=json_encode($no50_3)?>;if(no50_3== "Y"){$("#no50_3").attr('checked',true);}else{$("#no50_3").attr('checked',false);}
var no51 = <?=json_encode($no51)?>;if(no51== "Y"){$("#no51").attr('checked',true);}else{$("#no51").attr('checked',false);}
var no5_2 = <?=json_encode($no5_2)?>;if(no5_2== "Y"){$("#no5_2").attr('checked',true);}else{$("#no5_2").attr('checked',false);}
var no5_3 = <?=json_encode($no5_3)?>;if(no5_3== "Y"){$("#no5_3").attr('checked',true);}else{$("#no5_3").attr('checked',false);}
var no6 = <?=json_encode($no6)?>;if(no6== "Y"){$("#no6").attr('checked',true);}else{$("#no6").attr('checked',false);}
var no6_2 = <?=json_encode($no6_2)?>;if(no6_2== "Y"){$("#no6_2").attr('checked',true);}else{$("#no6_2").attr('checked',false);}
var no6_3 = <?=json_encode($no6_3)?>;if(no6_3== "Y"){$("#no6_3").attr('checked',true);}else{$("#no6_3").attr('checked',false);}
var no7 = <?=json_encode($no7)?>;if(no7== "Y"){$("#no7").attr('checked',true);}else{$("#no7").attr('checked',false);}
var no7_2 = <?=json_encode($no7_2)?>;if(no7_2== "Y"){$("#no7_2").attr('checked',true);}else{$("#no7_2").attr('checked',false);}
var no7_3 = <?=json_encode($no7_3)?>;if(no7_3== "Y"){$("#no7_3").attr('checked',true);}else{$("#no7_3").attr('checked',false);}
var no8 = <?=json_encode($no8)?>;if(no8== "Y"){$("#no8").attr('checked',true);}else{$("#no8").attr('checked',false);}
var no8_2 = <?=json_encode($no8_2)?>;if(no8_2== "Y"){$("#no8_2").attr('checked',true);}else{$("#no8_2").attr('checked',false);}
var no8_3 = <?=json_encode($no8_3)?>;if(no8_3== "Y"){$("#no8_3").attr('checked',true);}else{$("#no8_3").attr('checked',false);}
var no9 = <?=json_encode($no9)?>;if(no9== "Y"){$("#no9").attr('checked',true);}else{$("#no9").attr('checked',false);}
var no9_2 = <?=json_encode($no9_2)?>;if(no9_2== "Y"){$("#no9_2").attr('checked',true);}else{$("#no9_2").attr('checked',false);}
var no9_3 = <?=json_encode($no9_3)?>;if(no9_3== "Y"){$("#no9_3").attr('checked',true);}else{$("#no9_3").attr('checked',false);}
   

        $("input[name=no48_1_text]").val(<?=json_encode($no48_1_text)?>);
         //textarea
        $("textarea#no51_text").val(<?=json_encode($no51_text)?>);
        $("input[name=no43_text]").val(<?=json_encode($no43_text)?>);
        $("input[name=no47_text]").val(<?=json_encode($no47_text)?>);
        $("input[name=no48_text]").val(<?=json_encode($no48_text)?>);
        $("input[name=no48_2_text]").val(<?=json_encode($no48_2_text)?>);
        $("input[name=no48_3_text]").val(<?=json_encode($no48_3_text)?>);
        $("input[name=nurse_name]").val(<?=json_encode($nurse_name)?>);
        $("input[name=nurse_name2]").val(<?=json_encode($nurse_name2)?>);
        $("input[name=nurse_name3]").val(<?=json_encode($nurse_name3)?>);
  
     
    });
</script>