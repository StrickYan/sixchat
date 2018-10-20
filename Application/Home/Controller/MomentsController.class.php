<?php
/***************************************************************************
 *
 * Copyright (c) 2017 beishanwen.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file Application/Home/Controller/MomentController.class.php
 * @author 1275330626(com@qq.com)
 * @date 2017/08/06 03:22:39
 * @brief
 *
 **/

namespace Home\Controller;

use Home\Service\MomentsService;
use Util\ErrCodeUtils;
use Util\ResponseUtils;
use Util\ParamsUtils;

class MomentsController extends OnlineController
{
    /**
     * @brief 显示信息流
     * @author strick@beishanwen.com
     * @param void
     * @return void
     */
    public function index()
    {
        ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $script = "<script>const GLOBAL_USER_NAME = \"" . $_SESSION['user_name'] . "\";const GLOBAL_USER_ID = \"" . $_SESSION["user_id"] . "\";</script>";
        $this->assign('script', $script);
        $this->display();
    }

    /**
     * @brief 发送 moment
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function addMoment()
    {
        ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $obj = new MomentsService();
        $ret = $obj->addMoment();

        return ResponseUtils::json($ret['code'], $ret['data']);
    }

    /**
     * @brief 删除 moment
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function deleteMoment()
    {
        ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $obj = new MomentsService();
        $ret = $obj->deleteMoment();

        return ResponseUtils::json($ret['code'], $ret['data']);
    }

    /**
     * @brief 查看一条朋友圈
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function getOneMoment()
    {
        ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $obj = new MomentsService();
        $ret = $obj->getOneMoment();

        return ResponseUtils::json($ret['code'], $ret['data']);
    }

    /**
     * @brief 加载下一页moments
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function loadNextPage()
    {
        ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $obj = new MomentsService();
        $ret = $obj->loadNextPage();

        return ResponseUtils::json($ret['code'], $ret['data']);
    }

    /**
     * @brief 加载下一页moments
     * @author strick@beishanwen.com
     * @param void
     * @return void
     */
    public function loadNextPageViaHtml()
    {
        $params = ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $obj = new MomentsService();
        $ret = $obj->loadNextPageViaHtml();

        $this->assign('page', $params['page']);
        $this->assign('list', $ret['data']);
        $this->display("Moments/flow");
    }

    /**
     * @brief moment详情页
     * @author strick@beishanwen.com
     * @param void
     * @return void
     */
    public function details()
    {
        ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $obj = new MomentsService();
        $ret = $obj->details();

        $this->assign('details', $ret['data']['details']);
        $this->assign('script', $ret['data']['script']);
        $this->display();
    }

    /**
     * @brief 选取随机三图做滚动墙纸
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function getRollingWall()
    {
        ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $obj = new MomentsService();
        $ret = $obj->getRollingWall();

        return ResponseUtils::json($ret['code'], $ret['data']);
    }
}
