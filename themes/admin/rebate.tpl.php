<?php
require 'header.php';
if($this->func=='index')
{
    ?>
    <div class="main_title">
        <span>对列管理</span>列表<?=$this->anchor('rebate/add','新增','class="but1"');?>
        <?=$this->anchor('rebate/calRebate','计算天天返|分红','class="but1"')?>
        <?=$this->anchor('rebate/cal2','计算排队','class="but1"')?>
        <?=$this->anchor('rebate/cal3','计算排队30倍返','class="but1"')?>
    </div>
    <form method="get">
        <div class="search">
            类型：
            <select name="typeid">
                <option value=""<? if($_GET['typeid']==""){?> selected="selected"<? }?>>请选择</option>
                <option value="1"<? if($_GET['typeid']==1){?> selected="selected"<? }?>>16%</option>
                <option value="2"<? if($_GET['typeid']==2){?> selected="selected"<? }?>>15%</option>
                <option value="3"<? if($_GET['typeid']==3){?> selected="selected"<? }?>>31%</option>
            </select>&nbsp;&nbsp;
            状态：<select name="status">
                <option value="" <? if($_GET['status']==""){?> selected="selected"<? }?>>请选择</option>
                <option value="0" <? if($_GET['status']==="0"){?> selected="selected"<? }?>>未开始</option>
                <option value="1"<? if($_GET['status']==1){?> selected="selected"<? }?>>正常</option>
                <option value="2"<? if($_GET['status']==2){?> selected="selected"<? }?>>己结束</option>
            </select>&nbsp;&nbsp;
            用户ID<input type="text" name="user_id" value="<?=$_GET['user_id']?>">
            <input type="submit" class="but2" value="查询" />
        </div>
    </form>
    <table class="table">
        <tr class="bt">
            <th>ID</th>
            <th>用户ID</th>
            <th>对列类型</th>
            <th>应返金额</th>
            <th>己返金额</th>
            <th>添加时间</th>
            <th>状态</th>
            <th>完成时间</th>
            <th>操作</th>
        </tr>
        <?
        $arr_typeid=array('','16%','15%','31%');
        $arr_status=array('未开始','正常','己结束');
        foreach($result['list'] as $row)
        {
            ?>
            <tr>
                <td><?=$row['id']?></td>
                <td><?=$row['user_id']?></td>
                <td><?=$arr_typeid[$row["typeid"]]?></td>
                <td><?=(float)$row['money']?></td>
                <td><?=(float)$row['money_rebate']?></td>
                <td><?=$row['addtime']?></td>
                <td><?=$arr_status[$row["status"]]?></td>
                <td><?=$row["success_time"]?></td>
                <td>
                    <?
                    //echo $this->anchor('rebate/edit/?id='.$row['id'],'编辑');
                    echo '&nbsp;|&nbsp;';
                    $arr=array(
                        'onclick'=>"return confirm('确定要删除吗？')"
                    );
                    //echo $this->anchor('rebate/delete/?id='.$row['id'],'删除',$arr);
                    ?>
                </td>
            </tr>
        <? }?>
    </table>
    <? if(empty($result['total'])){echo "无记录！";}else{echo $result['page'];}?>
<?
}
elseif($this->func=='add'||$this->func=='edit')
{
    ?>
    <div class="main_title">
        <span>对列管理</span><? if($this->func=='add'){?>新增<? }else{ ?>编辑<? }?>
        <?=$this->anchor('usertype','列表','class="but1"');?>
    </div>
    <form method="post">
        <input type="hidden" name="id" value="<?=$row['id']?>"/>
        <div class="form1">
            <ul>
                <li><label>用户id：</label><input type="text" name="user_id" value="<?=$row['user_id']?>"/><span></span></li>
                <li><label>应返金额：</label><input type="text" name="money" value="<?=$row['money']?>"/><span></span></li>
                <li><label>队列类型：</label><select name="typeid">
                        <option value="1">16%</option>
                        <option value="2">15%</option>
                        <option value="3">31%</option>
                    </select><span></span></li>
            </ul>
            <input type="submit" class="but3" value="保存" />
            <input type="button" class="but3" value="返回" onclick="window.history.go(-1)"/>
        </div>
    </form>
<?
}
elseif($this->func=='rebatelist'){
    ?>
    <div class="main_title">
        <span>对列排队位置</span>列表
    </div>
    <form method="get">
        <div class="search">
            队列类型：
            <select name="typeid">
                <option value=""<? if($_GET['typeid']==""){?> selected="selected"<? }?>>请选择</option>
                <option value="1"<? if($_GET['typeid']==1){?> selected="selected"<? }?>>500队列</option>
                <option value="2"<? if($_GET['typeid']==2){?> selected="selected"<? }?>>100队列</option>
            </select>&nbsp;&nbsp;
            用户ID<input type="text" name="user_id" value="<?=$_GET['user_id']?>">
            <input type="submit" class="but2" value="查询" />
        </div>
    </form>
    <table class="table">
        <tr class="bt">
            <th>ID</th>
            <th>用户ID</th>
            <th>队列ID</th>
            <th>应返金额</th>
            <th>己返金额</th>
            <th>对列类型</th>
            <th>待返个数</th>
            <th>开始位置</th>
            <th>结束位置</th>
            <th>添加时间</th>
            <th>结余个数</th>
            <th>结余金额</th>
            <th>状态</th>
            <th>完成时间</th>
        </tr>
        <?
        $arr_typeid=array('','500队列','100队列');
        $arr_status=array('0','正常','己结束');
        foreach($result['list'] as $row)
        {
            ?>
            <tr>
                <td><?=$row['id']?></td>
                <td><?=$row['user_id']?></td>
                <td><?=$row['rebate_id']?></td>
                <td><?=(float)$row['money']?></td>
                <td><?=(float)$row['money_rebate']?></td>
                <td><?=$arr_typeid[$row["typeid"]]?></td>
                <td><?=$row['position_quantity']?></td>
                <td><?=$row['position_start']?></td>
                <td><?=$row['position_end']?></td>
                <td><?=$row['addtime']?></td>
                <td><?=$row['position_last']?></td>
                <td><?=floatval($row['money_last'])?></td>
                <td><?=$arr_status[$row["status"]]?></td>
                <td><?=$row["success_time"]?></td>
            </tr>
        <? }?>
    </table>
    <? if(empty($result['total'])){echo "无记录！";}else{echo $result['page'];}?>
    <?
}elseif($this->func=='rebatelog'){

    $arr_typeid=array(
        '1,1,1,'=>'16天天返',
        '1,1,3,'=>'31天天返',
        '1,2,1,'=>'16平台分红',
        '1,2,2,'=>'15平台分红',
        '1,3,1,1,'=>'排队：500排队',
        '1,3,1,2,'=>'排队：100排队',
        '1,3,2,1,'=>'排队：60整倍返',
        '1,3,2,2,'=>'排队：70整倍返',
        '1,3,3,'=>'排队：30倍返',
    );
    ?>
    <div class="main_title">
        <span>对列收益流水</span>列表
    </div>
    <form method="get">
        <div class="search">
            类型：
            <select name="typeid">
                <option value=""<? if($_GET['typeid']==""){?> selected="selected"<? }?>>请选择</option>
                <?
                foreach($arr_typeid as $i=>$v){
                    ?>
                    <option value="<?=$i?>" <? if($_GET['typeid']==$i){?> selected="selected"<? }?>><?=$v?></option>
                    <?
                }
                ?>
            </select>&nbsp;&nbsp;
            金额：<input type="text" size="10" name="money" value="<?=$_GET['money']?>">&nbsp;&nbsp;
            用户ID：<input type="text" size="10" name="user_id" value="<?=$_GET['user_id']?>">&nbsp;&nbsp;
            队列ID：<input type="text" size="10" name="rebate_id" value="<?=$_GET['rebate_id']?>">
            <input type="submit" class="but2" value="查询" />
        </div>
    </form>
    <table class="table">
        <tr class="bt">
            <th>ID</th>
            <th>用户ID</th>
            <th>队列ID</th>
            <th>金额</th>
            <th>类型</th>
            <th>触发队列id</th>
            <th>返还队列id</th>
            <th>添加时间</th>
        </tr>
        <?

        foreach($result['list'] as $row)
        {
            ?>
            <tr>
                <td><?=$row['id']?></td>
                <td><?=$row['user_id']?></td>
                <td><?=$row['rebate_id']?></td>
                <td><?=(float)$row['money']?></td>
                <td><?=$arr_typeid[$row["typeid"]]?></td>
                <td><?=$row['rebate_list_in']!=0?$row['rebate_list_in']:''?></td>
                <td><?=$row['rebate_list_out']!=0?$row['rebate_list_out']:''?></td>
                <td><?=$row['addtime']?></td>
            </tr>
        <? }?>
    </table>
    <? if(empty($result['total'])){echo "无记录！";}else{echo $result['page'];}?>
<?
}
require 'footer.php';