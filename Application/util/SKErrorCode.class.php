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
    const MSG_SUCCESS = "Succeeded";
    const MSG_FAILED  = "Failed";

    public static function getErrorMsg($code)
    {
        switch ($code) {
            case self::FAILED:
                $errorMsg = self::MSG_FAILED;
                break;
            case self::SUCCESS:
                $errorMsg = self::MSG_SUCCESS;
                break;
            default:
                $errorMsg = "";
        }
        return $errorMsg;
    }
}