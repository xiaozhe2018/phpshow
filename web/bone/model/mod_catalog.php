<?php
if( !defined('PHPBONE') ) exit('Request Error!');
/**
 * 分类数据管理
 * @version $Id$
 */
class mod_catalog
{
    //是否使用缓存
    public static $is_cache = true;
    
    //分类模型信息
    public static $models = array();
    
    //分类表前缀
    public static $table_base = 'bone_catalog_';
    
    /**
     * 获取缓存prefix
     * @return string
     */
    protected static function _get_prefix()
    {
        return (empty($GLOBALS['config']['cache']['df_prefix']) ? __CLASS__ : $GLOBALS['config']['cache']['df_prefix'].__CLASS__);
    }
    
    /**
     * 获取分类结构表(只返回树状数据，不包含类目的具体信息)
     * @param $cmid
     * @param $type  tree | ems  数据类型（树结构或普通元素列表）
     * @param $parent 父栏目id(只适用于ems类型数据)
     * @return array
     */
    public static function get_catalogs( $cmid, $type='tree', $parent=-1 )
    {
        $key = ($type=='tree' ? 'catalogs_'.$cmid : 'catalog_ems_'.$cmid);
        $data = cache::get(self::_get_prefix(), $key);
        if( $data !== false )
        {
            if( $type=='ems' && $parent != -1 ) {
                return self::_get_son_note($data, $parent);
            } else {
               return $data;
            }
        }
        $model_info = self::get_models( $cmid );
        if( empty($model_info) ) {
            return false;
        }
        $catalogs = self::_get_all_catalogs( $cmid );
        if( !empty($catalogs) )
        {
            $tree = self::_tree_catalogs($catalogs[1], 0);
            cache::set(self::_get_prefix(), 'catalogs_'.$cmid, $tree);
            cache::set(self::_get_prefix(), 'catalog_ems_'.$cmid, $catalogs[0]);
            if( $type=='tree')
            {
                return $tree;
            } else {
                return ($parent==-1 ? $catalogs[0] : self::_get_son_note($catalogs[0], $parent));
            }
        }
        return array();
    }
    
   /**
    * 获取单个分类数据
    * @return array
    */
    public static function get_one( $cmid, $pri_key )
    {
        $cats = self::get_catalogs( $cmid, 'ems', -1);
        return isset( $cats[$pri_key] ) ? $cats[$pri_key] : array();
    }
    
   /**
    * 获取子节点
    */
    private static function _get_son_note(&$catalogs, $parent)
    {
        $data = array();
        foreach($catalogs as $cid => $v) {
            if( $v['pid'] == $parent ) {
                $data[ $cid ] = $v;
            }
        }
        return $data;
    }
    
    /**
     * 获取指定id的分类名
     * @param $cmid
     * @param $cid
     * @return string
     */
    public static function get_name( $cmid, $cid )
    {
        $catalogs = self::get_catalogs($cmid, 'ems');
        if( isset($catalogs[$cid]) )  return $catalogs[$cid]['cname'];
        else return '';
    }
    
    /**
     * 获取某个分类的所有子元素id（包含当前元素）
     * @param $cmid
     * @param $cid
     * @return array
     */
    public static function get_son_catids( $cmid, $cid )
    {
        $ids  = array();
        $node = self::get_son_node( $cmid, $cid );
        self::_search_cat_id( $node, $ids );
        return $ids;
    }
    
    /**
     * 获取某个分类的子元素树
     * @param $cmid
     * @param $cid
     * @return array
     */
    public static function get_son_node( $cmid, $cid )
    {
        $node = array();
        $cats = self::get_catalogs( $cmid );
        self::_search_cat_node( $cid, $cats, $node );
        return $node;
    }
    
    /**
     * 获取子类所在节点
     * @param $cid
     * @param $cats  分类结构表
     * @param &$node  回传数据
     * @return array
     */
    private static function _search_cat_node( $cid, $cats, &$node )
    {
        if( !isset($cats[$cid]) )
        {
            foreach($cats as $_cid => $r) {
                self::_search_cat_node( $cid, $r['s'], $node );
            }
        } else {
            //返回节点数据要包含自身，所以用 $node[$cid] 表示当前节点
            $node[$cid] = $cats[$cid];
        }
    }
    
    /**
     * 获取子指定节点所有子类的id
     * @param $cid
     * @param $node  节点数组
     * @param &$ids  回传数据
     * @return array
     */
    private static function _search_cat_id( $node, &$ids )
    {
        foreach($node as $cid => $r)
        {
            $ids[] = $cid;
            if( !empty($r['s']) )  self::_search_cat_id( $r['s'], $ids );
        }
    }
    
    /**
     * 获取某模型的所有分类数据
     * @param $cmid
     * @return bool
     */
    private static function _get_all_catalogs( $cmid )
    {
        $catalogs = array();
        $model_info = self::get_models( $cmid );
        if( empty($model_info) ) return false;
        $arr = array();
        $model_table = self::$table_base.( empty($model_info['cmtable']) ? 'base' : $model_info['cmtable'] );
        $rs = db::query("Select * From `{$model_table}` where `cmid` = '{$cmid}' order by `sortrank` desc, `cid` desc ");
        while( $row = db::fetch_one($rs) ) {
            $catalogs[0][$row['cid']] = $row;
            $catalogs[1][$row['cid']] = array('cname' => $row['cname'], 'pid' => $row['pid'], 'sortrank' => $row['sortrank'] );
        }
        return $catalogs;
    }
    
    
   /**
    *  删除某个分类(要一起删除下级分类)
    *  @param array $cids
    *  @param $cmid
    *  @return bool
    */
    public static function catalog_del( $cids, $cmid )
    {
        $model_info = self::get_models( $cmid );
        if( empty($model_info) ) return false;
        $model_table = self::$table_base.( empty($model_info['cmtable']) ? 'base' : $model_info['cmtable'] );
        $ids = array();
        foreach($cids as $cid) {
            $_ids = self::get_son_catids( $cmid, $cid );
            $ids = array_merge($ids, $_ids);
        }
        $ids = array_unique( $ids );
        $idstr = join(',', $ids);
        if( $idstr != '' ) {
            db::query("Delete From `{$model_table}` where `cid` in({$idstr}); ");
            self::model_cache_del();
        }
        return true;
    }
    
