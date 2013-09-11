<?php
class sqds extends spController {

	function index() {

	}

	function submit() {
		//判断检查DDZ帐号情况
		$uid = 0;
		$spm = '';
		$spm_mark = 'a.sziit';
		$alipay_id = trim($this -> spArgs('alipay_id'));
		$phone_no = trim($this -> spArgs('phone_no'));
		$sn = trim($this -> spArgs('sn'));
		$rs_flag=FALSE;

		if (strcmp('', $alipay_id) == 0) {
			$this -> msg = "请提供参赛支付宝帐号!";
			return;
		} else {
			if (!($this -> checkEmail($alipay_id) || $this -> checkMobile($alipay_id))) {
				$this -> msg = "不是合法的支付宝帐号!";
				return;
			}
		}

		if (strcmp('', $sn) == 0) {
			$this -> msg = "请提供学号!";
			return;
		}

		if (strcmp('', $phone_no) == 0) {
			$this -> msg = "请提供手机号码!";
			return;
		}

		$db_ddz_account = spDB("DDZ_ACCOUNT");
		$condition = array('alipay_id' => $alipay_id);
		$rs_account = $db_ddz_account -> find($condition);
		if ($rs_account) {
			$uid = $rs['ID'];
			$spm = $rs['REG_SPM'];
		}

		//如果没有spm标识,归为大赛推荐
		if ($uid > 0 & strcasecmp('', $spm) == 0) {
			$up_condition = array('id' => $uid);
			$up_content = array('REG_SPM' => $spm_mark);
			$up_rs = $db_ddz_account -> update($up_condition, $up_content);
		}

		//创建报名数据
		$db_sq_reg = spDB("SQ_REG");
		$condition_reg = array('ALIPAY_ID' => $alipay_id);
		$rs_sq_reg = $db_sq_reg -> find($condition_reg);
		if (!$rs_sq_reg) {
			$now = date("Y-m-d H:i:s", time());
			$new_sq_reg = array("ALIPAY_ID" => $alipay_id, "PHONE_NO" => $phone_no, 'GMT_CREATE' => $now, 'STATUS' => '1');
			$create_rs = $db_sq_reg -> create($new_sq_reg);
			if (!$create_rs) {
				$this -> msg = "信息提交失败,请重试!";
			} else {
				$this -> msg = "报名成功!";
				$this->rs_flag=TRUE;
			}

		} else {
			$this -> msg = "该支付宝帐号已经报名!";
		}

	}

	function checkEmail($C_mailaddr) {
		if (!preg_match("/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/", $C_mailaddr)) {
			return false;

		}
		return true;

	}

	function checkMobile($str) {
		$pattern = "/^(130|131|132|133|134|135|136|137|138|139|150|151|153|158|159|188|189|186)\d{8}$/";
		if (preg_match($pattern, $str)) {
			Return true;
		} else {
			Return false;
		}
	}

}
