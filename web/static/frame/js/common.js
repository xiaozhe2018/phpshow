$(document).ready(function() {
});


var TableHandle = (function(){
							
							
	function init(table){
		iehover();
	}
	
	function iehover(){
		if($.browser.msie && $.browser.version == "6.0"){
			$("tbody tr").hover(
			  function () {
				$(this).addClass("iehover");
			  },
			  function () {
				$(this).removeClass("iehover");
			  }
			);
		}
	}
	
	
	function checkboxHandle(table){
		var isCheckall = false;
		var Items = table.find('tbody :checkbox');
		
		$(Items).each(function(){
			$(this).click(function (){
				activeTr(this);
				if(isCheckall==true){
					table.find('thead :checkbox').attr("checked",false);
				}
			});

		});//选中高亮tr
		
		
		
		table.find('thead :checkbox').click(function(){
			if(this.checked==true){
				$("tbody :checkbox:not(:checked)").each(function(){
					this.checked = true;
					isCheckall = true;
					activeTr(this);
				});
			}else{										 
				$("tbody :checkbox:checked").each(function(){
					this.checked = false;
					activeTr(this);
				});									 
			}
			
		});//全选反选按钮
		
		function activeTr(_this){
			var tr = $(_this).parent().parent();
			if($(_this).is(":checked")){
				tr.addClass("checked");	
			}else{
				tr.removeClass("checked");
			}	
		}
		
	}
	
	
	return{
		init:init,
		checkboxHandle:checkboxHandle
	};
	
	
	
	
	
	
})();

function selectTab(el,show){
	$(el).bind("click",function (event) {
		event.stopPropagation();
		$(this).blur();
		$(el).parent().find("a.active").removeClass("active");
		$(this).addClass("active");
		show(this);
		return false;
	});
}

/**
 * 文本复制功能
 * @param el  string
 * @return
 */
function textCopy(copy)
{
	//alert(copy);
    if (window.clipboardData) //ie
    {
        window.clipboardData.setData("Text", copy);
    }
    else if (window.netscape)  //非ie
    {
        if(!$.browser.mozilla)  //非ff
        {
    		alert('你使用的浏览器不支持此种方式的复制，请使用Ctrl+C或者鼠标右键。');
    		document.getElementById(el).focus();
    		document.getElementById(el).select();
    		return false;
        }
        
        try
        {
            netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
        }
        catch (e)
        {
            alert("您的firefox安全限制限制您进行剪贴板操作，请打开'about:config'将signed.applets.codebase_principal_support'设置为true'之后重试");
            return false;
        }
        
        var clip = Components.classes['@mozilla.org/widget/clipboard;1'].createInstance(Components.interfaces.nsIClipboard);
        if (!clip)
        {
             return false;
        }
        var trans = Components.classes['@mozilla.org/widget/transferable;1'].createInstance(Components.interfaces.nsITransferable);
        if (!trans)
        {
            return false;
        }
        trans.addDataFlavor('text/unicode');
        var str = new Object();
        var len = new Object();
        var str = Components.classes["@mozilla.org/supports-string;1"].createInstance(Components.interfaces.nsISupportsString);
        var copytext=copy; str.data=copytext; trans.setTransferData("text/unicode",str,copytext.length*2);
        var clipid=Components.interfaces.nsIClipboard;
        if (!clip)
        {
    		return false;
        }

        clip.setData(trans,null,clipid.kGlobalClipboard);
    }
	alert("已成功复制\""+copy+'"');
	return false;
}

/**
 * 显示图片
 * @return
 */
function showPic(url, width, height)
{
	if(!width){width = 500;}
	if(!height){height = 400;}
	//var host = get_host();
    tb_show('显示图片', url + '?&TB_iframe=true&height='+height+'&width='+width, true);
}

/**
 * 获取host
 * @return string
 */
function get_host()
{
	var host = location.href;
	if(/^http:\/\/(\S+?)\//.test(host))
	{
		return host.replace(/^http:\/\/(\S+?)\//, '$1');
	}
	return host.replace(/^http:\/\/(\S+?)$/, '$1');
}