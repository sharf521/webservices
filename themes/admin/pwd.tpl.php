<?php require 'header.php';
if($this->func=='index')
{
?>
	<div class="main_title">
    	<span>修改密码</span>
    </div>
    <form method="post" onsubmit="return setdisabled();">
    <table cellpadding="4" cellspacing="1">
        <tr>
            <td align="right">原密码：</td><td><input type="password" name="old_password"/></td><td></td>
        </tr>
        <tr>
            <td align="right">新密码：</td>
            <td><input type="password" name="password"/></td>
            <td>密码长度6位到15位</td>
        </tr>
        <tr>
            <td align="right">确认新密码：</td>
            <td><input type="password" name="sure_password" /></td><td></td>
        </tr>
        <tr>
            <td align="right">验证码： </td>
            <td><input name="valicode" type="text" size="11" maxlength="4" value=""/>
            <img src="/plugin/code.php" alt="点击刷新" onClick="this.src='/plugin/code.php?t=' + Math.random();" align="absmiddle" style="cursor:pointer" /></td><td></td>
        </tr>
        <tr><td></td><td> <input class="but3" value="保存" type="submit"/></td><td></td></tr>
    </table>
    </form>
<? }?>