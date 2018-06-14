<?php
/***************************************************************************
 *
 * Copyright (c) 2018 beishanwen.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file TimeUtils.php
 * @author strickyan@beishanwen.com
 * @date 2018/05/01 17:45:23
 * @brief 工具包
 */

namespace beishanwen\php\libs\time;

class TimeUtils
{
    /**
     * @brief 指定时区的日期转服务器所在时区时间戳
     * @author strickyan@beishanwen.com
     * @param string $str 日期格式字符串
     * @param int $strTimezone 第一个参数日期所在时区, +8表示东八区, -8表示西八区
     * @return int
     */
    public static function dateToUnixTimeWithTimezone($str, $strTimezone)
    {
        return strtotime($str) + date('Z') - $strTimezone * 3600;
    }

    /**
     * @brief 服务器所在时区时间戳转指定时区的日期
     * @author strickyan@beishanwen.com
     * @param int $time 时间戳
     * @param int $strTimezone 目标 date 所在时区, +8表示东八区, -8表示西八区
     * @param string 日期格式
     * @return string
     */
    public static function unixTimeToDateWithTimezone($time, $strTimezone, $dateType = 'Y-m-d H:i:s')
    {
        return date($dateType, $time - date('Z') + $strTimezone * 3600);
    }

    /**
     * @brief 时间戳转指定时区的日期
     * @author strickyan@beishanwen.com
     * @param string $data 日期
     * @param string $format 格式
     * @return boolean
     */
    public static function checkDateTime($data, $format = 'Y-m-d H:i:s')
    {
        if (date($format, strtotime($data)) === $data) {
            return true;
        }
        return false;
    }
}