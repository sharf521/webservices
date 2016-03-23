<?php
class realnameClass extends Model
{	
	public function __construct()
    {  
		parent::__construct();
    }
	function getlist($data = array())
	{
		global $pager;
		$_select="i.*,u.*";			
		$where=" where 1=1";
		if(!empty($data['subsite_id']))
		{
			$where.=" and u.subsite_id={$data['subsite_id']}";	
		}
		if(!empty($data['username']))
		{
			$where.=" and u.username like '{$data['username']}%'";	
		}
		if(!empty($data['starttime']))
		{
			$where.=" and i.card_time>'{$data['starttime']}'";
		}
		if(!empty($data['endtime']))
		{
			$where.=" and i.card_time<'{$data['endtime']}'";
		}
		$sql = "select SELECT from {$this->dbfix}userinfo i left join {$this->dbfix}user u on i.user_id=u.user_id {$where} order by i.card_status desc LIMIT";

		//总条数	
		$row=$this->mysql->get_one(str_replace(array('SELECT', 'LIMIT'), array('count(1) as num', ''), $sql));
		$total = $row['num'];
		
		$epage = empty($data['epage'])?1:$data['epage'];	
		$page=$data['page'];
		if(!empty($page))
		{
			$index = $epage * ($page - 1);	
		}
		else
		{
			$index=0;$page=1;
		}		
		if($index>$total){$index=0;$page=1;}
		$limit = " limit {$index}, {$epage}";
		$list = $this->mysql->get_all(str_replace(array('SELECT', 'LIMIT'), array($_select, $limit), $sql));		
		global $pager;
		$pager->page=$page;
		$pager->epage=$epage;
		$pager->total=$total;
		return array(
            'list' => $list,
            'total' => $total,
            'page' => $pager->show()           
        );
	}
	//实名认证显示单条
	function getone($data)
	{
		$select="i.*,u.username,u.name";
		$where=" where i.user_id=".$data['user_id'];
		$sql="select {$select} from {$this->dbfix}userinfo i left join {$this->dbfix}user u on i.user_id=u.user_id {$where} limit 1";
		return $this->mysql->get_one($sql);
	}
}