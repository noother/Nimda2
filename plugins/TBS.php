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

class TBS extends Plugin {

	function isTriggered() {
		if(isset($this->info['text'])) $target = $this->info['text'];
		else $target = $this->info['nick'];
		$result = libHTTP::GET("www.bright-shadows.net","/userdata.php?username=".urlencode($target));
		if($result['content'][0] == "Unknown User") {
			$this->sendOutput($this->CONFIG['notfound_text']);
			return;
		}
		$tmp = explode(":",$result['content'][0]);
		$text = sprintf($this->CONFIG['text'],
							$tmp[0],
							$tmp[3],
							$tmp[4],
							$tmp[1],
							$tmp[2]);
		$this->sendOutput($text);
	}

}

?>
