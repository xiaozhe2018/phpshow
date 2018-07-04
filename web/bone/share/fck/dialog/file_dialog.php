<?php
if( !defined('PATH_ROOT') ) {
    require_once dirname(__FILE__).'/config.php';
}

//变量获取
$dlg_url  = req::item('dlg_url', '');
$job      = req::item('job', '');
$dlg_i    = req::item('dlg_i', 'GetBoneDlgUpload');   //父窗口接收返回文件的接口函数
$dlg_v    = req::item('dlg_v', '');  //父窗口如果有预览区域这值表示 id='div+dlg_v'
$dlg_s    = req::item('dlg_s', 'name');
if( !isset($_cfg_mediatype) ) $_cfg_mediatype = 10;

//上传文件操作
$errmsg = '';
$upfilename = '';
if( $job=='upload' )
{
    try
    {
        $use_oldname = req::item('oldname', 0)==0 ? false : true;
        $filenames = pub_media_dlg::uploadfile($dlg_url, $_cfg_mediatype, 'uploadfile', $use_oldname);
        if( strlen($filenames['path']) > strlen($dlg_url) ) {
            $dlg_url = $filenames['path'];
        }
        $upfilename = $filenames['filename'];
        //对图片做处理
        if( $_cfg_mediatype==1 )
        {
            $imgurl = $filenames['path'].'/'.$filenames['filename'];
            $img_resize = req::item('img_resize', '');
            $img_w = req::item('iwidth', 0);
            $img_h = req::item('iheight', 0);
            if( $img_resize==1 && $img_w > 0 && $img_h > 0 )
            {
                cls_image::thumb_df( PATH_ROOT.$imgurl, $img_w, $img_h );
            }
            $img_mark = req::item('img_mark', '');
            if( $img_mark==1)
            {
                cls_image::watermark_df( PATH_ROOT.$imgurl );
            }
        }
    }
    catch(Exception $e)
    {
        $errmsg = $e->getMessage();
    }
}
//上传多个文件（只适合图片）
else if( $job=='upload_muti' )
{
    $restr = '';
    $item = req::item('totalform', 1, 'int');
    $isdd = req::item('dd', 0);
    $dd_w = req::item('w', 0);
    $dd_h = req::item('h', 0);
    $needwatermark = req::item('needwatermark', 0);
    $islimitwidth  = req::item('islimitwidth', 0);
    $limitwidth    = req::item('limitwidth', 700);
    $alttitle = req::item('alttitle', 0);
    for($i=1; $i <= $item; $i++)
    {
        try
        {
            $alt = req::item('alt'.$i, '');
            $filenames = pub_media_dlg::uploadfile($dlg_url, 1, 'imgfile'.$i);
            $imgurl = $filenames['path'].'/'.$filenames['filename'];
            $im = new cls_image( PATH_ROOT.$imgurl );
            //限制图片宽度
            if( $islimitwidth==1 ) {
                $im->thumb($limitwidth, 1000, PATH_ROOT.$imgurl, 'w');
                $im = new cls_image( PATH_ROOT.$imgurl );
            }
            list($width, $height) = $im->image_info;
            if( $isdd==1 ) {
                $ddurl = substr($imgurl, 0, strlen($imgurl) - 4).'_dd'.substr($imgurl, -4, 4);
                //生成缩略图并重新获取图片信息
                $im->thumb($dd_w, $dd_h, PATH_ROOT.$ddurl, 'auto');
                $im = new cls_image( PATH_ROOT.$ddurl );
                list($width, $height) = $im->image_info;
                $restr .= "<a href='{$imgurl}' target='_blank'><img src='{$ddurl}' width='{$width}' alt='{$alt}' border='0'/></a><br />\r\n";
                if( $alttitle==1 ) {
                   $restr .= "{$alt}<br />\r\n"; 
                }
            } else {
                $restr .= "<div align='center'>\r\n<a href='{$imgurl}' target='_blank'><img src='{$imgurl}' width='{$width}' alt='{$alt}' border='0'/></a>\r\n";
                if( $alttitle==1 ) {
                   $restr .= "<br />{$alt}\r\n"; 
                }
                $restr .= "</div>\r\n";
            }
            //处理水印
            if( $needwatermark==1 ) {
                cls_image::watermark_df( PATH_ROOT.$imgurl );
            }
        }
        catch(Exception $e)
        {
            ;
        }
    }
    $restrs[] = $restr;
    $restr = 'var restr = '.json_encode($restrs).";\r\n";
    echo "<script language='javascript'>\r\n";
    echo $restr;
    echo "parent.document.getElementById('upload_area').style.display = 'none';\r\n";
    echo "parent.document.getElementById('imghtml').value = restr[0];\r\n";
    echo "parent.document.getElementById('upload_ok_area').style.display = 'table-row';\r\n";   
    echo "</script>";
    exit();
}

//浏览目录
$data    = pub_media_dlg::read_dir($dlg_url, $dlg_s, $_cfg_mediatype);
$baseurl = "?dlg_url=%s&dlg_i={$dlg_i}&dlg_v={$dlg_v}&dlg_s=%s";
$dlg_url = preg_replace("#/$#", '', $dlg_url);

