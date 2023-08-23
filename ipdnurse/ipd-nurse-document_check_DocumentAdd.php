<?php  

//ตัวเลือก เพิ่มเอกสาร
include('main-include.php');

//echo $login;

     $getDocumentAddmissionNurse = KphisQueryUtils::getDocumentAddmissionNurse($an);

        if(!($getDocumentAddmissionNurse)){
            ?><a class="dropdown-item" href="ipdnurse/ipd-nurse-admission-note.php?an=<?=$an?>&loginname=<?=$login?>" target="_blank"><em class="fas fa-file-alt"></em> การประเมินสภาพผู้ป่วยแรกรับและแบบแผนสุขภาพ (ยกเว้นผู้ป่วยเด็กอายุ < 1 ปี)</a><?php
        }

        $getDocumentAddmissionDoctor = KphisQueryUtils::getDocumentAddmissionDoctor($an);

        if(!($getDocumentAddmissionDoctor)){
            ?><a class="dropdown-item" href="ipdnurse/ipd-dr-admission-note-form.php?an=<?=$an?>&loginname=<?=$login?>" target="_blank"><em class="fas fa-file-alt"></em> แบบบันทึกการรับใหม่ผู้ป่วยใน <?=htmlspecialchars(DbConstant::HOSPITAL_NAME)?></a><?php
        }

        $getDocumentSummary = KphisQueryUtils::getDocumentSummary($an);

        if(!($getDocumentSummary)){
            ?><a class="dropdown-item" href="ipdnurse/ipd-summary.php?an=<?=$an?>&loginname=<?=$login?>" target="_blank"><em class="fas fa-file-alt"></em> SUMMARY FORM</a><?php
        }

        $getDocumentNurseSummary = KphisQueryUtils::getDocumentNurseSummary($an);

        if(!($getDocumentNurseSummary)){
            ?><a class="dropdown-item" href="ipdnurse/ipd-nurse-summary.php?an=<?=$an?>&loginname=<?=$login?>" target="_blank"><em class="fas fa-file-alt"></em> IpdNurseSummary</a><?php
        }

     
//ReportQueryUtil
        $docmain = 'getDocumentOrComplication';
        $docmain2 = '$'.$docmain ;
        $docmain2 = ReportQueryUtils::$docmain($an);
        if(!($docmain2)){
            ?><a class="dropdown-item" href="ors/or-complication.php?an=<?=$an?>&loginname=<?=$login?>" target="_blank"><em class="fas fa-file-alt"></em> ใบประเมินภาวะแทรกซ้อนหลังระงับความรู้สึกใน 24-48 ชั่วโมง</a><?php
        } 

        $docmain = 'getDocumentLaborReport1';
        $docmain2 = '$'.$docmain ;
        $docmain2 = ReportQueryUtils::$docmain($an);
        if(!($docmain2)){
            ?><a class="dropdown-item" href="lr-report1/lr-report1.php?an=<?=$an?>&loginname=<?=$login?>" target="_blank"><em class="fas fa-file-alt"></em> ข้อมูลแบบแผนสุขภาพ (Functional Health Pattern)</a><?php
        } 

        $docmain = 'getDocumentLaborReport2';
        $docmain2 = '$'.$docmain ;
        $docmain2 = ReportQueryUtils::$docmain($an);
        if(!($docmain2)){
            ?><a class="dropdown-item" href="lr-report1/lr-report2.php?an=<?=$an?>&loginname=<?=$login?>" target="_blank"><em class="fas fa-file-alt"></em> ข้อมูลแบบแผนสุขภาพ2</a><?php
        } 


       
   
  
   

?>