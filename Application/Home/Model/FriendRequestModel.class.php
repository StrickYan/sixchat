<?php
namespace Home\Model;

use Think\Model;

class FriendRequestModel extends Model
{
    public function getFriendRequest($map)
    {
        return $this->where($map)->select();
    }

    public function addFriendRequest($data)
    {
        return $this->data($data)->add();
    }

    public function setFriendRequestState($map)
    {
        return $this->where($map)->setField('state', 0);
    }
}
