<?php
/***************************************************************************
 *
 * Copyright (c) 2018 beishanwen.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file ErrCodeUtils.php
 * @author strick@beishanwen.com
 * @date 2018/05/01 17:45:23
 * @brief
 */

namespace Util;

class ErrCodeUtils
{
    const SUCCESS = 0;
    const FAILED = 1;
    const PARAMS_INVALID = 2;
    const SYSTEM_ERROR = 3;
    const UN_DEFINED_ERR_CODE = 999; // 错误码不存在

    // 项目错误码从1000开始，之前为系统保留错误码

    /**
     * 错误码对应的错误信息变量
     */
    private static $errMsg = array(
        self::SUCCESS => 'success',
        self::FAILED => 'failed',
        self::PARAMS_INVALID => 'params invalid, check it please',
        self::SYSTEM_ERROR => 'system busy',
        self::UN_DEFINED_ERR_CODE => 'undefined error code',
    );

    /**
     * @brief 通过错误码获取对应的错误信息
     * @author strick@beishanwen.com
     * @param int $errCode
     * @return string $errMsg
     */
    public static function getErrMsg($errCode)
    {
        if (isset(self::$errMsg[$errCode])) {
            return self::$errMsg[$errCode];
        }
        return false;
    }
}
