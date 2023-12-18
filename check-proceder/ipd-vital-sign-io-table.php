<?php   require_once './project/function/DbUtils.php';
        require_once './project/function/KphisQueryUtils.php';
        require_once './project/function/SessionManager.php';

        $conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล
        SessionManager::checkLoginSessionAndShowMessage(); //เช็ค session
        if(!(
            // && SessionManager::checkPermission('IPD_NURSE_MAIN_PROGRAM','ACCESS')
            // && SessionManager::checkPermission('IO','ADD')
            // && SessionManager::checkPermission('IO','EDIT')
            SessionManager::checkPermission('IO','VIEW')
            // && SessionManager::checkPermission('IO','REMOVE')
            )){
            return;
        }
        $an = $_REQUEST['an'];//รับค่า an
        $select_search_io_date = $_REQUEST['select_search_io_date'];//รับค่า วันที่
        $hn = KphisQueryUtils::getHnByAn($an);// function ที่ส่งค่า an เพื่อไปค้นหา hn แล้วส่งค่า hn กลับมา
        $session_loginname = $_SESSION['loginname'];//session ผู้ที่ login เข้าใช้งาน

        $query_parameters = ['an'=>$an,
                             'select_search_io_date' => $select_search_io_date
                            ];
        $sql_shift = "SELECT io.io_date,
                        SUM(io.io_parenteral_absorb) AS sum8_io_parenteral_absorb,
                        SUM(io.io_oral_absorb) AS sum8_io_oral_absorb,
                        SUM(io.io_output_amount) AS sum8_io_output_amount,
                        CASE
                        WHEN io.io_time BETWEEN '00:00:00.000' AND '07:59:59' THEN 'ดึก'
                        WHEN io.io_time BETWEEN '08:00:00.000' AND '15:59:59' THEN 'เช้า'
                        WHEN io.io_time BETWEEN '16:00:00.000' AND '23:59:59' THEN 'บ่าย'
                        ELSE null
                        END AS shift
                        FROM ".KphisConstant::HOSXP_CONNECTION_KPHIS_DBNAME.".ipd_io io
                        WHERE io.an = :an AND io.io_date = :select_search_io_date
                        GROUP BY io.io_date,shift
                        ORDER BY io.io_date,io.io_time ASC
                        ";
        $stmt_shift = $conn->prepare($sql_shift);
        $stmt_shift->execute($query_parameters);
        $sum24_io_parenteral_absorb = 0;
        $sum24_io_oral_absorb = 0;
        $sum24_io_output_amount = 0;
        $sum24_parenteral_oral_absorb = 0;
        while ($row_shift = $stmt_shift->fetch()){


            $sum8_io_parenteral_absorb = $row_shift['sum8_io_parenteral_absorb'];
            $sum8_io_oral_absorb = $row_shift['sum8_io_oral_absorb'];
            $sum8_io_output_amount = $row_shift['sum8_io_output_amount'];

            $sql="SELECT io.*,kphuser.name as user_name,kphuser.entryposition
                    FROM ".KphisConstant::HOSXP_CONNECTION_KPHIS_DBNAME.".ipd_io io
                    LEFT JOIN ".KphisConstant::HOSXP_CONNECTION_HOSXP_DBNAME.".opduser kphuser on kphuser.loginname = io.update_user
                    WHERE io.an = :an AND io.io_date = :select_search_io_date ";
            if($row_shift['shift'] == 'ดึก'){
                $sql.= " AND io.io_time BETWEEN '00:00:00.000' AND '07:59:59' ";
            } else if($row_shift['shift'] == 'เช้า'){
                $sql.= " AND io.io_time BETWEEN '08:00:00.000' AND '15:59:59' ";
            } else if($row_shift['shift'] == 'บ่าย'){
                $sql.= " AND io.io_time BETWEEN '16:00:00.000' AND '23:59:59' ";
            }
            $sql.= " ORDER BY io.io_time ASC ";

            $stmt = $conn->prepare($sql);
            $stmt->execute($query_parameters);
            $rowCount = 0;
            while ($row = $stmt->fetch()){
                $rowCount++;
                $io_id = $row['io_id'];

                $io_date = $row['io_date'];
                $io_time = $row['io_time'];

                $io_parenteral_type = $row['io_parenteral_type'];

                $io_parenteral_name = htmlspecialchars($row['io_parenteral_name']);
                $io_parenteral_name_replace1 = str_replace("[","<span class='text-danger'>",$io_parenteral_name);
                $io_parenteral_name_replace2 = str_replace("]","</span>",$io_parenteral_name_replace1);

                $io_parenteral_amount = $row['io_parenteral_amount'];
                $io_parenteral_absorb = $row['io_parenteral_absorb'];
                $io_parenteral_carry_forward = $row['io_parenteral_carry_forward'];
                $io_parenteral_remark = $row['io_parenteral_remark'];

                $io_oral_name = $row['io_oral_name'];
                $io_oral_amount = $row['io_oral_amount'];
                $io_oral_absorb = $row['io_oral_absorb'];
                $io_oral_carry_forward = $row['io_oral_carry_forward'];
                $io_oral_remark = $row['io_oral_remark'];

                $io_output_type = $row['io_output_type'];
                $io_output_amount = $row['io_output_amount'];
                $io_output_remark = $row['io_output_remark'];

                $user_name = $row['user_name'];
                $entryposition = $row['entryposition'];
                ?>
                <tr>
                    <td><?=htmlspecialchars($rowCount)?></td>
                    <td><?=date("d/m/Y", strtotime($io_date))?></td>
                    <td><?=substr($io_time, 0, -3)?></td>
                    <td><?=htmlspecialchars($io_parenteral_type)?></td>
                    <td><?=$io_parenteral_name_replace2?></td>
                    <td><?=htmlspecialchars($io_parenteral_amount)?></td>
                    <td><?=htmlspecialchars($io_parenteral_absorb)?></td>
                    <td><?=htmlspecialchars($io_parenteral_carry_forward)?></td>
                    <td><?=htmlspecialchars($io_parenteral_remark)?></td>
                    <td style="background-color:#ECF3F3;"><?=htmlspecialchars($io_oral_name)?></td>
                    <td style="background-color:#ECF3F3;"><?=htmlspecialchars($io_oral_amount)?></td>
                    <td style="background-color:#ECF3F3;"><?=htmlspecialchars($io_oral_absorb)?></td>
                    <td style="background-color:#ECF3F3;"><?=htmlspecialchars($io_oral_carry_forward)?></td>
                    <td style="background-color:#ECF3F3;"><?=htmlspecialchars($io_oral_remark)?></td>
                    <td><?=htmlspecialchars($io_output_type)?></td>
                    <td><?=htmlspecialchars($io_output_amount)?></td>
                    <td><?=htmlspecialchars($io_output_remark)?></td>
                    <td>
                        <?php
                        if((
                            // && SessionManager::checkPermission('IPD_NURSE_MAIN_PROGRAM','ACCESS')
                            SessionManager::checkPermission('IO','ADD')
                            // && SessionManager::checkPermission('IO','EDIT')
                            // SessionManager::checkPermission('IO','VIEW')
                            // && SessionManager::checkPermission('IO','REMOVE')
                        )){?>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="edit_vital_sign_io(<?=htmlspecialchars($io_id)?>,<?=htmlspecialchars($an)?>)">แก้ไข</button><?php
                        }?>
                        <i class="fas fa-user" title="<?=htmlspecialchars($user_name)." (".htmlspecialchars($entryposition).")"?>"></i>
                    </td>
                </tr><?php
            }
            if($rowCount > 0){?>
                <tr class="table-success">
                    <td colspan="3">(เวร<?=$row_shift['shift']?>) รวม 8 ชั่วโมง</td>
                    <td style="text-align:center;" colspan="6"><?=htmlspecialchars($sum8_io_parenteral_absorb)?> c.c.</td>
                    <td style="text-align:center;" colspan="5"><?=htmlspecialchars($sum8_io_oral_absorb)?> c.c.</td>
                    <td style="text-align:center;" colspan="3"><?=htmlspecialchars($sum8_io_output_amount)?> c.c.</td>
                    <td></td>
                </tr>
                <?php
            }
        }

        $sql_sum24="SELECT
                    SUM(io.io_parenteral_absorb) AS sum24_io_parenteral_absorb,
                    SUM(io.io_oral_absorb) AS sum24_io_oral_absorb,
                    SUM(io.io_output_amount) AS sum24_io_output_amount,
                    SUM(IFNULL(io.io_parenteral_absorb,0)+IFNULL(io.io_oral_absorb,0)) AS sum24_parenteral_oral_absorb
                    FROM ".KphisConstant::HOSXP_CONNECTION_KPHIS_DBNAME.".ipd_io io
                    WHERE io.an = :an AND io.io_date = :select_search_io_date ";

        $stmt_sum24 = $conn->prepare($sql_sum24);
        $stmt_sum24->execute($query_parameters);
        while ($row_sum24 = $stmt_sum24->fetch()){
            $sum24_io_parenteral_absorb = $row_sum24['sum24_io_parenteral_absorb'];
            $sum24_io_oral_absorb = $row_sum24['sum24_io_oral_absorb'];
            $sum24_io_output_amount = $row_sum24['sum24_io_output_amount'];
            $sum24_parenteral_oral_absorb = $row_sum24['sum24_parenteral_oral_absorb'];?>

            <tr class="table-primary">
                <td colspan="3">รวม 24 ชั่วโมง</td>
                <td style="text-align:center;" class="font-weight-bold" colspan="6"><?=htmlspecialchars($sum24_io_parenteral_absorb)?> c.c.</td>
                <td style="text-align:center;" class="font-weight-bold" colspan="5"><?=htmlspecialchars($sum24_io_oral_absorb)?> c.c.</td>
                <td style="text-align:center;" class="font-weight-bold" colspan="3"><?=htmlspecialchars($sum24_io_output_amount)?> c.c.</td>
                <td></td>
            </tr>
            <tr class="table-primary">
                <td colspan="3">รวม 24 ชั่วโมง</td>
                <td style="text-align:center;" class="font-weight-bold" colspan="11"><font color="red"><?=htmlspecialchars($sum24_parenteral_oral_absorb)?> c.c.</font></td>
                <td style="text-align:center;" class="font-weight-bold" colspan="3"><font color="red"><?=htmlspecialchars($sum24_io_output_amount)?> c.c.</font></td>
                <td></td>
            </tr>
        <?php } ?>