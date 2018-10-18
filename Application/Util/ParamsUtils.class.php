<?php

/**
 * @desc 参数操作类
 * @author strick@beishanwen.com
 * @date 2018/10/10 10:00:00
 */

namespace Util;

class ParamsUtils
{
    private static $_params = array();
    private static $isLocked = false;

    /**
     * @desc 主要获取入参的执行函数
     * @param $actionName
     * @return array $arrInput
     * @author strick@beishanwen.com
     * @throws
     */
    public static function execute($actionName = '')
    {
        // 公共参数
        $params['time'] = time();
        $params['session_user_id'] = $_SESSION['user_id'];
        $params['session_user_name'] = $_SESSION['user_name'];

        switch ($actionName) {
            case "Auth/register":
            case "Auth/login":
                $params['id'] = $_POST['id'];
                $params['password'] = $_POST['password'];
                break;

            case "Auth/logout":
                break;

            case "User/getSessionUser":
                break;

            case "User/getUser":
                $params['user_id'] = $_POST['user_id'];
                break;

            case "User/searchUser":
                $params['search_name'] = $_POST['search_name'];
                break;

            case "User/follow":
                $params['operation_follow'] = $_POST['operation_follow'];
                $params['follow_id'] = $_POST['follow_id'];
                $params['followed_id'] = $_POST['followed_id'];
                break;

            case "User/modifyProfile":
                $params['profile_name'] = $_POST['profile_name_box'];
                $params['profile_sex'] = $_POST['profile_sex_box'];
                $params['profile_region'] = $_POST['profile_region_box'];
                $params['profile_whatsup'] = $_POST['profile_whatsup_box'];
                break;

            default:
                break;
        }

        // 参数类型转换
        self::$_params = array_map("self::checkInputString", $params);

        LogUtils::info("uri=$actionName params=" . serialize(self::$_params));
        return self::$_params;
    }

    /**
     * @desc 获取所有入参或指定入参
     * @author strick@beishanwen.com
     * @param string $strRecord
     * @param string $defVal
     * @return string | array $arrInput or string $param
     */
    public static function get($strRecord = '', $defVal = '')
    {
        if ($strRecord == '') {
            return self::$_params;
        }
        if (!isset(self::$_params[$strRecord])) {
            return $defVal;
        } else {
            return self::$_params[$strRecord];
        }
    }

    /**
     * @desc 添加参数
     * @author strick@beishanwen.com
     * @param string $key
     * @param string/array/annyType $value
     * @param int/boolean $forceInsert
     * @return boolean true/false
     */
    public static function add($key, $value, $forceInsert = 0)
    {
        if (self::$isLocked) {
            LogUtils::warning("params have been locked, can not add.");
            return false;
        }
        if ('' == $key && !$forceInsert) {
            LogUtils::error("add param key is empty");
            return false;
        }
        if (isset(self::$_params[$key])) {
            LogUtils::error("this param exist. key[$key]");
            return false;
        }
        self::$_params[$key] = $value;
        return true;
    }

    /**
     * @desc 修改参数
     * @author strick@beishanwen.com
     * @param string $key
     * @param string/array/annyType $value
     * @return boolean true/false
     */
    public static function edit($key, $value)
    {
        if (self::$isLocked) {
            LogUtils::warning("params have been locked, can not edit.");
            return false;
        }
        if ($value == self::$_params[$key]) {
            return true;
        }
        $oldKey = 'o_' . $key;
        self::$_params[$oldKey] = self::$_params[$key];
        self::$_params[$key] = $value;
        return true;
    }

    /**
     * @desc 删除参数
     * @author strick@beishanwen.com
     * @param string $key
     * @return boolean true/false
     */
    public static function del($key)
    {
        if (self::$isLocked) {
            LogUtils::warning("params have been locked, can not del.");
            return false;
        }
        if (isset(self::$_params[$key])) {
            unset(self::$_params[$key]);
        }
        return true;
    }

    /**
     * @desc 锁定 param
     * @author strick@beishanwen.com
     * @return null
     */
    public static function locked()
    {
        self::$isLocked = true;
        return true;
    }

    /**
     * @brief 防止 sql 注入
     * @author strick@beishanwen.com
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
                $value = htmlspecialchars($value);
            }
        }

        return $value;
    }
}
