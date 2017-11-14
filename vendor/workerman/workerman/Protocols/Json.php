<?php
namespace Workerman\Protocols;
use Mic\Lib\Encryption;
use Mic\Lib\Log;
//use Mic\Lib\Security;

class Json
{
    public static function input($recv_buffer)
    {
        Log::log('debug',"===========>".$recv_buffer);
        // 接收到的数据还不够4字节，无法得知包的长度，返回0继续等待数据
        if(strlen($recv_buffer)<2)
        {
            return 0;
        }
        // 利用unpack函数将首部4字节转换成数字，首部4字节即为整个数据包长度
        $unpack_data = unpack('ntotal_length', $recv_buffer);
        return $unpack_data['total_length'];
    }

    public static function decode($recv_buffer)
    {
        
        // 去掉首部2字节，得到包体Json数据
        $str = substr($recv_buffer, 2);
        //$str = $recv_buffer;
        Log::log("debug", "before====>".$str);
        $encryption = new Encryption;
        $result = $encryption->decrypt(
        base64_decode($str),
        array(
            'cipher' => MCRYPT_RIJNDAEL_128,
            'mode' => MCRYPT_MODE_ECB,
            'key' => SECRET_KEY,
            'hmac'=>FALSE,
	    
            )   
        );
        //$sc = new Security();
        Log::log("debug", "after====>".print_r($result,TRUE));
        // json解码
        return json_decode($result,TRUE);
    }

    public static function encode($data)
    {
        // Json编码得到包体
        $str = json_encode($data,JSON_UNESCAPED_UNICODE);
        //Log::log("debug", __METHOD__."before====>".$str);
        $encryption = new Encryption;
        $args = array(
            'handle'=>'AES-128-ECB',
            'cipher' => MCRYPT_RIJNDAEL_128,
            'mode' => MCRYPT_MODE_ECB,
            'key' => SECRET_KEY,
            'hmac'=>FALSE,
            'raw_data'=>FALSE,
            );


        $result = $encryption->encrypt(
        $str,
        $args	
        );
        //Log::log("debug", __METHOD__."after====>".$result);
        // 计算整个包的长度，首部4字节+包体字节数
        $result = base64_encode($result);
        Log::log("debug", __METHOD__."before====>".$result);
        $total_length = 2 + strlen($result);
        // 返回打包的数据
        //return $result;
        return pack('n',$total_length) . $result;
    }
}