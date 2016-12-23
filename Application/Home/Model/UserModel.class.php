<?php
namespace Home\Model;

use Think\Model;

class UserModel extends Model
{
    public function getUserAvatar($map)
    {
        return $this->where($map)->getField('avatar');
    }

    public function getUserId($map)
    {
        return $this->where($map)->getField('user_id');
    }

    public function searchUser($map)
    {
        return $this->where($map)->find();
    }

    public function getUserName($map)
    {
        return $this->where($map)->getField('user_name');
    }

    public function updateUser($map, $data)
    {
        return $this->where($map)->setField($data);
    }

    public function addUser($data)
    {
        return $this->data($data)->filter('htmlspecialchars')->add();
    }

    public function getUserIdViaUserName($condition)
    {
        //原生SQL查询版本
        //     $sql = "";
        //     $sql = "
        //     select u1.user_id as reply_id,u2.user_id as replyed_id
        //         from think_user u1,think_user u2
        // where u1.user_name=".$reply_name." and u2.user_name=".$replyed_name.";";  //三表联合查询
        //     $result = M()->query($sql);

        $result = $this->table('think_user u1,think_user u2')
            ->field('u1.user_id as reply_id,u2.user_id as replyed_id')
            ->where($condition)
            ->select();
        return $result;
    }

    public function getUserNameViaUserId($condition)
    {
        $result = $Model->table('think_user u1,think_user u2')
            ->field('u1.user_nname as reply_name,u2.user_name as replyed_name')
            ->where($condition)
            ->select();
        return $result;
    }

}
