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

class Convert extends Plugin {

	function isTriggered() {
		
		if(!isset($this->info['text'])) {
			$this->sendOutput(sprintf($this->CONFIG['no_text'],$this->info['triggerUsed']));
			return;
		}
		
		switch($this->info['triggerUsed']) {
			case "!asc2bin":
				$output = libConvert::asc2bin($this->info['text']);
				break;
			case "!asc2dec":
				$output = libConvert::asc2dec($this->info['text']);
				break;
			case "!asc2hex":
				$output = libConvert::asc2hex($this->info['text']);
				break;
			case "!asc2oct":
				$output = libConvert::asc2oct($this->info['text']);
				break;
			
			case "!bin2asc":
				$output = libConvert::bin2asc($this->info['text']);
				break;
			case "!bin2dec":
				$output = libConvert::bin2dec($this->info['text']);
				break;
			case "!bin2hex":
				$output = libCOnvert::bin2hex($this->info['text']);
				break;
			case "!bin2oct":
				$output = libConvert::bin2oct($this->info['text']);
				break;
			
			case "!dec2asc":
				$output = libConvert::dec2asc($this->info['text']);
				break;
			case "!dec2bin":
				$output = libConvert::dec2bin($this->info['text']);
				break;
			case "!dec2hex":
				$output = libConvert::dec2hex($this->info['text']);
				break;
			case "!dec2oct":
				$output = libConvert::dec2oct($this->info['text']);
				break;
			
			case "!hex2asc":
				$output = libConvert::hex2asc($this->info['text']);
				break;
			case "!hex2bin":
				$output = libConvert::hex2bin($this->info['text']);
				break;
			case "!hex2dec":
				$output = libConvert::hex2dec($this->info['text']);
				break;
			case "!hex2oct":
				$output = libConvert::hex2oct($this->info['text']);
				break;
			
			case "!oct2asc":
				$output = libConvert::oct2asc($this->info['text']);
				break;
			case "!oct2bin":
				$output = libConvert::oct2bin($this->info['text']);
				break;
			case "!oct2dec":
				$output = libConvert::oct2dec($this->info['text']);
				break;
			case "!oct2hex":
				$output = libConvert::oct2hex($this->info['text']);
				break;
		}
		
		if(!$output) {
			$this->sendOutput($this->CONFIG['invalid_text']);
			return;
		}
		
		$filtered = "";
		for($x=0;$x<strlen($output);$x++) {
			if(ord($output[$x]) < 32) $filtered.= '?';
			else $filtered.= $output[$x];
		}
		
		$this->sendOutput($filtered);
	}




}

?>
