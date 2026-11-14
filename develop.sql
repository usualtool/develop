DROP TABLE IF EXISTS `cms_admin`;
CREATE TABLE `cms_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `state` int(11) DEFAULT '0',
  `roleid` int(11) NOT NULL DEFAULT '1',
  `username` varchar(50) NOT NULL,
  `password` varchar(250) NOT NULL,
  `avatar` varchar(250) DEFAULT NULL,
  `addtime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `cms_admin_log`;
CREATE TABLE `cms_admin_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `ip` varchar(50) NOT NULL,
  `logintime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `cms_admin_role`;
CREATE TABLE `cms_admin_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role` varchar(50) NOT NULL,
  `module` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `cms_api`;
CREATE TABLE `cms_api` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(250) DEFAULT NULL,
  `white` text,
  `opentable` text,
  `protect` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `cms_api_app`;
CREATE TABLE `cms_api_app` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `appid` varchar(100) NOT NULL,
  `appsecret` varchar(100) NOT NULL,
  `status` int(11) DEFAULT 1,
  `bindip` varchar(200) DEFAULT NULL,
  `remark` varchar(200) DEFAULT NULL,
  `addtime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `appid` (`appid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `cms_api_nonce`;
CREATE TABLE `cms_api_nonce` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `appid` varchar(100) NOT NULL,
  `nonce` varchar(64) NOT NULL,
  `addtime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `appid_nonce` (`appid`,`nonce`),
  KEY `addtime` (`addtime`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `cms_api_secret`;
CREATE TABLE `cms_api_secret` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `appid` varchar(100) DEFAULT NULL,
  `secret` varchar(100) DEFAULT NULL,
  `addtime` datetime DEFAULT NULL,
  `usetime` datetime DEFAULT NULL,
  `ip` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `secret` (`secret`),
  KEY `addtime` (`addtime`),
  KEY `ip_addtime` (`ip`,`addtime`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `cms_api_log`;
CREATE TABLE `cms_api_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `act` varchar(50) DEFAULT NULL,
  `utable` varchar(100) DEFAULT NULL,
  `usecret` varchar(100) DEFAULT NULL,
  `uwhere` varchar(500) DEFAULT NULL,
  `udata` text,
  `ip` varchar(100) DEFAULT NULL,
  `addtime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `addtime` (`addtime`),
  KEY `act` (`act`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `cms_module`;
CREATE TABLE `cms_module` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bid` int(11) DEFAULT '0',
  `mid` varchar(50) DEFAULT NULL,
  `modname` varchar(100) DEFAULT NULL,
  `modurl` varchar(200) DEFAULT NULL,
  `isopen` int(11) DEFAULT '0',
  `ordernum` int(11) DEFAULT '0',
  `look` int(11) DEFAULT '0',
  `othername` varchar(100) DEFAULT NULL,
  `befoitem` varchar(250) DEFAULT NULL,
  `backitem` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `cms_plugin`;
CREATE TABLE `cms_plugin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` varchar(50) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `auther` varchar(50) DEFAULT NULL,
  `title` varchar(50) DEFAULT NULL,
  `ver` varchar(10) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `cms_template`;
CREATE TABLE `cms_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tid` varchar(50) DEFAULT NULL,
  `lang` varchar(20) DEFAULT NULL,
  `title` varchar(50) DEFAULT NULL,
  `makefront` int(11) DEFAULT '0',
  `makeadmin` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `cms_update`;
CREATE TABLE `cms_update` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `updateid` int(11) DEFAULT NULL,
  `updatetime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `oauth_clients`;
CREATE TABLE `oauth_clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT '0',
  `client_id` varchar(80) DEFAULT NULL,
  `client_secret` varchar(80) DEFAULT NULL,
  `redirect_uri` varchar(250) DEFAULT NULL,
  `grant_types` varchar(80) DEFAULT 'authorization_code',
  `scope` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `oauth_access_tokens`;
