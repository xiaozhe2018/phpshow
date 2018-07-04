<{include file="admin/header.tpl"}>
<script lang='javascript'>
function add_data(nid)
{
    tb_show('增加友情链接', '?ct=friendlink&even=add&tb=bone_friendlinks&TB_iframe=true&height=450&width=600', true);
}
function show_data(nid)
{
    tb_show('浏览/编辑友情链接', '?ct=friendlink&even=edit&id='+ nid +'&TB_iframe=true&height=450&width=600', true);
}
function do_delete()
{
    document.form1.even.value = 'delete';
    var msg = "你确定要删除选中的网站？";
    msg += "<br/><a href='javascript:tb_remove2();'>&lt;&lt;点错了</a> &nbsp;|&nbsp; <a href='javascript:document.form1.submit();'>确定要删除&gt;&gt;</a>";
    tb_showmsg(msg);
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
<body class="frame-from">
<div id="contents">
<form name="formsearch" action="?" method="GET">
<input type='hidden' name='ct' value= 'friendlink'>
<dl class="search-class">
    <dd>
    关键字：
    <input type='text' name='keyword' style='width:200px;' class='text' value="<{if !empty($reqs.keyword)}><{$reqs.keyword}><{/if}>" />
    <select name='type'>
    <{#catalog_options cmid='2' selid=$reqs.type dfname='--类型--' }>
    </select>
    <button type='submit'>搜索</button>
    </dd>
</dl>
</form>

<form name="form1" action="?ct=friendlink" method="POST">
<input type="hidden" name="even" value="delete" />
<table class="table-sort table-operate">
  <tr>
    <th style='width:60px'> <label for='id[]'><input type='checkbox' name='id[]' id='id[]'  rel='parent' /> 选择</label> </th>
    <th style='width:50px'>id</th>
    <th style='width:100px'>网站logo</th>
    <th style='width:150px'>网站名称</th>
    <th style='width:100px'>排序(值大在前)</th>
    <th style='width:100px'>网站描述</th>
    <th style='width:100px'>类型</th>
    <th style='width:100px'>显示位置</th>
    <th></th>
  </tr>
  <{lurd_list item='v'}>
  <tr>
  <td><a href="javascript:show_data('<{$v.id}>');"><img src='../../static/frame/admin/images/icons/text.gif' alt='修改' border='0' /></a> <input type='checkbox' rel='child' class='cbox' name='id[]' value='<{$v.id}>' /></td>
  <td> <{$v.id}> </td>
  <td> <{if !empty($v.logo)}><img src='<{$v.logo}>' height='60'><{/if}> </td>
  <td> <a href='<{$v.url}>' target='_blank'><{$v.webname}></a> </td>
  <td>
    <input type='text' name='sortrank[<{$v.id}>]' value='<{$v.sortrank}>' style='width:50px;' />
    <input type='hidden' name='old_sortrank[<{$v.id}>]' value='<{$v.sortrank}>' />
  </td>
  <td> <{$v.description}> </td>
  <td> <{$v.type.typeid|mod_catalog::get_name(2, @me)}> </td>
  <td> <{if $v.position==2}>首页<{elseif $v.position==1}>内页<{else}>不显示<{/if}> </td>
  <td> </td>
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
 