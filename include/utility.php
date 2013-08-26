<?php

function utf_substr($params) {
	$str = $params['str'];
	$len = $params['len'];
	$ex = $params['ex'];
	for ($i = 0; $i < $len; $i++) {
		$temp_str = substr($str, 0, 1);
		if (ord($temp_str) > 127) {
			$i++;
			if ($i < $len) {
				$new_str[] = substr($str, 0, 3);
				$str = substr($str, 3);
			}
		} else {
			$new_str[] = substr($str, 0, 1);
			$str = substr($str, 1);
		}
	}
	return join($new_str) . $ex;
}

//前台页面推广佣金状态处理
function promotion_status($params) {
	$cnt = $params['cnt'];
	if ($cnt > 0) {
		return '有效 <i class=icon-ok></i>';
	} else {
		return '待确认';
	}

}

//前台页面好友返利笔数处理
function friend_trade($params) {
	$cnt = $params['cnt'];
	if ($cnt > 10) {
		return '大于10笔';
	} else {
		return $cnt;
	}

}

spAddViewFunction('utfSub', 'utf_substr');
spAddViewFunction('promStatus', 'promotion_status');
spAddViewFunction('friendTrade', 'friend_trade');