<?php
/**
 * Created by PhpStorm.
 * User: strick
 * Date: 2017/8/7
 * Time: 20:35
 */

namespace util;

class SKErrorCode
{
    const SUCCESS = 0;
    const FAILED = -1;

    public static function getErrorMsg($code)
    {
        switch ($code) {
            case self::FAILED:
                $errorMsg = "Failed";
                break;
            case self::SUCCESS:
                $errorMsg = "Succeeded";
                break;
            default:
                $errorMsg = "";
        }
        return $errorMsg;
    }
}