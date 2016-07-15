# ************************************************************
# Sequel Pro SQL dump
# Version 4529
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: 127.0.0.1 (MySQL 5.6.19)
# Database: gift_appgame_com
# Generation Time: 2016-07-15 11:06:58 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table hoho_admin_auth
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hoho_admin_auth`;

CREATE TABLE `hoho_admin_auth` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) unsigned NOT NULL,
  `title` varchar(250) NOT NULL,
  `auth_module` varchar(250) NOT NULL,
  `auth_action` varchar(250) NOT NULL,
  `menu_name` varchar(20) NOT NULL,
  `menu_url` varchar(200) NOT NULL,
  `orderid` mediumint(6) unsigned NOT NULL,
  `status` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `hoho_admin_auth` WRITE;
/*!40000 ALTER TABLE `hoho_admin_auth` DISABLE KEYS */;

INSERT INTO `hoho_admin_auth` (`id`, `pid`, `title`, `auth_module`, `auth_action`, `menu_name`, `menu_url`, `orderid`, `status`)
VALUES
	(1,45,'开服信息','','','开服信息','',1,1),
	(2,46,'用户管理','','','用户管理','',1,1),
	(3,1,'服务器安装确认','Server','step2,','','',3,1),
	(4,1,'活动策划确认','Server','step3,','','',4,1),
	(5,1,'测试状态确认','Server','step5,','','',6,1),
	(6,1,'接口状态确认','Server','step4,','','',5,1),
	(7,1,'清档状态确认','Server','step6,','','',7,1),
	(8,1,'查看操作日志','Server','log,','','',8,1),
	(9,2,'查看后台角色','AdminRole','index,','角色管理','admin.php?m=AdminRole',1,1),
	(10,2,'编辑后台角色','AdminRole','add,edit,insert,update,delete,status,','','',1,1),
	(11,2,'后台角色授权','AdminRole','auth,','','',2,1),
	(12,2,'查看后台用户','AdminUser','index,','用户管理','admin.php?m=AdminUser',3,1),
	(13,2,'编辑后台用户','AdminUser','add,edit,insert,update,delete,status,','','',4,1),
	(14,47,'查看权限列表','AdminAuth','index,','权限列表','admin.php?m=AdminAuth',6,0),
	(15,47,'编辑权限列表','AdminAuth','add,edit,insert,update,delete,status,','','',7,0),
	(16,46,'其 他','','','','',9,1),
	(17,16,'修改密码','AdminUser','changePwd,','','',0,1),
	(19,2,'后台用户授权','AdminUser','auth,','','',5,1),
	(20,1,'开服列表','Server','index,','开服列表','admin.php?m=Server',1,1),
	(21,1,'开服确认','Server','add,edit,step1,','','',2,1),
	(22,45,'联运平台','','','联运平台','',2,1),
	(23,22,'联运平台列表','Agent','index,','联运平台列表','admin.php?m=Agent',1,1),
	(24,22,'新增联运平台','Agent','add,insert,','','',2,1),
	(25,22,'编辑联运平台','Agent','edit,update,status,','','',3,1),
	(26,22,'删除联运平台','Agent','delete,','','',4,1),
	(27,45,'游戏信息','','','游戏信息','',3,1),
	(28,27,'游戏列表','Game','index,','游戏列表','admin.php?m=Game',1,1),
	(29,27,'新增游戏','Game','add,insert,','','',2,1),
	(30,27,'编辑游戏','Game','edit,update,status,','','',3,1),
	(31,27,'删除游戏','Game','delete,','','',4,1),
	(32,1,'删除开服','Server','delete,','','',9,1),
	(33,1,'运维组操作链接','Server','index,','','',10,1),
	(34,1,'运营组操作链接','Server','index,','','',11,1),
	(35,1,'策划组操作链接','Server','index,','','',12,1),
	(36,1,'测试组操作链接','Server','index,','','',13,1),
	(37,1,'查看备注','Server','remark,','','',14,1),
	(38,1,'更新备注','Server','remark_update,','','',15,1),
	(39,1,'信息导出','Server','export,','信息导出','admin.php?m=Server&a=export',16,1),
	(40,22,'参数配置','Config','agent,','参数配置','admin.php?m=Config&a=agent',5,1),
	(41,1,'rsync参数设置','Server','rsync,','','',17,1),
	(42,1,'更新参数','Server','Config,','','',18,1),
	(43,1,'按开服状态统计','Total','agent,','按开服状态统计','admin.php?m=Total&a=agent',66,1),
	(44,1,'按监控数据统计','Total','monitor,','按监控数据统计','admin.php?m=Total&a=monitor',77,1),
	(45,0,'系统测试2','','','系统测试2','',3,1),
	(46,0,'系统设置','','','系统设置','',99,1),
	(47,46,'权限设置','','','权限设置','',2,0),
	(48,0,'系统测试1','','','系统测试1','',2,1),
	(49,48,'联运资料','','','联运资料','',0,1),
	(50,49,'查看联运须知','Notice','index,view,','联运须知','admin.php?m=Notice',0,1),
	(51,48,'客户资料','','','客户资料','',0,1),
	(52,51,'查看客户分组','CustomerGroup','index,','客户分组','admin.php?m=CustomerGroup',1,1),
	(53,51,'查看客户列表','Customer','index,','客户列表','admin.php?m=Customer',3,1),
	(54,51,'群发短信','Customer','sendMsg,proccessMsg,','群发短信','admin.php?m=Customer&a=sendMsg',9,1),
	(55,48,'问题处理跟踪','','','问题处理跟踪','',3,1),
	(56,55,'我处理的问题','Support','deal,','我处理的问题','admin.php?m=Support&a=deal',0,1),
	(57,55,'我提交的问题','Support','post,','我提交的问题','admin.php?m=Support&a=post',0,1),
	(58,55,'全部问题列表','Support','index,','全部问题列表','admin.php?m=Support',3,1),
	(59,55,'问题分类设置','SupportCategory','*','问题分类设置','admin.php?m=SupportCategory',9,1),
	(60,55,'用户分组设置','SupportUserGroup','*','用户分组设置','admin.php?m=SupportUserGroup',11,1),
	(61,49,'编辑联运须知','Notice','add,edit,insert,update,delete,status,','','',1,1),
	(62,51,'编辑客户分组','CustomerGroup','add,edit,insert,update,delete,status,','','',2,1),
	(63,51,'编辑客户列表','Customer','add,edit,insert,update,delete,status,','','',4,1),
	(64,55,'处理转交问题','Support','add,edit,insert,update,delete,deliver,view,','','',4,1),
	(65,0,'礼包中心','','','礼包中心','',1,1),
	(66,65,'礼包管理','','','礼包管理','',0,1),
	(67,66,'礼包列表','Gift','*','礼包列表','admin.php?m=Gift',0,1),
	(68,66,'推荐位列表','Position','*','推荐位列表','admin.php?m=Position',1,1);

/*!40000 ALTER TABLE `hoho_admin_auth` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table hoho_admin_role
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hoho_admin_role`;

