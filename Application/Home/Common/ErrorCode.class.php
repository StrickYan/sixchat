<?php
/**
 * Created by PhpStorm.
 * User: strick
 * Date: 2017/8/7
 * Time: 20:35
 */

namespace Home\Common;

class ErrorCode
{
    const SUCCESS = 0;
    const FAILED = -1;

    public static function getErrorMsg($code)
    {
        switch ($code)
        {
            case -1:
                $errorMsg = "Failed";
                break;
            case 0:
                $errorMsg = "Successed";
                break;
            default:
                $errorMsg = "";
        }
        return $errorMsg;
    }
}