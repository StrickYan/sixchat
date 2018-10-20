<?php
/***************************************************************************
 *
 * Copyright (c) 2017 beishanwen.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * Created by PhpStorm.
 * User: strick
 * Date: 2017/12/11
 * Time: 下午4:11
 */

namespace Home\Service;

use Util\ErrCodeUtils;
use Util\ResponseUtils;
use Util\ParamsUtils;
use Util\TimeUtils;

class CommentService extends BaseService
{
    private $arrInput;

    /**
     * @brief 初始化
     * @author strick@beishanwen.com
     * @param void
     * @return void
     */
    public function _initialize()
    {
        $this->arrInput = ParamsUtils::get();
    }

    /**
     * @brief 获取某条moment的所有赞
     * @author strick@beishanwen.com
     * @param void
     * @return array
     */
    public function getLikes()
    {
        $params = $this->arrInput;

        if (empty($params['moment_id'])) {
            return ResponseUtils::arrayRet(ErrCodeUtils::PARAMS_INVALID);
        }

        $list = D('Comment')->getLikes($params['moment_id']);
        if (false === $list) {
            return ResponseUtils::arrayRet(ErrCodeUtils::SYSTEM_ERROR);
        }

        foreach ($list as $key => &$value) {
            $value['reply_name'] = htmlspecialchars($value['reply_name']);
        }
        unset($value);

        return ResponseUtils::arrayRet(ErrCodeUtils::SUCCESS, $list);
    }

    /**
     * @brief 获取某条moment的所有评论
     * @author strick@beishanwen.com
     * @param void
     * @return array
     */
    public function getComments()
    {
        $params = $this->arrInput;

        if (empty($params['moment_id'])) {
            return ResponseUtils::arrayRet(ErrCodeUtils::PARAMS_INVALID);
        }

        $list = D('Comment')->getComments1($params['moment_id']);
        if (false === $list) {
            return ResponseUtils::arrayRet(ErrCodeUtils::SYSTEM_ERROR);
        }

        foreach ($list as $key => &$value) {
            $value = array(
                "reply_name" => htmlspecialchars($value['reply_name']),
                "replyed_name" => htmlspecialchars($value['replyed_name']),
                "comment_id" => $value['comment_id'],
                "comment" => htmlspecialchars($value['comment']),
                "comment_level" => $value['comment_level'],
                "time" => $value['time']);
        }
        unset($value);

        return ResponseUtils::arrayRet(ErrCodeUtils::SUCCESS, $list);
    }

    /**
     * @brief 获取某条moment的权限内可以看到的赞
     * @author strick@beishanwen.com
     * @param void
     * @return array
     */
    public function getLikesInAuth()
    {
        $params = $this->arrInput;

        if (empty($params['moment_id'])) {
            return ResponseUtils::arrayRet(ErrCodeUtils::PARAMS_INVALID);
        }

        $list = D('Comment')->getLikesInAuth($params['moment_id'], $params['session_user_name'], $params['moment_user_name']);
        if (false === $list) {
            return ResponseUtils::arrayRet(ErrCodeUtils::SYSTEM_ERROR);
        }

        foreach ($list as $key => &$value) {
            $value['reply_name'] = htmlspecialchars($value['reply_name']);
        }
        unset($value);

        return ResponseUtils::arrayRet(ErrCodeUtils::SUCCESS, $list);
    }

    /**
     * @brief 获取每条朋友圈下面权限内可阅的评论
     * @author strick@beishanwen.com
     * @param void
     * @return array
     */
    public function getCommentsInAuth()
    {
        $params = $this->arrInput;

        if (empty($params['moment_id'])) {
            return ResponseUtils::arrayRet(ErrCodeUtils::PARAMS_INVALID);
        }

        if (!strcmp($params['session_user_name'], $params['moment_user_name'])) {
            // 相等，浏览自己的帖子可以看到所有评论包括好友与非好友
            $list = D('Comment')->getComments1($params['moment_id']);
        } else {
            // 浏览他人的帖子时只能看到互为好友的评论或者 自己与该用户的对话
            $map = array(
                'user_name' => $params['moment_user_name'],
            );
            $userInfo = D('User')->searchUser($map);
            if (false === $userInfo) {
                return ResponseUtils::arrayRet(ErrCodeUtils::SYSTEM_ERROR);
            } else if (empty($userInfo)) {
                return ResponseUtils::arrayRet(ErrCodeUtils::SUCCESS, array());
            }
            $momentUserId = $userInfo["user_id"];

            $list = D('Comment')->getComments2($params['moment_id'], $params['session_user_id'], $momentUserId); //好友关系可见
        }
        if (false === $list) {
            return ResponseUtils::arrayRet(ErrCodeUtils::SYSTEM_ERROR);
        }

        foreach ($list as $key => &$value) {
            $value = array(
                "reply_name" => htmlspecialchars($value['reply_name']),
                "replyed_name" => htmlspecialchars($value['replyed_name']),
                "comment_id" => $value['comment_id'],
                "comment" => htmlspecialchars($value['comment']),
                "time" => $value['time']);
        }
        unset($value);

        return ResponseUtils::arrayRet(ErrCodeUtils::SUCCESS, $list);
    }

