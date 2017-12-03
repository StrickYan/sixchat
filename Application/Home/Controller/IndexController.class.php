<?php

namespace Home\Controller;

use Think\Controller;

class IndexController extends Controller
{
    public function index()
    {
        $this->success('Welcome To SixChat.', U('/login/index'), 1);
    }
}
