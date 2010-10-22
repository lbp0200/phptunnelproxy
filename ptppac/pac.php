<?php
/*
 * This is a php implementation of autoproxy2pac
 */


function reg_encode($str) {
	$tmp_str=$str;
	$tmp_str=str_replace('/', "\\/", $tmp_str);
	$tmp_str=str_replace('.', "\\.", $tmp_str);
	$tmp_str=str_replace(':', "\\:", $tmp_str);
	$tmp_str=str_replace('%', "\\%", $tmp_str);
	$tmp_str=str_replace('*', ".*", $tmp_str);
	$tmp_str=str_replace('-', "\\-", $tmp_str);
	$tmp_str=str_replace('&', "\\&", $tmp_str);
	$tmp_str=str_replace('?', "\\?", $tmp_str);
	
	return $tmp_str;
}

function get_pac($proxy_type, $proxy_host, $proxy_port) {
	$gfwlist_url='http://autoproxy-gfwlist.googlecode.com/svn/trunk/gfwlist.txt';
	
	$ch=curl_init();  
	curl_setopt($ch, CURLOPT_URL, $gfwlist_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$gfwlist_raw=curl_exec($ch);
	curl_close($ch);
	if(isset($_GET['raw']))
	{
		header('Content-Type: text/plain');
		echo $gfwlist_raw;
		exit(0);
	}
	$rulelist=base64_decode($gfwlist_raw);
	$gfwlist=explode("\n", $rulelist); 
	
	$pac_content='//Generated by ptp gfwlist2pac'."\n";
	$find_function_content.='function FindProxyForURL(url, host){var PROXY = "'.$proxy_type.' '.$proxy_host.':'.$proxy_port.'";var DEFAULT = "DIRECT";';
		
	foreach($gfwlist as $index=>$rule){
		if(empty($rule))
			continue;
		else if(substr($rule,0,1)=='!' || substr($rule,0,1)=='[')
			continue;
		$return_proxy='PROXY';
		if(substr($rule,0,2)=='@@')
		{
			$rule=substr($rule,2);
			$return_proxy="DEFAULT";
		}
		
		if(substr($rule,0,2)=='||')
		{
			$rule_reg = "^[\\w\\-]+:\\/+(?!\\/)(?:[^\\/]+\\.)?".reg_encode(substr($rule,2));
		}
		else if(substr($rule,0,1)=='|')
		{
			$rule_reg = "^".reg_encode(substr($rule,1));
		}
		else if(substr($rule,0,1)=='/'&&substr($rule,-1)=='/')
		{
			$rule_reg = substr($rule,1,strlen($rule)-2);
		}
		else
		{
			$rule_reg=reg_encode($rule);
		}
		if(eregi("\|$", $rule_reg))
		{
			$rule_reg=substr($rule_reg,0,strlen($rule_reg)-1)."$";
		}
		//echo '//'.$rule_reg."\n\t";
		$find_function_content.= 'if(/'.$rule_reg.'/i.test(url)) return '.$return_proxy.';';
	}
	$find_function_content.='return DEFAULT;'."}";
	
	$pac_content.='eval(decode64("'.base64_encode($find_function_content).'"));';
		
	$pac_content.="\n";
	
	$pac_content.='function decode64(_1){var _2="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";var _3="";var _4,_5,_6;var _7,_8,_9,_a;var i=0;_1=_1.replace(/[^A-Za-z0-9\+\/\=]/g,"");do{_7=_2.indexOf(_1.charAt(i++));_8=_2.indexOf(_1.charAt(i++));_9=_2.indexOf(_1.charAt(i++));_a=_2.indexOf(_1.charAt(i++));_4=(_7<<2)|(_8>>4);_5=((_8&15)<<4)|(_9>>2);_6=((_9&3)<<6)|_a;_3=_3+String.fromCharCode(_4);if(_9!=64){_3=_3+String.fromCharCode(_5);}if(_a!=64){_3=_3+String.fromCharCode(_6);}}while(i<_1.length);return _3;}';
	
	return $pac_content;
}

function main() {
	if($_GET['proxy-name']=='free-gate') {
		$proxy_type='PROXY';
		$proxy_host='127.0.0.1';
		$proxy_port='8580';
	}else if($_GET['proxy-name']=='tor') {
		$proxy_type='SOCKS5';
		$proxy_host='127.0.0.1';
		$proxy_port='9050';
	}else if($_GET['proxy-name']=='ssh-d') {
		$proxy_type='SOCKS5';
		$proxy_host='127.0.0.1';
		$proxy_port='7070';
	}else if($_GET['proxy-name']=='gappproxy') {
		$proxy_type='PROXY';
		$proxy_host='127.0.0.1';
		$proxy_port='8000';
	}else if($_GET['proxy-name']=='jap') {
		$proxy_type='PROXY';
		$proxy_host='127.0.0.1';
		$proxy_port='4001';
	}else if($_GET['proxy-name']=='your-freedom') {
		$proxy_type='PROXY';
		$proxy_host='127.0.0.1';
		$proxy_port='8080';
	}else if($_GET['proxy-name']=='puff') {
		$proxy_type='PROXY';
		$proxy_host='127.0.0.1';
		$proxy_port='1984';
	}else if($_GET['proxy-name']=='privoxy') {
		$proxy_type='PROXY';
		$proxy_host='127.0.0.1';
		$proxy_port='8118';
	}else if($_GET['proxy-name']=='wu-jie') {
		$proxy_type='PROXY';
		$proxy_host='127.0.0.1';
		$proxy_port='9666';
	} else {
		if($_GET['proxy_type']=='http')
			$prroxy_type='PROXY';
		else if ($_GET['proxy_type']=='socks')
			$proxy_type='SOCKS5';
		else
			$proxy_type=$_GET['proxy_type'];
	
		$proxy_host=$_GET['proxy_host'];
		$proxy_port=$_GET['proxy_port'];
	}
	
	
	$pac = get_pac($proxy_type, $proxy_host, $proxy_port);
	
	header('Content-Type: text/plain');
	header('Content-Length: '.strlen($pac));
	echo $pac;
}

main();
?>