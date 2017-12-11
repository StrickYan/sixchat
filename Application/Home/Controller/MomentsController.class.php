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

class MomentsController extends BaseController
{
    /**
     * 显示信息流
     */
    public function index()
    {
        $script = "<script>const GLOBAL_USER_NAME = \"" . $_SESSION["name"] . "\";</script>";
        $this->assign('script', $script);
        $this->display();
    }

    /**
     * 获取某条moment的所有赞
     */
    public function getLikes()
    {
        if (isset($_REQUEST['id'])) {
            $id = htmlspecialchars($_REQUEST['id']); // moment_id
            if (is_numeric($id)) {
                $list = $this->commentModel->getLikes($id);
                foreach ($list as $key => &$value) {
                    $value['reply_name'] = htmlspecialchars($value['reply_name']);
                }
                echo json_encode($list);
            }
        }
    }

    /**
     * 获取某条moment的权限内可以看到的赞
     */
    public function getLikesInAuth()
    {
        if (isset($_REQUEST['id'])) {
            $id = htmlspecialchars($_REQUEST['id']); // moment_id
            $momentUserName = htmlspecialchars($_REQUEST['moment_user_name']); // 获取该条朋友圈的用户名
            $userName = $_SESSION["name"]; //当前用户

            if (is_numeric($id)) {
                $list = $this->commentModel->getLikesInAuth($id, $userName, $momentUserName);
                foreach ($list as $key => &$value) {
                    $value['reply_name'] = htmlspecialchars($value['reply_name']);
                }
                echo json_encode($list);
            }
        }
    }

    /**
     * 获取某条moment的所有评论
     */
    public function getComments()
    {
        if (isset($_REQUEST['id'])) {
            $id = htmlspecialchars($_REQUEST['id']);
            if (is_numeric($id)) {
                $list = $this->commentModel->getComments1($id);
                foreach ($list as $key => &$value) {
                    $value = array(
                        "reply_name" => htmlspecialchars($value['reply_name']),
                        "replyed_name" => htmlspecialchars($value['replyed_name']),
                        "comment_id" => $value['comment_id'],
                        "comment" => htmlspecialchars($value['comment']),
                        "time" => $value['time']);
                }
                echo json_encode($list);
            }
        }
    }

    /**
     * 获取所有的赞
     */
    public function getAllLikes()
    {
        $list = $this->commentModel->getAllLikes();
        foreach ($list as $key => &$value) {
            $value['reply_names'] = htmlspecialchars($value['reply_names']);
        }
        echo json_encode($list);
    }

    /**
     * 获取所有的评论
     */
    public function getAllComments()
    {
        $list = $this->commentModel->getAllComments();
        foreach ($list as $key => &$value) {
            $value = array(
                "reply_name" => htmlspecialchars($value['reply_name']),
                "replyed_name" => htmlspecialchars($value['replyed_name']),
                "comment_id" => $value['comment_id'],
                "moment_id" => $value['moment_id'],
                "comment" => htmlspecialchars($value['comment']),
                "time" => $value['time']);
        }
        echo json_encode($list);
    }

    /**
     * 获取每条朋友圈下面权限内可阅的评论
     */
    public function getCommentsInAuth()
    {
        if (isset($_REQUEST['id'])) {
            $id = htmlspecialchars($_REQUEST['id']);
            $momentUserName = htmlspecialchars($_REQUEST['moment_user_name']); // 获取该条朋友圈的用户名
            $userName = $_SESSION["name"]; //当前用户

            if (is_numeric($id)) {
                $list = array();
                if (!strcmp($userName, $momentUserName)) {
                    // 相等，浏览自己的帖子可以看到所有评论包括好友与非好友
                    $list = $this->commentModel->getComments1($id);
                } else {
                    // 浏览他人的帖子时只能看到互为好友的评论或者 自己与该用户的对话
                    foreach ($this->obj->getUserId($userName, $momentUserName) as $k => $val) {
                        $userId = $val["reply_id"];
                        $momentUserId = $val["replyed_id"];
                        $list = $this->commentModel->getComments2($id, $userId, $momentUserId); //好友关系可见
                    }
                }
                foreach ($list as $key => &$value) {
                    $value = array(
                        "reply_name" => htmlspecialchars($value['reply_name']),
                        "replyed_name" => htmlspecialchars($value['replyed_name']),
                        "comment_id" => $value['comment_id'],
                        "comment" => htmlspecialchars($value['comment']),
                        "time" => $value['time']);
                }
                echo json_encode($list);
            }
        }
    }

