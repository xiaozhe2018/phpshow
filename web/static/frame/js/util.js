/**
*　事符格式化
*/
String.format = function(str) {
    var args = arguments, re = new RegExp("%([1-" + args.length + "])", "g");
    return String(str).replace(
    re,
    function($1, $2) {
        return args[$2];
    }
    );
};

var  Util = {};

/*
* 配置信息
*/
Util.Config = {
    //遮罩层背景颜色
    Screen_Background: "#999",
    //遮罩层透明度
    Screen_Opacity: "2",
    //遮罩层内容背景颜色
    Screen_ContentBg: "transparent",
    Screen_PositionTop:"0",
    Screen_PositionLeft:"50%",

    //结果隐藏时间
    Result_HideTime:4000,
    //结果背景色（警告）
    Result_Alert_BgColor:"#FFE222",
    //结果背景色（成功）
    Result_Success_BgColor:"#008000",
    //结果背景色（失败）
    Result_Failed_BgColor:"#D84544",
    //结果字体色（警告）
    Result_Alert_FontColor:"#000",
    //结果字体色（成功）
    Result_Success_FontColor:"#fff",
    //结果字体色（失败）
    Result_Failed_FontColor:"#fff",

    TextBoxDefaultColor: "#666",
    TextBoxActiveColor:"#000"
}

/*
*
* 复制
*/
Util.Copy = function(pStr,hasReturn){
	var result = false;
    //IE
    if(window.clipboardData)
    {
        window.clipboardData.clearData();
        result = window.clipboardData.setData("Text", pStr);
    }
    //FireFox
    else if (window.netscape)
    {
        try
        {
            netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
        }
        catch (e)
        {
            alert("您的firefox安全限制限制您进行剪贴板操作，请打开'about:config'将signed.applets.codebase_principal_support'设置为true'之后重试");
            return result;
        }
        var clip = Components.classes["@mozilla.org/widget/clipboard;1"].createInstance(Components.interfaces.nsIClipboard);
        if (!clip)
        	return result;
        var trans = Components.classes["@mozilla.org/widget/transferable;1"].createInstance(Components.interfaces.nsITransferable);
        if (!trans)
        	return result;
			
        trans.addDataFlavor('text/unicode');
        var str = new Object();
        var len = new Object();
        var str = Components.classes["@mozilla.org/supports-string;1"].createInstance(Components.interfaces.nsISupportsString);
        var copytext = pStr;
        str.data = copytext;
        trans.setTransferData("text/unicode",str,copytext.length*2);
        var clipid = Components.interfaces.nsIClipboard;
        if (!clip)
        	return result;
        clip.setData(trans,null,clipid.kGlobalClipboard);
		result = true;
    }
	if(hasReturn){
    	return result;
	}
	else{
		if(result){
			alert("内容已复制至剪贴板!");
		}
		else{
			alert("复制失败! 您使用的浏览器不支持复制功能.");
		}
	}
}

