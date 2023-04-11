/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50726
Source Host           : localhost:3306
Source Database       : seablog

Target Server Type    : MYSQL
Target Server Version : 50726
File Encoding         : 65001

Date: 2021-06-12 10:30:31
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for s_advanced
-- ----------------------------
DROP TABLE IF EXISTS `s_advanced`;
CREATE TABLE `s_advanced` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shenhe_switch` int(1) DEFAULT '0',
  `pinlun_switch` int(1) DEFAULT NULL,
  `liuyan_switch` int(1) DEFAULT NULL,
  `smtp` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `smtpsecure` varchar(255) DEFAULT NULL,
  `port` varchar(255) DEFAULT NULL,
  `sendemail` varchar(255) DEFAULT NULL,
  `sendname` varchar(255) DEFAULT NULL,
  `replyemail` varchar(255) DEFAULT NULL,
  `replyename` varchar(255) DEFAULT NULL,
  `liuyan` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of s_advanced
-- ----------------------------
INSERT INTO `s_advanced` VALUES ('1', '0', '0', '0', '', '', '', '', '', '', '', '', '', '<div style=\"border:1px double #f60;\">\r\n                         <div style=\"background:#F60; padding:10px 10px 10px 20px; color:#FFF; font-size:16px;\">\r\n                         <a style=\"text-decoration:none;color:#fff;\" href=\"http://\'.$basic[\'url\'].\'\" target=\"_blank\">\'.$basic[\'name\'].\'</a> 上有新的留言：\r\n                         </div>\r\n                         <div style=\" padding:10px 10px 5px 20px; font-size:12px\">亲爱的 [ \'.$basic[\'author\'].\' ] ：您好!</div>\r\n                         <div style=\" padding:5px 10px 10px 20px; font-size:12px\">[ \'.$data[\'name\'].\' ] 在 [ \'.$basic[\'name\'].\' ] 上发表了留言：</div>\r\n                         <div style=\"padding:10px 10px 10px 10px; font-size:12px; background:#f2f2f2;border:1px double #ccc; margin:0px 15px 0px 15px; line-height:25px;\">\'.$data[\'content\'].\'</div>\r\n                         <div style=\" padding:10px 10px 10px 20px; font-size:12px\">→ 您可以点击 <a style=\"text-decoration:none;\" href=\"http://\'.$basic[\'url\'].url(\'/guestbook\').\'#comment\" target=\"_blank\">查看完整內容</a></div>\r\n                         <div style=\" padding:10px 10px 10px 20px; font-size:12px\"><strong>温馨提示</strong> 本邮件由系统自动发出，可以直接回复！</div>\r\n                         </div>');

-- ----------------------------
-- Table structure for s_album
-- ----------------------------
DROP TABLE IF EXISTS `s_album`;
CREATE TABLE `s_album` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `pinyin` varchar(255) DEFAULT NULL,
  `status` int(1) DEFAULT NULL,
  `time` bigint(20) DEFAULT NULL,
  `click` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of s_album
-- ----------------------------
INSERT INTO `s_album` VALUES ('1', '默认相册', 'moren', '1', '1621318924', '3');

-- ----------------------------
-- Table structure for s_article
-- ----------------------------
DROP TABLE IF EXISTS `s_article`;
CREATE TABLE `s_article` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `class` int(11) DEFAULT NULL,
  `click` int(11) DEFAULT NULL,
  `tag` varchar(255) DEFAULT NULL,
  `status` int(1) DEFAULT NULL,
  `top` int(1) DEFAULT '0' COMMENT '置顶',
  `img` varchar(255) DEFAULT NULL,
  `simg` varchar(255) DEFAULT NULL,
  `content` longtext,
  `time` bigint(20) DEFAULT NULL,
  `edittime` bigint(20) DEFAULT NULL,
  `ip` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url_only` (`url`) USING BTREE COMMENT 'url 唯一性'
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of s_article
-- ----------------------------
INSERT INTO `s_article` VALUES ('1', 'j9tq8hrx65', '这是一篇示例文章', '1', '0', '这是,示例', '1', '0', '', null, '<p>这是一篇示例文章</p>', '1621317955', '1621318211', null);
INSERT INTO `s_article` VALUES ('2', 'svcahvxs5f', '这是第二篇示例文章', '1', '0', '这是,第二', '1', '0', '', null, '<p>这是第二篇示例文章</p>', '1621318147', '1621318208', null);
INSERT INTO `s_article` VALUES ('3', 'pwzcgxarb1', '这是第三篇示例文章', '1', '0', '这是,第三', '1', '0', '', null, '<p>这是第三篇示例文章</p>', '1621318157', '1621318205', null);
INSERT INTO `s_article` VALUES ('4', 'sau0sbgv6a', '这是第四篇示例文章', '1', '0', '这是,示例', '1', '0', '/storage/uploads/images/20210518/e96662a108b2cf53601e289067b7d2fa.jpg', '/storage/uploads/images/20210518/s-e96662a108b2cf53601e289067b7d2fa.jpg', '<p>这是第四篇示例文章</p>', '1621318168', '1621319152', null);
INSERT INTO `s_article` VALUES ('5', 'le1pzk0mhq', '欢迎使用熊海博客V3.0系统', '1', '7', '这是,第五', '1', '1', '/storage/uploads/images/20210518/4fba3aefcaf56116cf2e8adb5a8db1f0.jpg', '/storage/uploads/images/20210518/s-4fba3aefcaf56116cf2e8adb5a8db1f0.jpg', '<p>这个博客系统是由熊海于2021年5月开发完成，目前模板只开发了两套，因为所有代码都只能由我一个人慢慢完成，时间成本很大，后面有空闲时间会尽量多写一些模板。届时会放在<a href=\"http://www.imxh.cn/\" style=\"white-space: normal;\">http://www.imxh.cn</a>上免费下载。</p><p>博客采用Thinkphp6.0最新框架，框架要求PHP环境7.2.5或以上，前后台采用H5，自适应手机、平板、电脑等，还有很多功能没有一一介绍，基本上博客的常用功能都有了，大家自己摸索吧。</p><p><span style=\"background-color: rgb(255, 255, 255); color: rgb(51, 51, 51); font-family: &quot;Microsoft YaHei&quot;, &quot;Lucida Grande&quot;, &quot;Microsoft JhengHei&quot;; text-indent: 20px;\">现在的博客名称版本定为&quot;</span><span style=\"font-family: &quot;Microsoft YaHei&quot;, &quot;Lucida Grande&quot;, &quot;Microsoft JhengHei&quot;; text-indent: 20px; box-sizing: border-box; color: rgb(111, 111, 255);\">熊海博客 V3.0</span><span style=\"background-color: rgb(255, 255, 255); color: rgb(51, 51, 51); font-family: &quot;Microsoft YaHei&quot;, &quot;Lucida Grande&quot;, &quot;Microsoft JhengHei&quot;; text-indent: 20px;\">&quot;，全部完成后会开源在各大源码平台，届时欢迎大家使用。</span></p><p><span style=\"background-color: rgb(255, 255, 255); color: rgb(51, 51, 51); font-family: &quot;Microsoft YaHei&quot;, &quot;Lucida Grande&quot;, &quot;Microsoft JhengHei&quot;; text-indent: 20px;\">赞助</span><span style=\"font-family: &quot;Microsoft YaHei&quot;, &quot;Lucida Grande&quot;, &quot;Microsoft JhengHei&quot;; text-indent: 20px; box-sizing: border-box; color: rgb(111, 111, 255);\">支付宝：me@isea.so</span><span style=\"background-color: rgb(255, 255, 255); color: rgb(51, 51, 51); font-family: &quot;Microsoft YaHei&quot;, &quot;Lucida Grande&quot;, &quot;Microsoft JhengHei&quot;; text-indent: 20px;\">&nbsp;-&gt;如果你觉得还不错，欢迎赞助我。</span></p><p>使用中遇到任何问题或者建议，请你前往官网：<a href=\"http://www.imxh.cn\">http://www.imxh.cn</a>&nbsp;反馈。&nbsp;QQ群：22206973。</p>', '1621318175', '1623464577', null);

