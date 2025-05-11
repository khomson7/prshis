<?php
require_once '../include/Session.php';
//SessionManager::checkLoginSessionAndShowMessage(); //เช็ค session
// SessionManager::checkPermissionAndShowMessage('KPHIS_ACCESS_IPD_NURSE_PROGRAM');

require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';

$conn = DbUtils::get_hosxp_connection(); //เชื่อมต่อฐานข้อมูล

$search = empty($_REQUEST['search']) ? "" : $_REQUEST['search'];
$page = empty($_REQUEST['page']) ? null : $_REQUEST['page'];

$page_size = 100;

try {
    $sql = "SELECT
                d.icode AS `id`,
                d.name AS `text`,
                concat(d.icode,' ',d.name) AS `addrname`
            FROM
                ".DbConstant::HOSXP_DBNAME.".drugitems d
                inner join ".DbConstant::KPHIS_DBNAME.".prs_due_drug d2 on d2.icode = d.icode
            WHERE d2.active_ in('Y') and d.name LIKE :search
            ORDER BY d.name
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
        $rows[] = $row;
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