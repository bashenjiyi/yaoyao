<?php

$activityFile = IA_ROOT . "/addons/we7_coupon/model/activity.mod.php";
if (file_exists($activityFile)) {
	require_once $activityFile;
}

global $_W, $_GPC;
$uniacid = $_W['uniacid'];
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
load()->func('tpl');

 // 微信授权登陆
checkauth();

// 会员信息
$uid = $_W['member']['uid'];
$member = pdo_fetch("SELECT * FROM ".tablename('mc_members')." WHERE uid = :uid" , array(':uid' => $uid));

$id = intval($_GPC['id']);
$info = pdo_fetch("SELECT a.* FROM ". tablename('lh_yaoyao')." a WHERE id = :id", array(":id"=>$id));
 
$memberInfo = pdo_fetch("SELECT a.* FROM ". tablename('lh_yaoyao_member')." a WHERE yaoyaoid = :id and uid = :uid", array(":id"=>$id, ':uid'=>$uid));
if ($memberInfo == NULL) {
	$data = array(
		'uniacid' => $uniacid,
		'uid' => $uid,
		'yaoyaoid' => $id,
		'playCount' => $info['playCount'],
		'playPerCount' => $info['playPerCount'],
		'shareCount' => 0,
		'createtime' => TIMESTAMP
	);
	pdo_insert('lh_yaoyao_member', $data);
}

if ($op == 'display') {
	$advList = pdo_fetchall("SELECT a.* FROM ". tablename('lh_yaoyao_adv')." a WHERE yaoyaoid = :id order by a.seq ASC", array(":id"=>$id));

	include $this->template('index');
}