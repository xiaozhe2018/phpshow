<?php
if( !defined('PHPBONE') ) exit('Request Error!');
/**
 * 媒体管理框模型类
 *
 * @author itprato<2500875@qq>
 * @version $Id$  
 */
class pub_media_dlg
{
    //文件目录设置
    //文件读取目录 = root_dir+upload_url
    public static $root_dir   = PATH_ROOT;
    public static $upload_url = '/uploads';
    
    //图片大小
    public static $img_width  = 1000;
    public static $img_height = 800;
    
    //文件类型设置
    public static $media_types = array(
                                        1 => 'jpg|png|gif|bmp|ico',
                                        2 => 'mp3|avi|mpg|mp4|3gp|flv|rm|rmvb|wmv|swf',
                                        3 => 'zip|7z|rar|gz|bz2|tar|iso|exe|dll|doc|xls|ppt|docx|xlsx|pptx|wps|pdf|psd',
                                    );
    
    //附件子目录（在 $upload_url 里面）
    public static $media_paths = array(
                                        1 => 'images',
                                        2 => 'media',
                                        3 => 'soft',
                                    );
    
    //上传的文件命名规则 %Y(年) %m(月) %d(日) %un 文件名 %s 扩展名 (目录即/符号只允许一重，否则后面的将被忽略)
    public static $name_rule  = '%Y%m%d/%un%a%s';
    
    //文件的默认ico
    public static $default_icos = array();
    
    //文件允许大小
    public static $max_size  = 16000000;
    
    /**
     * 进行一些初始化处理
     */
    private static function _init()
    {
        if( !empty(self::$default_icos) )  return true;
        
        //根据系统配置变量获取相关信息
        if( config::get('attachment_image') != '' ) {
            self::$media_types[1] = config::get('attachment_image');
        }
        if( config::get('attachment_media') != '' ) {
            self::$media_types[2] = config::get('attachment_media');
        }
        if( config::get('attachment_soft') != '' ) {
            self::$media_types[3] = config::get('attachment_soft');
        }
        if( config::get('attachment_size') != '' ) {
            self::$max_size = config::get('attachment_size') * 1024 * 1024;
        }
        if( config::get('site_upload_path') != '' ) {
            self::$upload_url = config::get('site_upload_path');
        }
        
        //文件默认ico
        foreach( self::$media_types as $m => $ext) {
            $exts = explode('|', $ext);
            foreach($exts as $ext) {
                self::$default_icos[$ext] = $m;
            }
        }
    }
    
    /**
     * 读取某文件夹的文件
     * @param $url = '' 相对路径
     * @param $sort     排序方式  name/mtime/size(只对文件有效，目录一直按name排序)
     * @param $media_type     self::$media_types 里的key，0表示全部
     * @return array()
     */                         
     public static function read_dir($url='', $sort='name', $media_type=10)
     {
        self::_init();
        $mediadir = isset(self::$media_paths[$media_type]) ? self::$upload_url.'/'.self::$media_paths[$media_type] : self::$upload_url;
        $data = array('dirs' => array(), 'files' => array(), 'url' => '', 'parent' => $mediadir);
        $urls = self::get_safe_dir($url, $mediadir);
        $data['parent'] = $urls['parent'];
        $data['url']    = $urls['url'];
        $readdir = self::$root_dir.$data['url'];
        $dh = dir( $readdir );
        $tmpfiles = array();
        
        //文件类型
        $allowtype = self::_get_exts($media_type);
        if( $allowtype=='' ) {
            return $data;
        }
        
        //读取目录
        while( $filename = $dh->read() )
        {
            if( $filename[0] == '.' ) continue;
            $true_file = $readdir.'/'.$filename;
            if( is_dir($true_file) ) {
                $data['dirs'][$filename] = $filename;
            } else {
                if( !preg_match('#\.('.$allowtype.')$#i', $filename) )  continue;
                $data['files'][$filename] = self::get_file_info($true_file);
            }
        }
        $dh->close();
        
        //排序
        if( !empty($data['dirs']) ) {
            asort($data['dirs']);
        }
        if( !empty($data['files']) )
        {
            if( $sort=='mtime' || $sort=='size' )
            {
                $tmparr1 = $tmparr2 = array();
                foreach($data['files'] as $filename => $info) {
                    $tmparr1[$filename] = $info[$sort];
                }
                arsort($tmparr1);
                foreach($tmparr1 as $filename => $se) {
                    $tmparr2[$filename] = $data['files'][$filename];
                }
                $data['files'] = $tmparr2;
                unset( $tmparr2 );
                unset( $tmparr1 );
            }
            else
            {
                ksort($data['files']);
            }
        }
        return $data;
     }
     
