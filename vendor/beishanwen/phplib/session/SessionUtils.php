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

namespace beishanwen\phplib\session;

use beishanwen\phplib\eds\EdsUtils;

class SessionUtils
{
    const SESSION_COOKIE_EXPIRE = 6400;
    const SESSION_COOKIE_NAME = '_beishanwen_';

    /**
     * @desc 根据session生成分布式cookie，用户从一台机器A登录后，请求到另外一台机器B时需要用到这个cookie然后解密，然后把解密的session设置到B机器
     * @author strickyan@beishanwen.com
     * @param $session_info
     * @return string $cookieStr
     */
    public static function sessionInfo2cookieString($session_info)
    {
        $user_id = $session_info['user_id'];
        $user_name = $session_info['user_name'];
        $str = $user_id . '#' . $user_name;
        $new_str = EdsUtils::authCode($str, "ENCODE", EdsUtils::KEY, self::SESSION_COOKIE_EXPIRE);
        return $new_str;
    }

    /**
     * @desc 根据算法把cookie string 解密成session 对应的用户信息
     * @author strickyan@beishanwen.com
     * @param string $str_cookie
     * @return boolean | array $session_info
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

        $user_id = $arr[0];
        $user_name = $arr[1];
        if (empty($user_id) || empty($user_name)) {
            return false;
        }

        $session_info = array(
            'user_id' => $user_id,
            'user_name' => $user_name,
        );
        return $session_info;
    }

    /**
     * @desc 获取函数cookieString2sessionInfo 解码出来的session info
     * @author strickyan@beishanwen.com
     * @param null
     * @return boolean $session
     */
    public static function getSessionUserId()
    {
        $session_info = self::cookieString2sessionInfo($_COOKIE[self::SESSION_COOKIE_NAME]);
        if (false === $session_info) {
            return null;
        }
        return $session_info['user_id'];
    }

    /**
     * @desc 获取函数cookieString2sessionInfo 解码出来的session info
     * @author strickyan@beishanwen.com
     * @param null
     * @return boolean $session
     */
    public static function getSessionUserName()
    {
        $session_info = self::cookieString2sessionInfo($_COOKIE[self::SESSION_COOKIE_NAME]);
        if (false === $session_info) {
            return null;
        }
        return $session_info['user_name'];
    }

    /**
     * @brief 检查是否登录
     * @author strickyan@beishanwen.com
     * @param $user_id
     * @return boolean
     */
    public static function checkUserIfLogin($user_id = '')
    {
        if (!empty($user_id)) {
            return true;
        }
        return false;
    }
}
