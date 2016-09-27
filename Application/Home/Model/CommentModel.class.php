<?php
namespace Home\Model;
use Think\Model;
class CommentModel extends Model{

    //获取moment的点赞人
    public function getLikes($id)
    {
        $sql = "CALL proc_CommentByUserNameSelect($id)";
        $result = M()->query($sql);
        return $result;
    }

    //获取所有评论
    public function getComments1($id)
    {
	    $sql = "CALL proc_CommentByFields1Select($id)";
	    $result = M()->query($sql);
        return $result;
    }

    //获取好友关系的评论或者 自己与该用户的对话
    public function getComments2($id,$user_id,$moment_user_id)
    {
		$sql = "CALL proc_CommentByFields2Select($id,$user_id,$moment_user_id)";
	    $result = M()->query($sql);
        return $result;
    }

}
