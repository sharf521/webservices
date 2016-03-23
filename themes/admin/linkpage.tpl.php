<?php require 'header.php';?>
<?
if($this->func=='index')
{
?>
<div class="main_title">
    <span>联动管理</span>列表<?=$this->anchor('linkpage/type_add','添加类型','class="but1"');?>
</div>

<? if($list){?>
<table border="0" class="flexme" width="100%">
<form method="post">
<thead class="theadtd">
<tr>
    	<th >ID</th>
		<th >联动类型</th>
    	<th >标识名</th>
        <th >排序</th>
		<th >添加时间</th>
        <th >操作</th>
    </tr>
</thead>
<tbody>
    <?php foreach($list as $row):?>
    <tr onmouseover="this.bgColor='#ECFBFF'" onmouseout="this.bgColor='#ffffff'" align="center">
        <td><?=$row['id']?><input type="hidden" name="id[]" value="<?=$row['id']?>" /></td>
        <td><input type="text" name="name[]" value="<?=$row['name']?>" size="40"/></td>
        <td><input type="text" name="code[]" value="<?=$row['code']?>" size="40"/></td>
		<td><input type="text" name="showorder[]" value="<?=$row['showorder']?>" size="6"/></td>
        <td><?=$row['createdate']?></td>
        <td>
        <? 
			echo $this->anchor("linkpage/linklist/?id={$row['id']}","管理")." | ";
			echo $this->anchor("linkpage/type_edit/?id={$row['id']}",'编辑')." | ";
			$arr=array(
				'onclick'=>"return confirm('确定要删除吗？')"
			);
			echo $this->anchor("linkpage/type_drop/?id={$row['id']}",'删除',$arr);
		    ?>
        </td>
    </tr>
     <?php endforeach?>
	<tr >
		<td colspan="6"  class="submit" align="center">
			<input type="submit" class="but3" value="修改资料" />
		</td>
	</tr>
	
</tbody>
</form>
</table>
<? echo $page;
}else{?>
没有符合条件的记录
<? }	
}elseif($this->func=='type_edit' || $this->func=='type_add'){?>
<div class="main_title">
    <span>联动管理</span><? if($this->func=='type_add'){?>添加类型<? }else{ ?>编辑<? }?>
    <?=$this->anchor('linkpage','列表','class="but1"');?>
</div>
<form method="post" action="<?=$this->base_url('linkpage/type_add')?>">  

<table cellpadding="4">
<tr><td>联动类型名称：</td><td><input class="bkboxinput" type="text" name="name" value="<?=$data['name']?>" size="40">
</td></tr>
<tr><td>联动类型标识名：</td><td><input class="bkboxinput" type="text" name="code" value="<?=$data['code']?>" size="40">
</td></tr>
<tr><td>排序：</td><td>
<input class="bkboxinput" type="text" name="showorder" value="<?=$data['showorder']?>"> 
</td></tr>

<tr><td></td><td><input type="submit" class="but3" value="保存">
 <input type="button" class="but3" value="返回" onclick="window.history.go(-1)"/></td></tr>
</table>
</form>
<? if ($this->func=='type_add'){?>
<table  border="0"  cellspacing="1"  width="100%">
<form method="post" action="<?=$this->base_url('linkpage/type_actions')?>">
	<tr >
		<td class="main_td" colspan="6" align="left">&nbsp;<font color="#990000"><b>批量添加</b></font></td>
	</tr>
	<tr  class="tr2">
		<td class="main_td1" align="center">联动类型名称</td>
		<td class="main_td1" align="center">联动类型标识名</td>
		<td class="main_td1" align="center" >排序</td>
	</tr>
	<tr >
		<td class="main_td1" align="center"><input type="text"  name="name[]" size="40"/></td>
		<td class="main_td1" align="center"><input type="text" name="code[]" size="40"/></td>
		<td class="main_td1" align="center" ><input type="text" name="showorder[]" value="10" size="5" /></td>
	</tr>
	<tr >
		<td class="main_td1" align="center"><input type="text"  name="name[]" size="40"/></td>
		<td class="main_td1" align="center"><input type="text" name="code[]" size="40"/></td>
		<td class="main_td1" align="center" ><input type="text" name="showorder[]"  value="10"size="5" /></td>
	</tr>
	<tr >
		<td class="main_td1" align="center"><input type="text"  name="name[]" size="40"/></td>
		<td class="main_td1" align="center"><input type="text" name="code[]" size="40"/></td>
		<td class="main_td1" align="center" ><input type="text" name="showorder[]" value="10" size="5" /></td>
	</tr>
	<tr >
		<td class="main_td1" align="center"><input type="text"  name="name[]" size="40"/></td>
		<td class="main_td1" align="center"><input type="text" name="code[]" size="40"/></td>
		<td class="main_td1" align="center" ><input type="text" name="showorder[]" value="10" size="5" /></td>
	</tr>
	<tr >
		<td class="main_td1" align="center"><input type="text"  name="name[]" size="40"/></td>
		<td class="main_td1" align="center"><input type="text" name="code[]" size="40"/></td>
		<td class="main_td1" align="center" ><input type="text" name="showorder[]" value="10" size="5" /></td>
	</tr>
	<tr >
		<td class="main_td1" align="center"><input type="text"  name="name[]" size="40"/></td>
		<td class="main_td1" align="center"><input type="text" name="code[]" size="40"/></td>
		<td class="main_td1" align="center" ><input type="text" name="showorder[]" value="10" size="5" /></td>
	</tr>
	<tr >
		<td class="main_td1" align="center"><input type="text"  name="name[]" size="40"/></td>
		<td class="main_td1" align="center"><input type="text" name="code[]" size="40"/></td>
		<td class="main_td1" align="center" ><input type="text" name="showorder[]"  value="10"size="5" /></td>
	</tr>
	<tr >
		<td class="main_td1" align="center"><input type="text"  name="name[]" size="40"/></td>
		<td class="main_td1" align="center"><input type="text" name="code[]" size="40"/></td>
		<td class="main_td1" align="center" ><input type="text" name="showorder[]" value="10" size="5" /></td>
	</tr>
	
<tr >
	<td colspan="6"  class="submit" align="center">
		<input type="submit" class="but3" value="添加">
	</td>
</tr>
</form>	
</table>
<? }
}elseif($this->func=='linklist'){?>
<div class="main_title">
    <span>联动管理</span>管理
    <?=$this->anchor('linkpage','列表','class="but1"');?>
</div>
<table border="0" class="flexme" width="100%">
<form method="post">
<thead class="theadtd">
<tr>
    	<th >ID</th>
		<th >联动类型</th>
    	<th >联动名称</th>
		<th >联动值(key)</th>
        <th >排序</th>
		<th >添加时间</th>
        <th >操作</th>
    </tr>
</thead>
<tbody>
    <? foreach($list as $row):?>
    <tr onmouseover="this.bgColor='#ECFBFF'" onmouseout="this.bgColor='#ffffff'" align="center">
        <td><?=$row['id']?><input type="hidden" value="<?=$row['id']?>" name="id[]" /></td>
        <td><?=$typename?></td>
        <td><input type="text" value="<?=$row['name']?>" name="name[]" size="40"/></td>
		<td><input type="text" value="<?=$row['value']?>" name="value[]" size="40"/></td>
		<td><input type="text" value="<?=$row['showorder']?>" name="showorder[]" size="6"/></td>
        <td><?=$row['createdate']?></td>
        <td>
        <? 
			$arr=array(
				'onclick'=>"return confirm('确定要删除吗？')"
			);
			echo $this->anchor("linkpage/link_drop/?id={$row['id']}&typeid={$_GET['id']}",'删除',$arr);
		    ?>
        </td>
    </tr>
    <? endforeach?>
	<tr >
	<td colspan="7"  class="submit" align="center">
		<input type="hidden" value="{$_G.row.id}" name="type" />
		<input type="submit" class="but3" value="修改" />
	</td>
</tr>
</tbody>
</form>
</table>
<? echo $page;?>
<div><font color="#990000"><b>添加(<?=$typename?>)分类下的联动</b></font></div>
<form method="post" action="<?=$this->base_url('linkpage/link_add')?>">  

<table cellpadding="4">
<tr><td>所属类别：</td><td><?=$typename?>
<input class="bkboxinput" type="hidden" name="typeid" value="<?=$_GET['id']?>" size="30">
</td></tr>
<tr><td>联动名称：</td><td><input class="bkboxinput" type="text" name="name">
</td></tr>
<tr><td>联动值：</td><td><input class="bkboxinput" type="text" name="value" size="30">
</td></tr>
<tr><td>排序：</td><td>
<input class="bkboxinput" type="text" name="showorder" value="10"> 
</td></tr>

<tr><td></td><td><input type="submit" class="but3" value="保存">
 <input type="button" class="but3" value="返回" onclick="window.history.go(-1)"/></td></tr>
</table>
</form>


<table  border="0"  cellspacing="1"  width="100%">
<form method="post" action="<?=$this->base_url('linkpage/link_action')?>">
	<tr >
		<td class="main_td" colspan="6" align="left">&nbsp;<font color="#990000"><b>批量添加</b></font></td>
	</tr>
	<tr  class="tr2">
		<td class="main_td1" align="center">联动名称</td>
		<td class="main_td1" align="center">联动值</td>
		<td class="main_td1" align="center" >排序</td>
	</tr>
	<tr >
		<td class="main_td1" align="center"><input type="text"  name="name[]" size="40"/></td>
		<td class="main_td1" align="center"><input type="text" name="value[]" size="40"/></td>
		<td class="main_td1" align="center" ><input type="text" name="showorder[]" value="10" size="5" /></td>
	</tr>
	<tr >
		<td class="main_td1" align="center"><input type="text"  name="name[]" size="40"/></td>
		<td class="main_td1" align="center"><input type="text" name="value[]" size="40"/></td>
		<td class="main_td1" align="center" ><input type="text" name="showorder[]"  value="10"size="5" /></td>
	</tr>
	<tr >
		<td class="main_td1" align="center"><input type="text"  name="name[]" size="40"/></td>
		<td class="main_td1" align="center"><input type="text" name="value[]" size="40"/></td>
		<td class="main_td1" align="center" ><input type="text" name="showorder[]" value="10" size="5" /></td>
	</tr>
	<tr >
		<td class="main_td1" align="center"><input type="text"  name="name[]" size="40"/></td>
		<td class="main_td1" align="center"><input type="text" name="value[]" size="40"/></td>
		<td class="main_td1" align="center" ><input type="text" name="showorder[]" value="10" size="5" /></td>
	</tr>
	<tr >
		<td class="main_td1" align="center"><input type="text"  name="name[]" size="40"/></td>
		<td class="main_td1" align="center"><input type="text" name="value[]" size="40"/></td>
		<td class="main_td1" align="center" ><input type="text" name="showorder[]" value="10" size="5" /></td>
	</tr>
	<tr >
		<td class="main_td1" align="center"><input type="text"  name="name[]" size="40"/></td>
		<td class="main_td1" align="center"><input type="text" name="value[]" size="40"/></td>
		<td class="main_td1" align="center" ><input type="text" name="showorder[]" value="10" size="5" /></td>
	</tr>
	<tr >
		<td class="main_td1" align="center"><input type="text"  name="name[]" size="40"/></td>
		<td class="main_td1" align="center"><input type="text" name="value[]" size="40"/></td>
		<td class="main_td1" align="center" ><input type="text" name="showorder[]"  value="10"size="5" /></td>
	</tr>
	<tr >
		<td class="main_td1" align="center"><input type="text"  name="name[]" size="40"/></td>
		<td class="main_td1" align="center"><input type="text" name="value[]" size="40"/></td>
		<td class="main_td1" align="center" ><input type="text" name="showorder[]" value="10" size="5" /></td>
	</tr>
	<input type="hidden" name="typeid" value="<?=$_GET['id']?>" />
<tr >
	<td colspan="6"  class="submit" align="center">
		<input type="submit" class="but3" value="添加">
	</td>
</tr>
</form>	
</table>
<? }?>
<?php require 'footer.php';?>