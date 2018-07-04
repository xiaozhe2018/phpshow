<?php
/**
  * 友情链接调用
  * @param &$tpl 引用的模板类对象实例
  * @parem $atts 传递的属性参数
  * @return string
  * 模板示例
  * <{friendlink position='2' limit='10' item='_v'}>
  * <a href='<{$v.url}>' target='_blank'><{$_v.webname}></a> 
  * <{/friendlink}>
  */
function tpl_block_friendlink(&$tpl, $atts)
{
    $position = isset($atts['position']) ? $atts['position'] : '2';
    $limit    = isset($atts['limit']) ? $atts['limit'] : '20';
    $key      = 'tpl_block_flink_'.join('_', $atts);
    $data = cache::get($GLOBALS['config']['cache']['df_prefix'], $key);
    if( $data===false )
    {
        $rs = db::query(" Select * From `bone_friendlinks` where `position` >= '{$position}' order by `sortrank` desc limit {$limit} ");
        $data = array();
        while($row = db::fetch($rs) ) {
            $data[] = $row;
        }
        cache::set($GLOBALS['config']['cache']['df_prefix'], $key, $data);
    }
    return $data;
}