<?php
/***************************************************************************
 *
 * Copyright (c) 2018 beishanwen.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file ResponseUtils.php
 * @author strickyan@beishanwen.com
 * @date 2018/05/01 17:45:23
 * @brief
 */

namespace beishanwen\php\libs\net;

class ResponseUtils
{
    /**
     * @brief json返回，统一必须有 errCode, errMsg, data三个字段
     * @param $arrayRet : 待返回的数据
     * @return string json json格式字符串
     */
    public static function json($arrayRet)
    {
        $ret = json_encode($arrayRet);

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
     * @brief 数组返回，统一必须有 errCode, errMsg, data三个字段
     * @author strickyan@beishanwen.com
     * @param $errCode : 错误码
     * @param $data : 待返回的数据
     * @return array $ret
     */
    public static function arrayRet($errCode, $data = array())
    {
        $errMsg = ErrCodeUtils::getErrMsg($errCode);
        if (false === $errMsg) {
            // Bingo_Log::fatal("errCode is not defined. errCode[" . $errCode . "]");
            $errCode = ErrCodeUtils::UN_DEFINED_ERR_CODE;
            $errMsg = ErrCodeUtils::getErrMsg($errCode);
            $data = array();
        }
        $ret = array(
            'errCode' => $errCode,
            'errMsg' => $errMsg,
            'data' => $data,
        );
        return $ret;
    }
}
