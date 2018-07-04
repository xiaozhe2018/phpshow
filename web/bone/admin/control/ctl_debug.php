<?php
if( !defined('PHPBONE') ) exit('Request Error!');
/**
 * 开发调试工具
 * $Id
 */
class ctl_debug
{
   
   /**
    * LURD向导
    */
    public function lurd()
    {
        $opts = array('add' => '增加数据',
                      'edit' => '查看数据',
                      'saveedit' => '更改数据',
                      'delete' => '删除数据');
        if( req::$request_mdthod == 'POST')
        {
            $mod_table = preg_replace('/[^\w]/', '', req::item('mod_table', ''));
            if( empty($mod_table) )
            {
                cls_msgbox::show('系统提示', '必须指定模型表', "-1", 3000);
            }
            $class_name = req::item('class_name', '');
            $front_mod = req::item('front_mod', 1);
            $front_mod_name = req::item('front_mod_name', '');
            if( preg_match('/[^a-z_]/', $class_name) || ($front_mod==1 && preg_match('/[^a-z_]/', $front_mod_name)) )
            {
                cls_msgbox::show('系统提示', '控制器标识和前台模型类名必须为小写字母及下划线组成', "-1", 3000);
            }
            if( file_exists(dirname(__FILE__).'/ctl_'.$class_name.'.php') )
            {
                cls_msgbox::show('系统提示', '要生成的控制器文件已经存在，如果你需要重新生成，请先删除这个文件', "-1", 3000);
            }
            if( $front_mod==1 && file_exists(PATH_MODEL.'/mod_'.$front_mod_name.'.php') )
            {
                cls_msgbox::show('系统提示', '要生成的模型类文件已经存在，如果你需要重新生成，请先删除这个文件', "-1", 3000);
            }
            //分析数据表
            try {
                $tb = cls_lurd::factory( $mod_table );
                if( $tb->primary_key=='' ) {
                    cls_msgbox::show('系统提示', '模型表找不到主键，无法进行处理', "-1", 3000);
                }
            } catch( Exception $e ) {
                cls_msgbox::show('系统提示', '分析模型表失败', "-1", 3000);
            }
            $control_name = trim(preg_replace("/['\"]/", '', stripslashes(req::item('control_name'))));
            $app_name     = trim(preg_replace("/['\"]/", '', stripslashes(req::item('app_name'))));
            $selopts      = req::item('selopts');
            $opts = array('list');
            foreach($selopts as $do => $v)
            {
                if( $do=='add' ) {
                    $opts[] = 'add';
                    $opts[] = 'saveadd';
                }
                else {
                    $opts[] = $do;
                }
            }
            ob_start();
            var_export($opts);
            $optstr = ob_get_contents();
            ob_end_clean();
            $optstr = preg_replace("/[\r\n]/", ' ', $optstr);
            $lock_evens  = "\$evens = {$optstr};\r\n";
            $lock_evens .= "        \$tb->lock_evens( \$evens );";
            $upkey = " `{$tb->primary_key}` =  '{\$id}' ";
            
            $okfile = '';
            $control_tpl = PATH_TEMPLATE.'/template/system/control.class.tpl';
            $mod_tpl     = PATH_TEMPLATE.'/template/system/mod.class.tpl';
            
            //生成控制器文件
            $control_temp = file_get_contents( $control_tpl );
            $rpkeys = array('~control_name~', '~mod_table~', '~class_name~', '//~allow_even~', '~upkey~');
            $rptos  = array($control_name, $mod_table, $class_name, $lock_evens, $upkey);
            $control_temp = str_replace($rpkeys, $rptos, $control_temp);
            if( !isset($tb->fields['sortrank']) ) {
                $control_temp = preg_replace("#//~sort_start~(.*)//~sort_end~#sU", '', $control_temp);
            }
            try {
                $okfile .= '<a href="?ct='.$class_name.'">ctl_'.$class_name.'.php</a><br>';
                file_put_contents(dirname(__FILE__).'/ctl_'.$class_name.'.php', $control_temp);
            } catch( Exception $e ) {
                cls_msgbox::show('系统提示', '生成控制器文件失败，请检查目录是否可写入', "-1", 3000);
            }
            
            //生成模型类文件
            if( $front_mod==1 )
            {
                $mod_temp = file_get_contents( $mod_tpl );
                $rpkeys = array('~control_name~', '~mod_table~', '~front_mod_name~', '~pri_key~');
                $rptos  = array($control_name, $mod_table, $front_mod_name, $tb->primary_key);
                $mod_temp = str_replace($rpkeys, $rptos, $mod_temp);
                try {
                    file_put_contents(PATH_MODEL.'/mod_'.$front_mod_name.'.php', $mod_temp);
                    $okfile .= 'mod_'.$front_mod_name.'.php';
                } catch( Exception $e ) {
                    unlink( dirname(__FILE__).'/ctl_'.$class_name.'.php' );
                    cls_msgbox::show('系统提示', '生成模型类文件失败，请检查目录是否可写入', "-1", 3000);
                }
            }
            
            //生成后台导航菜单
            $need_menu = req::item('need_menu', 1);
            if( $need_menu==1 )
            {
                if( $app_name=='' ) $app_name = $control_name;
                $menu = config::get('admin_menu');
                $app_item  = "<node appname='{$app_name}' ct='{$class_name}'></node>\r\n    ";
                $menu_item = "<item name='{$app_name}' url='' ct='{$class_name}' ac='index' />\r\n      ";
                $menu = str_replace('<tpl>statement</tpl>', $app_item.'<tpl>statement</tpl>', $menu);
                $menu = str_replace('<tpl>commonitem</tpl>', $menu_item.'<tpl>commonitem</tpl>', $menu);
                config::save('admin_menu', addslashes($menu));
            }
            
            //返回成功页面
            tpl::assign('okfile', $okfile);
            tpl::assign('success', 'yes');
            tpl::display('debug.lurd.tpl');
        }
        else
        {
            $tables = mod_admin_debug::get_tables();
            foreach($tables as $tname => $n)
            {
                $cname = preg_replace("/^[^-]+_/", '', $tname);
                if( file_exists(dirname(__FILE__).'/ctl_'.$cname.'.php') || preg_match("/^admin|config$/", $cname) ) {
                    unset( $tables[$tname] );
                }
            }
            tpl::assign('opts', $opts);
            tpl::assign('tables', $tables);
            tpl::display('debug.lurd.tpl');
        }
    }
    
   /**
    * 模板标签测试
    */
    public function tpltest()
    {
        if( req::$request_mdthod == 'POST')
        {
            $code = stripslashes(trim(req::item('code', '')));
            if( $code=='' ) {
                cls_msgbox::show('系统提示', '你没有输入任何内容呀，你马妹...', '');
            }
            bone::$instance->app_name = '';
            $tpl = "../compile/tpltest.tpl";
            file_put_contents(PATH_TEMPLATE.'/template/'.$tpl, $code);
            tpl::display($tpl);
            exit();
        }
        else
        {
            tpl::display('debug.tpltest.tpl');
        }
    }

   /**
    * 数据库文档
    */
    public function dbinfos()
    {
        $prefix = req::item('prefix', '');
        mod_admin_debug::list_table_infos( $prefix );
    }
}
