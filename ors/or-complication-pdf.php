<?php   require_once '../include/Session.php';
       // Session::checkLoginSessionAndShowMessage(); //เช็ค session
       // if(!(Session::checkPermission('IPD_NURSE_ADDMISSION_NOTE','VIEW'))){
         //   return;
       // }
       include('../mains/datethai.php');
        require_once '../include/DbUtils.php';
        require_once '../include/KphisQueryUtils.php';
        $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
        $an = $_REQUEST['an'];
        $hn = KphisQueryUtils::getHnByAn($an);   // function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา
       require_once __DIR__ . '../../vendor/autoload.php';
      // require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
        date_default_timezone_set('asia/bangkok');
        $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
        $mpdf->AddPageByArray([
            'margin-left' => 8,
            'margin-right' => 8,
            'margin-top' => 8,
            'margin-bottom' => 5,
        ]);
        //----------------------select ข้อมูล จากฐานข้อมูลเพื่อค้นหา ชื่อ - สกุล
        $image_uncheck = "<img src='../include/images/check-adm.jpg' width='1.6%' class='check_img'>";
        $image_check = "<img src='../include/images/check-adm-1.png' width='1.6%' class='check_img'>";
        $query_parameters_REQUEST = ['an'=>$an];

        $sql_ipt = "select patient.hn,patient.pname,patient.fname,patient.lname,patient.drugallergy,
                    an_stat.age_y,an_stat.age_m,an_stat.age_d,an_stat.an,
                    ipt.regdate,ipt.regtime,ipt.ward,ward.name as ward_name,
                    ipt.pttype,
                    pttype.`name` as pttype_name,
                    ward.shortname
                    from ".DbConstant::HOSXP_DBNAME.".ipt
                    left outer join ".DbConstant::HOSXP_DBNAME.".an_stat on an_stat.an=ipt.an
                    left outer join ".DbConstant::HOSXP_DBNAME.".patient on patient.hn=ipt.hn
                    left outer join ".DbConstant::HOSXP_DBNAME.".ward on ward.ward=ipt.ward
                    LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".pttype ON pttype.pttype = ipt.pttype
                    WHERE ipt.an=:an";

        $stmt_ipt = $conn->prepare($sql_ipt);
        $stmt_ipt->execute($query_parameters_REQUEST);
        $row_iptCount = 0;
        while ($row_ipt = $stmt_ipt->fetch()){
            $hn_row_ipt = htmlspecialchars($row_ipt['hn']);
            $pname_row_ipt = htmlspecialchars($row_ipt['pname']);
            $fname_row_ipt = htmlspecialchars($row_ipt['fname']);
            $lname_row_ipt = htmlspecialchars($row_ipt['lname']);
            $age_y_row_ipt = htmlspecialchars($row_ipt['age_y']);
            $an_row_ipt = htmlspecialchars($row_ipt['an']);
            $wardname_row_ipt = htmlspecialchars($row_ipt['ward_name']);
        }

        $strDate = date("Y-m-d H:i:s");

        $mpdf->setFooter(' (พิมพ์โดย '.$_SESSION['name'].' วันที่พิมพ์ '.DateThai($strDate).' ) '.'<br>ผู้ป่วย : (hn : '.$hn_row_ipt.')(an : '.$an.')(ชื่อ - สกุล : '.$pname_row_ipt.' '.$fname_row_ipt.' '.$lname_row_ipt.')'.' ( Page {PAGENO} of {nb} )');
        $mpdf->WriteHTML('');
        //----------------------select ข้อมูล จากฐานข้อมูลเพื่อค้นหา ชื่อ - สกุล
        //----------------------ipd_nurse_admission_note
        $sql = "SELECT t.* ,opduser.name AS name_full, entryposition
                FROM ".DbConstant::KPHIS_DBNAME.".prs_or_complication t
                LEFT OUTER JOIN ".DbConstant::HOSXP_DBNAME.".opduser ON t.update_user = opduser.loginname
                WHERE an=:an";
        $parameters['an'] = $an;
        $stmt = $conn->prepare($sql);
        $stmt->execute($parameters);
        $row = $stmt->fetch();

        //ความรู้สึกตัว
        $concious       =  $row['concious'];
        /*if($concious == "รู้สึกตัวดี"){$concious_1 = $image_check;}else{$concious_1 = $image_uncheck;}
        if($concious == "สับสน"){$concious_2 = $image_check;}else{$concious_2 = $image_uncheck;}
        if($concious == "ง่วงซึม"){$concious_3 = $image_check;}else{$concious_3 = $image_uncheck;}
        if($concious == "ไม่รู้สึกตัว"){$concious_4 = $image_check;}else{$concious_4 = $image_uncheck;} */
        //ลักษณะการหายใจ

        //1.Retained ET tube / Tracheostomy tube
        $no1  =  $row['no1'];
        if($no1 == "Y"){$no1 = $image_check;}else{$no1 = $image_uncheck;}
        $no1_2  =  $row['no1_2'];
        if($no1_2 == "Y"){$no1_2 = $image_check;}else{$no1_2 = $image_uncheck;}
        $no1_3  =  $row['no1_3'];
        if($no1_3 == "Y"){$no1_3 = $image_check;}else{$no1_3 = $image_uncheck;}
        //2.Ventilatory support
        $no2  =  $row['no2'];
        if($no2 == "Y"){$no2 = $image_check;}else{$no2 = $image_uncheck;}
        $no2_2  =  $row['no2_2'];
        if($no2_2 == "Y"){$no2_2 = $image_check;}else{$no2_2 = $image_uncheck;}
        $no2_3  =  $row['no2_3'];
        if($no2_3 == "Y"){$no2_3 = $image_check;}else{$no2_3 = $image_uncheck;}
        //3.Sore throat
        $no3  =  $row['no3'];
        if($no3 == "Y"){$no3 = $image_check;}else{$no3 = $image_uncheck;}
        $no3_2  =  $row['no3_2'];
        if($no3_2 == "Y"){$no3_2 = $image_check;}else{$no3_2 = $image_uncheck;}
        $no3_3  =  $row['no3_3'];
        if($no3_3 == "Y"){$no3_3 = $image_check;}else{$no3_3 = $image_uncheck;}

        $no4  =  $row['no4'];
        if($no4 == "Y"){$no4 = $image_check;}else{$no4 = $image_uncheck;}
        $no4_2  =  $row['no4_2'];
        if($no4_2 == "Y"){$no4_2 = $image_check;}else{$no4_2 = $image_uncheck;}
        $no4_3  =  $row['no4_3'];
        if($no4_3 == "Y"){$no4_3 = $image_check;}else{$no4_3 = $image_uncheck;}

        $no5  =  $row['no5'];
        if($no5 == "Y"){$no5 = $image_check;}else{$no5 = $image_uncheck;}
        $no5_2  =  $row['no5_2'];
        if($no5_2 == "Y"){$no5_2 = $image_check;}else{$no5_2 = $image_uncheck;}
        $no5_3  =  $row['no5_3'];
        if($no5_3 == "Y"){$no5_3 = $image_check;}else{$no5_3 = $image_uncheck;}

        $no6  =  $row['no6'];
        if($no6 == "Y"){$no6 = $image_check;}else{$no6 = $image_uncheck;}
        $no6_2  =  $row['no6_2'];
        if($no6_2 == "Y"){$no6_2 = $image_check;}else{$no6_2 = $image_uncheck;}
        $no6_3  =  $row['no6_3'];
        if($no6_3 == "Y"){$no6_3 = $image_check;}else{$no6_3 = $image_uncheck;}

        $no7  =  $row['no7'];
        if($no7 == "Y"){$no7 = $image_check;}else{$no7 = $image_uncheck;}
        $no7_2  =  $row['no7_2'];
        if($no7_2 == "Y"){$no7_2 = $image_check;}else{$no7_2 = $image_uncheck;}
        $no7_3  =  $row['no7_3'];
        if($no7_3 == "Y"){$no7_3 = $image_check;}else{$no7_3 = $image_uncheck;}

        $no8  =  $row['no8'];
        if($no8 == "Y"){$no8 = $image_check;}else{$no8 = $image_uncheck;}
        $no8_2  =  $row['no8_2'];
        if($no8_2 == "Y"){$no8_2 = $image_check;}else{$no8_2 = $image_uncheck;}
        $no8_3  =  $row['no8_3'];
        if($no8_3 == "Y"){$no8_3 = $image_check;}else{$no8_3 = $image_uncheck;}

        $no9  =  $row['no9'];
        if($no9 == "Y"){$no9 = $image_check;}else{$no9 = $image_uncheck;}
        $no9_2  =  $row['no9_2'];
        if($no9_2 == "Y"){$no9_2 = $image_check;}else{$no9_2 = $image_uncheck;}
        $no9_3  =  $row['no9_3'];
        if($no9_3 == "Y"){$no9_3 = $image_check;}else{$no9_3 = $image_uncheck;}

        $no10  =  $row['no10'];
        if($no10 == "Y"){$no10 = $image_check;}else{$no10 = $image_uncheck;}
        $no10_2  =  $row['no10_2'];
        if($no10_2 == "Y"){$no10_2 = $image_check;}else{$no10_2 = $image_uncheck;}
        $no10_3  =  $row['no10_3'];
        if($no10_3 == "Y"){$no10_3 = $image_check;}else{$no10_3 = $image_uncheck;}

        $no11  =  $row['no11'];
        if($no11 == "Y"){$no11 = $image_check;}else{$no11 = $image_uncheck;}
        $no11_2  =  $row['no11_2'];
        if($no11_2 == "Y"){$no11_2 = $image_check;}else{$no11_2 = $image_uncheck;}
        $no11_3  =  $row['no11_3'];
        if($no11_3 == "Y"){$no11_3 = $image_check;}else{$no11_3 = $image_uncheck;}

        $no12  =  $row['no12'];
        if($no12 == "Y"){$no12 = $image_check;}else{$no12 = $image_uncheck;}
        $no12_2  =  $row['no12_2'];
        if($no12_2 == "Y"){$no12_2 = $image_check;}else{$no12_2 = $image_uncheck;}
        $no12_3  =  $row['no12_3'];
        if($no12_3 == "Y"){$no12_3 = $image_check;}else{$no12_3 = $image_uncheck;}

        $no13  =  $row['no13'];
        if($no13 == "Y"){$no13 = $image_check;}else{$no13 = $image_uncheck;}
        $no13_2  =  $row['no13_2'];
        if($no13_2 == "Y"){$no13_2 = $image_check;}else{$no13_2 = $image_uncheck;}
        $no13_3  =  $row['no13_3'];
        if($no13_3 == "Y"){$no13_3 = $image_check;}else{$no13_3 = $image_uncheck;}

        $no14  =  $row['no14'];
        if($no14 == "Y"){$no14 = $image_check;}else{$no14 = $image_uncheck;}
        $no14_2  =  $row['no14_2'];
        if($no14_2 == "Y"){$no14_2 = $image_check;}else{$no14_2 = $image_uncheck;}
        $no14_3  =  $row['no14_3'];
        if($no14_3 == "Y"){$no14_3 = $image_check;}else{$no14_3 = $image_uncheck;}

        $no15  =  $row['no15'];
        if($no15 == "Y"){$no15 = $image_check;}else{$no15 = $image_uncheck;}
        $no15_2  =  $row['no15_2'];
        if($no15_2 == "Y"){$no15_2 = $image_check;}else{$no15_2 = $image_uncheck;}
        $no15_3  =  $row['no15_3'];
        if($no15_3 == "Y"){$no15_3 = $image_check;}else{$no15_3 = $image_uncheck;}

        $no16  =  $row['no16'];
        if($no16 == "Y"){$no16 = $image_check;}else{$no16 = $image_uncheck;}
        $no16_2  =  $row['no16_2'];
        if($no16_2 == "Y"){$no16_2 = $image_check;}else{$no16_2 = $image_uncheck;}
        $no16_3  =  $row['no16_3'];
        if($no16_3 == "Y"){$no16_3 = $image_check;}else{$no16_3 = $image_uncheck;}

        $no17  =  $row['no17'];
        if($no17 == "Y"){$no17 = $image_check;}else{$no17 = $image_uncheck;}
        $no17_2  =  $row['no17_2'];
        if($no17_2 == "Y"){$no17_2 = $image_check;}else{$no17_2 = $image_uncheck;}
        $no17_3  =  $row['no17_3'];
        if($no17_3 == "Y"){$no17_3 = $image_check;}else{$no17_3 = $image_uncheck;}

        $no18  =  $row['no18'];
        if($no18 == "Y"){$no18 = $image_check;}else{$no18 = $image_uncheck;}
        $no18_2  =  $row['no18_2'];
        if($no18_2 == "Y"){$no18_2 = $image_check;}else{$no18_2 = $image_uncheck;}
        $no18_3  =  $row['no18_3'];
        if($no18_3 == "Y"){$no18_3 = $image_check;}else{$no18_3 = $image_uncheck;}

        $no19  =  $row['no19'];
        if($no19 == "Y"){$no19 = $image_check;}else{$no19 = $image_uncheck;}
        $no19_2  =  $row['no19_2'];
        if($no19_2 == "Y"){$no19_2 = $image_check;}else{$no19_2 = $image_uncheck;}
        $no19_3  =  $row['no19_3'];
        if($no19_3 == "Y"){$no19_3 = $image_check;}else{$no19_3 = $image_uncheck;}

        $no19_1  =  $row['no19_1'];
        if($no19_1 == "Y"){$no19_1 = $image_check;}else{$no19_1 = $image_uncheck;}
        $no19_1_2  =  $row['no19_1_2'];
        if($no19_1_2 == "Y"){$no19_1_2 = $image_check;}else{$no19_1_2 = $image_uncheck;}
        $no19_1_3  =  $row['no19_1_3'];
        if($no19_1_3 == "Y"){$no19_1_3 = $image_check;}else{$no19_1_3 = $image_uncheck;}

        $no20  =  $row['no20'];
        if($no20 == "Y"){$no20 = $image_check;}else{$no20 = $image_uncheck;}
        $no20_2  =  $row['no20_2'];
        if($no20_2 == "Y"){$no20_2 = $image_check;}else{$no20_2 = $image_uncheck;}
        $no20_3  =  $row['no20_3'];
        if($no20_3 == "Y"){$no20_3 = $image_check;}else{$no20_3 = $image_uncheck;}

        $no21  =  $row['no21'];
        if($no21 == "Y"){$no21 = $image_check;}else{$no21 = $image_uncheck;}
        $no21_2  =  $row['no21_2'];
        if($no21_2 == "Y"){$no21_2 = $image_check;}else{$no21_2 = $image_uncheck;}
        $no21_3  =  $row['no21_3'];
        if($no21_3 == "Y"){$no21_3 = $image_check;}else{$no21_3 = $image_uncheck;}

        $no22  =  $row['no22'];
        if($no22 == "Y"){$no22 = $image_check;}else{$no22 = $image_uncheck;}
        $no22_2  =  $row['no22_2'];
        if($no22_2 == "Y"){$no22_2 = $image_check;}else{$no22_2 = $image_uncheck;}
        $no22_3  =  $row['no22_3'];
        if($no22_3 == "Y"){$no22_3 = $image_check;}else{$no22_3 = $image_uncheck;}

        $no23  =  $row['no23'];
        if($no23 == "Y"){$no23 = $image_check;}else{$no23 = $image_uncheck;}
        $no23_2  =  $row['no23_2'];
        if($no23_2 == "Y"){$no23_2 = $image_check;}else{$no23_2 = $image_uncheck;}
        $no23_3  =  $row['no23_3'];
        if($no23_3 == "Y"){$no23_3 = $image_check;}else{$no23_3 = $image_uncheck;}

        $no24  =  $row['no24'];
        if($no24 == "Y"){$no24 = $image_check;}else{$no24 = $image_uncheck;}
        $no24_2  =  $row['no24_2'];
        if($no24_2 == "Y"){$no24_2 = $image_check;}else{$no24_2 = $image_uncheck;}
        $no24_3  =  $row['no24_3'];
        if($no24_3 == "Y"){$no24_3 = $image_check;}else{$no24_3 = $image_uncheck;}

        $no25  =  $row['no25'];
        if($no25 == "Y"){$no25 = $image_check;}else{$no25 = $image_uncheck;}
        $no25_2  =  $row['no25_2'];
        if($no25_2 == "Y"){$no25_2 = $image_check;}else{$no25_2 = $image_uncheck;}
        $no25_3  =  $row['no25_3'];
        if($no25_3 == "Y"){$no25_3 = $image_check;}else{$no25_3 = $image_uncheck;}

        $no26  =  $row['no26'];
        if($no26 == "Y"){$no26 = $image_check;}else{$no26 = $image_uncheck;}
        $no26_2  =  $row['no26_2'];
        if($no26_2 == "Y"){$no26_2 = $image_check;}else{$no26_2 = $image_uncheck;}
        $no26_3  =  $row['no26_3'];
        if($no26_3 == "Y"){$no26_3 = $image_check;}else{$no26_3 = $image_uncheck;}

        $no27  =  $row['no27'];
        if($no27 == "Y"){$no27 = $image_check;}else{$no27 = $image_uncheck;}
        $no27_2  =  $row['no27_2'];
        if($no27_2 == "Y"){$no27_2 = $image_check;}else{$no27_2 = $image_uncheck;}
        $no27_3  =  $row['no27_3'];
        if($no27_3 == "Y"){$no27_3 = $image_check;}else{$no27_3 = $image_uncheck;}

        $no28  =  $row['no28'];
        if($no28 == "Y"){$no28 = $image_check;}else{$no28 = $image_uncheck;}
        $no28_2  =  $row['no28_2'];
        if($no28_2 == "Y"){$no28_2 = $image_check;}else{$no28_2 = $image_uncheck;}
        $no28_3  =  $row['no28_3'];
        if($no28_3 == "Y"){$no28_3 = $image_check;}else{$no28_3 = $image_uncheck;}

        $no29  =  $row['no29'];
        if($no29 == "Y"){$no29 = $image_check;}else{$no29 = $image_uncheck;}
        $no29_2  =  $row['no29_2'];
        if($no29_2 == "Y"){$no29_2 = $image_check;}else{$no29_2 = $image_uncheck;}
        $no29_3  =  $row['no29_3'];
        if($no29_3 == "Y"){$no29_3 = $image_check;}else{$no29_3 = $image_uncheck;}

        $no30  =  $row['no30'];
        if($no30 == "Y"){$no30 = $image_check;}else{$no30 = $image_uncheck;}
        $no30_2  =  $row['no30_2'];
        if($no30_2 == "Y"){$no30_2 = $image_check;}else{$no30_2 = $image_uncheck;}
        $no30_3  =  $row['no30_3'];
        if($no30_3 == "Y"){$no30_3 = $image_check;}else{$no30_3 = $image_uncheck;}

        $no31  =  $row['no31'];
        if($no31 == "Y"){$no31 = $image_check;}else{$no31 = $image_uncheck;}
        $no31_2  =  $row['no31_2'];
        if($no31_2 == "Y"){$no31_2 = $image_check;}else{$no31_2 = $image_uncheck;}
        $no31_3  =  $row['no31_3'];
        if($no31_3 == "Y"){$no31_3 = $image_check;}else{$no31_3 = $image_uncheck;}

        $no32  =  $row['no32'];
        if($no32 == "Y"){$no32 = $image_check;}else{$no32 = $image_uncheck;}
        $no32_2  =  $row['no32_2'];
        if($no32_2 == "Y"){$no32_2 = $image_check;}else{$no32_2 = $image_uncheck;}
        $no32_3  =  $row['no32_3'];
        if($no32_3 == "Y"){$no32_3 = $image_check;}else{$no32_3 = $image_uncheck;}

        $no33  =  $row['no33'];
        if($no33 == "Y"){$no33 = $image_check;}else{$no33 = $image_uncheck;}
        $no33_2  =  $row['no33_2'];
        if($no33_2 == "Y"){$no33_2 = $image_check;}else{$no33_2 = $image_uncheck;}
        $no33_3  =  $row['no33_3'];
        if($no33_3 == "Y"){$no33_3 = $image_check;}else{$no33_3 = $image_uncheck;}

        $no34  =  $row['no34'];
        if($no34 == "Y"){$no34 = $image_check;}else{$no34 = $image_uncheck;}
        $no34_2  =  $row['no34_2'];
        if($no34_2 == "Y"){$no34_2 = $image_check;}else{$no34_2 = $image_uncheck;}
        $no34_3  =  $row['no34_3'];
        if($no34_3 == "Y"){$no34_3 = $image_check;}else{$no34_3 = $image_uncheck;}

        $no35  =  $row['no35'];
        if($no35 == "Y"){$no35 = $image_check;}else{$no35 = $image_uncheck;}
        $no35_2  =  $row['no35_2'];
        if($no35_2 == "Y"){$no35_2 = $image_check;}else{$no35_2 = $image_uncheck;}
        $no35_3  =  $row['no35_3'];
        if($no35_3 == "Y"){$no35_3 = $image_check;}else{$no35_3 = $image_uncheck;}

        $no36  =  $row['no36'];
        if($no36 == "Y"){$no36 = $image_check;}else{$no36 = $image_uncheck;}
        $no36_2  =  $row['no36_2'];
        if($no36_2 == "Y"){$no36_2 = $image_check;}else{$no36_2 = $image_uncheck;}
        $no36_3  =  $row['no36_3'];
        if($no36_3 == "Y"){$no36_3 = $image_check;}else{$no36_3 = $image_uncheck;}

        $no37  =  $row['no37'];
        if($no37 == "Y"){$no37 = $image_check;}else{$no37 = $image_uncheck;}
        $no37_2  =  $row['no37_2'];
        if($no37_2 == "Y"){$no37_2 = $image_check;}else{$no37_2 = $image_uncheck;}
        $no37_3  =  $row['no37_3'];
        if($no37_3 == "Y"){$no37_3 = $image_check;}else{$no37_3 = $image_uncheck;}

        $no38  =  $row['no38'];
        if($no38 == "Y"){$no38 = $image_check;}else{$no38 = $image_uncheck;}
        $no38_2  =  $row['no38_2'];
        if($no38_2 == "Y"){$no38_2 = $image_check;}else{$no38_2 = $image_uncheck;}
        $no38_3  =  $row['no38_3'];
        if($no38_3 == "Y"){$no38_3 = $image_check;}else{$no38_3 = $image_uncheck;}

        $no39  =  $row['no39'];
        if($no39 == "Y"){$no39 = $image_check;}else{$no39 = $image_uncheck;}
        $no39_2  =  $row['no39_2'];
        if($no39_2 == "Y"){$no39_2 = $image_check;}else{$no39_2 = $image_uncheck;}
        $no39_3  =  $row['no39_3'];
        if($no39_3 == "Y"){$no39_3 = $image_check;}else{$no39_3 = $image_uncheck;}

        $no40  =  $row['no40'];
        if($no40 == "Y"){$no40 = $image_check;}else{$no40 = $image_uncheck;}
        $no40_2  =  $row['no40_2'];
        if($no40_2 == "Y"){$no40_2 = $image_check;}else{$no40_2 = $image_uncheck;}
        $no40_3  =  $row['no40_3'];
        if($no40_3 == "Y"){$no40_3 = $image_check;}else{$no40_3 = $image_uncheck;}

        $no41  =  $row['no41'];
        if($no41 == "Y"){$no41 = $image_check;}else{$no41 = $image_uncheck;}
        $no41_2  =  $row['no41_2'];
        if($no41_2 == "Y"){$no41_2 = $image_check;}else{$no41_2 = $image_uncheck;}
        $no41_3  =  $row['no41_3'];
        if($no41_3 == "Y"){$no41_3 = $image_check;}else{$no41_3 = $image_uncheck;}

        $no42  =  $row['no42'];
        if($no42 == "Y"){$no42 = $image_check;}else{$no42 = $image_uncheck;}
        $no42_2  =  $row['no42_2'];
        if($no42_2 == "Y"){$no42_2 = $image_check;}else{$no42_2 = $image_uncheck;}
        $no42_3  =  $row['no42_3'];
        if($no42_3 == "Y"){$no42_3 = $image_check;}else{$no42_3 = $image_uncheck;}

        $no42_2_1  =  $row['no42_2_1'];
        if($no42_2_1 == "Y"){$no42_2_1 = $image_check;}else{$no42_2_1 = $image_uncheck;}
        $no42_2_2  =  $row['no42_2_2'];
        if($no42_2_2 == "Y"){$no42_2_2 = $image_check;}else{$no42_2_2 = $image_uncheck;}
        $no42_2_3  =  $row['no42_2_3'];
        if($no42_2_3 == "Y"){$no42_2_3 = $image_check;}else{$no42_2_3 = $image_uncheck;}

        $no42_3_1  =  $row['no42_3_1'];
        if($no42_3_1 == "Y"){$no42_3_1 = $image_check;}else{$no42_3_1 = $image_uncheck;}
        $no42_3_2  =  $row['no42_3_2'];
        if($no42_3_2 == "Y"){$no42_3_2 = $image_check;}else{$no42_3_2 = $image_uncheck;}
        $no42_3_3  =  $row['no42_3_3'];
        if($no42_3_3 == "Y"){$no42_3_3 = $image_check;}else{$no42_3_3 = $image_uncheck;}

        $no43  =  $row['no43'];
        if($no43 == "Y"){$no43 = $image_check;}else{$no43 = $image_uncheck;}
        $no43_2  =  $row['no43_2'];
        if($no43_2 == "Y"){$no43_2 = $image_check;}else{$no43_2 = $image_uncheck;}
        $no43_3  =  $row['no43_3'];
        if($no43_3 == "Y"){$no43_3 = $image_check;}else{$no43_3 = $image_uncheck;}


       

        $no44  =  $row['no44'];
        if($no44 == "Y"){$no44 = $image_check;}else{$no44 = $image_uncheck;}
        $no44_2  =  $row['no44_2'];
        if($no44_2 == "Y"){$no44_2 = $image_check;}else{$no44_2 = $image_uncheck;}
        $no44_3  =  $row['no44_3'];
        if($no44_3 == "Y"){$no44_3 = $image_check;}else{$no44_3 = $image_uncheck;}

        $no45  =  $row['no45'];
        if($no45 == "Y"){$no45 = $image_check;}else{$no45 = $image_uncheck;}
        $no45_2  =  $row['no45_2'];
        if($no45_2 == "Y"){$no45_2 = $image_check;}else{$no45_2 = $image_uncheck;}
        $no45_3  =  $row['no45_3'];
        if($no45_3 == "Y"){$no45_3 = $image_check;}else{$no45_3 = $image_uncheck;}

        $no46  =  $row['no46'];
        if($no46 == "Y"){$no46 = $image_check;}else{$no46 = $image_uncheck;}
        $no46_2  =  $row['no46_2'];
        if($no46_2 == "Y"){$no46_2 = $image_check;}else{$no46_2 = $image_uncheck;}
        $no46_3  =  $row['no46_3'];
        if($no46_3 == "Y"){$no46_3 = $image_check;}else{$no46_3 = $image_uncheck;}

        $no47  =  $row['no47'];
        if($no47 == "Y"){$no47 = $image_check;}else{$no47 = $image_uncheck;}
        $no47_2  =  $row['no47_2'];
        if($no47_2 == "Y"){$no47_2 = $image_check;}else{$no47_2 = $image_uncheck;}
        $no47_3  =  $row['no47_3'];
        if($no47_3 == "Y"){$no47_3 = $image_check;}else{$no47_3 = $image_uncheck;}

        $no48  =  $row['no48'];
        if($no48 == "Y"){$no48 = $image_check;}else{$no48 = $image_uncheck;}
        $no48_2  =  $row['no48_2'];
        if($no48_2 == "Y"){$no48_2 = $image_check;}else{$no48_2 = $image_uncheck;}
        $no48_3  =  $row['no48_3'];
        if($no48_3 == "Y"){$no48_3 = $image_check;}else{$no48_3 = $image_uncheck;}

        $no49  =  $row['no49'];
        if($no49 == "Y"){$no49 = $image_check;}else{$no49 = $image_uncheck;}
        $no49_2  =  $row['no49_2'];
        if($no49_2 == "Y"){$no49_2 = $image_check;}else{$no49_2 = $image_uncheck;}
        $no49_3  =  $row['no49_3'];
        if($no49_3 == "Y"){$no49_3 = $image_check;}else{$no49_3 = $image_uncheck;}

        $no50  =  $row['no50'];
        if($no50 == "Y"){$no50 = $image_check;}else{$no50 = $image_uncheck;}
        $no50_2  =  $row['no50_2'];
        if($no50_2 == "Y"){$no50_2 = $image_check;}else{$no50_2 = $image_uncheck;}
        $no50_3  =  $row['no50_3'];
        if($no50_3 == "Y"){$no50_3 = $image_check;}else{$no50_3 = $image_uncheck;}

        $no51  =  $row['no51'];
        if($no51 == "Y"){$no51 = $image_check;}else{$no51 = $image_uncheck;}

        $no43_text = htmlspecialchars($row['no43_text']);
        $no47_text = htmlspecialchars($row['no47_text']);
        $no51_text = htmlspecialchars($row['no51_text']);

        $normal_breath  =  $row['normal_breath'];
        if($normal_breath == "Y"){$normal_breath = $image_check;}else{$normal_breath = $image_uncheck;}
        $emotional_other_text   =  $row['emotional_other_text'];

        $no431  =  $row['no431'];
        if($no431 == "Y"){$no431 = $image_check.'&nbsp; 1';}else{$no431 = '-';}
        $no432  =  $row['no432'];
        if($no432 == "Y"){$no432 = $image_check.'&nbsp; 2';}else{$no432 = '-';}
        $no433  =  $row['no433'];
        if($no433 == "Y"){$no433 = $image_check.'&nbsp; 3';}else{$no433 = '-';}
      
        //ชื่อ-สกุล ตำแหน่งผู้บันทึกข้อมูล
        $name_full        =  $row['name_full'];
        $entryposition        =  $row['entryposition'];

        $nodata = '.';

        //----------------------ipd_nurse_admission_note
        $head = '
                <style>
                body{
                        font-family: "Garuda";//เรียกใช้font Garuda สำหรับแสดงผล ภาษาไทย
                }
                footer {
                        position: fixed;
                        bottom: -60px;
                        left: 0px;
                        right: 0px;
                        height: 50px;

                        /** Extra personal styles **/
                        line-height: 35px;
                }
                .check_img{
                    /*margin-top: 10px;*/
                }

