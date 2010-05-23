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

class libHTTP {

	static function GET($host,$get,$cookie=null,$timeout=30,$port=80) {
		$fp     = fsockopen($host,$port,$timeout);
		if(!$fp) return false;
		
		$header = "GET ".$get." HTTP/1.0\r\n";
		$header.= "Host: ".$host."\r\n";
		$header.= "User-Agent: NimdaV2.0\r\n";
		if(isset($cookie)) $header.= "Cookie: ".$cookie."\r\n";
		
		fputs($fp,$header."\r\n");
		
		$headersCheck = true;
		$output = array();
		$output['content'] = array();
		
		while(false !== $row = fgets($fp)) {
			$row = trim($row);
			
			if($headersCheck && empty($row)) {
				$headersCheck = false;
				continue;
			}
			
			if($headersCheck) {
				$tmp = explode(": ",$row,2);
				$output['header'][$tmp[0]] = $tmp[1];
			} else {
				array_push($output['content'],$row);
			}
			
		}
		
		fclose($fp);
		echo "HTTP GET Request sent: http://".$host.$get."\n";
		
		$output['raw'] = implode("\n",$output['content']);
	return $output;
	}
	
	static function POST($host,$get,$post,$cookie=null,$timeout=30,$port=80) {
		$fp = fsockopen($host,$port,$timeout);
		if(!$fp) return false;
		
		$header = "POST ".$get." HTTP/1.0\r\n";
		$header.= "Host: ".$host."\r\n";
		$header.= "User-Agent: NimdaV2.0\r\n";
		if(isset($cookie)) {
			$header.= "Cookie: ".$cookie."\r\n";
		}
		$header.= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header.= "Content-Length: ".strlen($post)."\r\n";
		$header.= "\r\n";
		$header.= $post."\r\n";
		
		fputs($fp,$header."\r\n");
		
		$headersCheck = true;
		$output = array();
		$output['content'] = array();
		while(false !== $row = fgets($fp)) {
			$row = trim($row);
			
			if($headersCheck && empty($row)) {
				$headersCheck = false;
				continue;
			}
			
			if($headersCheck) {
				$tmp = explode(": ",$row,2);
				$output['header'][$tmp[0]] = $tmp[1];
			} else {
				array_push($output['content'],$row);
			}
			
		}
		fclose($fp);
		$output['raw'] = implode("\n",$output['content']);
		echo "HTTP POST Request sent: http://".$host.$get."\n";
	return $output;
	}

}


?>
