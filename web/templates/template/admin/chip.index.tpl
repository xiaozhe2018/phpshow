<{include file="admin/header.tpl"}>
<script lang='javascript'>
function show_data(nid)
{
    tb_show('浏览/编辑记录', '?ct=chip&even=edit&tb=bone_chip&id='+ nid +'&TB_iframe=true&height=450&width=700', true);
}
function do_delete()
{
    document.form1.even.value = 'delete';
    var msg = "你确定要删除选中的记录？！";
    msg += "<br/><a href='javascript:tb_remove();'>&lt;&lt;点错了</a> &nbsp;|&nbsp; <a href='javascript:document.form1.submit();'>确定要删除&gt;&gt;</a>";
    tb_showmsg(msg);
}
</script>
</head>
<body class="frame-from">
<div id="contents">
<form name="formsearch" action="?ct=chip&even=list" method="POST">
    <input type='hidden' name='tb' value='bone_chip' />
    <input type='hidden' name='orderby' value='' />
    <dl class="search-class">
    <dd>
    关键字：
    <input type='text' name='keyword' style='width:200px;' class='text' value="" />
    <button type='submit'>搜索</button>
    </dd>
    </dl>
</form>

<form name="form1" action="?ct=chip" method="POST">
<input type='hidden' name='tb' value='bone_chip' />
<input type="hidden" name="even" value="delete" />
<table class="table-sort table-operate">
    <tr>
        <th width="60"> <label for='id[]'><input type='checkbox' name='id[]' id='id[]'  rel="parent" /> 全选</label></th>
        <th><strong>ID</strong></th>
        <th><strong>排序</strong></th>
        <th><strong>名称</strong></th>
        <th><strong>调用代码</strong></th>
        <th><strong>描述</strong></th>
        <th><strong>操作</strong></th>
    </tr>
  <{lurd_list item='v'}>
    <tr>
        <td> <input type='checkbox' name='id[]' id="<{$v.id}>" rel='child' value='<{$v.id}>' /> </td>
        <td> <label for="<{$v.id}>"><{$v.id}></label> </td>
        <td>
            <input type="text" class="text s" style="width:30px;" name="sortrank[<{$v.id}>]" value="<{$v.sortrank}>">
            <input type="hidden" name="sortrank_old[<{$v.id}>]" value="<{$v.sortrank}>">
        </td>
        <td> <a href="?ct=chip&ac=edit&id=<{$v.id}>" > <{$v.name}> </a> </td>
        <td> <input type="text" class="text" value="&lt;{#chip name=<{$v.name}>}&gt;" style="width:250px;" /> </td>
        <td> <{$v.description}> </td>
        <td> 
            <a href="javascript:;" onclick="tb_show('预览<{$v.name}>标签', '?ct=chip&ac=preview&name=<{$v.name}>&TB_iframe=true&height=250&width=600', true)">预览</a> | <a href="?ct=chip&ac=edit&id=<{$v.id}>">修改</a>
        </td>
    </tr>
  <{/lurd_list}>
</table>
</form>
</div>

<div id="bottom">
    <div class="fl">
        <button type="button" onclick="location='?ct=chip&ac=add';">增加记录</button>
        &nbsp;
        <button type="button" onclick="do_delete();">删除选中记录</button>
    </div>
    <div class="pages">
        <{$lurd_pagination}>
    </div>
</div>

</body>
</html>
 