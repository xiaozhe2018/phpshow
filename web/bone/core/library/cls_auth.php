<?php
if( !defined('PHPBONE') ) exit('Request Error!');
/**
 * 管理员权限控制类
 * @version $Id$
 *
 */
@header('Cache-Control:private');
class cls_auth
{
    //当前访问的应用池
    public  $pool_name = '';
    
    //权限配置串
    private $_cache_cfg_prefix   = 'cls_auth_cfg';
    
    //session 和 cookie 的前缀
    private  $_auth_hand = 'auth_';
    
    //用户信息接口类
    private $_user_handler = '';
    public static $user = null;
    
    //分析处理后的权限配置数组
    public $pur_configs = array();
    
    //配置权限文件键值
    private $_auth_key = 'admin_df_auth';
    
    //用户数值id
    public  $uid = 0;
    
    //用户隶属的应用池
    public  $user_pools = '';
    
    //用户隶属组， 用 pool_name1-group1,pool_name2-group2 这样分开
    public  $user_groups = '';
    
    //用户单独的权限
    public $user_private_purviews = '';
    
    //用户权限模块
    public  $user_purview_mods = '';
    
    //手工指定登录后跳转到的url
    public  $return_url = '';
    
    //当前类的实例
    public static $instance = null;

    /**
     * 构造函数，根据池的初始化检测用户登录信息
     * @parem $_user_handler      用户信息接口
     * @parem $_auth_key       权限配置信息key
     * @parem $pool_name         应用池名称
     */
    function __construct($_user_handler, $_auth_key, $pool_name)
    {
         $this->_auth_key  = $_auth_key;
         $this->_user_handler = $_user_handler;
         $this->pur_configs = $this->get_config( $this->_auth_key );
         
         $this->cookiepwd = $GLOBALS['config']['cookie_pwd'];
         if( !isset($this->pur_configs['pools'][$pool_name]) )
         {
             bone::fatal_error( 'cls_auth', "Setting `{$pool_name}` is not Found! page: ".util::get_cururl() );
         }
         $this->pool_name = $pool_name;
         
         //如果用户已经登录，获取用户ID信息
         if($this->pur_configs['pools'][$pool_name]['auttype']=='session')
         {
             $this->uid = isset($_SESSION[$this->_auth_hand.$this->pool_name.'_uid']) ? $_SESSION[$this->_auth_hand.$this->pool_name.'_uid'] : 0;
         }
         else if($this->pur_configs['pools'][$pool_name]['auttype']=='cookie')
         {
             $this->uid = ($this->get_cookie($this->pool_name.'_uid')=='') ? 0 : intval($this->get_cookie($this->pool_name.'_uid'));
         }
         //没指定认证类型的情况是public应用，并且需要与其它应用池混编(仅从cookie或session中查询中用户信息由具体模块自行处理)
         else
         {
             $this->uid = isset($_SESSION[$this->_auth_hand.$this->pool_name.'_uid']) ? $_SESSION[$this->_auth_hand.$this->pool_name.'_uid'] : 0;
             $this->uid = ($this->get_cookie($this->pool_name.'_uid')=='') ? 0 : intval($this->get_cookie($this->pool_name.'_uid'));
         }
         
         $this->_get_userinfos( $this->uid );
         
         self::$instance = $this;
         
    }

   /**
    * 设置控制器路由的url
    */
    public function set_control_url( $url )
    {
        $this->pur_configs[$this->pool_name]['control'] = $url;
    }

   /**
    * 获得控制器路由的url
    */
    public function get_control_url( )
    {
        return $this->pur_configs[$this->pool_name]['control'];
    }

