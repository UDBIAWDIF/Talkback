<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

use \GatewayWorker\Lib\Gateway;
use Mic\Lib\Token;
use Workerman\Lib\Timer;
use Mic\Lib\User;
use Mic\Lib\Log;
/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{
    public static $db;
    public static function onWorkerStart($worker)
    {
        self::$db = new GatewayWorker\Lib\DbConnection(HOST, '3306', DBUSER, DBPASSWORD,DBNAME);
        $User = new User(self::$db);
        
        if($worker->name=="timerWorker"){
            Timer::add(10, function() use($worker,$User){
                //Gateway::sendToUid("88ADE65AC2FE662002BEC20902DD47E3",array("data"=>print_r($worker->id.$worker->name,TRUE)));
                //Gateway::sendToUid("88ADE65AC2FE662002BEC20902DD47E3",array("data"=>"haha".$worker->id));
                $pushMsg = $User->getPushMsg();
                foreach ($pushMsg as $k){
                    if(Gateway::isUidOnline($k['receiver'])){
                        $User->updatePushMsg($k['receiver'],$k['sender'],"pushMsg");
                        $k?Gateway::sendToUid($k['receiver'], array('data'=>array('type'=>$k['type'],'content'=>$k['content'],'sender'=>$k['sender'],'group_id'=>$k['group_id']))):'';
                    }  else {
                        
                    }
                }
                });
        }
    }
    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     * 
     * @param int $client_id 连接id
     */
    public static function onConnect($client_id) {
        log_message('debug', time().$client_id.'已上线');
        // 向当前client_id发送数据
        Gateway::sendToClient($client_id, array('status'=>'1111','data'=>$client_id,'rspcode'=>'','msg'=>"Hello {$client_id}"));
        // 向所有人发送
        //Gateway::sendToAll(array('status'=>'1111','data'=>'','rspcode'=>'','msg'=>"{$client_id} is login"));
    }
    
   /**
    * 当客户端发来消息时触发
    * @param int $client_id 连接id
    * @param mixed $request 具体消息
    */
   public static function onMessage($client_id, $request) {
        
        //检查token
        $verifyToken = Token::verifyToken($request['token'], $request['user_id'],self::$db);
        if($verifyToken['status'] !=='1111')
        {
            Gateway::sendToClient($client_id, $verifyToken);
            return;
        }
        // 向所有人发送
        if($request['action'] == 'bind' ){
            switch ($request['bindto'])
            {
                case "user":
                    Gateway::bindUid($client_id, $request['user_id']);
                    Gateway::sendToClient($client_id,  array('status'=>'1111','data'=>'','rspcode'=>'','msg'=>'成功'));
                    break;
                case "group":
                    $cid = Gateway::getClientIdByUid($request['user_id']);
                    $User = new User(self::$db);
                    $result = $User->addGroup($request);
                    if($result['status'] == '1111'){
                        Gateway::joinGroup($cid[0],$request['group_id']);
                        Gateway::sendToClient($cid[0],array('status'=>'1111','data'=>array("group_id"=>$request['group_id']),'rspcode'=>'','msg'=>'成功'));
                    }else{
                        Gateway::sendToClient($cid[0],array('status'=>'0000','data'=>'','rspcode'=>$result['rspcode'],'msg'=>$result['info']));
                    }
            }
        }
        //type 1:文字,2:表情,3:图片,4:语音,5:经纬度
        if($request['action'] == 'sendmsg'){
            switch ($request['sendto'])
            {
                case "user":
                    $User = new User(self::$db);
                    Gateway::sendToUid($client_id,array('status'=>'1111','data'=>'','rspcode'=>'','msg'=>'成功'));
                    Gateway::sendToUid($request['to_user_id'], array('data'=>$request['content'],'type'=>$request['type']));
                    break;
                case "group":
                    Gateway::sendToUid($client_id,array('status'=>'1111','data'=>'','rspcode'=>'','msg'=>'成功'));
                    Gateway::sendToGroup($request['to_group_id'], array('data'=>$request['content'],'type'=>$request['type']));
                    break;
            }
        }
        if($request['action'] == 'creategroup'){
            Gateway::joinGroup($client_id, $request['group_id']);
        }
        if($request['action'] == 'leavegroup'){
            Gateway::leaveGroup($client_id, $request['group_id']);
        }
   }    
    
   
   /**
    * 当用户断开连接时触发
    * @param int $client_id 连接id
    */
   public static function onClose($client_id) {
       // 向所有人发送 
       GateWay::sendToAll("$client_id logout");
   }
}
