<?php
class accountClass extends Model
{	
	public function __construct()
    {  
		parent::__construct();
    }
	function getBank($data)
	{
		$user_id =(int)$data['user_id'];
		return $this->mysql->one('account_bank',array('user_id' =>$user_id));	
	}
	function setBank($data)
	{
		if(isset($data['user_id']))
		{
			$user_id = (int)$data['user_id'];
			$account_bank=$this->mysql->one('account_bank',array('user_id' =>$user_id));
			if($account_bank)
			{
				unset($data['user_id']);
				return $this->mysql->update('account_bank',$data,"user_id={$user_id} limit 1");
			}
			else
			{
				$user=$this->mysql->one('user',array('user_id' =>$user_id));
				if($user)
				{
					$data['addtime'] = date('Y-m-d H:i:s');
					$data['addip'] = ip();
					return $this->mysql->insert('account_bank',$data);
				}			
			}
		}
		else
		{
			return 'no param user_id in bank';	
		}
	}
}
?>