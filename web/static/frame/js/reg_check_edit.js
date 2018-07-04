var step = 1;
var username = false;

function reg_match(str, patrn)
{
	return patrn.exec(str);
}
function extend_type_change(obj)
{
    var selval = obj.options[obj.options.selectedIndex].value;
    if( selval=='其它方式' )
    {
        $("#extend_type_h").show();
        $("#extend_type").val("");
    }
    else
    {
        $("#extend_type_h").hide();
        $("#extend_type").val(selval);
    }
    check_step();
}
function question_change(obj)
{
    var selval = obj.options[obj.options.selectedIndex].value;
    if( selval=='自定义问题' || selval=='' )
    {
        $("#question_h").show();
        $("#questionf").val("");
        if( selval != '' )
        {
            $("#question_info").html("");
	          $("#question_info").removeClass("onerror");
		        $("#question_info").addClass("onshow");
        }
    }
    else
    {
        $("#question_h").hide();
        $("#questionf").val(selval);
        
    }
    check_step();
}
function remove_sta(obj)
{
    obj.removeClass("onerror");
    obj.removeClass("onfocus");
}
function check_step()
{
	    if( $("#extend_type").val()=="" )
	    {
	        if($("#extend_type_sel").val()=="其它方式")
	        {
	            $("#extend_type_info").html("当选择自定义其它方式时，您需要自行填写！");
	        }
	        else
	        {
	            $("#extend_type_info").html("请选择您的推广方式！");
	        }
	        $("#extend_type_info").addClass("onerror");
	        $("#question_h").show();
			    return false;
	    } else {
	        $("#extend_type_info").html("");
	        $("#extend_type_info").removeClass("onerror");
		      $("#extend_type_info").addClass("onshow");
	    }
	    if( $("#extend_msg").val()=="" )
	    {
	        $("#extend_msg_info").html("请填写推广方式具体描述");
	        $("#extend_msg_info").addClass("onerror");
			    return false;
	    } else {
	        $("#extend_msg_info").html("");
	        $("#extend_msg_info").removeClass("onerror");
		      $("#extend_msg_info").addClass("onshow");
	    }
	    if( $("#name").val()=="" )
	    {
	        $("#name_info").html("联系人信息不可为空");
	        $("#name_info").addClass("onerror");
			    return false;
	    } else {
	        $("#name_info").html("");
	        $("#name_info").removeClass("onerror");
		      $("#name_info").addClass("onshow");
	    }
	    if( $("#qq").val()=="" )
	    {
	        $("#qq_info").html("QQ号码不可为空");
	        $("#qq_info").addClass("onerror");
			    return false;
	    }
	    if( !reg_match( $("#qq").val(), /^[0-9]{5,11}$/) )
	    {
	        $("#qq_info").html("错误的QQ号码格式，请重新填写");
	        $("#qq_info").addClass("onerror");
			    return false;
	    } else {
	        $("#qq_info").html("");
	        $("#qq_info").removeClass("onerror");
		      $("#qq_info").addClass("onshow");
	    }
	    if( $("#mobile").val()=="" )
	    {
	        $("#mobile_info").html("手机号码不可为空");
	        $("#mobile_info").addClass("onerror");
			    return false;
	    }
	    if( !reg_match( $("#mobile").val(), /^(13|15|18)[0-9]{9}$/) )
	    {
	        $("#mobile_info").html("错误的手机号码格式，请重新填写");
	        $("#mobile_info").addClass("onerror");
			    return false;
	    } else {
	        $("#mobile_info").html("");
	        $("#mobile_info").removeClass("onerror");
		      $("#mobile_info").addClass("onshow");
	    }
	    if( $("#tel").val() != "" && !reg_match( $("#tel").val(), /^[0-9-]{7,12}$/) )
	    {
	        $("#tel_info").html("错误的电话号码格式，请重新填写");
		      $("#tel_info").addClass("onerror");
			    return false;
	    } else {
	        $("#tel_info").html("");
	        $("#tel_info").removeClass("onerror");
		      $("#tel_info").addClass("onshow");
	    }
	    if( $("#question_select").val()=="" )
	    {
	        $("#question_info").html("请选择安全问题");
	        $("#question_info").addClass("onerror");
			    return false;
	    }
	    if( !reg_match( $("#questionf").val(), /^[^\r\n\t]{4,20}$/) )
	    {
	        $("#question_info").html("");
	        $("#question2_info").html("安全问题长度需要为4-20个字符");
	        $("#question2_info").addClass("onerror");
			    return false;
	    } else {
	        $("#question_info").html("");
	        $("#question2_info").html("");
	        $("#question2_info").removeClass("onerror");
		      $("#question2_info").addClass("onshow");
	    }
	    if( !reg_match( $("#answer").val(), /^[^\r\n\t]{2,20}$/) )
	    {
	        $("#answer_info").html("安全问题答案必须为2-20字符");
	        $("#answer_info").addClass("onerror");
			    return false;
	    } else {
	        $("#answer_info").html("");
	        $("#answer_info").removeClass("onerror");
		      $("#answer_info").addClass("onshow");
	    }
	    if( !reg_match( $("#email").val(), /^[0-9a-zA-Z][a-zA-Z0-9\._-]{1,}@[a-zA-Z0-9-]{1,}[a-zA-Z0-9]\.[a-zA-Z\.]{1,}[a-zA-Z]$/) )
	    {
	        $("#email_info").html("请输入正确的电子邮箱格式！");
	        $("#email_info").addClass("onerror");
			    return false;
	    } else {
	        $("#email_info").html("");
	        $("#email_info").removeClass("onerror");
		      $("#email_info").addClass("onshow");
	    }
	    return true;
}

function check_forms()
{
	 return check_step();
}
 