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

class Notes extends Plugin {

	function isTriggered() {
		
		$status = $this->IRCBot->getUserStatus($this->info['nick']);
		if($status != 3) {
			$this->sendOutput($this->CONFIG['nopermission_text'],$this->info['nick']);
			return;
		}
		
		switch($this->info['triggerUsed']) {
			case '!leavenote':
				list($foruser, $note) = explode(' ',$this->info['text'],2);
				
				if(empty($foruser) || empty($note)) {
					$this->sendOutput($this->CONFIG['usage_text']);
					return;
				}
				
				$sql = "INSERT INTO
							notes (foruser, fromuser, note, created)
						VALUES (
							'".addslashes($foruser)."',
							'".addslashes($this->info['nick'])."',
							'".addslashes($note)."',
							NOW()
						)";
				$this->MySQL->query($sql);
				$this->sendOutput(sprintf($this->CONFIG['notesaved_text'],
											$this->info['nick'],
											$foruser));
				
				break;
			case '!notes':
				$notes = $this->getNotes($this->info['nick']);
				if(!$notes) {
					$this->sendOutput($this->CONFIG['nonotes_text'],$this->info['nick']);
					return;
				}
				
				$c = 0;
				foreach($notes as $note) {
					$c++;
					$this->sendOutput(	'Note #'.$c.' from '.$note['fromuser'].': '.$note['note'],
										$this->info['nick']);
					$sql = "UPDATE notes SET `read` = 1 WHERE id='".$note['id']."'";
					$this->MySQL->query($sql);
				}	
				
				break;
		}
	}
	
	function onJoin() {
		$notes = $this->getNotes($this->info['nick']);
		if(!$notes) return;
		
		if(sizeof($notes) == 1) {
			$this->sendOutput(sprintf($this->CONFIG['gotnote_text'],$this->info['nick'],$notes[0]['fromuser']),$this->info['nick']);
		} else {
			$this->sendOutput(sprintf($this->CONFIG['gotmanynotes_text'],$this->info['nick'],sizeof($notes)),$this->info['nick']);
		}
		
	}
	
	function getNotes($nick) {
		$sql = "SELECT
					*
				FROM
					notes
				WHERE
					foruser='".addslashes($nick)."'
					AND `read` = 0
				";
		$res = $this->MySQL->query($sql);
		if(!$res['count']) return false;
	return $res['result'];
	}

}

?>

