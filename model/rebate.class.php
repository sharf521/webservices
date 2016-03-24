<?php
/*
 * bcadd — 加法
bccomp — 比较
bcdiv — 相除
bcmod — 求余数
bcmul — 乘法
bcpow — 次方
bcpowmod — 先次方然后求余数
bcscale — 给所有函数设置小数位精度
bcsqrt — 求平方根
bcsub — 减法


结束队列。有问题。
 * */
class rebateClass extends Model
{
    public function __construct()
    {
        ini_set("max_execution_time", "1800000");
        ini_set('default_socket_timeout',600000);
        parent::__construct();
        $this->table=$this->dbfix.'rebate';
        $this->fields=array('id','site_id','user_id','typeid','money','addtime','status','money_rebate','success_time');
        /**
         * [rebate_date] => 2016-02-04
        [rabate1_dividend_ratio] => 0.005
        [rabate1_dividend_equity] => 500
        [rabate2_dividend_ratio] => 0.05
        [rabate2_dividend_equity] => 120
        [rebate_probability] => 0
         */
        $config_result=$this->mysql->get_all("select k,v from {$this->dbfix}rebate_config");
        $arr=array();
        foreach($config_result as $c){
            $arr[$c['k']]=$c['v'];
        }
        $this->config=$arr;
    }

