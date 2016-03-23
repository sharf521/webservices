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
 * */
class zjClass extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table=$this->dbfix.'zj';
        $this->fields=array();
    }

    private function add_pre($plate)
    {
        $plate=(int)$plate;
        if($plate==0){
            $plate=1;
        }
        for($i=0;$i<5;$i++){
            $row=$this->mysql->get_one("select `index` as nums from {$this->table} where plate={$plate} order by id desc limit 1");
            $index=intval($row['nums'])+1;
            if($this->checkSild($index)) {
                $arr = array(
                    'site_id' => 0,
                    'user_id' => 0,
                    'pid' => 0,
                    'money' => bcmul(800,pow(2,$plate-1)),
                    'income' => 0,
                    'plate' => $plate,
                    'index'=>$index,
                    'addtime' => date('Y-m-d H:i:s'),
                    'dayplan'=>0,
                    'status'=>0
                );
                if($index==1){
                    $arr['status']=1;
                }
                $result = $this->mysql->insert("zj", $arr);
            }else{
                break;
            }
        }
        return true;
    }
    function add($data)
    {
        $plate=(int)$data['plate'];
        if($plate==0){
            $plate=1;
        }
        if(empty($data['user_id'])){
            $return=array('code'=>0,'msg'=>'参数错误！');
        }
        else{
            /*
            $row=$this->mysql->one('zj',array('user_id'=>$data['user_id']));
            if($row){
                $return=array('code'=>1,'msg'=>'用户己购买！');
                return json_encode($return);
            }*/

            $this->add_pre($plate);
            $row=$this->mysql->get_one("select `index` as nums from {$this->table} where plate={$plate} order by id desc limit 1");
            $arr=array(
                'site_id' => 1,
                'user_id' => (int)$data['user_id'],
                'pid' => 0,
                'index'=>intval($row['nums'])+1,
                'money' => bcmul(800,pow(2,$plate-1)),
                'income'=>0,
                'plate'=>$plate,
                'addtime' => date('Y-m-d H:i:s'),
                'dayplan'=>0,
                'status' => 0
            );
            $result=$this->mysql->insert("zj",$arr);
            if($result==true){
                $return=array('code'=>200,'msg'=>'ok');
            }else{
                $return=array('code'=>0,'msg'=>'内部错误');
            }
        }
        return json_encode($return);
    }
    function calAdd1000()
    {
        $this->mysql->query('TRUNCATE TABLE  `plf_zj`');
        $this->mysql->query('TRUNCATE TABLE  `plf_zj_log`');
        for($i=1;$i<=50;$i++){
            $this->add(array('user_id'=>$i,'plate'=>1));
        }
        return true;
    }
    function calZj(){
        $this->mysql->query("start transaction");
        $transaction_result=true;
        try {
            $transaction_result=$this->calPlate(1);
            $transaction_result=$this->calPlate(2);
            $transaction_result=$this->calPlate(3);
            $transaction_result=$this->calPlate(4);
            $transaction_result=$this->calPlate(5);
            $transaction_result=$this->calPlate(6);
            $transaction_result=$this->calPlate(7);
            $transaction_result=$this->calPlate(8);
            $transaction_result=$this->calPlate(9);
            $transaction_result=$this->calPlate(10);
            if($transaction_result!==true){
                $this->mysql->query("rollback");
                return false;
            }
            $transaction_result=$this->cal25DaysPlan();
            if($transaction_result!==true){
                $this->mysql->query("rollback");
                return false;
            }
        }catch (Exception $e){
            $this->mysql->query("rollback");
        }
        if($transaction_result===true){
            $this->mysql->query("commit");
        }
        return $transaction_result;
    }

    //25天计划
    private function cal25DaysPlan()
    {
        $today=date('Y-m-d');
        $sql = "select id,user_id,income,addtime,plate,dayplan from {$this->dbfix}zj where dayplan!=25 order by id";
        $result = $this->mysql->get_all($sql);
        foreach ($result as $row) {
            $date = substr($row['addtime'], 0, 10);
            $child = $this->mysql->get_one("select addtime from {$this->dbfix}zj where pid={$row['id']} order by id limit 1");
            if($child){
                $childDay=substr($child['addtime'],0,10);
            }else{
                $childDay='2200-01-01';
            }
            $arr_days=array(
                3=>150,
                5=>50,
                10=>50,
                15=>50,
                20=>50,
                25=>150,
            );
            foreach($arr_days as $k=>$v){
                if($k > $row['dayplan']){
                    $day = date('Y-m-d', strtotime($date) + 3600 * 24 * $k);
                    if($day < $today && $day < $childDay){
                        $money_log = array(
                            'user_id' =>  $row['user_id'],
                            'zj_id' => $row['id'],
                            'in_user_id' => 0,
                            'in_zj_id' => 0,
                            'plate' => $row['plate'],
                            'money'=>bcmul($v,pow(2,$row['plate']-1)),
                            'typeid' => '3,3,',
                            'addtime' => date('Y-m-d H:i:s')
                        );
                        $transaction_result=$this->mysql->insert('zj_log',$money_log);
                        if ($transaction_result !==true){
                            return false;
                        };
                        $transaction_result=$this->mysql->query("update {$this->dbfix}zj set income=income+{$money_log['money']},dayplan={$k} where id={$row['id']} limit 1");
                        if ($transaction_result !==true){
                            return false;
                        };
                    }
                }
            }
            //已经过了25天
            if($today>$day){
                $transaction_result=$this->mysql->query("update {$this->dbfix}zj set dayplan={$k} where id={$row['id']} limit 1");
                if ($transaction_result !==true){
                    return false;
                };
            }
        }
        return true;
    }
    private function calPlate($plate)
    {
        $sql = "select id,user_id from {$this->dbfix}zj where status=0 and plate={$plate} order by id";//每一盘的第一个跳过
        $result = $this->mysql->get_all($sql);
        foreach ($result as $row) {
            $sql = "select id,user_id,pids,childsize,income from {$this->dbfix}zj where plate={$plate} and childsize!=3 order by id limit 1";
            $_row = $this->mysql->get_one($sql);
            $arr = array(
                'status'=>1,
                'pid' => $_row['id'],
                'pids' => $_row['pids'] . $_row['id'] . ','
            );
            $transaction_result=$this->mysql->update('zj', $arr, "id={$row['id']} limit 1");
            if ($transaction_result !==true){
                return false;
            };
            //第一层奖励
            $money_log = array(
                'user_id' =>  $_row['user_id'],
                'zj_id' => $_row['id'],
                'in_user_id' => $row['user_id'],
                'in_zj_id' => $row['id'],
                'plate' => $plate,
                'money'=>bcmul(300,pow(2,$plate-1)),
                'typeid' => '3,1,',
                'addtime' => date('Y-m-d H:i:s')
            );
            $transaction_result=$this->mysql->insert('zj_log',$money_log);
            if ($transaction_result !==true){
                return false;
            };

            $_arr=array(
                'childsize'=>$_row['childsize']+1,
                'income'=>bcadd($_row['income'],$money_log['money'],5)
            );
            $transaction_result=$this->mysql->update('zj',$_arr, "id={$_row['id']} limit 1");
            if ($transaction_result !==true){
                return false;
            };

            //滑落 上层已经够3个了 再 判断上层的上层是不是可以滑落
            if($plate<10 && $_arr['childsize']==3){
                $arr=explode(',',trim($arr['pids'],','));
                if(count($arr)>1){
                    array_pop($arr);
                    $pp_id=intval(array_pop($arr));
                    $sql = "select user_id,income from {$this->dbfix}zj where id={$pp_id} and childsize=3 limit 1";
                    $pp_row = $this->mysql->get_one($sql);
                    if($pp_row){
                        $p_row=$this->mysql->get_one("select count(id) as counts from {$this->dbfix}zj where pid={$pp_id} and childsize=3");
                        if($p_row['counts']==3){
                            $money_log = array(
                                'user_id' =>  $pp_row['user_id'],
                                'zj_id' => $pp_id,
                                'in_user_id' => $row['user_id'],
                                'in_zj_id' => $row['id'],
                                'plate' => $plate,
                                'money'=>bcmul(3600,pow(2,$plate-1)),//T2 每个400 一共9个
                                'typeid' => '3,2,',
                                'addtime' => date('Y-m-d H:i:s')
                            );
                            $transaction_result=$this->mysql->insert('zj_log',$money_log);
                            if ($transaction_result !==true){
                                return false;
                            };
                            //进入下一盘
                            $money_log2 = array(
                                'user_id' =>  $pp_row['user_id'],
                                'zj_id' => $pp_id,
                                'in_user_id' => $row['user_id'],
                                'in_zj_id' => $row['id'],
                                'plate' => $plate+1,
                                'money'=>'-'.bcmul(800,pow(2,$plate+1-1)),
                                'typeid' => '3,2,',
                                'addtime' => date('Y-m-d H:i:s')
                            );
                            $transaction_result=$this->mysql->insert('zj_log',$money_log2);
                            if ($transaction_result !==true){
                                return false;
                            };
                            $arr=array(
                                'status'=>10,
                                'income'=>bcadd($pp_row['income'],bcadd($money_log['money'],$money_log2['money']),5)
                            );
                            $transaction_result=$this->mysql->update('zj',$arr, "id={$pp_id} limit 1");
                            if ($transaction_result !==true){
                                return false;
                            };

                            $this->add(array('user_id' =>  $pp_row['user_id'],'plate' => $plate+1));
                        }
                    }
                }
            }
        }
        return true;
    }
    //判断边缘
    private function checkSild($x){
        $a0 = 0;
        if ($x == $a0 || $x == $a0 + 1) return true;
        $flag = false;
        $a0=1;
        while (true){
            $an = $a0 * 3-1;
            if ($x == $an || $x == $an -1){
                $flag = true;
                break;
            }
            if($an>$x) break;
            $a0 = $an;
        }
        return $flag;
    }

    function getFbbByPage($data)
    {
        $_select="r.*";
        $where="where 1=1";
        if(!empty($data['type_id']))
        {
            $where.=" and u.type_id={$data['type_id']}";
        }
        if(!empty($data['subsite_id']))
        {
            $where.=" and u.subsite_id={$data['subsite_id']}";
        }
        $sql = "select SELECT from {$this->dbfix}zj r left join {$this->dbfix}user u on r.user_id=u.user_id {$where} ORDER LIMIT";

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
    function getFbbLogByPage($data){
        $_select="fl.*";
        $where="where 1=1";
        if(!empty($data['user_id']))
        {
            $where.=" and fl.user_id={$data['user_id']}";
        }
        if(!empty($data['money']))
        {
            $where.=" and fl.money={$data['money']}";
        }
        if(!empty($data['zj_id']))
        {
            $where.=" and fl.zj_id={$data['zj_id']}";
        }
        $sql = "select SELECT from {$this->dbfix}zj_log fl {$where} ORDER LIMIT";

        $_order=isset($data['order'])?' order by '.$data['order']:'order by fl.id desc';
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