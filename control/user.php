<?php
if (!defined('ROOT'))  die('no allowed');
class user extends Control
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
        大于 0:返回用户 ID，表示用户注册成功
        -1:用户名不合法
        -2:包含不允许注册的词语
        -3:用户名已经存在
        -4:Email 格式有误
        -5:Email 不允许注册
        -6:该 Email 已经被注册
        -7:本地写入数据失败
     */
    function user_register()
    {
        $status = outer_call('uc_user_register',array($_POST['username'],$_POST['password'], $_POST['email']));
        if($status > 0)
        {
            //写入用户信息
            $arr=array(
                'user_id' => $status,
                'type_id' => 1,
                'username' => $_POST['username'],
                'zf_password' => md5($_POST['password']),
                'addtime' => date('Y-m-d H:i:s'),
                'email' => $_POST['email'],
                'invite_userid'=>$_POST['invite_userid'],
                'subsite_id'=>(int)$_POST['subsite_id'],
            );
            $result=$this->mysql->insert("user",$arr);
            if($result != true)
            {
                $status = -7;
            }
        }
        echo $status;
    }

    //登录
    /*返回对应状态
        大于 0:返回用户 ID，表示用户登录成功
        -1:用户不存在，或者被删除
        -2:密码错
        -3:安全提问错
     */
    function user_login()
    {
        list($uid, $username, $password, $email) =outer_call('uc_user_login',array($_POST['username'], $_POST['password']));
        echo $uid;
    }

    //获取用户信息
    /*返回对应状态
        数组：获取用户信息成功
        -1：该用户不存在
     */
    function get_user()
    {
        if((int)$_POST['user_id']>0)
        {
            $user = $this->mysql->one('user',array('user_id'=>(int)$_REQUEST['user_id']));
        }
        else
        {
            $user = $this->mysql->one('user',array('username'=>$_REQUEST['username']));
        }
        if(!$user)
        {
            echo -1;
        }
        else
        {
            echo json_encode($user);
        }
    }

    //获取用户头像  真实姓名  认证状态
    /*返回对应状态
        数组：获取用户信息成功
        -1：该用户不存在
     */
    function get_userinfo()
    {
        $user = m('interfaces/getuserinfo',array('user_id'=>(int)$_REQUEST['user_id']));
        if(!$user)
        {
            echo -1;
        }
        else
        {
            if($user['portrait']=="")
            {
                $user['portrait']="http://".$_SERVER['HTTP_HOST']."/themes/images/touxiang.png";
            }
            echo json_encode($user);
        }
    }

    //获取用户实名认证信息
    /*返回对应状态
        数组：获取用户信息成功
        -1：该用户不存在
     */
    function get_realname()
    {
        $userinfo=m('interfaces/getrealname',array('user_id'=>(int)$_REQUEST['user_id']));
        if(!$userinfo)
        {
            echo -1;
        }
        else
        {
            echo json_encode($userinfo);
        }
    }

    //获取用户银行账号
    /*返回对应状态
        数组：获取用户银行账号成功
        数组为空：未设置银行账号
     */
    function get_userbank()
    {
        $user = m('interfaces/getuserbank',array('user_id'=>(int)$_REQUEST['user_id']));
        echo json_encode($user);
    }

    //修改登录密码
    /*返回对应状态
        1:更新成功
        0:没有做任何修改
        -1:旧密码不正确
        -4:Email 格式有误
        -5:Email 不允许注册
        -6:该 Email 已经被注册
        -7:没有做任何修改
        -8:该用户受保护无权限更改
     */
    function user_changepwd()
    {
        $status = outer_call('uc_user_edit',array($_POST['username'],$_POST['old_password'], $_POST['password'],""));
        echo $status;
    }

    //重置登录密码
    /*返回对应状态
        1:更新成功
        0:没有做任何修改
        -1:旧密码不正确
        -4:Email 格式有误
        -5:Email 不允许注册
        -6:该 Email 已经被注册
        -7:没有做任何修改
        -8:该用户受保护无权限更改
     */
    function user_resetpwd()
    {
        $status = outer_call('uc_user_edit',array($_POST['username'],"", $_POST['password'],"",1));
        echo $status;
    }

    //修改支付密码
    /*返回对应状态
        1:修改成功
        -1:用户不存在
        -2:旧密码不正确
        -3:写入数据失败
     */
    function user_changepaypwd()
    {
        $arr=array(
            'user_id'=>$_POST['user_id'],
            'old_zf_password'=>$_POST['old_zf_password'],
            'zf_password'=>$_POST['zf_password'],
            'mark'=>"",
        );
        $status=m('interfaces/changepaypwd',$arr);
        echo $status;
    }

    //重置支付密码
    /*返回对应状态
        1:修改成功
        -1:用户不存在
        -3:写入数据失败
     */
    function user_resetpaypwd()
    {
        $arr=array(
            'user_id'=>$_POST['user_id'],
            'zf_password'=>$_POST['zf_password'],
            'mark'=>1,
        );
        $status=m('interfaces/changepaypwd',$arr);
        echo $status;
    }

    //修改用户信息
    /*返回对应状态
        1:成功
        -1:失败
     */
    function user_changeuser()
    {
        $result=m('interfaces/changeuser',$_POST);
        if($result == true)
        {
            echo 1;
        }
        else
        {
            echo -1;
        }
    }

    //设置银行账号
    /*返回对应状态
        1:成功
        -1:失败
     */
    function user_setbank()
    {
        $result=m('interfaces/setbank',$_POST);
        if($result == true)
        {
            echo 1;
        }
        else
        {
            echo -1;
        }
    }

    //申请实名认证
    /*返回对应状态
        1:申请成功
        -1:用户不存在
        -2:写入数据失败
     */
    function user_realname()
    {
        $status=m('interfaces/realname',$_POST);
        echo $status;
    }

    //邮箱认证成功
    /*返回对应状态
        1:认证成功
        -1:用户不存在
        -2:写入数据失败
     */
    function user_realemail()
    {
        $user_id=(int)$_POST['user_id'];
        $user = $this->mysql->one('user',array('user_id'=>$user_id));
        if(!$user)
        {
            echo -1;
        }

        $result = m('interfaces/realemail',array('user_id'=>$user_id));
        if($result == true)
        {
            echo 1;
        }
        else
        {
            echo -2;
        }
    }

    //设置用户头像
    /*返回对应状态
        1:设置成功
        -1:写入数据失败
     */
    function user_setportrait()
    {
        $user_id = (int)$_POST['user_id'];
        unset($_POST['user_id']);
        $result = $this->mysql->update('user',$_POST,"user_id={$user_id} limit 1");
        if($result == true)
        {
            echo 1;
        }
        else
        {
            echo -1;
        }
    }

    //验证用户名
    /*返回对应状态
        1:成功
        -1:用户名不合法
        -2:包含要允许注册的词语
        -3:用户名已经存在
     */
    function check_username()
    {
        $status = outer_call('uc_user_checkname',array($_POST['username']));
        echo $status;
    }

    //验证邮箱
    /*返回对应状态
        1:成功
        -4:Email 格式有误
        -5:Email 不允许注册
        -6:该 Email 已经被注册
     */
    function check_email()
    {
        $status = outer_call('uc_user_checkemail',array($_POST['email']));
        echo $status;
    }
}