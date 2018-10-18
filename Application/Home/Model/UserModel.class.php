<?php

namespace Home\Model;

class UserModel extends BaseModel
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

    public function getUser($map)
    {
        return $this->table('think_user')
            ->field('user_id, user_name, avatar, sex, region, whatsup, register_time')
            ->where($map)
            ->select();
    }
}
