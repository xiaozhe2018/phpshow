<?php
if( !defined('PHPBONE') ) exit('Request Error!');
/**
 * 实用函数集合
 *
 * 替代lib_common
 *
 * @author itprato<2500875@qq>
 * @version $Id$  
 */
class util
{
    public static $client_ip = NULL;
    
    public static $cfc_handle = NULL;
    
    public static $user_agent = 'Mozilla/5.0 (Windows; U; Windows NT 5.2; zh-CN; rv:1.9.2.13) Gecko/20101203 Firefox/3.6.13';
    
    /**
     * 获得用户的真实IP 地址
     *
     * HTTP_X_FORWARDED_FOR 的信息可以进行伪造
     * 对于需要检测用户IP是否重复的情况，如投票程序，为了防止IP伪造
     * 可以使用 REMOTE_ADDR + HTTP_X_FORWARDED_FOR 联合使用进行杜绝用户模拟任意IP的可能性
     *
     * @param 多个用多行分开
     * @return void
     */
    public static function get_client_ip()
    {
        $client_ip = '';
        
        if( self::$client_ip !== NULL )
        {
            return self::$client_ip;
        }
        
        //分析代理IP
        if( isset($_SERVER['HTTP_X_FORWARDED_FOR2']) )
        {
            $_SERVER['HTTP_X_FORWARDED_FOR'] = $_SERVER['HTTP_X_FORWARDED_FOR2'];
        }
        
        if( isset($_SERVER['HTTP_X_FORWARDED_FOR']) )
        {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($arr as $ip)
            {
                $ip = trim($ip);
                if ($ip != 'unknown' ) {
                    $client_ip = $ip; break;
                }
            }
        }
        else
        {
            $client_ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        }
        
        preg_match("/[\d\.]{7,15}/", $client_ip, $onlineip);
        $client_ip = ! empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';
        
        self::$client_ip = $client_ip;
        
        return $client_ip;
    }
    
    /**
     * 写文件
     */
    function put_file($file, $content, $flag = 0)
    {
        $pathinfo = pathinfo ( $file );
        if (! empty ( $pathinfo ['dirname'] ))
        {
            if (file_exists ( $pathinfo ['dirname'] ) === false)
            {
                if (@mkdir ( $pathinfo ['dirname'], 0777, true ) === false)
                {
                    return false;
                }
            }
        }
        if ($flag === FILE_APPEND)
        {
            return @file_put_contents ( $file, $content, FILE_APPEND );
        }
        else
        {
            return @file_put_contents ( $file, $content, LOCK_EX );
        }
    }

   /**
    * 获得当前的Url
    */
    public static function get_cururl()
    {
        if(!empty($_SERVER["REQUEST_URI"]))
        {
            $scriptName = $_SERVER["REQUEST_URI"];
            $nowurl = $scriptName;
        }
        else
        {
            $scriptName = $_SERVER["PHP_SELF"];
            $nowurl = empty($_SERVER["QUERY_STRING"]) ? $scriptName : $scriptName."?".$_SERVER["QUERY_STRING"];
        }
        return $nowurl;
    }
    
    /**
     * 检查路径是否存在
     * @parem $path
     * @return bool
     */
    public static function path_exists( $path )
    {
        $pathinfo = pathinfo ( $path . '/tmp.txt' );
        if ( !empty( $pathinfo ['dirname'] ) )
        {
            if (file_exists ( $pathinfo ['dirname'] ) === false)
            {
                if (mkdir ( $pathinfo ['dirname'], 0777, true ) === false)
                {
                    return false;
                }
            }
        }
        return $path;
    }

