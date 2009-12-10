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

class Google extends Plugin {

	function isTriggered() {
		
		if(!isset($this->info['text'])) {
			$this->sendOutput(sprintf($this->CONFIG['no_term'],$this->info['triggerUsed']));
			return;
		}
		
		$host   = "www.google.com";
		$get    = "/search?q=".urlencode($this->info['text'])."&hl=".$this->CONFIG['lang']."&safe=off";
		$link   = "http://".$host.$get;
		$result = libHTTP::GET($host,$get);
		
		$result = implode($result['content'],"\n");
		
		preg_match('#Ergebnisse <b>1</b> - <b>\d{1,2}</b> von (ungef.hr )?<b>(.*?)</b>#',$result,$arr);
		
		$output = $link." (Results: ";
		if(empty($arr)) $output.= "0";
		elseif(!empty($arr[1])) $output.= "~ ";
		$output.= $arr[2].")";
		
		$this->sendOutput($output);
		
		
		
		
		
	}

}

?>
