<?php
if( !defined('PHPBONE') ) exit('Request Error!');
/**
 * 友情连接管理
 */
class ctl_friendlink
{
    private $table = 'bone_friendlinks';

    public function index()
    {
        $even = request('even', 'list');
        
        //排序不使用lurd处理
        if( $even=='up_sort' ) {
            return $this->up_sort();
        }
        
        $tb = cls_lurd_control::factory($this->table);
        $tb->form_url = '?ct=friendlink';
        $tb->set_order_query("order by `sortrank` desc");
        $reqs['keyword'] = request('keyword', '');
        $reqs['type'] = request('type', 0, 'int');
        if( !empty($reqs['keyword']) )
        {
            $tb->add_search_condition(" LOCATE('{$reqs['keyword']}', `webname`) > 0 ");
            $tb->form_url .= "&keyword=".urlencode($reqs['keyword']);
        }
        if( !empty($reqs['type']) )
        {
            $tb->add_search_condition(" `type` = '{$reqs['type']}' ");
            $tb->form_url .= "&type=".urlencode($reqs['type']);
        }
        tpl::assign('reqs', $reqs);
        $lurd_hooks = array('saveadd_start' => '_friendlink_add', 'saveadd_end' => '_friendlink_add',
                            'saveedit_start' => '_friendlink_edit', 'saveedit_end' => '_friendlink_edit',
                            'delete_start' => '_friendlink_delete', 'delete_end' => '_friendlink_delete',
                            );
        $tb->bind_hooks($this, $lurd_hooks);
        $tb->set_tplfiles('friendlink.index.tpl', 'friendlink.add.tpl', 'friendlink.edit.tpl');
        $tb->listen(req::$forms);
    }
    
    //保存增加模型数据后的处理
    public function _friendlink_add($hookname, &$data, $insert_id)
    {
        if( $hookname=='saveadd_start' )
        {
            //lurd操作前处理
        }
        else
        {
            cls_msgbox::show('系统提示', '成功增加友情连接', "javascript:parent.tb_remove();");
            exit();
        }
    }
    
    //lurd保存修改数据后的处理
    public function _friendlink_edit($hookname, &$data, $add_data='')
    {
        if( $hookname=='saveedit_start' )
        {
            //lurd操作前处理
        }
        else
        {
            cls_msgbox::show('系统提示', '成功修改友情连接', "javascript:parent.tb_remove();");
            exit();
        }
    }
    
    //lurd删除数据hooks
    public function _friendlink_delete($hookname, &$data, &$add_data)
    {
        if( $hookname=='delete_start' )
        {
            //lurd操作前处理
        }
        else
        {
            cls_msgbox::show('系统提示', '成功删除指定的友情连接数据', '?ct=friendlink');
            exit();
        }
    }

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
                    db::query("UPDATE `{$this->table}` SET `sortrank`='{$val}' WHERE `id`='{$id}' LIMIT 1");
                }
            }
        }
        cls_msgbox::show('系统提示', '更新排序成功', '?ct=friendlink', 1000);
    }
}

