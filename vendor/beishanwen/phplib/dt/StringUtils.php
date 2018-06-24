<?php
/***************************************************************************
 *
 * Copyright (c) 2018 beishanwen.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file StringUtils.php
 * @author strickyan@beishanwen.com
 * @date 2018/05/01 17:45:23
 * @brief 工具包
 */

namespace beishanwen\phplib\dt;

class StringUtils
{
    /**
     * @brief 防止 sql 注入
     * @author strickyan@beishanwen.com
     * @param string $value
     * @return boolean
     */
    public static function checkInputString($value)
    {
        if (is_string($value)) {
            $value = trim($value);
            if (is_numeric($value)) {
                if (false === strpos($value, '.')) {
                    $value = intval($value);
                } else {
                    $value = floatval($value);
                }
            } else {
                $value = addslashes($value);
                // 适用各个 PHP 版本的用法
                if (get_magic_quotes_gpc()) {
                    $value = stripslashes($value);
                }
            }
        }

        return $value;
    }
}
