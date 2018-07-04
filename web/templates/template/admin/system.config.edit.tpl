<{include file='admin/header.tpl'}>
<body class="frame-from">
<div class="tboxform">
    <form name="form1" jstype="vali" action="?ct=system&ac=config_edit" method="POST" enctype="multipart/form-data" jstype="vali">
        <input type='hidden' name='data[name]' value='<{$data.name}>'>
        <table class="form">
            <tr>
                <td class='title' width='90'>变量名称：</td>
                <td class='fitem'>
                    <{$data.name}>
                 </td>
            </tr>
            <tr>
                <td class='title'>变量分类：</td>
                <td class='fitem'>
                <select name='data[group_id]'>
                    <{#catalog_options cmid='1' selid=$data.group_id dfname='请选择分类'}>
                </select>
                </td>
            </tr>
            <tr>
                <td class='title'>说明标题：</td>
                <td class='fitem'>
                    <input type='input' name='data[title]' class="text error" style='width:250px;' value='<{$data.title}>' vali="notempty" errormsg="说明标题不能为空！"/>
                 </td>
            </tr>
            <tr>
                <td class='title'>说明备注：</td>
                <td class='fitem'>
                    <input type='input' name='data[info]' class="text" style='width:250px;' value='<{$data.info}>' />
                 </td>
            </tr>
            <tr>
                <td class='title'>变量值：</td>
                <td class='fitem'>
                    <{if $data.type=='bool'}>
                       <label><input type="radio" value="1" name="data[value]" <{if $data.value==1}>checked<{/if}>> 是</label>
                       <label><input type="radio" value="0" name="data[value]" <{if $data.value==0}>checked<{/if}>> 否</label>
                    <{elseif $data.type=='text'}>
                       <textarea name='data[value]' style='width:250px;height:50px;'><{$data.value}></textarea>
                    <{else}>
                        <input type="text" value="<{$data.value}>" name="data[value]" class="text" <{if $data.type=='number'}>style="width:80px"<{else}>style="width:250px
                       "<{/if}> />
                     <{/if}>
                 </td>
            </tr>
            
            <tr>
                <td class='title'>变量类型：</td>
                <td class='fitem'>
                <label><input type='radio' name='data[type]' value='string' <{if $data.type=='string'}>checked<{/if}>> 字符串</label>
                <label><input type='radio' name='data[type]' value='number' <{if $data.type=='number'}>checked<{/if}>> 数字</label>
                <label><input type='radio' name='data[type]' value='text' <{if $data.type=='text'}>checked<{/if}>> 多行文本</label>
                <label><input type='radio' name='data[type]' value='bool' <{if $data.type=='bool'}>checked<{/if}>> Bool(布尔变量)</label>
                </td>
            </tr>
            <tr>
                <td class='title'>排序id：</th>
                <td class='fitem'>
                    <input type='input' name='data[sort_id]' class="text s" value='<{$data.sort_id}>' />
                    <span class='info'>大的靠前</span>
                 </td>
            </tr>
            <tr>
                <td colspan="2" style="padding-left:60px;" height='48'>
                    <button type="submit">保存</button> &nbsp;&nbsp;&nbsp;
                    <button type="reset">重设</button>
                </td>
            </tr>
        </table>
    </form>
</div>
</body>
</html>
