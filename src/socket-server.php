<?php
require __DIR__.'/../vendor/autoload.php';

$loop   = React\EventLoop\Factory::create();
$socket = new React\Socket\Server($loop);

function getId($loop) {
    $deferred = new \React\Promise\Deferred();

    $loop->nextTick(function() use ($deferred) {
        $id = uniqid();
        $deferred->resolve($id);
    });

    return $deferred->promise();
}

$socket->on('connection', function (\React\Socket\Connection $conn) use ($loop) {
    getId($loop)->then(function($data) use ($conn) {
        echo memory_get_usage(true), PHP_EOL;

        $conn->write($data);
    });
});

$socket->listen(1337);

$loop->run();