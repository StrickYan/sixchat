-- --------------------------------------------------------
-- 主机:                           119.29.24.253
-- 服务器版本:                        5.5.48-log - Source distribution
-- 服务器操作系统:                      Linux
-- HeidiSQL 版本:                  9.4.0.5125
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- 导出 think_sixchat 的数据库结构
DROP DATABASE IF EXISTS `think_sixchat`;
CREATE DATABASE IF NOT EXISTS `think_sixchat` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `think_sixchat`;

-- 导出  过程 think_sixchat.proc_CommentByFields1Select 结构
DROP PROCEDURE IF EXISTS `proc_CommentByFields1Select`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `proc_CommentByFields1Select`(
	IN `moment_id` INT

)
    COMMENT '获取所有评论'
BEGIN
	set @sqlcmd = "
		select u1.user_name as reply_name,u2.user_name as replyed_name,c.comment_id,c.comment,c.time 
	   	from think_comment c,think_user u1,think_user u2 
	        	where c.reply_id=u1.user_id and c.replyed_id=u2.user_id and c.state=1 and c.type=2 and c.moment_id=? 
					order by c.time asc";  
	prepare stmt from @sqlcmd;  
	set @a = moment_id;
	execute stmt using @a; 
	deallocate prepare stmt;
END//
DELIMITER ;

-- 导出  过程 think_sixchat.proc_CommentByFields2Select 结构
DROP PROCEDURE IF EXISTS `proc_CommentByFields2Select`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `proc_CommentByFields2Select`(
	IN `moment_id` INT,
	IN `user_id` INT,
	IN `moment_user_id` INT
)
    COMMENT '获取好友关系的评论或者 自己与该用户的对话'
BEGIN
	set @sqlcmd = "
			select u1.user_name as reply_name,u2.user_name as replyed_name,c.comment_id,c.comment,c.time 
				from think_comment c,think_user u1,think_user u2
					where c.moment_id=? 
					and c.state=1 and c.type=2 
					and c.reply_id=u1.user_id and c.replyed_id=u2.user_id 
					and 
					((c.reply_id in (select friend_id from think_friend where user_id=?) and c.replyed_id in (select friend_id from think_friend where user_id=?)) 
						or (c.reply_id in (?,?) and c.replyed_id in (?,?))) 
						order by c.time asc";  
	prepare stmt from @sqlcmd;  
	set @a = moment_id;
	set @b = user_id;
	set @c = user_id;
	set @d = user_id;
	set @e = moment_user_id;
	set @f = moment_user_id;
	set @g = user_id;
	execute stmt using @a,@b,@c,@d,@e,@f,@g; 
	deallocate prepare stmt;
END//
DELIMITER ;

-- 导出  过程 think_sixchat.proc_CommentByUserNameSelect 结构
DROP PROCEDURE IF EXISTS `proc_CommentByUserNameSelect`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `proc_CommentByUserNameSelect`(
	IN `moment_id` INT

)
    COMMENT '获取该条moment的全部点赞人'
BEGIN
	set @sqlcmd = "
		SELECT u.user_name as reply_name 
			FROM think_comment c,think_user u 
				where c.reply_id = u.user_id and c.moment_id=? and c.state=1 and c.type=1 
					order by c.comment_id asc";  
	prepare stmt from @sqlcmd;  
	set @a = moment_id;
	execute stmt using @a; 
	deallocate prepare stmt;
END//
DELIMITER ;

-- 导出  过程 think_sixchat.proc_MomentByFieldsSelect 结构
DROP PROCEDURE IF EXISTS `proc_MomentByFieldsSelect`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `proc_MomentByFieldsSelect`()
    COMMENT 'get moments'
BEGIN
	set @sqlcmd = "
		select u.user_name,u.avatar,m.info,m.img_url,m.time,m.moment_id
			from think_moment m,think_user u
				where m.state = 1 and m.user_id = u.user_id
					order by m.time 
						desc limit 0,15";  
	prepare stmt from @sqlcmd;  
	execute stmt; 
	deallocate prepare stmt;
END//
DELIMITER ;

-- 导出  过程 think_sixchat.proc_MomentGetNextPage 结构
DROP PROCEDURE IF EXISTS `proc_MomentGetNextPage`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `proc_MomentGetNextPage`(IN `page` INT)
    COMMENT '加载更多朋友圈'
