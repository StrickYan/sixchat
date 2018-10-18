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

use Util\ErrCodeUtils;
use Util\ResponseUtils;
use Util\ParamsUtils;
use Util\TimeUtils;
use Util\UploadImgUtils;

class MomentsController extends BaseController
{
    /**
     * @brief 登录状态验证
     * @author strick@beishanwen.com
     * @param void
     * @return void
     */
    public function _initialize()
    {
        // 判断用户是否已经登录
        if (!isset($_SESSION['user_name'])) {
            $this->error('', U('/auth/login'), 1);
        }
    }

    /**
     * @brief 显示信息流
     * @author strick@beishanwen.com
     * @param void
     * @return void
     */
    public function index()
    {
        $script = "<script>const GLOBAL_USER_NAME = \"" . $_SESSION['user_name'] . "\";const GLOBAL_USER_ID = \"" . $_SESSION["user_id"] . "\";</script>";
        $this->assign('script', $script);
        $this->display();
    }

    /**
     * @brief 获取某条moment的所有赞
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function getLikes()
    {
        $params = ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        if (empty($params['moment_id'])) {
            return ResponseUtils::json(ErrCodeUtils::PARAMS_INVALID);
        }

        $list = D('Comment')->getLikes($params['moment_id']);
        if (false === $list) {
            return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
        }

        foreach ($list as $key => &$value) {
            $value['reply_name'] = htmlspecialchars($value['reply_name']);
        }
        unset($value);

        return ResponseUtils::json(ErrCodeUtils::SUCCESS, $list);
    }

    /**
     * @brief 获取某条moment的权限内可以看到的赞
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function getLikesInAuth()
    {
        $params = ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        if (empty($params['moment_id'])) {
            return ResponseUtils::json(ErrCodeUtils::PARAMS_INVALID);
        }

        $list = D('Comment')->getLikesInAuth($params['moment_id'], $params['session_user_name'], $params['moment_user_name']);
        if (false === $list) {
            return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
        }

        foreach ($list as $key => &$value) {
            $value['reply_name'] = htmlspecialchars($value['reply_name']);
        }
        unset($value);

        return ResponseUtils::json(ErrCodeUtils::SUCCESS, $list);
    }

    /**
     * @brief 获取某条moment的所有评论
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function getComments()
    {
        $params = ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        if (empty($params['moment_id'])) {
            return ResponseUtils::json(ErrCodeUtils::PARAMS_INVALID);
        }

        $list = D('Comment')->getComments1($params['moment_id']);
        if (false === $list) {
            return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
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

        return ResponseUtils::json(ErrCodeUtils::SUCCESS, $list);
    }

    /**
     * @brief 获取所有的赞
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function getAllLikes()
    {
        $params = ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $list = D('Comment')->getAllLikes();
        if (false === $list) {
            return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
        }

        foreach ($list as $key => &$value) {
            $value['reply_names'] = htmlspecialchars($value['reply_names']);
        }
        unset($value);

        return ResponseUtils::json(ErrCodeUtils::SUCCESS, $list);
    }

    /**
     * @brief 获取所有的评论
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function getAllComments()
    {
        $params = ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $list = D('Comment')->getAllComments();
        if (false === $list) {
            return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
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

        return ResponseUtils::json(ErrCodeUtils::SUCCESS, $list);
    }

    /**
     * @brief 获取每条朋友圈下面权限内可阅的评论
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function getCommentsInAuth()
    {
        $params = ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        if (empty($params['moment_id'])) {
            return ResponseUtils::json(ErrCodeUtils::PARAMS_INVALID);
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
                return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
            } else if (empty($userInfo)) {
                return ResponseUtils::json(ErrCodeUtils::SUCCESS, array());
            }
            $momentUserId = $userInfo["user_id"];

            $list = D('Comment')->getComments2($params['moment_id'], $params['session_user_id'], $momentUserId); //好友关系可见
        }
        if (false === $list) {
            return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
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

        return ResponseUtils::json(ErrCodeUtils::SUCCESS, $list);
    }

    /**
     * @brief 点赞
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function addLike()
    {
        $params = ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $momentId = $params['moment_id'];
        $momentUserName = $params['moment_user_name'];
        if (empty($momentId) || empty($momentUserName)) {
            return ResponseUtils::json(ErrCodeUtils::PARAMS_INVALID);
        }

        $replyId = $params['session_user_id'];

        $map = array(
            'user_name' => $momentUserName,
        );
        $userInfo = D('User')->searchUser($map);
        if (false === $userInfo) {
            return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
        } else if (empty($userInfo)) {
            return ResponseUtils::json(ErrCodeUtils::SUCCESS, array());
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
            return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
        } else if (count($result)) {
            // 已点赞 则删除赞记录
            $updateData = array(
                'comment_id' => $result,
            );
            $ret = D('Comment')->updateCommentState($updateData);
            if (false === $ret) {
                return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
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
                return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
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

        return ResponseUtils::json(ErrCodeUtils::SUCCESS, array());
    }

    /**
     * @brief 发布评论
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function addComment()
    {
        $params = ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $momentId = $params['moment_id'];
        $replyedName = $params['replyed_name'];
        $commentVal = $params['comment_val'];
        $commentLevel = $params['comment_level'];
        if (empty($momentId) || empty($replyedName) || empty($commentVal) || empty($commentLevel)) {
            return ResponseUtils::json(ErrCodeUtils::PARAMS_INVALID);
        }
        $replyName = $params['session_user_name'];
        $replyId = $params['session_user_id'];

        $map = array(
            'user_name' => $replyedName,
        );
        $userInfo = D('User')->searchUser($map);
        if (false === $userInfo) {
            return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
        } else if (empty($userInfo)) {
            return ResponseUtils::json(ErrCodeUtils::SUCCESS, array());
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
            return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
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

        return ResponseUtils::json(ErrCodeUtils::SUCCESS, $list);
    }

    /**
     * @brief 发送 moment
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function addMoment()
    {
        $params = ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $textBox = $params['text_box'] ?? ''; //获取朋友圈文本内容
        $imageName = '';

        if (empty($textBox) && empty($_FILES['upfile']['tmp_name'])) {
            // echo "没有内容";
            return ResponseUtils::json(ErrCodeUtils::PARAMS_INVALID, array(), $_FILES['upfile']['error']);
        }

        // 上传图片
        if (!empty($_FILES['upfile']['tmp_name'])) {
            $destinationFolder = "moment_img/"; //上传文件路径
            $inputFileName = "upfile";
            $maxWidth = 640;
            $maxHeight = 1136;
            $uploadResult = UploadImgUtils::uploadImg($destinationFolder, $inputFileName, $maxWidth, $maxHeight); //调用上传函数
            if (ErrCodeUtils::SUCCESS !== $uploadResult['code']) {
                return ResponseUtils::json($uploadResult['code'], array(), $uploadResult['msg']);
            }

            // 有图片上传且上传成功返回图片名
            $imageName = $uploadResult['data']['image_name'];
        }

        // 新增朋友圈
        $insertData = array(
            'user_id' => $params['session_user_id'],
            'info' => $textBox,
            'img_url' => $imageName,
            'time' => date("Y-m-d H:i:s"),
        );
        $newMomentId = D('Moment')->addMoment($insertData);
        if (false === $newMomentId) {
            return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
        }

        $response = array(
            'isSuccess' => true,
            'moment_id' => $newMomentId,
            'user_name' => $params['session_user_name'],
            'avatar' => $params['session_user_avatar'],
            'text_box' => $textBox,
            'photo' => $imageName,
            'time' => TimeUtils::tranTime(strtotime(date("Y-m-d H:i:s"))),
        );

        return ResponseUtils::json(ErrCodeUtils::SUCCESS, $response);
    }

    /**
     * @brief 选取随机三图做滚动墙纸
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function getRollingWall()
    {
        $params = ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $list = D('Moment')->getRollingWall();
        if (false === $list) {
            return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
        }

        // 返回json数据
        $response[] = array(
            "img_url_1" => "1477756153.JPG",
            "moment_id_1" => 101,
            "img_url_2" => $list[0]['img_url'],
            "moment_id_2" => $list[0]['moment_id'],
            "img_url_3" => $list[1]['img_url'],
            "moment_id_3" => $list[1]['moment_id'],
        );

        return ResponseUtils::json(ErrCodeUtils::SUCCESS, $response);
    }

    /**
     * @brief 删除 moment
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function deleteMoment()
    {
        $params = ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $model = M();
        $model->startTrans();

        $condition = array(
            'moment_id' => $params['moment_id'],
        );
        $ret = D('Moment')->updateMomentState($condition);
        if (false === $ret) {
            $model->rollback();
            return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
        }

        // 删除moment的时候连带删除其下所有评论
        $ret = D('Comment')->updateCommentState($condition);
        if (false === $ret) {
            $model->rollback();
            return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
        }

        $model->commit();

        return ResponseUtils::json(ErrCodeUtils::SUCCESS);
    }

    /**
     * @brief 删除 comment
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function deleteComment()
    {
        $params = ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $condition = array(
            'comment_id' => $params['comment_id'],
        );
        $ret = D('Comment')->updateCommentState($condition);
        if (false === $ret) {
            return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
        }

        return ResponseUtils::json(ErrCodeUtils::SUCCESS);
    }

    /**
     * @brief 显示赞与评论
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function loadMessages()
    {
        $params = ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $list = D('Comment')->getUnreadMessagesViaUserId($params['session_user_id']);
        if (false === $list) {
            return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
        }

        foreach ($list as $key => &$value) {
            $value['time'] = TimeUtils::tranTime(strtotime($value['time']));
        }
        unset($value);

        $ret = D('Comment')->updateNewsViaUserId($params['session_user_id']);
        if (false === $ret) {
            return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
        }

        return ResponseUtils::json(ErrCodeUtils::SUCCESS, $list);
    }

    /**
     * @brief 查看一条朋友圈
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function getOneMoment()
    {
        $params = ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $list = D('Moment')->getOneMoment($params['moment_id']);
        if (false === $list) {
            return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
        }

        foreach ($list as $key => &$value) {
            $value['text_box'] = $value['info'];
            $value['photo'] = $value['img_url'];
            $value['time'] = date("M j, Y H:i", strtotime($value['time']));
        }
        unset($value);

        return ResponseUtils::json(ErrCodeUtils::SUCCESS, $list);
    }

    /**
     * @brief 加载下一页moments
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function loadNextPage()
    {
        $params = ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $list = D('Moment')->getNextPage($params['page']);
        if (false === $list) {
            return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
        }

        foreach ($list as $key => &$value) {
            $value['user_name'] = htmlspecialchars($value['user_name']);
            $value['time'] = TimeUtils::tranTime(strtotime($value['time']));
            $value['info'] = htmlspecialchars($value['info']);
        }
        unset($value);

        return ResponseUtils::json(ErrCodeUtils::SUCCESS, $list);
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

        $list = D('Moment')->getNextPage($params['page']);
        if (false === $list) {
            $list = array();
        }

        foreach ($list as $key => &$value) {
            $value['user_name'] = htmlspecialchars($value['user_name']);
            $value['time'] = TimeUtils::tranTime(strtotime($value['time']));
            $value['info'] = str_replace("\n", "<br>", htmlspecialchars($value['info']));
        }
        unset($value);

        $this->assign('page', $params['page']);
        $this->assign('list', $list);
        $this->display("Moments/flow");
    }

    /**
     * @brief 加载未读消息数量
     * @author strick@beishanwen.com
     * @param void
     * @return string
     */
    public function loadNews()
    {
        $params = ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $list = D('Moment')->getNews($params['session_user_id']);
        if (false === $list) {
            return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
        }

        $map = array(
            'requested_id' => $params['session_user_id'],
            'state' => 1,
        );
        $result = D('FriendRequest')->getFriendRequest($map);
        if (false === $result) {
            return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
        }

        $ret = array(
            "number" => count($list) + count($result),
        );

        return ResponseUtils::json(ErrCodeUtils::SUCCESS, $ret);
    }

    /**
     * @brief moment详情页
     * @author strick@beishanwen.com
     * @param void
     * @return void
     */
    public function details()
    {
        $params = ParamsUtils::execute(CONTROLLER_NAME . '/' . ACTION_NAME);

        $userName = $params['session_user_name'];
        $userId = $params['session_user_id'];
        $momentId = $params['moment_id'];
        $result = D('Moment')->getOneMoment($momentId);
        if (false === $result) {
            $result = array();
        }

        foreach ($result as $key => &$value) {
            $value['user_name'] = htmlspecialchars($value['user_name']);
            $value['time'] = TimeUtils::tranTime(strtotime($value['time']));
            $value['info'] = htmlspecialchars($value['info']);
        }
        unset($value);

        $script = "<script>const GLOBAL_USER_NAME = \"" . $userName . "\"; const GLOBAL_USER_ID = \"" . $userId . "\";</script>";

        $this->assign('details', $result[0] ?? array());
        $this->assign('script', $script);
        $this->display();
    }
}
