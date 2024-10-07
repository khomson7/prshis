<?php  
//แก้ไขรายการเอกสาร 
 //include('../include/main-include.php');
 require_once '../include/main-include.php';
        
        $getDoc = 'getDocument';
        $getData = 'getData';

        $getreport = 'Mental3Form';
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
                $variation1 = isset($row['variation_1']) ? (int)$row['variation_1'] : 0;
                $variation2 = isset($row['variation2']) ? (int)$row['variation2'] : 0;
                $variation3 = isset($row['variation3']) ? (int)$row['variation3'] : 0;
                $variation4 = isset($row['variation4']) ? (int)$row['variation4'] : 0;
                
                // Set the background color based on the value of total_sum
                
                $font_color = 'white';
                $bg_color = 'green';
                $message1 = 'S';
                  //$message1 = 'S';
                if ($variation1 >= 1 && $variation1 <= 2) {
                    $bg_color = 'yellow';
                    $font_color = 'black';
                } elseif ($variation1 > 2) {
                    $bg_color = 'red';
                } 

                $font_color2 = 'white';
                $bg_color2 = 'green';
                $message2 = 'A';
                if ($variation2 >= 1 && $variation2 <= 2) {
                    $bg_color2 = 'yellow';
                    $font_color2 = 'black';
                } elseif ( $variation2 > 2) {
                    $bg_color2 = 'red';
                }

                $font_color3 = 'white';
                $bg_color3 = 'green';
                $message3 = 'V';
                if ($variation3 >= 1 && $variation3 <= 2) {
                    $bg_color3 = 'yellow';
                    $font_color3 = 'black';
                } elseif ( $variation3 > 2) {
                    $bg_color3 = 'red';
                }

                $font_color4 = 'white';
                $bg_color4 = 'green';
                $message4 = 'E';
                if ($variation4 >= 1 && $variation4 <= 2) {
                    $bg_color4 = 'yellow';
                    $font_color4 = 'black';
                } elseif ( $variation4 > 2) {
                    $bg_color4 = 'red';
                }

                
                $ids = $row['ids'];
            ?>  
        
            <tr>
               
                    <td><a href="form-mental-health31.php?an=<?=$an?>&id=<?=$ids?>&loginname=<?=$login?>" target="_blank">แบบประเมินอาการทางจิต(Save) (วันที่ <?=date("d/m/Y", strtotime($create_datetimeAddmissionNurse))?>)</a></td>
                    
                    <td><div class='badge  mt-1 font-weight-bold' style="color: <?= htmlspecialchars($font_color) ?>;  font-size:100%; background-color: <?= htmlspecialchars($bg_color) ?>;">
  
    <?= htmlspecialchars($message1) ?>

</div><div class='badge  mt-1 font-weight-bold' style="color: <?= htmlspecialchars($font_color2) ?>;  font-size:100%; background-color: <?= htmlspecialchars($bg_color2) ?>;">
  
  <?= htmlspecialchars($message2) ?>

</div><div class='badge  mt-1 font-weight-bold' style="color: <?= htmlspecialchars($font_color3) ?>;  font-size:100%; background-color: <?= htmlspecialchars($bg_color3) ?>;">
  
  <?= htmlspecialchars($message3) ?>

</div><div class='badge  mt-1 font-weight-bold' style="color: <?= htmlspecialchars($font_color4) ?>;  font-size:100%; background-color: <?= htmlspecialchars($bg_color4) ?>;">
  
  <?= htmlspecialchars($message4) ?>

</div></td>
                    <td><?=date("d/m/Y H:i:s", strtotime($create_datetimeAddmissionNurse))?></td>
                    <td><?=date("d/m/Y H:i:s", strtotime($update_datetimeAddmissionNurse))?></td>
                </tr>
            <?php }
        }


?>