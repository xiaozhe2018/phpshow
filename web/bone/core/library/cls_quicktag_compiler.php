<?php
/**
 * quick模板编译引擎
 *
 * 这个类主要用于对未编译或已经改动的模板进行编译
 *
 function __quicktag_compiler(){ }
 *
 */
if( !defined('QUICKTAG') )
{
    exit('Request Error!');
}
define('S_PHP', '<'.'?php'); 
define('E_PHP', '?'.'>'); 
class cls_quicktag_compiler extends cls_quicktag
{
    
    //模板里包含的自动触发块函数
    var $_tpl_blocks = array();
    var $_block_id = 0;
    
    //未编译前和编译后的tag数组
    var $all_tag_ids = array();
    var $_all_tags_body = array();
    var $_all_tags = array();
    var $_all_compiles = array();
    
    //属性解析类
    var $att_parse = null;
    
    /**
     * 构造函数
     */
    function __construct()
    {
        $this->att_parse = new quick_attribute_parse();
    }
    
   /**
    * 编译指定的模板
    * @parem string $tpl_file 源模板（绝对路径）
    * @parem string $compile_to 目录文件
    */
    public function compiler($tpl_file, $compile_to)
    {
        $source = file_get_contents($tpl_file);
        $tarr = '';
        preg_match_all('/'.$this->left_delimiter.'(.*)'.$this->right_delimiter.'/U', $source, $tarr);
        if( !empty($tarr) )
        {
            $i = 0;
            foreach($tarr[0] as $k=>$v)
            {
                $mdkey = $this->base64key($v);
                if( isset($this->all_tag_ids[$mdkey]) )
                {
                    $this->all_tag_ids[$mdkey][1]++;
                    continue;
                }
                else
                {
                    $this->all_tag_ids[$mdkey][0] = $v;
                    $this->all_tag_ids[$mdkey][1] = 1;
                }
                $this->_all_tags[$i] = $v;
                $this->_all_tags_body[$i] = trim($tarr[1][$k]);
                $this->_all_compiles[$i] = '';
                $i++;
            }
            $this->compiler_tag();
            $source = str_replace($this->_all_tags, $this->_all_compiles, $source);
            
            $headphp  = S_PHP."\n";
            $headphp .= "if( !defined('QUICKTAG') ) exit('Request Error!');\n";
            foreach($this->_tpl_blocks as $bc=>$btype)
            {
                $bc = preg_replace('/^#/', '', $bc);
                if( $btype=='func' )
                {
                    $headphp .= "require_once \$this->plugins_dir.'/function.{$bc}.php';\n";
                }
                else if( $btype=='modifier' )
                {
                    $headphp .= "require_once \$this->plugins_dir.'/modifier.{$bc}.php';\n";
                }
                else
                {
                    $headphp .= "require_once \$this->plugins_dir.'/block.{$bc}.php';\n";
                }
            }
            $headphp .= E_PHP;
            $source = $headphp.$source;
        }
        $source = str_replace(E_PHP.S_PHP, '', $source);
        $rs = file_put_contents($compile_to, $source);
        return $rs;
    }

   /**
    * 编译具体标签
    */
    private function compiler_tag()
    {
        foreach($this->_all_tags_body as $k => $tagstr)
        {
            //编译变量
            if( preg_match("/^\\\$/i", $tagstr) )
            {
                $this->compiler_tag_var($tagstr, $this->_all_compiles[$k]);
            }
            //编译elseif
            else if( preg_match("/elseif[\( \t]/i", $tagstr) )
            {
                $this->compiler_tag_if($tagstr, $this->_all_compiles[$k], 'elseif');
            }
            //编译if
            else if( preg_match("/if[\( \t]/i", $tagstr) )
            {
                $this->compiler_tag_if($tagstr, $this->_all_compiles[$k], 'if');
            }
            //else 直接返回 } else {
            else if( preg_match("/^else$/i", $tagstr) )
            {
                $this->_all_compiles[$k] = S_PHP.' } else { '.E_PHP;
            }
            //编译函数
            else if( preg_match("/^[\w:]*[ \t]{0,}\(/i", $tagstr) )
            {
                $this->compiler_tag_func($tagstr, $this->_all_compiles[$k]);
            }
            //编译rewrite
            else if( preg_match("/rewrite/i", $tagstr) )
            {
                $this->compiler_tag_rewrite($tagstr, $this->_all_compiles[$k]);
            }
            else if( preg_match("/\/rewrite/i", $tagstr) )
            {
                $this->compiler_tag_rewrite($tagstr, $this->_all_compiles[$k]);
            }
            //带 / 的， 全都是直接返回 }
            else if( preg_match("/^\//", $tagstr) )
            {
                $this->_all_compiles[$k] = S_PHP.' } '.E_PHP;
            }
            //编译include
            else if( preg_match("/include[ \t]/i", $tagstr) )
            {
                $this->compiler_tag_include($tagstr, $this->_all_compiles[$k]);
            }
            //编译普通块
            else
            {
                $this->compiler_tag_block($tagstr, $this->_all_compiles[$k]);
            }
        }
    }
    
