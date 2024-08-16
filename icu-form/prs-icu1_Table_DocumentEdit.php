<?php  
//แก้ไขรายการเอกสาร 
 //include('../include/main-include.php');
 require_once '../include/main-include.php';
        
        $getDoc = 'getDocument';
        $getData = 'getData';

        $getreport = 'Icu1Form';
        $docmain = $getDoc.$getreport;
        $docmain2 = '$'.$docmain ;
        $data = $getData.$getreport;
        $data2 = '$'.$data ;

        
        
        $docmain2 = FormQueryUtils::$docmain($an);

        

        if($docmain2){
            $data2  = FormQueryUtils::$data($an);
            echo $data2;
            foreach($data2 as $row){
                $create_datetimeAddmissionNurse = $row['create_datetimeAddmissionNurse'];
                $update_datetimeAddmissionNurse = $row['update_datetimeAddmissionNurse'];
                $ids = $row['ids'];
            ?>  <tr>
               
                    <td><a href="form-icu1.php?an=<?=$an?>&id=<?=$ids?>&loginname=<?=$login?>" target="_blank">แบบประเมินผู้ป่วยวิกฤตแรกรับตามแนวคิด FANCAS โรงพยาบาลปราสาท (วันที่ <?=date("d/m/Y", strtotime($create_datetimeAddmissionNurse))?>)</a></td>
                    
                    <td><?=date("d/m/Y H:i:s", strtotime($create_datetimeAddmissionNurse))?></td>
                    <td><?=date("d/m/Y H:i:s", strtotime($update_datetimeAddmissionNurse))?></td>
                </tr>
            <?php }
        }


?>