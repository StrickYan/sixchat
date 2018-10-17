<?php

namespace Home\Controller;

class IndexController extends BaseController
{
    /**
     * 登录成功，跳转主页面
     */
    public function index()
    {
        $this->success('Welcome To SixChat.', U('/auth/login'), 1);
    }
}