   /**
    * 编译变量标签
    * @parem tag字符
    * @parem $compilevalue 返回值
    */
    private function compiler_tag_var($tagstr, &$compilevalue)
    {
        $tagstrs = explode('|', $tagstr);
        if( isset($tagstrs[1]) )
        {
            $tagstrs[1] = trim( str_replace('@me', trim($tagstrs[0]), $tagstrs[1]) );
            
            //modifier函数
            if( $tagstrs[1][0] == '#' ) {
                $tagstrs[1] = preg_replace('/^#/', 'tpl_modifier_', $tagstrs[1]);
                $fs = explode('(', $tagstrs[1]);
                $bk_name = str_replace('tpl_modifier_', '', $fs[0]);
                $this->_tpl_blocks[ $bk_name ] = 'modifier';
            }
            
            $revalue = $this->parse_function( $tagstrs[1] );
        }
        else
        {
            $revalue = $this->parse_var( trim($tagstrs[0]) );
        }
        $compilevalue = ($revalue != '' ? S_PHP." echo {$revalue}; ".E_PHP : "error: {$tagstr} incorrect!");
    }
    
   /**
    * 编译 func 标签
    * @parem tag字符
    * @parem $compilevalue 返回值
    */
    private function compiler_tag_func($tagstr, &$compilevalue)
    {
        $revalue = $this->parse_function($tagstr);
        $compilevalue = ($revalue != '' ? S_PHP." echo {$revalue}; ".E_PHP : "error: {$tagstr} incorrect!");
    }
    
   /**
    * 编译 if 标签
    * @parem tag字符
    * @parem $compilevalue 返回值
    */
    private function compiler_tag_if($tagstr, &$compilevalue, $iftype='if')
    {
        if( $iftype=='elseif' )
        {
            $tagstr = preg_replace('/^elseif[ \t]/i', '', $tagstr);
            $cond = $this->parse_function($tagstr);
            $compilevalue = ($cond=='' ? "error: {$tagstr} incorrect! ".S_PHP.' } else if(false){ '.E_PHP : S_PHP." } else if({$cond}) { ".E_PHP);
        } else {
            $tagstr = preg_replace('/^if[ \t]/i', '', $tagstr);
            $cond = $this->parse_function($tagstr);
            $compilevalue = ($cond=='' ? "error: {$tagstr} incorrect! ".S_PHP.' if(false){ '.E_PHP : S_PHP." if({$cond}) { ".E_PHP);
        }
    }
    
   /**
    * 编译 include 标签
    * @parem tag字符
    * @parem $compilevalue 返回值
    */
    private function compiler_tag_include($tagstr, &$compilevalue)
    {
        $this->att_parse->set_source($tagstr);
        if( $this->att_parse->c_att->is_att('file') )
        {
            $filename = $this->att_parse->c_att->get_att('file');
            $infos = $this->fetch_resource_info( $filename );
            if( $infos['tplcache']=='' )
            {
                $compilevalue = "error: {$filename} not found!";
            } else {
                $infos['tplcache'] = preg_replace("/[\/\\\\]{1,}/", '/', $infos['tplcache']);
                $compilevalue = S_PHP."  include \"{$infos['tplcache']}\";  ".E_PHP;
            }
        }
        else
        {
            $compilevalue = "error: {$tagstr} incorrect!";
        }
    }
    
   /**
    * 编译 rewrite 标签
    * 关于rewrite的控制方法，开启了rewrite之后，只对rewrite标签编译为<rw></rw>，
    * 在最终进行display或fetch时才根据rewrite规则对<rw></rw>里的网址进行替换
    * 关于处理最终网址的时候，最佳方法是使用二次编译法
    * 不过目前暂时用的是直接替换法，在效率要求比较高的情况下，不建议使用这种方法
    * @parem tag字符
    * @parem $compilevalue 返回值
    * @return void
    */
    private function compiler_tag_rewrite($tagstr, &$compilevalue)
    {
        if( isset($GLOBALS['config']['use_rewrite']) && $GLOBALS['config']['use_rewrite'] ) {
            $compilevalue .= ($tagstr[0]=='/' ? '</rw>' : '<rw>');
        } else {
            $compilevalue .= '';
        }
    }
    
