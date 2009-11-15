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

class Stats extends Plugin {
	
	private $nick;
	private $channel;
	
	function isTriggered() {
		switch($this->info['triggerUsed']) {
			case "!stats":
				$this->showStats();
				break;
			case "!top10":
				$this->showTop10();
				break;
		}
	return;
	}
	
	function showStats() {
		$target = isset($this->info['text'])?$this->info['text']:$this->info['nick'];
		
		if($this->info['isQuery']) {
		
			$sql = "SELECT nick FROM stats WHERE nick='".addslashes($target)."'";
			$res = $this->MySQL->sendQuery($sql);
			if($res['count'] == 0) {
				$this->sendOutput("No stats available about '".$target.".");
				return;
			}
			$nick = $res['result'][0]['nick'];
		
			$sql = "SELECT
						SUM(chars) chars,
						SUM(words) words,
						SUM(`lines`) `lines`,
						SUM(actions) actions,
						SUM(smilies) smilies,
						SUM(kicks) kicks,
						SUM(kicked) kicked,
						SUM(modes) modes,
						SUM(topics) topics
					FROM
						stats
					WHERE
						nick='".addslashes($target)."'
					";
			$res = $this->MySQL->sendQuery($sql);
			$data = $res['result'][0];
			$output = $nick.": ";
			
			
		} else {
			$sql = "SELECT * FROM stats WHERE channel='".addslashes($this->info['channel'])."' AND nick = '".addslashes($target)."'";
			$res = $this->MySQL->sendQuery($sql);
			if($res['count'] == 0) {
				$this->sendOutput("No stats available about '".$target."' in '".$this->info['channel']."'");
				return;
			}
			
			$data = $res['result'][0];
			
			$output = $data['nick']." in ".$data['channel'].": ";
		}
		
		$output.= $data['chars']." ".libString::end_s("char",$data['chars']).", ";
		$output.= $data['words']." ".libString::end_s("word",$data['words']).", ";
		$output.= $data['lines']." ".libString::end_s("line",$data['lines']).", ";
		$output.= round($data['chars']/$data['lines'],2)." cpl, ";
		$output.= round($data['words']/$data['lines'],2)." wpl, ";
		$output.= $data['actions']." ".libString::end_s("action",$data['actions']).", ";
		$output.= $data['smilies']." ".libString::end_s("smilie",$data['smilies']).", ";
		$output.= "kicked ".$data['kicks']." ".libString::end_s("luser",$data['kicks']).", ";
		$output.= "been kicked ".$data['kicked']." ".libString::end_s("time",$data['kicked']).", ";
		$output.= "set ".$data['modes']." ".libString::end_s("mode",$data['modes']).", ";
		$output.= "changed the topic ".$data['topics']." ".libString::end_s("time",$data['topics']).".";
		
		$this->sendOutput($output);
		
	}
	
