<?php
require_once '../include/Session.php';
require_once '../include/DbUtils.php';

header('Content-Type: application/json; charset=utf-8');

// ============================================================
// ฟังก์ชันอ่าน/เขียน SATI Config จากไฟล์ JSON
// ============================================================
define('SATI_CONFIG_FILE', __DIR__ . '/sati_config.json');
define('SATI_API_URL', 'https://dcsum-tenant-api.sati.co.th/api/clinical-summary/get-clinical-summary');

function sati_read_config(): array {
    if (!file_exists(SATI_CONFIG_FILE)) {
        return ['tenant_id' => '', 'bearer_token' => '', 'token_saved_at' => '', 'updated_by' => ''];
    }
    $raw = file_get_contents(SATI_CONFIG_FILE);
    return json_decode($raw, true) ?? [];
}

function sati_write_config(array $cfg): void {
    file_put_contents(SATI_CONFIG_FILE, json_encode($cfg, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

/**
 * ตรวจสอบอายุ Token จาก JWT exp claim จริง (ไม่ใช่เวลาบันทึก)
 */
function sati_token_expired(string $bearer_token): bool {
    if (empty($bearer_token)) return true;

    $parts = explode('.', $bearer_token);
    if (count($parts) !== 3) return true;

    // Decode payload (ส่วนที่ 2) — เติม Base64 padding
    $b64 = strtr($parts[1], '-_', '+/');
    $b64 = str_pad($b64, strlen($b64) + (4 - strlen($b64) % 4) % 4, '=');
    $payload = json_decode(base64_decode($b64), true);

    if (!isset($payload['exp'])) return true;

    // เผื่อ grace period 5 นาที
    return time() >= ($payload['exp'] - 300);
}

function sati_token_expire_datetime(string $bearer_token): string {
    if (empty($bearer_token)) return '';
    $parts = explode('.', $bearer_token);
    if (count($parts) !== 3) return '';
    $b64 = strtr($parts[1], '-_', '+/');
    $b64 = str_pad($b64, strlen($b64) + (4 - strlen($b64) % 4) % 4, '=');
    $payload = json_decode(base64_decode($b64), true);
    if (!isset($payload['exp'])) return '';
    return date('d/m/' . (date('Y', $payload['exp']) + 543) . ' H:i', $payload['exp']);
}

// ============================================================
// ACTION: อัพเดท Token (จากหน้า Admin)
// ============================================================
$action = trim($_POST['action'] ?? '');

if ($action === 'update_token') {
    $new_token  = trim($_POST['bearer_token'] ?? '');
    $new_tenant = trim($_POST['tenant_id'] ?? '');
    $loginname  = $_SESSION['loginname'] ?? 'unknown';

    if (empty($new_token) || empty($new_tenant)) {
        echo json_encode(['status' => 'error', 'message' => 'กรุณากรอก Tenant ID และ Bearer Token']);
        exit;
    }

    if (count(explode('.', $new_token)) !== 3) {
        echo json_encode(['status' => 'error', 'message' => 'รูปแบบ Bearer Token ไม่ถูกต้อง (ต้องมี 3 ส่วนคั่นด้วยจุด)']);
        exit;
    }

    if (sati_token_expired($new_token)) {
        echo json_encode(['status' => 'error', 'message' => 'Token นี้หมดอายุแล้ว กรุณาใช้ Token ใหม่']);
        exit;
    }

    $cfg = sati_read_config();
    $cfg['tenant_id']      = $new_tenant;
    $cfg['bearer_token']   = $new_token;
    $cfg['token_saved_at'] = date('c');
    $cfg['updated_by']     = $loginname;
    sati_write_config($cfg);

    $expire_dt = sati_token_expire_datetime($new_token);
    echo json_encode([
        'status'      => 'success',
        'message'     => 'บันทึก Token สำเร็จ' . ($expire_dt ? ' (หมดอายุ: ' . $expire_dt . ')' : ''),
        'expire_time' => $expire_dt,
    ]);
    exit;
}

// ============================================================
// ACTION: ดึงสถานะ Token ปัจจุบัน
// ============================================================
if ($action === 'get_token_status') {
    $cfg     = sati_read_config();
    $token   = $cfg['bearer_token'] ?? '';
    $expired = sati_token_expired($token);
    $expire_time = sati_token_expire_datetime($token);

    echo json_encode([
        'status'       => 'success',
        'is_expired'   => $expired,
        'expire_time'  => $expire_time,
        'saved_at'     => $cfg['token_saved_at'] ?? '',
        'updated_by'   => $cfg['updated_by'] ?? '',
        'tenant_id'    => $cfg['tenant_id'] ?? '',
        'token_length' => strlen($token),
    ]);
    exit;
}

// ============================================================
// ACTION: Debug IP — แสดง Public IP ของ PHP Server
// ============================================================
if ($action === 'debug_ip') {
    $ch = curl_init('https://api.ipify.org?format=json');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ]);
    $ip_raw = curl_exec($ch);
    curl_close($ch);
    $ip_data = json_decode($ip_raw, true);

    $cfg   = sati_read_config();
    $token = $cfg['bearer_token'] ?? '';

    echo json_encode([
        'status'           => 'success',
        'server_public_ip' => $ip_data['ip'] ?? 'ดึงไม่ได้',
        'php_server_addr'  => $_SERVER['SERVER_ADDR'] ?? 'N/A',
        'token_expired'    => sati_token_expired($token),
        'token_exp_date'   => sati_token_expire_datetime($token),
        'token_length'     => strlen($token),
        'php_version'      => PHP_VERSION,
    ]);
    exit;
}

// ============================================================
// ACTION: Debug Request — แสดงค่าที่ PHP จะส่งจริงๆ ไปหา SATI
// ============================================================
if ($action === 'debug_request') {
    $cfg               = sati_read_config();
    $tenant_identifier = trim($cfg['tenant_id'] ?? '');
    $authorization     = trim($cfg['bearer_token'] ?? '');
    $test_an           = trim($_POST['an'] ?? 'TEST_AN');
    $payload           = json_encode(['admissionNumber' => (string) $test_an]);

    // ส่ง request จริงพร้อม verbose
    $verbose_log = fopen('php://temp', 'w+');
    $ch = curl_init(SATI_API_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_VERBOSE        => true,
        CURLOPT_STDERR         => $verbose_log,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Accept: application/json',
            'tenant-identifier: ' . $tenant_identifier,
            'Authorization: Bearer ' . $authorization,
        ],
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    rewind($verbose_log);
    $verbose_out = stream_get_contents($verbose_log);
    fclose($verbose_log);
    curl_close($ch);

    echo json_encode([
        'status'           => 'success',
        'httpcode'         => $httpcode,
        'response'         => $response,
        'tenant_id_sent'   => $tenant_identifier,
        'tenant_id_length' => strlen($tenant_identifier),
        'token_prefix'     => substr($authorization, 0, 60) . '...',
        'token_length'     => strlen($authorization),
        'payload_sent'     => $payload,
        'verbose_log'      => $verbose_out,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// ============================================================
// ACTION: ดึงข้อมูล Clinical Summary (หลัก)
// ============================================================
$an = trim($_POST['an'] ?? '');

if (empty($an)) {
    echo json_encode(['status' => 'error', 'message' => 'ไม่พบข้อมูล AN']);
    exit;
}

$cfg               = sati_read_config();
$tenant_identifier = $cfg['tenant_id'] ?? '';
$authorization     = $cfg['bearer_token'] ?? '';

if (empty($tenant_identifier) || empty($authorization)) {
    echo json_encode([
        'status'     => 'error',
        'message'    => 'ยังไม่ได้ตั้งค่า SATI Token กรุณากดปุ่ม "ตั้งค่า Token" ก่อนใช้งาน',
        'need_token' => true,
    ]);
    exit;
}

// ตรวจสอบอายุจาก JWT exp จริง
if (sati_token_expired($authorization)) {
    $expire_dt = sati_token_expire_datetime($authorization);
    echo json_encode([
        'status'     => 'error',
        'message'    => 'SATI Token หมดอายุแล้ว' . ($expire_dt ? ' (หมด: ' . $expire_dt . ')' : '') . ' กรุณาอัพเดท Token ใหม่',
        'need_token' => true,
    ]);
    exit;
}

// Request Payload
$payload = json_encode(['admissionNumber' => (string) $an]);

// Initialize cURL
$ch = curl_init(SATI_API_URL);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_PROXY          => '',        // bypass system proxy
    CURLOPT_USERAGENT      => 'curl/8.19.0',
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'tenant-identifier: ' . $tenant_identifier,
        'Authorization: Bearer ' . $authorization,
        'Expect:',
    ],
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_SSL_VERIFYPEER => false,
]);

$response   = curl_exec($ch);
$httpcode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

// ---- cURL Error ----
if ($response === false) {
    echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถเชื่อมต่อ API ได้: ' . $curl_error]);
    exit;
}

$data = json_decode($response, true);

// ---- 401 / 403 : Token ผิดหรือหมดอายุ หรือ IP ถูกบล็อก ----
if ($httpcode === 401 || $httpcode === 403) {
    $ch_ip = curl_init('https://api.ipify.org?format=json');
    curl_setopt_array($ch_ip, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5, CURLOPT_SSL_VERIFYPEER => false]);
    $ip_raw = curl_exec($ch_ip);
    curl_close($ch_ip);
    $server_ip = json_decode($ip_raw, true)['ip'] ?? 'ไม่ทราบ';

    echo json_encode([
        'status'         => 'error',
        'message'        => 'API ตอบ HTTP ' . $httpcode . " — Token อาจถูกบล็อกโดย IP\nIP ของ PHP Server: " . $server_ip . "\nกรุณาแจ้ง SATI ให้ Whitelist IP นี้",
        'need_token'     => false,
        'httpcode'       => $httpcode,
        'server_ip'      => $server_ip,
        'raw_response'   => $response,
        // Debug: ตรวจสอบค่าที่ PHP ส่งออกไปจริงๆ
        'debug_an'       => $an,
        'debug_payload'  => $payload,
        'debug_tenant'   => substr($tenant_identifier, 0, 8) . '...',
        'debug_token_len' => strlen($authorization),
    ]);
    exit;
}

