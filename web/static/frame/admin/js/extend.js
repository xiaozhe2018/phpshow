function set_template(tpl)
{
    $("#template").show();
    if(tpl=='list1')
    {
        $("#template").val('<ul>\n<{loop item=chip}>\n<li><a href="<{$chip.url}>.html" target="_blank" alt="<{$chip.seo_description}>"><{$chip.title}></a></li>\n<{/loop}>\n </ul>');
    }
    else if(tpl=='list2')
    {
        $("#template").val('<ul>\n<{loop item=chip}>\n<li><img src="<{$chip.thumb}>" width="100" height="100"><a href="<{$chip.url}>.html" target="_blank" alt="<{$chip.seo_description}>"><{$chip.title}></a></li>\n<{/loop}>\n</ul>');
    }
    else if(tpl=='list3')
    {
        $("#template").val('<{loop item=chip}>\n请输入您要循环的内容！\n<{/loop}>');
    }
}

function additem()
{
    $("#items").append('<tr><td> 链接：<input type="text" name="url[]" value=""> 标题：<input type="text" name="title[]" value="" > 缩略图：<input type="file" name="thumb[]" size="20">描述： <textarea name="seo_description[]" style="width:150px;height:30px;"></textarea></td></tr>');
}

function switch_type( array_type )
{
   if(array_type==1)
   {
       $(".data1").show();
       $(".data0").hide();
       $("#btnsel0").get(0).disabled = false;
       $("#btnsel1").get(0).disabled = true;
       $("#is_array").val( 1 );
   }
   else
   {
       $(".data1").hide();
       $(".data0").show();
       $("#btnsel0").get(0).disabled = true;
       $("#btnsel1").get(0).disabled = false;
       $("#is_array").val( 0 );
   }
}

function switch_image(key)
{
   $("#class_ico_i_"+key).toggle();
   $("#class_ico_"+key).toggle();
   attr = $("#class_ico_"+key).css('display');
   if(attr=='none')
   {
       $('#switch_btn_'+key).html('重新上传');
   }
   else
   {
       $('#switch_btn_'+key).html('取消');
   }
}

$(function(){
    if( $(".preview") ) {
        $(".preview").preview();
    }
});