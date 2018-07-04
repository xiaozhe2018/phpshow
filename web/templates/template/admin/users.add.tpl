<{include file='admin/header.tpl'}>
<script language='javascript'>
    function checkpass()
    {
        if( document.form1.userpwd.value.length < 6 )
        {
            document.getElementById('pwdtest').innerHTML = "<font color='red'>[密码必须大于6位！]</font>";
            return false;
        }
        else
        {
            document.getElementById('pwdtest').innerHTML = "";
            return true;
        }
    }
</script>
</head>
<body class="frame-from">
<div clss="tboxform">
<form name="form1" jstype="vali" action="?ct=users&ac=index&even=saveadd&tb=users" method="POST" onsubmit="return checkpass()" enctype="multipart/form-data">
<table class="form">
<tr>
  <td class="title">用户名：</td>
  <td class="fitem"><input type='input' name='user_name' class="text" value='' /></td>
</tr>
<tr>
  <td class="title">用户密码：</td>
  <td class="fitem">
    <input type='input' name='userpwd' id='userpwd' class="text" value='' />
    <span id='pwdtest'>(必须大于6位)</span>
  </td>
</tr>
<tr>
  <td class="title">用户email：</td>
  <td class="fitem">
    <input type='input' name='email' class="text" value='' />
   </td>
</tr>
<tr>
  <td class="title">用户组：</td>
  <td class="fitem">
    <{foreach from=$pur_configs.pools.admin.private key=k item=v}>
        <input type='checkbox' name='groups[]' value='admin_<{$k}>' /> <{$v.name}>
    <{/foreach}>
  </td>
</tr>
<tr>
  <td colspan='2' align='center' height='60' class='single'>
      <button type="submit">保存</button> &nbsp;&nbsp;&nbsp;
      <button type="reset">重设</button>
  </td>
</tr>
</table>
</form>
</div>

</body>
</html>
