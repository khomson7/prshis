<?php
require_once '../include/Session.php';
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../include/DbUtils.php';

header('Content-Type: application/json; charset=utf-8');

$loginname = isset($_SESSION['loginname']) ? $_SESSION['loginname'] : null;
if (!$loginname) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$conn = DbUtils::get_hosxp_connection();

try {
    if ($action === 'get_groups') {
        $stmt = $conn->query("SELECT * FROM prs_group_print_index ORDER BY group_print");
        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $groups]);
    } 
    elseif ($action === 'save_group') {
        $group_print = isset($_POST['group_print']) ? (int)$_POST['group_print'] : 0;
        $group_name = trim($_POST['group_name'] ?? '');
        
        if (!$group_name) {
            echo json_encode(['success' => false, 'message' => 'กรุณาระบุชื่อกลุ่ม']);
            exit;
        }

        if ($group_print > 0) {
            // Update
            $stmt = $conn->prepare("UPDATE prs_group_print_index SET group_name = :group_name WHERE group_print = :group_print");
            $stmt->execute(['group_name' => $group_name, 'group_print' => $group_print]);
        } else {
            // Insert - Auto increment group_print manually since it might not be AUTO_INCREMENT in DB schema
            $maxStmt = $conn->query("SELECT MAX(group_print) FROM prs_group_print_index");
            $newId = (int)$maxStmt->fetchColumn() + 1;
            
            $stmt = $conn->prepare("INSERT INTO prs_group_print_index (group_print, group_name) VALUES (:group_print, :group_name)");
            $stmt->execute(['group_print' => $newId, 'group_name' => $group_name]);
        }
        echo json_encode(['success' => true]);
    }
    elseif ($action === 'delete_group') {
        $group_print = isset($_POST['group_print']) ? (int)$_POST['group_print'] : 0;
        if ($group_print > 0) {
            $conn->prepare("DELETE FROM prs_group_print_item WHERE group_print = :group_print")->execute(['group_print' => $group_print]);
            $conn->prepare("DELETE FROM prs_group_print_index WHERE group_print = :group_print")->execute(['group_print' => $group_print]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        }
    }
    elseif ($action === 'get_items') {
        $group_print = isset($_REQUEST['group_print']) ? (int)$_REQUEST['group_print'] : 0;
        $stmt = $conn->prepare("SELECT * FROM prs_group_print_item WHERE group_print = :group_print ORDER BY sort_order, id");
        $stmt->execute(['group_print' => $group_print]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $items]);
    }
    elseif ($action === 'save_item') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $group_print = isset($_POST['group_print']) ? (int)$_POST['group_print'] : 0;
        $document_name = trim($_POST['document_name'] ?? '');
        $pdf_script = trim($_POST['pdf_script'] ?? '');
        $sort_order = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;

        if (!$group_print || !$document_name || !$pdf_script) {
            echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
            exit;
        }

        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE prs_group_print_item SET document_name = :document_name, pdf_script = :pdf_script, sort_order = :sort_order WHERE id = :id");
            $stmt->execute([
                'document_name' => $document_name,
                'pdf_script' => $pdf_script,
                'sort_order' => $sort_order,
                'id' => $id
            ]);
        } else {
            $stmt = $conn->prepare("INSERT INTO prs_group_print_item (group_print, document_name, pdf_script, sort_order) VALUES (:group_print, :document_name, :pdf_script, :sort_order)");
            $stmt->execute([
                'group_print' => $group_print,
                'document_name' => $document_name,
                'pdf_script' => $pdf_script,
                'sort_order' => $sort_order
            ]);
        }
        echo json_encode(['success' => true]);
    }
    elseif ($action === 'delete_item') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id > 0) {
            $conn->prepare("DELETE FROM prs_group_print_item WHERE id = :id")->execute(['id' => $id]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
