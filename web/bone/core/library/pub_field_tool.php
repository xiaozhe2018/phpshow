<?php
if( !defined('PHPBONE') ) exit('Request Error!');
/**
 * 调用编辑器，附件浏览按钮或对特殊字段处理等
 *
 * @since 2013/10/20
 * @author itprato<2500875@qq>
 * @version $Id$  
 */
class pub_field_tool
{
    
    //编辑器url路径
    public static $editor_base_path = '../../static/ueditor';
    
    protected static $_btn_num = 1;
    
    protected static $_has_load_editor = false;
    
    //管理路径（用于配置图片上传接口）
    public static $admin_path = 'ivalimb';
    
   /**
    * 加载编辑器基本资源
    */
    protected static function load_editor_base()
    {
        if( self::$_has_load_editor )  return '';
        $editor_base_path = self::$editor_base_path;
        $res = "<script type=\"text/javascript\" src=\"{$editor_base_path}/ueditor.config.js\"></script>
        <script charset=\"utf-8\" src=\"{$editor_base_path}/ueditor.all.min.js\"></script>
        <script type=\"text/javascript\" src=\"{$editor_base_path}/lang/zh-cn/zh-cn-u8.js\"></script>";
        self::$_has_load_editor = true;
        return $res;
    }
    
    /**
     * 获取一个ue编辑器
     * 使用编辑器，项目中应该包含文件  ctl_ue_editor.php（管理操作目录的控制器，用于上传附件接口）
     * @param $field 字段名
     * @param $dfvalue 默认内容
     * @param $dftoolbar 工具栏要布局  Full | Base | Small
     * @param $height 编辑器高度
     * @param $width  编辑器宽度
     * @return $html
     */
     public static function get_editor($field, $dfvalue, $dftoolbar='Base', $height='300px', $width='100%', $config='')
     {
        $restr = self::load_editor_base();
        if( !preg_match("/(%|px)/i", $height) ) {
            $height = $height.'px';
        }
        if( !preg_match("/(%|px)/i", $width) ) {
            $width = $width.'px';
        }
        $admin_path = self::$admin_path;
        $restr .= "
        <textarea name=\"{$field}\" id=\"{$field}\" style=\"width:{$width};height:{$height};\" class=\"txtarea\">{$dfvalue}</textarea>
        <script type=\"text/javascript\">
    	    UE.getEditor('{$field}', {
                theme:'default',
                lang:'zh-cn-u8',
                serverUrl: window.UEDITOR_CONFIG.UEDITOR_HOME_URL + '../../../bone/{$admin_path}/index.php?ct=ue_editor',
                imageUrl: window.UEDITOR_CONFIG.UEDITOR_HOME_URL + '../../../bone/{$admin_path}/index.php?ct=ue_editor&ac=editor_upload',
                allowDivTransToP:false,
                enterTag:'br',
            });
        </script>";
        return $restr;
     }
     
    /**
     * 获取一个'浏览...'站内文件对话框
     * @param $field_id            窗口返回内容字段的id
     * @param $addontype           附件类型  images/media/soft
     * @param $win_arg             window.open 窗口的参数
     * @param $band_function_name  处理窗口返回事件的js函数名
     * @param $band_function       处理窗口返回事件的js函数
     * @return $html
     */
     public static function get_dlg_btn( $field_id, $addontype='images', $win_arg='', $band_function_name='', $band_function = '' )
     {
         $html = '';
         if( $win_arg=='' ) {
             $win_arg = '"scrollbars=yes,resizable=yes,statebar=no,width=600,height=400,left=100,top=100"';
         }
         if( $band_function_name=='' )
         {
             $band_function_name = "GetBoneDlgUpload_{$field_id}";
             $band_function = "<script  language='javascript'>
                     function {$band_function_name}( reurl ) {
                        document.getElementById('{$field_id}').value = reurl;
                        if( document.getElementById('preimg_{$field_id}') ) {
                            document.getElementById('preimg_{$field_id}').src    = reurl;
                        }
                     }
                     </script>";
         }
         $html .= $band_function;
         $html .= "<input type='button' name='dlg_btn_".self::$_btn_num."' value='浏览...' cls='dlg_btn' onclick='window.open(\"".self::$editor_base_path."dialog/select_{$addontype}.php?dlg_i={$band_function_name}\", \"dlg_popUpImgWin\", {$win_arg});' />\r\n";
         self::$_btn_num++;
         return $html;
     }
}

