<{include file="admin/header.tpl"}>
</head>
<body class="frame-from">
<div class="tboxform">
<form name="form1" action="?ct=friendlink&even=saveedit" method="POST" enctype="multipart/form-data">
<table class="form">
<{lurd_list item='v'}>
<input type='hidden' name='id' value='<{$v.id}>' />
<tr>
  <td class='title' style='width:120px;'>网站名称：</td>
  <td class='fitem'>
    <input type='text' name='webname' id='lurd_webname' class='text' value='<{$v.webname}>' />
  </td>
</tr>
<tr>
  <td class='title'>类型：</td>
  <td class='fitem'>
    <select name='type'>
    <{#catalog_options cmid='2' selid=$v.type dfname='--请选择--' }>
    </select>
  </td>
</tr>
<tr>
  <td class='title'>网址：</td>
  <td class='fitem'>
    <input type='text' name='url' id='lurd_url' class='text' value='<{$v.url}>' style='width:300px;' />
  </td>
</tr>
<tr>
  <td class='title'>排序(值大在前)：</td>
  <td class='fitem'>
    <input type='text' name='sortrank' class='text s' value='<{$v.sortrank}>' />
  </td>
</tr>
<tr>
  <td class='title'>网站logo：</td>
  <td class='fitem'>
    <input type='text' name='logo' id='lurd_logo' class='text' value='<{$v.logo}>' />
    <script  language='javascript'>
                     function GetBoneDlgUpload_lurd_logo( reurl ) {
                        document.getElementById('lurd_logo').value = reurl;
                        if( document.getElementById('preimg_lurd_logo') ) {
                            document.getElementById('preimg_lurd_logo').src    = reurl;
                        }
                     }
   </script><input type='button' name='dlg_btn_1' value='浏览...' cls='dlg_btn' onclick='window.open("../share/fck/dialog/select_images.php?dlg_i=GetBoneDlgUpload_lurd_logo", "dlg_popUpImgWin", "scrollbars=yes,resizable=yes,statebar=no,width=600,height=400,left=100,top=100");' />
   <br /><img src='<{$v.logo}>' id='preimg_lurd_logo' height='80' style='margin-top:10px;' />
</td>
</tr>
<tr>
  <td class='title'>网站描述：</td>
  <td class='fitem'>
    <textarea name='description' id='lurd_description' class='text' style='width:300px;height:50px;'><{$v.description}></textarea>
  </td>
</tr>

<tr>
  <td class='title'>显示位置：</td>
  <td class='fitem'>
    <label><input type='radio' name='position' value='2' <{if $v.position==2}>checked<{/if}>> 主页</label>
    <label><input type='radio' name='position' value='1' <{if $v.position==1}>checked<{/if}>> 内页</label>
    <label><input type='radio' name='position' value='0' <{if $v.position==0}>checked<{/if}>> 不显示</label>
  </td>
</tr>
<{/lurd_list}>
<tr>
  <td colspan='2' align='center' height='60'>
      <button type="submit">保存</button> &nbsp;&nbsp;&nbsp;
      <button type="reset">重设</button>
  </td>
</tr>
</table>
</form>
</div>
</body>
</html>
