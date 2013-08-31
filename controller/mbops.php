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
		$rs_sum_jfb = $db -> find($conditon, null, "count(*) cnt ,sum(settle_fee) as fee");
		$this -> ttl_jfb = round($rs_sum_jfb['fee'], 2);
		$this -> ttl_jfb_cnt = $rs_sum_jfb['cnt'];
		
		//查看实时订单
		$today=date("Y-m-d",time());
		//echo($today);
		$db = spDB("DDZ_TAOKE_ORDER_LOG");
		$conditon= array("date(GMT_CREATE)"=>$today,"STATUS"=>"S","TYPE"=>'UP');
		$rs_order= $db -> find($conditon, "GMT_CREATE DESC", null);
		$rs_dtl=json_decode($rs_order['DETAIL']);
		//dump($rs_dtl);
		$this -> ttl_real_cnt=$rs_dtl->{'payCount'};
		$this -> ttl_real_commission=$rs_dtl->{'commission'};
		$this -> ttl_real_date=$rs_order['GMT_CREATE'];
		
		//实时确认收货
		$db = spDB("DDZ_TAOKE_REPORT");
		$conditon= array("date(GMT_PAID)"=>$today);
		$rs_report=$db->find($conditon,null,"SUM(COMMISSION) comminsion,count(*) cnt");
		$this -> ttl_cnt_confirm=$rs_report['cnt'];
		$this -> ttl_sum_confirm=$rs_report['comminsion'];
		

	}

}
