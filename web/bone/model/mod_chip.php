<?php
/**
 * chip标签模型
 * 管理操作基本由lurd控制，这里主要是进行读取和缓存处理
 *
 * $Id$ 
 */
class mod_chip
{
    protected static $_mod_table = 'bone_chip';
    
    /**
     * 读取chip数据
     */
     public static function get_chip( $chipname )
     {
        $data = cache::get(self::$_mod_table, $chipname);
        if( $data === false )
        {
            $sql  = " SELECT `data`,`template`,`isarray`  FROM `".self::$_mod_table."` WHERE `name`='{$chipname}' ";
            $data =  db::get_one($sql);
            if( !is_array($data) ) {
                cache::set(self::$_mod_table, $chipname, ':empty:', 3600);
                return ':empty:';
            }
            //处理列表数据
            if($data['isarray']==1) {
                $_data = json_decode(db::revert($data['data']), true);
                $data['data'] = self::_build_chip_list( $_data, $data['template'], $chipname );
            }
            cache::set(self::$_mod_table, $chipname, $data['data'], 3600);
            $data = $data['data'];
        }
        return $data;
     }
     
    /**
     * 获取 chip 列表数据
     * @param $data
     * @return string
     */
     public static function _build_chip_list( &$data, $template, $chipname )
     {
         $varname = 'chip_'.md5($chipname);
         tpl::assign($varname, $data);
         tpl::assign('chipname', $chipname);
         
         //生成临时模板(模板路径必须相对于PATH_TEMPLATE . '/template/')
         $tmp_file  = "../compile/{$varname}.tpl";
         $template = str_replace('{/loop', '{/foreach', str_replace('{loop','{foreach from=$'.$varname.' ', $template));
         file_put_contents(PATH_TEMPLATE.'/template/'.$tmp_file, $template);
         
         //chip模板里不能含有include语法
         $app_tmp_name = bone::$instance->app_name;
         bone::$instance->app_name = '';
         $content = tpl::fetch( $tmp_file );
         bone::$instance->app_name = $app_tmp_name;
         
         return $content;
     }
     
    /**
     * 删除缓存
     */
     public static function del_chip_cacahe( $chipname )
     {
         cache::del(self::$_mod_table, $chipname);
     }
     
     public static function test()
     {
        return 'test';
     }

}