    /**
     * 点赞
     */
    public function addLike()
    {
        if (isset($_REQUEST['moment_id']) && isset($_REQUEST['moment_user_name'])) {
            $momentId = htmlspecialchars($_REQUEST['moment_id']);
            $replyName = $_SESSION["name"];
            $replyedName = htmlspecialchars($_REQUEST['moment_user_name']);

            foreach ($this->obj->getUserId($replyName, $replyedName) as $k => $val) {
                $replyId = $val["reply_id"];
                $replyedId = $val["replyed_id"];

                $condition['moment_id'] = $momentId;
                $condition['reply_id'] = $replyId;
                $condition['replyed_id'] = $replyedId;
                $condition['state'] = 1;
                $condition['type'] = 1;
                $result = $this->commentModel->getCommentId($condition);

                if ($result) {
                    // 已点赞 则删除赞记录
                    $data['comment_id'] = $result;
                    $this->commentModel->updateCommentState($data);
                } else {
                    // 没有点赞记录 则增加点赞
                    $data['moment_id'] = $momentId;
                    $data['reply_id'] = $replyId;
                    $data['replyed_id'] = $replyedId;
                    $data['time'] = date("Y-m-d H:i:s");
                    $data['type'] = 1;
                    $data['comment'] = "赞了你";
                    $this->commentModel->addComment($data);
                }
            }
            $list[0] = "点赞成功";
            echo json_encode($list);
        }
    }

    /**
     * 发布评论
     */
    public function addComment()
    {
        if (isset($_REQUEST['moment_id']) && isset($_REQUEST['replyed_name']) && isset($_REQUEST['comment_val'])) {
            $momentId = htmlspecialchars($_REQUEST['moment_id']);
            $replyName = $_SESSION["name"];
            $replyedName = htmlspecialchars($_REQUEST['replyed_name']);
            $commentVal = htmlspecialchars(trim($_REQUEST['comment_val']));

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
                $this->commentModel->addComment($data);
            }

            // 获取新增评论的 comment_id
            $commentId = $this->commentModel->getMaxCommentId();

            // 返回json数据
            $list[] = array(
                "comment_id" => $commentId,
                "moment_id" => $momentId,
                "reply_name" => $replyName,
                "replyed_name" => $replyedName,
                "comment_val" => $commentVal);
            echo json_encode($list);
        }
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
            echo $_FILES['upfile']['error'];
            echo "没有内容";
            exit;
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
        $userName = $_SESSION["name"];
        foreach ($this->obj->getUserId($userName, $userName) as $k => $val) {
            $userId = $val["reply_id"];

            // 插入朋友圈
            $data['user_id'] = $userId;
            $data['info'] = $textBox;
            $data['img_url'] = $imageName;
            $data['time'] = date("Y-m-d H:i:s");
            $this->momentModel->addMoment($data);
        }

        // 获取自己头像
        $map['user_name'] = $userName;
        $avatar = $this->userModel->getUserAvatar($map);

        // 获取新增朋友圈的 moment_id
        $moment_id = $this->momentModel->getMaxMomentId();

