<?php
require __DIR__ . '/../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$context = new React\ZMQ\Context($loop);
$socket = $context->getSocket(ZMQ::SOCKET_REP);
$socket->bind('tcp://127.0.0.1:1337');



function getId($loop)
{
    $deferred = new \React\Promise\Deferred();

    $loop->nextTick(function () use ($deferred) {
        $id = uniqid();
        $deferred->resolve($id);
    });

    return $deferred->promise();
}

$socket->on('message', function ($message) use ($loop, $socket) {
    getId($loop)->then(function ($data) use ($socket) {
        echo memory_get_usage(true), PHP_EOL;

        $socket->send($data);
    });
});



$loop->run();