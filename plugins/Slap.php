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

class Slap extends Plugin {
	
	function isTriggered() {
		switch($this->info['triggerUsed']) {
			case "!slap":
				$this->slapSomeone();
				break;
			case "!addslap":
				$this->addSlap();
				break;
		}
		
	return;
	}
	
	function slapSomeone() {
		if(!isset($this->info['text'])) {
			$this->sendAction(sprintf("pokes %s in the eye for not knowing how to use !slap.",
										$this->info['nick']));
			return;
		}
		
		if($this->info['text'] == "stats") {
			$this->showStats();
			return;
		}
		
		$res = $this->MySQL->sendQuery("SELECT text FROM slaps ORDER BY RAND() LIMIT 1");
		$slap = $res['result'][0]['text'];
		
		$output = sprintf($slap,$this->info['text']);
		
		$this->sendAction($output);
		
		
	return;
	}
	
	function addSlap() {
		if(!$this->isAuthorized()) return;
		
		if(!isset($this->info['text'])) {
			$this->sendOutput("Usage: ".$this->info['triggerUsed']." your_slap_here");
			return;
		}
		
		$sql = "INSERT INTO slaps 
					(text, author, created)
				VALUES (
					'".addslashes($this->info['text'])."',
					'".addslashes($this->info['nick'])."',
					NOW()
				)";
		$this->MySQL->sendQuery($sql);
		$this->sendOutput("Your slap has been added.");
	return;
	}
	
	function isAuthorized() {
		if(!$this->IRCBot->isAuthorized($this->info['nick'],$this->CONFIG['add_level'])) {
			$output = sprintf($this->IRCBot->CONFIG['text_not_authorized'],$this->CONFIG['add_level'],$this->IRCBot->lastLevel);
			$this->sendOutput($output);
			return false;
		} else {
			return true;
		}
	}
	
	function showStats() {
		$sql = "SELECT COUNT(*) FROM slaps";
		$res = $this->MySQL->sendQuery($sql);
		$count = $res['result'][0]['COUNT(*)'];
		
		$output = sprintf($this->CONFIG['status_text'],
							$count,
							libString::end_s("way",$count)
						);
		$this->sendOutput($output);
	return;
	}

}

?>