    function addRebate($data)
    {
        if(empty($data['user_id']) || empty($data['typeid']) || empty($data['money'])){
            $return=array('code'=>0,'msg'=>'参数错误！');
        }
        else{
            $arr=array(
                'site_id' => 1,
                'typeid' => (int)$data['typeid'],
                'user_id' => (int)$data['user_id'],
                'money' => (float)($data['money']),
                'addtime' => date('Y-m-d H:i:s'),
                'status' => 0,
                'money_rebate' =>0
            );
            $result=$this->mysql->insert("rebate",$arr);
            $user=$this->mysql->one('rebate_user',array('user_id'=>$arr['user_id']));
            if(!$user){
                $this->mysql->insert('rebate_user',array('user_id'=>$arr['user_id'],'money_last'=>0,'money_30time'=>0));
            }
            if($result==true){
                $return=array('code'=>200,'msg'=>'ok');
            }
            else{
                $return=array('code'=>0,'msg'=>'内部错误');
            }
        }
        return json_encode($return);
    }
    //计算
    function calRebate(){
        //global $_G;
        //$rebate_date=$_G['system']['rebate_date'];
        $rebate_date=$this->config['rebate_date'];
        $today=date('Y-m-d');
        $i=0;
        while($today>$rebate_date && $i<1000) {//最多1000次
            $rebate_date=date('Y-m-d',strtotime($rebate_date)+3600*24);
            try {
                $this->mysql->beginTransaction();

                $this->mysql->update('rebate_config',array('v'=>$rebate_date),"k='rebate_date' limit 1");
                $this->calEveryDay($rebate_date);
                $this->calDividend($rebate_date);

                $this->mysql->commit();
            } catch (Exception $e) {
                $this->mysql->rollBack();
                echo "Failed: " . $e->getMessage();
                return false;
            }
            $i++;
        }
        return true;
    }
    function cal2()
    {
        try {
            $this->mysql->beginTransaction();
            $this->calRebateList();
            $this->mysql->commit();
        } catch (Exception $e) {
            $this->mysql->rollBack();
            echo "Failed: " . $e->getMessage();
            return false;
        }
        return true;
    }
    function cal3()
    {
        try {
            $this->mysql->beginTransaction();
            $this->calRebate_30TimesReturn();
            $this->mysql->commit();
        } catch (Exception $e) {
            $this->mysql->rollBack();
            echo "Failed: " . $e->getMessage();
            return false;
        }
        return true;
    }
    //生成排队位置
    function calRebateList(){
       // $today=date('Y-m-d');
        //$sql="select id,user_id,money,addtime,money_rebate from {$this->dbfix}rebate where status=0 and addtime<'{$rebate_date}'";
        $sql="select id,user_id,money,addtime,money_rebate,typeid from {$this->dbfix}rebate where status=0 ";
        $result=$this->mysql->get_all($sql);
        foreach($result as $row){
            $this->mysql->update('rebate',array('status'=>1),"id={$row['id']} limit 1");//改为己处理
            $user=$this->mysql->one('rebate_user',array('user_id'=>$row['user_id']));
            if($row['typeid']==3){
                $row['money']=$row['money']*2;
            }
            $money=bcadd($row['money'],floatval($user['money_last']),5);
            $nums500=bcdiv($money,500);//500排队个数
            if($nums500>0){
                $money_100=bcsub($money,bcmul($nums500,500),5);// $money-$nums*500 计算排队100的金额  bcmod 结果为整数，所以不能使用
                $this->calRebateListDo($row,$nums500,1,$money_100);
            }
            else{
                $money_100=$money;
            }
            $nums100=bcdiv($money_100,100);//100排队个数
            if($nums100>0) {
                $money_last = bcsub($money_100, bcmul($nums100, 100), 5);
                $this->calRebateListDo($row, $nums100, 2,$money_last);
            }else{
                $money_last=$money_100;
            }
            //更新用户的未排队金额
            $this->mysql->update('rebate_user',array('money_last'=>$money_last),"user_id={$row['user_id']} limit 1");
        }
    }
    /*
     * $quantity:点位数量
     * $typeid: 1:500队，2:100队
     * $money_last:剩余金额--log查看
     * */
    function calRebateListDo($rebate,$quantity,$typeid,$money_last)
    {
        if($typeid==1){
            $position_size=60;
            $position_money=500;
        }else{
            $position_size=70;
            $position_money=100;
        }
        $rebate_last=$this->mysql->get_one("select position_end,position_last from {$this->dbfix}rebate_list where typeid={$typeid} order by id desc limit 1");
        if(!$rebate_last){
            $rebate_last['position_end']=0;
            $rebate_last['position_last']=0;
        }
        $to_quantity=bcdiv($quantity+$rebate_last['position_last'],$position_size);//应返个数()
        $position_last_new=bcmod($quantity+$rebate_last['position_last'],$position_size);//不够60个 剩余的个数
        $rebate_list=array(
            'rebate_id'=>$rebate['id'],
            'user_id'=>$rebate['user_id'],
            'typeid'=>$typeid,
            'addtime'=>date('Y-m-d H:i:s'),
            'position_quantity'=>$quantity,
            'position_start'=>$rebate_last['position_end']+1,
            'position_end'=>$rebate_last['position_end']+$quantity,// 7 位：1--7
            'position_last'=>$position_last_new,
            'money_last'=>$money_last,
            'status'=>1
        );
        $this->mysql->insert('rebate_list',$rebate_list);
        $rebate_list['id']=$this->mysql->insert_id();
        //整数倍返
        $this->calRebate_Just60Return($rebate,$rebate_list);

        //排队奖励
        $sql="select id,rebate_id,user_id,position_quantity  from {$this->dbfix}rebate_list  where typeid={$typeid} and status=1 order by id limit 0,$to_quantity";
        $result=$this->mysql->get_all($sql);
        foreach($result as $rList){
            if($to_quantity>=$rList['position_quantity']){
                $to_quantity-=$rList['position_quantity'];
                $quantity_ying=$rList['position_quantity'];
                //rebate_list 己完成
                $arr=array(
                    'status'=>2,
                    'position_quantity'=>0,
                    'success_time'=>date('Y-m-d H:i:s')
                );
            }else{
                $quantity_ying=$to_quantity;
                $to_quantity=0;
                $arr=array(
                    'position_quantity'=>$rList['position_quantity']-$quantity_ying
                );
            }
            $this->mysql->update('rebate_list',$arr," id={$rList['id']} limit 1");//更新剩余返还位数

            $money_all=bcmul($position_money,$quantity_ying);//500的倍数

            //返还给用户
            $this->rebateMoney($money_all,$rList['user_id'],'1,3,1,'.$typeid.',',array('rebate_list_in'=>$rebate_list['id'],'rebate_list_out'=>$rList['id']));
            if($to_quantity==0){
                break;
            }
        }
    }

