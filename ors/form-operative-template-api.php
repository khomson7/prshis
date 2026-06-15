<?php
require_once '../include/Session.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$loginname = isset($_SESSION['loginname']) ? $_SESSION['loginname'] : null;

if (!$loginname) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../include/DbUtils.php';
$conn = DbUtils::get_hosxp_connection();

// Create table if not exists
$sql_create = "
CREATE TABLE IF NOT EXISTS `prs_operative_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operation_name` varchar(255) NOT NULL,
  `image_data` longblob,
  `image_type` varchar(50) DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `create_datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";
try {
    $conn->exec($sql_create);
} catch (Exception $e) {
    // Ignore error if table creation fails due to permissions, assuming it exists
}

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

if ($action === 'list') {
    $stmt = $conn->prepare("SELECT id, operation_name, create_datetime FROM prs_operative_template WHERE created_by = :created_by ORDER BY operation_name ASC");
    $stmt->execute(['created_by' => $loginname]);
    $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $templates]);
    exit;
}

if ($action === 'get') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $stmt = $conn->prepare("SELECT image_data, image_type, operation_name FROM prs_operative_template WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $bin = $row['image_data'];
        if (is_resource($bin)) $bin = stream_get_contents($bin);
        $mime = $row['image_type'] ?: 'image/png';
        $b64 = 'data:' . $mime . ';base64,' . base64_encode($bin);
        echo json_encode(['success' => true, 'b64' => $b64, 'operation_name' => $row['operation_name']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Not found']);
    }
    exit;
}

if ($action === 'delete') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $stmt = $conn->prepare("DELETE FROM prs_operative_template WHERE id = :id AND created_by = :created_by");
    $stmt->execute(['id' => $id, 'created_by' => $loginname]);
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'save') {
    $operation_name = isset($_POST['operation_name']) ? trim($_POST['operation_name']) : '';
    
    if (empty($operation_name)) {
        echo json_encode(['success' => false, 'message' => 'กรุณาระบุชื่อรายการผ่าตัด']);
        exit;
    }

    if (!isset($_FILES['template_image']) || $_FILES['template_image']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'กรุณาอัพโหลดรูปภาพ']);
        exit;
    }

    $file = $_FILES['template_image'];
    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'ขนาดไฟล์ต้องไม่เกิน 5MB']);
        exit;
    }

    $imgData = file_get_contents($file['tmp_name']);
    $imgType = $file['type'];

    // Check if template already exists for this doctor and operation
    $stmt_chk = $conn->prepare("SELECT id FROM prs_operative_template WHERE operation_name = :op AND created_by = :user");
    $stmt_chk->execute(['op' => $operation_name, 'user' => $loginname]);
    $existing = $stmt_chk->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // Update
        $stmt_upd = $conn->prepare("UPDATE prs_operative_template SET image_data = :img, image_type = :mime, create_datetime = NOW() WHERE id = :id");
        $stmt_upd->execute(['img' => $imgData, 'mime' => $imgType, 'id' => $existing['id']]);
    } else {
        // Insert
        $stmt_ins = $conn->prepare("INSERT INTO prs_operative_template (operation_name, image_data, image_type, created_by, create_datetime) VALUES (:op, :img, :mime, :user, NOW())");
        $stmt_ins->execute(['op' => $operation_name, 'img' => $imgData, 'mime' => $imgType, 'user' => $loginname]);
    }

    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
