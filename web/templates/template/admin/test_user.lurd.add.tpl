<{include file="admin/header.tpl"}>
</head>
<body>
<div class="tboxform">
<form name="form1" action="?ct=test_user&even=saveadd" method="POST" enctype="multipart/form-data">
<table class="form">
    <tr>
  <td class='title'>用户名：</td>
  <td class='fitem'><input type='text' name='user_name' id='lurd_user_name' class='text' value='' /></td>
</tr>
<tr>
  <td class='title'>用户密码：</td>
  <td class='fitem'><input type='text' name='userpwd' id='lurd_userpwd' class='text' value='' /></td>
</tr>
<tr>
  <td class='title'>邮箱：</td>
  <td class='fitem'><input type='text' name='email' id='lurd_email' class='text' value='' /></td>
</tr>
<tr>
  <td class='title'>权限池：</td>
  <td class='fitem'><input type='text' name='pools' id='lurd_pools' class='text' value='' /></td>
</tr>
<tr>
  <td class='title'>权限组：</td>
  <td class='fitem'><input type='text' name='groups' id='lurd_groups' class='text' value='' /></td>
</tr>
<tr>
  <td class='title'>注册时间：</td>
  <td class='fitem'><input type='text' name='regtime' class='text s' value='' /></td>
</tr>
<tr>
  <td class='title'>注册ip：</td>
  <td class='fitem'><input type='text' name='regip' id='lurd_regip' class='text' value='' /></td>
</tr>
<tr>
  <td class='title'>帐号状态：</td>
  <td class='fitem'><input type='text' name='sta' class='text s' value='' /></td>
</tr>
<tr>
  <td class='title'>最后登录时间：</td>
  <td class='fitem'><input type='text' name='logintime' class='text s' value='' /></td>
</tr>
<tr>
  <td class='title'>最后登录IP：</td>
  <td class='fitem'><input type='text' name='loginip' id='lurd_loginip' class='text' value='' /></td>
</tr>

<tr>
  <td colspan='2' align='center' height='60' style='padding-left:60px;'>
      <button type="submit">保存</button> &nbsp;&nbsp;&nbsp;
      <button type="reset">重设</button>
  </td>
</tr>
</table>
</form>
</div>
</body>
</html>
