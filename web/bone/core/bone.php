<?php
/**
 * 框架核心入口文件 t2015
 *
 * 环境检查，核心文件加载
 *
 * @author itprato<2500875@qq>
 * @version $Id$
 */

////////////////////////////////////////////////////////////////////
//init start 系统初始化开始

$_page_start_time = microtime(true);

// 严格开发模式
error_reporting( E_ALL );

//开启register_globals会有诸多不安全可能性，因此强制要求关闭register_globals
if ( ini_get('register_globals') )
{
    exit('php.ini register_globals must is Off! ');
}

//核心库目录
define('PHPBONE', substr(dirname(__FILE__), 0, -5));

//系统配置
require PHPBONE.'/config/inc_config.php';

//设置时区
date_default_timezone_set( $GLOBALS['config']['timezone_set'] );

//CLI模式下不debug，不启动路由和不启用session
if( PHP_SAPI == 'cli' )
{
    require PHPBONE.'/core/util.php';
    require PHPBONE.'/core/db.php';
    require PHPBONE.'/core/tpl.php';
    require PHPBONE.'/core/log.php';
    require PHPBONE.'/core/cache.php';
    require PHPBONE.'/core/config.php';

}
//WEB访问模式
else
{
    //外部请求程序处理(路由)
    require PHPBONE.'/core/req.php';
    req::init();

    //加载核心类库
    require PHPBONE.'/core/util.php';
    require PHPBONE.'/core/db.php';
    require PHPBONE.'/core/tpl.php';
    require PHPBONE.'/core/log.php';
    require PHPBONE.'/core/cache.php';
    require PHPBONE.'/core/config.php';

    //debug设置
    if( in_array( util::get_client_ip(), $GLOBALS['config']['safe_client_ip']) ) {
        $_debug_safe_ip = true;
    } else {
        $_debug_safe_ip = false;
    }
    require PATH_LIBRARY.'/debug/lib_debug.php';
    if( $_debug_safe_ip || DEBUG_MODE === true )
    {
        ini_set('display_errors', 'On');
    }
    else
    {
        ini_set('display_errors', 'Off');
    }
    set_exception_handler('handler_debug_exception');
    set_error_handler('handler_debug_error', E_ALL);

    //session接口(使用session前需自行调用session_start，可以app_config里设定，验证码类程序建议使用独立的app)
    require PHPBONE.'/core/session.php';
}

//加载用户自定义配置，通过 config::$bone_configs[$key] 或 config::get($key) 调用
config::get();

/**
 * 程序结束后执行的动作
 */
register_shutdown_function('handler_php_shutdown');
function handler_php_shutdown()
{
    //调试模式执行时间
    global $_page_start_time,$_debug_safe_ip;
    if( ($_debug_safe_ip || DEBUG_MODE === true) && !bone::$is_ajax && PHP_SAPI != 'cli' ) {
        $et = sprintf('%0.4f', microtime(true) - $_page_start_time);
        echo "<div style='font-size:11px' align='center'>执行时间：{$et} 秒</div>";
    }

    if( PHP_SAPI != 'cli' && !bone::$is_ajax ) {
        show_debug_error();
    }
    log::save();
    if( defined('CLS_CACHE') ) {
        cache::free();
    }
    if( defined('PUB_NATIVE_CACHE') ) {
        pub_native_cache::close();
    }
}

//init finish 系统初始化完成，后面是一些附加类和函数
/////////////////////////////////////////////////////////////

/**
 * 自动加载类库处理
 * 加载优先级 /bone/library => 应用目录/model => 根目录/model
 * (如果不在这些位置, 则需自行手工加载，对于小型项目，也可以把model全放到library以减少类文件查找时间)
 * @return void
 */
function __autoload( $classname )
{
    $classname = preg_replace("/[^0-9a-z_]/i", '', $classname);
    if( class_exists ( $classname ) ) {
        return true;
    }
    $classfile = $classname.'.php';
    try
    {
        //echo PATH_LIBRARY.'/'.$classfile, PATH_MODEL.'/'.$classfile;
        if ( file_exists ( PATH_LIBRARY.'/'.$classfile ) )
        {
            require PATH_LIBRARY.'/'.$classfile;
        }
        else if( file_exists ( PATH_MODEL.'/'.$classfile ) )
        {
            require PATH_MODEL.'/'.$classfile;
        }
        else
        {
            //return false;
            throw new Exception ( 'Error: Cannot find the '.$classname );
        }
    }
    catch ( Exception $e )
    {
        bone::fatal_error( 'init.php __autoload()', $e->getMessage().'|'.$classname.' url:'.util::get_cururl() );
    }
}
//注册autoload类
//spl_autoload_register('bone_autoload');

/**
 * req::item 别名函数
 */
function request($key, $df='', $filter='')
{
    return req::item($key, $df, $filter);
}


/**
 * app类
 * 框架里每一个入口文件都称之为一个app，可以在子目录里，也可以放在外面目录
 * 文件只需引入 bone.php 并使用这个类进行设置即可（如果是cli程序，则不需要设置，但仍要引入此类）
 *
 function __cls_bone(){ } //ultraedit识别
 *
 * @author itprato<2500875@qq>
 */
