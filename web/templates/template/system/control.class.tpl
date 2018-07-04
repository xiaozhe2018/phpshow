<?php
if( !defined('PHPBONE') ) exit('Request Error!');
/**
 * ~control_name~
 */
class ctl_~class_name~
{
    private $table = '~mod_table~';
    
    private $base_url = '?ct=~class_name~';

    public function index()
    {
        $even = request('even', 'list');
        //~sort_start~
        //排序不使用lurd处理
        if( $even=='up_sort' ) {
            return $this->up_sort();
        }
        //~sort_end~
        $tb = cls_lurd_control::factory($this->table);
        $tb->form_url = $this->base_url;
        $reqs['keyword'] = request('keyword', '');
        if( !empty($reqs['keyword']) )
        {
            //这个自行处理，如果不设置关键字搜索条件，lurd会自动取所有字符串类型作为搜索条件
            //$tb->add_search_condition(" LOCATE('{$reqs['keyword']}', `field`) > 0 ");
            $tb->form_url .= "&keyword=".urlencode($reqs['keyword']);
        }
        tpl::assign('reqs', $reqs);
        $lurd_hooks = array('saveadd_start' => '_~class_name~_add', 'saveadd_end' => '_~class_name~_add',
                            'saveedit_start' => '_~class_name~_edit', 'saveedit_end' => '_~class_name~_edit',
                            'delete_start' => '_~class_name~_delete', 'delete_end' => '_~class_name~_delete',
                            );
        $tb->bind_hooks($this, $lurd_hooks);
        //~allow_even~
        $tb->set_tplfiles('~class_name~.lurd.index.tpl', '~class_name~.lurd.add.tpl', '~class_name~.lurd.edit.tpl');
        $tb->listen(req::$forms);
    }
    
    //保存增加模型数据后的处理
    public function _~class_name~_add($hookname, &$data, $insert_id)
    {
        if( $hookname=='saveadd_start' )
        {
            //lurd操作前处理
        }
        else
        {
            cls_msgbox::show('系统提示', '成功增加~control_name~', "javascript:parent.tb_remove();");
        }
    }
    
    //lurd保存修改数据后的处理
    public function _~class_name~_edit($hookname, &$data)
    {
        if( $hookname=='saveedit_start' )
        {
            //lurd操作前处理
        }
        else
        {
            //~mod::cache_del_edit~
            cls_msgbox::show('系统提示', '成功修改~control_name~', "javascript:parent.tb_remove();");
        }
    }
    
    //lurd删除数据hooks
    public function _~class_name~_delete($hookname, &$data, &$add_data)
    {
        if( $hookname=='delete_start' )
        {
            //lurd操作前处理
        }
        else
        {
            //~mod::cache_del_del~
            cls_msgbox::show('系统提示', '成功删除指定的数据', $this->base_url);
        }
    }

   //~sort_start~
   /**
    * 更新排序
    */
    public function up_sort()
    {
        $sortrank = request('sortrank');
        $old_sortrank = request('old_sortrank');
        if( !empty($sortrank) )
        {
            foreach($sortrank as $id => $val) {
                if( $old_sortrank[$id] != $val ) {
                    db::query("UPDATE `{$this->table}` SET `sortrank`='{$val}' WHERE ~upkey~ LIMIT 1");
                }
            }
        }
        cls_msgbox::show('系统提示', '更新排序成功', $this->base_url, 1000);
    }
    //~sort_end~
}

