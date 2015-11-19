<?php
require __DIR__ . '/../vendor/autoload.php';

use React\Socket\Connection;
use ShortFlake\IdGenerator;

$loop         = React\EventLoop\Factory::create();
$id_generator = new IdGenerator($loop);

// id socket
$id_socket = new React\Socket\Server($loop);
$id_socket->listen(1337);
$id_socket->on('connection', function (Connection $conn) use ($id_generator) {
    $id_generator->computeId()->then(function ($uuid) use ($conn) {
        $conn->write($uuid);
    });
});

// metric socket
$metric_socket = new React\Socket\Server($loop);
$metric_socket->listen(1338);
$metric_socket->on('connection', function (Connection $conn) use ($id_generator) {
    $mem = memory_get_usage(TRUE);
    $conn->write('Generated Ids    : '. $id_generator->getTotalGeneratedIds() . PHP_EOL);
    $conn->write('Mem Usage: '. $mem . 'Bytes, '. round($mem/1024/104).'M'. PHP_EOL);
});

$loop->run();