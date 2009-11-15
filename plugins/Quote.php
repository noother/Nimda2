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

class Quote extends Plugin {

	function isTriggered() {
		switch($this->info['triggerUsed']) {
			case "!quote":
				$this->showQuote();
				break;
			case "!quote add":
				$this->addQuote();
				break;
			case "!quote del":
				$this->delQuote();
				break;
			case "!quote++":
			case "!quote--":
				$this->rateQuote();
				break;
		}
	}
	
	function showQuote() {
		if(!isset($this->info['text'])) {
			$sql = "SELECT * FROM quotes ORDER BY RAND() LIMIT 1";
			$text = "\x02[Random Quote]\x02 ";
		} elseif(preg_match("/[^0-9]/",$this->info['text'])) {
			$check = false;
			$sql = "SELECT id FROM quotes WHERE quote LIKE '%".addslashes($this->info['text'])."%'";
			$res = $this->MySQL->sendQuery($sql);
			if($res['count'] == 0) {
				$this->sendOutput("There's no quote that matches.");
				return;
			} elseif($res['count'] == 1) {
				$check = $res['result'][0]['id'];
				$sql = "SELECT * FROM quotes WHERE id='".addslashes($check)."'";
				$text = "";
			} else {
				$text = "Quotes which match are: ";
				foreach($res['result'] as $data) {
					$text.= $data['id'].", ";
				}
				$text = substr($text,0,-2);
				$this->sendOutput($text);
			}
			if(!$check) return;
		} else {
			$sql = "SELECT * FROM quotes WHERE id='".addslashes($this->info['text'])."'";
			$text = "";
		}
		
		$res = $this->MySQL->sendQuery($sql);
		if($res['count'] == 0) {
			$this->sendOutput("A quote with this ID doesn't exist.");
			return;
		}
		
		$data = $res['result'][0];
		$text.= sprintf($this->CONFIG['text'],
							$data['quote'],
							$data['id'],
							$data['views'],
							$data['rating']
						);
		$this->sendOutput($text);
		
		$this->MySQL->sendQuery("UPDATE quotes SET views=views+1 WHERE id='".$data['id']."'");
	}
	
	function addQuote() {
		if(!isset($this->info['text'])) {
			$this->sendOutput("Usage: !quote add your_quote_here");
			return;
		}
		
		$quote  = addslashes($this->info['text']);
		$author = addslashes($this->info['nick']);
		
		$id = $this->MySQL->sendQuery("INSERT INTO quotes (quote, author, created) VALUES ('".$quote."', '".$author."', NOW())");
		$this->sendOutput("Quote has been added. (id: ".$id.")");
		
	}
	
	function delQuote() {
		if($this->IRCBot->getUserStatus($this->info['nick']) != 3) {
			$this->sendOutput("You need to be registered and identified to delete quotes.");
			return;
		}		
		if(!isset($this->info['text'])) {
			$this->sendOutput("Usage: !quote del id");
			return;
		}
		
		if(!$this->quoteExists($this->info['text'])) {
			$this->sendOutput("A quote with this ID doesn't exist.");
			return;
		}
		
		$sql = "DELETE FROM quotes WHERE id='".addslashes($this->info['text'])."'";
		$this->MySQL->sendQuery($sql);
		
		$this->sendOutput("The quote with id ".$this->info['text']." has been deleted.");
	}
	
	function rateQuote() {
		if(!isset($this->info['text'])) {
			$this->sendOutput("Usage: !quote++ id or !quote-- id");
			return;
		}
		
		if(!$this->quoteExists($this->info['text'])) {
			$this->sendOutput("A quote with this id doesn't exist.");
			return;
		}
		
		switch($this->info['triggerUsed']) {
			case "!quote++":
				$this->MySQL->sendQuery("UPDATE quotes set rating = rating+1 WHERE id='".addslashes($this->info['text'])."'");
				$this->sendOutput("Quote with ID ".$this->info['text']." has been rated ++");
				break;
			case "!quote--":
				$this->MySQL->sendQuery("UPDATE quotes set rating = rating-1 WHERE id='".addslashes($this->info['text'])."'");
				$this->sendOutput("Quote with ID ".$this->info['text']." has been rated --");
				break;
		}
		
		
	}
	
	function quoteExists($id) {
		$sql = "SELECT 1 FROM quotes WHERE id='".addslashes($id)."'";
		$res = $this->MySQL->sendQuery($sql);
		if($res['count'] == 0) return false;
	return true;
	}

}

?>
