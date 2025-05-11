<?php  
//แก้ไขรายการเอกสาร 
 //include('../include/main-include.php');
 require_once '../include/main-include.php';
        
        $getDoc = 'getDocument';
        $getData = 'getData';

        $getreport = 'Due';
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
                $drugname = $row['drugname'];
                $physician_approved = $row['physician_approved'];
                $ids = $row['ids'];

                 // Set the background color based on the value of total_sum
                 if ($physician_approved == null) {
                    $bg_color = 'red';
                   
                } else {
                    $bg_color = 'green'; // default if the value is outside the range
                }

            ?>  <tr>
               
                    <td><a href="form-due.php?an=<?=$an?>&id=<?=$ids?>&loginname=<?=$login?>" target="_blank">แบบประเมินDue (วันที่ <?=date("d/m/Y", strtotime($create_datetimeAddmissionNurse))?>)</a></td>
                    <td><div class='badge text-white mt-1 font-weight-bold' style="font-size:100%; background-color: <?= htmlspecialchars($bg_color) ?>;">
   <?= htmlspecialchars($drugname) ?> 
</div></td>
                    <td><?=date("d/m/Y H:i:s", strtotime($create_datetimeAddmissionNurse))?></td>
                    <td><?=date("d/m/Y H:i:s", strtotime($update_datetimeAddmissionNurse))?></td>
                </tr>
            <?php }
        }


?>