<{include file='admin/header.tpl'}>
<body class="frame-from">
<div class="tboxform">
    <form name="form1" jstype="vali" action="?ct=system&ac=config_add" method="POST" enctype="multipart/form-data" jstype="vali">
        <table class="form">
            <tr>
                <td class='title' width='90'>变量名称：</td>
                <td class='fitem'>
                    <input type='input' name='data[name]' class="text error" style='width:250px;' value='' vali="notempty" errormsg="变量名称不能为空！"/>
                    <br /><span class='info'>必须由英文数字或下划线组成</span>
                 </td>
            </tr>
            <tr>
                <td class='title'>变量分类：</td>
                <td class='fitem'>
                <select name='data[group_id]'>
                    <{#catalog_options cmid='1' selid=1 dfname='系统隐藏变量'}>
                </select>
                </td>
            </tr>
            <tr>
                <td class='title'>说明标题：</td>
                <td class='fitem'>
                    <input type='input' name='data[title]' class="text error" style='width:250px;' value='' vali="notempty" errormsg="说明标题不能为空！"/>
                 </td>
            </tr>
            <tr>
                <td class='title'>说明备注：</td>
                <td class='fitem'>
                    <input type='input' name='data[info]' class="text" style='width:250px;' value='' />
                 </td>
            </tr>
            <tr>
                <td class='title'>变量值：</td>
                <td class='fitem'>
                    <textarea type='input' name='data[value]' style='width:250px;height:50px;'/></textarea>
                 </td>
            </tr>
            
            <tr>
                <td class='title'>变量类型：</td>
                <td class='fitem'>
                <label><input type='radio' name='data[type]' value='string' checked> 字符串</label>
                <label><input type='radio' name='data[type]' value='number'> 数字</label>
                <label><input type='radio' name='data[type]' value='text'> 多行文本</label>
                <label><input type='radio' name='data[type]' value='bool'> Bool(布尔变量)</label>
                </td>
            </tr>
            <tr>
                <td class='title'>排序id：</th>
                <td class='fitem'>
                    <input type='input' name='data[sort_id]' class="text s" value='0' />
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
