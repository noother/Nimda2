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

class Hackquest extends Plugin {

	function isTriggered() {
		if(!isset($this->info['text'])) $nick = $this->info['nick'];
		else $nick = $this->info['text'];
		
		$domain = $this->CONFIG['domain'];
		$get    = sprintf($this->CONFIG['get'],urlencode($nick));
		
		$result = libHTTP::GET($domain,$get);
		$result = implode($result['content'],"\n");
		
		if(preg_match("/There is no available information for/",$result)) {
			$this->sendOutput(sprintf($this->CONFIG['doesnt_exist'],$nick,$this->CONFIG['domain']));
			return;
		}
		
		preg_match('#<h3>User information of (VIP )?(.*?)</h3>#',$result,$arr);
		$nick = $arr[2];
		
		preg_match('#<b>Rank:</b>.*?<font color=".*?">(.*?)</font>#',$result,$arr);
		$rank = $arr[1];
		
		preg_match('#<b>Donated:</b>.*?<font color=".*?">(.*?)</font>#',$result,$arr);
		$donated = $arr[1];
		if($donated == "This guy didn't donate anything yet!") $donated = false;
		else $donated = str_replace(" ","",$donated);
		
		preg_match('#<b>Number of hacks:</b>.*?<td>(.*?)</td>#',$result,$arr);
		$solved = $arr[1];
		
		preg_match('#<b>Rankpoints:</b>.*?<td>(.*?)</td>#',$result,$arr);
		$rankpoints = $arr[1];
		
		preg_match('#<b>Visited:</b>.*?<td>(.*?)</td>#',$result,$arr);
		$visited = $arr[1];
		
		preg_match('#<b>Time spent overall:</b>.*?<td>(.*?)</td>#',$result,$arr);
		$timeSpent = $arr[1];
		
		preg_match('#<b>Last online:</b>.*?<td>(.*?)</td>#',$result,$arr);
		$lastOnline = trim($arr[1]);
		
		preg_match('#<b>User\'s current status:</b>.*?<td>(.*?)</td>#',$result,$arr);
		if($arr[1] == "offline") $online = false;
		else $online = true;
		
		$output = sprintf($this->CONFIG['text_status'],
								$nick,
								$rank,
								$solved,
								libString::end_s("challenge",$solved),
								$rankpoints);
		$output.= " ";
		$output.= sprintf($this->CONFIG['text_visit'],
								$nick,
								$this->CONFIG['domain'],
								$visited,
								libTime::secondsToString($timeSpent));
		if($donated) {
			$output.= " ";
			$output.= sprintf($this->CONFIG['text_donate'],
								$nick,
								$donated);
		}
		
		$output.= " ";
		if($online) {
			$output.= sprintf($this->CONFIG['text_online'],
								$nick,
								$domain);
		} else {
			$output.= sprintf($this->CONFIG['text_lastonline'],
								$nick,
								$lastOnline);
		}
		
		$this->sendOutput($output);
	}

}

?>
