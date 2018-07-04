<?php
/**
  * 自定义函数
  *
  * @param &$tpl 引用的模板类对象实例
  * @parem $atts 传递的属性参数
  *
  * @return string
  *
  */
function tpl_function_frame_union(&$tpl, $atts)
{
    if( empty($atts['do']) || empty($atts['var']) )
    {
        return;
    }
    //还原应用池名称
    if($atts['do']=='pools')
    {
        $arr = bone::$auth->pur_configs['pools'];
        return isset($arr[$atts['var']]['name']) ? $arr[$atts['var']]['name'] : '';
    }
    //还原应用池名称
    else if($atts['do']=='groups')
    {
        $arr = bone::$auth->pur_configs['pools'];
        $strings = explode(',', $atts['var']);
        $okstr = '';
        foreach($strings as $str)
        {
            $str = trim($str);
            list($p, $g) = explode('_', $str);
            $str = isset($arr[$p]['private'][$g]) ? $arr[$p]['private'][$g]['name'] : $p.'_'.$g;
            $okstr .= ($okstr=='' ? $str : ','.$str);
        }
        return $okstr;
    }
    //从数组取keyvalue
    else
    {
        return isset($atts['from'][$atts['var']]) ? $atts['from'][$atts['var']] : '';
    }
}