     /**
      * 检测权限
      * @parem $mod
      * @parem $action
      * @parem backtype 返回类型， 1--是由权限控制程序直接处理
      *                            2--是只返回结果给控制器(结果为：1 正常，0 没登录，-1 没组权限， -2 没应用池权限)
      *
      * @return int  对于没权限的用户会提示或跳转到 ct=login
      *
      */
      public function check_purview($mod, $action, $backtype='1')
      {
           $rs = 0;
           //检测应用池开放权限的模块
           $public_mod = isset($this->pur_configs['pools'][$this->pool_name]['public'][$mod]) ? 
                         $this->pur_configs['pools'][$this->pool_name]['public'][$mod] : array();
           //检测开放控制器和事件
           if( !empty($this->pur_configs['pools'][$this->pool_name]['public']) && 
               ( $this->pur_configs['pools'][$this->pool_name]['public']=='*' || in_array($action, $public_mod) || in_array('*', $public_mod) ) )
           {
               $rs = 1;
           }
           //未登录用户
           else if( empty($this->uid) )
           {
               $rs = 0;
           }
           //具体权限检测
           else
           {
                  //确定是否具有应用池权限
                  $pools = explode(',', $this->pur_configs['pools'][$this->user_pools]['allowpool']);
                  $pools[] = $this->user_pools;
                  if( !in_array($this->pool_name, $pools) )
                  {
                       $rs = -2;
                  }
                  else
                  {
                    //检测池保护开放控制器和事件（即是登录用户允许访问的所有公共事件）
                    $protected_mod = isset($this->pur_configs['pools'][$this->pool_name]['protected'][$mod]) ? $this->pur_configs['pools'][$this->pool_name]['protected'][$mod] : array();
                    if (   !empty($this->pur_configs['pools'][$this->pool_name]['protected']) && 
                          ( $this->pur_configs['pools'][$this->pool_name]['protected']=='*' || in_array($action, $protected_mod) || in_array('*',     $protected_mod) ) )
                    {
                       $rs = 1;
                    }
                    else
                    {
                        if( empty($this->user_private_purviews) )
                        {
                             //检测用户在当前池的私有权限
                             if( $this->user_purview_mods != '#' )
                             {
                                 $this->user_private_purviews = isset($this->user_purview_mods[$this->pool_name]) ? $this->user_purview_mods[$this->pool_name] : '';
                             }
                             else
                             {
                                 $this->user_private_purviews = '#';
                             }
                        }
                        //设定单独权限的用户
                        if( $this->user_private_purviews=='#' )
                        {
                               $rs = -1;
                        }
                        else if( $this->user_private_purviews=='*' )
                        {
                               $rs = 1;
                        }
                        else
                        {
                              if(    is_array($this->user_private_purviews) && isset($this->user_private_purviews[$mod]) && 
                                   ( in_array($action, $this->user_private_purviews[$mod]) ||  in_array('*', $this->user_private_purviews[$mod]) ))
                              {
                                    $rs = 1;
                              } else {
                                    $rs = -1;
                              }
                        }
                   }
                }
          }
          //返回检查结果
          if( $backtype==2 )
          {
                return $rs;
          }
          //直接处理异常
          else
          {
                //正常状态
                if($rs==1)
                {
                      return true;
                }
                //异常状态
                else if($rs==-1)
                {
                      cls_msgbox::show('组权限限制', '权限不足, 对不起，你没权限执行本操作！', '');
                      exit();
                }
                else if($rs==-2)
                {
                      $jumpurl = $this->get_control_url();
                      cls_msgbox::show('组权限限制', '你没有在这个组的应用进行操作的权限！', '');
                      exit();
                }
                else
                {
                     $jumpurl = $this->get_control_url();
                     //echo $jumpurl;exit();
                     header("Location:$jumpurl");
                     exit();
                }
          }
     }

   /**
    * 获得用户允许访问的模块的信息
    *
    * @return bool
    *
    */
    protected function _check_purview_mods()
    {
        $rs = array();
        $userGroups = explode(',', $this->user_groups);
        if( !is_array($userGroups) )
        {
            $rs = '#';
        }
        foreach($userGroups as $userGroup)
        {
            $userGroup = preg_replace("/[^\w]/", '', $userGroup);
            list($poolname, $gp) = explode('_', $userGroup);
            if( isset($this->pur_configs['pools'][$poolname]['private'][$gp]['allow']) )
            {
                $rs[$poolname] = $this->pur_configs['pools'][$poolname]['private'][$gp]['allow'];
            }
        }
        if( !is_array($rs) )
        {
            $rs = '#';
        }
        return $rs;
    }

