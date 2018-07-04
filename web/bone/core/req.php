<?php
if( !defined('PHPBONE') ) exit('Request Error!');
/**
 * 处理外部请求变量的类
 *
 * 禁止此文件以外的文件出现 $_POST、$_GET、$_FILES变量及eval函数(用 req::myeval )
 * 以便于对主要黑客攻击进行防范
 *
 * @author itprato<2500875@qq>
 * @version $Id$
 */
class req
{
    //用户的cookie
    public static $cookies = array();

    //把GET、POST的变量合并一块，相当于 _REQUEST
    public static $forms = array();
    
    //_GET 变量
    public static $gets = array();

    //_POST 变量
    public static $posts = array();

    //用户的请求模式 GET 或 POST
    public static $request_mdthod = 'GET';

    //文件变量
    public static $files = array();
    
    //url_rewrite
    public static $url_rewrite = false;
    
    //严禁保存的文件名
    public static $filter_filename = '/\.(php|pl|sh|js)$/i';
    
    //过滤器是否抛出异常
    //(只对邮箱、用户名、qq、手机类型有效)
    //如果不抛出异常，对无效的数据修改为空字符串
    public static $throw_error = false;

   /**
    * 初始化用户请求
    * 对于 post、get 的数据，会转到 selfforms 数组， 并删除原来数组
    * 对于 cookie 的数据，会转到 cookies 数组，但不删除原来数组
    */
    public static function init()
    {
        //命令行模式
        if( empty($_SERVER['REQUEST_METHOD']) ) {
            return false;
        }
        
        $magic_quotes_gpc = ini_get('magic_quotes_gpc');
        
        //是否启用rewrite(保留参数)
        self::$url_rewrite = isset($GLOBALS['config']['use_rewrite']) ? $GLOBALS['config']['use_rewrite'] : false;
        
        //处理post、get
        self::$request_mdthod = '';
        if( $_SERVER['REQUEST_METHOD']=='GET' ) {
            self::$request_mdthod = 'GET';
            $request_arr = $_GET;
        } else {
            self::$request_mdthod = $_SERVER['REQUEST_METHOD'];
            $request_arr = $_REQUEST;
        }
        //POST里的变更覆盖$_REQUEST(即是表单名与cookie同名, 表单优先)
        if($_SERVER['REQUEST_METHOD']=='POST') {
            self::$request_mdthod = 'POST';
            foreach( $_POST as $k => $v) {
                $request_arr[$k] = $v;
            }
        }
        unset($_POST);
        unset($_GET);
        unset($_REQUEST);
        if( count($request_arr) > 0 )
        {
            foreach($request_arr as $k => $v)
            {
                 if( preg_match('/^config/i', $k) ) {
                     throw new Exception('request var name not alllow!');
                     exit();
                 }
                 if( !$magic_quotes_gpc ) {
                     self::add_s( $v );
                 }
                 self::$forms[$k] = $v;
                 if( self::$request_mdthod=='POST' ) {
                     self::$posts[$k] = $v;
                 } else if( self::$request_mdthod=='GET' ) {
                     self::$gets[$k] = $v;
                 }
            }
        }
        
        //处理url_rewrite(暂时不实现)
        if( self::$url_rewrite )
        {
            $gstr = empty($_SERVER['QUERY_STRING']) ? '' : $_SERVER['QUERY_STRING'];
            if( empty($gstr) )
            {
                $gstr = empty($_SERVER['PATH_INFO']) ? '' : $_SERVER['PATH_INFO'];
            }
        }
        
        //默认ac和ct
        self::$forms['ct'] = isset(self::$forms['ct']) ? self::$forms['ct'] : 'index';
        self::$forms['ac'] = isset(self::$forms['ac']) ? self::$forms['ac'] : 'index';
        
        //处理cookie
        if( count($_COOKIE) > 0 )
        {
            if( !$magic_quotes_gpc ) {
                self::add_s( $_COOKIE );
            }
            self::$cookies = $_COOKIE;
        }
        
        //上传的文件处理
        if( isset($_FILES) && count($_FILES) > 0 )
        {
            if( !$magic_quotes_gpc ) {
                self::add_s( $_FILES );
            }
            self::filter_files($_FILES);
        }

    }
    
