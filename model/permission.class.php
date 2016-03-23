<?php
class permissionClass extends Model
{
	function __construct()
	{
		parent::__construct();		
	}
    function getlist($data=array())
	{
		$sql="select * from {$this->dbfix}permissions order by `order`,id";
		$result=$this->mysql->get_all($sql);
		//结果转换为特定格式
		$items=array();
		foreach($result as $row)
		{
			$items[$row['id']]=$row;
		}
		return genTree5($items);
	}
	function getone($data=array())
	{
		return $this->mysql->one("permissions",$data);
	}
	function add($data=array())
	{
		$arr['pid'] = (int)$data['pid'];
		$arr['name'] = $data['name'];
		$arr['desc'] = $data['desc'];
		$arr['url'] = $data['url'];
		$arr['cmvalue'] = str_replace('：',':',$data['cmvalue']);
		$arr['order'] = (int)$data['order'];
		return $this->mysql->insert("permissions",$arr);
	}
	function edit($data=array())
	{
		$id=(int)$data['id'];
		$arr['pid'] = (int)$data['pid'];
		$arr['name'] = $data['name'];
		$arr['desc'] = $data['desc'];
		$arr['url'] = $data['url'];
		$arr['cmvalue'] = str_replace('：',':',$data['cmvalue']);
		$arr['order'] = (int)$data['order'];
		return $this->mysql->update("permissions",$arr,"id={$id} limit 1");
	}
	function delete($data=array())
	{
		return $this->mysql->delete("permissions",$data);	
	}

}