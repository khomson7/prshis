<?php
/*
require_once './project/function/SessionManager.php';
SessionManager::checkLoginSessionAndShowMessage(); //เช็ค session
// SessionManager::checkPermissionAndShowMessage('KPHIS_ACCESS_IPD_NURSE_PROGRAM');
if(!SessionManager::checkPermission('VITAL_SIGN','ADD') &&
   !SessionManager::checkPermission('VITAL_SIGN','EDIT')){
    // SessionManager::showMessage();
    return;
}
*/
require_once '../include/DbUtils.php';
require_once '../include/KphisQueryUtils.php';
$an = empty($_REQUEST['an']) ? null : $_REQUEST['an'];
$vs_id = empty($_REQUEST['vs_id']) ? null : $_REQUEST['vs_id'];
$hn = KphisQueryUtils::getHnByAn($an);
$data_mode = empty($_REQUEST['data_mode']) ? 'I':$_REQUEST['data_mode'];

if($data_mode == 'U'){
    if(!empty($vs_id)){
        $conn = DbUtils::get_hosxp_connection();

        $sql = "SELECT vs_id,hn,an,vs_datetime, date(vs_datetime) as vs_date, time(vs_datetime) as vs_time,bt,pr,rr,respirator,sbp,dbp,inotrope,sat,cvp,
            end_co2,conscious_id,bw,height,urine,catheter,urine_amount,urine_duration,feces,head,t_inc,line_id,line_no,
            line_mark,braden,pain,eye,verbal,movement,right_pupil,right_cha_id,left_pupil,left_cha_id,
            va_id,lt_arm,lt_leg,rt_arm,rt_leg,
            mass_id,severity,had_name,had_drop,hct,dtx,bl,mcb,suction,
            nb,o2_id,o2_flow,tube_id,tube_no,tube_mark,ventilator_name,mode,tv,pip,
            r_rate,i_rate,e_rate,ti,ps,fio2,peep,ft,delta_p,map,
            intake_id,intake_type,intake_amount,intake_absorb,output_id,output_amount,
            lr_int,lr_dur,lr_fsh,lr_sev,lr_cer,lr_eff,lr_sta,lr_mem,lr_af,other,
            create_user,create_datetime,update_user,update_datetime,version
            FROM ".DbConstant::KPHIS_DBNAME.".ipd_vs_vital_sign
            WHERE vs_id=:vs_id
            ORDER BY vs_datetime ";

        $parameters['vs_id'] = $vs_id;
        $stmt = $conn->prepare($sql);
        $stmt->execute($parameters);
        $row = $stmt->fetch();

        $an = $row['an'];
        $hn = $row['hn'];
    } else {
        exit;
    }
} else {
    $row = [];
}
?>
<?php
    require_once '../include/SelectUtils.php';
