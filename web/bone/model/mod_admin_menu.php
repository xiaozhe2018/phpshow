<?php
if( !defined('PHPBONE') ) exit('Request Error!');
/**
 * 管理菜单读取
 *
 * @version $Id$
 */
class mod_admin_menu
{
    private static $_menu_key = 'admin_menu';
    public static $apps = array();

   /**
    * 分析菜单，返回特定格式的js
    * @return string
    */
    public static function parse_menu()
    {
        $menu_config = config::get( self::$_menu_key );
        self::replace_app_menu( $menu_config );
        $menu = '';
        preg_match_all("#<menu([^>]*)>(.*)</menu>#sU", $menu_config, $arr);
        $j = 0;
        foreach($arr[1] as $k=>$v)
        {
            $mi = ++$j;
            $atts = self::parse_atts($v);
            $menu_name = self::get_att($atts, 'name');
            $display       = self::get_att($atts, 'display');
            $menu_cls = self::get_att($atts, 'class');
            //$menu_cls = $menu_cls=='' ? 'default' : $menu_cls;
            if( $display=='none' ) continue;
            $topmm = "'{$mi}': {Text:'{$menu_name}', Cls:'{$menu_cls}'},\n";
            if( trim($arr[2][$k]) == '') continue;
            preg_match_all("#<node([^>]*)>(.*)</node>#sU", $arr[2][$k], $nodes);
            $sonmm = '';
            foreach($nodes[1] as $n=>$n_v)
            {
                $ni = ++$j;
                $df = '';
                $atts = self::parse_atts($n_v);
                $n_name  = self::get_att($atts, 'name');
                $n_url   = self::get_att($atts, 'url');
                $n_default = self::get_att($atts, 'default');
                $display       = self::get_att($atts, 'display');
                if( $display=='none' ) continue;
                if( $n_default== 1 ) $n_default = ',Default:true';
                if( $n_url != '' ) $n_url = ",url:'{$n_url}'";
                $sonmm_tmp = "   '{$ni}':{Text:'{$n_name}',Parent:'{$mi}'{$n_url}{$n_default}},\n";
                if( trim($nodes[2][$n]) == '' || $n_url != '' ) continue;
                preg_match_all("#<item([^>]*)/>#sU", $nodes[2][$n], $items);
                $sonmm2 = '';
                foreach($items[1] as $i=>$i_v)
                {
                    $ii = ++$j;
                    $df = '';
                    $atts = self::parse_atts($i_v);
                    $i_name = self::get_att($atts, 'name');
                    $i_ct = self::get_att($atts, 'ct');
                    $i_ac = self::get_att($atts, 'ac');
                    $display       = self::get_att($atts, 'display');
                    if( $display=='none' ) continue;
                    if( !self::has_purview($i_ct, $i_ac) ) continue;
                    $i_url  = self::get_att($atts, 'url');
                    if( $i_url=='' ) {
                        $i_url = "?ct={$i_ct}&amp;ac={$i_ac}";
                    }
                    $default = self::get_att($atts, 'default');
                    if( $default==1 ) $default = ',Default:true';
                    if( $i_url != '' ) $i_url = ",url:'{$i_url}'";
                    $sonmm2 .= "      '{$ii}':{Text:'{$i_name}',Parent:'{$ni}'{$i_url}{$default}},\n";
                }
                if( $sonmm2!='' )
                {
                    $sonmm .= $sonmm_tmp.$sonmm2;
                }
            }
            if( $sonmm != '')
            {
                $menu .= $topmm.$sonmm;
            }
        }
        return $menu;
    }
    
   /**
    * 处理应用菜单的操作
    * @parem string $menu
    * @return bool
    */
    protected static function replace_app_menu( &$menu )
    {
        $menu = str_replace('<tpl>catalogmenu</tpl>', mod_catalog::get_catalog_menu(), $menu);
        //echo '<xmp>', $menu, '</xmp>'; exit();
        return $menu;
    }
    
   /**
    * 检测用户是否有指定权限
    * @parem string $ct
    * @parem string $ac
    * @return bool
    */
    protected static function has_purview($ct, $ac)
    {
        $rs = bone::$auth->check_purview($ct, $ac, 2);
        if( $rs==1 )
        {
            return true;
        } else {
            return false;
        }
    }
    
   /**
    * 分析菜单，返回所有项的名称(用于设置用户权限时显示应用信息)
    * @return string
    */
    public static function parse_apps()
    {
        if( !empty(self::$apps) ) {
            return self::$apps;
        }
        $menu_config = config::get( self::$_menu_key );
        $menu = '';
        preg_match_all("#<menu([^>]*)>(.*)</menu>#sU", $menu_config, $arr);
        $j = 0;
        foreach($arr[1] as $k => $v)
        {
            if( trim($arr[2][$k]) == '') {
                continue;
            }
            preg_match_all("#<node([^>]*)>(.*)</node>#sU", $arr[2][$k], $nodes);
            foreach($nodes[1] as $n => $n_v)
            {
                $atts = self::parse_atts($n_v);
                $app       = self::get_att($atts, 'ct');
                $app_name  = self::get_att($atts, 'appname');
                if( $app != '' ) {
                    self::$apps[$app]['app_name'] = $app_name;
                }
                if( $nodes[2][$n]=='' ) {
                    continue;
                }
                preg_match_all("#<item([^>]*)/>#sU", $nodes[2][$n], $items);
                foreach($items[1] as $i => $i_v)
                {
                    $atts = self::parse_atts($i_v);
                    $i_name = self::get_att($atts, 'name');
                    $i_ct = self::get_att($atts, 'ct');
                    $i_ac = self::get_att($atts, 'ac');
                    if( !isset(self::$apps[$i_ct][$i_ac]) ) {
                        self::$apps[$i_ct][$i_ac] = $i_name;
                    }
                }
            }
        }
        return self::$apps;
    }
    
   /**
    * 分析属性
    * @parem string $attstr
    * @return array
    */
    protected static function parse_atts($attstr)
    {
        $patts = '';
        preg_match_all("/([0-9a-z_-]*)[\t ]{0,}=[\t ]{0,}[\"']([^>\"']*)[\"']/isU", $attstr, $patts);
        if( !isset($patts[1]) )
        {
           return false;
        }
        $atts = array();
        foreach($patts[1] as $ak=>$attname)
        {
           $atts[trim($attname)] = trim($patts[2][$ak]);
        }
        return $atts;
    }
    
   /**
    * 从属性数组中读取一个元素
    * @parem $atts
    * @parem $key
    * @parem $df = ''
    * @return string
    */
    protected static function get_att(&$atts, $key, $df='')
    {
        return isset($atts[$key]) ? trim($atts[$key]) : $df;
    }
}
