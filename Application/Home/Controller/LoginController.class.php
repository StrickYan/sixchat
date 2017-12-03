<?php
/***************************************************************************
 *
 * Copyright (c) 2017 classmateer.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file LoginController.class.php
 * @author 1275330626(com@qq.com)
 * @date 2017/08/06 03:22:39
 * @brief
 *
 **/

namespace Home\Controller;

use Think\Controller;

class LoginController extends Controller
{
    /**
     * 登录函数
     */
    public function index()
    {
        if (isset($_SESSION['name']) && !empty($_SESSION['name'])) {
            // 判断是否已经登录
            $this->redirect('/moments/index');
            return;
        }

        $id = null;
        $tempId = null; // 存放cookie获取的id
        $tempPassword = null; // 存放cookie获取的password
        $password = null;
        $namePlaceholder = "Name";
        $passwordPlaceholder = "Password";
        $headImage = "default_head.jpg"; //默认头像
        if (isset($_COOKIE["user"])) {
            //获取cookie
            $tempId = $_COOKIE["user"];
        }
        if (isset($_COOKIE["password"])) {
            //获取cookie
            $tempPassword = $_COOKIE["password"];
        }
        $id = trim($_POST['id']);
        if ($id == null) {
            //为空则读入cookie值
            $id = $tempId;
        }
        $password = trim($_POST['password']);
        if ($password == null) {
            //为空则读入cookie值
            $password = $tempPassword;
        }
        if ($id != null && $password != null) {
            $obj = new SixChatApi2016Controller();
            $result = $obj->login($id, $password);
            if ($result == -1) {
                //用户名不存在
                $id = null;
                $password = null;
                $namePlaceholder = "该用户不存在";
            } else if ($result == -2) {
                //密码错误
                $password = null;
                $passwordPlaceholder = "密码错误";
            } else if (!$result) {
                //登录成功
                $this->redirect('/moments/index');
                return;
            }
        }
        if ($id != null) {
            //加载用户头像
            $condition2['user_name'] = $id;
            $avatar = D('User')->getUserAvatar($condition2);
            if ($avatar) {
                $headImage = $avatar;
            }
        }

        $array['head_image'] = $headImage;
        $array['text_placeholder'] = $namePlaceholder;
        $array['id'] = $id;
        $array['pw_placeholder'] = $passwordPlaceholder;
        $array['password'] = $password;
        $this->assign($array); //模板赋值
        $this->display(); //模板渲染
    }
}