table, th, td {
  border:1px solid black;
  border-collapse: collapse;
  font-size: 10.4px;
}

                </style>
                <!--<h6 style="text-align:right;">Anes01</h6>-->
                <table id="bg-table" width="100%" style="border-collapse: collapse;font-size:8pt;margin-top:7px;">
                    
                <tr style="border:1px solid #000;margin: 35px;">
                        <td style="border-right:0.5px solid #000;padding:4px; text-align:center; background-color:#C0C0C0;" colspan="10"><B>ใบประเมินภาวะแทรกซ้อนหลังการระงับความรู้สึกใน 24-48 ชั่วโมง</B></td>
                    </tr>
                    <tr style="border:1px solid #000;margin: 35px;">
                        <td style="border-right:0.5px solid #000;padding:2px; text-align:center;" colspan="10"><B>Complication 1 = Intra-op  &emsp;&emsp;&emsp;&emsp;&ensp;  2= PACU
                        &emsp;&emsp;&emsp;&emsp;&ensp;  3= Post-op 24 hrs.</B></td>
                    </tr>
                    <tr style="border:1px solid #000;margin: 45px;">
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>1</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Retained ET tube / Tracheostomy tube</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no1.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no1_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no1_3.'&nbsp; 3
                        </td>

                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>36</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Hypothermia (Temp < 35 °C)</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no1.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no1_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no1_3.'&nbsp; 3
                        </td>
                                                                     
                </tr>

                <tr style="border:1px solid #000;margin: 45px;">
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>2</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Ventilatory support</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no2.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no2_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no2_3.'&nbsp; 3
                        </td>

                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>37</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Fever (Temp > 38 °C) , MH</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no37.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no37_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no37_3.'&nbsp; 3
                        </td>
                                                 
                </tr>

                <tr style="border:1px solid #000;margin: 45px;">
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>3</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Sore throat</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no3.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no3_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no3_3.'&nbsp; 3
                        </td>

                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>38</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Transfusion reaction / Mismatch</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no38.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no38_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no38_3.'&nbsp; 3
                        </td>
                                                 
                </tr>

                <tr style="border:1px solid #000;margin: 45px;">
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>4</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Upper airway obstruction</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no4.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no4_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no4_3.'&nbsp; 3
                        </td>

                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>39</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Coagulopathy</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no39.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no39_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no39_3.'&nbsp; 3
                        </td>
                                                 
                </tr>

                <tr style="border:1px solid #000;margin: 45px;">
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>5</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Lower airway obstuction</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no5.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no5_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no5_3.'&nbsp; 3
                        </td>

                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>40</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Massive blood loss</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no40.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no40_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no40_3.'&nbsp; 3
                        </td>
                                                 
                </tr>

                <tr style="border:1px solid #000;margin: 45px;">
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>6</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Unpredicted difficult intubation</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no6.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no6_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no6_3.'&nbsp; 3
                        </td>

                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>41</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Allergic reaction / Anaphylactic shock</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no41.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no41_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no41_3.'&nbsp; 3
                        </td>
                                                 
                </tr>

                <tr style="border:1px solid #000;margin: 45px;">
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>7</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Aspiration</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no7.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no7_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no7_3.'&nbsp; 3
                        </td>

                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>42</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Burn</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no42.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no42_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no42_3.'&nbsp; 3
                        </td>
                                                 
                </tr>

                <tr style="border:1px solid #000;margin: 45px;">
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>8</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Airway injury</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no8.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no8_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no8_3.'&nbsp; 3
                        </td>

                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B></B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Shivering</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no42_2_1.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no42_2_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no42_2_3.'&nbsp; 3
                        </td>
                                                 
                </tr>

                <tr style="border:1px solid #000;margin: 45px;">
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>9</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Dental injury</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no9.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no9_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no9_3.'&nbsp; 3
                        </td>

                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B></B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Warm</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no42_3_1.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no42_3_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no42_3_3.'&nbsp; 3
                        </td>
                                                 
                </tr>

                <tr style="border:1px solid #000;margin: 45px;">
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>10</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Esophageal intubation (เขียว หรือ SpO2 < 90 %)</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no10.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no10_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no10_3.'&nbsp; 3
                        </td>

                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>43</B></td>
                        <td  width="25%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;">

                <B>ใช้ยา</B>&nbsp;
                <B style="text-decoration: underline;"> '.htmlspecialchars($row['no43_text']).' </B>&nbsp; <br>
                
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<B style="text-decoration: underline;"> '.htmlspecialchars($row['no43_tet']).' </B>&nbsp;
                
                </td>
                <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no43.'&nbsp; 1
                            '.$no431.'

                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no43_2.'&nbsp; 2
                            '.$no432.'
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no43_3.'&nbsp; 3
                            '.$no433.'
                        </td>

                                                 
                </tr>

                <tr style="border:1px solid #000;margin: 45px;">
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>11</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Pneumothorax</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no11.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no11_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no11_3.'&nbsp; 3
                        </td>

                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>44</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Urinary retenion</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no44.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no44_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no44_3.'&nbsp; 3
                        </td>
                                                 
                </tr>

                <tr style="border:1px solid #000;margin: 45px;">
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>12</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Hypoxaemia</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no12.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no12_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no12_3.'&nbsp; 3
                        </td>

                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>45</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Itching</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no45.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no45_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no45_3.'&nbsp; 3
                        </td>
                                                 
                </tr>

                <tr style="border:1px solid #000;margin: 45px;">
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>13</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Hypoventilation</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no13.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no13_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no13_3.'&nbsp; 3
                        </td>

                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>46</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Drug error / Human error</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no46.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no46_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no46_3.'&nbsp; 3
                        </td>
                                                 
                </tr>

                <tr style="border:1px solid #000;margin: 45px;">
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>14</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Reintubation</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no14.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no14_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no14_3.'&nbsp; 3
                        </td>

                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>47</B></td>

                        <td  width="25%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;">
                <B>Other (specify).</B>&nbsp;
                <B style="text-decoration: underline;"> '.htmlspecialchars($row['no47_text']).' </B>
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no47.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no47_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no47_3.'&nbsp; 3
                        </td>
                                                 
                </tr>

                <tr style="border:1px solid #000;margin: 45px;">
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>15</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Atelectasis</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no15.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no15_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no15_3.'&nbsp; 3
                        </td>

                        <td style="text-align:top; border-right:0.5px solid #000;padding:4px;" width="3%" rowspan="5"><B>48</B></td>
                        <td  width="25%" style="border-right:0.5px solid #000;margin: 35px;padding:4px;" rowspan="5">
                        <B>Nausea & Vomitting</B><br>
                        &nbsp;&nbsp;&nbsp;<B>premed</B>&nbsp;
                <B style="text-decoration: underline;"> '.htmlspecialchars($row['no48_1_text']).' </B>&nbsp; <br>
                
                &nbsp;&nbsp;&nbsp;<B>intraop. prophylaxis</B>&nbsp;<B style="text-decoration: underline;"> '.htmlspecialchars($row['no48_2_text']).' </B>&nbsp;<br>
                <B>อาการ N / V</B><br>
                <B>ใช้ยา</B>&nbsp;
                <B style="text-decoration: underline;"> '.htmlspecialchars($row['no48_3_text']).' </B>
                </td>
                <td style="border-right:0.5px solid #000;padding:2px;" width="4%" rowspan="5">
                            '.$nodata.'<br>
                            '.$nodata.'<br>
                            '.$nodata.'<br>
                            '.$no48.'&nbsp; 1

                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%" rowspan="5">
                            '.$nodata.'<br>
                            '.$nodata.'<br>
                            '.$nodata.'<br>
                            '.$no48_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%" rowspan="5">
                            '.$nodata.'<br>
                            '.$nodata.'<br>
                            '.$nodata.'<br>
                            '.$no48_3.'&nbsp; 3
                        </td>

                                                 
                </tr>

                <tr style="border:1px solid #000;margin: 45px;">
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>16</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Pulmonary edema / effusion</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no16.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no16_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no16_3.'&nbsp; 3
                        </td>

                                                 
                </tr>

                <tr style="border:1px solid #000;margin: 45px;">
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>17</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Sig. hypertension (SBP > 180 mmHg)</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no17.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no17_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no17_3.'&nbsp; 3
                        </td>
                                                 
                </tr>

                <tr style="border:1px solid #000;margin: 45px;">
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>18</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Sig. hypertension (SBP < 80 mmHg)</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no18.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no18_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no18_3.'&nbsp; 3
                        </td>
                                                 
                </tr>

                <tr style="border:1px solid #000;margin: 45px;">
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>19</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Sig. arrhythmia (includeing techycardia > 120)</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no19.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no19_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no19_3.'&nbsp; 3
                        </td>
                                                 
                </tr>

                <tr style="border:1px solid #000;margin: 45px;">
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B></B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Sig. arrhythmia (bradycardia < 40)</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no19_1.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no19_1_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no19_1_3.'&nbsp; 3
                        </td>

                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B></B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>NONE</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no49.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no49_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no49_3.'&nbsp; 3
                        </td>
                                                 
                </tr>

                <tr style="border:1px solid #000;margin: 45px;">
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>20</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Myocardia ischaenia / MI</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no20.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no20_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no20_3.'&nbsp; 3
                        </td>

                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B></B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Direct tranfered to</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no50.'&nbsp; ward
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no50_2.'&nbsp; ICU
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no50_3.'&nbsp; Refer
                        </td>
                                                 
                </tr>

                <tr style="border:1px solid #000;margin: 45px;">
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>21</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Cardiac failure</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no21.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no21_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no21_3.'&nbsp; 3
                        </td>

                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px; vertical-align:top;" colspan="5" rowspan="15">
                        <div style="color:blue;font-weight:bold;font-size:13px;"> &nbsp;กิจกรรมการพยาบาล</div>
                        <div style="color:blue;font-weight:bold;font-size:13px;"> &nbsp;ประเมินผู้ป่วยหลังการระงับความรู้สึกใน 24 ชั่วโมง</div><br>
                        <div style="color:black;font-weight:bold;font-size:14px;">'.$no51.'&nbsp; No incidented anesthesia</div><br>
                        <div style="color:black;font-weight:bold;font-size:12px;">&nbsp;&nbsp;&nbsp;'.$no51_text.'</div>
                        <br>&nbsp;&nbsp;&nbsp;.....................................................................................................................<br><br><br><br><br><br>
                    
                       
                       <div style="text-align:center;font-weight:bold;font-size:12px;"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ลงชื่อผู้ตรวจสอบ .................................................</div>
                       <div style="text-align:center;font-weight:bold;font-size:12px;"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                       &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(....'.$name_full.'.....)</div>

