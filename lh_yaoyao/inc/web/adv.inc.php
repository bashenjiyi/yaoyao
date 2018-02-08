<?php


global $_W, $_GPC;
$uniacid = $_W['uniacid'];
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
load()->func('tpl');

$yaoyaoid = intval($_GPC['yaoyaoid']);

if ($op == 'display') {
    $pindex = max(1, intval($_GPC['page']));
    $psize =15;
    $params = array(
    	':yaoyaoid' => $yaoyaoid
    );
    $condition = " where yaoyaoid = :yaoyaoid";

    $list = pdo_fetchall("SELECT a.* FROM ". tablename('lh_yaoyao_adv')." a {$condition} ORDER BY seq ASC LIMIT ".($pindex - 1) * $psize.',
'.$psize, $params);
    $total = pdo_fetchcolumn('SELECT count(*)  FROM ' . tablename('lh_yaoyao_adv'). $condition , $params);
    $pager = pagination($total, $pindex, $psize);
    
	include $this->template('web/adv/index');
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
		'areaText'=>'只限制在XXX访问'
	);
	
    include $this->template('web/adv/add');
} elseif ($op == 'edit') { // 添加
    $id = intval($_GPC['id']);
	$item = pdo_fetch("SELECT * FROM ".tablename('lh_yaoyao_adv')." WHERE id = :id" , array(':id' => $id));
	if (empty($item)) {
		message('抱歉，记录不存在或是已经删除！', '', 'error');
	}
	
	include $this->template('web/adv/edit');
} elseif ($op == 'save') { // 保存
	$id = intval($_GPC['id']);
	$name = $_GPC['name'];
	$imageurl = $_GPC['imageurl'];
	$seq = $_GPC['seq'];
	$yaoyaoid = $_GPC['yaoyaoid'];

	$item = pdo_fetch("SELECT * FROM ".tablename('lh_yaoyao_adv')." WHERE id = :id" , array(':id' => $id));
	if (!empty($item)) {
		$yaoyaoid = $item['yaoyaoid'];
	}
	
	$data = array(
        'imageurl'=>$imageurl,
        'name'=>$name,
        'seq'=>$seq
    );
    
    // 添加活动
    if ($id == 0) {
    	$data['uniacid'] = $uniacid;
    	$data['yaoyaoid'] = $yaoyaoid;
    	$data['createtime'] = TIMESTAMP;
    	
		pdo_insert('lh_yaoyao_adv', $data);
	} else {
		pdo_update('lh_yaoyao_adv', $data, array('id'=>$id));
	}
        
    message('更新成功！', $this->createWebUrl('adv', array('op' => 'display', 'yaoyaoid' => $yaoyaoid, 'page'=>$pindex)), 'success');
} elseif ($op=='delete') {	// 删除
    $id = intval($_GPC['id']);
    $row = pdo_fetch("SELECT id FROM ".tablename('lh_yaoyao_adv')." WHERE id = :id", array(':id' => $id));
    if (empty($row)) {
        message('抱歉，记录不存在或是已经被删除！');
    }
    pdo_delete('lh_yaoyao_adv', array('id' => $id));
    message('删除成功！', referer(), 'success');
}