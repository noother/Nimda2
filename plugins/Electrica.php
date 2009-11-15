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

class Electrica extends Plugin {

	function isTriggered() {
		
		clearstatcache();
		if(!file_exists("plugins/Electrica/cache") || (filemtime("plugins/Electrica/cache") < time()-60*20 && filectime("plugins/Electrica/cache") < time()-60*20)) {
			$this->cacheRanklist();
		}
		
		if(isset($this->info['text'])) $target = $this->info['text'];
		else $target = $this->info['nick'];
		
		$tmp = file_get_contents("plugins/Electrica/cache");
		$pos = stripos($tmp,'>'.$target.'<');
		if($pos === false) {
			$this->sendOutput($this->CONFIG['notfound_text']);
			return;
		}
		
		$tmp1 = substr($tmp,0,$pos);
		$rankpos = strrpos($tmp1,'<b>');
		preg_match("#^<b>(.*?)</b>#",substr($tmp1,$rankpos,50),$arr);
		$rank = $arr[1];
		
		$tmp = substr($tmp,$pos-100,200);
		$target = preg_quote($target);
		
		preg_match('#<td align=center>(.*?)</td><td>('.$target.')</td><td align=right>(.*?)</td><td.*?>(.*?)</td>#i',$tmp,$arr);
		
		$output = sprintf($this->CONFIG['chall_text'],
								$arr[2],
								$rank,
								$arr[3],
								libString::end_s("challenge",$arr[3]),
								$arr[4]);
		$this->sendOutput($output);
	}
	
	function cacheRanklist() {
		$result = libHTTP::POST("caesum.com","/game/stats.php?action=rollyourown","maxusers=0&ind=on&action=showcustomhof&blah=Show+HOF","key=M0qUaqlkmfMoLq0Jdlb2aQ4F8032");
		$tmp = $result['content'];
		$fp = fopen("plugins/Electrica/cache","w");
		foreach($tmp as $line) {
			fputs($fp,$line."\n");
		}
		fclose($fp);
	}

}

?>
