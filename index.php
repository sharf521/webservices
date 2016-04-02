<?php
//error_reporting(E_ALL & ~E_NOTICE);
error_reporting(7);
//define('ROOT', dirname(__FILE__).'/');
define('ROOT', dirname($_SERVER['SCRIPT_FILENAME']).'/');
//__DIR__//5.3 新增
$_G=array();
require 'core/init.php';
require 'data/config.php';
require 'core/function.php';
require 'core/URI.php';
$uriClass=new URI();
require 'core/page.class.php';
$pager=new Page();
//require 'core/mysql.class.php';
//$mysql = new Mysql($db_config);
require 'core/pdo.php';
$mysql = DB::instance('db1');

//$row=DB::table('plf_fbb')->where('id>10')->limit(10)->lists('id');
//print_r($row);
//$row=DB::table('plf_rebate_config')->lists('v','k');
//print_r($row);
//$row=DB::table('plf_fbb')->select('id,user_id,money')->where('id=1')->row();
//print_r($row);
//$row=DB::get_all("select * from plf_fbb where id>? limit 10",array(10));
//print_r($row);
//$row=DB::table('plf_fbb')->select('id,user_id,money')->where('id<10')->orderBy('id desc')->all();
//print_r($row);
//echo '<br>'.DB::table('plf_fbb')->where('id=1')->value('money');

//$aa=DB::get_all("select * from plf_fbb where id =:id limit 10",array('id'=>1));
//$aa=$mysql->query("insert into  uc_vars(name,value)values(?,?)",array('555','sdf'));
//print_r($aa);

require 'core/Controller.php';
$_G['class']=($uriClass->get(0)!='')?$uriClass->get(0):'index';
$_G['func']	=($uriClass->get(1)!='')?$uriClass->get(1):'index';
//联动值
$_G['linkpage']=m('linkpage/getlinkpage');
//参数
$_G['system']=m('system/lists');

if($_G['class']==$_G['system']['houtai'])   
{
	$_G['class']=($uriClass->get(1)!='')?$uriClass->get(1):'index';
	$_G['func']	=($uriClass->get(2)!='')?$uriClass->get(2):'index';
	require 'control/admin/index.php';
	exit;
}
/*elseif($_G['class']=='member')
{
	$_G['class']=($uriClass->get(1)!='')?$uriClass->get(1):'index';
	$_G['func']	=($uriClass->get(2)!='')?$uriClass->get(2):'index';
	require 'control/member/index.php';
	exit;
}*/
elseif(file_exists('control/'.$_G['class'].'.php'))
{	
	require ROOT.'control/'.$_G['class'].'.php';
	$class   = new $_G['class'];
	if($class)
	{
		if(method_exists($class,$_G['func']))
		{
			return call_user_func(array($class,$_G['func']),array());
		}
		else
			return call_user_func(array($class,'error'),array());
	}
	else
	{
		//return false;
		die("error class({$_G['class']}) method({$_G['func']})");
	}
}
else
{
	echo 'page error';	
}
