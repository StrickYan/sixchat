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

namespace Home\Service;

use Util\ErrCodeUtils;
use Util\ResponseUtils;
use Util\ParamsUtils;
use Util\UploadImgUtils;

class UserService extends BaseService
{
    private $arrInput;

    /**
     * @brief 初始化
     * @author strick@beishanwen.com
     * @param void
     * @return void
     */
    public function _initialize()
    {
        $this->arrInput = ParamsUtils::get();
    }

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
     * @brief 注销
     * @author strick@beishanwen.com
     * @param void
     * @return void
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
     * @return array
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

    /**
     * @brief 获取当前登录用户信息
     * @author strick@beishanwen.com
     * @param void
     * @return array
     */
    public function getSessionUser()
    {
        $map = array(
            "user_name" => $_SESSION['user_name'],
        );
        $retData = D('User')->getUser($map);
        // var_dump($retData);exit;

        if (false === $retData || count($retData) != 1) {
            return ResponseUtils::arrayRet(ErrCodeUtils::FAILED);
        }
        return ResponseUtils::arrayRet(ErrCodeUtils::SUCCESS, $retData[0]);
    }

    /**
     * @brief 获取用户信息
     * @author strick@beishanwen.com
     * @param void
     * @return array
     */
    public function getUser()
    {
        $params = $this->arrInput;

        $retData = D('User')->getUser($params);
        // var_dump($retData);exit;

        if ($retData === false) {
            return ResponseUtils::arrayRet(ErrCodeUtils::FAILED);
        }
        return ResponseUtils::arrayRet(ErrCodeUtils::SUCCESS, $retData);
    }

    /**
     * @brief 查找特定某个用户信息
     * @author strick@beishanwen.com
     * @param void
     * @return array
     */
    public function searchUser()
    {
        $params = $this->arrInput;

        $map = array(
            'user_name' => $params['search_name'],
        );
        $userInfo = D('User')->searchUser($map);
        if (false === $userInfo) {
            return ResponseUtils::arrayRet(ErrCodeUtils::SYSTEM_ERROR);
        } else if (empty($userInfo)) {
            return ResponseUtils::arrayRet(ErrCodeUtils::SUCCESS, array());
        }

        $ret = array(
            'avatar' => $userInfo['avatar'],
            'user_name' => $userInfo['user_name'],
            'sex' => $userInfo['sex'],
            'region' => $userInfo['region'],
            'whatsup' => $userInfo['whatsup'],

            'is_follow' => 0,
            'follow_id' => $params['session_user_id'],
            'followed_id' => $userInfo['user_id'],
        );

        // 查询是否关注
        $condition = array(
            'user_id' => $params['session_user_id'],
            'friend_id' => $userInfo['user_id'],
        );
        $result = D('Friend')->where($condition)->find();
        if (false === $result) {
            return ResponseUtils::arrayRet(ErrCodeUtils::SYSTEM_ERROR);
        } else if (count($result)) {
            $ret['is_follow'] = 1; // 已关注
        }

        return ResponseUtils::arrayRet(ErrCodeUtils::SUCCESS, $ret);
    }

    /**
     * @brief 关注或者取消关注
     * @author strick@beishanwen.com
     * @param void
     * @return array
     */
    public function follow()
    {
        $params = $this->arrInput;

        if (1 == $params['operation_follow']) {
            $insertData = array(
                'user_id' => $params['follow_id'],
                'friend_id' => $params['followed_id'],
                'time' => date("Y-m-d H:i:s"),
            );
            $ret = D('Friend')->addFriend($insertData); // 添加关注记录
        } else if (0 == $params['operation_follow']) {
            $condition = array(
                'user_id' => $params['follow_id'],
                'friend_id' => $params['followed_id'],
            );
            $ret = D('Friend')->deleteFriend($condition); // 取消关注
        } else {
            return ResponseUtils::arrayRet(ErrCodeUtils::PARAMS_INVALID);
        }
        if (false === $ret) {
            return ResponseUtils::arrayRet(ErrCodeUtils::SYSTEM_ERROR);
        }

        return ResponseUtils::arrayRet(ErrCodeUtils::SUCCESS, array());
    }

    /**
     * @brief 修改资料
     * @author strick@beishanwen.com
     * @param void
     * @return array
     */
    public function modifyProfile()
    {
        $params = $this->arrInput;

        if (empty($params['profile_name']) && empty($params['profile_sex'])
            && empty($params['profile_region']) && empty($params['profile_whatsup'])
            && empty($_FILES['profile_upfile']['tmp_name'])) {
            return ResponseUtils::arrayRet(ErrCodeUtils::PARAMS_INVALID);
        }

        // 查询用户名是否存在
        $condition = array(
            'user_id' => array('neq', $params['session_user_id']),
            'user_name' => array('eq', $params['profile_name']),
        );
        $ret = D('User')->searchUser($condition);
        if (false === $ret) {
            return ResponseUtils::arrayRet(ErrCodeUtils::SYSTEM_ERROR);
        } else if (!empty($ret)) {
            $response = array(
                'isSuccess' => false,
                'msg' => '该用户名已存在',
            );
            return ResponseUtils::arrayRet(ErrCodeUtils::SUCCESS, $response);
        }

        // 更新用户信息
        $map = array(
            'user_id' => $params['session_user_id'],
        );
        $updateData = array(
            'user_name' => $params['profile_name'],
            'sex' => $params['profile_sex'],
            'region' => $params['profile_region'],
            'whatsup' => $params['profile_whatsup'],
        );

        // 头像上传
        if (!empty($_FILES['profile_upfile']['tmp_name'])) {
            $destinationFolder = "avatar_img/"; //上传文件路径
            $inputFileName = "profile_upfile";
            $maxWidth = 200;
            $maxHeight = 200;
            $uploadResult = UploadImgUtils::uploadImg($destinationFolder, $inputFileName, $maxWidth, $maxHeight);
            if (ErrCodeUtils::SUCCESS !== $uploadResult['code']) {
                return ResponseUtils::arrayRet($uploadResult['code'], array(), $uploadResult['msg']);
            }

            // 有图片上传且上传成功返回图片名
            // 上传成功后进行修改数据库图片路径操作
            $updateData['avatar'] = $uploadResult['data']['image_name'];
        }

        $ret = D('User')->updateUser($map, $updateData);
        if (false === $ret) {
            return ResponseUtils::arrayRet(ErrCodeUtils::SYSTEM_ERROR);
        }

        // 更新 session name
        $_SESSION['user_name'] = $params['profile_name'];

        $response = array(
            'isSuccess' => true,
        );

        return ResponseUtils::arrayRet(ErrCodeUtils::SUCCESS, $response);
    }
}
