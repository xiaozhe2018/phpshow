<?php
/****************************
 * 获取块元素
 ****************************/
function tpl_function_chip(&$tpl, $atts)
{
    $chipname = $atts['name'];
    if( empty($chipname) ) {
        return '';
    }
    $data = mod_chip::get_chip( $chipname );
    if( $data == ':empty:' ||  $data === false ) {
        return '';
    }
    return  $data;
}

