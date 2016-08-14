<?php 	//功能封装集成Api,方便其他控制器调用
namespace Home\Controller;
use Think\Controller;
class CommonController extends Controller{
    public function _initialize(){
        //判断用户是否已经登录
        if (!isset($_SESSION['name'])) {
         	// $this->show("<style type='text/css'>img{display:block;z-index: 10;width:100%;height:100%;}</style><img src=\"http://119.29.24.253/sixchat/Public/Home/img/default/welcome.jpg\">");
        	 $this->error('', U('Login/index'), 1);
                    
        }
    }
}