class bone
{
    //app配置数组
    public $app_config = array();

    //app名称(一些自动化程序默认会把这个作为title)
    public $app_title = '';

    //app名称(必须为无空格英文，app控制器和模板默认目录，根目录下此项为空)
    public $app_name  = '';

    //是否启动session
    public $session_start = false;

    public $purview_config = array();

    //如果是ajax的应用，可以自行改变这个变量，避免输出debug信息
    public static $is_ajax  = false;

    //当前启动的实例
    public static $instance = null;

    //权限类的实例
    public static $auth = null;

    //当前ct和ac
    public static $ct = '';
    public static $ac = '';

   /**
    * 构造函数
    * @return void
    */
    public function __construct( $config = array() )
    {
        //获取当前控制器及action
        $this->_get_action();

        //获取配置
        $this->app_config = $config;
        if( isset($config['app_title']) ) {
            $this->app_title = $config['app_title'];
        }
        if( isset($config['app_name']) ) {
            $this->app_name = $config['app_name'];
        }
        if( isset($config['session_start']) && $config['session_start'] ) {
            session_start();
            $this->session_start = $config['session_start'];
        }

        //初始化权限类
        if( isset($config['purview_config']) )
        {
            $this->purview_config = $config['purview_config'];
            if( !empty($this->purview_config['user_handler']) && !empty($this->purview_config['purview_key']) &&
                !empty($this->purview_config['pool_name']) )
            {
                self::$auth  = new cls_auth( $this->purview_config['user_handler'],
                                             $this->purview_config['purview_key'],
                                             $this->purview_config['pool_name'] );
                if( $this->purview_config['auto_check'] )  {
                    $this->check_purview( 1 );
                }
            }
        }

        self::$instance = $this;

    }

   /**
    * 权限检查
    * @parem $backtype        返回类型: 1--是由权限控制程序直接处理 2--只返回结果(结果为：1 正常，0 没登录，-1 没组权限， -2 没应用池权限)
    * return void
    */
    public function check_purview( $backtype = 1 )
    {
        if( $backtype == 1 && $this->purview_config['auto_check'] )  {
           return true;
        }
        self::$auth->set_control_url( $this->purview_config['login_url'] );
        $rs = self::$auth->check_purview(self::$ct, self::$ac, $backtype);
        return $rs;
    }

    /**
     * 路由映射
     * @param $ctl  控制器
     * @parem $ac   动作
     * @return void
     */
    public function run()
    {
        $ac = self::$ac;
        $ct = self::$ct;
        $ctl  = 'ctl_'.$ct;
        $control_path = ($this->app_name == '' ? PATH_CONTROL : PATH_CONTROL.'/'.$this->app_name);
        $path_file = $control_path . '/' . $ctl . '.php';
        $path_file2 = './control/' . $ctl . '.php';
        //禁止 _ 开头的方法
        if( $ac[0]=='_' )
        {
            bone::fatal_error( 'bone.php run_controller() action {$ac} is not allow url:'.util::get_cururl() );
        }
        try
        {
            if( file_exists( $path_file ) )
            {
                require $path_file;
            }
            else if( file_exists( $path_file2 ) )
            {
                require $path_file2;

            } else {
                throw new Exception ( "Contrl {$ctl}--{$path_file} is not exists!" );
            }
            if( method_exists ( $ctl, $ac ) === true )
            {
                $instance = new $ctl( );
                $instance->$ac();
            } else {
                throw new Exception ( "Method {$ctl}::{$ac}() is not exists!" );
            }
        }
        catch ( Exception $e )
        {
            bone::fatal_error( 'bone.php run_controller()', $e->getMessage().' url:'.util::get_cururl() );
        }
    }

   /**
    * 获取ac和ct
    * return void
    */
    public function _get_action()
    {
        if( PHP_SAPI == 'cli' ) {
            return false;
        }
        self::$ac   = preg_replace("/[^0-9a-z_]/i", '', req::item('ac') );
        self::$ct   = preg_replace("/[^0-9a-z_]/i", '', req::item('ct') );
        if( self::$ac=='' ) self::$ac = 'index';
        if( self::$ct=='' ) self::$ct = 'index';
    }

    /**
     * 致命错误处理接口
     * 系统发生致命错误后的提示
     * (致命错误是指发生错误后要直接中断程序的错误，如数据库连接失败、找不到控制器等)
     *
     * @parem $errtype
     * @parem $msg
     * @return void
     */
    public static function fatal_error( $errtype, $msg )
    {
        global $_debug_safe_ip;
        $log_str = $errtype.':'.$msg;
        if ( DEBUG_MODE === true || $_debug_safe_ip || PHP_SAPI == 'cli' )
        {
            throw new Exception( $log_str );
        }
        else
        {
            log::add('fatal_error', $msg."\r\n");
            header ( "location:/404.html" );
            exit();
        }
    }

   /**
    * 框架版本标识
    */
    public static function get_ver()
    {
        return $GLOBALS['config']['frame_name'].' V'.$GLOBALS['config']['frame_ver'];
    }

} //end class
