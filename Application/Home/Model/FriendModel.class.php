<?php
namespace Home\Model;

use Think\Model;

class FriendModel extends Model
{
    public function addFriend($data)
    {
        return $this->data($data)->filter('htmlspecialchars')->add();
    }
}
