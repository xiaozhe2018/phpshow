<?php
/**
 * 对指定变量执行某些Lurd类里的格式化操作
 * <{#lurd do="make_key" format=field_list var=field_array }>
 * <{#lurd do="format_date" format="Y-m-d" var="1261360460" }>  <{#lurd do="format_date" }> 无参数显示date( 'Y-m-d H:i:s' )
 * <{#lurd do="format_float" format="%0.4f" var="92.4325544" }>
 */
function tpl_function_chip(&$tpl, $atts)
{
    if ( empty($atts['do']) ) {
        return '';
    }
    if($atts['do']=='format_date' || $atts['do']=='format_float')
    {
        if ( empty($atts['var']) ) {
            $atts['var'] = 0;
        }
    }
    //格式式日期时间
    else if($atts['do']=='format_date') 
    {    
         if( empty($atts['format']) ) {
                $atts['format'] = 'Y-m-d H:i:s';
         }
         if( empty($atts['var']) ) {
                $atts['var'] = time();
         }
         return date( $atts['format'], $atts['var'] );
    }
    //格式化浮点数
    else if($atts['do']=='format_float') 
    {    
        if( empty($atts['var']) ) {
            return '0';
        }
        if( empty($atts['format']) ) {
            $atts['format'] = '%0.4f';
        }
        return sprintf($atts['format'], $atts['var']);
    }
    //合并字段为md5数组
    else if($atts['do']=='make_key') 
    {
        if( empty( $atts['format'] ) || empty($atts['var']) ) {
            return '';
        }
        $str = '';
        $keys = explode('', $atts['format']);
        foreach($keys as $k) {
            $str .= $atts['var'][$k];
        }
        return md5($str);
    }
    return '';
}

