<?php
/***************************************************************************
 *
 * Copyright (c) 2017 beishanwen.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * Created by PhpStorm.
 * User: strick
 * Date: 2017/12/11
 * Time: 下午4:11
 */

namespace Home\Service;

use Util\ErrCodeUtils;
use Util\ResponseUtils;
use Util\ParamsUtils;
use Util\UploadImgUtils;
use Util\TimeUtils;

class MomentsService extends BaseService
{
    private $arrInput;

    /**
     * @brief 初始化
     * @author strick@beishanwen.com
     * @param void
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->arrInput = ParamsUtils::get();
    }

    /**
     * @brief 发送 moment
     * @author strick@beishanwen.com
     * @param void
     * @return array
     */
    public function addMoment()
    {
        $params = $this->arrInput;

        $textBox = $params['text_box'] ?? ''; //获取朋友圈文本内容
        $imageName = '';

        if (empty($textBox) && empty($_FILES['upfile']['tmp_name'])) {
            // echo "没有内容";
            return ResponseUtils::arrayRet(ErrCodeUtils::PARAMS_INVALID, array(), $_FILES['upfile']['error']);
        }

        // 上传图片
        if (!empty($_FILES['upfile']['tmp_name'])) {
            $destinationFolder = "moment_img/"; //上传文件路径
            $inputFileName = "upfile";
            $maxWidth = 640;
            $maxHeight = 1136;
            $uploadResult = UploadImgUtils::uploadImg($destinationFolder, $inputFileName, $maxWidth, $maxHeight); //调用上传函数
            if (ErrCodeUtils::SUCCESS !== $uploadResult['code']) {
                return ResponseUtils::arrayRet($uploadResult['code'], array(), $uploadResult['msg']);
            }

            // 有图片上传且上传成功返回图片名
            $imageName = $uploadResult['data']['image_name'];
        }

        // 新增朋友圈
        $insertData = array(
            'user_id' => $params['session_user_id'],
            'info' => $textBox,
            'img_url' => $imageName,
            'time' => date("Y-m-d H:i:s"),
        );
        $newMomentId = D('Moment')->addMoment($insertData);
        if (false === $newMomentId) {
            return ResponseUtils::arrayRet(ErrCodeUtils::SYSTEM_ERROR);
        }

        $response = array(
            'isSuccess' => true,
            'moment_id' => $newMomentId,
            'user_name' => $params['session_user_name'],
            'avatar' => $params['session_user_avatar'],
            'text_box' => $textBox,
            'photo' => $imageName,
            'time' => TimeUtils::tranTime(strtotime(date("Y-m-d H:i:s"))),
        );

        return ResponseUtils::arrayRet(ErrCodeUtils::SUCCESS, $response);
    }

    /**
     * @brief 删除 moment
     * @author strick@beishanwen.com
     * @param void
     * @return array
     */
    public function deleteMoment()
    {
        $params = $this->arrInput;

        $model = M();
        $model->startTrans();

        $condition = array(
            'moment_id' => $params['moment_id'],
        );
        $ret = D('Moment')->updateMomentState($condition);
        if (false === $ret) {
            $model->rollback();
            return ResponseUtils::arrayRet(ErrCodeUtils::SYSTEM_ERROR);
        }

        // 删除moment的时候连带删除其下所有评论
        $ret = D('Comment')->updateCommentState($condition);
        if (false === $ret) {
            $model->rollback();
            return ResponseUtils::arrayRet(ErrCodeUtils::SYSTEM_ERROR);
        }

        $model->commit();

        return ResponseUtils::arrayRet(ErrCodeUtils::SUCCESS);
    }

    /**
     * @brief 查看一条朋友圈
     * @author strick@beishanwen.com
     * @param void
     * @return array
     */
    public function getOneMoment()
    {
        $params = $this->arrInput;

        $list = D('Moment')->getOneMoment($params['moment_id']);
        if (false === $list) {
            return ResponseUtils::arrayRet(ErrCodeUtils::SYSTEM_ERROR);
        }

        foreach ($list as $key => &$value) {
            $value['text_box'] = $value['info'];
            $value['photo'] = $value['img_url'];
            $value['time'] = date("M j, Y H:i", strtotime($value['time']));
        }
        unset($value);

        return ResponseUtils::arrayRet(ErrCodeUtils::SUCCESS, $list);
    }

    /**
     * @brief 加载下一页moments
     * @author strick@beishanwen.com
     * @param void
     * @return array
     */
    public function loadNextPage()
    {
        $params = $this->arrInput;

        $list = D('Moment')->getNextPage($params['page']);
        if (false === $list) {
            return ResponseUtils::arrayRet(ErrCodeUtils::SYSTEM_ERROR);
        }

        foreach ($list as $key => &$value) {
            $value['user_name'] = htmlspecialchars($value['user_name']);
            $value['time'] = TimeUtils::tranTime(strtotime($value['time']));
            $value['info'] = htmlspecialchars($value['info']);
        }
        unset($value);

        return ResponseUtils::arrayRet(ErrCodeUtils::SUCCESS, $list);
    }

    /**
     * @brief 加载下一页moments
     * @author strick@beishanwen.com
     * @param void
     * @return array
     */
    public function loadNextPageViaHtml()
    {
        $params = $this->arrInput;

        $list = D('Moment')->getNextPage($params['page']);
        if (false === $list) {
            $list = array();
        }

        foreach ($list as $key => &$value) {
            $value['user_name'] = htmlspecialchars($value['user_name']);
            $value['time'] = TimeUtils::tranTime(strtotime($value['time']));
            $value['info'] = str_replace("\n", "<br>", htmlspecialchars($value['info']));
        }
        unset($value);

        return ResponseUtils::arrayRet(ErrCodeUtils::SUCCESS, $list);
    }

    /**
     * @brief moment详情页
     * @author strick@beishanwen.com
     * @param void
     * @return array
     */
    public function details()
    {
        $params = $this->arrInput;

        $userName = $params['session_user_name'];
        $userId = $params['session_user_id'];
        $momentId = $params['moment_id'];
        $result = D('Moment')->getOneMoment($momentId);
        if (false === $result) {
            $result = array();
        }

        foreach ($result as $key => &$value) {
            $value['user_name'] = htmlspecialchars($value['user_name']);
            $value['time'] = TimeUtils::tranTime(strtotime($value['time']));
            $value['info'] = htmlspecialchars($value['info']);
        }
        unset($value);

        $script = "<script>const GLOBAL_USER_NAME = \"" . $userName . "\"; const GLOBAL_USER_ID = \"" . $userId . "\";</script>";

        $ret = array(
            'details' => $result[0] ?? array(),
            'script' => $script,
        );

        return ResponseUtils::arrayRet(ErrCodeUtils::SUCCESS, $ret);
    }

    /**
     * @brief 选取随机三图做滚动墙纸
     * @author strick@beishanwen.com
     * @param void
     * @return array
     */
    public function getRollingWall()
    {
        $list = D('Moment')->getRollingWall();
        if (false === $list) {
            return ResponseUtils::arrayRet(ErrCodeUtils::SYSTEM_ERROR);
        }

        // 返回json数据
        $response[] = array(
            "img_url_1" => "1477756153.JPG",
            "moment_id_1" => 101,
            "img_url_2" => $list[0]['img_url'],
            "moment_id_2" => $list[0]['moment_id'],
            "img_url_3" => $list[1]['img_url'],
            "moment_id_3" => $list[1]['moment_id'],
        );

        return ResponseUtils::arrayRet(ErrCodeUtils::SUCCESS, $response);
    }
}
