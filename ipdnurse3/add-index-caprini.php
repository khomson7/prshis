<?php
require_once '../include/DbUtils.php';
try {
    $conn = DbUtils::get_hosxp_connection();
    $sql = "ALTER TABLE prs_caprini_score ADD INDEX idx_an (an)";
    $conn->exec($sql);
    echo "Index created successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
