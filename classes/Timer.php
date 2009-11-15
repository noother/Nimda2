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

class Timer {
	public  $name        = "";
	public  $author      = "";
	public  $description = "";
	public  $version     = "";
	public  $interval    = 1;
	public  $configFile  = "";
	public  $CONFIG      = array();
	public  $lastTimeTriggered;
	
	public  $IRCBot;
	
	function Timer($IRCBot, $config) {
		$this->IRCBot   = $IRCBot;
		
		foreach($config as $name => $value) {
			switch($name) {
				case "name":
					$this->name = $value;
					break;
				case "author":
					$this->author = $value;
					break;
				case "description":
					$this->description = $value;
					break;
				case "version":
					$this->version = $value;
					break;
				case "interval":
					$this->interval = $value;
					break;
				case "configFile":
					$this->configFile = $value;
					break;
				default:
					$this->CONFIG[$name] = $value;
					break;
			}
		}
		
		$this->lastTimeTriggered = time();
	}
	
	function sendBroadcast($text) {
		$target = $this->IRCBot->CONFIG['broadcast_channel'];
		$this->IRCBot->sendPrivmsg($target,$text);
	}
	
	function trigger() {
		$this->lastTimeTriggered = time();
		$this->isTriggered();
	}

}

?>
