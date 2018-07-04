<{include file="admin/header.tpl"}>
<script lang='javascript'>
function add_data(nid)
{
    tb_show('增加记录', '?ct=test_user&even=add&tb=bone_test_user&TB_iframe=true&height=350&width=600', true);
}
function show_data(nid)
{
    tb_show('浏览/编辑记录', '?ct=test_user&even=edit&admin_id='+ nid +'&TB_iframe=true&height=350&width=600', true);
}
function do_delete()
{
    Util.MsgBox.Confirm({
				text: "你确定要删除选中的记录？",
				type: "warm",
				title:"系统提示",
				callback: function(t){
				    if( !t )  return ;
				    document.form1.even.value = 'delete';
				    document.form1.submit();
				}
	});
}
function up_sort()
{
    document.form1.even.value = 'up_sort';
    if( document.form1.ac ) {
        document.form1.ac.value = 'up_sort';
    }
    document.form1.submit();
}
</script>
</head>
<body>
<div id="contents">
<form name="formsearch" action="?ct=test_user&even=list" method="POST">
<dl class="search-class">
    <dd>
    关键字：
    <input type='text' name='keyword' style='width:200px;' class='text' value="<{if !empty($reqs.keyword)}><{$reqs.keyword}><{/if}>" />
    <button type='submit'>搜索</button>
    </dd>
</dl>
</form>

<form name="form1" action="?ct=test_user" method="POST">
<input type="hidden" name="even" value="delete" />
<table class="table-sort table-operate">
  <tr>
      <th> <label for='id[]'><input type='checkbox' name='id[]' id='id[]'  rel='parent' /> 选择</label> </th>
    <th>管理id</th>
    <th>用户名</th>
    <th>用户密码</th>
    <th>邮箱</th>
    <th>权限池</th>
    <th>权限组</th>
    <th>注册时间</th>
    <th>注册ip</th>
    <th>帐号状态</th>
    <th>最后登录时间</th>
    <th>最后登录IP</th>

  </tr>
  <{lurd_list item='v'}>
  <tr>
      <td><a href="javascript:show_data('<{$v.admin_id}>');"><img src='../../static/frame/admin/images/icons/text.gif' alt='修改' border='0' /></a> <input type='checkbox' rel='child' class='cbox' name='admin_id[]' value='<{$v.admin_id}>' /></td>
  <td> <{$v.admin_id}> </td>
  <td> <{$v.user_name}> </td>
  <td> <{$v.userpwd}> </td>
  <td> <{$v.email}> </td>
  <td> <{$v.pools}> </td>
  <td> <{$v.groups}> </td>
  <td> <{$v.regtime}> </td>
  <td> <{$v.regip}> </td>
  <td> <{$v.sta}> </td>
  <td> <{$v.logintime}> </td>
  <td> <{$v.loginip}> </td>

  </tr>
  <{/lurd_list}>
  <tr>
</table>
</form>
</div>

<div id="bottom">
    <div class="fl">
        <button type="button" onclick="add_data();">增加记录</button>
        <{if $has_sort}>&nbsp; <button type="button" onclick="up_sort();">更新排序</button><{/if}>
        &nbsp;
        <button type="button" onclick="do_delete();">删除选中记录</button>
    </div>
    <div class="pages">
        <{$lurd_pagination}>
    </div>
</div>

</body>
</html>
 