   /**
    * 编译通用block标签
    * @parem tag字符
    * @parem $compilevalue 返回值
    */
    private function compiler_tag_block($tagstr, &$compilevalue)
    {
        $this->_block_id++;
        $this->att_parse->set_source($tagstr);
        $tagname = $this->att_parse->c_att->get_att('tagname');
        if( $tagname=='' )
        {
            $compilevalue = "error: {$tagstr} incorrect!".S_PHP.' if(false){ '.E_PHP;
        }
        else
        {
            $btype = $blockname = '';
            if( $tagname !='foreach' )
            {
                $btype = $this->_tpl_blocks[$tagname] = (substr($tagname, 0, 1)=='#' ? 'func' : 'block');
            }
            //块类型(block和foreach)，获取循环时key、item变量名和块名称($this->_tpl_vars['system']['blockname'])
            if( $btype != 'func' )
            {
                if( empty($this->att_parse->c_att->items['key']) )
                {
                    $this->att_parse->c_att->items['key'] = 'k';
                }
                if( empty($this->att_parse->c_att->items['item']) )
                {
                    $this->att_parse->c_att->items['item'] = 'v';
                }
                $kk = $this->att_parse->c_att->items['key'];
                $vv = $this->att_parse->c_att->items['item'];
                if( isset($this->att_parse->c_att->items['name']) )
                {
                    $blockname = $this->att_parse->c_att->items['name'];
                } else {
                    $blockname = '';
                }
            }
            
            if( $tagname != 'foreach' )
            {
                $compilevalue = "\n".S_PHP."\n";
                $compilevalue .= "\$atts_{$this->_block_id} = array(";
                $kwn = 0; 
                foreach($this->att_parse->c_att->items as $k=>$v)
                {
                    if( preg_match('/^[$]{1,}/', $v) )
                    {
                        $v = $this->parse_var( preg_replace('/^[$]{1,}/', '', $v) );
                        if( $v=='' ) $v = "''";
                        if( $kwn !=0 ) $compilevalue .= ', ';
                        $compilevalue .= "'{$k}' => {$v}";
                        $kwn++;
                    }
                    else
                    {
                        if( $kwn !=0 ) $compilevalue .= ', ';
                        $compilevalue .= "'{$k}' => '{$v}'";
                        $kwn++;
                    }
                }
                $compilevalue .= ");\n";
                if( $btype=='func' )
                {
                    $tagname = preg_replace('/^#/', '', $tagname);
                    $compilevalue .= "echo tpl_function_{$tagname}(\$this, \$atts_{$this->_block_id});\n";
                }
                else
                {
                    $compilevalue .= "\$blocks_{$this->_block_id} = tpl_block_{$tagname}(\$this, \$atts_{$this->_block_id});\n";
                    if( $blockname != '')
                    {
                        $compilevalue .= "if( empty(\$blocks_{$this->_block_id}) ) { \$blocks_{$this->_block_id} = array(); }\n";
                        $compilevalue .= "\$this->_tpl_vars['_block']['{$blockname}']['index'] = 0;\n";
                        $compilevalue .=  "foreach(\$blocks_{$this->_block_id} as \$this->_tpl_vars['{$kk}']=>\$this->_tpl_vars['{$vv}'])\n{\n";
                        $compilevalue .= "\$this->_tpl_vars['_block']['{$blockname}']['index']++;\n";
                    } else {
                        $compilevalue .=  "foreach(\$blocks_{$this->_block_id} as \$this->_tpl_vars['{$kk}']=>\$this->_tpl_vars['{$vv}'])\n{\n";
                    }
                }
            }
            else
            {
                $compilevalue = S_PHP."\n";
                //使用静态类成员函数作为传递的变量
                if( preg_match('/::/', $this->att_parse->c_att->get_att('from')) ) {
                    $v = $this->parse_function( $this->att_parse->c_att->get_att('from') );
                    $compilevalue .= "\$this->_tpl_vars['looptmp'] = {$v};\n";
                    $v = "\$this->_tpl_vars['looptmp']";
                }
                //使用普通assign的变量
                else {
                    $v = $this->parse_var( preg_replace('/^[$]{1,}/', '', $this->att_parse->c_att->get_att('from')) );
                }
                if( $v=='' )
                {
                    $compilevalue = "error: {$tagstr} incorrect!".S_PHP.'php if(false){ '.E_PHP;
                    return ;
                }
                else
                {
                    //在指定了 name 属性的情况下，允许用 
                    if( $blockname != '')
                    {
                        $compilevalue .= "\$this->_tpl_vars['_block']['{$blockname}']['index'] = 0;\n";
                        $compilevalue .=  "foreach({$v} as \$this->_tpl_vars['{$kk}']=>\$this->_tpl_vars['{$vv}'])\n{\n";
                        $compilevalue .= "\$this->_tpl_vars['_block']['{$blockname}']['index']++;\n";
                    } else {
                        $compilevalue .=  "foreach({$v} as \$this->_tpl_vars['{$kk}']=>\$this->_tpl_vars['{$vv}'])\n{\n";
                    }
                }
            }
        }
        $compilevalue .= E_PHP;
    }
    