-- ----------------------------
-- Table structure for s_basic
-- ----------------------------
DROP TABLE IF EXISTS `s_basic`;
CREATE TABLE `s_basic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `keywords` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `mail` varchar(255) DEFAULT NULL,
  `qq` varchar(255) DEFAULT NULL,
  `tel` varchar(255) DEFAULT NULL,
  `icp` varchar(255) DEFAULT NULL,
  `copyright` text,
  `ver` varchar(255) DEFAULT NULL,
  `status` int(1) DEFAULT NULL,
  `statistics` text,
  `author` varchar(255) DEFAULT NULL,
  `author_img` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of s_basic
-- ----------------------------
INSERT INTO `s_basic` VALUES ('1', '熊海博客系统', '欢迎使用熊海博客系统V 3.0', '', '', 'www.imxh.cn', '86226999@qq.com', '86226999', '', '京ICP备123456号', '©2021  www.imxh.cn', '3.0.2', '1', '', '佚名', '/storage/images/user.jpg');

-- ----------------------------
-- Table structure for s_class
-- ----------------------------
DROP TABLE IF EXISTS `s_class`;
CREATE TABLE `s_class` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `menu` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `pinyin` varchar(255) DEFAULT NULL,
  `status` int(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of s_class
-- ----------------------------
INSERT INTO `s_class` VALUES ('1', '3', '默认分类', 'list', '1');

-- ----------------------------
-- Table structure for s_guestbook
-- ----------------------------
DROP TABLE IF EXISTS `s_guestbook`;
CREATE TABLE `s_guestbook` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(1) DEFAULT '1',
  `aid` int(11) DEFAULT NULL,
  `up` int(11) DEFAULT '0',
  `name` varchar(255) DEFAULT NULL,
  `portrait` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `content` text,
  `time` bigint(20) DEFAULT NULL,
  `status` int(1) DEFAULT NULL,
  `star` int(1) DEFAULT '0',
  `reply` text,
  `replytime` bigint(20) DEFAULT NULL,
  `ip` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of s_guestbook
-- ----------------------------
INSERT INTO `s_guestbook` VALUES ('1', '1', '0', '0', '熊海', '127', '86226999@qq.com', 'http://www.imxh.cn', '这是一条测试留言，您可以在后台-互动管理-留言列表 将此条留言删除。', '1621318509', '1', '1', '这是一条回复。', '1621318535', '192.168.1.242');

-- ----------------------------
-- Table structure for s_link
-- ----------------------------
DROP TABLE IF EXISTS `s_link`;
CREATE TABLE `s_link` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `status` int(1) DEFAULT NULL,
  `time` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of s_link
-- ----------------------------
INSERT INTO `s_link` VALUES ('1', '熊海博客', '/static/portrait/10.jpg', 'http://www.imxh.cn', '1', '1620181648');

-- ----------------------------
-- Table structure for s_manage
-- ----------------------------
DROP TABLE IF EXISTS `s_manage`;
CREATE TABLE `s_manage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(255) DEFAULT NULL,
  `class` int(1) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `img` varchar(255) DEFAULT NULL,
  `time` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of s_manage
