<?php
require __DIR__ . '/../vendor/autoload.php';

use ShortFlake\IdGenerator;

$loop         = React\EventLoop\Factory::create();
$id_generator = new IdGenerator($loop);

$context = new React\ZMQ\Context($loop);
$socket  = $context->getSocket(ZMQ::SOCKET_REP);
$socket->bind('tcp://127.0.0.1:1337');


$socket->on('message', function ($message) use ($id_generator, $socket) {
    if ('id' == $message) {
        $id_generator->computeId()->then(function ($uuid) use ($socket) {
            $socket->send($uuid);
        });
    }
});


$loop->run();