    //返还给用户
    /*
     * $money_all:返还的金额
     * $user_id:返还的用户id
     * $typeid:类型 1,3,1,
     * */
    function rebateMoney($money,$user_id,$typeid,$data)
    {
        $date=isset($data['date'])?$data['date']:date('Y-m-d H:i:s');
        $sql="select  id,user_id,money,money_rebate from {$this->dbfix}rebate where user_id={$user_id} and status!=2 order by id";
        $restult=$this->mysql->get_all($sql);
        foreach($restult as $row){
            $rebate_log=array(
                'user_id'=>$row['user_id'],
                'rebate_id'=>$row['id'],
                'rebate_list_in'=>(int)$data['rebate_list_in'],
                'rebate_list_out'=>(int)$data['rebate_list_out'],
                'typeid'=>$typeid,
                'addtime'=>$date
            );
            $arr_rebate=array();
            $money_yu=$row['money']-$row['money_rebate'];
            if($money >= $money_yu){
                $rebate_log['money']=$money_yu;
                $arr_rebate['status']=2;
                $arr_rebate['success_time']=$date;
                $money=$money-$rebate_log['money'];
            }else{
                $rebate_log['money']=$money;
                $money=0;
            }
            if($rebate_log['money']>0) {
                $this->mysql->insert('rebate_log', $rebate_log);//结算日志
            }
            //更新己返还金额
            $arr_rebate['money_rebate']=$row['money_rebate']+$rebate_log['money'];
            $this->mysql->update('rebate',$arr_rebate,"id={$row['id']} limit 1");

            if($money==0){
                break;
            }
        }
        return true;
    }

    //整数倍返  60返1，70返1
    function calRebate_Just60Return($rebate,$rebate_list)
    {
        //整数倍返 概率0 到1
        if(rand(1,100)<=$this->config['rebate_probability']*100){
            if($rebate_list['typeid']==1){
                $position_size=60;
                $position_money=500;
            }else{
                $position_size=70;
                $position_money=100;
            }
            $position_start_nextpos=(bcdiv($rebate_list['position_start'],$position_size)+1) * $position_size;//下一个整位置
            if(bcmod($rebate_list['position_start'],$position_size)==0 || bcmod($rebate_list['position_end'],$position_size)==0 || $position_start_nextpos < $rebate_list['position_end']){
                $times=bcdiv($rebate_list['position_end']-$position_start_nextpos,$position_size)+1;//多少个整位置
                $rebate_money=$position_money;
                $rebate_quantity=1;
                for($i=1;$i<$times;$i++){
                    if(rand(1,100)<=$this->config['rebate_probability']*100){
                        $rebate_money+=$position_money;
                        $rebate_quantity++;
                    }
                }
                $rebate_log=array(
                    'user_id'=>$rebate_list['user_id'],
                    'rebate_id'=>$rebate_list['rebate_id'],
                    'rebate_list_in'=>$rebate_list['id'],
                    'rebate_list_out'=>$rebate_list['id'],
                    'typeid'=>'1,3,2,'.$rebate_list['typeid'].',',
                    'addtime'=>date('Y-m-d H:i:s')
                );
                $arr_rebate=array();
                if($rebate_money >= $rebate['money']){
                    $rebate_log['money']=$rebate['money'];
                    $arr_rebate['status']=2;
                    $arr_rebate['success_time']=date('Y-m-d H:i:s');
                }
                else{
                    $rebate_log['money']=$rebate_money;
                }
                if($rebate_log['money']>0) {
                    $this->mysql->insert('rebate_log', $rebate_log);//结算日志
                }
                //更新己返还金额
                $arr_rebate['money_rebate']=$rebate_log['money'];
                $this->mysql->update('rebate',$arr_rebate,"id={$rebate['id']} limit 1");

                //减少待返位置
                $arr = array(
                    'position_quantity' => $rebate_list['position_quantity'] - $rebate_quantity
                );
                if ($arr == 0) {
                    $arr['status'] =2;
                }
                $this->mysql->update('rebate_list', $arr, " id={$rebate_list['id']} limit 1");//更新剩余返还位数
            }
        }
    }

