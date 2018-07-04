<?php
if( !defined('PHPBONE') ) exit('Request Error!');
/**
 *
 * 帮助文档分类
 *
 * @version $Id$
 */
class ctl_doc_catalog
{
    
   var $cmid = 3;  //默认模型id
   
   /**
    * 构造函数
    * @return void
    */
    public function __construct()
    {
        $r_cmid = req::item('cmid', '', 'var');
        if( $r_cmid > 0 ) {
            $this->cmid = $r_cmid;
        }
    }
    
    /**
     * 主入口
     */
    public function index()
    {
        $pid = req::item('pid', 0, 'int');
        $cur_modes = mod_catalog::get_models( $this->cmid );
        if( empty($cur_modes) )
        {
            cls_msgbox::show('系统提示', "找不到分类模型：{$this->cmid} ！", "-1");
            exit();
        }
        
        //LURD操作
        $catalog_table = mod_catalog::$table_base.$cur_modes['cmtable'];
        $tb = cls_lurd_control::factory( $catalog_table );
        $tb->form_url = "?ct=doc_catalog&cmid={$this->cmid}";
        $tb->set_order_query(" Order by `sortrank` desc, `cid` desc ");
        $even = req::item('even', 'list');
        
        //当前环境变量
        $infos['models'] = mod_catalog::$models;
        $infos['cur_model'] = $cur_modes;
        $infos['cmid'] = $this->cmid;
        $infos['pid'] = $pid;
        tpl::assign('infos', $infos);
        if( $even=='list' || $even=='add' || $even=='edit' ) {
            tpl::assign('cats', mod_catalog::get_catalogs( $this->cmid ));
        }
        
        //不调用lurd的列表数据
        if( $even=='list' ) {
            tpl::display('doc_catalog.index.tpl');
            exit();
        }
        
        $tb->set_tplfiles('doc_catalog.index.tpl', 'doc_catalog.add.tpl', 'doc_catalog.edit.tpl');
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
            $this->cmid = preg_replace("/[^\w]/", '', $add_data['cmid']);
            mod_catalog::catalog_del( $ids, $this->cmid );
            cls_msgbox::show('系统提示', '成功删除指定的分类！', "?ct=catalog&cmid={$this->cmid}");
            exit();
        }
    }
    
    /**
     * 列出全部下级分类
     */
     public function ajax_load_son()
     {
         $cid  = req::item('cid', '0', 'int');
         $node = mod_catalog::get_son_node( $this->cmid, $cid );
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
         $cur_modes = mod_catalog::get_models( $this->cmid );
         if( empty($cur_modes) ) {
            cls_msgbox::show('系统提示', "找不到分类模型：{$this->cmid} ！", "-1");
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
         mod_catalog::catalog_cache_del( $this->cmid );
         cls_msgbox::show('系统提示', '成功更新排序！', "?ct=doc_catalog&cmid={$this->cmid}");
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
