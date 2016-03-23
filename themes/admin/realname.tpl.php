<?php
require 'header.php';
if($this->func=='index')
{
?>
	<div class="main_title">
    	<span>实名认证</span>列表
    </div>
    <form method="get">
    <div class="search">
        用户名：<input type="text" name="username" value="<?=$_GET['username']?>"/>
        申请时间：<input type="text" name="starttime" value="<?=$_GET['starttime']?>" class="Wdate" onclick="javascript:WdatePicker();" size="10"/>
               到<input type="text" name="endtime" value="<?=$_GET['endtime']?>" class="Wdate" onclick="javascript:WdatePicker();" size="10"/>
        <input type="submit" class="but2" value="查询" />
    </div>
    </form>
        <table class="table">
        	<tr class="bt">
            	<th>USER_ID</th>
                <th>用户名</th>
                <th>真实姓名</th>
                <th>性别</th>
                <th>出生日期</th>
                <th>籍贯</th>
                <th>身份证号</th>
                <th>正面</th>
                <th>背面</th>
                <th>认证时间</th>
                <th>审核备注</th>
                <th>状态</th>
                <th>操作</th>
            </tr>
            <?
            foreach($list as $row)
			{
			?>
            <tr>
            	<td><?=$row['user_id']?></td>
                <td><?=$row['username']?></td>
                <td><?=$row['name']?></td>
                <td><?=$row['sex']?></td>
                <td><?=$row['birthtime']?></td>
                <td><?=$row['area']?></td>
                <td><?=$row['card_id']?></td>
                <td><? if($row['card_pic1']){?><a href="<?=$row['card_pic1'];?>" target="_blank">正面照片</a><? }?></td>
                <td><? if($row['card_pic2']){?><a href="<?=$row['card_pic2'];?>" target="_blank">背面照片</a><? }?></td>
                <td><?=$row['card_time']?></td>
                <td><?=$row['card_remark']?></td>
                <td><? switch($row['card_status']){case 2:echo "待审核";break;case 1:echo "认证成功";break;case 0:echo "审核不通过";break;}?></td>
                <td>
				<? 
				if($row['card_status']==2)
				{
					echo $this->anchor('realname/edit/?user_id='.$row['user_id'],'审核');
				}
				else
				{
					echo '完成';
				}
				?>
                    </td>
            </tr>
            <? }?>
        </table>
        
		<? if(empty($total))
		   {
			   echo "无记录！";
		   }
		   else
		   {
			   echo $page;
		   }?>
<?
}
elseif($this->func=='edit')
{
?>
    <div class="main_title">
        <span>实名认证</span>审核
		  <?=$this->anchor('realname','列表','class="but1"');?>
    </div>
    <form method="post">
    	<input type="hidden" name="user_id" value="<?=$data['user_id']?>"/>
    	<div class="form1">
            <ul>
                <li><label>用户名：</label><?=$data['username']?></li>
                <li><label>真实姓名：</label><?=$data['name']?></li>
                <li><label>性别：</label><?=($data['sex']==1)?'男':'女'?></li>
                <li><label>出生日期：</label><?=$data['birthtime']?></li>
                <li><label>身份证号：</label><?=$data['card_id']?></li>
                <li><label>籍贯：</label><?=$data['province']." ".$data['city']." ".$data['county']?></li>
                <li><label>身份证正面：</label><a href="<?=$data['card_pic1'];?>" target="_blank"><img src="<?=$data['card_pic1']?>" align="absmiddle" width="100"/></a></li>
                <li><label>身份证背面：</label><a href="<?=$data['card_pic2'];?>" target="_blank"><img src="<?=$data['card_pic2']?>" align="absmiddle" width="100"/></a></li>
                <li><label>状态：</label><input type="radio" name="card_status" value="1"/>审核通过<input type="radio" name="card_status" value="0"/>审核不通过</li>
                <li><label>审核备注：</label><textarea name="card_remark" cols="45" rows="5"></textarea></li>
            </ul>
            <input type="submit" class="but3" value="保存" />
            <input type="button" class="but3" value="返回" onclick="window.history.go(-1)"/>
        </div>
    </form>

<?
}
require 'footer.php';?>