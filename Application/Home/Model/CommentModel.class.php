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


    //获取moment的点赞人
    public function getAllLikes()
    {
        $sql = '
            SELECT 
                group_concat(distinct(u.user_name)) as reply_names,c.moment_id,group_concat(c.comment_id) as comment_ids
            FROM 
                think_comment c,think_user u 
            where 
                c.reply_id = u.user_id and c.state=1 and c.type=1 
            group by 
                c.moment_id
            order by 
                c.moment_id desc
        ';
        return M()->query($sql);
    }
    public function getAllComments()
    {
        $sql = '
            SELECT 
                u1.user_name as reply_name,u2.user_name as replyed_name,c.moment_id,c.comment_id,c.comment,c.time 
            from 
                think_comment c,think_user u1,think_user u2 
            where 
                c.reply_id=u1.user_id and c.replyed_id=u2.user_id and c.state=1 and c.type=2
            order by 
                c.moment_id desc,c.time asc
        ';
        return M()->query($sql);
    }

    public function getCommentId($condition)
    {
        return $this->where($condition)->getField('comment_id');
    }

    public function updateCommentState($condition)
    {
        $this->where($condition)->setField('state', 0);
    }

    public function addComment($data)
    {
        $this->data($data)->add();
    }

    public function getMaxCommentId()
    {
        return $this->max('comment_id');
    }

    public function getUnreadMessagesViaUserId($user_id)
    {
        // $sql = "SELECT user_name as reply_name,avatar,moment_id,comment,time FROM think_comment c,think_user u where c.reply_id=u.user_id and state=1 and ((reply_id<>".$user_id." and reply_id=replyed_id and moment_id in(select moment_id from think_moment where user_id=".$user_id." and state=1)) or (replyed_id=".$user_id." and reply_id<>replyed_id)) order by comment_id desc limit 0,20";

        $sql  = "SELECT user_name as reply_name,avatar,moment_id,comment,time FROM think_comment c,think_user u where c.reply_id=u.user_id and state=1 and ((reply_id<>" . $user_id . " and reply_id=replyed_id and moment_id in(select moment_id from think_moment where user_id=" . $user_id . ")) or (replyed_id=" . $user_id . " and reply_id<>replyed_id)) order by comment_id desc limit 0,100";
        $list = M()->query($sql);
        return $list;
    }

    public function updateNewsViaUserId($user_id)
    {
        $sql = "UPDATE think_comment SET news=0 WHERE state=1 and news=1 and ((reply_id<>" . $user_id . " and reply_id=replyed_id and moment_id in(select moment_id from think_moment where user_id=" . $user_id . ")) or (replyed_id=" . $user_id . " and reply_id<>replyed_id)) ";
        M()->execute($sql);
    }

}
