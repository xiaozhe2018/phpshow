<?php
/**
 * quick模板引擎
 *
 */
define('QUICKTAG', dirname(__FILE__));
class cls_quicktag
{
    //相关文件夹配置
    var $template_dir    =  'templates/template';
    var $compile_dir     =  'templates/compile';
    var $cache_dir       =  'templates/cache';
    var $plugins_dir     =  'plugins';
    
    //编译后的php文件名加密码
    var $compile_pwd = '';
    
    //是否一直都编译（通常是调试时使用，普通情况是系统自动检查模板与编译后的文件时间，如果编译文件更旧则重编译）
    var $force_compile   =  true;
    
    //是否缓存
    var $is_caching      =  false;
    var $cache_lifetime  =  3600;
    
    //标签格式
    var $left_delimiter  =  '<{';
    var $right_delimiter =  '}>';

    //模板类内置变量
    var $_tpl_vars     = array();

    var $tpl_file      = '';
    var $cache_file    = '';
    var $compile_file  = '';
    
    //引入当前模板的对象（这个主要是为了便于在相关块调用中引用）
    var $ref_obj           = null;
    
    //是否开启了rewrite处理
    var $is_rewrite = false;
    
    /**
    * 构造函数
    * @return void
    */
    public function __construct()
    {
        //编译完成后进行rewrite替换
        if( $GLOBALS['config']['use_rewrite']==true )
        {
            $this->is_rewrite = true;
        }
    }

    /**
     * 传递值给模板变量（这方法与smarty功能完全相同）
     *
     * @param $tpl_var the 变量名
     * @param mixed $value 变量值
     */
    public function assign($tpl_var, $value = null)
    {
        if ( is_array($tpl_var) )
        {
            foreach ($tpl_var as $key => $val)
            {
                if ($key != '') $this->_tpl_vars[$key] = $val;
            }
        } else {
            if ($tpl_var != '') $this->_tpl_vars[$tpl_var] = $value;
        }
    }
    
   /**
    * 清除已经给值的模板变量
    *
    * @param string $tpl_var
    */
    public function clear_assign($tpl_var)
    {
        if (is_array($tpl_var))
        {
            foreach ($tpl_var as $curr_var)
            {
                unset($this->_tpl_vars[$curr_var]);
            }
        }
        else
        {
            unset($this->_tpl_vars[$tpl_var]);
        }
    }
    
   /**
    * 获取编译后保存文件名的加密码
    */
    protected function get_compile_pwd()
    {
        if( $this->compile_pwd=='' )
        {
            $compile_pwd = '';
            $cache_file = $this->compile_dir.'/__qt_compile.inc.php';
            if( file_exists($cache_file) )
            {
                $compile_pwd = trim( preg_replace('/(.*);\/\//U', '', file_get_contents($cache_file)) );
            }
            if( $compile_pwd=='' )
            {
                for( $i = 0; $i < 12; $i++)
                {
                    $t = mt_rand(1, 3);
                    if($t==1) $compile_pwd .= chr(mt_rand(ord('A'), ord('Z')));
                    else if($t==1) $compile_pwd .= chr(mt_rand(ord('0'), ord('9')));
                    else $compile_pwd .= chr(mt_rand(ord('a'), ord('z')));
                }
                $pstr = '<'.'?php exit();//'.$compile_pwd;
                file_put_contents($cache_file, $pstr);
            }
            $this->compile_pwd = $compile_pwd;
        }
        return $this->compile_pwd;
    }
    
    /**
     * 重置模板变量
     */
    public function reset_assign()
    {
        $this->_tpl_vars = array();
    }
    
    /**
     * 对用于编译的对象复制当前类部份属性
     *
     * @param $obj 对象实例
     */
    public function set_property(&$obj)
    {
        $obj->template_dir    =  $this->template_dir;
        $obj->compile_dir     =  $this->compile_dir;
        $obj->cache_dir       =  $this->cache_dir;
        $obj->force_compile   =  $this->force_compile;
        $obj->left_delimiter  =  $this->left_delimiter;
        $obj->right_delimiter =  $this->right_delimiter;
        $obj->ref_obj         =  $this->ref_obj;
    }
    