CREATE TABLE `hoho_admin_role` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(40) NOT NULL,
  `description` varchar(250) NOT NULL,
  `auth` text NOT NULL,
  `game` text NOT NULL,
  `status` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `hoho_admin_role` WRITE;
/*!40000 ALTER TABLE `hoho_admin_role` DISABLE KEYS */;

INSERT INTO `hoho_admin_role` (`id`, `title`, `description`, `auth`, `game`, `status`)
VALUES
	(1,'系统管理员','系统管理员不需要授权就拥有全部管理权限','','',1),
	(2,'无权限用户组','本组不授权。用户属于本组的单独对该用户进行授权','','',1),
	(3,'任玩堂市场部','','67,17','',1),
	(4,'超级管理员','超级管理员拥有后台的绝大部分管理权限','50,61,52,62,53,63,54,57,56,58,64,59,60,20,21,3,4,6,5,7,8,32,33,34,35,36,37,38,39,41,42,43,44,23,24,25,26,40,28,29,30,31,10,9,11,12,13,19,17','1',1);

/*!40000 ALTER TABLE `hoho_admin_role` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table hoho_admin_user
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hoho_admin_user`;

CREATE TABLE `hoho_admin_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uname` varchar(40) NOT NULL,
  `pwd` varchar(40) NOT NULL,
  `description` varchar(250) NOT NULL,
  `role_id` int(11) unsigned NOT NULL,
  `auth` text NOT NULL,
  `game` text NOT NULL,
  `status` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uname` (`uname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `hoho_admin_user` WRITE;
/*!40000 ALTER TABLE `hoho_admin_user` DISABLE KEYS */;

