<{include file="admin/header.tpl"}>
</head>
<body class="frame-from">
<div class="tboxform">
<form name="form1" action="?ct=catalog&ac=model&even=saveedit" method="POST" enctype="multipart/form-data">
<table class="form">
<{lurd_list item='v'}>
    <input type='hidden' name='cmid' value='<{$v.cmid}>' />
<tr>
  <td class='title' style='width:110px;'>分类模型名：</td>
  <td class='fitem'><input type='text' name='cmname' id='lurd_cmname' class='text' value='<{$v.cmname}>' /></td>
</tr>
<tr>
  <td class='title'>分类数据表：</td>
  <td class='fitem'>
    <{$tablebase}><input type='text' name='cmtable' id='lurd_cmtable' class='s' style='width:96px;' value='<{$v.cmtable}>' />
    <input type='hidden' name='old_cmtable' id='lurd_cmtable' value='<{$v.cmtable}>' />
    <br /><span class='info'>(如果不是复杂的分类，此项建议使用默认表)</span>
  </td>
</tr>
<tr>
  <td class='title'>排序：</td>
  <td class='fitem'>
    <input type='text' name='sortrank' class='text s' value='<{$v.sortrank}>' />
    <span class='info'>(大的靠前)</span>
  </td>
</tr>
<tr>
  <td class='title'>管理网址：</td>
  <td class='fitem'>
    <input type='text' name='adm_url' class='text' value='<{$v.adm_url}>' />
    <br /><span class='info'>(如果不是复杂的分类，此项建议使用默认托管)</span>
  </td>
</tr>
<tr>
  <td class='title'>出现在快捷菜单：</td>
  <td class='fitem'>
    <input type='radio' name='showmenu' value='0' <{if $v.showmenu=='0'}>checked<{/if}> /> 不出现
    <input type='radio' name='showmenu' value='1' <{if $v.showmenu=='1'}>checked<{/if}> /> 出现
  </td>
</tr>
<tr>
  <td class='title'>模型删除操作：</td>
  <td class='fitem'>
    <input type='radio' name='delopt' value='0' <{if $v.delopt=='0'}>checked<{/if}> /> 不允许
    <input type='radio' name='delopt' value='1' <{if $v.delopt=='1'}>checked<{/if}> /> 允许
  </td>
</tr>
<{/lurd_list}>
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
