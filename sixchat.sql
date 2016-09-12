-- --------------------------------------------------------
-- 主机:                           119.29.24.253
-- 服务器版本:                        5.1.73 - Source distribution
-- 服务器操作系统:                      redhat-linux-gnu
-- HeidiSQL 版本:                  9.3.0.4984
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- 导出 think_sixchat 的数据库结构
CREATE DATABASE IF NOT EXISTS `think_sixchat` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `think_sixchat`;


-- 导出  表 think_sixchat.think_comment 结构
CREATE TABLE IF NOT EXISTS `think_comment` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT,
  `moment_id` int(11) DEFAULT NULL,
  `comment` varchar(280) DEFAULT NULL,
  `reply_id` int(11) DEFAULT NULL,
  `replyed_id` int(11) DEFAULT NULL,
  `type` int(2) DEFAULT NULL,
  `time` datetime DEFAULT NULL,
  `state` int(2) DEFAULT '1',
  `news` int(2) DEFAULT '1',
  PRIMARY KEY (`comment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='type=1:点赞记录\r\ntype=2:评论记录';

-- 数据导出被取消选择。


-- 导出  表 think_sixchat.think_friend 结构
CREATE TABLE IF NOT EXISTS `think_friend` (
  `user_id` varchar(50) DEFAULT NULL,
  `friend_id` varchar(50) DEFAULT NULL,
  `no` int(11) NOT NULL AUTO_INCREMENT,
  `time` datetime DEFAULT NULL,
  PRIMARY KEY (`no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 数据导出被取消选择。


-- 导出  表 think_sixchat.think_friend_request 结构
CREATE TABLE IF NOT EXISTS `think_friend_request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request_id` int(11) DEFAULT NULL,
  `requested_id` int(11) DEFAULT NULL,
  `state` int(2) DEFAULT '1',
  `remark` varchar(140) DEFAULT NULL,
  `request_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 数据导出被取消选择。


-- 导出  表 think_sixchat.think_moment 结构
CREATE TABLE IF NOT EXISTS `think_moment` (
  `moment_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(50) DEFAULT NULL,
  `info` varchar(300) DEFAULT NULL,
  `img_url` varchar(100) DEFAULT NULL,
  `time` datetime DEFAULT NULL,
  `state` int(11) DEFAULT '1',
  PRIMARY KEY (`moment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 数据导出被取消选择。


-- 导出  表 think_sixchat.think_user 结构
CREATE TABLE IF NOT EXISTS `think_user` (
  `user_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_name` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `avatar` varchar(100) NOT NULL DEFAULT 'default_head.jpg',
  `sex` varchar(50) DEFAULT 'you ask me?',
  `region` varchar(50) DEFAULT 'Shenzhen,Guangdong',
  `whatsup` varchar(50) DEFAULT 'hhh',
  `register_time` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_name` (`user_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 数据导出被取消选择。
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
