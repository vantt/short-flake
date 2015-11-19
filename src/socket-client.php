<?php

require __DIR__.'/../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();

$dnsResolverFactory = new React\Dns\Resolver\Factory();
$dns = $dnsResolverFactory->createCached('8.8.8.8', $loop);
$connector = new React\SocketClient\Connector($loop, $dns);

function doConnect($connector, $loop) {
    return function () use ($connector, $loop) {
        $connector->create('127.0.0.1', 1337)->then(function (React\Stream\Stream $stream) {
            $stream->on('data', function ($data, React\Stream\Stream $stream) {
                echo $data, PHP_EOL;
                $stream->close();
            });
        });

        $loop->futureTick(doConnect($connector, $loop));
    };
}

$loop->nextTick(doConnect($connector, $loop));

$loop->run();