-- ----------------------------
INSERT INTO `s_manage` VALUES ('1', 'admin', '1', 'admin', 'e10adc3949ba59abbe56e057f20f883e', '/storage/images/user.jpg', '1620181648');

-- ----------------------------
-- Table structure for s_material
-- ----------------------------
DROP TABLE IF EXISTS `s_material`;
CREATE TABLE `s_material` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) DEFAULT NULL,
  `img` varchar(255) DEFAULT NULL,
  `status` int(1) DEFAULT NULL,
  `time` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=132 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of s_material
-- ----------------------------
INSERT INTO `s_material` VALUES ('122', '2', '/storage/uploads/material/20210518/9998847ee60b98621f02ed69148b43bc.jpg', '1', '1621318654');
INSERT INTO `s_material` VALUES ('123', '2', '/storage/uploads/material/20210518/e4f7069ec25ca3228ab198353a35f4aa.jpg', '1', '1621318659');
INSERT INTO `s_material` VALUES ('124', '2', '/storage/uploads/material/20210518/1c92985940c953553c05ec9c90cff787.jpg', '1', '1621318664');
INSERT INTO `s_material` VALUES ('125', '2', '/storage/uploads/material/20210518/d5ab2f8f3a0f871faa510be08708b740.jpg', '1', '1621318669');
INSERT INTO `s_material` VALUES ('126', '2', '/storage/uploads/material/20210518/5e09d7cb83a59d988f79ced56ef58c9e.jpg', '1', '1621318675');
INSERT INTO `s_material` VALUES ('127', '1', '/storage/uploads/material/20210518/379865273d9c62373692f4caff1f8d60.jpg', '1', '1621318704');
INSERT INTO `s_material` VALUES ('128', '1', '/storage/uploads/material/20210518/3599e6650e753850e4d2712f1a9143ca.jpg', '1', '1621318708');
INSERT INTO `s_material` VALUES ('129', '1', '/storage/uploads/material/20210518/c1d8f3de0a2977240fe3cd6c145386b4.jpg', '1', '1621318714');
INSERT INTO `s_material` VALUES ('130', '1', '/storage/uploads/material/20210518/6e85fc334ac96e95d6652ceeadd90675.jpg', '1', '1621318720');
INSERT INTO `s_material` VALUES ('131', '1', '/storage/uploads/material/20210518/f5cd9df1ee49f7469f1abb92680a687c.jpg', '1', '1621318726');

