<?php
class regionClass extends Model
{	
	public function __construct()
    {
		parent::__construct();
    }
	
	function select_all($data=array())
	{
		$where=" where 1=1";
		if(isset($data['pid']))
		{
			$where.=" and pid={$data['pid']}";	
		}
		return $this->mysql->get_all("select * from {$this->dbfix}region $where");
	}
	function select_name($id)
	{
		$region=$this->mysql->one('region',array('id'=>$id));
		return $region['name'];
	}
}