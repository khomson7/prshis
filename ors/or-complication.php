<?php  // require_once './project/function/Session.php';
       // Session::checkPermissionAndShowMessage('IPD_DISCHARGE_SUMMARY','VIEW');
       require_once '../include/Session.php';
       // Session::checkLoginSessionAndShowMessage(); //เช็ค session
       // Session::checkPermissionAndShowMessage('IPD_NURSE_ADDMISSION_NOTE','VIEW');
        require_once '../mains/main-report.php';
        require_once '../mains/ipd-show-patient-main.php'; //เป็นส่วนที่แสดง ข้อมูลผู้ป่วย เช่น รูป,hn,an,ชื่อ-สกุล,แพ้ยา ฯลฯ
        require_once '../include/DbUtils.php';
        require_once '../include/KphisQueryUtils.php';
        $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
        $an = $_REQUEST['an'];//รับค่า an

        $hn = KphisQueryUtils::getHnByAn($an);// function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา

        $login = empty($_REQUEST['loginname']) ? null : $_REQUEST['loginname'];
        $loginname = $_SESSION['loginname'];
        $values =['loginname'=>$loginname];
        if($login != $loginname){
            session_start();
            session_destroy();
          }
        //----------------------เช็คว่า an นี้ มีข้อมูลหรือไม่
        $sql = "SELECT count(*) AS count_row, id FROM ".DbConstant::KPHIS_DBNAME.".prs_or_complication WHERE an = :an ";
        $id  = null;
        $parameters['an'] = $an;
        $stmt = $conn->prepare($sql);
        $stmt->execute($parameters);
        $row = $stmt->fetch();
        if($row['count_row'] > 0){
            $id = $row['id'];
        }
        //----------------------เช็คว่า an นี้ มีข้อมูลหรือไม่

        date_default_timezone_set('asia/bangkok');


?>

<style>
      .main {
        border: 1px solid #4287f5;
        height: 180px;
        width: 500px;
        position: relative;
      }
      .column1 {
        color: #4287f5;
        text-align: center;
      }
      .column2 {
        text-align: center;
      }
      #bottom {
        position: absolute;
        bottom: 0;
        left: 0;
      }
    </style>




