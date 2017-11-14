<?php
namespace Mic\Lib;
use Mic\Lib\Log;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class User extends BaseLib{

    public function __construct($db) {
        parent::__construct($db);
    }
    public function getUserId($tel)
    {
        $result = $this->db->select("id")
                ->from("worker_user")
                ->where("telephone='".$tel."'")
                ->row();
        return empty($result)?FALSE:$result;
        
    }
    /**
     * @todo用户呢称
     */
    public function getUserName($name)
    {
        return $name == ''?FALSE:$name;
    }

    public function getUserInfo()
    {
        return "112233";
    }
    public function register($params)
    {
        $telephone = $params['telephone'];
        $account = $this->getUniqId;
        $password = $params['password'];
        
        if(!$this->isMobile($telephone))
        {
            return ['status'=>"4008","info"=>"手机号码不正确"];
        }
        
        if($this->getUserId($telephone)){
            
            return ['status'=>"4009","info"=>"该用户已注册"];
            
        }
        if(!$this->getUserName($params['name']))
        {
            return array('status'=>'4010','info'=>'用户呢称不能为空');
        }
        $insert_id = $this->db->insert('worker_user')->cols(array(
            'id'=>$account,
            'telephone'=>$telephone,
            'password'=>  md5($password),
            'name'=>$params['name'],
                ))->query();
        
        if($insert_id)
        {
            return ['status'=>"1111","data"=>$insert_id];
            
        }
    }
    public function login($params)
    {
        $telephone = $params['telephone'];
        $password = md5($params['password']);
        
        if(!$this->isMobile($telephone))
        {
            return ['status'=>"4001","info"=>"手机号码不正确"];
        }
        
        $login = $this->db
                ->select("*")
                ->from('worker_user')
                ->where('telephone=:telephone AND password =:password')
                ->bindValues(array(
                    'telephone'=>$telephone,
                    'password'=>$password,
                ))
                ->row();
        
        if(!empty($login))
        {
            $now = time();
            $token_str = md5(microtime(TRUE));
            $expire_time = $now+EXPIRE_TIME;
            $token = $this->db
                ->select("*")
                ->from('worker_token')
                ->where('user_id=\''.$login['id'].'\'')
                ->row();

            if($token['user_id']){
                $this->db
                        ->update('worker_token')
                        ->cols(array('token','expire_time'))
                        ->where('user_id=\''.$login['id'].'\'')
                        ->bindValue('token',$token_str)
                        ->bindValue('expire_time',$expire_time)
                        ->query();
            }else{
                Log::log("debug","insert====>".$token['token']);
                $this->db->insert('worker_token')->cols(array(
                    'token'=>$token_str,
                    'login_time'=>$now,
                    'expire_time'=>$expire_time,
                    'user_id'=>$login['id'],
                ))->query();
            }
            $data = array("token"=>$token_str,"expire_time"=>$expire_time,"user_id"=>$login['id']);
            $_SESSION['user'] = $data;
            return array('status'=>"1111","data"=>$data);
            
        } else {
            return ['status'=>"4005","info"=>"手机号或密码错误"];
        }
        
    }
    public function forgetPassword()
    {
        
        
    }
    public function getGroups()
    {
        
        
    }
    public function getFriends($params)
    {
        //Log::log('debug', "=====>>>>".print_r($params,TRUE));
        switch($params['type'])
        {
            case "1":
                $type = "group";
                break;
            default :
                $type="friend";
        }
        $rt = $this->db
                ->select("g.*,gm.user_id as friend_id,u.name as friend_name")
                ->from('worker_group as g')
                ->where('g.category=:category AND g.founder=:founder AND g.is_effect=1')
                ->bindValues(
                        array(
                            "category"=>$type,
                            "founder"=>$params['user_id'],
                        ))
                ->leftJoin('worker_groupmember as gm','gm.groupId = g.groupId')
                ->leftJoin('worker_user as u','u.id = gm.user_id')
                ->query();
        //Log::log('debug', "=====>>>>".print_r($rt,TRUE));
        return $rt?array('status'=>'1111','data'=>$rt):array('status'=>'0000','info'=>'没有好友记录');
    }
    public function getFriend()
    {
        
        
    }
    public function addFriend($params)
    {
//        if(!$_SESSION['user']){
//            return array('status'=>'4009','info'=>'请登陆后重试');
//        }
        Log::log('debug', "====>".print_r($params,TRUE));
        $unid = $this->getUniqId();
        $mobile = $params['mobile'];
        $search_user = $this->searchUser($mobile);
        switch ($params['opt'])
        {
            case "addMsg":
                $this->getBottleWithRS($search_user['data']['id'], $params['user_id'])?"":($search_user['data']?$this->insertBottle($unid, $params, $search_user['data'],"addFriend"):"");
                break;
            case "agreeMsg":
                $this->updatePushMsg($params['user_id'], $params['sender'], 'agreeMsg');
                break;
            case "repulseMsg":
                $this->updatePushMsg($params['user_id'], $params['sender'], 'repulseMsg');
                break;
            case "browseMsg":
                $this->updatePushMsg($params['user_id'], $params['sender'], 'browseMsg');
                break;
        } 
    }
    
    public function addGroup($params)
    {
//        if(!$_SESSION['user']){
//            return array('status'=>'4009','info'=>'请登陆后重试');
//        }
        Log::log('debug', "创建组开始====>\n接收参数====>".print_r($params,TRUE));
        $unid = $this->getUniqId();
        $mobile = $params['mobile'];
        $search_user = $this->searchUser($mobile);
        switch ($params['opt'])
        {
            case "addMsg":
                $this->getBottleWithRS($search_user['data']['id'], $params['user_id'])?"":($search_user['data']?$this->insertBottle($unid, $params, $search_user['data'],"addFriend"):"");
                break;
            case "agreeMsg":
                $this->updatePushMsg($params['user_id'], $params['sender'], 'agreeMsg');
                break;
            case "repulseMsg":
                $this->updatePushMsg($params['user_id'], $params['sender'], 'repulseMsg');
                break;
            case "browseMsg":
                $this->updatePushMsg($params['user_id'], $params['sender'], 'browseMsg');
                break;
        } 
    }    
    /**
     * @todo创建群聊
     * @param array $params 被加入群的用户
     */
    public function createGroup($params)
    {
        $group_id = $this->getUniqId();
        $this->db
                ->insert("worker_group")
                ->cols(array(
                    "groupId"=>$group_id,
                    "category"=>"group",
                    "founder"=>$params['founder'],
                ))
                ->query();
        foreach($params['user'] as $v){
            $unid = $this->getUniqId();
            $rt = $this->db
                    ->insert("worker_groupmember")
                    ->cols(array(
                        'gid'=> $unid,
                        'user_id' => $v,
                        'groupId' => $group_id,
                    ))
                    ->query();
            
        }
        return array('status'=>'1111','data'=>array('group_id'=>$group_id));
    }
    public function joinGroup()
    {
        
        
    }
    public function delFriend()
    {
        
        
    }
    public function delGroup()
    {
        
        
    }
    public function searchUser($mobile)
    {
        if($this->isMobile($mobile))
        {
            $user = $this->db
                ->select("telephone,name,online,gender,id")
                ->from('worker_user')
                ->where('telephone=\''.$mobile.'\' AND is_effect = 1')
                ->row();
        }
        return !empty($user)?array('status'=>'1111','data'=>$user):array('status'=>'0000','info'=>'用户不存在');
    }

    public function getPushMsg(){
        $msg = $this->db
                ->select("*")
                ->from('worker_bottle')
                ->where('status=0 AND keep<20')
                ->orderByASC(array('timestamp'))
                ->query();
        return empty($msg)?FALSE:$msg;
    }
    /**
     * @todo 查询是否已加好友
     * @param integer $receiver
     * @param integer $sender
     * @return array
     */
    private function getBottleWithRS($receiver,$sender)
    {
        $rs = $this->db
                ->select("*")
                ->from("worker_bottle")
                ->where("receiver=:receiver AND sender=:sender")
                ->bindValues(array(
                    "receiver"=>$receiver,
                    "sender"=>$sender,
                ))
                ->query();
        return empty($rs)?FALSE:$rs;
        
    }
    /**
     * @todo 查询添加好友信息，所有的状态为0
     * @param integer $receiver
     * @param integer $sender
     * @return array
     */
    public function getAddFriendMsg($param)
    {
        $receiver=$param['user_id'];
        $sender = $param['sender'];
        Log::log('debug', "=====>".print_r($param,TRUE));
        $rs = $this->db
                ->select("*")
                ->from("worker_bottle")
                ->where("receiver=:receiver AND sender=:sender AND status=0")
                ->bindValues(array(
                    "receiver"=>$receiver,
                    "sender"=>$sender,
                ))
                ->query();
        return empty($rs)?array('status'=>'0000','info'=>'没有新的消息'):array('status'=>"1111",'data'=>$rs); 
    }
    private function insertBottle($unid,$params,$search_user,$opt)
    {
        $now = time();
        if(!$opt){
            return FALSE;
        }
        switch ($opt)
        {
            case "addFriend":
                $rt = $this->db->insert('worker_bottle')->cols(array(
                    'gid'=>$unid,
                    'timestamp'=>$now,
                    'sender'=>$params['user_id'],
                    'receiver'=>$search_user['id'],
                    'content'=>'addFriend',
                    'type'=>'addFriend',
                ))->query(); 
                break;
            case "addGroup":
                $rt = $this->db->insert('worker_bottle')->cols(array(
                    'gid'=>$unid,
                    'timestamp'=>$now,
                    'sender'=>$params['user_id'],
                    'receiver'=>$search_user['id'],
                    'content'=>'addGroup',
                    'type'=>'addGroup',
                ))->query();
                break;
        }
        return $rt;
    }

    public function updatePushMsg($receiver,$sender,$opt,$type)
    {
        switch ($opt){
            case "pushMsg":
                $rt = $this->db->query("update worker_bottle set keep=(keep+1) "
                        . "where status=0 and keep<20 and receiver='".$receiver."' "
                        . "and sender = '".$sender."'");
                break;
            case "agreeMsg":
                $ck = $this->db->query("update worker_bottle set status=2 "
                        . "where status!=3 AND receiver='".$receiver."'"
                        . "and sender = '".$sender."'");
                
                if($ck && $type == 'friend'){
                    $group_id = $this->getUniqId();
                    $this->db
                            ->insert("worker_group")
                            ->cols(array(
                                "groupId"=>$group_id,
                                "category"=>"friend",
                                "founder"=>$sender,
                            ))
                            ->query();
                    $unid = $this->getUniqId();
                    $rt = $this->db
                            ->insert("worker_groupmember")
                            ->cols(array(
                                'gid'=> $unid,
                                'user_id' => $receiver,
                                'groupId' => $group_id,
                            ))
                            ->query();
                }  elseif($ck && $type == 'group') {
                    
                    
                    
                }
                break;
            case "repulseMsg":
                $rt = $this->db->query("update worker_bottle set status=3 "
                        . " where status!=2 receiver='".$receiver."'"
                        . " and sender = '".$sender."'");
                break;
            case "browseMsg":
                $rt = $this->db->query("update worker_bottle set status=1 "
                        . " where receiver='".$receiver."'"
                        . " and sender = '".$sender."'");
                break;    
        }

        return $rt?true:FALSE;
    }

    public  function isMobile($mobile) {
        if (!is_numeric($mobile)) {
            return false;
        }
        return preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $mobile) ? true : false;
    }
    
}
