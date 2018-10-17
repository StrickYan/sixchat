<?php
/***************************************************************************
 *
 * Copyright (c) 2018 beishanwen.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file ResponseUtils.php
 * @author strick@beishanwen.com
 * @date 2018/05/01 17:45:23
 * @brief
 */

namespace util;

class ResponseUtils
{
    /**
     * @brief json返回，统一必须有 code, msg, data 三个字段
     * @param $code : 错误码
     * @param $data : 待返回的数据
     * @param $msg : 错误信息
     * @return string json json格式字符串
     */
    public static function json($code, $data = array(), $msg = '')
    {
        if (empty($msg)) {
            $msg = ErrCodeUtils::getErrMsg($code);
        }
        $ret = array(
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        );
        $ret = json_encode($ret);
        if (!empty($_GET['callback'])) {
            // jsonp
            echo $_GET['callback'] . '(' . $ret . ')';
        } else {
            // json
            echo $ret;
        }
        return true;
    }

    /**
     * @brief 数组返回，统一必须有 code, msg, data 三个字段
     * @author strick@beishanwen.com
     * @param $code : 错误码
     * @param $data : 待返回的数据
     * @param $msg : 错误信息
     * @return array $ret
     */
    public static function arrayRet($code, $data = array(), $msg = '')
    {
        if (empty($msg)) {
            $msg = ErrCodeUtils::getErrMsg($code);
        }
        if (false === $msg) {
            // Bingo_Log::fatal("error code is not defined, data[" . $code . "]");
            $code = ErrCodeUtils::UN_DEFINED_ERR_CODE;
            $msg = ErrCodeUtils::getErrMsg($code);
            $data = array();
        }
        $ret = array(
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        );
        return $ret;
    }
}
