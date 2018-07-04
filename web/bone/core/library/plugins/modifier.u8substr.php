<?php
/**
  * 字符串截取
  * @param &$tpl 引用的模板类对象实例
  * @parem $atts 传递的属性参数
  * @return string
  */
function tpl_modifier_u8substr( $str, $len, $start=0 )
{
   $str = preg_replace('#&([^;]+);#', '', $str);
   return util::utf8_substr($str, $len, $start);
}