/*
* 弹出层
*/
Util.ScreenManager = {
    /*Public 隐藏方法*/
    Hide: function(doFun){
        this.canClose = true;
        this.popCoverDiv(false);
        if(doFun){
            doFun();
        }
    },
    /*Public 显示方法*/
    Show: function(containBox,isClickHide){
        if(isClickHide != undefined){
            Util.ScreenManager.IsClickHide = isClickHide;
        }
        else{
            Util.ScreenManager.IsClickHide = false;
        }
        this.popCoverDiv(true,containBox);
    },
    //取得页面的高宽
    getBodySize: function (){
        var bodySize = [];
        with(document.documentElement) {
            bodySize[0] = (scrollWidth>clientWidth)?scrollWidth:clientWidth;//如果滚动条的宽度大于页面的宽度，取得滚动条的宽度，否则取页面宽度
            bodySize[1] = (scrollHeight>clientHeight)?scrollHeight:clientHeight;//如果滚动条的高度大于页面的高度，取得滚动条的高度，否则取高度
        }
        return bodySize;
    },
    config:{
        cachebox:"screen_cache_box",/*缓存层*/
        contentbox:"screen_content_box",/*内容层*/
        coverbox:"screen_cover_div",/*透明层*/
        gonebox:"screen_gone_box"	/*移位缓存层*/
    },
    canClose:true,
    ShowSelfControl:function(containBox,showFun){
        Util.ScreenManager.IsClickHide = true;
        this.popCoverDiv(3,containBox,undefined,showFun);
    },
    //创建遮盖层
    popCoverDiv: function (isShow,containBox,setWidth,showFun){
        var screenBox = document.getElementById(Util.ScreenManager.config.coverbox);
        if (!screenBox) {
            //如果存在遮盖层，则让其显示
            //否则创建遮盖层
            var coverDiv = document.createElement('div');
            document.body.appendChild(coverDiv);
            coverDiv.id = Util.ScreenManager.config.coverbox;
            var bodySize;
            with(coverDiv.style) {
                if ($.browser.msie && $.browser.version == 6) {
                    position = 'absolute';
                    background = Util.Config.Screen_Background;
                    left = '0px';
                    top = '0px';
                    bodySize = this.getBodySize();
                    width = '100%';
                    height = bodySize[1] + 'px';
                }
                else{
                    position = 'fixed';
                    background = Util.Config.Screen_Background;
                    left = '0';
                    top = '0';
                    width = '100%'
                    height = '100%';
                }
                zIndex = 99;
                if ($.browser.msie) {
                    filter = "Alpha(Opacity=" + Util.Config.Screen_Opacity + "0)";	//IE逆境
                } else {
                    opacity = Number("0."+Util.Config.Screen_Opacity);
                }
            }
            coverDiv.onclick = function(){
                if(Util.ScreenManager.canClose){
                    if(Util.ScreenManager.IsClickHide == undefined || Util.ScreenManager.IsClickHide == false){
                        coverDiv.style.display = "none";
                        document.getElementById(Util.ScreenManager.config.contentbox).style.display = "none";
                    }
                }
            };

            var contentDiv = document.createElement("div");
            contentDiv.id = Util.ScreenManager.config.contentbox;
            with(contentDiv.style){
                position = "absolute";
                backgroundColor = Util.Config.Screen_ContentBg;
                var widthNum = Number(setWidth != undefined?setWidth:500);
                width = widthNum + "px";
                left = Util.Config.Screen_PositionLeft;
                var mfNum = widthNum/2;
                marginLeft = "-" + mfNum + 'px';
                top = Util.Config.Screen_PositionTop;
                zIndex = 100;
            }

            document.body.appendChild(contentDiv);
            contentDiv.onmouseover = function(){
                Util.ScreenManager.canClose = false;
            };

            contentDiv.onmouseout = function(){
                Util.ScreenManager.canClose = true;
            };
            screenBox = contentDiv;
        }
        screenBox.style.display = isShow ? "block" : "none" ;
        if(isShow == 3){
            if(showFun){
                showFun();
            }
        }
        else{
            document.getElementById(Util.ScreenManager.config.contentbox).style.display = isShow ? "block" : "none" ;
            if(isShow && containBox){
                //创建Cache Box
                var cacheBox = document.getElementById(Util.ScreenManager.config.cachebox);
                if(!cacheBox){
                    var cBox = document.createElement("div");
                    document.body.appendChild(cBox);
                    cBox.id = Util.ScreenManager.config.cachebox;
                    cBox.style.display = "none";
                    cacheBox = cBox;
                }

                var goneBox = document.getElementById(Util.ScreenManager.config.gonebox);
                if(!goneBox){
                    var gBox = document.createElement("div");
                    document.body.appendChild(gBox);
                    gBox.id = Util.ScreenManager.config.gonebox;
                    gBox.style.display = "none";
                    goneBox = cBox;
                }

                var cBox = document.getElementById(Util.ScreenManager.config.contentbox);
                var contentNodes = cBox.childNodes;
                for(var i = 0,len = contentNodes.length; i < len; i++){
                    cacheBox.appendChild(contentNodes[i]);
                }
                containBox.style.display = "";
                cBox.appendChild(containBox);
            }
        }
        this.canClose = true;
    }
}


Util.Date = {
	GetTimeText:function(num){
		var arr = [3600,60];
		var result = "";
		for(var i = 0,len = arr.length; i < len; i++){
			var item = arr[i];
			if(num >= item){
				var s = (num / item).toFixed(0);
				result += Util.Date._getDoubleText(s) + ":";
				num = num % item;
			}
			else{
				result += "00:";
			}
		}
		result += Util.Date._getDoubleText(num);
		return result;
	},
	_getDoubleText: function(num){
		if(num.toString().length > 1){
			return num;
		}
		else{
			return "0" + num.toString();
		}
	}
}

