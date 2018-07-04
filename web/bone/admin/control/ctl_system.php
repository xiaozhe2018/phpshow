<?php
if( !defined('PHPBONE') ) exit('Request Error!');
/**
 * 系统管理
 *
 * @version $Id$
 */
class ctl_system
{
    private $dosubmit;
   /**
    * 构造函数
    * @return void
    */
    public function __construct()
    {
        $keyword = req::item('keyword');
        $this->dosubmit = request('dosubmit');
        tpl::assign('keyword', $keyword);
    }

   /**
    * 操作日志
    */
    public function oplog()
    {
        $tb = cls_lurd_control::factory('#PB#_admin_oplog');
        $tb->lock_evens( array('edit', 'delete', 'list') );
        $tb->set_search_field("user_name,msg");
        $tb->set_order_query(" order by id desc ");
        $tb->form_url = '?ct=system&ac=oplog';
        $tb->set_tplfiles('system.admin_oplog.tpl', '', 'system.admin_oplog.edit.tpl');
        $tb->listen(req::$forms);
        exit();
    }
    
   /**
    * 登录日志
    */
    public function login_log()
    {
        $tb = cls_lurd_control::factory('#PB#_admin_login');
        $tb->lock_evens( array('delete', 'list') );
        $tb->set_search_field("accounts,loginip");
        $tb->set_order_query(" order by `id` desc ");
        $tb->form_url = '?ct=system&ac=login_log';
        $tb->set_tplfiles('system.login_log.tpl', '', '');
        $tb->listen(req::$forms);
        exit();
    }
    
   /**
    * 清空旧的登录日志(清空数据库内容, 并保存一份到日志文件)
    */
    public function del_old_login_log()
    {
        $log_name = 'login-log-'.date('Y-m', time());
        $three_mo = time() - (3600 * 24 * 90);
        $rs = db::query("Select * From `#PB#_admin_login` where `logintime` < {$three_mo} ");
        $tmp = '';
        $i = $n = 0;
        while( $row = db::fetch_one($rs) )
        {
            $i++;
            $n++;
            if( $i > 200 )
            {
                log::add($log_name, $tmp);
                log::save();
                $tmp = '';
                $i = 0;
            } else {
                $tmp .= $row['id']."\t".$row['admin_id']."\t".$row['accounts']."\t".$row['loginip']."\t".date('Y-m-d H:i:s', $row['logintime'])."\t".$row['pools']."\t".$row['loginsta']."\n";
            }
        }
        if( $tmp != '' )
        {
            log::add($log_name, $tmp);
            log::save();
        }
        db::query("Delete From `#PB#_admin_login` where `logintime` < {$three_mo} ", true);
        cls_msgbox::show('系统提示', "成功清理 {$n} 条旧登录日志！", '?ct=system&ac=login_log');
        exit();
    }
    
   /**
    *  单项配置修改
    */
    private function _edit_hidden_config($key, $dotitle, $info, $alert_msg, $ac, $area_height=0)
    {
        if( $area_height==0 ) {
            $area_height = 350;
        }
        tpl::assign('dotitle', $dotitle);
        tpl::assign('info', $info);
        tpl::assign('c_ac', $ac);
        tpl::assign('area_height', $area_height);
        if( !isset(req::$forms['new_value']) )
        {
            $value = config::get( $key );
            tpl::assign('value', $value);
            tpl::display( 'system.edit_hidden_config.tpl' );
        }
        else
        {
            config::save($key, req::$forms['new_value']);
            cls_auth::save_admin_log( cls_auth::$user->fields['user_name'], "修改了系统配置的 {$key} 项目的值");
            cls_msgbox::show('系统提示', $alert_msg, '?ct=system&ac='.$ac);
        }
    }
    
   /**
    * 后台管理菜单
    */
    public function edit_admin_menu()
    {
        $this->_edit_hidden_config('admin_menu', '后台管理菜单配置', '"APP声明项"用于声明控制器，会在组权限管理的地方显示控制器名字。', '成功修改后台菜单配置', 'edit_admin_menu', 450);
    }
    
   /**
    * 登录IP限制
    */
    public function edit_iplimit()
    {
        $this->_edit_hidden_config('ip_limit', '登录IP限制', '每行填写一个IP，表示只允许这里的IP才能登录，不限制请不要填写', '成功修改IP限制配置', 'edit_iplimit');
    }
    
