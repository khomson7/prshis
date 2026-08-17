<?php
require_once '../include/DbUtils.php';
try {
    $conn = DbUtils::get_hosxp_connection();
    
    // Check if column exists first
    $stmt = $conn->query("SHOW COLUMNS FROM prs_caprini_score LIKE 'assessment_time'");
    $exists = $stmt->fetch();
    
    if (!$exists) {
        $sql = "ALTER TABLE prs_caprini_score ADD COLUMN assessment_time TIME AFTER assessment_date";
        $conn->exec($sql);
        echo "Column 'assessment_time' created successfully.";
    } else {
        echo "Column 'assessment_time' already exists.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
