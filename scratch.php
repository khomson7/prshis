<?php
require_once __DIR__ . '/include/DbUtils.php';
try {
    $conn = new PDO("mysql:host=127.0.0.1;", "sa", "sa");
    $stmt = $conn->query("SHOW DATABASES");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Database'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