?>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
<title> 文件浏览器 </title>
<link href='img/base.css' rel='stylesheet' type='text/css'>
<script language='javascript'>
function TNav()
{
    if(window.navigator.userAgent.indexOf("MSIE")>=1) return 'IE';
    else if(window.navigator.userAgent.indexOf("Firefox")>=1) return 'FF';
    else return "OT";
}
function return_file( reimg )
{
    //通过父窗口的接口函数接收返回的图片并进行相应处理
    if( window.opener.<?php echo $dlg_i; ?> && typeof(window.opener.<?php echo $dlg_i; ?>)=="function") {
        window.opener.<?php echo $dlg_i; ?>( reimg );
    }
	if(document.all) window.opener = true;
    window.close();
}
</script>
</head>
<body background='img/allbg.gif' leftmargin='0' topmargin='0'>
<table width='100%' border='0' cellpadding='0' cellspacing='1' bgcolor='#CBD8AC'>
  <tr bgcolor='#FFFFFF'>
  <td>
    <table width='100%' border='0' cellspacing='0' cellpadding='2'>
        <tr height="24">
            <td class="col1"><a href="<?php echo sprintf($baseurl, $dlg_url, 'name'); ?>"><strong>文件名</strong></a></td>
            <td class="col2"><a href="<?php echo sprintf($baseurl, $dlg_url, 'size'); ?>"><strong>文件大小</strong></a></td>
            <td class="col1"><a href="<?php echo sprintf($baseurl, $dlg_url, 'mtime'); ?>"><strong>修改时间</strong></a></td>
            <td class="col1"><strong>查看</strong></td>
        </tr>
<?php
//显示错误信息
if( $errmsg != '' )
{
    echo "        <tr> <td class=\"colmsg\" colspan=\"4\">提示：{$errmsg}</td> </tr>\r\n";
}
//上级目录
if( $data['parent'] != '' )
{
    $_durl = sprintf($baseurl, $data['parent'], $dlg_s);
    echo "        <tr> <td class=\"coldirp\" colspan=\"4\"><a href=\"{$_durl}\">上级目录</a> <div style='float:right;padding-right:8px'>当前目录：{$data['url']}</div></td> </tr>\r\n";
} else {
    echo "        <tr> <td class=\"coldirp\" colspan=\"4\">根目录  <div style='float:right;padding-right:8px'>当前目录：{$data['url']}</div></td> </tr>\r\n";
}
//列出目录
foreach($data['dirs'] as $_d)
{
    $_durl = sprintf($baseurl, $data['url'].'/'.$_d, $dlg_s);
    echo "        <tr> <td class=\"coldir\" colspan=\"4\"><a href=\"{$_durl}\">{$_d}</a></td> </tr>\r\n";
}
//列出文件
if( $dlg_url=='' )  {
    $dlg_url = $data['url'];
}
foreach($data['files'] as $_name => $_f)
{
    $_durl = $dlg_url.'/'.$_name;
    $mtime = date('Y-m-d H:i:s', $_f['mtime']);
    $ico = pub_media_dlg::get_ico($_name, $_cfg_mediatype);
    $st = ($_name == $upfilename ? " style='color:red;' " : "");
    echo "        <tr>
            <td class=\"colf\" style=\"background-image:url({$ico});\"> <a href=\"javascript:return_file('{$_durl}');\"{$st}>{$_name}</a> </td>
            <td class=\"colt\"> {$_f['ksize']}K </td>
            <td class=\"colt\"> {$mtime} </td>
            <td class=\"colt\"> <a href=\"{$_durl}\" target=\"_blank\"><img src='img/picviewnone.gif'></a> </td>
        </tr>\r\n";
}
?>
    </table>
    <!-- //上传表单 -->
    <form action='' method='POST' enctype="multipart/form-data" name='myform'>
    <input type='hidden' name='dlg_url' value='<?php echo $data['url']; ?>'>
    <input type='hidden' name='dlg_v' value='<?php echo $dlg_v; ?>'>
    <input type='hidden' name='dlg_i' value='<?php echo $dlg_i; ?>'>
    <input type='hidden' name='job' value='upload'>
    <table width='100%' border='0' cellpadding='0' cellspacing='0'>       
    <tr>
        <td background="img/tbg.gif" bgcolor="#99CC00" height="28">
        &nbsp;上　传： <input type='file' name='uploadfile' style='width:150px'/>
<?php
if( $_cfg_mediatype==1 )
{
    $_w = pub_media_dlg::$img_width;
    $_h = pub_media_dlg::$img_height;
    echo <<<EOT
            <input type='checkbox' name='img_mark' value='1' class='np' />水印
            <input type='checkbox' name='img_resize' value='1' class='np' />限制宽：
            <input type='text' style='width:30px' name='iwidth' value='{$_h}' />
            高：<input type='text' style='width:30px' name='iheight' value='{$_w}' />
EOT;
}
else
{
    echo "<input type='checkbox' name='oldname' value='1' class='np' checked>保持原名(不支持中文名)";
}
?>
            <input type='submit' name='sb1' value='确定' />
            </td>
        </tr>
        </table>
        </form>
  </td>
  </tr>
</table>
</body>
</html>