   /**
    * 注销登录
    */
    public function logout()
    {
        if( !empty($_SESSION[$this->_auth_hand.$this->pool_name.'_uid']) ) {
            $_SESSION[$this->_auth_hand.$this->pool_name.'_uid'] = '';
            session_destroy();
        }
        $this->_drop_cookie(session_id());
        $this->_drop_cookie($this->pool_name.'_uid');
        return true;
    }
     
   /**
    * 把指定用户保持登录状态
    * @parem $rows  用户信息(如果用户id不是uid,则需要自行赋予一个uid键的值)
    * @parem $keeptime 登录状态保存时间
    * @return bool
    */
    public function auth_user( &$row, $keeptime=86400 )
    {
        if( !is_array( $row ) || !isset($row['uid']) )
        {
            return false;
        }
        self::$user->save_login_history($row, 1);
        $this->_put_logininfo($row, $keeptime);
        $this->uid = $row['uid'];
        $this->_get_userinfos( $this->uid );
        return true;
    }
    
    /**
     * 获取用户信息
     * @parem $uid
     */
    protected function _get_userinfos( $uid )
    {
        //获取用户信息
        //if( $this->uid==0 )  return false;
        self::$user = new $this->_user_handler( $this->uid );
        if( !empty( self::$user->fields ) )
        {
            $this->user_pools              = self::$user->pools;
            $this->user_groups             = self::$user->groups;
            $this->user_private_purviews   = self::parse_private(trim(self::$user->get_purviews()));
            $this->user_purview_mods  = $this->_check_purview_mods();
        }
    }
    
    /**
     * 获取用户信息
     * @parem $uid
     */
    public function get_userinfos()
    {
        if( $this->uid==0 )  {
            return array();
        } else {
            return self::$user->fields;
        }
    }
   
   /**
    * 保存登录信息
    */
    protected function _put_logininfo(&$row, $keeptime=86400)
    {
        $ltime = time();
        $this->uid = $row['uid'];
        if($this->pur_configs['pools'][$this->pool_name]['auttype']=='session')
        {
            $_SESSION[$this->_auth_hand.$this->pool_name.'_uid']  = $this->uid;
            $this->_put_cookie(session_id(), session_id(), $keeptime, false);
        }
        $this->_put_cookie($this->pool_name.'_uid', $this->uid, $keeptime);
        return $ltime;
    }

    /**
     * 保存一个cookie值
     * $key, $value, $keeptime
     */
    protected function _put_cookie($key, $value, $keeptime=0, $encode=true)
    {
         $keeptime = $keeptime==0 ? null : time()+$keeptime;
         setcookie($this->_auth_hand.$key, $value, $keeptime, '/', COOKIE_DOMAIN);
         if($encode)
         {
            setcookie($this->_auth_hand.$key.'_bone', substr(md5($this->cookiepwd.$value), 0, 24), $keeptime, '/', COOKIE_DOMAIN);
         }
    }

   /**
    * 删除cookie值
    * @parem $key
    */
    protected function _drop_cookie($key, $encode=true)
    {
        setcookie($this->_auth_hand.$key, '', time()-360000, '/', COOKIE_DOMAIN);
        if($encode)
        {
            setcookie($this->_auth_hand.$key.'_bone', '', time()-360000, '/', COOKIE_DOMAIN);
        }
    }

   /**
    * 获得经过加密对比的cookie值
    * @parem $key
    */
    public function get_cookie($key, $encode=true)
    {
         $key = $this->_auth_hand.$key;
         if( !isset($_COOKIE[$key]) )
         {
              return '';
         }
         else
         {
              if($encode)
              {
                 $epwd = substr( md5($this->cookiepwd.$_COOKIE[$key]), 0, 24 );
                 if( !isset($_COOKIE[$key.'_bone']) ) return '';
                 else return ($_COOKIE[$key.'_bone'] != $epwd ) ? '' : $_COOKIE[$key];
              }
              else
              {
                 return ($_COOKIE[$key.'_bone'] != $epwd ) ? '' : $_COOKIE[$key];
              }
         }
     }

