<{include file="admin/header.tpl"}>
<script lang='javascript'>
function add_data(nid)
{
    tb_show('增加记录', '~lurdurl~&even=add&tb=~tablename~&TB_iframe=true&height=350&width=600', true);
}
function show_data(nid)
{
    tb_show('浏览/编辑记录', '~lurdurl~&even=edit&~primarykey~='+ nid +'&TB_iframe=true&height=350&width=600', true);
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
<form name="formsearch" action="~lurdurl~&even=list" method="POST">
<dl class="search-class">
    <dd>
    关键字：
    <input type='text' name='keyword' style='width:200px;' class='text' value="<{if !empty($reqs.keyword)}><{$reqs.keyword}><{/if}>" />
    <button type='submit'>搜索</button>
    </dd>
</dl>
</form>

<form name="form1" action="~lurdurl~" method="POST">
<input type="hidden" name="even" value="delete" />
<table class="table-sort table-operate">
  <tr>
    ~listtitle~
  </tr>
  <{lurd_list item='v'}>
  <tr>
    ~listitem~
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
 