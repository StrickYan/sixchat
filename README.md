# SIXCHAT
* 原文：https://github.com/sk1275330626/sixchat
* 作者：Strick Yan
* 转载请标明出处，谢谢



# 效果图（最新版可能不同）

* PC端：  
  ![](https://raw.githubusercontent.com/sk1275330626/sixchat/master/md_img/pc.png)  
* Mobile端：  

<div align="center">
<img src="https://raw.githubusercontent.com/sk1275330626/sixchat/master/md_img/mobile.png"/>
 </div>



# 目录说明

* Application/ 存放后台PHP代码
* avatar_img/ 存放头像文件
* md_img/ 存放README.md中引用的图片文件
* moment_img/ 存放内容图片
* Public/ 存放相关JS，CSS文件和默认的图片资源
* ThinkPHP/ ThinkPHP框架核心文件，无须改动
* sixchat.sql 应用的数据库与数据表结构，导入即可



# 配置nginx.conf

```
#ThnkPHP URL_MODEL=>2 rewrite
location /sixchat/ {
    if (!-e $request_filename) {
        rewrite ^/sixchat/(.*)$ /sixchat/index.php?s=/Home/$1 last;
        break;
    }
}
```



# 代码规范

* id关键词用下划线（_）连接，class关键词用中划线（-）连接



# 开发日志

尼泊尔真是让人又爱又恨的地方，宗教酝酿的另类风情像一张有魔力的网，无论走到哪里都有探索欲望，但它又会随时停电，炎热难忍。路边时常有陌生女子搭讪，买所有东西哪怕打车都要斗智斗勇讨价还价……



# TODO

1. 个人主页
2. 好友列表

## 2017/05/05

1. 页面改版
2. 代码重构
3. 性能优化

## 2016/10/07

1. 转移部分SQL为存储过程

## 2016/08/19

1. 稍微适配下PC端

## 2016/08/14
1. fix修改资料有空格不显示bug

## 2016/08/13
1. 增加图片压缩功能
2. 整合图片上传与压缩为公共API接口函数
3. 利用lightbox插件实现图片查看
4. 利用lazyload插件延迟加载图片
5. 上拉加载更多

## 2016/08/12
1. 增加处理好友请求功能
2. 增加个人资料查看与修改

## 2016/08/11
1. 完善返回功能
2. 增加添加好友请求功能
3. 增加信息流显示好友请求功能

## 2016/08/10
1. 完善查看一条moment功能
2. fix bug:id冲突

## 2016/08/09
1. 增加查看一条moment详情功能

## 2016/08/08
1. 弃用侧边栏插件，改用jquery animal()动画函数实现

## 2016/08/07
1. 修改数据表结构，把赞与评论合并为评论表
2. 增加侧边栏查看消息功能

## 2016/08/06
1. 修改查看赞权限，允许查看所有人的赞记录
2. 优化$(document).on("click","selector",function(){})事件
3. 增加moment删除功能
4. 增加comment删除功能

## 2016/08/05
1. 配置nginx.conf，支持thinkphp的url解析规则 
2. 增加弹出点赞评论动画
3. 实现like功能

## 2016/08/04
1. 实现发moment功能（同时或单独发文字与图片）

2. swiper插件（http://www.idangero.us/swiper/) 实现滚动墙功能
    随机从数据库选取3个朋友圈图片展示,点击可跳转详细该条

3. 评论权限：
    浏览自己的帖子可以看到所有评论包括好友与非好友

    浏览他人的帖子时只能看到互为好友的comment或者自己与该用户的对话

## 2016/08/03 ago
1. 基于thinkphp3.2.3的webapp设计
2. 编写login和register界面，实现login与register功能
3. 编写moments首页界面
4. 实现查看所有moments信息流（文字/图片）功能
5. 实现comment功能
6. 其他？好像还有：無