-- ----------------------------
-- Table structure for s_menu
-- ----------------------------
DROP TABLE IF EXISTS `s_menu`;
CREATE TABLE `s_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `pinyin` varchar(255) DEFAULT NULL,
  `type` int(11) DEFAULT NULL,
  `sort` int(11) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `view` int(1) DEFAULT NULL,
  `content` longtext,
  `time` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of s_menu
-- ----------------------------
INSERT INTO `s_menu` VALUES ('1', '首页', 'index', '1', '1', '1', '1', null, null);
INSERT INTO `s_menu` VALUES ('2', '关于', 'about', '2', '2', '1', '1', '<div class=\"about\"><div class=\"title\">请在后台&nbsp;菜单管理 -&nbsp;菜单列表 -&nbsp;关于&nbsp;修改这里的内容</div></div>', null);
INSERT INTO `s_menu` VALUES ('3', '分类', 'archives', '3', '3', '1', '1', null, null);
INSERT INTO `s_menu` VALUES ('4', '相册', 'album', '4', '5', '1', '1', null, null);
INSERT INTO `s_menu` VALUES ('5', '留言', 'guestbook', '5', '6', '1', '1', null, null);
INSERT INTO `s_menu` VALUES ('6', '邻居', 'link', '6', '4', '0', '0', null, null);

-- ----------------------------
-- Table structure for s_photo
-- ----------------------------
DROP TABLE IF EXISTS `s_photo`;
CREATE TABLE `s_photo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class` int(11) DEFAULT NULL,
  `img` varchar(255) DEFAULT NULL,
  `explain` varchar(255) DEFAULT NULL,
  `time` bigint(20) DEFAULT NULL,
  `status` int(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of s_photo
-- ----------------------------
INSERT INTO `s_photo` VALUES ('1', '1', '/storage/uploads/photos/20210518/9091a1edf6929392f87b25b5e4bd69ee.jpg', '您可以在图片列表修改这条介绍', '1621319015', '1');
INSERT INTO `s_photo` VALUES ('2', '1', '/storage/uploads/photos/20210518/140824192d56b4ed61ab8c1b6588d143.jpg', '您可以在图片列表修改这条介绍', '1621319021', '1');
INSERT INTO `s_photo` VALUES ('3', '1', '/storage/uploads/photos/20210518/08493acfbf08f13f6edc78c3744cb72c.jpg', '您可以在图片列表修改这条介绍', '1621319027', '1');
INSERT INTO `s_photo` VALUES ('4', '1', '/storage/uploads/photos/20210518/a14ecf52fb25f5fede457d550dc28a2a.jpg', '您可以在图片列表修改这条介绍', '1621319032', '1');
INSERT INTO `s_photo` VALUES ('5', '1', '/storage/uploads/photos/20210518/6ad5f4e5a9b70a4e31974c88128d25b0.jpg', '您可以在图片列表修改这条介绍', '1621319037', '1');
INSERT INTO `s_photo` VALUES ('6', '1', '/storage/uploads/photos/20210518/59c0d348cce45e87d325a318606bb970.jpg', '您可以在图片列表修改这条介绍', '1621319044', '1');
INSERT INTO `s_photo` VALUES ('7', '1', '/storage/uploads/photos/20210518/f8a3be5a5ad6a62d600c9c8e7eb17340.jpg', '您可以在图片列表修改这条介绍', '1621319049', '1');

-- ----------------------------
-- Table structure for s_theme
-- ----------------------------
DROP TABLE IF EXISTS `s_theme`;
CREATE TABLE `s_theme` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `route` varchar(255) DEFAULT NULL,
  `status` int(1) DEFAULT NULL,
  `time` bigint(20) DEFAULT NULL,
  `author` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `menu` varchar(255) DEFAULT NULL,
  `images` varchar(255) DEFAULT NULL,
  `explain` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of s_theme
-- ----------------------------
INSERT INTO `s_theme` VALUES ('16', '标准风格', 'default', '1', '1621260360', '熊海', 'http://www.imxh.cn', '1,2,3,4,5', 'theme.jpg', '这是熊海博客的默认风格');
