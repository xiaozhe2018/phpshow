<?php
if( !defined('PHPBONE') ) exit('Request Error!');
/**
 * 数据库操作类 <<读写分离>>
 *
 * 读 - mysql master
 *    - mysql slave 1
 *    - mysql slave 2
 *    ......
 *
 * 写 - master
 *
 * @author itprato<2500875@qq>
 * @version $Id$
 */
class db extends db_base
{
    protected static function _init_mysql( $is_master = false )
    {
        //获取配置
        $db_config = (self::$link_name=='default' ? self::_get_default_config() : self::$configs[self::$link_name]);
        //连接属性及host
        if( $is_master === true || empty($db_config['host']['slave']) )
        {
            $link = 'w';
            $host = $db_config['host']['master'];
        }
        else
        {
            $link = 'r';
            $key = array_rand($db_config['host']['slave']);
            $host = $db_config['host']['slave'][$key];
        }
        //创建连接
        if( empty( self::$links[self::$link_name][$link] ) )
        {
            try
            {
                self::$links[self::$link_name][$link] = mysql_connect($host, $db_config['user'], $db_config['pass']);
                if( empty(self::$links[self::$link_name][$link]) )
                {
                    throw new Exception(mysql_error());
                }
                else
                {
                    $charset = str_replace('-', '', strtolower($db_config['charset']));
                    mysql_query(" SET character_set_connection=" . $charset . ", character_set_results=" . $charset . ", character_set_client=binary, sql_mode='' ");
                    if ( mysql_select_db($db_config['name']) === false ) {
                        throw new Exception( mysql_error() );
                    }
                }
            }
            catch (Exception $e)
            {
                bone::fatal_error( 'db.php _init_mysql()', $e->getMessage().' page: '.util::get_cururl() );
            }
        }
        self::$cur_link = self::$links[self::$link_name][$link];
        return self::$links[self::$link_name][$link];
    }
    
    public static function select_db($dbname)
    {
        return self::query(" use `{$dbname}`; ");
    }
    
    public static function query ($sql, $is_master = false)
    {
        $start_time = microtime(true);
        $sql = trim($sql);
        
        //对SQL语句进行安全过滤
        if( self::$safe_test==true ) {
            $sql = self::_filter_sql($sql);
        }
        
        //获取当前连接
        if( $is_master===true )
        {
            self::$cur_link = self::_init_mysql( true );
        }
        else
        {
            if( substr(strtolower($sql), 0, 1) === 's' )
            {
                self::$cur_link = self::_init_mysql( false );
            } else {
                self::$cur_link = self::_init_mysql( true );
            }
        }
        
        try
        {
            self::$cur_result = mysql_query(self::_get_sql($sql), self::$cur_link);
            //self::$results[ self::$cur_result ] = self::$cur_result;
            //记录慢查询
            if( self::$log_slow_query )
            {
                $querytime = microtime(true) - $start_time;
                if( $querytime > self::$log_slow_time )
                {
                    self::_slow_query_log($sql, $querytime);
                }
            }
            if (self::$cur_result === false)
            {
                throw new Exception(mysql_error());
                return false;
            }
            else
            {
                self::$query_count ++;
                return self::$cur_result;
            }
        }
        catch (Exception $e)
        {
            //bone::fatal_error( 'db.php query()', $e->getMessage().'|'.$sql.' page:'.util::get_cururl() );
            $msg = $e->getMessage();
            if( preg_match("/gone away/i", $msg) && $retry < 3)
            {
                db::ping();
                $retry++;
                return self::query ($sql, $is_master, $retry);
            }
            else
            {
                bone::fatal_error( 'db.php query()', $msg.'|'.$sql.' page:'.util::get_cururl() );
            }
        }
    }
    
    public static function query_over( $sql )
    {
        self::$cur_link = self::_init_mysql(false, true);
        if( self::$safe_test==true )
        {
            $sql = self::_filter_sql($sql);
        }
        $rs = @mysql_query(self::_get_sql($sql), self::$cur_link);
        return $rs;
    }
    
    public static function insert_id ()
    {
        return mysql_insert_id( self::$cur_link );
    }
    
    public static function affected_rows ()
    {
        return mysql_affected_rows( self::$cur_link );
    }
    
    public static function num_rows ( $rsid='' )
    {
        $rsid = self::_get_rsid( $rsid );
        return mysql_num_rows( $rsid );
    }
    
    public static function fetch_one($rsid = '', $result_type = DB_GET_ASSOC)
    {
        $rsid = self::_get_rsid( $rsid );
        $row = mysql_fetch_array($rsid, $result_type);
        return $row;
    }
    public static function fetch($rsid = '', $result_type = DB_GET_ASSOC)
    {
        return self::fetch_one($rsid, $result_type);
    }
    
    public static function get_one ($sql, $result_type = DB_GET_ASSOC)
    {
        if( !preg_match("/limit/i", $sql) ) {
            $sql = preg_replace("/[,;]$/i", '', trim($sql))." limit 1 ";
        }
        $rsid = self::query($sql, false);
        $row = mysql_fetch_array( $rsid, $result_type);
        mysql_free_result( $rsid );
        return $row;
    }
    
    public static function fetch_all ( $rsid='' )
    {
        $rsid = self::_get_rsid( $rsid );
        $row = $rows = array();
        while ($row = mysql_fetch_array($rsid, DB_GET_ASSOC))
        {
            $rows[] = $row;
        }
        mysql_free_result( $rsid );
        return empty($rows) ? false : $rows;
    }
    
    public static function get_all($sql, $key = '')
    {
        $rsid = self::query($sql, false);
        while( $row = self::fetch_one($rsid, DB_GET_ASSOC) )
        {
            if(!empty($key) && isset($row[$key]))
            {
                $rows[$row[$key]] = $row;
            } else {
                $rows[] = $row;
            }
        }
        return empty($rows) ? false : $rows;
    }
    
    public static function ping( $link = 'w' )
    {
        if( self::$links[self::$link_name][$link] != null && !mysqli_ping( self::$links[self::$link_name][$link] ) ) 
        {
            mysql_close( self::$links[self::$link_name][$link] );
            @mysql_close( self::$cur_link );
            self::$links[self::$link_name][$link] = self::$cur_link = null;
            self::_init_mysql( $link=='w' );
        }
    }
    
    public static function free( $rsid )
    {
        return mysql_free_result( $rsid );
    }
    
    public static function autocommit( $mode = false )
    {
        self::$cur_link = self::_init_mysql( true );
        $int = $mode ? 1 : 0;
        return @mysql_query("SET autocommit={$int}", self::$cur_link);
    }

    public static function begin_tran()
    {
        self::$cur_link = self::_init_mysql( true );
        return @mysql_query('BEGIN', self::$cur_link);
    }
    
    public static function commit()
    {
        return @mysql_query('COMMIT', self::$cur_link);
    }
    
    public static function rollback()
    {
        return @mysql_query('ROLLBACK', self::$cur_link);
    }

}
