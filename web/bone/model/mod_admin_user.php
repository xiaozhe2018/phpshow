<?php
/**
 * 获取管理员信息的接口
 *
 * 详细说明
 *
 * @since 2009-x-x
 * @copyright http://PB
 * @author IT柏拉图
 * $Id$ 
 */
class mod_admin_user
{
    //缓存前缀
    public static $cache_prefix = 'mod_admin_user';
    
    //用户信息
    public $uid    = 0;
    public $pools  = '';
    public $groups = '';
    public $pool_name = 'admin';
    public $fields = array();
    
    /**
    * 构造函数
    * @return void
    */
    public function __construct( $uid=0 )
    {
        if( $uid != 0 ) {
            $this->fields = $this->get_infos( $uid );
            $this->_set_fields();
        }
    }
    
    /**
     * 获取用户具体信息
     *
     * @return array (如果用户尚未登录，则返回 false )
     *
     */
     public function get_infos( $uid )
     {
        if( $uid==0 ) {
            return false;
        }
        //缓存
        $this->fields = $this->get_cache( $uid );
        //源数据
        if( $this->fields === false)
        {
            $query = "Select * From `#PB#_admin` where `admin_id`='{$uid}' ";
            $this->fields = db::get_one($query);
            $this->fields['uid'] = $this->fields['admin_id'];
            $this->set_cache($this->fields['uid'], $this->fields);
            return is_array($this->fields) ? $this->fields : false;
        }
        else
        {
            if( !isset($this->fields['uid']) ) {
                $this->fields['uid'] = $this->fields['admin_id'];
            }
            return $this->fields;
        }
     }
     
    /**
     * 获取用户缓存
     * @return mix
     *
     */
     public function get_cache( $uid )
     {
        return cache::get(self::$cache_prefix, $uid);
     }
     
    /**
     * 设置用户缓存
     * @return bool
     *
     */
     public function set_cache( $uid, &$row )
     {
        cache::set(self::$cache_prefix, $uid, $row);
     }
     
    /**
     * 删除用户缓存
     * @return bool
     *
     */
     public function del_cache( $uid )
     {
        cache::del(self::$cache_prefix, $uid);
     }
     
     /**
     * 获取用户私有权限(非组权限)
     *
     * @return array (如果用户尚未登录，则返回 false )
     *
     */
    public function get_purviews( )
    {
        if( empty($this->uid) ) {
            return '';
        }
        //缓存
        $fields = cache::get(self::$cache_prefix.'_purview_mods', $this->uid);
        //源数据
        if( $fields === false)
        {
            $query = "Select `purviews` From `#PB#_admin_purview` where `admin_id`='{$this->uid}' ";
            $fields = db::get_one($query);
            cache::set(self::$cache_prefix.'_purview_mods', $fields['purviews'], $fields);
            return is_array($fields) ? $fields : '';
        }
        else
        {
            return $fields;
        }
    }
    
    /**
     * 检测用户登录
     * @return int 返回值： 0 无该用户， -1 密码错误 ， 1 登录正常
     */
    public function check_user($account, $loginpwd, $keeptime=86400)
    {
        //检测用户名合法性
        $ftype = 'user_name';
        if( pub_validate::email($account) )
        {
            $ftype = 'email';
        }
        else if( !pub_validate::user_name($account) )
        {
           throw new Exception('会员名格式不合法！');
           return 0;
        }
        //同一ip使用某帐号连续错误次数检测
        if( $this->get_login_error24( $account ) )
        {
            throw new Exception('连续登录失败超过5次，暂时禁止登录！');
            return -5;
        }
        //读取用户数据
        $row = db::get_one( "Select * From `#PB#_admin` where `{$ftype}` like '{$account}' " );
        //存在用户数据
        if( is_array($row) )
        {
            $row['accounts'] = $account;
            //密码错误，保存登录记录
            if( $row['userpwd'] != $this->_get_encodepwd($loginpwd) )
            {
                $this->save_login_history($row, -1);
                throw new Exception ('密码错误！');
                return -1;
            }
            //正确生成会话信息
            else
            {
                $row['uid'] = $row['admin_id'];
                $this->save_login_history($row, 1);
                cache::set(self::$cache_prefix, $row['uid'], $row);
                $this->fields = $row;
                $this->_set_fields();
                return 1;
            }
        }
        //不存在用户数据时不进行任何操作
        else
        {
            $row['accounts'] = $account;
            $this->save_login_history($row, -1);
            throw new Exception ('用户不存在！');
            return 0;
        }
     }
     
     /**
    * 检测用户24小时内连续输错密码次数是否已经超过
    * @return bool 超过返回true, 正常状态返回false
    */
    public function get_login_error24( $accounts )
    {
        $error_num = 5;
        $day_starttime =  strtotime( date('Y-m-d 00:00:00', time()) );
        $loginip  = util::get_client_ip();
        $cli_hash = md5($accounts.'-'.$loginip);
        $query = "Select SQL_CALC_FOUND_ROWS `loginsta` From `#PB#_admin_login` where `cli_hash`='{$cli_hash}' 
                  And `logintime` > {$day_starttime} order by `logintime` desc limit {$error_num}";
        $rc = db::query( $query );
        $info_row = db::get_one(' SELECT FOUND_ROWS() as dd ');
        if( $info_row['dd'] < $error_num)
        {
            return false;
        }
        while( $row = db::fetch_one($rc) )
        {
            if( $row['loginsta'] > 0 ) {
                return false;
            }
        }
        return true;
    }
   
   /**
    * 保存历史登录记录
    */
    public function save_login_history(&$row, $loginsta)
    {
        $ltime = time();
        $loginip  = util::get_client_ip();
        if( !isset($row['accounts']) ) {
            $row['accounts'] = $row['user_name'];
        }
        $cli_hash = md5($row['accounts'].'-'.$loginip);
        $row['uid'] = isset($row['uid']) ? $row['uid'] : 0;
        
        db::query( "Update `#PB#_admin` set `logintime`='{$ltime}', `loginip`='{$loginip}' where `admin_id` = '{$row['uid']}' " );
        
        $query = "INSERT INTO `#PB#_admin_login` (`admin_id`, `accounts`, `loginip`, `logintime`, `pools`, `loginsta`, `cli_hash`)
                  VALUES('{$row['uid']}', '{$row['accounts']}', '{$loginip}', '{$ltime}', '{$this->pool_name}', '{$loginsta}', '{$cli_hash}'); ";
                  
        $q = db::query($query, true);
        return true;
    }
    
    /**
     * 获得用户上次登录时间和ip
     * @return array
     */
     public function get_last_login( $uid )
     {
        db::query("Select `loginip`,`logintime` From `#PB#_admin_login` where `admin_id`='{$uid}' And `loginsta`=1 order by `logintime` desc limit 0,2 ");
        $datas = db::fetch_all();
        if( isset($datas[1]) )
        {
            return $datas[1];
        } else {
            return array('loginip'=>'','logintime'=>0);
        }
     }
    
   /**
    * 会员密码加密方式接口（默认是 md5）
    */
    protected function _get_encodepwd($pwd)
    {
        return md5($pwd);
    }
    
    /**
     *
     * 设置用户池等信息
     *
     * @return void
     *
     */
     private function _set_fields( )
     {
        if( !is_array( $this->fields) ) {
            return false;
        }
        $this->uid    = $this->fields['uid'];
        $this->pools  = $this->fields['pools'];
        $this->groups = $this->fields['groups'];
     }


}
