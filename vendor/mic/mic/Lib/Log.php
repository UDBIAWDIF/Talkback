<?php
namespace Mic\Lib;
class Log{
    
    public static $logFile;
    public static function log($code,$msg)
    {
        if(DEBUG){
        if (empty(self::$logFile)) {
            self::$logFile = __DIR__ . '/../Logs/'.date('Ymd', time()).'.log';
        }
        $log_file = (string)self::$logFile;
        if (!is_file($log_file)) {
            touch($log_file);
            chmod($log_file, 0622);
        }
        $msg = $msg . "\n";
        file_put_contents((string)self::$logFile, $code.' '.date('Y-m-d H:i:s') . ' ' . 'pid:'. posix_getpid() . ' ' . $msg, FILE_APPEND | LOCK_EX);
    }
    }
}