<?php
namespace Mic\Lib;
use Mic\Lib\Log;
use Mic\Lib\BaseLib;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class User extends BaseLib{

    public function __construct() {
        parent::__construct();
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
        $account = $this->getUniqId();
        $password = $params['password'];
        if(!$this->isMobile($telephone))
        {
            return array('status'=>"4008",'rspcode'=>'4008',"msg"=>"手机号码不正确");
        }
        if($this->getUserId($telephone)){
            return array('status'=>"4009",'rspcode'=>'4009',"msg"=>"该用户已注册");
        }
        if(!$this->getUserName($params['name']))
        {
            return array('status'=>'4010','rspcode'=>'4010','msg'=>'用户呢称不能为空');
        }
        $insert_id = $this->db->insert('worker_user')->cols(array(
            'id'=>$account,
            'telephone'=>$telephone,
            'password'=> md5($password),
            'name'=>$params['name'],
                ))->query();
        $lstid = $this->db->LastInsertId();
        if($lstid)
        {
            return array('status'=>"1111","data"=>['user_id'=>$lstid,'name'=>$params['name']],'msg'=>'成功');
            
        }
    }
    public function login($params)
    {
        log_message("debug", "登陆方法开始==>");
        $telephone = $params['telephone'];
        $password = md5($params['password']);
        
        if(!$this->isMobile($telephone))
        {
            return array('status'=>"4001",'rspcode'=>'4001',"msg"=>"手机号码不正确");
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
            $token_str = sha1($this->guid());
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
            return array('status'=>"1111","data"=>$data,'msg'=>'成功');
        } else {
            return array('status'=>"4005",'rspcode'=>'4005','msg'=>"手机号或密码错误");
        }
        
    }
    public function forgetPassword()
    {
        
        
    }
    public function getGroups($params)
    {
        
        $groups = array();
        $groups_founder = $this->db
                ->select('gm.*,g.founder')
                ->from('worker_group AS g')
                ->leftJoin('worker_groupmember AS gm','gm.groupId = g.groupId')
                ->where('g.founder=:founder AND g.category = "group"')
                ->bindValues(
                        array(
                            'founder'=>$params['user_id']
                        ))
                ->query();
        $groups_user=  $this->db
                ->select('gm.*,g.founder')
                ->from('worker_group AS g')
                ->leftJoin('worker_groupmember AS gm','gm.groupId = g.groupId')
                ->where('gm.user_id=:user_id AND g.category = "group"')
                ->bindValues(
                        array(
                            'user_id'=>$params['user_id']
                        ))
                ->query();   
        foreach ($groups_founder as $v)
        {
            $groups[$v['founder']][] = $v;
        }
        foreach ($groups_user as $v)
        {
            $groups[$v['founder']][] = $v;
        }
        return array('status'=>'1111','data'=>$groups,'msg'=>'成功');
    }
    public function getFriends($params)
    {
        //Log::log('debug', "=====>>>>".print_r($params,TRUE));
        $type = "friend";
        $rt_friend = array();
        $rt_founder = $this->db
                ->select("gm.user_id as friend_id,u.name as friend_name")
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
      $rt_friend = $this->db
                ->select("g.founder as friend_id,u.name as friend_name")
                ->from('worker_group as g')
                ->where('g.category=:category AND gm.user_id=:user_id AND g.is_effect=1')
                ->bindValues(
                        array(
                            "category"=>$type,
                            "user_id"=>$params['user_id'],
                        ))
                ->leftJoin('worker_groupmember as gm','gm.groupId = g.groupId')
                ->leftJoin('worker_user as u','u.id = gm.user_id')
                ->query();
        //Log::log('debug', "=====>>>>".print_r($rt,TRUE));
        while ($arr = array_shift($rt_founder))
        {
            $rt_friend[] = $arr;
        }
        return $rt_friend?array('status'=>'1111','data'=>$rt_friend,'msg'=>'成功'):array('status'=>'0000','rspcode'=>'4012','msg'=>'您还没有添加好友');
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
                $rt = $this->getBottleWithRS($search_user['data']['id'], $params['user_id'])?array('status'=>'0000','rspcode'=>'4013','msg'=>'操作失败'):($search_user['data']?$this->insertBottle($unid, $params, $search_user['data'],"addFriend"):array('status'=>'0000','rspcode'=>'4013','msg'=>'操作失败'));
                break;
            case "agreeMsg":
                $rt = $this->updatePushMsg($params['user_id'], $params['sender'], 'agreeMsg');
                break;
            case "repulseMsg":
                $rt = $this->updatePushMsg($params['user_id'], $params['sender'], 'repulseMsg');
                break;
            case "browseMsg":
                $rt = $this->updatePushMsg($params['user_id'], $params['sender'], 'browseMsg');
                break;
        } 
        return $rt;
    }
    /**
     * @todo 添加好友到群聊组
     * @param type $params
     * @return type
     */
    public function addGroup($params)
    {
        Log::log('debug', "添加成员开始====>\n接收参数====>".print_r($params,TRUE));
        $user_id = $params['user_id'];
        $friend_id  =$params['friend_id'];
        $group_id = $params['group_id'];
        $friends = $params['friends'];//成员
        if(!$this->isGroup($group_id)){
            log_message('debug', "群聊不存在");
            return array('status'=>'0000','rspcode'=>'4014','msg'=>'群聊不存在');;
        }
        
        if($params['opt']=='addMsg'&&!$this->isFounder($user_id,$group_id))
        {
            log_message('debug', "没有创建者权限");
            return array('status'=>'0000','rspcode'=>'4015','msg'=>'没有创建者权限');
        }
        if($params['opt'] == 'addMsg'){
            foreach ($friends as $v){
                if(!$this->isFriend($v['friend_id'], $user_id))
                {
                    log_message('debug', "请先添加为好友");
                    return array('status'=>'0000','rspcode'=>'4016','msg'=>'请先添加为好友');
                }
            }
        }
        if($params['opt'] == 'agreeMsg'){
            if(!$this->isFriend($friend_id, $user_id))
            {
                log_message('debug', "请先添加为好友");
                return array('status'=>'0000','rspcode'=>'4017','msg'=>'请先添加为好友');
            }
        }
        if($params['opt'] == 'agreeMsg' && $this->inGroup($group_id, $user_id))
        {
            log_message('debug', "您已加入群聊");
            return array('status'=>'0000','rspcode'=>'4018','msg'=>'您已加入群聊');
        }
        $unid = $this->getUniqId();
        //$mobile = $params['mobile'];
        switch ($params['opt'])
        {
            case "addMsg":
                $this->insertBottle($unid, $params, $friends,"addGroup");
                return array('status'=>'1111','data'=>'','msg'=>'成功');
                break;
            case "agreeMsg":
                $ck = $this->db->query("update worker_bottle set status=2 "
                        . "where status!=3 AND receiver='".$user_id."'"
                        . "and sender = '".$friend_id."' "
                        . "and type='addGroup' "
                        . "and group_id = '".$group_id."'");
                
                if($ck){
                    try{
                    $unid = $this->getUniqId();
                    $rt = $this->db
                            ->insert("worker_groupmember")
                            ->cols(array(
                                'gid'=> $unid,
                                'user_id' => $user_id,
                                'groupId' => $group_id,
                            ))
                            ->query();
                            return array('status'=>'1111','data'=>'','msg'=>'成功');
                    }  catch (Exception $e)
                    {
                            return array('status'=>'0000','rspcode'=>'4019','msg'=>'操作失败');
                    }
                }else{
                   
                    return array('status'=>'0000','rspcode'=>'4020','msg'=>'您已添加过');
                }
                
                break;
            case "repulseMsg":
                $this->db->query("update worker_bottle set status=2 "
                        . "where status!=2 AND receiver='".$user_id."'"
                        . "and sender = '".$friend_id."' "
                        . "and type='addGroup' "
                        . "and group_id = '".$group_id."'");
                break;
            case "browseMsg":
                $this->db->query("update worker_bottle set status=1 "
                        . "where status=0 AND receiver='".$user_id."'"
                        . "and sender = '".$friend_id."' "
                        . "and type='addGroup' "
                        . "and group_id = '".$group_id."'");
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
        $this->db->setLastInsertIdNames(array('groupId'));
        $rs = $this->db
                ->insert("worker_group")
                ->cols(array(
                    "groupId"=>$group_id,
                    "category"=>"group",
                    "founder"=>$params['user_id'],
                ))
                ->query();
        
        $lstid = $this->db->LastInsertId();
        if($lstid)
        { 
            return array('status'=>'1111','data'=>array('group_id'=>$lstid),'msg'=>'成功');
        }else{
            return array('status'=>'0000','rspcode'=>'5001','msg'=>"系统错误,请稍后再试");
        }

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
        return !empty($user)?array('status'=>'1111','data'=>$user,'msg'=>'成功'):array('status'=>'0000','rspcode'=>'4021','msg'=>'用户不存在');
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
        return empty($rs)?array('status'=>'0000','msg'=>'没有新的消息'):array('status'=>"1111",'data'=>$rs,'msg'=>'成功'); 
    }
    private function insertBottle($unid,$params,$receiver,$opt)
    {
        $now = time();
        if(!$opt){
            return array('status'=>'0000','rspcode'=>'5006','msg'=>'系统错误');
        }
        switch ($opt)
        {
            case "addFriend":
                try{
                $rt = $this->db->insert('worker_bottle')->cols(array(
                    'gid'=>$unid,
                    'timestamp'=>$now,
                    'sender'=>$params['user_id'],
                    'receiver'=>$receiver['id'],
                    'content'=>'addFriend',
                    'type'=>'addFriend',
                ))->query(); 
                $rt = array('status'=>'1111','data'=>'','msg'=>'成功');
                }catch(Exception $e)
                {
                    $rt = array('status'=>'0000','rspcode'=>'5002','msg'=>'系统错误');
                }
                break;
            //创建群聊，向被添加成员发送加入申请
            case "addGroup":
                //$receiver成员
                foreach ($receiver as $v){
                    //$unid为主键，这里需要独立生成
                    $unid = $this->getUniqId();
                    try{
                    //向通知表插入申请信息，待成员上线即可收取到申请信息
                    $rt = $this->db->insert('worker_bottle')->cols(array(
                        'gid'=>$unid,
                        'timestamp'=>$now,
                        'sender'=>$params['user_id'],
                        'receiver'=>$v['friend_id'],
                        'content'=>'addGroup',
                        'type'=>'addGroup',
                        'group_id'=>$params['group_id']
                    ))->query();
                    $rt = array('status'=>'1111','data'=>'','msg'=>'成功');
                }catch(Exception $e)
                {
                    $rt = array('status'=>'0000','rspcode'=>'5003','msg'=>'系统错误');
                }
                }
                break;
        }
        return $rt;
    }

    public function updatePushMsg($receiver,$sender,$opt,$type='addFriend')
    {
        switch ($opt){
            case "pushMsg":
                $rt = $this->db->query("update worker_bottle set keep=(keep+1) "
                        . "where status=0 and keep<20 and receiver='".$receiver."' "
                        . "and sender = '".$sender."' ");
                break;
            case "agreeMsg":
                $ck = $this->db->query("update worker_bottle set status=2 "
                        . "where status!=3 AND receiver='".$receiver."'"
                        . "and sender = '".$sender."' "
                        . "and type='addFriend'");
                
                if($ck){
                    $group_id = $this->getUniqId();
                    $this->db
                            ->insert("worker_group")
                            ->cols(array(
                                "groupId"=>$group_id,
                                "category"=>"friend",
                                "founder"=>$sender,
                            ))
                            ->query();
                    try{
                    $unid = $this->getUniqId();
                    
                    $rt = $this->db
                            ->insert("worker_groupmember")
                            ->cols(array(
                                'gid'=> $unid,
                                'user_id' => $receiver,
                                'groupId' => $group_id,
                            ))
                            ->query();
                        return array('status'=>'1111','data'=>'','msg'=>'成功');
                    }catch(Exception $e){
                        return array('status'=>'0000','rspcode'=>'5004','msg'=>'添加失败');
                    }
                }else{
                    return array('status'=>'0000','rspcode'=>'4022',"msg"=>'已是好友');
                } 
                break;
            case "repulseMsg":
                try{
                    $rt = $this->db->query("update worker_bottle set status=3 "
                        . " where status!=2 receiver='".$receiver."'"
                        . " and sender = '".$sender."' "
                        . "and type='addFriend'");
                    return array('status'=>'1111','data'=>'','msg'=>'成功');
                }catch(Exception $e){
                    return array('status'=>'0000','rspcode'=>'5004','msg'=>'操作失败');
                }
                break;
            case "browseMsg":
                try{
                $rt = $this->db->query("update worker_bottle set status=1 "
                        . " where status=0 "
                        . " and receiver='".$receiver."'"
                        . " and sender = '".$sender."' "
                        . "and type='addFriend'");
                    return array('status'=>'1111','data'=>'','msg'=>'成功');
                }catch(Exception $e){
                    return array('status'=>'0000','rspcode'=>'5005','msg'=>'操作失败');
                }
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
    /**
     * @todo 是否是创建者
     * @param integer $user_id 会员ID
     */
    private function isFounder($user_id,$group_id)
    {
        log_message("debug", __METHOD__."查询是否为创建者开始===>传参===>会员ID：".$user_id."群聊ID".$group_id);
        $founder = $this->db
                ->select('founder')
                ->from('worker_group')
                ->where("founder=:founder AND groupId=:groupId")
                ->bindValues(
                        array(
                            "founder"=>$user_id,
                            "groupId"=>$group_id
                        ))
                ->row();
        
        log_message("debug", __METHOD__."查询结果结束===>".print_r($founder,TRUE).$this->db->lastSQL());
        return $founder['founder'] == $user_id?TRUE:FALSE;
    }
    /**
     * @todo 是否互为好友
     * @param integer $friend_id 好友ID
     * @param integer $user_id 会员ID
     */
    private function isFriend($friend_id,$user_id)
    {
        $rs_user = $this->db
                ->select('u.*')
                ->from('worker_group AS g')
                ->leftJoin('worker_groupmember AS gm','gm.groupId = g.groupId')
                ->leftJoin('worker_user AS u','u.id = gm.user_id')
                ->where('g.founder=:founder AND gm.user_id=:friend_id AND category=\'friend\' AND u.is_effect=1')
                ->bindValues(
                        array(
                            'founder'=>$user_id,
                            'friend_id'=>$friend_id
                        )
                        )
                ->row();
        $rs_friend = $this->db
                ->select('u.*')
                ->from('worker_group AS g')
                ->leftJoin('worker_groupmember AS gm','gm.groupId = g.groupId')
                ->leftJoin('worker_user AS u','u.id = gm.user_id')
                ->where('g.founder=:founder AND gm.user_id=:friend_id AND category=\'friend\' AND u.is_effect=1')
                ->bindValues(
                        array(
                            'founder'=>$friend_id,
                            'friend_id'=>$user_id
                        )
                        )
                ->row();
                
        if(!empty($rs_user) || !empty($rs_friend))
        {
            return TRUE;
        }else{
            return FALSE;
        }
        
    }
    
    /**
     * @todo 是否为同一群组
     * @param integer $user_id 会员Id
     * @param integer $group_id 分组ID
     */
    private function inGroup($group_id,$user_id)
    {
        $rs = $this->db
                ->select('u.*')
                ->from('worker_group AS g')
                ->leftJoin('worker_groupmember AS gm','gm.groupId = g.groupId')
                ->leftJoin('worker_user AS u','u.id = gm.user_id')
                ->where('g.groupId=:groupId AND gm.user_id=:user_id AND g.category=\'group\' AND u.is_effect=1 AND g.is_effect=1')
                ->bindValues(
                        array(
                            'user_id'=>$user_id,
                            'groupId'=>$group_id
                        )
                        )
                ->row();
        return !empty($rs)?TRUE:FALSE;
    }
    
    private function isGroup($group_id)
    {
        $group = $this->db
                ->select('groupId')
                ->from('worker_group')
                ->where('groupId=:groupId AND is_effect = 1')
                ->bindValues(
                        array(
                            'groupId'=>$group_id
                        ))
                ->row();
        return empty($group)?FALSE:TRUE;
    }
}
