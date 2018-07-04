<?php
if( !defined('PHPBONE') ) exit('Request Error!');
/**
 * Url Rewrite 处理类
 * 类仅对输出的html进行预先转换处理，具体实现需rewrite结合或程序自行生成相应的实体文件
 */
class pub_rewrite
{
    
    public static $rules = array();
    protected static $is_load = false;
    
   /**
    * 加载 rewrite rule 文件
    */
    protected static function load_rule()
    {
        self::$is_load = true;
        $rulefile = PATH_DATA.'/rewrite.ini';
        if( file_exists($rulefile) )
        {
            $ds = file($rulefile);
            foreach($ds as $line)
            {
                $line = trim($line);
                if( $line=='' || $line[0]=='#')
                {
                    continue;
                }
                list($s, $t) = preg_split('/[ ]{4,}/', $line); //用至少四个空格分隔，这样即使s、t中有空格也能识别
                $s = rtrim($s);
                $t = ltrim($t);
                if( $s != '' && $t !='' )
                {
                    $_s = preg_replace("#(^[\^]|[\$]$)#", '', $s);
                    $sok = $s[0]=='^' ? '<rw>'.$_s : '<rw>(.*)'.$_s;
                    $s = $s[strlen($s)-1]=='$' ? $sok.'</rw>' : $sok.'([^<]*)</rw>';
                    $s = preg_replace("#(^[\^]|[\$]$)#", '', $s);
                    //$s = '<rw>'.$_s.'</rw>';
                    self::$rules[ $s ] = $t;
                }
            }
        }
    }
    
   /**
    * 转换要输出的内容里的网址
    * @parem string $html
    */
    public static function convert_html(&$html)
    {
        if( !self::$is_load ) {
            self::load_rule();
        }
        //echo '<xmp>';
        foreach(self::$rules as $s => $t) {
            //echo "$s -- $t \n";
            $html = preg_replace('~'.$s.'~iU', $t, $html);
        }
        //exit();
        $html = preg_replace('#<[/]{0,1}rw>#', '', $html);
        return $html;
    }
    
    /**
    * 转换单个网址
    * @parem string $url
    */
    public static function convert_url($url)
    {
        if( !self::$is_load )
        {
            self::load_rule();
        }
        foreach(self::$rules as $s=>$t)
        {
            $url = preg_replace('/'.$s.'/iU', $t, $url);
        }
        return $url;
    }
    
}
