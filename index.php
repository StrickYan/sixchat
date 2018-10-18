<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用入口文件

// 检测PHP环境
// if (version_compare(PHP_VERSION, '5.3.0', '<')) die('require PHP > 5.3.0 !');
if (version_compare(PHP_VERSION, '7.0.0', '<')) die('require PHP > 7.0.0 !');

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG', True);

// 定义应用目录
define('APP_PATH', './Application/');

// 定义 log 目录
define('LOG_PATH', './logs/');
define('LOG_NAME', 'sixchat'); // 日志名
define('LOG_FILE_NAME', 'sixchat.log'); // 日志路径
define('MAX_LOG_FILES', 1); // 日志数量

// 引入 composer 模块入口文件
require './vendor/autoload.php';

// 引入ThinkPHP入口文件
require './ThinkPHP/ThinkPHP.php';

// 亲^_^ 后面不需要任何代码了 就是如此简单