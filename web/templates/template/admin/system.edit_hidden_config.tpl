<{include file="admin/header.tpl"}>
</head>
<body class="frame-from">
<dl class="tips">
    <dd>
     <strong>当前位置：</strong> <a href='?ct=system&ac=config_list'>系统配置修改</a> &gt;&gt; <{$dotitle}>
    </dd>
</dl>

<form id="form1" action="?" method="POST" enctype="multipart/form-data">
<input type='hidden' name='ct' value='system'>
<input type='hidden' name='ac' value='<{$c_ac}>'>
<table class="form">
    <tr>
        <td class='title'><{$info}></td>
    </tr>
    <tr>
        <td style='padding-left:20px'>
            <textarea name="new_value" class="text" style="width:90%;height:<{$area_height}>px;"><{$value}></textarea>
        </td>
    </tr>
</table>

<div id="bottom" style="padding-left:20px;">
    <button type="submit">确定保存</button>
</div>

</form>

</body>
</html>
