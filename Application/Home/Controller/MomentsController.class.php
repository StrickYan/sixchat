<?php
/***************************************************************************
 *
 * Copyright (c) 2017 classmateer.com, Inc. All Rights Reserved
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

class MomentsController extends BaseController
{
    protected $obj;

    /**
     * BaseController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->obj = new SixChatApi2016Controller();
    }

    /**
     * 登录状态验证
     */
    public function _initialize()
    {
        // 判断用户是否已经登录
        if (!isset($_SESSION['name'])) {
            $this->error('', U('/auth/login'), 1);
        }
    }

    /**
     * 显示信息流
     */
    public function index()
    {
        $script = "<script>const GLOBAL_USER_NAME = \"" . $_SESSION["user_name"] . "\";const GLOBAL_USER_ID = \"" . $_SESSION["user_id"] . "\";</script>";
        $this->assign('script', $script);
        $this->display();
    }

    /**
     * 获取某条moment的所有赞
     */
    public function getLikes()
    {
        $id = intval($_REQUEST['id']); // moment_id
        if (empty($id)) {
            return ResponseUtils::json(ErrCodeUtils::PARAMS_INVALID);
        }

        $list = D('Comment')->getLikes($id);
        if (false === $list) {
            return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
        }

        foreach ($list as $key => &$value) {
            $value['reply_name'] = htmlspecialchars($value['reply_name']);
        }

        return ResponseUtils::json(ErrCodeUtils::SUCCESS, $list);
    }

    /**
     * 获取某条moment的权限内可以看到的赞
     */
    public function getLikesInAuth()
    {
        $id = intval($_REQUEST['id']); // moment_id
        if (empty($id)) {
            return ResponseUtils::json(ErrCodeUtils::PARAMS_INVALID);
        }
        $momentUserName = htmlspecialchars($_REQUEST['moment_user_name']); // 获取该条朋友圈的用户名
        $userName = $_SESSION["user_name"]; //当前用户

        $list = D('Comment')->getLikesInAuth($id, $userName, $momentUserName);
        if (false === $list) {
            return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
        }

        foreach ($list as $key => &$value) {
            $value['reply_name'] = htmlspecialchars($value['reply_name']);
        }

        return ResponseUtils::json(ErrCodeUtils::SUCCESS, $list);
    }

    /**
     * 获取某条moment的所有评论
     */
    public function getComments()
    {
        $id = intval($_REQUEST['id']);
        if (empty($id)) {
            return ResponseUtils::json(ErrCodeUtils::PARAMS_INVALID);
        }

        $list = D('Comment')->getComments1($id);
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

        return ResponseUtils::json(ErrCodeUtils::SUCCESS, $list);
    }

    /**
     * 获取所有的赞
     */
    public function getAllLikes()
    {
        $list = D('Comment')->getAllLikes();
        if (false === $list) {
            return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
        }

        foreach ($list as $key => &$value) {
            $value['reply_names'] = htmlspecialchars($value['reply_names']);
        }

        return ResponseUtils::json(ErrCodeUtils::SUCCESS, $list);
    }

    /**
     * 获取所有的评论
     */
    public function getAllComments()
    {
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
                "time" => $value['time']);
        }

        return ResponseUtils::json(ErrCodeUtils::SUCCESS, $list);
    }

    /**
     * 获取每条朋友圈下面权限内可阅的评论
     */
    public function getCommentsInAuth()
    {
        $id = intval($_REQUEST['id']);
        if (empty($id)) {
            return ResponseUtils::json(ErrCodeUtils::PARAMS_INVALID);
        }
        $momentUserName = htmlspecialchars($_REQUEST['moment_user_name']); // 获取该条朋友圈的用户名
        $userName = $_SESSION["user_name"]; //当前用户

        $list = array();
        if (!strcmp($userName, $momentUserName)) {
            // 相等，浏览自己的帖子可以看到所有评论包括好友与非好友
            $list = D('Comment')->getComments1($id);
        } else {
            // 浏览他人的帖子时只能看到互为好友的评论或者 自己与该用户的对话
            foreach ($this->obj->getUserId($userName, $momentUserName) as $k => $val) {
                $userId = $val["reply_id"];
                $momentUserId = $val["replyed_id"];
                $list = D('Comment')->getComments2($id, $userId, $momentUserId); //好友关系可见
            }
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

        return ResponseUtils::json(ErrCodeUtils::SUCCESS, $list);
    }

    /**
     * 点赞
     */
    public function addLike()
    {
        $momentId = intval($_REQUEST['moment_id']);
        $momentUserName = htmlspecialchars($_REQUEST['moment_user_name']);
        if (empty($momentId) || empty($momentUserName)) {
            return ResponseUtils::json(ErrCodeUtils::PARAMS_INVALID);
        }
        $replyName = $_SESSION["user_name"];
        $replyedName = $momentUserName;

        foreach ($this->obj->getUserId($replyName, $replyedName) as $k => $val) {
            $replyId = $val["reply_id"];
            $replyedId = $val["replyed_id"];

            $condition['moment_id'] = $momentId;
            $condition['reply_id'] = $replyId;
            $condition['replyed_id'] = $replyedId;
            $condition['state'] = 1;
            $condition['type'] = 1;
            $result = D('Comment')->getCommentId($condition);
            if (false === $result) {
                return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
            }

            if ($result) {
                // 已点赞 则删除赞记录
                $data['comment_id'] = $result;
                $ret = D('Comment')->updateCommentState($data);
                if (false === $ret) {
                    return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
                }
            } else {
                // 没有点赞记录 则增加点赞
                $data['moment_id'] = $momentId;
                $data['reply_id'] = $replyId;
                $data['replyed_id'] = $replyedId;
                $data['time'] = date("Y-m-d H:i:s");
                $data['type'] = 1;
                $data['comment'] = "赞了你";
                $ret = D('Comment')->addComment($data);
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
                $return = curl_exec($ch);
                curl_close($ch);
                // var_export($return);
            }
        }

        $list[0] = "点赞成功";

        return ResponseUtils::json(ErrCodeUtils::SUCCESS, $list);
    }

    /**
     * 发布评论
     */
    public function addComment()
    {
        $momentId = intval($_REQUEST['moment_id']);
        $replyedName = htmlspecialchars($_REQUEST['replyed_name']);
        $commentVal = htmlspecialchars(trim($_REQUEST['comment_val']));
        if (empty($momentId) || empty($replyedName) || empty($commentVal)) {
            return ResponseUtils::json(ErrCodeUtils::PARAMS_INVALID);
        }
        $replyName = $_SESSION["user_name"];

        foreach ($this->obj->getUserId($replyName, $replyedName) as $k => $val) {
            $replyId = $val["reply_id"];
            $replyedId = $val["replyed_id"];

            // 插入评论
            $data['moment_id'] = $momentId;
            $data['reply_id'] = $replyId;
            $data['replyed_id'] = $replyedId;
            $data['comment'] = $commentVal;
            $data['time'] = date("Y-m-d H:i:s");
            $data['type'] = 2;
            $ret = D('Comment')->addComment($data);
            if (false === $ret) {
                return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
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
                $return = curl_exec($ch);
                curl_close($ch);
                // var_export($return);
            }
        }

        // 获取新增评论的 comment_id
        $commentId = D('Comment')->getMaxCommentId();
        if (false === $commentId) {
            return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
        }

        // 返回json数据
        $list[] = array(
            "comment_id" => $commentId,
            "moment_id" => $momentId,
            "reply_name" => $replyName,
            "replyed_name" => $replyedName,
            "comment_val" => $commentVal,
        );

        return ResponseUtils::json(ErrCodeUtils::SUCCESS, $list);
    }

    /**
     * 发送 moment
     */
    public function addMoment()
    {
        $textBox = trim(isset($_POST['text_box'])) ? htmlspecialchars(trim($_POST['text_box'])) : ''; //获取朋友圈文本内容
        $imageName = '';
        $response = array();

        if (!$textBox && empty($_FILES['upfile']['tmp_name'])) {
            // echo $_FILES['upfile']['error'];
            // echo "没有内容";
            return ResponseUtils::json(ErrCodeUtils::PARAMS_INVALID);
        }
        $destinationFolder = "moment_img/"; //上传文件路径
        $inputFileName = "upfile";
        $maxWidth = 640;
        $maxHeight = 1136;
        $uploadResult = $this->obj->uploadImg($destinationFolder, $inputFileName, $maxWidth, $maxHeight); //调用上传函数
        if ($uploadResult) {
            // 有图片上传且上传成功返回图片名
            $imageName = $uploadResult;
        }

        $userName = $_SESSION["user_name"];
        foreach ($this->obj->getUserId($userName, $userName) as $k => $val) {
            $userId = $val["reply_id"];

            // 插入朋友圈
            $data['user_id'] = $userId;
            $data['info'] = $textBox;
            $data['img_url'] = $imageName;
            $data['time'] = date("Y-m-d H:i:s");
            $ret = D('Moment')->addMoment($data);
            if (false === $ret) {
                return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
            }
        }

        // 获取自己头像
        $map['user_name'] = $userName;
        $avatar = D('User')->getUserAvatar($map);
        if (false === $avatar) {
            return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
        }

        // 获取新增朋友圈的 moment_id
        $moment_id = D('Moment')->getMaxMomentId();
        if (false === $moment_id) {
            return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
        }

        $response['isSuccess'] = true;
        $response['moment_id'] = $moment_id;
        $response['user_name'] = $userName;
        $response['avatar'] = $avatar;
        $response['text_box'] = $textBox;
        $response['photo'] = $imageName;
        $response['time'] = $this->obj->tranTime(strtotime(date("Y-m-d H:i:s")));

        return ResponseUtils::json(ErrCodeUtils::SUCCESS, $response);
    }

    /**
     * 选取随机三图做滚动墙纸
     */
    public function getRollingWall()
    {
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
     * 删除 moment
     */
    public function deleteMoment()
    {
        $momentId = htmlspecialchars($_REQUEST['moment_id']);
        $condition['moment_id'] = $momentId;
        D('Moment')->updateMomentState($condition);
        D('Comment')->updateCommentState($condition); // 删除moment的时候连带删除其下所有评论
        $list[0] = "Delete moment is success.";

        return ResponseUtils::json(ErrCodeUtils::SUCCESS, $list);
    }

    /**
     * 删除 comment
     */
    public function deleteComment()
    {
        $commentId = htmlspecialchars($_REQUEST['comment_id']);
        $condition['comment_id'] = $commentId;
        D('Comment')->updateCommentState($condition);
        $list[0] = "Delete comment is success.";

        return ResponseUtils::json(ErrCodeUtils::SUCCESS, $list);
    }

    /**
     * 显示赞与评论
     */
    public function loadMessages()
    {
        $userName = $_SESSION["user_name"];
        $map['user_name'] = $userName;
        $userId = D('User')->getUserId($map);
        $list = D('Comment')->getUnreadMessagesViaUserId($userId);
        foreach ($list as $key => &$value) {
            $value['time'] = $this->obj->tranTime(strtotime($value['time']));
        }
        D('Comment')->updateNewsViaUserId($userId);

        return ResponseUtils::json(ErrCodeUtils::SUCCESS, $list);
    }

    /**
     * 查看一条朋友圈
     */
    public function getOneMoment()
    {
        $momentId = htmlspecialchars($_POST['moment_id']);
        $list = D('Moment')->getOneMoment($momentId);
        if (false === $list) {
            return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
        }

        foreach ($list as $key => &$value) {
            $value['text_box'] = $value['info'];
            $value['photo'] = $value['img_url'];
            $value['moment_id'] = $momentId;
            $value['time'] = date("M j, Y H:i", strtotime($value['time']));
        }

        return ResponseUtils::json(ErrCodeUtils::SUCCESS, $list);
    }

    /**
     * 加载下一页moments
     */
    public function loadNextPage()
    {
        $page = htmlspecialchars($_REQUEST['page']);
        $list = D('Moment')->getNextPage($page);
        if (false === $list) {
            return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
        }

        foreach ($list as $key => &$value) {
            $value['user_name'] = htmlspecialchars($value['user_name']);
            $value['time'] = $this->obj->tranTime(strtotime($value['time']));
            $value['info'] = htmlspecialchars($value['info']);
        }
        return ResponseUtils::json(ErrCodeUtils::SUCCESS, $list);
    }

    /**
     * 加载下一页moments
     */
    public function loadNextPageViaHtml()
    {
        $page = htmlspecialchars($_REQUEST['page']);
        $list = D('Moment')->getNextPage($page);
        if (false === $list) {
            return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
        }

        foreach ($list as $key => &$value) {
            $value['user_name'] = htmlspecialchars($value['user_name']);
            $value['time'] = $this->obj->tranTime(strtotime($value['time']));
            $value['info'] = str_replace("\n", "<br>", htmlspecialchars($value['info']));
        }

        $this->assign('page', $page);
        $this->assign('list', $list);
        $this->display("Moments/flow");
    }

    /**
     * 加载未读消息数量
     */
    public function loadNews()
    {
        $user_name = $_SESSION["user_name"];
        $map['user_name'] = $user_name;
        $user_id = D('User')->getUserId($map);
        $list = D('Moment')->getNews($user_id);
        $map1['requested_id'] = $user_id;
        $map1['state'] = 1;
        $result = D('FriendRequest')->getFriendRequest($map1);
        $num = count($list) + count($result);
        $ret = array(
            "number" => $num,
        );

        return ResponseUtils::json(ErrCodeUtils::SUCCESS, $ret);
    }

    /**
     * moment详情页
     */
    public function details()
    {
        session_start();
        $userName = $_SESSION["user_name"];
        $userId = $_SESSION["user_id"];
        $momentId = $_REQUEST['id'];
        $result = D('Moment')->getOneMoment($momentId);
        if (false === $result) {
            return ResponseUtils::json(ErrCodeUtils::SYSTEM_ERROR);
        }

        foreach ($result as $key => &$value) {
            $value['user_name'] = htmlspecialchars($value['user_name']);
            $value['time'] = $this->obj->tranTime(strtotime($value['time']));
            $value['info'] = htmlspecialchars($value['info']);
        }

        $script = "<script>const GLOBAL_USER_NAME = \"" . $userName . "\"; const GLOBAL_USER_ID = \"" . $userId . "\";</script>";

        $this->assign('details', $result[0]);
        $this->assign('script', $script);
        $this->display();
    }
}
