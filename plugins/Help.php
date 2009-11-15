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

class Help extends Plugin {

	function isTriggered() {
		if(!isset($this->info['text'])) {
			$this->displayOverview();
		} else {
			$this->displayHelp();
		}
	}
	
	function displayOverview() {
		$this->sendOutput("Available commands:",$this->info['nick']);
		$this->sendOutput("Type a '!' in front of each command.",$this->info['nick']);
		
		$level = $this->IRCBot->getUserLevel($this->info['nick']);
		$sql = "SELECT command, category FROM help  WHERE level <= ".$level." ORDER BY category ASC, command ASC";
		$res = $this->MySQL->sendQuery($sql);
		
		$help = array();
		foreach($res['result'] as $topic) {
			if(!isset($help[$topic['category']])) $help[$topic['category']] = array();
			array_push($help[$topic['category']],$topic['command']);
		}
		
		foreach($help as $category => $commands) {
			$text = $category.": ";
			foreach($commands as $command) {
				$text.= $command.", ";
			}
			$text = substr($text,0,-2);
			$this->sendOutput($text,$this->info['nick']);
		}
		
		$this->sendOutput("Type !help <command> to get a function explained.",$this->info['nick']);
		$this->sendOutput("If you want Nimda in your channel, contact noother. (It's free!)",$this->info['nick']);
		
	}
	
	function displayHelp() {
		$sql = "SELECT * FROM help WHERE command = '".addslashes($this->info['text'])."'";
		$res = $this->MySQL->sendQuery($sql);
		if($res['count'] == 0) {
			$this->sendOutput("No information available about '".$this->info['text']."'");
			return;
		}
		
		$help = $res['result'][0];
		
		$text = "Usage: ".$help['usage'].", ".$help['text'];
		$this->sendOutput($text,$this->info['nick']);
		
	}

}

?>
