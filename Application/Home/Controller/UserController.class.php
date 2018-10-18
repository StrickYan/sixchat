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

namespace Home\Controller;

use Util\ErrCodeUtils;
use Util\ResponseUtils;
use Util\ParamsUtils;
use Util\UploadImgUtils;

class UserController extends BaseController
{
    /**
     * @brief 获取当前登录用户信息
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function getSessionUser()
    {
        $params = ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $map = array(
            "user_name" => $_SESSION['user_name'],
        );
        $retData = D('User')->getUser($map);
        // var_dump($retData);exit;

        if (false === $retData || count($retData) != 1) {
            return ResponseUtils::json(ErrCodeUtils::FAILED);
        }
        return ResponseUtils::json(ErrCodeUtils::SUCCESS, $retData[0]);
    }

    /**
     * @brief 获取用户信息
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function getUser()
    {
        $params = ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $retData = D('User')->getUser($params);
        // var_dump($retData);exit;

        if ($retData === false) {
            return ResponseUtils::json(ErrCodeUtils::FAILED);
        }
        return ResponseUtils::json(ErrCodeUtils::SUCCESS, $retData);
    }

    /**
     * @brief 查找特定某个用户信息
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function searchUser()
    {
        $params = ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $map = array(
            'user_name' => $params['search_name'],
        );
        $userInfo = D('User')->searchUser($map);
        if (false === $userInfo) {
            return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
        } else if (empty($userInfo)) {
            return ResponseUtils::json(ErrCodeUtils::SUCCESS, array());
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
            return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
        } else if (count($result)) {
            $ret['is_follow'] = 1; // 已关注
        }

        return ResponseUtils::json(ErrCodeUtils::SUCCESS, $ret);
    }

    /**
     * @brief 关注或者取消关注
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function follow()
    {
        $params = ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

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
            return ResponseUtils::json(ErrCodeUtils::PARAMS_INVALID);
        }
        if (false === $ret) {
            return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
        }

        return ResponseUtils::json(ErrCodeUtils::SUCCESS, array());
    }

    /**
     * @brief 修改资料
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function modifyProfile()
    {
        $params = ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        if (empty($params['profile_name']) && empty($params['profile_sex'])
            && empty($params['profile_region']) && empty($params['profile_whatsup'])
            && empty($_FILES['profile_upfile']['tmp_name'])) {
            return ResponseUtils::json(ErrCodeUtils::PARAMS_INVALID);
        }

        // 查询用户名是否存在
        $condition = array(
            'user_id' => array('neq', $params['session_user_id']),
            'user_name' => array('eq', $params['profile_name']),
        );
        $ret = D('User')->searchUser($condition);
        if (false === $ret) {
            return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
        } else if (!empty($ret)) {
            $response = array(
                'isSuccess' => false,
                'msg' => '该用户名已存在',
            );
            return ResponseUtils::json(ErrCodeUtils::SUCCESS, $response);
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
                return ResponseUtils::json($uploadResult['code'], array(), $uploadResult['msg']);
            }

            // 有图片上传且上传成功返回图片名
            // 上传成功后进行修改数据库图片路径操作
            $updateData['avatar'] = $uploadResult['data']['image_name'];
        }

        $ret = D('User')->updateUser($map, $updateData);
        if (false === $ret) {
            return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
        }

        // 更新 session name
        $_SESSION['user_name'] = $params['profile_name'];

        $response = array(
            'isSuccess' => true,
        );

        return ResponseUtils::json(ErrCodeUtils::SUCCESS, $response);
    }
}
