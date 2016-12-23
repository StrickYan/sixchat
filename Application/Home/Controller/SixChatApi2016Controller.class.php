<?php //功能封装集成Api,方便其他控制器调用
namespace Home\Controller;

use Think\Controller;

class SixChatApi2016Controller extends Controller
{

    protected $momentModel;
    protected $userModel;
    protected $commentModel;
    protected $friendRuquestModel;
    protected $friendModel;

    public function __construct()
    {
        parent::__construct();
        $this->momentModel        = D('Moment');
        $this->userModel          = D('User');
        $this->commentModel       = D('comment');
        $this->friendRuquestModel = D("Friend_request");
        $this->friendModel        = D("Friend");
    }

    /*登录API*/
    public function login($id, $password)
    {
        $condition['user_name'] = $id;
        // $user_name              = M('User')->where($condition)->getfield('user_name');
        $user_name = $this->userModel->getUserName($condition);
        if (!$user_name) {
            //该用户不存在
            return -1;
        } else {
            setcookie("user", "$id", time() + 60 * 60 * 24 * 7); //用户名存在，保存用户名cookie
            $condition1['user_name'] = $id;
            $condition1['password']  = md5($password);
            // $user_name_1             = M('User')->where($condition1)->getfield('user_name');
            $user_name_1 = $this->userModel->getUserName($condition1);
            if ($user_name_1) {
                //登录成功
                setcookie("password", "$password", time() + 60 * 60 * 24, "/sixchat/Home/Login/", "119.29.24.253"); //登录成功再保存密码cookie
                // setcookie("password", "$password", time()+60*60*24,"/sixchat/Home/Login/", "localhost");//本地环境
                session_start();
                $_SESSION["name"] = $user_name_1;
                return 0;
            } else //密码错误
            {
                return -2;
            }

        }
    }

    //注销
    public function logout()
    {
        session_destroy();
        setcookie("password", "", time() - 3600, "/sixchat/Home/Login/", "119.29.24.253");
        // setcookie("password","", time()-3600,"/sixchat/Home/Login/","localhost");//本地环境
    }

    /*注册API*/
    public function register($id, $password)
    {
        $condition['user_name'] = $id;
        // $user_name              = M('User')->where($condition)->getfield('user_name');
        $user_name = $this->userModel->getUserName($condition);
        if ($user_name) {
            //该账号已存在
            return -1;
        } else {
            //注册成功添加新用户
            $data['user_name']     = $id;
            $data['password']      = MD5($password);
            $data['register_time'] = date("Y-m-d H:i:s");
            // M('User')->data($data)->filter('htmlspecialchars')->add();
            $this->userModel->addUser($data);

            //查询该新用户的user_id
            $condition1['user_name'] = $id;
            // $user_id                 = M('User')->where($condition1)->getfield('user_id');
            $user_id = $this->userModel->getUserId($condition1);

            //注册时自动添加自己为好友
            $data1['user_id']   = $user_id;
            $data1['friend_id'] = $user_id;
            $data1['time']      = date("Y-m-d H:i:s");
            // M('Friend')->data($data1)->filter('htmlspecialchars')->add();
            $this->friendModel->addFriend($data1);
            return 0;
        }
    }

