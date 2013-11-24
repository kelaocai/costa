<?php

define("TOKEN", "1qaz2wsx");

class wx extends spController {
	
	function wxreader(){
		
		$echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->check()){
        	echo $echoStr;
        	exit;
        }
		
	}
	
	
	function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->check()){
        	echo $echoStr;
        	exit;
        }
    }
	
	function check()
	{
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];	
        		
		$token = TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}

}
?>