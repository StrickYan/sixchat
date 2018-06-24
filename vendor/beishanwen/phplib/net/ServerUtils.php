<?php
/***************************************************************************
 *
 * Copyright (c) 2018 beishanwen.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file ServerUtils.php
 * @author strickyan@beishanwen.com
 * @date 2018/05/01 17:45:23
 * @brief $_SERVER 工具包
 */

namespace beishanwen\phplib\net;

class ServerUtils
{
    /**
     * @brief 获取基础 url，http协议+域名+端口
     * @author strickyan@beishanwen.com
     * @return string url
     */
    public static function getBaseUrl()
    {
        $protocol = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
        return $protocol . $_SERVER['HTTP_HOST'];
    }
}
