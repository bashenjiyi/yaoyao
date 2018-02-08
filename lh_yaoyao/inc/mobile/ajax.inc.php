<?php

$activityFile = IA_ROOT . "/addons/we7_coupon/model/activity.mod.php";
if (file_exists($activityFile)) {
	require_once $activityFile;
}
require_once IA_ROOT . "/addons/lh_yaoyao/common/libs.php";
require_once IA_ROOT . "/addons/lh_yaoyao/common/IpArea.php";

global $_W, $_GPC;
$uniacid = $_W['uniacid'];
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';

 // 微信授权登陆
checkauth();

// 会员信息
$uid = $_W['member']['uid'];

$id = intval($_GPC['id']);
$info = pdo_fetch("SELECT a.* FROM ". tablename('lh_yaoyao')." a WHERE id = :id", array(":id"=>$id));
$memberInfo = pdo_fetch("SELECT a.* FROM ". tablename('lh_yaoyao_member')." a WHERE yaoyaoid = :id", array(":id"=>$id));

if ($op == 'getLottory') { // 获取钱包
	$result = array();
	$result["status"] = 1;
	$result["idx"] = 0;
	
	$currenttime = date("Y-m-d H:i");
	$startDate = $info["startDate"];
	$endDate = $info["endDate"];
	if ($currenttime < $startDate) {
		$result["message"] = "活动尚未正式开始，请稍等！"; 
		echo json_encode($result);
		exit;
	}
	
	if ($currenttime > $endDate) {
		$result["message"] = "活动已结束，请期待下次活动"; 
		echo json_encode($result);
		exit;
	}
	
	if ($info['areaFlag'] == '1') {
		$ipArea = new IpArea();
		$ip = get_client_ip();
		$ipResult = $ipArea->get($ip);
		// 检测是否在区域内
		$isArea = false;
		
		if (!empty($info['provinceName'])) {
			// 若省份带市，则直接检测省份，否则检测城市
			$cityFlag = strpos($info['provinceName'], '市');
			if ($cityFlag === false) { // 检测省份和城市
				$province= str_replace("省", "", $info['provinceName']);
				$pos = strpos($ipResult, $province);
				if ($pos !== false) {
					if (!empty($info['cityName'])) {
						$city= str_replace("市", "", $info['cityName']);
						$city= str_replace("区", "", $city);
						$pos = strpos($ipResult, $city);
						if ($pos !== false) {
							$isArea = true;
						}
					} else { // 城市为空，直接在区域
						$isArea = true;
					}
				} else {
					// 若省份中没有找到，则可能ip地址中没有省份信息，直接检测城市
					if (!empty($info['cityName'])) {
						$city= str_replace("市", "", $info['cityName']);
						$city= str_replace("区", "", $city);
						$pos = strpos($ipResult, $city);
						if ($pos !== false) {
							$isArea = true;
						}
					} else { // 城市为空，直接在区域
						$isArea = true;
					}
				}
			} else { // 只检测省份
				$province= str_replace("市", "", $info['provinceName']);
				$pos = strpos($ipResult, $province);
				if ($pos !== false) {
					$isArea = true;
				}
			}
		} else { // 若省份为空，则直接不限制
			$isArea = true;
		}
		
		if (!$isArea) {
			$tips = "";
			if (!empty($info['provinceName'])) {
				$tips .= $info['provinceName'];
			}
			if (!empty($info['cityName'])) {
				$tips .= $info['cityName'];
			}
			
			$areaText = $info['areaText'];
			if (empty($info['areaText'])) {
				$areaText = "只限制在XXX访问";
			}
			$result["message"] = str_replace("XXX", $tips, "<strong>" . $areaText . $ipResult . "</strong>"); 
			echo json_encode($result);
			exit;
		}
	}
	
	$time = strtotime(date('Y-m-d'));
	// 检测是否有可玩次数
	$playCount = pdo_fetchcolumn("SELECT count(*) FROM ". tablename('lh_yaoyao_record')." a WHERE yaoyaoid = :yaoyaoid and uid = :uid and createtime >= :time", array(":yaoyaoid"=>$info['id'], ':uid'=>$uid, ':time'=>$time));
	if ($playCount >= $memberInfo['playPerCount']) {
		if ($memberInfo['shareCount'] > 0) { // 还有分享可玩次数
			// 更新分享数
			pdo_query("update ". tablename('lh_yaoyao_member'). "set shareCount = shareCount - 1 where id =:id", array(":id"=>$memberInfo['id']));
		} else {
			$result["message"] = "<strong>每天可摇红包" . $memberInfo['playPerCount'] . "次</strong>"; 
			if ($info['shareflag'] == '1') {
				$result["message"] = "<strong>每天可摇红包" . $memberInfo['playPerCount'] . "次</strong><br />分享好友额外获取一次机会";	
			}
			
			echo json_encode($result);
			exit;
		}
	}
	
	$ret = checkSendStatus($info);
	if ($ret == 'noprize') { // 未中奖
		$result["message"] = "<strong>手滑了没抢到</strong><br />可以接着抢哦！"; 
		
		// 未中奖时，检测是否可中卡券
		if ($info['couponFlag'] == '1') {
			$couponResult = coupon_rand($info['couponRate']);
			if ($couponResult == '1') { // 中卡券
				$grantResult = we7_coupon_activity_coupon_grant($info['couponId'], $_W['openid']);
				if ($grantResult['errno'] == -1) { // 领取失败
//					echo $grantResult['message'];
				} else { // 领取成功
					$result["message"] = "<strong>恭喜您抢到一个优惠券，请进入会员中心查看！~</strong>"; 
				}
			}
		}
		
	} else if ($ret == 'noprize2') { // 由于红包发送失败导致的未中奖，直接返回
		$result["message"] = "<strong>手滑了没抢到</strong><br />可以接着抢哦！";
	} else if ($ret == 'end') { // 红包发完
		$result["message"] = "<strong>红包已经发光了，请等待近期活动哦！~</strong>";
	} else { // 中奖红包
		$result["message"] = "<strong>恭喜你抢到了" . $ret . "元红包</strong>";
	}
	
	echo json_encode($result);
	exit;
}