CREATE TABLE `oauth_access_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `access_token` varchar(40) DEFAULT NULL,
  `client_id` varchar(80) DEFAULT NULL,
  `user_id` varchar(80) DEFAULT NULL,
  `expires` timestamp NULL DEFAULT NULL,
  `scope` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `oauth_authorization_codes`;
CREATE TABLE `oauth_authorization_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `authorization_code` varchar(40)  DEFAULT NULL,
  `client_id` varchar(80)  DEFAULT NULL,
  `user_id` varchar(80) DEFAULT NULL,
  `redirect_uri` varchar(250) DEFAULT NULL,
  `expires` timestamp NULL DEFAULT NULL,
  `scope` varchar(250) DEFAULT NULL,
  `id_token` varchar(250) DEFAULT NULL,
  `code_challenge` varchar(128) DEFAULT NULL,
  `code_challenge_method` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `oauth_refresh_tokens`;
CREATE TABLE `oauth_refresh_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `refresh_token` varchar(40) DEFAULT NULL,
  `client_id` varchar(80) DEFAULT NULL,
  `user_id` varchar(80) DEFAULT NULL,
  `expires` timestamp NULL DEFAULT NULL,
  `scope` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `oauth_users`;
CREATE TABLE `oauth_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,  
  `username` varchar(80) DEFAULT NULL,
  `password` varchar(80) DEFAULT NULL,
  `fullname` varchar(80) DEFAULT NULL,
  `email` varchar(80) DEFAULT NULL,
  `addtime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `oauth_scopes`;
CREATE TABLE `oauth_scopes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) DEFAULT NULL,
  `scope` varchar(50) DEFAULT NULL,
  `dbtable` varchar(50) DEFAULT NULL,
  `is_default` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `oauth_jwt`;
CREATE TABLE `oauth_jwt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` varchar(80) DEFAULT NULL,
  `subject` varchar(80) DEFAULT NULL,
  `public_key` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
INSERT INTO `cms_admin` VALUES (1, 0, 1, 'admin', '$2y$12$IXiotxHNFPNDQ3c.tdyvPumkb9W1d1Lr5N7ZDxWgX2cVlBx5n554W', '/assets/images/noimage.png', '2021-08-08 00:00:00');
INSERT INTO `cms_admin_role` VALUES (1, '超级管理', 'ut-frame,ut-module,ut-plugin,ut-template,ut-cac,ut-system,ut-data,ut-oauth,ut-api,ut-power');
INSERT INTO `cms_api` VALUES (1,NULL,NULL,NULL,'password');
INSERT INTO `cms_module` VALUES (1, 3, 'ut-frame', 'UT公共模块', 'index.php', 1, 90, 0, '', NULL, NULL);
INSERT INTO `cms_module` VALUES (2, 3, 'ut-module', '模块', 'index.php', 1, 91, 1, '', '', '模块市场:module.php,管理模块:index.php');
INSERT INTO `cms_module` VALUES (3, 3, 'ut-plugin', '插件', 'index.php', 1, 92, 1, '', '', '插件市场:plugin.php,管理插件:index.php');
INSERT INTO `cms_module` VALUES (4, 3, 'ut-template', '模板', 'index.php', 1, 93, 1, '', '', '模板市场:template.php,创建模板:template_creat.php,管理模板:index.php');
INSERT INTO `cms_module` VALUES (5, 3, 'ut-cac', 'CAC', 'index.php', 1, 94, 1, '', '', '');
INSERT INTO `cms_module` VALUES (6, 3, 'ut-system', '配置', 'index.php', 1, 95, 1, '', '', '');
INSERT INTO `cms_module` VALUES (7, 3, 'ut-data', '数据', 'index.php', 1, 96, 1, '', '', '表管理:index.php,SQL查询:sql.php,备份还原:backup.php');
INSERT INTO `cms_module` VALUES (8, 3, 'ut-oauth', 'OAuth', 'index.php', 1, 97, 1, '', '', '管理应用:index.php,授权记录:log.php,权限集合:role.php,用户列表:user.php');
INSERT INTO `cms_module` VALUES (9, 3, 'ut-api', '接口', 'index.php', 1, 98, 1, '', '', '接口配置:index.php,应用密钥:app.php,在线调试:test.php,Secret票据:secret.php,通行日志:pass.php');
INSERT INTO `cms_module` VALUES (10, 3, 'ut-power', '权限', 'index.php', 1, 99, 1, '', '', '角色:role.php,管理员:index.php,登陆日志:log.php');