<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../libs/ratchet/vendor/autoload.php';
require_once __DIR__ . '/../libs/class.tweet_helper.php';
require_once 'Jittli/Twitterwall.php';

$Redis = new Redis;
$Redis->connect('127.0.0.1');

$loop   = React\EventLoop\Factory::create();
$Twitterwall = new Jittli\Twitterwall($Redis);
$loop->addPeriodicTimer(60, array($Twitterwall, 'onEveryMinute'));

// Listen for the web server to make a ZeroMQ push after an ajax request
$context = new React\ZMQ\Context($loop);
$pull = $context->getSocket(ZMQ::SOCKET_PULL);
$pull->bind('tcp://127.0.0.1:5555'); // Binding to 127.0.0.1 means the only client that can connect is itself
$pull->on('message', array($Twitterwall, 'onApiEvent'));

// Set up our WebSocket server for clients wanting real-time updates
$webSock = new React\Socket\Server($loop);
$webSock->listen(8080, '0.0.0.0'); // Binding to 0.0.0.0 means remotes can connect
$webServer = new Ratchet\Server\IoServer(
	new Ratchet\WebSocket\WsServer(
		new Ratchet\Wamp\WampServer(
			$Twitterwall
		)
	),
	$webSock
);
$loop->run();