<?php
if( !defined('PHPBONE') ) exit('Request Error!');
/**
 * 用户管理
 *
 * @version $Id$
 */
class ctl_users
{
   
   /**
    * 构造函数
    * @return void
    */
    public function __construct()
    {
        tpl::assign('pur_configs', bone::$auth->pur_configs);
    }
   
   /**
    * 管理员帐号管理
    */
    public function index()
    {
        $showtype = req::item('show', 'all');
        $tb = cls_lurd_control::factory('#PB#_admin');
        $tb->form_url = '?ct=users';
        $tb->add_search_condition(" `pools`='admin' ");
        $gp = req::item('gp', '');
        if( $gp != '' )
        {
            $tb->add_search_condition(" `groups` like '%{$gp}%' ");
            $tb->form_url .= '&gp='.$gp;
        }
        $tb->set_search_field('user_name,email');
        //用户上次登录时间、IP信息
        $even = req::item('even', '');
        if( $even=='edit')
        {
            $last_login = cls_auth::$user->get_last_login( req::item('admin_id',0) );
            tpl::assign('last_login', $last_login );
        }
        //修改用户资料事件前处理
        else if( $even=='saveedit' )
        {
            if(req::item('userpwd') != '')
            {
                req::$forms['userpwd'] = md5(req::$forms['userpwd']);
            } else {
                unset(req::$forms['userpwd']);
            }
            //保存附表数据
            if(empty(req::$forms['groups']))
            {
                req::$forms['groups'] = 'member_pub';
            } else {
                $gp = join(',', req::$forms['groups']);
                req::$forms['groups'] = $gp;
            }
            $uid = req::item('admin_id', 0, 'int');
            cls_auth::$user->del_cache($uid);
            cls_auth::save_admin_log(cls_auth::$user->fields['user_name'], "修改了管理员 ".req::item('admin_id')." 的密码！");
        }
        //保存新增用户事件前处理
        else if($even=='saveadd' )
        {
            req::$forms['pools'] = 'admin';
            if(empty(req::$forms['groups']))
            {
                req::$forms['groups'] = 'member_pub';
            } else {
                $gp = join(',', req::$forms['groups']);
                req::$forms['groups'] = $gp;
            }
            $user_name = req::item('user_name');
            $row = $tb->get_one(" where `user_name`='{$user_name}' ");
            if( is_array($row['data'][0]) )
            {
                cls_msgbox::show('系统提示', '用户名已经存在！', '-1');
                exit();
            }
            if( req::$forms['userpwd']=='' )
            {
                cls_msgbox::show('系统提示', '用户密码不能为空！', '-1');
                exit();
            }
            req::$forms['userpwd'] = md5(req::$forms['userpwd']);
            $tb->end_need_done = true;
            cls_auth::save_admin_log( cls_auth::$user->fields['user_name'], "增加了一个管理员");
        }
        //删除用户前处理
        else if( $even=='delete' )
        {
            $uid = req::item('admin_id');
            $uid = intval($uid);
            if( $uid ==1 )
            {
                exit( cls_msgbox::show('系统提示', '用户id为1的用户不允许删除！', '-1') );
            }
            req::$forms['admin_id'] = array($uid);
            //db::query("Delete From `users_details` where `uid`='".req::item('uid', 0)."'");
            cls_auth::save_admin_log( cls_auth::$user->fields['user_name'], "执行了一次删除管理员操作");
        }

        //自动化操作
        $tb->bind_type('logintime', 'TIMESTAMP');
        $tb->set_tplfiles('users.index.tpl', 'users.add.tpl', 'users.edit.tpl');
        $tb->listen(req::$forms);
        
        //后处理程序（必须设置参数 end_need_done = true 才能操作，否则lurd控制器完成后会直接exit）
        if( $even=='saveadd' )
        {
            req::$forms['admin_id'] = $tb->insert_id();
            //req::$forms['birthday'] = '0000-00-00';
            if( req::$forms['admin_id'] > 0 )
            {
                //$tb->insert( req::$forms, 'users_details' );
                cls_msgbox::show('', '成功增加一条记录！', 'javascript:parent.tb_remove();');
                exit();
            }
            else
            {
                cls_msgbox::show('', '成功增加用户失败，可能用户名已经存在！', '-1');
                exit();
            }
        }

        exit();
    }


