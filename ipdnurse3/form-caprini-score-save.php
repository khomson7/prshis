<?php
require_once '../include/Session.php';
require_once '../include/session-sso.php';
require_once '../include/DbUtils.php';

date_default_timezone_set('Asia/Bangkok');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$conn = DbUtils::get_hosxp_connection();
$loginname = isset($_SESSION['loginname']) ? $_SESSION['loginname'] : '';

function capriniEvaluationMessage(int $score): string
{
    if ($score <= 1)
        return 'ประเมิน Caprini Score = ' . $score . ' คะแนน ระดับความเสี่ยง: Low Risk';
    elseif ($score == 2)
        return 'ประเมิน Caprini Score = ' . $score . ' คะแนน ระดับความเสี่ยง: Moderate Risk';
    elseif ($score <= 4)
        return 'ประเมิน Caprini Score = ' . $score . ' คะแนน ระดับความเสี่ยง: High Risk';
    else
        return 'ประเมิน Caprini Score = ' . $score . ' คะแนน ระดับความเสี่ยง: Highest Risk';
}

function getCheckbox($name): int
{
    return isset($_POST[$name]) ? 1 : 0;
}

try {
    $conn->beginTransaction();

    $an = trim($_POST['an'] ?? '');
    $hn = trim($_POST['hn'] ?? '');
    $assessment_date = $_POST['assessment_date'] ?? date('Y-m-d');
    $assessment_time = $_POST['assessment_time'] ?? date('H:i');
    $total_score = isset($_POST['total_score']) ? (int) $_POST['total_score'] : 0;
    $risk_level = $_POST['risk_level'] ?? '';
    $age_range = $_POST['age_range'] ?? '<41';
    $now = date('Y-m-d H:i:s');

    if (empty($an))
        throw new Exception("ไม่พบข้อมูล AN");

    $fields = [
        'minor_surgery',
        'bmi_over_25',
        'swollen_legs',
        'varicose_veins',
        'pregnancy_postpartum',
        'history_unexplained_miscarriage',
        'oral_contraceptives_hrt',
        'sepsis_1_month',
        'severe_lung_disease',
        'abnormal_pulmonary_function',
        'ischemic_heart_disease',
        'chf_1_month',
        'inflammatory_bowel_disease',
        'bed_rest',
        'arthroscopic_surgery',
        'major_surgery_over_45m',
        'laparoscopic_surgery_over_45m',
        'cancer_patient',
        'bedridden_over_72h',
        'patient_in_cast',
        'central_venous_access',
        'history_vte',
        'family_history_vte',
        'factor_v_leiden',
        'prothrombin_20210a',
        'lupus_anticoagulant',
        'anticardiolipin_antibodies',
        'increased_homocysteine',
        'heparin_induced_thrombocytopenia',
        'thrombophilia',
        'stroke_1_month',
        'elective_major_lower_extremity_arthroplasty',
        'hip_pelvis_leg_fracture',
        'acute_spinal_cord_injury_1_month'
    ];

    $params = [
        'hn' => $hn,
        'an' => $an,
        'assessment_date' => $assessment_date,
        'assessment_time' => $assessment_time,
        'total_score' => $total_score,
        'risk_level' => $risk_level,
        'age_range' => $age_range,
    ];
    foreach ($fields as $field) {
        $params[$field] = getCheckbox($field);
    }

    // INSERT
    $columns = implode(', ', array_keys($params));
    $placeholders = ':' . implode(', :', array_keys($params));
    $conn->prepare("INSERT INTO prs_caprini_score ({$columns}) VALUES ({$placeholders})")
        ->execute($params);
    $new_id = $conn->lastInsertId();

    $conn->commit();

    Session::insertSystemAccessLog(json_encode([
        'form' => 'CAPRINI-FORM',
        'action' => 'INSERT',
        'an' => $an,
        'score' => $total_score,
    ], JSON_UNESCAPED_UNICODE));

    echo json_encode(['status' => 'success', 'id' => $new_id]);

} catch (Exception $e) {
    if ($conn->inTransaction())
        $conn->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>