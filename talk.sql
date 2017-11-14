/*
 Navicat Premium Data Transfer

 Source Server         : I
 Source Server Type    : MySQL
 Source Server Version : 50624
 Source Host           : 127.0.0.1
 Source Database       : talk

 Target Server Type    : MySQL
 Target Server Version : 50624
 File Encoding         : utf-8

 Date: 11/14/2017 13:43:04 PM
*/

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `worker_article`
-- ----------------------------
DROP TABLE IF EXISTS `worker_article`;
CREATE TABLE `worker_article` (
  `gid` varchar(32) NOT NULL,
  `account` varchar(32) NOT NULL,
  `content` varchar(10000) NOT NULL,
  `link` varchar(640) DEFAULT NULL,
  `thumbnail` varchar(200) DEFAULT NULL,
  `timestamp` varchar(13) DEFAULT NULL,
  `type` varchar(2) DEFAULT NULL,
  PRIMARY KEY (`gid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `worker_bottle`
-- ----------------------------
DROP TABLE IF EXISTS `worker_bottle`;
CREATE TABLE `worker_bottle` (
  `gid` bigint(20) NOT NULL,
  `content` varchar(2000) DEFAULT NULL,
  `length` varchar(3) DEFAULT NULL,
  `receiver` bigint(20) DEFAULT NULL,
  `region` varchar(20) DEFAULT NULL,
  `sender` bigint(20) DEFAULT NULL,
  `status` smallint(1) NOT NULL DEFAULT '0' COMMENT '0未查看,1已查看,2已接受,3拒绝',
  `timestamp` varchar(13) DEFAULT NULL,
  `type` varchar(32) DEFAULT NULL COMMENT '1添加好友',
  `keep` int(2) DEFAULT '0' COMMENT '计数器',
  `group_id` bigint(20) DEFAULT NULL COMMENT '分组ID',
  PRIMARY KEY (`gid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `worker_bottle`
-- ----------------------------
BEGIN;
INSERT INTO `worker_bottle` VALUES ('68', 'addFriend', null, '2', null, '1', '2', '1495854927', 'addFriend', '20', '113'), ('138', 'addGroup', null, '1', null, '3', '2', '1496295021', 'addGroup', '20', '131'), ('139', 'addGroup', null, '2', null, '3', '2', '1496295021', 'addGroup', '2', '131');
COMMIT;

-- ----------------------------
--  Table structure for `worker_cimsession`
-- ----------------------------
DROP TABLE IF EXISTS `worker_cimsession`;
CREATE TABLE `worker_cimsession` (
  `gid` varchar(128) NOT NULL,
  `nid` bigint(20) DEFAULT NULL,
  `deviceId` varchar(128) DEFAULT NULL COMMENT '设备ID',
  `hostAddress` varchar(64) DEFAULT NULL COMMENT '终端IP',
  `account` varchar(32) DEFAULT NULL COMMENT '用户ID',
  `channel` varchar(32) DEFAULT NULL COMMENT '通道',
  `deviceModel` varchar(64) DEFAULT NULL COMMENT '设备型号',
  `clientVersion` varchar(32) DEFAULT NULL COMMENT '客户端版本号',
  `systemVersion` varchar(32) DEFAULT NULL COMMENT '系统版本号',
  `bindTime` bigint(20) DEFAULT NULL COMMENT '绑定时间',
  `heartbeat` bigint(20) DEFAULT NULL COMMENT '心跳',
  `longitude` double DEFAULT NULL,
  `latitude` double DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `packageName` varchar(64) DEFAULT NULL COMMENT '包名',
  `apnsAble` int(11) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  PRIMARY KEY (`gid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='通讯模型';

-- ----------------------------
--  Table structure for `worker_comment`
-- ----------------------------
DROP TABLE IF EXISTS `worker_comment`;
CREATE TABLE `worker_comment` (
  `gid` varchar(32) NOT NULL,
  `account` varchar(32) DEFAULT NULL,
  `articleId` varchar(32) DEFAULT NULL,
  `content` varchar(320) DEFAULT NULL,
  `timestamp` varchar(13) DEFAULT NULL,
  `type` varchar(12) DEFAULT NULL,
  PRIMARY KEY (`gid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `worker_config`
-- ----------------------------
DROP TABLE IF EXISTS `worker_config`;
CREATE TABLE `worker_config` (
  `gid` varchar(32) NOT NULL,
  `description` varchar(200) DEFAULT NULL,
  `domain` varchar(32) DEFAULT NULL,
  `ikey` varchar(32) DEFAULT NULL,
  `value` text,
  PRIMARY KEY (`gid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `worker_fredback`
-- ----------------------------
DROP TABLE IF EXISTS `worker_fredback`;
CREATE TABLE `worker_fredback` (
  `gid` varchar(32) NOT NULL,
  `account` varchar(32) DEFAULT NULL,
  `appVersion` varchar(32) DEFAULT NULL,
  `content` varchar(320) DEFAULT NULL,
  `deviceModel` varchar(32) DEFAULT NULL,
  `sdkVersion` varchar(32) DEFAULT NULL,
  `timestamp` varchar(13) DEFAULT NULL,
  PRIMARY KEY (`gid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `worker_group`
-- ----------------------------
DROP TABLE IF EXISTS `worker_group`;
CREATE TABLE `worker_group` (
  `groupId` bigint(20) NOT NULL AUTO_INCREMENT,
  `category` varchar(32) DEFAULT NULL COMMENT '分组类型',
  `founder` varchar(32) DEFAULT NULL,
  `name` varchar(32) DEFAULT NULL,
  `summary` varchar(32) DEFAULT NULL,
  `is_effect` smallint(1) NOT NULL DEFAULT '1' COMMENT '是否有效，1有效，0无效,默认有效',
  PRIMARY KEY (`groupId`)
) ENGINE=InnoDB AUTO_INCREMENT=132 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `worker_group`
-- ----------------------------
BEGIN;
INSERT INTO `worker_group` VALUES ('112', 'friend', '2', null, null, '1'), ('113', 'friend', '1', null, null, '1'), ('131', 'group', '3', null, null, '1');
COMMIT;

-- ----------------------------
--  Table structure for `worker_groupmember`
-- ----------------------------
DROP TABLE IF EXISTS `worker_groupmember`;
CREATE TABLE `worker_groupmember` (
  `gid` bigint(20) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `groupId` bigint(20) DEFAULT NULL,
  `host` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`gid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `worker_groupmember`
-- ----------------------------
BEGIN;
INSERT INTO `worker_groupmember` VALUES ('114', '2', '113', null), ('122', '3', '112', null), ('161', '3', '113', null), ('178', '1', '131', null), ('180', '2', '131', null);
COMMIT;

-- ----------------------------
--  Table structure for `worker_host`
-- ----------------------------
DROP TABLE IF EXISTS `worker_host`;
CREATE TABLE `worker_host` (
  `ip` varchar(15) NOT NULL,
  `descrption` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `worker_manager`
-- ----------------------------
DROP TABLE IF EXISTS `worker_manager`;
CREATE TABLE `worker_manager` (
  `account` varchar(20) NOT NULL,
  `name` varchar(16) DEFAULT NULL,
  `password` varchar(64) DEFAULT NULL,
  `status` varchar(1) DEFAULT NULL,
  PRIMARY KEY (`account`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `worker_message`
-- ----------------------------
DROP TABLE IF EXISTS `worker_message`;
CREATE TABLE `worker_message` (
  `mid` bigint(20) NOT NULL AUTO_INCREMENT,
  `action` varchar(255) NOT NULL,
  `content` varchar(3200) DEFAULT NULL,
  `format` varchar(64) DEFAULT NULL,
  `receiver` bigint(20) NOT NULL,
  `sender` bigint(20) NOT NULL,
  `status` varchar(2) DEFAULT NULL,
  `timestamp` bigint(20) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`mid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `worker_momentrule`
-- ----------------------------
DROP TABLE IF EXISTS `worker_momentrule`;
CREATE TABLE `worker_momentrule` (
  `gid` varchar(32) NOT NULL,
  `account` varchar(32) DEFAULT NULL,
  `otherAccount` varchar(32) DEFAULT NULL,
  `type` varchar(1) DEFAULT NULL,
  PRIMARY KEY (`gid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `worker_organization`
-- ----------------------------
DROP TABLE IF EXISTS `worker_organization`;
CREATE TABLE `worker_organization` (
  `code` varchar(32) NOT NULL,
  `name` varchar(32) DEFAULT NULL,
  `parentCode` varchar(32) DEFAULT NULL,
  `sort` int(11) DEFAULT NULL,
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `worker_publicaccount`
-- ----------------------------
DROP TABLE IF EXISTS `worker_publicaccount`;
CREATE TABLE `worker_publicaccount` (
  `account` varchar(32) NOT NULL,
  `apiUrl` varchar(100) DEFAULT NULL,
  `description` varchar(320) DEFAULT NULL,
  `greet` varchar(320) DEFAULT NULL,
  `link` varchar(640) DEFAULT NULL,
  `name` varchar(20) DEFAULT NULL,
  `power` varchar(640) DEFAULT NULL,
  `status` varchar(2) DEFAULT NULL,
  `timestamp` varchar(13) DEFAULT NULL,
  PRIMARY KEY (`account`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `worker_publicmenu`
-- ----------------------------
DROP TABLE IF EXISTS `worker_publicmenu`;
CREATE TABLE `worker_publicmenu` (
  `gid` varchar(32) NOT NULL,
  `account` varchar(32) DEFAULT NULL,
  `code` varchar(32) DEFAULT NULL,
  `content` varchar(1024) DEFAULT NULL,
  `fid` varchar(32) DEFAULT NULL,
  `link` varchar(640) DEFAULT NULL,
  `name` varchar(20) DEFAULT NULL,
  `sort` int(11) DEFAULT NULL,
  `type` varchar(2) DEFAULT NULL,
  PRIMARY KEY (`gid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `worker_sequence`
-- ----------------------------
DROP TABLE IF EXISTS `worker_sequence`;
CREATE TABLE `worker_sequence` (
  `name` varchar(32) DEFAULT NULL,
  `value` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`value`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=187 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `worker_sequence`
-- ----------------------------
BEGIN;
INSERT INTO `worker_sequence` VALUES ('a', '186');
COMMIT;

-- ----------------------------
--  Table structure for `worker_sms`
-- ----------------------------
DROP TABLE IF EXISTS `worker_sms`;
CREATE TABLE `worker_sms` (
  `cms_code` char(6) DEFAULT NULL,
  `mobile` char(11) DEFAULT NULL,
  `expire_time` char(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `worker_sms`
-- ----------------------------
BEGIN;
INSERT INTO `worker_sms` VALUES ('123456', '13924667758', null);
COMMIT;

-- ----------------------------
--  Table structure for `worker_snsshake`
-- ----------------------------
DROP TABLE IF EXISTS `worker_snsshake`;
CREATE TABLE `worker_snsshake` (
  `gid` varchar(32) NOT NULL,
  `account` varchar(64) DEFAULT NULL,
  `timestamp` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`gid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `worker_subscriber`
-- ----------------------------
DROP TABLE IF EXISTS `worker_subscriber`;
CREATE TABLE `worker_subscriber` (
  `gid` varchar(32) NOT NULL,
  `account` varchar(32) DEFAULT NULL,
  `publicAccount` varchar(32) DEFAULT NULL,
  `timestamp` varchar(13) DEFAULT NULL,
  PRIMARY KEY (`gid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `worker_token`
-- ----------------------------
DROP TABLE IF EXISTS `worker_token`;
CREATE TABLE `worker_token` (
  `login_time` char(10) DEFAULT NULL,
  `expire_time` char(10) DEFAULT NULL,
  `token` char(32) NOT NULL,
  `user_id` char(32) NOT NULL,
  PRIMARY KEY (`user_id`,`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `worker_token`
-- ----------------------------
BEGIN;
INSERT INTO `worker_token` VALUES ('1495953032', '1497224518', '3ef648d244685cca6434342e6f467100', '1'), ('1497057548', '1497166632', 'c2976911b603130d357eaf25e3606f95', '186'), ('1496046792', '1497224556', 'f383770ac4e14dd524e75f8b3a321c57', '2'), ('1496291937', '1496378337', '3886c8d4112802c09c067f80a00b2631', '3');
COMMIT;

-- ----------------------------
--  Table structure for `worker_user`
-- ----------------------------
DROP TABLE IF EXISTS `worker_user`;
CREATE TABLE `worker_user` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `email` varchar(50) DEFAULT NULL,
  `gender` smallint(1) DEFAULT NULL COMMENT '性别',
  `grade` int(11) DEFAULT NULL COMMENT '级别',
  `latitude` double DEFAULT NULL COMMENT '纬度',
  `location` varchar(100) DEFAULT NULL,
  `longitude` double DEFAULT NULL COMMENT '经度',
  `motto` varchar(200) DEFAULT NULL COMMENT '签名',
  `name` varchar(16) DEFAULT NULL COMMENT '呢称',
  `online` varchar(1) DEFAULT NULL COMMENT '是否在线',
  `orgCode` varchar(32) DEFAULT NULL COMMENT '分组代码',
  `password` varchar(64) DEFAULT NULL COMMENT '口令',
  `power` varchar(32) DEFAULT NULL,
  `telephone` varchar(20) NOT NULL COMMENT '手机',
  `token` varchar(32) DEFAULT NULL,
  `is_effect` smallint(1) NOT NULL DEFAULT '1' COMMENT '是否有效1有效，0无效；默认1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `telephone` (`telephone`)
) ENGINE=InnoDB AUTO_INCREMENT=187 DEFAULT CHARSET=utf8 COMMENT='用户管理';

-- ----------------------------
--  Records of `worker_user`
-- ----------------------------
BEGIN;
INSERT INTO `worker_user` VALUES ('1', null, null, null, null, null, null, null, 'johnny1', null, null, 'e10adc3949ba59abbe56e057f20f883e', null, '13244556677', null, '1'), ('2', null, null, null, null, null, null, null, 'johnny2', null, null, 'e10adc3949ba59abbe56e057f20f883e', null, '13244557788', null, '1'), ('3', null, null, null, null, null, null, null, 'johnny3', null, null, 'e10adc3949ba59abbe56e057f20f883e', null, '13244558899', null, '1');
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
