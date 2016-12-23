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

}
