<?php

global $_W, $_GPC;
$uniacid = $_W['uniacid'];
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
load()->func('tpl');

load()->model('activity');
//activity_coupon_grant('1', '1');

if ($op == 'display') {
    $item = pdo_fetch('SELECT *  FROM ' . tablename('lh_yaoyao_setting'). " where uniacid = :uniacid", array("uniacid"=>$uniacid));
    if ($item == NULL) {
    	$item = array(
    		'serviceProviderFlag' => '0',
    		'sendMode' => '0',
    		'consumeFlag' => '0'
    	);
    }
    $serviceList = pdo_fetchall('SELECT a.*, b.name  FROM ' . tablename('lh_yaoyao_setting'). " a inner join " . tablename('account_wechats') . " b on a.uniacid = b.uniacid where a.serviceProviderFlag = 1 order by updatetime DESC");
    
	include $this->template('web/setting');
} elseif ($op == 'save') { // 保存
	$info = pdo_fetch('SELECT *  FROM ' . tablename('lh_yaoyao_setting'). " where uniacid = :uniacid", array("uniacid"=>$uniacid));
    
    $serviceProviderFlag = $_GPC['serviceProviderFlag'];
    $sendMode = $_GPC['sendMode'];
    $serviceProviderId = $_GPC['serviceProviderId'];
    $mchId = $_GPC['mchId'];
    $password = $_GPC['password'];
    $consumeFlag = $_GPC['consumeFlag'];
    $apiCert = $_GPC['apiCert'];
    $apiKey = $_GPC['apiKey'];
    $apiRoot = $_GPC['apiRoot'];
    
    if ($serviceProviderFlag == "0") { // 身份：普通商户
    	if ($sendMode == '0') { // 普通商户
    		if (empty($mchId)) {
    			message("商户号不能为空", referer(), 'error');
    		}
    		if (empty($password)) {
    			message("商户秘钥不能为空", referer(), 'error');
    		}
    		if (empty($apiCert)) {
    			message("CERT证书文件不能为空", referer(), 'error');
    		}
    		if (empty($apiKey)) {
    			message("支付证书密钥文件不能为空", referer(), 'error');
    		}
    		if (empty($apiRoot)) {
    			message("支付根证书文件不能为空", referer(), 'error');
    		}
    	} else { // 服务商
    		if (empty($serviceProviderId)) {
    			message("请选择服务商", referer(), 'error');
    		}
    		if (empty($mchId)) {
    			message("商户号不能为空", referer(), 'error');
    		}
    	}
    } else { // 身份：服务商
    	if (empty($mchId)) {
			message("商户号不能为空", referer(), 'error');
		}
		if (empty($password)) {
			message("商户秘钥不能为空", referer(), 'error');
		}
		if (empty($apiCert)) {
			message("CERT证书文件不能为空", referer(), 'error');
		}
		if (empty($apiKey)) {
			message("支付证书密钥文件不能为空", referer(), 'error');
		}
		if (empty($apiRoot)) {
			message("支付根证书文件不能为空", referer(), 'error');
		}
		$sendMode = "0";
    }
    $data = array(
    	'serviceProviderFlag'=>$serviceProviderFlag,
    	'sendMode'=>$sendMode,
    	'serviceProviderId'=>$serviceProviderId,
        'mchId'=>$mchId,
        'password'=>$password,
        'consumeFlag'=>$consumeFlag,
        'apiCert'=>$apiCert,
        'apiKey'=>$apiKey,
        'apiRoot'=>$apiRoot,
        'updatetime'=>TIMESTAMP
    );
    
    if ($info == null) {
    	$data['uniacid'] = $uniacid;
    	$data['createtime'] = TIMESTAMP;
    	
		pdo_insert('lh_yaoyao_setting', $data);
	} else {
		pdo_update('lh_yaoyao_setting', $data, array('id'=>$info['id']));
	}
        
    message('更新成功！', $this->createWebUrl('setting', array('op' => 'display')), 'success');
}