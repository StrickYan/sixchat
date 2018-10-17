<?php
/***************************************************************************
 *
 * Copyright (c) 2017 classmateer.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * Created by PhpStorm.
 * User: strick
 * Date: 2017/12/11
 * Time: 下午4:11
 */

namespace Home\Controller;

use util\ErrCodeUtils;
use util\ResponseUtils;

class UserController extends BaseController
{
    public function getSessionUser()
    {
        $map = array(
            "user_name" => $_SESSION["name"],
        );
        $retData = D('User')->getUser($map);
        // var_dump($retData);exit;

        if (false === $retData || count($retData) != 1) {
            ResponseUtils::json(ErrCodeUtils::FAILED);
        }
        ResponseUtils::json(ErrCodeUtils::SUCCESS, $retData[0]);
    }

    public function getUser()
    {
        $map = $_REQUEST;
        $retData = D('User')->getUser($map);
        // var_dump($retData);exit;

        if ($retData === false) {
            ResponseUtils::json(ErrCodeUtils::FAILED);
        }
        ResponseUtils::json(ErrCodeUtils::SUCCESS, $retData);
    }
}
