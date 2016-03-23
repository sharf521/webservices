<?php require 'header.php';?>
<?
if($this->func=='index')
{
?>
    <div class="main_title">
    	<span>菜单管理</span>列表<?=$this->anchor('permission/add/?pid=0','新增','class="but1"');?>
    </div>
    <form method="post">	
    <table class="table">
        <tr class="bt">
            <th>ID</th>
            <th>PID</th>        
            <th>权限名称</th>
            <th>权限描述</th>
            <th>URL</th>
            <th>排序</th>
            <th>操作</th>                                
        </tr>    
    <?php foreach($result as $row):?>
        <tr <?php if($row['pid']==0) echo "bgcolor='#4cd1fc'"?> >
            <td><?=$row['id']?></td>
            <td><?=$row['pid']?></td>        
            <td><?=$row['name']?></td>
            <td><?=$row['desc']?></td>
            <td><?=$row['url']?></td>
            <td>
            <input type="text" value="<?=$row['order']?>" name="order[]" size="5">
            <input type="hidden" name="id[]" value="<?=$row['id']?>">
            </td>
            <td>
            <? 
			echo $this->anchor("permission/add/?pid={$row['id']}","添加子菜单")." | ";
			echo $this->anchor('permission/edit/?id='.$row['id'],'编辑')." | ";
			$arr=array(
				'onclick'=>"return confirm('确定要删除吗？')"
			);
			echo $this->anchor('permission/delete/?id='.$row['id'],'删除',$arr);
		    ?>
			</td>                                
        </tr> 
        <?
        if(!empty($row['son']))
		{
			foreach($row['son'] as $row1)
			{
			?>
                <tr>
                    <td><?=$row1['id']?></td>
                    <td><?=$row1['pid']?></td>        
                    <td><?=$row1['name']?></td>
                    <td><?=$row1['desc']?></td>
                    <td><?=$row1['url']?></td>
                    <td>
                    <input type="text" value="<?=$row1['order']?>" name="order[]" size="5">
                    <input type="hidden" name="id[]" value="<?=$row1['id']?>">
                    </td>
                    <td>
        			<?
						echo $this->anchor("permission/edit/?id={$row1['id']}","编辑")." | ";
						$arr=array(
				          'onclick'=>"return confirm('确定要删除吗？')"
			            );
						echo $this->anchor("permission/delete/?id={$row1['id']}","删除",$arr);
					?></td>                                
                </tr>
            <?
			}
		}
		?>       
    <?php endforeach?>
    </table>
    <div align="center"><input type="submit" value="更新排序" class="but3" /></div>
    </form>
    <?
}
elseif($this->func =='add' || $this->func =='edit')
{
	?>
    <div class="main_title">
        <span>菜单管理</span><? if($this->func=='add'){?>新增<? }else{ ?>编辑<? }?>
		<?=$this->anchor('permission','列表','class="but1"');?>
    </div>
    <form method="post">	
	    <input type="hidden" name="id" value="<?=$row['id']?>" />
    	<div class="form1">
            <ul>
                <? if($this->func =='add'){?>
                <li><label>PID：</label><?=$_GET['pid']?><input type="hidden" name="pid" value="<?=$_GET['pid']?>"/></li><? }else{?>
                <li><label>PID：</label><?=$row['pid']?><input type="hidden" name="pid" value="<?=$row['pid']?>"/></li><? }?>
                <li><label>权限名称：</label><input type="text" name="name" value="<?=$row['name']?>"/></li>
                <li><label>权限描述：</label><input type="text" name="desc" value="<?=$row['desc']?>"/></li>
                <li><label>URL：</label><input type="text" name="url" value="<?=$row['url']?>"/></li>
                <li><label>控制器：</label><textarea name="cmvalue" cols="50" rows="10"><?=$row['cmvalue']?></textarea></li>
                <li><label>排序：</label><input type="text" name="order" value="<?=$row['order']?>"/></li>
            </ul>
            <input type="submit" class="but3" value="保存" />
            <input type="button" class="but3" value="返回" onclick="window.history.go(-1)"/>
        </div>
    </form>
    <?
}
?>
<?php require 'footer.php';?>