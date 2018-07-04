<{include file="admin/header.tpl"}>
<script language='javascript'>
    function checkpass()
    {
        if( document.form1.userpwd.value == document.form1.userpwdok.value)
        {
            document.getElementById('pwdtest').innerHTML = "";
            return true;
        }
        else
        {
            document.getElementById('pwdtest').innerHTML = "[两次输入密码效验不正确！]";
            return false;
        }
    }
    function done_purview( gurl )
    {
        parent.location.href = gurl;
        parent.ref_parent = false;
        parent.tb_remove();
    }
</script>
</head>
<body class="frame-from">
<div class="tboxform">
<form name="form1" action="?ct=users&ac=index&even=saveedit&tb=users" method="POST" onsubmit="return checkpass()" enctype="multipart/form-data">
<{lurd_list item='v'}>
<input type="hidden" name="admin_id" value="<{$v.admin_id}>" />
<table class="form">
<tr>
  <td class="title" width="100">用户名：</td>
  <td class="fitem">
    <{$v.user_name}>
  </td>
</tr>
<tr>
  <td class="title">用户密码：</td>
  <td class="fitem">
    <input type='input' name='userpwd' id='userpwd' class="text" value='' onchange='checkpass()' />
    <span>(必须大于6位)</span>
  </td>
</tr>
<tr>
  <td class="title">确认密码：</td>
  <td class="fitem">
    <input type='input' name='userpwdok' id='userpwdok' class="text" value='' onchange='checkpass()' />
    <span id='pwdtest' style='color:red'></span>
  </td>
</tr>
<tr>
  <td class="title">用户email：</td>
  <td class="fitem">
    <{$v.email}>
   </td>
</tr>
<tr>
  <td class="title">用户组：</td>
  <td class="fitem">
    <{foreach from=$pur_configs.pools.admin.private key=kk item=vv}>
             <input type='checkbox' name='groups[]' value='admin_<{$kk}>' <{if preg_match("/admin_". $kk ."/", $v.groups) }> checked='checked'<{/if}> /> <{$vv.name}>
    <{/foreach}>
    <hr size='1' />
    <a href='javascript:done_purview("?ct=users&ac=user_purview&admin_id=<{$v.admin_id}>");'>[为此用户设置独立权限]</a>
  </td>
</tr>
<tr>
  <td class="title">上次登录时间：</td>
  <td class="fitem">
    <{$last_login.logintime|date('Y-m-d H:i:s', @me)}>
   </td>
</tr>
<tr>
  <td class="title">上次登录IP：</td>
  <td class="fitem">
    <{$last_login.loginip}>
   </td>
</tr>
<tr>
  <td colspan='2' align='center' height='60' class='single'>
      <button type="submit">保存</button> &nbsp;&nbsp;&nbsp;
      <button type="reset">重设</button>
  </td>
</tr>
</table>
<{/lurd_list}>
</form>
</div>
</body>
</html>
