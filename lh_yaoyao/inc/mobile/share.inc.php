<?php

global $_W, $_GPC;
$uniacid = $_W['uniacid'];
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
load()->func('tpl');

 // 微信授权登陆
checkauth();

$userid = $_W['member']['uid'];

// 会员信息
$touserid = intval($_GPC['uid']);
$id = intval($_GPC['id']);
$serverId = $_GPC['serverId'];

if ($touserid != $userid) {
	$jiangliInfo = pdo_fetch("SELECT * FROM ".tablename('lh_yaoyao_jiangli')." WHERE yaoyaoid = :yaoyaoid and userid = :userid and touserid = :touserid" , array(':yaoyaoid' => $id, ':userid'=>$userid, 'touserid'=>$touserid));
	if ($jiangliInfo == NULL) {
		$memberInfo = pdo_fetch("SELECT a.* FROM ". tablename('lh_yaoyao_member')." a WHERE yaoyaoid = :id and uid = :uid", array(":id"=>$id, ':uid'=>$touserid));
		if ($memberInfo != NULL) {
			// 保存分享记录
			$data = array();
			$data["uniacid"] = $uniacid;
			$data["yaoyaoid"] = $id;
			$data["createtime"] = time();
			$data["ffmoney"] = "";
			$data["userid"] = $userid;
			$data["touserid"] = $touserid;
			pdo_insert('lh_yaoyao_jiangli', $data);
			
			// 更新分享次数
			pdo_query("update ". tablename('lh_yaoyao_member'). "set shareCount = shareCount + 1 where id =:id", array(":id"=>$memberInfo['id']));
		}
	}
}

header('location: '. $this->createMobileUrl('index', array('id'=>$id)));