<?php
if (!defined('ROOT'))  die('no allowed');
class realname extends Manager
{
	public function __construct()
    {
        parent::__construct();
    }
	//列表
	function index()
	{
		$arr=array(
			'username'=>$_GET['username'],
			'starttime'=>$_GET['starttime'],
			'endtime'=>$_GET['endtime'],
			'page'=>(int)$_REQUEST['page'],
			'epage'=>10
		);
		if($this->user['type_id']!=2)
		{
			$arr['subsite_id']=$this->user['subsite_id'];
		}
		$region=$this->mysql->get_all("select * from {$this->dbfix}region");
		$erarname=array();
		foreach($region as $row)
		{
			$erarname[$row['id']]=$row['name'];
		}
		$data=m('realname/getlist',$arr);
		foreach($data['list'] as $key=>$row)
		{
			$data['list'][$key]['area']=$erarname[$row['province']]." ".$erarname[$row['city']]." ".$erarname[$row['county']];
		}
		$this->view('realname',$data);
	}
	//实名认证审核
	function edit()
	{
		if($_POST)
		{
			if($_POST['card_status']==""){show_msg(array('审核状态必选'));exit;}
			$user_id=$_POST['user_id'];
			$card_status=$_POST['card_status'];
			$card_remark=$_POST['card_remark'];
			$this->mysql->update('userinfo',array('card_status'=>$card_status,'card_remark'=>$card_remark),'user_id='.$user_id);
			show_msg(array('操作成功','',$this->base_url('realname')));
			//$this->redirect('usertype');
		}
		else
		{
			$data=m('realname/getone',array('user_id'=>(int)$_GET['user_id']));
			$data['province']=m('region/select_name',$data['province']);
			$data['city']=m('region/select_name',$data['city']);
			$data['county']=m('region/select_name',$data['county']);
			$this->view('realname',$data);	
		}
	}
}