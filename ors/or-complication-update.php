<?php
        //เวลาตาม timezone
        date_default_timezone_set("Asia/Bangkok");
        require_once '../include/Session.php';
        Session::checkLoginSessionAndShowMessage(); //เช็ค session
        // SessionManager::checkPermissionAndShowMessage('KPHIS_ACCESS_IPD_NURSE_ADMISSION_NOTE');
        //ตรวจสอบสิทธ update
        /*
        if(!(Session::checkPermission('IPD_NURSE_ADDMISSION_NOTE','EDIT'))){
            return;
        }*/

        require_once '../include/DbUtils.php';
        require_once '../include/KphisQueryUtils.php';

        $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
       // $an = '660005698';
         $an = $_REQUEST['an'];
        $hn = KphisQueryUtils::getHnByAn($an);   // function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา

       
        $id = $_REQUEST['id'];
        /*$no1  = empty($_REQUEST['no1']) ? null : $_REQUEST['no1'];
        $no1_2       = empty($_REQUEST['no1_2']) ? null : $_REQUEST['no1_2'];
        $no1_3      = empty($_REQUEST['no1_3']) ? null : $_REQUEST['no1_3'];
        $no2        = empty($_REQUEST['no2']) ? null : $_REQUEST['no2'];
        $no2_2        = empty($_REQUEST['no2_2']) ? null : $_REQUEST['no2_2'];
        $no2_3        = empty($_REQUEST['no2_3']) ? null : $_REQUEST['no2_3'];
        $no43_text    = empty($_REQUEST['no43_text']) ? null : $_REQUEST['no43_text'];
        $nurse_name = empty($_REQUEST['nurse_name']) ? null : $_REQUEST['nurse_name']; */

        $no1 = empty($_REQUEST['no1']) ? null : $_REQUEST['no1'];
        $no10 = empty($_REQUEST['no10']) ? null : $_REQUEST['no10'];
        $no10_2 = empty($_REQUEST['no10_2']) ? null : $_REQUEST['no10_2'];
        $no10_3 = empty($_REQUEST['no10_3']) ? null : $_REQUEST['no10_3'];
        $no11 = empty($_REQUEST['no11']) ? null : $_REQUEST['no11'];
        $no11_2 = empty($_REQUEST['no11_2']) ? null : $_REQUEST['no11_2'];
        $no11_3 = empty($_REQUEST['no11_3']) ? null : $_REQUEST['no11_3'];
        $no12 = empty($_REQUEST['no12']) ? null : $_REQUEST['no12'];
        $no12_2 = empty($_REQUEST['no12_2']) ? null : $_REQUEST['no12_2'];
        $no12_3 = empty($_REQUEST['no12_3']) ? null : $_REQUEST['no12_3'];
        $no13 = empty($_REQUEST['no13']) ? null : $_REQUEST['no13'];
        $no13_2 = empty($_REQUEST['no13_2']) ? null : $_REQUEST['no13_2'];
        $no13_3 = empty($_REQUEST['no13_3']) ? null : $_REQUEST['no13_3'];
        $no14 = empty($_REQUEST['no14']) ? null : $_REQUEST['no14'];
        $no14_2 = empty($_REQUEST['no14_2']) ? null : $_REQUEST['no14_2'];
        $no14_3 = empty($_REQUEST['no14_3']) ? null : $_REQUEST['no14_3'];
        $no15 = empty($_REQUEST['no15']) ? null : $_REQUEST['no15'];
        $no15_2 = empty($_REQUEST['no15_2']) ? null : $_REQUEST['no15_2'];
        $no15_3 = empty($_REQUEST['no15_3']) ? null : $_REQUEST['no15_3'];
        $no16 = empty($_REQUEST['no16']) ? null : $_REQUEST['no16'];
        $no16_2 = empty($_REQUEST['no16_2']) ? null : $_REQUEST['no16_2'];
        $no16_3 = empty($_REQUEST['no16_3']) ? null : $_REQUEST['no16_3'];
        $no17 = empty($_REQUEST['no17']) ? null : $_REQUEST['no17'];
        $no17_2 = empty($_REQUEST['no17_2']) ? null : $_REQUEST['no17_2'];
        $no17_3 = empty($_REQUEST['no17_3']) ? null : $_REQUEST['no17_3'];
        $no18 = empty($_REQUEST['no18']) ? null : $_REQUEST['no18'];
        $no18_2 = empty($_REQUEST['no18_2']) ? null : $_REQUEST['no18_2'];
        $no18_3 = empty($_REQUEST['no18_3']) ? null : $_REQUEST['no18_3'];
        $no19 = empty($_REQUEST['no19']) ? null : $_REQUEST['no19'];
        $no19_1 = empty($_REQUEST['no19_1']) ? null : $_REQUEST['no19_1'];
        $no19_1_2 = empty($_REQUEST['no19_1_2']) ? null : $_REQUEST['no19_1_2'];
        $no19_1_3 = empty($_REQUEST['no19_1_3']) ? null : $_REQUEST['no19_1_3'];
        $no19_2 = empty($_REQUEST['no19_2']) ? null : $_REQUEST['no19_2'];
        $no19_3 = empty($_REQUEST['no19_3']) ? null : $_REQUEST['no19_3'];
        $no1_2 = empty($_REQUEST['no1_2']) ? null : $_REQUEST['no1_2'];
        $no1_3 = empty($_REQUEST['no1_3']) ? null : $_REQUEST['no1_3'];
        $no2 = empty($_REQUEST['no2']) ? null : $_REQUEST['no2'];
        $no20 = empty($_REQUEST['no20']) ? null : $_REQUEST['no20'];
        $no20_2 = empty($_REQUEST['no20_2']) ? null : $_REQUEST['no20_2'];
        $no20_3 = empty($_REQUEST['no20_3']) ? null : $_REQUEST['no20_3'];
        $no21 = empty($_REQUEST['no21']) ? null : $_REQUEST['no21'];
        $no21_2 = empty($_REQUEST['no21_2']) ? null : $_REQUEST['no21_2'];
        $no21_3 = empty($_REQUEST['no21_3']) ? null : $_REQUEST['no21_3'];
        $no22 = empty($_REQUEST['no22']) ? null : $_REQUEST['no22'];
        $no22_2 = empty($_REQUEST['no22_2']) ? null : $_REQUEST['no22_2'];
        $no22_3 = empty($_REQUEST['no22_3']) ? null : $_REQUEST['no22_3'];
        $no23 = empty($_REQUEST['no23']) ? null : $_REQUEST['no23'];
        $no23_2 = empty($_REQUEST['no23_2']) ? null : $_REQUEST['no23_2'];
        $no23_3 = empty($_REQUEST['no23_3']) ? null : $_REQUEST['no23_3'];
        $no24 = empty($_REQUEST['no24']) ? null : $_REQUEST['no24'];
        $no24_2 = empty($_REQUEST['no24_2']) ? null : $_REQUEST['no24_2'];
        $no24_3 = empty($_REQUEST['no24_3']) ? null : $_REQUEST['no24_3'];
        $no25 = empty($_REQUEST['no25']) ? null : $_REQUEST['no25'];
        $no25_2 = empty($_REQUEST['no25_2']) ? null : $_REQUEST['no25_2'];
        $no25_3 = empty($_REQUEST['no25_3']) ? null : $_REQUEST['no25_3'];
        $no26 = empty($_REQUEST['no26']) ? null : $_REQUEST['no26'];
        $no26_2 = empty($_REQUEST['no26_2']) ? null : $_REQUEST['no26_2'];
        $no26_3 = empty($_REQUEST['no26_3']) ? null : $_REQUEST['no26_3'];
        $no27 = empty($_REQUEST['no27']) ? null : $_REQUEST['no27'];
        $no27_2 = empty($_REQUEST['no27_2']) ? null : $_REQUEST['no27_2'];
        $no27_3 = empty($_REQUEST['no27_3']) ? null : $_REQUEST['no27_3'];
        $no28 = empty($_REQUEST['no28']) ? null : $_REQUEST['no28'];
        $no28_2 = empty($_REQUEST['no28_2']) ? null : $_REQUEST['no28_2'];
        $no28_3 = empty($_REQUEST['no28_3']) ? null : $_REQUEST['no28_3'];
        $no29 = empty($_REQUEST['no29']) ? null : $_REQUEST['no29'];
        $no29_2 = empty($_REQUEST['no29_2']) ? null : $_REQUEST['no29_2'];
        $no29_3 = empty($_REQUEST['no29_3']) ? null : $_REQUEST['no29_3'];
        $no2_2 = empty($_REQUEST['no2_2']) ? null : $_REQUEST['no2_2'];
        $no2_3 = empty($_REQUEST['no2_3']) ? null : $_REQUEST['no2_3'];
        $no3 = empty($_REQUEST['no3']) ? null : $_REQUEST['no3'];
        $no30 = empty($_REQUEST['no30']) ? null : $_REQUEST['no30'];
        $no30_2 = empty($_REQUEST['no30_2']) ? null : $_REQUEST['no30_2'];
        $no30_3 = empty($_REQUEST['no30_3']) ? null : $_REQUEST['no30_3'];
        $no31 = empty($_REQUEST['no31']) ? null : $_REQUEST['no31'];
        $no31_2 = empty($_REQUEST['no31_2']) ? null : $_REQUEST['no31_2'];
        $no31_3 = empty($_REQUEST['no31_3']) ? null : $_REQUEST['no31_3'];
        $no32 = empty($_REQUEST['no32']) ? null : $_REQUEST['no32'];
        $no32_2 = empty($_REQUEST['no32_2']) ? null : $_REQUEST['no32_2'];
        $no32_3 = empty($_REQUEST['no32_3']) ? null : $_REQUEST['no32_3'];
        $no33 = empty($_REQUEST['no33']) ? null : $_REQUEST['no33'];
        $no33_2 = empty($_REQUEST['no33_2']) ? null : $_REQUEST['no33_2'];
        $no33_3 = empty($_REQUEST['no33_3']) ? null : $_REQUEST['no33_3'];
        $no34 = empty($_REQUEST['no34']) ? null : $_REQUEST['no34'];
        $no34_2 = empty($_REQUEST['no34_2']) ? null : $_REQUEST['no34_2'];
        $no34_3 = empty($_REQUEST['no34_3']) ? null : $_REQUEST['no34_3'];
        $no35 = empty($_REQUEST['no35']) ? null : $_REQUEST['no35'];
        $no35_2 = empty($_REQUEST['no35_2']) ? null : $_REQUEST['no35_2'];
        $no35_3 = empty($_REQUEST['no35_3']) ? null : $_REQUEST['no35_3'];
        $no36 = empty($_REQUEST['no36']) ? null : $_REQUEST['no36'];
        $no36_2 = empty($_REQUEST['no36_2']) ? null : $_REQUEST['no36_2'];
        $no36_3 = empty($_REQUEST['no36_3']) ? null : $_REQUEST['no36_3'];
        $no37 = empty($_REQUEST['no37']) ? null : $_REQUEST['no37'];
        $no37_2 = empty($_REQUEST['no37_2']) ? null : $_REQUEST['no37_2'];
        $no37_3 = empty($_REQUEST['no37_3']) ? null : $_REQUEST['no37_3'];
        $no38 = empty($_REQUEST['no38']) ? null : $_REQUEST['no38'];
        $no38_2 = empty($_REQUEST['no38_2']) ? null : $_REQUEST['no38_2'];
        $no38_3 = empty($_REQUEST['no38_3']) ? null : $_REQUEST['no38_3'];
        $no39 = empty($_REQUEST['no39']) ? null : $_REQUEST['no39'];
        $no39_2 = empty($_REQUEST['no39_2']) ? null : $_REQUEST['no39_2'];
        $no39_3 = empty($_REQUEST['no39_3']) ? null : $_REQUEST['no39_3'];
        $no3_2 = empty($_REQUEST['no3_2']) ? null : $_REQUEST['no3_2'];
        $no3_3 = empty($_REQUEST['no3_3']) ? null : $_REQUEST['no3_3'];
        $no4 = empty($_REQUEST['no4']) ? null : $_REQUEST['no4'];
        $no40 = empty($_REQUEST['no40']) ? null : $_REQUEST['no40'];
        $no40_2 = empty($_REQUEST['no40_2']) ? null : $_REQUEST['no40_2'];
        $no40_3 = empty($_REQUEST['no40_3']) ? null : $_REQUEST['no40_3'];
        $no41 = empty($_REQUEST['no41']) ? null : $_REQUEST['no41'];
        $no41_2 = empty($_REQUEST['no41_2']) ? null : $_REQUEST['no41_2'];
        $no41_3 = empty($_REQUEST['no41_3']) ? null : $_REQUEST['no41_3'];
        $no42 = empty($_REQUEST['no42']) ? null : $_REQUEST['no42'];
        $no42_2 = empty($_REQUEST['no42_2']) ? null : $_REQUEST['no42_2'];
        $no42_2_1 = empty($_REQUEST['no42_2_1']) ? null : $_REQUEST['no42_2_1'];
        $no42_2_2 = empty($_REQUEST['no42_2_2']) ? null : $_REQUEST['no42_2_2'];
        $no42_2_3 = empty($_REQUEST['no42_2_3']) ? null : $_REQUEST['no42_2_3'];
        $no42_3 = empty($_REQUEST['no42_3']) ? null : $_REQUEST['no42_3'];
        $no42_3_1 = empty($_REQUEST['no42_3_1']) ? null : $_REQUEST['no42_3_1'];
        $no42_3_2 = empty($_REQUEST['no42_3_2']) ? null : $_REQUEST['no42_3_2'];
        $no42_3_3 = empty($_REQUEST['no42_3_3']) ? null : $_REQUEST['no42_3_3'];
        $no43 = empty($_REQUEST['no43']) ? null : $_REQUEST['no43'];
        $no43_2 = empty($_REQUEST['no43_2']) ? null : $_REQUEST['no43_2'];
        $no43_3 = empty($_REQUEST['no43_3']) ? null : $_REQUEST['no43_3'];
        $no43_text = empty($_REQUEST['no43_text']) ? null : $_REQUEST['no43_text'];
        $no44 = empty($_REQUEST['no44']) ? null : $_REQUEST['no44'];
        $no44_2 = empty($_REQUEST['no44_2']) ? null : $_REQUEST['no44_2'];
        $no44_3 = empty($_REQUEST['no44_3']) ? null : $_REQUEST['no44_3'];
        $no45 = empty($_REQUEST['no45']) ? null : $_REQUEST['no45'];
        $no45_2 = empty($_REQUEST['no45_2']) ? null : $_REQUEST['no45_2'];
        $no45_3 = empty($_REQUEST['no45_3']) ? null : $_REQUEST['no45_3'];
        $no46 = empty($_REQUEST['no46']) ? null : $_REQUEST['no46'];
        $no46_2 = empty($_REQUEST['no46_2']) ? null : $_REQUEST['no46_2'];
        $no46_3 = empty($_REQUEST['no46_3']) ? null : $_REQUEST['no46_3'];
        $no47 = empty($_REQUEST['no47']) ? null : $_REQUEST['no47'];
        $no47_2 = empty($_REQUEST['no47_2']) ? null : $_REQUEST['no47_2'];
        $no47_3 = empty($_REQUEST['no47_3']) ? null : $_REQUEST['no47_3'];
        $no47_text = empty($_REQUEST['no47_text']) ? null : $_REQUEST['no47_text'];
        $no48 = empty($_REQUEST['no48']) ? null : $_REQUEST['no48'];
        $no48_1_text = empty($_REQUEST['no48_1_text']) ? null : $_REQUEST['no48_1_text'];
        $no48_2 = empty($_REQUEST['no48_2']) ? null : $_REQUEST['no48_2'];
        $no48_2_text = empty($_REQUEST['no48_2_text']) ? null : $_REQUEST['no48_2_text'];
        $no48_3 = empty($_REQUEST['no48_3']) ? null : $_REQUEST['no48_3'];
        $no48_3_text = empty($_REQUEST['no48_3_text']) ? null : $_REQUEST['no48_3_text'];
        $no49 = empty($_REQUEST['no49']) ? null : $_REQUEST['no49'];
        $no49_2 = empty($_REQUEST['no49_2']) ? null : $_REQUEST['no49_2'];
        $no49_3 = empty($_REQUEST['no49_3']) ? null : $_REQUEST['no49_3'];
        $no4_2 = empty($_REQUEST['no4_2']) ? null : $_REQUEST['no4_2'];
        $no4_3 = empty($_REQUEST['no4_3']) ? null : $_REQUEST['no4_3'];
        $no5 = empty($_REQUEST['no5']) ? null : $_REQUEST['no5'];
        $no50 = empty($_REQUEST['no50']) ? null : $_REQUEST['no50'];
        $no50_2 = empty($_REQUEST['no50_2']) ? null : $_REQUEST['no50_2'];
        $no50_3 = empty($_REQUEST['no50_3']) ? null : $_REQUEST['no50_3'];
        $no51 = empty($_REQUEST['no51']) ? null : $_REQUEST['no51'];
        $no51_text = empty($_REQUEST['no51_text']) ? null : $_REQUEST['no51_text'];
        $no5_2 = empty($_REQUEST['no5_2']) ? null : $_REQUEST['no5_2'];
        $no5_3 = empty($_REQUEST['no5_3']) ? null : $_REQUEST['no5_3'];
        $no6 = empty($_REQUEST['no6']) ? null : $_REQUEST['no6'];
        $no6_2 = empty($_REQUEST['no6_2']) ? null : $_REQUEST['no6_2'];
        $no6_3 = empty($_REQUEST['no6_3']) ? null : $_REQUEST['no6_3'];
        $no7 = empty($_REQUEST['no7']) ? null : $_REQUEST['no7'];
        $no7_2 = empty($_REQUEST['no7_2']) ? null : $_REQUEST['no7_2'];
        $no7_3 = empty($_REQUEST['no7_3']) ? null : $_REQUEST['no7_3'];
        $no8 = empty($_REQUEST['no8']) ? null : $_REQUEST['no8'];
        $no8_2 = empty($_REQUEST['no8_2']) ? null : $_REQUEST['no8_2'];
        $no8_3 = empty($_REQUEST['no8_3']) ? null : $_REQUEST['no8_3'];
        $no9 = empty($_REQUEST['no9']) ? null : $_REQUEST['no9'];
        $no9_2 = empty($_REQUEST['no9_2']) ? null : $_REQUEST['no9_2'];
        $no9_3 = empty($_REQUEST['no9_3']) ? null : $_REQUEST['no9_3'];
        $nurse_name = empty($_REQUEST['nurse_name']) ? null : $_REQUEST['nurse_name'];
        $nurse_name2 = empty($_REQUEST['nurse_name2']) ? null : $_REQUEST['nurse_name2'];
        $nurse_name3 = empty($_REQUEST['nurse_name3']) ? null : $_REQUEST['nurse_name3'];
        
        $update_datetime= date('Y-m-d H:i:s');
        $update_user = $_SESSION['loginname'];
        $version = $_REQUEST['version'];
        $version = $version + 1;

       

        //-----------------เช็คเลข version ว่าตรงกับฐานข้อมูลหรือไม่
       /* $query_parameters_version = ['an' => '660005698'];
        $version_request = $_REQUEST['version_request'];//รับค่าเลข version
        $sql_version = "SELECT version FROM ".DbConstant::KPHIS_DBNAME.".prs_or_complication WHERE an=:an";
        $stmt_version = $conn->prepare($sql_version);
        $stmt_version->execute($query_parameters_version);
        $row_version = $stmt_version->fetch();
        $version = $row_version['version']; */
       
            
           /* try {

                $stmt = $conn->prepare("UPDATE ".DbConstant::KPHIS_DBNAME.".prs_or_complication SET an=:an,no1=:no1,no1_2=:no1_2,no1_3=:no1_3,no2=:no2,
                no2_2=:no2_2,no2_3=:no2_3,no43_text=:no43_text,nurse_name=:nurse_name,
                update_user=:update_user,version=:version
                WHERE id=:id");
                $stmt->execute(array('id'=>$id,  'an'=>$an, 'no1'=>$no1, 'no1_2'=>$no1_2, 'no1_3'=>$no1_3,'no2'=>$no2,
                 'no2_2'=>$no2_2,'no2_3'=>$no2_3,'no43_text'=>$no43_text,'nurse_name'=>$nurse_name,
                'update_user'=>$update_user,'version'=>$version
                )); 

                */

                try {

                    $stmt = $conn->prepare("UPDATE ".DbConstant::KPHIS_DBNAME.".prs_or_complication SET  an=:an,no1=:no1,no1_2=:no1_2,no1_3=:no1_3,no2=:no2
                    ,no2_2=:no2_2,no2_3=:no2_3,no3=:no3,no3_2=:no3_2,no3_3=:no3_3,no4=:no4,no4_2=:no4_2,no4_3=:no4_3,no5=:no5,no5_2=:no5_2,no5_3=:no5_3
                    ,no6=:no6,no6_2=:no6_2,no6_3=:no6_3,no7=:no7,no7_2=:no7_2,no7_3=:no7_3,no8=:no8,no8_2=:no8_2,no8_3=:no8_3,no9=:no9,no9_2=:no9_2
                    ,no9_3=:no9_3,no10=:no10,no10_2=:no10_2,no10_3=:no10_3,no11=:no11,no11_2=:no11_2,no11_3=:no11_3,no12=:no12,no12_2=:no12_2,no12_3=:no12_3,no13=:no13,no13_2=:no13_2
                    ,no13_3=:no13_3,no14=:no14,no14_2=:no14_2,no14_3=:no14_3,no15=:no15,no15_2=:no15_2,no15_3=:no15_3,no16=:no16,no16_2=:no16_2,no16_3=:no16_3
                    ,no17=:no17,no17_2=:no17_2,no17_3=:no17_3,no18=:no18,no18_2=:no18_2,no18_3=:no18_3,no19=:no19,no19_2=:no19_2,no19_3=:no19_3,no19_1=:no19_1
                    ,no19_1_2=:no19_1_2,no19_1_3=:no19_1_3,no20=:no20,no20_2=:no20_2,no20_3=:no20_3,no21=:no21,no21_2=:no21_2,no21_3=:no21_3,no22=:no22,no22_2=:no22_2
                    ,no22_3=:no22_3,no23=:no23,no23_2=:no23_2,no23_3=:no23_3,no24=:no24,no24_2=:no24_2,no24_3=:no24_3,no25=:no25,no25_2=:no25_2,no25_3=:no25_3
                    ,no26=:no26,no26_2=:no26_2,no26_3=:no26_3,no27=:no27,no27_2=:no27_2,no27_3=:no27_3,no28=:no28,no28_2=:no28_2,no28_3=:no28_3,no29=:no29
                    ,no29_2=:no29_2,no29_3=:no29_3,no30=:no30,no30_2=:no30_2,no30_3=:no30_3,no31=:no31,no31_2=:no31_2,no31_3=:no31_3,no32=:no32,no32_2=:no32_2
                    ,no32_3=:no32_3,no33=:no33,no33_2=:no33_2,no33_3=:no33_3,no34=:no34,no34_2=:no34_2,no34_3=:no34_3,no35=:no35,no35_2=:no35_2,no35_3=:no35_3
                    ,no36=:no36,no36_2=:no36_2,no36_3=:no36_3,no37=:no37,no37_2=:no37_2,no37_3=:no37_3,no38=:no38,no38_2=:no38_2,no38_3=:no38_3,no39=:no39
                    ,no39_2=:no39_2,no39_3=:no39_3,no40=:no40,no40_2=:no40_2,no40_3=:no40_3,no41=:no41,no41_2=:no41_2,no41_3=:no41_3,no42=:no42,no42_2=:no42_2
                    ,no42_3=:no42_3,no42_2_1=:no42_2_1,no42_2_2=:no42_2_2,no42_2_3=:no42_2_3,no42_3_1=:no42_3_1,no42_3_2=:no42_3_2,no42_3_3=:no42_3_3,no43=:no43
                    ,no43_2=:no43_2,no43_3=:no43_3,no43_text=:no43_text,no44=:no44,no44_2=:no44_2,no44_3=:no44_3,no45=:no45,no45_2=:no45_2,no45_3=:no45_3,no46=:no46
                    ,no46_2=:no46_2,no46_3=:no46_3,no47=:no47,no47_2=:no47_2,no47_3=:no47_3,no47_text=:no47_text,no48_1_text=:no48_1_text,no48_2_text=:no48_2_text
                    ,no48_3_text=:no48_3_text,no48=:no48,no48_2=:no48_2,no48_3=:no48_3,no49=:no49,no49_2=:no49_2,no49_3=:no49_3,no50=:no50,no50_2=:no50_2
                    ,no50_3=:no50_3,no51=:no51,no51_text=:no51_text,nurse_name=:nurse_name,nurse_name2=:nurse_name2,nurse_name3=:nurse_name3,
                    update_user=:update_user,version=:version,update_datetime=:update_datetime
                    WHERE id=:id");
                    $stmt->execute(array('id'=>$id,  'an'=>$an, 'no1'=>$no1,'no1_2'=>$no1_2,'no1_3'=>$no1_3,'no2'=>$no2,'no2_2'=>$no2_2,'no2_3'=>$no2_3
                    ,'no3'=>$no3,'no3_2'=>$no3_2,'no3_3'=>$no3_3,'no4'=>$no4,'no4_2'=>$no4_2,'no4_3'=>$no4_3,'no5'=>$no5,'no5_2'=>$no5_2,'no5_3'=>$no5_3
                    ,'no6'=>$no6,'no6_2'=>$no6_2,'no6_3'=>$no6_3,'no7'=>$no7,'no7_2'=>$no7_2,'no7_3'=>$no7_3,'no8'=>$no8,'no8_2'=>$no8_2,'no8_3'=>$no8_3
                    ,'no9'=>$no9,'no9_2'=>$no9_2,'no9_3'=>$no9_3,'no10'=>$no10,'no10_2'=>$no10_2,'no10_3'=>$no10_3,'no11'=>$no11,'no11_2'=>$no11_2,'no11_3'=>$no11_3,'no12'=>$no12
                    ,'no12_2'=>$no12_2,'no12_3'=>$no12_3,'no13'=>$no13,'no13_2'=>$no13_2,'no13_3'=>$no13_3,'no14'=>$no14,'no14_2'=>$no14_2,'no14_3'=>$no14_3,'no15'=>$no15,'no15_2'=>$no15_2
                    ,'no15_3'=>$no15_3,'no16'=>$no16,'no16_2'=>$no16_2,'no16_3'=>$no16_3,'no17'=>$no17,'no17_2'=>$no17_2,'no17_3'=>$no17_3,'no18'=>$no18,'no18_2'=>$no18_2
                    ,'no18_3'=>$no18_3,'no19'=>$no19,'no19_2'=>$no19_2,'no19_3'=>$no19_3,'no19_1'=>$no19_1,'no19_1_2'=>$no19_1_2,'no19_1_3'=>$no19_1_3,'no20'=>$no20
                    ,'no20_2'=>$no20_2,'no20_3'=>$no20_3,'no21'=>$no21,'no21_2'=>$no21_2,'no21_3'=>$no21_3,'no22'=>$no22,'no22_2'=>$no22_2,'no22_3'=>$no22_3,'no23'=>$no23
                    ,'no23_2'=>$no23_2,'no23_3'=>$no23_3,'no24'=>$no24,'no24_2'=>$no24_2,'no24_3'=>$no24_3,'no25'=>$no25,'no25_2'=>$no25_2,'no25_3'=>$no25_3,'no26'=>$no26
                    ,'no26_2'=>$no26_2,'no26_3'=>$no26_3,'no27'=>$no27,'no27_2'=>$no27_2,'no27_3'=>$no27_3,'no28'=>$no28,'no28_2'=>$no28_2,'no28_3'=>$no28_3,'no29'=>$no29
                    ,'no29_2'=>$no29_2,'no29_3'=>$no29_3,'no30'=>$no30,'no30_2'=>$no30_2,'no30_3'=>$no30_3,'no31'=>$no31,'no31_2'=>$no31_2,'no31_3'=>$no31_3,'no32'=>$no32
                    ,'no32_2'=>$no32_2,'no32_3'=>$no32_3,'no33'=>$no33,'no33_2'=>$no33_2,'no33_3'=>$no33_3,'no34'=>$no34,'no34_2'=>$no34_2,'no34_3'=>$no34_3,'no35'=>$no35
                    ,'no35_2'=>$no35_2,'no35_3'=>$no35_3,'no36'=>$no36,'no36_2'=>$no36_2,'no36_3'=>$no36_3,'no37'=>$no37,'no37_2'=>$no37_2,'no37_3'=>$no37_3,'no38'=>$no38
                    ,'no38_2'=>$no38_2,'no38_3'=>$no38_3,'no39'=>$no39,'no39_2'=>$no39_2,'no39_3'=>$no39_3,'no40'=>$no40,'no40_2'=>$no40_2,'no40_3'=>$no40_3,'no41'=>$no41
                    ,'no41_2'=>$no41_2,'no41_3'=>$no41_3,'no42'=>$no42,'no42_2'=>$no42_2,'no42_3'=>$no42_3,'no42_2_1'=>$no42_2_1,'no42_2_2'=>$no42_2_2,'no42_2_3'=>$no42_2_3
                    ,'no42_3_1'=>$no42_3_1,'no42_3_2'=>$no42_3_2,'no42_3_3'=>$no42_3_3,'no43'=>$no43,'no43_2'=>$no43_2,'no43_3'=>$no43_3,'no43_text'=>$no43_text,'no44'=>$no44
                    ,'no44_2'=>$no44_2,'no44_3'=>$no44_3,'no45'=>$no45,'no45_2'=>$no45_2,'no45_3'=>$no45_3,'no46'=>$no46,'no46_2'=>$no46_2,'no46_3'=>$no46_3,'no47'=>$no47
                    ,'no47_2'=>$no47_2,'no47_3'=>$no47_3,'no47_text'=>$no47_text,'no48_1_text'=>$no48_1_text,'no48_2_text'=>$no48_2_text,'no48_3_text'=>$no48_3_text
                    ,'no48'=>$no48,'no48_2'=>$no48_2,'no48_3'=>$no48_3,'no49'=>$no49,'no49_2'=>$no49_2,'no49_3'=>$no49_3,'no50'=>$no50,'no50_2'=>$no50_2,'no50_3'=>$no50_3
                    ,'no51'=>$no51,'no51_text'=>$no51_text,'nurse_name'=>$nurse_name,'nurse_name2'=>$nurse_name2,'nurse_name3'=>$nurse_name3
                    ,'update_user'=>$update_user,'version'=>$version,'update_datetime' => $update_datetime 
                    )); 


               
            $output_error = '<div class="alert alert-success">บันทึกข้อมูลเรียบร้อยแล้วคะ</div>';

        } catch (PDOException  $e) {
            echo $e->getMessage();
            $output_error = '<div class="alert alert-danger">ERROR !!FOCUS LIST</div>';
        }

        echo $output_error;
?>