<?php
/***************************************************************************
 *
 * Copyright (c) 2017 beishanwen.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file Application/Home/Model/MomentModel.class.php
 * @author 1275330626(com@qq.com)
 * @date 2017/08/06 03:22:39
 * @brief
 *
 **/

namespace Home\Model;

use Util\SKUtility;

class MomentModel extends BaseModel
{

    //获取朋友圈信息流
    public function getMoments()
    {
        $sql = "CALL proc_MomentByFieldsSelect()";
        $result = M()->query($sql);
        return $result;
    }

    //加载更多
    public function getNextPage($page)
    {
        $userName = $_SESSION['user_name'];
        $sql = "
            select 
                u.user_name, u.avatar, m.info, m.img_url, m.time, m.moment_id
            from 
                think_moment m,
                think_user u,
                think_user u2,
                think_friend f
            where 
                m.state = 1 
                and m.user_id = f.friend_id and f.user_id = u2.user_id and u2.user_name = " . SKUtility::qstr($userName) . " 
                and m.user_id = u.user_id
            order by 
                m.time desc 
            limit " . ($page * 15) . ", 15
        ";

        //$sql = "CALL proc_MomentGetNextPage($page)";

        $result = M()->query($sql);
        return $result;
    }

    public function addMoment($data)
    {
        return $this->data($data)->add();
    }

    public function getMaxMomentId()
    {
        return $this->max('moment_id');
    }

    public function getRollingWall()
    {
        $sql = "select img_url,moment_id from think_moment where img_url <>'' and state=1 order by rand() limit 2"; //显示朋友圈信息流
        $list = M()->query($sql);
        return $list;
    }

    public function updateMomentState($condition)
    {
        return $this->where($condition)->setField('state', 0);
    }

    public function getOneMoment($momentId)
    {
        $sql = "
            SELECT 
                u.user_name,u.avatar,m.info,m.img_url,m.time,m.moment_id
            from 
                think_moment m,think_user u
            where 
                m.moment_id=" . $momentId . " and m.state=1 and m.user_id=u.user_id
        "; //显示该条朋友圈内容
        $list = M()->query($sql);
        return $list;
    }

    public function getNews($userId)
    {
        $sql = "SELECT moment_id FROM think_comment c,think_user u where c.reply_id=u.user_id and state=1 and news=1 and ((reply_id<>" . $userId . " and reply_id=replyed_id and moment_id in(select moment_id from think_moment where user_id=" . $userId . ")) or (replyed_id=" . $userId . " and reply_id<>replyed_id)) order by comment_id desc limit 0,100";
        $list = M()->query($sql);
        return $list;
    }

}
