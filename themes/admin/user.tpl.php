<?php
require 'header.php';
if($this->func=='index'){?>
	<div class="main_title">
    	<span>用户管理</span>列表
    </div>
    <form method="get">
    <div class="search">
    	用户类型：<select name="type_id" id="type_id">
                	<option value="">请选择</option>
                	<?
                    	foreach($usertype as $utype)
						{
							?>
                            <option value="<?=$utype['id']?>" <? if($utype['id']==$_GET['type_id']){echo ' selected';}?>><?=$utype['name']?></option>  
                            <?
						}
					?>   
                </select>
        <? if($this->user['type_id']==2){?>
        注册来源：<select name="subsite_id">
                	<option value=""<? if($_GET['subsite_id']==""){?> selected="selected"<? }?>>请选择</option>
                    <option value="1"<? if($_GET['subsite_id']==1){?> selected="selected"<? }?>>车务系统</option>
                    <option value="2"<? if($_GET['subsite_id']==2){?> selected="selected"<? }?>>众筹系统</option>
                 </select>
        <? }?>
        用户名：<input type="text" name="username" value="<?=$_GET['username']?>" size="10"/>
        <input type="submit" class="but2" value="查询" />
    </div>
    </form>
        <table class="table">
        	<tr class="bt">
            	<th>USER_ID</th>
                <th>用户类型</th>
                <th>用户名</th>
                <th>EMAIL</th>
                <th>真实姓名</th>
                <th>电话</th>
                <th>QQ</th>
                <th>地址</th>
                <th>注册来源</th>
                <th>注册时间</th>
                <th>操作</th>
            </tr>
            <? $subsite=array("","车务系统","众筹系统");foreach($list as $key=>$row){?>
            <tr <? if($key%2==1)echo 'style="background-color:#efefef"';?>>
            	<td><?=$row['user_id']?></td>
                <td><?=$row['typename']?></td>
                <td><?=$row['username']?></td>
                <td><?=$row['email']?></td>
                <td><?=$row['name']?></td>
                <td><?=$row['tel']?></td>
                <td><?=$row['qq']?></td>
                <td><?=$row['address']?></td>
                <td><?=$subsite[$row['subsite_id']]?></td>
                <td><?=$row['addtime']?></td>
                <td class="operate">
				<? 
				if($row['user_id']=="1")
				{
					echo "ADMIN用户禁止操作";
				}
				else
				{
					echo $this->anchor('user/edit?user_id='.$row['user_id'],'个人资料');
					echo '&nbsp;|&nbsp;';
					echo $this->anchor('user/edit_bank?user_id='.$row['user_id'],'银行账号');
					echo '&nbsp;|&nbsp;';
					echo $this->anchor('user/edittype?user_id='.$row['user_id'],'修改用户类型');
				}
				?>
                    </td>
            </tr>
            <? }?>
        </table>
        <script>
		function doExcel()
		{
			href=window.location.href;
			if(href.indexOf("?") > 0 )
			{
				href=window.location.href+'&xls=excel';
			}
			else
			{
				href=window.location.href+'?xls=excel';
			}
			window.location=href;
		}
		</script>
        <input type="button" onclick="doExcel()" value="导出列表" />
		<? if(empty($total)){echo "无记录！";}else{echo $page;}?>
<? }elseif($this->func=='edit'){?>
    <div class="main_title">
        <span>用户管理</span>编辑
		<?=$this->anchor('user','列表','class="but1"');?>
    </div>
    <form method="post">
    	<input type="hidden" name="user_id" value="<?=$row['user_id']?>"/>
    	<div class="form1">
            <ul>
                <li><label>用户名：</label><?=$row['username']?></li>
                <li><label>真实姓名：</label><input type="text" name="name" value="<?=$row['name']?>"/></li>
                <li><label>电话：</label><input type="text" name="tel" value="<?=$row['tel']?>"/></li>
                <li><label>QQ：</label><input type="text" name="qq" value="<?=$row['qq']?>"/></li>
                <li><label>地址：</label><input type="text" name="address" value="<?=$row['address']?>"/></li>
            </ul>
            <input type="submit" class="but3" value="保存" />
            <input type="button" class="but3" value="返回" onclick="window.history.go(-1)"/>
        </div>
    </form>
<? }elseif($this->func=='edit_bank'){?>
    <div class="main_title">
        <span>用户管理</span>编辑银行账号
		<?=$this->anchor('user','列表','class="but1"');?>
    </div>
    <form method="post">
    	<input type="hidden" name="user_id" value="<?=$row['user_id']?>"/>
        <div class="form1">
            <ul>
                <li><label>用户名：</label><?=$row['username']?></li>
                <li><label>真实姓名：</label><?=$row['name']?></li>
                <li><label>银行：</label><input type="text" name="bank" value="<?=$row['bank']?>"/></li>
                <li><label>分行：</label><input type="text" name="branch" value="<?=$row['branch']?>" size="40"/></li>
                <li><label>账号：</label><input type="text" name="account" value="<?=$row['account']?>" size="40"/></li>
            </ul>
            <input type="submit" class="but3" value="保存" />
            <input type="button" class="but3" value="返回" onclick="window.history.go(-1)"/>
        </div>
    </form>
<? }elseif($this->func=='edittype'){?>
    <div class="main_title">
        <span>用户管理</span>修改用户类型
		<?=$this->anchor('user','列表','class="but1"');?>
    </div>
    <form method="post">
    	<input type="hidden" name="user_id" value="<?=$row['user_id']?>"/>
    	<div class="form1">
            <ul>
                <li><label>用户名：</label><?=$row['username']?></li>
                <li><label>真实姓名：</label><?=$row['name']?></li>
                <li><label>用户类型：</label>
                <select name="type_id" id="type_id">
                	<option value="">请选择</option>
                	<?
                    	foreach($usertype as $utype)
						{
							?>
                            <option value="<?=$utype['id']?>" <? if($utype['id']==$row['type_id']){echo ' selected';}?>><?=$utype['name']?></option>  
                            <?
						}
					?>   
                </select>   
                </li>
            </ul>
            <input type="submit" class="but3" value="保存" />
            <input type="button" class="but3" value="返回" onclick="window.history.go(-1)"/>
        </div>
    </form>
<?
}
elseif($this->func=='thaw')
{
?>
    <div class="main_title">
        <span>用户管理</span>解冻保证金
		<?=$this->anchor('user','列表','class="but1"');?>
    </div>
    <form method="post">
    	<input type="hidden" name="user_id" value="<?=$row['user_id']?>"/>
    	<div class="form1">
            <ul>
                <li><label>用户名：</label><?=$row['username']?></li>
                <li><label>真实姓名：</label><?=$row['name']?></li>
                <li><label>可用金额：</label><?=($row['use_money']!=0)?'￥'.$row['use_money']:'N/A'?></li>
                <li><label>保证金：</label><?=($row['baozheng_money']!=0)?'￥'.$row['baozheng_money']:'N/A'?></li>
                <li><label>解冻金额：</label><input type="text" name="money" onKeyUp="value=value.replace(/[^0-9.]/g,'')"/>元</li>
                <li><label>解冻备注：</label><textarea name="thaw_remark" cols="45" rows="5"></textarea>*必填</li>
            </ul>
            <input type="submit" class="but3" value="保存" />
            <input type="button" class="but3" value="返回" onclick="window.history.go(-1)"/>
        </div>
    </form>
<?
}
elseif($this->func=='frozen')
{
?>
    <div class="main_title">
        <span>用户管理</span>冻结保证金
		<?=$this->anchor('user','列表','class="but1"');?>
    </div>
    <form method="post">
    	<input type="hidden" name="user_id" value="<?=$row['user_id']?>"/>
    	<div class="form1">
            <ul>
                <li><label>用户名：</label><?=$row['username']?></li>
                <li><label>真实姓名：</label><?=$row['name']?></li>
                <li><label>可用金额：</label><?=($row['use_money']!=0)?'￥'.$row['use_money']:'N/A'?></li>
                <li><label>保证金：</label><?=($row['baozheng_money']!=0)?'￥'.$row['baozheng_money']:'N/A'?></li>
                <li><label>冻结金额：</label><input type="text" name="money" onKeyUp="value=value.replace(/[^0-9.]/g,'')"/>元</li>
                <li><label>冻结备注：</label><textarea name="frozen_remark" cols="45" rows="5"></textarea>*必填</li>
            </ul>
            <input type="submit" class="but3" value="保存" />
            <input type="button" class="but3" value="返回" onclick="window.history.go(-1)"/>
        </div>
    </form>
<?
}
require 'footer.php';