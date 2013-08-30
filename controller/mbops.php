<?php
class mbops extends spController {

	function index() {

		$db = spDB("DDZ_TAOKE_REPORT_SETTLE");
		//统计现金未结算佣金合计
		$conditon = array("outcode_type" => 'B', "settle_status" => 'U');
		$rs_cnt = $db -> findCount($conditon);
		$rs_sum = $db -> find($conditon, null, "sum(settle_fee) as fee");
		$this -> ttl_cash = round($rs_sum['fee'], 2);
		$this -> ttl_cash_cnt = $rs_cnt;
		//查看实时订单
		$today=date("Y-m-d",time());
		//echo($today);
		$db = spDB("DDZ_TAOKE_ORDER_LOG");
		$conditon= array("date(GMT_CREATE)"=>$today,"STATUS"=>"S","TYPE"=>'UP');
		$rs= $db -> find($conditon, "GMT_CREATE DESC", null);
		$rs_dtl=json_decode($rs['DETAIL']);
		//dump($rs_dtl);
		$this -> ttl_real_cnt=$rs_dtl->{'tradeCount'};
		$this -> ttl_real_commission=$rs_dtl->{'commission'};
		$this -> ttl_real_date=$rs['GMT_CREATE'];
		
		//实时确认收货
		//select sum(commission) from DDZ_TAOKE_REPORT where date(GMT_PAID)='2013-08-26'
		$db = spDB("DDZ_TAOKE_REPORT");
		$conditon= array("date(GMT_PAID)"=>$today);
		$rs_cnt_confirm=$db->findCount($conditon);
		$rs_sum_confirm=$db->find($conditon,null,"SUM(COMMISSION) comminsion");
		$this -> ttl_cnt_confirm=$rs_cnt_confirm;
		$this -> ttl_sum_confirm=$rs_sum_confirm['comminsion'];
		

	}

}
