<?php
if (!defined('ROOT'))  die('no allowed');
class linkpage extends Manager
{
	public function __construct()
	{
		parent::__construct();	
	}
	//列表
	function index()
	{
		if($_POST)
		{
			$id = $_POST['id'];
			$name = $_POST['name'];
			$code = $_POST['code'];
			$showorder = $_POST['showorder'];
			foreach ($id as $key => $val)
			{
				$sql = "update {$this->dbfix}linkpage_type set `name`='".$name[$key]."',`code`='".$code[$key]."',`showorder`='".intval($showorder[$key])."' where id=$val limit 1";		
				$this->mysql->query($sql);
			}
			show_msg(array('操作成功','',$this->base_url('linkpage')));
		}
		else
		{
			$arr=array(
				'page'=>(int)$_REQUEST['page'],
				'epage'=>30,
			);
			$result=m('linkpage/getlist',$arr);
			$this->view('linkpage',$result);
		}
	}
	//类型编辑
	function type_edit()
	{
		$id=(int)$_REQUEST['id'];
		if($_POST)
		{
			$linktype->edit($_POST,$id);	
			$msg=array('修改成功！','',$_G['query_url']);
		}
		else
		{
			//$_G['linktype']=$linktype->get_one(array("where"=>"id=$id "));
			$data=$this->mysql->one("linkpage_type",array('id'=>"$id"));
			$this->view('linkpage',$data);
		}
	}
	//类型添加
	function type_add()
	{
		if($_POST)
		{
			$result=m('linkpage/add',$_POST);
			show_msg(array('操作成功','',$this->base_url('linkpage')));
		}
		else
		{
			$this->view('linkpage');
		}
	}
	//类型删除
	function type_drop()
	{
		$result=$this->mysql->delete("linkpage_type",array('id'=>(int)$_GET['id']));
		show_msg(array('删除成功','',$this->base_url('linkpage')));	
	}
	//类型批量添加
	function type_actions()
	{
		if (isset($_POST['name'])){
			$data['type'] = "add";
			$data['name'] = $_POST['name'];
			$data['code'] = $_POST['code'];
			$data['showorder'] = $_POST['showorder'];
			$result=m('linkpage/Action',$data);
			if ($result !== true){
				$msg = array($result);
			}else{
				//$msg = array("操作成功",'',$_G['query_url'].'/linklist&id='.$_POST['typeid']);
				show_msg(array('操作成功','',$this->base_url('linkpage')));
			}
		}else{
			$msg = array("操作有误");
		}
	}
	//子菜单列表
	function linklist()
	{
		if($_POST)
		{
			$id = $_POST['id'];
			$name = $_POST['name'];
			$value = $_POST['value'];
			$showorder = $_POST['showorder'];
			foreach ($id as $key => $val)
			{
				$sql = "update {$this->dbfix}linkpage set `name`='".$name[$key]."',`value`='".$value[$key]."',`showorder`='".intval($showorder[$key])."' where id=$val limit 1";		
				$this->mysql->query($sql);
			}
			show_msg(array('操作成功','',$this->base_url('linkpage/linklist/?id='.$_GET['id'])));
		}
		else
		{
			$id=$_GET['id'];
			$sql = "select * from {$this->dbfix}linkpage_type where id=$id limit 1";
			$typename=$this->mysql->get_one($sql);
			if(!empty($id))
			$arr=array(
				'typeid'=>(int)$_GET['id'],
				'page'=>(int)$_REQUEST['page'],
				'epage'=>30,
			);
			$result=m('linkpage/linklist',$arr);
			$result['typename']=$typename['name'];
			$this->view('linkpage',$result);
		}
	}
	//子菜单添加
	function link_add()
	{
		$id=$_POST['typeid'];
		if($_POST['name'])
		{
			$data=array(
			   'typeid'=>$_POST['typeid'],
			   'name'=>$_POST['name'],
			   'value'=>$_POST['value'],
			   'showorder'=>$_POST['showorder']
			);
			$result=m('linkpage/link_add',$data);
			show_msg(array('操作成功','',$this->base_url('linkpage/linklist/?id='.$id)));
		}
		else
		{
			$this->view('linkpage');
		}
	}
	//子菜单删除
	function link_drop()
	{
		$id=$_GET['typeid'];
		$result=$this->mysql->delete("linkpage",array('id'=>(int)$_GET['id']));
		show_msg(array('删除成功','',$this->base_url('linkpage/linklist/?id='.$id)));
	}
	//子菜单批量添加
	function link_action()
	{
		$id=$_POST['typeid'];
		if (isset($_POST['name'])){
			$data['type'] = "ling_add";
			$data['typeid'] = $_POST['typeid'];
			$data['name'] = $_POST['name'];
			$data['value'] = $_POST['value'];
			$data['showorder'] = $_POST['showorder'];
			$result=m('linkpage/Action',$data);
			if ($result !== true){
				$msg = array($result);
			}else{
				show_msg(array('操作成功','',$this->base_url('linkpage/linklist/?id='.$id)));
			}
		}else{
			show_msg(array('操作错误','',$this->base_url('linkpage/linklist/?id='.$id)));
		}
	}
}