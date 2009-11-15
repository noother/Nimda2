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

class Trace extends Plugin {

	function isTriggered() {
		if(isset($this->info['text'])) $target = $this->info['text'];
		else $target = $this->info['nick'];
		
		$host = $this->IRCBot->getHost($target);
		if(!$host) {
			$this->sendOutput($target.' is not online.');
			return;
		}
		$res = libHTTP::GET('www.geoiptool.com','/de/?IP='.urlencode($host));
		if(strstr($res['raw'],'function.gethostbyaddr')) {
			$this->sendOutput("Can't trace ".$target.". Host is cloaked.");
			return;
		} else {
			$raw = strtr($res['raw'], array("\n" => " ", "\r" => " "));
			preg_match('#IP-Addresse:.*?<td.*?>(.*?)</td>#',$raw,$arr);
			$ip = $arr[1];
			preg_match('#Stadt:.*?<td.*?>(.*?)</td>#',$raw,$arr);
			$city = utf8_encode($arr[1]);
			preg_match('#Land:.*?<td.*?><a.*?>(.*?)</a>#',$raw,$arr);
			$country = utf8_encode(trim($arr[1]));
			preg_match('#Region.*?</td.*?><a.*?>(.*?)</a>#',$raw,$arr);
			$region = utf8_encode($arr[1]);
			
			//$this->sendOutput('IP: '.$ip);
			$this->sendOutput($target.'\'s location: '.$city.', '.$region.', '.$country);
		}
		
	}

}

?>
