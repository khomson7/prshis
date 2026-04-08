<?php
require_once 'include/DbUtils.php';
require_once 'include/DbConstant.php';

$conn = DbUtils::get_hosxp_connection();
try {
    $stmt = $conn->query("SHOW TABLES FROM " . DbConstant::KPHIS_DBNAME . " LIKE 'ipd_progress_note%'");
    echo "Matching tables in kphis:\n";
    while($row = $stmt->fetch(PDO::FETCH_NUM)) {
        echo $row[0] . "\n";
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
