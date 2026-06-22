<?php
require_once '../include/Session.php';
require_once '../include/session-sso.php';
$loginname = isset($_SESSION['loginname']) ? $_SESSION['loginname'] : null;

if (!$loginname) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../include/DbUtils.php';
require_once '../include/DbConstant.php';
$conn = DbUtils::get_hosxp_connection();

// Create table if not exists
$sql_create = "
CREATE TABLE IF NOT EXISTS `prs_operative_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operation_name` varchar(255) NOT NULL,
  `image_data` longblob,
  `image_type` varchar(50) DEFAULT NULL,
  `clinical_diagnosis` text DEFAULT NULL,
  `post_op_diagnosis` text DEFAULT NULL,
  `anesthetic_technique` varchar(255) DEFAULT NULL,
  `op_position` varchar(255) DEFAULT NULL,
  `incision` varchar(255) DEFAULT NULL,
  `finding` text DEFAULT NULL,
  `procedure_detail` text DEFAULT NULL,
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

// Auto alter table for new fields
$new_cols = [
    'clinical_diagnosis' => 'text',
    'post_op_diagnosis' => 'text',
    'anesthetic_technique' => 'varchar(255)',
    'op_position' => 'varchar(255)',
    'incision' => 'varchar(255)',
    'finding' => 'text',
    'procedure_detail' => 'text'
];
foreach ($new_cols as $col => $type) {
    try { $conn->exec("ALTER TABLE `prs_operative_template` ADD COLUMN `$col` $type"); } catch (Exception $e) {}
}

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

if ($action === 'list') {
    $stmt = $conn->prepare("SELECT id, operation_name, create_datetime FROM prs_operative_template WHERE created_by = :created_by ORDER BY operation_name ASC");
    $stmt->execute(['created_by' => $loginname]);
    $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $templates]);
    exit;
}

