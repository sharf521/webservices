<?php
if (!defined('ROOT'))  die('no allowed');
class rebate extends Manager
{
    public function __construct()
    {
        parent::__construct();
    }
    function index()
    {
        $arr=array(
            'typeid'		=>(int)$_GET['typeid'],
            'status'		=>$_GET['status'],
            'user_id'		=>(int)$_GET['user_id'],
            'page'			=>(int)$_REQUEST['page'],
            'epage'			=>10
        );
        $data['result']=m('rebate/getRebateByPage',$arr);
        $this->view('rebate',$data);
    }
    function add($data)
    {
        if($_POST)
        {
            $post=array(
                'user_id'=>$_POST['user_id'],
                'typeid'=>$_POST['typeid'],
                'money'=>$_POST['money']
            );
            $return=m('rebate/addRebate',$post);
            show_msg(array('添加成功','',$this->base_url('rebate')));
        }
        else
        {
            $this->view('rebate',$data);
        }
    }
    function calRebate()
    {
        $return=m('rebate/calRebate');
        if($return===true){
            show_msg(array('完成','',$this->base_url('rebate')));
        }else{
            show_msg(array('失败！！'));
        }
    }
    function cal2()
    {
        $return=m('rebate/cal2');
        if($return===true){
            show_msg(array('完成','',$this->base_url('rebate')));
        }else{
            show_msg(array('失败！！'));
        }
    }
    function cal3()
    {
        $return=m('rebate/cal3');
        if($return===true){
            show_msg(array('完成','',$this->base_url('rebate')));
        }else{
            show_msg(array('失败！！'));
        }
    }
    function delete()
    {
        $result=m('usertype/delete',array('id'=>(int)$_GET['id']));
        show_msg(array('删除成功','',$this->base_url('usertype')));
        //$this->redirect('usertype');
    }

    function rebatelist(){
        $arr=array(
            'typeid'		=>(int)$_GET['typeid'],
            'user_id'		=>(int)$_GET['user_id'],
            'page'			=>(int)$_REQUEST['page'],
            'epage'			=>10
        );
        $data['result']=m('rebate/getRebateListByPage',$arr);
        $this->view('rebate',$data);
    }
    function rebatelog(){
        $arr=array(
            'typeid'		=>$_GET['typeid'],
            'user_id'		=>(int)$_GET['user_id'],
            'rebate_id'		=>(int)$_GET['rebate_id'],
            'money'		=>(float)$_GET['money'],
            'page'			=>(int)$_REQUEST['page'],
            'epage'			=>10
        );
        $data['result']=m('rebate/getRebateLogByPage',$arr);
        $this->view('rebate',$data);
    }
}