<form id="or_complication_form">
    <div class="container-fluid">
        <div class="row">
            <div class="col-auto">
                <button type="button" class="btn btn-sm btn-primary btn-block" onclick="window.close()"><i class="fas fa-arrow-left"></i> กลับ</button>
            </div>
            <div class="col-auto p-1 font-weight-bold">
                ใบประเมินภาวะแทรกซ้อนหลังการระงับความรู้สึกใน 24-48 ชั่วโมง
            </div>
        </div><hr>

   <div class="row">
        <div class="col-md-12">


            <div class="row">
                <div class="col-sm-12">
                   <h6><p class="text-left"><B> Complication 1 = Intra-op  &emsp;&emsp;&emsp;&emsp;&ensp;  2= PACU
                   &emsp;&emsp;&emsp;&emsp;&ensp;  3= Post-op 24 hrs.
                   </B></p></h6>
               </div>
            </div>

            <div class="row">

        <div class="col-md-6">
           <!-- begin table -->
            <table id="bg-table" width="100%" style="border-collapse: collapse;font-size:10pt;margin-top:8px;">
            <!-- 1-->
            <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;1.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Retained ET tube / Tracheostomy tube</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no1" value="Y" name="no1">
                <label class="custom-control-label" for="no1">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no1_2" value="Y" name="no1_2">
                <label class="custom-control-label" for="no1_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no1_3" value="Y" name="no1_3">
                <label class="custom-control-label" for="no1_3">3</label>
        </div>
        </td>

           <!-- 2 -->
           <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;2.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Ventilatory support</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no2" value="Y" name="no2">
                <label class="custom-control-label" for="no2">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no2_2" value="Y" name="no2_2">
                <label class="custom-control-label" for="no2_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no2_3" value="Y" name="no2_3">
                <label class="custom-control-label" for="no2_3">3</label>
        </div>
        </td>
            <!-- 3 -->
            <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;3.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Sore throat</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no3" value="Y" name="no3">
                <label class="custom-control-label" for="no3">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no3_2" value="Y" name="no3_2">
                <label class="custom-control-label" for="no3_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no3_3" value="Y" name="no3_3">
                <label class="custom-control-label" for="no3_3">3</label>
        </div>
        </td>

           <!-- 4 -->
           <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;4.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Upper airway obstruction</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no4" value="Y" name="no4">
                <label class="custom-control-label" for="no4">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no4_2" value="Y" name="no4_2">
                <label class="custom-control-label" for="no4_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no4_3" value="Y" name="no4_3">
                <label class="custom-control-label" for="no4_3">3</label>
        </div>
        </td>

        <!-- 5 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;5.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Lower airway obstuction</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no5" value="Y" name="no5">
                <label class="custom-control-label" for="no5">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no5_2" value="Y" name="no5_2">
                <label class="custom-control-label" for="no5_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no5_3" value="Y" name="no5_3">
                <label class="custom-control-label" for="no5_3">3</label>
        </div>
        </td>

        <!-- 6 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;6.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Unpredicted difficult intubation</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no6" value="Y" name="no6">
                <label class="custom-control-label" for="no6">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no6_2" value="Y" name="no6_2">
                <label class="custom-control-label" for="no6_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no6_3" value="Y" name="no6_3">
                <label class="custom-control-label" for="no6_3">3</label>
        </div>
        </td>

        <!-- 7 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;7.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Aspiration</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no7" value="Y" name="no7">
                <label class="custom-control-label" for="no7">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no7_2" value="Y" name="no7_2">
                <label class="custom-control-label" for="no7_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no7_3" value="Y" name="no7_3">
                <label class="custom-control-label" for="no7_3">3</label>
        </div>
        </td>

        <!-- 8 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;8.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Airway injury</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no8" value="Y" name="no8">
                <label class="custom-control-label" for="no8">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no8_2" value="Y" name="no8_2">
                <label class="custom-control-label" for="no8_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no8_3" value="Y" name="no8_3">
                <label class="custom-control-label" for="no8_3">3</label>
        </div>
        </td>

        <!-- 9 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;9.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Dental injury</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no9" value="Y" name="no9">
                <label class="custom-control-label" for="no9">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no9_2" value="Y" name="no9_2">
                <label class="custom-control-label" for="no9_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no9_3" value="Y" name="no9_3">
                <label class="custom-control-label" for="no9_3">3</label>
        </div>
        </td>

        <!-- 10 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;10.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Esophageal intubation (เขียว หรือ SpO2 < 90 %)</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no10" value="Y" name="no10">
                <label class="custom-control-label" for="no10">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no10_2" value="Y" name="no10_2">
                <label class="custom-control-label" for="no10_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no10_3" value="Y" name="no10_3">
                <label class="custom-control-label" for="no10_3">3</label>
        </div>
        </td>

        <!-- 11 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;11.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Pneumothorax</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no11" value="Y" name="no11">
                <label class="custom-control-label" for="no11">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no11_2" value="Y" name="no11_2">
                <label class="custom-control-label" for="no11_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no11_3" value="Y" name="no11_3">
                <label class="custom-control-label" for="no11_3">3</label>
        </div>
        </td>

        <!-- 12 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;12.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Hypoxaemia</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no12" value="Y" name="no12">
                <label class="custom-control-label" for="no12">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no12_2" value="Y" name="no12_2">
                <label class="custom-control-label" for="no12_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no12_3" value="Y" name="no12_3">
                <label class="custom-control-label" for="no12_3">3</label>
        </div>
        </td>

        <!-- 13 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;13.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Hypoventilation</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no13" value="Y" name="no13">
                <label class="custom-control-label" for="no13">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no13_2" value="Y" name="no13_2">
                <label class="custom-control-label" for="no13_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no13_3" value="Y" name="no13_3">
                <label class="custom-control-label" for="no13_3">3</label>
        </div>
        </td>

        <!-- 14 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;14.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Reintubation</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no14" value="Y" name="no14">
                <label class="custom-control-label" for="no14">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no14_2" value="Y" name="no14_2">
                <label class="custom-control-label" for="no14_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no14_3" value="Y" name="no14_3">
                <label class="custom-control-label" for="no14_3">3</label>
        </div>
        </td>

        <!-- 15 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;15.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Atelectasis</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no15" value="Y" name="no15">
                <label class="custom-control-label" for="no15">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no15_2" value="Y" name="no15_2">
                <label class="custom-control-label" for="no15_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no15_3" value="Y" name="no15_3">
                <label class="custom-control-label" for="no15_3">3</label>
        </div>
        </td>

        <!-- 16 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;16.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Pulmonary edema / effusion</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no16" value="Y" name="no16">
                <label class="custom-control-label" for="no16">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no16_2" value="Y" name="no16_2">
                <label class="custom-control-label" for="no16_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no16_3" value="Y" name="no16_3">
                <label class="custom-control-label" for="no16_3">3</label>
        </div>
        </td>

        <!-- 17 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;17.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Sig. hypertension (SBP > 180 mmHg)</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no17" value="Y" name="no17">
                <label class="custom-control-label" for="no17">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no17_2" value="Y" name="no17_2">
                <label class="custom-control-label" for="no17_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no17_3" value="Y" name="no17_3">
                <label class="custom-control-label" for="no17_3">3</label>
        </div>
        </td>

        <!-- 18 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;18.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Sig. hypertension (SBP < 80 mmHg)</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no18" value="Y" name="no18">
                <label class="custom-control-label" for="no18">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no18_2" value="Y" name="no18_2">
                <label class="custom-control-label" for="no18_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no18_3" value="Y" name="no18_3">
                <label class="custom-control-label" for="no18_3">3</label>
        </div>
        </td>

        <!-- 19 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;19.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Sig. arrhythmia (includeing techycardia > 120)</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no19" value="Y" name="no19">
                <label class="custom-control-label" for="no19">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no19_2" value="Y" name="no19_2">
                <label class="custom-control-label" for="no19_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no19_3" value="Y" name="no19_3">
                <label class="custom-control-label" for="no19_3">3</label>
        </div>
        </td>

        <!-- 19_1 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Sig. arrhythmia (bradycardia < 40)</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no19_1" value="Y" name="no19_1">
                <label class="custom-control-label" for="no19_1">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no19_1_2" value="Y" name="no19_1_2">
                <label class="custom-control-label" for="no19_1_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no19_1_3" value="Y" name="no19_1_3">
                <label class="custom-control-label" for="no19_1_3">3</label>
        </div>
        </td>

        <!-- 20 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;20.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Myocardia ischaenia / MI</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no20" value="Y" name="no20">
                <label class="custom-control-label" for="no20">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no20_2" value="Y" name="no20_2">
                <label class="custom-control-label" for="no20_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no20_3" value="Y" name="no20_3">
                <label class="custom-control-label" for="no20_3">3</label>
        </div>
        </td>

        <!-- 21 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;21.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Cardiac failure</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no21" value="Y" name="no21">
                <label class="custom-control-label" for="no21">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no21_2" value="Y" name="no21_2">
                <label class="custom-control-label" for="no21_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no21_3" value="Y" name="no21_3">
                <label class="custom-control-label" for="no21_3">3</label>
        </div>
        </td>

        <!-- 22 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;22.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Cardiac arrest</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no22" value="Y" name="no22">
                <label class="custom-control-label" for="no22">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no22_2" value="Y" name="no22_2">
                <label class="custom-control-label" for="no22_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no22_3" value="Y" name="no22_3">
                <label class="custom-control-label" for="no22_3">3</label>
        </div>
        </td>

        <!-- 23 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;23.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Shock</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no23" value="Y" name="no23">
                <label class="custom-control-label" for="no23">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no23_2" value="Y" name="no23_2">
                <label class="custom-control-label" for="no23_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no23_3" value="Y" name="no23_3">
                <label class="custom-control-label" for="no23_3">3</label>
        </div>
        </td>

        <!-- 24 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;24.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Delayed emergence (ตื่นช้า >= 1 ชั่วโมง)</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no24" value="Y" name="no24">
                <label class="custom-control-label" for="no24">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no24_2" value="Y" name="no24_2">
                <label class="custom-control-label" for="no24_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no24_3" value="Y" name="no24_3">
                <label class="custom-control-label" for="no24_3">3</label>
        </div>
        </td>

        <!-- 25 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;25.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Coma / CVA</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no25" value="Y" name="no25">
                <label class="custom-control-label" for="no25">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no25_2" value="Y" name="no25_2">
                <label class="custom-control-label" for="no25_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no25_3" value="Y" name="no25_3">
                <label class="custom-control-label" for="no25_3">3</label>
        </div>
        </td>

        <!-- 26 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;26.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Awareness under GA</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no26" value="Y" name="no26">
                <label class="custom-control-label" for="no26">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no26_2" value="Y" name="no26_2">
                <label class="custom-control-label" for="no26_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no26_3" value="Y" name="no26_3">
                <label class="custom-control-label" for="no26_3">3</label>
        </div>
        </td>

        <!-- 27 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;27.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;High block / Total block</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no27" value="Y" name="no27">
                <label class="custom-control-label" for="no27">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no27_2" value="Y" name="no27_2">
                <label class="custom-control-label" for="no27_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no27_3" value="Y" name="no27_3">
                <label class="custom-control-label" for="no27_3">3</label>
        </div>
        </td>

        <!-- 28 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;28.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Post dural headache</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no28" value="Y" name="no28">
                <label class="custom-control-label" for="no28">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no28_2" value="Y" name="no28_2">
                <label class="custom-control-label" for="no28_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no28_3" value="Y" name="no28_3">
                <label class="custom-control-label" for="no28_3">3</label>
        </div>
        </td>

        <!-- 29 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;29.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Peripheral nerve injury</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no29" value="Y" name="no29">
                <label class="custom-control-label" for="no29">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no29_2" value="Y" name="no29_2">
                <label class="custom-control-label" for="no29_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no29_3" value="Y" name="no29_3">
                <label class="custom-control-label" for="no29_3">3</label>
        </div>
        </td>

        <!-- 30 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;30.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Volume overload Delirium</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no30" value="Y" name="no30">
                <label class="custom-control-label" for="no30">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no30_2" value="Y" name="no30_2">
                <label class="custom-control-label" for="no30_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no30_3" value="Y" name="no30_3">
                <label class="custom-control-label" for="no30_3">3</label>
        </div>
        </td>

        <!-- 31 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;31.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Back pain</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no31" value="Y" name="no31">
                <label class="custom-control-label" for="no31">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no31_2" value="Y" name="no31_2">
                <label class="custom-control-label" for="no31_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no31_3" value="Y" name="no31_3">
                <label class="custom-control-label" for="no31_3">3</label>
        </div>
        </td>

        <!-- 32 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;32.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Convulsion</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no32" value="Y" name="no32">
                <label class="custom-control-label" for="no32">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no32_2" value="Y" name="no32_2">
                <label class="custom-control-label" for="no32_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no32_3" value="Y" name="no32_3">
                <label class="custom-control-label" for="no32_3">3</label>
        </div>
        </td>

        <!-- 33 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;33.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;LA toxicity</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no33" value="Y" name="no33">
                <label class="custom-control-label" for="no33">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no33_2" value="Y" name="no33_2">
                <label class="custom-control-label" for="no33_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no33_3" value="Y" name="no33_3">
                <label class="custom-control-label" for="no33_3">3</label>
        </div>
        </td>

        <!-- 34 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;34.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Hypoglycemia</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no34" value="Y" name="no34">
                <label class="custom-control-label" for="no34">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no34_2" value="Y" name="no34_2">
                <label class="custom-control-label" for="no34_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no34_3" value="Y" name="no34_3">
                <label class="custom-control-label" for="no34_3">3</label>
        </div>
        </td>

        <!-- 35 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;35.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Electrolyte / Acid-base imbalance</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no35" value="Y" name="no35">
                <label class="custom-control-label" for="no35">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no35_2" value="Y" name="no35_2">
                <label class="custom-control-label" for="no35_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no35_3" value="Y" name="no35_3">
                <label class="custom-control-label" for="no35_3">3</label>
        </div>
        </td>



        </tr>

            </table>
         <!-- end table -->
         </div>

         <div class="col-md-6">
           <!-- begin table -->
            <table id="bg-table" width="100%" style="border-collapse: collapse;font-size:10pt;margin-top:8px;">

            <!-- 36 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;36.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Hypothermia (Temp < 35 °C)</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no36" value="Y" name="no36">
                <label class="custom-control-label" for="no36">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no36_2" value="Y" name="no36_2">
                <label class="custom-control-label" for="no36_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no36_3" value="Y" name="no36_3">
                <label class="custom-control-label" for="no36_3">3</label>
        </div>
        </td>

        <!-- 37 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;37.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Fever (Temp > 38 °C) , MH</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no37" value="Y" name="no37">
                <label class="custom-control-label" for="no37">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no37_2" value="Y" name="no37_2">
                <label class="custom-control-label" for="no37_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no37_3" value="Y" name="no37_3">
                <label class="custom-control-label" for="no37_3">3</label>
        </div>
        </td>

        <!-- 38 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;38.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Transfusion reaction / Mismatch</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no38" value="Y" name="no38">
                <label class="custom-control-label" for="no38">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no38_2" value="Y" name="no38_2">
                <label class="custom-control-label" for="no38_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no38_3" value="Y" name="no38_3">
                <label class="custom-control-label" for="no38_3">3</label>
        </div>
        </td>

        <!-- 39 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;39.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Coagulopathy</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no39" value="Y" name="no39">
                <label class="custom-control-label" for="no39">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no39_2" value="Y" name="no39_2">
                <label class="custom-control-label" for="no39_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no39_3" value="Y" name="no39_3">
                <label class="custom-control-label" for="no39_3">3</label>
        </div>
        </td>

        <!-- 40 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;40.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Massive blood loss</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no40" value="Y" name="no40">
                <label class="custom-control-label" for="no40">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no40_2" value="Y" name="no40_2">
                <label class="custom-control-label" for="no40_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no40_3" value="Y" name="no40_3">
                <label class="custom-control-label" for="no40_3">3</label>
        </div>
        </td>

        <!-- 41 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;41.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Allergic reaction / Anaphylactic shock</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no41" value="Y" name="no41">
                <label class="custom-control-label" for="no41">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no41_2" value="Y" name="no41_2">
                <label class="custom-control-label" for="no41_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no41_3" value="Y" name="no41_3">
                <label class="custom-control-label" for="no41_3">3</label>
        </div>
        </td>

        <!-- 42 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;42.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Burn</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no42" value="Y" name="no42">
                <label class="custom-control-label" for="no42">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no42_2" value="Y" name="no42_2">
                <label class="custom-control-label" for="no42_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no42_3" value="Y" name="no42_3">
                <label class="custom-control-label" for="no42_3">3</label>
        </div>
        </td>

        <!-- 42_2 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Shivering</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no42_2_1" value="Y" name="no42_2_1">
                <label class="custom-control-label" for="no42_2_1">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no42_2_2" value="Y" name="no42_2_2">
                <label class="custom-control-label" for="no42_2_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no42_2_3" value="Y" name="no42_2_3">
                <label class="custom-control-label" for="no42_2_3">3</label>
        </div>
        </td>

        <!-- 42_3 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Warm</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no42_3_1" value="Y" name="no42_3_1">
                <label class="custom-control-label" for="no42_3_1">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no42_3_2" value="Y" name="no42_3_2">
                <label class="custom-control-label" for="no42_3_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no42_3_3" value="Y" name="no42_3_3">
                <label class="custom-control-label" for="no42_3_3">3</label>
        </div>
        </td>

        <!-- 43 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;43.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%"><span style="color:blue;font-weight:bold">ใช้ยา.</span>
            <input type="text" class="form-control form-control-sm CheckPer_2" name="no43_text" id="no43_text">

        </td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no43" value="Y" name="no43">
                <label class="custom-control-label" for="no43">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no43_2" value="Y" name="no43_2">
                <label class="custom-control-label" for="no43_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no43_3" value="Y" name="no43_3">
                <label class="custom-control-label" for="no43_3">3</label>
        </div>
        </td>

        <!-- 44 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;44.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Urinary retenion</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no44" value="Y" name="no44">
                <label class="custom-control-label" for="no44">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no44_2" value="Y" name="no44_2">
                <label class="custom-control-label" for="no44_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no44_3" value="Y" name="no44_3">
                <label class="custom-control-label" for="no44_3">3</label>
        </div>
        </td>

        <!-- 45 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;45.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Itching</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no45" value="Y" name="no45">
                <label class="custom-control-label" for="no45">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no45_2" value="Y" name="no45_2">
                <label class="custom-control-label" for="no45_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no45_3" value="Y" name="no45_3">
                <label class="custom-control-label" for="no45_3">3</label>
        </div>
        </td>

        <!-- 46 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;46.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Drug error / Human error</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no46" value="Y" name="no46">
                <label class="custom-control-label" for="no46">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no46_2" value="Y" name="no46_2">
                <label class="custom-control-label" for="no46_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no46_3" value="Y" name="no46_3">
                <label class="custom-control-label" for="no46_3">3</label>
        </div>
        </td>

        <!-- 47 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;47.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%"><span style="color:blue;font-weight:bold">Other (specify).</span>
            <input type="text" class="form-control form-control-sm CheckPer_2" name="no47_text" id="no47_text">
        </td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no47" value="Y" name="no47">
                <label class="custom-control-label" for="no47">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no47_2" value="Y" name="no47_2">
                <label class="custom-control-label" for="no47_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no47_3" value="Y" name="no47_3">
                <label class="custom-control-label" for="no47_3">3</label>
        </div>
        </td>

        <!-- 48 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;48.</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%"><span style="color:blue;font-weight:bold">Nausea & Vommitting.</span>
            <br>
            <span style="color:blue;">premed.</span>
            <input type="text" class="form-control form-control-sm CheckPer_2" name="no48_1_text" id="no48_1_text">

            <span style="color:blue;">intraop. prophylaxis</span>
            <input type="text" class="form-control form-control-sm CheckPer_2" name="no48_2_text" id="no48_2_text">
            <span style="color:blue;font-weight:bold">อาการ N / V</span>
            <br>
            <span style="color:blue;">ใช้ยา.</span>
            <input type="text" class="form-control form-control-sm CheckPer_2" name="no48_3_text" id="no48_3_text">
        </td>

            <td  style="vertical-align:bottom; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no48" value="Y" name="no48">
                <label class="custom-control-label" for="no48">1</label>
            </div>
           </td>
           <td  style="vertical-align:bottom; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no48_2" value="Y" name="no48_2">
                <label class="custom-control-label" for="no48_2">2</label>
           </div>
           </td>
           <td  style="vertical-align:bottom; border-right:0.5px solid #000;padding:4px;" width="2%">

        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no48_3" value="Y" name="no48_3">
                <label class="custom-control-label" for="no48_3">3</label>
        </div>
        </td>

        <!-- 49 -->
        <tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;NONE</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no49" value="Y" name="no49">
                <label class="custom-control-label" for="no49">1</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no49_2" value="Y" name="no49_2">
                <label class="custom-control-label" for="no49_2">2</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no49_3" value="Y" name="no49_3">
                <label class="custom-control-label" for="no49_3">3</label>
        </div>
        </td>

