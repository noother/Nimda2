<?php

/*
	Copyright (C) 2009  noother [noothy@gmail.com]

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
*/

class libConvert {

	function asc2dec($string) {
		if(!$string) return false;
		$output = "";
		for($x=0;$x<strlen($string);$x++) {
			$output.= ord($string[$x])." ";
		}
	return substr($output,0,-1);
	}
	
	function asc2bin($string) { return self::dec2bin(self::asc2dec($string)); }
	function asc2hex($string) { return self::dec2hex(self::asc2dec($string)); }
	function asc2oct($string) { return self::dec2oct(self::asc2dec($string)); }
	
	
	function bin2dec($string) {
		if(!$string) return false;
		$tmp = preg_replace('/\s+/',' ',$string);
		if(preg_match("/[^01 ]/",$tmp)) return false;
		$tmp = explode(" ",$tmp);
		for($x=0;$x<sizeof($tmp);$x++) {
			$tmp[$x] = str_pad($tmp[$x],8,'0',STR_PAD_LEFT);
		}
		$tmp = implode($tmp);
		
		$output = "";
		for($x=0;$x<=strlen($tmp)-8;$x+=8) {
			$output.= bindec(substr($tmp,$x,8))." ";
		}
	return substr($output,0,-1);
	}
	
	function bin2asc($string) { return self::dec2asc(self::bin2dec($string)); }
	function bin2hex($string) { return self::dec2hex(self::bin2dec($string)); }
	function bin2oct($string) { return self::dec2oct(self::bin2dec($string)); }
	
	
	function dec2asc($string) {
		if(!$string) return false;
		$tmp    = preg_replace('/\s+/',' ',$string);
		if(preg_match("/[^0-9 ]/",$tmp)) return false;
		$tmp    = explode(" ",$tmp);
		$output = "";
		foreach($tmp as $val) {
			$output.=chr($val);
		}
	return $output;
	}
	
	
	function dec2bin($string) {
		if(!$string) return false;
		$tmp    = preg_replace('/\s+/',' ',$string);
		if(preg_match("/[^0-9 ]/",$tmp)) return false;
		$tmp    = explode(" ",$tmp);
		$output = "";
		foreach($tmp as $val) {
			$tmp1 = decbin($val);
			$tmp1 = str_pad($tmp1,8,'0',STR_PAD_LEFT);
			$output.= $tmp1." ";
		}
	return substr($output,0,-1);
	}
	
	
	function dec2hex($string) {
		if(!$string) return false;
		$tmp    = preg_replace('/\s+/',' ',$string);
		if(preg_match("/[^0-9 ]/",$tmp)) return false;
		$tmp    = explode(" ",$tmp);
		$output = "";
		foreach($tmp as $val) {
			$tmp1 = dechex($val);
			$tmp1 = str_pad($tmp1,2,'0',STR_PAD_LEFT);
			$output.= $tmp1." ";
		}
	return substr($output,0,-1);
	}
	
	
	function dec2oct($string) {
		if(!$string) return false;
		$tmp = preg_replace('/\s+/',' ',$string);
		if(preg_match("/[^0-9 ]/",$tmp)) return false;
		$tmp = explode(" ",$tmp);
		$output = "";
		foreach($tmp as $val) {
			$output.=decoct($val)." ";
		}
	return substr($output,0,-1);
	}
	
	
	function hex2dec($string) {
		if(!$string) return false;
		$tmp = preg_replace('/\s+/',' ',$string);
		$tmp = str_replace('%','',$tmp);
		if(preg_match("/[^0-9A-F ]/i",$tmp)) return false;
		$tmp = explode(" ",$tmp);
		for($x=0;$x<sizeof($tmp);$x++) {
			$tmp[$x] = str_pad($tmp[$x],2,'0',STR_PAD_LEFT);
		}
		$tmp = implode($tmp);
		
		$output = "";
		for($x=0;$x<=strlen($tmp)-2;$x+=2) {
			$output.= hexdec(substr($tmp,$x,2))." ";
		}
	return substr($output,0,-1);
	}
	
	function hex2asc($string) { return self::dec2asc(self::hex2dec($string)); }
	function hex2bin($string) { return self::dec2bin(self::hex2dec($string)); }
	function hex2oct($string) { return self::dec2oct(self::hex2dec($string)); }
	
	
	function oct2dec($string) {
		if(!$string) return false;
		$tmp = preg_replace('/\s+/',' ',$string);
		if(preg_match("/[^0-7 ]/",$tmp)) return false;
		$tmp = explode(" ",$tmp);
		$output = "";
		foreach($tmp as $val) {
			$output.= octdec($val)." ";
		}
	return substr($output,0,-1);
	}
	
	function oct2asc($string) { return self::dec2asc(self::oct2dec($string)); }
	function oct2bin($string) { return self::dec2bin(self::oct2dec($string)); }
	function oct2hex($string) { return self::dec2hex(self::oct2dec($string)); }

}

?>
