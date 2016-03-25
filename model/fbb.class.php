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
class fbbClass extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table=$this->dbfix.'fbb';
        $this->fields=array('id','site_id','user_id','money','income','pid','pids','position','addtime','status');
    }

    function add($data)
    {
        if(empty($data['user_id']) || empty($data['money'])){
            $return=array('code'=>0,'msg'=>'参数错误！');
        }
        else{
            $pid=(int)$data['pid'];
            $arr=array(
                'site_id' => 1,
                'user_id' => (int)$data['user_id'],
                'pid' => $pid,
                'pids'=>'',
                'money' => (float)($data['money']),
                'income'=>0,
                'addtime' => date('Y-m-d H:i:s'),
                'status' => 0
            );
            $row=$this->mysql->one('fbb',array('user_id'=>$data['user_id']));
            if($row){
                $return=array('code'=>1,'msg'=>'用户己购买！');
                return json_encode($return);
            }
            $pids='';
            if ($pid != 0) {
                $row = $this->mysql->one('fbb', array('id' => $pid));
                if (!$row) {
                    $return = array('code' => 2, 'msg' => 'pid错误！');
                    return json_encode($return);
                } else {
                    $pids = $row['pids'];
                    $_row = $this->mysql->get_one("select count(id) as count1 from {$this->dbfix}fbb where pid={$pid}");
                    $arr['position'] = $_row['count1'] + 1;
                }
            }
            $result=$this->mysql->insert("fbb",$arr);
            $id=$this->mysql->insert_id();
            $pids=$pids.$id.',';
            $this->mysql->query("update {$this->dbfix}fbb set pids='{$pids},' where id={$id} limit 1");
            if($result==true){
                $return=array('code'=>200,'msg'=>'ok');
            }
            else{
                $return=array('code'=>0,'msg'=>'内部错误');
            }
        }
        return json_encode($return);
    }

    function calFbb()
    {
        try {
            $this->mysql->beginTransaction();
            $this->calFbbDo();
            $this->mysql->commit();
        } catch (Exception $e) {
            $this->mysql->rollBack();
            echo "Failed: " . $e->getMessage();
            return false;
        }
        return true;
    }
    private function calFbbDo()
    {
        $sql = "select id,user_id,pids,`position`,money from {$this->dbfix}fbb where status=0 order by id";
        $result = $this->mysql->get_all($sql);
        foreach ($result as $row) {
            $this->mysql->update('fbb', array('status' => 1), " id={$row['id']} limit 1");//设为己处理
            $pids = rtrim($row['pids'], ',');//去除最后一个，
            if (!empty($pids)) {
                $arr_pid = explode(',', $pids);
                array_pop($arr_pid);
                $arr_pid = array_reverse($arr_pid);
                $arr_pos = array();//上面所有元素的位置
                $i = 1;
                foreach ($arr_pid as $pid) {
                    $prow = $this->mysql->one('fbb', array('id' => $pid));
                    $arr_pos[$i] = $prow['position'];
                    //$money = $row['money'] < $prow['money'] ? $row['money'] : $prow['money'];
                    $money = $row['money'];
                    $fbb_log = array(
                        'user_id' => $prow['user_id'],
                        'fbb_id' => $prow['id'],
                        'in_fbb_id' => $row['id'],
                        'in_user_id' => $row['user_id'],
                        'layer' => $i,
                        'typeid' => '2,1',
                        'addtime' => date('Y-m-d H:i:s')
                    );
                    if ($i == 1) {
                        if ($row['position'] == 1) {
                            $fbb_log['money'] = bcmul($money, 0.2, 5);//400
                        } else {
                            $fbb_log['money'] = bcmul($money, 0.65, 5);//1300
                        }
                    } elseif ($i == 2) {
                        if ($row['position'] == 1 && $arr_pos[1] == 2) {
                            $fbb_log['money'] = bcmul($money, 0.5, 5);//1000
                        } else {
                            $fbb_log['money'] = bcmul($money, 0.01, 5);//20
                        }
                    } else {
                        if ($this->isFbb2_1($row['position'], $arr_pos)) {
                            $fbb_log['money'] = bcmul($money, 0.5, 5);//1000
                        } elseif ($this->isFbb2_2_1($row['position'], $arr_pos)) {
                            $fbb_log['money'] = bcmul($money, 0.1, 5);//200
                        } else {
                            if ($i <= 5) {
                                $fbb_log['money'] = bcmul($money, 0.01, 5);//20
                            } else {
                                $fbb_log['money'] = bcmul($money, 0.005, 5);//10
                            }
                        }
                    }
                    if ($fbb_log['money'] != 0) {
                        $this->mysql->update('fbb', array('income' => bcadd($fbb_log['money'], $prow['income'], 5)), " id={$prow['id']} limit 1");
                        $this->mysql->insert('fbb_log', $fbb_log);
                    }
                    if ($i >= 15) {
                        break;
                    }
                    $i++;
                }
            }
        }
    }


    private function isFbb2_1($my_pos,$arr_pos){
        if($my_pos!=1) return false;//当前位置必须是上级的第一个推荐
        array_pop($arr_pos);//删除最后一个元素
        $last1=array_pop($arr_pos);//删除最后一个元素，返回最后一个
        $return=true;
        foreach($arr_pos as $pos){
            if($pos!=1){
                $return=false;
                break;
            }
        }
        if($return && $last1==2){
            return true;
        }
        return false;
    }
    private function isFbb2_2_1($my_pos,$arr_pos){
        if($my_pos!=1) return false;
        array_pop($arr_pos);//删除最后一个元素
        $last1=array_pop($arr_pos);//删除最后一个元素
        $last2=array_pop($arr_pos);//最后第二个
        $return=true;
        foreach($arr_pos as $pos){
            if($pos!=1){
                $return=false;
                break;
            }
        }
        if($return && $last2==2 && $last1==2){
            return true;
        }
        return false;
    }

    ///////////////////////////////////////////////////
    function getFbbByPage($data)
    {
        $_select="r.*";
        $where="where 1=1";
        if(!empty($data['user_id']))
        {
            $where.=" and r.user_id={$data['user_id']}";
        }
        if(!empty($data['money']))
        {
            $where.=" and r.money={$data['money']}";
        }
        if(!empty($data['id']))
        {
            $_one=$this->mysql->one('fbb',array('id'=>$data['id']));
            $where.=" and  r.pids like '{$_one['pids']}%'";
        }
        $sql = "select SELECT from {$this->dbfix}fbb r left join {$this->dbfix}user u on r.user_id=u.user_id {$where} ORDER LIMIT";

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
        if(!empty($data['fbb_id']))
        {
            $where.=" and fl.fbb_id={$data['fbb_id']}";
        }
        if(!empty($data['in_fbb_id']))
        {
            $where.=" and fl.in_fbb_id={$data['in_fbb_id']}";
        }
        $sql = "select SELECT from {$this->dbfix}fbb_log fl {$where} ORDER LIMIT";

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