INSERT INTO `hoho_admin_user` (`id`, `uname`, `pwd`, `description`, `role_id`, `auth`, `game`, `status`)
VALUES
	(18,'zwj','9c6ce8ccea5fe1bd6550a58966bfdfb0','',1,'','',1),
	(19,'test','e10adc3949ba59abbe56e057f20f883e','',3,'','',1);

/*!40000 ALTER TABLE `hoho_admin_user` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table hoho_agent
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hoho_agent`;

CREATE TABLE `hoho_agent` (
  `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(60) NOT NULL,
  `short_name` varchar(20) NOT NULL,
  `description` varchar(250) NOT NULL,
  `orderid` mediumint(4) unsigned NOT NULL,
  `status` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `short_name` (`short_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `hoho_agent` WRITE;
/*!40000 ALTER TABLE `hoho_agent` DISABLE KEYS */;

INSERT INTO `hoho_agent` (`id`, `title`, `short_name`, `description`, `orderid`, `status`)
VALUES
	(1,'91wan','91wan','',0,1),
	(2,'2918','2918','',0,1),
	(3,'915','915','',0,1),
	(4,'4399','4399','',0,1),
	(5,'9377','9377','',0,1),
	(6,'快播','kuaiwan','',0,1),
	(7,'51社区','51','',0,1),
	(8,'要玩','yaowan','',0,1),
	(9,'搜搜玩','sosowan','',0,1),
	(10,'05wan','05wan','',0,1),
	(11,'4311','4311','',0,1),
	(12,'360玩','360','',0,1),
	(13,'酷狗','kugou','',0,1),
	(14,'3737','3737','',0,1),
	(15,'群英','5037','',0,1),
	(16,'特游','特游','',0,1),
	(17,'ya247','ya247','',0,1),
	(18,'众衡','众衡','',0,1),
	(19,'5757g','5757g','',0,1),
	(20,'8090','8090','',0,1),
	(21,'3977','3977','',0,1),
	(22,'7K7K','7K7K','',0,1),
	(23,'开心网','kaixin001','',0,1),
	(24,'趣游','趣游','',0,1),
	(25,'51wan','51wan','',0,1),
	(26,'趣游','5ding','',0,1),
	(27,'西路','xilu','',0,1),
	(28,'星碟ux18','ux18','',0,1),
	(29,'2866','2866','',0,1),
	(30,'game66','game66','',0,1),
	(31,'552you','552you','',0,1),
	(32,'87yd','87yd','',0,1),
	(33,'万游','91rpg','',0,1),
	(34,'麒麟','70','',0,1),
	(35,'2567','2567','',0,1),
	(36,'PPS','PPS','',0,1),
	(37,'6616','6616','',0,1),
	(38,'酷玩','kuwan8','',0,1),
	(39,'game5','game5','',0,1),
	(40,'56','56','',0,1),
	(41,'3896','3896','',0,1),
	(42,'21cn','21cn','',0,1),
	(43,'baofeng','baofeng','',0,1),
	(44,'酷我','kuwo','',0,1),
	(45,'game2','game2','',0,1),
	(46,'pptv','pptv','',0,1),
	(47,'37wan','37wan','',0,1),
	(48,'niua','niua','',0,1),
	(49,'4299','4299','',0,1),
	(50,'wowan365','wowan365','',0,1),
	(51,'wan8','wan8','',0,1),
	(52,'awo','awo','',0,1),
	(53,'tdwan','tdwan','',0,1),
	(54,'bigzhu','bigzhu','',0,1),
	(55,'juu','juu','',0,1),
	(56,'runup','runup','',0,1),
	(57,'renren','renren','',0,1),
	(58,'ruixing','ruixing','',0,1),
	(59,'9c','9c','',0,1),
	(60,'lelidou','lelidou','',0,1),
	(61,'星碟96AK','96AK','',0,1),
	(62,'56UU','56UU','',0,1),
	(63,'616wan','616wan','',0,1),
	(64,'7wan','7wan','',0,1),
	(65,'666wan','666wan','',0,1),
	(66,'蝌蚪','蝌蚪','',0,1),
	(67,'九天','九天','',0,1),
	(68,'百度','baidu','',0,1),
	(69,'多玩','duowan','',0,1),
	(70,'uu178','uu178','',0,1),
	(71,'655u','655u','',0,1),
	(72,'e7uu','e7uu','',0,1),
	(73,'开心一点','kx1d','',0,1),
	(74,'万游网','万游网','',0,1),
	(75,'1212wan','1212wan','',0,1),
	(76,'xdwan','xdwan','',0,1),
	(77,'乐途','乐途','',0,1),
	(78,'766z','766z','',0,1),
	(79,'49you','49you','',0,1),
	(80,'打卡网','打卡网','',0,1),
	(81,'游戏群','游戏群','',0,1),
	(82,'7789','7789','',0,1),
	(83,'边锋','边锋','',0,1),
	(84,'竞游网','竞游网','',0,1),
	(85,'6543','6543','',0,1),
	(86,'爱扑','爱扑','',0,1),
	(87,'96pk','96pk','',0,1),
	(88,'指掌','指掌','',0,1),
	(89,'聚购','聚购','',0,1),
	(90,'风行','风行','',0,1),
	(91,'91y','91y','',0,1),
	(92,'联合互动','联合互动','',0,1),
	(93,'97wan','97wan','',0,1),
	(94,'大猪网','大猪网','',0,1),
	(95,'我玩网','我玩网','',0,1),
	(96,'8866pk','8866pk','',0,1),
	(97,'搜狗','搜狗','',0,1),
	(98,'聚游','聚游','',0,1),
	(99,'92y','92y','',0,1),
	(100,'975game','975game','',0,1),
	(101,'新浪','新浪','',0,1),
	(102,'爆米花','爆米花','',0,1),
	(103,'99yx','99yx','',0,1),
	(104,'酷奇','酷奇','',0,1),
	(105,'125wan','125wan','',0,1),
	(106,'欢乐园','欢乐园','',0,1),
	(107,'我友','我友','',0,1),
	(108,'256wan','256wan','',0,1),
	(109,'91yy','91yy','',0,1),
	(110,'百娱','百娱','',0,1),
	(111,'快亲','快亲','',0,1),
	(112,'909wan','909wan','',0,1),
	(113,'92you','92you','',0,1),
	(114,'007wan','007wan','',0,1),
	(115,'金桔网','金桔网','',0,1),
	(116,'7618','7618','',0,1),
	(119,'3651wan','3651wan','',0,1),
	(120,'wanku','wanku','',0,1),
	(121,'358wan','358wan','',0,1),
	(122,'紫霞','紫霞','',0,1),
	(123,'76ju','76ju','',0,1),
	(124,'accgame','accgame','',0,1),
	(125,'可可国','可可国','',0,1),
	(126,'17777','17777','',0,1),
	(127,'65wan','65wan','',0,1),
	(128,'yeyou365','yeyou365','',0,1),
	(129,'8787','8787','',0,1),
	(130,'盛大','sd','',0,1),
	(131,'789hi','789hi','',0,1),
	(132,'起点','qidian','',0,1),
	(133,'游窝','游窝','',0,1),
	(134,'GTV','GTV','',0,1),
	(135,'恺英','恺英','',0,1),
	(136,'37wanwan','37wanwan','',0,1),
	(137,'5173','5173','',0,1),
	(138,'游戏风云','游戏风云','132',0,1);

