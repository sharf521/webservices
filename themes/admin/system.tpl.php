<?php require 'header.php';?>
<?
if($this->func=='index')
{
?>
    <div class="main_title">
    	<span>系统参数</span>列表<?=$this->anchor('system/add','新增','class="but1"');?>
    </div>
    <form method="post">	
    <table class="table">
        <tr class="bt">
            <th>ID</th>
            <th>标识</th>        
            <th>名称</th>
            <th>参数值</th>
            <th>排序</th>
            <th>类型</th>
            <th>操作</th>                                
        </tr>    
    <?php foreach($result as $row):?>
        <tr>
            <td><?=$row['id']?></td>
            <td><?=$row['code']?></td>        
            <td><?=$row['name']?></td>
            <td align="left">
            <? if($row['style']==1){?>
            <input type="text" name="value[]" value="<?=$row['value']?>" size="61">
            <? }elseif($row['style']==2){?>
            <textarea name="value[]" cols="60" rows="5"><?=$row['value']?></textarea>
            <? }elseif($row['style']==3){?>
            <input type="radio" name="value[]" value="1"/> 是 <input type="radio" name="value[]"  value="0"/> 否
            <? }?>
            </td>
            <td>
            <input type="text" value="<?=$row['showorder']?>" name="showorder[]" size="5">
            <input type="hidden" name="id[]" value="<?=$row['id']?>">
            </td>
            <td><?=$row['style']?></td>
            <td>
            <? 
			echo $this->anchor('system/edit/?id='.$row['id'],'编辑')." | ";
			$arr=array(
				'onclick'=>"return confirm('确定要删除吗？')"
			);
			echo $this->anchor('system/delete/?id='.$row['id'],'删除',$arr);
		    ?>
			</td>                                
        </tr> 
    <?php endforeach?>
    </table>
    <div align="center"><input type="submit" value="确认修改" class="but3" /></div>
    </form>
    <?
}
elseif($this->func =='add' || $this->func =='edit')
{
	?>
    <div class="main_title">
        <span>系统参数</span><? if($this->func=='add'){?>新增<? }else{ ?>编辑<? }?>
		<?=$this->anchor('system','列表','class="but1"');?>
    </div>
    <form method="post">	
	    <input type="hidden" name="id" value="<?=$row['id']?>" />
    	<div class="form1">
            <ul>
                <? if($this->func =='edit'){?>
                <li><label>ID：</label><?=$row['id']?><input type="hidden" name="id" value="<?=$row['id']?>"/></li><? }?>
                <li><label>标识：</label><input type="text" name="code" value="<?=$row['code']?>"/></li>
                <li><label>名称：</label><input type="text" name="name" value="<?=$row['name']?>"/></li>
                <li><label>参数值：</label><input type="text" name="value" value="<?=$row['value']?>"/></li>
                <li><label>排序：</label><input type="text" name="showorder" value="<?=$row['showorder']?>"/></li>
                <li><label>类型：</label>
                <select name="style">
                	<option value="1" selected="selected">文本框</option>
                    <option value="2" <? if($row['style']==2){echo 'selected';}?>>多行文本框</option>
                    <option value="3" <? if($row['style']==3){echo 'selected';}?>>单选</option>
                </select></li>
            </ul>
            <input type="submit" class="but3" value="保存" />
            <input type="button" class="but3" value="返回" onclick="window.history.go(-1)"/>
        </div>
    </form>
    
    <?
}
?>
<?php require 'footer.php';?>