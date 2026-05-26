<?php
// Helper functions for pre-ane-assess form rendering
function paa_cb($name, $label, $id=null) {
    $id = $id ?: $name;
    return '<div class="custom-control custom-checkbox d-inline-block mr-3">
        <input type="checkbox" class="custom-control-input" id="'.$id.'" name="'.$name.'" value="1">
        <label class="custom-control-label" for="'.$id.'">'.$label.'</label></div>';
}
function paa_radio($name, $value, $label, $id=null) {
    $id = $id ?: $name.'_'.preg_replace('/[^a-z0-9]/i','_',$value);
    return '<div class="custom-control custom-radio d-inline-block mr-3">
        <input type="radio" class="custom-control-input" id="'.$id.'" name="'.$name.'" value="'.$value.'">
        <label class="custom-control-label" for="'.$id.'">'.$label.'</label></div>';
}
function paa_text($name, $ph='', $cls='form-control-sm', $w='') {
    $s = $w ? 'style="width:'.$w.'"' : '';
    return '<input type="text" class="form-control '.$cls.'" name="'.$name.'" id="'.$name.'" placeholder="'.$ph.'" '.$s.'>';
}
function paa_ta($name, $rows=2) {
    return '<textarea class="form-control form-control-sm" name="'.$name.'" id="'.$name.'" rows="'.$rows.'"></textarea>';
}
function paa_select($name, $options, $cls='form-control-sm', $w='') {
    $s = $w ? 'style="width:'.$w.'"' : '';
    $html = '<select name="'.$name.'" id="'.$name.'" class="form-control '.$cls.'" '.$s.'>';
    $html .= '<option value="">- เลือก -</option>';
    foreach ($options as $val => $text) {
        $html .= '<option value="'.htmlspecialchars($val).'">'.htmlspecialchars($text).'</option>';
    }
    $html .= '</select>';
    return $html;
}
function paa_date($name, $cls='form-control-sm', $w='') {
    $s = $w ? 'style="width:'.$w.'"' : '';
    return '<input type="date" class="form-control '.$cls.'" name="'.$name.'" id="'.$name.'" '.$s.'>';
}