/*!40000 ALTER TABLE `hoho_agent` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table hoho_agent_config
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hoho_agent_config`;

CREATE TABLE `hoho_agent_config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `agent_id` int(6) unsigned NOT NULL,
  `game_id` int(6) unsigned NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table hoho_customer
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hoho_customer`;

CREATE TABLE `hoho_customer` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(8) unsigned NOT NULL,
  `true_name` varchar(20) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `qq` varchar(20) NOT NULL,
  `email` varchar(60) NOT NULL,
  `remark` varchar(120) NOT NULL,
  `status` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table hoho_customer_group
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hoho_customer_group`;

CREATE TABLE `hoho_customer_group` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(60) NOT NULL,
  `orderid` mediumint(6) unsigned NOT NULL,
  `status` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table hoho_events
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hoho_events`;

CREATE TABLE `hoho_events` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `location` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bg_type` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'color',
  `bg_color` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `bg_image_path` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `icon` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `game` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `game_id` bigint(20) unsigned NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `zone_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `down_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` tinyint(4) NOT NULL,
  `position` smallint(6) NOT NULL,
  `hot` tinyint(4) NOT NULL,
  `is_tao` tinyint(4) NOT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `on_sale_date` datetime DEFAULT NULL,
  `account_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `organiser_id` int(10) unsigned NOT NULL,
  `get_num` int(10) unsigned NOT NULL,
  `tao_num` int(10) unsigned NOT NULL,
  `total` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `orderid` mediumint(4) unsigned NOT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `hoho_events` WRITE;
/*!40000 ALTER TABLE `hoho_events` DISABLE KEYS */;

