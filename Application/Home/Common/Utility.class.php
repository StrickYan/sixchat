<?php
/**
 * Created by PhpStorm.
 * User: strick
 * Date: 2017/8/6
 * Time: 5:00
 */

namespace Home\Common;

class Utility
{
    public static function qstr($s)
    {
        $x = "'" . str_replace("'", "''", $s) . "'";
        return $x;
    }

    /**
     * @brief 异步返回json数据
     * @param int | $code 返回代码
     * @param array | $data 返回的数据
     * @return string | $msg 提示信息
     */
    public static function returnData($code, $data = array(), $msg = '')
    {
        $data = isset($data) ? $data : array();
        if (empty($msg)) {
            $msg = ErrorCode::getErrorMsg($code);
        }
        Log::pushNotice('retCode', $code);
        Log::pushNotice('retMsg', $msg);
        echo json_encode(array(
            'retCode' => $code,
            'retMsg' => $msg,
            'retData' => $data,
        ));
        exit;
    }

}