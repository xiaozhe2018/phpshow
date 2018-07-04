<{include file="admin/header.tpl"}>
<body class="frame-from">
<dl class="tips">
    <dd>
        <strong>当前位置：</strong>开发调试工具 &gt;&gt; <a href='?ct=debug&ac=lurd'>lurd控制类创建向导</a>
    </dd>
    <dd class='right'></dd>
</dl>
<{if !empty($success)}>
<table class="form">
    <tr>
        <td class='title' style='line-height:28px;height:28px'>
            成功生成指定的控制器和模型类：
        </td>
    </tr>
    <tr>
        <td class='fitem' style='padding-left:20px;line-height:36px;height:36px'>
            生成了如下文件：<br />
            <{$okfile}>
            <br />如果显示管理菜单，请刷新框架整体。
        </td>
    </tr>
</table>
<{else}>
<form name="form1" action="?" method="POST" jstype="vali" enctype="multipart/form-data">
<input type='hidden' name='ct' value='debug'>
<input type='hidden' name='ac' value='lurd'>
<table class="form">
    <tr>
        <td class='title' colspan='2' style='line-height:36px;height:36px'>
            选择的数据表必须含有主键或唯一索引键，才能使用这个工具去创建控制器和模型类
        </td>
    </tr>
    <tr>
        <td class='title' width='130'>控制器标识：<font color="red">*</font></td>
        <td class='fitem'><input type='text' name="class_name"  class='text error' value='' vali="notempty" errormsg="请填写控制器标识！" />
        <span class="info">对应 ctl_控制器标识.php，此文件必须为不存在</span>
        </td>
    </tr>
    <tr>
        <td class='title'>控制器名称：<font color="red">*</font></td>
        <td class='fitem'><input type='text' name="control_name"  class='text error' value='' vali="notempty" errormsg="请填写控制器名称！" />
        <span class="info">中文名称</span>
        </td>
    </tr>
    <tr>
        <td class='title'>创建默认菜单：</td>
        <td class='fitem'>
            <label><input type='radio' name='need_menu' value='0' > 不创建</label>
            <label><input type='radio' name='need_menu' value='1' checked> 创建</label>
        </td>
    </tr>
    <tr>
        <td class='title'>菜单显示名：</td>
        <td class='fitem'>
            <input type='text' name="app_name"  class='text error' value='' />
            <span class="info">在导航菜单显示的名称</span>
        </td>
    </tr>
    <tr>
        <td class='title'>模型主表：<font color="red">*</font></td>
        <td class='fitem'>
            <!--// mod_table -->
            <select name='mod_table'>
            <option name=''>--选择数据表--</option>
            <{foreach from=$tables key='_k' item='_v'}>
            <option name='<{$_k}>'><{$_k}></option>
            <{/foreach}>
            </select>
            <span class="info">控制器操作的主表</span>
        </td>
    </tr>
    <tr>
        <td class='title'>允许的操作：</td>
        <td class='fitem'>
            <label><input type='checkbox' checked='checked' disabled='disabled'> 列出数据</label>
            <{foreach from=$opts key='_k' item='_v'}>
            <label><input type='checkbox' name='selopts[<{$_k}>]' value='<{$_k}>' checked='checked'> <{$_v}></label>
            <{/foreach}>
        </td>
    </tr>
    <tr>
        <td class='title'>创建数据模型类：</td>
        <td class='fitem'>
            <label><input type='radio' name='front_mod' value='0' > 不创建</label>
            <label><input type='radio' name='front_mod' value='1' checked> 创建</label>
        </td>
    </tr>
    <tr>
        <td class='title'>前台模型类名：</td>
        <td class='fitem'>
            mod_<input type='text' name="front_mod_name"  class='text' value='' style='width:150px' />
            <span class="info">默认是：mod_控制器标识 </span>
        </td>
    </tr>
    <tr>
        <td colspan='2' align='center' class="single" height='60'>
            <button type="submit">保存</button> &nbsp;&nbsp;&nbsp;
            <button type="reset">重设</button>
        </td>
    </tr>
</table>
</form>
<{/if}>
</body>
</html>
