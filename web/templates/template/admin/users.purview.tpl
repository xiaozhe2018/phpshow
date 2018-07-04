<{include file="admin/header.tpl"}>
</head>
<body class="frame-from">
<dl class="tips">
    <dd>
     <strong>当前位置：</strong><a href='?ct=users&ac=edit_purview_groups'>权限管理</a> -- 用户：<{$users.user_name}> (<{#frame_union do='groups' var=$users.groups}>) 
    </dd>
</dl>

<div id="rightmain" style="width:98%;margin:auto;padding:auto;padding-top:16px">
<form name="form1" action="?ct=users&ac=user_purview&admin_id=<{$admin_id}>&even=saveedit&gp=<{$gp}>" method="POST" enctype="multipart/form-data">
<div class="formBox m10">

<{foreach from=$config_apps key=k item=v}>
<div style='padding-left:10px;'>
    <div class='title' style='line-height:28px;border-bottom:1px dashed #ccc;font-weight:bold;'><{$v.app_name}></div>
    <div style='line-height:28px;width:90%;padding-top:10px;'>
       <{foreach from=$v key=kk item=vv name=foo}>
       <{if $kk != 'app_name'  }>
       <{if $_block.foo.index > 1 && ($_block.foo.index-1) / 5 == 0 }><br /><{/if}>
       <span style="display:-moz-inline-box; display:inline-block; width:200px;"><input type='checkbox' name='groups[]' value='<{$k}>-<{$kk}>'<{if $groups=='*' || (in_array($kk, $groups.$k) )  }> checked='checked'<{/if}>/> <{$vv}>
       </span>
       <{/if}>
       <{/foreach}>
    </div>
</div>
<br />
<{/foreach}>

<div class="submit">
    <br />
    <button class="confirm" type="submit" style="margin-left:100px;padding-left:5px;">确定</button>
    <button class="confirm" type="reset" style="margin-left:60px;padding-left:5px;">重设</button>
</div>

</form>
</div>

</body>
</html>