   /**
    * 对修改用户自己的密码使用单独事件
    */
    public function editpwd()
    {
        $tb = cls_lurd_control::factory('#PB#_admin');

        req::$forms['admin_id'] = cls_auth::$user->uid;
        
        $last_login = cls_auth::$user->get_last_login( cls_auth::$user->uid );
        tpl::assign('last_login', $last_login );

        req::$forms['even'] = (req::item('even', '') != 'saveedit' ? 'edit' : 'saveedit');

        if( req::$forms['even'] == 'saveedit')
        {
            if(req::item('userpwd') != '')
            {
                req::$forms['userpwd'] = md5(req::$forms['userpwd']);
            } else {
                cls_msgbox::show('系统提示', '你没进行任何操作！', '-1');
                exit();
            }
        }

        //自动化操作
        $tb->bind_type('logintime', 'TIMESTAMP');
        $tb->set_tplfiles('', '', 'users.edit.me.tpl');
        $tb->listen(req::$forms);

        exit();
    }
    
   /**
    * 设置具体用户的权限
    */
    public function user_purview()
    {
        $even = req::item('even', '');
        $uid  = req::item('admin_id', 0, 'int');
        //显示用户原有权限
        if( $even == '' )
        {
            $fields = db::get_one("Select * From `#PB#_admin` where `admin_id`='{$uid}' ");
            $groups = bone::$auth->get_user_groups($fields['admin_id'], 'admin', $fields['groups']);
            tpl::assign('users', $fields);
            tpl::assign('groups', $groups);
            tpl::assign('config_apps', mod_admin_menu::parse_apps());
            tpl::assign('user_name', $fields['user_name']);
            tpl::assign('admin_id', $fields['admin_id']);
            tpl::assign('gp', $fields['groups']);
        }
        //保存修改
        else if( $even == 'saveedit' )
        {
            $groups = req::item('groups', '');
            $gp     = req::item('gp', '');
            $gstr   = join(',', $groups);
            bone::$auth->user->del_cache($admin_id);
            db::query("Replace Into `#PB#_admin_purview`(`admin_id`, `purviews`) Values('{$admin_id}', '{$gstr}'); ");
            cls_msgbox::show('系统提示', '成功指定用户的独立权限！', '?ct=users&ac=index&gp='.$gp);
            exit();
        }
        tpl::assign('pur_configs', bone::$auth->pur_configs['pools']['admin']);
        tpl::display('users.purview.tpl');
        exit();
    }
    
   /**
    * 当前用户登录后列出它的权限
    */
    public function mypurview()
    {
        $groups = bone::$auth->get_user_groups(cls_auth::$user->fields['uid'], 'admin', cls_auth::$user->fields['groups']);
        tpl::assign('users', cls_auth::$user->fields);
        tpl::assign('groups', $groups);
        tpl::assign('config_apps', mod_admin_menu::parse_apps());
        tpl::display('users.mypurview.tpl');
        exit();
    }

   /**
    * 修改组权限
    */
    public function edit_purview_groups()
    {
        $even = req::item('even', '');
        $gp = req::item('group', '');
        if( $gp != '') {
            list($poolname, $gp) = explode('_', $gp);
        }
        tpl::assign('group_name', '所有组');
        //修改具体组
        if( $even == 'edit' )
        {
            tpl::assign('group_name', bone::$auth->pur_configs['pools'][$poolname]['private'][$gp]['name']);
            tpl::assign('config_apps', mod_admin_menu::parse_apps());
            tpl::assign('groups', bone::$auth->pur_configs['pools'][$poolname]['private'][$gp]);
            //print_r( bone::$auth->$pur_configs['pools'][$poolname]['private'][$gp] );
        }
        //保存修改
        else if( $even == 'saveedit' )
        {
            
            $groups = req::item('groups', '');
            $gstr = join(',', $groups);
            
            $groups = cls_auth::parse_private($gstr);
            
            bone::$auth->pur_configs['pools'][$poolname]['private'][$gp]['allow'] = $groups;
            
            $new_config = bone::$auth->save_config( bone::$auth->pur_configs, 'admin_df_purview' );
            
            cls_msgbox::show('系统提示', '成功修改指定的组权限！', '?ct=users&ac=edit_purview_groups&even=edit&group='.$poolname.'_'.$gp);
            exit();
        }
        tpl::assign('access_groups', bone::$auth->pur_configs);
        tpl::display('users.edit_purview_groups.tpl');
        exit();
    }

}
