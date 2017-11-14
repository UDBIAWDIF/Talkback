<?php
namespace Mic\Lib;
class Message extends BaseLib{
    public function saveMsg()
    {
        $mtime = getMillisecond();
        $this->db
                ->insert()
                ->cols(
                       [
                           'receiver'=>$receiver,
                           'sender'=>$sender,
                           'status'=>0,
                           'timestamp'=>$mtime,
                           'format'=>$format,
                           ''
                       ])
                ->query();
        
    }
    
}
