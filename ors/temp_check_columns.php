<?php
require_once 'd:\prshis\php\src\include\DbUtils.php';
try {
    $conn = DbUtils::get_hosxp_connection();
    $stmt = $conn->query("SHOW COLUMNS FROM " . DbConstant::KPHIS_DBNAME . ".prs_or_complication");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($columns as $col) {
        echo $col['Field'] . "\n";
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
