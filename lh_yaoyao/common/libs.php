<?php
	
/* 
 * 奖品概率计算
 */ 
function get_rand($proArr) {   
    $result = array();
    foreach ($proArr as $key => $val) { 
        $arr[$key] = $val['v']; 
    } 
    // 概率数组的总概率  
    $proSum = array_sum($arr);        
    asort($arr);
    // 概率数组循环   
    foreach ($arr as $k => $v) {   
        $randNum = mt_rand(1, $proSum);   
        if ($randNum <= $v) {   
            $result = $proArr[$k];   
            break;   
        } else {   
            $proSum -= $v;   
        }         
    }     
    return $result;   
}   

function coupon_rand($prizeRate) {
	$arr = array(   
	    array('id'=>1,'name'=>'中奖','v'=>$prizeRate),
	    array('id'=>0,'name'=>'未中奖','v'=>100 - $prizeRate)
	);   

	$result = get_rand($arr);
	return $result['id'];
}

?>