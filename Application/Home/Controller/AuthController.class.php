<?php
/***************************************************************************
 *
 * Copyright (c) 2017 classmateer.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file AuthController.class.php
 * @author 1275330626(com@qq.com)
 * @date 2017/08/06 03:22:39
 * @brief
 *
 **/

namespace Home\Controller;

class AuthController extends BaseController
{
    /**
     * 登录
     */
    public function login()
    {
        if (isset($_SESSION['user_name']) && !empty($_SESSION['user_name'])) {
            // 判断是否已经登录
            $this->redirect('/moments/index');
            return;
        }

        $idPlaceholder = "Name";
        $passwordPlaceholder = "Password";
        $headImage = "default_head.jpg"; //默认头像

        $cookieId = null; // 存放cookie获取的id
        $cookiePassword = null; // 存放cookie获取的password
        // 获取cookie
        if (isset($_COOKIE["id"])) {
            $cookieId = $_COOKIE["id"];
        }
        if (isset($_COOKIE["password"])) {
            $cookiePassword = $_COOKIE["password"];
        }

        $id = null;
        $password = null;
        $id = trim($_POST['id']);
        // 为空则读入cookie值
        if ($id == null) {
            $id = $cookieId;
        }
        $password = trim($_POST['password']);
        if ($password == null) {
            $password = $cookiePassword;
        }

        // 验证登录
        if ($id != null && $password != null) {
            $obj = new SixChatApi2016Controller();
            $result = $obj->login($id, $password);
            if ($result == -1) {
                //用户不存在
                $id = null;
                $password = null;
                $idPlaceholder = "该用户不存在";
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
            $condition['user_name'] = $id;
            $avatar = D('User')->getUserAvatar($condition);
            if ($avatar) {
                $headImage = $avatar;
            }
        }

        $array['head_image'] = $headImage;
        $array['id_placeholder'] = $idPlaceholder;
        $array['password_placeholder'] = $passwordPlaceholder;
        $array['id'] = $id;
        $array['password'] = $password;
        $this->assign($array); // 模板赋值
        $this->display("Login/index"); // 模板渲染
    }

    /**
     * 注销
     */
    public function logout()
    {
        $obj = new SixChatApi2016Controller();
        $obj->logout();
        header("Location:login");
    }

    /**
     * 注册
     */
    public function register()
    {
        $id = trim($_POST['id']);
        $password = trim($_POST['password']);
        $idPlaceholder = "新的账号";
        if (!empty($id) && !empty($password)) {
            $obj = new SixChatApi2016Controller();
            $result = $obj->register($id, $password); //调用注册api
            if (!$result) {
                //注册成功
                echo "<script>window.alert('注册成功,现在登录>>');window.location.href='login';</script>";
            } else {
                $idPlaceholder = "该账号已存在";
            }
        }
        $array['id_placeholder'] = $idPlaceholder;
        $this->assign($array); //模板赋值
        $this->display("Register/index"); //模板渲染
    }

}
