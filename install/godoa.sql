CREATE TABLE IF NOT EXISTS `goa_auth_group` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` char(100) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `rules` text COMMENT '规则id',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '说明',
  `listorder` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='用户组表' AUTO_INCREMENT=28 ;
INSERT INTO `goa_auth_group` (`id`, `title`, `status`, `rules`, `description`, `listorder`) VALUES
(1, '超级管理员', 1, 'all', '拥有所有权限', 0),
(2, '登录用户', 1, '4,5,6,7,8', '作用于所有登录用户', 0),
(3, '游客', 1, '2,3', '未登录访客默认权限', 0);
CREATE TABLE IF NOT EXISTS `goa_auth_group_relation` (
  `uid` int(11) unsigned NOT NULL COMMENT '用户id',
  `group_id` int(11) unsigned NOT NULL COMMENT '用户组id',
  UNIQUE KEY `uid_group_id` (`uid`,`group_id`),
  KEY `uid` (`uid`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户组明细表';
INSERT INTO `goa_auth_group_relation` (`uid`, `group_id`) VALUES
(1, 1);
CREATE TABLE IF NOT EXISTS `goa_auth_rule` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '父级id',
  `title` char(20) NOT NULL DEFAULT '' COMMENT '规则中文名称',
  `module` varchar(45) NOT NULL DEFAULT '' COMMENT '模块',
  `controller` varchar(45) NOT NULL DEFAULT '' COMMENT '控制器',
  `action` varchar(45) NOT NULL DEFAULT '' COMMENT '行为',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：为1正常，为0禁用',
  `ismenu` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '菜单显示',
  `condition` char(100) NOT NULL DEFAULT '' COMMENT '附加queryString',
  `icon` varchar(45) NOT NULL DEFAULT '' COMMENT '图标',
  `listorder` int(11) NOT NULL DEFAULT '9999' COMMENT '排序',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='规则表' AUTO_INCREMENT=210 ;
INSERT INTO `goa_auth_rule` (`id`, `pid`, `title`, `module`, `controller`, `action`, `status`, `ismenu`, `condition`, `icon`, `listorder`) VALUES
(1, 0, '系统设置', '', '', '', 1, 1, '', 'fa fa-gears', 0),
(2, 0, '游客权限', '', '', '', 1, 0, '', '', 2),
(3, 2, '登录', 'auth', 'Login', '*', 1, 0, '', '', 0),
(4, 0, '基础权限', '', '', '', 1, 0, '', '', 1),
(5, 4, '后台首页', 'auth', 'Index', 'index', 1, 0, '', '', 0),
(6, 4, '个人信息', 'auth', 'Profile', '*', 1, 0, '', '', 3),
(7, 4, '修改密码', 'auth', 'Password', '*', 1, 0, '', '', 1),
(8, 4, '修改头像', 'auth', 'Avatar', '*', 1, 0, '', '', 2),
(9, 1, '权限组', 'auth', 'Group', 'index', 1, 1, '', '', 0),
(10, 9, '新增', 'auth', 'Group', 'add', 1, 0, '', '', 0),
(11, 9, '修改', 'auth', 'Group', 'edit', 1, 0, '', '', 1),
(12, 9, '删除', 'auth', 'Group', 'delete', 1, 0, '', '', 2),
(13, 1, '菜单管理', 'auth', 'Rule', 'index', 1, 1, '', '', 2),
(14, 13, '添加', 'auth', 'Group', 'add', 1, 0, '', '', 0),
(15, 13, '修改', 'auth', 'Group', 'edit,index', 1, 0, '', '', 1),
(16, 13, '删除', 'auth', 'Group', 'delete', 1, 0, '', '', 2),
(17, 1, '用户管理', 'auth', 'User', 'index', 1, 1, '', '', 1),
(18, 17, '修改', 'auth', 'User', 'edit', 1, 0, '', '', 1),
(19, 17, '新增', 'auth', 'User', 'add', 1, 0, '', '', 0),
(20, 17, '删除', 'auth', 'User', 'delete', 1, 0, '', '', 2),
(21, 17, '回收站', 'auth', 'User', 'recycle', 1, 1, '', 'fa fa-trash', 3);
CREATE TABLE IF NOT EXISTS `goa_users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(60) NOT NULL DEFAULT '' COMMENT '用户名',
  `password` varchar(64) NOT NULL DEFAULT '' COMMENT '登录密码；mb_password加密',
  `name` varchar(45) NOT NULL DEFAULT '' COMMENT '姓名',
  `nickname` varchar(45) NOT NULL DEFAULT '' COMMENT '昵称',
  `avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '用户头像，相对于upload/avatar目录',
  `email` varchar(100) NOT NULL DEFAULT '' COMMENT '登录邮箱',
  `email_code` varchar(60) DEFAULT NULL COMMENT '激活码',
  `phone` bigint(11) unsigned DEFAULT NULL COMMENT '手机号',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '用户状态 0：禁用； 1：正常 ；2：未验证',
  `register_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '注册时间',
  `last_login_ip` varchar(16) NOT NULL DEFAULT '' COMMENT '最后登录ip',
  `last_login_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `listorder` int(11) NOT NULL DEFAULT '0' COMMENT '排序编号',
  `deleted` tinyint(4) NOT NULL DEFAULT '0' COMMENT '删除标记',
  PRIMARY KEY (`id`),
  KEY `user_login_key` (`username`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=111 ;
INSERT INTO `goa_users` (`id`, `username`, `password`, `name`, `nickname`, `avatar`, `email`, `email_code`, `phone`, `status`, `register_time`, `last_login_ip`, `last_login_time`, `listorder`, `deleted`) VALUES
(1, 'admin', 'e10adc3949ba59abbe56e057f20f883e', '魏强', '老四', 'http://goa.godok.cn/statics/images/avatar/default.jpg', 'fafa2088@qq.com', '', 13541350044, 1, 1449199996, '127.0.0.1', 1491535486, 0, 0);