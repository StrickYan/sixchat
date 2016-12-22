<?php
namespace Home\Model;

use Think\Model;

class UserModel extends Model
{
    public function getUserAvatar($map)
    {
        return $this->where($map)->getField('avatar');
    }
}
