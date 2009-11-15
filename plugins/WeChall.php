<?php

/*
	This file is part of Nimda - An advanced event-driven IRC Bot written in PHP with a nice plugin system
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

class WeChall extends Plugin {

	function isTriggered() {
		if(isset($this->info['text'])) $target = $this->info['text'];
		else $target = $this->info['nick'];
		
		
		if($this->info['triggerUsed'] == '!wcc') {
			$result = libHTTP::GET("www.wechall.net","/wechallchalls.php?username=".urlencode($target));
			$tmp = $result['content'][0];
			
			$this->sendOutput($tmp);
			return;
		}
		
		
		
		
		
		$result = libHTTP::GET("www.wechall.net","/wechall.php?username=".urlencode($target));
		$tmp = $result['content'][0];
		
		if($tmp == 'The user \''.$target.'\' doesnt exist.') {
			$this->sendOutput($this->CONFIG['notfound_text']);
			return;
		}
		
		$check = preg_match('/^(.*?) is ranked (.*?) from (.*?), linked to (.*?) sites with an average of (.*?)% solved\. Total score is (.*?)\. (.*?) points needed for rankup\.$/',$tmp,$arr);
		if(!$check) {
			preg_match('/^(.*?) is ranked (.*?) from (.*?), linked to (.*?) sites with an average of (.*?)% solved\. Total score is (.*?)\. (.*?)$/',$tmp,$arr);
		}
		
		if($check) { 
			$output = sprintf($this->CONFIG['chall_text'],
								$arr[1],
								$arr[2],
								$arr[3],
								$arr[1],
								$arr[4],
								$arr[5],
								$arr[1],
								$arr[6],
								$arr[1],
								$arr[7]
								);
		} else {
			$output = sprintf($this->CONFIG['chall_text1'],
								$arr[1],
								$arr[2],
								$arr[3],
								$arr[1],
								$arr[4],
								$arr[5],
								$arr[1],
								$arr[6],
								$arr[7]
								);
		}
		
		$this->sendOutput($output);
	}

}

?>
