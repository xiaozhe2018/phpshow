<?php
//附件上传对话框配置
require_once dirname(__FILE__).'/../../../core/bone.php';
bone::$is_ajax = true;
/*******************************
 * 检测是否管理员
 *******************************/
 
//权限控制程序信息
$purview_config = array(
    'user_handler' => 'mod_admin_user',           //获取用户信息的接口类
    'purview_key' => 'admin_df_purview',          //获取用户组权限配置的key(针对bone_config)
    'pool_name' => 'admin',                       //当前应用池名
    'login_url' => '?ct=index&ac=login',  //用户登录入口地址
);

session_start();
bone::$auth  = new cls_auth($purview_config['user_handler'], $purview_config['purview_key'], $purview_config['pool_name']);
$_user = bone::$auth->get_userinfos();
if( empty($_user) ) {
    cls_msgbox::show("系统提示", "只允许通过管理后台访问本页面！", '');
    exit();
}