    /*时间转换函数*/
    public function tranTime($time)
    {
        $rtime = date("M j, Y H:i", $time);
        $time  = time() - $time;
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
            $str = $rtime;
        }
        return $str;
    }

    //以user_name匹配用户user_id
    public function getUserId($reply_name, $replyed_name)
    {
        $condition['u1.user_name'] = $reply_name;
        $condition['u2.user_name'] = $replyed_name;
        // $result                    = M()->table('think_user u1,think_user u2')
        //     ->field('u1.user_id as reply_id,u2.user_id as replyed_id')
        //     ->where($condition)
        //     ->select();
        $result = $this->userModel->getUserIdViaUserName($condition);
        return $result;
    }

    //以user_id匹配用户user_name
    public function getUserName($reply_id, $replyed_id)
    {
        $condition['u1.user_id'] = $reply_id;
        $condition['u2.user_id'] = $replyed_id;
        // $result                  = M()->table('think_user u1,think_user u2')
        //     ->field('u1.user_nname as reply_name,u2.user_name as replyed_name')
        //     ->where($condition)
        //     ->select();
        $result = $this->userModel->getUserNameViaUserId($condition);
        return $result;
    }

    /*图片上传函数*/
    public function uploadImg($destination_folder, $input_file_name, $maxwidth, $maxheight)
    {

        /******************************************************************************
        参数说明:
        $max_file_size  : 上传文件大小限制, 单位BYTE
        $destination_folder : 上传文件路径
        $input_file_name ：文件上传input的name
        $maxwidth="640";//设置压缩后图片的最大宽度
        $maxheight="1136";//设置压缩图片的最大高度

        使用说明:
        1. 将PHP.INI文件里面的"extension=php_gd2.dll"一行前面的;号去掉,因为我们要用到GD库;
        2. 将extension_dir =改为你的php_gd2.dll所在目录;
         ******************************************************************************/

        //上传文件类型列表
        $uptypes = array(
            'image/jpg',
            'image/jpeg',
            'image/png',
            'image/pjpeg',
            'image/gif',
            'image/bmp',
            'image/x-png',
        );
        $max_file_size = 8000000; //上传文件大小限制, 单位BYTE
        $image_name    = '';
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_FILES["$input_file_name"]['tmp_name'])) //已选择图片才执行下面
        {
            if (!is_uploaded_file($_FILES["$input_file_name"]['tmp_name']))
            //判断指定的文件是否是通过 HTTP POST 上传的
            {
                echo "post出错，尝试修改服务器post文件大小限制，默认2M";
                exit;
            }
            $file = $_FILES["$input_file_name"];
            if ($max_file_size < $file["size"])
            //检查文件大小
            {
                echo "文件太大!";
                exit;
            }
            if (!in_array($file["type"], $uptypes))
            //检查文件类型
            {
                echo "文件类型不符!" . $file["type"];
                exit;
            }
            if (!file_exists($destination_folder)) {
                mkdir($destination_folder);
            }
            $filename     = $file["tmp_name"];
            $image_size   = getimagesize($filename);
            $pinfo        = pathinfo($file["name"]);
            $ftype        = $pinfo['extension'];
            $current_time = time();
            $image_name   = $current_time . "." . $ftype;
            $destination  = $destination_folder . $image_name;
            if (file_exists($destination) && $overwrite != true) {
                echo "同名文件已经存在了";
                exit;
            }

            if (!move_uploaded_file($filename, $destination)) {
                echo "移动文件出错";
                exit;
            }

            //图片压缩并写回原位置替代原文件
            $route    = $destination; //原图片路径
            $name     = $destination_folder . $current_time; //压缩图片存放路径加名称，不带后缀
            $filetype = $ftype; //图片类型
            self::resizeImage($route, $maxwidth, $maxheight, $name, $filetype); //调用函数
            return $image_name;
        }
        return false;
    }

    /*图片压缩函数
    $route;//原图片的存放路径
    $maxwidth="640";//设置图片的最大宽度
    $maxheight="1136";//设置图片的最大高度
    $name=$destination_folder.$current_time;//压缩图片存放路径加名称，不带后缀
    $filetype="jpg";//图片类型
     */
    public function resizeImage($route, $maxwidth, $maxheight, $name, $filetype)
    {
        $im = '';
        if (!strcasecmp($filetype, "jpg") || !strcasecmp($filetype, "jpeg")) {
            $im = imagecreatefromjpeg("$route"); //参数是原图片的存放路径
        } else if (!strcasecmp($filetype, "png")) {
            $im = imagecreatefrompng("$route"); //参数是原图片的存放路径
        } else if (!strcasecmp($filetype, "gif")) {
            $im = imagecreatefromgif("$route"); //参数是原图片的存放路径
        }

        $pic_width  = imagesx($im);
        $pic_height = imagesy($im);
        if (($maxwidth && $pic_width > $maxwidth) || ($maxheight && $pic_height > $maxheight)) {
            if ($maxwidth && $pic_width > $maxwidth) {
                $widthratio      = $maxwidth / $pic_width;
                $resizewidth_tag = true;
            }
            if ($maxheight && $pic_height > $maxheight) {
                $heightratio      = $maxheight / $pic_height;
                $resizeheight_tag = true;
            }
            if ($resizewidth_tag && $resizeheight_tag) {
                if ($widthratio < $heightratio) {
                    $ratio = $widthratio;
                } else {
                    $ratio = $heightratio;
                }

            }
            if ($resizewidth_tag && !$resizeheight_tag) {
                $ratio = $widthratio;
            }

            if ($resizeheight_tag && !$resizewidth_tag) {
                $ratio = $heightratio;
            }

            $newwidth  = $pic_width * $ratio;
            $newheight = $pic_height * $ratio;

            if (function_exists("imagecopyresampled")) {
                $newim = imagecreatetruecolor($newwidth, $newheight); //PHP系统函数
                imagecopyresampled($newim, $im, 0, 0, 0, 0, $newwidth, $newheight, $pic_width, $pic_height); //PHP系统函数
            } else {
                $newim = imagecreate($newwidth, $newheight);
                imagecopyresized($newim, $im, 0, 0, 0, 0, $newwidth, $newheight, $pic_width, $pic_height);
            }
            $name = $name . "." . $filetype;
            if (!strcasecmp($filetype, "jpg") || !strcasecmp($filetype, "jpeg")) {
                imagejpeg($newim, $name);
            } else if (!strcasecmp($filetype, "png")) {
                imagepng($newim, $name);
            }
            // else if( !strcasecmp($filetype,"gif")  ){    //不处理GIF文件因为压缩后就不会动了，开注释可处理
            //     imagegif($newim,$name);
            // }
            imagedestroy($newim);
        } else {
            //原图小于设定的最大长度和宽度，则不进行压缩，原图输出
            $name = $name . "." . $filetype;
            if (!strcasecmp($filetype, "jpg") && !strcasecmp($filetype, "jpeg")) {
                imagejpeg($im, $name);
            } else if (!strcasecmp($filetype, "png")) {
                imagepng($im, $name);
            }
            // else if( !strcasecmp($filetype,"gif")  ){    //不处理GIF文件因为压缩后就不会动了，开注释可处理
            //     imagegif($im,$name);
            // }
        }
    }

}
