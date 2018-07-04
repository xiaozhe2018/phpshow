CREATE TABLE `bone_admin` (
  `admin_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '管理id',
  `user_name` varchar(20) DEFAULT NULL COMMENT '用户名',
  `userpwd` char(32) DEFAULT NULL COMMENT '用户密码',
  `email` varchar(50) NOT NULL COMMENT '邮箱',
  `pools` varchar(20) DEFAULT NULL COMMENT '权限池',
  `groups` varchar(100) NOT NULL COMMENT '权限组',
  `regtime` int(11) NOT NULL COMMENT '注册时间',
  `regip` varchar(15) NOT NULL COMMENT '注册ip',
  `sta` smallint(6) NOT NULL COMMENT '帐号状态',
  `logintime` int(10) unsigned NOT NULL COMMENT '最后登录时间',
  `loginip` varchar(15) NOT NULL COMMENT '最后登录IP',
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `userid` (`user_name`),
  KEY `sta` (`sta`),
  KEY `pools` (`pools`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

CREATE TABLE `bone_admin_login` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) unsigned NOT NULL COMMENT '用户id',
  `accounts` varchar(60) NOT NULL COMMENT '用户名',
  `loginip` varchar(15) NOT NULL COMMENT '登录ip',
  `logintime` int(10) unsigned NOT NULL COMMENT '登录时间',
  `pools` varchar(20) NOT NULL COMMENT '应用池',
  `loginsta` tinyint(2) unsigned NOT NULL COMMENT '登录时状态',
  `cli_hash` varchar(32) NOT NULL COMMENT '用户登录名和ip的hash',
  PRIMARY KEY (`id`),
  KEY `logintime` (`logintime`),
  KEY `cli_hash` (`cli_hash`,`loginsta`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `bone_admin_oplog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '项id',
  `user_name` varchar(20) NOT NULL COMMENT '管理员用户名',
  `msg` varchar(250) NOT NULL COMMENT '消息内容',
  `do_time` int(10) unsigned NOT NULL COMMENT '发生时间',
  `do_ip` varchar(15) NOT NULL COMMENT '客户端IP',
  `do_url` varchar(100) NOT NULL COMMENT '操作网址',
  PRIMARY KEY (`id`),
  KEY `user_name` (`user_name`),
  KEY `do_time` (`do_time`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='管理员操作日志';

CREATE TABLE `bone_admin_purview` (
  `admin_id` int(11) NOT NULL COMMENT '管理员id',
  `purviews` text NOT NULL COMMENT '配置字符',
  PRIMARY KEY (`admin_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `bone_chip` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT 'chip名称(建议用非中文)',
  `description` varchar(100) NOT NULL COMMENT '描述',
  `isarray` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否列表数组',
  `data` mediumtext NOT NULL COMMENT '数据',
  `template` text NOT NULL COMMENT '数组类型chip模板',
  `sortrank` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '排序（小靠前）',
  `disabled` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否禁用',
  `typeid` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '所属分类',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='内容碎片';

CREATE TABLE `bone_friendlinks` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `webname` varchar(50) NOT NULL COMMENT '网站名称',
  `url` varchar(100) NOT NULL COMMENT '网址',
  `sortrank` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '排序(值大在前)',
  `logo` varchar(100) NOT NULL DEFAULT '' COMMENT '网站logo@image@',
  `description` char(200) NOT NULL DEFAULT '' COMMENT '网站描述',
  `type` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '类型@catalog-5@',
  `position` tinyint(1) unsigned NOT NULL DEFAULT '2' COMMENT '显示位置(2主页 1内页 0不显示)',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `bone_config` (
  `sort_id` smallint(6) NOT NULL DEFAULT '0' COMMENT '排序id',
  `name` varchar(20) NOT NULL COMMENT '变量名称',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '说明标题',
  `info` varchar(200) NOT NULL COMMENT '备注',
  `group_id` smallint(5) unsigned NOT NULL DEFAULT '1' COMMENT '分组',
  `type` varchar(10) NOT NULL DEFAULT 'string' COMMENT '变量类型',
  `value` text COMMENT '变量值',
  PRIMARY KEY (`name`),
  KEY `sortid` (`sort_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='系统配置变量';

CREATE TABLE `bone_catalog_base` (
  `cid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '分类id',
  `cmid` varchar(12) NOT NULL DEFAULT '' COMMENT '模型id',
  `pid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '父类id',
  `cname` varchar(50) NOT NULL DEFAULT '' COMMENT '分类名称',
  `sortrank` smallint(6) NOT NULL DEFAULT '0' COMMENT '排序(值大在前)',
  `ico` varchar(50) NOT NULL DEFAULT '' COMMENT '分类ico(@image@)',
  PRIMARY KEY (`cid`),
  KEY `cmid` (`cmid`)
) ENGINE=MyISAM AUTO_INCREMENT=100 DEFAULT CHARSET=utf8 COMMENT='分类数据基本表';

CREATE TABLE `bone_catalog_model` (
  `cmid` varchar(12) NOT NULL DEFAULT '' COMMENT '分类模型id',
  `cmname` varchar(50) NOT NULL DEFAULT '' COMMENT '分类模型名',
  `cmtable` varchar(30) NOT NULL DEFAULT '' COMMENT '分类数据表(默认为catalog_base)',
  `sortrank` smallint(6) NOT NULL DEFAULT '0' COMMENT '排序（大的靠前）',
  `showmenu` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否出现在快捷菜单',
  `adm_url` varchar(50) NOT NULL DEFAULT '' COMMENT '管理入口url(默认自动托管)',
  `delopt` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否允许删除',
  PRIMARY KEY (`cmid`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='分类数据模型';

INSERT INTO `bone_config` (`sort_id`, `name`, `title`, `info`, `group_id`, `type`, `value`) VALUES(98, 'site_url', '主站网址', '', 1, 'string', 'http://www.phpbone.com');

INSERT INTO `bone_config` (`sort_id`, `name`, `title`, `info`, `group_id`, `type`, `value`) VALUES(99, 'site_name', '主站名称', '', 1, 'string', 'PHPBONE开发框架');

INSERT INTO `bone_config` (`sort_id`, `name`, `title`, `info`, `group_id`, `type`, `value`) VALUES(99, 'site_upload_path', '附件上传目录', '', 3, 'string', '/uploads');

INSERT INTO `bone_config` (`sort_id`, `name`, `title`, `info`, `group_id`, `type`, `value`) VALUES(85, 'attachment_size', '最大附件大小(Mb)', '', 3, 'number', '16');

INSERT INTO `bone_config` (`sort_id`, `name`, `title`, `info`, `group_id`, `type`, `value`) VALUES(0, 'admin_df_purview', '管理员默认权限', '', 0, 'string', '<pools:admin name="管理员" auttype="session" login_control="?ct=index&ac=login">\r\n\r\n    <!—— //公开的控制器，不需登录就能访问 ——>\r\n    <ctl:public>index-login,index-loginout,index-validate_image</ctl:public>\r\n\r\n    <!—— //保护的控制器，当前池会员登录后都能访问 ——>\r\n    <ctl:protected>index-index,index-adminmsg,users-mypurview</ctl:protected>\r\n\r\n    <!—— //私有控制器，只有特定组才能访问 ——>\r\n    <ctl:private>\r\n        <admin name="管理员">*</admin>\r\n        <test name="测试组">index-index,system-iplimit,users-editpwd,system-log,system-login_log</test>\r\n        <boy name="帅哥组">index-index,users-editpwd</boy>\r\n    </ctl:private>\r\n\r\n</pools:admin>\r\n');

INSERT INTO `bone_config` (`sort_id`, `name`, `title`, `info`, `group_id`, `type`, `value`) VALUES(0, 'ip_limit', '后台登录IP限制', '', 0, 'string', '');

INSERT INTO `bone_config` (`sort_id`, `name`, `title`, `info`, `group_id`, `type`, `value`) VALUES(98, 'site_upload_url', '附件目录网址', '如果不使用二级域名，此项留空', 3, 'string', '');

INSERT INTO `bone_config` (`sort_id`, `name`, `title`, `info`, `group_id`, `type`, `value`) VALUES(80, 'site_tj', '主站统计代码', '', 1, 'text', '');

INSERT INTO `bone_config` (`sort_id`, `name`, `title`, `info`, `group_id`, `type`, `value`) VALUES(90, 'site_description', '主站摘要信息', '', 1, 'text', '');

INSERT INTO `bone_config` (`sort_id`, `name`, `title`, `info`, `group_id`, `type`, `value`) VALUES(96, 'site_keyword', '主站关键字', '', 1, 'string', '');

INSERT INTO `bone_config` (`sort_id`, `name`, `title`, `info`, `group_id`, `type`, `value`) VALUES(88, 'attachment_image', '图片文件类型', '', 3, 'string', 'jpg|png|gif|bmp|ico');

INSERT INTO `bone_config` (`sort_id`, `name`, `title`, `info`, `group_id`, `type`, `value`) VALUES(100, 'open_upload', '是否允许上传文件', '', 3, 'bool', '1');

INSERT INTO `bone_config` (`sort_id`, `name`, `title`, `info`, `group_id`, `type`, `value`) VALUES(87, 'attachment_media', '多媒体文件类型', '', 3, 'string', 'mp3|avi|mpg|mp4|3gp|flv|rm|rmvb|wmv|swf');

INSERT INTO `bone_config` (`sort_id`, `name`, `title`, `info`, `group_id`, `type`, `value`) VALUES(86, 'attachment_soft', '其它文件件类型', '', 3, 'string', 'zip|7z|rar|gz|bz2|tar|iso|exe|dll|doc|xls|ppt|docx|xlsx|pptx|wps|pdf|psd');

INSERT INTO `bone_config` (`sort_id`, `name`, `title`, `info`, `group_id`, `type`, `value`) VALUES(99, 'doc_auto_keywords', '自动获取关键字', '', 4, 'bool', '1');

INSERT INTO `bone_config` (`sort_id`, `name`, `title`, `info`, `group_id`, `type`, `value`) VALUES(98, 'doc_auto_des', '自动提取摘要', '', 4, 'bool', '1');

INSERT INTO `bone_config` (`sort_id`, `name`, `title`, `info`, `group_id`, `type`, `value`) VALUES(97, 'doc_auto_thumb', '自动提取缩略图', '', 4, 'bool', '0');

INSERT INTO `bone_config` (`sort_id`, `name`, `title`, `info`, `group_id`, `type`, `value`) VALUES(96, 'doc_thumb_w', '缩略图默认宽度', '', 4, 'number', '200');

INSERT INTO `bone_config` (`sort_id`, `name`, `title`, `info`, `group_id`, `type`, `value`) VALUES(95, 'doc_thumb_h', '缩略图默认高度', '', 4, 'number', '200');

INSERT INTO `bone_config` (`sort_id`, `name`, `title`, `info`, `group_id`, `type`, `value`) VALUES(90, 'doc_down_remove', '抓取远程资源', '', 4, 'bool', '0');

INSERT INTO `bone_config` (`sort_id`, `name`, `title`, `info`, `group_id`, `type`, `value`) VALUES(94, 'doc_auto_des_len', '自动摘要长度', '', 4, 'number', '150');

INSERT INTO `bone_catalog_base` (`cid`, `cmid`, `pid`, `cname`, `sortrank`, `ico`) VALUES(11, '2', 0, 'IT资讯', 0, '');

INSERT INTO `bone_catalog_base` (`cid`, `cmid`, `pid`, `cname`, `sortrank`, `ico`) VALUES(12, '2', 0, '地方门户', 0, '');

INSERT INTO `bone_catalog_base` (`cid`, `cmid`, `pid`, `cname`, `sortrank`, `ico`) VALUES(13, '2', 0, '综合网站', 0, '');

INSERT INTO `bone_catalog_base` (`cid`, `cmid`, `pid`, `cname`, `sortrank`, `ico`) VALUES(14, '2', 0, '电子商务', 0, '');

INSERT INTO `bone_catalog_base` (`cid`, `cmid`, `pid`, `cname`, `sortrank`, `ico`) VALUES(1, '1', 0, '基本配置', 99, '');

INSERT INTO `bone_catalog_base` (`cid`, `cmid`, `pid`, `cname`, `sortrank`, `ico`) VALUES(2, '1', 0, 'SEO变量', 98, '');

INSERT INTO `bone_catalog_base` (`cid`, `cmid`, `pid`, `cname`, `sortrank`, `ico`) VALUES(3, '1', 0, '附件设置', 97, '');

INSERT INTO `bone_catalog_base` (`cid`, `cmid`, `pid`, `cname`, `sortrank`, `ico`) VALUES(4, '1', 0, '文档设置', 96, '');

INSERT INTO `bone_catalog_base` (`cid`, `cmid`, `pid`, `cname`, `sortrank`, `ico`) VALUES(5, '1', 0, '互动设置', 95, '');

INSERT INTO `bone_catalog_base` (`cid`, `cmid`, `pid`, `cname`, `sortrank`, `ico`) VALUES(6, '1', 0, '模块设置', 94, '');

INSERT INTO `bone_catalog_base` (`cid`, `cmid`, `pid`, `cname`, `sortrank`, `ico`) VALUES(7, '1', 0, '其它选项', 93, '');

INSERT INTO `bone_catalog_model` (`cmid`, `cmname`, `cmtable`, `sortrank`, `showmenu`, `adm_url`, `delopt`) VALUES('2', '友情链接', 'base', 998, 1, '', 0);

INSERT INTO `bone_catalog_model` (`cmid`, `cmname`, `cmtable`, `sortrank`, `showmenu`, `adm_url`, `delopt`) VALUES('1', '系统配置', 'base', 999, 0, '', 0);

INSERT INTO `bone_config` (`sort_id`, `name`, `title`, `info`, `group_id`, `type`, `value`) VALUES(0, 'admin_menu', '管理菜单', '', 0, 'string', '<!-- //不显示的项，用于前置app声明 -->\r\n<menu name=\'APP声明\' display=\'none\'> \r\n    <node appname=\'管理中心\' ct=\'index\'>\r\n        <!-- //这里的子菜单为隐性项目 -->\r\n        <item name=\'主框架页\' url=\'\' ct=\'index\' ac=\'index\' />\r\n    </node>\r\n    <node appname=\'帐号管理\'  ct=\'users\'></node>\r\n    <node appname=\'系统管理\'  ct=\'system\'></node>\r\n    <node appname=\'碎片管理\'  ct=\'chip\'></node>\r\n    <node appname=\'广告管理\'  ct=\'ads\'></node>\r\n    <node appname=\'分类管理\'  ct=\'catalog\'></node>\r\n    <node appname=\'友情链接\'  ct=\'friendlink\'></node>\r\n    <node appname=\'文档管理\'  ct=\'doc\'></node>\r\n   <node appname=\'文档分类管理\'  ct=\'doc_catalog\'></node>\r\n    <node appname=\'会员管理\'  ct=\'member\'></node>\r\n    <node appname=\'调试开发\'  ct=\'debug\'></node>\r\n    <node appname=\'更新HTML\'  ct=\'html\'></node>\r\n    <!--appname-->\r\n</menu>\r\n\r\n<!-- //正常的菜单项  -->\r\n<menu name=\'常用\' class=\"common\"> \r\n <node name=\'内容管理\'>\r\n      <item name=\'分类模型管理\' url=\'\' ct=\'catalog\' ac=\'model\' />\r\n      <item name=\'文档栏目管理\' url=\'\' ct=\'doc_catalog\' />\r\n      <item name=\'文档管理\' url=\'\' ct=\'doc\' />\r\n      <item name=\'新增文档\' url=\'?ct=doc&even=add\' ct=\'doc\' />\r\n      <!--appitem-->\r\n  </node>\r\n  <node name=\'常用操作\'>\r\n       <item name=\'系统帐号管理\' url=\'\' ct=\'users\' ac=\'index\' />\r\n       <item name=\'系统配置管理\' url=\'\' ct=\'system\' ac=\'config_list\' />\r\n       <item name=\'修改密码\' url=\'\' ct=\'users\' ac=\'editpwd\' />\r\n  </node>\r\n</menu>\r\n\r\n<menu name=\'文档\'> \r\n<node name=\"帮助文档管理\">\r\n     <item name=\'新增文档\' url=\'?ct=doc&even=add\' ct=\'doc\' />\r\n     <item name=\'文档管理\' url=\'\' ct=\'doc\' />\r\n     <item name=\'文档回收站\' url=\'?ct=doc&sta=-1\' ct=\'doc\' />\r\n     <item name=\'文档HTML更新\' url=\'\' ct=\'html\' ac=\'doc\' />\r\n</node>\r\n<node name=\"分类管理\">\r\n       <item name=\'文档分类\' url=\'\' ct=\'doc_catalog\' />\r\n       <item name=\'文档属性\' url=\'?ct=catalog&cmid=4\' ct=\'catalog\' />\r\n</node>\r\n</menu>\r\n\r\n<menu name=\'会员\' class=\"user\"> \r\n<node name=\"会员管理\">\r\n       <item name=\'会员管理\' url=\'\' ct=\'member\' />\r\n</node>\r\n<node name=\"会员设置\">\r\n       <item name=\'会员权限配置\' url=\'\' ct=\'system\' ac=\'edit_member_xml\' />\r\n</node>\r\n</menu>\r\n\r\n<menu name=\"模块\">\r\n<node name=\"辅助功能\">\r\n    <item name=\'chip标签管理\' url=\'\' ct=\'chip\' ac=\'index\' />\r\n    <item name=\'友情链接管理\' url=\'\' ct=\'friendlink\' ac=\'index\' />\r\n    <item name=\'广告管理\' url=\'\' ct=\'ads\' ac=\'index\' />\r\n</node>\r\n<node name=\"分类管理\">\r\n       <item name=\'分类模型管理\' url=\'\' ct=\'catalog\' ac=\'model\' />\r\n        <#catalog_menu#>\r\n</node>\r\n</menu>\r\n\r\n<menu name=\'测试\'> \r\n  <node name=\'开发调试\'>\r\n     <item name=\'CRUD向导\' url=\'\' ct=\'debug\' ac=\'lurd\' />\r\n     <item name=\'模板标签测试\' url=\'\' ct=\'debug\' ac=\'tpltest\' />\r\n     <item name=\'数据库文档\' url=\'\' ct=\'debug\' ac=\'dbinfos\' />\r\n  </node>\r\n</menu>\r\n\r\n<menu name=\'系统\'  class=\"set\"> \r\n  <node name=\'帐号管理\'>\r\n    <item name=\'系统帐号管理\' url=\'\' ct=\'users\' ac=\'index\' />\r\n    <item name=\'组权限管理\' url=\'\' ct=\'users\' ac=\'edit_purview_groups\' />\r\n    <item name=\'我的权限\' url=\'\' ct=\'users\' ac=\'mypurview\' default=\'1\' />\r\n    <item name=\'修改密码\' url=\'\' ct=\'users\' ac=\'editpwd\' />\r\n  </node>\r\n  <node name=\'系统管理\'>\r\n    <item name=\'后台菜单配置\' url=\'\' ct=\'system\' ac=\'edit_admin_menu\' />\r\n    <item name=\'系统配置管理\' url=\'\'\' ct=\'system\' ac=\'config_list\' />\r\n    <item name=\'登录IP限制\' url=\'\' ct=\'system\' ac=\'edit_iplimit\' />\r\n    <item name=\'操作日志\' url=\'\' ct=\'system\' ac=\'oplog\' />\r\n    <item name=\'登录日志\' url=\'\' ct=\'system\' ac=\'login_log\' />\r\n  </node>\r\n</menu>');

INSERT INTO `bone_admin` (`admin_id`, `user_name`, `userpwd`, `email`, `pools`, `groups`, `regtime`, `regip`, `sta`, `logintime`, `loginip`) VALUES(1, 'admin', '21232f297a57a5a743894a0e4a801fc3', '', 'admin', 'admin_admin', 0, '', 0, 1385377768, '127.0.0.1');