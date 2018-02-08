<?php

if(!pdo_fieldexists('lh_yaoyao', 'frontAdvFlag')) {
    pdo_query("ALTER TABLE ".tablename('lh_yaoyao')." ADD COLUMN frontAdvFlag char(1) default '0' comment '是否开启首屏广告';");
}

if(!pdo_fieldexists('lh_yaoyao', 'frontAdv')) {
    pdo_query("ALTER TABLE ".tablename('lh_yaoyao')." ADD COLUMN frontAdv varchar(500) comment '首屏广告';");
}

if(!pdo_fieldexists('lh_yaoyao', 'areaFlag')) {
    pdo_query("ALTER TABLE ".tablename('lh_yaoyao')." ADD COLUMN areaFlag char(1) default '0' comment '区域限制 0-不限制 1-限制';");
}

if(!pdo_fieldexists('lh_yaoyao', 'provinceName')) {
    pdo_query("ALTER TABLE ".tablename('lh_yaoyao')." ADD COLUMN provinceName varchar(32) comment '省份';");
}

if(!pdo_fieldexists('lh_yaoyao', 'cityName')) {
    pdo_query("ALTER TABLE ".tablename('lh_yaoyao')." ADD COLUMN cityName varchar(32) comment '城市';");
}

if(!pdo_fieldexists('lh_yaoyao', 'areaText')) {
    pdo_query("ALTER TABLE ".tablename('lh_yaoyao')." ADD COLUMN areaText varchar(64) comment '区域限制文本';");
}

if(!pdo_fieldexists('lh_yaoyao_setting', 'serviceProviderFlag')) {
    pdo_query("ALTER TABLE ".tablename('lh_yaoyao_setting')." ADD COLUMN serviceProviderFlag char(1) default '0' comment '是否服务商 0-否 1-是';");
}

if(!pdo_fieldexists('lh_yaoyao_setting', 'serviceProviderId')) {
    pdo_query("ALTER TABLE ".tablename('lh_yaoyao_setting')." ADD COLUMN serviceProviderId int default 0 comment '服务商ID';");
}

if(!pdo_fieldexists('lh_yaoyao_setting', 'sendMode')) {
    pdo_query("ALTER TABLE ".tablename('lh_yaoyao_setting')." ADD COLUMN sendMode char(1) default '0' comment '发送类型 0-普通商户模式 1-服务商模式';");
}

if(!pdo_fieldexists('lh_yaoyao_setting', 'consumeFlag')) {
    pdo_query("ALTER TABLE ".tablename('lh_yaoyao_setting')." ADD COLUMN consumeFlag char(1) default '0' comment '扣钱模式 0-默认扣商户 1-扣服务商';");
}

if(!pdo_fieldexists('lh_yaoyao_jiangli', 'touserid')) {
    pdo_query("ALTER TABLE ".tablename('lh_yaoyao_jiangli')." ADD COLUMN touserid int(11);");
}

if(!pdo_fieldexists('lh_yaoyao', 'rid')) {
    pdo_query("ALTER TABLE ".tablename('lh_yaoyao')." ADD COLUMN `rid` int(11) COMMENT '规则ID';");
}

if(!pdo_fieldexists('lh_yaoyao', 'sendType')) {
    pdo_query("ALTER TABLE ".tablename('lh_yaoyao')." ADD COLUMN sendType char(1) default '0' comment '红包派送类型 0-按顺序 1-按概率';");
}

if(!pdo_fieldexists('lh_yaoyao', 'playPerCount')) {
    pdo_query("ALTER TABLE ".tablename('lh_yaoyao')." ADD COLUMN playPerCount int default 10 comment '每天可游戏次数';");
}

if(!pdo_fieldexists('lh_yaoyao', 'hbRate')) {
    pdo_query("ALTER TABLE ".tablename('lh_yaoyao')." ADD COLUMN hbRate int(11) default 50 comment '红包派送概率';");
}

if(!pdo_fieldexists('lh_yaoyao_member', 'playPerCount')) {
    pdo_query("ALTER TABLE ".tablename('lh_yaoyao_member')." ADD COLUMN playPerCount int(11) default 1 comment '每人每天游戏次数';");
}

if(!pdo_fieldexists('lh_yaoyao_member', 'shareCount')) {
    pdo_query("ALTER TABLE ".tablename('lh_yaoyao_member')." ADD COLUMN shareCount int(11) default 0 comment '分享游戏次数';");
}

if(!pdo_fieldexists('lh_yaoyao', 'couponFlag')) {
    pdo_query("ALTER TABLE ".tablename('lh_yaoyao')." ADD COLUMN couponFlag char(1) default '0' comment '是否开启未中奖发放卡券';");
}

if(!pdo_fieldexists('lh_yaoyao', 'couponId')) {
    pdo_query("ALTER TABLE ".tablename('lh_yaoyao')." ADD COLUMN couponId int(11) default 0 comment '卡券ID';");
}

if(!pdo_fieldexists('lh_yaoyao', 'couponRate')) {
    pdo_query("ALTER TABLE ".tablename('lh_yaoyao')." ADD COLUMN couponRate int(11) default 0 comment '卡券中奖概率';");
}

$sql ="
CREATE TABLE IF NOT EXISTS ".tablename('lh_yaoyao_money_record')." (
	  `id` int(10) NOT NULL AUTO_INCREMENT,
	  `uniacid` int(11) DEFAULT '0',
	  `yaoyaoid` int(11),
	  `money` int(11),
	  `frontMoney` int(11),
	  `afterMoney` int(11),
	  `createtime` int(11) DEFAULT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='金额修改记录';
";
pdo_run($sql);

$sql ="
CREATE TABLE IF NOT EXISTS ".tablename('lh_yaoyao_member')." (
	  `id` int(10) NOT NULL AUTO_INCREMENT,
	  `uniacid` int(11) DEFAULT '0',
    `uid` int comment '用户ID', 
	  `yaoyaoid` int(11),
	  `playCount` int(11) default 1 comment '游戏次数',
	  playPerCount int(11) default 1 comment '每人每天游戏次数',
	  shareCount int(11) default 0 comment '分享游戏次数',
	  `createtime` int(11) DEFAULT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='摇红包用户表';
";
pdo_run($sql);