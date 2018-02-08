<?php

$activityFile = IA_ROOT . "/addons/we7_coupon/model/activity.mod.php";
if (file_exists($activityFile)) {
	require_once $activityFile;
}

global $_W, $_GPC;
$uniacid = $_W['uniacid'];
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
load()->func('tpl');

if ($op == 'display') {
    $pindex = max(1, intval($_GPC['page']));
    $psize =15;
    $status = $_GPC['status'];
    $params = array(
    	':uniacid' => $uniacid
    );
    $condition = " where uniacid= :uniacid ";

    $list = pdo_fetchall("SELECT a.*, (select IFNULL(sum(ffmoney), 0) from ". tablename('lh_yaoyao_record')." b where b.yaoyaoid = a.id and b.status = 1) as ffmoney FROM ". tablename('lh_yaoyao')." a {$condition} ORDER BY updatetime DESC LIMIT ".($pindex - 1) * $psize.',
'.$psize, $params);
    $total = pdo_fetchcolumn('SELECT count(*)  FROM ' . tablename('lh_yaoyao'). $condition , $params);
    $pager = pagination($total, $pindex, $psize);
    
	include $this->template('web/index');
} elseif ($op == 'add') { // 添加
	$currentDate = date('Y-m-d H:i');
	$item = array(
		'startDate' => $currentDate,
		'endDate' => $currentDate,
		'share_title' => '语音红包摇摇乐',
		'share_content' => '给您拜年啦，听祝福，抢红包，大礼响不停！',
		'minmoney' => 1,
		'maxmoney' => 1,
		'toprate' => 10,
		'nextrate' => 10,
		'percount' => 1,
		'areaFlag'=>0,
		'areaText'=>'只限制在XXX访问',
		'frontAdvFlag'=>0
	);
	
	$coupons = pdo_getall('coupon', array('uniacid' => $_W['uniacid'], 'status' => '3', 'is_display' => '1', 'quantity >' => '0'));
	foreach ($coupons as $key => &$coupon) {
		$coupon = we7_coupon_activity_coupon_info($coupon['id']);
		if (strtotime(date('Y-m-d')) < strtotime(str_replace('.', '-', $coupon['date_info']['time_limit_start'])) || strtotime(date('Y-m-d')) > strtotime(str_replace('.', '-', $coupon['date_info']['time_limit_end']))) {
			if ($coupon['date_info']['time_type'] == 1) {
				unset($coupons[$key]);
			}
		}
		$coupon['extra'] = iunserializer($coupon['extra']);
	}
	unset($coupon);
    
    include $this->template('web/add');
} elseif ($op == 'edit') { // 添加
    $id = intval($_GPC['id']);
	$item = pdo_fetch("SELECT * FROM ".tablename('lh_yaoyao')." WHERE id = :id" , array(':id' => $id));
	if (empty($item)) {
		message('抱歉，记录不存在或是已经删除！', '', 'error');
	}
	
	if (!empty($item['rid'])) {
		$ruleInfo = pdo_fetch("SELECT * FROM ".tablename('rule_keyword')." WHERE rid = :rid LIMIT 1" , array(':rid' => $item['rid']));
		$replyInfo = pdo_fetch("SELECT * FROM ".tablename('cover_reply')." WHERE rid = :rid LIMIT 1" , array(':rid' => $item['rid']));
	}
	
	$coupons = pdo_getall('coupon', array('uniacid' => $_W['uniacid'], 'status' => '3', 'is_display' => '1', 'quantity >' => '0'));
	foreach ($coupons as $key => &$coupon) {
		$coupon = we7_coupon_activity_coupon_info($coupon['id']);
		if (strtotime(date('Y-m-d')) < strtotime(str_replace('.', '-', $coupon['date_info']['time_limit_start'])) || strtotime(date('Y-m-d')) > strtotime(str_replace('.', '-', $coupon['date_info']['time_limit_end']))) {
			if ($coupon['date_info']['time_type'] == 1) {
				unset($coupons[$key]);
			}
		}
		$coupon['extra'] = iunserializer($coupon['extra']);
	}
	unset($coupon);
	
	if ($item['couponFlag'] == '1' && !empty($item['couponId'])) {
		$couponInfo = we7_coupon_activity_coupon_info($item['couponId']);
	}
	
	include $this->template('web/edit');
} elseif ($op == 'save') { // 保存
	$id = intval($_GPC['id']);
	$replyKeyword = $_GPC['replyKeyword'];
	$replyTitle= $_GPC['replyTitle'];
	$replyThumb = $_GPC['replyThumb'];
	$replySummary = $_GPC['replySummary'];
	
	$couponFlag = $_GPC['couponFlag'];
	$couponId = $_GPC['couponId'];
	$couponRate = $_GPC['couponRate'];
	
	$sendType = $_GPC['sendType'];
	$hbRate = $_GPC['hbRate'];
	$playPerCount = $_GPC['playPerCount'];
	
	$areaFlag = $_GPC['areaFlag'];
	$provinceName = $_GPC['provinceName'];
	$cityName = $_GPC['cityName'];
	$areaText = $_GPC['areaText'];
	
	$frontAdvFlag = $_GPC['frontAdvFlag'];
	$frontAdv = $_GPC['frontAdv'];
	
	$rid = 0;
	$item = pdo_fetch("SELECT * FROM ".tablename('lh_yaoyao')." WHERE id = :id" , array(':id' => $id));
	if (!empty($item)) {
		$rid = $item['rid'];
	}
	
	if (!empty($replyKeyword)) {
		if (empty($rid)) {
			$rule = array(
				'uniacid' => $uniacid,
				'name' => '语音红包',
				'module' => 'cover', 
				'status' => 1,
				'displayorder' => 0
			);
			$result = pdo_insert('rule', $rule);
			$rid = pdo_insertid();
		}
		
		// 更新，添加，删除关键字
		$sql = 'DELETE FROM '. tablename('rule_keyword') . ' WHERE `rid`=:rid AND `uniacid`=:uniacid';
		$pars = array();
		$pars[':rid'] = $rid;
		$pars[':uniacid'] = $_W['uniacid'];
		pdo_query($sql, $pars);
	
		$row = array(
			'rid' => $rid,
			'uniacid' => $uniacid,
			'module' => 'cover',
			'content' => $replyKeyword,
			'type' => 1,
			'displayorder' => 0,
			'status' => 1,
		);
		pdo_insert('rule_keyword', $row);
	}
	
	$data = array(
        'startDate'=>$_GPC['startDate'],
        'endDate'=>$_GPC['endDate'],
        'title'=>$_GPC['title'],
        'nick_name'=>$_GPC['nick_name'],
        'logo'=>$_GPC['logo'],
        'introimageurl'=>$_GPC['introimageurl'],
        'ruleimageurl'=>$_GPC['ruleimageurl'],
        'playimageurl'=>$_GPC['playimageurl'],
        'pyimageurl'=>$_GPC['pyimageurl'],
        'word'=>$_GPC['word'],
        'act_name'=>$_GPC['act_name'],
        'remark'=>$_GPC['remark'],
        'wishing'=>$_GPC['wishing'],
        'minmoney'=>$_GPC['minmoney'],
        'maxmoney'=>$_GPC['maxmoney'],
        'send_name'=>$_GPC['nick_name'],
        'nextrate'=>$_GPC['nextrate'],
        'percount'=>$_GPC['percount'],
        'description'=>$_GPC['description'],
        'gzurl'=>$_GPC['gzurl'],
        'shareimageurl'=>$_GPC['shareimageurl'],
        'share_title'=>$_GPC['share_title'],
        'share_content'=>$_GPC['share_content'],
        'updatetime'=>TIMESTAMP,
        'rid'=>$rid,
        'couponFlag'=>$couponFlag,
        'couponId'=>$couponId,
        'couponRate'=>$couponRate,
        'sendType'=>$sendType,
        'hbRate'=>$hbRate,
        'playPerCount'=>$playPerCount,
        'shareflag' => $_GPC['shareflag'],
        'areaFlag' => $areaFlag,
        'provinceName' => $provinceName,
        'cityName' => $cityName,
        'areaText' => $areaText,
        'frontAdvFlag' => $frontAdvFlag,
        'frontAdv' => $frontAdv
    );
    
    // 添加活动
    if ($id == 0) {
    	$data['uniacid'] = $uniacid;
    	$data['totalmoney'] = $_GPC['totalmoney'];
    	$data['remainmoney'] = $_GPC['totalmoney'];
    	$data['toprate'] = $_GPC['toprate'];
    	$data['views'] = 0;
    	$data['sharecount'] = 0;
    	$data['createtime'] = TIMESTAMP;
    	
		pdo_insert('lh_yaoyao', $data);
		$id = pdo_insertid();
	} else {
		pdo_update('lh_yaoyao', $data, array('id'=>$id));
	}
	
	// 添加关键字回复
	$sql = "SELECT * FROM " . tablename('cover_reply') . ' WHERE `rid` = :rid';
	$pars = array();
	$pars[':rid'] = $rid;
	$cover = pdo_fetch($sql, $pars);
	
	$entry = array(
		'uniacid' => $uniacid,
		'multiid' => 0,
		'rid' => $rid,
		'title' => $replyTitle,
		'description' => $replySummary,
		'thumb' => $replyThumb,
		'url' => murl('entry', array('m' => 'lh_yaoyao', 'do' => 'index', 'id' => $id), true, false),
		'do' => '',
		'module' => 'lh_yaoyao',
	);
	
	if (empty($cover)) {
		pdo_insert('cover_reply', $entry);
	} else {
		pdo_update('cover_reply', $entry, array('id' => $cover['id']));
	}
        
    message('更新成功！', $this->createWebUrl('yaoyao', array('op' => 'display','page'=>$pindex)), 'success');
} elseif ($op=='delete') {	// 删除
    $id = intval($_GPC['id']);
    $row = pdo_fetch("SELECT id FROM ".tablename('lh_yaoyao')." WHERE id = :id", array(':id' => $id));
    if (empty($row)) {
        message('抱歉，名片记录不存在或是已经被删除！');
    }
    pdo_delete('lh_yaoyao', array('id' => $id));
    pdo_delete('lh_yaoyao_member', array('yaoyaoid' => $id));
    pdo_delete('lh_yaoyao_money_record', array('yaoyaoid' => $id));
    pdo_delete('lh_yaoyao_record', array('yaoyaoid' => $id));
    pdo_delete('lh_yaoyao_adv', array('yaoyaoid' => $id));
    pdo_delete('lh_yaoyao_jiangli', array('yaoyaoid' => $id));
    message('删除成功！', referer(), 'success');
}