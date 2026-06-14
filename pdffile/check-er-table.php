<?php
/**
 * ไฟล์ชั่วคราวสำหรับตรวจสอบโครงสร้างตาราง opd_er_nurse_index_action
 * ลบทิ้งหลังใช้งานเสร็จ
 */
require_once '../include/DbUtils.php';
require_once '../include/DbConstant.php';

$conn = DbUtils::get_hosxp_connection();

$db = DbConstant::KPHIS_DBNAME;

echo "<h2>ตรวจสอบตาราง opd_er_nurse_index_action</h2>";

// 1. ตรวจสอบว่าตารางมีอยู่จริง
echo "<h3>1. ตาราง kphis ที่มีคำว่า 'opd_er_nurse'</h3><pre>";
$stmt = $conn->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$db' AND TABLE_NAME LIKE '%opd_er_nurse%' ORDER BY TABLE_NAME");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
if ($tables) {
    foreach ($tables as $t) echo "  - $t\n";
} else {
    echo "  *** ไม่พบตารางที่มีชื่อขึ้นต้นด้วย opd_er_nurse ***\n";
}
echo "</pre>";

// 2. โครงสร้างตาราง opd_er_nurse_index_action
echo "<h3>2. DESCRIBE opd_er_nurse_index_action</h3><pre>";
try {
    $stmt2 = $conn->query("DESCRIBE `$db`.`opd_er_nurse_index_action`");
    $cols = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $col) {
        printf("  %-35s %-20s %-5s %-10s %s\n",
            $col['Field'], $col['Type'], $col['Null'], $col['Key'], $col['Default'] ?? '');
    }
} catch (Exception $e) {
    echo "  *** ไม่พบตาราง: " . $e->getMessage() . " ***\n";
}
echo "</pre>";

// 3. โครงสร้างตาราง opd_er_nurse_index_plan
echo "<h3>3. DESCRIBE opd_er_nurse_index_plan</h3><pre>";
try {
    $stmt3 = $conn->query("DESCRIBE `$db`.`opd_er_nurse_index_plan`");
    $cols3 = $stmt3->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols3 as $col) {
        printf("  %-35s %-20s %-5s %-10s %s\n",
            $col['Field'], $col['Type'], $col['Null'], $col['Key'], $col['Default'] ?? '');
    }
} catch (Exception $e) {
    echo "  *** ไม่พบตาราง: " . $e->getMessage() . " ***\n";
}
echo "</pre>";

// 4. โครงสร้างตาราง opd_er_order_item
echo "<h3>4. DESCRIBE opd_er_order_item</h3><pre>";
try {
    $stmt4 = $conn->query("DESCRIBE `$db`.`opd_er_order_item`");
    $cols4 = $stmt4->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols4 as $col) {
        printf("  %-35s %-20s %-5s %-10s %s\n",
            $col['Field'], $col['Type'], $col['Null'], $col['Key'], $col['Default'] ?? '');
    }
} catch (Exception $e) {
    echo "  *** ไม่พบตาราง: " . $e->getMessage() . " ***\n";
}
echo "</pre>";

// 5. ตัวอย่างข้อมูล 5 แถวล่าสุด
echo "<h3>5. ตัวอย่างข้อมูลใน opd_er_nurse_index_action (5 แถวล่าสุด)</h3><pre>";
try {
    $stmt5 = $conn->query("SELECT * FROM `$db`.`opd_er_nurse_index_action` ORDER BY 1 DESC LIMIT 5");
    $rows = $stmt5->fetchAll(PDO::FETCH_ASSOC);
    if ($rows) {
        // Header
        echo "  " . implode(" | ", array_keys($rows[0])) . "\n";
        echo "  " . str_repeat("-", 100) . "\n";
        foreach ($rows as $r) {
            echo "  " . implode(" | ", array_map(fn($v) => substr((string)$v, 0, 20), $r)) . "\n";
        }
    } else {
        echo "  *** ไม่มีข้อมูลในตาราง ***\n";
    }
} catch (Exception $e) {
    echo "  *** Error: " . $e->getMessage() . " ***\n";
}
echo "</pre>";

echo "<hr><small style='color:red'>⚠️ ลบไฟล์นี้ทิ้งหลังใช้งานเสร็จ: check-er-table.php</small>";
