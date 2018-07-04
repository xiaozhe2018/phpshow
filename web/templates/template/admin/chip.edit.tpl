<{include file="admin/header.tpl"}>
<body class="frame-from">
<script src="../../static/frame/admin/js/jquery.imagePreview.js" type="text/javascript"></script>
<script src="../../static/frame/admin/js/extend.js" type="text/javascript"></script>

<dl class="tips">
    <dd>
        <strong>当前位置：</strong><a href="?ct=chip">chip碎片管理</a> &gt;&gt; 修改chip标签
    </dd>
    <dd class='right'></dd>
</dl>

<form name="form1" action="?" method="POST"  jstype="vali" enctype="multipart/form-data">
<input type="hidden" name="ct" value="chip">
<input type="hidden" name="ac" value="edit">
<input type="hidden" name="id" value="<{$data.id}>">
<input type='hidden' name='is_array' id='is_array' value='<{$data.isarray}>'>
<table class="form">
    <tr>
        <td class='title' width='100'> 名称：<font color="red">*</font> </td>
        <td class='fitem'><input type='text'  class='text error' name="name" value="<{$data.name}>"  vali="notempty" errormsg="请填写chip名称！" />
            <span class="text-hint normal">名称必须唯一。</span>
        </td>
    </tr>
    <tr>
        <td class='title'>描述：<font color="red">*</font></td>
        <td class='fitem'><input type='text' name="description"  class='text error l' value='<{$data.description}>' vali="notempty" errormsg="请填chip描述！" />
        <span class="text-hint normal">填写相关描述</span>
        </td>
    </tr>
    <tr>
        <td class='title'>排序：</td>
        <td class='fitem'><input type='text' name="sortrank"  class='text error s' value='<{$data.sortrank}>' vali="notempty" errormsg="请填chip排序！" />
        <span class="text-hint normal">越大越靠前。</span>
        </td>
            </tr>
            <!--
            <tr>
                <td class='title'>数据格式：</td>
                <td class='fitem'>
            <input type='hidden' name='is_array' id='is_array' value='<{$data.isarray}>' />
            <button type='button' name='sel0' id='btnsel0' onclick="switch_type(0)" <{if $data.isarray==0}>disabled=disabled<{/if}>>HTML代码块</button>
            &nbsp;
            <button type='button' name='sel1' id='btnsel1' onclick="switch_type(1)" <{if $data.isarray==1}>disabled=disabled<{/if}>>数据列表</button>
                </td>
            </tr>
            -->
            <tr class="data0" <{if $data.isarray==1}>style="display:none"<{/if}>>
                <td colspan="2" class="single">
                    <{if $data.isarray==0}>
                    <{#editor toolbar='Default' fieldname='content' value=$data.data height='350'}>
                    <{else}>
                    <{#editor toolbar='Default' fieldname='content' height='350'}>
                    <{/if}>
                </td>
            </tr>
            <tr class="data1" <{if $data.isarray==0}>style="display:none"<{/if}>>
                <td class='title'>列表数据：</td>
                <td class='fitem'>
                 <button type="button" onclick="javascript:additem();" >增加数据项</button>
                 &nbsp; &nbsp;
                 <button type="submit">保存数据</button>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="data1 single" <{if $data.isarray==0}>style="display:none"<{/if}>>
                    <table class="table-sort table-operate" id="items">
                        <{if $data.isarray==1}>
                        <{foreach from=$data.data key=key item=v}>
                        <tr>
                            <td>
                            链接：<input type="text" name="url[<{$key}>]" value="<{$v.url}>">
                            标题：<input type="text" name=title[<{$key}>]  value='<{$v.title}>'>
                            缩略图：<input type='file' size="20"  name='thumb[<{$key}>]' <{if isset($v.thumb)}> style="display:none;"<{/if}>  id="class_ico_<{$key}>">
                            <{if isset($v.thumb)}>
                            <input type='hidden' name='thumb[<{$key}>]' value="<{$v.thumb}>"><button type='button' class="preview"  bwidth="210px" bimg="<{$v.thumb}>">预览</button>
                            <button type="button" onclick="javascript:switch_image(<{$key}>)" id="switch_btn_<{$key}>">重新上传</button>
                            <{/if}>
                            描述： <textarea name="seo_description[]" style="width:150px;height:30px;"><{$v.seo_description}></textarea>
                            </td>
                        </tr>
                    <{/foreach}>
                    <{/if}>
                    </table>
            </td>
            </tr>
            <tr class="data1" <{if $data.isarray==0}>style="display:none"<{else}>style="border-top:1px dashed #ddd"<{/if}>>
                <td class='title'>列表模板：</td>
                <td class='fitem'>
                    <button type="button" onclick="javascript:set_template('list1')">默认列表模板（不带缩略图）</button>
                    <button type="button" onclick="javascript:set_template('list2')">列表模板（带缩略图）</button>
                    <button type="button" onclick="javascript:set_template('list3')">自定义列表模板</button>
                    <div style="height:10px;"></div>
                    <textarea name="template" style="width:80%" id="template"><{$data.template}></textarea>
                    <br>
                    1. 标签以&lt;{}&gt;开始，&lt;{/}&gt;结束<br>
                    2. loop 指循块，item=循环当前数组赋值到这个参数(默认是chip)。<br>
                    3. 内循环变量：$chip.url--链接 $chip.title--标题 $chip.thumb--图片地址 $chip.seo_description--描述。
                </td>
            </tr>
            <tr>
                <td colspan='2' height='60' class='single'>
                <button type="submit">保存</button> &nbsp;&nbsp;&nbsp;
                <button type="reset">重设</button>
                </td>
            </tr>
        </table>
    </form>
</div>
</body>
</html>
