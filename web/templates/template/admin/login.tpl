<!DOCTYPE html>
<html lang="zh-cn">
<head>
	<meta charset="UTF-8" />
	<title> <{$title}>--登录 </title>
	<link rel="stylesheet" href="../../static/newui/css/login.css" />
</head>
<body>
	<div id="bg"></div>
	<form name="form1" method="post" action="?ct=index&ac=login">
        <input type="hidden" name="gourl" value="<{$gourl}>" />

		<div class="form">
			<div class="form-head">
				<div class="logo"></div>
			</div>
			<div class="form-body">
				<div class="form-control">
					<label for="username">用户名：</label>
					<div class="input">
						<input name="username" type="text" id="username" placeholder="用户名">
					</div>
				</div>
				<div class="form-control">
					<label for="password">密　码：</label>
					<div class="input">
						<input name="password" type="password" id="password" placeholder="密码">
					</div>
				</div>
				<div class="form-control">
					<label for="vdcode">验证码：</label>
					<div class="input validate">
						<input name="validate" type="text" id="vdcode" class="text" placeholder="验证码"/>
            			<img id="vdimgck" src="./?ac=validate_image" alt="看不清？点击更换" align="absmiddle" style="cursor:pointer;"  onclick="this.src=this.src+'?'" />
					</div>
				</div>
			</div>
			<div class="form-error-tip">
				<{$errmsg}>
			</div>
			<div class="form-action clearfix">
				<button type="submit" name="bt_submit" class="bt_login">登录</button>
          		<button type="reset" name="bt_reset" class="bt_reset">重置</button>
			</div>
		</div>
	</form>
</body>
</html>