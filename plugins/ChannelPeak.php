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

class ChannelPeak extends Plugin {
	
	private $peaks = array();
	
	function ChannelPeak($config,$IRCBot) {
		parent::Plugin($config,$IRCBot);
		$res = $this->MySQL->query("SELECT * FROM channel_peaks");
		foreach($res['result'] as $item) {
			$this->peaks[strtolower($item['channel'])] = array('users' => $item['users'], 'time' => $item['date']);
		}
	}
	
	function isTriggered() {
		$channel = strtolower($this->info['channel']);
		
		$this->sendOutput(sprintf($this->CONFIG['text'],
									$this->info['channel'],
									$this->peaks[$channel]['users'],
									date('Y-m-d H:i:s',$this->peaks[$channel]['time'])
									));
	}
	
	function onJoin() {
		$info = $this->info;
		
		$lChannel = strtolower($info['channel']);
		
		if(!isset($this->peaks[$lChannel])) {
			$this->peaks[$lChannel] = array('users' => 0, 'time' => 0);
			$this->MySQL->query("INSERT INTO channel_peaks (channel, users, date) VALUES ('".addslashes($lChannel)."', 0, '".time()."')");
		}
		
		$users = $this->IRCBot->getUsers($info['channel']);
		if(!$users) return;
		$users = sizeof($users);
		
		if($users > $this->peaks[$lChannel]['users']) {
			
			$oldpeak = array();
			$oldpeak['users'] = $this->peaks[$lChannel]['users'];
			$oldpeak['time']  = $this->peaks[$lChannel]['time'];
			
			$this->peaks[$lChannel]['users'] = $users;
			$this->peaks[$lChannel]['time']  = time();
			
			$this->MySQL->query("UPDATE channel_peaks SET users='".$users."', date='".time()."' WHERE channel='".addslashes($lChannel)."'");
			
			if($oldpeak['users'] == 0) return;
			
			$this->sendOutput(sprintf($this->CONFIG['text_newpeak'],
										$info['channel'],
										$users,
										$oldpeak['users'],
										date('Y-m-d H:i:s',$oldpeak['time']),
										libTime::secondsToString(time()-$oldpeak['time'])
										),$info['channel']);
			
		}
	}

}

?>
