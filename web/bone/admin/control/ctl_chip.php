<?php
if( !defined('PHPBONE') ) exit('Request Error!');
/**
 * Chip标签管理
 * $Id
 */
class ctl_chip
{
    private $table = '#PB#_chip';

    public function index()
    {
        $req = req::$forms;
        $tb  = cls_lurd_control::factory($this->table);
        $tb->form_url = '?ct=chip';
        $tb->set_order_query('order by `sortrank` desc');
        if( !empty($req['keyword']) ) {
            $tb->add_search_condition("`name` like '%{$req['keyword']}%'");
            $tb->form_url .= '&keyword='.urlencode($req['keyword']);
        }
        $tb->lock_evens( array('list', 'delete') );
        $tb->set_tplfiles('chip.index.tpl', 'chip.add.tpl', 'chip.edit.tpl');
        $tb->listen(req::$forms);
    }

    public function add()
    {
        if( req::$request_mdthod=='POST' )
        {
            $info = req::$posts;
            $result = db::get_one("SELECT `id` FROM `{$this->table}` WHERE `name`='{$info['name']}'");
            if( !empty($result['id']) ) {
                cls_msgbox::show("系统提示", "对不起，{$info['name']} chip标签已存在！", '-1', 3000);
            }
            //列表
            if($info['is_array'] == 1)
            {
                $muti_files = pub_media_dlg::uploadfiles('', 1, 'thumb');
                foreach($info['url'] as $key => $val)
                {
                    if(empty($val['title'])) continue;
                    $tmp['url'] = stripslashes($info['url'][$key]);
                    $tmp['title'] = stripslashes($info['title'][$key]);
                    $tmp['seo_description'] = stripslashes($info['seo_description'][$key]);
                    if( !empty($muti_files[$key]) ) {
                        $tmp['thumb'] = $muti_files[$key]['path'] . '/' . $muti_files[$key]['filename'];
                    }
                    $ret[] = $tmp;
                }
                $info['data']  = addslashes(json_encode($ret));
            }
            //普通数据
            else
            {
                $info['data'] = $info['content'];
            }
            if( empty($info['name']) or empty($info['data']) )
            {
                cls_msgbox::show('系统提示', '对不起，还有必要的数据没有填写！', '-1', 3000);
            }
            $lurd = cls_lurd::factory($this->table);
            $lurd->set_safe_check( 0 );
            $lurd->insert($info);
            $insert_id = $lurd->insert_id();
            if($insert_id) {
                cls_msgbox::show('系统提示', '添加成功！', '?ct=chip');
            } else {
                cls_msgbox::show('系统提示', '添加失败！', '-1', 3000);
            }
        }
        tpl::display('chip.add.tpl');
    }

    public function edit()
    {
        if( req::$request_mdthod=='POST' )
        {
            $muti_files = array();
            $info = req::$forms;
            if(empty($info['id'])) {
                cls_msgbox::show("系统提示", "缺少ID！", '-1', 3000);
            }
            if($info['is_array'] == 1)
            {
                $muti_files = pub_media_dlg::uploadfiles('', 1, 'thumb');
                foreach($info['url'] as $key => $val)
                {
                    if(empty($val['title'])) continue;
                    $tmp['url'] = stripslashes($info['url'][$key]);
                    $tmp['title'] = stripslashes($info['title'][$key]);
                    $tmp['seo_description'] = stripslashes($info['seo_description'][$key]);
                    if( !empty($muti_files[$key]) ) {
                        $tmp['thumb'] = $muti_files[$key]['path'] . '/' . $muti_files[$key]['filename'];
                    } else if( !empty($info['thumb'][$key]) ) {
                        $tmp['thumb'] = $info['thumb'][$key];
                    }
                    $ret[] = $tmp;
                }
                $info['data'] = addslashes(json_encode($ret));
            }
            else
            {
                $info['data'] = $info['content'];
            }
            if( empty($info['name']) or empty($info['data']) )
            {
                cls_msgbox::show("系统提示", "对不起，还有必要的数据没有填写！", '-1', 3000);
            }
            $info['isarray'] = $info['is_array'];
            $lurd = cls_lurd::factory($this->table);
            $lurd->set_safe_check( 0 );
            $result = $lurd->update($info);
            if($result != false ) {
                mod_chip::del_chip_cacahe( $info['name'] );
                cls_msgbox::show("系统提示", "修改成功！", '?ct=chip');
            } else {
                cls_msgbox::show("系统提示", "修改失败！", '-1', 3000);
            }
        }
        else
        {
            $id = req::item('id');
            $data = $this->_get($id);
            tpl::assign('data', $data);
            tpl::display('chip.edit.tpl');
        }
    }

    //读取单个记录
    public function _get($key, $is_id = true)
    {
        if($is_id == true) {
            $data = db::get_one(" SELECT * FROM `{$this->table}` WHERE `id` = '{$key}' ");
        }else {
            $data = db::get_one(" SELECT * FROM `{$this->table}` WHERE `name` = '{$key}' ");
        }
        if($data['isarray'] == 1) {
            $data['data'] = json_decode($data['data'], true);
        }
        return $data;
    }

    //预览
    public function preview()
    {
        $chipname = req::item('name');
        tpl::assign('chipname',$chipname);
        tpl::display('chip.preview.tpl');
    }
}