   /**
    *  保存管理日志
    *  @parem $user_name 管理员登录id 
    *  @parem $msg 具体消息（如有引号，无需自行转义）
    *  @return bool
    */
    public static function save_admin_log($user_name, $msg)
    {
        $user_name = addslashes( $user_name );
        $msg = addslashes( $msg );
        $url = '?ct='.bone::$ct.'&ac='.bone::$ac;
        foreach(req::$forms as $k => $v)
        {
            if( preg_match('/pwd|password|sign|cert/', $k) || $k=='ct' || $k=='ac' ) {
                continue;
            }
            $nstr = "&{$k}=".(is_array($v) ? 'array()' : $v);
            if( strlen($url.$nstr) < 100 ) {
                $url .= $nstr;
            } else {
                break;
            }
        }
        $do_url  = addslashes( $url );
        $do_time = time();
        $do_ip   = util::get_client_ip();
        $sql = "Insert Into `#PB#_admin_oplog`(`user_name`,`msg`,`do_time`,`do_ip`,`do_url`) 
                             Values('{$user_name}','{$msg}','{$do_time}','{$do_ip}','{$do_url}');";
        $rs = db::query( $sql );
        return $rs;
    }
    
    /**
    * 获得配置数组
    */
    public function get_config( $_auth_key )
    {
        return $this->_parse_config( config::get( $_auth_key ) );
    }

   /**
    * 获得用户的所有权限信息
    * @parem $uid        用户ID
    * @parem $poolname   应用池
    * @parem $gp         应用池里的组
    * @return array | string
    */
    public function get_user_groups($uid, $poolname, $gp)
    {
        $upurview = cache::get($this->_cache_cfg_prefix.'_upurview', $uid);
        if( $upurview === false )
        {
            $upurview = db::get_one("Select * From `#PB#_admin_purview` where `admin_id`='{$uid}' ");
            cache::set($this->_cache_cfg_prefix.'_upurview', $uid, $upurview);
        }
        $gstr = '';
        if( empty($upurview['purviews']) )
        {
            $gpall = explode(',', $gp);
            foreach($gpall as $gp)
            {
                list($p, $g) = explode('_', $gp);
                if( $p==$poolname && isset($this->pur_configs['pools'][$poolname]['private'][$g]['allow']) )
                {
                    $all = $this->pur_configs['pools'][$poolname]['private'][$g]['allow'];
                    if( empty($all) )
                    {
                        continue;
                    } else if($all=='*') {
                        $gstr = '*';
                        break;
                    } else {
                        $gstr .= ($gstr=='' ? '' : ',').$this->_gps_str($all);
                    }
                }
            }
            //echo $uid,'|', $poolname,'|', $gp;
            //exit();
        }
        else
        {
            $gstr = $upurview['purviews'];
        }
        $groups = self::parse_private($gstr);
        return $groups;
    }
    
   /**
    * 更新用户组配置
    */
    public function save_config($cfg_arr, $_auth_key)
    {
        $new_config = '';
        foreach($cfg_arr['pools'] as $k => $pools)
        {
            if( empty($pools) || $k=='' ) continue;
            $new_config .= "<pools:{$k} name=\"{$pools['name']}\" allowpool=\"{$pools['allowpool']}\" auttype=\"{$pools['auttype']}\" login_control=\"{$pools['control']}\">\n\n";
            $public_ctl = $this->_gps_str($pools['public']);
            $protected_ctl = $this->_gps_str($pools['protected']);
            $new_config   .= "    <!-- //公开的控制器，不需登录就能访问 -->\n";
            $new_config   .= "    <ctl:public>{$public_ctl}</ctl:public>\n\n";
            $new_config   .= "    <!-- //保护的控制器，当前池会员登录后都能访问 -->\n";
            $new_config   .= "    <ctl:protected>{$protected_ctl}</ctl:protected>\n\n";
            $new_config   .= "    <!-- //私有控制器，只有特定组才能访问 -->\n";
            $new_config   .= "    <ctl:private>\n";
            foreach($pools['private'] as $gp => $gps)
            {
                $private_ctl = $this->_gps_str($gps['allow']);
                $new_config .= "        <{$gp} name=\"{$gps['name']}\">{$private_ctl}</{$gp}>\n";
            }
            $new_config .= "    </ctl:private>\n\n";
            $new_config .= "</pools:{$k}>\n\n";
        }
        
        config::save($_auth_key, addslashes($new_config) );
        
        return true;
    }
    