	function showTop10() {
		$category = isset($this->info['text'])?$this->info['text']:"chars";
		
		if($this->info['isQuery']) {
			$output = "Top10 ('".$category."'): ";
			switch($category) {
				case "chars":
					$sql = "SELECT nick, SUM(chars) number FROM stats GROUP BY nick ORDER BY number DESC LIMIT 10";
					break;
				case "words":
					$sql = "SELECT nick, SUM(words) number FROM stats GROUP BY nick ORDER BY number DESC LIMIT 10";
					break;
				case "lines":
					$sql = "SELECT nick, SUM(`lines`) number FROM stats GROUP BY nick ORDER BY number DESC LIMIT 10";
					break;
				case "actions":
					$sql = "SELECT nick, SUM(actions) number FROM stats GROUP BY nick ORDER BY number DESC LIMIT 10";
					break;
				case "smilies":
					$sql = "SELECT nick, SUM(smilies) number FROM stats GROUP BY nick ORDER BY number DESC LIMIT 10";
					break;
				case "kicks":
					$sql = "SELECT nick, SUM(kicks) number FROM stats GROUP BY nick ORDER BY number DESC LIMIT 10";
					break;
				case "kicked":
					$sql = "SELECT nick, SUM(kicked) number FROM stats GROUP BY nick ORDER BY number DESC LIMIT 10";
					break;
				case "modes":
					$sql = "SELECT nick, SUM(modes) number FROM stats GROUP BY nick ORDER BY number DESC LIMIT 10";
					break;
				case "topics":
					$sql = "SELECT nick, SUM(topics) number FROM stats GROUP BY nick ORDER BY number DESC LIMIT 10";
					break;
				case "wpl":
					$sql = "SELECT nick, ROUND(SUM(words)/SUM(`lines`),2) number FROM stats GROUP BY nick ORDER BY number DESC LIMIT 10";
					break;
				case "cpl":
					$sql = "SELECT nick, ROUND(SUM(chars)/SUM(`lines`),2) number FROM stats GROUP BY nick ORDER BY number DESC LIMIT 10";
					break;
				default:
					$this->sendOutput("The category '".$category."' doesn't exist.");
					return;
			}
		} else {
			$output = "Top10 ('".$category."') in ".$this->info['channel'].": ";
			$channel = addslashes($this->info['channel']);
			switch($category) {
				case "chars":
					$sql = "SELECT nick, chars number FROM stats WHERE channel='".$channel."' ORDER BY chars DESC LIMIT 10";
					break;
				case "words":
					$sql = "SELECT nick, words number FROM stats WHERE channel='".$channel."' ORDER BY words DESC LIMIT 10";
					break;
				case "lines":
					$sql = "SELECT nick, `lines` number FROM stats WHERE channel='".$channel."' ORDER BY `lines` DESC LIMIT 10";
					break;
				case "actions":
					$sql = "SELECT nick, actions number FROM stats WHERE channel='".$channel."' ORDER BY actions DESC LIMIT 10";
					break;
				case "smilies":
					$sql = "SELECT nick, smilies number FROM stats WHERE channel='".$channel."' ORDER BY smilies DESC LIMIT 10";
					break;
				case "kicks":
					$sql = "SELECT nick, kicks number FROM stats WHERE channel='".$channel."' ORDER BY kicks DESC LIMIT 10";
					break;
				case "kicked":
					$sql = "SELECT nick, kicked number FROM stats WHERE channel='".$channel."' ORDER BY kicked DESC LIMIT 10";
					break;
				case "modes":
					$sql = "SELECT nick, modes number FROM stats WHERE channel='".$channel."' ORDER BY modes DESC LIMIT 10";
					break;
				case "topics":
					$sql = "SELECT nick, topics number FROM stats WHERE channel='".$channel."' ORDER BY topics DESC LIMIT 10";
					break;
				case "wpl":
					$sql = "SELECT nick, ROUND(words/`lines`,2) number FROM stats WHERE channel='".$channel."' ORDER BY number DESC LIMIT 10";
					break;
				case "cpl":
					$sql = "SELECT nick, ROUND(chars/`lines`,2) number FROM stats WHERE channel='".$channel."' ORDER BY number DESC LIMIT 10";
					break;
				default:
					$this->sendOutput("The category '".$category."' doesn't exist.");
					return;
			}
		}
		
		$res = $this->MySQL->sendQuery($sql);
		foreach($res['result'] as $data) {
			$output.= $data['nick']." (".$data['number']."), ";
		}
		$output = substr($output,0,-2);
		
		$this->sendOutput($output);
		
		
	}
	
	
	function init($nick=null,$channel=null) {
		if(isset($nick)) $this->nick = addslashes($nick);
		else $this->nick = addslashes($this->info['nick']);
		if(isset($channel)) $this->channel = addslashes($channel);
		else $this->channel = addslashes($this->info['channel']);
		
		$sql = "SELECT 1 FROM stats WHERE channel='".$this->channel."' AND nick = '".$this->nick."'";
		$res = $this->MySQL->sendQuery($sql);
		if($res['count'] == 0) {
			$sql = "INSERT INTO stats (channel, nick) VALUES ('".$this->channel."', '".$this->nick."')";
			$this->MySQL->sendQuery($sql);
		}
	}
	
	function onChannelMessage() {
		$this->init();
		
		$chars = strlen($this->info['text']);
		$words = sizeof(explode(" ",$this->info['text']));
		$smilies = libString::countSmilies($this->info['text']);
		
		$sql = "UPDATE stats SET chars = chars+".$chars.", words=words+".$words.", smilies=smilies+".$smilies.", `lines`=`lines`+1 WHERE channel='".$this->channel."' AND nick='".$this->nick."'";
		$this->MySQL->sendQuery($sql);
	}
	
	function onAction() {
		if($this->info['isQuery']) return;
		$this->init();
		
		$sql = "UPDATE stats SET actions=actions+1 WHERE channel='".$this->channel."' AND nick='".$this->nick."'";
		$this->MySQL->sendQuery($sql);
	}
	
	function onKick() {
		$this->init();
		$sql = "UPDATE stats SET kicks=kicks+1 WHERE channel='".$this->channel."' AND nick='".$this->nick."'";
		$this->MySQL->sendQuery($sql);
		
		$this->init($this->info['kicked']);
		$sql = "UPDATE stats SET kicked=kicked+1 WHERE channel='".$this->channel."' AND nick='".$this->nick."'";
		$this->MySQL->sendQuery($sql);
	}
	
	function onTopic() {
		$this->init();
		$sql = "UPDATE stats SET topics=topics+1 WHERE channel='".$this->channel."' AND nick='".$this->nick."'";
		$this->MySQL->sendQuery($sql);
	}
	
	function onMode() {
		$this->init();
		$modes = sizeof($this->info['modes']);
		$sql = "UPDATE stats SET modes=modes+".$modes." WHERE channel='".$this->channel."' AND nick='".$this->nick."'";
		$this->MySQL->sendQuery($sql);
		/*
		$nick = $this->info['nick'];
		foreach($this->info['modes'] as $mode) {
			$this->sendOutput($nick." has ".($mode[0]=="+"?"given":"taken")." mode ".$mode[1]." ".($mode[0]=="+"?'to':'from')." ".$mode[2]);
		}
		*/
	}
}

?>
