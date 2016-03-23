<?php
class userClass extends Model
{	
	public function __construct()
    {  
		$this->table=$this->dbfix.'user';
		$this->fields=array('id','name','username','password','money','addtime','status','lastip','times','money_dj','zf_password','email','tel','qq','address');
		parent::__construct();
    }
	function logout()
	{
		setSession('user_id','');
		setSession('username','');
		setSession('lastip','');
		setSession('usertype','');
		setSession('permission_id','');	
	}
	function login($data)
	{
		$return['status']=0;
		
		if($data['direct']=='1')
		{
			//后台WAP登录
			$uid=(int)$data['user_id'];
		}
		else
		{
			list($uid, $username, $password, $email) =outer_call('uc_user_login',array($data['username'], $data['password']));
			
		}
		
		if($uid > 0)
		{
			$return['status']=1;
			$user=$this->getone(array('user_id'=>$uid));
			if($user)
			{
				if($data['admin']==true)
				{
					$usertype=$this->mysql->one('usertype',array('id'=>$user['type_id']));
					if($usertype['is_admin'] !=1)
					{
						$return['msg'] = '会员禁止登陆！';	
						return $return;
					}
					setSession('usertype',		$usertype['id']);
					setSession('permission_id',	$usertype['permission_id']);
				}
				else
				{
					setSession('usertype',0);
					setSession('permission_id','');
				}
				setSession('user_id',	$user['user_id']);
				setSession('username',	$user["username"]);
				setSession('lastip',	$user["lastip"]);
				$arr=array(
					'lastip'=>ip()
				);
				$this->mysql->update('user',$arr,"user_id=".$user["user_id"],1);
				return true;
			}
			else
			{
				$return['msg'] = '本地用户错误！';	
			}
		} 
		elseif($uid == -1 || $uid == -2) 
		{
			$return['msg'] = '用户名或密码错误！';
		} else {
			$return['msg'] = '未知错误！';
		}
		return $return;
	}
	function register($data)
	{
		if(empty($data['username']))
		{
			return "用户名不能为空！";
		}
		if(strlen($data['username'])<4 || strlen($data['username'])>30)
		{
			return "用户名长度5位到15位！";
		}
		if(strlen($data['password'])==0)
		{
			return "密码不能为空！";
		}
		if(strlen($data['password'])>15 || strlen($data['password'])<6)
		{
			return "密码长度6位到15位！";
		}
		if($data['password'] != $data['sure_password'])
		{
			return "两次输入密码不同！";
		}
		if(strlen($data['email'])==0)
		{
			return "邮箱不能为空！";
		}
		if(!empty($data['names']))
		{
			$invitation=$this->mysql->one('user',array('username'=>$data['names']));
			if(is_array($invitation))
			{
				$invite_userid=$invitation['user_id'];
			}
			else
			{
				return "介绍人用户名不正确！";
			}
		}
		$status = outer_call('uc_user_register',array($data['username'],$data['password'], $data['email']));
		if($status > 0){
			$arr=array(
			     'user_id' => $status,
				 'type_id' => 1,
                 'username' => $data['username'],
				 'zf_password' => md5($data['password']),
				 'addtime' => date('Y-m-d H:i:s'),
				 'times' => 0,
				 'email' => $data['email'],
				 'invite_userid' => (int)$invite_userid,
				 'subsite_id'=>$data['subsite_id'],
			);
			$this->mysql->insert("user",$arr);
			return true;
		} 
		elseif($status == -1 ) {
			$returnmsg = '用户名不合法！';
		}
		elseif($status == -2 ) {
			$returnmsg = '包含不允许注册的词语！';
		} 
		elseif($status == -3 ) {
			$returnmsg = '用户名已经存在！';
		} 
		elseif($status == -4 ) {
			$returnmsg = 'Email 格式有误！';
		} 
		elseif($status == -5 ) {
			$returnmsg = 'Email 不允许注册！';
		} 
		elseif($status == -6 ) {
			$returnmsg = '该 Email 已经被注册！';
		}
		return $returnmsg;
	}
	function getone($data=array())
	{
		$where=" where 1=1";
		if(isset($data['user_id']))
		{
			$where.=" and user_id={$data['user_id']}";
		}		
		$sql="select * from {$this->dbfix}user {$where} limit 1";
		return $this->mysql->get_one($sql);
	}
	function getlist($data)
	{
		global $pager;
		$_select="u.*,ut.name as typename";
		$where="where 1=1";
		if(!empty($data['type_id']))
		{
			$where.=" and u.type_id={$data['type_id']}";	
		}
		if(!empty($data['subsite_id']))
		{
			$where.=" and u.subsite_id={$data['subsite_id']}";	
		}
		if(!empty($data['username']))
		{
			$where.=" and u.username like '%{$data['username']}%'";	
		}
		$sql = "select SELECT from {$this->dbfix}user u left join {$this->dbfix}usertype ut on u.type_id=ut.id {$where} ORDER LIMIT";

		$_order=isset($data['order'])?' order by '.$data['order']:'order by u.user_id desc';
		//总条数	
		$row=$this->mysql->get_one(str_replace(array('SELECT', 'ORDER', 'LIMIT'), array('count(1) as num', '', ''), $sql));
		$total = $row['num'];
		
		$epage = empty($data['epage'])?1:$data['epage'];	
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
	//导出用户EXCLE列表
	function xlslist($data)
	{
		$select="u.user_id,ut.name as typename,u.username,u.email,u.name,u.tel,u.qq,u.address,u.subsite_id,u.addtime";
		$where=" where 1=1";
		if(!empty($data['type_id']))
		{
			$where.=" and u.type_id={$data['type_id']}";
		}
		if(!empty($data['subsite_id']))
		{
			$where.=" and u.subsite_id={$data['subsite_id']}";	
		}
		if(!empty($data['username']))
		{
			$where.=" and u.username like '%{$data['username']}%'";	
		}
		$sql = "select {$select} from {$this->dbfix}user u left join {$this->dbfix}usertype ut on u.type_id=ut.id {$where} order by u.user_id desc";
        return $this->mysql->get_all($sql);
	}
	//修改密码
	function changepwd($data)
	{
		$status = outer_call('uc_user_edit',array($data['username'],$data['old_password'], $data['password'],""));
		if($status == 1){
			$returnmsg = '修改密码成功！';	
		} 
		elseif($status == -1 ) 
		{
			$returnmsg = '原密码错误！';
		} 
		elseif($status == -7 || $status == 0) 
		{
			$returnmsg = '没有做任何修改！';
		} else {
			$returnmsg = '未知错误！';
		}
		return $returnmsg;
	}
	//找回密码
	function updatepwd($data)
	{
		$status = outer_call('uc_user_edit',array($data['username'],"", $data['password'],"",1));
		if($status == 1 || $status == -7 || $status == 0)
		{
			$returnmsg = '找回密码成功！';	
		}
		else
		{
			$returnmsg = '未知错误！';
		}
		return $returnmsg;
	}
	//修改支付密码
	function paypwd($data)
	{
		$arr['zf_password']=md5($data['zf_password']);
		return $this->mysql->update("user",$arr,"user_id={$data['user_id']} limit 1");
	}
	//用户管理显示单条
	function one($data=array())
	{
		return $this->mysql->one("user",$data);
	}
    //获取银行账号
    function getBank($data)
    {
        $user_id =(int)$data['user_id'];
        return $this->mysql->one('account_bank',array('user_id' =>$user_id));
    }
    //设置银行账号
    function setBank($data)
    {
        if(isset($data['user_id']))
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
            }
        }
        else
        {
            return 'no param user_id in bank';
        }
    }
	//实名认证显示信息
	function userinfoone($data)
	{
		$select="u.*,i.*,b.account";
		$where="where u.user_id=".$data['user_id'];
		$sql = "select {$select} from {$this->dbfix}user u left join {$this->dbfix}userinfo i on u.user_id=i.user_id left join {$this->dbfix}account_bank b on u.user_id=b.user_id {$where}";
		return $this->mysql->get_one($sql);
	}
	//用户管理编辑
	function edit($data=array())
	{
		$user_id=(int)$data['user_id'];
		unset($data['user_id']);
		return $this->mysql->update("user",$data,"user_id={$user_id}");
	}
	//实名认证
	function editinfo($data=array())
	{
		$user_id=(int)$data['user_id'];
		$user=$this->mysql->one('userinfo',array('user_id'=>$user_id));
		if(is_array($user))
		{
			unset($data['user_id']);
			return $this->mysql->update("userinfo",$data,"user_id={$user_id}");
		}
		else
		{
			return $this->mysql->insert("userinfo",$data);
		}
	}
    //客服-领导列表
    function getuser($data)
    {
        $type_id=(int)$data['type_id'];
        $select="user_id,username";
        $sql = "select {$select} from {$this->dbfix}user where type_id={$type_id} order by user_id";
        return $this->mysql->get_all(str_replace(array('SELECT', 'LIMIT'), array($_select, $limit), $sql));
    }
}