    /**
     *  分析配置
     */
     protected function _parse_config( $xmlstr )
     {
        $content = preg_replace("/^(.*)[\r\n]\?>/sU", '', $xmlstr);
        preg_match_all("/<pools:([^>]*)>(.*)<\/pools:([^>]*)>/sU", $content, $arr);
        $groups = array();
        foreach( $arr[1] as $k => $attstr )
        {
            $atts = $this->_parse_atts($attstr);
            $poolname = $arr[3][$k];
            $groups['pools'][$poolname]['allowpool'] = $this->_get_trim_atts($atts, 'allowpool');
            $groups['pools'][$poolname]['auttype']   = $this->_get_trim_atts($atts, 'auttype');
            $groups['pools'][$poolname]['name']      = $this->_get_trim_atts($atts, 'name');
            $groups['pools'][$poolname]['control']   = $this->_get_trim_atts($atts, 'login_control');
            $groups['pools'][$poolname]['public']    = '';
            $groups['pools'][$poolname]['protected'] = '';
            $groups['pools'][$poolname]['private']   = array();
            preg_match_all("/<ctl:([^>]*)>(.*)<\/ctl:([^>]*)>/sU", $arr[2][$k], $ctls);
            foreach( $ctls[1] as $j => $ctlname )
            {
                if( $ctlname=='private' )
                {
                    preg_match_all("/<([\w]*)([^>]*)>(.*)<\/([\w]*)>/sU", $ctls[2][$j], $p_groups);
                    foreach($p_groups[4] as $l => $v )
                    {
                        $atts2     = $this->_parse_atts( $p_groups[2][$l] );
                        $groupname = $this->_get_trim_atts($atts2, 'name');
                        $groups['pools'][$poolname]['private'][$v]['name']  = $groups['access_groups']["{$poolname}_{$v}"] = $groupname;
                        $groups['pools'][$poolname]['private'][$v]['allow'] = self::parse_private($this->_get_trim_atts($p_groups[3], $l));
                    }
                } else {
                    $groups['pools'][$poolname][ $ctlname ] = self::parse_private($this->_get_trim_atts($ctls[2], $j));
                }
            }
        }
        return $groups;
     }
    
    /**
    * 解析私有权限属性
    * @parem $attstr
    * @return array
    */
    public static function parse_private($cfgstr)
    {
        $rearr = array();
        if( empty($cfgstr) )
        {
            return array();
        }
        if( $cfgstr=='*' )
        {
            return $cfgstr;
        }
        $cfgstrs = explode(',', $cfgstr);
        foreach($cfgstrs as $v)
        {
            if( $v=='*' ) continue;
            $vs = explode('-', $v);
            $rearr[$vs[0]][] = $vs[1];
        }
        return $rearr;
    }


   /**
    * 解析属性
    * @parem $attstr
    * @return array
    */
    protected function _parse_atts($attstr)
    {
        $patts = '';
        preg_match_all("/([0-9a-z_-]*)[\t ]{0,}=[\t ]{0,}[\"']([^>\"']*)[\"']/isU", $attstr, $patts);
        if( !isset($patts[1]) )
        {
           return false;
        }
        $atts = array();
        foreach($patts[1] as $ak => $attname)
        {
           $atts[trim($attname)] = trim($patts[2][$ak]);
        }
        return $atts;
    }

    /**
    * 把值里的空白字符去除
    * @parem $atts
    * @parem $key
    * @return string
    */
    protected function _get_trim_atts($atts, $key)
    {
        if( !isset($atts[$key]) )
        {
            return '';
        } else {
            return preg_replace("/[ \t\r\n]/", '', $atts[$key]);
        }
    }
    
    /**
    * 把权限数组转为字符串
    */
    protected function _gps_str(&$gps)
    {
        $gstr = '';
        if( is_array($gps))
        {
             foreach($gps as $kctl => $kacs)
             {
                  if( is_array($kacs) )
                  {
                       foreach($kacs as $ac) $gstr .= ($gstr=='' ? "{$kctl}-{$ac}" : ",{$kctl}-{$ac}");
                  }
             }
        }
        else
        {
                $gstr = $gps;
        }
        return $gstr;
    }

}


