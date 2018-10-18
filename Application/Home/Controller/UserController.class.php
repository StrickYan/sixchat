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

use util\ErrCodeUtils;
use util\ResponseUtils;

class UserController extends BaseController
{
    public function getSessionUser()
    {
        $map = array(
            "user_name" => $_SESSION["name"],
        );
        $retData = D('User')->getUser($map);
        // var_dump($retData);exit;

        if (false === $retData || count($retData) != 1) {
            return ResponseUtils::json(ErrCodeUtils::FAILED);
        }
        return ResponseUtils::json(ErrCodeUtils::SUCCESS, $retData[0]);
    }

    public function getUser()
    {
        $map = $_REQUEST;
        $retData = D('User')->getUser($map);
        // var_dump($retData);exit;

        if ($retData === false) {
            return ResponseUtils::json(ErrCodeUtils::FAILED);
        }
        return ResponseUtils::json(ErrCodeUtils::SUCCESS, $retData);
    }

    /**
     * 查找用户资料
     */
    public function searchUser()
    {
        $searchName = htmlspecialchars($_REQUEST['search_name']);
        $userName = $_SESSION['name'];
        $map['user_name'] = $searchName;
        $list = D('User')->searchUser($map);
        if (false === $list) {
            return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
        }

        $data = array(
            'avatar' => $list['avatar'],
            'user_name' => $list['user_name'],
            'sex' => $list['sex'],
            'region' => $list['region'],
            'whatsup' => $list['whatsup'],
            'is_follow' => 0,
        );

        foreach ($this->obj->getUserId($userName, $searchName) as $k => $val) {
            $userId = $val["reply_id"];
            $friendId = $val["replyed_id"];

            $map1['user_id'] = $userId;
            $map1['friend_id'] = $friendId;
            $result = D('Friend')->where($map1)->find();
            if ($result) {
                $data['is_follow'] = 1; // 已关注
            }

            $data['follow_id'] = $userId;
            $data['followed_id'] = $friendId;
        }

        return ResponseUtils::json(ErrCodeUtils::SUCCESS, $data);
    }

    /**
     * 关注或者取消关注
     **/
    public function follow()
    {
        $operationFollow = htmlspecialchars($_REQUEST['operation_follow']);
        $data = array(
            'user_id' => htmlspecialchars($_REQUEST['follow_id']),
            'friend_id' => htmlspecialchars($_REQUEST['followed_id']),
            'time' => date("Y-m-d H:i:s"),
        );

        $ret = false;

        if ($operationFollow == 1) {
            $ret = D('Friend')->addFriend($data); // 添加关注记录
        } else if ($operationFollow == 0) {
            $where = array(
                'user_id' => $data['user_id'],
                'friend_id' => $data['friend_id'],
            );
            $ret = D('Friend')->deleteFriend($where); // 取消关注
        }
        if (false === $ret) {
            return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
        }

        $ret = array(
            "is_success" => ($ret === false ? 0 : 1),
        );

        return ResponseUtils::json(ErrCodeUtils::SUCCESS, $ret);
    }

    /**
     * 修改资料
     */
    public function modifyProfile()
    {
        $profileNameBox = isset($_POST['profile_name_box']) ? htmlspecialchars(trim($_POST['profile_name_box'])) : ''; //获取文本内容
        $profileSexBox = isset($_POST['profile_sex_box']) ? htmlspecialchars($_POST['profile_sex_box']) : '';
        $profileRegionBox = isset($_POST['profile_region_box']) ? htmlspecialchars($_POST['profile_region_box']) : '';
        $profileWhatsupBox = isset($_POST['profile_whatsup_box']) ? htmlspecialchars($_POST['profile_whatsup_box']) : '';

        if (!$profileNameBox && !$profileSexBox && !$profileRegionBox && !$profileWhatsupBox && empty($_FILES['profile_upfile']['tmp_name'])) {
            return ResponseUtils::json(ErrCodeUtils::PARAMS_INVALID);
        }

        $response = array();
        $destinationFolder = "avatar_img/"; //上传文件路径
        $inputFileName = "profile_upfile";
        $maxWidth = 200;
        $maxHeight = 200;
        $uploadResult = $this->obj->uploadImg($destinationFolder, $inputFileName, $maxWidth, $maxHeight); //上传头像
        if ($uploadResult) {
            //有图片上传且上传成功返回图片名
            $image_name = $uploadResult;
            //上传成功后进行修改数据库图片路径操作
            $map['user_name'] = $_SESSION['name'];
            $data = array(
                'avatar' => $image_name,
            );
            D('User')->updateUser($map, $data);
            unset($map);
        }
        $userName = $_SESSION["name"];
        foreach ($this->obj->getUserId($userName, $userName) as $k => $val) {
            $userId = $val["reply_id"];

            $condition = array(
                'user_name' => array('eq', $profileNameBox),
                'user_id' => array('neq', $userId),
            );
            $ret = D('User')->searchUser($condition);
            if (false === $ret) {
                return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
            }

            if (!empty($ret)) {
                $response['isSuccess'] = false;
                $response['msg'] = '该用户名已存在';
                return ResponseUtils::json(ErrCodeUtils::SUCCESS, $response);
            }

            $map['user_id'] = $userId;
            $data = array(
                'user_name' => $profileNameBox,
                'sex' => $profileSexBox,
                'region' => $profileRegionBox,
                'whatsup' => $profileWhatsupBox,
            );
            $ret = D('User')->updateUser($map, $data);
            if (false === $ret) {
                return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
            }
        }
        $_SESSION["name"] = $profileNameBox;
        $response['isSuccess'] = true;

        return ResponseUtils::json(ErrCodeUtils::SUCCESS, $response);
    }
}
