<{include file="admin/header.tpl"}>
<script lang='javascript'>
function show_data(nid)
{
    tb_show('浏览记录', '?ct=system&ac=oplog&even=edit&id='+ nid +'&TB_iframe=true&height=250&width=400', true);
}
function do_delete()
{
    document.form1.even.value = 'delete';
    var msg = "你确定要删除选中的记录？！";
    msg += "<br/><a href='javascript:tb_remove();'>&lt;&lt;点错了</a> &nbsp;|&nbsp; <a href='javascript:document.form1.submit();'>确定要删除&gt;&gt;</a>";
    tb_showmsg(msg);
}
</script>
<style>
    .urlinfo {
        width:220px;height:20px;line-height:20px;overflow:hidden;
    }
    .msg {
        width:280px;
    }
</style>
</head>
<body class="frame-from">
<div id="contents">
<form name="formsearch" action="?ct=system&ac=oplog&even=list" method="POST">
<input type='hidden' name='orderby' value='' />
<dl class="search-class">
    <dd>
    关键字：
    <input type='text' name='keyword' style='width:200px;' class='text' value="<{$keyword}>" />
    <button type='submit'>搜索</button>
    </dd>
</dl>
</form>

<form name="form1" action="?ct=system&ac=oplog" method="POST">
<input type="hidden" name="even" value="delete" />
<table class="table-sort table-operate">
  <tr>
    <th> <label><input type="checkbox" rel="parent"/> 选择</label> </th>
    <th><strong>记录id</strong></th>
    <th><strong>用户</strong></th>
    <th><strong>消息</strong></th>
    <th><strong>时间</strong></th>
    <th><strong>IP</strong></th>
    <th><strong>操作网址</strong></th>

  </tr>
  <{lurd_list item='v'}>
  <tr>
  <td>
    <a href="javascript:show_data('<{$v.id}>');"><img src='../../static/frame/admin/images/icons/text.gif' alt='浏览' title='浏览' border='0' /></a>
    <input type='checkbox' name='id[]' value='<{$v.id}>' rel="child" class="cbox" />
  </td>
  <td> <{$v.id}> </td>
  <td> <{$v.user_name}> </td>
  <td> <div class='msg'><{$v.msg}></div> </td>
  <td> <{$v.do_time|date('Y-m-d H:i', @me)}> </td>
  <td> <{$v.do_ip}> </td>
  <td> 
    <div class='urlinfo' title='<{$v.do_url|str_replace("'", "‘", @me)}>'> <{$v.do_url}> </div>
  </td>
  </tr>
  <{/lurd_list}>
  <tr>
</table>
</form>
</div>

<div id="bottom">
    <div class="fl">
        <button type="button" onclick="do_delete();">删除选中记录</button>
    </div>
    <div class="pages">
        <{$lurd_pagination}>
    </div>
</div>

</body>
</html>
 