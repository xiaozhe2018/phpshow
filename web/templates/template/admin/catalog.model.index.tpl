<{include file="admin/header.tpl"}>
<script lang='javascript'>
function show_data(nid)
{
    tb_show('浏览/编辑记录', '?ct=catalog&ac=model&even=edit&cmid='+ nid +'&TB_iframe=true&height=330&width=550', true);
}
function add_data()
{
    tb_show('增加记录', '?ct=catalog&ac=model&even=add&TB_iframe=true&height=360&width=550', true)
}
function do_delete()
{
    document.form1.even.value = 'delete';
    document.form1.ac.value = 'model';
    var msg = "你确定要删除选中的记录？！";
    msg += "<br/><a href='javascript:tb_remove();'>&lt;&lt;点错了</a> &nbsp;|&nbsp; <a href='javascript:document.form1.submit();'>确定要删除&gt;&gt;</a>";
    tb_showmsg(msg);
}
function up_sort()
{
    document.form1.ac.value = 'model_sort';
    document.form1.submit();
}
</script>
</head>
<body class="frame-from">
<div id="contents">
<dl class="tips-list">
    <dd>
        <strong>当前位置：</strong>分类模型管理
    </dd>
    <dd class='right'></dd>
</dl>

<form name="form1" action="?ct=catalog" method="POST">
<input type='hidden' name='ac' value='model' />
<input type="hidden" name="even" value="delete" />
<table class="table-sort table-operate">
  <tr>
    <th class="thc" width='50'> <label><input type="checkbox" rel="parent"/> 选择</label> </th>
    <th class="thc" width='50'><strong>cmid</strong></th>
    <th class="thc" width='150'><strong>模型名</strong></th>
    <th class="thc" width='150'><strong>分类数据表</strong></th>
    <th class="thc" width='50'><strong>排序</strong></th>
    <th class="thc" width='80'><strong>快捷菜单</strong></th>
    <th class="thc" width='150'><strong>操作</strong></th>
    <th></th>
  </tr>
  <{lurd_list item='v'}>
  <tr align='center'>
  <td>
    <a href="javascript:show_data('<{$v.cmid}>');"><img src='../../static/frame/admin/images/icons/text.gif' alt='修改' title='修改' border='0' /></a> 
    <input type='checkbox' name='cmid[]' class='cbox' value='<{$v.cmid}>' rel="child" />
  </td>
  <td> <{$v.cmid}> </td>
  <td> <a href="<{if $v.adm_url !=''}><{$v.adm_url}><{else}>?ct=catalog&cmid=<{$v.cmid}><{/if}>"><{$v.cmname}></a> </td>
  <td> <{$v.cmtable}> </td>
  <td>
    <input type='text' name='sortranks[<{$v.cmid}>]' value='<{$v.sortrank}>' style='width:50px;' />
    <input type='hidden' name='old_sortranks[<{$v.cmid}>]' value='<{$v.sortrank}>' />
  </td>
  <td> <{if $v.showmenu==1}>出现<{else}>不出现<{/if}> </td>
  <td> <a href="<{if $v.adm_url !=''}><{$v.adm_url}><{else}>?ct=catalog&cmid=<{$v.cmid}><{/if}>">[管理分类]</a> </td>
  <td></td>
  </tr>
  <{/lurd_list}>
  <tr>
</table>
</form>
</div>

<div id="bottom">
    <div class="fl">
        <button type="button" onclick="add_data()">增加记录</button>
        &nbsp; 
        <button type="button" onclick="up_sort()">更新排序</button>
        &nbsp; 
        <button type="button" onclick="do_delete();">删除选中记录</button>
    </div>
    <div class="pages">
        <{$lurd_pagination}>
    </div>
</div>

</body>
</html>
 