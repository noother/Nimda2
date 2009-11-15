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

class Seen extends Plugin {
	
	private $nick;
	private $channel;
	private $text;
	
	function isTriggered() {
		if(!isset($this->info['text'])) {
			$this->sendOutput("Usage: ".$this->info['triggerUsed']." nick");
			return;
		}
		
		$nick = addslashes($this->info['text']);
		
		$res = $this->MySQL->sendQuery("SELECT * FROM seen WHERE nick='".$nick."'");
		if($res['count'] == 0) {
			$this->sendOutput("I don't know ".$nick.".");
			return;
		}
		
		$data = $res['result'][0];
		
		switch($data['action']) {
			case "PRIVMSG":
				$action = "saying";
				break;
		}
		
		$text = sprintf($this->CONFIG['text'],
							$data['nick'],
							libTime::secondsToString(time()-strtotime($data['last_update'])),
							$data['channel'],
							$action,
							$data['text']
						);
		
		$this->sendOutput($text);
		
	}
	
	function onChannelMessage() {
		$this->init();
		
		$sql = "UPDATE
					seen
				SET
					channel = '".$this->channel."',
					action = 'PRIVMSG',
					text = '".$this->text."',
					last_update = NOW()
				WHERE
					nick='".$this->nick."'
				";
		$this->MySQL->sendQuery($sql);
	}
	
	function init() {
		$this->nick    = addslashes($this->info['nick']);
		$this->channel = addslashes($this->info['channel']);
		$this->text    = addslashes($this->info['text']);
		if(!$this->userInDb()) $this->insertUser();
	}
	
	function userInDb() {
		$res = $this->MySQL->sendQuery("SELECT 1 FROM seen WHERE nick='".$this->nick."'");
		if($res['count'] == 0) return false;
	return true;
	}
	
	function insertUser() {
		$this->MySQL->sendQuery("INSERT INTO seen (nick) VALUES ('".$this->nick."')");
	return true;
	}
}

?>
