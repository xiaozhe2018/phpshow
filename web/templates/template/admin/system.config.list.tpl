<{include file="admin/header.tpl"}>
<body class="frame-from">
<script lang='javascript'>
function add_data( )
{
    tb_show('增加系统变量', '?ct=system&ac=config_add&TB_iframe=true&height=400&width=500', true);
}
function edit_data( vname )
{
    tb_show('修改系统变量', '?ct=system&ac=config_edit&name='+vname+'&TB_iframe=true&height=350&width=500', true);
}
</script>
<style>
input.inpw {
    width:96%;
}
.inpwh {
    width:96%;
    height:50px;
}
input.inps {
    width:40px;
}
td.v {
    padding-right:10px;
}
</style>
<dl class="tips">
    <dd>
     <strong>当前位置：</strong> &gt;&gt; 系统配置修改
     <span class='info'>(系统配置变量，在模板中用$config.varname调用，在程序中则应该用config::get(varname)调用)</span>
     &nbsp; <button type="button" onclick="add_data()">增加新变量</button>
    </dd>
</dl>
<div class="tboxform" style="width:100%">
    <dl class="search-class" style="padding-left:0px;padding-right:0px;border-bottom:1px dashed #ddd">
        <dd>
            <ul id="li-type">
                <{foreach from=$config_types name='config_type' key='_k' item='_v'}>
                <li rel="<{$_k}>" <{if $_k==$selitem}>class="focus"<{/if}>><a  href="javascript:;" rel="<{$_k}>" <{if $_k==$selitem}>class="focus"<{/if}>><{$_v.cname}></a></li>
                <{/foreach}>
            </ul>
        </dd>
    </dl>
    <form name="form1" action="?ct=system&ac=config_list" method="POST" jstype="vali">
        <input type='hidden' name='selitem' id='selitem' value='<{$selitem}>'>
        <table class="form" id="tab_table" style="margin-top:0px;">
                <{foreach from=$config_types name='config_type' key='_k' item='_v'}>
                <tbody <{if $_k != $selitem}>style="display:none;"<{/if}>>
                    <tr>
                        <td class='title v' align="center" width="180">变量说明</td>
                        <td class='fitem' width='400'><strong>变量值</strong></td>
                        <td class='fitem' width="60"><strong>排序</strong></td>
                        <td class='fitem'><strong>调用代码</strong></td>
                        <td class='fitem'></td>
                    </tr>
                <{foreach from=config::get_all($_k) key='_kk' item='_vv'}>
                    <tr align="center">
                        <td class='title v'>
                            <img src='../../static/frame/admin/images/icons/text.gif'  style='cursor:pointer' onclick='edit_data("<{$_vv.name}>")' alt='修改' width='14' height='14' />
                            <{$_vv.title}>：
                            <{if $_vv.info !=''}><br><span class='info'>(<{$_vv.info}>)</span><{/if}>
                        </td>
                        <td class='fitem v'>
                          <{if $_vv.type=='bool'}>
                            <label><input type="radio" value="1" name="datas[<{$_vv.name}>]" <{if $_vv.value==1}>checked<{/if}>> 是</label>
                            <label><input type="radio" value="0" name="datas[<{$_vv.name}>]" <{if $_vv.value==0}>checked<{/if}>> 否</label>
                          <{elseif $_vv.type=='text'}>
                            <textarea name='datas[<{$_vv.name}>]' class='inpwh'><{$_vv.value}></textarea>
                          <{else}>
                            <input type="text" value="<{$_vv.value}>" name="datas[<{$_vv.name}>]" class="text <{if $_vv.type=='number'}>s<{else}>inpw<{/if}>" />
                          <{/if}>
                         </td>
                         <td class='fitem'>
                            <input type='text' name='sorts[<{$_vv.name}>]' value='<{$_vv.sort_id}>' class='text inps'>
                        </td>
                         <td class='fitem'>
                            <span class="text-hint normal">&lt;{$config.<{$_kk}>}&gt;</span>
                         </td>
                         <td></td>
                    </tr>
                <{/foreach}>
                </tbody>
            <{/foreach}>
        </table>
        <table width="70%" height='60'>
            <tr>
                <td align='center'>
                    <button type="submit">保存</button> &nbsp; &nbsp;
                    <button type="reset">重设</button> &nbsp; &nbsp;
                    <button type="button" onclick="add_data()">增加新变量</button>
                </td>
            </tr>
        </table>
    </form>
</div>
<script language="javascript">
    $(function(){
        $('#li-type').find('li').each(function(i){
            $(this).click(function(){
                $('#li-type').find('li').removeClass('focus');
                $(this).addClass('focus');
                $('#tab_table').find('tbody').hide();
                $('#tab_table').find('tbody').eq(i).show();
                $('#selitem').val( $(this).attr('rel') );
            });
            
        });
    });
</script>
</body>
</html>
