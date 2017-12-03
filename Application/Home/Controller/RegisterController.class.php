<?php
/***************************************************************************
 *
 * Copyright (c) 2017 classmateer.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file Application/Home/Controller/RrgisterController.class.php
 * @author 1275330626(com@qq.com)
 * @date 2017/08/06 03:22:39
 * @brief
 *
 **/

namespace Home\Controller;

use Think\Controller;

class RegisterController extends Controller
{
    public function index()
    {
        $id = trim($_POST['id']);
        $password = trim($_POST['password']);
        $text_placeholder = "新的账号";
        // $userid = null;
        if ($id != null && $password != null) {
            $obj = new SixChatApi2016Controller();
            $result = $obj->register($id, $password); //调用注册api
            if (!$result) {
                //注册成功
                echo "<script>window.alert('注册成功,现在登录>>');window.location.href='../login/';</script>";
            } else {
                $text_placeholder = "该账号已存在";
            }
        }
        $array['text_placeholder'] = $text_placeholder;
        $this->assign($array); //模板赋值
        $this->display(); //模板渲染
    }
}
