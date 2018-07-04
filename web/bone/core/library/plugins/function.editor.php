<?php
/****************************
 * 获取一个可视化编辑器
 ****************************/
function tpl_function_editor(&$tpl, $atts)
{
    $fname    = isset($atts['fieldname']) ? $atts['fieldname'] : '';
    $toolbar  = isset($atts['toolbar']) ? $atts['toolbar'] : 'Base';
    $width    = isset($atts['width']) ? $atts['width'] : '100%';
    $height   = isset($atts['height']) ? $atts['height'] : '350';
    $dfvalue  = isset($atts['value']) ? $atts['value'] : '';
    return pub_field_tool::get_editor($fname, $dfvalue, $toolbar, $height, $width);
}