   /**
    * 后台权限配置XML
    */
    public function edit_purview_xml()
    {
        $this->_edit_hidden_config('admin_df_purview', '后台权限配置XML手动配置 [<a href="?ct=users&ac=edit_purview_groups">组权限管理</a>]', '组权限的XML配置文件，如果不理解请不要修改', '成功修改后台权限配置', 'edit_purview_xml');
    }
    
   /**
    * 会员权限配置XML
    */
    public function edit_member_xml()
    {
        $this->_edit_hidden_config('member_df_purview', '会员访问权限配置XML', '会员访问权限配置XML(格式为：控制器-动作，表示允许访问的内容，*为通配符)', '成功修改会员访问权限配置', 'edit_member_xml');
    }
    
    /**
     * 列出系统缓存, 缓存类型为0的表示系统变量，不在此处列出管理
     * @return void
     */
    public function config_list()
    {
        $selitem = req::item('selitem', 0, 'int');
        if( $selitem==0 ) $selitem = 1;
        if( req::$request_mdthod=='POST' )
        {
            $datas = req::item('datas');
            $sorts = req::item('sorts');
            foreach ($datas as $name => $value)
            {
                if( !isset($sorts[$name]) ) {
                    $data = $value;
                } else {
                    $data['sort_id'] = $sorts[$name];
                    $data['value'] = $value;
                }
                config::save($name, $data, false);
            }
            config::reload();
            cls_msgbox::show('系统提示', '成功修改配置！', '?ct=system&ac=config_list&selitem='.$selitem, 1000);
        }
        $config_types = mod_catalog::get_catalogs( 1, 'ems' );
        tpl::assign('config_types', $config_types);
        tpl::assign('selitem', $selitem);
        tpl::display('system.config.list.tpl');
    }

    /**
     * 修改系统配置信息
     */
    public function config_edit()
    {
        if( req::$request_mdthod=='POST' )
        {
            $info = req::item('data');
            $info['name'] = isset($info['name']) ? preg_replace("#[^\w]#", '', $info['name']) : '';
            if( empty($info['name']) )
            {
                $msg = '变量名不能为空！';
            }
            elseif( empty($info['title']) )
            {
                $msg = '变量说明标题不能为空！';
            }
            elseif( empty($info['type']) )
            {
                $msg = '变量类型不能为空！';
            }
            if( !empty($msg) )
            {
                cls_msgbox::show('系统消息', $msg, -1, 3000);
            }
            config::save($info['name'], $info, false);
            $okjs = "parent.location='?ct=system&ac=config_list&selitem={$info['group_id']}';";
            cls_msgbox::show('系统消息', '成功更新系统变量！', 'javascript:'.$okjs);
        }
        $name = req::item('name');
        $data = db::get_one("Select * From `#PB#_config` WHERE `name` = '{$name}' ");
        tpl::assign('data', $data);
        tpl::display('system.config.edit.tpl');
    }
    
    /**
     * 添加一个系统配置信息
     */
    public function config_add()
    {
        if( req::$request_mdthod=='POST' )
        {
            $info = req::item('data');
            $info['name'] = isset($info['name']) ? preg_replace("#[^\w]#", '', $info['name']) : '';
            if( empty($info['name']) )
            {
                $msg = '变量名不能为空！';
            }
            elseif( empty($info['title']) )
            {
                $msg = '变量说明标题不能为空！';
            }
            elseif( empty($info['type']) )
            {
                $msg = '变量类型不能为空！';
            }
            //检查缓存中是否存在该变量
            $row = db::get_one(" SELECT `value` FROM `#PB#_config` WHERE `name` = '{$info['name']}' ");
            if( !empty($row['value']) )
            {
                $msg = '对不起，该变量已存在，请指定另一个变量名！';
            }
            if( !empty($msg) )
            {
                cls_msgbox::show('系统消息', $msg, -1, 3000);
            }
            $result = db::insert('#PB#_config', $info);
            if( !empty($result) )
            {
                config::reload();
                $okjs = "parent.location='?ct=system&ac=config_list&selitem={$info['group_id']}';";
                cls_msgbox::show('系统消息', '系统变量添加成功！', 'javascript:'.$okjs);
            }
        }
        tpl::display('system.config.add.tpl');
    }
    
}
