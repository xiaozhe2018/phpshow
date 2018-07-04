<?php
if( !defined('PHPBONE') ) exit('Request Error!');
/**
 * test
 */
class ctl_test_user
{
    private $table = 'bone_test_user';
    
    private $base_url = '?ct=test_user';

    public function index()
    {
        $even = request('even', 'list');
        
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
        $lurd_hooks = array('saveadd_start' => '_test_user_add', 'saveadd_end' => '_test_user_add',
                            'saveedit_start' => '_test_user_edit', 'saveedit_end' => '_test_user_edit',
                            'delete_start' => '_test_user_delete', 'delete_end' => '_test_user_delete',
                            );
        $tb->bind_hooks($this, $lurd_hooks);
        $evens = array (   0 => 'list',   1 => 'add',   2 => 'saveadd',   3 => 'edit',   4 => 'saveedit',   5 => 'delete', );
        $tb->lock_evens( $evens );
        $tb->set_tplfiles('test_user.lurd.index.tpl', 'test_user.lurd.add.tpl', 'test_user.lurd.edit.tpl');
        $tb->listen(req::$forms);
    }
    
    //保存增加模型数据后的处理
    public function _test_user_add($hookname, &$data, $insert_id)
    {
        if( $hookname=='saveadd_start' )
        {
            //lurd操作前处理
        }
        else
        {
            cls_msgbox::show('系统提示', '成功增加test', "javascript:parent.tb_remove();");
        }
    }
    
    //lurd保存修改数据后的处理
    public function _test_user_edit($hookname, &$data)
    {
        if( $hookname=='saveedit_start' )
        {
            //lurd操作前处理
        }
        else
        {
            //~mod::cache_del_edit~
            cls_msgbox::show('系统提示', '成功修改test', "javascript:parent.tb_remove();");
        }
    }
    
    //lurd删除数据hooks
    public function _test_user_delete($hookname, &$data, &$add_data)
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

   
}