INSERT INTO `hoho_events` (`id`, `title`, `location`, `bg_type`, `bg_color`, `bg_image_path`, `icon`, `game`, `game_id`, `description`, `zone_url`, `down_url`, `type`, `position`, `hot`, `is_tao`, `start_date`, `end_date`, `on_sale_date`, `account_id`, `user_id`, `organiser_id`, `get_num`, `tao_num`, `total`, `created_at`, `updated_at`, `orderid`, `status`)
VALUES
	(6,'全民超神礼包123',NULL,'color','',NULL,'http://app.static.appgame.com/images/game/14771117515/icon/qmcs.png.png','全民超神123',14771117515,'<p>test2</p>','http://moba.appgame.com/','',1,1,1,0,'2016-07-13 14:39:14','2016-07-13 14:39:14',NULL,0,0,0,59,0,5000,NULL,NULL,0,1),
	(7,'糖果传奇礼包',NULL,'color','',NULL,'http://app.static.appgame.com/images/game/45763624/icon/icon175x175-217.png','糖果传奇',45763624,'<p>test</p>','http://ccs.appgame.com/','',1,1,1,0,'2016-07-13 14:02:11','2016-07-13 14:02:11',NULL,0,0,0,24,0,0,NULL,NULL,1,1),
	(8,'刀塔传奇标题',NULL,'color','',NULL,'http://app.static.appgame.com/images/game/53621671135/icon/dtcq175.png','刀塔传奇',53621671135,'<p>sdf </p>','http://dtcq.appgame.com/','',1,1,1,1,'2016-07-15 14:02:54','2016-07-15 14:02:54',NULL,0,0,0,0,0,0,NULL,NULL,0,1),
	(9,'地下城堡liba',NULL,'color','',NULL,'http://app.static.appgame.com/images/game/83413120023/icon/1751.png','地下城堡',83413120023,'','http://hszz.appgame.com/','',1,1,1,1,'2016-07-15 15:17:56','2016-07-15 15:17:56',NULL,0,0,0,0,0,0,NULL,NULL,0,1),
	(10,'地下城堡liba',NULL,'color','',NULL,'http://app.static.appgame.com/images/game/83413120023/icon/1751.png','地下城堡123',83413120023,'','http://hszz.appgame.com/','',1,1,1,1,'2016-07-15 15:17:56','2016-07-15 15:17:56',NULL,0,0,0,0,0,0,NULL,NULL,0,1),
	(11,'地下城堡移动版3',NULL,'color','',NULL,'http://app.static.appgame.com/images/game/45764063/icon/mzl.nlynfkyw.175x175-75.png','我的世界：移动版',45764063,'','http://www.appgame.com/page/minecraft-pe','',1,1,1,1,'2016-07-15 15:17:56','2016-07-15 15:17:56',NULL,0,0,0,0,0,0,NULL,NULL,0,1),
	(12,'地下城堡移动版2',NULL,'color','',NULL,'http://app.static.appgame.com/images/game/50085283451/icon/icon175x175.png.png','笨拙忍者',50085283451,'','http://dtcq.appgame.com/','',1,1,1,1,'2016-07-15 15:17:56','2016-07-15 15:17:56',NULL,0,0,0,0,0,0,NULL,NULL,0,1),
	(13,'地下城堡移动版1',NULL,'color','',NULL,'http://app.static.appgame.com/images/game/86722255821/icon/heroki-logo.png','浮天小子',86722255821,'','http://ccs.appgame.com/','',1,1,1,1,'2016-07-15 15:17:56','2016-07-15 15:17:56',NULL,0,0,0,0,0,0,NULL,NULL,0,1),
	(14,'绝地战警a',NULL,'color','',NULL,'http://app.static.appgame.com/images/game/86722255847/icon/icon175x17536.png','绝地战警',86722255847,'','http://ccs.appgame.com/','',1,1,1,1,'2016-07-15 15:20:52','2016-07-15 15:20:52',NULL,0,0,0,0,0,0,NULL,NULL,0,1),
	(15,'隐藏英雄2',NULL,'color','',NULL,'http://app.static.appgame.com/images/game/86722255874/icon/icon175x17545.png','隐藏英雄',86722255874,'','http://mir.appgame.com/','',1,1,1,1,'2016-07-15 15:20:52','2016-07-15 15:20:52',NULL,0,0,0,0,0,0,NULL,NULL,0,1),
	(16,'我的兄弟是超级英雄gh',NULL,'color','',NULL,'http://app.static.appgame.com/images/game/86722255850/icon/icon175x175318.png','我的兄弟是超级英雄',86722255850,'','http://dtcq.appgame.com/','',1,1,1,1,'2016-07-15 15:20:52','2016-07-15 15:20:52',NULL,0,0,0,0,0,0,NULL,NULL,0,1),
	(17,'植物大战僵尸2g',NULL,'color','',NULL,'http://app.static.appgame.com/images/game/50088915487/icon/icon175x175-154.png','植物大战僵尸2',50088915487,'','http://hszz.appgame.com/','',1,1,1,1,'2016-07-15 15:20:52','2016-07-15 15:20:52',NULL,0,0,0,0,0,0,NULL,NULL,0,1),
	(18,'梦想小镇2g',NULL,'color','',NULL,'http://app.static.appgame.com/images/game/70022078036/icon/mxxz.jpeg','梦想小镇',70022078036,'','http://moba.appgame.com/','',1,1,1,1,'2016-07-15 15:20:52','2016-07-15 15:20:52',NULL,0,0,0,0,0,0,NULL,NULL,0,1);

