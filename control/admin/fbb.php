<?php
if (!defined('ROOT'))  die('no allowed');
class fbb extends Manager
{
    public function __construct()
    {
        parent::__construct();
    }
    function index()
    {
        $arr=array(
            'user_id'		=>(int)$_GET['user_id'],
            'id'		=>(int)$_GET['id'],
            'money'		=>(float)$_GET['money'],
            'page'			=>(int)$_REQUEST['page'],
            'epage'			=>10
        );
        $data['result']=m('fbb/getFbbByPage',$arr);
        $this->view('fbb',$data);
    }
    function add($data)
    {
        if($_POST)
        {
            $post=array(
                'user_id'=>$_POST['user_id'],
                'pid'=>(int)$_POST['pid'],
                'money'=>$_POST['money']
            );
            $return=m('fbb/add',$post);
            $return=json_decode($return,true);
            if($return['code']==200){
                show_msg(array('添加成功','',$this->base_url('fbb')));
            }
           else{
               show_msg(array($return['msg']));
           }
        }
        else
        {
            $this->view('fbb',$data);
        }
    }
    function calFbb(){
        $return=m('fbb/calFbb');
        if($return===true){
            show_msg(array('完成','',$this->base_url('fbb')));
        }else{
            show_msg(array('失败！！'));
        }
    }
    function  fbblog(){
        $arr=array(
            'typeid'		=>$_GET['typeid'],
            'user_id'		=>(int)$_GET['user_id'],
            'fbb_id'		=>(int)$_GET['fbb_id'],
            'in_fbb_id'		=>(int)$_GET['in_fbb_id'],
            'money'		=>(float)$_GET['money'],
            'page'			=>(int)$_REQUEST['page'],
            'epage'			=>10
        );
        $data['result']=m('fbb/getFbbLogByPage',$arr);
        $this->view('fbb',$data);
    }
}