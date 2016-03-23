<?php
if (!defined('ROOT'))  die('no allowed');
class sys extends Manager
{
	public function __construct()
    {
        parent::__construct();
    }
	function index()
	{
		$permission_id=$this->permission_id;
		//获得所有菜单
		$data['menu']=m('permission/getlist');
		if($permission_id != 'ALL')
		{
			//如果不是超级管理，反序列化成数组
			$permission_id=unserialize($permission_id);
			if(empty($permission_id['menu']))
			{
				$permission_id['menu']=array();
			}
			if(empty($permission_id['submenu']))
			{
				$permission_id['submenu']=array();
			}
			foreach($data['menu'] as $key=>$menu)
			{
				//如果角色权限里没有该一级菜单，则移除该一级菜单。
				if(! in_array($menu['id'],$permission_id['menu']))
				{
					unset($data['menu'][$key]);	
				}
				if(isset($menu['son']) && is_array($menu['son']))
				{
					foreach($menu['son'] as $i=>$submenu)
					{
						//如果角色权限里没有该二级菜单，则移除该二级菜单。
						if(! in_array($submenu['id'],$permission_id['submenu']))
						{
							unset($data['menu'][$key]['son'][$i]);	
						}
					}
				}
			}			
		}	
		$this->view('manage',$data);
		exit;
	}
	function logout()
	{
		m('user/logout');
		$this->redirect('login');
		exit;
	}
	function login()
	{
		if($_POST)
		{
			if($_POST['valicode']!=$_SESSION['randcode'])
			{
				$msg = '验证码不正确！';		
			}
			else
			{
				$data=array(
					'admin'=>true,
					'username'=>trim($_POST['username']),
					'password'=>$_POST['password']
				);
				$result=m('user/login',$data);
				if($result===true)
				{
					$this->redirect('index');
				}
				else
				{
					$msg=$result['msg'];
				}
			}
			if($msg) show_msg(array($msg));
		}
		else
		{
			$this->view('login',$data);
		}
	}
	//修改密码
	function changepwd()
	{
		if($_POST)
		{
			$username=$this->username;
			$errorMsg="";
			if(strlen($_POST['password'])==0)
			{
				$errorMsg .= "密码不能为空！"."<br>";
			}
			if(strlen($_POST['password'])>15 || strlen($_POST['password'])<6)
			{
				$errorMsg .= "密码长度6位到15位！"."<br>";
			}
			if($_POST['password'] != $_POST['sure_password'])
			{
				$errorMsg .= "两次输入密码不同！"."<br>";
			}
			if($_POST['valicode'] != $_SESSION['randcode'])
			{
				$errorMsg .= "验证码不正确！"."<br>";		
			}
			if(strlen($errorMsg) > 0)
			{
				show_msg(array($errorMsg));
			}
			else
			{
				$post=array(
					'username'=>$username,
					'old_password'=>$_POST['old_password'],
					'password'=>$_POST['password'],
				);
				$returnmsg = "";
				$status = outer_call('uc_user_edit',array($post['username'],$post['old_password'], $post['password'],""));
				if($status == 1)
				{
					$returnmsg = '修改密码成功！';	
				} 
				elseif($status == -1 ) 
				{
					$returnmsg = '原密码错误！';
				} 
				elseif($status == -7 || $status == 0) 
				{
					$returnmsg = '没有做任何修改！';
				}
				else
				{
					$returnmsg = '未知错误！';
				}
				show_msg(array($returnmsg));
				//$this->redirect('user/changepwd');
			}
			
		}
		else
		{
		    $this->view('pwd');
		}	
	}
}