/*!40000 ALTER TABLE `hoho_events` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table hoho_game
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hoho_game`;

CREATE TABLE `hoho_game` (
  `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(20) NOT NULL,
  `description` varchar(250) NOT NULL,
  `link1` mediumtext NOT NULL,
  `link2` mediumtext NOT NULL,
  `link3` mediumtext NOT NULL,
  `link4` mediumtext NOT NULL,
  `rsync` varchar(250) NOT NULL,
  `show_second` tinyint(1) unsigned NOT NULL,
  `orderid` mediumint(4) unsigned NOT NULL,
  `status` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `title` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `hoho_game` WRITE;
/*!40000 ALTER TABLE `hoho_game` DISABLE KEYS */;

INSERT INTO `hoho_game` (`id`, `title`, `description`, `link1`, `link2`, `link3`, `link4`, `rsync`, `show_second`, `orderid`, `status`)
VALUES
	(1,'画仙','','<a href=http://gmt.play67.com/ target=\"_blank\">后台</a>','<a href=http://gmt.play67.com/ target=\"_blank\">后台</a>','','','',0,0,1);

/*!40000 ALTER TABLE `hoho_game` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table hoho_log
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hoho_log`;

CREATE TABLE `hoho_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `server_id` int(11) unsigned NOT NULL,
  `uname` varchar(60) NOT NULL,
  `content` text NOT NULL,
  `create_time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table hoho_notice
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hoho_notice`;

CREATE TABLE `hoho_notice` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `game_id` int(8) unsigned NOT NULL,
  `title` varchar(120) NOT NULL,
  `content` longtext NOT NULL,
  `orderid` mediumint(6) unsigned NOT NULL,
  `create_time` int(11) unsigned NOT NULL,
  `uname` varchar(60) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `hoho_notice` WRITE;
/*!40000 ALTER TABLE `hoho_notice` DISABLE KEYS */;

