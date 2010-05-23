<?php

/*
	This file is part of Nimda - An advanced event-driven IRC Bot written in PHP with a nice plugin system
	Copyright (C) 2010  noother [noothy@gmail.com]

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
		
		$link   = "http://www.google.com/search?q=".urlencode($this->info['text'])."&hl=".$this->CONFIG['lang']."&safe=off";
		
		$results = number_format(libInternet::googleResults($this->info['text']),0,',','.');
		
		$output = $link." (Results: ";
		if(!$results) $output.= "0";
		else $output.= "~ ";
		$output.= $results.")";
		
		$this->sendOutput($output);
		
	}

}

?>
