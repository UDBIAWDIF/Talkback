<?php
namespace Mic\Lib;
use Mic\Lib\Log;
class Route{
    /**
     * 
     * @param String $method
     * @return array
     */
    public static function getRoute($param){
        
        if(!is_string($param)){
            Log::log('error',"路由参数不合法".print_r($param,TRUE));
            return array('status'=>"4002",'data'=>'','rspcode'=>'4002',"msg"=>"路由参数不合法");
        }
        
        $arr = explode('.', $param);
        if($arr[1] != '')
        {
            $class = $arr[1];
            
        }
        $arr[1]?$class = $arr[1]:'';
        $arr[2]?$method = $arr[2]:'';
        if($class && $method){
            Log::log('debug',"路由获取成功".print_r(array('class'=>$class,'method'=>$method),TRUE));
            return array('status'=>"1111","data"=>array('class'=>$class,'method'=>$method),'rspcode'=>'','msg'=>"成功");
        }  else {
            Log::log('error',"路由参数不合法");
            return array('status'=>"4003",'data'=>'','rspcode'=>'4003',"msg"=>"路由参数不合法");
        }
        
        
    } 
    
}