   /**
    *  检测模板资源状态
    *  
    *  @parem string $tplname 模板名称
    */
    protected function fetch_resource_info($tplname)
    {
        $infos = array('tplcache' => '', 'save_cache' => false, 'cache_data' => '');
        $this->tpl_file   = $this->template_dir.'/'.$tplname;

        $tplnames = preg_split("/[\/\\\\]/", $tplname);
        $comfile = str_ireplace('.tpl', '_'.$this->get_compile_pwd().'.php', join('__', $tplnames));
        $comfile = str_replace('..', '__', $comfile);
        $this->compile_file = str_replace("\\", '/', $this->compile_dir).'/'.$comfile;
        $this->compile_file = preg_replace('#[/]+#', '/', $this->compile_file);
        
        $this->cache_file = $this->cache_dir.'/'.preg_replace("/\.([^\.]*)$/U", ".html", $tplname);
        
        //使用缓存，并且缓存可用时直接返回缓存
        if( $this->is_caching )
        {
            if( file_exists($this->cache_file) && time() - filemtime($this->cache_file) < $this->cache_lifetime )
            {
                $infos['cache_data'] = file_get_contents($this->cache_file);
            }
            else
            {
                $infos['save_cache'] = true;
            }
        }
        
        //没有缓存html数据时检查是否需要编译
        if( ($infos['cache_data']=='' && file_exists($this->tpl_file)) || $this->force_compile )
        {
            if(  $this->force_compile || !file_exists($this->compile_file)  || filemtime($this->compile_file) < filemtime($this->tpl_file) )
            {
                require_once QUICKTAG.'/cls_quicktag_compiler.php';
                $compiler = new cls_quicktag_compiler();
                $this->set_property( $compiler );
                $rs = $compiler->compiler($this->tpl_file, $this->compile_file);
                if( $rs )
                {
                    $infos['tplcache'] = $this->compile_file;
                }
            }
            else
            {
                $infos['tplcache'] = $this->compile_file;
            }
        }
        return $infos;
    }
    
   /**
    *  显示一个模板
    *  
    *  @parem string $tplname 模板名称（相对于 this->template_dir）
    */
    public function display($tplname)
    {
        if( $this->is_rewrite )
        {
            echo $this->fetch( $tplname );
        }
        else
        {
            $infos = $this->fetch_resource_info($tplname);
            if( file_exists( $infos['tplcache'] ) )
            {
                require $infos['tplcache'];
            } else {
                exit("display error: $tplname not found! ");
            }
        }
    }
    
   /**
    *  获取内容
    *  
    * @parem string $tplname 模板名称（相对于 this->template_dir）
    * @return string
    */
    public function fetch( $tplname )
    {
        $infos = $this->fetch_resource_info($tplname);
        if( file_exists( $infos['tplcache'] ) )
        {
            //检测原有的输出
            $old_content = ob_get_contents();
            if( !empty($old_content) ) {
                ob_end_clean();
            }
            //处理当前模板的输出
            ob_start();
            require $infos['tplcache'];
            $content = ob_get_contents();
            ob_end_clean();
            if( $this->is_rewrite ) {
                pub_rewrite::convert_html($content);
            }
            //恢复原来的上下文
            if( !empty($old_content) ) {
                ob_start();
                echo $old_content;
            }
            return $content;
        }
        else
        {
            return '';
        }
    }
    
   /**
    *  保存输出内容为html
    *  
    * @parem string $tplname 模板名称（相对于 this->template_dir）
    * @parem string $tohtml 保存为文件名（绝对路径）
    * @return bool
    */
    public function save_html($tplname, $tofile)
    {
        $content = $this->fetch( $tplname );
        if( $content !== false )
        {
            $fp = fopen($tofile, 'w') or dir("Write file {$tofile} error!");
            fwrite($fp, $content);
            fclose($fp);
            return true;
        }
        return false;
    } 
}
