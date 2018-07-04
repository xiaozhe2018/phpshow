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
</script>
</head>
<body class="frame-from">
<dl class="tips">
    <dd>
     <strong>当前位置：</strong><a href='?ct=users'>用户管理</a> -- 修改登录密码
    </dd>
</dl>
<form name="form1" action="?ct=users&ac=editpwd&even=saveedit" method="POST" onsubmit="return checkpass()" enctype="multipart/form-data">
<{lurd_list item='v'}>
<table class="form">
<tr>
  <td class="title" width="150">用户名：</td>
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
  <td class="title">用户组：</d>
  <td class="fitem">
    <{#groupname group=$v.groups}>
  </td>
</tr>
<tr>
  <td class="title">上次登录时间：</td>
  <td class="fitem">
    <{$last_login.logintime|date("Y-m-d H:i:s", @me)}>
   </td>
</tr>
<tr>
  <td class="title">上次登录IP：</td>
  <td class="fitem">
    <{$last_login.loginip}>
   </td>
</tr>
<tr>
  <td colspan='2' align='center' height='60'>
      &nbsp;&nbsp;&nbsp;<button type="submit">保存</button> 
  </td>
</tr>
</table>
<{/lurd_list}>
</form>

</body>
</html>
