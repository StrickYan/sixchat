<?php
namespace Home\Controller;

use Think\Controller;

class MomentsController extends CommonController
{

    // 显示朋友圈信息流
    public function index()
    {
        session_start();
        $user_name        = $_SESSION["name"];
        $map['user_name'] = $user_name; //获取自己头像
        $avatar           = $this->userModel->getUserAvatar($map);
        // $list             = $this->momentModel->getMoments(); //获取朋友圈信息流
        $list = array();

        foreach ($list as $key => &$value) {
            $value['user_name'] = htmlspecialchars($value['user_name']);
            $value['time']      = $this->obj->tranTime(strtotime($value['time']));
            $value['info']      = htmlspecialchars($value['info']);
        }

        $script = "<script>var global_user_name = \"" . $user_name . "\";</script>";

        $this->assign('list', $list);
        $this->assign('avatar', $avatar);
        $this->assign('my_name', $user_name);
        $this->assign('script', $script);
        $this->display();
    }

    // 获取所有赞
    public function getLikes()
    {
        if (isset($_REQUEST['id'])) {
            $id = htmlspecialchars($_REQUEST['id']); //moment_id
            if (is_numeric($id)) {
                $list = $this->commentModel->getLikes($id);
                foreach ($list as $key => &$value) {
                    $value['reply_name'] = htmlspecialchars($value['reply_name']);
                }
                echo json_encode($list);
            }
        }
    }

    //获取权限内可以看到的赞
    public function getLikesInAuth()
    {
        if (isset($_REQUEST['id'])) {
            $id               = htmlspecialchars($_REQUEST['id']); //moment_id
            $moment_user_name = htmlspecialchars($_REQUEST['moment_user_name']); //获取该条朋友圈的用户名
            $user_name        = $_SESSION["name"]; //当前用户

            if (is_numeric($id)) {
                $list = $this->commentModel->getLikesInAuth($id, $user_name, $moment_user_name);
                foreach ($list as $key => &$value) {
                    $value['reply_name'] = htmlspecialchars($value['reply_name']);
                }
                echo json_encode($list);
            }
        }
    }

    // 加载每条朋友圈下面所有的评论
    public function getComments()
    {
        if (isset($_REQUEST['id'])) {
            $id = htmlspecialchars($_REQUEST['id']);
            if (is_numeric($id)) {
                $list = $this->commentModel->getComments1($id);
                foreach ($list as $key => &$value) {
                    $value = array(
                        "reply_name"   => htmlspecialchars($value['reply_name']),
                        "replyed_name" => htmlspecialchars($value['replyed_name']),
                        "comment_id"   => $value['comment_id'],
                        "comment"      => htmlspecialchars($value['comment']),
                        "time"         => $value['time']);
                }
                echo json_encode($list);
            }
        }
    }

    // 获取所有赞
    public function getAllLikes()
    {
        $list = $this->commentModel->getAllLikes();
        foreach ($list as $key => &$value) {
            $value['reply_names'] = htmlspecialchars($value['reply_names']);
        }
        echo json_encode($list);
    }
    // 加载所有朋友圈下面所有的评论
    public function getAllComments()
    {

        $list = $this->commentModel->getAllComments();
        foreach ($list as $key => &$value) {
            $value = array(
                "reply_name"   => htmlspecialchars($value['reply_name']),
                "replyed_name" => htmlspecialchars($value['replyed_name']),
                "comment_id"   => $value['comment_id'],
                "moment_id"    => $value['moment_id'],
                "comment"      => htmlspecialchars($value['comment']),
                "time"         => $value['time']);
        }
        echo json_encode($list);
    }