    /**
     * 返回安全目录(锁定在upload_dir)
     * @param $url = '' 相对路径
     * @return array('url', 'parent')
     */
     public static function get_safe_dir($url='', $basedir='')
     {
         $urls = array('url' => '', 'parent' => '');
         $url = preg_replace("#\./|/\.#", '/', $url);
         $url = preg_replace("#[/]+#", '/', $url);
         //错误目录或根目录
         if( !preg_match('#^'.$basedir.'#i', $url) || $url=='' || $url == $basedir )
         {
            $urls['url']    = $basedir;
            $urls['parent'] = '';
            if( !is_dir(PATH_ROOT.$basedir) ) {
                mkdir(PATH_ROOT.$basedir, 0777);
            }
         }
         else
         {
            $urls['url'] = preg_replace("#/$#", '', $url);
            $urls['parent'] = preg_replace("#/[^/]+$#", '', $urls['url']);
            if( $urls['parent']=='' )  $urls['parent'] = $basedir;
         }
         return $urls;
     }
     
    /**
     * 获取当前允许的文件类型
     * @param $url = '' 相对路径
     * @return string
     */
     private static function _get_exts($media_type)
     {
         if( $media_type==10 ) {
            $allowtype = join('|', self::$media_types);
         } else {
            $allowtypes[] = isset(self::$media_types[$media_type]) ? self::$media_types[$media_type] : self::$media_types;
            $allowtype = join('|', $allowtypes);
        }
        return $allowtype;
     }
     
    /**
     * 上传单个文件
     * @param $url = '' 相对路径
     * @param $media_type 媒体框类型 1 图片 2 多媒体文件 3 软件 10 全部
     * @return array('path', 'filename', 'ext')
     */
     public static function uploadfile($url, $media_type=10, $formname='uploadfile', $use_oldname = false)
     {
         self::_init();
         if( !req::is_upload_file( $formname ) ) {
            throw new Exception("上传文件失败");
         }
         $mediadir = isset(self::$media_paths[$media_type]) ? self::$upload_url.'/'.self::$media_paths[$media_type] : self::$upload_url;
         $upfile = req::get_file_info( $formname );
         $urls = self::get_safe_dir($url, $mediadir);
         //获取文件名，自动生成文件名模式（有同名文件时不覆盖）
         if( !$use_oldname )
         {
             $filenames = self::get_filename($urls['url'], $media_type, $upfile['name']);
             $truename = PATH_ROOT.$filenames['path'].'/'.$filenames['filename'];
             $r = 0;
             while( file_exists($truename) )
             {
                $filenames = self::get_filename($urls['url'], $media_type, $upfile['name']);
                $truename = PATH_ROOT.$filenames['path'].'/'.$filenames['filename'];
                $r++;
                if( $r > 10 ) {
                    throw new Exception("获取文件名失败，请稍后重试");
                    return false;
                }
            }
         }
         //使用原文件名模式（有同名文件时会覆盖已有文件）
         else
         {
             $filenames = self::get_oldname($urls['url'], $media_type, $upfile);
             $truename = PATH_ROOT.$filenames['path'].'/'.$filenames['filename'];
         }
         //保存文件
         req::move_upload_file($formname, $truename);
         if( !file_exists($truename) ) {
            throw new Exception("保存文件失败");
         }
         @chmod($truename, 0777);
         $filenames['size'] = filesize($truename);
         return $filenames;
     }
     
     /**
     * 上传多个文件
     * @param $url = '' 相对路径
     * @param $media_type 媒体框类型 1 图片 2 多媒体文件 3 软件 10 全部
     * @param $formname 文件数组名（文件表单用 $formname[] 的形式定义名称，如果用 $formname_$n 这样定义，请使用 uploadfile 进行处理）
     * @return array(array('path', 'filename', 'ext'))
     */
     public static function uploadfiles($url, $media_type=10, $formname='uploadfile')
     {
         self::_init();
         $mediadir = isset(self::$media_paths[$media_type]) ? self::$upload_url.'/'.self::$media_paths[$media_type] : self::$upload_url;
         $urls = self::get_safe_dir($url, $mediadir);
         $files = array();
         //找不到表单，返回空数组
         if( empty(req::$files[$formname]['tmp_name']) ) {
            return $files;
         }
         //遍历上传
         $_file_names = array();
         foreach( req::$files[$formname]['tmp_name'] as $item => $uploadfile )
         {
            if( !req::is_upload_file( $formname, $item ) ) {
                $refiles[ $item ] = array();
                continue;
            }
            //获取文件名
            if( empty($_file_names) )
            {
                try {
                    $_file_names = self::get_filename($urls['url'], $media_type, req::$files[$formname]['name'][$item], true);
                } catch( Exception $e ) {
                    $files[ $item ] = array();
                    continue;
                }
            }
            $filenames = $_file_names;
            $filenames['filename'] = $_file_names['filename'].'_'.$item.'.'.$_file_names['ext'];
            $truename  = PATH_ROOT.$filenames['path'].'/'.$filenames['filename'];
            //保存文件
            req::move_upload_file($formname, $truename, $item);
            if( !file_exists($truename) ) {
                $files[ $item ] = array();
            } else {
                $files[ $item ] = $filenames;
                @chmod($truename, 0777);
            }
         }
         return $files;
     }
     