INSERT INTO `hoho_notice` (`id`, `game_id`, `title`, `content`, `orderid`, `create_time`, `uname`)
VALUES
	(1,1,'礼包中心须知','<p style=\"text-align:center;\"><img src=\"http://img.baidu.com/hi/jx2/j_0038.gif\" /><br /></p>',0,1355198355,'hyl');

/*!40000 ALTER TABLE `hoho_notice` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table hoho_remark
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hoho_remark`;

CREATE TABLE `hoho_remark` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `server_id` int(11) unsigned NOT NULL,
  `content` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table hoho_server
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hoho_server`;

CREATE TABLE `hoho_server` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `game_id` int(6) unsigned NOT NULL,
  `agent_id` int(6) unsigned NOT NULL,
  `server_num` int(8) unsigned NOT NULL,
  `server_merge` varchar(60) NOT NULL,
  `server_name` varchar(12) NOT NULL,
  `open_time` int(11) unsigned NOT NULL,
  `open_state` tinyint(1) unsigned NOT NULL,
  `domain` varchar(60) NOT NULL,
  `ct_ip` varchar(20) NOT NULL,
  `cnc_ip` varchar(20) NOT NULL,
  `domain_secondary` varchar(60) NOT NULL,
  `ct_ip_secondary` varchar(20) NOT NULL,
  `cnc_ip_secondary` varchar(20) NOT NULL,
  `server_state` tinyint(1) unsigned NOT NULL,
  `setup_state` tinyint(1) unsigned NOT NULL,
  `activity_state` tinyint(1) unsigned NOT NULL,
  `api_state` tinyint(1) unsigned NOT NULL,
  `test_state` tinyint(1) unsigned NOT NULL,
  `clear_state` tinyint(1) unsigned NOT NULL,
  `rsync` varchar(250) NOT NULL,
  `ssh` tinyint(1) unsigned NOT NULL,
  `config` varchar(250) NOT NULL,
  `update_time` int(11) unsigned NOT NULL,
  `close_time` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `game_id` (`game_id`,`agent_id`,`server_num`,`open_state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table hoho_support
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hoho_support`;

CREATE TABLE `hoho_support` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(11) unsigned NOT NULL,
  `title` varchar(120) NOT NULL,
  `content` longtext NOT NULL,
  `from_name` varchar(60) NOT NULL,
  `to_name` varchar(250) NOT NULL,
  `to_group` varchar(250) NOT NULL,
  `status` tinyint(1) unsigned NOT NULL,
  `create_time` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table hoho_support_category
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hoho_support_category`;

CREATE TABLE `hoho_support_category` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(60) NOT NULL,
  `orderid` mediumint(6) unsigned NOT NULL,
  `status` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table hoho_support_history
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hoho_support_history`;

CREATE TABLE `hoho_support_history` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `support_id` int(11) unsigned NOT NULL,
  `uname` varchar(60) NOT NULL,
  `content` varchar(250) NOT NULL,
  `create_time` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table hoho_support_user
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hoho_support_user`;

CREATE TABLE `hoho_support_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(11) unsigned NOT NULL,
  `uname` varchar(60) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_uname` (`group_id`,`uname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table hoho_support_user_group
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hoho_support_user_group`;

CREATE TABLE `hoho_support_user_group` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(60) NOT NULL,
  `orderid` mediumint(6) unsigned NOT NULL,
  `status` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table hoho_tickets
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hoho_tickets`;

CREATE TABLE `hoho_tickets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `card` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `visitor` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `state` tinyint(4) NOT NULL,
  `is_tao` tinyint(4) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `hoho_tickets` WRITE;
/*!40000 ALTER TABLE `hoho_tickets` DISABLE KEYS */;