    // 获取每条朋友圈下面权限内可阅的评论
    public function getCommentsInAuth()
    {
        if (isset($_REQUEST['id'])) {
            $id               = htmlspecialchars($_REQUEST['id']);
            $moment_user_name = htmlspecialchars($_REQUEST['moment_user_name']); //获取该条朋友圈的用户名
            $user_name        = $_SESSION["name"]; //当前用户

            if (is_numeric($id)) {
                $list = '';
                if (!strcmp($user_name, $moment_user_name)) {
                    //相等，浏览自己的帖子可以看到所有评论包括好友与非好友
                    $list = $this->commentModel->getComments1($id);
                } else {
                    //浏览他人的帖子时只能看到互为好友的评论或者 自己与该用户的对话
                    foreach ($this->obj->getUserId($user_name, $moment_user_name) as $k => $val) {
                        $user_id        = $val["reply_id"];
                        $moment_user_id = $val["replyed_id"];

                        $list = $this->commentModel->getComments2($id, $user_id, $moment_user_id); //好友关系可见

                    }
                }
                foreach ($list as $key => &$value) {
                    $value = array(
                        "reply_name"   => htmlspecialchars($value['reply_name']),
                        "replyed_name" => htmlspecialchars($value['replyed_name']),
                        "comment_id"   => $value['comment_id'],
                        "comment"      => htmlspecialchars($value['comment']),
                        "time"         => $value['time']);
                }
                echo json_encode($list);
            }
        }
    }

    // 点赞
    public function addLike()
    {
        if (isset($_REQUEST['moment_id']) && isset($_REQUEST['moment_user_name'])) {
            $moment_id    = htmlspecialchars($_REQUEST['moment_id']);
            $reply_name   = $_SESSION["name"];
            $replyed_name = htmlspecialchars($_REQUEST['moment_user_name']);

            foreach ($this->obj->getUserId($reply_name, $replyed_name) as $k => $val) {
                $reply_id   = $val["reply_id"];
                $replyed_id = $val["replyed_id"];

                $condition['moment_id']  = $moment_id;
                $condition['reply_id']   = $reply_id;
                $condition['replyed_id'] = $replyed_id;
                $condition['state']      = 1;
                $condition['type']       = 1;
                $result                  = $this->commentModel->getCommentId($condition);

                if ($result) {
                    //已点赞 则删除赞记录
                    $data['comment_id'] = $result;
                    $this->commentModel->updateCommentState($data);
                } else {
                    //没有点赞记录 则增加点赞
                    //插入赞
                    $data['moment_id']  = $moment_id;
                    $data['reply_id']   = $reply_id;
                    $data['replyed_id'] = $replyed_id;
                    $data['time']       = date("Y-m-d H:i:s");
                    $data['type']       = 1;
                    $data['comment']    = "赞了你";
                    $this->commentModel->addComment($data);
                }
            }
            $list[0] = "点赞成功";
            echo json_encode($list);
        }
    }

    // 发布评论
    public function addComment()
    {
        if (isset($_REQUEST['moment_id']) && isset($_REQUEST['replyed_name']) && isset($_REQUEST['comment_val'])) {
            $moment_id    = htmlspecialchars($_REQUEST['moment_id']);
            $reply_name   = $_SESSION["name"];
            $replyed_name = htmlspecialchars($_REQUEST['replyed_name']);
            $comment_val  = htmlspecialchars(trim($_REQUEST['comment_val']));

            foreach ($this->obj->getUserId($reply_name, $replyed_name) as $k => $val) {
                $reply_id   = $val["reply_id"];
                $replyed_id = $val["replyed_id"];

                //插入评论
                $data['moment_id']  = $moment_id;
                $data['reply_id']   = $reply_id;
                $data['replyed_id'] = $replyed_id;
                $data['comment']    = $comment_val;
                $data['time']       = date("Y-m-d H:i:s");
                $data['type']       = 2;
                $this->commentModel->addComment($data);
            }

            //获取新增评论的comment_id
            $comment_id = $this->commentModel->getMaxCommentId();

            //返回json数据
            $list[] = array(
                "comment_id"   => $comment_id,
                "moment_id"    => $moment_id,
                "reply_name"   => $reply_name,
                "replyed_name" => $replyed_name,
                "comment_val"  => $comment_val);
            echo json_encode($list);
        }
    }

