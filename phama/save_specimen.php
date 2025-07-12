<?php
require_once '../include/Session.php';
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';

$conn = DbUtils::get_hosxp_connection();

if (isset($_POST['specimen']) && trim($_POST['specimen']) != "") {
    $specimen = trim($_POST['specimen']);

    try {
        // ตรวจสอบว่ามีอยู่แล้วหรือยัง
        $stmt = $conn->prepare("SELECT COUNT(*) FROM ".DbConstant::KPHIS_DBNAME.".prs_due_specimen WHERE sp_value = :specimen");
        $stmt->bindParam(':specimen', $specimen);
        $stmt->execute();

        if ($stmt->fetchColumn() == 0) {
            // ยังไม่มี → ให้เพิ่มใหม่
            $stmt = $conn->prepare("INSERT INTO ".DbConstant::KPHIS_DBNAME.".prs_due_specimen (sp_value) VALUES (:specimen)");
            $stmt->bindParam(':specimen', $specimen);
            $stmt->execute();
        }

        echo json_encode(['status' => 'success', 'specimen' => $specimen]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>
