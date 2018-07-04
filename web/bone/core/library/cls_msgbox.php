<?php
if( !defined('PHPBONE') ) exit('Request Error!');
/**
 * 简单对话框类
 * @version $Id$
 */
class cls_msgbox
{
    
    public static $tpl = 'cls_msgbox.tpl';
    
    /**
    * 显示一个简单的对话框
    *
    * @parem $title 标题
    * @parem $msg 消息
    * @parem $gourl 跳转网址（其中 javascript:; 或 空 表示不跳转）
    * @parem $limittime 跳转时间
    *
    * @return void
    *
    */
    public static function show($title, $msg, $gourl='', $limittime=500)
    {
        if($title=='') $title = '系统提示信息';
        $jumpmsg = $jstmp = '';
        //返回上一页
        $ists = false;
        if($gourl=='javascript:;')
        {
            $gourl == '';
        }
        else if($gourl=='-1')
        {
           $ists = true;
           $gourl = "javascript:history.go(-1);";
        }
        //后续操作为一段指定的js(允许换行)
        if( preg_match('/^javascript:/', $gourl) && !$ists )
        {
            $jstmp = preg_replace('/^javascript:/', '', $gourl);
            $gourl = '';
        }
        //正常$gourl不为空的操作(为空时，不进行后续处理)
        else if( $gourl != '' )
        {
            $jumpmsg = "<div class='ct2'><a href='{$gourl}'>如果你的浏览器没反应，请点击这里...</a></div>";
            $jstmp = "setTimeout('JumpUrl()', {$limittime});";
        }
        bone::$instance->app_name = 'system';
        tpl::assign('title', $title);
        tpl::assign('msg', $msg);
        tpl::assign('gourl', $gourl);
        tpl::assign('jumpmsg', $jumpmsg);
        tpl::assign('jstmp', $jstmp);
        tpl::display( self::$tpl );
        exit();
    }
}