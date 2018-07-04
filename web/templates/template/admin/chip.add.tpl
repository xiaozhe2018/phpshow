<{include file="admin/header.tpl"}>
<body class="frame-from">
<script src="../../static/frame/admin/js/extend.js" type="text/javascript"></script>
<dl class="tips">
    <dd>
        <strong>当前位置：</strong><a href="?ct=chip">chip碎片管理</a> &gt;&gt; 增加新chip标签
    </dd>
    <dd class='right'></dd>
</dl>

<form name="form1" action="?" method="POST"  jstype="vali" enctype="multipart/form-data">
<input type='hidden' name='ct' value='chip'>
<input type='hidden' name='ac' value='add'>
<table class="form">
    <tr>
        <td class='title' width='100'>名称：<font color="red">*</font></td>
        <td class='fitem'><input type='text' name="name"  class='text error' value='' vali="notempty" errormsg="请填写chip名称！" />
        <span class="info">名称必须唯一。</span>
        </td>
    </tr>
    <tr>
        <td class='title'>描述：<font color="red">*</font></td>
        <td class='fitem'><input type='text' name="description"  class='text error l' value='' vali="notempty" errormsg="请填chip描述！" />
        <span class="info">填写相关描述。</span>
        </td>
    </tr>
    <tr>
        <td class='title'>排序：</td>
        <td class='fitem'><input type='text' name="sortrank"  class='text error s' value='99' vali="notempty" errormsg="请填chip排序！" />
        <span class="info">越大越靠前。</span>
        </td>
    </tr>
    <tr>
        <td class='title' height='28'>数据格式：</td>
        <td class='fitem'>
            <input type='hidden' name='is_array' id='is_array' value='0' />
            <button type='button' name='sel0' id='btnsel0' onclick="switch_type(0)" disabled=disabled>HTML代码块</button>
            &nbsp;
            <button type='button' name='sel1' id='btnsel1' onclick="switch_type(1)">数据列表</button>
        </td>
    </tr>
    <tr class="data0">
        <td colspan="2" class="single">
        <{#editor toolbar='Default' fieldname='content' height='350'}>
        </td>
    </tr>
    <tr class="data1" style="display:none">
        <td class='title'>列表数据：</td>
        <td class='fitem'>
            <button type="button" onclick="javascript:additem();" >增加数据项</button>
            &nbsp; &nbsp;
            <button type="submit">保存数据</button>
        </td>
    </tr>
    <tr>
        <td colspan="2" class="data1 single" style="display:none">
            <table class="table-sort table-operate" id="items">
                    <tr>
                        <td>
                        链接：<input type="text" name="url[]" value=""> 
                        标题：<input type="text" name="title[]" value="" > 
                        缩略图：<input type="file" name="thumb[]" size="20">
                        描述： <textarea name="seo_description[]" style="width:150px;height:30px;"></textarea>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr class="data1" style="display:none;border-top:1px dashed #ddd">
        <td class='title'>列表模板：</td>
        <td class='fitem'>
                <button type="button" onclick="javascript:set_template('list1')">默认列表模板（不带缩略图）</button>
                <button type="button" onclick="javascript:set_template('list2')">列表模板（带缩略图）</button>
                <button type="button" onclick="javascript:set_template('list3')">自定义列表模板</button>
                <div style="height:10px;"></div>
                <textarea name="template" style="width:80%" id="template"> <ul>
                &lt;{foreach from=$data item=v}&gt;
                <li><a href="&lt;{$v.url}&gt;.html" target="_blank" alt="&lt;{$v.seo_description}&gt;">&lt;{$v.title}&gt;</a></li>
                <{/foreach}&gt;
                </ul>
                </textarea>
                <br>
                1. 标签以&lt;{}&gt;开始，&lt;{/}&gt;结束<br>
                2. loop 指循块，item=循环当前数组赋值到这个参数(默认是chip)。<br>
                3. 内循环变量：$chip.url--链接 $chip.title--标题 $chip.thumb--图片地址 $chip.seo_description--描述。
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
</body>
</html>
