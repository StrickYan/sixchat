<?php
namespace Home\Model;

use Think\Model;

class MomentModel extends Model
{

    //获取朋友圈信息流
    public function getMoments()
    {
        $sql    = "CALL proc_MomentByFieldsSelect()";
        $result = M()->query($sql);
        return $result;
    }

    //加载更多
    public function getNextPage($page)
    {
        $sql    = "CALL proc_MomentGetNextPage($page)";
        $result = M()->query($sql);
        return $result;
    }

    public function addMoment($data)
    {
        $this->data($data)->add();
    }

    public function getMaxMomentId()
    {
        return $this->max('moment_id');
    }

    public function getRollingWall()
    {
        $sql  = "select img_url,moment_id from think_moment where img_url <>'' and state=1 order by rand() limit 3"; //显示朋友圈信息流
        $list = M()->query($sql);
        return $list;
    }

    public function updateMomentState($condition)
    {
        $this->where($condition)->setField('state', 0);
    }

}
