<?php
require_once '../include/Session.php';
require_once '../include/session-sso.php';
require_once '../include/DbUtils.php';
date_default_timezone_set('Asia/Bangkok');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status'=>'error','message'=>'Invalid request']); exit;
}

$conn      = DbUtils::get_hosxp_connection();
$loginname = isset($_SESSION['loginname']) ? $_SESSION['loginname'] : '';
$now       = date('Y-m-d H:i:s');

try {
    $an = trim(isset($_POST['an']) ? $_POST['an'] : '');
    if (!$an) { echo json_encode(['status'=>'error','message'=>'ไม่พบ AN']); exit; }

    $v  = function($k){ $val=isset($_POST[$k])?$_POST[$k]:null; return ($val===''||$val===null)?null:$val; };
    $vi = function($k){ $val=isset($_POST[$k])?$_POST[$k]:null; return ($val===''||$val===null)?null:(int)$val; };

    $fields = [];

    // ---- LATCH 4 ครั้ง × 3 เวลา × ทุกหัวข้อ ----
    $latchKeys = ['latch_l','latch_a','latch_t','latch_c','latch_h','milk_level'];
    for ($d = 1; $d <= 4; $d++) {
        $fields["assess_date_$d"] = $v("assess_date_$d");
        for ($t = 1; $t <= 3; $t++) {
            $tot = 0; $hasData = false;
            foreach ($latchKeys as $k) {
                $col = "{$k}_{$d}_{$t}";
                $val = isset($_POST[$col]) ? $_POST[$col] : null;
                $fields[$col] = ($val===''||$val===null) ? null : (int)$val;
                if ($fields[$col] !== null) { $tot += $fields[$col]; $hasData = true; }
            }
            $fields["assess_time_{$d}_{$t}"] = $hasData ? $v("assess_time_{$d}_{$t}") : null;
            $fields["latch_total_{$d}_{$t}"] = $hasData ? $tot : null;
        }
    }

    // ---- ความเครียด ----
    $stressTotal = 0; $hasStress = false;
    for ($q = 1; $q <= 5; $q++) {
        $val = $vi("stress_q$q");
        $fields["stress_q$q"] = $val;
        if ($val !== null) { $stressTotal += $val; $hasStress = true; }
    }
    $fields['stress_total']  = $hasStress ? $stressTotal : null;
    $fields['depression_q1'] = $vi('depression_q1');
    $fields['depression_q2'] = $vi('depression_q2');
    $fields['alcohol_ever']  = $vi('alcohol_ever');
    $fields['alcohol_refer'] = $v('alcohol_refer');

    // ---- Upsert 1 record per AN ----
    $stmt_chk = $conn->prepare("SELECT id FROM `prs_latch_postnatal` WHERE an=:an LIMIT 1");
    $stmt_chk->execute(['an'=>$an]);
    $existing = $stmt_chk->fetchColumn();

    if ($existing) {
        $sets = [];
        foreach ($fields as $col=>$val) $sets[] = "`$col`=:$col";
        $sets[] = '`updated_at`=:updated_at';
        $sets[] = '`updated_by`=:updated_by';
        $params = $fields;
        $params['updated_at'] = $now;
        $params['updated_by'] = $loginname;
        $params['an'] = $an;
        $conn->prepare("UPDATE `prs_latch_postnatal` SET ".implode(',',$sets)." WHERE an=:an")->execute($params);
        $rid = $existing;
    } else {
        $cols   = array_keys($fields);
        $colStr = '`an`,'.implode(',',array_map(function($c){return "`$c`";},$cols)).',`created_at`,`created_by`';
        $valStr = ':an,'.implode(',',array_map(function($c){return ":$c";},$cols)).',:created_at,:created_by';
        $params = $fields;
        $params['an']         = $an;
        $params['created_at'] = $now;
        $params['created_by'] = $loginname;
        $conn->prepare("INSERT INTO `prs_latch_postnatal` ($colStr) VALUES ($valStr)")->execute($params);
        $rid = $conn->lastInsertId();
    }

    Session::insertSystemAccessLog(json_encode([
        'form'=>'LATCH-POSTNATAL','action'=>$existing?'UPDATE':'INSERT','an'=>$an
    ],JSON_UNESCAPED_UNICODE));

    echo json_encode(['status'=>'success','id'=>$rid]);

} catch (Exception $e) {
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}

