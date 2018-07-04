<?php
if( !defined('PHPBONE') ) exit('Request Error!');
/**
 * 模板引擎实现类
 *
 * @author itprato<2500875@qq>
 * @version $Id$
 */
class tpl
{
    protected static $instance = null;
    public static $debug_error = '';
    
    /**
     * Smarty 初始化
     * @return resource
     */
    public static function init ()
    {
        if (self::$instance === null)
        {
            self::$instance = new cls_quicktag();
            self::$instance->template_dir = util::path_exists(PATH_TEMPLATE . '/template/');
            self::$instance->compile_dir = util::path_exists(PATH_TEMPLATE . '/compile/');
            self::$instance->cache_dir = util::path_exists(PATH_TEMPLATE . '/cache/');
            self::$instance->left_delimiter = '<{';
            self::$instance->right_delimiter = '}>';
            self::$instance->is_caching = false;
            self::$instance->force_compile = DEBUG_MODE;
            self::$instance->plugins_dir = util::path_exists(PATH_LIBRARY . '/plugins');
            self::config();
        }
        return self::$instance;
    }
    
    protected static function config ()
    {
        $instance = self::init();
        if( !defined('MAKE_HTML') || !MAKE_HTML ) {
            tpl::assign('site_make_html', false);
        } else {
            tpl::assign('site_make_html', true);
        }
    }
    
    public static function assign ($tpl_var, $value)
    {
        $instance = self::init();
        $instance->assign($tpl_var, $value);
    }
    
    public static function display ( $tpl )
    {
        $instance = self::init();
        $app_tpldir = bone::$instance->app_name.'/';
        $instance->display($app_tpldir.$tpl);
        if( PHP_SAPI !== 'cli' && !bone::$is_ajax ) {
            debug_hanlde_xhprof();
        }
    }

    public static function fetch( $tpl )
    {
        $instance = self::init();
        return $instance->fetch( $tpl );
    }
}
