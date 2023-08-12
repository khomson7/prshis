function score_bt(age_y,bt){
    if(age_y >= 15){
        bt = parseFloat(bt);
        if(bt == null){
        return null;
        } else if(bt <= 35){
            return 2;
        } else if(bt <= 36){
            return 1;
        } else if(bt <= 38){
            return 0;
        } else if(bt < 38.5){
            return 1;
        } else if(bt >= 38.5){
            return 2;
        } else {
            return null;
        }
    } else {
        return null;
    }
}
function score_pr(age_y,pr){
    if(age_y >= 15){
        pr = parseInt(pr,10);
        if(pr == null){
            return null;
        } else if(pr <= 40){
            return 3;
        } else if(pr <= 50){
            return 1;
        } else if(pr <= 100){
            return 0;
        } else if(pr <= 120){
            return 1;
        } else if(pr < 140){
            return 2;
        } else if(pr >= 140){
            return 3;
        } else {
            return null;
        }
    } else {
        return null;
    }
}
function score_rr(age_y,rr,respirator){
    if(age_y >= 15){
        rr = parseInt(rr,10);
        if(respirator){
            return 2;
        } else {
            if(rr == null){
                return null;
            } else if(rr <= 8){
                return 3;
            } else if(rr <= 20){
                return 0;
            } else if(rr <= 25){
                return 1;
            } else if(rr <= 35){
                return 2;
            } else if(rr > 35){
                return 3;
            } else {
                return null;
            }
        }
    } else {
        return null;
    }
}
function score_sbp(age_y,sbp,inotrope){
    if(age_y >= 15){
        sbp = parseInt(sbp,10);
        if(inotrope){
            return 3;
        } else {
            if(sbp == null){
                return null;
            } else if(sbp <= 80){
                return 3;
            } else if(sbp <= 90){
                return 2;
            } else if(sbp <= 100){
                return 1;
            } else if(sbp <= 180){
                return 0;
            } else if(sbp < 200){
                return 1;
            } else if(sbp >= 200){
                return 2;
            } else {
                return null;
            }
        }
    } else {
        return null;
    }
}
function score_conscious_id(age_y,conscious_id){
    if(age_y >= 15){
        if(conscious_id == null){
            return null;
        } else if(conscious_id == '1'){
            return 0;
        } else if(conscious_id == '2'){
            return 1;
        } else if(conscious_id == '3'){
            return 1;
        } else if(conscious_id == '4'){
            return 1;
        } else if(conscious_id == '5'){
            return 2;
        } else if(conscious_id == '6'){
            return 3;
        } else {
            return null;
        }
    } else {
        return null;
    }
}
function score_urine(age_y,urine_amount, urine_duration){
    if(age_y >= 15){
        if((urine_amount == null || urine_amount == "") || (urine_duration == null || urine_duration == "")){
            return null;
        }else if(urine_amount == '1'){
            if(urine_duration == '1'){
                return 0;
            }else if(urine_duration == '2'){
                return 0;
            }else if(urine_duration == '3'){
                return 0;
            }else if(urine_duration == '4'){
                return 0;
            }
        }else if(urine_amount == '2'){
            if(urine_duration == '1'){
                return 1;
            }else if(urine_duration == '2'){
                return 0;
            }else if(urine_duration == '3'){
                return 0;
            }else if(urine_duration == '4'){
                return 0;
            }
        }else if(urine_amount == '3'){
            if(urine_duration == '1'){
                return 2;
            }else if(urine_duration == '2'){
                return 1;
            }else if(urine_duration == '3'){
                return 0;
            }else if(urine_duration == '4'){
                return 0;
            }
        }else if(urine_amount == '4'){
            if(urine_duration == '1'){
                return 2;
            }else if(urine_duration == '2'){
                return 2;
            }else if(urine_duration == '3'){
                return 1;
            }else if(urine_duration == '4'){
                return 0;
            }
        }else if(urine_amount == '5'){
            if(urine_duration == '1'){
                return 2;
            }else if(urine_duration == '2'){
                return 2;
            }else if(urine_duration == '3'){
                return 2;
            }else if(urine_duration == '4'){
                return 0;
            }
        }else if(urine_amount == '6'){
            if(urine_duration == '1'){
                return 2;
            }else if(urine_duration == '2'){
                return 2;
            }else if(urine_duration == '3'){
                return 2;
            }else if(urine_duration == '4'){
                return 1;
            }
        }else if(urine_amount == '7'){
            if(urine_duration == '1'){
                return 2;
            }else if(urine_duration == '2'){
                return 2;
            }else if(urine_duration == '3'){
                return 2;
            }else if(urine_duration == '4'){
                return 2;
            }
        }
    } else {
        return null;
    }
}
function score_total(age_y,bt, pr, rr, sbp, conscious_id, urine){
    if((bt === null) && (pr === null) && (rr === null) && (sbp === null) && (conscious_id === null) && (urine === null)){
        return null;
    }else{
        bt = parseFloat(bt);
        pr = parseFloat(pr);
        rr = parseFloat(rr);
        sbp = parseFloat(sbp);
        conscious_id = parseFloat(conscious_id);
        urine = parseFloat(urine);
        if(isNaN(bt)){bt = 0;}
        if(isNaN(pr)){pr = 0;}
        if(isNaN(rr)){rr = 0;}
        if(isNaN(sbp)){sbp = 0;}
        if(isNaN(conscious_id)){conscious_id = 0;}
        if(isNaN(urine)){urine = 0;}
        let total = bt+pr+rr+sbp+conscious_id+urine;
        return total;
    }
}
function mews(age_y, input_bt, input_pr, input_rr, input_respirator, input_sbp, input_inotrope, input_conscious_id, input_urine_amount, input_urine_duration) {
    let bt = score_bt(age_y,input_bt);
    let pr = score_pr(age_y,input_pr);
    let rr = score_rr(age_y,input_rr, (input_respirator === "Y"));
    let sbp = score_sbp(age_y,input_sbp, (input_inotrope === "Y"));
    let conscious_id = score_conscious_id(age_y,input_conscious_id);
    let urine = score_urine(age_y,input_urine_amount, input_urine_duration);
    let total = score_total(age_y,bt, pr, rr, sbp, conscious_id, urine);
    return total;
}