    /**
     * @brief 获取所有的赞
     * @author strick@beishanwen.com
     * @param void
     * @return array
     */
    public function getAllLikes()
    {
        $list = D('Comment')->getAllLikes();
        if (false === $list) {
            return ResponseUtils::arrayRet(ErrCodeUtils::SYSTEM_ERROR);
        }

        foreach ($list as $key => &$value) {
            $value['reply_names'] = htmlspecialchars($value['reply_names']);
        }
        unset($value);

        return ResponseUtils::arrayRet(ErrCodeUtils::SUCCESS, $list);
    }

    /**
     * @brief 获取所有的评论
     * @author strick@beishanwen.com
     * @param void
     * @return array
     */
    public function getAllComments()
    {
        $list = D('Comment')->getAllComments();
        if (false === $list) {
            return ResponseUtils::arrayRet(ErrCodeUtils::SYSTEM_ERROR);
        }

        foreach ($list as $key => &$value) {
            $value = array(
                "reply_name" => htmlspecialchars($value['reply_name']),
                "replyed_name" => htmlspecialchars($value['replyed_name']),
                "comment_id" => $value['comment_id'],
                "moment_id" => $value['moment_id'],
                "comment" => htmlspecialchars($value['comment']),
                "comment_level" => $value['comment_level'],
                "time" => $value['time']);
        }
        unset($value);

        return ResponseUtils::arrayRet(ErrCodeUtils::SUCCESS, $list);
    }

