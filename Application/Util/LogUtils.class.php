<?php
/***************************************************************************
 *
 * Copyright (c) 2017 classmateer.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file Application/Home/Common/CLog.class.php
 * @author 1275330626(com@qq.com)
 * @date 2017/08/17 03:22:39
 * @brief 二次封装 Monolog
 *
 **/

namespace Util;

use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;

class LogUtils
{
    /*
     * Log Levels
     * Monolog supports the logging levels described by RFC 5424.
     * DEBUG (100): Detailed debug information.
     * INFO (200): Interesting events. Examples: User logs in, SQL logs.
     * NOTICE (250): Normal but significant events.
     * WARNING (300): Exceptional occurrences that are not errors. Examples: Use of deprecated APIs, poor use of an API, undesirable things that are not necessarily wrong.
     * ERROR (400): Runtime errors that do not require immediate action but should typically be logged and monitored.
     * CRITICAL (500): Critical conditions. Example: Application component unavailable, unexpected exception.
     * ALERT (550): Action must be taken immediately. Example: Entire website down, database unavailable, etc. This should trigger the SMS alerts and wake you up.
     * EMERGENCY (600): Emergency: system is unusable.
     * */

    /**
     * @brief 以天为单位 打印日志到文件
     * @param string | $logLevel 日志等级
     * @param string | $msg 日志信息
     * @param array | $data 打印的数据
     */
    public static function _log($logLevel, $msg = '', $data = array())
    {
        $logger = new Logger(LOG_NAME);
        $handler = (new RotatingFileHandler(LOG_PATH . LOG_FILE_NAME, MAX_LOG_FILES));
        $logger->pushHandler($handler);
        $logger->{$logLevel}($msg, $data);
    }

    public static function debug($msg = '', $data = array())
    {
        self::_log("debug", $msg, $data);
    }

    public static function info($msg = '', $data = array())
    {
        self::_log("info", $msg, $data);
    }

    public static function notice($msg = '', $data = array())
    {
        self::_log("notice", $msg, $data);
    }

    public static function warning($msg = '', $data = array())
    {
        self::_log("warning", $msg, $data);
    }

    public static function error($msg = '', $data = array())
    {
        self::_log("error", $msg, $data);
    }

    public static function critical($msg = '', $data = array())
    {
        self::_log("critical", $msg, $data);
    }

    public static function emergency($msg = '', $data = array())
    {
        self::_log("emergency", $msg, $data);
    }
}
