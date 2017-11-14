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
use Mic\Lib\Log;
use Mic\Lib\Route;
use Mic\Lib\Sign;
use Mic\Lib\Token;
use Mic\Lib\Encryption;
/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{
    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     * 
     * @param int $client_id 连接id
     */
    public static $db;
    public static $encryption;
    public static function onWorkerStart($worker)
    {
        //self::$db = new GatewayWorker\Lib\DbConnection(HOST, '3306', DBUSER, DBPASSWORD,DBNAME);
        self::$encryption = new Encryption;
    }
    public static function onConnect($client_id) {
        // 向当前client_id发送数据 
        //Gateway::sendToClient($client_id, "Hello $client_id\n");
        // 向所有人发送
        //Gateway::sendToAll("$client_id login\n");
    }
//    
   /**
    * 当客户端发来消息时触发
    * @param int $client_id 连接id
    * @param mixed $message 具体消息
    */
   public static function onMessage($client_id, $arg) {
        // 向所有人发送 
        Log::log("debug","解密前数据".$arg['post']['request']);
        $ecrypt_str = $arg['post']['request'];
        $result = self::$encryption->decrypt(
        base64_decode($ecrypt_str),
        array(
            'cipher' => MCRYPT_RIJNDAEL_128,
            'mode' => MCRYPT_MODE_ECB,
            'key' => SECRET_KEY,
            'hmac'=>FALSE,
	    
            )   
        );
        Log::log("debug","解密结果".print_r($result,TRUE));
        $request = json_decode($result,TRUE);
        $params = $request['data'];
        //两个免token接口

        if(!in_array($request['method'],array('com.user.login', 'com.user.register'))){
            $token = Token::verifyToken($params['token'],$params['user_id'],self::$db);
            if($token['status']!='1111'){
                self::outPut($client_id, $token);
                return;
            }
        }
        $sign = Sign::verifySign($params, $request['sign'], $request['_t']);
        if($sign['status']!="1111"){
            self::outPut($client_id, $sign);
                        return;
        }
        
        $route = Route::getRoute($request['method']);
        if($route['status']!="1111"){
            self::outPut($client_id, $route);
                        return;
        }
        $lib = ucfirst(strtolower($route['data']['class']));
        $method = $route['data']['method'];
        $data = $params;
        $class = "Mic\Lib\\".$lib;
        Log::log("debug", "{$class}{$method}".print_r($data,TRUE));
        $handle = new $class;
        $rt = $handle->$method($data);
        if($rt['status']==='1111'){
            self::outPut($client_id, array('status'=>"1111",'rspcode'=>'','data'=>$rt['data'],'msg'=>$rt['msg']));
        }  else {
            self::outPut($client_id, array('status'=>"0000",'rspcode'=>$rt['rspcode'],'data'=>'','msg'=>$rt['msg']));
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
   
   public static function outPut($client_id,$data)
   {
        //Log::log("debug", "输出的结果: 客户端client_id:".$client_id."参数:".print_r($data,TRUE));
        log_message("debug", "加密前的请求结果: 客户端client_id:".$client_id."参数:".print_r($data,TRUE));
        $args = array(
            'handle'=>'AES-128-ECB',
            'cipher' => MCRYPT_RIJNDAEL_128,
            'mode' => MCRYPT_MODE_ECB,
            'key' => SECRET_KEY,
            'hmac'=>FALSE,
            'raw_data'=>FALSE,
            );
        $result = self::$encryption->encrypt(
        json_encode($data,JSON_UNESCAPED_UNICODE),
        $args	
        );
        Log::log('debug', "加密后的请求结果:".print_r($result,TRUE));
        Gateway::sendToClient($client_id, base64_encode($result));

   }
   
}