    /**
     * @brief 点赞
     * @author strick@beishanwen.com
     * @param void
     * @return array
     */
    public function addLike()
    {
        $params = $this->arrInput;

        $momentId = $params['moment_id'];
        $momentUserName = $params['moment_user_name'];
        if (empty($momentId) || empty($momentUserName)) {
            return ResponseUtils::arrayRet(ErrCodeUtils::PARAMS_INVALID);
        }

        $replyId = $params['session_user_id'];

        $map = array(
            'user_name' => $momentUserName,
        );
        $userInfo = D('User')->searchUser($map);
        if (false === $userInfo) {
            return ResponseUtils::arrayRet(ErrCodeUtils::SYSTEM_ERROR);
        } else if (empty($userInfo)) {
            return ResponseUtils::arrayRet(ErrCodeUtils::SUCCESS, array());
        }
        $replyedId = $userInfo["user_id"];

        $condition = array(
            'moment_id' => $momentId,
            'reply_id' => $replyId,
            'replyed_id' => $replyedId,
            'state' => 1,
            'type' => 1,
        );
        $result = D('Comment')->getCommentId($condition);
        if (false === $result) {
            return ResponseUtils::arrayRet(ErrCodeUtils::SYSTEM_ERROR);
        } else if (count($result)) {
            // 已点赞 则删除赞记录
            $updateData = array(
                'comment_id' => $result,
            );
            $ret = D('Comment')->updateCommentState($updateData);
            if (false === $ret) {
                return ResponseUtils::arrayRet(ErrCodeUtils::SYSTEM_ERROR);
            }
        } else {
            // 没有点赞记录 则增加点赞
            $insertData = array(
                'moment_id' => $momentId,
                'reply_id' => $replyId,
                'replyed_id' => $replyedId,
                'time' => date("Y-m-d H:i:s"),
                'type' => 1,
                'comment' => '赞了你',
                'comment_level' => 1,
            );
            $ret = D('Comment')->addComment($insertData);
            if (false === $ret) {
                return ResponseUtils::arrayRet(ErrCodeUtils::SYSTEM_ERROR);
            }
        }

        if ($replyId != $replyedId) {
            // 指明给谁推送，为空表示向所有在线用户推送
            $to_uid = $replyedId;
            // 推送的url地址
            $push_api_url = "http://localhost:2121/";
            $post_data = array(
                'type' => 'publish_new_msg_num',
                'content' => '你有新消息',
                'to' => $to_uid,
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $push_api_url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            curl_exec($ch);
            curl_close($ch);
        }

        return ResponseUtils::arrayRet(ErrCodeUtils::SUCCESS, array());
    }

    /**
     * @brief 发布评论
     * @author strick@beishanwen.com
     * @param void
     * @return array
     */
    public function addComment()
    {
        $params = $this->arrInput;

        $momentId = $params['moment_id'];
        $replyedName = $params['replyed_name'];
        $commentVal = $params['comment_val'];
        $commentLevel = $params['comment_level'];
        if (empty($momentId) || empty($replyedName) || empty($commentVal) || empty($commentLevel)) {
            return ResponseUtils::arrayRet(ErrCodeUtils::PARAMS_INVALID);
        }
        $replyName = $params['session_user_name'];
        $replyId = $params['session_user_id'];

        $map = array(
            'user_name' => $replyedName,
        );
        $userInfo = D('User')->searchUser($map);
        if (false === $userInfo) {
            return ResponseUtils::arrayRet(ErrCodeUtils::SYSTEM_ERROR);
        } else if (empty($userInfo)) {
            return ResponseUtils::arrayRet(ErrCodeUtils::SUCCESS, array());
        }
        $replyedId = $userInfo["user_id"];

        // 插入评论
        $insertData = array(
            'moment_id' => $momentId,
            'reply_id' => $replyId,
            'replyed_id' => $replyedId,
            'comment' => $commentVal,
            'comment_level' => $commentLevel,
            'time' => date("Y-m-d H:i:s"),
            'type' => 2,
        );
        $ret = D('Comment')->addComment($insertData);
        if (false === $ret) {
            return ResponseUtils::arrayRet(ErrCodeUtils::SYSTEM_ERROR);
        }

        // 获取新增评论的 comment_id
        $newCommentId = $ret;

        if ($replyId != $replyedId) {
            // 指明给谁推送，为空表示向所有在线用户推送
            $to_uid = $replyedId;
            // 推送的url地址
            $push_api_url = "http://localhost:2121/";
            $post_data = array(
                'type' => 'publish_new_msg_num',
                'content' => '你有新消息',
                'to' => $to_uid,
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $push_api_url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            curl_exec($ch);
            curl_close($ch);
            // var_export($return);
        }

        // 返回json数据
        $list[] = array(
            "comment_id" => $newCommentId,
            "moment_id" => $momentId,
            "reply_name" => $replyName,
            "replyed_name" => $replyedName,
            "comment_val" => $commentVal,
        );

        return ResponseUtils::arrayRet(ErrCodeUtils::SUCCESS, $list);
    }

    /**
     * @brief 删除 comment
     * @author strick@beishanwen.com
     * @param void
     * @return array
     */
    public function deleteComment()
    {
        $params = $this->arrInput;

        $condition = array(
            'comment_id' => $params['comment_id'],
        );
        $ret = D('Comment')->updateCommentState($condition);
        if (false === $ret) {
            return ResponseUtils::arrayRet(ErrCodeUtils::SYSTEM_ERROR);
        }

        return ResponseUtils::arrayRet(ErrCodeUtils::SUCCESS);
    }

    /**
     * @brief 显示赞与评论
     * @author strick@beishanwen.com
     * @param void
     * @return array
     */
    public function loadMessages()
    {
        $params = $this->arrInput;

        $list = D('Comment')->getUnreadMessagesViaUserId($params['session_user_id']);
        if (false === $list) {
            return ResponseUtils::arrayRet(ErrCodeUtils::SYSTEM_ERROR);
        }

        foreach ($list as $key => &$value) {
            $value['time'] = TimeUtils::tranTime(strtotime($value['time']));
        }
        unset($value);

        $ret = D('Comment')->updateNewsViaUserId($params['session_user_id']);
        if (false === $ret) {
            return ResponseUtils::arrayRet(ErrCodeUtils::SYSTEM_ERROR);
        }

        return ResponseUtils::arrayRet(ErrCodeUtils::SUCCESS, $list);
    }

    /**
     * @brief 加载未读消息数量
     * @author strick@beishanwen.com
     * @param void
     * @return array
     */
    public function loadNews()
    {
        $params = $this->arrInput;

        $list = D('Comment')->getNews($params['session_user_id']);
        if (false === $list) {
            return ResponseUtils::arrayRet(ErrCodeUtils::SYSTEM_ERROR);
        }

        $map = array(
            'requested_id' => $params['session_user_id'],
            'state' => 1,
        );
        $result = D('FriendRequest')->getFriendRequest($map);
        if (false === $result) {
            return ResponseUtils::arrayRet(ErrCodeUtils::SYSTEM_ERROR);
        }

        $ret = array(
            "number" => count($list) + count($result),
        );

        return ResponseUtils::arrayRet(ErrCodeUtils::SUCCESS, $ret);
    }
}
