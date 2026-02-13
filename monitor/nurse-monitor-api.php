<?php
/**
 * API: nurse-monitor-api.php
 * ดึงข้อมูล plan vs action ของผู้ป่วยใน ward สำหรับหน้าจอ monitor
 * 
 * Parameters:
 *   ward  = รหัส ward
 *   date  = วันที่ (default = วันนี้) format: YYYY-MM-DD
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once '../include/DbUtils.php';

date_default_timezone_set('asia/bangkok');

$ward = empty($_REQUEST['ward']) ? null : $_REQUEST['ward'];
$date = empty($_REQUEST['date']) ? date('Y-m-d') : $_REQUEST['date'];

if (!$ward) {
    echo json_encode(['error' => 'ward parameter is required'], JSON_UNESCAPED_UNICODE);
    exit;
}

$conn = DbUtils::get_hosxp_connection();
$now = date('H:i:s');

// =====================================================
// 1. ดึงรายชื่อผู้ป่วยใน ward (ยังไม่ discharge)
// =====================================================
$sqlPatients = "SELECT i.an, i.hn, i.ward,id.bedno as bedname,
CONCAT(p.pname, p.fname, ' ', p.lname) AS patient_name
                FROM " . DbConstant::HOSXP_DBNAME . ".ipt i
                INNER JOIN " . DbConstant::HOSXP_DBNAME . ".iptadm id on id.an = i.an
                LEFT JOIN " . DbConstant::HOSXP_DBNAME . ".patient p ON p.hn = i.hn
                WHERE i.ward = :ward AND i.dchdate IS NULL
                ORDER BY CAST(id.bedno AS UNSIGNED), id.bedno";

$stmtP = $conn->prepare($sqlPatients);
$stmtP->bindParam(':ward', $ward);
$stmtP->execute();
$patients = $stmtP->fetchAll(PDO::FETCH_ASSOC);

// =====================================================
// 2. ดึงข้อมูล plan vs action ของแต่ละ AN ในวันที่กำหนด
// =====================================================
$sqlPlan = "SELECT 
                ap.plan_id,
                ap.order_item_id,
                ap.plan_date,
                ap.plan_time,
                io.order_item_detail,
                di.sticker_short_name,
                act.action_date,
                act.action_time,
                d.name AS action_person_1_name,
                d2.name AS action_person_2_name
            FROM " . DbConstant::KPHIS_DBNAME . ".ipd_nurse_index_plan ap
            INNER JOIN " . DbConstant::KPHIS_DBNAME . ".ipd_order_item io ON io.order_item_id = ap.order_item_id
            LEFT OUTER JOIN " . DbConstant::HOSXP_DBNAME . ".drugitems di ON di.icode = io.icode
            LEFT OUTER JOIN " . DbConstant::KPHIS_DBNAME . ".ipd_nurse_index_action act ON act.plan_id = ap.plan_id
            LEFT OUTER JOIN " . DbConstant::HOSXP_DBNAME . ".doctor d ON d.code = act.action_person_1
            LEFT OUTER JOIN " . DbConstant::HOSXP_DBNAME . ".doctor d2 ON d2.code = act.action_person_2
            WHERE ap.an = :an
              AND ap.plan_date = :plan_date
              AND io.icode IS NOT NULL
            ORDER BY ap.plan_time ASC";

$stmtPlan = $conn->prepare($sqlPlan);

$result = [];

foreach ($patients as $pt) {
    $an = $pt['an'];

    $stmtPlan->bindParam(':an', $an);
    $stmtPlan->bindParam(':plan_date', $date);
    $stmtPlan->execute();
    $plans = $stmtPlan->fetchAll(PDO::FETCH_ASSOC);

    $totalPlan = 0;
    $totalDone = 0;
    $totalPending = 0;
    $totalOverdue = 0;
    $items = [];

    foreach ($plans as $plan) {
        $totalPlan++;
        $planTime = $plan['plan_time'];
        $isDone = !empty($plan['action_time']);
        $isOverdue = false;

        if ($isDone) {
            $totalDone++;
            $status = 'done';
        } else {
            // เปรียบเทียบ plan_time กับเวลาปัจจุบัน
            if ($plan['plan_date'] == date('Y-m-d') && $planTime <= $now) {
                $totalOverdue++;
                $status = 'overdue';
            } else {
                $totalPending++;
                $status = 'pending';
            }
        }

        $drugDisplay = !empty($plan['sticker_short_name']) ? $plan['sticker_short_name'] : $plan['order_item_detail'];
        $detailDisplay = !empty($plan['sticker_short_name']) ? $plan['order_item_detail'] : '';

        $items[] = [
            'plan_id' => $plan['plan_id'],
            'drug_name' => $drugDisplay,
            'drug_detail' => $detailDisplay,
            'plan_time' => substr($planTime, 0, 5),
            'action_time' => $isDone ? substr($plan['action_time'], 0, 5) : null,
            'action_person' => trim(($plan['action_person_1_name'] ?? '') . ' ' . ($plan['action_person_2_name'] ?? '')),
            'status' => $status
        ];
    }

    $result[] = [
        'an' => $an,
        'hn' => $pt['hn'],
        'bedname' => $pt['bedname'] ?? '-',
        'patient_name' => $pt['patient_name'],
        'summary' => [
            'total' => $totalPlan,
            'done' => $totalDone,
            'pending' => $totalPending,
            'overdue' => $totalOverdue,
        ],
        'items' => $items
    ];
}

// =====================================================
// 3. ดึงชื่อ ward
// =====================================================
$sqlWard = "SELECT name FROM " . DbConstant::HOSXP_DBNAME . ".ward WHERE ward = :ward LIMIT 1";
$stmtW = $conn->prepare($sqlWard);
$stmtW->bindParam(':ward', $ward);
$stmtW->execute();
$wardName = $stmtW->fetchColumn() ?: $ward;

echo json_encode([
    'ward' => $ward,
    'ward_name' => $wardName,
    'date' => $date,
    'server_time' => date('H:i:s'),
    'beds' => $result
], JSON_UNESCAPED_UNICODE);
