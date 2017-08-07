<?php

namespace Home\Controller;

use Home\Common\ErrorCode;
use Home\Common\Utility;
use Think\Controller;

class TestController extends Controller
{
    public function index()
    {
        echo "hello world";
    }

    public function test()
    {
        $data = array(
            'name' => "kobe",
            'age' => 22,
        );
        Utility::returnData(ErrorCode::SUCCESS, $data, $msg = 'test.');
    }
}