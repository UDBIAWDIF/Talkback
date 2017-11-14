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
use \Workerman\Worker;
use \Workerman\WebServer;
use \GatewayWorker\Gateway;
use \GatewayWorker\BusinessWorker;
use \Workerman\Autoloader;

$worker = new \stdClass();
// bussinessWorker 进程
$worker->w = new BusinessWorker();
// worker名称
$worker->w->name = 'jsonWorker';
// bussinessWorker进程数量
$worker->w->count = 4;
// 服务注册地址
$worker->w->registerAddress = '127.0.0.1:1211';

$worker->t = new BusinessWorker();
// worker名称
$worker->t->name = 'timerWorker';
// bussinessWorker进程数量
$worker->t->count = 1;
// 服务注册地址
$worker->t->registerAddress = '127.0.0.1:1211';
// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START')) {
    Worker::runAll();
}