<!-- 50 -->
<tr style="border:1px solid #000;margin: 45px;">
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="1%">&nbsp;</td>
            <td  style="text-align:left; border-right:0.5px solid #000;padding:4px;" width="10%">&nbsp;Direct tranfered to</td>
            <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
            <div class="custom-control custom-checkbox col-sm-1">
                 <input type="checkbox"  class="custom-control-input" id="no50" value="Y" name="no50">
                <label class="custom-control-label" for="no50">Ward</label>
            </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
           <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no50_2" value="Y" name="no50_2">
                <label class="custom-control-label" for="no50_2">ICU</label>
           </div>
           </td>
           <td  style="text-align:center; border-right:0.5px solid #000;padding:4px;" width="2%">
        <div class="custom-control custom-checkbox col-sm-1">
                <input type="checkbox" class="custom-control-input" id="no50_3" value="Y" name="no50_3">
                <label class="custom-control-label" for="no50_3">Refer</label>
        </div>
        </td>


        </tr>

            </table>
         <!-- end table -->

       <div style="color:blue;font-weight:bold"> &nbsp;กิจกรรมการพยาบาล</div>
       <div style="color:blue;font-weight:bold"> &nbsp;ประเมินผู้ป่วยหลังการระงับความรู้สึกใน 24 ชั่วโมง</div>
       <div class="custom-control custom-checkbox col-sm-6">
                <input type="checkbox" class="custom-control-input" id="no51" value="Y" name="no51">
                <label style="color:blue;font-weight:bold" class="custom-control-label" for="no51">No incidented anesthesia</label>
        </div>
