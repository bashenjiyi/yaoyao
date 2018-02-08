<?php

global $_W, $_GPC;
$uniacid = $_W['uniacid'];
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
load()->func('tpl');

if ($op == 'display') {
	$id = intval($_GPC['id']);
    $pindex = max(1, intval($_GPC['page']));
    $psize =15;
    $status = $_GPC['status'];
    $params = array(
    	':uniacid' => $uniacid,
    	':id' => $id
    );
    $condition = " where a.uniacid= :uniacid and a.yaoyaoid = :id and a.status >= 0";

    $list = pdo_fetchall("SELECT a.*, b.nickname, b.openid FROM ". tablename('lh_yaoyao_record')." a inner join ". tablename('mc_mapping_fans')." b on a.uid = b.uid {$condition} ORDER BY a.id DESC LIMIT ".($pindex - 1) * $psize.',
'.$psize, $params);
    $total = pdo_fetchcolumn('SELECT count(*)  FROM ' . tablename('lh_yaoyao_record'). " a " . $condition, $params);
    $pager = pagination($total, $pindex, $psize);
    
	include $this->template('web/report');
} 