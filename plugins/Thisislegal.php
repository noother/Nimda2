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

class Thisislegal extends Plugin {

	function isTriggered() {
		if(isset($this->info['text'])) $target = $this->info['text'];
		else $target = $this->info['nick'];
		$result = libHTTP::GET("www.thisislegal.com","/userscore.php?username=".urlencode($target));
		
		if(empty($result['raw'])) {
			$this->sendOutput(sprintf($this->CONFIG['text_notfound'],$target));
			return;
		}
		$tmp = explode(":",$result['content'][0]);
		
		$nick  = $target;
		$rank  = $tmp[0];
		$score = $tmp[1];
		$score_total = $tmp[2];
		$users = $tmp[3];
		$challs_total = $tmp[4];
		
		$result = libHTTP::GET("www.thisislegal.com","/profile.php?username=".urlencode($target));
		$challs = substr_count($result['raw'],'<font color=\'#00FF00\' face=\'Verdana\' size=\'2\'>Yes</font>');
		
		$text = sprintf($this->CONFIG['text'],
							$target,
							$challs,
							$challs_total,
							$rank,
							$users,
							$score,
							$score_total);
		$this->sendOutput($text);
	}

}

?>

