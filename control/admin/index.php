<?php
class Manager extends Control
{
	public function __construct()
	{
		global $_G;
		parent::__construct();
		$this->base_url='/index.php/'.$_G['system']['houtai'].'/';
		$this->template='admin';
		$this->control	=($this->uri->get(1)!='')?$this->uri->get(1):'index';
		$this->func		=($this->uri->get(2)!='')?$this->uri->get(2):'index';
		$this->user_typeid	=getSession('usertype');
		$this->permission_id=getSession('permission_id');
		if($this->control !='login' && $this->control !='logout')
		{
			if(empty($this->user_id) || empty($this->permission_id))
			{
				$this->redirect('login');
				exit;		
			}
			$this->user=m('user/one',array('user_id'=>$this->user_id));
		}
		if(!in_array($this->control,array('index','login','logout','changepwd')))//主界面不验证权限
		{
			/*if(! check_cmvalue($class.'_'.$func))
			{
				echo 'no permission_id';
				exit;	
			}*/	
			$permission_id=$this->permission_id;		
			if($permission_id!='ALL')
			{
				$permission_id=unserialize($permission_id);
				if(empty($permission_id['func']))
				{
					$permission_id['func']=array();	
				}		
				if(!in_array($this->control.'_'.$this->func,$permission_id['func']))
				{
					echo '无权限';
					exit;
				}			
			}
		}	
	}
}




//index.php/admin/login
if(in_array($_G['class'],array('login','logout','index','changepwd')))
{
	require ROOT.'control/admin/sys.php';
	$sys=new sys();
	call_user_func(array($sys,$_G['class']),array());
}
elseif(file_exists('control/admin/'.$_G['class'].'.php'))
{	
	require ROOT.'control/admin/'.$_G['class'].'.php';	
	$class   = new $_G['class'];
	if($class)
	{
		if(method_exists($class,$_G['func']))
		{
			return call_user_func(array(&$class,$_G['func']),array());
		}
		else
			return call_user_func(array(&$class,'error'),array());
	}
	else
	{
		//return false;
		die("error class({$_G['class']}) method({$_G['func']})");
	}
}
else
{
	die("error class file({$_G['class']})");
}