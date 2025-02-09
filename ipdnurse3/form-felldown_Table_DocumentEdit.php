<?php  
//แก้ไขรายการเอกสาร 
 //include('../include/main-include.php');
 require_once '../include/main-include.php';
        
        $getDoc = 'getDocument';
        $getData = 'getData';

        $getreport = 'FellDownForm';
        $docmain = $getDoc.$getreport;
        $docmain2 = '$'.$docmain ;
        $data = $getData.$getreport;
        $data2 = '$'.$data ;

        
        
        $docmain2 = FormQueryUtils::$docmain($an);

        

        if($docmain2){
            $data2  = FormQueryUtils::$data($an);
            //echo $data2;
            foreach($data2 as $row){
                $create_datetimeAddmissionNurse = $row['create_datetimeAddmissionNurse'];
                $update_datetimeAddmissionNurse = $row['update_datetimeAddmissionNurse'];
                $my_score= $row['my_score'];
                
                
                

                
                $ids = $row['ids'];

                echo $Score;
            ?>  
        
            <tr>
               
                    <td><a href="form-felldown.php?an=<?=$an?>&id=<?=$ids?>&loginname=<?=$login?>" target="_blank">แบบประเมินภาวะเสี่ยงต่อการพลัดตก หกล้ม (ผู้ป่วยผู้ใหญ่) (วันที่ <?=date("d/m/Y", strtotime($create_datetimeAddmissionNurse))?>)</a></td>
                    
                    <td class='font-weight-bold' style='text-align: center; color: white;  font-size:100%; background-color: gray;'>
                    <?= htmlspecialchars($my_score) ?>
</td>
                    <td><?=date("d/m/Y H:i:s", strtotime($create_datetimeAddmissionNurse))?></td>
                    <td><?=date("d/m/Y H:i:s", strtotime($update_datetimeAddmissionNurse))?></td>
                </tr>
            <?php }
        }


?>