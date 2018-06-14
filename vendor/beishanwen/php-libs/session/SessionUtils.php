<?php
/***************************************************************************
 *
 * Copyright (c) 2018 beishanwen.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file SessionUtils.php
 * @author strickyan@beishanwen.com
 * @date 2018/05/01 17:45:23
 * @brief 工具包
 */

namespace beishanwen\php\libs\session;

use beishanwen\php\libs\eds\EdsUtils;

class SessionUtils
{
    const SESSION_COOKIE_EXPIRE = 86400;
    const SESSION_COOKIE_NAME = '_beishanwen_';

    /**
     * @desc 根据session生成分布式cookie，用户从一台机器A登录后，请求到另外一台机器B时需要用到这个cookie然后解密，然后把解密的session设置成B机器
     * @author strickyan@beishanwen.com
     * @return string $cookieStr
     */
    public static function sessionInfo2cookieString()
    {
        $str = $_SESSION['user_id'] . '#' . $_SESSION['user_name'];
        $new_str = EdsUtils::authCode($str, "ENCODE", EdsUtils::KEY, self::SESSION_COOKIE_EXPIRE);
        return $new_str;
    }

    /**
     * @desc 根据算法把cookie string 解密成session 对应的用户信息
     * @author strickyan@beishanwen.com
     * @param string $str_cookie
     * @return boolean $session_info
     */
    public static function cookieString2sessionInfo($str_cookie)
    {
        if ('' == $str_cookie) {
            return false;
        }

        $str = EdsUtils::authCode($str_cookie, "DECODE", EdsUtils::KEY);
        $arr = explode('#', $str);
        if (count($arr) != 2) {
            return false;
        }
        $_SESSION['user_id'] = $arr[0];
        $_SESSION['user_name'] = $arr[1];

        return true;
    }

    /**
     * @desc 获取函数cookieString2sessionInfo 解码出来的session info
     * @author strickyan@beishanwen.com
     * @param null
     * @return boolean $session
     */
    public static function getSessionUserId()
    {
        if (empty($_SESSION['user_id']) && false === self::cookieString2sessionInfo($_COOKIE[self::SESSION_COOKIE_NAME])) {
            return null;
        }
        return $_SESSION['user_id'];
    }

    /**
     * @desc 获取函数cookieString2sessionInfo 解码出来的session info
     * @author strickyan@beishanwen.com
     * @param null
     * @return boolean $session
     */
    public static function getSessionUserName()
    {
        if (empty($_SESSION['user_name']) && false === self::cookieString2sessionInfo($_COOKIE[self::SESSION_COOKIE_NAME])) {
            return null;
        }
        return $_SESSION['user_name'];
    }

    /**
     * @brief 检查是否登录
     * @author strickyan@beishanwen.com
     * @return boolean
     */
    public static function checkUserIfLogin()
    {
        if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
            return true;
        }
        return false;
    }
}