<hr>
        <div class="col-md-12">
                        <!--<textarea class="form-control CheckPer_2" name="no51_text" id="no51_text" rows="3"></textarea>-->
                        <textarea class="form-control" id="no51_text" name="no51_text" rows="3"></textarea>
                    </div>
                <br>
                    <div class="col-md-4">
                <div class="form-group">
                    <label class="mb-3" for="action-person-nurse">ลงชื่อผู้ตรวจสอบ(Intra-op)</label>
                    <div class="input-group">
                        <input type="text" class="form-control form-control" id="nurse_name"  name="nurse_name"  value="<?=htmlspecialchars($row['nurse_name'])?>" readonly>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-secondary" onclick="PersonAsCurrentUser_1()">ลงชื่อ</button>
                        </div>
                    </div>
                </div>
            </div>

            <br>
                    <div class="col-md-4">
                <div class="form-group">
                    <label class="mb-3" for="action-person-nurse">ลงชื่อผู้ตรวจสอบ(PACU)</label>
                    <div class="input-group">
                        <input type="text" class="form-control form-control" id="nurse_name2"  name="nurse_name2"  value="<?=htmlspecialchars($row['nurse_name2'])?>" readonly>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-secondary" onclick="PersonAsCurrentUser_2()">ลงชื่อ</button>
                        </div>
                    </div>
                </div>
            </div>

            <br>
                    <div class="col-md-4">
                <div class="form-group">
                    <label class="mb-3" for="action-person-nurse">ลงชื่อผู้ตรวจสอบ(Post-op 24 hrs.)</label>
                    <div class="input-group">
                        <input type="text" class="form-control form-control" id="nurse_name3"  name="nurse_name3"  value="<?=htmlspecialchars($row['nurse_name3'])?>" readonly>
                        <div class="input-group-append">

                            <button type="button" class="btn btn-secondary" onclick="PersonAsCurrentUser_3()">ลงชื่อ</button>

                        </div>
                    </div>
                </div>
            </div>



         </div>

    </div>


        </div>
    </div>


        <div class="row">
            <input type="hidden" id="an" name="an" value="<?=$an?>"><!-- ฟิลด์ hidden  "an"  -->
            <input type="hidden" id="id" name="id" value="<?=$id?>"><!-- ฟิลด์ hidden "id"  -->


            <div class="col-md-9">
                <div id="data_or_complication_save"></div><!-- แสดงข้อความการบันมึก >> บันทึกข้อมูลสำเร็จ, EORROR -->
                <div id="data_or_complication_edit"></div>
                <div id="data_or_complication_update"></div>
            </div>
            <div class="col-md-12 text-right">
                <?php
                if((($id == null)) || (($id != null))){?>
                    <button type="button" class="btn btn-primary" id="btn_or_complication" onclick="or_complication_save()"><i class="fas fa-save"></i> บันทึก</button>
                <?php } ?>
                <a href="or-complication-pdf.php?an=<?php echo $an;?>" target="_blank" class="btn btn-secondary"><i class="fas fa-file-pdf"></i> Print <U>PDF</U> File</a>
            </div>
        </div><br>
    </div>