    //强制要求对gpc变量进行转义处理
    public static function add_s( &$array )
    {
        if( !is_array($array) )
        {
            $array =  addslashes($array);
        }
        else
        {
            foreach($array as $key => $value)
            {
                if( !is_array($value) ) {
                $array[$key] = addslashes($value);
                } else {
                self::add_s($array[$key]);
                }
            }
        }
    }

   /**
    * 把 eval 重命名为 myeval
    */
    public static function myeval( $phpcode )
    {
        return eval( $phpcode );
    }

   /**
    * 获得任意表单值
    * (即相当于$_REQUEST也可能获得cookie，但是当get/post和cookie同名时，gp优先)
    */
    public static function item( $formname, $defaultvalue = '', $filter_type='')
    {
        if( isset( self::$forms[$formname] ) ) {
            pub_filter::filter(self::$forms[$formname], $filter_type, self::$throw_error);
            return self::$forms[$formname];
        } else {
            return $defaultvalue;
        }
        return $value;
    }
    
   /**
    * 获得get表单值
    */
    public static function get( $formname, $defaultvalue = '', $filter_type='' )
    {
        if( isset( self::$gets[$formname] ) ) {
            pub_filter::filter(self::$gets[$formname], $filter_type, self::$throw_error);
            return self::$gets[$formname];
        } else {
            return $defaultvalue;
        }
        return $value;
    }
    
   /**
    * 获得post表单值
    */
    public static function post( $formname, $defaultvalue = '', $filter_type='' )
    {
        if( isset( self::$posts[$formname] ) ) {
            pub_filter::filter(self::$posts[$formname], $filter_type, self::$throw_error);
            return self::$posts[$formname];
        } else {
            return $defaultvalue;
        }
        return $value;
    }
    
   /**
    * 获得指定cookie值
    */
    public static function cookie( $key, $defaultvalue = '', $filter_type='' )
    {
        if( isset( self::$cookies[$key] ) ) {
            pub_filter::filter(self::$cookies[$key], $filter_type, self::$throw_error);
            return self::$cookies[$key];
        } else {
            $value = $defaultvalue;
        }
        return $value;
    }

   /**
    * 过滤文件类型(保留接口)
    */
    public static function filter_files( &$files )
    {
        /*
        foreach($files as $k => $v) {
            self::$files[$k] = $v;
        }
        */
        self::$files = $files;
        unset($_FILES);
    }

   /**
    * 移动上传的文件
    * $item 是用于当文件表单名为数组，如 upfile[] 之类的情况, $item 表示数组的具体键值，下同
    * @return bool
    */
    public static function move_upload_file( $formname, $filename, $item = '' )
    {
        if( self::is_upload_file( $formname, $item ) )
        {
            if( preg_match(self::$filter_filename, $filename) )
            {
                return false;
            }
            else
            {
                if( $item === '' ) {
                    if( PHP_OS == 'WINNT')
                        return copy(self::$files[$formname]['tmp_name'], $filename);
                    else
                        return move_uploaded_file(self::$files[$formname]['tmp_name'], $filename);
                } else {
                    if( PHP_OS == 'WINNT')
                        return copy(self::$files[$formname]['tmp_name'][$item], $filename);
                    else return
                        move_uploaded_file(self::$files[$formname]['tmp_name'][$item], $filename);
                }
            }
        }
    }
    
   /**
    * 获得指定临时文件名值
    */
    public static function get_tmp_name( $formname, $defaultvalue = '', $item = '' )
    {
        if( $item === '' ) {
            return isset(self::$files[$formname]['tmp_name']) ? self::$files[$formname]['tmp_name'] :  $defaultvalue;
        } else {
            return isset(self::$files[$formname]['tmp_name'][$item]) ? self::$files[$formname]['tmp_name'][$item] :  $defaultvalue;
        }
    }

