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

class Tv extends Plugin {

	function isTriggered() {
		$program = libInternet::getTvProgram();
		
		$stations = explode(',',$this->CONFIG['stations']);
		
		$output = array();
		$current = 0;
		
		$output[0] = "\x02[Current TV Program]\x02 ";
		foreach($stations as $station) {
			$save = $output[$current];
			$output[$current].= "\x02".$station."\x02: ".$program[$station]['title'].", ";
			if(strlen($output[$current]) > 450) {
				$output[$current] = substr($save,0,-2);
				$current++;
				$output[$current] = "\x02".$station."\x02: ".$program[$station]['title'].", ";
			}
		}
		$output[$current] = substr($output[$current],0,-2);
		
		foreach($output as $line) {
			$this->sendOutput($line);
		}
	}

}

?>