    //发送朋友圈
    public function addMoment()
    {
        $text_box   = trim(isset($_POST['text_box'])) ? htmlspecialchars(trim($_POST['text_box'])) : ''; //获取朋友圈文本内容
        $image_name = '';
        $response   = array();

        if (!$text_box && empty($_FILES['upfile']['tmp_name'])) {
            echo $_FILES['upfile']['error'];
            echo "没有内容";
            exit;
        }
        $destination_folder = "moment_img/"; //上传文件路径
        $input_file_name    = "upfile";
        $maxwidth           = 640;
        $maxheight          = 1136;
        $upload_result      = $this->obj->uploadImg($destination_folder, $input_file_name, $maxwidth, $maxheight); //调用上传函数
        if ($upload_result) {
            //有图片上传且上传成功返回图片名
            $image_name = $upload_result;
        }
        $user_name = $_SESSION["name"];
        foreach ($this->obj->getUserId($user_name, $user_name) as $k => $val) {
            $user_id = $val["reply_id"];

            //插入朋友圈
            $data['user_id'] = $user_id;
            $data['info']    = $text_box;
            $data['img_url'] = $image_name;
            $data['time']    = date("Y-m-d H:i:s");
            $this->momentModel->addMoment($data);
        }

        //获取自己头像
        $map['user_name'] = $user_name;
        $avatar           = $this->userModel->getUserAvatar($map);

        //获取新增朋友圈的moment_id
        $moment_id = $this->momentModel->getMaxMomentId();

        $response['isSuccess'] = true;
        $response['moment_id'] = $moment_id;
        $response['user_name'] = $user_name;
        $response['avatar']    = $avatar;
        $response['text_box']  = $text_box;
        $response['photo']     = $image_name;
        $response['time']      = $this->obj->tranTime(strtotime(date("Y-m-d H:i:s")));
        echo json_encode($response);
    }

    //选取随机三图做滚动墙纸
    public function getRollingWall()
    {
        $list = $this->momentModel->getRollingWall();

        //返回json数据
        $response[] = array(
            "img_url_1"   => "1477756153.JPG",
            "moment_id_1" => 101,
            "img_url_2"   => $list[0]['img_url'],
            "moment_id_2" => $list[0]['moment_id'],
            "img_url_3"   => $list[1]['img_url'],
            "moment_id_3" => $list[1]['moment_id'],
        );
        echo json_encode($response);
    }

    // 删除 moment
    public function deleteMoment()
    {
        $moment_id              = htmlspecialchars($_REQUEST['moment_id']);
        $condition['moment_id'] = $moment_id;
        $this->momentModel->updateMomentState($condition);
        $this->commentModel->updateCommentState($condition); //删除moment的时候连带删除其下所有评论
        $list[0] = "Delete moment is success.";
        echo json_encode($list);
    }

    // 删除 comment
    public function deleteComment()
    {
        $comment_id              = htmlspecialchars($_REQUEST['comment_id']);
        $condition['comment_id'] = $comment_id;
        $this->commentModel->updateCommentState($condition);
        $list[0] = "Delete comment is success.";
        echo json_encode($list);
    }

    // 显示赞与评论
    public function loadMessages()
    {
        $user_name        = $_SESSION["name"];
        $map['user_name'] = $user_name;
        $user_id          = $this->userModel->getUserId($map);
        $list             = $this->commentModel->getUnreadMessagesViaUserId($user_id);
        foreach ($list as $key => &$value) {
            $value['time'] = $this->obj->tranTime(strtotime($value['time']));
        }
        $this->commentModel->updateNewsViaUserId($user_id);
        echo json_encode($list);
    }

    // 查看一条朋友圈
    public function getOneMoment()
    {
        $moment_id = htmlspecialchars($_POST['moment_id']);
        $my_name   = $_SESSION["name"];
        $list      = $this->momentModel->getOneMoment($moment_id);
        foreach ($list as $key => &$value) {
            $value['my_name']   = $my_name;
            $value['user_name'] = $value['user_name'];
            $value['avatar']    = $value['avatar'];
            $value['text_box']  = $value['info'];
            $value['photo']     = $value['img_url'];
            $value['moment_id'] = $moment_id;
            $value['time']      = date("M j, Y H:i", strtotime($value['time']));
            echo json_encode($list);
        }
    }

