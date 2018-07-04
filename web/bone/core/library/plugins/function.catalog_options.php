<?php
/**********************************
 * 类目录的select 表单的 option 项
 **********************************/
function tpl_function_catalog_options(&$tpl, $atts)
{
    $cmid    = isset($atts['cmid']) ? preg_replace("/[^\w]/", '', $atts['cmid'] ) : '';
    $selid  = isset($atts['selid']) ? $atts['selid'] : 0;
    $cats  = isset($atts['cats']) ? $atts['cats'] : array();
    $dfname  = isset($atts['dfname']) ? $atts['dfname'] : '请选择分类';
    if( empty($cmid) ) return '';
    if( empty($cats) ) {
        $cats = mod_catalog::get_catalogs( $cmid );
    }
    $optionstr = '';
    _tpl_catalog_options_recursion( $cats, $optionstr, '', $selid );
    $sel = $selid==0 ? ' selected' : '';
    $optionstr = "<option value='0' {$sel}>{$dfname}</option>\r\n".$optionstr;
    return $optionstr;
}

//附加递归函数
function _tpl_catalog_options_recursion( $cats, &$restr, $addstr='', $selid = 0 )
{
    foreach( $cats as $cid => $r) {
        $sel = $selid==$cid ? ' selected' : '';
        $restr .= "<option value='{$cid}' {$sel}>{$addstr}{$r['d']['cname']}</option>\r\n";
        if( !empty($r['s']) ) _tpl_catalog_options_recursion( $r['s'], $restr, $addstr.'--', $selid );
    }
}