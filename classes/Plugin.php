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

class Plugin {

	public  $name        = "";
	public  $author      = "";
	public  $description = "";
	public  $version     = "";
	public  $level       = 0;
	public  $triggers    = array();
	public	$originalTriggers = array();
	public  $configFile  = "";
	public  $CONFIG      = array();

	public  $output      = array();
	
	public  $IRCBot;
	public  $MySQL;
	
	public $info;
	
	function Plugin($config, $IRCBot) {
		$this->IRCBot = $IRCBot;
		$this->MySQL = $IRCBot->MySQL;
	
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
				case "level":
					$this->level = $value;
					break;
				case "triggers":
					//preg_match_all("/'(.*?)'/",$value,$arr);
					preg_match_all("/'(.*?[^\\\\])'/",$value,$arr);
					$triggers = libArray::stripslashes($arr[1]);
					$this->triggers = libArray::sortByLengthDESC($triggers);
					$this->originalTriggers = $this->triggers;
					break;
				case "configFile":
					$this->configFile = $value;
					break;
				default:
					$this->CONFIG[$name] = $value;
					break;
			}
		}
	}
	
	function addTrigger($trigger) {
		array_push($this->triggers,$trigger);
		$this->triggers = libArray::sortByLengthDESC($this->triggers);
	return true;
	}
	
	function delTrigger($trigger) {
		$key = array_search($trigger,$this->triggers);
		if($key === false) return false;
		unset($this->triggers[$key]);
	return true;
	}
	
	function resetTriggers() {
		$this->triggers = $this->originalTriggers;
	}
	
	function sendOutput($text,$target=null) {
		if(!isset($target)) $target = $this->info['target'];
		$this->IRCBot->sendPrivmsg($target,$text);
	}
	
	function sendBroadcast($text) {
		$target = $this->IRCBot->CONFIG['broadcast_channel'];
		$this->IRCBot->sendPrivmsg($target,$text);
	}
	
	function sendAction($text,$target=null) {
		if(!isset($target)) $target = $this->info['target'];
		$this->IRCBot->sendAction($target,$text);
	}
	
	function sendNotice($text,$target=null) {
		if(!isset($target)) $target = $this->info['target'];
		$this->IRCBot->sendNotice($target,$text);
	}
	
	function trigger() {
		$this->output = array();
		if($this->IRCBot->isAuthorized($this->info['nick'],$this->level)) {
			$this->isTriggered();
		} else {
			$this->IRCBot->sendPrivmsg($this->info['target'],sprintf($this->IRCBot->CONFIG['text_not_authorized'],$this->level,$this->IRCBot->lastLevel));
		}
	}
	
	function onMessage() {}
	function onChannelMessage() {}
	function onQuery() {}
	function onPing() {}
	function onKick() {}
	function onTopic() {}
	function onMode() {}
	function onAction() {}
	function onJoin() {}
	function onCtcp() {}
	function onCtcpPing() {}
	function onCtcpFinger() {}
	function onCtcpVersion() {}
	function onCtcpTime() {}
}


?>
