<?php
if( !defined('PHPBONE') ) exit('Request Error!');
/**
 * 日期选择框最终日期
 */
class mod_seltime
{
    /**
     * 设置时间段
     * @param integer 0 全部 1 今天 2  昨天 3 本星期 4 上一个星期
     * 5: //上上星期 6: //本月 7 上月   默认是以 / 分割的标准时间段
     * @param boolean $format true 返回时间粗格式 false 直接返回标准时间
     */
    public static function get_time($n = 0, $format = false)
    {
        $ret = array();
        switch ($n) {
            case 0: //全部
                $ret['s_date'] = '';
                $ret['e_date'] = '';
                break;
            case 1://今天
                $ret['s_date'] = date('Y-m-d');
                $ret['e_date'] = '';
                break;
            case 2:// 昨天
                $ret['s_date'] = date('Y-m-d', strtotime('-1 day'));
                $ret['e_date'] = date('Y-m-d', strtotime('-1 day'));
                break;
            case 3://本星期
                $ret = self::get_one_week_rank(date('Y-m-d'));
                break;
            case 4://上一个星期
                $str_data = date('Y-m-d',strtotime('-1 week'));
                $ret      = self::get_one_week_rank($str_data);
                break;
            case 6: //本月
                $ret = self::get_one_month_rank(date('Y-m-d'));
                break;
            case 7://上月
                $str_data = date('Y-m-d', strtotime('-1 month'));
                $ret      = self::get_one_month_rank($str_data);
                break;
            default:
                list($ret['s_date'], $ret['e_date']) = explode(' / ', $n);
                break;
        }
        return $ret;
    }
    
   /**
    * 转化成可以用于查询的日期
    */
    public static function format_date($date)
    {
        $dates = explode('-', $date);
        $y = substr($dates[0], 2, 2);
        $m = $dates[1];
        if( strlen($m)==1 ) $m = '0'.$m;
        $d = $dates[2];
        if( strlen($d)==1 ) $d = '0'.$d;
        return $y.$m.$d;
    }

    /**
     * 获取某一周的标准时间
     * @param  [type] $date [description]
     * @return [type]       [description]
     */
    private static function get_one_week_rank($date)
    {
        $ret = array();
        $timestamp = strtotime($date);
        $wd = date('N', $timestamp);
        //$w = strftime('%u', $timestamp);
        $ret['s_date'] = date('Y-m-d', $timestamp-($wd-1)*86400);
        $ret['e_date'] = date('Y-m-d', $timestamp+(7-$wd)*86400);
        return $ret;
    }
     
    /**
    * 获取指定日期所在月的开始日期与结束日期
    * @param  [type] $date [description]
    * @return [type]       [description]
    */
    private static function get_one_month_rank($date)
    {
        $ret = array();
        $timestamp = strtotime($date);
        $mdays = date('t', $timestamp);
        $ret['s_date'] = date('Y-m-01', $timestamp);
        $ret['e_date'] = date('Y-m-'. $mdays, $timestamp);
        return $ret;
    }

}