    /**
     * 判断是否为utf8字符串
     * @parem $str
     * @return bool
     */
    public static function is_utf8($str)
    {
        if ($str === mb_convert_encoding(mb_convert_encoding($str, "UTF-32", "UTF-8"), "UTF-8", "UTF-32"))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

   /**
    * utf8编码模式的中文截取2，单字节截取模式
    * 这里不使用mbstring扩展
    * @return string
    */
    public static function utf8_substr($str, $slen, $startdd=0)
    {
        return mb_substr($str , $startdd , $slen , 'UTF-8');
    }
    
   /**
    * utf-8中文截取，按字数截取模式
    * @return string
    */
    function utf8_substr_num($str, $length, $start=0)
    {
	    preg_match_all('/./su', $str, $ar);
	    if( count($ar[0]) <= $length ) {
		    return $str;
	    }
	    $tstr = '';
	    $n = 1;
	    for($i=0; isset($ar[0][$i]); $i++)
	    {
		    if($n < $length)
		    {
			    $tstr .= $ar[0][$i];
			    /*
			    if( strlen($ar[0][$i]) == 1) $n += 0.5;
			    else $n++;
			    */
			    $n++;
		    } else {
			    break;
		    }
	    }
	    return $tstr;
    }

    /**
     * 从普通时间返回Linux时间截(strtotime中文处理版)
     * @parem string $dtime
     * @return int
     */
    public static function cn_strtotime( $dtime )
    {
        if(!preg_match("/[^0-9]/", $dtime))
        {
            return $dtime;
        }
        $dtime = trim($dtime);
        $dt = Array(1970, 1, 1, 0, 0, 0);
        $dtime = preg_replace("/[\r\n\t]|日|秒/", " ", $dtime);
        $dtime = str_replace("年", "-", $dtime);
        $dtime = str_replace("月", "-", $dtime);
        $dtime = str_replace("时", ":", $dtime);
        $dtime = str_replace("分", ":", $dtime);
        $dtime = trim(preg_replace("/[ ]{1,}/", " ", $dtime));
        $ds = explode(" ", $dtime);
        $ymd = explode("-", $ds[0]);
        if(!isset($ymd[1]))
        {
            $ymd = explode(".", $ds[0]);
        }
        if(isset($ymd[0]))
        {
            $dt[0] = $ymd[0];
        }
        if(isset($ymd[1])) $dt[1] = $ymd[1];
        if(isset($ymd[2])) $dt[2] = $ymd[2];
        if(strlen($dt[0])==2) $dt[0] = '20'.$dt[0];
        if(isset($ds[1]))
        {
            $hms = explode(":", $ds[1]);
            if(isset($hms[0])) $dt[3] = $hms[0];
            if(isset($hms[1])) $dt[4] = $hms[1];
            if(isset($hms[2])) $dt[5] = $hms[2];
        }
        foreach($dt as $k=>$v)
        {
            $v = preg_replace("/^0{1,}/", '', trim($v));
            if($v=='')
            {
                $dt[$k] = 0;
            }
        }
        $mt = mktime($dt[3], $dt[4], $dt[5], $dt[1], $dt[2], $dt[0]);
        if(!empty($mt))
        {
            return $mt;
        }
        else
        {
            return strtotime( $dtime );
        }
    }

    /**
     * 发送邮件
     * @param array  $to      收件人
     * @param string $subject 邮件标题
     * @param string $body　      邮件内容
     * @return bool
     * @author xiaocai
     */
    public static function send_email($to, $subject, $body)
    {
        $send_account = $GLOBALS['config']['send_smtp_mail_account'];
        try
        {
            $smtp = new cls_mail($send_account['host'], $send_account['port'], true, $send_account['user'], $send_account['password']);
            $smtp->debug = $send_account['debug'];
            $result = $smtp->sendmail($to, $send_account['from'], $subject, $body, $send_account['type']);

            return $result;
        }
        catch( Exception $e )
        {
            return false;
        }
    }
    
    /*
     * http get函数
     * @parem $url
     * @parem $$timeout=30
     * @parem $referer_url=''
     */
    public static function http_get($url, $timeout=30, $referer_url='')
    {
        $startt = time();
        if (function_exists('curl_init'))
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            if( $referer_url != '' )  curl_setopt($ch, CURLOPT_REFERER, $referer_url);
            curl_setopt($ch, CURLOPT_USERAGENT, self::$user_agent);
            $result = curl_exec($ch);
            $errno  = curl_errno($ch);
            curl_close($ch);
            return $result;
        }
        else
        {
            $Referer = ($referer_url=='' ?  '' : "Referer:{$referer_url}\r\n");
            $context =
            array('http' =>
                array('method' => 'GET',
                  'header' => 'User-Agent:'.self::$user_agent."\r\n".$Referer
                )
            );
            $contextid = stream_context_create($context);
            $sock = fopen($url, 'r', false, $contextid);
            stream_set_timeout($sock, $timeout);
            if($sock)
            {
                $result = '';
                while (!feof($sock)) {
                    //$result .= stream_get_line($sock, 10240, "\n");
                    $result .= fgets($sock, 4096);
                    if( time() - $startt > $timeout ) {
                        return '';
                    }
                }
                fclose($sock);
            }
        }
        return $result;
    }
    
    /**
    * 向指定网址发送post请求
    * @parem $url
    * @parem $query_str
    * @parem $$timeout=30
    * @parem $referer_url=''
    * @return string
    */
    public function http_post($url, $query_str, $timeout=30, $referer_url='')
    {
        $startt = time();
        if( function_exists('curl_init') )
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query_str);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, self::$user_agent );
            $result = curl_exec($ch);
            $errno  = curl_errno($ch);
            curl_close($ch);
            //echo " $url & $query_str <hr /> $errno , $result ";
            return $result;
        }
        else
        {
            $context =
            array('http' =>
            array('method' => 'POST',
            'header' => 'Content-type: application/x-www-form-urlencoded'."\r\n".
            'User-Agent: '.self::$user_agent."\r\n".
            'Content-length: ' . strlen($query_str),
            'content' => $query_str));
            $contextid = stream_context_create($context);
            $sock = fopen($url, 'r', false, $contextid);
            if ($sock)
            {
                $result = '';
                while (!feof($sock))
                {
                    $result .= fgets($sock, 4096);
                    if( time() - $startt > $timeout ) {
                        return '';
                    }
                }
                fclose($sock);
            }
        }
        return $result;
    }
    
    /**
    * 向指定网址post文件
    * @parem $url
    * @parem $files  文件数组 array('fieldname' => filepathname ...)
    * @param $fields 附加的数组  array('fieldname' => content ...)
    * @parem $$timeout=30
    * @parem $referer_url=''
    * @return string
    */
    public function http_post_file($url, $files, $fields, $timeout=30, $referer_url='')
    {
        $startt = time();
        if( function_exists('curl_init') )
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_USERAGENT, self::$user_agent );
            $need_class = class_exists('\CURLFile') ? true : false;
            foreach($files as $k => $v)
            {
                if ( $need_class ) {
                    $fields[$k] = new CURLFile(realpath($v));
                } else {
                    $fields[$k] = '@' . realpath($v);
                }
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
            return $result;
        }
        else
        {
            return false;
        }
    }
    
}
