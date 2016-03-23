<?php
class usertypeClass extends Model
{	
	public function __construct()
    {  
		$this->table=$this->dbfix.'user';
		$this->fields=array();
		parent::__construct();
    }
	function getlist($data=array())
	{
		$sql="select * from {$this->dbfix}usertype order by id";
		return $this->mysql->get_all($sql);
	}
	function getone($data=array())
	{
		return $this->mysql->one("usertype",$data);
	}
	function add($data=array())
	{
		$arr['name'] = $data['name'];
		$arr['desc'] = $data['desc'];
		$arr['is_admin'] = (int)$data['is_admin'];
		$arr['addtime'] = date('Y-m-d H:i:s');			
		$permission['menu']		=$data["menu"];
		$permission['submenu']	=$data["submenu"];
		$permission['func']		=$data["func"];
		$arr['permission_id']=serialize($permission);
		return $this->mysql->insert("usertype",$arr);
	}
	function edit($data=array())
	{
			$arr['name'] = $data['name'];
			$arr['desc'] = $data['desc'];
			$arr['is_admin'] = (int)$data['is_admin'];	
			
			$permission['menu']		=$data["menu"];
			$permission['submenu']	=$data["submenu"];
			$permission['func']		=$data["func"];
			$arr['permission_id']=serialize($permission);		
			return $this->mysql->update("usertype",$arr,"id={$data['id']} limit 1");
	}
	function delete($data=array())
	{
		return $this->mysql->delete("usertype",$data);	
	}
}