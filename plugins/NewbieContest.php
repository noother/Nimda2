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

class NewbieContest extends Plugin {

	function isTriggered() {
		$target = isset($this->info['text'])?$this->info['text']:$this->info['nick'];
		$res = libHTTP::GET("newbiecontest.org","/userscore.php?username=".urlencode($target));
		
		if($res['raw'] == "Member : unknown") {
			$text = sprintf($this->CONFIG['text_notfound'],
							$target);
			$this->sendOutput($text);
			return;
		}
		
		preg_match('#Member : (.*?)<br>Ranking : (.*?)/(.*?)<br>Points : (.*?)/(.*?)<br>Challenges solved : (.*?)/(.*?)<br>#',$res['raw'],$arr);
		
		$name = $arr[1];
		$rank = $arr[2];
		$rank_total = $arr[3];
		$points = $arr[4];
		$points_total = $arr[5];
		$challs = $arr[6];
		$challs_total = $arr[7];
		
		$output = sprintf($this->CONFIG['text'],
							$name,
							$challs,
							$challs_total,
							$rank,
							$rank_total,
							$points,
							$points_total
							);
		$this->sendOutput($output);
	}
	
}

?>
