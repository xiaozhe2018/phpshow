<{include file="admin/header.tpl"}>
</head>
<body class="frame-from">
<div class="tboxform">
<table class="form">
<{lurd_list item='v'}>
<tr>
  <td class='title'>记录ID：</td>
  <td class='fitem'> <{$v.id}> </td>
</tr>
<tr>
  <td class='title'>用户名：</td>
  <td class='fitem'> <{$v.user_name}> </td>
</tr>
<tr>
  <td class='title'>消息内容:</td>
  <td class='fitem'> <{$v.msg}> </td>
</tr>
<tr>
  <td class='title'>发生时间:</td>
  <td class='fitem'> <{$v.do_time|date('Y-m-d H:i:s', @me)}> </td>
</tr>
<tr>
  <td class='title'>客户端IP：</td>
  <td class='fitem'> <{$v.do_ip}> </td>
</tr>
<tr>
  <td class='title'>操作网址：</td>
  <td class='fitem'> <{$v.do_url}> </td>
</tr>
<{/lurd_list}>
</table>
</div>
</body>
</html>
