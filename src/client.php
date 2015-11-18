<?php

require __DIR__.'/../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();

$dnsResolverFactory = new React\Dns\Resolver\Factory();
$dns = $dnsResolverFactory->createCached('8.8.8.8', $loop);
$connector = new React\SocketClient\Connector($loop, $dns);

for ($i=0; $i<100; $i++) {
    $connector->create('127.0.0.1', 1337)->then(function (React\Stream\Stream $stream) {
        //$stream->write('id');
        $stream->on('data', function ($data, React\Stream\Stream $stream) {
            echo $data, PHP_EOL;
            $stream->close();
        });
    });
}

$loop->run();