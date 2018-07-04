var step = 1;
var username = false;

$(document).ready(function()
{
    $("#email").change(function()
    {
        check_step();
    });
    $("#agreement").click(function()
    {
        if( !$("#agreement").get(0).checked )
        {
            $("#agreement_info").html("您需要阅读并接受注册协议才可以继续注册");
            $("#agreement_info").addClass("onerror");
            $("#bt_item2").get(0).disabled = true;
            $("#bt_item2").addClass("disabled");
         }
         else
         {
            $("#agreement_info").html("");
            remove_sta( $("#agreement_info") );
            $("#bt_item2").get(0).disabled = false;
            $("#bt_item2").removeClass("disabled");
         }
    });
    $("#userpwd").change(function()
    {
        if ( $("#userpwd").val() == "" )
		    {
			    $("#userpwd_info").html("密码不能为空");
			    remove_sta( $("#userpwd_info") );
			    $("#userpwd_info").addClass("onerror");
			    return false;
		    }
		    if ( !reg_match( $("#userpwd").val(), /^[a-zA-Z0-9]{8,16}$/) )
		    {
			    $("#userpwd_info").html("8-16个字符内，只能包含英文字母、数字");
			    $("#userpwd_info").addClass("onerror");
			    return false;
		    } else {
		        $("#userpwd_info").html("正确");
		        remove_sta( $("#userpwd_info") );
		        $("#userpwd_info").addClass("oncorrect");
		    }
    });
    $("#userpwd_affirm").change(function()
    {
        if ( !reg_match( $("#userpwd_affirm").val(), /^[a-zA-Z0-9]{8,16}$/) )
		    {
			    $("#userpwd_affirm_info").html("8-16个字符内，只能包含英文字母、数字");
			    $("#userpwd_affirm_info").addClass("onerror");
			    return false;
		    } else {
		        $("#userpwd_affirm_info").html("正确");
		        remove_sta( $("#userpwd_info") );
		        $("#userpwd_affirm_info").addClass("oncorrect");
		    }
        if ( $("#userpwd").val() != $("#userpwd_affirm").val() )
		    {
			    $("#userpwd_affirm_info").html("两次输入密码不一致");
			    $("#userpwd_affirm_info").addClass("onerror");
			     return false;
		    } else {
		        $("#userpwd_affirm_info").html("正确");
		        remove_sta( $("#userpwd_affirm_info") );
		        $("#userpwd_affirm_info").addClass("oncorrect");
		    }
		 });   
});

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
function userid_change()
{
    if( $("#userid").val() == "" ) {
        $("#userid_info").html("请填写用户名");
        return false;
    }
    if ( reg_match( $("#userid").val(), /^[a-zA-Z0-9]{4,20}$/) )
    {
        $("#userid_info").ajaxStart(function(){
           $("#userid_info").removeClass("onerror");
           $("#userid_info").removeClass("oncorrect");
           $("#userid_info").html("验证中...");
           $("#userid_info").addClass("onfocus");
        });
        $.ajax({
            type: "POST",
            url: "member/index.php",
            data: "ct=index&ac=test_userid&userid="+$("#userid").val(),
		        dataType: 'html',
            success: function( rs ) {
                if( rs != "") {
                    username = false;
                    $("#userid_info").html( rs );
                    $("#userid_info").addClass("onerror");
                } else {
                    username = true;
                    check_step();
                }
            }
        });
    }
    else
    {
        $("#userid_info").html("必须为4-20个字符的字母、数字或其组合！");
			  remove_sta( $("#userid_info") );
			  $("#userid_info").addClass("onerror");
    }
}
function remove_sta(obj)
{
    obj.removeClass("onerror");
    obj.removeClass("onfocus");
}
function check_step()
{
	if( !username )
	{
	    userid_change();
	    return false;
	}
	//用户密码信息
	if(step==1)
	{
		if ( $("#userid").val() == "" )
		{
			  $("#userid_info").html("用户名不能为空");
			  remove_sta( $("#userid_info") );
			  $("#userid_info").addClass("onerror");
			  return false;
		}
		if ( !reg_match( $("#userid").val(), /^[a-zA-Z0-9]{4,20}$/) )
		{
			  $("#userid_info").html("4-20个字符内，只能包含英文字母、数字");
			  $("#userid_info").addClass("onerror");
			  return false;
		} else {
		    $("#userid_info").html("正确");
		    remove_sta($("#userid_info"));
		    $("#userid_info").addClass("oncorrect");
		}
		if ( $("#userpwd").val() == "" )
		{
			  $("#userpwd_info").html("密码不能为空");
			  remove_sta( $("#userpwd_info") );
			  $("#userpwd_info").addClass("onerror");
			  return false;
		}
		if ( !reg_match( $("#userpwd").val(), /^[a-zA-Z0-9]{8,16}$/) )
		{
			  $("#userpwd_info").html("8-16个字符内，只能包含英文字母、数字");
			  $("#userpwd_info").addClass("onerror");
			  return false;
		} else {
		    $("#userpwd_info").html("正确");
		    remove_sta( $("#userpwd_info") );
		    $("#userpwd_info").addClass("oncorrect");
		}
		if ( $("#userpwd_affirm").val() == "" )
		{
			  $("#userpwd_affirm_info").html("请输入确认密码");
			  remove_sta( $("#userpwd_affirm_info") );
			  $("#userpwd_affirm_info").addClass("onfocus");
			  return false;
		}
		if ( $("#userpwd").val() != $("#userpwd_affirm").val() )
		{
			  $("#userpwd_affirm_info").html("两次输入密码不一致");
			  $("#userpwd_affirm_info").addClass("onerror");
			  return false;
		} else {
		    $("#userpwd_affirm_info").html("正确");
		    remove_sta( $("#userpwd_affirm_info") );
		    $("#userpwd_affirm_info").addClass("oncorrect");
		}
		if( !$("#agreement").get(0).checked ) {
        $("#agreement_info").html("您需要阅读并接受注册协议才可以继续注册");
        $("#agreement_info").addClass("onerror");
        $("#bt_item2").get(0).disabled = true;
        $("#bt_item2").addClass("disabled");
        return false;
     }
     else {
        $("#agreement_info").html("");
        remove_sta( $("#agreement_info") );
        $("#bt_item2").get(0).disabled = false;
        $("#bt_item2").removeClass("disabled");
    }
		return true;
	}
	//推广类型
	else if(step==3)
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
	    return true;
	}
	//联系方式
	else if(step==4)
	{
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
	    return true;
	}
	//安全问题
	else if(step==5)
	{
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
	return true;
}

function check_forms()
{
	if(step==1)
	{
		if( check_step()  )
		{
			$("#item2").hide();
			$("#item3").show();
			step = 3;
		}
	}
	else if(step==3)
	{
		if( check_step()  )
		{
			$("#item3").hide();
			$("#item4").show();
			step = 4;
		}
	}
	else if(step==4)
	{
		if( check_step()  )
		{
			$("#item4").hide();
			$("#bt_next").hide();
			$("#bt_submit").show();
			$("#item5").show();
			step = 5;
		}
	}
	else if(step==5)
	{
		return check_step();
	}
}
 