    /**
     * 获取单个文件的文件名
     * @param $url = ''   相对路径
     * @param $sfilename  原始文件名
     * @return array('url', 'parent')
     */
     public static function get_filename($url, $media_type, $sfilename, $noext=false)
     {
         self::_init();
         $mediadir = isset(self::$media_paths[$media_type]) ? self::$upload_url.'/'.self::$media_paths[$media_type] : self::$upload_url;
         $urls   = self::get_safe_dir($url, $mediadir);
         $files  = array('path' => $mediadir, 'filename' => '');
         $sname  = strtolower(substr($sfilename, -3, 3));
         $_sname =  $noext ? '' : '.'.$sname;
         $rps  = array('%Y', '%m', '%d', '%un', '%s');
         $rpto = array(date('Y'), date('m'), date('d'), pub_super2dec32::get_unique(15), $_sname);
         $allowtype = self::_get_exts($media_type);
         if( !preg_match('#^('.$allowtype.')$#', $sname) ) {
            throw new Exception("文件类型不允许");
         }
         $filename = str_replace($rps, $rpto, self::$name_rule);
         $dpos = strpos($filename, '/');
         if( $dpos > 1 ) {
            $files['path'] = $mediadir.'/'.substr($filename, 0, $dpos);
            $files['path'] = preg_replace('#[/]{1,}#', '/', $files['path']);
            $filename = substr($filename, $dpos+1, strlen($filename) - $dpos);
         }
         $files['filename'] = preg_replace("#[^\w\.]#", '', $filename);
         if( !is_dir(PATH_ROOT.$files['path']) ) {
            mkdir(PATH_ROOT.$files['path'], 0777);
         }
         $files['ext'] = $sname;
         return $files;
     }
     
     /**
     * 获取单个文件的文件名(使用上传的原始文件名[不允许存在中文])
     * @param $url = ''   相对路径
     * @param $sfilename  原始文件名
     * @return array('url', 'parent')
     */
     public static function get_oldname($url, $media_type, $upfile)
     {
         $sname  = strtolower(substr($upfile['name'], -3, 3));
         $allowtype = self::_get_exts($media_type);
         if( !preg_match('#^('.$allowtype.')$#', $sname) ) {
            throw new Exception("文件类型不允许");
         }
         //文件名必须为非中文
         if( preg_match("/[^0-9a-zA-Z_\.,;-]/", $upfile['name']) ) {
            return self::get_filename($url, $media_type, $upfile['name']);
         }
         $mediadir = isset(self::$media_paths[$media_type]) ? self::$upload_url.'/'.self::$media_paths[$media_type] : self::$upload_url;
         $urls   = self::get_safe_dir($url, $mediadir);
         $files['filename'] = $upfile['name'];
         $files['path'] = $urls['url'];
         $files['ext'] = $sname;
         return $files;
     }
     
    /**
     * 返回文件的ico(扩展名.gif，如果不存在，则用m+type.gif)
     * @param $filename
     * @param $media_type
     * @return string
     */
     public static function get_ico($filename, $media_type)
     {
          self::_init();
          $icopath = dirname(__FILE__).'/img';
          $ext = strtolower(substr($filename, -3, 3));
          if( file_exists($icopath.'/'.$ext.'.gif') ) {
              return 'img/'.$ext.'.gif';
          } else if( isset(self::$default_icos[$ext]) ) {
             return 'img/m'.self::$default_icos[$ext].'.gif';
          }
          else {
              return 'img/m'.$media_type.'.gif';
          }
     }
     
    /**
     * 读取某个文件的属性
     * @param $filename
     * @return array('mtime', 'size', 'ksize')
     */
     public static function get_file_info( $filename )
     {
         $fileinfo['mtime']  = filemtime($filename);
         $fileinfo['size']   = filesize($filename);
         $fileinfo['ksize']  = sprintf('%0.1f', $fileinfo['size'] / 1024);
         return $fileinfo;
     }                            
        
}