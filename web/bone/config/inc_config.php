<?php
if( !defined('PHPBONE') ) exit('Request Error!');
/**
 * 全局配置文件
 * 本文件为初始化时固定的配置，相关参数不可在后台直接调整
 *
 */
//在线项目配置
if( file_exists(dirname(__FILE__).'/inc_online_config.php') )
{
    include dirname(__FILE__).'/inc_online_config.php';
}
//debug配置
else
{
//-------------------------------------------------------------
//基本常量
//------------------------------------------------------------
define('PATH_ROOT', substr(PHPBONE, 0, -5) );
define('PATH_MODEL', PHPBONE.'/model');
define('PATH_CONTROL', PHPBONE.'/control');
define('PATH_LIBRARY', PHPBONE . '/core/library');
define('PATH_CONFIG', PHPBONE . '/config');
define('PATH_DATA', PHPBONE . '/data');
define('PATH_SHARE', PHPBONE . '/share');
define('PATH_CACHE', PATH_DATA . '/cache');
define('PATH_DM_CONFIG', PATH_CONFIG . '/dm_config');
define('PATH_TEMPLATE', PATH_ROOT . '/templates');

//静态化(模板或程序里分别用MAKE_HTML、$site_make_html判断是否生成HTML处理对应的网址)
define('MAKE_HTML', false);

//开启调试模式
define('DEBUG_MODE', true);

//全局禁用cache( cache::get 强制返回 false)
define('NO_CACHE', true);

//正式环境中如果要考虑二级域名问题的应该用 .xxx.com
define('COOKIE_DOMAIN', '');

//session类型 file || memcache
define('SESSION_TYPE', 'file');

//-----------------------------------
//不适合存储到config系统的配置变量
//-----------------------------------

//指定某些IP允许开启调试，数组格式为 array('ip1', 'ip2'...)
$GLOBALS['config']['safe_client_ip'] = array('127.0.0.1');

//网站日志配置
$GLOBALS['config']['log'] = array(
   'file_path' => PATH_DATA.'/log',
   'log_type'  => 'file', 
);

//cache配置(df_prifix建议按网站名分开,如mc_114la_ / mc_tuan_ 等)
//cache_type一般是memcache，如无可用则用file，如有条件，用memcached
$GLOBALS['config']['cache'] = array(
    'enable'  => true,
    'cache_type' => 'file',
    'cache_time' => 7200,
    'file_cachename' => PATH_CACHE.'/cfc_data',
    'df_prefix' => 'mc_df_',
    'memcache' => array(
        'time_out' => 1,
        'host' => array( 'memcache://127.0.0.1:11211/bone_frame' )
    )
);

//MySql配置
//slave数据库从库可以使用多个
$GLOBALS['config']['db'] = array( 
        'host'    => array(
                        'master'  => '127.0.0.1',
                        'slave' => array('127.0.0.1')
                     ),
        'user'    => 'root',
        'pass'    => 'root',
        'name'    => 'phpshow',
        'charset' => 'utf-8',
);

//session
$GLOBALS['config']['session'] = array(
   'live_time' => 86400,
);

//默认时区
$GLOBALS['config']['timezone_set'] = 'Asia/Shanghai';

//需处理的网址必须使用<{rewrite}><{/rewriet}>括起来
//此项需要修改 PATH_DATA/rewrite.ini
$GLOBALS['config']['use_rewrite'] = false;

//数据表前缀
$GLOBALS['config']['db_prefix'] = 'bone';

//cookie加密码
$GLOBALS['config']['cookie_pwd'] = 'oXXXrd@uuw!ppr';

//框架版本标识
$GLOBALS['config']['frame_name'] = 'phpbone';
$GLOBALS['config']['frame_ver']  = '2.1';

} //if debug
