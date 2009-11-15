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

require_once("plugins/Roulette/Game.php");

class Roulette extends Plugin {

private $games = array();

	function isTriggered() {
		
		switch($this->info['triggerUsed']) {
			case "!roulette":
				if(isset($this->info['text'])) {
					if($this->info['text'] == "stats") {
						if($this->info['isQuery']) {
							$this->showStats();
						} else {
							$this->showStats($this->info['channel']);
						}
					} else {
						$tmp = explode(" ",$this->info['text'],2);
						if(sizeof($tmp) == 2 && $tmp[0] == "stats") {
							if($this->info['isQuery']) {
								$this->showPlayerStats($tmp[1]);
							} else {
								$this->showPlayerStats($tmp[1],$this->info['channel']);
							}
						}
					}
				} else {
					$this->init();
				}
				break;
		}
	}
	
	function init() {
		if($this->info['isQuery']) {
			$this->sendOutput("You can only play in the channel.");
			return;
		}
		if(!isset($this->games[$this->info['channel']])) $this->games[$this->info['channel']] = new Game($this);
		
		$this->games[$this->info['channel']]->info = $this->info;
		$return = $this->games[$this->info['channel']]->isTriggered();
		if($return == "end") unset($this->games[$this->info['channel']]);
	}
	
	function showStats($channel=null) {
		$sql = "SELECT
					SUM(lost) played,
					SUM(trigger_pulled) shots,
					COUNT(nick) players
				FROM
					roulette_stats
				".(isset($channel)?"WHERE channel = '".addslashes($channel)."'":"")."
				";
		$tmp = $this->MySQL->sendQuery($sql);
		$stats = $tmp['result'][0];
		if(!$stats['played']) {
			$this->sendOutput("No games have been played yet.");
			return;
		}
		
		$sql = "SELECT
					nick,
					clicks/trigger_pulled percentage
				FROM
					roulette_stats
				".(isset($channel)?"WHERE channel = '".addslashes($channel)."'":"")."
				GROUP BY
					nick
				ORDER BY
					percentage DESC
				";
		$tmp = $this->MySQL->sendQuery($sql);
		$luckiest = $tmp['result'][0];
		$unluckiest = $tmp['result'][$tmp['count']-1];
		
		
		$output = sprintf("Roulette stats: %s %s completed, %s %s fired at %s %s. Luckiest: %s (%s%% clicks). Unluckiest: %s (%s%% clicks).",
											$stats['played'],
											libString::end_s("game",$stats['played']),
											$stats['shots'],
											libString::end_s("shot",$stats['shots']),
											$stats['players'],
											libString::end_s("player",$stats['players']),
											$luckiest['nick'],
						 					$luckiest['percentage']*100,
											$unluckiest['nick'],
											$unluckiest['percentage']*100
						 					);
		
		$this->sendOutput($output);
		
	}
	
	function showPlayerStats($nick,$channel=null) {
		
		$sql = "SELECT
					nick,
					SUM(played) played,
					SUM(won) won,
					SUM(lost) lost,
					SUM(trigger_pulled) trigger_pulled,
					SUM(clicks) clicks
				FROM
					roulette_stats
				WHERE
					nick='".addslashes($nick)."'
					".(isset($channel)?" AND channel='".addslashes($channel)."'":"")."
				GROUP BY
					nick";
		$res = $this->MySQL->sendQuery($sql);
		if($res['count'] == 0) {
			$this->sendOutput(sprintf("%s has never played roulette.",$nick));
			return;
		}
		
		$stats = $res['result'][0];
		
		$text = "%s has played %s %s, won %s and lost %s. %s pulled the trigger %s %s and found the chamber empty on %s %s.";
		
		$this->sendOutput(sprintf($text,
							$stats['nick'],
							$stats['played'],
							libString::end_s("game",$stats['played']),
							$stats['won'],
							$stats['lost'],
							$stats['nick'],
							$stats['trigger_pulled'],
							libString::end_s("time",$stats['trigger_pulled']),
							$stats['clicks'],
							libString::end_s("ocassion",$stats['clicks'])));
	}

}

?>
