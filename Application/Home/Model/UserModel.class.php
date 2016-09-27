<?php
namespace Home\Model;
use Think\Model;
class UserModel extends Model{

    //获取头像链接
    public function getAvatar($user_name)
    {
        $sql = "CALL proc_UserByAvatarSelect($user_name)";
        $result = M()->query($sql);
        return $result;
    }


}