</form>
<script>

function PersonAsCurrentUser_1(){
        const nurse_name = <?=json_encode($_SESSION['name'])?>;
        const entryposition = <?=json_encode($_SESSION['entryposition'])?>;
        $("#nurse_name").val(nurse_name);
        $("#nurse_pos").val(entryposition);
    }
    function PersonAsCurrentUser_2(){
        const nurse_name2 = <?=json_encode($_SESSION['name'])?>;
        const entryposition = <?=json_encode($_SESSION['entryposition'])?>;
        $("#nurse_name2").val(nurse_name2);
        $("#nurse_pos").val(entryposition);
    }
    function PersonAsCurrentUser_3(){
        const nurse_name3 = <?=json_encode($_SESSION['name'])?>;
        const entryposition = <?=json_encode($_SESSION['entryposition'])?>;
        $("#nurse_name3").val(nurse_name3);
        $("#nurse_pos").val(entryposition);
    }

    $( document ).ready(function() {
        var id =  <?=json_encode($id)?>;
        if(id != null && id != ""){
            or_complication_edit(<?=json_encode($id)?>,<?=json_encode($an)?>);
        }else{
           // import_DataOR_Hosxp(<?=json_encode($an)?>);
        }
        //summary_CheckPer();
    });



    function or_complication_save(){
      //  var summary_plan_date = $("#summary_plan_date").val();
       // var summary_plan_time = $("#summary_plan_time").val();
        //var principal_diagnosis = $("#principal_diagnosis").val();
        var id = $("#id").val();

        //url
        var url_save = 'or-complication-save.php';

       var url_update = 'or-complication-update.php';

       /* if(summary_plan_date == ""){
            alert("กรอกวันที่");
            $("#summary_plan_date").focus();
        }else if (summary_plan_time == ""){
            alert("กรอกเวลา");
            $("#summary_plan_time").focus();
        } else { */
            $("#btn_or_complication").attr('disabled', 'disabled');
            if(id == ""){
                $.post(url_save,$("#or_complication_form").serialize(),function(data_save){
                    $("#data_or_complication_save").html(data_save);
                    window.location.reload(true);
                })
                .fail(function(){
                    alert("บันทึกข้อมูลไม่สำเร็จ");
                    $("#btn_or_complication").removeAttr("disabled");
                });
            }else{
                $.post(url_update,$("#or_complication_form").serialize(),function(data_update){
                    $("#data_or_complication_update").html(data_update);
                    window.location.reload(true);
                })
                .fail(function(){
                    alert("บันทึกข้อมูลไม่สำเร็จ");
                    $("#btn_or_complication").removeAttr("disabled");
                });
            }
       // }
    }

    function or_complication_edit(id,an){
        var url="or-complication-edit.php";
        $.post(url,{id,an},function(data_edit){
            $("#data_or_complication_edit").html(data_edit);
            //console.log(data_edit);
        });
    }


</script>
