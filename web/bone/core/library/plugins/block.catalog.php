<?php
/**
 * 类目列表获取
 * 属性 cmid 必须指定
 * <{catalog cmid=1 parent=0 item='_v' key='_k' }>
 *    <{$_v.name}>--<{$_v.cid}>...<br />
 * <{/catalog}>
 * @return array
 */
function tpl_block_catalog(&$tpl, $atts)
{
    $cmid   = !isset( $atts['cmid'] ) ? '' : preg_replace("/[^\w]/", '', $atts['cmid'] );
    $parent = !isset( $atts['parent'] ) ? -1 : intval( $atts['parent'] );
    if( empty($cmid) ) return array();
    return mod_catalog::get_catalogs( $cmid, 'ems', $parent );
}
