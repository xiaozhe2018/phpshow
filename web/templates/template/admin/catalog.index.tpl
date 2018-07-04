<{include file="admin/header.tpl"}>
<script lang='javascript'>
function add_data(nid)
{
    tb_show('增加分类', '?ct=catalog&cmid=<{$infos.cmid}>&even=add&TB_iframe=true&height=350&width=600', true);
}
function show_data(nid)
{
    tb_show('浏览/编辑分类', '?ct=catalog&cmid=<{$infos.cmid}>&even=edit&cid='+ nid +'&TB_iframe=true&height=350&width=600', true);
}
function del_one( cid )
{
    var ems = $('.cbox');
    for(i=0; i < ems.length; i++) {
        em = ems.get(i);
        em.checked = (em.value != cid ? false : true);
    }
    do_delete();
}
function do_delete()
{
    document.form1.even.value = 'delete';
    var msg = "你确定要删除选中的分类？！";
    msg += "<br/><a href='javascript:tb_remove2();'>&lt;&lt;点错了</a> &nbsp;|&nbsp; <a href='javascript:document.form1.submit();'>确定要删除&gt;&gt;</a>";
    tb_showmsg(msg);
}
function up_sort()
{
    document.form1.ac.value = 'catalog_sort';
    document.form1.submit();
}
$(function(){
    //展开/关闭子类
    $('.expand').click(function(){
        var cmid = document.form1.cmid.value;
        var cid  = $(this).attr('rel');
        var curimg = $(this);
        var emhtml = $('#son_'+cid).html();
        emhtml = $.trim( emhtml );
        if( emhtml != '' )
        {
            //关闭动作
            if( curimg.attr('src') != '../../static/frame/admin/images/m-expand.gif' )
            {
                $('#son_'+cid).hide();
                curimg.attr('src', '../../static/frame/admin/images/m-expand.gif');
            }
            //已有子类展开
            else {
                $('#son_'+cid).show();
                curimg.attr('src', '../../static/frame/admin/images/m-last.gif');
            }
            return;
        }
        //Ajax请求动作
        if( curimg.attr('src') != '../../static/frame/admin/images/m-close.gif' )
        {
            tb_offset = curimg.offset();
            tb_show_loading();
            $.get("?ct=catalog&ac=ajax_load_son&cmid="+cmid+"&cid="+cid, function(data){
                $('#son_'+cid).html( data );
                curimg.attr('src', '../../static/frame/admin/images/m-last.gif');
                tb_remove();
            });
        }
    });
}); 
</script>
</head>
<body class="frame-from">
<div id="contents">
<dl class="tips">
    <dd>
        <strong>当前位置：</strong><a href='?ct=catalog&ac=model'>分类模型管理</a> &gt;&gt; <{$infos.cur_model.cmname}>管理
    </dd>
    <dd class='right'></dd>
</dl>

<form name="form1" action="?" method="POST">
<input type="hidden" name="ct" value="catalog" />
<input type="hidden" name="ac" value="index" />
<input type="hidden" name="cmid" value="<{$infos.cmid}>" />
<input type="hidden" name="even" value="delete" />

<dl class='catalog_tree'>
<dt>
  <ul class='tree_top'>
    <li class='sel'><a href='javascript:select_checkbox("cbox");'><u>选择</u></a></li>
    <li class='name'>分类名称</li>
    <li class='sort'>排序值</li>
    <li class='opt'>操作</li>
  </ul>
</dt>
<dd>
<{foreach from=$cats key='_k'}>
  <ul class='tree_item'>
    <li class='sel'><input type='checkbox' class='cbox' name='cid[<{$_k}>]' value='<{$_k}>' rel="child" /></li>
    <li class='name'>
        <{if !empty($v.s)}>
        <img src='../../static/frame/admin/images/m-last.gif' width='11' height='11' class='expand' rel='<{$_k}>' />
        <{else}>
        <img src='../../static/frame/admin/images/m-cur.gif' width='11' height='11' />
        <{/if}>
        <{$v.d.cname}>[cid:<{$_k}>]
    </li>
    <li class='sort'>
        <input type='text' style='width:50px;' name='sortrank[<{$_k}>]' value='<{$v.d.sortrank}>' />
        <input type='hidden' name='sort_old[<{$_k}>]' value='<{$v.d.sortrank}>' />
    </li>
    <li class='opt'>
        <a href="javascript:show_data('<{$_k}>');"><img src='../../static/frame/admin/images/icons/write.gif' alt='修改' width='14' height='14' border='0' /></a>
        <a href="javascript:del_one('<{$_k}>');"><img src='../../static/frame/admin/images/icons/gtk-del.png' alt='删除' width='16' height='16' border='0' /></a>
    </li>
  </ul>
<!--//下级分类-->
<{if !empty($v.s)}>
<div class='son' id='son_<{$_k}>'>
<{foreach from=$v.s key='_kk' item='vv'}>
<ul class='tree_item'>
    <li class='sel'><input type='checkbox' class='cbox' name='cid[<{$_kk}>]' value='<{$_kk}>' /></li>
    <li class='step1'>
        <{if !empty($vv.s)}>
        <img src='../../static/frame/admin/images/m-expand.gif' width='11' height='11' class='expand' rel='<{$_kk}>' />
        <{else}>
        <img src='../../static/frame/admin/images/m-cur.gif' width='11' height='11' />
        <{/if}>
        <{$vv.d.cname}>[cid:<{$_kk}>]
    </li>
    <li class='sort'>
        <input type='text' style='width:50px;' name='sortrank[<{$_kk}>]' value='<{$vv.d.sortrank}>' />
        <input type='hidden' name='sort_old[<{$_kk}>]' value='<{$vv.d.sortrank}>' />
    </li>
    <li class='opt'>
        <a href="javascript:show_data('<{$_kk}>');"><img src='../../static/frame/admin/images/icons/write.gif' alt='修改' width='14' height='14' border='0' /></a>
        <a href="javascript:del_one('<{$_kk}>');"><img src='../../static/frame/admin/images/icons/gtk-del.png' alt='删除' width='16' height='16' border='0' /></a>
    </li>
</ul>
<{if !empty($vv.s)}><div class='son' id='son_<{$_kk}>'></div><{/if}>
<{/foreach}>
</div>
<{/if}>
<{/foreach}>
</dd>
</dl>
</form>
</div>

<div id="bottom">
    <div class="fl">
        <button type="button" onclick="add_data();">增加分类</button>
        &nbsp;
        <button type="button" onclick="up_sort();">更新排序</button>
        &nbsp;
        <button type="button" onclick="do_delete();">删除选中分类</button>
    </div>
    <div class="pages"></div>
</div>

</body>
</html>
 