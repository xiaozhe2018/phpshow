<?php
/**
 *
 * 开发人员调试开发类
 *
 */
class mod_admin_debug
{
    
    /**
     * 获取当前库的所有数据表信息
     * @param $prefix = '' 表前缀（返回特定前缀的表）
     * @return array();
     */
    public static function get_tables( $prefix='' )
    {
        $rearr = array();
        $rs = db::query(' Show Tables; ', false, 1);
        while( $row = db::fetch_one($rs, DB_GET_NUM) )
        {
            $tablename = $row[0];
            if( $prefix != '' && !preg_match("/^".$prefix."/", $tablename) ) {
                continue;
            }
            $drow = db::get_one("Select count(*) as dd From `{$tablename}` ");
            $rearr[ $tablename ] = $drow['dd'];
        }
        return $rearr;
    }
    
    /**
     * 分析具体表的结构
     * @$tablename 表名
     * @param $row_num 记录数
     * @return string
     */
    protected static function _analyse_table( $tablename, $row_num )
    {
        //获取表信息
        $rs = db::query(" Show CREATE TABLE `{$tablename}` ", false);
        $row = db::fetch_one($rs, DB_GET_NUM);
        $tableinfo = $row[1];
        
        //分析表结构
        $flines = explode("\n", $tableinfo);
        $addinfo = $tbinfo = $tb_comment = '';
        $fields = array();
        foreach($flines as $line)
        {
            $line = trim($line);
            if( $line=='' ) continue;
            if( preg_match('/CREATE TABLE/i', $line) ) continue;
            if( !preg_match('/`/', $line) )
            {
                $arr = '';
                preg_match("/ENGINE=([a-z]*)(.*)DEFAULT CHARSET=([a-z0-9]*)/i", $line, $arr);
                $tbinfo = "ENGINE=".$arr[1].'/CHARSET='.$arr[3];
                $arr = '';
                preg_match("/comment='([^']*)'/i", $line, $arr);
                if( isset($arr[1]) )
                {
                    $tb_comment = $arr[1];
                }
                continue;
            }
            if( preg_match('/KEY/', $line) )
            {
                $addinfo .= $line."<br />\n";
            }
            else
            {
                $arr = '';
                $nline = preg_replace("/comment '([^']*)'/i", '', $line);
                preg_match("/`([^`]*)` (.*)[,]{0,1}$/U", $nline, $arr);
                $f = $arr[1];
                $fields[ $f ][0] = $arr[2];
                $fields[ $f ][1] = '';
                $arr = '';
                preg_match("/comment '([^']*)'/i", $line, $arr);
                if( isset($arr[1]) )
                {
                    $fields[ $f ][1] = $arr[1];
                }
            
            }
        }
        //返回html
        $tablehtml = "    <table width=\"96%\" align=\"center\" border=\"0\" cellpadding=\"5\" cellspacing=\"1\" bgcolor=\"#C1D1A3\" style=\"font-size:14px;margin-bottom:10px\">
    <tr>
        <td height=\"34\" colspan=\"3\" bgcolor=\"#DDEDA5\">
        <a name=\"{$tablename}\"></a>
        <table width=\"90%\" border=\"0\" cellspacing=\"1\" cellpadding=\"1\">
            <tr>
                <td width=\"35%\"><strong>表名：{$tablename}</strong> <br />($tbinfo)</td>
                <td width=\"65%\"><strong>记录数：</strong>{$row_num} &nbsp; &nbsp;<strong>说明：</strong>{$tb_comment}</td>
            </tr>
        </table></td>
    </tr>
    <tr>
        <td width=\"20%\" height=\"28\" bgcolor=\"#F7FDEA\">字段名</td>
        <td width=\"28%\" bgcolor=\"#F7FDEA\">说明描述</td>
        <td bgcolor=\"#F7FDEA\">具体参数</td>
    </tr>\n";
        foreach($fields as $k=>$v)
        {
            $tablehtml .= "    <tr height=\"24\" bgcolor=\"#FFFFFF\">
        <td><b>{$k}</b></td>
        <td>{$v[1]}</td>
        <td>{$v[0]}</td>
    </tr>\n";
        }
        $tablehtml .= "    <tr>
        <td height=\"28\" colspan=\"3\" bgcolor=\"#F7FDEA\">
        <b>索引：</b><br />
        {$addinfo}
        </td>
    </tr>
    </table>";
        return $tablehtml;
    }
    
    /**
     * 列出数据库的所有表结构
     */
    public static function list_table_infos( $prefix='' )
    {
        $namehtml = $tablehtml = '';
        $tables = self::get_tables( $prefix );
        foreach($tables as $table => $num)
        {
            $namehtml .= "<a href='#{$table}'>{$table}</a> | ";
            $tablehtml .= self::_analyse_table( $table, $num );
        }
        $htmlhead = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
<style>
* {
    font-size:14px;
    font-family:Arial, \"宋休\", \"Courier New\";
}
a {
  text-decoration:none;
}
</style>
<title>数据库说明文档</title>
</head>";

    echo $htmlhead;
    echo "<table align='center' width='96%' style='margin-bottom:8px' ><tr><td>&nbsp; ".$namehtml."</td></tr></table>";
    echo $tablehtml;
    echo "</body>\n</html>";
    exit();

    }//end show
}
