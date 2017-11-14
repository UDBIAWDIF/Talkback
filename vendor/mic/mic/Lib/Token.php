<?php
namespace Mic\Lib;
use Mic\Lib\Log;
class Token{
    
    public static function verifyToken($token,$user_id,$db){

        if($token == '' && strlen($token) != 32){
            Log::log("error", "code:4004,token非法");
            return array('status'=>"4004",'data'=>'','rspcode'=>'4004',"msg"=>"token非法");;
        }
        $result = $db->
                select("token,expire_time")
                ->from("worker_token")
                ->where('token=:token AND user_id = :user_id')
                ->bindValues(
                        array(
                            'token'=>$token,
                            'user_id'=>$user_id
                            )
                        )->row();
        if(empty($result)){
            Log::log("error", "code:4006,token非法");
            return array('status'=>"4006",'data'=>'','rspcode'=>'4006',"msg"=>"token非法");
        }
        if($result['expire_time'] < time() ){
            $row_count = $db->delete('worker_token')->where('token=\''.$token.'\' AND user_id = \''.$user_id.'\'')->query();
            return array('status'=>"4007",'data'=>'','rspcode'=>'4007',"msg"=>"会话过期，请重新登陆");
        }
        return array('status'=>"1111","data"=>[],'rspcode'=>'','msg'=>'成功');
    }

}
