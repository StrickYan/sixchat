<?php
/**
 * Created by PhpStorm.
 * User: StrickYan
 * Date: 2018/10/21
 * Time: 02:19
 */

namespace Home\Controller;

class OnlineController extends BaseController
{
    /**
     * @brief 登录状态验证
     * @author strick@beishanwen.com
     * @param void
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // 判断用户是否已经登录
        if (!isset($_SESSION['user_name'])) {
            // $this->error('', U('/auth/login'), 1);
            $this->redirect('/auth/login');
        }
    }
}
