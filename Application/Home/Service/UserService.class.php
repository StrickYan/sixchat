<?php
/***************************************************************************
 *
 * Copyright (c) 2017 classmateer.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * Created by PhpStorm.
 * User: strick
 * Date: 2017/12/11
 * Time: 下午4:11
 */

namespace Home\Service;

use Util\ErrCodeUtils;
use Util\ResponseUtils;
use Util\ParamsUtils;

class UserService extends BaseService
{
    /**
     * 登录API
     * @param string $id 用户名
     * @param string $password 密码
     * @return array
     */
    public function login($id, $password)
    {
        $condition = array(
            'user_name' => $id,
        );
        $userInfo = D('User')->searchUser($condition);
        // 该用户不存在
        if (empty($userInfo)) {
            return ResponseUtils::arrayRet(ErrCodeUtils::USER_NOT_EXIST);
        }

        // 用户存在，保存用户名cookie
        setcookie("id", "$id", time() + 60 * 60 * 24 * 7);

        $condition = array(
            'user_name' => $id,
            'password' => md5($password),
        );
        $userInfo = D('User')->searchUser($condition);

        // 密码错误
        if (empty($userInfo)) {
            return ResponseUtils::arrayRet(ErrCodeUtils::ERR_PASSWORD);
        }

        // 登录成功
        // 保存密码cookie
        setcookie("password", "$password", time() + 60 * 60 * 24 * 7, "/auth", "sixchat.classmateer.com");

        session_start();
        $_SESSION = $userInfo;

        return ResponseUtils::arrayRet(ErrCodeUtils::SUCCESS, $userInfo);
    }

    /**
     * 注销
     */
    public function logout()
    {
        session_destroy();
        setcookie("password", "", time() - 3600, "/auth", "sixchat.classmateer.com");
    }

    /**
     * 注册
     * @param $id
     * @param $password
     * @return int | array
     */
    public function register($id, $password)
    {
        $condition = array(
            'user_name' => $id,
        );
        $userInfo = D('User')->searchUser($condition);
        // 该账号已存在
        if (!empty($userInfo)) {
            return ResponseUtils::arrayRet(ErrCodeUtils::USER_NAME_EXIST);
        }

        $model = M();
        $model->startTrans();

        // 注册成功 添加新用户
        $insertData = array(
            'user_name' => $id,
            'password' => md5($password),
            'register_time' => date("Y-m-d H:i:s"),
        );
        $newUserId = D('User')->addUser($insertData);
        if (false === $newUserId) {
            $model->rollback();
            return ResponseUtils::arrayRet(ErrCodeUtils::SYSTEM_ERROR);
        }

        // 注册时自动关注自己
        $insertData = array(
            'user_id' => $newUserId,
            'friend_id' => $newUserId,
            'time' => date("Y-m-d H:i:s"),
        );
        $ret = D('Friend')->addFriend($insertData);
        if (false === $ret) {
            $model->rollback();
            return ResponseUtils::arrayRet(ErrCodeUtils::SYSTEM_ERROR);
        }

        // 注册时自动和官方账号建立双向关系
        $insertData = array(
            'user_id' => $newUserId,
            'friend_id' => 18,
            'time' => date("Y-m-d H:i:s"),
        );
        $ret = D('Friend')->addFriend($insertData);
        if (false === $ret) {
            $model->rollback();
            return ResponseUtils::arrayRet(ErrCodeUtils::SYSTEM_ERROR);
        }

        $insertData = array(
            'user_id' => 18,
            'friend_id' => $newUserId,
            'time' => date("Y-m-d H:i:s"),
        );
        $ret = D('Friend')->addFriend($insertData);
        if (false === $ret) {
            $model->rollback();
            return ResponseUtils::arrayRet(ErrCodeUtils::SYSTEM_ERROR);
        }

        $model->commit();

        return ResponseUtils::arrayRet(ErrCodeUtils::SUCCESS);
    }
}
