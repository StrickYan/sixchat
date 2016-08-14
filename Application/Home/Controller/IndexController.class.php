<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
        // $result="Welcome To SixChat.";
        // $this->assign('result',$result);
        // $this->display();
        $this->success('Welcome To SixChat.', U('Login/index'), 1);
    }
}