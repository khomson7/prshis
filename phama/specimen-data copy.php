<?php
require_once '../include/Session.php';
//SessionManager::checkLoginSessionAndShowMessage(); //เช็ค session
// SessionManager::checkPermissionAndShowMessage('KPHIS_ACCESS_IPD_NURSE_PROGRAM');

require_once './project/function/DbUtils.php';
require_once './project/function/KphisQueryUtils.php';

$conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล

$search = empty($_REQUEST['search']) ? "" : $_REQUEST['search'];
$page = empty($_REQUEST['page']) ? null : $_REQUEST['page'];

$page_size = 100;

try {
    $sql = "SELECT
                s.sp_value AS `addrname`
            FROM
                ".DbConstant::KPHIS_DBNAME.".prs_due_specimen s
            WHERE s.sp_value LIKE :search
            ORDER BY s.sp_value
            limit :limit offset :offset";
    $stmt = $conn->prepare($sql);

    $search = '%'.$search.'%';
    $limit = $page_size;
    $offset = ($page-1)*$page_size;

    $stmt->bindParam(':search', $search, PDO::PARAM_STR);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();
	$rows = array();
	while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

       // $rows[] = $row;

       $rows[] = [
        "id" => $row["addrname"],
        "text" => $row["addrname"]
    ];
	}

    $sql = "SELECT FOUND_ROWS() fr";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $foundRows = $row['fr'];
	}

    $data['results'] = $rows;
    $data['pagination']['more'] = ((($page-1) * $page_size) < $foundRows);

	echo json_encode($data, JSON_UNESCAPED_UNICODE );
} catch (PDOException  $e) {
    ErrorUtils::errorMessage($e,ErrorUtils::ERROR_MESSAGE_TYPE_HTML);
    http_response_code(500);
}