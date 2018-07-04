<{include file="admin/header.tpl"}>
</head>
<body class="frame-from">
<script  language='javascript'>
function GetBoneDlgUpload_lurd_ico( reurl ) {
document.getElementById('lurd_ico').value = reurl;
    if( document.getElementById('preimg_lurd_ico') ) {
        document.getElementById('preimg_lurd_ico').src    = reurl;
    }
}
</script>
<div class="tboxform">
<form name="form1" action="?" method="POST" enctype="multipart/form-data">
<input type='hidden' name='ct' value='catalog' />
<input type='hidden' name='cmid' value='<{$infos.cmid}>' />
<input type='hidden' name='even' value='saveedit' />
<table class="form">
<{lurd_list item='v'}>
    <input type='hidden' name='cid' value='<{$v.cid}>' />
<tr>
  <td class='title' style='width:90px'>分类模型：</td>
  <td class='fitem'>
    <{$infos.cur_model.cmname}>
  </td>
</tr>
<tr>
  <td class='title'>隶属父类：</td>
  <td class='fitem'>
    <select name='pid'/>
    <{#catalog_options cmid=$infos.cmid selid=$v.pid dfname='--顶级分类--' cats=$cats}>
    </select>
    <span class='info'>(上级分类不允许向下级移动)</span>
 </td>
</tr>
<tr>
  <td class='title'>分类名称：</td>
  <td class='fitem'><input type='text' name='cname' id='lurd_cname' class='text' value='<{$v.cname}>' /></td>
</tr>
<tr>
  <td class='title'>排序：</td>
  <td class='fitem'>
<input type='text' name='sortrank' class='text s' value='<{$v.sortrank}>' /><span class='info'>(值大在前)</span>
  </td>
</tr>
<tr>
  <td class='title'>分类ico：</td>
  <td class='fitem'>
    <input type='text' name='ico' id='lurd_ico' class='text' value='<{$v.ico}>' />
    <input type='button' name='dlg_btn_1' value='浏览...' cls='dlg_btn' onclick='window.open("../share/fck/dialog/select_images.php?dlg_i=GetBoneDlgUpload_lurd_ico", "dlg_popUpImgWin", "scrollbars=yes,resizable=yes,statebar=no,width=600,height=400,left=100,top=100");' />
    <br />
    <img src='<{if $v.ico==''}>../../static/frame/admin/images/preview.gif<{else}><{$v.ico}><{/if}>' id='preimg_lurd_ico' width='64' style='margin-top:10px;' />
</td>
</tr>

<{/lurd_list}>
<tr>
  <td colspan='2' align='center' height='60'>
      <button type="submit">保存</button> &nbsp;&nbsp;&nbsp;
      <button type="reset">重设</button>
  </td>
</tr>
</table>
</form>
</div>
</body>
</html>