   /**
    * 把字符串编码译为base64，并去除'='
    */
    private function base64key($str)
    {
        return str_replace('=', '', base64_encode($str));
    }
    
   /**
    * 分析变量为模板变量值
    * @parem $varstr
    */
    private function parse_var($varstr)
    {
        $varstr = preg_replace('/^[$]{1,}/', '', $varstr);
        $vars = explode('.', $varstr);
        if( $vars[0]=='request' )
        {
            $varstr = "req::\$forms";
        }
        else if( $vars[0]=='config' )
        {
            $varstr = "config::\$bone_configs";
        }
       /** 不允许模板直接输出下面的变量类型
        else if( $vars[0]=='server' )
        {
            $varstr = "\$_SERVER";
        }
        else if( $vars[0]=='cookies' )
        {
            $varstr = "\$_COOKIE";
        }
        else if( $vars[0]=='session' )
        {
            $varstr = "\$_SESSION";
        }
        else if( $vars[0]=='global' )
        {
            $varstr = "\$GLOBALS";
        }
        */
        else
        {
            $varstr = "\$this->_tpl_vars['{$vars[0]}']";
        }
        for($i=1; $i < count($vars); $i++)
        {
            if( preg_match('#\[#', $vars[$i]) ) {
                $vars[$i] = str_replace('[', "'][", $vars[$i]);
                $varstr .= "['{$vars[$i]}";
            } else if( $vars[$i][0] != '$' ) {
                $varstr .= "['{$vars[$i]}']";
            } else {
                $qkey = str_replace('$', '', $vars[$i]);
                $varstr .= "[\$this->_tpl_vars['{$qkey}']]";
            }
        }
        return $varstr;
    }
    
   /**
    * 分析函数
    * @parem $tagstr
    */
    private function parse_function($tagstr)
    {
        $func = $prec = $nextc = '';
        $taglen = strlen($tagstr);
        $isquote = false;
        for($i=0; $i < $taglen; $i++)
        {
            $prec  = isset($tagstr[$i-1]) ? $tagstr[$i-1] : '';
            $nextc = isset($tagstr[$i+1]) ? $tagstr[$i+1] : '';
            $cur_c = $tagstr[$i];
            if( $cur_c=='$' && $nextc != '.' )
            {
                $vstr = $cur_c;
                $i++;
                for(; $i < $taglen; $i++)
                {
                    if( preg_match("/[\$\w\.]/", $tagstr[$i]) )
                    {
                        $vstr .= $tagstr[$i];
                    }
                    else
                    {
                        break;
                    }
                }
                $cv = $this->parse_var($vstr);
                $func .= $isquote ? '{'.$cv.'}' : $cv;
                $i--;
            }
            else
            {
                if( $cur_c=='"' && $prec != '\\' )
                {
                    $isquote = $isquote ? false : true;
                }
                $func .= $cur_c;
            }
            $prec = '';
        }
        return $func;
    }
    
    
}//end class quicktag_compiler

/**
 * 属性解析器
 * 把 att1='123' att2=1 att3 = "U\"U\"U" 这种属性串进行解析结果传递给 attribute(属性的结构描述类)
 *
 function __quick_attribute_parse(){ }
 *
 */
class quick_attribute_parse
{
    var $c_att = '';
    var $sourceString = '';
    var $sourceMaxSize = 1024;
    var $charToLow = true;
	
    public function set_source($str = '')
    {
        $this->c_att = new quick_attribute();
        $strLen = 0;
        $this->sourceString = trim(preg_replace("/[ \r\n\t\f]{1,}/", ' ', $str));
        $strLen = strlen($this->sourceString);
        if($strLen>0 && $strLen <= $this->sourceMaxSize)
        {
            $this->parse();
        }
    }

