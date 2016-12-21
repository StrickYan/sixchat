<?php
namespace Home\Model;

use Think\Model;

class CommentModel extends Model
{

    //获取moment的点赞人
    public function getLikes($id)
    {
        $sql    = "CALL proc_CommentByUserNameSelect($id)";
        $result = M()->query($sql);
        return $result;
    }

    //获取权限内可看到的moment点赞人
    public function getLikesInAuth($id, $user_name, $moment_user_name)
    {
        $sql = "";
        if (!strcmp($user_name, $moment_user_name)) {
            //相等，浏览自己的帖子可以看到所有赞包括好友与非好友
            $sql = "SELECT u.user_name as like_name FROM think_like l,think_user u where l.like_user_id = u.user_id and l.moment_id=" . $id . " and l.state=1 order by l.like_id asc";
        } else {
//浏览他人的帖子时只能看到互为好友的赞或者 自己与该用户的赞
            $obj = new SixChatApi2016Controller();
            foreach ($obj->getUserId($user_name, $moment_user_name) as $k => $val) {
                $user_id        = $val["reply_id"];
                $moment_user_id = $val["replyed_id"];
                $sql            = "SELECT distinct(u.user_name) as like_name FROM think_like l,think_friend f,think_user u where l.like_user_id = u.user_id and l.moment_id=" . $id . " and l.state=1 and ((f.user_id=" . $user_id . " and f.friend_id=l.like_user_id) OR (l.like_user_id=" . $moment_user_id . ")) order by l.like_id asc"; //？？？？？查询结果三个重复待排查
            }
        }
        $result = M()->query($sql);
        return $result;
    }

    //获取所有评论
    public function getComments1($id)
    {
        $sql    = "CALL proc_CommentByFields1Select($id)";
        $result = M()->query($sql);
        return $result;
    }

    //获取好友关系的评论或者 自己与该用户的对话
    public function getComments2($id, $user_id, $moment_user_id)
    {
        $sql    = "CALL proc_CommentByFields2Select($id,$user_id,$moment_user_id)";
        $result = M()->query($sql);
        return $result;
    }

}
