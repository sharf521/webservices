<?php
class linkpageClass extends Model
{
	function __construct()
	{
		parent::__construct();		
	}
	function getlinkpage()
	{
		$arr=array();
		$_result=$this->mysql->get_all("select b.*,a.code,a.name as tname from {$this->dbfix}linkpage_type a join {$this->dbfix}linkpage b on a.id=b.typeid order by b.showorder asc");
		foreach($_result as $_row)
		{
			$arr[$_row['id']]=$_row['name'];
			$arr[$_row['code']][$_row['value']]=$_row['name'];
		}
		$_result=null;
		return $arr;
	}

	function getlist($data = array())
	{
		global $pager;
		$_select=" * ";
		$sql = "select SELECT from {$this->dbfix}linkpage_type ORDER LIMIT";
		$_order=isset($data['order'])?' order by '.$data['order']:'order by showorder,id';
		
		//总条数	
		$row=$this->mysql->get_one(str_replace(array('SELECT', 'ORDER', 'LIMIT'), array('count(1) as num', '', ''), $sql));
		$total = $row['num'];
		
		$epage = empty($data['epage'])?10:$data['epage'];	
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
		$list = $this->mysql->get_all(str_replace(array('SELECT', 'ORDER', 'LIMIT'), array($_select, $_order, $limit), $sql));
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
	/*//根据code获取联动列表
	function getlistbycode($data)
	{
		$sql="select b.* from {$this->dbfix}linkpage_type a,{$this->dbfix}linkpage b where a.id=b.typeid and a.code='{$data['code']}' order by b.showorder asc";
		return $this->mysql->get_all($sql);
	}
	function getlistid($data)
	{
		$sql="select b.id from {$this->dbfix}linkpage_type a,{$this->dbfix}linkpage b where a.id=b.typeid and a.code='{$data['code']}' and b.name='{$data['name']}' and b.value='{$data['value']}' limit 1";
		$row=$this->mysql->get_one($sql);
		return $row['id'];
	}*/
	function linklist($data = array())
	{
		global $pager;
		$_select=" * ";	
		$where="where 1=1";
		if(!empty($data['typeid']))
		{
			$where.=" and typeid={$data['typeid']}";
		}
		$sql = "select SELECT from {$this->dbfix}linkpage {$where} ORDER LIMIT";
		$_order=isset($data['order'])?' order by '.$data['order']:'order by showorder,id';
		
		//总条数	
		$row=$this->mysql->get_one(str_replace(array('SELECT', 'ORDER', 'LIMIT'), array('count(1) as num', '', ''), $sql));
		$total = $row['num'];
		
		$epage = empty($data['epage'])?10:$data['epage'];	
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
		$list = $this->mysql->get_all(str_replace(array('SELECT', 'ORDER', 'LIMIT'), array($_select, $_order, $limit), $sql));		
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
	
	function set($post)
	{
		$post['name']=(strip_tags($post['name']));
		$post['code']=(strip_tags($post['code']));
		$post['showorder']=(strip_tags($post['showorder']));
		return $post;	
	}
	
	function edit($post,$id)
	{
		$post=$this->set($post);
		$this->id=intval($post['id']);
		$this->update($post,"id=$id");
	}
	
	function add($post)
	{
		$post=$this->set($post);
		$post['showorder']=($post['showorder'])?$post['showorder']:10;
		$post['createdate']=date('Y-m-d H:i:s');	
		return $this->mysql->insert("linkpage_type",$post);
	}
	function link_add($data)
	{
		$data['createdate']=date('Y-m-d H:i:s');
		$post['showorder']=($post['showorder'])?$post['showorder']:10;	
		return $this->mysql->insert("linkpage",$data);
	}
	
	public function Action($data = array()){
		global $mysql;
		$name = $data['name'];
		$code = $data['code'];
		$showorder = $data['showorder'];
		$type = isset($data['type'])?$data['type']:"";
		unset($data['type']);
		$riqi=date('Y-m-d H:i:s');
		if ($type == "add"){
			
			foreach ($name as $key => $val){
				if ($val!=""){
					$sql = "insert into {$this->dbfix}linkpage_type set `name`='".$name[$key]."',`code`='".$code[$key]."',`showorder`='".$showorder[$key]."', `createdate`='".$riqi."', `status`=1 ";			
					$mysql->query($sql);
				}
			}
		}else{
			$typeid = $data['typeid'];
			$value = $data['value'];
			foreach ($name as $key => $val){
				if ($val!=""){
					$sql = "insert into {$this->dbfix}linkpage set `typeid`='".$typeid."',`name`='".$name[$key]."',`value`='".$value[$key]."',`showorder`='".$showorder[$key]."', `createdate`='".$riqi."', `status`=1 ";			
					$mysql->query($sql);
				}
			}
		}
		return true;
	}
}