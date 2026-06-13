<?php
ob_start();
require_once '../include/Session.php';
require_once '../include/DbUtils.php';
date_default_timezone_set('Asia/Bangkok');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean(); header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status'=>'error','message'=>'Invalid request method']); exit;
}
$loginname = isset($_SESSION['loginname']) ? $_SESSION['loginname'] : '';
if (!$loginname) {
    ob_end_clean(); header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status'=>'error','message'=>'Session expired']); exit;
}

function p($k,$d=null){ return (isset($_POST[$k])&&$_POST[$k]!=='') ? $_POST[$k] : $d; }
function pint($k,$d=null){ return (isset($_POST[$k])&&$_POST[$k]!=='') ? (int)$_POST[$k] : $d; }
function pf($k,$d=null){ return (isset($_POST[$k])&&$_POST[$k]!=='') ? (float)$_POST[$k] : $d; }
function pb($k){ return (isset($_POST[$k])&&$_POST[$k]=='1') ? 1 : 0; }

try {
    $conn = DbUtils::get_hosxp_connection();
    $conn->beginTransaction();
    $an = trim(p('an',''));
    $id = pint('id',0);

    $fields = [
        'an'=>$an,
        'ecog'=>pint('ecog'),
        'diagnosis'=>p('diagnosis'),
        'operation'=>p('operation'),
        'lab_hb'=>pf('lab_hb'),'lab_wbc'=>pf('lab_wbc'),
        'lab_n'=>pf('lab_n'),'lab_l'=>pf('lab_l'),'lab_m'=>pf('lab_m'),
        'lab_e'=>pf('lab_e'),'lab_b'=>pf('lab_b'),
        'lab_hct'=>pf('lab_hct'),'lab_plt'=>pf('lab_plt'),'lab_anc'=>pf('lab_anc'),
        'lab_na'=>pf('lab_na'),'lab_k'=>pf('lab_k'),'lab_bun'=>pf('lab_bun'),
        'lab_hco3'=>pf('lab_hco3'),'lab_cl'=>pf('lab_cl'),'lab_scr'=>pf('lab_scr'),
        'lab_alb'=>pf('lab_alb'),'lab_glob'=>pf('lab_glob'),
        'lab_tb'=>pf('lab_tb'),'lab_db'=>pf('lab_db'),
        'lab_ast'=>pf('lab_ast'),'lab_alt'=>pf('lab_alt'),'lab_alp'=>pf('lab_alp'),
        'order_type'=>p('order_type'),
        'order_date'=>p('order_date',date('Y-m-d')),
        'cycle_no'=>pint('cycle_no'),
        'bw'=>pf('bw'),'ht'=>pf('ht'),'bsa'=>pf('bsa'),
        'order_ac4'=>pb('order_ac4'),
        'order_cbc_lab'=>pb('order_cbc_lab'),
        'order_nss1000'=>pb('order_nss1000'),
        'premed_dexa_ondan'=>pb('premed_dexa_ondan'),
        'premed_cpm'=>pb('premed_cpm'),
        'premed_famotidine'=>pb('premed_famotidine'),
        'premed_other'=>p('premed_other'),
        'paclitaxel_dose'=>pf('paclitaxel_dose'),
        'next_appt_date'=>p('next_appt_date'),
        'fu_lab_cbc'=>pb('fu_lab_cbc'),'fu_lab_electrolyte'=>pb('fu_lab_electrolyte'),
        'fu_lab_lft'=>pb('fu_lab_lft'),'fu_lab_bun'=>pb('fu_lab_bun'),
        'fu_lab_scr'=>pb('fu_lab_scr'),'fu_lab_ua'=>pb('fu_lab_ua'),
        'fu_lab_cea'=>pb('fu_lab_cea'),'fu_lab_cxrpa'=>pb('fu_lab_cxrpa'),
        'fu_lab_other'=>p('fu_lab_other'),
        'hmed_dexa4'=>pb('hmed_dexa4'),'hmed_ondan8'=>pb('hmed_ondan8'),
        'hmed_metoclo'=>pb('hmed_metoclo'),'hmed_tramadol'=>pb('hmed_tramadol'),
        'hmed_senokot'=>pb('hmed_senokot'),
        'hmed_multivit'=>pb('hmed_multivit'),'hmed_multivit_qty'=>p('hmed_multivit_qty'),
        'hmed_ff200'=>pb('hmed_ff200'),'hmed_ff200_qty'=>p('hmed_ff200_qty'),
        'hmed_lorazepam'=>pb('hmed_lorazepam'),'hmed_lorazepam_qty'=>p('hmed_lorazepam_qty'),
        'hmed_extra1'=>p('hmed_extra1'),'hmed_extra1_sig'=>p('hmed_extra1_sig'),
        'hmed_extra2'=>p('hmed_extra2'),'hmed_extra2_sig'=>p('hmed_extra2_sig'),
        'hmed_extra3'=>p('hmed_extra3'),'hmed_extra3_sig'=>p('hmed_extra3_sig'),
        'created_name'=>p('created_name', $_SESSION['name']??$loginname),
        'created_position'=>p('created_position', $_SESSION['entryposition']??''),
        'created_by'=>$loginname,
    ];

    if ($id > 0) {
        // UPDATE
        unset($fields['an'],$fields['created_by'],$fields['created_name'],$fields['created_position']);
        $fields['updated_by'] = $loginname;
        $sets = implode(', ', array_map(fn($k)=>"`$k`=:$k", array_keys($fields)));
        $stmt = $conn->prepare("UPDATE prs_ca_breast SET $sets, updated_at=NOW() WHERE id=:id AND an=:an");
        $fields['id'] = $id; $fields['an'] = $an;
        $stmt->execute($fields);
        $new_id = $id;
    } else {
        // INSERT
        $cols = implode(',', array_map(fn($k)=>"`$k`", array_keys($fields)));
        $vals = implode(',', array_map(fn($k)=>":$k", array_keys($fields)));
        $stmt = $conn->prepare("INSERT INTO prs_ca_breast ($cols) VALUES ($vals)");
        $stmt->execute($fields);
        $new_id = $conn->lastInsertId();
    }

    $conn->commit();
    Session::insertSystemAccessLog(json_encode(['form'=>'CA-BREAST','action'=>$id?'UPDATE':'SAVE','an'=>$an,'id'=>$new_id],JSON_UNESCAPED_UNICODE));
    ob_end_clean(); header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status'=>'success','id'=>$new_id]);

} catch (Exception $e) {
    if(isset($conn)) $conn->rollBack();
    ob_end_clean(); header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