BEGIN
	set @sqlcmd = "
		select u.user_name,u.avatar,m.info,m.img_url,m.time,m.moment_id
			from think_moment m,think_user u
				where m.state=1 and m.user_id=u.user_id
					order by m.time 
						desc limit ?,15";  
	prepare stmt from @sqlcmd;
	set @a = page*15;
	execute stmt using @a;
	deallocate prepare stmt;
END//
DELIMITER ;

-- 导出  表 think_sixchat.think_comment 结构
DROP TABLE IF EXISTS `think_comment`;
CREATE TABLE IF NOT EXISTS `think_comment` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'comment id',
  `moment_id` int(11) NOT NULL DEFAULT '0' COMMENT 'moment id',
  `comment` varchar(280) NOT NULL DEFAULT '' COMMENT '评论内容',
  `reply_id` int(11) NOT NULL DEFAULT '0' COMMENT '评论人id',
  `replyed_id` int(11) NOT NULL DEFAULT '0' COMMENT '被评论人id',
  `type` int(2) NOT NULL DEFAULT '0' COMMENT '类型，1为点赞，2为评论',
  `state` int(2) NOT NULL DEFAULT '1' COMMENT '状态',
  `news` int(2) NOT NULL DEFAULT '1' COMMENT '未读状态，0为已读，1为未读',
  `time` datetime NOT NULL COMMENT '发布时间',
  PRIMARY KEY (`comment_id`)
) ENGINE=InnoDB AUTO_INCREMENT=296 DEFAULT CHARSET=utf8mb4 COMMENT='type=1:点赞记录\r\ntype=2:评论记录';

-- 数据导出被取消选择。
-- 导出  表 think_sixchat.think_friend 结构
DROP TABLE IF EXISTS `think_friend`;
CREATE TABLE IF NOT EXISTS `think_friend` (
  `no` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` varchar(50) NOT NULL DEFAULT '' COMMENT 'user id',
  `friend_id` varchar(50) NOT NULL DEFAULT '' COMMENT 'friend id',
  `time` datetime NOT NULL COMMENT '建立好友关系的时间',
  PRIMARY KEY (`no`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COMMENT='好友关系表';

-- 数据导出被取消选择。
-- 导出  表 think_sixchat.think_friend_request 结构
DROP TABLE IF EXISTS `think_friend_request`;
CREATE TABLE IF NOT EXISTS `think_friend_request` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `request_id` int(11) NOT NULL DEFAULT '0' COMMENT '好友请求的 user id',
  `requested_id` int(11) NOT NULL DEFAULT '0' COMMENT '被好友请求的 user id',
  `state` int(2) NOT NULL DEFAULT '1' COMMENT '该条好友请求状态',
  `remark` varchar(140) NOT NULL DEFAULT '' COMMENT '备注',
  `request_time` datetime NOT NULL COMMENT '请求时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='好友请求';

-- 数据导出被取消选择。
-- 导出  表 think_sixchat.think_moment 结构
DROP TABLE IF EXISTS `think_moment`;
CREATE TABLE IF NOT EXISTS `think_moment` (
  `moment_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'moment id',
  `user_id` varchar(50) NOT NULL DEFAULT '0' COMMENT 'user id',
  `info` varchar(300) NOT NULL DEFAULT '' COMMENT 'moment文本',
  `img_url` varchar(100) NOT NULL DEFAULT '' COMMENT 'moment图片',
  `state` int(11) NOT NULL DEFAULT '1' COMMENT '状态，0：删除，1：正常',
  `time` datetime NOT NULL COMMENT '发布时间',
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`moment_id`)
) ENGINE=InnoDB AUTO_INCREMENT=229 DEFAULT CHARSET=utf8mb4 COMMENT='moment表';

-- 数据导出被取消选择。
-- 导出  表 think_sixchat.think_user 结构
DROP TABLE IF EXISTS `think_user`;
CREATE TABLE IF NOT EXISTS `think_user` (
  `user_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'user id, 用户唯一标识',
  `user_name` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名',
  `password` varchar(100) NOT NULL DEFAULT '' COMMENT '密码',
  `avatar` varchar(100) NOT NULL DEFAULT 'default_head.jpg' COMMENT '头像',
  `sex` varchar(50) NOT NULL DEFAULT 'you ask me?' COMMENT '性别',
  `region` varchar(50) NOT NULL DEFAULT 'Shenzhen,Guangdong' COMMENT '地区',
  `whatsup` varchar(50) NOT NULL DEFAULT 'hhh' COMMENT '个性签名',
  `register_time` datetime NOT NULL COMMENT '注册时间',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_name` (`user_name`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COMMENT='用户表';

-- 数据导出被取消选择。
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
