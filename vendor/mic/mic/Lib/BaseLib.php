<?php
namespace Mic\Lib;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use GatewayWorker\Lib\DbConnection;
class BaseLib{
    
    public $db;
    public static $logFile;
    public function __construct() {
        $this->db = new DbConnection(HOST, '3306', DBUSER, DBPASSWORD,DBNAME);
    }
    protected function guid(){

            mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = "";//chr(45);// "-"
            $uuid = //chr(123)// "{"
                    substr($charid, 0, 8).$hyphen
                    .substr($charid, 8, 4).$hyphen
                    .substr($charid,12, 4).$hyphen
                    .substr($charid,16, 4).$hyphen
                    .substr($charid,20,12);// "}"
            return $uuid;

    }
    protected function getUniqId()
    {
        $insert_id = $this->db
                ->query("REPLACE INTO worker_sequence(`name`) VALUES ('a')");
        $id = $this->db
                ->query("SELECT LAST_INSERT_ID() as id");
        return $id[0]["id"];
    }
    
}

