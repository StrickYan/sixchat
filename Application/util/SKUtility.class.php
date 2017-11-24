<?php
/**
 * Created by PhpStorm.
 * User: strick
 * Date: 2017/8/6
 * Time: 5:00
 */

namespace util;

class SKUtility
{
    /**
     * @param $s
     * @return string
     * @brief e.g. abc'ced =>'abc''ced'
     */
    public static function qstr($s)
    {
        $x = "'" . str_replace("'", "''", $s) . "'";
        return $x;
    }

    /**
     * @param int $code 返回代码
     * @param array $data 返回的数据
     * @param string $msg 提示信息
     * @brief 异步返回json数据
     */
    public static function returnData($code, $data = array(), $msg = '')
    {
        $data = isset($data) ? $data : array();
        if (empty($msg)) {
            $msg = SKErrorCode::getErrorMsg($code);
        }
        //Log::pushNotice('retCode', $code);
        //Log::pushNotice('retMsg', $msg);
        echo json_encode(array(
            'retCode' => $code,
            'retMsg' => $msg,
            'retData' => $data,
        ));
        exit;
    }

}