/*
* Cookie控制
*/
Util.Cookie = {
    get:function(name){
        var cookieValue = "";
        var search = name + "=";
        if (document.cookie.length > 0) {
            offset = document.cookie.indexOf(search);
            if (offset != -1) {
                offset += search.length;
                end = document.cookie.indexOf(";", offset);
                if (end == -1)
                end = document.cookie.length;
                cookieValue = unescape(document.cookie.substring(offset, end));
            }
        }
        return cookieValue;
    },
    set:function(name, value, hours){
        var expire = "";
        if (hours != null) {
            expire = new Date((new Date()).getTime() + hours * 3600000);
            expire = "; expires=" + expire.toGMTString();
        }
        document.cookie = name + "=" + escape(value) + expire +";path=/";
    }
}

/**
* 显示结果静态方法
* Util.Result.Show(msg,isSuccess); //(msg: 内容; isSuccess： 0失败，1成功，2警告)
*/
Util.Result = {
    Show:function(msg,isSuccess,timeOut,isHide){
        if(!Util.Result.show_result_class){
            Util.Result.show_result_class = new ShowResultObject();
        }
        Util.Result.show_result_class.ShowMessage(msg,isSuccess);
        var time = Util.Config.Result_HideTime;
        if(timeOut){
            time = timeOut
        }
        if(isHide == undefined || isHide == true){
            window.setTimeout("Util.Result.show_result_class.Hide()",time);
        }
    },
    Hide: function(){
        if(Util.Result.show_result_class){
            Util.Result.show_result_class.Hide();
        }
    }
}

var ShowResultObject = function(boxID,displayBoxId){
    var _ResultBpx;
    var _DisplayTextBox;
    if(boxID !=undefined){
        _ResultBpx = document.getElementById(boxID);
    }
    else{
        _ResultBpx = document.createElement("div");
        document.body.appendChild(_ResultBpx);
    }
    if(displayBoxId != undefined){
        _DisplayTextBox = document.getElementById(displayBoxId);
    }
    else{
        _DisplayTextBox = document.createElement("span");
        _ResultBpx.appendChild(_DisplayTextBox);
    }

    var setStyle = function(isPerfect){
        var fontColor = "#69AE03";
        var bgColor = "#fff";
        switch(isPerfect){
            case 0:	//失败
            fontColor = Util.Config.Result_Failed_FontColor;
            bgColor = Util.Config.Result_Failed_BgColor;
            break;
            case 1:	//成功
            fontColor = Util.Config.Result_Success_FontColor;
            bgColor = Util.Config.Result_Success_BgColor;
            break;
            case 2:	//警告
            fontColor = Util.Config.Result_Alert_FontColor;
            bgColor = Util.Config.Result_Alert_BgColor;
            break;
        }

        _DisplayTextBox.style.color = fontColor;
        var  resultStyle = _ResultBpx.style;
        resultStyle.backgroundColor = bgColor;
        resultStyle.position = "absolute";
        resultStyle.zIndex = 1000;
        resultStyle.top = document.body.scrollTop + "px";
        resultStyle.padding = "2px 6px";
        var l = document.documentElement.clientWidth * 48/100;
        resultStyle.left = l + "px";
    }

    this.getResultBox = _ResultBpx;
    this.getDisplayBox = _DisplayTextBox;
    this.Hide = function(){
        this.getResultBox.style.display = "none";
        this.getDisplayBox.innerHTML =  "";

        /*取消监听 zen 2009-11-17 17:24*/
        $(window).unbind("scroll");
    },
    this.ShowMessage = function(text,isSuccess){
        setStyle(isSuccess);
        this.getDisplayBox.innerHTML = text;
        this.getResultBox.style.display = "";

        /*总是相对于窗口的顶部 zen 2009-11-17 17:24*/
        $(this.getResultBox).css("top",$("html").scrollTop());
        var box=$(this.getResultBox);
        $(window).bind("scroll",function(){
            box.css("top",$("html").scrollTop());
        });
    }
}

/**
* 验证
*/
Util.Validate = {
    mb_strlen: function(str){
        var offset = 0;
        for(var i=0; i<str.length; i++){
            var string = str.substr(i,1);
            if(escape(string).substr(0,2)=="%u"){
                offset +=3;
            }
            else{
                offset +=1;
            }
        }
        return offset;
    },

    Email: function(email) {
        var regular = /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/;
        if (!regular.test(email)) {
            return false;
        } else {
            return true;
        }
    }
};
