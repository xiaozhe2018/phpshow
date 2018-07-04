<?php
header('Content-Type: text/html; charset=utf-8');
require '../core/bone.php';

//权限控制程序信息
$purview_config = array(
    'auto_check' => false,                         //自动加载权限检查（此项为true时，自动执行$app->check_purview(1)模式）
    'user_handler' => 'mod_admin_user',           //获取用户信息的接口类
    'purview_key' => 'admin_df_purview',          //获取用户组权限配置的key(针对bone_config)
    'pool_name' => 'admin',                       //当前应用池名
    'login_url' => '?ct=index&ac=login',          //用户登录入口地址
);

//APP信息
$app_config = array(
    'app_title' => 'phpbone admin',
    'app_name' => 'admin',
    'session_start' => true,
    'purview_config' => $purview_config,
);

tpl::assign('title', 'phpbone管理后台');

$app = new bone( $app_config );

$app->check_purview( 1 );

$app->run();
