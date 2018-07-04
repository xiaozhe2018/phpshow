<?php
/**
  * 自定义函数示例
  *
  * @param &$tpl 引用的模板类对象实例
  * @parem $atts 传递的属性参数
  *
  * @return string
  *
  */
function tpl_function_assign(&$tpl, $atts)
{
     if(!empty($atts['name']) && !empty($atts['value'])) {
        $tpl->assign($atts['name'], $atts['value']);
     }
}
