<?php
if( !defined('PHPBONE') ) exit('Request Error!');
/**
 * 后台管理控制器
 *
 * @version $Id$
 */
class ctl_index
{
   /**
    * 主入口
    */
    public function index()
    {
        $t1 = microtime(true);
        $menu = preg_replace('/,$/', '', mod_admin_menu::parse_menu() );
        tpl::assign('menu',  $menu );
        tpl::assign('user', cls_auth::$user->fields );
        tpl::display('index.tpl');
        exit();
    }

   /**
    * 用户登录
    */
    public function login()
    {
       $rs = 0;
       $errmsg = '';
       $gourl = req::item('gourl', '');
       if(req::item('username', '') != '' && req::item('password', '') != '')
       {
          $validate = req::item('validate', '');
          $vdimg = new cls_securimage();
          if( empty($validate) || !$vdimg->check($validate) )
          {
              $errmsg = 'Error：请输入正确的验证码！';
          }
          else
          {
             try
             {
                 $rs = cls_auth::$user->check_user(req::item('username'), req::item('password'));
             }
             catch ( Exception $e )
             {
                 $errmsg = 'Error：'.$e->getMessage();
             }
             if( $rs == 1 )
             {
                 bone::$auth->auth_user( cls_auth::$user->fields );
                 $jumpurl = empty($gourl) ? '?ct=index' : $gourl;
                 cls_msgbox::show('成功登录', '成功登录，正在重定向你访问的页面', $jumpurl);
                 exit();
             }
          }
       }
       tpl::assign('gourl', $gourl );
       tpl::assign('errmsg', $errmsg );
       tpl::display('login.tpl');
       exit();
    }

   /**
    * 系统消息
    */
    public function adminmsg()
    {
        exit('ok');
    }

   /**
    * 退出
    */
    public function loginout()
    {
        bone::$auth->logout();
        cls_msgbox::show('注销登录', '成功退出登录！', './');
        exit();
    }
    
    /**
     * 验证码图片
     */
     public function validate_image()
     {
        $vdimg = new cls_securimage(4, 120, 24);
        bone::$is_ajax = true;
        $vdimg->show();
     }

}