    // 查找用户资料
    public function searchUser()
    {
        $search_name      = htmlspecialchars($_REQUEST['search_name']);
        $user_name        = $_SESSION['name'];
        $map['user_name'] = $search_name;
        $list             = $this->userModel->searchUser($map);

        $list['is_friend'] = 0; //0代表不是好友关系
        foreach ($this->obj->getUserId($user_name, $search_name) as $k => $val) {
            $user_id   = $val["reply_id"];
            $friend_id = $val["replyed_id"];

            $map1['user_id']   = $user_id;
            $map1['friend_id'] = $friend_id;
            $result            = $this->friendModel->where($map1)->find();
            if ($result) {
                $list['is_friend'] = 1; //是好友关系
            }
        }
        echo json_encode($list);
    }

    // 好友请求
    public function friendRuquest()
    {
        $requested_name = htmlspecialchars($_REQUEST['requested_name']);
        $request_name   = $_SESSION['name'];
        $remark         = htmlspecialchars($_REQUEST['remark']);

        foreach ($this->obj->getUserId($request_name, $requested_name) as $k => $val) {
            $request_id   = $val["reply_id"];
            $requested_id = $val["replyed_id"];

            $map['request_id']   = $request_id;
            $map['requested_id'] = $requested_id;
            $map['state']        = 1;

            $map1['request_id']   = $requested_id;
            $map1['requested_id'] = $request_id;
            $map1['state']        = 1;

            $result_1 = $this->friendRuquestModel->getFriendRequest($map);
            $result_2 = $this->friendRuquestModel->getFriendRequest($map1);
            if ($result_1 || $result_2) {
                //已存在任意一方的请求则不进行操作
            } else {
                //插入好友请求
                $data['request_id']   = $request_id;
                $data['requested_id'] = $requested_id;
                $data['remark']       = $remark;
                $data['request_time'] = date("Y-m-d H:i:s");
                $this->friendRuquestModel->addFriendRequest($data);
            }
        }
        echo json_encode(array("result" => "ok"));
    }

    public function loadFriendRequest()
    {
        $user_name            = $_SESSION["name"];
        $map['user_name']     = $user_name;
        $user_id              = $this->userModel->getUserId($map);
        $map1['requested_id'] = $user_id;
        $map1['state']        = 1;
        $result               = $this->friendRuquestModel->getFriendRequest($map1);
        foreach ($result as $key => &$value) {
            $map2['user_id']       = $value['request_id'];
            $request_name          = $this->userModel->getUserName($map2);
            $avatar                = $this->userModel->getUserAvatar($map2);
            $value['request_name'] = $request_name;
            $value['avatar']       = $avatar;
            $value['id']           = $value['id'];
            $value['remark']       = $value['remark'];
            $value['time']         = $this->obj->tranTime(strtotime($value['request_time']));
        }
        echo json_encode($result);
    }

    // 处理好友请求
    public function agreeRequest()
    {
        $id             = htmlspecialchars($_REQUEST['id']); //该好友请求id
        $request_name   = htmlspecialchars($_REQUEST['request_name']); //请求人
        $requested_name = $_SESSION['name']; //被请求人

        $map['id'] = $id;
        $this->friendRuquestModel->setFriendRequestState($map);

        foreach ($this->obj->getUserId($request_name, $requested_name) as $k => $val) {
            $request_id   = $val["reply_id"];
            $requested_id = $val["replyed_id"];

            $data['user_id']   = $request_id;
            $data['friend_id'] = $requested_id;
            $data['time']      = date("Y-m-d H:i:s");
            $this->friendModel->addFriend($data); //好友表添加记录

            $data1['user_id']   = $requested_id;
            $data1['friend_id'] = $request_id;
            $data1['time']      = date("Y-m-d H:i:s");
            $this->friendModel->addFriend($data1); //双向好友

        }
        echo json_encode(array("result" => "ok"));
    }

