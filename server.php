<?php
use Workerman\Worker;
require_once "../Workerman/Autoloader.php";
//保存所有用户
$allUsers=[];
//有连接时调用
function connect($connection){
    //接收get参数
    $connection->onWebSocketConnect=function ($connection,$http_header){
        global $allUsers;
        //保存当前用户到用户列表
        $allUsers[$connection->id]=['username'=>$_GET['username']];
        //保存当前用户名到当前连接的$connection对象上
        $connection->username=$_GET['username'];
        //给所有客户端发消息
        sendToAll([
            'username'=>$connection->username,
            'content' =>'加入了聊天室',
            'datetime'=>date('Y-m-d H:i'),
            'allUsers'=>$allUsers,
        ]);
    };
};
//接收消息
function message($connection,$data){
    //转发消息给所有客户端
    sendToAll([
        'username'=>$connection->username,
        'content' =>$data,
        'datetime'=>date('Y-m-d H:i'),
    ]);
};
//当有客户端断开连接就从数组中删除
function close($connection){
    global $allUsers;
    //删除当前退出的用户，并且给所有用户发信息
    unset($allUsers[$connection->id]);
    sendToAll([
        'username'=>$connection->username,
        'content' =>'离开了聊天室',
        'datetime'=>date('Y-m-d H:i'),
        'allUsers'=>$allUsers
    ]);
};
function sendToAll($data){
    global  $worker;
    if(is_array($data)){
        $data=json_encode($data);
    }
    //循环所有客户端
    foreach ($worker->connections as $c){
        $c->send($data);
    }
}
//实例化Worker类对象
$worker=new Worker('websocket://0.0.0.0:8686');
//设置进程数
$worker->count=1;
//设置回调函数
$worker->onConnect='connect';
$worker->onMessage='message';
$worker->onClose='close';
//运行
Worker::runAll();