/**
 * 检测发放红包或卡券
 */
function checkSendStatus ($info) {
	global $_W;
	
	$uniacid = $_W['uniacid'];
	$uid = $_W['member']['uid'];

	// 若用户已中了指定的个数红包，则显示手滑了
	$zjcount = pdo_fetchcolumn("SELECT count(*) FROM ". tablename('lh_yaoyao_record')." a WHERE yaoyaoid = :yaoyaoid and uid = :uid and status = 1", array(":yaoyaoid"=>$info['id'], ':uid'=>$uid));
	if ($zjcount >= $info["percount"]) {
		return "noprize";
	}
	
	// 根据不同的计算方式，计算是否中奖
	$zjflag = false;
	if ($info['sendType'] == '0') { // 按顺序计算
		$count = pdo_fetchcolumn("SELECT count(*) FROM ". tablename('lh_yaoyao_record')." a WHERE yaoyaoid = :yaoyaoid", array(":yaoyaoid"=>$info['id']));
		$count = $count + 1; // 当前楼层
		if ($count <= intval($info["toprate"])) { // 前几名必中
			$zjflag = true;
		} else {
			$diff = ($count - intval($info["toprate"]));
			$nextrate = intval($info["nextrate"]) + 1;
			if ($diff % $nextrate == 0) {
				$zjflag = true;
			}
		}
	} else if ($info['sendType'] == '1') { // 按概率计算
		$couponResult = coupon_rand($info['hbRate']);
		if ($couponResult == '1') { // 中红包 
			$zjflag = true;
		}
	}
	
	if (!$zjflag) { // 未中奖，记录发放失败状态
		// 记录游戏次数
		$data = array();
		$data["uniacid"] = $uniacid;
		$data["uid"] = $uid;
		$data["createtime"] = time();
		$data["ffmoney"] = "";
		$data["yaoyaoid"] = $info["id"];
		$data["msg"] = ""; 
		$data["status"] = 0;
		pdo_insert('lh_yaoyao_record', $data);
		
		return "noprize";
	}
	
	// 首先检测红包是否发完
	$hongbao_money = pdo_fetchcolumn("SELECT IFNULL(sum(ffmoney), 0) as sumffmoney FROM ". tablename('lh_yaoyao_record')." a WHERE yaoyaoid = :yaoyaoid and status = 1", array(":yaoyaoid"=>$info['id']));
	if ($hongbao_money >= $info["totalmoney"]) { // 红包发完
		return "end";
	}
	
	$money = 0;
	// 计算当前可发的红包
	$money = rand($info["minmoney"], $info["maxmoney"]);
	if (($hongbao_money + $money) > $info["totalmoney"]) {
		$money = $hongbao_money + $money - $info["totalmoney"];
	}
	
	// 尝试发送红包
	$settings = pdo_fetch("SELECT a.* FROM ". tablename('lh_yaoyao_setting')." a WHERE uniacid = :uniacid", array(":uniacid"=>$uniacid));
	if ($settings != null) {
		$settings['appId'] = $_W['account']['key'];
	}
	$returnArr = send_cash_bonus($settings, $_W['fans']['openid'], $money, $info);
	if ($returnArr["code"] == 0) { // 中奖
		$data = array();
		$data["uniacid"] = $uniacid;
		$data["uid"] = $uid;
		$data["createtime"] = time();
		$data["ffmoney"] = $money;
		$data["yaoyaoid"] = $info["id"];
		$data["status"] = 1;
		$data["source"] = "";
		pdo_insert('lh_yaoyao_record', $data);
		
		return $money;
	} else {
		// 记录发放失败情况
		$data = array();
		$data["uniacid"] = $uniacid;
		$data["uid"] = $uid;
		$data["createtime"] = time();
		$data["ffmoney"] = $money;
		$data["yaoyaoid"] = $info["id"];
		$data["msg"] = $returnArr["msg"]; 
		$data["status"] = 1;
		pdo_insert('lh_yaoyao_record', $data);
		
		if (strpos($returnArr["msg"], "帐号余额不足")) {
			return "end";
		} else {
			return "noprize2";
		}
	}
}

