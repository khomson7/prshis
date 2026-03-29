<?php
require_once '../include/Session.php';
require_once '../include/DbUtils.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = DbUtils::get_hosxp_connection();
    try {
        $conn->beginTransaction();

        $an = $_POST['an'];
        
        // Check if record already exists for this AN
        $sql_check = "SELECT id FROM prs_audit_ipd WHERE an = :an LIMIT 1";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->execute(['an' => $an]);
        $existing_id = $stmt_check->fetchColumn();

        if ($existing_id) {
            // If exists, use the update logic instead
            $audit_id = $existing_id;
            $sql = "UPDATE prs_audit_ipd SET full_score = :full_score, sum_score = :sum_score, 
                    overall_finding_1 = :of1, overall_finding_2 = :of2, 
                    overall_finding_3 = :of3, overall_finding_4 = :of4, 
                    overall_finding_5 = :of5, overall_finding_text = :of_text, 
                    audit_date = :audit_date, updated_by = :updated_by 
                    WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'full_score' => $_POST['full_score'],
                'sum_score' => $_POST['sum_score'],
                'of1' => isset($_POST['overall_finding_1']) ? 1 : 0,
                'of2' => isset($_POST['overall_finding_2']) ? 1 : 0,
                'of3' => isset($_POST['overall_finding_3']) ? 1 : 0,
                'of4' => isset($_POST['overall_finding_4']) ? 1 : 0,
                'of5' => isset($_POST['overall_finding_5']) ? 1 : 0,
                'of_text' => $_POST['overall_finding_text'],
                'audit_date' => $_POST['audit_date'],
                'updated_by' => $_SESSION['loginname'],
                'id' => $audit_id
            ]);
            
            // Delete old items
            $sql_del = "DELETE FROM prs_audit_ipd_item WHERE audit_id = :audit_id";
            $stmt_del = $conn->prepare($sql_del);
            $stmt_del->execute(['audit_id' => $audit_id]);
        } else {
            $full_score = $_POST['full_score'];
            $sum_score = $_POST['sum_score'];
            $overall_finding_1 = isset($_POST['overall_finding_1']) ? 1 : 0;
            $overall_finding_2 = isset($_POST['overall_finding_2']) ? 1 : 0;
            $overall_finding_3 = isset($_POST['overall_finding_3']) ? 1 : 0;
            $overall_finding_4 = isset($_POST['overall_finding_4']) ? 1 : 0;
            $overall_finding_5 = isset($_POST['overall_finding_5']) ? 1 : 0;
            $overall_finding_text = $_POST['overall_finding_text'];
            $audit_date = $_POST['audit_date'];
            $loginname = $_SESSION['loginname'];

            $sql = "INSERT INTO prs_audit_ipd (an, full_score, sum_score, overall_finding_1, overall_finding_2, 
                    overall_finding_3, overall_finding_4, overall_finding_5, overall_finding_text, 
                    audit_by, audit_date, created_by) 
                    VALUES (:an, :full_score, :sum_score, :of1, :of2, :of3, :of4, :of5, :of_text, 
                    :audit_by, :audit_date, :created_by)";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'an' => $an,
                'full_score' => $full_score,
                'sum_score' => $sum_score,
                'of1' => $overall_finding_1,
                'of2' => $overall_finding_2,
                'of3' => $overall_finding_3,
                'of4' => $overall_finding_4,
                'of5' => $overall_finding_5,
                'of_text' => $overall_finding_text,
                'audit_by' => $loginname,
                'audit_date' => $audit_date,
                'created_by' => $loginname
            ]);

            $audit_id = $conn->lastInsertId();
        }

        if (isset($_POST['item']) && is_array($_POST['item'])) {
            foreach ($_POST['item'] as $idx => $item) {
                $sql_item = "INSERT INTO prs_audit_ipd_item (audit_id, content_index, is_na, is_missing, is_no, 
                            c1, c2, c3, c4, c5, c6, c7, c8, c9, deduct_score, total_score, remark) 
                            VALUES (:audit_id, :idx, :na, :missing, :no_val, :c1, :c2, :c3, :c4, :c5, :c6, :c7, :c8, :c9, :deduct, :total, :remark)";
                
                $stmt_item = $conn->prepare($sql_item);
                $stmt_item->execute([
                    'audit_id' => $audit_id,
                    'idx' => $idx,
                    'na' => isset($item['na']) ? 1 : 0,
                    'missing' => isset($item['missing']) ? 1 : 0,
                    'no_val' => isset($item['no_val']) ? 1 : 0,
                    'c1' => isset($item['c1']) ? 1 : 0,
                    'c2' => isset($item['c2']) ? 1 : 0,
                    'c3' => isset($item['c3']) ? 1 : 0,
                    'c4' => isset($item['c4']) ? 1 : 0,
                    'c5' => isset($item['c5']) ? 1 : 0,
                    'c6' => isset($item['c6']) ? 1 : 0,
                    'c7' => isset($item['c7']) ? 1 : 0,
                    'c8' => isset($item['c8']) ? 1 : 0,
                    'c9' => isset($item['c9']) ? 1 : 0,
                    'deduct' => $item['deduct_score'],
                    'total' => $item['total_score'],
                    'remark' => $item['remark']
                ]);
            }
        }

        $conn->commit();
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>
