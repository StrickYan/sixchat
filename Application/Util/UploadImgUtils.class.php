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

namespace Util;

class UploadImgUtils
{
    /**
     * 图片上传函数
     * @param $destinationFolder
     * @param $inputFileName
     * @param $maxWidth
     * @param $maxHeight
     * @return array
     */
    public static function uploadImg($destinationFolder, $inputFileName, $maxWidth, $maxHeight)
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

        // 已选择图片才执行下面
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_FILES["$inputFileName"]['tmp_name'])) {
            // 判断指定的文件是否是通过 HTTP POST 上传
            if (!is_uploaded_file($_FILES["$inputFileName"]['tmp_name'])) {
                return ResponseUtils::arrayRet(ErrCodeUtils::FAILED, array(), 'post出错，尝试修改服务器post文件大小限制，默认2M');
            }

            $file = $_FILES["$inputFileName"];

            // 检查文件大小
            if ($maxFileSize < $file["size"]) {
                return ResponseUtils::arrayRet(ErrCodeUtils::FAILED, array(), '文件太大');
            }

            // 检查文件类型
            if (!in_array($file["type"], $upTypes)) {
                return ResponseUtils::arrayRet(ErrCodeUtils::FAILED, array(), "文件类型不符!" . $file["type"]);
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
                return ResponseUtils::arrayRet(ErrCodeUtils::FAILED, array(), '存在同名文件');
            }

            if (!move_uploaded_file($filename, $destination)) {
                return ResponseUtils::arrayRet(ErrCodeUtils::FAILED, array(), '移动文件出错');
            }

            // 图片压缩并写回原位置替代原文件
            $route = $destination; // 原图片路径
            $name = $destinationFolder . $current_time; // 压缩图片存放路径加名称，不带后缀
            $filetype = $ftype; // 图片类型
            self::resizeImage($route, $maxWidth, $maxHeight, $name, $filetype); // 调用函数

            $ret = array(
                'image_name' => $image_name,
            );
            return ResponseUtils::arrayRet(ErrCodeUtils::SUCCESS, $ret);
        }

        return ResponseUtils::arrayRet(ErrCodeUtils::FAILED);
    }

    /**
     * 图片压缩函数
     * @param string $route 原图片的存放路径
     * @param int $maxWidth 设置图片的最大宽度
     * @param int $maxHeight 设置图片的最大高度
     * @param string $name 压缩图片存放路径加名称，不带后缀
     * @param string $fileType 图片类型
     */
    public static function resizeImage($route, $maxWidth, $maxHeight, $name, $fileType)
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
