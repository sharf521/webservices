<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="utf-8" />
<title>网站管理后台</title>
<link rel="stylesheet" type="text/css" href="<?=$tpldir?>/css/base.css">

</head>
<body>
<div class="wrap">
	<div class="head">
		<div class="logo">网站管理后台</div>
	</div>
	<div class="login">
	<form method="post">
		<table cellpadding="0" cellspacing="0">
			<tr>
				<th><label for="username">管理员账号：</label></th>
				<td><span class="input"><input name="username" type="text" /></span></td>
			</tr>
			<tr>
				<th><label for="password">密码：</label></th>
				<td><span class="input admin_pwd"><input type="password"  name="password" /></span></td>
			</tr>

			<tr valign="top">
				<th><label for="lg_num">验证码：</label></th>
				<td style="padding-top:4px;"><span class="input lg_num"><input name="valicode" type="text" size="11" maxlength="4" value=""/>&nbsp;</span>
					<img src="/plugin/code.php" alt="点击刷新" onClick="this.src='/plugin/code.php?t=' + Math.random();" align="absmiddle" style="cursor:pointer" />
				</td>
			</tr>
			<tr>
				<th>&nbsp;</th>
				<td><input name="submit" class="admin_btn" type="submit" value="" /></td>
			</tr>
		</table>
	</form>
	</div>
</div>
<div class="bottom"><span style="color:#ff6600"></span></div>
<div style="display:none">{$_G.system.tongji}</div>
</body></html>

