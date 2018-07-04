<{include file="admin/header.tpl"}>
<script lang='javascript'>
function do_delete()
{
    document.form1.even.value = 'delete';
    var msg = "你确定要删除选中的记录？！";
    msg += "<br/><a href='javascript:tb_remove();'>&lt;&lt;点错了</a> &nbsp;|&nbsp; <a href='javascript:document.form1.submit();'>确定要删除&gt;&gt;</a>";
    tb_showmsg(msg);
}
function delete_old()
{
    document.form1.even.value = 'delete';
    var msg = "你确定要清空三个月前记录？<br /><font color='#666'>(系统会备份一份记录到文本)</font>";
    msg += "<br/><a href='javascript:tb_remove();'>&lt;&lt;点错了</a> &nbsp;|&nbsp; <a href='?ct=system&ac=del_old_login_log;'>确定操作&gt;&gt;</a>";
    tb_showmsg(msg);
}
</script>
</head>
<body class="frame-from">
<div id="contents">
<form name="formsearch" method="GET">
<input type='hidden' name='ct' value='system' />
<input type='hidden' name='ac' value='login_log' />
<input type='hidden' name='even' value='list' />
<input type='hidden' name='orderby' value='' />
<dl class="search-class">
    <dd>
    关键字：
    <input type='text' name='keyword' style='width:200px;' class='text' value="<{$keyword}>" />
    <button type='submit'>搜索</button>
    </dd>
</dl>
</form>

<form name="form1" action="?ct=system&ac=login_log" method="POST">
<input type="hidden" name="even" value="delete" />
<table class="table-sort table-operate">
  <tr>
    <th> <label><input type="checkbox" rel="parent"/> 选择</label> </th>
	<th> 用户id </th>
	<th> 用户名 </th>
	<th> 登录ip </th>
	<th> 登录时间 </th>
	<th> 应用池 </th>
	<th> 登录时状态 </th>
  </tr>
  <{lurd_list item='v'}>
  <tr>
  <td> <input type="checkbox" name="id[]" value="<{$v.id}>" rel="child" class="cbox" /> <{$v.id}> </td>
  <td> <{$v.admin_id}> </td>
  <td> <{$v.accounts}> </td>
  <td> <{$v.loginip}> </td>
  <td> <{$v.logintime|date('Y-m-d H:i', @me)}> </td>
  <td> <{$v.pools}> </td>
  <td> <{if $v.loginsta==1}>成功<{else}><font color='red'>失败</font><{/if}> </td>
  </tr>
  <{/lurd_list}>
  <tr>
</table>
</form>
</div>

<div id="bottom">
    <div class="fl">
        <button type="button" onclick="delete_old()">清空三个月前记录</button>&nbsp;
        <button type="button" onclick="do_delete();">删除选中记录</button>
    </div>
    <div class="pages">
        <{$lurd_pagination}>
    </div>
</div>

</body>
</html>
 