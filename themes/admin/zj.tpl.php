<?php
require 'header.php';
if($this->func=='index')
{
    ?>
    <div class="main_title">
        <span>增进管理</span>列表<?=$this->anchor('zj/add','新增','class="but1"');?>
        <?=$this->anchor('zj/calZj','计算','class="but1"')?>
        <?=$this->anchor('zj/calAdd1000','添加10个','class="but1"')?>
    </div>

    <form method="get">
        <div class="search">
            用户ID：<input type="text" size="10" name="user_id" value="<?=$_GET['user_id']?>">&nbsp;&nbsp;
            增进ID：<input type="text" size="10" name="id" value="<?=$_GET['id']?>">&nbsp;&nbsp;
            盘数：<input type="text" size="10" name="plate" value="<?=$_GET['plate']?>">&nbsp;&nbsp;
            <input type="submit" class="but2" value="查询" />
        </div>
    </form>
    <table class="table">
        <tr class="bt">
            <th>ID</th>
            <th>用户ID</th>
            <th>金额</th>
            <th>收入</th>
            <th>上层id</th>
            <th>路径</th>
            <th>盘数</th>
            <th>位置</th>
            <th>25天计划</th>
            <th>25天发放</th>
            <th>提前发放</th>
            <th>状态</th>
            <th>添加时间</th>
        </tr>
        <?
        $arr_status=array('未计算','正常','己滑落');
        foreach($result['list'] as $row)
        {
            ?>
            <tr>
                <td><?=$row['id']?></td>
                <td><?=$row['user_id']?></td>
                <td><?=(float)$row['money']?></td>

                <td><?=$row['income']?></td>
                <td><?=$row['pid']?></td>
                <td class="l"><?=str_replace(',','->',rtrim($row['pids'],','))?></td>
                <td><?=$row['plate']?></td>
                <td><?=$row['index']?></td>
                <td><?=$row['dayplan']?></td>
                <td>￥<?=(float)$row['dayplan_income']?></td>
                <td>￥<?=(float)$row['dayplan_last']?></td>
                <td><?=$arr_status[$row["status"]]?></td>
                <td><?=$row['addtime']?></td>
            </tr>
        <? }?>
    </table>
    <? if(empty($result['total'])){echo "无记录！";}else{echo $result['page'];}?>

    <?
    if ((int)$_GET['plate']>0) {
        ?>
        <script>
            mxBasePath = '/themes/admin/js/mxgraph/src';
        </script>
        <script src="/themes/admin/js/mxgraph/src/js/mxClient.js"></script>
        <script src="/themes/admin/js/zj.js"></script>
        <script>
            $(document).ready(function () {
                main(<?=(int)$_GET['user_id']?>, <?=(int)$_GET['id']?>, <?=(int)$_GET['plate']?>);
            });
        </script>
    <?
    }
    ?>


    <div><div class="drawContent" id="drawContent"></div></div>

<?
}
elseif($this->func=='add'||$this->func=='edit')
{
    ?>
    <div class="main_title">
        <span>增进管理</span><? if($this->func=='add'){?>新增<? }else{ ?>编辑<? }?>
        <?=$this->anchor('usertype','列表','class="but1"');?>
    </div>
    <form method="post">
        <input type="hidden" name="id" value="<?=$row['id']?>"/>
        <div class="form1">
            <ul>
                <li><label>用户id：</label><input type="text" name="user_id" value="<?=$row['user_id']?>"/><span></span></li>
            </ul>
            <input type="submit" class="but3" value="保存" />
            <input type="button" class="but3" value="返回" onclick="window.history.go(-1)"/>
        </div>
    </form>
<?
}elseif($this->func=='zjlog'){
    $arr_typeid=array(
        '3,1,'=>'T1',
        '3,2,'=>'T2',
        '3,3,'=>'滑落',
        '3,4,'=>'25天计划',
    );
    ?>
    <div class="main_title">
        <span>对列收益流水</span>列表
    </div>
    <form method="get">
        <div class="search">
            金额：<input type="text" size="10" name="money" value="<?=$_GET['money']?>">&nbsp;&nbsp;
            用户ID：<input type="text" size="10" name="user_id" value="<?=$_GET['user_id']?>">&nbsp;&nbsp;
            增进ID：<input type="text" size="10" name="zj_id" value="<?=$_GET['zj_id']?>">&nbsp;&nbsp;
            进入增进ID：<input type="text" size="10" name="in_zj_id" value="<?=$_GET['in_zj_id']?>">&nbsp;&nbsp;
            盘数：<input type="text" size="10" name="plate" value="<?=$_GET['plate']?>">&nbsp;&nbsp;
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
            <input type="submit" class="but2" value="查询" />
        </div>
    </form>
    <table class="table">
        <tr class="bt">
            <th>ID</th>
            <th>zj_id/用户ID</th>
            <th>进入zj_id/进入用户ID</th>
            <th>金额</th>
            <th>盘数</th>
            <th>类型</th>
            <th>添加时间</th>
        </tr>
        <?
        foreach($result['list'] as $row)
        {
            ?>
            <tr>
                <td><?=$row['id']?></td>
                <td><?=$row['zj_id']?>/<?=$row['user_id']?></td>
                <td><?=$row['in_user_id']?>/<?=$row['in_zj_id']?></td>
                <td><?=(float)$row['money']?></td>
                <td><?=$row['plate']?></td>
                <td><?=$arr_typeid[$row["typeid"]]?></td>
                <td><?=$row['addtime']?></td>
            </tr>
        <? }?>
    </table>
    <? if(empty($result['total'])){echo "无记录！";}else{echo $result['page'];}?>
<?
}
require 'footer.php';