?>
<div class="container-fluid">
    <div class="card border-primary row">
        <div class="card-body ">
            <div class="row pt-3">
                <div class="col-md-12">
                <form id="vital-sign-form" onsubmit="onclickVitalSignFormSaveButton(event)">
                    <input type="hidden" id="vs_id" name="vs_id" value="<?=htmlspecialchars($vs_id)?>">
                    <input type="hidden" id="vs_an" name="an" value="<?=htmlspecialchars($an)?>">
                    <input type="hidden" id="hn" name="hn" value="<?=htmlspecialchars($hn)?>">
                    <input type="hidden" id="data_mode" name="data_mode" value="<?=htmlspecialchars($data_mode)?>">
                    <div class="form-group row">
                        <label for="vs_date" class="col-sm-4 text-right col-form-label">วันที่บันทึก</label>
                        <div class="col-sm-6">
                            <input type="date" class="form-control" id="vs_date" name="vs_date" required value="<?=(isset($row['vs_date']) ? htmlspecialchars($row['vs_date']) : '')?>">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="vs_time" class="col-sm-4 text-right col-form-label">เวลาที่บันทึก</label>
                        <div class="col-sm-6">
                            <input type="time" class="form-control" id="vs_time" name="vs_time" required value="<?=(isset($row['vs_time']) ? htmlspecialchars($row['vs_time']) : '')?>">
                        </div>
                    </div>
                    <nav class="pb-3">
                        <div class="nav nav-tabs" id="nav-tab" role="tablist">
                            <a class="nav-item nav-link active" id="nav-vitalsign-tab" data-toggle="tab" href="#nav-vitalsign"
                                role="tab" aria-controls="nav-vitalsign" aria-selected="true">VS</a>
                            <a class="nav-item nav-link" id="nav-ptinfo-tab" data-toggle="tab" href="#nav-ptinfo"
                                role="tab" aria-controls="nav-ptinfo" aria-selected="false">Info</a>
                            <a class="nav-item nav-link" id="nav-score-tab" data-toggle="tab" href="#nav-score"
                                role="tab" aria-controls="nav-score" aria-selected="false">Score</a>
                            <a class="nav-item nav-link" id="nav-had-tab" data-toggle="tab" href="#nav-had"
                                role="tab" aria-controls="nav-had" aria-selected="false">HAD</a>
                            <a class="nav-item nav-link" id="nav-intervention-tab" data-toggle="tab" href="#nav-intervention"
                                role="tab" aria-controls="nav-intervention" aria-selected="false">Intervention</a>
                            <a class="nav-item nav-link" id="nav-o2-tab" data-toggle="tab" href="#nav-o2"
                                role="tab" aria-controls="nav-o2" aria-selected="false">O2</a>
                            <!-- <a class="nav-item nav-link" id="nav-io-tab" data-toggle="tab" href="#nav-io"
                                role="tab" aria-controls="nav-io" aria-selected="false">IO</a> -->
                            <a class="nav-item nav-link" id="nav-lr-tab" data-toggle="tab" href="#nav-lr"
                                role="tab" aria-controls="nav-lr" aria-selected="false">LR/หลังคลอด</a>
                        </div>
                    </nav>
                    <div class="tab-content" id="nav-vs-tabContent">
                        <div class="tab-pane fade show active" id="nav-vitalsign" role="tabpanel" aria-labelledby="nav-vitalsign-tab">
                            <div class="form-group row">
                                <label for="bt" class="col-sm-4 text-right col-form-label">BT</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="bt" name="bt" value="<?=(isset($row['bt']) ? htmlspecialchars($row['bt']) : '')?>" oninput="oninputVitalSignValue()">
                                </div>
                                <div class="col-sm-1" id="score_bt_result"></div>
                            </div>
                            <div class="form-group row">
                                <label for="pr" class="col-sm-4 text-right col-form-label">PR</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="pr" name="pr" value="<?=(isset($row['pr']) ? htmlspecialchars($row['pr']) : '')?>" oninput="oninputVitalSignValue()">
                                </div>
                                <div class="col-sm-1" id="score_pr_result"></div>
                            </div>
                            <div class="form-group row">
                                <label for="rr" class="col-sm-4 text-right col-form-label">RR</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="rr" name="rr" value="<?=(isset($row['rr']) ? htmlspecialchars($row['rr']) : '')?>" oninput="oninputVitalSignValue()">
                                </div>
                                <div class="col-sm-1" id="score_rr_result"></div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-6 offset-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="respirator" name="respirator" <?=(isset($row['respirator']) && $row['respirator'] == 'Y' ? 'checked' : '')?> onchange="oninputVitalSignValue()">
                                        <label class="form-check-label" for="respirator">ใส่เครื่องช่วยหายใจ</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="sbp" class="col-sm-4 text-right col-form-label">SBP</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="sbp" name="sbp" value="<?=(isset($row['sbp']) ? htmlspecialchars($row['sbp']) : '')?>" oninput="oninputVitalSignValue()">
                                </div>
                                <div class="col-sm-1" id="score_sbp_result"></div>
                            </div>
                            <div class="form-group row">
                                <label for="dbp" class="col-sm-4 text-right col-form-label">DBP</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="dbp" name="dbp" value="<?=(isset($row['dbp']) ? htmlspecialchars($row['dbp']) : '')?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-6 offset-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="inotrope" name="inotrope" <?=(isset($row['inotrope']) && $row['inotrope'] == 'Y' ? 'checked' : '')?> onchange="oninputVitalSignValue()">
                                        <label class="form-check-label" for="inotrope">ให้ยากระตุ้นความดันโลหิต</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="map" class="col-sm-4 text-right col-form-label">MAP</label>
                                <div class="col-sm-6">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="map" name="map" value="<?=(isset($row['map']) ? htmlspecialchars($row['map']) : '')?>">
                                        <div class="input-group-append">
                                            <button class="btn btn-secondary" type="button" onclick="onclick_map_calculate_button(event)"><i class="fas fa-calculator"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="sat" class="col-sm-4 text-right col-form-label">O2 Sat</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="sat" name="sat" value="<?=(isset($row['sat']) ? htmlspecialchars($row['sat']) : '')?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="conscious_id" class="col-sm-4 text-right col-form-label">Conscious</label>
                                <div class="col-sm-6">
                                    <select class="form-control" id="conscious_id" name="conscious_id" onchange="oninputVitalSignValue()">
                                        <option value=""></option>
                                        <?=SelectUtils::getConsciousSelectOption((isset($row['conscious_id']) ? $row['conscious_id'] : null))?>
                                    </select>
                                </div>
                                <div class="col-sm-1" id="score_conscious_id_result"></div>
                            </div>
                            <div class="form-group row">
                                <label for="conscious_id" class="col-sm-4 text-right col-form-label">Urine(ปริมาณ)</label>
                                <div class="col-sm-6">
                                    <select class="form-control" name="urine_amount" id="urine_amount" onchange="oninputVitalSignValue()">
                                        <option value=""></option>
                                        <?=SelectUtils::getUrinAmountSelectOption((isset($row['urine_amount']) ? $row['urine_amount'] : null))?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="conscious_id" class="col-sm-4 text-right col-form-label">Urine(ระยะเวลา)</label>
                                <div class="col-sm-6">
                                    <select class="form-control" name="urine_duration" id="urine_duration" onchange="oninputVitalSignValue()">
                                        <option value=""></option>
                                        <?=SelectUtils::getUrinDurationSelectOption((isset($row['urine_duration']) ? $row['urine_duration'] : null))?>
                                    </select>
                                </div>
                                <div class="col-sm-1" id="score_urine_result"></div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-6 offset-md-4" id="score_total_result"></div>
                            </div><hr>
                            <div class="form-group row">
                                <label for="urine" class="col-sm-4 text-right col-form-label">Urine/12 ชม.</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="urine" name="urine" value="<?=(isset($row['urine']) ? htmlspecialchars($row['urine']) : '')?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-6 offset-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="catheter" name="catheter" <?=(isset($row['catheter']) && $row['catheter'] == 'Y' ? 'checked' : '')?> onchange="onchange_catheter()">
                                        <label class="form-check-label" for="catheter">ใส่สายสวนปัสสาวะ</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="feces" class="col-sm-4 text-right col-form-label">Feces/12 ชม.</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="feces" name="feces" value="<?=(isset($row['feces']) ? htmlspecialchars($row['feces']) : '')?>">
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="nav-ptinfo" role="tabpanel" aria-labelledby="nav-ptinfo-tab">
                            <div class="form-group row">
                                <label for="cvp" class="col-sm-4 text-right col-form-label">CVP</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="cvp" name="cvp" value="<?=(isset($row['cvp']) ? htmlspecialchars($row['cvp']) : '')?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="end_co2" class="col-sm-4 text-right col-form-label">End Tidal CO2</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="end_co2" name="end_co2" value="<?=(isset($row['end_co2']) ? htmlspecialchars($row['end_co2']) : '')?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="bw" class="col-sm-4 text-right col-form-label">BW</label>
                                <div class="col-sm-6">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="bw" name="bw" value="<?=(isset($row['bw']) ? (float)($row['bw']) : '')?>">
                                        <div class="input-group-append">
                                            <span class="input-group-text">kg</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="height" class="col-sm-4 text-right col-form-label">Height</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="height" name="height" value="<?=(isset($row['height']) ? htmlspecialchars($row['height']) : '')?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="head" class="col-sm-4 text-right col-form-label">Head</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="head" name="head" value="<?=(isset($row['head']) ? htmlspecialchars($row['head']) : '')?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="t_inc" class="col-sm-4 text-right col-form-label">Incubator Temp</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="t_inc" name="t_inc" value="<?=(isset($row['t_inc']) ? htmlspecialchars($row['t_inc']) : '')?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="line_id" class="col-sm-4 text-right col-form-label">Line</label>
                                <div class="col-sm-6">
                                    <select class="form-control" id="line_id" name="line_id">
                                        <option value=""></option>
                                        <?=SelectUtils::getLineSelectOption((isset($row['line_id']) ? $row['line_id'] : null))?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="line_no" class="col-sm-4 text-right col-form-label">Line No.</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="line_no" name="line_no" value="<?=(isset($row['line_no']) ? htmlspecialchars($row['line_no']) : '')?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="line_mark" class="col-sm-4 text-right col-form-label">Line Mark</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="line_mark" name="line_mark" value="<?=(isset($row['line_mark']) ? htmlspecialchars($row['line_mark']) : '')?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="line_mark" class="col-sm-4 text-right col-form-label">Other</label>
                                <div class="col-sm-6">
                                    <textarea class="form-control" id="other" name="other"  rows="3"><?=(isset($row['other']) ? htmlspecialchars($row['other']) : '')?></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="nav-score" role="tabpanel" aria-labelledby="nav-score-tab">
                            <div class="form-group row">
                                <label for="braden" class="col-sm-4 text-right col-form-label">Braden</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="braden" name="braden" value="<?=(isset($row['braden']) ? htmlspecialchars($row['braden']) : '')?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="pain" class="col-sm-4 text-right col-form-label">Pain</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="pain" name="pain" value="<?=(isset($row['pain']) ? htmlspecialchars($row['pain']) : '')?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="eye" class="col-sm-4 text-right col-form-label">Eye</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="eye" name="eye" value="<?=(isset($row['eye']) ? htmlspecialchars($row['eye']) : '')?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="verbal" class="col-sm-4 text-right col-form-label">Verbal</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="verbal" name="verbal" value="<?=(isset($row['verbal']) ? htmlspecialchars($row['verbal']) : '')?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="movement" class="col-sm-4 text-right col-form-label">Movement</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="movement" name="movement" value="<?=(isset($row['movement']) ? htmlspecialchars($row['movement']) : '')?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="right_pupil" class="col-sm-4 text-right col-form-label">Right Pupil</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="right_pupil" name="right_pupil" value="<?=(isset($row['right_pupil']) ? htmlspecialchars($row['right_pupil']) : '')?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="right_cha_id" class="col-sm-4 text-right col-form-label">Right Cha</label>
                                <div class="col-sm-6">
                                    <select class="form-control" id="right_cha_id" name="right_cha_id">
                                        <option value=""></option>
                                        <?=SelectUtils::getChaSelectOption((isset($row['right_cha_id']) ? $row['right_cha_id'] : null))?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="left_pupil" class="col-sm-4 text-right col-form-label">Left Pupil</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="left_pupil" name="left_pupil" value="<?=(isset($row['left_pupil']) ? htmlspecialchars($row['left_pupil']) : '')?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="left_cha_id" class="col-sm-4 text-right col-form-label">Left Cha</label>
                                <div class="col-sm-6">
                                    <select class="form-control" id="left_cha_id" name="left_cha_id">
                                        <option value=""></option>
                                        <?=SelectUtils::getChaSelectOption((isset($row['left_cha_id']) ? $row['left_cha_id'] : null))?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="va_id" class="col-sm-4 text-right col-form-label">VA</label>
                                <div class="col-sm-6">
                                    <select class="form-control" id="va_id" name="va_id">
                                        <option value=""></option>
                                        <?=SelectUtils::getVaSelectOption((isset($row['va_id']) ? $row['va_id'] : null))?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="mass_id" class="col-sm-4 text-right col-form-label">Mass</label>
                                <div class="col-sm-6">
                                    <select class="form-control" id="mass_id" name="mass_id">
                                        <option value=""></option>
                                        <?=SelectUtils::getMassSelectOption((isset($row['mass_id']) ? $row['mass_id'] : null))?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="lt_arm" class="col-sm-4 text-right col-form-label">Lt Arm</label>
                                <div class="col-sm-6">
                                    <select class="form-control" id="lt_arm" name="lt_arm">
                                        <option value=""></option>
                                        <?=SelectUtils::getKphisLtArmSelectOption((isset($row['lt_arm']) ? $row['lt_arm'] : null))?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="lt_leg" class="col-sm-4 text-right col-form-label">Lt Leg</label>
                                <div class="col-sm-6">
                                    <select class="form-control" id="lt_leg" name="lt_leg">
                                        <option value=""></option>
                                        <?=SelectUtils::getKphisLtArmSelectOption((isset($row['lt_leg']) ? $row['lt_leg'] : null))?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="rt_arm" class="col-sm-4 text-right col-form-label">Rt Arm</label>
                                <div class="col-sm-6">
                                    <select class="form-control" id="rt_arm" name="rt_arm">
                                        <option value=""></option>
                                        <?=SelectUtils::getKphisLtArmSelectOption((isset($row['rt_arm']) ? $row['rt_arm'] : null))?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="rt_leg" class="col-sm-4 text-right col-form-label">Rt Leg</label>
                                <div class="col-sm-6">
                                    <select class="form-control" id="rt_leg" name="rt_leg">
                                        <option value=""></option>
                                        <?=SelectUtils::getKphisLtArmSelectOption((isset($row['rt_leg']) ? $row['rt_leg'] : null))?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row" style="display: none;">
                                <label for="severity" class="col-sm-4 text-right col-form-label">Severity</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="severity" name="severity" value="<?=(isset($row['severity']) ? htmlspecialchars($row['severity']) : '')?>">
                                </div>
                            </div>
                            </div>
                            <div class="tab-pane fade" id="nav-had" role="tabpanel" aria-labelledby="nav-had-tab">
                            <div class="form-group row">
                                <label for="had_name" class="col-sm-4 text-right col-form-label">HAD</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="had_name" name="had_name" value="<?=(isset($row['had_name']) ? htmlspecialchars($row['had_name']) : '')?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="had_drop" class="col-sm-4 text-right col-form-label">HAD Drop</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="had_drop" name="had_drop" value="<?=(isset($row['had_drop']) ? htmlspecialchars($row['had_drop']) : '')?>">
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="nav-intervention" role="tabpanel" aria-labelledby="nav-intervention-tab">
                            <div class="form-group row">
                                <label for="hct" class="col-sm-4 text-right col-form-label">HCT</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="hct" name="hct" value="<?=(isset($row['hct']) ? htmlspecialchars($row['hct']) : '')?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="dtx" class="col-sm-4 text-right col-form-label">DTX</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="dtx" name="dtx" value="<?=(isset($row['dtx']) ? htmlspecialchars($row['dtx']) : '')?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="bl" class="col-sm-4 text-right col-form-label">Blood Lactate</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="bl" name="bl" value="<?=(isset($row['bl']) ? htmlspecialchars($row['bl']) : '')?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="mcb" class="col-sm-4 text-right col-form-label">MCB</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="mcb" name="mcb" value="<?=(isset($row['mcb']) ? htmlspecialchars($row['mcb']) : '')?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-4 text-right">
                                    <label for="suction" class="">Suction</label>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="suction" name="suction" <?=(isset($row['suction']) && $row['suction'] == 'Y' ? 'checked' : '')?> >
                                        <label class="form-check-label" for="suction">
                                            ทำ
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-4 text-right">
                                    <label for="nb">NB</label>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="nb" name="nb" <?=(isset($row['nb']) && $row['nb'] == 'Y' ? 'checked' : '')?> >
                                        <label class="form-check-label" for="nb">
                                            ทำ
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="nav-o2" role="tabpanel" aria-labelledby="nav-o2-tab">
                            <div class="form-group row">
                                <label for="o2_id" class="col-sm-4 text-right col-form-label">O2</label>
                                <div class="col-sm-6">
                                    <select class="form-control" id="o2_id" name="o2_id">
                                        <option value=""></option>
                                        <?=SelectUtils::getO2SelectOption((isset($row['o2_id']) ? $row['o2_id'] : null))?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="o2_flow" class="col-sm-4 text-right col-form-label">O2 Flow</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="o2_flow" name="o2_flow" value="<?=(isset($row['o2_flow']) ? htmlspecialchars($row['o2_flow']) : '')?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="tube_id" class="col-sm-4 text-right col-form-label">Tube</label>
                                <div class="col-sm-6">
                                    <select class="form-control" id="tube_id" name="tube_id">
                                        <option value=""></option>
                                        <?=SelectUtils::getTubeSelectOption((isset($row['tube_id']) ? $row['tube_id'] : null))?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="tube_no" class="col-sm-4 text-right col-form-label">Tube No.</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="tube_no" name="tube_no" value="<?=(isset($row['tube_no']) ? htmlspecialchars($row['tube_no']) : '')?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="tube_mark" class="col-sm-4 text-right col-form-label">Tube Mark</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="tube_mark" name="tube_mark" value="<?=(isset($row['tube_mark']) ? htmlspecialchars($row['tube_mark']) : '')?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="ventilator_name" class="col-sm-4 text-right col-form-label">Ventilator Name</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="ventilator_name" name="ventilator_name" value="<?=(isset($row['ventilator_name']) ? htmlspecialchars($row['ventilator_name']) : '')?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="mode" class="col-sm-4 text-right col-form-label">Mode</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="mode" name="mode" value="<?=(isset($row['mode']) ? htmlspecialchars($row['mode']) : '')?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="tv" class="col-sm-4 text-right col-form-label">TV</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="tv" name="tv" value="<?=(isset($row['tv']) ? htmlspecialchars($row['tv']) : '')?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="pip" class="col-sm-4 text-right col-form-label">PIP</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="pip" name="pip" value="<?=(isset($row['pip']) ? htmlspecialchars($row['pip']) : '')?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="r_rate" class="col-sm-4 text-right col-form-label">Respiratory Rate</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="r_rate" name="r_rate" value="<?=(isset($row['r_rate']) ? htmlspecialchars($row['r_rate']) : '')?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="i_rate" class="col-sm-4 text-right col-form-label">Inspiratory (I)</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="i_rate" name="i_rate" value="<?=(isset($row['i_rate']) ? htmlspecialchars($row['i_rate']) : '')?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="e_rate" class="col-sm-4 text-right col-form-label">Expiratory (E)</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="e_rate" name="e_rate" value="<?=(isset($row['e_rate']) ? htmlspecialchars($row['e_rate']) : '')?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="ti" class="col-sm-4 text-right col-form-label">TI</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="ti" name="ti" value="<?=(isset($row['ti']) ? htmlspecialchars($row['ti']) : '')?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="ps" class="col-sm-4 text-right col-form-label">PS</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="ps" name="ps" value="<?=(isset($row['ps']) ? htmlspecialchars($row['ps']) : '')?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="fio2" class="col-sm-4 text-right col-form-label">FIO2</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="fio2" name="fio2" value="<?=(isset($row['fio2']) ? htmlspecialchars($row['fio2']) : '')?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="peep" class="col-sm-4 text-right col-form-label">PEEP</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="peep" name="peep" value="<?=(isset($row['peep']) ? htmlspecialchars($row['peep']) : '')?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="ft" class="col-sm-4 text-right col-form-label">FT</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="ft" name="ft" value="<?=(isset($row['ft']) ? htmlspecialchars($row['ft']) : '')?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="delta_p" class="col-sm-4 text-right col-form-label">Delta P</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="delta_p" name="delta_p" value="<?=(isset($row['delta_p']) ? htmlspecialchars($row['delta_p']) : '')?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="o2_map" class="col-sm-4 text-right col-form-label">MAP</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="o2_map" name="o2_map" value="<?=(isset($row['o2_map']) ? htmlspecialchars($row['o2_map']) : '')?>">
                                </div>
                            </div>
                        </div>
                        <!-- <div class="tab-pane fade" id="nav-io" role="tabpanel" aria-labelledby="nav-io-tab">
                            <div class="form-group row">
                                <label for="intake_id" class="col-sm-4 text-right col-form-label">Intake</label>
                                <div class="col-sm-6">
                                    <select class="form-control" id="intake_id" name="intake_id">
                                        <option value=""></option>
                                        <?=SelectUtils::getIntakeSelectOption((isset($row['intake_id']) ? $row['intake_id'] : null))?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="intake_type" class="col-sm-4 text-right col-form-label">Intake Type</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="intake_type" name="intake_type" value="<?=(isset($row['intake_type']) ? htmlspecialchars($row['intake_type']) : '')?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="intake_amount" class="col-sm-4 text-right col-form-label">Intake Amount</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="intake_amount" name="intake_amount" value="<?=(isset($row['intake_amount']) ? htmlspecialchars($row['intake_amount']) : '')?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="intake_absorb" class="col-sm-4 text-right col-form-label">Intake Absorb</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="intake_absorb" name="intake_absorb" value="<?=(isset($row['intake_absorb']) ? htmlspecialchars($row['intake_absorb']) : '')?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="output_id" class="col-sm-4 text-right col-form-label">Output</label>
                                <div class="col-sm-6">
                                    <select class="form-control" id="output_id" name="output_id">
                                        <option value=""></option>
                                        <?=SelectUtils::getOutputSelectOption((isset($row['output_id']) ? $row['output_id'] : null))?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="output_amount" class="col-sm-4 text-right col-form-label">Output Amount</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="output_amount" name="output_amount" value="<?=(isset($row['output_amount']) ? htmlspecialchars($row['output_amount']) : '')?>">
                                </div>
                            </div>
                        </div> -->
                        <div class="tab-pane fade" id="nav-lr" role="tabpanel" aria-labelledby="nav-lr-tab">
                            <div class="form-group row">
                                <label for="bw" class="col-sm-4 text-right col-form-label">Interval</label>
                                <div class="col-sm-7">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="lr_int" name="lr_int" value="<?=(isset($row['lr_int']) ? htmlspecialchars($row['lr_int']) : '')?>">
                                        <div class="input-group-append">
                                            <span class="input-group-text">min</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="bw" class="col-sm-4 text-right col-form-label">Duration</label>
                                <div class="col-sm-7">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="lr_dur" name="lr_dur" value="<?=(isset($row['lr_dur']) ? htmlspecialchars($row['lr_dur']) : '')?>">
                                        <div class="input-group-append">
                                            <span class="input-group-text">sec</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="bw" class="col-sm-4 text-right col-form-label">Fetal Heart Sound</label>
                                <div class="col-sm-7">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="lr_fsh" name="lr_fsh" value="<?=(isset($row['lr_fsh']) ? htmlspecialchars($row['lr_fsh']) : '')?>">
                                        <div class="input-group-append">
                                            <span class="input-group-text">beat/min</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="bw" class="col-sm-4 text-right col-form-label">Severity</label>
                                <div class="col-sm-7">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="lr_sev" name="lr_sev" value="<?=(isset($row['lr_sev']) ? htmlspecialchars($row['lr_sev']) : '')?>">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="bw" class="col-sm-4 text-right col-form-label">Cervix</label>
                                <div class="col-sm-7">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="lr_cer" name="lr_cer" value="<?=(isset($row['lr_cer']) ? htmlspecialchars($row['lr_cer']) : '')?>">
                                        <div class="input-group-append">
                                            <span class="input-group-text">cm.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="bw" class="col-sm-4 text-right col-form-label">Effacement</label>
                                <div class="col-sm-7">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="lr_eff" name="lr_eff" value="<?=(isset($row['lr_eff']) ? htmlspecialchars($row['lr_eff']) : '')?>">
                                        <div class="input-group-append">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="o2_map" class="col-sm-4 text-right col-form-label">Station</label>
                                <div class="col-sm-7">
                                    <select class="form-control" name="lr_sta" id="lr_sta">
                                        <option value=""></option>
                                        <?=SelectUtils::getLRstaSelectOption((isset($row['lr_sta']) ? $row['lr_sta'] : null))?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="o2_map" class="col-sm-4 text-right col-form-label">Membrane</label>
                                <div class="col-sm-7">
                                    <select class="form-control" name="lr_mem" id="lr_mem">
                                        <option value=""></option>
                                        <?=SelectUtils::getLRmemSelectOption((isset($row['lr_mem']) ? $row['lr_mem'] : null))?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="o2_map" class="col-sm-4 text-right col-form-label">ลักษณะ Membrane</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control" id="lr_af" name="lr_af" value="<?=(isset($row['lr_af']) ? htmlspecialchars($row['lr_af']) : '')?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" onclick="onclickVitalSignFormSaveButton(event)"><i class="fas fa-save"></i> บันทึก</button>
                    <?php if($data_mode == 'U'){ ?>
                    <button type="button" class="btn btn-secondary" onclick="onclickVitalSignFormNewButton(event)"><i class="fas fa-times"></i> ยกเลิก</button>
                    
                   <!-- <button type="button" class="btn btn-danger float-right" onclick="onclickVitalSignFormDeleteButton(event)"><i class="fas fa-trash"></i> ลบ</button> -->
                    
                    <?php } ?>
                </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function onclick_map_calculate_button(event){
        var dbp = $('#dbp').val();
        var sbp = $('#sbp').val();
        var map = roundNumber((((parseFloat(dbp,10)*2)+parseFloat(sbp,10))/3),0);
        $('#map').val(Number.isNaN(map) ? '':map);
    }
    function onclickVitalSignFormSaveButton(event){
        event.preventDefault();
        saveDataAndReload();
    }
    function onclickVitalSignFormDeleteButton(event){
        if(confirm('ยืนยันการลบข้อมูล')){
            $('#data_mode').val('D');
            saveDataAndReload();
        }
    }
    function onclickVitalSignFormNewButton(event){
        var an = $('#vs_an').val();
        reload(an);
    }
    function reload(an){
        $("#show-vital-sign-form").html('');
        $.get("ipd-vital-sign-show-form.php",{an,'data_mode':'I'},function(data_vital_sign){
            $("#show-vital-sign-form").html(data_vital_sign);
        });

        $("#ipd-show-patient-main").html('');
        var url="ipd-show-patient-main.php";
        $.get(url,{an},function(data){
            $("#ipd-show-patient-main").html(data);
        });
    }

    function saveDataAndReload(){
        if($("#vs_date").val() != '' && $("#vs_time").val() != ''){
            // if($("#vs_date").val()){

            // }
            $.post("ipd-vital-sign-save.php",$("#vital-sign-form").serialize(),function(html){
                var an = $('#vs_an').val();
                $("#show-chart-table").html('');
                var url="ipd-vital-sign-show-chart.php";
                $.get(url,{an},function(data){
                    $("#show-chart-table").html(data);
                    var url="ipd-vital-sign-show-table.php";
                    $.get(url,{an},function(data){
                        $("#show-chart-table").append(data);
                    });
                });
                reload(an);
            });
        } else {
            alert('กรุณากรอกเวลาและวันที่ให้ครบถ้วน');
        }
    }
    $(document).ready(function() {
        if($('#data_mode').val() == 'I'){
            var now = moment();
            document.getElementById("vs_date").value = now.format("YYYY-MM-DD");
            // document.getElementById("vs_time").value = now.format("HH:mm");
        }
        oninputVitalSignValue();
    });
    function display_score(score, score_display_id){
        if(score === "" || score === null) {
            $('#'+score_display_id).html("");
        }else{
            if(score != null){
                let MEWS_COLOR = ['#45c351','#e6b728','#e8832a','#e51616'];
                $('#'+score_display_id).html("<div class='badge text-white mt-1 font-weight-bold' style='font-size:120%; background-color: " + MEWS_COLOR[score] + ";'>" + score + "</div>");
            }
        }
    }
    function display_score_total(score, score_display_id){
        if(score === "" || score === null) {
            $('#'+score_display_id).html("");
        }else{
            color = 'inherit';
            if(score === 0){
                color = '#45c351';
            }else if(score > 0 && score <= 3){
                color = '#e6b728';
            }else if(score >= 4){
                color = '#e51616';
            }
            $('#'+score_display_id).html("<div class='alert text-white text-center font-weight-bold' style='font-size:100%;  background-color: " + color + ";'> MEWS SCORE : " + score + "</div>");
        }
    }
    function oninputVitalSignValue(){
        let age_y = <?=json_encode(KphisQueryUtils::checkPatienAge($an))?>;
        let bt = score_bt(age_y, $("#bt").val());
        let pr = score_pr(age_y, $("#pr").val());
        let rr = score_rr(age_y, $("#rr").val(), $('#respirator:checked').val());
        let sbp = score_sbp(age_y, $("#sbp").val(), $('#inotrope:checked').val());
        let conscious_id = score_conscious_id(age_y, $("#conscious_id").val());
        let urine = score_urine(age_y, $("#urine_amount").val(), $("#urine_duration").val());
        let total = score_total(age_y, bt, pr, rr, sbp, conscious_id, urine);

        display_score(bt, "score_bt_result");
        display_score(pr, "score_pr_result");
        display_score(rr, "score_rr_result");
        display_score(sbp, "score_sbp_result");
        display_score(conscious_id, "score_conscious_id_result");
        display_score(urine, "score_urine_result");

        display_score_total(total, "score_total_result");
    }
    function onchange_catheter(){
        let catheter = $('#catheter:checked').val();
        if(catheter){
            $("#urine").val('ใส่สายสวนฯ');
        }
    }
</script>