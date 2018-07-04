<?php
if( !defined('PHPBONE') ) exit('Request Error!');
/**
 * 配置文件类
 *
 * @version $Id$
 *
 */
class config
{
    public static $cfg_groups  = '';
    public static $bone_configs   = array();
    public static $cfg_cache_prefix   = 'acc_cfg';
    public static $cfg_cache_key   = 'configs';
   
   /**
    * 获得配置
    * @parem string $key 为空时返回所有key-value对(不含详细描述) 
    * @return mix
    */
    public static function get( $key='' )
    {
        if( empty(self::$bone_configs) )
        {
            //检查缓存
            self::$bone_configs = cache::get(self::$cfg_cache_prefix, self::$cfg_cache_key);
            if( self::$bone_configs === false )
            {
                $rs = db::query("Select * From `#PB#_config` ");
                if( $rs )
                {
                    while( $row = db::fetch_one($rs) )
                    {
                        self::$bone_configs[ $row['name'] ] = $row['value'];
                    }
                    cache::set(self::$cfg_cache_prefix, self::$cfg_cache_key, self::$bone_configs, 0);
                }
                else
                {
                    return '';
                }
            }
        }
        if( $key=='' ) {
            return self::$bone_configs;
        } else {
            return isset(self::$bone_configs[$key]) ? self::$bone_configs[$key] : '';
        }
    }
    
   /**
    * 保存某项配置的值
    * @parem string $key  项
    * @parem mix $value 值(为字符串时只修改value, 为数组时修改所有相关值)
    * @return bool
    */
    public static function save( $key, $value, $upcache = true )
    {
        $tb = cls_lurd::factory( '#PB#_config' );
        $tb->set_safe_check(0);
        if( !is_array($value) ) {
            $data['value'] = $value;
        } else {
            $data = $value;
        }
        $data['name'] = $key;
        $rs = $tb->update( $data );
        if( $upcache ) {
            self::$bone_configs[$key] = stripslashes( $data['value'] );
            cache::set(self::$cfg_cache_prefix, self::$cfg_cache_key, self::$bone_configs, 0);
        }
        return $rs;
    }

   /**
    * 获得所有配置数组(包含详细信息)
    * @return array()
    */
    public static function get_all( $group_id = 0 )
    {
        $rows = array();
        $wheresql = '';
        if( $group_id > 0 ) {
            $wheresql = " where `group_id` = '{$group_id}' ";
        }
        $rs = db::query("Select * From `#PB#_config` {$wheresql} order by `sort_id` desc ");
        while( $row = db::fetch_one($rs) )
        {
            $rows[ $row['name'] ] = $row;
        }
        return $rows;
    }

    /**
     * 重载全部系统变量
     * @return [type] [description]
     */
    public static function reload( )
    {
        $rs = db::query("Select * From `#PB#_config` ");
        if( $rs )
        {
            while( $row = db::fetch_one($rs) )
            {
                self::$bone_configs[ $row['name'] ] = $row['value'];
            }
            cache::set(self::$cfg_cache_prefix, self::$cfg_cache_key, self::$bone_configs, 0);
        }
        return true;
    }


}


