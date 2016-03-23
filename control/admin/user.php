<?php
if (!defined('ROOT'))  die('no allowed');
class user extends Manager
{
	public function __construct()
    {
        parent::__construct();
    }
	//列表
	function index()
	{
		if(!empty($_GET['xls']))
		{
			$arr=array(
			'type_id'		=>(int)$_GET['type_id'],
			'username'		=>$_GET['username'],
			'subsite_id'	=>$_GET['subsite_id'],
			);
			$data=m('user/xlslist',$arr);
			$title = array("编号","用户类型","用户名","邮箱","真实姓名","电话","QQ","地址","注册来源（1车务/2众筹）","注册时间");
			excel('用户列表',$title,$data);
			exit;
		}
		$arr=array(
			'type_id'		=>(int)$_GET['type_id'],
			'username'		=>$_GET['username'],
			'subsite_id'	=>$_GET['subsite_id'],
			'page'			=>(int)$_REQUEST['page'],
			'epage'			=>10
		);
		$data=m('user/getlist',$arr);
		$data['usertype']=m('usertype/getlist');
		$this->view('user',$data);
	}
	//编辑用户资料
	function edit()
	{
		if($_REQUEST['user_id']=="1")
		{
			show_msg(array('超级管理员禁止操作','',$this->base_url('user')));
			exit;
		}
		if($_POST)
		{
			$arr=array();
			$arr['name'] 	= $_POST['name'];
			$arr['tel'] 	= $_POST['tel'];
			$arr['qq'] 		= $_POST['qq'];
			$arr['address'] = $_POST['address'];
			$arr['user_id'] = (int)$_POST['user_id'];
			$return=m('user/edit',$arr);
			show_msg(array('修改成功','',$this->base_url('user')));
			//$this->redirect('usertype');
		}
		else
		{
			$data['row']=m('user/one',array('user_id'=>(int)$_GET['user_id']));
			$this->view('user',$data);	
		}
	}
	//编辑银行账号
	function edit_bank()
	{
		$user_id=$_REQUEST['user_id'];
		if($user_id=="1")
		{
			show_msg(array('超级管理员禁止操作','',$this->base_url('user')));
			exit;
		}
		if($_POST)
		{
			$arr=array();
			$arr['user_id'] = $user_id;
			$arr['bank'] 	= $_POST['bank'];
			$arr['branch'] 	= $_POST['branch'];
			$arr['account'] = $_POST['account'];
			m('account/setBank',$arr);
			show_msg(array('修改成功','',$this->base_url('user')));
			//$this->redirect('usertype');
		}
		else
		{
			$user=m('user/one',array('user_id'=>$user_id));
			$data['row']=m('account/getBank',array('user_id'=>$user_id));
			$data['row']['user_id']=$user['user_id'];
			$data['row']['username']=$user['username'];
			$data['row']['name']=$user['name'];
			$this->view('user',$data);	
		}
	}
	//修改用户类型
	function edittype()
	{
		if($_POST)
		{
			if(!empty($_POST['invite_userid']))
			{
				$invitation=$this->mysql->one('user',array('user_id'=>(int)$_POST['invite_userid']));
				if(!is_array($invitation))
				{
					show_msg(array('邀请人ID不正确'));exit;
				}
			}
			$arr=array(
				'type_id'		=>(int)$_POST['type_id'],
				'user_id'		=>(int)$_POST['user_id'],
			);
			$return=m('user/edit',$arr);
			show_msg(array('修改成功','',$this->base_url('user')));
			//$this->redirect('usertype');
		}
		else
		{
			$data['usertype']=m('usertype/getlist');
			$data['row']=m('user/one',array('user_id'=>(int)$_GET['user_id']));
			$this->view('user',$data);	
		}
	}
	//WAP登录
	function wap_login()
	{
		global $_G;
		$token=$_G['system']['sys_token'];
		$user_id=$_GET['user_id'];
		if($user_id=="1")
		{
			show_msg(array('超级管理员禁止操作','',$this->base_url('user')));
			exit;
		}
		$time=time();
		$tmpArr = array($user_id,$time,$token);
		sort($tmpArr, SORT_STRING);
		$tmpStr = sha1(implode($tmpArr));
			
		header("Location: http://wap.test.cn?direct=1&user_id={$user_id}&time={$time}&signature={$tmpStr}");
	}
}