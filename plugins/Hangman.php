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

require_once("plugins/Hangman/HangmanGame.php");

class Hangman extends Plugin {
	
	private $games = array();
	
	function isTriggered() {
		if($this->info['isQuery']) {
			$this->sendOutput("You can only play in the channel.");
			return;
		}
		
		if(!isset($this->info['text'])) {
			$this->sendOutput($this->CONFIG['usage']);
			return;
		}
		
		$tmp = explode(" ",$this->info['text']);
		switch($tmp[0]) {
			case "start":
				// Start a new game
				$this->startGame($this->info['channel']);
				break;
			case "stop":
				// Stop the current game and show solution
				$this->games[$this->info['channel']]->showSolution();
				$this->stopGame($this->info['channel']);
				break;
			case "stats":
				if(isset($tmp[1])) {
					// Show user stats for current channel
					$this->showUserStats($tmp[1],$this->info['channel']);
				} else {
					// Show channel specific stats
					$this->showStats($this->info['channel']);
				}
				break;
			case "add":
				// Add a word to the database
				if(!isset($tmp[1])) $this->sendOutput("No word given.");
				else $this->addWord($tmp[1]);
				break;
			case "del":
				// Delete a word from the database
				if(!isset($tmp[1])) $this->sendOutput("No word given.");
				else $this->delWord($tmp[1]);
				break;
			default:
				if(!isset($this->games[$this->info['channel']])) {
					$this->sendOutput("Hangman is not started yet.");
					return false;
				}
				
				if(strlen($this->info['text']) == 1) {
					// Try a single char
					$this->games[$this->info['channel']]->tryChar($this->info['nick'],$this->info['text']);
				} else {
					// Try a solution
					$this->games[$this->info['channel']]->trySolution($this->info['nick'],$this->info['text']);
				}
				break;
		}
		
		if($this->games[$this->info['channel']]->finish == "true") {
			$this->stopGame($this->info['channel']);
		}
		
	return;
	}
	
	function startGame($channel) {
		if(isset($this->games[$channel])) {
			$this->sendOutput("There is already a game of Hangman running.");
			return false;
		}
		
		$this->games[$channel] = new HangmanGame($this,$channel);
	return true;
	}
	
	function stopGame($channel) {
		if(!isset($this->games[$channel])) {
			$this->sendOutput("Hangman is not running yet.");
			return false;
		}
		
		unset($this->games[$channel]);
		$this->sendOutput("Hangman has been stopped.");
	return true;
	}
	
	function showStats($channel) {
		$words = $this->MySQL->sendQuery("SELECT COUNT(*) FROM hangman");
		$words = $words['result'][0]['COUNT(*)'];
		
		$sql = "SELECT COUNT(*) FROM hangman_stats WHERE channel='".addslashes($channel)."'";
		$res = $this->MySQL->sendQuery($sql);
		$players = $res['result'][0]['COUNT(*)'];
		
		$output = sprintf($this->CONFIG['stats_text'],
								$players,
								$words);
		
		$sql = "SELECT
					nick, points
				FROM
					hangman_stats
				WHERE
					channel='".addslashes($channel)."'
				ORDER BY
					points DESC
				LIMIT 5";
		$res = $this->MySQL->sendQuery($sql);
		
		$output.=" Top5: ";
		foreach($res['result'] as $row) {
			$output.= $row['nick']." (".$row['points']."), ";
		}
		$output = substr($output,0,-2);
		$this->sendOutput($output);
	return true;
	}
	
	function showUserStats($nick,$channel) {
		
		$sql = "SELECT
					nick, points, last_played
				FROM
					hangman_stats
				WHERE
					channel='".addslashes($channel)."'
				ORDER BY
					points
				DESC
				";
		$res = $this->MySQL->sendQuery($sql);
		
		$check = false;
		foreach($res['result'] as $key => $value) {
			if(strtolower($value['nick']) == strtolower($nick)) {
				$check = true;
				$nick = $value['nick'];
				$rank = $key+1;
				$points = $value['points'];
				$last_played = $value['last_played'];
				break;
			} 
		}
		
		if(!$check) {
			$this->sendOutput($nick." has never played Hangman.");
			return;
		}
		
		$output = sprintf($this->CONFIG['userstats_text'],
								$nick,
								$points,
								libString::end_s("point",$points),
								$rank,
								$nick,
								$last_played);
		$this->sendOutput($output);
	}
	
	function addWord($word) {
		$old_word = $word;
		$word = libString::convertUmlaute($word);
		$word = libString::capitalize($word);
		if(preg_match("/[^a-zA-Z]/",$word)) {
			$this->sendOutput("Your word must contain only letters.");
			return;
		}
		
		$sql = "SELECT id FROM hangman WHERE word='".addslashes($word)."'";
		$res = $this->MySQL->sendQuery($sql);
		if($res['count'] != 0) {
			$this->sendOutput("This word is already in the database.");
			return;
		}
		
		$google_results = libInternet::googleResults($old_word);
		if($google_results < $this->CONFIG['google_min']) {
			//$output = sprintf($this->CONFIG['googlemin_text'],
			//					$this->CONFIG['google_min']
			//				 );
			$this->sendOutput($this->CONFIG['googlemin_text']);
			return;
		}
		
		$blacklist = array("add","del","stats","start","stop");
		if(array_search(strtolower($word),$blacklist) !== false) {
			$this->sendOutput("Stuuuuupiiid!");
			return;
		}
		
		$sql = "INSERT INTO
					hangman
					(word, author, created)
				VALUES (
					'".addslashes($word)."',
					'".addslashes($this->info['nick'])."',
					NOW()
				)";
		$this->MySQL->sendQuery($sql);
		$this->sendOutput("Word '".$word."' has been added to the database.");
	return;
	}
	
	function delWord($word) {
		$sql = "SELECT COUNT(*) FROM hangman WHERE word='".addslashes($word)."'";
		$res = $this->MySQL->sendQuery($sql);
		if($res['result'][0]['COUNT(*)'] == 0) {
			$this->sendOutput("This word is not in the database.");
			return;
		}
		
		$sql = "DELETE FROM hangman WHERE word='".addslashes($word)."'";
		$this->MySQL->sendQuery($sql);
		$this->sendOutput("Word '".$word."' has been deleted from the database.");
	return;
	}
}

?>
