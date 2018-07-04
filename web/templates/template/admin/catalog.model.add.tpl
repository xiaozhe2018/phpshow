<{include file="admin/header.tpl"}>
</head>
<body class="frame-from">
<div class="tboxform">
<form name="form1" action="?ct=catalog&ac=model&even=saveadd" method="POST" enctype="multipart/form-data">
<table class="form">
 <tr>
  <td class='title' style='width:110px;'>模型id(cmid)：</td>
  <td class='fitem'>
    <input type='text' name='cmid' id='lurd_cmid' class='text s' value='' /> <span class='info'>(英文，12个字符以内)</span>
  </td>
</tr>
 <tr>
  <td class='title' style='width:110px;'>分类模型名：</td>
  <td class='fitem'>
    <input type='text' name='cmname' id='lurd_cmname' class='text' value='' /> <span class='info'>(中文)</span>
  </td>
</tr>
<tr>
  <td class='title'>分类数据表：</td>
  <td class='fitem'>
    <{$tablebase}><input type='text' name='cmtable' id='lurd_cmtable' class='s' style='width:96px;' value='base' />
    <br /><span class='info'>(如果不是复杂的分类，此项建议使用默认表)</span>
 </td>
</tr>
<tr>
  <td class='title'>排序：</td>
  <td class='fitem'>
    <input type='text' name='sortrank' class='text s' value='0' />
    <span class='info'>(大的靠前)</span>
  </td>
</tr>
<tr>
  <td class='title'>管理网址：</td>
  <td class='fitem'>
    <input type='text' name='adm_url' class='text' value='' />
    <br /><span class='info'>(如果不是复杂的分类，此项建议使用默认托管)</span>
  </td>
</tr>
<tr>
  <td class='title'>出现在快捷菜单：</td>
  <td class='fitem'>
    <input type='radio' name='showmenu' value='0' checked /> 不出现
    <input type='radio' name='showmenu' value='1' /> 出现
  </td>
</tr>
<tr>
  <td class='title'>模型删除操作：</td>
  <td class='fitem'>
    <input type='radio' name='delopt' value='0' checked /> 不允许
    <input type='radio' name='delopt' value='1' /> 允许
  </td>
</tr>
<tr>
  <td colspan='2' align='center' height='40' style="padding-left:60px;">
      <button type="submit">保存</button> &nbsp;&nbsp;&nbsp;
      <button type="reset">重设</button>
  </td>
</tr>
</table>
</form>
</div>
</body>
</html>
