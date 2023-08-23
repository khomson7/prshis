<?php  
//แก้ไขรายการเอกสาร 
 include('main-include.php');
        
        $getDoc = 'getDocument';
        $getData = 'getData';

        $getreport = 'AddmissionNurse';
        $docmain = $getDoc.$getreport;
        $docmain2 = '$'.$docmain ;
        $data = $getData.'Document'.$getreport;
        $data2 = '$'.$data ;
        
        $docmain2 = KphisQueryUtils::$docmain($an);
        if($docmain2){
            $data2  = KphisQueryUtils::$data($an);
            foreach($data2 as $row){
                $create_datetimeAddmissionNurse = $row['create_datetimeAddmissionNurse'];
                $update_datetimeAddmissionNurse = $row['update_datetimeAddmissionNurse'];
            ?>  <tr>
               
                    <td><a href="ipdnurse/ipd-nurse-admission-note.php?an=<?=$an?>&loginname=<?=$login?>" target="_blank">การประเมินสภาพผู้ป่วยแรกรับและแบบแผนสุขภาพ (ยกเว้นผู้ป่วยเด็กอายุ < 1 ปี)</a></td>
                    
                    <td><?=date("d/m/Y H:i:s", strtotime($create_datetimeAddmissionNurse))?></td>
                    <td><?=date("d/m/Y H:i:s", strtotime($update_datetimeAddmissionNurse))?></td>
                </tr>
            <?php }
        }

        $getreport = 'AddmissionDoctor';
        $docmain = $getDoc.$getreport;
        $docmain2 = '$'.$docmain ;
        $data = $getData.'Document'.$getreport;
        $data2 = '$'.$data ;
        
        $docmain2 = KphisQueryUtils::$docmain($an);
        if($docmain2){
            $data2  = KphisQueryUtils::$data($an);
            foreach($data2 as $row){
                $create_datetimeAddmissionDoctor = $row['create_datetimeAddmissionDoctor'];
                $update_datetimeAddmissionDoctor = $row['update_datetimeAddmissionDoctor'];
            ?>  <tr>
                 
                    <td>
                        <a href="ipdnurse/ipd-dr-admission-note-form.php?an=<?=$an?>&loginname=<?=$login?>" target="_blank">แบบบันทึกการรับใหม่ผู้ป่วยใน <?=htmlspecialchars(DbConstant::HOSPITAL_NAME)?></a></td>
            
                    <td><?=date("d/m/Y H:i:s", strtotime($create_datetimeAddmissionDoctor))?></td>
                    <td><?=date("d/m/Y H:i:s", strtotime($update_datetimeAddmissionDoctor))?></td>

                </tr>
            <?php }
        }


        $getreport = 'AddmissionDoctor2';
        $docmain = $getDoc.$getreport;
        $docmain2 = '$'.$docmain ;
        $data = $getData.'Document'.$getreport;
        $data2 = '$'.$data ;
        
        $docmain2 = KphisQueryUtils::$docmain($an);

        if($docmain2){
            $data2  = KphisQueryUtils::$data($an);
            foreach($data2 as $row){
                $create_datetimeAddmissionDoctor = $row['create_datetimeAddmissionDoctor'];
                $update_datetimeAddmissionDoctor = $row['update_datetimeAddmissionDoctor'];
            ?>  <tr>
                 
                    <td>
                        <a href="ipdnurse/ipd-dr-admission-note-form2.php?an=<?=$an?>&loginname=<?=$login?>" target="_blank">แบบบันทึกการรับใหม่ IPD (NewBorn) <?=htmlspecialchars(DbConstant::HOSPITAL_NAME)?></a></td>
            
                    <td><?=date("d/m/Y H:i:s", strtotime($create_datetimeAddmissionDoctor))?></td>
                    <td><?=date("d/m/Y H:i:s", strtotime($update_datetimeAddmissionDoctor))?></td>

                </tr>
            <?php }
        }


      
        
        $getreport = 'Summary'; //แก้ไขส่วนนี้
        $docmain = $getDoc.$getreport;
        $docmain2 = '$'.$docmain ;
        $data = $getData.'Document'.$getreport;
        $data2 = '$'.$data ;
        
        $docmain2 = KphisQueryUtils::$docmain($an);
        if($docmain2){
            $data2  = KphisQueryUtils::$data($an);
            foreach($data2 as $row){
                $create_datetimeSummary = $row['create_datetimeSummary'];
                $update_datetimeSummary = $row['update_datetimeSummary'];
            ?>  <tr>
                    <td><a href="ipdnurse/ipd-summary.php?an=<?=$an?>&loginname=<?=$login?>" target="_blank">SUMMARY FORM</a></td>
                    <td><?=date("d/m/Y H:i:s", strtotime($create_datetimeSummary))?></td>
                    <td><?=date("d/m/Y H:i:s", strtotime($update_datetimeSummary))?></td>
                </tr>
            <?php }
        }


        $getreport = 'OrComplication'; //แก้ไขส่วนนี้
        $docmain = $getDoc.$getreport;
        $docmain2 = '$'.$docmain ;
        $data = $getData.$getreport;
        $data2 = '$'.$data ;

        $docmain2 = ReportQueryUtils::$docmain($an);
        if($docmain2){
            $data2  = ReportQueryUtils::$data($an);
            foreach($data2 as $row){
                $create_datetime = $row['create_datetime'];
                $update_datetime= $row['update_datetime'];
            ?>  <tr>
                    <td><a href="ors/or-complication.php?an=<?=$an?>&loginname=<?=$login?>" target="_blank">ใบประเมินภาวะแทรกซ้อนหลังการระงับความรู้สึกใน 24 - 48 ชั่วโมง</a></td>
                    <td><?=date("d/m/Y H:i:s", strtotime($create_datetime))?></td>
                    <td><?=date("d/m/Y H:i:s", strtotime($update_datetime))?></td>
                </tr>
            <?php }
        }

        $getreport = 'LaborReport1'; //แก้ไขส่วนนี้
        $docmain = $getDoc.$getreport;
        $docmain2 = '$'.$docmain ;
        $data = $getData.$getreport;
        $data2 = '$'.$data ;

        $docmain2 = ReportQueryUtils::$docmain($an);
        if($docmain2){
            $data2  = ReportQueryUtils::$data($an);
            foreach($data2 as $row){
                $create_datetime = $row['create_datetime'];
                $update_datetime= $row['update_datetime'];
            ?>  <tr>
                    <td><a href="lr-report1/lr-report1.php?an=<?=$an?>&loginname=<?=$login?>" target="_blank">ข้อมูลแบบแผนสุขภาพ (Functional Health Pattern)</a></td>
                    <td><?=date("d/m/Y H:i:s", strtotime($create_datetime))?></td>
                    <td><?=date("d/m/Y H:i:s", strtotime($update_datetime))?></td>
                </tr>
            <?php }
        }




?>