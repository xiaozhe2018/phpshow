<?php
/**
 * 公共上传类
 * 如果没有自定义附件目录及文件名需求，请使用：
 * pub_media_dlg类里的上传方法进行上传
 * 如果因为某种原因，必须使用这个类进行上传多个序列文件，可以用 file_1、file_2...的格式定义表单名 
 * @since 2013-11-13
 * $Id$
 */
class cls_upload
{
    protected $config = array ();
    protected $data  = array();

    /**
     * 初始化配置
     * 这个上传类，不适用于表单名为数组的情况
     */
    public function __construct( $config = array() )
    {
        //上传文件路径
        $this->config['upload_path'] = $config ['upload_path'];
        
        //文件类型限制
        $this->config['type_limit']  = $config['type_limit'];
        
        //文件大小限制
        $this->config['size_limit']  = $config ['size_limit'];
    }

   /**
    * 上传文件
    */
    public function upload($field = 'file')
    {
        //文件上传表单值检查
        if ( !isset (req::$files[$field]) )
        {
            return false;
        }
        if ( !req::is_upload_file ( $field ) )
        {
            $error = (!isset( req::$files[$field] ['error'] )) ? 4 : req::$files[$field] ['error'];
            switch ($error)
            {
                case 1 :
                    $log = 'UPLOAD_ERR_INI_SIZE'; // 其值为 1，上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值。
                    break;
                case 2 :
                    $log = 'UPLOAD_ERR_FORM_SIZE'; // 其值为 2，上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值。
                    break;
                case 3 :
                    $log = ' UPLOAD_ERR_PARTIAL'; // 其值为 3，文件只有部分被上传。
                    break;
                case 4 :
                    $log = 'UPLOAD_ERR_NO_FILE'; // 其值为 4，没有文件被上传。
                    break;
                case 6 :
                    $log = 'UPLOAD_ERR_NO_TMP_DIR'; // 其值为 6，找不到临时文件夹。PHP 4.3.10 和 PHP 5.0.3 引进
                    break;
                case 7 :
                    $log = 'UPLOAD_ERR_CANT_WRITE'; // 其值为 7，文件写入失败。PHP 5.1.0 引进。
                    break;
                case 8 :
                    $log = 'UPLOAD_ERR_EXTENSION'; // File upload stopped by extension. PHP 5.2.0 引进.
                    break;
                default:
                    $log = 'UNKNOWN_ERROR'; // 未知错误
                    break;
            }
            throw new Exception ( '401:::很抱歉，文件上传失败，原因是：{$log}，请重试！' );
        }
        
        //相关变量
        $this->data['file_ext']       = req::get_shortname ( $field );
        $this->data['file_temp']      = req::$files[$field]['tmp_name'];
        $this->data['mime_limit']     = req::$files[$field]['type'];        
        $this->data['file_name']      = date('Ymd').'/'.pub_super2dec32::get_super_s(date('YmdHis')).'-'.pub_super2dec32::get_super_s(mt_rand(100000, 999999)) . '.' . $this->data['file_ext'];
        $this->data['file_size']      = intval(req::$files[$field]['size']);
        //创建日期目录
        if( !is_dir( $this->config['upload_path'].'/'.date('Ymd') ) ) {
            mkdir($this->config['upload_path'].'/'.date('Ymd'), 0777);
        }
        $this->data['file_path_name'] = $this->config['upload_path'] . '/' . $this->data ['file_name'] ;
        
        //相关检查
        $this->is_upload_path();
        $this->is_type_limit();
        $this->is_size_limit();
        
        //建立文件，这步没有必要，因为已经检测了目录的存在性
        //util::put_file($this->data['file_path_name'], '');
       
        //移动文件
        if ( !req::move_upload_file( $field , $this->data['file_path_name']) )
        {
            throw new Exception ( '501:::很抱歉，文件转移失败，请重新上传！' );
        }

        //检测文件可疑内容
        if( !$this->_safe_check( $this->data['file_path_name'] ) )
        {
            @unlink ( $this->data['file_path_name'] );
            throw new Exception ( '501:::对不起，文件没有通过安全检测！' );
        }
        
        //删除临时文件
        @unlink ( $this->data['file_temp'] );
        return $this->data['file_name'];
    }
    
   /**
    * 检测文件合法性
    */
    protected function _safe_check( $upfile )
    {
        if( preg_match("/\.php$/i", $upfile) )
        {
            return false;
        }
        if( preg_match("/\.(jpg|gif|png)$/i", $upfile) )
        {
            $arr = @getimagesize( $upfile );
            if( !is_array($arr) )
            {
                return false;
            }
        }
        $str = @file_get_contents( $upfile );
        if( empty($str) )
        {
            return false;
        } else {
            return !preg_match("/<\?php[\s]/i", $str);
        }
     }

   /**
    * 判断文件是否超出上传单文件大小限制
    */
    protected function is_size_limit()
    {
        //size_limit = 0 时没有任何限制
        if( empty($this->config['size_limit']) )
        {
            return true;
        }
        if ($this->data ['file_size'] > $this->config['size_limit'])
        {
            throw new Exception ( '201:::抱歉，上传文件超出最大上传大小限制' );
        }
        else
        {
            return true;
        }
    }

    /**
     * 判断文件类型是否允许上传
     */
    protected function is_type_limit()
    {
        // 允许上传任意文件的情况
        if ($this->config['type_limit'] == '*')
        {
            return true;
        }
        else
        {
            $type_limit =  $this->config['type_limit'];
            if( in_array( $this->data['file_ext'], $type_limit, true ) === false)
            {
                throw new Exception ( '301:::抱歉，上传文件类型不允许上传！' );
            }
        }
        return true;
    }

   /**
    * 验证文件上传路径是否合法
    */
    protected function is_upload_path()
    {
        $log = '';
        //上传路径是否为空
        if( empty ($this->config['upload_path']) )
        {
            $log = '上传路径是否为空！';
        }
        //上传路径是否存在
        if ( !file_exists ( $this->config['upload_path'] ) )
        {
            $log = '上传文件存放目录不存在！';
        }
        //上传路径是否可写
        if (! is_writable ( $this->config['upload_path'] ))
        {
            $log = '上传文件存放目录不可写';
        }
        if ( !empty ( $log ) )
        {
            throw new Exception ( '101:::很抱歉，上传路径存在错误！' );
        }
        return true;
    }
}
