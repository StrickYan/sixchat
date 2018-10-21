<?php
return array(
    //'配置项'=>'配置值'

    //数据库连接
    'DB_TYPE' => 'mysql', // 数据库类型
    'DB_HOST' => 'localhost', // 服务器地址
    'DB_NAME' => 'think_sixchat', // 数据库名
    'DB_USER' => 'root', // 用户名
    'DB_PWD' => 'root', // 密码
    'DB_PORT' => 3306, // 端口
    'DB_PREFIX' => 'think_', // 数据库表前缀
    'DB_CHARSET' => 'utf8mb4', // 字符集

    'URL_MODEL' => '2', //url省略index.php
    'URL_HTML_SUFFIX' => '',  // URL伪静态后缀设置

    // 自定义全局模板变量
    'TMPL_PARSE_STRING' => array(
        '__DEPLOY_VERSION__' => '2018102105',
    ),
);