    // 修改资料
    public function modifyProfile()
    {
        $profile_name_box    = isset($_POST['profile_name_box']) ? htmlspecialchars($_POST['profile_name_box']) : ''; //获取文本内容
        $profile_sex_box     = isset($_POST['profile_sex_box']) ? htmlspecialchars($_POST['profile_sex_box']) : '';
        $profile_region_box  = isset($_POST['profile_region_box']) ? htmlspecialchars($_POST['profile_region_box']) : '';
        $profile_whatsup_box = isset($_POST['profile_whatsup_box']) ? htmlspecialchars($_POST['profile_whatsup_box']) : '';

        if (!$profile_name_box && !$profile_sex_box && !$profile_region_box && !$profile_whatsup_box && empty($_FILES['profile_upfile']['tmp_name'])) {
            echo "没有内容";
            exit;
        }
        $response           = array();
        $image_name         = '';
        $destination_folder = "avatar_img/"; //上传文件路径
        $input_file_name    = "profile_upfile";
        $maxwidth           = 200;
        $maxheight          = 200;
        $upload_result      = $this->obj->uploadImg($destination_folder, $input_file_name, $maxwidth, $maxheight); //上传头像
        if ($upload_result) {
            //有图片上传且上传成功返回图片名
            $image_name = $upload_result;
            //上传成功后进行修改数据库图片路径操作
            $map['user_name'] = $_SESSION['name'];
            $data             = array(
                'avatar' => $image_name,
            );
            $this->userModel->updateUser($map, $data);
            unset($map);
        }
        $user_name = $_SESSION["name"];
        foreach ($this->obj->getUserId($user_name, $user_name) as $k => $val) {
            $user_id        = $val["reply_id"];
            $map['user_id'] = $user_id;
            $data           = array(
                'user_name' => $profile_name_box,
                'sex'       => $profile_sex_box,
                'region'    => $profile_region_box,
                'whatsup'   => $profile_whatsup_box,
            );
            $this->userModel->updateUser($map, $data);
        }
        $_SESSION["name"]      = $profile_name_box;
        $response['isSuccess'] = true;
        echo json_encode($response);
    }

    // 加载下一页moments
    public function loadNextPage()
    {
        $page = htmlspecialchars($_REQUEST['page']);
        $list = $this->momentModel->getNextPage($page);

        foreach ($list as $key => &$value) {
            $value['user_name'] = htmlspecialchars($value['user_name']);
            $value['time']      = $this->obj->tranTime(strtotime($value['time']));
            $value['info']      = htmlspecialchars($value['info']);
        }
        echo json_encode($list);
    }

    // 加载下一页moments
    public function loadNextPageViaHtml()
    {
        $page = htmlspecialchars($_REQUEST['page']);
        $list = $this->momentModel->getNextPage($page);

        foreach ($list as $key => &$value) {
            $value['user_name'] = htmlspecialchars($value['user_name']);
            $value['time']      = $this->obj->tranTime(strtotime($value['time']));
            $value['info']      = str_replace("\n", "<br>", htmlspecialchars($value['info']));
        }

        $this->assign('page', $page);
        $this->assign('list', $list);
        $this->display("Moments/flow");
    }

    // 加载未读消息数量
    public function loadNews()
    {
        $user_name            = $_SESSION["name"];
        $map['user_name']     = $user_name;
        $user_id              = $this->userModel->getUserId($map);
        $list                 = $this->momentModel->getNews($user_id);
        $map1['requested_id'] = $user_id;
        $map1['state']        = 1;
        $result               = $this->friendRuquestModel->getFriendRequest($map1);
        $num                  = count($list) + count($result);
        echo json_encode(array("number" => $num));
    }

    //注销
    public function logout()
    {
        $this->obj->logout();
        header("Location:index");
    }

    public function details()
    {
        session_start();
        $user_name = $_SESSION["name"];
        $momentId  = $_REQUEST['id'];
        $result    = $this->momentModel->getOneMoment($momentId);

        foreach ($result as $key => &$value) {
            $value['user_name'] = htmlspecialchars($value['user_name']);
            $value['time']      = $this->obj->tranTime(strtotime($value['time']));
            $value['info']      = htmlspecialchars($value['info']);
        }

        $script = "<script>var global_user_name = \"" . $user_name . "\";</script>";

        $this->assign('details', $result[0]);
        $this->assign('my_name', $user_name);
        $this->assign('script', $script);
        $this->display();
    }

}
