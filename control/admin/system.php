<?php
if (!defined('ROOT'))  die('no allowed');
class system extends Manager
{
	public function __construct()
    {
        parent::__construct();
    }
	function index()
	{
		if(isset($_POST['showorder']))
		{
			$id = $_POST['id'];
			$value = $_POST['value'];
			$showorder = $_POST['showorder'];
			foreach ($id as $key => $val)
			{
				$sql = "update {$this->dbfix}system set `value`='".$value[$key]."',`showorder`='".intval($showorder[$key])."' where id=$val limit 1";			
				$this->mysql->query($sql);
			}
			show_msg(array('操作成功','',$this->base_url('system')));
		}
		else
		{
			$data['result']=m('system/getlist');
			$this->view('system',$data);
		}	
	}
	function add($data)
	{
		if($_POST)
		{
			$return=m('system/add',$_POST);
			show_msg(array('添加成功','',$this->base_url('system')));
			//$this->redirect('system');
		}
		else
		{
			$this->view('system',$data);	
		}
	}
	function edit()
	{
		if($_POST)
		{
			$return=m('system/edit',$_POST);
			show_msg(array('修改成功','',$this->base_url('system')));
			//$this->redirect('system');
		}
		else
		{
			$data['row']=m('system/getone',array('id'=>(int)$_GET['id']));
			$this->view('system',$data);	
		}
	}
	function delete()
	{
		$result=m('system/delete',array('id'=>(int)$_GET['id']));
		show_msg(array('删除成功','',$this->base_url('system')));
		//$this->redirect('system');
	}
}