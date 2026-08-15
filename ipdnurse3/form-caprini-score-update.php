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

function getCheckbox($name): int
{
    return isset($_POST[$name]) ? 1 : 0;
}

try {
    $conn->beginTransaction();

    $id = (int) $_POST['id'];
    $an = trim($_POST['an'] ?? '');
    $hn = trim($_POST['hn'] ?? '');
    $assessment_date = $_POST['assessment_date'] ?? date('Y-m-d');
    $total_score = isset($_POST['total_score']) ? (int) $_POST['total_score'] : 0;
    $risk_level = $_POST['risk_level'] ?? '';
    $age_range = $_POST['age_range'] ?? '<41';

    if (empty($an) || !$id)
        throw new Exception("ไม่พบข้อมูล AN หรือ ID");

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

    $set_parts = [
        'hn              = :hn',
        'assessment_date = :assessment_date',
        'total_score     = :total_score',
        'risk_level      = :risk_level',
        'age_range       = :age_range',
    ];
    foreach ($fields as $field) {
        $set_parts[] = "{$field} = :{$field}";
    }

    $params = [
        'id' => $id,
        'an' => $an,
        'hn' => $hn,
        'assessment_date' => $assessment_date,
        'total_score' => $total_score,
        'risk_level' => $risk_level,
        'age_range' => $age_range,
    ];
    foreach ($fields as $field) {
        $params[$field] = getCheckbox($field);
    }

    $conn->prepare("UPDATE prs_caprini_score SET " . implode(', ', $set_parts) . " WHERE id = :id AND an = :an")
        ->execute($params);

    $conn->commit();

    Session::insertSystemAccessLog(json_encode([
        'form' => 'CAPRINI-FORM',
        'action' => 'UPDATE',
        'an' => $an,
        'score' => $total_score,
    ], JSON_UNESCAPED_UNICODE));

    echo json_encode(['status' => 'success', 'id' => $id]);

} catch (Exception $e) {
    if ($conn->inTransaction())
        $conn->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>