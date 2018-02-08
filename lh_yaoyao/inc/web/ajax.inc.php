<?php

global $_W, $_GPC;
$uniacid = $_W['uniacid'];
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
load()->func('tpl');

if ($op == 'saveMoney') { // 修改金额
	$result = array();
	$id = intval($_GPC['id']);
	$price = intval($_GPC['price']);
	
	$item = pdo_fetch("SELECT * FROM ".tablename('lh_yaoyao')." WHERE id = :id" , array(':id' => $id));
  	if ($item == null) {
		$result['code'] = 1;
		$result['message'] = '活动不存在';
		echo json_encode($result);
		return;
  	}
  	
  	$ret = pdo_query("update " . tablename('lh_yaoyao') . " set totalmoney = totalmoney + :price where id = :id", array(':id'=>$id, ':price'=>$price));
  	if ($ret) {
  		// 添加金额修改记录
  		$data = array();
  		$data['uniacid'] = $uniacid;
    	$data['yaoyaoid'] = $id;
    	$data['money'] = $price;
    	$data['createtime'] = TIMESTAMP;
    	$data['frontMoney'] = $item['totalmoney'];
    	$data['afterMoney'] = $data['frontMoney'] + $price;
    	
		pdo_insert('lh_yaoyao_money_record', $data);
  	}
  	
  	$result['code'] = 0;
  	echo json_encode($result);
}