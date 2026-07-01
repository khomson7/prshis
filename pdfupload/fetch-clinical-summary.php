<?php
require_once '../include/Session.php';
require_once '../include/DbUtils.php';

header('Content-Type: application/json; charset=utf-8');

$an = trim($_POST['an'] ?? '');

if (empty($an)) {
    echo json_encode(['status' => 'error', 'message' => 'ไม่พบข้อมูล AN']);
    exit;
}

// ข้อมูลสำหรับเชื่อมต่อ API ภายนอก
$url = "https://dcsum-tenant-api.sati.co.th/api/clinical-summary/get-clinical-summary";
// แทนที่ด้วยค่าจริงของ Token
$tenant_identifier = "47fc36cb-7c79-4301-b9e0-f5a8d4ead39a";
$authorization = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0eXBlIjoic2VydmljZSIsImFwcElkIjoiYTU3MjMzY2ItMGJiZi00YzI1LTk3NDktOWExNDBhNzY1NGQwIiwiaG9zcGl0YWxDb2RlIjoiMTA5MTgiLCJ0ZW5hbnRJZGVudGlmaWVySWQiOiI0N2ZjMzZjYi03Yzc5LTQzMDEtYjllMC1mNWE4ZDRlYWQzOWEiLCJpYXQiOjE3ODI4MDcwNjAsImV4cCI6MTgxNDM2NDY2MH0.R3V2MrPiiHrpEmiTl5axDD0Yv6-pteEKRgVYiVa4E4I";

// Request Payload
$payload = json_encode([
    'admissionNumber' => (string) $an
]);

// Initialize cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'tenant-identifier: ' . $tenant_identifier,
    'Authorization: ' . $authorization,
    'Content-Length: ' . strlen($payload)
]);
// ข้ามการตรวจสอบ SSL ชั่วคราวกรณีมีปัญหา SSL Certificate ใน Local (ควรนำออกใน Production)
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($response === false) {
    echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถเชื่อมต่อ API ได้: ' . $error]);
    exit;
}

$data = json_decode($response, true);

if ($httpcode >= 200 && $httpcode < 300 && isset($data['finalDiagnosis'])) {

    try {
        $conn = DbUtils::get_hosxp_connection();

        $final_diagnosis = $data['finalDiagnosis'] ?? '';
        $progression = $data['progression'] ?? '';
        $follow_up = $data['followUp'] ?? '';

        // ตรวจสอบว่ามีข้อมูลในตารางอยู่แล้วหรือไม่
        $stmt_check = $conn->prepare("SELECT COUNT(*) FROM prs_clinical_summay_sati WHERE admission_number = :an");
        $stmt_check->execute(['an' => $an]);
        $count = $stmt_check->fetchColumn();

        if ($count > 0) {
            // Update
            $stmt = $conn->prepare("UPDATE prs_clinical_summay_sati SET final_diagnosis = :final_diagnosis, progression = :progression, follow_up = :follow_up WHERE admission_number = :an");
        } else {
            // Insert
            $stmt = $conn->prepare("INSERT INTO prs_clinical_summay_sati (admission_number, final_diagnosis, progression, follow_up) VALUES (:an, :final_diagnosis, :progression, :follow_up)");
        }

        $stmt->execute([
            'an' => $an,
            'final_diagnosis' => $final_diagnosis,
            'progression' => $progression,
            'follow_up' => $follow_up
        ]);

        echo json_encode(['status' => 'success', 'message' => 'ดึงข้อมูลและบันทึกสำเร็จ']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล Database: ' . $e->getMessage()]);
    }

} else {
    $err_msg = isset($data['message']) ? $data['message'] : 'API ส่งคืนข้อผิดพลาด หรือรูปแบบข้อมูลไม่ถูกต้อง';
    echo json_encode(['status' => 'error', 'message' => $err_msg, 'httpcode' => $httpcode, 'raw_response' => $response]);
}