    /**
     * 整理分类结构
     * @param $cmid
     * @return bool
     */
    private static function _tree_catalogs(&$catalogs, $pid = 0 )
    {
        $rearr = array();
        foreach($catalogs as $cid => $_r) {
            if( $_r['pid'] == $pid )  {
                $rearr[$cid]['d'] = $_r;
                $rearr[$cid]['s'] = self::_tree_catalogs($catalogs, $cid);
            }
        }
        return $rearr;
    }
    
    /**
     * 删除分类模型数据缓存
     * @return void
     */
    public static function model_cache_del()
    {
        self::$models = array();
        cache::del(self::_get_prefix(), 'models');
    }
    
    /**
     * 删除分类数据缓存
     * @return void
     */
    public static function catalog_cache_del( $cmid )
    {
        cache::del(self::_get_prefix(), 'catalogs_'.$cmid);
    }
    
    /**
     * 删除分类模型
     * @param $cmid
     * @return bool
     */
    public static function model_del( $cmid )
    {
        $model_info = self::get_models( $cmid );
        if( empty($model_info) ) return false;
        $table = self::$table_base.( empty($model_info['cmtable']) ? 'base' : $model_info['cmtable'] );
        db::query(" Delete From `{$table}` where `cmid` = '{$cmid}' ");
        return true;
    }
    
    /**
     * 创建分类表
     * @param $tbname  表名（不含self::$table_base前缀）
     * @return bool
     */
    public static function create_catalog_table( $tbname )
    {
        if( $tbname == 'base' ) {
            return true;
        }
        $table = self::$table_base.$tbname;
        $basetable = self::$table_base.'base';
        $rs  = db::query("SHOW CREATE TABLE `{$basetable}`;", false);
        $row = db::fetch_one($rs, DB_GET_NUM);
        $tbinfo = $row[1];
        $tbinfo = preg_replace("/DEFAULT CHARSET=utf8(.*)$/", " DEFAULT CHARSET=utf8 COMMENT='分类数据表'; ", $tbinfo);
        $tbinfo = preg_replace("/AUTO_INCREMENT=[0-9]{1,}/", '', $tbinfo);
        $tbinfo = str_replace("`{$basetable}`", " IF NOT EXISTS `{$table}`", $tbinfo);
        return db::query( $tbinfo );
    }
    
    /**
     * 加载全部分类模型信息
     * @param $cmid  模型id(为空时返回全部)
     * @return array
     */
    public static function get_models( $cmid = '' )
    {
        if( empty(self::$models) )
        {
            $models = cache::get(self::_get_prefix(), 'models');
            if( $models===false )
            {
                $models = db::get_all("Select * From `bone_catalog_model` order by `sortrank` desc, `cmid` desc ", 'cmid');
                if( !empty($models) ) {
                    cache::set(self::_get_prefix(), 'models', $models);
                }
            }
            self::$models = $models;
        }
        if( $cmid=='' ) {
            return self::$models;
        } else {
            return ( isset(self::$models[$cmid]) ? self::$models[$cmid] : array() );
        }
    }
    
    /**
     * 获取分类模型菜单xml
     * @return string
     */
    public static function get_catalog_menu( )
    {
        $models = self::get_models( );
        $restr = '';
        foreach($models as $model)
        {
            if( $model['showmenu'] == 0 ) continue;
            $adm_url = ($model['adm_url']=='' ? " url='?ct=catalog&amp;cmid={$model['cmid']}' " : " url='{$model['adm_url']}&amp;cmid={$model['cmid']}' " );
            $restr .= "<item name='{$model['cmname']}分类' {$adm_url} ct='catalog' ac='index' />\r\n";
        }
        return $restr;
    }
    
    /**
     * 获取某个分类上级分类列表（包含当前分类）
     * @param $cmid
     * @param $cid
     * @return array
     */
    public static function get_parent_catalogs( $cmid, $cid )
    {
        $rearr = array();
        $catalogs = self::get_catalogs( $cmid, 'ems' );
        $cur_node = self::get_one( $cmid, $cid );
        $rearr[] = $cur_node;
        if( $cur_node['pid'] > 0 ) {
            self::_get_parent_catalog( $catalogs, $rearr, $cur_node );
        }
        return $rearr;
    }
    
   /**
    *  递归获取父分类
    */
    private static function _get_parent_catalog( &$catalogs, &$rearr, $cur_node )
    {
        if( isset($catalogs[$cur_node['pid']]) )
        {
            $rearr[] = $catalogs[$cur_node['pid']];
            if( $catalogs[$cur_node['pid']]['pid'] > 0 ) {
                self::_get_parent_catalog( $catalogs, $rearr, $catalogs[$cur_node['pid']] );
            }
        }
    }
    
}