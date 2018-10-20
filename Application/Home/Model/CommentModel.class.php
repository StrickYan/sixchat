<?php

namespace Home\Model;

class CommentModel extends BaseModel
{
    // 获取moment的点赞人
    public function getLikes($id)
    {
        $sql = "CALL proc_CommentByUserNameSelect($id)";
        $result = M()->query($sql);
        return $result;
    }

    // 获取权限内可看到的moment点赞人
    public function getLikesInAuth($id, $userName, $momentUserName)
    {
        $sql = "";
        if (!strcmp($userName, $momentUserName)) {
            // 相等，浏览自己的帖子可以看到所有赞包括好友与非好友
            $sql = "SELECT u.user_name as like_name FROM think_like l,think_user u where l.like_user_id = u.user_id and l.moment_id=" . $id . " and l.state=1 order by l.like_id asc";
        } else {
            // 浏览他人的帖子时只能看到互为好友的赞或者 自己与该用户的赞
            $obj = new SixChatApi2016Controller();
            foreach ($obj->getUserId($userName, $momentUserName) as $k => $val) {
                $userId = $val["reply_id"];
                $momentUserId = $val["replyed_id"];
                $sql = "SELECT distinct(u.user_name) as like_name FROM think_like l, think_friend f, think_user u where l.like_user_id = u.user_id and l.moment_id=" . $id . " and l.state=1 and ((f.user_id=" . $userId . " and f.friend_id=l.like_user_id) OR (l.like_user_id=" . $momentUserId . ")) order by l.like_id asc"; //？？？？？查询结果三个重复待排查
            }
        }
        $result = M()->query($sql);
        return $result;
    }

    // 获取所有评论
    public function getComments1($id)
    {
        $sql = "CALL proc_CommentByFields1Select($id)";
        $result = M()->query($sql);
        return $result;
    }

    // 获取好友关系的评论或者 自己与该用户的对话
    public function getComments2($id, $userId, $momentUserId)
    {
        $sql = "CALL proc_CommentByFields2Select($id, $userId, $momentUserId)";
        $result = M()->query($sql);
        return $result;
    }


    // 获取moment的点赞人
    public function getAllLikes()
    {
        $sql = "
            SELECT 
                group_concat(distinct(u.user_name) SEPARATOR ', ') as reply_names,c.moment_id,group_concat(c.comment_id) as comment_ids
            FROM 
                think_comment c,think_user u 
            where 
                c.reply_id = u.user_id and c.state=1 and c.type=1 
            group by 
                c.moment_id
            order by 
                c.moment_id desc
        ";
        return M()->query($sql);
    }

    public function getAllComments()
    {
        $sql = '
            SELECT 
                u1.user_name as reply_name,u2.user_name as replyed_name,c.moment_id,c.comment_id,c.comment,c.time,c.comment_level 
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
        return $this->where($condition)->setField('state', 0);
    }

    public function addComment($data)
    {
        return $this->data($data)->add();
    }

    public function getMaxCommentId()
    {
        return $this->max('comment_id');
    }

    public function getUnreadMessagesViaUserId($userId)
    {
        $sql = "SELECT user_name as reply_name,avatar,moment_id,comment,time FROM think_comment c,think_user u where c.reply_id=u.user_id and state=1 and ((reply_id<>" . $userId . " and reply_id=replyed_id and moment_id in(select moment_id from think_moment where user_id=" . $userId . ")) or (replyed_id=" . $userId . " and reply_id<>replyed_id)) order by comment_id desc limit 0,100";
        return M()->query($sql);
    }

    public function updateNewsViaUserId($userId)
    {
        $sql = "UPDATE think_comment SET news=0 WHERE state=1 and news=1 and ((reply_id<>" . $userId . " and reply_id=replyed_id and moment_id in(select moment_id from think_moment where user_id=" . $userId . ")) or (replyed_id=" . $userId . " and reply_id<>replyed_id)) ";
        return M()->execute($sql);
    }

    public function getNews($userId)
    {
        $sql = "SELECT moment_id FROM think_comment c,think_user u where c.reply_id=u.user_id and state=1 and news=1 and ((reply_id<>" . $userId . " and reply_id=replyed_id and moment_id in (select moment_id from think_moment where user_id=" . $userId . ")) or (replyed_id=" . $userId . " and reply_id<>replyed_id)) order by comment_id desc limit 0,100";
        $list = M()->query($sql);
        return $list;
    }
}
