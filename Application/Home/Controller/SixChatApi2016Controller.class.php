<?php
/***************************************************************************
 *
 * Copyright (c) 2017 classmateer.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @file Application/Home/Controller/SixChatApi2016Controller.class.php
 * @author 1275330626(com@qq.com)
 * @date 2017/08/06 03:22:39
 * @brief 功能封装集成Api,方便其他控制器调用
 *
 **/

namespace Home\Controller;

use Think\Controller;

class SixChatApi2016Controller extends Controller
{

    protected $momentModel;
    protected $userModel;
    protected $commentModel;
    protected $friendRequestModel;
    protected $friendModel;

    /**
     * SixChatApi2016Controller constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->momentModel = D('Moment');
        $this->userModel = D('User');
        $this->commentModel = D('comment');
        $this->friendRequestModel = D("FriendRequest");
        $this->friendModel = D("Friend");
    }

    /**
     * 登录API
     * @param string $id 用户名
     * @param string $password 密码
     * @return int
     */
    public function login($id, $password)
    {
        $condition['user_name'] = $id;
        $userName = $this->userModel->getUserName($condition);
        // 该用户不存在
        if (!$userName) {
            return -1;
        } else {
            // 用户存在，保存用户名cookie
            setcookie("id", "$id", time() + 60 * 60 * 24 * 7);
            $condition['password'] = md5($password);
            $userName = $this->userModel->getUserName($condition);
            // 登录成功
            if ($userName) {
                // 保存密码cookie
                setcookie("password", "$password", time() + 60 * 60 * 24 * 7, "/auth", "six.classmateer.com");
                session_start();
                $_SESSION["name"] = $userName;
                return 0;
            } else {
                return -2; //密码错误
            }

        }
    }

    /**
     * 注销
     */
    public function logout()
    {
        session_destroy();
        setcookie("password", "", time() - 3600, "/auth/login/", "six.classmateer.com");
    }

    /**
     * 注册
     * @param $id
     * @param $password
     * @return int
     */
    public function register($id, $password)
    {
        $condition['user_name'] = $id;
        $userName = $this->userModel->getUserName($condition);
        //该账号已存在
        if ($userName) {
            return -1;
        } else {
            //注册成功添加新用户
            $data['user_name'] = $id;
            $data['password'] = MD5($password);
            $data['register_time'] = date("Y-m-d H:i:s");
            $this->userModel->addUser($data);

            //查询该新用户的user_id
            $userId = $this->userModel->getUserId($condition);

            $saveData['time'] = date("Y-m-d H:i:s");

            //注册时自动关注自己
            $saveData['user_id'] = $userId;
            $saveData['friend_id'] = $userId;
            $this->friendModel->addFriend($saveData);

            //注册时自动和官方账号建立双向关系
            $saveData['user_id'] = $userId;
            $saveData['friend_id'] = 18;
            $this->friendModel->addFriend($saveData);
            $saveData['user_id'] = 18;
            $saveData['friend_id'] = $userId;
            $this->friendModel->addFriend($saveData);

            return 0;
        }
    }

    /**
     * 时间转换函数
     * @param $time
     * @return false|string
     */
    public function tranTime($time)
    {
        $fullTime = date("M j, Y H:i", $time);
        $time = time() - $time;
        if ($time < 60 * 2) {
            $str = '1 min ago ';
        } elseif ($time < 60 * 60) {
            $min = floor($time / 60);
            $str = $min . ' mins ago ';
        } elseif ($time < 60 * 60 * 24) {
            $h = floor($time / (60 * 60));
            if ($h == 1) {
                $str = '1 hour ago ';
            } else {
                $str = $h . ' hours ago ';
            }
        } elseif ($time < 60 * 60 * 24 * 7) {
            $d = floor($time / (60 * 60 * 24));
            if ($d == 1) {
                $str = 'yesterday ';
            } else {
                $str = $d . ' day(s) ago ';
            }
        } else {
            $str = $fullTime;
        }
        return $str;
    }

    /**
     * 以user_name匹配用户user_id
     * @param $replyName
     * @param $replyedName
     * @return mixed
     */
    public function getUserId($replyName, $replyedName)
    {
        $condition['u1.user_name'] = $replyName;
        $condition['u2.user_name'] = $replyedName;
        $result = $this->userModel->getUserIdViaUserName($condition);
        return $result;
    }

    /**
     * 以user_id匹配用户user_name
     * @param $replyId
     * @param $replyedId
     * @return mixed
     */
    public function getUserName($replyId, $replyedId)
    {
        $condition['u1.user_id'] = $replyId;
        $condition['u2.user_id'] = $replyedId;
        $result = $this->userModel->getUserNameViaUserId($condition);
        return $result;
    }