        $response['isSuccess'] = true;
        $response['moment_id'] = $moment_id;
        $response['user_name'] = $userName;
        $response['avatar'] = $avatar;
        $response['text_box'] = $textBox;
        $response['photo'] = $imageName;
        $response['time'] = $this->obj->tranTime(strtotime(date("Y-m-d H:i:s")));
        echo json_encode($response);
    }

    /**
     * 选取随机三图做滚动墙纸
     */
    public function getRollingWall()
    {
        $list = $this->momentModel->getRollingWall();

        //返回json数据
        $response[] = array(
            "img_url_1" => "1477756153.JPG",
            "moment_id_1" => 101,
            "img_url_2" => $list[0]['img_url'],
            "moment_id_2" => $list[0]['moment_id'],
            "img_url_3" => $list[1]['img_url'],
            "moment_id_3" => $list[1]['moment_id'],
        );
        echo json_encode($response);
    }

    /**
     * 删除 moment
     */
    public function deleteMoment()
    {
        $momentId = htmlspecialchars($_REQUEST['moment_id']);
        $condition['moment_id'] = $momentId;
        $this->momentModel->updateMomentState($condition);
        $this->commentModel->updateCommentState($condition); // 删除moment的时候连带删除其下所有评论
        $list[0] = "Delete moment is success.";
        echo json_encode($list);
    }

    /**
     * 删除 comment
     */
    public function deleteComment()
    {
        $commentId = htmlspecialchars($_REQUEST['comment_id']);
        $condition['comment_id'] = $commentId;
        $this->commentModel->updateCommentState($condition);
        $list[0] = "Delete comment is success.";
        echo json_encode($list);
    }

    /**
     * 显示赞与评论
     */
    public function loadMessages()
    {
        $userName = $_SESSION["name"];
        $map['user_name'] = $userName;
        $userId = $this->userModel->getUserId($map);
        $list = $this->commentModel->getUnreadMessagesViaUserId($userId);
        foreach ($list as $key => &$value) {
            $value['time'] = $this->obj->tranTime(strtotime($value['time']));
        }
        $this->commentModel->updateNewsViaUserId($userId);
        echo json_encode($list);
    }

    /**
     * 查看一条朋友圈
     */
    public function getOneMoment()
    {
        $momentId = htmlspecialchars($_POST['moment_id']);
        $myName = $_SESSION["name"];
        $list = $this->momentModel->getOneMoment($momentId);
        foreach ($list as $key => &$value) {
            //$value['my_name'] = $myName;
            $value['user_name'] = $value['user_name'];
            $value['avatar'] = $value['avatar'];
            $value['text_box'] = $value['info'];
            $value['photo'] = $value['img_url'];
            $value['moment_id'] = $momentId;
            $value['time'] = date("M j, Y H:i", strtotime($value['time']));
            echo json_encode($list);
        }
    }

    /**
     * 查找用户资料
     */
    public function searchUser()
    {
        $searchName = htmlspecialchars($_REQUEST['search_name']);
        $userName = $_SESSION['name'];
        $map['user_name'] = $searchName;
        $list = $this->userModel->searchUser($map);

        $data = array(
            'avatar' => $list['avatar'],
            'user_name' => $list['user_name'],
            'sex' => $list['sex'],
            'region' => $list['region'],
            'whatsup' => $list['whatsup'],
            'is_follow' => 0,
        );

        foreach ($this->obj->getUserId($userName, $searchName) as $k => $val) {
            $userId = $val["reply_id"];
            $friendId = $val["replyed_id"];

            $map1['user_id'] = $userId;
            $map1['friend_id'] = $friendId;
            $result = $this->friendModel->where($map1)->find();
            if ($result) {
                $data['is_follow'] = 1; // 已关注
            }

            $data['follow_id'] = $userId;
            $data['followed_id'] = $friendId;
        }
        echo json_encode($data);
    }

    /**
     * 关注或者取消关注
     **/
    public function follow()
    {
        $operationFollow = htmlspecialchars($_REQUEST['operation_follow']);
        $data = array(
            'user_id' => htmlspecialchars($_REQUEST['follow_id']),
            'friend_id' => htmlspecialchars($_REQUEST['followed_id']),
            'time' => date("Y-m-d H:i:s"),
        );

        $ret = false;

        if ($operationFollow == 1) {
            $ret = $this->friendModel->addFriend($data); // 添加关注记录
        } else if ($operationFollow == 0) {
            $where = array(
                'user_id' => $data['user_id'],
                'friend_id' => $data['friend_id'],
            );
            $ret = $this->friendModel->deleteFriend($where); // 取消关注
        }

        echo json_encode(array("is_success" => ($ret === false ? 0 : 1)));
    }

    /**
     * 修改资料
     */
    public function modifyProfile()
    {
        $profileNameBox = isset($_POST['profile_name_box']) ? htmlspecialchars($_POST['profile_name_box']) : ''; //获取文本内容
        $profileSexBox = isset($_POST['profile_sex_box']) ? htmlspecialchars($_POST['profile_sex_box']) : '';
        $profileRegionBox = isset($_POST['profile_region_box']) ? htmlspecialchars($_POST['profile_region_box']) : '';
        $profileWhatsupBox = isset($_POST['profile_whatsup_box']) ? htmlspecialchars($_POST['profile_whatsup_box']) : '';

        if (!$profileNameBox && !$profileSexBox && !$profileRegionBox && !$profileWhatsupBox && empty($_FILES['profile_upfile']['tmp_name'])) {
            echo "没有内容";
            exit;
        }

        $response = array();
        $destinationFolder = "avatar_img/"; //上传文件路径
        $inputFileName = "profile_upfile";
        $maxWidth = 200;
        $maxHeight = 200;
        $uploadResult = $this->obj->uploadImg($destinationFolder, $inputFileName, $maxWidth, $maxHeight); //上传头像
        if ($uploadResult) {
            //有图片上传且上传成功返回图片名
            $image_name = $uploadResult;
            //上传成功后进行修改数据库图片路径操作
            $map['user_name'] = $_SESSION['name'];
            $data = array(
                'avatar' => $image_name,
            );
            $this->userModel->updateUser($map, $data);
            unset($map);
        }
        $userName = $_SESSION["name"];
        foreach ($this->obj->getUserId($userName, $userName) as $k => $val) {
            $userId = $val["reply_id"];

            $condition = array(
                'user_name' => array('eq', $profileNameBox),
                'user_id' => array('neq', $userId),
            );
            $ret = $this->userModel->searchUser($condition);
            if (!empty($ret)) {
                $response['isSuccess'] = false;
                $response['msg'] = '该用户名已存在';
                echo json_encode($response);
                exit;
            }

            $map['user_id'] = $userId;
            $data = array(
                'user_name' => $profileNameBox,
                'sex' => $profileSexBox,
                'region' => $profileRegionBox,
                'whatsup' => $profileWhatsupBox,
            );
            $this->userModel->updateUser($map, $data);
        }
        $_SESSION["name"] = $profileNameBox;
        $response['isSuccess'] = true;
        echo json_encode($response);
    }

    /**
     * 加载下一页moments
     */
    public function loadNextPage()
    {
        $page = htmlspecialchars($_REQUEST['page']);
        $list = $this->momentModel->getNextPage($page);

        foreach ($list as $key => &$value) {
            $value['user_name'] = htmlspecialchars($value['user_name']);
            $value['time'] = $this->obj->tranTime(strtotime($value['time']));
            $value['info'] = htmlspecialchars($value['info']);
        }
        echo json_encode($list);
    }

    /**
     * 加载下一页moments
     */
    public function loadNextPageViaHtml()
    {
        $page = htmlspecialchars($_REQUEST['page']);
        $list = $this->momentModel->getNextPage($page);

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
        $user_name = $_SESSION["name"];
        $map['user_name'] = $user_name;
        $user_id = $this->userModel->getUserId($map);
        $list = $this->momentModel->getNews($user_id);
        $map1['requested_id'] = $user_id;
        $map1['state'] = 1;
        $result = $this->friendRequestModel->getFriendRequest($map1);
        $num = count($list) + count($result);
        echo json_encode(array("number" => $num));
    }

    /**
     * 注销
     */
    public function logout()
    {
        $this->obj->logout();
        header("Location:index");
    }

    /**
     * moment详情页
     */
    public function details()
    {
        session_start();
        $userName = $_SESSION["name"];
        $momentId = $_REQUEST['id'];
        $result = $this->momentModel->getOneMoment($momentId);

        foreach ($result as $key => &$value) {
            $value['user_name'] = htmlspecialchars($value['user_name']);
            $value['time'] = $this->obj->tranTime(strtotime($value['time']));
            $value['info'] = htmlspecialchars($value['info']);
        }

        $script = "<script>const GLOBAL_USER_NAME = \"" . $userName . "\";</script>";

        $this->assign('details', $result[0]);
        $this->assign('script', $script);
        $this->display();
    }

}
