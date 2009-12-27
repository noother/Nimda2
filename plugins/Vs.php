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

class Vs extends Plugin {
	
	# Based on noother's Google Plugin.

	function isTriggered() {
		
		if(!isset($this->info['text'])) {
			$this->sendOutput(sprintf($this->CONFIG['no_term'],$this->info['triggerUsed']));
			return;
		}
		
		$input = explode(",",$this->info['text']);
		
		if($input[1] == ""){
			$this->sendOutput(sprintf($this->CONFIG['no_term'],$this->info['triggerUsed']));
			return;
		}
		
		$word1Hits = libInternet::googleResults($input[0]);
		$word2Hits = libInternet::googleResults($input[1]);
		
		if ($word1Hits + $word2Hits == 0){
			$zero = array("zero", "oh", "null", "nil", "nought");
			$this->sendOutput("I can't compare ".$zero[rand(0,4)]." with ".$zero[rand(0,4)].".");
			return;
		}
		
		$this->sendOutput("(".number_format($word1Hits,0,',','.').") \x02".$input[0]."\x02 ".$this->getBar($word1Hits,$word2Hits)."\x02 ".$input[1]."\x02 (".number_format($word2Hits,0,',','.').")");
	}
	
	function getBar($number1, $number2){
		$divider = ($number1 + $number2) / 20;
		$output = "[".str_repeat("=",round($number1/$divider));
		$output .= "|";
		$output .= str_repeat("=",round($number2/$divider))."]";
		return $output;
	}

}

?>
