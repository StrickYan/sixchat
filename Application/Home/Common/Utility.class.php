<?php
/**
 * Created by PhpStorm.
 * User: strick
 * Date: 2017/8/6
 * Time: 5:00
 */

namespace Home\Common;

class Utility
{
    public function qstr($s)
    {
        $x = "'" . str_replace("'", "''", $s) . "'";
        return $x;
    }
}