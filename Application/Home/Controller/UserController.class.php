<?php
/***************************************************************************
 *
 * Copyright (c) 2017 beishanwen.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * Created by PhpStorm.
 * User: strick
 * Date: 2017/12/11
 * Time: 下午4:11
 */

namespace Home\Controller;

use Home\Service\UserService;
use Util\ErrCodeUtils;
use Util\ResponseUtils;
use Util\ParamsUtils;

class UserController extends OnlineController
{
    /**
     * @brief 获取当前登录用户信息
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function getSessionUser()
    {
        ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $obj = new UserService();
        $ret = $obj->getSessionUser();

        return ResponseUtils::json($ret['code'], $ret['data']);
    }

    /**
     * @brief 获取用户信息
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function getUser()
    {
        ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $obj = new UserService();
        $ret = $obj->getUser();

        return ResponseUtils::json($ret['code'], $ret['data']);
    }

    /**
     * @brief 查找特定某个用户信息
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function searchUser()
    {
        ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $obj = new UserService();
        $ret = $obj->searchUser();

        return ResponseUtils::json($ret['code'], $ret['data']);
    }

    /**
     * @brief 关注或者取消关注
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function follow()
    {
        ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $obj = new UserService();
        $ret = $obj->follow();

        return ResponseUtils::json($ret['code'], $ret['data']);
    }

    /**
     * @brief 修改资料
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function modifyProfile()
    {
        ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $obj = new UserService();
        $ret = $obj->modifyProfile();

        return ResponseUtils::json($ret['code'], $ret['data']);
    }
}
