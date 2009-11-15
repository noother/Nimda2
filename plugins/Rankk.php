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

class Rankk extends Plugin {

	function isTriggered() {
		if(isset($this->info['text'])) $target = $this->info['text'];
		else $target = $this->info['nick'];
		$result = libHTTP::GET("www.rankk.org","/profile/".urlencode($target));
		var_dump($result);
		$result = implode("\n",$result['content']);
		
		if(preg_match("#<title>User Not Found</title>#",$result)) {
			$this->sendOutput($this->CONFIG['notfound_text']);
			return;
		}
		
		$nick = $target;
		
		preg_match("#>Rankk Title</td><td>(.*?)</td>#",$result,$arr);
		$title = $arr[1];
		
		preg_match("#>Rankked</td><td>(.*?)</td>#",$result,$arr);
		$rank = $arr[1];
		
		preg_match("#>Points</td><td>(.*?)</td>#",$result,$arr);
		$points = $arr[1];
		
		preg_match("#>Solved</td><td>(.*?)</td>#",$result,$arr);
		$solved = $arr[1];
		
		preg_match("#>Points</td><td>(.*?)</td>#",$result,$arr);
		$points = $arr[1];
		
		preg_match("#>Level</td><td>(.*?)</td>#",$result,$arr);
		$level = $arr[1];
		
		$output = sprintf($this->CONFIG['chall_text'],
							$nick,
							$title,
							$rank,
							$points,
							libString::end_s("point",$points),
							$solved,
							libString::end_s("challenge",$solved),
							$nick,
							$level);
		$this->sendOutput($output);
	}

}

?>
