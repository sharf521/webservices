<?php
require 'header.php';
if($this->func=='index')
{
?>
	<div class="main_title">
    	<span>用户类型管理</span>列表<?=$this->anchor('usertype/add','新增','class="but1"');?>
    </div>
        <table class="table">
        	<tr class="bt">
            	<th>ID</th>
                <th>名称</th>
                <th>描述</th>
                <th>添加时间</th>
                <th>是否管理员</th>
                <th>操作</th>
            </tr>
            <?
			$arr1=array('否','是');
            foreach($result as $row)
			{
			?>
            <tr>
            	<td><?=$row['id']?></td>
                <td><?=$row['name']?></td>
                <td><?=$row['desc']?></td>
                <td><?=$row['addtime']?></td>
                <td><?=$arr1[$row["is_admin"]]?></td>
                <td>
				<? 
				if($row['id']=="2")
				{
					echo "超级管理员禁止操作";
				}
				else
				{
					echo $this->anchor('usertype/edit/?id='.$row['id'],'编辑');
					echo '&nbsp;|&nbsp;';
					$arr=array(
						'onclick'=>"return confirm('确定要删除吗？')"
					);
					echo $this->anchor('usertype/delete/?id='.$row['id'],'删除',$arr); 
				}
				?>
                    </td>
            </tr>
            <? }?>
        </table>
<?
}
elseif($this->func=='add'||$this->func=='edit')
{
?>
    <div class="main_title">
        <span>用户类型管理</span><? if($this->func=='add'){?>新增<? }else{ ?>编辑<? }?>
		<?=$this->anchor('usertype','列表','class="but1"');?>
    </div>
    <form method="post">
    	<input type="hidden" name="id" value="<?=$row['id']?>"/>
    	<div class="form1">
            <ul>
                <li><label>名称：</label><input type="text" name="name" value="<?=$row['name']?>"/><span></span></li>
                <li><label>描述：</label><input type="text" name="desc" value="<?=$row['desc']?>"/><span></span></li>
                <li><label>管理员：</label>
                <input type="radio" name="is_admin" value="0" checked="checked"/>非管理员
                <input type="radio" name="is_admin" value="1" <? if($row['is_admin']=='1'){echo 'checked';}?>/>管理员<span></span></li>
                <li><label>权限：</label>
                <table border='1' cellpadding="4" cellspacing="1">
				<? 
                foreach($permission as $per)
                {
                    ?>
                    <tr><td >
                    <input type="checkbox" name="menu[]"
                    <? if(in_array($per['id'],$permission_id['menu'])){echo ' checked ';}?>
                     value="<?=$per['id']?>"/><?=$per['name']?>   </td><td>
                    
                    <?
                    if(!empty($per['son']))
                    {
                        foreach($per['son'] as $per1)
                        {
                            ?>
                            <input type="checkbox" name="submenu[]"
                            <? if(in_array($per1['id'],$permission_id['submenu'])){echo ' checked ';}?>
                             value="<?=$per1['id']?>"/><?=$per1['name']?><br />&nbsp;&nbsp;&nbsp;
                            
                            <?
                            $funcs=explode("\r\n",trim($per1['cmvalue']));
                            foreach($funcs as $fun)
                            {
                                if($fun!='')
                                {
                                    $_t=explode(':',$fun);
                                    ?>
                                    &nbsp;&nbsp;&nbsp;<input type="checkbox" name="func[]"
                                    <? if(in_array($_t[1],$permission_id['func'])){echo ' checked ';}?>
                                     value="<?=$_t[1]?>"/><?=$_t[0]?>
                                    <?
                                }
                            }
                            echo '<hr>';
                        }
                    }
                    echo '</td></tr>';	
                }
                
                ?>
                </table>
                
                </li>
            </ul>
            <input type="submit" class="but3" value="保存" />
            <input type="button" class="but3" value="返回" onclick="window.history.go(-1)"/>
        </div>
    </form>
<?
}
require 'footer.php';