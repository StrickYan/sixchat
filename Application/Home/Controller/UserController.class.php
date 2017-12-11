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

use Think\Controller;
use util\SKErrorCode;
use util\SKUtility;

class UserController extends Controller
{
    public function getSessionUser()
    {
        $map = array(
            "user_name" => $_SESSION["name"],
        );
        $retData = D('User')->getUser($map);
        // var_dump($retData);exit;

        if ($retData === false) {
            SKUtility::returnData(SKErrorCode::FAILED);
        }
        SKUtility::returnData(SKErrorCode::SUCCESS, $retData[0]);
    }

    public function getUser()
    {
        $map = $_REQUEST;
        $retData = D('User')->getUser($map);
        // var_dump($retData);exit;

        if ($retData === false) {
            SKUtility::returnData(SKErrorCode::FAILED);
        }
        SKUtility::returnData(SKErrorCode::SUCCESS, $retData);
    }

}