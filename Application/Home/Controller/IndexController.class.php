<?php

namespace Home\Controller;

use Think\Controller;

class IndexController extends Controller
{
    /**
     * 登录成功，跳转主页面
     */
    public function index()
    {
        $this->success('Welcome To SixChat.', U('/auth/login'), 1);
    }
}