INSERT INTO `hoho_tickets` (`id`, `event_id`, `user_id`, `card`, `visitor`, `state`, `is_tao`, `created_at`, `updated_at`, `deleted_at`)
VALUES
	(97,7,18,'asdasdasdasd1','',0,0,'2016-07-13 04:44:54','2016-07-13 04:44:54',NULL),
	(98,7,18,'sdfsdfsdf2312','',0,0,'2016-07-13 04:44:54','2016-07-13 04:44:54',NULL),
	(99,7,18,'sdfsdf2132343','',0,0,'2016-07-13 04:44:54','2016-07-13 04:44:54',NULL),
	(100,7,18,'456eg64564564','',0,0,'2016-07-13 04:44:54','2016-07-13 04:44:54',NULL),
	(101,7,18,'asdasdasdasd5','',0,0,'2016-07-13 04:44:54','2016-07-13 04:44:54',NULL),
	(102,7,18,'sdfsdfsdf2316','',0,0,'2016-07-13 04:44:54','2016-07-13 04:44:54',NULL),
	(103,7,18,'sdfsdf2132347','',0,0,'2016-07-13 04:44:54','2016-07-13 04:44:54',NULL),
	(104,7,18,'456eg64564568','',0,0,'2016-07-13 04:44:54','2016-07-13 04:44:54',NULL),
	(105,7,18,'asdasdasdasd9','',0,0,'2016-07-13 04:44:54','2016-07-13 04:44:54',NULL),
	(106,7,18,'sdfsdfsdf2310','',0,0,'2016-07-13 04:44:54','2016-07-13 04:44:54',NULL),
	(107,7,18,'sdfsdf213234a','',0,0,'2016-07-13 04:44:54','2016-07-13 04:44:54',NULL),
	(108,7,18,'456eg6456456s','',0,0,'2016-07-13 04:44:54','2016-07-13 04:44:54',NULL),
	(109,7,18,'asdasdasdasdd','',0,0,'2016-07-13 04:44:54','2016-07-13 04:44:54',NULL),
	(110,7,18,'sdfsdfsdf231f','',0,0,'2016-07-13 04:44:54','2016-07-13 04:44:54',NULL),
	(111,7,18,'sdfsdf213234g','',0,0,'2016-07-13 04:44:54','2016-07-13 04:44:54',NULL),
	(112,7,18,'456eg6456456h','',0,0,'2016-07-13 04:44:54','2016-07-13 04:44:54',NULL),
	(113,7,18,'asdasdasdasdj','',0,0,'2016-07-13 04:44:54','2016-07-13 04:44:54',NULL),
	(114,7,18,'sdfsdfsdf231k','',0,0,'2016-07-13 04:44:54','2016-07-13 04:44:54',NULL),
	(115,7,18,'sdfsdf213234l','',0,0,'2016-07-13 04:44:54','2016-07-13 04:44:54',NULL),
	(116,7,18,'456eg6456456z','',0,0,'2016-07-13 04:44:54','2016-07-13 04:44:54',NULL),
	(117,7,18,'asdasdasdasdx','',0,0,'2016-07-13 04:44:54','2016-07-13 04:44:54',NULL),
	(118,7,18,'sdfsdfsdf231c','',0,0,'2016-07-13 04:44:54','2016-07-13 04:44:54',NULL),
	(119,7,18,'sdfsdf213234v','',0,0,'2016-07-13 04:44:54','2016-07-13 04:44:54',NULL),
	(120,7,18,'456eg6456456b','',0,0,'2016-07-13 04:44:54','2016-07-13 04:44:54',NULL);

/*!40000 ALTER TABLE `hoho_tickets` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
