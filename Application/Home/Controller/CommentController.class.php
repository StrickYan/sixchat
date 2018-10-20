<?php
/***************************************************************************
 *
 * Copyright (c) 2017 beishanwen.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file Application/Home/Controller/CommentController.class.php
 * @author 1275330626(com@qq.com)
 * @date 2018/10/21 01:22:39
 * @brief
 *
 **/

namespace Home\Controller;

use Home\Service\CommentService;
use Util\ErrCodeUtils;
use Util\ResponseUtils;
use Util\ParamsUtils;

class CommentController extends OnlineController
{
    /**
     * @brief 获取某条moment的所有赞
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function getLikes()
    {
        ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $obj = new CommentService();
        $ret = $obj->getLikes();

        return ResponseUtils::json($ret['code'], $ret['data']);
    }

    /**
     * @brief 获取某条moment的所有评论
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function getComments()
    {
        ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $obj = new CommentService();
        $ret = $obj->getComments();

        return ResponseUtils::json($ret['code'], $ret['data']);
    }

    /**
     * @brief 获取某条moment的权限内可以看到的赞
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function getLikesInAuth()
    {
        ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $obj = new CommentService();
        $ret = $obj->getLikesInAuth();

        return ResponseUtils::json($ret['code'], $ret['data']);
    }

    /**
     * @brief 获取每条朋友圈下面权限内可阅的评论
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function getCommentsInAuth()
    {
        ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $obj = new CommentService();
        $ret = $obj->getCommentsInAuth();

        return ResponseUtils::json($ret['code'], $ret['data']);
    }

    /**
     * @brief 获取所有的赞
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function getAllLikes()
    {
        ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $obj = new CommentService();
        $ret = $obj->getAllLikes();

        return ResponseUtils::json($ret['code'], $ret['data']);
    }

    /**
     * @brief 获取所有的评论
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function getAllComments()
    {
        ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $obj = new CommentService();
        $ret = $obj->getAllComments();

        return ResponseUtils::json($ret['code'], $ret['data']);
    }

    /**
     * @brief 点赞
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function addLike()
    {
        ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $obj = new CommentService();
        $ret = $obj->addLike();

        return ResponseUtils::json($ret['code'], $ret['data']);
    }

    /**
     * @brief 发布评论
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function addComment()
    {
        ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $obj = new CommentService();
        $ret = $obj->addComment();

        return ResponseUtils::json($ret['code'], $ret['data']);
    }

    /**
     * @brief 删除 comment
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function deleteComment()
    {
        ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $obj = new CommentService();
        $ret = $obj->deleteComment();

        return ResponseUtils::json($ret['code'], $ret['data']);
    }

    /**
     * @brief 显示赞与评论
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function loadMessages()
    {
        ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $obj = new CommentService();
        $ret = $obj->loadMessages();

        return ResponseUtils::json($ret['code'], $ret['data']);
    }

    /**
     * @brief 加载未读消息数量
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function loadNews()
    {
        ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $obj = new CommentService();
        $ret = $obj->loadNews();

        return ResponseUtils::json($ret['code'], $ret['data']);
    }
}
