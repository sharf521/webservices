<?php
if (!defined('ROOT'))  die('no allowed');
class plugin extends Control
{
    public function __construct()
    {
        parent::__construct();
    }
    //显示头像
    function face()
    {
        $pic='/themes/images/touxiang.png';
        $user_id=$this->uri->get(2);
        if(is_numeric($user_id) && $user_id>0)
        {
            $user=$this->mysql->one('user',array('user_id'=>$user_id));
            if($user['portrait'])
            {
                $pic=$user['portrait'];
            }
        }
        header("location:$pic");
        exit;
    }
}