    //30倍返 12队列和31队列 只判断第一个未完成的记录
    function calRebate_30TimesReturn()
    {
        $day1=date('Y-m-d',time()-3600*24*1);
        $sql="select user_id,sum(money-money_rebate) as money_norebate from {$this->dbfix}rebate  where typeid!=1 and status=1 and addtime<'{$day1} 23:59:59' group by user_id order by null";
        $result=$this->mysql->get_all($sql);
        foreach($result as $row){
            $user_id=$row['user_id'];
            $money_norebate=floatval($row['money_norebate']);
            $sql="select u.money_30time,r.id,r.money,r.money_rebate from {$this->dbfix}rebate_user u left join {$this->dbfix}rebate r on u.user_id=r.user_id
                  where r.typeid!=1 and r.status=1 and r.user_id={$user_id} order by r.id limit 1";
            $rebate=$this->mysql->get_one($sql);
            if($rebate){
                $money_30=bcmul($rebate['money'],30,5);
                if ($money_norebate - $rebate['money_30time'] - $rebate['money'] >= $money_30) {
                    $rebate_log=array(
                        'user_id'=>$user_id,
                        'money'=>$rebate['money'] - $rebate['money_rebate'],
                        'rebate_id'=>$rebate['id'],
                        'typeid'=>"1,3,3,",
                        'addtime'=>date('Y-m-d')
                    );
                    $this->mysql->insert('rebate_log', $rebate_log);//结算日志
                    //更新己返还金额
                    $arr_rebate['status'] = 2;
                    $arr_rebate['success_time'] = date('Y-m-d');
                    $arr_rebate['money_rebate'] = $rebate['money'];
                    $this->mysql->update('rebate', $arr_rebate, "id={$rebate['id']} limit 1");
                    //更新30倍返金额
                    $arr=array('money_30time'=>bcadd($rebate['money_30time'],$money_30,5));
                    $this->mysql->update('rebate_user', $arr, "user_id={$user_id} limit 1");
                }
            }
        }
    }
    //分红  前天的营业额 分前天之前的 包含前天
    function calDividend($rebate_date)
    {
        $rabate1_dividend_ratio=(float)$this->config['rabate1_dividend_ratio'];//16分红比例  0.005
        $rabate1_dividend_equity=(float)$this->config['rabate1_dividend_equity'];//16股权大小 500
        $rabate2_dividend_ratio=(float)$this->config['rabate2_dividend_ratio'];//12分红比例  0.05
        $rabate2_dividend_equity=(float)$this->config['rabate2_dividend_equity'];//12股权大小 120
        $day2=date('Y-m-d',strtotime($rebate_date)-3600*24*2);
        $day1=date('Y-m-d',strtotime($rebate_date)-3600*24*1);
        //16和31队列的16
        $one16_money=0;//16队列每份分红金额
        $sql="select sum(money) as totals from {$this->dbfix}rebate where typeid!=2 and addtime>'{$day2}' and addtime<'{$day1}'";
        $row=$this->mysql->get_one($sql);
        $totals16=(float)$row['totals'];//前天营业额
        if($totals16>0){
            $total=bcmul($totals16,$rabate1_dividend_ratio,5);//分红总金额
            $sql="select user_id,sum(money) as money from {$this->dbfix}rebate where typeid!=2 and status!=2 and money>={$rabate1_dividend_equity} and addtime<'{$day2} 23:59:59' group by user_id order by null";
            $result16=$this->mysql->get_all($sql);
            $nums16=0;//16总份数
            foreach($result16 as $k=>$row){
                $_num=bcdiv($row['money'],$rabate1_dividend_equity);
                $nums16+=$_num;
                $result16[$k]['num16']=$_num;
            }
            if($nums16>0){
                $one16_money=bcdiv($total,$nums16,5);//16队列每份分红金额
            }
        }
        //15队列和31队列
        $one15_money=0;//16队列每份分红金额
        $sql="select sum(money) as totals from {$this->dbfix}rebate where typeid!=1 and addtime>'{$day2}' and addtime<'{$day1}'";
        $row=$this->mysql->get_one($sql);
        $totals15=(float)$row['totals'];//前天营业额
        if($totals15>0){
            $total=bcmul($totals15,$rabate2_dividend_ratio,5);//分红总金额
            $sql="select user_id,sum(money) as money from {$this->dbfix}rebate where typeid!=1 and status!=2 and money>={$rabate2_dividend_equity} and addtime<'{$day2} 23:59:59' group by user_id order by null";
            $result15=$this->mysql->get_all($sql);
            $nums15=0;//15总分数
            foreach($result15 as $k=>$row){
                $_num=bcdiv($row['money'],$rabate2_dividend_equity);
                $nums15+=$_num;
                $result15[$k]['nums15']=$_num;
            }
            if($nums15>0){
                $one15_money=bcdiv($total,$nums15,5);//15队列每份分红金额
            }
        }
        //16 分红 包含31
        if($one16_money>0){
            foreach($result16 as $row){
                $money=bcmul($one16_money,$row['num16'],5);
                $this->rebateMoney($money,$row['user_id'],"1,2,1,",array('date'=>$rebate_date));
            }
        }
        //15 分红 包含31
        if($one15_money>0){
            foreach($result15 as $row){
                $money=bcmul($one15_money,$row['num15'],5);
                $this->rebateMoney($money,$row['user_id'],"1,2,2,",array('date'=>$rebate_date));
            }
        }
    }
    //天天返 今天结算昨天的数据  参数为 今天的日期
    function calEveryDay($rebate_date)
    {
        $day500=date('Y-m-d',strtotime($rebate_date)-3600*24*500);
        $day60=date('Y-m-d',strtotime($rebate_date)-3600*24*60);
        $sql="select id,user_id,typeid,money,addtime,money_rebate from {$this->dbfix}rebate where typeid!=2 and status!=2 and addtime>'{$day500}' and addtime<'{$rebate_date}'";
        $result=$this->mysql->get_all($sql);
        foreach ($result as $row) {
            $rebate_log = array(
                'user_id' => $row['user_id'],
                'rebate_id' => $row['id'],
                'typeid' => '1,1,'.$row['typeid'].',',
                'addtime' => $rebate_date  //应该结算日期的
            );
            if (substr($row['addtime'], 0, 10) >= $day60) {
                $money = bcmul($row['money'], 0.002, 5);
            } else {
                $money = bcdiv(bcmul($row['money'], 0.005, 5), 440, 5);
            }
            $arr_rebate = array();

            if ($money >= $row['money'] - $row['money_rebate']) {
                $rebate_log['money'] = $row['money'] - $row['money_rebate'];
                $arr_rebate['status'] = 2;
                $arr_rebate['success_time'] = $rebate_date;
            } else {
                $rebate_log['money'] = $money;
            }
            if ($rebate_log['money'] > 0) {
                $this->mysql->insert('rebate_log', $rebate_log);//结算日志
            }
            //更新己返还金额
            $arr_rebate['money_rebate'] = $row['money_rebate'] + $rebate_log['money'];
            $this->mysql->update('rebate', $arr_rebate, "id={$row['id']} limit 1");
        }
        return true;
    }

