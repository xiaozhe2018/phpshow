<?php
if( !defined('PHPBONE') ) exit('Request Error!');
/**
 *
 * 分类管理
 *
 * @version $Id$
 */
class ctl_catalog
{
    
    /**
     * 主入口
     */
    public function index()
    {
        $cmid = req::item('cmid', '', 'var');
        $pid = req::item('pid', 0, 'int');
        $cur_modes = mod_catalog::get_models( $cmid );
        if( empty($cur_modes) )
        {
            cls_msgbox::show('系统提示', "找不到分类模型：{$cmid} ！", "-1");
            exit();
        }
        
        //LURD操作
        $catalog_table = mod_catalog::$table_base.$cur_modes['cmtable'];
        $tb = cls_lurd_control::factory( $catalog_table );
        $tb->form_url = "?ct=catalog&cmid={$cmid}";
        $tb->set_order_query(" Order by `sortrank` desc, `cid` desc ");
        $even = req::item('even', 'list');
        
        //当前环境变量
        $infos['models'] = mod_catalog::$models;
        $infos['cur_model'] = $cur_modes;
        $infos['cmid'] = $cmid;
        $infos['pid'] = $pid;
        tpl::assign('infos', $infos);
        if( $even=='list' || $even=='add' || $even=='edit' ) {
            tpl::assign('cats', mod_catalog::get_catalogs( $cmid ));
        }
        
        //不调用lurd的列表数据
        if( $even=='list' ) {
            tpl::display('catalog.index.tpl');
            exit();
        }
        
        $tb->set_tplfiles('catalog.index.tpl', 'catalog.add.tpl', 'catalog.edit.tpl');
        $lurd_hooks = array('saveadd_end' => '_catalog_add',
                            'saveedit_end' => '_catalog_edit',
                            'delete_start' => '_catalog_delete',
                            );
        $tb->bind_hooks($this, $lurd_hooks);
        $tb->listen( req::$forms );
        exit();
    }
    
    //保存增加分类后的处理
    public function _catalog_add($hookname, &$data, $insert_id)
    {
        mod_catalog::catalog_cache_del( $data['cmid'] );
        cls_msgbox::show('系统提示', '成功增加一个分类！', "javascript:parent.tb_remove();");
        exit();
    }
    
    //保存修改分类后的处理
    public function _catalog_edit($hookname, &$data)
    {
        mod_catalog::catalog_cache_del( $data['cmid'] );
        cls_msgbox::show('系统提示', '成功修改一个分类！', "javascript:parent.tb_remove();");
        exit();
    }
    
    //删除分类后的处理
    public function _catalog_delete($hookname, &$ids, &$add_data)
    {
        //自动化操作前，排除不允许删除的模型id，并进行删除附加操作
        if( $hookname=='delete_start' )
        {
            $cmid = intval( $add_data['cmid'] );
            mod_catalog::catalog_del( $ids, $cmid );
            cls_msgbox::show('系统提示', '成功删除指定的分类！', "?ct=catalog&cmid={$cmid}");
            exit();
        }
    }
    
    /**
     * 列出全部下级分类
     */
     public function ajax_load_son()
     {
         $cid  = req::item('cid', 0, 'int');
         $cmid = req::item('cmid', '', 'var');
         $node = mod_catalog::get_son_node( $cmid, $cid );
         if( empty($node) ) {
            return '';
         }
         $restr = '';
         $this->_load_son_tree($node, $restr, 2);
         echo $restr;
         exit();
     }
    
    /**
     * 更新分类排序
     */
     public function catalog_sort()
     {
         $cmid = req::item('cmid', '', 'var');
         $cur_modes = mod_catalog::get_models( $cmid );
         if( empty($cur_modes) ) {
            cls_msgbox::show('系统提示', "找不到分类模型：{$cmid} ！", "-1");
            exit();
         }
         $sortranks = req::item('sortrank');
         $old_sortranks = req::item('sort_old');
         $sorts = array();
         foreach($sortranks as $cid => $rank) {
            if( $old_sortranks[$cid] != $rank )  $sorts[$cid] = $rank;
         }
         $model_table = mod_catalog::$table_base.( empty($cur_modes['cmtable']) ? 'base' : $cur_modes['cmtable'] );
         foreach($sorts as $cid => $rank) {
            $cid = intval( $cid );
            $rank = intval( $rank );
            db::query("Update `{$model_table}` set `sortrank` = '{$rank}' where `cid` = '{$cid}' ");
         }
         mod_catalog::catalog_cache_del( $cmid );
         cls_msgbox::show('系统提示', '成功更新排序！', "?ct=catalog&cmid={$cmid}");
         exit();
     }

    /**
     * 分类模型管理
     */
    public function model()
    {
        $tb = cls_lurd_control::factory('bone_catalog_model');
        $tb->form_url = '?ct=catalog&ac=model';
        $tb->set_order_query(" Order by `sortrank` desc ");
        $even = req::item('even', 'list');
        $keyword = req::item('keyword', '');
        if( $keyword != '' )
        {
            $tb->add_search_condition(" `cmname` like '%{$keyword}%' ");
            $tb->form_url .= '&keyword='.urlencode(stripslashes( $keyword ));
            tpl::assign('keyword', $keyword);
        }
        tpl::assign('tablebase', mod_catalog::$table_base);
        $tb->set_tplfiles('catalog.model.index.tpl', 'catalog.model.add.tpl', 'catalog.model.edit.tpl');
        $lurd_hooks = array('saveadd_start' => '_model_add', 'saveadd_end' => '_model_add',
                            'saveedit_start' => '_model_edit', 'saveedit_end' => '_model_edit',
                            'delete_start' => '_model_delete', 'delete_end' => '_model_delete',
                            );
        $tb->bind_hooks($this, $lurd_hooks);
        $tb->listen( req::$forms );
    }
    
