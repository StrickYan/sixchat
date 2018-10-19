<?php

namespace Home\Controller;

class IndexController extends BaseController
{
    /**
     * @brief 登录成功，跳转主页面
     * @author strick@beishanwen.com
     * @param void
     * @return void
     */
    public function index()
    {
        // $this->success('Welcome To SixChat.', U('/auth/login'), 1);
        $this->redirect('/moments/index');
    }
}