    //解析属性
    private function parse()
    {
        $d = $tmpatt = $tmpvalue = $ddtag = '';
        $startdd = -1;
        $hasattribute=false;
        $strLen = strlen($this->sourceString);

        // 获得Tag的名称，解析到 cAtt->get_att('tagname') 中
        for($i=0; $i<$strLen; $i++)
        {
            if($this->sourceString[$i]==' ')
            {
                $this->c_att->count++;
                $tmpvalues = explode('.', $tmpvalue);
                //不转换成数组在php7会有问题
                $this->c_att->items = array();
                $this->c_att->items['tagname'] = ($this->charToLow ? strtolower($tmpvalues[0]) : $tmpvalues[0]);
                if( isset($tmpvalues[2]) )
                {
                    $okname = $tmpvalues[1];
                    for($j=2;isset($tmpvalues[$j]);$j++)
                    {
                        $okname .= "['".$tmpvalues[$j]."']";
                    }
                    $this->c_att->items['name'] = $okname;
                }
                else if(isset($tmpvalues[1]) && $tmpvalues[1]!='')
                {
                    $this->c_att->items['name'] = $tmpvalues[1];
                }
                $tmpvalue = '';
                $hasattribute = true;
                break;
            } else {
                $tmpvalue .= $this->sourceString[$i];
            }
        }
        //不存在属性列表的情况
        if(!$hasattribute)
        {
            $this->c_att->count++;
            $tmpvalues = explode('.', $tmpvalue);
            $this->c_att->items['tagname'] = ($this->charToLow ? strtolower($tmpvalues[0]) : $tmpvalues[0]);
            if( isset($tmpvalues[2]) )
            {
                $okname = $tmpvalues[1];
                for($i=2;isset($tmpvalues[$i]);$i++)
                {
                    $okname .= "['".$tmpvalues[$i]."']";
                }
                $this->c_att->items['name'] = $okname;
            }
            else if(isset($tmpvalues[1]) && $tmpvalues[1]!='')
            {
                $this->c_att->items['name'] = $tmpvalues[1];
            }
            return ;
        }
        $tmpvalue = '';
        //如果字符串含有属性值，遍历源字符串,并获得各属性
        for($i; $i<$strLen; $i++)
        {
            $d = $this->sourceString[$i];
            //查找属性名称
            if($startdd==-1)
            {
                if($d != '=')
                {
                    $tmpatt .= $d;
                }
                else
                {
                    $tmpatt = $this->charToLow ? strtolower(trim($tmpatt)) : trim($tmpatt);
                    $startdd=0;
                }
            }
            //查找属性的限定标志
            else if($startdd==0)
            {
                switch($d)
                {
                    case ' ':
                        break;
                    case '\'':
                        $ddtag = '\'';
                        $startdd = 1;
                        break;
                    case '"':
                        $ddtag = '"';
                        $startdd = 1;
                        break;
                    default:
                        $tmpvalue .= $d;
                        $ddtag = ' ';
                        $startdd = 1;
                        break;
                }
            }
            else if($startdd==1)
            {
                if($d==$ddtag && ( isset($this->sourceString[$i-1]) && $this->sourceString[$i-1]!="\\") )
                {
                    $this->c_att->count++;
                    $this->c_att->items[$tmpatt] = trim($tmpvalue);
                    $tmpatt = $tmpvalue = '';
                    $startdd = -1;
                }
                else
                {
                    $tmpvalue .= $d;
                }
            }
        }//end for
        //最后一个属性的给值
        if($tmpatt != '')
        {
            $this->c_att->count++;
            $this->c_att->items[$tmpatt] = trim($tmpvalue);
        }//print_r($this->c_att->items);

    }// end func
}
/**
 * 属性的数据描述
 function __quick_attribute(){ }
 */
class quick_attribute
{
    var $count = -1;
    var $items = '';

    //获得某个属性
    public function get_att($str, $dfstr='')
    {
        if($str == '')
        {
            return '';
        }
        else
        {
            return isset($this->items[$str]) ? $this->items[$str] : $dfstr;
        }
    }

    //判断属性是否存在
    public function is_att($str)
    {
        return isset($this->items[$str]);
    }

    //获得标记名称
    public function get_tagname()
    {
        return $this->get_att('tagname');
    }

    // 获得属性个数
    public function get_count()
    {
        return $this->count+1;
    }
}
