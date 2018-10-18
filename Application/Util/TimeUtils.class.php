<?php
/**
 * Created by PhpStorm.
 * User: strick
 * Date: 2017/8/6
 * Time: 5:00
 */

namespace Util;

class TimeUtils
{
    /**
     * 时间转换函数
     * @param $time
     * @return false|string
     */
    public static function tranTime($time)
    {
        $fullTime = date("M j, Y H:i", $time);
        $time = time() - $time;
        if ($time < 60 * 2) {
            $str = '1 min ago ';
        } elseif ($time < 60 * 60) {
            $min = floor($time / 60);
            $str = $min . ' mins ago ';
        } elseif ($time < 60 * 60 * 24) {
            $h = floor($time / (60 * 60));
            if ($h == 1) {
                $str = '1 hour ago ';
            } else {
                $str = $h . ' hours ago ';
            }
        } elseif ($time < 60 * 60 * 24 * 7) {
            $d = floor($time / (60 * 60 * 24));
            if ($d == 1) {
                $str = 'yesterday ';
            } else {
                $str = $d . ' day(s) ago ';
            }
        } else {
            $str = $fullTime;
        }
        return $str;
    }
}
