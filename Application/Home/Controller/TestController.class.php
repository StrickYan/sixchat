<?php

namespace Home\Controller;

use util\SKErrorCode;
use util\SKUtility;
use util\SKLog;
use Think\Controller;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;

class TestController extends Controller
{
    public function index()
    {
        echo "hello world";
    }

    public function test()
    {
        $data = array(
            'name' => "kobe",
            'age' => 22,
        );
        SKUtility::returnData(SKErrorCode::SUCCESS, $data, $msg = '');
    }

    public function testMonolog()
    {
        // Create the logger
        $logger = new Logger('sixchat');
        // Now add some handlers
        // $logger->pushHandler(new StreamHandler(LOG_PATH . '/my_app.log', Logger::DEBUG));

        $maxFiles = 0;
        $handler = (new RotatingFileHandler(LOG_PATH . '/sixchat.log', $maxFiles));
        $logger->pushHandler($handler);

        $logger->debug('My logger is now ready', array('username' => 'Seldaek'));
        $logger->info('My logger is now ready', array('username' => 'Seldaek'));
        $logger->notice('My logger is now ready', array('username' => 'Seldaek'));
        $logger->warning('My logger is now ready', array('username' => 'Seldaek'));
        $logger->error('My logger is now ready', array('username' => 'Seldaek'));
        $logger->critical('My logger is now ready', array('username' => 'Seldaek'));
        $logger->emergency('My logger is now ready', array('username' => 'Seldaek'));
    }

    public function testCLog()
    {
        SKLog::warning("test log");
    }

}