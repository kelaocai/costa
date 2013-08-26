<?php
class promotion extends spController {

	function portal() {

		$_SESSION['account_id'] = trim($this -> spArgs('account_id'));
		$_SESSION['invite_code'] = trim($this -> spArgs('invite_code'));
		$account_id = $_SESSION['account_id'];
		$invite_code = $_SESSION['invite_code'];
		$db = spDB("DDZ_ACCOUNT");
		$ttl_valid = $db -> findCount("REG_SPM = 'i." . $account_id . "' AND REBATE_TRADE_COUNT>0");
		$ttl_unconfirmed = $db -> findCount("REG_SPM = 'i." . $account_id . "' AND REBATE_TRADE_COUNT=0");
		$this -> ttl_valid = $ttl_valid * 3;
		$this -> ttl_unconfirmed = $ttl_unconfirmed * 3;
		$this -> invite_url = "http://diandianzhe.com/s/" . $invite_code;
	}

	function send_msg() {
		echo "send msg";
	}

	function add_share() {
		$db = spDB("DDZ_SHARE");
		ini_set('date.timezone','Asia/Shanghai');
		$now = date("Y-m-d H:i:s", time());
		$new_share = array('ACCOUNT_ID' => $_SESSION['account_id'] , 'WEB_ID' => $this -> spArgs('webid'), 'GMT_CREATE' => $now);
		$db -> create($new_share);
	}

	function detail() {
		$account_id = $_SESSION['account_id'];
		$db = spDB("DDZ_ACCOUNT");
		$rs = $db -> spPager($this -> spArgs('page'), 10) -> findAll("REG_SPM = 'i." . $account_id . "'", 'GMT_CREATE DESC');
		$pager = $db -> spPager() -> getPager();
		$this -> pager = $pager;
		$this -> ctrl = "promotion";
		$this -> action = "detail";
		$this -> rs = $rs;
	}

}