    function getRebateAll(){

    }
    function getRebateByPage($data)
    {
        $_select="r.*";
        $where="where 1=1";
        if(!empty($data['typeid']))
        {
            $where.=" and r.typeid={$data['typeid']}";
        }
        if(!empty($data['user_id']))
        {
            $where.=" and r.user_id={$data['user_id']}";
        }
        if($data['status']!='')
        {
            $where.=" and r.status={$data['status']}";
        }
        $sql = "select SELECT from {$this->dbfix}rebate r left join {$this->dbfix}user u on r.user_id=u.user_id {$where} ORDER LIMIT";

        $_order=isset($data['order'])?' order by '.$data['order']:'order by r.id desc';
        //总条数
        $row=$this->mysql->get_one(str_replace(array('SELECT', 'ORDER', 'LIMIT'), array('count(1) as num', '', ''), $sql));
        $total = $row['num'];

        $epage = empty($data['epage'])?10:$data['epage'];
        $page=$data['page'];
        if(!empty($page))
        {
            $index = $epage * ($page - 1);
        }
        else
        {
            $index=0;$page=1;
        }
        if($index>$total){$index=0;$page=1;}
        $limit = " limit {$index}, {$epage}";
       // echo str_replace(array('SELECT', 'ORDER', 'LIMIT'), array($_select, $_order, $limit), $sql);
        $list = $this->mysql->get_all(str_replace(array('SELECT', 'ORDER', 'LIMIT'), array($_select, $_order, $limit), $sql));
        global $pager;
        $pager->page=$page;
        $pager->epage=$epage;
        $pager->total=$total;
        return array(
            'list' => $list,
            'total' => $total,
            'page' => $pager->show()
        );
    }