// ---- 404 : ไม่พบข้อมูล AN นี้ใน SATI ----
if ($httpcode === 404) {
    echo json_encode([
        'status'   => 'not_found',
        'message'  => 'ไม่พบข้อมูล Clinical Summary สำหรับ AN: ' . htmlspecialchars($an) . ' ในระบบ SATI',
        'httpcode' => 404,
    ]);
    exit;
}

// ---- Success ----
if ($httpcode >= 200 && $httpcode < 300) {

    $record = $data['data'] ?? $data;

    if (!isset($record['finalDiagnosis'])) {
        echo json_encode([
            'status'       => 'error',
            'message'      => 'API ตอบสำเร็จ แต่รูปแบบข้อมูลไม่ถูกต้อง (ไม่พบ finalDiagnosis)',
            'httpcode'     => $httpcode,
            'raw_response' => $response,
        ]);
        exit;
    }

    try {
        $conn = DbUtils::get_connection(
            DbConstant::HOSXP_HOST,
            DbConstant::HOSXP_USERNAME,
            DbConstant::HOSXP_PASSWORD,
            DbConstant::KPHIS_DBNAME
        );

        $final_diagnosis = $record['finalDiagnosis'] ?? '';
        $progression     = $record['progression']    ?? '';
        $follow_up       = $record['followUp']       ?? '';

        $stmt_check = $conn->prepare(
            "SELECT COUNT(*) FROM prs_clinical_summay WHERE an = :an"
        );
        $stmt_check->execute(['an' => $an]);
        $count = $stmt_check->fetchColumn();

        if ($count > 0) {
            $stmt = $conn->prepare(
                "UPDATE prs_clinical_summay
                 SET final_diagnosis = :final_diagnosis,
                     progression     = :progression,
                     follow_up       = :follow_up
                 WHERE an = :an"
            );
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO prs_clinical_summay
                    (an, final_diagnosis, progression, follow_up)
                 VALUES (:an, :final_diagnosis, :progression, :follow_up)"
            );
        }

        $stmt->execute([
            'an'              => $an,
            'final_diagnosis' => $final_diagnosis,
            'progression'     => $progression,
            'follow_up'       => $follow_up,
        ]);

        echo json_encode(['status' => 'success', 'message' => 'ดึงข้อมูลและบันทึกสำเร็จ']);

    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาด Database: ' . $e->getMessage()]);
    }

} else {
    $err_msg = $data['message'] ?? ('API ส่งคืนข้อผิดพลาด HTTP ' . $httpcode);
    echo json_encode([
        'status'       => 'error',
        'message'      => $err_msg,
        'httpcode'     => $httpcode,
        'raw_response' => $response,
    ]);
}