    /**
     * 图片上传函数
     * @param $destinationFolder
     * @param $inputFileName
     * @param $maxWidth
     * @param $maxHeight
     * @return bool|string
     */
    public function uploadImg($destinationFolder, $inputFileName, $maxWidth, $maxHeight)
    {

        /******************************************************************************
         * 参数说明:
         * $maxFileSize  : 上传文件大小限制, 单位BYTE
         * $destinationFolder : 上传文件路径
         * $inputFileName ：文件上传input的name
         * $maxWidth="640";//设置压缩后图片的最大宽度
         * $maxHeight="1136";//设置压缩图片的最大高度
         *
         * 使用说明:
         * 1. 将PHP.INI文件里面的"extension=php_gd2.dll"一行前面的;号去掉,因为我们要用到GD库;
         * 2. 将extension_dir =改为你的php_gd2.dll所在目录;
         ******************************************************************************/

        // 上传文件类型列表
        $upTypes = array(
            'image/jpg',
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/bmp',
            'image/x-png',
        );
        $maxFileSize = 8000000; // 上传文件大小限制, 单位BYTE
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_FILES["$inputFileName"]['tmp_name'])) // 已选择图片才执行下面
        {
            if (!is_uploaded_file($_FILES["$inputFileName"]['tmp_name'])) // 判断指定的文件是否是通过 HTTP POST 上传的
            {
                echo "post出错，尝试修改服务器post文件大小限制，默认2M";
                exit;
            }
            $file = $_FILES["$inputFileName"];
            if ($maxFileSize < $file["size"]) // 检查文件大小
            {
                echo "文件太大!";
                exit;
            }
            if (!in_array($file["type"], $upTypes)) // 检查文件类型
            {
                echo "文件类型不符!" . $file["type"];
                exit;
            }
            if (!file_exists($destinationFolder)) {
                mkdir($destinationFolder);
            }
            $filename = $file["tmp_name"];
            $pinfo = pathinfo($file["name"]);
            $ftype = $pinfo['extension'];
            $current_time = time();
            $image_name = $current_time . "." . $ftype;
            $destination = $destinationFolder . $image_name;
            if (file_exists($destination)) {
                echo "同名文件已经存在了";
                exit;
            }

            if (!move_uploaded_file($filename, $destination)) {
                echo "移动文件出错";
                exit;
            }

            // 图片压缩并写回原位置替代原文件
            $route = $destination; // 原图片路径
            $name = $destinationFolder . $current_time; // 压缩图片存放路径加名称，不带后缀
            $filetype = $ftype; // 图片类型
            self::resizeImage($route, $maxWidth, $maxHeight, $name, $filetype); // 调用函数
            return $image_name;
        }
        return false;
    }

    /**
     * 图片压缩函数
     * @param string $route 原图片的存放路径
     * @param int $maxWidth 设置图片的最大宽度
     * @param int $maxHeight 设置图片的最大高度
     * @param string $name 压缩图片存放路径加名称，不带后缀
     * @param string $fileType 图片类型
     */
    public function resizeImage($route, $maxWidth, $maxHeight, $name, $fileType)
    {
        $im = '';
        if (!strcasecmp($fileType, "jpg") || !strcasecmp($fileType, "jpeg")) {
            $im = imagecreatefromjpeg("$route"); // 参数是原图片的存放路径
        } else if (!strcasecmp($fileType, "png")) {
            $im = imagecreatefrompng("$route"); // 参数是原图片的存放路径
        } else if (!strcasecmp($fileType, "gif")) {
            $im = imagecreatefromgif("$route"); // 参数是原图片的存放路径
        }

        $picWidth = imagesx($im);
        $picHeight = imagesy($im);
        if (($maxWidth && $picWidth > $maxWidth) || ($maxHeight && $picHeight > $maxHeight)) {
            if ($maxWidth && $picWidth > $maxWidth) {
                $widthRatio = $maxWidth / $picWidth;
                $resizeWidthTag = true;
            }
            if ($maxHeight && $picHeight > $maxHeight) {
                $heightRatio = $maxHeight / $picHeight;
                $resizeHeightTag = true;
            }
            if ($resizeWidthTag && $resizeHeightTag) {
                if ($widthRatio < $heightRatio) {
                    $ratio = $widthRatio;
                } else {
                    $ratio = $heightRatio;
                }

            }
            if ($resizeWidthTag && !$resizeHeightTag) {
                $ratio = $widthRatio;
            }

            if ($resizeHeightTag && !$resizeWidthTag) {
                $ratio = $heightRatio;
            }

            $newWidth = $picWidth * $ratio;
            $newHeight = $picHeight * $ratio;

            if (function_exists("imagecopyresampled")) {
                $newIm = imagecreatetruecolor($newWidth, $newHeight); // PHP系统函数
                imagecopyresampled($newIm, $im, 0, 0, 0, 0, $newWidth, $newHeight, $picWidth, $picHeight); // PHP系统函数
            } else {
                $newIm = imagecreate($newWidth, $newHeight);
                imagecopyresized($newIm, $im, 0, 0, 0, 0, $newWidth, $newHeight, $picWidth, $picHeight);
            }
            $name = $name . "." . $fileType;
            if (!strcasecmp($fileType, "jpg") || !strcasecmp($fileType, "jpeg")) {
                imagejpeg($newIm, $name);
            } else if (!strcasecmp($fileType, "png")) {
                imagepng($newIm, $name);
            } else if (!strcasecmp($fileType, "gif")) {
                imagegif($newIm, $name);
            }
            imagedestroy($newIm);
        } else {
            // 原图小于设定的最大长度和宽度，则不进行压缩，原图输出
            $name = $name . "." . $fileType;
            if (!strcasecmp($fileType, "jpg") && !strcasecmp($fileType, "jpeg")) {
                imagejpeg($im, $name);
            } else if (!strcasecmp($fileType, "png")) {
                imagepng($im, $name);
            } else if (!strcasecmp($fileType, "gif")) {
                imagegif($im, $name);
            }
        }
    }

}
