<?php
/*
 * 调用接口所用到的方法
 */
class interfacesClass extends Model
{
    public function __construct()
    {
        parent::__construct();
    }
    //接口   获取用户 真实姓名  认证状态
    function getuserinfo($data)
    {
        $where = "where 1=1";
        if(!empty($data['user_id']))
        {
            $where .= " and u.user_id={$data['user_id']}";
        }
        $sql = "select u.user_id,u.username,u.name,u.portrait,u.qq,i.email_status,i.card_status,i.province,i.city,i.county from {$this->dbfix}user u left join {$this->dbfix}userinfo i on i.user_id=u.user_id {$where} limit 1";
        return $this->mysql->get_one($sql);
    }
    //接口   获取用户实名认证信息
    function getrealname($data)
    {
        $where = "where 1=1";
        if(!empty($data['user_id']))
        {
            $where .= " and u.user_id={$data['user_id']}";
        }
        $sql = "select u.username,u.name,i.* from {$this->dbfix}user u left join {$this->dbfix}userinfo i on i.user_id=u.user_id {$where} limit 1";
        return $this->mysql->get_one($sql);
    }
    //接口   获取用户银行账号
    function getuserbank($data)
    {
        $where = "where 1=1";
        if(!empty($data['user_id']))
        {
            $where .= " and user_id={$data['user_id']}";
        }
        $sql = "select user_id,account,bank,branch from {$this->dbfix}account_bank {$where} limit 1";
        return $this->mysql->get_one($sql);
    }
    //接口   设置银行账号
    function setbank($data)
    {
        $user_id = (int)$data['user_id'];
        $account_bank=$this->mysql->one('account_bank',array('user_id' =>$user_id));
        if($account_bank)
        {
            unset($data['user_id']);
            return $this->mysql->update('account_bank',$data,"user_id={$user_id} limit 1");
        }
        else
        {
            $user=$this->mysql->one('user',array('user_id' =>$user_id));
            if($user)
            {
                $data['addtime'] = date('Y-m-d H:i:s');
                $data['addip'] = ip();
                return $this->mysql->insert('account_bank',$data);
            }
            else
            {
                return false;
            }
        }
    }
    //接口   修改用户信息
    function changeuser($data)
    {
        $user_id=(int)$data['user_id'];
        unset($data['user_id']);
        return $this->mysql->update("user",$data,"user_id={$user_id}");
    }
    //接口   修改支付密码
    function changepaypwd($data)
    {
        $user = $this->mysql->one('user',array('user_id'=>$data['user_id']));
        if(!$user)
        {
            return -1;
        }

        $mark = (int)$data['mark'];
        if($mark == 0 && md5($data['old_zf_password'])!=$user['zf_password'])
        {
            return -2;
        }

        $result = $this->mysql->update("user",array('zf_password'=>md5($data['zf_password'])),"user_id={$data['user_id']}");
        if($result==true)
        {
            return 1;
        }
        else
        {
            return -3;
        }
    }
    //接口   申请实名认证
    function realname($data)
    {
        $user_id=(int)$data['user_id'];

        $user=$this->mysql->one('user',array('user_id'=>$user_id));
        if(!$user)
        {
            return -1;
        }

        $result=$this->mysql->update('user',array('name'=>$data['name']),"user_id={$user_id}");
        if($result == false)
        {
            return -2;
        }
        unset($data['name']);

        $userinfo=$this->mysql->one('userinfo',array('user_id'=>$user_id));
        if($userinfo)
        {
            unset($data['user_id']);
            $result=$this->mysql->update('userinfo',$data,"user_id={$user_id}");
            if($result == false)
            {
                return -2;
            }
            return 1;
        }
        else
        {
            $result=$this->mysql->insert('userinfo',$data);
            if($result == false)
            {
                return -2;
            }
            return 1;
        }
    }
    //接口   邮箱认证成功
    function realemail($data)
    {
        $user_id=$data['user_id'];
        $userinfo=$this->mysql->one('userinfo',array('user_id'=>$user_id));
        if($userinfo)
        {
            return $this->mysql->update('userinfo',array('email_status'=>1),"user_id={$user_id}");
        }
        else
        {
            $data['email_status']=1;
            return $this->mysql->insert('userinfo',$data);
        }
    }
}