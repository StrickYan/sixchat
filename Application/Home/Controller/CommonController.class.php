<?php //功能封装集成Api,方便其他控制器调用
namespace Home\Controller;

use Think\Controller;

class CommonController extends Controller
{

    protected $obj;
    protected $momentModel;
    protected $userModel;
    protected $commentModel;
    protected $friendRuquestModel;
    protected $friendModel;

    public function __construct()
    {
        parent::__construct();
        $this->obj                = new SixChatApi2016Controller();
        $this->momentModel        = D('Moment');
        $this->userModel          = D('User');
        $this->commentModel       = D('comment');
        $this->friendRuquestModel = D("Friend_request");
        $this->friendModel        = D("Friend");
    }

    public function _initialize()
    {
        //判断用户是否已经登录
        if (!isset($_SESSION['name'])) {
            $this->error('', U('/Login/index'), 1);
        }
    }

}
