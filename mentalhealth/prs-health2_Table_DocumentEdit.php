<?php  
//แก้ไขรายการเอกสาร 
 //include('../include/main-include.php');
 require_once '../include/main-include.php';
        
        $getDoc = 'getDocument';
        $getData = 'getData';

        $getreport = 'Mental2Form';
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
                $total_sum = isset($row['total_Sum']) ? (int)$row['total_Sum'] : 0;
                $dateAlert = $row['date_alert'];

                // Set the background color based on the value of total_sum
                if ($total_sum >= 1 && $total_sum <= 36) {
                    $bg_color = 'green';
                    $message = 'แนะนำประเมินต่อทุก 1 สัปดาห์';
                } elseif ($total_sum >= 37 && $total_sum <= 40) {
                    $bg_color = 'orange';
                    $message = 'แนะนำประเมินต่อทุก 2 วัน';
                } elseif ($total_sum > 40) {
                    $bg_color = 'red';
                    $message = 'แนะนำประเมินวันละ 1 ครั้ง';
                } else {
                    $bg_color = ''; // default if the value is outside the range
                }


                $ids = $row['ids'];
            ?>  <tr>
               
                    <td><a href="form-mental-health21.php?an=<?=$an?>&id=<?=$ids?>&loginname=<?=$login?>" target="_blank">แบบประเมินอาการทางจิต(Brief Phychiatric Rating Scale : BRPS) (วันที่ <?=date("d/m/Y", strtotime($create_datetimeAddmissionNurse))?>)</a></td>
                    
                    <td><div class='badge text-white mt-1 font-weight-bold' style="font-size:100%; background-color: <?= htmlspecialchars($bg_color) ?>;">
    <!-- Your content here -->
    คะแนนรวม <?= htmlspecialchars($total_sum) ?> คะแนน
    <br>
    <?= htmlspecialchars($message) ?>
    <br>
    <font color='orange'>วันที่ <?=date("d/m/Y", strtotime($dateAlert))?></font>
</div></td>
                    <td><?=date("d/m/Y H:i:s", strtotime($create_datetimeAddmissionNurse))?></td>
                    <td><?=date("d/m/Y H:i:s", strtotime($update_datetimeAddmissionNurse))?></td>
                </tr>
            <?php }
        }


?>