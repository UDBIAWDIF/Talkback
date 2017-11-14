<?php
function log_message($code,$msg)
{
    if(DEBUG){
        $logFile = __DIR__ . '/../Logs/'.date('Ymd', time()).'.log';
        $log_file = $logFile;
        if (!is_file($log_file)) {
            touch($log_file);
            chmod($log_file, 0622);
        }
        $msg = $msg . "\n";
        file_put_contents((string)$logFile, $code.' '.date('Y-m-d H:i:s') . ' ' . 'pid:'. posix_getpid() . ' ' . $msg, FILE_APPEND | LOCK_EX);
    }
}
//毫秒
function getMillisecond() { 
    list($s1, $s2) = explode(' ', microtime()); 
    return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000); 
} 