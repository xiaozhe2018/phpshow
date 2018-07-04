<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style>
* { margin:0;padding:0;color:#333;font-size:12px; }
h2 { font-size:12px;height:32px; line-height:32px;padding:0 10px;background:#EEF8FF;border-bottom:1px #8ebce1 solid; }
h4 { font-size:14px; line-height:24px; margin-bottom:10px; }
.main { padding:5px;width:500px;margin:10px auto; }
.border { border:1px #C3E0F8 solid; padding-bottom:20px;  }
.content { padding:20px 0; text-align:center; background:#fff; }
 </style>
<base target='_self' />
<title> <{$title}> </title>
</head>
<body>
<div class='main'>
    <div class='border'>
        <h2><{$title}></h2>
        <div class='content'>
            <h4> <{$msg}> </h4>
            <{$jumpmsg}>
        </div>
    </div>
</div>
<script lang='javascript'>
var pgo=0;
function JumpUrl(){ if(pgo==0){ location='<{$gourl}>'; pgo=1;  } }
<{$jstmp}>
</script>
</body>
</html>