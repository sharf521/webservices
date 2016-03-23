<?php
if (!defined('ROOT'))  die('no allowed');
class usertype extends Manager
{
	public function __construct()
    {
        parent::__construct();
    }
	function index()
	{
		$data['result']=m('usertype/getlist',array());
		$this->view('usertype',$data);	
	}
	function add($data)
	{
		if($_POST)
		{
			$return=m('usertype/add',$_POST);
			show_msg(array('添加成功','',$this->base_url('usertype')));
			//$this->redirect('usertype');
		}
		else
		{
			$data['permission']=m('permission/getlist');
			$data['permission_id']=array();
			$data['permission_id']['menu']=array();
			$data['permission_id']['submenu']=array();
			$data['permission_id']['func']=array();
			$this->view('usertype',$data);	
		}
	}
	function edit()
	{
		if($_REQUEST['id']=="2")
		{
			show_msg(array('超级管理员禁止操作','',$this->base_url('usertype')));
			exit;
		}
		if($_POST)
		{
			$return=m('usertype/edit',$_POST);
			show_msg(array('修改成功','',$this->base_url('usertype')));
			//$this->redirect('usertype');
		}
		else
		{
			$data['row']=m('usertype/getone',array('id'=>(int)$_GET['id']));
			$data['permission']=m('permission/getlist');
			$data['permission_id']=unserialize($data['row']['permission_id']);
			if(!is_array($data['permission_id']['menu']))	$data['permission_id']['menu']=array();
			if(!is_array($data['permission_id']['submenu']))$data['permission_id']['submenu']=array();
			if(!is_array($data['permission_id']['func']))	$data['permission_id']['func']=array();
			$this->view('usertype',$data);	
		}
	}
	function delete()
	{
		$result=m('usertype/delete',array('id'=>(int)$_GET['id']));
		show_msg(array('删除成功','',$this->base_url('usertype')));
		//$this->redirect('usertype');
	}
}