    function getRebateListByPage($data)
    {
        $_select="rl.*,r.money,r.money_rebate";
        $where="where 1=1";
        if(!empty($data['typeid']))
        {
            $where.=" and rl.typeid={$data['typeid']}";
        }
        if(!empty($data['user_id']))
        {
            $where.=" and rl.user_id={$data['user_id']}";
        }
        $sql = "select SELECT from {$this->dbfix}rebate_list rl left join {$this->dbfix}rebate r on rl.rebate_id=r.id
 left join {$this->dbfix}user u on rl.user_id=u.user_id {$where} ORDER LIMIT";

        $_order=isset($data['order'])?' order by '.$data['order']:'order by rl.id desc';
        //总条数
        $row=$this->mysql->get_one(str_replace(array('SELECT', 'ORDER', 'LIMIT'), array('count(1) as num', '', ''), $sql));
        $total = $row['num'];

        $epage = empty($data['epage'])?10:$data['epage'];
        $page=$data['page'];
        if(!empty($page))
        {
            $index = $epage * ($page - 1);
        }
        else
        {
            $index=0;$page=1;
        }
        if($index>$total){$index=0;$page=1;}
        $limit = " limit {$index}, {$epage}";
        // echo str_replace(array('SELECT', 'ORDER', 'LIMIT'), array($_select, $_order, $limit), $sql);
        $list = $this->mysql->get_all(str_replace(array('SELECT', 'ORDER', 'LIMIT'), array($_select, $_order, $limit), $sql));
        global $pager;
        $pager->page=$page;
        $pager->epage=$epage;
        $pager->total=$total;
        return array(
            'list' => $list,
            'total' => $total,
            'page' => $pager->show()
        );
    }
    function getRebateLogByPage($data)
    {
        $_select="rl.*";
        $where="where 1=1";
        if(!empty($data['typeid']))
        {
            $where.=" and rl.typeid like '{$data['typeid']}%'";
        }
        if(!empty($data['user_id']))
        {
            $where.=" and rl.user_id={$data['user_id']}";
        }
        if(!empty($data['money']))
        {
            $where.=" and rl.money={$data['money']}";
        }
        if(!empty($data['rebate_id']))
        {
            $where.=" and rl.rebate_id={$data['rebate_id']}";
        }
        $sql = "select SELECT from {$this->dbfix}rebate_log rl  left join {$this->dbfix}user u on rl.user_id=u.user_id {$where} ORDER LIMIT";

        $_order=isset($data['order'])?' order by '.$data['order']:'order by rl.id desc';
        //总条数
        $row=$this->mysql->get_one(str_replace(array('SELECT', 'ORDER', 'LIMIT'), array('count(1) as num', '', ''), $sql));
        $total = $row['num'];

        $epage = empty($data['epage'])?10:$data['epage'];
        $page=$data['page'];
        if(!empty($page))
        {
            $index = $epage * ($page - 1);
        }
        else
        {
            $index=0;$page=1;
        }
        if($index>$total){$index=0;$page=1;}
        $limit = " limit {$index}, {$epage}";
        // echo str_replace(array('SELECT', 'ORDER', 'LIMIT'), array($_select, $_order, $limit), $sql);
        $list = $this->mysql->get_all(str_replace(array('SELECT', 'ORDER', 'LIMIT'), array($_select, $_order, $limit), $sql));
        global $pager;
        $pager->page=$page;
        $pager->epage=$epage;
        $pager->total=$total;
        return array(
            'list' => $list,
            'total' => $total,
            'page' => $pager->show()
        );
    }

}