    //保存增加模型数据后的处理
    public function _model_add($hookname, &$data, $insert_id)
    {
        $data['cmtable'] = trim(preg_replace("/[^a-z0-9_]/", '', strtolower($data['cmtable'])));
        if( $data['cmtable']=='' ) $data['cmtable']='base';
        if( $hookname=='saveadd_start' )
        {
            $data['cmname'] = trim($data['cmname']);
            $data['cmid'] = strtolower( trim($data['cmid']) );
            if( preg_match("/[^0-9a-z]/", $data['cmid']) || strlen($data['cmid']) > 12 ) {
                cls_msgbox::show('系统提示', "模型id不合法，请注意提示!", "-1", 2000);
            }
            $models = mod_catalog::get_models( $data['cmid'] );
            if( !empty($models) ) {
                cls_msgbox::show('系统提示', "模型id已经存在，不允许使用!", "-1", 2000);
            }
            if( $data['cmname'] == '' ) {
                cls_msgbox::show('系统提示', "模型名不能为空!", "-1", 2000);
            }
            if( $data['cmtable']=='model' ) {
                cls_msgbox::show('系统提示', "禁止使用表名'{$data['cmtable']}'!", "-1", 2000);
            }
        }
        else
        {
            mod_catalog::create_catalog_table( $data['cmtable'] );
            mod_catalog::model_cache_del();
            cls_msgbox::show('系统提示', '成功增加一个数据模型！', "javascript:parent.tb_remove();");
            exit();
        }
    }
    
    //保存修改数据后的处理
    public function _model_edit($hookname, &$data)
    {
        $data['cmtable'] = trim(preg_replace("/[^a-z0-9_]/", '', strtolower($data['cmtable'])));
        if( $data['cmtable']=='' ) $data['cmtable'] = 'base';
        if( $hookname=='saveedit_start' )
        {
            if( $data['cmtable']=='model' ) {
                cls_msgbox::show('系统提示', "禁止使用表名'{$data['cmtable']}'!", "-1");
                exit();
            }
        }
        else
        {
            if( $data['cmtable'] != $data['old_cmtable'] ) {
                mod_catalog::create_catalog_table( $data['cmtable'] );
            }
            mod_catalog::model_cache_del();
            cls_msgbox::show('系统提示', '成功修改一个数据模型！', "javascript:parent.tb_remove();");
            exit();
        }
    }
    
    //删除模型数据后的处理
    public function _model_delete($hookname, &$data, &$add_data)
    {
        //自动化操作前，排除不允许删除的模型id，并进行删除附加操作
        if( $hookname=='delete_start' )
        {
            $modes = mod_catalog::get_models();
            foreach($data as $k => $cmid)
            {
                if( !mod_catalog::model_del( $cmid ) ) {
                    unset( $data[$k] );
                }
                mod_catalog::catalog_cache_del( $cmid );
            }
        }
        else
        {
            mod_catalog::model_cache_del();
            cls_msgbox::show('系统提示', '成功删除指定的数据模型！', "?ct=catalog&ac=model");
            exit();
        }
    }
    
    /**
     * 更新模型排序
     */
     public function model_sort()
     {
         $sortranks = req::item('sortranks');
         $old_sortranks = req::item('old_sortranks');
         $sorts = array();
         foreach($sortranks as $cmid => $rank) {
            if( $old_sortranks[$cmid] != $rank )  $sorts[$cmid] = $rank;
         }
         foreach($sorts as $cmid => $rank) {
            $rank = intval( $rank );
            db::query("Update `bone_catalog_model` set `sortrank` = '{$rank}' where `cmid` = '{$cmid}' ");
         }
         mod_catalog::model_cache_del();
         cls_msgbox::show('系统提示', '成功更新排序！', "?ct=catalog&ac=model");
         exit();
     }
     
     //递归处理子上目录树
     private function _load_son_tree($node, &$restr, $step = 2)
     {
           foreach($node as $_kk => $vv)
           {
               $img = "<img src='../../static/frame/admin/images/m-cur.gif' width='11' height='11' />";
               $restr .= "    <ul class='tree_item'>
    <li class='sel'><input type='checkbox' class='cbox' name='cid[{$_kk}]' value='{$_kk}' /></li>
    <li class='step{$step}'>
        {$img}
        {$vv['d']['cname']}[cid:{$_kk}]
    </li>
    <li class='sort'>
        <input type='text' style='width:50px;' name='sortrank[{$_kk}]' value='{$vv['d']['sortrank']}' />
        <input type='hidden' name='sort_old[{$_kk}]' value='{$vv['d']['sortrank']}' />
    </li>
    <li class='opt'>
        <a href=\"javascript:show_data('{$_kk}');\"><img src='../../static/frame/admin/images/icons/write.gif' alt='修改' width='14' height='14' border='0' /></a>
        <a href=\"javascript:del_one('{$_kk}');\"><img src='../../static/frame/admin/images/icons/gtk-del.png' alt='删除' width='16' height='16' border='0' /></a>
    </li>\r\n    </ul>\r\n";
              if( !empty($vv['s']) ) {
                   $restr .= "<div class='son' id='son_<{$_kk}>'>";
                   $this->_load_son_tree($vv['s'], $restr, $step + 1);
                   $restr .= "</div>";
              }
          }
     }

}
