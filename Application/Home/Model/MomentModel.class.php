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

}