   /**
    * 获得文件的扩展名
    */
    public static function get_shortname( $formname, $item = '' )
    {
        if( $item === '' ) {
            $filetype = strtolower(isset(self::$files[$formname]['type']) ? self::$files[$formname]['type'] : '');
        } else {
            $filetype = strtolower(isset(self::$files[$formname]['type'][$item]) ? self::$files[$formname]['type'][$item] : '');
        }
        $shortname = '';
        switch($filetype)
        {
            case 'image/jpeg':
                $shortname = 'jpg';
                break;
            case 'image/pjpeg':
                $shortname = 'jpg';
                break;
            case 'image/gif':
                $shortname = 'gif';
                break;
            case 'image/png':
                $shortname = 'png';
                break;
            case 'image/xpng':
                $shortname = 'png';
                break;
            case 'image/wbmp':
                $shortname = 'bmp';
                break;
            default:
                if( $item === '' ) {
                    $filename = isset(self::$files[$formname]['name']) ? self::$files[$formname]['name'] : '';
                } else {
                    $filename = isset(self::$files[$formname]['name'][$item]) ? self::$files[$formname]['name'][$item] : '';
                }
                if( preg_match("/\./", $filename) )
                {
                    $fs = explode('.', $filename);
                    $shortname = strtolower($fs[ count($fs)-1 ]);
                }
                break;
        }
        return $shortname;
    }

   /**
    * 获得指定文件表单的文件详细信息
    */
    public static function get_file_info( $formname, $item = '' )
    {
        if( !isset( self::$files[$formname] ) )
        {
            return false;
        }
        else
        {
            if($item === '')
            {
                return self::$files[$formname];
            }
            else
            {
                if( !isset(self::$files[$formname]['tmp_name'][$item]) ) {
                    return false;
                }
                else
                {
                    $infos = array();
                    foreach(self::$files[$formname] as $k => $_a) {
                        $infos[$k] = $_a[$item];
                    }
                    return $infos;
                }
            }
        }
    }

   /**
    * 判断是否存在上传的文件
    */
    public static function is_upload_file( $formname,  $item = '' )
    {
        if( $item === '' ) {
            if( isset(self::$files[$formname]['error']) && self::$files[$formname]['error']==UPLOAD_ERR_OK  ) {
                return true;
            } else {
                return false;
            }
        } else {
            if( isset(self::$files[$formname]['error'][$item]) && self::$files[$formname]['error'][$item]==UPLOAD_ERR_OK  ) {
                return true;
            } else {
                return false;
            }
            //return is_uploaded_file( self::$files[$formname]['tmp_name'][$item] );
        }
    }
    
    /**
     * 检查文件后缀是否为指定值
     *
     * @param  string  $subfix
     * @return boolean
     */
    public static function check_subfix($formname, $subfix = 'csv', $item= '')
    {
        if( self::get_shortname( $formname, $item ) != $subfix)
        {
            return false;
        }
        return true;
    }
    
    /**
     * 把指定数据转化为路由数据
     * @param  $dfarr   默认数据列表 array( array(key, dfvalue)... )
     * @param  $datas   数据列表
     * @param  $method  方法
     * @return boolean
     */
    public static function assign_values(&$dfarr, &$datas, $method='get')
    {
        $method = strtolower( $method );
        foreach($dfarr as $k => $v)
        {
            if( isset($datas[$k]) )
            {
                req::$forms[ $v[0] ] = $datas[$k];
            } else {
                req::$forms[ $v[0] ] = $v[1];
            }
            //给值gets/posts
            if( $method=='get' ) {
                req::$gets[ $v[0] ] = req::$forms[ $v[0] ];
            } else {
                req::$posts[ $v[0] ] = req::$forms[ $v[0] ];
            }
        }
    }

}
