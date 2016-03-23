<?php
if (!defined('ROOT'))  die('no allowed');
class permission extends Manager
{
	public function __construct()
	{
		parent::__construct();	
	}
	function index()
	{
		if(isset($_POST['order']))
		{
			$id = $_POST['id'];
			$order = $_POST['order'];
			foreach ($id as $key => $val)
			{
				$sql = "update {$this->dbfix}permissions set `order`='".intval($order[$key])."' where id=$val limit 1";			
				$this->mysql->query($sql);
			}
			show_msg(array('操作成功','',$this->base_url('permission')));
		}
		else
		{
			$data['result']=m('permission/getlist');
			$this->view('permission',$data);
		}
	}
	function add($data)
	{
		if($_POST)
		{
			$return=m('permission/add',$_POST);
			show_msg(array('添加成功','',$this->base_url('permission')));
			//$this->redirect('permission');
		}
		else
		{	
			$this->view('permission');	
		}
	}
	function edit()
	{
		if($_POST)
		{
			$return=m('permission/edit',$_POST);
			show_msg(array('修改成功','',$this->base_url('permission')));
			//$this->redirect('permission');
		}
		else
		{
			$data['row']=m('permission/getone',array('id'=>(int)$_GET['id']));
			$this->view('permission',$data);	
		}
	}
	function delete()
	{
		$result=m('permission/delete',array('id'=>(int)$_GET['id']));
		show_msg(array('删除成功','',$this->base_url('permission')));
		//$this->redirect('permission');
	}
}