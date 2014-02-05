<?php
class mbops extends spController {

	function index() {

		$db = spDB("DDZ_TAOKE_REPORT_SETTLE");
		//统计现金未结算佣金合计
		$conditon = array("outcode_type" => 'B', "settle_status" => 'U');
		$rs_sum = $db -> find($conditon, null, "count(*) cnt ,sum(settle_fee) as fee");
		$this -> ttl_cash = round($rs_sum['fee'], 2);
		$this -> ttl_cash_cnt = $rs_sum['cnt'];

		$conditon = array("outcode_type" => 'J', "settle_status" => 'U');
		$rs_sum_jfb = $db -> find($conditon, null, "count(*) cnt ,sum(settle_JFB) as fee");
		$this -> ttl_jfb = round($rs_sum_jfb['fee'] / 100, 2);
		$this -> ttl_jfb_cnt = $rs_sum_jfb['cnt'];

		$mydate = date("Y-m-d", time());
		if($this->spArgs('mydate')){
			$mydate=$this->spArgs('mydate');
		}
		
		//fb($mydate,'mydate');
		//查看实时订单
		//echo($today);
		$db = spDB("DDZ_TAOKE_ORDER_LOG");
		$conditon = array("date(GMT_CREATE)" => $mydate, "STATUS" => "S", "TYPE" => 'UP');
		$rs_order = $db -> find($conditon, "GMT_CREATE DESC", null);
		$rs_dtl = json_decode($rs_order['DETAIL']);
		//dump($rs_dtl);
		$this -> ttl_real_cnt = $rs_dtl->{'payCount'};
		$this -> ttl_real_commission = round($rs_dtl->{'commission'},2);
		$this -> ttl_real_date = $rs_order['GMT_CREATE'];

		//实时确认收货
		$db = spDB("DDZ_TAOKE_REPORT");
		$conditon = array("date(GMT_PAID)" => $mydate);
		$rs_report = $db -> find($conditon, null, "SUM(COMMISSION) comminsion,count(*) cnt");
		$this -> ttl_cnt_confirm = $rs_report['cnt'];
		$this -> ttl_sum_confirm = round($rs_report['comminsion'],2);

	}

	function sum_pay() {
		
		
		//统计打款支出
		$db = spDB('DDZ_TAOKE_REPORT_SETTLE');
		
		
		if($this->spArgs('date_start')){
			$date_start=$this->spArgs('date_start');
		}
		if($date_start=="开始日期"){
			//本月第一天
		    $date_start = date('Y-m-d', mktime(0, 0, 0, date('n'), 1, date('Y')));
		}
		//fb($date_start,'date_start');
		
		//Today
		$today = date("Y-m-d", time());
		$interval=floor((strtotime($today)-strtotime($date_start))/86400);
		//fb($interval,'interval');
		
		$ttl_cnt_pay_cash = 0;
		$ttl_sum_pay_cash = 0;
		$ttl_cnt_pay_jfb = 0;
		$ttl_sum_pay_jfb = 0;
		$ttl_cnt_pay = 0;
		$ttl_sum_pay = 0;
		$avg_pay=0;
		//现金打款
		$condition = "GMT_CREATE >'$date_start' AND SETTLE_STATUS='S' AND OUTCODE_TYPE='B'";
		$rs_report = $db -> find($condition, null, "SUM(SETTLE_FEE) ttl_sum_pay,count(*) ttl_cnt_pay");
		if ($rs_report) {
			$ttl_cnt_pay_cash = round($rs_report['ttl_cnt_pay'],2);
			$ttl_sum_pay_cash = round($rs_report['ttl_sum_pay'],2);
		}
		//集分宝
		$db = spDB('DDZ_JFB_SETTLE_RECORD');
		$condition = "GMT_CREATE >'$date_start'";
		$rs_report = $db -> find($condition, null, "SUM(SUCCESS_JFB_COUNT) ttl_sum_pay,count(*) ttl_cnt_pay");
		if ($rs_report) {
			$ttl_cnt_pay_jfb = round($rs_report['ttl_cnt_pay'],2);
			$ttl_sum_pay_jfb = round($rs_report['ttl_sum_pay'] / 100,2);
		}

		$ttl_cnt_pay = $ttl_cnt_pay_cash + $ttl_cnt_pay_jfb;
		$ttl_sum_pay = round($ttl_sum_pay_cash + $ttl_sum_pay_jfb*(1.07),2);
		
		$avg_pay=round($ttl_sum_pay/$interval,2);
		
		//未提现资金余额
		$db = spDB('DDZ_ACCOUNT');
		$rs_report=$db -> find(null, null, "SUM(JFB_AMOUNT)/100+SUM(CASH_AMOUNT) AS balance");
		$ttl_sum_balance=round($rs_report['balance'],2);

		$rs = array('ttl_cnt_pay_cash' => $ttl_cnt_pay_cash, 'ttl_sum_pay_cash' => $ttl_sum_pay_cash, 'ttl_cnt_pay_jfb' => $ttl_cnt_pay_jfb, 'ttl_sum_pay_jfb' => $ttl_sum_pay_jfb,'ttl_cnt_pay'=>$ttl_cnt_pay,'ttl_sum_pay'=>$ttl_sum_pay,'avg_pay'=>$avg_pay,'ttl_sum_balance'=>$ttl_sum_balance);
		echo json_encode($rs);
		
		
		

	}

}
?>
