<?php
if (!defined('ROOT'))  die('no allowed');
class algorithm extends Control
{
    private $token='vcivc';
    public function __construct()
    {
        parent::__construct();
        $signature 	= $_REQUEST["signature"];
        $time 		= $_REQUEST["time"];
        if(abs(time()-$time)>600)
        {
            die('time over');
        }
        $nonce 		= $_REQUEST["nonce"];
        $tmpArr = array($this->token, $time, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        unset($_POST['signature']);
        unset($_POST['nonce']);
        unset($_POST['time']);

        if( $tmpStr != $signature )
        {
            die('no checked');
        }
    }

    //注册
    /*返回对应状态
     */
    function register()
    {
        $data = $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'].time().rand();
        $user_id=sha1($data);
        //写入用户信息
        $arr=array(
            'user_id' => $user_id,
            'money_last' => 0,
            'addtime' => date('Y-m-d H:i:s')
        );
        //$result=$this->mysql->insert("rebate_user",$arr);
        echo json_encode(array('code'=>200,'msg'=>''));
    }




}