//现金红包接口
function send_cash_bonus($settings, $fromUser, $amount, $info) {
    $Hour = date('G');

	if ($settings['serviceProviderFlag'] == '0' && $settings['sendMode'] == '1') {
		$serviceSettings = pdo_fetch("SELECT a.* FROM ". tablename('lh_yaoyao_setting')." a WHERE id = :id", array(":id"=>$settings['serviceProviderId']));
		$accountInfo = pdo_fetch("SELECT a.* FROM ". tablename('account_wechats')." a WHERE uniacid = :uniacid", array(":uniacid"=>$serviceSettings['uniacid']));
	}

    $ret = array();
    $ret['code'] = 0;
    $ret['message'] = "success";
    $amount = $amount * 100;
    $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';
    $pars = array();
    $pars['nonce_str'] = random(32);
    $pars['mch_billno'] = random(10) . date('Ymd') . random(3);
    
    if ($settings['serviceProviderFlag'] == '0' && $settings['sendMode'] == '1') {
    	$pars['mch_id'] = $serviceSettings['mchId'];
    	$pars['sub_mch_id'] = $settings['mchId'];
	 	$pars['wxappid'] = $accountInfo['key'];
    	$pars['msgappid'] = $settings['appId'];
    } else {
    	$pars['mch_id'] = $settings['mchId'];
    	$pars['wxappid'] = $settings['appId'];
    }

    $pars['nick_name'] = $info['nick_name'];
    $pars['send_name'] = $info['send_name'];
    $pars['re_openid'] = $fromUser;
    $pars['total_amount'] = $amount;
    $pars['min_value'] = $amount;
    $pars['max_value'] = $amount;
    $pars['total_num'] = 1;
    $pars['wishing'] = $info['wishing'];
    $pars['client_ip'] = get_client_ip();
    $pars['act_name'] = $info['act_name'];
    $pars['remark'] = $info['remark'];
    
    if ($settings['serviceProviderFlag'] == '0' && $settings['sendMode'] == '1') {
    	if ($settings['consumeFlag'] == '0') {
    		$pars['consume_mch_id'] = $settings['mchId'];
    	} else {
    		$pars['consume_mch_id'] = $serviceSettings['mchId'];
    	}
    }
    
    ksort($pars, SORT_STRING);
    $string1 = '';
    foreach ($pars as $k => $v) {
        $string1 .= "{$k}={$v}&";
    }
    
    if ($settings['serviceProviderFlag'] == '0' && $settings['sendMode'] == '1') {
    	$string1 .= "key={$serviceSettings['password']}";
    } else {
    	$string1 .= "key={$settings['password']}";
    }
    
    $pars['sign'] = strtoupper(md5($string1));
    $xml = array2xml($pars);
    $extras = array();
    
    if ($settings['serviceProviderFlag'] == '0' && $settings['sendMode'] == '1') {
	    if (empty($serviceSettings['apiCert']) || empty($serviceSettings['apiKey']) || empty($serviceSettings['apiRoot'])) {
	        $ret['code'] = -1;
	        $ret['msg'] = '未上传完整的微信支付证书，请到【参数设置】->【红包设置】中上传';
	        return $ret;
	    }
	    $certfile = IA_ROOT . "/addons/lh_yaoyao/cert/" . random(128);
	    file_put_contents($certfile, $serviceSettings['apiCert']);
	    $keyfile = IA_ROOT . "/addons/lh_yaoyao/cert/" . random(128);
	    file_put_contents($keyfile, $serviceSettings['apiKey']);
	    $rootfile = IA_ROOT . "/addons/lh_yaoyao/cert/" . random(128);
	    file_put_contents($rootfile, $serviceSettings['apiRoot']);
	    $extras['CURLOPT_SSLCERT'] = $certfile;
	    $extras['CURLOPT_SSLKEY']  = $keyfile;
	    $extras['CURLOPT_CAINFO']  = $rootfile;
    } else {
	    if (empty($settings['apiCert']) || empty($settings['apiKey']) || empty($settings['apiRoot'])) {
	        $ret['code'] = -1;
	        $ret['msg'] = '未上传完整的微信支付证书，请到【参数设置】->【红包设置】中上传';
	        return $ret;
	    }
	    $certfile = IA_ROOT . "/addons/lh_yaoyao/cert/" . random(128);
	    file_put_contents($certfile, $settings['apiCert']);
	    $keyfile = IA_ROOT . "/addons/lh_yaoyao/cert/" . random(128);
	    file_put_contents($keyfile, $settings['apiKey']);
	    $rootfile = IA_ROOT . "/addons/lh_yaoyao/cert/" . random(128);
	    file_put_contents($rootfile, $settings['apiRoot']);
	    $extras['CURLOPT_SSLCERT'] = $certfile;
	    $extras['CURLOPT_SSLKEY']  = $keyfile;
	    $extras['CURLOPT_CAINFO']  = $rootfile;
    }

    load()->func('communication');
    $procResult = null;
    $resp = ihttp_request($url, $xml, $extras);

    @unlink($certfile);
    @unlink($keyfile);
    @unlink($rootfile);
    if(is_error($resp)) {
        $procResult = $resp["message"];
        $ret['code'] = -1;
        $ret['msg'] = $procResult;
        return $ret;
    } else {
        $xml = '<?xml version="1.0" encoding="utf-8"?>' . $resp['content'];
        $dom = new DOMDocument();
        if ($dom->loadXML($xml)) {
            $xpath = new DOMXPath($dom);
            $code = $xpath->evaluate('string(//xml/return_code)');
            $result = $xpath->evaluate('string(//xml/result_code)');
            if (strtolower($code) == 'success' && strtolower($result) == 'success') {
                $ret['code'] = 0;
                $ret['msg'] = "success";
                return $ret;
            } else {
                $error = $xpath->evaluate('string(//xml/err_code_des)');
                $ret['code'] = -2;
                $ret['msg'] = $error;
                return $ret;
            }
        } else {
            $ret['code'] = -3;
            $ret['msg'] = "3error3";
            return $ret;
        }
    }
}

// 获取客户端IP地址
function get_client_ip(){
	// 首先从360加速节点中获取
   if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], "unknown")){
       $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
   }else if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")){
       $ip = getenv("HTTP_CLIENT_IP");
   }else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")){
       $ip = getenv("HTTP_X_FORWARDED_FOR");
   }else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")){
       $ip = getenv("REMOTE_ADDR");
   }else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")){
       $ip = $_SERVER['REMOTE_ADDR'];
   }else{
       $ip = "unknown";
	}
	if (preg_match('#^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$#', $ip)) {
		$ip_array = explode('.', $ip);	
		if($ip_array[0]<=255 && $ip_array[1]<=255 && $ip_array[2]<=255 && $ip_array[3]<=255){
			return $ip;
		}			
	}		
   return "unknown";
}