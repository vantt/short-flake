<?php
require __DIR__ . '/../vendor/autoload.php';

$context = new ZMQContext();
$socket  = $context->getSocket(ZMQ::SOCKET_REQ);
$socket->connect('tcp://127.0.0.1:1337');

$socket->send('id');
$uuid = $socket->recv();

$socket->disconnect('tcp://127.0.0.1:1337');

echo $uuid, PHP_EOL;


