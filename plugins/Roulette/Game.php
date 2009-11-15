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

class Game {

	private $players 		= array();
	private $currentPlayer 	= "";
	private $lastPlayer		= "";
	private $chambers       = 0;
	private $badChamber		= 0;
	private $currentChamber = 0;
	private $waiting		= false;
	public  $isActive       = false;
	
	private $Plugin;
	private $IRCBot;
	private $MySQL;
	public  $info;
	private $CONFIG;
	
	
	function Game($game) {
		$this->isActive = true;
		echo $game->info['channel']." started a new game of Roulette.\n";
		$this->Plugin   = $game;
		$this->IRCBot   = $game->IRCBot;
		$this->MySQL    = $game->IRCBot->MySQL;
		$this->info     = $game->info;
		$this->CONFIG   = $game->CONFIG;
		
		$this->chambers       = $this->CONFIG['chambers'];
		$this->currentChamber = 0;
		$this->badChamber     = mt_rand(1,$this->CONFIG['chambers']);
	}
	
	function isTriggered() {
		$this->currentPlayer = $this->info['nick'];
		
		if($this->currentPlayer == $this->lastPlayer) {
			$this->Plugin->sendOutput("You can't pull the trigger twice in a row, dolt!");
			return;
		}
		$this->lastPlayer = $this->currentPlayer;
		
		$this->currentChamber++;
		echo $this->currentChamber." / ".$this->chambers." ".$this->badChamber."\n";
		
		if(!isset($this->players[$this->currentPlayer]) && $this->IRCBot->getUserStatus($this->currentPlayer) == 3) {
			$this->addPlayerToList($this->currentPlayer);
		}
		
		$this->increaseTriggerPulled($this->currentPlayer);
		$this->displayWaitMessage();
		//$this->wait();
		$this->shoot();
		
		if(!$this->isActive) {
			$this->updateStats();
			return "end";
		}
	}
	
	function displayWaitMessage() {
		$this->Plugin->sendOutput(sprintf($this->getText('wait'),$this->info['nick']));
	}
	
	function wait() {
		$waitTime = mt_rand($this->CONFIG['min_wait_time'],$this->CONFIG['max_wait_time']);
		$this->waiting = true;
		array_push($this->IRCBot->oneShotTimers, new oneShotTimer(time()+$waitTime,"Roulette","continueGame"));
	}
	
	function addPlayerToList($nick) {
		$this->players[$nick] = array();
		$this->players[$nick]['triggerPulled'] = 0;
		$this->players[$nick]['clicks'] = 0;
	}
	
	function increaseClicks($nick) {
		if(isset($this->players[$nick])) $this->players[$nick]['clicks']++;
	}
	
	function increaseTriggerPulled($nick) {
		if(isset($this->players[$nick])) $this->players[$nick]['triggerPulled']++;
	}
	
	function kill($nick) {
		if(isset($this->players[$nick])) $this->players[$nick]['lost'] = true;
	}
	
	function shoot() {
		$text = sprintf($this->CONFIG['text_shoot'],$this->currentPlayer,$this->currentChamber,$this->chambers);
		
		if($this->currentChamber != $this->badChamber) {
			$text.= " ".$this->CONFIG['text_miss'];
			$this->Plugin->sendOutput($text);
			$this->Plugin->sendOutput(sprintf($this->getText('miss'),$this->currentPlayer));
			$this->increaseClicks($this->currentPlayer);
		} else {
			$text.= " ".$this->CONFIG['text_kill'];
			$this->Plugin->sendOutput($text);
			$this->Plugin->sendOutput(sprintf($this->getText('kill'),$this->currentPlayer));
			$this->Plugin->sendAction("reloads");
			
			$this->kill($this->currentPlayer);
			$this->isActive = false;
		}
	}
	
	function updateStats() {
		foreach($this->players as $nick => $player) {
			if(!$this->userExists($nick)) $this->createUser($nick);
			$sql = "UPDATE
						roulette_stats
					SET
						played = played+1,
						".($player['lost']?'lost = lost+1':'won = won+1').",
						trigger_pulled = trigger_pulled + ".$player['triggerPulled'].",
						clicks = clicks + ".$player['clicks'].",
						lastUpdate = NOW()
					WHERE
						nick = '".addslashes($nick)."' AND
						channel = '".addslashes($this->info['channel'])."'
					";
			$this->MySQL->sendQuery($sql);
		}
	}
	
	function userExists($nick) {
		$sql = "SELECT * FROM roulette_stats WHERE nick='".addslashes($nick)."' AND channel='".addslashes($this->info['channel'])."'";
		$result = $this->MySQL->sendQuery($sql);
		if($result['count'] === 0) return false;
		else return true;
	}
	
	function createUser($nick) {
		$sql = "INSERT INTO roulette_stats (nick, channel, lastUpdate, created) VALUES ('".addslashes($nick)."', '".addslashes($this->info['channel'])."', NOW(), NOW())";
		$this->MySQL->sendQuery($sql);
	}
	
	function getText($type) {
		$sql    = "SELECT text FROM roulette_text WHERE type='".addslashes($type)."' ORDER BY RAND() LIMIT 1";
		$result = $this->MySQL->sendQuery($sql);
	return $result['result'][0]['text'];
	}

}

?>