<br><br>
                    
                       
<div style="text-align:center;font-weight:bold;font-size:12px;"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ลงชื่อผู้ตรวจสอบ .................................................</div>
<div style="text-align:center;font-weight:bold;font-size:12px;"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(....'.$name_full.'.....)</div>

<br><br>
                    
                       
<div style="text-align:center;font-weight:bold;font-size:12px;"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ลงชื่อผู้ตรวจสอบ .................................................</div>
<div style="text-align:center;font-weight:bold;font-size:12px;"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(....'.$name_full.'.....)</div>

                       </td>

                       
                                                 
                </tr>

                <tr style="border:1px solid #000;margin: 45px;">
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>22</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Cardiac arrest</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no22.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no22_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no22_3.'&nbsp; 3
                        </td>
                      
                        
                                                 
                </tr>

                <tr style="border:1px solid #000;margin: 45px;">
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>23</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Shock</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no23.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no23_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no23_3.'&nbsp; 3
                        </td>
                                                 
                </tr>

                <tr style="border:1px solid #000;margin: 45px;">
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>24</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Delayed emergence (ตื่นช้า >= 1 ชั่วโมง)</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no24.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no24_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no24_3.'&nbsp; 3
                        </td>
                                                 
                </tr>

                <tr style="border:1px solid #000;margin: 45px;">
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>25</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Coma / CVA</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no25.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no25_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no25_3.'&nbsp; 3
                        </td>
                                                 
                </tr>

                <tr style="border:1px solid #000;margin: 45px;">
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>26</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Awareness under GA</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no26.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no26_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no26_3.'&nbsp; 3
                        </td>
                                                 
                </tr>

                <tr style="border:1px solid #000;margin: 45px;">
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>27</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>High block / Total block</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no27.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no27_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no27_3.'&nbsp; 3
                        </td>
                                                 
                </tr>

                <tr style="border:1px solid #000;margin: 45px;">
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>28</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Post dural headache</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no28.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no28_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no28_3.'&nbsp; 3
                        </td>
                                                 
                </tr>

                <tr style="border:1px solid #000;margin: 45px;">
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>29</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Peripheral nerve injury</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no29.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no29_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no29_3.'&nbsp; 3
                        </td>
                                                 
                </tr>

                <tr style="border:1px solid #000;margin: 45px;">
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>30</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Volume overload Delirium</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no30.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no30_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no30_3.'&nbsp; 3
                        </td>
                                                 
                </tr>

                <tr style="border:1px solid #000;margin: 45px;">
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>31</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Back pain</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no31.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no31_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no31_3.'&nbsp; 3
                        </td>
                                                 
                </tr>

                <tr style="border:1px solid #000;margin: 45px;">
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>32</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Convulsion</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no32.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no32_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no32_3.'&nbsp; 3
                        </td>
                                                 
                </tr>

                <tr style="border:1px solid #000;margin: 45px;">
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>33</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>LA toxicity</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no33.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no33_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no33_3.'&nbsp; 3
                        </td>
                                                 
                </tr>

                <tr style="border:1px solid #000;margin: 45px;">
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>34</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Hypoglycemia</B></td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no34.'&nbsp; 1
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no34_2.'&nbsp; 2
                        </td>
                        <td style="border-right:0.5px solid #000;padding:2px;" width="4%">
                            '.$no34_3.'&nbsp; 3
                        </td>
                                                 
                </tr>

                <tr style="border:1px solid #000;margin: 45px;">
                        <td style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="3%"><B>35</B></td>
                        <td style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="34%" ><B>Electrolyte / Acid-base imbalance</B></td>
                        <td style="height:10px;" width="4%">
                            '.$no35.'&nbsp; 1
                        </td>
                        <td style="height:10px;" width="4%">
                            '.$no35_2.'&nbsp; 2
                        </td>
                        <td style="height:10px;" width="4%">
                            '.$no35_3.'&nbsp; 3
                        </td>
                                                 
                </tr>

                

                <tr style="border:1px solid #000;margin: 45px;">
                <td style="text-align:left; border-right:0.5px solid #000;padding:4px; vertical-align:top;" colspan="5" rowspan="1">              
                <td style="text-align:left; border-right:0.5px solid #000;padding:4px; vertical-align:top;" colspan="5" rowspan="15">
                <div style="font-weight:bold;font-size:12px;"> &nbsp;ชื่อ...'.$pname_row_ipt.' '.$fname_row_ipt.' '.$lname_row_ipt.'.....อายุ...'.$age_y_row_ipt.'...ปี'.'</div>
                <div style="font-weight:bold;font-size:13px;"> &nbsp;HN...'.$hn_row_ipt.'......AN...'.$an_row_ipt.'......</div>
                <div style="font-weight:bold;font-size:13px;"> &nbsp;Ward...'.$wardname_row_ipt.'....'.'</div><br>
                <div style="font-weight:bold;font-size:13px;"> &nbsp;แพทย์เจ้าของไข้...'.'.....................................'.'....'.'</div>


               </td>

               
                                         
        </tr>




                </table>
        ';
        $mpdf->WriteHTML($head);
        $mpdf->Output();
?>