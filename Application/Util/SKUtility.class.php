<?php
/**
 * Created by PhpStorm.
 * User: strick
 * Date: 2017/8/6
 * Time: 5:00
 */

namespace Util;

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
     * @param int $retCode 返回代码
     * @param array $retData 返回的数据
     * @param string $retMsg 提示信息
     * @brief 异步返回json数据
     */
    public static function returnData($retCode, $retData = array(), $retMsg = '')
    {
        $retData = isset($retData) ? $retData : array();
        if (empty($retMsg)) {
            $retMsg = SKErrorCode::getErrorMsg($retCode);
        }
        $result = json_encode(array(
            'retCode' => $retCode,
            'retData' => $retData,
            'retMsg' => $retMsg,
        ));
        LogUtils::info($result);
        echo $result . "\n";
        exit;
    }
}