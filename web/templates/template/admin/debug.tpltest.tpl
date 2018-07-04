<{include file="admin/header.tpl"}>
<body class="frame-from">
<dl class="tips">
    <dd>
        <strong>当前位置：</strong>开发调试工具 &gt;&gt; 模板标签测试
    </dd>
    <dd class='right'></dd>
</dl>

<form name="form1" action="?" target="stafrm" method="post">
<input type="hidden" name="ct" value="debug" />
<input type="hidden" name="ac" value="tpltest" />
<table class="form">
    <tr> 
      <td class="title" width="150">
      调试说明：
      </td>
      <td class="fitem">
      可以输入 function、block、modifier 类标签进行测试，如果foreach的源数据是静态类的方法，也可以在此测试
      </td>
    </tr>
    <tr> 
      <td class="title"><strong>输入要测试的局部代码：</strong> </td>
      <td class="fitem">
        <input type="submit" name="Submit" value="提交测试" class="coolbg np" /> 
       </td>
    </tr>
    <tr> 
      <td class="fitem fix_left" colspan="2">
	  <textarea name="code" id="code" style="width:90%;height:200px"></textarea>
	  </td>
    </tr>
  <tr bgcolor="#F9FCEF"> 
    <td class="title">
    	返回结果：
    </td>
    <td class='fitem'></td>
  </tr>
  <tr> 
    <td colspan="2" class="fix_left" style='height:300px;'>
        <iframe name="stafrm" frameborder="0" id="stafrm" width="90%" height="100%"></iframe>
    </td>
  </tr>
</table>
</form>

</body>
</html>
