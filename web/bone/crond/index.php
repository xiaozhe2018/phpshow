<?php
/**
 *
 * CROND 定时控制器
 *
 * @since 2013-08-05
 * @author itprato
 * $Id
 */
define('CRON_PATH', dirname(__FILE__));
define('CRON_MAXTIME', 86400);
require CRON_PATH. '/../core/bone.php';

//只允许命令行模式运行这个脚本
if( PHP_SAPI != 'cli' )
{
    exit("Not Allow");
}

//设置最大执行时间为一天
ini_set('max_execution_time', CRON_MAXTIME);

//执行CROND
exit( crond() );

/**
 * CROND函数
 */
function crond()
{

    require CRON_PATH.'/../config/inc_crond.php';
    $logpath = CRON_PATH.'/../data/log/';
    $time = microtime(true);
    
    //如果当时运行的crond尚未结束, 不允许开多个副本
    if( file_exists($logpath.'crond.lock') && time() - filemtime($logpath.'crond.lock')  <  CRON_MAXTIME )
    {
        exit( date('Y-m-d H:i:s')."crond not finish!\r\n" );
    } else {
        file_put_contents($logpath.'crond.lock', date('Y-m-d H:i:s'));
    }

    //根据配置提取要执行的文件
    $exe_file = array();

    foreach ($GLOBALS['CROND_TIMER']['the_format'] as $format)
    {
        $key = date($format, ceil($time));
        if (is_array(@$GLOBALS['CROND_TIMER']['the_time'][$key]))
        {
            $exe_file = array_merge($exe_file, $GLOBALS['CROND_TIMER']['the_time'][$key]);
        }
    }

    //加载要执行的文件
    echo date('Y-m-d H:i', time()), "\r\n";
    foreach ($exe_file as $file)
    {
        echo '  ', $file,"\r\n";
        include CRON_PATH.'/'.$file;
        echo "\r\n";
    }
    $use_time = microtime(true) - $time;
    echo 'total: ', microtime(true) - $time . "\r\n--------------------------------------------\r\n";
    unlink($logpath.'crond.lock');

}

