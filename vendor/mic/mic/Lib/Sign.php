<?php
namespace Mic\Lib;
use Mic\Lib\Log;
class Sign{
    
    //验证签名
    public static function verifySign($params, $_sign, $_t) {
	$sign = self::to_sign($params, $_t);
	if (
		32 != strlen($_sign) ||
		strtoupper($_sign) != $sign
	) {
	    Log::log('error','签名不正确：' . print_r($params, 1));
	    return array("status"=>"4001",'data'=>'','rspcode'=>'4001', "info"=>'签名不正确');
	}  else {
            return array("status"=>"1111","data"=>TRUE);
        }
    }
    public static function to_sign($str, $time) {

	if (is_string($str)) {
	    parse_str($str, $str);
	}

	if (is_array($str)) {
	    foreach ($str as $k => $v) {
		if (is_array($v)) {
		    unset($str[$k]);
		} else {
		    $str[$k] = $v . '';
		}
	    }
	}
        
        //客户端开发有难度
	ksort($str);
	$str = '' != $str ? http_build_query($str) : '';
	$str2 = trim($str) . '&time=' . $time;
	$sign = strtoupper(md5($str2));
	Log::log('debug','签名过程====>' . print_r(array('http_build_query' => $str2, 'sign' => $sign), 1));
	return $sign;
    }
}

