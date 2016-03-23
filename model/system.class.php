<?php
class systemClass extends Model
{	
	public function __construct()
    {  
		parent::__construct();
    }
	function getlist($data=array())
	{
		$sql="select * from {$this->dbfix}system order by `showorder`,id";
		$result=$this->mysql->get_all($sql);
		//结果转换为特定格式
		/*$items=array();
		foreach($result as $row)
		{
			$items[$row['id']]=$row;
		}*/
		return $result;
	}
	function lists()
	{
		$_system = $this->getlist();
		foreach ($_system as $key => $value){
			$system[$value['code']] = $value['value'];
		}
		return $system;
	}
	function getone($data=array())
	{
		return $this->mysql->one("system",$data);
	}
	function add($data=array())
	{
		$arr['code'] = $data['code'];
		$arr['name'] = $data['name'];
		$arr['value'] = $data['value'];
		$arr['showorder'] = (int)$data['showorder'];
		$arr['style'] = (int)$data['style'];
		return $this->mysql->insert("system",$arr);
	}
	function edit($data=array())
	{
		$id=(int)$data['id'];
		$arr['code'] = $data['code'];
		$arr['name'] = $data['name'];
		$arr['value'] = $data['value'];
		$arr['showorder'] = (int)$data['showorder'];
		$arr['style'] = (int)$data['style'];
		return $this->mysql->update("system",$arr,"id={$id} limit 1");
	}
	function delete($data=array())
	{
		return $this->mysql->delete("system",$data);	
	}
}