if ($action === 'users') {
    try {
        $stmt = $conn->query("SELECT loginname, name FROM " . DbConstant::HOSXP_DBNAME . ".opduser WHERE account_disable IS NULL OR account_disable <> 'Y' ORDER BY name ASC");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $users]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'copy') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $target_user = isset($_POST['target_user']) ? trim($_POST['target_user']) : '';
    
    if (empty($target_user)) {
        echo json_encode(['success' => false, 'message' => 'กรุณาระบุผู้รับ']);
        exit;
    }
    
    try {
        $stmt = $conn->prepare("SELECT * FROM prs_operative_template WHERE id = :id AND created_by = :user");
        $stmt->execute(['id' => $id, 'user' => $loginname]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $stmt_chk = $conn->prepare("SELECT id FROM prs_operative_template WHERE operation_name = :op AND created_by = :target");
            $stmt_chk->execute(['op' => $row['operation_name'], 'target' => $target_user]);
            if ($stmt_chk->fetch()) {
                echo json_encode(['success' => false, 'message' => 'ผู้ใช้ปลายทางมี Template ชื่อนี้อยู่แล้ว']);
                exit;
            }
            
            $bin = $row['image_data'];
            if (is_resource($bin)) $bin = stream_get_contents($bin);
            
            $sql = "INSERT INTO prs_operative_template (operation_name, image_data, image_type, clinical_diagnosis, post_op_diagnosis, anesthetic_technique, op_position, incision, finding, procedure_detail, created_by, create_datetime) 
                    VALUES (:op, :img, :mime, :cd, :pod, :ane, :pos, :inc, :fin, :proc, :target, NOW())";
            $stmt_ins = $conn->prepare($sql);
            $stmt_ins->execute([
                'op' => $row['operation_name'], 'img' => $bin, 'mime' => $row['image_type'],
                'cd' => $row['clinical_diagnosis'], 'pod' => $row['post_op_diagnosis'], 'ane' => $row['anesthetic_technique'],
                'pos' => $row['op_position'], 'inc' => $row['incision'], 'fin' => $row['finding'], 'proc' => $row['procedure_detail'],
                'target' => $target_user
            ]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูล หรือคุณไม่มีสิทธิ์']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
    }
    exit;
}

if ($action === 'get') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $stmt = $conn->prepare("SELECT * FROM prs_operative_template WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $b64 = '';
        if (!empty($row['image_data'])) {
            $bin = $row['image_data'];
            if (is_resource($bin)) $bin = stream_get_contents($bin);
            $mime = $row['image_type'] ?: 'image/png';
            $b64 = 'data:' . $mime . ';base64,' . base64_encode($bin);
        }
        echo json_encode([
            'success' => true, 
            'b64' => $b64, 
            'operation_name' => $row['operation_name'],
            'clinical_diagnosis' => $row['clinical_diagnosis'],
            'post_op_diagnosis' => $row['post_op_diagnosis'],
            'anesthetic_technique' => $row['anesthetic_technique'],
            'op_position' => $row['op_position'],
            'incision' => $row['incision'],
            'finding' => $row['finding'],
            'procedure_detail' => $row['procedure_detail']
        ]);
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

    $imgData = null;
    $imgType = null;
    if (isset($_FILES['template_image']) && $_FILES['template_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['template_image'];
        if ($file['size'] > 5 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'ขนาดไฟล์ต้องไม่เกิน 5MB']);
            exit;
        }
        $imgData = file_get_contents($file['tmp_name']);
        $imgType = $file['type'];
    }

    $clinical_diagnosis = $_POST['clinical_diagnosis'] ?? null;
    $post_op_diagnosis = $_POST['post_op_diagnosis'] ?? null;
    $anesthetic_technique = $_POST['anesthetic_technique'] ?? null;
    $op_position = $_POST['op_position'] ?? null;
    $incision = $_POST['incision'] ?? null;
    $finding = $_POST['finding'] ?? null;
    $procedure_detail = $_POST['procedure_detail'] ?? null;
    $tpl_id = isset($_POST['tpl_id']) ? (int)$_POST['tpl_id'] : 0;

    $existing = null;
    if ($tpl_id > 0) {
        $stmt_chk = $conn->prepare("SELECT id FROM prs_operative_template WHERE id = :id AND created_by = :user");
        $stmt_chk->execute(['id' => $tpl_id, 'user' => $loginname]);
        $existing = $stmt_chk->fetch(PDO::FETCH_ASSOC);
    } else {
        // Check if template already exists for this doctor and operation
        $stmt_chk = $conn->prepare("SELECT id FROM prs_operative_template WHERE operation_name = :op AND created_by = :user");
        $stmt_chk->execute(['op' => $operation_name, 'user' => $loginname]);
        $existing = $stmt_chk->fetch(PDO::FETCH_ASSOC);
    }

    if ($existing) {
        // Update
        $sql = "UPDATE prs_operative_template SET 
                operation_name = :op, clinical_diagnosis = :cd, post_op_diagnosis = :pod, anesthetic_technique = :ane,
                op_position = :pos, incision = :inc, finding = :fin, procedure_detail = :proc,
                create_datetime = NOW() ";
        $params = [
            'op' => $operation_name,
            'cd' => $clinical_diagnosis, 'pod' => $post_op_diagnosis, 'ane' => $anesthetic_technique,
            'pos' => $op_position, 'inc' => $incision, 'fin' => $finding, 'proc' => $procedure_detail,
            'id' => $existing['id']
        ];
        
        if ($imgData !== null) {
            $sql .= ", image_data = :img, image_type = :mime ";
            $params['img'] = $imgData;
            $params['mime'] = $imgType;
        }
        
        $sql .= " WHERE id = :id";
        $stmt_upd = $conn->prepare($sql);
        $stmt_upd->execute($params);
    } else {
        // Insert
        $stmt_ins = $conn->prepare("INSERT INTO prs_operative_template 
            (operation_name, image_data, image_type, clinical_diagnosis, post_op_diagnosis, anesthetic_technique, op_position, incision, finding, procedure_detail, created_by, create_datetime) 
            VALUES (:op, :img, :mime, :cd, :pod, :ane, :pos, :inc, :fin, :proc, :user, NOW())");
        $stmt_ins->execute([
            'op' => $operation_name, 'img' => $imgData, 'mime' => $imgType,
            'cd' => $clinical_diagnosis, 'pod' => $post_op_diagnosis, 'ane' => $anesthetic_technique,
            'pos' => $op_position, 'inc' => $incision, 'fin' => $finding, 'proc' => $procedure_detail,
            'user' => $loginname
        ]);
    }

    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);

