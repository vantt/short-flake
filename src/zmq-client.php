<?php

require __DIR__.'/../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$context = new React\ZMQ\Context($loop);
$socket = $context->getSocket(ZMQ::SOCKET_REQ);
$socket->connect('tcp://127.0.0.1:1337');

$socket->on('message', function ($msg) {
    echo $msg, PHP_EOL;
});

$socket->send('id');

$loop->run();